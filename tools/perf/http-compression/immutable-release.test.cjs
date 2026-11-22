'use strict';

const assert = require('node:assert/strict');
const { createHash } = require('node:crypto');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const test = require('node:test');
const zlib = require('node:zlib');
const { stage, verify, assertFresh, prepare, publish, releaseName } = require('./immutable-release.cjs');

const entry = 'resources/js/app.js';
const oldName = 'assets/app-Ab3dEf90.js';
const newName = 'assets/app-Bc4eFg01.js';
const cssName = 'assets/style-Cd5fGh12.css';
const dependencyName = 'assets/dependency-De6gHi23.mjs';
const knownStable = 'js/theory-search.js';
const stamp = 1672531200;
const bytes = Buffer.from('const existing = 1;\n'.repeat(128));
const relative = (root, name) => path.join(root, ...name.split('/'));
const digest = input => createHash('sha256').update(input).digest('hex');

function write(root, name, contents, modified = stamp) {
    const file = relative(root, name);
    fs.mkdirSync(path.dirname(file), { recursive: true });
    fs.writeFileSync(file, contents);
    fs.utimesSync(file, modified, modified);
    return file;
}

function fixture(t) {
    const parent = fs.realpathSync.native(os.tmpdir());
    const root = fs.mkdtempSync(path.join(parent, 'glz29-'));
    const publicRoot = path.join(root, 'public');
    const releaseRoot = path.join(root, 'releases');
    fs.mkdirSync(releaseRoot);
    t.after(() => {
        const target = fs.realpathSync.native(root);
        assert.equal(path.dirname(target), parent);
        assert.match(path.basename(target), /^glz29-[a-z0-9]+$/i);
        fs.rmSync(target, { recursive: true });
    });
    const setManifest = (name = oldName) => write(publicRoot, 'build/manifest.json', JSON.stringify({
        [entry]: { file: name, isEntry: true, imports: ['_dependency.js'], css: [cssName] },
        '_dependency.js': { file: dependencyName },
        'resources/js/unreviewed.js': { file: 'assets/unreviewed-Ef7hIj34.js', isEntry: true },
    }));
    setManifest();
    for (const file of [oldName, cssName, 'assets/unreviewed-Ef7hIj34.js', oldName + '.map']) {
        write(publicRoot, 'build/' + file, bytes);
    }
    write(publicRoot, 'build/' + dependencyName, 'export const tiny = 1;');
    write(publicRoot, knownStable, bytes);
    write(publicRoot, 'gramlyze-background.svg', '<svg>' + ' '.repeat(2048) + '</svg>');
    write(publicRoot, 'js/not-reviewed.js', bytes);
    const options = { publicRoot, releaseRoot };
    return {
        root, publicRoot, releaseRoot, setManifest, options,
        stage: (includeStable = false) => stage({ ...options, includeStable }),
        prepare: staged => prepare({ ...options, staged }),
    };
}

test('stages deterministic exact identity/gzip pairs, Vite metadata and Last-Modified with no stable URLs by default', t => {
    const x = fixture(t);
    const a = x.stage(), b = x.stage();
    assert.notEqual(a.generation, b.generation);
    assert.equal(a.status, 'VERIFIED_BYTES_ONLY');
    assert.equal(a.manifestSha256, b.manifestSha256);
    assert.equal(a.manifest.scope, 'vite');
    assert.deepEqual(a.manifest.assets.map(asset => asset.url), [
        '/build/' + oldName, '/build/' + dependencyName, '/build/' + cssName,
    ]);
    const storedVite = fs.readFileSync(relative(a.generation, a.manifest.vite.path));
    assert.equal(digest(storedVite), a.viteManifestSha256);
    assert.deepEqual(storedVite, fs.readFileSync(relative(x.publicRoot, 'build/manifest.json')));
    for (const item of a.manifest.assets) {
        assert.equal(item.class, 'vite');
        assert.equal(item.modified, stamp);
        const identityPath = relative(a.generation, item.identity.path);
        const original = fs.readFileSync(identityPath);
        assert.equal(digest(original), item.identity.sha256);
        assert.equal(original.length, item.identity.bytes);
        assert.deepEqual(original, fs.readFileSync(relative(x.publicRoot, item.source)));
        assert.equal(Math.floor(fs.statSync(identityPath).mtimeMs / 1000), stamp);
        if (item.source === 'build/' + dependencyName) {
            assert.equal(item.gzip, null);
            continue;
        }
        const gzipPath = relative(a.generation, item.gzip.path);
        const packed = fs.readFileSync(gzipPath);
        assert.equal(digest(packed), item.gzip.sha256);
        assert.equal(packed.length, item.gzip.bytes);
        assert.deepEqual(zlib.gunzipSync(packed), original);
        assert.equal(Math.floor(fs.statSync(gzipPath).mtimeMs / 1000), stamp);
        assert.deepEqual(packed, fs.readFileSync(relative(b.generation, item.gzip.path)));
        assert(!fs.existsSync(relative(x.publicRoot, item.source + '.gz')));
    }
    assert.deepEqual(verify({ generation: a.generation, expectedDigest: a.manifestSha256 }), a);
});

