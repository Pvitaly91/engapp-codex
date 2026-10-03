'use strict';
// Real, unauthenticated GET-only gramlyze.loc acceptance. No production, DB writes or secret evidence.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const {guard, BASE, fidelity, practice, htmlText} = require('./seo-m28-local.cjs');
const {capture, controls} = require('./capture-m30-http.cjs');
const ROOT = path.resolve(__dirname, '../..');
const read = file => JSON.parse(fs.readFileSync(file, 'utf8'));
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const publicUrl = value => {try {const u = new URL(value); return u.origin + u.pathname;} catch {return '(no URL)';}};
const sources = [
    ['m30-m14-participle-clauses.v1.json', 'M14'],
    ['m29-m13-sentence-structure.v2.json', 'M29'],
    ['m27-m11-linking-words.v2.json', 'M27'],
    ['m28-m12-emphasis-inversion.v2.json', 'M28'],
];
const allTargets = sources.flatMap(([file, group]) => read(path.join(ROOT, 'database/content-patches', file)).targets.map(target => ({
    ...target, group, path: '/theory/' + (target.ancestry || ['clauses-and-linking-words']).concat(target.slug).join('/'),
})));
const targets = allTargets.filter(target => ['M14', 'M29'].includes(target.group));
const regressions = allTargets.filter(target => ['M27', 'M28'].includes(target.group));
const points = target => target.plans.reduce((count, plan) => count + plan.points.filter(point => point.detail !== '').length, 0);
const save = (dir, name, value) => fs.writeFileSync(path.join(dir, name), JSON.stringify(value, null, 2), {flag: 'wx'});

function exactAuthorSelfCheck(doc, target, noJS = false) {
    if (target.group !== 'M14') return;
    const data = JSON.parse(target.after.page.blocks.find(block => block.type === 'practice-set').body);
    const source = data.author_self_check;
    assert.ok(source && source.prompts.length === 6 && source.answers.length === 6);
    assert.equal(htmlText(doc.querySelector('[data-m30-self-check-intro]')?.innerHTML), htmlText(source.intro));
    const tasks = [...doc.querySelectorAll('[data-m30-author-prompt]')];
    assert.equal(tasks.length, source.prompts.length);
    for (const [index, prompt] of source.prompts.entries()) {
        const matches = tasks.filter(task => task.getAttribute('data-m30-author-prompt') === String(index + 1));
        assert.equal(matches.length, 1, 'Unique exact accepted practice source index');
        assert.equal(htmlText(matches[0].innerHTML), htmlText(prompt), 'Accepted prompt preserved in practice');
    }
    const key = doc.querySelector('[data-m30-self-check-answers]');
    assert.ok(key, 'Author key remains visible rather than hidden in a detail');
    assert.deepEqual([...key.querySelectorAll('li')].map(item => htmlText(item.innerHTML)), source.answers.map(htmlText));
    if (noJS) {
        const prompts = [...doc.querySelectorAll('[data-m30-self-check-no-js] li')];
        assert.deepEqual(prompts.map(item => htmlText(item.innerHTML)), source.prompts.map(htmlText), 'No-JS exact prompts');
    }
}

async function content(page, target, noJS = false) {
    const dom = new JSDOM(await page.content());
    try {fidelity(dom.window.document, target); exactAuthorSelfCheck(dom.window.document, target, noJS);} finally {dom.window.close();}
    const expected = points(target);
    assert.equal(await page.locator('[data-theory-native-extension] > details').count(), expected);
    if (expected === 0) {
        assert.equal(await page.locator('[data-theory-native-extension]').count(), 0, 'No empty or pointless disclosure wrappers');
        for (const card of await page.locator('[data-theory-main] article.theory-item').all()) {
            assert.ok(await card.isVisible(), 'Basic point immediately visible');
            assert.equal(await card.locator('details[data-theory-details]').count(), 0);
        }
    }
}

async function observe(page, row, violations) {
    const requests = await guard(page, row, violations);
    row.consoleErrors = []; row.expectedFontConsoleErrors = [];
    page.on('console', message => {
        if (message.type() !== 'error') return;
        const item = {source: publicUrl(message.location().url), messageSha256: sha(message.text())};
        if (/^https:\/\/fonts\.(googleapis|gstatic)\.com(?:\/|$)/.test(message.location().url)) row.expectedFontConsoleErrors.push(item);
        else row.consoleErrors.push(item);
    });
    return requests;
}

