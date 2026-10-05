'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {sourceData, canBuild, evidenceNames, key, BASE, OUTPUT, run} = require('../../tools/diagnostics/ppc-quality-theory-browser.cjs');

test('finite live theory harness covers four pages and three locales without HTTP', () => {
    const source = sourceData();
    assert.equal(source.targets.length, 12);
    assert.equal(new Set(source.targets.map(target => target.slug)).size, 4);
    for (const locale of ['uk', 'en', 'pl']) {
        const targets = source.targets.filter(target => target.locale === locale);
        assert.equal(targets.length, 4);
        assert.equal(targets.reduce((n, target) => n + target.data.selects.length + target.data.choices.length + target.data.inputs.length, 0), 24);
        assert.ok(targets.every(target => new URL(BASE + target.path).origin === BASE));
    }
    assert.equal(source.targets.reduce((n, target) => n + target.expectedPoints.length, 0), 48);
    assert.equal(source.targets.filter(target => target.locale !== 'uk').reduce((n, target) => n + target.expectedPoints.length, 0), 0);
    assert.ok(source.targets.every(target => target.bank.length === 72));
    const anna = source.targets.find(target => target.locale === 'uk' && target.slug.endsWith('-forms')).data.inputs[1];
    assert.equal(anna.answer, 'By 2020, Anna had known Lev for five years.');
    assert.match(anna.prompt, /Анна знала Лева/u);
});

test('token feasibility preserves occurrence counts and negative tokens', () => {
    assert.ok(canBuild(['had', 'not', 'been', 'working'], ['working', 'not', 'had', 'been']));
    assert.equal(canBuild(['had', 'not', 'been', 'working'], ['working', 'had', 'been']), false);
    assert.equal(canBuild(['had', 'had'], ['had']), false);
    assert.ok(canBuild(["hadn't", 'been'], ["hadn’t", 'been']));
    assert.equal(key('Anna had known Lev.'), 'anna had known lev');
});

test('finite first-five linked pools exercise contractions only where tokens actually offer them', () => {
    const matcher = require('./load-answer-variants.cjs');
    for (const target of sourceData().targets.filter(item => item.locale === 'uk')) {
        const firstFive = [...target.bank].sort((a, b) => a.uuid.localeCompare(b.uuid)).slice(0, 5);
        const eligible = firstFive.filter(question => matcher.variants(question.target_text)
            .filter(answer => /['’]d\b|hadn['’]t/iu.test(answer))
            .some(answer => canBuild(answer.replace(/[.!?]+$/u, '').split(/\s+/u), question.options)));
        assert.equal(eligible.length, target.slug.endsWith('-negatives') ? 5 : 0);
    }
});

test('live invocation cannot start before an explicit finite local-apply flag', async () => {
    await assert.rejects(run(OUTPUT, 'offline-guard', undefined), /Explicit post-apply acceptance flag/u);
    await assert.rejects(run('D:/unrelated', 'offline-guard', '--local-applied'), /Private evidence stays/u);
});

test('fresh evidence label covers every report and public screenshot before any browser launch', () => {
    const filenames = evidenceNames(sourceData().targets, 'offline-unique');
    assert.equal(filenames.length, 25);
    assert.equal(new Set(filenames).size, 25);
    assert.ok(filenames.every(filename => !filename.includes('/') && !filename.includes('\\')));
    assert.equal(filenames.filter(filename => filename.endsWith('.json')).length, 1);
    assert.equal(filenames.filter(filename => filename.endsWith('.png')).length, 24);
});

test('opt-in punctuation uses the authored question while legacy fallback stays unchanged', () => {
    const view = fs.readFileSync(path.resolve(__dirname, '../../resources/views/components/text-block-practice-questions.blade.php'), 'utf8');
    const match = view.match(/composePunctuation\(text\)\s*\{([\s\S]*?)\n\s*\},\s*\n\s*normalizeAnswer/u);
    assert.ok(match);
    const punctuation = new Function('text', match[1]);
    const englishInstruction = 'Ask whether the studying had continued until the teacher arrived.';
    assert.equal(punctuation.call({currentQuestion: {question: englishInstruction, authored_compose: true, compose_punctuation: '?'}}, 'Had she been studying'), '?');
    assert.equal(punctuation.call({currentQuestion: {question: englishInstruction, authored_compose: false}}, 'Had she been studying'), '.');
    assert.equal(punctuation.call({currentQuestion: {question: englishInstruction, authored_compose: true, compose_punctuation: '?'}}, 'Had she been studying?'), '');
});
