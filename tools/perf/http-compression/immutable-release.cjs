'use strict';

// Generation preparation and verification only. Preparing a generation does
// not publish HTTP configuration. Seal the final path with an independent OS
// policy before producing any mapping for it.
const assert = require('node:assert/strict');
const { createHash, randomUUID } = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');
const zlib = require('node:zlib');
const { TextDecoder } = require('node:util');
const c28 = require('./precompress.cjs');

const SCHEMA = 'gramlyze-perf29-immutable-v1';
const SOURCE_MANIFEST = 'build/manifest.json';
const STORED_VITE = 'vite-manifest.json';
const STORED_MANIFEST = 'manifest.json';
const DIGEST = /^[a-f0-9]{64}$/;
const RELEASE_DIRECTORY = /^gen-[a-f0-9]{32}$/;
const STAGE_DIRECTORY = /^\.stage-[a-f0-9]{32}$/;
const FINGERPRINTED = /^build\/assets\/[A-Za-z0-9][A-Za-z0-9_.-]{0,159}-[A-Za-z0-9_-]{8,32}\.(?:css|m?js)$/;
const VITE_ROOTS = Object.freeze([
    'resources/css/app.css', 'resources/css/catalog-public.css',
    'resources/js/app.js', 'resources/js/catalog-public.js',
]);
// Kept in lockstep with the finite reviewed PERF-28 stable assets. The default
// build excludes them; the opt-in snapshot is for an isolated environment only.
const STABLE = new Set([
    'js/theory-search.js', 'js/theory-mixed-test.js', 'js/theory-menu.js',
    'js/theory-course-progress.js', 'js/polyglot-course-progress.js', 'js/english-answer-variants.js',
    'js/authored-practice-ui.js', 'js/m39-practice-ui.js', 'js/m40-practice-ui.js',
    'js/m41-practice-ui.js', 'js/m43-practice-ui.js', 'js/m44-practice-ui.js', 'js/m45-practice-ui.js',
    'engram-language-pattern.svg', 'gramlyze-background.svg', 'gramlyze-background-cards.svg', 'favicon.svg',
]);
const MIMES = Object.freeze({ css: 'text/css', js: 'text/javascript', mjs: 'text/javascript', svg: 'image/svg+xml' });
const utf8 = new TextDecoder('utf-8', { fatal: true });
const posix = (base, file) => path.join(base, ...file.split('/'));
const sha = bytes => createHash('sha256').update(bytes).digest('hex');
const serial = data => Buffer.from(JSON.stringify(data, null, 2) + '\n', 'utf8');
const limitManifest = 2 * 1024 * 1024;
const limitFile = 32 * 1024 * 1024;
const limitTotal = 64 * 1024 * 1024;
const samePath = (a, b) => process.platform === 'win32' ? a.toLowerCase() === b.toLowerCase() : a === b;
const mtime = stat => Number(stat.mtimeNs / 1000000000n);

function absolute(file) {
    assert(typeof file === 'string' && path.isAbsolute(file), 'paths must be absolute');
    return boundedPath(path.resolve(file));
}

function boundedPath(file) {
    if (process.platform === 'win32') assert(file.length <= 240, 'path exceeds Windows Apache budget');
    return file;
}

function directory(file) {
    const stat = fs.lstatSync(file);
    assert(stat.isDirectory() && !stat.isSymbolicLink() && samePath(fs.realpathSync.native(file), file),
        'expected canonical physical directory: ' + file);
    return file;
}

function inside(parent, child) {
    const rel = path.relative(parent, child);
    return rel !== '..' && !rel.startsWith('..' + path.sep) && !path.isAbsolute(rel);
}

function roots(publicInput, rootInput) {
    const publicRoot = directory(absolute(publicInput));
    const releaseRoot = directory(absolute(rootInput));
    assert(!inside(publicRoot, releaseRoot) && !inside(releaseRoot, publicRoot),
        'release root must be outside the public tree and not its ancestor');
    return { publicRoot, releaseRoot };
}

function plain(value, keys) {
    assert(value && Object.getPrototypeOf(value) === Object.prototype, 'expected a JSON object');
    if (keys) assert.deepEqual(Object.keys(value).sort(), [...keys].sort(), 'unexpected manifest fields');
}

