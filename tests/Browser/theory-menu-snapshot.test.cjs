const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');

const scripts = ['public/js/theory-search.js', 'public/js/theory-menu.js'].map(file => fs.readFileSync(file, 'utf8'));
const page = (id, title) => `<a data-theory-menu-page="${id}" data-theory-sidebar-node href="/theory/test/lesson-${id}">
    <span data-theory-sidebar-highlight>${title}</span></a>`;
const branch = (id, title, children, top = false) => `<div data-theory-menu-category="${id}" data-theory-sidebar-node ${top ? 'data-theory-sidebar-top-node' : ''}>
    <div><a data-theory-menu-category-link href="/theory/category-${id}"><span data-theory-sidebar-highlight>${title}</span></a>
        <button type="button" data-theory-menu-toggle aria-expanded="false"></button></div>
    <div data-theory-menu-children hidden>${children}</div></div>`;
const fragment = variant => `<div data-theory-menu-fragment="${variant}">
    <div data-theory-sidebar-search><input id="menu-search-${variant}" data-theory-sidebar-search-input>
        <button type="button" data-theory-sidebar-search-clear>Clear</button><span data-theory-sidebar-search-count>2 / 2</span>
        <div data-theory-sidebar-search-empty class="hidden"></div></div>
    <div data-theory-sidebar-scroll>${branch(1, 'Tenses', branch(2, 'Perfect', page(23, 'Present Perfect')) + page(24, 'Past Simple'), true)}
        ${branch(3, 'Clauses', page(25, 'Relative Clauses'), true)}</div></div>`;

function fixture(t, desktop = fragment('desktop')) {
    const dom = new JSDOM(`
        <section id="desktop" data-theory-sidebar data-theory-menu-context="desktop" data-theory-menu-current-page="23" data-theory-menu-selected-category="2">${desktop}</section>
        <section id="mobile" data-theory-sidebar data-theory-menu-context="mobile" data-theory-menu-selected-category="3"></section>`, {
        runScripts: 'outside-only', pretendToBeVisual: true,
    });
    t.after(() => dom.window.close());
    const {window} = dom, {document} = window;
    let observers = 0;
    const NativeObserver = window.MutationObserver;
    window.MutationObserver = class extends NativeObserver {
        constructor(callback) {super(callback); observers++;}
    };
    window.Alpine = {initTree: () => assert.fail('cached HTML must not be initialized by Alpine'),
        mutateDom: fn => fn()};
    window.fetch = () => assert.fail('desktop initialization must not fetch the tree');
    const pendingFrames = [];
    window.requestAnimationFrame = callback => pendingFrames.push(callback);
    scripts.forEach(source => window.eval(source));
    const desktopRoot = document.getElementById('desktop');
    const mobileRoot = document.getElementById('mobile');
    window.mountTheoryMenu(desktopRoot);
    return {window, document, desktopRoot, mobileRoot, observers: () => observers,
        frames: () => {while (pendingFrames.length) pendingFrames.shift()();}};
}

function inputValue(window, root, value) {
    const input = root.querySelector('[data-theory-sidebar-search-input]');
    input.value = value;
    input.dispatchEvent(new window.Event('input', {bubbles: true}));
}

test('ready desktop fragments open only active parent branches, with one aria-current and no fetch', t => {
    const {desktopRoot, document, frames} = fixture(t);
    const activePage = desktopRoot.querySelector('[aria-current="page"]');
    assert.equal(activePage.dataset.theoryMenuPage, '23');
    assert.equal(desktopRoot.querySelectorAll('[aria-current]').length, 1);
    assert.equal(desktopRoot.querySelector('[data-theory-nav-active="true"]').closest('[data-theory-menu-category]').dataset.theoryMenuCategory, '2');
    assert.deepEqual(Array.from(desktopRoot.querySelectorAll('[data-theory-menu-active-branch]'), category => category.dataset.theoryMenuCategory), ['1', '2']);
    assert.deepEqual(Array.from(desktopRoot.querySelectorAll('[data-theory-menu-toggle]'), button => button.getAttribute('aria-expanded')), ['true', 'true', 'false']);
    assert.deepEqual(Array.from(desktopRoot.querySelectorAll('[data-theory-menu-children]'), el => el.hidden), [false, false, true]);
    const scroll = desktopRoot.querySelector('[data-theory-sidebar-scroll]');
    scroll.getClientRects = activePage.getClientRects = () => [{}];
    scroll.getBoundingClientRect = () => ({top: 35});
    activePage.getBoundingClientRect = () => ({top: 140});
    scroll.scrollTo = options => {scroll.scrollTop = options.top;};
    frames();
    assert.equal(scroll.scrollTop, 97);
    assert.equal(document.querySelectorAll('[data-theory-menu-fragment]').length, 1);
});

