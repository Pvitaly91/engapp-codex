// Local-only acceptance: no cookies, tokens or full response bodies are saved.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const base = 'http://gramlyze.loc';
const out = path.resolve('storage/app/answer-punctuation');
fs.mkdirSync(out, { recursive: true });

async function enterAnswer(page, index, answer) {
    for (const word of answer.trim().split(/\s+/)) {
        const input = page.locator(`article[data-idx="${index}"] input[data-manual-active="true"]:not([disabled]):visible`).first();
        await input.fill(word);
        await input.press('Enter');
        await page.waitForTimeout(120);
    }
}

(async () => {
    const browser = await chromium.launch({ headless: true,
        ...(process.env.CHROMIUM_EXECUTABLE ? { executablePath: process.env.CHROMIUM_EXECUTABLE } : {}) });
    const results = [];
    const scenarios = [
        ...[false, true].flatMap(mobile => [false, true].map(step => ({ topic: 'present-perfect-continuous/negatives', mobile, step }))),
        { topic: 'future-perfect/questions', mobile: false, step: false },
        { topic: 'past-perfect/negatives', mobile: false, step: false },
    ];
    try {
        for (const { topic, mobile, step } of scenarios) {
            const name = `${topic.replaceAll('/', '-')}-${mobile ? 'mobile' : 'desktop'}-${step ? 'step' : 'cards'}`;
            const result = { name, checks: [], pageErrors: [], failedRequests: [], startedAt: new Date().toISOString() };
            const context = await browser.newContext({ locale: 'uk-UA', serviceWorkers: 'block',
                viewport: mobile ? { width: 390, height: 844 } : { width: 1440, height: 1000 },
                isMobile: mobile, hasTouch: mobile });
            await context.route('**/*', route => new URL(route.request().url()).hostname === 'gramlyze.loc'
                ? route.continue() : route.abort('blockedbyclient'));
            const page = await context.newPage();
            page.setDefaultTimeout(15000);
            page.on('pageerror', error => result.pageErrors.push(error.message));
            const statuses = new WeakMap();
            page.on('response', response => statuses.set(response.request(), response.status()));
            page.on('requestfailed', request => {
                const url = new URL(request.url());
                if (url.hostname === 'gramlyze.loc') result.failedRequests.push({ path: url.pathname,
                    status: statuses.get(request) ?? null, error: request.failure()?.errorText });
            });
            try {
                const response = await page.goto(`${base}/test/${topic}${step ? '/step' : ''}?source=theory`, { waitUntil: 'load' });
                assert.equal(response.status(), 200);
                await page.waitForFunction(() => typeof state !== 'undefined' && state.items?.length > 0);
                const selected = await page.evaluate(preferScreenshot => {
                    const suitable = q => isPolyglotComposeQuestion(q) && /[.!?…]$/.test(q.answers.at(-1));
                    let index = preferScreenshot ? state.items.findIndex(q => suitable(q) && q.question === 'Ти не слухаєш мене останню хвилину.') : -1;
                    if (index < 0) index = state.items.findIndex(suitable);
                    if (index < 0) index = state.items.findIndex(q => isPolyglotComposeQuestion(q) && q.answers?.length);
                    if (index < 0) return null;
                    return { index, question: state.items[index].question, answers: state.items[index].answers };
                }, !step && topic === 'present-perfect-continuous/negatives');
                assert.ok(selected, 'A real sentence-builder question is available');
                result.url = page.url();
                result.question = selected.question;
                if (step) {
                    for (let i = 0; i < selected.index; i++) {
                        const answers = await page.evaluate(i => state.items[i].answers, i);
                        for (const answer of answers) await enterAnswer(page, i, answer);
                        await page.waitForFunction(i => state.items[i].done, i);
                        await page.locator('#next').click();
                    }
                }
                const before = await page.evaluate(() => ({ answered: state.answered, correct: state.correct }));
                for (const answer of selected.answers.slice(0, -1)) await enterAnswer(page, selected.index, answer);
                const canonical = selected.answers.at(-1);
                const omittedPunctuation = /[.!?…]$/.test(canonical);
                const finalAnswer = omittedPunctuation ? canonical.replace(/[.!?…]+$/u, '').trim() : `${canonical}.`;
                await enterAnswer(page, selected.index, finalAnswer);
                await page.waitForFunction(i => state.items[i].done, selected.index);
                const actual = await page.evaluate(i => ({ done: state.items[i].done, wrong: state.items[i].wrongAttempt,
                    chosen: state.items[i].chosen.at(-1), expected: state.items[i].answers.at(-1),
                    answered: state.answered, correct: state.correct }), selected.index);
                assert.equal(actual.wrong, false, 'Optional ending punctuation must not count as a mistake');
                assert.equal(actual.chosen, finalAnswer);
                assert.equal(actual.expected, selected.answers.at(-1), 'Canonical answer must retain its punctuation');
                assert.equal(actual.correct, before.correct + 1, 'First-attempt score must increase');
                if (!step) assert.equal(actual.answered, before.answered + 1);
                result.checks.push(`Whole question completed ${omittedPunctuation ? 'without' : 'with optional'} final punctuation, on first attempt`);
                result.finalAnswer = { entered: finalAnswer, canonical: actual.expected };
                await page.waitForTimeout(350);
                await page.evaluate(async () => { await JS_TEST_SAVE_QUEUE; });
                await page.reload({ waitUntil: 'load' });
                await page.waitForFunction(i => typeof state !== 'undefined' && state.items?.[i]?.done, selected.index);
                assert.equal(await page.evaluate(i => state.items[i].chosen.at(-1), selected.index), finalAnswer);
                result.checks.push('Correct answer survives reload');
                await page.locator(`article[data-idx="${selected.index}"]`).scrollIntoViewIfNeeded();
                await page.screenshot({ path: path.join(out, `${name}.png`) });
                assert.deepEqual(result.pageErrors, []);
                // Retain Chromium's raw empty-response events, but distinguish
                // successful HTTP 204 saves (independently verified by reload).
                assert.deepEqual(result.failedRequests.filter(request => !(request.status === 204
                    && request.path.endsWith('/state') && request.error === 'net::ERR_ABORTED')), []);
            } catch (error) {
                result.error = error.message;
                await page.screenshot({ path: path.join(out, `${name}-failure.png`) }).catch(() => {});
            } finally {
                result.finishedAt = new Date().toISOString();
                results.push(result);
                console.log(JSON.stringify({ name, checks: result.checks, error: result.error }));
                await context.close();
                fs.writeFileSync(path.join(out, 'acceptance.json'), JSON.stringify(results, null, 2));
            }
        }
    } finally { await browser.close(); }
    if (results.some(result => result.error)) process.exitCode = 1;
})().catch(error => { console.error(error.message); process.exitCode = 1; });
