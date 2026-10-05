'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../resources/views/components/text-block-practice-questions.blade.php'), 'utf8');
const start = source.indexOf('composeTextFromTokens(tokens) {');
const end = source.indexOf('normalizeAnswer(value) {', start);
assert(start > 0 && end > start);
const methods = vm.runInNewContext('({' + source.slice(start, end) + '})');

test('localized guided prompt cannot change authored question punctuation', () => {
  for (const question of ['Ти працював перед вечерею?', 'Build a question about the earlier activity.', 'Ułóż pytanie o wcześniejszą czynność.']) {
    methods.currentQuestion = { authored_compose: true, compose_punctuation: '?', question };
    assert.equal(methods.composeTextFromTokens(['Had', 'you', 'been', 'working']), 'Had you been working?');
    assert.equal(methods.composeTextFromTokens(['Had', 'you', 'been', 'working?']), 'Had you been working?');
  }
});

test('authored statements and polarity-specific short answers preserve natural punctuation', () => {
  methods.currentQuestion = { authored_compose: true, compose_punctuation: '.', question: 'Construct the negative answer.' };
  assert.equal(methods.composeTextFromTokens(['She', 'had', 'not', 'been', 'sleeping']), 'She had not been sleeping.');
  assert.equal(methods.composeTextFromTokens(['No', 'she', 'had', 'not']), 'No, she had not.');
  assert.equal(methods.composeTextFromTokens(['Yes', 'they', 'had']), 'Yes, they had.');
});

test('non-opted-in legacy question/statement punctuation stays unchanged', () => {
  methods.currentQuestion = { authored_compose: false, question: 'Old instruction.' };
  assert.equal(methods.composeTextFromTokens(['Had', 'you', 'worked']), 'Had you worked.');
  methods.currentQuestion.question = 'Старе питання?';
  assert.equal(methods.composeTextFromTokens(['Had', 'you', 'worked']), 'Had you worked?');
});
