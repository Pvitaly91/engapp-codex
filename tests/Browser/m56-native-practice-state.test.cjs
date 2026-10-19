// Offline tests of the unchanged shipped native factory. No browser, DB, Laravel, working .env or network.
const { test, after } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = process.env.M56_REPO_ROOT || path.resolve(__dirname, '../..');
const read = p => fs.readFileSync(path.join(root, p), 'utf8');
const hash = p => crypto.createHash('sha256').update(fs.readFileSync(path.join(root, p))).digest('hex');
const packagePath = 'app/Support/M56GerundSimplePassivesPackage.php';
const draft = process.env.M56_AUTHOR_DRAFT === '1';
const packageSource = fs.existsSync(path.join(root, packagePath)) ? read(packagePath) : '';
function constant(name) { const m = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';")); assert.ok(m, name); return m[1]; }
const masterPath = draft ? 'storage/app/seo-m56-local/draft/m56-author-master.draft.json' : constant('MASTER');
const master = JSON.parse(read(masterPath));
if (draft) assert.match(master.status, /^draft-/);
else { assert.equal(master.status, 'frozen'); assert.equal(hash(masterPath), constant('MASTER_SHA')); }
const factoryPath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const shipped = {
  [factoryPath]: 'ad7dd5027fb0202cf595ecc4d358e7329bcf7307ad52d93179557448171fb666',
  'public/js/english-answer-variants.js': '7000925d6de41fdb6671457bd8d1d7766b9c5b05d2c3d5fe0f1195f80666c681',
  'public/data/english-contractions.json': '2815805630fc1987314c9632a055cf3feced856372f73cb29c2a6ade80cb6491',
  'tests/Browser/load-answer-variants.cjs': '8b3f5690a441e0455d2691de3c59f85fd83e5f8d85429f5b2c5197722b14aa1e',
};
for (const [p, sha] of Object.entries(shipped)) assert.equal(hash(p), sha, 'Unchanged shipped engine: ' + p);
const protectedPaths = [...Object.keys(shipped), masterPath];
const protectedBefore = Object.fromEntries(protectedPaths.map(p => [p, hash(p)]));
const groups = ['selects', 'choices', 'inputs'];
const prop = { selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers' };
const clone = o => JSON.parse(JSON.stringify(o));
const key = s => String(s).trim().toLowerCase();
const lessons = master.lessons.map(l => ({ ...l, data: l.blocks.find(b => b.type === 'practice-set').data }));
const i18n = { empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.', answer: 'Правильна відповідь', score: 'Результат: :correct / :total' };
const score = n => 'Результат: ' + n + ' / 2';

// Independent editorial keys and complete bilingual feedback expectations: not inferred from AFTER or the factory.
const expected = {
  'm56-gerund-form-simple': ['being interrupted', 'Eva avoided being interrupted during the interview.', 'Єва уникала того, щоб її перебивали під час співбесіди.'],
  'm56-gerund-form-perfect': ['having been paid', 'The accountant denied having been paid twice.', 'Бухгалтер заперечував, що йому двічі заплатили.'],
  'm56-gerund-meaning-roles': ['Бен.', 'I object to Ben being blamed.', 'Я заперечую проти того, щоб звинувачували Бена.'],
  'm56-gerund-meaning-memory': ['Я згадую, як мені видали червоний бейдж.', 'I remember being given a red badge.', 'Я пам’ятаю, як мені видали червоний бейдж.'],
  'm56-gerund-build-memory': ['I remember being shown the map.', 'I remember being shown the map.', 'Я пам’ятаю, як мені показали карту.'],
  'm56-gerund-build-denial': ['He denied having been paid twice.', 'He denied having been paid twice.', 'Він заперечував, що йому двічі заплатили.'],
  'm56-present-form-agreement': ['are', 'The labels are checked every morning.', 'Етикетки перевіряють щоранку.'],
  'm56-present-form-v3': ['given', 'A copy is given to each customer.', 'Кожному клієнтові дають копію.'],
  'm56-present-meaning-short-answer': ['Джо.', 'Am I asked to help every Friday? — Yes, you are.', 'Мене просять допомагати щоп’ятниці? — Так.'],
  'm56-present-meaning-routine': ['Це звичайний щоденний порядок прибирання.', 'The rooms are cleaned every day.', 'Кімнати прибирають щодня.'],
  'm56-present-build-question': ['Are the tools checked every Monday?', 'Are the tools checked every Monday?', 'Інструменти перевіряють щопонеділка?'],
  'm56-present-build-negative': ['I am not paid in cash.', 'I am not paid in cash.', 'Мені не платять готівкою.'],
  'm56-past-form-singular-you': ['were', 'You were given a key when you arrived.', 'Тобі дали ключ, коли ти приїхала.'],
  'm56-past-form-v3': ['sent', 'The message was sent before lunch.', 'Повідомлення надіслали до обіду.'],
  'm56-past-meaning-sent': ['Посилку відправили у вівторок.', 'The parcel was sent on Tuesday.', 'Посилку відправили у вівторок.'],
  'm56-past-meaning-repeat': ['Повторювану заміну рушників у минулому періоді.', 'During the camp, towels were changed every day.', 'Під час табору рушники міняли щодня.'],
  'm56-past-build-question': ['Where were the tickets printed on Thursday?', 'Where were the tickets printed on Thursday?', 'Де в четвер надрукували квитки?'],
  'm56-past-build-negative': ['The forms were not signed at the meeting.', 'The forms were not signed at the meeting.', 'Бланки не підписали на зустрічі.'],
};
const aliases = {
  'm56-gerund-build-memory': ['I remember being shown the map.'],
  'm56-gerund-build-denial': ['He denied having been paid twice.'],
  'm56-present-build-question': ['Are the tools checked every Monday?'],
  'm56-present-build-negative': ['I am not paid in cash.', "I'm not paid in cash."],
  'm56-past-build-question': ['Where were the tickets printed on Thursday?'],
  'm56-past-build-negative': ['The forms were not signed at the meeting.', "The forms weren't signed at the meeting."],
};
const wrong = {
  'm56-gerund-build-memory': ['I remember showing the map.', 'I remember showing the map.', 'Я пам’ятаю, як показував карту.'],
  'm56-gerund-build-denial': ['He denied having paid twice.', 'He denied having paid twice.', 'Він заперечував, що двічі платив.'],
  'm56-present-build-question': ['Is the tools checked every Monday?', 'Are the tools checked every Monday?', 'Інструменти перевіряють щопонеділка?'],
  'm56-present-build-negative': ['I do not paid in cash.', 'I am not paid in cash.', 'Мені не платять готівкою.'],
  'm56-past-build-question': ['Where was the tickets printed on Thursday?', 'Where were the tickets printed on Thursday?', 'Де в четвер надрукували квитки?'],
  'm56-past-build-negative': ['The forms did not signed at the meeting.', 'The forms were not signed at the meeting.', 'Бланки не підписали на зустрічі.'],
};
let listener, factory, networkCalls = 0;
const source = read(factoryPath);
const scripts = [...source.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(m => m[1]).filter(s => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(s));
assert.equal(scripts.length, 1);
assert.doesNotMatch(scripts[0], /@(?:php|json|js|endphp)\b|\{\{!!?/);
const ctx = vm.createContext({
  window: { EnglishAnswerVariants: require(path.join(root, 'tests/Browser/load-answer-variants.cjs')) },
  document: { addEventListener(name, cb) { assert.equal(name, 'alpine:init'); listener = cb; }, querySelector(s) { assert.match(s, /^\[data-word-suggestion-input="inputs-\d+"\]$/); return { focus() {} }; } },
  Alpine: { data(name, cb) { assert.equal(name, 'theoryPracticeSet'); factory = cb; } },
  fetch() { networkCalls++; throw Error('Network forbidden.'); }, console, URL,
});
vm.runInContext(scripts[0], ctx, { filename: factoryPath, timeout: 1000 }); listener();
function stateFor(l) { const config = clone({ ...l.data, i18n, wordSearchEndpoint: '' }); const before = JSON.stringify(config); const s = factory(config); s.$nextTick = cb => cb(); s.init(); return { s, unchanged() { assert.equal(JSON.stringify(config), before); } }; }
function feedback(item, actual, answer = item.answer) {
  assert.equal(typeof actual, 'string');
  const row = wrong[item.id]?.[0] === answer ? wrong[item.id] : expected[item.id];
  assert.ok(actual.includes(row[2]), item.id + ': complete Ukrainian translation');
  const allowed = aliases[item.id] && row === expected[item.id] ? aliases[item.id] : [row[1]];
  assert.ok(allowed.some(sentence => actual.includes(sentence)), item.id + ': complete English sentence');
}
function orderFor(s, index, answer) {
  const bank = s.inputTokenBank(index), words = Array.from(s.inputTokenWords(answer));
  const used = new Set(), order = []; let pos = 0;
  while (pos < words.length) {
    const found = bank.findIndex((t, i) => { const ws = Array.from(s.inputTokenWords(t.value)); return !used.has(i) && ws.length && ws.every((w, n) => words[pos + n] === w); });
    assert.ok(found >= 0, 'Canonical token absent at ' + pos + ': ' + answer);
    used.add(found); order.push(found); pos += s.inputTokenWords(bank[found].value).length;
  }
  assert.equal(used.size, bank.length); return order;
}
function build(s, i, order) { for (const n of order) s.appendInputToken('inputs', i, s.inputTokenBank(i)[n]); }
const counts = { own_tasks: 18, options: 0, manual_inputs: 0, token_inputs: 0, aliases: 0, deferred_meaning_translations: 0, checked: new Set() };

test('independent M56 author scope, exact metadata, 2/2/2 and supported payload fields', () => {
  assert.equal(master.version, '1.0.1'); assert.equal(master.base_sha, 'fff71b7daa371497b3df100c2d97d108058dc175');
  assert.deepEqual(lessons.map(l => l.identity), [
    'Database\\Seeders\\Page_V3\\PassiveVoice\\InfinitivesGerund\\PassiveVoicePassiveGerundTheorySeeder',
    'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder',
    'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastSimpleTheorySeeder',
  ]);
  const levels = [['B2','B2','B2','C1','B2','B2','B2','B2','B2',null], Array(9).fill('A2'), Array(10).fill('A2')];
  const bankNames = ['PolyglotPassiveVoicePassiveGerundAllLevelsLessonSeeder','PolyglotPassiveVoicePresentSimpleAllLevelsLessonSeeder','PolyglotPassiveVoicePastSimpleAllLevelsLessonSeeder'];
  const ids = [];
  for (const [n,l] of lessons.entries()) {
    assert.equal(l.locale, 'uk'); assert.equal(l.technical_block_level, n === 0 ? 'B2' : 'A2'); assert.equal(l.display_level, n === 0 ? 'B2–C1' : 'A2');
    assert.deepEqual(l.category_ancestry, n === 0 ? ['passive-voice','passive-voice-infinitives-gerund'] : ['passive-voice']);
    assert.deepEqual(l.blocks.slice(0,-1).map(b => b.level), levels[n]);
    assert.deepEqual(l.blocks.map(b => b.existing_source_index), [...Array.from({length:levels[n].length},(_,i)=>i),null]);
    assert.equal(l.blocks.at(-1).type,'practice-set'); assert.equal(l.blocks.at(-1).level,n===0?'B2':'A2');
    assert.equal(l.data.linked_practice.source,'theory_links'); assert.deepEqual(l.data.linked_practice.question_types,['4']);
    assert.deepEqual(l.data.linked_practice.seeder_classes,['Database\\Seeders\\V3\\Polyglot\\'+bankNames[n]]);
    assert.deepEqual(groups.flatMap(g=>l.data[g].map(t=>t.source_index)),[1,2,3,4,5,6]);
    for(const g of groups){assert.equal(l.data[g].length,2);for(const t of l.data[g]){
      assert.ok(expected[t.id]);ids.push(t.id);assert.equal(t.answer,expected[t.id][0]);assert.ok(t.accepted.includes(t.answer));
      const keys=g==='inputs'?['id','source_index','before','after','answer','accepted','feedback']:['id','source_index','label',...(g==='choices'?['prompt']:[]),'options','answer','accepted','feedback'];
      assert.deepEqual(Object.keys(t).sort(),keys.sort());
      if(g==='inputs')assert.deepEqual(t.accepted,aliases[t.id]); else {assert.equal(t.options.length,3);assert.equal(new Set(t.options).size,3);}
    }}
  }
  assert.deepEqual(ids,Object.keys(expected));assert.equal(new Set(ids).size,18);
});

for(const l of lessons){
  test(l.slug+': all visible options, empty/partial/wrong/repeated wrong/retry/correct/recheck/reset',()=>{
    const f=stateFor(l),s=f.s;
    for(const g of ['selects','choices'])for(const [i,t]of l.data[g].entries()){
      s.resetGroup(g);s.check(g);assert.equal(s.feedbackText(g,i),i18n.empty);assert.equal(s.scoreText(g),score(0));
      for(const option of t.options){s.resetGroup(g);s[prop[g]][i]=option;s.check(g);s.check(g);assert.equal(s.isCorrect(g,i),t.accepted.includes(option));assert.equal(s.scoreText(g),score(t.accepted.includes(option)?1:0));assert.equal(s.feedbackText(g,i),t.feedback[key(option)]);feedback(t,s.feedbackText(g,i),option);counts.options++;}
      const bad=t.options.find(o=>!t.accepted.includes(o));s.resetGroup(g);
      for(let r=0;r<2;r++){s[prop[g]][i]=bad;s.check(g);assert.equal(s.isCorrect(g,i),false);assert.equal(s.scoreText(g),score(0));assert.match(s.fieldClass(g,i),/rose/);}
      s[prop[g]][i]=t.answer;s.check(g);s.check(g);assert.equal(s.isCorrect(g,i),true);assert.equal(s.scoreText(g),score(1));assert.match(s.fieldClass(g,i),/emerald/);feedback(t,s.feedbackText(g,i));
      s.resetGroup(g);assert.equal(s.isChecked(g),false);assert.equal(s.userAnswer(g,i),'');assert.equal(s.scoreText(g),score(0));counts.checked.add(t.id);
    }f.unchanged();
  });
  test(l.slug+': all meaning translations stay after Check with visible Ukrainian criterion',()=>{
    const f=stateFor(l),s=f.s;assert.ok(source.includes('x-show="isChecked(\'choices\') && hasAnswer(\'choices\', {{ $index }})"'));
    for(const[i,t]of l.data.choices.entries()){
      assert.match(t.label,/[А-Яа-яІіЇїЄєҐґ]/u);assert.match(t.prompt,/lang="en"/);assert.ok(!t.label.includes(expected[t.id][2]));assert.ok(!t.prompt.includes(expected[t.id][2]));
      s.resetGroup('choices');s.choiceAnswers[i]=t.answer;assert.equal(s.isChecked('choices'),false);s.check('choices');assert.equal(s.isChecked('choices'),true);feedback(t,s.feedbackText('choices',i));s.resetGroup('choices');assert.equal(s.isChecked('choices'),false);counts.deferred_meaning_translations++;
    }f.unchanged();
  });
  for(const[i,t]of l.data.inputs.entries()){
    test(t.id+': manual, independent aliases, mixed edit, full translated feedback and retry',()=>{
      const f=stateFor(l),s=f.s;assert.match(t.after,/[А-Яа-яІіЇїЄєҐґ]/u);assert.ok(t.before.includes('/'));assert.ok(!t.before.includes(t.answer));assert.ok(!t.after.includes(t.answer));
      s.check('inputs');assert.equal(s.feedbackText('inputs',i),i18n.empty);s.inputAnswers[i]=t.answer.split(/\s+/).slice(0,2).join(' ');s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),false);
      for(let r=0;r<2;r++){s.inputAnswers[i]=wrong[t.id][0];s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),false);assert.equal(s.scoreText('inputs'),score(0));assert.equal(s.feedbackText('inputs',i),t.feedback[key(wrong[t.id][0])]);feedback(t,s.feedbackText('inputs',i),wrong[t.id][0]);}
      for(const a of aliases[t.id]){s.inputAnswers[i]=a;s.syncInputTokenBank(i);s.check('inputs');s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);assert.equal(s.scoreText('inputs'),score(1));feedback(t,s.feedbackText('inputs',i));counts.aliases++;
        s.inputAnswers[i]=a.replace(/[.!?]$/,'');s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);feedback(t,s.feedbackText('inputs',i));}
      s.resetGroup('inputs');assert.equal(s.isChecked('inputs'),false);assert.equal(s.userAnswer('inputs',i),'');assert.equal(s.scoreText('inputs'),score(0));counts.manual_inputs++;counts.checked.add(t.id);f.unchanged();
    });
    test(t.id+': strict shuffled token spelling/case/multiplicity, delete/return/rebuild and mixed editing',()=>{
      const f=stateFor(l),s=f.s,order=orderFor(s,i,expected[t.id][0]);const bank=s.inputTokenBank(i),declared=t.before.split('/').map(t=>t.trim()).filter(Boolean);
      assert.deepEqual(Array.from(bank,t=>t.value),declared);assert.deepEqual([...declared].sort(),expected[t.id][0].split(/\s+/).sort());assert.equal(new Set(bank.map(t=>t.id)).size,bank.length);assert.notDeepEqual(order,Array.from(bank,(_,n)=>n));
      build(s,i,order.slice(0,1));const partial=s.userAnswer('inputs',i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),false);build(s,i,order.slice(0,1));assert.equal(s.userAnswer('inputs',i),partial);
      build(s,i,order.slice(1));assert.equal(s.userAnswer('inputs',i),expected[t.id][0]);s.check('inputs');s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);assert.equal(s.scoreText('inputs'),score(1));assert.ok(s.inputTokenBank(i).every(t=>t.used));feedback(t,s.feedbackText('inputs',i));
      const last=bank[order.at(-1)].value;s.inputAnswers[i]=t.answer.slice(0,-last.length).trimEnd();s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),false);assert.equal(s.inputTokenBank(i).filter(t=>!t.used).length,1);
      s.inputAnswers[i]=t.answer;s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);assert.ok(s.inputTokenBank(i).every(t=>t.used));
      s.resetGroup('inputs');assert.ok(s.inputTokenBank(i).every(t=>!t.used));build(s,i,order);s.check('inputs');s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);assert.equal(s.scoreText('inputs'),score(1));s.resetGroup('inputs');assert.equal(s.isChecked('inputs'),false);assert.equal(s.userAnswer('inputs',i),'');counts.token_inputs++;f.unchanged();
    });
  }
  test(l.slug+': scores are independent and repeated Check cannot double count',()=>{
    const f=stateFor(l),s=f.s;
    for(const g of groups){s[prop[g]][0]=l.data[g][0].answer;s.check(g);s.check(g);assert.equal(s.scoreText(g),score(1));assert.equal(s.isEmpty(g,1),true);s[prop[g]][1]=l.data[g][1].answer;s.check(g);s.check(g);assert.equal(s.scoreText(g),score(2));}
    s.resetGroup('inputs');assert.equal(s.scoreText('inputs'),score(0));assert.equal(s.scoreText('selects'),score(2));assert.equal(s.scoreText('choices'),score(2));s.resetGroup('choices');assert.equal(s.scoreText('selects'),score(2));f.unchanged();
  });
}
after(()=>{
  assert.equal(networkCalls,0);assert.equal(counts.options,36);assert.equal(counts.manual_inputs,6);assert.equal(counts.token_inputs,6);assert.equal(counts.aliases,8);assert.equal(counts.deferred_meaning_translations,6);assert.deepEqual([...counts.checked].sort(),Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(p=>[p,hash(p)])),protectedBefore);
  const {checked,...totals}=counts;console.log('M56_STATE_COVERAGE '+JSON.stringify({...totals,checked_task_ids:[...checked],network_calls:networkCalls,source_mode:draft?'draft-only-not-frozen':'frozen-release',master_sha256:hash(masterPath),limitation:'Offline shipped factory only; not browser/widget/DB/no-JS/print acceptance.'}));
});
