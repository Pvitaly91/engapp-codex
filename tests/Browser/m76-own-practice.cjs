'use strict';
// Actual unchanged native factory, offline. NOT live browser acceptance.
// Default: final frozen package. Draft: --draft-author-sha256 SHA (ordered three-file raw-byte digest).
// Optional --factory-root ABSOLUTE_PATH tests a separately served, hash-approved unchanged factory.
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '../..');
const digest = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const tracked = new Map();
function read(full) {
  const bytes = fs.readFileSync(full); tracked.set(full, digest(bytes)); return bytes.toString('utf8');
}
const args = process.argv.slice(2);
let draftSha = null, factoryRoot = root;
while (args.length) {
  const option = args.shift(), value = args.shift();
  assert.equal(typeof value, 'string', 'Every explicit test option requires a value.');
  if (option === '--draft-author-sha256') { assert.equal(draftSha, null); assert.match(value, /^[a-f0-9]{64}$/); draftSha = value; }
  else if (option === '--factory-root') { assert.ok(path.isAbsolute(value)); factoryRoot = path.resolve(value); }
  else assert.fail('Unknown test option: ' + option);
}
let master, masterSha, mode;
if (draftSha !== null) {
  const parts = ['relative', 'each', 'reciprocal'].map(name => read(path.join(root, 'docs/content/m76-author-parts/' + name + '.v1.json')));
  assert.equal(digest(Buffer.concat(parts.map(text => Buffer.from(text)))), draftSha, 'Explicit draft bytes changed.');
  master = { lessons: parts.map(text => JSON.parse(text).lesson) }; masterSha = draftSha;
  mode = 'explicit SHA-bound author draft; NOT frozen or browser acceptance';
} else {
  const policy = read(path.join(root, 'app/Support/M76PronounRelations.php'));
  function constant(name) {
    const found = policy.match(new RegExp("public const " + name + " = '([^']+)';")); assert.ok(found, name); return found[1];
  }
  const pin = name => { const sha = constant(name); assert.match(sha, /^[a-f0-9]{64}$/); assert.notEqual(sha, '0'.repeat(64), 'M76 is not frozen.'); return sha; };
  for (const name of ['BEFORE', 'SOURCE', 'MASTER', 'CONTRACT', 'CHECKSUMS']) {
    const relative = constant(name); assert.match(relative, /^(?:docs\/content\/|database\/content-patches\/)[A-Za-z0-9./-]+\.json$/);
    assert.ok(!relative.includes('..')); const text = read(path.join(root, relative)); assert.equal(digest(Buffer.from(text)), pin(name + '_SHA'));
    if (name === 'MASTER') { master = JSON.parse(text); masterSha = digest(Buffer.from(text)); }
  }
  const policyVersion = policy.match(/'version'\s*=>\s*'([^']+)'/);
  assert.ok(policyVersion); assert.ok(['1.0.0', '1.0.1'].includes(policyVersion[1]));
  assert.equal(master.status, 'frozen'); assert.equal(master.version, policyVersion[1]);
  if (master.version === '1.0.1') {
    const oldText = read(path.join(root, 'docs/content/m76-relative-each-reciprocal.v1.0.0.json'));
    assert.equal(digest(Buffer.from(oldText)), 'acd759984a5e3129d46137ab8f99227f3944311da8b8617687f94308c27f681b');
    const predecessor = JSON.parse(oldText), restored = JSON.parse(JSON.stringify(master));
    const oldIntro = predecessor.lessons[2].blocks[0].data.intro;
    assert.equal(restored.lessons[2].blocks[0].data.intro, oldIntro.replace('<strong>', '<strong id="block-4077">'),
      'The immutable successor adds only the reviewed existing hero anchor.');
    assert.equal(restored.editorial_review.expected_http_metadata.metadata[2].hero_intro_sha256,
      digest(Buffer.from(restored.lessons[2].blocks[0].data.intro)));
    restored.version = predecessor.version; restored.lessons[2].blocks[0].data.intro = oldIntro;
    restored.editorial_review.expected_http_metadata.metadata[2].hero_intro_sha256 =
      predecessor.editorial_review.expected_http_metadata.metadata[2].hero_intro_sha256;
    assert.deepEqual(restored, predecessor, 'All own teaching/tasks, aliases, feedback and metadata outside the exact anchor receipt remain unchanged.');
  }
  assert.equal(master.base_sha, 'e0720938bad114c7e43e8e6efafa8355c1993fc1');
  mode = 'frozen final M76 package; NOT live browser acceptance';
}
assert.equal(master.lessons.length, 3);
const nativePath = path.join(factoryRoot, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php');
const native = read(nativePath), factoryRawSha = digest(Buffer.from(native)), factoryLfSha = digest(Buffer.from(native.replace(/\r\n/g, '\n')));
const profile = [
  { label: 'accepted-worktree', raw: ['05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e', 'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d'], lf: 'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d' },
  { label: 'independently-captured-served-before', raw: ['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186'], lf: 'a419b43d853126d88bc43683bddeb4d8efd190b08d1bfcf60944a005ad122ba7' },
].find(entry => entry.raw.includes(factoryRawSha) && entry.lf === factoryLfSha);
assert.ok(profile, 'Only an exact approved, unchanged native factory can be exercised.');
const scripts = [...native.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(match => match[1])
  .filter(script => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(script));
assert.equal(scripts.length, 1); assert.doesNotMatch(scripts[0], /@(?:php|json|js|endphp)\b|\{\{!!?/);
const variantsPath = path.join(factoryRoot, 'tests/Browser/load-answer-variants.cjs'); read(variantsPath);
read(path.join(factoryRoot, 'public/js/english-answer-variants.js')); read(path.join(factoryRoot, 'public/data/english-contractions.json'));
const variants = require(variantsPath);
let factory, listener, networkCalls = 0, mockedFocusCalls = 0;
const context = vm.createContext({ window: { EnglishAnswerVariants: variants, location: { origin: 'http://gramlyze.loc' } },
  document: { addEventListener(name, callback) { assert.equal(name, 'alpine:init'); listener = callback; },
    querySelector() { return { focus() { mockedFocusCalls++; }, setSelectionRange() {} }; } },
  Alpine: { data(name, callback) { assert.equal(name, 'theoryPracticeSet'); factory = callback; } },
  fetch() { networkCalls++; throw Error('Network forbidden in offline factory test.'); }, URL });
vm.runInContext(scripts[0], context, { filename: nativePath, timeout: 1000 }); listener(); assert.equal(typeof factory, 'function');
const make = data => { const model = factory({ ...data, i18n: { empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.',
  answer: 'Правильна відповідь', score: 'Результат: :correct / :total' } }); model.$nextTick = callback => callback(); model.init(); return model; };
const props = { selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers', rephrase: 'rephraseAnswers' };
const expected = [
  { suffix: 'RelativePronouns', slot: 10, count: 16, sentence: [4, 5],
    selects: ['who', 'which', 'whose', 'that', 'who'],
    choices: ['Олег телефонував цьому чоловікові; чоловік — керівник мовця.', 'Речення А'],
    inputs: ['who', 'which', 'whose', 'that', 'The man who he called is my boss.', 'I know the woman whose new bike is blue.'],
    rephrase: [null, 'My brother, who lives in Kyiv, is a doctor.', 'The dog that barks is mine.', 'The man whose car was stolen called the police.'] },
  { suffix: 'EachEveryAll', slot: 10, count: 16, sentence: [4, 5],
    selects: ['each', 'all', 'every', 'each', 'all'], choices: ['У кожного є книга; власність і назви книг не встановлено.', 'Готові четверо, один ще ні.'],
    inputs: ['Each', 'All', 'every', 'each', 'Each of us has a ticket.', 'All the books are on the table.'],
    rephrase: [null, 'Each of the books is good.', 'All students are ready.', 'The students each have books.'] },
  { suffix: 'ReciprocalPronouns', slot: 9, count: 14, sentence: [0, 1],
    selects: ['each other', 'each other', 'her', 'one another', 'herself'],
    choices: ['Учора вони не писали одне одному; про листи іншим людям нічого не сказано.', 'Це прохання взаємно допомогти; ще не сказано, чи його виконали.'],
    inputs: ['We listen to each other.', "Do they know each other's names?"],
    rephrase: ['They met each other at the party.', 'речення Б', 'She and I understand each other very well.', 'The students helped each other.', 'He talked to himself.'] },
];
const score = (n, total) => `Результат: ${n} / ${total}`;
function plain(text) { assert.equal(typeof text, 'string'); assert.match(text, /[\u0400-\u04ff]/); assert.doesNotMatch(text, /<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu); }
function order(model, index, answer) {
  const words = answer.split(/\s+/), bank = [...model.inputTokenBank(index)], chosen = [];
  for (let at = 0; at < words.length;) {
    const index = bank.findIndex(token => token.value.split(/\s+/).every((word, n) => words[at + n] === word));
    assert.notEqual(index, -1, 'Missing native token chunk: ' + words.slice(at).join(' '));
    const token = bank.splice(index, 1)[0]; chosen.push(token); at += token.value.split(/\s+/).length;
  }
  assert.equal(bank.length, 0, 'Every authored token instance must be used exactly once.'); return chosen;
}
function click(model, index, token) {
  model.appendInputToken('inputs', index, token); const value = model.inputAnswers[index];
  model.appendInputToken('inputs', index, token); assert.equal(model.inputAnswers[index], value, 'Used token cannot be appended twice.');
  assert.equal(model.isWordSuggestionOpen('inputs', index), false);
}
(async () => {
  let graded = 0, demos = 0, aliases = 0, options = 0, retries = 0, sentences = 0, manual = 0, tokens = 0, clearRebuild = 0, mixed = 0;
  const perLesson = [];
  for (const [li, spec] of expected.entries()) {
    const lesson = master.lessons[li]; assert.equal(lesson.identity, 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstratives' + spec.suffix + 'TheorySeeder');
    const data = lesson.blocks[spec.slot].data, immutable = JSON.stringify(data);
    assert.equal(lesson.blocks[spec.slot].type, 'practice-set'); assert.equal(lesson.technical_block_level, null);
    for (const group of Object.keys(props)) {
      assert.equal(data[group].length, spec[group].length); const total = spec[group].filter(value => value !== null).length;
      for (const [index, task] of data[group].entries()) {
        const model = make(data), answer = spec[group][index];
        if (answer === null) { demos++; assert.equal(model.hasAnswer(group, index), false); assert.equal(task.answer, undefined); continue; }
        graded++; assert.equal(task.answer, answer); assert.ok(task.accepted.includes(answer));
        assert.equal(task.punctuation_sensitive, undefined); assert.equal(task.strict, undefined); assert.equal(task.m38_semantic_checks, undefined);
        for (const blank of ['', ' ', '\t\n', '\u00a0', '—']) {
          model[props[group]][index] = blank; model.check(group); assert.equal(model.isCorrect(group, index), false);
          assert.equal(model.feedbackText(group, index), 'Введи відповідь.'); assert.equal(model.scoreText(group), score(0, total));
        }
        model[props[group]][index] = 'zzzz wrong'; model.check(group); assert.equal(model.isCorrect(group, index), false); plain(model.feedbackText(group, index));
        for (const accepted of task.accepted) {
          const key = accepted.trim().toLowerCase(); plain(task.feedback[key]);
          for (const value of [accepted, '  ' + accepted.toUpperCase() + '  ', accepted.replace(/[.!?]+$/, '') + '!!!']) {
            model[props[group]][index] = value; model.check(group); assert.equal(model.isCorrect(group, index), true, `${li}/${group}/${index}: ${value}`);
          }
          model[props[group]][index] = accepted; model.check(group); assert.equal(model.feedbackText(group, index), task.feedback[key]); aliases++;
        }
        for (const option of task.options || []) {
          const valid = task.accepted.some(value => variants.variants(value).some(variant => model.normalize(variant) === model.normalize(option)));
          model[props[group]][index] = option; model.check(group); assert.equal(model.isCorrect(group, index), valid);
          assert.equal(model.feedbackText(group, index), task.feedback[option.trim().toLowerCase()]); plain(model.feedbackText(group, index)); options++;
        }
        model[props[group]][index] = 'zzzz wrong'; model.check(group); assert.equal(model.isCorrect(group, index), false); assert.equal(model.scoreText(group), score(0, total));
        model[props[group]][index] = answer; model.check(group); assert.equal(model.isCorrect(group, index), true); assert.equal(model.scoreText(group), score(1, total));
        model.check(group); assert.equal(model.scoreText(group), score(1, total)); retries++;
        model[props[group]][index] = ''; model.check(group); assert.equal(model.isCorrect(group, index), false); assert.equal(model.scoreText(group), score(0, total));
        model.resetGroup(group); assert.equal(model.isChecked(group), false); model.check(group); assert.equal(model.scoreText(group), score(0, total));
      }
    }
    for (const index of spec.sentence) {
      sentences++; const task = data.inputs[index], answer = spec.inputs[index], total = data.inputs.length, model = make(data);
      const bank = [...model.inputTokenBank(index)]; assert.ok(bank.length >= 2); assert.equal(new Set(bank.map(token => token.id)).size, bank.length);
      for (const token of bank) { assert.ok(token.value.split(/\s+/).length >= 1 && token.value.split(/\s+/).length <= 3); }
      assert.notEqual(bank.map(token => token.value).join(' '), answer, 'Tokens must start shuffled.'); assert.match(task.after, /[\u0400-\u04ff]/);
      const chosen = order(model, index, answer); const untouched = data.inputs.map((_, n) => n === index ? null : JSON.stringify(model.inputTokenBank(n)));
      model.wordSearchEndpoint = '/forbidden'; model.wordSuggestionOpen[`inputs-${index}`] = true; model.wordSuggestionResults[`inputs-${index}`] = [{ word: 'not-opened' }];
      for (const [at, token] of chosen.entries()) {
        click(model, index, token); await model.searchWordSuggestions('inputs', index, { target: { value: 'word', selectionStart: 4 } });
        assert.equal(model.isWordSuggestionOpen('inputs', index), false); model.check('inputs'); assert.equal(model.isCorrect('inputs', index), at === chosen.length - 1);
      }
      assert.equal(model.inputAnswers[index], answer); assert.equal(model.isInputTokenBankEmpty(index), true); tokens++;
      const last = chosen.at(-1); model.inputAnswers[index] = chosen.slice(0, -1).map(token => token.value).join(' '); model.syncInputTokenBank(index);
      assert.equal(model.inputTokenBank(index).find(token => token.id === last.id).used, false); assert.equal(model.isCorrect('inputs', index), false);
      click(model, index, model.inputTokenBank(index).find(token => token.id === last.id)); assert.equal(model.isCorrect('inputs', index), true);
      model.inputAnswers[index] = ''; model.syncInputTokenBank(index); model.check('inputs'); assert.ok(model.inputTokenBank(index).every(token => !token.used));
      assert.equal(model.scoreText('inputs'), score(0, total)); for (const token of order(model, index, answer)) click(model, index, token);
      model.check('inputs'); assert.equal(model.isCorrect('inputs', index), true); clearRebuild++;
      model.inputAnswers[index] = answer; model.syncInputTokenBank(index); model.check('inputs'); assert.equal(model.isCorrect('inputs', index), true);
      assert.equal(model.isInputTokenBankEmpty(index), true); manual++;
      model.inputAnswers[index] = chosen[0].value; model.syncInputTokenBank(index);
      for (const token of chosen.slice(1)) click(model, index, model.inputTokenBank(index).find(item => item.id === token.id));
      assert.equal(model.inputAnswers[index], answer); assert.equal(model.isCorrect('inputs', index), true); mixed++;
      for (let n = 0; n < data.inputs.length; n++) { if (n !== index) { assert.equal(JSON.stringify(model.inputTokenBank(n)), untouched[n]); assert.equal(model.inputAnswers[n], undefined); } }
      model.resetGroup('inputs'); model.check('inputs'); assert.equal(model.isCorrect('inputs', index), false);
      assert.ok(model.inputTokenBank(index).every(token => !token.used)); assert.equal(model.scoreText('inputs'), score(0, total));
    }
    const complete = make(data);
    for (const group of Object.keys(props)) { for (const [index, answer] of spec[group].entries()) { if (answer !== null) complete[props[group]][index] = answer; }
      complete.check(group); const total = spec[group].filter(value => value !== null).length; assert.equal(complete.scoreText(group), score(total, total)); }
    for (const group of Object.keys(props)) {
      const other = Object.keys(props).filter(key => key !== group).map(key => [key, complete.scoreText(key)]);
      complete.resetGroup(group); complete.check(group); assert.equal(complete.scoreText(group), score(0, spec[group].filter(value => value !== null).length));
      for (const [key, value] of other) assert.equal(complete.scoreText(key), value, 'Retry/reset cannot change another group.');
    }
    assert.equal(JSON.stringify(data), immutable); perLesson.push({ identity: lesson.identity, graded: spec.count, full_sentence_inputs: spec.sentence });
  }
  const semantic = [
    [0, 'inputs', 3, '', false], [0, 'inputs', 3, 'Займенник не потрібен', true],
    [0, 'inputs', 4, 'The man who called is my boss.', false], [0, 'inputs', 4, 'The man he called is my manager.', true],
    [0, 'inputs', 5, 'I know the woman whose new bicycle is blue.', true], [0, 'inputs', 5, 'I know the woman who new bike is blue.', false],
    [1, 'inputs', 1, 'All of', true], [1, 'inputs', 4, 'Each of us have a ticket.', false],
    [1, 'inputs', 4, 'We each have a ticket.', true], [1, 'rephrase', 3, 'The students each has books.', false],
    [2, 'inputs', 0, 'We listen each other.', false], [2, 'inputs', 0, 'We listen to one another.', true],
    [2, 'inputs', 1, "Do they know each other's name?", false], [2, 'rephrase', 2, 'She and I understand each other very good.', false],
    [2, 'rephrase', 4, 'They talked to each other.', false], [2, 'rephrase', 4, 'He talked to himself.', true],
    [2, 'rephrase', 1, 'А', false], [2, 'rephrase', 1, 'речення А', false], [2, 'rephrase', 1, 'Б', true],
    [2, 'rephrase', 1, 'We respect each others opinions.', false], [2, 'rephrase', 1, "We respect each other's opinions.", false],
  ];
  for (const [li, group, index, answer, valid] of semantic) {
    const model = make(master.lessons[li].blocks[expected[li].slot].data); model[props[group]][index] = answer; model.check(group);
    assert.equal(model.isCorrect(group, index), valid, 'Task-bound semantic role/normalization case: ' + answer); plain(model.feedbackText(group, index));
  }
  assert.equal(graded, 46); assert.equal(demos, 2); assert.equal(retries, 46); assert.equal(sentences, 6);
  for (const count of [manual, tokens, clearRebuild, mixed]) assert.equal(count, 6);
  assert.equal(networkCalls, 0); for (const [full, sha] of tracked) assert.equal(digest(fs.readFileSync(full)), sha, 'Read-only bytes changed: ' + full);
  console.log(JSON.stringify({ kind: 'actual unchanged native theoryPracticeSet factory; NOT live browser acceptance', mode,
    master_sha256: masterSha, factory_profile: profile.label, factory_raw_sha256: factoryRawSha, factory_lf_sha256: factoryLfSha,
    lessons: perLesson, graded_tasks: graded, ungraded_demos: demos, accepted_values_checked: aliases, displayed_options_checked: options,
    wrong_correct_empty_retry_paths: retries, full_sentence_inputs: sentences, full_sentence_manual_paths: manual,
    full_sentence_token_paths: tokens, clear_return_rebuild_paths: clearRebuild, mixed_manual_token_paths: mixed,
    full_sentence_path_cases: manual + tokens + clearRebuild + mixed, semantic_cases: semantic.length,
    token_word_suggestions_suppressed: true, independent_group_scores_and_reset: true, source_bytes_unchanged: true,
    mocked_focus_calls: mockedFocusCalls, network_calls: networkCalls, sql_calls: 0, file_writes: 0 }, null, 2));
})().catch(error => { console.error(error); process.exitCode = 1; });
