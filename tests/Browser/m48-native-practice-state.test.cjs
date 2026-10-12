// Offline state acceptance only: no browser rendering, network, app boot or DB.
// Run the complete shipped Alpine factory and EnglishAnswerVariants unchanged.
// MASTER supplies items; the expectations below independently pin their meanings.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');

const root = path.join(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const sourcePath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const masterPath = 'docs/content/m48-conditionals.v1.0.0.json';
const protectedPaths = [
    sourcePath, masterPath,
    'public/js/english-answer-variants.js',
    'public/data/english-contractions.json',
];
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const originalHashes = new Map(protectedPaths.map(file => [file, sha(read(file))]));
const source = read(sourcePath);
const masterBytes = read(masterPath);
const master = JSON.parse(masterBytes);
const canonicalAnswers = {
    'in-case': [
        'gets', "isn't", "didn't work",
        "I'll take a snack in case the café is closed.",
        'Shall we leave early in case the bus is late?',
        'In case of rain, use the covered entrance.',
    ],
    'wish-if-only': ['were', "hadn't left", 'could', 'a', 'b', 'a'],
    'would-rather': [
        'take', 'not wait', 'I waited',
        "I'd rather cook tonight than order pizza.",
        "I'd rather you didn't send the file to Olha before noon.",
        "I'd rather not.",
    ],
};
const correctTranslations = {
    'in-case': [
        'Я покладу светр у сумку на випадок, якщо завтра стане холодно.',
        'Я залишу записку на випадок, якщо Марти немає вдома.',
        'Я взяв запасну ручку на випадок, якщо моя не писатиме.',
        'Я візьму перекус на випадок, якщо кав’ярня зачинена.',
        'Може, вийдемо раніше на випадок, якщо автобус запізниться?',
        'У разі дощу користуйтеся накритим входом.',
    ],
    'wish-if-only': [
        'От якби я зараз сидів біля озера.',
        'Шкода, що я вчора залишив свій квиток удома.',
        'Хотілося б мати змогу приєднатися до вас завтра.',
        'Сподіваюся, Леся зможе приєднатися до нас завтра.',
        'Хотілося б, щоб принтер не зупинявся через кожні кілька сторінок.',
        'От якби я не надіслав того повідомлення вчора ввечері!',
    ],
    'would-rather': [
        'Я б краще поїхав завтра потягом о сьомій.',
        'Ми б воліли не чекати надворі сьогодні ввечері.',
        'Ти б волів, щоб я зачекав тут завтра вранці?',
        'Я б краще приготував їжу сьогодні ввечері, ніж замовляв піцу.',
        'Я б волів, щоб ти не надсилав файл Ользі до полудня.',
        'Я б краще не співав.',
    ],
};
const i18n = {
    empty: 'Обери відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.',
    answer: 'Правильна відповідь', score: 'Результат: :correct / :total',
};
const supportedGroups = ['selects', 'choices'];
const answerProperty = { selects: 'selectAnswers', choices: 'choiceAnswers' };
const clone = value => JSON.parse(JSON.stringify(value));
const feedbackKey = value => String(value).trim().toLowerCase();
const scripts = [...source.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)]
    .map(match => match[1])
    .filter(script => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(script));
assert.equal(scripts.length, 1, 'Extract one complete real factory, not copied methods');
assert.doesNotMatch(scripts[0], /@(?:js|json|php|endphp)\b|\{\{!!?/, 'Standalone JavaScript only');

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
    assert.equal(practice.length, 1, lesson.slug + ': one native practice set');
    return { slug: lesson.slug, identity: lesson.identity, locale: lesson.locale, data: practice[0].data };
});
const groupsFor = lesson => supportedGroups.filter(group => (lesson.data[group] || []).length > 0);
const itemsFor = (lesson, group) => lesson.data[group] || [];
const canonical = (lesson, item) => canonicalAnswers[lesson.slug][item.source_index - 1];

function expectedVisibleCorrect(lesson, item, option) {
    // Semantic alternative, not a generic assumption that every item has one key.
    return option === canonical(lesson, item)
        || (lesson.slug === 'wish-if-only' && item.source_index === 1 && option === 'was');
}

