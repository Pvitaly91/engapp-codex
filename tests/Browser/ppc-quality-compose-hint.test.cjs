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
  const dom = new JSDOM(`<span id="compose-source-label"></span>${withHint ? '<p id="compose-learning-hint" class="hidden"></p>' : ''}`);
  const label = dom.window.document.getElementById('compose-source-label'); label.textContent = legacy;
  const context = {document: dom.window.document, testUi: key => localeText(locale, key.split('.').at(-1))};
  const render = vm.runInNewContext(method + '\nrenderComposePreAnswerHint;', context);
  return {dom, label, render, legacy, hint: dom.window.document.getElementById('compose-learning-hint')};
}
for (const [locale, expected] of Object.entries({uk: 'Умова', en: 'Task', pl: 'Polecenie'})) {
  test(`actual composer ${locale} uses finite Task label and escaped lexical hint`, () => {
    const h = harness(locale);
    const hint = 'Lemma: repair <img src=x onerror="bad()"> & <script>bad()</script>';
    h.render({showPreAnswerHint: true, hintUk: hint});
    assert.equal(h.label.textContent, expected);
    assert.equal(h.hint.textContent, hint); assert.equal(h.hint.classList.contains('hidden'), false);
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
    h.dom.window.close();
  }
});