function readPhysical(file, limit) {
    boundedPath(file);
    const initial = fs.lstatSync(file, { bigint: true });
    assert(initial.isFile() && initial.nlink === 1n && !initial.isSymbolicLink() &&
        samePath(fs.realpathSync.native(file), file),
        'expected canonical regular file with no symbolic/hard links: ' + file);
    assert(initial.size <= BigInt(limit), 'asset exceeds file-size limit: ' + file);
    const flags = fs.constants.O_RDONLY | (process.platform === 'win32' ? 0 : (fs.constants.O_NOFOLLOW || 0));
    const fd = fs.openSync(file, flags);
    try {
        const same = (a, b) => {
            // Managed Windows path stat can report device 0 where fstat
            // reports the serial. The other file-identity fields must agree.
            return (a.dev === b.dev || (process.platform === 'win32' && (a.dev === 0n || b.dev === 0n))) &&
                ['ino', 'size', 'nlink', 'mtimeNs', 'ctimeNs'].every(key => a[key] === b[key]);
        };
        const opened = fs.fstatSync(fd, { bigint: true });
        assert(opened.isFile() && same(initial, opened), 'file changed before read: ' + file);
        const value = fs.readFileSync(fd);
        assert(BigInt(value.length) === opened.size && same(opened, fs.fstatSync(fd, { bigint: true })) &&
            same(opened, fs.lstatSync(file, { bigint: true })), 'file changed during read: ' + file);
        return { value, modified: mtime(opened) };
    } finally { fs.closeSync(fd); }
}

function exists(file) {
    try { fs.lstatSync(file); return true; }
    catch (error) { if (error.code === 'ENOENT') return false; throw error; }
}

function namesFromVite(bytes) {
    const parsed = JSON.parse(utf8.decode(bytes));
    plain(parsed);
    assert(Object.keys(parsed).length <= 1024, 'too many Vite records');
    const keys = new Set();
    const files = new Set();
    const add = file => {
        assert(typeof file === 'string' && FINGERPRINTED.test('build/' + file),
            'reviewed Vite asset does not have a safe fingerprinted path: ' + file);
        files.add('build/' + file);
        assert(files.size <= 128, 'too many reviewed Vite files');
    };
    function visit(key) {
        if (keys.has(key)) return;
        assert(typeof key === 'string' && Object.hasOwn(parsed, key), 'missing Vite dependency');
        keys.add(key);
        const record = parsed[key];
        plain(record);
        add(record.file);
        for (const p of ['imports', 'dynamicImports', 'css']) {
            if (!Object.hasOwn(record, p)) continue;
            assert(Array.isArray(record[p]) && record[p].length <= 1024, 'invalid Vite dependency list');
            for (const item of record[p]) {
                if (p === 'css') {
                    assert(typeof item === 'string' && item.endsWith('.css'), 'invalid Vite CSS dependency');
                    add(item);
                } else visit(item);
            }
        }
    }
    for (const entry of VITE_ROOTS) {
        if (!Object.hasOwn(parsed, entry)) continue;
        assert(parsed[entry]?.isEntry === true, 'invalid Vite entry');
        visit(entry);
    }
    assert(files.size > 0, 'no reviewed Vite assets');
    return files;
}

function assetRef(file, pin, bytes) {
    return { path: file, sha256: pin, bytes };
}

function descriptor(root, ref, expectedPath, limit) {
    plain(ref, ['path', 'sha256', 'bytes']);
    assert.equal(ref.path, expectedPath, 'unexpected physical asset path');
    assert.match(ref.sha256, DIGEST, 'invalid file SHA-256');
    assert(Number.isSafeInteger(ref.bytes) && ref.bytes >= 0 && ref.bytes <= limit, 'invalid pinned asset size');
    const found = readPhysical(posix(root, ref.path), ref.bytes);
    assert(found.value.length === ref.bytes && sha(found.value) === ref.sha256, 'integrity mismatch: ' + ref.path);
    return found;
}

function inventory(generation, wanted) {
    const directories = new Set();
    const found = new Set();
    for (const file of wanted) {
        const parts = file.split('/');
        for (let i = 1; i < parts.length; i++) directories.add(parts.slice(0, i).join('/'));
    }
    function visit(rel) {
        for (const d of fs.readdirSync(posix(generation, rel), { withFileTypes: true })) {
            const name = rel ? rel + '/' + d.name : d.name;
            if (d.isDirectory()) {
                assert(directories.has(name), 'unexpected generation directory: ' + name);
                directory(posix(generation, name));
                visit(name);
            } else {
                assert(d.isFile() && wanted.has(name), 'unexpected generation file/link: ' + name);
                found.add(name);
            }
        }
    }
    visit('');
    assert(found.size === wanted.size, 'generation is incomplete');
}

