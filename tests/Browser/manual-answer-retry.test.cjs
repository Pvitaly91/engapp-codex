// Exercise the shipped manual input/keyboard bindings and checker, not copies.
// Card shell rendering, persistence and remote explanations are isolated.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { JSDOM } = require('jsdom');

const root = path.join(__dirname, '../..');
const read = name => fs.readFileSync(path.join(root, name), 'utf8');

function functionSource(source, name) {
    const start = source.indexOf(`function ${name}(`);
    assert.notEqual(start, -1, `Missing shipped function ${name}`);
    const remainder = source.slice(start);
    const end = remainder.search(/^\}/m);
    assert.notEqual(end, -1, `Missing closing brace for ${name}`);
    return remainder.slice(0, end + 1);
}

function inputBinding(source, mode) {
    const startText = mode === 'card-easy'
        ? "card.addEventListener('input', (e) => {"
        : "document.getElementById('question-card').addEventListener('input', (e) => {";
    const start = source.indexOf(startText);
    assert.notEqual(start, -1, `${mode} must bind a real manual input listener`);
    const remainder = source.slice(start);
    const endText = mode === 'card-easy' ? '\n    });' : '\n});';
    const end = remainder.indexOf(endText);
    assert.notEqual(end, -1, `Missing input binding close in ${mode}`);
    return remainder.slice(0, end + endText.length);
}

function fixture(t, mode, overrides = {}) {
    const source = read(`resources/views/test-modes/${mode}.blade.php`);
    const shared = read('resources/views/components/saved-test-js-helpers.blade.php');
    const keyboard = read('resources/views/components/test-suggestion-keyboard.blade.php');
    const dom = new JSDOM('<!doctype html><body><article id="question-card" data-idx="0"></article></body>', {
        url: 'http://gramlyze.loc/test/past-perfect-continuous/forms',
    });
    t.after(() => dom.window.close());
    const q = {
        type: 'multiple_choice', tense: 'Tenses / Past Perfect Continuous',
        question: 'Mia {a1} notes for half an hour before lunch.',
        markers: ['a1'], markers_count: 1, answers: ['had been making'],
        chosen: [null], activeSlot: 0, done: false, wrongAttempt: false,
        feedback: '', feedbackMeta: null, explanation: '', pendingExplanationKey: null,
        manualInputsBySlot: [''], manualWordIndexBySlot: [0],
        attemptsBySlot: [0], lastWrongBySlot: [null],
        ...overrides,
    };
    const state = { items: [q], current: 0, activeCardIdx: 0, answered: 0, correct: 0 };
    const card = dom.window.document.getElementById('question-card');
    const counters = { renders: 0, persists: 0, progress: 0 };
    const context = vm.createContext({
        window: dom.window, document: dom.window.document, console, Promise,
        state, q, card, idx: 0,
        EnglishAnswerVariants: require('./load-answer-variants.cjs'),
        COMPOSE_TOKENS_QUESTION_TYPE: 'compose_tokens', IS_POLYGLOT_STEP_PREVIEW: false,
        testUi: (key, params = {}) => `${key}${params.answer ? ': ' + params.answer : ''}`,
        setTimeout(fn) { fn(); return 0; },
        persistState() { counters.persists += 1; },
        updateProgress() { counters.progress += 1; }, checkAllDone() {},
        renderManualSuggestions: () => '', searchManualWords() {},
        buildExplanationKey: (wrong, correct) => `${wrong}|${correct}`,
        ensureExplanation: () => Promise.resolve(''),
        isSentenceReorderQuestion: () => false,
        renderMarkerTheoryButton: () => '', hasMarkerTags: () => false,
        renderMarkerTagsDebug: () => '',
    });
    const sharedNames = [
        'canonicalTestAnswer', 'acceptedTestAnswers', 'testAnswerMatches',
        'composeManualAnswerWords', 'html', 'clearManualAnswerFeedbackOnEdit',
        'isPastPerfectContinuousQuestion', 'restoreSavedPpcRetryState',
    ];
    const names = [
        'getMarkersCount', 'isPolyglotComposeQuestion', 'getMarkerLabel',
        'findFirstUnfilledSlot', 'clampActiveSlot', 'questionContainsSlotMarker',
        'shouldRenderPolyglotTranslationPreview', 'ensureManualSlotState',
        'manualAnswerWords', 'manualAnswerProgress', 'manualAnswerIsComplete',
        'collectManualAnswer', 'manualInputId', 'rememberFeedbackAnswer',
        'slotFeedbackState', 'manualWordFeedbackState', 'renderManualGapInput',
        'focusManualAnswer', 'invalidateManualSuggestions', 'commitManualWord',
        'submitManualAnswer', 'onChoose', 'renderSentence',
        'renderFeedbackAnswers', 'renderFeedback', 'handleManualAnswerShortcut',
    ];
    if (mode === 'card-easy') names.push('acceptedAnswersForSlot', 'answerMatchesSlot');
    vm.runInContext([
        ...sharedNames.map(name => functionSource(shared, name)),
        functionSource(keyboard, 'isTestAnswerCommitKey'),
        ...names.map(name => functionSource(source, name)),
    ].join('\n'), context);
    function render() {
        counters.renders += 1;
        const feedbackId = mode === 'card-easy' ? 'feedback-0' : 'feedback';
        card.innerHTML = `<div data-sentence>${context.renderSentence(q, 0)}</div>`
            + `<div id="${feedbackId}" role="status">${context.renderFeedback(q)}</div>`;
    }
    context.rerenderCard = render;
    context.render = render;
    render();
    vm.runInContext(inputBinding(source, mode), context);
    dom.window.document.addEventListener('keydown', context.handleManualAnswerShortcut, true);

    return {
        q, state, counters, card, context,
        choose(value) { mode === 'card-easy' ? context.onChoose(0, value) : context.onChoose(value); },
        input() { return card.querySelector('input[data-manual-active="true"]'); },
        feedback() { return card.querySelector('[role="status"]'); },
        edit(value) {
            const input = this.input();
            assert.ok(input, 'Manual draft must remain editable');
            input.value = value;
            input.dispatchEvent(new dom.window.Event('input', { bubbles: true }));
            return input;
        },
        press(key) {
            const input = this.input();
            assert.ok(input, 'There must be an active manual input before committing');
            const event = new dom.window.KeyboardEvent('keydown', { key, bubbles: true, cancelable: true });
            input.dispatchEvent(event);
            return event;
        },
        render,
    };
}

