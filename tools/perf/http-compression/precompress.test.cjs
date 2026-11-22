'use strict';

const assert = require('node:assert/strict');
const { spawnSync } = require('node:child_process');
const { createHash } = require('node:crypto');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const test = require('node:test');
const zlib = require('node:zlib');
const { build, verify, POLICY } = require('./precompress.cjs');

const entryKey = 'resources/css/app.css';
const appAsset = 'build/assets/app-AAAA.css';
const oldManifest = () => ({ [entryKey]: { file: 'assets/app-AAAA.css', src: entryKey, isEntry: true } });
const sha = (bytes, algorithm = 'sha256') => createHash(algorithm).update(bytes).digest('hex');
const fileAt = (r, rel) => path.join(r, ...rel.split('/'));
const stamp = 1672531200;

function write(root, rel, data) {
    const filename = fileAt(root, rel);
    fs.mkdirSync(path.dirname(filename), { recursive: true });
    fs.writeFileSync(filename, data);
    fs.utimesSync(filename, stamp, stamp);
    return filename;
}

function fixture(t) {
    const parent = fs.realpathSync.native(os.tmpdir());
    const temp = fs.mkdtempSync(path.join(parent, 'glz-p28-'));
    const publicRoot = path.join(temp, 'public');
    fs.mkdirSync(publicRoot);
    t.after(() => {
        const candidate = fs.realpathSync.native(temp);
        assert.equal(path.dirname(candidate).toLowerCase(), parent.toLowerCase());
        assert.match(path.basename(candidate), /^glz-p28-[A-Za-z0-9]+$/);
        fs.rmSync(candidate, { recursive:true, force:true });
    });
    const setManifest = obj => write(publicRoot, 'build/manifest.json', JSON.stringify(obj));
    setManifest(oldManifest());
    write(publicRoot, appAsset, '/* '+('CSS > body { color: rebeccapurple; }\n'.repeat(90))+' */');
    return { temp, publicRoot, out: path.join(temp, 'generation'), setManifest };
}

test('builds only reviewed manifest roots, their JS/CSS graph and exact public filenames', t => {
    const x = fixture(t);
    const jsKey = 'resources/js/catalog-public.js';
    const manifest = oldManifest();
    manifest[entryKey].assets = ['assets/logo-AAAA.svg'];
    manifest[jsKey] = { file:'assets/catalog-AAAA.js', isEntry:true, imports:['_shared.js'], dynamicImports:['chunks/lazy.js'] };
    manifest['_shared.js'] = { file:'assets/shared-AAAA.js', css:['assets/dep-AAAA.css'], imports:['chunks/lazy.js'] };
    manifest['chunks/lazy.js'] = { file:'assets/lazy-AAAA.mjs', imports:['_shared.js'] };
    manifest['resources/js/unapproved.js'] = { file:'assets/unused-AAAA.js', isEntry:true };
    x.setManifest(manifest);
    for (const file of ['catalog-AAAA.js','shared-AAAA.js','lazy-AAAA.mjs','unused-AAAA.js','dep-AAAA.css','logo-AAAA.svg',
        'unlisted-AAAA.css','shared-AAAA.js.map']) write(x.publicRoot,'build/assets/'+file,'abc\n'.repeat(400));
    for (const rel of ['js/theory-search.js','gramlyze-background.svg','robots.txt','storage/unlisted.js','uploads/unlisted.svg','admin.html',
        'js/unlisted.js','img/cover.webp']) write(x.publicRoot,rel,'abc\n'.repeat(400));
    const result=build(x.publicRoot,x.out);
    assert.deepEqual(result.manifest.entries.map(a=>a.source),[
        'build/assets/app-AAAA.css', 'build/assets/catalog-AAAA.js','build/assets/dep-AAAA.css',
        'build/assets/lazy-AAAA.mjs','build/assets/shared-AAAA.js','gramlyze-background.svg','js/theory-search.js',
    ]);
    for (const entry of result.manifest.entries) {
        const original = fs.readFileSync(fileAt(x.publicRoot, entry.source));
        assert.equal(entry.sha256, sha(original));
        assert.equal(entry.sha1, sha(original, 'sha1'));
        const gzip = fs.readFileSync(fileAt(x.out, entry.gzip.path));
        assert.equal(gzip.readUInt32LE(4),0);
        assert.equal(gzip[9],255);
        assert.equal(entry.gzip.sha256,sha(gzip));
        assert.deepEqual(zlib.gunzipSync(gzip),original);
        assert.equal(Math.floor(fs.statSync(fileAt(x.out,entry.gzip.path)).mtimeMs/1000),stamp);
        assert.equal(Math.floor(fs.statSync(fileAt(x.publicRoot,entry.source)).mtimeMs/1000),stamp);
        assert(!fs.existsSync(fileAt(x.publicRoot,entry.source+'.gz')));
    }
    assert.deepEqual(verify(x.publicRoot,x.out),result);
    assert.throws(()=>build(x.publicRoot,x.out),{code:'EEXIST'});
    assert.deepEqual(verify(x.publicRoot,x.out),result);
});

