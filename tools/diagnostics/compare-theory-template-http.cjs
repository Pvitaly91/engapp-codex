'use strict';
// Independent immutable live BEFORE/AFTER comparison. Report discrepancies;
// do not rewrite expected content, source identities, classes, or ARIA.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto'), assert = require('node:assert/strict');
const { JSDOM } = require('jsdom');
const { stablePracticeText, semanticTables } = require('./capture-theory-template.cjs');
const PRIVATE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/theory-template-local';
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
function normalizedLearning(html) {
    const dom = new JSDOM('<main>' + html + '</main>'), root = dom.window.document.querySelector('main'), excluded = [];
    try {
        root.querySelectorAll('[data-theory-ui],.theory-section-number').forEach(node => { excluded.push({ selectorRole: node.classList.contains('theory-section-number') ? 'section-number-ui' : 'explicit-theory-ui', text: node.textContent.replace(/\s+/gu, ' ').trim() }); node.remove(); });
        for (const node of root.querySelectorAll('summary')) {
            const text = node.textContent.replace(/\s+/gu, ' ').trim();
            if (node.classList.contains('theory-section-toggle') || node.parentElement.hasAttribute('data-m45-detail') || /^(?:Теги|Tags|Tagi)\s*\(/u.test(text)) { excluded.push({ selectorRole: 'identified-disclosure-or-tags-ui', text }); node.remove(); }
        }
        for (const node of root.querySelectorAll('.theory-example > span.flex-shrink-0')) if (node.textContent.trim() === '💬') { excluded.push({ selectorRole: 'native-example-chat-icon', text: '💬' }); node.remove(); }
        for (const node of root.querySelectorAll('.rounded-full')) if (/^\s*\d+\s*$/u.test(node.textContent)) { excluded.push({ selectorRole: 'numeric-round-marker-ui', text: node.textContent.trim() }); node.remove(); }
        const parts = [], walker = dom.window.document.createTreeWalker(root, 4); let part;
        while ((part = walker.nextNode())) parts.push(part.nodeValue);
        const text = parts.join(' ').replace(/\s+/gu, ' ').trim();
        const words = text.match(/[\p{L}\p{N}]+(?:[’'\-][\p{L}\p{N}]+)*/gu) || [];
        return { text, words, punctuationSensitive: text.replace(/\s+/gu, ''), excluded, count: words.length };
    } finally { dom.window.close(); }
}
function diff(left, right) { try { assert.deepEqual(left, right); return null; } catch { return { before: left, after: right }; } }
async function compare(beforeLabel, afterLabel, outName, diagnostic = false) {
    assert.match(beforeLabel, /^registry-http-before-v[1-9][0-9]*$/u); assert.match(afterLabel, /^registry-http-(?:after|pilot-after)-v[1-9][0-9]*$/u); assert.match(outName, /^registry-http-(?:pilot-)?comparison-v[1-9][0-9]*\.json$/u);
    const beforeDir = path.join(PRIVATE, beforeLabel), afterDir = path.join(PRIVATE, afterLabel), beforeBytes = fs.readFileSync(path.join(beforeDir, 'manifest.json')), afterBytes = fs.readFileSync(path.join(afterDir, 'manifest.json'));
    const before = JSON.parse(beforeBytes), after = JSON.parse(afterBytes); assert.equal(before.mode, '--before'); assert.equal(before.pass, true); assert.equal(after.mode, '--after'); assert.ok(after.pass || diagnostic); assert.equal(after.rows.length, after.targets); assert.equal(before.registrySha256, after.registrySha256);
    const report = { before: beforeLabel, after: afterLabel, diagnosticOnly: diagnostic, pilot: Boolean(after.pilot), beforeSha256: sha(beforeBytes), afterSha256: sha(afterBytes), compared: 0, exact: 0, rows: [], pass: false,
        normalization: 'Only explicit data-theory-ui, identified native disclosure/tag summaries, section numbers, numeric rounded rule markers and the exact native example chat icon span are separated. Author summaries are retained. No CSS classes, structure or ARIA are removed from stored DOM; those remain in immutable per-page artifacts. Both ordered words and a punctuation/operator-sensitive character projection are checked, complemented by punctuation-sensitive per-cell tables, exact practice payloads and finite author-field checks. The character projection ignores whitespace only, never formula arrows/operators or learner punctuation.' };
    for (const row of before.rows) {
        if (after.pilot && !after.rows.some(candidate => candidate.file === row.file)) continue;
        const a = JSON.parse(fs.readFileSync(path.join(beforeDir, row.file))), b = JSON.parse(fs.readFileSync(path.join(afterDir, row.file))), differences = {};
        for (const key of ['id', 'owner', 'locale', 'path', 'status', 'url', 'contentType', 'xRobots', 'location', 'metadata', 'jsonLd']) { const changed = diff(a[key], b[key]); if (changed) differences[key] = changed; }
        if (!b.learner) { report.rows.push({ id: a.id, path: a.path, locale: a.locale, pass: false, differences: { ...differences, missingLearner: true } }); report.compared++; continue; }
        { const changed = diff(semanticTables(a.learner.html), semanticTables(b.learner.html)); if (changed) differences.tables = changed; }
        { const changed = diff(a.learner.practice.map(row => row.state), b.learner.practice.map(row => row.state)); if (changed) differences.practiceState = changed; }
        { const changed = diff(stablePracticeText(a.learner.html), stablePracticeText(b.learner.html)); if (changed) differences.practiceStaticText = changed; }
        if (a.learner.details.length !== b.learner.details.length) differences.detailCount = { before: a.learner.details.length, after: b.learner.details.length };
        const missingAnchors = a.learner.anchors.filter(id => !b.learner.anchors.includes(id));
        if (missingAnchors.length) differences.anchors = { missing: missingAnchors };
        const addedAnchors = b.learner.anchors.filter(id => !a.learner.anchors.includes(id));
        const introducedDuplicates = [...new Set(b.learner.anchors)].filter(id => b.learner.anchors.filter(found => found === id).length > Math.max(1, a.learner.anchors.filter(found => found === id).length));
        if (introducedDuplicates.length) differences.duplicateAnchors = introducedDuplicates;
        const old = normalizedLearning(a.learner.html), current = normalizedLearning(b.learner.html);
        if (JSON.stringify(old.words) !== JSON.stringify(current.words)) {
            let at = 0; while (at < old.words.length && old.words[at] === current.words[at]) at++;
            differences.orderedLearningWords = { firstDifference: at, beforeCount: old.words.length, afterCount: current.words.length, before: old.words.slice(Math.max(0, at - 8), at + 24), after: current.words.slice(Math.max(0, at - 8), at + 24) };
        }
        if (old.punctuationSensitive !== current.punctuationSensitive) {
            let at = 0; while (at < old.punctuationSensitive.length && old.punctuationSensitive[at] === current.punctuationSensitive[at]) at++;
            differences.punctuationSensitiveLearning = { firstDifference: at, beforeLength: old.punctuationSensitive.length, afterLength: current.punctuationSensitive.length, before: old.punctuationSensitive.slice(Math.max(0, at - 60), at + 200), after: current.punctuationSensitive.slice(Math.max(0, at - 60), at + 200) };
        }
        const entry = { id: a.id, path: a.path, locale: a.locale, pass: Object.keys(differences).length === 0, counts: { before: old.count, after: current.count }, anchors: { before: a.learner.anchors.length, after: b.learner.anchors.length, retained: a.learner.anchors.length - missingAnchors.length, added: addedAnchors }, detailCounts: { before: a.learner.details.length, after: b.learner.details.length }, practiceCounts: { before: a.learner.practice.length, after: b.learner.practice.length }, tableCounts: { before: a.learner.tables.length, after: b.learner.tables.length }, differences };
        report.rows.push(entry); report.compared++; if (entry.pass) report.exact++;
        // Let jsdom's queued window cleanup run between finite pages, rather
        // than retaining hundreds of closed documents in one synchronous turn.
        await new Promise(resolve => setImmediate(resolve));
        if (report.compared % 20 === 0) { if (global.gc) global.gc(); console.log(JSON.stringify({ compared: report.compared, exact: report.exact })); }
    }
    report.totalsAcrossLocaleVariants = Object.fromEntries(['counts', 'detailCounts', 'practiceCounts', 'tableCounts', 'anchors'].map(key => [key, { before: report.rows.reduce((sum, row) => sum + (row[key]?.before || 0), 0), after: report.rows.reduce((sum, row) => sum + (row[key]?.after || 0), 0) }]));
    report.pass = !diagnostic && report.compared === (after.pilot ? after.targets : before.targets) && report.exact === report.compared; fs.writeFileSync(path.join(PRIVATE, outName), JSON.stringify(report, null, 2) + '\n', { flag: 'wx' });
    console.log(JSON.stringify({ pass: report.pass, compared: report.compared, exact: report.exact, failures: report.rows.filter(row => !row.pass).map(row => ({ id: row.id, locale: row.locale, fields: Object.keys(row.differences) })), sha256: sha(fs.readFileSync(path.join(PRIVATE, outName))) })); return report;
}
function compareControls(beforeLabel, afterLabel, outName) {
    assert.match(beforeLabel, /^controls-before-v[1-9][0-9]*$/u); assert.match(afterLabel, /^controls-after-v[1-9][0-9]*$/u); assert.match(outName, /^controls-comparison-v[1-9][0-9]*\.json$/u);
    const beforeBytes = fs.readFileSync(path.join(PRIVATE, beforeLabel, 'manifest.json')), afterBytes = fs.readFileSync(path.join(PRIVATE, afterLabel, 'manifest.json'));
    const before = JSON.parse(beforeBytes), after = JSON.parse(afterBytes); assert.ok(before.pass && after.pass);
    const report = { before: beforeLabel, after: afterLabel, beforeSha256: sha(beforeBytes), afterSha256: sha(afterBytes), rows: [], pass: false };
    for (const old of before.rows) {
        const current = after.rows.find(row => row.path === old.path); assert.ok(current);
        const differences = {};
        for (const key of ['status', 'location', 'metadata', 'jsonLd', 'text', 'anchors', 'headings']) { const changed = diff(old[key], current[key]); if (changed) differences[key] = changed; }
        report.rows.push({ path: old.path, pass: Object.keys(differences).length === 0, differences, beforeAssets: old.assets, afterAssets: current.assets });
    }
    report.pass = report.rows.length === 8 && report.rows.every(row => row.pass); fs.writeFileSync(path.join(PRIVATE, outName), JSON.stringify(report, null, 2) + '\n', { flag: 'wx' });
    console.log(JSON.stringify({ pass: report.pass, passed: report.rows.filter(row => row.pass).length, total: report.rows.length, failures: report.rows.filter(row => !row.pass).map(row => ({ path: row.path, fields: Object.keys(row.differences) })), sha256: sha(fs.readFileSync(path.join(PRIVATE, outName))) })); return report;
}
async function compareFallback(beforeLabel, afterLabel, outName) {
    assert.match(beforeLabel, /^registry-http-before-v[1-9][0-9]*$/u); assert.match(afterLabel, /^fallback-http-after-v[1-9][0-9]*$/u); assert.match(outName, /^fallback-http-comparison-v[1-9][0-9]*\.json$/u);
    const beforeDir = path.join(PRIVATE, beforeLabel), afterDir = path.join(PRIVATE, afterLabel), bytes = fs.readFileSync(path.join(afterDir, 'manifest.json')), after = JSON.parse(bytes);
    assert.equal(after.pass, true); assert.equal(after.rows.length, 98);
    const fallbackLearningHtml = html => {
        const dom = new JSDOM('<main>' + html + '</main>');
        try {
            const document = dom.window.document, hero = document.querySelector('.theory-hero'), blocks = document.querySelector('.theory-content-blocks');
            assert.ok(hero && blocks, 'Verified owner hero and learning-block containers');
            const level = hero.querySelector('span.soft-accent');
            if (level) { assert.match(level.textContent.trim(), /^(?:Рівень|Level|Poziom)\s+[ABC][12]/u); level.textContent = level.textContent.trim().replace(/^(?:Рівень|Level|Poziom)\s+/u, ''); }
            return [hero.outerHTML, document.querySelector('.theory-rules')?.outerHTML || '', blocks.outerHTML].join('\n');
        } finally { dom.window.close(); }
    };
    const report = { beforeContent: beforeLabel, after: afterLabel, afterSha256: sha(bytes), limitation: 'AFTER-only requested-locale HTTP routes. Learner content is compared with the same verified owner UK content captured independently BEFORE; no live BEFORE request-locale metadata comparison is claimed. Cross-locale text checks cover the hero, rule cards and learning blocks; the translated UI prefix Рівень/Level/Poziom is separated while its CEFR value is retained. Localized mobile navigation and related-test chrome are not learner content. Full raw HTML, metadata and anchors remain in artifacts.', rows: [], pass: false };
    for (const item of after.rows) {
        const old = JSON.parse(fs.readFileSync(path.join(beforeDir, item.id + '-uk.json'))), current = JSON.parse(fs.readFileSync(path.join(afterDir, item.file))), differences = {};
        assert.equal(old.pass, true); assert.equal(current.id, old.id); assert.equal(current.owner, old.owner);
        const a = normalizedLearning(fallbackLearningHtml(old.learner.html)), b = normalizedLearning(fallbackLearningHtml(current.learner.html));
        for (const [key, left, right] of [
            ['learnerWords', a.words, b.words], ['learnerPunctuation', a.punctuationSensitive, b.punctuationSensitive],
            ['tables', semanticTables(old.learner.html), semanticTables(current.learner.html)],
            ['practiceState', old.learner.practice.map(row => row.state), current.learner.practice.map(row => row.state)],
            ['practiceStaticText', stablePracticeText(old.learner.html), stablePracticeText(current.learner.html)],
            ['detailCount', old.learner.details.length, current.learner.details.length],
        ]) { const change = diff(left, right); if (change) differences[key] = change; }
        const missing = old.learner.anchors.filter(id => !current.learner.anchors.includes(id)); if (missing.length) differences.missingAnchors = missing;
        report.rows.push({ id: item.id, requestedLocale: item.locale, contentLocale: item.contentLocale, path: item.path, pass: !Object.keys(differences).length, differences });
        await new Promise(resolve => setImmediate(resolve));
        if (report.rows.length % 20 === 0 && global.gc) global.gc();
    }
    report.pass = report.rows.every(row => row.pass); fs.writeFileSync(path.join(PRIVATE, outName), JSON.stringify(report, null, 2) + '\n', { flag: 'wx' });
    console.log(JSON.stringify({ pass: report.pass, compared: report.rows.length, exact: report.rows.filter(row => row.pass).length, failures: report.rows.filter(row => !row.pass).map(row => ({ id: row.id, locale: row.requestedLocale, fields: Object.keys(row.differences) })), sha256: sha(fs.readFileSync(path.join(PRIVATE, outName))) })); return report;
}
if (require.main === module) { const args = process.argv.slice(2); const result = args[0] === '--controls' ? compareControls(...args.slice(1)) : args[0] === '--fallback' ? compareFallback(...args.slice(1)) : args[0] === '--diagnostic' ? compare(...args.slice(1), true) : compare(...args); Promise.resolve(result).then(report => { if (!report.pass) process.exitCode = 1; }).catch(error => { console.error(error.stack); process.exitCode = 1; }); }
module.exports = { compare, normalizedLearning, compareControls, compareFallback };
