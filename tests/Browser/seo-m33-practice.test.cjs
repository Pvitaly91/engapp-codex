'use strict';
// Pure VM/source checks: no Laravel boot, working .env, HTTP, browser or database.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../..');
const view = fs.readFileSync(path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php'), 'utf8');
const script = view.slice(view.indexOf('<script>') + 8, view.lastIndexOf('</script>'));
const content = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m33-m17-academic-english.v1.json'), 'utf8'));
const before = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/m33-m17-academic-english-before.json'), 'utf8'));
let factory;
let network = 0;
vm.runInNewContext(script, {
    document: {addEventListener: (_, fn) => fn(), querySelector: () => null},
    Alpine: {data: (_, fn) => {factory = fn;}},
    window: {EnglishAnswerVariants: require('./load-answer-variants.cjs')},
    fetch: () => {network += 1; throw Error('Unexpected network request');},
    setTimeout, clearTimeout,
});
const groups = ['selects', 'choices', 'inputs'];
const mappings = [
    {selects: [2, 5], choices: [1, 3], inputs: [4, 6]},
    {selects: [2, 4], choices: [1, 3], inputs: [5, 6]},
    {selects: [2, 5], choices: [1, 4], inputs: [3, 6]},
];
const banks = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotHedgingAndCautiousLanguageBasicsB2LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotHedgingAndCautiousLanguageC1LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotStanceRegisterAndEvaluationC2LessonSeeder',
];
function practice(index) {
    return JSON.parse(content.targets[index].after.page.blocks.find(block => block.type === 'practice-set').body);
}
function state(data) {
    const result = factory({...data, i18n: {
        score: ':correct / :total', correct: 'Correct', incorrect: 'Incorrect', answer: 'Answer', empty: 'Empty',
    }});
    result.$nextTick = fn => fn();
    result.init();
    return result;
}
function sourceCase(data, number) {
    const matches = groups.flatMap(group => data[group].map((item, index) => ({group, index, item})))
        .filter(value => value.item.source_index === number);
    assert.equal(matches.length, 1, 'Exactly one interactive owner for original case ' + number);
    return matches[0];
}
function sourceLists(targetIndex) {
    const html = before.targets[targetIndex].before.page.blocks[1].body;
    const lists = {};
    for (const [name, attribute] of [['prompts', 'data-self-checks'], ['answers', 'data-self-check-answers']]) {
        const match = html.match(new RegExp('<ol ' + attribute + '>([\\s\\S]*?)<\\/ol>'));
        assert.ok(match, 'Accepted author list exists: ' + attribute);
        lists[name] = Array.from(match[1].matchAll(/<li>([\s\S]*?)<\/li>/g), item => item[1]);
        assert.equal(lists[name].length, 6);
    }
    return lists;
}
function exactOwnership(data, targetIndex) {
    const author = sourceLists(targetIndex);
    assert.deepEqual(data.author_self_check.prompts, author.prompts);
    assert.deepEqual(data.author_self_check.answers, author.answers);
    const indices = [];
    for (const group of groups) {
        assert.deepEqual(data[group].map(item => item.source_index), mappings[targetIndex][group]);
        for (const item of data[group]) {
            assert.ok(item.answer, 'Interactive answer cannot disappear');
            assert.equal(item.context, author.prompts[item.source_index - 1]);
            assert.equal(item.author_explanation, author.answers[item.source_index - 1]);
            indices.push(item.source_index);
        }
    }
    assert.deepEqual(indices.sort((a, b) => a - b), [1, 2, 3, 4, 5, 6]);
}
for (const [targetIndex, target] of content.targets.entries()) {
    const data = practice(targetIndex);
    for (const group of groups) {
        test(target.slug + ': finite ' + group + ' mappings preserve two original cases', () => {
            assert.equal(data[group].length, 2);
            assert.deepEqual(data[group].map(item => item.source_index), mappings[targetIndex][group]);
        });
        test(target.slug + ': ' + group + ' correct, alternatives, wrong, score and reset', () => {
            const s = state(data);
            data[group].forEach((item, index) => {
                for (const accepted of item.accepted || [item.answer]) {
                    s.answerSource(group)[index] = accepted;
                    assert.equal(s.isCorrect(group, index), true, 'Approved variant for case ' + item.source_index);
                    if (group === 'inputs') {
                        s.inputAnswers[index] = accepted.replace(/[.!?]+$/, '');
                        assert.equal(s.isCorrect(group, index), true, 'Terminal punctuation is optional');
                    }
                }
                s.answerSource(group)[index] = item.answer;
            });
            s.check(group);
            assert.equal(s.scoreText(group), '2 / 2');
            assert.equal(s.isChecked(group), true);
            assert.match(s.fieldClass(group, 0), /emerald/);
            s.answerSource(group)[0] = 'wrong answer';
            assert.equal(s.isCorrect(group, 0), false);
            assert.equal(s.scoreText(group), '1 / 2');
            assert.match(s.fieldClass(group, 0), /rose/);
            s.resetGroup(group);
            assert.equal(s.isChecked(group), false);
            assert.equal(Object.keys(s.answerSource(group)).length, 0);
            assert.equal(s.fieldClass(group, 0), '');
        });
        if (group !== 'inputs') test(target.slug + ': ' + group + ' existing distractors are rejected unless author-approved', () => {
            const s = state(data);
            data[group].forEach((item, index) => {
                const values = item.options || data.options || (group === 'choices' ? data.choice_options : []);
                const accepted = item.accepted || [item.answer];
                assert.ok(values.length >= 2, 'At least two actual options, not an invented fixture');
                for (const value of values) {
                    s.answerSource(group)[index] = value;
                    assert.equal(s.isCorrect(group, index), accepted.includes(value), 'Actual option case ' + item.source_index);
                }
            });
        });
    }
    test(target.slug + ': grouped tokens, manual editing, deletion reuse and no suggestions/fetch', async () => {
        const s = state(data);
        for (const [index, item] of data.inputs.entries()) {
            const bank = s.inputTokenBank(index);
            assert.ok(bank.length > 1);
            assert.ok(bank.every(token => token.value.trim().split(/\s+/).length <= 3));
            s.appendInputToken('inputs', index, bank[0]);
            assert.equal(s.inputTokenBank(index)[0].used, true);
            const once = s.inputAnswers[index];
            s.appendInputToken('inputs', index, bank[0]);
            assert.equal(s.inputAnswers[index], once, 'Used token cannot be inserted twice');
            s.inputAnswers[index] = '';
            s.syncInputTokenBank(index);
            assert.equal(s.inputTokenBank(index)[0].used, false);
            s.inputAnswers[index] = item.answer;
            s.syncInputTokenBank(index);
            assert.ok(s.inputTokenBank(index).every(token => token.used));
            s.wordSuggestionOpen['inputs-' + index] = true;
            s.wordSuggestionResults['inputs-' + index] = [{word: 'test'}];
            assert.equal(s.isWordSuggestionOpen('inputs', index), false);
            await s.searchWordSuggestions('inputs', index, {target: {value: 'test', selectionStart: 4}});
            assert.equal(s.isWordSuggestionOpen('inputs', index), false);
        }
        s.resetGroup('inputs');
        assert.ok(Object.values(s.inputTokenBanks).every(bank => bank.every(token => !token.used)));
        assert.equal(network, 0);
    });
    test(target.slug + ': complete canonical answers are constructible only by token clicks', () => {
        const s = state(data);
        data.inputs.forEach((item, index) => {
            let remaining = item.answer;
            const used = new Set();
            while (remaining !== '') {
                const token = s.inputTokenBank(index).filter(value => !used.has(value.id) && remaining.startsWith(value.value))
                    .sort((a, b) => b.value.length - a.value.length)[0];
                assert.ok(token, 'Unconstructible original case ' + item.source_index + ': ' + remaining);
                s.appendInputToken('inputs', index, token);
                used.add(token.id);
                remaining = remaining.slice(token.value.length).trimStart();
            }
            assert.equal(s.inputAnswers[index], item.answer);
            assert.equal(s.isCorrect('inputs', index), true);
            assert.equal(s.isInputTokenBankEmpty(index), true);
        });
    });
    test(target.slug + ': exact six prompts, keys and source ownership; single-level own widget', () => {
        exactOwnership(data, targetIndex);
        assert.deepEqual(data.linked_practice.question_types, ['4']);
        assert.deepEqual(data.linked_practice.seeder_classes, [banks[targetIndex]]);
        assert.doesNotMatch(data.linked_practice.seeder_classes[0], /AllLevels/);
        assert.match(data.linked_practice.seeder_classes[0], /Polyglot.*LessonSeeder$/);
    });
}

