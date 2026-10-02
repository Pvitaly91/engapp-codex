'use strict';
const {test}=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const root=path.resolve(__dirname,'../..');
const source=fs.readFileSync(path.join(root,'resources/views/engram/theory/blocks-v3/practice-set.blade.php'),'utf8');
const script=source.slice(source.indexOf('<script>')+8,source.lastIndexOf('</script>'));
const packageData=JSON.parse(fs.readFileSync(path.join(root,'database/content-patches/m27-m11-linking-words.v1.json'),'utf8'));
let factory,network=0;
vm.runInNewContext(script,{document:{addEventListener:(_,fn)=>fn(),querySelector:()=>null},
 Alpine:{data:(_,fn)=>{factory=fn;}},window:{EnglishAnswerVariants:require('./load-answer-variants.cjs')},
 fetch:()=>{network++;throw Error('Unexpected fetch');},setTimeout,clearTimeout});
function state(data){const s=factory({...data,i18n:{score:':correct / :total'}});s.$nextTick=fn=>fn();s.init();return s;}
for(const target of packageData.targets){
 const data=JSON.parse(target.after.page.blocks.find(b=>b.type==='practice-set').body);
 for(const group of ['selects','choices','inputs'])test(`${target.slug}: ${group}, accepted alternatives, wrong, reset`,()=>{
  const s=state(data),answers=s.answerSource(group);
  data[group].forEach((item,i)=>{
   for(const answer of item.accepted||[item.answer]){
    answers[i]=answer;assert.equal(s.isCorrect(group,i),true);
    if(group==='inputs'){answers[i]=answer.replace(/[.!?]+$/,'');assert.equal(s.isCorrect(group,i),true);}
   }
   answers[i]=item.answer;
  });
  s.check(group);assert.equal(s.scoreText(group),'2 / 2');
  answers[0]='wrong answer';assert.equal(s.isCorrect(group,0),false);assert.equal(s.scoreText(group),'1 / 2');
  s.resetGroup(group);assert.equal(s.isChecked(group),false);assert.equal(Object.keys(s.answerSource(group)).length,0);
 });
 test(`${target.slug}: token click, manual editing, backspace reuse, no suggestions/fetch`,()=>{
  const s=state(data);
  data.inputs.forEach((item,i)=>{
   const bank=s.inputTokenBank(i);if(!bank.length)return;
   s.appendInputToken('inputs',i,bank[0]);assert.equal(s.inputTokenBank(i)[0].used,true);
   s.inputAnswers[i]='';s.syncInputTokenBank(i);assert.equal(s.inputTokenBank(i)[0].used,false);
   s.inputAnswers[i]=item.answer;s.syncInputTokenBank(i);assert.ok(s.inputTokenBank(i).every(t=>t.used));
   s.wordSuggestionOpen[`inputs-${i}`]=true;s.wordSuggestionResults[`inputs-${i}`]=[{word:'test'}];
   assert.equal(s.isWordSuggestionOpen('inputs',i),false);
  });
  s.resetGroup('inputs');assert.ok(Object.values(s.inputTokenBanks).every(bank=>bank.every(t=>!t.used)));assert.equal(network,0);
 });
}
test('C1 editing keeps sentence boundaries; generic M26 normalization is unchanged',()=>{
 const c1=JSON.parse(packageData.targets[1].after.page.blocks.find(b=>b.type==='practice-set').body),s=state(c1);
 s.inputAnswers[1]='The dataset is incomplete, therefore the estimate is provisional.';assert.equal(s.isCorrect('inputs',1),false);
 for(const v of ['The dataset is incomplete; therefore, the estimate is provisional','The dataset is incomplete. Therefore, the estimate is provisional.']){
  s.inputAnswers[1]=v;assert.equal(s.isCorrect('inputs',1),true);
 }
 const old=state({inputs:[{answer:"I haven't finished."}]});old.inputAnswers[0]='I have not finished';assert.equal(old.isCorrect('inputs',0),true);
 old.inputAnswers[0]='I have finished';assert.equal(old.isCorrect('inputs',0),false);
});
