'use strict';
// Pure contracts: no HTTP, Laravel, browser or database.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require('jsdom');
const diagnostic = require('../../tools/diagnostics/seo-m39-local.cjs');
const capture = require('../../tools/diagnostics/capture-m39-http.cjs');
const root = path.resolve(__dirname, '../..');

test('Exact M39 C2/C1/C2 inventory, 36 M27–M38 regressions, finite guest HTTP controls', () => {
    assert.equal(capture.BASE, 'http://gramlyze.loc');
    assert.equal(capture.routes.length, 49); assert.equal(new Set(capture.routes).size, 49);
    assert.equal(diagnostic.targets.length, 3); assert.equal(diagnostic.regressions.length, 36);
    assert.deepEqual(diagnostic.targets.map(diagnostic.points), [0, 0, 0]);
    assert.deepEqual(diagnostic.targets.map(t => JSON.parse(t.after.page.blocks[0].body).level), ['C2', 'C1', 'C2']);
    for (const group of ['M27', 'M28', 'M29', 'M14', 'M31', 'M32', 'M33', 'M34', 'M35', 'M36', 'M37', 'M38']) {
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
        const html = `<div data-m39-self-check-intro>${author.intro}</div>`
            + ['selects', 'choices', 'inputs'].flatMap(g => data[g].map(item =>
                `<div data-m39-author-prompt="${item.source_index}">${item.context}</div>`)).join('')
            + `<ol data-m39-self-check-answers>${author.answers.map(a => `<li>${a}</li>`).join('')}</ol>`
            + `<div data-m39-self-check-no-js><ol>${author.prompts.map(p => `<li>${p}</li>`).join('')}</ol></div>`;
        const dom = new JSDOM(html); diagnostic.exactAuthorSelfCheck(dom.window.document, target, true);
        dom.window.document.querySelector('[data-m39-self-check-answers] li').remove();
        assert.throws(() => diagnostic.exactAuthorSelfCheck(dom.window.document, target)); dom.window.close();
        const moved = new JSDOM(html); moved.window.document.querySelector('[data-m39-author-prompt]').textContent = 'Neighbouring case';
        assert.throws(() => diagnostic.exactAuthorSelfCheck(moved.window.document, target)); moved.window.close();
        const duplicate = new JSDOM(html); duplicate.window.document.querySelector('[data-m39-author-prompt]').setAttribute('data-m39-author-prompt', '6');
        assert.throws(() => diagnostic.exactAuthorSelfCheck(duplicate.window.document, target)); duplicate.window.close();
    }
});

test('Full visible core, nominal status/mixed time/reporting/negation scope and tables; short outline rejected', async () => {
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
    const source = fs.readFileSync(path.join(root, 'tools/diagnostics/seo-m39-local.cjs'), 'utf8');
    assert.match(source, /contextCount === 4/); assert.match(source, /fullPage: false/);
    assert.doesNotMatch(source, /fullPage: true|page\.setContent|route\.fulfill|retry\s*[:=(]/);
    assert.match(source, /for \(const target of allTargets\)/); assert.match(source, /bankProof\(dir, target/);
    assert.match(source, /negativeSemantics\(page, target\)/); assert.match(source, /table-right\.png/);
    assert.match(source, /m39_token_groups/);
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


test('Immutable M23 author body and casing are independent of generated presentation', () => {
    const master = JSON.parse(fs.readFileSync(path.join(root, 'docs/content/m23-authored-content.v1.json'), 'utf8'));
    const before = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m39-m23-authored-revision-before.json'), 'utf8'));
    for (const [index, target] of diagnostic.targets.entries()) {
        const lesson = master.lessons[index], original = before.targets[index].before;
        assert.equal(original.page.blocks[1].body, lesson.body_html);
        assert.equal(original.page.title, lesson.preserve_page_title);
        assert.equal(diagnostic.identities[target.slug].h1, lesson.preserve_subtitle_strong);
        assert.equal(JSON.parse(original.page.blocks[0].body).intro, lesson.hero.intro);
        assert.deepEqual(master.content_policy, {rewrite: false, summarise: false, translate_again: false,
            add_examples: false, add_exercises: false, production_write: false, mixed_question_bank_write: false,
            allow_technical_changes: 'JSON/HTML encoding and presentation attributes only; learner-visible text, section/exercise/answer order, punctuation, numbers and modality must be preserved. Keep this source immutable.'});
    }
});
test('M39 contains exactly two original tables and never invents a C2 Mixed comparison table', () => {
    assert.deepEqual(diagnostic.targets.map(t => t.after.page.blocks.filter(b => b.type === 'comparison-table').length), [1, 1, 0]);
    for (const target of diagnostic.targets) for (const block of target.after.page.blocks.filter(b => b.type === 'comparison-table')) {
        const table = JSON.parse(block.body); assert.equal(table.table_min_width, 720);
        assert.deepEqual(table.column_min_widths, [240, 240, 240]); assert.equal(table.headers.length, 3); assert.equal(table.rows.length, 3);
    }
});
test('Nominal course-copy check is GET/server-only and never bypasses or claims visible gated content', () => {
    assert.equal(capture.coursePath, '/courses/english-grammar-theory/lesson/formal-english/nominal-style-and-information-density');
    assert.ok(capture.routes.includes(capture.coursePath));
    const source = fs.readFileSync(path.join(root, 'tools/diagnostics/capture-m39-http.cjs'), 'utf8');
    assert.match(source, /Server properties only; no browser gate bypass or visual acceptance claim/);
    assert.match(source, /\[data-m39-self-check-answers\] li/);
    assert.doesNotMatch(source, /page\.evaluate|page\.setContent|route\.fulfill|setExtraHTTPHeaders|storageState|Authorization/);
});

test('Course server-only author-key count handles the real container-around-OL markup', () => {
    const target = diagnostic.targets[0];
    const author = JSON.parse(target.after.page.blocks.find(b => b.type === 'practice-set').body).author_self_check;
    const dom = new JSDOM('<section data-theory-lesson-content><div data-m39-self-check-answers><ol>'
        + author.answers.map(answer => '<li>' + answer + '</li>').join('') + '</ol></div></section>');
    const source = fs.readFileSync(path.join(root, 'tools/diagnostics/capture-m39-http.cjs'), 'utf8');
    const selector = source.match(/sourceKeys: \[\.\.\.doc\.querySelectorAll\('([^']+)'\)\]/)[1];
    assert.equal(dom.window.document.querySelectorAll(selector).length, 6);
    dom.window.close();
});
