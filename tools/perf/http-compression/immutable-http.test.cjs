'use strict';

const assert = require('node:assert/strict');
const { randomUUID, createHash } = require('node:crypto');
const { spawnSync, spawn } = require('node:child_process');
const fs = require('node:fs');
const http = require('node:http');
const net = require('node:net');
const path = require('node:path');
const { setTimeout: delay } = require('node:timers/promises');
const { gunzipSync } = require('node:zlib');
const { render } = require('./immutable-delivery.cjs');
const { prepare, stage, verify } = require('./immutable-release.cjs');

const CSS = '/build/assets/app-B49X7Qf9.css';
const OLD = '/build/assets/app-F9oL6Pq8.css';
const JS = '/build/assets/app-C0mM9I9L.js';
const TINY = '/build/assets/small-Y0pM3j2Q.css';
const SVG = '/gramlyze-background.svg';
const NAMED_JS = '/js/theory-search.js';
const FALLBACK = '/synthetic.html';
const fixtureData = new Map([
  [CSS, Buffer.from('/* synthetic active */ a { color: green; }\n'.repeat(125))],
  [OLD, Buffer.from('/* synthetic retained */ a { color: orange; }\n'.repeat(140))],
  [JS, Buffer.from('const opaque = "Є і ї: fixture";\n'.repeat(150))],
  [TINY, Buffer.from('/* tiny identity */')],
  [SVG, Buffer.from('<svg xmlns="http://www.w3.org/2000/svg">' + '<g/>'.repeat(300) + '</svg>')],
  [NAMED_JS, Buffer.from('const stable = "compatible public name";\n'.repeat(130))],
  [FALLBACK, Buffer.from('<!doctype html><p>not a static test asset</p>')],
]);
const hash = bytes => createHash('sha256').update(bytes).digest('hex');
const isoDate = new Date('2025-01-01T01:02:03.000Z');
const q = input => {
  const escaped = input.replaceAll('\\', '/');
  assert.match(escaped, /^[A-Za-z]:\/[A-Za-z0-9_./ -]+$/);
  return '"' + escaped + '"';
};

function write(file, data, flags = 'wx') {
  fs.mkdirSync(path.dirname(file), {recursive: true});
  fs.writeFileSync(file, data, {flag: flags});
  fs.utimesSync(file, isoDate, isoDate);
}

function prepareFixture({baseDir, sealRoot}) {
  assert(path.isAbsolute(baseDir) && path.isAbsolute(sealRoot));
  const scratch = fs.mkdtempSync(path.join(baseDir, 'immutable-http-'));
  const releaseRoot = path.join(sealRoot, 'h-' + randomUUID().slice(0, 8));
  const currentPublic = path.join(scratch, 'www');
  const retainedPublic = path.join(scratch, 'retained');
  const conflictPublic = path.join(scratch, 'conflict');
  fs.mkdirSync(releaseRoot);
  const createGeneration = (publicRoot, css, {collision = false, time = isoDate} = {}) => {
    for (const asset of [css, JS, TINY, SVG, NAMED_JS, FALLBACK]) {
      const file = path.join(publicRoot, asset.slice(1));
      const contents = collision && asset === css ? Buffer.from('/* colliding fingerprint */'.repeat(150)) : fixtureData.get(asset);
      write(file, contents);
      fs.utimesSync(file, time, time);
    }
    write(path.join(publicRoot, 'build', 'manifest.json'), JSON.stringify({
      'resources/css/app.css': {file: css.slice('/build/'.length), isEntry: true},
      'resources/css/catalog-public.css': {file: TINY.slice('/build/'.length), isEntry: true},
      'resources/js/app.js': {file: JS.slice('/build/'.length), isEntry: true},
    }));
    // The builder already rejects different bytes under a known URL in the
    // same release root. Create a separate valid vault subtree to verify the
    // renderer also refuses a conflicting pair of independent release roots.
    const buildRoot = collision ? path.join(releaseRoot, 'c') : releaseRoot;
    if (collision) fs.mkdirSync(buildRoot);
    const release = prepare({releaseRoot: buildRoot, publicRoot, staged: stage({publicRoot, releaseRoot: buildRoot})});
    return {generation: release.generation, expectedDigest: release.manifestSha256, sealRoot};
  };
  const active = createGeneration(currentPublic, CSS);
  const old = createGeneration(retainedPublic, OLD, {time: new Date('2024-01-01T01:02:03.000Z')});
  const conflict = createGeneration(conflictPublic, CSS, {collision: true});
  write(path.join(currentPublic, '.htaccess'), [
    'RewriteEngine On', 'RewriteCond %{REQUEST_FILENAME} !-f', 'RewriteRule . ' + FALLBACK + ' [L]', '',
  ].join('\n'));
  const fixtureFile = path.join(scratch, 'fixture.json');
  write(fixtureFile, JSON.stringify({scratch, publicRoot: currentPublic, generations: [active, old], conflict}, null, 2) + '\n');
  return fixtureFile;
}

