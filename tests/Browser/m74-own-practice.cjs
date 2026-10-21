'use strict';
// M74 own practice in the unchanged native factory. NOT live browser acceptance.
// No app bootstrap, .env, SQL, HTTP, progress or file writes.
// Default: frozen public package. Draft: --projection ABSOLUTE_PATH --sha256 RAW_SHA256.
const fs=require('node:fs'), path=require('node:path'), vm=require('node:vm');
const assert=require('node:assert/strict'), crypto=require('node:crypto');
const root=path.resolve(__dirname,'../..');
const read=p=>fs.readFileSync(p,'utf8'), digest=b=>crypto.createHash('sha256').update(b).digest('hex');
const hash=p=>digest(fs.readFileSync(p)), tracked=new Map();
const track=p=>{const s=hash(p);tracked.set(p,s);return s;};
const args=process.argv.slice(2);
let master,before,mode,masterSha,beforeSha,projectionSha=null;
if(args.length){
 assert.equal(args.length,4,'Explicit draft projection path and raw SHA are required');
 assert.equal(args[0],'--projection');assert.equal(args[2],'--sha256');
 assert.ok(path.isAbsolute(args[1]));assert.match(args[3],/^[a-f0-9]{64}$/);
 const p=path.resolve(args[1]);projectionSha=track(p);assert.equal(projectionSha,args[3]);
 const x=JSON.parse(read(p));assert.equal(x.status,'private-draft-not-frozen');
 assert.equal(x.master.status,'private-draft-not-frozen');assert.equal(x.own_graded_tasks,35);
 master=x.master;before=x.source_before;
 masterSha=digest(Buffer.from(JSON.stringify(master)));beforeSha=digest(Buffer.from(JSON.stringify(before)));
 mode='explicit private projection; NOT frozen or browser acceptance';
}else{
 const p=path.join(root,'app/Support/M74OtherPronounsBasics.php');track(p);const source=read(p);
 const constant=name=>{const m=source.match(new RegExp("public const "+name+" = '([^']+)';"));assert.ok(m,'Missing active '+name);return m[1];};
 const pin=name=>{const value=constant(name);assert.match(value,/^[a-f0-9]{64}$/);assert.notEqual(value,'0'.repeat(64));return value;};
 const activePath=name=>{const value=constant(name);assert.match(value,/^(?:docs\/content\/|database\/content-patches\/)[A-Za-z0-9./-]+\.json$/);assert.ok(!value.includes('..'));return path.join(root,value);};
 const mf=activePath('CHECKSUMS');assert.equal(track(mf),pin('CHECKSUMS_SHA'));
 const manifest=JSON.parse(read(mf));assert.equal(manifest.status,'frozen');assert.equal(manifest.package,'m74-other-pronouns-basics');
 const mp=activePath('MASTER'),bp=activePath('BEFORE');
 masterSha=track(mp);beforeSha=track(bp);assert.equal(masterSha,pin('MASTER_SHA'));assert.equal(beforeSha,pin('BEFORE_SHA'));
 for(const p of[mp,bp]){const relative=path.relative(root,p).split(path.sep).join('/');const a=manifest.artifacts.find(x=>x.path===relative);assert.ok(a);assert.equal(hash(p),a.sha256);assert.equal(fs.statSync(p).size,a.bytes);}
 master=JSON.parse(read(mp));before=JSON.parse(read(bp));assert.equal(master.status,'frozen');assert.equal(master.version,'1.0.1');assert.equal(manifest.version,'1.0.1');
 const oldPath=path.join(root,'docs/content/m74-other-pronouns-basics.v1.0.0.json');
 assert.equal(track(oldPath),'9d7a4183793d4005c59ef400ef191990a2fe7ba07a32fc4b363eb457f1618671','Historical frozen 1.0.0 raw bytes never rewritten');
 const legacy=JSON.parse(read(oldPath)),restored=JSON.parse(JSON.stringify(master));
 const banks=restored.lessons[0].blocks[6].data.linked_practice.seeder_classes;
 assert.deepEqual(banks,['Database\\Seeders\\V3\\Polyglot\\PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder','Database\\Seeders\\V3\\Polyglot\\M74AnotherRemainderSupplementSeeder']);
 banks.pop();restored.version=legacy.version;
 assert.deepEqual(restored,legacy,'Only bank linkage and master version differ; all 35 own tasks and teaching data remain exact');
 mode='frozen active 1.0.1 bank-only successor; historical 1.0.0 preserved; NOT browser acceptance';
}
assert.equal(master.base_sha,'fd1ad30e710b3d950e574848c886d86f6045f2e3');assert.equal(before.base_sha,master.base_sha);
assert.equal(before.content_type,'M74_source_before');assert.equal(master.lessons.length,3);assert.equal(before.targets.length,3);
const sourcePath=path.join(root,'resources/views/engram/theory/blocks-v3/practice-set.blade.php');
const sourceSha=track(sourcePath);assert.equal(sourceSha,'05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e','Unchanged accepted native factory');
const scripts=[...read(sourcePath).matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(m=>m[1]).filter(s=>/Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(s));
assert.equal(scripts.length,1);assert.doesNotMatch(scripts[0],/@(?:php|json|js|endphp)\b|\{\{!!?/);
const variantsPath=path.join(root,'tests/Browser/load-answer-variants.cjs'),variantsSha=track(variantsPath);
track(path.join(root,'public/js/english-answer-variants.js'));track(path.join(root,'public/data/english-contractions.json'));
let factory,listener,networkCalls=0;
const context=vm.createContext({window:{EnglishAnswerVariants:require(variantsPath)},document:{
 addEventListener(n,f){assert.equal(n,'alpine:init');listener=f;},querySelector(){return{focus(){}};}
},Alpine:{data(n,f){assert.equal(n,'theoryPracticeSet');factory=f;}},fetch(){networkCalls++;throw Error('Network forbidden');},URL});
vm.runInContext(scripts[0],context,{filename:sourcePath,timeout:1000});assert.equal(typeof listener,'function');listener();assert.equal(typeof factory,'function');
const make=data=>{const m=factory({...data,i18n:{empty:'Введи відповідь.',correct:'Правильно.',incorrect:'Неправильно.',answer:'Правильна відповідь',score:'Результат: :correct / :total'}});m.$nextTick=c=>c();m.init();return m;};
const groups=['selects','choices','inputs','rephrase'],props={selects:'selectAnswers',choices:'choiceAnswers',inputs:'inputAnswers',rephrase:'rephraseAnswers'};
// Independent reviewed canonicals. Never build expectations from AFTER payload.
const expected=[
 {identity:'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAnotherOtherTheOtherTheorySeeder',
  continuing:6,slot:6,appended:1,level:'B1',old:{},newTasks:6,
  selects:['another','other'],choices:['A','B'],inputs:['I need some others.','The other chairs are by the window.'],rephrase:[],tokens:[0,1]},
 {identity:'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPronounsTheorySeeder',
  continuing:12,slot:10,appended:0,level:null,old:{selects:4,inputs:3,rephrase:3},newTasks:4,
  selects:['I','mine','herself','This'],choices:['Вона представила себе.','Кожен допомагає іншому учасникові.'],
  inputs:['I','me','my','This bag is mine, and that notebook is yours.','We help each other with our homework.'],
  rephrase:[null,'речення Б','This book is mine.'],tokens:[3,4]},
 {identity:'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPersonalObjectPronounsTheorySeeder',
  continuing:10,slot:8,appended:0,level:null,old:{selects:5,inputs:4,rephrase:4},newTasks:4,
  selects:['I','me','She','us','They'],choices:['A','B'],
  inputs:['I','her','We','them','Does she know him?','My friend and I do not know them.'],
  rephrase:[null,'She is my sister.','Come with me.','They are students.'],tokens:[4,5]}
];
const nativeBefore=before.targets.map((t,i)=>{
 const definition=t.before;assert.equal(t.identity,expected[i].identity);assert.equal(definition.seeder.class,t.identity);
 const b=definition.page.blocks.find(x=>x.type==='practice-set');return b?JSON.parse(b.body):null;
});
assert.equal(nativeBefore[0],null,'Another source did not have a practice');
const beforeProbe=[];
{
 const old=make(nativeBefore[1]);
 assert.equal(old.normalize('Its a beautiful day.'),old.normalize("It's a beautiful day."));
 assert.equal(old.normalize('I'),old.normalize('i'));assert.equal(old.normalize('This'),old.normalize('this'));
 for(const a of['Its a beautiful day.',"It's a beautiful day."]){old.rephraseAnswers[1]=a;old.check('rephrase');assert.equal(old.isCorrect('rephrase',1),true);beforeProbe.push({case:'old apostrophe collision',answer:a,correct:true});}
 for(const[g,i,a]of[['selects',0,'i'],['selects',3,'this'],['inputs',0,'i']]){old[props[g]][i]=a;old.check(g);assert.equal(old.isCorrect(g,i),true);beforeProbe.push({case:'case-insensitive display boundary',group:g,index:i,answer:a,correct:true});}
 const personal=make(nativeBefore[2]),options=nativeBefore[2].selects[2].options||nativeBefore[2].options;
 assert.equal(options.length,10);const he=options.find(v=>v.toLowerCase()==='he');assert.ok(he);
 personal.selectAnswers[2]=he;personal.check('selects');assert.equal(personal.isCorrect('selects',2),false);
 beforeProbe.push({case:'broad options include context-free HE but old key only She',option:he,correct:false});
}
function plain(value){assert.equal(typeof value,'string');assert.match(value,/[\u0400-\u04ff]/);assert.doesNotMatch(value,/<\/?[a-z]|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu);}
const unicodeAlias=v=>v.replace(/[A-Za-z]/g,c=>String.fromCharCode(c.charCodeAt(0)+0xfee0)).replace(/'/g,'\u2019').replace(/ /g,'\u00a0').normalize('NFD');
function assemble(m,index,key,partial){
 const remaining=[...m.inputTokenBank(index)],words=key.split(/\s+/);
 assert.ok(remaining.length>1);assert.equal(new Set(remaining.map(t=>t.id)).size,remaining.length);
 for(const[pos,word]of words.entries()){
  const at=remaining.findIndex(t=>t.value===word);assert.notEqual(at,-1,'Exact case/multiplicity token '+word);
  const t=remaining.splice(at,1)[0];m.appendInputToken('inputs',index,t);
  const current=m.inputAnswers[index];m.appendInputToken('inputs',index,t);assert.equal(m.inputAnswers[index],current,'Used instance cannot be appended twice');
  if(partial&&pos<words.length-1){m.check('inputs');assert.equal(m.isCorrect('inputs',index),false);}
 }
 assert.equal(remaining.length,0,'All authored tokens belong to canonical');assert.equal(m.inputAnswers[index],key);assert.equal(m.isCorrect('inputs',index),true);
}
let taskCount=0,optionCount=0,acceptedCount=0,demos=0,retained=0,added=0,manual=0,tokenPaths=0;
const ids=new Set(),results=[],records=[];
for(const[li,e]of expected.entries()){
 const lesson=master.lessons[li],old=before.targets[li].before;
 assert.equal(lesson.identity,e.identity);assert.equal(lesson.locale,'uk');assert.equal(old.page.blocks.length,e.continuing);assert.equal(lesson.blocks.length,e.continuing+e.appended);
 for(const[i,b]of old.page.blocks.entries()){
  const after=lesson.blocks[i];assert.equal(after.existing_source_index,i);assert.equal(after.type,b.type);assert.equal(after.column,b.column);
  assert.equal(after.level??null,b.level??null,'Continuing SQL semantics exact; source absence not a new inferred transition');
 }
 assert.equal(lesson.blocks.filter(b=>b.type==='practice-set').length,1);
 const practice=lesson.blocks[e.slot];assert.equal(practice.type,'practice-set');assert.equal(practice.column,'left');assert.equal(practice.level??null,e.level);
 if(e.appended)assert.equal(practice.existing_source_index,null);
 const data=practice.data,model=make(data);
 for(const g of groups)assert.equal((data[g]||[]).length,e[g].length,'Exact group count '+li+'/'+g);
 assert.deepEqual((data.inputs||[]).flatMap((t,i)=>typeof t.before==='string'&&t.before.includes('/')?[i]:[]),e.tokens);
 for(const[g,count]of Object.entries(e.old))for(let i=0;i<count;i++){
  const t=data[g][i],prior=nativeBefore[li][g][i];assert.equal(Object.hasOwn(t,'id'),Object.hasOwn(prior,'id'),'Retained ID absence');
  assert.equal(Object.hasOwn(t,'source_index'),Object.hasOwn(prior,'source_index'),'Retained source index absence');
  if(Object.hasOwn(prior,'id'))assert.equal(t.id,prior.id);if(Object.hasOwn(prior,'source_index'))assert.equal(t.source_index,prior.source_index);
 }
 if(li>0){assert.ok(data.rephrase[0].example_original&&data.rephrase[0].example_target);assert.equal(Object.hasOwn(data.rephrase[0],'answer'),false);assert.equal(Object.hasOwn(data.rephrase[0],'accepted'),false);}
 for(const g of groups){
  const items=data[g]||[],total=e[g].filter(x=>x!==null).length;
  assert.equal(model.scoreText(g),'Результат: 0 / '+total);
  for(const[i,t]of items.entries()){
   const key=e[g][i],prop=props[g];if(key===null){demos++;assert.equal(model.hasAnswer(g,i),false);continue;}
   taskCount++;const oldPosition=i<(e.old[g]||0);
   if(oldPosition)retained++;else{added++;assert.match(t.id,/^m74-[a-z0-9-]+$/);assert.ok(!ids.has(t.id));ids.add(t.id);}
   assert.equal(t.answer,key,'Independent canonical '+li+'/'+g+'/'+i);assert.ok(Array.isArray(t.accepted)&&t.accepted.includes(key));assert.ok(t.accepted.every(a=>typeof a==='string'&&a.trim()));
   assert.equal(t.punctuation_sensitive,undefined,'No new strict grader flag');assert.equal(t.m38_semantic_checks,undefined);
   const m=make(data),r={lesson:li,group:g,index:i,canonical:key,actual_options:[]};
   for(const blank of['',' ','\t',' \n ','\u00a0']){m[prop][i]=blank;m.check(g);assert.equal(m.isCorrect(g,i),false);assert.equal(m.feedbackText(g,i),'Введи відповідь.');assert.equal(m.scoreText(g),'Результат: 0 / '+total);}
   m[prop][i]='zzzz wrong';m.check(g);assert.equal(m.isCorrect(g,i),false);const wrong=m.scoreText(g);m.check(g);assert.equal(m.scoreText(g),wrong);plain(m.feedbackText(g,i));
   for(const alias of t.accepted){
    assert.equal(typeof t.feedback[alias.trim().toLowerCase()],'string','Actual literal-lower feedback key exists: '+alias);
    for(const a of[alias,'  '+alias.toUpperCase()+'  ',alias.replace(/[.!?]+$/,'')+'!!!',unicodeAlias(alias)]){m[prop][i]=a;m.check(g);assert.equal(m.isCorrect(g,i),true,'Alias/actual normalization '+a);plain(m.feedbackText(g,i));}
    m[prop][i]=alias;m.check(g);assert.equal(m.feedbackText(g,i),t.feedback[alias.trim().toLowerCase()],'Canonical/alias gets authored feedback, not generic correct');acceptedCount++;
   }
   for(const f of Object.values(t.feedback||{}))plain(f);
   if(g==='selects'||g==='choices'){
    const options=t.options||(g==='selects'?data.options:data.choice_options)||[];
    assert.ok(options.length>=2&&options.includes(key));assert.ok(options.every(x=>typeof x==='string'&&m.normalize(x)));
    assert.equal(new Set(options.map(x=>m.normalize(x))).size,options.length,'Normalized correct/distractor collision');
    let correct=0;
    for(const o of options){
     m[prop][i]=o;m.check(g);const valid=t.accepted.some(a=>windowVariants(a).some(v=>m.normalize(v)===m.normalize(o)));
     assert.equal(m.isCorrect(g,i),valid);assert.equal(typeof t.feedback[o.trim().toLowerCase()],'string','All displayed options have actual plain feedback');
     assert.equal(m.feedbackText(g,i),t.feedback[o.trim().toLowerCase()]);plain(m.feedbackText(g,i));correct+=Number(valid);optionCount++;r.actual_options.push({value:o,correct:valid});
    }
    assert.equal(correct,1);
   }
   if(g==='inputs'&&e.tokens.includes(i)){
    assert.match(t.after,/[\u0400-\u04ff]/,'Visible Ukrainian full-sentence instruction');assert.ok(!t.after.includes(key),'No ready English solution in condition');assert.doesNotMatch(key,/[\u0400-\u04ff]/);
    m.resetGroup(g);const otherBanks=data.inputs.map((_,n)=>n===i?null:JSON.stringify(m.inputTokenBank(n)));
    assemble(m,i,key,true);tokenPaths++;
    for(let n=0;n<data.inputs.length;n++)if(n!==i){assert.equal(JSON.stringify(m.inputTokenBank(n)),otherBanks[n]);assert.equal(m.inputAnswers[n],undefined);}
    assert.equal(m.inputTokenBank(i).filter(t=>!t.used).length,0);
    const words=key.split(/\s+/);m.inputAnswers[i]=words.slice(0,-1).join(' ');m.syncInputTokenBank(i);m.check(g);assert.equal(m.isCorrect(g,i),false);
    const returned=m.inputTokenBank(i).find(t=>!t.used&&t.value===words.at(-1));assert.ok(returned);m.appendInputToken('inputs',i,returned);assert.equal(m.inputAnswers[i],key);assert.equal(m.isCorrect(g,i),true);
    const score=m.scoreText(g);m.check(g);m.check(g);assert.equal(m.scoreText(g),score);
    m.resetGroup(g);m.inputAnswers[i]=key;m.syncInputTokenBank(i);m.check(g);assert.equal(m.isCorrect(g,i),true);assert.equal(m.inputTokenBank(i).filter(t=>!t.used).length,0);manual++;
   }
   m[prop][i]=key;m.check(g);assert.equal(m.isCorrect(g,i),true);const correct=m.scoreText(g);m.check(g);assert.equal(m.scoreText(g),correct);assert.equal(correct,'Результат: 1 / '+total);
   m.resetGroup(g);assert.equal(m.scoreText(g),'Результат: 0 / '+total);m.check(g);assert.equal(m.isCorrect(g,i),false);records.push(r);
  }
 }
 const active=groups.filter(g=>e[g].length),mixed=make(data);
 for(const g of active){const indices=e[g].flatMap((k,i)=>k===null?[]:[i]);mixed[props[g]][indices[0]]='zzzz wrong';mixed[props[g]][indices[1]]=e[g][indices[1]];mixed.check(g);assert.equal(mixed.scoreText(g),'Результат: 1 / '+indices.length);}
 mixed.resetGroup('inputs');mixed.check('inputs');assert.equal(mixed.scoreText('inputs'),'Результат: 0 / '+e.inputs.length);
 for(const g of active.filter(g=>g!=='inputs'))assert.equal(mixed.scoreText(g),'Результат: 1 / '+e[g].filter(k=>k!==null).length);
 const full=make(data);
 for(const g of active){for(const[i,k]of e[g].entries())if(k!==null)full[props[g]][i]=k;full.check(g);const n=e[g].filter(k=>k!==null).length;assert.equal(full.scoreText(g),'Результат: '+n+' / '+n);}
 for(const g of active){const others=active.filter(x=>x!==g).map(x=>[x,full.scoreText(x)]);full.resetGroup(g);full.check(g);assert.equal(full.scoreText(g),'Результат: 0 / '+e[g].filter(k=>k!==null).length);for(const[x,s]of others)assert.equal(full.scoreText(x),s);}
 results.push({identity:e.identity,continuing:e.continuing,practice_source_index:e.slot,append:e.appended,graded:e.selects.length+e.choices.length+e.inputs.length+e.rephrase.filter(k=>k!==null).length,token_indices:e.tokens});
}
function windowVariants(v){return require(variantsPath).variants(v);}
{
 const data=master.lessons[1].blocks[10].data,m=make(data),t=data.rephrase[1];
 assert.equal(t.answer,'речення Б');assert.deepEqual(t.accepted,['речення Б','Б']);
 assert.match(t.original,/Its a beautiful day\./);assert.match(t.original,/It's a beautiful day\./);
 assert.notEqual(m.normalize('речення А'),m.normalize('речення Б'));
 for(const a of['речення А','А','Its a beautiful day.',"It's a beautiful day.",'',' ']){m.rephraseAnswers[1]=a;m.check('rephrase');assert.equal(m.isCorrect('rephrase',1),false,'Transferred task rejects wrong semantic label and old English stimuli');}
 for(const a of t.accepted){m.rephraseAnswers[1]=a;m.check('rephrase');assert.equal(m.isCorrect('rephrase',1),true);assert.equal(m.scoreText('rephrase'),'Результат: 1 / 2');}
 m.rephraseAnswers[2]='This book is mine.';m.check('rephrase');assert.equal(m.scoreText('rephrase'),'Результат: 2 / 2');m.check('rephrase');assert.equal(m.scoreText('rephrase'),'Результат: 2 / 2');
 // Real authored repeated token instances, not a synthetic extra M74 task.
 const repeated=m.inputTokenBank(3).filter(t=>t.value==='is');assert.equal(repeated.length,2);assert.notEqual(repeated[0].id,repeated[1].id);
 m.inputAnswers[3]='This bag is mine, and that notebook yours.';m.syncInputTokenBank(3);assert.equal(m.isCorrect('inputs',3),false);assert.equal(m.inputTokenBank(3).filter(t=>t.value==='is'&&!t.used).length,1);
 m.resetGroup('inputs');assert.equal(m.inputTokenBank(3).filter(t=>t.value==='is'&&!t.used).length,2);
}
const aliasCases=[
 [0,0,'I need some other ones.',true],[0,0,'I need some other.',false],[0,0,'I need the others.',false],
 [0,1,'The others chairs are by the window.',false],[0,1,'The other chairs is by the window.',false],
 [1,4,'We help one another with our homework.',true],[1,4,'We help ourselves with our homework.',false],
 [2,5,"My friend and I don't know them.",true],[2,5,'My friend and me do not know them.',false],
 [2,5,'My friend and I do not know they.',false],[2,4,'Does her know him?',false],[2,4,'Does she know he?',false]
];
for(const[li,i,value,valid]of aliasCases){const m=make(master.lessons[li].blocks[expected[li].slot].data);m.inputAnswers[i]=value;m.check('inputs');assert.equal(m.isCorrect('inputs',i),valid,'Reviewed task-bound alternative '+value);plain(m.feedbackText('inputs',i));}
const contraction=make({inputs:[{answer:'It has stopped raining.',accepted:['It has stopped raining.']}]});
for(const value of["It's stopped raining.",'It’s stopped raining.']){contraction.inputAnswers[0]=value;contraction.check('inputs');assert.equal(contraction.isCorrect('inputs',0),true,'Existing contraction expansion stays supported');}
assert.equal(taskCount,35);assert.equal(demos,2);assert.equal(retained,21);assert.equal(added,14);assert.equal(ids.size,14);
assert.equal(optionCount,36);assert.equal(acceptedCount,39);assert.equal(manual,6);assert.equal(tokenPaths,6);assert.equal(networkCalls,0);
for(const[p,s]of tracked)assert.equal(hash(p),s,'Read source bytes unchanged: '+p);
console.log(JSON.stringify({kind:'isolated unchanged native factory; NOT browser acceptance',mode,frozen:args.length===0,
 private_projection_sha256:projectionSha,master_sha256:masterSha,before_source_sha256:beforeSha,factory_sha256:sourceSha,answer_variant_loader_sha256:variantsSha,
 lessons:results,graded_own_tasks:taskCount,retained_functions:retained,semantic_transfers:1,new_tasks:added,ungraded_demos:demos,
 visible_options:optionCount,accepted_values:acceptedCount,full_sentence_manual_paths:manual,full_sentence_token_paths:tokenPaths,
 before_probe:beforeProbe,task_bound_alias_cases:aliasCases.length,real_repeated_token_instances:true,
 literal_and_normalized_feedback_boundary_checked:true,group_score_reset_independence:true,
 network_calls:networkCalls,db_writes:0,file_writes:0,browser_visibility:'not_checked_by_this_script',browser_interaction:'not_checked_by_this_script'}));
