const {test} = require('node:test');
const assert = require('node:assert/strict');
const {decision} = require('../../tools/diagnostics/seo-m6-browser.cjs');

test('M6 browser blocks all nonlocal requests, including production before navigation', () => {
    const paths = new Set(['/tests/cards', '/catalog/tests-cards']);
    for (const url of ['https://gramlyze.com/', 'https://gramlyze.ub/', 'https://www.gramlyze.com/',
        'https://fonts.googleapis.com/css', 'http://user:pass@gramlyze.loc/', 'http://attacker.invalid/']) {
        assert.equal(decision(url, true, paths), 'nonlocal-blocked');
        assert.equal(decision(url, false, paths), 'nonlocal-blocked');
    }
    assert.equal(decision('http://gramlyze.loc/tests/cards', true, paths), null);
    assert.equal(decision('http://gramlyze.loc/catalog/tests-cards', true, paths), null);
    assert.equal(decision('http://gramlyze.loc/catalog/tests-cards#filter', true, paths), null);
    assert.equal(decision('http://gramlyze.loc/unplanned', true, paths), 'outside-navigation-plan');
    assert.equal(decision('http://gramlyze.loc/tests/cards?redirect=/other', true, paths), 'outside-navigation-plan');
    assert.equal(decision('http://gramlyze.loc/build/assets/app.js', false, paths), null);
});
