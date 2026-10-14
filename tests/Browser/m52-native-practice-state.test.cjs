// Offline unchanged shipped factory only. No browser, Laravel, working .env, DB or network.
const { test, after } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = path.join(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const sha = file => crypto.createHash('sha256').update(fs.readFileSync(path.join(root, file))).digest('hex');
const packagePath = 'app/Support/M52PassiveBasicsPackage.php';
const packageSource = read(packagePath);
function constant(name) {
  const match = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';"));
  assert.ok(match, name); return match[1];
}
const draftReview = process.env.M52_DRAFT_REVIEW === '1';
const masterPath = draftReview ? 'docs/content/m52-passive-basics.DRAFT.json' : constant('MASTER');
const masterBytes = fs.readFileSync(path.join(root, masterPath));
const actualMasterSha = crypto.createHash('sha256').update(masterBytes).digest('hex');
const master = JSON.parse(masterBytes.toString('utf8'));
if (draftReview) {
  assert.equal(constant('MASTER_SHA'), 'NOT_FROZEN', 'Draft review cannot masquerade as a frozen release run');
  assert.equal(master.status, 'draft', 'Explicit mutable draft source required');
  assert.match(masterPath, /\.DRAFT\.json$/);
} else {
  assert.match(constant('MASTER_SHA'), /^[a-f0-9]{64}$/, 'Draft sentinel cannot be accepted as frozen');
  assert.equal(actualMasterSha, constant('MASTER_SHA'), 'Independent reviewed frozen M52 author source');
  assert.equal(master.status, 'frozen');
}
const factoryPath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const factorySource = read(factoryPath);
const shippedHashes = {
  'resources/views/engram/theory/blocks-v3/practice-set.blade.php': 'ad7dd5027fb0202cf595ecc4d358e7329bcf7307ad52d93179557448171fb666',
  'public/js/english-answer-variants.js': '7000925d6de41fdb6671457bd8d1d7766b9c5b05d2c3d5fe0f1195f80666c681',
  'public/data/english-contractions.json': '2815805630fc1987314c9632a055cf3feced856372f73cb29c2a6ade80cb6491',
  'tests/Browser/load-answer-variants.cjs': '8b3f5690a441e0455d2691de3c59f85fd83e5f8d85429f5b2c5197722b14aa1e',
};
for (const [file, expectedSha] of Object.entries(shippedHashes)) assert.equal(sha(file), expectedSha, 'Unchanged shipped engine: ' + file);
const protectedPaths = [packagePath, masterPath, factoryPath, 'public/js/english-answer-variants.js',
  'public/data/english-contractions.json', 'tests/Browser/load-answer-variants.cjs'];
const protectedBefore = Object.fromEntries(protectedPaths.map(file => [file, sha(file)]));
const lessons = master.lessons.map(lesson => ({ ...lesson,
  data: lesson.blocks.find(block => block.type === 'practice-set').data }));
const groups = ['selects', 'choices', 'inputs'];
const properties = { selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers' };
const feedbackKey = value => String(value).trim().toLowerCase();
const clone = value => JSON.parse(JSON.stringify(value));
const i18n = { empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.',
  answer: 'Правильна відповідь', score: 'Результат: :correct / :total' };
const score = number => 'Результат: ' + number + ' / 2';

// Editorial strings declared here independently, never inferred from generated AFTER or the answer engine.
const expected = {
  'm52-formation-plural-present': { en: ['The parcels are sorted here every day.'], uk: ['Посилки сортують тут щодня.'] },
  'm52-formation-v3-past': { en: ['The sign was written yesterday.'], uk: ['Цю табличку написали вчора.'] },
  'm52-formation-retain-agent': { en: ['The suitcase was packed by Sofia.'], uk: ['Валізу спакувала Софія.'] },
  'm52-formation-cause-instrument': { en: ['The fence was damaged by the storm.', 'The paper was cut with scissors.'], uk: ['Буря пошкодила паркан.', 'Папір розрізали ножицями.'] },
  'm52-formation-build-past': { en: ['The shelves were repaired by Nora yesterday.'], uk: ['Полиці вчора полагодила Нора.'] },
  'm52-formation-build-present': { en: ['The maps are printed by Iryna.'], uk: ['Карти друкує Ірина.'] },
  'm52-negative-perfect-not': { en: ['The report has not been sent yet.'], uk: ['Звіт ще не надіслали.'] },
  'm52-negative-future-answer': { en: ['Will the windows be washed tomorrow? — Yes, they will.'], uk: ['Вікна помиють завтра? — Так.'] },
  'm52-negative-who-patient': { en: ['Who was invited to the meeting?'], uk: ['Кого запросили на зустріч?'] },
  'm52-negative-neutral-inversion': { en: ['Were the keys found?'], uk: ['Ключі знайшли?'] },
  'm52-negative-build-question': { en: ['Was the parcel sent on Monday?'], uk: ['Посилку надіслали в понеділок?'] },
  'm52-negative-build-perfect': { en: ['The plans have not been approved yet.'], uk: ['Плани ще не затвердили.'] },
  'm52-modal-must-passive': { en: ['The battery must be checked before the flight.'], uk: ['Перед польотом акумулятор треба перевірити.'] },
  'm52-modal-perfect-been': { en: ['The file should have been copied yesterday.'], uk: ['Файл слід було скопіювати учора.'] },
  'm52-modal-no-obligation': { en: ["The receipt doesn't have to be printed."], uk: ['Чек не обов’язково друкувати.'] },
  'm52-modal-perfect-expectation': { en: ['The notice should have been sent by now.'], uk: ['Повідомлення, очікується, вже мали відправити.'] },
  'm52-modal-build-prohibition': { en: ['The gate must not be opened during the test.'], uk: ['Під час випробування ворота відчиняти заборонено.'] },
  'm52-modal-build-perfect-agent': { en: ['The cables should have been checked by Olha.'], uk: ['Кабелі слід було перевірити Ользі.'] },
};
const expectedInputAliases = {
  'm52-formation-build-past': ['The shelves were repaired by Nora yesterday.'],
  'm52-formation-build-present': ['The maps are printed by Iryna.'],
  'm52-negative-build-question': ['Was the parcel sent on Monday?'],
  'm52-negative-build-perfect': ['The plans have not been approved yet.', "The plans haven't been approved yet."],
  'm52-modal-build-prohibition': ['The gate must not be opened during the test.', "The gate mustn't be opened during the test."],
  'm52-modal-build-perfect-agent': ['The cables should have been checked by Olha.', "The cables should've been checked by Olha."],
};
const wrongProbes = {
  'm52-formation-build-past': 'The shelves was repaired by Nora yesterday.',
  'm52-formation-build-present': 'The maps is printed by Iryna.',
  'm52-negative-build-question': 'The parcel was sent on Monday?',
  'm52-negative-build-perfect': 'The plans have been not approved yet.',
  'm52-modal-build-prohibition': "The gate needn't be opened during the test.",
  'm52-modal-build-perfect-agent': 'The cables should have be checked by Olha.',
};
// Finite pedagogical visibility expectations: N/Q explicitly asks for the UK
// target/question model, while the other four choices test an English stimulus.
const deferredMeaningTranslationIds = new Set([
  'm52-formation-retain-agent', 'm52-formation-cause-instrument',
  'm52-modal-no-obligation', 'm52-modal-perfect-expectation',
]);
const visibleQuestionCriteria = {
  'm52-negative-who-patient': 'Кого запросили на зустріч?',
  'm52-negative-neutral-inversion': 'звичайною yes/no-моделлю з інверсією',
};

const scripts = [...factorySource.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)]
  .map(match => match[1]).filter(script => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(script));
assert.equal(scripts.length, 1);
assert.doesNotMatch(scripts[0], /@(?:js|json|php|endphp)\b|\{\{!!?/);
let listener, factory, networkCalls = 0;
const variants = require(path.join(root, 'tests/Browser/load-answer-variants.cjs'));
const context = vm.createContext({
  window: { EnglishAnswerVariants: variants },
  document: {
    addEventListener(name, callback) { assert.equal(name, 'alpine:init'); listener = callback; },
    querySelector(selector) {
      assert.match(selector, /^\[data-word-suggestion-input="inputs-\d+"\]$/);
      return { focus() {} };
    },
  },
  Alpine: { data(name, callback) { assert.equal(name, 'theoryPracticeSet'); factory = callback; } },
  fetch() { networkCalls++; throw Error('Network forbidden in native practice state test'); }, console, URL,
});
vm.runInContext(scripts[0], context, { filename: factoryPath, timeout: 1000 }); listener();
function stateFor(lesson) {
  const config = clone({ ...lesson.data, i18n, wordSearchEndpoint: '' });
  const before = JSON.stringify(config); const state = factory(config);
  state.$nextTick = callback => callback(); state.init();
  return { state, unchanged() { assert.equal(JSON.stringify(config), before, 'Factory does not mutate author data'); } };
}
function assertFeedback(item, actual) {
  assert.equal(typeof actual, 'string');
  for (const ukrainian of expected[item.id].uk) assert.ok(actual.includes(ukrainian), item.id + ': full precise UK translation');
  const aliases = expectedInputAliases[item.id];
  if (aliases) assert.ok(aliases.some(sentence => actual.includes(sentence)), item.id + ': full canonical/accepted English sentence');
  else for (const english of expected[item.id].en) assert.ok(actual.includes(english), item.id + ': full English sentence, not only gap answer');
}
function canonicalOrder(state, index, answer) {
  const bank = state.inputTokenBank(index); const words = Array.from(state.inputTokenWords(answer));
  const used = new Set(), order = []; let position = 0;
  while (position < words.length) {
    const found = bank.findIndex((token, number) => {
      const part = Array.from(state.inputTokenWords(token.value));
      return !used.has(number) && part.length > 0 && part.every((word, offset) => words[position + offset] === word);
    });
    assert.ok(found >= 0, 'Missing canonical token at ' + position + ': ' + answer);
    used.add(found); order.push(found); position += state.inputTokenWords(bank[found].value).length;
  }
  assert.equal(used.size, bank.length, 'Canonical answer uses every token instance once');
  return order;
}
function build(state, index, order) {
  for (const number of order) state.appendInputToken('inputs', index, state.inputTokenBank(index)[number]);
}
const coverage = { lessons: 3, own_tasks: 18, selects: 6, choices: 6, inputs: 6,
  manual_inputs: 0, token_inputs: 0, visible_options: 0, declared_aliases: 0,
  choices_feedback_after_check: 0, meaning_translations_deferred: 0,
  explicit_uk_question_criteria: 0, checked_task_ids: new Set() };

test((draftReview ? 'explicit draft-only author contract' : 'frozen author contract') + ': exact three UK owners, mixed technical levels, 2/2/2 and independent themes', () => {
  assert.equal(master.version, '1.0.0');
  assert.equal(master.base_sha, '0e2ed40c438400e2f2a3992314220fdf206db9a7');
  assert.deepEqual(lessons.map(lesson => lesson.slug), ['theory-passive-voice-formation-rules', 'theory-passive-voice-negatives-questions', 'theory-passive-voice-modal-verbs']);
  const technical = ['A2', 'B1', 'B1'], display = ['A2', 'B1', 'B1'];
  const continuing = [9, 11, 11];
  const banks = ['PolyglotPassiveVoiceBasicsLessonSeeder', 'PolyglotPassiveVoiceNegativesQuestionsAllLevelsLessonSeeder', 'PolyglotPassiveVoiceModalVerbsAllLevelsLessonSeeder'];
  const ids = [];
  for (const [number, lesson] of lessons.entries()) {
    assert.equal(lesson.locale, 'uk'); assert.equal(lesson.technical_block_level, technical[number]);
    assert.equal(lesson.display_level, display[number]);
    assert.deepEqual(lesson.blocks.slice(0, continuing[number]).map(block => block.existing_source_index), Array.from({ length: continuing[number] }, (_, i) => i));
    assert.equal(lesson.blocks.at(-1).type, 'practice-set');
    assert.equal(lesson.blocks.at(-1).existing_source_index, null);
    assert.equal(lesson.blocks.at(-1).column, 'left');
    assert.deepEqual(lesson.data.linked_practice.question_types, ['4']);
    assert.deepEqual(lesson.data.linked_practice.seeder_classes, ['Database\\Seeders\\V3\\Polyglot\\' + banks[number]]);
    assert.equal(lesson.data.linked_practice.source, 'theory_links');
    assert.deepEqual(groups.flatMap(group => lesson.data[group].map(item => item.source_index)), [1, 2, 3, 4, 5, 6]);
    for (const group of groups) {
      assert.equal(lesson.data[group].length, 2);
      for (const item of lesson.data[group]) {
        assert.ok(expected[item.id]); ids.push(item.id);
        const supportedKeys = group === 'inputs'
          ? ['id', 'source_index', 'before', 'after', 'answer', 'accepted', 'feedback']
          : ['id', 'source_index', 'label', ...(group === 'choices' ? ['prompt'] : []), 'options', 'answer', 'accepted', 'feedback'];
        assert.deepEqual(Object.keys(item).sort(), supportedKeys.sort(), item.id + ': only supported visible condition/control and answer fields');
        assert.ok(item.accepted.includes(item.answer));
        assert.equal(new Set(item.accepted).size, item.accepted.length);
        if (group === 'inputs') assert.deepEqual(item.accepted, expectedInputAliases[item.id]);
        else { assert.equal(item.options.length, 3); assert.equal(new Set(item.options).size, 3); }
      }
    }
  }
  assert.deepEqual(ids, Object.keys(expected)); assert.equal(new Set(ids).size, 18);
});

for (const lesson of lessons) {
  test(lesson.slug + ': every visible form/meaning option, empty/partial/wrong/retry/correct/recheck/reset uses unchanged engine', () => {
    const fixture = stateFor(lesson), state = fixture.state;
    for (const group of ['selects', 'choices']) {
      for (const [index, item] of lesson.data[group].entries()) {
        const options = item.options || (group === 'choices' ? lesson.data.choice_options || ['a', 'b'] : lesson.data.options || []);
        const wrong = options.filter(option => !item.accepted.includes(option));
        assert.ok(wrong.length > 0, item.id + ': explicit wrong alternative');
        state.resetGroup(group); state.check(group);
        assert.equal(state.feedbackText(group, index), i18n.empty); assert.equal(state.scoreText(group), score(0));
        for (const option of options) {
          state.resetGroup(group); state[properties[group]][index] = option; state.check(group); state.check(group);
          assert.equal(state.isCorrect(group, index), item.accepted.includes(option), item.id + ':' + option);
          assert.equal(state.scoreText(group), score(item.accepted.includes(option) ? 1 : 0));
          assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(option)]);
          assertFeedback(item, state.feedbackText(group, index)); coverage.visible_options++;
        }
        state.resetGroup(group);
        for (let repeat = 0; repeat < 2; repeat++) {
          state[properties[group]][index] = wrong[0]; state.check(group);
          assert.equal(state.isCorrect(group, index), false); assert.equal(state.scoreText(group), score(0));
          assert.match(state.fieldClass(group, index), /rose/); assertFeedback(item, state.feedbackText(group, index));
        }
        state[properties[group]][index] = item.answer; state.check(group); state.check(group);
        assert.equal(state.isCorrect(group, index), true); assert.equal(state.scoreText(group), score(1));
        assert.match(state.fieldClass(group, index), /emerald/); assertFeedback(item, state.feedbackText(group, index));
        state.resetGroup(group); assert.equal(state.isChecked(group), false); assert.equal(state.userAnswer(group, index), '');
        assert.equal(state.scoreText(group), score(0)); coverage.checked_task_ids.add(item.id);
      }
    }
    fixture.unchanged();
  });

  test(lesson.slug + ': finite UK criteria/stimulus visibility and all feedback stay Check-guarded', () => {
    const fixture = stateFor(lesson), state = fixture.state;
    // The actual shipped template owns visibility; serialized answers are not a no-JS key.
    assert.ok(factorySource.includes('x-show="isChecked(\'choices\') && hasAnswer(\'choices\', {{ $index }})"'));
    for (const [index, item] of lesson.data.choices.entries()) {
      const ukrainian = expected[item.id].uk[0];
      if (deferredMeaningTranslationIds.has(item.id)) {
        assert.ok(!String(item.label || '').includes(ukrainian));
        assert.ok(!String(item.prompt || '').includes(ukrainian));
        assert.match(item.prompt, /lang="en"/, 'English meaning stimulus shown without ready UK meaning');
        coverage.meaning_translations_deferred++;
      } else {
        assert.equal(item.prompt, '', 'Question criteria do not contain a duplicate English stimulus');
        assert.ok(item.label.includes(visibleQuestionCriteria[item.id]), 'Required UK target/model is visible');
        coverage.explicit_uk_question_criteria++;
      }
      state.resetGroup('choices'); state.choiceAnswers[index] = item.answer;
      assert.equal(state.isChecked('choices'), false, 'Feedback visibility guard stays closed before Check');
      state.check('choices'); assert.equal(state.isChecked('choices'), true);
      assertFeedback(item, state.feedbackText('choices', index));
      state.resetGroup('choices'); assert.equal(state.isChecked('choices'), false);
      coverage.choices_feedback_after_check++;
    }
    fixture.unchanged();
  });

  for (const [index, item] of lesson.data.inputs.entries()) {
    test(item.id + ': manual empty/partial/wrong/repeated wrong/correct, every declared alias and full translated feedback', () => {
      const fixture = stateFor(lesson), state = fixture.state;
      assert.match(item.after, /[А-Яа-яІіЇїЄєҐґ]/u); assert.ok(item.before.includes('/'));
      assert.ok(!item.before.includes(item.answer)); assert.ok(!item.after.includes(item.answer));
      assert.ok(!('hint' in item) && !('placeholder' in item), 'No extra hidden hint/ready-answer field');
      state.check('inputs'); assert.equal(state.feedbackText('inputs', index), i18n.empty);
      assert.equal(state.scoreText('inputs'), score(0));
      state.inputAnswers[index] = item.answer.split(/\s+/).slice(0, 2).join(' '); state.syncInputTokenBank(index); state.check('inputs');
      assert.equal(state.isCorrect('inputs', index), false); assert.equal(state.scoreText('inputs'), score(0));
      const wrong = wrongProbes[item.id]; assert.equal(typeof wrong, 'string');
      for (let repeat = 0; repeat < 2; repeat++) {
        state.inputAnswers[index] = wrong; state.syncInputTokenBank(index); state.check('inputs');
        assert.equal(state.isCorrect('inputs', index), false, wrong); assert.equal(state.scoreText('inputs'), score(0));
        assert.match(state.fieldClass('inputs', index), /rose/);
        assert.equal(state.feedbackText('inputs', index), item.feedback[feedbackKey(wrong)]);
        assertFeedback(item, state.feedbackText('inputs', index));
      }
      for (const alias of expectedInputAliases[item.id]) {
        state.inputAnswers[index] = alias; state.syncInputTokenBank(index); state.check('inputs'); state.check('inputs');
        assert.equal(state.isCorrect('inputs', index), true, alias); assert.equal(state.scoreText('inputs'), score(1));
        assert.match(state.fieldClass('inputs', index), /emerald/);
        assert.equal(state.feedbackText('inputs', index), item.feedback[feedbackKey(alias)]);
        assertFeedback(item, state.feedbackText('inputs', index)); coverage.declared_aliases++;
      }
      state.inputAnswers[index] = item.answer.replace(/[.!?]$/, ''); state.syncInputTokenBank(index); state.check('inputs');
      assert.equal(state.isCorrect('inputs', index), true); assertFeedback(item, state.feedbackText('inputs', index));
      state.resetGroup('inputs'); assert.equal(state.isChecked('inputs'), false); assert.equal(state.userAnswer('inputs', index), '');
      assert.equal(state.scoreText('inputs'), score(0));
      coverage.manual_inputs++; coverage.checked_task_ids.add(item.id); fixture.unchanged();
    });

    test(item.id + ': complete shuffled canonical token multiset, used instances, deletion/return and rebuild/reset', () => {
      const fixture = stateFor(lesson), state = fixture.state, order = canonicalOrder(state, index, item.answer);
      const bank = state.inputTokenBank(index), declaredTokens = item.before.split('/').map(token => token.trim()).filter(Boolean);
      assert.deepEqual(Array.from(bank, token => token.value), declaredTokens);
      assert.equal(new Set(bank.map(token => token.id)).size, bank.length);
      assert.notDeepEqual(order, Array.from(bank, (_, number) => number), 'Declared bank remains shuffled, not a VM-realm prototype mismatch');
      assert.ok(bank.every(token => state.inputTokenWords(token.value).length > 0), 'Each native input token is usable; punctuation remains attached');
      build(state, index, order.slice(0, 1)); const partial = state.userAnswer('inputs', index);
      state.check('inputs'); assert.equal(state.isCorrect('inputs', index), false); assert.equal(state.scoreText('inputs'), score(0));
      build(state, index, order.slice(0, 1)); assert.equal(state.userAnswer('inputs', index), partial, 'Used token cannot be selected twice');
      build(state, index, order.slice(1)); assert.equal(state.userAnswer('inputs', index), item.answer);
      state.check('inputs'); state.check('inputs'); assert.equal(state.isCorrect('inputs', index), true);
      assert.equal(state.scoreText('inputs'), score(1)); assertFeedback(item, state.feedbackText('inputs', index));
      assert.ok(state.inputTokenBank(index).every(token => token.used));
      const last = state.inputTokenBank(index)[order.at(-1)].value;
      state.inputAnswers[index] = item.answer.slice(0, -last.length).trimEnd(); state.syncInputTokenBank(index); state.check('inputs');
      assert.equal(state.isCorrect('inputs', index), false); assert.equal(state.scoreText('inputs'), score(0));
      assert.equal(state.inputTokenBank(index).filter(token => !token.used).length, 1, 'Deleted last token returns to bank');
      state.inputAnswers[index] = item.answer; state.syncInputTokenBank(index); state.check('inputs');
      assert.ok(state.inputTokenBank(index).every(token => token.used)); assert.equal(state.isCorrect('inputs', index), true);
      state.resetGroup('inputs'); assert.ok(state.inputTokenBank(index).every(token => !token.used));
      build(state, index, order); state.check('inputs'); state.check('inputs');
      assert.equal(state.isCorrect('inputs', index), true); assert.equal(state.scoreText('inputs'), score(1));
      state.resetGroup('inputs'); assert.equal(state.userAnswer('inputs', index), ''); assert.equal(state.isChecked('inputs'), false);
      coverage.token_inputs++; fixture.unchanged();
    });
  }

  test(lesson.slug + ': partial groups and repeated checks do not inflate score or cross-mutate groups', () => {
    const fixture = stateFor(lesson), state = fixture.state;
    for (const group of groups) {
      assert.equal(state.scoreText(group), score(0));
      state[properties[group]][0] = lesson.data[group][0].answer; state.check(group); state.check(group);
      assert.equal(state.scoreText(group), score(1)); assert.equal(state.isEmpty(group, 1), true);
      state[properties[group]][1] = lesson.data[group][1].answer; state.check(group); state.check(group);
      assert.equal(state.scoreText(group), score(2));
    }
    state.resetGroup('inputs'); assert.equal(state.scoreText('inputs'), score(0));
    assert.equal(state.scoreText('selects'), score(2)); assert.equal(state.scoreText('choices'), score(2));
    state.resetGroup('choices'); assert.equal(state.scoreText('choices'), score(0)); assert.equal(state.scoreText('selects'), score(2));
    fixture.unchanged();
  });
}

after(() => {
  assert.equal(networkCalls, 0); assert.equal(coverage.manual_inputs, 6); assert.equal(coverage.token_inputs, 6);
  assert.equal(coverage.choices_feedback_after_check, 6); assert.equal(coverage.visible_options, 36);
  assert.equal(coverage.meaning_translations_deferred, 4); assert.equal(coverage.explicit_uk_question_criteria, 2);
  assert.equal(coverage.declared_aliases, 9, 'Six canonical inputs plus three declared contraction aliases');
  assert.deepEqual([...coverage.checked_task_ids].sort(), Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(file => [file, sha(file)])), protectedBefore);
  const { checked_task_ids, ...counts } = coverage;
  console.log('M52_STATE_COVERAGE ' + JSON.stringify({ ...counts, checked_task_ids: [...checked_task_ids], network_calls: networkCalls,
    source_mode: draftReview ? 'draft-only-not-frozen' : 'frozen-release', master_sha256: actualMasterSha,
    limitation: 'Offline unchanged shipped factory only; not browser visibility/interaction, widget grading, DB apply, no-JS or print.' }));
});
