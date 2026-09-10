const test = require('node:test');
const assert = require('node:assert/strict');
const {decision} = require('../../tools/diagnostics/seo-m9-browser.cjs');
const Q = '/test/future-perfect/questions';
test('M9 guard precedes navigation, denies production and unexpected redirect destinations', () => {
    for (const host of ['gramlyze.com', 'www.gramlyze.com', 'gramlyze.ub', 'x.gramlyze.ub.'])
        assert.equal(decision('https://' + host + Q, false, false, [Q]), 'forbidden-host');
    assert.equal(decision('http://gramlyze.loc' + Q, true, false, [Q]), null);
    assert.equal(decision('http://gramlyze.loc' + Q, true, true, [Q]), 'outside-navigation-plan');
    assert.equal(decision('https://example.com/', true, false, [Q]), 'outside-navigation-plan');
    assert.equal(decision('http://gramlyze.loc' + Q + '?x=1', true, false, [Q]), 'outside-navigation-plan');
});
test('Only ephemeral Questions state writes are permitted in that scenario', () => {
    assert.equal(decision('http://gramlyze.loc' + Q + '/state', false, false, [Q], 'POST', true), null);
    for (const [url, method, enabled] of [[Q + '/state', 'POST', false], [Q + '/state?x=1', 'POST', true], ['/progress', 'POST', true], [Q + '/state', 'DELETE', true]])
        assert.equal(decision('http://gramlyze.loc' + url, false, false, [Q], method, enabled), 'unplanned-write');
});
test('Ordinary fonts are permitted; retired script CDNs are not', () => {
    assert.equal(decision('https://fonts.googleapis.com/css2?family=Manrope', false, false, [Q]), null);
    assert.equal(decision('https://fonts.gstatic.com/font.woff2', false, false, [Q]), null);
    assert.equal(decision('https://cdn.tailwindcss.com/', false, false, [Q]), 'retired-runtime-cdn');
});
