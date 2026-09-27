const {test} = require('node:test');
const assert = require('node:assert/strict');
const {BASE, LESSONS, PATHS, MODES, decision, normalizeText, contentSnapshot, compareCaptures} = require('../../tools/diagnostics/seo-m12-styling-local.cjs');

const html = '<html><head><title>Lesson title</title><meta name="description" content="Unchanged description"><meta name="robots" content="noindex, nofollow"><link rel="canonical" href="https://gramlyze.com/theory/example"></head><body><a href="/theory">Theory</a><main data-theory-main><h1>Original heading</h1><article><div class="prose"><h2>Rule</h2><p>Keep every word.</p><section id="self-check-example"><ol><li>Question?</li></ol><details><summary>Key</summary><ol><li>Answer.</li></ol></details></section><a href="/test/example">Пройти тест</a></div></article></main></body></html>';
function capture() {
    return {at: 'now', base: BASE, package: 'm12-styling', rows: PATHS.map(path => ({path, status: 200, location: null, contentType: path === '/sitemap.xml' ? 'text/xml' : 'text/html', xRobotsTag: 'noindex', ...contentSnapshot(path === '/sitemap.xml' ? '<urlset><url><loc>first</loc></url><url><loc>second</loc></url></urlset>' : html, path)}))};
}

test('styling scope fixes six affected lessons, two verified old references and six test destinations', () => {
    assert.equal(LESSONS.length, 8); assert.equal(LESSONS.filter(l => l.reference).length, 2);
    assert.equal(PATHS.length, 15); assert.ok(Object.isFrozen(PATHS) && Object.isFrozen(LESSONS));
    assert.ok(LESSONS.every(Object.isFrozen));
    assert.deepEqual(MODES, ['capture', 'browser', 'browser-styled', 'compare']);
    assert.ok(PATHS.includes('/theory/tenses/present-perfect/present-perfect-forms'));
    assert.ok(PATHS.includes('/theory/tenses/past-perfect/past-perfect-forms'));
});

test('runner denies production, unknown navigation, stateful requests, credentials and arbitrary fixtures', () => {
    for (const p of PATHS) assert.equal(decision(BASE + p, 'GET', true), null);
    for (const url of ['https://gramlyze.com' + PATHS[0], 'http://gramlyze.ub' + PATHS[0], BASE + '/admin', BASE + PATHS[0] + '?unlock=1', 'http://a:b@gramlyze.loc' + PATHS[0], 'not-a-url']) assert.ok(decision(url, 'GET', true));
    for (const method of ['POST', 'PUT', 'PATCH', 'DELETE']) assert.equal(decision(BASE + PATHS[0], method), 'stateful-request');
    assert.equal(decision('https://fonts.googleapis.com/css2?family=Inter'), null);
    assert.equal(decision('https://fonts.gstatic.com/font.woff2'), null);
    assert.equal(decision('https://example.com/script.js'), 'external-host');
});

test('normalization ignores whitespace only, not word, punctuation or order changes', () => {
    assert.equal(normalizeText(' One\n two\tthree. '), 'One two three.');
    assert.notEqual(normalizeText('One two.'), normalizeText('One TWO.'));
    assert.notEqual(normalizeText('One two.'), normalizeText('One two!'));
    assert.notEqual(normalizeText('One two.'), normalizeText('two One.'));
});

test('presentation wrappers and classes preserve the capture contract', () => {
    const before = contentSnapshot(html, PATHS[0]);
    const styled = html.replace('<p>Keep every word.</p>', '<div class="lesson-card"><p class="text-lg">Keep every word.</p></div>');
    assert.deepEqual(contentSnapshot(styled, PATHS[0]), before);
    assert.equal(compareCaptures(capture(), capture()).pass, true);
});

test('captures exclude executable styles/scripts and inert templates, never storing runtime tokens', () => {
    const runtime = html.replace('</article>', '<script>const CSRF_TOKEN = "private-per-request-token";</script><style>.x{color:red}</style><template>Unrendered duplicate</template></article>');
    assert.deepEqual(contentSnapshot(runtime, PATHS[0]), contentSnapshot(html, PATHS[0]));
    assert.ok(!JSON.stringify(contentSnapshot(runtime, PATHS[0])).includes('private-per-request-token'));
});

test('capture comparison preserves every metadata field including all descriptions', () => {
    for (const key of ['title', 'h1', 'description', 'canonical', 'robots', 'ogDescription', 'twitterDescription']) {
        const after = capture(); after.rows[0].metadata[key] = ['changed'];
        assert.throws(() => compareCaptures(capture(), after), key);
    }
});

test('capture comparison refuses changed words, link inventory/order and self-check shape', () => {
    for (const field of ['normalizedText', 'textSha256', 'lessonText', 'lessonTextSha256']) {
        const after = capture(); after.rows[0][field] = 'changed';
        assert.throws(() => compareCaptures(capture(), after), field);
    }
    for (const field of ['links', 'mainLinks']) {
        const after = capture(); after.rows[0][field].push('/another-destination');
        assert.throws(() => compareCaptures(capture(), after), field);
    }
    const reordered = capture(); reordered.rows[0].links.reverse();
    assert.throws(() => compareCaptures(capture(), reordered));
    const changedKeys = capture(); changedKeys.rows[0].selfChecks[0].keys = 0;
    assert.throws(() => compareCaptures(capture(), changedKeys));
});

test('capture comparison refuses headers, failed pages, reordered sitemap and scope changes', () => {
    for (const field of ['location', 'contentType', 'xRobotsTag', 'status']) {
        const after = capture(); after.rows[0][field] = 'changed';
        assert.throws(() => compareCaptures(capture(), after), field);
    }
    const remote = capture(); remote.base = 'https://gramlyze.com';
    assert.throws(() => compareCaptures(capture(), remote));
    const paths = capture(); paths.rows.reverse();
    assert.throws(() => compareCaptures(capture(), paths));
    const sitemap = capture(); sitemap.rows.at(-1).orderedLocs.reverse();
    assert.throws(() => compareCaptures(capture(), sitemap));
    const hash = capture(); hash.rows.at(-1).orderedSha256 = 'different';
    assert.throws(() => compareCaptures(capture(), hash));
});
