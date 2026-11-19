'use strict';
// Shipped card/step/order functions, finite compiled data. No live database or network.
const {test}=require('node:test'),assert=require('node:assert/strict');
const fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const {execFileSync}=require('node:child_process'),{JSDOM}=require('jsdom');
const root=path.resolve(__dirname,'../..'),read=p=>fs.readFileSync(path.join(root,p),'utf8'),plain=v=>JSON.parse(JSON.stringify(v));
const variants=require('./load-answer-variants.cjs');
const fixture=(...args)=>JSON.parse(execFileSync(process.env.PHP_BINARY||'C:/Program Files/xampp/php/php.exe',
 [path.join(root,'tests/Fixtures/questions-negations-quality.php'),...args],{cwd:root,encoding:'utf8',maxBuffer:20*1024*1024}));
const all=fixture(),mixed=fixture('--mixed'),helpers=read('resources/views/components/saved-test-js-helpers.blade.php');
function fn(source,name){const start=source.indexOf(`function ${name}(`);assert.notEqual(start,-1,name);const tail=source.slice(start),end=tail.search(/^\}/m);assert.notEqual(end,-1,name);return tail.slice(0,end+1);}
const {wordPolicyHelpers, assertWordSentencePath, assertWordChoiceCount} = require('./finite-word-policy-harness.cjs');
const shared=[...wordPolicyHelpers, 'canonicalTestAnswer','composeSentenceChoiceMatches','reviewedQuestionSurfaceAnswers','acceptedTestAnswers','testAnswerMatches','isPassivePhraseGapQuestion',
 'isReviewedComposePhraseSlot','normalizeManualGapInput','composeManualAnswerWords'];
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
test('648 finite rows cover twelve topics with clean Ukrainian hints and exact gap flags',()=>{
 assert.equal(all.length,648);assert.equal(new Set(all.map(q=>q.uuid)).size,648);assert.equal(new Set(all.map(q=>q._topic)).size,12);
 const c=optionsContext('card-easy');for(const q of all){assert.ok(q.psp_content_revision);assert.match(q.verb_hint,/[А-Яа-яІіЇїЄєҐґ]/u);
  assert.doesNotMatch(q.verb_hint,/[A-Za-z]|<[^>]+>|\{a\d+\}/u);if(q.type!=='0')continue;
  assert.equal(q.passive_phrase_word_inputs,true);assert.equal(q.passive_gap_phrase_input,true);assert.equal(c.testAnswerMatches(q,0,q.answers[0]),true);
  assert.equal(c.testAnswerMatches(q,0,''),false);if(q.answers[0].split(/\s+/).length>1)assert.equal(c.testAnswerMatches(q,0,q.answers[0].split(/\s+/)[0]),false,q.uuid);
 }
});
for(const mode of ['card-easy','step-easy']){
 test(`${mode}: all missing phrases and aliases require every word before awarding credit`,t=>{
  const f=gapFixture(t,mode,all.find(q=>q.type==='0'));let checked=0;
  for(const raw of all.filter(q=>q.type==='0'))for(const alternative of [...new Set([raw.answers[0],...(raw.answer_synonyms_by_marker?.a1||[])])]){
   f.reset(raw);assert.equal(f.inputs().length,raw.answers[0].split(/\s+/).length,raw.uuid);
   const words=alternative.split(/\s+/);for(const[i,word]of words.entries()){
    f.commit(i===words.length-1?word.replace(/[.,!?;:]/g,'')+'?!':word);
    if(i<words.length-1){assert.equal(f.q.done,false,raw.uuid);assert.equal(f.q.chosen[0],null);assert.equal(f.state.correct,0);}
   }assert.equal(f.q.done,true,raw.uuid+'/'+alternative);assert.equal(f.state.correct,1);checked++;
  }assert.ok(checked>=432);
 });
 test(`${mode}: every compose slot has short, role-specific, varied choices`,()=>{
  const c=optionsContext(mode),sets=new Set();let slots=0;
  for(const raw of all.filter(q=>q.type==='4')){const q=plain(raw);q.chosen=[];q.optionsBySlot=c.normalizeOptionsBySlot(q);
   for(let i=0;i<q.answers.length;i++){slots++;q.activeSlot=i;const choices=Array.from(c.getActiveOptions(q));assertWordChoiceCount(c,q,i,choices,variants);assert.ok(choices.some(value=>variants.normalize(value)===variants.normalize(q.answers[i])));
    sets.add(JSON.stringify(choices));for(const choice of choices)assert.ok(choice.split(/\s+/).length<=3,q.uuid);
    assert.equal(c.testAnswerMatches(q,i,q.answers[i].replace(/[.,!?;:]/g,'')),true,q.uuid);q.chosen[i]=q.answers[i];assert.equal(c.syncPresentPerfectPassiveWordPath(q),true);
   }
  }assert.ok(slots>=500);assert.ok(sets.size>100,'Not one recycled distractor list');
 });
 test(`${mode}: every finite lexical and contracted sentence is reachable in its reviewed slots`,()=>{
  const c=optionsContext(mode);let checked=0;
  for(const raw of all.filter(q=>q.type==='4'))for(const sentence of raw.compose_accepted_sentences){
   assertWordSentencePath(c,raw,sentence,variants);checked++;
  }assert.ok(checked>=216);
 });
}
test('negative compose full-form branches reject every crossed auxiliary/NOT-placement combination',()=>{
 let valid=0,invalid=0;
 for(const mode of ['card-easy','step-easy']){
  const c=optionsContext(mode);
  for(const raw of all.filter(q=>q._topic==='negative-questions'&&q.type==='4')){
   const paths=c.presentPerfectPassiveWordPaths(raw);
   const allowed=new Set(paths.map(words=>variants.normalize(words.join(' '))));let combinations=[[]];
   for(let slot=0;slot<raw.answers.length;slot++){
    const marker=raw.markers[slot];
    const candidates=[...new Set([raw.answers[slot],...(raw.answer_synonyms_by_marker?.[marker]||[]),
     ...(raw.qnq_strict_slot_surfaces?.answers_by_marker?.[marker]||[])])];
    combinations=combinations.flatMap(parts=>candidates.map(value=>[...parts,value]));
   }
   // Whole-word branches can differ in length: include each complete contraction/full path,
   // then cross prefixes/suffixes to keep the no-hybrid assertion exercised.
   combinations.push(...paths.map(words=>Array.from(words)));
   for(const left of paths)for(const right of paths)for(let cut=1;cut<Math.min(left.length,right.length);cut++){
    combinations.push([...left.slice(0,cut),...right.slice(cut)]);
   }
   const unique=new Map(combinations.map(parts=>[variants.normalize(parts.join(' ')),parts]));
   for(const parts of unique.values()){
    const q=plain(raw);q.chosen=[];let accepted=true;
    for(let slot=0;slot<parts.length;slot++){
     if(!c.testAnswerMatches(q,slot,parts[slot])){accepted=false;break;}
     q.chosen[slot]=parts[slot];assert.equal(c.syncPresentPerfectPassiveWordPath(q),true);
    }
    if(accepted&&!q.chosen.every(Boolean))accepted=false;
    const sentence=parts.join(' '),expected=allowed.has(variants.normalize(sentence));
    assert.equal(accepted,expected,mode+'/'+raw.uuid+'/'+sentence);
    if(expected)valid++;else invalid++;
   }
  }
 }assert.ok(valid>=72);assert.ok(invalid>=72,'Crossed short/full NOT placement is rejected, not added as another synonym.');
});
test('all twelve actual mixed presentations interleave three formats at each level',()=>{
 assert.equal(mixed.length,648);for(const topic of new Set(mixed.map(q=>q._topic))){let previous=null;
  for(const level of ['A1','A2','B1','B2','C1','C2']){const counts={gap:0,compose:0,order:0};
   for(const q of mixed.filter(q=>q._topic===topic&&q.level===level)){const kind=q.presentation==='sentence_reorder'?'order':q.type==='4'?'compose':'gap';counts[kind]++;assert.notEqual(kind,previous,topic);previous=kind;}
   assert.deepEqual(counts,{gap:3,compose:3,order:3},topic+'/'+level);
  }
 }
});
test('all216 order questions support click, manual deletion/reuse and punctuation-free grading',()=>{
 const source=read('resources/views/components/sentence-reorder-question-js.blade.php').match(/<script>([\s\S]*?)<\/script>/)[1];
 const c=vm.createContext({TEST_LOCALE:'uk',EnglishAnswerVariants:variants,testUi:k=>k,
  html:v=>String(v??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('"','&quot;'),isPastPerfectContinuousQuestion:()=>false});
 vm.runInContext(source,c);const orders=mixed.filter(q=>q.presentation==='sentence_reorder');assert.equal(orders.length,216);
 for(const raw of orders){const q={...plain(raw),done:false};assert.equal(q.reorder_manual_input,true);const rendered=c.renderSentenceReorderQuestion(q);
  assert.match(rendered,/data-reorder-manual-input/);assert.doesNotMatch(rendered,/datalist|\{a\d+\}/u);
  const index=q.reorder_tokens.indexOf(q.reorder_groups[0]);assert.ok(index>=0);c.applySentenceReorderAction(q,{dataset:{reorderAction:'add',reorderTokenIndex:String(index)}});
  assert.ok(q.reorderUsed.includes(index));c.syncSentenceReorderManualText(q,'');assert.equal(q.reorderUsed.length,0);
  c.applySentenceReorderAction(q,{dataset:{reorderAction:'add',reorderTokenIndex:String(index)}});assert.ok(q.reorderUsed.includes(index));
  c.syncSentenceReorderManualText(q,q.reorder_answer.replace(/[.,!?;:]/g,'')+'?!');assert.equal(c.applySentenceReorderAction(q,{dataset:{reorderAction:'check'}}),'checked',q.uuid);
  assert.equal(q.feedback,'correct',q.uuid);
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

test('all100 exact Ukrainian lexical hint clarifications refresh saved copy without changing answers or progress',()=>{
 const map=JSON.parse(read('docs/content/questions-negations-hint-clarifications.v1.json'));
 assert.equal(map.entries.length,100);const c=optionsContext('card-easy');vm.runInContext(fn(helpers,'mergeFreshQuestionContentIntoSavedState'),c);
 const rawRows=new Map();
 for(const topic of new Set(all.map(q=>q._topic))){
  const stem={
   'question-forms':'QuestionForms','yes-no':'YesNoQuestionsGeneralQuestions','wh':'WhQuestionsSpecialQuestions','subject':'SubjectQuestions',
   'answers':'AnswersToQuestions','alternative':'AlternativeQuestions','tags':'QuestionTagsDisjunctiveQuestions','negative-questions':'NegativeQuestions',
   'embedded':'EmbeddedQuestions','indirect':'IndirectQuestions','negative-forms':'NegativeForms','double-negatives':'DoubleNegatives'
  }[topic];
  const p=`database/seeders/V3/QuestionsNegations/UkrainianMixed${stem}QualityStandardSeeder/definition.json`;
  for(const q of JSON.parse(read(p)).questions)rawRows.set(q.uuid,q);
 }
 for(const entry of map.entries){
  const fresh=all.find(q=>q.uuid===entry.question_uuid),raw=rawRows.get(entry.question_uuid);assert.ok(fresh&&raw,entry.question_uuid);
  assert.equal(fresh.psp_content_revision,entry.source_row_sha256);assert.equal(fresh.verb_hint,entry.after_hint_uk);assert.equal(fresh.verb_hints.a1,entry.after_hint_uk);
  assert.deepEqual(fresh.answers,Object.values(raw.markers).map(m=>m.answer));assert.equal(fresh.question,raw.question);
  const lexical=JSON.parse(read('docs/content/questions-negations-answer-variants.v1.json')).entries.find(e=>e.question_uuid===entry.question_uuid);
  const full=[...raw.accepted_target_texts];
  if(lexical)for(const alias of lexical.added_answers)for(const value of variants.variants(alias))full.push(raw.question.replace('{a1}',value));
  assert.deepEqual(fresh.reorder_accepted_answers,[...new Set(full)]);assert.equal(raw.verb_hint,entry.before_hint_uk);
  for(const cue of entry.lexical_cues_uk)assert.ok(entry.after_hint_uk.toLowerCase().includes(cue.toLowerCase()));
  // A saved browser state already contains the pre-existing contraction
  // presentation (including the proven pronoun lift), not the raw DB stem.
  const prepared=plain(variants.prepareQuestion(plain(fresh)));
  const old={...prepared,verb_hint:entry.before_hint_uk,verb_hints:{a1:entry.before_hint_uk},chosen:[prepared.answers[0]],
   done:true,manualInputsBySlot:['kept draft'],attemptsBySlot:[2],activeSlot:0};
  const state={items:[old],answered:3,correct:3,current:4},after=c.mergeFreshQuestionContentIntoSavedState(state);
  assert.equal(after.items[0].verb_hint,entry.after_hint_uk);assert.equal(after.items[0].verb_hints.a1,entry.after_hint_uk);
  for(const key of ['chosen','done','manualInputsBySlot','attemptsBySlot','activeSlot'])assert.deepEqual(plain(after.items[0][key]),old[key]);
  for(const key of ['answered','correct','current'])assert.equal(after[key],state[key]);
 }
});

test('two exact lexical gap aliases accept full and contracted forms, display parentheses and never award prefix credit',t=>{
 const entries=JSON.parse(read('docs/content/questions-negations-answer-variants.v1.json')).entries;assert.equal(entries.length,2);
 for(const mode of ['card-easy','step-easy']){
  const first=all.find(q=>q.uuid===entries[0].question_uuid),f=gapFixture(t,mode,first);vm.runInContext(fn(helpers,'formatAnswerOptionLabel'),f.c);
  for(const entry of entries){const raw=all.find(q=>q.uuid===entry.question_uuid);assert.ok(raw);assert.equal(raw.answers[0],entry.primary_answer);
   assert.equal(f.c.formatAnswerOptionLabel(raw,0,raw.answers[0]),`${entry.primary_answer} (${entry.added_answers[0]})`);
   const accepted=[...new Set([entry.primary_answer,...variants.variants(entry.primary_answer),...entry.added_answers.flatMap(a=>variants.variants(a))])];
   for(const answer of accepted){f.reset(raw);const words=answer.split(/\s+/);assert.equal(f.c.testAnswerMatches(raw,0,words[0]),false);
    for(const[i,word]of words.entries()){f.commit(i===words.length-1?word+'?!':word);if(i<words.length-1){assert.equal(f.q.done,false);assert.equal(f.q.chosen[0],null);assert.equal(f.state.correct,0);}}
    assert.equal(f.q.done,true,mode+'/'+raw.uuid+'/'+answer);assert.equal(f.state.correct,1);
   }
   assert.equal(f.c.testAnswerMatches(raw,0,'does not nullify'),false);assert.equal(f.c.testAnswerMatches(raw,0,'is not delayed'),false);
   for(const wrong of raw.options.filter(v=>v!==entry.primary_answer))assert.equal(f.c.testAnswerMatches(raw,0,wrong),false,raw.uuid+'/'+wrong);
  }
 }
});

const negativeAuxiliary={"isn't":'is',"aren't":'are',"wasn't":'was',"weren't":'were',"don't":'do',"doesn't":'does',"didn't":'did',
 "haven't":'have',"hasn't":'has',"hadn't":'had',"can't":'can',"couldn't":'could',"won't":'will',"wouldn't":'would',
 "shouldn't":'should',"mightn't":'might',"mustn't":'must',"needn't":'need',"oughtn't":'ought'};
function invalidInvertedExpansion(text){
 const words=text.split(/\s+/),aux=negativeAuxiliary[words[0].toLowerCase().replaceAll('’',"'")];
 return aux?`${aux} not ${words.slice(1).join(' ')}`:null;
}
for(const mode of ['card-easy','step-easy']){
 test(`${mode}: all36 negative-question gaps reject NOT before the subject and retain complete full forms`,t=>{
  const rows=all.filter(q=>q._topic==='negative-questions'&&q.type==='0');assert.equal(rows.length,36);
  const c=optionsContext(mode),f=gapFixture(t,mode,rows[0]);
  for(const raw of rows){
   assert.ok(raw.qnq_strict_slot_surfaces);const surfaces=Array.from(c.reviewedQuestionSurfaceAnswers(raw,0));
   assert.ok(surfaces.length>=2,raw.uuid);assert.ok(surfaces.includes(raw.answers[0]));
   const wrong=invalidInvertedExpansion(raw.answers[0]);assert.ok(wrong,raw.uuid);
   assert.equal(c.testAnswerMatches(raw,0,wrong),false,raw.uuid+'/'+wrong);
   assert.equal(f.c.manualAnswerProgress(raw,0,wrong),'incorrect',raw.uuid+'/'+wrong);
   assert.equal(c.testAnswerMatches(raw,0,raw.answers[0].split(/\s+/)[0]),false,raw.uuid+' incomplete auxiliary');
   for(const answer of surfaces){
    assert.equal(c.testAnswerMatches(raw,0,answer.toUpperCase().replace(/[.,!?;:]/g,'')+'?!'),true,raw.uuid+'/'+answer);
    f.reset(raw);const words=answer.split(/\s+/);for(const[i,word]of words.entries()){
     f.commit(i===words.length-1?word.replace(/[.,!?;:]/g,'')+'?!':word);
     if(i<words.length-1){assert.equal(f.q.done,false);assert.equal(f.q.chosen[0],null);assert.equal(f.state.correct,0);}
    }assert.equal(f.q.done,true,raw.uuid+'/'+answer);assert.equal(f.state.correct,1);
   }
  }
 });
 test(`${mode}: finite tag and compose auxiliary slots do not manufacture inverted expansions`,()=>{
  const c=optionsContext(mode);let tags=0,negativeCompose=0;
  for(const raw of all.filter(q=>['tags','negative-questions'].includes(q._topic))){
   const q=plain(raw);q.chosen=[];
   for(let slot=0;slot<q.answers.length;slot++){
    const reviewed=c.reviewedQuestionSurfaceAnswers(q,slot);
    if(reviewed!==null){
     assert.equal(c.testAnswerMatches(q,slot,q.answers[slot]),true,q.uuid+'/'+slot);
     const wrong=invalidInvertedExpansion(q.answers[slot]);
     if(wrong){assert.equal(c.testAnswerMatches(q,slot,wrong),false,q.uuid+'/'+wrong);}
     if(q._topic==='tags')tags++;else if(q.type==='4')negativeCompose++;
    }
    q.chosen[slot]=q.answers[slot];
   }
  }assert.ok(tags>=54);assert.equal(negativeCompose,18);
 });
}
test('strict surfaces are exact-revision opt-ins and refreshing them preserves learner state',()=>{
 const c=optionsContext('card-easy');vm.runInContext(fn(helpers,'mergeFreshQuestionContentIntoSavedState'),c);
 const fresh=all.find(q=>q._topic==='negative-questions'&&q.type==='0');assert.ok(fresh.qnq_strict_slot_surfaces);
 for(const mutate of [q=>delete q.qnq_strict_slot_surfaces,q=>q.qnq_strict_slot_surfaces.revision='stale',q=>q.qnq_strict_slot_surfaces.answers_by_marker.a1=['foreign']]){
  const q=plain(fresh);mutate(q);assert.equal(c.reviewedQuestionSurfaceAnswers(q,0),null);
 }
 const old={...plain(fresh),qnq_strict_slot_surfaces:{revision:'stale',answers_by_marker:{a1:['wrong order']}},
  chosen:[null],manualInputsBySlot:['unfinished'],manualWordIndexBySlot:[0],attemptsBySlot:[1],done:false,activeSlot:0};
 const state={items:[old],answered:2,correct:1,current:0};const updated=c.mergeFreshQuestionContentIntoSavedState(state);
 assert.deepEqual(plain(updated.items[0].qnq_strict_slot_surfaces),fresh.qnq_strict_slot_surfaces);
 for(const key of ['chosen','manualInputsBySlot','manualWordIndexBySlot','attemptsBySlot','done','activeSlot'])assert.deepEqual(plain(updated.items[0][key]),old[key]);
 for(const key of ['answered','correct','current'])assert.equal(updated[key],state[key]);
 const legacy={type:'0',question:'You {a1} be late.',markers:['a1'],answers:['will not'],answer_map:{a1:'will not'}};
 assert.equal(c.testAnswerMatches(legacy,0,"won't"),true,'Unaffiliated contraction policy remains unchanged.');
});
for(const mode of ['card-easy','step-easy']){
 test(`${mode}: short affirmative replies keep stressed auxiliaries while negative contractions remain valid`,()=>{
  const c=optionsContext(mode);let rejected=0,negativeAliases=0;
  for(const raw of all.filter(q=>q._topic==='answers')){
   assert.ok(raw.qnq_strict_slot_surfaces,raw.uuid);
   if(raw.type==='0'){
    const answer=raw.answers[0],reviewed=Array.from(c.reviewedQuestionSurfaceAnswers(raw,0));
    assert.equal(c.testAnswerMatches(raw,0,answer),true);
    if(!/\bnot\b|n't|cannot/i.test(answer))for(const bad of variants.variants(answer)){
     if(variants.normalize(bad)===variants.normalize(answer)||!bad.includes("'"))continue;
     assert.equal(c.testAnswerMatches(raw,0,bad),false,raw.uuid+'/'+bad);rejected++;
    }
    for(const alias of reviewed)if(alias.includes("'")){
     assert.equal(c.testAnswerMatches(raw,0,alias),true,raw.uuid+'/'+alias);negativeAliases++;
    }
   }else for(const sentence of raw.compose_accepted_sentences){
    assert.doesNotMatch(sentence,/^Yes,?\s+(?:I'm|you're|he's|she's|it's|we're|they're|I've|you've|we've|they've|I'll|you'll|he'll|she'll|it'll|we'll|they'll|I'd|you'd|he'd|she'd|it'd|we'd|they'd)[.!?]*$/i);
   }
  }assert.ok(rejected>=5,'Actual affirmative short answers were checked');assert.ok(negativeAliases>=10,'Finite negative contractions remain usable');
 });
}
