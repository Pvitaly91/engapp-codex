'use strict';
// Run actual card/step rendering, word submission and grading on the complete
// finite topic. Only persistence, network and card-shell effects are isolated.
const {test} = require('node:test'), assert = require('node:assert/strict');
const fs = require('node:fs'), path = require('node:path'), vm = require('node:vm');
const {execFileSync} = require('node:child_process'), {JSDOM} = require('jsdom');
const root = path.resolve(__dirname, '../..'), read = p => fs.readFileSync(path.join(root, p), 'utf8');
const plain = value => JSON.parse(JSON.stringify(value));
const variants = require('./load-answer-variants.cjs');
const data = JSON.parse(execFileSync(process.env.PHP_BINARY || 'C:/Program Files/xampp/php/php.exe',
    [path.join(root, 'tests/Fixtures/present-perfect-passive-word-slots.php')], {cwd: root, encoding: 'utf8', maxBuffer: 5 * 1024 * 1024}));
const helpers = read('resources/views/components/saved-test-js-helpers.blade.php');
function fn(source, name) {
    const start = source.indexOf(`function ${name}(`); assert.notEqual(start, -1, name);
    const tail = source.slice(start), end = tail.search(/^\}/m); assert.notEqual(end, -1, name);
    return tail.slice(0, end + 1);
}
const policyFunctions = ['presentPerfectPassiveWordPolicy', 'presentPerfectPassiveWordPaths', 'presentPerfectPassiveNextWords',
    'syncPresentPerfectPassiveWordPath', 'presentPerfectPassiveWordChoices', 'restorePresentPerfectPassiveWordProgress'];
const shared = ['canonicalTestAnswer', ...policyFunctions, 'composeSentenceChoiceMatches', 'reviewedQuestionSurfaceAnswers',
    'acceptedTestAnswers', 'testAnswerMatches', 'isPassivePhraseGapQuestion', 'isReviewedComposePhraseSlot',
    'normalizeManualGapInput', 'composeManualAnswerWords', 'finitePracticeOptionCaption', 'formatAnswerOptionLabel', 'html'];
function context(mode, extras = {}) {
    const c = vm.createContext({EnglishAnswerVariants: variants, COMPOSE_TOKENS_QUESTION_TYPE: '4',
        IS_POLYGLOT_STEP_PREVIEW: false, POLYGLOT_OPTIONS_PER_SLOT: 4, shuffle: a => a.reverse(), cloneState: plain,
        getTechnicalQuestions: () => plain(data.plain), ...extras});
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
    const render = () => { dom.window.document.body.innerHTML = c.renderManualGapInput(q, 0, q.activeSlot, q.type === '4' ? 'preview' : 'sentence'); };
    c.render = render; c.rerenderCard = render;
    const result = {q, state, c,
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
            input.value = value;
            c.commitManualWord(0, q.activeSlot, Number(input.dataset.manualWord), q.type === '4' ? 'preview' : 'sentence');
        },
        choose(value) { mode === 'card-easy' ? c.onChoose(0, value) : c.onChoose(value); },
    };
    result.reset(raw); return result;
}

test('all54 topic questions keep strictly alternating three-format coverage on all six levels', () => {
    assert.equal(data.plain.length, 54); assert.equal(data.mixed.length, 54);
    assert.equal(new Set(data.plain.map(q => q.uuid)).size, 54);
    for (const level of ['A1', 'A2', 'B1', 'B2', 'C1', 'C2']) {
        const counts = {gap: 0, compose: 0, order: 0}; let previous = null;
        for (const q of data.mixed.filter(q => q.level === level)) {
            const kind = q.presentation === 'sentence_reorder' ? 'order' : q.type === '4' ? 'compose' : 'gap';
            counts[kind]++; assert.notEqual(kind, previous, level); previous = kind;
        }
        assert.deepEqual(counts, {gap: 3, compose: 3, order: 3});
    }
});

