'use strict';

// Read-only renderer/matcher contracts for every static task; no working DB or HTTP.
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.join(__dirname, '../..');
const payload = JSON.parse(fs.readFileSync(path.join(root, 'database/content-patches/ppc-practice-quality.v2.json'), 'utf8'));
const source = fs.readFileSync(path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php'), 'utf8');
const context = vm.createContext({window: {EnglishAnswerVariants: require('./load-answer-variants.cjs')}});
const method = (start, end) => source.slice(source.indexOf(start), source.indexOf(end)).trim();
const practice = vm.runInContext(`({${method('normalize(value) {', 'isEmpty(group, index) {')}${method('isCorrect(group, index) {', 'fieldClass(group, index) {')}})`, context);
function correct(item, answer) {
    practice.userAnswer = () => answer;
    practice.item = () => item;
    practice.hasAnswer = () => true;
    practice.acceptedAnswers = () => item.accepted || item.answers || [item.answer];
    return practice.isCorrect('inputs', 0);
}

for (const target of payload.targets) for (const locale of payload.locales) {
    const data = target.body_data[locale];
    for (const group of ['selects', 'choices', 'inputs']) for (const [index, item] of data[group].entries()) {
        test(`${target.slug} ${locale} ${group}-${index + 1}: canonical and explicit accepted responses`, () => {
            assert.ok(item.prompt.trim());
            assert.equal(correct(item, item.answer), true);
            for (const answer of item.accepted || []) assert.equal(correct(item, answer), true);
            if (group !== 'inputs') {
                for (const wrong of (group === 'selects' ? data.options : data.choice_options).filter(v => v !== item.answer)) {
                    assert.equal(correct(item, wrong), false);
                }
            } else {
                assert.equal(correct(item, item.answer.toUpperCase().replace(/[.!?]$/, '')), true);
            }
        });
    }
}

test('Anna is the visible subject; role reversal and state-to-process replacement fail', () => {
    const item = payload.targets[0].body_data.uk.inputs[1];
    assert.match(item.prompt, /Анна знала Лева/);
    assert.match(item.prompt, /Почни речення з By 2020/);
    assert.equal(correct(item, 'By 2020, Anna had known Lev for five years.'), true);
    assert.equal(correct(item, 'By 2020, Lev had known Anna for five years.'), false);
    assert.equal(correct(item, 'By 2020, Anna had been knowing Lev for five years.'), false);
});

test('Full/contracted negatives work, including both explicitly allowed positions of long', () => {
    const [tablet, cafe] = payload.targets[1].body_data.uk.inputs;
    for (const item of [tablet, cafe]) {
        assert.equal(correct(item, item.answer.replace('had not', "hadn't")), true);
        assert.equal(correct(item, item.answer.replace('not ', '')), false);
        assert.equal(correct(item, item.answer.replace('been ', '')), false);
    }
    assert.equal(correct(cafe, "He hadn't been working long in the café when he became the evening manager."), true);
    assert.equal(correct(cafe, 'He had not been working in the café when he became the evening manager.'), false);
});

test('Short answers preserve the required polarity and auxiliary', () => {
    const [yes, no] = payload.targets[2].body_data.uk.selects;
    assert.equal(correct(yes, 'had'), true); assert.equal(correct(yes, "hadn't"), false);
    assert.equal(correct(no, 'had not'), true); assert.equal(correct(no, 'had'), false);
    assert.equal(correct(no, "didn't"), false);
});

test('Each temporal select has one answer, preserving the stated duration/start-point distinction', () => {
    for (const locale of payload.locales) {
        const data = payload.targets[3].body_data[locale];
        assert.match(data.selects[0].label, /____ three hours/);
        assert.match(data.selects[1].label, /____ eight/);
        for (const item of data.selects) assert.equal(data.options.filter(option => correct(item, option)).length, 1);
    }
});

test('All static prompts are finite visible instructions, not a new global word-order matcher', () => {
    for (const target of payload.targets) for (const locale of payload.locales) {
        for (const item of target.body_data[locale].inputs) {
            assert.match(item.prompt, locale === 'uk' ? /Почни/ : locale === 'en' ? /Begin/ : /Zacznij/);
        }
    }
    assert.match(source, /item\['prompt'\]/);
    assert.match(source, /PastPerfectContinuousPracticeQuality::presentation/);
});