const ppcBuilderSeeders = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousNegativesAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousQuestionsAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousTimeExpressionsAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousBasicsB2LessonSeeder',
];

for (const seeder of ppcBuilderSeeders) {
    const name = seeder.split('\\').pop();
    test(`${name}: exact finite builder identity works in tech_info and legacy seeder, near names do not`, t => {
        const f = fixture(t, 'card-easy');
        for (const identity of [{ tech_info: { seeder: { class: seeder } } }, { seeder }]) {
            assert.equal(f.context.isPastPerfectContinuousQuestion({ tense: 'English Sentence Builder', ...identity }), true);
        }
        for (const nearName of [seeder + 'Extra', seeder.toLowerCase(), seeder.replace('PastPerfectContinuous', 'PresentPerfectContinuous'), seeder.replace('V3', 'V2')]) {
            assert.equal(f.context.isPastPerfectContinuousQuestion({ tense: 'English Sentence Builder', tech_info: { seeder: { class: nearName } } }), false);
            assert.equal(f.context.isPastPerfectContinuousQuestion({ tense: 'English Sentence Builder', seeder: nearName }), false);
        }
        assert.equal(f.context.isPastPerfectContinuousQuestion({
            tense: 'English Sentence Builder', tech_info: { seeder: { class: seeder + 'Extra' } }, seeder,
        }), false, 'Current explicit authorship must not be replaced by a conflicting legacy seeder');
    });

    for (const mode of ['card-easy', 'step-easy']) {
        test(`${mode}: ${name} builder allows 3 wrong choices then correct and safely reopens an old autofill`, async t => {
            const identity = {
                type: 'compose_tokens', tense: 'English Sentence Builder',
                tech_info: { seeder: { class: seeder } },
            };
            const f = fixture(t, mode, identity);
            for (const wrong of ['has been making', 'was making', 'had making']) {
                f.choose(wrong);
                await Promise.resolve();
                assert.equal(f.q.chosen[0], null);
                assert.equal(f.q.done, false);
                assert.equal(f.q.feedbackMeta.submittedAnswer, wrong);
            }
            f.edit('had');
            assert.equal(f.q.feedbackMeta, null, 'Builder draft edits also clear stale wrong feedback');
            assert.equal(f.feedback().textContent.trim(), '');
            assert.equal(f.q.wrongAttempt, true);
            assert.equal(f.q.attemptsBySlot[0], 3);
            f.choose('had been making');
            assert.equal(f.q.chosen[0], 'had been making');
            assert.equal(f.q.done, true);
            assert.equal(f.q.feedbackMeta.result, 'correct');
            assert.equal(f.state.correct, 0);
            if (mode === 'card-easy') assert.equal(f.state.answered, 1);

            const restored = fixture(t, mode, identity);
            Object.assign(restored.q, {
                chosen: ['had been making'], done: true, wrongAttempt: true,
                manualInputsBySlot: ['has been making'], attemptsBySlot: [0],
                feedbackMeta: { slotIndex: 0, result: 'incorrect', wordIndex: null,
                    submittedAnswer: 'has been making', displayedAnswer: 'had been making' },
            });
            assert.equal(restored.context.restoreSavedPpcRetryState(restored.q), true);
            assert.equal(restored.q.chosen[0], null);
            assert.equal(restored.q.done, false);
            assert.equal(restored.q.wrongAttempt, true);
            assert.equal(restored.q.attemptsBySlot[0], 2);
            restored.render();
            restored.choose('had been making');
            assert.equal(restored.q.done, true);
            assert.equal(restored.q.feedbackMeta.result, 'correct');
            assert.equal(restored.state.correct, 0);
            assert.equal(restored.context.restoreSavedPpcRetryState(restored.q), false);
        });
    }
}