function optionsFor(lesson, group, item) {
    return item.options || (group === 'choices' ? lesson.data.choice_options || ['a', 'b'] : lesson.data.options || []);
}

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
        unchanged() { assert.equal(JSON.stringify(config), originalConfig, 'Author items remain immutable'); },
    };
}

function select(state, group, index, value) {
    // The native option @click assignment. Grading is exclusively the real factory.
    state[answerProperty[group]][index] = value;
}

function score(lesson, group, correct) {
    return 'Результат: ' + correct + ' / ' + itemsFor(lesson, group).length;
}

function combinations(optionLists) {
    return optionLists.reduce((rows, options) => rows.flatMap(row => options.map(option => [...row, option])), [[]]);
}

function locate(slug, sourceIndex) {
    const lesson = lessons.find(item => item.slug === slug);
    assert.ok(lesson, slug);
    for (const group of groupsFor(lesson)) {
        const index = itemsFor(lesson, group).findIndex(item => item.source_index === sourceIndex);
        if (index !== -1) return { lesson, group, index, item: lesson.data[group][index] };
    }
    assert.fail(slug + ': missing source_index ' + sourceIndex);
}

test('M48 MASTER has exactly three UK lessons, six source tasks each and 18 native controls with translated outcomes', () => {
    assert.equal(master.version, '1.0.0');
    assert.deepEqual(lessons.map(lesson => lesson.slug), Object.keys(canonicalAnswers));
    let controlCount = 0;
    let optionCount = 0;
    for (const lesson of lessons) {
        assert.equal(lesson.locale, 'uk');
        assert.match(lesson.identity, /^Database\\Seeders\\Page_V3\\Conditionals\\Conditionals(?:InCase|WishIfOnly|WouldRather)TheorySeeder$/);
        assert.equal((lesson.data.inputs || []).length, 0, 'Do not invent manual coverage in M48');
        assert.equal((lesson.data.rephrase || []).length, 0, 'No new free-text engine');
        const items = groupsFor(lesson).flatMap(group => itemsFor(lesson, group));
        assert.equal(items.length, 6);
        assert.deepEqual(items.map(item => item.source_index).sort((a, b) => a - b), [1, 2, 3, 4, 5, 6]);
        for (const group of groupsFor(lesson)) {
            for (const item of itemsFor(lesson, group)) {
                controlCount += 1;
                assert.equal(item.answer, canonical(lesson, item), lesson.slug + '/' + item.source_index);
                assert.ok(Array.isArray(item.accepted), 'Explicit accepted answers');
                assert.ok(item.accepted.includes(item.answer));
                const options = optionsFor(lesson, group, item);
                assert.ok(options.length >= 2, 'A genuine selected-response choice');
                assert.equal(new Set(options).size, options.length);
                assert.ok(options.includes(item.answer));
                assert.ok(options.some(option => !expectedVisibleCorrect(lesson, item, option)));
                for (const option of options) {
                    optionCount += 1;
                    const feedback = item.feedback[feedbackKey(option)];
                    assert.equal(typeof feedback, 'string');
                    assert.ok(feedback.includes(correctTranslations[lesson.slug][item.source_index - 1]),
                        lesson.slug + '/' + item.source_index + ': full correct translation for ' + option);
                }
            }
        }
    }
    assert.equal(controlCount, 18);
    // The lesson's group split and option count are content-driven, not a quota
    // copied from M47. Individual options are all traversed in the state tests.
    assert.ok(optionCount > controlCount);
});

