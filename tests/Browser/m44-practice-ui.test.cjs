'use strict';
// Pure VM contracts, bound to an independently frozen author master.
// No HTTP, Laravel boot, working database or progress writes.
const test=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),vm=require('node:vm'),crypto=require('node:crypto');
const ROOT=path.resolve(__dirname,'../..');
const MASTER_PATH='docs/content/m44-authored-future-forms.v1.0.0.json';
const MASTER_SHA='541db4dec57ed372d23832e849f84c0554fb40faf73693c5b7ab2d306fa7b888';
const bytes=fs.readFileSync(path.join(ROOT,MASTER_PATH));
assert.equal(crypto.createHash('sha256').update(bytes).digest('hex'),MASTER_SHA,'Independent frozen M44 author master');
const master=JSON.parse(bytes);
const common=fs.readFileSync(path.join(ROOT,'public/js/authored-practice-ui.js'),'utf8');
const wrapper=fs.readFileSync(path.join(ROOT,'public/js/m44-practice-ui.js'),'utf8');
const scope={EnglishAnswerVariants:require('./load-answer-variants.cjs')};
vm.runInNewContext(common,scope);vm.runInNewContext(wrapper,scope);
const plain=value=>JSON.parse(JSON.stringify(value));
function uiCases(lesson){return lesson.practice.map(task=>({id:task.id,source_index:task.source_index,scoring:task.scoring,
    interaction:task.controls.length>1?'compound':task.controls[0].kind==='tokens'?'manual':task.controls[0].kind,
    controls:task.controls.map(control=>({id:control.id,kind:control.kind==='tokens'?'manual':control.kind,
        source_kind:control.kind,label:control.label_uk,required:control.required,
        ...(control.stimulus_en?{stimulus_en:control.stimulus_en}:{}),
        ...(['manual','tokens'].includes(control.kind)?{options:[],answer:control.canonical_answer,accepted:control.accepted_answers,tokens:control.tokens}
            :{answer:control.correct_value,options:control.options})}))}));}
