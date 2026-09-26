'use strict';

// Eight isolated production guest scenarios.  Persist only sanitized counters,
// hashes, URLs and screenshots; never cookies, CSRF, answers or full HTML.
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');
const {progressEvidence, serverBootstrapEvidence} = require('./m32-local-acceptance.cjs');

const HOSTS = new Set(['gramlyze.com', 'www.gramlyze.com']);
const QUESTIONS = '/test/future-perfect/questions';
const THEORY = '/theory/basic-grammar/sentence-types';
const ONE = '/theory/zaimennyky-ta-vkazivni-slova/one-ones';
const COURSE = '/courses/english-grammar-theory';
const SCENARIOS = ['theory', 'questions', 'course', 'one-ones'];
const OUTPUT = path.resolve(__dirname, '../../storage/app/seo-m10-production');

const digest = value => crypto.createHash('sha256').update(JSON.stringify(value)).digest('hex');
const errorEvidence = error => ({type: ['AssertionError', 'TimeoutError', 'Error', 'TypeError'].includes(error?.name) ? error.name : 'DiagnosticError',
    messageSha256: digest(String(error?.message || 'diagnostic failure'))});

function validateOrigin(value) {
    let url;
    try { url = new URL(value); } catch { throw new Error('Invalid production origin'); }
    if (url.protocol !== 'https:' || !HOSTS.has(url.hostname) || url.username || url.password
        || (url.port && url.port !== '443') || !['', '/'].includes(url.pathname) || url.search || url.hash)
        throw new Error('Origin must be exactly https://gramlyze.com or https://www.gramlyze.com');
    return `https://${url.hostname}`;
}

function safeUrl(value) {
    try {
        const url = new URL(value);
        return `${url.protocol}//${url.host}${url.pathname}`;
    } catch { return '[invalid-url]'; }
}

function isFont(url) {
    return url.protocol === 'https:' && ['fonts.googleapis.com', 'fonts.gstatic.com'].includes(url.hostname);
}

function requestDecision(value, {origin, navigation = false, allowedPaths = [], method = 'GET', questionsWrite = false} = {}) {
    let url;
    try { url = new URL(value); } catch { return 'invalid-url'; }
    const normalizedOrigin = validateOrigin(origin);
    if (url.protocol === 'http:') return 'mixed-content';
    if (isFont(url) && !navigation && ['GET', 'HEAD'].includes(method)) return null;
    if (url.origin !== normalizedOrigin) return navigation ? 'external-navigation' : 'external-resource';
    if (!['GET', 'HEAD'].includes(method)) {
        if (questionsWrite && method === 'POST' && url.pathname === QUESTIONS + '/state' && !url.search) return null;
        return 'unplanned-write';
    }
    if (navigation && (!allowedPaths.includes(url.pathname) || url.search)) return 'outside-navigation-plan';
    return null;
}

function removeOwnSnapshots({keys, local = localStorage, session = sessionStorage}) {
    if (!Array.isArray(keys) || !keys.length || keys.some(key => typeof key !== 'string' || !key)) throw new Error('Exact own keys required');
    const snapshot = storage => Object.fromEntries(Array.from({length: storage.length}, (_, index) => storage.key(index))
        .filter(key => !keys.includes(key)).map(key => [key, storage.getItem(key)]));
    const before = [snapshot(local), snapshot(session)];
    let removed = 0;
    for (const storage of [local, session]) for (const key of keys) {
        if (storage.getItem(key) !== null) removed++;
        storage.removeItem(key);
    }
    return {removed, unrelatedStoragePreserved: JSON.stringify(before) === JSON.stringify([snapshot(local), snapshot(session)]),
        snapshotsAbsent: [local, session].every(storage => keys.every(key => storage.getItem(key) === null))};
}

