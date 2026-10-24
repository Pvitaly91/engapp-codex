'use strict';
// Isolated model replay of the existing native factory, NOT live browser/layout acceptance.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const input = JSON.parse(fs.readFileSync(0, 'utf8'));
const root = path.resolve(process.argv[2]);
const hashes = new Map();
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const read = relative => { const file = path.join(root, relative), bytes = fs.readFileSync(file); hashes.set(file, sha(bytes)); return bytes.toString('utf8'); };
const native = read('resources/views/engram/theory/blocks-v3/practice-set.blade.php');
assert.ok(['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186',
  '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e'].includes(sha(Buffer.from(native))));
const scripts = [...native.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)]
  .map(x => x[1]).filter(x => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(x));
assert.equal(scripts.length, 1); assert.doesNotMatch(scripts[0], /@(?:php|json|js|endphp)\b|\{\{!!?/);
for (const file of ['tests/Browser/load-answer-variants.cjs', 'public/js/english-answer-variants.js', 'public/data/english-contractions.json']) read(file);
const variants = require(path.join(root, 'tests/Browser/load-answer-variants.cjs'));
let factory, listener, network = 0;
const win = {EnglishAnswerVariants: variants, location: {origin: 'http://gramlyze.loc'}, innerWidth: 1440};
const context = vm.createContext({window: win, URL,
  document: {addEventListener(name, fn) { assert.equal(name, 'alpine:init'); listener = fn; }, querySelector() { return {focus() {}, setSelectionRange() {}}; }},
  Alpine: {data(name, fn) { assert.equal(name, 'theoryPracticeSet'); factory = fn; }},
  fetch() { network++; throw Error('Network forbidden in offline M84 model replay.'); }});
vm.runInContext(scripts[0], context, {timeout: 1000}); listener();
const make = (data, width = 1440) => { win.innerWidth = width; const model = factory({...data, noInputTokenFocus: true, i18n: {
  empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.', answer: 'Правильна відповідь', score: 'Результат: :correct / :total'}});
  model.$nextTick = fn => fn(); model.init(); return model; };
const props = {selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers', rephrase: 'rephraseAnswers'};
const grade = (m, group, index, value, expected) => {
  m[props[group]][index] = value; m.check(group);
  assert.equal(m.isCorrect(group, index), expected, `${group}[${index}] ${value}`);
};
const plain = text => { assert.equal(typeof text, 'string'); assert.match(text, /[\u0400-\u04ff]/u);
  assert.doesNotMatch(text, /<[^>]+>|\{a\d+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z]+);/iu); };
const expectedFull = [
  ['She often reads books.', 'I eat breakfast every morning.', 'We have dinner at home on Sundays.'],
  ['Do you speak English?', 'Does she work in a bank?', 'Did they go to the party?'],
  ['She is always late.', 'He plays the piano well.', 'They work in the office every day.'],
  ['Pick it up.', 'Turn it off.', 'Put them on.'],
  ['It was you who called me.', 'It was yesterday that she left.', 'It is help that I need.'],
  ['Our teacher has got two pets.', 'The children have not got a red ball.']
];
const expectedGraded = [10, 10, 10, 10, 10, 6];
const allData = input.lessons.map(lesson => { const p = lesson.blocks.filter(b => b.type === 'practice-set'); assert.equal(p.length, 1); return p[0].data; });
assert.equal(allData.length, 6);
let tasks = 0, demos = 0, aliases = 0, shownOptions = 0, full = 0, modes = 0, deleteReturn = 0, mixed = 0, clearRebuild = 0, semantic = 0, groupScores = 0;
const perLesson = [];
function ordered(m, index, answer) {
  const words = m.inputTokenWords(answer), bank = [...m.inputTokenBank(index)], result = [];
  for (let position = 0; position < words.length;) {
    const at = bank.findIndex(t => m.inputTokenWords(t.value).every((w, n) => words[position + n] === w));
    assert.notEqual(at, -1, answer); const token = bank.splice(at, 1)[0]; result.push(token); position += m.inputTokenWords(token.value).length;
  }
  assert.equal(bank.length, 0); return result;
}
(async () => {
  for (const [li, data] of allData.entries()) {
    const untouched = JSON.stringify(data); let local = 0, localDemos = 0;
    const independent = make(data), sizes = {};
    for (const group of Object.keys(props)) {
      const rows = data[group] || [], graded = rows.map((task, index) => ({task, index})).filter(x => typeof x.task.answer === 'string');
      sizes[group] = graded.length;
      const m = make(data);
      for (const [index, task] of rows.entries()) {
        if (typeof task.answer !== 'string') {
          assert.equal(group, 'rephrase'); assert.ok(task.example_original && task.example_target);
          assert.equal(m.hasAnswer(group, index), false); assert.equal(m.isCorrect(group, index), false);
          demos++; localDemos++; continue;
        }
        local++; tasks++;
        const visible = group === 'inputs' ? task.after : group === 'rephrase' ? task.original : task.label;
        assert.match(visible, /[\u0400-\u04ff]/u); assert.doesNotMatch(visible, /\{a\d+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z]+);/iu);
        for (const empty of ['', ' ', '\t\n', '\u00a0', '—']) {
          grade(m, group, index, empty, false); assert.equal(m.feedbackText(group, index), 'Введи відповідь.');
        }
        grade(m, group, index, 'zzzz wrong', false); plain(m.feedbackText(group, index));
        assert.ok(Array.isArray(task.accepted) && task.accepted.includes(task.answer));
        for (const accepted of task.accepted) {
          grade(m, group, index, accepted, true); plain(m.feedbackText(group, index)); aliases++;
          grade(m, group, index, ` \t${accepted.replace(/ /g, '  ')}\n`, true);
        }
        if (group === 'selects' || group === 'choices') {
          const options = task.options || (group === 'selects' ? data.options : data.choice_options || ['a', 'b']);
          assert.ok(options.length >= 2); assert.equal(new Set(options.map(x => m.normalize(x))).size, options.length);
          let correct = 0;
          for (const option of options) {
            const yes = task.accepted.some(value => variants.variants(value).some(x => m.normalize(x) === m.normalize(option)));
            grade(m, group, index, option, yes); plain(m.feedbackText(group, index)); shownOptions++; correct += Number(yes);
          }
          assert.equal(correct, 1, 'One displayed native-correct option per finite reviewed task.');
        }
        grade(m, group, index, task.answer, true); grade(independent, group, index, task.answer, true);
      }
      if (graded.length) {
        assert.equal(m.scoreText(group), `Результат: ${graded.length} / ${graded.length}`);
        m.check(group); assert.equal(m.scoreText(group), `Результат: ${graded.length} / ${graded.length}`);
        m.resetGroup(group); assert.equal(m.isChecked(group), false);
        m.check(group); assert.equal(m.scoreText(group), `Результат: 0 / ${graded.length}`); groupScores++;
      }
    }
    assert.equal(local, expectedGraded[li]); assert.equal(localDemos, li < 5 ? 1 : 0);
    for (const group of Object.keys(props)) {
      independent.resetGroup(group);
      for (const other of Object.keys(props)) {
        const correct = Object.keys(props).indexOf(other) <= Object.keys(props).indexOf(group) ? 0 : sizes[other];
        assert.equal(independent.scoreText(other), `Результат: ${correct} / ${sizes[other]}`);
      }
    }
    assert.equal(data.inputs.length, expectedFull[li].length);
    for (const [index, canonical] of expectedFull[li].entries()) {
      full++; assert.equal(data.inputs[index].answer, canonical);
      for (const width of [1440, 320]) {
        const m = make(data, width), bank = m.inputTokenBank(index);
        assert.ok(bank.length >= 2); assert.equal(new Set(bank.map(t => t.id)).size, bank.length);
        for (const token of bank) assert.ok(token.value.trim().split(/\s+/u).length <= 3);
        assert.notEqual(m.normalize(bank.map(t => t.value).join(' ')), m.normalize(canonical));
        grade(m, 'inputs', index, canonical, true); m.syncInputTokenBank(index);
        assert.equal(m.isInputTokenBankEmpty(index), true); modes++;
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
        // Real manual deletion makes the exact final token available again; rebuilding must still grade.
        m.inputAnswers[index] = sequence.slice(0, -1).map(t => t.value).join(' '); m.syncInputTokenBank(index);
        const last = m.inputTokenBank(index).find(t => t.id === sequence.at(-1).id); assert.equal(last.used, false);
        m.appendInputToken('inputs', index, last); grade(m, 'inputs', index, canonical, true); deleteReturn++;
        // Mixed manual first group / clicked remaining groups, then clear and rebuild all groups.
        m.resetGroup('inputs'); m.inputAnswers[index] = sequence[0].value; m.syncInputTokenBank(index);
        assert.equal(m.inputTokenBank(index).find(t => t.id === sequence[0].id).used, true);
        for (const token of sequence.slice(1)) m.appendInputToken('inputs', index, token);
        assert.equal(m.inputAnswers[index], canonical); grade(m, 'inputs', index, canonical, true); mixed++;
        m.inputAnswers[index] = ''; m.syncInputTokenBank(index); assert.ok(m.inputTokenBank(index).every(t => !t.used));
        for (const token of sequence) m.appendInputToken('inputs', index, token);
        assert.equal(m.inputAnswers[index], canonical); grade(m, 'inputs', index, canonical, true); clearRebuild++;
        m.resetGroup('inputs'); assert.ok(m.inputTokenBank(index).every(t => !t.used));
      }
    }
    assert.equal(JSON.stringify(data), untouched); perLesson.push({graded: local, demos: localDemos, full_inputs: data.inputs.length});
  }
  // Independent semantic probes: rejection can reflect an explicit task constraint, not a universal grammar ban.
  const cases = [
    [0, 'inputs', 0, 'She reads often books.', false], [0, 'inputs', 0, 'Often she reads books.', false],
    [0, 'inputs', 1, 'Every morning, I eat breakfast.', true], [0, 'inputs', 1, 'I eat dinner every morning.', false],
    [0, 'inputs', 2, 'On Sundays, we have dinner at home.', true], [0, 'inputs', 2, 'We have dinner at school on Sundays.', false],
    [0, 'rephrase', 2, 'Every week, we go to the supermarket.', false],
    [1, 'inputs', 0, 'You speak English?', false], [1, 'inputs', 1, 'Does she works in a bank?', false],
    [1, 'inputs', 2, 'Did they went to the party?', false], [1, 'rephrase', 1, 'She does not play tennis.', true],
    [1, 'rephrase', 1, "She doesn't plays tennis.", false], [1, 'rephrase', 2, 'They did not watch the movie.', true],
    [2, 'inputs', 0, 'She always is late.', false], [2, 'inputs', 1, 'He plays well the piano.', false],
    [2, 'inputs', 2, 'Every day, they work in the office.', false],
    [2, 'rephrase', 1, 'She speaks well French.', false], [2, 'rephrase', 2, 'Never I have been to Japan.', false],
    [2, 'rephrase', 2, 'I have never been to Japan.', false],
    [3, 'inputs', 0, 'Pick up it.', false], [3, 'inputs', 1, 'Turn off it.', false], [3, 'inputs', 2, 'Put it on.', false],
    [3, 'rephrase', 1, 'He can play guitar.', false], [3, 'rephrase', 1, 'He can to play the guitar.', false],
    [3, 'rephrase', 2, 'Throw away them.', false],
    [4, 'inputs', 0, 'It was you that called me.', true], [4, 'inputs', 0, 'It was you which called me.', false],
    [4, 'inputs', 1, 'It was she who left yesterday.', false], [4, 'inputs', 2, 'It is money that I need.', false],
    [4, 'rephrase', 1, 'Rarely does she makes mistakes.', false],
    [4, 'rephrase', 2, 'Not only did he finish early, but also he helped.', false],
    [4, 'rephrase', 2, 'Not only did he finish early, but he also helps.', false],
    [5, 'inputs', 0, "Our teacher's got two pets.", true], [5, 'inputs', 0, 'Our teacher have got two pets.', false],
    [5, 'inputs', 1, "The children haven't got a red ball.", true], [5, 'inputs', 1, 'The children have got a red ball.', false],
    [5, 'choices', 1, 'No, you have not.', false], [5, 'choices', 1, 'No, I have not.', true]
  ];
  for (const [li, group, index, value, yes] of cases) { const m = make(allData[li]); grade(m, group, index, value, yes); plain(m.feedbackText(group, index)); semantic++; }
  assert.equal(tasks, 56); assert.equal(demos, 5); assert.equal(full, 17); assert.equal(modes, 68);
  assert.equal(deleteReturn, 34); assert.equal(mixed, 34); assert.equal(clearRebuild, 34); assert.equal(network, 0);
  for (const [file, hash] of hashes) assert.equal(sha(fs.readFileSync(file)), hash);
  console.log(JSON.stringify({tasks, demos, aliases, shown_options: shownOptions, full_inputs: full,
    manual_token_nominal_cases: modes, delete_return_cases: deleteReturn, mixed_edit_cases: mixed,
    clear_rebuild_cases: clearRebuild, group_score_cases: groupScores, semantic_cases: semantic, lessons: perLesson,
    nominal_widths: [1440, 320], width_labels_are_not_layout_acceptance: true,
    network_calls: network, db_calls: 0, file_writes: 0, source_bytes_unchanged: true,
    acceptance: 'offline native factory only; no live browser or layout claim', native_sha256: sha(Buffer.from(native))}));
})().catch(error => { console.error(error); process.exitCode = 1; });
