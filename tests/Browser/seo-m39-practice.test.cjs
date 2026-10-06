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
const content = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m39-m23-authored-revision.v1.json'), 'utf8'));
const before = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m39-m23-authored-revision-before.json'), 'utf8'));
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
// Independently confirmed by actual read-only question_theory_text_blocks ownership.
const banks = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotNominalStyleAndInformationDensityC2LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillC1LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillC2LessonSeeder',
];
function practice(i) {return JSON.parse(content.targets[i].after.page.blocks.find(b => b.type === 'practice-set').body);}
function state(data) {
    const s = factory({...data, i18n: {score: ':correct / :total', correct: 'Correct', incorrect: 'Incorrect', answer: 'Answer', empty: 'Empty'}});
    s.$nextTick = fn => fn(); s.init(); return s;
}
function completeSubparts() {} // M39 uses each full original author answer, including all subparts.
function sourceCase(data, number) {
    const matches = groups.flatMap(group => data[group].map((item, index) => ({group, item, index})))
        .filter(x => x.item.source_index === number);
    assert.equal(matches.length, 1, 'One owner for original case ' + number);
    return matches[0];
}
function exactOwnership(data, i) {
    const master = JSON.parse(fs.readFileSync(path.join(root, 'docs/content/m23-authored-content.v1.json'), 'utf8'));
    const {JSDOM} = require('jsdom');
    const dom = new JSDOM(master.lessons[i].body_html);
    const section = dom.window.document.querySelector('section[id^="self-check-"]');
    const lists = {prompts: [...section.querySelector(':scope > ol').children].map(node => node.innerHTML),
        answers: [...section.querySelector(':scope > details > ol').children].map(node => node.innerHTML)};
    assert.equal(lists.prompts.length, 6); assert.equal(lists.answers.length, 6);
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
    assert.deepEqual(indices, [1, 2, 3, 4, 5, 6]); dom.window.close();
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
const {semanticFixtures} = require('../../tools/diagnostics/m39-semantic-fixtures.cjs');
test('M39 explicit groups preserve sentence punctuation; legacy slash banks stay unchanged', () => {
    const data = practice(2), s = state(data);
    assert.ok(s.inputTokenBank(1).some(token => token.value.includes('outdoors,')));
    const legacy = state({inputs: [{before: 'I / have worked / today', answer: 'I have worked today'}]});
    assert.deepEqual(Array.from(legacy.inputTokenBank(0), token => token.value), ['I', 'have worked', 'today']);
    assert.equal(network, 0);
});

test('Nominal cases retain all facts, proposed status, original cases and explicit author variants', () => {
    const data = practice(0);
    for (const text of ['expansion', 'is', 'Запланованість не означає']) assert.ok(sourceCase(data, 1).item.answer.includes(text));
    for (const text of ['committee', 'exhibition plan', 'October', 'may']) assert.ok(sourceCase(data, 2).item.answer.includes(text));
    for (const text of ['Ні.', 'час уже скоротився', 'обслуговування поліпшилося', 'The team plans a reduction']) assert.ok(sourceCase(data, 3).item.prompt.includes(text));
    for (const text of ['curator', 'insurance documents', 'Monday', 'Не додавай']) assert.ok(sourceCase(data, 4).item.prompt.includes(text));
    assert.ok(sourceCase(data, 5).item.accepted.includes('The digitisation by the volunteers of the catalogue began in June. The work is still in progress.'));
    assert.equal(sourceCase(data, 6).item.accepted.length, 2);
    for (const answer of sourceCase(data, 6).item.accepted) for (const text of ['has proposed', 'No decision has been made', 'have not been measured']) assert.ok(answer.includes(text));
});
test('C1 cases retain present-result modality, both passive stages, exact subgroup and unchecked descriptions', () => {
    const data = practice(1);
    for (const text of ['would be able to restore them now', 'минул', 'теперішній']) assert.ok(sourceCase(data, 1).item.answer.includes(text));
    for (const text of ['Only after the supervisor had approved', 'were the boxes dispatched', 'без інверсії']) assert.ok(sourceCase(data, 2).item.answer.includes(text));
    for (const text of ['is believed', 'to have been opened before delivery', 'Хто відкрив']) assert.ok(sourceCase(data, 3).item.prompt.includes(text));
    for (const text of ['The two guides who have completed the course are leading the tour', 'троє інших']) assert.ok(sourceCase(data, 4).item.prompt.includes(text));
    for (const answer of sourceCase(data, 5).item.accepted) assert.match(answer, /^Leila may have sent/);
    for (const answer of sourceCase(data, 6).item.accepted) {
        assert.ok(answer.includes('The dates in the catalogue were checked yesterday'));
        assert.ok(answer.includes('The descriptions have not'));
        assert.ok(answer.includes('Could you send the final file by Friday?'));
    }
});
test('C2 case3 keeps both A/B, needn’t/need not and unknown execution; case4 uses source b and unknown distribution', () => {
    const data = practice(2), marta = sourceCase(data, 3).item, scope = sourceCase(data, 4).item;
    for (const text of ['Marta needn’t have printed a second timetable', 'need not have printed', 'У Б відома лише відсутність необхідності', 'факт друку не заданий']) assert.ok(marta.prompt.includes(text));
    assert.equal(scope.answer, 'b'); assert.deepEqual(scope.options, ['a', 'b', 'c']);
    for (const text of ['Гарантоване б)', 'принаймні один', 'Не встановлено точного числа', 'не доведений нею', 'рівно один']) assert.ok(scope.prompt.includes(text));
    const s = state(data); s.choiceAnswers[0] = 'b'; s.choiceAnswers[1] = 'a';
    assert.equal(s.isCorrect('choices', 0), false); assert.equal(s.isCorrect('choices', 1), false);
    s.choiceAnswers[0] = 'a'; s.choiceAnswers[1] = 'b'; assert.equal(s.isCorrect('choices', 0), true); assert.equal(s.isCorrect('choices', 1), true);
});
test('C2 open answers preserve registry quantity/reference and Anika exact indoor limits, no outdoor guarantee', () => {
    const data = practice(2);
    assert.ok(sourceCase(data, 5).item.accepted.some(answer => answer.includes('The two entries without phone numbers')));
    for (const answer of sourceCase(data, 6).item.accepted) {
        for (const text of ['Anika', 'first prototype', 'Monday', 'The second prototype', 'untested', 'may also work outdoors', 'no outdoor test']) {
            if (text === 'untested') assert.ok(answer.includes('untested') || answer.includes('has not been tested'));
            else if (text === 'no outdoor test') assert.match(answer, /[Nn]o outdoor test has taken place/);
            else assert.ok(answer.includes(text), text);
        }
        assert.doesNotMatch(answer, /guarantees|proven reliable outdoors|Both prototypes/);
    }
});
test('Only terminal punctuation is optional; internal sentence boundaries survive manual input', () => {
    const data = practice(0), s = state(data), answer = data.inputs[0].answer;
    s.inputAnswers[0] = answer.replace(/[.!?]+$/, ''); assert.equal(s.isCorrect('inputs', 0), true);
    s.inputAnswers[0] = answer.replace('June. The', 'June The'); assert.equal(s.isCorrect('inputs', 0), false);
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