function parseArgs(argv) {
    const result = {};
    for (let index = 0; index < argv.length; index++) {
        const key = argv[index];
        if (key === '--skip-headed-smoke') { result.skipHeadedSmoke = true; continue; }
        if (!['--origin', '--label', '--http', '--cases'].includes(key) || index + 1 >= argv.length) throw new Error(`Unknown or incomplete argument: ${key}`);
        result[key.slice(2)] = argv[++index];
    }
    assert.ok(result.origin && result.label && result.http, '--origin, --label and --http are required');
    result.origin = validateOrigin(result.origin);
    assert.match(result.label, /^[a-z0-9][a-z0-9-]*$/);
    return result;
}

function selectedCases(value) {
    const all = [false, true].flatMap(mobile => SCENARIOS.map(kind => ({kind, mobile})));
    if (!value) return all;
    const requested = value.split(',').filter(Boolean);
    const selected = all.filter(item => requested.includes(`${item.kind}-${item.mobile ? 'mobile' : 'desktop'}`));
    assert.equal(selected.length, requested.length, 'Each follow-up case must be a unique known kind-viewport pair');
    return selected;
}

async function settled(page) {
    await page.waitForTimeout(450);
    await page.evaluate(async () => { await JS_TEST_SAVE_QUEUE; });
    await page.waitForTimeout(250);
}

async function progress(page) {
    return page.evaluate(() => ({answered: state.answered, correct: state.correct, activeCardIdx: state.activeCardIdx,
        order: state.items.map(question => question.uuid || question.id), items: state.items.map(question => ({chosen: question.chosen,
            manual: question.manualInputsBySlot?.map(value => value ?? ''), done: question.done, activeSlot: question.activeSlot}))}));
}

async function theoryReady(page, mobile) {
    if (mobile) await page.locator('[data-theory-mobile-nav-toggle]').click();
    await page.waitForFunction(isMobile => {
        const root = isMobile ? document.querySelector('[data-theory-mobile-nav-toggle]')?.parentElement
            : document.querySelector('[data-theory-desktop-navigation-loader]');
        const data = root?._x_dataStack?.[0];
        return data && !data.loading && !data.error && (isMobile ? data.loaded : root.querySelector('[x-ref="content"] a'));
    }, mobile, {timeout: 30000});
    return page.locator(mobile ? '[data-theory-mobile-nav-panel]' : '[data-theory-desktop-navigation-loader]');
}

async function installGuard(context, row, origin, allowedPaths, questionsWrite) {
    await context.route('**/*', async route => {
        const request = route.request();
        const reason = requestDecision(request.url(), {origin, navigation: request.isNavigationRequest(), allowedPaths,
            method: request.method(), questionsWrite});
        if (reason) {
            row.blocked.push({url: safeUrl(request.url()), method: request.method(), reason});
            await route.abort('blockedbyclient');
        } else await route.continue();
    });
}

async function headedSmoke(chromium, executablePath, origin, label) {
    const result = {startedAt: new Date().toISOString(), pass: false, blocked: []};
    const browser = await chromium.launch({headless: false, ...(executablePath ? {executablePath} : {})});
    try {
        const context = await browser.newContext({viewport: {width: 1440, height: 1000}, serviceWorkers: 'block', locale: 'uk-UA'});
        await installGuard(context, result, origin, [THEORY], false);
        const page = await context.newPage();
        const response = await page.goto(origin + THEORY, {waitUntil: 'load', timeout: 60000});
        assert.equal(response.status(), 200);
        await page.locator('h1').waitFor({state: 'visible'});
        assert.ok((await page.locator('main').first().innerText()).length > 500);
        await page.screenshot({path: path.join(OUTPUT, `${label}-headed-smoke.png`), animations: 'disabled'});
        await page.waitForTimeout(1200);
        assert.deepEqual(result.blocked, []);
        result.pass = true;
        await context.close();
    } catch (error) { result.error = errorEvidence(error); }
    finally { await browser.close(); result.finishedAt = new Date().toISOString(); }
    return result;
}

