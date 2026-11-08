// Run the actual Blade render/commit/choice/feedback functions in both modes.
// Only shell rendering, explanations and persistence are replaced by local stubs.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { JSDOM } = require('jsdom');
const root = path.join(__dirname, '../..');
const helpers = fs.readFileSync(path.join(root, 'resources/views/components/saved-test-js-helpers.blade.php'), 'utf8');
const variants = require('./load-answer-variants.cjs');

function sourceFunction(source, name) {
    const start = source.indexOf(`function ${name}(`);
    assert.notEqual(start, -1, name);
    const tail = source.slice(start), end = tail.search(/^\}/m);
    assert.notEqual(end, -1, name);
    return tail.slice(0, end + 1);
}

function fixture(t, mode, expected = 'will be made', overrides = {}) {
    const source = fs.readFileSync(path.join(root, `resources/views/test-modes/${mode}.blade.php`), 'utf8');
    const dom = new JSDOM('<!doctype html><body></body>', { url: 'http://gramlyze.loc/test/fixture' });
    t.after(() => dom.window.close());
    const question = { uuid: 'future-passive-manual-regression', type: '0',
        question: 'The little beds {a1} before bedtime.', answers: [expected],
        markers: ['a1'], markers_count: 1, chosen: [null], activeSlot: 0,
        passive_gap_phrase_input: true, passive_phrase_word_inputs: true,
        psp_content_revision: 'a'.repeat(64), done: false, wrongAttempt: false,
        manualInputsBySlot: [''], manualWordIndexBySlot: [0], lastWrongBySlot: [null],
        attemptsBySlot: [0], feedbackMeta: null, ...overrides };
    const state = {items: [question], current: 0, answered: 0, correct: 0};
    const context = vm.createContext({ EnglishAnswerVariants: variants, window: dom.window,
        document: dom.window.document, console, state, COMPOSE_TOKENS_QUESTION_TYPE: 'compose_tokens',
        IS_POLYGLOT_STEP_PREVIEW: false, testUi: key => key, persistState() {},
        setTimeout(fn) {fn(); return 0;}, renderManualSuggestions: () => '',
        buildExplanationKey: () => 'test', ensureExplanation: () => Promise.resolve(''),
        updateProgress() {}, checkAllDone() {}, isPastPerfectContinuousQuestion: () => false });
    const shared = ['canonicalTestAnswer', 'composeSentenceChoiceMatches', 'acceptedTestAnswers',
        'testAnswerMatches', 'isPassivePhraseGapQuestion', 'isReviewedComposePhraseSlot', 'normalizeManualGapInput', 'composeManualAnswerWords', 'html'];
    const functions = ['getMarkersCount', 'isPolyglotComposeQuestion', 'getMarkerLabel', 'findFirstUnfilledSlot',
        'clampActiveSlot', 'questionContainsSlotMarker', 'shouldRenderPolyglotTranslationPreview',
        'ensureManualSlotState', 'manualAnswerWords', 'manualAnswerProgress', 'manualAnswerIsComplete',
        'collectManualAnswer', 'manualInputId', 'rememberFeedbackAnswer', 'manualWordFeedbackState',
        'renderManualGapInput', 'focusManualAnswer', 'invalidateManualSuggestions', 'commitManualWord',
        'submitManualAnswer', 'renderFeedback', 'renderFeedbackAnswers', 'onChoose'];
    if (mode === 'card-easy') functions.push('acceptedAnswersForSlot', 'answerMatchesSlot');
    vm.runInContext([...shared.map(name => sourceFunction(helpers, name)),
        ...functions.map(name => sourceFunction(source, name))].join('\n'), context);
    function render() {
        dom.window.document.body.innerHTML = context.renderManualGapInput(state.items[0], 0, 0, 'sentence');
    }
    context.render = render; context.rerenderCard = render; render();
    return {context, state, question, render,
        inputs: () => [...dom.window.document.querySelectorAll('input[data-manual-gap]')],
        feedback: () => context.renderFeedback(question),
        commit(value) {
            const input = dom.window.document.querySelector('input[data-manual-active="true"]');
            assert.ok(input, 'The current word remains editable');
            input.value = value;
            context.commitManualWord(0, 0, Number(input.dataset.manualWord), 'sentence');
        }};
}

