'use strict';

// Render a verified local vhost include, or exercise it on a private Apache.
// No production config, running HTTP service, application bootstrap or DB.
const assert = require('node:assert/strict');
const { createHash } = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');

const template = path.join(__dirname, 'gramlyze-apache-precompressed.conf');
const sha256 = bytes => createHash('sha256').update(bytes).digest('hex');
const rx = string => string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

function apachePath(value) {
  const f = path.resolve(value).split(path.sep).join('/');
  // Apache expands '$', '%' and several backslash sequences in RewriteRules.
  // Refuse instead of guessing how two grammars would escape a new path.
  assert.match(f, /^(?:[A-Za-z]:)?\/[A-Za-z0-9_./ -]+$/, 'path not representable in this Apache fragment');
  return f;
}

function quote(str) {
  assert(!/[\r\n"]/.test(str), 'unsafe Apache argument');
  return '"' + str + '"';
}

function render({publicRoot, generationRoot}) {
  assert(path.isAbsolute(publicRoot) && path.isAbsolute(generationRoot), 'use absolute roots');
  const root = apachePath(fs.realpathSync.native(publicRoot));
  const generation = apachePath(fs.realpathSync.native(generationRoot));
  const {manifest, proof} = require('./precompress.cjs').verify(publicRoot, generationRoot);
  const entries = manifest.entries;
  const eligible = entries.filter(entry => entry.gzip !== null);
  assert(entries.length > 0 && eligible.length > 0, 'no verified gzip sidecars');
  for (const entry of entries) {
    assert.match(entry.source, /^(?:build\/assets\/[A-Za-z0-9_-]+\.(?:css|m?js)|js\/[A-Za-z0-9_-]+\.js|[A-Za-z0-9_-]+\.svg)$/);
    assert.equal(entry.url, '/' + entry.source);
    assert.match(entry.sha1, /^[a-f0-9]{40}$/);
    assert(Number.isSafeInteger(entry.bytes) && entry.bytes >= 0);
    if (entry.gzip !== null) {
      assert.equal(entry.gzip.path, 'assets/' + entry.source + '.gz');
      assert(Number.isSafeInteger(entry.gzip.bytes) && entry.gzip.bytes > 0 && entry.gzip.bytes < entry.bytes);
    }
  }
  const group = list => '(?:' + list.map(entry => rx(entry.url)).join('|') + ')';
  const selected = '^' + group(eligible) + '$';
  const authored = group(entries.filter(entry => !entry.source.startsWith('build/assets/')));
  const staticScope = entries.some(entry => !entry.source.startsWith('build/assets/'))
    ? '(?:/build/assets/.*|' + authored + ')' : '/build/assets/.*';
  const guards = [
    'RewriteRule "^/build/assets/.*\\.gz(?:/|$)" "-" [R=404,L,NC]',
    ...(authored === '(?:)' ? [] : ['RewriteRule "^' + authored + '\\.gz(?:/|$)" "-" [R=404,L,NC]']),
    'RewriteCond ' + quote(root + '$0') + ' !-f',
    'RewriteRule "^' + staticScope + '$" "-" [R=404,L]',
  ];

  // Use req_novary to avoid changing cache keys for nonmatches. The header
  // below explicitly describes the one negotiated static representation.
  const aw = "[[:blank:]]*";
  const weight = aw + ';' + aw + 'q' + aw + '=' + aw;
  const positive = String.raw`(?:1(?:[.]0{0,3})?|0[.](?=[0-9]{0,2}[1-9])[0-9]{1,3})`;
  const token = coding => '(?:^|,)' + aw + coding;
  const end = aw + '(?:,|$)';
  const acceptsGzip = token('gzip') + '(?:' + weight + positive + ')?' + end;
  const explicitZero = coding => token(coding) + weight + '0(?:[.]0*)?' + end;
  const acceptEncoding = "req_novary('Accept-Encoding')";
  const match = (operator, pattern) => acceptEncoding + ' ' + operator + ' m#' + pattern + '#i';

  const rules = [
    'RewriteCond expr ' + quote("%{REQUEST_METHOD} in { 'GET', 'HEAD' } && -z req_novary('Range') && " +
      match('=~', acceptsGzip) + ' && ' + match('!~', explicitZero('gzip'))),
    'RewriteRule "' + selected + '" "-" [E=PERF28_GZIP_OK:1]',
  ];
  for (const entry of eligible) {
    const identity = root + '/' + entry.source;
    const gzip = generation + '/' + entry.gzip.path;
    // The packing is independently verified via SHA-256 at activation. Apache
    // can only hash an ap_expr string, so it rechecks the NUL-free identity
    // text for drift; it does not claim to hash-check arbitrary binary gzip.
    const check = [
      "-f '" + identity + "'", "! -L '" + identity + "'", "filesize('" + identity + "') -eq " + entry.bytes,
      "-f '" + gzip + "'", "! -L '" + gzip + "'", "filesize('" + gzip + "') -eq " + entry.gzip.bytes,
      "sha1(file('" + identity + "')) == '" + entry.sha1 + "'",
    ];
    rules.push(
      'RewriteCond %{ENV:PERF28_GZIP_OK} =1',
      'RewriteCond expr ' + quote(check.join(' && ')),
      'RewriteRule "^' + rx(entry.url) + '$" ' + quote(gzip) + ' [END,E=no-gzip:1,E=no-brotli:1]',
    );
  }
  // No wildcard-based implicit opt-in to gzip. If it wasn't selected and an
  // eligible route explicitly forbids identity, don't send that representation.
  rules.push(
    'RewriteCond expr ' + quote("%{REQUEST_METHOD} in { 'GET', 'HEAD' } && (" +
      match('=~', explicitZero('identity')) + ' || (' + match('=~', explicitZero('[*]')) + ' && ' +
      match('!~', token('identity') + '(?:' + weight + positive + ')?' + end) + '))'),
    'RewriteRule "' + selected + '" "-" [R=406,L]',
  );
  const vary = [
    '<LocationMatch "' + selected + '">',
    '    Header merge Vary Accept-Encoding "expr=%{REQUEST_STATUS} != 304 && %{REQUEST_STATUS} != 406"',
    '    Header always merge Vary Accept-Encoding "expr=%{REQUEST_STATUS} == 304 || %{REQUEST_STATUS} == 406"',
    '</LocationMatch>',
  ];
  const values = {
    STATIC_GUARDS: guards.join('\n'), SELECT_GZIP: rules.join('\n'),
    PACKED_ROOT: generation + '/assets', VARY: vary.join('\n'),
  };
  const input = fs.readFileSync(template);
  const seen = new Set();
  const config = '# Verified manifest: ' + sha256(fs.readFileSync(path.join(generation, 'manifest.json'))) + '\n' +
    input.toString('utf8').replace(/@@([A-Z_]+)@@/g, (_, key) => {
      assert(Object.hasOwn(values, key) && !seen.has(key), 'unexpected template placeholder ' + key);
      seen.add(key);
      return values[key];
    });
  assert.equal(seen.size, Object.keys(values).length, 'missing template placeholder');
  return {config, manifest, proof, templateSha256: sha256(input), configSha256: sha256(config)};
}

module.exports = { render };

async function runContract() {
  const { spawn, spawnSync } = require('node:child_process');
  const { gunzipSync } = require('node:zlib');
  const { setTimeout: delay } = require('node:timers/promises');
  const { build } = require('./precompress.cjs');
  const http = require('node:http');
  const net = require('node:net');
  const os = require('node:os');
  const checkout = process.env.PERF28_APACHE_ROOT || 'C:/Program Files/xampp/apache';
  const apache = apachePath(fs.realpathSync.native(checkout));
  const exe = path.join(apache, 'bin', process.platform === 'win32' ? 'httpd.exe' : 'httpd');
  const scratch = fs.mkdtempSync(path.join(os.tmpdir(), 'gramlyze-perf28-contract-'));
  const publicRoot = path.join(scratch, 'www');
  const generationRoot = path.join(scratch, 'generation');
  const receipt = {schema: 'gramlyze-perf28-apache-contract-v1', rows: []};
  const fixtureDate = new Date('2025-01-01T01:02:03.000Z');
  const cssURL = '/build/assets/fixture-a.css';
  const jsURL = '/build/assets/fixture.js';
  const tinyURL = '/build/assets/tiny.css';
  const nextURL = '/build/assets/fixture-b.css';
  const svgURL = '/gramlyze-background.svg';
  const publicJS = '/js/theory-search.js';
  const fallback = Buffer.from('public fixture front controller marker');
  const fixtures = new Map([
    [cssURL, Buffer.from('/* fixture */ a { color: green; }\r\n'.repeat(120))],
    [jsURL, Buffer.from('const alpha = "Є і ї: checked UTF-8";\n'.repeat(200))],
    [tinyURL, Buffer.from('/* small fixture */')],
    [svgURL, Buffer.from('<svg xmlns="http://www.w3.org/2000/svg">' + '<g/>'.repeat(300) + '</svg>')],
    [publicJS, Buffer.from('const beta = "already-reviewed script";\n'.repeat(100))],
    ['/storage/unreviewed.css', Buffer.from('private download fixture\n'.repeat(100))],
    ['/front-fixture.txt', fallback],
  ]);
  const manifestFile = path.join(publicRoot, 'build', 'manifest.json');
  const write = (file, bytes, flag = 'wx') => {
    fs.mkdirSync(path.dirname(file), {recursive: true});
    fs.writeFileSync(file, bytes, {flag});
    fs.utimesSync(file, fixtureDate, fixtureDate);
  };
  const makeManifest = css => JSON.stringify({
    'resources/css/app.css': {file: css.slice('/build/'.length), src: 'resources/css/app.css', isEntry: true},
    'resources/js/app.js': {file: jsURL.slice('/build/'.length), src: 'resources/js/app.js', isEntry: true},
    'resources/css/catalog-public.css': {file: tinyURL.slice('/build/'.length), src: 'resources/css/catalog-public.css', isEntry: true},
  }) + '\n';
  for (const [url, bytes] of fixtures) write(path.join(publicRoot, url.slice(1)), bytes);
  write(manifestFile, makeManifest(cssURL));
  write(path.join(publicRoot, '.htaccess'), [
    'RewriteEngine On', 'RewriteCond %{REQUEST_FILENAME} !-f',
    'RewriteRule . /front-fixture.txt [L]', '',
  ].join('\n'));
  const quoteFile = f => quote(apachePath(f));
  let worker, closed;
  try {
    build(publicRoot, generationRoot);
    const rendered = render({publicRoot, generationRoot});
    const configFile = path.join(scratch, 'gzip-vhost.conf');
    const config = path.join(scratch, 'httpd.conf');
    write(configFile, rendered.config);
    receipt.configSha256 = rendered.configSha256;
    receipt.templateSha256 = rendered.templateSha256;
    receipt.manifestSha256 = rendered.proof.manifestSha256;
    const originalFile = path.join(publicRoot, cssURL.slice(1));
    const sidecarFile = path.join(generationRoot, 'assets', cssURL.slice(1) + '.gz');
    const original = fixtures.get(cssURL);
    const packed = fs.readFileSync(sidecarFile);
    const port = await new Promise((resolve, reject) => {
      const server = net.createServer();
      server.once('error', reject);
      server.listen(0, '127.0.0.1', () => {
        const p = server.address().port;
        server.close(err => err ? reject(err) : resolve(p));
      });
    });
    receipt.port = port;
    const modules = ['authz_core', 'mime', 'rewrite', 'headers', 'alias'];
    const serve = name => [
      '<VirtualHost 127.0.0.1:' + port + '>',
      '  ServerName ' + name,
      '  <Location "' + cssURL + '">',
      '    Header set Cache-Control "public, max-age=600"',
      '    Header always merge Vary X-Fixture',
      '  </Location>',
      ...(name === 'gramlyze.loc' ? ['  Include ' + quoteFile(configFile)] : []),
      '</VirtualHost>',
    ];
    write(config, [
      'ServerRoot ' + quoteFile(apache),
      'DefaultRuntimeDir ' + quoteFile(scratch),
      'Listen 127.0.0.1:' + port,
      'ServerName 127.0.0.1',
      'PidFile ' + quoteFile(path.join(scratch, 'httpd.pid')),
      'ErrorLog ' + quoteFile(path.join(scratch, 'error.log')),
      'LogLevel warn',
      'EnableSendfile Off',
      ...modules.map(name => 'LoadModule ' + name + '_module modules/mod_' + name + '.so'),
      'TypesConfig conf/mime.types',
      'DocumentRoot ' + quoteFile(publicRoot),
      '<Directory />', '  Require all denied', '</Directory>',
      '<Directory ' + quoteFile(publicRoot) + '>',
      '  AllowOverride FileInfo', '  Options +FollowSymLinks -Indexes -ExecCGI',
      '  Require all granted', '</Directory>',
      ...serve('perf28-other.invalid'), ...serve('gramlyze.loc'), '',
    ].join('\n'));
    const args = ['-d', apache, '-f', config];
    const syntax = spawnSync(exe, [...args, '-t'], {cwd: scratch, encoding: 'utf8', windowsHide: true, timeout: 8000});
    receipt.syntax = (syntax.error || syntax.stderr || syntax.stdout || 'no syntax result').toString().trim();
    assert.equal(syntax.status, 0, receipt.syntax);
    worker = spawn(exe, [...args, '-X'], {cwd: scratch, stdio: 'ignore', windowsHide: true});
    let startError;
    worker.once('error', error => { startError = error; });
    closed = new Promise(resolve => worker.once('close', (code, signal) => resolve({code, signal})));

    const get = (name, url = cssURL, {method = 'GET', host = 'gramlyze.loc', headers = {}} = {}) =>
      new Promise((resolve, reject) => {
        const req = http.request({hostname: '127.0.0.1', port, method, path: url, agent: false, headers: {Host: host, ...headers}}, res => {
          const chunks = [];
          let count = 0;
          res.on('data', chunk => {
            count += chunk.length;
            if (count > 1024 * 1024) req.destroy(new Error('contract response exceeds byte cap'));
            else chunks.push(chunk);
          });
          res.on('error', reject);
          res.on('end', () => {
            if (!res.complete) return reject(new Error('incomplete response'));
            try {
              const bytes = Buffer.concat(chunks);
              const lower = res.headers;
              const encoding = lower['content-encoding'] || 'identity';
              const body = bytes.length && encoding === 'gzip' ? gunzipSync(bytes, {maxOutputLength: 1024 * 1024}) : bytes;
              receipt.rows.push({name, url, method, host, status: res.statusCode, encoding, encodedBytes: bytes.length,
                decodedBytes: body.length, decodedSha256: sha256(body), mime: lower['content-type'] || null,
                vary: lower.vary || null, etag: lower.etag || null, modified: lower['last-modified'] || null,
                length: lower['content-length'] || null, cache: lower['cache-control'] || null});
              resolve({status: res.statusCode, encoding, bytes, body, lower});
            } catch (error) { reject(error); }
          });
        });
        const timer = setTimeout(() => req.destroy(new Error('private HTTP deadline')), 7000);
        req.on('error', reject);
        req.on('close', () => clearTimeout(timer));
        req.end();
      });
    for (let attempt = 0; ; attempt++) {
      if (startError) throw startError;
      if (worker.exitCode !== null || attempt >= 30) throw new Error('private Apache unavailable');
      try {
        assert.equal((await get('startup fixture', '/front-fixture.txt', {host: 'perf28-other.invalid'})).status, 200);
        break;
      } catch (error) {
        if (error.code !== 'ECONNREFUSED') throw error;
        await delay(100);
      }
    }

    const G = {'Accept-Encoding': 'gzip'};
    const I = {'Accept-Encoding': 'identity'};
    const vary = res => assert((res.lower.vary || '').toLowerCase().split(',').some(s => s.trim() === 'accept-encoding'));
    const body = (res, decoded, encoding = 'identity', status = 200) => {
      assert.equal(res.status, status);
      assert.equal(res.encoding, encoding);
      assert.deepEqual(res.body, decoded);
      assert.equal(Number(res.lower['content-length']), res.bytes.length);
      assert.equal(res.lower['transfer-encoding'], undefined);
    };
    const control = await get('other vhost unchanged', cssURL, {host: 'perf28-other.invalid', headers: G});
    body(control, original);
    assert(!control.lower.vary.toLowerCase().includes('accept-encoding'));
    const id = await get('identity', cssURL, {headers: I});
    body(id, original); vary(id);
    const gz = await get('gzip selection', cssURL, {headers: G});
    body(gz, original, 'gzip'); vary(gz);
    assert.deepEqual(gz.bytes, packed, 'same encoded bytes as on disk');
    assert.match(id.lower.etag, /^"[^"]+"$/);
    assert.match(gz.lower.etag, /^"[^"]+"$/);
    assert.notEqual(gz.lower.etag, id.lower.etag);
    assert.equal(id.lower.etag, control.lower.etag);
    for (const v of [id, gz]) {
      assert.equal(v.lower['last-modified'], control.lower['last-modified']);
      assert.equal(v.lower['cache-control'], control.lower['cache-control']);
      assert.equal(v.lower['content-type'], 'text/css');
      assert(v.lower.vary.toLowerCase().includes('x-fixture'));
    }
    for (const [offer, enc] of [
      ['', 'identity'], ['br', 'identity'], ['*', 'identity'], ['gzip;q=0', 'identity'],
      ['br, GZip ; Q = 0.000, identity', 'identity'], ['gzip, gzip;q=0', 'identity'],
      ['gzip;q=abc', 'identity'], ['br, GZip ; Q = 0.001', 'gzip'], ['gzip;q=1., br', 'gzip'],
      ['identity;q=0, gzip;q=0.8', 'gzip'],
    ]) {
      body(await get('negotiation ' + (offer || 'absent'), cssURL, {headers: offer ? {'Accept-Encoding': offer} : {}}), original, enc);
    }
    const unsup = await get('explicit no acceptable representation', cssURL, {headers: {'Accept-Encoding': 'gzip;q=0, identity;q=0'}});
    assert.equal(unsup.status, 406); assert.equal(unsup.encoding, 'identity'); vary(unsup);
    for (const [item, headers, offer] of [[id, I, 'identity'], [gz, G, 'gzip']]) {
      const head = await get('HEAD ' + offer, cssURL, {method: 'HEAD', headers});
      assert.equal(head.status, 200); assert.equal(head.bytes.length, 0); assert.equal(head.encoding, offer);
      assert.equal(head.lower['content-length'], item.lower['content-length']);
      assert.equal(head.lower.etag, item.lower.etag); vary(head);
      for (const [key, value] of [['If-None-Match', item.lower.etag], ['If-Modified-Since', item.lower['last-modified']]]) {
        const notModified = await get('304 ' + offer + ' ' + key, cssURL, {headers: {...headers, [key]: value}});
        assert.equal(notModified.status, 304); assert.equal(notModified.bytes.length, 0);
        if (notModified.lower['content-length']) assert.equal(notModified.lower['content-length'], head.lower['content-length']);
        assert.equal(notModified.lower.etag, item.lower.etag); vary(notModified);
      }
    }
    body(await get('identity etag cannot revalidate gzip', cssURL, {headers: {...G, 'If-None-Match': id.lower.etag}}), original, 'gzip');
    body(await get('gzip etag cannot revalidate identity', cssURL, {headers: {...I, 'If-None-Match': gz.lower.etag}}), original);
    const bytes16 = { ...G, Range: 'bytes=0-15' };
    const partial = await get('range is always identity', cssURL, {headers: bytes16});
    body(partial, original.subarray(0, 16), 'identity', 206); vary(partial);
    assert.equal(partial.lower['content-range'], 'bytes 0-15/' + original.length);
    body(await get('If-Range identity matches', cssURL, {headers: {...bytes16, 'If-Range': id.lower.etag}}),
      original.subarray(0, 16), 'identity', 206);
    body(await get('If-Range gzip never validates identity', cssURL, {headers: {...bytes16, 'If-Range': gz.lower.etag}}), original);
    for (const u of [jsURL, svgURL, publicJS]) {
      const i = await get('type control', u, {headers: I});
      const g = await get('compressed static type', u, {headers: G});
      body(i, fixtures.get(u));
      body(g, fixtures.get(u), 'gzip'); vary(g);
      assert.ok(['text/javascript', 'image/svg+xml'].includes(i.lower['content-type']));
      assert.equal(g.lower['content-type'], i.lower['content-type']);
    }
    for (const u of [tinyURL, '/storage/unreviewed.css']) {
      body(await get('excluded static', u, {headers: G}), fixtures.get(u));
    }
    body(await get('dynamic routes unchanged', '/api/progress-fixture', {headers: G}), fallback);
    for (const u of [cssURL + '.gz', svgURL + '.gz', publicJS + '.gz', '/build/assets/unknown.css']) {
      const reply = await get('static 404 without fallback', u, {headers: G});
      assert.equal(reply.status, 404); assert.equal(reply.encoding, 'identity');
      assert(!reply.body.equals(fallback));
    }

    // Runtime's stale-text guard must not depend on one stat field; preserve
    // source byte count and exact fixture mtime while changing original bytes.
    const mutated = Buffer.from(original);
    mutated[12] = mutated[12] === 97 ? 98 : 97;
    write(originalFile, mutated, 'w');
    assert.throws(() => render({publicRoot, generationRoot}), /stale|manifest/i);
    body(await get('same-byte-count mtime-original drift', cssURL, {headers: G}), mutated);
    write(originalFile, original, 'w');

    fs.renameSync(sidecarFile, sidecarFile + '.parked');
    assert.throws(() => render({publicRoot, generationRoot}));
    body(await get('missing sidecar fallback', cssURL, {headers: G}), original);
    fs.renameSync(sidecarFile + '.parked', sidecarFile);
    write(sidecarFile, packed.subarray(0, packed.length - 1), 'w');
    assert.throws(() => render({publicRoot, generationRoot}));
    body(await get('truncated sidecar fallback', cssURL, {headers: G}), original);
    const corrupt = Buffer.from(packed);
    corrupt[corrupt.length - 8] ^= 1;
    write(sidecarFile, corrupt, 'w');
    assert.throws(() => render({publicRoot, generationRoot}));
    // Do not serve an in-place same-size corruption: the release verifier
    // rejects it, and the live tree must be treated as immutable.
    write(sidecarFile, packed, 'w');
    fs.renameSync(originalFile, originalFile + '.parked');
    const removed = await get('missing source cannot use gzip', cssURL, {headers: G});
    assert.equal(removed.status, 404); assert.equal(removed.encoding, 'identity');
    fs.renameSync(originalFile + '.parked', originalFile);
    assert.equal(render({publicRoot, generationRoot}).configSha256, rendered.configSha256);

    write(path.join(publicRoot, nextURL.slice(1)), mutated);
    write(manifestFile, makeManifest(nextURL), 'w');
    assert.throws(() => render({publicRoot, generationRoot}), /stale|manifest|generation/i);
    body(await get('new manifest asset uses identity until activation', nextURL, {headers: G}), mutated);
    const next = path.join(scratch, 'next');
    build(publicRoot, next);
    const updated = render({publicRoot, generationRoot: next});
    assert(!updated.manifest.entries.some(x => x.url === cssURL));
    assert(updated.manifest.entries.some(x => x.url === nextURL && x.gzip));
    assert.notEqual(updated.configSha256, rendered.configSha256);
    receipt.result = 'PASS';
  } catch (error) {
    receipt.result = 'FAIL';
    receipt.error = error.stack || error.message;
    process.exitCode = 1;
  } finally {
    if (worker) {
      worker.kill();
      receipt.closed = await closed;
    }
    const destination = path.join(scratch, 'receipt.json');
    fs.writeFileSync(destination, JSON.stringify(receipt, null, 2) + '\n', {flag: 'wx'});
    console.log(JSON.stringify({result: receipt.result, checks: receipt.rows.length, receipt: destination, error: receipt.error || null}));
  }
}

if (require.main === module) {
  const args = process.argv.slice(2);
  if (args.length === 0) runContract().catch(error => {
    console.error(error.message);
    process.exitCode = 1;
  });
  else if (args.length === 7 && args[0] === 'render' && args[1] === '--public' &&
      args[3] === '--generation' && args[5] === '--out') {
    try {
      const {config, configSha256, templateSha256} = render({publicRoot: args[2], generationRoot: args[4]});
      assert(path.isAbsolute(args[6]), 'use an absolute output path');
      fs.writeFileSync(args[6], config, {flag: 'wx'});
      console.log(JSON.stringify({file: args[6], configSha256, templateSha256}));
    } catch (error) {
      console.error(error.message);
      process.exitCode = 1;
    }
  } else {
    console.error('Usage: node precompressed-contract.cjs [render --public ABS --generation ABS --out ABS]');
    process.exitCode = 1;
  }
}
