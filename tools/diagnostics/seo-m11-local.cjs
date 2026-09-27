'use strict';
// M11 read-only, local-only acceptance. No session cookies, answer POSTs or unlocks.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const BASE = 'http://gramlyze.loc';
const SLUGS = ['linking-words-reason-result-contrast', 'advanced-linking-devices', 'concessive-and-contrastive-structures'];
const theory = s => '/theory/clauses-and-linking-words/' + s;
const test = s => '/test/clauses-and-linking-words/' + s;
const course = '/courses/english-grammar-theory/lesson/clauses-and-linking-words/' + SLUGS[0];
const PATHS = [...SLUGS.map(theory), ...SLUGS.map(test), course, '/sitemap.xml'];
const sha = s => crypto.createHash('sha256').update(s).digest('hex');
const safeUrl = s => { try { const u = new URL(s); return u.origin + u.pathname; } catch { return '[invalid-url]'; } };
function decision(value, method = 'GET', navigation = false) {
    const u = new URL(value);
    if (!['GET', 'HEAD'].includes(method)) return 'stateful-request';
    if (u.username || u.password) return 'credentialed-url';
    if (navigation) return u.origin === BASE && PATHS.includes(u.pathname) && !u.search ? null : 'outside-plan';
    return u.origin === BASE || ['fonts.googleapis.com', 'fonts.gstatic.com'].includes(u.hostname) ? null : 'external-host';
}
function metadata(document) {
    const out = {};
    for (const [key, selector, attr] of [
        ['title', 'title'], ['h1', 'h1'], ['description', 'meta[name="description"]', 'content'],
        ['canonical', 'link[rel="canonical"]', 'href'], ['robots', 'meta[name="robots"]', 'content'],
        ['ogDescription', 'meta[property="og:description"]', 'content'], ['twitterDescription', 'meta[name="twitter:description"]', 'content'],
    ]) out[key] = [...document.querySelectorAll(selector)].map(n => attr ? n.getAttribute(attr) : n.textContent.trim());
    return out;
}
async function capture(dir, label) {
    assert.match(label, /^[a-z0-9-]+$/);
    const result = {at: new Date().toISOString(), base: BASE, rows: []};
    for (const urlPath of PATHS) {
        const r = await fetch(BASE + urlPath, {headers: {Accept: urlPath.endsWith('.xml') ? 'application/xml' : 'text/html'}, redirect: 'manual', signal: AbortSignal.timeout(60000)});
        const html = await r.text();
        const row = {path: urlPath, status: r.status, location: r.headers.get('location'), contentType: r.headers.get('content-type'), xRobotsTag: r.headers.get('x-robots-tag')};
        if (urlPath === '/sitemap.xml') {
            const dom = new JSDOM(html, {contentType: 'text/xml'});
            row.orderedLocs = [...dom.window.document.querySelectorAll('loc')].map(n => n.textContent);
            row.orderedSha256 = sha(JSON.stringify(row.orderedLocs)); dom.window.close();
        } else {
            const dom = new JSDOM(html); const d = dom.window.document;
            row.metadata = metadata(d);
            const main = d.querySelector('[data-theory-main]') || d.querySelector('[data-theory-lesson-content]') || d.querySelector('main') || d.body;
            row.textSha256 = sha(main.textContent.replace(/\s+/g, ' ').trim());
            row.testLinks = [...d.querySelectorAll('a[href]')].map(n => n.getAttribute('href')).filter(s => s.includes('/test/clauses-and-linking-words/'));
            row.selfChecks = d.querySelectorAll('[id^="self-check-"]').length;
            row.anchorPlaceholder = /theory anchor|lesson package/.test(main.textContent);
            row.details = d.querySelectorAll('details').length;
            dom.window.close();
        }
        result.rows.push(row);
    }
    fs.writeFileSync(path.join(dir, label + '-http.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
    console.log(JSON.stringify(result.rows.map(r => ({path: r.path, status: r.status, metadata: r.metadata, tests: r.testLinks, locs: r.orderedLocs?.length})), null, 2));
}
async function browserChecks(dir, label) {
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    const report = {at: new Date().toISOString(), base: BASE, browser: browser.version(), rows: [], scope: 'Live local rendering only; no environment override, database write, course unlock or answer submission.'};
    const file = path.join(dir, label + '-browser.json');
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    try {
        for (const mobile of [false, true]) for (const slug of SLUGS) {
            const row = {path: theory(slug), mobile, errors: [], failed: [], blocked: [], screenshots: []}; report.rows.push(row);
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000}, isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', serviceWorkers: 'block'});
            await context.route('**/*', async route => {
                const q = route.request(), reason = decision(q.url(), q.method(), q.isNavigationRequest());
                if (reason) { row.blocked.push({url: safeUrl(q.url()), reason}); await route.abort(); } else await route.continue();
            });
            const page = await context.newPage();
            page.on('pageerror', e => row.errors.push({name: e.name, messageSha256: sha(e.message)}));
            page.on('requestfailed', q => row.failed.push({url: safeUrl(q.url()), reason: q.failure()?.errorText}));
            try {
                const response = await page.goto(BASE + theory(slug), {waitUntil: 'networkidle', timeout: 60000});
                row.status = response.status(); assert.equal(row.status, 200);
                row.selfChecks = await page.locator('[id^="self-check-"]').count();
                const box = page.locator('[data-theory-main] article .prose').first();
                await box.scrollIntoViewIfNeeded();
                row.boxVisible = await box.isVisible();
                row.rawMarkup = await box.evaluate(n => /<\/?(?:p|strong|table)\b/.test(n.textContent));
                for (const summary of await box.locator('details > summary').all()) await summary.click();
                row.overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
                const screenshot = label + '-' + (mobile ? 'mobile-' : 'desktop-') + slug + '.png';
                await page.screenshot({path: path.join(dir, screenshot), fullPage: true, animations: 'disabled'}); row.screenshots.push(screenshot);
                assert.ok(row.boxVisible && !row.rawMarkup && !row.overflow);
                const link = page.locator('a[href]').filter({hasText: /Пройти тест/});
                const actual = await link.evaluateAll((nodes, expected) => nodes.filter(n => new URL(n.href).pathname === expected).map(n => n.href), test(slug));
                row.mainTestHref = actual[0] || null;
                if (!mobile) {
                    const candidate = page.locator(`a[href="${actual[0]}"]`).first();
                    assert.ok(actual.length);
                    await candidate.click(); await page.waitForURL(BASE + test(slug));
                    row.mainTestOpened = page.url() === BASE + test(slug);
                    row.testH1 = await page.locator('h1').allTextContents();
                }
                row.pass = true;
            } catch (e) { row.pass = false; row.error = {name: e.name, message: e.message.slice(0, 400)}; }
            finally { await context.close(); fs.writeFileSync(file, JSON.stringify(report, null, 2)); }
        }
        const context = await browser.newContext({viewport: {width: 1440, height: 1000}, serviceWorkers: 'block'});
        await context.route('**/*', async route => decision(route.request().url(), route.request().method(), route.request().isNavigationRequest()) ? route.abort() : route.continue());
        const page = await context.newPage(); const response = await page.goto(BASE + course, {waitUntil: 'networkidle', timeout: 60000});
        report.course = {path: course, status: response.status(), h1: await page.locator('h1').allTextContents(), contentVisible: await page.locator('[data-theory-lesson-content]').isVisible()};
        await page.screenshot({path: path.join(dir, label + '-course.png'), fullPage: true}); await context.close();
    } finally { await browser.close(); fs.writeFileSync(file, JSON.stringify(report, null, 2)); }
    console.log(JSON.stringify({file, rows: report.rows.map(r => ({path: r.path, mobile: r.mobile, pass: r.pass, selfChecks: r.selfChecks, overflow: r.overflow, error: r.error})), course: report.course}, null, 2));
    return report.rows.every(r => r.pass);
}
async function fixtureChecks(dir, label, fixtureDir) {
    // Clearly separate renderer-fixture evidence from live DB acceptance. Only the
    // browser DOM is replaced, inside an ephemeral context; no page/DB is updated.
    assert.ok(fixtureDir, 'Pass the private isolated-test fixture directory');
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    const report = {at: new Date().toISOString(), type: 'isolated-render-fixtures-not-live-content', rows: []};
    const file = path.join(dir, label + '-fixtures.json');
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    try {
        for (const mobile of [false, true]) for (const slug of SLUGS) {
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000}, isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', serviceWorkers: 'block'});
            const row = {slug, mobile, errors: [], failed: [], screenshots: []}; report.rows.push(row);
            await context.route('**/*', async r => decision(r.request().url(), r.request().method(), r.request().isNavigationRequest()) ? r.abort() : r.continue());
            try {
                const page = await context.newPage();
                page.on('pageerror', e => row.errors.push({name: e.name, messageSha256: sha(e.message)}));
                page.on('requestfailed', q => row.failed.push({url: safeUrl(q.url()), reason: q.failure()?.errorText}));
                await page.goto(BASE + theory(slug), {waitUntil: 'networkidle', timeout: 60000});
                const html = fs.readFileSync(path.join(fixtureDir, 'm11-' + slug + '.html'), 'utf8');
                const dom = new JSDOM(html);
                const source = dom.window.document.querySelector('[data-theory-main]');
                assert.ok(source); source.querySelectorAll('script').forEach(n => n.remove());
                await page.locator('[data-theory-main]').evaluate((n, body) => { n.innerHTML = body; }, source.innerHTML);
                dom.window.close();
                const exercise = page.locator('#self-check-' + slug);
                await exercise.scrollIntoViewIfNeeded();
                row.questions = await exercise.locator(':scope > ol > li').count();
                assert.equal(row.questions, 6);
                await exercise.locator('details > summary').click();
                row.keys = await exercise.locator('details[open] > ol > li').count();
                assert.equal(row.keys, 6);
                row.numberedLists = await exercise.locator('ol').evaluateAll(nodes => nodes.every(n => getComputedStyle(n).listStyleType === 'decimal'));
                assert.ok(row.numberedLists);
                row.rawMarkup = await page.locator('[data-theory-main]').evaluate(n => /<\/?(?:p|strong|table)\b/.test(n.textContent));
                row.overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
                assert.ok(!row.rawMarkup && !row.overflow);
                for (const [part, locator] of [['keys', exercise.locator('details')], ['table', page.locator('[data-theory-main] table').first()]]) {
                    if (!(await locator.count())) continue;
                    await locator.scrollIntoViewIfNeeded();
                    const name = label + '-' + (mobile ? 'mobile-' : 'desktop-') + slug + '-' + part + '.png';
                    await page.screenshot({path: path.join(dir, name), animations: 'disabled'}); row.screenshots.push(name);
                    if (part === 'table' && mobile) {
                        row.tableScroll = await locator.evaluate(n => {
                            const wrapper = n.parentElement;
                            wrapper.scrollLeft = wrapper.scrollWidth;
                            return {left: wrapper.scrollLeft, width: wrapper.clientWidth, contentWidth: wrapper.scrollWidth};
                        });
                        assert.ok(row.tableScroll.left > 0, 'Wide comparison table must scroll on mobile');
                        const right = label + '-mobile-' + slug + '-table-right.png';
                        await page.screenshot({path: path.join(dir, right), animations: 'disabled'}); row.screenshots.push(right);
                    }
                }
                const full = label + '-' + (mobile ? 'mobile-' : 'desktop-') + slug + '-full.png';
                await page.screenshot({path: path.join(dir, full), fullPage: true, animations: 'disabled'}); row.screenshots.push(full);
                assert.equal(row.errors.length, 0);
                row.pass = true;
            } catch (e) { row.pass = false; row.error = e.message.slice(0, 400); }
            finally { await context.close(); fs.writeFileSync(file, JSON.stringify(report, null, 2)); }
        }
    } finally { await browser.close(); }
    console.log(JSON.stringify(report, null, 2));
    return report.rows.every(r => r.pass);
}
if (require.main === module) {
    const [mode, dir, label, fixtureDir] = process.argv.slice(2);
    assert.ok(['capture', 'browser', 'fixtures'].includes(mode)); assert.match(label || '', /^[a-z0-9-]+$/);
    fs.mkdirSync(dir, {recursive: true});
    (mode === 'capture' ? capture(dir, label) : mode === 'fixtures' ? fixtureChecks(dir, label, fixtureDir) : browserChecks(dir, label)).then(ok => {if (ok === false) process.exitCode = 1;}).catch(e => {console.error(e.message); process.exitCode = 1;});
}
module.exports = {decision, metadata, PATHS};
