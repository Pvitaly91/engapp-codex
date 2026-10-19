'use strict';
// Finite live inventory from a verified Page-owner registry, not URL guessing.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto'), assert = require('node:assert/strict');
const { JSDOM } = require('jsdom');
const { metadata } = require('./seo-m28-local.cjs');
const old = require('./capture-m42-design-http.cjs');
const { BASE, PRIVATE, hashes } = require('./capture-theory-template.cjs');
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const REGISTRY_SHA = '0a2109e6aa0fc6ee0dba207f9eb2b864af94f08f8e1e604fdcb6008b41eef628';
const REQUEST_LOCALES_SHA = 'b20b9e6584e60a5bc9f5f0bdf3a3421f9083c5e9f9e9a2b613db776c5bc0255b';
function clean(document) {
    const main = document.querySelector('[data-theory-main]'); if (!main) return null;
    const clone = main.cloneNode(true), norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
    clone.querySelectorAll('script,style,noscript,[data-sentence-builder]').forEach(node => node.remove());
    const textNodes = [], walker = document.createTreeWalker(clone, 4); let textNode;
    while ((textNode = walker.nextNode())) if (norm(textNode.nodeValue)) textNodes.push(norm(textNode.nodeValue));
    return {
        text: norm(clone.textContent), html: clone.innerHTML,
        anchors: [...clone.querySelectorAll('[id]')].map(node => node.id),
        textNodes,
        headings: [...clone.querySelectorAll('h2,h3,h4')].map(node => ({ tag: node.tagName, text: norm(node.textContent) })),
        paragraphs: [...clone.querySelectorAll('p')].map(node => norm(node.textContent)),
        details: [...clone.querySelectorAll('details')].map(node => ({ id: node.id, text: norm(node.textContent), summary: norm(node.querySelector('summary')?.textContent) })),
        tables: [...clone.querySelectorAll('table')].map(table => ({ heads: [...table.querySelectorAll('thead th')].map(node => norm(node.textContent)), rows: [...table.querySelectorAll('tbody tr')].map(row => [...row.children].map(node => norm(node.textContent))) })),
        practice: [...clone.querySelectorAll('[x-data]')].filter(node => /practice/iu.test(node.getAttribute('x-data'))).map(node => ({ state: node.getAttribute('x-data'), text: norm(node.textContent) })),
        forms: clone.querySelectorAll('.theory-item.group.relative,[data-m45-form-card]').length,
        nativeItems: clone.querySelectorAll('.theory-item').length,
        sections: clone.querySelectorAll('.theory-section-card').length,
        canonicalComponents: Object.fromEntries([...new Set([...clone.querySelectorAll('[data-theory-component]')].map(node => node.dataset.theoryComponent))].sort().map(kind => [kind, clone.querySelectorAll('[data-theory-component="' + kind + '"]').length])),
        fallbackMarkers: [...clone.querySelectorAll('*')].flatMap(node => [...node.attributes].filter(attribute => /fallback/u.test(attribute.name)).map(attribute => ({ name: attribute.name, value: attribute.value }))),
    };
}
async function get(target, dir) {
    const row = { id: target.id, owner: target.owner, locale: target.locale, path: target.path, packages: target.packages, views: target.views, startedAt: new Date().toISOString(), attempts: [], pass: false };
    for (let attempt = 1; attempt <= 2; attempt++) {
        try {
            assert.equal(new URL(BASE + row.path).origin, BASE); const response = await fetch(BASE + row.path, { redirect: 'manual', signal: AbortSignal.timeout(45000), headers: { Accept: 'text/html', Connection: 'close' } });
            const html = await response.text(), dom = new JSDOM(html); row.attempts.push({ attempt, status: response.status });
            try { Object.assign(row, { status: response.status, url: BASE + row.path, contentType: response.headers.get('content-type'), xRobots: response.headers.get('x-robots-tag'), location: response.headers.get('location'), metadata: metadata(dom.window.document), jsonLd: old.jsonLd(dom.window.document), learner: clean(dom.window.document), originalLearner: old.learner(dom.window.document), bodySha256: sha(html), assets: [...dom.window.document.querySelectorAll('script[src],link[rel=stylesheet]')].map(node => node.src || node.href) }); } finally { dom.window.close(); }
            row.pass = row.status === 200 && Boolean(row.learner) && row.metadata.h1.length === 1; break;
        } catch (error) { row.attempts.push({ attempt, error: error.message }); }
    }
    row.finishedAt = new Date().toISOString(); fs.writeFileSync(path.join(dir, row.id + '-' + row.locale + '.json'), JSON.stringify(row) + '\n', { flag: 'wx' }); console.log(JSON.stringify({ id: row.id, locale: row.locale, status: row.status, pass: row.pass, errors: row.attempts.filter(item => item.error) })); return row;
}
async function run(mode, label, resumeLabel = null) {
    assert.ok(['--before', '--after', '--pilot-after', '--diagnostic-after'].includes(mode)); assert.match(label, /^registry-http-(?:before|after|pilot-after)-v[1-9][0-9]*$/u);
    const bytes = fs.readFileSync(path.join(PRIVATE, 'registry-before-v1.json')); assert.equal(sha(bytes), REGISTRY_SHA);
    const registry = JSON.parse(bytes); let targets = registry.registry.flatMap(page => page.variants.map(variant => ({ id: page.page_id, owner: page.owner, locale: variant.locale, path: variant.url, packages: variant.package_groups, views: variant.effective_views })));
    assert.equal(targets.length, 664); assert.equal(new Set(targets.map(target => target.path)).size, 664); assert.ok(targets.every(target => target.id && target.path.startsWith('/')));
    if (mode === '--pilot-after') {
        const matrix = fs.readFileSync(path.join(PRIVATE, 'browser-before-v1/manifest.json')), representatives = fs.readFileSync(path.join(PRIVATE, 'representatives-before-v1/manifest.json'));
        assert.equal(sha(matrix), 'c11b8cc7e287b3ba1463672780349bb274cc7caaea1d15945a1b3a2f8990e485');
        assert.equal(sha(representatives), '0a8d1bf2f593d30de4553ace8443e2e0b83adf39932e780308de70cd8bcdb02e');
        const paths = new Set([...JSON.parse(matrix).rows, ...JSON.parse(representatives).rows].map(row => row.path));
        targets = targets.filter(target => paths.has(target.path)); assert.equal(targets.length, paths.size);
    }
    const dir = path.join(PRIVATE, label); assert.equal(fs.existsSync(dir), false); fs.mkdirSync(dir, { recursive: true });
    const report = { mode: ['--pilot-after', '--diagnostic-after'].includes(mode) ? '--after' : mode, diagnosticOnly: mode === '--diagnostic-after', pilot: mode === '--pilot-after', label, base: BASE, startedAt: new Date().toISOString(), registrySha256: REGISTRY_SHA, owners: new Set(targets.map(target => target.id)).size, targets: targets.length, sourcesBefore: hashes(), rows: [], pass: false };
    if (resumeLabel) {
        assert.equal(mode, '--diagnostic-after'); assert.match(resumeLabel, /^registry-http-after-v[1-9][0-9]*$/u);
        const previousDir = path.join(PRIVATE, resumeLabel), bytes = fs.readFileSync(path.join(previousDir, 'manifest.json')), previous = JSON.parse(bytes);
        assert.equal(previous.mode, '--after'); assert.equal(previous.registrySha256, REGISTRY_SHA); assert.deepEqual(previous.sourcesBefore, report.sourcesBefore, 'Resume diagnostic only across exactly unchanged served sources');
        for (const row of previous.rows) { assert.ok(targets.some(target => row.file === target.id + '-' + target.locale + '.json')); fs.copyFileSync(path.join(previousDir, row.file), path.join(dir, row.file), fs.constants.COPYFILE_EXCL); report.rows.push(row); }
        report.resumed = { label: resumeLabel, manifestSha256: sha(bytes), immutableRows: previous.rows.length };
    }
    const remaining = targets.filter(target => !report.rows.some(row => row.file === target.id + '-' + target.locale + '.json'));
    try {
        for (let index = 0; index < remaining.length; index += 3) {
            const rows = await Promise.all(remaining.slice(index, index + 3).map(target => get(target, dir))); report.rows.push(...rows.map(({ learner, originalLearner, ...row }) => ({ ...row, file: row.id + '-' + row.locale + '.json', learnerSha256: learner ? sha(JSON.stringify(learner)) : null, originalLearnerSha256: originalLearner ? sha(JSON.stringify(originalLearner)) : null })));
            if (mode !== '--diagnostic-after') assert.ok(rows.every(row => row.pass), 'Stop at the first failed live registry batch; preserve its exact responses and partial manifest.');
        }
        report.sourcesAfter = hashes(); assert.deepEqual(report.sourcesAfter, report.sourcesBefore); report.pass = !report.diagnosticOnly && report.rows.length === targets.length && report.rows.every(row => row.pass);
    } finally { report.finishedAt = new Date().toISOString(); fs.writeFileSync(path.join(dir, 'manifest.json'), JSON.stringify(report, null, 2) + '\n', { flag: 'wx' }); }
    console.log(JSON.stringify({ directory: dir, pass: report.pass, total: report.rows.length, passed: report.rows.filter(row => row.pass).length, sha256: sha(fs.readFileSync(path.join(dir, 'manifest.json'))) })); return report;
}
async function controls(label) {
    assert.match(label, /^controls-(?:before|after)-v[1-9][0-9]*$/u); const dir = path.join(PRIVATE, label); assert.equal(fs.existsSync(dir), false); fs.mkdirSync(dir, { recursive: true });
    const paths = ['/', '/theory', '/theory/tenses', '/theory/maibutni-formy', '/courses/english-grammar-theory', '/test/past-perfect-continuous/forms', '/test/tenses/stative-verbs', '/test/maibutni-formy/future-perfect-vs-future-continuous'];
    const report = { base: BASE, label, startedAt: new Date().toISOString(), sourcesBefore: hashes(), rows: [], pass: false };
    for (const route of paths) {
        const row = { path: route, pass: false }; report.rows.push(row);
        try { const response = await fetch(BASE + route, { redirect: 'manual', signal: AbortSignal.timeout(route === '/theory/tenses' ? 180000 : 60000), headers: { Accept: 'text/html', Connection: 'close' } }); const body = await response.text(), dom = new JSDOM(body);
            try { const main = dom.window.document.querySelector('main')?.cloneNode(true); if (main) main.querySelectorAll('script,style,noscript,input,textarea').forEach(node => node.remove());
                Object.assign(row, { status: response.status, location: response.headers.get('location'), metadata: metadata(dom.window.document), jsonLd: old.jsonLd(dom.window.document), bodySha256: sha(body), text: main ? main.textContent.replace(/\s+/gu, ' ').trim() : null, anchors: main ? [...main.querySelectorAll('[id]')].map(node => node.id) : [], headings: main ? [...main.querySelectorAll('h1,h2,h3,h4')].map(node => ({ tag: node.tagName, text: node.textContent.replace(/\s+/gu, ' ').trim() })) : [], assets: [...dom.window.document.querySelectorAll('script[src],link[rel=stylesheet]')].map(node => node.src || node.href) });
                row.pass = row.status === 200;
            } finally { dom.window.close(); }
        } catch (error) { row.error = error.message; }
        console.log(JSON.stringify({ control: row.path, status: row.status, pass: row.pass, error: row.error || null }));
    }
    report.sourcesAfter = hashes(); assert.deepEqual(report.sourcesAfter, report.sourcesBefore); report.finishedAt = new Date().toISOString(); report.pass = report.rows.every(row => row.pass); fs.writeFileSync(path.join(dir, 'manifest.json'), JSON.stringify(report, null, 2) + '\n', { flag: 'wx' }); return report;
}
async function fallback(label) {
    assert.match(label, /^fallback-http-after-v[1-9][0-9]*$/u);
    const bytes = fs.readFileSync(path.join(PRIVATE, 'request-locales-v1.json')); assert.equal(sha(bytes), REQUEST_LOCALES_SHA);
    const inventory = JSON.parse(bytes), targets = inventory.fallback_registry.map(row => ({ id: row.page_id, owner: row.owner, locale: row.requested_locale, path: row.url, contentLocale: row.content_locale })); assert.equal(targets.length, 98);
    const dir = path.join(PRIVATE, label); assert.equal(fs.existsSync(dir), false); fs.mkdirSync(dir, { recursive: true });
    const report = { mode: '--after', label, base: BASE, startedAt: new Date().toISOString(), requestLocalesSha256: REQUEST_LOCALES_SHA, baselineLimitation: 'These 98 requested-locale fallbacks have verified immutable BEFORE content owners and unchanged routing/locale-selection sources, but no independent live HTTP BEFORE. They are not counted among the 664 paired live comparisons.', sourcesBefore: hashes(), rows: [], pass: false };
    try {
        for (let index = 0; index < targets.length; index += 2) {
            const rows = await Promise.all(targets.slice(index, index + 2).map(target => get(target, dir)));
            report.rows.push(...rows.map(({ learner, originalLearner, ...row }) => ({ ...row, contentLocale: 'uk', file: row.id + '-' + row.locale + '.json', learnerSha256: learner ? sha(JSON.stringify(learner)) : null })));
        }
        report.sourcesAfter = hashes(); assert.deepEqual(report.sourcesAfter, report.sourcesBefore); report.pass = report.rows.length === 98 && report.rows.every(row => row.pass);
    } finally { report.finishedAt = new Date().toISOString(); fs.writeFileSync(path.join(dir, 'manifest.json'), JSON.stringify(report, null, 2) + '\n', { flag: 'wx' }); }
    console.log(JSON.stringify({ directory: dir, pass: report.pass, passed: report.rows.filter(row => row.pass).length, total: report.rows.length, sha256: sha(fs.readFileSync(path.join(dir, 'manifest.json'))) })); return report;
}
if (require.main === module) { const args = process.argv.slice(2), work = args[0] === '--controls' ? controls(args[1]) : args[0] === '--fallback' ? fallback(args[1]) : run(...args); work.then(report => { if (!report.pass) process.exitCode = 1; }).catch(error => { console.error(error.stack); process.exitCode = 1; }); }
module.exports = { run, clean, controls, fallback };
