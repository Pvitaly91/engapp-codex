<?php

namespace Tests\Feature;

use App\Support\M80NegativesTransformations as Package;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** The unchanged native factory, offline. Nominal widths are not real browser/layout evidence. */
class M80NativePracticeTest extends TestCase
{
    public function test_all_thirty_six_tasks_and_twelve_full_inputs_use_native_manual_token_and_retry_paths(): void
    {
        $root = dirname(__DIR__, 2);
        $release = Package::load($root);
        Package::validate($release[0], $release[1], $release[2], $release[3]);
        $factoryRoot = getenv('GRAMLYZE_M80_FACTORY_ROOT') ?: $root;
        $script = <<<'JS'
'use strict';
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),vm=require('node:vm');
const assert=require('node:assert/strict');
const root=path.resolve(process.argv[2]),factoryRoot=path.resolve(process.argv[3]);
const input=globalThis.__M80_FROZEN_INPUT;
assert.equal(input.lessons.length,6);
const sha=bytes=>crypto.createHash('sha256').update(bytes).digest('hex'),tracked=new Map();
function read(file){const bytes=fs.readFileSync(file);tracked.set(file,sha(bytes));return bytes.toString('utf8');}
const file=path.join(factoryRoot,'resources/views/engram/theory/blocks-v3/practice-set.blade.php');
const native=read(file),raw=sha(Buffer.from(native)),lf=sha(Buffer.from(native.replace(/\r\n/g,'\n')));
const accepted=[
 {raw:['05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e','c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d'],lf:'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d'},
 {raw:['0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186'],lf:'a419b43d853126d88bc43683bddeb4d8efd190b08d1bfcf60944a005ad122ba7'}
];
assert.ok(accepted.some(p=>p.raw.includes(raw)&&p.lf===lf),'Unchanged accepted factory bytes only.');
const scripts=[...native.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(x=>x[1]).filter(x=>/Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(x));
assert.equal(scripts.length,1);assert.doesNotMatch(scripts[0],/@(?:php|json|js|endphp)\b|\{\{!!?/);
for(const relative of ['tests/Browser/load-answer-variants.cjs','public/js/english-answer-variants.js','public/data/english-contractions.json'])read(path.join(factoryRoot,relative));
const variants=require(path.join(factoryRoot,'tests/Browser/load-answer-variants.cjs'));
let factory,listener,network=0;
const win={EnglishAnswerVariants:variants,location:{origin:'http://gramlyze.loc'},innerWidth:1280};
const context=vm.createContext({window:win,document:{addEventListener(name,fn){assert.equal(name,'alpine:init');listener=fn;},querySelector(){return{focus(){},setSelectionRange(){}};}},Alpine:{data(name,fn){assert.equal(name,'theoryPracticeSet');factory=fn;}},fetch(){network++;throw Error('Network forbidden.');},URL});
vm.runInContext(scripts[0],context,{filename:file,timeout:1000});listener();assert.equal(typeof factory,'function');
const make=(data,width=1280)=>{win.innerWidth=width;const model=factory({...data,i18n:{empty:'Введи відповідь.',correct:'Правильно.',incorrect:'Неправильно.',answer:'Правильна відповідь',score:'Результат: :correct / :total'}});model.$nextTick=fn=>fn();model.init();return model;};
const groups=['selects','choices','inputs'],props={selects:'selectAnswers',choices:'choiceAnswers',inputs:'inputAnswers'};
const plain=value=>{assert.equal(typeof value,'string');assert.match(value,/[\u0400-\u04ff]/);assert.doesNotMatch(value,/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{a\d+\}/iu);};
const check=(model,group,index,value,correct)=>{model[props[group]][index]=value;model.check(group);assert.equal(model.isCorrect(group,index),correct,value);};
function ordered(model,index,canonical){const words=model.inputTokenWords(canonical),remaining=[...model.inputTokenBank(index)],out=[];for(let at=0;at<words.length;){const pos=remaining.findIndex(t=>model.inputTokenWords(t.value).every((word,n)=>words[at+n]===word));assert.notEqual(pos,-1,'Canonical group missing');const token=remaining.splice(pos,1)[0];out.push(token);at+=model.inputTokenWords(token.value).length;}assert.equal(remaining.length,0);return out;}
function click(model,index,token){model.appendInputToken('inputs',index,token);const prior=model.inputAnswers[index];model.appendInputToken('inputs',index,token);assert.equal(model.inputAnswers[index],prior,'No duplicate instance use');assert.equal(model.isWordSuggestionOpen('inputs',index),false);}
// These literal semantic expectations are independent of the frozen AFTER answer values.
const abc=[
 ['He promised not to be late.','Marko did not call us yesterday.'],
 ['We did not meet anyone yesterday.','Nobody called us after the meeting.'],
 ['Oleh has been learning French for three years.','By the time we arrived, the film had started.']
];
const semantic=[
 [0,0,'He did not promise to be late.',false],
 [0,0,'He promised not be late.',false],
 [0,0,'He promised to not be late.',false],
 [0,1,'Marko did call us yesterday.',false],
 [0,1,"Marko didn't phone us yesterday.",true],
 [1,0,'We did not meet nobody yesterday.',false],
 [1,0,'We do not meet anyone yesterday.',false],
 [1,0,'We did not meet anybody yesterday.',true],
 [1,1,'Everybody called us after the meeting.',false],
 [1,1,'No one phoned us after the meeting.',true],
 [2,0,'Oleh has learned French for three years.',false],
 [2,0,'Oleh has been studying French for three years.',true],
 [2,0,'Oleh has been learning French since September 2021.',false],
 [2,1,'When we arrived, the film started.',false],
 [2,1,'By the time we arrived, the film has started.',false],
 [2,1,'By the time we arrived, the film had already started.',true]
];
(async()=>{
 let tasks=0,aliases=0,options=0,retries=0,full=0,manual=0,tokens=0,deletion=0,rebuild=0,mixed=0;
 const allData=input.lessons.map(lesson=>{const blocks=lesson.blocks.filter(x=>x.type==='practice-set');assert.equal(blocks.length,1);return blocks[0].data;});
 const identities=new Set();
 for(const [li,data]of allData.entries()){
  const untouched=JSON.stringify(data);assert.equal(data.rephrase.length,0);
  if(li<3)assert.deepEqual(data.inputs.map(t=>t.answer),abc[li]);
  for(const group of groups){assert.equal(data[group].length,2);for(const[index,task]of data[group].entries()){
   tasks++;assert.ok(!identities.has(task.id));identities.add(task.id);const model=make(data);
   for(const empty of ['', ' ', '\t\n', '\u00a0','—']){check(model,group,index,empty,false);assert.equal(model.feedbackText(group,index),'Введи відповідь.');}
   check(model,group,index,'zzzz wrong',false);plain(model.feedbackText(group,index));
   for(const value of task.accepted){check(model,group,index,value,true);assert.equal(model.feedbackText(group,index),task.feedback[value.trim().toLowerCase()]);plain(model.feedbackText(group,index));aliases++;}
   if(group!=='inputs'){const shown=task.options;assert.ok(shown.length>=2);const normalized=shown.map(x=>model.normalize(x));assert.equal(new Set(normalized).size,normalized.length);for(const value of shown){check(model,group,index,value,task.accepted.includes(value));assert.equal(model.feedbackText(group,index),task.feedback[value.trim().toLowerCase()]);plain(model.feedbackText(group,index));options++;}}
   check(model,group,index,'zzzz wrong',false);check(model,group,index,task.answer,true);assert.equal(model.scoreText(group),'Результат: 1 / 2');model.check(group);assert.equal(model.scoreText(group),'Результат: 1 / 2');retries++;
   model.resetGroup(group);model.check(group);assert.equal(model.scoreText(group),'Результат: 0 / 2');
  }}
  for(const[index,task]of data.inputs.entries()){
   full++;const base=make(data),bank=base.inputTokenBank(index);assert.ok(bank.length>=2);assert.equal(new Set(bank.map(t=>t.id)).size,bank.length);
   for(const token of bank)assert.ok(token.value.split(/\s+/u).length<=3);
   assert.notEqual(base.normalize(bank.map(t=>t.value).join(' ')),base.normalize(task.answer));
   for(const width of[320,1280]){
    const typed=make(data,width);typed.inputAnswers[index]=task.answer;typed.syncInputTokenBank(index);typed.check('inputs');assert.equal(typed.isCorrect('inputs',index),true);assert.equal(typed.isInputTokenBankEmpty(index),true);manual++;
    const model=make(data,width),chosen=ordered(model,index,task.answer);model.wordSearchEndpoint='/forbidden';model.wordSuggestionOpen['inputs-'+index]=true;
    for(const[pos,token]of chosen.entries()){click(model,index,token);await model.searchWordSuggestions('inputs',index,{target:{value:'word',selectionStart:4}});assert.equal(model.isWordSuggestionOpen('inputs',index),false);model.check('inputs');assert.equal(model.isCorrect('inputs',index),pos===chosen.length-1);}
    assert.equal(model.inputAnswers[index],task.answer,'Actual token text, not merely normalized grading');assert.equal(model.isInputTokenBankEmpty(index),true);tokens++;
    const last=chosen.at(-1);model.inputAnswers[index]=chosen.slice(0,-1).map(t=>t.value).join(' ');model.syncInputTokenBank(index);assert.equal(model.inputTokenBank(index).find(t=>t.id===last.id).used,false);model.check('inputs');assert.equal(model.isCorrect('inputs',index),false);click(model,index,model.inputTokenBank(index).find(t=>t.id===last.id));model.check('inputs');assert.equal(model.isCorrect('inputs',index),true);deletion++;
    model.inputAnswers[index]='';model.syncInputTokenBank(index);model.check('inputs');assert.equal(model.scoreText('inputs'),'Результат: 0 / 2');assert.ok(model.inputTokenBank(index).every(t=>!t.used));for(const token of ordered(model,index,task.answer))click(model,index,token);model.check('inputs');assert.equal(model.isCorrect('inputs',index),true);rebuild++;
    const other=index===0?1:0;assert.equal(model.inputAnswers[other],undefined);assert.ok(model.inputTokenBank(other).every(t=>!t.used));
    model.resetGroup('inputs');model.check('inputs');assert.equal(model.scoreText('inputs'),'Результат: 0 / 2');
   }
   const model=make(data),chosen=ordered(model,index,task.answer);model.inputAnswers[index]=chosen[0].value;model.syncInputTokenBank(index);for(const token of chosen.slice(1))click(model,index,model.inputTokenBank(index).find(t=>t.id===token.id));model.check('inputs');assert.equal(model.isCorrect('inputs',index),true);assert.equal(model.inputAnswers[index],task.answer);mixed++;
  }
  const complete=make(data);for(const group of groups){for(const[index,task]of data[group].entries())complete[props[group]][index]=task.answer;complete.check(group);assert.equal(complete.scoreText(group),'Результат: 2 / 2');}
  for(const group of groups){const other=groups.filter(x=>x!==group).map(x=>[x,complete.scoreText(x)]);complete.resetGroup(group);complete.check(group);assert.equal(complete.scoreText(group),'Результат: 0 / 2');for(const[k,v]of other)assert.equal(complete.scoreText(k),v);}
  assert.equal(JSON.stringify(data),untouched);
 }
 for(const[li,index,value,correct]of semantic){const model=make(allData[li]);check(model,'inputs',index,value,correct);plain(model.feedbackText('inputs',index));if(!correct){check(model,'inputs',index,abc[li][index],true);model.check('inputs');assert.equal(model.scoreText('inputs'),'Результат: 1 / 2');}}
 assert.equal(tasks,36);assert.equal(retries,36);assert.equal(full,12);assert.equal(manual,24);assert.equal(tokens,24);assert.equal(deletion,24);assert.equal(rebuild,24);assert.equal(mixed,12);assert.equal(network,0);
 for(const[file,pin]of tracked)assert.equal(sha(fs.readFileSync(file)),pin,'Read-only native bytes changed');
 console.log(JSON.stringify({kind:'actual unchanged native factory; offline only, not browser acceptance',master_sha256:input.master_sha256,lessons:6,tasks,aliases,options,retries,full,manual,tokens,deletion,rebuild,mixed,semantic_cases:semantic.length,network_calls:network,sql_calls:0,file_writes:0,nominal_widths:[320,1280],native_raw_sha256:raw,native_lf_sha256:lf}));
})().catch(error=>{console.error(error);process.exitCode=1;});
JS;
        // Windows command lines must not carry a multiline program or a large JSON payload.
        // Node consumes both as one stdin program; no temporary script/data files are written.
        $process = new Process([getenv('NODE_BINARY') ?: 'node', '-', $root, $factoryRoot], $root, timeout: 30);
        $payload = json_encode(['lessons' => $release[2]['lessons'], 'master_sha256' => Package::MASTER_SHA], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $process->setInput('globalThis.__M80_FROZEN_INPUT = '.$payload.";\n".$script);
        $process->run();
        self::assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('actual unchanged native factory; offline only, not browser acceptance', $result['kind']);
        self::assertSame(Package::MASTER_SHA, $result['master_sha256']);
        foreach (['lessons' => 6, 'tasks' => 36, 'retries' => 36, 'full' => 12, 'manual' => 24, 'tokens' => 24,
            'deletion' => 24, 'rebuild' => 24, 'mixed' => 12, 'semantic_cases' => 16, 'network_calls' => 0, 'sql_calls' => 0, 'file_writes' => 0] as $key => $expected) {
            self::assertSame($expected, $result[$key], $key);
        }
        self::assertGreaterThanOrEqual(36, $result['aliases']); self::assertGreaterThanOrEqual(48, $result['options']);
        self::assertSame([320, 1280], $result['nominal_widths']);
    }
}
