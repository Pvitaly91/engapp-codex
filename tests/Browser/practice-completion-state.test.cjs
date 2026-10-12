// Offline actual-factory acceptance only: no browser, Laravel, .env, DB or network.
const { test, after } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = path.join(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const digest = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const sha = file => digest(fs.readFileSync(path.join(root, file)));
const clone = value => JSON.parse(JSON.stringify(value));
const packagePath = 'app/Support/PracticeCompletionPackage.php';
const packageSource = read(packagePath);
function constant(name) {
  const match = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';"));
  assert.ok(match, name); assert.doesNotMatch(match[1], /PLACEHOLDER/); return match[1];
}
const masterPath = constant('MASTER'), beforePath = constant('BEFORE'), patchPath = constant('SOURCE');
for (const [file, name] of [[masterPath, 'MASTER_SHA'], [beforePath, 'BEFORE_SHA'], [patchPath, 'SOURCE_SHA']]) {
  assert.match(constant(name), /^[a-f0-9]{64}$/); assert.equal(sha(file), constant(name), file);
}
const master = JSON.parse(read(masterPath)), before = JSON.parse(read(beforePath)), patch = JSON.parse(read(patchPath));
const factoryPath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const protectedPaths = [...new Set([packagePath, masterPath, beforePath, patchPath, factoryPath,
  'public/js/english-answer-variants.js', 'public/data/english-contractions.json', 'tests/Browser/load-answer-variants.cjs',
  ...before.targets.map(target => target.definition)])];
assert.ok(protectedPaths.every(file => !/(^|\/)\.env(?:\.|$)/.test(file)));
const protectedBefore = Object.fromEntries(protectedPaths.map(file => [file, sha(file)]));
// Independent author-reviewed sentences, translations and wrong probes; not derived from grading results.
const expected = {
  'zero-conditional': ['ConditionalsZeroTheorySeeder',
    ['If I have ripe tomatoes, I make soup.', 'If I do not understand a word, I use a dictionary.'],
    ['Якщо в мене є стиглі помідори, я готую суп.', 'Якщо я не розумію якогось слова, я користуюся словником.'],
    ['If I have ripe tomatoes, I will make soup.', 'If I understand a word, I do not use a dictionary.']],
  'first-conditional': ['ConditionalsFirstTheorySeeder',
    ['If I finish the poster tonight, I will print it tomorrow.', 'What will you cook tomorrow if our guests do not eat meat?'],
    ['Якщо я закінчу плакат сьогодні ввечері, я надрукую його завтра.', 'Що ти приготуєш завтра, якщо наші гості не їдять м’яса?'],
    ['If I will finish the poster tonight, I print it tomorrow.', 'What you will cook tomorrow if our guests do not eat meat?']],
  'second-conditional': ['ConditionalsSecondTheorySeeder',
    ['If I knew sign language, I would understand this video.', 'If I were you, I would ask for a written estimate.'],
    ['Якби я знав жестову мову, я б розумів це відео.', 'На твоєму місці я б попросив письмовий кошторис.'],
    ['If I know sign language, I will understand this video.', 'If I were you, I would to ask for a written estimate.']],
  'third-conditional': ['ConditionalsThirdTheorySeeder',
    ['If we had waited for the cake to cool, it would not have broken.', 'Would the guide have stopped at the lake yesterday if we had asked?'],
    ['Якби ми дочекалися, поки пиріг охолоне, він би не розламався.', 'Чи зупинився б учора гід біля озера, якби ми попросили?'],
    ['If we had waited for the cake to cool, it would not break.', 'Would have the guide stopped at the lake yesterday if we had asked?']],
  'mixed-conditionals': ['ConditionalsMixedTheorySeeder',
    ['If we had planted trees ten years ago, we would be in the shade now.', 'If I liked spicy food, I would have tried the curry last night.'],
    ['Якби ми посадили дерева десять років тому, то зараз були б у затінку.', 'Якби я любив гостру їжу, то скуштував би карі вчора ввечері.'],
    ['If we had planted trees ten years ago, we would have been in the shade then.', 'If I liked spicy food, I would try the curry tomorrow.']],
  'unless-provided-as-long-as': ['ConditionalsUnlessProvidedAsLongAsTheorySeeder',
    ['We will meet at the library tomorrow unless it closes early.', 'You can publish the photo provided that everyone agrees.'],
    ['Ми зустрінемося завтра в бібліотеці, якщо вона не зачиниться рано.', 'Ти можеш опублікувати фото за умови, що всі погодяться.'],
    ['We will meet at the library tomorrow unless it does not close early.', 'You must publish the photo provided that everyone agrees.']],
  'in-case': ['ConditionalsInCaseTheorySeeder',
    ['I will charge my phone in case I need to call a taxi.', 'She saved the receipt in case she needed to return the shoes.'],
    ['Я заряджу телефон на випадок, якщо мені потрібно буде викликати таксі.', 'Вона зберегла чек на випадок, якщо їй потрібно буде повернути взуття.'],
    ['I will charge my phone in case I will need to call a taxi.', 'She saved the receipt in case she needed return the shoes.']],
  'wish-if-only': ['ConditionalsWishIfOnlyTheorySeeder',
    ['I wish our kitchen had a window.', 'If only I had saved the document before closing it!'],
    ['Шкода, що в нашій кухні немає вікна.', 'От якби я зберіг документ перед тим, як закрити його!'],
    ['I wish our kitchen has a window.', 'If only I saved the document before closing it!']],
  'would-rather': ['ConditionalsWouldRatherTheorySeeder',
    ['I would rather repair this chair than buy a new one.', 'We would rather the visitors left their shoes by the door tonight.'],
    ['Я б краще відремонтував цей стілець, ніж купував новий.', 'Ми б воліли, щоб відвідувачі сьогодні ввечері залишили взуття біля дверей.'],
    ['I had rather repair this chair than buy a new one.', 'We would rather leave their shoes by the door tonight.']],
  'had-better': ['ConditionalsHadBetterTheorySeeder',
    ['I had better set an alarm for tomorrow.', 'You had better not sit on this bench.'],
    ['Мені краще поставити будильник на завтра.', 'Тобі краще не сідати на цю лавку.'],
    ['I would better set an alarm for tomorrow.', 'You had better sit on this bench.']],
  'can-could': ['ModalVerbsCanCouldTheorySeeder',
    ['My little brother cannot walk yet.', 'Could you pass me the salt?'],
    ['Мій маленький брат ще не вміє ходити.', 'Не міг би ти передати мені сіль?'],
    ['My little brother can walk yet.', 'Could I pass you the salt?']],
  'may-might': ['ModalVerbsMayMightTheorySeeder',
    ['The keys might be in the kitchen.', 'May we take a photo here?'],
    ['Ключі, можливо, на кухні.', 'Можна нам зробити тут фото?'],
    ['The keys are in the kitchen.', 'May you take a photo here?']],
};
const lessons = master.lessons.map((lesson, i) => ({ ...lesson, oldTarget: before.targets[i], target: patch.targets[i],
  oldData: JSON.parse(before.targets[i].practice.body), data: JSON.parse(patch.targets[i].after.practice.body) }));
const groups = ['selects', 'choices'], props = { selects: 'selectAnswers', choices: 'choiceAnswers' };
const i18n = { empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.', answer: 'Правильна відповідь', score: 'Результат: :correct / :total' };
const key = value => String(value).trim().toLowerCase();
const score = (n, total = 2) => 'Результат: ' + n + ' / ' + total;
const scripts = [...read(factoryPath).matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)]
  .map(m => m[1]).filter(script => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(script));
assert.equal(scripts.length, 1); assert.doesNotMatch(scripts[0], /@(?:js|json|php|endphp)\b|\{\{!!?/);
const variants = require(path.join(root, 'tests/Browser/load-answer-variants.cjs'));
let listener, factory, networkCalls = 0, focusRequests = 0;
const context = vm.createContext({
  window: { EnglishAnswerVariants: variants },
  document: { addEventListener(name, value) { assert.equal(name, 'alpine:init'); listener = value; },
    querySelector(selector) { assert.match(selector, /^\[data-word-suggestion-input="inputs-\d+"\]$/); return { focus() { focusRequests++; } }; } },
  Alpine: { data(name, value) { assert.equal(name, 'theoryPracticeSet'); factory = value; } },
  fetch() { networkCalls++; throw Error('No network allowed'); }, console, URL,
});
vm.runInContext(scripts[0], context, { filename: factoryPath, timeout: 1000 }); listener();
function stateFor(lesson, inputs = lesson.data.inputs) {
  const config = clone({ ...lesson.data, inputs, i18n, wordSearchEndpoint: '' }), original = JSON.stringify(config);
  const state = factory(config); state.$nextTick = callback => callback(); state.init();
  return { state, unchanged() { assert.equal(JSON.stringify(config), original, 'No config mutation'); } };
}
function orderFor(state, index, answer) {
  const bank = state.inputTokenBank(index), words = Array.from(state.inputTokenWords(answer)), used = new Set(), order = [];
  let p = 0;
  while (p < words.length) {
    const j = bank.findIndex((token, j) => { const part = Array.from(state.inputTokenWords(token.value));
      return !used.has(j) && part.length && part.every((word, k) => words[p + k] === word); });
    assert.ok(j >= 0, 'Missing token at ' + p + ': ' + answer);
    order.push(j); used.add(j); p += state.inputTokenWords(bank[j].value).length;
  }
  assert.equal(used.size, bank.length, 'Every token instance must be necessary'); return order;
}
function build(state, index, order) { for (const j of order) state.appendInputToken('inputs', index, state.inputTokenBank(index)[j]); }
const coverage = { lessons: 12, old_controls: 72, new_inputs: 24, declared_accepted: 50, token_instances: 169,
  manual_controls: 0, token_controls: 0, question_controls: 0, repeated_word_controls: 0,
  known_wrong_full_feedback: 0, unknown_wrong_native_fallback: 0, synthetic_standalone_question_mark: 1 };

test('exact frozen inventory and BEFORE-to-AFTER preservation: 12 owners, 72 old controls, 24 inputs, 50 aliases, 169 tokens', () => {
  assert.equal(master.version, '1.0.1'); assert.equal(master.base_sha, '7d339c237060d76e9f7c7981ab71f07dfad3529d');
  assert.deepEqual(lessons.map(l => l.slug), Object.keys(expected));
  const oldIds = new Set(), ids = new Set(); let oldCount = 0, count = 0, accepted = 0, tokens = 0;
  for (const l of lessons) {
    const family = ['can-could', 'may-might'].includes(l.slug) ? 'ModalVerbs' : 'Conditionals';
    const category = family === 'ModalVerbs' ? 'modal-verbs' : 'conditionals';
    assert.equal(l.identity, 'Database\\Seeders\\Page_V3\\' + family + '\\' + expected[l.slug][0]);
    assert.deepEqual(l.ancestry, [category]); assert.equal(l.oldTarget.page.locale, 'uk');
    assert.equal(l.oldTarget.identity, l.identity); assert.equal(l.target.identity, l.identity);
    assert.equal(l.oldTarget.local_url, 'http://gramlyze.loc/theory/' + category + '/' + l.slug);
    assert.deepEqual(l.target.before.practice, l.oldTarget.practice);
    const { body: oldBody, ...oldIdentity } = l.oldTarget.practice;
    const { body: newBody, ...newIdentity } = l.target.after.practice;
    assert.deepEqual(newIdentity, oldIdentity, 'UUID/order/level/column/all identity fields'); assert.notEqual(newBody, oldBody);
    const data = clone(l.oldData);
    data.input_title = l.input_title; data.input_intro = l.input_intro; data.inputs = l.inputs;
    data.practice_completion_v1 = { version: 1, source_before_sha256: digest(oldBody), package: constant('PACKAGE') };
    assert.deepEqual(l.data, data, 'Only exact supported additions, including preservation of all unknown historical fields');
    assert.deepEqual(groups.flatMap(g => l.oldData[g].map(t => t.source_index)), [1, 2, 3, 4, 5, 6]);
    for (const g of groups) { assert.deepEqual(l.data[g], l.oldData[g]); for (const t of l.oldData[g]) {
      oldCount++; if (Object.hasOwn(t, 'id')) { assert.ok(!oldIds.has(t.id)); oldIds.add(t.id); }
    }}
    assert.equal((l.oldData.inputs || []).length, 0); assert.deepEqual(l.inputs.map(t => t.source_index), [7, 8]);
    const f = stateFor(l);
    for (const [i, t] of l.inputs.entries()) {
      count++; assert.deepEqual(Object.keys(t).sort(), ['id', 'source_index', 'before', 'after', 'answer', 'accepted', 'feedback'].sort());
      assert.match(t.id, /^pc-m46-m49-/); assert.ok(!ids.has(t.id)); ids.add(t.id);
      assert.equal(t.answer, expected[l.slug][1][i]); assert.ok(t.accepted.includes(t.answer));
      assert.equal(new Set(t.accepted).size, t.accepted.length); assert.match(t.after, /[А-Яа-яІіЇїЄєҐґ]/u); assert.doesNotMatch(t.after, /[A-Za-z]/);
      assert.ok(t.after.includes(expected[l.slug][2][i].replace(/[.!?]$/, '')));
      assert.ok(!t.before.includes(t.answer)); assert.ok(!t.after.includes(t.answer));
      const bank = f.state.inputTokenBank(i), order = orderFor(f.state, i, t.answer);
      assert.ok(bank.length > 1); assert.equal(new Set(bank.map(b => b.id)).size, bank.length);
      assert.ok(bank.every(b => f.state.inputTokenWords(b.value).length > 0), 'No bare punctuation token in real banks');
      assert.notDeepEqual(order, bank.map((_, j) => j)); assert.equal(order.map(j => bank[j].value).join(' '), t.answer);
      accepted += t.accepted.length; tokens += bank.length;
      for (const value of Object.values(t.feedback)) {
        assert.equal(typeof value, 'string'); assert.ok(value.includes(t.answer)); assert.ok(value.includes(expected[l.slug][2][i]));
      }
    }
    f.unchanged();
  }
  for (const id of ids) assert.ok(!oldIds.has(id));
  assert.equal(oldCount, 72); assert.equal(count, 24); assert.equal(accepted, 50); assert.equal(tokens, 169);
});

for (const l of lessons) {
  for (const [i, t] of l.inputs.entries()) {
    test(t.id + ': manual lifecycle, repeated wrong, declared aliases, translated feedback and explicit native unknown-wrong limit', () => {
      const f = stateFor(l), s = f.state; assert.equal(s.scoreText('inputs'), score(0)); assert.equal(s.fieldClass('inputs', i), '');
      s.check('inputs'); assert.equal(s.isEmpty('inputs', i), true); assert.equal(s.feedbackText('inputs', i), i18n.empty);
      s.inputAnswers[i] = t.answer.split(' ')[0]; s.syncInputTokenBank(i); s.check('inputs');
      assert.equal(s.isCorrect('inputs', i), false); assert.equal(s.scoreText('inputs'), score(0));
      const wrong = expected[l.slug][3][i]; assert.ok(t.feedback[key(wrong)], 'Independent known wrong probe');
      for (let n = 0; n < 2; n++) {
        s.inputAnswers[i] = wrong; s.syncInputTokenBank(i); s.check('inputs');
        assert.equal(s.isCorrect('inputs', i), false); assert.match(s.fieldClass('inputs', i), /rose/);
        assert.equal(s.scoreText('inputs'), score(0)); assert.equal(s.feedbackText('inputs', i), t.feedback[key(wrong)]);
      }
      coverage.known_wrong_full_feedback++;
      const unknown = 'xx-unmapped-' + t.id; assert.equal(t.feedback[key(unknown)], undefined);
      s.inputAnswers[i] = unknown; s.check('inputs'); assert.equal(s.isCorrect('inputs', i), false);
      assert.equal(s.feedbackText('inputs', i), i18n.incorrect + ' ' + i18n.answer + ': ' + t.accepted[0]);
      coverage.unknown_wrong_native_fallback++; const stale = s.feedbackText('inputs', i);
      for (const alias of t.accepted) for (const value of [alias, '  ' + alias.toUpperCase() + '  ', alias.replace(/'/g, '’')]) {
        s.inputAnswers[i] = value; s.syncInputTokenBank(i); s.check('inputs'); s.check('inputs');
        assert.equal(s.isCorrect('inputs', i), true, value); assert.equal(s.scoreText('inputs'), score(1));
        assert.match(s.fieldClass('inputs', i), /emerald/); assert.ok(t.feedback[key(value)], 'Finite alias feedback');
        assert.equal(s.feedbackText('inputs', i), t.feedback[key(value)]); assert.notEqual(s.feedbackText('inputs', i), stale);
      }
      s.inputAnswers[i] = ''; s.syncInputTokenBank(i); assert.equal(s.feedbackText('inputs', i), i18n.empty); assert.equal(s.scoreText('inputs'), score(0));
      s.resetGroup('inputs'); assert.equal(s.isChecked('inputs'), false); assert.equal(s.fieldClass('inputs', i), '');
      assert.ok(s.inputTokenBank(i).every(b => !b.used));
      s.inputAnswers[i] = t.answer; s.syncInputTokenBank(i); s.check('inputs'); assert.equal(s.scoreText('inputs'), score(1));
      coverage.manual_controls++; f.unchanged();
    });
    test(t.id + ': token lifecycle, duplicate instances, deletion sync, attached/separated punctuation and rebuild', () => {
      const f = stateFor(l), s = f.state, order = orderFor(s, i, t.answer);
      s.appendInputToken('inputs', i, s.inputTokenBank(i)[order[0]]);
      assert.equal(s.isCorrect('inputs', i), false); assert.equal(s.inputTokenBank(i).filter(b => b.used).length, 1);
      const partial = s.userAnswer('inputs', i); s.appendInputToken('inputs', i, s.inputTokenBank(i)[order[0]]);
      assert.equal(s.userAnswer('inputs', i), partial, 'Used token cannot be appended twice');
      build(s, i, order.slice(1)); assert.equal(s.userAnswer('inputs', i), t.answer); assert.ok(s.inputTokenBank(i).every(b => b.used));
      s.check('inputs'); s.check('inputs'); assert.equal(s.isCorrect('inputs', i), true); assert.equal(s.scoreText('inputs'), score(1));
      const repeated = [...new Set(s.inputTokenBank(i).filter((b, j, a) => a.findIndex(x => x.value === b.value) !== j).map(b => b.value))];
      if (repeated.length) {
        coverage.repeated_word_controls++; const value = repeated[0], n = s.inputTokenBank(i).filter(b => b.value === value).length;
        const escaped = value.replace(/[.*+?^$\x7b\x7d()|[\]\\]/g, '\\$&');
        s.inputAnswers[i] = t.answer.replace(new RegExp('\\b' + escaped + '\\b'), '').replace(/\s+/g, ' ').trim();
        s.syncInputTokenBank(i); assert.equal(s.inputTokenBank(i).filter(b => b.value === value && b.used).length, n - 1);
        s.inputAnswers[i] = t.answer; s.syncInputTokenBank(i); assert.ok(s.inputTokenBank(i).every(b => b.used));
      }
      s.inputAnswers[i] = t.answer.slice(0, -1); s.syncInputTokenBank(i);
      assert.equal(s.isCorrect('inputs', i), true); assert.ok(s.inputTokenBank(i).every(b => b.used));
      const last = s.inputTokenBank(i)[order.at(-1)].value;
      s.inputAnswers[i] = t.answer.slice(0, -last.length).trimEnd(); s.syncInputTokenBank(i);
      assert.equal(s.isCorrect('inputs', i), false); assert.equal(s.inputTokenBank(i).filter(b => !b.used).length, 1);
      if (t.answer.endsWith('?')) {
        coverage.question_controls++; s.inputAnswers[i] = t.answer.replace(/\?$/, ' ?'); s.syncInputTokenBank(i); s.check('inputs');
        assert.equal(s.isCorrect('inputs', i), true); assert.ok(s.inputTokenBank(i).every(b => b.used));
        assert.ok(s.feedbackText('inputs', i).includes(expected[l.slug][2][i]));
      }
      s.resetGroup('inputs'); assert.equal(s.userAnswer('inputs', i), ''); assert.equal(s.isChecked('inputs'), false);
      assert.ok(s.inputTokenBank(i).every(b => !b.used)); build(s, i, order); s.check('inputs');
      assert.equal(s.userAnswer('inputs', i), t.answer); assert.equal(s.scoreText('inputs'), score(1));
      coverage.token_controls++; f.unchanged();
    });
  }
  test(l.slug + ': 0/2 → 1/2 → 2/2; old six controls and group reset stay independent', () => {
    const f = stateFor(l), s = f.state; assert.equal(s.scoreText('inputs'), score(0));
    for (const g of groups) {
      const old = l.oldData[g]; assert.equal(s.scoreText(g), score(0, old.length));
      for (const [i, t] of old.entries()) {
        for (const alias of t.accepted || [t.answer]) { s[props[g]][i] = alias; assert.equal(s.isCorrect(g, i), true, 'Old alias ' + l.slug + '/' + t.source_index); }
        s[props[g]][i] = t.answer;
      }
      s.check(g); assert.equal(s.scoreText(g), score(old.length, old.length));
    }
    s.inputAnswers[0] = l.inputs[0].answer; s.check('inputs'); assert.equal(s.scoreText('inputs'), score(1));
    s.inputAnswers[1] = l.inputs[1].answer; s.check('inputs'); s.check('inputs'); assert.equal(s.scoreText('inputs'), score(2));
    for (const g of groups) assert.equal(s.scoreText(g), score(l.oldData[g].length, l.oldData[g].length));
    s.resetGroup('inputs'); assert.equal(s.scoreText('inputs'), score(0));
    for (const g of groups) { assert.equal(s.isChecked(g), true); assert.equal(s.scoreText(g), score(l.oldData[g].length, l.oldData[g].length)); }
    s.inputAnswers[0] = l.inputs[0].answer; s.inputAnswers[1] = l.inputs[1].answer; s.check('inputs'); s.resetGroup('selects');
    assert.equal(s.scoreText('inputs'), score(2)); assert.equal(s.scoreText('selects'), score(0, l.oldData.selects.length));
    assert.equal(s.scoreText('choices'), score(l.oldData.choices.length, l.oldData.choices.length)); f.unchanged();
  });
}
test('had better means had, rather means would; not remains semantic', () => {
  for (const [slug, i, good, bad] of [
    ['had-better', 0, ["I'd better set an alarm for tomorrow.", 'I’d better set an alarm for tomorrow.'], ['I would better set an alarm for tomorrow.']],
    ['had-better', 1, ["You'd better not sit on this bench.", 'You had better not sit on this bench.'], ['You had better sit on this bench.', 'You would better not sit on this bench.']],
    ['would-rather', 0, ["I'd rather repair this chair than buy a new one."], ['I had rather repair this chair than buy a new one.']],
    ['can-could', 0, ["My little brother can't walk yet.", 'My little brother cannot walk yet.'], ['My little brother can walk yet.']],
  ]) {
    const f = stateFor(lessons.find(l => l.slug === slug));
    for (const value of good) { f.state.inputAnswers[i] = value; assert.equal(f.state.isCorrect('inputs', i), true, value); }
    for (const value of bad) { f.state.inputAnswers[i] = value; assert.equal(f.state.isCorrect('inputs', i), false, value); } f.unchanged();
  }
});
test('synthetic bare-question-mark used-state limit is separate from 24 real attached-mark banks', () => {
  const l = lessons.find(l => l.slug === 'can-could'), item = clone(l.inputs[1]);
  assert.ok(item.before.includes('the salt?')); item.before = item.before.replace('the salt?', 'the salt / ?');
  const f = stateFor(l, [item]), s = f.state;
  for (const value of ['Could', 'you', 'pass', 'me', 'the salt', '?']) {
    const token = s.inputTokenBank(0).find(b => b.value === value && !b.used); assert.ok(token); s.appendInputToken('inputs', 0, token);
  }
  assert.equal(s.userAnswer('inputs', 0), 'Could you pass me the salt ?'); assert.equal(s.isCorrect('inputs', 0), true);
  assert.ok(s.inputTokenBank(0).every(b => b.used)); s.syncInputTokenBank(0);
  assert.equal(s.inputTokenBank(0).find(b => b.value === '?').used, false, 'Known engine limit: punctuation has no words for manual sync');
  assert.equal(s.inputTokenBank(0).filter(b => !b.used).length, 1); assert.equal(s.isCorrect('inputs', 0), true); f.unchanged();
});
after(() => {
  assert.equal(networkCalls, 0);
  assert.equal(coverage.manual_controls, 24);
  assert.equal(coverage.token_controls, 24);
  assert.equal(coverage.known_wrong_full_feedback, 24);
  assert.equal(coverage.unknown_wrong_native_fallback, 24);
  assert.equal(coverage.question_controls, 4);
  assert.equal(coverage.repeated_word_controls, 8);
  assert.deepEqual(Object.fromEntries(protectedPaths.map(file => [file, sha(file)])), protectedBefore);
  console.log('PRACTICE_COMPLETION_PROTECTED ' + JSON.stringify(protectedBefore));
  console.log('PRACTICE_COMPLETION_COVERAGE ' + JSON.stringify({ ...coverage, network_calls: networkCalls, stubbed_focus_requests: focusRequests,
    limit: 'Offline actual factory only, not browser/DOM/DB/local apply/no-JS/print. Unknown manual wrong uses native canonical-only fallback. Automatic contractions are not an author grammar whitelist.' }));
});
