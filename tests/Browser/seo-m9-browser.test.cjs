const test = require('node:test');
const assert = require('node:assert/strict');
const {decision, scenarioKinds, removeOwnSnapshots} = require('../../tools/diagnostics/seo-m9-browser.cjs');
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
test('M9.4 mode runs only theory and Questions on each viewport', () => {
    assert.deepEqual(scenarioKinds('--theory-questions-only'), ['theory', 'questions']);
    assert.deepEqual(scenarioKinds('--questions-course-only'), ['questions', 'course']);
    assert.deepEqual(scenarioKinds('--handler-acceptance'), ['theory', 'questions', 'course']);
    assert.deepEqual(scenarioKinds(), ['theory', 'questions', 'course', 'one-ones']);
    assert.throws(() => scenarioKinds('--all-the-things'));
});

test('Server restoration removes only the exact own snapshot keys and preserves other storage', () => {
    const storage = entries => {
        const values = new Map(entries);
        return {get length() { return values.size; }, key: i => [...values.keys()][i],
            getItem: key => values.get(key) ?? null, removeItem: key => values.delete(key)};
    };
    const local = storage([['own-question', 'snapshot'], ['theme', 'dark'], ['other-question', 'untouched']]);
    const session = storage([['own-legacy', 'old snapshot'], ['course', 'untouched']]);
    assert.deepEqual(removeOwnSnapshots({keys: ['own-question', 'own-legacy'], local, session}),
        {removed: 2, unrelatedStoragePreserved: true, snapshotsAbsent: true});
    assert.equal(local.getItem('theme'), 'dark');
    assert.equal(local.getItem('other-question'), 'untouched');
    assert.equal(session.getItem('course'), 'untouched');
    assert.throws(() => removeOwnSnapshots({keys: [], local, session}));
});
