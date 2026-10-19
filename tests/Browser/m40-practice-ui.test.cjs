'use strict';
// Pure VM/DOM/source tests. No Laravel, .env, HTTP, browser, or working DB.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {JSDOM} = require('jsdom');
const {targets, assertOptionOnly} = require('../../tools/diagnostics/seo-m40-local.cjs');
const ROOT = path.resolve(__dirname, '../..');
const common = fs.readFileSync(path.join(ROOT, 'public/js/authored-practice-ui.js'), 'utf8');
const commonPartial = fs.readFileSync(path.join(ROOT, 'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php'), 'utf8');
const javascript = fs.readFileSync(path.join(ROOT, 'public/js/m40-practice-ui.js'), 'utf8');
const partial = fs.readFileSync(path.join(ROOT, 'resources/views/engram/theory/blocks-v3/m40-practice-ui.blade.php'), 'utf8');
const scope = {EnglishAnswerVariants: require('./load-answer-variants.cjs')};
vm.runInNewContext(common, scope); vm.runInNewContext(javascript, scope);
const factory = scope.m40PracticeUi;
const plain = value => JSON.parse(JSON.stringify(value));
// Finite short response fragments, independently read from the original M24
// questions/keys; this is semantic ownership, not an arbitrary payload limit.
const expectedCandidates = require('../../tools/diagnostics/seo-m40-local.cjs').answerFragments;
function state(target) {return factory({cases: target.data.cases});}
function correct(s, taskIndex) {
    s.cases[taskIndex].controls.forEach((control, p) => {
        if (control.kind === 'multi') control.answer.forEach(value => s.setAnswer(taskIndex, p, value));
        else s.setAnswer(taskIndex, p, control.answer);
    });
}
function locatorEvent(values, sourceIndex = 0) {
    const focused = [];
    const buttons = values.map((_, i) => ({focus: () => focused.push(i)}));
    return {event: {target: {getAttribute: () => values[sourceIndex].value,
        closest: () => ({querySelectorAll: () => buttons})}}, focused, sourceIndex};
}
test('Finite M24 ownership:18 cases,12 compound and32 controls 10select6choice16manual', () => {
    assert.equal(targets.length, 3);
    const tasks = targets.flatMap(t => t.data.cases);
    const kinds = tasks.flatMap(task => task.controls.map(c => c.kind));
    assert.equal(tasks.length, 18); assert.equal(tasks.filter(t => t.interaction === 'compound').length, 12);
    assert.equal(kinds.length, 32); assert.equal(kinds.filter(k => k === 'manual').length, 16);
    assert.equal(kinds.filter(k => k === 'select').length, 10);
    assert.equal(kinds.filter(k => k === 'choice').length, 6); assert.equal(kinds.filter(k => k === 'multi').length, 0);
    assert.deepEqual(targets.map(t => t.data.cases.reduce((n,c) => n+c.controls.length,0)), [10,9,13]);
    for (const t of targets) assert.deepEqual(t.data.cases.map(task => task.source_index), [1,2,3,4,5,6]);
});
for (const target of targets) {
    for (const [i, task] of target.data.cases.entries()) {
        test(target.slug + ': original case ' + task.source_index + ' correct/wrong/reset/score', () => {
            const s = state(target); assert.equal(s.checked[i], false); assert.equal(s.isCorrect(i), false); assert.equal(s.score, 0);
            correct(s, i); assert.equal(s.isCorrect(i), true); s.check(i); assert.equal(s.checked[i], true); assert.equal(s.score, 1);
            const first = task.controls[0];
            if (first.kind === 'multi') s.setAnswer(i, 0, first.options.find(o => !first.answer.includes(o.value)).value);
            else s.setAnswer(i, 0, first.kind === 'manual' ? 'wrong answer' : first.options.find(o => o.value !== first.answer).value);
            assert.equal(s.checked[i], false, 'Editing hides feedback'); s.check(i); assert.equal(s.isCorrect(i), false); assert.equal(s.score, 0);
            s.reset(i); assert.equal(s.checked[i], false);
            assert.deepEqual(plain(s.answers[i]), task.controls.map(c => c.kind === 'multi' ? [] : ''));
        });
        for (const [p, control] of task.controls.entries()) {
            if (control.kind === 'manual') {
                test(target.slug + ': case ' + task.source_index + ' manual ' + control.id + ' tokens/aliases/punctuation', () => {
                    const s = state(target);
                    assert.equal(control.tokens.join(' '), control.answer);
                    assert.ok(control.tokens.every(value => value.split(/\s+/u).length <= 3));
                    assert.ok(control.tokens.every(value => !/[.!?]\s+\p{L}/u.test(value)), 'No sentence-crossing token group');
                    assert.notDeepEqual(s.banks[i][p].map(token => token.index), control.tokens.map((_, index) => index));
                    s.appendToken(i, p, 0); assert.equal(s.tokenUsed(i, p, 0), true);
                    const once = s.answers[i][p]; s.appendToken(i, p, 0); assert.equal(s.answers[i][p], once);
                    s.setAnswer(i, p, ''); assert.equal(s.tokenUsed(i, p, 0), false, 'Backspace/manual deletion restores token');
                    control.tokens.forEach((_, index) => s.appendToken(i, p, index));
                    assert.equal(s.answers[i][p], control.answer); assert.equal(s.partCorrect(i, p), true);
                    assert.ok(control.tokens.every((_, index) => s.tokenUsed(i, p, index)));
                    for (const alias of control.accepted) {
                        s.setAnswer(i, p, alias); assert.equal(s.partCorrect(i, p), true);
                        s.setAnswer(i, p, alias.replace(/[.!?]+$/u, '')); assert.equal(s.partCorrect(i, p), true);
                    }
                    const internal = control.answer.match(/[.!?]\s+\p{L}/u);
                    if (internal) {
                        s.setAnswer(i, p, control.answer.replace(internal[0], internal[0].slice(1)));
                        assert.equal(s.partCorrect(i, p), false, 'Internal sentence punctuation is not erased');
                    }
                });
            } else {
                test(target.slug + ': case ' + task.source_index + ' short candidates have no full key or explanation', () => {
                    const key = target.data.author_self_check.answers[task.source_index - 1];
                    assert.deepEqual(control.options.map(o => o.label), expectedCandidates[control.id]);
                    for (const option of control.options) assertOptionOnly(option.label, option.value, key);
                    const leaking = control.options[0].label + ' ' + key;
                    assert.throws(() => assertOptionOnly(leaking, expectedCandidates[control.id][0], key));
                    assert.ok(control.options.every(o => !/<[^>]+>/u.test(o.label)));
                    const s = state(target);
                    if (control.kind === 'multi') {
                        s.setAnswer(i, p, control.answer[0]); assert.equal(s.partCorrect(i, p), false);
                        control.answer.slice(1).forEach(value => s.setAnswer(i, p, value)); assert.equal(s.partCorrect(i, p), true);
                    } else {
                        control.options.forEach(option => {
                            s.setAnswer(i, p, option.value); assert.equal(s.partCorrect(i, p), option.value === control.answer);
                        });
                    }
                });
                if (control.kind !== 'multi') test(target.slug + ': case ' + task.source_index + ' radio arrow cycle is keyboard-operable', () => {
                    const s = state(target), {event, focused} = locatorEvent(control.options);
                    s.setAnswer(i, p, control.options[0].value); s.cycleAnswer(i, p, -1, event);
                    assert.equal(s.answers[i][p], control.options.at(-1).value); assert.equal(focused.at(-1), control.options.length - 1);
                    s.cycleAnswer(i, p, 1, event); assert.equal(s.answers[i][p], control.options[0].value); assert.equal(focused.at(-1), 0);
                    s.reset(i); s.cycleAnswer(i, p, -1, event);
                    assert.equal(s.answers[i][p], control.options.at(-1).value, 'Blank reverse cycle wraps from focused first radio');
                });
            }
        }
    }
    test(target.slug + ': all six correct source cases yield 6, per-case reset reduces score', () => {
        const s = state(target);
        target.data.cases.forEach((_, i) => {correct(s, i); s.check(i);}); assert.equal(s.score, 6);
        s.reset(0); assert.equal(s.score, 5);
    });
}
for (const target of targets) for (const [i, task] of target.data.cases.entries()) if (task.controls.length > 1)
    test(target.slug + ': compound ' + task.source_index + ' requires every subpart', () => {
        const s = state(target); correct(s, i); assert.equal(s.isCorrect(i), true);
        for (let p = 0; p < task.controls.length; p++) {
            correct(s, i); s.setAnswer(i,p,''); s.check(i);
            assert.equal(s.isCorrect(i), false); assert.equal(s.score,0);
        }
    });

