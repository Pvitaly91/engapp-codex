'use strict';
// Fixed M22 inventory, verified against the working Page/course/test resolvers.
// Reuse M11's read-only runner, but deliberately expose no fixture mode.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const shared = require('./seo-m11-local.cjs');
const lessons = Object.freeze({
    "advanced-article-and-quantifier-nuance": {
        "theory": "/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance",
        "test": "/test/articles-and-quantifiers/advanced-article-and-quantifier-nuance",
        "minTableWidth": 1000
    },
    "precision-with-articles-and-determiners": {
        "theory": "/theory/articles-and-quantifiers/precision-with-articles-and-determiners",
        "test": "/test/articles-and-quantifiers/precision-with-articles-and-determiners",
        "minTableWidth": 1000
    },
    "advanced-collocation-and-lexical-choice": {
        "theory": "/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice",
        "test": "/test/vocabulary-and-collocations/advanced-collocation-and-lexical-choice",
        "minTableWidth": 1000
    }
});
const controls = Object.freeze(["/theory/relative-clauses/complex-relative-clauses","/theory/formal-english/nominalisation-formal-register"]);
const references = Object.freeze([
    "/theory/imennyky-artykli-ta-kilkist/articles-a-an-the",
    "/theory/imennyky-artykli-ta-kilkist/countable-vs-uncountable-nouns",
    "/theory/imennyky-artykli-ta-kilkist/quantifiers-much-many-a-lot-few-little",
    "/theory/zaimennyky-ta-vkazivni-slova/each-every-all",
    "/theory/imennyky-artykli-ta-kilkist/no-none-neither-either",
    "/theory/sentence-structure/complex-noun-phrases",
    "/theory/academic-english/stance-register-and-evaluation",
    "/theory/formal-english/paraphrase-and-reformulation"
]);
const slugs = Object.freeze(Object.keys(lessons));
const theory = slug => {assert.ok(Object.hasOwn(lessons, slug)); return lessons[slug].theory;};
const test = slug => {assert.ok(Object.hasOwn(lessons, slug)); return lessons[slug].test;};
const course = '/courses/english-grammar-theory/lesson/articles-and-quantifiers/advanced-article-and-quantifier-nuance';
const PATHS = Object.freeze([...slugs.map(theory), ...slugs.map(test), course, ...controls, ...references, '/sitemap.xml']);
const profile = Object.freeze({id: 'm22', slugs, theory, test, course, paths: PATHS, extendedChecks: true, richContent: true});
const MODES = Object.freeze(['capture', 'browser-applied', 'compare']);
const decision = (url, method = 'GET', navigation = false) => shared.decision(url, method, navigation, profile);

function assertMobileTables(report) {
    const mobile = report.rows.filter(row => row.mobile);
    assert.equal(mobile.length, 3);
    for (const row of mobile) {
        assert.ok(row.pass && !row.overflow, row.path + ' mobile acceptance');
        const lesson = Object.values(lessons).find(lesson => lesson.theory === row.path);
        assert.ok(lesson, 'Known M22 lesson');
        assert.ok(row.tableScroll?.contentWidth >= lesson.minTableWidth, row.path + ' content-specific readable table width');
        assert.ok(row.tableScroll.left > 0, row.path + ' actual local horizontal scroll');
    }
}

async function browserAcceptance(dir, label) {
    const ok = await shared.browserChecks(dir, label, true, profile);
    assertMobileTables(JSON.parse(fs.readFileSync(path.join(dir, label + '-browser.json'), 'utf8')));
    return ok;
}

// These older pages embed randomly selected practice questions in script text.
// Their full textContent hash is not a stable lesson/bank fingerprint.
const dynamicReferences = Object.freeze([references[0], references[1], references[2], references[4]]);
function assertProtectedData(evidence) {
    assert.ok(evidence?.before && evidence?.after, 'Real before/after DB fingerprints required for dynamic neighbours');
    const {before, after} = evidence;
    assert.ok(Object.keys(before.tables).length > 0);
    assert.deepEqual(after.tables, before.tables, 'Protected DB rows/banks changed');
    assert.equal(after.env_sha256, before.env_sha256, 'Working environment changed');
    assert.deepEqual(after.accepted_sources_match, before.accepted_sources_match);
    assert.ok(Object.keys(after.accepted_sources_match).length > 0);
    assert.ok(Object.values(after.accepted_sources_match).every(value => value === true));
}
function compareCaptures(before, after, evidence = null) {
    for (const capture of [before, after]) {
        assert.equal(capture.base, 'http://gramlyze.loc');
        assert.equal(capture.package, 'm22');
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
            if (dynamicReferences.includes(old.path) && next.textSha256 !== old.textSha256) {
                assertProtectedData(evidence);
            } else {
                assert.equal(next.textSha256, old.textSha256, 'Protected neighbour content: ' + old.path);
            }
            assert.equal(next.richSections, old.richSections);
        }
        assert.deepEqual(next.testLinks, old.testLinks, old.path + ' main test links');
        return {path: old.path, protectedMetadataUnchanged: true, descriptionAllowed, descriptionChanged: JSON.stringify(next.metadata.description) !== JSON.stringify(old.metadata.description), dynamicContentVerifiedByDb: dynamicReferences.includes(old.path) && next.textSha256 !== old.textSha256};
    });
    return {at: new Date().toISOString(), base: after.base, package: 'm22', pass: true, before: before.at, after: after.at, rows};
}

if (require.main === module) {
    const [mode, dir, label, afterLabel] = process.argv.slice(2);
    assert.ok(MODES.includes(mode), 'M22 accepts only real capture/browser-applied/compare modes');
    assert.match(label || '', /^[a-z0-9-]+$/);
    fs.mkdirSync(dir, {recursive: true});
    if (mode === 'compare') {
        assert.match(afterLabel || '', /^[a-z0-9-]+$/);
        const read = name => JSON.parse(fs.readFileSync(path.join(dir, name + '-http.json'), 'utf8'));
        const fingerprints = phase => JSON.parse(fs.readFileSync(path.join(dir, 'protected-' + phase + '.json'), 'utf8'));
        const evidence = {before: fingerprints('before'), after: fingerprints('after')};
        assertProtectedData(evidence);
        const result = compareCaptures(read(label), read(afterLabel), evidence);
        fs.writeFileSync(path.join(dir, afterLabel + '-comparison.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
        console.log(JSON.stringify(result, null, 2));
    } else {
        (mode === 'capture' ? shared.capture(dir, label, profile) : browserAcceptance(dir, label))
            .then(ok => {if (ok === false) process.exitCode = 1;})
            .catch(e => {console.error(e.message); process.exitCode = 1;});
    }
}
module.exports = {decision, PATHS, MODES, profile, controls, references, dynamicReferences, compareCaptures, assertMobileTables, assertProtectedData};
