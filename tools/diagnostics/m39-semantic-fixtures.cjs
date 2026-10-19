'use strict';

// Finite negative mutations of the six exact M23 manual keys. This is not a
// semantic paraphrase scorer and does not add learner-facing teaching material.
function semanticFixtures(data) {
    const replacements = [
        ['The digitisation of the catalogue by the volunteers began in June', 'The digitisation of the catalogue by the volunteers was completed in June'],
        ['began in June', 'began in July'],
        ['by the volunteers', 'by the curator'],
        ['The work is still in progress', 'The work has been completed'],
        ['The work is still in progress', 'The work has not begun'],
        ['The team has proposed an expansion of the hall', 'The team has completed an expansion of the hall'],
        ['The team has proposed an expansion of the hall', 'The team has successfully implemented an expansion of the hall'],
        ['No decision has been made', 'A decision has been made'],
        ['the new waiting times have not been measured', 'the new waiting times have been reduced'],
        ['the new waiting times have not been measured', 'the new waiting times have been measured'],
        ['Leila may have sent the draft yesterday', 'Leila sent the draft yesterday'],
        ['Leila may have sent the draft yesterday', 'Leila must have sent the draft yesterday'],
        ['Leila may have sent the draft yesterday', 'Leila may have sent the draft today'],
        ['Leila may have sent', 'Marta may have sent'],
        ['we have not checked the mailbox', 'we have checked the mailbox'],
        ['The dates in the catalogue were checked yesterday', 'The entire catalogue was checked yesterday'],
        ['The dates in the catalogue were checked yesterday', 'The dates in the catalogue were checked today'],
        ['The descriptions have not yet been checked', 'Every description is certainly correct'],
        ['The descriptions have not yet been checked', 'The descriptions have been checked'],
        ['Could you send the final file by Friday?', 'You could have sent the final file by Friday.'],
        ['by Friday', 'by Monday'],
        ['The register includes every member', 'The register is missing two members'],
        ['two entries have no phone number', 'three entries have no phone number'],
        ['two entries have no phone number', 'two members are missing'],
        ['These incomplete entries need to be updated', 'Every entry needs to be updated'],
        ['These incomplete entries need to be updated', 'These incomplete entries have been updated'],
        ['Anika’s indoor test of the first prototype on Monday was successful', 'Anika’s outdoor test of both prototypes on Monday was successful'],
        ['on Monday', 'on Tuesday'],
        ['Anika’s indoor test', 'Marta’s indoor test'],
        ['The second prototype has not been tested', 'The second prototype has failed the test'],
        ['The second prototype has not been tested', 'The second prototype is defective'],
        ['the first prototype may also work outdoors', 'both prototypes have been proven reliable outdoors'],
        ['the first prototype may also work outdoors', 'the first prototype will work outdoors'],
        ['no outdoor test has taken place', 'an outdoor test has taken place'],
        ['no outdoor test has taken place', 'good performance in every setting is guaranteed'],
    ];
    return data.inputs.flatMap((item, index) => replacements.filter(([from]) => item.answer.includes(from))
        .map(([from, to]) => [index, item.answer.replace(from, to), from]));
}

// Reserved for the existing supplemental-control contract. M39 currently keeps
// source semantic subparts in full select/choice keys, not invented input parts.
function semanticCheckFixtures(data) {
    return data.inputs.flatMap((item, inputIndex) => (item.m39_semantic_checks || []).flatMap((check, checkIndex) =>
        (check.options || []).filter(option => option !== check.answer)
            .map(option => [inputIndex, checkIndex, option])));
}

module.exports = {semanticFixtures, semanticCheckFixtures};
