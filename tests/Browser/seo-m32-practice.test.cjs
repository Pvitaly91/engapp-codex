'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../..');
const source = fs.readFileSync(path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php'), 'utf8');
const script = source.slice(source.indexOf('<script>') + 8, source.lastIndexOf('</script>'));
const content = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m32-m16-formal-english.v1.json'), 'utf8'));
const before = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m32-m16-formal-english-before.json'), 'utf8'));
let factory;
let network = 0;
vm.runInNewContext(script, {
  document: {addEventListener: (_, fn) => fn(), querySelector: () => null},
  Alpine: {data: (_, fn) => {factory = fn;}},
  window: {EnglishAnswerVariants: require('./load-answer-variants.cjs')},
  fetch: () => {network += 1; throw Error('Unexpected request');}, setTimeout, clearTimeout,
});
function state(data) {
  const value = factory({...data, i18n: {score: ':correct / :total', correct: 'Correct', incorrect: 'Incorrect', answer: 'Answer'}});
  value.$nextTick = fn => fn();
  value.init();
  return value;
}
function practice(i) {
  return JSON.parse(content.targets[i].after.page.blocks.find(block => block.type === 'practice-set').body);
}
const layouts = [[0, 3, 3], [0, 0, 6], [0, 3, 3]];
const sourceIndices = [
  {choices: [1, 2, 4], inputs: [3, 5, 6]},
  {choices: [], inputs: [1, 2, 3, 4, 5, 6]},
  {choices: [1, 3, 5], inputs: [2, 4, 6]},
];
const banks = [
  'Database\\Seeders\\V3\\Polyglot\\PolyglotFormalRegisterAndNominalisationBasicsB2LessonSeeder',
  'Database\\Seeders\\V3\\Polyglot\\PolyglotNominalisationFormalRegisterC1LessonSeeder',
  'Database\\Seeders\\V3\\Polyglot\\PolyglotRegisterToneAndParaphraseC1LessonSeeder',
];
for (const [targetIndex, target] of content.targets.entries()) {
  const data = practice(targetIndex);
  for (const [groupIndex, group] of ['selects', 'choices', 'inputs'].entries()) {
    test(`${target.slug}: finite ${group} count follows the author cases`, () => {
      assert.equal(data[group].length, layouts[targetIndex][groupIndex]);
      if (group !== 'selects') assert.deepEqual(data[group].map(item => item.source_index), sourceIndices[targetIndex][group]);
    });
    if (data[group].length === 0) continue;
    test(`${target.slug}: ${group} correct, wrong, terminal punctuation and reset`, () => {
      const s = state(data);
      data[group].forEach((item, i) => {
        s.answerSource(group)[i] = item.answer;
        assert.equal(s.isCorrect(group, i), true);
        for (const accepted of item.accepted || [item.answer]) {
          s.answerSource(group)[i] = accepted;
          assert.equal(s.isCorrect(group, i), true, `All approved variants: ${item.source_index}`);
          if (group === 'inputs') {
            s.inputAnswers[i] = accepted.replace(/[.!?]+$/, '');
            assert.equal(s.isCorrect(group, i), true, 'Terminal punctuation remains optional.');
          }
        }
      });
      const total = data[group].length;
      s.check(group); assert.equal(s.scoreText(group), `${total} / ${total}`);
      s.answerSource(group)[0] = 'wrong answer';
      assert.equal(s.isCorrect(group, 0), false); assert.equal(s.scoreText(group), `${total - 1} / ${total}`);
      s.resetGroup(group); assert.equal(s.isChecked(group), false);
      assert.equal(Object.keys(s.answerSource(group)).length, 0);
    });
  }
  test(`${target.slug}: grouped tokens, manual editing, deletion reuse, no suggestions/fetch`, async () => {
    const s = state(data);
    for (const [i, item] of data.inputs.entries()) {
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
      await s.searchWordSuggestions('inputs', i, {target: {value: 'test', selectionStart: 4}});
      assert.equal(s.isWordSuggestionOpen('inputs', i), false);
    }
    s.resetGroup('inputs');
    assert.ok(Object.values(s.inputTokenBanks).every(bank => bank.every(token => !token.used)));
    assert.equal(network, 0);
  });
  test(`${target.slug}: every canonical answer is constructible by token clicks`, () => {
    const s = state(data);
    data.inputs.forEach((item, i) => {
      let remaining = item.answer;
      const used = new Set();
      while (remaining !== '') {
        const token = s.inputTokenBank(i).filter(token => !used.has(token.id) && remaining.startsWith(token.value))
          .sort((a, b) => b.value.length - a.value.length)[0];
        assert.ok(token, `Unconstructible source case ${item.source_index}: ${remaining}`);
        s.appendInputToken('inputs', i, token); used.add(token.id);
        remaining = remaining.slice(token.value.length).trimStart();
      }
      assert.equal(s.inputAnswers[i], item.answer);
      assert.equal(s.isCorrect('inputs', i), true);
      assert.equal(s.isInputTokenBankEmpty(i), true);
    });
  });
  test(`${target.slug}: exact six source prompts and keys keep their interactive ownership`, () => {
    const original = before.targets[targetIndex].before.page.blocks[1].body;
    for (const [field, attribute] of [['prompts', 'data-self-checks'], ['answers', 'data-self-check-answers']]) {
      const list = original.match(new RegExp(`<ol ${attribute}>([\\s\\S]*?)<\\/ol>`));
      assert.ok(list);
      const values = Array.from(list[1].matchAll(/<li>([\s\S]*?)<\/li>/g), match => match[1]);
      assert.deepEqual(data.author_self_check[field], values);
      assert.equal(values.length, 6);
    }
    const indices = [];
    for (const group of ['selects', 'choices', 'inputs']) {
      for (const item of data[group]) {
        indices.push(item.source_index);
        assert.equal(item.context, data.author_self_check.prompts[item.source_index - 1]);
        assert.equal(item.author_explanation, data.author_self_check.answers[item.source_index - 1]);
      }
    }
    assert.deepEqual(indices.sort((a, b) => a - b), [1, 2, 3, 4, 5, 6]);
    assert.deepEqual(data.linked_practice.question_types, ['4']);
    assert.deepEqual(data.linked_practice.seeder_classes, [banks[targetIndex]]);
    assert.doesNotMatch(data.linked_practice.seeder_classes[0], /AllLevels/);
  });
}
const negatives = [
  ['B2 familiar opening is not suitable for this first letter', 0, 'choices', 0, 'a'],
  ['B2 noun phrase is not automatically nominalisation', 0, 'choices', 1, 'b'],
  ['B2 obtain does not preserve arrival meaning', 0, 'choices', 2, 'c'],
  ['B2 decision actor is changed', 0, 'inputs', 0, 'They made a decision on Monday to invite two speakers.'],
  ['B2 decision day is changed', 0, 'inputs', 0, 'We made a decision on Tuesday to invite two speakers.'],
  ['B2 decision object quantity is changed', 0, 'inputs', 0, 'We made a decision on Monday to invite three speakers.'],
  ['B2 decision is turned into completed invitation', 0, 'inputs', 0, 'We invited two speakers on Monday.'],
  ['B2 request deadline is changed to an appointment time', 0, 'inputs', 1, 'Could you send me the seating plan at noon tomorrow, please? I need it to prepare the name cards.'],
  ['B2 request object is lost', 0, 'inputs', 1, 'Could you send it by noon tomorrow, please? I need it to prepare the name cards.'],
  ['B2 request purpose is lost', 0, 'inputs', 1, 'Could you send me the seating plan by noon tomorrow, please?'],
  ['B2 request sentence boundary is replaced by comma splice', 0, 'inputs', 1, 'Could you send me the seating plan by noon tomorrow, please, I need it to prepare the name cards.'],
  ['B2 confirmation is turned into an accomplished act', 0, 'inputs', 2, 'Yesterday we confirmed the booking.'],
  ['B2 confirmation time is moved', 0, 'inputs', 2, 'Today we decided to confirm the booking tomorrow.'],
  ['Nominalisation ongoing examination is declared complete', 1, 'inputs', 0, 'The conservators’ examination of the mural is complete.'],
  ['Nominalisation ongoing actor is lost', 1, 'inputs', 0, 'The examination of the mural is currently in progress.'],
  ['Nominalisation result quantity is changed', 1, 'inputs', 1, 'The editors reviewed the captions. Their review revealed three incorrect dates.'],
  ['Nominalisation result actor is changed', 1, 'inputs', 1, 'The conservators reviewed the captions. Their review revealed two incorrect dates.'],
  ['Nominalisation review result becomes cause-free second fragment', 1, 'inputs', 1, 'The editors reviewed the captions. Two incorrect dates.'],
  ['Nominalisation review sentence boundary is comma splice', 1, 'inputs', 1, 'The editors reviewed the captions, their review revealed two incorrect dates.'],
  ['Nominalisation possible repair becomes certain repair', 1, 'inputs', 2, 'The volunteers will repair the gate next week.'],
  ['Nominalisation repair planned time is lost', 1, 'inputs', 2, 'Repair of the gate by the volunteers is possible.'],
  ['Nominalisation repair actor is invented', 1, 'inputs', 2, 'Repair of the gate by the manager next week is possible.'],
  ['Nominalisation analysis ignores exact conduct instruction', 1, 'inputs', 3, 'We carried out an analysis of the booking requests.'],
  ['Nominalisation analysis object is changed', 1, 'inputs', 3, 'We conducted an analysis of the seating plans.'],
  ['Nominalisation unknown announcer is invented', 1, 'inputs', 4, 'The manager announced yesterday that the workshop had been cancelled.'],
  ['Nominalisation announcement time is changed', 1, 'inputs', 4, 'It was announced today that the workshop had been cancelled.'],
  ['Nominalisation planned action is completed', 1, 'inputs', 5, 'The committee evaluated the two designs on Tuesday. This evaluation revealed a missing label. The committee added the label on Friday.'],
  ['Nominalisation planned action time is changed', 1, 'inputs', 5, 'The committee evaluated the two designs on Tuesday. This evaluation revealed a missing label. The committee plans to add the label on Tuesday.'],
  ['Nominalisation three-sentence review loses evaluation outcome', 1, 'inputs', 5, 'The committee evaluated the two designs on Tuesday. The committee plans to add the label on Friday.'],
  ['Nominalisation review internal sentence boundary is lost', 1, 'inputs', 5, 'The committee evaluated the two designs on Tuesday, this evaluation revealed a missing label. The committee plans to add the label on Friday.'],
  ['Tone familiar opening is not suitable for this neutral new-client letter', 2, 'choices', 0, 'a'],
  ['Tone quantifier/modal changes are mislabeled word order only', 2, 'choices', 1, 'b'],
  ['Tone summary deletes restriction or attribution', 2, 'choices', 2, 'b'],
  ['Tone passive designer actor is lost', 2, 'inputs', 0, 'The revised map will be sent to you by 4 p.m. on Wednesday.'],
  ['Tone passive recipient is lost', 2, 'inputs', 0, 'The revised map will be sent by our designer by 4 p.m. on Wednesday.'],
  ['Tone passive future is weakened', 2, 'inputs', 0, 'The revised map may be sent to you by our designer by 4 p.m. on Wednesday.'],
  ['Tone deadline is changed to exact moment', 2, 'inputs', 0, 'The revised map will be sent to you by our designer at 4 p.m. on Wednesday.'],
  ['Tone invoice deadline is changed', 2, 'inputs', 1, 'Could you send me the invoice by noon today, please?'],
  ['Tone invoice object is changed', 2, 'inputs', 1, 'Could you send me the seating plan by noon tomorrow, please?'],
  ['Tone Saturday-only scope becomes all sessions', 2, 'inputs', 2, 'If the hall is unavailable, the organiser may move all workshops online. Friday’s session will still take place in person.'],
  ['Tone possibility becomes certainty', 2, 'inputs', 2, 'If the hall is unavailable, the organiser will move only the Saturday workshop online. Friday’s session will still take place in person.'],
  ['Tone condition is recast as established cause', 2, 'inputs', 2, 'Because the hall is unavailable, the organiser may move only the Saturday workshop online. Friday’s session will still take place in person.'],
  ['Tone Friday certainty becomes possibility', 2, 'inputs', 2, 'If the hall is unavailable, the organiser may move only the Saturday workshop online. Friday’s session may still take place in person.'],
  ['Tone Friday-session information is lost', 2, 'inputs', 2, 'If the hall is unavailable, the organiser may move only the Saturday workshop online.'],
  ['Tone conditional comma boundary is lost', 2, 'inputs', 2, 'If the hall is unavailable the organiser may move only the Saturday workshop online. Friday’s session will still take place in person.'],
  ['Tone internal sentence boundary becomes comma splice', 2, 'inputs', 2, 'If the hall is unavailable, the organiser may move only the Saturday workshop online, Friday’s session will still take place in person.'],
];
for (const [label, target, group, index, answer] of negatives) {
  test(`Reject ${label}`, () => {
    const s = state(practice(target)); s.answerSource(group)[index] = answer;
    assert.equal(s.isCorrect(group, index), false);
  });
}
test('Contextual feedback preserves register judgment and exact instructions', () => {
  for (const i of [0, 2]) {
    const data = practice(i); const s = state(data);
    s.choiceAnswers[0] = 'a';
    assert.equal(s.isCorrect('choices', 0), false);
    assert.equal(s.feedbackText('choices', 0), data.choices[0].author_explanation);
  }
  assert.match(state(practice(0)).choices[0].feedback.a, /граматично можливе/u);
  assert.match(state(practice(2)).choices[0].feedback.a, /може бути доречним/u);
  const nominalisation = state(practice(1));
  nominalisation.inputAnswers[3] = 'We carried out an analysis of the booking requests.';
  assert.equal(nominalisation.isCorrect('inputs', 3), false);
  assert.match(nominalisation.feedbackText('inputs', 3), /conduct/u);
  const b2 = state(practice(0));
  for (const option of ['a', 'b']) {
    b2.choiceAnswers[2] = option; assert.equal(b2.isCorrect('choices', 2), true);
  }
  assert.equal(network, 0);
});
