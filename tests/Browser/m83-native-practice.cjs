'use strict';
// Offline execution of the unchanged canonical factory. This is not live browser evidence.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const input = JSON.parse(fs.readFileSync(0, 'utf8'));
const root = path.resolve(process.argv[2]);
const hashes = new Map();
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const read = relative => { const file = path.join(root, relative), raw = fs.readFileSync(file); hashes.set(file, sha(raw)); return raw.toString('utf8'); };
const native = read('resources/views/engram/theory/blocks-v3/practice-set.blade.php');
assert.ok(['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186',
  '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e'].includes(sha(Buffer.from(native))));
const scripts = [...native.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(x => x[1]).filter(x => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(x));
assert.equal(scripts.length, 1);
assert.doesNotMatch(scripts[0], /@(?:php|json|js|endphp)\b|\{\{!!?/);
for (const file of ['tests/Browser/load-answer-variants.cjs', 'public/js/english-answer-variants.js', 'public/data/english-contractions.json']) read(file);
const variants = require(path.join(root, 'tests/Browser/load-answer-variants.cjs'));
let factory, listener, network = 0;
const win = {EnglishAnswerVariants: variants, location: {origin: 'http://gramlyze.loc'}, innerWidth: 1440};
const context = vm.createContext({window: win, URL,
  document: {addEventListener(name, fn) { assert.equal(name, 'alpine:init'); listener = fn; }, querySelector() { return {focus() {}, setSelectionRange() {}}; }},
  Alpine: {data(name, fn) { assert.equal(name, 'theoryPracticeSet'); factory = fn; }},
  fetch() { network++; throw Error('Network forbidden in an isolated native probe.'); }});
vm.runInContext(scripts[0], context, {timeout: 1000}); listener();
const make = (data, width = 1440) => { win.innerWidth = width; const model = factory({...data, i18n: {
  empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.', answer: 'Правильна відповідь', score: 'Результат: :correct / :total'}});
  model.$nextTick = fn => fn(); model.init(); return model; };
const props = {selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers', rephrase: 'rephraseAnswers'};
const grade = (m, g, i, value, expected) => { m[props[g]][i] = value; m.check(g); assert.equal(m.isCorrect(g, i), expected, `${g}[${i}] ${value}`); };
const plain = text => { assert.equal(typeof text, 'string'); assert.match(text, /[\u0400-\u04ff]/u); assert.doesNotMatch(text, /<[^>]+>|\{a\d+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z]+);/iu); };
const expected = [6, 12, 10, 11, 10, 11], fullIndices = [[0, 1], [3, 4], [0, 1, 2], [0, 1, 2], [0, 1, 2], [0, 1, 2]];
const expectedFull = [
  ['Ivan said that he was tired.', 'The implementation of the plan, which we approved, will take time.'],
  ['A small bird sings loudly.', 'Olia and I are in the garden.'],
  ['Maria loves music.', 'He plays tennis.', 'The students read books.'],
  ["She doesn't like music.", 'Does she like music?', 'Be quiet.'],
  ["Don't open the window.", "Don't be loud.", "Don't forget your keys."],
  ['I was tired, so I went to bed.', "She's rich, but she's not happy.", "I can't come because I'm busy."]
];
let tasks = 0, demos = 0, aliases = 0, shownOptions = 0, full = 0, modes = 0, deleteReturn = 0, semantic = 0;
const allData = input.lessons.map(l => { const p = l.blocks.filter(b => b.type === 'practice-set'); assert.equal(p.length, 1); return p[0].data; });
function ordered(m, index, answer) {
  const words = m.inputTokenWords(answer), bank = [...m.inputTokenBank(index)], result = [];
  for (let pos = 0; pos < words.length;) {
    const at = bank.findIndex(t => m.inputTokenWords(t.value).every((w, n) => words[pos + n] === w));
    assert.notEqual(at, -1, answer); const token = bank.splice(at, 1)[0]; result.push(token); pos += m.inputTokenWords(token.value).length;
  }
  assert.equal(bank.length, 0); return result;
}
(async () => {
  for (const [li, data] of allData.entries()) {
    const untouched = JSON.stringify(data); let local = 0;
    for (const group of Object.keys(props)) {
      const rows = data[group] || [], graded = rows.map((task, index) => ({task, index})).filter(x => typeof x.task.answer === 'string');
      for (const {task, index} of rows.map((task, index) => ({task, index})).filter(x => typeof x.task.answer !== 'string')) {
        assert.equal(group, 'rephrase'); assert.ok(task.example_original && task.example_target); demos++;
      }
      const m = make(data);
      for (const {task, index} of graded) {
        local++; tasks++;
        const visible = group === 'inputs' ? task.after : group === 'rephrase' ? task.original : task.label;
        assert.match(visible, /[\u0400-\u04ff]/u);
        assert.doesNotMatch(visible, /\{a\d+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z]+);/iu);
        for (const empty of ['', ' ', '\t\n', '\u00a0', '—']) { grade(m, group, index, empty, false); assert.equal(m.feedbackText(group, index), 'Введи відповідь.'); }
        grade(m, group, index, 'zzzz wrong', false); plain(m.feedbackText(group, index));
        const accepted = task.accepted || [task.answer]; assert.ok(accepted.includes(task.answer));
        for (const value of accepted) { grade(m, group, index, value, true); plain(m.feedbackText(group, index)); aliases++; }
        if (group === 'selects' || group === 'choices') {
          const options = task.options || data.options;
          assert.ok(options.length >= 2); const norm = options.map(x => m.normalize(x)); assert.equal(new Set(norm).size, norm.length);
          let correct = 0;
          for (const option of options) { const yes = accepted.some(x => m.normalize(x) === m.normalize(option)); grade(m, group, index, option, yes); plain(m.feedbackText(group, index)); shownOptions++; correct += Number(yes); }
          assert.equal(correct, 1, 'One visibly supported key per control.');
        }
        grade(m, group, index, task.answer, true);
      }
      if (graded.length) {
        m.check(group); assert.equal(m.scoreText(group), `Результат: ${graded.length} / ${graded.length}`);
        m.check(group); assert.equal(m.scoreText(group), `Результат: ${graded.length} / ${graded.length}`);
        m.resetGroup(group); m.check(group); assert.equal(m.scoreText(group), `Результат: 0 / ${graded.length}`);
      }
    }
    assert.equal(local, expected[li]);
    for (const [fi, index] of fullIndices[li].entries()) {
      full++; const canonical = expectedFull[li][fi]; assert.equal(data.inputs[index].answer, canonical);
      for (const width of [1440, 320]) {
        const m = make(data, width), bank = m.inputTokenBank(index);
        assert.ok(bank.length >= 2); assert.equal(new Set(bank.map(t => t.id)).size, bank.length);
        for (const token of bank) assert.ok(token.value.trim().split(/\s+/u).length <= 3);
        assert.notEqual(m.normalize(bank.map(t => t.value).join(' ')), m.normalize(canonical));
        grade(m, 'inputs', index, canonical, true); m.syncInputTokenBank(index); assert.equal(m.isInputTokenBankEmpty(index), true); modes++;
        m.inputAnswers[index] = ''; m.syncInputTokenBank(index); assert.ok(m.inputTokenBank(index).every(t => !t.used));
        m.wordSearchEndpoint = '/forbidden'; const sequence = ordered(m, index, canonical);
        for (const [n, token] of sequence.entries()) {
          m.wordSuggestionOpen[`inputs-${index}`] = true; m.appendInputToken('inputs', index, token);
          await m.searchWordSuggestions('inputs', index, {target: {value: 'word', selectionStart: 4}});
          assert.equal(m.isWordSuggestionOpen('inputs', index), false);
          const text = m.inputAnswers[index]; m.appendInputToken('inputs', index, token); assert.equal(m.inputAnswers[index], text);
          m.check('inputs'); assert.equal(m.isCorrect('inputs', index), n === sequence.length - 1);
        }
        assert.equal(m.inputAnswers[index], canonical); assert.equal(m.isInputTokenBankEmpty(index), true); modes++;
        m.inputAnswers[index] = sequence.slice(0, -1).map(t => t.value).join(' '); m.syncInputTokenBank(index);
        const last = m.inputTokenBank(index).find(t => t.id === sequence.at(-1).id); assert.equal(last.used, false);
        m.appendInputToken('inputs', index, last); grade(m, 'inputs', index, canonical, true); deleteReturn++;
        m.resetGroup('inputs'); assert.ok(m.inputTokenBank(index).every(t => !t.used));
      }
    }
    assert.equal(JSON.stringify(data), untouched);
  }
  // Independent finite semantic negatives. Grammatical alternatives may fail a stated narrow task.
  const cases = [
    [0, 'inputs', 0, 'Ivan said he was tired.', true], [0, 'inputs', 0, 'Ivan said that I was tired.', false],
    [0, 'inputs', 1, 'We approved the plan.', false],
    [1, 'inputs', 0, 'beautiful', false], [1, 'inputs', 2, 'hardly', false],
    [1, 'inputs', 3, 'A small bird sings loud.', false], [1, 'inputs', 4, 'Me and Olia are in the garden.', false],
    [1, 'rephrase', 1, 'pronoun conjunction pronoun verb preposition noun adjective', false],
    [1, 'rephrase', 2, 'interjection article adjective noun verb preposition article adjective noun', false],
    [2, 'inputs', 0, 'Music loves Maria.', false], [2, 'rephrase', 1, 'I study English.', false],
    [3, 'inputs', 0, 'She does not like music.', true], [3, 'inputs', 0, 'She likes music.', false],
    [3, 'inputs', 1, 'Does she likes music?', false], [3, 'rephrase', 2, 'You be quiet!', false],
    [4, 'inputs', 0, 'Do not open the window.', true], [4, 'inputs', 0, 'Open the window.', false],
    [4, 'inputs', 1, "Don't loud.", false], [4, 'inputs', 2, "Don't forget keys.", false],
    [5, 'inputs', 0, 'I went to bed, so I was tired.', false],
    [5, 'inputs', 1, "She is rich, but she isn't happy.", true], [5, 'inputs', 1, "She's rich and happy.", false],
    [5, 'inputs', 2, 'I cannot come because I am busy.', true], [5, 'inputs', 2, 'I can come because I am busy.', false],
    [5, 'choices', 0, 'B', false], [5, 'rephrase', 1, 'I stayed home because of it was cold.', false]
  ];
  for (const [li, group, index, value, yes] of cases) { const m = make(allData[li]); grade(m, group, index, value, yes); plain(m.feedbackText(group, index)); semantic++; }
  assert.equal(tasks, 60); assert.equal(demos, 5); assert.equal(full, 16); assert.equal(modes, 64); assert.equal(deleteReturn, 32); assert.equal(network, 0);
  for (const [file, hash] of hashes) assert.equal(sha(fs.readFileSync(file)), hash);
  console.log(JSON.stringify({tasks, demos, aliases, shown_options: shownOptions, full_inputs: full, manual_token_nominal_cases: modes,
    delete_return_cases: deleteReturn, semantic_cases: semantic, network_calls: network, db_calls: 0, file_writes: 0,
    acceptance: 'offline native factory only; no live browser or layout claim', native_sha256: sha(Buffer.from(native))}));
})().catch(error => { console.error(error); process.exitCode = 1; });
