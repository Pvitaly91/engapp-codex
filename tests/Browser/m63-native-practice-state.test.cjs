// Offline tests of the unchanged shipped native factory. No browser, DB, Laravel, working .env or network.
const { test, after } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = process.env.M63_REPO_ROOT || path.resolve(__dirname, '../..');
const read = p => fs.readFileSync(path.isAbsolute(p) ? p : path.join(root, p), 'utf8');
const hash = p => crypto.createHash('sha256').update(fs.readFileSync(path.isAbsolute(p) ? p : path.join(root, p))).digest('hex');
const packagePath = 'app/Support/M63RelativeCompletionLinkingPackage.php';
const draft = process.env.M63_AUTHOR_DRAFT === '1';
const packageSource = fs.existsSync(path.join(root, packagePath)) ? read(packagePath) : '';
function constant(name) { const m = packageSource.match(new RegExp('public const ' + name + " = '([^']+)';")); assert.ok(m, name); return m[1]; }
const masterPath = draft ? (process.env.M63_AUTHOR_PATH || 'storage/app/seo-m63-local/author-draft-v2.json') : constant('MASTER');
const master = JSON.parse(read(masterPath));
if (draft) assert.match(master.status, /^draft(?:-|$)/);
else { assert.equal(master.status, 'frozen'); assert.equal(hash(masterPath), constant('MASTER_SHA')); }
const factoryPath = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const engineProfile = process.env.M63_ENGINE_PROFILE || 'accepted-worktree';
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

