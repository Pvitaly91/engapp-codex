'use strict';
// Finite real-.loc acceptance. Guest contexts, local GETs only; no DB/bootstrap,
// response-body exports, cookies, tokens or authentication state in evidence.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const ROOT = path.resolve(__dirname, '../..');
const BASE = 'http://gramlyze.loc';
const OUTPUT = path.resolve(ROOT, '../ppc-quality-local');
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const read = file => JSON.parse(fs.readFileSync(path.join(ROOT, file), 'utf8'));
const key = value => norm(value).toLowerCase().replace(/[’‘ʼ`´]/gu, "'").replace(/[.,!?;:]+/gu, '');
const copy = {
    uk: {check: 'Перевірити', reset: 'Спробувати ще раз', dynamicCheck: 'Перевірити відповідь'},
    en: {check: 'Check', reset: 'Try again', dynamicCheck: 'Check answer'},
    pl: {check: 'Sprawdź', reset: 'Spróbuj ponownie', dynamicCheck: 'Sprawdź odpowiedź'},
};
const frozen = {
    'docs/content/m26-past-perfect-continuous-detail-master.v1.json': '2aeb5d65b07f05dc540e8d35fb6779a8775ac8f364a1b906d2d93a4cdac891d0',
    'database/content-patches/m26-ppc-before.json': 'd4328511e38d9ff10edfb98158e5fd18ebef04aefa448d2a1bacf0c93d9b6aa4',
    'database/content-patches/m26-ppc-point-details.v1.json': '210dec0e198857c4f67b085ebb38b505b0d5b9d729f3978fbfeb278ede5ba28e',
    'database/content-patches/m26-ppc-interactive-practice.v1.json': '8b105df87c0f81172c3315927a9b5d6a35e2f9dc1475d611d8e5ee4623055466',
    'database/content-patches/ppc-practice-quality.v2.json': '99f99e139401a9ef757af3f652eecc144caedb6f7378f3406c8dd6cac53271dc',
};
function text(html) {
    const dom = new JSDOM(String(html || ''));
    const value = norm(dom.window.document.body.textContent);
    dom.window.close(); return value;
}
function leaves(value) {
    if (typeof value === 'string') return [text(value)].filter(Boolean);
    if (Array.isArray(value)) return value.flatMap(leaves);
    if (value && typeof value === 'object') return Object.entries(value)
        .filter(([name]) => !['color', 'url', 'progressive_v2', 'detail_native_sha256'].includes(name))
        .flatMap(([, item]) => leaves(item));
    return [];
}
function publicUrl(value) {
    try {const url = new URL(value); return url.origin + url.pathname;} catch {return '(unknown source)';}
}
function sourceData() {
    for (const [file, expected] of Object.entries(frozen)) assert.equal(sha(fs.readFileSync(path.join(ROOT, file))), expected, 'Immutable source: ' + file);
    const master = read('docs/content/m26-past-perfect-continuous-detail-master.v1.json');
    const map = read('database/content-patches/m26-ppc-point-details.v1.json').blocks;
    assert.equal(Object.values(map).reduce((n, rows) => n + rows.length, 0), 56, 'Full frozen M26 56-point map');
    const authored = new Map(master.targets.flatMap(target => target.blocks.filter(block => block.progressive_v1)
        .map(block => [block.progressive_v1.key, block])));
    const quality = read('database/content-patches/ppc-practice-quality.v2.json');
    assert.equal(quality.targets.length, 4);
    const sourceHashes = {...frozen};
    const targets = quality.targets.flatMap(target => {
        const owner = master.targets.find(item => item.identity === target.identity);
        assert.ok(owner && owner.owner_kind === 'page');
        const definition = read(target.definition_path);
        sourceHashes[target.definition_path] = sha(fs.readFileSync(path.join(ROOT, target.definition_path)));
        const expectedPoints = owner.blocks.filter(block => block.progressive_v1).flatMap(block => {
            const blockKey = block.progressive_v1.key;
            return map[blockKey].map((point, index) => {
                const values = point.fragments.map(ref => {
                    let value = authored.get(ref.source).native_data[ref.field];
                    if (Object.hasOwn(ref, 'index')) value = value[ref.index];
                    return value;
                });
                if (point.supplement) values.push(point.supplement);
                return {key: blockKey + '-point-' + (index + 1), index, units: values.map(leaves)};
            });
        });
        const bankClass = target.body_data.uk.linked_practice.seeder_classes[0].replace(/\\\\/gu, '\\');
        const bankPath = 'database/seeders/' + bankClass.replace(/^Database\\Seeders\\/u, '').replace(/\\/gu, '/') + '/definition.json';
        const bank = read(bankPath); assert.equal(bank.questions.length, 72);
        assert.equal(bank.seeder.class, bankClass);
        sourceHashes[bankPath] = sha(fs.readFileSync(path.join(ROOT, bankPath)));
        return ['uk', 'en', 'pl'].map(locale => {
            const localizedPath = path.posix.dirname(target.definition_path) + '/localizations/' + locale + '.json';
            const localization = locale === 'uk' ? null : read(localizedPath);
            if (localization) sourceHashes[localizedPath] = sha(fs.readFileSync(path.join(ROOT, localizedPath)));
            const basics = definition.page.blocks.filter(block => block.type !== 'practice-set').map((block, index) => {
                const translation = localization?.blocks.find(item => item.index === index + 1);
                return {type: block.type, data: JSON.parse(translation?.body || block.body)};
            });
            return {...target, locale, path: (locale === 'uk' ? '' : '/' + locale) + owner.expected_theory_path,
                data: target.body_data[locale], basics, bank: bank.questions, expectedPoints: locale === 'uk' ? expectedPoints : []};
        });
    });
    assert.equal(targets.length, 12);
    assert.equal(targets.filter(target => target.locale === 'uk').reduce((n, target) => n + target.expectedPoints.length, 0), 48);
    return {targets, sourceHashes};
}
async function guard(page, row, report) {
    row.pageErrors = []; row.consoleErrors = []; row.httpErrors = []; row.localFailures = []; row.blockedFonts = 0;
    row.localRequestCount = 0;
    await page.route('**/*', async route => {
        const request = route.request(), url = new URL(request.url());
        if (/^https:\/\/fonts\.(?:googleapis|gstatic)\.com$/u.test(url.origin)) {row.blockedFonts++; await route.abort(); return;}
        if (url.origin !== BASE || request.method() !== 'GET') {
            report.violations.push({method: request.method(), url: publicUrl(request.url())}); await route.abort(); return;
        }
        row.localRequestCount++; await route.continue();
    });
    page.on('pageerror', error => row.pageErrors.push(sha(String(error.message))));
    page.on('console', message => {
        if (message.type() === 'error' && !/^https:\/\/fonts\./u.test(message.location().url)) row.consoleErrors.push({source: publicUrl(message.location().url), messageSha256: sha(message.text())});
    });
    page.on('response', response => {if (new URL(response.url()).origin === BASE && response.status() >= 400) row.httpErrors.push({url: publicUrl(response.url()), status: response.status()});});
    page.on('requestfailed', request => {if (new URL(request.url()).origin === BASE) row.localFailures.push({url: publicUrl(request.url()), reason: request.failure()?.errorText || 'unknown'});});
}
async function theory(page, target, row) {
    const main = page.locator('[data-theory-main]'); assert.equal(await main.count(), 1);
    const bootstrap = await page.locator('script').evaluateAll(nodes => ({
        rules: nodes.filter(node => node.textContent.startsWith('window.GRAMLYZE_CONTRACTION_RULES')).map(node => Boolean(node.closest('[data-theory-main]'))),
        matcher: nodes.filter(node => /\/js\/english-answer-variants\.js(?:\?|$)/u.test(node.src)).map(node => Boolean(node.closest('[data-theory-main]'))),
    }));
    assert.deepEqual(bootstrap, {rules: [false], matcher: [false]}, 'Exactly one page-global matcher bootstrap, outside native cards, for every interface locale');
    assert.ok(await page.evaluate(() => typeof window.EnglishAnswerVariants?.matches === 'function'), 'Actual matching engine bootstrapped');
    const basicText = await main.evaluate(node => {
        const copy = node.cloneNode(true);
        copy.querySelectorAll('[data-theory-native-extension],noscript,script,style,[data-theory-ui],[x-data^="theoryPracticeSet("]').forEach(item => item.remove());
        return copy.textContent;
    });
    for (const block of target.basics) for (const leaf of leaves(block.data)) assert.ok(norm(basicText).includes(leaf), 'Complete visible basic: ' + leaf);
    const details = page.locator('[data-theory-native-extension][data-theory-point-index] > details');
    assert.equal(await details.count(), target.expectedPoints.length, 'Finite M26 disclosure count');
    assert.ok(await details.evaluateAll(nodes => nodes.every(node => !node.open)), 'Initially closed native details');
    const summaryIds = await details.locator(':scope > summary').evaluateAll(nodes => nodes.map(node => node.id));
    assert.equal(new Set(summaryIds).size, summaryIds.length, 'Unique disclosure anchors');
    const requestsBeforeDisclosures = row.localRequestCount;
    for (const point of target.expectedPoints) {
        const disclosure = page.locator('[data-theory-section="' + point.key + '"] > details');
        assert.equal(await disclosure.count(), 1);
        const units = await disclosure.locator('.theory-section-detail-body > section').allTextContents();
        assert.equal(units.length, point.units.length, 'Exact fragment/supplement unit count');
        for (const [i, expected] of point.units.entries()) for (const leaf of expected) assert.ok(norm(units[i]).includes(leaf), 'Frozen detail author fragment');
        await disclosure.locator(':scope > summary').click(); assert.ok(await disclosure.evaluate(node => node.open));
        await disclosure.locator(':scope > summary').click(); assert.equal(await disclosure.evaluate(node => node.open), false);
    }
    if (target.expectedPoints.length) {
        const first = details.first(), summary = first.locator(':scope > summary');
        await summary.focus(); await page.keyboard.press('Enter'); assert.ok(await first.evaluate(node => node.open));
        await page.keyboard.press('Space'); await page.waitForFunction(id => !document.getElementById(id).closest('details').open, summaryIds[0]);
        const state = await details.evaluateAll(nodes => nodes.map(node => node.open));
        await page.emulateMedia({media: 'print'}); await page.waitForFunction(() => [...document.querySelectorAll('[data-theory-details]')].every(node => node.open));
        await page.emulateMedia({media: 'screen'}); await page.waitForFunction(saved => JSON.stringify([...document.querySelectorAll('[data-theory-details]')].map(node => node.open)) === JSON.stringify(saved), state);
    }
    assert.equal(row.localRequestCount, requestsBeforeDisclosures, 'Native disclosure and print interactions do not fetch content');
    return {basicLeaves: target.basics.reduce((n, block) => n + leaves(block.data).length, 0), basicTextSha256: sha(norm(basicText)), disclosures: target.expectedPoints.length, authorFragments: true, noDisclosureFetch: true, keyboardAndPrint: target.expectedPoints.length > 0, oneGlobalMatcherBootstrap: true};
}
async function staticPractice(page, target) {
    const root = page.locator('[x-data^="theoryPracticeSet("]'); assert.equal(await root.count(), 1);
    assert.deepEqual(await root.evaluate(node => {const state = window.Alpine.$data(node); return {selects: state.selects, choices: state.choices, inputs: state.inputs};}), {selects: target.data.selects, choices: target.data.choices, inputs: target.data.inputs});
    const groups = root.locator('.theory-exercise'); assert.equal(await groups.count(), 3);
    const result = {groups: [], inputTokens: [], acceptedAlternatives: 0, semanticRejections: 0};
    for (const [g, name] of ['selects', 'choices', 'inputs'].entries()) {
        const group = groups.nth(g), check = group.getByRole('button', {name: copy[target.locale].check, exact: true});
        const reset = group.getByRole('button', {name: copy[target.locale].reset, exact: true});
        const score = group.locator('p[x-text="scoreText(\'' + name + '\')"]');
        for (const [i, item] of target.data[name].entries()) {
            if (name !== 'inputs') {
                const row = group.locator('label').nth(i).locator('..');
                assert.equal(text(await row.locator('label').innerHTML()), text(item.label));
                await row.getByRole('button', {name: item.answer, exact: true}).click();
            } else {
                const input = group.locator('input').nth(i), row = input.locator('xpath=../..');
                assert.ok(norm(await row.innerText()).includes(text(item.prompt)), 'Visible localized static condition');
                const bank = row.locator('button').filter({has: page.locator('span[x-text="token.value"]')});
                const tokens = item.before.split('/').map(norm);
                assert.equal(await bank.count(), tokens.length);
                await bank.first().click(); assert.ok(await bank.first().isDisabled());
                await input.press('ControlOrMeta+A'); await input.press('Backspace'); assert.ok(await bank.first().isEnabled(), 'Removed static token reusable');
                let pending = key(item.answer).split(' '), unused = [...tokens];
                while (pending.length) {
                    const index = unused.findIndex(token => token && key(token).split(' ').every((word, j) => pending[j] === word));
                    assert.ok(index >= 0, 'Complete answer constructible from bank');
                    await bank.nth(index).click(); pending = pending.slice(key(unused[index]).split(' ').length); unused[index] = '';
                }
                assert.equal(key(await input.inputValue()), key(item.answer), 'Clicked complete static answer before manual edits');
                result.inputTokens.push({index: i, fullBankTarget: true, removalReuse: true});
            }
        }
        await check.click(); await score.waitFor({state: 'visible'}); assert.match(norm(await score.textContent()), /2\s*(?:\/|з|із|of|z)\s*2/u);
        if (name === 'inputs') {
            for (const [i, item] of target.data.inputs.entries()) {
                for (const answer of item.accepted || [item.answer]) {
                    await group.locator('input').nth(i).fill(answer.replace(/[.!?]+$/u, '')); await check.click();
                    assert.ok(await group.locator('input').nth(i).evaluate(node => node.classList.contains('border-emerald-400'))); result.acceptedAlternatives++;
                }
                const variants = await page.evaluate(answer => window.EnglishAnswerVariants.variants(answer), item.answer);
                const contraction = variants.find(answer => /['’]d\b|hadn['’]t/iu.test(answer));
                if (contraction) {await group.locator('input').nth(i).fill(contraction); await check.click(); assert.ok(await group.locator('input').nth(i).evaluate(node => node.classList.contains('border-emerald-400'))); result.acceptedAlternatives++;}
                const invalid = [];
                if (/\bbeen\b/iu.test(item.answer)) invalid.push(item.answer.replace(/\bbeen\s*/iu, ''));
                if (/\bnot\b/iu.test(item.answer)) invalid.push(item.answer.replace(/\bnot\s*/iu, ''));
                if (/\bAnna had known Lev\b/u.test(item.answer)) invalid.push(item.answer.replace('Anna had known Lev', 'Lev had known Anna'), item.answer.replace('had known', 'had been knowing'));
                for (const answer of invalid) {await group.locator('input').nth(i).fill(answer); await check.click(); assert.ok(await group.locator('input').nth(i).evaluate(node => node.classList.contains('border-rose-400')), 'Wrong polarity/state/role or missing been rejected'); result.semanticRejections++;}
                await group.locator('input').nth(i).fill(item.answer);
            }
        }
        if (name === 'inputs') await group.locator('input').first().fill('wrong answer');
        else {const item = target.data[name][0], options = name === 'selects' ? (item.options || target.data.options) : target.data.choice_options; await group.locator('label').first().locator('..').getByRole('button', {name: options.find(option => option !== item.answer), exact: true}).click();}
        await check.click(); assert.match(norm(await score.textContent()), /1\s*(?:\/|з|із|of|z)\s*2/u);
        await reset.click(); await score.waitFor({state: 'hidden'});
        if (name === 'inputs') {for (const input of await group.locator('input').all()) assert.equal(await input.inputValue(), ''); assert.equal(await group.locator('button[disabled]').count(), 0);}
        result.groups.push({name, tasks: 2, correct: '2/2', wrong: '1/2', reset: true});
    }
    return result;
}
function canBuild(values, available) {
    const bag = new Map(); for (const value of available) bag.set(key(value), (bag.get(key(value)) || 0) + 1);
    for (const value of values) {const k = key(value), count = bag.get(k) || 0; if (!count) return false; bag.set(k, count - 1);} return true;
}
function evidenceNames(targets, label) {
    return [label + '-theory-browser.json', ...targets.flatMap(target => [
        label + '-' + target.slug + '-' + target.locale + '-practice.png',
        label + '-' + target.slug + '-' + target.locale + '-linked.png',
    ])];
}
async function linkedPractice(page, target) {
    const widget = page.locator('[data-sentence-builder]'); assert.equal(await widget.count(), 1);
    // Both selected tokens and bank tokens use button[x-text="token.value"].
    // Scope by their authored x-for containers so removal/reuse never clicks a
    // selected token when it intends to take a second occurrence from the bank.
    const bankButtons = () => widget.locator('template[x-for="token in tokenBank"]').locator('..').locator('button[x-text="token.value"]');
    const selectedButtons = () => widget.locator('template[x-for="token in selectedTokens"]').locator('..').locator('button[x-text="token.value"]');
    const pool = await widget.evaluate(node => window.Alpine.$data(node).questions);
    assert.equal(pool.length, 5, 'The actual linked service returns exactly its configured five-question pool');
    assert.equal(new Set(pool.map(question => question.id)).size, pool.length);
    const bank = new Map(target.bank.map(question => [key(question.target_text), question]));
    const expectedPoolEditorialUuids = [...target.bank].sort((a, b) => a.uuid < b.uuid ? -1 : a.uuid > b.uuid ? 1 : 0).slice(0, 5).map(question => question.uuid);
    const actualPoolEditorialUuids = pool.map(question => {
        const source = bank.get(key(question.correct_tokens.join(' ')));
        assert.ok(source, 'Only target-bank full English target'); return source.uuid;
    }).sort();
    assert.deepEqual(actualPoolEditorialUuids, expectedPoolEditorialUuids, 'Exact first-five linked UUID subset; no incomplete or unrelated pool');
    const generatedTargets = await widget.evaluate(node => {
        const state = window.Alpine.$data(node), original = state.currentQuestion;
        try {return state.questions.map(question => {state.currentQuestion = question; return state.composeCorrectText();});}
        finally {state.currentQuestion = original;}
    });
    for (const [index, question] of pool.entries()) {
        const source = bank.get(key(question.correct_tokens.join(' '))); assert.ok(source, 'Only target-bank full English target');
        assert.equal(key(generatedTargets[index]), key(source.target_text), 'Displayed correct answer retains the full authored English target');
        assert.equal(generatedTargets[index].slice(-1), source.target_text.slice(-1), 'Localized condition cannot turn an English question into a statement');
        if (/^(?:Yes|No),/u.test(source.target_text)) assert.match(generatedTargets[index], /^(?:Yes|No), /u, 'Natural short-answer comma is preserved');
        assert.equal(question.type, '4'); assert.equal(question.authored_compose, true);
        assert.equal(question.level, source.level);
        assert.equal(question.question, source.localizations[target.locale].source_text, 'App-locale compose condition from persisted source');
        assert.ok(Array.isArray(question.hints)); assert.ok(question.hints.every(hint => hint.provider !== 'compose_prompt'));
        assert.deepEqual(question.hints.map(hint => hint.hint), source.localizations[target.locale].hints, 'App-locale explanations only');
        assert.equal(question.compose_preanswer_hint, source.localizations[target.locale].hints[0], 'Finite pre-answer lexical hint is localized');
    }
    async function load(index) {
        await widget.evaluate((node, i) => {const state = window.Alpine.$data(node); state.currentIndex = i; state.loadQuestion();}, index);
        await page.waitForFunction(i => {
            const state = window.Alpine.$data(document.querySelector('[data-sentence-builder]'));
            return state.currentIndex === i && state.currentQuestion === state.questions[i] && state.selectedTokens.length === 0 && !state.answered;
        }, index);
        const prompt = widget.locator('p[x-html="getDisplayText()"]');
        await prompt.waitFor({state: 'visible'});
        assert.equal(norm(await prompt.innerText()), norm(pool[index].question), 'Condition actually visible');
        const lexicalHint = widget.locator('[data-authored-compose-hint] span[x-text="currentComposeHint"]');
        await lexicalHint.waitFor({state: 'visible'});
        assert.equal(norm(await lexicalHint.innerText()), norm(pool[index].compose_preanswer_hint), 'Lexical lemma visible before first attempt');
        return widget.evaluate(node => {const state = window.Alpine.$data(node); return {target: state.correctAnswer, bank: state.tokenBank.map(token => token.value), explanation: state.currentExplanation};});
    }
    async function build(values) {
        for (const value of values) {
            const buttons = bankButtons();
            const options = await buttons.allTextContents(), index = options.findIndex(option => key(option) === key(value));
            assert.ok(index >= 0, 'Requested target/contraction token actually present in bank'); await buttons.nth(index).click();
        }
    }
    async function check(expected) {
        await widget.getByRole('button', {name: copy[target.locale].dynamicCheck, exact: true}).click();
        const state = await widget.evaluate(node => {const s = window.Alpine.$data(node); return {answered: s.answered, correct: s.isCorrect};});
        assert.deepEqual(state, {answered: true, correct: expected});
        const feedback = widget.locator('div[x-show="answered"]');
        await feedback.waitFor({state: 'visible'});
        assert.ok(await feedback.isVisible(), 'Visible actual feedback');
    }
    const processIndex = pool.findIndex(question => question.correct_tokens.some(token => key(token) === 'been'));
    assert.ok(processIndex >= 0);
    let active = await load(processIndex);
    const first = bankButtons().first(), firstValue = await first.textContent();
    await first.click();
    await selectedButtons().first().click();
    assert.ok((await bankButtons().allTextContents()).some(value => key(value) === key(firstValue)), 'Removed linked token returned to bank');
    await build(pool[processIndex].correct_tokens);
    const submitted = await widget.evaluate(node => window.Alpine.$data(node).composeSelectedText()); assert.equal(key(submitted), key(active.target));
    await check(true);
    active = await load(processIndex); await build(pool[processIndex].correct_tokens.filter(token => key(token) !== 'been')); await check(false);
    let notRejected = false;
    const notIndex = pool.findIndex(question => question.correct_tokens.some(token => key(token) === 'not'));
    if (notIndex >= 0) {await load(notIndex); await build(pool[notIndex].correct_tokens.filter(token => key(token) !== 'not')); await check(false); notRejected = true;}
    let contractionAccepted = false, contractionEligibleTargets = 0;
    for (let index = 0; index < pool.length; index++) {
        active = await load(index);
        const variants = await page.evaluate(answer => window.EnglishAnswerVariants.variants(answer), active.target);
        const candidate = variants.filter(answer => /['’]d\b|hadn['’]t/iu.test(answer)).find(answer => canBuild(answer.replace(/[.!?]+$/u, '').split(/\s+/u), active.bank));
        if (candidate) {
            contractionEligibleTargets++;
            if (!contractionAccepted) {await build(candidate.replace(/[.!?]+$/u, '').split(/\s+/u)); await check(true); contractionAccepted = true;}
        }
    }
    assert.equal(contractionAccepted, contractionEligibleTargets > 0, 'Every offered contraction coverage is exercised without inventing tokens');
    if (target.slug.endsWith('-negatives')) assert.ok(contractionAccepted && notRejected, 'Negatives actual pool covers contraction acceptance and omitted-not rejection in every locale');
    await load(processIndex);
    return {poolCount: pool.length, sourceBankCount: target.bank.length, expectedPoolEditorialUuids, allFiveActualPoolPromptsAndTargetsAudited: true, allFivePreAnswerLexicalHintsVisible: true, locale: target.locale, fullTargetFromBank: true, removalReuse: true, omittedBeenRejected: true, omittedNotRejected: notRejected, omittedNotNotApplicableToCurrentPool: notIndex < 0, contractionEligibleTargets, contractionFromActualBankAccepted: contractionAccepted, contractionNotOfferedByCurrentPool: contractionEligibleTargets === 0};
}
async function run(dir, label, ready) {
    assert.equal(ready, '--local-applied', 'Explicit post-apply acceptance flag required');
    assert.equal(path.resolve(dir), OUTPUT, 'Private evidence stays in the finite local diagnostics directory');
    assert.match(label, /^[a-z0-9-]+$/u);
    const source = sourceData(), report = {at: new Date().toISOString(), base: BASE, guest: true, getOnly: true, sourceHashes: source.sourceHashes, frozenM26MapPoints: 56, rows: [], violations: [], pass: false};
    for (const filename of evidenceNames(source.targets, label)) assert.equal(fs.existsSync(path.join(dir, filename)), false, 'Evidence label must be fresh; never overwrite a report or screenshot');
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    assert.ok(process.env.CHROMIUM_EXECUTABLE, 'Use the supplied installed Chrome executable');
    fs.mkdirSync(dir, {recursive: true}); let browser;
    try {
        browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
        for (const target of source.targets) {
            const context = await browser.newContext({viewport: {width: 1440, height: 1000}, colorScheme: 'light'});
            const page = await context.newPage(), row = {path: target.path, locale: target.locale, pass: false}; report.rows.push(row);
            try {
                await guard(page, row, report);
                const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle', timeout: 60000}); assert.equal(response.status(), 200); assert.equal(new URL(page.url()).origin, BASE);
                await page.waitForFunction(() => Boolean(window.Alpine && document.querySelector('[data-sentence-builder]') && window.Alpine.$data(document.querySelector('[data-sentence-builder]')).currentQuestion));
                row.theory = await theory(page, target, row); row.static = await staticPractice(page, target); row.linked = await linkedPractice(page, target);
                row.screenshot = label + '-' + target.slug + '-' + target.locale + '-practice.png';
                await page.locator('[x-data^="theoryPracticeSet("]').locator('.theory-exercise').nth(2).scrollIntoViewIfNeeded();
                await page.screenshot({path: path.join(dir, row.screenshot), fullPage: false, animations: 'disabled'});
                row.linkedScreenshot = label + '-' + target.slug + '-' + target.locale + '-linked.png';
                await page.locator('[data-sentence-builder]').scrollIntoViewIfNeeded();
                await page.screenshot({path: path.join(dir, row.linkedScreenshot), fullPage: false, animations: 'disabled'});
                assert.deepEqual(row.pageErrors, []); assert.deepEqual(row.consoleErrors, []); assert.deepEqual(row.httpErrors, []); assert.deepEqual(row.localFailures, []);
                row.pass = true; console.log(JSON.stringify({path: row.path, staticTasks: 6, linkedPool: row.linked.poolCount, disclosures: row.theory.disclosures, pass: true}));
            } catch (error) {row.failureSha256 = sha(String(error.message)); report.failure = {path: target.path, message: String(error.message).replace(/https?:\/\/\S+/gu, publicUrl).slice(0, 900)}; throw error;}
            finally {await context.close();}
        }
        assert.equal(report.rows.length, 12); assert.deepEqual(report.violations, []);
        assert.equal(report.rows.filter(row => row.linked.contractionFromActualBankAccepted).length >= 3, true, 'Actual-bank contraction acceptance covers all three interface locales');
        for (const [file, expected] of Object.entries(source.sourceHashes)) assert.equal(sha(fs.readFileSync(path.join(ROOT, file))), expected, 'Source unchanged during acceptance');
        report.pass = true;
    } finally {
        if (browser) await browser.close();
        fs.writeFileSync(path.join(dir, label + '-theory-browser.json'), JSON.stringify(report, null, 2), {flag: 'wx'});
    }
    return report;
}
if (require.main === module) run(...process.argv.slice(2)).then(report => console.log(JSON.stringify({pass: report.pass, pages: report.rows.length, staticTasks: 72, liveM26Disclosures: 48, evidence: process.argv[3] + '-theory-browser.json'})))
    .catch(error => {console.error(String(error.message).replace(/https?:\/\/\S+/gu, publicUrl)); process.exitCode = 1;});
module.exports = {sourceData, canBuild, evidenceNames, key, BASE, OUTPUT, run};
