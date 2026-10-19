'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const m23 = require('../../tools/diagnostics/seo-m23-local.cjs');

function capture(after = false) {
    return {at: after ? 'after' : 'before', base: 'http://gramlyze.loc', package: 'm23', rows: m23.PATHS.map(path => {
        if (path === '/sitemap.xml') return {path, status: 200, contentType: 'application/xml', location: null,
            xRobotsTag: null, orderedLocs: ['one', 'two'], orderedSha256: 'ordered'};
        const target = m23.slugs.some(slug => m23.theory(slug) === path);
        const description = [target && after ? 'Авторський український опис' : 'Попередній опис'];
        return {path, status: 200, contentType: 'text/html', location: null, xRobotsTag: 'noindex',
            textSha256: path === m23.controls[0] ? 'control' : 'other', testLinks: [],
            selfChecks: target && after ? 1 : 0, selfCheckQuestions: target && after ? 6 : 0,
            selfCheckKeys: target && after ? 6 : 0, anchorPlaceholder: target && !after,
            richSections: target && after ? 8 : 0, richExamples: target && after ? 9 : 0,
            metadata: {title: ['Existing title'], h1: ['Existing H1'], canonical: ['https://gramlyze.com' + path],
                robots: [], description, ogDescription: [...description], twitterDescription: [...description]}};
    })};
}
const protectedData = () => ({tables: Object.fromEntries(Array.from({length: 46}, (_, i) => ['table-' + i, {rows: i, protected_sha256: 'same'}])),
    env_sha256: 'same', accepted_sources_match: Object.fromEntries(Array.from({length: 36}, (_, i) => ['older-' + i, true]))});
const evidence = () => ({before: protectedData(), after: protectedData()});

test('M23 is confined to three fixed local lessons and GET-only acceptance', () => {
    assert.deepEqual(m23.MODES, ['capture', 'browser-applied', 'compare']);
    assert.equal(m23.slugs.length, 3);
    assert.equal(m23.PATHS.length, 10);
    assert.throws(() => m23.theory('foreign'));
    for (const path of m23.PATHS) assert.equal(m23.decision('http://gramlyze.loc' + path, 'GET', true), null);
    for (const url of ['https://gramlyze.com/', 'https://gramlyze.ub/', 'http://gramlyze.loc/admin',
        'http://gramlyze.loc' + m23.PATHS[0] + '?unlock=1']) assert.ok(m23.decision(url, 'GET', true));
    for (const method of ['POST', 'PATCH', 'PUT', 'DELETE']) assert.equal(m23.decision('http://gramlyze.loc/test/state', method), 'stateful-request');
});

test('only the three authored theory descriptions may change', () => {
    const result = m23.compareCaptures(capture(), capture(true), evidence());
    assert.ok(result.pass);
    assert.equal(result.rows.filter(row => row.descriptionChanged).length, 3);
    for (const key of ['title', 'h1', 'canonical', 'robots']) {
        const changed = capture(true); changed.rows[0].metadata[key] = ['changed'];
        assert.throws(() => m23.compareCaptures(capture(), changed, evidence()));
    }
    const changed = capture(true); changed.rows.find(row => row.path === m23.controls[0]).textSha256 = 'changed';
    assert.throws(() => m23.compareCaptures(capture(), changed, evidence()));
});

test('missing authored sections, altered sitemap, and changed protected DB fail', () => {
    for (const [field, value] of [['selfChecks', 0], ['selfCheckQuestions', 5], ['selfCheckKeys', 5],
        ['anchorPlaceholder', true], ['richSections', 0], ['status', 500]]) {
        const changed = capture(true); changed.rows[0][field] = value;
        assert.throws(() => m23.compareCaptures(capture(), changed, evidence()), field);
    }
    const changed = capture(true); changed.rows.at(-1).orderedLocs.reverse();
    assert.throws(() => m23.compareCaptures(capture(), changed, evidence()));
    const data = evidence(); data.after.tables['table-2'].rows++;
    assert.throws(() => m23.compareCaptures(capture(), capture(true), data));
});

test('mobile table acceptance applies only to the two authored tables', () => {
    const report = () => ({rows: m23.slugs.map(slug => ({path: m23.theory(slug), mobile: true, pass: true,
        overflow: false, ...(slug === 'c2-mixed-revision' ? {} : {tableScroll: {contentWidth: 760, width: 320, left: 440}})}))});
    m23.assertBrowserTables(report());
    const noScroll = report(); noScroll.rows[0].tableScroll.left = 0;
    assert.throws(() => m23.assertBrowserTables(noScroll));
    const fakeC2 = report(); fakeC2.rows[2].tableScroll = {contentWidth: 760, width: 320, left: 440};
    assert.throws(() => m23.assertBrowserTables(fakeC2));
});
