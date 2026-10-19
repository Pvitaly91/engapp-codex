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
const content = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m38-m22-articles-collocations.v1.json'), 'utf8'));
const before = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m38-m22-articles-collocations-before.json'), 'utf8'));
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
    {selects: [1, 2], choices: [3, 4], inputs: [5, 6]},
];
const banks = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedArticleAndQuantifierNuanceC1LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotPrecisionWithArticlesAndDeterminersC2LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedCollocationAndLexicalChoiceC2LessonSeeder',
];
function practice(i) {return JSON.parse(content.targets[i].after.page.blocks.find(b => b.type === 'practice-set').body);}
function state(data) {
    const s = factory({...data, i18n: {score: ':correct / :total', correct: 'Correct', incorrect: 'Incorrect', answer: 'Answer', empty: 'Empty'}});
    s.$nextTick = fn => fn(); s.init(); return s;
}
function completeSubparts(s, index, item) {
    for (const [part, check] of (item.m38_semantic_checks || []).entries()) s.setM38SemanticAnswer(index, part, check.answer);
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
                if (group === 'inputs') completeSubparts(s, index, item);
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
            completeSubparts(s, index, item);
            assert.equal(s.inputAnswers[index], item.answer); assert.equal(s.isCorrect('inputs', index), true); assert.equal(s.isInputTokenBankEmpty(index), true);
        });
    });
    test(target.slug + ': literal six prompts/keys/ownership and own primary widget', () => {
        exactOwnership(data, i); assert.deepEqual(data.linked_practice.question_types, ['4']);
        assert.deepEqual(data.linked_practice.seeder_classes, [banks[i]]); assert.doesNotMatch(banks[i], /AllLevels/);
    });
}
const {semanticFixtures} = require('../../tools/diagnostics/m38-semantic-fixtures.cjs');
test('M38 explicit groups preserve sentence punctuation; legacy slash banks stay unchanged', () => {
    const data = practice(2), s = state(data);
    assert.ok(s.inputTokenBank(1).some(token => token.value.includes('purchase,')));
    const legacy = state({inputs: [{before: 'I / have worked / today', answer: 'I have worked today'}]});
    assert.deepEqual(Array.from(legacy.inputTokenBank(0), token => token.value), ['I', 'have worked', 'today']);
    assert.equal(network, 0);
});
test('Articles cases keep countability, positive amount without sufficiency, context and number agreement', () => {
    const data = practice(0);
    for (const text of ['three pieces of information', 'some useful research', 'three items of information']) assert.ok(sourceCase(data, 1).item.answer.includes(text));
    for (const text of ['Суперечності немає', 'не гарантує достатність', 'ненульову кількість']) assert.ok(sourceCase(data, 2).item.answer.includes(text));
    for (const text of ['a pencil', 'the logbook', 'information about yesterday’s delivery']) assert.ok(sourceCase(data, 3).item.prompt.includes(text));
    for (const text of ['labels are missing', 'labels is not known', 'точного числа']) assert.ok(sourceCase(data, 4).item.prompt.includes(text));
    assert.ok(sourceCase(data, 5).item.accepted.includes('For this explanation, there is not much evidence.'));
    assert.ok(sourceCase(data, 6).item.accepted.includes('We have a little useful information, but it is not enough for a complete catalogue.'));
});
test('Determiners retain reference boundary, three some/any functions, five formal forms, two negation subparts', () => {
    const data = practice(1);
    for (const text of ['принесена таця', 'чотири чашки з шести', 'не встановлює']) assert.ok(sourceCase(data, 1).item.answer.includes(text));
    for (const text of ['a cabinet', 'The cabinet', 'a key', 'Furniture needs care']) assert.ok(sourceCase(data, 2).item.answer.includes(text));
    for (const text of ['some biscuits', 'any lockers', 'any cup from these five', 'пропозиція', 'Відкрите питання', 'вільний вибір']) assert.ok(sourceCase(data, 3).item.prompt.includes(text));
    for (const text of ['Each of the three drafts has', 'Every one of', 'Both of the final layouts are', 'Either of the two layouts is', 'Neither of them is']) assert.ok(sourceCase(data, 4).item.prompt.includes(text));
    const negation = sourceCase(data, 5).item;
    assert.equal(negation.answer, 'Not both lamps work.'); assert.equal(negation.m38_semantic_checks.length, 2);
    assert.ok(negation.m38_semantic_checks[0].answer.includes('друга теж несправна'));
    assert.ok(negation.m38_semantic_checks[1].answer.includes('другу ще не перевірили'));
    assert.ok(sourceCase(data, 6).item.accepted.includes('Six folders were placed in the cabinet. Two of the six folders were checked. The two checked folders were dry.'));
});
test('Collocations keep roles/allowed pairs, both questions, statistical subpart, and exact evidential limits', () => {
    const data = practice(2);
    assert.ok(sourceCase(data, 1).item.answer.includes('raise a question about spare-key storage'));
    for (const text of ['The narrow passage poses', 'The team faces', 'неможливість перенесення не додана']) assert.ok(sourceCase(data, 2).item.answer.includes(text));
    for (const text of ['set / established', 'draws / makes', 'не гарантує']) assert.ok(sourceCase(data, 3).item.prompt.includes(text));
    for (const text of ['a distinction between repair and replacement', 'two questions about the warranty']) assert.ok(sourceCase(data, 4).item.prompt.includes(text));
    const adjective = sourceCase(data, 5).item; assert.equal(adjective.answer, 'a small difference');
    assert.equal(adjective.m38_semantic_checks.length, 1); assert.ok(adjective.m38_semantic_checks[0].answer.includes('статистичної значущості'));
    for (const answer of sourceCase(data, 6).item.accepted) {
        for (const model of ['The team faces a challenge', 'The note raises a question', 'does not answer it', 'One dated invoice', 'documentary evidence of the purchase', 'not a complete account']) assert.ok(answer.includes(model));
        assert.doesNotMatch(answer, /impossible|all storage questions|statistically|damaged|loss|will /);
    }
});
test('Only terminal punctuation is optional; internal sentence boundaries survive manual input', () => {
    const data = practice(0), s = state(data), answer = data.inputs[1].answer;
    s.inputAnswers[1] = answer.replace(/[.!?]+$/, ''); assert.equal(s.isCorrect('inputs', 1), true);
    s.inputAnswers[1] = answer.replace('information. It', 'information It'); assert.equal(s.isCorrect('inputs', 1), false);
    const collocation = practice(2), c = state(collocation);
    c.inputAnswers[1] = collocation.inputs[1].answer.replace('store. The', 'store The'); assert.equal(c.isCorrect('inputs', 1), false);
});
test('Supplemental original semantic subparts require every answer and reset with their own case', () => {
    for (const data of [practice(1), practice(2)]) {
        const item = data.inputs[0], s = state(data); s.inputAnswers[0] = item.answer;
        assert.equal(s.isCorrect('inputs', 0), false, 'Phrase alone cannot drop semantic subpart');
        completeSubparts(s, 0, item); assert.equal(s.isCorrect('inputs', 0), true);
        for (const [part, check] of item.m38_semantic_checks.entries()) {
            s.setM38SemanticAnswer(0, part, check.options.find(option => option !== check.answer));
            assert.equal(s.isCorrect('inputs', 0), false, 'Correct phrase plus false subpart rejected');
            completeSubparts(s, 0, item);
        }
        s.resetGroup('inputs'); assert.equal(Object.keys(s.m38SemanticAnswers).length, 0);
        assert.equal(s.isCorrect('inputs', 0), false);
    }
});
for (const [i, target] of content.targets.entries()) {
    const data = practice(i);
    for (const [index, invalid, from] of semanticFixtures(data)) {
        test(target.slug + ': actual semantic boundary ' + from, () => {
            assert.notEqual(invalid, data.inputs[index].answer); const s = state(data);
            completeSubparts(s, index, data.inputs[index]); s.inputAnswers[index] = invalid; assert.equal(s.isCorrect('inputs', index), false);
        });
    }
    test(target.slug + ': ownership rejects lost key and neighbouring prompt', () => {
        const lost = structuredClone(data); lost.author_self_check.answers.pop(); assert.throws(() => exactOwnership(lost, i));
        const moved = structuredClone(data); moved.inputs[0].context = moved.inputs[1].context; assert.throws(() => exactOwnership(moved, i));
    });
}
