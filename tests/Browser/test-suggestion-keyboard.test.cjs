const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');

const read = file => fs.readFileSync(`resources/views/${file}.blade.php`, 'utf8');
const keyboard = read('components/test-suggestion-keyboard').match(/<script>([\s\S]*?)<\/script>/)[1];

function fixture(t, mode = 'card-easy', selector = 'button') {
    const dom = new JSDOM(`<article id="question-card" data-idx="0">
        <input data-manual-gap="0" data-manual-word="0" data-question="0" value="hav">
        <div id="suggestions"></div></article><input id="search">`, {runScripts: 'outside-only'});
    t.after(() => dom.window.close());
    const {window} = dom;
    window.HTMLElement.prototype.scrollIntoView = function () {};
    window.eval(keyboard);
    const input = window.document.querySelector('input');
    const list = window.document.getElementById('suggestions');
    const commits = [];
    const choices = [];
    window.state = {items: [{done: false}], current: 0};
    window.commitManualWord = (...args) => commits.push(args);
    if (mode) {
        const source = read(`test-modes/${mode}`);
        const handler = source.match(/function handleManualAnswerShortcut\(e\) \{[\s\S]*?^\}/m)[0];
        window.eval(handler);
        window.document.addEventListener('keydown', window.handleManualAnswerShortcut, true);
    }
    const tag = selector.startsWith('li') ? 'li' : 'button';
    ['have', 'had', "haven't"].forEach(word => {
        const option = window.document.createElement(tag);
        option.textContent = word;
        option.dataset.value = word;
        option.addEventListener('click', () => { choices.push(word); input.value = word; });
        list.append(option);
    });
    const press = (key, init = {}, target = input) => {
        const event = new window.KeyboardEvent('keydown', {key, bubbles: true, cancelable: true, ...init});
        target.dispatchEvent(event);
        return event;
    };
    const activate = () => window.activateTestSuggestionList(input, list, selector);
    input.focus();
    return {window, input, list, commits, choices, press, activate};
}

for (const mode of ['card-easy', 'step-easy']) {
    test(`${mode}: Tab cycles the popup without committing; Enter picks it; Tab then commits once`, t => {
        const f = fixture(t, mode);
        f.activate();
        for (const word of ['have', 'had', "haven't", 'have']) {
            assert.equal(f.press('Tab').defaultPrevented, true);
            assert.equal(f.list.querySelector('[aria-selected="true"]').textContent, word);
            assert.equal(f.input.value, 'hav');
            assert.equal(f.input.ownerDocument.activeElement, f.input);
            assert.equal(f.list.classList.contains('hidden'), false);
            assert.equal(f.commits.length, 0);
        }
        f.press('Enter');
        assert.deepEqual(f.choices, ['have']);
        assert.equal(f.commits.length, 0);
        assert.equal(f.list.classList.contains('hidden'), true);
        f.press('Tab');
        assert.equal(f.commits.length, 1);
    });

    test(`${mode}: absent, empty, hidden and detached lists allow Tab to commit`, t => {
        for (const state of ['absent', 'empty', 'hidden', 'detached']) {
            const f = fixture(t, mode);
            if (state !== 'absent') f.activate();
            if (state === 'empty') f.list.replaceChildren();
            if (state === 'hidden') f.list.classList.add('hidden');
            if (state === 'detached') f.list.remove();
            f.press('Tab');
            assert.equal(f.commits.length, 1, state);
            assert.equal(f.choices.length, 0, state);
        }
    });
}

for (const selector of ['button', 'li[data-value]', 'li']) {
    test(`${selector} suggestions: arrow navigation, Escape and Shift+Tab remain usable`, t => {
        const f = fixture(t, 'card-easy', selector);
        f.activate();
        f.press('ArrowUp');
        assert.equal(f.list.querySelector('[aria-selected="true"]').textContent, "haven't");
        f.press('Tab');
        assert.equal(f.list.querySelector('[aria-selected="true"]').textContent, 'have');
        assert.equal(f.press('Tab', {shiftKey: true}).defaultPrevented, false);
        assert.equal(f.list.classList.contains('hidden'), true);
        assert.equal(f.commits.length, 0);
        f.activate();
        f.press('Escape');
        assert.equal(f.input.hasAttribute('aria-activedescendant'), false);
        f.press('Tab');
        assert.equal(f.commits.length, 1);
    });
}

