'use strict';

const assert = require('node:assert/strict');
const { spawnSync } = require('node:child_process');
const { createHash } = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');

const MANIFEST_SCHEMA = 'gramlyze-perf29-immutable-v1';
const SEAL_SCHEMA = 'gramlyze.ntfs-seal/v1';
const HEX = /^[a-f0-9]{64}$/;
const VITE_SOURCE = /^build\/assets\/[A-Za-z0-9][A-Za-z0-9_.-]{0,159}-[A-Za-z0-9_-]{8,32}\.(?:css|m?js)$/;
const STABLE_SOURCE = /^(?:js\/[A-Za-z0-9_-]+\.js|[A-Za-z0-9_-]+\.svg)$/;
const digest = value => createHash('sha256').update(value).digest('hex');
const rx = value => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
const canonical = value => {
  assert.equal(typeof value, 'string');
  assert(path.isAbsolute(value), 'use absolute paths');
  return fs.realpathSync.native(value);
};
const within = (root, file) => {
  const rel = path.relative(root, file);
  return !rel || (rel !== '..' && !rel.startsWith('..' + path.sep) && !path.isAbsolute(rel));
};
const apart = (a, b) => !within(a, b) && !within(b, a);

function apachePath(value) {
  const result = value.split(path.sep).join('/');
  // The confined grammar excludes interpolation, sed-like escapes and UNC.
  assert.match(result, /^[A-Za-z]:\/[A-Za-z0-9_./ -]{1,820}$/, 'unsupported Apache path');
  return result;
}

