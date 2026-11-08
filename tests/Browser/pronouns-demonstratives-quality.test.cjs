'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs'), path = require('node:path'), vm = require('node:vm');
const {execFileSync} = require('node:child_process');
const root = path.join(__dirname, '../..');
const read = p => fs.readFileSync(path.join(root, p), 'utf8');
const php = process.env.PHP_BINARY || 'C:/Program Files/xampp/php/php.exe';
const all = JSON.parse(execFileSync(php, [path.join(root, 'tests/Fixtures/pronouns-demonstratives-quality.php')], {cwd: root, encoding: 'utf8', maxBuffer: 20*1024*1024}));
const variants = require('./load-answer-variants.cjs');
const helpers = read('resources/views/components/saved-test-js-helpers.blade.php');
const plain = value => JSON.parse(JSON.stringify(value));
function fn(source, name) {
  const start = source.indexOf(`function ${name}(`); assert.notEqual(start, -1, name);
  const tail = source.slice(start); return tail.slice(0, tail.search(/^\}/m)+1);
}
const {wordPolicyHelpers, assertWordSentencePath, assertWordChoiceCount} = require('./finite-word-policy-harness.cjs');
const shared = [...wordPolicyHelpers, 'canonicalTestAnswer', 'composeSentenceChoiceMatches', 'acceptedTestAnswers', 'testAnswerMatches',
  'isPassivePhraseGapQuestion', 'isReviewedComposePhraseSlot', 'normalizeManualGapInput', 'composeManualAnswerWords'];
function context(mode) {
  const c = vm.createContext({EnglishAnswerVariants: variants, COMPOSE_TOKENS_QUESTION_TYPE:'4',
    IS_POLYGLOT_STEP_PREVIEW:false, POLYGLOT_OPTIONS_PER_SLOT:4, shuffle:a=>a.reverse(),cloneState:plain,getTechnicalQuestions:()=>plain(all)});
  const source = read(`resources/views/test-modes/${mode}.blade.php`);
  const options = ['getMarkersCount','sanitizeOptions','buildShuffledOptions','normalizeAnswer','isPolyglotComposeQuestion',
    'limitOptionsForSlot','buildFallbackOptionsBySlot','normalizeOptionsBySlot','getActiveOptions'];
  vm.runInContext(shared.map(n=>fn(helpers,n)).concat(options.map(n=>fn(source,n))).join('\n'),c);
  return c;
}

test('fresh role-aware choices replace old random choices without resetting saved answers, drafts or counters',()=>{
  const c=context('card-easy');vm.runInContext(fn(helpers,'mergeFreshQuestionContentIntoSavedState'),c);
  const fresh=all.find(q=>q.type==='4');assert.ok(fresh);
  const old={...plain(fresh),compose_content_revision:'old',options_by_marker:fresh.answers.map(a=>[a,'wealth','fell','lorry']),
    chosen:[fresh.answers[0]],inputs:[['saved draft']],attempts:2,activeSlot:1,done:false};
  const saved={items:[old],answered:2,correct:2,current:1};const merged=c.mergeFreshQuestionContentIntoSavedState(saved);
  for(const key of ['answered','correct','current'])assert.equal(merged[key],saved[key]);
  for(const key of ['chosen','inputs','attempts','activeSlot','done'])assert.deepEqual(plain(merged.items[0][key]),old[key]);
  assert.deepEqual(plain(merged.items[0].options_by_marker),fresh.options_by_marker);
  assert.equal(merged.items[0].compose_content_revision,fresh.compose_content_revision);
});

test('all540 finite questions: full gaps, punctuation, empty answers and aliases use the actual shared grader',()=>{
  assert.equal(all.length,540); const c=context('card-easy'); let phrases=0,aliases=0,omissions=0;
  for(const raw of all.filter(q=>q.type==='0')) {
    const q=plain(raw),expected=q.answers[0];
    assert.equal(q.passive_phrase_word_inputs,true,q.uuid);
    assert.equal(c.composeManualAnswerWords(q,0).length,expected.split(/\s+/).length,q.uuid+' field count');
    assert.equal(c.testAnswerMatches(q,0,expected),true,q.uuid);
    assert.equal(c.testAnswerMatches(q,0,''),false,q.uuid);
    assert.equal(c.testAnswerMatches(q,0,'zzzz'),false,q.uuid);
    if(expected==='-'){omissions++;assert.equal(c.testAnswerMatches(q,0,'.'),false);}
    else assert.equal(c.testAnswerMatches(q,0,expected.replace(/[.,!?;:]/g,'')+'?!'),true,q.uuid);
    const words=expected.split(/\s+/); if(words.length>1){
      const completeShortAlias=(q.answer_synonyms_by_marker?.a1||[]).some(alias=>variants.normalize(alias)===variants.normalize(words[0]));
      if(!completeShortAlias)phrases++;
      assert.equal(c.testAnswerMatches(q,0,words[0]),completeShortAlias,q.uuid+' incomplete prefix is wrong; an explicit shorter whole alias remains valid');
    }
    for(const alias of q.answer_synonyms_by_marker?.a1||[]){aliases++;assert.equal(c.testAnswerMatches(q,0,alias),true,q.uuid+'/'+alias);}
  }
  assert.ok(phrases>0);assert.ok(aliases>0);assert.equal(omissions,0);
});

for(const mode of ['card-easy','step-easy']) {
  test(`${mode}: all180 compose questions have reviewed four-choice pools without recycling selected correct words`,()=>{
    const c=context(mode);let slots=0;
    for(const raw of all.filter(q=>q.type==='4')) {
      const q=plain(raw);q.chosen=[];q.optionsBySlot=c.normalizeOptionsBySlot(q);const wrongUsed=new Set();
      for(let index=0;index<q.answers.length;index++){
        slots++;q.activeSlot=index;const options=Array.from(c.getActiveOptions(q));
        assertWordChoiceCount(c,q,index,options,variants);assert.ok(options.some(value=>variants.normalize(value)===variants.normalize(q.answers[index])));
        const valid=new Set(c.acceptedTestAnswers(q,index).map(v=>variants.normalize(v)));
        for(const option of options){const key=variants.normalize(option);if(!valid.has(key)){
          wrongUsed.add(key);
          assert.equal(q.answers.some(answer=>variants.normalize(answer)===key),false,q.uuid+' recycles correct word');
        }}
        assert.equal(c.testAnswerMatches(q,index,q.answers[index]),true,q.uuid+'/'+index);q.chosen[index]=q.answers[index];assert.equal(c.syncPresentPerfectPassiveWordPath(q),true);
      }
    }
    assert.ok(slots>700);
  });
  test(`${mode}: every accepted full sentence is reachable without accepting a hybrid or dropping words`,()=>{
  const c=context(mode);let checked=0;
  for(const raw of all.filter(q=>q.type==='4'))for(const sentence of raw.compose_accepted_sentences){
   assertWordSentencePath(c,raw,sentence,variants);checked++;
  }assert.ok(checked>=180);
  });
}
