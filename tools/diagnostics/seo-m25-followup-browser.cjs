'use strict';
// Read-only, live-local sidebar acceptance. No content substitution, DB apply,
// stateful requests, production requests or performance-score simulation.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const {createPage, DEFAULT_PLAN} = require('./seo-m25-browser.cjs');
const BASE = 'http://gramlyze.loc';
const PATHS = [
    '/theory/tenses/past-simple-vs-past-continuous',
    '/theory/tenses/present-perfect/present-perfect-forms',
    '/theory/clauses-and-linking-words/linking-words-reason-result-contrast',
    '/theory/reported-speech/reported-statements',
    '/theory/present-simple',
];
const sha = text => crypto.createHash('sha256').update(text).digest('hex');
const compact = text => text.replace(/\s+/g, ' ').trim();
const paint = page => page.evaluate(() => new Promise(done => requestAnimationFrame(() => requestAnimationFrame(done))));
const visible = locator => locator.filter({visible: true});
const diagnostics = () => ({blocked: [], pageErrors: [], console: [], failures: [], httpErrors: [], assets: [], screenshots: []});

function authoredProof(html) {
    const dom = new JSDOM(html); const d = dom.window.document;
    const main = d.querySelector('[data-theory-main]'); assert.ok(main, 'Actual learning main required');
    const ids = [...main.querySelectorAll('[id]')].map(node => node.id);
    const links = [...main.querySelectorAll('a[href]')].map(node => ({href: node.getAttribute('href'), text: compact(node.textContent)}));
    const fields = [...main.querySelectorAll('input,select,textarea,button')].map(node => ({tag: node.tagName,
        type: node.getAttribute('type'), name: node.getAttribute('name'), text: compact(node.textContent)}));
    main.querySelectorAll('[data-theory-ui],script,style,template').forEach(node => node.remove());
    const text = compact(main.textContent);
    const proof = {title: d.title, canonical: d.querySelector('link[rel="canonical"]')?.getAttribute('href'),
        authoredText: text, authoredTextSha256: sha(text), ids, links, fields,
        tables: main.querySelectorAll('table').length, nativePractice: main.querySelectorAll('.theory-exercise').length};
    dom.window.close(); return proof;
}

async function geometry(page) {
    return page.evaluate(() => {
        const describe = node => {
            if (!node) return null; const b = node.getBoundingClientRect(), s = getComputedStyle(node);
            return {top: b.top, bottom: b.bottom, left: b.left, right: b.right, height: b.height, width: b.width,
                clientHeight: node.clientHeight, scrollHeight: node.scrollHeight, scrollTop: node.scrollTop,
                position: s.position, display: s.display, overflowY: s.overflowY, border: s.borderWidth};
        };
        const shell = document.querySelector('[data-theory-sidebar-shell]');
        const aside = document.querySelector('[data-theory-aside]');
        const scrolls = [...(aside?.querySelectorAll('*') || [])].filter(node => node.getClientRects().length
            && ['auto', 'scroll'].includes(getComputedStyle(node).overflowY) && node.scrollHeight > node.clientHeight + 2);
        return {scrollY, viewport: {width: innerWidth, height: innerHeight}, header: describe(document.getElementById('site-header')),
            shell: describe(shell), map: describe(document.querySelector('[data-theory-sidebar]')),
            mapScroll: describe(aside?.querySelector('[data-theory-sidebar-scroll]')),
            lesson: describe(document.querySelector('[data-theory-sidebar-panel="lesson"]')),
            topics: describe(document.querySelector('[data-theory-sidebar-panel="topics"]')),
            scrolls: scrolls.map(node => ({tag: node.tagName, class: String(node.className).slice(0, 120), ...describe(node)})),
            horizontalLearningOverflow: [...document.querySelectorAll('[data-theory-main] *')].filter(node => {
                const b = node.getBoundingClientRect(); if (!node.getClientRects().length || (b.right <= innerWidth + 2 && b.left >= -2)) return false;
                let parent = node.parentElement;
                while (parent && !parent.matches('[data-theory-main]')) {
                    if (node.closest('table') && ['auto', 'scroll'].includes(getComputedStyle(parent).overflowX)
                        && parent.scrollWidth > parent.clientWidth + 2) return false;
                    parent = parent.parentElement;
                }
                return true;
            }).slice(0, 10).map(node => ({tag: node.tagName, class: String(node.className).slice(0, 80)}))};
    });
}