function clean(row) {
    assert.deepEqual(row.errors, [], 'No JavaScript page errors');
    assert.deepEqual(row.httpErrors, [], 'No failed HTTP responses');
    assert.deepEqual(row.failed, [], 'No failed local requests');
    assert.deepEqual(row.consoleErrors, [], 'No unclassified console errors');
    assert.ok(row.fontFailures.every(item => /ERR_NETWORK_ACCESS_DENIED|ERR_FAILED|ERR_NAME_NOT_RESOLVED|ERR_CONNECTION|csp/i.test(item.reason || '')), 'Font failures classified separately');
}

async function bankProof(dir, target, result) {
    const app = path.resolve(dir, '..');
    let inventory;
    if (target.group === 'M14') inventory = read(path.join(dir, 'm30-bank-inventory-v1.json'));
    else if (target.group === 'M29') inventory = read(path.join(app, 'seo-m29-local', 'm29-bank-inventory-v1.json'));
    else {
        const candidates = [path.join(app, 'm28-quality-before-db.json'),
            path.join(app, 'seo-m11-worktree/storage/app/m28-quality-before-db.json')];
        const existing = candidates.find(file => fs.existsSync(file));
        assert.ok(existing, 'Read-only accepted M27/M28 bank inventory available');
        inventory = read(existing);
    }
    const owner = inventory.targets.find(item => item.slug === target.slug);
    const allowed = owner?.linked_bank_ids?.[result.expectedSeeder];
    assert.ok(allowed?.length > 0, 'Exact primary linked bank inventoried');
    assert.ok(result.linked.ids.length > 0);
    assert.equal(new Set(result.linked.ids).size, result.linked.ids.length, 'Linked pool has no repeated questions');
    assert.ok(result.linked.ids.every(id => allowed.includes(id)), 'No foreign level/test bank in widget');
    result.linked.exactBank = true;
    result.linked.bankInventoryCount = allowed.length;
    result.linked.bankIdsSha256 = sha(JSON.stringify(allowed));
}

async function negativePunctuation(page, target) {
    if (target.slug !== 'advanced-participle-and-absolute-clauses') return {applicable: false};
    const root = page.locator('[x-data^="theoryPracticeSet("]');
    const group = root.locator('.theory-exercise').nth(2);
    const check = group.getByRole('button', {name: 'Перевірити', exact: true});
    const reset = group.getByRole('button', {name: 'Спробувати ще раз', exact: true});
    const data = JSON.parse(target.after.page.blocks.find(block => block.type === 'practice-set').body);
    const invalid = [
        [0, 'The lights, having been switched off the guard locked the hall.'],
        [0, 'The lights had been switched off, the guard locked the hall.'],
        [1, 'After the rehearsal had ended the instruments were packed The players were tired The conductor thanked everyone.'],
    ];
    for (const [index, answer] of invalid) {
        for (const [i, item] of data.inputs.entries()) await group.locator('input').nth(i).fill(item.answer);
        await group.locator('input').nth(index).fill(answer); await check.click();
        assert.ok(await group.locator('input').nth(index).evaluate(input => input.classList.contains('border-rose-400')), 'Invalid internal punctuation/model rejected');
        await reset.click();
    }
    return {applicable: true, rejectedFixtures: invalid.length};
}

async function tokenAndManualProof(page, target) {
    const data = JSON.parse(target.after.page.blocks.find(block => block.type === 'practice-set').body);
    const root = page.locator('[x-data^="theoryPracticeSet("]');
    const group = root.locator('.theory-exercise').nth(2);
    const check = group.getByRole('button', {name: 'Перевірити', exact: true});
    const reset = group.getByRole('button', {name: 'Спробувати ще раз', exact: true});
    const words = value => value.toLowerCase().replace(/[.,!?;:]+/g, '').split(/\s+/).filter(Boolean);
    const results = [];
    for (const [index, item] of data.inputs.entries()) {
        if (!item.before?.includes('/')) {results.push({index, tokenBank: false}); continue;}
        const input = group.locator('input').nth(index);
        const task = input.locator('xpath=../..');
        const bank = task.locator('button').filter({has: page.locator('span[x-text="token.value"]')});
        const first = bank.first(); await first.click(); assert.ok(await first.isDisabled());
        await input.press('ControlOrMeta+A'); await input.press('Backspace'); assert.ok(await first.isEnabled(), 'Deleted token is reusable');
        const available = item.before.split('/').map(value => value.trim());
        const expected = words(item.answer); let offset = 0;
        while (offset < expected.length) {
            const token = available.findIndex(value => value && words(value).every((word, k) => expected[offset + k] === word));
            assert.ok(token >= 0, 'Exact answer is constructible from its own token groups');
            await bank.nth(token).click(); offset += words(available[token]).length; available[token] = '';
        }
        assert.deepEqual(words(await input.inputValue()), expected, 'Clicked token sequence matches accepted answer before any manual replacement');
        await check.click();
        assert.ok(await input.evaluate(node => node.classList.contains('border-emerald-400')), 'Token-only answer is accepted');
        await reset.click();
        results.push({index, tokenBank: true, tokenOnlyAccepted: true, backspaceReusable: true});
    }
    for (const [index, item] of data.inputs.entries()) await group.locator('input').nth(index).fill(item.answer.replace(/[.!?]+$/, ''));
    await check.click();
    for (const input of await group.locator('input').all()) assert.ok(await input.evaluate(node => node.classList.contains('border-emerald-400')), 'Manual complete answer without terminal punctuation is accepted');
    await reset.click();
    return {inputs: results, manualAccepted: true, terminalPunctuationOptional: true};
}

