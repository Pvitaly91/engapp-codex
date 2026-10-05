'use strict';
// Actual card function + real HTML escaper, in a DOM without DB or HTTP.
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {JSDOM} = require('jsdom');
const root = path.resolve(__dirname, '../..');
const card = fs.readFileSync(path.join(root, 'resources/views/test-modes/card-easy.blade.php'), 'utf8');
const helpers = fs.readFileSync(path.join(root, 'resources/views/components/saved-test-js-helpers.blade.php'), 'utf8');
function between(source, start, end) {
  const a = source.indexOf(start), b = source.indexOf(end, a + start.length);
  assert.ok(a >= 0 && b > a, 'Actual source function boundaries');
  return source.slice(a, b);
}
const escaper = between(helpers, 'function html(str) {', '\n}',) + '\n}';
const renderer = between(card, 'function renderQuestions(showOnlyWrong = false) {', 'function renderOptionsBlock(q, idx,');
const hintRenderer = between(card, 'function renderHints(q, idx) {', 'function toggleTheoryPanel(q, idx) {');
const localHelp = between(helpers, 'function getAuthoredComposeHelpText(q) {', 'function techInfoUi(key, fallback');
const fetchHints = between(card, 'function fetchHints(q, idx, refresh = false) {', 'function renderHints(q, idx) {');
const revision = 'a'.repeat(64);

function render(question) {
  const dom = new JSDOM('<div id="questions"></div><div id="summary" class="hidden"></div>');
  const context = vm.createContext({
    document: dom.window.document, state: {items: [question], answered: 0, correct: 0},
    testUi: key => key, clampActiveSlot: () => {},
    renderSentence: q => q.question,
    renderPolyglotTranslationPreview: () => '',
    getActiveOptions: () => [], getTheoryBlocks: () => [], renderFeedback: () => '', renderOptionsBlock: () => '',
    fetch: () => { throw new Error('Authored help must not send a POST'); },
  });
  vm.runInContext([escaper, localHelp, fetchHints, hintRenderer, renderer, 'renderQuestions();'].join('\n'), context);
  return {dom, card: dom.window.document.querySelector('article[data-idx="0"]')};
}

for (const [locale, hint] of Object.entries({
  uk: 'Лексична основа: work — працювати. Збережи названу минулу точку відліку.',
  en: 'Learning lemma: work. Preserve the stated past reference point.',
  pl: 'Czasownik do użycia: work. Zachowaj wskazany punkt odniesienia w przeszłości.',
})) {
  test(`actual card keeps ${locale} authored hint closed and shows it only on real help-button click`, () => {
    const question = {type: 4, compose_content_revision: revision, hint, question: 'Current localized sentence', done: false, hints: {chatgpt: 'Stale cached hint'}};
    const {dom, card} = render(question);
    assert.equal(card.querySelector('[data-authored-compose-hint]'), null);
    assert.equal(card.querySelector('#hints-0').textContent, '');
    assert.equal(card.querySelector('.leading-relaxed').textContent, question.question);
    assert.equal(card.querySelector('#options-block-0').textContent.trim(), '');
    const help = card.querySelector('.help-btn');
    assert.equal(help.getAttribute('aria-expanded'), 'false');
    help.click();
    assert.equal(help.getAttribute('aria-expanded'), 'true');
    assert.equal(card.querySelector('[data-authored-compose-hint]').textContent, hint);
    assert.equal(card.querySelector('[data-authored-compose-help-label]').textContent, 'question.hide_help');
    help.click();
    assert.equal(help.getAttribute('aria-expanded'), 'false');
    assert.equal(card.querySelector('[data-authored-compose-hint]'), null);
    assert.equal(question.done, false);
    dom.window.close();
  });
}

test('actual card escapes authored hint as text, not executable markup', () => {
  const hint = 'Lemma: <img src=x onerror="alert(1)"> & "read" <script>bad()</script>.';
  const {dom, card} = render({type: '4', compose_content_revision: revision, hint, question: 'Condition', done: false});
  card.querySelector('.help-btn').click();
  const visibleHint = card.querySelector('[data-authored-compose-hint]');
  assert.equal(visibleHint.textContent, hint);
  assert.equal(visibleHint.querySelector('img,script'), null);
  assert.match(visibleHint.innerHTML, /&lt;img/);
  dom.window.close();
});

test('step choose mode also discloses local authored help without a generation request', () => {
  const step = fs.readFileSync(path.join(root, 'resources/views/test-modes/step-easy.blade.php'), 'utf8');
  const fetcher = between(step, 'function fetchHints(q, refresh = false) {', 'function renderHints(q) {')
    .replace(/\{\{[^}]+\}\}/g, '/unused-legacy-hint-endpoint');
  const renderHints = between(step, 'function renderHints(q) {', 'function toggleTheoryPanel(q) {');
  const dom = new JSDOM('<button id="help"><span data-authored-compose-help-label>Show</span></button><div id="hints"></div>');
  const q = {type: 4, compose_content_revision: revision, hint: 'Exact authored instruction. Lemma: work.', hints: {chatgpt: 'Old text'}};
  const context = vm.createContext({document: dom.window.document, testUi: key => key,
    fetch: () => { throw new Error('No generation POST for finite help'); }});
  vm.runInContext([escaper, localHelp, fetcher, renderHints].join('\n'), context);
  context.renderHints(q);
  assert.equal(dom.window.document.getElementById('hints').textContent, '');
  context.fetchHints(q);
  assert.equal(dom.window.document.querySelector('[data-authored-compose-hint]').textContent, q.hint);
  assert.equal(dom.window.document.getElementById('help').getAttribute('aria-expanded'), 'true');
  context.fetchHints(q);
  assert.equal(dom.window.document.querySelector('[data-authored-compose-hint]'), null);
  dom.window.close();
});

test('legacy, non-type4, missing/invalid revision and empty hints emit no authored hint markup', () => {
  for (const question of [
    {type: 4, hint: 'Legacy general hint'},
    {type: 0, compose_content_revision: revision, hint: 'Mixed gap hint'},
    {type: 4, compose_content_revision: '', hint: 'No revision'},
    {type: 4, compose_content_revision: 'legacy-snapshot', hint: 'Not finite'},
    {type: 4, compose_content_revision: revision, hint: '  '},
    {type: 4, compose_content_revision: revision, hint: null},
  ]) {
    const {dom, card} = render({...question, question: 'Condition', done: false});
    assert.equal(card.querySelector('[data-authored-compose-hint]'), null);
    assert.equal(card.querySelector('#hints-0').textContent, '');
    dom.window.close();
  }
});
