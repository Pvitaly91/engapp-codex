// Local-only acceptance. Cookies, CSRF tokens and response bodies stay in memory.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const base = 'http://gramlyze.loc';
const out = path.resolve('storage/app/test-restart');
fs.mkdirSync(out, { recursive: true });

async function ready(page) {
    await page.waitForFunction(() => typeof state !== 'undefined' && state.items?.length > 0);
    await page.evaluate(async () => { await JS_TEST_SAVE_QUEUE; });
}

async function clean(page) {
    const result = await page.evaluate(() => ({
        started: isStartedState(state),
        serverRestored: window.__restartHadServerProgress,
        localRestored: JS_TEST_PERSISTENCE.storageKeys.some(key => localStorage.getItem(key) || sessionStorage.getItem(key)),
        filled: [...document.querySelectorAll('input[data-manual-gap]')].some(input => input.value !== ''),
        scroll: window.scrollY,
    }));
    assert.equal(result.started, false, 'No answers, attempts or position from the old test');
    assert.equal(result.serverRestored, false, 'Server delivered a fresh test');
    assert.equal(result.localRestored, false, 'Browser snapshot removed');
    assert.equal(result.filled, false, 'Manual inputs are blank');
    assert.equal(result.scroll, 0, 'Reload starts at the top');
}

async function answerAndType(page, step) {
    const index = step ? 0 : await page.evaluate(() => state.items.findIndex((q, i) =>
        q.answers?.length === 1 && document.querySelector(`article[data-idx="${i}"] button[data-options-toggle]`)));
    assert.ok(index >= 0, 'An answerable question exists');
    const card = step ? page.locator('#quiz-app') : page.locator(`article[data-idx="${index}"]`);
    await card.locator('button[data-options-toggle]').click();
    const answer = await page.evaluate(i => state.items[i].answers[state.items[i].activeSlot], index);
    // Match the exact data value, not a substring of the visible label.
    await card.locator(`button[data-opt=${JSON.stringify(answer)}]`).click();
    await page.waitForTimeout(350);
    const input = page.locator('input[data-manual-gap]:not([disabled])').first();
    if (await input.count()) await input.fill('draft');
    await page.waitForTimeout(350);
    await page.evaluate(async () => { await JS_TEST_SAVE_QUEUE; });
    assert.equal(await page.evaluate(() => isStartedState(state)), true, 'Real UI interaction creates saved progress');
}

