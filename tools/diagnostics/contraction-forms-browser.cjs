// Local browser acceptance. Captures public question text and results, no credentials.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const out = path.resolve('storage/app/contraction-forms');
fs.mkdirSync(out, { recursive: true });
const cases = [
    ['modal-verbs/can-could', "can't", false, false],
    ['future-simple/forms', "I'll", false, false],
    ['future-simple/forms', 'I will', true, false],
    ['present-simple/negatives', 'does not', false, false],
    ['past-simple/negatives', "didn't", false, true],
    ['present-continuous/negatives', "isn't", false, true],
    ['present-perfect-continuous/negatives', "haven't", true, true],
    ['verb-to-be/present', "She's", false, false],
    ['types-of-questions/negative-questions-dont-you-know', "Don't", false, false],
];
(async () => {
    const browser = await chromium.launch({ headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE });
    const results = [];
    try {
        for (const [topic, target, step, mobile] of cases) {
            const result = { topic, target, step, mobile, errors: [], startedAt: new Date().toISOString() };
            const context = await browser.newContext({ locale: 'uk-UA', viewport: mobile ? { width: 390, height: 844 } : { width: 1440, height: 1000 } });
            await context.route('**/*', route => new URL(route.request().url()).hostname === 'gramlyze.loc' ? route.continue() : route.abort());
            const page = await context.newPage();
            page.setDefaultTimeout(10000);
            page.on('pageerror', error => result.errors.push(error.message));
            const name = `${topic.replaceAll('/', '-')}-${target.replace(/[^a-z]/gi, '')}-${step ? 'step' : 'cards'}`;
            try {
                const response = await page.goto(`http://gramlyze.loc/test/${topic}${step ? '/step' : ''}?source=theory`);
                assert.equal(response.status(), 200);
                await page.waitForFunction(() => typeof state !== 'undefined' && state.items?.length);
                const selected = await page.evaluate(target => {
                    const index = state.items.findIndex(q => q.presentation !== 'sentence_reorder' && q.answers?.some(a => a.toLowerCase().includes(target.toLowerCase())));
                    if (index < 0) return null;
                    const q = state.items[index];
                    const slot = q.answers.findIndex(a => a.toLowerCase().includes(target.toLowerCase()));
                    const expected = q.answers[slot];
                    const alternatives = acceptedTestAnswers(q, slot).filter(a => a.toLowerCase() !== expected.toLowerCase());
                    const alternate = alternatives.find(a => target.includes("'") ? !a.includes("'") : a.includes("'"));
                    return { index, slot, question: q.question, expected, alternate, answers: q.answers };
                }, target);
                result.selected = selected;
                assert.ok(selected?.alternate, `A real selected question has the requested alternate spelling: ${JSON.stringify(selected)}`);
                async function answerQuestion(index, answers, replacementSlot = -1, replacement = '') {
                    for (let slot = 0; slot < answers.length; slot++) {
                        const value = slot === replacementSlot ? replacement : answers[slot];
                        for (const word of value.split(/\s+/)) {
                            const input = page.locator(`article[data-idx="${index}"] input[data-manual-active="true"]:not([disabled]):visible`).first();
                            await input.fill(word);
                            await input.press('Enter');
                            await page.waitForTimeout(80);
                        }
                    }
                    await page.waitForFunction(i => state.items[i].done, index);
                }
                if (step) {
                    for (let index = 0; index < selected.index; index++) {
                        const q = await page.evaluate(i => state.items[i], index);
                        if (q.presentation === 'sentence_reorder') {
                            let rest = q.reorder_answer;
                            const used = new Set();
                            while (rest) {
                                const token = q.reorder_tokens.findIndex((value, i) => !used.has(i) && (rest === value || rest.startsWith(value + ' ')));
                                assert.notEqual(token, -1);
                                await page.locator(`[data-reorder-action="add"][data-reorder-token-index="${token}"]`).click();
                                rest = rest.slice(q.reorder_tokens[token].length).trim();
                                used.add(token);
                            }
                            await page.locator('[data-reorder-action="check"]').click();
                        } else await answerQuestion(index, q.answers);
                        await page.locator('#next').click();
                    }
                }
                await answerQuestion(selected.index, selected.answers, selected.slot, selected.alternate);
                assert.equal(await page.evaluate(i => state.items[i].wrongAttempt, selected.index), false);
                await page.waitForTimeout(300);
                await page.evaluate(async () => { await JS_TEST_SAVE_QUEUE; });
                // Keep the guest session but remove its browser backup: restoration
                // must also work from the server snapshot with grouped markers.
                await page.evaluate(() => { localStorage.clear(); sessionStorage.clear(); });
                await page.reload();
                await page.waitForFunction(i => typeof state !== 'undefined' && state.items?.[i]?.done, selected.index);
                assert.equal(await page.evaluate(({index, slot}) => state.items[index].chosen[slot], selected), selected.alternate);
                await page.locator(`article[data-idx="${selected.index}"]`).scrollIntoViewIfNeeded();
                await page.screenshot({ path: path.join(out, `${name}.png`) });
                assert.deepEqual(result.errors, []);
                result.passed = true;
            } catch (error) {
                result.failure = error.message;
                await page.screenshot({ path: path.join(out, `${name}-failure.png`) }).catch(() => {});
            }
            results.push(result);
            console.log(JSON.stringify({ topic, target, passed: result.passed, failure: result.failure }));
            fs.writeFileSync(path.join(out, 'acceptance.json'), JSON.stringify(results, null, 2));
            await context.close();
        }
    } finally { await browser.close(); }
    if (results.some(r => !r.passed)) process.exitCode = 1;
})().catch(error => { console.error(error); process.exitCode = 1; });
