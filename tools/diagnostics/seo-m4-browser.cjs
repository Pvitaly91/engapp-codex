// M4 functional-only local acceptance: eight separate guests, no performance probe.
'use strict';
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');

const BASE = 'http://gramlyze.loc';
const SEO_ORIGIN = 'https://gramlyze.com'; // Metadata comparison only; never requested.
const PAGES = Object.freeze([
    ['questions', '/test/future-perfect/questions', 'test'],
    ['forms', '/test/future-perfect/forms', 'test'],
    ['courses', '/courses', 'catalog'],
    ['theory-course', '/courses/english-grammar-theory', 'course'],
].map(Object.freeze));
const digest = value => crypto.createHash('sha256').update(String(value)).digest('hex');
const diagnosticError = error => ({
    type: ['AssertionError', 'TimeoutError', 'Error', 'TypeError'].includes(error?.name) ? error.name : 'DiagnosticError',
    messageSha256: digest(error?.message || 'Diagnostic failure'),
});

function safeUrl(value) {
    try { const url = new URL(value); return url.origin + url.pathname; }
    catch { return '[invalid-url]'; }
}

function navigationUrl(pagePath) {
    assert.ok(PAGES.some(([, candidate]) => candidate === pagePath), 'Only the four planned local pages may be opened');
    return BASE + pagePath;
}

function requestDecision(value, {navigation = false, redirected = false} = {}) {
    let url;
    try { url = new URL(value); } catch { return 'invalid-url'; }
    const host = url.hostname.toLowerCase().replace(/\.+$/, '');
    if (['gramlyze.com', 'gramlyze.ub'].some(domain => host === domain || host.endsWith('.' + domain))) return 'forbidden-host';
    if (host === 'cdn.tailwindcss.com' || (host === 'unpkg.com' && /^\/(?:alpinejs|@alpinejs)(?:\/|@|$)/.test(url.pathname))) return 'retired-runtime-cdn';
    if (url.username || url.password) return 'credentialed-url';
    if (navigation && redirected) return 'document-redirect';
    if (navigation && (url.origin !== BASE || !PAGES.some(([, pagePath]) => pagePath === url.pathname) || url.search || url.hash)) return 'outside-navigation-plan';
    return null;
}

function questionEndpoint(endpoint, mode, pagePath) {
    assert.ok(PAGES.some(([, candidate, kind]) => candidate === pagePath && kind === 'test'), 'Expected a planned test page');
    const url = new URL(endpoint, BASE);
    assert.ok(url.origin === BASE && !url.username && !url.password && !url.search && !url.hash
        && url.pathname === pagePath + '/questions', 'App questions endpoint must be the exact local public data route');
    assert.equal(mode, 'saved-test-js-v2', 'Expected the normal main all-level test mode');
    url.searchParams.set('mode', mode);
    return url.href;
}

function questionEvidence(payload) {
    assert.ok(payload && Array.isArray(payload.questions), 'Questions response must contain an array');
    return {count: payload.questions.length}; // No IDs, text, choices, answers, tokens or state.
}

function questionUiEvidence(value) {
    const fields = ['initial', 'rendered', 'cards', 'answered', 'enabledInputs'];
    assert.ok(value && fields.every(field => Number.isSafeInteger(value[field]) && value[field] >= 0), 'Question UI evidence requires numeric counters');
    return Object.fromEntries(fields.map(field => [field, value[field]]));
}

function networkEvidence(record, pagePath) {
    const stateUrl = BASE + pagePath + '/state';
    let state204s = record.responses.filter(row => row.url === stateUrl && row.status === 204 && row.method === 'POST').length;
    let expectedState204Aborts = 0;
    const unexpectedFailures = record.failures.filter(row => {
        if (row.url === stateUrl && row.method === 'POST' && row.error === 'net::ERR_ABORTED' && state204s > 0) {
            state204s--; expectedState204Aborts++; return false;
        }
        return true;
    });
    const unsuccessfulResponses = record.responses.filter(row => row.status >= 400 || (row.type === 'document' && row.status !== 200));
    const scriptErrors = record.console.filter(row => row.type === 'pageerror' || row.type === 'error' || row.duplicateRuntime);
    return {expectedState204Aborts, unexpectedFailures: unexpectedFailures.length,
        unsuccessfulResponses: unsuccessfulResponses.length, scriptErrors: scriptErrors.length,
        pass: record.blocked.length === 0 && unexpectedFailures.length === 0 && unsuccessfulResponses.length === 0 && scriptErrors.length === 0};
}

