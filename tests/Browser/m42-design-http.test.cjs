'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const {BASE, registry, routes, targetPaths, words, learner, jsonLd, assertRoute, compare} = require('../../tools/diagnostics/capture-m42-design-http.cjs');
function evidence() {
    const content = {textSha256: 'exact', words: {Example: 1, переклад: 1}, orderedWords: ['Example', 'переклад'],
        details: [{id: 'point-detail', text: 'Exact own detail', pointAnchor: 'point'}], tables: [{headings: ['Column1', 'Column2', 'Column3'], rows: [['A', 'B', 'C']]}],
        practice: [{factory: 'originalPractice', stateSha256: 'exact aliases scoring reset', controls: [{type: 'manual'}]}],
        anchors: ['block-original', 'point', 'point-detail'], headings: [{tag: 'H2', text: 'Exact source title'}]};
    return {at: 'before', pass: true, base: BASE, registrySha256: 'exact finite source',
        rows: routes.map(route => ({path: route, status: 200, finalUrl: BASE + route, contentType: 'text/html', xRobots: null,
            meta: {title: 'exact', h1: ['exact'], description: 'exact', canonical: BASE + route, robots: 'index,follow'},
            jsonLd: [{valid: true, sha256: 'exact', value: {'@type': 'WebPage'}}], courseGate: null, learner: structuredClone(content)})),
        extras: Object.fromEntries(['/robots.txt', '/sitemap.xml', '/health', '/dev/site-mode'].map(route => [route,
            {status: 200, finalUrl: BASE + route, contentType: 'exact', bodySha256: 'exact', safeJson: route === '/health' ? {status: 'ok'} : undefined}]))};
}
test('finite inventory contains all42 unique M11–M24 owners separate from3M41 references', () => {
    assert.equal(registry.targets.length, 42); assert.equal(targetPaths.length, 42);
    assert.equal(new Set(registry.targets.map(row => row.identity)).size, 42);
    assert.ok(routes.includes('/theory/basic-grammar/word-order/inversion-basics'));
    assert.ok(routes.includes('/en/theory/basic-grammar/word-order/inversion-basics'));
});
test('initial independent evidence cannot be replaced by a failed/incomplete baseline', () => {
    const before = evidence(); before.pass = false; assert.throws(() => compare(before, evidence()));
});
test('presentation may change markup, while content banks practice and anchors stay protected', () => {
    const before = evidence(), after = structuredClone(before); after.at = 'after';
    for (const row of after.rows.filter(row => targetPaths.includes(row.path))) {
        row.learner.textSha256 = 'different whitespace/numeric UI only'; row.learner.anchors.unshift('new-native-wrapper');
    }
    assert.equal(compare(before, after).pass, true);
});
for (const [index, target] of registry.targets.entries()) {
    for (const [name, mutate] of Object.entries({
        'loss or duplication of example/translation': row => {row.learner.words.Example++;},
        'changed learning source order': row => {row.learner.orderedWords.reverse();},
        'lost table column/cell relationship': row => {row.learner.tables[0].rows[0] = ['A', 'B C'];},
        'own-detail reassignment': row => {row.learner.details[0].pointAnchor = 'wrong point';},
        'lost practice answer alias or scoring': row => {row.learner.practice[0].stateSha256 = 'changed';},
        'lost original anchor': row => {row.learner.anchors.pop();},
        'changed exact SEO title': row => {row.meta.title = 'changed';},
        'changed structured data': row => {row.jsonLd[0].value['@type'] = 'wrong';},
    })) {
        test('owner' + (index + 1) + ' ' + target.slug + ' rejects ' + name, () => {
            const before = evidence(), after = structuredClone(before); mutate(after.rows[index]);
            assert.throws(() => compare(before, after));
        });
    }
}
test('shared styling cannot mutate EN/PL or M41/control content', () => {
    const before = evidence(), after = structuredClone(before);
    after.rows.find(row => row.path.startsWith('/en/')).learner.words.Example++;
    assert.throws(() => compare(before, after));
});
test('ordered sitemap/robots changes are rejected', () => {
    const before = evidence(), after = structuredClone(before); after.extras['/sitemap.xml'].bodySha256 = 'changed';
    assert.throws(() => compare(before, after));
});
test('reader does not save scripts/styles/noscript duplicate learning copies', () => {
    const dom = new JSDOM('<main data-theory-main><h2>Rule</h2><p>Example переклад</p><script>secret</script><noscript>copy</noscript><style>style</style>'
        + '<span data-theory-ui>UI</span><article id="point"><div data-theory-native-extension><details><summary>Докладніше</summary>'
        + '<div class="theory-point-fragment" id="detail">Own detail</div></details></div></article></main>');
    try {
        const result = learner(dom.window.document);
        assert.deepEqual(result.details, [{id: 'detail', fragmentId: 'detail', text: 'Own detail', pointAnchor: 'point', pointKey: null, pointIndex: null}]);
        assert.ok(!result.words.secret && !result.words.copy && !result.words.style && !result.words.UI);
    } finally {dom.window.close();}
});
test('JSON-LD parser records valid data and exact invalid errors without inventing correctness', () => {
    const dom = new JSDOM('<script type="application/ld+json">{"@type":"WebPage"}</script><script type="application/ld+json">invalid</script>');
    try {const rows = jsonLd(dom.window.document); assert.equal(rows[0].valid, true); assert.equal(rows[1].valid, false);}
    finally {dom.window.close();}
});
test('guest URL guard rejects production/protocol relative/scheme payloads', () => {
    assertRoute('/theory');
    for (const route of ['//gramlyze.com/theory', 'https://gramlyze.com/theory', 'https://gramlyze.ub/', 'theory']) assert.throws(() => assertRoute(route));
});
test('learning bag binds apostrophes, casing and meaningful source numerals', () => {
    assert.deepEqual(words("1 Example hasn’t приклад 2"), {1: 1, 2: 1, Example: 1, 'hasn’t': 1, приклад: 1});
});
