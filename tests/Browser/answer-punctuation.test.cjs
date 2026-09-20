const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../resources/views/components/saved-test-js-helpers.blade.php'), 'utf8');
const context = vm.createContext({});
vm.runInContext(source.slice(source.indexOf('function canonicalTestAnswer('), source.indexOf('function composeManualAnswerWords(')), context);
const matches = (expected, answer, extra = {}) => context.testAnswerMatches({ answers: [expected], ...extra }, 0, answer);

for (const [expected, answer] of [
    ['minute.', 'minute'], ['minute.', 'minute.'], ['minute', 'minute.'],
    ['ready?', 'ready'], ['Stop!', 'stop'], ['wait...', 'wait'], ['wait…', 'wait'],
    ['  MINUTE.  ', ' minute '], ['last minute.', 'last   minute'],
    ["haven't finished.", 'have not finished'], ["hadn't left!", 'had not left'],
    ["hasn’t arrived?", 'has not arrived'], ["won't go.", 'will not go'],
]) {
    test(`shared matcher accepts ${JSON.stringify(answer)} for ${JSON.stringify(expected)}`, () => {
        assert.equal(matches(expected, answer), true);
    });
}

for (const [expected, answer] of [
    ['minute.', 'minutes'], ['minute.', 'minut'], ["haven't.", 'have'],
    ["can't.", 'cant'], ["John's.", 'Johns'], ['well-known.', 'wellknown'],
    ['3.14.', '314'], ['end, then.', 'end then'], ['a.b.', 'ab'],
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
