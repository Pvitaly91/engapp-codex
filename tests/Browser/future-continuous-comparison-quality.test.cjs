'use strict';
// Actual card/step functions with finite compiled rows; only persistence/network are stubbed.
const {test}=require('node:test'),assert=require('node:assert/strict');
const fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const {execFileSync}=require('node:child_process'),{JSDOM}=require('jsdom');
const root=path.resolve(__dirname,'../..'),read=p=>fs.readFileSync(path.join(root,p),'utf8');
const plain=v=>JSON.parse(JSON.stringify(v)),variants=require('./load-answer-variants.cjs');
const all=JSON.parse(execFileSync(process.env.PHP_BINARY||'C:/Program Files/xampp/php/php.exe',
 [path.join(root,'tests/Fixtures/future-continuous-comparison-quality.php')],{cwd:root,encoding:'utf8',maxBuffer:5*1024*1024}));
const mixed=JSON.parse(execFileSync(process.env.PHP_BINARY||'C:/Program Files/xampp/php/php.exe',
 [path.join(root,'tests/Fixtures/future-continuous-comparison-quality.php'),'--mixed'],{cwd:root,encoding:'utf8',maxBuffer:5*1024*1024}));
const helpers=read('resources/views/components/saved-test-js-helpers.blade.php');
function fn(source,name){const start=source.indexOf(`function ${name}(`);assert.notEqual(start,-1,name);const tail=source.slice(start),end=tail.search(/^\}/m);assert.notEqual(end,-1,name);return tail.slice(0,end+1);}
const {wordPolicyHelpers, assertWordSentencePath, assertWordChoiceCount} = require('./finite-word-policy-harness.cjs');
const shared=[...wordPolicyHelpers, 'canonicalTestAnswer','composeSentenceChoiceMatches','acceptedTestAnswers','testAnswerMatches',
 'isPassivePhraseGapQuestion','isReviewedComposePhraseSlot','normalizeManualGapInput','composeManualAnswerWords'];
function optionsContext(mode){
 const c=vm.createContext({EnglishAnswerVariants:variants,COMPOSE_TOKENS_QUESTION_TYPE:'4',IS_POLYGLOT_STEP_PREVIEW:false,
  POLYGLOT_OPTIONS_PER_SLOT:4,shuffle:a=>a.reverse(),cloneState:plain,getTechnicalQuestions:()=>plain(all)});
 const source=read(`resources/views/test-modes/${mode}.blade.php`),methods=['getMarkersCount','sanitizeOptions','buildShuffledOptions',
  'normalizeAnswer','isPolyglotComposeQuestion','limitOptionsForSlot','buildFallbackOptionsBySlot','normalizeOptionsBySlot','getActiveOptions'];
 vm.runInContext([...shared.map(n=>fn(helpers,n)),...methods.map(n=>fn(source,n))].join('\n'),c);return c;
}
function gapFixture(t,mode,raw){
 const source=read(`resources/views/test-modes/${mode}.blade.php`),dom=new JSDOM('<!doctype html><body></body>',{url:'http://gramlyze.loc/test/fixture'});t.after(()=>dom.window.close());
 const q={...plain(raw),chosen:[null],activeSlot:0,done:false,wrongAttempt:false,feedback:'',feedbackMeta:null,
  manualInputsBySlot:[''],manualWordIndexBySlot:[0],attemptsBySlot:[0],lastWrongBySlot:[null]},state={items:[q],current:0,answered:0,correct:0};
 const c=vm.createContext({EnglishAnswerVariants:variants,window:dom.window,document:dom.window.document,state,
  COMPOSE_TOKENS_QUESTION_TYPE:'4',IS_POLYGLOT_STEP_PREVIEW:false,testUi:k=>k,persistState(){},setTimeout(f){f();return 0;},
  renderManualSuggestions:()=>'',buildExplanationKey:()=>'',ensureExplanation:()=>Promise.resolve(''),updateProgress(){},checkAllDone(){},isPastPerfectContinuousQuestion:()=>false});
 const methods=['getMarkersCount','isPolyglotComposeQuestion','getMarkerLabel','findFirstUnfilledSlot','clampActiveSlot',
  'questionContainsSlotMarker','shouldRenderPolyglotTranslationPreview','ensureManualSlotState','manualAnswerWords','manualAnswerProgress',
  'manualAnswerIsComplete','collectManualAnswer','manualInputId','rememberFeedbackAnswer','manualWordFeedbackState','renderManualGapInput',
  'focusManualAnswer','invalidateManualSuggestions','commitManualWord','submitManualAnswer','renderFeedback','renderFeedbackAnswers','onChoose'];
 if(mode==='card-easy')methods.push('acceptedAnswersForSlot','answerMatchesSlot');
 vm.runInContext([...shared.map(n=>fn(helpers,n)),fn(helpers,'html'),...methods.map(n=>fn(source,n))].join('\n'),c);
 const render=()=>{dom.window.document.body.innerHTML=c.renderManualGapInput(q,0,0,'sentence');};c.render=render;c.rerenderCard=render;render();
 return {q,state,c,render,inputs:()=>[...dom.window.document.querySelectorAll('input[data-manual-gap]')],
  reset(row){Object.assign(q,plain(row),{chosen:[null],activeSlot:0,done:false,wrongAttempt:false,feedback:'',feedbackMeta:null,
   manualInputsBySlot:[''],manualWordIndexBySlot:[0],attemptsBySlot:[0],lastWrongBySlot:[null],explanation:'',pendingExplanationKey:null});state.correct=0;state.answered=0;render();},
  commit(value){const input=dom.window.document.querySelector('input[data-manual-active="true"]');assert.ok(input);input.value=value;c.commitManualWord(0,0,Number(input.dataset.manualWord),'sentence');}};
}

