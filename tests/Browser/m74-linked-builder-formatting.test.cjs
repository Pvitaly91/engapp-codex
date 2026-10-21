'use strict';
// Exact frozen M74 bank-only successor through four unchanged native methods.
// This is offline source execution, NOT a browser/DB/HTTP acceptance substitute.
const {test,after}=require('node:test');
const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),vm=require('node:vm'),crypto=require('node:crypto');
const root=path.resolve(__dirname,'../..'),tracked=new Map();
const hash=b=>crypto.createHash('sha256').update(b).digest('hex');
const read=p=>{const b=fs.readFileSync(path.resolve(root,p));tracked.set(path.resolve(root,p),hash(b));return b.toString('utf8');};
const args=process.argv.slice(2);let audit,mode;
if(args.length){
 assert.equal(args.length,4);assert.equal(args[0],'--candidate');assert.equal(args[2],'--sha256');
 assert.match(args[1],/^storage\/app\/seo-m74-local\/bank-revision-freeze-candidate-v[0-9]+\.json$/);
 assert.match(args[3],/^[a-f0-9]{64}$/);const raw=read(args[1]);assert.equal(hash(raw),args[3]);
 const candidate=JSON.parse(raw);assert.equal(candidate.status,'private-reviewed-input-assembly-not-exported');
 const p='docs/content/m74-other-pronouns-basics-bank-audit.v1.0.1.json';
 assert.equal(hash(candidate.files[p]),candidate.pins[p]);audit=JSON.parse(candidate.files[p]);mode='explicit exact private freeze candidate';
}else{
 const php=read('app/Support/M74BuilderSelectionPresentation.php');
 const constant=n=>{const m=php.match(new RegExp("public const "+n+" = '([^']+)';"));assert.ok(m);return m[1];};
 const p=constant('AUDIT'),pin=constant('AUDIT_SHA');
 assert.equal(p,'docs/content/m74-other-pronouns-basics-bank-audit.v1.0.1.json');assert.match(pin,/^[a-f0-9]{64}$/);
 const raw=read(p);assert.equal(hash(raw),pin);audit=JSON.parse(raw);mode='frozen active public bank revision';
}
assert.equal(audit.status,'frozen');assert.equal(audit.version,'1.0.1');
assert.equal(audit.package,'m74-other-pronouns-bank-audit');
const fixtureText=read('tests/Fixtures/m73-owned-source-hunks.json');
assert.equal(hash(fixtureText),'a63f7829d4d0009b8ce615af46ced46a2065659781df8834abbcb7177917bf0a');
const fixture=JSON.parse(fixtureText),bladePath='resources/views/components/text-block-practice-questions.blade.php';
const pin=fixture.visual_sources.find(x=>x.path===bladePath);assert.ok(pin);
assert.equal(pin.base_lf_sha256,'c224042fcc5fe767064a5b175369c020de4cb61ecfbdf7f73f9e5679385795f6');
const blade=read(bladePath).replace(/\r\n/g,'\n');assert.equal(hash(blade),pin.base_lf_sha256);
const caseSource=read('app/Support/ComposeTokenCase.php');
// Two independently reviewed, unchanged checkout EOL profiles; no arbitrary raw fallback.
assert.ok([
 '8cf7d1ad3972b571285b7d621f23d87e7efaf3cd76126b4843b7d6ae85be7923',
 'f1ba518446fe46e1927142cfc94454ec2b65dc389c4e3271c526c0ab4957d6d8',
].includes(hash(caseSource)));
assert.equal(hash(caseSource.replace(/\r\n/g,'\n')),'851c87ba65ad47763e91b6c43434323d9d3a11aa4fa826a5f116b13650a14852');
const first='                composeCorrectText() {',last='                normalizeAnswer(value) {';
assert.equal(blade.split(first).length,2);assert.equal(blade.split(last).length,2);
const methods=blade.slice(blade.indexOf(first),blade.indexOf(last));assert.ok(methods.length<5000);
assert.doesNotMatch(methods,/@(?:php|json|js|endphp)\b|\{\{!!?/);
let network=0;
const native=new vm.Script('({'+methods+'})',{filename:bladePath+':actual-unchanged-methods'}).runInNewContext({fetch(){network++;throw Error('Network forbidden');}},{timeout:1000});
assert.deepEqual(Object.keys(native),['composeCorrectText','composeSelectedText','composeTextFromTokens','composePunctuation']);
const empty='4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945';
const ids=[[32707,32708,32711,32714,59720],[33123,33126,33128,33131,33133],[51184,51185,51187,51188,51189]];
// Independent complete expected outputs and translations, never harvested from formatter output.
const expected=new Map([
 [32707,['I need another minute to check this.','Мені потрібна ще одна хвилина, щоб це перевірити.']],
 [32708,['We should discuss other ideas too.','Нам також слід обговорити інші ідеї.']],
 [32711,['Can I have another cup of tea?','Можна мені іншу чашку чаю?']],
 [32714,['We visited three other cities last year.','Торік ми відвідали три інші міста.']],
 [59720,['The others are outside.','Решта надворі.']],
 [33123,['I am ready for class at school.','Я готовий до заняття в школі.']],
 [33126,['We invited them to lunch at school.','Ми запросили їх на обід у школі.']],
 [33128,['Their office is upstairs at school.','Їхній кабінет у школі — нагорі.']],
 [33131,['Marta prepared the report by herself at school.','Марта без сторонньої допомоги підготувала звіт у школі.']],
 [33133,['I found the ticket and put it on the desk at school.','Я знайшов квиток і поклав його на стіл у школі.']],
 [51184,['She leads the meeting today.','Сьогодні вона веде зустріч.']],
 [51185,['The teacher asked me today.','Сьогодні вчитель запитав мене.']],
 [51187,['We thanked her for the help today.','Сьогодні ми подякували їй за допомогу.']],
 [51188,['The message is for us today.','Сьогодні це повідомлення для нас.']],
 [51189,['Please sit next to them today.','Будь ласка, сядь сьогодні поруч із ними.']],
]);
const rows=audit.targets.flatMap((target,i)=>{assert.deepEqual(target.expected_selected_ids,ids[i]);return target.first_five;});
assert.deepEqual(rows.map(q=>q.id),ids.flat());assert.equal(new Set(rows.map(q=>q.id)).size,15);
function model(q){return{...native,currentQuestion:{id:q.id,type:'4',question:q.visible_condition_uk,authored_compose:false,compose_punctuation:null,correct_tokens:[...q.correct_tokens]},selectedTokens:q.correct_tokens.map((value,i)=>({id:i+'-'+value,value}))};}

test('whole accepted native formatter and case sources are unchanged',()=>{
 assert.equal(hash(blade),pin.base_lf_sha256);assert.equal(network,0);
 assert.deepEqual(Object.keys(native),['composeCorrectText','composeSelectedText','composeTextFromTokens','composePunctuation']);
});
test('finite revision has157 exact candidates37 reviews120 scope exclusions and14 reused plus1 new selection',()=>{
 assert.equal(audit.review_summary.actual_filtered_pool,157);assert.equal(audit.review_summary.candidate_semantic_review,37);
 assert.equal(audit.review_summary.scope_excluded_without_semantic_review,120);
 assert.deepEqual(audit.targets.map(t=>t.all_pool_snapshots.length),[13,72,72]);
 assert.deepEqual(audit.targets.map(t=>t.all_eligible_review.length),[13,12,12]);
 assert.deepEqual(audit.targets.map(t=>t.scope_exclusions.length),[0,60,60]);
 assert.equal(audit.scope.selected_questions,15);assert.equal(audit.scope.reused_questions,14);assert.equal(audit.scope.new_questions,1);
 assert.equal(rows.filter(q=>q.is_new).length,1);assert.equal(rows.find(q=>q.is_new).id,59720);
 assert.equal(rows.some(q=>q.id===32712),false);
});
for(const q of rows)test(q.id+': exact correct and selected native sentences plus bilingual meaning',()=>{
 const [en,uk]=expected.get(q.id);assert.equal(q.translation_uk,uk);
 assert.equal(q.correct_tokens.join(' '),en.replace(/[?.!]$/,''));
 assert.equal(q.expected_word_key,en.replace(/[?.!]$/,''));
 assert.deepEqual(q.original_ordered_instances.map(x=>x.option),q.raw_correct_tokens);
 assert.equal(q.snapshot_hashes.hints_snapshot,empty);assert.equal(q.snapshot_hashes.verb_hints_snapshot,empty);
 assert.deepEqual(q.token_presentations,[]);assert.deepEqual(q.option_presentations,[]);
 assert.ok(/[А-Яа-яІіЇїЄєҐґ]/u.test(q.visible_condition_uk));
 assert.equal(q.actual_correct_visible,en);assert.equal(q.actual_selected_visible,en);
 const m=model(q);assert.equal(m.composeCorrectText(),en);assert.equal(m.composeSelectedText(),en);
 for(const token of q.correct_tokens)assert.ok(q.token_bank_with_multiplicity.filter(t=>t===token).length>=q.correct_tokens.filter(t=>t===token).length);
 assert.equal(network,0);
});
test('replacement and known remainder are explicit before Check without changing old32711 tokens',()=>{
 const tea=rows.find(q=>q.id===32711),rest=rows.find(q=>q.id===59720);
 assert.match(tea.visible_condition_uk,/замість/);assert.match(tea.visible_condition_uk,/не питимеш/);assert.ok(tea.visible_condition_uk.endsWith('?'));
 assert.match(rest.visible_condition_uk,/п’ятеро/);assert.match(rest.visible_condition_uk,/Двоє/);assert.match(rest.visible_condition_uk,/усі троє/);
 const old=JSON.parse(read('docs/content/m74-other-pronouns-basics-bank-audit.v1.json'));
 for(const q of rows.filter(x=>!x.is_new)){
  const original=old.targets.flatMap(t=>t.first_five).find(x=>x.id===q.id);assert.ok(original);
  const copy=JSON.parse(JSON.stringify(q));
  if(q.id===32711){copy.visible_condition_uk=original.visible_condition_uk;copy.translation_uk=original.translation_uk;}
  assert.deepEqual(copy,original,'No existing row/token/snapshot field changes for '+q.id);
 }
});
test('repeated original article instances and complete assembled answer are not deduplicated',()=>{
 const q=rows.find(q=>q.id===33133),m=model(q);
 assert.equal(q.correct_tokens.filter(t=>t==='the').length,2);
 const instances=q.original_ordered_instances.filter(t=>t.option.toLowerCase()==='the');assert.equal(instances.length,2);
 assert.notEqual(instances[0].id,instances[1].id);assert.equal(new Set(m.selectedTokens.map(t=>t.id)).size,m.selectedTokens.length);
 m.selectedTokens.splice(m.selectedTokens.findIndex(t=>t.value==='the'),1);
 assert.notEqual(m.composeSelectedText(),expected.get(q.id)[0]);
});
test('actual negative controls still expose bad case and a closing quote after question mark',()=>{
 const old=audit.targets[0].all_eligible_review.find(q=>q.id===32713);assert.ok(old);assert.equal(old.selected,false);
 const q={id:old.id,visible_condition_uk:old.visible_condition_uk,correct_tokens:old.native_case_word_key.split(' ')};
 assert.equal(model(q).composeCorrectText(),'The other Students stayed outside.');
 assert.notEqual(model(q).composeCorrectText(),'The other students stayed outside.');
 const tea={...rows.find(q=>q.id===32711)};tea.visible_condition_uk+='»';
 assert.equal(model(tea).composeCorrectText(),'Can I have another cup of tea.');
 assert.notEqual(model(tea).composeCorrectText(),expected.get(32711)[0]);
});
after(()=>{
 assert.equal(network,0);for(const[p,sha]of tracked)assert.equal(hash(fs.readFileSync(p)),sha,'Read source unchanged: '+p);
 console.log(JSON.stringify({mode,kind:'offline unchanged native formatter; NOT browser acceptance',selected_questions:15,correct_and_selected_strings:30,new_questions:1,reused_questions:14,network_calls:0,db_access:0,file_writes:0}));
});
