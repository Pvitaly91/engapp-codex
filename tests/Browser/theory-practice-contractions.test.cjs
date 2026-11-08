const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../resources/views/engram/theory/blocks-v3/practice-set.blade.php'), 'utf8');
const method = (start, end) => source.slice(source.indexOf(start), source.indexOf(end)).trim();
const context = vm.createContext({ window: { EnglishAnswerVariants: require('./load-answer-variants.cjs') } });
// Use the native item/answer accessors too: isCorrect consults finite semantic
// and punctuation flags on the item even when neither flag is enabled here.
const practice = vm.runInContext(`({${method('item(group, index) {', 'isEmpty(group, index) {')}${method('isCorrect(group, index) {', 'fieldClass(group, index) {')}})`, context);

for (const [expected, answer, correct] of [
    ["I haven't finished.", 'I have not finished', true],
    ['I will call.', "I'll call", true],
    ["She doesn't work here.", 'She does not work here', true],
    ["I haven't finished.", 'I have finished', false],
    ['A', 'B', false], ['A', 'A', true],
]) test(`theory practice: ${answer} for ${expected}`, () => {
    practice.inputs = [{ accepted: [expected] }];
    practice.inputAnswers = { 0: answer };
    assert.equal(practice.isCorrect('inputs', 0), correct);
});
