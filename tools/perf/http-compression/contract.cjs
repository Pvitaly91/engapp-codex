'use strict';

// Local Apache contract only. No application, database, Apache service,
// elevated installer, remote host, or personal session is used.
const assert = require('node:assert/strict');
const { spawn, spawnSync } = require('node:child_process');
const { createHash } = require('node:crypto');
const fs = require('node:fs');
const http = require('node:http');
const net = require('node:net');
const os = require('node:os');
const path = require('node:path');
const { performance } = require('node:perf_hooks');
const { setTimeout: sleep } = require('node:timers/promises');
const { gunzipSync, gzipSync } = require('node:zlib');

const root = fs.realpathSync(process.env.PERF27_APACHE_ROOT || 'C:/Program Files/xampp/apache');
const httpd = path.join(root, 'bin', process.platform === 'win32' ? 'httpd.exe' : 'httpd');
const include = path.join(__dirname, 'gramlyze-apache-gzip.conf');
const scratch = fs.mkdtempSync(path.join(os.tmpdir(), 'gramlyze-perf27-contract-'));
const docroot = path.join(scratch, 'www');
const conf = path.join(scratch, 'httpd.conf');
const receiptPath = path.join(scratch, 'receipt.json');
const diagnose = process.argv.includes('--diagnose');
const sha = bytes => createHash('sha256').update(bytes).digest('hex');
const quote = file => JSON.stringify(file.replaceAll('\\', '/'));
const content = text => Buffer.from(text, 'utf8');

const sample = content('a { color: green; }\n'.repeat(120));
const large = content(Array.from({ length: 600 }, (_, i) => (
  '/* ' + sha('gramlyze-perf27-static-fixture:' + i) + ' */\n'
)).join(''));
const preencoded = gzipSync(sample);
const tiny = content('/*' + 'x'.repeat(1019) + '*/');
const minimum = content('/*' + 'x'.repeat(1020) + '*/');
const shaAtStart = sha(fs.readFileSync(include));
const fixture = {
  '/build/assets/large-fixture.css': large,
  '/build/assets/static-fixture.css': sample,
  '/build/assets/wrong-type-fixture.css': sample,
  '/build/assets/preencoded-fixture.js': preencoded,
  '/build/assets/large-fixture.js': content('const fixture = "public";\n'.repeat(120)),
  '/build/assets/min-fixture.css': minimum,
  '/build/assets/tiny-fixture.css': tiny,
  '/gramlyze-background.svg': content('<svg xmlns="http://www.w3.org/2000/svg">' + '<g/>'.repeat(300) + '</svg>'),
  '/robots.txt': content('User-agent: *\nDisallow:\n'),
  '/build/assets/test.zip': content('not a valid zip, intentional MIME exclusion control'.repeat(100)),
  '/api/private-fixture.json': content(JSON.stringify({ progress: Array(100).fill('fixture') })),
  '/test/private-fixture.html': content('<html><title>Test fixture</title>' + '<p>PRIVATE FIXTURE</p>'.repeat(100) + '</html>'),
  '/storage/unreviewed-fixture.css': sample,
  '/unreviewed-fixture.css': sample,
};
for (const [url, bytes] of Object.entries(fixture)) {
  const file = path.join(docroot, url.slice(1));
  fs.mkdirSync(path.dirname(file), { recursive: true });
  fs.writeFileSync(file, bytes, { flag: 'wx' });
  fs.utimesSync(file, new Date('2025-01-01T00:00:00Z'), new Date('2025-01-01T00:00:00Z'));
}

const receipt = { schema: 'gramlyze-perf27-apache-contract-v1', includeSha256: shaAtStart, rows: [] };
function choosePort() {
  return new Promise((resolve, reject) => {
    const probe = net.createServer();
    probe.once('error', reject);
    probe.listen(0, '127.0.0.1', () => {
      const { port } = probe.address();
      probe.close(error => error ? reject(error) : resolve(port));
    });
  });
}

