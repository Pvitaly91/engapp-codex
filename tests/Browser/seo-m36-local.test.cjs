'use strict';
// Pure contracts: no HTTP, Laravel, browser or database.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require('jsdom');
const diagnostic = require('../../tools/diagnostics/seo-m36-local.cjs');
const capture = require('../../tools/diagnostics/capture-m36-http.cjs');
const root = path.resolve(__dirname, '../..');

test('Exact M36 C1/C1/C2 inventory, 27 M27–M35 regressions, finite guest HTTP controls', () => {
    assert.equal(capture.BASE, 'http://gramlyze.loc');
    assert.equal(capture.routes.length, 39); assert.equal(new Set(capture.routes).size, 39);
    assert.equal(diagnostic.targets.length, 3); assert.equal(diagnostic.regressions.length, 27);
    assert.deepEqual(diagnostic.targets.map(diagnostic.points), [0, 0, 0]);
    assert.deepEqual(diagnostic.targets.map(t => JSON.parse(t.after.page.blocks[0].body).level), ['C1', 'C1', 'C2']);
    for (const group of ['M27', 'M28', 'M29', 'M14', 'M31', 'M32', 'M33', 'M34', 'M35']) {
        const targets = diagnostic.regressions.filter(t => t.group === group); assert.equal(targets.length, 3);
        const expected = {M27: 13, M31: 2, M32: 1, M33: 2, M34: 2}[group] || 0;
        assert.equal(targets.reduce((n, t) => n + diagnostic.points(t), 0), expected);
    }
    for (const route of capture.routes) capture.assertRoute(route);
    assert.throws(() => capture.assertRoute('https://gramlyze.com/theory'));
    assert.throws(() => capture.assertRoute('//gramlyze.ub/theory'));
});

test('Each original case/key occurs once, including all subdecisions and no-JS literal keys', () => {
    for (const target of diagnostic.targets) {
        const data = JSON.parse(target.after.page.blocks.find(b => b.type === 'practice-set').body);
        const author = data.author_self_check;
        const html = `<div data-m36-self-check-intro>${author.intro}</div>`
            + ['selects', 'choices', 'inputs'].flatMap(g => data[g].map(item =>
                `<div data-m36-author-prompt="${item.source_index}">${item.context}</div>`)).join('')
            + `<ol data-m36-self-check-answers>${author.answers.map(a => `<li>${a}</li>`).join('')}</ol>`
            + `<div data-m36-self-check-no-js><ol>${author.prompts.map(p => `<li>${p}</li>`).join('')}</ol></div>`;
        const dom = new JSDOM(html); diagnostic.exactAuthorSelfCheck(dom.window.document, target, true);
        dom.window.document.querySelector('[data-m36-self-check-answers] li').remove();
        assert.throws(() => diagnostic.exactAuthorSelfCheck(dom.window.document, target)); dom.window.close();
        const moved = new JSDOM(html); moved.window.document.querySelector('[data-m36-author-prompt]').textContent = 'Neighbouring case';
        assert.throws(() => diagnostic.exactAuthorSelfCheck(moved.window.document, target)); moved.window.close();
        const duplicate = new JSDOM(html); duplicate.window.document.querySelector('[data-m36-author-prompt]').setAttribute('data-m36-author-prompt', '6');
        assert.throws(() => diagnostic.exactAuthorSelfCheck(duplicate.window.document, target)); duplicate.window.close();
    }
});

test('Full visible core, facts/inference/request/advice/performance and tables; short outline rejected', async () => {
    for (const target of diagnostic.targets) {
        const expected = diagnostic.identities[target.slug];
        const parts = target.plans.flatMap(p => p.points.map(point => point.basic));
        for (const b of target.after.page.blocks.filter(b => b.type === 'comparison-table')) {
            const table = JSON.parse(b.body); parts.push(table.intro, table.outro, ...table.headers, ...table.rows.flatMap(r => r.cells));
        }
        const dom = new JSDOM(`<h1>${expected.h1}</h1><main data-theory-main>${parts.join(' ')}</main>`);
        const page = {locator: selector => ({
            allTextContents: async () => [...dom.window.document.querySelectorAll(selector)].map(n => n.textContent),
            evaluate: async callback => callback(dom.window.document.querySelector(selector)),
        })};
        await diagnostic.identityAndVisibleCore(page, target);
        const wrong = structuredClone(target); wrong.ancestry = ['basic-grammar'];
        await assert.rejects(() => diagnostic.identityAndVisibleCore(page, wrong));
        dom.window.document.querySelector('main').textContent = 'Short outline with core facts hidden';
        await assert.rejects(() => diagnostic.identityAndVisibleCore(page, target)); dom.window.close();
    }
});

test('Local acceptance is bounded, read-only, real-page, exact-bank and no silent retry', () => {
    for (const name of ['observe', 'clean', 'bankProof', 'navigationAndPrint']) assert.equal(typeof diagnostic[name], 'function');
    const source = fs.readFileSync(path.join(root, 'tools/diagnostics/seo-m36-local.cjs'), 'utf8');
    assert.match(source, /contextCount === 4/); assert.match(source, /fullPage: false/);
    assert.doesNotMatch(source, /fullPage: true|page\.setContent|route\.fulfill|retry\s*[:=(]/);
    assert.match(source, /for \(const target of allTargets\)/); assert.match(source, /bankProof\(dir, target/);
    assert.match(source, /negativeSemantics\(page, target\)/); assert.match(source, /table-right\.png/);
    assert.match(source, /m36_token_groups/);
});

test('Actual semantic negatives alter accepted answers and all localized scores are exact', () => {
    for (const target of diagnostic.targets) {
        const data = JSON.parse(target.after.page.blocks.find(b => b.type === 'practice-set').body);
        assert.deepEqual(['selects', 'choices', 'inputs'].map(g => data[g].length), [2, 2, 2]);
        const fixtures = diagnostic.semanticFixtures(data); assert.ok(fixtures.length >= 7);
        for (const [i, invalid] of fixtures) assert.notEqual(invalid, data.inputs[i].answer);
    }
    diagnostic.assertScore('Результат: 2 з 2', 2, 2);
    assert.throws(() => diagnostic.assertScore('Результат: 1 з 2', 2, 2));
});
