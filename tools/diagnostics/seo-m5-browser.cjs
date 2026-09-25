'use strict';
// Local functional metadata acceptance only. No performance probes or answers submitted.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const m4 = require('./seo-m4-browser.cjs');
const PAGES = Object.freeze([
    ['lesson', '/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms'],
    ['questions', '/test/future-perfect/questions'],
    ['category', '/theory/future-perfect'],
    ['course', '/courses/english-grammar-theory'],
]);
function decision(url, navigation, redirected) {
    const unsafe = m4.requestDecision(url);
    if (unsafe) return unsafe;
    const u = new URL(url);
    if (navigation && (redirected || u.origin !== m4.BASE || u.search || u.hash || !PAGES.some(([, p]) => p === u.pathname))) return 'outside-navigation-plan';
    return null;
}
async function main(label) {
    assert.match(label || '', /^[a-z0-9_-]+$/i);
    const output = path.resolve(__dirname, '../../storage/app/seo-m5-local');
    fs.mkdirSync(output, {recursive: true});
    const file = path.join(output, label + '-browser.json');
    const report = {base: m4.BASE, startedAt: new Date().toISOString(), rows: []};
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    const save = () => fs.writeFileSync(file, JSON.stringify(report, null, 2));
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    report.browser = browser.version();
    try {
        for (const mobile of [false, true]) for (const [name, pagePath] of PAGES) {
            const row = {name, path: pagePath, mobile, blocked: [], console: [], failures: [], responses: []};
            report.rows.push(row); save();
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000}, isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', serviceWorkers: 'block'});
            // Install before creating the page, including guards for redirects and popup navigation.
            await context.route('**/*', async route => {
                const q = route.request(); const reason = decision(q.url(), q.isNavigationRequest(), !!q.redirectedFrom());
                if (reason) { row.blocked.push({url: m4.safeUrl(q.url()), reason}); await route.abort(); }
                else await route.continue();
            });
            const page = await context.newPage();
            page.on('console', message => {
                if (!['error', 'warning'].includes(message.type())) return;
                row.console.push({...m4.diagnosticError(new Error(message.text())), type: message.type(), duplicateRuntime: /multiple instances|already initialized/i.test(message.text())});
            });
            page.on('pageerror', e => row.console.push({type: 'pageerror', ...m4.diagnosticError(e)}));
            page.on('requestfailed', q => row.failures.push({url: m4.safeUrl(q.url()), method: q.method(), error: q.failure()?.errorText === 'net::ERR_ABORTED' ? 'net::ERR_ABORTED' : 'request-failed'}));
            page.on('response', r => row.responses.push({url: m4.safeUrl(r.url()), method: r.request().method(), type: r.request().resourceType(), status: r.status()}));
            try {
                const response = await page.goto(m4.BASE + pagePath, {waitUntil: 'load', timeout: 45000});
                assert.equal(response.status(), 200);
                assert.match(response.headers()['x-robots-tag'], /noindex/);
                await page.waitForFunction(() => !!window.Alpine);
                row.metadata = await page.evaluate(() => {
                    const fields = {title: 'head title', description: 'meta[name="description"]', ogTitle: 'meta[property="og:title"]', ogDescription: 'meta[property="og:description"]', twitterTitle: 'meta[name="twitter:title"]', twitterDescription: 'meta[name="twitter:description"]'};
                    const result = {};
                    for (const [key, selector] of Object.entries(fields)) result[key] = [...document.querySelectorAll(selector)].map(n => n.content ?? n.textContent.trim());
                    return {...result, h1: [...document.querySelectorAll('h1')].map(n => n.textContent.trim()), canonical: [...document.querySelectorAll('link[rel="canonical"]')].map(n => n.href), overflow: document.documentElement.scrollWidth - innerWidth};
                });
                const m = row.metadata;
                for (const key of ['title', 'description', 'ogTitle', 'ogDescription', 'twitterTitle', 'twitterDescription', 'h1']) assert.equal(m[key].length, 1, key);
                assert.deepEqual(m.ogTitle, m.title); assert.deepEqual(m.twitterTitle, m.title);
                assert.deepEqual(m.ogDescription, m.description); assert.deepEqual(m.twitterDescription, m.description);
                assert.deepEqual(m.canonical, [m4.SEO_ORIGIN + pagePath]); assert.ok(m.overflow <= 1);
                if (mobile) {
                    const menu = page.locator('#site-header [x-show="mobile"]').first();
                    await page.getByRole('button', {name: 'Меню', exact: true}).click(); await menu.waitFor({state: 'visible'});
                    assert.ok(await menu.locator('a[href*="/theory"]').count());
                    await page.getByRole('button', {name: 'Меню', exact: true}).click(); await menu.waitFor({state: 'hidden'});
                }
                if (name === 'questions') {
                    await page.waitForFunction(() => typeof state !== 'undefined' && state.items?.length > 0);
                    row.questions = await page.evaluate(() => ({initial: window.__INITIAL_JS_TEST_QUESTIONS__.length, rendered: state.items.length, answered: state.answered, inputs: document.querySelectorAll('#questions input, #questions button').length}));
                    assert.equal(row.questions.rendered, row.questions.initial); assert.equal(row.questions.answered, 0); assert.ok(row.questions.inputs > 0);
                }
                if (name === 'course') assert.ok(await page.locator('[data-theory-course-card]').count());
                if (name === 'lesson') assert.ok(await page.locator('[data-theory-main]').count());
                row.screenshot = `${label}-${name}-${mobile ? 'mobile' : 'desktop'}.png`;
                await page.screenshot({path: path.join(output, row.screenshot), fullPage: false, animations: 'disabled'});
                row.network = m4.networkEvidence(row, pagePath); assert.ok(row.network.pass);
                row.pass = true;
            } catch (error) { row.error = m4.diagnosticError(error); row.pass = false; }
            finally { await context.close(); save(); console.log(JSON.stringify({name, mobile, pass: row.pass, error: row.error})); }
        }
    } finally { await browser.close(); report.finishedAt = new Date().toISOString(); report.pass = report.rows.length === 8 && report.rows.every(r => r.pass); save(); }
    if (!report.pass) process.exitCode = 1;
}
module.exports = {decision, PAGES};
if (require.main === module) main(process.argv[2]).catch(e => { console.error(m4.diagnosticError(e)); process.exitCode = 1; });