function quote(value) {
  assert(!/[\r\n"]/.test(value), 'unsafe Apache argument');
  return '"' + value + '"';
}

function assertSealed(root, workerRoleSid = null) {
  assert.equal(process.platform, 'win32', 'no supported NTFS seal verifier');
  // The trusted interpreter is deliberately not selected via PATH, a
  // caller override or an execution-policy change. Missing PS7 blocks D.
  const powershell = 'C:/Program Files/PowerShell/7/pwsh.exe';
  const script = path.join(__dirname, 'seal-release.ps1');
  const result = spawnSync(powershell, ['-NoLogo', '-NoProfile', '-NonInteractive', '-File', script,
    '-Mode', 'Verify', '-ReleaseRoot', root, ...(workerRoleSid === null ? [] : ['-WorkerRoleSid', workerRoleSid])],
  {encoding: 'utf8', timeout: 60_000, windowsHide: true});
  assert(!result.error && result.status === 0 && !result.signal,
    'NTFS generation seal not proven: ' + (result.error || result.stderr || 'missing success status'));
  // Receipts are returned by a fresh verifier subprocess, never supplied by
  // the caller or read from a claimed metadata file inside the generation.
  const proof = JSON.parse(result.stdout.replace(/^\uFEFF/, ''));
  assert.equal(proof.schema, SEAL_SCHEMA, 'unexpected NTFS verifier');
  assert.equal(proof.immutable, true, 'NTFS verifier did not attest immutability');
  assert.equal(proof.principal, 'current-process', 'NTFS verifier checked a different principal');
  assert.equal(proof.workerRoleSid ?? null, workerRoleSid, 'NTFS verifier checked a different worker role');
  assert.equal(canonical(proof.root), root, 'NTFS verifier did not check this root');
  assert.match(proof.sid, /^S-1-(?:\d+-)*\d+$/);
  assert.match(proof.integritySid, /^S-1-16-\d+$/);
  assert(Array.isArray(proof.inventory) && proof.inventory.length > 0, 'empty NTFS seal inventory');
  return proof;
}

function validateEntry(entry, manifestScope) {
  assert(entry && typeof entry === 'object');
  assert(entry.class === 'vite' || (entry.class === 'stable' && manifestScope === 'isolated-with-stable'));
  assert.match(entry.source, entry.class === 'vite' ? VITE_SOURCE : STABLE_SOURCE);
  assert.equal(entry.url, '/' + entry.source);
  assert.equal(entry.identity.path, 'identity/' + entry.source);
  assert.match(entry.identity.sha256, HEX);
  assert(Number.isSafeInteger(entry.identity.bytes) && entry.identity.bytes >= 0);
  assert(Number.isSafeInteger(entry.modified) && entry.modified >= 0);
  const extension = path.posix.extname(entry.source);
  const mime = extension === '.css' ? ['text/css'] : extension === '.svg' ? ['image/svg+xml'] : ['text/javascript', 'application/javascript'];
  assert(mime.includes(entry.mime), 'unexpected MIME type');
  if (entry.gzip !== null) {
    assert.equal(entry.gzip.path, 'gzip/' + entry.source + '.gz');
    assert.match(entry.gzip.sha256, HEX);
    assert(Number.isSafeInteger(entry.gzip.bytes) && entry.gzip.bytes > 0 && entry.gzip.bytes < entry.identity.bytes);
  }
}

const sameRepresentation = (a, b) => a.mime === b.mime &&
  a.identity.sha256 === b.identity.sha256 && a.identity.bytes === b.identity.bytes &&
  a.gzip?.sha256 === b.gzip?.sha256 && a.gzip?.bytes === b.gzip?.bytes;

function patterns(entries, capture = false) {
  // Finite batched maps so an additional Vite entry does not produce an
  // overlong Apache directive. The only backreference is of a verified URL.
  const result = [];
  let chunk = [];
  let length = 0;
  for (const entry of entries) {
    const value = rx(entry.url);
    if (chunk.length && length + value.length > 3500) {
      result.push(chunk);
      chunk = [];
      length = 0;
    }
    chunk.push(value);
    length += value.length + 1;
  }
  if (chunk.length) result.push(chunk);
  return result.map(values => '^(' + (capture ? '' : '?:') + values.join('|') + ')$');
}

function headerRules(pattern) {
  return [
    '<LocationMatch ' + quote(pattern) + '>',
    '    Header merge Vary Accept-Encoding "expr=%{REQUEST_STATUS} != 304 && %{REQUEST_STATUS} != 406"',
    '    Header always merge Vary Accept-Encoding "expr=%{REQUEST_STATUS} == 304 || %{REQUEST_STATUS} == 406"',
    '</LocationMatch>',
  ];
}

const blank = '[[:blank:]]*';
const weight = blank + ';' + blank + 'q' + blank + '=' + blank;
const low = String.raw`0[.](?=[0-9]{0,2}[1-9])[0-9]{1,3}`;
const positive = '(?:1(?:[.]0{0,3})?|' + low + ')';
const quality = String.raw`(1(?:[.]0{0,3})?|0(?:[.][0-9]{0,3})?)`;
const token = coding => '(?:^|,)' + blank + coding;
const end = blank + '(?:,|$)';
const positiveOffer = coding => token(coding) + '(?:' + weight + positive + ')?' + end;
const zeroOffer = coding => token(coding) + weight + '0(?:[.]0*)?' + end;
const hasOffer = coding => token(coding) + blank + '(?:[;,]|$)';
const qualityOffer = (coding, value) => token(coding) + weight + value + blank + '(?=,|$)';
const match = (operator, pattern) => "req_novary('Accept-Encoding') " + operator + ' m#' + pattern + '#i';
const staticMethod = "%{REQUEST_METHOD} in { 'GET', 'HEAD' }";
const identityProhibited = '(' + match('=~', zeroOffer('identity')) + ' || (' +
  match('=~', zeroOffer('[*]')) + ' && ' + match('!~', positiveOffer('identity')) + '))';

function identityPreferred(coding) {
  // Lexical comparison is monotonic for valid qvalues in [0,1]; if equal
  // values differ only by trailing zeros either encoding may win the tie.
  const encoded = qualityOffer(coding, quality);
  const identity = qualityOffer('identity', quality);
  return '(' + match('=~', hasOffer('identity')) + ' && ((' +
    match('=~', encoded + '.*?' + identity) + ' && $1 < $2) || (' +
    match('=~', identity + '.*?' + encoded) + ' && $1 > $2) || (' +
    match('=~', token('identity') + end) + ' && ' + match('=~', qualityOffer(coding, low)) + ')))';
}

function codingUsable(coding) {
  return '(' + match('=~', positiveOffer(coding)) + ' && ' + match('!~', zeroOffer(coding)) +
    ' && (!' + identityPreferred(coding) + ' || ' + identityProhibited + '))';
}

const gzipEligible = staticMethod + " && (-z req_novary('Range') || " + identityProhibited + ') && (' +
  codingUsable('gzip') + ' || (' + match('!~', hasOffer('gzip')) + ' && ' + codingUsable('[*]') + '))';
const identityDenied = staticMethod + ' && ' + identityProhibited;

function render({publicRoot, generations, scope = 'vite'}) {
  assert(scope === 'vite' || scope === 'isolated-with-stable', 'invalid delivery scope');
  assert(Array.isArray(generations) && generations.length > 0 && generations.length <= 32, 'need pinned generations');
  for (const pin of generations) assert.match(pin.expectedDigest, HEX, 'invalid manifest SHA-256 pin');
  assert.equal(process.platform, 'win32', 'no supported NTFS seal verifier');
  const publicDir = canonical(publicRoot);
  const publics = apachePath(publicDir);
  const verify = require('./immutable-release.cjs').verify;

  const receipts = [];
  const selected = new Map();
  const directives = ['# PERF-29 Windows NTFS immutable release; include ONLY inside the dedicated Gramlyze VirtualHost.',
    '# Generated from independently pinned manifests and live NTFS seal verification.',
    '# No file contents are read for negotiation. An unavailable sealed file is not replaced with a mutable copy.',
    'RewriteEngine On',
    'RewriteRule "^/build/assets/.*\\.gz(?:/|$)" "-" [R=404,L,NC]'];
  const sections = [];

  for (let index = 0; index < generations.length; index++) {
    const pin = generations[index];
    const role = pin.workerRoleSid ?? null;
    if (role !== null) assert.match(role, /^S-1-5-21(?:-[1-9][0-9]{0,9}){3}-[1-9][0-9]{3,9}$/, 'invalid worker role SID');
    assert.equal(role, generations[0].workerRoleSid ?? null, 'mixed worker roles in one HTTP mapping');
    const root = canonical(pin.generation);
    const sealRoot = canonical(pin.sealRoot);
    assert(within(sealRoot, root) && sealRoot !== root && apart(publicDir, sealRoot),
      'protected generations must live strictly under a private vault outside the public directory');
    assert(!receipts.some(receipt => receipt.generation === root), 'generation repeated');
    // Hash and decompress only once (before publication of the include).
    // Check effective storage rights on both sides of the integrity read.
    const before = assertSealed(sealRoot, role);
    const verified = verify({generation: root, expectedDigest: pin.expectedDigest});
    const after = assertSealed(sealRoot, role);
    assert.equal(before.sid, after.sid, 'NTFS proof principal changed during integrity validation');
    assert.equal(before.integritySid, after.integritySid, 'NTFS proof integrity level changed during validation');
    assert.deepEqual(before.inventory, after.inventory, 'protected inventory changed during integrity validation');
    assert.equal(canonical(pin.generation), root);
    assert.equal(verified.manifestSha256, pin.expectedDigest);
    assert.equal(verified.manifest.schema, MANIFEST_SCHEMA);
    assert(verified.manifest.scope === 'vite' || verified.manifest.scope === 'isolated-with-stable');
    if (scope === 'vite') assert.equal(verified.manifest.scope, 'vite');
    const vouched = new Map(after.inventory.map(entry => [entry.path, entry.type]));
    const relative = path.relative(sealRoot, root).split(path.sep).join('/');
    assert(vouched.has(relative), 'generation directory is absent from the NTFS seal inventory');
    const directory = apachePath(root);
    receipts.push({generation: root, sealRoot, manifestSha256: verified.manifestSha256,
      viteManifestSha256: verified.viteManifestSha256, sealSid: after.sid, sealIntegritySid: after.integritySid,
      ...(role === null ? {} : {workerRoleSid: role})});
    directives.push('# Generation ' + pin.expectedDigest);

    const entries = [];
    for (const entry of verified.manifest.assets) {
      validateEntry(entry, verified.manifest.scope);
      assert(vouched.has(relative + '/' + entry.identity.path), 'identity path not attested by the NTFS seal');
      if (entry.gzip) assert(vouched.has(relative + '/' + entry.gzip.path), 'gzip path not attested by the NTFS seal');
      if (entry.class === 'stable' && index > 0) continue;
      if (selected.has(entry.url)) {
        // Reusing a fingerprint with different bytes/representation metadata
        // invalidates the whole routed set; don't silently serve new bytes.
        assert(sameRepresentation(selected.get(entry.url), entry), 'conflicting fingerprints: ' + entry.url);
        continue;
      }
      selected.set(entry.url, entry);
      entries.push(entry);
    }

    const aliases = entries.filter(entry => entry.class === 'stable');
    for (const pattern of patterns(aliases)) {
      directives.push('RewriteRule ' + quote(pattern.slice(0, -1) + '\\.gz(?:/|$)') + ' "-" [R=404,L,NC]');
    }
    const gzip = entries.filter(entry => entry.gzip !== null);
    for (const pattern of patterns(gzip, true)) {
      directives.push('RewriteCond expr ' + quote(gzipEligible),
        'RewriteRule ' + quote(pattern) + ' ' + quote(directory + '/gzip$1.gz') + ' [END,E=no-gzip:1,E=no-brotli:1]');
    }
    for (const pattern of patterns(entries, true)) {
      directives.push('RewriteCond expr ' + quote(identityDenied),
        'RewriteRule ' + quote(pattern) + ' "-" [R=406,L]',
        'RewriteRule ' + quote(pattern) + ' ' + quote(directory + '/identity$1') + ' [END,E=no-gzip:1,E=no-brotli:1]');
    }
    for (const pattern of patterns(entries)) sections.push(...headerRules(pattern));
    // Only authorized rewrite targets can reach this private directory.
    // Serve through the native file handler so sizes, dates and preconditions
    // apply to the chosen file. Never disable normal Apache range handling.
    if (entries.length) sections.push(...['identity', 'gzip'].map(kind => [
      '<Directory ' + quote(directory + '/' + kind) + '>',
      '    AllowOverride None',
      '    Options -Indexes -ExecCGI -MultiViews -FollowSymLinks',
      '    Require all granted',
      '    FileETag INode MTime Size',
      '    SetHandler default-handler',
      ...(kind === 'gzip' ? ['    AddEncoding gzip .gz'] : []),
      '    <FilesMatch "\\.css(?:\\.gz)?$">', '        ForceType text/css', '    </FilesMatch>',
      '    <FilesMatch "\\.m?js(?:\\.gz)?$">', '        ForceType text/javascript', '    </FilesMatch>',
      '    <FilesMatch "\\.svg(?:\\.gz)?$">', '        ForceType image/svg+xml', '    </FilesMatch>',
      '</Directory>',
    ].join('\n')));
  }

  assert(selected.size > 0 && [...selected.values()].some(entry => entry.gzip !== null), 'no sealed gzip sidecars');
  // Static paths outside the finite manifest keep the old fallback contract.
  // This stat only runs for misses; mapped GET/HEAD never examine the live
  // original and direct .gz is forbidden even if a stray public copy exists.
  directives.push('RewriteCond ' + quote(publics + '$0') + ' !-f',
    'RewriteRule "^/build/assets/.*$" "-" [R=404,L]', '', ...sections, '');
  const config = directives.join('\n');
  return {config, configSha256: digest(config), receipts, assetCount: selected.size,
    assets: [...selected.values()]};
}

module.exports = { render };
