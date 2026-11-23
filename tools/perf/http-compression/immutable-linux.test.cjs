'use strict';

const assert = require('node:assert/strict');
const { createHash } = require('node:crypto');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { test } = require('node:test');
const { gzipSync } = require('node:zlib');
const { nginxConfig, selectAssets, validateDelivery, publish } = require('./immutable-linux.cjs');

const sha = value => createHash('sha256').update(value).digest('hex');
const root = '/srv/gramlyze-perf31/serve-' + 'a'.repeat(64);
const modified = 1710505445;

function sample(file = 'app-Abcdef01.js', body = Buffer.from('console.log("test");\n'.repeat(512)), compressed = true) {
    const source = 'build/assets/' + file;
    const gzip = gzipSync(body);
    return { body, gzip, entry:{
        source, url:'/' + source, class:'vite',
        mime:source.endsWith('.css') ? 'text/css' : 'text/javascript',
        identity:{ bytes:body.length, sha256:sha(body) },
        gzip:compressed ? { bytes:gzip.length, sha256:sha(gzip) } : null,
        modified,
    }};
}

function generation(assets, deliveryRoot = root, scope = 'vite') {
    return { manifest:{scope, assets}, deliveryRoot };
}

test('finite exact static locations only: native gzip, cache semantics and hard 404 fallback', () => {
    const script = sample();
    const css = sample('theme-Zz9854aa.css',Buffer.from('/*tiny*/'),false);
    const config = nginxConfig([generation([script.entry,css.entry])]);
    assert.match(config, /location = \/build\/assets\/app-Abcdef01\.js \{/);
    assert.match(config, /location = \/build\/assets\/theme-Zz9854aa\.css \{/);
    assert.match(config, /types \{ text\/javascript js mjs; \}/);
    assert.match(config, /types \{ text\/css css; \}/);
    assert.equal((config.match(/gzip_static on;/g) ?? []).length,1);
    assert.equal((config.match(/add_header Vary "Accept-Encoding" always;/g) ?? []).length,1);
    assert.equal((config.match(/add_header Cache-Control "public, max-age=31536000, immutable";/g) ?? []).length,2);
    assert.doesNotMatch(config,/gzip on;|gzip_static always|add_header Content-Encoding|add_header ETag/);
    assert.match(config,/location \^~ \/build\/ \{\n    error_page 404 = @gramlyze_perf31_build_not_found;\n    return 404;/);
    assert.match(config,/location @gramlyze_perf31_build_not_found \{\n    recursive_error_pages off;/);
});

test('retention maps old URLs, prefers active identical bytes, and rejects coding/hash collisions', () => {
    const current = sample();
    const previous = sample('app-Zzzzzz01.js');
    const oldRoot = '/srv/gramlyze-perf31/serve-' + 'b'.repeat(64);
    const old = generation([previous.entry,{...current.entry,modified:modified-10}],oldRoot);
    const selected = selectAssets([generation([current.entry]),old]);
    assert.equal(selected[0].asset.url,current.entry.url);
    assert.equal(selected[0].deliveryRoot,root);
    assert.equal(selected[1].deliveryRoot,oldRoot);
    const different = sample('app-Abcdef01.js',Buffer.from('one false response\n'.repeat(256)));
    assert.throws(() => selectAssets([generation([different.entry]),old]), /collision in retained/);
    assert.throws(() => selectAssets([generation([{...current.entry,gzip:null}]),old]), /collision in retained/);
    const differentCase = sample('App-Abcdef01.js');
    assert.throws(() => selectAssets([generation([differentCase.entry]),old]), /collision in retained/);
});

test('declines stable files, injection, noncanonical roots and non-fingerprinted inputs', () => {
    const { entry } = sample();
    assert.throws(() => nginxConfig([generation([entry],root,'isolated-with-stable')]), /only the reviewed Vite graph/);
    for (const source of ['build/assets/a.js','build/assets/app-Abcdef01.js.gz','build/assets/app-Abcdef01.js.map',
        'build/assets/../a-Abcdef01.js','build/assets/a-Abcdef01.js;return 200']) {
        assert.throws(() => nginxConfig([generation([{...entry,source,url:'/'+source}])]), /unsafe asset URI/);
    }
    for (const bad of ['relative','/srv/vault/../root','/srv/a b','/srv/$document_root','/srv/foo;']) {
        assert.throws(() => nginxConfig([generation([entry],bad)]), /path/);
    }
});

test('restates only reviewed static headers; no cache, encoding, or header injection', () => {
    const fixture = [generation([sample().entry])];
    const header = { name:'X-Content-Type-Options',value:'nosniff',always:true };
    assert.match(nginxConfig(fixture,[header]), /add_header X-Content-Type-Options "nosniff" always;/);
    assert.throws(() => nginxConfig(fixture,[header,header]), /duplicate/);
    for (const name of ['SET-COOKIE','Vary','Content-Encoding','Cache-Control','ETag']) {
        assert.throws(() => nginxConfig(fixture,[{...header,name}]), /unsupported reviewed header/);
    }
    for (const value of ['nosniff\r\nx:y','$variable','nosniff";gzip on;']) {
        assert.throws(() => nginxConfig(fixture,[{...header,value}]), /unsafe reviewed header value/);
    }
});

test('projection re-verification rejects chmod, extra files, and both kinds of links', {skip:process.platform!=='linux'}, () => {
    const base = fs.mkdtempSync(path.join(os.tmpdir(),'perf31-linux-test-'));
    const target = path.join(base,'sealed');
    const assets = path.join(target,'build','assets');
    const fixture = sample();
    const file = path.join(target,fixture.entry.source);
    const sidecar = file + '.gz';
    const uid = process.getuid();
    const manifest = generation([fixture.entry]).manifest;
    try {
        fs.mkdirSync(assets,{recursive:true});
        for (const [name, bytes] of [[file,fixture.body],[sidecar,fixture.gzip]]) {
            fs.writeFileSync(name,bytes,{flag:'wx'});
            fs.utimesSync(name,modified,modified);
            fs.chmodSync(name,0o444);
        }
        for (const dir of [assets,path.dirname(assets),target]) fs.chmodSync(dir,0o555);
        assert.doesNotThrow(() => validateDelivery(target,manifest,uid));
        fs.chmodSync(file,0o644);
        assert.throws(() => validateDelivery(target,manifest,uid),/file mode/);
        fs.chmodSync(file,0o444);
        const hard = path.join(base,'hard');
        fs.linkSync(file,hard);
        assert.throws(() => validateDelivery(target,manifest,uid),/unlinked regular/);
        fs.unlinkSync(hard);
        fs.chmodSync(assets,0o755);
        const extra = path.join(assets,'private.json');
        fs.writeFileSync(extra,'null');
        fs.chmodSync(assets,0o555);
        assert.throws(() => validateDelivery(target,manifest,uid),/unexpected public projection file/);
        fs.chmodSync(assets,0o755);
        fs.unlinkSync(extra);
        fs.unlinkSync(sidecar);
        fs.symlinkSync(file,sidecar);
        fs.chmodSync(assets,0o555);
        assert.throws(() => validateDelivery(target,manifest,uid),/unexpected public projection file or link/);
    } finally {
        for (const dir of [target,path.dirname(assets),assets]) if (fs.existsSync(dir)) fs.chmodSync(dir,0o700);
        fs.rmSync(base,{recursive:true});
    }
});

test('platform guard cannot use Windows/NTFS as a Linux publication proof', {skip:process.platform==='linux'}, () => {
    assert.throws(() => publish({}), /requires native Linux/);
});
