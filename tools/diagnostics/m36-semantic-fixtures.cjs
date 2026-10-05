'use strict';
// Explicit mutations of actual accepted M20 input answers, never a universal scorer.
function semanticFixtures(data) {
    const replacements = [
        ['Nora must have unlocked', 'Nora may have unlocked'],
        ['Nora must have unlocked', 'Nora must has unlocked'],
        ['Nora must have unlocked', 'Nora must have unlock'],
        ['Nora must have unlocked', 'Eli must have unlocked'],
        ['Eli can’t have been', 'Eli may not have been'],
        ['Eli can’t have been', 'Eli couldn’t have been'],
        ['at nine', 'at ten'],
        ['The studio was dark yesterday', 'The studio was dark today'],
        ['The rehearsal may have been cancelled', 'The rehearsal must have been cancelled'],
        ['The rehearsal may have been cancelled', 'The rehearsal was cancelled'],
        ['may have been cancelled', 'might of been cancelled'],
        ['each reviewer read', 'each reviewer reads'],
        ['each reviewer read', 'each reviewer should read'],
        ['by Friday', 'by Thursday'],
        ['The coordinator recommends', 'The coordinator confirms'],
        ['both folders', 'one folder'],
        ['the reviewers would not confuse', 'the reviewers would confuse'],
        ['the reviewers would not confuse', 'the writers would not confuse'],
        ['so that the reviewers would not confuse', 'lest the reviewers not confuse'],
        ['You might want to check', 'You must check'],
        ['It may be worth checking', 'It may be worth to check'],
        ['You would be wise to check', 'You would be wise checking'],
        ['You would be wise to check', 'You must check'],
        ['before printing', 'after printing'],
        ['listed one name twice', 'listed every name once'],
        ['Ira needn’t have printed', 'Ira didn’t print'],
        ['Ira needn’t have printed', 'Ira didn’t need to print'],
        ['the extra poster yesterday', 'the extra poster today'],
        ['could well explain', 'definitely explain'],
        ['could well explain', 'may as well explain'],
        ['today’s delay', 'yesterday’s delay'],
        ['It may be worth checking the cupboard', 'You must check the cupboard'],
    ];
    return data.inputs.flatMap((item, index) => replacements.filter(([from]) => item.answer.includes(from))
        .map(([from, to]) => [index, item.answer.replace(from, to), from]));
}
module.exports = {semanticFixtures};