async function shot(page, directory, row, name) {
    if (row.javaScriptEnabled === false) await page.waitForTimeout(34); else await paint(page);
    const file = path.join(directory, name + '.png');
    assert.ok(!fs.existsSync(file), 'Never overwrite evidence');
    await page.screenshot({path: file, animations: 'disabled'}); row.screenshots.push(path.basename(file));
}
async function clickability(locator) {
    return locator.evaluate(node => { const b = node.getBoundingClientRect();
        const hit = document.elementFromPoint(b.x + b.width / 2, b.y + b.height / 2);
        return b.top >= 0 && b.bottom <= innerHeight && (hit === node || node.contains(hit)); });
}
async function settle(page) { await paint(page); await page.waitForTimeout(260); }
async function scrollStopped(page) {
    let previous = await page.evaluate(() => scrollY), stable = 0;
    for (let samples = 1; samples <= 45; samples++) {
        await page.waitForTimeout(60); const current = await page.evaluate(() => scrollY);
        stable = Math.abs(current - previous) < .5 ? stable + 1 : 0;
        previous = current;
        if (stable >= 4) return {samples, finalScrollY: current};
    }
    throw new Error('Smooth anchor scrolling did not settle within 2.7 seconds');
}
async function darkTheme(page) {
    let button = visible(page.locator('.site-header button[\\@click="toggleTheme"]')).first();
    let openedMenu = false;
    if (!await button.count()) {
        await page.getByRole('button', {name: 'Меню', exact: true}).click(); openedMenu = true;
        button = visible(page.locator('.site-header button[\\@click="toggleTheme"]')).first();
    }
    await button.click(); await page.waitForFunction(() => document.documentElement.classList.contains('dark'));
    if (openedMenu) await page.getByRole('button', {name: 'Меню', exact: true}).click();
}
async function readyMap(page, mobile) {
    const root = mobile ? '[data-theory-mobile-nav-panel]' : '[data-theory-desktop-navigation-loader]';
    await page.waitForFunction(selector => document.querySelector(selector)?.getAttribute('aria-busy') === 'false', root);
    // Existing sidebar autoscroll has a final scheduled pass at 450ms.
    // Wait for it before testing a real user's independent wheel action.
    await page.waitForTimeout(600); await paint(page);
}
async function wheel(page, locator) {
    const initial = await locator.evaluate(node => ({top: node.scrollTop, max: node.scrollHeight - node.clientHeight}));
    const box = await locator.boundingBox(); assert.ok(box && box.height > 0);
    const y = await page.evaluate(() => scrollY);
    await page.mouse.move(box.x + box.width / 2, Math.min(box.y + box.height / 2, (await page.viewportSize()).height - 5));
    await page.mouse.wheel(0, initial.top > initial.max / 2 ? -400 : 400); await settle(page);
    const end = await locator.evaluate(node => node.scrollTop);
    if (initial.max > 5) assert.notEqual(end, initial.top, 'Wheel must scroll the active topic list');
    assert.equal(await page.evaluate(() => scrollY), y, 'Topic wheel must not unexpectedly move the document');
    return {before: initial, after: end, documentScrollY: y};
}