// Independent editorial keys and complete bilingual feedback expectations: not inferred from AFTER or the factory.
const expected = {
  "m63-nondefining-form-object": [
    "which",
    "This camera, which I borrowed for the trip, needs a new battery.",
    "Цій камері, яку я позичив для поїздки, потрібна нова батарея."
  ],
  "m63-nondefining-form-whose": [
    "whose",
    "Rex, whose collar is blue, belongs to Iryna.",
    "Рекс, чий нашийник синій, належить Ірині."
  ],
  "m63-nondefining-meaning-group": [
    "Варіант А: опис відбирає двох музикантів із шести.",
    "The musicians who read music will join the first rehearsal.",
    "Музиканти, які вміють читати ноти, приєднаються до першої репетиції."
  ],
  "m63-nondefining-meaning-actors": [
    "Олег запросив Світлану; Oleh зберігається, а who тут не опускаємо.",
    "I greeted Svitlana, who Oleh had invited.",
    "Я привітався зі Світланою, яку запросив Олег."
  ],
  "m63-nondefining-build-subject": [
    "Tania, who repairs clocks, works upstairs.",
    "Tania, who repairs clocks, works upstairs.",
    "Таня, яка лагодить годинники, працює нагорі."
  ],
  "m63-nondefining-build-negative": [
    "Our tent, which we bought in May, did not leak.",
    "Our tent, which we bought in May, did not leak.",
    "Наш намет, який ми купили в травні, не протікав."
  ],
  "m63-omitting-form-zero": [
    "Yana sent me",
    "The recipe Yana sent me is easy.",
    "Рецепт, який Яна мені надіслала, простий."
  ],
  "m63-omitting-form-preposition": [
    "with",
    "The key I opened the cabinet with is on the ring.",
    "Ключ, яким я відчинив шафку, на кільці."
  ],
  "m63-omitting-meaning-nondefining": [
    "Ні: це non-defining опис; whom зберігається, як і підмет Marta.",
    "Our guide, whom Marta had met before, speaks Polish.",
    "Наш гід, з яким Марта раніше зустрічалася, розмовляє польською."
  ],
  "m63-omitting-meaning-subject-question": [
    "That — підмет protect і залишається; питання Did you стосується головної частини.",
    "Did you find the gloves that protect your hands?",
    "Ти знайшов рукавички, які захищають твої руки?"
  ],
  "m63-omitting-build-object": [
    "The jacket I tried on was too small.",
    "The jacket I tried on was too small.",
    "Куртка, яку я приміряв, була замала."
  ],
  "m63-omitting-build-preposition": [
    "The neighbour we spoke to owns the garage.",
    "The neighbour we spoke to owns the garage.",
    "Сусід, з яким ми говорили, володіє гаражем."
  ],
  "m63-linking-form-reason": [
    "because",
    "The path was closed because a tree had fallen across it.",
    "Стежку закрили, тому що на неї впало дерево."
  ],
  "m63-linking-form-purpose": [
    "so that",
    "The curator lowered the screen so that the children could see the model.",
    "Куратор опустив екран, щоб діти могли побачити модель."
  ],
  "m63-linking-meaning-boundary": [
    "Варіант Б: крапка розділяє самостійні речення, а however позначає контраст.",
    "The screen was cracked. However, it still worked.",
    "Екран був тріснутий. Проте він досі працював."
  ],
  "m63-linking-meaning-evidence": [
    "Відомий лише порядок у часі; причинний наслідок не встановлено.",
    "At noon the bell rang. At one the parcel arrived.",
    "Опівдні пролунав дзвінок. О першій прибув пакунок."
  ],
  "m63-linking-build-reason": [
    "The seedlings died because the heater failed.",
    "The seedlings died because the heater failed.",
    "Саджанці загинули, тому що обігрівач зламався."
  ],
  "m63-linking-build-purpose": [
    "Danylo brought a ladder to reach the shelf.",
    "Danylo brought a ladder to reach the shelf.",
    "Данило приніс драбину, щоб дістатися до полиці."
  ]
};
const aliases = {
  "m63-nondefining-build-subject": [
    "Tania, who repairs clocks, works upstairs."
  ],
  "m63-nondefining-build-negative": [
    "Our tent, which we bought in May, did not leak.",
    "Our tent, which we bought in May, didn't leak."
  ],
  "m63-omitting-build-object": [
    "The jacket I tried on was too small."
  ],
  "m63-omitting-build-preposition": [
    "The neighbour we spoke to owns the garage."
  ],
  "m63-linking-build-reason": [
    "The seedlings died because the heater failed."
  ],
  "m63-linking-build-purpose": [
    "Danylo brought a ladder to reach the shelf.",
    "Danylo brought a ladder in order to reach the shelf."
  ]
};
const wrong = {
  "m63-nondefining-build-subject": "Tania, repairs clocks, works upstairs.",
  "m63-nondefining-build-negative": "Our tent, which we bought in May, did leak.",
  "m63-omitting-build-object": "The jacket tried on was too small.",
  "m63-omitting-build-preposition": "The neighbour to we spoke owns the garage.",
  "m63-linking-build-reason": "The seedlings failed because the heater died.",
  "m63-linking-build-purpose": "Danylo brought a ladder so reach the shelf."
};
const fullRelativeRejected = {
  'm63-omitting-build-object': [
    'The jacket that I tried on was too small.',
    'The jacket which I tried on was too small.',
  ],
  'm63-omitting-build-preposition': [
    'The neighbour who we spoke to owns the garage.',
    'The neighbour whom we spoke to owns the garage.',
    'The neighbour that we spoke to owns the garage.',
  ],
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
const counts = { own_tasks: 18, options: 0, manual_inputs: 0, token_inputs: 0, accepted_variants: 0, deferred_meaning_translations: 0, grammatical_but_instruction_rejections: 0, checked: new Set() };

test('independent M63 7/7/7 continuing slots, exact semantic maps, owner details and plain 2/2/2 practice', () => {
  assert.equal(master.version,'1.0.0'); assert.equal(master.base_sha,'c8502bc75b9d4258d79824a9be0b343ffbfc07f4');
  assert.deepEqual(lessons.map(l=>l.identity),[
    'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsNonDefiningRelativeClausesTheorySeeder',
    'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsOmittingRelativePronounsTheorySeeder',
    'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsLinkingWordsTheorySeeder']);
  const types=['hero','usage-panels','forms-grid','comparison-table','mistakes-grid','summary-list','navigation-chips','practice-set'];
  const levels=['B1',null,null,null,null,null,null,'B1'];
  const indexes=[0,1,2,3,4,5,6,null];
  const columns=['header','left','left','right','right','right','footer','left'];
  const detail=[['nondefining-whole-message','nondefining-reference','/sections/1'],['omitting-versus-reduction','omitting-boundaries','/items/0'],['linking-since-as-context','linking-functions','/sections/0']];
  const banks=['PolyglotNonDefiningRelativeClausesAllLevelsLessonSeeder','PolyglotOmittingRelativePronounsAllLevelsLessonSeeder','PolyglotLinkingWordsAllLevelsLessonSeeder'];
  const ids=[], explanations=new Set();
  for(const[n,l]of lessons.entries()){
    assert.equal(l.locale,'uk');assert.equal(l.technical_block_level,'B1');assert.equal(l.display_level,'B1-B2');
    assert.deepEqual(l.category_ancestry,['clauses-and-linking-words']);
    assert.deepEqual(l.blocks.map(b=>b.type),types);assert.deepEqual(l.blocks.map(b=>b.level),levels);
    assert.deepEqual(l.blocks.map(b=>b.column),columns);assert.deepEqual(l.blocks.map(b=>b.existing_source_index),indexes);
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
test('default unchanged factory ignores punctuation across selects/choices/inputs; finite UA keys distinguish both A/B cases',()=>{
  const nd=stateFor(lessons[0]),s=nd.s;
  assert.equal(s.normalize('The musicians who read music will join the first rehearsal.'),s.normalize('The musicians, who read music, will join the first rehearsal.'));
  assert.equal(s.normalize('The screen was cracked, however, it still worked.'),s.normalize('The screen was cracked. However, it still worked.'));
  s.inputAnswers[0]='Tania who repairs clocks works upstairs';s.check('inputs');assert.equal(s.isCorrect('inputs',0),true);
  // Explicit LIMITATION: this acceptance is not endorsement of incorrect non-defining punctuation.
  for(const [ln,choiceIndex,needles]of [[0,0,['A: The musicians who read music','B: The musicians, who read music,']],[2,0,['A: The screen was cracked, however,','B: The screen was cracked. However,']]]){
    const f=stateFor(lessons[ln]),state=f.s,t=lessons[ln].data.choices[choiceIndex];
    for(const needle of needles)assert.ok(t.prompt.includes(needle));
    assert.equal(new Set(t.options.map(x=>state.normalize(x))).size,3);
    for(const o of t.options){state.choiceAnswers[choiceIndex]=o;state.check('choices');assert.equal(state.isCorrect('choices',choiceIndex),o===expected[t.id][0]);feedback(t,state.feedbackText('choices',choiceIndex));}
    // The SAME normalize contract also applies to select and choice values, not only manual inputs.
    const artificial=clone(lessons[ln]);artificial.data.selects[0].accepted=['One clause, however, another clause.'];
    const selectState=stateFor(artificial);selectState.s.selectAnswers[0]='One clause. However, another clause.';selectState.s.check('selects');assert.equal(selectState.s.isCorrect('selects',0),true);selectState.unchanged();
    f.unchanged();
  }
  nd.unchanged();
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
        s.inputAnswers[i]=candidate;s.syncInputTokenBank(i);s.check('inputs');assert.equal(s.isCorrect('inputs',i),false);assert.equal(s.scoreText('inputs'),score(0));assert.equal(s.feedbackText('inputs',i),t.feedback[key(candidate)]);feedback(t,s.feedbackText('inputs',i));assert.match(s.feedbackText('inputs',i),/граматична/);assert.match(s.feedbackText('inputs',i),/умов|потрібно|вправі/);counts.grammatical_but_instruction_rejections++;
      }
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
  assert.equal(networkCalls,0);assert.equal(counts.options,36);assert.equal(counts.manual_inputs,6);assert.equal(counts.token_inputs,6);assert.equal(counts.accepted_variants,8);assert.equal(counts.deferred_meaning_translations,6);assert.equal(counts.grammatical_but_instruction_rejections,10);assert.deepEqual([...counts.checked].sort(),Object.keys(expected).sort());
  assert.deepEqual(Object.fromEntries(protectedPaths.map(p=>[p,hash(p)])),protectedBefore);
  const {checked,...totals}=counts;console.log('M63_STATE_COVERAGE '+JSON.stringify({...totals,checked_task_ids:[...checked],network_calls:networkCalls,engine_profile:engineProfile,exact_factory_sha256:'43b4bd3e266594ce7b6ffe697e979ba64c3a824cfb816af06fd8beef31847f8b',source_mode:draft?'draft-only-not-frozen':'frozen-release',punctuation_limitation:'Default matching ignores commas; explicit contextual UA choices distinguish A/B, not comma-sensitive input grading',master_sha256:hash(masterPath),limitation:'Offline shipped factory only; not browser/widget/DB/no-JS/print acceptance.'}));
});