function writeConfig(port) {
  const fromInstall = ['authz_core', 'mime', 'headers', 'filter', 'deflate']
    .map(name => 'LoadModule ' + name + '_module modules/mod_' + name + '.so');
  const lines = [
    'ServerRoot ' + quote(root), 'ServerName 127.0.0.1',
    'Listen 127.0.0.1:' + port,
    'PidFile ' + quote(path.join(scratch, 'httpd.pid')),
    'ErrorLog ' + quote(path.join(scratch, 'apache-error.log')),
    'LogLevel warn', 'EnableSendfile Off',
    ...fromInstall, 'TypesConfig conf/mime.types',
    'DocumentRoot ' + quote(docroot), '<Directory ' + quote(docroot) + '>',
    '  AllowOverride None', '  Options -Indexes -FollowSymLinks -ExecCGI',
    '  Require all granted', '</Directory>',
    '<Files "preencoded-fixture.js">', '  AddEncoding gzip .js', '</Files>',
    '<Files "static-fixture.css">',
    '  Header set Cache-Control "public, max-age=600"',
    '  Header always merge Vary "X-Fixture"',
    '</Files>',
    '<Files "wrong-type-fixture.css">', '  ForceType image/png', '</Files>',
    '<FilesMatch "(?:private-fixture\\.html|private-fixture\\.json)$">',
    '  Header set Cache-Control "private, no-store"',
    '  Header set Set-Cookie "fixture=not-a-secret; HttpOnly"',
    '</FilesMatch>',
    '<VirtualHost 127.0.0.1:' + port + '>',
    '  ServerName perf27-control.invalid', '</VirtualHost>',
    '<VirtualHost 127.0.0.1:' + port + '>', '  ServerName gramlyze.loc',
    '  Include ' + quote(include),
    ...(diagnose ? ['<LocationMatch "^/build/assets/">',
      '  Header always set X-Fixture-Handler "expr=%{HANDLER}"',
      '  Header always set X-Fixture-Path "expr=%{REQUEST_FILENAME}"',
      '  Header always set X-Fixture-Type "expr=%{CONTENT_TYPE}"',
      '  Header always set X-Fixture-Size "expr=%{filesize:%{REQUEST_FILENAME}}"',
      '  Header always set X-Fixture-Out-Length "expr=%{resp:Content-Length}"',
      '</LocationMatch>'] : []),
    '</VirtualHost>', '',
  ];
  fs.writeFileSync(conf, lines.join('\n'), { flag: 'wx' });
}

function get(port, name, url, { method = 'GET', host = 'gramlyze.loc', headers = {} } = {}) {
  return new Promise((resolve, reject) => {
    const start = performance.now();
    const request = http.request({
      hostname: '127.0.0.1', port, path: url, method, agent: false,
      headers: { Host: host, ...headers },
    }, response => {
      const ttfb = performance.now() - start;
      const chunks = [];
      let bytes = 0;
      response.on('error', reject);
      response.on('data', chunk => {
        bytes += chunk.length;
        if (bytes > 1024 * 1024) request.destroy(new Error('contract body exceeded 1 MiB'));
        else chunks.push(chunk);
      });
      response.on('end', () => {
        if (!response.complete) return reject(new Error('incomplete response: ' + name));
        const wire = Buffer.concat(chunks);
        const encoding = response.headers['content-encoding'] || 'identity';
        let decoded;
        try {
          decoded = wire.length && encoding === 'gzip' ? gunzipSync(wire, { maxOutputLength: 1024 * 1024 }) : wire;
        } catch (error) {
          return reject(new Error('invalid gzip response: ' + name, { cause: error }));
        }
        const row = {
          name, method, host, url, status: response.statusCode, encoding,
          wire: wire.length, decoded: decoded.length, bodySha256: sha(decoded), elapsedMs: performance.now() - start, ttfbMs: ttfb,
          vary: response.headers.vary || null, etag: response.headers.etag || null,
          cache: response.headers['cache-control'] || null, length: response.headers['content-length'] || null,
          transfer: response.headers['transfer-encoding'] || null, version: response.httpVersion,
        };
        if (diagnose) row.debug = Object.fromEntries(Object.entries(response.headers).filter(([k]) => k.startsWith('x-fixture-')));
        receipt.rows.push(row);
        resolve({ ...row, headers: response.headers, bytes: wire, content: decoded });
      });
    });
    const timeout = setTimeout(() => request.destroy(new Error('contract deadline: ' + name)), 6000);
    request.once('error', reject);
    request.once('close', () => clearTimeout(timeout));
    request.end();
  });
}

