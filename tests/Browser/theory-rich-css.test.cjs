const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const postcss = require('postcss');
const root = path.resolve(__dirname, '../..');
const css = fs.readFileSync(path.join(root, 'resources/css/theory-rich-content.css'), 'utf8');

test('rich lesson rules are opt-in and do not restyle legacy prose or site navigation', () => {
    const tree = postcss.parse(css);
    tree.walkRules(rule => {
        for (const selector of rule.selectors) {
            assert.match(selector, /\.theory-rich-/, selector);
            assert.doesNotMatch(selector, /(?:^|\s)(?:body|main|:root)(?:\s|$)|\[data-theory-(?:aside|sidebar)\]/, selector);
        }
    });
    assert.doesNotMatch(css, /!important|https?:|@import|position:\s*(?:fixed|absolute)/);
    assert.match(fs.readFileSync(path.join(root, 'resources/css/catalog-public.css'), 'utf8'), /^@import '\.\/theory-rich-content\.css';/);
});

test('rich content keeps explicit numbering, local table scroll, keyboard focus and both themes', () => {
    assert.match(css, /\.theory-rich-content ol\s*\{[^}]*list-style: decimal/s);
    assert.match(css, /\.theory-rich-content \.theory-rich-table\s*\{[^}]*max-width: 100%[^}]*overflow-x: auto/s);
    assert.match(css, /:focus-visible\s*\{[^}]*outline: 3px solid var\(--accent\)/s);
    assert.match(css, /\.dark \.theory-rich-content\s*\{/);
    assert.match(css, /@media \(max-width: 639px\)/);
    assert.match(css, /\.theory-rich-example\s*\{[^}]*display: block/s);
    assert.match(css, /\.theory-rich-practice > \.theory-rich-heading/);
    assert.match(css, /details\[open\] > summary/);
});