test('54 finite questions have exact clean Ukrainian hints and short full-phrase gaps',()=>{
 assert.equal(all.length,54);assert.equal(new Set(all.map(q=>q.uuid)).size,54);const c=optionsContext('card-easy');
 assert.equal(all.filter(q=>q.type==='0').length,36);assert.equal(all.filter(q=>q.type==='4').length,18);
 for(const q of all){assert.ok(q.psp_content_revision);assert.match(q.verb_hint,/[А-Яа-яІіЇїЄєҐґ]/u);assert.doesNotMatch(q.verb_hint,/[A-Za-z]|<[^>]+>|\{a\d+\}/u);if(q.type!=='0')continue;
  assert.equal(q.passive_phrase_word_inputs,true);assert.equal(q.passive_gap_phrase_input,true);
  assert.ok(q.answers[0].split(/\s+/).length>=2&&q.answers[0].split(/\s+/).length<=4);
  assert.equal(c.testAnswerMatches(q,0,q.answers[0]),true);assert.equal(c.testAnswerMatches(q,0,''),false);
  assert.equal(c.testAnswerMatches(q,0,q.answers[0].split(/\s+/)[0]),false,q.uuid+' incomplete phrase');
 }
});

for(const mode of ['card-easy','step-easy']){
 test(`${mode}: every gap and lexical alias needs every missing word before feedback/credit`,t=>{
  const first=all.find(q=>q.type==='0'),f=gapFixture(t,mode,first);let checked=0;
  for(const raw of all.filter(q=>q.type==='0')){
   const alternatives=[...new Set([raw.answers[0],...(raw.answer_synonyms_by_marker?.a1||[])])];
   for(const alternative of alternatives){f.reset(raw);assert.equal(f.inputs().length,raw.answers[0].split(/\s+/).length,raw.uuid);
    const words=alternative.split(/\s+/);for(const[i,word]of words.entries()){
     f.commit(i===words.length-1?word.replace(/[.,!?;:]/g,'')+'?!':word);
     if(i<words.length-1){assert.equal(f.q.done,false,raw.uuid);assert.equal(f.q.chosen[0],null);assert.equal(f.state.correct,0);assert.equal(f.q.feedback,'');}
    }
    assert.equal(f.q.done,true,raw.uuid+'/'+alternative);assert.equal(f.state.correct,1);assert.equal(f.q.answers[0],raw.answers[0]);checked++;
   }
  }assert.ok(checked>=36);
 });
 test(`${mode}: all compose slots show four varied finite choices without correct-word recycling`,()=>{
  const c=optionsContext(mode),sets=new Set();let slots=0;
  for(const raw of all.filter(q=>q.type==='4')){const q=plain(raw);q.chosen=[];q.optionsBySlot=c.normalizeOptionsBySlot(q);
   for(let index=0;index<q.answers.length;index++){slots++;q.activeSlot=index;const options=Array.from(c.getActiveOptions(q));assertWordChoiceCount(c,q,index,options,variants);assert.ok(options.some(value=>variants.normalize(value)===variants.normalize(q.answers[index])));
    const valid=new Set(c.acceptedTestAnswers(q,index).map(v=>variants.normalize(v)));sets.add(JSON.stringify(options));
    for(const option of options){assert.ok(option.split(/\s+/).length<=3);if(!valid.has(variants.normalize(option)))assert.equal(q.answers.some(a=>variants.normalize(a)===variants.normalize(option)),false);}
    assert.equal(c.testAnswerMatches(q,index,q.answers[index].replace(/[.,!?;:]/g,'')),true);
    if(q.answers[index].includes("'"))assert.equal(c.testAnswerMatches(q,index,q.answers[index].replaceAll("'",'')),false,'Orthographic apostrophes remain required.');
    q.chosen[index]=q.answers[index];assert.equal(c.syncPresentPerfectPassiveWordPath(q),true);
   }
  }assert.ok(slots>=60);assert.ok(sets.size>18,'Choice pools are not one recycled list.');
 });
 test(`${mode}: every reviewed full sentence including contractions is reachable in its authored slots`,()=>{
  const c=optionsContext(mode);let checked=0;
  for(const raw of all.filter(q=>q.type==='4'))for(const sentence of raw.compose_accepted_sentences){
   assertWordSentencePath(c,raw,sentence,variants);checked++;
  }assert.ok(checked>=18);
 });
}

