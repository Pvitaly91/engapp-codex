'use strict';
// Pure state contracts: no HTTP, Laravel boot, database or learner-progress writes.
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const ROOT = path.resolve(__dirname, '../..');
const MASTER_PATH = path.join(ROOT, 'docs/content/m45-authored-future-comparisons.v1.0.0.json');
const wrapper = fs.readFileSync(path.join(ROOT, 'public/js/m45-practice-ui.js'), 'utf8');
const scope = { EnglishAnswerVariants: require('./load-answer-variants.cjs') };
vm.runInNewContext(fs.readFileSync(path.join(ROOT, 'public/js/authored-practice-ui.js'), 'utf8'), scope);
vm.runInNewContext(wrapper, scope);
const m45PracticeUi = scope.m45PracticeUi;
const authoredPracticeUi = scope.authoredPracticeUi;
const plain = value => JSON.parse(JSON.stringify(value));

function manual(id, answer, accepted = [answer], tokens = []) {
    return { id, kind: 'manual', source_kind: 'manual', required: true, answer, accepted, tokens, options: [] };
}
function choice(id = 'meaning') {
    return {
        id, kind: 'choice', source_kind: 'choice', required: true, answer: 'result',
        options: [
            { value: 'result', label: 'Результат до майбутньої точки.' },
            { value: 'duration', label: 'Тривалість процесу.' },
            { value: 'start', label: 'Початок діяльності.' },
        ],
    };
}
function state(...controls) { return m45PracticeUi({ cases: [{ id: 'independent-fixture', controls }] }); }
const negative = () => manual('negative', 'We will not have packed 20 boxes by five.', [
    'We will not have packed 20 boxes by five.', "We won't have packed 20 boxes by five.",
]);
const tokenQuestion = () => ({
    ...manual('question', 'Will they have been waiting by the time the doors open?', undefined,
        ['the', 'Will', 'doors', 'waiting', 'open?', 'have', 'they', 'by', 'the', 'been', 'time']),
    source_kind: 'tokens',
});
const fixtureOrder = [1, 6, 5, 9, 3, 7, 0, 10, 8, 2, 4];
const wrongFeedbackVisible = (s, i = 0) => s.checked[i] && !s.isCorrect(i);