function verify({ generation: generationInput, expectedDigest } = {}) {
    const generation = directory(absolute(generationInput));
    const rawManifest = readPhysical(path.join(generation, STORED_MANIFEST), limitManifest).value;
    const manifestSha256 = sha(rawManifest);
    if (expectedDigest !== undefined) {
        assert.match(expectedDigest, DIGEST, 'expected full manifest SHA-256 pin');
        assert.equal(manifestSha256, expectedDigest, 'generation manifest pin mismatch');
    }
    if (RELEASE_DIRECTORY.test(path.basename(generation))) {
        assert.equal(path.basename(generation), releaseName(manifestSha256), 'incorrect content-identified directory');
    }
    const manifest = JSON.parse(utf8.decode(rawManifest));
    plain(manifest, ['schema', 'scope', 'vite', 'assets']);
    assert.equal(manifest.schema, SCHEMA);
    assert(['vite', 'isolated-with-stable'].includes(manifest.scope), 'unexpected generation scope');
    assert(rawManifest.equals(serial(manifest)), 'noncanonical manifest encoding');
    assert(Array.isArray(manifest.assets) && manifest.assets.length <= 128, 'invalid asset inventory');
    const vite = descriptor(generation, manifest.vite, STORED_VITE, limitManifest);
    const viteNames = namesFromVite(vite.value);
    const seen = new Set();
    const wanted = new Set([STORED_MANIFEST, STORED_VITE]);
    let previous = '';
    let total = 0;
    for (const item of manifest.assets) {
        plain(item, ['url', 'source', 'class', 'mime', 'modified', 'identity', 'gzip']);
        assert(typeof item.source === 'string' && item.source > previous &&
            !seen.has(item.source.toLowerCase()), 'duplicate or unsorted asset');
        seen.add(item.source.toLowerCase());
        previous = item.source;
        if (item.class === 'vite') {
            assert(viteNames.delete(item.source), 'unexpected Vite file: ' + item.source);
        } else {
            assert(item.class === 'stable' && manifest.scope === 'isolated-with-stable' && STABLE.has(item.source),
                'stable asset outside reviewed isolated snapshot');
        }
        assert.equal(item.url, '/' + item.source, 'unexpected public URL');
        assert.equal(item.mime, MIMES[item.source.split('.').at(-1)], 'unexpected MIME type');
        assert(Number.isSafeInteger(item.modified) && item.modified >= 0, 'invalid asset timestamp');
        const identityPath = 'identity/' + item.source;
        const identity = descriptor(generation, item.identity, identityPath, limitFile);
        assert.equal(identity.modified, item.modified, 'identity Last-Modified mismatch: ' + item.source);
        wanted.add(identityPath);
        total += identity.value.length;
        assert(total <= limitTotal, 'generation exceeds reviewed byte limit');
        if (item.gzip !== null) {
            const gzipPath = 'gzip/' + item.source + '.gz';
            const gzip = descriptor(generation, item.gzip, gzipPath, item.identity.bytes);
            assert.equal(gzip.modified, item.modified, 'gzip Last-Modified mismatch: ' + item.source);
            assert(gzip.value.length >= 18 && gzip.value.readUInt32BE(0) === 0x1f8b0800 &&
                gzip.value.readUInt32LE(4) === 0 && gzip.value[8] === 0 && gzip.value[9] === 255, 'unexpected gzip envelope');
            assert(item.identity.bytes - gzip.value.length >= c28.POLICY.minSavingBytes &&
                (item.identity.bytes - gzip.value.length) * 100 >= item.identity.bytes * c28.POLICY.minSavingPercent &&
                item.identity.bytes >= c28.POLICY.minBytes, 'gzip outside PERF-28 policy');
            const decoded = zlib.gunzipSync(gzip.value, { maxOutputLength: item.identity.bytes });
            assert(decoded.equals(identity.value), 'gzip decoded bytes mismatch: ' + item.source);
            wanted.add(gzipPath);
        }
    }
    assert.equal(viteNames.size, 0, 'generation is missing reviewed Vite assets');
    inventory(generation, wanted);
    return { generation, manifest, manifestSha256, viteManifestSha256: manifest.vite.sha256, status: 'VERIFIED_BYTES_ONLY' };
}

