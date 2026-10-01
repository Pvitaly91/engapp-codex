'use strict';
// M25 real-server, read-only Chromium acceptance. Live captures never
// substitute lesson HTML or change the DB. The optional frozen-before zoom
// replay is explicitly labelled a private fixture, never a live baseline.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {execFileSync} = require('node:child_process');
const {JSDOM} = require('jsdom');
const {metadata} = require('./seo-m11-local.cjs');
const {summarizeShifts} = require('./cls-session-window.cjs');
const BASE = 'http://gramlyze.loc';
const noScriptPages = new WeakSet();
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const clean = value => String(value || '').replace(/\s+/g, ' ').trim();
const safeUrl = value => { try { const u = new URL(value); return u.origin + u.pathname; } catch { return '[non-url]'; } };
const VIEWPORTS = Object.freeze([
    {name: 'desktop', width: 1440, height: 900, mobile: false},
    {name: 'mobile', width: 390, height: 844, mobile: true},
    {name: 'narrow-mobile', width: 320, height: 800, mobile: true},
]);
const DEFAULT_PLAN = Object.freeze({
    theoryPaths: [
        '/theory/clauses-and-linking-words/linking-words-reason-result-contrast',
        '/theory/formal-english/nominal-style-and-information-density',
        '/theory/basic-grammar/word-order/inversion-basics',
        '/theory/academic-english/hedging-and-cautious-language',
        '/theory/tenses/present-perfect-vs-present-perfect-continuous',
        '/theory/tenses/narrative-tenses',
        '/theory/mixed-revision/b1-mixed-revision',
        '/theory/tenses/past-simple-vs-past-continuous',
        '/theory/tenses/present-perfect/present-perfect-forms',
        '/theory/reported-speech/reported-statements',
        '/theory/present-simple',
    ],
    performancePaths: [
        '/theory/tenses/past-simple-vs-past-continuous',
        '/theory/tenses/narrative-tenses',
        '/theory/formal-english/nominal-style-and-information-density',
    ],
    testPath: '/test/future-perfect/questions',
    coursePath: '/courses/english-grammar-theory/lesson/tenses/narrative-tenses',
});

function validatePlan(plan) {
    const relative = value => typeof value === 'string' && /^\/(?:theory|test|courses)\/[a-z0-9/-]+$/.test(value)
        && !value.includes('//') && !value.includes('..');
    assert.ok(Array.isArray(plan.theoryPaths) && plan.theoryPaths.length >= 10, 'At least ten unique representative paths required');
    assert.equal(new Set(plan.theoryPaths).size, plan.theoryPaths.length, 'Repeated paths are not new acceptance pages');
    assert.ok(plan.theoryPaths.every(value => relative(value) && value.startsWith('/theory/')));
    assert.ok(Array.isArray(plan.performancePaths) && plan.performancePaths.length === 3);
    assert.ok(plan.performancePaths.every(value => plan.theoryPaths.includes(value)));
    assert.ok(relative(plan.testPath) && plan.testPath.startsWith('/test/'));
    assert.ok(relative(plan.coursePath) && plan.coursePath.startsWith('/courses/'));
    return {...plan};
}

function decision(value, method, navigation, plan) {
    const u = new URL(value);
    if (!['GET', 'HEAD'].includes(method)) return 'stateful-request-blocked';
    if (u.username || u.password) return 'credentialed-url';
    if (navigation) {
        const allowed = [...plan.theoryPaths, plan.testPath, plan.coursePath];
        return u.origin === BASE && allowed.includes(u.pathname) && !u.search ? null : 'outside-navigation-plan';
    }
    if (u.origin === BASE) return null;
    // Preserve the current page's natural font network, recording actual
    // failures. No production host or third-party executable CDN is allowed.
    return ['fonts.googleapis.com', 'fonts.gstatic.com'].includes(u.hostname) && u.protocol === 'https:' ? null : 'external-host';
}

function diagnostics() {
    return {pageErrors: [], console: [], failures: [], httpErrors: [], blocked: [], assets: [], screenshots: []};
}

function assertLocalReads(row) {
    assert.equal(row.failures.filter(item => ['GET', 'HEAD'].includes(item.method) && new URL(item.url).origin === BASE
        && !item.expectedScriptDisabled).length,
        0, 'Local read-only network requests must succeed');
    assert.equal(row.httpErrors.filter(item => new URL(item.url).origin === BASE).length, 0, 'Local HTTP resource errors');
}

async function createPage(browser, viewport, row, plan, javaScriptEnabled = true, privateFixtures = null) {
    row.javaScriptEnabled = javaScriptEnabled;
    const context = await browser.newContext({
        viewport: {width: viewport.width, height: viewport.height}, isMobile: viewport.mobile,
        hasTouch: viewport.mobile, locale: 'uk-UA', colorScheme: 'light', serviceWorkers: 'block', javaScriptEnabled,
        ...(viewport.deviceScaleFactor ? {deviceScaleFactor: viewport.deviceScaleFactor} : {}),
    });
    await context.route('**/*', route => {
        const q = route.request();
        if (q.isNavigationRequest() && q.method() === 'GET' && privateFixtures?.has(q.url())) {
            return route.fulfill({status: 200, contentType: 'text/html; charset=utf-8', body: privateFixtures.get(q.url())});
        }
        const reason = decision(q.url(), q.method(), q.isNavigationRequest(), plan);
        if (reason) { row.blocked.push({url: safeUrl(q.url()), method: q.method(), reason}); return route.abort('blockedbyclient'); }
        return route.continue();
    });
    const page = await context.newPage();
    if (!javaScriptEnabled) noScriptPages.add(page);
    page.setDefaultTimeout(20000);
    page.on('pageerror', error => row.pageErrors.push({name: error.name, messageSha256: sha(error.message)}));
    page.on('console', message => {
        if (['warning', 'error'].includes(message.type())) row.console.push({type: message.type(),
            messageSha256: sha(message.text()), source: safeUrl(message.location().url),
            resourceFailure: /Failed to load resource/.test(message.text())});
    });
    page.on('requestfailed', q => row.failures.push({url: safeUrl(q.url()), method: q.method(), type: q.resourceType(),
        error: q.failure()?.errorText,
        expectedScriptDisabled: !javaScriptEnabled && q.resourceType() === 'script' && q.failure()?.errorText === 'csp'}));
    page.on('response', response => {
        const q = response.request(); const u = new URL(response.url());
        if (response.status() >= 400) row.httpErrors.push({url: safeUrl(response.url()), status: response.status()});
        if (u.origin === BASE && ['stylesheet', 'script', 'font', 'image'].includes(q.resourceType())) {
            row.assets.push({url: safeUrl(response.url()), type: q.resourceType(), status: response.status()});
        }
    });
    return {context, page};
}

