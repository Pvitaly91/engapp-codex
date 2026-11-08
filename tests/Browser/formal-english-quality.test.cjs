'use strict';
// Shipped card/step/order functions, finite compiled data. No live database or network.
const {test}=require('node:test'),assert=require('node:assert/strict');
const fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const {execFileSync}=require('node:child_process'),{JSDOM}=require('jsdom');
const root=path.resolve(__dirname,'../..'),read=p=>fs.readFileSync(path.join(root,p),'utf8'),plain=v=>JSON.parse(JSON.stringify(v));
const variants=require('./load-answer-variants.cjs');
const fixture=(...args)=>JSON.parse(execFileSync(process.env.PHP_BINARY||'C:/Program Files/xampp/php/php.exe',
 [path.join(root,'tests/Fixtures/formal-english-quality.php'),...args],{cwd:root,encoding:'utf8',maxBuffer:20*424*424}));
const all=fixture(),mixed=fixture('--mixed'),helpers=read('resources/views/components/saved-test-js-helpers.blade.php');
function fn(source,name){const start=source.indexOf(`function ${name}(`);assert.notEqual(start,-1,name);const tail=source.slice(start),end=tail.search(/^\}/m);assert.notEqual(end,-1,name);return tail.slice(0,end+1);}
const {wordPolicyHelpers, assertWordSentencePath, assertWordChoiceCount} = require('./finite-word-policy-harness.cjs');
const shared=[...wordPolicyHelpers, 'canonicalTestAnswer','composeSentenceChoiceMatches','reviewedQuestionSurfaceAnswers','acceptedTestAnswers','testAnswerMatches','isPassivePhraseGapQuestion',
 'isReviewedComposePhraseSlot','normalizeManualGapInput','composeManualAnswerWords','finitePracticeOptionCaption','formatAnswerOptionLabel'];
