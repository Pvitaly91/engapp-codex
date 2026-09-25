const test = require('node:test');
const assert = require('node:assert/strict');
const {decision, PAGES} = require('../../tools/diagnostics/seo-m5-browser.cjs');
test('M5 blocks production before navigation and permits only four planned local documents', () => {
    for (const url of ['https://gramlyze.com/', 'https://www.gramlyze.com/', 'https://gramlyze.ub/']) assert.equal(decision(url, true, false), 'forbidden-host');
    for (const [, p] of PAGES) assert.equal(decision('http://gramlyze.loc' + p, true, false), null);
    assert.equal(decision('http://gramlyze.loc/theory', true, false), 'outside-navigation-plan');
    assert.equal(decision('http://gramlyze.loc/test/future-perfect/questions?source=theory', true, false), 'outside-navigation-plan');
    assert.equal(decision('http://gramlyze.loc/test/future-perfect/questions', true, true), 'outside-navigation-plan');
});