function assertFresh({ generation, publicRoot: input, expectedDigest } = {}) {
    const publicRoot = directory(absolute(input));
    const verified = verify({ generation, expectedDigest });
    const current = readPhysical(posix(publicRoot, SOURCE_MANIFEST), limitManifest);
    assert.equal(sha(current.value), verified.viteManifestSha256, 'source Vite manifest changed');
    for (const item of verified.manifest.assets) {
        const original = readPhysical(posix(publicRoot, item.source), limitFile);
        assert(original.modified === item.modified && original.value.length === item.identity.bytes &&
            sha(original.value) === item.identity.sha256, 'source asset changed: ' + item.source);
    }
    if (verified.manifest.scope === 'isolated-with-stable') {
        const want = [...STABLE].filter(f => exists(posix(publicRoot, f))).sort();
        assert.deepEqual(verified.manifest.assets.filter(item => item.class === 'stable').map(item => item.source),
            want, 'stable asset inventory changed');
    }
    return verified;
}

function closedWrite(generation, rel, bytes, modified) {
    const dest = boundedPath(posix(generation, rel));
    fs.mkdirSync(path.dirname(dest), { recursive: true, mode: 0o700 });
    fs.writeFileSync(dest, bytes, { flag: 'wx', mode: 0o600 });
    if (modified !== undefined) fs.utimesSync(dest, modified, modified);
}

function stage({ publicRoot: publicInput, releaseRoot: rootInput, includeStable = false } = {}) {
    assert.equal(typeof includeStable, 'boolean', 'invalid stable asset opt-in');
    const { publicRoot, releaseRoot } = roots(publicInput, rootInput);
    const generation = boundedPath(path.join(releaseRoot, '.stage-' + randomUUID().replaceAll('-', '')));
    fs.mkdirSync(generation, { mode: 0o700 });
    const scratch = path.join(generation, '.c28');
    // The existing reviewed builder is the only policy that chooses eligible
    // gzip variants. The scratch tree is never published or HTTP-addressable.
    try {
        const c = c28.build(publicRoot, scratch);
        const vite = readPhysical(posix(publicRoot, SOURCE_MANIFEST), limitManifest).value;
        const vitePin = sha(vite);
        assert.equal(vitePin, c.manifest.sourceManifest.sha256, 'Vite manifest changed during build');
        const viteNames = namesFromVite(vite);
        const assets = [];
        for (const item of c.manifest.entries) {
            const isVite = viteNames.has(item.source);
            if (!isVite) assert(STABLE.has(item.source), 'unexpected stable asset in PERF-28 output');
            if (!isVite && !includeStable) continue;
            const original = readPhysical(posix(publicRoot, item.source), limitFile);
            assert(original.value.length === item.bytes && sha(original.value) === item.sha256,
                'source asset changed during build: ' + item.source);
            const copy = {
                url: '/' + item.source, source: item.source, class: isVite ? 'vite' : 'stable',
                mime: MIMES[item.source.split('.').at(-1)], modified: original.modified,
                identity: assetRef('identity/' + item.source, item.sha256, item.bytes), gzip: null,
            };
            closedWrite(generation, copy.identity.path, original.value, copy.modified);
            if (item.gzip !== null) {
                const binary = readPhysical(posix(scratch, item.gzip.path), item.gzip.bytes);
                assert(binary.value.length === item.gzip.bytes && sha(binary.value) === item.gzip.sha256 &&
                    binary.modified === copy.modified, 'PERF-28 sidecar changed during build: ' + item.source);
                copy.gzip = assetRef('gzip/' + item.source + '.gz', item.gzip.sha256, item.gzip.bytes);
                closedWrite(generation, copy.gzip.path, binary.value, copy.modified);
            }
            assets.push(copy);
        }
        closedWrite(generation, STORED_VITE, vite);
        closedWrite(generation, STORED_MANIFEST, serial({
            schema: SCHEMA, scope: includeStable ? 'isolated-with-stable' : 'vite',
            vite: assetRef(STORED_VITE, vitePin, vite.length), assets,
        }));
    } finally {
        if (exists(scratch)) {
            // Only the canonical child of this invocation's exclusive staging
            // directory is removed; old content generations are never touched.
            directory(generation);
            directory(scratch);
            assert(samePath(path.dirname(scratch), generation) && STAGE_DIRECTORY.test(path.basename(generation)));
            fs.rmSync(scratch, { recursive: true });
        }
    }
    return assertFresh({ generation, publicRoot });
}

const releaseName = digest => 'gen-' + digest.slice(0, 32);