function optionsContext(mode){
 const c=vm.createContext({EnglishAnswerVariants:variants,COMPOSE_TOKENS_QUESTION_TYPE:'4',IS_POLYGLOT_STEP_PREVIEW:false,
  POLYGLOT_OPTIONS_PER_SLOT:4,shuffle:a=>a.reverse(),cloneState:plain,getTechnicalQuestions:()=>plain(all)});
 const source=read(`resources/views/test-modes/${mode}.blade.php`),methods=['getMarkersCount','sanitizeOptions','buildShuffledOptions','normalizeAnswer',
  'isPolyglotComposeQuestion','limitOptionsForSlot','buildFallbackOptionsBySlot','normalizeOptionsBySlot','getActiveOptions'];
 vm.runInContext([...shared.map(n=>fn(helpers,n)),...methods.map(n=>fn(source,n))].join('\n'),c);return c;
}
function gapFixture(t,mode,raw){
 const source=read(`resources/views/test-modes/${mode}.blade.php`),dom=new JSDOM('<!doctype html><body></body>',{url:'http://gramlyze.loc/test/fixture'});t.after(()=>dom.window.close());
 const q={...plain(raw),chosen:[null],activeSlot:0,done:false,wrongAttempt:false,feedback:'',feedbackMeta:null,
  manualInputsBySlot:[''],manualWordIndexBySlot:[0],attemptsBySlot:[0],lastWrongBySlot:[null]},state={items:[q],current:0,answered:0,correct:0};
 const c=vm.createContext({EnglishAnswerVariants:variants,window:dom.window,document:dom.window.document,state,COMPOSE_TOKENS_QUESTION_TYPE:'4',
  IS_POLYGLOT_STEP_PREVIEW:false,testUi:k=>k,persistState(){},setTimeout(f){f();return 0;},renderManualSuggestions:()=>'',buildExplanationKey:()=>'',
  ensureExplanation:()=>Promise.resolve(''),updateProgress(){},checkAllDone(){},isPastPerfectContinuousQuestion:()=>false});
 const methods=['getMarkersCount','isPolyglotComposeQuestion','getMarkerLabel','findFirstUnfilledSlot','clampActiveSlot','questionContainsSlotMarker',
  'shouldRenderPolyglotTranslationPreview','ensureManualSlotState','manualAnswerWords','manualAnswerProgress','manualAnswerIsComplete','collectManualAnswer',
  'manualInputId','rememberFeedbackAnswer','manualWordFeedbackState','renderManualGapInput','focusManualAnswer','invalidateManualSuggestions',
  'commitManualWord','submitManualAnswer','renderFeedback','renderFeedbackAnswers','onChoose'];
 if(mode==='card-easy')methods.push('acceptedAnswersForSlot','answerMatchesSlot');
 vm.runInContext([...shared.map(n=>fn(helpers,n)),fn(helpers,'html'),...methods.map(n=>fn(source,n))].join('\n'),c);
 const render=()=>{dom.window.document.body.innerHTML=c.renderManualGapInput(q,0,0,'sentence');};c.render=render;c.rerenderCard=render;render();
 return {q,state,c,inputs:()=>[...dom.window.document.querySelectorAll('input[data-manual-gap]')],
  reset(row){Object.assign(q,plain(row),{chosen:[null],activeSlot:0,done:false,wrongAttempt:false,feedback:'',feedbackMeta:null,
   manualInputsBySlot:[''],manualWordIndexBySlot:[0],attemptsBySlot:[0],lastWrongBySlot:[null],explanation:'',pendingExplanationKey:null});state.correct=0;state.answered=0;render();},
  commit(value){const input=dom.window.document.querySelector('input[data-manual-active="true"]');assert.ok(input);input.value=value;c.commitManualWord(0,0,Number(input.dataset.manualWord),'sentence');}};
}
test('108 finite rows cover six topics with clean Ukrainian hints and exact gap flags',()=>{
 assert.equal(all.length,108);assert.equal(new Set(all.map(q=>q.uuid)).size,108);assert.equal(new Set(all.map(q=>q._topic)).size,6);
 const c=optionsContext('card-easy');for(const q of all){assert.ok(q.psp_content_revision);assert.match(q.verb_hint,/[А-Яа-яІіЇїЄєҐґ]/u);
  assert.doesNotMatch(q.verb_hint,/[A-Za-z]|<[^>]+>|\{a\d+\}/u);if(q.type!=='0')continue;
  assert.equal(q.passive_phrase_word_inputs,true);assert.equal(q.passive_gap_phrase_input,true);assert.equal(c.testAnswerMatches(q,0,q.answers[0]),true);
  assert.equal(c.testAnswerMatches(q,0,''),false);
  if(q.answers[0].split(/\s+/).length>1){const prefix=q.answers[0].split(/\s+/)[0];
   const reviewedCompleteAlias=c.acceptedTestAnswers(q,0).some(answer=>variants.normalize(answer)===variants.normalize(prefix));
   assert.equal(c.testAnswerMatches(q,0,prefix),reviewedCompleteAlias,q.uuid);
  }
 }
});
for(const mode of ['card-easy','step-easy']){
 for(const restored of [false,true])test(`${mode}: ${restored?'persisted':'fresh'} phrase requires all words rather than a prefix`,t=>{
  let raw=plain(all.find(q=>q.uuid==='feq-ton-c2-g02'));assert.ok(raw);assert.equal(raw.answers[0],'That may be so');
  if(restored){
   const c=optionsContext(mode);vm.runInContext(fn(helpers,'mergeFreshQuestionContentIntoSavedState'),c);
   const saved={...plain(raw),passive_phrase_word_inputs:false,feq_option_caption_revision:'stale',chosen:[null],manualInputsBySlot:['saved partial draft'],attemptsBySlot:[2],done:false};
   const state={items:[saved],answered:4,correct:3,current:0};const after=c.mergeFreshQuestionContentIntoSavedState(state);
   raw=plain(after.items[0]);assert.equal(raw.passive_phrase_word_inputs,true);assert.equal(raw.feq_option_caption_revision,raw.psp_content_revision);
   assert.deepEqual(raw.manualInputsBySlot,['saved partial draft']);assert.deepEqual(raw.attemptsBySlot,[2]);assert.equal(after.answered,4);assert.equal(after.correct,3);
  }
  const f=gapFixture(t,mode,raw);assert.equal(f.inputs().length,4);
  f.commit('That');assert.equal(f.q.done,false);assert.equal(f.state.correct,0);assert.equal(f.q.chosen[0],null);
  f.commit('may');assert.equal(f.q.done,false);assert.equal(f.state.correct,0);assert.equal(f.q.chosen[0],null);
  f.commit('be');assert.equal(f.q.done,false);assert.equal(f.state.correct,0);f.commit('so?!');assert.equal(f.q.done,true);assert.equal(f.state.correct,1);
  f.reset(raw);f.commit('That might be so?!');assert.equal(f.q.done,true);assert.equal(f.state.correct,1,'An explicitly reviewed complete short alias is accepted.');
 });
 test(`${mode}: credit requires a complete phrase or an explicitly reviewed complete short alias`,t=>{
  const f=gapFixture(t,mode,all.find(q=>q.type==='0'));let checked=0;
  for(const raw of all.filter(q=>q.type==='0'))for(const alternative of [...new Set([raw.answers[0],...(raw.answer_synonyms_by_marker?.a1||[])])]){
   f.reset(raw);assert.equal(f.inputs().length,raw.answers[0].split(/\s+/).length,raw.uuid);
   const words=alternative.split(/\s+/);for(const[i,word]of words.entries()){
    f.commit(i===words.length-1?word.replace(/[.,!?;:]/g,'')+'?!':word);
    if(i<words.length-1){
     if(f.q.done){const prefix=words.slice(0,i+1).join(' ');
      assert.ok(f.c.acceptedTestAnswers(f.q,0).some(answer=>variants.normalize(answer)===variants.normalize(prefix)),raw.uuid+' must not credit an unaccepted prefix');
      break;
     }
     assert.equal(f.q.chosen[0],null);assert.equal(f.state.correct,0);
    }
   }assert.equal(f.q.done,true,raw.uuid+'/'+alternative);assert.equal(f.state.correct,1);
   // A short synonym such as provided must not make the full provided that unreachable.
   f.reset(raw);f.commit(alternative.replace(/[.,!?;:]/g,'')+'?!');
   assert.equal(f.q.done,true,raw.uuid+'/'+alternative+' complete pasted phrase');assert.equal(f.state.correct,1);checked++;
  }assert.ok(checked>=72);
 });
 test(`${mode}: every compose slot has short, role-specific, varied choices`,()=>{
  const c=optionsContext(mode),sets=new Set();let slots=0;
  for(const raw of all.filter(q=>q.type==='4')){const q=plain(raw);q.chosen=[];q.optionsBySlot=c.normalizeOptionsBySlot(q);
   for(let i=0;i<q.answers.length;i++){slots++;q.activeSlot=i;const choices=Array.from(c.getActiveOptions(q));assertWordChoiceCount(c,q,i,choices,variants);assert.ok(choices.some(value=>variants.normalize(value)===variants.normalize(q.answers[i])));
    sets.add(JSON.stringify(choices));for(const choice of choices)assert.ok(choice.split(/\s+/).length<=3,q.uuid);
    assert.equal(c.testAnswerMatches(q,i,q.answers[i].replace(/[.,!?;:]/g,'')),true,q.uuid);q.chosen[i]=q.answers[i];assert.equal(c.syncPresentPerfectPassiveWordPath(q),true);
   }
  }assert.ok(slots>=100);assert.ok(sets.size>90,'Not one recycled distractor list');
 });
 test(`${mode}: every finite lexical and contracted sentence is reachable in its reviewed slots`,()=>{
  const c=optionsContext(mode);let checked=0;
  for(const raw of all.filter(q=>q.type==='4'))for(const sentence of raw.compose_accepted_sentences){
   assertWordSentencePath(c,raw,sentence,variants);checked++;
  }assert.ok(checked>=54);
 });
}
test('all six actual mixed presentations interleave three formats at each level',()=>{
 assert.equal(mixed.length,108);for(const topic of new Set(mixed.map(q=>q._topic))){let previous=null;
  for(const level of ['A1','A2','B1','B2','C1','C2']){const counts={gap:0,compose:0,order:0};
   for(const q of mixed.filter(q=>q._topic===topic&&q.level===level)){const kind=q.presentation==='sentence_reorder'?'order':q.type==='4'?'compose':'gap';counts[kind]++;assert.notEqual(kind,previous,topic);previous=kind;}
   assert.deepEqual(counts,{gap:1,compose:1,order:1},topic+'/'+level);
  }
 }
});
test('all 36 order questions support click, manual deletion/reuse and punctuation-free grading',()=>{
 const source=read('resources/views/components/sentence-reorder-question-js.blade.php').match(/<script>([\s\S]*?)<\/script>/)[1];
 const c=vm.createContext({TEST_LOCALE:'uk',EnglishAnswerVariants:variants,testUi:k=>k,
  html:v=>String(v??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('"','&quot;'),isPastPerfectContinuousQuestion:()=>false});
 vm.runInContext(source,c);const orders=mixed.filter(q=>q.presentation==='sentence_reorder');assert.equal(orders.length,36);
 for(const raw of orders){const q={...plain(raw),done:false};assert.equal(q.reorder_manual_input,true);const rendered=c.renderSentenceReorderQuestion(q);
  assert.match(rendered,/data-reorder-manual-input/);assert.doesNotMatch(rendered,/datalist|\{a\d+\}/u);
  const index=q.reorder_tokens.indexOf(q.reorder_groups[0]);assert.ok(index>=0);c.applySentenceReorderAction(q,{dataset:{reorderAction:'add',reorderTokenIndex:String(index)}});
  assert.ok(q.reorderUsed.includes(index),q.uuid+'/'+index+'/'+JSON.stringify(q.reorder_tokens));c.syncSentenceReorderManualText(q,'');assert.equal(q.reorderUsed.length,0);
  c.applySentenceReorderAction(q,{dataset:{reorderAction:'add',reorderTokenIndex:String(index)}});assert.ok(q.reorderUsed.includes(index));
  c.syncSentenceReorderManualText(q,q.reorder_answer.replace(/[.,!?;:]/g,'')+'?!');
  assert.equal(q.reorderUsed.length,q.reorder_tokens.length,q.uuid+' must reclaim every canonical token instance');
  assert.equal(c.applySentenceReorderAction(q,{dataset:{reorderAction:'check'}}),'checked',q.uuid);
  assert.equal(q.feedback,'correct',q.uuid);
  for(const answer of raw.reorder_accepted_answers){
   const alternative={...plain(raw),done:false};c.syncSentenceReorderManualText(alternative,answer.replace(/[.,!?;:]/g,'')+'?!');
   assert.equal(c.applySentenceReorderAction(alternative,{dataset:{reorderAction:'check'}}),'checked',raw.uuid+'/'+answer);
   assert.equal(alternative.feedback,'correct',raw.uuid+'/'+answer);
  }
 }assert.equal(source.includes('.focus('),false,'Token clicks do not open native suggestions.');
});
test('refreshing owned choices preserves learner drafts, prior answers and counters',()=>{
 const c=optionsContext('card-easy');vm.runInContext(fn(helpers,'mergeFreshQuestionContentIntoSavedState'),c);
 const fresh=all.find(q=>q.type==='4'),old={...plain(fresh),compose_content_revision:'old',options_by_marker:fresh.answers.map(a=>[a,'wealth','fell','lorry']),
  chosen:[fresh.answers[0]],manualInputsBySlot:['unfinished'],attemptsBySlot:[2],activeSlot:1,done:false};
 const state={items:[old],answered:2,correct:1,current:0},after=c.mergeFreshQuestionContentIntoSavedState(state);
 for(const key of ['answered','correct','current'])assert.equal(after[key],state[key]);
 for(const key of ['chosen','manualInputsBySlot','attemptsBySlot','activeSlot','done'])assert.deepEqual(plain(after.items[0][key]),old[key]);
 assert.deepEqual(plain(after.items[0].options_by_marker),fresh.options_by_marker);
});

for(const mode of ['card-easy','step-easy']){
 test(`${mode}: FormalEnglish compose captions hide only sentence punctuation while raw options and synonyms stay intact`,()=>{
  const c=optionsContext(mode);let labelled=0;
  for(const raw of all.filter(q=>q.type==='4')){
   const q=plain(raw),before=JSON.stringify(q);
   for(let slot=0;slot<q.answers.length;slot++)for(const option of q.options_by_marker[q.markers[slot]]){
    const label=c.formatAnswerOptionLabel(q,slot,option);labelled++;
    assert.doesNotMatch(label,/[.!?;:]/u,q.uuid+'/'+option);
    assert.equal(label.includes(variants.withoutSentencePunctuation(option)),true,q.uuid+'/'+option);
   }
   assert.equal(JSON.stringify(q),before,'Rendering does not rewrite values or aliases');
  }assert.ok(labelled>360);
  const q=plain(all.find(row=>row.type==='4'));
  assert.equal(c.finitePracticeOptionCaption(q,"James’ well-known 3.14-pound parcel."),"James' well-known 3.14-pound parcel");
  for(const foreign of [{...q,uuid:'qnq-wh-a1-c01'},{...q,feq_option_caption_revision:'stale'},
   {...q,psp_content_revision:'stale'},{...q,type:'0',presentation:undefined}]){
   assert.equal(c.finitePracticeOptionCaption(foreign,'with the result.'),'with the result.');
  }
  const foreign={...q,uuid:'historical-course-question'},before=JSON.stringify(foreign);
  const withoutFlag={...foreign};delete withoutFlag.feq_option_caption_revision;
  assert.equal(c.formatAnswerOptionLabel(foreign,0,foreign.answers[0]),c.formatAnswerOptionLabel(withoutFlag,0,foreign.answers[0]));
  assert.equal(JSON.stringify(foreign),before);
 });
}

test('FormalEnglish reorder bank captions are clean but click values, token indices and foreign captions remain byte-exact',()=>{
 const source=read('resources/views/components/sentence-reorder-question-js.blade.php').match(/<script>([\s\S]*?)<\/script>/)[1];
 const c=vm.createContext({TEST_LOCALE:'uk',EnglishAnswerVariants:variants,testUi:k=>k,html:v=>String(v??''),isPastPerfectContinuousQuestion:()=>false});
 vm.runInContext(fn(helpers,'finitePracticeOptionCaption')+'\n'+source,c);
 const dom=new JSDOM('<!doctype html><body></body>');
 try{for(const raw of mixed.filter(q=>q.presentation==='sentence_reorder')){
  const q={...plain(raw),done:false},tokens=plain(raw.reorder_tokens);
  dom.window.document.body.innerHTML=c.renderSentenceReorderQuestion(q);
  const buttons=[...dom.window.document.querySelectorAll('button[data-reorder-action="add"]')];
  assert.equal(buttons.length,tokens.length);
  for(const[index,button]of buttons.entries()){
   assert.equal(button.dataset.reorderTokenIndex,String(index));
   assert.equal(button.textContent,variants.withoutSentencePunctuation(tokens[index]));
  }
  const index=tokens.findIndex(value=>/[.!?,;:]$/.test(value));assert.ok(index>=0);
  c.applySentenceReorderAction(q,{dataset:{reorderAction:'add',reorderTokenIndex:String(index)}});
  assert.equal(q.reorderManualText,tokens[index],'Caption cleanup must not alter the clicked value');
  assert.deepEqual(plain(q.reorder_tokens),tokens);c.syncSentenceReorderManualText(q,'');assert.equal(q.reorderUsed.length,0);
  const foreign={...plain(raw),uuid:'other-course-reorder',done:false};
  dom.window.document.body.innerHTML=c.renderSentenceReorderQuestion(foreign);
  assert.deepEqual([...dom.window.document.querySelectorAll('button[data-reorder-action="add"]')].map(button=>button.textContent),tokens);
 }}finally{dom.window.close();}
});

test('a cached FormalEnglish draft receives the display flag without changing answers, manual drafts or counters',()=>{
 const c=optionsContext('card-easy');vm.runInContext(fn(helpers,'mergeFreshQuestionContentIntoSavedState'),c);
 const fresh=all.find(q=>q.type==='4'),saved={...plain(fresh),feq_option_caption_revision:'old',chosen:[fresh.answers[0]],
  manualInputsBySlot:['kept draft'],attemptsBySlot:[2],activeSlot:1,done:false};
 const state={items:[saved],answered:2,correct:1,current:0},after=c.mergeFreshQuestionContentIntoSavedState(state);
 assert.equal(after.items[0].feq_option_caption_revision,fresh.psp_content_revision);
 for(const field of ['chosen','manualInputsBySlot','attemptsBySlot','activeSlot','done','answers','answer_map','options_by_marker'])assert.deepEqual(plain(after.items[0][field]),saved[field]);
 for(const field of ['answered','correct','current'])assert.equal(after[field],state[field]);
});

test('a pre-profile cached order run gains manual authored groups while retaining 1/18 credit and its draft',()=>{
 const fresh=mixed.find(q=>q.presentation==='sentence_reorder'),gap=all.find(q=>q.type==='0'&&q.uuid!==fresh.uuid);
 const c=optionsContext('card-easy');c.getTechnicalQuestions=()=>plain([gap,fresh]);
 vm.runInContext(fn(helpers,'mergeFreshQuestionContentIntoSavedState'),c);
 const draft=fresh.reorder_groups[0],old={...plain(fresh),reorder_manual_input:false,reorder_groups:['old blind group'],
  reorder_tokens:['old blind group'],reorderChosen:[{tokenIndex:0,token:draft}],reorderUsed:[0],
  manualInputsBySlot:['kept typed draft'],attemptsBySlot:[2],done:false};
 delete old.reorderManualText;
 const state={items:[{...plain(gap),chosen:[gap.answers[0]],done:true},old],answered:1,correct:1,current:1};
 const after=c.mergeFreshQuestionContentIntoSavedState(state);
 assert.equal(after.answered,1);assert.equal(after.correct,1);assert.equal(after.current,1);
 assert.equal(after.items[0].done,true);assert.deepEqual(plain(after.items[0].chosen),[gap.answers[0]]);
 const restored=after.items[1];assert.equal(restored.reorder_manual_input,true);
 assert.deepEqual(plain(restored.reorder_groups),fresh.reorder_groups);assert.deepEqual(plain(restored.reorder_tokens),fresh.reorder_tokens);
 assert.deepEqual(plain(restored.attemptsBySlot),[2]);assert.deepEqual(plain(restored.manualInputsBySlot),['kept typed draft']);
 c.TEST_LOCALE='uk';c.testUi=k=>k;c.html=v=>String(v??'');c.isPastPerfectContinuousQuestion=()=>false;
 vm.runInContext(read('resources/views/components/sentence-reorder-question-js.blade.php').match(/<script>([\s\S]*?)<\/script>/)[1],c);
 assert.match(c.renderSentenceReorderQuestion(restored),/data-reorder-manual-input/);
 assert.equal(restored.reorderManualText,draft);assert.equal(after.correct,1);
});

for(const mode of ['card-easy','step-easy'])test(`${mode}: explicitly reviewed incompatible synonym combinations never receive full credit, regardless of punctuation`,()=>{
 const c=optionsContext(mode);let checked=0;
 for(const raw of all.filter(q=>q.type==='4'&&q._reviewed_rejected_targets?.length)){
  for(const rejected of raw._reviewed_rejected_targets)for(const punctuation of [false,true]){
   const q=plain(raw);q.chosen=[];
   const expected=variants.normalize(punctuation?rejected.replace(/[.,!?;:]/g,'')+'?!':rejected);
   const path=(slot,remaining,chosen)=>{
    if(slot===q.answers.length)return remaining===''?chosen:null;
    q.chosen=[...chosen];
    for(const candidate of c.acceptedTestAnswers(q,slot)){
     const key=variants.normalize(candidate);if(remaining!==key&&!remaining.startsWith(key+' '))continue;
     if(!c.testAnswerMatches(q,slot,punctuation?candidate.replace(/[.,!?;:]/g,'')+'?!':candidate))continue;
     const result=path(slot+1,remaining.slice(key.length).trim(),[...chosen,candidate]);if(result)return result;
    }return null;
   };
   assert.equal(path(0,expected,[]),null,raw.uuid+'/'+rejected+' must not have a fully accepted slot path');checked++;
  }
 }
 assert.equal(checked,12,'Six finite invalid hybrids, with and without punctuation');
});
