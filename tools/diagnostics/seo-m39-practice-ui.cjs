'use strict';
// GET-only real gramlyze.loc practice UI evidence; no app/DB/Git mutations.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const {htmlText, BASE} = require('./seo-m28-local.cjs');
const {observe, clean} = require('./seo-m39-local.cjs');
const ROOT = path.resolve(__dirname, '../..');
const read = name => JSON.parse(fs.readFileSync(path.join(ROOT, name), 'utf8'));
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const master = read('docs/content/m23-authored-content.v1.json');
const snapshot = read('database/content-patches/m39-practice-ui.v1.json');
const targets = snapshot.targets.map((target, index) => ({...target,
    slug: target.after.slug, path: master.lessons[index].theory_path,
    data: JSON.parse(target.after.page.blocks.find(block => block.type === 'practice-set').body)}));
const sourceHashes = () => Object.fromEntries(['database/content-patches/m39-practice-ui.v1.json',
    'public/js/authored-practice-ui.js', 'public/js/m39-practice-ui.js',
    'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php',
    'resources/views/engram/theory/blocks-v3/m39-practice-ui.blade.php']
    .map(file => [file, sha(fs.readFileSync(path.join(ROOT, file)))]));
const answerFragments = {
    'n1-form': ['is', 'are'], 'n1-head': ['expansion', 'rooms'],
    'n3-equivalence': ['Ні.', 'Так.'],
    'n3-added-facts': ['час уже скоротився', 'обслуговування поліпшилося', 'лише план'],
    'c2-3-b': ['факт друку не заданий', 'надрукувала', 'не надрукувала'],
    'c2-4-guaranteed': ['кожний непридатний', 'принаймні один непридатний', 'рівно один непридатний'],
};
function assertOptionOnly(label, intended, key) {
    assert.equal(norm(label), norm(intended), 'Visible candidate is exactly its intended answer fragment');
    assert.doesNotMatch(label, /<[^>]+>/u, 'No raw HTML in a selectable candidate');
    assert.notEqual(norm(label), htmlText(key), 'Full author key must not become an option');
    const dom = new JSDOM('<div>' + key + '</div>');
    try {
        for (const english of dom.window.document.querySelectorAll('[lang="en"]')) {
            const answer = norm(english.textContent);
            assert.ok(!norm(label).startsWith(answer + ' — '), 'Answer plus translation/rationale leaked into option');
        }
    } finally {dom.window.close();}
}
function uiData(target) { return target.data; }
async function caseLocator(page, task) { return page.locator('[data-m39-ui-case="' + task.source_index + '"]'); }
function field(card, control) { return card.locator('[data-m39-control="' + control.id + '"]'); }
function option(fieldLocator, control, value) {
    const item = control.options.find(candidate => candidate.value === value); assert.ok(item, 'Declared candidate exists');
    return fieldLocator.getByRole(control.kind === 'multi' ? 'checkbox' : 'radio', {name: item.label, exact: true});
}
async function showCard(card) { await card.scrollIntoViewIfNeeded(); }
async function shot(card, dir, name) {
    const file = path.join(dir, name); assert.ok(!fs.existsSync(file), 'Exclusive screenshot');
    const page = card.page(), original = page.viewportSize();
    const height = await card.evaluate(node => node.getBoundingClientRect().height);
    // Keep the tested width, but capture the bounded case rather than cutting
    // a tall open answer/key behind the sticky header. Never hide page UI.
    if (height + 140 > original.height) await page.setViewportSize({width: original.width, height: Math.ceil(height) + 160});
    try {
        await card.evaluate(node => window.scrollTo(0, node.getBoundingClientRect().top + scrollY - 110));
        await card.screenshot({path: file, animations: 'disabled'});
    } finally {if (page.viewportSize().height !== original.height) await page.setViewportSize(original);}
    return name;
}
async function pageReady(page, target, row, violations, viewport) {
    await observe(page, row, violations);
    assert.equal(new URL(BASE + target.path).origin, BASE);
    const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle'});
    assert.equal(response.status(), 200);
    await page.waitForFunction(() => Boolean(window.Alpine && document.querySelector('[data-m39-practice-ui] [x-data]')));
    await page.evaluate(() => {document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light');});
    await page.waitForFunction(() => {
        const node = document.querySelector('[data-m39-practice-ui] [x-data]');
        return window.Alpine.$data(node)?.cases?.length === 6;
    });
    assert.equal(await page.locator('[data-m39-ui-case]').count(), 6);
    assert.deepEqual(await page.locator('[data-m39-ui-case]').evaluateAll(nodes => nodes.map(node => Number(node.dataset.m39UiCase))), [1,2,3,4,5,6]);
    row.viewport = viewport; row.theme = 'light';
}
async function verifyCase(page, target, task, initial = true) {
    const card = await caseLocator(page, task);
    assert.equal(await card.getAttribute('data-m39-ui-interaction'), task.interaction);
    assert.equal(htmlText(await card.locator('[data-m39-author-prompt]').innerHTML()),
        htmlText(target.data.author_self_check.prompts[task.source_index - 1]), 'Exact original prompt');
    const key = target.data.author_self_check.answers[task.source_index - 1];
    const explanation = card.locator('[data-m39-ui-explanation]');
    const keyNode = explanation.locator('[data-m39-self-check-answer]');
    assert.equal(await keyNode.count(), 1);
    assert.equal(htmlText(await keyNode.innerHTML()), htmlText(key), 'Exact full original author key');
    assert.equal(await keyNode.evaluate(node => getComputedStyle(node).textTransform), 'none');
    if (initial) {
        assert.equal(await explanation.evaluate(node => node.open), false, 'Key is closed before check');
        assert.equal(await keyNode.isVisible(), false, 'Explanation is not a visible candidate');
        assert.equal(await explanation.locator(':scope > summary').isVisible(), false);
    }
    const controls = [];
    for (const control of task.controls) {
        const area = field(card, control); assert.equal(await area.count(), 1);
        assert.equal(await area.getAttribute('data-m39-control-kind'), control.kind);
        const result = {id: control.id, kind: control.kind, labels: [], naturalCasing: true, explanationSeparate: true};
        if (control.kind === 'manual') {
            assert.equal(await area.locator('textarea').getAttribute('autocomplete'), 'off');
            assert.equal(await area.locator('textarea').evaluate(node => getComputedStyle(node).textTransform), 'none');
            const tokens = area.locator('[data-m39-token]'); assert.equal(await tokens.count(), control.tokens.length);
            const labels = (await tokens.allTextContents()).map(norm);
            assert.deepEqual(labels.slice().sort(), control.tokens.slice().sort(), 'Only original answer tokens');
            assert.ok(labels.every(label => label.split(/\s+/u).length <= 3));
            for (const token of await tokens.all()) assert.equal(await token.evaluate(node => getComputedStyle(node).textTransform), 'none');
            result.tokens = labels.length;
        } else {
            const candidates = area.locator('[data-m39-answer]');
            assert.equal(await candidates.count(), control.options.length);
            result.labels = (await candidates.allTextContents()).map(norm);
            assert.deepEqual(result.labels, answerFragments[control.id], 'Finite source answer fragments, not key/rationale payloads');
            for (const [index, candidate] of (await candidates.all()).entries()) {
                assertOptionOnly(result.labels[index], control.options[index].label, key);
                assert.equal(await candidate.evaluate(node => getComputedStyle(node).textTransform), 'none', 'No ALL CAPS normalization');
                assert.ok(await candidate.isVisible()); assert.equal(await candidate.getAttribute('data-m39-answer'), control.options[index].value);
                assert.equal(await candidate.evaluate(node => Math.max(0, node.scrollWidth - node.clientWidth)), 0, 'Candidate wraps rather than clips');
            }
        }
        assert.equal(await area.evaluate(node => Math.max(0, node.scrollWidth - node.clientWidth)), 0);
        controls.push(result);
    }
    assert.equal(await card.evaluate(node => Math.max(0, node.scrollWidth - node.clientWidth)), 0);
    return {source_index: task.source_index, interaction: task.interaction, controls,
        promptExact: true, authorKeyExact: true, initiallyHidden: initial, naturalCasing: true};
}
async function fillCorrect(card, task) {
    for (const control of task.controls) {
        const area = field(card, control);
        if (control.kind === 'manual') await area.locator('textarea').fill(control.answer);
        else for (const value of control.kind === 'multi' ? control.answer : [control.answer]) await option(area, control, value).click();
    }
}
async function resetCase(card, task) {
    await card.locator('[data-m39-reset]').click();
    const details = card.locator('[data-m39-ui-explanation]');
    await details.locator('[data-m39-self-check-answers]').waitFor({state: 'hidden'});
    await card.locator('[data-m39-case-feedback]').waitFor({state: 'hidden'});
    assert.equal(await details.evaluate(node => node.open), false);
    assert.equal(await details.locator('[data-m39-self-check-answers]').isVisible(), false);
    for (const control of task.controls) {
        const area = field(card, control);
        if (control.kind === 'manual') {
            assert.equal(await area.locator('textarea').inputValue(), '');
            assert.equal(await area.locator('[data-m39-token][disabled]').count(), 0);
        } else assert.equal(await area.locator('[aria-checked="true"]').count(), 0);
    }
    assert.equal(await card.locator('[data-m39-case-feedback]').isVisible(), false);
}
async function checkedState(card, correct) {
    await card.locator('[data-m39-case-feedback]').waitFor({state: 'visible'});
    assert.equal(norm(await card.locator('[data-m39-case-feedback]').textContent()), correct ? 'Правильно' : 'Не всі частини відповіді правильні');
    assert.equal(await card.locator('[data-m39-ui-explanation]').evaluate(node => node.open), true);
    assert.equal(await card.locator('[data-m39-self-check-answers]').isVisible(), true);
    for (const part of await card.locator('[data-m39-part-feedback]').all()) assert.ok(await part.isVisible());
}
async function tokenProof(card, task) {
    let count = 0;
    for (const control of task.controls.filter(control => control.kind === 'manual')) {
        const area = field(card, control), input = area.locator('textarea'), tokens = area.locator('[data-m39-token]');
        const first = tokens.first(); await first.click(); assert.equal(await first.isDisabled(), true);
        await input.press('ControlOrMeta+A'); await input.press('Backspace'); assert.equal(await first.isEnabled(), true);
        let remaining = control.answer;
        const used = new Set();
        while (remaining) {
            const labels = (await tokens.allTextContents()).map(norm);
            const index = labels.findIndex((label, i) => !used.has(i) && remaining.startsWith(label));
            assert.ok(index >= 0, 'Canonical answer constructible by tokens');
            await tokens.nth(index).click(); used.add(index); remaining = remaining.slice(labels[index].length).trimStart();
        }
        assert.equal(await input.inputValue(), control.answer);
        assert.equal(await tokens.locator('xpath=self::*[not(@disabled)]').count(), 0);
        count++;
    }
    return count;
}
async function keyboardProof(card, task) {
    const controls = [];
    for (const control of task.controls.filter(c => c.kind !== 'manual')) {
        const area = field(card, control);
        if (control.kind === 'multi') {
            const checkbox = option(area, control, control.answer[0]); await checkbox.focus(); await checkbox.press('Space');
            assert.equal(await checkbox.getAttribute('aria-checked'), 'false'); await checkbox.press('Space');
            assert.equal(await checkbox.getAttribute('aria-checked'), 'true'); controls.push({id: control.id, spaceToggle: true});
        } else {
            const selected = option(area, control, control.answer), index = control.options.findIndex(o => o.value === control.answer);
            await selected.focus(); await selected.press('ArrowRight');
            const next = option(area, control, control.options[(index + 1) % control.options.length].value);
            assert.equal(await next.getAttribute('aria-checked'), 'true'); await next.press('ArrowLeft');
            assert.equal(await selected.getAttribute('aria-checked'), 'true');
            assert.equal(await selected.evaluate(node => node.matches(':focus-visible')), true);
            controls.push({id: control.id, arrowCycle: true, focusVisible: true});
        }
    }
    const manual = task.controls.find(control => control.kind === 'manual');
    if (manual) {
        const input = field(card, manual).locator('textarea'); await input.focus(); await input.press('Control+Enter');
        assert.equal(await input.evaluate(node => node.matches(':focus-visible')), true);
        return {kind: 'Ctrl+Enter', focusVisible: true, controls};
    }
    const control = task.controls.find(c => c.kind !== 'multi');
    const selected = option(field(card, control), control, control.answer);
    await selected.focus(); await selected.press('Space');
    assert.equal(await selected.getAttribute('aria-checked'), 'true');
    const check = card.locator('[data-m39-check]'); await check.focus(); await check.press('Enter');
    assert.equal(await check.evaluate(node => node.matches(':focus-visible')), true);
    return {kind: 'Space/Enter', focusVisible: true, controls};
}
async function acceptanceCase(page, target, task, dir, stem) {
    const card = await caseLocator(page, task);
    const row = await verifyCase(page, target, task, true);
    await fillCorrect(card, task); row.keyboard = await keyboardProof(card, task); await checkedState(card, true);
    row.correct = true;
    row.postCorrect = await shot(card, dir, stem + '-post-correct.png');
    await resetCase(card, task);
    row.tokenManual = await tokenProof(card, task);
    if (row.tokenManual) { // Token-only construction must still answer every non-manual subpart.
        for (const control of task.controls.filter(c => c.kind !== 'manual'))
            for (const value of control.kind === 'multi' ? control.answer : [control.answer]) await option(field(card, control), control, value).click();
        await card.locator('[data-m39-check]').click(); await checkedState(card, true); await resetCase(card, task);
    }
    await fillCorrect(card, task);
    const first = task.controls[0], area = field(card, first);
    if (first.kind === 'manual') await area.locator('textarea').fill('wrong answer');
    else {
        const wrong = first.options.find(o => first.kind === 'multi' ? !first.answer.includes(o.value) : o.value !== first.answer);
        assert.ok(wrong); await option(area, first, wrong.value).click();
    }
    await card.locator('[data-m39-check]').click(); await checkedState(card, false); row.wrong = true;
    row.postWrong = await shot(card, dir, stem + '-post-wrong.png');
    await resetCase(card, task); row.reset = true;
    const inversion = task.controls.find(control => control.id === 'c2-1-answer');
    if (inversion) {
        for (const invalid of [
            "Hadn't the guide brought a spare lamp, we could have been stranded underground.",
            'Had not the guide brought a spare lamp, we could have been stranded underground.',
        ]) {
            await field(card, inversion).locator('textarea').fill(invalid);
            await card.locator('[data-m39-check]').click(); await checkedState(card, false); await resetCase(card, task);
        }
        await field(card, inversion).locator('textarea').fill("Had the guide not brought a spare lamp, we could've been stranded underground.");
        await card.locator('[data-m39-check]').click(); await checkedState(card, true); await resetCase(card, task);
        row.explicitInversion = {hadntRejected: true,hadNotBeforeSubjectRejected: true,couldveAccepted: true};
    }
    for (const control of task.controls.filter(c => c.kind === 'manual')) {
        for (const alias of control.accepted || [control.answer]) {
            await fillCorrect(card, task); await field(card, control).locator('textarea').fill(alias.replace(/[.!?]+$/u, ''));
            await card.locator('[data-m39-check]').click(); await checkedState(card, true); await resetCase(card, task);
        }
    }
    row.acceptedVariants = true; row.terminalPunctuationOptional = true; return row;
}
async function measure(page) {
    const data = await page.locator('[data-theory-main]').evaluate(node => ({main: Math.max(0, node.scrollWidth - node.clientWidth),
        document: Math.max(0, document.documentElement.scrollWidth - innerWidth)}));
    assert.equal(data.main, 0); return data;
}
async function supplemental(dir, label, beforeLabel) {
    assert.equal(path.basename(dir), 'seo-m39-local'); assert.match(label, /^[a-z0-9-]+$/u);
    const before = JSON.parse(fs.readFileSync(path.join(dir, beforeLabel + '-visual.json'), 'utf8'));
    assert.equal(before.pass, true); assert.deepEqual(before.sourceHashes, sourceHashes());
    const output = path.join(dir, label + '-supplemental.json'); assert.ok(!fs.existsSync(output));
    const {chromium} = require('playwright'), legacy = require('./seo-m39-local.cjs');
    const report = {at: new Date().toISOString(), base: BASE, missingParts: [], noJS: [], regressions: [], violations: [], pass: false};
    const browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
    try {
        for (const target of targets.filter(t => t.data.cases.some(task => task.controls.length > 1))) {
            const context = await browser.newContext({viewport: {width: 1440, height: 1000}, colorScheme: 'light'}), page = await context.newPage();
            const row = {path: target.path, errors: [], httpErrors: [], failed: [], cases: []}; report.missingParts.push(row);
            try {
                await pageReady(page, target, row, report.violations, {width: 1440, height: 1000});
                for (const task of target.data.cases.filter(task => task.controls.length > 1)) {
                    const card = await caseLocator(page, task), absent = task.controls.at(-1);
                    for (const control of task.controls.slice(0, -1)) {
                        const area = field(card, control);
                        if (control.kind === 'manual') await area.locator('textarea').fill(control.answer);
                        else for (const value of control.kind === 'multi' ? control.answer : [control.answer]) await option(area, control, value).click();
                    }
                    await card.locator('[data-m39-check]').click(); await checkedState(card, false);
                    assert.equal(norm(await page.locator('[data-m39-ui-score]').textContent()), 'Результат: 0 / 6');
                    const area = field(card, absent);
                    if (absent.kind === 'manual') await area.locator('textarea').fill(absent.answer);
                    else for (const value of absent.kind === 'multi' ? absent.answer : [absent.answer]) await option(area, absent, value).click();
                    await card.locator('[data-m39-check]').click(); await checkedState(card, true);
                    assert.equal(norm(await page.locator('[data-m39-ui-score]').textContent()), 'Результат: 1 / 6');
                    row.cases.push({source_index: task.source_index, missing: absent.id, rejectedIncomplete: true, restoredComplete: true});
                    await resetCase(card, task);
                }
                clean(row); row.pass = true;
            } finally {await context.close();}
        }
        for (const target of targets) {
            const context = await browser.newContext({javaScriptEnabled: false, viewport: {width: 390, height: 844}}), page = await context.newPage();
            const row = {path: target.path, javaScriptEnabled: false, errors: [], httpErrors: [], failed: [], expectedDisabledScripts: [], keys: []}; report.noJS.push(row);
            try {
                await observe(page, row, report.violations); const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle'}); assert.equal(response.status(), 200);
                assert.equal(await page.locator('[data-m39-ui-case]').count(), 6);
                for (const task of target.data.cases) {
                    const card = await caseLocator(page, task), key = card.locator('[data-m39-self-check-answer]'), details = card.locator('[data-m39-ui-explanation]');
                    assert.equal(htmlText(await card.locator('[data-m39-author-prompt]').innerHTML()), htmlText(target.data.author_self_check.prompts[task.source_index - 1]));
                    assert.equal(htmlText(await key.innerHTML()), htmlText(target.data.author_self_check.answers[task.source_index - 1]));
                    const summary = details.locator(':scope > summary'); assert.equal(await key.isVisible(), false);
                    await summary.click(); assert.equal(await key.isVisible(), true); await summary.focus(); await summary.press('Space');
                    assert.equal(await details.evaluate(node => node.open), false); await summary.press('Enter');
                    assert.equal(await key.isVisible(), true); await summary.click();
                    row.keys.push({source_index: task.source_index, originalPromptKey: true, nativeMouseSpaceEnter: true});
                }
                row.overflow = await measure(page); clean(row); row.pass = true;
            } finally {await context.close();}
        }
        for (const target of legacy.regressions.filter(t => t.group === 'M38')) {
            const context = await browser.newContext({viewport: {width: 1440, height: 1000}, colorScheme: 'light'}), page = await context.newPage();
            const row = {path: target.path, errors: [], httpErrors: [], failed: []}; report.regressions.push(row);
            try {
                await observe(page, row, report.violations); await page.goto(BASE + target.path, {waitUntil: 'networkidle'}); await page.waitForFunction(() => Boolean(window.Alpine));
                await legacy.content(page, target); row.practice = await legacy.practice(page, target); await legacy.bankProof(dir, target, row.practice);
                row.tokens = await legacy.tokenAndManualProof(page, target); row.overflow = await legacy.overflow(page);
                clean(row); row.pass = true;
            } finally {await context.close();}
        }
        const {capture, compare} = require('./capture-m39-http.cjs');
        const http = await capture(); compare(JSON.parse(fs.readFileSync(path.join(dir, 'before-http.json'), 'utf8')), http);
        fs.writeFileSync(path.join(dir, 'ui-quality-final-http.json'), JSON.stringify(http, null, 2), {flag: 'wx'});
        assert.deepEqual(report.violations, []); assert.equal(report.missingParts.flatMap(row => row.cases).length, 4);
        report.http = {rows: http.rows.length, sitemap: http.sitemap}; report.pass = true;
    } finally {await browser.close(); fs.writeFileSync(output, JSON.stringify(report, null, 2), {flag: 'wx'});}
    console.log(JSON.stringify({pass: report.pass, missingCompoundCases: 4, noJS: report.noJS.length, regressions: report.regressions.length, http: report.http}));
}
async function run(dir, label, mode, beforeLabel) {
    if (mode === '--supplemental') return supplemental(dir, label, beforeLabel);
    assert.equal(path.basename(dir), 'seo-m39-local'); assert.match(label, /^[a-z0-9-]+$/u);
    assert.ok(['--visual-before-acceptance', '--acceptance'].includes(mode));
    const initial = mode === '--visual-before-acceptance';
    const hashes = sourceHashes();
    if (!initial) {
        assert.match(beforeLabel || '', /^[a-z0-9-]+$/u);
        const before = JSON.parse(fs.readFileSync(path.join(dir, beforeLabel + '-visual.json'), 'utf8'));
        assert.equal(before.pass, true); assert.equal(before.caseStates, 36); assert.deepEqual(before.sourceHashes, hashes);
    }
    const output = path.join(dir, label + (initial ? '-visual.json' : '-acceptance.json'));
    assert.ok(!fs.existsSync(output));
    const {chromium} = require('playwright');
    const report = {at: new Date().toISOString(), base: BASE, mode, sourceHashes: hashes, rows: [], violations: [], pass: false};
    let browser;
    try {
        browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
        for (const viewport of [{width: 1440, height: 1000}, {width: 390, height: 844}]) for (const target of targets) {
            const context = await browser.newContext({viewport, colorScheme: 'light'}), page = await context.newPage();
            const row = {path: target.path, errors: [], httpErrors: [], failed: [], cases: []}; report.rows.push(row);
            try {
                await pageReady(page, target, row, report.violations, viewport);
                for (const task of target.data.cases) {
                    const stem = label + '-' + target.slug + '-' + viewport.width + '-case-' + task.source_index;
                    if (initial) {
                        const result = await verifyCase(page, target, task, true), card = await caseLocator(page, task);
                        result.pre = await shot(card, dir, stem + '-pre.png'); row.cases.push(result);
                    } else row.cases.push(await acceptanceCase(page, target, task, dir, stem));
                }
                if (!initial) {
                    for (const task of target.data.cases) {
                        const card = await caseLocator(page, task); await fillCorrect(card, task); await card.locator('[data-m39-check]').click();
                    }
                    assert.equal(norm(await page.locator('[data-m39-ui-score]').textContent()), 'Результат: 6 / 6');
                    for (const task of target.data.cases) await resetCase(await caseLocator(page, task), task);
                    assert.equal(norm(await page.locator('[data-m39-ui-score]').textContent()), 'Результат: 0 / 6');
                    row.scoreCorrectReset = true;
                }
                row.overflow = await measure(page); clean(row); row.pass = true;
                console.log(JSON.stringify({path: target.path, width: viewport.width, mode, cases: row.cases.length, pass: true}));
            } finally {await context.close();}
        }
        assert.deepEqual(report.violations, []); report.caseStates = report.rows.reduce((n, row) => n + row.cases.length, 0);
        assert.equal(report.caseStates, 36); report.pass = true;
    } finally {
        if (browser) await browser.close();
        fs.writeFileSync(output, JSON.stringify(report, null, 2), {flag: 'wx'});
    }
    console.log(JSON.stringify({pass: report.pass, mode, pages: report.rows.length, caseStates: report.caseStates}));
}
if (require.main === module) run(...process.argv.slice(2)).catch(error => {console.error(error.stack || error); process.exitCode = 1;});
module.exports = {targets, uiData, assertOptionOnly, sourceHashes, verifyCase, run,
    acceptanceCase, pageReady, resetCase, fillCorrect, checkedState, measure};