for (const mode of ['card-easy', 'step-easy']) {
    test(`${mode}: all18 builders have only sentence text and one field/choice per real word`, t => {
        const rows = data.plain.filter(q => q.type === '4'), f = fixture(t, mode, rows[0]); let slots = 0;
        for (const raw of rows) {
            f.reset(raw); assert.ok(f.c.presentPerfectPassiveWordPolicy(f.q), raw.uuid);
            assert.doesNotMatch(raw.question, /Використай|Уведи|Склади|пасив/u);
            assert.strictEqual(variants.prepareQuestion(f.q), f.q, 'No legacy multiword contraction grouping');
            for (const [index, word] of raw.answers.entries()) {
                assert.doesNotMatch(word, /\s/); assert.equal(f.inputs().length, 1, raw.uuid+'/'+index);
                const choices = Array.from(f.c.getActiveOptions(f.q)); assert.equal(choices.length, 4);
                assert.deepEqual(Array.from(f.c.getActiveOptions(f.q)), choices, 'Keyboard and button order remain identical');
                assert.equal(new Set(choices).size, 4); assert.ok(choices.some(v => variants.normalize(v) === variants.normalize(word)));
                choices.forEach(choice => assert.doesNotMatch(choice, /\s|[.!?;:]/u));
                f.commit(word.replace(/[.!?;:]/g, '')+'?!'); slots++;
                assert.equal(f.q.done, index === raw.answers.length - 1, raw.uuid+'/'+index);
            }
            assert.equal(f.state.correct, 1);
        }
        assert.ok(slots > 150);
    });

    test(`${mode}: every accepted full sentence remains reachable word by word, including two-word synonyms`, t => {
        const rows = data.plain.filter(q => q.type === '4'), f = fixture(t, mode, rows[0]); let sentences = 0;
        for (const raw of rows) for (const sentence of raw.compose_accepted_sentences) {
            f.reset(raw); const words = variants.withoutSentencePunctuation(sentence).split(' ');
            words.forEach((word, index) => {
                assert.equal(f.inputs().length, 1, raw.uuid+'/'+sentence+'/'+index);
                assert.ok(Array.from(f.c.getActiveOptions(f.q)).some(v => variants.normalize(v) === variants.normalize(word)), raw.uuid+'/'+word);
                f.commit(word); assert.equal(f.q.done, index === words.length - 1, raw.uuid+'/'+sentence+'/'+word);
            });
            assert.equal(f.q.answers.length, words.length); assert.equal(f.state.correct, 1); sentences++;
        }
        assert.ok(sentences >= 25);
    });

    test(`${mode}: all36 gaps still require the complete phrase; no prefix receives whole-answer credit`, t => {
        const rows = data.plain.filter(q => q.type === '0'), f = fixture(t, mode, rows[0]);
        for (const raw of rows) {
            f.reset(raw); assert.equal(f.inputs().length, raw.answers[0].split(/\s+/).length);
            const words = raw.answers[0].split(/\s+/);
            words.forEach((word, index) => {
                f.commit(word); assert.equal(f.q.done, index === words.length - 1, raw.uuid);
                if (index < words.length - 1) { assert.equal(f.q.chosen[0], null); assert.equal(f.q.feedback, ''); assert.equal(f.state.correct, 0); }
            });
            assert.equal(f.state.correct, 1);
        }
    });

    test(`${mode}: the screenshot phrase is not accepted in one word field and false synonym hybrids fail`, t => {
        const screenshot = data.plain.find(q => q.uuid === 'pvq-present-perfect-b2-c02');
        const f = fixture(t, mode, screenshot);
        assert.equal(f.q.answers.length, 9); assert.equal(f.c.testAnswerMatches(f.q, 0, 'The methodological limitations'), false);
        f.commit('The'); f.commit('methodological'); f.commit('limitations');
        assert.equal(f.c.testAnswerMatches(f.q, 3, 'have been'), false);
        f.commit('have'); assert.equal(f.q.done, false); assert.equal(f.q.activeSlot, 4);
        const mug = data.plain.find(q => q.uuid === 'pvq-present-perfect-a1-c03'); f.reset(mug);
        for (const word of ['The', 'broken', 'cup', 'has', 'been', 'replaced', 'for']) f.commit(word);
        assert.equal(f.c.testAnswerMatches(f.q, f.q.activeSlot, 'charge'), false);
        assert.equal(f.c.testAnswerMatches(f.q, f.q.activeSlot, 'no'), false);
        f.commit('free'); assert.equal(f.q.done, true); assert.equal(f.q.chosen.length, 8);
    });

    test(`${mode}: genuine contracted words stay one field, while their full spelling uses separate fields`, t => {
        const raw = plain(data.plain.find(q => q.type === '4'));
        raw.question = 'Посилки ще не доставили.';
        raw.answers = ['The', 'parcels', "haven't", 'been', 'delivered.'];
        raw.markers = raw.answers.map((_, index) => `a${index + 1}`); raw.markers_count = 5;
        raw.answer_map = Object.fromEntries(raw.markers.map((marker, index) => [marker, raw.answers[index]]));
        raw.compose_accepted_sentences = ["The parcels haven't been delivered."];
        raw.pvq_word_slots = {...raw.pvq_word_slots, question:raw.question, answers:[...raw.answers], sentences:[...raw.compose_accepted_sentences]};
        delete raw.finite_word_slots; // Exercise the supported historic single-topic policy too.
        const f = fixture(t, mode, raw);
        for (const words of [['The','parcels',"haven’t",'been','delivered'], ['The','parcels','have','not','been','delivered']]) {
            f.reset(raw);
            words.forEach((word, index) => {
                assert.equal(f.inputs().length, 1); f.commit(word);
                assert.equal(f.q.done, index === words.length - 1);
            });
            assert.equal(f.state.correct, 1); assert.equal(f.q.answers.length, words.length);
        }
    });

    test(`${mode}: two wrong choices autofill only the next word on the selected synonym path`, t => {
        const raw = data.plain.find(q => q.uuid === 'pvq-present-perfect-a1-c03'), f = fixture(t, mode, raw);
        for (const word of ['The','broken','cup','has','been','replaced','for']) f.choose(word);
        f.choose('charge'); assert.equal(f.q.done, false);
        f.choose('cost'); assert.equal(f.q.done, true);
        assert.deepEqual(plain(f.q.chosen), ['The','broken','cup','has','been','replaced','for','free']);
        assert.equal(f.state.correct, 0); assert.equal(f.q.wrongAttempt, true);
    });
}

