'use strict';
// M42 guest GET evidence only. Fixed .loc inventory; no cookies/Auth/Referer or response secrets.
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const {metadata} = require('./seo-m28-local.cjs');
const ROOT = path.resolve(__dirname, '../..'), BASE = 'http://gramlyze.loc';
const PRIVATE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local';
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const registryBytes = fs.readFileSync(path.join(ROOT, 'database/content-patches/m42-native-design-registry.v1.json'));
const registry = JSON.parse(registryBytes);
assert.equal(registry.targets.length, 42); assert.equal(new Set(registry.targets.map(t => t.identity)).size, 42);
const targetPaths = registry.targets.map(t => new URL(t.local_url).pathname);
assert.equal(new Set(targetPaths).size, 42);
const referencePaths = JSON.parse(fs.readFileSync(path.join(ROOT, 'docs/content/m41-authored-tense-comparisons.v1.0.1.json'))).lessons.map(t => t.theory_path);
const controls = ['/', '/theory', '/theory/clauses-and-linking-words', '/courses/english-grammar-theory',
    '/courses/english-grammar-theory/lesson/clauses-and-linking-words/linking-words-reason-result-contrast',
    '/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms',
    '/theory/tenses/present-perfect/present-perfect-forms',
    '/test/tenses/present-perfect-vs-past-simple', '/test/clauses-and-linking-words/linking-words-reason-result-contrast'];
