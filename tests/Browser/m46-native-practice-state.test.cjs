// Offline native-state checks, not a live browser/Alpine rendering acceptance.
// The complete shipped factory and contraction engine run unchanged in a VM;
// M46 MASTER supplies independent author data. Network and DOM work are forbidden.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');

const root = path.join(__dirname, '../..');
const read = name => fs.readFileSync(path.join(root, name), 'utf8');
const sourcePath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const masterPath = 'docs/content/m46-conditionals.v1.0.1.json';
const source = read(sourcePath);
const masterBytes = read(masterPath);
const master = JSON.parse(masterBytes);
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const originalHashes = { source: sha(source), master: sha(masterBytes) };
const canonicalAnswers = {
    'zero-conditional': ['starts', "doesn't work", 'detect a page', 'a', 'b', 'a'],
    'first-conditional': ['rains', "doesn't arrive", 'Will you wait', 'a', 'b', 'a'],
    'second-conditional': ['had', 'wouldn’t take', 'ride', 'a', 'b', 'b'],
};
const negativeCases = {
    'zero-conditional': { aliases: ["doesn't work", 'doesn’t work', 'does not work'], opposite: 'does work' },
    'first-conditional': { aliases: ["doesn't arrive", 'doesn’t arrive', 'does not arrive'], opposite: 'does arrive' },
    'second-conditional': { aliases: ["wouldn't take", 'wouldn’t take', 'would not take'], opposite: 'would take' },
};
const i18n = {
    empty: 'Обери відповідь.',
    correct: 'Правильно.',
    incorrect: 'Неправильно.',
    answer: 'Правильна відповідь',
    score: 'Результат: :correct / :total',
};
const groups = ['selects', 'choices'];
const answerProperty = { selects: 'selectAnswers', choices: 'choiceAnswers' };
const clone = value => JSON.parse(JSON.stringify(value));
const feedbackKey = value => String(value).trim().toLowerCase();

const scripts = [...source.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)]
    .map(match => match[1])
    .filter(script => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(script));
