'use strict';
// Execute the real, unchanged native factory. This is an offline engine contract, not a browser test.
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const vm = require('node:vm');
const nativeAssert = require('node:assert/strict');
let assertions = 0;
const assert = new Proxy(nativeAssert, { get(target, key) {
  const value = target[key];
  return typeof value === 'function' && key !== 'AssertionError' ? (...args) => { assertions++; return value(...args); } : value;
} });
const root = path.resolve(__dirname, '../..');
const digest = value => crypto.createHash('sha256').update(value).digest('hex');
const tracked = new Map();
function read(file) { const bytes = fs.readFileSync(file); tracked.set(file, digest(bytes)); return bytes.toString('utf8'); }
const names = ['separable', 'topical', 'question-forms', 'yesno', 'wh', 'answers'];
const args = process.argv.slice(2);
let parts = false, partsSha = null, factoryRoot = root;
while (args.length) {
  const flag = args.shift();
  if (flag === '--parts') { assert.equal(parts, false); parts = true; continue; }
  const value = args.shift(); assert.equal(typeof value, 'string', 'Every valued flag needs its value.');
  if (flag === '--parts-sha256') { assert.equal(partsSha, null); assert.match(value, /^[a-f0-9]{64}$/); partsSha = value; }
  else if (flag === '--factory-root') { assert.ok(path.isAbsolute(value)); factoryRoot = path.resolve(value); }
  else assert.fail('Unknown contract flag: ' + flag);
}
assert.equal(parts, partsSha !== null, '--parts requires the ordered six-part byte SHA256.');
let master, masterSha, mode;
if (parts) {
  const texts = names.map(name => read(path.join(root, 'docs/content/m78-author-parts/' + name + '.v1.json')));
  assert.equal(digest(Buffer.concat(texts.map(text => Buffer.from(text)))), partsSha, 'The explicitly reviewed draft bytes changed.');
  master = { lessons: texts.map(text => JSON.parse(text).lesson) }; masterSha = partsSha;
  mode = 'explicit SHA-bound six-author draft; NOT frozen or browser acceptance';
} else {
  const policy = read(path.join(root, 'app/Support/M78PhrasalQuestionBasics.php'));
  const constant = name => { const hit = policy.match(new RegExp("public const " + name + " = '([^']+)';")); assert.ok(hit, name); return hit[1]; };
  for (const name of ['BEFORE', 'SOURCE', 'MASTER', 'CONTRACT', 'CHECKSUMS', 'BINDINGS']) {
    const relative = constant(name), pin = constant(name + '_SHA');
    assert.match(relative, /^(?:docs\/content\/|database\/content-patches\/)[A-Za-z0-9./-]+\.json$/); assert.ok(!relative.includes('..'));
    assert.match(pin, /^[a-f0-9]{64}$/); assert.notEqual(pin, '0'.repeat(64), 'M78 is not frozen.');
    const text = read(path.join(root, relative)); assert.equal(digest(Buffer.from(text)), pin, name + ' reviewed bytes');
    if (name === 'MASTER') { master = JSON.parse(text); masterSha = pin; }
  }
  assert.equal(master.status, 'frozen'); assert.equal(master.version, '1.0.0');
  assert.equal(master.base_sha, 'fe61b3634af5d307cc32167a16e224c1d5fb1591');
  mode = 'frozen final M78 package; NOT live browser acceptance';
}
assert.equal(master.lessons.length, 6);
const nativePath = path.join(factoryRoot, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php');
const native = read(nativePath), rawSha = digest(Buffer.from(native)), lfSha = digest(Buffer.from(native.replace(/\r\n/g, '\n')));
const profile = [
  { label: 'accepted-worktree', raw: ['05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e', 'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d'], lf: 'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d' },
  { label: 'independently-captured-served-before', raw: ['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186'], lf: 'a419b43d853126d88bc43683bddeb4d8efd190b08d1bfcf60944a005ad122ba7' },
].find(item => item.raw.includes(rawSha) && item.lf === lfSha);
assert.ok(profile, 'Only an exact, unchanged native factory profile may be exercised.');
const scripts = [...native.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(hit => hit[1]).filter(text => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(text));
assert.equal(scripts.length, 1); assert.doesNotMatch(scripts[0], /@(?:php|json|js|endphp)\b|\{\{!!?/);
for (const relative of ['tests/Browser/load-answer-variants.cjs', 'public/js/english-answer-variants.js', 'public/data/english-contractions.json']) read(path.join(factoryRoot, relative));
const variants = require(path.join(factoryRoot, 'tests/Browser/load-answer-variants.cjs'));
let factory, listener, networkCalls = 0, focusCalls = 0;
const windowStub = { EnglishAnswerVariants: variants, location: { origin: 'http://gramlyze.loc' }, innerWidth: 1280 };
const context = vm.createContext({ window: windowStub,
  document: { addEventListener(name, callback) { assert.equal(name, 'alpine:init'); listener = callback; },
    querySelector() { return { focus() { focusCalls++; }, setSelectionRange() {} }; } },
  Alpine: { data(name, callback) { assert.equal(name, 'theoryPracticeSet'); factory = callback; } },
  fetch() { networkCalls++; throw Error('Network is forbidden in the offline native contract.'); }, URL });
vm.runInContext(scripts[0], context, { filename: nativePath, timeout: 1000 }); listener(); assert.equal(typeof factory, 'function');
const make = (data, width = 1280) => { windowStub.innerWidth = width; const model = factory({ ...data, i18n: {
  empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.', answer: 'Правильна відповідь', score: 'Результат: :correct / :total' } });
  model.$nextTick = callback => callback(); model.init(); return model; };
const groups = ['selects', 'choices', 'inputs', 'rephrase'];
const props = { selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers', rephrase: 'rephraseAnswers' };
// Independent teaching expectations: not computed from the AFTER payload or its answer lists.
const expected = [
  { suffix: 'PrepositionsAndPhrasalVerbs\\PhrasalVerbsSeparableInseparableTheorySeeder', slot: 6,
    selects: ['turn it off', 'look after them'],
    choices: ['Обидва порядки можливі; А часто зручніше з довгим додатком.', 'Ні: значення злетіти тут не потребує прямого додатка.'],
    inputs: ['She picked the box up.', 'Did you write it down?'], rephrase: [], full: [0, 1] },
  { suffix: 'PrepositionsAndPhrasalVerbs\\CommonPhrasalVerbsByTopicTheorySeeder', slot: 6,
    selects: ['back them up', 'up for'],
    choices: ['Тарас прокинувся, але ще залишався в ліжку.', 'Є потреба владнати проблему; про завершений результат не повідомлено.'],
    inputs: ['We set off for Lviv at dawn.', 'She didn’t log in to the system.'], rephrase: [], full: [0, 1] },
  { suffix: 'QuestionsNegations\\QuestionsNegationsQuestionFormsTheorySeeder', slot: 11,
    selects: ['b', 'c', 'a', 'b'], choices: ['A', 'A'],
    inputs: ['Does she speak French?', 'Are they students?', 'Can he drive?', 'Where did you go?'],
    rephrase: [null, 'Are you happy?', 'Where does she live?', 'Can you swim?', 'Who called you?'], full: [0, 1, 2, 3] },
  { suffix: 'QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsYesNoQuestionsGeneralQuestionsTheorySeeder', slot: 10,
    selects: ['a', 'a', 'a', 'a'], choices: ['A', 'A'],
    inputs: ['Does she like tea?', 'Are they happy?', 'Can he dance?', 'Do you have a car?'],
    rephrase: [null, 'Is she happy?', 'Did they go home?', 'Can you help me?'], full: [0, 1, 2, 3] },
  { suffix: 'QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsWhQuestionsSpecialQuestionsTheorySeeder', slot: 12,
    selects: ['What', 'Where', 'Why', 'How'],
    choices: ['У першому шукають того, хто телефонував; у другому — того, кому телефонувала Анна.', 'How тут запитує спосіб навчання; why — причину або мету.'],
    inputs: ['Where do you live?', 'How old is she?', 'Where did he go?', 'When did they arrive?'],
    rephrase: [null, 'Where does she live?', 'Who lives here?', 'How much money do you have?'], full: [0, 1, 2, 3] },
  { suffix: 'QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsAnswersToQuestionsTheorySeeder', slot: 12,
    selects: ['Yes, I do', 'Yes, she is', 'Yes, I can', "No, they didn't"],
    choices: ['Чекати не обов’язково; ця відповідь сама по собі не забороняє чекати.', 'Оля ввічливо просить виконати дію; згода й виконання ще не підтверджені.'],
    inputs: ['I do', "she isn't", 'they can', "I didn't", 'Yes, I am ready.', 'No, he does not work here.'],
    rephrase: [null, 'Yes, I can.', 'Yes, she is.', 'Yes, I do.'], full: [4, 5] },
];
const extraAccepted = {
  '0/inputs/0': ['She picked up the box.'], '1/inputs/1': ['She did not log in to the system.'],
  '3/inputs/3': ['Have you got a car?'], '5/selects/3': ['No, they did not'],
  '5/inputs/1': ['she is not', "she's not"], '5/inputs/3': ['I did not'],
  '5/inputs/4': ["Yes, I'm ready."], '5/inputs/5': ["No, he doesn't work here."],
};
const newIds = {
  '0/selects/0': 'm78-separable-form-light-pronoun', '0/selects/1': 'm78-separable-form-care-pronoun',
  '0/choices/0': 'm78-separable-meaning-long-object', '0/choices/1': 'm78-separable-meaning-flight',
  '0/inputs/0': 'm78-separable-sentence-noun', '0/inputs/1': 'm78-separable-sentence-question-pronoun',
  '1/selects/0': 'm78-topical-form-backup-pronoun', '1/selects/1': 'm78-topical-form-signup-course',
  '1/choices/0': 'm78-topical-meaning-wake-get', '1/choices/1': 'm78-topical-meaning-need-result',
  '1/inputs/0': 'm78-topical-sentence-travel', '1/inputs/1': 'm78-topical-sentence-login-negative',
  '2/choices/0': 'm78-qf-meaning-subject-object', '2/choices/1': 'm78-qf-meaning-state',
  '3/choices/0': 'm78-yesno-meaning-ability', '3/choices/1': 'm78-yesno-meaning-two-speakers',
  '4/choices/0': 'm78-wh-meaning-person-roles', '4/choices/1': 'm78-wh-meaning-method-reason',
  '5/choices/0': 'm78-answers-meaning-no-necessity', '5/choices/1': 'm78-answers-meaning-request',
  '5/inputs/4': 'm78-answers-sentence-ready', '5/inputs/5': 'm78-answers-sentence-brother',
};
const dataFor = index => master.lessons[index].blocks[expected[index].slot].data;
const totalFor = (spec, group) => spec[group].filter(value => value !== null).length;
const score = (n, total) => `Результат: ${n} / ${total}`;
function plain(text) { assert.equal(typeof text, 'string'); assert.match(text, /[\u0400-\u04ff]/);
  assert.doesNotMatch(text, /<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{a\d+\}/iu); }
function ordered(model, index, answer) {
  const words = model.inputTokenWords(answer), remaining = [...model.inputTokenBank(index)], result = [];
  for (let at = 0; at < words.length;) {
    const found = remaining.findIndex(token => model.inputTokenWords(token.value).every((word, offset) => words[at + offset] === word));
    assert.notEqual(found, -1, 'Missing native token group: ' + words.slice(at).join(' '));
    const token = remaining.splice(found, 1)[0]; result.push(token); at += model.inputTokenWords(token.value).length;
  }
  assert.equal(remaining.length, 0, 'Each authored token instance is used exactly once.'); return result;
}
function click(model, index, token) {
  model.appendInputToken('inputs', index, token); const after = model.inputAnswers[index]; model.appendInputToken('inputs', index, token);
  assert.equal(model.inputAnswers[index], after, 'Used tokens cannot append twice.'); assert.equal(model.isWordSuggestionOpen('inputs', index), false);
}
function setAndCheck(model, group, index, answer, correct, label) {
  model[props[group]][index] = answer; model.check(group); assert.equal(model.isCorrect(group, index), correct, label);
}
(async () => {
  let graded = 0, demos = 0, retained = 0, added = 0, retainedRephrase = 0, aliases = 0, options = 0, retries = 0;
  let allInputs = 0, fullSentences = 0, partialInputs = 0, partialManual = 0, manual = 0, clicked = 0, deleteReturn = 0, rebuild = 0, mixed = 0;
  const lessonResults = [], taskIds = new Set(), sentenceKeys = new Set(), settled = [];
  for (const [li, spec] of expected.entries()) {
    const lesson = master.lessons[li], block = lesson.blocks[spec.slot], data = block.data, immutable = JSON.stringify(data);
    assert.equal(lesson.identity, 'Database\\Seeders\\Page_V3\\' + spec.suffix); assert.equal(block.type, 'practice-set');
    assert.equal(lesson.blocks.filter(item => item.type === 'practice-set').length, 1);
    assert.equal(data.linked_practice.source, 'theory_links'); assert.deepEqual(data.linked_practice.question_types, ['4']);
    assert.equal(data.linked_practice.seeder_classes.length, 1);
    assert.equal(data.linked_practice.title, li < 2 ? 'Вправа 4. Побудуй речення' : li === 5 ? 'Вправа 5. Побудуй відповідь' : 'Вправа 5. Побудуй речення');
    assert.doesNotMatch([data.title, data.select_title, data.choice_title, data.input_title, data.rephrase_title].join(' '), /поліглот/i);
    for (const group of groups) {
      assert.equal(data[group].length, spec[group].length);
      for (const [index, task] of data[group].entries()) {
        const model = make(data), answer = spec[group][index], total = totalFor(spec, group), taskKey = `${li}/${group}/${index}`;
        if (answer === null) {
          demos++; assert.equal(group, 'rephrase'); assert.equal(index, 0);
          assert.equal(Object.hasOwn(task, 'answer'), false); assert.equal(Object.hasOwn(task, 'accepted'), false);
          assert.equal(Object.hasOwn(task, 'id'), false); assert.equal(Object.hasOwn(task, 'source_index'), false);
          assert.match(task.example_original, /[\u0400-\u04ff]/); assert.match(task.example_target, /[\u0400-\u04ff]/);
          assert.equal(model.hasAnswer(group, index), false);
          setAndCheck(model, group, index, 'not a graded answer', false, 'Demos remain ungraded');
          assert.equal(model.scoreText(group), score(0, total)); continue;
        }
        graded++; assert.equal(task.answer, answer); assert.deepEqual(task.accepted, [answer, ...(extraAccepted[taskKey] || [])]);
        assert.equal(Object.hasOwn(task, 'source_index'), false); assert.equal(task.punctuation_sensitive, undefined);
        assert.equal(task.strict, undefined); assert.equal(task.m38_semantic_checks, undefined);
        if (newIds[taskKey]) { added++; assert.equal(task.id, newIds[taskKey]); assert.ok(!taskIds.has(task.id)); taskIds.add(task.id); }
        else { retained++; assert.equal(Object.hasOwn(task, 'id'), false, 'Retained task ID absence is preserved.'); }
        if (group === 'rephrase') retainedRephrase++;
        for (const [key, text] of Object.entries(task.feedback)) { assert.equal(key, key.trim().toLowerCase()); plain(text); }
        for (const blank of ['', ' ', '\t\n', '\u00a0', '—']) {
          setAndCheck(model, group, index, blank, false, taskKey + ': empty');
          assert.equal(model.feedbackText(group, index), 'Введи відповідь.'); assert.equal(model.scoreText(group), score(0, total));
        }
        setAndCheck(model, group, index, 'zzzz wrong', false, taskKey + ': wrong'); plain(model.feedbackText(group, index));
        for (const accepted of task.accepted) {
          const key = accepted.trim().toLowerCase(); plain(task.feedback[key]);
          for (const value of [accepted, '  ' + accepted.toUpperCase() + '  ', accepted.replace(/[.!?]+$/, '') + '!!!']) {
            setAndCheck(model, group, index, value, true, `${taskKey}: ${value}`);
          }
          setAndCheck(model, group, index, accepted, true, taskKey);
          assert.equal(model.feedbackText(group, index), task.feedback[key]); aliases++;
        }
        if (group === 'selects' || group === 'choices') {
          const shown = task.options || (group === 'selects' ? data.options : data.choice_options || ['a', 'b']);
          assert.ok(shown.length >= 2); const keys = shown.map(value => model.normalize(value));
          assert.equal(new Set(keys).size, keys.length, 'Shown options cannot collide under actual normalization.'); assert.ok(!keys.includes(''));
          for (const accepted of task.accepted) assert.ok(shown.includes(accepted), 'Accepted selected forms are visibly offered.');
          for (const option of shown) {
            const correct = [answer, ...(extraAccepted[taskKey] || [])].includes(option);
            plain(task.feedback[option.trim().toLowerCase()]); setAndCheck(model, group, index, option, correct, `${taskKey}: option ${option}`);
            assert.equal(model.feedbackText(group, index), task.feedback[option.trim().toLowerCase()]); options++;
          }
        }
        setAndCheck(model, group, index, 'zzzz wrong', false, taskKey); assert.equal(model.scoreText(group), score(0, total));
        setAndCheck(model, group, index, answer, true, taskKey); assert.equal(model.scoreText(group), score(1, total));
        model.check(group); assert.equal(model.scoreText(group), score(1, total)); retries++;
        setAndCheck(model, group, index, '', false, taskKey); assert.equal(model.scoreText(group), score(0, total));
        model.resetGroup(group); assert.equal(model.isChecked(group), false); model.check(group); assert.equal(model.scoreText(group), score(0, total));
      }
    }
    for (const [index, task] of data.inputs.entries()) {
      allInputs++; const answer = spec.inputs[index], total = spec.inputs.length, original = make(data);
      assert.match(task.after, /[\u0400-\u04ff]/); assert.doesNotMatch(task.after, /\{a\d+\}|<\/?(?:p|span|strong)\b/);
      if (!spec.full.includes(index)) {
        partialInputs++; assert.equal(li, 5); assert.ok(index < 4);
        assert.match(task.before, /— (?:Yes|No),\s*$/); assert.equal(original.inputTokenBank(index).length, 0);
        assert.doesNotMatch(answer, /^(?:Yes|No)[,\s]/i);
        assert.match(task.prompt, /Ол[яю]/); assert.match(task.prompt, /Андрій|Марта/);
        for (const value of [answer, ...extraAccepted[`${li}/inputs/${index}`] || []]) {
          setAndCheck(original, 'inputs', index, value, true, 'Retained short gap');
          setAndCheck(original, 'inputs', index, (index === 1 || index === 3 ? 'No, ' : 'Yes, ') + value, false, 'Do not duplicate shown Yes/No');
        }
        for (const width of [320, 1280]) {
          const model = make(data, width); setAndCheck(model, 'inputs', index, answer, true, 'Short-gap manual path');
          assert.equal(model.scoreText('inputs'), score(1, total)); assert.equal(model.inputTokenBank(index).length, 0);
          model.resetGroup('inputs'); setAndCheck(model, 'inputs', index, '', false, 'Short-gap reset');
          assert.equal(model.scoreText('inputs'), score(0, total)); partialManual++;
        }
        continue;
      }
      fullSentences++; assert.match(answer, /^[A-Z].*[?.!]$/); assert.match(answer, /\s/);
      const canonicalKey = original.normalize(answer), startingBank = original.inputTokenBank(index); assert.ok(startingBank.length >= 2);
      assert.ok(!sentenceKeys.has(canonicalKey), 'Independent full-sentence tasks are not duplicated.'); sentenceKeys.add(canonicalKey);
      assert.equal(new Set(startingBank.map(token => token.id)).size, startingBank.length);
      for (const token of startingBank) assert.ok(token.value.split(/\s+/).length >= 1 && token.value.split(/\s+/).length <= 3);
      assert.notEqual(original.normalize(startingBank.map(token => token.value).join(' ')), canonicalKey, 'Authored groups start shuffled.');
      for (const width of [320, 1280]) {
        // Nominal configuration only; no DOM, responsive-layout or visibility acceptance is implied.
        const manualModel = make(data, width); manualModel.inputAnswers[index] = answer; manualModel.syncInputTokenBank(index); manualModel.check('inputs');
        assert.equal(manualModel.isCorrect('inputs', index), true); assert.equal(manualModel.isInputTokenBankEmpty(index), true);
        assert.equal(manualModel.scoreText('inputs'), score(1, total)); manual++;
        const model = make(data, width), chosen = ordered(model, index, answer);
        const untouched = data.inputs.map((_, n) => n === index ? null : JSON.stringify(model.inputTokenBank(n)));
        model.wordSearchEndpoint = '/forbidden'; model.wordSuggestionOpen[`inputs-${index}`] = true;
        model.wordSuggestionResults[`inputs-${index}`] = [{ word: 'not-opened' }];
        for (const [position, token] of chosen.entries()) {
          click(model, index, token); await model.searchWordSuggestions('inputs', index, { target: { value: 'word', selectionStart: 4 } });
          assert.equal(model.isWordSuggestionOpen('inputs', index), false); model.check('inputs');
          assert.equal(model.isCorrect('inputs', index), position === chosen.length - 1);
        }
        assert.equal(model.normalize(model.inputAnswers[index]), canonicalKey);
        assert.equal(model.inputAnswers[index], answer, 'Canonical token assembly preserves the visible case and punctuation, not merely the grading key.');
        assert.equal(model.isInputTokenBankEmpty(index), true); clicked++;
        settled.push({ lesson: names[li], task: `${li}/inputs/${index}`, nominal_width: width, token_assembled_text: model.inputAnswers[index], canonical: answer });
        const last = chosen.at(-1); model.inputAnswers[index] = chosen.slice(0, -1).map(token => token.value).join(' '); model.syncInputTokenBank(index);
        assert.equal(model.inputTokenBank(index).find(token => token.id === last.id).used, false); assert.equal(model.isCorrect('inputs', index), false);
        click(model, index, model.inputTokenBank(index).find(token => token.id === last.id)); assert.equal(model.isCorrect('inputs', index), true); deleteReturn++;
        model.inputAnswers[index] = ''; model.syncInputTokenBank(index); model.check('inputs');
        assert.ok(model.inputTokenBank(index).every(token => !token.used)); assert.equal(model.scoreText('inputs'), score(0, total));
        for (const token of ordered(model, index, answer)) click(model, index, token);
        model.check('inputs'); assert.equal(model.isCorrect('inputs', index), true); rebuild++;
        for (let n = 0; n < data.inputs.length; n++) if (n !== index) {
          assert.equal(JSON.stringify(model.inputTokenBank(n)), untouched[n]); assert.equal(model.inputAnswers[n], undefined);
        }
        model.resetGroup('inputs'); model.check('inputs'); assert.equal(model.scoreText('inputs'), score(0, total));
        assert.ok(model.inputTokenBank(index).every(token => !token.used));
      }
      const model = make(data), chosen = ordered(model, index, answer); model.inputAnswers[index] = chosen[0].value; model.syncInputTokenBank(index);
      for (const token of chosen.slice(1)) click(model, index, model.inputTokenBank(index).find(item => item.id === token.id));
      model.check('inputs'); assert.equal(model.isCorrect('inputs', index), true); assert.equal(model.normalize(model.inputAnswers[index]), canonicalKey); mixed++;
    }
    const complete = make(data);
    for (const group of groups) {
      for (const [index, answer] of spec[group].entries()) if (answer !== null) complete[props[group]][index] = answer;
      complete.check(group); assert.equal(complete.scoreText(group), score(totalFor(spec, group), totalFor(spec, group)));
    }
    for (const group of groups) {
      const others = groups.filter(key => key !== group).map(key => [key, complete.scoreText(key)]);
      complete.resetGroup(group); complete.check(group); assert.equal(complete.scoreText(group), score(0, totalFor(spec, group)));
      for (const [other, prior] of others) assert.equal(complete.scoreText(other), prior, 'Group reset is independent.');
    }
    assert.equal(JSON.stringify(data), immutable);
    lessonResults.push({ name: names[li], graded: groups.reduce((sum, group) => sum + totalFor(spec, group), 0),
      ungraded_demos: spec.rephrase.includes(null) ? 1 : 0, full_sentence_inputs: spec.full.length,
      partial_inputs: spec.inputs.length - spec.full.length });
  }
  const semantic = [
    [0, 'selects', 0, 'turn off it', false], [0, 'selects', 0, 'turn it off', true],
    [0, 'selects', 1, 'look them after', false], [0, 'selects', 1, 'looks after them', false],
    [0, 'inputs', 0, 'She picked up the box.', true], [0, 'inputs', 0, 'She picked the box up.', true],
    [0, 'inputs', 0, 'She picked the box.', false], [0, 'inputs', 1, 'Did you write down it?', false],
    [0, 'inputs', 1, 'Did he write it down?', false],
    [1, 'selects', 0, 'back up them', false], [1, 'selects', 1, 'in for', false],
    [1, 'inputs', 0, 'We arrived in Lviv at dawn.', false], [1, 'inputs', 1, 'She did not log in to the system.', true],
    [1, 'inputs', 1, 'She did not sign up for the system.', false],
    [2, 'inputs', 0, 'Does she speaks French?', false], [2, 'inputs', 0, 'She speaks French?', false],
    [2, 'inputs', 2, 'Do he can drive?', false], [2, 'inputs', 3, 'Where did I go?', false],
    [2, 'rephrase', 4, 'Who did call you?', false], [2, 'rephrase', 4, 'Who called you?', true],
    [3, 'inputs', 3, 'Have you got a car?', true], [3, 'inputs', 3, 'Do I have a car?', false],
    [3, 'rephrase', 2, 'Did they went home?', false], [3, 'rephrase', 3, 'You can help me?', false],
    [3, 'rephrase', 3, 'Can you help me?', true],
    [4, 'selects', 2, 'How', false], [4, 'selects', 3, 'Why', false],
    [4, 'inputs', 0, 'Where do I live?', false], [4, 'inputs', 1, 'How old are you?', false],
    [4, 'inputs', 2, 'Where did he come from?', false], [4, 'inputs', 3, 'Why did they arrive?', false],
    [4, 'rephrase', 2, 'Who does live here?', false], [4, 'rephrase', 2, 'Who lives here?', true],
    [5, 'selects', 1, "Yes, she's", false], [5, 'inputs', 0, 'Yes, I do', false],
    [5, 'inputs', 0, 'you do', false], [5, 'inputs', 1, "she's not", true],
    [5, 'inputs', 2, 'we can', false], [5, 'inputs', 3, "I don't", false],
    [5, 'inputs', 4, "Yes, I'm ready.", true], [5, 'inputs', 4, "Yes, I'm", false],
    [5, 'inputs', 4, 'Yes, I am.', false], [5, 'inputs', 4, 'Yes, you are ready.', false],
    [5, 'inputs', 5, "No, he doesn't work here.", true], [5, 'inputs', 5, 'No, he does not works here.', false],
    [5, 'rephrase', 1, 'Q: Can you swim? A: Yes, I can.', false],
    [5, 'rephrase', 1, 'Yes, I can.', true], [5, 'rephrase', 2, "Yes, she's", false],
    [5, 'rephrase', 2, 'Q: Is she happy? A: Yes, she is.', false],
    [5, 'rephrase', 3, 'Q: Do you like it? A: Yes, I do.', false], [5, 'rephrase', 3, 'Yes, I like it.', false],
  ];
  for (const [li, group, index, answer, correct] of semantic) {
    const model = make(dataFor(li)); setAndCheck(model, group, index, answer, correct, `Topic-bound ${names[li]}/${group}/${index}: ${answer}`);
    plain(model.feedbackText(group, index));
  }
  assert.equal(graded, 67); assert.equal(demos, 4); assert.equal(retained, 45); assert.equal(added, 22); assert.equal(retainedRephrase, 13);
  assert.equal(allInputs, 22); assert.equal(fullSentences, 18); assert.equal(partialInputs, 4);
  assert.equal(partialManual, 8);
  assert.equal(aliases, 76); assert.equal(options, 85); assert.equal(retries, 67);
  assert.equal(manual, 36); assert.equal(clicked, 36); assert.equal(deleteReturn, 36); assert.equal(rebuild, 36); assert.equal(mixed, 18);
  assert.equal(networkCalls, 0);
  for (const [file, sha] of tracked) assert.equal(digest(fs.readFileSync(file)), sha, 'Read-only source bytes changed: ' + file);
  console.log(JSON.stringify({ kind: 'actual unchanged native theoryPracticeSet factory; offline engine contract only', mode,
    master_sha256: masterSha, factory_profile: profile.label, factory_raw_sha256: rawSha, factory_lf_sha256: lfSha,
    lessons: lessonResults, graded_tasks: graded, retained_graded_tasks: retained, new_graded_tasks: added,
    retained_graded_rephrase: retainedRephrase, ungraded_demos: demos, accepted_values_checked: aliases, displayed_options_checked: options,
    wrong_correct_empty_retry_paths: retries, native_inputs: allInputs, full_sentence_inputs: fullSentences, partial_inputs: partialInputs,
    partial_manual_nominal_size_cases: partialManual,
    nominal_widths: [320, 1280], width_labels_are_not_layout_acceptance: true,
    manual_path_cases: manual, token_path_cases: clicked, full_sentence_mode_size_cases: manual + clicked,
    deletion_returns_token_paths: deleteReturn, clear_return_rebuild_paths: rebuild, mixed_manual_token_paths: mixed,
    semantic_cases: semantic.length, token_settled_texts: settled,
    token_word_suggestions_suppressed: true, independent_group_scores_and_reset: true, source_bytes_unchanged: true,
    mocked_focus_calls: focusCalls, assertions, network_calls: networkCalls, sql_calls: 0, file_writes: 0 }, null, 2));
})().catch(error => { console.error(error); process.exitCode = 1; });
