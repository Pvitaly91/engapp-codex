const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const helpers = read('resources/views/components/saved-test-js-helpers.blade.php');
const revision = 'a'.repeat(64);
const q = {
    uuid: 'naqq-generic-reference-a2-g01', type: '0', activeSlot: 0,
    question: '{a1} Teachers often work after their classes finish.',
    answer_map: { a1: '-' }, markers: ['a1'], answers: ['-'],
    psp_content_revision: revision, naqq_generic_reference_caption_revision: revision,
};

function scope(label = 'Без артикля') {
    const context = vm.createContext({
        EnglishAnswerVariants: require('./load-answer-variants.cjs'),
        testUi: key => key === 'question.no_article' ? label : '',
    });
    vm.runInContext(helpers.slice(helpers.indexOf('function canonicalTestAnswer('), helpers.indexOf('// Use the exact category')), context);
    return context;
}

test('localized omission caption and literal legacy dash grade correctly; empty punctuation does not', () => {
    for (const label of ['Без артикля', 'No article', 'Bez przedimka']) {
        const c = scope(label);
        assert.equal(c.formatAnswerOptionLabel(q, 0, '-'), label);
        assert.equal(c.testAnswerMatches(q, 0, '-'), true);
        assert.equal(c.testAnswerMatches(q, 0, label.toLowerCase()), true);
        for (const wrong of ['', '.', '—', 'the', 'an']) {
            assert.equal(c.testAnswerMatches(q, 0, wrong), false);
        }
        assert.equal(c.testAnswerMatches({ ...q, answers: ['the'] }, 0, label), false);
    }
});

test('only an exact Generic Reference opt-in cleans captions, preserving lexical punctuation', () => {
    const c = scope();
    assert.equal(c.finitePracticeOptionCaption(q, 'Teachers,'), 'Teachers');
    assert.equal(c.finitePracticeOptionCaption(q, "she's well-known."), "she's well-known");
    assert.equal(c.finitePracticeOptionCaption({ ...q, presentation: 'sentence_reorder' }, 'their classes finish.'), 'their classes finish');
    for (const other of [{ ...q, uuid: 'naqq-zero-article-a2-g01' }, { ...q, naqq_generic_reference_caption_revision: 'b'.repeat(64) }]) {
        assert.equal(c.formatAnswerOptionLabel(other, 0, '-'), '-');
        assert.equal(c.finitePracticeOptionCaption(other, 'Teachers,'), 'Teachers,');
        assert.equal(c.testAnswerMatches(other, 0, 'Без артикля'), false);
    }
});

for (const mode of ['card-easy', 'step-easy']) {
    test(`${mode} renders text while submitting the original canonical option`, () => {
        const c = scope();
        const view = read(`resources/views/test-modes/${mode}.blade.php`);
        vm.runInContext(view.slice(view.indexOf('function renderOptionButton('), view.indexOf('function rememberFeedbackAnswer(')), c);
        const args = mode === 'card-easy' ? [q, 0, '-', 0] : [q, '-', 0];
        const html = c.renderOptionButton(...args);
        assert.match(html, /data-opt="-"/);
        assert.match(html, /<span class="flex-1">Без артикля<\/span>/u);
        assert.doesNotMatch(html, /<span class="flex-1">-<\/span>/);
    });
}
