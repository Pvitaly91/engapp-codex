'use strict';
// Real UI acceptance for the unchanged finite M45 author payload. No answer or
// score assignments through Alpine; input/focus/click/keyboard only.
const fs = require('node:fs'), path = require('node:path'), assert = require('node:assert/strict'), crypto = require('node:crypto');
const { chromium } = require('playwright');
const shared = require('./capture-theory-template.cjs'), prior = require('./capture-m45-theory.cjs');
const ref = require('./capture-m44-reference-supplement.cjs');
const BASE = shared.BASE, PRIVATE = shared.PRIVATE, CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const sha = value => crypto.createHash('sha256').update(value).digest('hex'), norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const masterBytes = fs.readFileSync(path.resolve(__dirname, '../../docs/content/m45-authored-future-comparisons.v1.0.0.json'));
assert.equal(sha(masterBytes), '333b39ead3e5bb20c2bcde9c42878cf98f176158d7ae6324d0c6510b09b2afb7');
const master = JSON.parse(masterBytes);
function exactFields(actual, values, label) {
    let cursor = 0;
    for (const value of values.filter(value => value !== null && value !== undefined && value !== '')) { const expected = norm(value), index = actual.indexOf(expected, cursor); assert.ok(index >= 0, label + ': exact ordered author field ' + expected); cursor = index + expected.length; }
}
async function fidelity(page, lesson, javaScript = true) {
    const data = await page.evaluate(() => {
        const text = node => { if (!node) return null; const copy = node.cloneNode(true); copy.querySelectorAll('[data-theory-ui]').forEach(n => n.remove()); return copy.textContent.replace(/\s+/gu, ' ').trim(); };
        const basic = node => { const copy = node.cloneNode(true); copy.querySelectorAll('[data-theory-native-extension],details,[data-theory-ui]').forEach(n => n.remove()); return text(copy); };
        const detail = node => { const found = node.querySelector('[data-m45-detail]'); return found ? { id: found.dataset.m45Detail, text: text(found.querySelector('.theory-section-detail-body')), open: found.open, nested: found.querySelectorAll('details').length } : null; };
        const component = document.querySelector('[data-m45-practice-ui] [x-data^="m45PracticeUi"]');
        return {
            allText: text(document.querySelector('[data-theory-main]')),
            sections: [...document.querySelectorAll('[data-m45-author-section]')].map(section => ({ id: section.dataset.m45AuthorSection, title: text(section.querySelector('.theory-section-title')), points: [...section.querySelectorAll('[data-m45-basic-point]')].map(node => ({ id: node.id, text: basic(node), detail: detail(node) })), cards: [...section.querySelectorAll('[data-m45-form-card]')].map(node => ({ id: node.id, text: basic(node), rows: [...node.querySelectorAll('[data-m45-form-row]')].map(text), detail: detail(node) })) })),
            tasks: [...document.querySelectorAll('[data-m45-ui-case]')].map(node => ({ prompt: text(node.querySelector('[data-m45-author-prompt]')), feedback: text(node.querySelector('[data-m45-self-check-answer]')), controls: [...node.querySelectorAll('[data-m45-control]')].map(field => ({ id: field.dataset.m45Control, label: text(field.querySelector('legend')), text: text(field), placeholder: field.querySelector('textarea')?.getAttribute('placeholder') || null })) })),
            cases: window.Alpine && component ? JSON.parse(JSON.stringify(window.Alpine.$data(component).cases)) : null,
            fallback: document.querySelectorAll('[data-m45-static-fallback]').length,
        };
    });
    assert.equal(data.fallback, 0); assert.equal(data.sections.length, lesson.sections.length); assert.equal(data.tasks.length, lesson.practice.length);
    assert.ok(data.allText.includes(norm(lesson.subtitle_uk))); assert.ok(data.allText.includes(norm(lesson.hero_intro_uk)));
    let fields = 2, detailCount = 0;
    function detail(actual, expected) {
        if (!expected) return assert.equal(actual, null); assert.equal(actual.id, expected.id); assert.equal(actual.nested, 0); detailCount++;
        const values = [expected.title, ...expected.paragraphs_uk, ...expected.examples.flatMap(example => [example.en, example.uk, example.note_uk])]; exactFields(actual.text, values, expected.id); fields += values.filter(Boolean).length;
    }
    lesson.sections.forEach((section, index) => {
        const actual = data.sections[index]; assert.equal(actual.id, section.id); assert.ok(actual.title.includes(section.title)); assert.equal(actual.points.length, section.points.length); assert.equal(actual.cards.length, (section.cards || []).length);
        for (const note of section.notes_uk || []) assert.ok(data.allText.includes(norm(note)), 'Unchanged section note');
        section.points.forEach((point, number) => { const observed = actual.points[number]; assert.equal(observed.id, point.id); const values = [point.title, ...point.basic_uk, ...point.examples.flatMap(example => [example.en, example.uk, example.note_uk])]; exactFields(observed.text, values, point.id); fields += values.filter(Boolean).length; detail(observed.detail, point.detail); });
        (section.cards || []).forEach((card, number) => { const observed = actual.cards[number]; assert.equal(observed.id, card.id); assert.ok(observed.text.includes(norm(card.title))); assert.equal(observed.rows.length, 3); card.rows.forEach((row, n) => { exactFields(observed.rows[n], [row.label_uk, row.formula, row.en, row.uk], card.id + ' row' + n); fields += 4; }); if (card.note_uk) assert.ok(observed.text.includes(norm(card.note_uk))); detail(observed.detail, card.detail); });
    });
    lesson.practice.forEach((task, index) => {
        const actual = data.tasks[index]; exactFields(actual.prompt, [task.title, task.prompt_uk, task.context_uk], task.id + ' prompt'); exactFields(actual.feedback, [...task.feedback.paragraphs_uk, ...task.feedback.answer_examples.flatMap(example => [example.en, example.uk, example.note_uk])], task.id + ' feedback'); assert.equal(actual.controls.length, task.controls.length);
        task.controls.forEach((control, n) => { const field = actual.controls[n]; assert.equal(field.id, control.id); assert.equal(field.label, norm(control.label_uk)); assert.equal(field.placeholder, null, 'No ready word-order answer placeholder'); for (const value of [control.stimulus_uk, control.stimulus_en]) if (value) assert.ok(field.text.includes(norm(value))); });
    });
    if (javaScript) assert.deepEqual(data.cases, prior.expectedCases(lesson), 'Exact runtime answers, aliases, tokens, identities and scoring contract');
    return { fields, details: detailCount, sections: data.sections.length, tasks: data.tasks.length, controls: data.tasks.reduce((sum, task) => sum + task.controls.length, 0), formCards: data.sections.reduce((sum, section) => sum + section.cards.length, 0), orderedExactAuthorFields: true, exactRuntimeCases: javaScript };
}
async function layout(page) {
    return page.evaluate(() => {
        const main = document.querySelector('[data-theory-main]'), outer = main.getBoundingClientRect();
        return [...main.querySelectorAll('article,section,table,fieldset,textarea,button,details,.theory-example,.theory-item')].filter(node => node.checkVisibility()).map(node => ({ tag: node.tagName, id: node.id, classes: node.className, rect: node.getBoundingClientRect().toJSON(), scrollWidth: node.scrollWidth, clientWidth: node.clientWidth, tableScroll: Boolean(node.closest('.theory-table-scroll')) })).filter(node => !node.tableScroll && (node.rect.right > outer.right + 1 || node.rect.left < outer.left - 1 || node.scrollWidth > node.clientWidth + 2));
    });
}
async function details(page, lesson, dir, stem) {
    const nodes = page.locator('[data-m45-detail]'), expected = lesson.sections.flatMap(section => [...section.points, ...(section.cards || [])]).flatMap(point => point.detail ? [point.detail.id] : []); assert.equal(await nodes.count(), expected.length);
    const count = () => nodes.evaluateAll(items => items.filter(node => node.open).length), screenshots = [];
    assert.equal(await count(), 0);
    for (const [index, id] of expected.entries()) { const node = page.locator('[data-m45-detail="' + id + '"]'), summary = node.locator(':scope > summary'); await summary.focus(); await summary.press(index % 2 ? 'Space' : 'Enter'); assert.equal(await count(), 1); assert.equal(await node.evaluate(node => node.open), true); assert.equal(await node.locator('.theory-section-detail-body').evaluate(node => node.checkVisibility()), true); screenshots.push(await shared.screenshot(node, dir, stem + '-detail-' + index + '.png')); await summary.press(index % 2 ? 'Enter' : 'Space'); assert.equal(await count(), 0); }
    await nodes.first().locator(':scope > summary').focus(); await nodes.first().locator(':scope > summary').press('Enter'); const states = await nodes.evaluateAll(items => items.map(node => node.open)); await page.emulateMedia({ media: 'print' }); await page.waitForFunction(() => [...document.querySelectorAll('[data-m45-detail]')].every(node => node.open)); await page.emulateMedia({ media: 'screen' }); await page.waitForFunction(states => JSON.stringify([...document.querySelectorAll('[data-m45-detail]')].map(node => node.open)) === JSON.stringify(states), states); await nodes.first().locator(':scope > summary').press('Enter');
    for (const id of expected) { await page.evaluate(id => { location.hash = id; }, id); await page.waitForFunction(id => document.getElementById(id)?.closest('details')?.open, id); assert.equal(await count(), 1); const node = page.locator('[data-m45-detail="' + id + '"]'); await node.locator(':scope > summary').focus(); await node.locator(':scope > summary').press('Enter'); await page.evaluate(() => history.replaceState(null, '', location.pathname)); }
    for (const node of await nodes.all()) { await node.locator(':scope > summary').focus(); await node.locator(':scope > summary').press('Enter'); } const expandedFidelity = await fidelity(page, lesson); assert.deepEqual(await layout(page), []); for (const node of await nodes.all()) { await node.locator(':scope > summary').focus(); await node.locator(':scope > summary').press('Enter'); }
    return { count: expected.length, keyboardIndependent: true, deepLinks: expected, printRestored: true, expandedFidelity, screenshots };
}
async function practice(page, lesson, dir, stem) {
    const rows = [], score = page.locator('[data-m45-ui-score] span');
    const expectScore = async value => { await page.waitForFunction(value => document.querySelector('[data-m45-ui-score] span')?.textContent.trim() === String(value), value); assert.equal(norm(await score.textContent()), String(value)); };
    const answer = async (card, control, value) => { const field = card.locator('[data-m45-control="' + control.id + '"]'); if (['manual', 'tokens'].includes(control.kind)) await field.locator('[data-m45-answer-input]').fill(value); else { const button = field.locator('[data-m45-answer="' + value + '"]'); await button.focus(); await button.press('Space'); } };
    const check = async (card, correct, total = correct ? 1 : 0) => { await card.locator('[data-m45-check]').click(); const feedback = card.locator('[data-m45-case-feedback]'); await feedback.waitFor({ state: 'visible' }); assert.equal(norm(await feedback.textContent()), correct ? 'Правильно' : 'Не всі частини відповіді правильні'); await expectScore(total); };
    const reset = async card => { await card.locator('[data-m45-reset]').click(); await card.locator('[data-m45-case-feedback]').waitFor({ state: 'hidden' }); await expectScore(0); for (const input of await card.locator('[data-m45-answer-input]').all()) assert.equal(await input.inputValue(), ''); assert.equal(await card.locator('[data-m45-answer][aria-checked="true"]').count(), 0); };
    const build = async (card, control) => { const field = card.locator('[data-m45-control="' + control.id + '"]'), ordered = prior.orderedTokens(control); for (const value of ordered) { let token = null; for (const option of await field.getByRole('button', { name: value, exact: true }).all()) if (await option.isEnabled()) { token = option; break; } assert.ok(token, 'Available actual token instance ' + value); await token.focus(); await token.press('Space'); } assert.equal(await field.locator('[data-m45-answer-input]').inputValue(), ordered.join(' ')); assert.equal(await field.locator('[data-m45-token]:not([disabled])').count(), 0); };
    for (const [index, task] of lesson.practice.entries()) {
        const card = page.locator('[data-m45-ui-case="' + (index + 1) + '"]'), control = task.controls[0], row = { id: task.id, screenshots: [], aliases: [] }; assert.equal(task.controls.length, 1); rows.push(row); await expectScore(0); await check(card, false);
        const manual = ['manual', 'tokens'].includes(control.kind), wrong = manual ? 'invalid probe one' : control.options.find(option => option.value !== control.correct_value).value;
        if (manual) { await answer(card, control, control.canonical_answer.split(' ')[0]); await check(card, false); }
        await answer(card, control, wrong); await check(card, false); await answer(card, control, manual ? 'invalid probe two' : wrong); await check(card, false); row.screenshots.push(await shared.screenshot(card, dir, stem + '-task-' + index + '-wrong.png'));
        await answer(card, control, manual ? control.canonical_answer : control.correct_value); await card.locator('[data-m45-case-feedback]').waitFor({ state: 'hidden' }); if (manual) { await card.locator('[data-m45-answer-input]').press('Control+Enter'); await card.locator('[data-m45-case-feedback]').waitFor({ state: 'visible' }); await expectScore(1); } await check(card, true); await check(card, true); row.screenshots.push(await shared.screenshot(card, dir, stem + '-task-' + index + '-correct.png'));
        for (const alias of [...new Set([...(control.accepted_answers || []), ...[control.canonical_answer, ...(control.accepted_answers || [])].filter(value => value?.includes("'")).map(value => value.replaceAll("'", '’'))])]) { await answer(card, control, '  ' + alias.replace(/ /gu, '  ') + '  '); await card.locator('[data-m45-case-feedback]').waitFor({ state: 'hidden' }); await check(card, true); row.aliases.push(alias); }
        if (control.kind === 'tokens') {
            const field = card.locator('[data-m45-control="' + control.id + '"]'); await answer(card, control, control.canonical_answer); await page.waitForFunction(({ id, count }) => document.querySelector('[data-m45-control="' + id + '"]').querySelectorAll('[data-m45-token][disabled]').length === count, { id: control.id, count: control.tokens.length });
            if (control.tokens.includes('?')) { await answer(card, control, control.canonical_answer.replace(/\?/gu, '')); await page.waitForFunction(id => document.querySelector('[data-m45-control="' + id + '"]').querySelectorAll('[data-m45-token]:not([disabled])').length === 1, control.id); assert.equal(norm(await field.locator('[data-m45-token]:not([disabled])').textContent()), '?'); await answer(card, control, control.canonical_answer); await check(card, true); row.questionMarkReturn = true; }
            await reset(card); await build(card, control); await check(card, true); await field.locator('[data-m45-answer-input]').fill(''); await expectScore(0); assert.equal(await field.locator('[data-m45-token][disabled]').count(), 0); await build(card, control); await check(card, true); row.tokens = { count: control.tokens.length, duplicateCount: control.tokens.length - new Set(control.tokens).size, returnedAfterClear: true, rebuiltWithKeyboard: true };
        }
        await reset(card); row.screenshots.push(await shared.screenshot(card, dir, stem + '-task-' + index + '-reset.png')); Object.assign(row, { empty: true, wrongTwice: true, editedCorrect: true, staleFeedbackCleared: true, noDoubleScore: true, reset: true });
    }
    for (const [index, task] of lesson.practice.entries()) { const card = page.locator('[data-m45-ui-case="' + (index + 1) + '"]'), control = task.controls[0]; await answer(card, control, ['manual', 'tokens'].includes(control.kind) ? control.canonical_answer : control.correct_value); await check(card, true, index + 1); } await check(page.locator('[data-m45-ui-case="6"]'), true, 6); for (let i = 1; i <= 6; i++) await page.locator('[data-m45-ui-case="' + i + '"] [data-m45-reset]').click(); await expectScore(0);
    return { tasks: rows, allCorrectScore: 6, finalScore: 0, actualUIOnly: true };
}
async function run(label, supplement = false) {
    assert.match(label, /^interaction-(?:before|after|supplement)-v[1-9][0-9]*$/u); const dir = path.join(PRIVATE, label); assert.equal(fs.existsSync(dir), false); fs.mkdirSync(dir, { recursive: true });
    const report = { label, base: BASE, masterSha256: sha(masterBytes), startedAt: new Date().toISOString(), sourcesBefore: shared.hashes(), rows: [], pass: false }, browser = await chromium.launch({ headless: true, executablePath: CHROME });
    try {
        for (const [index, lesson] of master.lessons.entries()) for (const javaScriptEnabled of supplement ? [true, false] : [true]) {
            const viewport = supplement ? { width: javaScriptEnabled ? 320 : 390, height: 844 } : { width: 1440, height: 1000 }, row = { key: 'ABC'[index], path: lesson.theory_path, viewport, javaScriptEnabled, pass: false }, context = await browser.newContext({ viewport, colorScheme: 'light', deviceScaleFactor: 1, javaScriptEnabled, serviceWorkers: 'block' }), page = await context.newPage(), stem = row.key + (supplement ? javaScriptEnabled ? '-320' : '-nojs' : '-desktop'); report.rows.push(row);
            try {
                await shared.observe(context, page, row); const response = await page.goto(BASE + lesson.theory_path, { waitUntil: 'networkidle', timeout: 60000 }); assert.equal(response.status(), 200); if (javaScriptEnabled) await page.waitForFunction(() => Boolean(window.Alpine)); row.fonts = await ref.fontEvidence(page); row.fidelity = await fidelity(page, lesson, javaScriptEnabled); assert.deepEqual(await layout(page), []);
                if (javaScriptEnabled) { row.details = await details(page, lesson, dir, stem); if (!supplement) row.practice = await practice(page, lesson, dir, stem); }
                else {
                    for (const detail of await page.locator('[data-m45-detail],[data-m45-ui-explanation]').all()) { await detail.locator(':scope > summary').focus(); await detail.locator(':scope > summary').press('Enter'); assert.equal(await detail.evaluate(node => node.open), true); }
                    assert.equal(await page.locator('[data-m45-ui-explanation]').count(), 6); row.selfCheckKeys = 6;
                    for (const [i, task] of lesson.practice.entries()) for (const control of task.controls) if (control.kind === 'tokens') assert.deepEqual((await page.locator('[data-m45-ui-case="' + (i + 1) + '"] [data-m45-static-token]').allTextContents()).map(norm), control.tokens);
                }
                row.screenshot = await shared.screenshot(page, dir, stem + '-full.png', { fullPage: true }); row.expectedDisabledScripts = row.localFailures.filter(item => !javaScriptEnabled && item.failure === 'csp' && item.type === 'script'); assert.deepEqual(row.localFailures.filter(item => !row.expectedDisabledScripts.includes(item)), []); assert.deepEqual(row.errors, []); assert.deepEqual(row.blocked, []); row.pass = true;
            } catch (error) { row.failure = { name: error.name, message: error.message, stack: error.stack, actual: error.actual, expected: error.expected }; }
            finally { await context.close(); fs.writeFileSync(path.join(dir, stem + '.json'), JSON.stringify(row, null, 2) + '\n', { flag: 'wx' }); }
            console.log(JSON.stringify({ key: row.key, width: viewport.width, javaScriptEnabled, pass: row.pass, error: row.failure?.message.split('\n')[0] || null }));
        }
        report.sourcesAfter = shared.hashes(); assert.deepEqual(report.sourcesAfter, report.sourcesBefore); report.pass = report.rows.every(row => row.pass);
    } finally { report.finishedAt = new Date().toISOString(); fs.writeFileSync(path.join(dir, 'manifest.json'), JSON.stringify(report, null, 2) + '\n', { flag: 'wx' }); await browser.close(); }
    console.log(JSON.stringify({ directory: dir, pass: report.pass, count: report.rows.length, sha256: sha(fs.readFileSync(path.join(dir, 'manifest.json'))) })); return report;
}
if (require.main === module) run(process.argv[2], process.argv[3] === '--supplement').then(report => { if (!report.pass) process.exitCode = 1; }).catch(error => { console.error(error.stack); process.exitCode = 1; });
module.exports = { run, fidelity, practice, details, layout };
