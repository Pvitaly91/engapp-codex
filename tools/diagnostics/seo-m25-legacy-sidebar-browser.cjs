'use strict';
// Real-local, read-only legacy sidebar acceptance. No HTML substitution,
// database writes, stateful requests, production navigation or fake metrics.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {createPage, DEFAULT_PLAN} = require('./seo-m25-browser.cjs');
const {authoredProof} = require('./seo-m25-followup-browser.cjs');
const BASE = 'http://gramlyze.loc';
const PATHS = [
    '/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives',
    '/theory/tenses/present-perfect/present-perfect-forms',
    '/theory/clauses-and-linking-words/linking-words-reason-result-contrast',
    '/theory/present-simple',
];
const visible = locator => locator.filter({visible: true});
// Exact source class of the x-for tokenBank buttons in the unchanged
// text-block-practice-questions component. BEFORE already demonstrates8/11
// instances after its normal Math.random question selection.
const RUNTIME_TOKEN_BUTTON_CLASS = 'rounded-md border border-border/70 bg-background px-3 py-1.5 text-sm text-foreground/85 transition hover:border-primary/40 hover:bg-primary/5';
const diagnostics = () => ({blocked: [], pageErrors: [], console: [], failures: [], httpErrors: [], assets: [], screenshots: []});
const paint = page => page.evaluate(() => new Promise(done => requestAnimationFrame(() => requestAnimationFrame(done))));
const settle = async page => { await paint(page); await page.waitForTimeout(300); };

function difference(expected, observed) {
    if (JSON.stringify(expected) === JSON.stringify(observed)) return null;
    const a = Array.from(JSON.stringify(expected) ?? 'undefined'), b = Array.from(JSON.stringify(observed) ?? 'undefined');
    let offset = 0;
    while (offset < Math.min(a.length, b.length) && a[offset] === b[offset]) offset++;
    return {firstDifferentCodePoint: offset, expectedLength: a.length, observedLength: b.length,
        expectedContext: a.slice(Math.max(0, offset - 100), offset + 180).join(''),
        observedContext: b.slice(Math.max(0, offset - 100), offset + 180).join('')};
}

async function mainPresentation(page) {
    return page.evaluate(() => {
        const main = document.querySelector('[data-theory-main]');
        if (!main) throw new Error('Learning main required');
        const box = main.getBoundingClientRect();
        const properties = ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'color', 'backgroundColor',
            'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 'borderTopWidth', 'borderRightWidth',
            'borderBottomWidth', 'borderLeftWidth', 'borderTopStyle', 'borderRightStyle', 'borderBottomStyle',
            'borderLeftStyle', 'borderTopColor', 'borderRightColor', 'borderBottomColor', 'borderLeftColor',
            'borderTopLeftRadius', 'borderTopRightRadius', 'borderBottomRightRadius', 'borderBottomLeftRadius',
            'boxShadow', 'display', 'gap'];
        const style = node => Object.fromEntries(properties.map(key => [key, getComputedStyle(node)[key]]));
        const nodes = [...main.querySelectorAll('h1,h2,h3,.theory-hero,.theory-rules,.theory-rules>article,.theory-example,.theory-section-card,.theory-section-header,.theory-section-body,.theory-rich-section,.theory-rich-section-body,.theory-html-section,.theory-item,table,th,td,.theory-native-block input,.theory-native-block button')]
            .filter(node => !node.closest('[data-theory-ui]'));
        return {mainStyle: style(main), mainBounds: {left: box.left, top: box.top, width: box.width, height: box.height},
            elements: nodes.map((node, index) => {const r = node.getBoundingClientRect();
                return {index, tag: node.tagName, id: node.id, class: String(node.className), style: style(node),
                    relativeBounds: {left: r.left - box.left, top: r.top - box.top, width: r.width, height: r.height}};})};
    });
}

