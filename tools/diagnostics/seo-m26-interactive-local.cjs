'use strict';
// Real-page, guest, GET-only local acceptance. Never deploys or writes database/progress.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const ROOT = path.resolve(__dirname, '../..');
const BASE = 'http://gramlyze.loc';
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const master = JSON.parse(fs.readFileSync(path.join(ROOT, 'docs/content/m26-past-perfect-continuous-detail-master.v1.json'), 'utf8'));
const sourcePath = path.join(ROOT, 'database/content-patches/m26-ppc-interactive-practice.v1.json');
const source = JSON.parse(fs.readFileSync(sourcePath, 'utf8'));
const targets = master.targets.map(target => target.expected_theory_path);
const viewports = [{width: 1440, height: 1000}, {width: 390, height: 844}];
const themes = ['light', 'dark'];
const byPath = new Map(source.targets.map(target => ['/theory/tenses/past-perfect-continuous/' + target.slug, target]));
const mainSelector = '[data-theory-main]';
const detailsSelector = '[data-theory-native-extension] > details';
const practiceSelector = 'section.theory-native-block > [x-data^="theoryPracticeSet("]';

function publicUrl(raw) {
    try { const url = new URL(raw); return url.origin + url.pathname; } catch { return '(unparseable URL)'; }
}
function sanitizedError(error) {
    return String(error?.message || error).replace(/https?:\/\/[^\s\])]+/g, publicUrl).slice(0, 1600);
}
async function attachReadOnlyGuard(page, report, row) {
    const requests = [];
    page.on('request', request => requests.push({method: request.method(), url: publicUrl(request.url())}));
    page.on('pageerror', error => row.pageErrors.push(sanitizedError(error)));
    page.on('requestfailed', request => {
        const item = {url: publicUrl(request.url()), reason: request.failure()?.errorText || 'unknown'};
        if (/^https:\/\/fonts\.(googleapis|gstatic)\.com\//.test(item.url)) row.fontFailures.push(item);
        else row.failedRequests.push(item);
    });
    page.on('response', response => {
        if (response.status() >= 400 && new URL(response.url()).origin === BASE) {
            row.httpErrors.push({url: publicUrl(response.url()), status: response.status()});
        }
    });
    await page.route('**/*', route => {
        const request = route.request();
        const url = new URL(request.url());
        const allowedHost = url.origin === BASE || ['fonts.googleapis.com', 'fonts.gstatic.com'].includes(url.hostname);
        if (request.method() !== 'GET' || !allowedHost) {
            report.policyViolations.push({url: publicUrl(url.href), method: request.method()});
            return route.abort('blockedbyclient');
        }
        return route.continue();
    });
    return requests;
}

async function setTheme(page, theme, viewport) {
    const alreadyDark = await page.locator('html').evaluate(node => node.classList.contains('dark'));
    if (alreadyDark === (theme === 'dark')) return;
    const name = theme === 'dark' ? 'Увімкнути темну тему' : 'Увімкнути світлу тему';
    const toggle = page.getByRole('button', {name, exact: true});
    const menu = page.getByRole('button', {name: 'Меню', exact: true});
    const menuOpened = !await toggle.isVisible();
    if (menuOpened) await menu.click();
    await toggle.click();
    await page.waitForFunction(dark => document.documentElement.classList.contains('dark') === dark, theme === 'dark');
    if (menuOpened && viewport.width < 1280) await menu.click();
}

async function verifyBasics(page, baseline) {
    for (const basic of baseline.basics) {
        const block = page.locator('#block-' + basic.id);
        assert.equal(await block.count(), 1, 'Original basic block ID must remain unique');
        assert.equal(await block.evaluate(node => Boolean(node.closest('details'))), false, 'Original basic must stay outside details');
        const visibleSourceText = await block.evaluate(node => {
            const copy = node.cloneNode(true);
            copy.querySelectorAll('[data-theory-native-extension]').forEach(extension => extension.remove());
            return copy.textContent;
        });
        assert.equal(norm(visibleSourceText), basic.text, 'Complete basic text fidelity: ' + basic.id);
    }
}

async function verifyDetails(page, baseline, requests) {
    const details = page.locator(detailsSelector);
    assert.equal(await details.count(), baseline.details.length, 'All approved disclosures rendered');
    const results = [];
    for (let index = 0; index < await details.count(); index++) {
        const item = details.nth(index);
        const summary = item.locator(':scope > summary');
        const geometry = await item.evaluate(node => {
            const card = node.closest('.theory-section-card');
            if (!card) return null;
            const c = card.getBoundingClientRect(), s = node.querySelector('summary').getBoundingClientRect();
            return {cardId: card.id || card.closest('[id]')?.id || null,
                contained: card.contains(node), left: s.left >= c.left - 1, right: s.right <= c.right + 1,
                top: s.top >= c.top - 1, bottom: s.bottom <= c.bottom + 1};
        });
        assert.ok(geometry && geometry.contained && geometry.left && geometry.right && geometry.top && geometry.bottom,
            'The disclosure control must be inside its native content card');
        assert.equal(await item.evaluate(node => node.open), false, 'Initially closed');
        const start = requests.length;
        await summary.click();
        assert.equal(await item.evaluate(node => node.open), true, 'Mouse opens');
        assert.equal(norm(await item.locator('section').textContent()), baseline.details[index].text, 'Approved detail text fidelity');
        await summary.click();
        assert.equal(await item.evaluate(node => node.open), false, 'Mouse closes');
        await summary.focus();
        await page.keyboard.press('Enter');
        assert.equal(await item.evaluate(node => node.open), true, 'Enter opens');
        assert.equal(await summary.evaluate(node => node.matches(':focus-visible') && getComputedStyle(node).outlineStyle !== 'none'), true,
            'Keyboard focus must be visible');
        await page.keyboard.press('Space');
        assert.equal(await item.evaluate(node => node.open), false, 'Space closes');
        assert.equal(requests.length, start, 'Detail disclosure must not fetch additional content');
        results.push({key: baseline.details[index].key, cardId: geometry.cardId, placement: 'inside-native-card', mouse: true, keyboard: true});
    }
    await details.nth(0).locator('summary').click();
    await details.nth(1).locator('summary').click();
    assert.equal(await details.nth(0).evaluate(node => node.open), true, 'Independent disclosure 1');
    assert.equal(await details.nth(1).evaluate(node => node.open), true, 'Independent disclosure 2');
    await details.nth(0).locator('summary').click();
    await details.nth(1).locator('summary').click();
    return results;
}

async function verifySelectionGroup(group, name, items, options, selectedClass) {
    const rows = group.locator('label').locator('..');
    assert.equal(await rows.count(), 2, name + ' has two tasks');
    const check = group.getByRole('button', {name: 'Перевірити', exact: true});
    const reset = group.getByRole('button', {name: 'Спробувати ще раз', exact: true});
    assert.equal(await check.isVisible(), true, name + ' check button visible');
    for (const [index, item] of items.entries()) {
        assert.equal(norm(await rows.nth(index).locator('label').textContent()), norm(item.label));
        const correct = rows.nth(index).getByRole('button', {name: item.answer, exact: true});
        const wrongText = options.find(option => option !== item.answer);
        assert.ok(wrongText, name + ' must have a wrong option');
        const wrong = rows.nth(index).getByRole('button', {name: wrongText, exact: true});
        await wrong.click();
        assert.equal(await wrong.evaluate((node, css) => node.classList.contains(css) && node.classList.contains('text-white'), selectedClass), true,
            name + ' selected option highlighted');
        await correct.click();
        assert.equal(await correct.evaluate((node, css) => node.classList.contains(css) && node.classList.contains('text-white'), selectedClass), true);
        assert.equal(await wrong.evaluate(node => node.classList.contains('text-white')), false, 'Previous selection cleared');
    }
    await check.click();
    const score = group.locator('p[x-text="scoreText(\'' + name + '\')"]');
    await score.waitFor({state: 'visible'});
    assert.equal(await score.isVisible(), true, name + ' score appears');
    assert.match(norm(await score.textContent()), /2\s*\/\s*2|2\s*(?:з|із)\s*2/u, name + ' complete score');
    for (const [index, item] of items.entries()) {
        assert.equal(await rows.nth(index).getByRole('button', {name: item.answer, exact: true}).evaluate(node => node.classList.contains('ring-emerald-300')), true);
    }
    assert.equal(await reset.isVisible(), true, name + ' reset button visible');
    await reset.click();
    await score.waitFor({state: 'hidden'});
    assert.equal(await score.isVisible(), false, name + ' score hidden after reset');
    assert.equal(await group.locator('button.text-white').count(), 1, 'Only Check button remains filled after reset');
    return {tasks: items.length, selectionHighlight: true, check: true, correctScore: '2/2', reset: true};
}

async function verifyInputs(group, items, requests) {
    const results = [];
    const check = group.getByRole('button', {name: 'Перевірити', exact: true});
    const reset = group.getByRole('button', {name: 'Спробувати ще раз', exact: true});
    const score = group.locator('p[x-text="scoreText(\'inputs\')"]');
    assert.equal(await group.locator('input[data-word-suggestion-input]').count(), 2);
    for (const [index, item] of items.entries()) {
        const input = group.locator('input[data-word-suggestion-input="inputs-' + index + '"]');
        const task = input.locator('xpath=../..');
        const tokens = item.before.split('/').map(value => value.trim()).filter(Boolean);
        assert.ok(tokens.length > 1);
        const bank = task.locator('button').filter({has: task.page().locator('span[x-text="token.value"]')});
        assert.equal(await bank.count(), tokens.length, 'Every authored token group visible');
        const actualTokens = (await bank.allTextContents()).map(norm);
        assert.deepEqual(actualTokens, tokens, 'Authored shuffled token bank');
        const first = bank.nth(0);
        const start = requests.length;
        await first.click();
        assert.equal(norm(await input.inputValue()), tokens[0], 'Token click appends its full group');
        assert.equal(await first.isDisabled(), true, 'Used token unavailable');
        assert.equal(await input.getAttribute('autocomplete'), 'off');
        assert.equal(await task.locator('[x-show^="isWordSuggestionOpen"]').count(), 0, 'Token fields do not expose autocomplete dropdown');
        await input.focus();
        await input.press('ControlOrMeta+A');
        await input.press('Backspace');
        assert.equal(await input.inputValue(), '');
        assert.equal(await first.isEnabled(), true, 'Deleting a clicked token makes it available again');
        await first.click();
        assert.equal(norm(await input.inputValue()), tokens[0], 'The token can be reused');
        assert.equal(requests.length, start, 'Token bank interactions do not request suggestions');
        await input.fill('');
        const words = value => value.toLowerCase().replace(/[.,!?;:()]+/gu, ' ').trim().split(/\s+/u).filter(Boolean);
        const answerWords = words(item.answer);
        const unused = tokens.map((value, tokenIndex) => ({value, tokenIndex, words: words(value)}));
        for (let offset = 0; offset < answerWords.length;) {
            const at = unused.findIndex(token => token.words.every((word, wordIndex) => word === answerWords[offset + wordIndex]));
            assert.ok(at >= 0, 'The authored bank must assemble the complete canonical answer');
            const token = unused.splice(at, 1)[0];
            await bank.nth(token.tokenIndex).click();
            offset += token.words.length;
        }
        assert.equal(unused.length, 0, 'Every authored group is used exactly once');
        assert.deepEqual(words(await input.inputValue()), answerWords, 'Complete answer can be composed by clicking');
        assert.equal(await bank.evaluateAll(nodes => nodes.every(node => node.disabled)), true);
        results.push({tokens: tokens.length, clickAppend: true, completeClickAnswer: true, backspaceUnlock: true, autocomplete: 'off'});
    }
    await check.click();
    await score.waitFor({state: 'visible'});
    assert.match(norm(await score.textContent()), /2\s*\/\s*2|2\s*(?:з|із)\s*2/u, 'Manual full-sentence score');
    for (let index = 0; index < items.length; index++) {
        assert.equal(await group.locator('input').nth(index).evaluate(node => node.classList.contains('border-emerald-400')), true);
    }
    for (const [index, item] of items.entries()) {
        for (const accepted of item.accepted || [item.answer]) {
            // No terminal punctuation is required; supported contractions are provided by the canonical source.
            await group.locator('input').nth(index).fill(accepted.replace(/[.!?]+$/u, ''));
            await check.click();
            assert.equal(await group.locator('input').nth(index).evaluate(node => node.classList.contains('border-emerald-400')), true,
                'Every canonical full/contracted/number answer accepted without terminal punctuation');
        }
        results[index].acceptedVariants = (item.accepted || [item.answer]).length;
    }
    await reset.click();
    await score.waitFor({state: 'hidden'});
    assert.equal(await score.isVisible(), false);
    for (const input of await group.locator('input').all()) assert.equal(await input.inputValue(), '');
    assert.equal(await group.locator('button[disabled]').count(), 0, 'Reset makes all token groups reusable');
    return {tasks: 2, manualCheck: true, reset: true, rows: results};
}

async function verifyPractice(page, target, requests) {
    const practice = page.locator(practiceSelector);
    assert.equal(await practice.count(), target ? 1 : 0, 'Exactly one lesson practice set, none on overview');
    if (!target) return null;
    const data = target.practice.body_data;
    const exercises = practice.locator('.theory-exercise');
    assert.equal(await exercises.count(), 3);
    for (const [index, title] of [data.select_title, data.choice_title, data.input_title].entries()) {
        assert.ok(norm(await exercises.nth(index).locator('h3').textContent()).endsWith(title), 'Exercise title and visible number');
        assert.equal(await exercises.nth(index).locator('h3 > span').isVisible(), true, 'Visible coloured exercise number');
    }
    const selects = await verifySelectionGroup(exercises.nth(0), 'selects', data.selects, data.options, 'bg-blue-600');
    const choices = await verifySelectionGroup(exercises.nth(1), 'choices', data.choices, data.choice_options, 'bg-amber-600');
    const inputs = await verifyInputs(exercises.nth(2), data.inputs, requests);
    const widget = practice.locator('[data-sentence-builder]');
    assert.equal(await widget.count(), 1, 'Own-topic Sentence Builder is attached');
    assert.equal(await widget.isVisible(), true);
    assert.equal(norm(await widget.locator('h4').textContent()), data.linked_practice.title);
    const pool = await widget.evaluate(node => {
        const state = window.Alpine.$data(node);
        return {count: state.questions?.length || 0, types: [...new Set((state.questions || []).map(question => question.type))],
            currentId: state.currentQuestion?.id || null, currentText: state.currentQuestion?.question || null,
            correctTokens: state.currentQuestion?.correct_tokens || []};
    });
    assert.ok(pool.count > 0 && pool.currentId && pool.currentText, 'A real linked question has loaded');
    assert.deepEqual(pool.types, ['4'], 'Linked Sentence Builder uses Compose Tokens questions only');
    assert.equal(await widget.locator('button').filter({hasText: /.+/}).count() > 0, true, 'Interactive bank renders actual tokens');
    return {selects, choices, inputs, linked: {...pool, expectedSeeder: data.linked_practice.seeder_classes[0]}};
}

async function overflow(page) {
    const result = {documentPixels: await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - innerWidth))};
    result.unclippedLearning = await page.locator(mainSelector).evaluate(root => {
        const clipped = node => {
            for (let parent = node.parentElement; parent; parent = parent.parentElement) {
                if (['auto', 'scroll', 'hidden', 'clip'].includes(getComputedStyle(parent).overflowX)) return true;
            }
            return false;
        };
        return [root, ...root.querySelectorAll('*')].filter(node => {
            const box = node.getBoundingClientRect();
            return box.width && (box.right > innerWidth + 1 || box.left < -1) && !clipped(node);
        }).map(node => ({tag: node.tagName, id: node.id || null}));
    });
    assert.deepEqual(result.unclippedLearning, [], 'No unclipped learning-content horizontal overflow');
    result.decorativeOutsidePixels = await page.locator('#shell-random-shapes span').evaluateAll(nodes => nodes.map(node => {
        const box = node.getBoundingClientRect(); return Math.max(0, box.right - innerWidth, -box.left);
    }).filter(value => value > 0));
    result.tables = await page.locator('.theory-table-scroll').evaluateAll(nodes => nodes.map(node => ({
        overflowX: getComputedStyle(node).overflowX, width: node.clientWidth, scrollWidth: node.scrollWidth
    })));
    assert.ok(result.tables.every(table => ['auto', 'scroll'].includes(table.overflowX)), 'Wide tables scroll locally');
    return result;
}

