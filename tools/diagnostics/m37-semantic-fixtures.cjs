'use strict';
// Finite mutations of actual accepted M21 input answers, not a universal grammar scorer.
function semanticFixtures(data) {
    const replacements = [
        ['Mila tried to lift the heavy lid', 'Mila failed to lift the heavy lid'],
        ['Mila tried to lift the heavy lid', 'Mila successfully lifted the heavy lid'],
        ['Mila tried to lift the heavy lid', 'Mila tried lifting the heavy lid'],
        ['Mila tried opening the side vent', 'Mila tried to open the side vent'],
        ['to cool the room', 'and successfully cooled the room'],
        ['Mila tried opening', 'Iva tried opening'],
        ['Yesterday we decided to fix the shelf', 'Yesterday we fixed the shelf'],
        ['Yesterday we decided to fix the shelf', 'Yesterday we decided fixing the shelf'],
        ['Yesterday we decided', 'Tomorrow we decided'],
        ['We tried to loosen the screw', 'We failed to loosen the screw'],
        ['We tried to loosen the screw', 'We tried to loosen the screw, so we definitely succeeded'],
        ['We look forward to seeing our helper', 'We look forward to see our helper'],
        ['The folder was damaged', 'The lamp was damaged'],
        ['The folder was damaged', 'The folder was repaired'],
        ['They placed the folder beside the lamp', 'They damaged the folder beside the lamp'],
        ['They placed the folder beside the lamp', 'They placed the lamp beside the folder'],
        ['whose three presses need servicing', 'who’s three presses need servicing'],
        ['whose three presses need servicing', 'whose four presses need servicing'],
        ['whose three presses need servicing', 'whose three presses have been serviced'],
        ['has hired Iva, who maintains them', 'has hired Iva who maintains them'],
        ['has hired Iva, who maintains them', 'has hired Iva, who she maintains them'],
        ['has hired Iva, who maintains them', 'has hired Iva, who maintains'],
        ['Only after the editor had confirmed consent', 'Only after had the editor confirmed consent'],
        ['Only after the editor had confirmed consent', 'Only after the editor confirmed consent'],
        ['Only after the editor had confirmed consent', 'Only before the editor had confirmed consent'],
        ['was the material released', 'did the editor release the material'],
        ['was the material released', 'the material was released'],
        ['was the material released', 'was the material not released'],
        ['than the visitors arrived', 'when the visitors arrived'],
        ['No sooner had the display been installed', 'No sooner was the display installed'],
        ['No sooner had the display been installed', 'No sooner had the architect installed the display'],
        ['Not only the architect but also the caretaker welcomed them', 'Not only the architect but also the caretaker did welcome them'],
        ['The visitors rarely speak loudly', 'The visitors rarely do speak loudly'],
        ['The visitors rarely speak loudly', 'The visitors never speak loudly'],
        ['in this hall', 'in another hall'],
    ];
    return data.inputs.flatMap((item, index) => replacements.filter(([from]) => item.answer.includes(from))
        .map(([from, to]) => [index, item.answer.replace(from, to), from]));
}
module.exports = {semanticFixtures};