const state=lesson=>scope.m44PracticeUi({cases:uiCases(lesson)});
function correct(s,i){s.cases[i].controls.forEach((control,p)=>s.setAnswer(i,p,control.answer));}
function invalid(control){return control.kind==='manual'?'not the requested answer':control.options.find(o=>o.value!==control.answer).value;}
function canonicalTokenOrder(control){
    let remaining=control.answer;const used=new Set(),order=[];
    while(remaining){
        const index=control.tokens.findIndex((token,n)=>!used.has(n)&&(remaining===token||remaining.startsWith(token+' ')));
        assert.notEqual(index,-1,'Every canonical segment must be represented in the author token bank');
        used.add(index);order.push(index);remaining=remaining.slice(control.tokens[index].length).trimStart();
    }
    assert.equal(order.length,control.tokens.length,'Use every token instance, including duplicates');
    return order;
}
test('M44 finite author scope: 3 lessons, 20 sections, 7 details, 18 tasks, 34 controls',()=>{
    assert.equal(master.lessons.length,3);
    assert.deepEqual(master.lessons.map(l=>l.sections.length),[8,5,7]);
    assert.deepEqual(master.lessons.map(l=>l.sections.flatMap(s=>s.points).filter(p=>p.detail).length),[2,3,2]);
    assert.deepEqual(master.lessons.map(l=>l.practice.length),[6,6,6]);
    assert.deepEqual(master.lessons.map(l=>l.practice.flatMap(t=>t.controls).length),[11,12,11]);
    for(const lesson of master.lessons)assert.deepEqual(lesson.practice.map(t=>t.source_index),[1,2,3,4,5,6]);
});
test('M44 owns namespace and finite aliases; no transport or persisted learner state',()=>{
    assert.match(wrapper,/m44PracticeUi/);assert.match(wrapper,/explicitManualVariants: true/);assert.match(wrapper,/data-m44-answer/);
    assert.doesNotMatch(wrapper,/m43PracticeUi|data-m43-answer/);
    assert.doesNotMatch(wrapper+common,/fetch\(|XMLHttpRequest|localStorage|sessionStorage|toUpperCase/);
});
for(const lesson of master.lessons){
    for(const[i,task]of lesson.practice.entries()){
        test(lesson.key+' '+task.id+' initial/empty/partial/wrong/correct/edit/reset/reanswer',()=>{
            const s=state(lesson);
            assert.equal(s.checked[i],false);assert.equal(s.isCorrect(i),false);assert.equal(s.score,0);
            assert.deepEqual(plain(s.answers[i]),task.controls.map(()=>''));s.check(i);assert.equal(s.score,0);
            correct(s,i);s.check(i);assert.equal(s.isCorrect(i),true);assert.equal(s.score,1);
            for(const[p,control]of s.cases[i].controls.entries()){
                correct(s,i);s.setAnswer(i,p,'');assert.equal(s.checked[i],false);s.check(i);
                assert.equal(s.isCorrect(i),false);assert.equal(s.score,0,'Empty required part is never fully correct');
                correct(s,i);s.check(i);s.setAnswer(i,p,invalid(control));assert.equal(s.checked[i],false);
                s.check(i);assert.equal(s.isCorrect(i),false);assert.equal(s.score,0,'One wrong part is never fully correct');
                s.setAnswer(i,p,control.answer);assert.equal(s.checked[i],false);s.check(i);assert.equal(s.score,1);
            }
            s.reset(i);assert.deepEqual(plain(s.answers[i]),task.controls.map(()=>''));assert.equal(s.checked[i],false);
            correct(s,i);s.check(i);assert.equal(s.score,1);
        });
        for(const[p,source]of task.controls.entries()){
            if(['manual','tokens'].includes(source.kind))test(lesson.key+' '+source.id+' all aliases/tokens/typography/boundaries',()=>{
                const s=state(lesson),control=s.cases[i].controls[p];
                assert.equal(control.kind,'manual');assert.ok(control.accepted.includes(control.answer));
                assert.ok(control.tokens.every(t=>t.trim()===t&&!/<[^>]+>/u.test(t)));
                if(source.kind==='tokens')assert.equal(control.source_kind,'tokens');else assert.equal(control.tokens.join(' '),control.answer);
                if(control.tokens.length>1)assert.notDeepEqual(plain(s.banks[i][p].map(t=>t.index)),control.tokens.map((_,n)=>n),'Display bank is shuffled');
                for(const alias of control.accepted)for(const variant of new Set([alias,alias.replace(/[‘’ʼ]/gu,"'"),alias.replace(/'/gu,'’'),alias.replace(/[.!?]+$/u,'')])){
                    s.setAnswer(i,p,variant);assert.equal(s.partCorrect(i,p),true,'Declared alias '+variant);
                }
                s.setAnswer(i,p,'');canonicalTokenOrder(control).forEach(n=>s.appendToken(i,p,n));
                assert.equal(s.answers[i][p],control.answer);assert.equal(s.partCorrect(i,p),true);
                assert.ok(control.tokens.every((_,n)=>s.tokenUsed(i,p,n)));
                const once=s.answers[i][p];s.appendToken(i,p,0);assert.equal(s.answers[i][p],once);
                s.setAnswer(i,p,'');assert.ok(control.tokens.every((_,n)=>!s.tokenUsed(i,p,n)));
                s.appendToken(i,p,0);s.reset(i);assert.ok(control.tokens.every((_,n)=>!s.tokenUsed(i,p,n)));
                const altered=[control.answer+' not',control.answer+' three',
                    control.answer.replace(/\bnot\b/u,'').replace(/\s+/gu,' ').trim(),
                    control.answer.replace(/\bthree\b/u,'two').replace(/\btwelve\b/u,'eleven').replace(/\bfive\b/u,'four')];
                for(const bad of altered)if(bad!==control.answer&&!control.accepted.includes(bad)){
                    s.setAnswer(i,p,bad);assert.equal(s.partCorrect(i,p),false,'Meaning-changing edit stays different');
                }
            });
            else test(lesson.key+' '+source.id+' options/values/keyboard wrap/focus',()=>{
                const s=state(lesson),control=s.cases[i].controls[p];
                assert.deepEqual(plain(control.options),source.options);assert.equal(control.answer,source.correct_value);
                assert.equal(new Set(control.options.map(o=>o.value)).size,control.options.length);
                for(const option of control.options){
                    assert.doesNotMatch(option.label,/<[^>]+>/u);assert.ok(!task.feedback.paragraphs_uk.includes(option.label));
                    s.setAnswer(i,p,option.value);assert.equal(s.partCorrect(i,p),option.value===source.correct_value);
                }
                const focused=[],buttons=control.options.map((_,n)=>({focus:()=>focused.push(n)}));
                const event={target:{getAttribute:()=>control.options[0].value,closest:()=>({querySelectorAll:()=>buttons})}};
                s.setAnswer(i,p,control.options[0].value);s.cycleAnswer(i,p,-1,event);
                assert.equal(s.answers[i][p],control.options.at(-1).value);assert.equal(focused.at(-1),control.options.length-1);
                s.cycleAnswer(i,p,1,event);assert.equal(s.answers[i][p],control.options[0].value);assert.equal(focused.at(-1),0);
                s.reset(i);s.cycleAnswer(i,p,-1,event);assert.equal(s.answers[i][p],control.options.at(-1).value);
            });
        }
    }
    test(lesson.key+' all six score once; independent reset',()=>{
        const s=state(lesson);lesson.practice.forEach((_,i)=>{correct(s,i);s.check(i);});
        assert.equal(s.score,6);s.reset(2);assert.equal(s.score,5);s.reset(2);assert.equal(s.score,5);
    });
}
test('A sentence builder requires both independently identified to instances',()=>{
    const lesson=master.lessons[0],i=lesson.practice.findIndex(t=>t.id==='m44-will-q4'),s=state(lesson),control=s.cases[i].controls[0];
    assert.equal(control.source_kind,'tokens');assert.equal(control.tokens.filter(t=>t==='to').length,2);
    const duplicates=control.tokens.map((t,n)=>t==='to'?n:null).filter(n=>n!==null);assert.equal(new Set(duplicates).size,2);
    s.appendToken(i,0,duplicates[0]);s.appendToken(i,0,duplicates[1]);
    assert.equal(s.answers[i][0],'to to');assert.equal(s.tokenUsed(i,0,duplicates[0]),true);assert.equal(s.tokenUsed(i,0,duplicates[1]),true);
    s.setAnswer(i,0,control.answer.replace('going to go to','going go to'));assert.equal(s.partCorrect(i,0),false);
    s.setAnswer(i,0,'');assert.equal(s.tokenUsed(i,0,duplicates[0]),false);assert.equal(s.tokenUsed(i,0,duplicates[1]),false);
    canonicalTokenOrder(control).forEach(n=>s.appendToken(i,0,n));s.check(i);assert.equal(s.isCorrect(i),true);
    s.setAnswer(i,0,'Are you going to the market after work?');assert.equal(s.partCorrect(i,0),false,'Requested model with go is explicit');
    s.reset(i);assert.equal(s.checked[i],false);
});
test('Typed textarea clearing releases duplicate instances through edited() like x-model',()=>{
    const lesson=master.lessons[0],i=lesson.practice.findIndex(t=>t.id==='m44-will-q4'),s=state(lesson),control=s.cases[i].controls[0];
    const duplicates=control.tokens.map((token,n)=>token==='to'?n:null).filter(n=>n!==null);
    s.appendToken(i,0,duplicates[0]);s.appendToken(i,0,duplicates[1]);
    s.check(i);assert.equal(s.checked[i],true);assert.equal(s.isCorrect(i),false);
    s.answers[i][0]='';s.edited(i); // Real Alpine x-model edit, not setAnswer().
    assert.equal(s.checked[i],false);assert.deepEqual(plain(s.history[i][0]),[]);
    assert.ok(control.tokens.every((_,n)=>!s.tokenUsed(i,0,n)));
    canonicalTokenOrder(control).forEach(n=>s.appendToken(i,0,n));
    assert.equal(s.answers[i][0],control.answer);s.check(i);assert.equal(s.isCorrect(i),true);
});
test('M44 finite aliases leave legacy automatic contraction policy unchanged',()=>{
    const cases=[{controls:[{id:'isolated',kind:'manual',answer:'She has finished.',accepted:['She has finished.'],tokens:['She','has finished.']}]}];
    const s=scope.m44PracticeUi({cases});s.setAnswer(0,0,"She's finished.");assert.equal(s.partCorrect(0,0),false);
    s.setAnswer(0,0,'She is finished.');assert.equal(s.partCorrect(0,0),false);
    const legacy=scope.authoredPracticeUi({cases});legacy.setAnswer(0,0,"She's finished.");assert.equal(legacy.partCorrect(0,0),true);
});
test('Internal punctuation, negation and numerical meaning are significant',()=>{
    const s=scope.m44PracticeUi({cases:[{controls:[{id:'isolated',kind:'manual',answer:'I waited. Then three people arrived.',accepted:['I waited. Then three people arrived.'],tokens:[]}]}]});
    for(const bad of ['I waited Then three people arrived.','I waited. Then two people arrived.','I did not wait. Then three people arrived.']){
        s.setAnswer(0,0,bad);assert.equal(s.partCorrect(0,0),false);
    }
    s.setAnswer(0,0,'I waited. Then three people arrived');assert.equal(s.partCorrect(0,0),true);
});
test('C aliases reject lost not, unsupported stative Continuous and detached ll',()=>{
    const lesson=master.lessons[2],s=state(lesson),neg=lesson.practice.findIndex(t=>t.id==='m44-choice-q6');
    s.setAnswer(neg,0,'They will have restored both statues by Monday.');assert.equal(s.partCorrect(neg,0),false);
    for(const answer of ["They won't have restored both statues by Monday.","They won’t have restored both statues by Monday.","They will not have restored both statues by Monday."]){
        s.setAnswer(neg,0,answer);assert.equal(s.partCorrect(neg,0),true);
    }
    const duration=lesson.practice.findIndex(t=>t.id==='m44-choice-q3');
    for(const answer of ['will have been practising','will have been practicing']){s.setAnswer(duration,0,answer);assert.equal(s.partCorrect(duration,0),true);}
    s.setAnswer(duration,0,"'ll have been practising");assert.equal(s.partCorrect(duration,0),false);
    s.setAnswer(duration,1,'will have been knowing');assert.equal(s.partCorrect(duration,1),false);
});
