'use strict';

// Linux publication adapter for PERF-29. It never edits or reloads Nginx.
// A verified PERF-29 generation is projected into an adjacent identity/.gz
// tree because ngx_http_gzip_static_module opens those two paths by suffix.
const assert = require('node:assert/strict');
const { spawnSync } = require('node:child_process');
const { createHash, randomUUID } = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');
const { gunzipSync, gzipSync } = require('node:zlib');
const release = require('./immutable-release.cjs');

const PIN = /^[a-f0-9]{64}$/;
const ASSET = /^\/build\/assets\/[A-Za-z0-9][A-Za-z0-9_.-]{0,159}-[A-Za-z0-9_-]{8,32}\.(?:css|m?js)$/;
const SAFE_PATH = /^\/(?:[A-Za-z0-9_.-]+\/)*[A-Za-z0-9_.-]+$/;
const MAX_MANIFEST = 2 * 1024 * 1024;
const TTL = 31_536_000;
const sha = bytes => createHash('sha256').update(bytes).digest('hex');
const now = () => new Date().toISOString();

function linux() { assert.equal(process.platform, 'linux', 'requires native Linux'); }

function validPin(pin) {
    assert.equal(typeof pin, 'string');
    assert.match(pin, PIN, 'requires a full manifest SHA-256 pin');
    return pin;
}

function safePath(input) {
    assert.equal(typeof input, 'string');
    assert.match(input, SAFE_PATH, 'expected an absolute path without config metacharacters');
    assert.equal(path.posix.resolve(input), input, 'noncanonical path');
    return input;
}

function numeric(value, label, min = 0) {
    assert((typeof value === 'number' && Number.isSafeInteger(value)) ||
        (typeof value === 'string' && /^(?:0|[1-9][0-9]{0,9})$/.test(value)), 'invalid ' + label);
    const result = Number(value);
    assert(result >= min && result <= 0xffffffff, 'invalid ' + label);
    return result;
}

function exists(file) {
    try { fs.lstatSync(file); return true; }
    catch (error) { if (error.code === 'ENOENT') return false; throw error; }
}

function within(parent, child) { return parent === child || child.startsWith(parent + '/'); }
function disjoint(one, two) { return !within(one, two) && !within(two, one); }
function folder(file, expectedUid, exactMode) {
    const stat = fs.lstatSync(file);
    assert(stat.isDirectory() && !stat.isSymbolicLink(), 'expected a physical directory: ' + file);
    assert.equal(fs.realpathSync.native(file), file, 'physical path mismatch: ' + file);
    if (expectedUid !== undefined) assert.equal(stat.uid, expectedUid, 'unexpected owner: ' + file);
    if (exactMode !== undefined) assert.equal(stat.mode & 0o7777, exactMode, 'unexpected directory mode: ' + file);
    return stat;
}

function trustedParents(file, uid, { worldTraverse = true } = {}) {
    let next = file;
    for (;;) {
        const stat = folder(next);
        assert(stat.uid === 0 || stat.uid === uid, 'untrusted ancestor owner: ' + next);
        // POSIX extended ACL rights are capped by the owning group mode mask.
        // No group/other write here also disallows named ACL write.
        assert(!(stat.mode & 0o022), 'mutable ancestor: ' + next);
        if (worldTraverse) assert(stat.mode & 0o001, 'worker traversal not guaranteed: ' + next);
        if (next === '/') return;
        next = path.posix.dirname(next);
    }
}

function fingerprint(stat) {
    return [stat.dev, stat.ino, stat.mode, stat.uid, stat.gid, stat.nlink, stat.size, stat.mtimeNs, stat.ctimeNs];
}

