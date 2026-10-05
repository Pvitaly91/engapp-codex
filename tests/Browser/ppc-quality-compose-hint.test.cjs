'use strict';
// The actual composer label/hint renderer, with versioned locale strings.
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {JSDOM} = require('jsdom');
const root = path.resolve(__dirname, '../..');
const source = fs.readFileSync(path.join(root, 'resources/views/test-modes/step-compose.blade.php'), 'utf8');
const start = source.indexOf('function renderComposePreAnswerHint(question) {');
const end = source.indexOf('function render() {', start);
assert.ok(start > 0 && end > start);
const method = source.slice(start, end);
assert.ok(source.includes('id="compose-source-label">{{ __(\'frontend.tests.compose.source_sentence\') }}'));
function localeText(locale, key) {
  const php = fs.readFileSync(path.join(root, `resources/lang/${locale}/frontend.php`), 'utf8');
  const value = php.match(new RegExp(`'${key}'\\s*=>\\s*'([^']*)'`));
  assert.ok(value, `Versioned ${locale}.${key}`);
  return value[1];
}
function harness(locale, withHint = true) {
  const legacy = localeText(locale, 'source_sentence');
  const dom = new JSDOM(`<span id="compose-source-label"></span><button id="compose-hint-btn" class="hidden" aria-expanded="false"></button>${withHint ? '<p id="compose-learning-hint" class="hidden"></p>' : ''}`);
  const label = dom.window.document.getElementById('compose-source-label'); label.textContent = legacy;
  const context = {document: dom.window.document, testUi: key => localeText(locale, key.split('.').at(-1))};
  const methods = vm.runInNewContext(method + '\n({render: renderComposePreAnswerHint, toggle: toggleComposePreAnswerHint});', context);
  const button = dom.window.document.getElementById('compose-hint-btn');
  button.addEventListener('click', methods.toggle);
  return {dom, label, ...methods, button, legacy, hint: dom.window.document.getElementById('compose-learning-hint')};
}
for (const [locale, expected] of Object.entries({uk: 'Умова', en: 'Task', pl: 'Polecenie'})) {
  test(`actual composer ${locale} keeps finite hint hidden until the localized help click`, () => {
    const h = harness(locale);
    const hint = 'Lemma: repair <img src=x onerror="bad()"> & <script>bad()</script>';
    const question = {uuid: 'finite-1', showPreAnswerHint: true, hintUk: hint};
    h.render(question);
    assert.equal(h.label.textContent, expected);
    assert.equal(h.hint.textContent, hint); assert.equal(h.hint.classList.contains('hidden'), true);
    assert.equal(h.button.classList.contains('hidden'), false);
    assert.equal(h.button.textContent, localeText(locale, 'show_help'));
    assert.equal(h.button.getAttribute('aria-expanded'), 'false');
    h.button.click();
    assert.equal(h.hint.classList.contains('hidden'), false);
    assert.equal(h.button.textContent, localeText(locale, 'hide_help'));
    assert.equal(h.button.getAttribute('aria-expanded'), 'true');
    h.render(question);
    assert.equal(h.hint.classList.contains('hidden'), false, 'Same task rerender preserves explicit help choice');
    h.button.click();
    assert.equal(h.hint.classList.contains('hidden'), true);
    assert.equal(h.button.textContent, localeText(locale, 'show_help'));
    h.button.click();
    h.render({...question, uuid: 'finite-2'});
    assert.equal(h.hint.classList.contains('hidden'), true, 'The next task starts with help closed');
    assert.equal(h.hint.querySelector('img,script'), null);
    h.dom.window.close();
  });
}
test('actual composer restores original legacy source_sentence labels and hides finite hints', () => {
  for (const locale of ['uk', 'en', 'pl']) {
    const h = harness(locale);
    h.render({showPreAnswerHint: true, hintUk: 'Visible finite lemma'});
    for (const legacyQuestion of [{}, {showPreAnswerHint: false}, {showPreAnswerHint: 1}, {showPreAnswerHint: 'true'}]) {
      h.render({...legacyQuestion, hintUk: 'Not opted in'});
      assert.equal(h.label.textContent, h.legacy);
      assert.equal(h.hint.textContent, ''); assert.equal(h.hint.classList.contains('hidden'), true);
      assert.equal(h.button.classList.contains('hidden'), true);
      h.button.click();
      assert.equal(h.hint.classList.contains('hidden'), true);
    }
    h.dom.window.close();
  }
});
test('finite source label updates independently of empty or absent hint markup', () => {
  for (const withHint of [false, true]) {
    const h = harness('en', withHint);
    h.render({showPreAnswerHint: true, hintUk: '  '});
    assert.equal(h.label.textContent, 'Task');
    if (h.hint) { assert.equal(h.hint.textContent, ''); assert.equal(h.hint.classList.contains('hidden'), true); }
    assert.equal(h.button.classList.contains('hidden'), true);
    h.dom.window.close();
  }
});

test('actual composer routes help clicks locally and does not auto-show finite hints after a wrong answer', () => {
  assert.match(source, /event\.target\.closest\('#compose-hint-btn'\)[\s\S]*?toggleComposePreAnswerHint\(\)/);
  assert.match(source, /hint: question\.showPreAnswerHint === true \? '' : question\.hintUk \|\| ''/);
  assert.match(source, /id="compose-hint-btn"[\s\S]*?aria-controls="compose-learning-hint"/);
});