function comparablePresentation(proof) {
    // Main width/wrapping intentionally follows restored sidebar width. The
    // concrete measured bounds stay in evidence; only declared styles compare.
    const stable = proof.elements.filter(node => !(node.tag === 'BUTTON' && node.class === RUNTIME_TOKEN_BUTTON_CLASS));
    const variants = [...new Set(proof.elements.filter(node => node.tag === 'BUTTON' && node.class === RUNTIME_TOKEN_BUTTON_CLASS)
        .map(({tag, id, class: classes, style}) => JSON.stringify({tag, id, class: classes, style})))].sort().map(value => JSON.parse(value));
    return {mainStyle: proof.mainStyle,
        elements: stable.map(({tag, id, class: classes, style}) => ({tag, id, class: classes, style})),
        runtimeTokenBankStyleVariants: variants};
}

async function geometry(page) {
    return page.evaluate(() => {
        const describe = node => {
            if (!node) return null;
            const b = node.getBoundingClientRect(), s = getComputedStyle(node);
            return {top: b.top, bottom: b.bottom, left: b.left, right: b.right, width: b.width, height: b.height,
                clientHeight: node.clientHeight, scrollHeight: node.scrollHeight, scrollTop: node.scrollTop,
                position: s.position, display: s.display, overflowY: s.overflowY,
                painted: node.checkVisibility?.({contentVisibilityAuto: true}) ?? node.getClientRects().length > 0};
        };
        const aside = document.querySelector('[data-theory-aside]');
        return {scrollY, viewport: {width: innerWidth, height: innerHeight}, header: describe(document.querySelector('#site-header')),
            aside: describe(aside), main: describe(document.querySelector('[data-theory-main]')),
            map: describe(aside?.querySelector('[data-theory-sidebar]')),
            tree: describe(aside?.querySelector('[data-theory-sidebar-scroll]')),
            toc: describe(aside?.querySelector('[data-theory-toc-pin-root]')),
            tocCard: describe(aside?.querySelector('[data-theory-toc-card]')),
            fullDocumentHorizontalOverflow: Math.max(document.documentElement.scrollWidth, document.body.scrollWidth) - innerWidth};
    });
}

async function shot(page, directory, row, key, noJs = false) {
    if (noJs) await page.waitForTimeout(35); else await paint(page);
    const target = path.join(directory, key + '.png'); assert.ok(!fs.existsSync(target), 'Preserve existing evidence');
    await page.screenshot({path: target, animations: 'disabled'}); row.screenshots.push(path.basename(target));
}

async function ready(page, mobile) {
    const selector = mobile ? '[data-theory-mobile-nav-panel]' : '[data-theory-desktop-navigation-loader]';
    await page.waitForFunction(selector => document.querySelector(selector)?.getAttribute('aria-busy') === 'false', selector);
    await page.waitForTimeout(600); await paint(page);
}

async function darkTheme(page) {
    let toggle = visible(page.locator('.site-header button[\\@click="toggleTheme"]')).first(), open = false;
    if (!await toggle.count()) {
        await page.getByRole('button', {name: 'Меню', exact: true}).click(); open = true;
        toggle = visible(page.locator('.site-header button[\\@click="toggleTheme"]')).first();
    }
    await toggle.click(); await page.waitForFunction(() => document.documentElement.classList.contains('dark'));
    if (open) await page.getByRole('button', {name: 'Меню', exact: true}).click();
}

async function scrollStopped(page) {
    let last = await page.evaluate(() => scrollY), stable = 0;
    for (let n = 1; n <= 50; n++) {
        await page.waitForTimeout(60); const current = await page.evaluate(() => scrollY);
        stable = Math.abs(last - current) < .5 ? stable + 1 : 0; last = current;
        if (stable >= 4) return {samples: n, finalScrollY: current};
    }
    throw new Error('Actual anchor scroll did not settle');
}

async function hitTest(locator, row, field = 'lastLinkHit') {
    const result = await locator.evaluate(node => {
        const b = node.getBoundingClientRect(), hit = document.elementFromPoint(b.x + b.width / 2, b.y + b.height / 2);
        return {top: b.top, bottom: b.bottom, left: b.left, right: b.right, centerX: b.x + b.width / 2, centerY: b.y + b.height / 2,
            viewportHeight: innerHeight, documentScrollY: scrollY, hitTag: hit?.tagName, hitId: hit?.id,
            hitClass: hit ? String(hit.className) : null, targetReceivesHit: node === hit || node.contains(hit),
            fullyWithinViewport: b.top >= 0 && b.bottom <= innerHeight};
    });
    if (row) row[field] = result;
    return result.fullyWithinViewport && result.targetReceivesHit;
}

