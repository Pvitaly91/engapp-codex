'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const {master, expected, assertBody, definitions, assertDatabase, assertHtml} = require('../../tools/diagnostics/seo-m23-fidelity.cjs');

test('immutable author master is the only source of ordered text expectations', () => {
    const data = master();
    definitions(data);
    assert.equal(data.lessons.length, 3);
    for (const lesson of data.lessons) {
        const inventory = expected(lesson);
        assert.equal(inventory.questions.length, 6);
        assert.equal(inventory.keys.length, 6);
        assert.equal(inventory.tables, lesson.key === 'c2-review' ? 0 : 1);
        assert.equal(inventory.headings.length, 8);
        assertBody(lesson, lesson.body_html);
    }
});

test('fidelity rejects missing not, stronger modality, changed number and missing Ukrainian translation', () => {
    const [nominal, , c2] = master().lessons;
    const mutations = [
        [nominal, nominal.body_html.replace(/\bnot\b/, '')],
        [nominal, nominal.body_html.replace(/\bmay\b/, 'will')],
        [c2, c2.body_html.replace('Not all five samples were usable.', 'Not all six samples were usable.')],
        [c2, c2.body_html.replace(' — Не всі п’ять зразків були придатними.', '')],
    ];
    for (const [lesson, changed] of mutations) {
        assert.notEqual(changed, lesson.body_html);
        assert.throws(() => assertBody(lesson, changed), lesson.key + ' learner text mutation');
    }
});

test('fidelity rejects reordered answer keys even when all six still exist', () => {
    for (const lesson of master().lessons) {
        const document = new JSDOM(lesson.body_html).window.document;
        const list = document.querySelector('section[id^="self-check-"] details > ol');
        const [first, second] = list.children;
        list.insertBefore(second, first);
        assert.equal(list.children.length, 6);
        assert.throws(() => assertBody(lesson, document.body.innerHTML), lesson.key + ' reordered keys');
    }
});

test('database and live HTML comparators reject independently mutated content', () => {
    const lesson = master().lessons[0];
    const row = {
        page: {seeder: lesson.seeder, slug: lesson.slug, title: lesson.preserve_page_title, text: lesson.subtitle_text},
        ancestry: lesson.category_path.map(slug => ({slug})),
        blocks: [
            {locale: 'uk', sort_order: 0, body: lesson.subtitle_html},
            {locale: 'uk', sort_order: 1, body: JSON.stringify(lesson.hero)},
            {locale: 'uk', sort_order: 2, heading: lesson.box_heading, body: lesson.body_html},
        ]
    };
    assertDatabase(lesson, row);
    row.blocks[2].body = row.blocks[2].body.replace(/\bmay\b/, 'will');
    assert.throws(() => assertDatabase(lesson, row));
    assert.throws(() => assertHtml(lesson, '<main data-theory-main></main>'));
});
