// Read-only, post-run parity gate. Never launches a browser or changes raw evidence.
'use strict';
const fs = require('node:fs');
const {isDeepStrictEqual} = require('node:util');

const BASE = 'http://gramlyze.loc';
const PAGES = [
    ['sentence-types', '/theory/basic-grammar/sentence-types'],
    ['present-perfect', '/theory/tenses/present-perfect/present-perfect-forms'],
    ['questions', '/test/future-perfect/questions'],
];
const VIEWPORTS = ['desktop', 'mobile'];
const CACHES = ['cold', 'repeat'];
const PROBE_FILES = ['tools/diagnostics/public-layout-shifts.cjs', 'tools/diagnostics/cls-session-window.cjs'];
const close = (a, b) => Number.isFinite(a) && Number.isFinite(b) && Math.abs(a - b) <= 1e-12;
const groupKey = row => `${row.name}/${row.viewport}/${row.cache}`;
const rowKey = row => `${groupKey(row)}/${row.run}`;
const host = value => { try { return new URL(value).hostname; } catch { return null; } };

// Separate implementation: do not import the running probe or its reducer.
function recompute(entries, cutoff) {
    if (!Array.isArray(entries) || !Number.isFinite(cutoff)) throw new TypeError('Missing raw shifts/cutoff');
    let last = -Infinity, first = 0, previous = 0, current = 0, cls = 0, sum = 0, count = 0;
    for (const entry of entries) {
        const t = entry.timestamp;
        if (!Number.isFinite(t) || t < 0 || t < last || !Number.isFinite(entry.value)
            || entry.value < 0 || typeof entry.hadRecentInput !== 'boolean') {
            throw new TypeError('Invalid or out-of-order raw shift');
        }
        last = t;
        if (t > cutoff) continue;
        count++;
        if (entry.hadRecentInput) continue;
        sum += entry.value;
        if (current > 0 && t - previous < 1000 && t - first < 5000) current += entry.value;
        else { first = t; current = entry.value; }
        previous = t;
        cls = Math.max(cls, current);
    }
    return {clsSessionWindow: cls, legacyShiftSum: sum, rawEntryCount: entries.length,
        metricEntryCount: count, afterCutoffEntryCount: entries.length - count};
}

function distribution(values) {
    if (!values.length) return null;
    const sorted = [...values].sort((a, b) => a - b), middle = Math.floor(sorted.length / 2);
    return {median: sorted.length % 2 ? sorted[middle] : (sorted[middle - 1] + sorted[middle]) / 2,
        min: sorted[0], max: sorted.at(-1)};
}