for (const lesson of lessons) {
    test(lesson.slug + ': every visible option preserves raw value, semantic grading and exact translated feedback', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        for (const group of groupsFor(lesson)) {
            for (const [index, item] of itemsFor(lesson, group).entries()) {
                for (const option of optionsFor(lesson, group, item)) {
                    state.resetGroup(group);
                    select(state, group, index, option);
                    assert.equal(state.isChecked(group), false);
                    assert.equal(state.fieldClass(group, index), '', 'No grading class before Check');
                    state.check(group);
                    const expected = expectedVisibleCorrect(lesson, item, option);
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

    test(lesson.slug + ': all actual option combinations keep group scores non-duplicating and item feedback matched', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        let visited = 0;
        let expectedCombinations = 0;
        for (const group of groupsFor(lesson)) {
            const items = itemsFor(lesson, group);
            const options = items.map(item => optionsFor(lesson, group, item));
            expectedCombinations += options.reduce((count, values) => count * values.length, 1);
            for (const values of combinations(options)) {
                visited += 1;
                state.resetGroup(group);
                values.forEach((value, index) => select(state, group, index, value));
                state.check(group);
                state.check(group);
                const expected = items.map((item, index) => expectedVisibleCorrect(lesson, item, values[index]));
                assert.deepEqual(items.map((item, index) => ({
                    source_index: item.source_index, correct: state.isCorrect(group, index),
                    feedback: state.feedbackText(group, index), value: state.userAnswer(group, index),
                })), items.map((item, index) => ({
                    source_index: item.source_index, correct: expected[index],
                    feedback: item.feedback[feedbackKey(values[index])], value: values[index],
                })));
                assert.equal(state.scoreText(group), score(lesson, group, expected.filter(Boolean).length));
            }
        }
        assert.equal(visited, expectedCombinations);
        fixture.unchanged();
    });

    test(lesson.slug + ': empty, partial, full, repeated check and independent reset derive from current answers', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        const groups = groupsFor(lesson);
        for (const group of groups) {
            const items = itemsFor(lesson, group);
            state.check(group);
            assert.equal(state.scoreText(group), score(lesson, group, 0));
            for (const [index] of items.entries()) {
                assert.equal(state.isEmpty(group, index), true);
                assert.equal(state.isCorrect(group, index), false);
                assert.equal(state.feedbackText(group, index), i18n.empty);
            }
            select(state, group, 0, canonical(lesson, items[0]));
            state.check(group);
            assert.equal(state.scoreText(group), score(lesson, group, 1));
            for (let index = 1; index < items.length; index += 1) assert.equal(state.isEmpty(group, index), true);
            items.forEach((item, index) => select(state, group, index, canonical(lesson, item)));
            state.check(group);
            state.check(group);
            assert.equal(state.scoreText(group), score(lesson, group, items.length));
        }
        for (const [position, group] of groups.entries()) {
            state.resetGroup(group);
            assert.equal(state.isChecked(group), false);
            assert.equal(state.scoreText(group), score(lesson, group, 0));
            assert.equal(Object.keys(state[answerProperty[group]]).length, 0);
            for (const otherGroup of groups.slice(position + 1)) {
                assert.equal(state.isChecked(otherGroup), true, 'Reset must not clear another group');
                assert.equal(state.scoreText(otherGroup), score(lesson, otherGroup, itemsFor(lesson, otherGroup).length));
            }
        }
        fixture.unchanged();
    });

    test(lesson.slug + ': wrong, wrong again, edit, correct and reset replace stale errors without locks', () => {
        for (const group of groupsFor(lesson)) {
            for (const [index, item] of itemsFor(lesson, group).entries()) {
                const fixture = stateFor(lesson);
                const { state } = fixture;
                const correct = canonical(lesson, item);
                const wrong = optionsFor(lesson, group, item)
                    .filter(value => !expectedVisibleCorrect(lesson, item, value));
                assert.ok(wrong.length > 0);
                // Wish was/were has only one wrong option: repeat am legitimately.
                for (const value of [wrong[0], wrong[1] || wrong[0]]) {
                    select(state, group, index, value);
                    state.check(group);
                    assert.equal(state.isCorrect(group, index), false);
                    assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(value)]);
                    assert.equal(state.scoreText(group), score(lesson, group, 0));
                    assert.match(state.fieldClass(group, index), /rose/);
                    assert.equal(state.userAnswer(group, index), value);
                }
                const previousFeedback = state.feedbackText(group, index);
                select(state, group, index, correct);
                // The real group stays checked and recomputes on edit. This tests
                // state only, not browser visibility or first-attempt accuracy.
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

    test(lesson.slug + ': declared aliases, surrounding whitespace and letter case retain accepted meaning', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        for (const group of groupsFor(lesson)) {
            for (const [index, item] of itemsFor(lesson, group).entries()) {
                for (const alias of item.accepted) {
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

test('Wish were and was are positive alternatives in one control; only am is wrong and credit never doubles', () => {
    const { lesson, group, index, item } = locate('wish-if-only', 1);
    assert.equal(group, 'selects');
    assert.equal(item.answer, 'were');
    assert.deepEqual(item.accepted, ['were', 'was']);
    assert.deepEqual(optionsFor(lesson, group, item), ['were', 'was', 'am']);
    const fixture = stateFor(lesson);
    const { state } = fixture;
    for (const value of ['am', 'am', 'were', 'was', 'were', 'was']) {
        select(state, group, index, value);
        state.check(group);
        state.check(group);
        const correct = value !== 'am';
        assert.equal(state.isCorrect(group, index), correct);
        assert.equal(state.scoreText(group), score(lesson, group, correct ? 1 : 0));
        assert.equal(state.feedbackText(group, index), item.feedback[value]);
        assert.match(state.fieldClass(group, index), correct ? /emerald/ : /rose/);
        if (correct) assert.match(state.feedbackText(group, index), /^Правильно\./);
    }
    state.resetGroup(group);
    assert.equal(state.scoreText(group), score(lesson, group, 0));
    assert.equal(state.isChecked(group), false);
    fixture.unchanged();
});

test('actual negative answers retain contractions and not, rather than accepting their opposite polarity', () => {
    const cases = [
        ['in-case', 2, ["isn't", 'isn’t', 'is not'], 'is'],
        ['in-case', 3, ["didn't work", 'didn’t work', 'did not work'], 'did work'],
        ['wish-if-only', 2, ["hadn't left", 'hadn’t left', 'had not left'], 'had left'],
        ['would-rather', 2, ['not wait'], 'wait'],
        ['would-rather', 5, [
            "I'd rather you didn't send the file to Olha before noon.",
            'I’d rather you didn’t send the file to Olha before noon.',
            'I would rather you did not send the file to Olha before noon.',
        ], "I'd rather you did send the file to Olha before noon."],
    ];
    for (const [slug, sourceIndex, aliases, opposite] of cases) {
        const { lesson, group, index } = locate(slug, sourceIndex);
        const fixture = stateFor(lesson);
        for (const alias of aliases) {
            select(fixture.state, group, index, alias);
            fixture.state.check(group);
            assert.equal(fixture.state.isCorrect(group, index), true, slug + ': ' + alias);
        }
        select(fixture.state, group, index, opposite);
        assert.equal(fixture.state.isCorrect(group, index), false, 'Normalization must not erase negation');
        fixture.unchanged();
    }
});

test('actual sentence answers tolerate punctuation and contractions without changing clause order or meaning', () => {
    // State probes only: selected-response controls are not free-text UI.
    const cases = [
        ['in-case', 5, [
            'Shall we leave early in case the bus is late?',
            'Shall we leave early in case the bus is late',
        ], 'Shall we leave early in case is the bus late?'],
        ['in-case', 6, [
            'In case of rain, use the covered entrance.',
            'In case of rain use the covered entrance',
        ], 'Use the covered entrance in case it rains.'],
        ['in-case', 4, [
            "I'll take a snack in case the café is closed.",
            'I’ll take a snack in case the café is closed.',
            'I will take a snack in case the café is closed.',
        ], "I'll take a snack if the café is closed."],
        ['would-rather', 4, [
            "I'd rather cook tonight than order pizza.",
            'I would rather cook tonight than order pizza.',
        ], "I'd rather order pizza tonight than cook."],
    ];
    for (const [slug, sourceIndex, aliases, wrong] of cases) {
        const { lesson, group, index } = locate(slug, sourceIndex);
        const fixture = stateFor(lesson);
        for (const alias of aliases) {
            select(fixture.state, group, index, alias);
            assert.equal(fixture.state.isCorrect(group, index), true, alias);
        }
        select(fixture.state, group, index, wrong);
        assert.equal(fixture.state.isCorrect(group, index), false, wrong);
        fixture.unchanged();
    }
});

test('offline execution does not access the network or mutate MASTER, factory, variants engine or contraction data', () => {
    assert.equal(networkCalls, 0);
    for (const [file, originalHash] of originalHashes) assert.equal(sha(read(file)), originalHash, file);
    assert.equal(JSON.stringify(master), JSON.stringify(JSON.parse(masterBytes)), 'Author content is immutable');
});