for (const mode of ['card-easy', 'step-easy']) {
    for (const key of ['Enter', 'Tab']) {
        test(`${mode}: wrong option then manual correction clears stale feedback; ${key} accepts retry once`, async t => {
            const f = fixture(t, mode);
            f.choose('has been making');
            await Promise.resolve();
            assert.equal(f.q.done, false);
            assert.equal(f.q.chosen[0], null);
            assert.equal(f.q.wrongAttempt, true);
            assert.equal(f.q.attemptsBySlot[0], 1);
            assert.match(f.feedback().textContent, /has been making/);
            assert.equal(f.q.manualInputsBySlot[0], '', 'A wrong option must not prefill future retry words');
            assert.ok([...f.card.querySelectorAll('input[data-manual-gap]')].every(input => input.value === ''));
            f.edit('');
            assert.equal(f.q.feedbackMeta.result, 'incorrect', 'An input event without an actual draft change keeps the checked result');
            assert.match(f.feedback().textContent, /has been making/);
            const rendersBeforeEdit = f.counters.renders;
            const editingInput = f.edit('had');
            assert.equal(f.input(), editingInput, 'Typing must not replace the focused field');
            assert.equal(f.counters.renders, rendersBeforeEdit, 'Editing must not rerender the whole card');
            assert.equal(f.feedback().textContent.trim(), '', 'The old submitted answer is not feedback on the new draft');
            assert.equal(f.q.feedbackMeta, null);
            assert.equal(f.q.feedback, '');
            assert.equal(f.q.lastWrongBySlot[0], null);
            assert.notEqual(editingInput.dataset.answerState, 'incorrect');
            assert.doesNotMatch(editingInput.className, /border-red|bg-red|ring-red|text-red/);
            assert.equal(f.q.wrongAttempt, true, 'Editing does not erase the first-attempt history');
            assert.equal(f.q.attemptsBySlot[0], 1, 'Editing is not another attempt');
            assert.equal(f.q.done, false, 'Typing is a draft, not automatic grading');
            assert.equal(f.state.correct, 0);
            assert.equal(f.state.answered, 0);
            assert.equal(f.press(key).defaultPrevented, true);
            assert.equal(f.q.manualWordIndexBySlot[0], 1);
            for (const word of ['been', 'making']) {
                f.edit(word);
                assert.equal(f.press(key).defaultPrevented, true);
            }
            assert.equal(f.q.chosen[0], 'had been making');
            assert.equal(f.q.done, true);
            assert.equal(f.q.feedback, 'correct');
            assert.equal(f.q.feedbackMeta.submittedAnswer, 'had been making');
            assert.match(f.feedback().textContent, /had been making/);
            assert.doesNotMatch(f.feedback().textContent, /has been making/);
            assert.equal(f.state.correct, 0, 'A correct retry cannot receive first-attempt credit');
            if (mode === 'card-easy') assert.equal(f.state.answered, 1);
            const counters = [f.state.correct, f.state.answered];
            f.context.commitManualWord(0, 0, 0, 'sentence');
            f.choose('had been making');
            assert.deepEqual([f.state.correct, f.state.answered], counters, 'Repeated commits cannot double-count progress');
        });
    }

    test(`${mode}: manual wrong word -> edited draft -> complete answer stays a retry`, t => {
        const f = fixture(t, mode);
        f.edit('has');
        f.press('Enter');
        assert.equal(f.q.wrongAttempt, true);
        assert.equal(f.q.feedbackMeta.result, 'incorrect');
        assert.match(f.feedback().textContent, /has/);
        f.edit('had');
        assert.equal(f.feedback().textContent.trim(), '');
        assert.equal(f.q.done, false);
        f.press('Enter');
        assert.equal(f.q.manualWordIndexBySlot[0], 1);
        f.edit('been');
        f.press('Tab');
        assert.equal(f.q.manualWordIndexBySlot[0], 2);
        f.edit('making');
        f.press('Enter');
        assert.equal(f.q.done, true);
        assert.equal(f.q.chosen[0], 'had been making');
        assert.equal(f.state.correct, 0);
        if (mode === 'card-easy') assert.equal(f.state.answered, 1);
    });

    test(`${mode}: wrong option tail cannot pollute a full-form negative manual retry`, async t => {
        const f = fixture(t, mode, { answers: ["hadn't been talking"] });
        f.choose('had not talking');
        await Promise.resolve();
        assert.equal(f.q.manualInputsBySlot[0], '');
        assert.ok([...f.card.querySelectorAll('input[data-manual-gap]')].every(input => input.value === ''), 'Every retry field starts empty, including disabled future words');
        assert.equal(f.q.feedbackMeta.submittedAnswer, 'had not talking', 'The checked distractor remains in feedback, not in the answer draft');
        assert.match(f.feedback().textContent, /had not talking/);
        assert.equal(f.q.wrongAttempt, true);
        assert.equal(f.q.attemptsBySlot[0], 1);
        for (const [index, word] of ['had', 'not', 'been', 'talking'].entries()) {
            f.edit(word);
            f.press(index % 2 ? 'Tab' : 'Enter');
            if (index < 3) {
                assert.equal(f.q.done, false);
                assert.equal(f.q.manualWordIndexBySlot[0], index + 1, 'The correct word must advance without the old wrong tail');
            }
        }
        assert.equal(f.q.chosen[0], 'had not been talking');
        assert.equal(f.q.answers[0], "hadn't been talking", 'The canonical contraction is not rewritten');
        assert.equal(f.q.feedbackMeta.result, 'correct');
        assert.equal(f.q.done, true);
        assert.equal(f.state.correct, 0);
        if (mode === 'card-easy') assert.equal(f.state.answered, 1);
    });

    test(`${mode}: guest builder public identity keeps retries and saved-state recovery working without tech_info`, async t => {
        const identity = { type: 'compose_tokens', tense: 'English Sentence Builder',
            is_past_perfect_continuous: true, tech_info: null };
        const f = fixture(t, mode, identity);
        for (const wrong of ['has been making', 'was making', 'had making']) {
            f.choose(wrong);
            await Promise.resolve();
            assert.equal(f.q.done, false);
            assert.equal(f.q.chosen[0], null);
            assert.equal(f.q.manualInputsBySlot[0], '');
        }
        f.choose('had been making');
        assert.equal(f.q.done, true);
        assert.equal(f.state.correct, 0);
        const restored = fixture(t, mode, identity);
        Object.assign(restored.q, {
            chosen: ['had been making'], done: true, wrongAttempt: true,
            feedbackMeta: { slotIndex: 0, result: 'incorrect', wordIndex: null,
                submittedAnswer: 'has been making', displayedAnswer: 'had been making' },
        });
        assert.equal(restored.context.restoreSavedPpcRetryState(restored.q), true);
        assert.equal(restored.q.done, false);
        restored.render();
        restored.choose('had been making');
        assert.equal(restored.q.done, true);
        assert.equal(restored.state.correct, 0);
    });

    test(`${mode}: clean manual answer earns first-attempt credit only once`, t => {
        const f = fixture(t, mode);
        for (const word of ['had', 'been', 'making']) {
            f.edit(word);
            f.press('Enter');
        }
        assert.equal(f.q.done, true);
        assert.equal(f.q.wrongAttempt, false);
        assert.equal(f.state.correct, 1);
        const counters = [f.state.correct, f.state.answered];
        f.context.commitManualWord(0, 0, 2, 'sentence');
        f.choose('had been making');
        assert.deepEqual([f.state.correct, f.state.answered], counters);
    });

    test(`${mode}: two or more wrong PPC options never lock out a correct retry`, async t => {
        const f = fixture(t, mode);
        for (const wrong of ['has been making', 'was making', 'had making']) {
            f.choose(wrong);
            await Promise.resolve();
            assert.equal(f.q.done, false, 'PPC answer must stay retryable after wrong options');
            assert.equal(f.q.chosen[0], null, 'Do not insert the canonical answer as if the learner submitted it');
            assert.equal(f.state.answered, 0);
            assert.equal(f.state.correct, 0);
            assert.equal(f.q.feedbackMeta.submittedAnswer, wrong);
            assert.ok(f.input(), 'The wrong slot remains editable');
        }
        assert.equal(f.q.attemptsBySlot[0], 3);
        f.choose('had been making');
        assert.equal(f.q.done, true);
        assert.equal(f.q.chosen[0], 'had been making');
        assert.equal(f.q.feedbackMeta.result, 'correct');
        assert.equal(f.state.correct, 0);
        if (mode === 'card-easy') assert.equal(f.state.answered, 1);
    });

    test(`${mode}: unrelated tense keeps its existing two-wrong-option autofill`, async t => {
        const f = fixture(t, mode);
        f.q.tense = 'Tenses / Present Perfect Continuous';
        f.choose('has been making');
        await Promise.resolve();
        assert.equal(f.q.done, false);
        f.choose('was making');
        await Promise.resolve();
        assert.equal(f.q.done, true);
        assert.equal(f.q.chosen[0], 'had been making');
        assert.equal(f.q.feedbackMeta.result, 'incorrect');
        assert.equal(f.state.correct, 0);
        if (mode === 'card-easy') assert.equal(f.state.answered, 1);
    });

    test(`${mode}: old PPC auto-filled local state reopens safely, genuine answers stay complete`, t => {
        const f = fixture(t, mode);
        Object.assign(f.q, {
            chosen: ['had been making'], done: true, wrongAttempt: true,
            attemptsBySlot: [0], manualInputsBySlot: ['has been making'],
            feedbackMeta: {
                slotIndex: 0, result: 'incorrect', wordIndex: null,
                submittedAnswer: 'has been making', displayedAnswer: 'had been making',
            },
        });
        f.context.restoreSavedPpcRetryState(f.q);
        assert.equal(f.q.done, false);
        assert.equal(f.q.chosen[0], null);
        assert.equal(f.q.wrongAttempt, true);
        assert.equal(f.q.activeSlot, 0);
        assert.ok(f.q.attemptsBySlot[0] >= 2, 'Old wrong attempts must not become a clean first try');
        f.render();
        f.choose('had been making');
        assert.equal(f.q.done, true);
        assert.equal(f.q.feedbackMeta.result, 'correct');
        assert.equal(f.state.correct, 0);
        const saved = JSON.stringify(f.q);
        f.context.restoreSavedPpcRetryState(f.q);
        assert.equal(JSON.stringify(f.q), saved, 'Restoration must not reopen a genuine correct retry');
        f.q.tense = 'Tenses / Present Perfect Continuous';
        f.q.feedbackMeta.result = 'incorrect';
        f.q.feedbackMeta.submittedAnswer = 'has been making';
        const unrelated = JSON.stringify(f.q);
        f.context.restoreSavedPpcRetryState(f.q);
        assert.equal(JSON.stringify(f.q), unrelated, 'Restoration is scoped to PPC identity');
    });

    test(`${mode}: PPC matching uses exact final category, not a substring in another topic`, t => {
        const f = fixture(t, mode);
        for (const tense of ['Past Perfect Continuous', 'Tenses / Past Perfect Continuous', 'Tenses / PAST PERFECT CONTINUOUS ']) {
            assert.equal(f.context.isPastPerfectContinuousQuestion({ tense }), true);
        }
        for (const tense of ['', 'Tenses / Present Perfect Continuous', 'Past Perfect Continuous / Conditionals', 'Tenses / Past Perfect Continuous vs Past Perfect']) {
            assert.equal(f.context.isPastPerfectContinuousQuestion({ tense }), false);
        }
    });

    test(`${mode}: recovery preserves other confirmed slots and ignores malformed metadata`, t => {
        const f = fixture(t, mode);
        Object.assign(f.q, {
            markers: ['a1', 'a2'], markers_count: 2, answers: ['Mia', 'had been making'],
            chosen: ['Mia', 'had been making'], done: true, wrongAttempt: true,
            manualInputsBySlot: ['Mia', 'has been making'], manualWordIndexBySlot: [0, 0],
            attemptsBySlot: [0, 0], lastWrongBySlot: [null, null], activeSlot: 1,
            feedbackMeta: {
                slotIndex: 1, result: 'incorrect', wordIndex: null,
                submittedAnswer: 'has been making', displayedAnswer: 'had been making',
            },
        });
        f.context.restoreSavedPpcRetryState(f.q);
        assert.equal(f.q.chosen[0], 'Mia');
        assert.equal(f.q.chosen[1], null);
        assert.equal(f.q.activeSlot, 1);
        assert.equal(f.q.done, false);
        for (const slotIndex of [-1, 9, '1']) {
            f.q.feedbackMeta = { slotIndex, result: 'incorrect', wordIndex: null, submittedAnswer: 'wrong' };
            const before = JSON.stringify(f.q);
            f.context.restoreSavedPpcRetryState(f.q);
            assert.equal(JSON.stringify(f.q), before, 'Malformed slot metadata does not change stored progress');
        }
    });
}

