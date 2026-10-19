'use strict';
// Fixed M13 inventory, verified against the working Page/course/test resolvers.
// Reuse M11's read-only runner, but deliberately expose no fixture mode.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const shared = require('./seo-m11-local.cjs');
const lessons = Object.freeze(Object.fromEntries(['cleft-sentences-emphasis', 'complex-noun-phrases', 'ellipsis-substitution-and-reference'].map(slug =>
    [slug, Object.freeze({theory: '/theory/sentence-structure/' + slug, test: '/test/sentence-structure/' + slug})])));
const controls = Object.freeze([
    '/theory/clauses-and-linking-words/linking-words-reason-result-contrast',
    '/theory/sentence-structure/cleft-sentences-basics',
]);
const references = Object.freeze([
    '/theory/basic-grammar/word-order/advanced-fronting-and-emphasis',
    '/theory/zaimennyky-ta-vkazivni-slova/one-ones',
    '/theory/clauses-and-linking-words/defining-relative-clauses',
]);
const slugs = Object.freeze(Object.keys(lessons));
const theory = slug => {assert.ok(Object.hasOwn(lessons, slug)); return lessons[slug].theory;};
const test = slug => {assert.ok(Object.hasOwn(lessons, slug)); return lessons[slug].test;};
const course = '/courses/english-grammar-theory/lesson/sentence-structure/cleft-sentences-emphasis';
const PATHS = Object.freeze([...slugs.map(theory), ...slugs.map(test), course, ...controls, ...references, '/sitemap.xml']);
const profile = Object.freeze({id: 'm13', slugs, theory, test, course, paths: PATHS, extendedChecks: true, richContent: true});
const MODES = Object.freeze(['capture', 'browser-applied', 'compare']);
const decision = (url, method = 'GET', navigation = false) => shared.decision(url, method, navigation, profile);

function compareCaptures(before, after) {
    for (const capture of [before, after]) {
        assert.equal(capture.base, 'http://gramlyze.loc');
        assert.equal(capture.package, 'm13');
        assert.deepEqual(capture.rows.map(r => r.path), PATHS);
    }
    const rows = before.rows.map((old, i) => {
        const next = after.rows[i];
        assert.equal(old.status, 200, old.path + ' before status');
        assert.equal(next.status, 200, old.path + ' after status');
        for (const field of ['location', 'contentType', 'xRobotsTag']) assert.deepEqual(next[field], old[field], old.path + ' ' + field);
        if (old.path === '/sitemap.xml') {
            assert.deepEqual(next.orderedLocs, old.orderedLocs, 'Ordered sitemap changed');
            assert.equal(next.orderedSha256, old.orderedSha256);
            return {path: old.path, unchanged: true, urls: next.orderedLocs.length, orderedSha256: next.orderedSha256};
        }
        const descriptionAllowed = slugs.some(slug => theory(slug) === old.path);
        for (const key of Object.keys(old.metadata)) {
            if (!(descriptionAllowed && ['description', 'ogDescription', 'twitterDescription'].includes(key))) {
                assert.deepEqual(next.metadata[key], old.metadata[key], old.path + ' ' + key);
            }
        }
        if (descriptionAllowed) {
            assert.equal(next.metadata.description.length, 1);
            assert.ok(next.metadata.description[0].trim());
            assert.deepEqual(next.metadata.ogDescription, next.metadata.description);
            assert.deepEqual(next.metadata.twitterDescription, next.metadata.description);
            assert.equal(next.anchorPlaceholder, false);
            assert.equal(next.selfChecks, 1);
            assert.equal(next.selfCheckQuestions, 6);
            assert.equal(next.selfCheckKeys, 6);
            assert.ok(next.richSections >= 5 && next.richExamples > 0, 'No silent plain renderer fallback');
        }
        if (controls.includes(old.path) || references.includes(old.path)) {
            assert.equal(next.textSha256, old.textSha256, 'Protected neighbour content: ' + old.path);
            assert.equal(next.richSections, old.richSections);
        }
        assert.deepEqual(next.testLinks, old.testLinks, old.path + ' main test links');
        return {path: old.path, protectedMetadataUnchanged: true, descriptionAllowed, descriptionChanged: JSON.stringify(next.metadata.description) !== JSON.stringify(old.metadata.description)};
    });
    return {at: new Date().toISOString(), base: after.base, package: 'm13', pass: true, before: before.at, after: after.at, rows};
}

if (require.main === module) {
    const [mode, dir, label, afterLabel] = process.argv.slice(2);
    assert.ok(MODES.includes(mode), 'M13 accepts only real capture/browser-applied/compare modes');
    assert.match(label || '', /^[a-z0-9-]+$/);
    fs.mkdirSync(dir, {recursive: true});
    if (mode === 'compare') {
        assert.match(afterLabel || '', /^[a-z0-9-]+$/);
        const read = name => JSON.parse(fs.readFileSync(path.join(dir, name + '-http.json'), 'utf8'));
        const result = compareCaptures(read(label), read(afterLabel));
        fs.writeFileSync(path.join(dir, afterLabel + '-comparison.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
        console.log(JSON.stringify(result, null, 2));
    } else {
        (mode === 'capture' ? shared.capture(dir, label, profile) : shared.browserChecks(dir, label, true, profile))
            .then(ok => {if (ok === false) process.exitCode = 1;})
            .catch(e => {console.error(e.message); process.exitCode = 1;});
    }
}
module.exports = {decision, PATHS, MODES, profile, controls, references, compareCaptures};
