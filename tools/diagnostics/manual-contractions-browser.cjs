// Local-only UI acceptance for contracted / expanded manual answers.
// Responses, cookies, CSRF tokens and complete saved snapshots stay in memory.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

const base = 'http://gramlyze.loc';
const out = path.resolve('storage/app/manual-contractions');
const baseline = process.argv.includes('--baseline');
fs.mkdirSync(out, { recursive: true });

async function ready(page) {
    await page.waitForFunction(() => typeof state !== 'undefined' && state.items?.length > 0);
    await settle(page);
}

async function settle(page) {
    await page.waitForTimeout(350);
    await page.evaluate(async () => { await JS_TEST_SAVE_QUEUE; });
}

function card(page, index) {
    return page.locator(`article[data-idx="${index}"]`);
}

function activeInput(page, index) {
    return card(page, index).locator('input[data-manual-gap][data-manual-active="true"]:not([disabled]):visible').first();
}

async function enter(page, index, text, shortcut = 'Enter') {
    const input = activeInput(page, index);
    await input.fill(text);
    await input.press(shortcut);
    await page.waitForTimeout(120);
}

async function snapshot(page, index) {
    return page.evaluate(i => {
        const q = state.items[i];
        const article = document.querySelector(`article[data-idx="${i}"]`);
        const inputs = [...article.querySelectorAll('input[data-manual-gap]')]
            .filter(el => el.getClientRects().length).map(el => ({
                slot: Number(el.dataset.manualGap), word: Number(el.dataset.manualWord),
                value: el.value, disabled: el.disabled, focused: el === document.activeElement,
            }));
        return { index: i, question: q.question, activeSlot: q.activeSlot,
            wordIndex: q.manualWordIndexBySlot?.[q.activeSlot], chosen: q.chosen,
            manual: q.manualInputsBySlot, done: q.done, feedback: q.feedback, inputs };
    }, index);
}

async function selectQuestion(page, step, contraction, exact = false) {
    const selected = await page.evaluate(({ contraction, exact }) => {
        const index = state.items.findIndex(q => q.type === '4' && q.answers.includes(contraction)
            && (!exact || q.question === 'Ти не слухаєш мене останню хвилину.'));
        if (index < 0) return null;
        const q = state.items[index];
        return { index, question: q.question, slot: q.answers.indexOf(contraction), answers: q.answers };
    }, { contraction, exact });
    assert.ok(selected, `A real ${contraction} question is available`);
    if (step) {
        // Complete earlier questions using their real UI; never overwrite answers/state.
        for (let i = 0; i < selected.index; i++) {
            const q = await page.evaluate(i => ({ answers: state.items[i].answers,
                presentation: state.items[i].presentation }), i);
            assert.notEqual(q.presentation, 'sentence-reorder', 'Step acceptance selects the first compose question');
            for (const answer of q.answers) {
                for (const word of answer.trim().split(/\s+/)) await enter(page, i, word);
            }
            await page.waitForFunction(i => state.items[i].done === true, i);
            await page.locator('#next').click();
        }
    }
    for (let slot = 0; slot < selected.slot; slot++) await enter(page, selected.index, selected.answers[slot]);
    const current = await snapshot(page, selected.index);
    assert.equal(current.activeSlot, selected.slot);
    return selected;
}

async function completeRemaining(page, selected) {
    for (let slot = selected.slot + 1; slot < selected.answers.length; slot++) {
        await enter(page, selected.index, selected.answers[slot]);
    }
    await page.waitForFunction(i => state.items[i].done === true, selected.index);
    const result = await snapshot(page, selected.index);
    assert.equal(result.done, true, 'The whole sentence can be completed');
    assert.equal(result.chosen.length, selected.answers.length);
    return result;
}