async function paint(page) {
    // Chromium script-disabled pages do not run requestAnimationFrame
    // callbacks; sync DOM evaluation remains available for observation.
    if (noScriptPages.has(page)) { await page.waitForTimeout(34); return; }
    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
}

async function navigate(page, urlPath) {
    const response = await page.goto(BASE + urlPath, {waitUntil: 'domcontentloaded', timeout: 60000});
    assert.ok(response, 'Missing HTTP response');
    return response;
}

function serverEvidence(html) {
    const dom = new JSDOM(html); const d = dom.window.document;
    const main = d.querySelector('[data-theory-main]') || d.querySelector('[data-theory-lesson-content]') || d.querySelector('main') || d.body;
    const learning = main.querySelector('[data-theory-learning-content]') || main.querySelector('.theory-content-stack') || main;
    const result = {metadata: metadata(d), responseSha256: sha(html), learningTextSha256: sha(clean(learning.textContent)),
        learningCharacters: clean(learning.textContent).length,
        details: learning.querySelectorAll('details').length, tables: learning.querySelectorAll('table').length,
        newDisclosureControls: d.querySelectorAll('[data-theory-disclosure], .theory-section-disclosure').length};
    dom.window.close(); return result;
}

async function screenshot(page, directory, row, name) {
    const target = path.join(directory, name + '.png');
    assert.ok(!fs.existsSync(target), 'Refusing screenshot overwrite');
    await paint(page); await page.screenshot({path: target, animations: 'disabled'});
    row.screenshots.push(path.basename(target));
}

async function snapshots(page, directory, row, prefix) {
    let main = page.locator('[data-theory-main]').first();
    if (!await main.count()) main = page.locator('main').first();
    assert.ok(await main.count(), 'Page main content required for three-position screenshots');
    const positions = await main.evaluate(node => {
        const box = node.getBoundingClientRect(); const start = box.top + scrollY;
        return [Math.max(0, start - 170), start + box.height / 2 - innerHeight / 2, start + box.height - innerHeight + 120];
    });
    for (const [i, suffix] of ['top', 'middle', 'bottom'].entries()) {
        await page.evaluate(y => scrollTo({top: Math.max(0, y), behavior: 'instant'}), positions[i]);
        await screenshot(page, directory, row, prefix + '-' + suffix);
    }
}

async function geometry(page) {
    return page.evaluate(() => {
        const main = document.querySelector('[data-theory-main]') || document.querySelector('main');
        const ids = [...document.querySelectorAll('[id]')].map(node => node.id).filter(Boolean);
        const documentDuplicateIds = [...new Set(ids.filter((id, i) => ids.indexOf(id) !== i))];
        const learningIds = [...(main?.querySelectorAll('[id]') || [])].map(node => node.id).filter(Boolean);
        const duplicateIds = [...new Set(learningIds.filter((id, i) => learningIds.indexOf(id) !== i))];
        const hidden = [...(main?.querySelectorAll('.theory-native-block, .theory-rich-section, .theory-legacy-section') || [])]
            .filter(node => !node.getClientRects().length).map(node => node.id || node.className);
        return {overflow: document.documentElement.scrollWidth > innerWidth + 2,
            overflowingElements: document.documentElement.scrollWidth > innerWidth + 2
                ? [...document.querySelectorAll('body *')].filter(node => {
                    const box = node.getBoundingClientRect(); return node.getClientRects().length && (box.right > innerWidth + 2 || box.left < -2);
                }).slice(0, 30).map(node => { const box = node.getBoundingClientRect();
                    return {tag: node.tagName, id: node.id, class: String(node.className).slice(0, 100),
                        left: box.left, right: box.right, top: box.top, width: box.width,
                        textSample: node.tagName === 'SPAN' ? node.textContent.slice(0, 80) : null,
                        ancestors: [node.parentElement, node.parentElement?.parentElement, node.parentElement?.parentElement?.parentElement]
                            .filter(Boolean).map(parent => ({tag: parent.tagName, id: parent.id,
                                class: String(parent.className).slice(0, 160), overflowX: getComputedStyle(parent).overflowX,
                                position: getComputedStyle(parent).position}))}; }) : [],
            viewport: {width: innerWidth, height: innerHeight, devicePixelRatio},
            contentWidth: document.documentElement.scrollWidth, duplicateIds, documentDuplicateIds, hiddenSections: hidden,
            h1: [...document.querySelectorAll('h1')].map(node => node.textContent.trim()),
            tables: main?.querySelectorAll('table').length || 0,
            nativeBlocks: main?.querySelectorAll('.theory-native-block').length || 0,
            richSections: main?.querySelectorAll('.theory-rich-section').length || 0,
            headings: [...(main?.querySelectorAll('h2,h3,h4') || [])].map(node => ({tag: node.tagName, text: node.textContent.replace(/\s+/g, ' ').trim()})),
            newDisclosureControls: document.querySelectorAll('[data-theory-disclosure], .theory-section-disclosure').length};
    });
}

