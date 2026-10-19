// Offline state acceptance only: no browser rendering, network, app boot or DB.
// Execute the complete shipped Alpine factory and contraction engine unchanged.
// Frozen MASTER supplies items; the independent expectations below pin their goals.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');

const root = path.join(__dirname, '../..');
const read = name => fs.readFileSync(path.join(root, name), 'utf8');
const sourcePath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const masterPath = 'docs/content/m47-conditionals.v1.0.0.json';
const protectedPaths = [
    sourcePath,
    masterPath,
    'public/js/english-answer-variants.js',
    'public/data/english-contractions.json',
];
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const originalHashes = new Map(protectedPaths.map(file => [file, sha(read(file))]));
const source = read(sourcePath);
const masterBytes = read(masterPath);
const master = JSON.parse(masterBytes);
const canonicalAnswers = {
    'third-conditional': [
        'written', "wouldn't have lost", 'Would you have waited if I had called?',
        'If Roman had won the final, he would have felt proud that night.',
        'Мовець розглядає інший перебіг завершеної події.',
        'If Taras had seen the message, he would have replied.',
    ],
    'mixed-conditionals': ['had charged', 'wouldn’t have left', 'a', 'b', 'a', 'b'],
    'unless-provided-as-long-as': ['clears', 'in case', 'b', 'a', 'b', 'a'],
};
const correctTranslations = {
    'third-conditional': [
        'Якби Оля записала адресу, то знайшла б студію.',
        'Якби Марта зберегла копію, то не втратила б свої нотатки.',
        'Ти б почекав, якби я зателефонував?',
        'Якби Роман виграв фінал, того вечора він відчував би гордість.',
        'Якби команда перевірила файл, то помітила б пропущену сторінку.',
        'Якби Тарас побачив повідомлення, то відповів би.',
    ],
    'mixed-conditionals': [
        'Якби я зарядив телефон учора ввечері, то зараз міг би ним користуватися.',
        'Якби Ден був уважнішим, то вчора не залишив би паспорт удома.',
        'Якби я прийняла ту роботу, то жила б у Лондоні.',
        'Якби Сара була впевненішою, то вчора попросила б допомоги.',
        'Чи знала б Марта систему, якби пройшла курс?',
        'Якби Том був уважнішим, то, можливо, учора помітив би помилку.',
    ],
    'unless-provided-as-long-as': [
        'Пором відпливе о дев’ятій за умови, що туман розсіється.',
        'Поклади запасну ручку на випадок, якщо у твоїй ручці закінчиться чорнило.',
        'Якщо сьогодні вночі опалення не працюватиме, Міла залишиться в сестри.',
        'Марко чекатиме вдома, хіба що кур’єр не розвозить посилки по понеділках.',
        'Ада співала весь час, поки Лео грав на піаніно.',
        'Софія може позичити велосипед за умови, що поверне його до шостої.',
    ],
};
const i18n = {
    empty: 'Обери відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.',
    answer: 'Правильна відповідь', score: 'Результат: :correct / :total',
};
const groups = ['selects', 'choices'];
const answerProperty = { selects: 'selectAnswers', choices: 'choiceAnswers' };
const clone = value => JSON.parse(JSON.stringify(value));
const feedbackKey = value => String(value).trim().toLowerCase();
const scripts = [...source.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)]
    .map(match => match[1])
    .filter(script => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(script));
