'use strict';
// Pure read-only contracts; no browser, HTTP or application writes.
const test = require('node:test');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const {packageData, noJsRegressions, unchangedAcceptedTokenBranch, tokenVisibility, contextClassification, serverPractice} = require('../../tools/diagnostics/seo-m31-m26-practice.cjs');

test('Initial server practice discovery preserves the exact x-data prefix despite JSDOM parenthesis parsing', () => {
    const target = packageData().targets[0], data = target.practice.body_data;
    const headings = ['select', 'choice', 'input'].map(name => data[name + '_title'] + ' ' + data[name + '_intro']).join(' ');
    const groups = ['selects', 'choices'].map(name => '<div class="theory-exercise">' + data[name].map(item => '<label>' + item.label + '</label>').join('') + '</div>').join('');
    const body = headings + groups + '<div class="theory-exercise"><input><input></div>';
    const dom = new JSDOM('<div x-data="theoryPracticeSet(abc)">' + body + '</div><div x-data="unrelated()"></div>');
    try {
        assert.deepEqual(serverPractice(dom.window.document, target), {groups: 3, tasks: 6, initialLabelsExact: true, introsExact: true});
        dom.window.document.querySelector('[x-data]').setAttribute('x-data', 'unrelated(theoryPracticeSet(abc))');
        assert.throws(() => serverPractice(dom.window.document, target), /One accepted interactive practice block/);
    } finally {dom.window.close();}
});

test('M26 practice-only inventory is the exact four accepted local lessons', () => {
    const source = packageData(); assert.equal(source.targets.length, 4);
    for (const target of source.targets) {
        assert.ok(target.path.startsWith('/theory/tenses/past-perfect-continuous/'));
        const block = target.after.page.blocks[0]; assert.equal(block.type, 'practice-set');
        assert.deepEqual(JSON.parse(block.body), target.practice.body_data);
        assert.equal(Object.values(target.authored_case_mapping).flat().length, 6);
    }
});

test('M31 preserves the accepted M26 token-only shared-view branch exactly', () => {
    const evidence = unchangedAcceptedTokenBranch(); assert.equal(evidence.identical, true);
    assert.equal(evidence.acceptedSha, 'ceb2162c862978ca461607be3913a9f717f380b2');
});

test('No-JS diagnostics do not mistake an empty token input for a complete exercise', () => {
    for (const target of packageData().targets) for (const [index, input] of target.practice.body_data.inputs.entries()) {
        const complete = tokenVisibility(input.before, input, index); assert.equal(complete.completeContextVisible, true);
        const missing = tokenVisibility('Банк токенів → ...', input, index); assert.equal(missing.completeContextVisible, false);
        assert.ok(missing.missingTokenGroups.length > 0);
    }
});

test('Extra no-JS scope is exactly nine unchanged accepted M27–M29 lessons', () => {
    const source = noJsRegressions(); assert.equal(source.targets.length, 9);
    for (const group of ['M27', 'M28', 'M29']) assert.equal(source.targets.filter(target => target.group === group).length, 3);
    assert.equal(source.targets.flatMap(target => target.practice.body_data.inputs).filter(input => input.before.includes('/')).length, 14);
    assert.ok(Object.values(source.sourceHashes).every(hash => /^[a-f0-9]{64}$/.test(hash)));
});

test('Absent token banks remain limitations even when an exact alternative prompt is visible', () => {
    const targets = noJsRegressions().targets;
    const reason = targets.find(target => target.slug === 'linking-words-reason-result-contrast');
    const ellipsis = targets.find(target => target.slug === 'ellipsis-substitution-and-reference');
    for (const [target, index, expectedSource] of [[reason, 0, 'choice'], [ellipsis, 1, 'after']]) {
        const item = target.practice.body_data.inputs[index], tokens = tokenVisibility('Банк токенів', item, index);
        assert.equal(tokens.completeContextVisible, false);
        const observed = {tokens, beforeVisible: false, afterVisible: true, choiceLabels: target.practice.body_data.choices.map(choice => choice.label)};
        const context = contextClassification(target, index, observed); assert.equal(context.completeManualContextVisible, true);
        assert.equal(context.tokenBankStillMissing, true); assert.ok(context.source.includes(expectedSource));
        observed.afterVisible = false; observed.choiceLabels = [];
        assert.equal(contextClassification(target, index, observed).completeManualContextVisible, false, 'Payload-only data cannot prove a visible alternative prompt');
    }
});

test('Plain M27 rewrite prompts are complete; unrelated partial after hints do not repair token context', () => {
    const targets = noJsRegressions().targets;
    const plain = targets.find(target => target.slug === 'advanced-linking-devices');
    const input = plain.practice.body_data.inputs[0];
    const complete = {tokens: tokenVisibility(input.before, input, 0), beforeVisible: true, afterVisible: false, choiceLabels: []};
    assert.equal(contextClassification(plain, 0, complete).completeManualContextVisible, true);
    complete.beforeVisible = false; assert.equal(contextClassification(plain, 0, complete).completeManualContextVisible, false);
    const partial = targets.find(target => target.slug === 'cleft-sentences-emphasis');
    const item = partial.practice.body_data.inputs[1];
    assert.equal(contextClassification(partial, 1, {tokens: tokenVisibility(item.after, item, 1), beforeVisible: false,
        afterVisible: true, choiceLabels: partial.practice.body_data.choices.map(choice => choice.label)}).completeManualContextVisible, false);
});