for (const mode of ['card-easy', 'step-easy']) {
    test(`${mode}: will be made starts with three fields and only all three words earn credit`, t => {
        const f = fixture(t, mode);
        assert.equal(f.inputs().length, 3);
        assert.equal(f.inputs().filter(input => !input.disabled).length, 1);
        f.commit('will');
        assert.equal(f.question.manualWordIndexBySlot[0], 1);
        assert.equal(f.inputs()[0].dataset.answerState, 'correct');
        assert.equal(f.question.feedback, '');
        assert.equal(f.feedback(), '');
        assert.equal(f.question.chosen[0], null);
        assert.equal(f.question.done, false);
        assert.equal(f.state.correct, 0);
        f.commit('be');
        assert.equal(f.question.manualWordIndexBySlot[0], 2);
        assert.equal(f.feedback(), '');
        assert.equal(f.question.done, false);
        assert.equal(f.state.correct, 0);
        f.commit('made');
        assert.equal(f.question.chosen[0], 'will be made');
        assert.equal(f.question.done, true);
        assert.equal(f.state.correct, 1);
        assert.match(f.feedback(), /status.correct/);
    });

    test(`${mode}: skipping be is wrong, retrying the same field completes without first-try credit`, t => {
        const f = fixture(t, mode);
        f.commit('will'); f.commit('made');
        assert.equal(f.question.chosen[0], null);
        assert.equal(f.question.done, false);
        assert.equal(f.state.correct, 0);
        assert.equal(f.question.manualWordIndexBySlot[0], 1);
        assert.equal(f.inputs()[1].disabled, false);
        assert.match(f.feedback(), /status.incorrect/);
        f.commit('be'); f.commit('made');
        assert.equal(f.question.chosen[0], 'will be made');
        assert.equal(f.question.done, true);
        assert.equal(f.state.correct, 0);
    });

    for (const [expected, submitted] of [['will not be made', ["won’t", 'be', 'made!']],
        ["won't be made", ['will', 'not', 'be', 'made']], ['be put', ['be', 'placed']]]) {
        test(`${mode}: dynamic phrase fields accept ${submitted.join(' ')} for ${expected}`, t => {
            const f = fixture(t, mode, expected, expected === 'be put'
                ? {accepted_answers_by_marker: {a1: ['be put', 'be placed']}} : {});
            for (const word of submitted) f.commit(word);
            assert.equal(f.question.done, true);
            assert.equal(f.state.correct, 1);
            assert.equal(f.question.chosen[0], submitted.join(' '));
            assert.equal(f.question.answers[0], expected, 'The authored answer is not rewritten');
        });
    }

    test(`${mode}: old quality phrase layouts stay one field but prefix success is neutral`, t => {
        const f = fixture(t, mode, 'will be made', {passive_phrase_word_inputs: false});
        assert.equal(f.inputs().length, 1);
        f.commit('will');
        assert.equal(f.inputs().length, 1);
        assert.equal(f.question.manualWordIndexBySlot[0], 0);
        assert.equal(f.feedback(), '');
        assert.equal(f.question.chosen[0], null);
        assert.equal(f.state.correct, 0);
        f.commit('will be made');
        assert.equal(f.question.done, true);
        assert.equal(f.state.correct, 1);
    });

    test(`${mode}: punctuation-free Tomorrow passes both real slot and whole-compose prefix checking`, t => {
        const f = fixture(t, mode, 'Tomorrow,', {type: '4', question: 'Завтра ми поїдемо.',
            answers: ['Tomorrow,', 'we', 'will leave.'], markers: ['a1', 'a2', 'a3'], markers_count: 3,
            chosen: [null, null, null], manualInputsBySlot: ['', '', ''], manualWordIndexBySlot: [0, 0, 0],
            compose_accepted_sentences: ['Tomorrow, we will leave.'], passive_phrase_word_inputs: false});
        f.commit('Tomorrow');
        assert.equal(f.question.chosen[0], 'Tomorrow');
        assert.equal(f.question.activeSlot, 1);
        assert.equal(f.question.done, false);
        assert.equal(f.state.correct, 0);
    });

    test(`${mode}: all 72 canonical gap phrases and declared synonyms require their full missing phrase`, t => {
        const source = JSON.parse(fs.readFileSync(path.join(root,
            'database/seeders/V3/PassiveVoice/PassiveVoiceFutureSimpleAllLevelsV3Seeder/definition.json'), 'utf8'));
        assert.equal(source.questions.length, 72);
        const f = fixture(t, mode);
        let completed = 0;
        for (const row of source.questions) {
            const expected = row.markers.a1.answer;
            const alternatives = [...new Set([expected, ...(row.answer_synonyms?.a1 || [])])];
            for (const alternative of alternatives) {
                Object.assign(f.question, {uuid: row.uuid, question: row.question, answers: [expected],
                    accepted_answers_by_marker: {a1: alternatives}, chosen: [null], activeSlot: 0,
                    done: false, wrongAttempt: false, feedback: '', feedbackMeta: null,
                    manualInputsBySlot: [''], manualWordIndexBySlot: [0], attemptsBySlot: [0],
                    lastWrongBySlot: [null], explanation: '', pendingExplanationKey: null});
                f.state.correct = 0; f.state.answered = 0; f.render();
                assert.equal(f.inputs().length, expected.split(/\s+/).length, row.uuid);
                const words = alternative.split(/\s+/);
                for (const [index, word] of words.entries()) {
                    f.commit(word);
                    if (index < words.length - 1) {
                        assert.equal(f.question.done, false, `${row.uuid} incomplete ${index}`);
                        assert.equal(f.question.chosen[0], null);
                        assert.equal(f.state.correct, 0);
                        assert.equal(f.feedback(), '');
                    }
                }
                assert.equal(f.question.done, true, `${row.uuid}: ${alternative}`);
                assert.equal(f.state.correct, 1);
                assert.equal(f.question.chosen[0], alternative);
                completed++;
            }
        }
        assert.ok(completed >= 72);
    });
}

