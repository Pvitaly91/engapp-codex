const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = path.join(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const helpers = read('resources/views/components/saved-test-js-helpers.blade.php');
const fn = name => {
  const source = helpers.slice(helpers.indexOf(`function ${name}(`));
  return source.slice(0, source.search(/^\}/m) + 1);
};
const reorder = read('resources/views/components/sentence-reorder-question-js.blade.php').match(/<script>([\s\S]*?)<\/script>/)[1];
const variants = require('./load-answer-variants.cjs');
function context() {
  const ctx = vm.createContext({EnglishAnswerVariants: variants, TEST_LOCALE: 'uk', testUi: key => key,
    html: value => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('"', '&quot;'),
    isPastPerfectContinuousQuestion: () => false});
  vm.runInContext(['canonicalTestAnswer', 'composeSentenceChoiceMatches', 'acceptedTestAnswers', 'testAnswerMatches'].map(fn).join('\n') + '\n' + reorder, ctx);
  return ctx;
}
test('Future Passive keeps every supplied will/not/be slot and does not manufacture repeated auxiliaries', () => {
  const q = {type: 4, psp_content_revision: 'e'.repeat(64), question: 'Квитки не продаватимуть завтра.',
    answers: ['The', 'tickets', 'will', 'not', 'be', 'sold', 'tomorrow.'],
    markers: ['a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7'],
    compose_accepted_sentences: ['The tickets will not be sold tomorrow.'],
    options_by_marker: {a3: ['will', 'would'], a4: ['not', 'no'], a5: ['be', 'been']}};
  const prepared = variants.prepareQuestion(q);
  assert.equal(prepared, q); assert.equal(prepared.answers.length, 7);
  assert.deepEqual(prepared.options_by_marker.a3, ['will', 'would']);
  assert.ok(!JSON.stringify(prepared).includes('will will not'));
  const legacy = {...q}; delete legacy.psp_content_revision;
  assert.ok(variants.prepareQuestion(legacy).answers.length < 7);
});
test('Future gap synonyms accept the missing phrase, never a duplicated visible question word or subject', () => {
  const ctx = context();
  const q = {type: 0, question: 'What {a1} after the meeting?', answers: ['will be sent'], markers: ['a1'],
    accepted_answers_by_marker: {a1: ['will be sent', 'will be dispatched']}};
  assert.equal(ctx.testAnswerMatches(q, 0, 'will be sent'), true);
  assert.equal(ctx.testAnswerMatches(q, 0, 'will be dispatched'), true);
  assert.equal(ctx.testAnswerMatches(q, 0, 'What will be sent'), false);
  const subjectShown = {...q, question: 'The letters {a1} tomorrow.'};
  assert.equal(ctx.testAnswerMatches(subjectShown, 0, 'The letters will be sent'), false);
});
test('Future ordering shares typing/token state and allows wrong-to-correct retry exactly once', () => {
  const ctx = context();
  const q = {presentation: 'sentence_reorder', reorder_manual_input: true,
    reorder_answer: 'The roof will be repaired tomorrow.',
    reorder_accepted_answers: ['The roof will be fixed tomorrow.'],
    reorder_tokens: ['tomorrow.', 'will be repaired', 'The roof'], done: false};
  const action = (name, fields = {}) => ctx.applySentenceReorderAction(q, {dataset: {reorderAction: name, ...fields}});
  ctx.syncSentenceReorderManualText(q, 'The roof will repaired tomorrow.');
  assert.equal(action('check'), 'retry'); assert.equal(q.done, false);
  const feedback = q.feedback; ctx.renderSentenceReorderQuestion(q); assert.equal(q.feedback, feedback);
  ctx.syncSentenceReorderManualText(q, 'The roof'); assert.deepEqual(Array.from(q.reorderUsed), [2]);
  action('add', {reorderTokenIndex: '1'}); action('add', {reorderTokenIndex: '0'});
  assert.equal(action('check'), 'checked'); assert.equal(q.done, true); assert.equal(q.wrongAttempt, true);
  assert.equal(action('check'), false);
  const alternative = {...q, done: false, wrongAttempt: false};
  ctx.syncSentenceReorderManualText(alternative, 'The roof will be fixed tomorrow.');
  assert.equal(ctx.applySentenceReorderAction(alternative, {dataset: {reorderAction: 'check'}}), 'checked');
  assert.equal(alternative.feedback, 'correct');
});
test('all 72 authored Future builder questions retain canonical slots and grade complete declared alternatives', () => {
  const source = JSON.parse(read('database/seeders/V3/Polyglot/PolyglotPassiveVoiceFutureSimpleAllLevelsLessonSeeder/definition.json'));
  assert.equal(source.questions.length, 72); const ctx = context();
  for (const question of source.questions) {
    assert.equal(question.quality_revision, 'future-simple-passive-v1', question.uuid);
    const markers = Object.keys(question.markers), answers = markers.map(marker => question.markers[marker].answer);
    const accepted = markers.map((marker, index) => [...new Set([answers[index], ...(question.answer_synonyms?.[marker] || []),
      ...question.accepted_target_texts.filter(text => text.split(/\s+/).length === answers.length).map(text => text.split(/\s+/)[index])])]);
    const q = {...question, type: 4, psp_content_revision: 'f'.repeat(64), markers, answers,
      compose_accepted_sentences: question.accepted_target_texts,
      accepted_answers_by_marker: Object.fromEntries(markers.map((marker, index) => [marker, accepted[index]]))};
    const before = JSON.stringify(q); assert.equal(variants.prepareQuestion(q), q, question.uuid);
    assert.equal(JSON.stringify(q), before, question.uuid);
    for (const text of question.accepted_target_texts.filter(text => text.split(/\s+/).length === answers.length)) {
      q.chosen = Array(answers.length).fill(null);
      for (const [index, word] of text.split(/\s+/).entries()) {
        assert.equal(ctx.testAnswerMatches(q, index, word), true, `${question.uuid} slot ${index}: ${word}`);
        q.chosen[index] = word;
      }
    }
  }
});
test('five immutable M58 widget rows have exact source-bound hints and selectable whole-sentence synonyms', () => {
  const raw = read('docs/content/future-simple-passive-widget-quality.v1.json');
  const quality = JSON.parse(raw);
  const declared = read('app/Support/FutureSimplePassivePracticeQuality.php').match(/WIDGET_SHA = '([a-f0-9]{64})'/)[1];
  assert.equal(crypto.createHash('sha256').update(raw).digest('hex'), declared);
  const original = read(quality.source_path);
  assert.equal(crypto.createHash('sha256').update(original).digest('hex'), quality.source_sha256);
  const bank = JSON.parse(original).banks[1]; assert.equal(quality.questions.length, 5);
  const words = sentence => sentence.match(/[A-Za-z]+(?:['’][A-Za-z]+)?|[,.!?;:]/g) || [];
  for (const [index, entry] of quality.questions.entries()) {
    assert.equal(entry.uuid, bank.questions[index].uuid);
    assert.equal(entry.original_question, bank.questions[index].question);
    assert.deepEqual(entry.original_tokens, bank.questions[index].tokens_correct);
    assert.ok(!/[A-Za-z<>]|\{[^}]*\}/.test(entry.verb_hint));
    for (const expected of entry.accepted_target_texts) {
      const available = [...entry.original_tokens, ...entry.additional_options];
      for (const token of words(expected)) {
        const offset = available.findIndex(value => value === token);
        assert.notEqual(offset, -1, `${entry.uuid}: token ${token}`); available.splice(offset, 1);
      }
      assert.ok(entry.accepted_target_texts.some(candidate => variants.matches(candidate, expected)));
    }
    for (const token of entry.additional_options) assert.ok(Object.values(entry.token_labels).some(label => label.includes(`(${token})`)));
  }
});

const widgetBlade = read('resources/views/components/text-block-practice-questions.blade.php');
const nativeHint = widgetBlade.slice(widgetBlade.indexOf('get currentComposeHint() {'), widgetBlade.indexOf('selectedTokens: [],', widgetBlade.indexOf('get currentComposeHint() {')));
const nativeMethods = widgetBlade.slice(widgetBlade.indexOf('isComposeQuestion() {'), widgetBlade.indexOf('hasMoreQuestions() {'));
const widgetRuntime = () => vm.runInNewContext('({' + nativeHint + nativeMethods + '})', {window: {EnglishAnswerVariants: variants}});

test('native finite widget hint does not opt legacy prompts into authored punctuation or expose unbound hints', () => {
  const native = widgetRuntime();
  const question = {type: '4', authored_compose: false, source_bound_compose_hint: true, compose_punctuation: null,
    question: 'Вас повідомлять про зміни. Побудуйте питання.', compose_preanswer_hint: 'Почни з допоміжного дієслова.'};
  const widget = Object.assign(Object.create(native), {currentQuestion: question});
  assert.equal(widget.currentComposeHint, question.compose_preanswer_hint);
  assert.equal(widget.composeTextFromTokens(['Will', 'you', 'be', 'notified', '?']), 'Will you be notified?');
  assert.equal(widget.currentQuestion.authored_compose, false);
  assert.equal(widget.currentQuestion.compose_punctuation, null);
  for (const changed of [{source_bound_compose_hint: false}, {source_bound_compose_hint: 'true'}, {type: '0'}]) {
    widget.currentQuestion = {...question, ...changed};
    assert.equal(widget.currentComposeHint, '');
  }
  widget.currentQuestion = {...question, authored_compose: true, source_bound_compose_hint: false};
  assert.equal(widget.currentComposeHint, question.compose_preanswer_hint, 'Existing authored prompts retain their help.');
});

test('native M58 widget question three accepts notified clicked from its actual token bank', () => {
  const quality = JSON.parse(read('docs/content/future-simple-passive-widget-quality.v1.json'));
  const entry = quality.questions[2];
  const widget = Object.assign(Object.create(widgetRuntime()), {currentQuestion: {type: '4', authored_compose: false, source_bound_compose_hint: true,
    compose_punctuation: null, question: entry.original_question, correct_tokens: entry.original_tokens,
    options: entry.additional_options, accepted_sentences: entry.accepted_target_texts, compose_preanswer_hint: entry.verb_hint},
    selectedTokens: [], tokenBank: [], answered: false, correctCount: 0, answeredIndices: [], currentIndex: 2, persistState() {}});
  widget.tokenBank = widget.shuffledTokens(widget.composeTokenValues());
  for (const value of ['Will', 'you', 'be', 'notified', 'about', 'the', 'changes', '?']) {
    const token = widget.tokenBank.find(item => item.value === value);
    assert.ok(token, 'Available clicked token ' + value); widget.selectToken(token);
  }
  assert.equal(widget.composeSelectedText(), 'Will you be notified about the changes?');
  widget.checkAnswer(); assert.equal(widget.isCorrect, true); assert.equal(widget.correctCount, 1);
  widget.checkAnswer(); assert.equal(widget.correctCount, 1, 'Repeated checks do not change progress.');
});
