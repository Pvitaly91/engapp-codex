'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../..');
const source = fs.readFileSync(path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php'), 'utf8');
const script = source.slice(source.indexOf('<script>') + 8, source.lastIndexOf('</script>'));
const content = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m30-m14-participle-clauses.v1.json'), 'utf8'));
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
  ['B2 -ing identification', 0, 'selects', 0, 'is'],
  ['B2 active/passive swap', 0, 'selects', 1, 'printing'],
  ['B2 subject/object attachment swap', 0, 'choices', 0, 'b'],
  ['B2 dangling modifier accepted', 0, 'choices', 1, 'a'],
  ['B2 repair silently invents actor action', 0, 'inputs', 1, 'While Taras was reading the message, he switched off the lights.'],
  ['C1 explicit prior completion lost', 1, 'selects', 0, 'Sorting'],
  ['C1 active/passive actor swapped', 1, 'selects', 1, 'Having inspected'],
  ['C1 lexical having called perfect', 1, 'choices', 0, 'b'],
  ['C1 might removed and damaged assigned to operator', 1, 'choices', 1, 'a'],
  ['C1 not lost', 1, 'inputs', 0, 'Having seen the revised map, Roman took the old route.'],
  ['C1 perfect model broken', 1, 'inputs', 0, 'Not having see the revised map, Roman took the old route.'],
  ['C1 explicit reason replaced by time', 1, 'inputs', 1, 'After Pavlo saw the warning light, he stopped the machine.'],
  ['C2 passive equipment role lost', 2, 'selects', 0, 'packing'],
  ['C2 explicit completion lost', 2, 'selects', 1, 'arriving'],
  ['C2 own absolute subject treated as dangling', 2, 'choices', 0, 'b'],
  ['C2 dangling versus absolute classification reversed', 2, 'choices', 1, 'b'],
  ['C2 comma between subject and participle', 2, 'inputs', 0, 'The lights, having been switched off the guard locked the hall.'],
  ['C2 own absolute subject removed', 2, 'inputs', 0, 'Having been switched off, the guard locked the hall.'],
  ['C2 finite comma splice accepted as absolute', 2, 'inputs', 0, 'The lights had been switched off, the guard locked the hall.'],
  ['C2 missing absolute boundary comma', 2, 'inputs', 0, 'The lights having been switched off the guard locked the hall.'],
  ['C2 comma-spliced unsplit replacement', 2, 'inputs', 1, 'After the rehearsal had ended, the instruments were packed, the players were tired, the conductor thanked everyone.'],
  ['C2 invented instrument packer', 2, 'inputs', 1, 'After the rehearsal had ended, the conductor packed the instruments. The players were tired. The conductor thanked everyone.'],
];
for (const [label, target, group, index, answer] of negatives) {
  test(`Reject ${label}`, () => {
    const s = state(practice(target)); s.answerSource(group)[index] = answer;
    assert.equal(s.isCorrect(group, index), false);
  });
}
test('C2 internal punctuation and existing contraction handling coexist', () => {
  const c2 = state(practice(2));
  c2.inputAnswers[0] = 'The lights having been switched off, the guard locked the hall';
  assert.equal(c2.isCorrect('inputs', 0), true);
  c2.inputAnswers[1] = 'After the rehearsal had ended, the instruments were packed. The players were tired. The conductor thanked everyone';
  assert.equal(c2.isCorrect('inputs', 1), true);
  const control = state({inputs: [{answer: "I haven't finished."}]});
  control.inputAnswers[0] = 'I have not finished';
  assert.equal(control.isCorrect('inputs', 0), true);
  assert.equal(network, 0);
});
