'use strict';
// Mutations of actual accepted input answers, not absent-string or invented fixtures.
function semanticFixtures(data) {
    const replacements = [
        ['The halls are believed', 'The halls is believed'],
        ['It is believed that the halls are empty', 'It is believed the halls to be empty'],
        ['was reported by the coordinator', 'is known'],
        ['to have arrived', 'to be arriving'], ['at four on Thursday', 'by four on Thursday'],
        ['on Thursday', 'on Friday'], ['has not been independently confirmed', 'has been independently confirmed'],
        ['causative у Past Simple', 'causative у Past Perfect'],
        ['Лев сам пофарбував', 'Маляр сам пофарбував'],
        ['had + his shelf + painted', 'had + painted + his shelf'],
        ['I have booked my scanner in for repair', 'I had my scanner repaired'],
        ['It is expected to be ready', 'It will definitely be ready'],
        ['on Thursday', 'on Tuesday'],
        ['two decorators are repainting', 'three decorators are repainting'],
        ['two decorators are repainting', 'two decorators have repainted'],
        ['Today’s update reports', 'Yesterday’s update reports'],
        ['the organiser reported', 'the printing company confirmed'],
        ['had printed forty programmes', 'had delivered forty programmes'],
        ['on Sunday', 'on Monday'],
        ['has not been independently checked', 'has been independently checked'],
        ['there is no information about delivery', 'the programmes were not delivered'],
    ];
    return data.inputs.flatMap((item, index) => replacements.filter(([from]) => item.answer.includes(from))
        .map(([from, to]) => [index, item.answer.replace(from, to), from]));
}
module.exports = {semanticFixtures};
