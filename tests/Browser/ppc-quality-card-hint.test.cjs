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
const renderer = between(card, 'function renderAuthoredComposeHint(q) {', 'function renderOptionsBlock(q, idx,');
const hintRenderer = between(card, 'function renderHints(q, idx) {', 'function toggleTheoryPanel(q, idx) {');
const revision = 'a'.repeat(64);

function render(question) {
  const dom = new JSDOM('<div id="questions"></div><div id="summary" class="hidden"></div>');
  const context = vm.createContext({
    document: dom.window.document, state: {items: [question], answered: 0, correct: 0},
    testUi: key => key, clampActiveSlot: () => {},
    renderSentence: q => q.question,
    renderPolyglotTranslationPreview: () => '',
    getActiveOptions: () => [], getTheoryBlocks: () => [], renderFeedback: () => '', renderOptionsBlock: () => '',
  });
  vm.runInContext(escaper + '\n' + hintRenderer + '\n' + renderer + '\nrenderQuestions();', context);
  return {dom, card: dom.window.document.querySelector('article[data-idx="0"]')};
}

for (const [locale, hint] of Object.entries({
  uk: 'Лексична основа: work — працювати. Збережи названу минулу точку відліку.',
  en: 'Learning lemma: work. Preserve the stated past reference point.',
  pl: 'Czasownik do użycia: work. Zachowaj wskazany punkt odniesienia w przeszłości.',
})) {
  test(`actual card shows ${locale} authored type4 hint before answering without options`, () => {
    const {dom, card} = render({type: 4, compose_content_revision: revision, hint, question: 'Current localized condition', done: false, hints: {general: hint}});
    const visibleHint = card.querySelector('[data-authored-compose-hint]');
    assert.ok(visibleHint); assert.equal(visibleHint.textContent, hint);
    assert.equal(visibleHint.closest('.hidden'), null);
    assert.equal(card.querySelector('#options-block-0').textContent.trim(), '');
    assert.ok(card.innerHTML.indexOf('Current localized condition') < card.innerHTML.indexOf('data-authored-compose-hint'));
    assert.ok(card.innerHTML.indexOf('data-authored-compose-hint') < card.innerHTML.indexOf('help-btn'));
    dom.window.close();
  });
}

test('actual card escapes authored hint as text, not executable markup', () => {
  const hint = 'Lemma: <img src=x onerror="alert(1)"> & "read" <script>bad()</script>.';
  const {dom, card} = render({type: '4', compose_content_revision: revision, hint, question: 'Condition', done: false});
  const visibleHint = card.querySelector('[data-authored-compose-hint]');
  assert.equal(visibleHint.textContent, hint);
  assert.equal(visibleHint.querySelector('img,script'), null);
  assert.match(visibleHint.innerHTML, /&lt;img/);
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