async function overflow(page) {
    const result = await page.locator('[data-theory-main]').evaluate(root => ({
        main: Math.max(0, root.scrollWidth - root.clientWidth),
        document: Math.max(0, document.documentElement.scrollWidth - innerWidth),
        cards: [...root.querySelectorAll('.theory-section-card,article.theory-item')].filter(item => !item.closest('details:not([open])'))
            .map(item => Math.max(0, item.scrollWidth - item.clientWidth)),
        tables: [...root.querySelectorAll('.theory-table-scroll')].map(item => ({client: item.clientWidth, scroll: item.scrollWidth, overflow: getComputedStyle(item).overflowX})),
        decorative: [...document.querySelectorAll('#shell-random-shapes span')].map(item => {const rect = item.getBoundingClientRect(); return Math.max(0, rect.right - innerWidth, -rect.left);}).filter(value => value > 0),
    }));
    assert.equal(result.main, 0, 'Learning main overflow is zero');
    assert.ok(result.cards.every(value => value === 0), 'Learning cards/points overflow is zero');
    assert.ok(result.tables.every(table => ['auto', 'scroll'].includes(table.overflow)), 'Tables own their horizontal scrolling');
    return result;
}

async function boundedShot(page, dir, file) {
    await page.screenshot({path: path.join(dir, file), animations: 'disabled', fullPage: false});
    return file;
}

async function screenshots(page, dir, label, target, viewport, theme) {
    const stem = label + '-' + target.group.toLowerCase() + '-' + target.slug + '-' + viewport.width + '-' + theme;
    await page.evaluate(() => window.scrollTo(0, 0));
    const shots = [await boundedShot(page, dir, stem + '-top.png')];
    if (target.group !== 'M14') return shots;
    const sections = {
        'participle-clauses-basics': [3, 5, 7],
        'participle-clauses': [2, 3, 5, 6, 7],
        'advanced-participle-and-absolute-clauses': [2, 3, 4, 5, 6, 7],
    }[target.slug];
    for (const section of sections) {
        const data = JSON.parse(target.after.page.blocks[section].body);
        const headings = page.locator('.theory-native-block .theory-section-header h2');
        let found = null;
        for (const heading of await headings.all()) {
            if (norm(await heading.evaluate(node => {const clone = node.cloneNode(true); clone.querySelectorAll('[data-theory-ui]').forEach(item => item.remove()); return clone.textContent;})) === htmlText(data.title)) {found = heading; break;}
        }
        assert.ok(found, 'Screenshot targets exact author section');
        await found.evaluate(node => node.closest('section.theory-native-block').scrollIntoView({block: 'start'}));
        shots.push(await boundedShot(page, dir, stem + '-section-' + section + '.png'));
    }
    return shots;
}

async function detailsInteraction(page, target, requests) {
    const details = page.locator('[data-theory-native-extension] > details');
    const count = points(target); assert.equal(await details.count(), count);
    assert.ok(await details.evaluateAll(items => items.every(item => !item.open)));
    if (!count) return {count, interaction: 'not applicable: complete basic has no point details'};
    for (const detail of await details.all()) {
        assert.ok(await detail.evaluate(node => Boolean(node.closest('article.theory-item'))), 'Own-point placement');
        const summary = detail.locator(':scope > summary'); const start = requests.length;
        await summary.click(); assert.ok(await detail.evaluate(node => node.open));
        assert.equal(await details.evaluateAll(items => items.filter(item => item.open).length), 1, 'Independent opening');
        await summary.click(); await summary.focus(); await page.keyboard.press('Enter');
        assert.ok(await detail.evaluate(node => node.open));
        assert.ok(await summary.evaluate(node => node.matches(':focus-visible') && getComputedStyle(node).outlineStyle !== 'none'));
        await page.keyboard.press('Space'); assert.ok(await detail.evaluate(node => !node.open));
        assert.equal(requests.length, start, 'No detail fetch');
    }
    if (count > 1) {
        await details.nth(0).locator(':scope > summary').click(); await details.nth(1).locator(':scope > summary').click();
        assert.equal(await details.evaluateAll(items => items.filter(item => item.open).length), 2);
        await details.nth(0).locator(':scope > summary').click(); assert.ok(await details.nth(1).evaluate(node => node.open));
        await details.nth(1).locator(':scope > summary').click();
    }
    return {count, mouse: true, enter: true, space: true, focusVisible: true, independent: true, simultaneous: count > 1, noDetailFetch: true};
}

