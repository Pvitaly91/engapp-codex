// M3.1 lab-only local performance probe. No screenshots/fault injection here.
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {execFileSync} = require('node:child_process');
const {summarizeShifts} = require('./cls-session-window.cjs');

const BASE = 'http://gramlyze.loc';
const WINDOW_MS = 12000;
const SETTLED_MS = 2000;
const RUNS = 5;
const PAGES = [
    ['sentence-types', '/theory/basic-grammar/sentence-types'],
    ['present-perfect', '/theory/tenses/present-perfect/present-perfect-forms'],
    ['questions', '/test/future-perfect/questions'],
];
const VIEWPORTS = [
    {name: 'desktop', width: 1440, height: 1000, mobile: false},
    {name: 'mobile', width: 390, height: 844, mobile: true},
];
const BLOCKED_PATTERNS = [
    '*://gramlyze.com/*', '*://*.gramlyze.com/*',
    '*://gramlyze.ub/*', '*://*.gramlyze.ub/*',
    '*://cdn.tailwindcss.com/*', '*://unpkg.com/*',
];
const safeUrl = value => {
    try { const url = new URL(value); return url.origin + url.pathname; }
    catch { return '[non-URL]'; }
};

// Registered before goto/reload; a fresh document owns a fresh trace/metric epoch.
// Capture all entries, including recent input. Never record text, HTML, cookies,
// request bodies, headers, query parameters, storage values or lesson answers.
function installObserver({windowMs, sidebarExpected}) {
    if (window !== top || location.origin !== 'http://gramlyze.loc') return;
    const trace = window.__m31 = {
        timeOrigin: performance.timeOrigin,
        requestedStart: 0, requestedEnd: windowMs,
        observerInstalledAt: performance.now(), observationEndedAt: null,
        shifts: [], lcpEntries: [], geometry: [], sidebarStates: [],
        timings: {domContentLoaded: null, load: null, fontsReady: null, alpineInit: [], alpineInitialized: null, sidebarReady: null},
        unsupported: [], visibility: [{timestamp: performance.now(), state: document.visibilityState}],
        complete: false,
    };
    let stopped = false;
    const observers = [];
    const rect = value => value ? Object.fromEntries(['x', 'y', 'width', 'height', 'top', 'right', 'bottom', 'left'].map(k => [k, value[k]])) : null;
    const node = value => value ? {
        tag: value.tagName || value.nodeName,
        classes: typeof value.className === 'string' ? value.className : value.className?.baseVal || '',
        hooks: [...(value.attributes || [])].map(a => a.name).filter(n => n.startsWith('data-theory-')),
    } : null;
    function observe(type, callback) {
        if (!PerformanceObserver.supportedEntryTypes.includes(type)) { trace.unsupported.push(type); return; }
        const observer = new PerformanceObserver(list => list.getEntries().forEach(callback));
        observer.observe({type, buffered: true});
        observers.push({observer, callback});
    }
    observe('layout-shift', entry => trace.shifts.push({
        timestamp: entry.startTime, value: entry.value, hadRecentInput: entry.hadRecentInput,
        sources: (entry.sources || []).map(source => ({node: node(source.node), previousRect: rect(source.previousRect), currentRect: rect(source.currentRect)})),
    }));
    observe('largest-contentful-paint', entry => trace.lcpEntries.push({timestamp: entry.startTime, size: entry.size, node: node(entry.element)}));
    const targets = '[data-theory-aside], [data-theory-main], [data-theory-toc-card], [data-theory-sidebar], [data-theory-desktop-navigation-loader], main';
    let previousGeometry = '', previousSidebar = '', readyScheduled = false;
    function inspect(reason) {
        if (stopped) return;
        const timestamp = performance.now();
        const boxes = [...document.querySelectorAll(targets)].map(element => ({
            node: node(element), rect: rect(element.getBoundingClientRect()),
            display: getComputedStyle(element).display,
            scrollHeight: element.scrollHeight, clientHeight: element.clientHeight,
        }));
        const serialized = JSON.stringify(boxes);
        if (serialized !== previousGeometry) {
            trace.geometry.push({timestamp, reason, boxes}); previousGeometry = serialized;
        }
        const loaders = [...document.querySelectorAll('[data-theory-desktop-navigation-loader]')];
        const states = loaders.map(element => ({
            busy: element.getAttribute('aria-busy'),
            hasTree: !!element.querySelector('[data-theory-sidebar-scroll]'),
            children: [...element.children].map(child => ({
                node: node(child), display: getComputedStyle(child).display, rect: rect(child.getBoundingClientRect()),
            })),
        }));
        const serializedStates = JSON.stringify(states);
        if (serializedStates !== previousSidebar) {
            trace.sidebarStates.push({timestamp, reason, states}); previousSidebar = serializedStates;
        }
        if (trace.timings.domContentLoaded !== null && trace.timings.sidebarReady === null && !readyScheduled) {
            const ready = sidebarExpected ? loaders.length > 0 && states.every(s => s.busy === 'false' && s.hasTree) : true;
            if (ready) {
                readyScheduled = true;
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    if (!stopped) trace.timings.sidebarReady = performance.now();
                }));
            }
        }
    }
    let inspectionPending = false;
    function scheduleInspection(reason) {
        if (inspectionPending || stopped) return;
        inspectionPending = true;
        requestAnimationFrame(() => { inspectionPending = false; inspect(reason); });
    }
    const mutations = new MutationObserver(() => scheduleInspection('DOM-state-change'));
    mutations.observe(document, {subtree: true, childList: true, attributes: true, attributeFilter: ['aria-busy', 'data-collapsed', 'data-settled', 'style']});
    const sizes = new ResizeObserver(() => scheduleInspection('resize'));
    document.addEventListener('DOMContentLoaded', () => {
        trace.timings.domContentLoaded = performance.now();
        document.querySelectorAll(targets).forEach(element => sizes.observe(element));
        inspect('DOMContentLoaded');
    }, {once: true});
    document.addEventListener('alpine:init', () => trace.timings.alpineInit.push(performance.now()));
    document.addEventListener('alpine:initialized', () => {
        trace.timings.alpineInitialized = performance.now(); scheduleInspection('alpine:initialized');
    });
    addEventListener('load', () => {
        trace.timings.load = performance.now();
        // Wait after load so stylesheet-discovered fonts are included, not the
        // initially resolved FontFaceSet before document parsing.
        document.fonts.ready.then(() => { if (!stopped) trace.timings.fontsReady = performance.now(); });
        inspect('load');
    }, {once: true});
    document.fonts.addEventListener('loadingdone', () => {
        if (!stopped && trace.timings.load !== null) trace.timings.fontsReady = performance.now();
    });
    document.addEventListener('visibilitychange', () => {
        if (!stopped) trace.visibility.push({timestamp: performance.now(), state: document.visibilityState});
    });
    setTimeout(() => {
        inspect('cutoff');
        for (const {observer, callback} of observers) {
            observer.takeRecords().forEach(callback); observer.disconnect();
        }
        stopped = true;
        mutations.disconnect(); sizes.disconnect();
        trace.observationEndedAt = performance.now();
        const navigation = performance.getEntriesByType('navigation')[0];
        trace.navigation = navigation ? {type: navigation.type, startTime: navigation.startTime, responseStart: navigation.responseStart, domContentLoadedEventEnd: navigation.domContentLoadedEventEnd, loadEventEnd: navigation.loadEventEnd} : null;
        trace.fcp = performance.getEntriesByName('first-contentful-paint')[0]?.startTime ?? null;
        trace.resources = performance.getEntriesByType('resource').map(entry => ({
            url: new URL(entry.name).origin + new URL(entry.name).pathname,
            initiatorType: entry.initiatorType, startTime: entry.startTime, responseEnd: entry.responseEnd,
            duration: entry.duration, transferSize: entry.transferSize, decodedBodySize: entry.decodedBodySize,
        }));
        trace.alpineVersion = window.Alpine?.version ?? null;
        trace.livewire = !!window.Livewire;
        trace.fontStatus = document.fonts.status;
        trace.iframeCount = document.querySelectorAll('iframe').length;
        trace.overflow = Math.max(0, document.documentElement.scrollWidth - innerWidth);
        trace.complete = true;
    }, Math.max(0, windowMs - performance.now()));
}