function mergeFixture(fresh) {
    const context = vm.createContext({EnglishAnswerVariants: variants,
        cloneState: value => JSON.parse(JSON.stringify(value)), getTechnicalQuestions: () => fresh});
    vm.runInContext(sourceFunction(helpers, 'mergeFreshQuestionContentIntoSavedState'), context);
    return state => context.mergeFreshQuestionContentIntoSavedState(state);
}

test('all 11 exact old and partially refreshed full-gap answers/aliases retain completed credit', () => {
    const manifest = JSON.parse(fs.readFileSync(path.join(root, 'docs/content/future-simple-passive-gap-compactness.v1.json'), 'utf8'));
    assert.equal(manifest.entries.length, 11);
    for (const entry of manifest.entries) {
        const fresh = {uuid: entry.uuid, type: '0', question: entry.question, answers: [entry.answer], markers: ['a1'],
            psp_content_revision: 'a'.repeat(64), passive_gap_phrase_input: true, passive_phrase_word_inputs: true,
            gap_previous_question: entry.previous_question, gap_previous_answer: entry.previous_answer,
            gap_previous_accepted_answers: entry.previous_to_current_answers};
        const merge = mergeFixture([fresh]);
        for (const [oldAnswer, newAnswer] of Object.entries(entry.previous_to_current_answers)) {
            const saved = {uuid: entry.uuid, type: '0', question: entry.previous_question, answers: [entry.previous_answer],
                markers: ['a1'], chosen: [oldAnswer], manualInputsBySlot: [oldAnswer], manualWordIndexBySlot: [0],
                done: true, feedback: 'correct', wrongAttempt: false, attemptsBySlot: [0]};
            for (const snapshot of [saved, {...saved, question: entry.question, answers: [entry.answer]}]) {
                const result = merge({items: [snapshot], correct: 80, answered: 80, current: 12});
                assert.equal(result.items[0].chosen[0], newAnswer, entry.uuid);
                assert.equal(result.items[0].manualInputsBySlot[0], newAnswer);
                assert.equal(result.items[0].question, entry.question);
                assert.equal(result.items[0].done, true);
                assert.equal(result.items[0].wrongAttempt, false);
                assert.equal(result.correct, 80); assert.equal(result.answered, 80); assert.equal(result.current, 12);
            }
        }
    }
});