async function desktop(page, directory, row, label, hasToc) {
    const shell = page.locator('[data-theory-sidebar-shell]'); assert.equal(await shell.count(), 1);
    row.initialGeometry = await geometry(page);
    assert.ok(row.initialGeometry.shell.height >= 240, 'Desktop sidebar must have usable height');
    assert.ok(row.initialGeometry.shell.bottom <= row.initialGeometry.viewport.height + 2, 'Sidebar bottom must fit viewport');
    const tabs = page.locator('[data-theory-sidebar-tab]');
    if (hasToc) {
        const lesson = page.locator('[data-theory-sidebar-tab="lesson"]');
        const topics = page.locator('[data-theory-sidebar-tab="topics"]');
        assert.equal(await lesson.getAttribute('aria-selected'), 'true', 'Lesson contents is default');
        assert.equal(await topics.getAttribute('aria-selected'), 'false');
        const first = page.locator('[data-theory-sidebar-panel="lesson"] a').first();
        assert.ok(await clickability(first), 'First lesson link must be unobstructed at start');
        await lesson.focus(); await page.keyboard.press('ArrowRight'); await settle(page);
        assert.equal(await topics.getAttribute('aria-selected'), 'true', 'Right arrow selects Topics');
        assert.equal(await topics.evaluate(node => document.activeElement === node), true);
        await page.keyboard.press('ArrowLeft'); await settle(page);
        assert.equal(await lesson.getAttribute('aria-selected'), 'true', 'Left arrow selects contents');
        await topics.click();
    } else assert.equal(await tabs.count(), 0, 'No empty tab controls on pages without a lesson TOC');
    await readyMap(page, false);
    const input = page.locator('[data-theory-aside] [data-theory-sidebar-search-input]');
    assert.ok(await input.isVisible(), 'Topics search visible');
    row.topicGeometry = await geometry(page);
    assert.ok(row.topicGeometry.mapScroll.clientHeight >= 240, 'Topic tree must receive at least 240px at desktop size');
    const scroller = page.locator('[data-theory-aside] [data-theory-sidebar-scroll]');
    row.wheel = await wheel(page, scroller);
    await input.fill('present'); await settle(page);
    row.searchVisibleLabels = await visible(page.locator('[data-theory-aside] [data-theory-sidebar-highlight]')).allTextContents();
    assert.ok(row.searchVisibleLabels.some(text => /present/i.test(text)), 'Search must retain matching topics');
    const clear = page.locator('[data-theory-aside] [data-theory-sidebar-search-clear]');
    await clear.click(); await settle(page); assert.equal(await input.inputValue(), '');
    const expandedBranch = visible(page.locator('[data-theory-aside] .theory-nav-toggle')).first();
    const expanded = await expandedBranch.getAttribute('aria-expanded');
    await expandedBranch.click(); await settle(page);
    assert.notEqual(await expandedBranch.getAttribute('aria-expanded'), expanded, 'Tree expand/collapse remains usable');
    await expandedBranch.click(); await settle(page);
    const collapse = page.locator('[data-theory-sidebar-collapse]'); assert.equal(await collapse.count(), 1);
    await collapse.click(); await settle(page);
    assert.equal(await page.locator('[data-theory-layout]').getAttribute('data-collapsed'), 'true');
    await collapse.click(); await settle(page);
    assert.equal(await page.locator('[data-theory-layout]').getAttribute('data-collapsed'), 'false');
    if (hasToc) await page.locator('[data-theory-sidebar-tab="lesson"]').click();
    await settle(page); await shot(page, directory, row, label + '-start');
    await page.evaluate(() => scrollTo({top: 900, behavior: 'instant'})); await settle(page);
    row.scrolledGeometry = await geometry(page);
    assert.ok(row.scrolledGeometry.shell.top >= row.scrolledGeometry.header.bottom - 1, 'Sticky sidebar must stay below real header');
    assert.ok(row.scrolledGeometry.shell.bottom <= row.scrolledGeometry.viewport.height + 2, 'Sticky sidebar must fit viewport');
    assert.equal(row.scrolledGeometry.shell.position, 'sticky', 'CSS sticky, not fixed overlay');
    await shot(page, directory, row, label + '-scrolled');
    if (hasToc) {
        const last = page.locator('[data-theory-sidebar-panel="lesson"] a').last();
        await last.scrollIntoViewIfNeeded(); await settle(page); assert.ok(await clickability(last));
        const href = await last.getAttribute('href'); await last.click(); row.anchorScroll = await scrollStopped(page);
        const target = page.locator('[id="' + href.slice(1) + '"]');
        const targetTop = await target.evaluate(node => node.getBoundingClientRect().top);
        const headerBottom = await page.locator('#site-header').evaluate(node => node.getBoundingClientRect().bottom);
        row.lastAnchor = {href, targetTop, headerBottom};
        assert.ok(targetTop >= headerBottom - 2, 'Anchor title must not be hidden behind header');
        assert.ok(targetTop < headerBottom + 100, 'Last anchor must land near header');
        await shot(page, directory, row, label + '-last-anchor');
    }
}

