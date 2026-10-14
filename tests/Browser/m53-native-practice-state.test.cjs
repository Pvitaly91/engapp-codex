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
const packagePath = 'app/Support/M53PassiveConstructionsPackage.php';
const draftReview = process.env.M53_AUTHOR_DRAFT === '1';
const packageSource = fs.existsSync(path.join(root, packagePath)) ? read(packagePath) : '';
if (!draftReview) assert.notEqual(packageSource, '', 'Frozen mode requires release package');
function constant(name) {
  const match = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';"));
  assert.ok(match, name); return match[1];
}
const masterPath = draftReview ? 'docs/content/m53-passive-constructions.DRAFT.json' : constant('MASTER');
const masterBytes = fs.readFileSync(path.join(root, masterPath));
const actualMasterSha = crypto.createHash('sha256').update(masterBytes).digest('hex');
const master = JSON.parse(masterBytes.toString('utf8'));
if (draftReview) {
  assert.match(master.status, /^draft(?:-|$)/, 'Explicit mutable draft source required');
  assert.match(master.version, /draft/, 'Draft source must not masquerade as frozen release');
  assert.match(masterPath, /\.DRAFT\.json$/);
} else {
  assert.match(constant('MASTER_SHA'), /^[a-f0-9]{64}$/, 'Draft sentinel cannot be accepted as frozen');
  assert.equal(actualMasterSha, constant('MASTER_SHA'), 'Independent reviewed frozen M53 author source');
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
  'public/data/english-contractions.json', 'tests/Browser/load-answer-variants.cjs'].filter(file => fs.existsSync(path.join(root, file)));
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
  'm53-causative-does-get': { en: ['Does Nina get her jacket cleaned here?'], uk: ['Ніна віддає куртку в чистку тут?'] },
  'm53-causative-participle': { en: ['Leo had the button replaced.'], uk: ['Леву замінили ґудзик.'] },
  'm53-causative-organiser': { en: ['Danylo had his bike brakes replaced.'], uk: ['Данилові замінили гальма велосипеда за його замовленням.'] },
  'm53-causative-past-perfect': { en: ['Oleh had repaired the chair himself before his sister arrived.'], uk: ['Олег сам полагодив стілець ще до приходу сестри.'] },
  'm53-causative-build-service': { en: ['Olena had her violin repaired yesterday.'], uk: ['Олені вчора полагодили скрипку за її замовленням.'] },
  'm53-causative-build-negative': { en: ["Taras didn't have his coat cleaned yesterday."], uk: ['Тарас учора не віддавав пальто в чистку.'] },
  'm53-get-did-base': { en: ['Did the parcel get damaged during delivery?'], uk: ['Посилку пошкодили під час доставки?'] },
  'm53-get-negative-base': { en: ["Mark didn't get paid yesterday."], uk: ['Марку вчора не заплатили.'] },
  'm53-get-positive': { en: ['Zoia got selected for the choir.'], uk: ['Зою відібрали до хору.'] },
  'm53-get-versus-causative': { en: ['The poster got damaged.', 'I got the poster reprinted.'], uk: ['Плакат пошкодили.', 'Мені передрукували плакат за моїм замовленням.'] },
  'm53-get-build-question': { en: ['Did Marta get promoted last week?'], uk: ['Марту підвищили минулого тижня?'] },
  'm53-get-build-negative': { en: ["We didn't get paid yesterday."], uk: ['Нам учора не заплатили.'] },
  'm53-impersonal-earlier': { en: ['Oksana is reported to have finished the course last month.'], uk: ['Повідомляють, що Оксана завершила курс минулого місяця.'] },
  'm53-impersonal-simultaneous': { en: ['At noon, the studio was believed to be empty.'], uk: ['Опівдні вважали, що студія в цей час порожня.'] },
  'm53-impersonal-expect-future': { en: ['At six, the courier was expected to arrive at eight.'], uk: ['О шостій очікували, що кур’єр прибуде о восьмій.'] },
  'm53-impersonal-not-known': { en: ['The door is not known to have been opened.'], uk: ['Немає підтверджених відомостей, що двері відчиняли.'] },
  'm53-impersonal-build-it': { en: ['It is believed that the library is quiet.'], uk: ['Вважають, що в бібліотеці тихо.'] },
  'm53-impersonal-build-earlier': { en: ['The guide is reported to have left yesterday.'], uk: ['Повідомляють, що гід пішов учора.'] },
};
const expectedInputAliases = {
  'm53-causative-build-service': ['Olena had her violin repaired yesterday.'],
  'm53-causative-build-negative': ["Taras didn't have his coat cleaned yesterday.", 'Taras did not have his coat cleaned yesterday.'],
  'm53-get-build-question': ['Did Marta get promoted last week?'],
  'm53-get-build-negative': ["We didn't get paid yesterday.", 'We did not get paid yesterday.'],
  'm53-impersonal-build-it': ['It is believed that the library is quiet.'],
  'm53-impersonal-build-earlier': ['The guide is reported to have left yesterday.'],
};
const wrongProbes = {
  'm53-causative-build-service': 'Olena had repaired her violin yesterday.',
  'm53-causative-build-negative': "Taras didn't had his coat cleaned yesterday.",
  'm53-get-build-question': 'Was Marta promoted last week?',
  'm53-get-build-negative': "We didn't got paid yesterday.",
  'm53-impersonal-build-it': 'It is known that the library is quiet.',
  'm53-impersonal-build-earlier': 'The guide was reported to have left yesterday.',
};
// Six meaning tasks display a full English stimulus; precise UK translations belong to Check feedback.
const deferredMeaningTranslationIds = new Set([
  'm53-causative-organiser', 'm53-causative-past-perfect',
  'm53-get-positive', 'm53-get-versus-causative',
  'm53-impersonal-expect-future', 'm53-impersonal-not-known',
]);
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
  assert.equal(master.version, draftReview ? '1.0.0-draft' : '1.0.0');
  assert.equal(master.base_sha, 'ba489be880d9a7c2b3dbc1698e887a05eb166fbe');
  assert.deepEqual(lessons.map(lesson => lesson.slug), ['theory-passive-voice-causative', 'theory-passive-voice-get-passive', 'theory-passive-voice-impersonal-passive']);
  const technical = ['B1', 'B1', 'B2'], display = ['B1–B2', 'B1', 'B2–C1'];
  const continuing = [10, 9, 10];
  const banks = [
    ['PolyglotCausativeHaveGetLessonSeeder'],
    ['PolyglotPassiveVoiceGetPassiveAllLevelsLessonSeeder', 'M53GetPassiveB1SupplementSeeder'],
    ['PolyglotPassiveVoiceImpersonalPassiveAllLevelsLessonSeeder'],
  ];
  const ids = [];
  for (const [number, lesson] of lessons.entries()) {
    assert.equal(lesson.locale, 'uk'); assert.equal(lesson.technical_block_level, technical[number]);
    assert.equal(lesson.display_level, display[number]);
    assert.deepEqual(lesson.blocks.slice(0, continuing[number]).map(block => block.existing_source_index), Array.from({ length: continuing[number] }, (_, i) => i));
    assert.equal(lesson.blocks.at(-1).type, 'practice-set');
    assert.equal(lesson.blocks.at(-1).existing_source_index, null);
    assert.equal(lesson.blocks.at(-1).column, 'left');
    assert.deepEqual(lesson.data.linked_practice.question_types, ['4']);
    assert.deepEqual(lesson.data.linked_practice.seeder_classes, banks[number].map(bank => 'Database\\Seeders\\V3\\Polyglot\\' + bank));
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
      } else { assert.fail('Every M53 meaning task has an independently declared deferred translation ID'); }
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
        const withoutTerminal = alias.replace(/[.!?]$/, '');
        state.inputAnswers[index] = withoutTerminal; state.syncInputTokenBank(index); state.check('inputs');
        assert.equal(state.isCorrect('inputs', index), true, withoutTerminal);
        assertFeedback(item, state.feedbackText('inputs', index));
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
  assert.equal(coverage.meaning_translations_deferred, 6); assert.equal(coverage.explicit_uk_question_criteria, 0);
  assert.equal(coverage.declared_aliases, 8, 'Six canonical inputs plus two declared full-negative aliases');
  assert.deepEqual([...coverage.checked_task_ids].sort(), Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(file => [file, sha(file)])), protectedBefore);
  const { checked_task_ids, ...counts } = coverage;
  console.log('M53_STATE_COVERAGE ' + JSON.stringify({ ...counts, checked_task_ids: [...checked_task_ids], network_calls: networkCalls,
    source_mode: draftReview ? 'draft-only-not-frozen' : 'frozen-release', master_sha256: actualMasterSha,
    limitation: 'Offline unchanged shipped factory only; not browser visibility/interaction, widget grading, DB apply, no-JS or print.' }));
});
