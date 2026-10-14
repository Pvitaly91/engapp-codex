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
const packagePath = 'app/Support/M51ModalMeaningsPackage.php';
const packageSource = read(packagePath);
function constant(name) {
  const match = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';"));
  assert.ok(match, name); return match[1];
}
const masterPath = constant('MASTER');
const draftReview = process.env.M51_DRAFT_REVIEW === '1';
const masterBytes = fs.readFileSync(path.join(root, masterPath));
const actualMasterSha = crypto.createHash('sha256').update(masterBytes).digest('hex');
const master = JSON.parse(masterBytes.toString('utf8'));
if (draftReview) {
  assert.equal(constant('MASTER_SHA'), 'UNREVIEWED', 'Draft review cannot masquerade as a frozen release run');
  assert.equal(actualMasterSha, '5a20361ade3a8399ee449f243f0fab5896c5bd65056ab743ebe19a95c9492e06', 'Exact main-reviewed pre-freeze draft');
  assert.equal(master.status, 'draft-unfrozen', 'Draft-only source status required');
} else {
  assert.match(constant('MASTER_SHA'), /^[a-f0-9]{64}$/, 'Draft SHA cannot be accepted as a frozen test source');
  assert.equal(actualMasterSha, constant('MASTER_SHA'), 'Independent reviewed frozen M51 author source');
}
const factoryPath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const factorySource = read(factoryPath);
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
  'm51-deduction-must-base': ['Nora must know the route.', 'Нора, напевно, знає маршрут.'],
  'm51-deduction-might-process': ['They might be working upstairs now.', 'Можливо, вони зараз працюють нагорі.'],
  'm51-deduction-may-not-meaning': ['The guide may not be available today.', 'Можливо, гід сьогодні недоступний.'],
  'm51-deduction-cant-meaning': ["Leo is speaking to us live. He can't be asleep.", 'Лео розмовляє з нами наживо. Не може бути, щоб він спав.'],
  'm51-deduction-build-process': ['They might be waiting outside.', 'Можливо, вони чекають надворі.'],
  'm51-deduction-build-negative': ["This code can't have four digits.", 'Не може бути, щоб цей код мав чотири цифри.'],
  'm51-past-must-have': ['The owner must have opened the shop yesterday.', 'Власник, напевно, відчинив магазин учора.'],
  'm51-past-shouldnt-v3': ["I shouldn't have deleted the file.", 'Мені не слід було видаляти файл.'],
  'm51-past-unused-meaning': ['Oleh could have taken the ferry, but he chose the train.', 'Олег міг скористатися поромом, але обрав потяг.'],
  'm51-past-expectation-meaning': ["The download should have finished by now. I haven't checked the laptop.", 'Завантаження вже мало б завершитися. Я ще не перевірив ноутбук.'],
  'm51-past-build-impossible': ["Alex can't have read the message yesterday.", 'Не може бути, щоб Алекс прочитав повідомлення вчора.'],
  'm51-past-build-criticism': ['Iryna should not have told the secret.', 'Ірині не слід було розповідати таємницю.'],
  'm51-requests-could-base': ['Could you help me with this box?', 'Не міг би ти допомогти мені з цією коробкою?'],
  'm51-requests-like-to': ['Would you like to come with me?', 'Хочеш піти зі мною?'],
  'm51-requests-me-to-actor': ['Would you like me to print the tickets?', 'Хочеш, щоб я надрукувала квитки?'],
  'm51-requests-shared-plan': ["Why don't we meet at six?", 'Чому б нам не зустрітися о шостій?'],
  'm51-requests-build-request': ['Would you close the gate, please?', 'Не зачиниш хвіртку, будь ласка?'],
  'm51-requests-build-negative-plan': ["Let's not leave the bags here.", 'Давай не залишатимемо сумки тут.'],
};
const expectedInputAliases = {
  'm51-deduction-build-process': ['They might be waiting outside.'],
  'm51-deduction-build-negative': ["This code can't have four digits.", 'This code cannot have four digits.'],
  'm51-past-build-impossible': ["Alex can't have read the message yesterday.", 'Alex cannot have read the message yesterday.'],
  'm51-past-build-criticism': ['Iryna should not have told the secret.', "Iryna shouldn't have told the secret."],
  'm51-requests-build-request': ['Would you close the gate, please?', 'Would you close the gate please?', 'Would you please close the gate?'],
  'm51-requests-build-negative-plan': ["Let's not leave the bags here.", 'Let us not leave the bags here.'],
};
const wrongProbes = {
  'm51-deduction-build-process': 'They might waiting outside.',
  'm51-deduction-build-negative': "This code can't has four digits.",
  'm51-past-build-impossible': "Alex can't had read the message yesterday.",
  'm51-past-build-criticism': 'Iryna should not have tell the secret.',
  'm51-requests-build-request': 'Would you to close the gate, please?',
  'm51-requests-build-negative-plan': "Let's don't leave the bags here.",
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
  const [english, ukrainian] = expected[item.id];
  assert.ok(actual.includes(ukrainian), item.id + ': full precise UK translation');
  const fullSentences = expectedInputAliases[item.id] || [english];
  assert.ok(fullSentences.some(sentence => actual.includes(sentence)), item.id + ': full English sentence, not only gap answer');
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
  choices_translation_after_check: 0, checked_task_ids: new Set() };

