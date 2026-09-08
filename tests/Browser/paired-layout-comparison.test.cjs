'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const {verifyPairedComparison, bootstrapPairedStats, recompute} = require('../../tools/diagnostics/verify-layout-comparison.cjs');

const hash = 'a'.repeat(64), otherHash = 'b'.repeat(64);
const clone = value => JSON.parse(JSON.stringify(value));
const epoch = Date.parse('2026-09-08T12:00:00Z');
const timestamp = seconds => new Date(epoch + seconds * 1000).toISOString();
function fixture() {
    const pages = [['sentence-types', '/theory/basic-grammar/sentence-types'],
        ['present-perfect', '/theory/tenses/present-perfect/present-perfect-forms']];
    const shared = {php: '8.2.12', sapi: 'cli-server', dependencies: {vite: '5.4.20', tailwindPublic: '3.4.17'},
        packageLockSha256: hash, composerLockSha256: hash, runtimePolicySha256: hash, dataSha256: hash,
        probeSha256: {'tools/diagnostics/public-layout-shifts.cjs': hash, 'tools/diagnostics/cls-session-window.cjs': hash}};
    const variants = {
        A: {...clone(shared), gitSha: '7c649117e747fb922da166c2e1fba59474fee203', base: 'http://127.0.0.1:9011', storageId: '/private/A/storage', sessionId: '/private/A/session', buildId: '/private/A/build'},
        B: {...clone(shared), gitSha: '54d34083a67e30084b967b572cc5a6b93c2eaf99', base: 'http://127.0.0.1:9012', storageId: '/private/B/storage', sessionId: '/private/B/session', buildId: '/private/B/build'},
    };
    const schedule = [], records = [];
    for (let run = 1; run <= 10; run++) for (const [name, path] of pages) {
        const pairId = `pair-${run}-${name}`, order = run % 2 ? 'AB' : 'BA';
        schedule.push({pairId, name, path, order});
        for (const variant of order) {
            const shifts = [{timestamp: 1100, value: 0.25, hadRecentInput: false},
                {timestamp: 1300, value: 0.5, hadRecentInput: true}, {timestamp: 12001, value: 0.125, hadRecentInput: false}];
            const lcp = 1800 + run * 20 + (variant === 'B' ? 100 : 0);
            const fontResources = [{kind: 'stylesheet', url: 'https://fonts.googleapis.com/css2', status: 200, mime: 'text/css', sha256: hash, bytes: 123},
                {kind: 'font', url: 'https://fonts.gstatic.com/font.woff2', status: 200, mime: 'font/woff2', sha256: otherHash, bytes: 456}];
            const network = fontResources.map(resource => ({url: resource.url, status: resource.status,
                type: resource.kind === 'font' ? 'Font' : 'Stylesheet', mime: resource.mime,
                sha256: resource.sha256, bodyBytes: resource.bytes, diskCache: false, serviceWorker: false}));
            records.push({pairId, variant, name, url: path, run, viewport: 'mobile', cache: 'cold',
                startedAt: timestamp(records.length * 14), finishedAt: timestamp(records.length * 14 + 13), contextId: `context-${records.length}`,
                httpStatus: 200, pageErrors: 0, guardErrors: 0, blocked: [], networkFailures: [], network, fontResources,
                standResponse: {sapi: shared.sapi, php: shared.php, dataReadOnly: 'sqlite-mode-ro-query-only'},
                usedFonts: [{familyName: 'Synthetic font', isCustomFont: true, glyphCount: 100}],
                metric: {status: 'complete', reasons: [], ...recompute(shifts, 12000), lcp},
                trace: {complete: true, requestedStart: 0, requestedEnd: 12000, observationEndedAt: 12001,
                    timings: {load: 1400, fontsReady: 1600, alpineInitialized: 1500, sidebarReady: 1800, alpineInit: [1490]},
                    fontStatus: 'loaded', unsupported: [], visibility: [{state: 'visible'}], iframeCount: 0,
                    fcp: 1500, lcpEntries: [{timestamp: 1600}, {timestamp: lcp}], shifts}});
        }
    }
    return {schema: 'gramlyze-isolated-paired-m32-v1', label: 'synthetic-paired', startedAt: timestamp(0), finishedAt: timestamp(560),
        browser: '147.0.0.0', playwright: '1.62.1', node: 'v22.0.0', fontMode: 'natural-network',
        plan: {frozenAt: timestamp(-60), pairsPerPage: 10, cache: 'cold', viewport: {width: 390, height: 844, mobile: true},
            windowMs: 12000, settledMs: 2000, schedule}, variants,
        safety: {workingFilesBeforeSha256: hash, workingFilesAfterSha256: hash, workingFilesChanged: [], dataReadOnly: true, noParallelWork: true}, records};
}
const failed = (mutate, message) => {
    const report = fixture(); mutate(report);
    const result = verifyPairedComparison(report);
    assert.equal(result.pass, false, message);
    return result;
};