function verifyReport(report, side, errors) {
    const check = (ok, message) => { if (!ok) errors.push(`${side}: ${message}`); };
    const records = Array.isArray(report?.records) ? report.records : [];
    check(report?.schema === 'gramlyze-local-cls-m31-v1', 'unsupported/missing schema');
    check(typeof report?.label === 'string' && report.label.length > 0, 'missing label');
    check(Number.isFinite(Date.parse(report?.startedAt)) && Number.isFinite(Date.parse(report?.finishedAt))
        && Date.parse(report.finishedAt) >= Date.parse(report.startedAt), 'missing/invalid completed run timestamps');
    check(records.length === 60, `expected 60 records, got ${records.length}`);
    check(report?.conditions?.base === BASE && report.conditions.windowMs === 12000
        && report.conditions.settledMs === 2000 && report.conditions.runsPerCacheAndViewport === 5,
    'unexpected measurement contract');
    check(isDeepStrictEqual(report?.conditions?.viewports, [
        {name: 'desktop', width: 1440, height: 1000, mobile: false},
        {name: 'mobile', width: 390, height: 844, mobile: true},
    ]), 'unexpected viewport contract');
    let expectedState204Aborts = 0, googleStylesheets = 0, googleFonts = 0;
    const seen = new Set(), recomputed = [];
    for (const row of records) {
        const id = rowKey(row), before = errors.length;
        const rowCheck = (ok, message) => check(ok, `${id}: ${message}`);
        rowCheck(!seen.has(id), 'duplicate navigation'); seen.add(id);
        rowCheck(PAGES.some(([name, url]) => row.name === name && row.url === url)
            && VIEWPORTS.includes(row.viewport) && CACHES.includes(row.cache)
            && Number.isInteger(row.run) && row.run >= 1 && row.run <= 5, 'unexpected navigation identity');
        rowCheck(row.metric?.status === 'complete' && Array.isArray(row.metric?.reasons)
            && row.metric.reasons.length === 0 && !row.error, 'measurement not complete');
        rowCheck(row.httpStatus === 200 && row.pageErrors === 0 && row.guardErrors === 0
            && Array.isArray(row.blocked) && row.blocked.length === 0, 'HTTP/page/guard error or missing evidence');
        const trace = row.trace;
        rowCheck(trace?.complete === true && trace.requestedStart === 0 && trace.requestedEnd === 12000
            && trace.observationEndedAt >= 12000 && trace.observationEndedAt <= 12250, 'invalid observation window');
        for (const key of ['load', 'fontsReady', 'alpineInitialized', 'sidebarReady']) {
            rowCheck(Number.isFinite(trace?.timings?.[key]) && trace.timings[key] >= 0
                && trace.timings[key] <= 10000, `${key} did not settle before cutoff`);
        }
        rowCheck(trace?.timings?.alpineInit?.length === 1 && trace?.fontStatus === 'loaded'
            && Array.isArray(trace?.unsupported) && trace.unsupported.length === 0
            && Array.isArray(trace?.visibility) && trace.visibility.length > 0
            && trace.visibility.every(event => event.state === 'visible') && trace?.iframeCount === 0,
        'invalid readiness/visibility/API evidence');
        rowCheck(Number.isFinite(trace?.fcp) && trace.fcp > 0, 'missing FCP');
        const lcp = trace?.lcpEntries?.filter(entry => entry.timestamp <= 12000).at(-1)?.timestamp;
        rowCheck(Number.isFinite(lcp) && lcp > 0 && close(lcp, row.metric?.lcp), 'LCP does not match raw entries');
        try {
            const metric = recompute(trace?.shifts, trace?.requestedEnd);
            for (const key of Object.keys(metric)) rowCheck(close(metric[key], row.metric?.[key]), `${key} does not match raw entries`);
            recomputed.push({...row, metric: {...metric, lcp}});
        } catch (error) { rowCheck(false, error.message); }

        const responses = Array.isArray(row.network) ? row.network : [];
        rowCheck(Array.isArray(row.network) && Array.isArray(row.networkFailures), 'missing network evidence');
        const stateUrl = row.name === 'questions' ? `${BASE}${row.url}/state` : null;
        let available204s = responses.filter(response => response.url === stateUrl && response.status === 204 && response.type === 'Fetch').length;
        for (const failure of row.networkFailures || []) {
            if (stateUrl && failure.url === stateUrl && failure.error === 'net::ERR_ABORTED' && available204s > 0) {
                available204s--; expectedState204Aborts++;
            } else rowCheck(false, `unexpected resource failure: ${failure.url} (${failure.error})`);
        }
        for (const response of responses) {
            rowCheck(host(response.url) !== null && Number.isInteger(response.status)
                && response.status >= 200 && response.status < 400,
            `unsuccessful resource response: ${response.url} (${response.status})`);
            rowCheck(!/(^|\.)gramlyze\.(com|ub)$/i.test(host(response.url) || ''), 'forbidden-origin response');
        }
        const styles = responses.filter(response => host(response.url) === 'fonts.googleapis.com');
        const fonts = responses.filter(response => host(response.url) === 'fonts.gstatic.com');
        // FontFaceSet.loaded also occurs after font/style failures; require real successful responses.
        rowCheck(styles.length > 0 && styles.every(response => response.status === 200
            && response.type === 'Stylesheet' && response.mime === 'text/css'), 'missing/failed Google font stylesheet');
        rowCheck(fonts.length > 0 && fonts.every(response => response.status === 200
            && response.type === 'Font' && /^(font\/|application\/.*font)/i.test(response.mime || '')),
        'missing/failed Google font binary');
        googleStylesheets += styles.length; googleFonts += fonts.length;
        // Count only truly verified rows, not the stored complete flag.
        if (errors.length === before) seen.add(`valid:${id}`);
    }
    const summaries = Array.isArray(report?.summary) ? report.summary : [];
    check(summaries.length === 12, 'expected 12 summary groups');
    for (const [name, url] of PAGES) for (const viewport of VIEWPORTS) for (const cache of CACHES) {
        const key = groupKey({name, viewport, cache});
        const rows = records.filter(row => groupKey(row) === key);
        const summary = summaries.filter(row => groupKey(row) === key);
        check(rows.length === 5 && new Set(rows.map(row => row.run)).size === 5, `${key}: expected five distinct runs`);
        check(summary.length === 1 && summary[0].url === url && summary[0].attempted === 5
            && summary[0].complete === 5 && summary[0].acceptanceComplete === true
            && Array.isArray(summary[0].incompleteRuns) && summary[0].incompleteRuns.length === 0,
        `${key}: incomplete/invalid stored summary`);
        const independent = recomputed.filter(row => groupKey(row) === key);
        if (summary.length === 1 && independent.length === 5) {
            for (const metric of ['clsSessionWindow', 'legacyShiftSum', 'lcp']) {
                const values = distribution(independent.map(row => row.metric[metric]));
                for (const stat of ['median', 'min', 'max']) check(close(values[stat], summary[0][metric]?.[stat]), `${key}: stored ${metric}.${stat} mismatch`);
            }
        }
    }
    return {label: report?.label ?? null, records: records.length,
        verifiedRecords: [...seen].filter(key => key.startsWith('valid:')).length,
        expectedState204Aborts, googleStylesheets, googleFonts};
}