test('repeated init / BFCache preserve closed branches and only initialize search once per fragment', t => {
    const {window, desktopRoot, mobileRoot, observers} = fixture(t);
    const toggle = desktopRoot.querySelector('[data-theory-menu-toggle]');
    toggle.click();
    assert.equal(toggle.getAttribute('aria-expanded'), 'false');
    window.dispatchEvent(new window.PageTransitionEvent('pageshow', {persisted: true}));
    window.mountTheoryMenu(desktopRoot);
    assert.equal(toggle.getAttribute('aria-expanded'), 'false');
    toggle.click();
    assert.equal(toggle.getAttribute('aria-expanded'), 'true', 'one click cannot fire two handlers');
    assert.equal(observers(), 1);

    mobileRoot.innerHTML = fragment('mobile');
    window.showTheoryMenu(mobileRoot);
    window.showTheoryMenu(mobileRoot);
    assert.equal(observers(), 2);
    assert.equal(mobileRoot.querySelector('[aria-current="page"]').getAttribute('href'), '/theory/category-3');
    assert.equal(mobileRoot.querySelectorAll('[data-theory-menu-active-branch]').length, 1);
    assert.equal(mobileRoot.querySelectorAll('[data-theory-nav-current-page]').length, 0);
    assert.notEqual(mobileRoot.querySelector('input').id, desktopRoot.querySelector('input').id);
});

test('independent desktop and mobile searches preserve collapsed branches after clearing', t => {
    const {window, desktopRoot, mobileRoot} = fixture(t);
    mobileRoot.innerHTML = fragment('mobile');
    window.showTheoryMenu(mobileRoot);
    const toggle = desktopRoot.querySelector('[data-theory-menu-toggle]');
    toggle.click();
    inputValue(window, desktopRoot, 'Present Perfect');
    assert.equal(desktopRoot.querySelector('[data-theory-sidebar-search-count]').textContent, '1 / 2');
    assert.equal(desktopRoot.querySelector('mark').textContent, 'Present Perfect');
    assert.equal(mobileRoot.querySelector('[data-theory-sidebar-search-count]').textContent, '2 / 2');
    desktopRoot.querySelector('[data-theory-sidebar-search-clear]').click();
    assert.equal(desktopRoot.querySelector('[data-theory-sidebar-search-count]').textContent, '2 / 2');
    assert.equal(toggle.getAttribute('aria-expanded'), 'false');
    assert.equal(desktopRoot.querySelectorAll('mark').length, 0);
});

test('fallback contains a working link but does not initialize a widget or require a request', t => {
    const {desktopRoot, observers} = fixture(t, '<p data-theory-menu-fallback><a href="/theory">All topics</a></p>');
    assert.equal(desktopRoot.querySelector('[data-theory-menu-fallback] a').getAttribute('href'), '/theory');
    assert.equal(observers(), 0);
});

test('mobile loads the pinned URL once and retains the same mounted DOM on reopening', async t => {
    const {window, mobileRoot, observers} = fixture(t);
    mobileRoot.dataset.theoryMenuUrl = '/theory/navigation?revision=' + 'd'.repeat(64);
    const content = mobileRoot.appendChild(window.document.createElement('div'));
    const urls = [];
    window.fetch = async url => {
        urls.push(url);
        return {ok: true, text: async () => fragment('mobile')};
    };
    const pending = window.loadTheoryMenu(mobileRoot, content);
    assert.equal(window.loadTheoryMenu(mobileRoot, content), pending);
    await pending;
    window.showTheoryMenu(mobileRoot);
    const previous = content.firstElementChild;
    await window.loadTheoryMenu(mobileRoot, content);
    window.showTheoryMenu(mobileRoot);
    assert.deepEqual(urls, [mobileRoot.dataset.theoryMenuUrl]);
    assert.equal(content.firstElementChild, previous);
    assert.equal(observers(), 2);
});

test('a failed mobile fetch does not replace any content or cache the failure', async t => {
    const {window, mobileRoot, observers} = fixture(t);
    mobileRoot.dataset.theoryMenuUrl = '/theory/navigation?revision=' + 'c'.repeat(64);
    const content = mobileRoot.appendChild(window.document.createElement('div'));
    content.innerHTML = '<p data-theory-menu-fallback><a href="/theory">All topics</a></p>';
    let requests = 0;
    window.fetch = async () => {
        requests++;
        return requests === 1 ? {ok: false, status: 503} : {ok: true, text: async () => fragment('mobile')};
    };
    await assert.rejects(window.loadTheoryMenu(mobileRoot, content), /HTTP 503/);
    assert.equal(content.querySelector('[data-theory-menu-fallback] a').getAttribute('href'), '/theory');
    assert.equal(observers(), 1);
    await window.loadTheoryMenu(mobileRoot, content);
    window.showTheoryMenu(mobileRoot);
    assert.equal(requests, 2);
    assert.equal(content.querySelectorAll('[data-theory-menu-fragment]').length, 1);
    assert.equal(observers(), 2);
});
