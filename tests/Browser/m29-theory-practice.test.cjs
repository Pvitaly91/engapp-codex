'use strict';
const {test}=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const root=path.resolve(__dirname,'../..');
const source=fs.readFileSync(path.join(root,'resources/views/engram/theory/blocks-v3/practice-set.blade.php'),'utf8');
const script=source.slice(source.indexOf('<script>')+8,source.lastIndexOf('</script>'));
const packageData=JSON.parse(fs.readFileSync(path.join(root,'database/content-patches/m29-m13-sentence-structure.v1.json'),'utf8'));
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
test('Contextual focus feedback, internal commas and ownership alternatives',()=>{
 const practice=i=>JSON.parse(packageData.targets[i].after.page.blocks.find(b=>b.type==='practice-set').body);
 const cleft=state(practice(0));cleft.choiceAnswers[0]='a';
 assert.equal(cleft.isCorrect('choices',0),false);assert.match(cleft.feedbackText('choices',0),/A граматичне/);
 const noun=state(practice(1));noun.inputAnswers[0]='Leila the project coordinator approved the change';
 assert.equal(noun.isCorrect('inputs',0),false);noun.inputAnswers[0]='Leila, the project coordinator, approved the change';
 assert.equal(noun.isCorrect('inputs',0),true);
 const ellipsis=state(practice(2));ellipsis.inputAnswers[1]='Olena told Marta, "My notes are missing."';
 assert.equal(ellipsis.isCorrect('inputs',1),true);ellipsis.inputAnswers[1]='Olena told Marta that her notes were missing.';
 assert.equal(ellipsis.isCorrect('inputs',1),false);
 const old=state({inputs:[{answer:"I haven't finished."}]});old.inputAnswers[0]='I have not finished';
 assert.equal(old.isCorrect('inputs',0),true);
});
