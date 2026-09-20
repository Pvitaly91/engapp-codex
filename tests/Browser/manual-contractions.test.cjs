// Exercise the real Blade answer/commit/render functions, not copies of their logic.
// The unrelated card shell, network persistence and final choice side effects are
// isolated; manual fields and focus use an actual jsdom document.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { JSDOM } = require('jsdom');

const root = path.join(__dirname, '../..');
const helpers = fs.readFileSync(path.join(root, 'resources/views/components/saved-test-js-helpers.blade.php'), 'utf8');

function readFunction(source, name) {
    const start = source.indexOf(`function ${name}(`);
    assert.notEqual(start, -1, `Missing production function ${name}`);
    const remainder = source.slice(start);
    // These top-level template functions close at column zero; nested blocks do not.
    const end = remainder.search(/^\}/m);
    assert.notEqual(end, -1, `Missing closing brace for ${name}`);
    return remainder.slice(0, end + 1);
}

function fixture(t, mode, expected, overrides = {}) {
    const source = fs.readFileSync(path.join(root, `resources/views/test-modes/${mode}.blade.php`), 'utf8');
    const dom = new JSDOM('<!doctype html><body></body>', { url: 'http://gramlyze.loc/test/fixture' });
    t.after(() => dom.window.close());
    const question = {
        type: 'compose_tokens',
        question: 'Ти не працюєш останню годину.',
        markers: ['a1', 'a2', 'a3'],
        markers_count: 3,
        answers: ['You', expected, 'working'],
        chosen: ['You', null, null],
        activeSlot: 1,
        done: false,
        wrongAttempt: false,
        manualInputsBySlot: ['', '', ''],
        manualWordIndexBySlot: [0, 0, 0],
        lastWrongBySlot: [null, null, null],
        feedbackMeta: null,
        ...overrides,
    };
    const state = { items: [question], current: 0, answered: 0, correct: 0 };
    const choices = [];
    const context = vm.createContext({
        window: dom.window,
        document: dom.window.document,
        console,
        state,
        COMPOSE_TOKENS_QUESTION_TYPE: 'compose_tokens',
        IS_POLYGLOT_STEP_PREVIEW: false,
        testUi: key => key,
        persistState() {},
        setTimeout(fn) { fn(); return 0; },
        renderManualSuggestions: () => '',
    });

    const sharedFunctions = [
        'canonicalTestAnswer', 'acceptedTestAnswers', 'testAnswerMatches',
        'composeManualAnswerWords', 'html',
    ];
    const modeFunctions = [
        'getMarkersCount', 'isPolyglotComposeQuestion', 'getMarkerLabel',
        'findFirstUnfilledSlot', 'clampActiveSlot', 'questionContainsSlotMarker',
        'shouldRenderPolyglotTranslationPreview', 'ensureManualSlotState',
        'manualAnswerWords', 'manualAnswerProgress', 'manualAnswerIsComplete',
        'collectManualAnswer', 'manualInputId', 'rememberFeedbackAnswer',
        'manualWordFeedbackState', 'renderManualGapInput', 'focusManualAnswer',
        'invalidateManualSuggestions', 'commitManualWord', 'submitManualAnswer',
    ];
    if (mode === 'card-easy') {
        modeFunctions.push('acceptedAnswersForSlot', 'answerMatchesSlot');
    }
    vm.runInContext([
        ...sharedFunctions.map(name => readFunction(helpers, name)),
        ...modeFunctions.map(name => readFunction(source, name)),
    ].join('\n'), context);

    function render() {
        const item = state.items[0];
        dom.window.document.body.innerHTML = context.renderManualGapInput(item, 0, item.activeSlot, 'preview');
    }
    context.rerenderCard = render;
    context.render = render;
    // The real submitManualAnswer calls this only after a whole slot matches.
    // Card-wide score/explanation/network behavior is outside this regression.
    context.onChoose = (...args) => {
        const answer = mode === 'card-easy' ? args[1] : args[0];
        const item = state.items[0];
        assert.equal(context.testAnswerMatches(item, item.activeSlot, answer), true);
        choices.push({ slot: item.activeSlot, answer });
        item.chosen[item.activeSlot] = answer;
        context.clampActiveSlot(item);
        render();
    };
    render();

    return {
        context,
        state,
        choices,
        render,
        document: dom.window.document,
        question: () => state.items[0],
        inputs: () => [...dom.window.document.querySelectorAll('input[data-manual-gap]')],
        activeInput: () => dom.window.document.querySelector('input[data-manual-active="true"]'),
        words: () => Array.from(context.manualAnswerWords(state.items[0], 1)),
        progress: value => context.manualAnswerProgress(state.items[0], 1, value),
        commit(value) {
            const input = dom.window.document.querySelector('input[data-manual-active="true"]');
            assert.ok(input, 'An active manual field must be present');
            input.value = value;
            context.commitManualWord(0, Number(input.dataset.manualGap), Number(input.dataset.manualWord), 'preview');
        },
    };
}

