'use strict';

const assert = require('node:assert/strict');
const { createHash } = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');
const zlib = require('node:zlib');
const { TextDecoder } = require('node:util');

const SCHEMA = 'gramlyze-perf28-sidecars-v1';
const VITE_MANIFEST = 'build/manifest.json';
const POLICY = Object.freeze({ minBytes: 1024, minSavingBytes: 256, minSavingPercent: 10, gzipLevel: 6 });
const VITE_ROOTS = Object.freeze({
    'resources/css/app.css': 'css',
    'resources/css/catalog-public.css': 'css',
    'resources/js/app.js': 'js',
    'resources/js/catalog-public.js': 'js',
});
const PUBLIC_ALLOWLIST = Object.freeze([
    'js/theory-search.js', 'js/theory-mixed-test.js', 'js/theory-menu.js',
    'js/theory-course-progress.js', 'js/polyglot-course-progress.js', 'js/english-answer-variants.js',
    'js/authored-practice-ui.js', 'js/m39-practice-ui.js', 'js/m40-practice-ui.js',
    'js/m41-practice-ui.js', 'js/m43-practice-ui.js', 'js/m44-practice-ui.js', 'js/m45-practice-ui.js',
    'engram-language-pattern.svg', 'gramlyze-background.svg', 'gramlyze-background-cards.svg', 'favicon.svg',
]);
const MAX_MANIFEST_BYTES = 2 * 1024 * 1024;
const MAX_SOURCE_BYTES = 32 * 1024 * 1024;
const MAX_TOTAL_SOURCE_BYTES = 64 * 1024 * 1024;
const MAX_SOURCES = 128;
const SAFE_VITE_ASSET = /^assets\/[A-Za-z0-9_-]{1,160}(?:\.[A-Za-z0-9_-]{1,30})+$/;
const MIME = Object.freeze({ css: 'text/css', js: 'application/javascript', mjs: 'application/javascript', svg: 'image/svg+xml' });
const sha = bytes => createHash('sha256').update(bytes).digest('hex');
const sha1 = bytes => createHash('sha1').update(bytes).digest('hex');
const text = new TextDecoder('utf-8', { fatal: true });
const serial = value => Buffer.from(JSON.stringify(value, null, 2) + '\n', 'utf8');
const samePath = (a, b) => process.platform === 'win32' ? a.toLowerCase() === b.toLowerCase() : a === b;
const seconds = stat => Number(stat.mtimeNs / 1000000000n);
const posix = (root, rel) => path.join(root, ...rel.split('/'));

function portableFile(file) {
    if (process.platform === 'win32') assert(file.length <= 240, 'path exceeds Windows Apache build budget: '+file);
    return file;
}

function absolute(value, description) {
    assert(typeof value === 'string' && path.isAbsolute(value), description+' must be absolute');
    return portableFile(path.resolve(value));
}

function realDirectory(file, description) {
    const stat = fs.lstatSync(file);
    assert(stat.isDirectory() && !stat.isSymbolicLink() && samePath(fs.realpathSync.native(file), file),
        description+' must be a physical, canonical directory: '+file);
    return file;
}

function relativeInside(root, target) {
    const rel = path.relative(root, target);
    return rel !== '..' && !rel.startsWith('..'+path.sep) && !path.isAbsolute(rel);
}

function locations(publicInput, outInput, existing) {
    const publicRoot = realDirectory(absolute(publicInput, 'public root'), 'public root');
    const out = absolute(outInput, 'generation');
    realDirectory(path.dirname(out), 'generation parent');
    assert(!relativeInside(publicRoot, out) && !relativeInside(out, publicRoot),
        'generation must be outside the public tree and must not contain it');
    if (existing) realDirectory(out, 'generation');
    return { publicRoot, out };
}

