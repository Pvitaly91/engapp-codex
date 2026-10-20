'use strict';
// Exact shipped factory in an isolated VM: not browser or SQL acceptance.
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict'),crypto=require('node:crypto');
const root=require('node:path').resolve(__dirname,'../..');
const hash=p=>crypto.createHash('sha256').update(fs.readFileSync(p)).digest('hex');
const read=p=>fs.readFileSync(p,'utf8');
const masterPath=root+'/docs/content/m70-intensifiers-noun-basics.v1.0.0.json';
assert.equal(hash(masterPath),'35f566620b34183e982d8d8978730ccc325ad86a6c0e8fb599c76111338a405b');
const draft={master:JSON.parse(read(masterPath))};
assert.equal(draft.master.status,'frozen');
const sourcePath=root+'/resources/views/engram/theory/blocks-v3/practice-set.blade.php';
assert.equal(hash(sourcePath),'05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e');
const scripts=[...read(sourcePath).matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(m=>m[1]).filter(s=>/Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(s));
assert.equal(scripts.length,1);assert.doesNotMatch(scripts[0],/@(?:php|json|js|endphp)\b|\{\{!!?/);
let factory,listener,network=0;
const context=vm.createContext({window:{EnglishAnswerVariants:require(root+'/tests/Browser/load-answer-variants.cjs')},
 document:{addEventListener(n,cb){assert.equal(n,'alpine:init');listener=cb;},querySelector(){return {focus(){}};}},
 Alpine:{data(n,cb){assert.equal(n,'theoryPracticeSet');factory=cb;}},fetch(){network++;throw Error('Network forbidden');},URL,console});
vm.runInContext(scripts[0],context,{filename:sourcePath,timeout:1000});listener();
const groups=['selects','choices','inputs','rephrase'];const props={selects:'selectAnswers',choices:'choiceAnswers',inputs:'inputAnswers',rephrase:'rephraseAnswers'};
const expected=[
 {selects:['big enough','enough time'],choices:['Вага перевищує прийнятну для Олі межу','Мовець не оцінює ціну як надмірну'],inputs:['This bag is too heavy for me.','Do we have enough chairs?'],rephrase:[]},
 {selects:['countries','potatoes','photos','sheep'],choices:['Two pairs of scissors are on the desk.','Sheep злічуване, але має незмінну форму'],
  inputs:['children','knives','mice','heroes','These boxes are on the table.','Do these books have pictures?'],
  rephrase:[null,'There are three boxes on the table.','She gave me useful information.','I bought a new pair of scissors yesterday.']},
 {selects:['U — незлічуване','C — злічуване','U — незлічуване','C — злічуване'],choices:['Дві порції кави','Дві чашки; узгодження з cups'],
  inputs:['many','much','many','I need some advice.','Two bottles of water are on the table.'],
  rephrase:[null,'She gave me a lot of information.','Can I have some milk?']},
];
const make=data=>{const x=factory({...data,i18n:{empty:'Введи відповідь.',correct:'Правильно.',incorrect:'Неправильно.',answer:'Правильна відповідь',score:'Результат: :correct / :total'}});x.$nextTick=cb=>cb();x.init();return x;};
const records=[];let taskCount=0,options=0,aliases=0,tokenTasks=0;
for(const [li,lesson] of draft.master.lessons.entries()){
 const data=lesson.blocks.find(b=>b.type==='practice-set').data,x=make(data);
 for(const g of groups){assert.equal((data[g]||[]).length,expected[li][g].length);const total=expected[li][g].filter(Boolean).length;
  assert.equal(x.scoreText(g),`Результат: 0 / ${total}`);
  for(const [index,item] of (data[g]||[]).entries()){
   const key=expected[li][g][index];if(key===null){assert.equal(x.hasAnswer(g,index),false);continue;}
   assert.equal(item.answer,key,`${li}/${g}/${index} independent key`);taskCount++;
   const rec={lesson:li,group:g,index,canonical:key,aliases:[],options:[],states:[]};
   x[props[g]][index]='';x.check(g);assert.equal(x.isCorrect(g,index),false);assert.equal(x.feedbackText(g,index),'Введи відповідь.');rec.states.push('empty');
   x[props[g]][index]='zzzz wrong';x.check(g);assert.equal(x.isCorrect(g,index),false);const wrong=x.scoreText(g);x.check(g);assert.equal(x.scoreText(g),wrong);rec.states.push('wrong/repeated-check');
   for(const accepted of item.accepted){
    x[props[g]][index]=accepted;x.check(g);assert.equal(x.isCorrect(g,index),true,accepted);const text=x.feedbackText(g,index);
    assert.match(text,/[\u0400-\u04ff]/);assert.doesNotMatch(text,/<\/?[a-z]|&(?:#[0-9]+|[a-z]+);/i);
    rec.aliases.push(accepted);aliases++;
    x[props[g]][index]='  '+accepted.toUpperCase()+'  ';assert.equal(x.isCorrect(g,index),true,'case/space accepted');
   }
   const choices=item.options||(g==='choices'?data.choice_options||['a','b']:[]);
   for(const option of choices){x[props[g]][index]=option;const correct=item.accepted.some(a=>x.normalize(a)===x.normalize(option));
    assert.equal(x.isCorrect(g,index),correct,'option/key normalization');assert.equal(typeof item.feedback[option.toLowerCase().trim()],'string');
    rec.options.push({value:option,correct});options++;}
   if(g==='inputs'&&item.before.includes('/')){
    tokenTasks++;const bank=x.inputTokenBank(index);assert.ok(bank.length>2);const remaining=[...bank];
    for(const word of key.split(/\s+/)){const at=remaining.findIndex(t=>t.value===word);assert.notEqual(at,-1,word+' exact token instance');const t=remaining.splice(at,1)[0];x.appendInputToken('inputs',index,t);}
    // Previous manual answer is intentionally present; reset before independent token assembly.
    x.resetGroup('inputs');const fresh=x.inputTokenBank(index),available=[...fresh];
    for(const word of key.split(/\s+/)){const at=available.findIndex(t=>t.value===word);assert.notEqual(at,-1,word);x.appendInputToken('inputs',index,available.splice(at,1)[0]);}
    assert.equal(x.inputAnswers[index],key);assert.equal(x.isCorrect(g,index),true);const score=x.scoreText(g);x.check(g);x.check(g);assert.equal(x.scoreText(g),score);
    assert.equal(x.inputTokenBank(index).filter(t=>!t.used).length,0);rec.states.push('token-instance-complete/repeated-check');
    x.inputAnswers[index]=key.split(' ').slice(0,-1).join(' ');x.syncInputTokenBank(index);assert.equal(x.isCorrect(g,index),false);assert.ok(x.inputTokenBank(index).some(t=>!t.used));
    x.inputAnswers[index]=key;x.syncInputTokenBank(index);assert.equal(x.isCorrect(g,index),true);rec.states.push('delete/return/rebuild');
   }
   x[props[g]][index]=key;assert.equal(x.isCorrect(g,index),true);const stable=x.scoreText(g);x.check(g);x.check(g);assert.equal(x.scoreText(g),stable);rec.states.push('edit-correct/idempotent-score');
   records.push(rec);
  }
  for(const [n,key] of expected[li][g].entries()){if(key!==null)x[props[g]][n]=key;}
  x.check(g);assert.equal(x.scoreText(g),`Результат: ${total} / ${total}`);
  const other=groups.filter(v=>v!==g).map(v=>[v,x.scoreText(v)]);x.resetGroup(g);assert.equal(x.scoreText(g),`Результат: 0 / ${total}`);
  for(const [v,score] of other)assert.equal(x.scoreText(v),score,'group score independence');
 }
}
assert.equal(taskCount,34);assert.equal(tokenTasks,6);assert.equal(options,38);assert.equal(network,0);
const receipt={kind:'isolated actual native factory, NOT browser acceptance',master_sha256:hash(masterPath),
 factory_sha256:hash(sourcePath),tasks:taskCount,options,accepted_values:aliases,sentence_token_tasks:tokenTasks,records,network_calls:network,db_writes:0};
assert.equal(receipt.master_sha256,hash(masterPath)); // Pure read-only test; no receipts, DB or network writes.
console.log(JSON.stringify({tasks:taskCount,options,accepted_values:aliases,sentence_token_tasks:tokenTasks,network_calls:network,db_writes:0}));
