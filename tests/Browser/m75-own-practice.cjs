'use strict';
// Run: node tests/Browser/m75-own-practice.cjs
// Reads the final WT master and executes the actual unchanged native factory.
// No Laravel/.env, SQL, network, browser, progress, or file writes. Not live UI acceptance.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '../..');
const tracked = new Map();
const digest = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const read = relative => {
  const full = path.join(root, relative);
  const bytes = fs.readFileSync(full);
  tracked.set(full, digest(bytes));
  return bytes.toString('utf8');
};
assert.equal(process.argv.length, 2, 'This test accepts only the explicitly frozen final WT package.');
const masterPath = 'docs/content/m75-possessive-reflexive-indefinite.v1.0.0.json';
const beforePath = 'docs/content/m75-before-source.v1.json';
const masterText = read(masterPath);
const beforeText = read(beforePath);
assert.equal(digest(Buffer.from(masterText)), '817b280b6e59bc60198a717b4b965238fa8d3ebcbc8a5181a8c909687db1f6e9');
assert.equal(digest(Buffer.from(beforeText)), '93ad3acd46790de520b4ef39d0f16d0b7300e43f77db5b111df8fe2a508c0e97');
const master = JSON.parse(masterText);
const before = JSON.parse(beforeText);
assert.equal(master.status, 'frozen');
assert.equal(master.version, '1.0.0');
assert.equal(master.base_sha, '67ebba5ed98fae05ca6022cac802870cfacf0d52');
assert.equal(before.base_sha, master.base_sha);
assert.equal(before.content_type, 'M75_source_before');
assert.equal(master.lessons.length, 3);
assert.equal(before.targets.length, 3);

const nativePath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const nativeText = read(nativePath);
const nativeRawHash = digest(Buffer.from(nativeText));
const nativeLfHash = digest(Buffer.from(nativeText.replace(/\r\n/g, '\n')));
const nativeProfile = [
  {
    label: 'accepted-M74-worktree',
    raw: ['05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e',
      'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d'],
    lf: 'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d',
  },
  {
    label: 'independent-M75-served-ROOT-before',
    raw: ['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186'],
    lf: 'a419b43d853126d88bc43683bddeb4d8efd190b08d1bfcf60944a005ad122ba7',
  },
].find(profile => profile.raw.includes(nativeRawHash) && profile.lf === nativeLfHash);
assert.ok(nativeProfile, 'Native raw and complete LF hashes must match the same independently approved paired profile; no cross-profile combination or arbitrary bytes.');
const scripts = [...nativeText.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)]
  .map(match => match[1]).filter(script => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(script));
