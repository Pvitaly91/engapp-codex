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
const packagePath = 'app/Support/M54PassivePatternsPackage.php';
const draftReview = process.env.M54_AUTHOR_DRAFT === '1';
const packageSource = fs.existsSync(path.join(root, packagePath)) ? read(packagePath) : '';
if (!draftReview) assert.notEqual(packageSource, '', 'Frozen mode requires release package');
function constant(name) {
  const match = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';"));
  assert.ok(match, name); return match[1];
}
const masterPath = draftReview ? 'storage/app/seo-m54-local/drafts/master.draft.json' : constant('MASTER');
const masterBytes = fs.readFileSync(path.join(root, masterPath));
const actualMasterSha = crypto.createHash('sha256').update(masterBytes).digest('hex');
const master = JSON.parse(masterBytes.toString('utf8'));
if (draftReview) {
  assert.match(master.status, /^draft(?:-|$)/, 'Explicit mutable draft source required');
  assert.equal(masterPath, 'storage/app/seo-m54-local/drafts/master.draft.json');
} else {
  assert.match(constant('MASTER_SHA'), /^[a-f0-9]{64}$/, 'Draft sentinel cannot be accepted as frozen');
  assert.equal(actualMasterSha, constant('MASTER_SHA'), 'Independent reviewed frozen M54 author source');
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
  "m54-phrasal-cancellation": {
    "en": [
      "The tour was called off yesterday."
    ],
    "uk": [
      "Екскурсію скасували вчора."
    ]
  },
  "m54-phrasal-continuous": {
    "en": [
      "The complaints are being looked into now."
    ],
    "uk": [
      "Скарги зараз розглядають."
    ]
  },
  "m54-phrasal-postponement": {
    "en": [
      "The workshop has been put off until Friday."
    ],
    "uk": [
      "Майстер-клас перенесли на п’ятницю."
    ]
  },
  "m54-phrasal-separability": {
    "en": [
      "The committee turned the applications down.",
      "The applications were turned down by the committee."
    ],
    "uk": [
      "Комітет відхилив заявки.",
      "Заявки відхилив комітет."
    ]
  },
  "m54-phrasal-build-perfect-question": {
    "en": [
      "Has the school trip been called off?"
    ],
    "uk": [
      "Шкільну поїздку скасували?"
    ]
  },
  "m54-phrasal-build-negative-process": {
    "en": [
      "The issue is not being looked into."
    ],
    "uk": [
      "Це питання зараз не розслідують."
    ]
  },
  "m54-restrictions-happen": {
    "en": [
      "The failure happened last night."
    ],
    "uk": [
      "Збій стався минулої ночі."
    ]
  },
  "m54-restrictions-weigh-action": {
    "en": [
      "The suitcases were weighed by the worker."
    ],
    "uk": [
      "Валізи зважив працівник."
    ]
  },
  "m54-restrictions-ownership": {
    "en": [
      "The workshop is owned by the town."
    ],
    "uk": [
      "Майстерня належить місту."
    ]
  },
  "m54-restrictions-open-event": {
    "en": [
      "The gate opened slowly."
    ],
    "uk": [
      "Ворота повільно відчинилися."
    ]
  },
  "m54-restrictions-build-arrival": {
    "en": [
      "The train arrived at noon."
    ],
    "uk": [
      "Потяг прибув опівдні."
    ]
  },
  "m54-restrictions-build-ownership": {
    "en": [
      "The shop is owned by my aunt."
    ],
    "uk": [
      "Магазин належить моїй тітці."
    ]
  },
  "m54-two-recipient-agreement": {
    "en": [
      "The guests were offered a room."
    ],
    "uk": [
      "Гостям запропонували кімнату."
    ]
  },
  "m54-two-beneficiary-for": {
    "en": [
      "Lunch was cooked for Nina."
    ],
    "uk": [
      "Обід приготували для Ніни."
    ]
  },
  "m54-two-focus-message": {
    "en": [
      "The letter was sent to Iryna."
    ],
    "uk": [
      "Лист надіслали Ірині."
    ]
  },
  "m54-two-agent-beneficiary": {
    "en": [
      "A meal was cooked for us by Olena."
    ],
    "uk": [
      "Для нас приготувала їжу Олена."
    ]
  },
  "m54-two-build-recipient": {
    "en": [
      "They were offered a room by the manager."
    ],
    "uk": [
      "Їм запропонував кімнату менеджер."
    ]
  },
  "m54-two-build-theme": {
    "en": [
      "The tickets were sent to them by my sister."
    ],
    "uk": [
      "Квитки надіслала їм моя сестра."
    ]
  }
};
const expectedInputAliases = {
  "m54-phrasal-build-perfect-question": [
    "Has the school trip been called off?"
  ],
  "m54-phrasal-build-negative-process": [
    "The issue is not being looked into.",
    "The issue isn't being looked into."
  ],
  "m54-restrictions-build-arrival": [
    "The train arrived at noon."
  ],
  "m54-restrictions-build-ownership": [
    "The shop is owned by my aunt."
  ],
  "m54-two-build-recipient": [
    "They were offered a room by the manager."
  ],
  "m54-two-build-theme": [
    "The tickets were sent to them by my sister."
  ]
};
const wrongProbes = {
  "m54-phrasal-build-perfect-question": "Has the school trip called off?",
  "m54-phrasal-build-negative-process": "The issue is not been looked into.",
  "m54-restrictions-build-arrival": "The train was arrived at noon.",
  "m54-restrictions-build-ownership": "My aunt owns the shop.",
  "m54-two-build-recipient": "A room was offered to them by the manager.",
  "m54-two-build-theme": "They were sent the tickets by my sister."
};
// Feedback may explain a grammatical alternative that does not meet this exercise's focus.
const feedbackOverrides = {
  "m54-two-focus-message": {
    "iryna was sent the letter.": {
      "en": [
        "Iryna was sent the letter."
      ],
      "uk": [
        "Ірині надіслали листа."
      ]
    },
    "iryna sent the letter.": {
      "en": [
        "Iryna sent the letter."
      ],
      "uk": [
        "Ірина надіслала листа."
      ]
    }
  },
  "m54-two-build-recipient": {
    "a room was offered to them by the manager.": {
      "en": [
        "A room was offered to them by the manager."
      ],
      "uk": [
        "Кімнату запропонував їм менеджер."
      ]
    }
  },
  "m54-two-build-theme": {
    "they were sent the tickets by my sister.": {
      "en": [
        "They were sent the tickets by my sister."
      ],
      "uk": [
        "Їм надіслала квитки моя сестра."
      ]
    }
  }
};
// Five meaning tasks display a full English stimulus; the focus task has full English candidates. Precise UK translations belong to Check feedback.
const deferredMeaningTranslationIds = new Set([
  'm54-phrasal-postponement', 'm54-phrasal-separability',
  'm54-restrictions-ownership', 'm54-restrictions-open-event', 'm54-two-agent-beneficiary',
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
function assertFeedback(item, actual, answer = item.answer) {
  assert.equal(typeof actual, 'string');
  const detail = feedbackOverrides[item.id]?.[feedbackKey(answer)] || expected[item.id];
  for (const ukrainian of detail.uk) assert.ok(actual.includes(ukrainian), item.id + ': full precise UK translation');
  const aliases = expectedInputAliases[item.id];
  if (aliases && !feedbackOverrides[item.id]?.[feedbackKey(answer)]) {
    assert.ok(aliases.some(sentence => actual.includes(sentence)), item.id + ': full canonical/accepted English sentence');
  } else for (const english of detail.en) assert.ok(actual.includes(english), item.id + ': full English sentence, not only a gap answer');
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

test((draftReview ? 'explicit draft-only author contract' : 'frozen author contract') + ': exact three UK owners, exact B1 technical levels and distinct display ranges, 2/2/2 and independent themes', () => {
  assert.equal(master.version, '1.0.0');
  assert.equal(master.base_sha, 'f4e1c0d94a590d630582e2bb613cbdc93654fc4a');
  assert.deepEqual(lessons.map(lesson => lesson.identity), [
    'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoicePhrasalVerbsTheorySeeder',
    'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceRestrictionsTheorySeeder',
    'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceTwoObjectVerbsTheorySeeder',
  ]);
  assert.deepEqual(lessons.map(lesson => lesson.slug), ['theory-passive-voice-phrasal-verbs', 'theory-passive-voice-restrictions', 'theory-passive-voice-two-object-verbs']);
  const technical = ['B1', 'B1', 'B1'], display = ['B1–B2', 'B1–B2', 'B1'];
  const continuing = [9, 10, 9];
  const banks = [
    ['PolyglotPassiveVoicePhrasalVerbsAllLevelsLessonSeeder'],
    ['PolyglotPassiveVoiceRestrictionsAllLevelsLessonSeeder'],
    ['PolyglotPassiveVoiceTwoObjectVerbsAllLevelsLessonSeeder'],
  ];
  const ids = [];
  for (const [number, lesson] of lessons.entries()) {
    assert.equal(lesson.locale, 'uk'); assert.equal(lesson.technical_block_level, technical[number]);
    assert.equal(lesson.display_level, display[number]);
    assert.deepEqual(lesson.blocks.slice(0, continuing[number]).map(block => block.existing_source_index), Array.from({ length: continuing[number] }, (_, i) => i));
    assert.equal(lesson.blocks.at(-1).type, 'practice-set');
    assert.equal(lesson.blocks.at(-1).existing_source_index, null);
    assert.equal(lesson.blocks.at(-1).column, 'left');
    assert.equal(lesson.blocks.at(-1).level, 'B1');
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
          assertFeedback(item, state.feedbackText(group, index), option); coverage.visible_options++;
        }
        state.resetGroup(group);
        for (let repeat = 0; repeat < 2; repeat++) {
          state[properties[group]][index] = wrong[0]; state.check(group);
          assert.equal(state.isCorrect(group, index), false); assert.equal(state.scoreText(group), score(0));
          assert.match(state.fieldClass(group, index), /rose/); assertFeedback(item, state.feedbackText(group, index), wrong[0]);
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
        assert.equal(item.id, 'm54-two-focus-message');
        assert.equal(item.prompt, '');
        assert.ok(item.options.every(option => /[A-Za-z]/.test(option)), 'English candidates retain full visible focus distinction');
        assert.ok(!String(item.label || '').includes(ukrainian), 'Exact candidate translation stays in Check feedback');
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
        assertFeedback(item, state.feedbackText('inputs', index), wrong);
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
  assert.equal(coverage.meaning_translations_deferred, 5); assert.equal(coverage.explicit_uk_question_criteria, 1);
  assert.equal(coverage.declared_aliases, 7, 'Six canonical inputs plus one declared contracted negative alias');
  assert.deepEqual([...coverage.checked_task_ids].sort(), Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(file => [file, sha(file)])), protectedBefore);
  const { checked_task_ids, ...counts } = coverage;
  console.log('M54_STATE_COVERAGE ' + JSON.stringify({ ...counts, checked_task_ids: [...checked_task_ids], network_calls: networkCalls,
    source_mode: draftReview ? 'draft-only-not-frozen' : 'frozen-release', master_sha256: actualMasterSha,
    limitation: 'Offline unchanged shipped factory only; not browser visibility/interaction, widget grading, DB apply, no-JS or print.' }));
});
