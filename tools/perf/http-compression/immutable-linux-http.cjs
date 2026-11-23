'use strict';

// A single-host native Nginx contract, not a benchmark. Must run on the
// verified Ubuntu host. No redirects, external DNS, or certificate bypass.
const assert = require('node:assert/strict');
const { createHash } = require('node:crypto');
const fs = require('node:fs');
const http = require('node:http');
const https = require('node:https');
const path = require('node:path');
const { gunzipSync } = require('node:zlib');
const release = require('./immutable-release.cjs');

const HOST = 'gramlyze.ub';
const sha = value => createHash('sha256').update(value).digest('hex');
const LIMIT = 32*1024*1024;

function argumentsOf(raw) {
    const values = new Map();
    for (let i=0;i<raw.length;) {
        const key = raw[i++];
        assert(!values.has(key), 'duplicate option ' + key);
        if (['--https','--active'].includes(key)) values.set(key,true);
        else {
            assert(['--port','--ca','--generation','--manifest','--old-generation','--old-manifest'].includes(key),
                'unknown option ' + key);
            const value = raw[i++];
            assert(value && !value.startsWith('--'), 'missing option value ' + key);
            values.set(key,value);
        }
    }
    assert(/^\d{1,5}$/.test(values.get('--port') ?? ''), 'invalid port format');
    const port = Number(values.get('--port'));
    const tls = values.has('--https');
    assert(Number.isSafeInteger(port) && port >= 1 && port <= 65535 && port !== 22, 'invalid HTTP port');
    if (values.has('--active')) assert.equal(port,tls ? 443 : 80, 'explicit active mode requires the expected public port');
    else assert(port >= 1024, 'private contract requires an isolated high port');
    assert(tls || !values.has('--ca'), 'CA is only for HTTPS');
    const pins = ['','old-'].filter(prefix=>prefix==='' || values.has('--'+prefix+'generation') || values.has('--'+prefix+'manifest'))
        .map(prefix=>{
            const generation = values.get('--'+prefix+'generation');
            const expectedDigest = values.get('--'+prefix+'manifest');
            assert(generation && path.isAbsolute(generation), 'absolute generation path required');
            assert(typeof expectedDigest === 'string' && /^[a-f0-9]{64}$/.test(expectedDigest), 'full generation pin required');
            const checked = release.verify({ generation, expectedDigest });
            assert.equal(checked.manifest.scope,'vite');
            return checked;
        });
    let ca;
    if (values.has('--ca')) {
        const file = values.get('--ca');
        assert(path.isAbsolute(file), 'absolute trusted CA certificate path required');
        const stat = fs.statSync(file);
        assert(stat.isFile() && stat.size <= 1024*1024, 'not a reasonable CA certificate');
        ca=fs.readFileSync(file);
    }
    return { port,tls,ca,pins };
}

function request(options, uri, { method='GET',headers={} } = {}) {
    assert(/^\/build(?:\/[A-Za-z0-9._/-]+)?$/.test(uri), 'only reviewed build resource requests');
    const transport = options.tls ? https : http;
    return new Promise((resolve,reject)=>{
        const req = transport.request({
            hostname:'127.0.0.1', port:options.port, path:uri, method, agent:false,
            ...(options.tls ? { servername:HOST,rejectUnauthorized:true,...(options.ca?{ca:options.ca}:{}) } : {}),
            headers:{Host:HOST,...headers},
        }, response => {
            const chunks=[];
            let bytes=0;
            response.on('data',chunk=>{
                bytes+=chunk.length;
                if (bytes>LIMIT) req.destroy(Error('encoded body exceeded reviewed bound'));
                else chunks.push(chunk);
            });
            response.on('error',reject);
            response.on('end',()=>{
                if (!response.complete) return reject(Error('incomplete Nginx response'));
                const body=Buffer.concat(chunks);
                resolve({ status:response.statusCode, headers:response.headers, body });
            });
        });
        const timer=setTimeout(()=>req.destroy(Error('Nginx contract timeout')),7000);
        req.on('error',reject);
        req.on('close',()=>clearTimeout(timer));
        req.end();
    });
}

function encoding(result) { return result.headers['content-encoding'] ?? 'identity'; }
function responseLength(result) { return Number(result.headers['content-length']); }
function vary(result, expected) {
    const tokens = (result.headers.vary ?? '').split(',').map(piece=>piece.trim().toLowerCase());
    assert.equal(tokens.filter(value=>value === 'accept-encoding').length,expected ? 1 : 0,'wrong or duplicated Accept-Encoding variation');
}
function cache(result) {
    assert.equal(result.headers['cache-control'],'public, max-age=31536000, immutable');
}
function etag(result) {
    assert.match(result.headers.etag ?? '', /^"[^"\s]+"$/, 'expected one strong ETag');
    return result.headers.etag;
}
function full(result, asset, compressed) {
    const pin = compressed ? asset.gzip : asset.identity;
    assert.equal(result.status,200);
    assert.equal(encoding(result),compressed ? 'gzip' : 'identity');
    assert.equal(result.headers['content-type'],asset.mime);
    assert.equal(result.headers['last-modified'],new Date(asset.modified*1000).toUTCString());
    assert.equal(responseLength(result),pin.bytes);
    assert.equal(result.body.length,pin.bytes);
    assert.equal(sha(result.body),pin.sha256);
    if (compressed) {
        const decoded=gunzipSync(result.body,{ maxOutputLength:asset.identity.bytes });
        assert.equal(decoded.length,asset.identity.bytes);
        assert.equal(sha(decoded),asset.identity.sha256,'double compression or decoded SHA mismatch');
    }
    vary(result,asset.gzip!==null);
    cache(result);
    etag(result);
}

