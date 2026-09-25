// Local-only M3.1/M3.2 fault/geometry acceptance. These are NOT the LCP median runs.
// Usage: node tools/diagnostics/theory-sidebar-stability.cjs LABEL reproduce|accept
// Optional: --page=sentence-types|present-perfect --viewport=desktop|mobile
//           --scenario=new-guest|saved-expanded|saved-collapsed|delay|error
//           --scenario=saved-collapsed-delayed-alpine (separate causal fault case)
//           --scenario=delayed-alpine --runs=10 --stage=m3-2
//           --observe-ms=8000 --desktop-retry-required
'use strict';

const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {summarizeShifts} = require('./cls-session-window.cjs');

const TARGETS = [
    ['sentence-types', '/theory/basic-grammar/sentence-types'],
    ['present-perfect', '/theory/tenses/present-perfect/present-perfect-forms'],
];
const SCENARIOS = ['new-guest', 'saved-expanded', 'saved-collapsed', 'delay', 'error'];
const ALL_SCENARIOS = [...SCENARIOS, 'saved-collapsed-delayed-alpine', 'delayed-alpine'];
const BASE = 'http://gramlyze.loc';
const sha256 = value => crypto.createHash('sha256').update(value).digest('hex');
const safeUrl = value => { const url = new URL(value); return url.origin + url.pathname; };

// Serialized into the browser before navigation. No application code is changed.
function installProbe({mobile, observeMs}) {
    const report = {timeOrigin: performance.timeOrigin, started: performance.now(), ended: null, marks: [], frames: [], shifts: [], alpineInits: 0};
    let stopped = false;
    let lastGeometry = '';
    let animationFrame;
    const mark = (name) => { if (!stopped) report.marks.push({name, timestamp: performance.now()}); };
    const box = (element) => {
        if (!element) return null;
        const rect = element.getBoundingClientRect();
        const css = getComputedStyle(element);
        return {
            x: rect.x, y: rect.y, width: rect.width, height: rect.height,
            // Keep the transformed visual rect above, but measure layout allocation
            // separately: mobile x-transition intentionally scales the open panel.
            layoutWidth: element.offsetWidth, layoutHeight: element.offsetHeight,
            display: css.display, position: css.position, visibility: css.visibility,
            opacity: css.opacity, transform: css.transform,
            scrollTop: element.scrollTop, scrollHeight: element.scrollHeight, clientHeight: element.clientHeight,
        };
    };
    const visible = (element) => !!element && element.getClientRects().length > 0
        && getComputedStyle(element).visibility !== 'hidden';
    const block = (element) => element ? {
        // Narrow state attributes only: never x-data, x-html, text or form values.
        xCloak: element.hasAttribute('x-cloak'), xShow: element.getAttribute('x-show'),
        xRef: element.getAttribute('x-ref'), role: element.getAttribute('role'),
        inlineDisplay: element.style.display, ariaBusy: element.getAttribute('aria-busy'),
        hasLayoutBox: element.getClientRects().length > 0, visible: visible(element),
        box: box(element),
    } : null;
    const geometry = () => {
        const desktop = document.querySelector('[data-theory-desktop-navigation-loader]');
        const toggle = document.querySelector('[data-theory-mobile-nav-toggle]');
        const mobileRoot = toggle?.parentElement;
        const panel = document.querySelector('[data-theory-mobile-nav-panel]');
        const loader = mobile ? mobileRoot : desktop;
        const region = mobile ? panel : desktop;
        const content = region?.querySelector('[x-ref="content"]');
        const state = loader?._x_dataStack?.[0];
        const layout = document.querySelector('[data-theory-layout]');
        const loadingElement = region?.querySelector('[x-show="loading"]');
        const errorElement = region?.querySelector('[x-show="error"]');
        const loading = visible(loadingElement);
        const error = visible(errorElement);
        // Also supports historical mobile display:contents with no wrapper box.
        const loaded = visible(content?.querySelector('a'));
        return {
            scrollY, collapsed: layout?.getAttribute('data-collapsed'),
            settled: layout?.getAttribute('data-settled'),
            state: state ? {loading: !!state.loading, error: !!state.error, loaded: !!state.loaded, open: !!state.open} : null,
            visibleStates: {loading, error, loaded, count: Number(loading) + Number(error) + Number(loaded)},
            blocks: {loading: block(loadingElement), error: block(errorElement), content: block(content)},
            aside: box(document.querySelector('[data-theory-aside]')),
            sidebar: box(mobile ? panel : desktop?.closest('[data-theory-sidebar]')),
            loader: box(loader), content: box(content),
            scroll: box(region?.querySelector('[data-theory-sidebar-scroll]')),
            toc: box(document.querySelector('[data-theory-toc-pin-root]')),
            tocCard: box(document.querySelector('[data-theory-toc-card]')),
            main: box(document.querySelector('[data-theory-main]')),
            mobilePanel: box(panel), mobileWrapper: box(mobileRoot),
            followingMenu: box(mobileRoot?.nextElementSibling),
        };
    };
    const snapshot = (label, capturePhase = 'event') => {
        const frame = {label, capturePhase, timestamp: performance.now(), ...geometry()};
        if (!stopped) report.frames.push(frame);
        return frame;
    };
    const tick = () => {
        if (stopped) return;
        if (!report.firstPaintedFrame && performance.getEntriesByName('first-contentful-paint').length) {
            report.firstPaintedFrame = snapshot('first-painted-frame', 'animation-frame');
        }
        const current = geometry();
        const key = JSON.stringify(current);
        if (key !== lastGeometry) {
            report.frames.push({label: 'geometry-change', capturePhase: 'animation-frame', timestamp: performance.now(), ...current});
            lastGeometry = key;
        }
        animationFrame = requestAnimationFrame(tick);
    };
    const identify = (node) => {
        if (!node) return null;
        const element = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;
        return element ? {
            tag: element.tagName,
            id: element.id || null,
            classes: typeof element.className === 'string' ? element.className : '',
            hooks: element.getAttributeNames().filter(name => name.startsWith('data-theory-')),
        } : null;
    };
    const consume = (entries) => {
        for (const entry of entries) report.shifts.push({
            timestamp: entry.startTime, value: entry.value, hadRecentInput: entry.hadRecentInput,
            sources: entry.sources.map(source => ({
                node: identify(source.node), previous: source.previousRect.toJSON(), current: source.currentRect.toJSON(),
            })),
        });
    };
    const observer = new PerformanceObserver(list => consume(list.getEntries()));
    observer.observe({type: 'layout-shift', buffered: true});
    const mutationObserver = new MutationObserver(records => {
        if (records.some(record => record.target.nodeType === Node.ELEMENT_NODE
            && record.target.closest('[data-theory-sidebar],[data-theory-layout]'))) mark('sidebar-dom-mutation');
        if (records.some(record => record.type === 'attributes' && record.target.matches(
            '[data-theory-desktop-navigation-loader], [data-theory-mobile-nav-panel], [x-show="loading"], [x-show="error"], [x-ref="content"]'
        ))) snapshot('state-attribute-mutation', 'mutation');
    });
    mutationObserver.observe(document, {subtree: true, childList: true, attributes: true,
        attributeFilter: ['style', 'data-collapsed', 'data-settled', 'aria-busy', 'x-cloak']});
    const stop = (reason = 'explicit') => {
        if (!stopped) {
            snapshot('observation-end');
            consume(observer.takeRecords());
            observer.disconnect();
            mutationObserver.disconnect();
            cancelAnimationFrame(animationFrame);
            report.ended = performance.now();
            report.stopReason = reason;
            report.paints = performance.getEntriesByType('paint').map(entry => ({name: entry.name, timestamp: entry.startTime}));
            report.endState = geometry();
            stopped = true;
        }
        return report;
    };
    document.addEventListener('alpine:init', () => { report.alpineInits++; mark('alpine:init'); snapshot('alpine-init'); });
    document.addEventListener('alpine:initialized', () => { mark('alpine:initialized'); snapshot('alpine-initialized'); });
    document.addEventListener('DOMContentLoaded', () => {
        mark('dom-content-loaded');
        snapshot('dom-content-loaded');
        document.fonts.ready.then(() => { mark('fonts-ready'); snapshot('fonts-ready'); });
    });
    addEventListener('load', () => mark('load'));
    window.__sidebarProbe = {report, snapshot, mark, stop};
    tick();
    setTimeout(() => stop('fixed-window'), observeMs);
}

