'use strict';
// Execute the real manual-card functions and shared matcher without DB/HTTP.
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const root = path.resolve(__dirname, '../..');
const variantContext = {GRAMLYZE_CONTRACTION_RULES: JSON.parse(fs.readFileSync(path.join(root, 'public/data/english-contractions.json'), 'utf8'))};
vm.runInNewContext(fs.readFileSync(path.join(root, 'public/js/english-answer-variants.js'), 'utf8'), variantContext);
const EnglishAnswerVariants = variantContext.EnglishAnswerVariants;
const helpers = fs.readFileSync(path.join(root, 'resources/views/components/saved-test-js-helpers.blade.php'), 'utf8');
const previewPath = path.join(root, 'resources/views/components/authored-compose-manual-preview.blade.php');
const preview = fs.readFileSync(previewPath, 'utf8').replace(/^<script>\s*|\s*<\/script>\s*$/g, '');
const revision = 'a'.repeat(64);
// Exact legacy function bodies from immutable M35 (LF-normalized only).
const legacyHashes = {
  'card-expert': 'a8f46443cd623976a16fec066e1cb66129d06606b673490f14ab8146a7a72539',
  'step-expert': 'bd5bd43f09c9b0932c187aead8574959a0002f5d93cab6a1e4ad136ad15c2007',
};
function between(source, start, end) {
  const a = source.indexOf(start), b = source.indexOf(end, a + start.length);
  assert.ok(a >= 0 && b > a, 'Actual source function boundaries');
  return source.slice(a, b);
}
const escaper = between(helpers, 'function html(str) {', '\n}') + '\n}';
const matcher = between(helpers, 'function canonicalTestAnswer(value) {', 'function composeManualAnswerWords(question, slotIndex) {');
const conditions = {
  uk: ['Склади переклад: Я не ремонтував велосипед протягом двох годин до приходу механіка.', 'Лексична основа: repair — ремонтувати. Збережи заперечення.'],
  en: ['Build the English sentence: I had not spent two hours repairing the bike before the mechanic arrived.', 'Learning lemma: repair. Preserve the negative meaning.'],
  pl: ['Ułóż zdanie po angielsku: Nie naprawiałem roweru przez dwie godziny przed przyjściem mechanika.', 'Czasownik do użycia: repair. Zachowaj przeczenie.'],
};
function authored(locale = 'uk') {
  const answers = 'I had not been repairing the bike for two hours when the mechanic arrived.'.split(' ');
  const markers = answers.map((_, i) => `a${i + 1}`);
  return {type: 4, compose_content_revision: revision, question: conditions[locale][0], hint: conditions[locale][1],
    answers, markers, answer_map: Object.fromEntries(markers.map((m, i) => [m, answers[i]])), verb_hints: {a5: 'repair'}};
}
function harness(mode, question, options = {}) {
  const source = fs.readFileSync(path.join(root, `resources/views/test-modes/${mode}.blade.php`), 'utf8');
  assert.ok(source.includes("@include('components.authored-compose-manual-preview')"));
  const cardMode = mode === 'card-expert';
  const renderer = cardMode
    ? between(source, 'function renderQuestions(showOnlyWrong = false) {', 'function onCheck(idx) {')
    : between(source, 'function render() {', 'function onCheck() {');
  const checker = cardMode
    ? between(source, 'function onCheck(idx) {', 'function renderFeedback(q) {')
    : between(source, 'function onCheck() {', "document.getElementById('prev').addEventListener");
  const sentence = between(source, cardMode ? 'function renderSentence(q, idx) {' : 'function renderSentence(q) {',
    cardMode ? 'function autoResize(el) {' : 'function renderFeedback(q) {');
  const dom = new JSDOM('<div id="questions"></div><div id="question-card"></div><div id="summary" class="hidden"></div><div id="summary-text"></div><div id="final-check"></div><button id="retry"></button><button id="show-wrong"></button><button id="prev"></button><button id="next"></button>');
  const prepared = options.prepare === false ? question : JSON.parse(JSON.stringify(EnglishAnswerVariants.prepareQuestion(question)));
  const item = {...prepared, chosen: options.chosen || Array(prepared.answers.length).fill(''), done: !!options.done, wrongAttempt: false, feedback: ''};
  const state = {items: [item], current: 0, answered: 0, correct: 0};
  let persisted = 0;
  const context = vm.createContext({document: dom.window.document, state, EnglishAnswerVariants,
    testUi: (key, replacements = {}) => `${key} ${Object.values(replacements).join(' ')}`.trim(),
    renderFeedback: q => q.feedback, updateProgress: () => {}, persistState: () => { persisted++; },
    autoResize: () => {}, pct: (a, b) => Math.round(a / b * 100), restartJsTest: () => {}, init: () => {},
    isTestAnswerCommitKey: e => e.key === 'Enter',
  });
  vm.runInContext([escaper, matcher, preview, sentence, renderer, checker].join('\n'), context);
  vm.runInContext(cardMode ? 'renderQuestions();' : 'render();', context);
  return {dom, state, item, context, cardMode, persisted: () => persisted,
    renderSentence: q => context.renderSentence(q, ...(cardMode ? [0] : [])),
    check: () => context.onCheck(...(cardMode ? [0] : [])),
    inputs: () => [...dom.window.document.querySelectorAll('input[data-slot]')],
    close: () => dom.window.close()};
}