test('Explicit guest PPC boolean is authoritative; false wins over stale category and seeder metadata', t => {
    const f = fixture(t, 'card-easy');
    assert.equal(f.context.isPastPerfectContinuousQuestion({
        is_past_perfect_continuous: true, tense: 'English Sentence Builder', tech_info: null,
    }), true);
    assert.equal(f.context.isPastPerfectContinuousQuestion({
        is_past_perfect_continuous: false, tense: 'Tenses / Past Perfect Continuous',
        tech_info: { seeder: { class: ppcBuilderSeeders[0] } }, seeder: ppcBuilderSeeders[1],
    }), false);
    for (const value of ['true', 'false', 0, 1, null]) {
        assert.equal(f.context.isPastPerfectContinuousQuestion({
            is_past_perfect_continuous: value, tense: 'English Sentence Builder', tech_info: null,
        }), false, 'Only a real boolean can set public scope; no truthy string or number');
    }
});

for (const [oldFlag, freshFlag] of [[false, true], [true, false]]) {
    test(`saved browser scope ${oldFlag} is replaced by authoritative guest scope ${freshFlag}, without losing progress`, () => {
        const helpers = read('resources/views/components/saved-test-js-helpers.blade.php');
        const fresh = [{id: 1, uuid: 'scope-fixture', is_past_perfect_continuous: freshFlag, answers: ['had been making']}];
        const context = vm.createContext({EnglishAnswerVariants: require('./load-answer-variants.cjs'), getTechnicalQuestions: () => fresh});
        vm.runInContext(functionSource(helpers, 'cloneState') + '\n'
            + functionSource(helpers, 'mergeFreshQuestionContentIntoSavedState'), context);
        const progress = {id: 1, uuid: 'scope-fixture', is_past_perfect_continuous: oldFlag,
            answers: ['had been making'], chosen: ['had been making'], done: true, wrongAttempt: true};
        const merged = context.mergeFreshQuestionContentIntoSavedState({items: [progress], correct: 0, answered: 1, __meta: {started: true}});
        assert.equal(merged.items[0].is_past_perfect_continuous, freshFlag);
        assert.equal(merged.__meta.question_data[0].is_past_perfect_continuous, freshFlag);
        assert.deepEqual(Array.from(merged.items[0].chosen), progress.chosen);
        assert.equal(merged.items[0].done, true);
        assert.equal(merged.items[0].wrongAttempt, true);
        assert.equal(merged.correct, 0);
        assert.equal(merged.answered, 1);
        assert.equal(progress.is_past_perfect_continuous, oldFlag, 'Input snapshot is not mutated');
    });
}
