'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fidelity = require('../../tools/diagnostics/seo-m24-fidelity.cjs');

const clone = value => structuredClone(value);
const lessons = fidelity.master().lessons;
const originals = fidelity.old().definitions;
test('M24 immutable master and all native definitions exactly match author payload', () => {
    assert.deepEqual(fidelity.assertSources(), {lessons: 3, nativeBlocks: [8, 6, 4]});
});
function changedBody(lesson, find, replacement) {
    const source = clone(fidelity.definition(lesson));
    const i = lesson.existing_blocks.findIndex(block => JSON.stringify(block.replacement_body_json).includes(find));
    assert.ok(i >= 0, lesson.key + ': missing fixture marker ' + find);
    source.page.blocks[i].body = source.page.blocks[i].body.replace(find, replacement);
    assert.notEqual(source.page.blocks[i].body, fidelity.definition(lesson).page.blocks[i].body);
    return source;
}
for (const [key, before, after] of [
    ['perfect-comparison', 'not', ''],
    ['perfect-comparison', 'four', 'five'],
    ['perfect-comparison', 'two hours', 'three hours'],
    ['narrative-tenses', 'before', 'after'],
    ['b1-mixed-revision', 'might', 'must'],
    ['b1-mixed-revision', 'might', 'will'],
    ['b1-mixed-revision', 'although', 'but']
]) {
    test('M24 refuses altered authored meaning: ' + key + ' ' + before + ' → ' + after, () => {
        const lesson = lessons.find(value => value.key === key);
        const source = changedBody(lesson, before, after);
        assert.throws(() => fidelity.assertDefinition(lesson, originals[lesson.seeder], source));
    });
}
test('M24 refuses a changed twenty-minute duration in the practice box', () => {
    const lesson = lessons[0], source = clone(fidelity.definition(lesson));
    assert.ok(source.page.blocks.at(-1).body.includes('twenty minutes'));
    source.page.blocks.at(-1).body = source.page.blocks.at(-1).body.replace('twenty minutes', 'ten minutes');
    assert.throws(() => fidelity.assertDefinition(lesson, originals[lesson.seeder], source));
});
test('M24 refuses missing Ukrainian translation', () => {
    const lesson = lessons[0], source = clone(fidelity.definition(lesson));
    const i = lesson.existing_blocks.findIndex(block => Array.isArray(block.replacement_body_json.rows));
    assert.ok(i >= 0);
    const body = JSON.parse(source.page.blocks[i].body);
    body.rows[0].ua = '';
    source.page.blocks[i].body = JSON.stringify(body);
    assert.throws(() => fidelity.assertDefinition(lesson, originals[lesson.seeder], source));
});
test('M24 refuses reordered answer key', () => {
    const lesson = lessons[1], source = clone(fidelity.definition(lesson));
    const body = source.page.blocks.at(-1).body;
    const details = body.indexOf('<details');
    const tail = body.slice(details);
    const matches = [...tail.matchAll(/<li\b[^>]*>[\s\S]*?<\/li>/g)];
    assert.ok(matches.length >= 2);
    source.page.blocks.at(-1).body = body.slice(0, details) + tail.replace(matches[0][0], '__M24_FIRST__')
        .replace(matches[1][0], matches[0][0]).replace('__M24_FIRST__', matches[1][0]);
    assert.throws(() => fidelity.assertDefinition(lesson, originals[lesson.seeder], source));
});
test('M24 refuses an omitted answer subpoint', () => {
    const lesson = lessons[2], source = clone(fidelity.definition(lesson));
    const body = source.page.blocks.at(-1).body;
    const details = body.indexOf('<details');
    const tail = body.slice(details);
    source.page.blocks.at(-1).body = body.slice(0, details) + tail.replace(/<li\b[^>]*>[\s\S]*?<\/li>/, '');
    assert.notEqual(source.page.blocks.at(-1).body, body);
    assert.throws(() => fidelity.assertDefinition(lesson, originals[lesson.seeder], source));
});
test('M24 refuses an omitted native block', () => {
    const lesson = lessons[2], source = clone(fidelity.definition(lesson));
    source.page.blocks.splice(1, 1);
    assert.throws(() => fidelity.assertDefinition(lesson, originals[lesson.seeder], source));
});
