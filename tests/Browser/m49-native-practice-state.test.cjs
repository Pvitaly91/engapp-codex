// Frozen M49 offline state acceptance: actual shipped Alpine factory and variants.
// No browser rendering, app boot, DB, network, or copied grading methods.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');

const root = path.join(__dirname, '../..');
const read = relative => fs.readFileSync(path.join(root, relative), 'utf8');
const sha = relative => crypto.createHash('sha256').update(fs.readFileSync(path.join(root, relative))).digest('hex');
const factoryPath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const masterPath = 'docs/content/m49-advice-modals.v1.0.0.json';
const master = JSON.parse(read(masterPath));
assert.equal(master.version, '1.0.0');
assert.equal(sha(masterPath), '554aace8763e3d2183c4ea3533dd781bc9171a5ca27b2a39a64d43c55f9c1662', 'Immutable reviewed M49 master');
const protectedPaths = [factoryPath, masterPath, 'public/js/english-answer-variants.js', 'public/data/english-contractions.json', 'tests/Browser/load-answer-variants.cjs'];
const hashes = Object.fromEntries(protectedPaths.map(relative => [relative, sha(relative)]));
const canonical = {
  'had-better': ['leave', 'had better not', 'I had better call Nina now.', 'Мені краще піти зараз.', "I'd rather sit by the window.", "Hadn't we better check the date first?"],
  'can-could': ['can', 'could', 'Can I open the window?', 'Could you call me after class?', 'I can meet you at five tomorrow.', 'Yesterday I was able to fix the printer.'],
  'may-might': ['may', 'arrive', 'may be', 'may not be', 'a', 'b'],
};
const visibleAlternatives = {
  'can-could/3': ['Could I open the window?'],
  'can-could/4': ['Can you call me after class?'],
  'can-could/6': ['Yesterday I managed to fix the printer.'],
  'may-might/1': ['might'],
  'may-might/4': ['might not be'],
};
const translations = {
  'had-better': ['Нам краще піти зараз.', 'Тобі краще поки не казати Олі.', 'Мені краще зателефонувати Ніні зараз.', 'Мені краще піти зараз.', 'Я волів би сісти біля вікна.', 'Чи не краще нам спочатку перевірити дату?'],
  'can-could': ['Ліна вміє добре плавати.', 'У вісім років Бен умів читати короткі книжки.', 'Можна я відкрию вікно?', 'Не міг би ти подзвонити мені після заняття?', 'Я можу зустрітися з тобою завтра о п’ятій.', 'Учора я зміг полагодити принтер.'],
  'may-might': ['Леся, можливо, завітає до нас завтра.', 'Він, можливо, приїде до обіду.', 'Марк, можливо, запізнюється.', 'Можливо, завтра магазин не буде відчинений.', 'Вам не дозволено брати ці книжки додому.', 'Можна я відчиню вікно?'],
};
// Independently reviewed complete translations of equally accepted outcomes.
// Feedback may teach either accepted request, not just the canonical wording.
const translatedAlternatives = {
  'can-could/3': ['Можна мені відкрити вікно?'],
  'can-could/4': ['Можеш подзвонити мені після заняття?'],
  'can-could/6': ['Учора мені вдалося полагодити принтер.'],
};
const lessons = master.lessons.map(lesson => {
  const practices = lesson.blocks.filter(block => block.type === 'practice-set');
  assert.equal(practices.length, 1, lesson.slug);
  return { ...lesson, data: practices[0].data };
});
const groups = ['selects', 'choices'];
const answerProperties = { selects: 'selectAnswers', choices: 'choiceAnswers' };
const clone = value => JSON.parse(JSON.stringify(value));
const expected = (lesson, item, value) => canonical[lesson.slug][item.source_index - 1] === value || (visibleAlternatives[lesson.slug + '/' + item.source_index] || []).includes(value);
const options = (lesson, group, item) => item.options || (group === 'choices' ? lesson.data.choice_options || ['a', 'b'] : lesson.data.options || []);
const feedbackKey = value => String(value).trim().toLowerCase();
const i18n = { empty: 'Обери відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.', answer: 'Правильна відповідь', score: 'Результат: :correct / :total' };
const scripts = [...read(factoryPath).matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(match => match[1]).filter(script => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(script));
assert.equal(scripts.length, 1);
assert.doesNotMatch(scripts[0], /@(?:js|json|php|endphp)\b|\{\{!!?/);
let initListener;
let factory;
let networkCalls = 0;
const variants = require(path.join(root, 'tests/Browser/load-answer-variants.cjs'));
const context = vm.createContext({
  window: { EnglishAnswerVariants: variants },
  document: { addEventListener(name, listener) { assert.equal(name, 'alpine:init'); initListener = listener; }, querySelector() { throw Error('No DOM audit'); } },
  Alpine: { data(name, value) { assert.equal(name, 'theoryPracticeSet'); factory = value; } },
  fetch() { networkCalls++; throw Error('Network forbidden'); }, console, URL,
});
vm.runInContext(scripts[0], context, { filename: factoryPath, timeout: 1000 });
initListener();
assert.equal(typeof factory, 'function');
function stateFor(lesson) {
  const config = clone({ selects: lesson.data.selects || [], choices: lesson.data.choices || [], inputs: lesson.data.inputs || [], rephrase: lesson.data.rephrase || [], i18n, wordSearchEndpoint: '' });
  const initial = JSON.stringify(config);
  const state = factory(config);
  state.init();
  return { state, unchanged() { assert.equal(JSON.stringify(config), initial); } };
}
const select = (state, group, index, value) => { state[answerProperties[group]][index] = value; };
const score = (lesson, group, n) => 'Результат: ' + n + ' / ' + (lesson.data[group] || []).length;
function locate(slug, sourceIndex) {
  const lesson = lessons.find(lesson => lesson.slug === slug);
  for (const group of groups) {
    const index = (lesson.data[group] || []).findIndex(item => item.source_index === sourceIndex);
    if (index >= 0) return { lesson, group, index, item: lesson.data[group][index] };
  }
  assert.fail(slug + '/' + sourceIndex);
}
function combinations(arrays) { return arrays.reduce((rows, choices) => rows.flatMap(row => choices.map(value => [...row, value])), [[]]); }

test('frozen inventory: three UK lessons, two exact categories, 18 controls and independent canonical answers', () => {
  assert.deepEqual(lessons.map(lesson => lesson.slug), Object.keys(canonical));
  let count = 0;
  let visibleCount = 0;
  let correctVisibleCount = 0;
  for (const lesson of lessons) {
    assert.equal(lesson.locale, 'uk');
    assert.match(lesson.identity, lesson.slug === 'had-better' ? /\\Conditionals\\ConditionalsHadBetterTheorySeeder$/ : /\\ModalVerbs\\ModalVerbs(?:CanCould|MayMight)TheorySeeder$/);
    assert.equal((lesson.data.inputs || []).length, 0);
    assert.equal((lesson.data.rephrase || []).length, 0);
    const items = groups.flatMap(group => lesson.data[group] || []);
    assert.equal(items.length, 6);
    assert.deepEqual(items.map(item => item.source_index).sort((a, b) => a - b), [1, 2, 3, 4, 5, 6]);
    for (const group of groups) for (const item of lesson.data[group] || []) {
      count++;
      assert.equal(item.answer, canonical[lesson.slug][item.source_index - 1]);
      assert.ok(item.accepted.includes(item.answer));
      const candidates = options(lesson, group, item);
      assert.equal(candidates.length, new Set(candidates).size);
      assert.ok(candidates.some(value => !expected(lesson, item, value)));
      for (const value of candidates) {
        visibleCount++;
        if (expected(lesson, item, value)) correctVisibleCount++;
        const feedback = item.feedback[feedbackKey(value)];
        assert.equal(typeof feedback, 'string', lesson.slug + '/' + item.source_index + ':' + value);
        assert.match(feedback, /[А-Яа-яІіЇїЄєҐґ]/u);
        const completeTranslations = [translations[lesson.slug][item.source_index - 1], ...(translatedAlternatives[lesson.slug + '/' + item.source_index] || [])];
        assert.ok(completeTranslations.some(translation => feedback.includes(translation)), lesson.slug + '/' + item.source_index + ': exact full translated outcome');
      }
    }
  }
  assert.equal(count, 18);
  assert.equal(visibleCount, 47);
  assert.equal(correctVisibleCount, 23);
});

for (const lesson of lessons) {
  test(lesson.slug + ': every visible option receives independent semantic grade, exact authored feedback and one-or-zero score', () => {
    const fixture = stateFor(lesson);
    const { state } = fixture;
    for (const group of groups) for (const [index, item] of (lesson.data[group] || []).entries()) for (const value of options(lesson, group, item)) {
      state.resetGroup(group);
      select(state, group, index, value);
      assert.equal(state.fieldClass(group, index), '');
      state.check(group);
      const correct = expected(lesson, item, value);
      assert.equal(state.isCorrect(group, index), correct, item.id + ':' + value);
      assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(value)]);
      assert.equal(state.scoreText(group), score(lesson, group, correct ? 1 : 0));
      assert.equal(state.userAnswer(group, index), value);
    }
    fixture.unchanged();
  });
  test(lesson.slug + ': all group option combinations derive current scores without duplicate credit', () => {
    const fixture = stateFor(lesson);
    const { state } = fixture;
    for (const group of groups) {
      const items = lesson.data[group] || [];
      for (const values of combinations(items.map(item => options(lesson, group, item)))) {
        state.resetGroup(group);
        values.forEach((value, index) => select(state, group, index, value));
        state.check(group); state.check(group);
        const correct = values.filter((value, index) => expected(lesson, items[index], value)).length;
        assert.equal(state.scoreText(group), score(lesson, group, correct));
        values.forEach((value, index) => assert.equal(state.feedbackText(group, index), items[index].feedback[feedbackKey(value)]));
      }
    }
    fixture.unchanged();
  });
  test(lesson.slug + ': empty, partial, all correct, repeated check and independent reset', () => {
    const fixture = stateFor(lesson);
    const { state } = fixture;
    for (const group of groups) {
      const items = lesson.data[group] || [];
      state.check(group);
      assert.equal(state.scoreText(group), score(lesson, group, 0));
      items.forEach((item, index) => assert.equal(state.feedbackText(group, index), i18n.empty));
      select(state, group, 0, canonical[lesson.slug][items[0].source_index - 1]);
      assert.equal(state.scoreText(group), score(lesson, group, 1));
      items.slice(1).forEach((item, offset) => assert.equal(state.isEmpty(group, offset + 1), true));
      items.forEach((item, index) => select(state, group, index, canonical[lesson.slug][item.source_index - 1]));
      state.check(group); state.check(group);
      assert.equal(state.scoreText(group), score(lesson, group, items.length));
    }
    state.resetGroup('selects');
    assert.equal(state.scoreText('selects'), score(lesson, 'selects', 0));
    assert.equal(state.isChecked('selects'), false);
    assert.equal(state.scoreText('choices'), score(lesson, 'choices', lesson.data.choices.length));
    assert.equal(state.isChecked('choices'), true);
    state.resetGroup('choices');
    assert.equal(state.isChecked('choices'), false);
    assert.equal(state.scoreText('choices'), score(lesson, 'choices', 0));
    fixture.unchanged();
  });
  test(lesson.slug + ': two wrong attempts, edit correct, cleared stale error, repeat and reset on every control', () => {
    for (const group of groups) for (const [index, item] of (lesson.data[group] || []).entries()) {
      const fixture = stateFor(lesson);
      const { state } = fixture;
      const wrong = options(lesson, group, item).filter(value => !expected(lesson, item, value));
      for (const value of [wrong[0], wrong[1] || wrong[0]]) {
        select(state, group, index, value); state.check(group);
        assert.equal(state.isCorrect(group, index), false);
        assert.match(state.fieldClass(group, index), /rose/);
        assert.equal(state.scoreText(group), score(lesson, group, 0));
      }
      const stale = state.feedbackText(group, index);
      const correct = canonical[lesson.slug][item.source_index - 1];
      select(state, group, index, correct);
      assert.equal(state.isCorrect(group, index), true);
      assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(correct)]);
      assert.notEqual(state.feedbackText(group, index), stale);
      assert.match(state.fieldClass(group, index), /emerald/);
      assert.doesNotMatch(state.fieldClass(group, index), /rose/);
      state.check(group); state.check(group);
      assert.equal(state.scoreText(group), score(lesson, group, 1));
      select(state, group, index, '');
      assert.equal(state.feedbackText(group, index), i18n.empty);
      state.resetGroup(group);
      assert.equal(state.userAnswer(group, index), '');
      assert.equal(state.fieldClass(group, index), '');
      fixture.unchanged();
    }
  });
  test(lesson.slug + ': every declared alias retains grading with case and whitespace normalization', () => {
    const fixture = stateFor(lesson);
    const { state } = fixture;
    for (const group of groups) for (const [index, item] of (lesson.data[group] || []).entries()) for (const alias of item.accepted) {
      for (const value of [alias, '  ' + alias.toUpperCase() + '  ']) {
        select(state, group, index, value); state.check(group);
        assert.equal(state.isCorrect(group, index), true, item.id + ':' + value);
        assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(value)] || i18n.correct);
      }
    }
    fixture.unchanged();
  });
}

