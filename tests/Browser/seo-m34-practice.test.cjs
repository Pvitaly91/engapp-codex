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
const content = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m34-m18-argumentation-cohesion.v1.json'), 'utf8'));
const before = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m34-m18-argumentation-cohesion-before.json'), 'utf8'));
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
    {selects: [3, 4], choices: [1, 2], inputs: [5, 6]},
    {selects: [1, 2], choices: [4, 6], inputs: [3, 5]},
    {selects: [1, 5], choices: [3, 4], inputs: [2, 6]},
];
const banks = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotArgumentationAndAcademicToneC2LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotDiscourseMarkersAndCohesionC2LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotParaphraseAndReformulationC2LessonSeeder',
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
        const m = html.match(new RegExp('<ol ' + attribute + '>([\\s\\S]*?)<\\/ol>')); assert.ok(m);
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
    test(target.slug + ': literal six prompts/keys/ownership and own C2 widget', () => {
        exactOwnership(data, i); assert.deepEqual(data.linked_practice.question_types, ['4']);
        assert.deepEqual(data.linked_practice.seeder_classes, [banks[i]]); assert.doesNotMatch(banks[i], /AllLevels/);
    });
}
test('Literal ownership rejects prompt/key/translation loss, duplicates and wrong context', () => {
    for (let i = 0; i < 3; i++) {
        const data = practice(i); exactOwnership(data, i);
        const lostKey = structuredClone(data); lostKey.author_self_check.answers.pop(); assert.throws(() => exactOwnership(lostKey, i));
        const lostAnswer = structuredClone(data); delete lostAnswer.inputs[0].answer; assert.throws(() => exactOwnership(lostAnswer, i));
        const duplicate = structuredClone(data); duplicate.inputs[0].source_index = duplicate.choices[0].source_index; assert.throws(() => exactOwnership(duplicate, i));
        const neighbour = structuredClone(data); neighbour.inputs[0].context = neighbour.inputs[1].context; assert.throws(() => exactOwnership(neighbour, i));
        const translation = structuredClone(data); translation.author_self_check.answers[0] = translation.author_self_check.answers[0].replace(/[А-Яа-яІіЇїЄєҐґ]+/u, '');
        assert.notEqual(translation.author_self_check.answers[0], data.author_self_check.answers[0]); assert.throws(() => exactOwnership(translation, i));
    }
});
// Mutations of actual answers, not unrelated dummy responses. Never teaching text.
const mutations = [
    ['A1 roles swapped', 0, 1, () => 'b'],
    ['A2 fits screen taken as sufficient legibility', 0, 2, () => 'b'],
    ['A3 desktop scope removed', 0, 3, a => a.replace('On the desktop screen described here, ', '')],
    ['A3 every device scope invented', 0, 3, a => a.replace('the desktop screen described here', 'every device')],
    ['A3 simultaneous comparison becomes faster booking', 0, 3, a => a.replace('two club timetables to be compared without switching views', 'everyone to book faster')],
    ['A4 no-phone-test changed to invented successful evidence', 0, 4, a => a.replace('phone legibility has not been checked', 'phone legibility has been checked successfully')],
    ['A4 restricted recommendation changed to all devices', 0, 4, a => a.replace('the desktop comparison task for now', 'all devices')],
    ['A5 sequence becomes causal connector', 0, 5, a => a.replace(', and attendance', '; therefore, attendance')],
    ['A5 limitation negation removed', 0, 5, a => a.replace('do not establish', 'establish')],
    ['A5 final inference limitation lost', 0, 5, a => a.split('. These facts')[0] + '.'],
    ['A5 Monday and Tuesday swapped', 0, 5, a => a.replace('Monday', 'Wednesday')],
    ['A5 internal comma splice', 0, 5, a => a.replace('Tuesday. These', 'Tuesday, these')],
    ['A6 unmeasured speed becomes proven', 0, 6, a => a.replace('does not establish', 'establishes')],
    ['A6 invented search-time measurement', 0, 6, a => a.replace('search times were not measured', 'search times were measured')],
    ['A6 index incorrectly assigned to Guide A', 0, 6, a => a.replace('Guide A has no index', 'Guide A has an index')],
    ['A6 actual every definition coverage lost', 0, 6, a => a.replace('every definition', 'some definitions')],
    ['A6 conclusion-only answer loses evidence and limitation', 0, 6, a => a.split('.')[0] + '.'],
    ['D1 concession becomes opposite case', 1, 1, a => a.replace('Nonetheless', 'Conversely')],
    ['D1 concession becomes unsupported consequence', 1, 1, a => a.replace('Nonetheless', 'Consequently')],
    ['D2 addition becomes consequence', 1, 2, a => a.replace('Moreover', 'Consequently')],
    ['D2 accordingly used as generic addition', 1, 2, a => a.replace('Moreover', 'Accordingly')],
    ['D3 comma splice', 1, 3, a => a.replace('; however', ', however')],
    ['D3 missing independent-clause boundary', 1, 3, a => a.replace('; however,', 'however')],
    ['D3 wording changed despite instruction', 1, 3, a => a.replace('every issue', 'some issues')],
    ['D3 known year changed', 1, 3, a => a.replace('2020', '2021')],
    ['D4 ambiguous antecedent retained', 1, 4, () => 'b'],
    ['D5 question turned into invented legibility result', 1, 5, a => a.replace('how clearly would the three routes remain distinguishable', 'the three routes remained clearly distinguishable')],
    ['D5 prior colour topic lost', 1, 5, a => a.replace('colour-based distinction', 'unrelated experiment')],
    ['D5 three routes changed to all routes', 1, 5, a => a.replace('the three routes', 'all routes')],
    ['D6 needless causal transition retained', 1, 6, () => 'c'],
    ['P1 specification misclassified as equivalent paraphrase', 2, 1, a => a.replace('Це конкретизація', 'Це точне перефразування')],
    ['P2 possibility replaced with certainty', 2, 2, a => a.replace('it is possible for two volunteers to open', 'two volunteers will open')],
    ['P2 possibility replaced with permission', 2, 2, a => a.replace('it is possible for two volunteers to open', 'two volunteers are permitted to open')],
    ['P2 two volunteers broadened to all', 2, 2, a => a.replace('two volunteers', 'all volunteers')],
    ['P2 deadline changed to exact noon', 2, 2, a => a.replace('by noon', 'at noon')],
    ['P2 condition dropped', 2, 2, a => a.replace(' if the keys arrive by noon', '')],
    ['P2 source strengthened', 2, 2, a => a.replace('note says', 'note proves')],
    ['P2 unnamed-person limit contradicted', 2, 2, a => a.replace('does not identify', 'identifies')],
    ['P2 Monday changed', 2, 2, a => a.replace('Monday', 'Tuesday')],
    ['P3 after becomes because', 2, 3, () => 'c'],
    ['P4 not-all/no and at-least/exactly conflated', 2, 4, () => 'b'],
    ['P5 only restriction dropped', 2, 5, a => a.replace('Only ', '')],
    ['P5 three known conditions changed', 2, 5, a => a.replace('all three', 'at least one')],
    ['P5 eligibility changed to guaranteed registration', 2, 5, a => a.replace('can register', 'will be registered')],
    ['P6 successful tablet outcome invented', 2, 6, a => a.replace('tested the audio guide', 'successfully tested the audio guide')],
    ['P6 no phone test changed to phone test', 2, 6, a => a.replace('did not test it on a phone', 'tested it on a phone')],
    ['P6 one tablet scope broadened', 2, 6, a => a.replace('a single tablet', 'several devices')],
    ['P6 phone result claimed known', 2, 6, a => a.replace('provides no phone-test result', 'provides a successful phone-test result')],
    ['P6 own inference limitation removed', 2, 6, a => a.replace('I cannot use it', 'I can use it')],
    ['P6 institution actor lost', 2, 6, a => a.replace('the museum ', '')],
    ['P6 exact report only loses requested inference limit', 2, 6, a => a.split('. The note')[0] + '.'],
];
for (const [label, i, number, mutate] of mutations) test('Reject ' + label, () => {
    const data = practice(i), {group, item, index} = sourceCase(data, number), s = state(data);
    const answer = mutate(item.answer); assert.notEqual(answer, item.answer, 'Real changed answer');
    s.answerSource(group)[index] = item.answer; assert.equal(s.isCorrect(group, index), true, 'Correct control');
    s.answerSource(group)[index] = answer; assert.equal(s.isCorrect(group, index), false);
});
test('All explicitly permitted discourse alternatives are covered; internal punctuation remains significant', () => {
    const data = practice(1), s = state(data), concession = sourceCase(data, 1), addition = sourceCase(data, 2), editing = sourceCase(data, 3);
    assert.equal(concession.item.accepted.length, 2); assert.ok(concession.item.accepted.some(a => a.includes('Nevertheless')));
    assert.ok(addition.item.accepted.some(a => a.includes('Furthermore')));
    assert.ok(addition.item.accepted.some(a => a.includes('In addition')));
    assert.ok(addition.item.accepted.includes('The handbook includes a glossary. It provides an index of names.'));
    assert.ok(addition.item.accepted.includes('The handbook includes a glossary and provides an index of names.'));
    assert.equal(editing.item.punctuation_sensitive, true); assert.equal(editing.item.accepted.length, 2);
    assert.ok(editing.item.accepted.some(a => a.includes('; however,')));
    assert.ok(editing.item.accepted.some(a => a.includes('. However,')));
    for (const alias of editing.item.accepted) {s.answerSource(editing.group)[editing.index] = alias; assert.equal(s.isCorrect(editing.group, editing.index), true);}
    s.answerSource(editing.group)[editing.index] = editing.item.answer.replace(';', ','); assert.equal(s.isCorrect(editing.group, editing.index), false);
});
test('Open author cases have finite semantic equivalents, full criteria and no invented evidence', () => {
    for (const [i, number] of [[0, 6], [1, 5], [2, 2], [2, 6]]) {
        const data = practice(i), {item, group, index} = sourceCase(data, number), s = state(data);
        assert.ok(item.accepted.length >= 2); assert.ok(item.author_explanation); exactOwnership(data, i);
        for (const alias of item.accepted) {s.answerSource(group)[index] = alias; assert.equal(s.isCorrect(group, index), true);}
    }
    assert.equal(network, 0);
});
