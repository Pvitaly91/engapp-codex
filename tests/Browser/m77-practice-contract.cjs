'use strict';
// Execute the existing factory, never a copied grader. No DOM/layout/browser acceptance is claimed.
// Draft input requires the SHA256 of the ordered six complete author-part byte strings.
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
const names = ['one', 'time', 'place', 'movement', 'dependent', 'phrasal'];
const args = process.argv.slice(2);
let draftSha = null, factoryRoot = root;
while (args.length) {
  const flag = args.shift(), value = args.shift(); assert.equal(typeof value, 'string', 'Every flag needs its explicit value.');
  if (flag === '--draft-author-sha256') { assert.equal(draftSha, null); assert.match(value, /^[a-f0-9]{64}$/); draftSha = value; }
  else if (flag === '--factory-root') { assert.ok(path.isAbsolute(value)); factoryRoot = path.resolve(value); }
  else assert.fail('Unknown contract flag: ' + flag);
}
let master, masterSha, mode;
if (draftSha !== null) {
  const parts = names.map(name => read(path.join(root, 'docs/content/m77-author-parts/' + name + '.v1.json')));
  assert.equal(digest(Buffer.concat(parts.map(text => Buffer.from(text)))), draftSha, 'Reviewed draft bytes changed.');
  master = { lessons: parts.map(text => JSON.parse(text).lesson) }; masterSha = draftSha;
  mode = 'explicit SHA-bound six-author draft; NOT frozen or browser acceptance';
} else {
  const policy = read(path.join(root, 'app/Support/M77OnePrepositionsPhrasal.php'));
  const constant = name => { const hit = policy.match(new RegExp("public const " + name + " = '([^']+)';")); assert.ok(hit, name); return hit[1]; };
  for (const name of ['BEFORE', 'SOURCE', 'MASTER', 'CONTRACT', 'CHECKSUMS', 'BINDINGS']) {
    const relative = constant(name), pin = constant(name + '_SHA');
    assert.match(relative, /^(?:docs\/content\/|database\/content-patches\/)[A-Za-z0-9./-]+\.json$/); assert.ok(!relative.includes('..'));
    assert.match(pin, /^[a-f0-9]{64}$/); assert.notEqual(pin, '0'.repeat(64), 'M77 is not frozen.');
    const text = read(path.join(root, relative)); assert.equal(digest(Buffer.from(text)), pin);
    if (name === 'MASTER') { master = JSON.parse(text); masterSha = pin; }
  }
  assert.equal(master.status, 'frozen'); assert.equal(master.version, '1.0.0');
  assert.equal(master.base_sha, '5270494e0f999eee2a3d1fba6fb7c3f46a914481');
  mode = 'frozen final M77 package; NOT live browser acceptance';
}
assert.equal(master.lessons.length, 6);
const nativePath = path.join(factoryRoot, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php');
const native = read(nativePath), rawSha = digest(Buffer.from(native)), lfSha = digest(Buffer.from(native.replace(/\r\n/g, '\n')));
const profile = [
  { label: 'accepted-worktree', raw: ['05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e', 'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d'], lf: 'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d' },
  { label: 'independently-captured-served-before', raw: ['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186'], lf: 'a419b43d853126d88bc43683bddeb4d8efd190b08d1bfcf60944a005ad122ba7' },
].find(item => item.raw.includes(rawSha) && item.lf === lfSha);
assert.ok(profile, 'Exercise only an exact existing approved native factory.');
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
  fetch() { networkCalls++; throw Error('Network is forbidden in this offline contract.'); }, URL });