assert.equal(scripts.length, 1);
assert.doesNotMatch(scripts[0], /@(?:php|json|js|endphp)\b|\{\{!!?/);
read('tests/Browser/load-answer-variants.cjs');
read('public/js/english-answer-variants.js');
read('public/data/english-contractions.json');
const variants = require(path.join(root, 'tests/Browser/load-answer-variants.cjs'));
let factory;
let listener;
let networkCalls = 0;
let focusCalls = 0;
const context = vm.createContext({
  window: { EnglishAnswerVariants: variants, location: { origin: 'http://gramlyze.loc' } },
  document: {
    addEventListener(name, callback) { assert.equal(name, 'alpine:init'); listener = callback; },
    querySelector() { return { focus() { focusCalls++; }, setSelectionRange() {} }; },
  },
  Alpine: { data(name, callback) { assert.equal(name, 'theoryPracticeSet'); factory = callback; } },
  fetch() { networkCalls++; throw new Error('Network is forbidden in this pure factory test.'); },
  URL,
});
vm.runInContext(scripts[0], context, { filename: nativePath, timeout: 1000 });
assert.equal(typeof listener, 'function');
listener();
assert.equal(typeof factory, 'function');
const make = data => {
  const model = factory({ ...data, i18n: {
    empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.',
    answer: 'Правильна відповідь', score: 'Результат: :correct / :total',
  } });
  model.$nextTick = callback => callback();
  model.init();
  return model;
};
const props = { selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers', rephrase: 'rephraseAnswers' };
const groups = Object.keys(props);
const score = (correct, total) => `Результат: ${correct} / ${total}`;
const expected = [
  {
    identity: 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder',
    blocks: 11, slot: 9, display: 'A1–A2',
    selects: ['my', 'mine', 'Her', 'hers', 'yours'],
    choices: ['Сестра Олени живе неподалік.', 'Телефонував друг мовця; речення не каже, що це його єдиний друг.'],
    inputs: ['my', 'mine', 'Our', 'ours', 'This is my new blue bag.', 'I need a room of my own.'],
    rephrase: [null, 'This book is mine.', 'That is her car.', 'речення Б'],
  },
  {
    identity: 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesReflexivePronounsTheorySeeder',
    blocks: 11, slot: 9, display: 'A2–B1',
    selects: ['myself', 'herself', 'Займенник не потрібен', 'ourselves', 'Займенник не потрібен'],
    choices: ['Підпис поставила саме директорка.', 'Кожен привітав іншого бігуна.'],
    inputs: ['myself', 'herself', 'themselves', 'ourselves', 'I bought this notebook for myself.', 'We carried the sofa upstairs by ourselves.'],
    rephrase: [null, 'He did it himself.', 'They enjoyed themselves.', 'We met at 5.'],
  },
  {
    identity: 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesIndefinitePronounsTheorySeeder',
    blocks: 12, slot: 10, display: 'A2–B1',
    selects: ['Someone', 'anyone', 'Nobody', 'Everyone', 'anyone'],
    choices: ['A', 'A'],
    inputs: ['Someone', 'something', 'Nobody', 'Everything', 'Everyone has their ticket.', 'Nobody knows the answer.'],
    rephrase: [null, 'Everyone is ready.', 'I want something interesting.', "She didn't say anything."],
  },
];
function plain(text) {
  assert.equal(typeof text, 'string');
  assert.match(text, /[\u0400-\u04ff]/);
  assert.doesNotMatch(text, /<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu);
}
function unicodeAlias(value) {
  return value.replace(/[A-Za-z]/g, c => String.fromCharCode(c.charCodeAt(0) + 0xfee0))
    .replace(/'/g, '\u2019').replace(/ /g, '\u00a0').normalize('NFD');
}
const isDeclaredCorrect = (model, task, answer) => task.accepted.some(accepted =>
  variants.variants(accepted).some(variant => model.normalize(variant) === model.normalize(answer)));
function chooseChunks(model, index, answer) {
  const words = answer.split(/\s+/);
  const remaining = [...model.inputTokenBank(index)];
  const selected = [];
  for (let at = 0; at < words.length;) {
    const tokenIndex = remaining.findIndex(token => {
      const chunk = token.value.split(/\s+/);
      return chunk.every((word, offset) => words[at + offset] === word);
    });
    assert.notEqual(tokenIndex, -1, `Missing exact native token chunk at ${words.slice(at).join(' ')}`);
    const token = remaining.splice(tokenIndex, 1)[0];
    selected.push(token);
    at += token.value.split(/\s+/).length;
  }
  assert.equal(remaining.length, 0, 'No extra authored tokens or collapsed repeated instances.');
  return selected;
}
function click(model, index, token) {
  model.appendInputToken('inputs', index, token);
  const value = model.inputAnswers[index];
  model.appendInputToken('inputs', index, token);
  assert.equal(model.inputAnswers[index], value, 'A used token instance cannot be appended twice.');
  assert.equal(model.isWordSuggestionOpen('inputs', index), false, 'Token clicks keep the native word-suggestion popup closed.');
}

(async () => {
  let graded = 0;
  let retained = 0;
  let added = 0;
  let demos = 0;
  let acceptedValues = 0;
  let optionsChecked = 0;
  let correctOptions = 0;
  let manual = 0;
  let tokenPaths = 0;
  let deleteReturn = 0;
  let mixedPaths = 0;
  let repeatedChecks = 0;
  const newIds = new Set();
  const results = [];
  const beforeProbe = [];
  const oldPractice = before.targets.map((target, i) => {
    assert.equal(target.identity, expected[i].identity);
    return JSON.parse(target.before.page.blocks[expected[i].slot].body);
  });
  {
    const old = make(oldPractice[0]);
    assert.equal(old.normalize('Its a beautiful day.'), old.normalize("It's a beautiful day."));
    for (const value of ['Its a beautiful day.', "It's a beautiful day."]) {
      old.rephraseAnswers[3] = value; old.check('rephrase');
      assert.equal(old.isCorrect('rephrase', 3), true);
      beforeProbe.push({ case: 'old apostrophe normalization collision', answer: value, correct: true });
    }
    const reflexive = make(oldPractice[1]);
    for (const index of [2, 4]) {
      assert.equal(oldPractice[1].selects[index].answer, '—');
      reflexive.selectAnswers[index] = '—'; reflexive.check('selects');
      assert.equal(reflexive.normalize('—'), '');
      assert.equal(reflexive.isCorrect('selects', index), false);
      assert.equal(reflexive.feedbackText('selects', index), 'Введи відповідь.');
      beforeProbe.push({ case: 'old dash is empty after normalization', index, correct: false });
    }
  }
  for (const [li, specification] of expected.entries()) {
    const lesson = master.lessons[li];
    const old = before.targets[li].before;
    assert.equal(lesson.identity, specification.identity);
    assert.equal(lesson.locale, 'uk');
    assert.equal(lesson.display_level, specification.display);
    assert.equal(lesson.technical_block_level, null);
    assert.equal(lesson.blocks.length, specification.blocks);
    assert.equal(old.page.blocks.length, specification.blocks);
    assert.equal(lesson.blocks.filter(block => block.type === 'practice-set').length, 1);
    for (const [slot, source] of old.page.blocks.entries()) {
      assert.equal(lesson.blocks[slot].existing_source_index, slot);
      assert.equal(lesson.blocks[slot].type, source.type);
      assert.equal(lesson.blocks[slot].column, source.column);
      assert.equal(lesson.blocks[slot].level ?? null, source.level ?? null);
    }
    const practice = lesson.blocks[specification.slot];
    assert.equal(practice.type, 'practice-set');
    assert.equal(practice.level ?? null, null);
    const data = practice.data;
    const immutableData = JSON.stringify(data);
    for (const group of groups) { assert.equal(data[group].length, specification[group].length); }
    assert.deepEqual(data.inputs.flatMap((task, index) => task.before.includes('/') ? [index] : []), [4, 5]);
    for (const group of groups) {
      const total = specification[group].filter(value => value !== null).length;
      for (const [index, task] of data[group].entries()) {
        const key = specification[group][index];
        const oldPosition = index < ({ selects: 5, inputs: 4, rephrase: 4 }[group] || 0);
        if (oldPosition) {
          assert.equal(Object.hasOwn(task, 'id'), Object.hasOwn(oldPractice[li][group][index], 'id'));
          assert.equal(Object.hasOwn(task, 'source_index'), Object.hasOwn(oldPractice[li][group][index], 'source_index'));
        } else {
          assert.match(task.id, /^m75-(?:possessive|reflexive|indefinite)-[a-z0-9-]+$/);
          assert.ok(!newIds.has(task.id)); newIds.add(task.id); added++;
        }
        const model = make(data);
        if (key === null) {
          demos++;
          assert.equal(model.hasAnswer(group, index), false);
          assert.equal(Object.hasOwn(task, 'answer'), false);
          assert.equal(Object.hasOwn(task, 'accepted'), false);
          continue;
        }
        graded++; if (oldPosition) retained++;
        assert.equal(task.answer, key, `Independent canonical ${li}/${group}/${index}`);
        assert.ok(task.accepted.includes(key));
        assert.equal(task.punctuation_sensitive, undefined);
        assert.equal(task.m38_semantic_checks, undefined);
        assert.equal(model.scoreText(group), score(0, total));
        assert.equal(model.isChecked(group), false);
        for (const blank of ['', ' ', '\t\n', '\u00a0', '—']) {
          model[props[group]][index] = blank; model.check(group);
          assert.equal(model.isCorrect(group, index), false);
          assert.equal(model.feedbackText(group, index), 'Введи відповідь.');
          assert.equal(model.scoreText(group), score(0, total));
        }
        for (let retry = 0; retry < 2; retry++) {
          model[props[group]][index] = 'zzzz wrong'; model.check(group);
          assert.equal(model.isCorrect(group, index), false);
          assert.equal(model.scoreText(group), score(0, total));
          plain(model.feedbackText(group, index));
        }
        for (const accepted of task.accepted) {
          assert.equal(typeof accepted, 'string'); assert.ok(accepted.trim());
          const literal = accepted.trim().toLowerCase();
          assert.equal(typeof task.feedback[literal], 'string', `Literal trim-lower feedback: ${literal}`);
          for (const value of [accepted, `  ${accepted.toUpperCase()}  `, accepted.replace(/[.!?]+$/, '') + '!!!', unicodeAlias(accepted)]) {
            model[props[group]][index] = value; model.check(group);
            assert.equal(model.isCorrect(group, index), true, `Native accepted normalization: ${value}`);
            plain(model.feedbackText(group, index));
          }
          model[props[group]][index] = accepted; model.check(group);
          assert.equal(model.feedbackText(group, index), task.feedback[literal], 'Canonical and declared aliases get authored feedback, not generic fallback.');
          acceptedValues++;
        }
        for (const [feedbackKey, text] of Object.entries(task.feedback)) {
          assert.equal(feedbackKey, feedbackKey.trim().toLowerCase()); plain(text);
        }
        if (group === 'selects' || group === 'choices') {
          assert.ok(task.options.length >= 2 && task.options.includes(key));
          assert.equal(new Set(task.options.map(option => model.normalize(option))).size, task.options.length);
          let actualCorrect = 0;
          for (const option of task.options) {
            const valid = isDeclaredCorrect(model, task, option);
            model[props[group]][index] = option; model.check(group);
            assert.equal(model.isCorrect(group, index), valid, `Shown option ${option}`);
            assert.equal(model.feedbackText(group, index), task.feedback[option.trim().toLowerCase()]);
            plain(model.feedbackText(group, index));
            actualCorrect += Number(valid); optionsChecked++;
          }
          assert.equal(actualCorrect, task.options.filter(option => task.accepted.includes(option)).length);
          assert.ok(actualCorrect >= 1 && actualCorrect < task.options.length, 'Each task retains a genuine distractor, while declared synonym options may both be correct.');
          correctOptions += actualCorrect;
        }
        model[props[group]][index] = key; model.check(group);
        assert.equal(model.isCorrect(group, index), true);
        assert.equal(model.scoreText(group), score(1, total));
        for (let again = 0; again < 2; again++) { model.check(group); repeatedChecks++; assert.equal(model.scoreText(group), score(1, total)); }
        model.resetGroup(group);
        assert.equal(model.isChecked(group), false);
        model.check(group);
        assert.equal(model.isCorrect(group, index), false);
        assert.equal(model.scoreText(group), score(0, total));
      }
    }
    for (const index of [4, 5]) {
      const task = data.inputs[index];
      const model = make(data);
      const key = specification.inputs[index];
      const bank = [...model.inputTokenBank(index)];
      assert.ok(bank.length >= 2);
      assert.equal(new Set(bank.map(token => token.id)).size, bank.length);
      for (const token of bank) { assert.ok(token.value.split(/\s+/).length >= 1 && token.value.split(/\s+/).length <= 3); }
      assert.notEqual(bank.map(token => token.value).join(' '), key, 'Initial authored chunks are shuffled.');
      assert.match(task.after, /[\u0400-\u04ff]/);
      assert.ok(!task.after.includes(key));
      const chosen = chooseChunks(model, index, key);
      const otherBanks = data.inputs.map((_, n) => n === index ? null : JSON.stringify(model.inputTokenBank(n)));
      model.wordSearchEndpoint = '/forbidden-word-search';
      model.wordSuggestionOpen[`inputs-${index}`] = true;
      model.wordSuggestionResults[`inputs-${index}`] = [{ word: 'must-not-open' }];
      for (const [at, token] of chosen.entries()) {
        click(model, index, token);
        await model.searchWordSuggestions('inputs', index, { target: { value: 'word', selectionStart: 4 } });
        assert.equal(model.isWordSuggestionOpen('inputs', index), false);
        model.check('inputs');
        assert.equal(model.isCorrect('inputs', index), at === chosen.length - 1, 'Partial chunks are not a correct full answer.');
      }
      assert.equal(model.inputAnswers[index], key);
      assert.equal(model.isInputTokenBankEmpty(index), true); tokenPaths++;
      for (let n = 0; n < data.inputs.length; n++) {
        if (n === index) continue;
        assert.equal(JSON.stringify(model.inputTokenBank(n)), otherBanks[n]);
        assert.equal(model.inputAnswers[n], undefined);
      }
      const last = chosen.at(-1);
      model.inputAnswers[index] = chosen.slice(0, -1).map(token => token.value).join(' ');
      model.syncInputTokenBank(index); model.check('inputs');
      assert.equal(model.isCorrect('inputs', index), false);
      assert.equal(model.inputTokenBank(index).find(token => token.id === last.id).used, false, 'Deleting text returns the exact chunk instance.');
      click(model, index, model.inputTokenBank(index).find(token => token.id === last.id));
      assert.equal(model.inputAnswers[index], key);
      assert.equal(model.isCorrect('inputs', index), true); deleteReturn++;
      // Remove the whole response, ensure all instances return, then rebuild entirely.
      model.inputAnswers[index] = ''; model.syncInputTokenBank(index); model.check('inputs');
      assert.equal(model.scoreText('inputs'), score(0, 6));
      assert.ok(model.inputTokenBank(index).every(token => !token.used));
      for (const token of chooseChunks(model, index, key)) click(model, index, token);
      assert.equal(model.isCorrect('inputs', index), true);
      // Manual response and native sync mark each canonical chunk as used.
      model.resetGroup('inputs');
      model.inputAnswers[index] = key; model.syncInputTokenBank(index); model.check('inputs');
      assert.equal(model.isCorrect('inputs', index), true);
      assert.equal(model.isInputTokenBankEmpty(index), true); manual++;
      // Mixed editing: type one complete chunk, then append only remaining chunks.
      model.resetGroup('inputs');
      model.inputAnswers[index] = chosen[0].value;
      model.syncInputTokenBank(index);
      assert.equal(model.inputTokenBank(index).find(token => token.id === chosen[0].id).used, true);
      for (const token of chosen.slice(1)) click(model, index, model.inputTokenBank(index).find(item => item.id === token.id));
      model.check('inputs'); assert.equal(model.isCorrect('inputs', index), true); mixedPaths++;
      model.resetGroup('inputs'); model.check('inputs');
      assert.equal(model.isCorrect('inputs', index), false);
      assert.equal(model.scoreText('inputs'), score(0, 6));
    }
    const full = make(data);
    for (const group of groups) {
      for (const [index, key] of specification[group].entries()) { if (key !== null) full[props[group]][index] = key; }
      full.check(group);
      const count = specification[group].filter(key => key !== null).length;
      assert.equal(full.scoreText(group), score(count, count));
    }
    for (const group of groups) {
      const independent = groups.filter(other => other !== group).map(other => [other, full.scoreText(other)]);
      full.resetGroup(group); full.check(group);
      assert.equal(full.scoreText(group), score(0, specification[group].filter(key => key !== null).length));
      for (const [other, value] of independent) assert.equal(full.scoreText(other), value, 'Group reset cannot change another group score.');
    }
    assert.equal(JSON.stringify(data), immutableData, 'The native factory never mutates frozen author payload.');
    results.push({ identity: lesson.identity, graded: 16, ungraded_demo: 1, source_index: specification.slot, full_sentence_indices: [4, 5] });
  }
  const own = i => master.lessons[i].blocks[expected[i].slot].data;
  {
    const data = own(0); const model = make(data); const task = data.rephrase[3];
    assert.deepEqual(task.accepted, ['речення Б', 'Б']);
    assert.match(task.original, /Its a beautiful day\./);
    assert.match(task.original, /It's a beautiful day\./);
    for (const value of ['А', 'речення А', '', ' ', '—', 'Its a beautiful day.', "It's a beautiful day."]) {
      model.rephraseAnswers[3] = value; model.check('rephrase');
      assert.equal(model.isCorrect('rephrase', 3), false);
    }
    for (const value of task.accepted) {
      model.rephraseAnswers[3] = value; model.check('rephrase');
      assert.equal(model.isCorrect('rephrase', 3), true);
      assert.equal(model.scoreText('rephrase'), score(1, 3));
      assert.equal(model.feedbackText('rephrase', 3), task.feedback[value.toLowerCase()]);
    }
    model.resetGroup('rephrase'); model.check('rephrase'); assert.equal(model.scoreText('rephrase'), score(0, 3));
  }
  {
    const data = own(1); const model = make(data);
    for (const index of [2, 4]) {
      const task = data.selects[index];
      assert.deepEqual(task.accepted, ['Займенник не потрібен']);
      for (const value of ['', ' ', '\t\n', '—']) {
        model.selectAnswers[index] = value; model.check('selects');
        assert.equal(model.isCorrect('selects', index), false);
      }
      model.selectAnswers[index] = 'Займенник не потрібен'; model.check('selects');
      assert.equal(model.isCorrect('selects', index), true);
      assert.equal(model.feedbackText('selects', index), task.feedback['займенник не потрібен']);
      model.resetGroup('selects'); model.check('selects');
      assert.equal(model.isCorrect('selects', index), false);
      assert.equal(model.scoreText('selects'), score(0, 5));
    }
    assert.match(data.selects[1].label, /самостійно/);
    assert.match(data.selects[1].label, /іншу жінку/);
    for (const [value, valid] of [['her', false], ['herself', true]]) {
      model.selectAnswers[1] = value; model.check('selects');
      assert.equal(model.isCorrect('selects', 1), valid);
      assert.equal(model.feedbackText('selects', 1), data.selects[1].feedback[value]);
    }
  }
  const semanticCases = [
    [0, 'inputs', 4, 'This is mine new blue bag.', false],
    [0, 'inputs', 5, 'I need my own room.', true],
    [0, 'inputs', 5, 'I need a room of your own.', false],
    [1, 'inputs', 4, 'I bought this notepad for myself.', true],
    [1, 'inputs', 4, 'I bought this notebook for herself.', false],
    [1, 'inputs', 5, 'We carried the couch upstairs without help.', true],
    [1, 'inputs', 5, 'We carried the sofa upstairs by themselves.', false],
    [1, 'rephrase', 3, 'We met at five.', true],
    [1, 'rephrase', 3, 'We met ourselves at 5.', false],
    [2, 'inputs', 0, 'Somebody', true],
    [2, 'inputs', 2, 'No one', true],
    [2, 'inputs', 2, 'No-one', true],
    [2, 'inputs', 2, 'Noone', false],
    [2, 'inputs', 4, 'Everybody has their ticket.', true],
    [2, 'inputs', 4, 'Everyone have their ticket.', false],
    [2, 'inputs', 5, 'No one knows the answer.', true],
    [2, 'inputs', 5, 'Nobody know the answer.', false],
    [2, 'rephrase', 3, 'She did not say anything.', true],
    [2, 'rephrase', 3, 'She said nothing.', true],
    [2, 'rephrase', 3, "She didn't say nothing.", false],
    [2, 'choices', 1, 'B', false],
  ];
  for (const [li, group, index, value, valid] of semanticCases) {
    const model = make(own(li));
    model[props[group]][index] = value; model.check(group);
    assert.equal(model.isCorrect(group, index), valid, `Task-bound semantic alternative: ${value}`);
    plain(model.feedbackText(group, index));
  }
  assert.equal(graded, 48);
  assert.equal(retained, 36);
  assert.equal(added, 12);
  assert.equal(newIds.size, 12);
  assert.equal(demos, 3);
  assert.equal(optionsChecked, 47);
  assert.equal(correctOptions, 26);
  assert.equal(acceptedValues, 68);
  assert.equal(manual, 6);
  assert.equal(tokenPaths, 6);
  assert.equal(deleteReturn, 6);
  assert.equal(mixedPaths, 6);
  assert.equal(networkCalls, 0);
  for (const [full, hash] of tracked) assert.equal(digest(fs.readFileSync(full)), hash, `Read-only source unchanged: ${full}`);
  console.log(JSON.stringify({
    kind: 'isolated actual unchanged theoryPracticeSet factory; NOT live browser acceptance',
    master_sha256: digest(Buffer.from(masterText)), before_source_sha256: digest(Buffer.from(beforeText)),
    factory_profile: nativeProfile.label, factory_raw_sha256: nativeRawHash,
    factory_lf_sha256: nativeLfHash, lessons: results, graded_own_tasks: graded,
    retained_or_corrected_functions: retained, new_tasks: added, ungraded_demos: demos,
    displayed_options_checked: optionsChecked, declared_correct_options: correctOptions, accepted_values_checked: acceptedValues,
    full_sentence_manual_paths: manual, full_sentence_token_paths: tokenPaths,
    delete_return_rebuild_paths: deleteReturn, mixed_manual_token_paths: mixedPaths,
    repeated_correct_checks: repeatedChecks, task_bound_semantic_cases: semanticCases.length,
    before_defect_probes: beforeProbe, literal_plain_feedback: true,
    independent_group_scores_and_reset: true, token_word_suggestions_suppressed: true,
    mocked_focus_calls: focusCalls, network_calls: networkCalls, sql_calls: 0, file_writes: 0,
    browser_visibility: 'not_checked_by_this_script', browser_interaction: 'not_checked_by_this_script',
  }));
})().catch(error => { console.error(error); process.exitCode = 1; });
