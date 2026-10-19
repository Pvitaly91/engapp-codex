// Offline tests of the unchanged shipped native factory. No browser, DB, Laravel, working .env or network.
const { test, after } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = process.env.M67_REPO_ROOT || path.resolve(__dirname, '../..');
const read = p => fs.readFileSync(path.isAbsolute(p) ? p : path.join(root, p), 'utf8');
const hash = p => crypto.createHash('sha256').update(fs.readFileSync(path.isAbsolute(p) ? p : path.join(root, p))).digest('hex');
const packagePath = 'app/Support/M67VerbPatternsCompletion.php';
const draft = process.env.M67_AUTHOR_DRAFT === '1';
const packageSource = fs.existsSync(path.join(root, packagePath)) ? read(packagePath) : '';
function constant(name) { const m = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';")); assert.ok(m, name); return m[1]; }
const masterPath = draft ? (process.env.M67_AUTHOR_PATH || 'storage/app/seo-m67-local/master-draft-v1.json') : constant('MASTER');
const master = JSON.parse(read(masterPath));
if (draft) assert.equal(master.status, 'private-draft-not-frozen');
else { assert.equal(master.status, 'frozen'); assert.equal(hash(masterPath), constant('MASTER_SHA')); }
const factoryPath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const engineProfile = process.env.M67_ENGINE_PROFILE || 'accepted-worktree';
assert.ok(['accepted-worktree','served-local'].includes(engineProfile));
const shipped = engineProfile === 'served-local' ? {
  [factoryPath]: '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e',
  'public/js/english-answer-variants.js': '082c2072ffdb6f4aac6a17feeeec87e48cd4c6848d2c84d45ed3136cd8ecc89b',
  'public/data/english-contractions.json': '9a5d411c3ad87774f7c3d2e79bcd4ac7aebefad8ae7c1a43efbd327a17ca8baf',
  'tests/Browser/load-answer-variants.cjs': 'b90f5df05b6743ff6bb7ab31e27fad3d968cd95832e5f7a626c304914b7eab49',
} : {
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

// Independent finite editorial fixtures; not answers inferred from the runtime projection.
const expected = {
  "m67-infinitive-form-1": [
    "to book",
    "Did Iryna decide to book the tickets?",
    "Ірина вирішила забронювати квитки?"
  ],
  "m67-infinitive-form-2": [
    "he arrives",
    "We hope he arrives before eight.",
    "Ми сподіваємося, що він прибуде до восьмої."
  ],
  "m67-infinitive-meaning-1": [
    "Забрати береться Назар; сестра — адресат",
    "Nazar promised his sister that he would collect the parcel.",
    "Назар пообіцяв сестрі, що він забере посилку."
  ],
  "m67-infinitive-meaning-2": [
    "Гід запропонував допомогу; сумки понесли ми",
    "The guide offered to carry our bags, but we thanked him and carried them ourselves.",
    "Гід запропонував понести наші сумки, але ми подякували йому й понесли їх самі."
  ],
  "m67-infinitive-build-1": [
    "They decided not to sell the house.",
    "They decided not to sell the house.",
    "Вони вирішили не продавати будинок."
  ],
  "m67-infinitive-build-2": [
    "I need you to check the address.",
    "I need you to check the address.",
    "Мені потрібно, щоб ти перевірив адресу."
  ],
  "m67-meaning-form-1": [
    "drinking",
    "He stopped drinking coffee last month.",
    "Минулого місяця він припинив пити каву."
  ],
  "m67-meaning-form-2": [
    "restarting",
    "Try restarting the router.",
    "Спробуй перезапустити маршрутизатор."
  ],
  "m67-meaning-meaning-1": [
    "A повідомляє про виконання; B просить підтвердження",
    "Danylo remembered to turn off the oven.",
    "Данило не забув вимкнути духовку."
  ],
  "m67-meaning-meaning-2": [
    "Опівдні дію пропущено; о другій форму надіслано",
    "I forgot to send the form at noon, but I sent it at two.",
    "Я забув надіслати форму опівдні, але надіслав її о другій."
  ],
  "m67-meaning-build-1": [
    "I regret not keeping the receipt.",
    "I regret not keeping the receipt.",
    "Я шкодую, що не зберіг квитанцію."
  ],
  "m67-meaning-build-2": [
    "We regret to inform you that the workshop is full.",
    "We regret to inform you that the workshop is full.",
    "На жаль, повідомляємо вам, що на майстер-класі немає вільних місць."
  ],
  "m67-usedto-form-1": [
    "use to",
    "Did Mark use to work on Saturdays?",
    "Марк раніше працював щосуботи?"
  ],
  "m67-usedto-form-2": [
    "got used to",
    "After two short shifts, Inna got used to working with the new scanner; it felt normal to her.",
    "Після двох коротких змін Інна звикла працювати з новим сканером; це стало для неї звичним."
  ],
  "m67-usedto-meaning-1": [
    "Тривалі наради були звичними, але не подобалися",
    "At my previous job, I was used to long meetings, but I disliked them.",
    "На попередній роботі тривалі наради були для мене звичними, але вони мені не подобалися."
  ],
  "m67-usedto-meaning-2": [
    "Щоденна поїздка стала лише поїздкою в дощову погоду",
    "I used to take the tram every day, but now I usually cycle and only take it when it rains.",
    "Раніше я щодня їздив трамваєм, а тепер зазвичай їжджу велосипедом і користуюся трамваєм лише коли йде дощ."
  ],
  "m67-usedto-build-1": [
    "We are not used to the damp climate yet.",
    "We are not used to the damp climate yet.",
    "Ми ще не звикли до вологого клімату."
  ],
  "m67-usedto-build-2": [
    "Did Artem get used to the new timetable last week?",
    "Did Artem get used to the new timetable last week?",
    "Чи звик Артем до нового розкладу минулого тижня?"
  ]
};
const aliases = {
  "m67-infinitive-build-1": [
    "They decided not to sell the house.",
    "They decided to not sell the house."
  ],
  "m67-infinitive-build-2": [
    "I need you to check the address."
  ],
  "m67-meaning-build-1": [
    "I regret not keeping the receipt.",
    "I regret not having kept the receipt."
  ],
  "m67-meaning-build-2": [
    "We regret to inform you that the workshop is full."
  ],
  "m67-usedto-build-1": [
    "We are not used to the damp climate yet.",
    "We aren't used to the damp climate yet.",
    "We're not used to the damp climate yet."
  ],
  "m67-usedto-build-2": [
    "Did Artem get used to the new timetable last week?"
  ]
};
const wrong = {
  "m67-infinitive-build-1": "They decided not to sold the house.",
  "m67-infinitive-build-2": "I need you check the address.",
  "m67-meaning-build-1": "I regret not to keep the receipt.",
  "m67-meaning-build-2": "We regret inform you that the workshop is full.",
  "m67-usedto-build-1": "We are not use to the damp climate yet.",
  "m67-usedto-build-2": "Did Artem get use to the new timetable last week?"
};
const fullRelativeRejected = {
  "m67-infinitive-build-1": [
    "They did not decide to sell the house."
  ],
  "m67-infinitive-build-2": [
    "I want you to check the address."
  ],
  "m67-meaning-build-1": [
    "I do not regret keeping the receipt."
  ],
  "m67-meaning-build-2": [
    "We regret informing you that the workshop is full."
  ],
  "m67-usedto-build-1": [
    "We were not used to the damp climate yet."
  ],
  "m67-usedto-build-2": [
    "Was Artem used to the new timetable last week?"
  ]
};
let listener, factory, networkCalls = 0;
const source = read(factoryPath);
const scripts = [...source.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(m => m[1]).filter(s => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(s));
assert.equal(scripts.length, 1);
assert.equal(crypto.createHash('sha256').update(scripts[0]).digest('hex'),'43b4bd3e266594ce7b6ffe697e979ba64c3a824cfb816af06fd8beef31847f8b','Exact shipped Alpine factory, not just whole-view SHA');
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
  const row = expected[item.id];
  assert.ok(actual.includes(row[2]), item.id + ': complete Ukrainian translation');
  const allowed = aliases[item.id] || [row[1]];
  assert.ok(allowed.some(sentence => actual.includes(sentence)), item.id + ': complete English sentence');
}
function orderFor(s, index, answer) {
  const bank = s.inputTokenBank(index), words = String(answer).trim().split(/\s+/);
  const used = new Set(), order = []; let pos = 0;
  while (pos < words.length) {
    const found = bank.findIndex((t, i) => { const ws = String(t.value).trim().split(/\s+/); return !used.has(i) && ws.length && ws.every((w, n) => words[pos + n] === w); });
    assert.ok(found >= 0, 'Canonical token absent at ' + pos + ': ' + answer);
    used.add(found); order.push(found); pos += String(bank[found].value).trim().split(/\s+/).length;
  }
  assert.equal(used.size, bank.length); return order;
}
function build(s, i, order) { for (const n of order) s.appendInputToken('inputs', i, s.inputTokenBank(i)[n]); }
function assertDistinctOptions(task, state) {
  assert.equal(new Set(task.options.map(value => state.normalize(value))).size, task.options.length, task.id + ': normalized option collision');
  assert.ok(!task.options.filter(value => !task.accepted.includes(value)).some(value => task.accepted.some(answer => state.normalize(answer) === state.normalize(value))), task.id + ': correct/distractor collision');
}
const counts = { own_tasks: 18, options: 0, manual_inputs: 0, token_inputs: 0, accepted_variants: 0, deferred_meaning_translations: 0, grammatical_but_instruction_rejections: 0, checked: new Set() };

test('independent M67 six-slot native maps, no type transitions, three own 2/2/2 practices', () => {
 assert.equal(master.version,'1.0.0'); assert.equal(master.base_sha,'a8db1d50a01005b9a519ee33a655cf65c20d13aa');
 const owners=['Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusInfinitiveTheorySeeder','Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsStopRememberForgetTryRegretTheorySeeder','Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder'];
 assert.deepEqual(lessons.map(l=>l.identity),owners);
 const native=['hero','forms-grid','usage-panels','mistakes-grid','summary-list','navigation-chips','practice-set'];
 const comparison=[...native.slice(0,1),'comparison-table',...native.slice(2)];
 const types=[native,comparison,comparison];
 const levels=Array.from({length:3},()=>['B1','B1','B1','B1','B1',null,'B1']);
 const columns=['header','left','left','right','right','footer','left'];
 const details=[['infinitive-negation-scope','infinitive-usage','/sections/2'],['meaning-memory-evidence','meaning-usage','/sections/1'],['usedto-duration-not-proof','usedto-usage','/sections/0']];
 const banks=['PolyglotVerbsPlusInfinitiveAllLevelsLessonSeeder','PolyglotStopRememberForgetTryRegretAllLevelsLessonSeeder','PolyglotBeUsedToGetUsedToUsedToAllLevelsLessonSeeder'];
 const ids=[],explanations=new Set();
 for(const[n,l] of lessons.entries()){
  assert.equal(l.locale,'uk');assert.equal(l.technical_block_level,'B1');assert.equal(l.display_level,n?'B1-B2':'B1');
  assert.deepEqual(l.category_ancestry,['verb-patterns']);
  assert.deepEqual(l.blocks.map(b=>b.type),types[n]);assert.deepEqual(l.blocks.map(b=>b.level),levels[n]);assert.deepEqual(l.blocks.map(b=>b.column),columns);
  assert.deepEqual(l.blocks.map(b=>b.existing_source_index),[0,1,2,3,4,5,null]);
  assert.deepEqual(l.details.map(d=>[d.key,d.owner_block_key,d.owner_pointer]),[details[n]]);
  for(const d of l.details){const owner=l.blocks.find(b=>b.key===d.owner_block_key),parts=d.owner_pointer.split('/');assert.equal(d.fragment.type,'usage-panels');assert.ok(owner.data[parts[1]][Number(parts[2])]);assert.ok(d.fragment.data.sections[0].description.length>100);}
  assert.equal(l.blocks.filter(b=>b.type==='practice-set').length,1);assert.equal(l.data.linked_practice.source,'theory_links');assert.deepEqual(l.data.linked_practice.question_types,['4']);assert.deepEqual(l.data.linked_practice.seeder_classes,['Database\\Seeders\\V3\\Polyglot\\'+banks[n]]);
  assert.deepEqual(groups.flatMap(g=>l.data[g].map(t=>t.source_index)),[1,2,3,4,5,6]);
  for(const g of groups){assert.equal(l.data[g].length,2);for(const t of l.data[g]){
   ids.push(t.id);assert.ok(expected[t.id]);assert.equal(t.answer,expected[t.id][0]);assert.ok(t.accepted.includes(t.answer));assert.equal(t.punctuation_sensitive,undefined);
   if(g==='inputs')assert.deepEqual(t.accepted,aliases[t.id]);else{assert.ok([2,3].includes(t.options.length));assert.equal(new Set(t.options).size,t.options.length);}
   for(const value of Object.values(t.feedback)){assert.doesNotMatch(value,/<[^>]+>|&(?:#\\d+|#x[0-9a-f]+|[a-z]+);/i);explanations.add(value);}
  }}
 }
 assert.deepEqual(ids,Object.keys(expected));assert.equal(new Set(ids).size,18);assert.ok(explanations.size>=40);
});
test('unchanged punctuation-insensitive normalizer in all three engines; finite values stay distinct',()=>{
  const f=stateFor(lessons[0]),s=f.s;
  assert.equal(s.normalize('One clause, therefore, another clause.'),s.normalize('One clause; therefore, another clause.'));
  for(const l of lessons)for(const g of ['selects','choices'])for(const t of l.data[g]){
    assertDistinctOptions(t,s);
  }
  // A deliberately corrupt author fixture must fail this same finite pre-freeze check.
  // The unchanged engine intentionally ignores punctuation; no global grader is added.
  for(const options of [['One clause, therefore another.','One clause; therefore another.'],["don't",'dont']]){
    assert.throws(()=>assertDistinctOptions({id:'synthetic-collision',options,accepted:[options[0]]},s),/normalized option collision/);
  }
  for(const g of groups){
    const artificial=clone(lessons[0]);artificial.data[g][0].accepted=['One clause, therefore, another clause.'];
    const x=stateFor(artificial);x.s[prop[g]][0]='One clause; therefore, another clause.';x.s.check(g);assert.equal(x.s.isCorrect(g,0),true);x.unchanged();
  }
  f.unchanged();
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
      assert.match(t.label,/[А-Яа-яІіЇїЄєҐґ]/u);assert.match(t.prompt,/lang="en"/);assert.ok(!t.prompt.includes(expected[t.id][2]));
      s.resetGroup('choices');s.choiceAnswers[i]=t.answer;assert.equal(s.isChecked('choices'),false);s.check('choices');assert.equal(s.isChecked('choices'),true);feedback(t,s.feedbackText('choices',i));s.resetGroup('choices');assert.equal(s.isChecked('choices'),false);counts.deferred_meaning_translations++;
    }f.unchanged();
  });
  for(const[i,t]of l.data.inputs.entries()){
    test(t.id+': manual, independent aliases, mixed edit, full translated feedback and retry',()=>{
      const f=stateFor(l),s=f.s;assert.match(t.after,/[А-Яа-яІіЇїЄєҐґ]/u);assert.ok(t.before.includes('/'));assert.ok(!t.before.includes(t.answer));assert.ok(!t.after.includes(t.answer));
      s.check('inputs');assert.equal(s.feedbackText('inputs',i),i18n.empty);s.inputAnswers[i]=t.answer.split(/\s+/).slice(0,2).join(' ');s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),false);
      for(let r=0;r<2;r++){s.inputAnswers[i]=wrong[t.id];s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),false);assert.equal(s.scoreText('inputs'),score(0));assert.equal(s.feedbackText('inputs',i),t.feedback[key(wrong[t.id])]);feedback(t,s.feedbackText('inputs',i),wrong[t.id]);}
      for(const full of fullRelativeRejected[t.id] || [])for(const candidate of [full,full.replace(/[.!?]+$/,'')]){
        s.inputAnswers[i]=candidate;s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),false);assert.equal(s.scoreText('inputs'),score(0));assert.equal(s.feedbackText('inputs',i),t.feedback[key(candidate)]);feedback(t,s.feedbackText('inputs',i));assert.match(s.feedbackText('inputs',i),/[Гг]раматич/u);assert.match(s.feedbackText('inputs',i),/задан|умов|вправі|виконав|факт|рішення|але/);counts.grammatical_but_instruction_rejections++;
      }
      for(const a of aliases[t.id]){s.inputAnswers[i]=a;s.syncInputTokenBank(i);s.check('inputs');s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);assert.equal(s.scoreText('inputs'),score(1));feedback(t,s.feedbackText('inputs',i));counts.accepted_variants++;
        s.inputAnswers[i]=a.replace(/[.!?]$/,'');s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);feedback(t,s.feedbackText('inputs',i));
        if(a.includes("'")){s.inputAnswers[i]=a.replaceAll("'",'’');s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);feedback(t,s.feedbackText('inputs',i));}}
      s.resetGroup('inputs');assert.equal(s.isChecked('inputs'),false);assert.equal(s.userAnswer('inputs',i),'');assert.equal(s.scoreText('inputs'),score(0));counts.manual_inputs++;counts.checked.add(t.id);f.unchanged();
    });
    test(t.id+': strict shuffled token spelling/case/multiplicity, delete/return/rebuild and mixed editing',()=>{
      const f=stateFor(l),s=f.s,order=orderFor(s,i,expected[t.id][0]);const bank=s.inputTokenBank(i),declared=t.before.split('/').map(t=>t.trim()).filter(Boolean);
      assert.deepEqual(Array.from(bank,t=>t.value),declared);assert.deepEqual(declared.flatMap(value=>value.split(/\s+/)).sort(),expected[t.id][0].split(/\s+/).sort(), 'Exact token chunks and canonical word multiplicity');assert.equal(new Set(bank.map(t=>t.id)).size,bank.length);assert.notDeepEqual(order,Array.from(bank,(_,n)=>n));
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
test('unknown typo keeps generic fallback and never adds answer keys or runtime stripping',()=>{for(const l of lessons){const f=stateFor(l),s=f.s;s.inputAnswers[0]='unknown deliberate typo zzzz';s.syncInputTokenBank(0);s.check('inputs');assert.equal(s.isCorrect('inputs',0),false);assert.ok(!s.feedbackText('inputs',0).includes('<'));assert.equal(l.data.inputs[0].feedback['unknown deliberate typo zzzz'],undefined);f.unchanged();}});
after(()=>{
  assert.equal(networkCalls,0);assert.equal(counts.options,30);assert.equal(counts.manual_inputs,6);assert.equal(counts.token_inputs,6);assert.equal(counts.accepted_variants,10);assert.equal(counts.deferred_meaning_translations,6);assert.equal(counts.grammatical_but_instruction_rejections,12);assert.deepEqual([...counts.checked].sort(),Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(p=>[p,hash(p)])),protectedBefore);
  const {checked,...totals}=counts;console.log('M67_STATE_COVERAGE '+JSON.stringify({...totals,checked_task_ids:[...checked],network_calls:networkCalls,engine_profile:engineProfile,exact_factory_sha256:'43b4bd3e266594ce7b6ffe697e979ba64c3a824cfb816af06fd8beef31847f8b',source_mode:draft?'draft-only-not-frozen':'frozen-release',punctuation_limitation:'Default matching ignores commas; explicit contextual UA choices distinguish A/B, not comma-sensitive input grading',master_sha256:hash(masterPath),limitation:'Offline shipped factory only; not browser/widget/DB/no-JS/print acceptance.'}));
});
