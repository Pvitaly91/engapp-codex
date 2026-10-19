'use strict';
// Fixed M21 inventory, verified against the working Page/course/test resolvers.
// Reuse M11's read-only runner, but deliberately expose no fixture mode.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const shared = require('./seo-m11-local.cjs');
const lessons = Object.freeze({
    "advanced-gerund-infinitive-patterns": {
        "theory": "/theory/verb-patterns/advanced-gerund-infinitive-patterns",
        "test": "/test/verb-patterns/advanced-gerund-infinitive-patterns",
        "minTableWidth": 1000
    },
    "complex-relative-clauses": {
        "theory": "/theory/relative-clauses/complex-relative-clauses",
        "test": "/test/relative-clauses/complex-relative-clauses",
        "minTableWidth": 1000
    },
    "inversion-after-negative-adverbials": {
        "theory": "/theory/basic-grammar/word-order/inversion-after-negative-adverbials",
        "test": "/test/word-order/inversion-after-negative-adverbials",
        "minTableWidth": 1000
    }
});
const controls = Object.freeze(["/theory/modal-verbs/modal-perfect-and-deduction","/theory/basic-grammar/word-order/inversion-basics"]);
const references = Object.freeze([
    "/theory/verb-patterns/verbs-plus-gerund",
    "/theory/verb-patterns/verbs-plus-infinitive",
    "/theory/verb-patterns/stop-remember-forget-try-regret",
    "/theory/clauses-and-linking-words/participle-clauses-basics",
    "/theory/clauses-and-linking-words/participle-clauses",
    "/theory/sentence-structure/complex-noun-phrases",
    "/theory/basic-grammar/word-order/advanced-fronting-and-emphasis"
]);
const slugs = Object.freeze(Object.keys(lessons));
const theory = slug => {assert.ok(Object.hasOwn(lessons, slug)); return lessons[slug].theory;};
const test = slug => {assert.ok(Object.hasOwn(lessons, slug)); return lessons[slug].test;};
const course = '/courses/english-grammar-theory/lesson/verb-patterns/advanced-gerund-infinitive-patterns';
const PATHS = Object.freeze([...slugs.map(theory), ...slugs.map(test), course, ...controls, ...references, '/sitemap.xml']);
const profile = Object.freeze({id: 'm21', slugs, theory, test, course, paths: PATHS, extendedChecks: true, richContent: true});
const MODES = Object.freeze(['capture', 'browser-applied', 'compare']);
const decision = (url, method = 'GET', navigation = false) => shared.decision(url, method, navigation, profile);

function assertMobileTables(report) {
    const mobile = report.rows.filter(row => row.mobile);
    assert.equal(mobile.length, 3);
    for (const row of mobile) {
        assert.ok(row.pass && !row.overflow, row.path + ' mobile acceptance');
        const lesson = Object.values(lessons).find(lesson => lesson.theory === row.path);
        assert.ok(lesson, 'Known M21 lesson');
        assert.ok(row.tableScroll?.contentWidth >= lesson.minTableWidth, row.path + ' content-specific readable table width');
        assert.ok(row.tableScroll.left > 0, row.path + ' actual local horizontal scroll');
    }
}

async function browserAcceptance(dir, label) {
    const ok = await shared.browserChecks(dir, label, true, profile);
    assertMobileTables(JSON.parse(fs.readFileSync(path.join(dir, label + '-browser.json'), 'utf8')));
    return ok;
}

function compareCaptures(before, after) {
    for (const capture of [before, after]) {
        assert.equal(capture.base, 'http://gramlyze.loc');
        assert.equal(capture.package, 'm21');
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
    return {at: new Date().toISOString(), base: after.base, package: 'm21', pass: true, before: before.at, after: after.at, rows};
}

if (require.main === module) {
    const [mode, dir, label, afterLabel] = process.argv.slice(2);
    assert.ok(MODES.includes(mode), 'M21 accepts only real capture/browser-applied/compare modes');
    assert.match(label || '', /^[a-z0-9-]+$/);
    fs.mkdirSync(dir, {recursive: true});
    if (mode === 'compare') {
        assert.match(afterLabel || '', /^[a-z0-9-]+$/);
        const read = name => JSON.parse(fs.readFileSync(path.join(dir, name + '-http.json'), 'utf8'));
        const result = compareCaptures(read(label), read(afterLabel));
        fs.writeFileSync(path.join(dir, afterLabel + '-comparison.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
        console.log(JSON.stringify(result, null, 2));
    } else {
        (mode === 'capture' ? shared.capture(dir, label, profile) : browserAcceptance(dir, label))
            .then(ok => {if (ok === false) process.exitCode = 1;})
            .catch(e => {console.error(e.message); process.exitCode = 1;});
    }
}
module.exports = {decision, PATHS, MODES, profile, controls, references, compareCaptures, assertMobileTables};
