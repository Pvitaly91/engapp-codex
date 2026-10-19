'use strict';
// Fresh guest GET-only .loc evidence. No cookies, tokens, response bodies or production requests.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const {metadata} = require('./seo-m28-local.cjs');
const ROOT = path.resolve(__dirname, '../..');
const BASE = 'http://gramlyze.loc';
const read = file => JSON.parse(fs.readFileSync(path.join(ROOT, file), 'utf8'));
const master=read('docs/content/m41-authored-tense-comparisons.v1.0.1.json');
const previous=require('./capture-m40-http.cjs');
const targetPaths=master.lessons.map(lesson=>lesson.theory_path);
const mixedPaths=master.lessons.map(lesson=>lesson.test_path);
const coursePath='/courses/english-grammar-theory/lesson/tenses/past-simple-vs-past-continuous';
const controls=[...previous.controls,...previous.targetPaths];
const routes = [...targetPaths, ...controls, ...mixedPaths, coursePath];
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
function expectedMetadata() {
    const {execFileSync}=require('node:child_process');
    return JSON.parse(execFileSync(process.env.PHP_EXECUTABLE||'C:/Program Files/xampp/php/php.exe',
        [path.join(__dirname,'m41-expected-http-metadata.php')],{encoding:'utf8',timeout:10000}));
}
function assertRoute(route) {
    assert.ok(route.startsWith('/') && !route.startsWith('//'), 'Relative fixed .loc route only');
    assert.equal(new URL(BASE + route).origin, BASE, 'Never request production');
}
async function get(route, accept = 'text/html') {
    assertRoute(route);
    return fetch(BASE + route, {redirect: 'manual', signal: AbortSignal.timeout(30000),
        headers: {Accept: accept, Connection: 'close'}});
}
async function capture() {
    const rows = [];
    for (const route of routes) {
        console.log(JSON.stringify({checking: route}));
        const attempts=[];let response,body;
        for(let attempt=1;attempt<=2;attempt++) {
            const started=new Date().toISOString();
            try {
                response=await get(route);body=await response.text();attempts.push({attempt,started,finished:new Date().toISOString(),status:response.status});break;
            } catch(error) {
                attempts.push({attempt,started,finished:new Date().toISOString(),name:error.name,message:error.message,code:error.cause?.code||null});
                console.log(JSON.stringify({route,attempt,error:attempts.at(-1)}));
            }
        }
        if(!response||response.status!==200||body===undefined){rows.push({path:route,checkedAt:new Date().toISOString(),status:response?.status||null,attempts,error:response&&body!==undefined?'Unexpected HTTP status':'GET failed'});continue;}
        const dom = new JSDOM(body);
        const doc = dom.window.document;
        try {
            const main = doc.querySelector('[data-theory-main]')?.cloneNode(true);
            if (route.startsWith('/theory/')) assert.ok(main, 'Full theory main ' + route);
            main?.querySelectorAll('[x-data],script,style,[data-theory-ui]').forEach(node => node.remove());
            rows.push({path: route, checkedAt: new Date().toISOString(), status: response.status, attempts,
                contentType: response.headers.get('content-type'), meta: metadata(doc),
                xRobots: response.headers.get('x-robots-tag'), mainTextSha256: main ? sha(norm(main.textContent)) : null,
                details: doc.querySelectorAll('[data-theory-native-extension] > details').length,
                legacyIds: [...doc.querySelectorAll('[id^="lesson-block-"],[id^="self-check-"]')].map(node => node.id),
                nativeBlockIds:[...doc.querySelectorAll('[data-theory-main] [id^="block-"]')].map(node=>node.id),
                courseReadOnly: route === coursePath ? {h1: [...doc.querySelectorAll('h1')].map(node => norm(node.textContent)),
                    sourceCases: [...doc.querySelectorAll('[id^="self-check-"] > ol > li,[data-m41-author-prompt]')].length,
                    sourceKeys: [...doc.querySelectorAll('[id^="self-check-"] > details > ol > li,[data-m41-self-check-answers] li')].length,
                    hasCourseContent: Boolean(doc.querySelector('[data-theory-lesson-content]')),
                    policy: 'Server properties only; no browser gate bypass or visual acceptance claim'} : null});
        } finally {dom.window.close();}
    }
    let response,xml;
    try{response=await get('/sitemap.xml','application/xml');xml=await response.text();}catch(error){return {at:new Date().toISOString(),base:BASE,rows,sitemap:{error:error.message,name:error.name,code:error.cause?.code||null},pass:false};}
    if(response.status!==200)return {at:new Date().toISOString(),base:BASE,rows,sitemap:{error:'Unexpected HTTP status',status:response.status},pass:false};
    const dom = new JSDOM(xml, {contentType: 'text/xml'});
    const urls = [...dom.window.document.querySelectorAll('loc')].map(node => node.textContent);
    dom.window.close();
    return {at: new Date().toISOString(), base: BASE, rows, sitemap: {count: urls.length, sha256: sha(JSON.stringify(urls))},pass:rows.every(row=>row.status===200&&!row.error)};
}
function compare(before, after, nativeBefore) {
    assert.equal(before.pass,true,'Complete successful before HTTP evidence required');assert.equal(after.pass,true,'Complete successful after HTTP evidence required');
    assert.equal(before.base, BASE); assert.equal(after.base, BASE);
    assert.deepEqual(after.rows.map(row => row.path), routes, 'Exact HTTP inventory');
    assert.deepEqual(after.sitemap, before.sitemap, 'Ordered sitemap unchanged');
    assert.equal(nativeBefore?.base,BASE,'Fresh pre-apply native-anchor inventory required');
    assert.deepEqual(nativeBefore.rows.map(row=>row.path),targetPaths);
    const expected=expectedMetadata();
    for (const row of after.rows) {
        const old = before.rows.find(item => item.path === row.path); assert.ok(old, row.path);
        if(targetPaths.includes(row.path)) {
            const anchors=nativeBefore.rows.find(item=>item.path===row.path).nativeBlockIds;
            for(const id of anchors)assert.ok(row.nativeBlockIds.includes(id),'Existing served native anchor preserved '+id);
            for(const name of ['title','h1','canonical','robots','ogTitle','twitterTitle'])
                assert.deepEqual(row.meta[name],old.meta[name],'Protected target metadata '+name+' '+row.path);
            assert.equal(row.meta.description,row.meta.ogDescription,'New target description/OG consistency');
            assert.equal(row.meta.description,row.meta.twitterDescription,'New target description/Twitter consistency');
            assert.equal(row.meta.title,expected[row.path].title,'Exact localized PageMetadata title from new author master');
            assert.equal(row.meta.description,expected[row.path].description,'Exact localized PageMetadata description from new author master');
        } else assert.deepEqual(row.meta,old.meta,'Exact unchanged control metadata '+row.path);
        assert.equal(row.xRobots, old.xRobots, row.path);
        assert.equal(row.contentType, old.contentType, row.path);
        for (const id of old.legacyIds) assert.ok(row.legacyIds.includes(id), 'Preserved old anchor ' + id);
        if (controls.includes(row.path)) {
            assert.equal(row.mainTextSha256, old.mainTextSha256, 'Exact unchanged control ' + row.path);
            assert.equal(row.details, old.details, 'Accepted detail count ' + row.path);
        }
    }
}
async function run() {
    const [dir, label] = process.argv.slice(2);
    assert.equal(path.basename(dir), 'seo-m41-local'); assert.match(label, /^[a-z0-9-]+$/);
    fs.mkdirSync(dir, {recursive: true});
    if(label==='before-native-anchors') {
        const rows=[];
        for(const route of targetPaths){const response=await get(route);assert.equal(response.status,200);const dom=new JSDOM(await response.text());
            rows.push({path:route,at:new Date().toISOString(),nativeBlockIds:[...dom.window.document.querySelectorAll('[data-theory-main] [id^="block-"]')].map(node=>node.id)});dom.window.close();}
        fs.writeFileSync(path.join(dir,label+'.json'),JSON.stringify({base:BASE,rows},null,2),{flag:'wx'});return;
    }
    const result = await capture();
    fs.writeFileSync(path.join(dir, label + '-http.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
    if (!label.startsWith('before')) {
        const accepted=process.env.M41_BEFORE_HTTP||'before-v2-http.json';assert.match(accepted,/^before(?:-v\d+)?-http\.json$/u);
        compare(JSON.parse(fs.readFileSync(path.join(dir,accepted),'utf8')),result,
            JSON.parse(fs.readFileSync(path.join(dir,'before-native-anchors.json'),'utf8')));
    }
    console.log(JSON.stringify({pass: result.pass, label, rows: result.rows.length, errors:result.rows.filter(row=>row.error).length,sitemap: result.sitemap}));
    if(!result.pass)process.exitCode=1;
}
if (require.main === module) run().catch(error => {console.error(error.message); process.exitCode = 1;});
module.exports = {capture, compare, controls, routes, targetPaths, mixedPaths, coursePath, BASE, assertRoute,expectedMetadata};
