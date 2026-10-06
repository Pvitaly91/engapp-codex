'use strict';

// Additional finite mutations of ACTUAL manual control answers, supplementing
// the 31 already covered in m40-practice-ui.test.cjs. No new learner examples.
function semanticFixtures(target) {
    const data = target.data || target;
    const rules = [
        ['m40-p3-answer', 'I have known', 'We have known'],
        ['m40-p3-answer', 'the password', 'the username'],
        ['m40-p3-answer', 'since Monday', 'for Monday'],
        ['m40-p3-answer', 'have known', 'have not known'],
        ['m40-p6-answer', 'have already translated', 'have not translated'],
        ['m40-p6-answer', 'the second chapter', 'the third chapter'],
        ['m40-p6-answer', 'I have already', 'We have already'],
        ['m40-p6-answer', 'for two hours', 'since two hours'],
        ['m40-n1-answer', 'Yesterday', 'Tomorrow'],
        ['m40-n1-answer', 'the gate', 'the door'],
        ['m40-n1-answer', 'opened the window', 'closed the window'],
        ['m40-n1-answer', 'Yesterday I', 'Yesterday we'],
        ['m40-n3-answer', 'the courier', 'the caretaker'],
        ['m40-n3-answer', 'had already left', 'had not left'],
        ['m40-n3-answer', 'When we arrived', 'Before we arrived'],
        ['m40-n3-answer', 'had already left', 'had been leaving'],
        ['m40-n5-question', 'Did she', 'Did he'],
        ['m40-n5-question', 'the box', 'the window'],
        ['m40-n5-guide', 'before I called', 'after I called'],
        ['m40-n5-guide', 'gone home', 'gone to the museum'],
        ['m40-n6-answer', 'Olena', 'Marta'],
        ['m40-n6-answer', 'on the phone', 'on the radio'],
        ['m40-n6-answer', 'the cupboard', 'the window'],
        ['m40-n6-answer', 'before I arrived', 'because I arrived'],
        ['m40-b3-question', 'Mila', 'Marta'],
        ['m40-b3-question', 'asked me', 'told me'],
        ['m40-b3-instruction', 'the parcel', 'the door'],
        ['m40-b3-instruction', 'She told me', 'I told her'],
        ['m40-b4-passive', 'must be labelled', 'may be labelled'],
        ['m40-b4-passive', 'by the volunteers', 'by the mechanic'],
        ['m40-b4-causative', 'my bicycle', 'my car'],
        ['m40-b4-causative', 'yesterday', 'tomorrow'],
        ['m40-b5-relative', 'Marta,', 'Mila,'],
        ['m40-b5-relative', 'lives nearby', 'lives far away'],
        ['m40-b5-although', 'Although', 'Because'],
        ['m40-b5-although', 'we continued', 'we stopped'],
        ['m40-b6-wish', 'a bigger desk', 'a smaller desk'],
        ['m40-b6-wish', 'I wish I had', 'I have'],
        ['m40-b6-conditional', 'left earlier', 'left later'],
        ['m40-b6-conditional', 'would have caught', 'might have caught'],
        ['m40-b6-might', 'Lena', 'Marta'],
        ['m40-b6-might', 'the reading room', 'the workshop'],
    ];
    const controls = data.cases.flatMap((task, caseIndex) => task.controls.map((control, controlIndex) =>
        ({control, caseIndex, controlIndex})));
    return rules.flatMap(([id, from, to]) => {
        const found = controls.find(entry => entry.control.id === id);
        if (!found) return [];
        if (!found.control.answer.includes(from)) throw new Error('M40 negative fixture source is absent: ' + id + ' / ' + from);
        const invalid = found.control.answer.replace(from, to);
        if ((found.control.accepted || []).includes(invalid)) throw new Error('M40 negative fixture equals an accepted alias: ' + id);
        return [{caseIndex: found.caseIndex, controlIndex: found.controlIndex, invalid, boundary: from}];
    });
}

module.exports = {semanticFixtures};
