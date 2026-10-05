'use strict';
// Pure VM/source tests; no Laravel boot, .env, HTTP, browser or database.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../..');
const view = fs.readFileSync(path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php'), 'utf8');
const script = view.slice(view.indexOf('<script>') + 8, view.lastIndexOf('</script>'));
const content = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m36-m20-modals-subjunctive.v1.json'), 'utf8'));
const before = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m36-m20-modals-subjunctive-before.json'), 'utf8'));
let factory, network = 0;
vm.runInNewContext(script, {
    document: {addEventListener: (_, fn) => fn(), querySelector: () => null},
    Alpine: {data: (_, fn) => {factory = fn;}},
    window: {EnglishAnswerVariants: require('./load-answer-variants.cjs')},
    fetch: () => {network += 1; throw Error('Unexpected network request');},
    setTimeout, clearTimeout,
});
const groups = ['selects', 'choices', 'inputs'];
const mappings = [
    {selects: [1, 4], choices: [3, 5], inputs: [2, 6]},
    {selects: [1, 5], choices: [3, 4], inputs: [2, 6]},
    {selects: [1, 4], choices: [2, 5], inputs: [3, 6]},
];
const banks = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotModalPerfectAndDeductionC1LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotSubjunctiveAndFormalStructuresC1LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotSubtleModalMeaningsC2LessonSeeder',
];
function practice(i) {return JSON.parse(content.targets[i].after.page.blocks.find(b => b.type === 'practice-set').body);}
function state(data) {
    const s = factory({...data, i18n: {score: ':correct / :total', correct: 'Correct', incorrect: 'Incorrect', answer: 'Answer', empty: 'Empty'}});
    s.$nextTick = fn => fn(); s.init(); return s;
}
function sourceCase(data, number) {
    const matches = groups.flatMap(group => data[group].map((item, index) => ({group, item, index})))
        .filter(x => x.item.source_index === number);
    assert.equal(matches.length, 1, 'One owner for original case ' + number);
    return matches[0];
}
function exactOwnership(data, i) {
    const html = before.targets[i].before.page.blocks[1].body;
    const lists = {};
    for (const [name, attribute] of [['prompts', 'data-self-checks'], ['answers', 'data-self-check-answers']]) {
        const m = html.match(new RegExp('<ol ' + attribute + '(?:="true")?>([\\s\\S]*?)<\\/ol>')); assert.ok(m);
        lists[name] = [...m[1].matchAll(/<li>([\s\S]*?)<\/li>/g)].map(x => x[1]); assert.equal(lists[name].length, 6);
    }
    assert.deepEqual(data.author_self_check.prompts, lists.prompts);
    assert.deepEqual(data.author_self_check.answers, lists.answers);
    const indices = [];
    for (const group of groups) {
        assert.deepEqual(data[group].map(x => x.source_index), mappings[i][group]);
        for (const item of data[group]) {
            assert.ok(item.answer); assert.equal(item.context, lists.prompts[item.source_index - 1]);
            assert.equal(item.author_explanation, lists.answers[item.source_index - 1]); indices.push(item.source_index);
        }
    }
    assert.deepEqual(indices.sort((a, b) => a - b), [1, 2, 3, 4, 5, 6]);
}
for (const [i, target] of content.targets.entries()) {
    const data = practice(i);
    for (const group of groups) {
        test(target.slug + ': finite ' + group + ' mapping preserves two original cases', () => {
            assert.equal(data[group].length, 2); assert.deepEqual(data[group].map(x => x.source_index), mappings[i][group]);
        });
        test(target.slug + ': ' + group + ' correct/aliases/wrong/score/reset', () => {
            const s = state(data);
            data[group].forEach((item, index) => {
                for (const answer of item.accepted || [item.answer]) {
                    s.answerSource(group)[index] = answer; assert.equal(s.isCorrect(group, index), true);
                    if (group === 'inputs') {s.inputAnswers[index] = answer.replace(/[.!?]+$/, ''); assert.equal(s.isCorrect(group, index), true);}
                }
                s.answerSource(group)[index] = item.answer;
            });
            s.check(group); assert.equal(s.scoreText(group), '2 / 2'); assert.equal(s.isChecked(group), true);
            assert.match(s.fieldClass(group, 0), /emerald/);
            s.answerSource(group)[0] = 'wrong answer'; assert.equal(s.isCorrect(group, 0), false);
            assert.equal(s.scoreText(group), '1 / 2'); assert.match(s.fieldClass(group, 0), /rose/);
            s.resetGroup(group); assert.equal(s.isChecked(group), false);
            assert.equal(Object.keys(s.answerSource(group)).length, 0); assert.equal(s.fieldClass(group, 0), '');
        });
        if (group !== 'inputs') test(target.slug + ': ' + group + ' actual distractors rejected unless approved', () => {
            const s = state(data);
            data[group].forEach((item, index) => {
                const options = item.options || data.options || (group === 'choices' ? data.choice_options : []);
                assert.ok(options.length >= 2);
                for (const option of options) {
                    s.answerSource(group)[index] = option;
                    assert.equal(s.isCorrect(group, index), (item.accepted || [item.answer]).includes(option));
                }
            });
        });
    }
    test(target.slug + ': grouped tokens/manual/deletion reuse without suggestions or fetch', async () => {
        const s = state(data);
        for (const [index, item] of data.inputs.entries()) {
            const bank = s.inputTokenBank(index); assert.ok(bank.length > 1);
            assert.ok(bank.every(t => t.value.trim().split(/\s+/).length <= 3));
            s.appendInputToken('inputs', index, bank[0]); assert.equal(s.inputTokenBank(index)[0].used, true);
            const once = s.inputAnswers[index]; s.appendInputToken('inputs', index, bank[0]); assert.equal(s.inputAnswers[index], once);
            s.inputAnswers[index] = ''; s.syncInputTokenBank(index); assert.equal(s.inputTokenBank(index)[0].used, false);
            s.inputAnswers[index] = item.answer; s.syncInputTokenBank(index); assert.ok(s.inputTokenBank(index).every(t => t.used));
            s.wordSuggestionOpen['inputs-' + index] = true; s.wordSuggestionResults['inputs-' + index] = [{word: 'test'}];
            assert.equal(s.isWordSuggestionOpen('inputs', index), false);
            await s.searchWordSuggestions('inputs', index, {target: {value: 'test', selectionStart: 4}});
            assert.equal(s.isWordSuggestionOpen('inputs', index), false);
        }
        s.resetGroup('inputs'); assert.ok(Object.values(s.inputTokenBanks).every(b => b.every(t => !t.used))); assert.equal(network, 0);
    });
    test(target.slug + ': canonical answer complete by token clicks only', () => {
        const s = state(data);
        data.inputs.forEach((item, index) => {
            let remaining = item.answer; const used = new Set();
            while (remaining !== '') {
                const token = s.inputTokenBank(index).filter(t => !used.has(t.id) && remaining.startsWith(t.value))
                    .sort((a, b) => b.value.length - a.value.length)[0]; assert.ok(token, 'Unconstructible case ' + item.source_index + ': ' + remaining);
                s.appendInputToken('inputs', index, token); used.add(token.id); remaining = remaining.slice(token.value.length).trimStart();
            }
            assert.equal(s.inputAnswers[index], item.answer); assert.equal(s.isCorrect('inputs', index), true); assert.equal(s.isInputTokenBankEmpty(index), true);
        });
    });
    test(target.slug + ': literal six prompts/keys/ownership and own primary widget', () => {
        exactOwnership(data, i); assert.deepEqual(data.linked_practice.question_types, ['4']);
        assert.deepEqual(data.linked_practice.seeder_classes, [banks[i]]); assert.doesNotMatch(banks[i], /AllLevels/);
    });
}
const {semanticFixtures} = require('../../tools/diagnostics/m36-semantic-fixtures.cjs');
test('M36 explicit groups preserve internal colon/apostrophe; legacy slash delimiter remains unchanged', () => {
    const data = practice(2); const s = state(data);
    assert.ok(s.inputTokenBank(0).some(token => token.value.includes('printing:')));
    assert.ok(s.inputTokenBank(1).some(token => token.value.includes('today’s')));
    const legacy = state({inputs: [{before: 'I / have worked / today', answer: 'I have worked today'}]});
    assert.deepEqual(Array.from(legacy.inputTokenBank(0), token => token.value), ['I', 'have worked', 'today']);
    assert.equal(network, 0);
});
test('All multipart Modal Perfect cases keep two deductions and three repairs', () => {
    const data = practice(0);
    assert.match(sourceCase(data, 2).item.answer, /Nora must have unlocked the door\. Eli can’t have been at the studio at nine\./);
    assert.ok(sourceCase(data, 2).item.accepted.some(a => a.includes('cannot have been')));
    for (const text of ['She might have left the note.', 'He must have gone home.', 'They can’t have seen the rehearsal.'])
        assert.ok(sourceCase(data, 5).item.prompt.includes(text));
    assert.deepEqual(sourceCase(data, 6).item.accepted.map(a => /The rehearsal (\w+) have/.exec(a)[1]), ['may', 'might', 'could']);
});
test('All Subjunctive subparts and explicit bare model remain answerable', () => {
    const data = practice(1), requirements = sourceCase(data, 3).item.prompt;
    for (const text of ['assistant not delete the comments', 'reviewers be informed by the assistant by noon']) assert.ok(requirements.includes(text));
    assert.match(sourceCase(data, 4).item.prompt, /On Monday, Leo recommended that Emma check the heading before publication\./);
    assert.equal(sourceCase(data, 2).item.answer, 'The coordinator recommends that each reviewer read the brief by Friday.');
    assert.ok(sourceCase(data, 6).item.accepted.some(a => a.includes('wouldn’t')));
});
test('All Subtle advice/performance subparts and requested model strengths survive', () => {
    const data = practice(2), advice = sourceCase(data, 3).item.answer;
    for (const text of ['You might want to check', 'It may be worth checking', 'You would be wise to check', 'the previous copy listed one name twice']) assert.ok(advice.includes(text));
    const performance = sourceCase(data, 4).item.context;
    for (const text of ['so I didn’t', 'but I did', 'I didn’t need to print the map.']) assert.ok(performance.includes(text));
    assert.ok(sourceCase(data, 6).item.accepted.some(a => a.includes('need not have printed')));
    assert.match(sourceCase(data, 6).item.answer, /Missing clips could well explain today’s delay\. It may be worth checking the cupboard\./);
});
test('Terminal punctuation is optional but internal sentence/colon boundaries survive manual entry', () => {
    const data = practice(2), s = state(data), answer = data.inputs[0].answer;
    s.inputAnswers[0] = answer.replace(/[.!?]+$/, ''); assert.equal(s.isCorrect('inputs', 0), true);
    s.inputAnswers[0] = answer.replace('printing:', 'printing'); assert.equal(s.isCorrect('inputs', 0), false);
    s.inputAnswers[0] = answer.replace('printing. It may', 'printing It may'); assert.equal(s.isCorrect('inputs', 0), false);
    for (const i of [0, 1, 2]) {
        const item = practice(i).inputs[1], stateWithApostrophes = state(practice(i));
        stateWithApostrophes.inputAnswers[1] = item.answer.replaceAll('’', "'");
        assert.equal(stateWithApostrophes.isCorrect('inputs', 1), true);
    }
});
for (const [i, target] of content.targets.entries()) {
    const data = practice(i);
    for (const [index, invalid, from] of semanticFixtures(data)) {
        test(target.slug + ': actual semantic boundary ' + from, () => {
            assert.notEqual(invalid, data.inputs[index].answer); const s = state(data);
            s.inputAnswers[index] = invalid; assert.equal(s.isCorrect('inputs', index), false);
        });
    }
    test(target.slug + ': ownership rejects lost key and neighbouring prompt', () => {
        const lost = structuredClone(data); lost.author_self_check.answers.pop(); assert.throws(() => exactOwnership(lost, i));
        const moved = structuredClone(data); moved.inputs[0].context = moved.inputs[1].context; assert.throws(() => exactOwnership(moved, i));
    });
}
