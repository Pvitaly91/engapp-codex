'use strict';
// Source-only replay of the real native factory. This is not a browser/layout test.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const input = JSON.parse(fs.readFileSync(0, 'utf8'));
const root = path.resolve(process.argv[2]);
const sha = x => crypto.createHash('sha256').update(x).digest('hex');
const protectedFiles = new Map();
function read(p) { const absolute = path.join(root, p), bytes = fs.readFileSync(absolute); protectedFiles.set(absolute, sha(bytes)); return bytes.toString('utf8'); }
const native = read('resources/views/engram/theory/blocks-v3/practice-set.blade.php');
const focusProfiles = new Map([
  ['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186', {present: true, value: false}],
  ['05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e', {present: false, value: undefined}],
]);
const expectedFocus = focusProfiles.get(sha(Buffer.from(native)));
assert.ok(expectedFocus, 'Exact independently captured native factory profile required.');
const scripts = [...native.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(x => x[1]).filter(x => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(x));
assert.equal(scripts.length, 1); assert.doesNotMatch(scripts[0], /@(?:php|json|js|endphp)\b|\{\{!!?/);
for (const p of ['tests/Browser/load-answer-variants.cjs', 'public/js/english-answer-variants.js', 'public/data/english-contractions.json']) read(p);
const variants = require(path.join(root, 'tests/Browser/load-answer-variants.cjs'));
let factory, listener, network = 0, focusCalls = 0;
const win = {EnglishAnswerVariants: variants, location: {origin: 'http://gramlyze.loc'}, innerWidth: 1440};
const context = vm.createContext({window: win, URL,
  document: {addEventListener(name, fn) { assert.equal(name, 'alpine:init'); listener = fn; }, querySelector() { return {focus() {focusCalls++;}, setSelectionRange() {}}; }},
  Alpine: {data(name, fn) { assert.equal(name, 'theoryPracticeSet'); factory = fn; }},
  fetch() { network++; throw Error('Network forbidden in offline M93 native factory replay.'); }});
vm.runInContext(scripts[0], context, {timeout: 1000}); listener();
function make(data, width = 1440) {
  win.innerWidth = width;
  const m = factory({...data, i18n: {empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.', answer: 'Правильна відповідь', score: 'Результат: :correct / :total'}});
  m.$nextTick = fn => fn(); m.init();
  assert.equal(Object.hasOwn(m, 'noInputTokenFocus'), expectedFocus.present); assert.equal(m.noInputTokenFocus, expectedFocus.value);
  return m;
}
const props = {selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers', rephrase: 'rephraseAnswers'};
function grade(m, group, index, value, yes) { m[props[group]][index] = value; m.check(group); assert.equal(m.isCorrect(group, index), yes, `${group}[${index}]: ${value}`); }
function plain(s) { assert.equal(typeof s, 'string'); assert.match(s, /[\u0400-\u04ff]/u); assert.doesNotMatch(s, /<[^>]+>|\{a\d+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z]+);/iu); }
const expected = [
  [
    "How long will the children have been waiting outside by noon tomorrow?",
    "Who will have been teaching English for two hours by ten tomorrow?"
  ],
  [
    "By noon tomorrow, we will have been painting the fence for three hours.",
    "By 1 July, I will have been living here since 1 June."
  ],
  [
    "Those are my keys.",
    "I like these ones."
  ],
  [
    "We can meet anywhere in this park.",
    "There is nowhere to park near the station."
  ],
  [
    "Some people in our group speak Spanish.",
    "I do not know anyone here."
  ],
  [
    "Some things in this bag are heavy.",
    "I would like something cold to drink."
  ]
];
const fullIndices = [[0,1],[0,1],[0,1],[3,4],[0,1],[0,1]];
const data = input.lessons.map(l => { const p = l.blocks.filter(b => b.type === 'practice-set'); assert.equal(p.length, 1); return p[0].data; });
assert.equal(data.length, 6);
let graded = 0, aliases = 0, options = 0, nominal = 0, returned = 0, mixed = 0, rebuilt = 0, scores = 0, full = 0, demos = 0, partial = 0, rephrases = 0;
const lessons = [];
function order(m, index, answer) {
  const words = m.inputTokenWords(answer), bank = [...m.inputTokenBank(index)], out = [];
  for (let n = 0; n < words.length;) {
    const i = bank.findIndex(t => m.inputTokenWords(t.value).every((w, at) => words[n + at] === w));
    assert.notEqual(i, -1, answer); const t = bank.splice(i, 1)[0]; out.push(t); n += m.inputTokenWords(t.value).length;
  }
  assert.equal(bank.length, 0); return out;
}
(async () => {
  for (const [li, d] of data.entries()) {
    const before = JSON.stringify(d), independent = make(d), sizes = {}, own = li === 3 ? 12 : 6;
    for (const group of Object.keys(props)) {
      const rows = d[group] || []; sizes[group] = rows.filter(t => typeof t.answer === 'string').length;
      const expectedSize = li === 3 ? {selects: 3, choices: 2, inputs: 5, rephrase: 3}[group] : (group === 'rephrase' ? 0 : 2);
      assert.equal(rows.length, expectedSize);
      const m = make(d);
      for (const [index, task] of rows.entries()) {
        if (typeof task.answer !== 'string') {
          assert.equal(li, 3); assert.equal(group, 'rephrase'); assert.equal(index, 0);
          assert.equal(task.accepted, undefined); assert.equal(m.hasAnswer(group,index),false);
          assert.equal(m.isCorrect(group,index),false); assert.match(task.example_original, /[\u0400-\u04ff]/u);
          assert.match(task.example_target, /[\u0400-\u04ff]/u); demos++; continue;
        }
        graded++;
        if (group === 'inputs' && !fullIndices[li].includes(index)) { partial++; assert.equal(m.inputTokenBank(index).length,0); }
        if (group === 'rephrase') rephrases++;
        assert.equal(typeof task.answer, 'string'); assert.ok(task.accepted.includes(task.answer));
        const visible = group === 'inputs' ? (fullIndices[li].includes(index) ? task.after : task.prompt) : group === 'rephrase' ? task.original : task.label;
        assert.match(visible, /[\u0400-\u04ff]/u); assert.doesNotMatch(visible, /\{a\d+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z]+);/iu);
        for (const empty of ['', ' ', '\t\n', '\u00a0', '—']) { grade(m, group, index, empty, false); assert.equal(m.feedbackText(group, index), 'Введи відповідь.'); }
        grade(m, group, index, 'zzzz wrong', false); plain(m.feedbackText(group, index));
        for (const answer of task.accepted) {
          grade(m, group, index, answer, true); plain(m.feedbackText(group, index)); aliases++;
          grade(m, group, index, ` \t${answer.replace(/ /g, '  ')}\n`, true);
        }
        if (group === 'selects' || group === 'choices') {
          const shown = task.options || (group === 'selects' ? d.options : d.choice_options || ['a', 'b']);
          assert.ok(shown.length >= 2); assert.equal(new Set(shown.map(s => m.normalize(s))).size, shown.length);
          let correct = 0;
          for (const option of shown) {
            const yes = task.accepted.some(a => variants.variants(a).some(s => m.normalize(s) === m.normalize(option)));
            grade(m, group, index, option, yes); plain(m.feedbackText(group, index)); options++; correct += Number(yes);
          }
          assert.equal(correct, 1, 'Exactly one displayed option satisfies this visible criterion.');
        }
        grade(m, group, index, task.answer, true); grade(independent, group, index, task.answer, true);
      }
      if (rows.length) {
        assert.equal(m.scoreText(group), `Результат: ${sizes[group]} / ${sizes[group]}`);
        m.check(group); assert.equal(m.scoreText(group), `Результат: ${sizes[group]} / ${sizes[group]}`);
        m.resetGroup(group); assert.equal(m.isChecked(group), false); m.check(group);
        assert.equal(m.scoreText(group), `Результат: 0 / ${sizes[group]}`); scores++;
      }
    }
    for (const group of Object.keys(props)) {
      independent.resetGroup(group);
      for (const other of Object.keys(props)) {
        const correct = Object.keys(props).indexOf(other) <= Object.keys(props).indexOf(group) ? 0 : sizes[other];
        assert.equal(independent.scoreText(other), `Результат: ${correct} / ${sizes[other]}`);
      }
    }
    for (const [position, canonical] of expected[li].entries()) {
      const index = fullIndices[li][position];
      full++; assert.equal(d.inputs[index].answer, canonical);
      for (const width of [1440, 320]) {
        const m = make(d, width), bank = m.inputTokenBank(index);
        assert.ok(bank.length >= 2); assert.equal(new Set(bank.map(t => t.id)).size, bank.length);
        for (const token of bank) assert.ok(token.value.trim().split(/\s+/u).length <= 3);
        assert.notEqual(m.normalize(bank.map(t => t.value).join(' ')), m.normalize(canonical));
        grade(m, 'inputs', index, canonical, true); m.syncInputTokenBank(index); assert.equal(m.isInputTokenBankEmpty(index), true); nominal++;
        m.inputAnswers[index] = ''; m.syncInputTokenBank(index); assert.ok(m.inputTokenBank(index).every(t => !t.used));
        m.wordSearchEndpoint = '/forbidden'; const sequence = order(m, index, canonical);
        for (const [n, token] of sequence.entries()) {
          m.wordSuggestionOpen[`inputs-${index}`] = true; m.appendInputToken('inputs', index, token);
          await m.searchWordSuggestions('inputs', index, {target: {value: 'word', selectionStart: 4}});
          assert.equal(m.isWordSuggestionOpen('inputs', index), false);
          const text = m.inputAnswers[index]; m.appendInputToken('inputs', index, token); assert.equal(m.inputAnswers[index], text);
          m.check('inputs'); assert.equal(m.isCorrect('inputs', index), n === sequence.length - 1);
        }
        assert.equal(m.inputAnswers[index], canonical); assert.equal(m.isInputTokenBankEmpty(index), true); nominal++;
        m.inputAnswers[index] = sequence.slice(0, -1).map(t => t.value).join(' '); m.syncInputTokenBank(index);
        const last = m.inputTokenBank(index).find(t => t.id === sequence.at(-1).id); assert.equal(last.used, false);
        m.appendInputToken('inputs', index, last); grade(m, 'inputs', index, canonical, true); returned++;
        m.resetGroup('inputs'); m.inputAnswers[index] = sequence[0].value; m.syncInputTokenBank(index);
        assert.equal(m.inputTokenBank(index).find(t => t.id === sequence[0].id).used, true);
        for (const token of sequence.slice(1)) m.appendInputToken('inputs', index, token);
        assert.equal(m.inputAnswers[index], canonical); grade(m, 'inputs', index, canonical, true); mixed++;
        m.inputAnswers[index] = ''; m.syncInputTokenBank(index); assert.ok(m.inputTokenBank(index).every(t => !t.used));
        for (const token of sequence) m.appendInputToken('inputs', index, token);
        assert.equal(m.inputAnswers[index], canonical); grade(m, 'inputs', index, canonical, true); rebuilt++;
        m.resetGroup('inputs'); assert.ok(m.inputTokenBank(index).every(t => !t.used));
      }
    }
    assert.equal(JSON.stringify(d), before); lessons.push({graded: own, full_inputs: 2, counts: sizes});
  }
  const badCases = [
  [
    0,
    0,
    "How long will the children have waiting outside by noon tomorrow?"
  ],
  [
    0,
    1,
    "Who will has been teaching English for two hours by ten tomorrow?"
  ],
  [
    1,
    0,
    "By noon yesterday, we will have been painting the fence for three hours."
  ],
  [
    1,
    1,
    "By 1 July, I will have been living here for 1 June."
  ],
  [
    2,
    0,
    "That is my key."
  ],
  [
    2,
    1,
    "I like this one."
  ],
  [
    3,
    3,
    "We cannot meet anywhere in this park."
  ],
  [
    3,
    4,
    "There is everywhere to park near the station."
  ],
  [
    4,
    0,
    "Someone in our group speak Spanish."
  ],
  [
    4,
    1,
    "I know anyone here."
  ],
  [
    5,
    0,
    "Something in this bag are heavy."
  ],
  [
    5,
    1,
    "I would like cold something to drink."
  ]
];
  for (const [li, index, value] of badCases) grade(make(data[li]), 'inputs', index, value, false);
  const legacyRows = input.legacy_practices;
  assert.equal(legacyRows.length,6);
  assert.deepEqual(legacyRows.map((row,index) => row === null ? null : index), [null,null,null,3,null,null]);
  let legacyAliases=0, legacyControls=0, legacyDemos=0;
  for(const [li,row] of legacyRows.entries()) {
    if(li !== 3) { assert.equal(row,null); continue; }
    const bytes=JSON.stringify(row),m=make(row);
    for(const group of ['selects','inputs','rephrase'])for(const [index,task]of(row[group]||[]).entries()) {
      if (!task.answer && !task.accepted?.length) { assert.equal(group,'rephrase'); assert.equal(index,0); assert.equal(m.hasAnswer(group,index),false); legacyDemos++; continue; }
      legacyControls++;
      for(const answer of task.accepted||[task.answer]){grade(m,group,index,answer,true);legacyAliases++;}
      for(const empty of ['', ' ', '\\t\\n'])grade(m,group,index,empty,false);
      grade(m,group,index,'zzzz wrong',false);
    }
    assert.equal(JSON.stringify(row),bytes);
  }
  assert.equal(legacyControls,8); assert.equal(legacyDemos,1);
  assert.equal(graded, 42); assert.equal(full, 12); assert.equal(nominal, 48); assert.equal(scores, 19);
  assert.equal(demos,1); assert.equal(partial,3); assert.equal(rephrases,2);
  assert.equal(returned, 24); assert.equal(mixed, 24); assert.equal(rebuilt, 24); assert.equal(network, 0);
  assert.ok(focusCalls > 0, 'Native default focus branch is exercised against a stub only.');
  for (const [file, hash] of protectedFiles) assert.equal(sha(fs.readFileSync(file)), hash);
  console.log(JSON.stringify({kind: 'M93-offline-actual-native-factory-replay', graded, full_inputs: full, ungraded_demonstrations: demos,
    retained_partial_inputs: partial, retained_graded_rephrases: rephrases, original_demonstrations_replayed: legacyDemos, declared_aliases: aliases,
    displayed_options: options, nominal_manual_token_cases: nominal, delete_return: returned, mixed_edit: mixed, clear_rebuild: rebuilt,
    original_controls_replayed: legacyControls, original_declared_answers_replayed: legacyAliases,
    independent_group_scores: scores, wrong_context_cases: badCases.length, lessons, native_sha256: sha(Buffer.from(native)),
    nominal_widths: [1440, 320], focus_calls_are_stubs: true, width_labels_are_not_layout_acceptance: true,
    DB_connections: 0, DB_writes: 0, HTTP_requests: 0, file_writes: 0, browser_acceptance: 'not claimed', network_calls: network}));
})().catch(error => { console.error(error); process.exitCode = 1; });