test('opt-in isolated snapshot includes exact reviewed stable files and rejects stale inventory', t => {
    const x = fixture(t);
    const snapshot = x.stage(true);
    assert.equal(snapshot.manifest.scope, 'isolated-with-stable');
    const stable = snapshot.manifest.assets.filter(item => item.class === 'stable');
    assert.deepEqual(stable.map(item => item.url), ['/gramlyze-background.svg', '/' + knownStable]);
    assert.deepEqual(stable.map(item => item.mime), ['image/svg+xml', 'text/javascript']);
    write(x.publicRoot, 'js/theory-mixed-test.js', bytes);
    assert.throws(() => x.prepare(snapshot), /stable asset inventory changed/);
    assert(fs.existsSync(snapshot.generation));
});

test('rejects identity, gzip and Vite corruption even when sizes and timestamps agree', t => {
    const x = fixture(t);
    const prepared = x.prepare(x.stage());
    const check = () => verify({ generation: prepared.generation, expectedDigest: prepared.manifestSha256 });
    const item = prepared.manifest.assets[0];
    for (const ref of [item.identity, item.gzip, prepared.manifest.vite]) {
        const old = fs.readFileSync(relative(prepared.generation, ref.path));
        const modified = Math.floor(fs.statSync(relative(prepared.generation, ref.path)).mtimeMs / 1000);
        const changed = Buffer.from(old);
        changed[changed.length - 1] ^= 1;
        write(prepared.generation, ref.path, changed, modified);
        assert.throws(check, /integrity mismatch/);
        write(prepared.generation, ref.path, old, modified);
        assert.equal(check().status, 'VERIFIED_BYTES_ONLY');
    }
    assert.throws(() => verify({ generation: prepared.generation, expectedDigest: '0'.repeat(64) }), /pin mismatch/);
});

test('rejects an unexpected physical path even if a copied manifest purports to pin it', t => {
    const x = fixture(t);
    const staged = x.stage();
    staged.manifest.assets[0].identity.path = '../public/build/' + oldName;
    const manifest = JSON.stringify(staged.manifest, null, 2) + '\n';
    write(staged.generation, 'manifest.json', manifest);
    assert.throws(() => verify({ generation: staged.generation, expectedDigest: digest(manifest) }),
        /unexpected physical asset path/);
});

test('rejects gzip whose bytes and size are correctly pinned but whose decoded content differs', t => {
    const x = fixture(t);
    const staged = x.stage();
    const item = staged.manifest.assets[0];
    const different = Buffer.from(bytes);
    different[1] ^= 1;
    const packed = zlib.gzipSync(different, { level: 6 });
    packed[9] = 255;
    item.gzip.sha256 = digest(packed);
    item.gzip.bytes = packed.length;
    write(staged.generation, item.gzip.path, packed);
    const manifest = JSON.stringify(staged.manifest, null, 2) + '\n';
    write(staged.generation, 'manifest.json', manifest);
    assert.throws(() => verify({ generation: staged.generation, expectedDigest: digest(manifest) }),
        /gzip decoded bytes mismatch/);
});

test('rejects missing, extra and hardlinked generation entries', t => {
    const x = fixture(t);
    const staged = x.stage();
    const check = () => verify({ generation: staged.generation, expectedDigest: staged.manifestSha256 });
    const item = staged.manifest.assets[0];
    const gzip = relative(staged.generation, item.gzip.path);
    const oldGzip = fs.readFileSync(gzip);
    fs.unlinkSync(gzip);
    assert.throws(check, { code: 'ENOENT' });
    write(staged.generation, item.gzip.path, oldGzip);
    const extra = write(staged.generation, 'extra.js', bytes);
    assert.throws(check, /unexpected generation file/);
    fs.unlinkSync(extra);
    const hardlink = path.join(x.root, 'external-link');
    fs.linkSync(relative(staged.generation, item.identity.path), hardlink);
    assert.throws(check, /no symbolic\/hard links/);
    fs.unlinkSync(hardlink);
    assert.equal(check().status, 'VERIFIED_BYTES_ONLY');
});