function readFile(file, max, uid, mode) {
    const initial = fs.lstatSync(file, { bigint: true });
    assert(initial.isFile() && initial.nlink === 1n && !initial.isSymbolicLink(), 'expected unlinked regular file: ' + file);
    assert.equal(fs.realpathSync.native(file), file, 'nonphysical file path: ' + file);
    if (uid !== undefined) assert.equal(initial.uid, BigInt(uid), 'unexpected file owner: ' + file);
    if (mode !== undefined) assert.equal(initial.mode & 0o7777n, BigInt(mode), 'unexpected file mode: ' + file);
    assert(initial.size <= BigInt(max), 'file exceeds reviewed size: ' + file);
    const fd = fs.openSync(file, fs.constants.O_RDONLY | (fs.constants.O_NOFOLLOW || 0));
    try {
        const opened = fs.fstatSync(fd, { bigint: true });
        const pinned = fingerprint(initial);
        assert.deepEqual(fingerprint(opened), pinned, 'file changed before read: ' + file);
        const bytes = fs.readFileSync(fd);
        assert.equal(BigInt(bytes.length), initial.size);
        assert.deepEqual(fingerprint(fs.fstatSync(fd, { bigint: true })), pinned, 'file changed during read: ' + file);
        assert.deepEqual(fingerprint(fs.lstatSync(file, { bigint: true })), pinned, 'path changed during read: ' + file);
        return { bytes, modified: Number(initial.mtimeNs / 1_000_000_000n) };
    } finally { fs.closeSync(fd); }
}

function statusField(raw, name) {
    const matches = raw.split('\n').filter(line => line.startsWith(name + ':'));
    assert.equal(matches.length, 1, 'missing or duplicate process field: ' + name);
    return matches[0].slice(name.length + 1).trim();
}

function procContents(pid) {
    const base = '/proc/' + pid;
    const stat = () => {
        const raw = fs.readFileSync(base + '/stat', 'utf8');
        const end = raw.lastIndexOf(') ');
        assert(end > 0 && raw.startsWith(String(pid) + ' ('), 'invalid process stat');
        const start = raw.slice(end + 2).trim().split(/\s+/)[19];
        assert(/^\d+$/.test(start), 'invalid process start time');
        return start;
    };
    const start = stat();
    const status = fs.readFileSync(base + '/status', 'utf8');
    const command = fs.readFileSync(base + '/cmdline', 'utf8').split('\0')[0];
    assert.equal(stat(), start, 'process changed during inspection');
    return { start, status, command };
}

function inspectWorker(input, publisherUid) {
    linux();
    const pid = numeric(input, 'Nginx worker PID', 2);
    const worker = procContents(pid);
    assert.equal(statusField(worker.status, 'Name'), 'nginx');
    assert.match(worker.command, /^nginx: worker process(?:\s*)$/, 'PID is not an active Nginx request worker');
    const ids = field => statusField(worker.status, field).split(/\s+/).map(id => numeric(id, field, 1));
    const user = ids('Uid');
    const group = ids('Gid');
    assert.equal(user.length,4);
    assert.equal(group.length,4);
    const [uid, euid, suid, fsuid] = user;
    const [gid, egid, sgid, fsgid] = group;
    assert(uid !== publisherUid && uid === euid && uid === suid && uid === fsuid, 'worker overlaps privileged filesystem role');
    assert(gid === egid && gid === sgid && gid === fsgid, 'worker GID has mixed credentials');
    const groups = statusField(worker.status, 'Groups').split(/\s+/).filter(Boolean).map(id => numeric(id, 'worker supplementary GID'));
    assert(!groups.includes(0), 'worker retains the root group');
    for (const cap of ['CapInh', 'CapPrm', 'CapEff', 'CapAmb']) assert.match(statusField(worker.status, cap), /^0+$/, 'worker retains ' + cap);
    const parentPid = numeric(statusField(worker.status, 'PPid'), 'Nginx master PID', 2);
    const master = procContents(parentPid);
    assert.equal(statusField(master.status, 'Name'), 'nginx');
    assert.match(master.command, /^nginx: master process(?: |$)/, 'worker parent is not the Nginx master');
    assert.match(statusField(master.status, 'Uid'), /^0\s+0\s+0\s+0$/, 'expected trusted root Nginx master');
    return { pid, parentPid, uid, gid, groups, workerStart: worker.start, masterStart:master.start };
}