function assessMeasurement(trace, {status = 200, blocked = [], pageErrors = 0} = {}) {
    const reasons = [];
    if (!trace?.complete) reasons.push('observation-timeout');
    if (status !== 200) reasons.push(`document-http-${status}`);
    if (blocked.length) reasons.push('blocked-production-or-CDN-request');
    if (pageErrors) reasons.push('page-javascript-error');
    if (!trace) return {status: 'incomplete', reasons, clsSessionWindow: null, legacyShiftSum: null};
    const deadline = trace.requestedEnd - SETTLED_MS;
    for (const key of ['load', 'fontsReady', 'alpineInitialized', 'sidebarReady']) {
        if (trace.timings[key] === null || trace.timings[key] > deadline) reasons.push(`${key}-not-ready-with-${SETTLED_MS}ms-settling`);
    }
    if (trace.timings.alpineInit.length !== 1) reasons.push('expected-one-alpine-init');
    if (trace.unsupported.length) reasons.push('unsupported-performance-entry');
    if (trace.visibility.some(entry => entry.state !== 'visible')) reasons.push('backgrounded-document');
    if (trace.iframeCount) reasons.push('unmeasured-iframe-layout-shifts');
    if (trace.fontStatus !== 'loaded') reasons.push('fonts-still-loading');
    if (!trace.fcp) reasons.push('missing-first-contentful-paint');
    if (trace.observationEndedAt < trace.requestedEnd) reasons.push('observation-ended-before-cutoff');
    if (trace.observationEndedAt > trace.requestedEnd + 250) reasons.push('observation-cutoff-overrun');
    // Every raw entry remains in trace.shifts. The fixed epoch [0, 12000 ms]
    // is applied uniformly; timer-delivery overrun is never an extra metric window.
    const shifts = trace.shifts.filter(entry => entry.timestamp <= trace.requestedEnd);
    const lcp = trace.lcpEntries.filter(entry => entry.timestamp <= trace.requestedEnd).at(-1)?.timestamp ?? null;
    if (!lcp) reasons.push('missing-largest-contentful-paint');
    return {
        status: reasons.length ? 'incomplete' : 'complete', reasons,
        ...summarizeShifts(shifts), lcp,
        rawEntryCount: trace.shifts.length, metricEntryCount: shifts.length,
        afterCutoffEntryCount: trace.shifts.length - shifts.length,
    };
}

