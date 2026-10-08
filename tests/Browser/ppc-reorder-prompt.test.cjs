const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.join(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const helpers = read('resources/views/components/saved-test-js-helpers.blade.php');
const script = read('resources/views/components/sentence-reorder-question-js.blade.php').match(/<script>([\s\S]*?)<\/script>/)[1];
const functionSource = name => {
    const source = helpers.slice(helpers.indexOf(`function ${name}(`));
    return source.slice(0, source.search(/^\}/m) + 1);
};
function fixture(locale = 'uk', tense = 'Tenses / Past Perfect Continuous') {
    const context = vm.createContext({TEST_LOCALE: locale,
        EnglishAnswerVariants: require('./load-answer-variants.cjs'), testUi: key => key});
    vm.runInContext(functionSource('html') + '\n' + functionSource('isPastPerfectContinuousQuestion') + '\n' + script, context);
    const q = {tense, presentation: 'sentence_reorder', question: 'Ben was out of breath because he {a1} uphill.',
        reorder_source_question: 'Ben was out of breath because he {a1} uphill.', reorder_template_constraint: true,
        reorder_answer: 'Ben was out of breath because he had been running uphill.',
        reorder_tokens: ['running uphill.', 'he had been', 'of breath because', 'Ben was out'], done: false};
    return {q, context, render: () => context.renderSentenceReorderQuestion(q),
        action: (action, data = {}) => context.applySentenceReorderAction(q, {dataset: {reorderAction: action, ...data}})};
}
for (const locale of ['uk', 'ua', 'en', 'pl']) {
    test(`${locale}: even an old template flag cannot reveal PPC word order`, () => {
        const f = fixture(locale), before = JSON.stringify(f.q), html = f.render();
        assert.equal(html.includes('data-reorder-template-constraint'), false);
        assert.equal(html.includes('Ben was out of breath because he ___ uphill.'), false);
        assert.equal(html.includes('data-reorder-action="check"'), true);
        assert.equal(html.includes('data-reorder-action="clear"'), true);
        assert.equal(html.includes('data-reorder-token-index="0"'), true);
        const original = JSON.parse(before);
        assert.deepEqual(Array.from(f.q.reorder_tokens), original.reorder_tokens);
        assert.equal(f.q.reorder_answer, original.reorder_answer);
    });
}
test('other categories retain their explicit template instruction', () => {
    assert.equal(fixture('uk', 'Tenses / Present Perfect Continuous').render().includes('data-reorder-template-constraint'), true);
    assert.equal(fixture('uk', 'Past Perfect vs Past Perfect Continuous').render().includes('data-reorder-template-constraint'), true);
});
test('correct PPC order still passes with the hidden template; wrong order fails', () => {
    const correct = fixture();
    for (const token of [3, 2, 1, 0]) correct.action('add', {reorderTokenIndex: String(token)});
    assert.equal(correct.action('check'), 'checked');
    assert.equal(correct.q.feedback, 'correct');
    assert.equal(correct.q.done, true);
    const wrong = fixture();
    for (const token of [0, 1, 2, 3]) wrong.action('add', {reorderTokenIndex: String(token)});
    assert.equal(wrong.action('check'), 'checked');
    assert.equal(wrong.q.feedback, 'status.incorrect');
    assert.equal(wrong.q.wrongAttempt, true);
});
test('PPC bank clear and token removal remain usable without an order hint', () => {
    const f = fixture();
    f.action('add', {reorderTokenIndex: '3'});
    assert.equal(f.action('remove', {reorderPosition: '0'}), true);
    assert.equal(f.q.reorderChosen.length, 0);
    f.action('add', {reorderTokenIndex: '1'});
    assert.equal(f.action('clear'), true);
    assert.equal(f.q.reorderChosen.length, 0);
    assert.equal(f.q.reorderUsed.length, 0);
});