function readPhysical(file, limit) {
    portableFile(file);
    const before = fs.lstatSync(file, { bigint: true });
    assert(before.isFile() && !before.isSymbolicLink() && samePath(fs.realpathSync.native(file), file),
        'expected a physical regular file: '+file);
    assert(before.size <= BigInt(limit), 'file exceeds safe build limit: '+file);
    const flags = fs.constants.O_RDONLY | (process.platform === 'win32' ? 0 : (fs.constants.O_NOFOLLOW || 0));
    const fd = fs.openSync(file, flags);
    const identity = ['ino', 'size', 'mtimeNs', 'ctimeNs'];
    const same = (a, b) => {
        // Windows path stat can omit the device serial in managed folders,
        // while fstat reports it. Still compare inode, times and sizes.
        const deviceKnown = a.dev !== 0n && b.dev !== 0n;
        return (a.dev === b.dev || (process.platform === 'win32' && !deviceKnown)) &&
            identity.every(key => a[key] === b[key]);
    };
    try {
        const opened = fs.fstatSync(fd, { bigint: true });
        assert(opened.isFile() && same(before, opened), 'file changed before reading: '+file);
        const bytes = fs.readFileSync(fd);
        assert(BigInt(bytes.length) === opened.size && same(opened, fs.fstatSync(fd, { bigint: true })) &&
            same(opened, fs.lstatSync(file, { bigint: true })), 'file changed during read: '+file);
        return { bytes, modified: seconds(opened) };
    } finally { fs.closeSync(fd); }
}

function optionalFileExists(file) {
    try { fs.lstatSync(file); return true; }
    catch (e) { if (e.code === 'ENOENT') return false; throw e; }
}

function manifestAsset(asset, name) {
    assert(typeof asset === 'string' && SAFE_VITE_ASSET.test(asset), 'unsafe Vite '+name+' path');
    return asset;
}

function recordMap(sourceBytes) {
    const manifest = JSON.parse(text.decode(sourceBytes));
    assert(manifest && Object.getPrototypeOf(manifest) === Object.prototype, 'Vite manifest must be an object');
    const entries = Object.entries(manifest);
    assert(entries.length > 0 && entries.length <= 1024, 'Vite manifest inventory is empty or too large');
    const files = new Map();
    for (const [key, value] of entries) {
        assert(key.length > 0 && key.length <= 512 && !/[\x00-\x1f\x7f]/.test(key), 'invalid Vite manifest key');
        assert(value && Object.getPrototypeOf(value) === Object.prototype, 'invalid Vite manifest record: '+key);
        const f = manifestAsset(value.file, 'file');
        const old = files.get(f.toLowerCase());
        assert(!old || old === f, 'case-colliding Vite filenames');
        files.set(f.toLowerCase(), f);
        for (const prop of ['css', 'assets', 'imports', 'dynamicImports']) {
            if (!Object.hasOwn(value, prop)) continue;
            assert(Array.isArray(value[prop]) && value[prop].length <= 1024, 'invalid Vite '+prop+' list: '+key);
            for (const item of value[prop]) {
                if (prop.endsWith('mports')) {
                    assert(typeof item === 'string' && Object.hasOwn(manifest, item), 'missing Vite '+prop+' record');
                } else {
                    const referencedFile = manifestAsset(item, prop);
                    const oldReference = files.get(referencedFile.toLowerCase());
                    assert(!oldReference || oldReference === referencedFile, 'case-colliding Vite filenames');
                    files.set(referencedFile.toLowerCase(), referencedFile);
                    if (prop === 'css') assert(referencedFile.endsWith('.css'), 'non-CSS file in Vite CSS list');
                }
            }
        }
    }
    return manifest;
}

function sourcePaths(publicRoot, sourceManifest) {
    const manifest = recordMap(sourceManifest);
    const sources = new Set();
    const visited = new Set();
    function visit(key) {
        if (visited.has(key)) return;
        visited.add(key);
        const record = manifest[key];
        assert(/\.(?:m?js|css)$/.test(record.file), 'reviewed Vite dependency is not CSS/JS: '+key);
        sources.add('build/'+record.file);
        for (const item of record.css || []) sources.add('build/'+item);
        for (const prop of ['imports', 'dynamicImports']) for (const child of record[prop] || []) visit(child);
    }
    for (const [entry, extension] of Object.entries(VITE_ROOTS)) {
        if (!Object.hasOwn(manifest, entry)) continue;
        const current = manifest[entry];
        assert(current.isEntry === true && (extension === 'css' ? current.file.endsWith('.css') : /\.m?js$/.test(current.file)),
            'invalid known Vite entry: '+entry);
        if (Object.hasOwn(current, 'src')) assert(current.src === entry, 'mismatched Vite entry src: '+entry);
        visit(entry);
    }
    assert(visited.size > 0, 'Vite manifest contains no reviewed entry point');
    for (const rel of PUBLIC_ALLOWLIST) {
        if (optionalFileExists(posix(publicRoot, rel))) sources.add(rel);
    }
    assert(sources.size <= MAX_SOURCES, 'too many approved assets');
    return [...sources].sort();
}