(async () => {
    const browser = await chromium.launch({ headless: true,
        ...(process.env.CHROMIUM_EXECUTABLE ? { executablePath: process.env.CHROMIUM_EXECUTABLE } : {}) });
    const results = [];
    try {
        for (const mobile of [false, true]) {
            for (const step of [false, true]) {
                const name = `${mobile ? 'mobile' : 'desktop'}-${step ? 'step' : 'cards'}`;
                const result = { name, checks: [], pageErrors: [], failedRequests: [], blockedExternal: [], startedAt: new Date().toISOString() };
                const context = await browser.newContext({
                    viewport: mobile ? { width: 390, height: 844 } : { width: 1440, height: 1000 },
                    isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', serviceWorkers: 'block',
                });
                await context.addInitScript(() => {
                    let persistence;
                    Object.defineProperty(window, 'JS_TEST_PERSISTENCE', {
                        configurable: true,
                        get: () => persistence,
                        set: value => {
                            window.__restartHadServerProgress = Boolean(value?.saved);
                            persistence = value;
                        },
                    });
                });
                await context.route('**/*', route => {
                    if (new URL(route.request().url()).hostname !== 'gramlyze.loc') {
                        result.blockedExternal.push(new URL(route.request().url()).hostname);
                        return route.abort('blockedbyclient');
                    }
                    return route.continue();
                });
                const page = await context.newPage();
                page.setDefaultTimeout(15000);
                page.on('pageerror', error => result.pageErrors.push(error.message));
                const statuses = new WeakMap();
                page.on('response', response => statuses.set(response.request(), response.status()));
                page.on('requestfailed', request => {
                    const url = new URL(request.url());
                    if (url.hostname !== 'gramlyze.loc') return;
                    result.failedRequests.push({ path: url.pathname, error: request.failure()?.errorText,
                        status: statuses.get(request) ?? null });
                });
                let resetRequests = 0;
                page.on('request', request => {
                    if (request.method() === 'POST' && new URL(request.url()).pathname.endsWith('/state')
                        && request.postDataJSON()?.state === null) resetRequests++;
                });
                try {
                    const url = `${base}/test/present-perfect-continuous/negatives${step ? '/step' : ''}?source=theory`;
                    const response = await page.goto(url, { waitUntil: 'load' });
                    assert.equal(response.status(), 200);
                    await ready(page);
                    // source=theory is intentionally consumed by the initial redirect.
                    const currentUrl = page.url();
                    // Some modes place restart outside the sticky controls.
                    const supportsSticky = await page.locator('#sticky-header #restart-test').count() > 0;
                    for (const sticky of !mobile && supportsSticky ? [false, true] : [false]) {
                        await answerAndType(page, step);
                        await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
                        await page.waitForFunction(() => !document.querySelector('#site-header-test-controls #restart-test'));
                        if (sticky) {
                            await page.evaluate(() => window.scrollTo({
                                top: document.querySelector('#sticky-header').getBoundingClientRect().top + 500,
                                behavior: 'instant',
                            }));
                            await page.waitForFunction(() => document.querySelector('#site-header-test-controls #restart-test'));
                        }
                        const button = page.locator('#restart-test');
                        if (!sticky) await button.scrollIntoViewIfNeeded();
                        assert.equal(await button.evaluate(el => Boolean(el.closest('#site-header-test-controls'))), sticky);
                        await page.screenshot({ path: path.join(out, `${name}-${sticky ? 'sticky' : 'normal'}-before.png`) });
                        const before = resetRequests;
                        await Promise.all([
                            page.waitForEvent('framenavigated', { predicate: frame => frame === page.mainFrame() }),
                            button.click(),
                        ]);
                        await page.waitForLoadState('load');
                        await ready(page);
                        assert.equal(page.url(), currentUrl, 'Current URL and mode preserved');
                        assert.equal(resetRequests, before + 1, 'One reset per click');
                        await clean(page);
                        await page.screenshot({ path: path.join(out, `${name}-${sticky ? 'sticky' : 'normal'}-after.png`) });
                        await page.reload({ waitUntil: 'load' });
                        await ready(page);
                        await clean(page);
                        result.checks.push(`${sticky ? 'sticky' : 'normal'}: reset, document reload, clean state, repeat reload`);
                        console.log(JSON.stringify({ name, check: result.checks.at(-1) }));
                    }
                    assert.deepEqual(result.pageErrors, [], 'No JavaScript errors');
                    assert.deepEqual(result.failedRequests.filter(request => !(request.path.endsWith('/state')
                        && request.status === 204 && request.error === 'net::ERR_ABORTED')), [], 'No unexpected failed requests');
                    // Preserve Chromium's raw 204/ERR_ABORTED events in the evidence.
                    // The successful UI restart plus two clean document loads prove
                    // reset persistence independently of the network event wording.
                } catch (error) {
                    result.error = error.message;
                    console.error(name, error.message);
                    await page.screenshot({ path: path.join(out, `${name}-failure.png`) }).catch(() => {});
                } finally {
                    result.finishedAt = new Date().toISOString();
                    results.push(result);
                    await context.close();
                    fs.writeFileSync(path.join(out, 'acceptance.json'), JSON.stringify(results, null, 2));
                }
            }
        }
    } finally { await browser.close(); }
    if (results.some(result => result.error)) process.exitCode = 1;
})().catch(error => { console.error(error.message); process.exitCode = 1; });
