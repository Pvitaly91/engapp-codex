const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../resources/views/components/saved-test-js-helpers.blade.php'), 'utf8');
const context = vm.createContext({ EnglishAnswerVariants: require('./load-answer-variants.cjs') });
vm.runInContext(source.slice(source.indexOf('function canonicalTestAnswer('), source.indexOf('function composeManualAnswerWords(')), context);
const matches = (expected, answer, extra = {}) => context.testAnswerMatches({ answers: [expected], ...extra }, 0, answer);

for (const [expected, answer] of [
    ['minute.', 'minute'], ['minute.', 'minute.'], ['minute', 'minute.'],
    ['ready?', 'ready'], ['Stop!', 'stop'], ['wait...', 'wait'], ['wait…', 'wait'],
    ['  MINUTE.  ', ' minute '], ['last minute.', 'last   minute'],
    ["haven't finished.", 'have not finished'], ["hadn't left!", 'had not left'],
    ["hasn’t arrived?", 'has not arrived'], ["won't go.", 'will not go'],
    ['Tomorrow,', 'Tomorrow'], ['end, then.', 'end then'],
    ['“Tomorrow,” she said.', 'Tomorrow she said'], ['(Wait!)', 'Wait'],
    ['first; then: stop!', 'first then stop'], ['red,blue', 'red blue'],
    ["'Tomorrow,' we will leave.", 'Tomorrow we will leave'],
    ["'go' and 'stop'", 'go and stop'], ["'James' bike'", "James' bike"],
    ["Students’ books", "Students' books"], ["James' bike.", "“James’ bike.”"],
]) {
    test(`shared matcher accepts ${JSON.stringify(answer)} for ${JSON.stringify(expected)}`, () => {
        assert.equal(matches(expected, answer), true);
    });
}

for (const [expected, answer] of [
    ['minute.', 'minutes'], ['minute.', 'minut'], ["haven't.", 'have'],
    ["can't.", 'cant'], ["John's.", 'Johns'], ['well-known.', 'wellknown'],
    ['3.14.', '314'], ['1,000.', '1000'], ['a.b.', 'ab'],
    ["won't", 'wont'], ["case's", 'cases'], ['well-known', 'well known'],
    ["students' books", 'students books'], ["James' bike", 'James bike'],
    ["students'", 'students'], ["James' bike", "'James' bike"],
    ["'90s music", '90s music'],
    ['minute.', ''], ['minute.', '.'], ['minute.', '?!'], ['', ''], ['.', '.'],
]) {
    test(`shared matcher rejects ${JSON.stringify(answer)} for ${JSON.stringify(expected)}`, () => {
        assert.equal(matches(expected, answer), false);
    });
}

test('explicit synonyms are accepted without terminal punctuation, without rewriting canonical data', () => {
    const question = { markers: ['a1'], answers: ['team.'], accepted_answers_by_marker: { a1: ['team.', 'crew.'] } };
    const before = JSON.stringify(question);
    assert.equal(context.testAnswerMatches(question, 0, 'crew'), true);
    assert.equal(context.testAnswerMatches(question, 0, 'group'), false);
    assert.equal(JSON.stringify(question), before);
});

test('normalization preserves internal punctuation and apostrophes', () => {
    assert.equal(context.canonicalTestAnswer("  John's well-known 3.14 example...  "), "john's well-known 3.14 example");
});

test('compose sentence prefix and final slot ignore optional punctuation without changing authored slots', () => {
    const question = { type: '4', answers: ['Tomorrow,', 'we', 'will leave.'], markers: ['a1', 'a2', 'a3'],
        chosen: [null, null, null], compose_accepted_sentences: ['Tomorrow, we will leave.'] };
    assert.equal(context.testAnswerMatches(question, 0, 'Tomorrow'), true);
    question.chosen[0] = 'Tomorrow';
    assert.equal(context.testAnswerMatches(question, 1, 'we'), true);
    question.chosen[1] = 'we';
    assert.equal(context.testAnswerMatches(question, 2, 'will leave'), true);
    assert.equal(context.testAnswerMatches(question, 2, 'will'), false);
    assert.deepEqual(question.answers, ['Tomorrow,', 'we', 'will leave.']);
});

test('three real Future Passive compose rows require their authored plural-possessive apostrophes', () => {
    const source = JSON.parse(fs.readFileSync(path.join(__dirname,
        '../../database/seeders/V3/Polyglot/PolyglotPassiveVoiceFutureSimpleAllLevelsLessonSeeder/definition.json'), 'utf8'));
    for (const uuid of ['pv-fut-simple-poly-b1-10', 'pv-fut-simple-poly-c1-03', 'pv-fut-simple-poly-c2-09']) {
        const row = source.questions.find(question => question.uuid === uuid);
        assert.ok(row, uuid);
        const markers = Object.keys(row.markers), answers = markers.map(marker => row.markers[marker].answer);
        const slot = answers.findIndex(answer => /[A-Za-z]'$/.test(answer));
        assert.ok(slot >= 0, uuid);
        const q = {type: '4', question: row.question, answers, markers,
            chosen: answers.map((answer, index) => index < slot ? answer : null),
            compose_accepted_sentences: row.accepted_target_texts};
        assert.equal(context.testAnswerMatches(q, slot, answers[slot]), true, uuid);
        assert.equal(context.testAnswerMatches(q, slot, answers[slot].slice(0, -1)), false, uuid);
    }
});

test('all 24 authored zero-article dash answers are semantic answers, not blank or arbitrary punctuation', () => {
    const engine = require('./load-answer-variants.cjs');
    assert.equal(engine.normalize(' - '), '-');
    assert.equal(engine.withoutSentencePunctuation('-'), '', 'Presentation still filters punctuation-only word-bank tokens.');
    const source = JSON.parse(fs.readFileSync(path.join(__dirname,
        '../../database/seeders/V3/CommonMistakes/CommonMistakesArticlesAllLevelsV3Seeder/definition.json'), 'utf8'));
    const rows = source.questions.filter(row => Object.values(row.markers).some(marker => marker.answer === '-'));
    assert.equal(rows.length, 24);
    for (const row of rows) {
        const markers = Object.keys(row.markers), answers = markers.map(marker => row.markers[marker].answer);
        const slot = answers.indexOf('-'), q = {type: '0', question: row.question, markers, answers};
        assert.equal(context.testAnswerMatches(q, slot, '-'), true, row.uuid);
        for (const wrong of ['', '.', '?!', '—', '–', 'the', 'a', 'an']) {
            assert.equal(context.testAnswerMatches(q, slot, wrong), false, `${row.uuid}: ${wrong}`);
        }
    }
});
