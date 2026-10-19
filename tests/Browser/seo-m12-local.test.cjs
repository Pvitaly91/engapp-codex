const {test} = require('node:test');
const assert = require('node:assert/strict');
const {decision, PATHS, MODES, profile, compareCaptures} = require('../../tools/diagnostics/seo-m12-local.cjs');
const m11 = require('../../tools/diagnostics/seo-m11-local.cjs');

test('computed CSS RGB and normalized srgb colors use the same channel scale', () => {
    for (const value of ['rgb(255, 255, 255)', 'rgb(255 255 255)', 'rgb(100% 100% 100%)', 'color(srgb 1 1 1)']) {
        const actual = m11.parseCssColor(value);
        actual.slice(0, 3).forEach(v => assert.ok(Math.abs(v - 255) < .000001));
        assert.equal(actual[3], 1);
    }
    assert.deepEqual(m11.parseCssColor('color(srgb .2 0.4 0.6 / 50%)'), [51, 102, 153, .5]);
    assert.deepEqual(m11.parseCssColor('rgba(51, 102, 153, .5)'), [51, 102, 153, .5]);
    assert.deepEqual(m11.parseCssColor('transparent'), [0, 0, 0, 0]);
    assert.throws(() => m11.parseCssColor('color(display-p3 1 0 0)'));
    assert.throws(() => m11.parseCssColor('not a color'));
});

test('contrast composites transparent foreground and background layers without lowering the threshold', () => {
    const base = {selector: 'p', color: 'rgb(91, 108, 128)', backgrounds: ['transparent', 'color(srgb 1 1 1)', 'rgb(0 0 0)'], fontSize: '16px', fontWeight: '400'};
    const sample = m11.readabilitySample(base);
    assert.deepEqual(sample.background, [255, 255, 255]);
    assert.ok(sample.contrast > 5 && sample.passes);
    assert.equal(sample.minimum, 4.5);
    const translucent = m11.readabilitySample({...base, color: 'rgb(0 0 0)', backgrounds: ['rgba(0, 0, 0, .5)', 'color(srgb 1 1 1)']});
    assert.deepEqual(translucent.background, [128, 128, 128]);
    assert.ok(translucent.contrast > 5 && translucent.contrast < 5.5);
    const fg = m11.readabilitySample({...base, color: 'color(srgb 1 1 1 / .5)', backgrounds: ['rgb(0 0 0)']});
    assert.equal(fg.contrast, translucent.contrast);
    const unreadable = m11.readabilitySample({...base, color: 'rgb(220 220 220)'});
    assert.equal(unreadable.passes, false);
    const dark = m11.readabilitySample({...base, color: 'rgb(255 255 255)', backgrounds: ['transparent', 'color(srgb 0 0 0)']});
    assert.equal(dark.contrast, 21);
});

function capture(applied = false) {
    return {at: applied ? 'after' : 'before', package: 'm12', base: 'http://gramlyze.loc', rows: PATHS.map(path => {
        if (path === '/sitemap.xml') return {path, status: 200, contentType: 'application/xml', location: null, xRobotsTag: null, orderedLocs: ['one', 'two'], orderedSha256: 'same-order'};
        const theory = profile.slugs.some(slug => profile.theory(slug) === path);
        const description = [applied && theory ? 'Змістовний новий опис.' : 'Original description.'];
        return {path, status: 200, contentType: 'text/html', location: null, xRobotsTag: 'noindex', testLinks: [],
            selfChecks: applied && theory ? 1 : 0, selfCheckQuestions: applied && theory ? 6 : 0, selfCheckKeys: applied && theory ? 6 : 0,
            anchorPlaceholder: theory && !applied,
            metadata: {title: [path], h1: ['Original heading'], canonical: ['https://gramlyze.com' + path], robots: ['noindex'],
                description, ogDescription: [...description], twitterDescription: [...description]}};
    })};
}