async function mobile(page, directory, row, label, hasToc) {
    assert.equal(await page.locator('[data-theory-aside]').isVisible(), false, 'No desktop sidebar overlay on mobile');
    const toggle = page.locator('[data-theory-mobile-nav-toggle]'); await toggle.click();
    await readyMap(page, true);
    assert.equal(await toggle.getAttribute('aria-expanded'), 'true');
    const panel = page.locator('[data-theory-mobile-nav-panel]');
    assert.ok(await panel.isVisible());
    const input = panel.locator('[data-theory-sidebar-search-input]');
    await input.fill('present'); await settle(page);
    assert.ok((await visible(panel.locator('[data-theory-sidebar-highlight]')).allTextContents()).some(text => /present/i.test(text)));
    await panel.locator('[data-theory-sidebar-search-clear]').click(); await toggle.click(); await settle(page);
    assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
    if (hasToc) {
        const disclosure = page.locator('.theory-mobile-toc'); assert.equal(await disclosure.count(), 1);
        await disclosure.locator('summary').click(); await settle(page);
        const last = disclosure.locator('a').last(); await last.scrollIntoViewIfNeeded(); assert.ok(await clickability(last));
        const href = await last.getAttribute('href'); await last.click(); row.anchorScroll = await scrollStopped(page);
        row.lastAnchor = {href, top: await page.locator('[id="' + href.slice(1) + '"]').evaluate(node => node.getBoundingClientRect().top)};
    }
    row.geometry = await geometry(page); assert.deepEqual(row.geometry.horizontalLearningOverflow, []);
    await shot(page, directory, row, label + '-mobile');
}

async function noScriptLegacy(browser, directory, label, plan, report, before, save) {
    const urlPath = PATHS[3]; const row = {kind: 'legacy-no-js', urlPath, width: 1366, height: 768, ...diagnostics()};
    report.controls.push(row);
    const {context, page} = await createPage(browser, {width: 1366, height: 768, mobile: false}, row, plan, false);
    try {
        const response = await page.goto(BASE + urlPath, {waitUntil: 'load', timeout: 60000});
        row.status = response.status(); assert.equal(row.status, 200);
        const proof = authoredProof(await response.text());
        const old = before.authored.find(old => old.path === urlPath); assert.ok(old);
        assert.equal(proof.authoredTextSha256, old.authoredTextSha256, 'No-JS authored text parity');
        assert.ok(await page.locator('[data-theory-main]').isVisible());
        const fallback = page.locator('[data-theory-sidebar-shell] noscript a');
        assert.equal(await fallback.count(), 1, 'Sidebar has a normal no-JS navigation fallback');
        row.fallbackHref = await fallback.getAttribute('href'); assert.ok(row.fallbackHref.includes('/theory'));
        row.geometry = await geometry(page);
        assert.deepEqual(row.geometry.horizontalLearningOverflow, []);
        await shot(page, directory, row, label + '-reported-statements-no-js');
        assert.equal(row.httpErrors.filter(item => new URL(item.url).origin === BASE).length, 0);
        assert.equal(row.failures.filter(item => new URL(item.url).origin === BASE && !item.expectedScriptDisabled).length, 0);
        row.pass = true;
    } catch (error) { row.pass = false; row.error = error.message.slice(0, 1000); }
    finally { await context.close(); save(); }
}

