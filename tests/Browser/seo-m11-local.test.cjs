const {test} = require('node:test');
const assert = require('node:assert/strict');
const {decision, PATHS} = require('../../tools/diagnostics/seo-m11-local.cjs');
test('M11 browser accepts only planned local navigation and read-only requests', () => {
    for (const p of PATHS) assert.equal(decision('http://gramlyze.loc' + p, 'GET', true), null);
    for (const u of ['https://gramlyze.com/', 'https://gramlyze.ub/', 'http://gramlyze.loc/admin', 'http://gramlyze.loc' + PATHS[0] + '?unlock=1']) assert.ok(decision(u, 'GET', true));
    assert.equal(decision('http://gramlyze.loc/state', 'POST'), 'stateful-request');
    assert.equal(decision('https://fonts.gstatic.com/font.woff2'), null);
});
