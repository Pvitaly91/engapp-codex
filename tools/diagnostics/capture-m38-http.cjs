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
const targetPaths = [
    '/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance',
    '/theory/articles-and-quantifiers/precision-with-articles-and-determiners',
    '/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice',
];
const mixedPaths = [
    '/test/articles-and-quantifiers/advanced-article-and-quantifier-nuance',
    '/test/articles-and-quantifiers/precision-with-articles-and-determiners',
    '/test/vocabulary-and-collocations/advanced-collocation-and-lexical-choice',
];
const regressions = [
    ...read('docs/content/m26-past-perfect-continuous-detail-master.v1.json').targets.map(t => t.expected_theory_path),
    ...['m27-m11-linking-words.v2', 'm28-m12-emphasis-inversion.v2', 'm29-m13-sentence-structure.v2',
        'm30-m14-participle-clauses.v1', 'm31-m15-conditionals.v1', 'm32-m16-formal-english.v1', 'm33-m17-academic-english.v1', 'm34-m18-argumentation-cohesion.v1', 'm35-m19-passive-reporting.v1', 'm36-m20-modals-subjunctive.v1', 'm37-m21-grammar-structures.v1'].flatMap(file => read('database/content-patches/' + file + '.json').targets
        .map(t => '/theory/' + (t.ancestry || ['clauses-and-linking-words']).concat(t.slug).join('/'))),
];
const controls = [...regressions, '/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms'];
const routes = [...targetPaths, ...controls, ...mixedPaths];
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
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
        const response = await get(route);
        assert.equal(response.status, 200, route);
        const dom = new JSDOM(await response.text());
        const doc = dom.window.document;
        try {
            const main = doc.querySelector('[data-theory-main]')?.cloneNode(true);
            if (route.startsWith('/theory/')) assert.ok(main, 'Full theory main ' + route);
            main?.querySelectorAll('[x-data],script,style,[data-theory-ui]').forEach(node => node.remove());
            rows.push({path: route, checkedAt: new Date().toISOString(), status: response.status,
                contentType: response.headers.get('content-type'), meta: metadata(doc),
                xRobots: response.headers.get('x-robots-tag'), mainTextSha256: main ? sha(norm(main.textContent)) : null,
                details: doc.querySelectorAll('[data-theory-native-extension] > details').length,
                legacyIds: [...doc.querySelectorAll('[id^="lesson-block-"],[id^="self-check-"]')].map(node => node.id)});
        } finally {dom.window.close();}
    }
    const response = await get('/sitemap.xml', 'application/xml');
    assert.equal(response.status, 200);
    const dom = new JSDOM(await response.text(), {contentType: 'text/xml'});
    const urls = [...dom.window.document.querySelectorAll('loc')].map(node => node.textContent);
    dom.window.close();
    return {at: new Date().toISOString(), base: BASE, rows, sitemap: {count: urls.length, sha256: sha(JSON.stringify(urls))}};
}
function compare(before, after) {
    assert.equal(before.base, BASE); assert.equal(after.base, BASE);
    assert.deepEqual(after.rows.map(row => row.path), routes, 'Exact HTTP inventory');
    assert.deepEqual(after.sitemap, before.sitemap, 'Ordered sitemap unchanged');
    for (const row of after.rows) {
        const old = before.rows.find(item => item.path === row.path); assert.ok(old, row.path);
        assert.deepEqual(row.meta, old.meta, 'Title/H1/description/canonical/robots/OG/Twitter unchanged ' + row.path);
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
    assert.equal(path.basename(dir), 'seo-m38-local'); assert.match(label, /^[a-z0-9-]+$/);
    fs.mkdirSync(dir, {recursive: true});
    const result = await capture();
    fs.writeFileSync(path.join(dir, label + '-http.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
    if (label !== 'before') compare(JSON.parse(fs.readFileSync(path.join(dir, 'before-http.json'), 'utf8')), result);
    console.log(JSON.stringify({pass: true, label, rows: result.rows.length, sitemap: result.sitemap}));
}
if (require.main === module) run().catch(error => {console.error(error.message); process.exitCode = 1;});
module.exports = {capture, compare, controls, routes, targetPaths, mixedPaths, BASE, assertRoute};
