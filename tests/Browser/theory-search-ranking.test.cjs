const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');

const helper = fs.readFileSync('public/js/theory-search.js', 'utf8');
const inline = path => fs.readFileSync(path, 'utf8').match(/<script>([\s\S]*?)<\/script>/)[1];
const setup = html => {
    const dom = new JSDOM(html, {runScripts: 'outside-only', pretendToBeVisual: true});
    dom.window.eval(helper);
    return dom;
};
const fill = (window, selector, value) => {
    const input = window.document.querySelector(selector);
    input.value = value;
    input.dispatchEvent(new window.Event('input', {bubbles: true}));
};
const texts = (document, selector) => Array.from(document.querySelectorAll(selector), node => node.textContent.trim());

test('exact title outranks prefix, word-boundary phrase, substring and separated words', () => {
    const dom = setup('');
    const {rank} = dom.window.GramlyzeTheorySearch;
    const titles = ['Present Perfect', 'Present Perfect Continuous', 'Using Present Perfect',
        'Xpresent perfect', 'Perfect and present'];
    const scores = titles.map(title => rank(title, '  PRESENT   perfect  '));
    scores.slice(1).forEach((score, index) => assert.ok(scores[index] > score));
    assert.equal(rank('Past Simple', 'present perfect'), 0);
    assert.equal(rank('Present Simple', 'present perfect'), 0, 'all search terms are required');
    assert.equal(rank('Present Perfect', '  '), 0);
    dom.window.close();
});

test('partial and Ukrainian queries prioritize close title matches without losing case-insensitivity', () => {
    const dom = setup('');
    const {rank} = dom.window.GramlyzeTheorySearch;
    assert.ok(rank('Present Simple', 'pres') > rank('Time Expressions', 'pres'));
    assert.ok(rank('Present Simple', 'pres') > rank('Present Perfect Continuous', 'pres'));
    assert.ok(rank('Часи', 'ЧАСИ') > rank('Майбутні часи', 'часи'));
    assert.ok(rank('Майбутні часи', 'часи') > rank('Підчаси', 'часи'));
    dom.window.close();
});

const card = (title, children = []) => `<article data-theory-category-card data-theory-search-text="${title}">
    <h3 data-theory-highlight>${title}</h3><div>${children.map(child =>
        `<a href="#${child}" data-theory-category-child data-theory-search-text="${child}"><span data-theory-highlight>${child}</span></a>`).join('')}</div></article>`;
const indexFixture = () => setup(`<div data-theory-category-search>
    <input data-theory-category-search-input><button data-theory-category-search-clear>Clear</button>
    <span data-theory-category-search-count></span></div><div data-theory-category-grid>
    ${card('Expressions', ['Time Expressions'])}
    ${card('Часи', ['Past Simple', 'Present Perfect Continuous', 'Present Simple', 'Present Perfect', 'Past Perfect'])}
    ${card('Present Perfect')}${card('Іменники')}</div><div data-theory-category-search-empty></div>`);
const startIndex = window => {
    window.eval(inline('resources/views/theory/index.blade.php'));
    window.document.dispatchEvent(new window.Event('DOMContentLoaded'));
};

test('catalog ranks both category cards and child links, preserving nonmatching siblings below matches', () => {
    const dom = indexFixture();
    const {window} = dom;
    startIndex(window);
    fill(window, 'input', 'pres');
    const visible = '[data-theory-category-card]:not(.hidden)';
    assert.deepEqual(texts(window.document, `${visible} h3`), ['Часи', 'Present Perfect', 'Expressions']);
    assert.deepEqual(texts(window.document, '[data-theory-search-text="Часи"] a'),
        ['Present Simple', 'Present Perfect', 'Present Perfect Continuous', 'Past Simple', 'Past Perfect']);
    assert.equal(window.document.querySelector('[data-theory-category-search-count]').textContent, '3 / 4');
    assert.equal(window.document.querySelector('[data-theory-search-text="Present Simple"] mark').textContent, 'Pres');
    fill(window, 'input', 'Present Perfect');
    assert.deepEqual(texts(window.document, '[data-theory-search-text="Часи"] a').slice(0, 2),
        ['Present Perfect', 'Present Perfect Continuous']);
    dom.window.close();
});