test('fresh flags restore without discarding prefix drafts or unrelated progress', () => {
    const question = {uuid: 'same-gap', type: '0', question: 'The beds {a1}.', answers: ['will be made'],
        psp_content_revision: 'a'.repeat(64), passive_gap_phrase_input: true, passive_phrase_word_inputs: true};
    const other = {uuid: 'unrelated', type: '4', answers: ['Saved'], chosen: ['Saved'], done: true};
    const result = mergeFixture([question])({items: [{...question, passive_phrase_word_inputs: false,
        chosen: [null], manualInputsBySlot: ['will'], manualWordIndexBySlot: [0], feedback: 'correct', done: false}, other],
        correct: 3, answered: 3});
    assert.equal(result.items[0].passive_phrase_word_inputs, true);
    assert.equal(result.items[0].feedback, '');
    assert.equal(result.items[0].manualInputsBySlot[0], 'will');
    assert.equal(result.items[0].chosen[0], null);
    assert.deepEqual(JSON.parse(JSON.stringify(result.items[1])), other);
    assert.equal(result.correct, 3); assert.equal(result.answered, 3);
});

test('finite compact-gap migration needs exact old text/answer and never invents an unsupported synonym', () => {
    const entry = JSON.parse(fs.readFileSync(path.join(root,
        'docs/content/future-simple-passive-gap-compactness.v1.json'), 'utf8')).entries[0];
    const fresh = {uuid: entry.uuid, type: '0', question: entry.question, answers: [entry.answer],
        psp_content_revision: 'a'.repeat(64), passive_gap_phrase_input: true, passive_phrase_word_inputs: true,
        gap_previous_question: entry.previous_question, gap_previous_answer: entry.previous_answer,
        gap_previous_accepted_answers: entry.previous_to_current_answers};
    const original = {uuid: entry.uuid, type: '0', question: entry.previous_question, answers: [entry.previous_answer],
        chosen: [entry.previous_answer], manualInputsBySlot: [entry.previous_answer], done: true};
    const merge = mergeFixture([fresh]);
    for (const saved of [{...original, question: `${entry.previous_question} changed`},
        {...original, answers: ['unsupported previous source']},
        {...original, chosen: ['will the flowers be invented']}]) {
        const result = merge({items: [saved], correct: 80, answered: 80});
        assert.equal(result.items[0].chosen[0], saved.chosen[0]);
        assert.equal(result.items[0].done, true);
        assert.equal(result.correct, 80);
    }
    const partial = {...original, chosen: [null], manualInputsBySlot: ['will the flowers be'],
        done: false, feedback: 'correct', manualWordIndexBySlot: [0]};
    const result = merge({items: [partial], correct: 80, answered: 80});
    assert.equal(result.items[0].chosen[0], null);
    assert.equal(result.items[0].manualInputsBySlot[0], 'be');
    assert.equal(result.items[0].feedback, '');
    assert.equal(result.items[0].done, false);
});

test('partially refreshed gaps recover only exact known old phrases, not compact answers or incomplete guesses', () => {
    const entries = JSON.parse(fs.readFileSync(path.join(root,
        'docs/content/future-simple-passive-gap-compactness.v1.json'), 'utf8')).entries;
    for (const entry of entries) {
        const fresh = {uuid: entry.uuid, type: '0', question: entry.question, answers: [entry.answer],
            psp_content_revision: 'a'.repeat(64), passive_gap_phrase_input: true, passive_phrase_word_inputs: true,
            gap_previous_question: entry.previous_question, gap_previous_answer: entry.previous_answer,
            gap_previous_accepted_answers: entry.previous_to_current_answers};
        const merge = mergeFixture([fresh]);
        for (const [oldAnswer, currentAnswer] of Object.entries(entry.previous_to_current_answers)) {
            const draftOnly = {...fresh, chosen: [null], manualInputsBySlot: [oldAnswer], done: false};
            const recovered = merge({items: [draftOnly], correct: 7, answered: 7});
            assert.equal(recovered.items[0].manualInputsBySlot[0], currentAnswer);
            assert.equal(recovered.items[0].chosen[0], null);
            assert.equal(recovered.items[0].done, false);
            for (const [chosen, draft] of [[currentAnswer, currentAnswer], [null, 'be'],
                [null, 'will'], ['unsupported old phrase', 'unsupported old phrase']]) {
                const saved = {...fresh, chosen: [chosen], manualInputsBySlot: [draft],
                    manualWordIndexBySlot: [0], done: chosen !== null};
                const result = merge({items: [saved], correct: 7, answered: 7});
                assert.equal(result.items[0].chosen[0], chosen, entry.uuid);
                assert.equal(result.items[0].manualInputsBySlot[0], draft, entry.uuid);
                assert.equal(result.items[0].done, chosen !== null);
                assert.equal(result.correct, 7); assert.equal(result.answered, 7);
            }
        }
    }
});