async function exercise(options, rows) {
    const performed=new Set();
    const check = async (label, uri, settings, assertion) => {
        const result=await request(options,uri,settings);
        const row={label,uri,status:result.status,encoding:encoding(result),bytes:result.body.length,
            etag:result.headers.etag ?? null};
        rows.push(row);
        assertion(result);
        row.pass=true;
        return result;
    };
    const offer=(value,extra={})=>({headers:{'Accept-Encoding':value,...extra}});
    let testedGzip=0;
    for (const [index, checked] of options.pins.entries()) {
        for (const asset of checked.manifest.assets) {
            if (performed.has(asset.url)) continue;
            performed.add(asset.url);
            const id=await check(index===0?'identity':'retained identity',asset.url,offer('identity'),r=>full(r,asset,false));
            const native=await check('gzip offered',asset.url,offer('gzip'),r=>full(r,asset,asset.gzip!==null));
            await check('explicit gzip refusal',asset.url,offer('gzip;q=0, identity;q=1'),r=>full(r,asset,false));
            await check('no encoding offered',asset.url,{},r=>full(r,asset,false));
            if (asset.gzip) {
                testedGzip++;
                assert.notEqual(etag(id),etag(native), 'gzip and identity validators must not alias');
                await check('identity ETag cannot revalidate gzip',asset.url,offer('gzip',{'If-None-Match':etag(id)}),r=>full(r,asset,true));
            }
            for (const [selected, rep] of asset.gzip ? [[id,'identity'],[native,'gzip']] : [[id,'identity']]) {
                await check('HEAD '+rep,asset.url,{method:'HEAD',...offer(rep)},r=>{
                    assert.equal(r.status,200);
                    assert.equal(r.body.length,0);
                    assert.equal(encoding(r),rep);
                    assert.equal(responseLength(r),selected.body.length);
                    assert.equal(etag(r),etag(selected));
                    vary(r,asset.gzip!==null);
                    cache(r);
                });
                for (const [header,value] of [['If-None-Match',etag(selected)],['If-Modified-Since',selected.headers['last-modified']]]) {
                    await check('conditional '+rep+' '+header,asset.url,offer(rep,{[header]:value}),r=>{
                        assert.equal(r.status,304);
                        assert.equal(r.body.length,0);
                        assert.equal(etag(r),etag(selected));
                        vary(r,asset.gzip!==null);
                        cache(r);
                    });
                }
                assert(selected.body.length>=2,'fixture asset too small for a strict partial request');
                await check('native range '+rep,asset.url,offer(rep,{Range:'bytes=0-0','If-Range':etag(selected)}),r=>{
                    assert.equal(r.status,206);
                    assert.equal(encoding(r),rep);
                    assert.equal(r.headers['content-range'],'bytes 0-0/'+selected.body.length);
                    assert.equal(responseLength(r),1);
                    assert(r.body.equals(selected.body.subarray(0,1)));
                    vary(r,asset.gzip!==null);
                    cache(r);
                });
            }
        }
    }
    assert(testedGzip>0,'no gzip representation was tested');
    const existing=options.pins[0].manifest.assets[0].url;
    for (const uri of ['/build','/build/assets/unknown-Aa8123zz.js',
        '/build/manifest.json','/build/.vite/manifest.json','/build/vite-manifest.json',existing+'.gz',existing+'.map']) {
        // HEAD requires 404 without reading any hypothetical protected body.
        await check('non-public build path rejected',uri,{method:'HEAD',...offer('gzip')},r=>{
            assert.equal(r.status,404);
            assert.equal(r.body.length,0);
            assert.doesNotMatch(r.headers['cache-control']??'',/immutable/);
            assert.equal(encoding(r),'identity');
        });
    }
}

async function main() {
    const rows=[];
    const started=new Date().toISOString();
    try {
        assert.equal(process.platform,'linux','must run on the verified native Ubuntu host');
        const options=argumentsOf(process.argv.slice(2));
        await exercise(options,rows);
        process.stdout.write(JSON.stringify({status:'PASS', started, ended:new Date().toISOString(),
            connect:'127.0.0.1',host:HOST,port:options.port,tls:options.tls,
            generations:options.pins.map(pin=>pin.manifestSha256), rows})+'\n');
    } catch (error) {
        process.stdout.write(JSON.stringify({status:'BLOCKED',started,ended:new Date().toISOString(),error:error.message,rows})+'\n');
        process.exitCode=1;
    }
}

if (require.main === module) main();