// Responses deliberately change an actual accepted task's facts, force, bounds or punctuation.
// These are test-only negative fixtures, never new teaching text or bank questions.
const negatives = [
    ['B2 carpet observation becomes proved rain causation', 0, 1, 'b'],
    ['B2 printer modal gains incorrect -s', 0, 2, 'The printer may needs paper. The printer seems to need paper.'],
    ['B2 printer seem loses singular agreement', 0, 2, 'The printer may need paper. The printer seem to need paper.'],
    ['B2 printer seem loses required infinitive', 0, 2, 'The printer may need paper. The printer seems need paper.'],
    ['B2 two requested printer constructions collapse to one', 0, 2, 'The printer may need paper.'],
    ['B2 might keeps incorrect -s', 0, 5, 'The handle might needs repair.'],
    ['B2 might gains incorrect to', 0, 5, 'The handle might to need repair.'],
    ['B2 generally is conflated with guaranteed present condition', 0, 3, 'b'],
    ['B2 relative route becomes a probability', 0, 4, 'On this journey, the new route was probably quick compared with our usual route: twenty minutes rather than forty-five.'],
    ['B2 relative route becomes a general tendency', 0, 4, 'On this journey, the new route was generally quick compared with our usual route: twenty minutes rather than forty-five.'],
    ['B2 route loses its comparison point', 0, 4, 'On this journey, the new route was relatively quick.'],
    ['B2 route gets a fabricated always guarantee', 0, 4, 'The new route is always quick compared with our usual route: twenty minutes rather than forty-five.'],
    ['B2 route comparison values reverse', 0, 4, 'On this journey, the new route was relatively quick compared with our usual route: forty-five minutes rather than twenty.'],
    ['B2 route criterion values change', 0, 4, 'On this journey, the new route was relatively quick compared with our usual route: ten minutes rather than forty-five.'],
    ['B2 known lamp observation becomes uncertain', 0, 6, 'The lamp may have come on after the battery was replaced. The old battery may have caused the problem.'],
    ['B2 possible battery cause becomes proven', 0, 6, 'The lamp came on after the battery was replaced. The old battery definitely caused the problem.'],
    ['B2 lamp sequence is changed into a causal guarantee', 0, 6, 'The lamp came on because the battery was replaced. The old battery may have caused the problem.'],
    ['B2 lamp observation is lost', 0, 6, 'The old battery may have caused the problem.'],
    ['B2 battery observation and explanation comma splice', 0, 6, 'The lamp came on after the battery was replaced, the old battery may have caused the problem.'],
    ['C1 seven of eight become everyone', 1, 2, 'In this group of eight volunteers, everyone completed the second search faster, when the new labels were used. The fixed order means that practice cannot be ruled out as an explanation.'],
    ['C1 direct group result becomes uncertain', 1, 2, 'In this group of eight volunteers, seven possibly completed the second search faster, when the new labels were used. The fixed order means that practice cannot be ruled out as an explanation.'],
    ['C1 known one-volunteer result is unnecessarily hedged', 1, 1, 'b'],
    ['C1 exact group size changes', 1, 2, 'In this group of nine volunteers, seven completed the second search faster, when the new labels were used. The fixed order means that practice cannot be ruled out as an explanation.'],
    ['C1 fixed-order confound disappears', 1, 2, 'In this group of eight volunteers, seven completed the second search faster, when the new labels were used.'],
    ['C1 labels claimed as proved cause', 1, 2, 'In this group of eight volunteers, seven completed the second search faster because the new labels caused the improvement.'],
    ['C1 not necessarily is changed to definitely not', 1, 3, 'b'],
    ['C1 untested-user forecast becomes categorical', 1, 4, 'Початкове may not категорично заперечувало будь-яку користь.'],
    ['C1 untested-user forecast is misread as prohibition', 1, 4, 'Обидві конструкції мають однакову силу; початкове may not було забороною.'],
    ['C1 signs sequence becomes proved cause', 1, 5, 'The new signs caused the shorter queues, but fewer visitors came that day. The signs may have helped; the lower number of visitors is another possible explanation.'],
    ['C1 fewer visitors observation is removed', 1, 5, 'The queues were shorter after the new signs were installed. The signs may have helped.'],
    ['C1 visitor alternative becomes certain cause', 1, 5, 'The queues were shorter after the new signs were installed, but fewer visitors came that day. The lower number of visitors definitely caused the shorter queues.'],
    ['C1 signs alternative becomes fabricated measurement', 1, 5, 'The queues were shorter after the new signs were installed, but fewer visitors came that day. A timed trial proved that the signs caused the change.'],
    ['C1 signs paragraph sentence boundary becomes comma splice', 1, 5, 'The queues were shorter after the new signs were installed, but fewer visitors came that day, the signs may have helped; the lower number of visitors is another possible explanation.'],
    ['C1 paragraph seven of eight become everyone', 1, 6, 'Everyone completed the second search faster. The new labels may have helped, but everyone used them second, so practice may also explain the change.'],
    ['C1 paragraph known result is hedged', 1, 6, 'Seven of the eight volunteers may have completed the second search faster. The new labels may have helped, but everyone used them second, so practice may also explain the change.'],
    ['C1 paragraph ordering limit is lost', 1, 6, 'Seven of the eight volunteers completed the second search faster. The new labels may have helped.'],
    ['C1 paragraph order is invented as counterbalanced', 1, 6, 'Seven of the eight volunteers completed the second search faster. The new labels may have helped, but everyone used them in a different order, so practice may also explain the change.'],
    ['C1 paragraph interpretation is strengthened to certainty', 1, 6, 'Seven of the eight volunteers completed the second search faster. The new labels helped, but everyone used them second, so practice may also explain the change.'],
    ['C1 paragraph internal sentence boundary is lost', 1, 6, 'Seven of the eight volunteers completed the second search faster, the new labels may have helped, but everyone used them second, so practice may also explain the change.'],
    ['C2 equal trial times do not prove false results', 2, 2, 'false'],
    ['C2 description evaluation and recommendation are misclassified', 2, 1, 'b'],
    ['C2 source stance is strengthened to proves and tested certainty', 2, 4, 'd'],
    ['C2 equal trial times do not prove equality in every situation', 2, 2, 'The two versions are equally fast in all possible tasks.'],
    ['C2 glossary evaluation loses its criterion', 2, 3, 'The glossary is good.'],
    ['C2 glossary evaluation invents a learning benefit', 2, 3, 'This edition improves learning: each term now links to an example, unlike the previous edition.'],
    ['C2 glossary criterion reverses the accepted comparison', 2, 3, 'The previous edition improves access to examples: each term links to one, unlike this edition.'],
    ['C2 glossary comparison loses previous-edition limit', 2, 3, 'This edition improves access to examples.'],
    ['C2 observed missing date is hedged', 2, 5, 'The checklist might contain no revision date. This may make it harder to identify the latest version, although users\' choices have not been observed.'],
    ['C2 missing revision date turns into present date', 2, 5, 'The checklist contains a revision date. This may make it harder to identify the latest version, although users\' choices have not been observed.'],
    ['C2 unobserved users become a universal guaranteed error', 2, 5, 'The checklist contains no revision date. Every user will choose the wrong version.'],
    ['C2 missing-date paragraph loses unobserved-user limit', 2, 5, 'The checklist contains no revision date. This will make it harder to identify the latest version.'],
    ['C2 audio-guide criterion is replaced by overall superiority', 2, 6, 'The new audio guide is better overall. However, it has been checked on only one device.'],
    ['C2 audio-guide limitation becomes fictional multi-device evidence', 2, 6, 'For navigation between sections, the new audio guide offers a clear advantage: listeners can select a named section directly. However, it has been checked on several devices, so its compatibility with other devices is established.'],
    ['C2 audio-guide navigation advantage becomes invented faster learning', 2, 6, 'For faster learning, the new audio guide offers a clear advantage: listeners learn more quickly. However, it has been checked on only one device, so its compatibility with other devices remains unknown.'],
    ['C2 audio-guide measured-function scope is removed', 2, 6, 'The new audio guide offers a clear advantage in every respect: listeners can select a named section directly. However, it has been checked on only one device, so its compatibility with other devices remains unknown.'],
    ['C2 audio-guide one-device limitation is lost', 2, 6, 'For navigation between sections, the new audio guide offers a clear advantage: listeners can select a named section directly.'],
    ['C2 audio-guide method limitation is replaced by personal attack', 2, 6, 'The author is careless and the review is useless.'],
    ['C2 audio-guide paragraph has a comma splice', 2, 6, 'For navigation between sections, the new audio guide offers a clear advantage: listeners can select a named section directly, however, it has been checked on only one device, so its compatibility with other devices remains unknown.'],
];
for (const [label, targetIndex, number, answer] of negatives) {
    test('Reject ' + label, () => {
        const data = practice(targetIndex), {group, index, item} = sourceCase(data, number);
        const s = state(data);
        assert.notEqual(answer, item.answer, 'Actual changed response, not the correct one');
        s.answerSource(group)[index] = answer;
        assert.equal(s.isCorrect(group, index), false);
    });
}