test('had better contraction expands as had; rather expands as would; not is not lost', () => {
  for (const [sourceIndex, accepted, rejected] of [
    [3, ['I had better call Nina now.', "I'd better call Nina now.", 'I’d better call Nina now.'], ['I would better call Nina now.']],
    [5, ["I'd rather sit by the window.", 'I’d rather sit by the window.', 'I would rather sit by the window.'], ['I had rather sit by the window.', "I'd better sit by the window."]],
    [6, ["Hadn't we better check the date first?", 'Hadn’t we better check the date first?', 'Had we not better check the date first?'], ["Don't we had better check the date first?", 'Had we better check the date first?']],
  ]) {
    const { lesson, group, index } = locate('had-better', sourceIndex);
    const fixture = stateFor(lesson);
    for (const value of accepted) { select(fixture.state, group, index, value); assert.equal(fixture.state.isCorrect(group, index), true, value); }
    for (const value of rejected) { select(fixture.state, group, index, value); assert.equal(fixture.state.isCorrect(group, index), false, value); }
    fixture.unchanged();
  }
});

test('two may/might alternatives in each uncertainty control never double score', () => {
  for (const sourceIndex of [1, 4]) {
    const { lesson, group, index, item } = locate('may-might', sourceIndex);
    const fixture = stateFor(lesson);
    for (const value of [...item.accepted, ...item.accepted]) {
      select(fixture.state, group, index, value); fixture.state.check(group); fixture.state.check(group);
      assert.equal(fixture.state.isCorrect(group, index), true);
      assert.equal(fixture.state.scoreText(group), score(lesson, group, 1));
      assert.equal(fixture.state.feedbackText(group, index), item.feedback[feedbackKey(value)]);
    }
    if (sourceIndex === 4) {
      select(fixture.state, group, index, 'may be');
      assert.equal(fixture.state.isCorrect(group, index), false, 'not must remain semantic');
      select(fixture.state, group, index, "mightn't be");
      assert.equal(fixture.state.isCorrect(group, index), true, 'existing full/contracted negative handling');
    }
    fixture.unchanged();
  }
});

