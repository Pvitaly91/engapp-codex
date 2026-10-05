'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {JSDOM} = require('jsdom');
const root = path.resolve(__dirname, '../..');
const source = fs.readFileSync(path.join(root, 'resources/views/components/text-block-practice-questions.blade.php'), 'utf8');
const a = source.indexOf('get currentComposeHint() {');
const b = source.indexOf('selectedTokens: [],', a);
assert.ok(a > 0 && b > a);
const getter = vm.runInNewContext('({' + source.slice(a, b) + '})');
const question = JSON.parse(fs.readFileSync(path.join(root,
  'database/seeders/V3/Polyglot/PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder/definition.json'), 'utf8')).questions[0];
for (const locale of ['uk', 'en', 'pl']) {
  test(`actual linked getter provides finite localized ${locale} help only inside a closed disclosure`, () => {
    getter.currentQuestion = {type: '4', authored_compose: true,
      compose_preanswer_hint: question.localizations[locale].hints[0]};
    assert.equal(getter.currentComposeHint, question.localizations[locale].hints[0]);
    assert.ok(!getter.currentComposeHint.includes(question.target_text));
    assert.ok(source.includes('x-show="!answered && currentComposeHint"'));
    assert.ok(source.includes('<details x-show="!answered && currentComposeHint"'));
    assert.ok(source.includes(':open="composeHintOpen"'));
    assert.ok(source.includes('composeHintOpen: false'));
    assert.ok(source.includes('this.composeHintOpen = false;'));
    assert.ok(source.includes('x-text="currentComposeHint"'));
    assert.ok(!source.includes('x-html="currentComposeHint"'));
  });
}
test('actual linked getter returns empty for legacy/non-compose/missing finite hint payload', () => {
  for (const q of [null, {}, {type: 4, authored_compose: false, compose_preanswer_hint: 'Legacy'},
    {type: 0, authored_compose: true, compose_preanswer_hint: 'Gap'},
    {type: 4, authored_compose: true, hints: [{provider: 'chatgpt', hint: 'Unscoped fallback must not leak'}]},
    {type: 4, authored_compose: true, compose_preanswer_hint: '  '},
    {type: 4, authored_compose: true, compose_preanswer_hint: null}]) {
    getter.currentQuestion = q; assert.equal(getter.currentComposeHint, '');
  }
  assert.match(source, /revisionEligible\(\$q\)[\s\S]*?TYPE_COMPOSE_TOKENS[\s\S]*?compose_preanswer_hint/);
  assert.match(source, /@if\(collect\(\$questionsData\)->contains[\s\S]*?array_key_exists\('compose_preanswer_hint'/);
});
test('actual linked getter and x-text contract keep hint markup literal and non-executable', () => {
  const hint = '<img src=x onerror="bad()"> & <script>bad()</script>';
  getter.currentQuestion = {type: 4, authored_compose: true, compose_preanswer_hint: hint};
  const markup = source.match(/<span x-text="currentComposeHint"><\/span>/)[0];
  const dom = new JSDOM(markup);
  const node = dom.window.document.querySelector('[x-text="currentComposeHint"]');
  node.textContent = getter.currentComposeHint;
  assert.equal(node.textContent, hint); assert.equal(node.querySelector('img,script'), null);
  dom.window.close();
});

test('actual linked finite details markup starts closed and a native summary click reveals the escaped hint', () => {
  const markup = source.match(/<details[^>]*data-authored-compose-hint>[\s\S]*?<\/details>/)[0];
  const dom = new JSDOM(markup);
  const detail = dom.window.document.querySelector('details');
  const summary = detail.querySelector('summary');
  const hint = detail.querySelector('[x-text="currentComposeHint"]');
  hint.textContent = '<img src=x> Technical instruction and lemma';
  assert.equal(detail.open, false);
  assert.equal(summary.hasAttribute('aria-expanded'), false, 'Alpine owns the reactive expanded value');
  assert.ok(summary.getAttribute('x-text').includes('practiceI18n.show_help'));
  assert.ok(summary.getAttribute('x-text').includes('practiceI18n.hide_help'));
  summary.click();
  assert.equal(detail.open, true);
  assert.equal(hint.querySelector('img'), null);
  summary.click();
  assert.equal(detail.open, false);
  dom.window.close();
});