async function run(dir, label, baselinePath) {
    const baseline = JSON.parse(fs.readFileSync(baselinePath, 'utf8'));
    assert.equal(baseline.base, BASE);
    const expected = new Map(baseline.rows.filter(row => targets.includes(row.path)).map(row => [row.path, row]));
    assert.equal(expected.size, 5, 'Complete previously accepted basic baseline required');
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const report = {at: new Date().toISOString(), base: BASE, sourceSha256: sha(fs.readFileSync(sourcePath)),
        baselineSha256: sha(fs.readFileSync(baselinePath)), conditions: {guest: true, onlyGet: true, fixtures: false},
        states: [], noJavaScript: [], policyViolations: [], pass: false};
    const browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
    try {
        for (const viewport of viewports) for (const theme of themes) for (const [index, pathname] of targets.entries()) {
            const context = await browser.newContext({viewport, colorScheme: theme});
            const page = await context.newPage();
            const row = {path: pathname, viewport, theme, at: new Date().toISOString(), pageErrors: [], failedRequests: [], fontFailures: [], httpErrors: []};
            report.states.push(row);
            try {
                const requests = await attachReadOnlyGuard(page, report, row);
                const response = await page.goto(BASE + pathname, {waitUntil: 'networkidle'});
                assert.equal(response.status(), 200);
                row.status = response.status();
                row.metadata = await page.evaluate(() => ({title: document.title, h1: [...document.querySelectorAll('h1')].map(node => node.textContent.trim()),
                    canonical: document.querySelector('link[rel="canonical"]')?.getAttribute('href') || null}));
                await page.waitForFunction(() => Boolean(window.Alpine));
                await setTheme(page, theme, viewport);
                await verifyBasics(page, expected.get(pathname));
                row.details = await verifyDetails(page, expected.get(pathname), requests);
                row.practice = await verifyPractice(page, byPath.get(pathname), requests);
                row.overflow = await overflow(page);
                const filename = label + '-' + index + '-' + viewport.width + '-' + theme + '.png';
                // Click/focus checks scroll the page; settle the real sticky header before visual capture.
                await page.evaluate(() => window.scrollTo(0, 0));
                await page.waitForFunction(() => window.scrollY === 0);
                await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
                await page.screenshot({path: path.join(dir, filename), fullPage: true, animations: 'disabled'});
                row.screenshot = filename;
                await page.reload({waitUntil: 'networkidle'});
                assert.equal(await page.locator(detailsSelector).evaluateAll(nodes => nodes.every(node => !node.open)), true, 'Reload starts with closed disclosures');
                const detailId = expected.get(pathname).details[0].id;
                await page.goto(BASE + pathname + '#' + detailId, {waitUntil: 'networkidle'});
                await page.waitForFunction(id => document.getElementById(id)?.closest('details')?.open, detailId);
                row.deepFragment = true;
                const printState = await page.locator('[data-theory-details]').evaluateAll(nodes => nodes.map(node => node.open));
                await page.emulateMedia({media: 'print'});
                await page.waitForFunction(() => [...document.querySelectorAll('[data-theory-details]')].every(node => node.open));
                await page.emulateMedia({media: 'screen'});
                await page.waitForFunction(state => JSON.stringify([...document.querySelectorAll('[data-theory-details]')].map(node => node.open)) === JSON.stringify(state), printState);
                row.printRestores = true;
                assert.deepEqual(row.pageErrors, []);
                assert.deepEqual(row.failedRequests, []);
                assert.deepEqual(row.httpErrors, []);
                row.pass = true;
                console.log(JSON.stringify({path: pathname, viewport: viewport.width, theme, pass: true, documentOverflow: row.overflow.documentPixels}));
            } catch (error) { row.failure = sanitizedError(error); throw error; } finally { await context.close(); }
        }
        for (const pathname of targets) {
            const context = await browser.newContext({javaScriptEnabled: false, viewport: viewports[0]});
            const page = await context.newPage();
            const row = {path: pathname, pageErrors: [], failedRequests: [], fontFailures: [], httpErrors: []};
            try {
                await attachReadOnlyGuard(page, report, row);
                const response = await page.goto(BASE + pathname, {waitUntil: 'networkidle'});
                assert.equal(response.status(), 200);
                await verifyBasics(page, expected.get(pathname));
                const disclosure = page.locator(detailsSelector).nth(0);
                assert.equal(await disclosure.evaluate(node => Boolean(node.closest('.theory-section-card'))), true);
                await disclosure.locator('summary').click();
                assert.equal(await disclosure.locator('section').isVisible(), true, 'Native details works without JavaScript');
                report.noJavaScript.push({path: pathname, pass: true});
            } finally { await context.close(); }
        }
        assert.deepEqual(report.policyViolations, []);
        report.pass = true;
        // Strict document criterion is independent, never hidden or relaxed for existing decoration.
        report.strictDocumentOverflowPass = report.states.every(row => row.overflow.documentPixels === 0);
        report.learningOverflowPass = report.states.every(row => row.overflow.unclippedLearning.length === 0);
    } catch (error) { report.failure = sanitizedError(error); throw error; } finally {
        await browser.close();
        fs.writeFileSync(path.join(dir, label + '-browser.json'), JSON.stringify(report, null, 2), {flag: 'wx'});
    }
    console.log(JSON.stringify({pass: report.pass, states: report.states.length, noJavaScript: report.noJavaScript.length,
        strictDocumentOverflowPass: report.strictDocumentOverflowPass, learningOverflowPass: report.learningOverflowPass}));
    return report;
}