async function runScenario(browser, origin, label, kind, mobile, accepted) {
    const startPath = {theory: THEORY, questions: QUESTIONS, course: COURSE, 'one-ones': ONE}[kind];
    const allowedPaths = kind === 'course' ? [COURSE] : kind === 'questions' ? [QUESTIONS, '/theory'] : [startPath];
    const row = {kind, viewport: mobile ? 'mobile' : 'desktop', startedAt: new Date().toISOString(), blocked: [],
        console: [], pageErrors: [], failedRequests: [], httpErrors: [], loadedAssets: [], screenshots: [], statePosts: []};
    const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000},
        isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', colorScheme: 'light', serviceWorkers: 'block'});
    await installGuard(context, row, origin, allowedPaths, kind === 'questions');
    await context.addInitScript(() => {
        window.__m10 = {alpineInits: 0, saves: []};
        addEventListener('alpine:init', () => window.__m10.alpineInits++);
        const original = window.fetch;
        window.fetch = async function(input, init) {
            const url = new URL(typeof input === 'string' ? input : input.url, location.href);
            const record = url.pathname === '/test/future-perfect/questions/state' ? {} : null;
            if (record) window.__m10.saves.push(record);
            try {
                const response = await original.apply(this, arguments);
                if (record) Object.assign(record, {resolved: true, status: response.status});
                return response;
            } catch (error) { if (record) record.rejected = true; throw error; }
        };
    });
    const page = await context.newPage();
    page.setDefaultTimeout(30000);
    const started = Date.now();
    const recordMessage = (type, text, location) => ({type, textSha256: digest(text), url: safeUrl(location || origin)});
    page.on('pageerror', error => row.pageErrors.push(recordMessage('pageerror', String(error.message), page.url())));
    page.on('console', message => {
        if (['error', 'warning'].includes(message.type())) row.console.push(recordMessage(message.type(), message.text(), message.location().url));
    });
    page.on('requestfailed', request => row.failedRequests.push({url: safeUrl(request.url()), method: request.method(), error: request.failure()?.errorText}));
    page.on('response', response => {
        const url = new URL(response.url());
        const item = {url: safeUrl(response.url()), method: response.request().method(), status: response.status()};
        if (response.status() >= 400) row.httpErrors.push(item);
        if (url.origin === origin && (/\/build\/assets\//.test(url.pathname) || /\/livewire[^/]*\//.test(url.pathname))) row.loadedAssets.push(item);
        if (url.origin === origin && url.pathname === QUESTIONS + '/state' && response.request().method() === 'POST') row.statePosts.push({status: response.status()});
    });
    const screenshot = async suffix => {
        const filename = `${label}-${kind}-${mobile ? 'mobile' : 'desktop'}-${suffix}.png`;
        await page.screenshot({path: path.join(OUTPUT, filename), animations: 'disabled'});
        row.screenshots.push(filename);
    };
    try {
        const response = await page.goto(origin + startPath, {waitUntil: 'load', timeout: 60000});
        assert.equal(response.status(), 200);
        await page.locator('h1').first().waitFor({state: 'visible'});
        if (kind === 'theory') {
            const main = page.locator('[data-theory-main], main').first();
            row.material = await main.evaluate(element => ({textCharacters: element.innerText.length, h1: element.querySelectorAll('h1').length,
                tables: element.querySelectorAll('table').length, literalInlineHtml: /<(?:strong|span)(?:\s|>)/i.test(element.innerText)}));
            assert.ok(row.material.textCharacters > 1000 && row.material.h1 === 1 && !row.material.literalInlineHtml);
            const navigation = await theoryReady(page, mobile);
            row.sidebar = {links: await navigation.locator('a').count(), current: await navigation.locator('[data-theory-nav-current-page="true"]').count()};
            assert.ok(row.sidebar.links > 10 && row.sidebar.current > 0);
            if (mobile) {
                assert.equal(await page.locator('[data-theory-mobile-nav-toggle]').getAttribute('aria-expanded'), 'true');
                await page.locator('[data-theory-mobile-nav-toggle]').click();
                const siteMenu = page.locator('#site-header button[\\@click="mobile = !mobile"]');
                await siteMenu.click();
                await siteMenu.click();
            } else {
                const toggle = page.locator('[data-theory-aside] [data-theory-sidebar] > div > button').first();
                await toggle.click();
                assert.equal(await page.locator('[data-theory-layout]').getAttribute('data-collapsed'), 'true');
                await toggle.click();
            }
            await page.locator('.site-header button[\\@click="toggleTheme"]:visible').first().click();
            await page.reload({waitUntil: 'load'});
            assert.ok(await page.evaluate(() => document.documentElement.classList.contains('dark') && localStorage.getItem('theme') === 'dark'));
            row.reload = {themeRestored: true, pageUsable: (await page.locator('main').first().innerText()).length > 500};
            await screenshot('final');
        } else if (kind === 'questions') {
            const httpRow = accepted.rows.find(item => item.requested_path.startsWith(QUESTIONS + '/questions?'));
            const count = httpRow?.json?.question_count;
            assert.ok(Number.isSafeInteger(count) && count > 0);
            await page.waitForFunction(expected => typeof state !== 'undefined' && state.items.length === expected, count);
            await settled(page);
            row.initial = progressEvidence(await progress(page));
            assert.equal(row.initial.answered, 0);
            await screenshot('before-answer');
            const indexes = await page.evaluate(() => state.items.map((question, index) => ({question, index}))
                .filter(item => item.question.answers?.length === 1 && document.querySelector(`article[data-idx="${item.index}"] button[data-options-toggle]`))
                .slice(0, 3).map(item => item.index));
            assert.equal(indexes.length, 3);
            for (const index of indexes) {
                const card = page.locator(`article[data-idx="${index}"]`);
                await card.locator('button[data-options-toggle]').click();
                const option = await card.evaluate((element, itemIndex) => [...element.querySelectorAll('button[data-opt]')]
                    .findIndex(button => button.getAttribute('data-opt') === state.items[itemIndex].answers[state.items[itemIndex].activeSlot]), index);
                assert.ok(option >= 0);
                await card.locator('button[data-opt]').nth(option).click();
                await settled(page);
            }
            row.saved = progressEvidence(await progress(page));
            row.saveFetches = await page.evaluate(() => __m10.saves.map(item => ({resolved: !!item.resolved, status: item.status, rejected: !!item.rejected})));
            assert.equal(row.saved.answered, 3);
            assert.equal(row.saved.correct, 3);
            assert.ok(row.saveFetches.length > 0 && row.saveFetches.every(item => item.resolved && item.status === 204 && !item.rejected));
            await page.reload({waitUntil: 'load'});
            await page.waitForFunction(expected => typeof state !== 'undefined' && state.items.length === expected, count);
            await settled(page);
            row.reload = progressEvidence(await progress(page));
            assert.deepEqual(row.reload, row.saved);
            const keys = await page.evaluate(() => JS_TEST_PERSISTENCE.storageKeys?.length ? [...JS_TEST_PERSISTENCE.storageKeys] : [JS_TEST_PERSISTENCE.storageKey]);
            await page.goto(origin + '/theory', {waitUntil: 'load'});
            row.snapshotRemoval = await page.evaluate(removeOwnSnapshots, {keys});
            assert.ok(row.snapshotRemoval.removed > 0 && row.snapshotRemoval.unrelatedStoragePreserved && row.snapshotRemoval.snapshotsAbsent);
            const restoredResponse = await page.goto(origin + QUESTIONS, {waitUntil: 'load'});
            const bootstrap = serverBootstrapEvidence(await restoredResponse.text());
            await page.waitForFunction(expected => typeof state !== 'undefined' && state.items.length === expected, count);
            await settled(page);
            row.restored = progressEvidence(await progress(page));
            row.serverBootstrap = bootstrap;
            assert.deepEqual(row.serverBootstrap, row.saved);
            assert.deepEqual(row.restored, row.saved);
            row.serverOnlyRestored = true;
            await screenshot('server-restored');
        } else if (kind === 'course') {
            await page.waitForFunction(() => !!window.TheoryCourseProgress);
            const links = page.locator('a[data-theory-course-action]');
            const linkIndex = await links.evaluateAll(nodes => nodes.findIndex(node => node.getAttribute('aria-disabled') !== 'true'));
            assert.ok(linkIndex >= 0, 'real course lesson must be guest-accessible');
            const link = links.nth(linkIndex);
            row.courseLink = safeUrl(await link.evaluate(element => element.href));
            const coursePath = new URL(row.courseLink).pathname;
            allowedPaths.push(coursePath);
            await link.click();
            await page.waitForURL(origin + coursePath);
            await page.locator('[data-theory-lesson-content]').waitFor({state: 'visible'});
            const canonical = await page.locator('link[rel="canonical"]').getAttribute('href');
            assert.ok(canonical.startsWith(origin + '/theory/'));
            row.canonical = canonical;
            await page.reload({waitUntil: 'load'});
            await page.locator('[data-theory-lesson-content]').waitFor({state: 'visible'});
            row.reload = {urlPreserved: page.url() === origin + coursePath, uiVisible: true};
            assert.ok(row.reload.urlPreserved);
            await screenshot('final');
        } else if (kind === 'one-ones') {
            const source = JSON.parse(fs.readFileSync(path.resolve(__dirname, '../../database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder/definition.json')));
            row.blocks = [];
            const targetOrders = source.page.blocks.map((block, index) => block.layout ? index + 1 : null).filter(Boolean);
            for (const order of targetOrders) {
                const body = source.page.blocks[order - 1].body;
                const match = await page.locator('[id^="block-"]').evaluateAll((nodes, expectedBody) => {
                    const normalize = text => text.replace(/\s+/g, ' ').trim();
                    const expected = new DOMParser().parseFromString(expectedBody, 'text/html').body;
                    const node = nodes.find(item => item.querySelector('.prose') && normalize(item.querySelector('.prose').textContent) === normalize(expected.textContent));
                    return node ? {id: node.id, items: node.querySelectorAll('.prose li').length, expectedItems: expected.querySelectorAll('li').length} : null;
                }, body);
                assert.ok(match);
                assert.equal(match.items, match.expectedItems);
                await page.locator('#' + match.id).scrollIntoViewIfNeeded();
                assert.ok(await page.locator('#' + match.id).isVisible());
                row.blocks.push({order, ...match});
            }
            row.content = await page.locator('main').first().evaluate(element => ({textCharacters: element.innerText.length,
                literalTags: /<(?:strong|span)(?:\s|>)/i.test(element.innerText),
                hasCoffee: /coffee/i.test(element.innerText), hasWater: /water/i.test(element.innerText),
                hasPossessive: /possessive|присв/i.test(element.innerText), hasSomeAnyOnes: /some/i.test(element.innerText) && /any/i.test(element.innerText) && /ones/i.test(element.innerText),
                coldSome: /a cold some/i.test(element.innerText)}));
            row.content.expectedTargetBlocks = targetOrders.length;
            row.content.matchedTargetBlocks = row.blocks.length;
            assert.ok(row.content.textCharacters > 1000 && row.content.matchedTargetBlocks === row.content.expectedTargetBlocks && !row.content.literalTags && row.content.hasCoffee
                && row.content.hasWater && row.content.hasPossessive && row.content.hasSomeAnyOnes && !row.content.coldSome);
            await screenshot('final');
        }
        row.runtime = await page.evaluate(() => ({alpineInits: __m10.alpineInits, alpineVersion: window.Alpine?.version || null, livewire: !!window.Livewire}));
        assert.equal(row.runtime.alpineInits, 1);
        assert.ok(row.runtime.livewire);
        row.horizontalOverflow = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - innerWidth));
        assert.ok(row.horizontalOverflow <= 2);
        row.loadedAssets = [...new Map(row.loadedAssets.map(item => [item.url, item])).values()];
        assert.ok(row.loadedAssets.some(item => /\/build\/assets\/.*\.css$/.test(item.url) && item.status === 200));
        assert.ok(row.loadedAssets.some(item => /\/build\/assets\/.*\.js$/.test(item.url) && item.status === 200));
        row.fontFailures = row.failedRequests.filter(item => /^https:\/\/fonts\.(googleapis|gstatic)\.com\//.test(item.url));
        let acknowledgements = row.statePosts.filter(item => item.status === 204).length;
        row.confirmedStateAborts = 0;
        const unexpectedFailures = row.failedRequests.filter(item => {
            if (row.fontFailures.includes(item)) return false;
            if (row.serverOnlyRestored && item.url === origin + QUESTIONS + '/state' && item.method === 'POST'
                && item.error === 'net::ERR_ABORTED' && acknowledgements > 0) { acknowledgements--; row.confirmedStateAborts++; return false; }
            return true;
        });
        const expectedBlocks = row.blocked.filter(item => item.reason === 'external-resource' && /^https:\/\/.*google/.test(item.url));
        assert.deepEqual(row.blocked.filter(item => !expectedBlocks.includes(item)), []);
        assert.deepEqual(unexpectedFailures, []);
        assert.deepEqual(row.httpErrors, []);
        assert.deepEqual(row.pageErrors, []);
        assert.deepEqual(row.console.filter(item => item.type === 'error' && !row.fontFailures.some(failure => failure.url === item.url)), []);
        row.pass = true;
    } catch (error) {
        row.pass = false;
        row.error = errorEvidence(error);
    } finally {
        row.durationMs = Date.now() - started;
        row.finishedAt = new Date().toISOString();
        await context.close();
    }
    return row;
}