assert.equal(scripts.length, 1, 'Load the one complete shipped factory, not copied methods');
assert.doesNotMatch(scripts[0], /@(?:js|json|php|endphp)\b|\{\{!!?/, 'The selected native script must be standalone JS');

let initListener;
let factory;
let networkCalls = 0;
const context = vm.createContext({
    window: { EnglishAnswerVariants: require('./load-answer-variants.cjs') },
    document: {
        addEventListener(name, listener) {
            assert.equal(name, 'alpine:init');
            assert.equal(initListener, undefined, 'Do not replace a factory registration');
            initListener = listener;
        },
        querySelector() { throw new Error('DOM access is outside this state-only suite'); },
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
        throw new Error('Network access is forbidden in this offline suite');
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
    assert.equal(practice.length, 1, lesson.slug + ': one native practice-set');
    return { slug: lesson.slug, identity: lesson.identity, locale: lesson.locale, data: practice[0].data };
});

function stateFor(lesson) {
    const config = clone({
        selects: lesson.data.selects || [],
        choices: lesson.data.choices || [],
        inputs: lesson.data.inputs || [],
        rephrase: lesson.data.rephrase || [],
        i18n,
        wordSearchEndpoint: '',
    });
    const frozenConfig = JSON.stringify(config);
    const state = factory(config);
    state.init();
    return {
        state,
        unchanged() { assert.equal(JSON.stringify(config), frozenConfig, 'State transitions must not mutate author items'); },
    };
}

function optionsFor(lesson, group, item) {
    return item.options || (group === 'choices' ? lesson.data.choice_options || ['a', 'b'] : lesson.data.options || []);
}

function answer(lesson, item) {
    return canonicalAnswers[lesson.slug][item.source_index - 1];
}

function select(state, group, index, value) {
    // This is the actual option @click operation; no synthetic checker or watcher.
    state[answerProperty[group]][index] = value;
}

function score(correct, total = 3) {
    return 'Результат: ' + correct + ' / ' + total;
}

test('M46 MASTER exposes exactly 3 UK lessons, 18 controls and 45 translated visible option outcomes', () => {
    assert.deepEqual(lessons.map(lesson => lesson.slug), Object.keys(canonicalAnswers));
    let controls = 0;
    let visibleOptions = 0;
    for (const lesson of lessons) {
        assert.equal(lesson.locale, 'uk');
        assert.match(lesson.identity, /^Database\\Seeders\\Page_V3\\Conditionals\\Conditionals(?:Zero|First|Second)TheorySeeder$/);
        assert.equal(lesson.data.selects.length, 3);
        assert.equal(lesson.data.choices.length, 3);
        assert.equal((lesson.data.inputs || []).length, 0);
        assert.equal((lesson.data.rephrase || []).length, 0);
        const items = [...lesson.data.selects, ...lesson.data.choices];
        assert.deepEqual(items.map(item => item.source_index), [1, 2, 3, 4, 5, 6]);
        assert.deepEqual(items.map(item => item.answer), canonicalAnswers[lesson.slug]);
        for (const group of groups) {
            for (const item of lesson.data[group]) {
                controls += 1;
                assert.ok(item.accepted.includes(answer(lesson, item)));
                const options = optionsFor(lesson, group, item);
                assert.equal(options.length, group === 'selects' ? 3 : 2);
                assert.equal(new Set(options).size, options.length);
                assert.ok(options.includes(answer(lesson, item)));
                for (const option of options) {
                    visibleOptions += 1;
                    const feedback = item.feedback[feedbackKey(option)];
                    assert.equal(typeof feedback, 'string');
                    assert.match(feedback, /Переклад/, lesson.slug + '/' + item.source_index + '/' + option);
                    assert.match(feedback, /[іїєґІЇЄҐ]/u, 'The explanation includes Ukrainian text');
                }
            }
        }
    }
    assert.equal(controls, 18);
    assert.equal(visibleOptions, 45);
});

for (const lesson of lessons) {
    test(lesson.slug + ': every visible option uses the author outcome and its exact translated feedback', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        for (const group of groups) {
            for (const [index, item] of lesson.data[group].entries()) {
                for (const option of optionsFor(lesson, group, item)) {
                    state.resetGroup(group);
                    select(state, group, index, option);
                    assert.equal(state.isChecked(group), false);
                    assert.equal(state.fieldClass(group, index), '', 'No pre-check grading class');
                    state.check(group);
                    const expected = option === answer(lesson, item);
                    assert.equal(state.isCorrect(group, index), expected, item.source_index + ': ' + option);
                    assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(option)]);
                    assert.equal(state.scoreText(group), score(expected ? 1 : 0));
                    assert.equal(state.userAnswer(group, index), option, 'A wrong answer is never replaced by the canonical answer');
                }
            }
        }
        fixture.unchanged();
    });

    test(lesson.slug + ': empty, partial, fully correct and independent group resets retain deterministic scores', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        for (const group of groups) {
            assert.equal(state.isChecked(group), false);
            state.check(group);
            assert.equal(state.scoreText(group), score(0));
            for (const [index] of lesson.data[group].entries()) {
                assert.equal(state.isEmpty(group, index), true);
                assert.equal(state.isCorrect(group, index), false);
                assert.equal(state.feedbackText(group, index), i18n.empty);
            }
            select(state, group, 0, answer(lesson, lesson.data[group][0]));
            state.check(group);
            assert.equal(state.scoreText(group), score(1));
            assert.equal(state.isEmpty(group, 1), true);
            assert.equal(state.isEmpty(group, 2), true);
            for (const [index, item] of lesson.data[group].entries()) select(state, group, index, answer(lesson, item));
            state.check(group);
            state.check(group);
            assert.equal(state.scoreText(group), score(3), 'Repeated checks cannot add duplicate credit');
        }
        state.resetGroup('selects');
        assert.equal(state.isChecked('selects'), false);
        assert.equal(state.scoreText('selects'), score(0));
        assert.equal(Object.keys(state.selectAnswers).length, 0);
        assert.equal(state.scoreText('choices'), score(3), 'Resetting selects does not erase choices');
        assert.equal(state.isChecked('choices'), true);
        state.resetGroup('choices');
        assert.equal(state.isChecked('choices'), false);
        assert.equal(state.scoreText('choices'), score(0));
        assert.equal(Object.keys(state.choiceAnswers).length, 0);
        fixture.unchanged();
    });

    test(lesson.slug + ': two wrong checks then correction replace stale feedback without locking or duplicate score', () => {
        for (const group of groups) {
            for (const [index, item] of lesson.data[group].entries()) {
                const fixture = stateFor(lesson);
                const { state } = fixture;
                const correct = answer(lesson, item);
                const wrong = optionsFor(lesson, group, item).filter(option => option !== correct);
                const attempts = [wrong[0], wrong[1] || wrong[0]];
                for (const value of attempts) {
                    select(state, group, index, value);
                    state.check(group);
                    state.check(group);
                    assert.equal(state.isCorrect(group, index), false);
                    assert.equal(state.scoreText(group), score(0));
                    assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(value)]);
                    assert.match(state.fieldClass(group, index), /rose/);
                    assert.equal(state.userAnswer(group, index), value, 'Wrong checks must not autofill the answer');
                }
                const previousFeedback = state.feedbackText(group, index);
                select(state, group, index, correct);
                // Native practice keeps a checked group reactive: this is not a
                // delayed re-check or first-attempt-accuracy test.
                assert.equal(state.isChecked(group), true);
                assert.equal(state.isCorrect(group, index), true);
                assert.equal(state.feedbackText(group, index), item.feedback[feedbackKey(correct)]);
                assert.notEqual(state.feedbackText(group, index), previousFeedback, 'Old wrong feedback is not retained');
                assert.match(state.fieldClass(group, index), /emerald/);
                assert.doesNotMatch(state.fieldClass(group, index), /rose/);
                assert.equal(state.scoreText(group), score(1));
                state.check(group);
                state.check(group);
                assert.equal(state.scoreText(group), score(1));
                select(state, group, index, '');
                assert.equal(state.feedbackText(group, index), i18n.empty);
                assert.equal(state.scoreText(group), score(0));
                state.resetGroup(group);
                assert.equal(state.isChecked(group), false);
                assert.equal(state.userAnswer(group, index), '');
                assert.equal(state.fieldClass(group, index), '');
                fixture.unchanged();
            }
        }
    });

    test(lesson.slug + ': declared aliases, letter case and real contraction variants stay accepted without dropping negation', () => {
        const fixture = stateFor(lesson);
        const { state } = fixture;
        for (const group of groups) {
            for (const [index, item] of lesson.data[group].entries()) {
                for (const alias of item.accepted) {
                    for (const value of [alias, '  ' + alias.toUpperCase() + '  ']) {
                        select(state, group, index, value);
                        state.check(group);
                        assert.equal(state.isCorrect(group, index), true, item.source_index + ': ' + value);
                        const feedback = item.feedback[feedbackKey(value)] || i18n.correct;
                        assert.equal(state.feedbackText(group, index), feedback);
                    }
                }
            }
        }
        for (const alias of negativeCases[lesson.slug].aliases) {
            select(state, 'selects', 1, alias);
            assert.equal(state.isCorrect('selects', 1), true, alias);
        }
        select(state, 'selects', 1, negativeCases[lesson.slug].opposite);
        assert.equal(state.isCorrect('selects', 1), false, 'The polarity-changing form is not an alias');
        fixture.unchanged();
    });
}

test('offline state checks do not use a network or mutate MASTER/native JS source', () => {
    assert.equal(networkCalls, 0);
    assert.equal(sha(read(masterPath)), originalHashes.master);
    assert.equal(sha(read(sourcePath)), originalHashes.source);
    assert.equal(JSON.stringify(master), JSON.stringify(JSON.parse(masterBytes)), 'The loaded MASTER object remains intact');
});