function gzipFor(raw) {
    const gzip = zlib.gzipSync(raw, {
        level: POLICY.gzipLevel, strategy: zlib.constants.Z_DEFAULT_STRATEGY, memLevel: 8, windowBits: 15,
    });
    assert(gzip.length >= 18 && gzip.readUInt32BE(0) === 0x1f8b0800 &&
        gzip.readUInt32LE(4) === 0 && gzip[8] === 0, 'unexpected gzip header');
    // Standardize the optional OS marker; the deflated payload and footer are unchanged.
    gzip[9] = 255;
    return gzip;
}

function verifyGzip(gzip, entry) {
    assert(gzip.length >= 18 && gzip.readUInt32BE(0) === 0x1f8b0800 && gzip.readUInt32LE(4) === 0 &&
        gzip[8] === 0 && gzip[9] === 255, 'non-canonical gzip header: '+entry.source);
    const decoded = zlib.gunzipSync(gzip, { maxOutputLength: entry.bytes });
    assert(decoded.length === entry.bytes && sha(decoded) === entry.sha256, 'decoded gzip SHA-256 mismatch: '+entry.source);
}

function plan(publicRoot) {
    const sourceManifest = readPhysical(posix(publicRoot, VITE_MANIFEST), MAX_MANIFEST_BYTES).bytes;
    let totalBytes = 0;
    const assets = sourcePaths(publicRoot, sourceManifest).map(source => {
        const original = readPhysical(posix(publicRoot, source), MAX_SOURCE_BYTES);
        // Apache's runtime file() check works on text. Never feed it a file
        // with an embedded string terminator or invalid UTF-8.
        assert(!original.bytes.includes(0), 'NUL byte in reviewed text asset: '+source);
        try { text.decode(original.bytes); }
        catch { throw new TypeError('invalid UTF-8 reviewed text asset: '+source); }
        totalBytes += original.bytes.length;
        assert(totalBytes <= MAX_TOTAL_SOURCE_BYTES, 'source inventory exceeds safe build limit');
        const entry = {
            url: '/'+source,
            source,
            sha256: sha(original.bytes),
            // Compatibility with Apache ap_expr only; this is not an
            // integrity proof. Build and activation always verify SHA-256.
            sha1: sha1(original.bytes),
            bytes: original.bytes.length,
            mime: MIME[source.slice(source.lastIndexOf('.')+1)],
            gzip: null,
        };
        assert(entry.mime, 'unknown reviewed MIME: '+source);
        let gzip = null;
        if (entry.bytes >= POLICY.minBytes) {
            const compressed = gzipFor(original.bytes);
            const saving = entry.bytes - compressed.length;
            if (saving >= POLICY.minSavingBytes && saving*100 >= entry.bytes*POLICY.minSavingPercent) {
                gzip = compressed;
                entry.gzip = { path: 'assets/'+source+'.gz', sha256: sha(gzip), bytes: gzip.length };
                verifyGzip(gzip, entry);
            }
        }
        return { entry, gzip, modified: original.modified };
    });
    const manifest = {
        schema: SCHEMA,
        policy: POLICY,
        sourceManifest: { path: VITE_MANIFEST, sha256: sha(sourceManifest) },
        entries: assets.map(item => item.entry),
    };
    return { manifest, assets };
}

