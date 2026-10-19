// Offline tests of the unchanged shipped native factory. No browser, DB, Laravel, working .env or network.
const { test, after } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = process.env.M60_REPO_ROOT || path.resolve(__dirname, '../..');
const read = p => fs.readFileSync(path.isAbsolute(p) ? p : path.join(root, p), 'utf8');
const hash = p => crypto.createHash('sha256').update(fs.readFileSync(path.isAbsolute(p) ? p : path.join(root, p))).digest('hex');
const packagePath = 'app/Support/M60ReportedSpeechBasicsPackage.php';
const draft = process.env.M60_AUTHOR_DRAFT === '1';
const packageSource = fs.existsSync(path.join(root, packagePath)) ? read(packagePath) : '';
function constant(name) { const m = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';")); assert.ok(m, name); return m[1]; }
const masterPath = draft ? (process.env.M60_AUTHOR_PATH || 'storage/app/seo-m60-local/author-draft-v5.json') : constant('MASTER');
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
  "m60-statements-form-recipient": [
    "Iryna",
    "Marta told Iryna that the bus was late.",
    "Марта сказала Ірині, що автобус запізнюється."
  ],
  "m60-statements-form-backshift": [
    "was working",
    "Oleh said that he was working on the report.",
    "Олег сказав, що працює над звітом."
  ],
  "m60-statements-meaning-negation": [
    "Тарас сказав, що не приєднається до нас.",
    "Taras said that he would not join us.",
    "Тарас сказав, що не приєднається до нас."
  ],
  "m60-statements-meaning-self-report": [
    "Марія, яка зараз говорить.",
    "I said that I needed the key.",
    "Я сказала, що мені потрібен ключ."
  ],
  "m60-statements-build-tomorrow": [
    "Olena said that she would bring the map the next day.",
    "Olena said that she would bring the map the next day.",
    "Олена сказала, що принесе карту наступного дня."
  ],
  "m60-statements-build-negative": [
    "Danylo said that he was not hungry.",
    "Danylo said that he was not hungry.",
    "Данило сказав, що він не голодний."
  ],
  "m60-questions-form-embedded-order": [
    "the museum opened",
    "Iryna asked what time the museum opened.",
    "Ірина запитала, о котрій відчиняється музей."
  ],
  "m60-questions-form-subject-who": [
    "had taken",
    "Oleh asked who had taken the folder.",
    "Олег запитав, хто забрав папку."
  ],
  "m60-questions-meaning-open-answer": [
    "Олена запитала Назара, чи користувався він запасним ключем.",
    "Olena asked Nazar whether he had used the spare key.",
    "Олена запитала Назара, чи користувався він запасним ключем."
  ],
  "m60-questions-meaning-object-who": [
    "Питали, кому телефонував Олег, не хто телефонував йому.",
    "Iryna asked Oleh who he had called.",
    "Ірина запитала Олега, кому він телефонував."
  ],
  "m60-questions-build-if-whether": [
    "Taras asked if Olena had locked the gate.",
    "Taras asked if Olena had locked the gate.",
    "Тарас запитав, чи Олена замкнула хвіртку."
  ],
  "m60-questions-build-main-question": [
    "Did Iryna ask where Petro worked?",
    "Did Iryna ask where Petro worked?",
    "Ірина запитала, де працює Петро?"
  ],
  "m60-commands-form-negative": [
    "not to",
    "The guard ordered us not to enter.",
    "Охоронець наказав нам не входити."
  ],
  "m60-commands-form-object": [
    "Andrii",
    "The director told Andrii to wait.",
    "Директорка сказала Андрієві зачекати."
  ],
  "m60-commands-meaning-requester": [
    "Ірина просила дозволу вийти сама.",
    "Iryna asked to leave early.",
    "Ірина попросила дозволу піти раніше."
  ],
  "m60-commands-meaning-no-compliance": [
    "Павлові порадили зробити перерву.",
    "The nurse advised Pavlo to take a break.",
    "Медсестра порадила Павлові зробити перерву."
  ],
  "m60-commands-build-request": [
    "Marta asked Oksana to carry the box.",
    "Marta asked Oksana to carry the box.",
    "Марта попросила Оксану понести коробку."
  ],
  "m60-commands-build-negative": [
    "The coach told us not to run to the door.",
    "The coach told us not to run to the door.",
    "Тренер сказав нам не бігти до дверей."
  ]
};
const aliases = {
  "m60-statements-build-tomorrow": [
    "Olena said that she would bring the map the next day.",
    "Olena said she would bring the map the next day.",
    "Olena said that she would bring the map the following day.",
    "Olena said she would bring the map the following day.",
    "Olena said that she'd bring the map the next day.",
    "Olena said she'd bring the map the next day.",
    "Olena said that she'd bring the map the following day.",
    "Olena said she'd bring the map the following day."
  ],
  "m60-statements-build-negative": [
    "Danylo said that he was not hungry.",
    "Danylo said he was not hungry.",
    "Danylo said that he wasn't hungry.",
    "Danylo said he wasn't hungry."
  ],
  "m60-questions-build-if-whether": [
    "Taras asked if Olena had locked the gate.",
    "Taras asked whether Olena had locked the gate."
  ],
  "m60-questions-build-main-question": [
    "Did Iryna ask where Petro worked?"
  ],
  "m60-commands-build-request": [
    "Marta asked Oksana to carry the box."
  ],
  "m60-commands-build-negative": [
    "The coach told us not to run to the door."
  ]
};
const wrong = {
  "m60-statements-build-tomorrow": [
    "Olena said that I would bring the map the next day.",
    "Olena said that she would bring the map the next day.",
    "Олена сказала, що принесе карту наступного дня."
  ],
  "m60-statements-build-negative": [
    "Danylo did not say that he was hungry.",
    "Danylo said that he was not hungry.",
    "Данило сказав, що він не голодний."
  ],
  "m60-questions-build-if-whether": [
    "Taras asked if had Olena locked the gate.",
    "Taras asked if Olena had locked the gate.",
    "Тарас запитав, чи Олена замкнула хвіртку."
  ],
  "m60-questions-build-main-question": [
    "Did Iryna ask where did Petro work?",
    "Did Iryna ask where Petro worked?",
    "Ірина запитала, де працює Петро?"
  ],
  "m60-commands-build-request": [
    "Marta asked to carry the box.",
    "Marta asked Oksana to carry the box.",
    "Марта попросила Оксану понести коробку."
  ],
  "m60-commands-build-negative": [
    "The coach did not tell us to run to the door.",
    "The coach told us not to run to the door.",
    "Тренер сказав нам не бігти до дверей."
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
const counts = { own_tasks: 18, options: 0, manual_inputs: 0, token_inputs: 0, accepted_variants: 0, deferred_meaning_translations: 0, checked: new Set() };

test('independent M60 author scope, preserved six continuing slots, finite null/right mapping and 2/2/2', () => {
  assert.equal(master.version,'1.0.1'); assert.equal(master.base_sha,'69994e34da410c66d6807a0921b588f85b81908c');
  assert.deepEqual(lessons.map(l=>l.identity),[
    'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechStatementsTheorySeeder',
    'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechQuestionsTheorySeeder',
    'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechCommandsRequestsTheorySeeder']);
  const prefixes=['statements','questions','commands'];
  const banks=[['PolyglotReportedSpeechStatementsAllLevelsLessonSeeder'],['PolyglotReportedSpeechQuestionsAllLevelsLessonSeeder','PolyglotReportedQuestionsLessonSeeder'],['PolyglotReportedSpeechCommandsRequestsAllLevelsLessonSeeder']];
  const ids=[];
  for(const[n,l]of lessons.entries()){
    assert.equal(l.locale,'uk');assert.equal(l.display_level,'B1-B2');assert.equal(l.technical_block_level,'B1');
    assert.deepEqual(l.category_ancestry,['reported-speech']);
    assert.deepEqual(l.blocks.map(b=>b.level),['B1',null,null,null,null,null,'B1','B1']);
    assert.deepEqual(l.blocks.map(b=>b.existing_source_index),[0,1,2,3,4,5,null,null]);
    assert.deepEqual(l.blocks.map(b=>b.type),['hero','forms-grid','comparison-table','mistakes-grid','summary-list','navigation-chips','usage-panels','practice-set']);
    assert.deepEqual(l.blocks.map(b=>b.column),['header','left','left','right','right','footer','left','left']);
    assert.equal(l.blocks[0].data.level,'B1-B2');
    const keys=['statements-backshift-current-truth','questions-ability-or-request','commands-negation-scope'], pointers=['/sections/0','/sections/3','/sections/1'];
    assert.deepEqual(l.details.map(d=>[d.key,d.owner_block_key,d.owner_pointer]),[[keys[n],prefixes[n]+'-context',pointers[n]]]);
    for(const d of l.details){const owner=l.blocks.find(b=>b.key===d.owner_block_key);assert.equal(d.fragment.type,'usage-panels');assert.ok(owner.data.sections[Number(d.owner_pointer.split('/').at(-1))]);assert.ok(d.fragment.data.sections[0].description.length>100);}
    assert.equal(l.blocks.filter(b=>b.type==='practice-set').length,1);assert.equal(l.blocks.at(-1).type,'practice-set');
    assert.equal(l.data.linked_practice.source,'theory_links');assert.deepEqual(l.data.linked_practice.question_types,['4']);
    assert.deepEqual(l.data.linked_practice.seeder_classes,banks[n].map(x=>'Database\\Seeders\\V3\\Polyglot\\'+x));
    assert.deepEqual(groups.flatMap(g=>l.data[g].map(t=>t.source_index)),[1,2,3,4,5,6]);
    for(const g of groups){assert.equal(l.data[g].length,2);for(const t of l.data[g]){
      assert.ok(expected[t.id]);ids.push(t.id);assert.equal(t.answer,expected[t.id][0]);assert.ok(t.accepted.includes(t.answer));
      const keys=g==='inputs'?['id','source_index','before','after','answer','accepted','feedback']:['id','source_index','label',...(g==='choices'?['prompt']:[]),'options','answer','accepted','feedback'];
      assert.deepEqual(Object.keys(t).sort(),keys.sort());if(g==='inputs')assert.deepEqual(t.accepted,aliases[t.id]);else{assert.equal(t.options.length,3);assert.equal(new Set(t.options).size,3);}
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
  assert.equal(networkCalls,0);assert.equal(counts.options,36);assert.equal(counts.manual_inputs,6);assert.equal(counts.token_inputs,6);assert.equal(counts.accepted_variants,17);assert.equal(counts.deferred_meaning_translations,6);assert.deepEqual([...counts.checked].sort(),Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(p=>[p,hash(p)])),protectedBefore);
  const {checked,...totals}=counts;console.log('M60_STATE_COVERAGE '+JSON.stringify({...totals,checked_task_ids:[...checked],network_calls:networkCalls,source_mode:draft?'draft-only-not-frozen':'frozen-release',master_sha256:hash(masterPath),limitation:'Offline shipped factory only; not browser/widget/DB/no-JS/print acceptance.'}));
});
