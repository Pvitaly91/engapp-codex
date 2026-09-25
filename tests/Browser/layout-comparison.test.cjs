'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const {verifyComparison, recompute, distribution} = require('../../tools/diagnostics/verify-layout-comparison.cjs');
const clone = value => JSON.parse(JSON.stringify(value));
const hash = 'a'.repeat(64);
function fixture() {
    const pages = [['sentence-types', '/theory/basic-grammar/sentence-types'],
        ['present-perfect', '/theory/tenses/present-perfect/present-perfect-forms'],
        ['questions', '/test/future-perfect/questions']];
    const records = [];
    for (const [name, url] of pages) for (const viewport of ['desktop', 'mobile']) for (const cache of ['cold', 'repeat']) for (let run = 1; run <= 5; run++) {
        const shifts = [{timestamp: 2000, value: 0.125, hadRecentInput: false},
            {timestamp: 3000, value: 0.25, hadRecentInput: false},
            {timestamp: 3100, value: 4, hadRecentInput: true},
            {timestamp: 12001, value: 8, hadRecentInput: false}];
        records.push({name, url, viewport, cache, run, metric: {status: 'complete', reasons: [], ...recompute(shifts, 12000), lcp: 1800},
            httpStatus: 200, pageErrors: 0, guardErrors: 0, blocked: [], networkFailures: [],
            network: [{url: 'https://fonts.googleapis.com/css2', status: 200, type: 'Stylesheet', mime: 'text/css'},
                {url: 'https://fonts.gstatic.com/font.woff2', status: 200, type: 'Font', mime: 'font/woff2'}],
            trace: {complete: true, requestedStart: 0, requestedEnd: 12000, observationEndedAt: 12001,
                timings: {load: 1500, fontsReady: 1800, alpineInitialized: 1600, sidebarReady: 2000, alpineInit: [1550]},
                fontStatus: 'loaded', unsupported: [], visibility: [{state: 'visible'}], iframeCount: 0,
                fcp: 1700, lcpEntries: [{timestamp: 1800}], shifts}});
    }
    const summary = [];
    for (const [name, url] of pages) for (const viewport of ['desktop', 'mobile']) for (const cache of ['cold', 'repeat']) {
        summary.push({name, url, viewport, cache, attempted: 5, complete: 5, acceptanceComplete: true, incompleteRuns: [],
            clsSessionWindow: distribution([0.25]), legacyShiftSum: distribution([0.375]), lcp: distribution([1800])});
    }
    return {schema: 'gramlyze-local-cls-m31-v1', label: 'synthetic', startedAt: '2026-09-08T12:00:00Z', finishedAt: '2026-09-08T12:13:00Z',
        browser: '147', playwright: '1.62.1', node: 'v22', dependencies: {vite: '5', tailwindPublic: '3', tailwindLegacy: '4', vitest: '2'},
        packageLockSha256: hash, sourceSha256: {'tools/diagnostics/public-layout-shifts.cjs': hash, 'tools/diagnostics/cls-session-window.cjs': hash},
        conditions: {base: 'http://gramlyze.loc', windowMs: 12000, settledMs: 2000, runsPerCacheAndViewport: 5,
            viewports: [{name: 'desktop', width: 1440, height: 1000, mobile: false}, {name: 'mobile', width: 390, height: 844, mobile: true}]}, records, summary};
}
test('complete matching sixty-run evidence passes without changing input', () => {
    const baseline = fixture(), after = fixture(), original = clone(after);
    const result = verifyComparison(baseline, after);
    assert.equal(result.pass, true, result.errors.join('\n'));
    assert.equal(result.after.verifiedRecords, 60);
    assert.equal(result.after.googleFonts, 60);
    assert.deepEqual(after, original);
});
test('an eight-record interrupted run and duplicate or incomplete runs fail closed', () => {
    for (const mutate of [r => { r.records.length = 8; delete r.finishedAt; },
        r => { r.records[1] = clone(r.records[0]); }, r => { r.records[0].metric.status = 'incomplete'; }]) {
        const after = fixture(); mutate(after); assert.equal(verifyComparison(fixture(), after).pass, false);
    }
});
test('browser, conditions, dependencies, package lock and either probe hash must match', () => {
    for (const mutate of [r => { r.browser = '148'; }, r => { r.conditions.extra = true; },
        r => { r.dependencies.vite = '6'; }, r => { r.packageLockSha256 = 'b'.repeat(64); },
        ...Object.keys(fixture().sourceSha256).map(file => r => { r.sourceSha256[file] = 'b'.repeat(64); })]) {
        const after = fixture(); mutate(after); assert.equal(verifyComparison(fixture(), after).pass, false);
    }
});
test('font stylesheet denial fails even when FontFaceSet says loaded and stored status says complete', () => {
    const after = fixture();
    after.records[0].network.shift();
    after.records[0].networkFailures.push({url: 'https://fonts.googleapis.com/css2', error: 'net::ERR_NETWORK_ACCESS_DENIED'});
    const result = verifyComparison(fixture(), after);
    assert.equal(result.pass, false);
    assert.ok(result.errors.some(error => error.includes('unexpected resource failure')));
    assert.ok(result.errors.some(error => error.includes('missing/failed Google font stylesheet')));
});
test('missing fonts, font HTTP errors and unrelated resource failures cannot pass', () => {
    for (const mutate of [row => { row.network.pop(); }, row => { row.network[1].status = 404; },
        row => { row.networkFailures.push({url: 'http://gramlyze.loc/build/a.js', error: 'net::ERR_ABORTED'}); },
        row => { row.network.push({url: 'http://gramlyze.loc/a.css', status: 500}); }]) {
        const after = fixture(); mutate(after.records[0]); assert.equal(verifyComparison(fixture(), after).pass, false);
    }
});
test('only matching Questions Fetch204 ERR_ABORTED is excluded, with an explicit count', () => {
    const after = fixture(), row = after.records.find(row => row.name === 'questions');
    const url = `http://gramlyze.loc${row.url}/state`;
    row.network.push({url, status: 204, type: 'Fetch'});
    row.networkFailures.push({url, error: 'net::ERR_ABORTED'});
    assert.equal(verifyComparison(fixture(), after).pass, true);
    assert.equal(verifyComparison(fixture(), after).after.expectedState204Aborts, 1);
    row.networkFailures.push({url, error: 'net::ERR_ABORTED'});
    assert.equal(verifyComparison(fixture(), after).pass, false, 'one response cannot excuse two failures');
    row.networkFailures.pop(); row.network.at(-1).status = 500;
    assert.equal(verifyComparison(fixture(), after).pass, false);
});
test('raw metric or aggregate tampering is rejected, while intentional layout source changes are allowed', () => {
    const after = fixture();
    after.sourceSha256['resources/css/catalog-public.css'] = 'b'.repeat(64);
    assert.equal(verifyComparison(fixture(), after).pass, true);
    after.records[0].metric.clsSessionWindow = 0;
    assert.equal(verifyComparison(fixture(), after).pass, false);
    after.records[0].metric.clsSessionWindow = 0.25; after.summary[0].lcp.max = 100;
    assert.equal(verifyComparison(fixture(), after).pass, false);
});
test('independent recomputation retains raw cutoff/input semantics and strict session boundaries', () => {
    const shift = (timestamp, value = 0.125, hadRecentInput = false) => ({timestamp, value, hadRecentInput});
    assert.equal(recompute([shift(0), shift(999), shift(1999)], 12000).clsSessionWindow, 0.25);
    assert.equal(recompute([0, 900, 1800, 2700, 3600, 4500, 5000].map(t => shift(t)), 12000).clsSessionWindow, 0.75);
    assert.equal(recompute([shift(0), shift(900, 2, true), shift(1100)], 12000).clsSessionWindow, 0.125);
    assert.throws(() => recompute([shift(2), shift(1)], 12000));
});