function rectDifference(first, last, fields) {
    if (!first || !last) return null;
    return Object.fromEntries(fields.map(field => [field, last[field] - first[field]]));
}

function capturePhase(frame) {
    if (frame.capturePhase) return ['animation-frame', 'mutation', 'event'].includes(frame.capturePhase) ? frame.capturePhase : 'unknown';
    // Backward-compatible classification of immutable M3.1/M3.2 traces.
    if (['geometry-change', 'first-painted-frame'].includes(frame.label)) return 'animation-frame';
    if (frame.label === 'state-attribute-mutation') return 'mutation';
    if (['alpine-init', 'alpine-initialized', 'dom-content-loaded', 'fonts-ready', 'ajax-ready',
        'mobile-closed', 'mobile-open', 'mobile-closed-after', 'observation-end'].includes(frame.label)) return 'event';
    return 'unknown';
}

function assessStateVisibility(trace) {
    const firstPaint = trace.paints?.find(paint => paint.name === 'first-contentful-paint')?.timestamp ?? 0;
    const overlaps = trace.frames.filter(frame => frame.timestamp >= firstPaint && frame.visibleStates.count > 1);
    const contentBoxes = overlaps.flatMap(frame => [frame.content, frame.blocks?.content?.box])
        .filter(box => box && box.width > 0 && box.height > 0);
    // A mutation callback can force layout between Alpine's show and hide rAF
    // callbacks, before that update is painted. After-FCP does not make every
    // DOM snapshot a rendered frame. Retain every such snapshot and its count.
    // Conversely, actual LayoutShift evidence matching that content box must
    // still fail, even if an animation-frame sample happened to miss the state.
    const matchesContent = rect => !!rect && contentBoxes.some(box => ['x', 'y', 'width']
        .every(key => Number.isFinite(rect[key]) && Math.abs(rect[key] - box[key]) <= 1));
    const corroboratingContentShifts = (trace.shifts || []).filter(shift => shift.timestamp >= firstPaint
        && shift.sources.some(source => {
            const previous = source.previous || source.previousRect, current = source.current || source.currentRect;
            return previous && current && (matchesContent(previous) || matchesContent(current))
                && (Math.abs(current.x - previous.x) > 1 || Math.abs(current.y - previous.y) > 1);
        })).map(shift => ({timestamp: shift.timestamp, value: shift.value, hadRecentInput: shift.hadRecentInput}));
    return {
        rawPostFcpOverlapSnapshots: overlaps.length,
        mutationOverlapSnapshots: overlaps.filter(frame => capturePhase(frame) === 'mutation').length,
        eventOverlapSnapshots: overlaps.filter(frame => capturePhase(frame) === 'event').length,
        unknownOverlapSnapshots: overlaps.filter(frame => capturePhase(frame) === 'unknown').length,
        overlappingStateFrames: overlaps.filter(frame => capturePhase(frame) === 'animation-frame').length,
        corroboratingContentShifts,
    };
}