test('fresh canonical choices refresh only content, preserving saved answers, drafts and counters',()=>{
 const c=optionsContext('card-easy');vm.runInContext(fn(helpers,'mergeFreshQuestionContentIntoSavedState'),c);
 const fresh=all.find(q=>q.type==='4'),old={...plain(fresh),compose_content_revision:'old',options_by_marker:fresh.answers.map(a=>[a,'wealth','fell','lorry']),
  chosen:[fresh.answers[0]],manualInputsBySlot:['unfinished'],attemptsBySlot:[2],activeSlot:1,done:false};
 const state={items:[old],answered:2,correct:1,current:0},after=c.mergeFreshQuestionContentIntoSavedState(state);
 for(const key of ['answered','correct','current'])assert.equal(after[key],state[key]);
 for(const key of ['chosen','manualInputsBySlot','attemptsBySlot','activeSlot','done'])assert.deepEqual(plain(after.items[0][key]),old[key]);
 assert.deepEqual(plain(after.items[0].options_by_marker),fresh.options_by_marker);assert.equal(after.items[0].compose_content_revision,fresh.compose_content_revision);
});

test('all54 actual mixed presentations interleave gap/compose/order at every level',()=>{
 assert.equal(mixed.length,54);let previous=null;
 for(const level of ['A1','A2','B1','B2','C1','C2']){const counts={gap:0,compose:0,order:0};
  for(const q of mixed.filter(q=>q.level===level)){const kind=q.presentation==='sentence_reorder'?'order':q.type==='4'?'compose':'gap';
   counts[kind]++;assert.notEqual(kind,previous);previous=kind;
  }assert.deepEqual(counts,{gap:3,compose:3,order:3});
 }
});

test('all18 actual reorder rows accept manual punctuation-free answers and return deleted tokens to the bank',()=>{
 const source=read('resources/views/components/sentence-reorder-question-js.blade.php').match(/<script>([\s\S]*?)<\/script>/)[1];
 const c=vm.createContext({TEST_LOCALE:'uk',EnglishAnswerVariants:variants,testUi:k=>k,
  html:v=>String(v??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('"','&quot;'),isPastPerfectContinuousQuestion:()=>false});
 vm.runInContext(source,c);const orders=mixed.filter(q=>q.presentation==='sentence_reorder');assert.equal(orders.length,18);
 for(const raw of orders){const q={...plain(raw),done:false};assert.equal(q.reorder_manual_input,true);
  const rendered=c.renderSentenceReorderQuestion(q);assert.match(rendered,/data-reorder-manual-input/);assert.doesNotMatch(rendered,/datalist|\{a\d+\}/u);
  const first=q.reorder_groups[0],index=q.reorder_tokens.indexOf(first);assert.ok(index>=0);
  c.applySentenceReorderAction(q,{dataset:{reorderAction:'add',reorderTokenIndex:String(index)}});assert.ok(q.reorderUsed.includes(index));
  c.syncSentenceReorderManualText(q,'');assert.equal(q.reorderUsed.length,0);
  c.applySentenceReorderAction(q,{dataset:{reorderAction:'add',reorderTokenIndex:String(index)}});assert.ok(q.reorderUsed.includes(index));
  c.syncSentenceReorderManualText(q,q.reorder_answer.replace(/[.,!?;:]/g,'')+'?!');
  assert.equal(c.applySentenceReorderAction(q,{dataset:{reorderAction:'check'}}),'checked',q.uuid);assert.equal(q.feedback,'correct',q.uuid);
 }
 assert.equal(source.includes('.focus('),false,'Token clicks do not open native input suggestions.');
});