test('fixed forty-run paired report passes independently and retains all attempts without input mutation', () => {
    const report = fixture(), original = clone(report), result = verifyPairedComparison(report);
    assert.equal(result.pass, true, result.errors.join('\n'));
    assert.equal(result.verifiedRecords, 40);
    assert.equal(result.attempts.length, 40);
    for (const page of result.pages) {
        assert.equal(page.paired.n, 10); assert.equal(page.validRuns.length, 20);
        assert.equal(page.paired.deltaMs.median, 100);
        assert.deepEqual(page.paired.deltaMs.ci95, {low: 100, high: 100});
    }
    assert.deepEqual(report, original);
});

test('count, frozen plan, AB/BA balance, execution order and fresh contexts are mandatory', () => {
    for (const mutate of [r => r.records.pop(), r => r.records.push(clone(r.records[0])),
        r => { r.records[1] = clone(r.records[0]); }, r => { r.plan.frozenAt = timestamp(1); },
        r => { r.plan.schedule[0].order = 'BA'; }, r => { r.plan.pairsPerPage = 11; },
        r => { r.plan.viewport.width = 391; }, r => { r.plan.cache = 'repeat'; },
        r => { r.records[1].contextId = r.records[0].contextId; },
        r => { r.records[1].startedAt = timestamp(1); }, r => { delete r.finishedAt; }]) failed(mutate);
});

test('exact git variants, loopback origin, dependencies, probe, backend and data parity cannot be relabelled', () => {
    for (const mutate of [r => { r.variants.A.gitSha = r.variants.B.gitSha; },
        r => { r.variants.B.base = 'http://gramlyze.loc'; }, r => { r.variants.B.base = 'http://127.0.0.1:99999'; },
        ...['php', 'sapi'].map(key => r => { r.variants.B[key] = 'different'; }),
        ...['packageLockSha256', 'composerLockSha256', 'runtimePolicySha256', 'dataSha256'].map(key => r => { r.variants.B[key] = otherHash; }),
        r => { r.variants.B.dependencies.vite = '6'; }, r => { r.variants.A.dependencies = {}; },
        r => { r.variants.B.probeSha256['tools/diagnostics/public-layout-shifts.cjs'] = otherHash; },
        r => { delete r.variants.B.probeSha256['tools/diagnostics/cls-session-window.cjs']; },
        r => { delete r.browser; }]) failed(mutate);
});

test('separate runtime identities and unchanged working-file/read-only safety evidence are required', () => {
    for (const mutate of [...['base', 'storageId', 'sessionId', 'buildId'].map(key => r => { r.variants.B[key] = r.variants.A[key]; }),
        r => { r.safety.workingFilesAfterSha256 = otherHash; }, r => { r.safety.workingFilesChanged = ['snapshot']; },
        r => { r.safety.dataReadOnly = false; }, r => { r.safety.noParallelWork = false; }, r => { delete r.safety; }]) failed(mutate);
});

test('each actual stand response must confirm the declared PHP/SAPI and read-only SQLite policy', () => {
    for (const mutate of [r => { delete r.records[0].standResponse; },
        r => { r.records[0].standResponse.sapi = 'apache2handler'; },
        r => { r.records[0].standResponse.php = '8.3.0'; },
        r => { r.records[0].standResponse.dataReadOnly = 'sqlite-mode-rw'; },
        r => { r.records[0].standResponse.dataReadOnly = true; }]) {
        const result = failed(mutate);
        assert.ok(result.attempts[0].reasons.some(reason => reason.includes('actual stand response')));
        assert.equal(result.verifiedRecords, 39);
    }
});

test('font status, matching URL or HTTP 200 cannot replace stylesheet and font byte identity', () => {
    for (const mutate of [r => { delete r.records[1].fontResources[0].sha256; },
        r => { r.records[1].fontResources[1].bytes = 0; }, r => { r.records[1].fontResources.pop(); },
        r => { r.records[1].fontResources[0].sha256 = otherHash; r.records[1].network[0].sha256 = otherHash; },
        r => { r.records[1].fontResources[1].sha256 = hash; r.records[1].network[1].sha256 = hash; },
        r => { r.records[1].network[1].bodyBytes = 455; }, r => { r.records[1].network[0].status = 404; },
        r => { r.records[1].usedFonts = []; }]) failed(mutate);
});