async function desktop(page, directory, row, key, hasToc) {
    const aside = page.locator('[data-theory-aside]');
    assert.equal(await page.locator('[data-theory-sidebar-shell]').count(), 0, 'No redesigned shared sidebar shell');
    assert.equal(await page.locator('[data-theory-sidebar-card]').count(), 0, 'No two-card redesign hooks');
    await ready(page, false); row.expanded = await geometry(page);
    assert.equal(Math.round(row.expanded.aside.width), row.width >= 1280 ? 410 : 390, 'Original expanded sidebar width');
    assert.ok(row.expanded.map.height > row.expanded.viewport.height * .7, 'Original full-height topic map');
    assert.ok(row.expanded.tree.clientHeight > 200, 'Full legacy tree is usable');
    assert.ok(await aside.locator('.theory-nav-icon').first().isVisible(), 'Original topic icons restored');
    const scroller = aside.locator('[data-theory-sidebar-scroll]');
    const oldTop = await scroller.evaluate(node => node.scrollTop), max = await scroller.evaluate(node => node.scrollHeight - node.clientHeight);
    const box = await scroller.boundingBox(); assert.ok(box);
    await page.mouse.move(box.x + box.width / 2, Math.min(box.y + box.height / 2, row.height - 8));
    const docTop = await page.evaluate(() => scrollY);
    await page.mouse.wheel(0, oldTop > max / 2 ? -400 : 400); await settle(page);
    row.topicWheel = {before: oldTop, after: await scroller.evaluate(node => node.scrollTop), documentBefore: docTop, documentAfter: await page.evaluate(() => scrollY)};
    if (max > 5) assert.notEqual(row.topicWheel.before, row.topicWheel.after, 'Legacy map responds to wheel');
    assert.equal(row.topicWheel.documentAfter, docTop, 'Tree wheel stays inside map');
    const input = aside.locator('[data-theory-sidebar-search-input]'); await input.fill('present'); await settle(page);
    assert.ok((await visible(aside.locator('[data-theory-sidebar-highlight]')).allTextContents()).some(text => /present/i.test(text)));
    await aside.locator('[data-theory-sidebar-search-clear]').click(); await settle(page);
    const collapse = aside.locator('button[\\@click="toggleTheorySidebar()"]'); assert.equal(await collapse.count(), 1);
    await collapse.focus(); await collapse.press('Enter'); await settle(page);
    assert.equal(await page.locator('[data-theory-layout]').getAttribute('data-collapsed'), 'true');
    row.collapsed = await geometry(page); assert.equal(Math.round(row.collapsed.aside.width), 116, 'Original collapsed sidebar width');
    await collapse.press('Enter'); await settle(page);
    assert.equal(await page.locator('[data-theory-layout]').getAttribute('data-collapsed'), 'false');
    await shot(page, directory, row, key + '-start');
    await page.evaluate(() => scrollTo({top: 1200, behavior: 'instant'})); await settle(page);
    row.scrolled = await geometry(page);
    if (hasToc) {
        const card = aside.locator('[data-theory-toc-card]'); assert.equal(await card.count(), 1);
        assert.ok(row.scrolled.tocCard.top >= row.scrolled.header.bottom - 2, 'Pinned TOC below header');
        assert.ok(row.scrolled.tocCard.bottom <= row.height + 2, 'Pinned TOC fits viewport');
        const last = card.locator('a[href^="#"]').last(); assert.ok(await last.count());
        await last.scrollIntoViewIfNeeded(); await settle(page); assert.ok(await hitTest(last, row), 'Last TOC link receives click');
        await shot(page, directory, row, key + '-pinned');
        const href = await last.getAttribute('href'); await last.click(); row.anchorScroll = await scrollStopped(page);
        const top = await page.locator('[id="' + href.slice(1) + '"]').evaluate(node => node.getBoundingClientRect().top);
        const headerBottom = await page.locator('#site-header').evaluate(node => node.getBoundingClientRect().bottom);
        row.lastAnchor = {href, targetTop: top, headerBottom};
        assert.ok(top >= headerBottom - 2 && top < headerBottom + 100, 'Anchor reaches visible heading');
        await shot(page, directory, row, key + '-anchor');
    } else await shot(page, directory, row, key + '-scrolled');
}

