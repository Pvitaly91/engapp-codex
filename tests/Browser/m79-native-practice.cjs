'use strict';
// Offline contract against the unchanged native factory. Nominal widths are not DOM acceptance.
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const vm = require('node:vm');
const strict = require('node:assert/strict');
let assertions = 0;
const assert = new Proxy(strict, { get(target, key) {
  const value = target[key];
  return typeof value === 'function' && key !== 'AssertionError' ? (...args) => { assertions++; return value(...args); } : value;
} });
const root = path.resolve(__dirname, '../..');
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const tracked = new Map();
function read(file) { const bytes = fs.readFileSync(file); tracked.set(file, sha(bytes)); return bytes.toString('utf8'); }
const names = ['alternative', 'tags', 'negative', 'subject', 'embedded', 'indirect'];
const args = process.argv.slice(2);
let draft = false, draftSha = null, factoryRoot = root;
while (args.length) {
  const flag = args.shift();
  if (flag === '--parts') { assert.equal(draft, false); draft = true; continue; }
  const value = args.shift(); assert.equal(typeof value, 'string');
  if (flag === '--parts-sha256') { assert.equal(draftSha, null); assert.match(value, /^[a-f0-9]{64}$/); draftSha = value; }
  else if (flag === '--factory-root') { assert.ok(path.isAbsolute(value)); factoryRoot = path.resolve(value); }
  else assert.fail('Unknown M79 native-contract flag: ' + flag);
}
assert.equal(draft, draftSha !== null, '--parts requires the exact ordered six-part byte SHA256.');
let master, masterSha, mode;
if (draft) {
  const texts = names.map(name => read(path.join(root, 'docs/content/m79-author-parts/' + name + '.v1.json')));
  assert.equal(sha(Buffer.concat(texts.map(text => Buffer.from(text)))), draftSha);
  master = { lessons: texts.map(text => JSON.parse(text).lesson) }; masterSha = draftSha;
  mode = 'explicit SHA-bound six-author draft; NOT frozen or browser acceptance';
} else {
  const policy = read(path.join(root, 'app/Support/M79QuestionTypesCompletion.php'));
  const constant = name => { const hit = policy.match(new RegExp("public const " + name + " = '([^']+)';")); assert.ok(hit, name); return hit[1]; };
  for (const name of ['BEFORE', 'SOURCE', 'MASTER', 'CONTRACT', 'CHECKSUMS', 'BINDINGS']) {
    const relative = constant(name), pin = constant(name + '_SHA');
    assert.match(relative, /^(?:docs\/content\/|database\/content-patches\/)[A-Za-z0-9./-]+\.json$/);
    assert.ok(!relative.includes('..')); assert.match(pin, /^[a-f0-9]{64}$/); assert.notEqual(pin, '0'.repeat(64));
    const text = read(path.join(root, relative)); assert.equal(sha(Buffer.from(text)), pin, name + ': frozen bytes');
    if (name === 'MASTER') { master = JSON.parse(text); masterSha = pin; }
  }
  assert.equal(master.status, 'frozen'); assert.equal(master.version, '1.0.1');
  assert.equal(master.base_sha, 'f33d1a0e2eff08e66044d9b746a46bc33f207bce');
  mode = 'frozen final M79 package; NOT live browser acceptance';
}
assert.equal(master.lessons.length, 6);
const factoryPath = path.join(factoryRoot, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php');
const native = read(factoryPath), rawSha = sha(Buffer.from(native)), lfSha = sha(Buffer.from(native.replace(/\r\n/g, '\n')));
const profile = [
  { label: 'accepted-worktree', raw: ['05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e', 'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d'], lf: 'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d' },
  { label: 'served-root-preserved-native', raw: ['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186'], lf: 'a419b43d853126d88bc43683bddeb4d8efd190b08d1bfcf60944a005ad122ba7' },
].find(item => item.raw.includes(rawSha) && item.lf === lfSha);
assert.ok(profile, 'Only an unchanged accepted or independently preserved served native factory is allowed.');
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
  fetch() { networkCalls++; throw Error('Network forbidden in offline M79 native contract.'); }, URL });