function checkInventory(out, expectedFiles) {
    const directories = new Set();
    for (const file of expectedFiles) {
        const parts = file.split('/');
        for (let i = 1; i < parts.length; i++) directories.add(parts.slice(0, i).join('/'));
    }
    const files = new Set();
    function visit(folder) {
        for (const entry of fs.readdirSync(posix(out, folder), { withFileTypes: true })) {
            const item = folder ? folder+'/'+entry.name : entry.name;
            if (entry.isDirectory()) {
                assert(directories.has(item), 'unexpected generation directory: '+item);
                realDirectory(posix(out, item), 'generation directory');
                visit(item);
            } else {
                assert(entry.isFile() && expectedFiles.has(item), 'unexpected generation file: '+item);
                files.add(item);
            }
        }
    }
    visit('');
    assert(files.size === expectedFiles.size, 'incomplete generation: missing file');
}

function verify(publicInput, outInput) {
    const { publicRoot, out } = locations(publicInput, outInput, true);
    const { manifest, assets } = plan(publicRoot);
    const expected = serial(manifest);
    const selected = assets.filter(asset => asset.entry.gzip);
    checkInventory(out, new Set(['manifest.json', ...selected.map(({entry}) => entry.gzip.path)]));
    const actual = readPhysical(path.join(out, 'manifest.json'), MAX_MANIFEST_BYTES).bytes;
    assert(expected.equals(actual), 'stale, modified or non-canonical generation manifest');
    for (const { entry, gzip, modified } of selected) {
        const artifact = readPhysical(posix(out, entry.gzip.path), entry.gzip.bytes);
        assert(artifact.modified === modified, 'sidecar Last-Modified differs from source: '+entry.source);
        verifyGzip(artifact.bytes, entry);
        assert(artifact.bytes.length === entry.gzip.bytes && sha(artifact.bytes) === entry.gzip.sha256 && gzip.equals(artifact.bytes),
            'sidecar gzip SHA-256 differs: '+entry.source);
    }
    return { manifest, proof: {
        status: 'PASS',
        schema: SCHEMA,
        manifestSha256: sha(actual),
        sourceManifestSha256: manifest.sourceManifest.sha256,
        reviewedFiles: assets.length,
        compressedFiles: selected.length,
        allOriginalBytes: assets.reduce((sum, {entry}) => sum+entry.bytes, 0),
        eligibleBytes: selected.reduce((sum, {entry}) => sum+entry.bytes, 0),
        gzipBytes: selected.reduce((sum, {entry}) => sum+entry.gzip.bytes, 0),
    } };
}

function build(publicInput, outInput) {
    const { publicRoot, out } = locations(publicInput, outInput, false);
    const { manifest, assets } = plan(publicRoot);
    portableFile(path.join(out, 'manifest.json'));
    for (const { entry } of assets) if (entry.gzip) portableFile(posix(out, entry.gzip.path));
    // Exclusive mkdir prevents appending to, overwriting, or reusing a release.
    // A failed build can leave an incomplete directory; it cannot verify.
    fs.mkdirSync(out, { mode: 0o700 });
    for (const { entry, gzip, modified } of assets) {
        if (!gzip) continue;
        const dest = posix(out, entry.gzip.path);
        fs.mkdirSync(path.dirname(dest), { recursive: true, mode: 0o700 });
        fs.writeFileSync(dest, gzip, { flag: 'wx', mode: 0o600 });
        fs.utimesSync(dest, modified, modified);
    }
    fs.writeFileSync(path.join(out, 'manifest.json'), serial(manifest), { flag: 'wx', mode: 0o600 });
    return verify(publicRoot, out);
}

function cli(args) {
    assert(args.length === 5 && ['build', 'verify'].includes(args[0]) && args[1] === '--public' && args[3] === '--out',
        'usage: node precompress.cjs (build|verify) --public <absolute-public> --out <absolute-generation>');
    const operation = args[0];
    const { proof } = (operation === 'build' ? build : verify)(args[2], args[4]);
    process.stdout.write(JSON.stringify({ operation, ...proof })+'\n');
}

module.exports = { build, verify, POLICY };
if (require.main === module) {
    try { cli(process.argv.slice(2)); }
    catch (error) {
        process.stderr.write(JSON.stringify({ status: 'FAIL', error: error.message })+'\n');
        process.exitCode = 1;
    }
}