const sourceNegatives = [
    [0,2,'have known','have been knowing'], [0,2,'Monday','Tuesday'],
    [0,5,'the introduction','the third chapter'], [0,5,'two hours','three hours'],
    [0,5,'have been translating','have translated'], [0,5,'not ready yet','ready now'],
    [1,0,'unlocked the gate','had unlocked the gate'],
    [1,0,'unlocked the gate, switched on the light','switched on the light, unlocked the gate'],
    [1,2,'had already left','left after we arrived'],
    [1,4,'Did she open','Did she opened'],
    [1,5,'Someone','Olena'], [1,5,'was talking','had finished talking'],
    [1,5,'before I arrived','after I arrived'], [1,5,'Then I opened','Before that I opened'],
    [2,2,'if I was','if was I'], [2,2,'if I was','if I am'],
    [2,3,'must be labelled','have been labelled'],
    [2,4,'Marta, who lives nearby,','Marta who lives nearby'],
    [2,4,'agreed to help','helped'],
    [2,5,'had a bigger desk','have a bigger desk'],
];
for (const [owner,i,from,to] of sourceNegatives) test('Source semantic boundary: '+from+' → '+to, () => {
    const target=targets[owner], s=state(target), control=target.data.cases[i].controls[0];
    assert.ok(control.answer.includes(from)); s.setAnswer(i,0,control.answer.replace(from,to));
    assert.equal(s.partCorrect(i,0),false);
});
for (const [id,invalid] of [
    ['m40-n5-guide','The guide had went home before I called.'],
    ['m40-b3-instruction','She told me to open the parcel.'],
    ['m40-b3-instruction','She told me not to opened the parcel.'],
    ['m40-b4-passive','The boxes must be labelled.'],
    ['m40-b4-causative','I repaired my bicycle yesterday.'],
    ['m40-b4-causative',"I'd my bicycle repaired yesterday."],
    ['m40-b5-although','Although it was late, but we continued.'],
    ['m40-b6-conditional','We left earlier and caught the bus.'],
    ['m40-b6-conditional','If we leave earlier, we will catch the bus.'],
    ['m40-b6-might','Lena is in the reading room.'],
    ['m40-b6-might','Lena must be in the reading room.'],
]) test('Source required control '+id+' rejects '+invalid, () => {
    const target=targets.find(t=>t.data.cases.some(c=>c.controls.some(p=>p.id===id)));
    const i=target.data.cases.findIndex(c=>c.controls.some(p=>p.id===id));
    const p=target.data.cases[i].controls.findIndex(c=>c.id===id), s=state(target);
    s.setAnswer(i,p,invalid); assert.equal(s.partCorrect(i,p),false);
});
test('M40 wrapper is independent and common renderer preserves exact post-check keys/natural answer casing', () => {
    assert.match(javascript, /data-m40-answer/); assert.doesNotMatch(javascript,/c2-1-answer|m39PracticeUi/);
    assert.match(commonPartial, /:open="checked/); assert.match(commonPartial,/summary x-show="checked/);
    assert.match(commonPartial,/self-check-answers/); assert.match(commonPartial,/answer-input/);
    assert.doesNotMatch(commonPartial,/\buppercase\b|strtoupper/); assert.doesNotMatch(common,/toUpperCase/);
    assert.match(commonPartial,/autocomplete="off"/); assert.match(commonPartial,/@keydown.ctrl.enter.prevent="check/);
    assert.match(common,/edited\(i\) \{ this.checked\[i\] = false/);
    assert.doesNotMatch(common,/fetch\(|XMLHttpRequest|localStorage|sessionStorage/);
});
for(const target of targets) for(const fixture of require('../../tools/diagnostics/m40-semantic-fixtures.cjs').semanticFixtures(target))
    test(target.slug+': independent source boundary '+fixture.boundary,()=>{
        const s=state(target);correct(s,fixture.caseIndex);s.setAnswer(fixture.caseIndex,fixture.controlIndex,fixture.invalid);s.check(fixture.caseIndex);
        assert.equal(s.partCorrect(fixture.caseIndex,fixture.controlIndex),false);assert.equal(s.isCorrect(fixture.caseIndex),false);assert.equal(s.score,0);
    });