test('M45 independent: browser factory owns only its selector and alias policy', () => {
    let observed;
    const scope = {
        authoredPracticeUi(config, policy) {
            observed = { config, policy };
            return { cases: [], answers: [], history: [], edited() {} };
        },
    };
    vm.runInNewContext(wrapper, scope);
    const config = { cases: [] };
    scope.m45PracticeUi(config);
    assert.equal(observed.config, config);
    assert.deepEqual(plain(observed.policy), { answerAttribute: 'data-m45-answer', explicitManualVariants: true });
    assert.equal(typeof m45PracticeUi, 'function');
    assert.equal(scope.m44PracticeUi, undefined);
    assert.doesNotMatch(wrapper, /m44PracticeUi|data-m44-answer|fetch\(|XMLHttpRequest|localStorage|sessionStorage/);
});

test('M45 independent: wrong, wrong, typed edit, correct clears stale feedback and scores once', () => {
    const s = state(negative());
    s.check(0);
    assert.equal(wrongFeedbackVisible(s), true);
    assert.equal(s.score, 0);
    for (const answer of ['We will have packed 20 boxes by five.', 'We will not have packed 21 boxes by five.']) {
        s.setAnswer(0, 0, answer);
        assert.equal(s.checked[0], false);
        s.check(0);
        assert.equal(wrongFeedbackVisible(s), true);
        assert.equal(s.score, 0);
    }
    s.answers[0][0] = "We won't have packed 20 boxes by five."; // Alpine x-model path.
    s.edited(0);
    assert.equal(wrongFeedbackVisible(s), false);
    assert.equal(s.checked[0], false);
    s.check(0);
    assert.equal(s.isCorrect(0), true);
    assert.equal(wrongFeedbackVisible(s), false);
    assert.equal(s.score, 1);
    s.check(0);
    s.check(0);
    assert.equal(s.score, 1);
    s.reset(0);
    assert.deepEqual(s.answers[0], ['']);
    assert.equal(s.checked[0], false);
    assert.equal(s.score, 0);
    s.setAnswer(0, 0, s.cases[0].controls[0].answer);
    s.check(0);
    assert.equal(s.score, 1);
});

test('M45 independent: every required part and independent reset determine score', () => {
    const s = m45PracticeUi({ cases: [
        { id: 'compound-fixture', controls: [negative(), choice()] },
        { id: 'separate-fixture', controls: [choice('other')] },
    ] });
    s.setAnswer(0, 0, s.cases[0].controls[0].answer);
    s.check(0);
    assert.equal(s.isCorrect(0), false);
    assert.equal(s.score, 0);
    s.setAnswer(0, 1, 'result');
    s.check(0);
    s.setAnswer(1, 0, 'result');
    s.check(1);
    assert.equal(s.score, 2);
    s.setAnswer(0, 0, '');
    assert.equal(s.checked[0], false);
    assert.equal(s.score, 1);
    s.check(0);
    assert.equal(s.isCorrect(0), false);
    s.reset(0);
    s.reset(0);
    assert.equal(s.checked[1], true);
    assert.equal(s.score, 1);
});

test('M45 independent: declared aliases normalise curly apostrophes and whitespace without rewriting input', () => {
    const s = state(negative());
    const typed = " \nWe  won’t\u00a0have\tpacked 20 boxes by five. \n";
    s.setAnswer(0, 0, typed);
    assert.equal(s.partCorrect(0, 0), true);
    assert.equal(s.answers[0][0], typed);
    s.setAnswer(0, 0, 'We will not have packed 20 boxes by five');
    assert.equal(s.partCorrect(0, 0), true, 'Optional final sentence punctuation is not an internal boundary');
    const spelling = state(manual('spelling', 'will have been practising', ['will have been practising', 'will have been practicing']));
    for (const alias of spelling.cases[0].controls[0].accepted) {
        spelling.setAnswer(0, 0, alias);
        assert.equal(spelling.partCorrect(0, 0), true);
    }
});

test('M45 independent: undeclared contractions stay outside finite aliases; legacy policy is unchanged', () => {
    const cases = [{ controls: [manual('finite', 'She will have finished.')] }];
    const s = m45PracticeUi({ cases });
    s.setAnswer(0, 0, "She'll have finished.");
    assert.equal(s.partCorrect(0, 0), false);
    const legacy = authoredPracticeUi({ cases });
    legacy.setAnswer(0, 0, "She'll have finished.");
    assert.equal(legacy.partCorrect(0, 0), true);
});

test('M45 independent: negation, numbers, auxiliary order and detached contractions remain significant', () => {
    const s = state(negative());
    for (const answer of [
        'We will have packed 20 boxes by five.',
        'We will not have packed 21 boxes by five.',
        'We will not packed have 20 boxes by five.',
        'We have will not packed 20 boxes by five.',
        "'ll not have packed 20 boxes by five.",
        'We will not have packed twenty boxes by five.',
    ]) {
        s.setAnswer(0, 0, answer);
        assert.equal(s.partCorrect(0, 0), false, 'Only reviewed finite answers match: ' + answer);
    }
});

test('M45 independent: internal sentence punctuation is not erased by comparison', () => {
    const s = state(manual('punctuation', 'I will have left. They will have arrived.'));
    for (const answer of [
        'I will have left They will have arrived.',
        'I will have left, They will have arrived.',
        'I will have left; They will have arrived.',
        'They will have arrived. I will have left.',
    ]) {
        s.setAnswer(0, 0, answer);
        assert.equal(s.partCorrect(0, 0), false);
    }
    s.setAnswer(0, 0, ' I will have left .  They will have arrived ');
    assert.equal(s.partCorrect(0, 0), true);
});

test('M45 independent: duplicate the instances return after typed clearing and can be rebuilt', () => {
    const control = tokenQuestion(), s = state(control);
    assert.equal(control.tokens.filter(token => token === 'the').length, 2);
    assert.notDeepEqual(s.banks[0][0].map(token => token.index), control.tokens.map((_, i) => i));
    s.appendToken(0, 0, 0);
    s.appendToken(0, 0, 8);
    assert.equal(s.answers[0][0], 'the the');
    assert.equal(s.tokenUsed(0, 0, 0), true);
    assert.equal(s.tokenUsed(0, 0, 8), true);
    s.appendToken(0, 0, 0);
    assert.equal(s.answers[0][0], 'the the', 'One token instance cannot be used twice');
    s.check(0);
    assert.equal(wrongFeedbackVisible(s), true);
    s.answers[0][0] = ' \t ';
    s.edited(0);
    assert.deepEqual(plain(s.history[0][0]), []);
    assert.equal(s.checked[0], false);
    assert.ok(control.tokens.every((_, i) => !s.tokenUsed(0, 0, i)));
    fixtureOrder.forEach(i => s.appendToken(0, 0, i));
    assert.equal(s.answers[0][0], control.answer);
    assert.ok(control.tokens.every((_, i) => s.tokenUsed(0, 0, i)));
    s.check(0);
    assert.equal(s.score, 1);
    s.reset(0);
    assert.deepEqual(plain(s.history[0][0]), []);
    assert.ok(control.tokens.every((_, i) => !s.tokenUsed(0, 0, i)));
});

test('M45 independent: partial token answers and manual removal release unused instances', () => {
    const s = state(tokenQuestion());
    fixtureOrder.slice(0, 3).forEach(i => s.appendToken(0, 0, i));
    s.check(0);
    assert.equal(s.isCorrect(0), false);
    assert.equal(s.score, 0);
    s.answers[0][0] = 'Will they';
    s.edited(0);
    assert.equal(s.checked[0], false);
    assert.equal(s.tokenUsed(0, 0, 1), true);
    assert.equal(s.tokenUsed(0, 0, 6), true);
    assert.equal(s.tokenUsed(0, 0, 5), false, 'Removed have is available again');
    fixtureOrder.slice(2).forEach(i => s.appendToken(0, 0, i));
    s.check(0);
    assert.equal(s.isCorrect(0), true);
});

test('M45 independent: standalone question mark stays available initially, after clearing and reset', () => {
    const control = {
        ...manual('standalone-punctuation', 'Will she have left by noon?', undefined,
            ['?', 'left', 'Will', 'by', 'have', 'noon', 'she']),
        source_kind: 'tokens',
    };
    const s = state(control), order = [2, 6, 4, 1, 3, 5, 0];
    const allAvailable = () => {
        assert.equal(s.usedTokens(0, 0).size, 0);
        assert.equal(s.tokenUsed(0, 0, 0), false, 'Standalone ? is not disabled in an empty field');
        assert.ok(control.tokens.every((_, i) => !s.tokenUsed(0, 0, i)));
        assert.deepEqual(plain(s.history[0][0]), []);
    };
    allAvailable();
    order.slice(0, 2).forEach(i => s.appendToken(0, 0, i));
    assert.equal(s.answers[0][0], 'Will she');
    assert.equal(s.tokenUsed(0, 0, 0), false, 'A partial answer without ? leaves it available');
    s.check(0);
    assert.equal(s.isCorrect(0), false);
    assert.equal(wrongFeedbackVisible(s), true);
    s.answers[0][0] = ' \t\u00a0 ';
    s.edited(0);
    assert.equal(s.checked[0], false);
    allAvailable();
    order.forEach(i => s.appendToken(0, 0, i));
    assert.equal(s.normalise(s.answers[0][0]), s.normalise(control.answer));
    assert.ok(control.tokens.every((_, i) => s.tokenUsed(0, 0, i)));
    s.check(0);
    assert.equal(s.isCorrect(0), true);
    assert.equal(s.score, 1);
    s.reset(0);
    allAvailable();
    s.setAnswer(0, 0, control.answer);
    s.setAnswer(0, 0, '');
    allAvailable();
    s.reset(0);
    order.forEach(i => s.appendToken(0, 0, i));
    s.check(0);
    assert.equal(s.isCorrect(0), true);
    assert.equal(s.score, 1, 'Reset and rebuild do not double-score');
});

test('M45 independent: bare question instances track attached input and preserve duplicate history order', () => {
    const control = {
        ...manual('attached-question', 'Will the team leave?', undefined,
            ['?', 'Will', 'the', 'team', 'leave', '?', 'the', 'p.m.']),
        source_kind: 'tokens',
    };
    const s = state(control), questions = [0, 5];
    s.setAnswer(0, 0, control.answer);
    assert.equal(questions.filter(i => s.tokenUsed(0, 0, i)).length, 1, 'Attached leave? consumes one bare question instance');
    assert.equal(s.partCorrect(0, 0), true);
    assert.ok([1, 3, 4].every(i => s.tokenUsed(0, 0, i)), 'Word tracking is unchanged');
    assert.equal([2, 6].filter(i => s.tokenUsed(0, 0, i)).length, 1, 'One the consumes one instance');
    s.setAnswer(0, 0, 'Will the team leave');
    assert.ok(questions.every(i => !s.tokenUsed(0, 0, i)), 'Removing ? releases it');
    s.setAnswer(0, 0, '.');
    assert.ok(questions.every(i => !s.tokenUsed(0, 0, i)), 'A period cannot reserve a bare question token');
    s.setAnswer(0, 0, 'p.m.');
    assert.equal(s.tokenUsed(0, 0, 7), true, 'The p.m. word token keeps shared matching');
    assert.ok(questions.every(i => !s.tokenUsed(0, 0, i)));
    s.reset(0);
    s.appendToken(0, 0, 5);
    assert.equal(s.tokenUsed(0, 0, 5), true, 'The clicked duplicate is retained, not a different bank instance');
    assert.equal(s.tokenUsed(0, 0, 0), false);
    s.appendToken(0, 0, 0);
    assert.ok(questions.every(i => s.tokenUsed(0, 0, i)));
    s.answers[0][0] = 'Will the team leave?';
    s.edited(0);
    assert.equal(s.tokenUsed(0, 0, 5), true, 'With one remaining literal ?, earliest history instance wins');
    assert.equal(s.tokenUsed(0, 0, 0), false);
    s.answers[0][0] = 'Will the team leave? the?';
    s.edited(0);
    assert.ok(questions.every(i => s.tokenUsed(0, 0, i)), 'Two attached marks consume two instances');
    assert.ok([2, 6].every(i => s.tokenUsed(0, 0, i)), 'Both independent the instances remain tracked');
    s.setAnswer(0, 0, '');
    assert.equal(s.usedTokens(0, 0).size, 0);
    assert.deepEqual(plain(s.history[0][0]), []);
    s.setAnswer(0, 0, control.answer);
    assert.equal(questions.filter(i => s.tokenUsed(0, 0, i)).length, 1, 'Typed question uses bank remainder after history clears');
});

test('M45 independent: keyboard cycling uses its own selector, wraps and moves focus', () => {
    const s = state(choice());
    const focused = [], attributes = [];
    const buttons = s.cases[0].controls[0].options.map((_, i) => ({ focus: () => focused.push(i) }));
    const event = { target: {
        getAttribute(attribute) { attributes.push(attribute); return 'result'; },
        closest(selector) {
            assert.equal(selector, 'fieldset');
            return { querySelectorAll(roles) { assert.equal(roles, '[role="radio"]'); return buttons; } };
        },
    } };
    s.check(0);
    s.cycleAnswer(0, 0, -1, event);
    assert.deepEqual(attributes, ['data-m45-answer']);
    assert.equal(s.answers[0][0], 'start');
    assert.equal(s.checked[0], false);
    assert.equal(s.selected(0, 0, 'start'), true);
    s.cycleAnswer(0, 0, 1, event);
    assert.equal(s.answers[0][0], 'result');
    assert.deepEqual(focused, [2, 0]);
});

function uiCases(lesson) {
    return lesson.practice.map(task => ({
        id: task.id,
        controls: task.controls.map(control => ({
            id: control.id, kind: control.kind === 'tokens' ? 'manual' : control.kind,
            source_kind: control.kind, required: control.required, label: control.label_uk,
            ...(['manual', 'tokens'].includes(control.kind)
                ? { answer: control.canonical_answer, accepted: [...new Set([control.canonical_answer, ...(control.accepted_answers || [])])], tokens: control.tokens || [], options: [] }
                : { answer: control.correct_value, options: control.options.map(option => ({ value: option.value, label: option.label_uk })) }),
        })),
    }));
}
function canonicalTokenOrder(control) {
    let remaining = control.answer;
    const used = new Set(), order = [];
    while (remaining) {
        const index = control.tokens.findIndex((token, i) => !used.has(i) && remaining.startsWith(token)
            && (remaining.length === token.length || /\s|[.,;:!?]/u.test(remaining[token.length])));
        assert.notEqual(index, -1, 'Canonical answer must be constructible from authored token instances: ' + control.id);
        used.add(index);
        order.push(index);
        remaining = remaining.slice(control.tokens[index].length).trimStart();
    }
    assert.equal(order.length, control.tokens.length, 'Every authored token instance has a role: ' + control.id);
    return order;
}

test('M45 frozen master: all 18 tasks replay empty, wrong, retry, aliases, tokens and reset', () => {
    // This is source-to-runtime replay, not an independent content/checksum certification.
    // Missing author source is a failure, never an automatic skip.
    const master = JSON.parse(fs.readFileSync(MASTER_PATH, 'utf8'));
    assert.equal(master.lessons.length, 3);
    assert.deepEqual(master.lessons.map(lesson => lesson.practice.length), [6, 6, 6]);
    for (const lesson of master.lessons) {
        const s = m45PracticeUi({ cases: uiCases(lesson) });
        for (const [i, task] of s.cases.entries()) {
            s.check(i);
            assert.equal(s.isCorrect(i), false, task.id + ' empty');
            for (const [p, control] of task.controls.entries()) {
                assert.equal(control.required, true, 'Required controls remain explicit');
                if (control.kind === 'manual') {
                    assert.ok(control.accepted.includes(control.answer), control.id + ' canonical is explicit');
                    for (const alias of control.accepted) {
                        const typography = '  ' + alias.replace(/'/gu, '’').replace(/ /gu, '  ') + '  ';
                        s.setAnswer(i, p, typography);
                        assert.equal(s.partCorrect(i, p), true, control.id + ' declared alias');
                    }
                    s.setAnswer(i, p, control.answer + ' not');
                    s.check(i);
                    assert.equal(s.isCorrect(i), false, control.id + ' first wrong');
                    s.setAnswer(i, p, control.answer + ' two');
                    s.check(i);
                    assert.equal(s.isCorrect(i), false, control.id + ' second wrong');
                    s.setAnswer(i, p, '');
                    if (control.source_kind === 'tokens') {
                        canonicalTokenOrder(control).forEach(index => s.appendToken(i, p, index));
                        assert.equal(s.normalise(s.answers[i][p]), s.normalise(control.answer));
                    } else s.setAnswer(i, p, control.answer);
                } else {
                    assert.equal(new Set(control.options.map(option => option.value)).size, control.options.length);
                    assert.ok(control.options.some(option => option.value === control.answer));
                    for (const option of control.options) {
                        s.setAnswer(i, p, option.value);
                        assert.equal(s.partCorrect(i, p), option.value === control.answer);
                    }
                    const wrong = control.options.find(option => option.value !== control.answer).value;
                    for (let retry = 0; retry < 2; retry++) {
                        s.setAnswer(i, p, wrong);
                        s.check(i);
                        assert.equal(s.isCorrect(i), false, control.id + ' repeated wrong');
                    }
                    s.setAnswer(i, p, control.answer);
                }
            }
            assert.equal(s.checked[i], false, task.id + ' edit clears previous check');
            s.check(i);
            assert.equal(s.isCorrect(i), true, task.id + ' corrected answer accepted');
            assert.equal(s.score, i + 1);
            s.check(i);
            assert.equal(s.score, i + 1, task.id + ' no doubled score');
        }
        assert.equal(s.score, 6);
        s.reset(2);
        s.reset(2);
        assert.equal(s.score, 5, lesson.key + ' independent repeated reset');
        assert.equal(s.checked[2], false);
        assert.ok(s.answers[2].every(answer => answer === ''));
        assert.ok(s.history[2].every(history => history.length === 0));
    }
});
