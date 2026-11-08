'use strict';
// Every registered finite builder, executed against the actual shared frontend helpers.
const {test} = require('node:test'), assert = require('node:assert/strict');
const fs = require('node:fs'), path = require('node:path'), vm = require('node:vm');
const {execFileSync} = require('node:child_process'), {JSDOM} = require('jsdom');
const root = path.resolve(__dirname, '../..'), read = p => fs.readFileSync(path.join(root, p), 'utf8');
const plain = value => JSON.parse(JSON.stringify(value));
const variants = require('./load-answer-variants.cjs');
const rows = JSON.parse(execFileSync(process.env.PHP_BINARY || 'C:/Program Files/xampp/php/php.exe',
    [path.join(root, 'tests/Fixtures/finite-builder-word-slots.php')], {cwd: root, encoding: 'utf8', maxBuffer: 64 * 1024 * 1024}));
const helpers = read('resources/views/components/saved-test-js-helpers.blade.php');
function fn(source, name) {
    const start = source.indexOf(`function ${name}(`); assert.notEqual(start, -1, name);
    const tail = source.slice(start), end = tail.search(/^\}/m); assert.notEqual(end, -1, name);
    return tail.slice(0, end + 1);
}
const shared = ['canonicalTestAnswer', 'presentPerfectPassiveWordPolicy', 'presentPerfectPassiveWordPaths', 'presentPerfectPassiveNextWords',
    'syncPresentPerfectPassiveWordPath', 'presentPerfectPassiveWordChoices', 'restorePresentPerfectPassiveWordProgress',
    'composeSentenceChoiceMatches', 'reviewedQuestionSurfaceAnswers', 'acceptedTestAnswers', 'testAnswerMatches',
    'isPassivePhraseGapQuestion', 'isReviewedComposePhraseSlot', 'normalizeManualGapInput', 'composeManualAnswerWords',
    'finitePracticeOptionCaption', 'formatAnswerOptionLabel', 'html'];
function context(mode = 'card-easy', extra = {}) {
    const c = vm.createContext({EnglishAnswerVariants: variants, COMPOSE_TOKENS_QUESTION_TYPE: '4',
        IS_POLYGLOT_STEP_PREVIEW: false, POLYGLOT_OPTIONS_PER_SLOT: 4, shuffle: a => a.reverse(), cloneState: plain, ...extra});
    const source = read(`resources/views/test-modes/${mode}.blade.php`);
    const methods = ['getMarkersCount', 'sanitizeOptions', 'buildShuffledOptions', 'normalizeAnswer', 'isPolyglotComposeQuestion',
        'limitOptionsForSlot', 'buildFallbackOptionsBySlot', 'normalizeOptionsBySlot', 'getActiveOptions'];
    vm.runInContext([...shared.map(name => fn(helpers, name)), ...methods.map(name => fn(source, name))].join('\n'), c);
    return c;
}
function fixture(t, mode, raw) {
    const source = read(`resources/views/test-modes/${mode}.blade.php`);
    const dom = new JSDOM('<!doctype html><body></body>', {url: 'http://gramlyze.loc/test/fixture'});
    t.after(() => dom.window.close());
    const q = {}, state = {items: [q], current: 0, answered: 0, correct: 0};
    const c = context(mode, {window: dom.window, document: dom.window.document, state, testUi: key => key,
        persistState() {}, setTimeout(f) { f(); return 0; }, renderManualSuggestions: () => '',
        buildExplanationKey: () => '', ensureExplanation: () => Promise.resolve(''), updateProgress() {},
        checkAllDone() {}, isPastPerfectContinuousQuestion: () => false});
    const methods = ['getMarkerLabel', 'findFirstUnfilledSlot', 'clampActiveSlot', 'questionContainsSlotMarker',
        'shouldRenderPolyglotTranslationPreview', 'ensureManualSlotState', 'manualAnswerWords', 'manualAnswerProgress',
        'manualAnswerIsComplete', 'collectManualAnswer', 'manualInputId', 'rememberFeedbackAnswer', 'manualWordFeedbackState',
        'renderManualGapInput', 'focusManualAnswer', 'invalidateManualSuggestions', 'commitManualWord', 'submitManualAnswer',
        'renderFeedback', 'renderFeedbackAnswers', 'onChoose'];
    if (mode === 'card-easy') methods.push('acceptedAnswersForSlot', 'answerMatchesSlot');
    vm.runInContext(methods.map(name => fn(source, name)).join('\n'), c);
    const render = () => { dom.window.document.body.innerHTML = c.renderManualGapInput(q, 0, q.activeSlot, 'preview'); };
    c.render = render; c.rerenderCard = render;
    const f = {q, c, state,
        reset(row) {
            for (const key of Object.keys(q)) delete q[key];
            Object.assign(q, plain(row), {chosen: row.answers.map(() => null), activeSlot: 0, done: false, wrongAttempt: false,
                feedback: '', feedbackMeta: null, manualInputsBySlot: row.answers.map(() => ''), manualWordIndexBySlot: row.answers.map(() => 0),
                attemptsBySlot: row.answers.map(() => 0), lastWrongBySlot: row.answers.map(() => null)});
            q.optionsBySlot = c.normalizeOptionsBySlot(q); state.answered = 0; state.correct = 0; render();
        },
        inputs: () => [...dom.window.document.querySelectorAll('input[data-manual-gap]')],
        commit(value) {
            const input = dom.window.document.querySelector('input[data-manual-active="true"]'); assert.ok(input, q.uuid);
            input.value = value; c.commitManualWord(0, q.activeSlot, Number(input.dataset.manualWord), 'preview');
        },
        choose(value) { mode === 'card-easy' ? c.onChoose(0, value) : c.onChoose(value); },
    };
    f.reset(raw); return f;
}