test('M12 fixed URLs preserve nested word-order theory/course ancestry but flat test namespace', () => {
    assert.equal(PATHS.length, 8);
    assert.equal(profile.theory('inversion-basics'), '/theory/basic-grammar/word-order/inversion-basics');
    assert.equal(profile.test('inversion-basics'), '/test/word-order/inversion-basics');
    assert.equal(profile.theory('cleft-sentences-basics'), '/theory/sentence-structure/cleft-sentences-basics');
    assert.equal(profile.course, '/courses/english-grammar-theory/lesson/basic-grammar/word-order/inversion-basics');
    assert.throws(() => profile.theory('cleft-sentences-emphasis'));
    assert.ok(Object.isFrozen(PATHS) && Object.isFrozen(profile));
});

test('M12 exposes real-server acceptance only, with no fixture or arbitrary URL mode', () => {
    assert.deepEqual(MODES, ['capture', 'browser-applied', 'compare']);
    for (const p of PATHS) assert.equal(decision('http://gramlyze.loc' + p, 'GET', true), null);
    for (const u of ['https://gramlyze.com/', 'https://gramlyze.ub/', 'http://gramlyze.loc/admin', 'http://gramlyze.loc' + PATHS[0] + '?unlock=1', 'http://a:b@gramlyze.loc' + PATHS[0]]) assert.ok(decision(u, 'GET', true));
    for (const method of ['POST', 'PUT', 'PATCH', 'DELETE']) assert.equal(decision('http://gramlyze.loc/state', method), 'stateful-request');
    assert.equal(decision('https://fonts.gstatic.com/font.woff2'), null);
    assert.equal(decision('https://example.com/file.js'), 'external-host');
});

test('M11 default plan and M12 plan remain separate', () => {
    assert.equal(m11.PATHS.length, 8);
    for (const p of m11.PATHS) assert.equal(m11.decision('http://gramlyze.loc' + p, 'GET', true), null);
    assert.equal(m11.decision('http://gramlyze.loc' + PATHS[0], 'GET', true), 'outside-plan');
    assert.equal(decision('http://gramlyze.loc' + m11.PATHS[0], 'GET', true), 'outside-plan');
});

test('capture comparison permits only the three theory descriptions and preserves ordered sitemap', () => {
    const result = compareCaptures(capture(), capture(true));
    assert.equal(result.pass, true);
    assert.equal(result.rows.filter(r => r.descriptionChanged).length, 3);
    assert.equal(result.rows.at(-1).urls, 2); // Dynamic baseline, not a hardcoded live count.
});

test('capture comparison refuses changed protected metadata, tests/course descriptions and response headers', () => {
    for (const key of ['title', 'h1', 'canonical', 'robots']) {
        const after = capture(true); after.rows[0].metadata[key] = ['changed'];
        assert.throws(() => compareCaptures(capture(), after), key);
    }
    for (const index of [3, 6]) {
        const after = capture(true); after.rows[index].metadata.description = ['changed'];
        assert.throws(() => compareCaptures(capture(), after));
    }
    for (const field of ['location', 'contentType', 'xRobotsTag']) {
        const after = capture(true); after.rows[0][field] = 'changed';
        assert.throws(() => compareCaptures(capture(), after), field);
    }
});

test('capture comparison refuses stale content, incomplete keys, inconsistent metadata and unavailable pages', () => {
    for (const [field, value] of [['anchorPlaceholder', true], ['selfChecks', 0], ['selfCheckQuestions', 5], ['selfCheckKeys', 5], ['status', 503]]) {
        const after = capture(true); after.rows[0][field] = value;
        assert.throws(() => compareCaptures(capture(), after), field);
    }
    const after = capture(true); after.rows[0].metadata.ogDescription = ['Other description'];
    assert.throws(() => compareCaptures(capture(), after));
});

test('capture comparison refuses different targets, path inventories and reordered sitemap', () => {
    const remote = capture(true); remote.base = 'https://gramlyze.com';
    assert.throws(() => compareCaptures(capture(), remote));
    const foreign = capture(true); foreign.package = 'm11';
    assert.throws(() => compareCaptures(capture(), foreign));
    const paths = capture(true); paths.rows.reverse();
    assert.throws(() => compareCaptures(capture(), paths));
    const sitemap = capture(true); sitemap.rows.at(-1).orderedLocs.reverse();
    assert.throws(() => compareCaptures(capture(), sitemap));
});