function prepare({ staged, releaseRoot: rootInput, publicRoot: publicInput } = {}) {
    const { publicRoot, releaseRoot } = roots(publicInput, rootInput);
    assert.match(staged?.manifestSha256, DIGEST, 'expected a pinned staged generation');
    const expectedDigest = staged.manifestSha256;
    const generation = directory(absolute(staged.generation));
    assert(samePath(path.dirname(generation), releaseRoot) && STAGE_DIRECTORY.test(path.basename(generation)),
        'expected a staging directory directly inside the release root');
    const lock = path.join(releaseRoot, '.publish-lock');
    // All publishers must use this exclusive lock within the trusted writer
    // root. Concurrent or interrupted publication fails closed, not unlocked
    // by a second process; administrative inspection decides stale lock removal.
    fs.mkdirSync(lock, { mode: 0o700 });
    try {
        const current = verify({ generation, expectedDigest });
        const target = boundedPath(path.join(releaseRoot, releaseName(expectedDigest)));
        assert(!exists(target), 'generation already exists; will not overwrite it');
        const identityPins = new Map(current.manifest.assets.filter(item => item.class === 'vite')
            .map(item => [item.url.toLowerCase(), item.identity.sha256]));
        for (const name of fs.readdirSync(releaseRoot)) {
            if (!RELEASE_DIRECTORY.test(name)) continue;
            const previous = verify({ generation: path.join(releaseRoot, name) });
            for (const asset of previous.manifest.assets) {
                const pin = identityPins.get(asset.url.toLowerCase());
                if (asset.class === 'vite' && pin !== undefined) assert.equal(asset.identity.sha256, pin,
                    'retained fingerprinted URL collision with changed bytes: ' + asset.url);
            }
        }
        // Same parent/filesystem, verified but unserved. The final generation
        // must be sealed in place so permissions are never weakened to move it.
        assertFresh({ generation, publicRoot, expectedDigest });
        fs.renameSync(generation, target);
        return { ...verify({ generation: target, expectedDigest }), status: 'UNSERVED_GENERATION' };
    } finally {
        directory(lock);
        fs.rmdirSync(lock);
    }
}

function publish({ prepared, releaseRoot: rootInput, publicRoot: publicInput, assertSealed } = {}) {
    assert.equal(typeof assertSealed, 'function', 'OS sealing assertion required');
    const { publicRoot, releaseRoot } = roots(publicInput, rootInput);
    assert.match(prepared?.manifestSha256, DIGEST, 'expected a pinned prepared generation');
    const expectedDigest = prepared.manifestSha256;
    const generation = directory(absolute(prepared.generation));
    assert(samePath(path.dirname(generation), releaseRoot) && path.basename(generation) === releaseName(expectedDigest),
        'expected the content-identified final path directly inside the release root');
    assert.equal(assertSealed({ generation, releaseRoot, manifestSha256: expectedDigest }), true,
        'independent OS sealing assertion did not pass');
    // No activation here. The caller must apply a single tested configuration
    // pointing at these exact pinned bytes, and retain all generations in use.
    return { ...assertFresh({ generation, publicRoot, expectedDigest }), status: 'SEALED_GENERATION_READY' };
}

module.exports = { stage, verify, assertFresh, prepare, publish, releaseName };

if (require.main === module) {
    try {
        const [mode, ...args] = process.argv.slice(2);
        let result;
        if (mode === 'stage' && (args.length === 4 || args.length === 5 && args[4] === '--isolated-with-stable')) {
            assert(args[0] === '--public' && args[2] === '--root', 'expected --public and --root');
            result = stage({ publicRoot: args[1], releaseRoot: args[3], includeStable: args.length === 5 });
        } else if (mode === 'prepare' && args.length === 8 && args[0] === '--staging' && args[2] === '--sha256' &&
            args[4] === '--public' && args[6] === '--root') {
            result = prepare({ staged: { generation: args[1], manifestSha256: args[3] },
                publicRoot: args[5], releaseRoot: args[7] });
        } else if (mode === 'verify' && args.length === 4 && args[0] === '--generation' && args[2] === '--sha256') {
            result = verify({ generation: args[1], expectedDigest: args[3] });
        } else {
            throw Error('usage: immutable-release.cjs stage --public ABS --root ABS [--isolated-with-stable] | ' +
                'prepare --staging ABS --sha256 HEX --public ABS --root ABS | verify --generation ABS --sha256 HEX');
        }
        process.stdout.write(JSON.stringify(result) + '\n');
    } catch (error) {
        process.stderr.write(JSON.stringify({ status: 'FAIL', error: error.message }) + '\n');
        process.exitCode = 1;
    }
}
