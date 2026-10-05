'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../resources/views/components/saved-test-js-helpers.blade.php'), 'utf8');
const start = source.indexOf('function mergeFreshQuestionContentIntoSavedState(state) {');
const end = source.indexOf('function getSavedState()', start);
assert(start > 0 && end > start);
function merge(state, questions) {
  const context = {
    cloneState: value => value === undefined ? undefined : JSON.parse(JSON.stringify(value)),
    getTechnicalQuestions: () => questions,
    EnglishAnswerVariants: { prepareQuestion: q => q },
  };
  const method = vm.runInNewContext(source.slice(start, end) + '\nmergeFreshQuestionContentIntoSavedState;', context);
  return JSON.parse(JSON.stringify(method(state)));
}

test('actual browser merge refreshes opt-in canonical task while preserving user history', () => {
  const item = { uuid: 'ppc', question: 'Old task', answers: ['old'], compose_source_text: 'Old prompt',
    reorder_tokens: ['Old'], reorder_answer: 'Old target', chosen: ['had'], manualInputsBySlot: ['had'],
    attempts: 3, done: true, wrongAttempt: true };
  const state = { items: [item], current: 2, answered: 7, __meta: { started: true, saved_at: 'kept' } };
  const q = { uuid: 'ppc', question: 'Current task', answers: ['had', 'not', 'been'],
    compose_content_revision: 'finite-ppc-revision', compose_source_text: 'Current localized prompt', compose_hint: 'Current hint', hint: 'Current lexical lemma',
    reorder_tokens: ['Had', 'she', 'been', 'working?'], reorder_answer: 'Had she been working?', reorder_source_question: '{a1} she been working?', reorder_template_constraint: true };
  const result = merge(state, [q]);
  for (const key of Object.keys(q)) assert.deepEqual(result.items[0][key], q[key]);
  for (const key of ['chosen', 'manualInputsBySlot', 'attempts', 'done', 'wrongAttempt']) assert.deepEqual(result.items[0][key], item[key]);
  assert.equal(result.current, 2); assert.equal(result.answered, 7); assert.equal(result.__meta.saved_at, 'kept');
  assert.deepEqual(result.__meta.question_data, [q]);
});

test('legacy snapshots do not adopt opt-in-only fields', () => {
  const item = { uuid: 'legacy', compose_hint: 'Kept hint', hint: 'Kept general hint', reorder_answer: 'Kept target', reorder_source_question: 'Kept template' };
  const result = merge({ items: [item] }, [{ uuid: 'legacy', question: 'Current legacy', compose_hint: 'Ignored', hint: 'Ignored general hint', reorder_answer: 'Ignored', reorder_source_question: 'Ignored template' }]);
  assert.equal(result.items[0].question, 'Current legacy');
  assert.equal(result.items[0].compose_hint, 'Kept hint'); assert.equal(result.items[0].reorder_answer, 'Kept target');
  assert.equal(result.items[0].hint, 'Kept general hint');
  assert.equal(result.items[0].reorder_source_question, 'Kept template');
});

test('existing UUID remains authoritative over a reused numeric id', () => {
  const item = { uuid: 'old-identity', id: 4, question: 'Kept identity' };
  const result = merge({ items: [item] }, [{ uuid: 'different', id: 4, question: 'Not this question', compose_content_revision: 'new' }]);
  assert.deepEqual(result.items[0], item);
});
