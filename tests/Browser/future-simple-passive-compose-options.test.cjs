const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {execFileSync} = require('node:child_process');
const root = path.join(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const variants = require('./load-answer-variants.cjs');
const php = process.env.PHP_BINARY || (fs.existsSync('C:/Program Files/xampp/php/php.exe') ? 'C:/Program Files/xampp/php/php.exe' : 'php');
const questions = JSON.parse(execFileSync(php, [path.join(root, 'tests/Fixtures/future-simple-passive-compose-options.php')], {cwd: root, encoding: 'utf8'}));
const plain = value => JSON.parse(JSON.stringify(value));
const helpers = read('resources/views/components/saved-test-js-helpers.blade.php');
function fn(source, name) {
  const start = source.indexOf(`function ${name}(`);
  assert.notEqual(start, -1, `Missing actual function ${name}`);
  const tail = source.slice(start);
  return tail.slice(0, tail.search(/^\}/m) + 1);
}
const optionFunctions = ['getMarkersCount', 'sanitizeOptions', 'buildShuffledOptions', 'normalizeAnswer', 'isPolyglotComposeQuestion',
  'limitOptionsForSlot', 'buildFallbackOptionsBySlot', 'normalizeOptionsBySlot', 'getActiveOptions'];
function context(mode, fresh = questions) {
  const source = read(`resources/views/test-modes/${mode}.blade.php`);
  const ctx = vm.createContext({EnglishAnswerVariants: variants,
    COMPOSE_TOKENS_QUESTION_TYPE: '4', IS_POLYGLOT_STEP_PREVIEW: false, POLYGLOT_OPTIONS_PER_SLOT: 4,
    shuffle: array => array.reverse(), cloneState: plain, getTechnicalQuestions: () => plain(fresh)});
  vm.runInContext(fn(helpers, 'canonicalTestAnswer') + '\n' + optionFunctions.map(name => fn(source, name)).join('\n'), ctx);
  return ctx;
}
for (const mode of ['card-easy', 'step-easy']) {
  test(`${mode}: actual normalizer uses varied authored pools for all72 Future compose questions`, () => {
    const ctx = context(mode);
    assert.equal(questions.length, 72);
    let slots = 0;
    for (const source of questions) {
      assert.match(source.fsp_compose_options_revision, /^[a-f0-9]{64}$/);
      const q = variants.prepareQuestion(plain(source));
      q.optionsBySlot = ctx.normalizeOptionsBySlot(q);
      q.chosen = Array(q.answers.length).fill(null);
      const used = new Set();
      for (let index = 0; index < q.answers.length; ++index) {
        ++slots; q.activeSlot = index;
        const active = Array.from(ctx.getActiveOptions(q));
        const valid = new Set((q.accepted_answers_by_marker[q.markers[index]] || []).map(value => variants.normalize(value)));
        assert.equal(active.length, 4, `${q.uuid}/${index} must display four choices`);
        assert.ok(active.includes(q.answers[index]), `${q.uuid}/${index} lost its correct answer`);
        for (const option of active) {
          const key = variants.normalize(option);
          if (!valid.has(key)) assert.equal(used.has(key), false, `${q.uuid}/${index} re-offers a previously selected word as wrong`);
        }
        q.chosen[index] = q.answers[index]; used.add(variants.normalize(q.answers[index]));
      }
      assert.deepEqual(plain(q.answers), source.answers);
      assert.deepEqual(plain(q.accepted_answers_by_marker), source.accepted_answers_by_marker);
      assert.deepEqual(plain(q.compose_accepted_sentences), source.compose_accepted_sentences);
    }
    assert.ok(slots > 700);
  });
  test(`${mode}: resumed Any/departure question refreshes choices without deleting answer state`, () => {
    const source = questions.find(q => q.uuid === 'pv-fut-simple-poly-c2-02');
    const ctx = context(mode, [source]);
    vm.runInContext(fn(helpers, 'mergeFreshQuestionContentIntoSavedState'), ctx);
    const old = {...plain(source), fsp_compose_options_revision: '0'.repeat(64),
      options_by_marker: source.answers.map(answer => [answer, 'Any', 'departure']),
      chosen: ['Any', 'departure'], inputs: [['partial']], attempts: 2, activeSlot: 2, done: false};
    const merged = ctx.mergeFreshQuestionContentIntoSavedState({items: [old], answered: 2, current: 4});
    assert.equal(merged.answered, 2); assert.equal(merged.current, 4);
    for (const key of ['chosen', 'inputs', 'attempts', 'activeSlot', 'done']) assert.deepEqual(plain(merged.items[0][key]), old[key]);
    assert.deepEqual(plain(merged.items[0].options_by_marker), source.options_by_marker);
    assert.equal(merged.items[0].compose_content_revision, source.compose_content_revision);
    const q = merged.items[0]; q.optionsBySlot = ctx.normalizeOptionsBySlot(q);
    for (let index = 2; index < q.answers.length; ++index) {
      q.activeSlot = index;
      const options = Array.from(ctx.getActiveOptions(q));
      assert.equal(options.some(value => /^(?:Any|departure)[.,!?;:]*$/i.test(value)), false, `${mode}/${index} recycles the old first words`);
    }
  });
}