async function navigationAndPrint(page, target, baseline) {
    await page.reload({waitUntil: 'networkidle'}); await content(page, target);
    const details = page.locator('[data-theory-native-extension] > details');
    assert.ok(await details.evaluateAll(items => items.every(item => !item.open)), 'Reload starts closed');
    const legacy = baseline.rows.find(row => row.path === target.path)?.legacyIds || [];
    for (const id of legacy) assert.equal(await page.locator('[id="' + id + '"]').count(), 1, 'Preserved legacy fragment');
    let deep;
    if (points(target)) {
        const id = await details.first().locator('.theory-point-fragment').getAttribute('id');
        await page.goto(BASE + target.path + '#' + id, {waitUntil: 'networkidle'});
        await page.waitForFunction(id => document.getElementById(id)?.closest('details')?.open, id);
        deep = {kind: 'own detail', id, open: true};
    } else {
        assert.ok(legacy.length > 0, 'Legacy educational fragment available');
        const id = legacy.find(value => value.startsWith('self-check-')) || legacy[0];
        await page.goto(BASE + target.path + '#' + id, {waitUntil: 'networkidle'});
        assert.equal(await page.evaluate(() => decodeURIComponent(location.hash.slice(1))), id);
        assert.equal(await page.locator('[id="' + id + '"]').count(), 1);
        await content(page, target); deep = {kind: 'legacy visible basic/practice', id, preserved: true};
    }
    const previous = await page.locator('[data-theory-details]').evaluateAll(items => items.map(item => item.open));
    await page.emulateMedia({media: 'print'});
    await page.waitForFunction(() => [...document.querySelectorAll('[data-theory-details]')].every(item => item.open));
    for (const card of await page.locator('[data-theory-main] article.theory-item').all()) assert.ok(await card.isVisible(), 'Complete author basic available in print');
    await page.emulateMedia({media: 'screen'});
    await page.waitForFunction(previous => JSON.stringify([...document.querySelectorAll('[data-theory-details]')].map(item => item.open)) === JSON.stringify(previous), previous);
    return {reloadClosed: true, deep, printRestores: true, legacyCount: legacy.length};
}

async function httpProof(dir, label) {
    const before = read(path.join(dir, 'before-http.json'));
    const current = await capture();
    save(dir, label + '-http.json', current);
    for (const row of current.rows) {
        const old = before.rows.find(item => item.path === row.path); assert.ok(old);
        assert.deepEqual(row.meta, old.meta, 'Unchanged metadata ' + row.path); assert.equal(row.xRobots, old.xRobots);
        for (const id of old.legacyIds) assert.ok(row.legacyIds.includes(id), 'Legacy anchor ' + id);
        if (controls.includes(row.path)) {assert.equal(row.mainTextSha256, old.mainTextSha256); assert.equal(row.details, old.details);}
    }
    assert.deepEqual(current.sitemap, before.sitemap, 'Ordered sitemap unchanged');
    for (const target of allTargets) {
        const response = await fetch(BASE + target.path, {redirect: 'manual', signal: AbortSignal.timeout(30000), headers: {Accept: 'text/html', Connection: 'close'}});
        assert.equal(response.status, 200);
        const dom = new JSDOM(await response.text());
        try {fidelity(dom.window.document, target); exactAuthorSelfCheck(dom.window.document, target);} finally {dom.window.close();}
        assert.equal(current.rows.find(row => row.path === target.path).details, points(target));
    }
    return before;
}

