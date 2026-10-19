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
const content = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m37-m21-grammar-structures.v1.json'), 'utf8'));
const before = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m37-m21-grammar-structures-before.json'), 'utf8'));
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
    {selects: [1, 2], choices: [3, 4], inputs: [5, 6]},
    {selects: [1, 2], choices: [3, 4], inputs: [5, 6]},
    {selects: [1, 4], choices: [2, 5], inputs: [3, 6]},
];
const banks = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedGerundInfinitivePatternsB2LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotComplexRelativeClausesC1LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotInversionAfterAdverbialsC1LessonSeeder',
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
const {semanticFixtures} = require('../../tools/diagnostics/m37-semantic-fixtures.cjs');
test('M37 explicit groups preserve internal comma/sentence punctuation; legacy slash banks stay unchanged', () => {
    const data = practice(1), s = state(data);
    assert.ok(s.inputTokenBank(1).some(token => token.value.includes('Iva,')));
    const legacy = state({inputs: [{before: 'I / have worked / today', answer: 'I have worked today'}]});
    assert.deepEqual(Array.from(legacy.inputTokenBank(0), token => token.value), ['I', 'have worked', 'today']);
    assert.equal(network, 0);
});
test('Gerund multipart cases retain exact forms, three remember meanings and stop viewpoint', () => {
    const data = practice(0);
    for (const text of ['storing', 'to move', 'не доводить']) assert.ok(sourceCase(data, 1).item.answer.includes(text));
    for (const text of ['meeting', 'meet her on Friday', 'прийменник', 'маркер інфінітива']) assert.ok(sourceCase(data, 2).item.answer.includes(text));
    for (const text of ['I remember packing the vase yesterday.', 'Remember to label the box before sending it.', 'I remembered to return the trolley yesterday.']) assert.ok(sourceCase(data, 3).item.prompt.includes(text));
    for (const text of ['The students stopped whispering.', 'The students stopped to read the sign.']) assert.ok(sourceCase(data, 4).item.prompt.includes(text));
    assert.equal(sourceCase(data, 5).item.answer, 'Mila tried to lift the heavy lid. Mila tried opening the side vent to cool the room.');
    assert.ok(sourceCase(data, 5).item.accepted.includes('Mila tried to lift the heavy lid. To cool the room, Mila tried opening the side vent.'));
    assert.ok(sourceCase(data, 6).item.accepted.length > 1);
    for (const answer of sourceCase(data, 6).item.accepted) {
        for (const phrase of ['decided to fix the shelf', 'tried to loosen the screw', 'look forward to seeing our helper']) assert.ok(answer.includes(phrase));
        assert.doesNotMatch(answer, /succeeded|failed|fixed the shelf|loosened the screw/);
    }
});
test('Relative subparts preserve prepositions, six/two photos, omission and nonambiguous folder alternatives', () => {
    const data = practice(1);
    assert.ok(sourceCase(data, 1).item.answer.includes('We rented a studio whose windows face the river.'));
    for (const text of ['to whom we spoke', 'on which the portrait rests']) assert.ok(sourceCase(data, 2).item.answer.includes(text));
    assert.ok(sourceCase(data, 3).item.prompt.includes('The photographs which have white frames were moved.'));
    for (const text of ['У а)', 'У б)', 'У в)', 'The restorer who we believe can repair the frame is away.']) assert.ok(sourceCase(data, 4).item.prompt.includes(text));
    assert.ok(sourceCase(data, 5).item.accepted.includes('The folder was damaged. They placed it beside the lamp.'));
    for (const answer of sourceCase(data, 5).item.accepted) assert.doesNotMatch(answer, /lamp was damaged|damaged it|while placing|during/);
    assert.equal(sourceCase(data, 6).item.answer, 'The workshop whose three presses need servicing has hired Iva, who maintains them.');
});
test('Inversion full cases keep aspect/voice/pairings, both only subparts and fact/prohibition', () => {
    const data = practice(2);
    assert.ok(sourceCase(data, 1).item.answer.includes('Rarely have the samples been stored outside the cold room.'));
    assert.ok(sourceCase(data, 2).item.prompt.includes('Hardly had the dispatcher finished checking the list when the alarm sounded.'));
    assert.equal(sourceCase(data, 3).item.answer, 'Only after the editor had confirmed consent was the material released.');
    for (const text of ['Only the archivist could open the vault.', 'Only after the check was complete could the vault be opened.']) assert.ok(sourceCase(data, 4).item.answer.includes(text));
    for (const text of ['Under no circumstances may visitors remove the seals.', 'At no time during the trial was the access code shared.']) assert.ok(sourceCase(data, 5).item.prompt.includes(text));
    assert.equal(sourceCase(data, 6).item.answer, 'No sooner had the display been installed than the visitors arrived. Not only the architect but also the caretaker welcomed them. The visitors rarely speak loudly in this hall.');
});
test('Only terminal punctuation is optional; relative comma and internal sentence boundaries survive manual entry', () => {
    const data = practice(1), s = state(data), answer = data.inputs[1].answer;
    s.inputAnswers[1] = answer.replace(/[.!?]+$/, ''); assert.equal(s.isCorrect('inputs', 1), true);
    s.inputAnswers[1] = answer.replace('Iva, who', 'Iva who'); assert.equal(s.isCorrect('inputs', 1), false);
    const gerund = practice(0), g = state(gerund);
    g.inputAnswers[0] = gerund.inputs[0].answer.replace('lid. Mila', 'lid Mila'); assert.equal(g.isCorrect('inputs', 0), false);
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