async function theme(page, dark) {
    const isDark = () => page.locator('html').evaluate(node => node.classList.contains('dark'));
    if (await isDark() !== dark) {
        let button = page.locator('.site-header button[\\@click="toggleTheme"]' + ':' + 'visible').first();
        let openedMenu = false;
        if (!await button.count()) {
            await page.getByRole('button', {name: 'Меню', exact: true}).click();
            openedMenu = true;
            button = page.locator('.site-header button[\\@click="toggleTheme"]' + ':' + 'visible').first();
        }
        await button.click();
        await page.waitForFunction(expected => document.documentElement.classList.contains('dark') === expected, dark);
        if (openedMenu) await page.getByRole('button', {name: 'Меню', exact: true}).click();
    }
    return await isDark();
}

async function tableChecks(page) {
    const rows = [];
    for (const table of await page.locator('[data-theory-main] table').all()) {
        await table.scrollIntoViewIfNeeded(); await paint(page);
        rows.push(await table.evaluate(node => {
            let parent = node.parentElement;
            while (parent && parent !== document.body && !['auto', 'scroll'].includes(getComputedStyle(parent).overflowX)) parent = parent.parentElement;
            if (!parent || parent === document.body) return {localScroller: false, tableWidth: node.scrollWidth};
            const old = parent.scrollLeft; parent.scrollLeft = parent.scrollWidth;
            const result = {localScroller: true, clientWidth: parent.clientWidth, scrollWidth: parent.scrollWidth, scrolled: parent.scrollLeft};
            parent.scrollLeft = old; return result;
        }));
    }
    return rows;
}