function verifyBody(reply, expected, encoding, status = 200) {
  assert.equal(reply.status, status, reply.name + ' status');
  assert.equal(reply.encoding, encoding, reply.name + ' encoding');
  assert.deepEqual(reply.content, expected, reply.name + ' decoded content');
  if (reply.length !== null) assert.equal(Number(reply.length), reply.wire, reply.name + ' Content-Length');
  if (reply.transfer !== null) assert.equal(reply.length, null, reply.name + ' cannot have both length and transfer encoding');
}

function hasVary(reply, field) {
  return (reply.vary || '').split(',').some(value => value.trim().toLowerCase() === field.toLowerCase());
}

async function verify(port) {
  const file = '/build/assets/static-fixture.css';
  const G = { 'Accept-Encoding': 'gzip' };
  const I = { 'Accept-Encoding': 'identity' };
  const control = await get(port, 'isolated A', file, { host: 'perf27-control.invalid', headers: G });
  verifyBody(control, sample, 'identity');
  assert.equal(hasVary(control, 'Accept-Encoding'), false);
  const plain = await get(port, 'identity', file, { headers: I });
  verifyBody(plain, sample, 'identity');
  assert.equal(plain.etag, control.etag);
  assert.equal(plain.cache, control.cache);
  const zipped = await get(port, 'gzip', file, { headers: G });
  verifyBody(zipped, sample, 'gzip');
  assert.ok(zipped.wire < plain.wire);
  assert.ok(zipped.etag && zipped.etag !== plain.etag);
  assert.equal(zipped.cache, control.cache);
  assert.equal(zipped.headers['content-type'], control.headers['content-type']);
  for (const reply of [plain, zipped]) {
    assert.equal(hasVary(reply, 'Accept-Encoding'), true, reply.name);
    assert.equal(hasVary(reply, 'X-Fixture'), true, reply.name);
  }
  for (const [name, value] of [
    ['missing negotiation', undefined], ['unsupported coding', 'zstd, br, deflate'],
    ['standard zero weight', 'gzip;q=0, identity'],
    ['zero weight mixed-case', 'gZiP; Q=0.000, identity'],
    ['zero weight in list', 'br, gzip;q=0.00, identity;q=1'],
  ]) {
    const reply = await get(port, name, file, { headers: value === undefined ? {} : { 'Accept-Encoding': value } });
    verifyBody(reply, sample, 'identity');
    assert.equal(hasVary(reply, 'Accept-Encoding'), true, name);
  }
  for (const [name, value] of [['browser offer', 'gzip, deflate, br'], ['positive weight', 'br, gzip;q=0.5']]) {
    verifyBody(await get(port, name, file, { headers: { 'Accept-Encoding': value } }), sample, 'gzip');
  }
  const head = await get(port, 'HEAD', file, { method: 'HEAD', headers: G });
  assert.equal(head.status, 200);
  assert.equal(head.wire, 0);
  assert.equal(hasVary(head, 'Accept-Encoding'), true);
  if (head.length !== null) assert.equal(Number(head.length), head.encoding === 'gzip' ? zipped.wire : plain.wire);
  const identityConditional = await get(port, 'identity ETag 304', file, {
    headers: { ...I, 'If-None-Match': plain.etag },
  });
  assert.equal(identityConditional.status, 304);
  assert.equal(identityConditional.wire, 0);
  assert.equal(hasVary(identityConditional, 'Accept-Encoding'), true);
  const modified = await get(port, 'gzip If-Modified-Since 304', file, {
    headers: { ...G, 'If-Modified-Since': zipped.headers['last-modified'] },
  });
  assert.equal(modified.status, 304);
  assert.equal(modified.wire, 0);
  assert.equal(hasVary(modified, 'Accept-Encoding'), true);
  const gzipConditional = await get(port, 'gzip ETag revalidation', file, {
    headers: { ...G, 'If-None-Match': zipped.etag },
  });
  // Depending on the release, AddSuffix may require a fresh gzip 200.
  if (gzipConditional.status === 200) verifyBody(gzipConditional, sample, 'gzip');
  else {
    assert.equal(gzipConditional.status, 304);
    assert.equal(gzipConditional.wire, 0);
  }
  assert.equal(hasVary(gzipConditional, 'Accept-Encoding'), true);
  const range = await get(port, 'range', file, { headers: { ...G, Range: 'bytes=0-15' } });
  verifyBody(range, sample.subarray(0, 16), 'identity', 206);
  assert.equal(range.headers['content-range'], 'bytes 0-15/' + sample.length);
  for (const url of ['/build/assets/large-fixture.css', '/build/assets/large-fixture.js', '/build/assets/min-fixture.css', '/gramlyze-background.svg']) {
    const asset = await get(port, 'eligible asset', url, { headers: G });
    verifyBody(asset, fixture[url], 'gzip');
    assert.ok(asset.wire < asset.decoded);
  }
  for (const url of ['/build/assets/tiny-fixture.css', '/build/assets/wrong-type-fixture.css', '/robots.txt',
    '/build/assets/test.zip', '/api/private-fixture.json', '/test/private-fixture.html',
    '/storage/unreviewed-fixture.css', '/unreviewed-fixture.css']) {
    const asset = await get(port, 'excluded asset', url, { headers: G });
    verifyBody(asset, fixture[url], 'identity');
  }
  assert.equal(receipt.rows.some(row => row.url === '/api/private-fixture.json' && row.cache === 'private, no-store'), true);
  const encodedAlready = await get(port, 'pre-encoded no double gzip', '/build/assets/preencoded-fixture.js', { headers: G });
  verifyBody(encodedAlready, sample, 'gzip');
  assert.deepEqual(encodedAlready.bytes, preencoded);
  const missing = await get(port, 'missing asset', '/build/assets/missing-fixture.css', { headers: G });
  assert.equal(missing.status, 404);
  assert.equal(missing.encoding, 'identity');
}