async function prepare(browser, mobile, record) {
    const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000},
        isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', colorScheme: 'light', serviceWorkers: 'block'});
    // Installed before creating/navigating any page, including popup navigations.
    // Routing changes cache behavior: this is deliberately NOT a performance run.
    await context.route('**/*', async route => {
        const request = route.request();
        const reason = requestDecision(request.url(), {navigation: request.isNavigationRequest(), redirected: !!request.redirectedFrom()});
        if (reason) { record.blocked.push({url: safeUrl(request.url()), reason}); await route.abort('blockedbyclient'); }
        else await route.continue();
    });
    await context.addInitScript(() => {
        window.__m4AlpineInits = 0;
        addEventListener('alpine:init', () => window.__m4AlpineInits++);
    });
    const page = await context.newPage();
    page.on('console', message => {
        if (!['error', 'warning'].includes(message.type())) return;
        const text = message.text();
        record.console.push({type: message.type(), textSha256: digest(text), duplicateRuntime: /multiple instances|already initialized/i.test(text)});
    });
    page.on('pageerror', error => {
        const failure = diagnosticError(error);
        record.console.push({type: 'pageerror', errorType: failure.type, messageSha256: failure.messageSha256});
    });
    page.on('requestfailed', request => record.failures.push({url: safeUrl(request.url()), method: request.method(),
        error: request.failure()?.errorText === 'net::ERR_ABORTED' ? 'net::ERR_ABORTED' : 'request-failed',
        errorSha256: digest(request.failure()?.errorText || 'request-failed')}));
    page.on('response', response => record.responses.push({url: safeUrl(response.url()), method: response.request().method(),
        type: response.request().resourceType(), status: response.status(), mime: response.headers()['content-type']?.split(';')[0]}));
    return {context, page};
}

async function menuEvidence(page, mobile) {
    const region = page.locator(mobile ? '#site-header [x-show="mobile"]' : '#site-header > div > nav').first();
    const button = page.getByRole('button', {name: 'Меню', exact: true});
    if (mobile) { await button.click(); await region.waitFor({state: 'visible'}); }
    const opened = await region.evaluate(element => {
        const visible = [...element.querySelectorAll('a[href]')].filter(link => link.getBoundingClientRect().width > 0 && link.getBoundingClientRect().height > 0);
        return {visibleLinks: visible.length, theory: visible.some(link => new URL(link.href).origin === location.origin && new URL(link.href).pathname === '/theory'),
            courses: visible.some(link => new URL(link.href).origin === location.origin && new URL(link.href).pathname === '/courses')};
    });
    if (mobile) { await button.click(); await region.waitFor({state: 'hidden'}); }
    return {...opened, closedAfterToggle: !mobile || !(await region.isVisible())};
}

async function screenshotBeforeNetworkCheck(page, screenshotPath, row, check) {
    // Content/guest/privacy checks have passed before this point. Keep the public
    // viewport even when a subsequent strict network assertion must fail.
    assert.ok(!fs.existsSync(screenshotPath), 'Screenshot label already exists');
    await page.screenshot({path: screenshotPath, fullPage: false, animations: 'disabled'});
    row.screenshot = path.basename(screenshotPath);
    check('no-unexpected-network-or-script-failures', row.network.pass);
}

