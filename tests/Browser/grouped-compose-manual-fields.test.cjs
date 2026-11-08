'use strict';
// Legacy grouped compatibility, bound to the exact pre-word-slot checkpoint.
// Current builders are covered by finite-builder-word-slots.test.cjs instead.
// The production input listener, render, Enter shortcut and grader run together.
// Only persistence/network/card-shell effects are isolated, never the answer logic.
const {test}=require('node:test'),assert=require('node:assert/strict');
const fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const {execFileSync}=require('node:child_process'),{JSDOM}=require('jsdom');
const root=path.resolve(__dirname,'../..'),read=p=>fs.readFileSync(path.join(root,p),'utf8');
const plain=x=>JSON.parse(JSON.stringify(x)),variants=require('./load-answer-variants.cjs');
const all=JSON.parse(execFileSync(process.env.PHP_BINARY||'C:/Program Files/xampp/php/php.exe',
 [path.join(root,'tests/Fixtures/legacy-grouped-compose.php')],{cwd:root,encoding:'utf8',maxBuffer:20*1024*1024}));
const shared=read('resources/views/components/saved-test-js-helpers.blade.php');
function fn(source,name){const start=source.indexOf(`function ${name}(`);assert.notEqual(start,-1,name);const tail=source.slice(start),end=tail.search(/^\}/m);assert.notEqual(end,-1,name);return tail.slice(0,end+1);}
function binding(source,mode){const start=source.indexOf(mode==='card-easy'?"card.addEventListener('input', (e) => {":"document.getElementById('question-card').addEventListener('input', (e) => {");assert.notEqual(start,-1);const tail=source.slice(start),endText=mode==='card-easy'?'\n    });':'\n});',end=tail.indexOf(endText);assert.notEqual(end,-1);return tail.slice(0,end+endText.length);}
const base=all.find(q=>q.type==='4'&&q.compose_accepted_sentences?.includes('The girls hug each other.'));
assert.ok(base,'The exact historical reciprocal question is present.');
function fixture(t,mode,raw=base,overrides={}){
 const source=read(`resources/views/test-modes/${mode}.blade.php`),dom=new JSDOM('<!doctype html><body><article id="question-card" data-idx="0"></article></body>',{url:'http://gramlyze.loc/test/fixture'});t.after(()=>dom.window.close());
 const slot=raw.answers.length-1,q={...plain(raw),chosen:raw.answers.map((a,i)=>i<slot?a:null),activeSlot:slot,done:false,wrongAttempt:false,feedback:'',feedbackMeta:null,
  manualInputsBySlot:raw.answers.map(()=>''),manualWordIndexBySlot:raw.answers.map(()=>0),attemptsBySlot:raw.answers.map(()=>0),lastWrongBySlot:raw.answers.map(()=>null),...overrides};
 const state={items:[q],current:0,activeCardIdx:0,answered:0,correct:0},card=dom.window.document.getElementById('question-card');
 const c=vm.createContext({EnglishAnswerVariants:variants,window:dom.window,document:dom.window.document,state,q,card,idx:0,
  COMPOSE_TOKENS_QUESTION_TYPE:'4',IS_POLYGLOT_STEP_PREVIEW:false,testUi:key=>key,persistState(){},setTimeout(f){f();return 0;},
  renderManualSuggestions:()=>'',searchManualWords(){},buildExplanationKey:()=>'',ensureExplanation:()=>Promise.resolve(''),updateProgress(){},checkAllDone(){},isPastPerfectContinuousQuestion:()=>false});
 const helpers=['canonicalTestAnswer','composeSentenceChoiceMatches','acceptedTestAnswers','testAnswerMatches','isPassivePhraseGapQuestion','isReviewedComposePhraseSlot','normalizeManualGapInput','composeManualAnswerWords','html','clearManualAnswerFeedbackOnEdit'];
 const functions=['getMarkersCount','isPolyglotComposeQuestion','getMarkerLabel','findFirstUnfilledSlot','clampActiveSlot','questionContainsSlotMarker','shouldRenderPolyglotTranslationPreview','ensureManualSlotState',
  'manualAnswerWords','manualAnswerProgress','manualAnswerIsComplete','collectManualAnswer','manualInputId','rememberFeedbackAnswer','manualWordFeedbackState','renderManualGapInput','focusManualAnswer',
  'invalidateManualSuggestions','commitManualWord','submitManualAnswer','renderFeedback','renderFeedbackAnswers','onChoose','handleManualAnswerShortcut'];
 if(mode==='card-easy')functions.push('acceptedAnswersForSlot','answerMatchesSlot');
 vm.runInContext([...helpers.map(n=>fn(shared,n)),fn(read('resources/views/components/test-suggestion-keyboard.blade.php'),'isTestAnswerCommitKey'),...functions.map(n=>fn(source,n))].join('\n'),c);
 const render=()=>{card.innerHTML=c.renderManualGapInput(q,0,q.activeSlot,'preview')+`<div id="${mode==='card-easy'?'feedback-0':'feedback'}" role="status">${c.renderFeedback(q)}</div>`;};
 c.render=render;c.rerenderCard=render;render();vm.runInContext(binding(source,mode),c);dom.window.document.addEventListener('keydown',c.handleManualAnswerShortcut,true);
 return {q,state,c,render,inputs:()=>[...card.querySelectorAll('input[data-manual-gap]')],input:()=>card.querySelector('input[data-manual-active="true"]'),
  edit(value){const input=this.input();assert.ok(input);input.value=value;input.dispatchEvent(new dom.window.Event('input',{bubbles:true}));return input;},
  enter(){const input=this.input();assert.ok(input);input.focus();input.dispatchEvent(new dom.window.KeyboardEvent('keydown',{key:'Enter',bubbles:true,cancelable:true}));},
  feedback:()=>card.querySelector('[role="status"]').textContent};
}
for(const mode of ['card-easy','step-easy']){
 test(`${mode}: pinned legacy grouped reciprocal alias keeps spaces through the real input event and Enter`,t=>{
  const f=fixture(t,mode);f.q.chosen[2]='embrace';const input=f.edit('one another');assert.equal(input.value,'one another');assert.equal(f.q.manualInputsBySlot[3],'one another');f.enter();
  assert.equal(f.q.done,true);assert.equal(f.q.chosen[3],'one another');assert.equal(f.state.correct,1);
 });
 test(`${mode}: whole grouped wrong draft survives rerender/reload and can be corrected`,t=>{
  const f=fixture(t,mode);f.edit('one somebody');f.enter();assert.equal(f.q.done,false);assert.equal(f.state.correct,0);assert.equal(f.input().value,'one somebody');
  const saved=plain({manualInputsBySlot:f.q.manualInputsBySlot,manualWordIndexBySlot:f.q.manualWordIndexBySlot,attemptsBySlot:f.q.attemptsBySlot,wrongAttempt:f.q.wrongAttempt,feedbackMeta:f.q.feedbackMeta,feedback:f.q.feedback});
  const restored=fixture(t,mode,base,saved);assert.equal(restored.input().value,'one somebody');restored.edit('one another?!');restored.enter();assert.equal(restored.q.done,true);assert.equal(restored.state.correct,0);
 });
 test(`${mode}: a prefix within a group stays neutral and sequential Enter remains available`,t=>{
  const f=fixture(t,mode);f.edit('one');f.enter();assert.equal(f.q.done,false);assert.equal(f.state.correct,0);assert.equal(f.q.chosen[3],null);assert.equal(f.q.feedback,'');assert.equal(f.inputs().length,2);
  f.edit('another');f.enter();assert.equal(f.q.done,true);assert.equal(f.q.chosen[3],'one another');
 });
 test(`${mode}: a longer reviewed alias for a single-word slot is one valid phrase, not merged letters`,t=>{
  const raw={...plain(base),answers:['Nobody','knows'],markers:['a1','a2'],markers_count:2,compose_accepted_sentences:['Nobody knows','No one knows'],
   accepted_answers_by_marker:{a1:['Nobody','No one'],a2:['knows']},answer_synonyms_by_marker:{a1:['No one']}};
  const f=fixture(t,mode,raw,{activeSlot:0,chosen:[null,null]});f.edit('No one');f.enter();assert.equal(f.q.chosen[0],'No one');assert.equal(f.q.activeSlot,1);assert.equal(f.q.done,false);f.edit('knows');f.enter();assert.equal(f.q.done,true);
 });
 test(`${mode}: grammatical apostrophes survive grouped phrases and missing ones still fail`,t=>{
  const raw={...plain(base),answers:['We','respect',"each other's",'privacy'],markers:['a1','a2','a3','a4'],markers_count:4,
   compose_accepted_sentences:["We respect each other's privacy","We respect one another's privacy"],accepted_answers_by_marker:{a1:['We'],a2:['respect'],a3:["each other's","one another's"],a4:['privacy']}};
  const f=fixture(t,mode,raw,{activeSlot:2,chosen:['We','respect',null,null]});f.edit('one another’s');f.enter();assert.equal(f.q.chosen[2],'one another’s');
  const bad=fixture(t,mode,raw,{activeSlot:2,chosen:['We','respect',null,null]});bad.edit('one anothers');bad.enter();assert.equal(bad.q.chosen[2],null);assert.equal(bad.q.done,false);
 });
 test(`${mode}: existing quality contraction groups accept full and contracted forms`,t=>{
  const raw={...plain(base),answers:['We have','arrived'],markers:['a1','a2'],markers_count:2,compose_accepted_sentences:['We have arrived',"We've arrived"],accepted_answers_by_marker:{a1:['We have',"We've"],a2:['arrived']}};
  for(const value of ['We have',"We’ve"]){const f=fixture(t,mode,raw,{activeSlot:0,chosen:[null,null]});f.edit(value);f.enter();assert.equal(f.q.chosen[0],value);assert.equal(f.q.done,false);f.edit('arrived');f.enter();assert.equal(f.q.done,true);}
 });
 test(`${mode}: old or foreign metadata does not opt legacy controls into phrase input`,t=>{
  const f=fixture(t,mode);for(const patch of [{psp_content_revision:undefined},{compose_content_revision:undefined},{compose_accepted_sentences:[]},{type:'compose_tokens'}]){
   const q={...plain(base),activeSlot:3,...patch};assert.equal(f.c.isReviewedComposePhraseSlot(q,3),false);assert.equal(f.c.normalizeManualGapInput(q,'one another',3),'oneanother');}
  assert.equal(f.c.isReviewedComposePhraseSlot(base,-1),false);assert.equal(f.c.isReviewedComposePhraseSlot(base,99),false);
 });
 test(`${mode}: every pinned legacy multiword slot or alias survives the real input binding`,t=>{
  const f=fixture(t,mode);let checked=0;
  for(const raw of all.filter(q=>q.type==='4'))for(let slot=0;slot<raw.answers.length;slot++){
   const reset=()=>Object.assign(f.q,plain(raw),{chosen:raw.answers.map((a,i)=>i<slot?a:null),activeSlot:slot,done:false,wrongAttempt:false,feedback:'',feedbackMeta:null,
    manualInputsBySlot:raw.answers.map(()=>''),manualWordIndexBySlot:raw.answers.map(()=>0),attemptsBySlot:raw.answers.map(()=>0),lastWrongBySlot:raw.answers.map(()=>null)});
   reset();if(!f.c.isReviewedComposePhraseSlot(f.q,slot))continue;
   const values=Array.from(f.c.acceptedTestAnswers(f.q,slot)).filter(value=>/\s/.test(value));
   for(const value of values){reset();f.render();assert.equal(f.inputs().length,1,raw.uuid+'/'+slot);f.edit(value);assert.equal(f.input().value,value,raw.uuid+'/'+value);f.enter();assert.equal(f.q.chosen[slot],value,raw.uuid+'/'+slot+'/'+value);checked++;}
  }
  assert.ok(checked>=36,'Pinned reciprocal and other reviewed grouped slots are all exercised.');
 });
}