/** Clean real-page element evidence, clipped from an unscrolled full-page capture. */
async function captureElements(dir, label) {
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
    const report = {at: new Date().toISOString(), base: BASE, policyViolations: [], screenshots: [], pass: false};
    const pathname = '/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms';
    try {
        for (const viewport of viewports) {
            const context = await browser.newContext({viewport, colorScheme: 'light'});
            const page = await context.newPage();
            const row = {path: pathname, viewport, pageErrors: [], failedRequests: [], fontFailures: [], httpErrors: []};
            try {
                await attachReadOnlyGuard(page, report, row);
                const response = await page.goto(BASE + pathname, {waitUntil: 'networkidle'});
                assert.equal(response.status(), 200);
                await page.waitForFunction(() => Boolean(window.Alpine));
                await setTheme(page, 'light', viewport);
                await page.evaluate(() => window.scrollTo(0, 0));
                await page.waitForFunction(() => window.scrollY === 0);
                await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
                assert.equal(await page.locator(detailsSelector).evaluateAll(nodes => nodes.every(node => !node.open)), true);
                const pieces = viewport.width === 1440
                    ? [['native-card', page.locator('.theory-section-card').filter({has: page.locator('[data-theory-native-extension]')}).first()],
                        ['practice', page.locator(practiceSelector)]]
                    : [['practice', page.locator(practiceSelector)]];
                for (const [kind, element] of pieces) {
                    const box = await element.boundingBox();
                    assert.ok(box && box.width > 0 && box.height > 0);
                    const filename = label + '-' + kind + '-' + viewport.width + '-light.png';
                    await page.screenshot({path: path.join(dir, filename), fullPage: true, clip: box, animations: 'disabled'});
                    report.screenshots.push({...row, kind, filename, clip: box, capture: 'fresh-guest-scroll-top-full-page-clip', mask: false});
                }
                assert.deepEqual(row.pageErrors, []);
                assert.deepEqual(row.failedRequests, []);
                assert.deepEqual(row.httpErrors, []);
            } finally { await context.close(); }
        }
        assert.deepEqual(report.policyViolations, []);
        report.pass = true;
        return report;
    } catch (error) { report.failure = sanitizedError(error); throw error; } finally {
        await browser.close();
        fs.writeFileSync(path.join(dir, label + '-elements.json'), JSON.stringify(report, null, 2), {flag: 'wx'});
        console.log(JSON.stringify({pass: report.pass, screenshots: report.screenshots.map(item => item.filename)}));
    }
}