test('rejects a linked directory in an otherwise valid generation', t => {
    const x = fixture(t);
    const staged = x.stage();
    const original = path.join(staged.generation, 'identity');
    const outside = path.join(x.root, 'other-identity');
    fs.renameSync(original, outside);
    fs.symlinkSync(outside, original, process.platform === 'win32' ? 'junction' : 'dir');
    assert.throws(() => verify({ generation: staged.generation, expectedDigest: staged.manifestSha256 }),
        /expected canonical regular file|unexpected generation file\/link/);
});

test('rejects a release tree inside public or a nonfingerprinted Vite asset', t => {
    const x = fixture(t);
    const inside = path.join(x.publicRoot, 'releases');
    fs.mkdirSync(inside);
    assert.throws(() => stage({ publicRoot: x.publicRoot, releaseRoot: inside }), /outside the public tree/);
    x.setManifest('assets/stable.js');
    write(x.publicRoot, 'build/assets/stable.js', bytes);
    assert.throws(() => x.stage(), /safe fingerprinted path/);
});

test('checks source freshness immediately before preparation, including same-size same-time edits', t => {
    const x = fixture(t);
    const staged = x.stage();
    const changed = Buffer.from(bytes);
    changed[0] ^= 1;
    write(x.publicRoot, 'build/' + oldName, changed);
    assert.throws(() => x.prepare(staged), /source asset changed/);
    assert(!fs.existsSync(path.join(x.releaseRoot, releaseName(staged.manifestSha256))));
    write(x.publicRoot, 'build/' + oldName, bytes);
    x.setManifest(newName);
    assert.throws(() => assertFresh({ ...x.options, generation: staged.generation }), /source Vite manifest changed/);
});

test('uses exclusive no-overwrite preparation and retains previous ready bytes across a new generation', t => {
    const x = fixture(t);
    const a = x.stage();
    const b = x.stage();
    const lock = path.join(x.releaseRoot, '.publish-lock');
    fs.mkdirSync(lock);
    assert.throws(() => x.prepare(a), { code: 'EEXIST' });
    fs.rmdirSync(lock);
    const r = x.prepare(a);
    assert.equal(r.status, 'UNSERVED_GENERATION');
    assert.equal(path.basename(r.generation), releaseName(a.manifestSha256));
    assert(!fs.existsSync(a.generation));
    assert.throws(() => x.prepare(b), /will not overwrite/);
    assert(fs.existsSync(b.generation));
    write(x.publicRoot, 'build/' + newName, bytes);
    x.setManifest(newName);
    const next = x.prepare(x.stage());
    assert.notEqual(r.generation, next.generation);
    assert.equal(verify({ generation: r.generation }).manifestSha256, r.manifestSha256);
});

test('rejects changed bytes at a retained fingerprinted URL', t => {
    const x = fixture(t);
    const old = x.prepare(x.stage());
    write(x.publicRoot, 'build/' + oldName, Buffer.from('let n = 2;\n'.repeat(512)));
    const staged = x.stage();
    assert.throws(() => x.prepare(staged), /retained fingerprinted URL collision/);
    assert.equal(verify({ generation: old.generation }).manifestSha256, old.manifestSha256);
});

test('post-seal gate uses final path, requires an explicit independent success and never modifies files', t => {
    const x = fixture(t);
    const staged = x.stage();
    assert.throws(() => publish({ ...x.options, prepared: staged, assertSealed: () => true }), /final path/);
    const prepared = x.prepare(staged);
    assert.throws(() => publish({ ...x.options, prepared }), /OS sealing assertion required/);
    assert.throws(() => publish({ ...x.options, prepared, assertSealed: () => false }), /did not pass/);
    assert.throws(() => publish({ ...x.options, prepared, assertSealed: async () => true }), /did not pass/);
    // Test double only: Node cannot prove filesystem immutability; the actual
    // Apache consumer must independently check the documented OS access policy.
    const gated = publish({ ...x.options, prepared, assertSealed: proof => {
        assert.deepEqual(proof, { generation: prepared.generation, releaseRoot: x.releaseRoot,
            manifestSha256: prepared.manifestSha256 });
        return true;
    } });
    assert.equal(gated.status, 'SEALED_GENERATION_READY');
    assert.equal(verify({ generation: prepared.generation }).manifestSha256, prepared.manifestSha256);
});
