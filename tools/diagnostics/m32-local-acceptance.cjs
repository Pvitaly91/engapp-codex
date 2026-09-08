// M3.2 final local UI acceptance only. No LCP/CLS values are read or reported.
// Six isolated guest contexts, six private screenshots, no full HTML/state dumps.
'use strict';
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');

const BASE = 'http://gramlyze.loc';
const PAGES = [
    ['sentence-types', '/theory/basic-grammar/sentence-types'],
    ['present-perfect', '/theory/tenses/present-perfect/present-perfect-forms'],
    ['questions', '/test/future-perfect/questions'],
];
const digest = value => crypto.createHash('sha256').update(JSON.stringify(value)).digest('hex');
const diagnosticError = error => ({
    type: ['AssertionError', 'TimeoutError', 'Error', 'TypeError'].includes(error?.name) ? error.name : 'DiagnosticError',
    messageSha256: digest(String(error?.message || 'Diagnostic failure')),
});

async function settled(page) {
    await page.waitForTimeout(400);
    await page.evaluate(async () => { await JS_TEST_SAVE_QUEUE; });
    await page.waitForTimeout(200);
}
async function readyQuestions(page) {
    await page.waitForFunction(() => window.__INITIAL_JS_TEST_QUESTIONS__?.length === 84
        && typeof state !== 'undefined' && state.items.length === 84, null, {timeout: 30000});
    await settled(page);
}
async function progress(page) {
    // Retained in process memory only. Persist only the digest and counters below.
    // Normalize untouched input slots exactly as the accepted M2 snapshot helper.
    return page.evaluate(() => ({answered: state.answered, correct: state.correct, activeCardIdx: state.activeCardIdx,
        order: state.items.map(q => q.uuid || q.id), items: state.items.map(q => ({chosen: q.chosen,
            manual: q.manualInputsBySlot?.map(value => value ?? ''), done: q.done, activeSlot: q.activeSlot}))}));
}
function progressEvidence(snapshot) {
    assert.ok(snapshot && Array.isArray(snapshot.items)
        && ['answered', 'correct', 'activeCardIdx'].every(key => Number.isSafeInteger(snapshot[key]) && snapshot[key] >= 0),
    'Progress evidence requires numeric counters and an item array');
    return {hash: digest(snapshot), answered: snapshot.answered,
        correct: snapshot.correct, activeCardIdx: snapshot.activeCardIdx, itemCount: snapshot.items.length};
}

function serverBootstrapEvidence(html) {
    // Laravel restores session state in the document, not a GET /state request.
    // Parse only the JSON saved field in memory; never persist the surrounding
    // script, which also contains CSRF and browser storage keys.
    const match = html.match(/window\.JS_TEST_PERSISTENCE\s*=\s*\{[\s\S]*?^\s*saved:\s*([^\r\n]+),\s*\r?\n\};/m);
    let saved;
    try { saved = match && JSON.parse(match[1]); } catch { throw new Error('Invalid server bootstrap JSON'); }
    assert.ok(saved && Array.isArray(saved.items), 'Server document must contain saved session items');
    return progressEvidence({answered: saved.answered, correct: saved.correct, activeCardIdx: saved.activeCardIdx,
        order: saved.items.map(q => q.uuid || q.id), items: saved.items.map(q => ({chosen: q.chosen,
            manual: q.manualInputsBySlot?.map(value => value ?? ''), done: q.done, activeSlot: q.activeSlot}))});
}

async function theoryReady(page, mobile) {
    if (mobile) await page.locator('[data-theory-mobile-nav-toggle]').click();
    await page.waitForFunction(mobile => {
        const root = mobile ? document.querySelector('[data-theory-mobile-nav-toggle]')?.parentElement
            : document.querySelector('[data-theory-desktop-navigation-loader]');
        const data = root?._x_dataStack?.[0];
        return data && !data.loading && !data.error && (mobile ? data.loaded : root.querySelector('[x-ref="content"] a'));
    }, mobile, {timeout: 30000});
    return page.locator(mobile ? '[data-theory-mobile-nav-panel]' : '[data-theory-desktop-navigation-loader]');
}

