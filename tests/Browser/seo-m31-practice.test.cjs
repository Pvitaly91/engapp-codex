'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../..');
const source = fs.readFileSync(path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php'), 'utf8');
const script = source.slice(source.indexOf('<script>') + 8, source.lastIndexOf('</script>'));
const content = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m31-m15-conditionals.v1.json'), 'utf8'));
let factory;
let network = 0;
vm.runInNewContext(script, {
  document: {addEventListener: (_, fn) => fn(), querySelector: () => null},
  Alpine: {data: (_, fn) => {factory = fn;}},
  window: {EnglishAnswerVariants: require('./load-answer-variants.cjs')},
  fetch: () => {network += 1; throw Error('Unexpected request');}, setTimeout, clearTimeout,
});
function state(data) {
  const value = factory({...data, i18n: {score: ':correct / :total'}});
  value.$nextTick = fn => fn();
  value.init();
  return value;
}
function practice(i) {
  return JSON.parse(content.targets[i].after.page.blocks.find(block => block.type === 'practice-set').body);
}
for (const [targetIndex, target] of content.targets.entries()) {
  const data = practice(targetIndex);
  for (const group of ['selects', 'choices', 'inputs']) {
    test(`${target.slug}: ${group} correct, wrong, terminal punctuation and reset`, () => {
      const s = state(data);
      assert.equal(data[group].length, 2);
      data[group].forEach((item, i) => {
        s.answerSource(group)[i] = item.answer;
        assert.equal(s.isCorrect(group, i), true);
        if (group === 'inputs') {
          s.inputAnswers[i] = item.answer.replace(/[.!?]+$/, '');
          assert.equal(s.isCorrect(group, i), true, 'Terminal punctuation remains optional.');
        }
      });
      s.check(group); assert.equal(s.scoreText(group), '2 / 2');
      s.answerSource(group)[0] = 'wrong answer';
      assert.equal(s.isCorrect(group, 0), false); assert.equal(s.scoreText(group), '1 / 2');
      s.resetGroup(group); assert.equal(s.isChecked(group), false);
      assert.equal(Object.keys(s.answerSource(group)).length, 0);
    });
  }
  test(`${target.slug}: grouped tokens, manual editing, backspace reuse, no suggestions/fetch`, () => {
    const s = state(data);
    data.inputs.forEach((item, i) => {
      const bank = s.inputTokenBank(i);
      assert.ok(bank.length > 1);
      assert.ok(bank.every(token => token.value.trim().split(/\s+/).length <= 3));
      s.appendInputToken('inputs', i, bank[0]);
      assert.equal(s.inputTokenBank(i)[0].used, true);
      s.inputAnswers[i] = ''; s.syncInputTokenBank(i);
      assert.equal(s.inputTokenBank(i)[0].used, false);
      s.inputAnswers[i] = item.answer; s.syncInputTokenBank(i);
      assert.ok(s.inputTokenBank(i).every(token => token.used));
      s.wordSuggestionOpen[`inputs-${i}`] = true;
      s.wordSuggestionResults[`inputs-${i}`] = [{word: 'test'}];
      assert.equal(s.isWordSuggestionOpen('inputs', i), false);
    });
    s.resetGroup('inputs');
    assert.ok(Object.values(s.inputTokenBanks).every(bank => bank.every(token => !token.used)));
    assert.equal(network, 0);
  });
  test(`${target.slug}: all six original cases and exact explanation/context ownership`, () => {
    const indices = [];
    for (const group of ['selects', 'choices', 'inputs']) {
      for (const item of data[group]) {
        indices.push(item.source_index);
        assert.equal(item.context, data.author_self_check.prompts[item.source_index - 1]);
        assert.equal(item.author_explanation, data.author_self_check.answers[item.source_index - 1]);
      }
    }
    assert.deepEqual(indices.sort((a, b) => a - b), [1, 2, 3, 4, 5, 6]);
    assert.equal(data.author_self_check.answers.length, 6);
    assert.equal(data.author_self_check.prompts.length, 6);
    assert.deepEqual(data.linked_practice.question_types, ['4']);
    assert.equal(data.linked_practice.seeder_classes.length, 1);
    assert.doesNotMatch(data.linked_practice.seeder_classes[0], /AllLevels/);
  });
}
const negatives = [
  ['B2 unless reverses confirmation polarity', 0, 'selects', 0, 'Unless you don’t confirm the booking, we’ll release the room.'],
  ['B2 rain exception reversed', 0, 'selects', 1, 'We’ll hold the workshop outside unless it doesn’t rain.'],
  ['B2 provided-that instruction replaced by natural as-long-as', 0, 'choices', 0, 'b'],
  ['B2 duration/permission meanings reversed', 0, 'choices', 1, 'b'],
  ['B2 will inserted into ordinary future condition', 0, 'inputs', 0, 'The organiser will reserve a desk provided that Eva will confirm by Friday.'],
  ['B2 payment polarity reversed', 0, 'inputs', 1, 'Unless you reply by noon, I’ll cancel your seat. You can keep it provided that you don’t pay today.'],
  ['B2 unless future auxiliary retained', 0, 'inputs', 1, 'Unless you will reply by noon, I’ll cancel your seat. You can keep it provided that you pay today.'],
  ['C1 battery result changed to past', 1, 'selects', 0, 'If we had charged the battery yesterday, the device would have worked yesterday.'],
  ['C1 current employment changed to completed past', 1, 'selects', 1, 'If Iryna had accepted the offer last year, she would have worked at the museum last year.'],
  ['C1 stable characteristic/past result reversed', 1, 'choices', 0, 'b'],
  ['C1 might strengthened to would', 1, 'choices', 1, 'b'],
  ['C1 could strengthened to would', 1, 'inputs', 0, 'Had we kept the access code, we would open the archive now.'],
  ['C1 current possibility changed to past', 1, 'inputs', 0, 'Had we kept the access code, we could have opened the archive yesterday.'],
  ['C1 two different result times called the same by duplication', 1, 'inputs', 1, 'We would have finished the report on Tuesday. We would have finished the report on Tuesday.'],
  ['C1 time contrast order reversed', 1, 'inputs', 1, 'We would be ready to start the report now. We would have finished the report on Tuesday.'],
  ['C2 otherwise loses send-today antecedent', 2, 'selects', 0, 'If the form is not signed, we will postpone the visit.'],
  ['C2 past but-for changed to present', 2, 'selects', 1, 'If it were not for the backup generator, the archive would have lost its files.'],
  ['C2 assumption and requirement swapped', 2, 'choices', 0, 'b'],
  ['C2 question substituted for negative condition', 2, 'choices', 1, 'b'],
  ['C2 were-to model loses to', 2, 'inputs', 0, 'Should you need a printed copy, contact the librarian. Were the ferry stop running, we would stay on the island. Had the courier arrived earlier, we could have sent the sample.'],
  ['C2 had inversion changed into question', 2, 'inputs', 0, 'Should you need a printed copy, contact the librarian. Were the ferry to stop running, we would stay on the island. Had the courier arrived earlier? We could have sent the sample.'],
  ['C2 could-have modal strengthened', 2, 'inputs', 0, 'Should you need a printed copy, contact the librarian. Were the ferry to stop running, we would stay on the island. Had the courier arrived earlier, we would have sent the sample.'],
  ['C2 one of three original transformations lost', 2, 'inputs', 0, 'Should you need a printed copy, contact the librarian. Had the courier arrived earlier, we could have sent the sample.'],
  ['C2 unconfirmed grant made requirement and certain opening', 2, 'inputs', 1, 'Provided that the grant is confirmed, we will open on Monday. We may open on condition that the director gives written approval; otherwise, we will postpone the opening.'],
  ['C2 requirement turned into assumption', 2, 'inputs', 1, 'Assuming the grant is confirmed, we might open on Monday. We may open assuming the director gives written approval; otherwise, we will postpone the opening.'],
  ['C2 otherwise antecedent dropped', 2, 'inputs', 1, 'Assuming the grant is confirmed, we might open on Monday. Otherwise, we will postpone the opening.'],
  ['C2 internal sentence boundary lost', 2, 'inputs', 1, 'Assuming the grant is confirmed, we might open on Monday, we may open on condition that the director gives written approval; otherwise, we will postpone the opening.'],
];
for (const [label, target, group, index, answer] of negatives) {
  test(`Reject ${label}`, () => {
    const s = state(practice(target)); s.answerSource(group)[index] = answer;
    assert.equal(s.isCorrect(group, index), false);
  });
}
test('Exact natural alternatives and contextual feedback do not make grammar universally wrong', () => {
  const b2 = state(practice(0));
  b2.choiceAnswers[0] = 'b';
  assert.equal(b2.isCorrect('choices', 0), false);
  assert.match(b2.feedbackText('choices', 0), /природним за змістом/u);
  assert.match(b2.feedbackText('choices', 0), /не виконує конкретної інструкції/u);
  for (const [i, data] of [practice(0), practice(1), practice(2)].entries()) {
    const s = state(data);
    data.inputs.forEach((item, index) => {
      for (const answer of item.accepted || [item.answer]) {
        s.inputAnswers[index] = answer.replace(/[.!?]+$/, '');
        assert.equal(s.isCorrect('inputs', index), true, `Accepted variant page ${i} input ${index}`);
      }
    });
  }
  const c2 = state(practice(2));
  c2.selectAnswers[0] = "If you don't send the signed form today, we will postpone the visit.";
  assert.equal(c2.isCorrect('selects', 0), true);
  c2.selectAnswers[1] = "If it hadn't been for the backup generator, the archive would have lost its files.";
  assert.equal(c2.isCorrect('selects', 1), true);
  assert.equal(network, 0);
});