test('can/could permissions, requests and completed success preserve all stated alternatives as one control', () => {
  for (const sourceIndex of [3, 4, 6]) {
    const { lesson, group, index, item } = locate('can-could', sourceIndex);
    const fixture = stateFor(lesson);
    assert.equal(item.accepted.length, 2);
    for (const value of [...item.accepted, ...item.accepted]) {
      select(fixture.state, group, index, value); fixture.state.check(group); fixture.state.check(group);
      assert.equal(fixture.state.isCorrect(group, index), true);
      assert.equal(fixture.state.scoreText(group), score(lesson, group, 1));
    }
    fixture.unchanged();
  }
});

test('punctuation normalization does not change actor, negation or the question word order', () => {
  for (const [slug, sourceIndex, good, bad] of [
    ['can-could', 3, ['Can I open the window', 'Could I open the window ?'], ['Can you open the window?', 'I can open the window?']],
    ['can-could', 5, ['I can meet you at five tomorrow'], ["I can't meet you at five tomorrow.", 'I cannot meet you at five tomorrow.']],
    ['had-better', 2, ['had better not'], ['had better', 'had not better']],
    ['may-might', 3, ['may be'], ['maybe', 'might']],
  ]) {
    const { lesson, group, index } = locate(slug, sourceIndex);
    const fixture = stateFor(lesson);
    for (const value of good) { select(fixture.state, group, index, value); assert.equal(fixture.state.isCorrect(group, index), true, value); }
    for (const value of bad) { select(fixture.state, group, index, value); assert.equal(fixture.state.isCorrect(group, index), false, value); }
    fixture.unchanged();
  }
});

test('no protected master/factory/answer-variant bytes change and no network calls occur', () => {
  assert.equal(networkCalls, 0);
  assert.deepEqual(Object.fromEntries(protectedPaths.map(relative => [relative, sha(relative)])), hashes);
  console.log('M49_PROTECTED ' + JSON.stringify(hashes));
  console.log('M49_COVERAGE ' + JSON.stringify({ lessons: 3, controls: 18, visible_options: 47, accepted_correct_visible_options: 23,
    group_combinations: lessons.reduce((sum, lesson) => sum + groups.reduce((n, group) => n + (lesson.data[group] || []).reduce((product, item) => product * options(lesson, group, item).length, 1), 0), 0),
    limit: 'Offline actual-factory state only. No browser, DB, local apply, or unrelated regression suite claim.' }));
});