async function mobile(page, directory, row, key, hasToc) {
    assert.equal(await page.locator('[data-theory-aside]').isVisible(), false, 'No desktop menu overlay on mobile');
    const toggle = page.locator('[data-theory-mobile-nav-toggle]'); await toggle.click(); await ready(page, true);
    assert.equal(await toggle.getAttribute('aria-expanded'), 'true');
    const panel = page.locator('[data-theory-mobile-nav-panel]'); assert.ok(await panel.isVisible(), 'Mobile topic panel visible');
    const input = panel.locator('[data-theory-sidebar-search-input]'); await input.fill('present'); await settle(page);
    assert.ok((await visible(panel.locator('[data-theory-sidebar-highlight]')).allTextContents()).some(text => /present/i.test(text)), 'Mobile search retains matching topics');
    await panel.locator('[data-theory-sidebar-search-clear]').click(); await toggle.click(); await settle(page);
    assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
    if (hasToc) {
        const toc = page.locator('.theory-mobile-toc'); await toc.locator('summary').click(); await settle(page);
        const last = toc.locator('a').last(); await last.scrollIntoViewIfNeeded();
        await hitTest(last, row, 'lastLinkImmediateHit');
        row.preClickScrollSettled = await scrollStopped(page); await paint(page);
        await hitTest(last, row, 'lastLinkSettledHit');
        if (!row.lastLinkSettledHit.fullyWithinViewport && row.lastLinkSettledHit.bottom > row.lastLinkSettledHit.viewportHeight) {
            // A real additional wheel action makes the whole last-link border
            // visibly clear of native scrollIntoView's fractional bottom edge.
            // Strict final hit bounds stay unchanged; no forced click or activation.
            await page.mouse.move(row.lastLinkSettledHit.centerX, Math.min(row.lastLinkSettledHit.centerY, row.height - 20));
            await page.mouse.wheel(0, 120); row.additionalWheel = {deltaY: 120, before: row.lastLinkSettledHit};
            row.additionalWheel.settled = await scrollStopped(page); await paint(page);
        }
        assert.ok(await hitTest(last, row), 'Mobile last contents link receives real hit after scrolling settles');
        const href = await last.getAttribute('href'); await last.click(); row.anchorScroll = await scrollStopped(page);
        row.lastAnchor = {href, top: await page.locator('[id="' + href.slice(1) + '"]').evaluate(node => node.getBoundingClientRect().top)};
    }
    row.mobileGeometry = await geometry(page); await shot(page, directory, row, key + '-mobile');
}

