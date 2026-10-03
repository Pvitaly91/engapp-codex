'use strict';
// Pure diagnostic-contract checks. These tests make no HTTP/browser/DB requests.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require('jsdom');
const diagnostic = require('../../tools/diagnostics/seo-m32-local.cjs');
const capture = require('../../tools/diagnostics/capture-m32-http.cjs');
const {guard} = require('../../tools/diagnostics/seo-m28-local.cjs');
const root = path.resolve(__dirname, '../..');

test('Localized visible score retains its label and exact correct/total counts', () => {
    diagnostic.assertScore('Результат: 3 з 3', 3, 3);
    diagnostic.assertScore('Результат: 5 із 6', 5, 6);
    assert.throws(() => diagnostic.assertScore('Результат: 2 з 3', 3, 3));
    assert.throws(() => diagnostic.assertScore('Результат: 3 з 6', 3, 3));
    assert.throws(() => diagnostic.assertScore('3 з 3', 3, 3));
});

test('Variable groups preserve six source cases and semantic negatives are actual changed answers', () => {
    const sizes = [[0, 3, 3], [0, 0, 6], [0, 3, 3]];
    for (const [index, target] of diagnostic.targets.entries()) {
        const data = JSON.parse(target.after.page.blocks.find(block => block.type === 'practice-set').body);
        assert.deepEqual(['selects', 'choices', 'inputs'].map(name => data[name].length), sizes[index]);
        assert.deepEqual(['selects', 'choices', 'inputs'].flatMap(name => data[name].map(item => item.source_index)).sort((a, b) => a - b), [1, 2, 3, 4, 5, 6]);
        const negative = diagnostic.semanticFixtures(data);
        assert.ok(negative.length >= 4);
        for (const [inputIndex, value] of negative) assert.notEqual(value, data.inputs[inputIndex].answer);
    }
});

test('Exact .loc inventory: three M32, fifteen M27–M31 browser regressions, all M26 HTTP controls', () => {
    assert.equal(capture.BASE, 'http://gramlyze.loc');
    assert.equal(capture.routes.length, 27);
    assert.equal(new Set(capture.routes).size, 27);
    assert.equal(capture.regressions, undefined, 'Internal source inventory is not an externally mutable API.');
    assert.equal(diagnostic.targets.length, 3);
    assert.equal(diagnostic.regressions.length, 15);
    assert.deepEqual(diagnostic.targets.map(diagnostic.points), [0, 1, 0]);
    for (const group of ['M27', 'M28', 'M29', 'M14', 'M31']) {
        const targets = diagnostic.regressions.filter(target => target.group === group);
        assert.equal(targets.length, 3);
        assert.equal(targets.reduce((sum, target) => sum + diagnostic.points(target), 0), group === 'M27' ? 13 : (group === 'M31' ? 2 : 0));
    }
    for (const route of capture.routes) {
        capture.assertRoute(route);
        assert.equal(new URL(capture.BASE + route).origin, capture.BASE);
    }
    assert.throws(() => capture.assertRoute('https://gramlyze.com/theory'));
    assert.throws(() => capture.assertRoute('//gramlyze.ub/theory'));
});

test('Read-only browser policy refuses stateful requests and all production origins', async () => {
    let intercept;
    const row = {errors: [], httpErrors: [], failed: []}, violations = [];
    const page = {route: async (_, callback) => {intercept = callback;}, on: () => {}};
    const requests = await guard(page, row, violations);
    async function request(method, url) {
        let action;
        await intercept({request: () => ({method: () => method, url: () => url}),
            abort: async () => {action = 'abort';}, continue: async () => {action = 'continue';}});
        return action;
    }
    assert.equal(await request('GET', 'http://gramlyze.loc/theory'), 'continue');
    assert.equal(await request('POST', 'http://gramlyze.loc/api/state'), 'abort');
    assert.equal(await request('GET', 'https://gramlyze.com/theory'), 'abort');
    assert.equal(await request('GET', 'https://gramlyze.ub/theory'), 'abort');
    assert.equal(await request('GET', 'https://fonts.googleapis.com/css2?family=Manrope'), 'continue');
    assert.equal(violations.length, 3); assert.equal(requests.length, 2);
});

