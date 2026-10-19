'use strict';
// M23: fixed local-only, read-only acceptance against the authored master.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const shared = require('./seo-m11-local.cjs');

const lessons = Object.freeze({
    'nominal-style-and-information-density': {
        theory: '/theory/formal-english/nominal-style-and-information-density',
        test: '/test/formal-english/nominal-style-and-information-density',
        tables: 1
    },
    'c1-mixed-revision': {
        theory: '/theory/mixed-revision/c1-mixed-revision',
        test: '/test/mixed-revision/c1-mixed-revision',
        tables: 1
    },
    'c2-mixed-revision': {
        theory: '/theory/mixed-revision/c2-mixed-revision',
        test: '/test/mixed-revision/c2-mixed-revision',
        tables: 0
    }
});
const slugs = Object.freeze(Object.keys(lessons));
const theory = slug => { assert.ok(Object.hasOwn(lessons, slug)); return lessons[slug].theory; };
const test = slug => { assert.ok(Object.hasOwn(lessons, slug)); return lessons[slug].test; };
const controls = Object.freeze([
    '/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance',
    '/theory/formal-english/nominalisation-formal-register'
]);
const course = '/courses/english-grammar-theory/lesson/formal-english/nominal-style-and-information-density';
const PATHS = Object.freeze([...slugs.map(theory), ...slugs.map(test), course, ...controls, '/sitemap.xml']);
const profile = Object.freeze({id: 'm23', slugs, theory, test, course, paths: PATHS, extendedChecks: true, richContent: true});
const MODES = Object.freeze(['capture', 'browser-applied', 'compare']);
const decision = (url, method = 'GET', navigation = false) => shared.decision(url, method, navigation, profile);
function assertProtectedData(evidence) {
    assert.ok(evidence?.before && evidence?.after, 'Real before/after DB fingerprints required');
    const {before, after} = evidence;
    assert.ok(Object.keys(before.tables).length >= 40);
    assert.deepEqual(after.tables, before.tables, 'Protected rows and question banks changed');
    assert.equal(after.env_sha256, before.env_sha256, 'Working environment changed');
    assert.deepEqual(after.accepted_sources_match, before.accepted_sources_match);
    assert.equal(Object.keys(after.accepted_sources_match).length, 36);
    assert.ok(Object.values(after.accepted_sources_match).every(Boolean));
}
function compareCaptures(before, after, evidence) {
    assertProtectedData(evidence);
    for (const capture of [before, after]) {
        assert.equal(capture.base, 'http://gramlyze.loc');
        assert.equal(capture.package, 'm23');
        assert.deepEqual(capture.rows.map(r => r.path), PATHS);
    }
    const rows = before.rows.map((old, i) => {
        const next = after.rows[i];
        assert.equal(old.status, 200, old.path + ' before status');
        assert.equal(next.status, 200, old.path + ' after status');
        for (const field of ['location', 'contentType', 'xRobotsTag']) assert.deepEqual(next[field], old[field], old.path + ' ' + field);
        if (old.path === '/sitemap.xml') {
            assert.deepEqual(next.orderedLocs, old.orderedLocs, 'Ordered sitemap changed');
            assert.equal(next.orderedSha256, old.orderedSha256);
            return {path: old.path, unchanged: true, urls: next.orderedLocs.length, orderedSha256: next.orderedSha256};
        }
        const isTarget = slugs.some(slug => theory(slug) === old.path);
        for (const key of Object.keys(old.metadata)) {
            if (!(isTarget && ['description', 'ogDescription', 'twitterDescription'].includes(key))) {
                assert.deepEqual(next.metadata[key], old.metadata[key], old.path + ' ' + key);
            }
        }
        if (isTarget) {
            assert.equal(next.metadata.description.length, 1);
            assert.ok(next.metadata.description[0].trim());
            assert.deepEqual(next.metadata.ogDescription, next.metadata.description);
            assert.deepEqual(next.metadata.twitterDescription, next.metadata.description);
            assert.equal(next.anchorPlaceholder, false);
            assert.equal(next.selfChecks, 1);
            assert.equal(next.selfCheckQuestions, 6);
            assert.equal(next.selfCheckKeys, 6);
            assert.ok(next.richSections >= 5 && next.richExamples > 0);
        }
        if (controls.includes(old.path)) {
            assert.equal(next.textSha256, old.textSha256, 'Protected control content: ' + old.path);
            assert.equal(next.richSections, old.richSections);
        }
        assert.deepEqual(next.testLinks, old.testLinks, old.path + ' main test links');
        return {path: old.path, protectedMetadataUnchanged: true, descriptionChanged: JSON.stringify(next.metadata.description) !== JSON.stringify(old.metadata.description)};
    });
    return {at: new Date().toISOString(), base: after.base, package: 'm23', pass: true, before: before.at, after: after.at, rows};
}
function assertBrowserTables(report) {
    const mobile = report.rows.filter(r => r.mobile);
    assert.equal(mobile.length, 3);
    for (const row of mobile) {
        assert.ok(row.pass && !row.overflow, row.path + ' mobile acceptance');
        const lesson = Object.values(lessons).find(l => l.theory === row.path);
        assert.ok(lesson);
        if (lesson.tables) {
            assert.ok(row.tableScroll?.contentWidth >= 720, row.path + ' readable table columns');
            assert.ok(row.tableScroll.left > 0, row.path + ' local horizontal scroll');
        } else assert.equal(row.tableScroll, undefined, 'C2 revision intentionally has no table');
    }
}
async function browserAcceptance(dir, label) {
    const ok = await shared.browserChecks(dir, label, true, profile);
    assertBrowserTables(JSON.parse(fs.readFileSync(path.join(dir, label + '-browser.json'), 'utf8')));
    await controlBrowserChecks(dir, label);
    return ok;
}
async function controlBrowserChecks(dir, label) {
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true,
        ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    const rows = [];
    try {
        for (const [index, control] of controls.entries()) for (const mobile of [false, true]) {
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 900},
                isMobile: mobile, hasTouch: mobile});
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', error => errors.push(error.name));
            await page.route('**/*', route => {
                const request = route.request();
                const refusal = decision(request.url(), request.method(), request.isNavigationRequest());
                return refusal ? route.abort() : route.continue();
            });
            const response = await page.goto('http://gramlyze.loc' + control, {waitUntil: 'domcontentloaded', timeout: 30000});
            await page.locator('[data-theory-main] h1').waitFor({state: 'visible'});
            const h1 = (await page.locator('[data-theory-main] h1').innerText()).trim();
            const content = await page.locator('[data-theory-main] article .theory-rich-content').count();
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
            const screenshot = label + '-control-' + index + '-' + (mobile ? 'mobile' : 'desktop') + '.png';
            await page.screenshot({path: path.join(dir, screenshot), fullPage: true});
            const row = {path: control, mobile, status: response.status(), h1, richContent: content > 0,
                pageErrors: errors, overflow, screenshot};
            assert.equal(row.status, 200);
            assert.ok(row.h1 && row.richContent && !row.overflow && !row.pageErrors.length);
            rows.push(row);
            await context.close();
        }
    } finally { await browser.close(); }
    fs.writeFileSync(path.join(dir, label + '-controls-browser.json'), JSON.stringify({at: new Date().toISOString(), rows}, null, 2), {flag: 'wx'});
    return rows;
}
if (require.main === module) {
    const [mode, dir, label, afterLabel] = process.argv.slice(2);
    assert.ok(MODES.includes(mode), 'M23 accepts only real capture/browser-applied/compare');
    assert.match(label || '', /^[a-z0-9-]+$/);
    fs.mkdirSync(dir, {recursive: true});
    if (mode === 'compare') {
        assert.match(afterLabel || '', /^[a-z0-9-]+$/);
        const read = name => JSON.parse(fs.readFileSync(path.join(dir, name + '-http.json'), 'utf8'));
        const fingerprints = phase => JSON.parse(fs.readFileSync(path.join(dir, 'protected-' + phase + '.json'), 'utf8'));
        const result = compareCaptures(read(label), read(afterLabel), {before: fingerprints('before'), after: fingerprints('after')});
        fs.writeFileSync(path.join(dir, afterLabel + '-comparison.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
        console.log(JSON.stringify(result.rows, null, 2));
    } else {
        (mode === 'capture' ? shared.capture(dir, label, profile) : browserAcceptance(dir, label))
            .then(ok => { if (ok === false) process.exitCode = 1; })
            .catch(error => { console.error(error.message); process.exitCode = 1; });
    }
}
module.exports = {lessons, slugs, theory, test, controls, course, PATHS, profile, MODES, decision, assertProtectedData, compareCaptures, assertBrowserTables, controlBrowserChecks};