test('only explicit exact topic metadata opts into word paths; stale/foreign payloads keep ordinary behavior', () => {
    const c = context('card-easy'), q = data.plain.find(q => q.type === '4');
    for (const patch of [{uuid:'pvq-past-perfect-a1-c01'}, {type:'0'}, {psp_content_revision:'stale'},
        {question:q.question+' changed'}, {compose_accepted_sentences:['Unknown sentence.']}, {pvq_word_slots:undefined, finite_word_slots:undefined}]) {
        assert.equal(c.presentPerfectPassiveWordPolicy({...plain(q), ...patch}), null);
    }
    assert.equal(variants.matches("can't", 'can not'), true);
    assert.equal(variants.matches("isn't", 'is not'), true);
    assert.equal(variants.matches("haven't", 'have not'), true);
    assert.equal(variants.matches('have been', 'have'), false);
});

test('old grouped progress maps only from the exact prior source and preserves counts/confirmed words', () => {
    const c = context('card-easy'); vm.runInContext(fn(helpers, 'mergeFreshQuestionContentIntoSavedState'), c);
    const fresh = data.plain.find(q => q.uuid === 'pvq-present-perfect-b2-c02'), previous = fresh.pvq_word_slots.previous;
    const old = {...plain(fresh), ...plain(previous), psp_content_revision:previous.revision,
        chosen:['The methodological limitations','have been','acknowledged',null],
        manualInputsBySlot:['The methodological limitations','have been','acknowledged','in'],
        activeSlot:3, attemptsBySlot:[0,0,0,1], wrongAttempt:true, done:false}; delete old.pvq_word_slots;
    const state = {items:[old], answered:8, correct:7, current:0}, result = c.mergeFreshQuestionContentIntoSavedState(state);
    assert.deepEqual(plain(result.items[0].chosen), ['The','methodological','limitations','have','been','acknowledged',null,null,null]);
    assert.equal(result.items[0].activeSlot, 6); assert.equal(result.items[0].manualInputsBySlot[6], 'in');
    assert.equal(result.items[0].attemptsBySlot[6], 1); assert.equal(result.items[0].wrongAttempt, true);
    assert.equal(result.answered, 8); assert.equal(result.correct, 7);
    const unknown = {...old, psp_content_revision:'unproven'};
    assert.deepEqual(plain(c.mergeFreshQuestionContentIntoSavedState({items:[unknown]}).items[0]), unknown);
    const undecorated = plain(old); delete undecorated.psp_content_revision;
    const recovered = c.mergeFreshQuestionContentIntoSavedState({items:[undecorated]}).items[0];
    assert.deepEqual(plain(recovered.chosen), plain(result.items[0].chosen));
    assert.equal(recovered.question, fresh.question);
    const prefix = {...plain(old), chosen:[null,null,null,null], manualInputsBySlot:['The','', '', ''], manualWordIndexBySlot:[1,0,0,0]};
    const prefixResult = c.mergeFreshQuestionContentIntoSavedState({items:[prefix]}).items[0];
    assert.equal(prefixResult.chosen[0], 'The'); assert.equal(prefixResult.activeSlot, 1);
});