test('all25 catalogs and193 banks expose2652 builders with one input per real word', () => {
    const c = context(); let slots = 0;
    assert.equal(rows.length, 2652); assert.equal(new Set(rows.map(q => q.test_catalog)).size, 25);
    assert.equal(new Set(rows.map(q => q.test_catalog+'/'+q.test_topic)).size, 193);
    for (const raw of rows) {
        const q = {...raw, chosen: raw.answers.map(() => null), activeSlot: 0};
        assert.ok(c.presentPerfectPassiveWordPolicy(q), q.uuid);
        assert.strictEqual(variants.prepareQuestion(q), q, 'Finite sources must not enter legacy phrase collapsing');
        q.optionsBySlot = c.normalizeOptionsBySlot(q);
        for (const [index, word] of raw.answers.entries()) {
            const choices = Array.from(c.getActiveOptions(q));
            assert.ok(choices.length >= 4, q.uuid+'/'+index);
            assert.deepEqual(Array.from(c.getActiveOptions(q)), choices, 'Keyboard choices remain stable');
            choices.forEach(option => { assert.doesNotMatch(option, /\s|[{}<>]/u); assert.equal(option, variants.withoutSentencePunctuation(option)); });
            assert.ok(choices.some(option => variants.normalize(option) === variants.normalize(word)), q.uuid+'/'+word);
            assert.equal(c.testAnswerMatches(q, index, word+'?!'), true, q.uuid+'/'+word);
            assert.equal(c.testAnswerMatches(q, index, word+' extra'), false, q.uuid+'/'+word);
            q.chosen[index] = word; assert.equal(c.syncPresentPerfectPassiveWordPath(q), true); slots++;
        }
        assert.equal(q.answers.length, raw.answers.length); assert.ok(q.chosen.every(Boolean));
    }
    assert.equal(slots, 23351);
});

test('all reviewed full sentences and genuine contraction paths remain reachable without hybrid phrases', () => {
    const c = context(); let checked = 0, changedLength = 0;
    for (const raw of rows) {
        for (const words of c.presentPerfectPassiveWordPaths(raw)) {
            const q = {...raw, chosen: raw.answers.map(() => null), activeSlot: 0};
            for (const [index, word] of words.entries()) {
                assert.equal(c.testAnswerMatches(q, index, word), true, raw.uuid+'/'+words.join(' '));
                q.chosen[index] = word; assert.equal(c.syncPresentPerfectPassiveWordPath(q), true, raw.uuid);
                assert.equal(q.chosen.every(Boolean), index === words.length - 1, raw.uuid+'/'+words.join(' '));
            }
            assert.equal(q.answers.length, words.length); checked++;
            if (words.length !== raw.answers.length) changedLength++;
        }
    }
    assert.ok(checked > rows.length); assert.ok(changedLength > 100);
    const mug = rows.find(q => q.uuid === 'pvq-present-perfect-a1-c03');
    const q = {...mug, chosen: ['The', 'broken', 'cup', 'has', 'been', 'replaced', 'for']};
    assert.equal(c.testAnswerMatches(q, 7, 'charge'), false);
    assert.equal(c.testAnswerMatches(q, 7, 'free of charge'), false);
    assert.equal(c.testAnswerMatches(q, 7, 'free'), true);
});

