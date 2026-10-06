'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require('jsdom');
const {contentFidelity, compare, routes, BASE} = require('../../tools/diagnostics/capture-m41-design-http.cjs');
const {targetPaths} = require('../../tools/diagnostics/capture-m41-http.cjs');
const master = JSON.parse(fs.readFileSync(path.resolve(__dirname, '../../docs/content/m41-authored-tense-comparisons.v1.0.1.json'), 'utf8'));
const esc = x => String(x).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('"', '&quot;');
function prose(point) {
    return '<h3>' + esc(point.title) + '</h3>' + point.paragraphs_uk.map(p => '<p>' + esc(p) + '</p>').join('')
        + point.examples.map(e => '<p lang="en">' + esc(e.en) + '</p><p lang="uk">' + esc(e.uk) + '</p>').join('');
}
function fixture(lesson) {
    const html = lesson.sections.map(section => '<section data-m41-author-section="' + section.id + '"><h2>' + esc(section.title) + '</h2>'
        + (section.table ? '<table><thead><tr>' + section.table.columns.map(c => '<th>' + esc(c) + '</th>').join('') + '</tr></thead><tbody>'
            + section.table.rows.map(row => '<tr>' + row.map(c => '<td>' + esc(c).replaceAll('\n', '<br>') + '</td>').join('') + '</tr>').join('') + '</tbody></table>' : '')
        + section.points.map(point => '<article id="' + point.id + '">' + prose(point)
            + (point.detail ? '<div data-theory-native-extension><details><summary>Докладніше</summary><div class="theory-point-fragment">'
                + prose(point.detail) + '</div></details></div>' : '') + '</article>').join('') + '</section>').join('')
        + lesson.practice.map(t => '<article data-m41-ui-case="' + t.id + '">' + t.controls.map(c => '<div data-m41-control="' + c.id + '"></div>').join('') + '</article>').join('');
    return new JSDOM(html);
}
for (const [index, lesson] of master.lessons.entries()) {
    test('all exact author points and BR-separated bilingual table cells ' + lesson.theory_path, () => {
        const dom = fixture(lesson);
        try {
            const result = contentFidelity(dom.window.document, lesson);
            assert.equal(result.details, [4, 5, 5][index]); assert.equal(result.tasks, 6);
            assert.equal(result.controls, lesson.practice.flatMap(t => t.controls).length);
        } finally {dom.window.close();}
    });
}
test('a removed basic example is rejected rather than accepted as a style change', () => {
    const lesson = master.lessons[0], dom = fixture(lesson);
    try {dom.window.document.querySelector('[lang="en"]').remove(); assert.throws(() => contentFidelity(dom.window.document, lesson));}
    finally {dom.window.close();}
});
test('an omitted source table value is rejected', () => {
    const lesson = master.lessons[0], dom = fixture(lesson);
    try {dom.window.document.querySelector('tbody td').textContent = 'changed'; assert.throws(() => contentFidelity(dom.window.document, lesson));}
    finally {dom.window.close();}
});
test('duplicate author detail is rejected', () => {
    const lesson = master.lessons[0], dom = fixture(lesson);
    try {const d = dom.window.document.querySelector('[data-theory-native-extension]'); d.after(d.cloneNode(true)); assert.throws(() => contentFidelity(dom.window.document, lesson));}
    finally {dom.window.close();}
});
function nativeFormsFixture(lesson) {
    const dom = fixture(lesson);
    for (const section of lesson.sections.filter(s => s.table)) {
        const root = dom.window.document.querySelector('[data-m41-author-section="' + section.id + '"]');
        root.querySelector('table').outerHTML = '<div data-m41-native-component="forms-grid">'
            + section.table.columns.map((c, i) => '<span data-m41-form-column="' + i + '">' + esc(c) + '</span>').join('') + '</div>'
            + section.table.rows.map((row, i) => '<div data-m41-form-row="' + i + '"><h3 data-m41-form-row-label="' + i + '">' + esc(row[0]) + '</h3>'
                + row.slice(1).map((c, j) => {const [en, uk] = c.split('\n'); return '<div data-m41-form-cell="' + i + '-' + (j + 1)
                    + '"><h3 data-m41-form-en>' + esc(en) + '</h3><p data-m41-form-uk>' + esc(uk) + '</p></div>';}).join('') + '</div>').join('');
    }
    return dom;
}
test('all original form values may render exactly once in native forms-grid cards', () => {
    for (const lesson of master.lessons) {
        const dom = nativeFormsFixture(lesson);
        try {assert.equal(contentFidelity(dom.window.document, lesson).tableExact, true);} finally {dom.window.close();}
    }
});
test('a native form card missing its original Ukrainian source value is rejected', () => {
    const lesson = master.lessons[0], dom = nativeFormsFixture(lesson);
    try {dom.window.document.querySelector('[data-m41-form-uk]').remove(); assert.throws(() => contentFidelity(dom.window.document, lesson));}
    finally {dom.window.close();}
});
function evidence() {
    return {pass: true, base: BASE, sitemap: {count: 554, sha256: 'exact-ordered'}, author: {at: 'before', rows: [
        {details: 4, points: [1, 2]}, {details: 5, points: [1, 2]}, {details: 5, points: [1, 2]}]},
    rows: routes.map(route => ({path: route, status: 200, contentType: 'text/html', xRobots: null,
        meta: {title: 'unchanged', h1: ['unchanged'], description: 'current M41', canonical: 'http://gramlyze.loc' + route,
            robots: 'index,follow', ogTitle: 'unchanged', twitterTitle: 'unchanged', ogDescription: 'current M41', twitterDescription: 'current M41'},
        details: 0, legacyIds: ['unchanged'], nativeBlockIds: ['unchanged'], mainTextSha256: 'exact'}))};
}
test('target presentation may change while exact metadata/source/control fingerprints stay protected', () => {
    const before = evidence(), after = structuredClone(before); after.author.at = 'after';
    for (const row of after.rows.filter(r => targetPaths.includes(r.path))) row.mainTextSha256 = 'new presentation';
    assert.equal(compare(before, after).pass, true);
});
for (const field of ['title', 'h1', 'description', 'canonical', 'robots', 'ogTitle', 'twitterTitle', 'ogDescription', 'twitterDescription']) {
    test('exact target ' + field + ' SEO metadata equality is required', () => {
        const before = evidence(), after = structuredClone(before); after.rows[0].meta[field] = 'changed';
        assert.throws(() => compare(before, after));
    });
}
test('ordered sitemap change is rejected', () => {
    const before = evidence(), after = structuredClone(before); after.sitemap.sha256 = 'changed';
    assert.throws(() => compare(before, after));
});
test('accepted control educational content cannot change', () => {
    const before = evidence(), after = structuredClone(before); after.rows.find(r => !targetPaths.includes(r.path)).mainTextSha256 = 'changed';
    assert.throws(() => compare(before, after));
});
test('additional native target component wrapper anchors preserve accepted original anchors', () => {
    const before = evidence(), after = structuredClone(before); after.rows[0].nativeBlockIds.unshift('new-wrapper');
    assert.equal(compare(before, after).pass, true);
});
test('an original target native anchor cannot be lost during component adaptation', () => {
    const before = evidence(), after = structuredClone(before); after.rows[0].nativeBlockIds = ['new-wrapper'];
    assert.throws(() => compare(before, after));
});
