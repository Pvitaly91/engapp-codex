// Offline tests of the unchanged shipped native factory. No browser, DB, Laravel, working .env or network.
const { test, after } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = process.env.M61_REPO_ROOT || path.resolve(__dirname, '../..');
const read = p => fs.readFileSync(path.isAbsolute(p) ? p : path.join(root, p), 'utf8');
const hash = p => crypto.createHash('sha256').update(fs.readFileSync(path.isAbsolute(p) ? p : path.join(root, p))).digest('hex');
const packagePath = 'app/Support/M61ReportedSpeechChangesPackage.php';
const draft = process.env.M61_AUTHOR_DRAFT === '1';
const packageSource = fs.existsSync(path.join(root, packagePath)) ? read(packagePath) : '';
function constant(name) { const m = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';")); assert.ok(m, name); return m[1]; }
const masterPath = draft ? (process.env.M61_AUTHOR_PATH || 'storage/app/seo-m61-local/author-draft-v2.json') : constant('MASTER');
const master = JSON.parse(read(masterPath));
if (draft) assert.match(master.status, /^draft(?:-|$)/);
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
  "m61-backshift-form-passive": [
    "were",
    "Marta said that the windows were being washed.",
    "Марта сказала, що вікна миють."
  ],
  "m61-backshift-form-perfect": [
    "had",
    "Petro said that he had already locked the gate.",
    "Петро сказав, що вже замкнув хвіртку."
  ],
  "m61-backshift-meaning-current": [
    "Графік подано з минулої точки повідомлення; чи змінився він, речення не встановлює.",
    "The receptionist said that the office opened at eight.",
    "Працівниця рецепції сказала, що офіс відчиняється о восьмій."
  ],
  "m61-backshift-meaning-prohibition": [
    "Відвідувачам заборонили торкатися пульта.",
    "The guard said that visitors must not touch the controls.",
    "Охоронець сказав, що відвідувачам не можна торкатися пульта."
  ],
  "m61-backshift-build-perfect": [
    "Sofiia said that she had not finished the drawing.",
    "Sofiia said that she had not finished the drawing.",
    "Софія сказала, що не завершила малюнок."
  ],
  "m61-backshift-build-should": [
    "Danylo said that we should keep the receipt.",
    "Danylo said that we should keep the receipt.",
    "Данило сказав, що нам слід зберегти чек."
  ],
  "m61-time-place-form-today": [
    "today",
    "Nazar said that the parcel would arrive today.",
    "Назар сказав, що посилка прибуде сьогодні."
  ],
  "m61-time-place-form-this": [
    "this",
    "Marta said that this key was hers.",
    "Марта сказала, що цей ключ її."
  ],
  "m61-time-place-meaning-earlier": [
    "Суботу, 30 травня.",
    "Iryna said that she had posted the letter two days earlier.",
    "Ірина сказала, що відправила листа двома днями раніше."
  ],
  "m61-time-place-meaning-two-days": [
    "Середу, 3 червня.",
    "Danylo said that he would return in two days.",
    "Данило сказав, що повернеться через два дні."
  ],
  "m61-time-place-build-before": [
    "Iryna said that she had received the parcel the day before.",
    "Iryna said that she had received the parcel the day before.",
    "Ірина сказала, що отримала посилку попереднього дня."
  ],
  "m61-time-place-build-that-that": [
    "Taras said that that shop would open the next day.",
    "Taras said that that shop would open the next day.",
    "Тарас сказав, що той магазин відкриється наступного дня."
  ],
  "m61-say-tell-ask-form-recipient": [
    "to us",
    "Lina said to us that the workshop was full.",
    "Ліна сказала нам, що в майстерні немає вільних місць."
  ],
  "m61-say-tell-ask-form-for": [
    "for",
    "Maksym asked Olena for a spare pen.",
    "Максим попросив в Олени запасну ручку."
  ],
  "m61-say-tell-ask-meaning-inform": [
    "Дарина повідомила Ігореві, де ключ.",
    "Daryna told me where the spare key was.",
    "Дарина повідомила мені, де запасний ключ."
  ],
  "m61-say-tell-ask-meaning-actor": [
    "Роман: він попросив дозволу сісти сам.",
    "Roman asked to sit near the window.",
    "Роман попросив дозволу сісти біля вікна."
  ],
  "m61-say-tell-ask-build-question": [
    "Marta asked Ihor if he needed a torch.",
    "Marta asked Ihor if he needed a torch.",
    "Марта запитала Ігоря, чи потрібен йому ліхтарик."
  ],
  "m61-say-tell-ask-build-negative": [
    "The assistant told us not to move the boxes.",
    "The assistant told us not to move the boxes.",
    "Помічник сказав нам не пересувати коробки."
  ]
};
const aliases = {
  "m61-backshift-build-perfect": [
    "Sofiia said that she had not finished the drawing.",
    "Sofiia said she had not finished the drawing.",
    "Sofiia said that she hadn't finished the drawing.",
    "Sofiia said she hadn't finished the drawing."
  ],
  "m61-backshift-build-should": [
    "Danylo said that we should keep the receipt.",
    "Danylo said we should keep the receipt."
  ],
  "m61-time-place-build-before": [
    "Iryna said that she had received the parcel the day before.",
    "Iryna said she had received the parcel the day before.",
    "Iryna said that she had received the parcel the previous day.",
    "Iryna said she had received the parcel the previous day.",
    "Iryna said that she'd received the parcel the day before.",
    "Iryna said she'd received the parcel the day before.",
    "Iryna said that she'd received the parcel the previous day.",
    "Iryna said she'd received the parcel the previous day."
  ],
  "m61-time-place-build-that-that": [
    "Taras said that that shop would open the next day.",
    "Taras said that that shop would open the following day."
  ],
  "m61-say-tell-ask-build-question": [
    "Marta asked Ihor if he needed a torch.",
    "Marta asked Ihor whether he needed a torch."
  ],
  "m61-say-tell-ask-build-negative": [
    "The assistant told us not to move the boxes."
  ]
};
const wrong = {
  "m61-backshift-build-perfect": [
    "Sofiia did not say that she had finished the drawing.",
    "Sofiia said that she had not finished the drawing.",
    "Софія сказала, що не завершила малюнок."
  ],
  "m61-backshift-build-should": [
    "Danylo said that we had kept the receipt.",
    "Danylo said that we should keep the receipt.",
    "Данило сказав, що нам слід зберегти чек."
  ],
  "m61-time-place-build-before": [
    "Iryna said that she had received the parcel yesterday.",
    "Iryna said that she had received the parcel the day before.",
    "Ірина сказала, що отримала посилку попереднього дня."
  ],
  "m61-time-place-build-that-that": [
    "Taras said that shop would open the next day.",
    "Taras said that that shop would open the next day.",
    "Тарас сказав, що той магазин відкриється наступного дня."
  ],
  "m61-say-tell-ask-build-question": [
    "Marta told Ihor that he needed a torch.",
    "Marta asked Ihor if he needed a torch.",
    "Марта запитала Ігоря, чи потрібен йому ліхтарик."
  ],
  "m61-say-tell-ask-build-negative": [
    "The assistant did not tell us to move the boxes.",
    "The assistant told us not to move the boxes.",
    "Помічник сказав нам не пересувати коробки."
  ]
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
const counts = { own_tasks: 18, options: 0, manual_inputs: 0, token_inputs: 0, accepted_variants: 0, deferred_meaning_translations: 0, checked: new Set() };

test('independent M61 author scope, exact six continuing slots, table/form semantic mapping, plain feedback and 2/2/2', () => {
  assert.equal(master.version,'1.0.0'); assert.equal(master.base_sha,'d60a288565f841dceefc16ac6ee10fa5c8c55395');
  assert.deepEqual(lessons.map(l=>l.identity),[
    'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechBackshiftTheorySeeder',
    'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechTimePlaceChangesTheorySeeder',
    'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechSayTellAskTheorySeeder']);
  const prefixes=['backshift','time-place','say-tell-ask'];
  const firstTypes=['comparison-table','comparison-table','forms-grid'];
  const secondTypes=['usage-panels','usage-panels','comparison-table'];
  const details=[['backshift-modal-meaning','backshift-use','/sections/2'],['time-place-motion-perspective','time-place-context','/sections/2'],['say-tell-ask-utterance-intent','say-tell-ask-patterns','/items/2']];
  const ids=[],explanations=new Set();
  for(const[n,l]of lessons.entries()){
    assert.equal(l.locale,'uk');assert.equal(l.display_level,'B1-B2');assert.equal(l.technical_block_level,'B1');
    assert.deepEqual(l.category_ancestry,['reported-speech']);
    assert.deepEqual(l.blocks.map(b=>b.level),['B1',null,null,null,null,null,'B1']);
    assert.deepEqual(l.blocks.map(b=>b.existing_source_index),[0,1,2,3,4,5,null]);
    assert.deepEqual(l.blocks.map(b=>b.type),['hero',firstTypes[n],secondTypes[n],'mistakes-grid','summary-list','navigation-chips','practice-set']);
    assert.deepEqual(l.blocks.map(b=>b.column),['header','left','left','right','right','footer','left']);
    assert.equal(l.blocks[0].data.level,'B1-B2');
    assert.deepEqual(l.details.map(d=>[d.key,d.owner_block_key,d.owner_pointer]),[details[n]]);
    for(const d of l.details){const owner=l.blocks.find(b=>b.key===d.owner_block_key),parts=d.owner_pointer.split('/');assert.equal(d.fragment.type,'usage-panels');assert.ok(owner.data[parts[1]][Number(parts[2])]);assert.ok(d.fragment.data.sections[0].description.length>100);}
    assert.equal(l.blocks.filter(b=>b.type==='practice-set').length,1);assert.equal(l.blocks.at(-1).key,prefixes[n]+'-practice');
    assert.equal(l.data.linked_practice.source,'theory_links');assert.deepEqual(l.data.linked_practice.question_types,['4']);
    const banks=['PolyglotReportedSpeechBackshiftOfTensesAllLevelsLessonSeeder','PolyglotReportedSpeechTimePlaceChangesAllLevelsLessonSeeder','PolyglotReportedSpeechSayTellAskAllLevelsLessonSeeder'];
    if(!draft)assert.deepEqual(l.data.linked_practice.seeder_classes,['Database\\Seeders\\V3\\Polyglot\\'+banks[n]]);
    assert.deepEqual(groups.flatMap(g=>l.data[g].map(t=>t.source_index)),[1,2,3,4,5,6]);
    for(const g of groups){assert.equal(l.data[g].length,2);for(const t of l.data[g]){
      assert.ok(expected[t.id]);ids.push(t.id);assert.equal(t.answer,expected[t.id][0]);assert.ok(t.accepted.includes(t.answer));
      const keys=g==='inputs'?['id','source_index','before','after','answer','accepted','feedback']:['id','source_index','label',...(g==='choices'?['prompt']:[]),'options','answer','accepted','feedback'];
      assert.deepEqual(Object.keys(t).sort(),keys.sort());if(g==='inputs')assert.deepEqual(t.accepted,aliases[t.id]);else{assert.equal(t.options.length,3);assert.equal(new Set(t.options).size,3);}
      for(const value of Object.values(t.feedback)){assert.doesNotMatch(value,/<[^>]+>|&(?:#\\d+|#x[0-9a-f]+|[a-z]+);/i);explanations.add(value);}
    }}
  }
  assert.deepEqual(ids,Object.keys(expected));assert.equal(new Set(ids).size,18);assert.equal(explanations.size,48);
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
      for(const a of aliases[t.id]){s.inputAnswers[i]=a;s.syncInputTokenBank(i);s.check('inputs');s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);assert.equal(s.scoreText('inputs'),score(1));feedback(t,s.feedbackText('inputs',i));counts.accepted_variants++;
        s.inputAnswers[i]=a.replace(/[.!?]$/,'');s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);feedback(t,s.feedbackText('inputs',i));
        if(a.includes("'")){s.inputAnswers[i]=a.replaceAll("'",'’');s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),true);feedback(t,s.feedbackText('inputs',i));}}
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
  assert.equal(networkCalls,0);assert.equal(counts.options,36);assert.equal(counts.manual_inputs,6);assert.equal(counts.token_inputs,6);assert.equal(counts.accepted_variants,19);assert.equal(counts.deferred_meaning_translations,6);assert.deepEqual([...counts.checked].sort(),Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(p=>[p,hash(p)])),protectedBefore);
  const {checked,...totals}=counts;console.log('M61_STATE_COVERAGE '+JSON.stringify({...totals,checked_task_ids:[...checked],network_calls:networkCalls,source_mode:draft?'draft-only-not-frozen':'frozen-release',master_sha256:hash(masterPath),limitation:'Offline shipped factory only; not browser/widget/DB/no-JS/print acceptance.'}));
});
