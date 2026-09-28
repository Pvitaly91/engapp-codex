'use strict';
// M24 read-only HTTP acceptance. The profile is fixed; it cannot navigate elsewhere.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const shared = require('./seo-m11-local.cjs');

const lessons = Object.freeze({
    'present-perfect-vs-present-perfect-continuous': {
        theory: '/theory/tenses/present-perfect-vs-present-perfect-continuous',
        test: '/test/tenses/present-perfect-vs-present-perfect-continuous'
    },
    'narrative-tenses': {
        theory: '/theory/tenses/narrative-tenses',
        test: '/test/tenses/narrative-tenses'
    },
    'b1-mixed-revision': {
        theory: '/theory/mixed-revision/b1-mixed-revision',
        test: '/test/mixed-revision/b1-mixed-revision'
    }
});
const slugs = Object.freeze(Object.keys(lessons));
const theory = slug => lessons[slug].theory;
const test = slug => lessons[slug].test;
const controls = Object.freeze([
    '/theory/formal-english/nominal-style-and-information-density',
    '/theory/tenses/past-simple-vs-past-continuous'
]);
const course = '/courses/english-grammar-theory/lesson/tenses/narrative-tenses';
const paths = Object.freeze([...slugs.map(theory), ...slugs.map(test), course, ...controls, '/sitemap.xml']);
const profile = Object.freeze({id: 'm24', slugs, theory, test, course, paths, extendedChecks: true});
const decision = (url, method = 'GET', navigation = false) => shared.decision(url, method, navigation, profile);

function compare(before, after) {
    assert.equal(before.base, 'http://gramlyze.loc');
    assert.equal(after.base, before.base);
    assert.equal(before.package, 'm24');
    assert.equal(after.package, 'm24');
    assert.deepEqual(before.rows.map(row => row.path), paths);
    assert.deepEqual(after.rows.map(row => row.path), paths);
    const rows = before.rows.map((old, i) => {
        const now = after.rows[i];
        assert.equal(old.status, 200, old.path + ' before');
        assert.equal(now.status, 200, old.path + ' after');
        for (const field of ['location', 'contentType', 'xRobotsTag']) assert.deepEqual(now[field], old[field], old.path + ' ' + field);
        if (old.path === '/sitemap.xml') {
            assert.deepEqual(now.orderedLocs, old.orderedLocs);
            assert.equal(now.orderedSha256, old.orderedSha256);
        } else {
            const target = slugs.some(slug => theory(slug) === old.path);
            for (const key of Object.keys(old.metadata)) {
                if (!(target && ['description', 'ogDescription', 'twitterDescription'].includes(key))) {
                    assert.deepEqual(now.metadata[key], old.metadata[key], old.path + ' ' + key);
                }
            }
            if (controls.includes(old.path)) assert.equal(now.textSha256, old.textSha256, old.path + ' control changed');
            assert.deepEqual(now.testLinks, old.testLinks, old.path + ' test links');
            if (target) {
                assert.equal(now.selfChecks, 1, old.path + ' practice count');
                assert.equal(now.selfCheckQuestions, 6, old.path + ' question count');
                assert.equal(now.selfCheckKeys, 6, old.path + ' answer count');
                assert.equal(now.anchorPlaceholder, false, old.path + ' placeholder');
                assert.equal(now.metadata.description.length, 1);
                assert.deepEqual(now.metadata.ogDescription, now.metadata.description);
                assert.deepEqual(now.metadata.twitterDescription, now.metadata.description);
            }
        }
        return {path: old.path, status: now.status, textChanged: old.textSha256 !== now.textSha256};
    });
    return {at: new Date().toISOString(), package: 'm24', pass: true, rows};
}

if (require.main === module) {
    const [mode, dir, beforeLabel, afterLabel] = process.argv.slice(2);
    assert.ok(['capture', 'compare'].includes(mode));
    assert.match(beforeLabel || '', /^[a-z0-9-]+$/);
    fs.mkdirSync(dir, {recursive: true});
    if (mode === 'capture') {
        shared.capture(dir, beforeLabel, profile).catch(error => { console.error(error); process.exitCode = 1; });
    } else {
        assert.match(afterLabel || '', /^[a-z0-9-]+$/);
        const read = label => JSON.parse(fs.readFileSync(path.join(dir, label + '-http.json'), 'utf8'));
        const result = compare(read(beforeLabel), read(afterLabel));
        fs.writeFileSync(path.join(dir, afterLabel + '-comparison.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
        console.log(JSON.stringify(result, null, 2));
    }
}
module.exports = {lessons, slugs, theory, test, controls, course, paths, profile, decision, compare};
