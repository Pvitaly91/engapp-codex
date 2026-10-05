'use strict';
// Finite mutations of actual M22 manual keys, not a universal paraphrase/grammar scorer.
function semanticFixtures(data) {
    const replacements = [
        ['There is not much evidence for this explanation', 'There is no evidence for this explanation'],
        ['There is not much evidence for this explanation', 'The explanation is false'],
        ['not much evidence', 'not many evidences'],
        ['not much evidence', 'exactly three pieces of evidence'],
        ['a little useful information', 'a few useful information'],
        ['a little useful information', 'no useful information'],
        ['a little useful information', 'five pieces of useful information'],
        ['It is not enough for a complete catalogue', 'It is enough for a complete catalogue'],
        ['It is not enough for a complete catalogue', 'The catalogue is complete'],
        ['Not both lamps work', 'Neither lamp works'],
        ['Not both lamps work', 'Both lamps work'],
        ['Not both lamps work', 'Exactly one lamp works'],
        ['Not both lamps work', 'The second lamp works'],
        ['Not both lamps work', 'Not both lamp works'],
        ['Six folders were placed in the cabinet', 'Five folders were placed in the cabinet'],
        ['Two of the six folders were checked', 'All six folders were checked'],
        ['Two of the six folders were checked', 'Two of the six folders were not checked'],
        ['Both of the checked folders were dry', 'All six folders were dry'],
        ['Both of the checked folders were dry', 'Both of the checked folders were safe'],
        ['Both of the checked folders were dry', 'The four unchecked folders were dry'],
        ['a small difference', 'a substantial gap'],
        ['a small difference', 'a statistically significant difference'],
        ['The team faces a challenge', 'The team poses a challenge'],
        ['The team faces a challenge', 'The team finds it impossible'],
        ['The team faces a challenge', 'The cramped store faces a challenge'],
        ['The note raises a question about storage', 'The note answers all storage questions'],
        ['raises a question about storage', 'raises two questions about storage'],
        ['but does not answer it', 'and answers it'],
        ['One dated invoice', 'Two dated invoices'],
        ['One dated invoice', 'One undocumented observation'],
        ['documentary evidence of the purchase', 'complete proof of the object’s entire history'],
        ['not a complete account of the object’s history', 'a complete account of the object’s history'],
        ['not a complete account of the object’s history', 'proof that the proposal has been approved'],
        ['in using the cramped store', 'because the cramped store damaged the goods'],
    ];
    return data.inputs.flatMap((item, index) => replacements.filter(([from]) => item.answer.includes(from))
        .map(([from, to]) => [index, item.answer.replace(from, to), from]));
}

// The separate statistical subpart of case 5 must also reject wrong choices.
function semanticCheckFixtures(data) {
    return data.inputs.flatMap((item, inputIndex) => (item.m38_semantic_checks || []).flatMap((check, checkIndex) =>
        (check.options || []).filter(option => option !== check.answer)
            .map(option => [inputIndex, checkIndex, option])));
}
module.exports = {semanticFixtures, semanticCheckFixtures};