async function main(argv = process.argv.slice(2)) {
    const args = parseArgs(argv);
    const accepted = JSON.parse(fs.readFileSync(args.http, 'utf8'));
    assert.equal(accepted.origin, args.origin, 'HTTP evidence origin mismatch');
    assert.ok(Array.isArray(accepted.rows) && accepted.rows.length >= 19, 'Completed HTTP evidence required');
    fs.mkdirSync(OUTPUT, {recursive: true});
    const evidence = path.join(OUTPUT, `${args.label}-browser.json`);
    assert.ok(!fs.existsSync(evidence), 'Evidence label already exists');
    const report = {schema: 'gramlyze-m10-production-browser-v1', origin: args.origin, startedAt: new Date().toISOString(), rows: []};
    const save = () => fs.writeFileSync(evidence, JSON.stringify(report, null, 2), {encoding: 'utf8'});
    fs.writeFileSync(evidence, '{}', {flag: 'wx'});
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const executablePath = process.env.CHROMIUM_EXECUTABLE || undefined;
    report.headedSmoke = args.skipHeadedSmoke ? {skipped: true, pass: true, reason: 'targeted follow-up after accepted headed smoke'}
        : await headedSmoke(chromium, executablePath, args.origin, args.label);
    save();
    const browser = await chromium.launch({headless: true, ...(executablePath ? {executablePath} : {})});
    report.browser = browser.version();
    try {
        for (const {mobile, kind} of selectedCases(args.cases)) {
            const row = await runScenario(browser, args.origin, args.label, kind, mobile, accepted);
            report.rows.push(row);
            save();
            console.log(JSON.stringify({kind, viewport: row.viewport, pass: row.pass, error: row.error}));
        }
    } finally { await browser.close(); }
    report.finishedAt = new Date().toISOString();
    report.pass = report.headedSmoke.pass && report.rows.length === selectedCases(args.cases).length && report.rows.every(row => row.pass);
    save();
    console.log(JSON.stringify({file: evidence, pass: report.pass, scenarios: report.rows.length}));
    process.exitCode = report.pass ? 0 : 1;
    return report.pass;
}

module.exports = {validateOrigin, safeUrl, requestDecision, removeOwnSnapshots, parseArgs, selectedCases};
if (require.main === module) main().catch(error => { console.error(errorEvidence(error)); process.exitCode = 1; });
