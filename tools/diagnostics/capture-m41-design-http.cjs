'use strict';
// Fresh guest GET-only design follow-up. Never requests production or saves response bodies/secrets.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const prior = require('./capture-m41-http.cjs');
const {metadata, htmlText} = require('./seo-m28-local.cjs');
const ROOT = path.resolve(__dirname, '../..');
const BASE = 'http://gramlyze.loc';
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const masterBytes = fs.readFileSync(path.join(ROOT, 'docs/content/m41-authored-tense-comparisons.v1.0.1.json'));
assert.equal(sha(masterBytes), '9020cf977e903c76f93c7165bbe402567b5fbf40a98a8139dc6751b223b34553');
const master = JSON.parse(masterBytes);
// The design follow-up requires theory/test/control evidence, not course acceptance.
// A prior optional course timeout remains in exclusive before-v1 evidence; never mask it.
const routes = prior.routes.filter(route => route !== prior.coursePath);
async function capture() {
    const rows = [];
    for (const route of routes) {
        prior.assertRoute(route); console.log(JSON.stringify({checking: route}));
        const attempts = []; let response, body;
        for (let attempt = 1; attempt <= 2; attempt++) {
            const started = new Date().toISOString();
            try {
                response = await fetch(BASE + route, {redirect: 'manual', signal: AbortSignal.timeout(30000),
                    headers: {Accept: 'text/html', Connection: 'close'}});
                body = await response.text();
                attempts.push({attempt, started, finished: new Date().toISOString(), status: response.status}); break;
            } catch (error) {
                attempts.push({attempt, started, finished: new Date().toISOString(), name: error.name, message: error.message, code: error.cause?.code || null});
            }
        }
        if (!response || response.status !== 200 || body === undefined) {
            rows.push({path: route, checkedAt: new Date().toISOString(), status: response?.status || null, attempts, error: 'Guest GET failed'}); continue;
        }
        const dom = new JSDOM(body), doc = dom.window.document;
        try {
            const main = doc.querySelector('[data-theory-main]')?.cloneNode(true);
            if (route.startsWith('/theory/')) assert.ok(main, 'Full theory main ' + route);
            main?.querySelectorAll('[x-data],script,style,[data-theory-ui]').forEach(node => node.remove());
            rows.push({path: route, checkedAt: new Date().toISOString(), status: response.status, attempts,
                contentType: response.headers.get('content-type'), meta: metadata(doc), xRobots: response.headers.get('x-robots-tag'),
                mainTextSha256: main ? sha(norm(main.textContent)) : null,
                details: doc.querySelectorAll('[data-theory-native-extension] > details').length,
                legacyIds: [...doc.querySelectorAll('[id^="lesson-block-"],[id^="self-check-"]')].map(node => node.id),
                nativeBlockIds: [...doc.querySelectorAll('[data-theory-main] [id^="block-"]')].map(node => node.id)});
        } finally {dom.window.close();}
    }
    const response = await fetch(BASE + '/sitemap.xml', {redirect: 'manual', signal: AbortSignal.timeout(30000),
        headers: {Accept: 'application/xml', Connection: 'close'}});
    assert.equal(response.status, 200, 'Actual local sitemap GET');
    const dom = new JSDOM(await response.text(), {contentType: 'text/xml'});
    const urls = [...dom.window.document.querySelectorAll('loc')].map(node => node.textContent); dom.window.close();
    return {at: new Date().toISOString(), base: BASE, rows, sitemap: {count: urls.length, sha256: sha(JSON.stringify(urls))},
        scope: '54 required guest routes. Course URL excluded from acceptance scope; its exact earlier timeout is retained in before-v1-http.json.',
        pass: rows.every(row => row.status === 200 && !row.error)};
}
function orderedText(node, fragments, label) {
    assert.ok(node, 'Missing authored node ' + label);
    const text = norm(node.textContent); let offset = 0;
    for (const fragment of fragments) {
        const wanted = norm(fragment); const index = text.indexOf(wanted, offset);
        assert.ok(index >= 0, 'Exact ordered author fragment ' + label + ': ' + wanted.slice(0, 80));
        offset = index + wanted.length;
    }
}
function contentFidelity(doc, lesson) {
    const records = [];
    for (const section of lesson.sections) {
        const root = doc.querySelector('[data-m41-author-section="' + section.id + '"]');
        assert.ok(root, 'Exact authored section ' + section.id);
        orderedText(root.querySelector('h2'), [section.title], section.id + ' heading');
        for (const point of section.points) {
            const node = doc.getElementById(point.id); assert.ok(node, 'Exact author point anchor ' + point.id);
            const basic = node.cloneNode(true);
            basic.querySelectorAll('[data-theory-native-extension],noscript,script,style').forEach(n => n.remove());
            const expected = [point.title, ...point.paragraphs_uk, ...point.examples.flatMap(e => [e.en, e.uk])];
            orderedText(basic, expected, point.id);
            const details = node.querySelectorAll('[data-theory-native-extension] > details');
            assert.equal(details.length, point.detail ? 1 : 0, 'Only exact own author detail ' + point.id);
            if (point.detail) {
                const d = point.detail;
                orderedText(details[0].querySelector('.theory-point-fragment'),
                    [d.title, ...d.paragraphs_uk, ...d.examples.flatMap(e => [e.en, e.uk])], point.id + ' detail');
            }
            records.push({id: point.id, basic: true, ownDetail: Boolean(point.detail)});
        }
        if (section.table) {
            const nativeForms = root.querySelector('[data-m41-native-component="forms-grid"]');
            if (nativeForms) {
                assert.equal(root.querySelectorAll('table').length, 0, 'Source table rendered once as native cards, never duplicated');
                const columns = [...root.querySelectorAll('[data-m41-form-column]')];
                assert.deepEqual(columns.map(n => Number(n.dataset.m41FormColumn)), [0, 1, 2]);
                assert.deepEqual(columns.map(n => norm(n.textContent)), section.table.columns.map(norm));
                const rows = [...root.querySelectorAll('[data-m41-form-row]')];
                assert.deepEqual(rows.map(n => Number(n.dataset.m41FormRow)), [0, 1, 2]);
                assert.equal(root.querySelectorAll('[data-m41-form-cell]').length, 6);
                for (const [rowIndex, row] of rows.entries()) {
                    const cells = [...row.querySelectorAll('[data-m41-form-cell]')];
                    assert.deepEqual(cells.map(n => n.dataset.m41FormCell), [rowIndex + '-1', rowIndex + '-2']);
                    const actual = [norm(row.querySelector('[data-m41-form-row-label]').textContent)];
                    for (const cell of cells) {
                        assert.equal(cell.querySelectorAll('[data-m41-form-en]').length, 1);
                        assert.equal(cell.querySelectorAll('[data-m41-form-uk]').length, 1);
                        actual.push(norm(cell.querySelector('[data-m41-form-en]').textContent + '\n'
                            + cell.querySelector('[data-m41-form-uk]').textContent));
                    }
                    assert.deepEqual(actual, section.table.rows[rowIndex].map(norm), 'Exact native-card source cells ' + section.id + ':' + rowIndex);
                }
            } else {
                const table = root.querySelector('table'); assert.ok(table, 'Complete authored table ' + section.id);
                assert.deepEqual([...table.querySelectorAll('thead th')].map(n => norm(n.textContent)), section.table.columns.map(norm));
                assert.deepEqual([...table.querySelectorAll('tbody tr')].map(row => [...row.querySelectorAll('td,th')].map(n => htmlText(n.innerHTML))),
                    section.table.rows.map(row => row.map(norm)), 'Exact bilingual source table ' + section.id);
            }
        }
    }
    const details = records.filter(row => row.ownDetail).length;
    assert.equal(details, lesson.sections.flatMap(s => s.points).filter(p => p.detail).length);
    assert.equal(doc.querySelectorAll('[data-m41-ui-case]').length, 6);
    assert.equal(doc.querySelectorAll('[data-m41-control]').length, lesson.practice.flatMap(t => t.controls).length);
    return {path: lesson.theory_path, points: records, details, tasks: 6,
        controls: lesson.practice.flatMap(t => t.controls).length, tableExact: true};
}
async function authored() {
    const rows = [];
    for (const lesson of master.lessons) {
        prior.assertRoute(lesson.theory_path);
        const response = await fetch(BASE + lesson.theory_path, {redirect: 'manual',
            signal: AbortSignal.timeout(30000), headers: {Accept: 'text/html', Connection: 'close'}});
        assert.equal(response.status, 200);
        const dom = new JSDOM(await response.text());
        try {rows.push(contentFidelity(dom.window.document, lesson));} finally {dom.window.close();}
    }
    return {at: new Date().toISOString(), authorSha256: sha(masterBytes), rows};
}
function compare(before, after) {
    assert.equal(before.pass, true); assert.equal(after.pass, true);
    assert.equal(before.base, BASE); assert.equal(after.base, BASE);
    assert.deepEqual(after.rows.map(row => row.path), routes);
    assert.deepEqual(after.sitemap, before.sitemap, 'Exact ordered sitemap equality');
    for (const row of after.rows) {
        const old = before.rows.find(item => item.path === row.path); assert.ok(old);
        for (const field of ['status', 'contentType', 'meta', 'xRobots', 'details', 'legacyIds'])
            assert.deepEqual(row[field], old[field], 'Exact protected ' + field + ' ' + row.path);
        if (prior.targetPaths.includes(row.path)) {
            assert.deepEqual(row.nativeBlockIds.filter(id => old.nativeBlockIds.includes(id)), old.nativeBlockIds,
                'Existing target native/disclosure anchors preserved in exact order; additional component wrappers are allowed');
            assert.equal(new Set(row.nativeBlockIds).size, row.nativeBlockIds.length, 'All target native component anchors remain unique');
        } else {
            assert.deepEqual(row.nativeBlockIds, old.nativeBlockIds, 'Exact unchanged control native anchors');
            assert.equal(row.mainTextSha256, old.mainTextSha256, 'Exact unchanged control content ' + row.path);
            assert.deepEqual(row.courseReadOnly, old.courseReadOnly, 'Unchanged course server properties');
        }
    }
    assert.deepEqual(after.author, before.author && {...before.author, at: after.author.at}, 'Exact master fidelity inventory');
    return {pass: true, guestGetRows: after.rows.length, metadataEquality: true,
        orderedSitemapEquality: true, controlsUnchanged: prior.controls.length,
        authoredPoints: after.author.rows.reduce((n, row) => n + row.points.length, 0),
        details: after.author.rows.map(row => row.details), dbWriteAuthorization: false};
}
async function run() {
    const [dir, label, beforeName] = process.argv.slice(2);
    assert.equal(path.resolve(dir), path.resolve('D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-design-local'));
    assert.match(label, /^(?:before|after|after-final)-v[1-9][0-9]*$/u);
    if (!label.startsWith('before')) assert.match(beforeName, /^before-v[1-9][0-9]*-http\.json$/u,
        'After evidence must explicitly select its fresh complete before baseline');
    fs.mkdirSync(dir, {recursive: true});
    const result = await capture();
    if (result.pass) {
        try {result.author = await authored();}
        catch (error) {result.authorError = {name: error.name, message: error.message}; result.pass = false;}
    }
    const file = path.join(dir, label + '-http.json');
    fs.writeFileSync(file, JSON.stringify(result, null, 2) + '\n', {flag: 'wx'});
    let checked = null;
    if (!label.startsWith('before') && result.pass)
        checked = compare(JSON.parse(fs.readFileSync(path.join(dir, beforeName), 'utf8')), result);
    console.log(JSON.stringify({pass: result.pass, label, file, sha256: sha(fs.readFileSync(file)),
        rows: result.rows.length, errors: result.rows.filter(row => row.error).length, sitemap: result.sitemap, checked}));
    if (!result.pass) process.exitCode = 1;
}
if (require.main === module) run().catch(error => {console.error(error.stack); process.exitCode = 1;});
module.exports = {orderedText, contentFidelity, authored, compare, capture, routes, BASE};