test('required font sets may differ between pages, but never between variants or runs of one page', () => {
    const report = fixture();
    for (const row of report.records.filter(row => row.name === 'present-perfect')) {
        row.fontResources[1].sha256 = 'c'.repeat(64); row.network[1].sha256 = 'c'.repeat(64);
    }
    assert.equal(verifyPairedComparison(report).pass, true);
    report.records[0].fontResources[1].sha256 = hash; report.records[0].network[1].sha256 = hash;
    const mismatch = verifyPairedComparison(report);
    assert.equal(mismatch.pass, false);
    assert.equal(mismatch.verifiedRecords, 40, 'parity failure must not selectively drop successful measurements');
});

test('natural network and controlled identical local-font experiments cannot be mixed', () => {
    const report = fixture(); report.fontMode = 'local-identical';
    for (const row of report.records) for (const [index, resource] of row.fontResources.entries()) {
        resource.url = report.variants[row.variant].base + `/font-fixture/${index}`; row.network[index].url = resource.url;
    }
    assert.equal(verifyPairedComparison(report).pass, true);
    report.records[0].fontResources[0].url = 'https://fonts.googleapis.com/css2';
    report.records[0].network[0].url = 'https://fonts.googleapis.com/css2';
    assert.equal(verifyPairedComparison(report).pass, false);
    failed(r => { r.fontMode = undefined; });
});

test('all valid runs remain visible when one invalid run prevents a complete pair; invalid evidence is retained', () => {
    const result = failed(r => {
        r.records[0].networkFailures.push({url: 'https://fonts.googleapis.com/css2', error: 'net::ERR_NETWORK_ACCESS_DENIED'});
        r.records[0].metric.status = 'incomplete'; r.records[0].metric.reasons = ['font-download-failed'];
    });
    assert.equal(result.attempts.length, 40); assert.equal(result.verifiedRecords, 39);
    assert.equal(result.pages[0].validRuns.length, 19); assert.equal(result.pages[0].paired.n, 9);
    assert.deepEqual(result.pages[0].incompletePairs, [{pairId: 'pair-1-sentence-types', validVariants: ['B']}]);
    assert.deepEqual(result.attempts[0].storedReasons, ['font-download-failed']);
    assert.ok(result.attempts[0].reasons.some(reason => reason.includes('ERR_NETWORK_ACCESS_DENIED')));
});

test('stored complete flags, undeclared failures and unsupported broad state abort exceptions fail closed', () => {
    for (const mutate of [r => { r.records[0].metric.status = 'incomplete'; },
        r => { r.records[0].metric.reasons = ['silent-problem']; }, r => { r.records[0].metric.lcp = 1; },
        r => { r.records[0].metric.clsSessionWindow = 0; }, r => { r.records[0].trace.complete = false; },
        r => { r.records[0].trace.visibility.push({state: 'hidden'}); },
        r => { r.records[0].network[0].diskCache = true; },
        r => { r.records[0].networkFailures.push({url: r.variants.A.base + r.records[0].url + '/state', error: 'net::ERR_ABORTED'});
            r.records[0].network.push({url: r.variants.A.base + r.records[0].url + '/state', status: 204, type: 'Fetch'}); },
        r => { r.records[0].network.push({url: 'https://gramlyze.com/app.js', status: 200}); }]) failed(mutate);
});

test('standard CLS and actual recent-input shifts stay separate without flag rewriting', () => {
    const result = verifyPairedComparison(fixture());
    assert.equal(result.attempts[0].clsSessionWindow, 0.25);
    assert.equal(result.attempts[0].legacyShiftSum, 0.25);
    assert.deepEqual(result.attempts[0].recentInputShifts, [{timestamp: 1300, value: 0.5}]);
});

test('paired bootstrap uses individual A denominators, fixed seed and whole pairs, not difference of group medians', () => {
    const pairs = [{A: 100, B: 200}, {A: 200, B: 100}, {A: 300, B: 400}];
    const result = bootstrapPairedStats(pairs);
    assert.equal(result.deltaMs.median, 100);
    assert.equal(result.B.median - result.A.median, 0);
    assert.ok(Math.abs(result.deltaPercent.median - 100 / 3) < 1e-12);
    assert.deepEqual(result, bootstrapPairedStats(pairs));
    assert.deepEqual(result.deltaMs.ci95, {low: -100, high: 100});
    assert.equal(result.bootstrap.seed, 32032); assert.equal(result.bootstrap.resamples, 10000);
    assert.match(result.limitations.join(' '), /not proof of equivalence/);
    assert.throws(() => bootstrapPairedStats([{A: 0, B: 1}]));
    assert.throws(() => bootstrapPairedStats([{A: 1, B: NaN}]));
    assert.equal(bootstrapPairedStats([]).deltaMs, null);
});

test('missing and malformed report structures return a failed verdict rather than an exception', () => {
    for (const report of [null, {}, {records: [null]}, {...fixture(), records: [{trace: {shifts: [null]}}]}]) {
        assert.equal(verifyPairedComparison(report).pass, false);
    }
});