test('the 1024-byte gate and minimum saving omit undersized or unhelpful text files', t => {
    const x = fixture(t);
    const manifest = oldManifest();
    manifest[entryKey].css = ['assets/just-small.css','assets/beneficial.css','assets/low-benefit.css'];
    x.setManifest(manifest);
    const lowBenefit = Buffer.concat(Array.from({length:32},(_,i)=>createHash('sha256').update('fixture:'+i).digest()))
        .toString('base64').slice(0,POLICY.minBytes);
    assert(POLICY.minBytes - zlib.gzipSync(lowBenefit, {level:6}).length < POLICY.minSavingBytes);
    write(x.publicRoot,'build/assets/just-small.css','x'.repeat(POLICY.minBytes-1));
    write(x.publicRoot,'build/assets/beneficial.css','x'.repeat(POLICY.minBytes));
    write(x.publicRoot,'build/assets/low-benefit.css',lowBenefit);
    const {manifest:output, proof}=build(x.publicRoot,x.out);
    const files=new Map(output.entries.map(a=>[a.source,a]));
    assert.equal(files.get('build/assets/just-small.css').gzip,null);
    assert.equal(files.get('build/assets/low-benefit.css').gzip,null);
    assert(files.get('build/assets/beneficial.css').gzip);
    assert.deepEqual([proof.reviewedFiles,proof.compressedFiles],[4,2]);
});

test('gzip bytes and generation manifest do not depend on output path or source creation time', t => {
    const x = fixture(t);
    const a=build(x.publicRoot,x.out), source=fileAt(x.publicRoot,appAsset), entry=a.manifest.entries[0];
    fs.utimesSync(source,stamp+10,stamp+10);
    assert.throws(()=>verify(x.publicRoot,x.out),/Last-Modified/);
    const second=path.join(x.temp,'second');
    const b=build(x.publicRoot,second);
    assert.equal(a.proof.manifestSha256,b.proof.manifestSha256);
    assert.deepEqual(fs.readFileSync(fileAt(x.out,entry.gzip.path)),fs.readFileSync(fileAt(second,entry.gzip.path)));
    assert.equal(Math.floor(fs.statSync(fileAt(second,entry.gzip.path)).mtimeMs/1000),stamp+10);
});

test('verify rejects stale content even with the same size and mtime, then an updated Vite manifest', t => {
    const x = fixture(t);
    build(x.publicRoot,x.out);
    const file=fileAt(x.publicRoot,appAsset), old=fs.readFileSync(file);
    const changed=Buffer.from(old);
    changed[3]=changed[3]===0x78?0x79:0x78;
    write(x.publicRoot,appAsset,changed);
    assert.throws(()=>verify(x.publicRoot,x.out),/stale/);
    write(x.publicRoot,appAsset,old);
    assert.equal(verify(x.publicRoot,x.out).proof.status,'PASS');

    const next='build/assets/app-BBBB.css';
    x.setManifest({[entryKey]:{file:'assets/app-BBBB.css',isEntry:true}});
    write(x.publicRoot,next,old);
    assert.throws(()=>verify(x.publicRoot,x.out),/unexpected generation file|stale/);
    const newer=build(x.publicRoot,path.join(x.temp,'updated'));
    assert.deepEqual(newer.manifest.entries.map(e=>e.source),[next]);
    assert(fs.existsSync(file),'old original asset is untouched');
});

