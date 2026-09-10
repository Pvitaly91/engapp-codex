'use strict';
// Eight fresh local guests. No performance measurements, external navigation or repairs.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {requestDecision, safeUrl, diagnosticError, questionEndpoint} = require('./seo-m4-browser.cjs');
const {progressEvidence, serverBootstrapEvidence} = require('./m32-local-acceptance.cjs');
const BASE = 'http://gramlyze.loc';
const QUESTIONS = '/test/future-perfect/questions';
const THEORY = '/theory/basic-grammar/sentence-types';
const ONE = '/theory/zaimennyky-ta-vkazivni-slova/one-ones';
const COURSE = '/courses/english-grammar-theory';

function decision(value, navigation, redirected, allowed, method = 'GET', stateAllowed = false) {
    const reason = requestDecision(value);
    if (reason) return reason;
    const u = new URL(value);
    if (!['GET', 'HEAD'].includes(method) && !(stateAllowed && method === 'POST' && u.origin === BASE && u.pathname === QUESTIONS + '/state' && !u.search)) return 'unplanned-write';
    if (navigation && (redirected || u.origin !== BASE || !allowed.includes(u.pathname) || u.search)) return 'outside-navigation-plan';
    return null;
}

async function settled(page) {
    await page.waitForTimeout(400);
    await page.evaluate(async () => { await JS_TEST_SAVE_QUEUE; });
    await page.waitForTimeout(200);
}
async function progress(page) {
    return page.evaluate(() => ({answered: state.answered, correct: state.correct, activeCardIdx: state.activeCardIdx,
        order: state.items.map(q => q.uuid || q.id), items: state.items.map(q => ({chosen: q.chosen,
            manual: q.manualInputsBySlot?.map(v => v ?? ''), done: q.done, activeSlot: q.activeSlot}))}));
}

