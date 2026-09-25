'use strict';
// Functional M6 checks. All requests local; no performance scoring or learner answers.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {safeUrl, diagnosticError} = require('./seo-m4-browser.cjs');
const BASE = 'http://gramlyze.loc';
const CASES = [
    ['catalog', '/catalog-tests/cards', '/catalog/tests-cards'],
    ['catalog-en', '/en/tests/cards', '/en/catalog/tests-cards'],
    ['course-home', '/courses/sentence-builder-english-a1', '/courses/sentence-builder-english-a1'],
    ['questions', '/test/future-perfect/questions', '/test/future-perfect/questions'],
    ['theory-course', '/courses/english-grammar-theory', '/courses/english-grammar-theory'],
];
function decision(value, navigation, allowed) {
    let u;
    try { u = new URL(value); } catch { return 'invalid-url'; }
    if (u.origin !== BASE || u.username || u.password) return 'nonlocal-blocked';
    if (navigation && (!allowed.has(u.pathname) || u.search)) return 'outside-navigation-plan';
    return null;
}
async function menuEvidence(page, mobile) {
    const region = page.locator(mobile ? '#site-header [x-show="mobile"]' : '#site-header > div > nav').first();
    const button = page.getByRole('button', {name: 'Меню', exact: true});
    if (mobile) { await button.click(); await region.waitFor({state: 'visible'}); }
    const evidence = await region.evaluate(node => {
        const links = [...node.querySelectorAll('a[href]')].filter(a => a.getBoundingClientRect().width > 0);
        return {theory: links.some(a => new URL(a.href).pathname === '/theory'),
            courses: links.some(a => new URL(a.href).pathname === '/courses')};
    });
    if (mobile) { await button.click(); await region.waitFor({state: 'hidden'}); }
    return {...evidence, closedAfterToggle: !mobile || !(await region.isVisible())};
}
async function main(label) {
    assert.match(label || '', /^[a-z0-9_-]+$/i);
    const output = path.resolve(__dirname, '../../storage/app/seo-m6-local');
    fs.mkdirSync(output, {recursive: true});
    const file = path.join(output, label + '-browser.json');
    const report = {startedAt: new Date().toISOString(), base: BASE, rows: [],
        policy: 'New guest per scenario. Nonlocal requests blocked before navigation. Screenshots and sanitized counters only; no answers submitted.'};
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    const save = () => fs.writeFileSync(file, JSON.stringify(report, null, 2));
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    report.browser = browser.version();
    try {
        for (const mobile of [false, true]) for (const [name, old, current] of CASES) {
            const row = {name, mobile, path: old, blocked: [], console: [], failures: [], responses: []};
            report.rows.push(row); save();
            const allowed = new Set([old, current]);
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000},
                isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', serviceWorkers: 'block'});
            await context.route('**/*', async route => {
                const q = route.request();
                const reason = decision(q.url(), q.isNavigationRequest(), allowed);
                if (reason) { row.blocked.push({url: safeUrl(q.url()), reason}); await route.abort('blockedbyclient'); }
                else await route.continue();
            });
            const page = await context.newPage();
            // A second browser-level barrier also covers server redirects.
            const cdp = await context.newCDPSession(page);
            await cdp.send('Network.enable');
            await cdp.send('Network.setBlockedURLs', {urls: ['*://gramlyze.com/*', '*://*.gramlyze.com/*', '*://gramlyze.ub/*', '*://*.gramlyze.ub/*']});
            page.on('console', message => {
                if (!['error', 'warning'].includes(message.type())) return;
                row.console.push({type: message.type(), url: safeUrl(message.location().url),
                    blockedResource: /ERR_BLOCKED_BY_CLIENT/.test(message.text()), ...diagnosticError(new Error(message.text()))});
            });
            page.on('pageerror', error => row.console.push({type: 'pageerror', ...diagnosticError(error)}));
            page.on('requestfailed', q => row.failures.push({url: safeUrl(q.url()), method: q.method(), error: q.failure()?.errorText}));
            page.on('response', r => row.responses.push({url: safeUrl(r.url()), status: r.status(), type: r.request().resourceType(), method: r.request().method()}));
            try {
                const response = await page.goto(BASE + old, {waitUntil: 'load', timeout: 45000});
                assert.equal(response.status(), 200); assert.equal(page.url(), BASE + current);
                assert.match(response.headers()['x-robots-tag'], /noindex/);
                await page.waitForFunction(() => !!window.Alpine);
                row.final = current;
                row.canonical = await page.locator('link[rel="canonical"]').getAttribute('href');
                assert.equal(row.canonical, 'https://gramlyze.com' + current.replace(/^\/(en|pl)(?=\/)/, ''));
                assert.equal(await page.locator('h1').count(), 1);
                if (name !== 'catalog-en') { row.menu = await menuEvidence(page, mobile); assert.ok(row.menu.theory && row.menu.courses && row.menu.closedAfterToggle); }
                row.legacyLinks = await page.locator('a[href]').evaluateAll(nodes => nodes.filter(n => /\/(catalog-tests\/cards|tests\/cards|courses\/polyglot-english)/.test(new URL(n.href).pathname)).length);
                assert.equal(row.legacyLinks, 0);
                if (name === 'catalog-en') {
                    // Real switcher URL from rendered navigation, retaining the same catalogue.
                    const link = page.locator('a[href="' + BASE + '/pl/catalog/tests-cards"]').first();
                    assert.ok(await link.count());
                    const href = await link.getAttribute('href');
                    allowed.add('/pl/catalog/tests-cards');
                    const switched = await page.goto(href, {waitUntil: 'load', timeout: 45000});
                    assert.equal(switched.status(), 200); assert.equal(page.url(), BASE + '/pl/catalog/tests-cards');
                    row.languageSwitch = '/pl/catalog/tests-cards';
                }
                if (name === 'course-home') {
                    const anchor = page.locator('a[href="#polyglot-course-lessons"]').first();
                    await anchor.click();
                    assert.equal(new URL(page.url()).hash, '#polyglot-course-lessons');
                    assert.ok(await page.locator('#polyglot-course-lessons').isVisible());
                    row.anchor = '#polyglot-course-lessons';
                    row.firstLesson = await page.locator('a[href*="/test/sentence-builder-to-be-a1/step/compose"]').first().getAttribute('href');
                    // Do not follow the known closed lesson or change its availability.
                }
                if (name === 'questions') {
                    await page.waitForFunction(() => typeof state !== 'undefined' && state.items?.length > 0);
                    row.questions = await page.evaluate(() => ({initial: window.__INITIAL_JS_TEST_QUESTIONS__.length, rendered: state.items.length,
                        answered: state.answered, endpoint: window.JS_TEST_PERSISTENCE.questionsEndpoint, mode: window.JS_TEST_PERSISTENCE.mode}));
                    assert.equal(row.questions.initial, row.questions.rendered); assert.equal(row.questions.answered, 0);
                    const endpoint = new URL(row.questions.endpoint, BASE);
                    assert.equal(endpoint.origin, BASE); assert.equal(endpoint.pathname, current + '/questions');
                    assert.equal(endpoint.search, ''); assert.equal(row.questions.mode, 'saved-test-js-v2');
                    endpoint.searchParams.set('mode', row.questions.mode);
                    const data = await context.request.get(endpoint.href, {headers: {Accept: 'application/json'}, maxRedirects: 0});
                    assert.equal(data.status(), 200); row.questions.loaded = (await data.json()).questions.length;
                    assert.equal(row.questions.loaded, row.questions.initial); await data.dispose();
                }
                if (name === 'theory-course') {
                    const entry = page.locator('#theory-course-hero-cta');
                    const href = await entry.getAttribute('href');
                    const destination = new URL(href, BASE);
                    assert.equal(destination.origin, BASE);
                    assert.ok(destination.pathname.startsWith('/courses/english-grammar-theory/lesson/'));
                    allowed.add(destination.pathname);
                    await entry.click(); await page.waitForURL(BASE + destination.pathname, {waitUntil: 'load'});
                    assert.equal(await page.locator('link[rel="canonical"]').getAttribute('href'),
                        'https://gramlyze.com' + destination.pathname.replace('/courses/english-grammar-theory/lesson/', '/theory/'));
                    row.lesson = destination.pathname;
                    assert.ok(await page.locator('a[href*="/courses/english-grammar-theory"]').count());
                }
                row.screenshot = `${label}-${name}-${mobile ? 'mobile' : 'desktop'}.png`;
                await page.screenshot({path: path.join(output, row.screenshot), fullPage: false, animations: 'disabled'});
                const blocked = new Set(row.blocked.map(r => r.url));
                row.unexpectedFailures = row.failures.filter(r => !blocked.has(r.url)
                    && !(r.error === 'net::ERR_ABORTED' && row.responses.some(s => s.url === r.url && s.method === 'POST' && s.status === 204)));
                row.unexpectedConsole = row.console.filter(r => !(r.blockedResource && blocked.has(r.url)));
                assert.equal(row.unexpectedFailures.length, 0);
                assert.equal(row.unexpectedConsole.filter(r => r.type !== 'warning').length, 0);
                assert.equal(row.responses.filter(r => r.status >= 400).length, 0);
                assert.equal(row.blocked.filter(r => r.reason !== 'nonlocal-blocked').length, 0);
                row.pass = true;
            } catch (e) { row.error = diagnosticError(e); row.pass = false; }
            finally { await context.close(); save(); console.log(JSON.stringify({name, mobile, pass: row.pass, error: row.error})); }
        }
    } finally { await browser.close(); report.finishedAt = new Date().toISOString(); report.pass = report.rows.length === 10 && report.rows.every(r => r.pass); save(); }
    if (!report.pass) process.exitCode = 1;
}
module.exports = {decision};
if (require.main === module) main(process.argv[2]).catch(e => { console.error(diagnosticError(e)); process.exitCode = 1; });