function loopbackPort() {
  return new Promise((resolve, reject) => {
    const server = net.createServer();
    server.once('error', reject);
    server.listen(0, '127.0.0.1', () => {
      const port = server.address().port;
      server.close(err => err ? reject(err) : resolve(port));
    });
  });
}

async function runContract({scratch, publicRoot, generations, conflict}, apacheInput = 'C:/Program Files/xampp/apache') {
  const receipt = {schema: 'gramlyze-perf29-apache-contract-v1', rows: []};
  const receiptFile = path.join(scratch, 'http-contract-' + randomUUID() + '.json');
  const publicFile = path.join(publicRoot, CSS.slice(1));
  const original = fixtureData.get(CSS);
  // Refuse to use any public root which isn't the exclusive synthetic fixture.
  assert.equal(fs.realpathSync.native(publicRoot), fs.realpathSync.native(path.join(scratch, 'www')));
  assert(path.basename(scratch).startsWith('immutable-http-'));
  assert.deepEqual(fs.readFileSync(publicFile), original);
  assert(!fs.existsSync(path.join(publicRoot, OLD.slice(1))), 'retained fixture must not be in DocumentRoot');
  let apacheWorker;
  let mutated = false;
  let result;
  try {
    const d = render({publicRoot, generations});
    receipt.configSha256 = d.configSha256;
    receipt.generations = d.receipts.map(({manifestSha256, viteManifestSha256}) => ({manifestSha256, viteManifestSha256}));
    if (conflict) {
      assert.throws(() => render({publicRoot, generations: [generations[0], conflict]}), /conflicting fingerprints/);
      receipt.conflictingFingerprintRejected = true;
    }
    const generation = verify({generation: generations[0].generation, expectedDigest: generations[0].expectedDigest});
    const compressed = generation.manifest.assets.find(value => value.url === CSS).gzip;
    const port = await loopbackPort();
    const apacheRoot = fs.realpathSync.native(apacheInput);
    const exe = path.join(apacheRoot, 'bin', 'httpd.exe');
    const cfg = path.join(scratch, 'httpd-' + port + '.conf');
    const inc = path.join(scratch, 'immutable-' + port + '.conf');
    write(inc, d.config);
    const site = name => [`<VirtualHost 127.0.0.1:${port}>`, 'ServerName ' + name,
      '<Location ' + CSS + '>', 'Header set Cache-Control "public, max-age=600"',
      'Header always merge Vary X-Fixture', '</Location>', ...(name === 'gramlyze.loc' ? ['Include ' + q(inc)] : []),
      '</VirtualHost>'];
    write(cfg, ['ServerRoot ' + q(apacheRoot), 'DefaultRuntimeDir ' + q(scratch), 'ServerName 127.0.0.1',
      'Listen 127.0.0.1:' + port, 'PidFile ' + q(path.join(scratch, 'httpd-' + port + '.pid')),
      'ErrorLog ' + q(path.join(scratch, 'error-' + port + '.log')), 'LogLevel warn', 'EnableSendfile Off',
      ...['authz_core', 'mime', 'rewrite', 'headers'].map(name => 'LoadModule ' + name + '_module modules/mod_' + name + '.so'),
      'TypesConfig conf/mime.types', 'DocumentRoot ' + q(publicRoot),
      '<Directory />', 'Require all denied', '</Directory>',
      '<Directory ' + q(publicRoot) + '>', 'AllowOverride FileInfo', 'Options +FollowSymLinks -Indexes -ExecCGI',
      'Require all granted', '</Directory>', ...site('control.invalid'), ...site('gramlyze.loc'), ''].join('\n'));
    const params = ['-d', apacheRoot, '-f', cfg];
    const options = {cwd: scratch, windowsHide: true};
    const syntax = spawnSync(exe, [...params, '-t'], {...options, encoding: 'utf8', timeout: 8000});
    receipt.syntax = String(syntax.error || syntax.stderr || syntax.stdout || '').trim();
    assert.equal(syntax.status, 0, receipt.syntax);
    assert.equal(hash(fs.readFileSync(inc)), d.configSha256, 'verified include changed before launch');
    apacheWorker = spawn(exe, [...params, '-X'], {...options, stdio: 'ignore'});
    let launchError, stopped;
    apacheWorker.on('error', err => { launchError = err; });
    stopped = new Promise(resolve => apacheWorker.once('close', resolve));
    const get = (label, url = CSS, headers = {}, method = 'GET', host = 'gramlyze.loc') =>
      new Promise((resolve, reject) => {
        const req = http.request({host: '127.0.0.1', port, method, path: url,
          agent: false, headers: {Host: host, ...headers}}, res => {
          const chunks = [];
          let received = 0;
          res.on('data', buf => {
            received += buf.length;
            if (received > 1024 * 1024) req.destroy(new Error('fixture byte cap'));
            else chunks.push(buf);
          });
          res.on('error', reject);
          res.on('end', () => {
            if (!res.complete) return reject(new Error('fixture response incomplete'));
            const body = Buffer.concat(chunks);
            const h = res.headers;
            const row = {label, url, host, method, status: res.statusCode, encoding: h['content-encoding'] || 'identity',
              bytes: received, sha256: hash(body), vary: h.vary, etag: h.etag, modified: h['last-modified'],
              contentType: h['content-type'], range: h['content-range'], cache: h['cache-control']};
            receipt.rows.push(row);
            resolve({...row, h, body});
          });
        });
        const timer = setTimeout(() => req.destroy(new Error('isolated Apache request deadline')), 6000);
        req.on('error', reject);
        req.on('close', () => clearTimeout(timer));
        req.end();
      });
    try {
      if (!Number.isInteger(apacheWorker.pid)) {
        const failure = await stopped;
        throw launchError || new Error('Apache launcher did not produce an isolated PID: ' + failure);
      }
      for (const root of new Set(generations.map(g => g.sealRoot))) {
        const verifier = path.join(__dirname, 'seal-release.ps1');
        const checked = spawnSync('C:/Program Files/PowerShell/7/pwsh.exe', ['-NoProfile', '-NonInteractive', '-File', verifier,
          '-Mode', 'Verify', '-ReleaseRoot', root, '-ApachePid', String(apacheWorker.pid)],
        {...options, encoding: 'utf8', timeout: 60_000});
        assert.equal(checked.status, 0, String(checked.error || checked.stderr || 'Apache token verification failed'));
        const workerSeal = JSON.parse(checked.stdout);
        assert.equal(workerSeal.immutable, true);
        assert.equal(workerSeal.apachePid, apacheWorker.pid);
        assert.match(workerSeal.apacheIntegritySid, /^S-1-16-\d+$/);
        receipt.apacheWorkerProtected = true;
      }
      for (let attempt = 0; ; attempt++) {
        if (launchError) throw launchError;
        if (apacheWorker.exitCode !== null || attempt === 30) throw new Error('isolated Apache unavailable');
        try {
          assert.equal((await get('ready', FALLBACK, {}, 'GET', 'control.invalid')).status, 200);
          break;
        } catch (error) {
          if (error.code !== 'ECONNREFUSED') throw error;
          await delay(100);
        }
      }
      const enc = (coding, extra = {}) => ({'Accept-Encoding': coding, ...extra});
      const vary = res => assert((res.vary || '').toLowerCase().split(',').some(s => s.trim() === 'accept-encoding'));
      const full = (res, expected, encoding) => {
        assert.equal(res.status, 200);
        assert.equal(res.encoding, encoding);
        assert.equal(Number(res.h['content-length']), res.bytes);
        assert.deepEqual(encoding === 'gzip' ? gunzipSync(res.body, {maxOutputLength: 1_048_576}) : res.body, expected);
      };
      const id = await get('identity', CSS);
      const gz = await get('gzip', CSS, enc('gzip'));
      full(id, original, 'identity'); full(gz, original, 'gzip');
      assert.equal(gz.sha256, compressed.sha256, 'no re-compression or double gzip');
      assert.equal(gz.bytes, compressed.bytes);
      assert.notEqual(id.etag, gz.etag);
      for (const res of [id, gz]) {
        vary(res);
        assert.match(res.etag, /^"[^"]+"$/);
        assert.equal(res.modified, isoDate.toUTCString());
        assert.equal(res.contentType, 'text/css');
        assert.equal(res.cache, 'public, max-age=600');
        assert(res.vary.toLowerCase().includes('x-fixture'));
      }
      for (const [offer, expected] of [
        ['identity', 'identity'], ['*', 'gzip'], ['br, *;q=0.8, identity;q=0', 'gzip'],
        ['*;q=1, gzip;q=0', 'identity'], ['gzip, gzip;q=0', 'identity'],
        ['gzip;q=0.2, identity;q=0.8', 'identity'], ['identity;q=0.8, gzip;q=0.2', 'identity'],
        ['gzip;q=0.900, identity;q=0.2', 'gzip'], ['identity, gzip;q=0.2', 'identity'],
        ['*;q=0.001, identity;q=0.002', 'identity'], ['br, gzip;q=invalid', 'identity'],
        ['identity;q=0.5, *;q=0', 'identity'], ['gzip;q=1.000, identity;q=0.990', 'gzip'],
      ]) full(await get('negotiate ' + offer, CSS, enc(offer)), original, expected);
      for (const offer of ['gzip;q=0, identity;q=0', '*;q=0', 'gzip;q=0, *;q=1, identity;q=0']) {
        const rejected = await get('406 ' + offer, CSS, enc(offer));
        assert.equal(rejected.status, 406); vary(rejected);
      }
      for (const res of [id, gz]) {
        const head = await get('HEAD ' + res.encoding, CSS, enc(res.encoding), 'HEAD');
        assert.equal(head.status, 200); assert.equal(head.bytes, 0); vary(head);
        assert.equal(head.encoding, res.encoding); assert.equal(head.etag, res.etag);
        assert.equal(head.h['content-length'], res.h['content-length']);
        for (const [key, value] of [['If-None-Match', res.etag], ['If-Modified-Since', res.modified]]) {
          const notModified = await get('304 ' + res.encoding + ' ' + key, CSS, enc(res.encoding, {[key]: value}));
          assert.equal(notModified.status, 304); assert.equal(notModified.bytes, 0); vary(notModified);
          assert.equal(notModified.etag, res.etag);
        }
      }
      full(await get('other representation is not revalidated', CSS, enc('gzip', {'If-None-Match': id.etag})), original, 'gzip');
      const range = enc('gzip', {Range: 'bytes=0-15'});
      const partial = await get('identity range', CSS, range);
      assert.equal(partial.status, 206); assert.equal(partial.encoding, 'identity'); vary(partial);
      assert.equal(partial.range, 'bytes 0-15/' + original.length);
      assert.deepEqual(partial.body, original.subarray(0, 16));
      const partialGzip = await get('gzip-only range', CSS, enc('gzip,identity;q=0', {Range: 'bytes=0-15'}));
      assert.equal(partialGzip.status, 206); assert.equal(partialGzip.encoding, 'gzip'); vary(partialGzip);
      assert.equal(partialGzip.range, 'bytes 0-15/' + gz.bytes);
      assert.deepEqual(partialGzip.body, gz.body.subarray(0, 16));
      assert.equal((await get('range has no supported encoding', CSS, enc('gzip;q=0, identity;q=0', {Range: 'bytes=0-15'}))).status, 406);
      assert.equal((await get('If-Range identity matches', CSS, {...range, 'If-Range': id.etag})).status, 206);
      full(await get('If-Range gzip is not an identity validator', CSS, {...range, 'If-Range': gz.etag}), original, 'identity');
      const js = await get('gzip JS MIME', JS, enc('gzip'));
      full(js, fixtureData.get(JS), 'gzip'); assert.equal(js.contentType, 'text/javascript');
      assert.equal(js.modified, isoDate.toUTCString(), 'current metadata wins for identical retained bytes');
      full(await get('old generation served without mutable original', OLD, enc('gzip')), fixtureData.get(OLD), 'gzip');
      full(await get('tiny asset identity fallback', TINY, enc('gzip')), fixtureData.get(TINY), 'identity');
      for (const url of [SVG, NAMED_JS, FALLBACK, '/livewire/synthetic']) {
        const res = await get('unmapped ' + url, url, enc('gzip'));
        full(res, fixtureData.get(url) || fixtureData.get(FALLBACK), 'identity');
        assert(!res.vary?.toLowerCase().includes('accept-encoding'));
      }
      full(await get('unmodified second vhost', CSS, enc('gzip'), 'GET', 'control.invalid'), original, 'identity');
      for (const url of [CSS + '.gz', OLD + '.gz', '/build/assets/never-listed-Cc43dAD3.css']) {
        const res = await get('guard ' + url, url, enc('gzip'));
        assert.equal(res.status, 404); assert(!res.body.equals(fixtureData.get(FALLBACK)));
      }
      // A writable source tree is not part of the generation trust boundary.
      // Changing an isolated public fixture must not affect either D variant.
      const other = Buffer.from(original); other[0] = 88;
      write(publicFile, other, 'w'); mutated = true;
      full(await get('sealed identity ignores changed live source'), original, 'identity');
      full(await get('sealed gzip ignores changed live source', CSS, enc('gzip')), original, 'gzip');
      full(await get('second vhost still uses mutable source', CSS, enc('gzip'), 'GET', 'control.invalid'), other, 'identity');
      result = {status: 'PASS', receiptFile, requests: receipt.rows.length};
    } finally {
      apacheWorker.kill();
      await Promise.race([stopped, delay(2000)]);
    }
  } catch (error) {
    receipt.error = String(error.stack || error);
    result = {status: 'NOT PASSED', receiptFile, requests: receipt.rows.length, error: error.message};
  } finally {
    if (mutated) write(publicFile, original, 'w');
    write(receiptFile, JSON.stringify({...receipt, result}, null, 2) + '\n');
  }
  return result;
}

