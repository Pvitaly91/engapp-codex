'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const {content, compare, authoredHeaders} = require('../../tools/diagnostics/seo-m25-local.cjs');

const snapshot = html => {
    const document = new JSDOM(html).window.document;
    const row = {path: '/theory/example', status: 200, location: null, contentType: 'text/html',
        xRobotsTag: 'noindex', metadata: {title: 'Unchanged'}, content: content(document.body), newDetailControls: 0};
    return {base: 'http://gramlyze.loc', rows: [row]};
};
const unchanged = '<section id="block-1"><h2>1. Content</h2><p>not 42.</p><a href="#old">Old link</a><table><tr><td>first</td><td>second</td></tr></table><details><summary>Answers</summary><p id="old">Exact key.</p></details></section>';

test('M25 comparison ignores only marked implementation UI and whitespace', () => {
    const after = unchanged.replace('<p>not', '<aside data-theory-ui>Navigation</aside><p>  not');
    assert.equal(compare(snapshot(unchanged), snapshot(after)).differences.length, 0);
});
for (const [name, from, to] of [
    ['negation', 'not 42.', '42.'], ['number', 'not 42.', 'not 43.'],
    ['punctuation', 'not 42.', 'not 42'], ['href', 'href="#old"', 'href="#new"'],
    ['table relation', '<td>first</td><td>second</td>', '<td>second</td><td>first</td>'],
    ['answer text', 'Exact key.', 'Different key.'], ['anchor', 'id="old"', 'id="new"'],
    ['duplicate anchor', '<p id="old">', '<i id="old"></i><p id="old">'],
]) test('M25 comparison rejects changed ' + name, () => {
    assert.ok(compare(snapshot(unchanged), snapshot(unchanged.replace(from, to))).differences.length);
});
test('M25 comparison rejects opening old exercise answers by default', () => {
    assert.ok(compare(snapshot(unchanged), snapshot(unchanged.replace('<details>', '<details open>'))).differences.length);
});
test('M25 comparison accepts added anchors without losing old anchors', () => {
    assert.equal(compare(snapshot(unchanged), snapshot(unchanged.replace('<h2>', '<h2 id="new-section">'))).differences.length, 0);
});
test('M25 comparison rejects SEO changes and new real-lesson detail controls', () => {
    const before = snapshot(unchanged), after = snapshot(unchanged);
    after.rows[0].metadata.title = 'Changed';
    after.rows[0].newDetailControls = 1;
    assert.deepEqual(compare(before, after).differences[0].errors, ['metadata', 'live-detail-controls']);
});
test('Authored native heading restoration validates exact source, not a punctuation-stripping heuristic', () => {
    const before = new JSDOM('<section id="block-1"><h2><span>1</span> Exact heading</h2><p>not42.</p></section>').window.document.body;
    const after = new JSDOM('<section id="block-1"><h2 class="theory-section-title"><span>1.</span> Exact heading</h2><p>not42.</p></section>').window.document.body;
    const expected = {'block-1': '1. Exact heading'};
    const old = authoredHeaders(before, expected), current = authoredHeaders(after, expected);
    assert.equal(old.restored.length, 1); assert.deepEqual(old.errors, []); assert.deepEqual(current.errors, []);
    assert.deepEqual(content(current.node), content(old.node));
    after.querySelector('h2').textContent = '1. Changed heading';
    assert.equal(authoredHeaders(after, expected).errors.length, 1);
});