async function run() {
  let processRef, closed;
  try {
    const port = await choosePort();
    receipt.port = port;
    writeConfig(port);
    const apache = ['-d', root, '-f', conf];
    const check = spawnSync(httpd, [...apache, '-t'], { cwd: scratch, encoding: 'utf8', windowsHide: true, timeout: 8000 });
    assert.equal(check.status, 0, (check.error || check.stderr || check.stdout || 'syntax check failed').toString());
    receipt.syntax = check.stderr.trim() || check.stdout.trim();
    processRef = spawn(httpd, [...apache, '-X'], { cwd: scratch, stdio: 'ignore', windowsHide: true });
    let startupError;
    processRef.once('error', error => { startupError = error; });
    closed = new Promise(resolve => processRef.once('close', (code, signal) => resolve({ code, signal })));
    for (let attempt = 0; ; attempt++) {
      if (startupError) throw startupError;
      if (processRef.exitCode !== null || attempt === 35) throw new Error('private Apache did not become ready');
      try {
        const ready = await get(port, 'private fixture ready', '/robots.txt', { host: 'perf27-control.invalid' });
        verifyBody(ready, fixture['/robots.txt'], 'identity');
        break;
      } catch (error) {
        if (error.code !== 'ECONNREFUSED') throw error;
        await sleep(100);
      }
    }
    await verify(port);
    assert.equal(sha(fs.readFileSync(include)), shaAtStart, 'candidate include changed during contract');
    receipt.result = 'PASS';
  } finally {
    if (processRef) {
      processRef.kill();
      receipt.listenerClose = await closed;
    }
  }
}

run().catch(error => {
  receipt.result = 'FAIL';
  receipt.error = error.message;
  process.exitCode = 1;
}).finally(() => {
  fs.writeFileSync(receiptPath, JSON.stringify(receipt, null, 2) + '\n', { flag: 'wx' });
  console.log(JSON.stringify({ result: receipt.result, checks: receipt.rows.length, receipt: receiptPath, error: receipt.error }));
});