async function main(label = process.argv[2]) {
    assert.match(label || '', /^[a-z0-9][a-z0-9_-]*$/i, 'Supply a unique evidence label');
    const output = path.resolve(__dirname, '../../storage/app/seo-m4-local');
    fs.mkdirSync(output, {recursive: true});
    const evidence = path.join(output, `${label}-browser.json`);
    const report = {schema: 'gramlyze-m4-browser-v1', label, base: BASE, startedAt: new Date().toISOString(),
        policy: 'Functional only; one new guest per case; no answers submitted; no redirects; production blocked before navigation; no HTML or session dumps', rows: []};
    fs.writeFileSync(evidence, JSON.stringify(report, null, 2), {flag: 'wx'});
    const save = () => fs.writeFileSync(evidence, JSON.stringify(report, null, 2));
    let browser;
    try {
        const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
        browser = await chromium.launch({headless: true,
            ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
        report.browser = browser.version(); save();
        for (const mobile of [false, true]) for (const [name, pagePath, kind] of PAGES) {
            const row = {name, path: pagePath, mobile, viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000},
                checks: [], blocked: [], console: [], responses: [], failures: []};
            report.rows.push(row); save();
            const check = (name, pass) => { row.checks.push({name, pass: !!pass}); assert.ok(pass, name); };
            let context;
            try {
                const prepared = await prepare(browser, mobile, row); context = prepared.context;
                const page = prepared.page;
                const response = await page.goto(navigationUrl(pagePath), {waitUntil: 'load', timeout: 45000});
                row.document = {status: response?.status(), siteMode: response?.headers()['x-site-mode'],
                    noindex: /\bnoindex\b/i.test(response?.headers()['x-robots-tag'] || '')};
                check('guest-local-document-200-development-noindex', row.document.status === 200 && page.url() === BASE + pagePath
                    && row.document.siteMode === 'development' && row.document.noindex);
                await page.evaluate(() => document.fonts.ready);
                await page.waitForFunction(() => !!window.Alpine && window.__m4AlpineInits === 1);
                row.content = await page.evaluate(() => {
                    const main = document.querySelector('main');
                    const canonicals = [...document.querySelectorAll('link[rel="canonical"]')];
                    return {headings: main?.querySelectorAll('h1').length || 0, textLength: main?.innerText.length || 0,
                        comingSoon: /coming\s+soon|незабаром|скоро\s+буде/i.test(main?.querySelector('h1')?.innerText || ''),
                        canonicalCount: canonicals.length, canonicalCorrect: canonicals.length === 1 && canonicals[0].href === 'https://gramlyze.com' + location.pathname,
                        overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth), alpineInits: window.__m4AlpineInits};
                });
                check('substantive-content-canonical-and-no-horizontal-overflow', row.content.headings === 1 && row.content.textLength > 100
                    && !row.content.comingSoon && row.content.canonicalCorrect && row.content.overflow <= 1);
                row.menu = await menuEvidence(page, mobile);
                check('public-menu-theory-course-links-and-mobile-toggle', row.menu.visibleLinks >= 2 && row.menu.theory && row.menu.courses && row.menu.closedAfterToggle);
                if (kind === 'test') {
                    await page.waitForFunction(() => Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__) && window.__INITIAL_JS_TEST_QUESTIONS__.length > 0
                        && typeof state !== 'undefined' && state.items.length === window.__INITIAL_JS_TEST_QUESTIONS__.length);
                    const settings = await page.evaluate(() => ({endpoint: window.JS_TEST_PERSISTENCE?.questionsEndpoint, mode: window.JS_TEST_PERSISTENCE?.mode}));
                    const endpoint = questionEndpoint(settings.endpoint, settings.mode, pagePath);
                    row.questions = questionUiEvidence(await page.evaluate(() => ({initial: window.__INITIAL_JS_TEST_QUESTIONS__.length, rendered: state.items.length,
                        cards: document.querySelectorAll('#questions article').length, answered: state.answered,
                        enabledInputs: document.querySelectorAll('#questions button:not(:disabled), #questions input:not(:disabled)').length})));
                    const dataResponse = await context.request.get(endpoint, {headers: {Accept: 'application/json'}, maxRedirects: 0, timeout: 45000});
                    row.questionsResponse = {path: pagePath + '/questions', mode: settings.mode, method: 'GET', status: dataResponse.status(),
                        mime: dataResponse.headers()['content-type']?.split(';')[0], redirectsFollowed: false};
                    if (dataResponse.status() === 200 && row.questionsResponse.mime === 'application/json') {
                        row.questionsResponse.evidence = questionEvidence(await dataResponse.json());
                    }
                    await dataResponse.dispose();
                    check('nonempty-main-test-ui-and-real-questions-endpoint-without-answer', row.questions.initial > 0 && row.questions.rendered === row.questions.initial
                        && row.questions.cards > 0 && row.questions.enabledInputs > 0 && row.questions.answered === 0
                        && row.questionsResponse.status === 200 && row.questionsResponse.evidence?.count === row.questions.initial);
                } else if (kind === 'catalog') {
                    row.catalog = await page.locator('main').evaluate(element => ({
                        courseLinks: element.querySelectorAll('a[data-course-slug]').length,
                        theoryCourseAvailable: [...element.querySelectorAll('a[data-course-slug="english-grammar-theory"]')]
                            .some(link => new URL(link.href).origin === location.origin && new URL(link.href).pathname === '/courses/english-grammar-theory'
                                && link.getAttribute('aria-disabled') !== 'true' && getComputedStyle(link).pointerEvents !== 'none'),
                    }));
                    check('catalog-has-available-canonical-course-link', row.catalog.courseLinks > 0 && row.catalog.theoryCourseAvailable);
                } else {
                    await page.waitForFunction(() => !!window.TheoryCourseProgress && window.__THEORY_COURSE_MANIFEST__?.lessons?.length > 0);
                    row.course = await page.evaluate(() => {
                        const cta = document.querySelector('#theory-course-hero-cta');
                        const lessons = window.__THEORY_COURSE_MANIFEST__.lessons;
                        return {lessons: lessons.length, cards: document.querySelectorAll('[data-theory-course-card]').length,
                            firstLessonAvailable: !!cta && new URL(cta.href).origin === location.origin
                                && new URL(cta.href).pathname.startsWith('/courses/english-grammar-theory/lesson/')
                                && cta.getAttribute('aria-disabled') !== 'true' && getComputedStyle(cta).pointerEvents !== 'none',
                            lessonLinks: document.querySelectorAll('[data-theory-course-action][href]').length};
                    });
                    check('course-material-and-first-lesson-link-available', row.course.lessons > 0 && row.course.cards === row.course.lessons
                        && row.course.firstLessonAvailable && row.course.lessonLinks === row.course.lessons);
                }
                await page.waitForTimeout(300);
                row.network = networkEvidence(row, pagePath);
                const screenshot = `${label}-${name}-${mobile ? 'mobile' : 'desktop'}.png`;
                await screenshotBeforeNetworkCheck(page, path.join(output, screenshot), row, check);
            } catch (error) { row.error = diagnosticError(error); }
            finally {
                if (context) await context.close();
                row.pass = !row.error && row.checks.length === 5 && row.checks.every(check => check.pass);
                save();
                console.log(JSON.stringify({name, mobile, pass: row.pass, checks: row.checks.length,
                    failed: row.checks.filter(check => !check.pass).map(check => check.name), error: row.error}));
            }
        }
    } catch (error) { report.error = diagnosticError(error); }
    finally {
        if (browser) await browser.close(); report.finishedAt = new Date().toISOString();
        report.pass = !report.error && report.rows.length === 8 && report.rows.every(row => row.pass); save();
    }
    console.log(JSON.stringify({evidence, pass: report.pass, scenarios: report.rows.length}));
    if (!report.pass) process.exitCode = 1;
    return report;
}

module.exports = {BASE, SEO_ORIGIN, PAGES, safeUrl, navigationUrl, requestDecision, questionEndpoint, questionEvidence, questionUiEvidence, networkEvidence, diagnosticError, prepare, screenshotBeforeNetworkCheck, main};
if (require.main === module) main().catch(error => { console.error(JSON.stringify(diagnosticError(error))); process.exitCode = 1; });
