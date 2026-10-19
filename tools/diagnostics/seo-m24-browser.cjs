'use strict';
// Read-only M24 live browser acceptance. No answer submission or course unlock.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const profile = require('./seo-m24-local.cjs');
const fidelity = require('./seo-m24-fidelity.cjs');
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const sha = text => crypto.createHash('sha256').update(text).digest('hex');
const clean = text => text.replace(/\s+/g, ' ').trim();
const BASE = 'http://gramlyze.loc';

async function run(directory, label) {
    assert.match(label, /^[a-z0-9-]+$/);
    fs.mkdirSync(directory, {recursive: true});
    const browser = await chromium.launch({headless: true,
        ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    const report = {at: new Date().toISOString(), package: 'm24', browser: browser.version(),
        scope: 'Live gramlyze.loc GET/browser checks; no answer POST, course unlock, or DOM substitution.',
        rows: [], controls: [], course: null};
    const reportPath = path.join(directory, label + '-browser.json');
    fs.writeFileSync(reportPath, JSON.stringify(report), {flag: 'wx'});
    try {
        for (const mobile of [false, true]) for (const lesson of fidelity.master().lessons) {
            const row = {path: lesson.theory_path, mobile, errors: [], failed: [], blocked: [], screenshots: []};
            report.rows.push(row);
            const context = await browser.newContext({
                viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 900},
                isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', colorScheme: 'light', serviceWorkers: 'block'
            });
            await context.route('**/*', route => {
                const q = route.request(); const reason = profile.decision(q.url(), q.method(), q.isNavigationRequest());
                if (reason) { row.blocked.push({url: new URL(q.url()).pathname, reason}); return route.abort(); }
                return route.continue();
            });
            const page = await context.newPage();
            page.on('pageerror', error => row.errors.push({name: error.name, sha256: sha(error.message)}));
            page.on('requestfailed', request => row.failed.push({path: new URL(request.url()).pathname,
                error: request.failure()?.errorText}));
            try {
                const response = await page.goto(BASE + lesson.theory_path, {waitUntil: 'domcontentloaded', timeout: 60000});
                row.status = response.status(); assert.equal(row.status, 200);
                const serverHtml = await response.text();
                row.server = fidelity.assertLiveHtml(lesson, serverHtml);
                await page.locator('[data-theory-main] h1').waitFor({state: 'visible'});
                const expected = clean(new JSDOM(serverHtml).window.document.querySelector('[data-theory-main]').textContent);
                row.serverMainSha256 = sha(expected);
                row.domMainSha256 = sha(clean(await page.locator('[data-theory-main]').textContent()));
                assert.equal(row.domMainSha256, row.serverMainSha256, lesson.key + ' live DOM differs from server');
                const reload = await page.reload({waitUntil: 'domcontentloaded', timeout: 60000});
                assert.equal(reload.status(), 200);
                row.reload = fidelity.assertLiveHtml(lesson, await reload.text());
                assert.equal(sha(clean(await page.locator('[data-theory-main]').textContent())), row.domMainSha256);
                row.details = [];
                const exercise = page.locator('#self-check-m24-' + lesson.key);
                const summary = exercise.locator('details > summary');
                await summary.click();
                assert.ok(await summary.evaluate(node => node.parentElement.open));
                await summary.click();
                assert.ok(!(await summary.evaluate(node => node.parentElement.open)));
                await summary.focus(); await page.keyboard.press('Enter');
                assert.ok(await summary.evaluate(node => node.parentElement.open));
                await page.keyboard.press('Space');
                assert.ok(!(await summary.evaluate(node => node.parentElement.open)));
                row.details.push({mouseOpenClose: true, enterOpens: true, spaceCloses: true});
                const snapshot = JSON.parse(fs.readFileSync(path.join(directory, 'final-targets.json'), 'utf8'));
                const owned = snapshot.rows.find(entry => entry.key === lesson.key);
                row.nativeBlocks = [];
                for (const block of owned.blocks.filter(block => block.locale === 'uk' &&
                    !['subtitle', 'hero', 'navigation-chips'].includes(block.type))) {
                    const element = page.locator('#block-' + block.id).first();
                    await element.scrollIntoViewIfNeeded();
                    assert.ok(await element.isVisible(), lesson.key + ' native/box block invisible: ' + block.type);
                    row.nativeBlocks.push({type: block.type, id: block.id, visible: true});
                }
                assert.equal(row.nativeBlocks.length, lesson.existing_blocks.length - 2 + 1);
                const tables = page.locator('[data-theory-main] table');
                row.tables = await tables.count();
                for (const mode of ['light', 'dark']) {
                    if (mode === 'dark') {
                        await page.getByRole('button', {name: 'Тема', exact: true}).click();
                        await page.waitForFunction(() => document.documentElement.classList.contains('dark'));
                    }
                    const theme = {mode, dark: await page.locator('html').evaluate(node => node.classList.contains('dark'))};
                    assert.equal(theme.dark, mode === 'dark');
                    theme.overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
                    assert.equal(theme.overflow, false, lesson.key + ' horizontal page overflow');
                    theme.practiceVisible = await exercise.isVisible();
                    assert.ok(theme.practiceVisible);
                    theme.tables = [];
                    for (const table of await tables.all()) {
                        const state = await table.evaluate(node => {
                            const parent = node.parentElement; const before = parent.scrollLeft;
                            parent.scrollLeft = parent.scrollWidth;
                            const result = {width: parent.clientWidth, contentWidth: parent.scrollWidth,
                                scrolled: parent.scrollLeft, was: before};
                            parent.scrollLeft = before;
                            return result;
                        });
                        if (mobile && state.contentWidth > state.width + 2) assert.ok(state.scrolled > 0,
                            lesson.key + ' native table is not horizontally scrollable');
                        theme.tables.push(state);
                    }
                    await page.locator('[data-theory-main] h1').scrollIntoViewIfNeeded();
                    const first = label + '-' + lesson.key + '-' + (mobile ? 'mobile' : 'desktop') + '-' + mode + '-top.png';
                    await page.screenshot({path: path.join(directory, first), animations: 'disabled'});
                    row.screenshots.push(first);
                    await exercise.scrollIntoViewIfNeeded();
                    const practice = label + '-' + lesson.key + '-' + (mobile ? 'mobile' : 'desktop') + '-' + mode + '-practice.png';
                    await page.screenshot({path: path.join(directory, practice), animations: 'disabled'});
                    row.screenshots.push(practice);
                    if (await tables.count()) {
                        await tables.first().scrollIntoViewIfNeeded();
                        // content-visibility:auto needs a paint after a deep programmatic scroll.
                        await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
                        const tableShot = label + '-' + lesson.key + '-' + (mobile ? 'mobile' : 'desktop') + '-' + mode + '-table.png';
                        await page.screenshot({path: path.join(directory, tableShot), animations: 'disabled'});
                        row.screenshots.push(tableShot);
                        if (mobile) {
                            await tables.first().evaluate(node => { node.parentElement.scrollLeft = node.parentElement.scrollWidth; });
                            const rightShot = label + '-' + lesson.key + '-mobile-' + mode + '-table-right.png';
                            await page.screenshot({path: path.join(directory, rightShot), animations: 'disabled'});
                            row.screenshots.push(rightShot);
                        }
                    }
                    const grid = page.locator('#block-' + owned.blocks.find(block => block.locale === 'uk' && block.type === 'forms-grid').id).first();
                    await grid.scrollIntoViewIfNeeded();
                    const gridShot = label + '-' + lesson.key + '-' + (mobile ? 'mobile' : 'desktop') + '-' + mode + '-forms-grid.png';
                    await page.screenshot({path: path.join(directory, gridShot), animations: 'disabled'});
                    row.screenshots.push(gridShot);
                    if (mode === 'light') {
                        const fullShot = label + '-' + lesson.key + '-' + (mobile ? 'mobile' : 'desktop') + '-full.png';
                        await page.screenshot({path: path.join(directory, fullShot), fullPage: true, animations: 'disabled'});
                        row.screenshots.push(fullShot);
                    }
                    row[mode] = theme;
                }
                const links = await page.locator('a[href]').evaluateAll((nodes, target) => nodes
                    .filter(node => new URL(node.href).pathname === target).map(node => node.href), profile.test(lesson.slug));
                assert.ok(links.length > 0, lesson.key + ' test link missing');
                row.testLink = links[0];
                if (!mobile) {
                    await page.locator('a[href="' + links[0] + '"]').first().click();
                    await page.waitForURL(BASE + profile.test(lesson.slug), {timeout: 30000});
                    row.testNavigation = page.url() === BASE + profile.test(lesson.slug);
                    assert.ok(row.testNavigation);
                }
                assert.equal(row.errors.length, 0, lesson.key + ' page errors');
                row.pass = true;
            } catch (error) { row.pass = false; row.error = error.message.slice(0, 500); }
            finally { await context.close(); fs.writeFileSync(reportPath, JSON.stringify(report, null, 2)); }
        }
        for (const control of profile.controls) {
            const context = await browser.newContext({viewport: {width: 1440, height: 900}, serviceWorkers: 'block'});
            await context.route('**/*', route => profile.decision(route.request().url(), route.request().method(),
                route.request().isNavigationRequest()) ? route.abort() : route.continue());
            const page = await context.newPage();
            const response = await page.goto(BASE + control, {waitUntil: 'domcontentloaded', timeout: 60000});
            const entry = {path: control, status: response.status(),
                h1: clean(await page.locator('[data-theory-main] h1').textContent()),
                overflow: await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2)};
            assert.equal(entry.status, 200); assert.ok(entry.h1 && !entry.overflow);
            report.controls.push(entry); await context.close();
        }
        const context = await browser.newContext({viewport: {width: 1440, height: 900}, serviceWorkers: 'block'});
        await context.route('**/*', route => profile.decision(route.request().url(), route.request().method(),
            route.request().isNavigationRequest()) ? route.abort() : route.continue());
        const page = await context.newPage();
        const response = await page.goto(BASE + profile.course, {waitUntil: 'domcontentloaded', timeout: 60000});
        report.course = {path: profile.course, status: response.status(), h1: await page.locator('h1').allTextContents(),
            contentVisible: await page.locator('[data-theory-lesson-content]').isVisible().catch(() => false)};
        assert.equal(report.course.status, 200);
        await context.close();
    } finally { await browser.close(); fs.writeFileSync(reportPath, JSON.stringify(report, null, 2)); }
    assert.ok(report.rows.every(row => row.pass), 'One or more live M24 browser scenarios failed');
    console.log(JSON.stringify({rows: report.rows.map(row => ({path: row.path, mobile: row.mobile,
        pass: row.pass, tables: row.tables, errors: row.errors.length, failed: row.failed.length,
        blocked: row.blocked.length, error: row.error})), controls: report.controls,
        course: report.course}, null, 2));
    return report;
}
if (require.main === module) {
    const [directory, label] = process.argv.slice(2);
    run(directory, label).catch(error => { console.error(error); process.exitCode = 1; });
}
module.exports = {run};
