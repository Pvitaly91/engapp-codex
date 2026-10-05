'use strict';
// Execute the actual alternate theory-course mixed renderer; no DB or HTTP.
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {JSDOM} = require('jsdom');
const root = path.resolve(__dirname, '../..');
const source = fs.readFileSync(path.join(root, 'public/js/theory-mixed-test.js'), 'utf8');
const script = source.replace(/^import TheoryCourseProgress from '\.\/theory-course-progress\.js';\s*/, '');
assert.notEqual(script, source, 'Only the actual static import is replaced by the in-memory test store');
const labels = Object.fromEntries(['uk', 'en', 'pl'].map(locale => {
  const php = fs.readFileSync(path.join(root, `resources/lang/${locale}/frontend.php`), 'utf8');
  return [locale, ['show_help', 'hide_help'].map(key => {
    const match = php.match(new RegExp(`'${key}'\\s*=>\\s*'([^']*)'`));
    assert.ok(match, `Actual versioned ${locale}.${key}`);
    return match[1];
  })];
}));
const sources = {uk: 'Я перед цим плавав, тому моє волосся було мокрим.',
  en: 'Explain the earlier swimming that left my hair wet.',
  pl: 'Wcześniej pływałem, więc moje włosy były mokre.'};
function harness(locale = 'en', overrides = {}, second = false) {
  const dom = new JSDOM('<div data-theory-mixed-root></div><div id="theory-mixed-lock" class="hidden"></div><div id="theory-mixed-workspace"></div><div id="theory-mixed-question-card"></div><div id="theory-mixed-summary" class="hidden"></div><span id="theory-mixed-progress-label"></span><span id="theory-mixed-score-label"></span><div id="theory-mixed-progress-bar"></div><button id="theory-mixed-prev"></button><button id="theory-mixed-next"></button>');
  const question = {uuid: 'finite-ppc-1', type: '4', renderer: 'compose', showPreAnswerHint: true,
    sourceTextUk: sources[locale], question: 'Full canonical source must not replace the display.',
    hintUk: `Instructions for ${locale}.\nLexical verb: swim <img src=x onerror="bad()"> & <script>bad()</script>`,
    correctText: 'I had been swimming.', punctuation: '.', correctTokenIds: ['c1', 'c2', 'c3', 'c4'],
    tokenBank: ['I', 'had', 'been', 'swimming'].map((value, i) => ({id: `c${i + 1}`, value})), ...overrides};
  const questions = [question];
  if (second) questions.push({...question, uuid: 'finite-ppc-2'});
  const config = {course: {slug: 'private-fixture'}, lesson: {lesson_slug: 'ppc-fixture'}, lessons: [], questions,
    helpI18n: {show_help: labels[locale][0], hide_help: labels[locale][1]}};
  const calls = {opened: 0, completed: 0, http: 0};
  const store = {read: () => ({}), getLessonStatus: () => 'ready', markLessonOpened: () => calls.opened++,
    markLessonCompleted: () => calls.completed++, resetLesson: () => {throw new Error('No progress reset in a help test');}};
  dom.window.__THEORY_MIXED_TEST__ = config;
  dom.window.EnglishAnswerVariants = {matches: (expected, answer) => expected.trim().toLowerCase() === answer.trim().toLowerCase()};
  const context = vm.createContext({window: dom.window, document: dom.window.document,
    TheoryCourseProgress: {createStore: () => store},
    fetch: () => {calls.http++; throw new Error('Help is local, never a generation/progress POST');}});
  const api = vm.runInContext(script + '\n({state, render, normalizeComposeQuestion, toggleComposeHelp});', context);
  return {dom, doc: dom.window.document, api, calls, question,
    button: () => dom.window.document.querySelector('[data-compose-help-toggle]'),
    hint: () => dom.window.document.querySelector('[data-theory-compose-hint]'),
    close: () => dom.window.close()};
}
for (const locale of ['uk', 'en', 'pl']) {
  test(`alternate compose ${locale}: short title, closed escaped local help, real toggle and token rerender`, () => {
    const h = harness(locale, {}, true);
    const heading = h.doc.querySelector('#theory-mixed-question-card .text-2xl');
    assert.equal(heading.textContent, sources[locale]);
    assert.equal(heading.textContent.includes('Lexical'), false);
    assert.equal(h.button().textContent, labels[locale][0]);
    assert.equal(h.button().getAttribute('aria-expanded'), 'false');
    assert.equal(h.hint().hidden, true);
    assert.equal(h.hint().querySelector('img,script'), null);
    assert.equal(h.api.state.items[0].done, false);
    h.button().click();
    assert.equal(h.button().textContent, labels[locale][1]);
    assert.equal(h.button().getAttribute('aria-expanded'), 'true');
    assert.equal(h.hint().hidden, false);
    assert.equal(h.hint().textContent, h.question.hintUk);
    h.doc.querySelector('[data-compose-bank-token="c1"]').click();
    assert.deepEqual([...h.api.state.items[0].selectedTokenIds], ['c1']);
    assert.equal(h.hint().hidden, false, 'Same-task token rerender preserves explicit help choice');
    h.button().click();
    assert.equal(h.hint().hidden, true);
    assert.equal(h.api.state.answered, 0);
    assert.equal(h.calls.completed, 0);
    assert.equal(h.calls.http, 0);
    h.button().click();
    h.api.state.index = 1; h.api.render();
    assert.equal(h.hint().hidden, true, 'A new task starts closed');
    h.close();
  });
}
test('alternate compose legacy/invalid opt-in and empty hints retain the previous no-help UI', () => {
  for (const overrides of [{showPreAnswerHint: false}, {showPreAnswerHint: undefined}, {showPreAnswerHint: 'true'},
    {showPreAnswerHint: 1}, {type: 0}, {type: undefined}, {hintUk: ''}, {hintUk: '  '}, {hintUk: null}]) {
    const h = harness('en', overrides);
    assert.equal(h.button(), null); assert.equal(h.hint(), null);
    const before = h.api.state.items[0].composeHelpOpen;
    h.api.toggleComposeHelp();
    assert.equal(h.api.state.items[0].composeHelpOpen, before);
    assert.equal(h.doc.querySelector('#theory-mixed-question-card .text-2xl').textContent, sources.en);
    assert.equal(h.doc.querySelectorAll('[data-compose-bank-token]').length, 4);
    assert.equal(h.calls.http, 0);
    h.close();
  }
});
test('alternate help toggle changes no tokens, answer checking, score, completion or request behavior', () => {
  const h = harness('en');
  const initial = JSON.stringify({answered: h.api.state.answered, correct: h.api.state.correct,
    completed: h.api.state.completed, tokens: h.api.state.items[0].selectedTokenIds});
  h.button().click(); h.button().click();
  assert.equal(JSON.stringify({answered: h.api.state.answered, correct: h.api.state.correct,
    completed: h.api.state.completed, tokens: h.api.state.items[0].selectedTokenIds}), initial);
  for (const id of h.question.correctTokenIds) h.doc.querySelector(`[data-compose-bank-token="${id}"]`).click();
  h.doc.querySelector('[data-compose-check]').click();
  assert.equal(h.api.state.answered, 1); assert.equal(h.api.state.correct, 1); assert.equal(h.api.state.completed, true);
  assert.equal(h.calls.http, 0);
  h.close();
});
test('actual alternate route payload and view supply a finite flag and localized help labels', () => {
  const service = fs.readFileSync(path.join(root, 'app/Services/TheoryCourseTestPoolService.php'), 'utf8');
  const view = fs.readFileSync(path.join(root, 'resources/views/test-modes/theory-mixed.blade.php'), 'utf8');
  assert.match(service, /'showPreAnswerHint'\s*=>\s*\\App\\Support\\LocalizedComposeText::revisionEligible\(\$question\)/);
  assert.match(view, /'helpI18n'\s*=>\s*__\('frontend\.tests\.question'\)/);
  assert.match(source, /event\.target\.closest\('\[data-compose-help-toggle\]'\)\)[\s\S]*?toggleComposeHelp\(\)/);
});