for (const mode of ['card-expert', 'step-expert']) {
  for (const locale of Object.keys(conditions)) {
    test(`${mode} ${locale}: real renderer creates all manual slots, localized lexical hint and no answers/options`, () => {
      const h = harness(mode, authored(locale));
      const doc = h.dom.window.document;
      assert.equal(doc.querySelector('[data-authored-compose-condition]').textContent, conditions[locale][0]);
      assert.equal(doc.querySelector('[data-authored-compose-hint]').textContent, conditions[locale][1]);
      assert.equal(h.inputs().length, h.item.answers.length);
      assert.equal(h.item.contraction_slots_version, 1);
      assert.ok(h.item.markers.some((m, i) => m !== `a${i + 1}`), 'Contraction groups leave non-sequential authored markers');
      h.inputs().forEach((input, i) => {
        assert.equal(input.value, '');
        assert.ok(input.getAttribute('aria-label').includes(h.item.markers[i]));
        assert.equal(input.id, h.cardMode ? `input-0-${i}` : `input-${i}`);
        assert.equal(input.hasAttribute('data-question'), h.cardMode);
        assert.equal(input.style.minWidth, '8rem', 'Empty fields stay readable after existing autoResize');
        assert.equal(input.style.maxWidth, '100%');
        assert.equal(input.placeholder, '____');
      });
      assert.equal(doc.querySelector('option,[data-option],.options-bank'), null);
      assert.ok(!doc.querySelector('[data-authored-compose-manual]').textContent.includes(h.item.answers.join(' ')));
      assert.equal(doc.querySelector('.verb-hint').textContent, '( repair )');
      h.close();
    });
  }

  test(`${mode}: real onCheck accepts full-form and contractions through unchanged shared matcher`, () => {
    for (const first of ['I had not', "I hadn't", 'I hadn’t']) {
      const h = harness(mode, authored());
      const expected = [first, ...h.item.answers.slice(1)];
      h.inputs().forEach((input, i) => {
        input.value = expected[i];
        input.dispatchEvent(new h.dom.window.Event('input', {bubbles: true}));
      });
      assert.deepEqual(h.item.chosen, expected, 'Existing input listeners persist typed slots');
      h.check();
      assert.equal(h.item.done, true, `Expected authored equivalent: ${first}`); assert.equal(h.item.feedback, 'correct');
      assert.equal(h.state.correct, 1); assert.ok(h.persisted() > 0);
      assert.equal(h.inputs().length, 0);
      assert.equal(h.dom.window.document.querySelectorAll('[data-authored-compose-slot]').length, expected.length);
      assert.ok(h.dom.window.document.querySelector('[data-polyglot-translation-status="done"]'));
      h.close();
    }
  });

  test(`${mode}: existing matcher also accepts positive I'd and inverted negative hadn't contractions`, () => {
    for (const [target, first] of [
      ['I had been repairing the bike for two hours when the mechanic arrived.', "I'd been"],
      ['Had she not been repairing the bike for two hours when the mechanic arrived?', "Hadn't she"],
    ]) {
      const q = authored(); q.answers = target.split(' ');
      q.markers = q.answers.map((_, i) => `a${i + 1}`);
      q.answer_map = Object.fromEntries(q.markers.map((m, i) => [m, q.answers[i]]));
      const h = harness(mode, q);
      assert.equal(EnglishAnswerVariants.matches(h.item.answers[0], first, EnglishAnswerVariants.contextFor(h.item, 0)), true);
      h.inputs().forEach((input, i) => { input.value = i === 0 ? first : h.item.answers[i]; });
      h.check(); assert.equal(h.item.done, true);
      h.close();
    }
  });

  test(`${mode}: real onCheck rejects omitted not, omitted been and wrong auxiliary`, () => {
    for (const mutate of [values => { values[0] = 'I had'; }, values => { values[1] = ''; }, values => { values[0] = 'I have not'; }]) {
      const h = harness(mode, authored());
      const values = [...h.item.answers]; mutate(values);
      h.inputs().forEach((input, i) => { input.value = values[i]; });
      h.check();
      assert.equal(h.item.done, false); assert.equal(h.state.correct, 0);
      assert.equal(h.item.feedback, 'status.incorrect_try_again');
      assert.equal(h.inputs().length, h.item.answers.length);
      h.close();
    }
  });

  test(`${mode}: finite condition, hint and chosen answers are escaped without executable markup`, () => {
    const q = authored();
    q.question = '<img src=x onerror="bad()"> Condition'; q.hint = '<script>bad()</script> & lemma';
    q.verb_hints.a5 = '<img src=x> repair';
    const h = harness(mode, q, {chosen: Array(q.answers.length).fill('" onfocus="bad()'), done: true});
    const doc = h.dom.window.document;
    assert.equal(doc.querySelector('img,script'), null);
    assert.equal(doc.querySelector('[data-authored-compose-condition]').textContent, q.question);
    assert.equal(doc.querySelector('[data-authored-compose-hint]').textContent, q.hint);
    assert.ok(doc.querySelector('mark').textContent.includes('onfocus'));
    h.close();
  });

  test(`${mode}: legacy rendering remains byte-identical and finite guard never rewrites other types`, () => {
    const source = fs.readFileSync(path.join(root, `resources/views/test-modes/${mode}.blade.php`), 'utf8').replace(/\r\n/g, '\n');
    const start = mode === 'card-expert' ? 'function renderSentence(q, idx) {' : 'function renderSentence(q) {';
    const end = mode === 'card-expert' ? 'function autoResize(el) {' : 'function renderFeedback(q) {';
    const current = between(source, start, end);
    const legacy = current.replace(/\n  if \(isAuthoredComposeManualQuestion\(q\)\) \{\n    return renderAuthoredComposeManualQuestion\(q(?:, idx)?\);\n  \}/, '');
    assert.equal(crypto.createHash('sha256').update(legacy).digest('hex'), legacyHashes[mode], 'Immutable M35 legacy function remains exact');
    const baseline = vm.runInNewContext(escaper + '\n' + legacy + '\nrenderSentence;', {});
    const h = harness(mode, authored());
    for (const q of [
      {type: 4}, {type: 4, compose_content_revision: 'not-finite'}, {type: 0, compose_content_revision: revision},
    ]) {
      for (const done of [false, true]) {
        const fixture = {...q, question: 'They {a1} {a2}.', answers: ['had', 'worked'], chosen: ['had', 'worked'], done, verb_hints: {a2: 'work'}};
        const expected = baseline(fixture, ...(mode === 'card-expert' ? [0] : []));
        assert.equal(h.renderSentence(fixture), expected);
      }
    }
    h.close();
  });
}

