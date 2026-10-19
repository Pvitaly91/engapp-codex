'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {decision, PATHS, MODES, profile, controls, references, compareCaptures, assertMobileTables} = require('../../tools/diagnostics/seo-m19-local.cjs');
const m12 = require('../../tools/diagnostics/seo-m12-local.cjs');
function capture(after = false) {
    return {at: after ? 'after' : 'before', package: 'm19', base: 'http://gramlyze.loc', rows: PATHS.map(path => {
        if (path === '/sitemap.xml') return {path, status: 200, contentType: 'application/xml', location: null, xRobotsTag: null, orderedLocs: ['one', 'two'], orderedSha256: 'same-order'};
        const target = profile.slugs.some(slug => profile.theory(slug) === path);
        const description = [after && target ? 'Новий український опис.' : 'Existing description.'];
        return {path, status: 200, contentType: 'text/html', location: null, xRobotsTag: 'noindex', testLinks: [], textSha256: after && target ? 'new' : 'same',
            selfChecks: after && target ? 1 : 0, selfCheckQuestions: after && target ? 6 : 0, selfCheckKeys: after && target ? 6 : 0,
            anchorPlaceholder: target && !after, richSections: after && target ? 9 : 0, richExamples: after && target ? 10 : 0,
            metadata: {title: [path], h1: ['Existing title'], canonical: ['https://gramlyze.com' + path], robots: [],
                description, ogDescription: [...description], twitterDescription: [...description]}};
    })};
}
test('M19 exposes only a fixed local inventory and read-only real acceptance', () => {
    assert.deepEqual(MODES, ['capture', 'browser-applied', 'compare']);
    assert.equal(profile.slugs.length, 3); assert.equal(controls.length, 2); assert.equal(references.length, 8);
    assert.equal(PATHS.length, 18); assert.ok(profile.richContent && profile.extendedChecks);
    assert.throws(() => profile.theory('foreign'));
    for (const p of PATHS) assert.equal(decision('http://gramlyze.loc' + p, 'GET', true), null);
    for (const u of ['https://gramlyze.com/', 'https://gramlyze.ub/', 'http://gramlyze.loc/admin', 'http://gramlyze.loc' + PATHS[0] + '?unlock=1', 'http://a:b@gramlyze.loc' + PATHS[0]]) assert.ok(decision(u, 'GET', true));
    for (const m of ['POST', 'PATCH', 'PUT', 'DELETE']) assert.equal(decision('http://gramlyze.loc/test/state', m), 'stateful-request');
});
test('M12 and M19 allowlists remain separate', () => {
    assert.equal(m12.profile.id, 'm12'); assert.equal(m12.PATHS.length, 8);
    assert.equal(m12.decision('http://gramlyze.loc' + PATHS[0], 'GET', true), 'outside-plan');
});
test('M19 mobile tables must scroll locally rather than squashing words', () => {
    const report = () => ({rows: profile.slugs.map((slug, i) => ({path: profile.theory(slug), mobile: true, pass: true, overflow: false, tableScroll: {contentWidth: [1000, 1000, 1000][i], width: 322, left: [678, 678, 678][i]}}))});
    assertMobileTables(report());
    for (const change of [{contentWidth: 322, left: 0}, {contentWidth: 720, left: 0}]) {
        const r = report(); Object.assign(r.rows[0].tableScroll, change);
        assert.throws(() => assertMobileTables(r));
    }
    const r = report(); r.rows[0].overflow = true; assert.throws(() => assertMobileTables(r));
});
test('only three target descriptions may change; sitemap count is observed', () => {
    const r = compareCaptures(capture(), capture(true));
    assert.ok(r.pass); assert.equal(r.rows.filter(r => r.descriptionChanged).length, 3);
    assert.equal(r.rows.at(-1).urls, 2);
});
test('protected metadata and old lessons cannot change', () => {
    for (const key of ['title', 'h1', 'canonical', 'robots']) {
        const a = capture(true); a.rows[0].metadata[key] = ['changed']; assert.throws(() => compareCaptures(capture(), a));
    }
    for (const path of [...controls, ...references, profile.course, profile.test(profile.slugs[0])]) {
        const a = capture(true); a.rows.find(r => r.path === path).metadata.description = ['changed'];
        assert.throws(() => compareCaptures(capture(), a));
    }
    const a = capture(true); a.rows.find(r => r.path === controls[0]).textSha256 = 'changed';
    assert.throws(() => compareCaptures(capture(), a));
});
test('plain fallback, stale content and incomplete self checks fail acceptance', () => {
    for (const [field, value] of [['richSections', 0], ['richExamples', 0], ['selfChecks', 0], ['selfCheckQuestions', 5], ['selfCheckKeys', 5], ['anchorPlaceholder', true], ['status', 500]]) {
        const a = capture(true); a.rows[0][field] = value; assert.throws(() => compareCaptures(capture(), a), field);
    }
    const a = capture(true); a.rows[0].metadata.ogDescription = ['wrong'];
    assert.throws(() => compareCaptures(capture(), a));
});
test('foreign targets, inventories and changed ordered sitemap are rejected', () => {
    for (const field of ['base', 'package']) {
        const a = capture(true); a[field] = 'foreign'; assert.throws(() => compareCaptures(capture(), a));
    }
    const a = capture(true); a.rows.at(-1).orderedLocs.reverse(); assert.throws(() => compareCaptures(capture(), a));
    const b = capture(true); b.rows.reverse(); assert.throws(() => compareCaptures(capture(), b));
});
test('shared rich opt-in checks actual DOM and mouse before existing keyboard/reload checks', () => {
    const source = fs.readFileSync(require.resolve('../../tools/diagnostics/seo-m11-local.cjs'), 'utf8');
    assert.ok(source.includes('if (profile.richContent)'));
    assert.ok(source.includes('Actual rich renderer required'));
    assert.ok(source.includes('Mouse opens keys'));
    assert.ok(source.includes('row.reloadPassed = true'));
});
