const test = require('node:test');
const assert = require('node:assert/strict');
const {decision} = require('../../tools/diagnostics/seo-m7-browser.cjs');
test('M7 permits planned local navigation and fonts, blocks production before requests', () => {
    const path = '/theory/tenses/narrative-tenses';
    assert.equal(decision('http://gramlyze.loc' + path, true, false, path), null);
    for (const host of ['gramlyze.com', 'www.gramlyze.com', 'gramlyze.ub', 'www.gramlyze.ub']) {
        assert.equal(decision('https://' + host + path, false, false, path), 'forbidden-host');
    }
    assert.equal(decision('https://fonts.googleapis.com/css2?family=Example', false, false, path), null);
    assert.equal(decision('https://fonts.gstatic.com/example.woff2', false, false, path), null);
    assert.equal(decision('http://gramlyze.loc' + path, true, true, path), 'outside-navigation-plan');
    assert.equal(decision('http://gramlyze.loc/theory/other', true, false, path), 'outside-navigation-plan');
    assert.equal(decision('http://gramlyze.loc' + path + '?query=value', true, false, path), 'outside-navigation-plan');
});