assert.equal(scripts.length, 1, 'Extract one complete real factory, not copied methods');
assert.doesNotMatch(scripts[0], /@(?:js|json|php|endphp)\b|\{\{!!?/, 'The script must be standalone JavaScript');

let initListener;
let factory;
let networkCalls = 0;
const context = vm.createContext({
    window: { EnglishAnswerVariants: require('./load-answer-variants.cjs') },
    document: {
        addEventListener(name, listener) {
            assert.equal(name, 'alpine:init');
            assert.equal(initListener, undefined);
            initListener = listener;
        },
        querySelector() { throw new Error('DOM access is outside this offline state suite'); },
    },
    Alpine: {
        data(name, registeredFactory) {
            assert.equal(name, 'theoryPracticeSet');
            assert.equal(factory, undefined);
            factory = registeredFactory;
        },
    },
    fetch() {
        networkCalls += 1;
        throw new Error('Network access is forbidden in this offline state suite');
    },
    console,
    URL,
});
vm.runInContext(scripts[0], context, { filename: sourcePath, timeout: 1000 });
assert.equal(typeof initListener, 'function');
initListener();
assert.equal(typeof factory, 'function');

const lessons = master.lessons.map(lesson => {
    const practice = lesson.blocks.filter(block => block.type === 'practice-set');
    assert.equal(practice.length, 1, lesson.slug + ': exactly one native practice set');
    return { slug: lesson.slug, identity: lesson.identity, locale: lesson.locale, data: practice[0].data };
});

function stateFor(lesson) {
    const config = clone({
        selects: lesson.data.selects || [], choices: lesson.data.choices || [],
        inputs: lesson.data.inputs || [], rephrase: lesson.data.rephrase || [],
        i18n, wordSearchEndpoint: '',
    });
    const originalConfig = JSON.stringify(config);
    const state = factory(config);
    state.init();
    return {
        state,
        unchanged() { assert.equal(JSON.stringify(config), originalConfig, 'Author items are not mutable answer state'); },
    };
}

function optionsFor(lesson, group, item) {
    return item.options || (group === 'choices' ? lesson.data.choice_options || ['a', 'b'] : lesson.data.options || []);
}

function answer(lesson, item) {
    return canonicalAnswers[lesson.slug][item.source_index - 1];
}

function select(state, group, index, value) {
    // Exactly the native option @click assignment, without a synthetic checker.
    state[answerProperty[group]][index] = value;
}

function score(lesson, group, correct) {
    return 'Результат: ' + correct + ' / ' + lesson.data[group].length;
}

function combinations(optionLists) {
    return optionLists.reduce((rows, options) => rows.flatMap(row => options.map(option => [...row, option])), [[]]);
}

test('M47 frozen MASTER has the exact three UK lessons, 18 controls and 42 translated option outcomes', () => {
    assert.equal(master.version, '1.0.0');
    assert.deepEqual(lessons.map(lesson => lesson.slug), Object.keys(canonicalAnswers));
    let controls = 0;
    let visibleOptions = 0;
    for (const lesson of lessons) {
        assert.equal(lesson.locale, 'uk');
        assert.match(lesson.identity, /^Database\\Seeders\\Page_V3\\Conditionals\\Conditionals(?:Third|Mixed|UnlessProvidedAsLongAs)TheorySeeder$/);
        assert.equal(lesson.data.selects.length, 2);
        assert.equal(lesson.data.choices.length, 4);
        assert.equal((lesson.data.inputs || []).length, 0);
        assert.equal((lesson.data.rephrase || []).length, 0);
        const items = [...lesson.data.selects, ...lesson.data.choices];
        assert.deepEqual(items.map(item => item.source_index), [1, 2, 3, 4, 5, 6]);
        assert.deepEqual(items.map(item => item.answer), canonicalAnswers[lesson.slug]);
        for (const group of groups) {
            for (const item of lesson.data[group]) {
                controls += 1;
                const correct = answer(lesson, item);
                assert.ok((item.accepted || [item.answer]).includes(correct));
                const options = optionsFor(lesson, group, item);
                assert.equal(options.length, group === 'selects' ? 3 : 2);
                assert.equal(new Set(options).size, options.length);
                assert.ok(options.includes(correct));
                for (const option of options) {
                    visibleOptions += 1;
                    const feedback = item.feedback[feedbackKey(option)];
                    assert.equal(typeof feedback, 'string');
                    assert.ok(feedback.includes(correctTranslations[lesson.slug][item.source_index - 1]),
                        lesson.slug + '/' + item.source_index + ': correct full translation for ' + option);
                }
            }
        }
    }
    assert.equal(controls, 18);
    assert.equal(visibleOptions, 42);
});

for (const lesson of lessons) {
    test(lesson.slug + ': each visible candidate retains its value, grading and exact translated author feedback', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        for (const group of groups) {
            for (const [index, item] of lesson.data[group].entries()) {
                for (const option of optionsFor(lesson, group, item)) {
                    state.resetGroup(group);
                    select(state, group, index, option);
                    assert.equal(state.isChecked(group), false);
                    assert.equal(state.fieldClass(group, index), '', 'No state grading class before Check');
                    state.check(group);
                    const expected = option === answer(lesson, item);
                    assert.deepEqual({
                        correct: state.isCorrect(group, index), feedback: state.feedbackText(group, index),
                        score: state.scoreText(group), value: state.userAnswer(group, index),
                    }, {
                        correct: expected, feedback: item.feedback[feedbackKey(option)],
                        score: score(lesson, group, expected ? 1 : 0), value: option,
                    }, item.source_index + ': ' + option);
                }
            }
        }
        fixture.unchanged();
    });

    test(lesson.slug + ': all exercise-group option combinations have non-duplicating scores and matching per-item feedback', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        let combinationsVisited = 0;
        for (const group of groups) {
            const items = lesson.data[group];
            for (const values of combinations(items.map(item => optionsFor(lesson, group, item)))) {
                combinationsVisited += 1;
                state.resetGroup(group);
                values.forEach((value, index) => select(state, group, index, value));
                state.check(group);
                state.check(group);
                const expectedCorrect = items.map((item, index) => values[index] === answer(lesson, item));
                assert.deepEqual(items.map((item, index) => ({
                    source_index: item.source_index, correct: state.isCorrect(group, index),
                    feedback: state.feedbackText(group, index), value: state.userAnswer(group, index),
                })), items.map((item, index) => ({
                    source_index: item.source_index, correct: expectedCorrect[index],
                    feedback: item.feedback[feedbackKey(values[index])], value: values[index],
                })));
                assert.equal(state.scoreText(group), score(lesson, group, expectedCorrect.filter(Boolean).length));
            }
        }
        assert.equal(combinationsVisited, 25, 'Nine select combinations and sixteen choice combinations');
        fixture.unchanged();
    });

    test(lesson.slug + ': empty, partial, full, repeated checks and independent resets use current answers', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        for (const group of groups) {
            state.check(group);
            assert.equal(state.scoreText(group), score(lesson, group, 0));
            for (const [index] of lesson.data[group].entries()) {
                assert.equal(state.isEmpty(group, index), true);
                assert.equal(state.isCorrect(group, index), false);
                assert.equal(state.feedbackText(group, index), i18n.empty);
            }
            select(state, group, 0, answer(lesson, lesson.data[group][0]));
            state.check(group);
            assert.equal(state.scoreText(group), score(lesson, group, 1));
            for (let index = 1; index < lesson.data[group].length; index += 1) assert.equal(state.isEmpty(group, index), true);
            lesson.data[group].forEach((item, index) => select(state, group, index, answer(lesson, item)));
            state.check(group);
            state.check(group);
            assert.equal(state.scoreText(group), score(lesson, group, lesson.data[group].length));
        }
        state.resetGroup('selects');
        assert.equal(state.isChecked('selects'), false);
        assert.equal(state.scoreText('selects'), score(lesson, 'selects', 0));
        assert.equal(Object.keys(state.selectAnswers).length, 0);
        assert.equal(state.isChecked('choices'), true);
        assert.equal(state.scoreText('choices'), score(lesson, 'choices', 4));
        state.resetGroup('choices');
        assert.equal(state.isChecked('choices'), false);
        assert.equal(state.scoreText('choices'), score(lesson, 'choices', 0));
        assert.equal(Object.keys(state.choiceAnswers).length, 0);
        fixture.unchanged();
    });

    test(lesson.slug + ': two wrong attempts then correction replace stale feedback without locks or duplicate credit', () => {
        for (const group of groups) {
            for (const [index, item] of lesson.data[group].entries()) {
                const fixture = stateFor(lesson);
                const { state } = fixture;
                const correct = answer(lesson, item);
                const wrong = optionsFor(lesson, group, item).filter(value => value !== correct);
                for (const value of [wrong[0], wrong[1] || wrong[0]]) {
                    select(state, group, index, value);
                    state.check(group);
                    assert.equal(state.isCorrect(group, index), false);
                    assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(value)]);
                    assert.equal(state.scoreText(group), score(lesson, group, 0));
                    assert.match(state.fieldClass(group, index), /rose/);
                    assert.equal(state.userAnswer(group, index), value, 'Check cannot replace an answer with its key');
                }
                const previousFeedback = state.feedbackText(group, index);
                select(state, group, index, correct);
                // The actual native group stays checked and reacts to the edit.
                // This does not assert DOM visibility or first-attempt accuracy.
                assert.equal(state.isChecked(group), true);
                assert.equal(state.isCorrect(group, index), true);
                assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(correct)]);
                assert.notEqual(state.feedbackText(group, index), previousFeedback);
                assert.match(state.fieldClass(group, index), /emerald/);
                assert.doesNotMatch(state.fieldClass(group, index), /rose/);
                state.check(group);
                state.check(group);
                assert.equal(state.scoreText(group), score(lesson, group, 1));
                select(state, group, index, '');
                assert.equal(state.feedbackText(group, index), i18n.empty);
                assert.equal(state.scoreText(group), score(lesson, group, 0));
                state.resetGroup(group);
                assert.equal(state.isChecked(group), false);
                assert.equal(state.userAnswer(group, index), '');
                assert.equal(state.fieldClass(group, index), '');
                fixture.unchanged();
            }
        }
    });

    test(lesson.slug + ': declared aliases, surrounding whitespace and letter case remain accepted', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        for (const group of groups) {
            for (const [index, item] of lesson.data[group].entries()) {
                for (const alias of item.accepted || [item.answer]) {
                    for (const value of [alias, '  ' + alias.toUpperCase() + '  ']) {
                        select(state, group, index, value);
                        state.check(group);
                        assert.equal(state.isCorrect(group, index), true, item.source_index + ': ' + value);
                        assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(value)] || i18n.correct);
                    }
                }
            }
        }
        fixture.unchanged();
    });
}