test('clearing or replacing searches restores initial DOM order, highlights and empty state', () => {
    const dom = indexFixture();
    const {window} = dom;
    startIndex(window);
    const original = texts(window.document, '[data-theory-category-card] h3');
    const children = texts(window.document, '[data-theory-category-child]');
    fill(window, 'input', 'pres');
    fill(window, 'input', 'not-a-real-topic');
    assert.equal(window.document.querySelector('[data-theory-category-search-count]').textContent, '0 / 4');
    assert.equal(window.document.querySelector('[data-theory-category-search-empty]').classList.contains('hidden'), false);
    window.document.querySelector('button').click();
    assert.deepEqual(texts(window.document, '[data-theory-category-card] h3'), original);
    assert.deepEqual(texts(window.document, '[data-theory-category-child]'), children);
    assert.equal(window.document.querySelectorAll('mark').length, 0);
    assert.equal(window.document.querySelector('[data-theory-category-search-count]').textContent, '4 / 4');
    dom.window.close();
});

test('words must match one title, not different child titles accidentally joined together', () => {
    const dom = indexFixture();
    startIndex(dom.window);
    fill(dom.window, 'input', 'simple present');
    assert.equal(dom.window.document.querySelector('[data-theory-category-search-count]').textContent, '1 / 4');
    assert.equal(texts(dom.window.document, '[data-theory-search-text="Часи"] a')[0], 'Present Simple');
    fill(dom.window, 'input', 'continuous past');
    assert.equal(dom.window.document.querySelector('[data-theory-category-search-count]').textContent, '0 / 4');
    dom.window.close();
});

const branch = (title, children = '', top = false) => `<div data-theory-sidebar-node ${top ? 'data-theory-sidebar-top-node' : ''}>
    <a href="#"><span data-theory-sidebar-highlight>${title}</span></a><div>${children}</div></div>`;

test('sidebar ranks nested branches by their best title, keeps ancestors and restores the tree', () => {
    const dom = setup(`<div data-theory-sidebar><div data-theory-sidebar-search>
        <input data-theory-sidebar-search-input><button data-theory-sidebar-search-clear>Clear</button>
        <span data-theory-sidebar-search-count></span><div data-theory-sidebar-search-empty></div></div>
        <div data-theory-sidebar-scroll>${branch('Using Present Perfect', '', true)}
        ${branch('Часи', branch('Past Simple') + branch('Present Perfect Continuous') +
            branch('Present Perfect', branch('Present Perfect: Forms')), true)}
        ${branch('Nouns', '', true)}</div></div>`);
    const {window} = dom;
    window.Alpine = {mutateDom: callback => callback()};
    window.eval(inline('resources/views/theory/partials/tree-nav.blade.php'));
    window.initTheorySidebarSearch();
    const original = texts(window.document, '[data-theory-sidebar-highlight]');
    fill(window, 'input', 'Present Perfect');
    assert.deepEqual(texts(window.document, '[data-theory-sidebar-top-node] > a'),
        ['Часи', 'Using Present Perfect', 'Nouns']);
    const tenses = window.document.querySelector('[data-theory-sidebar-top-node]');
    assert.deepEqual(Array.from(tenses.lastElementChild.children, node => node.firstElementChild.textContent.trim()),
        ['Present Perfect', 'Present Perfect Continuous', 'Past Simple']);
    assert.equal(tenses.classList.contains('hidden'), false);
    assert.equal(window.document.querySelector('[data-theory-sidebar-search-count]').textContent, '2 / 3');
    window.document.querySelector('button').click();
    assert.deepEqual(texts(window.document, '[data-theory-sidebar-highlight]'), original);
    assert.equal(window.document.querySelectorAll('[data-theory-sidebar-node].hidden').length, 0);
    dom.window.close();
});