async function run(dir, label) {
    assert.equal(path.basename(dir), 'seo-m30-local'); assert.match(label, /^[a-z0-9-]+$/); fs.mkdirSync(dir, {recursive: true});
    const baseline = await httpProof(dir, label);
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    let browser = null, contextCount = 0;
    // Bound Chromium process resources during this long local acceptance matrix.
    // A failed request still fails the run; this is not a retry or error filter.
    const freshContext = async options => {
        if (!browser || contextCount === 4) {
            if (browser) await browser.close();
            browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
            contextCount = 0;
        }
        contextCount++;
        return browser.newContext(options);
    };
    const report = {at: new Date().toISOString(), base: BASE, conditions: {guest: true, getOnly: true, chromiumContextsPerProcess: 4}, states: [], noJS: [], regressions: [], violations: [], pass: false};
    try {
        for (const viewport of [{width: 1440, height: 1000}, {width: 390, height: 844}]) for (const theme of ['light', 'dark']) for (const target of targets) {
            const context = await freshContext({viewport, colorScheme: theme}); const page = await context.newPage();
            const row = {path: target.path, group: target.group, viewport, theme, errors: [], httpErrors: [], failed: []}; report.states.push(row);
            try {
                const requests = await observe(page, row, report.violations);
                await page.goto(BASE + target.path, {waitUntil: 'networkidle'}); await page.waitForFunction(() => Boolean(window.Alpine));
                await page.evaluate(theme => {document.documentElement.classList.toggle('dark', theme === 'dark'); localStorage.setItem('theme', theme);}, theme);
                await content(page, target); row.initialFullBasic = true;
                row.details = await detailsInteraction(page, target, requests);
                row.practice = await practice(page, target); await bankProof(dir, target, row.practice);
                row.tokenAndManual = await tokenAndManualProof(page, target);
                row.punctuation = await negativePunctuation(page, target); row.overflow = await overflow(page);
                row.screenshots = await screenshots(page, dir, label, target, viewport, theme);
                row.navigation = await navigationAndPrint(page, target, baseline);
                clean(row); row.pass = true;
                console.log(JSON.stringify({path: target.path, width: viewport.width, theme, points: row.details.count, pass: true}));
            } catch (error) {row.failure = String(error.stack || error).slice(0, 2500); throw error;} finally {await context.close();}
        }
        for (const target of targets) {
            const context = await freshContext({javaScriptEnabled: false, viewport: {width: 1440, height: 1000}}); const page = await context.newPage();
            const row = {path: target.path, group: target.group, javaScriptEnabled: false, expectedDisabledScripts: [], errors: [], httpErrors: [], failed: []}; report.noJS.push(row);
            try {
                await observe(page, row, report.violations); await page.goto(BASE + target.path, {waitUntil: 'networkidle'});
                await content(page, target, true);
                if (target.group === 'M14') {assert.ok(await page.locator('[data-m30-self-check-no-js]').isVisible()); assert.ok(await page.locator('[data-m30-self-check-answers]').isVisible());}
                row.points = points(target); row.fullAuthorBasic = true; row.authorPracticeReadable = true; clean(row); row.pass = true;
                console.log(JSON.stringify({path: target.path, noJS: true, pass: true}));
            } catch (error) {row.failure = String(error.stack || error).slice(0, 2500); throw error;} finally {await context.close();}
        }
        for (const target of regressions) {
            const context = await freshContext({viewport: {width: 1440, height: 1000}, colorScheme: 'light'}); const page = await context.newPage();
            const row = {path: target.path, group: target.group, errors: [], httpErrors: [], failed: []}; report.regressions.push(row);
            try {
                const requests = await observe(page, row, report.violations); await page.goto(BASE + target.path, {waitUntil: 'networkidle'});
                await page.waitForFunction(() => Boolean(window.Alpine)); await content(page, target);
                row.details = await detailsInteraction(page, target, requests); row.practice = await practice(page, target); await bankProof(dir, target, row.practice);
                row.tokenAndManual = await tokenAndManualProof(page, target);
                row.overflow = await overflow(page); row.navigation = await navigationAndPrint(page, target, baseline);
                clean(row); row.pass = true; console.log(JSON.stringify({path: target.path, regression: target.group, points: row.details.count, pass: true}));
            } catch (error) {row.failure = String(error.stack || error).slice(0, 2500); throw error;} finally {await context.close();}
        }
        assert.deepEqual(report.violations, []); report.pass = true;
    } finally {if (browser) await browser.close(); save(dir, label + '-browser.json', report);}
    console.log(JSON.stringify({pass: report.pass, states: report.states.length, noJS: report.noJS.length, regressions: report.regressions.length,
        details: allTargets.map(target => ({group: target.group, slug: target.slug, count: points(target)}))}));
    return report;
}

if (require.main === module) {
    const [dir, label] = process.argv.slice(2);
    run(dir, label).catch(error => {console.error(String(error.message).replace(/https?:\/\/\S+/g, publicUrl)); process.exitCode = 1;});
}
module.exports = {run, targets, regressions, exactAuthorSelfCheck, content, tokenAndManualProof, negativePunctuation};
