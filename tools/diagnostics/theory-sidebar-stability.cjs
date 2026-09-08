// Local-only M3.1 fault/geometry acceptance. These are NOT the LCP median runs.
// Usage: node tools/diagnostics/theory-sidebar-stability.cjs LABEL reproduce|accept
// Optional: --page=sentence-types|present-perfect --viewport=desktop|mobile
//           --scenario=new-guest|saved-expanded|saved-collapsed|delay|error
//           --scenario=saved-collapsed-delayed-alpine (separate causal fault case)
//           --observe-ms=8000 --desktop-retry-required
'use strict';

const fs = require('node:fs');
const path = require('node:path');
const {summarizeShifts} = require('./cls-session-window.cjs');

const TARGETS = [
    ['sentence-types', '/theory/basic-grammar/sentence-types'],
    ['present-perfect', '/theory/tenses/present-perfect/present-perfect-forms'],
];
const SCENARIOS = ['new-guest', 'saved-expanded', 'saved-collapsed', 'delay', 'error'];
const ALL_SCENARIOS = [...SCENARIOS, 'saved-collapsed-delayed-alpine'];
const BASE = 'http://gramlyze.loc';
const OUTPUT = path.resolve('storage/app/seo-m3-1-local');

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
            scrollTop: element.scrollTop, scrollHeight: element.scrollHeight, clientHeight: element.clientHeight,
        };
    };
    const visible = (element) => !!element && element.getClientRects().length > 0
        && getComputedStyle(element).visibility !== 'hidden';
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
        const loading = visible(region?.querySelector('[x-show="loading"]'));
        const error = visible(region?.querySelector('[x-show="error"]'));
        // Mobile's content wrapper uses display:contents and has no box of its own.
        const loaded = visible(content?.querySelector('a'));
        return {
            scrollY, collapsed: layout?.getAttribute('data-collapsed'),
            settled: layout?.getAttribute('data-settled'),
            state: state ? {loading: !!state.loading, error: !!state.error, loaded: !!state.loaded, open: !!state.open} : null,
            visibleStates: {loading, error, loaded, count: Number(loading) + Number(error) + Number(loaded)},
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
    const snapshot = (label) => {
        const frame = {label, timestamp: performance.now(), ...geometry()};
        if (!stopped) report.frames.push(frame);
        return frame;
    };
    const tick = () => {
        if (stopped) return;
        if (!report.firstPaintedFrame && performance.getEntriesByName('first-contentful-paint').length) {
            report.firstPaintedFrame = snapshot('first-painted-frame');
        }
        const current = geometry();
        const key = JSON.stringify(current);
        if (key !== lastGeometry) {
            report.frames.push({label: 'geometry-change', timestamp: performance.now(), ...current});
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
    document.addEventListener('alpine:init', () => { report.alpineInits++; mark('alpine:init'); });
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
        overlappingStateFrames: trace.frames.filter(frame => frame.timestamp >= firstPaint && frame.visibleStates.count > 1).length,
    };
    // Compare document positions because clicks can scroll the mobile toggle into view.
    for (const key of ['sidebarDelta', 'mainDelta', 'followingMenuDelta']) {
        if (result[key]) result[key].y += final.scrollY - initial.scrollY;
    }
    if (result.tocDelta) result.tocDelta.y += final.scrollY - tocInitial.scrollY;
    return result;
}

function sourceContract(html) {
    return {
        cloakedErrorBlocks: (html.match(/<div[^>]*x-show="error"[^>]*x-cloak[^>]*>/g) || []).length,
        gatedContentBlocks: (html.match(/<div[^>]*x-ref="content"[^>]*x-show="[^\"]*!loading[^\"]*!error[^\"]*"[^>]*x-cloak[^>]*>/g) || []).length,
        earlyCollapsedMarker: html.includes('document.documentElement.dataset.theorySidebarCollapsed'),
        mobileAriaControls: html.includes('aria-controls="theory-mobile-navigation"'),
        publicStylesheets: [...html.matchAll(/href="([^\"]*\/build\/assets\/catalog-public-[^\"]+\.css)"/g)].map(match => match[1]),
    };
}

function stableSidebarFootprint(geometry) {
    return !!geometry.sidebarFootprintDelta && Object.values(geometry.sidebarFootprintDelta)
        .every(value => Number.isFinite(value) && Math.abs(value) <= 1);
}

function options(argv) {
    const [label = 'sidebar-local', mode = 'reproduce', ...flags] = argv;
    if (!/^[a-z0-9_-]+$/i.test(label)) throw new Error('Label must contain only letters, digits, underscores and hyphens');
    if (!['reproduce', 'accept'].includes(mode)) throw new Error('Mode must be reproduce or accept');
    const result = {label, mode, observeMs: 8000, desktopRetryRequired: false};
    for (const flag of flags) {
        if (flag === '--desktop-retry-required') result.desktopRetryRequired = true;
        else {
            const match = flag.match(/^--(page|viewport|scenario|observe-ms)=(.+)$/);
            if (!match) throw new Error(`Unknown flag: ${flag}`);
            result[match[1] === 'observe-ms' ? 'observeMs' : match[1]] = match[1] === 'observe-ms' ? Number(match[2]) : match[2];
        }
    }
    if (!Number.isInteger(result.observeMs) || result.observeMs < 5000 || result.observeMs > 30000) throw new Error('Observation must be 5000–30000 ms');
    if (result.page && !TARGETS.some(([name]) => name === result.page)) throw new Error('Unknown page');
    if (result.viewport && !['desktop', 'mobile'].includes(result.viewport)) throw new Error('Unknown viewport');
    if (result.scenario && !ALL_SCENARIOS.includes(result.scenario)) throw new Error('Unknown scenario');
    return result;
}

async function main(config = options(process.argv.slice(2))) {
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    // prepare() blocks production/subdomains/.ub through CDP BEFORE navigation.
    // The fault routes below match only the local AJAX endpoint. No blanket routing.
    const {prepare} = require('./public-assets-browser.cjs');
    fs.mkdirSync(OUTPUT, {recursive: true});
    const evidencePath = path.join(OUTPUT, `${config.label}-sidebar-${config.mode}.json`);
    if (fs.existsSync(evidencePath)) throw new Error('Evidence label already exists; choose a new label');
    const report = {created: new Date().toISOString(), config, base: BASE, rows: []};
    const save = () => fs.writeFileSync(evidencePath, JSON.stringify(report, null, 2));
    const browser = await chromium.launch({headless: true,
        ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    report.browser = browser.version();
    save();
    try {
        for (const [name, url] of TARGETS.filter(([name]) => !config.page || name === config.page)) {
            for (const mobile of [false, true].filter(mobile => !config.viewport || config.viewport === (mobile ? 'mobile' : 'desktop'))) {
                for (const scenario of config.scenario ? [config.scenario] : SCENARIOS) {
                    const row = {name, url, mobile, scenario, checks: [], blocked: [], console: [], responses: [], failures: [], ajax: []};
                    const check = (name, pass, detail) => row.checks.push({name, pass: !!pass, ...(detail === undefined ? {} : {detail})});
                    const {context, page} = await prepare(browser, {mobile}, row);
                    let firstTarget = true;
                    let delayAlpineOnReload = false;
                    await context.addInitScript(installProbe, {mobile, observeMs: config.observeMs});
                    if (scenario === 'saved-collapsed-delayed-alpine') {
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
                        row.initialDocumentContract = sourceContract(await response.text());
                        row.measuredDocumentContract = row.initialDocumentContract;
                        await page.waitForFunction(() => window.Alpine && document.querySelector('[data-theory-layout]')?.hasAttribute('data-collapsed'));
                        if (scenario.startsWith('saved-')) {
                            await page.evaluate(() => window.__sidebarProbe.stop('pre-reload-fixture'));
                            const collapsed = scenario.startsWith('saved-collapsed');
                            if (mobile) await page.evaluate(value => localStorage.setItem('theorySidebarCollapsed', value), String(collapsed));
                            else if ((await page.locator('[data-theory-layout]').getAttribute('data-collapsed') === 'true') !== collapsed) {
                                await page.locator('[data-theory-aside] [data-theory-sidebar] > div > button').first().click();
                                await page.waitForTimeout(300);
                            }
                            check('saved-state-written', await page.evaluate(value => localStorage.getItem('theorySidebarCollapsed') === value, String(collapsed)));
                            delayAlpineOnReload = true;
                            response = await page.reload({waitUntil: 'load', timeout: 45000});
                            check('reload-200', response.status() === 200);
                            row.measuredDocumentContract = sourceContract(await response.text());
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
                        check('one-Alpine-init', row.trace.alpineInits === 1, row.trace.alpineInits);
                        check('mutually-exclusive-visible-states', row.geometry.overlappingStateFrames === 0, row.geometry.overlappingStateFrames);
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
                        row.measuredScreenshot = `${config.label}-${name}-${mobile ? 'mobile' : 'desktop'}-${scenario}-settled.png`;
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
                        row.screenshot = `${config.label}-${name}-${mobile ? 'mobile' : 'desktop'}-${scenario}.png`;
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
                        row.pass = !row.error && row.checks.every(item => item.pass);
                        report.rows.push(row);
                        save();
                        await context.close();
                        console.log(JSON.stringify({name, mobile, scenario, pass: row.pass,
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

module.exports = {installProbe, rectDifference, assessGeometry, stableSidebarFootprint, sourceContract, options, main};
if (require.main === module) main().catch(error => { console.error(error); process.exitCode = 1; });
