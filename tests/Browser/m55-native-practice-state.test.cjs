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
const packagePath = 'app/Support/M55PassiveReviewInfinitivePackage.php';
const draftReview = process.env.M55_AUTHOR_DRAFT === '1';
const packageSource = fs.existsSync(path.join(root, packagePath)) ? read(packagePath) : '';
if (!draftReview) assert.notEqual(packageSource, '', 'Frozen mode requires release package');
function constant(name) {
  const match = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';"));
  assert.ok(match, name); return match[1];
}
const masterPath = draftReview ? 'storage/app/seo-m55-local/draft/m55-author-master.draft.json' : constant('MASTER');
const masterBytes = fs.readFileSync(path.join(root, masterPath));
const actualMasterSha = crypto.createHash('sha256').update(masterBytes).digest('hex');
const master = JSON.parse(masterBytes.toString('utf8'));
if (draftReview) {
  assert.match(master.status, /^draft(?:-|$)/, 'Explicit mutable draft source required');
  assert.equal(masterPath, 'storage/app/seo-m55-local/draft/m55-author-master.draft.json');
} else {
  assert.match(constant('MASTER_SHA'), /^[a-f0-9]{64}$/, 'Draft sentinel cannot be accepted as frozen');
  assert.equal(actualMasterSha, constant('MASTER_SHA'), 'Independent reviewed frozen M55 author source');
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
  "m55-mistakes-form-plural": {
    "en": [
      "The doors were painted yesterday."
    ],
    "uk": [
      "Двері пофарбували вчора."
    ]
  },
  "m55-mistakes-form-v3": {
    "en": [
      "The key was taken yesterday."
    ],
    "uk": [
      "Ключ узяли вчора."
    ]
  },
  "m55-mistakes-meaning-agent": {
    "en": [
      "The bike was repaired by Olia."
    ],
    "uk": [
      "Велосипед полагодила Оля."
    ]
  },
  "m55-mistakes-meaning-time": {
    "en": [
      "The box wasn't opened yesterday.",
      "The box isn't opened every day."
    ],
    "uk": [
      "Скриньку вчора не відкривали.",
      "Скриньку відкривають не щодня."
    ]
  },
  "m55-mistakes-build-question": {
    "en": [
      "Were the windows cleaned yesterday?"
    ],
    "uk": [
      "Вікна помили вчора?"
    ]
  },
  "m55-mistakes-build-negative": {
    "en": [
      "The shop is not opened on Sundays."
    ],
    "uk": [
      "Магазин не відчиняють по неділях."
    ]
  },
  "m55-style-form-perfect": {
    "en": [
      "The layout has been checked by our team."
    ],
    "uk": [
      "Макет перевірила наша команда."
    ]
  },
  "m55-style-form-process-question": {
    "en": [
      "Were the posters being printed at ten?"
    ],
    "uk": [
      "О десятій плакати саме друкували?"
    ]
  },
  "m55-style-meaning-responsibility": {
    "en": [
      "We entered the wrong date.",
      "The wrong date was entered."
    ],
    "uk": [
      "Ми внесли неправильну дату.",
      "Було внесено неправильну дату."
    ]
  },
  "m55-style-meaning-topic": {
    "en": [
      "The bench was repaired by volunteers on Saturday."
    ],
    "uk": [
      "Лавку відремонтували волонтери в суботу."
    ]
  },
  "m55-style-build-topic": {
    "en": [
      "The draft was revised by our team."
    ],
    "uk": [
      "Чернетку відредагувала наша команда."
    ]
  },
  "m55-style-build-responsibility": {
    "en": [
      "We checked the labels before lunch."
    ],
    "uk": [
      "Ми перевірили етикетки до обіду."
    ]
  },
  "m55-infinitive-form-simple": {
    "en": [
      "The badges need to be printed by Monday."
    ],
    "uk": [
      "Бейджі потрібно надрукувати до понеділка."
    ]
  },
  "m55-infinitive-form-perfect": {
    "en": [
      "The tickets seem to have been sent yesterday."
    ],
    "uk": [
      "Здається, квитки надіслали вчора."
    ]
  },
  "m55-infinitive-meaning-receiver": {
    "en": [
      "I want Nina to be invited."
    ],
    "uk": [
      "Я хочу, щоб Ніну запросили."
    ]
  },
  "m55-infinitive-meaning-expectation": {
    "en": [
      "We hope to be informed on Friday."
    ],
    "uk": [
      "Ми сподіваємося, що нас поінформують у п’ятницю."
    ]
  },
  "m55-infinitive-build-need": {
    "en": [
      "The printer needs to be repaired by Friday."
    ],
    "uk": [
      "Принтер потрібно відремонтувати до п’ятниці."
    ]
  },
  "m55-infinitive-build-object": {
    "en": [
      "I want the guests to be invited today."
    ],
    "uk": [
      "Я хочу, щоб гостей запросили сьогодні."
    ]
  }
};
// Correct controls are reviewed independently of the loaded author data and factory.
const expectedControlAnswers = {
  'm55-mistakes-form-plural': 'were',
  'm55-mistakes-form-v3': 'taken',
  'm55-mistakes-meaning-agent': 'Велосипед полагодила Оля.',
  'm55-mistakes-meaning-time': 'A',
  'm55-mistakes-build-question': 'Were the windows cleaned yesterday?',
  'm55-mistakes-build-negative': 'The shop is not opened on Sundays.',
  'm55-style-form-perfect': 'has been',
  'm55-style-form-process-question': 'being printed',
  'm55-style-meaning-responsibility': 'A',
  'm55-style-meaning-topic': 'B',
  'm55-style-build-topic': 'The draft was revised by our team.',
  'm55-style-build-responsibility': 'We checked the labels before lunch.',
  'm55-infinitive-form-simple': 'to be printed',
  'm55-infinitive-form-perfect': 'to have been sent',
  'm55-infinitive-meaning-receiver': 'Запросити мають Ніну; хто саме запросить, не вказано.',
  'm55-infinitive-meaning-expectation': 'Є надія отримати інформацію в п’ятницю; здійснення ще не підтверджене.',
  'm55-infinitive-build-need': 'The printer needs to be repaired by Friday.',
  'm55-infinitive-build-object': 'I want the guests to be invited today.',
};
const expectedInputAliases = {
  "m55-mistakes-build-question": [
    "Were the windows cleaned yesterday?"
  ],
  "m55-mistakes-build-negative": [
    "The shop is not opened on Sundays.",
    "The shop isn't opened on Sundays."
  ],
  "m55-style-build-topic": [
    "The draft was revised by our team."
  ],
  "m55-style-build-responsibility": [
    "We checked the labels before lunch."
  ],
  "m55-infinitive-build-need": [
    "The printer needs to be repaired by Friday."
  ],
  "m55-infinitive-build-object": [
    "I want the guests to be invited today."
  ]
};
const wrongProbes = {
  "m55-mistakes-build-question": "Was the windows cleaned yesterday?",
  "m55-mistakes-build-negative": "The shop is not open on Sundays.",
  "m55-style-build-topic": "Our team revised the draft.",
  "m55-style-build-responsibility": "The labels were checked before lunch.",
  "m55-infinitive-build-need": "The printer needs repairing by Friday.",
  "m55-infinitive-build-object": "I want to invite the guests today."
};
// Grammatical alternatives can miss a context-bound focus, not become grammar errors.
const feedbackOverrides = {
  "m55-style-meaning-topic": {
    "a": {
      "en": [
        "The bench was repaired on Saturday."
      ],
      "uk": [
        "Лавку відремонтували в суботу."
      ]
    },
    "c": {
      "en": [
        "Volunteers repaired the bench on Saturday."
      ],
      "uk": [
        "Волонтери відремонтували лавку в суботу."
      ]
    }
  },
  "m55-style-build-topic": {
    "our team revised the draft.": {
      "en": [
        "Our team revised the draft."
      ],
      "uk": [
        "Наша команда відредагувала чернетку."
      ]
    }
  },
  "m55-style-build-responsibility": {
    "the labels were checked before lunch.": {
      "en": [
        "The labels were checked before lunch."
      ],
      "uk": [
        "Етикетки перевірили до обіду."
      ]
    }
  },
  "m55-infinitive-build-need": {
    "the printer needs repairing by friday.": {
      "en": [
        "The printer needs repairing by Friday."
      ],
      "uk": [
        "Принтер потрібно відремонтувати до п’ятниці."
      ]
    }
  },
  "m55-infinitive-build-object": {
    "i want to invite the guests today.": {
      "en": [
        "I want to invite the guests today."
      ],
      "uk": [
        "Я хочу запросити гостей сьогодні."
      ]
    }
  }
};
const deferredMeaningTranslationIds = new Set([
  "m55-mistakes-meaning-agent", "m55-mistakes-meaning-time",
  "m55-style-meaning-responsibility", "m55-style-meaning-topic",
  "m55-infinitive-meaning-receiver", "m55-infinitive-meaning-expectation",
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

test((draftReview ? 'explicit draft-only author contract' : 'frozen author contract') + ': exact three UK owners, A2/B2/B2 technical levels, nested ancestry, 2/2/2 and independent themes', () => {
  assert.equal(master.version, '1.0.0');
  assert.equal(master.base_sha, '0cf633036f4c91f2376976651ec0519d80ec6191');
  assert.deepEqual(lessons.map(lesson => lesson.identity), [
    'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceCommonMistakesTheorySeeder',
    'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormalityStyleTheorySeeder',
    'Database\\Seeders\\Page_V3\\PassiveVoice\\InfinitivesGerund\\PassiveVoicePassiveInfinitiveTheorySeeder',
  ]);
  assert.deepEqual(lessons.map(lesson => lesson.slug), ['theory-passive-voice-common-mistakes', 'theory-passive-voice-formality-style', 'theory-passive-voice-passive-infinitive']);
  const technical = ['A2', 'B2', 'B2'], display = ['A2–B2', 'B2–C1', 'B2–C1'];
  const oldSlots = [11, 10, 9];
  const levels = [
    ['A2', 'A2', 'A2', 'A2', 'A2', 'B1', 'B1', 'A2', 'A2', 'A2', null],
    ['B2', 'B2', 'B2', 'B2', 'B2', 'C1', 'C1', 'B2', 'B2', null],
    ['B2', 'B2', 'B2', 'C1', 'B2', 'B2', 'B2', 'B2', null],
  ];
  const banks = [
    ['PolyglotPassiveVoiceCommonMistakesAllLevelsLessonSeeder'],
    ['PolyglotPassiveVoiceFormalityStyleAllLevelsLessonSeeder'],
    ['PolyglotPassiveVoicePassiveInfinitiveAllLevelsLessonSeeder'],
  ];
  const ids = [];
  for (const [number, lesson] of lessons.entries()) {
    assert.equal(lesson.locale, 'uk'); assert.equal(lesson.technical_block_level, technical[number]);
    assert.equal(lesson.display_level, display[number]);
    assert.deepEqual(lesson.category_ancestry, number === 2 ? ['passive-voice', 'passive-voice-infinitives-gerund'] : ['passive-voice']);
    assert.deepEqual(lesson.blocks.slice(0, oldSlots[number]).map(block => block.existing_source_index), Array.from({ length: oldSlots[number] }, (_, i) => i));
    assert.deepEqual(lesson.blocks.slice(0, oldSlots[number]).map(block => block.level), levels[number]);
    assert.equal(lesson.blocks.at(-1).type, 'practice-set');
    assert.equal(lesson.blocks.at(-1).existing_source_index, null);
    assert.equal(lesson.blocks.at(-1).column, 'left');
    assert.equal(lesson.blocks.at(-1).level, technical[number]);
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
        assert.deepEqual(Object.keys(item).sort(), supportedKeys.sort(), item.id + ': only supported control and answer fields');
        assert.equal(item.answer, expectedControlAnswers[item.id], item.id + ': independently reviewed canonical control');
        assert.ok(item.accepted.includes(item.answer));
        assert.equal(new Set(item.accepted).size, item.accepted.length);
        if (group === 'inputs') assert.deepEqual(item.accepted, expectedInputAliases[item.id]);
        else { assert.ok(item.options.length >= 2 && item.options.length <= 3); assert.equal(new Set(item.options).size, item.options.length); }
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
        assert.fail('Every M55 meaning task must have its reviewed after-Check translation contract: ' + item.id);
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
      assert.deepEqual([...declaredTokens].sort(), expectedInputAliases[item.id][0].split(/\s+/).sort(),
        'Visible token spelling, case, punctuation and instance multiplicity match the independent canonical sentence strictly');
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
  assert.equal(coverage.choices_feedback_after_check, 6); assert.equal(coverage.visible_options, 34);
  assert.equal(coverage.meaning_translations_deferred, 6); assert.equal(coverage.explicit_uk_question_criteria, 0);
  assert.equal(coverage.declared_aliases, 7, 'Six canonical inputs plus one declared contracted negative alias');
  assert.deepEqual([...coverage.checked_task_ids].sort(), Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(file => [file, sha(file)])), protectedBefore);
  const { checked_task_ids, ...counts } = coverage;
  console.log('M55_STATE_COVERAGE ' + JSON.stringify({ ...counts, checked_task_ids: [...checked_task_ids], network_calls: networkCalls,
    source_mode: draftReview ? 'draft-only-not-frozen' : 'frozen-release', master_sha256: actualMasterSha,
    limitation: 'Offline unchanged shipped factory only; not browser visibility/interaction, widget grading, DB apply, no-JS or print.' }));
});
