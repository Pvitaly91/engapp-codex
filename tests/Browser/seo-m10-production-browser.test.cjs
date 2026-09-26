'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const {validateOrigin, safeUrl, requestDecision, removeOwnSnapshots, parseArgs, selectedCases} = require('../../tools/diagnostics/seo-m10-production-browser.cjs');

const ORIGIN = 'https://gramlyze.com';
const Q = '/test/future-perfect/questions';

test('production origin allowlist is exact HTTPS without credentials', () => {
    assert.equal(validateOrigin(ORIGIN), ORIGIN);
    assert.equal(validateOrigin('https://www.gramlyze.com/'), 'https://www.gramlyze.com');
    for (const value of ['http://gramlyze.com', 'http://gramlyze.loc', 'https://localhost', 'https://example.com',
        'https://user:pass@gramlyze.com', 'https://gramlyze.com:8443', 'https://gramlyze.com/path'])
        assert.throws(() => validateOrigin(value));
});

test('external navigation is denied while Google Fonts resources are allowed', () => {
    const options = {origin: ORIGIN, navigation: true, allowedPaths: [Q], method: 'GET'};
    assert.equal(requestDecision(ORIGIN + Q, options), null);
    assert.equal(requestDecision('https://example.com/', options), 'external-navigation');
    assert.equal(requestDecision(ORIGIN + Q + '?x=1', options), 'outside-navigation-plan');
    assert.equal(requestDecision('https://fonts.googleapis.com/css2?family=Manrope', {...options, navigation: false}), null);
    assert.equal(requestDecision('https://fonts.googleapis.com/', options), 'external-navigation');
    assert.equal(requestDecision('http://gramlyze.com/app.js', {...options, navigation: false}), 'mixed-content');
});

test('only the anonymous Questions state POST is allowed', () => {
    const base = {origin: ORIGIN, navigation: false, allowedPaths: [Q], questionsWrite: true};
    assert.equal(requestDecision(ORIGIN + Q + '/state', {...base, method: 'POST'}), null);
    for (const [url, method, enabled] of [[Q + '/state?x=1', 'POST', true], ['/progress', 'POST', true], [Q + '/state', 'DELETE', true], [Q + '/state', 'POST', false]])
        assert.equal(requestDecision(ORIGIN + url, {...base, method, questionsWrite: enabled}), 'unplanned-write');
});

test('URL evidence removes query and fragments', () => {
    assert.equal(safeUrl(ORIGIN + Q + '?token=secret#answer'), ORIGIN + Q);
});

test('server restoration removes only exact own storage keys', () => {
    const storage = entries => {
        const values = new Map(entries);
        return {get length() { return values.size; }, key: index => [...values.keys()][index],
            getItem: key => values.get(key) ?? null, removeItem: key => values.delete(key)};
    };
    const local = storage([['own', 'state'], ['theme', 'dark'], ['other', 'untouched']]);
    const session = storage([['legacy', 'state'], ['course', 'untouched']]);
    assert.deepEqual(removeOwnSnapshots({keys: ['own', 'legacy'], local, session}),
        {removed: 2, unrelatedStoragePreserved: true, snapshotsAbsent: true});
    assert.equal(local.getItem('theme'), 'dark');
    assert.equal(session.getItem('course'), 'untouched');
});

test('CLI requires explicit origin, label and HTTP evidence', () => {
    assert.deepEqual(parseArgs(['--origin', ORIGIN, '--label', 'acceptance', '--http', 'evidence.json']),
        {origin: ORIGIN, label: 'acceptance', http: 'evidence.json'});
    assert.throws(() => parseArgs(['--label', 'acceptance']));
    assert.throws(() => parseArgs(['--origin', 'http://gramlyze.loc', '--label', 'x', '--http', 'x.json']));
});

test('targeted follow-up accepts only unique known kind-viewport pairs', () => {
    assert.deepEqual(selectedCases('theory-mobile,course-desktop'), [
        {kind: 'course', mobile: false}, {kind: 'theory', mobile: true},
    ]);
    assert.equal(selectedCases().length, 8);
    assert.throws(() => selectedCases('unknown-mobile'));
    assert.throws(() => selectedCases('course-desktop,course-desktop'));
});