function distribution(values) {
    if (!values.length) return null;
    const sorted = [...values].sort((a, b) => a - b), middle = Math.floor(sorted.length / 2);
    return {median: sorted.length % 2 ? sorted[middle] : (sorted[middle - 1] + sorted[middle]) / 2, min: sorted[0], max: sorted.at(-1)};
}

function summarizeRecords(records) {
    const groups = [];
    for (const [name, url] of PAGES) for (const viewport of VIEWPORTS) for (const cache of ['cold', 'repeat']) {
        const selected = records.filter(row => row.name === name && row.viewport === viewport.name && row.cache === cache);
        const completed = selected.filter(row => row.metric.status === 'complete');
        const worst = completed.reduce((a, b) => !a || b.metric.clsSessionWindow > a.metric.clsSessionWindow ? b : a, null);
        groups.push({name, url, viewport: viewport.name, cache, attempted: selected.length, complete: completed.length,
            acceptanceComplete: selected.length === RUNS && completed.length === RUNS,
            lcp: distribution(completed.map(row => row.metric.lcp)),
            clsSessionWindow: distribution(completed.map(row => row.metric.clsSessionWindow)),
            legacyShiftSum: distribution(completed.map(row => row.metric.legacyShiftSum)),
            worstClsRun: worst?.run ?? null,
            incompleteRuns: selected.filter(row => row.metric.status !== 'complete').map(row => ({run: row.run, reasons: row.metric.reasons})),
        });
    }
    return groups;
}

