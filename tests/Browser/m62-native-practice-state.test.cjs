// Offline tests of the unchanged shipped native factory. No browser, DB, Laravel, working .env or network.
const { test, after } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = process.env.M62_REPO_ROOT || path.resolve(__dirname, '../..');
const read = p => fs.readFileSync(path.isAbsolute(p) ? p : path.join(root, p), 'utf8');
const hash = p => crypto.createHash('sha256').update(fs.readFileSync(path.isAbsolute(p) ? p : path.join(root, p))).digest('hex');
const packagePath = 'app/Support/M62ReportedReviewRelativeBasicsPackage.php';
const draft = process.env.M62_AUTHOR_DRAFT === '1';
const packageSource = fs.existsSync(path.join(root, packagePath)) ? read(packagePath) : '';
function constant(name) { const m = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';")); assert.ok(m, name); return m[1]; }
const masterPath = draft ? (process.env.M62_AUTHOR_PATH || 'storage/app/seo-m62-local/author-draft-v4.json') : constant('MASTER');
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
  "m62-advanced-form-suggest": [
    "taking",
    "Marta suggested taking the early train.",
    "Марта запропонувала сісти на ранній потяг."
  ],
  "m62-advanced-form-advise": [
    "not to",
    "The doctor advised Ihor not to run.",
    "Лікарка порадила Ігореві не бігти."
  ],
  "m62-advanced-meaning-agreement": [
    "Максим погодився зібрати зразки; Ірина нагадала йому взяти сумку-холодильник. Виконання обох дій речення не підтверджує.",
    "After Maksym agreed to collect the samples, Iryna reminded him to bring a cool bag.",
    "Після того як Максим погодився зібрати зразки, Ірина нагадала йому взяти сумку-холодильник."
  ],
  "m62-advanced-meaning-suggest": [
    "Віра запропонувала групі зробити перерву; згоди й виконання ще не встановлено.",
    "Vira suggested taking a short break.",
    "Віра запропонувала зробити коротку перерву."
  ],
  "m62-advanced-build-promise": [
    "Danylo promised not to publish the photograph.",
    "Danylo promised not to publish the photograph.",
    "Данило пообіцяв не публікувати фотографію."
  ],
  "m62-advanced-build-apology": [
    "Olha apologised for damaging the cover.",
    "Olha apologised for damaging the cover.",
    "Ольга перепросила за те, що пошкодила обкладинку."
  ],
  "m62-relative-form-whose": [
    "whose",
    "The student whose violin needs repair is absent.",
    "Учениця, чия скрипка потребує ремонту, відсутня."
  ],
  "m62-relative-form-where": [
    "where",
    "The courtyard where we met is now closed.",
    "Двір, де ми зустрілися, тепер закритий."
  ],
  "m62-relative-meaning-object": [
    "Ольга навчила помічника, а помічник зараз перевіряє бронювання.",
    "The assistant who she trained now checks the bookings.",
    "Помічник, якого вона навчила, тепер перевіряє бронювання."
  ],
  "m62-relative-meaning-subject": [
    "Volunteers — додаток thanked; who — підмет carried, а boxes — додаток carried.",
    "I thanked the volunteers who carried the boxes.",
    "Я подякував волонтерам, які перенесли коробки."
  ],
  "m62-relative-build-object": [
    "The man who she thanked is our neighbour.",
    "The man who she thanked is our neighbour.",
    "Чоловік, якому вона подякувала, — наш сусід."
  ],
  "m62-relative-build-subject": [
    "The students who grow herbs meet here.",
    "The students who grow herbs meet here.",
    "Учні, які вирощують пряні трави, збираються тут."
  ],
  "m62-defining-form-agreement": [
    "live",
    "The technicians who live near the station arrive early.",
    "Техніки, які живуть біля станції, приходять рано."
  ],
  "m62-defining-form-place-object": [
    "which",
    "The museum which we visited is being repaired.",
    "Музей, який ми відвідали, зараз ремонтують."
  ],
  "m62-defining-meaning-subgroup": [
    "A: опис без ком вибирає двох екскурсоводів за ознакою.",
    "The guides who speak Polish should come here.",
    "Екскурсоводи, які розмовляють польською, мають підійти сюди:"
  ],
  "m62-defining-meaning-deletion": [
    "Речення лишиться граматичним, але в цій розмові перестане вибирати зважену посилку з чотирьох.",
    "The parcel that we weighed is on the counter.",
    "Посилка, яку ми зважили, лежить на прилавку."
  ],
  "m62-defining-build-object": [
    "The box that Oksana labelled contains cables.",
    "The box that Oksana labelled contains cables.",
    "Коробка, яку підписала Оксана, містить кабелі."
  ],
  "m62-defining-build-whose": [
    "The visitor whose pass expired is waiting outside.",
    "The visitor whose pass expired is waiting outside.",
    "Відвідувач, у якого закінчився строк дії перепустки, чекає надворі."
  ]
};
const aliases = {
  "m62-advanced-build-promise": [
    "Danylo promised not to publish the photograph."
  ],
  "m62-advanced-build-apology": [
    "Olha apologised for damaging the cover.",
    "Olha apologized for damaging the cover."
  ],
  "m62-relative-build-object": [
    "The man who she thanked is our neighbour."
  ],
  "m62-relative-build-subject": [
    "The students who grow herbs meet here.",
    "The students that grow herbs meet here."
  ],
  "m62-defining-build-object": [
    "The box that Oksana labelled contains cables.",
    "The box which Oksana labelled contains cables.",
    "The box Oksana labelled contains cables."
  ],
  "m62-defining-build-whose": [
    "The visitor whose pass expired is waiting outside."
  ]
};
const wrong = {
  "m62-advanced-build-promise": "Danylo did not promise to publish the photograph.",
  "m62-advanced-build-apology": "Olha apologised to damage the cover.",
  "m62-relative-build-object": "The man who thanked she is our neighbour.",
  "m62-relative-build-subject": "The students who grows herbs meet here.",
  "m62-defining-build-object": "The box that Oksana labelled it contains cables.",
  "m62-defining-build-whose": "The visitor who's pass expired is waiting outside."
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
const counts = { own_tasks: 18, options: 0, manual_inputs: 0, token_inputs: 0, accepted_variants: 0, deferred_meaning_translations: 0, checked: new Set() };

test('independent M62 2/7/7 continuing slots, exact semantic maps, owner details and plain 2/2/2 practice', () => {
  assert.equal(master.version,'1.0.0'); assert.equal(master.base_sha,'17572e80bad9b968fd40f394d012cb0414a35ccf');
  assert.deepEqual(lessons.map(l=>l.identity),[
    'Database\\Seeders\\Page_V3\\ReportedSpeech\\AdvancedReportedSpeechTheorySeeder',
    'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsRelativeClausesTheorySeeder',
    'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsDefiningRelativeClausesTheorySeeder']);
  const types=[
    ['hero','forms-grid','usage-panels','mistakes-grid','summary-list','navigation-chips','practice-set'],
    ['hero','usage-panels','comparison-table','usage-panels','mistakes-grid','summary-list','navigation-chips','practice-set'],
    ['hero','usage-panels','forms-grid','comparison-table','mistakes-grid','summary-list','navigation-chips','practice-set']];
  const levels=[['B2',null,'B2','B2','B2',null,'B2'],['B1',null,null,null,null,null,null,'B1'],['B1',null,null,null,null,null,null,'B1']];
  const indexes=[[0,1,null,null,null,null,null],[0,1,2,3,4,5,6,null],[0,1,2,3,4,5,6,null]];
  const columns=[['header','left','left','left','left','footer','left'],['header','left','left','right','right','right','footer','left'],['header','left','left','right','right','right','footer','left']];
  const detail=[['advanced-reported-hypothetical','advanced-reported-decisions','/sections/3'],['relative-two-subject-positions','relative-structure','/sections/2'],['defining-preposition-position','defining-patterns','/items/1']];
  const banks=['PolyglotAdvancedReportedSpeechAllLevelsLessonSeeder','PolyglotRelativeClausesAllLevelsLessonSeeder','PolyglotDefiningRelativeClausesAllLevelsLessonSeeder'];
  const ids=[], explanations=new Set();
  for(const[n,l]of lessons.entries()){
    assert.equal(l.locale,'uk');assert.equal(l.technical_block_level,n===0?'B2':'B1');assert.equal(l.display_level,n===0?'B2':'B1-B2');
    assert.deepEqual(l.category_ancestry,n===0?['reported-speech']:['clauses-and-linking-words']);
    assert.deepEqual(l.blocks.map(b=>b.type),types[n]);assert.deepEqual(l.blocks.map(b=>b.level),levels[n]);
    assert.deepEqual(l.blocks.map(b=>b.column),columns[n]);assert.deepEqual(l.blocks.map(b=>b.existing_source_index),indexes[n]);
    assert.deepEqual(l.details.map(d=>[d.key,d.owner_block_key,d.owner_pointer]),[detail[n]]);
    for(const d of l.details){const owner=l.blocks.find(b=>b.key===d.owner_block_key),parts=d.owner_pointer.split('/');assert.equal(d.fragment.type,'usage-panels');assert.ok(owner.data[parts[1]][Number(parts[2])]);assert.ok(d.fragment.data.sections[0].description.length>100);}
    assert.equal(l.blocks.filter(b=>b.type==='practice-set').length,1);
    assert.equal(l.data.linked_practice.source,'theory_links');assert.deepEqual(l.data.linked_practice.question_types,['4']);
    assert.deepEqual(l.data.linked_practice.seeder_classes,['Database\\Seeders\\V3\\Polyglot\\'+banks[n]]);
    assert.deepEqual(groups.flatMap(g=>l.data[g].map(t=>t.source_index)),[1,2,3,4,5,6]);
    for(const g of groups){assert.equal(l.data[g].length,2);for(const t of l.data[g]){
      assert.ok(expected[t.id]);ids.push(t.id);assert.equal(t.answer,expected[t.id][0]);assert.ok(t.accepted.includes(t.answer));
      assert.equal(t.punctuation_sensitive,undefined);
      if(g==='inputs')assert.deepEqual(t.accepted,aliases[t.id]);else{assert.equal(t.options.length,3);assert.equal(new Set(t.options).size,3);}
      for(const value of Object.values(t.feedback)){assert.doesNotMatch(value,/<[^>]+>|&(?:#\\d+|#x[0-9a-f]+|[a-z]+);/i);explanations.add(value);}
    }}
  }
  assert.deepEqual(ids,Object.keys(expected));assert.equal(new Set(ids).size,18);assert.equal(explanations.size,52);
});
test('default unchanged factory ignores commas: contextual meaning choice, not punctuation-only input grading',()=>{
  const f=stateFor(lessons[2]),s=f.s;
  assert.equal(s.normalize('The guides who speak Polish should come here.'),s.normalize('The guides, who speak Polish, should come here.'));
  s.inputAnswers[0]='The box, that Oksana labelled, contains cables.';s.check('inputs');assert.equal(s.isCorrect('inputs',0),true);
  // This acceptance is an explicit grading LIMITATION, not endorsement of incorrect defining punctuation.
  const t=lessons[2].data.choices[0];assert.ok(t.prompt.includes('A. The guides who speak Polish'));assert.ok(t.prompt.includes('B. The guides, who speak Polish,'));
  assert.equal(new Set(t.options.map(x=>s.normalize(x))).size,3);
  for(const o of t.options){s.choiceAnswers[0]=o;s.check('choices');assert.equal(s.isCorrect('choices',0),o===expected[t.id][0]);assert.ok(s.feedbackText('choices',0).includes('B. The guides, who speak Polish, should come here.'));}
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
  assert.equal(networkCalls,0);assert.equal(counts.options,36);assert.equal(counts.manual_inputs,6);assert.equal(counts.token_inputs,6);assert.equal(counts.accepted_variants,10);assert.equal(counts.deferred_meaning_translations,6);assert.deepEqual([...counts.checked].sort(),Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(p=>[p,hash(p)])),protectedBefore);
  const {checked,...totals}=counts;console.log('M62_STATE_COVERAGE '+JSON.stringify({...totals,checked_task_ids:[...checked],network_calls:networkCalls,source_mode:draft?'draft-only-not-frozen':'frozen-release',punctuation_limitation:'Default matching ignores commas; explicit contextual UA choices distinguish A/B, not comma-sensitive input grading',master_sha256:hash(masterPath),limitation:'Offline shipped factory only; not browser/widget/DB/no-JS/print acceptance.'}));
});