function evidence() {
    return {base: capture.BASE, sitemap: {count: 554, sha256: 'ordered-exact'}, rows: capture.routes.map(route => ({
        path: route, meta: {title: route, h1: [route], description: 'unchanged', canonical: 'canonical', robots: null,
            ogTitle: route, ogDescription: 'unchanged', twitterTitle: route, twitterDescription: 'unchanged'},
        xRobots: 'noindex', contentType: 'text/html', mainTextSha256: 'core', details: 0, legacyIds: ['lesson-block-3'],
    }))};
}
test('HTTP postconditions reject OG/Twitter/robots/control/anchor/sitemap regressions', () => {
    const before = evidence(); capture.compare(before, structuredClone(before));
    for (const property of ['title', 'h1', 'description', 'canonical', 'robots', 'ogTitle', 'ogDescription', 'twitterTitle', 'twitterDescription']) {
        const after = structuredClone(before); after.rows[0].meta[property] = 'changed';
        assert.throws(() => capture.compare(before, after), property);
    }
    for (const property of ['xRobots', 'contentType']) {
        const after = structuredClone(before); after.rows[0][property] = 'changed';
        assert.throws(() => capture.compare(before, after), property);
    }
    const changedControl = structuredClone(before);
    changedControl.rows.find(row => capture.controls.includes(row.path)).mainTextSha256 = 'changed';
    assert.throws(() => capture.compare(before, changedControl));
    const anchor = structuredClone(before); anchor.rows[0].legacyIds = [];
    assert.throws(() => capture.compare(before, anchor));
    const sitemap = structuredClone(before); sitemap.sitemap.sha256 = 'changed-order';
    assert.throws(() => capture.compare(before, sitemap));
    const inventory = structuredClone(before); inventory.rows.reverse();
    assert.throws(() => capture.compare(before, inventory));
});

function authorFixture(target) {
    const data = JSON.parse(target.after.page.blocks.find(block => block.type === 'practice-set').body);
    const author = data.author_self_check;
    return `<div data-m32-self-check-intro>${author.intro}</div>`
        + ['selects', 'choices', 'inputs'].flatMap(group => data[group].map(item =>
            `<div data-m32-author-prompt="${item.source_index}">${item.context}</div>`)).join('')
        + `<ol data-m32-self-check-answers>${author.answers.map(answer => `<li>${answer}</li>`).join('')}</ol>`
        + `<div data-m32-self-check-no-js><ol>${author.prompts.map(prompt => `<li>${prompt}</li>`).join('')}</ol></div>`;
}
test('Exact six prompts and six keys, normal and no-JS; lost/moved/duplicate source rejected', () => {
    for (const target of diagnostic.targets) {
        const dom = new JSDOM(authorFixture(target));
        diagnostic.exactAuthorSelfCheck(dom.window.document, target, true);
        const lost = new JSDOM(authorFixture(target)); lost.window.document.querySelector('[data-m32-self-check-answers] li').remove();
        assert.throws(() => diagnostic.exactAuthorSelfCheck(lost.window.document, target)); lost.window.close();
        const duplicate = new JSDOM(authorFixture(target));
        duplicate.window.document.querySelector('[data-m32-author-prompt]').setAttribute('data-m32-author-prompt', '6');
        assert.throws(() => diagnostic.exactAuthorSelfCheck(duplicate.window.document, target)); duplicate.window.close();
        const wrongOwner = new JSDOM(authorFixture(target));
        wrongOwner.window.document.querySelector('[data-m32-author-prompt]').textContent = 'Another question';
        assert.throws(() => diagnostic.exactAuthorSelfCheck(wrongOwner.window.document, target)); wrongOwner.window.close();
        const noJS = new JSDOM(authorFixture(target)); noJS.window.document.querySelector('[data-m32-self-check-no-js] li').remove();
        assert.throws(() => diagnostic.exactAuthorSelfCheck(noJS.window.document, target, true)); noJS.window.close(); dom.window.close();
    }
});

test('Bounded real acceptance contains no silent retries, production requests or full-page screenshots', () => {
    const source = fs.readFileSync(path.join(root, 'tools/diagnostics/seo-m32-local.cjs'), 'utf8');
    const http = fs.readFileSync(path.join(root, 'tools/diagnostics/capture-m32-http.cjs'), 'utf8');
    assert.match(source, /contextCount === 4/);
    assert.match(source, /fullPage: false/);
    assert.doesNotMatch(source, /fullPage: true|page\.setContent|route\.fulfill|retry\s*[:=(]/);
    assert.match(source, /for \(const target of allTargets\)/, 'All regressions included in actual no-JS matrix');
    assert.match(source, /negativeSemantics\(page, target\)/);
    assert.match(http, /Connection: 'close'/);
    assert.match(http, /redirect: 'manual'/);
    assert.match(http, /flag: 'wx'/, 'Private evidence must not overwrite old runs');
});