/** Public metadata and teaching-text digest only; no HTML, cookies or CSRF are persisted. */
async function checkControls(dir, label, baselinePath = path.join(dir, 'after-http.json')) {
    const {JSDOM} = require('jsdom');
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const baseline = JSON.parse(fs.readFileSync(baselinePath, 'utf8'));
    const report = {at: new Date().toISOString(), base: BASE, policyViolations: [], rows: [], pass: false};
    const paths = ['/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms',
        '/theory/tenses/present-perfect/present-perfect-forms'];
    const browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
    try {
        for (const pathname of paths) {
            const context = await browser.newContext({viewport: viewports[0], colorScheme: 'light'});
            const page = await context.newPage();
            const row = {path: pathname, at: new Date().toISOString(), pageErrors: [], failedRequests: [], fontFailures: [], httpErrors: []};
            report.rows.push(row);
            try {
                await attachReadOnlyGuard(page, report, row);
                const response = await page.goto(BASE + pathname, {waitUntil: 'networkidle'});
                assert.equal(response.status(), 200);
                row.status = response.status();
                row.contentType = response.headers()['content-type'] || null;
                row.robotsHeader = response.headers()['x-robots-tag'] || null;
                const dom = new JSDOM(await response.text());
                const document = dom.window.document;
                row.metadata = {title: document.title, h1: [...document.querySelectorAll('h1')].map(node => norm(node.textContent)),
                    canonical: document.querySelector('link[rel="canonical"]')?.getAttribute('href') || null};
                const learner = document.querySelector(mainSelector).cloneNode(true);
                learner.querySelectorAll('script,style').forEach(node => node.remove());
                row.learnerSha256 = sha(norm(learner.textContent));
                dom.window.close();
                const before = baseline.rows.find(item => item.path === pathname);
                row.baselineAvailable = Boolean(before?.learnerSha256);
                if (row.baselineAvailable) {
                    assert.equal(row.learnerSha256, before.learnerSha256, 'Existing control full teaching text unchanged');
                    row.teachingTextUnchanged = true;
                }
                await page.waitForFunction(() => Boolean(window.Alpine));
                row.m26Extensions = await page.locator('[data-theory-native-extension]').count();
                assert.equal(row.m26Extensions, 0, 'M26 disclosures must never leak onto control topics');
                const practice = page.locator(practiceSelector);
                row.practiceSets = await practice.count();
                row.practiceGroups = await practice.locator('.theory-exercise').count();
                row.checkButtons = await practice.locator('.theory-exercise').getByRole('button', {name: 'Перевірити', exact: true}).count();
                row.linkedWidgets = await practice.locator('[data-sentence-builder]').count();
                if (pathname === paths[0]) {
                    assert.equal(row.practiceSets, 1);
                    assert.equal(row.practiceGroups, 3, 'Reference exposes the same three native groups');
                    assert.equal(row.checkButtons, 3, 'All three reference Check buttons render');
                    assert.equal(row.linkedWidgets, 1, 'Reference linked Sentence Builder renders');
                    row.checkButtonsVisible = await Promise.all((await practice.locator('.theory-exercise').getByRole('button', {name: 'Перевірити', exact: true}).all()).map(button => button.isVisible()));
                    assert.deepEqual(row.checkButtonsVisible, [true, true, true]);
                }
                assert.deepEqual(row.pageErrors, []);
                assert.deepEqual(row.failedRequests, []);
                assert.deepEqual(row.httpErrors, []);
                row.pass = true;
            } finally { await context.close(); }
        }
        assert.deepEqual(report.policyViolations, []);
        report.pass = true;
        return report;
    } catch (error) { report.failure = sanitizedError(error); throw error; } finally {
        await browser.close();
        fs.writeFileSync(path.join(dir, label + '-controls.json'), JSON.stringify(report, null, 2), {flag: 'wx'});
        console.log(JSON.stringify({pass: report.pass, controls: report.rows.map(row => ({path: row.path, status: row.status,
            practiceGroups: row.practiceGroups, checkButtons: row.checkButtons, linkedWidgets: row.linkedWidgets,
            m26Extensions: row.m26Extensions, teachingTextUnchanged: row.teachingTextUnchanged}))}));
    }
}

if (require.main === module) {
    const [rawDir, label, rawBaseline] = process.argv.slice(2);
    assert.ok(rawDir && label, 'Usage: node seo-m26-interactive-local.cjs PRIVATE_OUTPUT_DIR LABEL [BASELINE_HTTP_JSON]');
    assert.match(label, /^[a-z0-9-]+$/u);
    const dir = path.resolve(rawDir);
    assert.equal(path.basename(dir), 'seo-m26-local', 'Diagnostics remain in the private local M26 directory');
    assert.equal(path.basename(path.dirname(dir)), 'app');
    assert.equal(path.basename(path.dirname(path.dirname(dir))), 'storage');
    assert.ok(fs.existsSync(dir), 'Use an existing verified local evidence directory');
    assert.ok(!fs.existsSync(path.join(dir, label + '-browser.json')), 'Do not overwrite previous evidence');
    const baseline = path.resolve(rawBaseline || path.join(dir, 'after-http.json'));
    run(dir, label, baseline).catch(error => { console.error(sanitizedError(error)); process.exitCode = 1; });
}
module.exports = {targets, byPath, run, captureElements, checkControls};