async function run(mode, directory, label, beforeFile) {
    assert.ok(['before', 'after-smoke', 'after', 'probe-mobile'].includes(mode)); assert.match(label, /^[a-z0-9-]+$/);
    fs.mkdirSync(directory, {recursive: true}); const target = path.join(directory, label + '.json'); assert.ok(!fs.existsSync(target));
    const before = beforeFile ? JSON.parse(fs.readFileSync(beforeFile, 'utf8')) : null;
    if (mode !== 'before') assert.equal(before?.mode, 'before');
    const cases = PATHS.flatMap(urlPath => [{width: 1366, height: 768}, {width: 390, height: 844}, {width: 320, height: 800}]
        .flatMap(viewport => [false, true].map(dark => ({urlPath, ...viewport, dark}))));
    const plan = {...DEFAULT_PLAN, theoryPaths: [...new Set([...DEFAULT_PLAN.theoryPaths, ...PATHS])]};
    const report = {schema: 'gramlyze-legacy-sidebar-live-v1', mode, base: BASE, startedAt: new Date().toISOString(),
        conditions: 'Real local server, fresh read-only guests, no production requests or HTML substitution. Actual computed learner styles retained; absolute geometry and wrapping observed, not equated after intentional sidebar-width restoration. Only proven runtime-variable tokenBank exact-class button multiplicity/index offsets normalize, with full computed-style variants still equal and every raw element/index retained. Source component Math.random selection and BEFORE8/11 token counts establish natural variation. All static ordered elements/native controls remain strict. External font errors recorded.',
        beforeFile: beforeFile ? path.basename(beforeFile) : null, rows: [], authored: []};
    const save = () => fs.writeFileSync(target, JSON.stringify(report, null, 2));
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    try {
        for (const item of mode === 'after-smoke' ? cases.slice(0, 1) : mode === 'probe-mobile' ? cases.filter(item => item.urlPath === PATHS[0] && item.width === 390 && !item.dark) : cases) {
            const row = {...item, ...diagnostics()}; report.rows.push(row);
            const key = label + '-' + item.urlPath.split('/').at(-1) + '-' + item.width + '-' + (item.dark ? 'dark' : 'light');
            const {context, page} = await createPage(browser, {...item, mobile: item.width < 1024}, row, plan);
            let proof = null;
            try {
                const response = await page.goto(BASE + item.urlPath, {waitUntil: 'load', timeout: 60000}); row.status = response.status();
                assert.equal(row.status, 200); proof = authoredProof(await response.text()); row.observedAuthoredSha256 = proof.authoredTextSha256;
                if (!report.authored.some(old => old.path === item.urlPath)) report.authored.push({path: item.urlPath, ...proof});
                if (item.dark) await darkTheme(page);
                await settle(page);
                row.mainPresentation = await mainPresentation(page); row.initialGeometry = await geometry(page);
                if (mode === 'before') {
                    if (item.width >= 1024) await ready(page, false);
                    await shot(page, directory, row, key + '-before');
                } else {
                    const old = before.authored.find(old => old.path === item.urlPath); assert.ok(old);
                    for (const property of ['authoredText', 'title', 'canonical', 'ids', 'links', 'fields', 'tables', 'nativePractice']) {
                        const diff = difference(old[property], proof[property]);
                        if (diff) row.authoredDifference = {property, ...diff};
                        assert.deepEqual(proof[property], old[property], 'Preserve ' + property);
                    }
                    row.authoredParity = true;
                    const match = before.rows.find(old => old.urlPath === item.urlPath && old.width === item.width && old.dark === item.dark); assert.ok(match);
                    const current = comparablePresentation(row.mainPresentation), previous = comparablePresentation(match.mainPresentation);
                    row.presentationDifference = difference(previous, current);
                    assert.deepEqual(current, previous, 'Learning fonts/padding/borders/backgrounds must stay unchanged');
                    row.presentationParity = true;
                    const hasToc = await page.locator('.theory-mobile-toc').count() > 0;
                    if (item.width >= 1024) await desktop(page, directory, row, key, hasToc);
                    else await mobile(page, directory, row, key, hasToc);
                }
                assert.equal(row.pageErrors.length, 0); assert.equal(row.blocked.length, 0);
                assert.equal(row.httpErrors.filter(e => new URL(e.url).origin === BASE).length, 0);
                assert.equal(row.failures.filter(e => new URL(e.url).origin === BASE).length, 0);
                row.pass = true;
            } catch (error) {
                row.pass = false; row.error = error.message.slice(0, 1200);
                if (proof) row.sanitizedObservedProofOnFailure = proof;
                try { await shot(page, directory, row, key + '-failure'); } catch (captureError) {row.screenshotError = captureError.message.slice(0, 500);}
            } finally {await context.close(); save();}
        }
    } finally {
        await browser.close(); report.finishedAt = new Date().toISOString();
        report.counts = {uniquePages: new Set(report.rows.map(row => row.urlPath)).size, scenarios: report.rows.length,
            passed: report.rows.filter(row => row.pass).length, screenshots: report.rows.reduce((total, row) => total + row.screenshots.length, 0)};
        report.pass = report.rows.every(row => row.pass); save();
    }
    console.log(JSON.stringify({file: path.resolve(target), pass: report.pass, counts: report.counts}));
    if (!report.pass) process.exitCode = 1; return report;
}
module.exports = {run, mainPresentation, comparablePresentation};
if (require.main === module) run(...process.argv.slice(2)).catch(error => {console.error(error.message); process.exitCode = 1;});