test((draftReview ? 'exact pinned draft-only author contract' : 'frozen author contract') + ': exact three UK owners, mixed technical levels, 2/2/2 and independent themes', () => {
  assert.equal(master.version, '1.0.0');
  assert.equal(master.base_sha, '7b0ca49b3cbfd7135b80f6931cdd42834eb24c53');
  assert.deepEqual(lessons.map(lesson => lesson.slug), ['modals-of-deduction', 'past-modals', 'requests-offers-suggestions']);
  const technical = ['B1', 'B1', 'A2'], display = ['B1-B2', 'B1-B2', 'A2-B1'];
  const banks = ['PolyglotModalsOfDeductionThematicLessonSeeder', 'PolyglotPastModalsThematicLessonSeeder', 'PolyglotRequestsOffersSuggestionsThematicLessonSeeder'];
  const ids = [];
  for (const [number, lesson] of lessons.entries()) {
    assert.equal(lesson.locale, 'uk'); assert.equal(lesson.technical_block_level, technical[number]);
    assert.equal(lesson.display_level, display[number]);
    assert.deepEqual(lesson.data.linked_practice.question_types, ['4']);
    assert.deepEqual(lesson.data.linked_practice.seeder_classes, ['Database\\Seeders\\V3\\Polyglot\\' + banks[number]]);
    assert.equal(lesson.data.linked_practice.source, 'theory_links');
    assert.deepEqual(groups.flatMap(group => lesson.data[group].map(item => item.source_index)), [1, 2, 3, 4, 5, 6]);
    for (const group of groups) {
      assert.equal(lesson.data[group].length, 2);
      for (const item of lesson.data[group]) {
        assert.ok(expected[item.id]); ids.push(item.id);
        assert.ok(item.accepted.includes(item.answer));
        assert.equal(new Set(item.accepted).size, item.accepted.length);
        if (group === 'inputs') assert.deepEqual(item.accepted, expectedInputAliases[item.id]);
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

  test(lesson.slug + ': all meaning translations stay in guarded feedback until filled choice is checked', () => {
    const fixture = stateFor(lesson), state = fixture.state;
    // The actual shipped template owns visibility; serialized answers are not a no-JS key.
    assert.ok(factorySource.includes('x-show="isChecked(\'choices\') && hasAnswer(\'choices\', {{ $index }})"'));
    for (const [index, item] of lesson.data.choices.entries()) {
      const ukrainian = expected[item.id][1];
      assert.ok(!String(item.label || '').includes(ukrainian));
      assert.ok(!String(item.prompt || '').includes(ukrainian));
      state.resetGroup('choices'); state.choiceAnswers[index] = item.answer;
      assert.equal(state.isChecked('choices'), false, 'Feedback visibility guard stays closed before Check');
      state.check('choices'); assert.equal(state.isChecked('choices'), true);
      assertFeedback(item, state.feedbackText('choices', index));
      state.resetGroup('choices'); assert.equal(state.isChecked('choices'), false);
      coverage.choices_translation_after_check++;
    }
    fixture.unchanged();
  });

  for (const [index, item] of lesson.data.inputs.entries()) {
    test(item.id + ': manual empty/partial/wrong/repeated wrong/correct, every declared alias and full translated feedback', () => {
      const fixture = stateFor(lesson), state = fixture.state;
      assert.match(item.after, /[А-Яа-яІіЇїЄєҐґ]/u); assert.ok(item.before.includes('/'));
      assert.ok(!item.before.includes(item.answer)); assert.ok(!item.after.includes(item.answer));
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
  assert.equal(coverage.choices_translation_after_check, 6);
  assert.deepEqual([...coverage.checked_task_ids].sort(), Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(file => [file, sha(file)])), protectedBefore);
  const { checked_task_ids, ...counts } = coverage;
  console.log('M51_STATE_COVERAGE ' + JSON.stringify({ ...counts, checked_task_ids: [...checked_task_ids], network_calls: networkCalls,
    source_mode: draftReview ? 'draft-only-not-frozen' : 'frozen-release', master_sha256: actualMasterSha,
    limitation: 'Offline unchanged shipped factory only; not browser visibility/interaction, widget grading, DB apply, no-JS or print.' }));
});
