'use strict';
// Pure VM/DOM/source tests. No Laravel, .env, HTTP, browser, or working DB.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {JSDOM} = require('jsdom');
const {targets, assertOptionOnly} = require('../../tools/diagnostics/seo-m39-practice-ui.cjs');
const ROOT = path.resolve(__dirname, '../..');
const javascript = fs.readFileSync(path.join(ROOT, 'public/js/m39-practice-ui.js'), 'utf8');
const partial = fs.readFileSync(path.join(ROOT, 'resources/views/engram/theory/blocks-v3/m39-practice-ui.blade.php'), 'utf8');
const scope = {EnglishAnswerVariants: require('./load-answer-variants.cjs')};
vm.runInNewContext(javascript, scope);
const factory = scope.m39PracticeUi;
const plain = value => JSON.parse(JSON.stringify(value));
// Finite short response fragments, independently read from the original M23
// questions/keys; this is semantic ownership, not an arbitrary payload limit.
const expectedCandidates = {
    'n1-form': ['is', 'are'], 'n1-head': ['expansion', 'rooms'],
    'n3-equivalence': ['Ні.', 'Так.'],
    'n3-added-facts': ['час уже скоротився', 'обслуговування поліпшилося', 'лише план'],
    'c2-3-b': ['факт друку не заданий', 'надрукувала', 'не надрукувала'],
    'c2-4-guaranteed': ['кожний непридатний', 'принаймні один непридатний', 'рівно один непридатний'],
};
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
test('Finite author ownership: 18 original cases, truthful interaction types and 23 controls', () => {
    assert.equal(targets.length, 3);
    const kinds = targets.flatMap(t => t.data.cases.flatMap(task => task.controls.map(c => c.kind)));
    assert.equal(kinds.length, 23); assert.equal(kinds.filter(k => k === 'manual').length, 17);
    assert.equal(kinds.filter(k => k === 'select').length, 1);
    assert.equal(kinds.filter(k => k === 'choice').length, 4);
    assert.equal(kinds.filter(k => k === 'multi').length, 1);
    for (const t of targets) assert.deepEqual(t.data.cases.map(task => task.source_index), [1,2,3,4,5,6]);
    assert.ok(targets[1].data.cases.every(task => task.interaction === 'manual'), 'Six C1 transformations stay manual');
    assert.equal(targets[2].data.cases[0].interaction, 'manual'); assert.equal(targets[2].data.cases[1].interaction, 'manual');
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
test('Marta keeps needn’t/need not A and unknown execution B; no partial case passes', () => {
    const target = targets[2], s = state(target), i = 2;
    s.setAnswer(i, 0, 'Marta need not have printed a second timetable.'); assert.equal(s.partCorrect(i, 0), true);
    assert.equal(s.isCorrect(i), false);
    s.setAnswer(i, 1, 'факт друку не заданий'); assert.equal(s.isCorrect(i), true);
    for (const value of ['надрукувала', 'не надрукувала']) {s.setAnswer(i, 1, value); assert.equal(s.isCorrect(i), false);}
    s.setAnswer(i, 0, 'Marta didn’t need to print a second timetable.'); assert.equal(s.partCorrect(i, 0), false);
});
test('Not all requires the guaranteed choice and the unknown distribution, not an exact-one fiction', () => {
    const s = state(targets[2]), i = 3; correct(s, i); assert.equal(s.isCorrect(i), true);
    s.setAnswer(i, 1, 'Рівно один зразок був непридатним.'); assert.equal(s.isCorrect(i), false);
    s.setAnswer(i, 0, 'кожний непридатний'); assert.equal(s.partCorrect(i, 0), false);
});
test('C2 explicit Had-subject-not instruction rejects inverted contractions while allowing could-have contraction', () => {
    const s = state(targets[2]);
    for (const value of [
        "Hadn't the guide brought a spare lamp, we could have been stranded underground.",
        'Had not the guide brought a spare lamp, we could have been stranded underground.',
    ]) {s.setAnswer(0, 0, value); assert.equal(s.partCorrect(0, 0), false);}
    s.setAnswer(0, 0, "Had the guide not brought a spare lamp, we could've been stranded underground.");
    assert.equal(s.partCorrect(0, 0), true);
});
for (const [owner, taskIndex, from, to] of [
    [0,1,'may take place','will take place'], [0,4,'began in June','was completed in June'],
    [0,5,'No decision has been made','A decision has been made'],
    [1,0,'would be able','would have been able'], [1,2,'is believed','is known'],
    [1,3,'guides who have completed the course','guides, who have completed the course,'],
    [1,4,'may have sent','must have sent'], [1,5,'have not yet been checked','are certainly correct'],
    [2,0,'could have been','would have been'], [2,1,'are thought','are known'],
    [2,4,'two entries','three entries'], [2,5,'may also work outdoors','will work outdoors'],
]) test('Actual authored boundary: ' + from, () => {
    const target = targets[owner], control = target.data.cases[taskIndex].controls[0], s = state(target);
    assert.ok(control.answer.includes(from)); s.setAnswer(taskIndex, 0, control.answer.replace(from, to));
    assert.equal(s.partCorrect(taskIndex, 0), false);
});
for (const taskIndex of [0,1]) test('Regression fixture: C2 sentence ' + (taskIndex + 1) + ' remains answer-only; key is separately revealed', () => {
    const target = targets[2], task = target.data.cases[taskIndex], answer = task.controls[0].answer;
    const key = target.data.author_self_check.answers[taskIndex];
    assertOptionOnly(answer, answer, key);
    assert.throws(() => assertOptionOnly(answer + ' ' + key, answer, key));
    const dom = new JSDOM('<button data-option></button><details data-explanation><summary>Відповіді та пояснення</summary><div>' + key + '</div></details>');
    const button = dom.window.document.querySelector('button'); button.textContent = answer;
    const details = dom.window.document.querySelector('details'); assert.equal(details.open, false);
    assert.equal(button.textContent, answer); assert.notEqual(button.textContent, details.textContent.trim());
    details.open = true; assert.equal(details.open, true);
    assert.ok(details.textContent.includes(' — ')); assert.equal(button.textContent, answer);
    dom.window.close();
});
test('M39-only partial gates full keys after check/reveal and never uppercases answer surfaces', () => {
    assert.match(partial, /:open="checked\[\{\{ \$i \}\}\]"/);
    assert.match(partial, /summary x-show="checked/);
    assert.match(partial, /data-m39-self-check-answers/); assert.match(partial, /data-m39-answer-input/);
    assert.doesNotMatch(partial, /\buppercase\b|strtoupper/); assert.doesNotMatch(javascript, /toUpperCase/);
    assert.match(partial, /autocomplete="off"/); assert.match(partial, /@keydown.ctrl.enter.prevent="check/);
    assert.match(javascript, /edited\(i\) \{ this.checked\[i\] = false/);
    assert.doesNotMatch(javascript, /fetch\(|XMLHttpRequest|localStorage|sessionStorage/);
});