async function main(label = process.argv[2], pageFilter = process.argv[3]) {
    assert.match(label || '', /^[a-z0-9][a-z0-9_-]*$/i, 'Supply a unique evidence label');
    assert.ok(pageFilter === undefined || pageFilter === '--page=questions', 'Only the bounded Questions follow-up is supported');
    const selectedPages = pageFilter ? PAGES.filter(([name]) => name === 'questions') : PAGES;
    // Require the existing helper only after label validation. Its pre-navigation
    // CDP denylist and isolated guest policy are reused, not bypassed by routing.
    const {prepare, loaded} = require('./public-assets-browser.cjs');
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const output = path.resolve('storage/app/seo-m3-2-local');
    fs.mkdirSync(output, {recursive: true});
    const evidence = path.join(output, `${label}-ui-acceptance.json`);
    assert.ok(!fs.existsSync(evidence), 'Evidence label already exists');
    const report = {label, base: BASE, startedAt: new Date().toISOString(), kind: 'functional-only-no-performance-claim', rows: []};
    // Console text can originate in application data; keep its type/count/hash,
    // not potentially private answer material, in the persisted UI evidence.
    const save = () => fs.writeFileSync(evidence, JSON.stringify(report,
        (key, value) => key === 'text' && typeof value === 'string' ? `[sha256:${digest(value)}]` : value, 2));
    const browser = await chromium.launch({headless: true,
        ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    report.browser = browser.version(); save();
    try {
        for (const mobile of [false, true]) for (const [name, url] of selectedPages) {
            const row = {name, url, mobile, checks: [], blocked: [], console: [], responses: [], failures: [], stateResponses: []};
            report.rows.push(row); save();
            const check = (name, pass, details) => {
                row.checks.push({name, pass: !!pass, ...(details === undefined ? {} : {details})});
                assert.ok(pass, name);
            };
            const {context, page} = await prepare(browser, {mobile}, row);
            const screenshot = `${label}-${name}-${mobile ? 'mobile' : 'desktop'}.png`;
            let statePhase = 'initial';
            page.on('response', response => {
                const target = new URL(response.url());
                if (target.origin === BASE && target.pathname === '/test/future-perfect/questions/state') {
                    row.stateResponses.push({phase: statePhase, method: response.request().method(), status: response.status()});
                }
            });
            try {
                const response = await page.goto(BASE + url, {waitUntil: 'load', timeout: 45000});
                assert.equal(response.status(), 200, 'local document HTTP200');
                await loaded(page);
                if (name !== 'questions') {
                    row.content = await page.locator('[data-theory-main]').evaluate(element => ({
                        headings: element.querySelectorAll('h1').length,
                        textLength: element.innerText.length,
                        tables: element.querySelectorAll('table').length,
                        tableCells: element.querySelectorAll('td,th').length,
                        formatted: element.querySelectorAll('strong,em,b,i').length,
                        visibleLiteralHtml: /<\/?(?:p|strong|em|span|table|tr|td|br)\b[^>]*>/i.test(element.innerText),
                        width: element.getBoundingClientRect().width,
                        pageOverflow: Math.max(0, document.documentElement.scrollWidth - innerWidth),
                    }));
                    check('rendered-learning-content-and-formatting', row.content.headings === 1 && row.content.textLength > 1000
                        && row.content.tables > 0 && row.content.tableCells > 0 && row.content.formatted > 0
                        && !row.content.visibleLiteralHtml && row.content.width > 0 && row.content.pageOverflow <= 1);
                    const region = await theoryReady(page, mobile);
                    await page.waitForTimeout(500);
                    row.navigation = await region.evaluate(element => {
                        const scroll = element.querySelector('[data-theory-sidebar-scroll]');
                        return {links: element.querySelectorAll('a').length,
                            active: !!element.querySelector('[data-theory-nav-current-page="true"]'),
                            clientHeight: scroll?.clientHeight, scrollHeight: scroll?.scrollHeight,
                            overflowY: scroll && getComputedStyle(scroll).overflowY};
                    });
                    if (name === 'present-perfect') {
                        const search = region.locator('[data-theory-sidebar-search-input]');
                        await search.fill('Perfect'); await page.waitForTimeout(150);
                        const matched = await region.locator('mark').count() > 0;
                        await region.locator('[data-theory-sidebar-search-clear]').click();
                        check('sidebar-search-and-clear', matched && await search.inputValue() === '');
                    }
                    if (mobile) {
                        row.navigation.openAria = await page.locator('[data-theory-mobile-nav-toggle]').getAttribute('aria-expanded');
                        await page.locator('[data-theory-mobile-nav-toggle]').click(); await page.waitForTimeout(250);
                        row.navigation.closedHeight = await region.evaluate(element => element.getBoundingClientRect().height);
                        check('mobile-menu-links-scroll-and-no-closed-space', row.navigation.links > 10 && row.navigation.active
                            && row.navigation.clientHeight > 0 && row.navigation.scrollHeight > row.navigation.clientHeight
                            && row.navigation.openAria === 'true' && row.navigation.closedHeight === 0);
                    } else {
                        const toggle = page.locator('[data-theory-aside] [data-theory-sidebar] > div > button').first();
                        await toggle.click(); await page.waitForTimeout(300);
                        const collapsed = await page.locator('[data-theory-layout]').getAttribute('data-collapsed') === 'true';
                        await toggle.click(); await page.waitForTimeout(300);
                        check('desktop-sidebar-links-scroll-collapse', row.navigation.links > 10 && row.navigation.active
                            && ['auto', 'scroll'].includes(row.navigation.overflowY) && row.navigation.clientHeight > 0
                            && row.navigation.scrollHeight > row.navigation.clientHeight && collapsed
                            && await page.locator('[data-theory-layout]').getAttribute('data-collapsed') === 'false');
                    }
                    if (name === 'sentence-types') {
                        if (mobile) await page.getByRole('button', {name: 'Меню', exact: true}).click();
                        await page.locator('.site-header button[\\@click="toggleTheme"]:visible').first().click();
                        await page.waitForTimeout(200);
                        const toggled = await page.evaluate(() => document.documentElement.classList.contains('dark'));
                        await page.reload({waitUntil: 'load'}); await loaded(page);
                        check('theme-toggle-and-reload-persistence', toggled
                            && await page.evaluate(() => document.documentElement.classList.contains('dark') && localStorage.getItem('theme') === 'dark'));
                    }
                    row.screenshot = screenshot;
                    await page.screenshot({path: path.join(output, screenshot)});
                } else {
                    await readyQuestions(page);
                    const initial = await progress(page);
                    row.initial = progressEvidence(initial);
                    check('fresh-guest-question-bank84', initial.items.length === 84 && initial.answered === 0 && initial.correct === 0);
                    // Before selecting any answer: the screenshot cannot disclose
                    // a correct choice selected by this diagnostic.
                    row.screenshot = screenshot; row.screenshotPhase = 'before-answer-selection';
                    await page.screenshot({path: path.join(output, screenshot)});
                    const index = await page.evaluate(() => state.items.findIndex((q, i) => q.answers?.length === 1
                        && document.querySelector(`article[data-idx="${i}"] button[data-options-toggle]`)));
                    assert.ok(index >= 0, 'one-slot option card exists');
                    const card = page.locator(`article[data-idx="${index}"]`);
                    await card.locator('button[data-options-toggle]').click();
                    // Return a numeric DOM index, not answer text; even a failed
                    // Playwright locator cannot print a correct answer into logs.
                    const optionIndex = await card.evaluate((element, index) => {
                        const question = state.items[index];
                        return [...element.querySelectorAll('button[data-opt]')]
                            .findIndex(button => button.getAttribute('data-opt') === question.answers[question.activeSlot]);
                    }, index);
                    assert.ok(optionIndex >= 0, 'correct option is rendered');
                    statePhase = 'answer';
                    await card.locator('button[data-opt]').nth(optionIndex).click();
                    await settled(page);
                    const expected = await progress(page);
                    row.saved = progressEvidence(expected);
                    check('one-correct-answer-saved', expected.answered === 1 && expected.correct === 1
                        && await page.evaluate(i => state.items[i].done && state.items[i].feedback === 'correct', index));
                    const keys = await page.evaluate(() => window.JS_TEST_PERSISTENCE.storageKeys);
                    await page.goto(BASE + '/theory', {waitUntil: 'load'}); await loaded(page);
                    row.browserStorageCleared = await page.evaluate(keys => {
                        localStorage.clear(); sessionStorage.clear();
                        return keys.every(key => localStorage.getItem(key) === null && sessionStorage.getItem(key) === null);
                    }, keys);
                    statePhase = 'server-restore';
                    const restoreResponse = await page.goto(BASE + url, {waitUntil: 'load'});
                    row.restoreDocument = {status: restoreResponse.status(), method: restoreResponse.request().method(),
                        source: 'saved session JSON embedded in Laravel document'};
                    row.serverBootstrap = serverBootstrapEvidence(await restoreResponse.text());
                    await readyQuestions(page);
                    const restored = await progress(page);
                    row.restored = progressEvidence(restored);
                    row.cookiesKeptInMemoryOnly = true;
                    check('server-only-restore-same-order-answer-and-counters', row.browserStorageCleared
                        && row.saved.hash === row.restored.hash && restored.answered === 1 && restored.correct === 1
                        && row.serverBootstrap.hash === row.saved.hash
                        && row.restoreDocument.method === 'GET' && row.restoreDocument.status === 200);
                }
                const alpine = await page.evaluate(() => ({inits: window.__m3?.alpineInits, livewire: !!window.Livewire, version: window.Alpine?.version}));
                row.alpine = alpine;
                // Only the established state204/ERR_ABORTED pairing is expected.
                const stateUrl = `${BASE}/test/future-perfect/questions/state`;
                let state204s = row.responses.filter(response => response.url === stateUrl && response.status === 204).length;
                row.expectedState204Aborts = 0;
                const unexpectedFailures = row.failures.filter(failure => {
                    if (failure.url === stateUrl && failure.error === 'net::ERR_ABORTED' && state204s > 0) {
                        state204s--; row.expectedState204Aborts++; return false;
                    }
                    return true;
                });
                check('built-assets-fonts-single-Alpine-and-local-guards', alpine.inits === 1 && alpine.livewire && !!alpine.version
                    && row.blocked.length === 0 && unexpectedFailures.length === 0
                    && !row.console.some(entry => entry.type === 'pageerror' || entry.type === 'error' || /multiple instances|already initialized/i.test(entry.text))
                    && row.responses.some(response => /\/build\/assets\/catalog-public-[^/]+\.css$/.test(response.url) && response.status === 200)
                    && row.responses.some(response => /\/build\/assets\/catalog-public-[^/]+\.js$/.test(response.url) && response.status === 200)
                    && row.responses.some(response => response.url === 'https://fonts.googleapis.com/css2' && response.status === 200)
                    && row.responses.some(response => response.url.startsWith('https://fonts.gstatic.com/') && response.status === 200));
            } catch (error) { row.error = diagnosticError(error); }
            finally {
                row.pass = !row.error && row.checks.length === 4 && row.checks.every(check => check.pass);
                await context.close(); save();
                console.log(JSON.stringify({name, mobile, pass: row.pass, checks: row.checks.length,
                    failed: row.checks.filter(check => !check.pass).map(check => check.name), error: row.error}));
            }
        }
    } finally {
        await browser.close(); report.finishedAt = new Date().toISOString();
        report.pass = report.rows.length === selectedPages.length * 2 && report.rows.every(row => row.pass); save();
    }
    console.log(JSON.stringify({evidence, pass: report.pass, scenarios: report.rows.length,
        checks: report.rows.reduce((sum, row) => sum + row.checks.length, 0)}));
    if (!report.pass) process.exitCode = 1;
    return report;
}

module.exports = {main, progressEvidence, serverBootstrapEvidence, diagnosticError};
if (require.main === module) main().catch(error => { console.error(JSON.stringify(diagnosticError(error))); process.exitCode = 1; });