async function main() {
    const label = process.argv[2];
    assert.match(label || '', /^[a-z0-9][a-z0-9_-]*$/i, 'Supply a unique evidence label');
    const evidenceRoot = path.resolve('storage/app/seo-m3-1-local');
    const output = path.join(evidenceRoot, `${label}-perf.json`);
    fs.mkdirSync(evidenceRoot, {recursive: true});
    assert.ok(!fs.existsSync(output), 'Evidence already exists; supply a new label');
    const playwrightModule = process.env.PLAYWRIGHT_MODULE || 'playwright';
    const {chromium} = require(playwrightModule);
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    const packageVersion = name => { try { return require(path.resolve('node_modules', name, 'package.json')).version; } catch { return null; } };
    let playwrightVersion = null;
    try { playwrightVersion = require(path.join(path.dirname(require.resolve(playwrightModule)), 'package.json')).version; } catch {}
    const report = {
        schema: 'gramlyze-local-cls-m31-v1', label, startedAt: new Date().toISOString(),
        head: execFileSync('git', ['rev-parse', 'HEAD'], {encoding: 'utf8'}).trim(),
        browser: browser.version(), playwright: playwrightVersion, node: process.version,
        dependencies: {vite: packageVersion('vite'), tailwindPublic: packageVersion('tailwindcss-public'), tailwindLegacy: packageVersion('tailwindcss'), vitest: packageVersion('vitest')},
        packageLockSha256: crypto.createHash('sha256').update(fs.readFileSync('package-lock.json')).digest('hex'),
        sourceSha256: Object.fromEntries([
            'public/build/manifest.json', 'resources/css/catalog-public.css',
            'resources/views/theory/partials/desktop-navigation-loader.blade.php',
            'resources/views/theory/partials/mobile-navigation.blade.php',
            'resources/views/theory/show.blade.php', 'resources/views/theory/partials/tree-nav.blade.php',
            'tools/diagnostics/public-layout-shifts.cjs', 'tools/diagnostics/cls-session-window.cjs',
        ].map(file => [file, crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex')])),
        conditions: {base: BASE, windowMs: WINDOW_MS, settledMs: SETTLED_MS, runsPerCacheAndViewport: RUNS,
            cache: 'cold=new isolated browser context; repeat=reload same context; HTTP cache enabled',
            network: 'no CPU/network throttling; origin-scoped CDP Fetch guard; Google Fonts retained',
            viewports: VIEWPORTS, locale: 'uk-UA', colorScheme: 'light', background: 'new-guest application default',
            decorations: 'unaltered: no seeded randomness, animation disabling or screenshot stabilization',
            interactions: 'none; closed mobile menu; desktop AJAX still observed when present',
            blockedPatterns: BLOCKED_PATTERNS,
            clsReferenceVersion: 'web-vitals 6.2.1',
            clsReferences: ['https://github.com/GoogleChrome/web-vitals/blob/v6.2.1/src/lib/LayoutShiftManager.ts', 'https://github.com/GoogleChrome/web-vitals/blob/v6.2.1/src/onCLS.ts', 'https://web.dev/articles/cls'],
            limitation: 'fixed-window main-frame lab metrics, not full-page-lifetime field Core Web Vitals; historical M3 shift sums cannot be recalculated without raw entries',
        }, records: [], summary: [],
    };
    const save = () => { report.summary = summarizeRecords(report.records); fs.writeFileSync(output, JSON.stringify(report, null, 2)); };
    try {
        // No parallel page loads: cold/repeat pairs share only their own context.
        for (const [name, url] of PAGES) for (const viewport of VIEWPORTS) for (let run = 1; run <= RUNS; run++) {
            const context = await browser.newContext({viewport: {width: viewport.width, height: viewport.height}, isMobile: viewport.mobile, hasTouch: viewport.mobile, locale: 'uk-UA', colorScheme: 'light', serviceWorkers: 'block'});
            const page = await context.newPage();
            const cdp = await context.newCDPSession(page);
            let active;
            await cdp.send('Network.enable');
            // Restrict interception to denied origins. Never page.route('**/*').
            cdp.on('Fetch.requestPaused', async event => {
                active?.blocked.push(safeUrl(event.request.url));
                try { await cdp.send('Fetch.failRequest', {requestId: event.requestId, errorReason: 'BlockedByClient'}); }
                catch { if (active) active.guardErrors++; }
            });
            await cdp.send('Fetch.enable', {patterns: BLOCKED_PATTERNS.map(urlPattern => ({urlPattern, requestStage: 'Request'}))});
            const requests = new Map();
            cdp.on('Network.responseReceived', event => {
                if (!active) return;
                const response = event.response;
                const row = {url: safeUrl(response.url), type: event.type, status: response.status, diskCache: !!response.fromDiskCache, serviceWorker: !!response.fromServiceWorker, mime: response.mimeType};
                requests.set(event.requestId, row); active.network.push(row);
            });
            cdp.on('Network.loadingFinished', event => {
                const row = requests.get(event.requestId); if (row) row.wireBytes = event.encodedDataLength;
            });
            page.on('pageerror', () => { if (active) active.pageErrors++; });
            page.on('console', message => { if (active && ['warning', 'error'].includes(message.type())) active.consoleCounts[message.type()]++; });
            page.on('requestfailed', request => active?.networkFailures.push({url: safeUrl(request.url()),error: request.failure()?.errorText}));
            await context.addInitScript(installObserver, {windowMs: WINDOW_MS, sidebarExpected: name !== 'questions'});
            let coldTimeOrigin = null;
            try {
                for (const cache of ['cold', 'repeat']) {
                    requests.clear();
                    active = {name, url, viewport: viewport.name, run, cache, startedAt: new Date().toISOString(), blocked: [], guardErrors: 0, network: [], networkFailures: [], pageErrors: 0, consoleCounts: {warning: 0, error: 0}};
                    try {
                        const response = cache === 'cold'
                            ? await page.goto(BASE + url, {waitUntil: 'commit', timeout: 30000})
                            : await page.reload({waitUntil: 'commit', timeout: 30000});
                        active.httpStatus = response?.status() ?? null;
                        assert.equal(new URL(page.url()).origin, BASE, 'Unexpected navigation origin');
                        await page.waitForFunction(() => window.__m31?.complete === true, null, {timeout: WINDOW_MS + 10000});
                        active.trace = await page.evaluate(() => window.__m31);
                        if (cache === 'cold') coldTimeOrigin = active.trace.timeOrigin;
                        else assert.ok(coldTimeOrigin !== null && active.trace.timeOrigin > coldTimeOrigin, 'Repeat must use a fresh navigation epoch');
                    } catch (error) {
                        // Never turn a load/observer timeout into a successful zero CLS.
                        active.error = error.name || 'measurement-error';
                        try { active.trace = await page.evaluate(() => window.__m31 || null); } catch { active.trace = null; }
                    }
                    active.metric = assessMeasurement(active.trace, {status: active.httpStatus, blocked: active.blocked, pageErrors: active.pageErrors});
                    if (active.error || active.guardErrors) {
                        active.metric.status = 'incomplete'; active.metric.reasons.push(active.error ? 'browser-operation-failed' : 'network-guard-error');
                    }
                    report.records.push(active); save();
                    console.log(JSON.stringify({name, viewport: viewport.name, run, cache, ...active.metric}));
                }
            } finally { active = null; await context.close(); }
        }
    } finally {
        await browser.close(); report.finishedAt = new Date().toISOString(); save();
    }
    if (report.summary.some(group => !group.acceptanceComplete)) process.exitCode = 1;
    console.log(`Evidence: ${output}`);
}

module.exports = {installObserver, assessMeasurement, summarizeRecords, distribution, WINDOW_MS, SETTLED_MS, BLOCKED_PATTERNS};
if (require.main === module) main().catch(error => { console.error(error.message); process.exitCode = 1; });