async function run(mode, directory, label, beforeFile) {
    assert.ok(['before', 'after', 'after-smoke'].includes(mode)); assert.match(label, /^[a-z0-9-]+$/);
    fs.mkdirSync(directory, {recursive: true}); const target = path.join(directory, label + '.json');
    assert.ok(!fs.existsSync(target));
    const before = beforeFile ? JSON.parse(fs.readFileSync(beforeFile, 'utf8')) : null;
    if (mode !== 'before') assert.equal(before?.mode, 'before');
    const plan = {...DEFAULT_PLAN, theoryPaths: [...new Set([...DEFAULT_PLAN.theoryPaths, ...PATHS])]};
    const report = {schema: 'gramlyze-sidebar-followup-live-v1', startedAt: new Date().toISOString(), mode, base: BASE,
        conditions: 'Fresh read-only guest contexts; local real server; no performance measurements; no fixtures; Google font failures recorded.', paths: PATHS,
        rows: [], controls: [], authored: []};
    const save = () => fs.writeFileSync(target, JSON.stringify(report, null, 2));
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    const cases = mode === 'after-smoke' ? [{urlPath: PATHS[0], width: 1366, height: 768, dark: false}]
        : mode === 'before' ? PATHS.map(urlPath => ({urlPath, width: 1366, height: 768, dark: false}))
        : PATHS.flatMap(urlPath => (urlPath === PATHS[0] ? [{width: 1440, height: 900}, {width: 1366, height: 768}, {width: 390, height: 844}, {width: 320, height: 800}]
            : [{width: 1366, height: 768}, {width: 320, height: 800}]).flatMap(viewport => [false, true].map(dark => ({urlPath, ...viewport, dark}))));
    try {
        for (const item of cases) {
            const row = {...item, ...diagnostics()}; report.rows.push(row);
            const {context, page} = await createPage(browser, {...item, mobile: item.width < 1024}, row, plan);
            try {
                const response = await page.goto(BASE + item.urlPath, {waitUntil: 'load', timeout: 60000});
                row.status = response.status(); assert.equal(row.status, 200);
                const proof = authoredProof(await response.text());
                if (!report.authored.some(proof => proof.path === item.urlPath)) report.authored.push({path: item.urlPath, ...proof});
                if (mode !== 'before') { const old = before.authored.find(old => old.path === item.urlPath); assert.ok(old);
                    for (const key of ['authoredText', 'title', 'canonical', 'ids', 'links', 'fields', 'tables', 'nativePractice']) assert.deepEqual(proof[key], old[key], 'Preserve ' + key);
                    row.authoredParity = true;
                }
                if (item.dark) {
                    await darkTheme(page); await settle(page);
                    assert.equal(await page.locator('html').evaluate(node => node.classList.contains('dark')), true);
                }
                await settle(page);
                const key = label + '-' + item.urlPath.split('/').at(-1) + '-' + item.width + '-' + (item.dark ? 'dark' : 'light');
                if (mode === 'before') await shot(page, directory, row, key);
                else {
                    const hasToc = await page.locator('.theory-mobile-toc').count() > 0;
                    if (item.width >= 1024) await desktop(page, directory, row, key, hasToc);
                    else await mobile(page, directory, row, key, hasToc);
                    assert.deepEqual((await geometry(page)).horizontalLearningOverflow, []);
                }
                assert.equal(row.pageErrors.length, 0, 'No browser runtime errors');
                assert.equal(row.httpErrors.filter(e => new URL(e.url).origin === BASE).length, 0);
                assert.equal(row.failures.filter(e => new URL(e.url).origin === BASE).length, 0);
                row.pass = true;
            } catch (error) { row.pass = false; row.error = error.message.slice(0, 1000); }
            finally { await context.close(); save(); }
        }
        if (mode === 'after') await noScriptLegacy(browser, directory, label, plan, report, before, save);
    } finally { await browser.close(); report.finishedAt = new Date().toISOString();
        report.counts = {uniquePages: new Set(report.rows.map(row => row.urlPath)).size, scenarios: report.rows.length,
            passed: report.rows.filter(row => row.pass).length, extraControls: report.controls.length,
            extraControlsPassed: report.controls.filter(row => row.pass).length,
            screenshots: [...report.rows, ...report.controls].reduce((n, row) => n + row.screenshots.length, 0)};
        report.pass = [...report.rows, ...report.controls].every(row => row.pass); save();
    }
    console.log(JSON.stringify({file: path.resolve(target), pass: report.pass, counts: report.counts}));
    if (!report.pass) process.exitCode = 1;
    return report;
}
module.exports = {run, authoredProof};
if (require.main === module) run(...process.argv.slice(2)).catch(error => {console.error(error.message); process.exitCode = 1;});