(async () => {
    const browser = await chromium.launch({ headless: true,
        ...(process.env.CHROMIUM_EXECUTABLE ? { executablePath: process.env.CHROMIUM_EXECUTABLE } : {}) });
    const results = [];
    const scenarios = baseline ? [{ mobile: false, step: false, contraction: "haven't", variant: 'expanded' }]
        : [false, true].flatMap(mobile => [false, true].flatMap(step =>
            ['expanded', 'short'].map(variant => ({ mobile, step, variant,
                contraction: mobile && !step ? "hasn't" : "haven't" }))));
    try {
        for (const scenario of scenarios) {
            const { mobile, step, variant, contraction } = scenario;
            const name = `${mobile ? 'mobile' : 'desktop'}-${step ? 'step' : 'cards'}-${variant}`;
            const result = { name, contraction, startedAt: new Date().toISOString(),
                viewport: mobile ? { width: 390, height: 844 } : { width: 1440, height: 1000 },
                checks: [], pageErrors: [], failedRequests: [] };
            const context = await browser.newContext({ locale: 'uk-UA', serviceWorkers: 'block',
                viewport: mobile ? { width: 390, height: 844 } : { width: 1440, height: 1000 },
                isMobile: mobile, hasTouch: mobile });
            await context.route('**/*', route => new URL(route.request().url()).hostname === 'gramlyze.loc'
                ? route.continue() : route.abort('blockedbyclient'));
            const page = await context.newPage();
            page.setDefaultTimeout(15000);
            page.on('pageerror', error => result.pageErrors.push(error.message));
            const responseStatuses = new WeakMap();
            page.on('response', response => responseStatuses.set(response.request(), response.status()));
            page.on('requestfailed', request => {
                const url = new URL(request.url());
                if (url.hostname === 'gramlyze.loc') result.failedRequests.push({ path: url.pathname,
                    error: request.failure()?.errorText, status: responseStatuses.get(request) ?? null });
            });
            try {
                const response = await page.goto(`${base}/test/present-perfect-continuous/negatives${step ? '/step' : ''}?source=theory`, { waitUntil: 'load' });
                assert.equal(response.status(), 200);
                await ready(page);
                result.url = page.url();
                const selected = await selectQuestion(page, step, contraction, baseline || (!mobile && !step));
                result.question = selected.question;
                const prefix = contraction === "hasn't" ? 'has' : 'have';
                if (variant === 'expanded') {
                    await enter(page, selected.index, prefix, mobile ? 'Tab' : 'Enter');
                    result.prefix = await snapshot(page, selected.index);
                    await card(page, selected.index).scrollIntoViewIfNeeded();
                    await page.screenshot({ path: path.join(out, `${baseline ? 'baseline-' : ''}${name}-prefix.png`) });
                    if (baseline) {
                        result.bugReproduced = result.prefix.activeSlot === selected.slot
                            && !result.prefix.inputs.some(input => input.word === 1 && !input.disabled);
                        assert.equal(result.bugReproduced, true, 'Old UI accepts the prefix but cannot provide the second field');
                        result.checks.push('Baseline reproduced: valid have prefix, missing not field');
                    } else {
                        assert.equal(result.prefix.activeSlot, selected.slot, 'A prefix must not finish the answer slot');
                        assert.equal(result.prefix.chosen[selected.slot], null);
                        assert.ok(result.prefix.inputs.some(input => input.word === 1 && input.focused && !input.disabled), 'Extra not field is focused');
                        result.checks.push(`Expanded prefix creates and focuses the additional field (${mobile ? 'Tab' : 'Enter'})`);
                        await settle(page);
                        await page.reload({ waitUntil: 'load' });
                        await ready(page);
                        result.restored = await snapshot(page, selected.index);
                        assert.equal(result.restored.activeSlot, selected.slot);
                        assert.equal(result.restored.manual[selected.slot], prefix);
                        assert.ok(result.restored.inputs.some(input => input.word === 1 && !input.disabled), 'Expanded field survives document reload');
                        result.checks.push('Prefix and extra field survive a real reload');
                        await enter(page, selected.index, 'never');
                        const wrong = await snapshot(page, selected.index);
                        assert.equal(wrong.activeSlot, selected.slot);
                        assert.equal(wrong.chosen[selected.slot], null, 'Wrong second word cannot finish the slot');
                        assert.equal(wrong.wordIndex, 1, 'Wrong second word stays editable');
                        result.checks.push('Wrong continuation is rejected without locking the correct prefix');
                        await enter(page, selected.index, 'not');
                        const accepted = await snapshot(page, selected.index);
                        assert.equal(accepted.activeSlot, selected.slot + 1, 'not advances to the been slot');
                        assert.equal(accepted.chosen[selected.slot], `${prefix} not`);
                        result.checks.push('have/has + not accepted as a complete answer');
                        result.completed = await completeRemaining(page, selected);
                    }
                } else {
                    // Curly apostrophe is the spelling used in the screenshot.
                    await enter(page, selected.index, contraction.replace("'", '’'));
                    const accepted = await snapshot(page, selected.index);
                    assert.equal(accepted.activeSlot, selected.slot + 1, 'Short form advances directly to been');
                    result.checks.push('Contracted form with curly apostrophe is accepted directly');
                    result.completed = await completeRemaining(page, selected);
                }
                if (!baseline) {
                    result.checks.push('Entire real question completed through manual UI input');
                    await card(page, selected.index).scrollIntoViewIfNeeded();
                    await page.screenshot({ path: path.join(out, `${name}-completed.png`) });
                }
                await settle(page);
                assert.deepEqual(result.pageErrors, [], 'No browser JavaScript errors');
                // Chromium can report the intentionally empty 204 state response as
                // ERR_ABORTED. Keep the raw event in the report; the independent
                // reload assertions above verify that persistence really succeeded.
                assert.deepEqual(result.failedRequests.filter(request => !(request.status === 204
                    && request.path.endsWith('/state') && request.error === 'net::ERR_ABORTED')), [], 'No unexpected failed local requests');
            } catch (error) {
                result.error = error.message;
                await page.screenshot({ path: path.join(out, `${name}-failure.png`) }).catch(() => {});
            } finally {
                result.finishedAt = new Date().toISOString();
                results.push(result);
                console.log(JSON.stringify({ name, checks: result.checks, error: result.error }));
                await context.close();
                fs.writeFileSync(path.join(out, baseline ? 'baseline.json' : 'acceptance.json'), JSON.stringify(results, null, 2));
            }
        }
    } finally { await browser.close(); }
    if (results.some(result => result.error)) process.exitCode = 1;
})().catch(error => { console.error(error.message); process.exitCode = 1; });