vm.runInContext(scripts[0], context, { filename: nativePath, timeout: 1000 }); listener(); assert.equal(typeof factory, 'function');
const make = (data, width = 1280) => { windowStub.innerWidth = width; const model = factory({ ...data, i18n: {
  empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.', answer: 'Правильна відповідь', score: 'Результат: :correct / :total' } });
  model.$nextTick = callback => callback(); model.init(); return model; };
const props = { selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers', rephrase: 'rephraseAnswers' };
const expected = [
  { suffix: 'PronounsDemonstratives\\PronounsDemonstrativesOneOnesTheorySeeder', slot: 8,
    selects: ['one', 'ones', 'one', 'one', 'yours', 'ones', 'some', 'this one'],
    choices: ['Той самий загублений блокнот.', 'Bottle: одну пляшку води.'],
    inputs: ["I don't want the small ones.", 'Which one is yours?'] },
  { suffix: 'PrepositionsAndPhrasalVerbs\\PrepositionsOfTimeTheorySeeder', slot: 6, selects: ['at', 'on'],
    choices: ['Прийменник не потрібен', 'Автобус вирушає о четвертій; це не тривалість поїздки.'],
    inputs: ["We don't work next Monday.", 'Do you visit your grandparents at the weekend?'] },
  { suffix: 'PrepositionsAndPhrasalVerbs\\PrepositionsOfPlaceTheorySeeder', slot: 6, selects: ['in', 'on'],
    choices: ['Кінотеатр — її місце перебування; усередині чи зовні не уточнено.', 'Місце телефона невідоме; він лише не всередині сумки.'],
    inputs: ['Where are the keys?', 'The children are at home.'] },
  { suffix: 'PrepositionsAndPhrasalVerbs\\PrepositionsOfMovementTheorySeeder', slot: 6, selects: ['onto', 'into'],
    choices: ['Жодне не підтверджує, що він уже прибув.', 'She walked along the river. — Вона йшла вздовж річки.'],
    inputs: ['We walked towards the station.', 'Do not run out of the house.'] },
  { suffix: 'PrepositionsAndPhrasalVerbs\\DependentPrepositionsTheorySeeder', slot: 6, selects: ['at', 'for'],
    choices: ['Оля поділяє думку Романа.', 'Короткі прогулянки корисні для спини.'],
    inputs: ['What does the decision depend on?', 'We need an answer to this question.'] },
  { suffix: 'PrepositionsAndPhrasalVerbs\\PhrasalVerbsBasicsTheorySeeder', slot: 6, selects: ['turn off', 'after'],
    choices: ['Значення тієї самої сполуки pick up; частка up не змінилася.', 'Літак злетів.'],
    inputs: ['Please turn it off.', 'She picked up the bag.'] },
];
const dataFor = index => master.lessons[index].blocks[expected[index].slot].data;
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
  assert.equal(remaining.length, 0, 'Use each authored token instance exactly once.'); return result;
}
function click(model, index, token) {
  model.appendInputToken('inputs', index, token); const after = model.inputAnswers[index]; model.appendInputToken('inputs', index, token);
  assert.equal(model.inputAnswers[index], after, 'A used token cannot append twice.'); assert.equal(model.isWordSuggestionOpen('inputs', index), false);
}
(async () => {
  let graded = 0, aliases = 0, options = 0, retries = 0, fullSentences = 0, manual = 0, clicked = 0, deleteReturn = 0, rebuild = 0, mixed = 0;
  const lessonResults = [], taskIds = new Set(), sentenceKeys = new Set(), settled = [];
  for (const [li, spec] of expected.entries()) {
    const lesson = master.lessons[li], block = lesson.blocks[spec.slot], data = block.data, immutable = JSON.stringify(data);
    assert.equal(lesson.identity, 'Database\\Seeders\\Page_V3\\' + spec.suffix); assert.equal(block.type, 'practice-set');
    assert.equal(lesson.blocks.filter(item => item.type === 'practice-set').length, 1);
    assert.deepEqual(data.rephrase, []); assert.equal(data.linked_practice.source, 'theory_links'); assert.deepEqual(data.linked_practice.question_types, ['4']);
    assert.equal(data.linked_practice.seeder_classes.length, 1); assert.equal(data.linked_practice.title, 'Вправа 4. Побудуй речення');
    assert.doesNotMatch([data.title, data.select_title, data.choice_title, data.input_title].join(' '), /поліглот/i);
    for (const group of ['selects', 'choices', 'inputs']) {
      assert.equal(data[group].length, spec[group].length);
      for (const [index, task] of data[group].entries()) {
        const model = make(data), answer = spec[group][index], total = data[group].length; graded++;
        assert.equal(task.answer, answer); assert.ok(task.accepted.includes(answer)); assert.equal(task.punctuation_sensitive, undefined);
        assert.equal(task.strict, undefined); assert.equal(task.m38_semantic_checks, undefined); assert.equal(task.source_index, undefined);
        if (li === 0 && group === 'selects') assert.equal(task.id, undefined, 'Transferred list tasks keep absent IDs.');
        else { assert.match(task.id, /^m77-/); assert.ok(!taskIds.has(task.id)); taskIds.add(task.id); }
        if (group !== 'inputs') {
          assert.ok(task.options.length >= 2); const keys = task.options.map(value => model.normalize(value));
          assert.equal(new Set(keys).size, keys.length, 'Displayed options cannot collide under actual normalization.'); assert.ok(!keys.includes(''));
          for (const accepted of task.accepted) assert.ok(task.options.includes(accepted), 'Every accepted selected form is visibly offered.');
        }
        for (const text of Object.values(task.feedback)) plain(text);
        for (const blank of ['', ' ', '\t\n', '\u00a0', '—']) {
          model[props[group]][index] = blank; model.check(group); assert.equal(model.isCorrect(group, index), false);
          assert.equal(model.feedbackText(group, index), 'Введи відповідь.'); assert.equal(model.scoreText(group), score(0, total));
        }
        model[props[group]][index] = 'zzzz wrong'; model.check(group); assert.equal(model.isCorrect(group, index), false); plain(model.feedbackText(group, index));
        for (const accepted of task.accepted) {
          const key = accepted.trim().toLowerCase(); plain(task.feedback[key]);
          for (const value of [accepted, '  ' + accepted.toUpperCase() + '  ', accepted.replace(/[.!?]+$/, '') + '!!!']) {
            model[props[group]][index] = value; model.check(group); assert.equal(model.isCorrect(group, index), true, `${names[li]}/${group}/${index}: ${value}`);
          }
          model[props[group]][index] = accepted; model.check(group); assert.equal(model.feedbackText(group, index), task.feedback[key]); aliases++;
        }
        for (const option of task.options || []) {
          const correct = task.accepted.some(accepted => variants.variants(accepted).some(value => model.normalize(value) === model.normalize(option)));
          model[props[group]][index] = option; model.check(group); assert.equal(model.isCorrect(group, index), correct);
          assert.equal(model.feedbackText(group, index), task.feedback[option.trim().toLowerCase()]); plain(model.feedbackText(group, index)); options++;
        }
        model[props[group]][index] = 'zzzz wrong'; model.check(group); assert.equal(model.scoreText(group), score(0, total));
        model[props[group]][index] = answer; model.check(group); assert.equal(model.scoreText(group), score(1, total));
        model.check(group); assert.equal(model.scoreText(group), score(1, total)); retries++;
        model[props[group]][index] = ''; model.check(group); assert.equal(model.scoreText(group), score(0, total));
        model.resetGroup(group); assert.equal(model.isChecked(group), false); model.check(group); assert.equal(model.scoreText(group), score(0, total));
      }
    }
    for (const [index, task] of data.inputs.entries()) {
      const answer = spec.inputs[index], original = make(data), total = data.inputs.length; fullSentences++;
      assert.match(task.after, /[\u0400-\u04ff]/); assert.doesNotMatch(task.after, /\{a\d+\}|<\/?(?:p|span|strong)\b/);
      assert.match(answer, /\s/); assert.match(answer, /^[A-Z].*[?.!]$/); const canonicalKey = original.normalize(answer);
      assert.ok(!sentenceKeys.has(canonicalKey), 'Full-sentence tasks cannot duplicate another lesson key.'); sentenceKeys.add(canonicalKey);
      const startingBank = original.inputTokenBank(index); assert.ok(startingBank.length >= 2);
      assert.equal(new Set(startingBank.map(token => token.id)).size, startingBank.length);
      for (const token of startingBank) assert.ok(token.value.split(/\s+/).length >= 1 && token.value.split(/\s+/).length <= 3);
      assert.notEqual(original.normalize(startingBank.map(token => token.value).join(' ')), canonicalKey, 'Author tokens start shuffled.');
      for (const width of [320, 1280]) {
        // Nominal size configuration only: the factory is exercised, but no layout/visibility claim is made.
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
        assert.equal(model.normalize(model.inputAnswers[index]), canonicalKey); assert.equal(model.isInputTokenBankEmpty(index), true); clicked++;
        settled.push({ lesson: names[li], task_id: task.id, nominal_width: width, token_assembled_text: model.inputAnswers[index], canonical: answer });
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
    for (const group of ['selects', 'choices', 'inputs']) {
      for (const [index, answer] of spec[group].entries()) complete[props[group]][index] = answer;
      complete.check(group); assert.equal(complete.scoreText(group), score(spec[group].length, spec[group].length));
    }
    for (const group of ['selects', 'choices', 'inputs']) {
      const others = ['selects', 'choices', 'inputs'].filter(key => key !== group).map(key => [key, complete.scoreText(key)]);
      complete.resetGroup(group); complete.check(group); assert.equal(complete.scoreText(group), score(0, spec[group].length));
      for (const [other, prior] of others) assert.equal(complete.scoreText(other), prior, 'Reset cannot change another group.');
    }
    assert.equal(JSON.stringify(data), immutable); lessonResults.push({ name: names[li], graded: spec.selects.length + spec.choices.length + spec.inputs.length, full_sentence_inputs: 2 });
  }
  const semantic = [
    [0, 'selects', 1, 'one', false], [0, 'selects', 1, 'ones', true], [0, 'selects', 4, 'your one', false],
    [0, 'selects', 5, 'ones', true], [0, 'selects', 6, 'one', false], [0, 'selects', 6, 'some', true],
    [0, 'inputs', 0, 'I do not want the small ones.', true], [0, 'inputs', 0, 'I do not want the small one.', false],
    [0, 'inputs', 1, 'Which ones are yours?', false],
    [1, 'choices', 0, '', false], [1, 'choices', 0, 'Прийменник не потрібен', true],
    [1, 'inputs', 0, "We don't work on next Monday.", false], [1, 'inputs', 0, 'We do not work next Monday.', true],
    [1, 'inputs', 1, 'Do you visit your grandparents on the weekend?', true], [1, 'inputs', 1, 'You visit your grandparents at the weekend?', false],
    [2, 'choices', 0, 'Вона обов’язково стоїть надворі, бо використала at.', false],
    [2, 'choices', 1, 'Телефон обов’язково лежить на сумці.', false],
    [2, 'inputs', 0, 'Where is the keys?', false], [2, 'inputs', 1, 'The kids are at home.', true], [2, 'inputs', 1, 'The children are to home.', false],
    [3, 'selects', 0, 'on', true], [3, 'selects', 0, 'onto', true], [3, 'selects', 0, 'into', false],
    [3, 'selects', 1, 'in', true], [3, 'selects', 1, 'into', true], [3, 'selects', 1, 'at', false],
    [3, 'inputs', 0, 'We walked toward the station.', true], [3, 'inputs', 0, 'We arrived at the station.', false],
    [3, 'inputs', 1, "Don't run out of the house.", true], [3, 'inputs', 1, 'Do not run out the house.', false],
    [4, 'selects', 0, 'for', false], [4, 'selects', 1, 'for', true],
    [4, 'inputs', 0, 'On what does the decision depend?', true], [4, 'inputs', 0, 'What does the decision depend?', false],
    [4, 'inputs', 1, 'We need an answer to this question.', true], [4, 'inputs', 1, 'We need an answer for this question.', false],
    [5, 'selects', 0, 'turned off', false], [5, 'selects', 1, 'for', false],
    [5, 'inputs', 0, 'Please turn off it.', false], [5, 'inputs', 0, 'Turn it off, please.', true],
    [5, 'inputs', 1, 'She picked the bag up.', true], [5, 'inputs', 1, 'She picked the bag.', false],
  ];
  for (const [li, group, index, answer, correct] of semantic) {
    const model = make(dataFor(li)); model[props[group]][index] = answer; model.check(group);
    assert.equal(model.isCorrect(group, index), correct, `Topic-bound ${names[li]}/${group}/${index}: ${answer}`); plain(model.feedbackText(group, index));
  }
  assert.equal(graded, 42); assert.equal(fullSentences, 12); assert.equal(manual, 24); assert.equal(clicked, 24);
  assert.equal(deleteReturn, 24); assert.equal(rebuild, 24); assert.equal(mixed, 12); assert.equal(networkCalls, 0);
  for (const [file, sha] of tracked) assert.equal(digest(fs.readFileSync(file)), sha, 'Read-only source bytes changed: ' + file);
  console.log(JSON.stringify({ kind: 'actual unchanged native theoryPracticeSet factory; offline engine contract only', mode,
    master_sha256: masterSha, factory_profile: profile.label, factory_raw_sha256: rawSha, factory_lf_sha256: lfSha,
    lessons: lessonResults, graded_tasks: graded, ungraded_demos: 0, accepted_values_checked: aliases, displayed_options_checked: options,
    wrong_correct_empty_retry_paths: retries, full_sentence_inputs: fullSentences, nominal_widths: [320, 1280],
    width_labels_are_not_layout_acceptance: true, manual_path_cases: manual, token_path_cases: clicked,
    full_sentence_mode_size_cases: manual + clicked, deletion_returns_token_paths: deleteReturn, clear_return_rebuild_paths: rebuild,
    mixed_manual_token_paths: mixed, semantic_cases: semantic.length, token_settled_texts: settled,
    token_word_suggestions_suppressed: true, independent_group_scores_and_reset: true, source_bytes_unchanged: true,
    mocked_focus_calls: focusCalls, assertions, network_calls: networkCalls, sql_calls: 0, file_writes: 0 }, null, 2));
})().catch(error => { console.error(error); process.exitCode = 1; });
