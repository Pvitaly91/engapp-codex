// Real local UI acceptance. Test-progress and AI requests are isolated in memory;
// no learning-data writes, production requests, cookies or tokens are exported.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {chromium} = require('playwright');
const base = 'http://gramlyze.loc';
const out = path.resolve('storage/app/ppc-answer-retry-local');
const snapshot = (page, i) => page.evaluate(i => {
    const q = state.items[i];
    return {i, id: q.id, uuid: q.uuid, question: q.question, answers: q.answers,
        presentation: q.presentation, tense: q.tense, chosen: q.chosen,
        isPpc: q.is_past_perfect_continuous, technicalInfoHidden: q.tech_info == null,
        done: q.done, feedback: q.feedback, feedbackMeta: q.feedbackMeta,
        wrongAttempt: q.wrongAttempt, attempts: q.attemptsBySlot,
        correct: state.correct, answered: state.answered,
        activeSlot: q.activeSlot, manualInputs: q.manualInputsBySlot, wordIndex: q.manualWordIndexBySlot,
        reorder_tokens: q.reorder_tokens, reorder_answer: q.reorder_answer};
}, i);
const card = (page, i) => page.locator(`article[data-idx="${i}"]`);
async function complete(page, i, key = 'Enter') {
    for (let turn = 0; turn < 80; turn++) {
        const q = await snapshot(page, i);
        if (q.done) { assert.equal(q.feedback, 'correct'); return; }
        if (q.presentation === 'sentence_reorder') {
            // Choose actual bank buttons in canonical order, no state mutation.
            let phrase = '';
            const unused = [...q.reorder_tokens.entries()];
            while (unused.length) {
                const match = unused.find(([_, token]) => q.reorder_answer.startsWith(phrase ? phrase + ' ' + token : token));
                assert.ok(match, 'Canonical reorder tokens can be selected');
                const [index, token] = match;
                phrase = phrase ? phrase + ' ' + token : token;
                await card(page, i).locator(`[data-reorder-token-index="${index}"]`).click();
                unused.splice(unused.findIndex(([j]) => j === index), 1);
            }
            await card(page, i).locator('[data-reorder-action="check"]').click();
        } else {
            const input = card(page, i).locator('input[data-manual-active="true"]:not([disabled])').first();
            const slot = Number(await input.getAttribute('data-manual-gap'));
            const word = Number(await input.getAttribute('data-manual-word'));
            const expected = await page.evaluate(({i, slot, word}) => {
                const value = manualAnswerWords(state.items[i], slot)[word];
                return value?.split(/\s+/)[0];
            }, {i, slot, word});
            assert.ok(expected, 'Active field has a canonical word');
            await input.fill(expected);
            await input.press('Escape');
            await input.press(key);
        }
    }
    throw Error('Question did not complete in 80 UI actions: ' + JSON.stringify(await snapshot(page, i)));
}
async function reach(page, i, step) {
    if (step) for (let j = 0; j < i; j++) {
        await complete(page, j);
        await page.locator('#next').click();
    }
    await card(page, i).scrollIntoViewIfNeeded();
}
async function runRow(browser, topic, step, mobile = false, builder = false) {
    const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000}});
    const page = await context.newPage(), errors = [], isolated = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.route('**/*', async route => {
        const request = route.request(), url = new URL(request.url());
        if (request.method() !== 'GET' && request.method() !== 'HEAD') {
            isolated.push({method: request.method(), path: url.pathname});
            if (/\/state$/.test(url.pathname)) return route.fulfill({status: 204});
            if (/explanation|hints/.test(url.pathname)) return route.fulfill({status: 200, contentType: 'application/json', body: '{}'});
            return route.abort();
        }
        if (url.hostname === 'gramlyze.com' || url.hostname === 'gramlyze.ub') return route.abort();
        return route.continue();
    });
    try {
        const suffix = step ? '/step' : '';
        const url = `${base}/test/past-perfect-continuous/${topic}${suffix}`;
        const response = await page.goto(url, {waitUntil: 'domcontentloaded', timeout: 30000});
        assert.equal(response.status(), 200);
        await page.waitForFunction(() => typeof state !== 'undefined' && state.items?.length > 0);
        const i = await page.evaluate(({topic, builder}) => state.items.findIndex(q => !q.presentation && (builder
            ? String(q.type) === '4'
            : q.question.includes('{') && (topic !== 'forms' || q.question === 'Mia {a1} notes for half an hour before lunch.'))), {topic, builder});
        assert.ok(i >= 0, 'Real English gap question found');
        await reach(page, i, step);
        const before = await snapshot(page, i);
        assert.equal(before.isPpc, true, 'Guest gets a safe authoritative PPC identity');
        assert.equal(before.technicalInfoHidden, true, 'No administrator data is exposed');
        const wrong = await page.evaluate(i => {
            const q = state.items[i];
            const wrongOptions = getActiveOptions(q).filter(answer => !testAnswerMatches(q, q.activeSlot, answer));
            const firstWord = q.answers[q.activeSlot].split(/\s+/)[0].toLowerCase();
            return wrongOptions.find(answer => answer.split(/\s+/)[0].toLowerCase() !== firstWord) || wrongOptions[0];
        }, i);
        assert.ok(wrong);
        const toggle = card(page, i).locator('[data-options-toggle]');
        if (await toggle.getAttribute('aria-expanded') === 'false') await toggle.click();
        for (let attempt = 0; attempt < 2; attempt++) {
            await card(page, i).locator('button[data-opt]').filter({hasText: wrong}).first().click();
            const q = await snapshot(page, i);
            assert.equal(q.done, false, 'Wrong attempts do not finish PPC');
            assert.equal(q.chosen[0], null, 'Canonical answer is never silently auto-filled');
            assert.equal(q.feedbackMeta.result, 'incorrect');
        }
        const input = card(page, i).locator('input[data-manual-active="true"]:not([disabled])').first();
        if (await input.inputValue() === before.answers[0].split(/\s+/)[0]) await input.fill('draft');
        await input.fill(before.answers[0].split(/\s+/)[0]);
        let draft = await snapshot(page, i);
        assert.equal(draft.feedback, '', 'Editing clears stale feedback');
        assert.equal(draft.wrongAttempt, true, 'Editing keeps first-attempt history');
        assert.equal(await input.getAttribute('data-answer-state'), 'active');
        await complete(page, i, mobile ? 'Tab' : 'Enter');
        const after = await snapshot(page, i);
        assert.equal(after.done, true);
        assert.equal(after.feedback, 'correct');
        assert.equal(after.correct, before.correct, 'A retry does not earn first-attempt credit');
        const name = `${topic}-${step ? 'step' : 'cards'}-${mobile ? 'mobile' : 'desktop'}${builder ? '-builder' : ''}`;
        await card(page, i).screenshot({path: path.join(out, name + '-correct.png')});
        // All PPC reorder cards, including existing flags, hide the order hint.
        const reorderCount = await page.evaluate(() => state.items.filter(q => q.presentation === 'sentence_reorder').length);
        assert.ok(reorderCount > 0);
        assert.equal(await page.locator('[data-reorder-template-constraint]').count(), 0);
        const reorderI = await page.evaluate(() => state.items.findIndex(q => q.presentation === 'sentence_reorder'));
        if (!step) {
            await card(page, reorderI).screenshot({path: path.join(out, name + '-reorder.png')});
            await complete(page, reorderI);
        }
        // Verify a real saved browser snapshot can repair an old auto-filled slot.
        if (topic === 'forms' && !mobile) {
            await page.evaluate(({i, wrong}) => {
                const q = state.items[i];
                q.chosen[0] = q.answers[0];
                q.done = true;
                q.wrongAttempt = true;
                q.feedback = testUi('status.incorrect');
                q.feedbackMeta = {slotIndex: 0, wordIndex: null, result: 'incorrect', submittedAnswer: wrong, displayedAnswer: q.answers[0]};
                q.attemptsBySlot[0] = 0;
                persistState(state, true);
            }, {i, wrong});
            await page.reload({waitUntil: 'domcontentloaded'});
            await page.waitForFunction(() => typeof state !== 'undefined' && state.items?.length > 0);
            const restored = await snapshot(page, i);
            assert.equal(restored.done, false);
            assert.equal(restored.chosen[0], null);
            assert.equal(restored.wrongAttempt, true);
            assert.ok(restored.attempts[0] >= 2);
            await complete(page, i);
        }
        assert.deepEqual(errors, []);
        return {url, builder, guestIdentity: before.isPpc, technicalInfoHidden: before.technicalInfoHidden,
            viewport: mobile ? '390x844' : '1440x1000', question: before.question, answer: before.answers,
            questionId: before.id, wrongSubmission: wrong, retryAccepted: true, staleFeedbackCleared: true,
            reorderCount, orderHintCount: 0, oldSnapshotRecovered: topic === 'forms' && !mobile,
            consolePageErrors: errors, isolatedRequestPaths: [...new Set(isolated.map(r => r.path))], pass: true};
    } finally { await context.close(); }
}
async function main() {
    fs.mkdirSync(out, {recursive: true});
    const browser = await chromium.launch({executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true});
    const rows = [];
    try {
        for (const topic of ['forms', 'negatives', 'questions', 'time-expressions']) for (const step of [false, true]) {
            console.log(`Checking ${topic} ${step ? 'step' : 'cards'}`);
            rows.push(await runRow(browser, topic, step));
        }
        for (const step of [false, true]) rows.push(await runRow(browser, 'forms', step, true));
        for (const step of [false, true]) rows.push(await runRow(browser, 'forms', step, false, true));
    } finally {
        await browser.close();
        fs.writeFileSync(path.join(out, 'acceptance.json'), JSON.stringify({at: new Date().toISOString(), base, rows, pass: rows.length === 12 && rows.every(r => r.pass)}, null, 2));
    }
    console.log(JSON.stringify({pass: true, states: rows.length, out}));
}
main().catch(error => {console.error(error.stack); process.exitCode = 1;});
