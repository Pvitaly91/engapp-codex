'use strict';
// Pure VM tests. Master is independent of generated content/Blade output.
const test=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const qa=require('../../tools/diagnostics/seo-m43-local.cjs'),ROOT=path.resolve(__dirname,'../..');
const common=fs.readFileSync(path.join(ROOT,'public/js/authored-practice-ui.js'),'utf8');
const wrapper=fs.readFileSync(path.join(ROOT,'public/js/m43-practice-ui.js'),'utf8');
const scope={EnglishAnswerVariants:require('./load-answer-variants.cjs')};vm.runInNewContext(common,scope);vm.runInNewContext(wrapper,scope);
const plain=v=>JSON.parse(JSON.stringify(v));
const state=lesson=>scope.m43PracticeUi({cases:qa.uiCases(lesson)});
function correct(s,i){s.cases[i].controls.forEach((control,p)=>s.setAnswer(i,p,control.answer));}
function invalid(control){return control.kind==='manual'?'not the requested answer':control.options.find(o=>o.value!==control.answer).value;}
test('M43 wrapper explicitly limits aliases and cannot write state or send requests',()=>{
    assert.match(wrapper,/explicitManualVariants: true/);assert.match(wrapper,/data-m43-answer/);
    assert.doesNotMatch(wrapper+common,/fetch\(|XMLHttpRequest|localStorage|sessionStorage|toUpperCase/);
});
for(const target of qa.targets){const lesson=target.lesson;
    for(const[i,task]of lesson.practice.entries()){
        test(target.key+' '+task.id+' initial/empty/correct/wrong/reset/reanswer',()=>{
            const s=state(lesson);assert.equal(s.checked[i],false);assert.equal(s.isCorrect(i),false);assert.equal(s.score,0);
            s.check(i);assert.equal(s.score,0);correct(s,i);s.check(i);assert.equal(s.isCorrect(i),true);assert.equal(s.score,1);
            for(const[p,control]of s.cases[i].controls.entries()){
                correct(s,i);s.setAnswer(i,p,'');s.check(i);assert.equal(s.isCorrect(i),false);assert.equal(s.score,0,'Empty required part is never full correct');
                s.setAnswer(i,p,invalid(control));assert.equal(s.checked[i],false);s.check(i);assert.equal(s.isCorrect(i),false);assert.equal(s.score,0);
            }
            s.reset(i);assert.deepEqual(plain(s.answers[i]),task.controls.map(()=>''));assert.equal(s.checked[i],false);
            correct(s,i);s.check(i);assert.equal(s.score,1);
        });
        for(const[p,source]of task.controls.entries()){
            if(source.kind==='manual')test(target.key+' '+source.id+' every alias/tokens/typography/boundary',()=>{
                const s=state(lesson),control=s.cases[i].controls[p];assert.equal(control.tokens.join(' '),control.answer);
                assert.ok(control.accepted.includes(control.answer));assert.ok(control.tokens.every(t=>t.trim()===t&&!/<[^>]+>/u.test(t)));
                if(control.tokens.length>1)assert.notDeepEqual(plain(s.banks[i][p].map(t=>t.index)),control.tokens.map((_,n)=>n),'Displayed token bank is shuffled');
                for(const alias of control.accepted)for(const variant of new Set([alias,alias.replace(/[‘’ʼ`]/gu,"'"),alias.replace(/'/gu,'’'),alias.replace(/[.!?]+$/u,'')])){
                    s.setAnswer(i,p,variant);assert.equal(s.partCorrect(i,p),true,'Declared alias '+variant);
                }
                s.setAnswer(i,p,'');control.tokens.forEach((_,n)=>s.appendToken(i,p,n));assert.equal(s.answers[i][p],control.answer);assert.equal(s.partCorrect(i,p),true);
                assert.ok(control.tokens.every((_,n)=>s.tokenUsed(i,p,n)));const once=s.answers[i][p];s.appendToken(i,p,0);assert.equal(s.answers[i][p],once);
                s.setAnswer(i,p,'');assert.ok(control.tokens.every((_,n)=>!s.tokenUsed(i,p,n)));s.appendToken(i,p,0);s.reset(i);assert.ok(control.tokens.every((_,n)=>!s.tokenUsed(i,p,n)));
                for(const bad of [control.answer+' not',control.answer+' three',control.answer.replace(/\bnot\b/u,'').replace(/\s+/gu,' ').trim(),control.answer.replace(/\bfour\b/u,'five').replace(/\bthree\b/u,'two')]){
                    if(bad!==control.answer&&!control.accepted.includes(bad)){s.setAnswer(i,p,bad);assert.equal(s.partCorrect(i,p),false,'Meaning-changing edit not normalized away');}
                }
            });
            else test(target.key+' '+source.id+' options/values/keyboard wrap',()=>{
                const s=state(lesson),control=s.cases[i].controls[p];assert.deepEqual(control.options,source.options);assert.equal(control.answer,source.correct_value);
                assert.equal(new Set(control.options.map(o=>o.value)).size,control.options.length);
                for(const option of control.options){assert.doesNotMatch(option.label,/<[^>]+>/u);assert.ok(!task.feedback.paragraphs_uk.includes(option.label),'Option is not complete explanation');
                    s.setAnswer(i,p,option.value);assert.equal(s.partCorrect(i,p),option.value===source.correct_value);}
                const focused=[],buttons=control.options.map((_,n)=>({focus:()=>focused.push(n)})),event={target:{getAttribute:()=>control.options[0].value,closest:()=>({querySelectorAll:()=>buttons})}};
                s.setAnswer(i,p,control.options[0].value);s.cycleAnswer(i,p,-1,event);assert.equal(s.answers[i][p],control.options.at(-1).value);assert.equal(focused.at(-1),control.options.length-1);
                s.cycleAnswer(i,p,1,event);assert.equal(s.answers[i][p],control.options[0].value);s.reset(i);s.cycleAnswer(i,p,-1,event);assert.equal(s.answers[i][p],control.options.at(-1).value);
            });
        }
    }
    test(target.key+' all six score once; independent reset',()=>{const s=state(lesson);lesson.practice.forEach((_,i)=>{correct(s,i);s.check(i);});assert.equal(s.score,6);s.reset(2);assert.equal(s.score,5);s.reset(2);assert.equal(s.score,5);});
}
test('explicit aliases are not broad grammar recognition or automatic possessive expansion',()=>{
    const s=scope.m43PracticeUi({cases:[{controls:[{id:'isolated',kind:'manual',answer:'She has finished.',accepted:['She has finished.'],tokens:['She','has finished.']}]}]});
    s.setAnswer(0,0,"She's finished.");assert.equal(s.partCorrect(0,0),false);s.setAnswer(0,0,'She is finished.');assert.equal(s.partCorrect(0,0),false);
    const legacy=scope.authoredPracticeUi({cases:s.cases});legacy.setAnswer(0,0,"She's finished.");assert.equal(legacy.partCorrect(0,0),true,'Legacy policy remains unchanged');
});
test('comparison retains internal punctuation and numerical meaning',()=>{
    const s=scope.m43PracticeUi({cases:[{controls:[{id:'isolated',kind:'manual',answer:'I waited. Then three people arrived.',accepted:['I waited. Then three people arrived.'],tokens:[]}]}]});
    for(const bad of ['I waited Then three people arrived.','I waited. Then two people arrived.','I did not wait. Then three people arrived.']){s.setAnswer(0,0,bad);assert.equal(s.partCorrect(0,0),false);}
    s.setAnswer(0,0,'I waited. Then three people arrived');assert.equal(s.partCorrect(0,0),true);
});
test('all original M43 master control fields survive independent mechanical UI projection',()=>{
    for(const target of qa.targets)for(const[i,task]of qa.uiCases(target.lesson).entries())for(const[p,control]of task.controls.entries()){
        const source=target.lesson.practice[i].controls[p];assert.equal(control.label,source.label_uk);assert.equal(control.required,true);assert.equal(control.id,source.id);assert.equal(control.stimulus_en,source.stimulus_en);
        if(control.kind==='manual'){assert.equal(control.answer,source.canonical_answer);assert.deepEqual(control.accepted,source.accepted_answers);assert.deepEqual(control.tokens,source.tokens);}
        else{assert.equal(control.answer,source.correct_value);assert.deepEqual(control.options,source.options);}
    }
});