function exclusiveVisibleStates(assessment) {
    return assessment.overlappingStateFrames === 0 && assessment.unknownOverlapSnapshots === 0
        && assessment.corroboratingContentShifts.length === 0;
}

function assessGeometry(trace, mobile) {
    const firstPaint = trace.paints?.find(paint => paint.name === 'first-contentful-paint')?.timestamp ?? 0;
    const earliest = trace.frames.find(frame => frame.sidebar?.height > 0);
    const painted = trace.frames.filter(frame => frame.timestamp >= firstPaint && frame.sidebar?.height > 0
        && (!mobile || frame.state?.open));
    const initialLoading = painted.find(frame => frame.visibleStates.loading);
    const initial = initialLoading || painted[0];
    const final = trace.endState;
    // x-cloak means the TOC has no initial layout box. Its first appearance below
    // the fold is not a translation from (0,0); compare its first actual box.
    // Keep every raw frame/shift, and retain the old visible-TOC -> +717px failure.
    const tocInitial = trace.frames.find(frame => frame.timestamp >= firstPaint && frame.toc?.width > 0 && frame.toc?.height > 0)
        || initial;
    const result = {
        earliest, firstPaint, initialLoading, initial, tocInitial, final,
        sidebarDelta: rectDifference(initial?.sidebar, final?.sidebar, ['x', 'y', 'width', 'height']),
        sidebarFootprintDelta: rectDifference(initial?.sidebar, final?.sidebar, ['layoutWidth', 'layoutHeight']),
        tocDelta: rectDifference(tocInitial?.toc, final?.toc, ['x', 'y']),
        mainDelta: rectDifference(initial?.main, final?.main, ['x', 'y', 'width']),
        followingMenuDelta: rectDifference(initial?.followingMenu, final?.followingMenu, ['x', 'y']),
        ...assessStateVisibility(trace),
    };
    // Compare document positions because clicks can scroll the mobile toggle into view.
    for (const key of ['sidebarDelta', 'mainDelta', 'followingMenuDelta']) {
        if (result[key]) result[key].y += final.scrollY - initial.scrollY;
    }
    if (result.tocDelta) result.tocDelta.y += final.scrollY - tocInitial.scrollY;
    return result;
}