function verifyComparison(baseline, after) {
    const errors = [];
    const result = {schema: 'gramlyze-layout-comparison-v1', baseline: verifyReport(baseline, 'baseline', errors),
        after: verifyReport(after, 'after', errors)};
    for (const field of ['browser', 'playwright', 'node', 'dependencies', 'packageLockSha256', 'conditions']) {
        if (baseline?.[field] === undefined || after?.[field] === undefined
            || !isDeepStrictEqual(baseline[field], after[field])) errors.push(`parity: ${field} differs or is missing`);
    }
    for (const field of ['browser', 'playwright', 'node']) {
        if (typeof baseline?.[field] !== 'string' || !baseline[field]) errors.push(`parity: invalid ${field}`);
    }
    for (const field of ['vite', 'tailwindPublic', 'tailwindLegacy', 'vitest']) {
        if (typeof baseline?.dependencies?.[field] !== 'string' || !baseline.dependencies[field]) errors.push(`parity: invalid dependency ${field}`);
    }
    if (!/^[a-f0-9]{64}$/i.test(baseline?.packageLockSha256 || '')) errors.push('parity: invalid package-lock hash');
    for (const file of PROBE_FILES) {
        const hash = baseline?.sourceSha256?.[file];
        if (!/^[a-f0-9]{64}$/i.test(hash || '') || hash !== after?.sourceSha256?.[file]) errors.push(`parity: probe hash differs or is missing: ${file}`);
    }
    result.pass = errors.length === 0;
    result.errors = errors;
    result.limitations = [
        'Parity/measurement integrity only: no automatic claim of LCP/CLS improvement or full-lifetime field CWV.',
        'Expected Questions state204/ERR_ABORTED is counted only with a matching local Fetch204 response; other failures are rejected.',
        'Application/manifest hashes may intentionally differ. The original probe omits catalog-public head-layout from source hashes; document supplementary provenance separately.',
    ];
    return result;
}

module.exports = {verifyComparison, recompute, distribution};
if (require.main === module) {
    try {
        if (process.argv.length !== 4) throw new Error('Usage: node verify-layout-comparison.cjs BASELINE-perf.json AFTER-perf.json');
        const result = verifyComparison(...process.argv.slice(2).map(file => JSON.parse(fs.readFileSync(file, 'utf8'))));
        console.log(JSON.stringify(result, null, 2));
        if (!result.pass) process.exitCode = 1;
    } catch (error) { console.error(error.message); process.exitCode = 1; }
}