test('card-expert: actual autoResize reserves finite padding, borders and caret without changing legacy +8', () => {
  const source = fs.readFileSync(path.join(root, 'resources/views/test-modes/card-expert.blade.php'), 'utf8');
  const resize = between(source, 'function autoResize(el) {', 'function updateProgress() {');
  const dom = new JSDOM('<input id="finite" data-authored-compose-input><input id="legacy">');
  const doc = dom.window.document;
  const createElement = doc.createElement.bind(doc);
  doc.createElement = name => {
    const node = createElement(name);
    if (name === 'span') Object.defineProperty(node, 'offsetWidth', {value: 146});
    return node;
  };
  const context = vm.createContext({document: doc, getComputedStyle: () => ({
    font: '20px sans-serif', paddingLeft: '12px', paddingRight: '12px',
    borderLeftWidth: '2px', borderRightWidth: '2px',
  })});
  vm.runInContext(resize, context);
  const finite = doc.getElementById('finite'), legacy = doc.getElementById('legacy');
  for (const input of [finite, legacy]) {
    input.dataset.minWidth = '128';
    input.value = 'I had been';
    context.autoResize(input);
  }
  assert.equal(finite.style.width, '176px', 'Full typed text plus 24px padding, 4px borders and 2px caret');
  assert.equal(legacy.style.width, '154px', 'Original text width +8 remains exact for every legacy field');
  assert.equal(doc.querySelectorAll('span').length, 0, 'Temporary measuring spans are removed');
  dom.window.close();
});