test('Literal ownership contract catches prompt/key loss, duplication, neighbour context and translation loss', () => {
    for (const [index, original] of content.targets.entries()) {
        const data = practice(index);
        exactOwnership(data, index);
        const lostKey = structuredClone(data); lostKey.author_self_check.answers.pop();
        assert.throws(() => exactOwnership(lostKey, index));
        const lostAnswer = structuredClone(data); delete lostAnswer.inputs[0].answer;
        assert.throws(() => exactOwnership(lostAnswer, index));
        const duplicate = structuredClone(data); duplicate.inputs[0].source_index = duplicate.choices[0].source_index;
        assert.throws(() => exactOwnership(duplicate, index));
        const neighbour = structuredClone(data); neighbour.inputs[0].context = neighbour.inputs[1].context;
        assert.throws(() => exactOwnership(neighbour, index));
        const translation = structuredClone(data);
        translation.author_self_check.answers[0] = translation.author_self_check.answers[0].replace(/[А-Яа-яІіЇїЄєҐґ]+/u, '');
        assert.notEqual(translation.author_self_check.answers[0], data.author_self_check.answers[0]);
        assert.throws(() => exactOwnership(translation, index));
        assert.equal(original.after.page.locale, 'uk');
    }
});

test('Contextual feedback preserves forecast versus prohibition and all approved reporting verbs', () => {
    const b2Data = practice(0), b2 = state(b2Data), lamp = sourceCase(b2Data, 6);
    assert.equal(lamp.item.accepted.length, 2);
    assert.equal(lamp.item.accepted[1], 'The lamp came on after the battery was replaced. The old battery could explain the problem.');
    for (const value of lamp.item.accepted) {
        b2.answerSource(lamp.group)[lamp.index] = value;
        assert.equal(b2.isCorrect(lamp.group, lamp.index), true);
        assert.ok(value.startsWith('The lamp came on after the battery was replaced.'));
    }
    const c1Data = practice(1), c1 = state(c1Data), forecast = sourceCase(c1Data, 4);
    for (const value of forecast.item.options.slice(1)) {
        c1.answerSource(forecast.group)[forecast.index] = value;
        assert.equal(c1.isCorrect(forecast.group, forecast.index), false);
        assert.equal(c1.feedbackText(forecast.group, forecast.index), forecast.item.author_explanation);
        assert.match(c1.feedbackText(forecast.group, forecast.index), /Обидві конструкції граматичні/u);
        assert.match(c1.feedbackText(forecast.group, forecast.index), /прогнозом, не забороною/u);
    }
    const c2Data = practice(2), c2 = state(c2Data), reporting = sourceCase(c2Data, 4);
    assert.deepEqual(reporting.item.options, ['a', 'b', 'c', 'd']);
    assert.deepEqual(reporting.item.accepted, ['a', 'b', 'c']);
    for (const value of reporting.item.accepted) {
        c2.answerSource(reporting.group)[reporting.index] = value;
        assert.equal(c2.isCorrect(reporting.group, reporting.index), true);
    }
    for (const verb of ['suggests', 'states', 'writes']) {
        assert.ok(reporting.item.prompt.includes('Source B ' + verb + ' that the shorter form may reduce copying errors but notes that this possibility has not been tested.'));
    }
    c2.answerSource(reporting.group)[reporting.index] = 'd';
    assert.equal(c2.isCorrect(reporting.group, reporting.index), false);
    assert.equal(c2.feedbackText(reporting.group, reporting.index), reporting.item.author_explanation.replace(/<[^>]*>/g, ''));
    assert.match(c2.feedbackText(reporting.group, reporting.index), /Proves додало б доведеність/u);
    const classification = sourceCase(c2Data, 1);
    assert.match(classification.item.prompt, /Опис → оцінка → рекомендація/u);
    assert.match(classification.item.prompt, /придатність для швидкого пошуку/u);
    assert.equal(network, 0);
});

test('Literal author-copy contract rejects changed recommendation status, one-source consensus and adverb force', () => {
    const recommendation = practice(2);
    assert.ok(recommendation.choices[0].context.includes('The next edition should include an index.'));
    recommendation.choices[0].context = recommendation.choices[0].context
        .replace('The next edition should include an index.', 'The next edition includes an index.');
    assert.throws(() => exactOwnership(recommendation, 2));
    const consensus = practice(2);
    consensus.choices[1].author_explanation = consensus.choices[1].author_explanation
        .replace('Source B suggests', 'All experts prove');
    assert.notEqual(consensus.choices[1].author_explanation, practice(2).choices[1].author_explanation);
    assert.throws(() => exactOwnership(consensus, 2));
    const adverb = practice(0);
    adverb.choices[1].author_explanation = adverb.choices[1].author_explanation.replace('generally', 'probably');
    assert.notEqual(adverb.choices[1].author_explanation, practice(0).choices[1].author_explanation);
    assert.throws(() => exactOwnership(adverb, 0));
});