const localeControls = ['en', 'pl'].flatMap(locale => [...targetPaths, ...referencePaths].map(route => '/' + locale + route));
const routes = [...targetPaths, ...referencePaths, ...localeControls, ...controls];
assert.equal(new Set(routes).size, routes.length);
function assertRoute(route) {
    assert.ok(typeof route === 'string' && route.startsWith('/') && !route.startsWith('//'));
    assert.equal(new URL(BASE + route).origin, BASE, 'Never request production');
}
function words(text) {
    const counts = {};
    // Meaningful learning digits remain bound. Only explicit UI DOM nodes are excluded below.
    for (const word of norm(text).match(/[\p{L}\p{N}]+(?:[’'\-][\p{L}\p{N}]+)*/gu) || []) {
        counts[word] = (counts[word] || 0) + 1;
    }
    return Object.fromEntries(Object.entries(counts).sort(([a], [b]) => a.localeCompare(b)));
}
function learner(doc) {
    const main = doc.querySelector('[data-theory-main]')?.cloneNode(true);
    if (!main) return null;
    main.querySelectorAll('script,style,noscript,[data-theory-ui],.theory-section-number').forEach(node => node.remove());
    const text = norm(main.textContent);
    const details = [...main.querySelectorAll('[data-theory-native-extension] > details')].map(node => ({
        id: node.querySelector('[id]')?.id || node.id || null,
        fragmentId: node.querySelector('.theory-point-fragment')?.id || null,
        text: norm(node.querySelector('.theory-point-fragment')?.textContent || node.textContent),
        pointAnchor: node.closest('article')?.id || null,
        pointKey: node.parentElement.getAttribute('data-theory-section'),
        pointIndex: node.parentElement.getAttribute('data-theory-point-index'),
    }));
    const tables = [...main.querySelectorAll('table')].map(table => ({
        headings: [...table.querySelectorAll('thead th')].map(cell => norm(cell.textContent)),
        rows: [...table.querySelectorAll('tbody tr')].map(row => [...row.querySelectorAll('td,th')].map(cell => norm(cell.textContent))),
    }));
    const practice = [...main.querySelectorAll('[x-data]')].filter(node => /Practice|practice/i.test(node.getAttribute('x-data'))).map(node => ({
        factory: node.getAttribute('x-data').split('(')[0],
        stateSha256: sha(node.getAttribute('x-data')),
        text: norm(node.textContent),
        controls: [...node.querySelectorAll('fieldset,input,select,textarea')].map(control => ({tag: control.tagName.toLowerCase(),
            type: control.getAttribute('type'), kind: [...control.attributes].find(a => /^data-m\d+-control-kind$/u.test(a.name))?.value || null,
            value: control.getAttribute('value'), required: control.hasAttribute('required'), label: norm(control.querySelector('legend')?.textContent)})),
        sourceCases: [...node.querySelectorAll('[data-m39-ui-case],[data-m40-ui-case],[data-m41-ui-case]')].map(n => [...n.attributes].find(a => /-ui-case$/u.test(a.name))?.value),
    }));
    return {textSha256: sha(text), words: words(text), orderedWords: text.match(/[\p{L}\p{N}]+(?:[’'\-][\p{L}\p{N}]+)*/gu) || [], details, tables, practice,
        anchors: [...main.querySelectorAll('[id]')].map(node => node.id),
        headings: [...main.querySelectorAll('h2,h3,h4')].map(node => ({tag: node.tagName, text: norm(node.textContent)}))};
}
function jsonLd(doc) {
    return [...doc.querySelectorAll('script[type="application/ld+json"]')].map(node => {
        try { const value = JSON.parse(node.textContent); return {valid: true, sha256: sha(JSON.stringify(value)), value}; }
        catch (error) { return {valid: false, error: error.message, rawSha256: sha(node.textContent)}; }
    });
}
async function guestGet(route, accept = 'text/html') {
    assertRoute(route); const attempts = [];
    for (let attempt = 1; attempt <= 2; attempt++) {
        const started = new Date().toISOString(); let current = route; const chain = [];
        try {
            for (let hop = 0; hop <= 5; hop++) {
                const timeoutMs = current === '/theory/clauses-and-linking-words' ? 120000 : 30000;
                const response = await fetch(BASE + current, {redirect: 'manual', signal: AbortSignal.timeout(timeoutMs),
                    headers: {Accept: accept, Connection: 'close'}});
                const body = await response.text();
                const location = response.headers.get('location');
                chain.push({url: BASE + current, status: response.status, location, timeoutMs});
                if (response.status >= 300 && response.status < 400 && location) {
                    const next = new URL(location, BASE + current);
                    assert.equal(next.origin, BASE, 'Off-local redirect is recorded, never followed');
                    current = next.pathname + next.search; assertRoute(current); continue;
                }
                attempts.push({attempt, started, finished: new Date().toISOString(), chain});
                return {response, body, attempts, finalUrl: BASE + current};
            }
            throw new Error('Redirect limit exceeded');
        } catch (error) {
            attempts.push({attempt, started, finished: new Date().toISOString(), chain,
                name: error.name, message: error.message, code: error.cause?.code || null});
        }
    }
    return {attempts, error: 'Guest GET failed'};
}
async function capture() {
    const rows = [];
    for (const route of routes) {
        console.log(JSON.stringify({checking: route}));
        const result = await guestGet(route);
        if (result.error) {rows.push({path: route, ...result}); continue;}
        const {response, body, attempts, finalUrl} = result;
        const doc = new JSDOM(body);
        try {
            rows.push({path: route, at: new Date().toISOString(), status: response.status, attempts, finalUrl,
                contentType: response.headers.get('content-type'), xRobots: response.headers.get('x-robots-tag'),
                meta: metadata(doc.window.document), jsonLd: jsonLd(doc.window.document), learner: learner(doc.window.document),
                courseGate: route.includes('/courses/') ? {mainPresent: Boolean(doc.window.document.querySelector('main')),
                    lessonContent: Boolean(doc.window.document.querySelector('[data-theory-lesson-content]')),
                    policy: 'Guest server-rendered properties only. No gate bypass.'} : null});
        } finally {doc.window.close();}
    }
    const extras = {};
    for (const route of ['/robots.txt', '/sitemap.xml', '/health', '/dev/site-mode']) {
        const result = await guestGet(route, route.endsWith('.xml') ? 'application/xml' : route.startsWith('/dev') || route === '/health' ? 'application/json' : 'text/plain');
        if (result.error) {extras[route] = result; continue;}
        const {response, body, attempts, finalUrl} = result;
        const record = {status: response.status, attempts, finalUrl, contentType: response.headers.get('content-type'),
            xRobots: response.headers.get('x-robots-tag'), bodySha256: sha(body)};
        if (route === '/sitemap.xml' && response.status === 200) {
            const dom = new JSDOM(body, {contentType: 'text/xml'});
            const urls = [...dom.window.document.querySelectorAll('loc')].map(n => n.textContent); dom.window.close();
            record.count = urls.length; record.orderedSha256 = sha(JSON.stringify(urls));
        }
        if (['/health', '/dev/site-mode'].includes(route) && response.status === 200) record.safeJson = JSON.parse(body);
        extras[route] = record;
    }
    return {at: new Date().toISOString(), base: BASE, registrySha256: sha(registryBytes), rows, extras,
        pass: rows.every(row => row.status === 200 && !row.error)
            && Object.values(extras).every(row => row.status === 200 && !row.error)};
}
function compare(before, after) {
    assert.equal(before.pass, true, 'Complete independent BEFORE required'); assert.equal(after.pass, true, 'Complete fresh AFTER required');
    assert.equal(before.base, BASE); assert.equal(after.base, BASE);
    assert.equal(before.registrySha256, after.registrySha256);
    assert.deepEqual(after.rows.map(row => row.path), routes);
    for (const row of after.rows) {
        const old = before.rows.find(r => r.path === row.path); assert.ok(old);
        for (const field of ['status', 'finalUrl', 'contentType', 'xRobots', 'meta', 'jsonLd', 'courseGate']) {
            assert.deepEqual(row[field], old[field], 'Unchanged exact HTTP/SEO ' + field + ' ' + row.path);
        }
        if (targetPaths.includes(row.path)) {
            assert.ok(row.learner && old.learner, 'Complete target learning content');
            assert.deepEqual(row.learner.words, old.learner.words, 'No lost/duplicated target learning words ' + row.path);
            assert.deepEqual(row.learner.orderedWords, old.learner.orderedWords, 'Exact learning word/source order including meaningful numerals ' + row.path);
            assert.deepEqual(row.learner.details, old.learner.details, 'Exact own point/details preservation ' + row.path);
            assert.deepEqual(row.learner.tables, old.learner.tables, 'All source table columns/cells/row relationships preserved ' + row.path);
            assert.deepEqual(row.learner.practice, old.learner.practice, 'Exact practice state/control/key preservation ' + row.path);
            assert.deepEqual(row.learner.anchors.filter(id => old.learner.anchors.includes(id)), old.learner.anchors,
                'Original anchors remain in exact source order ' + row.path);
            assert.equal(new Set(row.learner.anchors).size, row.learner.anchors.length, 'No duplicate anchors');
        } else {
            assert.deepEqual(row.learner, old.learner, 'Unchanged reference/locale/control learning content ' + row.path);
        }
    }
    for (const route of Object.keys(before.extras)) {
        for (const field of ['status', 'finalUrl', 'contentType', 'xRobots', 'bodySha256', 'count', 'orderedSha256', 'safeJson'])
            assert.deepEqual(after.extras[route][field], before.extras[route][field], 'Unchanged sitemap/robots/runtime ' + route + ':' + field);
    }
    return {pass: true, guestGetRows: routes.length + 4, targets: 42, m41References: 3, localeControls: localeControls.length,
        seoExact: true, structuredDataExact: true, sitemapOrderedExact: true, sourceWordCountsExact: true, practiceExact: true, dbWriteAuthorization: false};
}
async function run() {
    const [label, beforeName] = process.argv.slice(2);
    assert.match(label, /^(?:before|after|after-final)-v[1-9][0-9]*$/u);
    if (!label.startsWith('before')) assert.match(beforeName, /^before-v[1-9][0-9]*-http\.json$/u);
    fs.mkdirSync(PRIVATE, {recursive: true}); const result = await capture();
    const file = path.join(PRIVATE, label + '-http.json');
    fs.writeFileSync(file, JSON.stringify(result, null, 2) + '\n', {flag: 'wx'});
    const checked = !label.startsWith('before') && result.pass ? compare(JSON.parse(fs.readFileSync(path.join(PRIVATE, beforeName))), result) : null;
    console.log(JSON.stringify({pass: result.pass, file, sha256: sha(fs.readFileSync(file)), rows: result.rows.length,
        errors: result.rows.filter(r => r.error || r.status !== 200).map(r => ({path: r.path, status: r.status, error: r.error})), checked}));
    if (!result.pass) process.exitCode = 1;
}
if (require.main === module) run().catch(error => {console.error(error.stack); process.exitCode = 1;});
module.exports = {BASE, PRIVATE, registry, targetPaths, referencePaths, localeControls, controls, routes, words, learner, jsonLd, assertRoute, guestGet, capture, compare};
