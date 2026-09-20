const { test } = require('node:test');
const assert = require('node:assert/strict');
const engine = require('./load-answer-variants.cjs');
const rules = require('../../public/data/english-contractions.json');
const plain = value => JSON.parse(JSON.stringify(value));

for (const [short, full] of Object.entries({ ...rules.negative, ...rules.positive })) {
    test(`${short} and ${full} work in both directions and with curly apostrophes`, () => {
        assert.equal(engine.matches(`${short} work.`, `${full} work`), true);
        assert.equal(engine.matches(`${full} work.`, `${short.replaceAll("'", '’')} work`), true);
        assert.equal(engine.matches(`${short} work`, 'different work'), false);
    });
}

for (const [expected, answer, after] of [
    ["She's", 'She is', 'a singer'], ["He's", 'He is', 'from Brazil'],
    ["It's", 'It is', 'freezing'], ["he's", 'he has', 'been working'],
    ["You'd", 'You had', 'better hurry'], ["she'd", 'she would', 'rather stay'],
    ["he'd", 'he had', 'already left'], ["I'd", 'I would', 'like to go'],
    ["can't", 'can not', 'swim'], ["cannot", "can't", 'swim'],
    ['she has finished', "she's finished", ''], ['she is happy', "she's happy", ''],
    ["Don't you know?", 'Do you not know?', ''], ['Do you not know?', "Don't you know?", ''],
    ["won't you come", 'will you not come', ''],
    ["Wouldn't a narrower reading be safer?", 'Would a narrower reading not be safer?', ''],
    ["Doesn't Mark know her?", 'Does Mark not know her?', ''],
    ["Isn't the shop open?", 'Is the shop not open?', ''],
    ['Has the causal link not been overstated?', "Hasn't the causal link been overstated?", ''],
    ["Aren't I right?", 'Am I not right?', ''], ['Am I not right?', "Aren't I right?", ''],
]) test(`${expected} accepts ${answer} with context ${after}`, () => assert.equal(engine.matches(expected, answer, { after }), true));

for (const [expected, answer, after = ''] of [
    ["can't", 'can'], ["can't", 'cant'], ["don't", 'do'], ["I'm", 'I'],
    ["she's", 'she has', 'a singer'], ["he's", 'he is', 'been working'],
    ["you'd", 'you would', 'better go'], ["I'd", 'I had', 'rather stay'],
    ['she has finished', 'she is finished'], ['he is working', 'he has working'],
    ["John's book", 'John is book'], ["Don't you know", 'Do not you know'],
    ['Yes, I am.', "Yes, I'm."], ['Yes, she is.', "Yes, she's."],
    ['He has a car', "He's a car"], ['He had a car', "He'd a car"],
]) test(`${expected} rejects ${answer} with context ${after}`, () => assert.equal(engine.matches(expected, answer, { after }), false));

function question(answers, extra = {}) {
    return { type: '4', question: 'Українське речення', answers, markers: answers.map((_, i) => `a${i + 1}`), ...extra };
}

test('split I / will becomes a slot which accepts both spellings without changing source data', () => {
    const original = question(['I', 'will', 'call', 'tomorrow']);
    const snapshot = JSON.stringify(original);
    const prepared = engine.prepareQuestion(original);
    assert.equal(engine.matches(prepared.answers[0], "I'll call", engine.contextFor(prepared, 0)), true);
    assert.equal(prepared.markers.length, prepared.answers.length);
    assert.equal(JSON.stringify(original), snapshot);
    assert.deepEqual(plain(engine.prepareQuestion(prepared)), plain(prepared));
});

test('previously completed split markers remain complete and preserve the next unanswered marker', () => {
    const prepared = engine.prepareQuestion(question(['I', 'will', 'call', 'tomorrow'], {
        chosen: ['I', 'will', 'call', null], activeSlot: 3, done: false,
        manualInputsBySlot: ['I', 'will', 'call', ''], manualWordIndexBySlot: [0, 0, 0, 0],
    }));
    assert.deepEqual(plain(prepared.chosen), ['I will call', null]);
    assert.equal(prepared.activeSlot, 1);
    assert.equal(prepared.done, false);
});

test('partial old progress remains a confirmed prefix in the grouped field', () => {
    const prepared = engine.prepareQuestion(question(['I', 'will', 'call', 'tomorrow'], {
        chosen: ['I', null, null, null], activeSlot: 1,
        manualInputsBySlot: ['I', '', '', ''], manualWordIndexBySlot: [0, 0, 0, 0],
    }));
    assert.equal(prepared.chosen[0], null);
    assert.equal(prepared.manualInputsBySlot[0], 'I');
    assert.equal(prepared.manualWordIndexBySlot[0], 1);
    assert.equal(prepared.activeSlot, 0);
});

test('ordinary gaps keep their markers; negative questions absorb the subject for grammatical expansion', () => {
    const prepared = engine.prepareQuestion(question(["Don't"], { type: null, question: '{a1} you know?' }));
    assert.equal(prepared.question, '{a1} know?');
    assert.deepEqual(plain(prepared.answers), ["Don't you"]);
    assert.equal(engine.matches(prepared.answers[0], 'Do you not'), true);
    assert.equal(engine.matches(prepared.answers[0], 'Do not you'), false);
    const gap = question(['is not'], { type: null, question: 'She {a1} here.' });
    assert.deepEqual(plain(engine.prepareQuestion(gap)), gap);
    const noun = engine.prepareQuestion(question(["wouldn't"], { type: null, question: '{a1} a narrower reading be safer?' }));
    assert.equal(noun.question, '{a1} be safer?');
    assert.equal(engine.matches(noun.answers[0], 'would a narrower reading not'), true);
    assert.equal(engine.matches(noun.answers[0], 'would not a narrower reading'), false);
    const firstPerson = engine.prepareQuestion(question(["aren't"], {
        type: null, question: '{a1} I right?', accepted_answers_by_marker: { a1: ["aren't", 'are not'] },
    }));
    assert.equal(firstPerson.accepted_answers[0].includes('am I not'), true);
    assert.equal(firstPerson.accepted_answers[0].includes('are I not'), false);
});

test('array-of-word input modes keep their nested input representation during restoration', () => {
    const prepared = engine.prepareQuestion(question(['I', 'will', 'call', 'tomorrow'], { inputs: [['I'], ['will'], ['call'], ['']] }));
    assert.deepEqual(plain(prepared.inputs), [['I', 'will', 'call'], ['']]);
});
