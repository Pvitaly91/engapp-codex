'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const matcher = require('./load-answer-variants.cjs');

const root = path.resolve(__dirname, '../..');
const names = ['BasicsB2', 'FormsAllLevels', 'NegativesAllLevels', 'QuestionsAllLevels', 'TimeExpressionsAllLevels'];
const questions = names.flatMap((name) => JSON.parse(fs.readFileSync(path.join(root,
    `database/seeders/V3/Polyglot/PolyglotPastPerfectContinuous${name}LessonSeeder/definition.json`), 'utf8')).questions);

test('all 336 Sentence Builder targets use the actual browser matcher', () => {
    assert.equal(questions.length, 336);
    let processes = 0;
    let negatives = 0;
    for (const q of questions) {
        assert.equal(matcher.matches(q.target_text, q.target_text), true, q.uuid);
        for (const candidate of matcher.variants(q.target_text)) {
            assert.equal(matcher.matches(q.target_text, candidate), true, `${q.uuid}: ${candidate}`);
        }
        if (/\bbeen [a-z]+ing\b/.test(q.target_text)) {
            processes += 1;
            assert.equal(matcher.matches(q.target_text, q.target_text.replace(/\bbeen\s+/, '')), false, `${q.uuid}: missing been`);
        }
        if (/\bnot\b/.test(q.target_text)) {
            negatives += 1;
            assert.equal(matcher.matches(q.target_text, q.target_text.replace(/\s+\bnot\b/, '')), false, `${q.uuid}: missing not`);
        }
        const tokens = Object.entries(q.answers).sort(([a], [b]) => Number(a.slice(1)) - Number(b.slice(1))).map(([, token]) => token);
        assert.deepEqual(tokens, q.tokens_correct, q.uuid);
        assert.equal(matcher.matches(q.target_text, tokens.join(' ')), true, `${q.uuid}: reconstruct`);
        const available = new Map();
        q.options.forEach((token) => available.set(token, (available.get(token) || 0) + 1));
        tokens.forEach((token) => {
            assert.ok(available.get(token) > 0, `${q.uuid}: missing ${token}`);
            available.set(token, available.get(token) - 1);
        });
    }
    assert.ok(processes > 300);
    assert.ok(negatives > 80);
});

test('contractions preserve inversion and cannot erase polarity or participant roles', () => {
    assert.equal(matcher.matches('Had the team not been preparing?', "Hadn't the team been preparing?"), true);
    assert.equal(matcher.matches('No, they had not.', "No, they hadn't."), true);
    assert.equal(matcher.matches('Yes, I had.', "Yes, I'd."), false);
    assert.equal(matcher.matches('I had been swimming so my hair was wet.', "I'd been swimming so my hair was wet."), true);
    const expected = 'Anna had known Lev for five years by then.';
    assert.equal(matcher.matches(expected, expected), true);
    assert.equal(matcher.matches(expected, 'Lev had known Anna for five years by then.'), false);
    assert.equal(matcher.matches(expected, 'Anna had been knowing Lev for five years by then.'), false);
});