test('verify fails closed on missing, corrupt, duplicated, or unexpected sidecar bytes and modified inventory', t => {
    const x = fixture(t);
    const {manifest}=build(x.publicRoot,x.out);
    const rel=manifest.entries[0].gzip.path, full=fileAt(x.out,rel), raw=fs.readFileSync(full);
    fs.unlinkSync(full);
    assert.throws(()=>verify(x.publicRoot,x.out),/incomplete generation/);
    write(x.out,rel,raw);
    const corrupt=Buffer.from(raw);
    corrupt[corrupt.length-8] ^= 1;
    write(x.out,rel,corrupt);
    assert.throws(()=>verify(x.publicRoot,x.out),/incorrect data check/i);
    write(x.out,rel,Buffer.concat([raw, raw]));
    assert.throws(()=>verify(x.publicRoot,x.out),/file exceeds/);
    write(x.out,rel,raw);
    const unwanted=write(x.out,'extra.txt','unexpected');
    assert.throws(()=>verify(x.publicRoot,x.out),/unexpected generation file/);
    fs.unlinkSync(unwanted);
    const inventory=fileAt(x.out,'manifest.json'), expected=fs.readFileSync(inventory);
    write(x.out,'manifest.json',expected.toString('utf8').trim());
    assert.throws(()=>verify(x.publicRoot,x.out),/non-canonical generation manifest/);
    write(x.out,'manifest.json',expected);
    assert.equal(verify(x.publicRoot,x.out).proof.status,'PASS');
});

test('rejects out-of-scope paths, unsupported references and content unsafe for the Apache text guard', t => {
    const x=fixture(t);
    assert.throws(()=>build(x.publicRoot,path.join(x.publicRoot,'generation')),/outside the public tree/);
    assert.throws(()=>build(x.publicRoot,path.relative(process.cwd(),x.out)),/must be absolute/);
    x.setManifest({[entryKey]:{file:'../../.env',isEntry:true}});
    assert.throws(()=>build(x.publicRoot,x.out),/unsafe Vite file/);
    x.setManifest({[entryKey]:{file:'assets/app-AAAA.css',isEntry:true,imports:['missing.js']}});
    assert.throws(()=>build(x.publicRoot,x.out),/missing Vite imports record/);
    x.setManifest({[entryKey]:{file:'assets/app-AAAA.css',isEntry:true,css:['assets/APP-AAAA.CSS']}});
    assert.throws(()=>build(x.publicRoot,x.out),/case-colliding/);
    x.setManifest(oldManifest());
    write(x.publicRoot,appAsset,Buffer.from([0x2f,0x00,0x2a]));
    assert.throws(()=>build(x.publicRoot,x.out),/NUL byte/);
    write(x.publicRoot,appAsset,Buffer.from([0x2f,0xc3,0x2a]));
    assert.throws(()=>build(x.publicRoot,x.out),/invalid UTF-8/);
    assert(!fs.existsSync(x.out));
});

test('rejects a nonphysical directory anywhere beneath public or at the output parent', t => {
    const x=fixture(t);
    const originalDir=path.join(x.publicRoot,'build','assets');
    const moved=path.join(x.temp,'actual-assets');
    assert.equal(path.dirname(moved),x.temp);
    fs.renameSync(originalDir,moved);
    fs.symlinkSync(moved,originalDir,process.platform==='win32'?'junction':'dir');
    assert.throws(()=>build(x.publicRoot,x.out),/physical regular file/);
    const linked=path.join(x.temp,'linked-parent');
    fs.symlinkSync(moved,linked,process.platform==='win32'?'junction':'dir');
    assert.throws(()=>build(x.publicRoot,path.join(linked,'generation')),/physical, canonical directory/);
});

test('CLI build/verify emit parseable receipts and a failure never changes a generation', t => {
    const x=fixture(t);
    const script=path.join(__dirname,'precompress.cjs');
    const run=(operation)=>spawnSync(process.execPath,[script,operation,'--public',x.publicRoot,'--out',x.out],{
        windowsHide:true,encoding:'utf8',timeout:15000,
    });
    const built=run('build');
    assert.equal(built.status,0,built.stderr);
    const first=JSON.parse(built.stdout);
    assert.equal(first.operation,'build');
    assert.equal(first.status,'PASS');
    const checked=run('verify');
    assert.equal(checked.status,0,checked.stderr);
    assert.equal(JSON.parse(checked.stdout).manifestSha256,first.manifestSha256);
    write(x.publicRoot,appAsset,'changed asset');
    const stale=run('verify');
    assert.equal(stale.status,1,stale.stderr);
    assert.equal(JSON.parse(stale.stderr).status,'FAIL');
    assert.equal(sha(fs.readFileSync(path.join(x.out,'manifest.json'))),first.manifestSha256);
});