function accessBoundary({ vault:input, publicRoot, publisherUid:owner, workerPid }) {
    linux();
    const vault = safePath(input);
    const publisherUid = numeric(owner, 'trusted publisher UID');
    trustedParents(vault, publisherUid);
    folder(vault, publisherUid);
    if (publicRoot !== undefined) {
        safePath(publicRoot);
        folder(publicRoot);
        assert(disjoint(vault, publicRoot), 'vault must be outside the public tree and cannot contain it');
    }
    const worker = inspectWorker(workerPid, publisherUid);
    return { vault, publicRoot, publisherUid, worker };
}

function selectAssets(releases) {
    assert(Array.isArray(releases) && releases.length > 0 && releases.length <= 16, 'missing or excessive generations');
    const selected = new Map();
    for (const verified of releases) {
        const { manifest } = verified;
        assert.equal(manifest.scope, 'vite', 'only the reviewed Vite graph may be exposed');
        for (const asset of manifest.assets) {
            assert(asset.class === 'vite' && ASSET.test(asset.url) && ('/' + asset.source) === asset.url, 'unsafe asset URI');
            assert.equal(asset.mime, asset.url.endsWith('.css') ? 'text/css' : 'text/javascript', 'unexpected pinned asset MIME');
            validPin(asset.identity.sha256);
            if (asset.gzip !== null) {
                validPin(asset.gzip.sha256);
                assert(asset.gzip.bytes < asset.identity.bytes, 'gzip and identity must have distinct ETags');
            }
            const key = asset.url.toLowerCase();
            const previous = selected.get(key);
            if (previous) {
                assert(previous.asset.url === asset.url && previous.asset.identity.sha256 === asset.identity.sha256 &&
                    previous.asset.gzip?.sha256 === asset.gzip?.sha256 && previous.asset.mime === asset.mime,
                'collision in retained fingerprinted URI: ' + asset.url);
                continue;
            }
            selected.set(key, { asset, deliveryRoot: verified.deliveryRoot });
        }
    }
    return [...selected.values()].sort((a,b) => a.asset.url < b.asset.url ? -1 : 1);
}

function privateGeneration(vault, digest, uid) {
    const generation = path.posix.join(vault, release.releaseName(validPin(digest)));
    function walk(dir) {
        folder(dir, uid, 0o700);
        for (const entry of fs.readdirSync(dir, { withFileTypes:true })) {
            const file = path.posix.join(dir, entry.name);
            if (entry.isDirectory()) walk(file);
            else {
                const stat = fs.lstatSync(file);
                assert(entry.isFile() && stat.isFile() && stat.nlink === 1, 'unexpected metadata link or special file');
                assert(stat.uid === uid && (stat.mode & 0o7777) === 0o600, 'private metadata is not publisher-only: ' + file);
            }
        }
    }
    walk(generation);
    return release.verify({ generation, expectedDigest:digest });
}

function expectedFiles(manifest) {
    const files = new Map();
    for (const { source, identity, gzip, modified } of manifest.assets) {
        files.set(source, { pin:identity, modified });
        if (gzip) files.set(source + '.gz', { pin:gzip, modified, decodesTo:identity });
    }
    return files;
}

function validateDelivery(dir, manifest, uid, mode = 0o555) {
    folder(dir, uid, mode);
    const expected = expectedFiles(manifest);
    const directories = new Set();
    for (const file of expected.keys()) {
        let current = path.posix.dirname(file);
        while (current !== '.') { directories.add(current); current = path.posix.dirname(current); }
    }
    const found = new Set();
    const walk = relative => {
        for (const entry of fs.readdirSync(path.posix.join(dir, relative), { withFileTypes:true })) {
            const name = path.posix.join(relative, entry.name);
            const target = path.posix.join(dir, name);
            if (entry.isDirectory()) {
                assert(directories.has(name), 'unexpected public projection directory: ' + name);
                folder(target, uid, 0o555);
                walk(name);
            } else {
                assert(entry.isFile() && expected.has(name), 'unexpected public projection file or link: ' + name);
                const { pin, modified, decodesTo } = expected.get(name);
                const artifact = readFile(target, pin.bytes, uid, 0o444);
                assert.equal(sha(artifact.bytes), pin.sha256, 'delivery bytes differ: ' + name);
                assert.equal(artifact.bytes.length, pin.bytes);
                assert.equal(artifact.modified, modified, 'delivery Last-Modified differs: ' + name);
                if (decodesTo) {
                    const decoded = gunzipSync(artifact.bytes, { maxOutputLength:decodesTo.bytes });
                    assert.equal(sha(decoded), decodesTo.sha256, 'gzip does not decode to identity: ' + name);
                    assert.equal(decoded.length, decodesTo.bytes);
                }
                found.add(name);
            }
        }
    };
    walk('');
    assert.equal(found.size, expected.size, 'projection missing a representation');
}