const negatives = [
    { contracted: "haven't", auxiliary: 'have' },
    { contracted: "hasn't", auxiliary: 'has' },
    { contracted: "won't", auxiliary: 'will' },
    { contracted: "hadn't", auxiliary: 'had' },
];

for (const mode of ['card-easy', 'step-easy']) {
    for (const { contracted, auxiliary } of negatives) {
        test(`${mode}: ${auxiliary} opens an active not field without completing ${contracted}`, t => {
            const f = fixture(t, mode, contracted);
            const original = JSON.stringify({ markers: f.question().markers, answers: f.question().answers });
            assert.equal(f.inputs().length, 1);
            assert.equal(f.progress(auxiliary), 'prefix');
            assert.equal(f.progress(`${auxiliary} not`), 'complete');
            assert.equal(f.progress(`${auxiliary} been`), 'incorrect');

            f.commit(auxiliary);

            assert.equal(f.choices.length, 0, 'A prefix must not submit the logical slot');
            assert.equal(f.question().chosen[1], null);
            assert.equal(f.question().activeSlot, 1);
            assert.equal(f.question().manualWordIndexBySlot[1], 1, 'Rerender must not clamp back to word zero');
            assert.equal(f.inputs().length, 2);
            assert.equal(f.inputs()[0].value, auxiliary);
            assert.equal(f.inputs()[0].disabled, true);
            assert.equal(f.activeInput().dataset.manualWord, '1');
            assert.equal(f.activeInput().disabled, false);
            assert.equal(f.activeInput().value, '');
            assert.equal(f.document.activeElement, f.activeInput());
            assert.deepEqual(f.words(), [auxiliary, 'not']);
            assert.equal(JSON.stringify({ markers: f.question().markers, answers: f.question().answers }), original);

            f.commit('not');

            assert.deepEqual(f.choices, [{ slot: 1, answer: `${auxiliary} not` }]);
            assert.equal(f.question().chosen[1], `${auxiliary} not`);
            assert.equal(f.question().activeSlot, 2);
            assert.equal(f.question().wrongAttempt, false);
            assert.equal(f.inputs().length, 1, 'The next marker must not inherit the additional field');
        });

        for (const apostrophe of ["'", '’']) {
            test(`${mode}: ${contracted.replace("'", apostrophe)} completes immediately in one field`, t => {
                const answer = contracted.replace("'", apostrophe);
                const f = fixture(t, mode, contracted);
                assert.equal(f.inputs().length, 1);
                assert.equal(f.progress(answer), 'complete');
                f.commit(answer);
                assert.deepEqual(f.choices, [{ slot: 1, answer }]);
                assert.equal(f.question().activeSlot, 2);
                assert.equal(f.question().wrongAttempt, false);
            });
        }
    }

    test(`${mode}: an incorrect second word keeps the not field editable for retry`, t => {
        const f = fixture(t, mode, "haven't");
        f.commit('have');
        f.commit('been');
        assert.equal(f.choices.length, 0);
        assert.equal(f.question().chosen[1], null);
        assert.equal(f.question().manualWordIndexBySlot[1], 1);
        assert.equal(f.inputs().length, 2);
        assert.equal(f.activeInput().dataset.answerState, 'incorrect');
        assert.equal(f.activeInput().value, 'been');
        assert.equal(f.document.activeElement, f.activeInput());
        assert.equal(f.question().feedbackMeta.displayedAnswer, 'not');
        f.commit('not');
        assert.deepEqual(f.choices, [{ slot: 1, answer: 'have not' }]);
        assert.equal(f.question().wrongAttempt, true, 'Retry must preserve first-attempt accuracy');
    });

    test(`${mode}: a restored partial answer renders the active second field`, t => {
        const f = fixture(t, mode, "haven't");
        f.commit('have');
        f.state.items[0] = JSON.parse(JSON.stringify(f.question()));
        f.render();
        assert.equal(f.inputs().length, 2);
        assert.equal(f.question().manualWordIndexBySlot[1], 1);
        assert.equal(f.activeInput().dataset.manualWord, '1');
        assert.equal(f.inputs()[0].value, 'have');
        assert.equal(f.question().chosen[1], null);
        f.commit('not');
        assert.deepEqual(f.choices, [{ slot: 1, answer: 'have not' }]);
    });

    for (const first of ["haven't", 'have']) {
        test(`${mode}: a multiword compose token follows the ${first} branch`, t => {
            const f = fixture(t, mode, "haven't been");
            assert.equal(f.inputs().length, 1, 'Compose groups start with one field');
            f.commit(first);
            assert.equal(f.inputs().length, 2);
            assert.equal(f.question().manualWordIndexBySlot[1], 1);
            assert.equal(f.choices.length, 0);
            if (first === 'have') {
                f.commit('not');
                assert.equal(f.inputs().length, 3);
                assert.equal(f.question().manualWordIndexBySlot[1], 2);
                assert.equal(f.activeInput().dataset.manualWord, '2');
                assert.equal(f.choices.length, 0);
            }
            f.commit('been');
            assert.deepEqual(f.choices, [{ slot: 1, answer: first === 'have' ? 'have not been' : "haven't been" }]);
            assert.equal(f.question().activeSlot, 2);
            assert.equal(f.question().wrongAttempt, false);
        });
    }

    test(`${mode}: an explicitly accepted longer synonym gets its continuation field`, t => {
        const f = fixture(t, mode, 'finish', {
            accepted_answers_by_marker: { a2: ['finish', 'complete work'] },
        });
        assert.equal(f.inputs().length, 1);
        f.commit('complete');
        assert.deepEqual(f.words(), ['complete', 'work']);
        assert.equal(f.inputs().length, 2);
        assert.equal(f.activeInput().dataset.manualWord, '1');
        assert.equal(f.choices.length, 0);
        f.commit('work');
        assert.deepEqual(f.choices, [{ slot: 1, answer: 'complete work' }]);
        assert.equal(f.question().answers[1], 'finish', 'Accepted alternatives must not mutate canonical content');
    });

    test(`${mode}: regular gap answers retain their existing multiword fields`, t => {
        const f = fixture(t, mode, "haven't been", { type: 'gap', question: '{a2} working.' });
        assert.equal(f.inputs().length, 3);
        f.commit('have');
        assert.equal(f.inputs().length, 3);
        assert.equal(f.question().manualWordIndexBySlot[1], 1);
        assert.equal(f.activeInput().dataset.manualWord, '1');
        f.commit('not');
        f.commit('been');
        assert.deepEqual(f.choices, [{ slot: 1, answer: 'have not been' }]);
    });
}