function sourceContract(html) {
    const blocks = [];
    const expressions = ['loading', 'error', '!loading && !error', 'loaded && !loading && !error'];
    // Lex only opening tags and retain a strict attribute allowlist. Script/body
    // content, CSRF, x-data and any private values are never retained.
    for (const match of html.matchAll(/<([a-z][\w:-]*)\b(?:[^"'<>]|"[^"]*"|'[^']*')*>/gi)) {
        const attributes = {};
        const text = match[0].slice(match[1].length + 1, -1);
        for (const attribute of text.matchAll(/([^\s=<>/"']+)(?:\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s"'=<>`]+)))?/g)) {
            const key = attribute[1].toLowerCase();
            if (!Object.hasOwn(attributes, key)) attributes[key] = attribute[2] ?? attribute[3] ?? attribute[4] ?? '';
        }
        const expression = attributes['x-show']?.replaceAll('&amp;', '&');
        const kind = attributes['x-ref'] === 'content' ? 'content'
            : ['loading', 'error'].includes(expression) ? expression : null;
        if (!kind) continue;
        blocks.push({tag: match[1].toLowerCase(), kind,
            xCloak: Object.hasOwn(attributes, 'x-cloak'),
            xShow: expression === undefined ? null : expressions.includes(expression) ? expression : '[unrecognized-expression]',
            xShowSha256: expression === undefined ? null : sha256(expression),
            xRef: attributes['x-ref'] === 'content' ? 'content' : null,
            role: attributes.role === 'alert' ? 'alert' : null,
            inlineDisplay: attributes.style?.match(/(?:^|;)\s*display\s*:\s*(none|block|flex|contents)\s*(?:;|$)/i)?.[1] ?? null});
    }
    return {
        htmlSha256: sha256(html), htmlBytes: Buffer.byteLength(html),
        stateMarkupSha256: sha256(JSON.stringify(blocks)), blocks,
        cloakedErrorBlocks: blocks.filter(block => block.kind === 'error' && block.xCloak).length,
        gatedContentBlocks: blocks.filter(block => block.kind === 'content' && block.xCloak
            && block.xShow?.includes('!loading') && block.xShow.includes('!error')).length,
        earlyCollapsedMarker: html.includes('document.documentElement.dataset.theorySidebarCollapsed'),
        mobileAriaControls: html.includes('aria-controls="theory-mobile-navigation"'),
        publicStylesheets: [...html.matchAll(/href="([^\"]*\/build\/assets\/catalog-public-[^\"]+\.css)"/g)].map(match => match[1]),
    };
}

function sourceIdentity() {
    const sourceFiles = ['resources/css/catalog-public.css', 'resources/views/layouts/catalog-public.blade.php',
        'resources/views/theory/show.blade.php', 'resources/views/theory/partials/tree-nav.blade.php',
        'resources/views/theory/partials/desktop-navigation-loader.blade.php',
        'resources/views/theory/partials/mobile-navigation.blade.php', 'tools/diagnostics/theory-sidebar-stability.cjs'];
    const manifestBytes = fs.readFileSync('public/build/manifest.json');
    const manifest = JSON.parse(manifestBytes);
    const assets = Object.values(manifest).flatMap(entry => [entry.file, ...(entry.css || [])])
        .filter(file => typeof file === 'string' && /^assets\/catalog-public-[a-z0-9_-]+\.(css|js)$/i.test(file));
    return {capturedAt: new Date().toISOString(), manifestSha256: sha256(manifestBytes),
        sourceSha256: Object.fromEntries(sourceFiles.map(file => [file, sha256(fs.readFileSync(file))])),
        publicAssets: [...new Set(assets)].map(file => {
            const bytes = fs.readFileSync(path.join('public/build', file));
            return {url: `${BASE}/build/${file}`, sha256: sha256(bytes), bytes: bytes.length};
        })};
}

async function responseIdentity(response, kind) {
    const body = await response.body();
    return {url: safeUrl(response.url()), status: response.status(), bodyCompleteAt: Date.now(),
        sha256: sha256(body), bytes: body.length,
        ...(kind === 'document' ? {contract: sourceContract(body.toString('utf8'))} : {})};
}

function stableSidebarFootprint(geometry) {
    return !!geometry.sidebarFootprintDelta && Object.values(geometry.sidebarFootprintDelta)
        .every(value => Number.isFinite(value) && Math.abs(value) <= 1);
}

function options(argv) {
    const [label = 'sidebar-local', mode = 'reproduce', ...flags] = argv;
    if (!/^[a-z0-9_-]+$/i.test(label)) throw new Error('Label must contain only letters, digits, underscores and hyphens');
    if (!['reproduce', 'accept'].includes(mode)) throw new Error('Mode must be reproduce or accept');
    const result = {label, mode, observeMs: 8000, desktopRetryRequired: false, runs: 1, stage: 'm3-1'};
    for (const flag of flags) {
        if (flag === '--desktop-retry-required') result.desktopRetryRequired = true;
        else {
            const match = flag.match(/^--(page|viewport|scenario|observe-ms|runs|stage)=(.+)$/);
            if (!match) throw new Error(`Unknown flag: ${flag}`);
            result[match[1] === 'observe-ms' ? 'observeMs' : match[1]] = ['observe-ms', 'runs'].includes(match[1]) ? Number(match[2]) : match[2];
        }
    }
    if (!Number.isInteger(result.observeMs) || result.observeMs < 5000 || result.observeMs > 30000) throw new Error('Observation must be 5000–30000 ms');
    if (result.page && !TARGETS.some(([name]) => name === result.page)) throw new Error('Unknown page');
    if (result.viewport && !['desktop', 'mobile'].includes(result.viewport)) throw new Error('Unknown viewport');
    if (result.scenario && !ALL_SCENARIOS.includes(result.scenario)) throw new Error('Unknown scenario');
    if (!['m3-1', 'm3-2'].includes(result.stage)) throw new Error('Unknown evidence stage');
    if (!Number.isInteger(result.runs) || result.runs < 1 || result.runs > 10) throw new Error('Runs must be 1–10');
    if (result.runs > 1 && (!result.page || !result.viewport || result.scenario !== 'new-guest')) {
        throw new Error('Repeated runs require an explicit page, viewport and new-guest scenario');
    }
    return result;
}

async function main(config = options(process.argv.slice(2))) {
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    // prepare() blocks production/subdomains/.ub through CDP BEFORE navigation.
    // The fault routes below match only the local AJAX endpoint. No blanket routing.
    const {prepare} = require('./public-assets-browser.cjs');
    const OUTPUT = path.resolve(`storage/app/seo-${config.stage || 'm3-1'}-local`);
    fs.mkdirSync(OUTPUT, {recursive: true});
    const evidencePath = path.join(OUTPUT, `${config.label}-sidebar-${config.mode}.json`);
    if (fs.existsSync(evidencePath)) throw new Error('Evidence label already exists; choose a new label');
    // Capture disk identity before the first browser attempt, including the head
    // layout omitted by the historical M3.1 performance source inventory.
    const report = {created: new Date().toISOString(), config, base: BASE, sourceIdentity: sourceIdentity(), rows: []};
    const save = () => fs.writeFileSync(evidencePath, JSON.stringify(report, null, 2));
    const browser = await chromium.launch({headless: true,
        ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    report.browser = browser.version();
    save();
    try {
        for (const [name, url] of TARGETS.filter(([name]) => !config.page || name === config.page)) {
            for (const mobile of [false, true].filter(mobile => !config.viewport || config.viewport === (mobile ? 'mobile' : 'desktop'))) {
                const cases = (config.scenario ? [config.scenario] : SCENARIOS).flatMap(scenario =>
                    Array.from({length: config.runs || 1}, (_, index) => ({scenario, run: index + 1})));
                for (const {scenario, run} of cases) {
                    const row = {name, url, mobile, scenario, run, checks: [], blocked: [], console: [], responses: [], failures: [], ajax: [],
                        documents: [], stylesheets: [], sourceBeforeNavigation: sourceIdentity()};
                    report.rows.push(row); save();
                    const check = (name, pass, detail) => row.checks.push({name, pass: !!pass, ...(detail === undefined ? {} : {detail})});
                    const {context, page} = await prepare(browser, {mobile}, row);
                    let firstTarget = true;
                    let delayAlpineOnReload = scenario === 'delayed-alpine';
                    let navigationPhase = 'initial';
                    const pendingCaptures = [];
                    // This listener exists before goto, so the first response is
                    // retained even if Alpine/navigation later times out. No raw
                    // HTML or headers are written; only hashes and state attrs.
                    page.on('response', response => {
                        const target = new URL(response.url());
                        if (target.origin !== BASE) return;
                        const documentResponse = response.request().isNavigationRequest()
                            && response.request().frame() === page.mainFrame();
                        const stylesheetResponse = /^\/build\/assets\/catalog-public-[a-z0-9_-]+\.css$/i.test(target.pathname);
                        if (!documentResponse && !stylesheetResponse) return;
                        const entry = {phase: navigationPhase, responseAt: Date.now(), url: safeUrl(response.url()), status: response.status()};
                        (documentResponse ? row.documents : row.stylesheets).push(entry);
                        save();
                        const capture = responseIdentity(response, documentResponse ? 'document' : 'stylesheet').then(identity => {
                            Object.assign(entry, identity);
                            if (stylesheetResponse) {
                                const expected = row.sourceBeforeNavigation.publicAssets.find(asset => asset.url === entry.url);
                                entry.matchesDiskAsset = !!expected && expected.sha256 === entry.sha256 && expected.bytes === entry.bytes;
                            }
                        }).catch(error => { entry.captureError = error.name || 'response-body-unavailable'; }).finally(save);
                        pendingCaptures.push(capture);
                    });
                    await context.addInitScript(installProbe, {mobile, observeMs: config.observeMs});
                    if (['saved-collapsed-delayed-alpine', 'delayed-alpine'].includes(scenario)) {
                        await page.route(/^http:\/\/gramlyze\.loc\/livewire\/livewire(?:\.min)?\.js(?:\?|$)/, async route => {
                            if (!delayAlpineOnReload) return route.continue();
                            row.ajax.push({event: 'injected', scenario, asset: 'Livewire/Alpine', delayMs: 1200, time: Date.now()});
                            const response = await route.fetch({maxRedirects: 0});
                            await new Promise(resolve => setTimeout(resolve, 1200));
                            await route.fulfill({response});
                        });
                    }
                    if (['delay', 'error'].includes(scenario)) {
                        await page.route(/^http:\/\/gramlyze\.loc\/theory\/navigation(?:\?|$)/, async route => {
                            const requestUrl = new URL(route.request().url());
                            const targeted = mobile ? requestUrl.searchParams.get('variant') !== 'desktop'
                                : requestUrl.searchParams.get('variant') === 'desktop';
                            if (!targeted || !firstTarget) return route.continue();
                            firstTarget = false;
                            row.ajax.push({event: 'injected', scenario, variant: mobile ? 'mobile' : 'desktop', time: Date.now()});
                            if (scenario === 'delay') {
                                // Delay only this response; do not alter application code, dependencies or data.
                                const response = await route.fetch({maxRedirects: 0});
                                await new Promise(resolve => setTimeout(resolve, 2200));
                                await route.fulfill({response});
                            } else await route.fulfill({status: 500, contentType: 'text/plain; charset=utf-8', body: 'Controlled local sidebar diagnostic error'});
                        });
                    }
                    page.on('request', request => {
                        const target = new URL(request.url());
                        if (target.origin === BASE && target.pathname === '/theory/navigation') {
                            row.ajax.push({event: 'request', variant: target.searchParams.get('variant') || 'mobile', time: Date.now()});
                        }
                    });
                    page.on('response', response => {
                        const target = new URL(response.url());
                        if (target.origin === BASE && target.pathname === '/theory/navigation') {
                            row.ajax.push({event: 'response', variant: target.searchParams.get('variant') || 'mobile', status: response.status(), time: Date.now()});
                        }
                    });
                    page.on('requestfinished', request => {
                        const target = new URL(request.url());
                        if (target.origin === BASE && target.pathname === '/theory/navigation') {
                            row.ajax.push({event: 'body-complete', variant: target.searchParams.get('variant') || 'mobile', time: Date.now()});
                        }
                    });
                    const region = () => page.locator(mobile ? '[data-theory-mobile-nav-panel]' : '[data-theory-desktop-navigation-loader]');
                    const waitReady = () => page.waitForFunction(mobile => {
                        const toggle = document.querySelector('[data-theory-mobile-nav-toggle]');
                        const root = mobile ? toggle?.parentElement : document.querySelector('[data-theory-desktop-navigation-loader]');
                        const state = root?._x_dataStack?.[0];
                        return state && !state.loading && (state.error || (mobile ? state.loaded : root.querySelector('[x-ref="content"] a')));
                    }, mobile, {timeout: 30000});
                    try {
                        let response = await page.goto(BASE + url, {waitUntil: 'load', timeout: 45000});
                        check('document-200', response.status() === 200);
                        await Promise.all(pendingCaptures);
                        row.initialDocumentContract = row.documents.find(entry => entry.phase === 'initial')?.contract;
                        row.measuredDocumentContract = row.initialDocumentContract;
                        await page.waitForFunction(() => window.Alpine && document.querySelector('[data-theory-layout]')?.hasAttribute('data-collapsed'));
                        if (scenario.startsWith('saved-')) {
                            // Preserve the setup document too: an initial transient
                            // must not disappear merely because this case reloads.
                            row.initialTrace = await page.evaluate(() => window.__sidebarProbe.stop('pre-reload-fixture'));
                            const collapsed = scenario.startsWith('saved-collapsed');
                            if (mobile) await page.evaluate(value => localStorage.setItem('theorySidebarCollapsed', value), String(collapsed));
                            else if ((await page.locator('[data-theory-layout]').getAttribute('data-collapsed') === 'true') !== collapsed) {
                                await page.locator('[data-theory-aside] [data-theory-sidebar] > div > button').first().click();
                                await page.waitForTimeout(300);
                            }
                            check('saved-state-written', await page.evaluate(value => localStorage.getItem('theorySidebarCollapsed') === value, String(collapsed)));
                            delayAlpineOnReload = true;
                            navigationPhase = 'measured-reload';
                            row.sourceBeforeReload = sourceIdentity();
                            response = await page.reload({waitUntil: 'load', timeout: 45000});
                            check('reload-200', response.status() === 200);
                            await Promise.all(pendingCaptures);
                            row.measuredDocumentContract = row.documents.find(entry => entry.phase === 'measured-reload')?.contract;
                            await page.waitForFunction(() => document.querySelector('[data-theory-layout]')?.hasAttribute('data-collapsed'));
                            check('saved-state-restored', await page.locator('[data-theory-layout]').getAttribute('data-collapsed') === String(collapsed));
                        }
                        if (mobile) {
                            row.closedBefore = await page.evaluate(() => window.__sidebarProbe.snapshot('mobile-closed'));
                            check('mobile-closed-no-reserved-panel-space', row.closedBefore.mobilePanel?.height === 0);
                            await page.locator('[data-theory-mobile-nav-toggle]').click();
                            await page.evaluate(() => window.__sidebarProbe.snapshot('mobile-open'));
                        }
                        await waitReady();
                        await page.evaluate(() => window.__sidebarProbe.snapshot('ajax-ready'));
                        await page.waitForFunction(() => window.__sidebarProbe.report.ended !== null, null, {timeout: config.observeMs + 5000});
                        row.trace = await page.evaluate(() => window.__sidebarProbe.stop());
                        row.metrics = summarizeShifts(row.trace.shifts);
                        row.geometry = assessGeometry(row.trace, mobile);
                        const final = row.trace.endState;
                        const ready = final.state && !final.state.loading && (final.state.error || final.visibleStates.loaded);
                        check('complete-observation', ready && row.trace.stopReason === 'fixed-window');
                        check('first-response-identity-captured', row.documents.some(entry => entry.phase === 'initial'
                            && entry.sha256 && entry.contract?.blocks.length && !entry.captureError));
                        check('measured-response-state-attributes-captured', !!row.measuredDocumentContract?.blocks.length);
                        check('measured-response-current-state-markers', row.measuredDocumentContract?.cloakedErrorBlocks === 2
                            && row.measuredDocumentContract?.gatedContentBlocks === 2
                            && row.measuredDocumentContract?.earlyCollapsedMarker === true);
                        check('public-css-response-matches-disk', row.stylesheets.length > 0
                            && row.stylesheets.every(entry => entry.status === 200 && entry.matchesDiskAsset && !entry.captureError));
                        check('FCP-recorded', row.trace.paints.some(paint => paint.name === 'first-contentful-paint'));
                        check('one-Alpine-init', row.trace.alpineInits === 1, row.trace.alpineInits);
                        check('mutually-exclusive-visible-states', exclusiveVisibleStates(row.geometry), assessStateVisibility(row.trace));
                        check('painted-geometry-captured', !!row.geometry.initial);
                        if (scenario === 'delay') check('loading-geometry-captured', !!row.geometry.initialLoading);
                        check('sidebar-footprint-stable', stableSidebarFootprint(row.geometry), row.geometry.sidebarFootprintDelta);
                        const neighborDelta = mobile ? row.geometry.followingMenuDelta : row.geometry.tocDelta;
                        check('adjacent-content-stable', neighborDelta && ['x', 'y'].every(key => Math.abs(neighborDelta[key]) <= 1), neighborDelta);
                        if (!mobile) {
                            check('main-position-width-stable', row.geometry.mainDelta
                                && Object.values(row.geometry.mainDelta).every(value => Math.abs(value) <= 1), row.geometry.mainDelta);
                        }
                        // Preserve the measured error/collapsed state before retry/search
                        // interactions can legitimately change what the screenshot shows.
                        const screenshotPrefix = `${config.label}-${name}-${mobile ? 'mobile' : 'desktop'}-${scenario}-run-${run}`;
                        row.measuredScreenshot = `${screenshotPrefix}-settled.png`;
                        await page.screenshot({path: path.join(OUTPUT, row.measuredScreenshot)});
                        if (scenario === 'error') {
                            check('controlled-error-visible', await region().locator('[x-show="error"]').isVisible());
                            if (mobile) {
                                await page.locator('[data-theory-mobile-nav-toggle]').click();
                                await page.waitForTimeout(250);
                                await page.locator('[data-theory-mobile-nav-toggle]').click();
                                await waitReady();
                                check('mobile-close-reopen-retry', await region().locator('a').count() > 10);
                            } else {
                                const retry = region().locator('[data-theory-navigation-retry]');
                                row.desktopRetrySupported = await retry.count() > 0;
                                if (row.desktopRetrySupported) {
                                    await retry.click();
                                    await waitReady();
                                    check('desktop-retry', await region().locator('a').count() > 10);
                                } else if (config.desktopRetryRequired) check('desktop-retry-present', false);
                            }
                        }
                        // Below this point all interaction and screenshot work is outside the metric window.
                        if (await region().locator('a').count()) {
                            if (!mobile && await page.locator('[data-theory-layout]').getAttribute('data-collapsed') === 'true') {
                                await page.locator('[data-theory-aside] [data-theory-sidebar] > div > button').first().click();
                                await page.waitForTimeout(600);
                            }
                            await page.waitForTimeout(500);
                            row.scroll = await region().evaluate(element => {
                                const scroll = element.querySelector('[data-theory-sidebar-scroll]');
                                const active = scroll?.querySelector('[data-theory-nav-current-page="true"]');
                                const activeRect = active?.getBoundingClientRect();
                                const bounds = scroll?.getBoundingClientRect();
                                return {links: scroll?.querySelectorAll('a').length, overflowY: scroll && getComputedStyle(scroll).overflowY,
                                    clientHeight: scroll?.clientHeight, scrollHeight: scroll?.scrollHeight,
                                    activePresent: !!active, activeVisible: !!bounds && !!activeRect && activeRect.top >= bounds.top - 1 && activeRect.bottom <= bounds.bottom + 1};
                            });
                            check('tree-links-accessible-by-internal-scroll', row.scroll.links > 10 && ['auto', 'scroll'].includes(row.scroll.overflowY)
                                && row.scroll.clientHeight > 0 && row.scroll.scrollHeight > row.scroll.clientHeight, row.scroll);
                            check('active-page-visible', row.scroll.activePresent && row.scroll.activeVisible);
                            const search = region().locator('[data-theory-sidebar-search-input]');
                            await search.fill('Perfect');
                            await page.waitForTimeout(100);
                            check('search-highlights', await region().locator('mark').count() > 0);
                            await search.fill('zzzz-no-matching-category-918273');
                            check('search-empty-state', await region().locator('[data-theory-sidebar-search-empty]').isVisible());
                            await region().locator('[data-theory-sidebar-search-clear]').click();
                            check('search-clear', await search.inputValue() === '');
                            // Some top-level categories can be leaves; choose an actual control.
                            const branchButton = region().locator('button[\\:aria-expanded]').first();
                            check('tree-collapse-control-present', await branchButton.count() === 1);
                            const before = await branchButton.getAttribute('aria-expanded');
                            await branchButton.click();
                            await page.waitForTimeout(250);
                            check('tree-collapse-toggle', await branchButton.getAttribute('aria-expanded') !== before);
                            await branchButton.click();
                            await page.waitForTimeout(250);
                        }
                        row.screenshot = `${screenshotPrefix}.png`;
                        await page.screenshot({path: path.join(OUTPUT, row.screenshot)});
                        if (mobile) {
                            await page.locator('[data-theory-mobile-nav-toggle]').click();
                            await page.waitForTimeout(250);
                            row.closedAfter = await page.evaluate(() => window.__sidebarProbe.snapshot('mobile-closed-after'));
                            check('mobile-close-removes-panel-space', row.closedAfter.mobilePanel?.height === 0);
                            if (scenario === 'new-guest') {
                                await page.locator('[data-theory-mobile-nav-toggle]').click();
                                const other = TARGETS.find(([, target]) => target !== url)[1];
                                const link = region().locator(`a[href="${BASE + other}"]`).first();
                                const href = await link.getAttribute('href');
                                check('menu-page-target-local', new URL(href).origin === BASE);
                                // Open the existing ancestor tree controls without modifying application state directly.
                                const hiddenBranches = await link.evaluate(element => {
                                    const ancestors = [];
                                    for (let node = element.parentElement; node; node = node.parentElement) {
                                        if (node.matches('[data-theory-sidebar-node]') && node._x_dataStack?.[0]?.expanded === false) ancestors.unshift(node);
                                    }
                                    return ancestors.map(node => [...document.querySelectorAll('[data-theory-sidebar-node]')].indexOf(node));
                                });
                                for (const index of hiddenBranches) {
                                    await page.locator('[data-theory-sidebar-node]').nth(index).locator('button[\\:aria-expanded]').first().click();
                                    await page.waitForTimeout(200);
                                }
                                navigationPhase = 'functional-navigation';
                                await Promise.all([page.waitForURL(BASE + other, {waitUntil: 'load'}), link.click()]);
                                check('mobile-menu-page-navigation', page.url() === BASE + other);
                                await page.evaluate(() => window.__sidebarProbe.stop('functional-navigation'));
                            }
                        }
                        check('no-production-or-old-CDN-attempt', row.blocked.length === 0);
                        check('no-page-errors-or-double-Alpine-warning', !row.console.some(item => item.type === 'pageerror' || /multiple instances|already initialized/i.test(item.text)));
                    } catch (error) {
                        row.error = error.message;
                        // Save even failed/timeout observations. No successful low-CLS result is invented.
                        try {
                            row.trace ||= await page.evaluate(() => window.__sidebarProbe?.stop('scenario-error'));
                            if (row.trace) row.metrics ||= summarizeShifts(row.trace.shifts);
                        } catch (captureError) { row.captureError = captureError.message; }
                    } finally {
                        await Promise.all(pendingCaptures);
                        row.pass = !row.error && row.checks.every(item => item.pass);
                        save();
                        await context.close();
                        console.log(JSON.stringify({name, mobile, scenario, run, pass: row.pass,
                            failedChecks: row.checks.filter(item => !item.pass).map(item => item.name), error: row.error}));
                    }
                }
            }
        }
    } finally {
        await browser.close();
        report.completed = new Date().toISOString();
        report.pass = report.rows.length > 0 && report.rows.every(row => row.pass);
        save();
    }
    console.log(JSON.stringify({evidence: evidencePath, pass: report.pass, mode: config.mode, scenarios: report.rows.length}));
    // Reproduction expects known failed geometry checks, not infrastructure failures.
    // Acceptance fails on every failing check; neither mode suppresses thrown errors.
    if (report.rows.some(row => row.error) || (config.mode === 'accept' && !report.pass)) process.exitCode = 1;
    return report;
}

function reassessEvidence(label, inputs) {
    if (!/^[a-z0-9][a-z0-9_-]*$/i.test(label || '') || !inputs.length) throw new Error('Supply a unique reassessment label and original JSON files');
    const output = path.resolve('storage/app/seo-m3-2-local', `${label}-sidebar-reassessment.json`);
    if (fs.existsSync(output)) throw new Error('Reassessment evidence already exists');
    const result = {schema: 'gramlyze-sidebar-phase-reassessment-v1', label, created: new Date().toISOString(),
        classifierSha256: sha256(capturePhase.toString() + assessStateVisibility.toString() + exclusiveVisibleStates.toString()),
        rationale: 'Mutation snapshots added in M3.2 are not render-frame samples. Preserve all raw snapshots/counts; gate rAF-compatible samples and corroborating actual content shifts. Historical raw files are not rewritten.',
        reports: []};
    for (const input of inputs) {
        const bytes = fs.readFileSync(input), original = JSON.parse(bytes.toString('utf8'));
        const report = {sourceFile: path.basename(input), sourceFileSha256: sha256(bytes), originalPass: original.pass,
            sourceIdentity: original.sourceIdentity, config: original.config, rows: []};
        for (const row of original.rows) {
            if (!row.trace) throw new Error('Cannot reassess an incomplete row without a trace');
            const assessment = assessStateVisibility(row.trace);
            const checks = row.checks.map(check => check.name === 'mutually-exclusive-visible-states'
                ? {...check, pass: exclusiveVisibleStates(assessment), detail: assessment} : check);
            if (checks.filter(check => check.name === 'mutually-exclusive-visible-states').length !== 1) throw new Error('Expected exactly one original visibility check');
            report.rows.push({name: row.name, mobile: row.mobile, scenario: row.scenario, run: row.run,
                originalPass: row.pass, pass: !row.error && checks.every(check => check.pass), error: row.error,
                originalVisibilityCheck: row.checks.find(check => check.name === 'mutually-exclusive-visible-states'),
                assessment, setupAssessment: row.initialTrace ? assessStateVisibility(row.initialTrace) : null,
                checks, sourceBeforeNavigation: row.sourceBeforeNavigation, sourceBeforeReload: row.sourceBeforeReload,
                documents: row.documents, stylesheets: row.stylesheets});
        }
        report.pass = report.rows.length > 0 && report.rows.every(row => row.pass);
        result.reports.push(report);
    }
    result.plannedCases = result.reports.reduce((count, report) => count + report.rows.length, 0);
    result.setupObservations = result.reports.reduce((count, report) => count + report.rows.filter(row => row.setupAssessment).length, 0);
    result.pass = result.reports.length > 0 && result.reports.every(report => report.pass);
    fs.writeFileSync(output, JSON.stringify(result, null, 2), {flag: 'wx'});
    return {evidence: output, pass: result.pass, plannedCases: result.plannedCases, setupObservations: result.setupObservations};
}

module.exports = {installProbe, rectDifference, assessGeometry, assessStateVisibility, exclusiveVisibleStates, capturePhase,
    stableSidebarFootprint, sourceContract, sourceIdentity, responseIdentity, reassessEvidence, options, main};
if (require.main === module) {
    if (process.argv[2] === 'reassess') {
        try {
            const result = reassessEvidence(process.argv[3], process.argv.slice(4));
            console.log(JSON.stringify(result)); if (!result.pass) process.exitCode = 1;
        } catch (error) { console.error(error.message); process.exitCode = 1; }
    } else main().catch(error => { console.error(error); process.exitCode = 1; });
}