function syncDirectory(dir) {
    const fd = fs.openSync(dir, fs.constants.O_RDONLY | fs.constants.O_DIRECTORY | fs.constants.O_NOFOLLOW);
    try { fs.fsyncSync(fd); } finally { fs.closeSync(fd); }
}

function writeProjection(staging, verified, uid) {
    const dirs = new Set();
    for (const [name, { pin, modified }] of expectedFiles(verified.manifest)) {
        const from = path.posix.join(verified.generation, pin.path);
        const target = path.posix.join(staging, name);
        const data = readFile(from, pin.bytes, uid, 0o600);
        assert(data.modified === modified && data.bytes.length === pin.bytes && sha(data.bytes) === pin.sha256,
            'input changed while making projection: ' + name);
        const parent = path.posix.dirname(target);
        fs.mkdirSync(parent, { recursive:true, mode:0o700 });
        for (let cursor = parent; cursor !== staging; cursor = path.posix.dirname(cursor)) dirs.add(cursor);
        const fd = fs.openSync(target, fs.constants.O_WRONLY | fs.constants.O_CREAT | fs.constants.O_EXCL | fs.constants.O_NOFOLLOW, 0o600);
        try {
            fs.writeFileSync(fd, data.bytes);
            fs.futimesSync(fd, modified, modified);
            fs.fchmodSync(fd, 0o444);
            fs.fsyncSync(fd);
        } finally { fs.closeSync(fd); }
    }
    for (const dir of [...dirs].sort((a,b) => b.length - a.length)) {
        fs.chmodSync(dir, 0o555);
        syncDirectory(dir);
    }
    // Remain private until bytes and mode pass inspection.
    validateDelivery(staging, verified.manifest, uid, 0o700);
    fs.chmodSync(staging, 0o555);
    syncDirectory(staging);
}

function publish(options) {
    const { vault, publicRoot, publisherUid, worker } = accessBoundary(options);
    assert(publicRoot, 'publisher requires a reviewed public source path');
    assert.equal(process.getuid(), publisherUid, 'publication must run as the configured publisher');
    const lock = path.posix.join(vault, '.linux-publish-lock');
    fs.mkdirSync(lock, { mode:0o700 });
    let verified, deliveryRoot, alreadySealed;
    try {
        const staged = release.stage({ publicRoot, releaseRoot:vault });
        const pin = staged.manifestSha256;
        selectAssets([staged]);
        const canonical = path.posix.join(vault, release.releaseName(pin));
        if (exists(canonical)) {
            verified = privateGeneration(vault, pin, publisherUid);
            release.assertFresh({ publicRoot, generation:verified.generation, expectedDigest:pin });
            // Only the exclusive, private staging path created above is removed.
            assert.equal(path.posix.dirname(staged.generation), vault);
            assert.match(path.posix.basename(staged.generation), /^\.stage-[a-f0-9]{32}$/);
            folder(staged.generation, publisherUid, 0o700);
            fs.rmSync(staged.generation, { recursive:true });
        } else {
            release.prepare({ publicRoot, releaseRoot:vault, staged });
            verified = privateGeneration(vault, pin, publisherUid);
        }
        deliveryRoot = path.posix.join(vault, 'serve-' + pin);
        alreadySealed = exists(deliveryRoot);
        if (!alreadySealed) {
            const staging = path.posix.join(vault, '.serve-stage-' + randomUUID().replaceAll('-', ''));
            fs.mkdirSync(staging, { mode:0o700 });
            writeProjection(staging, verified, publisherUid);
            assert(!exists(deliveryRoot), 'refusing to replace an existing delivery tree');
            fs.renameSync(staging, deliveryRoot);
            syncDirectory(vault);
        }
        validateDelivery(deliveryRoot, verified.manifest, publisherUid);
        assert.deepEqual(inspectWorker(worker.pid, publisherUid), worker, 'worker changed during publication');
    } finally { fs.rmdirSync(lock); }
    return { status:alreadySealed ? 'ALREADY_SEALED' : 'SEALED', deliveryRoot, generation:verified.generation,
        manifestSha256:verified.manifestSha256, viteManifestSha256:verified.viteManifestSha256,
        assets:verified.manifest.assets.length, gzipAssets:verified.manifest.assets.filter(a=>a.gzip).length, worker, utc:now() };
}