vm.runInContext(scripts[0], context, { filename: factoryPath, timeout: 1000 }); listener();
const make = (data, width = 1280) => {
  windowStub.innerWidth = width;
  const model = factory({ ...data, i18n: { empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.',
    answer: 'Правильна відповідь', score: 'Результат: :correct / :total' } });
  model.$nextTick = callback => callback(); model.init(); return model;
};
const groups = ['selects', 'choices', 'inputs', 'rephrase'];
const props = { selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers', rephrase: 'rephraseAnswers' };
// These teaching expectations are literal fixtures, not computed from AFTER answers.
const expected = [
  { suffix: 'TypesOfQuestionsAlternativeQuestionsTheorySeeder', slot: 11, selects: ['b', 'a', 'c'], choices: ['A', 'A'],
    inputs: ['Does she like cats or dogs?', 'Are you from Spain or Italy?', 'Will they come today or tomorrow?', 'Is it big or small?'],
    rephrase: [null, 'Does she like cats or dogs?', 'Is it hot or cold?', 'Can you swim or dive?'], full: [0, 1, 2, 3] },
  { suffix: 'TypesOfQuestionsQuestionTagsDisjunctiveQuestionsTheorySeeder', slot: 12,
    selects: ["isn't she", "don't you", "can't they", "aren't I"], choices: ['A', 'A'],
    inputs: ["isn't she", 'do you', "can't he", 'will they', "Marta works here, doesn't she?", "There isn't a bus, is there?"],
    rephrase: [null, "You like coffee, don't you?", "She can swim, can't she?", "I am right, aren't I?"], full: [4, 5] },
  { suffix: 'TypesOfQuestionsNegativeQuestionsTheorySeeder', slot: 11, selects: ['b', 'b', 'a'], choices: ['A', 'B'],
    inputs: ["Don't you know?", "Isn't she coming?", "Can't they help?", "Didn't you see it?"],
    rephrase: [null, "Doesn't she like it?", "Can't you swim?", "Don't you know?"], full: [0, 1, 2, 3] },
  { suffix: 'TypesOfQuestionsSubjectQuestionsTheorySeeder', slot: 6, selects: ['contacted', 'are'],
    choices: ['Б: Анна вже є підметом; шукаємо її співрозмовника.', 'Ні: це предмет у пасиві; виконавець може залишатися невідомим.'],
    inputs: ['How many children live here?', 'Who is waiting outside?'], rephrase: [], full: [0, 1] },
  { suffix: 'TypesOfQuestionsEmbeddedQuestionsTheorySeeder', slot: 6, selects: ['does', "didn't come"],
    choices: ['А — головне питання; Б — твердження, хоча обидва містять вкладену частину.', 'Тут допустимі if і whether; that не передає того самого «чи».'],
    inputs: ["I don't know what Lina does.", 'Do you know if the door is locked?'], rephrase: [], full: [0, 1] },
  { suffix: 'TypesOfQuestionsIndirectQuestionsTheorySeeder', slot: 6, selects: ['where the manager is', 'you arrived'],
    choices: ['А: називає платформу, про яку запитали.', 'Це теперішній м’який запит; was wondering саме по собі не робить наявність кімнат минулою.'],
    inputs: ['Could you tell me where you work?', 'I was wondering if the room is available tonight.'], rephrase: [], full: [0, 1] },
];
const extraAccepted = {
  '1/selects/3': ['am I not'], '1/inputs/0': ['is she not'], '1/inputs/2': ['can he not'],
  '1/inputs/4': ['Marta works here, does she not?'], '1/inputs/5': ['There is not a bus, is there?'],
  '1/rephrase/1': ['You like coffee, do you not?'], '1/rephrase/2': ['She can swim, can she not?'], '1/rephrase/3': ['I am right, am I not?'],
  '2/inputs/0': ['Do you not know?'], '2/inputs/1': ['Is she not coming?'], '2/inputs/2': ['Can they not help?'], '2/inputs/3': ['Did you not see it?'],
  '2/rephrase/1': ['Does she not like it?'], '2/rephrase/2': ['Can you not swim?'], '2/rephrase/3': ['Do you not know?'],
  '4/inputs/0': ['I do not know what Lina does.'], '4/inputs/1': ['Do you know whether the door is locked?'],
  '5/inputs/1': ['I was wondering whether the room is available tonight.', 'I was wondering if the room is free tonight.', 'I was wondering whether the room is free tonight.'],
};
const newIds = {
  '0/choices/0': 'm79-alternative-meaning-drink', '0/choices/1': 'm79-alternative-meaning-existence',
  '1/choices/0': 'm79-tags-meaning-confirmation', '1/choices/1': 'm79-tags-meaning-factual-answer',
  '1/inputs/4': 'm79-tags-full-work', '1/inputs/5': 'm79-tags-full-there',
  '2/choices/0': 'm79-negative-meaning-coming', '2/choices/1': 'm79-negative-meaning-know',
  '3/selects/0': 'm79-subject-form-neutral-past', '3/selects/1': 'm79-subject-form-plural-state',
  '3/choices/0': 'm79-subject-meaning-participants', '3/choices/1': 'm79-subject-meaning-passive',
  '3/inputs/0': 'm79-subject-sentence-many-children', '3/inputs/1': 'm79-subject-sentence-continuous',
  '4/selects/0': 'm79-embedded-form-lexical-do', '4/selects/1': 'm79-embedded-form-negative-past',
  '4/choices/0': 'm79-embedded-meaning-main-clause', '4/choices/1': 'm79-embedded-meaning-if-whether',
  '4/inputs/0': 'm79-embedded-sentence-lexical-do', '4/inputs/1': 'm79-embedded-sentence-if-whether',
  '5/selects/0': 'm79-indirect-form-where-manager', '5/selects/1': 'm79-indirect-form-speaker-preservation',
  '5/choices/0': 'm79-indirect-meaning-information-reply', '5/choices/1': 'm79-indirect-meaning-current-wondering',
  '5/inputs/0': 'm79-indirect-sentence-current-work', '5/inputs/1': 'm79-indirect-sentence-current-room',
};
function plain(text) { assert.equal(typeof text, 'string'); assert.match(text, /[\u0400-\u04ff]/);
  assert.doesNotMatch(text, /<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{a\d+\}/iu); }
const score = (n, total) => `Результат: ${n} / ${total}`;
const totalFor = (spec, group) => spec[group].filter(item => item !== null).length;
const dataFor = index => master.lessons[index].blocks[expected[index].slot].data;
function setAndCheck(model, group, index, value, correct, message) {
  model[props[group]][index] = value; model.check(group); assert.equal(model.isCorrect(group, index), correct, message);
}
function ordered(model, index, canonical) {
  const words = model.inputTokenWords(canonical), remaining = [...model.inputTokenBank(index)], out = [];
  for (let at = 0; at < words.length;) {
    const position = remaining.findIndex(token => model.inputTokenWords(token.value).every((word, offset) => words[at + offset] === word));
    assert.notEqual(position, -1, 'Missing canonical chunk: ' + words.slice(at).join(' '));
    const token = remaining.splice(position, 1)[0]; out.push(token); at += model.inputTokenWords(token.value).length;
  }
  assert.equal(remaining.length, 0, 'All authored instances, including repeated auxiliaries, must be used.'); return out;
}
function click(model, index, token) {
  model.appendInputToken('inputs', index, token); const text = model.inputAnswers[index];
  model.appendInputToken('inputs', index, token); assert.equal(model.inputAnswers[index], text, 'Used token cannot append twice.');
  assert.equal(model.isWordSuggestionOpen('inputs', index), false);
}
(async () => {
  let graded = 0, demos = 0, retained = 0, added = 0, aliases = 0, options = 0, retries = 0;
  let allInputs = 0, full = 0, partial = 0, manual = 0, clicked = 0, deleted = 0, rebuilt = 0, mixed = 0, partialManual = 0;
  const ids = new Set(), canonicalInputs = new Set(), results = [], settled = [];
  for (const [li, spec] of expected.entries()) {
    const lesson = master.lessons[li], block = lesson.blocks[spec.slot], data = block.data, immutable = JSON.stringify(data);
    assert.equal(lesson.identity, 'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\' + spec.suffix);
    assert.equal(block.type, 'practice-set'); assert.equal(lesson.blocks.filter(item => item.type === 'practice-set').length, 1);
    assert.equal(data.linked_practice.source, 'theory_links'); assert.deepEqual(data.linked_practice.question_types, ['4']);
    assert.equal(data.linked_practice.title, li < 3 ? 'Вправа 5. Побудуй речення' : 'Вправа 4. Побудуй речення');
    assert.doesNotMatch([data.title, data.select_title, data.choice_title, data.input_title, data.rephrase_title].join(' '), /поліглот/i);
    for (const group of groups) {
      assert.equal(data[group].length, spec[group].length);
      for (const [index, task] of data[group].entries()) {
        const answer = spec[group][index], model = make(data), total = totalFor(spec, group), key = `${li}/${group}/${index}`;
        if (answer === null) {
          demos++; assert.equal(group, 'rephrase'); assert.equal(index, 0);
          for (const field of ['answer', 'accepted', 'id', 'source_index']) assert.equal(Object.hasOwn(task, field), false);
          assert.match(task.example_original, /[\u0400-\u04ff]/); assert.match(task.example_target, /[\u0400-\u04ff]/);
          assert.equal(model.hasAnswer(group, index), false); setAndCheck(model, group, index, 'not graded', false, key);
          assert.equal(model.scoreText(group), score(0, total)); continue;
        }
        graded++; assert.equal(task.answer, answer); assert.deepEqual(task.accepted, [answer, ...(extraAccepted[key] || [])]);
        for (const field of ['punctuation_sensitive', 'strict', 'm38_semantic_checks']) assert.equal(task[field], undefined);
        if (newIds[key]) { added++; assert.equal(task.id, newIds[key]); assert.ok(!ids.has(task.id)); ids.add(task.id); }
        else { retained++; assert.equal(Object.hasOwn(task, 'id'), false); }
        if (li < 3) assert.equal(Object.hasOwn(task, 'source_index'), false, 'Captured task source-index absence remains.');
        else assert.equal(task.source_index, { selects: 1, choices: 3, inputs: 5 }[group] + index);
        for (const [feedbackKey, feedback] of Object.entries(task.feedback)) { assert.equal(feedbackKey, feedbackKey.trim().toLowerCase()); plain(feedback); }
        for (const empty of ['', ' ', '\t\n', '\u00a0', '—']) {
          setAndCheck(model, group, index, empty, false, key + ': empty'); assert.equal(model.feedbackText(group, index), 'Введи відповідь.');
          assert.equal(model.scoreText(group), score(0, total));
        }
        setAndCheck(model, group, index, 'zzzz wrong', false, key + ': wrong'); plain(model.feedbackText(group, index));
        for (const accepted of task.accepted) {
          plain(task.feedback[accepted.trim().toLowerCase()]);
          for (const value of [accepted, '  ' + accepted.toUpperCase() + '  ', accepted.replace(/[.!?]+$/, '') + '!!!']) {
            setAndCheck(model, group, index, value, true, key + ': accepted ' + value);
          }
          setAndCheck(model, group, index, accepted, true, key); assert.equal(model.feedbackText(group, index), task.feedback[accepted.trim().toLowerCase()]); aliases++;
        }
        if (group === 'selects' || group === 'choices') {
          const shown = task.options || (group === 'selects' ? data.options : data.choice_options || ['a', 'b']);
          assert.ok(shown.length >= 2); const keys = shown.map(value => model.normalize(value));
          assert.equal(new Set(keys).size, keys.length); assert.ok(!keys.includes(''));
          for (const accepted of task.accepted) assert.ok(shown.includes(accepted), 'Correct form is visibly offered.');
          for (const option of shown) {
            plain(task.feedback[option.trim().toLowerCase()]); setAndCheck(model, group, index, option, task.accepted.includes(option), key + ': option ' + option);
            assert.equal(model.feedbackText(group, index), task.feedback[option.trim().toLowerCase()]); options++;
          }
        }
        setAndCheck(model, group, index, 'zzzz wrong', false, key); model.check(group); assert.equal(model.scoreText(group), score(0, total));
        setAndCheck(model, group, index, answer, true, key); assert.equal(model.scoreText(group), score(1, total));
        model.check(group); assert.equal(model.scoreText(group), score(1, total)); retries++;
        setAndCheck(model, group, index, '', false, key); model.resetGroup(group); model.check(group);
        assert.equal(model.scoreText(group), score(0, total));
      }
    }
    for (const [index, task] of data.inputs.entries()) {
      allInputs++; const answer = spec.inputs[index], base = make(data), total = spec.inputs.length;
      assert.match(task.after, /[\u0400-\u04ff]/); assert.doesNotMatch(task.after, /\{a\d+\}|<\/?(?:p|span|strong)\b/);
      if (!spec.full.includes(index)) {
        partial++; assert.equal(li, 1); assert.ok(index < 4); assert.match(task.before, /,\s*$/);
        assert.equal(base.inputTokenBank(index).length, 0); assert.doesNotMatch(answer, /,|[?.!]$/);
        for (const width of [320, 1280]) {
          const model = make(data, width); setAndCheck(model, 'inputs', index, answer, true, 'Retained partial tag');
          setAndCheck(model, 'inputs', index, task.before + answer + '?', false, 'Do not duplicate shown main clause');
          model.resetGroup('inputs'); model.check('inputs'); assert.equal(model.scoreText('inputs'), score(0, total)); partialManual++;
        }
        continue;
      }
      full++; assert.match(answer, /^[A-Z].*[?.!]$/); assert.ok(!canonicalInputs.has(base.normalize(answer))); canonicalInputs.add(base.normalize(answer));
      const bank = base.inputTokenBank(index); assert.ok(bank.length >= 2); assert.equal(new Set(bank.map(token => token.id)).size, bank.length);
      for (const token of bank) assert.ok(token.value.split(/\s+/).length >= 1 && token.value.split(/\s+/).length <= 3);
      assert.notEqual(base.normalize(bank.map(token => token.value).join(' ')), base.normalize(answer), 'Initial chunks are shuffled.');
      for (const width of [320, 1280]) {
        const manualModel = make(data, width); manualModel.inputAnswers[index] = answer; manualModel.syncInputTokenBank(index); manualModel.check('inputs');
        assert.equal(manualModel.isCorrect('inputs', index), true); assert.equal(manualModel.isInputTokenBankEmpty(index), true); manual++;
        const model = make(data, width), chosen = ordered(model, index, answer);
        const others = data.inputs.map((_, n) => n === index ? null : JSON.stringify(model.inputTokenBank(n)));
        model.wordSearchEndpoint = '/forbidden'; model.wordSuggestionOpen[`inputs-${index}`] = true;
        for (const [position, token] of chosen.entries()) {
          click(model, index, token); await model.searchWordSuggestions('inputs', index, { target: { value: 'word', selectionStart: 4 } });
          assert.equal(model.isWordSuggestionOpen('inputs', index), false); model.check('inputs');
          assert.equal(model.isCorrect('inputs', index), position === chosen.length - 1);
        }
        assert.equal(model.inputAnswers[index], answer, 'Visible canonical, not only normalized grading key.');
        assert.equal(model.isInputTokenBankEmpty(index), true); clicked++;
        settled.push({ lesson: names[li], task: `${li}/inputs/${index}`, nominal_width: width, canonical: answer, token_assembled_text: model.inputAnswers[index] });
        const last = chosen.at(-1); model.inputAnswers[index] = chosen.slice(0, -1).map(token => token.value).join(' '); model.syncInputTokenBank(index);
        assert.equal(model.inputTokenBank(index).find(token => token.id === last.id).used, false); assert.equal(model.isCorrect('inputs', index), false);
        click(model, index, model.inputTokenBank(index).find(token => token.id === last.id)); assert.equal(model.isCorrect('inputs', index), true); deleted++;
        model.inputAnswers[index] = ''; model.syncInputTokenBank(index); model.check('inputs'); assert.equal(model.scoreText('inputs'), score(0, total));
        assert.ok(model.inputTokenBank(index).every(token => !token.used));
        for (const token of ordered(model, index, answer)) click(model, index, token);
        model.check('inputs'); assert.equal(model.isCorrect('inputs', index), true); rebuilt++;
        for (let n = 0; n < data.inputs.length; n++) if (n !== index) {
          assert.equal(JSON.stringify(model.inputTokenBank(n)), others[n]); assert.equal(model.inputAnswers[n], undefined);
        }
        model.resetGroup('inputs'); model.check('inputs'); assert.equal(model.scoreText('inputs'), score(0, total));
      }
      const model = make(data), chosen = ordered(model, index, answer); model.inputAnswers[index] = chosen[0].value; model.syncInputTokenBank(index);
      for (const token of chosen.slice(1)) click(model, index, model.inputTokenBank(index).find(item => item.id === token.id));
      model.check('inputs'); assert.equal(model.isCorrect('inputs', index), true); assert.equal(model.inputAnswers[index], answer); mixed++;
    }
    const complete = make(data);
    for (const group of groups) { for (const [index, value] of spec[group].entries()) if (value !== null) complete[props[group]][index] = value;
      complete.check(group); assert.equal(complete.scoreText(group), score(totalFor(spec, group), totalFor(spec, group))); }
    for (const group of groups) {
      const otherScores = groups.filter(key => key !== group).map(key => [key, complete.scoreText(key)]);
      complete.resetGroup(group); complete.check(group); assert.equal(complete.scoreText(group), score(0, totalFor(spec, group)));
      for (const [other, prior] of otherScores) assert.equal(complete.scoreText(other), prior);
    }
    assert.equal(JSON.stringify(data), immutable);
    results.push({ name: names[li], graded: groups.reduce((n, group) => n + totalFor(spec, group), 0),
      ungraded_demos: spec.rephrase.includes(null) ? 1 : 0, full_sentence_inputs: spec.full.length, partial_inputs: spec.inputs.length - spec.full.length });
  }
  const semantic = [
    [0, 'choices', 0, 'B', false], [0, 'choices', 1, 'A', true], [0, 'choices', 1, 'B', false],
    [0, 'inputs', 0, 'Does she like cats and dogs?', false], [0, 'inputs', 2, 'Will I come today or tomorrow?', false],
    [0, 'rephrase', 3, 'You can swim or dive?', false],
    [1, 'selects', 3, 'am I not', true], [1, 'selects', 3, "aren't I", true], [1, 'selects', 3, "isn't I", false],
    [1, 'choices', 0, 'B', false], [1, 'choices', 1, 'B', false], [1, 'choices', 1, 'A', true],
    [1, 'inputs', 4, 'Marta works here, does she?', false], [1, 'inputs', 5, 'There is a bus, is there?', false],
    [1, 'rephrase', 3, 'I am right, am I not?', true], [1, 'rephrase', 3, 'I am right, am not I?', false],
    [2, 'choices', 0, 'B', false], [2, 'choices', 1, 'A', false],
    [2, 'inputs', 0, 'Do you not know?', true], [2, 'inputs', 0, 'Do not you know?', false],
    [2, 'inputs', 1, 'Is she coming?', false], [2, 'inputs', 3, 'Did you see it?', false],
    [2, 'rephrase', 2, 'Can you swim?', false], [2, 'rephrase', 3, 'Do you not know?', true],
    [3, 'selects', 0, 'did contact', false], [3, 'selects', 1, 'is', false],
    [3, 'inputs', 0, 'How many children lives here?', false], [3, 'inputs', 1, 'Who waiting outside?', false],
    [4, 'selects', 0, 'does do', false], [4, 'selects', 1, 'came', false],
    [4, 'inputs', 0, "I don't know what does Lina.", false], [4, 'inputs', 0, "I don't know what Lina.", false],
    [4, 'inputs', 1, 'Do you know that the door is locked?', false], [4, 'inputs', 1, 'Do you know whether the door is locked?', true],
    [5, 'selects', 0, 'where the manager was', false], [5, 'selects', 1, 'I arrived', false],
    [5, 'inputs', 0, 'Can you tell me where you work?', false], [5, 'inputs', 0, 'Could you tell me where I work?', false],
    [5, 'inputs', 1, 'I was wondering whether the room is free tonight.', true],
    [5, 'inputs', 1, 'I was wondering if the room was available tonight.', false],
    [5, 'inputs', 1, 'I am wondering if the room is available tonight.', false],
  ];
  for (const [li, group, index, value, correct] of semantic) {
    const model = make(dataFor(li)); setAndCheck(model, group, index, value, correct, names[li] + ': semantic ' + value);
    plain(model.feedbackText(group, index));
  }
  assert.equal(graded, 57); assert.equal(demos, 3); assert.equal(retained, 31); assert.equal(added, 26);
  assert.equal(aliases, 77); assert.equal(options, 72); assert.equal(retries, 57);
  assert.equal(allInputs, 20); assert.equal(full, 16); assert.equal(partial, 4); assert.equal(partialManual, 8);
  assert.equal(manual, 32); assert.equal(clicked, 32); assert.equal(deleted, 32); assert.equal(rebuilt, 32); assert.equal(mixed, 16);
  assert.equal(networkCalls, 0);
  for (const [file, pin] of tracked) assert.equal(sha(fs.readFileSync(file)), pin, 'Read-only bytes changed: ' + file);
  console.log(JSON.stringify({ kind: 'actual unchanged native theoryPracticeSet factory; offline engine contract only', mode,
    master_sha256: masterSha, factory_profile: profile.label, factory_raw_sha256: rawSha, factory_lf_sha256: lfSha,
    lessons: results, graded_tasks: graded, retained_graded_tasks: retained, new_graded_tasks: added, ungraded_demos: demos,
    accepted_values_checked: aliases, displayed_options_checked: options, wrong_correct_empty_retry_paths: retries,
    native_inputs: allInputs, full_sentence_inputs: full, partial_inputs: partial, partial_manual_nominal_size_cases: partialManual,
    nominal_widths: [320, 1280], width_labels_are_not_layout_acceptance: true,
    manual_path_cases: manual, token_path_cases: clicked, full_sentence_mode_size_cases: manual + clicked,
    deletion_returns_token_paths: deleted, clear_return_rebuild_paths: rebuilt, mixed_manual_token_paths: mixed,
    semantic_cases: semantic.length, token_settled_texts: settled, token_word_suggestions_suppressed: true,
    independent_group_scores_and_reset: true, source_bytes_unchanged: true, assertions,
    mocked_focus_calls: focusCalls, network_calls: networkCalls, sql_calls: 0, file_writes: 0 }, null, 2));
})().catch(error => { console.error(error); process.exitCode = 1; });