async function tocCheck(page) {
    let link = page.locator('[data-theory-toc-links] a' + ':' + 'visible').first();
    let mobileTocExpanded = false;
    if (!await link.count()) {
        const summary = page.locator('.theory-mobile-toc > summary' + ':' + 'visible').first();
        if (await summary.count()) {
            await summary.click();
            assert.ok(await summary.evaluate(node => node.parentElement.open), 'Mobile native TOC expands');
            mobileTocExpanded = true;
            link = page.locator('[data-theory-toc-links] a' + ':' + 'visible').first();
        }
    }
    if (!await link.count()) return {available: false};
    const href = await link.getAttribute('href');
    assert.match(href, /^#[^\s]+$/);
    await link.click();
    // Existing anchor navigation may use smooth scrolling; two paints do not
    // mean the target has arrived. Observe arrival without modifying the UI.
    await page.waitForFunction(id => {
        const node = document.getElementById(id); if (!node) return false;
        const box = node.getBoundingClientRect(); return box.bottom > 0 && box.top < innerHeight;
    }, href.slice(1));
    await paint(page);
    const target = await page.evaluate(id => {
        const node = document.getElementById(id); if (!node) return null;
        const box = node.getBoundingClientRect();
        const header = document.getElementById('site-header')?.getBoundingClientRect();
        return {id, top: box.top, bottom: box.bottom, headerBottom: header?.bottom, hash: location.hash, visible: box.bottom > 0 && box.top < innerHeight};
    }, href.slice(1));
    assert.ok(target?.visible, 'TOC link must reach the existing visible section');
    return {available: true, mobileTocExpanded, ...target};
}

async function answerDisclosure(page) {
    // The mobile-only TOC is not an answer control and is hidden on desktop.
    const summary = page.locator('.theory-content-blocks details > summary' + ':' + 'visible').first();
    if (!await summary.count()) return {available: false};
    const open = () => summary.evaluate(node => node.parentElement.open);
    const initial = await open();
    await summary.click(); assert.notEqual(await open(), initial);
    await summary.click(); assert.equal(await open(), initial);
    await summary.focus(); await page.keyboard.press('Enter'); assert.notEqual(await open(), initial);
    await page.keyboard.press('Space'); assert.equal(await open(), initial);
    await page.keyboard.press('Tab');
    const focusMovedOnTab = !(await summary.evaluate(node => document.activeElement === node));
    assert.ok(focusMovedOnTab, 'Tab must move focus after the existing answer summary');
    return {available: true, initialOpen: initial, mouse: true, enter: true, space: true,
        focusMovedOnTab};
}

async function nativePractice(page) {
    const exercise = page.locator('.theory-exercise').first();
    if (!await exercise.count()) return {available: false};
    const check = exercise.getByRole('button', {name: 'Перевірити', exact: true}).first();
    if (!await check.count()) return {available: true, checkable: false};
    const option = exercise.getByRole('button', {name: /^(?:a|b|have|has)$/i}).first();
    if (await option.count()) { await option.click(); await paint(page); }
    await check.click(); await paint(page);
    const feedback = await exercise.locator('[x-text^="feedbackText"]' + ':' + 'visible').allTextContents();
    const visibleFeedback = clean(feedback.join(' '));
    assert.ok(visibleFeedback, 'Existing native check button should display local feedback');
    return {available: true, checkable: true, optionClicked: await option.count() > 0,
        feedbackVisible: true, feedbackSha256: sha(visibleFeedback), serverMutationAllowed: false};
}

async function navigationControls(page, mobile) {
    const result = {};
    if (mobile && await page.locator('[data-theory-mobile-nav-toggle]').count()) {
        await page.locator('[data-theory-mobile-nav-toggle]').click();
        await page.waitForFunction(() => {
            const root = document.querySelector('[data-theory-mobile-nav-toggle]')?.parentElement;
            const data = root?._x_dataStack?.[0]; return data && !data.loading && data.loaded && !data.error;
        });
        result.mobileMapLinks = await page.locator('[data-theory-mobile-nav-panel] a').count();
        assert.ok(result.mobileMapLinks > 0);
        await page.locator('[data-theory-mobile-nav-toggle]').click();
        await page.getByRole('button', {name: 'Меню', exact: true}).click();
        await page.locator('#site-header [x-show="mobile"] nav a').first().waitFor({state: 'visible'});
        result.headerMenuLinks = await page.locator('#site-header a' + ':' + 'visible').count();
        assert.ok(result.headerMenuLinks > 3);
        await page.getByRole('button', {name: 'Меню', exact: true}).click();
    } else if (!mobile && await page.locator('[data-theory-desktop-navigation-loader]').count()) {
        await page.waitForFunction(() => document.querySelector('[data-theory-desktop-navigation-loader] [x-ref="content"] a'));
        result.desktopMapLinks = await page.locator('[data-theory-desktop-navigation-loader] a').count();
        assert.ok(result.desktopMapLinks > 0);
        const toggle = page.locator('[data-theory-aside] [data-theory-sidebar] > div > button').first();
        if (await toggle.count()) {
            await toggle.click();
            await page.waitForFunction(() => document.querySelector('[data-theory-layout]')?.dataset.collapsed === 'true');
            await toggle.click();
            await page.waitForFunction(() => document.querySelector('[data-theory-layout]')?.dataset.collapsed === 'false');
            result.collapseRestore = true;
        }
        const search = page.locator('#site-header input[x-model="query"]' + ':' + 'visible').first();
        if (await search.count()) {
            const responsePromise = page.waitForResponse(response => new URL(response.url()).origin === BASE
                && /^\/(?:[a-z]{2}\/)?search$/.test(new URL(response.url()).pathname)
                && response.request().method() === 'GET' && response.request().headers().accept === 'application/json', {timeout: 20000});
            await search.fill('perfect'); const response = await responsePromise;
            result.headerSearchStatus = response.status(); assert.equal(response.status(), 200);
            const data = await response.json(); result.headerSearchResults = Array.isArray(data) ? data.length : null;
            await search.press('Escape'); await search.fill('');
        }
    }
    return result;
}

// PerformanceObserver instrumentation observes only. It never patches the DOM,
// fetch, application state, theme, or learning text.
function installPerformance(windowMs) {
    const data = window.__m25Performance = {windowMs, supported: PerformanceObserver.supportedEntryTypes,
        lcp: [], shifts: [], complete: false};
    const observers = [];
    for (const type of ['largest-contentful-paint', 'layout-shift']) {
        if (!PerformanceObserver.supportedEntryTypes.includes(type)) continue;
        const callback = entries => entries.forEach(entry => {
            if (type === 'largest-contentful-paint') data.lcp.push({startTime: entry.startTime, size: entry.size});
            else data.shifts.push({startTime: entry.startTime, value: entry.value, hadRecentInput: entry.hadRecentInput});
        });
        const observer = new PerformanceObserver(list => callback(list.getEntries()));
        observer.observe({type, buffered: true}); observers.push({observer, callback});
    }
    setTimeout(() => {
        observers.forEach(({observer, callback}) => { callback(observer.takeRecords()); observer.disconnect(); });
        data.endedAt = performance.now(); data.complete = true;
    }, windowMs);
}

async function performanceCapture(browser, directory, label, plan, report, save) {
    for (const urlPath of plan.performancePaths) for (let repeat = 1; repeat <= 3; repeat++) {
        const row = {path: urlPath, repeat, ...diagnostics()}; report.performance.push(row); save();
        const {context, page} = await createPage(browser, VIEWPORTS[0], row, plan);
        await page.addInitScript(installPerformance, 5000);
        try {
            const response = await navigate(page, urlPath); row.status = response.status(); assert.equal(row.status, 200);
            await page.waitForFunction(() => window.__m25Performance?.complete, {timeout: 20000});
            const values = await page.evaluate(() => ({observer: window.__m25Performance,
                navigation: performance.getEntriesByType('navigation').map(item => ({requestStart: item.requestStart,
                    responseStart: item.responseStart, responseEnd: item.responseEnd, domContentLoadedEventEnd: item.domContentLoadedEventEnd,
                    loadEventEnd: item.loadEventEnd, transferSize: item.transferSize, encodedBodySize: item.encodedBodySize}))[0],
                resources: performance.getEntriesByType('resource').map(item => {
                    const u = new URL(item.name); return {url: u.origin + u.pathname, initiatorType: item.initiatorType,
                        duration: item.duration, transferSize: item.transferSize, encodedBodySize: item.encodedBodySize};
                }), visibility: document.visibilityState}));
            row.measurement = {windowMs: values.observer.windowMs, endedAtMs: values.observer.endedAt,
                ttfbMs: values.navigation.responseStart - values.navigation.requestStart,
                lcpMs: values.observer.lcp.at(-1)?.startTime ?? null,
                cls: summarizeShifts(values.observer.shifts), navigation: values.navigation,
                observerSupported: values.observer.supported, visibility: values.visibility,
                resources: values.resources, conditions: 'Fresh isolated context; desktop1440; natural fonts; routed requests disable browser cache; 5s observation; no interaction; local laboratory, not field CWV.'};
            assertLocalReads(row); row.pass = row.pageErrors.length === 0;
        } catch (error) { row.pass = false; row.error = error.message.slice(0, 500); }
        finally { await context.close(); save(); }
        console.log(JSON.stringify({mode: label, performance: urlPath, repeat, pass: row.pass,
            ttfbMs: row.measurement?.ttfbMs, lcpMs: row.measurement?.lcpMs, cls: row.measurement?.cls.clsSessionWindow, error: row.error}));
    }
}

async function realLessons(browser, directory, label, mode, plan, report, save, baseline) {
    const viewports = mode === 'before' ? [VIEWPORTS[0]] : VIEWPORTS;
    for (const urlPath of plan.theoryPaths) for (const viewport of viewports) {
        const row = {path: urlPath, viewport: viewport.name, themes: [], ...diagnostics()}; report.lessons.push(row); save();
        const {context, page} = await createPage(browser, viewport, row, plan);
        try {
            const response = await navigate(page, urlPath); row.status = response.status(); assert.equal(row.status, 200);
            row.server = serverEvidence(await response.text());
            await page.locator('h1').first().waitFor({state: 'visible'});
            await page.waitForLoadState('load', {timeout: 30000});
            await paint(page);
            const key = urlPath.split('/').at(-1);
            for (const name of mode === 'before' ? ['light'] : ['light', 'dark']) {
                if (mode === 'after') assert.equal(await theme(page, name === 'dark'), name === 'dark');
                const state = {name, geometry: await geometry(page)};
                if (mode === 'after') {
                    assert.equal(state.geometry.overflow, false, 'Document overflow');
                    assert.deepEqual(state.geometry.duplicateIds, [], 'Duplicate learning DOM IDs');
                    const previous = baseline?.lessons?.find(item => item.path === urlPath)?.themes?.[0]?.geometry;
                    assert.ok(previous, 'Before browser proof required for shell-ID comparison');
                    // Older before-proof schema recorded all IDs in duplicateIds.
                    const known = previous.documentDuplicateIds ?? previous.duplicateIds;
                    state.preexistingShellDuplicateIds = state.geometry.documentDuplicateIds.filter(id => known.includes(id));
                    assert.ok(state.geometry.documentDuplicateIds.every(id => known.includes(id)), 'New full-document duplicate ID');
                    assert.deepEqual(state.geometry.hiddenSections, [], 'Learning sections hidden');
                    assert.equal(state.geometry.newDisclosureControls, 0, 'No new disclosure on real M25 lessons');
                    state.tables = await tableChecks(page);
                    for (const table of state.tables) if (table.localScroller && table.scrollWidth > table.clientWidth + 2)
                        assert.ok(table.scrolled > 0, 'A wide local table must scroll');
                    const reload = await page.reload({waitUntil: 'domcontentloaded', timeout: 60000});
                    assert.equal(reload.status(), 200);
                    state.themeReload = await page.locator('html').evaluate(node => node.classList.contains('dark')) === (name === 'dark');
                    assert.ok(state.themeReload, 'Theme survives real reload');
                    assert.deepEqual(serverEvidence(await reload.text()).metadata, row.server.metadata, 'Reload SEO unchanged');
                }
                await snapshots(page, directory, row, `${label}-${key}-${viewport.name}-${name}`);
                row.themes.push(state);
            }
            if (mode === 'after') {
                row.answerDisclosure = await answerDisclosure(page);
                row.toc = await tocCheck(page);
                // One interaction control per viewport avoids multiplying the
                // category-navigation request cost for every representative.
                if (urlPath === plan.performancePaths[0]) row.navigationControls = await navigationControls(page, viewport.mobile);
                row.nativePractice = await nativePractice(page);
            }
            assert.equal(row.pageErrors.length, 0, 'Browser pageerror');
            assertLocalReads(row); row.pass = true;
        } catch (error) { row.pass = false; row.error = error.message.slice(0, 500); }
        finally { await context.close(); save(); }
        console.log(JSON.stringify({path: urlPath, viewport: viewport.name, pass: row.pass, themes: row.themes.length,
            errors: row.pageErrors.length, failed: row.failures.length, error: row.error}));
    }
}

async function supplementary(browser, directory, label, plan, report, save, baselineControls) {
    for (const urlPath of plan.performancePaths) for (const viewport of [VIEWPORTS[0], VIEWPORTS[1]]) {
        const row = {kind: 'no-js', path: urlPath, viewport: viewport.name, ...diagnostics()}; report.controls.push(row);
        const {context, page} = await createPage(browser, viewport, row, plan, false);
        try {
            const response = await navigate(page, urlPath); row.status = response.status(); assert.equal(row.status, 200);
            await page.waitForLoadState('load', {timeout: 30000});
            row.server = serverEvidence(await response.text()); row.geometry = await geometry(page);
            assert.equal(row.geometry.overflow, false); assert.deepEqual(row.geometry.hiddenSections, []);
            assert.ok(row.server.learningCharacters > 100);
            const summary = page.locator('.theory-content-blocks details > summary' + ':' + 'visible').first();
            if (await summary.count()) { const old = await summary.evaluate(node => node.parentElement.open); await summary.click();
                row.nativeAnswerOpensWithoutJs = await summary.evaluate(node => node.parentElement.open) !== old; assert.ok(row.nativeAnswerOpensWithoutJs); }
            await snapshots(page, directory, row, `${label}-no-js-${urlPath.split('/').at(-1)}-${viewport.name}`);
            assertLocalReads(row); row.pass = true;
        } catch (error) { row.pass = false; row.error = error.message.slice(0, 500); }
        finally { await context.close(); save(); }
    }
    for (const urlPath of plan.performancePaths) {
        const row = {kind: '200-percent-zoom-layout', path: urlPath,
            condition: '720 CSS px at DPR2 = layout equivalent of 1440 device px at 200% browser zoom; not a browser UI shortcut.', ...diagnostics()};
        report.controls.push(row);
        const {context, page} = await createPage(browser, {width: 720, height: 450, mobile: false, deviceScaleFactor: 2}, row, plan);
        try {
            const response = await navigate(page, urlPath); assert.equal(response.status(), 200);
            await page.waitForLoadState('load', {timeout: 30000}); await paint(page);
            row.geometry = await geometry(page); assert.equal(row.geometry.overflow, false);
            assert.equal(row.geometry.viewport.width, 720); assert.equal(row.geometry.viewport.devicePixelRatio, 2);
            await snapshots(page, directory, row, `${label}-zoom-layout-${urlPath.split('/').at(-1)}`);
            assertLocalReads(row); row.pass = true;
        } catch (error) { row.pass = false; row.error = error.message.slice(0, 500); }
        finally { await context.close(); save(); }
    }
    await endpointControls(browser, directory, label, plan, report, save, baselineControls);
}

async function endpointControls(browser, directory, label, plan, report, save, baselineControls) {
    for (const [kind, urlPath] of [['unchanged-test', plan.testPath], ['course-gate', plan.coursePath]]) {
        const row = {kind, path: urlPath, ...diagnostics()}; report.controls.push(row);
        const {context, page} = await createPage(browser, VIEWPORTS[0], row, plan);
        try {
            const response = await navigate(page, urlPath); row.status = response.status(); assert.equal(row.status, 200);
            await page.waitForLoadState('load', {timeout: 30000}); await paint(page);
            if (kind === 'unchanged-test') {
                await page.waitForFunction(() => typeof state !== 'undefined' && state.items?.length > 0, {timeout: 30000});
                row.test = await page.evaluate(() => ({questionCount: state.items.length, answered: state.answered, correct: state.correct,
                    renderedInputs: document.querySelectorAll('main input').length}));
                assert.ok(row.test.questionCount > 0); assert.equal(row.test.answered, 0);
                row.progressSubmission = 'Not exercised; all stateful requests blocked. Persistence is accepted separately in isolated tests.';
            } else {
                row.courseContentVisible = await page.locator('[data-theory-lesson-content]').isVisible().catch(() => false);
                row.courseContentInServerHtml = (await response.text()).includes('data-theory-lesson-content');
                if (['after', 'supplement-only'].includes(report.mode)) {
                    const previous = baselineControls?.controls?.find(item => item.kind === 'course-gate' && item.path === urlPath);
                    assert.ok(previous?.pass, 'Before course-gate proof required');
                    assert.equal(row.courseContentInServerHtml, previous.courseContentInServerHtml, 'Course gate SSR contract changed');
                    if (typeof previous.courseContentVisible === 'boolean') {
                        assert.equal(row.courseContentVisible, previous.courseContentVisible, 'Course gate visibility changed');
                    } else {
                        row.beforeVisibilityEvidence = 'Not browser-measured before; independent SSR baseline plus byte/text-identical existing gate sources. After visibility is measured live.';
                        assert.ok(previous.gateSourcesUnchanged, 'Existing gate source contract must remain unchanged');
                    }
                    row.gateComparedWithBaseline = true;
                }
            }
            await screenshot(page, directory, row, `${label}-${kind}`); assertLocalReads(row); row.pass = true;
        } catch (error) { row.pass = false; row.error = error.message.slice(0, 500); }
        finally { await context.close(); save(); }
    }
}

// Extra visual evidence for controls/tables that need not happen to fall at
// the three normal scroll positions. This remains a live local page, not a fixture.
async function focusedVisual(browser, directory, label, plan, report, save) {
    const paths = plan.theoryPaths.filter(value => /\/(?:present-perfect-forms|present-perfect-vs-present-perfect-continuous|present-simple)$/.test(value));
    for (const urlPath of paths) for (const viewport of [VIEWPORTS[0], VIEWPORTS[2]]) {
        const row = {kind: 'focused-native-dark', path: urlPath, viewport: viewport.name, ...diagnostics()};
        report.controls.push(row);
        const {context, page} = await createPage(browser, viewport, row, plan);
        try {
            const response = await navigate(page, urlPath); row.status = response.status(); assert.equal(row.status, 200);
            await page.waitForLoadState('load', {timeout: 30000});
            assert.equal(await theme(page, true), true); await paint(page);
            row.geometry = await geometry(page); assert.equal(row.geometry.overflow, false);
            const key = urlPath.split('/').at(-1);
            const table = page.locator('[data-theory-main] table, main table').first();
            if (await table.count()) {
                await table.scrollIntoViewIfNeeded();
                await screenshot(page, directory, row, `${label}-${key}-${viewport.name}-table`);
                row.tables = await tableChecks(page);
            }
            row.nativePractice = await nativePractice(page);
            if (row.nativePractice.checkable) {
                await page.locator('.theory-exercise').first().scrollIntoViewIfNeeded();
                await screenshot(page, directory, row, `${label}-${key}-${viewport.name}-practice-feedback`);
            }
            if (!await table.count() && !row.nativePractice.checkable) {
                const native = page.locator('.theory-native-block').last();
                if (await native.count()) { await native.scrollIntoViewIfNeeded();
                    await screenshot(page, directory, row, `${label}-${key}-${viewport.name}-native`); }
            }
            assert.equal(row.pageErrors.length, 0); assertLocalReads(row); row.pass = true;
        } catch (error) { row.pass = false; row.error = error.message.slice(0, 500); }
        finally { await context.close(); save(); }
    }
}

async function zoomProof(browser, directory, label, plan, report, save, beforeHtml, beforeHttp) {
    const baseSha = '7307750ea503731499fd7d91f04be6442fe3b4c4';
    const normalize = value => value.replace(/\r\n/g, '\n');
    const oldSource = file => normalize(execFileSync('git', ['show', baseSha + ':' + file], {encoding: 'utf8'}));
    const sources = [
        {file: 'resources/views/layouts/catalog-public.blade.php', extract: text => text},
        {file: 'resources/js/catalog-public.js', extract: text => text.slice(text.indexOf('function buildShellRandomShapes() {'), text.indexOf('function randomizeAppBackgroundIcons() {'))},
        {file: 'resources/css/catalog-public.css', extract: text => ['.catalog-shell', '#shell-random-shapes', '#shell-random-shapes > span'].map(selector => {
            const escaped = selector.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const found = text.match(new RegExp(escaped + '\\s*\\{[^}]*\\}')); assert.ok(found, selector); return found[0];
        }).join('\n')},
    ];
    report.backgroundSourceProof = {baseSha, baseDescription: 'Authored M24 commit before M25', sources: sources.map(({file, extract}) => {
        const old = extract(oldSource(file)); const current = extract(normalize(fs.readFileSync(file, 'utf8')));
        const main = extract(normalize(fs.readFileSync(path.join('D:/DEV/htdocs/gramlyze.loc', file), 'utf8')));
        assert.ok(old.length > 0); assert.equal(old, current); assert.equal(current, main);
        return {file, selectedSourceSha256: sha(old), oldEqualsWorktree: true, worktreeEqualsMain: true};
    })};
    assert.ok(oldSource('resources/js/catalog-public.js').includes('window.requestAnimationFrame(buildShellRandomShapes);'));
    const before = JSON.parse(fs.readFileSync(beforeHttp, 'utf8'));
    const nominal = '/theory/formal-english/nominal-style-and-information-density';
    const baselineRow = before.rows.find(row => row.path === nominal); assert.equal(baselineRow?.status, 200);
    const html = fs.readFileSync(beforeHtml, 'utf8'); const d = new JSDOM(html);
    assert.equal(clean(d.window.document.title), baselineRow.metadata.title); d.window.close();
    const fixtureUrl = BASE + '/__m25_private_before_nominal_fixture__';
    const fixtures = new Map([[fixtureUrl, html]]);
    report.beforeFixture = {sourceHtml: path.resolve(beforeHtml), sourceHtmlSha256: sha(html), capturedAt: before.at,
        sourceRow: nominal, sourceStatus: 200, oldAssets: [...new Set(baselineRow.assets)],
        note: 'Read-only browser-route fixture of frozen before HTML plus its actual old local build assets; not a new live baseline or an application endpoint. No authored content substitution in live captures.'};
    const cases = [
        ...plan.performancePaths.flatMap(urlPath => [1, 2, 3].map(repeat => ({kind: 'live-learning-zoom-proof', urlPath, repeat}))),
        ...[1, 2, 3].map(repeat => ({kind: 'frozen-before-zoom-fixture', urlPath: nominal, repeat})),
    ];
    for (const item of cases) {
        const row = {...item, ...diagnostics()}; report.controls.push(row);
        const fixture = item.kind === 'frozen-before-zoom-fixture';
        const {context, page} = await createPage(browser, {width: 720, height: 450, mobile: false, deviceScaleFactor: 2}, row, plan, true, fixture ? fixtures : null);
        try {
            const response = await page.goto(fixture ? fixtureUrl : BASE + item.urlPath, {waitUntil: 'load', timeout: 60000});
            row.status = response.status(); assert.equal(row.status, 200); await paint(page);
            row.geometry = await geometry(page);
            row.classification = await page.evaluate(() => {
                const main = document.querySelector('[data-theory-main]') || document.querySelector('main');
                const outside = [...document.querySelectorAll('body *')].filter(node => {
                    const box = node.getBoundingClientRect(); return node.getClientRects().length && (box.right > innerWidth + 2 || box.left < -2);
                });
                const tableClip = node => {
                    if (!node.closest('table')) return false;
                    let parent = node.parentElement;
                    while (parent && parent !== main) {
                        if (['auto', 'scroll'].includes(getComputedStyle(parent).overflowX) && parent.scrollWidth > parent.clientWidth + 2) return true;
                        parent = parent.parentElement;
                    }
                    return false;
                };
                const describe = node => ({tag: node.tagName, id: node.id, class: String(node.className).slice(0, 120),
                    left: node.getBoundingClientRect().left, right: node.getBoundingClientRect().right});
                const background = outside.filter(node => node.closest('#shell-random-shapes'));
                const clippedTables = outside.filter(node => tableClip(node));
                const learning = outside.filter(node => main?.contains(node) && !tableClip(node));
                return {decorativeOffenders: background.map(describe), locallyClippedTableElements: clippedTables.length,
                    learningOffenders: learning.map(describe), mainBounds: main ? describe(main) : null};
            });
            assert.deepEqual(row.classification.learningOffenders, [], 'Learning content must fit, excluding only real local table scrollers');
            row.tables = await tableChecks(page);
            for (const table of row.tables) if (table.localScroller && table.scrollWidth > table.clientWidth + 2) assert.ok(table.scrolled > 0);
            row.documentOverflowWarning = row.geometry.overflow;
            row.learningLayoutPass = true;
            if (fixture) {
                assert.ok([...new Set(baselineRow.assets)].every(url => row.assets.some(asset => asset.url === url && asset.status === 200)), 'Actual old build assets must load');
            }
            await snapshots(page, directory, row, `${label}-${fixture ? 'before-fixture' : 'live'}-${item.urlPath.split('/').at(-1)}-${item.repeat}`);
            assert.equal(row.pageErrors.length, 0); assertLocalReads(row); row.pass = true;
        } catch (error) { row.pass = false; row.error = error.message.slice(0, 500); }
        finally { await context.close(); save(); }
    }
}

async function run(mode, directory, label, planFile, beforeFile, beforeControlsFile, beforeCourseHtml, performanceFile) {
    assert.ok(['before', 'after', 'baseline-controls', 'focused-visual', 'supplement-only', 'zoom-proof'].includes(mode)); assert.match(label || '', /^[a-z0-9-]+$/);
    const plan = validatePlan(planFile && planFile !== '-' ? JSON.parse(fs.readFileSync(planFile, 'utf8')) : DEFAULT_PLAN);
    const baseline = beforeFile ? JSON.parse(fs.readFileSync(beforeFile, 'utf8')) : null;
    let baselineControls = beforeControlsFile ? JSON.parse(fs.readFileSync(beforeControlsFile, 'utf8')) : null;
    if (['after', 'supplement-only'].includes(mode) && baselineControls?.rows && baselineControls.label === 'before-v2') {
        const row = baselineControls.rows.find(item => item.path === plan.coursePath);
        assert.equal(row?.status, 200); assert.ok(beforeCourseHtml, 'Independent saved course HTML required');
        const html = fs.readFileSync(beforeCourseHtml, 'utf8'); const dom = new JSDOM(html);
        assert.equal(clean(dom.window.document.title), row.metadata.title);
        const sourceFiles = ['resources/views/courses/theory-lesson.blade.php', 'public/js/theory-course-progress.js'];
        const gateSources = sourceFiles.map(file => {
            const old = execFileSync('git', ['show', '7307750ea503731499fd7d91f04be6442fe3b4c4:' + file], {encoding: 'utf8'}).replace(/\r\n/g, '\n');
            const now = fs.readFileSync(file, 'utf8').replace(/\r\n/g, '\n');
            return {file, oldSha256: sha(old), currentSha256: sha(now), unchanged: old === now};
        });
        baselineControls = {base: BASE, mode: 'independent-before-http', capturedAt: baselineControls.at, plan,
            controls: [{kind: 'course-gate', path: plan.coursePath, status: row.status, pass: true,
                courseContentInServerHtml: !!dom.window.document.querySelector('[data-theory-lesson-content]'),
                courseContentVisible: null, gateSourcesUnchanged: gateSources.every(source => source.unchanged), gateSources,
                sourceHtmlSha256: sha(html), sourceProof: path.resolve(beforeControlsFile)}]};
        dom.window.close();
    }
    if (['after', 'supplement-only'].includes(mode)) {
        assert.equal(baseline?.base, BASE); assert.equal(baseline?.mode, 'before');
        assert.equal(baselineControls?.base, BASE); assert.ok(['baseline-controls', 'independent-before-http'].includes(baselineControls?.mode));
        assert.deepEqual(baseline.plan, plan); assert.deepEqual(baselineControls.plan, plan);
    }
    fs.mkdirSync(directory, {recursive: true}); const file = path.join(directory, label + '-browser.json');
    const report = {schema: 'gramlyze-m25-real-browser-v1', startedAt: new Date().toISOString(), base: BASE, mode, label, plan,
        scope: mode === 'zoom-proof'
            ? 'Nine live local learning-layout checks plus three explicitly labelled frozen-before SSR fixtures. Full-document decorative overflow remains a warning; this is not an overall full-site PASS.'
            : 'Real gramlyze.loc SSR/browser only, no DOM content substitution, no production requests, no answer POST, no course unlock.',
        courseBeforeProof: baselineControls?.controls?.find(item => item.kind === 'course-gate') || null,
        performance: [], lessons: [], controls: []};
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    const save = () => fs.writeFileSync(file, JSON.stringify(report, null, 2));
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true,
        ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    report.browser = browser.version();
    try {
        if (mode === 'baseline-controls') await endpointControls(browser, directory, label, plan, report, save);
        else if (mode === 'focused-visual') await focusedVisual(browser, directory, label, plan, report, save);
        else if (mode === 'supplement-only') await supplementary(browser, directory, label, plan, report, save, baselineControls);
        else if (mode === 'zoom-proof') await zoomProof(browser, directory, label, plan, report, save, beforeCourseHtml, beforeControlsFile);
        else {
            if (performanceFile) {
                assert.equal(mode, 'after', 'Saved performance reuse is only for after UI acceptance');
                const proof = JSON.parse(fs.readFileSync(performanceFile, 'utf8'));
                assert.equal(proof.base, BASE); assert.equal(proof.mode, 'after'); assert.deepEqual(proof.plan, plan);
                assert.equal(proof.performance.length, 9); assert.ok(proof.performance.every(row => row.pass && row.measurement));
                report.performance = proof.performance;
                report.performanceProof = {file: path.resolve(performanceFile), startedAt: proof.startedAt,
                    note: 'The original nine clean measurements finished before other diagnostics began. UI-only rerun fixes a diagnostic locator; no remeasurement or padded performance count.'};
                save();
            } else await performanceCapture(browser, directory, label, plan, report, save);
            await realLessons(browser, directory, label, mode, plan, report, save, baseline);
            if (mode === 'after') {
                await supplementary(browser, directory, label, plan, report, save, baselineControls);
                await focusedVisual(browser, directory, label + '-focused', plan, report, save);
            }
        }
    } finally {
        await browser.close(); report.finishedAt = new Date().toISOString();
        report.counts = {uniqueRepresentativePages: new Set(report.lessons.map(row => row.path)).size,
            completedViewportScenarios: report.lessons.filter(row => row.pass).length,
            plannedViewportScenarios: ['baseline-controls', 'focused-visual', 'supplement-only', 'zoom-proof'].includes(mode) ? 0 : plan.theoryPaths.length * (mode === 'after' ? VIEWPORTS.length : 1),
            completedThemeChecks: report.lessons.reduce((sum, row) => sum + row.themes.length, 0),
            performanceRuns: report.performance.length, supplementaryScenarios: report.controls.length,
            liveLearningZoomCases: report.controls.filter(row => row.kind === 'live-learning-zoom-proof').length,
            beforeFixtureCases: report.controls.filter(row => row.kind === 'frozen-before-zoom-fixture').length,
            documentOverflowWarnings: report.controls.filter(row => row.documentOverflowWarning).length,
            screenshots: [...report.lessons, ...report.controls].reduce((sum, row) => sum + row.screenshots.length, 0)};
        report.pass = [...report.lessons, ...report.controls, ...report.performance].every(row => row.pass)
            && report.counts.completedViewportScenarios === report.counts.plannedViewportScenarios;
        save();
    }
    console.log(JSON.stringify({file, pass: report.pass, counts: report.counts}));
    if (!report.pass) process.exitCode = 1;
    return report;
}

module.exports = {run, validatePlan, decision, DEFAULT_PLAN, createPage, geometry};
if (require.main === module) run(...process.argv.slice(2)).catch(error => { console.error(error.message); process.exitCode = 1; });