if (require.main === module && process.argv[2] === 'prepare') {
  // Intentionally requires a separately created and explicitly sealed vault.
  console.log(prepareFixture({baseDir: process.argv[3], sealRoot: process.argv[4]}));
} else if (require.main === module && process.argv[2] === 'run') {
  runContract(JSON.parse(fs.readFileSync(process.argv[3], 'utf8')), process.argv[4])
    .then(result => { console.log(JSON.stringify(result)); if (result.status !== 'PASS') process.exitCode = 1; })
    .catch(error => { console.error(error.message); process.exitCode = 1; });
} else {
  const { test } = require('node:test');
  test('D rejects missing pins and unsupported scope before reading or HTTP', () => {
    assert.throws(() => render({generations: [{expectedDigest: 'not-pinned'}]}), /manifest SHA-256 pin/);
    assert.throws(() => render({scope: 'any-path', generations: []}), /invalid delivery scope/);
  });
  test('D refuses a normally writable NTFS work directory even with a real pinned release', {skip: process.platform !== 'win32'}, () => {
    const logs = path.resolve(__dirname, '../../../storage/logs');
    fs.mkdirSync(logs, {recursive: true});
    const scratch = fs.mkdtempSync(path.join(logs, 'g29-'));
    const vault = fs.mkdtempSync(path.join(scratch, 'u-'));
    const fixture = JSON.parse(fs.readFileSync(prepareFixture({baseDir: scratch, sealRoot: vault}), 'utf8'));
    assert.throws(() => render(fixture), /NTFS generation seal not proven/);
  });
}

module.exports = { prepareFixture, runContract };