function buildCoverage(publicRoot, verified, selections) {
    const served = new Set(selections.map(value => value.asset.source));
    const active = new Set(verified.manifest.assets.map(asset => asset.source));
    const vite = JSON.parse(readFile(path.posix.join(verified.generation, 'vite-manifest.json'), MAX_MANIFEST).bytes);
    for (const record of Object.values(vite)) {
        for (const name of [record.file, ...(record.css ?? []), ...(record.assets ?? [])]) {
            assert(active.has('build/' + name), 'build contains unreviewed Vite dependency, refusing to fence it: ' + name);
        }
    }
    const build = path.posix.join(publicRoot, 'build');
    folder(build);
    for (const item of fs.readdirSync(build)) {
        assert(['manifest.json', 'assets'].includes(item), 'unreviewed public /build/ path: ' + item);
    }
    folder(path.posix.join(build,'assets'));
    for (const entry of fs.readdirSync(path.posix.join(build, 'assets'), { withFileTypes:true })) {
        const name = 'build/assets/' + entry.name;
        assert(entry.isFile() && served.has(name), 'unreviewed public build asset: ' + name);
    }
}

function checkedHeaders(list = []) {
    assert(Array.isArray(list) && list.length <= 32, 'invalid reviewed header list');
    const excluded = /^(?:cache-control|pragma|expires|vary|etag|last-modified|accept-ranges|transfer-encoding|content-(?:type|encoding|length|range)|set-cookie|location)$/i;
    const seen = new Set();
    return list.map(record => {
        assert.deepEqual(Object.keys(record).sort(), ['always','name','value']);
        const { name, value, always } = record;
        assert(typeof name === 'string' && /^[A-Za-z][A-Za-z0-9-]{0,63}$/.test(name) && !excluded.test(name), 'unsupported reviewed header');
        assert(typeof value === 'string' && value.length <= 2048 && /^[ -~]*$/.test(value) && !/["\\$]/.test(value), 'unsafe reviewed header value');
        assert(typeof always === 'boolean');
        assert(!seen.has(name.toLowerCase()), 'duplicate reviewed header');
        seen.add(name.toLowerCase());
        return '    add_header ' + name + ' "' + value + '"' + (always ? ' always' : '') + ';';
    });
}

function nginxConfig(releases, headers) {
    const approved = checkedHeaders(headers);
    const locations = selectAssets(releases).map(({asset, deliveryRoot}) => {
        const contentType = asset.mime === 'text/css' ? 'text/css css;' : 'text/javascript js mjs;';
        const root = safePath(deliveryRoot);
        return [
            'location = ' + asset.url + ' {',
            '    root ' + root + ';',
            // The provided prefix was already pinned beneath protected
            // physical parents; recheck only the relative lookup suffix.
            '    disable_symlinks on from=' + root + ';',
            '    gzip off;',
            '    gzip_static ' + (asset.gzip ? 'on' : 'off') + ';',
            // Nginx can avoid opening the sidecar on identity requests with
            // gzip_vary off; emit exactly one explicit Vary on 200/206/304.
            '    gzip_vary off;',
            '    types { ' + contentType + ' }',
            '    etag on;',
            '    if_modified_since exact;',
            '    expires off;',
            '    error_page 403 404 = @gramlyze_perf31_build_not_found;',
            ...approved,
            '    add_header Cache-Control "public, max-age=' + TTL + ', immutable";',
            ...(asset.gzip ? ['    add_header Vary "Accept-Encoding" always;'] : []),
            '}',
        ].join('\n');
    });
    const guards = [
        'location = /build {',
        '    error_page 404 = @gramlyze_perf31_build_not_found;',
        '    return 404;',
        '}',
        'location ^~ /build/ {',
        '    error_page 404 = @gramlyze_perf31_build_not_found;',
        '    return 404;',
        '}',
        'location @gramlyze_perf31_build_not_found {',
        '    recursive_error_pages off;',
        '    gzip off;',
        '    gzip_static off;',
        '    return 404;',
        '}',
    ].join('\n');
    return ['# PERF-31: include only within the reviewed Gramlyze .ub server {}.',
        '# Exact asset paths only; the separate vault is not a public document root.',
        ...locations, guards, ''].join('\n\n');
}

function trustedBinary(file) {
    const canonical = fs.realpathSync.native(safePath(file));
    const info = fs.statSync(canonical);
    assert(info.isFile() && info.uid === 0 && !(info.mode & 0o022) && (info.mode & 0o001), 'binary is not trusted or executable');
    trustedParents(path.posix.dirname(canonical), 0);
    return canonical;
}

function nginxSupportsStaticGzip(file) {
    const binary = trustedBinary(file);
    const result = spawnSync(binary, ['-V'], { encoding:'utf8', timeout:5000, maxBuffer:1024*1024, env:{ PATH:'/usr/sbin:/usr/bin:/bin', LC_ALL:'C' } });
    assert.equal(result.status, 0, 'cannot verify Nginx configure flags');
    const message = result.stderr + '\n' + result.stdout;
    assert(/(?:^|[\s'"])--with-http_gzip_static_module(?=[\s'"]|$)/.test(message), 'reviewed Nginx binary does not include gzip_static');
    return { binary, version:message.match(/^nginx version: ([^\r\n]+)$/m)?.[1] ?? 'unknown' };
}

function render(options) {
    const { vault, publicRoot, publisherUid, worker } = accessBoundary(options);
    assert(publicRoot, 'renderer requires the exact active Vite source tree');
    const nginx = nginxSupportsStaticGzip(options.nginx ?? '/usr/sbin/nginx');
    const configured = fs.statSync(nginx.binary, { bigint:true });
    const running = fs.statSync('/proc/' + worker.parentPid + '/exe', { bigint:true });
    assert(configured.dev === running.dev && configured.ino === running.ino,
        'gzip_static module check did not inspect the executable used by the active master');
    const pins = [validPin(options.manifest), ...(options.retain ?? []).map(validPin)];
    assert(pins.length <= 16 && new Set(pins).size === pins.length, 'duplicate or excessive generation pins');
    const selected = pins.map(pin => {
        const verified = privateGeneration(vault, pin, publisherUid);
        const deliveryRoot = path.posix.join(vault, 'serve-' + pin);
        validateDelivery(deliveryRoot, verified.manifest, publisherUid);
        return {...verified, deliveryRoot};
    });
    release.assertFresh({ publicRoot, generation:selected[0].generation, expectedDigest:pins[0] });
    buildCoverage(publicRoot, selected[0], selectAssets(selected));
    const config = nginxConfig(selected, options.headers);
    assert.deepEqual(inspectWorker(worker.pid, publisherUid), worker, 'worker changed during verification');
    return { status:'CANDIDATE_ONLY', config, configSha256:sha(config), generations:pins,
        assets:selectAssets(selected).length, worker, nginx, utc:now() };
}

// All mutations performed by this routine are against disposable fixtures.
// The subprocess gets the actual Nginx worker UID/GIDs with no capabilities;
// this is a credential-equivalence test, not ptrace/injection into Nginx.
function fixtureChecks(base) {
    const fs = require('node:fs');
    const path = require('node:path');
    const sealed = path.join(base, 'sealed');
    const asset = path.join(sealed, 'approved.css');
    const gzip = asset + '.gz';
    const alias = path.join(base, 'writable', 'alias');
    const rename = path.join(sealed, 'renamed.css');
    const cases = [
        ['read-css', () => fs.readFileSync(asset), true],
        ['read-gzip', () => fs.readFileSync(gzip), true],
        ['write-worker-scratch', () => fs.writeFileSync(alias,'own scratch',{flag:'wx'}), true],
        ['read-private-manifest', () => fs.readFileSync(path.join(base, 'metadata', 'manifest.json')), false],
        ['write-css', () => fs.writeFileSync(asset,'bad'), false],
        ['append-sidecar', () => fs.appendFileSync(gzip,'bad'), false],
        ['delete-css', () => fs.unlinkSync(asset), false],
        ['rename-css', () => fs.renameSync(asset,rename), false],
        ['replace-gzip', () => fs.renameSync(alias,gzip), false],
        ['change-timestamp', () => fs.utimesSync(asset,new Date(),new Date()), false],
        ['permission-change', () => fs.chmodSync(asset,0o0666), false],
        ['ownership-change', () => fs.chownSync(asset,process.getuid(),process.getgid()), false],
        ['symlink-in-sealed-dir', () => fs.symlinkSync(alias,rename), false],
        ['hardlink-in-sealed-dir', () => fs.linkSync(alias,rename), false],
        ['hardlink-out-of-sealed-dir', () => fs.linkSync(asset, alias + '.link'), false],
        ['create-sibling', () => fs.mkdirSync(path.join(base, 'replaced')), false],
    ];
    const rows = cases.map(([name, run, allowed]) => {
        try { run(); return { name, allowed, result:'OK', pass:allowed }; }
        catch (e) { return { name, allowed, result:e.code, pass:!allowed && ['EPERM','EACCES','EROFS'].includes(e.code) }; }
    });
    const status = fs.readFileSync('/proc/self/status', 'utf8');
    const field = name => status.split('\n').find(line => line.startsWith(name + ':')).slice(name.length+1).trim();
    return { operations:rows, uid:process.getuid(), gid:process.getgid(),
        groups:field('Groups').split(/\s+/).filter(Boolean).map(Number),
        caps:['CapInh','CapPrm','CapEff','CapBnd','CapAmb'].map(field), noNewPrivs:field('NoNewPrivs') };
}

function probe(options) {
    const { vault, publisherUid, worker } = accessBoundary(options);
    assert.equal(process.getuid(), 0, 'credential-equivalent negative fixture probe requires a trusted root operator');
    const setter = trustedBinary('/usr/bin/setpriv');
    const node = trustedBinary(process.execPath);
    const base = path.posix.join(vault, '.linux-probe-' + randomUUID().replaceAll('-', ''));
    fs.mkdirSync(base, { mode:0o755 });
    const sealed = path.posix.join(base,'sealed');
    const metadata = path.posix.join(base,'metadata');
    const writable = path.posix.join(base,'writable');
    for (const dir of [sealed,metadata,writable]) fs.mkdirSync(dir,{mode:0o700});
    const body = Buffer.from('/* synthetic immutable fixture */\n'.repeat(100));
    const assets = new Map([['approved.css', body],['approved.css.gz', gzipSync(body)]]);
    for (const [name, bytes] of assets) {
        const target = path.posix.join(sealed,name);
        fs.writeFileSync(target, bytes, {flag:'wx',mode:0o600});
        fs.chownSync(target,publisherUid,0);
        fs.chmodSync(target,0o444);
    }
    fs.writeFileSync(path.posix.join(metadata,'manifest.json'), '{}\n', {flag:'wx',mode:0o600});
    for (const dir of [sealed,metadata,base]) fs.chownSync(dir,publisherUid,0);
    fs.chmodSync(sealed,0o555);
    fs.chmodSync(base,0o555);
    fs.chownSync(writable,worker.uid,worker.gid);
    const program = 'process.stdout.write(JSON.stringify((' + fixtureChecks.toString() + ')(process.argv[1])) + "\\n");';
    const groupArguments = worker.groups.length ? ['--groups=' + worker.groups.join(',')] : ['--clear-groups'];
    const run = spawnSync(setter, ['--reuid=' + worker.uid, '--regid=' + worker.gid,
        ...groupArguments, '--bounding-set=-all','--inh-caps=-all','--ambient-caps=-all','--no-new-privs',
        '--', node, '-e', program, base], { encoding:'utf8', timeout:10000, maxBuffer:256*1024, env:{ PATH:'/usr/bin:/bin', LC_ALL:'C' } });
    assert.equal(run.status,0, 'worker fixture command did not complete; preserved ' + base);
    const evidence = JSON.parse(run.stdout);
    const { operations } = evidence;
    assert.equal(evidence.uid,worker.uid, 'fixture subprocess has the wrong UID');
    assert.equal(evidence.gid,worker.gid, 'fixture subprocess has the wrong GID');
    assert.deepEqual(evidence.groups.sort((a,b)=>a-b), [...worker.groups].sort((a,b)=>a-b), 'fixture subprocess has mismatched supplementary groups');
    assert(evidence.caps.every(value => /^0+$/.test(value)) && evidence.noNewPrivs === '1', 'fixture subprocess kept privileges');
    assert(Array.isArray(operations) && operations.length === 16, 'incomplete fixture evidence; preserved ' + base);
    assert.deepEqual(inspectWorker(worker.pid, publisherUid), worker, 'worker changed during fixture probe');
    const unchanged = [...assets].every(([name, bytes]) => {
        try { return readFile(path.posix.join(sealed,name),bytes.length,publisherUid,0o444).bytes.equals(bytes); }
        catch { return false; }
    });
    assert(operations.every(row => row.pass) && unchanged,
        'immutable worker fixture checks failed; preserved ' + base + ': ' + JSON.stringify(operations));
    return { status:'PASS', proof:'disposable fixtures, actual worker UID/GIDs, capabilities cleared',
        base, worker, operations, unchanged, utc:now() };
}

function cli(args) {
    const [operation, ...fields] = args;
    assert(['publish','render','probe'].includes(operation) && fields.length % 2 === 0,
        'operation must be publish, render, or probe; pass --vault ABS --publisher-uid UID --worker-pid PID');
    const permitted = {
        publish:['--vault','--publisher-uid','--worker-pid','--public'],
        render:['--vault','--publisher-uid','--worker-pid','--public','--manifest','--retain','--nginx','--headers'],
        probe:['--vault','--publisher-uid','--worker-pid'],
    }[operation];
    const values = new Map();
    const retain = [];
    for (let i=0; i<fields.length; i+=2) {
        const name = fields[i], value = fields[i+1];
        assert(permitted.includes(name) && !values.has(name) && value && !value.startsWith('--'), 'unknown/duplicate or missing flag value: ' + name);
        if (name === '--retain') retain.push(value);
        else values.set(name, value);
    }
    const options = { vault:values.get('--vault'), publisherUid:values.get('--publisher-uid'),
        workerPid:values.get('--worker-pid'), publicRoot:values.get('--public'),
        manifest:values.get('--manifest'), retain, nginx:values.get('--nginx') };
    if (values.has('--headers')) options.headers=JSON.parse(readFile(safePath(values.get('--headers')),32*1024).bytes);
    const result = ({ publish,render,probe })[operation](options);
    process.stdout.write(JSON.stringify({ operation, ...result }) + '\n');
}

module.exports = { publish, render, probe, nginxConfig, selectAssets, validateDelivery };
if (require.main === module) {
    try { cli(process.argv.slice(2)); }
    catch (error) {
        process.stderr.write(JSON.stringify({ status:'BLOCKED', error:error.message }) + '\n');
        process.exitCode = 1;
    }
}