test('a completed shorter synonym path survives reload without adding phantom fields or dropping progress', () => {
    const c = context('card-easy'); vm.runInContext(fn(helpers, 'mergeFreshQuestionContentIntoSavedState'), c);
    const fresh = data.plain.find(q => q.uuid === 'pvq-present-perfect-a1-c03');
    const saved = {...plain(fresh), chosen:['The','broken','cup','has','been','replaced','for','free'], done:true, wrongAttempt:false};
    assert.equal(c.syncPresentPerfectPassiveWordPath(saved), true);
    saved.manualInputsBySlot = [...saved.chosen];
    const state = {items:[saved], answered:1, correct:1, current:0};
    const result = c.mergeFreshQuestionContentIntoSavedState(state);
    assert.equal(result.items[0].answers.length, 8); assert.equal(result.items[0].done, true);
    assert.deepEqual(plain(result.items[0].chosen), plain(saved.chosen)); assert.equal(result.correct, 1);
});

test('all18 exact old grouped completed answers recover without clearing personal progress', () => {
    const c = context('card-easy'); vm.runInContext(fn(helpers, 'mergeFreshQuestionContentIntoSavedState'), c);
    for (const fresh of data.plain.filter(q => q.type === '4')) {
        const previous = fresh.pvq_word_slots.previous;
        const old = {...plain(fresh), ...plain(previous), psp_content_revision:previous.revision,
            chosen:[...previous.answers], manualInputsBySlot:[...previous.answers], activeSlot:3, done:true, wrongAttempt:false};
        delete old.pvq_word_slots;
        const state = {items:[old], answered:8, correct:7, current:0}, result = c.mergeFreshQuestionContentIntoSavedState(state);
        assert.equal(result.items[0].done, true); assert.equal(result.items[0].question, fresh.question);
        assert.equal(result.items[0].answers.length, fresh.answers.length);
        assert.equal(variants.normalize(result.items[0].chosen.join(' ')), variants.normalize(fresh.answers.join(' ')));
        assert.equal(result.answered, 8); assert.equal(result.correct, 7);
    }
});
