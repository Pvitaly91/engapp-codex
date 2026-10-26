'use strict';
// Source-only replay of the real native factory and frozen public M94 lesson.
// This is not a browser, DOM, layout or live integration test.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const root = path.resolve(process.argv[2]);
const input = JSON.parse(fs.readFileSync(0, 'utf8'));
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const profiles = {
  '0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186': {
    label: 'root', pins: {
      'resources/views/engram/theory/blocks-v3/practice-set.blade.php': '0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186',
      'tests/Browser/load-answer-variants.cjs': 'b90f5df05b6743ff6bb7ab31e27fad3d968cd95832e5f7a626c304914b7eab49',
      'public/js/english-answer-variants.js': '92f4984a6d6d77fd4774a716e8fef5db79f2df1eab29b54333aed60a47cc2540',
      'public/data/english-contractions.json': '9a5d411c3ad87774f7c3d2e79bcd4ac7aebefad8ae7c1a43efbd327a17ca8baf'
    }
  },
  '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e': {
    label: 'worktree', pins: {
      'resources/views/engram/theory/blocks-v3/practice-set.blade.php': '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e',
      'tests/Browser/load-answer-variants.cjs': '8b3f5690a441e0455d2691de3c59f85fd83e5f8d85429f5b2c5197722b14aa1e',
      'public/js/english-answer-variants.js': '7000925d6de41fdb6671457bd8d1d7766b9c5b05d2c3d5fe0f1195f80666c681',
      'public/data/english-contractions.json': '2815805630fc1987314c9632a055cf3feced856372f73cb29c2a6ade80cb6491'
    }
  }
};
const nativeSha = sha(fs.readFileSync(path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php')));
const profile = profiles[nativeSha];
assert.ok(profile, 'Exact independently captured native factory source profile required.');
const requested = profile.label, pins = profile.pins;
for (const [p, expected] of Object.entries(pins)) assert.equal(sha(fs.readFileSync(path.join(root, p))), expected, p);
assert.equal(input.master_sha256, '39d88cc16eef08bacb58103edff346e094cd39de27997d8b87a875501b465ade');
const native = fs.readFileSync(path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php'), 'utf8');
const scripts = [...native.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(x => x[1]).filter(x => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(x));
assert.equal(scripts.length, 1);
assert.doesNotMatch(scripts[0], /@(?:php|json|js|endphp)\b|\{\{!!?/);
const variants = require(path.join(root, 'tests/Browser/load-answer-variants.cjs'));
let factory, listener, network = 0, focusCalls = 0;
const win = {EnglishAnswerVariants: variants, location: {origin: 'http://gramlyze.loc'}, innerWidth: 1440};
const ctx = vm.createContext({
  window: win, URL,
  document: {
    addEventListener(name, fn) { assert.equal(name, 'alpine:init'); listener = fn; },
    querySelector() { return {focus() {focusCalls++;}, setSelectionRange() {}}; }
  },
  Alpine: {data(name, fn) { assert.equal(name, 'theoryPracticeSet'); factory = fn; }},
  fetch() { network++; throw Error('Network forbidden in offline M94 native factory replay.'); }
});
vm.runInContext(scripts[0], ctx, {timeout: 1000});
listener();
function make(data) {
  const m = factory({...data, i18n: {
    empty: 'Введи відповідь.', correct: 'Правильно.', incorrect: 'Неправильно.',
    answer: 'Правильна відповідь', score: 'Результат: :correct / :total'
  }});
  m.$nextTick = fn => fn();
  m.init();
  assert.equal(Object.hasOwn(m, 'noInputTokenFocus'), requested === 'root');
  if (requested === 'root') assert.equal(m.noInputTokenFocus, false);
  return m;
}
const props = {selects: 'selectAnswers', choices: 'choiceAnswers', inputs: 'inputAnswers'};
let assertions = 0, acceptedKeys = 0, optionCount = 0, emptyCases = 0, wrongCases = 0, fullModeExecutions = 0;
function check(condition, message) { assertions++; assert.ok(condition, message); }
function plain(text, reason) {
  check(typeof text === 'string' && /[\u0400-\u04ff]/u.test(text), reason + ': Ukrainian plain text required');
  check(!/<[^>]+>|\{a\d+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z]+);/iu.test(text), reason + ': technical output absent');
}
function grade(m, group, index, value, expected) {
  m[props[group]][index] = value; m.check(group);
  assertions++; assert.equal(m.isCorrect(group, index), expected, group + '[' + index + '] ' + value);
}
function sequence(m, index, canonical) {
  const bank = [...m.inputTokenBank(index)], words = m.inputTokenWords(canonical), out = [];
  for (let offset = 0; offset < words.length;) {
    const at = bank.findIndex(t => {
      const part = m.inputTokenWords(t.value);
      return part.length && part.every((word, i) => words[offset + i] === word);
    });
    check(at !== -1, 'Every canonical word is available with multiplicity.');
    const token = bank.splice(at, 1)[0]; out.push(token); offset += m.inputTokenWords(token.value).length;
  }
  assertions++; assert.equal(bank.length, 0);
  return out;
}
(async () => {
  assert.equal(input.lessons.length, 1);
  const lesson = input.lessons[0];
  assert.equal(lesson.identity, 'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\RelativeClausesWithWhoseAndWhichTheorySeeder');
  assert.equal(lesson.blocks.length, 8);
  assert.equal(lesson.blocks[7].key, 'm94-practice');
  assert.equal(lesson.blocks[7].type, 'practice-set');
  assert.equal(lesson.blocks[7].level, 'B2');
  const d = lesson.blocks[7].data, before = JSON.stringify(d);
  check(d.selects.length === 2 && d.choices.length === 2 && d.inputs.length === 2, 'Exactly six authored tasks.');
  check(!d.rephrase || d.rephrase.length === 0, 'No hidden extra task group.');
  const ids = Object.keys(props).flatMap(group => d[group].map(task => task.id));
  check(new Set(ids).size === 6, 'Distinct authored IDs.');
  const observations = [], optionObservations = [];
  const independent = make(d);
  for (const group of Object.keys(props)) {
    const m = make(d);
    for (const [index, task] of d[group].entries()) {
      check(typeof task.answer === 'string' && task.accepted.includes(task.answer), task.id + ': declared canonical.');
      for (const value of ['', ' ', '\t\n', '\u00a0', '—']) {
        grade(m, group, index, value, false);
        assertions++; assert.equal(m.feedbackText(group, index), 'Введи відповідь.');
        emptyCases++;
      }
      grade(m, group, index, 'zzzz wrong', false); plain(m.feedbackText(group,index), task.id + ': wrong fallback'); wrongCases++;
      for (const answer of task.accepted) {
        grade(m, group, index, answer, true);
        plain(m.feedbackText(group,index), task.id + ': authored feedback');
        check(m.feedbackText(group,index).includes('—'), task.id + ': canonical/declared alias translation remains in contextual feedback.');
        observations.push({id:task.id, group, index, answer, accepted:true, feedback:m.feedbackText(group,index)});
        acceptedKeys++;
        grade(m, group, index, ' \t' + answer.replace(/ /g,'  ') + '\n', true);
      }
      if (group !== 'inputs') {
        const shown = task.options;
        check(Array.isArray(shown) && shown.length === 3, task.id + ': three actual displayed candidates.');
        check(new Set(shown.map(x=>m.normalize(x))).size === shown.length, task.id + ': no punctuation-only/case-only normalized collision.');
        let correct = 0;
        for (const option of shown) {
          const expected = task.accepted.some(answer=>variants.variants(answer).some(value=>m.normalize(value)===m.normalize(option)));
          grade(m,group,index,option,expected);
          plain(m.feedbackText(group,index), task.id + ': displayed-option feedback');
          optionObservations.push({id:task.id, option, correct:expected, feedback:m.feedbackText(group,index)});
          correct += Number(expected); optionCount++;
        }
        assertions++; assert.equal(correct,1,'Exactly one candidate meets explicit criterion.');
      }
      grade(m,group,index,task.answer,true);
      grade(independent,group,index,task.answer,true);
    }
    assertions++; assert.equal(m.scoreText(group),'Результат: 2 / 2');
    m.check(group); assertions++; assert.equal(m.scoreText(group),'Результат: 2 / 2');
    m.resetGroup(group); check(!m.isChecked(group), 'Reset clears only group checked-state.');
    m.check(group); assertions++; assert.equal(m.scoreText(group),'Результат: 0 / 2');
  }
  for (const [at,group] of Object.keys(props).entries()) {
    independent.resetGroup(group);
    for (const [otherAt,other] of Object.keys(props).entries()) {
      assertions++; assert.equal(independent.scoreText(other),'Результат: ' + (otherAt<=at?0:2) + ' / 2');
    }
  }
  const expectedFull = [
    'The studio whose owner we interviewed will open next month.',
    'We missed the last bus, which delayed our arrival.'
  ];
  const full = [];
  for (const [index,task] of d.inputs.entries()) {
    assertions++; assert.equal(task.answer,expectedFull[index]);
    check(!task.punctuation_sensitive, 'Ordinary normalizer branch is explicit, no punctuation-sensitive opt-in.');
    check(/[\u0400-\u04ff]/u.test(task.hint_uk) && !/[A-Za-z]/u.test(task.hint_uk),'Ukrainian editorial hint only.');
    check(!task.after.includes(task.answer) && !task.hint_uk.includes(task.answer),'No complete English target leaked in visible criterion/hint.');
    const m=make(d), bank=m.inputTokenBank(index), order=sequence(m,index,task.answer);
    check(new Set(bank.map(t=>t.id)).size===bank.length,'Instance IDs preserve token multiplicity.');
    check(bank.every(t=>t.value.trim().split(/\s+/u).length>=1 && t.value.trim().split(/\s+/u).length<=3),'Logical groups contain 1–3 words.');
    check(m.normalize(bank.map(t=>t.value).join(' '))!==m.normalize(task.answer),'Initial order is shuffled.');
    grade(m,'inputs',index,task.answer,true); m.syncInputTokenBank(index);
    check(m.isInputTokenBankEmpty(index),'Manual canonical consumes all matching instances.'); fullModeExecutions++;
    m.inputAnswers[index]='';m.syncInputTokenBank(index);
    check(m.inputTokenBank(index).every(t=>!t.used),'Manual deletion returns all token instances.');
    m.wordSearchEndpoint='/forbidden-in-offline-probe';
    for(const token of order) {
      m.wordSuggestionOpen['inputs-'+index]=true;
      m.appendInputToken('inputs',index,token);
      const text=m.inputAnswers[index];m.appendInputToken('inputs',index,token);
      assertions++;assert.equal(m.inputAnswers[index],text,'Used token cannot append twice.');
      await m.searchWordSuggestions('inputs',index,{target:{value:'word',selectionStart:4}});
      check(!m.isWordSuggestionOpen('inputs',index),'Tokenized inputs suppress word-suggestion branch.');
      check(m.wordSuggestionOpen['inputs-'+index]===false,'Actual native branch closes the stored suggestion flag.');
    }
    assertions++;assert.equal(m.inputAnswers[index],task.answer);
    grade(m,'inputs',index,task.answer,true);fullModeExecutions++;
    m.inputAnswers[index]=order.slice(0,-1).map(t=>t.value).join(' ');m.syncInputTokenBank(index);
    const returned=m.inputTokenBank(index).find(t=>t.id===order.at(-1).id);
    check(!returned.used,'Deleting the last group returns that instance.');
    m.appendInputToken('inputs',index,returned);grade(m,'inputs',index,task.answer,true);
    m.resetGroup('inputs');m.inputAnswers[index]=order[0].value;m.syncInputTokenBank(index);
    for(const token of order.slice(1))m.appendInputToken('inputs',index,token);
    assertions++;assert.equal(m.inputAnswers[index],task.answer);
    grade(m,'inputs',index,task.answer,true);
    m.resetGroup('inputs');
    for(const token of order)m.appendInputToken('inputs',index,token);
    grade(m,'inputs',index,task.answer,true);m.check('inputs');
    assertions++;assert.equal(m.scoreText('inputs'),'Результат: 1 / 2');
    const containsComma=task.answer.includes(',');
    let commaProbe=null;
    if(containsComma){
      const commaRemoved=task.answer.replaceAll(',','');
      assertions++;assert.equal(m.normalize(commaRemoved),m.normalize(task.answer));
      grade(m,'inputs',index,commaRemoved,true);
      commaProbe={original:task.answer,commaRemoved,ordinaryNormalizerCollision:true,nativeLexicalScoreAcceptsCommaRemoved:true,punctuationAcceptanceClaim:false};
    }
    full.push({id:task.id,bank:bank.map(t=>({id:t.id,value:t.value})),canonicalOrder:order.map(t=>t.id),manual:true,tokens:true,mixedEditing:true,deleteReturn:true,clearRebuild:true,containsComma,commaProbe});
  }
  const synthetic={inputs:[{answer:'The artist showed the drawing to the curator.',accepted:['The artist showed the drawing to the curator.'],before:'drawing / The artist / the / to / the / curator. / showed'}]};
  const sm=make(synthetic), sb=sm.inputTokenBank(0), so=sequence(sm,0,synthetic.inputs[0].answer);
  const repeated=sb.filter(t=>t.value==='the');
  assertions++;assert.equal(repeated.length,2);check(repeated[0].id!==repeated[1].id,'Equal-label tokens retain distinct IDs.');
  for(const token of so)sm.appendInputToken('inputs',0,token);
  grade(sm,'inputs',0,synthetic.inputs[0].answer,true);
  sm.inputAnswers[0]='The artist showed the drawing to curator.';sm.syncInputTokenBank(0);
  assertions++;assert.equal(sm.inputTokenBank(0).filter(t=>t.value==='the'&&t.used).length,1);
  assertions++;assert.equal(sm.inputTokenBank(0).filter(t=>t.value==='the'&&!t.used).length,1);
  const aliasTask=d.inputs[1], alias=aliasTask.accepted[1], am=make(d);
  grade(am,'inputs',1,alias,true);check(sequenceAliasImpossible(am,1,alias),'Manual-only alias has no false token feasibility promise.');
  check(JSON.stringify(d)===before,'Factory replay leaves author payload unchanged.');
  for(const[p,expected]of Object.entries(pins))assertions++,assert.equal(sha(fs.readFileSync(path.join(root,p))),expected,p+' unchanged');
  check(network===0,'No network calls.');
  check(acceptedKeys===7&&optionCount===12&&fullModeExecutions===4,'Finite coverage counts.');
  check(focusCalls>0,'Actual default focus branch exercised against stub only.');
  console.log(JSON.stringify({
    kind:'M94-offline-actual-native-factory-replay',
    source_context:requested,master_sha256:input.master_sha256,source_profiles:pins,
    graded_authored_tasks:6,full_authored_inputs:2,declared_keys_including_canonical:acceptedKeys,
    displayed_options:optionCount,empty_cases:emptyCases,wrong_cases:wrongCases,
    offline_full_manual_token_executions:fullModeExecutions,synthetic_multiplicity_fixtures:1,
    counted_assertions:assertions,observations,option_observations:optionObservations,full,
    synthetic_multiplicity:{repeatedLabel:'the',instances:2,distinctIDs:true,deleteReturnsOneInstance:true,notAnAuthoredTask:true},
    manual_only_alias:{answer:alias,nativeAccepted:true,tokenModePromised:false},
    native_feedback_lookup:'trim+lowercase raw input; additional normalized whitespace/punctuation variants may use existing generic feedback fallback',
    focus_calls_are_stubs:true,nominal_width_labels_used:false,browser_acceptance:'not claimed',layout_acceptance:'not claimed',
    application_bootstrap:false,working_env_loaded:false,DB_connections:0,DB_writes:0,HTTP_requests:0,network_calls:network,file_writes:0
  }));
})().catch(error=>{console.error(error);process.exitCode=1;});
function sequenceAliasImpossible(m,index,answer){
  const available=m.inputTokenBank(index).flatMap(t=>m.inputTokenWords(t.value));
  const wanted=m.inputTokenWords(answer);
  return wanted.some(word=>!available.includes(word));
}
