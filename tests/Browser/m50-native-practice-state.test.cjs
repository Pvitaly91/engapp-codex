// Offline actual shipped factory only: no browser, Laravel, .env, DB or network.
const { test, after } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = path.join(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const sha = file => crypto.createHash('sha256').update(fs.readFileSync(path.join(root, file))).digest('hex');
const packagePath = 'app/Support/M50ObligationAdvicePackage.php';
const source = read(packagePath);
function constant(name) {
  const match = source.match(new RegExp('public const ' + name + " = '([^']+)';"));
  assert.ok(match, name); return match[1];
}
const masterPath = constant('MASTER');
assert.match(constant('MASTER_SHA'), /^[a-f0-9]{64}$/);
assert.equal(sha(masterPath), constant('MASTER_SHA'), 'Reviewed immutable M50 master');
const master = JSON.parse(read(masterPath));
const factoryPath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const protectedPaths = [packagePath, masterPath, factoryPath, 'public/js/english-answer-variants.js',
  'public/data/english-contractions.json', 'tests/Browser/load-answer-variants.cjs'];
const protectedBefore = Object.fromEntries(protectedPaths.map(file => [file, sha(file)]));
const lessons = master.lessons.map(lesson => ({ ...lesson,
  data: lesson.blocks.find(block => block.type === 'practice-set').data }));
const groups = ['selects', 'choices', 'inputs'];
const props = { selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers' };
const key = value => String(value).trim().toLowerCase();
const clone = value => JSON.parse(JSON.stringify(value));
const i18n = { empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.',
  answer: 'Правильна відповідь', score: 'Результат: :correct / :total' };
const score = n => 'Результат: ' + n + ' / 2';
const scripts = [...read(factoryPath).matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)]
  .map(match => match[1]).filter(script => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(script));
assert.equal(scripts.length, 1); assert.doesNotMatch(scripts[0], /@(?:js|json|php|endphp)\b|\{\{!!?/);
let listener, factory, networkCalls = 0;
const variants = require(path.join(root, 'tests/Browser/load-answer-variants.cjs'));
const context = vm.createContext({
  window: { EnglishAnswerVariants: variants },
  document: {
    addEventListener(name, fn) { assert.equal(name, 'alpine:init'); listener = fn; },
    querySelector(selector) { assert.match(selector, /^\[data-word-suggestion-input="inputs-\d+"\]$/); return { focus() {} }; },
  },
  Alpine: { data(name, fn) { assert.equal(name, 'theoryPracticeSet'); factory = fn; } },
  fetch() { networkCalls++; throw Error('Network forbidden'); }, console, URL,
});
vm.runInContext(scripts[0], context, { filename: factoryPath, timeout: 1000 }); listener();
function stateFor(lesson) {
  const config = clone({ ...lesson.data, i18n, wordSearchEndpoint: '' });
  const before = JSON.stringify(config); const state = factory(config);
  state.$nextTick = fn => fn(); state.init();
  return { state, unchanged() { assert.equal(JSON.stringify(config), before); } };
}
function orderFor(state, index, answer) {
  const bank = state.inputTokenBank(index), words = Array.from(state.inputTokenWords(answer));
  const used = new Set(), order = []; let position = 0;
  while (position < words.length) {
    const found = bank.findIndex((token, n) => {
      const part = Array.from(state.inputTokenWords(token.value));
      return !used.has(n) && part.length > 0 && part.every((word, offset) => words[position + offset] === word);
    });
    assert.ok(found >= 0, 'Missing canonical token: ' + answer + ' at ' + position);
    used.add(found); order.push(found); position += state.inputTokenWords(bank[found].value).length;
  }
  assert.equal(used.size, bank.length, 'Every canonical token instance must be usable');
  return order;
}
function build(state, index, order) {
  for (const n of order) state.appendInputToken('inputs', index, state.inputTokenBank(index)[n]);
}
const coverage = { lessons: 3, own_tasks: 18, selects: 6, choices: 6, inputs: 6,
  manual_inputs: 0, token_inputs: 0, visible_options: 0, declared_aliases: 0 };

test('frozen source contract: three UK A2 lessons, all three own groups and exact independent linked banks', () => {
  assert.equal(master.version, '1.0.0'); assert.equal(master.base_sha, '4d85f890b8fe4fa0887d2f989581c18eb8cf4534');
  assert.deepEqual(lessons.map(lesson => lesson.slug), ['must-have-to', 'should-ought-to', 'need-neednt-dont-have-to']);
  const banks = [['PolyglotMustHaveToLessonSeeder'], ['PolyglotShouldOughtToLessonSeeder'],
    ['PolyglotGerundVsInfinitiveLessonSeeder', 'PolyglotMustHaveToLessonSeeder']];
  const ids = new Set();
  for (const [n, lesson] of lessons.entries()) {
    assert.equal(lesson.locale, 'uk'); assert.equal(lesson.technical_block_level, 'A2');
    assert.equal(lesson.display_level, 'A2-B1');
    assert.deepEqual(lesson.data.linked_practice.question_types, ['4']);
    assert.deepEqual(lesson.data.linked_practice.seeder_classes, banks[n].map(bank => 'Database\\Seeders\\V3\\Polyglot\\' + bank));
    assert.equal(lesson.data.linked_practice.source, 'theory_links');
    assert.deepEqual(groups.flatMap(group => lesson.data[group].map(item => item.source_index)), [1, 2, 3, 4, 5, 6]);
    for (const group of groups) {
      assert.equal(lesson.data[group].length, 2);
      for (const item of lesson.data[group]) {
        assert.ok(!ids.has(item.id)); ids.add(item.id);
        assert.ok(item.accepted.includes(item.answer));
        assert.equal(new Set(item.accepted).size, item.accepted.length);
        assert.equal(typeof lesson.practice_review.translations[item.id], 'string');
        assert.match(lesson.practice_review.translations[item.id], /[А-Яа-яІіЇїЄєҐґ]/u);
      }
    }
  }
  assert.equal(ids.size, 18);
});

for (const lesson of lessons) {
  test(lesson.slug + ': every visible choice, feedback, retry, repeated check and group reset use the unchanged engine', () => {
    const fixture = stateFor(lesson), state = fixture.state;
    for (const group of ['selects', 'choices']) {
      for (const [index, item] of lesson.data[group].entries()) {
        const options = item.options || (group === 'choices' ? lesson.data.choice_options || ['a', 'b'] : lesson.data.options || []);
        const wrong = options.filter(value => !item.accepted.includes(value));
        assert.ok(wrong.length > 0, item.id + ': explicit wrong candidate');
        for (const option of options) {
          state.resetGroup(group); state[props[group]][index] = option; state.check(group); state.check(group);
          assert.equal(state.isCorrect(group, index), item.accepted.includes(option), item.id + ':' + option);
          assert.equal(state.scoreText(group), score(item.accepted.includes(option) ? 1 : 0));
          assert.equal(state.feedbackText(group, index), item.feedback[key(option)]);
          assert.ok(item.feedback[key(option)].includes(lesson.practice_review.translations[item.id]));
          coverage.visible_options++;
        }
        state.resetGroup(group); state.check(group); assert.equal(state.feedbackText(group, index), i18n.empty);
        for (const option of [wrong[0], wrong[1] || wrong[0]]) {
          state[props[group]][index] = option; state.check(group); assert.equal(state.isCorrect(group, index), false);
          assert.equal(state.scoreText(group), score(0)); assert.match(state.fieldClass(group, index), /rose/);
        }
        state[props[group]][index] = item.answer; state.check(group); state.check(group);
        assert.equal(state.isCorrect(group, index), true); assert.match(state.fieldClass(group, index), /emerald/);
        assert.equal(state.scoreText(group), score(1)); state.resetGroup(group);
      }
    }
    fixture.unchanged();
  });
  for (const [index, item] of lesson.data.inputs.entries()) {
    test(item.id + ': manual aliases, independent wrong probes, full translated feedback and retry lifecycle', () => {
      const fixture = stateFor(lesson), state = fixture.state;
      assert.match(item.after, /[А-Яа-яІіЇїЄєҐґ]/u); assert.ok(item.before.includes('/'));
      assert.ok(!item.before.includes(item.answer)); assert.ok(!item.after.includes(item.answer));
      state.check('inputs'); assert.equal(state.feedbackText('inputs', index), i18n.empty);
      const wrong = lesson.practice_review.wrong_probes[item.id]; assert.equal(typeof wrong, 'string');
      for (let n = 0; n < 2; n++) {
        state.inputAnswers[index] = wrong; state.syncInputTokenBank(index); state.check('inputs');
        assert.equal(state.isCorrect('inputs', index), false, wrong); assert.equal(state.scoreText('inputs'), score(0));
        assert.match(state.fieldClass('inputs', index), /rose/);
        assert.equal(state.feedbackText('inputs', index), item.feedback[key(wrong)]);
        assert.ok(state.feedbackText('inputs', index).includes(lesson.practice_review.translations[item.id]));
      }
      for (const alias of item.accepted) {
        state.inputAnswers[index] = alias; state.syncInputTokenBank(index); state.check('inputs'); state.check('inputs');
        assert.equal(state.isCorrect('inputs', index), true, alias); assert.equal(state.scoreText('inputs'), score(1));
        assert.equal(state.feedbackText('inputs', index), item.feedback[key(alias)]);
        assert.ok(state.feedbackText('inputs', index).includes(lesson.practice_review.translations[item.id])); coverage.declared_aliases++;
      }
      state.resetGroup('inputs'); assert.equal(state.isChecked('inputs'), false); assert.equal(state.userAnswer('inputs', index), '');
      assert.equal(state.scoreText('inputs'), score(0)); coverage.manual_inputs++; fixture.unchanged();
    });
    test(item.id + ': shuffled token instances, deletion/return, punctuation and rebuild', () => {
      const fixture = stateFor(lesson), state = fixture.state, order = orderFor(state, index, item.answer);
      const bank = state.inputTokenBank(index);
      assert.equal(new Set(bank.map(token => token.id)).size, bank.length); assert.notDeepEqual(order, bank.map((_, n) => n));
      assert.ok(bank.every(token => state.inputTokenWords(token.value).length > 0), 'Attached punctuation, no unusable bare punctuation');
      build(state, index, order.slice(0, 1)); assert.equal(state.isCorrect('inputs', index), false);
      const partial = state.userAnswer('inputs', index); build(state, index, order.slice(0, 1)); assert.equal(state.userAnswer('inputs', index), partial);
      build(state, index, order.slice(1)); assert.equal(state.userAnswer('inputs', index), item.answer);
      state.check('inputs'); state.check('inputs'); assert.equal(state.isCorrect('inputs', index), true); assert.equal(state.scoreText('inputs'), score(1));
      assert.ok(state.inputTokenBank(index).every(token => token.used));
      const last = state.inputTokenBank(index)[order.at(-1)].value;
      state.inputAnswers[index] = item.answer.slice(0, -last.length).trimEnd(); state.syncInputTokenBank(index);
      assert.equal(state.isCorrect('inputs', index), false); assert.equal(state.inputTokenBank(index).filter(token => !token.used).length, 1);
      state.inputAnswers[index] = item.answer; state.syncInputTokenBank(index); assert.ok(state.inputTokenBank(index).every(token => token.used));
      state.inputAnswers[index] = item.answer.replace(/[.!?]$/, ''); state.syncInputTokenBank(index); assert.equal(state.isCorrect('inputs', index), true);
      state.resetGroup('inputs'); assert.ok(state.inputTokenBank(index).every(token => !token.used));
      build(state, index, order); state.check('inputs'); assert.equal(state.isCorrect('inputs', index), true);
      coverage.token_inputs++; fixture.unchanged();
    });
  }
  test(lesson.slug + ': 0/2 to1/2 to2/2 scores remain independent across all own groups', () => {
    const fixture = stateFor(lesson), state = fixture.state;
    for (const group of groups) {
      assert.equal(state.scoreText(group), score(0));
      state[props[group]][0] = lesson.data[group][0].answer; state.check(group); assert.equal(state.scoreText(group), score(1));
      state[props[group]][1] = lesson.data[group][1].answer; state.check(group); state.check(group); assert.equal(state.scoreText(group), score(2));
    }
    state.resetGroup('inputs'); assert.equal(state.scoreText('inputs'), score(0));
    assert.equal(state.scoreText('selects'), score(2)); assert.equal(state.scoreText('choices'), score(2)); fixture.unchanged();
  });
}

after(() => {
  assert.equal(networkCalls, 0); assert.equal(coverage.manual_inputs, 6); assert.equal(coverage.token_inputs, 6);
  assert.deepEqual(Object.fromEntries(protectedPaths.map(file => [file, sha(file)])), protectedBefore);
  console.log('M50_STATE_COVERAGE ' + JSON.stringify({ ...coverage, network_calls: networkCalls,
    limitation: 'Offline shipped factory only, not browser visibility/interaction/DB apply/no-JS/print.' }));
});