test('every exact prior source can migrate grouped progress, but unknown source changes fail closed', () => {
    const c = context(); let checked = 0;
    for (const fresh of rows) for (const old of fresh.finite_word_slots.previous_versions) {
        const saved = {uuid: fresh.uuid, type: '4', question: old.question, answers: old.answers, markers: old.markers,
            psp_content_revision: old.revision, chosen: old.answers.map((word, index) => index === 0 ? word : null)};
        const restored = c.restorePresentPerfectPassiveWordProgress(saved, fresh);
        assert.ok(restored, fresh.uuid);
        const words = variants.withoutSentencePunctuation(old.answers[0]).split(' ');
        assert.equal(restored.chosen.filter(Boolean).join(' '), words.join(' '));
        assert.equal(restored.answers.length, fresh.answers.length);
        assert.equal(c.restorePresentPerfectPassiveWordProgress({...saved, psp_content_revision: 'stale'}, fresh), null);
        assert.equal(c.restorePresentPerfectPassiveWordProgress({...saved, question: 'Changed source'}, fresh), null);
        assert.ok(c.restorePresentPerfectPassiveWordProgress({...saved, psp_content_revision: undefined}, fresh));
        const completed = c.restorePresentPerfectPassiveWordProgress({...saved, chosen: [...old.answers], done: true}, fresh);
        assert.ok(completed, fresh.uuid);
        assert.ok(completed.chosen.every(Boolean));
        assert.equal(variants.normalize(completed.chosen.join(' ')), variants.normalize(fresh.answers.join(' ')));
        checked++;
    }
    assert.equal(checked, 2670);
});

test('already-current canonical progress is never reinterpreted or padded during content refresh', () => {
    const c = context(), fresh = rows[0];
    const saved = {...plain(fresh), chosen: [fresh.answers[0]], manualInputsBySlot: ['unfinished'], attemptsBySlot: [1],
        wrongAttempt: true, activeSlot: 1, done: false};
    assert.deepEqual(plain(c.restorePresentPerfectPassiveWordProgress(saved, fresh)), {});
    assert.deepEqual(saved.manualInputsBySlot, ['unfinished']);
    assert.deepEqual(saved.chosen, [fresh.answers[0]]);
});

test('word policy does not leak into gap or order questions, unknown banks or modified policy identity', () => {
    const c = context(), raw = rows[0];
    for (const patch of [{type: '0'}, {presentation: 'sentence_reorder'}, {uuid: 'unknown'}, {psp_content_revision: 'stale'},
        {question: 'Changed question'}, {compose_accepted_sentences: ['Changed target']}, {finite_word_slots: {...raw.finite_word_slots, schema: 0}}]) {
        assert.equal(c.presentPerfectPassiveWordPolicy({...raw, ...patch}), null);
    }
});

for (const mode of ['card-easy', 'step-easy']) {
    test(`${mode}: actual manual input and button grading in each catalog require every word`, t => {
        const representatives = [...new Map(rows.map(row => [row.test_catalog, row])).values()];
        const f = fixture(t, mode, representatives[0]);
        for (const raw of representatives) {
            f.reset(raw);
            for (const [index, word] of raw.answers.entries()) {
                assert.equal(f.inputs().length, 1, raw.uuid+'/'+index);
                assert.equal(f.c.testAnswerMatches(f.q, index, word+' ignored'), false);
                if (index % 2) f.choose(variants.withoutSentencePunctuation(word));
                else f.commit(variants.withoutSentencePunctuation(word));
                assert.equal(f.q.done, index === raw.answers.length - 1, raw.uuid+'/'+index);
                assert.equal(f.state.correct, index === raw.answers.length - 1 ? 1 : 0);
            }
        }
    });
}