test('actual negative answers accept apostrophe/contraction variants but never the opposite polarity', () => {
    const cases = [
        ['third-conditional', ["wouldn't have lost", 'wouldn’t have lost', 'would not have lost'], 'would have lost'],
        ['mixed-conditionals', ["wouldn't have left", 'wouldn’t have left', 'would not have left'], 'would have left'],
    ];
    for (const [slug, aliases, opposite] of cases) {
        const fixture = stateFor(lessons.find(lesson => lesson.slug === slug));
        const { state } = fixture;
        for (const alias of aliases) {
            select(state, 'selects', 1, alias);
            state.check('selects');
            assert.equal(state.isCorrect('selects', 1), true, alias);
        }
        select(state, 'selects', 1, opposite);
        assert.equal(state.isCorrect('selects', 1), false, 'Negation cannot disappear during normalization');
        fixture.unchanged();
    }
});

test('actual full-sentence choice answers retain existing comma/question-mark tolerance, not a wrong word order', () => {
    const lesson = lessons.find(item => item.slug === 'third-conditional');
    const fixture = stateFor(lesson);
    const { state } = fixture;
    // State normalization probes only: these selected-response controls are not
    // presented as text-input UI and this does not claim punctuation teaching.
    for (const value of ['Would you have waited if I had called?', 'Would you have waited if I had called']) {
        select(state, 'choices', 0, value);
        assert.equal(state.isCorrect('choices', 0), true);
    }
    select(state, 'choices', 0, 'Would have you waited if I had called?');
    assert.equal(state.isCorrect('choices', 0), false);
    const canonical = canonicalAnswers[lesson.slug][3];
    for (const value of [canonical, canonical.replace(',', ''), canonical.replace(/\.$/, '')]) {
        select(state, 'choices', 1, value);
        assert.equal(state.isCorrect('choices', 1), true);
    }
    select(state, 'choices', 1, 'If Roman had won the final, he would feel proud now.');
    assert.equal(state.isCorrect('choices', 1), false, 'Punctuation tolerance cannot change the result time');
    fixture.unchanged();
});

test('offline execution does not access the network or change MASTER, factory, variants engine or contraction data', () => {
    assert.equal(networkCalls, 0);
    for (const [file, originalHash] of originalHashes) assert.equal(sha(read(file)), originalHash, file);
    assert.equal(JSON.stringify(master), JSON.stringify(JSON.parse(masterBytes)), 'Loaded author content is immutable');
});
