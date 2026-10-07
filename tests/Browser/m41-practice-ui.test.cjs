'use strict';
// Pure VM/DOM/source tests. No Laravel, .env, HTTP, browser, or working DB.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {JSDOM} = require('jsdom');
const {targets, assertOptionOnly} = require('../../tools/diagnostics/seo-m41-local.cjs');
const ROOT = path.resolve(__dirname, '../..');
const common = fs.readFileSync(path.join(ROOT, 'public/js/authored-practice-ui.js'), 'utf8');
const commonPartial = fs.readFileSync(path.join(ROOT, 'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php'), 'utf8');
const javascript = fs.readFileSync(path.join(ROOT, 'public/js/m41-practice-ui.js'), 'utf8');
const partial = fs.readFileSync(path.join(ROOT, 'resources/views/engram/theory/blocks-v3/m41-practice-ui.blade.php'), 'utf8');
const scope = {EnglishAnswerVariants: require('./load-answer-variants.cjs')};
vm.runInNewContext(common, scope); vm.runInNewContext(javascript, scope);
const factory = scope.m41PracticeUi;
const plain = value => JSON.parse(JSON.stringify(value));
// Finite short response fragments, independently read from the original M41
// questions/keys; this is semantic ownership, not an arbitrary payload limit.
const expectedCandidates = require('../../tools/diagnostics/seo-m41-local.cjs').answerFragments;
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
test('Finite approved M41:18tasks14compound32controls9select8choice15manual20explicitaliases',()=>{
    const tasks=targets.flatMap(target=>target.data.cases),controls=tasks.flatMap(task=>task.controls);
    assert.equal(targets.length,3);assert.equal(tasks.length,18);assert.equal(tasks.filter(task=>task.interaction==='compound').length,14);
    assert.equal(controls.length,32);for(const [kind,count]of [['select',9],['choice',8],['manual',15]])assert.equal(controls.filter(control=>control.kind===kind).length,count);
    assert.equal(controls.filter(control=>control.kind==='manual').reduce((n,control)=>n+control.accepted.length,0),20);
    for(const target of targets)assert.deepEqual(target.data.cases.map(task=>task.source_index),[1,2,3,4,5,6]);
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
                        s.setAnswer(i, p, alias.replace(/'/gu,'’')); assert.equal(s.partCorrect(i, p), true,'Only typography changes inside an explicitly accepted variant');
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
                    for (const option of control.options) assertOptionOnly(option.label, option.label, key);
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

test('M41 wrapper is independent and common renderer preserves exact post-check keys/natural answer casing', () => {
    assert.match(javascript, /data-m41-answer/); assert.doesNotMatch(javascript,/c2-1-answer|m39PracticeUi|m40PracticeUi/);assert.match(javascript,/explicitManualVariants: true/);
    assert.match(commonPartial, /:open="checked/); assert.match(commonPartial,/summary x-show="checked/);
    assert.match(commonPartial,/self-check-answers/); assert.match(commonPartial,/answer-input/);
    assert.doesNotMatch(commonPartial,/\buppercase\b|strtoupper/); assert.doesNotMatch(common,/toUpperCase/);
    assert.match(commonPartial,/autocomplete="off"/); assert.match(commonPartial,/@keydown.ctrl.enter.prevent="check/);
    assert.match(common,/edited\(i\) \{ this.checked\[i\] = false/);
    assert.doesNotMatch(common,/fetch\(|XMLHttpRequest|localStorage|sessionStorage/);
});
for(const target of targets) for(const fixture of require('../../tools/diagnostics/m41-semantic-fixtures.cjs').semanticFixtures(target))
    test(target.slug+': independent source boundary '+fixture.boundary,()=>{
        const s=state(target);correct(s,fixture.caseIndex);s.setAnswer(fixture.caseIndex,fixture.controlIndex,fixture.invalid);s.check(fixture.caseIndex);
        assert.equal(s.partCorrect(fixture.caseIndex,fixture.controlIndex),false);assert.equal(s.isCorrect(fixture.caseIndex),false);assert.equal(s.score,0);
    });


test('Explicit M41 aliases do not leak legacy contraction expansion; M39/M40 default stays unchanged',()=>{
    const target=targets[2],s=state(target),i=3,p=0;
    for(const value of ["She's written the note.","She’s written the note.","She's written the note"]){
        s.setAnswer(i,p,value);assert.equal(s.partCorrect(i,p),false);
    }
    const legacy=scope.authoredPracticeUi({cases:target.data.cases});
    legacy.setAnswer(i,p,"She's written the note.");assert.equal(legacy.partCorrect(i,p),true);
});
test('Master fields are preserved, including source values distinct from labels and exact blank stimuli',()=>{
    const {master}=require('../../tools/diagnostics/seo-m41-local.cjs');
    for(const [owner,target]of targets.entries())for(const [i,task]of target.data.cases.entries()){
        const original=master.lessons[owner].practice[i];
        assert.equal(task.id,original.id);assert.equal(task.scoring,original.scoring);
        for(const [p,control]of task.controls.entries()){
            const source=original.controls[p];assert.equal(control.id,source.id);assert.equal(control.kind,source.kind);assert.equal(control.label,source.label_uk);
            assert.equal(control.required,true);assert.equal(control.stimulus_en,source.stimulus_en);
            if(control.kind==='manual'){assert.equal(control.answer,source.canonical_answer);assert.deepEqual(control.accepted,source.accepted_answers);assert.deepEqual(control.tokens,source.tokens);}
            else{assert.deepEqual(control.options,source.options);assert.equal(control.answer,source.correct_value);}
        }
    }
});
test('M41 and explicit M43 no-JS token fallbacks are finite; no legacy owner gets additional token markup',()=>{
    // M43 reuses this component without widening the legacy scopes. Keep the
    // exact two-member, strict allowlist and the entire token loop protected.
    const fallback=commonPartial.match(/@if\(in_array\(\$practiceScope, \['m41', 'm43'\], true\)\)([\s\S]*?)@endif/g);
    assert.equal(fallback?.length,1);
    assert.match(fallback[0],/<noscript><div[^>]+data-\{\{ \$practiceScope \}\}-static-token-bank/);
    assert.match(fallback[0],/data-\{\{ \$practiceScope \}\}-static-token>\{\{ \$token \}\}/);
    assert.match(fallback[0],/@foreach\(\$control\['tokens'\] as \$token\)/);
    assert.equal((commonPartial.match(/-static-token-bank/g)||[]).length,1);
});