test('composition, browser shortcuts and unrelated inputs do not navigate or submit answers', t => {
    const f = fixture(t);
    f.activate();
    for (const options of [{isComposing: true}, {ctrlKey: true}, {altKey: true}, {metaKey: true}]) {
        assert.equal(f.press('Tab', options).defaultPrevented, false);
        assert.equal(f.press('Enter', options).defaultPrevented, false);
    }
    assert.equal(f.press('Tab', {}, f.window.document.getElementById('search')).defaultPrevented, false);
    assert.equal(f.list.querySelector('[aria-selected="true"]'), null);
    assert.equal(f.commits.length, 0);
});

test('dialogue: Tab checks an answer field but preserves navigation from unrelated fields', t => {
    const f = fixture(t, null);
    f.input.setAttribute('data-blank-index', '0');
    f.window.state = {currentIndex: 0};
    f.window.handleCheck = () => f.commits.push('check');
    const handler = read('test-modes/dialogue').match(/document\.addEventListener\('keydown',[\s\S]*?^\}\);/m)[0];
    f.window.eval(handler);
    assert.equal(f.press('Tab').defaultPrevented, true);
    assert.equal(f.commits.length, 1);
    assert.equal(f.press('Tab', {}, f.window.document.getElementById('search')).defaultPrevented, false);
    assert.equal(f.press('Tab', {shiftKey: true}).defaultPrevented, false);
    assert.equal(f.commits.length, 1);
});

// Run each actual per-input handler behind the real document capture listener.
// The answer checker is isolated so keyboard tests never change persisted progress.
for (const view of [
    'test-modes/card-hard', 'test-modes/step-hard', 'test-modes/card-expert', 'test-modes/step-expert',
    'engram/saved-test-js-input', 'engram/saved-test-js-step-input',
    'engram/saved-test-js-manual', 'engram/saved-test-js-step-manual',
    'verbs/test', 'words/test', 'test-modes/step-compose',
]) {
    test(`${view}: popup Tab wins over the mode handler, no-popup Tab checks once`, t => {
        const f = fixture(t, null);
        const source = read(view);
        const pattern = /(?:inp|answerInput|els\.answerInput\?|root)\.addEventListener\('keydown',\s*(?:\(\w+\)|\w+) => \{[\s\S]*?^\s*\}\);/gm;
        const binding = [...source.matchAll(pattern)].find(match => match[0].includes('isTestAnswerCommitKey'))?.[0];
        assert.ok(binding, `Missing answer keyboard handler in ${view}`);
        f.window.inp = f.input;
        f.window.answerInput = f.input;
        f.window.els = {answerInput: f.input};
        f.window.root = f.input.parentElement;
        f.window.idx = 0;
        f.window.onCheck = () => f.commits.push('check');
        f.window.evaluateAnswer = f.window.onCheck;
        f.window.checkAnswer = f.window.onCheck;
        f.window.answerForm = {requestSubmit: f.window.onCheck};
        f.window.currentQuestion = () => ({});
        f.window.sanitizeInteger = value => Number.parseInt(value, 10);
        f.window.setManualSlotValue = () => {};
        f.input.setAttribute('data-compose-manual-slot', '0');
        f.window.eval(binding);
        f.activate();
        f.press('Tab');
        assert.equal(f.list.querySelector('[aria-selected="true"]').textContent, 'have');
        assert.equal(f.commits.length, 0);
        f.press('Enter');
        assert.deepEqual(f.choices, ['have']);
        assert.equal(f.commits.length, 0);
        f.press('Tab');
        assert.equal(f.commits.length, 1);
        assert.equal(f.press('Tab', {shiftKey: true}).defaultPrevented, false);
        assert.equal(f.commits.length, 1);
    });
}