async function main(label, httpFile, followup) {
    assert.match(label || '', /^[a-z0-9-]+$/);
    const accepted = JSON.parse(fs.readFileSync(httpFile));
    assert.equal(accepted.pass, true, 'Completed local GET acceptance required');
    assert.equal(accepted.base, BASE);
    assert.ok(followup === undefined || followup === '--questions-course-only');
    const kinds = followup ? ['questions', 'course'] : ['theory', 'questions', 'course', 'one-ones'];
    const coursePath = accepted.course_path;
    assert.ok(/^\/courses\/english-grammar-theory\/lesson\/[a-z0-9/-]+\/one-ones$/.test(coursePath));
    const out = path.resolve(__dirname, '../../storage/app/seo-m9-local');
    const file = path.join(out, label + '-browser.json');
    const report = {startedAt: new Date().toISOString(), base: BASE, rows: []};
    fs.mkdirSync(out, {recursive: true});
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    const save = () => fs.writeFileSync(file, JSON.stringify(report, null, 2));
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    report.browser = browser.version();
    try {
        for (const mobile of [false, true]) for (const kind of kinds) {
            const startPath = {theory: THEORY, questions: QUESTIONS, course: COURSE, 'one-ones': ONE}[kind];
            const allowed = kind === 'course' ? [COURSE, coursePath] : kind === 'questions' ? [QUESTIONS, '/theory'] : [startPath];
            const row = {kind, mobile, blocked: [], failures: [], console: [], responses: [], screenshots: []};
            report.rows.push(row); save();
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000},
                isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', colorScheme: 'light', serviceWorkers: 'block'});
            await context.route('**/*', async route => {
                const q = route.request();
                const reason = decision(q.url(), q.isNavigationRequest(), !!q.redirectedFrom(), allowed, q.method(), kind === 'questions');
                if (reason) { row.blocked.push({url: safeUrl(q.url()), reason}); await route.abort('blockedbyclient'); }
                else await route.continue();
            });
            await context.addInitScript(() => {
                window.__m9 = {alpine: 0, saves: []};
                addEventListener('alpine:init', () => window.__m9.alpine++);
                const fetchOriginal = window.fetch;
                window.fetch = async function(input, init) {
                    const url = new URL(typeof input === 'string' ? input : input.url, location.href);
                    const record = url.pathname === '/test/future-perfect/questions/state' ? {} : null;
                    if (record) window.__m9.saves.push(record);
                    try {
                        const response = await fetchOriginal.apply(this, arguments);
                        if (record) Object.assign(record, {resolved: true, status: response.status});
                        return response;
                    } catch (error) { if (record) record.rejected = true; throw error; }
                };
            });
            const page = await context.newPage();
            page.setDefaultTimeout(20000);
            const cdp = await context.newCDPSession(page);
            await cdp.send('Network.enable');
            await cdp.send('Network.setBlockedURLs', {urls: ['*://gramlyze.com/*', '*://*.gramlyze.com/*', '*://gramlyze.ub/*', '*://*.gramlyze.ub/*']});
            page.on('pageerror', error => row.console.push({...diagnosticError(error), type: 'pageerror'}));
            page.on('console', msg => { if (['error', 'warning'].includes(msg.type())) row.console.push({...diagnosticError(new Error(msg.text())), type: msg.type(),
                url: safeUrl(msg.location().url), resourceFailure: /Failed to load resource/.test(msg.text())}); });
            page.on('requestfailed', q => row.failures.push({url: safeUrl(q.url()), method: q.method(), error: q.failure()?.errorText}));
            page.on('response', r => row.responses.push({url: safeUrl(r.url()), method: r.request().method(), status: r.status()}));
            const screenshot = async suffix => {
                const name = `${label}-${kind}-${mobile ? 'mobile' : 'desktop'}-${suffix}.png`;
                await page.screenshot({path: path.join(out, name), animations: 'disabled'}); row.screenshots.push(name);
            };
            try {
                const response = await page.goto(BASE + startPath, {waitUntil: 'load', timeout: 60000});
                assert.equal(response.status(), 200);
                assert.match(response.headers()['x-robots-tag'], /noindex/);
                await page.waitForFunction(() => !!window.Alpine);
                if (kind === 'theory') {
                    if (mobile) await page.locator('[data-theory-mobile-nav-toggle]').click();
                    await page.waitForFunction(mobile => {
                        const root = mobile ? document.querySelector('[data-theory-mobile-nav-toggle]')?.parentElement : document.querySelector('[data-theory-desktop-navigation-loader]');
                        const data = root?._x_dataStack?.[0];
                        return data && !data.loading && !data.error && (mobile ? data.loaded : root.querySelector('[x-ref="content"] a'));
                    }, mobile);
                    const nav = page.locator(mobile ? '[data-theory-mobile-nav-panel]' : '[data-theory-desktop-navigation-loader]');
                    row.sidebarLinks = await nav.locator('a').count(); assert.ok(row.sidebarLinks > 10);
                    assert.ok(await nav.locator('[data-theory-nav-current-page="true"]').count());
                    if (mobile) { await page.locator('[data-theory-mobile-nav-toggle]').click(); await page.getByRole('button', {name: 'Меню', exact: true}).click(); }
                    else {
                        const toggle = page.locator('[data-theory-aside] [data-theory-sidebar] > div > button').first();
                        await toggle.click(); assert.equal(await page.locator('[data-theory-layout]').getAttribute('data-collapsed'), 'true');
                        await toggle.click();
                    }
                    await page.locator('.site-header button[\\@click="toggleTheme"]:visible').first().click();
                    await page.reload({waitUntil: 'load'});
                    assert.ok(await page.evaluate(() => document.documentElement.classList.contains('dark') && localStorage.getItem('theme') === 'dark'));
                    row.themeRestored = true;
                } else if (kind === 'questions') {
                    const count = accepted.rows.find(r => r.path === QUESTIONS + '/questions').question_count;
                    await page.waitForFunction(count => typeof state !== 'undefined' && state.items.length === count, count);
                    await settled(page);
                    row.initial = progressEvidence(await progress(page)); assert.equal(row.initial.answered, 0);
                    const endpoint = await page.evaluate(() => ({endpoint: JS_TEST_PERSISTENCE.questionsEndpoint, mode: JS_TEST_PERSISTENCE.mode}));
                    const url = questionEndpoint(endpoint.endpoint, endpoint.mode, QUESTIONS);
                    const api = await page.evaluate(async url => { const r = await fetch(url, {headers: {Accept: 'application/json'}}); const body = await r.json(); return {status: r.status, count: body.questions?.length, robots: r.headers.get('x-robots-tag')}; }, url);
                    row.endpoint = safeUrl(url); row.api = api;
                    assert.equal(api.status, 200); assert.equal(api.count, count); assert.match(api.robots, /noindex/);
                    await screenshot('before-answer');
                    const index = await page.evaluate(() => state.items.findIndex((q, i) => q.answers?.length === 1 && document.querySelector(`article[data-idx="${i}"] button[data-options-toggle]`)));
                    assert.ok(index >= 0);
                    const card = page.locator(`article[data-idx="${index}"]`);
                    await card.locator('button[data-options-toggle]').click();
                    const option = await card.evaluate((el, i) => [...el.querySelectorAll('button[data-opt]')].findIndex(b => b.getAttribute('data-opt') === state.items[i].answers[state.items[i].activeSlot]), index);
                    assert.ok(option >= 0); await card.locator('button[data-opt]').nth(option).click(); await settled(page);
                    row.saved = progressEvidence(await progress(page));
                    row.saveFetches = await page.evaluate(() => __m9.saves);
                    assert.equal(row.saved.answered, 1); assert.equal(row.saved.correct, 1);
                    assert.ok(row.saveFetches.length > 0 && row.saveFetches.every(r => r.resolved && r.status === 204 && !r.rejected));
                    await page.reload({waitUntil: 'load'});
                    await page.waitForFunction(count => typeof state !== 'undefined' && state.items.length === count, count); await settled(page);
                    row.reload = progressEvidence(await progress(page)); assert.deepEqual(row.reload, row.saved);
                    await page.goto(BASE + '/theory', {waitUntil: 'load'});
                    await page.evaluate(() => { localStorage.clear(); sessionStorage.clear(); });
                    const restored = await page.goto(BASE + QUESTIONS, {waitUntil: 'load'});
                    row.serverBootstrap = serverBootstrapEvidence(await restored.text());
                    await page.waitForFunction(count => typeof state !== 'undefined' && state.items.length === count, count); await settled(page);
                    row.restored = progressEvidence(await progress(page));
                    assert.deepEqual(row.serverBootstrap, row.saved); assert.deepEqual(row.restored, row.saved);
                    row.serverOnlyRestored = true;
                } else if (kind === 'course') {
                    await page.waitForFunction(() => !!window.TheoryCourseProgress);
                    row.progress = await page.evaluate(target => {
                        const m = __THEORY_COURSE_MANIFEST__, store = TheoryCourseProgress.createStore(m.course.slug, m.lessons);
                        const lesson = m.lessons.find(l => new URL(l.lesson_url, location.href).pathname === target);
                        if (!lesson) throw new Error('Native course lesson not found');
                        const before = store.getLessonStatus(lesson.lesson_slug);
                        if (before === 'locked' && lesson.previous_lesson_slug) store.markLessonCompleted(lesson.previous_lesson_slug, {scorePercent: 100, correct: 1, total: 1});
                        return {before, after: store.getLessonStatus(lesson.lesson_slug)};
                    }, coursePath);
                    assert.equal(row.progress.before, 'locked'); assert.equal(row.progress.after, 'current');
                    const linkIndex = await page.locator('a[data-theory-course-action]').evaluateAll((links, target) => links.findIndex(link => new URL(link.href).pathname === target), coursePath);
                    assert.ok(linkIndex >= 0);
                    const link = page.locator('a[data-theory-course-action]').nth(linkIndex);
                    row.courseLink = {href: safeUrl(await link.evaluate(el => el.href)), ariaDisabled: await link.getAttribute('aria-disabled')};
                    await link.click();
                    await page.waitForURL(BASE + coursePath); await page.locator('[data-theory-lesson-content]').waitFor({state: 'visible'});
                    assert.equal(await page.locator('link[rel="canonical"]').getAttribute('href'), 'https://gramlyze.com' + ONE);
                    row.nativeNavigation = true;
                }
                if (kind === 'one-ones' || kind === 'course') {
                    const source = JSON.parse(fs.readFileSync(path.resolve(__dirname, '../../database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder/definition.json')));
                    row.blocks = [];
                    for (const order of [2, 5, 7, 8, 9]) {
                        const body = source.page.blocks[order - 1].body;
                        const match = await page.locator('[id^="block-"]').evaluateAll((nodes, body) => {
                            const normalize = t => t.replace(/\s+/g, ' ').trim();
                            const expected = new DOMParser().parseFromString(body, 'text/html').body;
                            const node = nodes.find(n => n.querySelector('.prose') && normalize(n.querySelector('.prose').textContent) === normalize(expected.textContent));
                            return node ? {id: node.id, items: node.querySelectorAll('.prose li').length, expectedItems: expected.querySelectorAll('li').length} : null;
                        }, body);
                        assert.ok(match); assert.equal(match.items, match.expectedItems);
                        await page.locator('#' + match.id).scrollIntoViewIfNeeded(); assert.ok(await page.locator('#' + match.id).isVisible());
                        row.blocks.push({order, ...match});
                        if ([5, 9].includes(order)) await screenshot('block-' + order);
                    }
                }
                row.alpine = await page.evaluate(() => ({inits: __m9.alpine, version: Alpine.version, livewire: !!Livewire}));
                assert.equal(row.alpine.inits, 1); assert.ok(row.alpine.livewire);
                row.mainElements = await page.locator('main').count();
                assert.ok(await page.locator('main').first().innerText());
                assert.equal(await page.locator('h1').count(), 1);
                assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 2));
                if (kind !== 'questions') await screenshot('final');
                row.fontLimitations = row.failures.filter(r => /^https:\/\/fonts\.(googleapis|gstatic)\.com\//.test(r.url));
                let acknowledgements = row.responses.filter(r => r.url === BASE + QUESTIONS + '/state' && r.method === 'POST' && r.status === 204).length;
                row.confirmedStateAborts = 0;
                const unexpected = row.failures.filter(r => {
                    if (row.fontLimitations.includes(r)) return false;
                    if (row.serverOnlyRestored && r.url === BASE + QUESTIONS + '/state' && r.method === 'POST' && r.error === 'net::ERR_ABORTED' && acknowledgements > 0) {
                        acknowledgements--; row.confirmedStateAborts++; return false;
                    }
                    return true;
                });
                assert.deepEqual(row.blocked, []); assert.deepEqual(unexpected, []);
                assert.deepEqual(row.responses.filter(r => r.status >= 400), []);
                assert.deepEqual(row.console.filter(r => r.type === 'pageerror' || !r.resourceFailure || row.fontLimitations.length === 0), []);
                assert.ok(row.responses.some(r => /\/build\/assets\/catalog-public-.*\.css$/.test(r.url) && r.status === 200));
                assert.ok(row.responses.some(r => /\/build\/assets\/catalog-public-.*\.js$/.test(r.url) && r.status === 200));
                row.pass = true;
            } catch (error) { row.pass = false; row.error = {...diagnosticError(error), callsite: error.stack?.match(/seo-m9-browser\.cjs:\d+:\d+/g)}; }
            finally { await context.close(); save(); console.log(JSON.stringify({kind, mobile, pass: row.pass, error: row.error})); }
        }
    } finally { await browser.close(); report.finishedAt = new Date().toISOString(); }
    report.pass = report.rows.length === kinds.length * 2 && report.rows.every(r => r.pass); save();
    console.log(JSON.stringify({file, pass: report.pass}));
    return report.pass;
}

module.exports = {decision};
if (require.main === module) main(process.argv[2], process.argv[3], process.argv[4]).then(ok => { process.exitCode = ok ? 0 : 1; })
    .catch(error => { console.error(diagnosticError(error)); process.exitCode = 1; });
