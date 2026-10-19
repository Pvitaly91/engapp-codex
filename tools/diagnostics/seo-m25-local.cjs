'use strict';
// GET-only actual working-server fidelity evidence. No cookie, auth or Referer.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const BASE = 'http://gramlyze.loc';
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const normalize = value => value.replace(/\s+/g, ' ').trim();
const privateDir = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m25-local';
function orderedText(node) {
    if (node.nodeType === 3) return node.nodeValue;
    const block = /^(ADDRESS|ARTICLE|ASIDE|BLOCKQUOTE|DIV|DL|DT|DD|FIGCAPTION|FIGURE|FOOTER|H[1-6]|HEADER|HR|LI|MAIN|NAV|OL|P|PRE|SECTION|SUMMARY|TABLE|TD|TH|TR|UL|DETAILS)$/.test(node.tagName || '');
    return (block ? ' ' : '') + [...node.childNodes].map(orderedText).join('') + (block ? ' ' : '');
}
function cleaned(node) {
    const copy = node.cloneNode(true);
    // Only implementation-only UI explicitly marked by the renderer is exempt.
    copy.querySelectorAll('script,style,[data-theory-ui]').forEach(n => n.remove());
    return copy;
}
function content(node) {
    const copy = cleaned(node);
    const ids = [...copy.querySelectorAll('[id]')].map(n => n.id);
    return {
        orderedText: normalize(orderedText(copy)),
        hrefs: [...copy.querySelectorAll('a[href]')].map(n => n.getAttribute('href')),
        anchors: [...new Set(ids)], duplicateIds: ids.filter((id, i) => ids.indexOf(id) !== i),
        tables: [...copy.querySelectorAll('table')].map(table => [...table.querySelectorAll('tr')].map(row =>
            [...row.children].filter(n => ['TD', 'TH'].includes(n.tagName)).map(n => normalize(orderedText(n))))),
        details: [...copy.querySelectorAll('details')].map(n => ({text: normalize(orderedText(n)), open: n.hasAttribute('open')})),
    };
}
function authoredHeaders(node, titles) {
    const copy = node.cloneNode(true), restored = [], errors = [];
    const blocks = new Map();
    for (const block of copy.querySelectorAll('[id^="block-"]')) {
        if (!blocks.has(block.id)) blocks.set(block.id, block);
    }
    for (const [id, block] of blocks) {
        const title = titles?.[id];
        if (!title) continue;
        const heading = block.querySelector('h2');
        if (!heading) continue;
        const exact = normalize(title);
        if (heading.classList.contains('theory-section-title')) {
            const current = normalize(orderedText(cleaned(heading)));
            if (current !== exact) errors.push({id, reason: 'new-native-title-does-not-equal-source'});
        } else {
            // Legacy native headers have one code-generated first badge and a
            // plain title tail. Validate that precise old representation first.
            const badge = heading.firstElementChild;
            if (!badge || badge.tagName !== 'SPAN') continue;
            const tail = heading.cloneNode(true); tail.firstElementChild.remove();
            const oldTitle = normalize(orderedText(tail));
            const oldExpected = normalize(title.replace(/^\d+\.\s*/, ''));
            if (oldTitle !== oldExpected) {
                errors.push({id, reason: 'unrecognized-legacy-native-title'}); continue;
            }
            restored.push({id, title});
        }
        // Full immutable authored title replaces only this verified UI header.
        // All punctuation/negation/numbers in lesson content remain untouched.
        heading.textContent = title;
    }
    return {node: copy, restored, errors};
}
function databaseTitles(inventory) {
    const native = new Set(['forms-grid', 'lesson-rule-cards', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list', 'practice-set', 'tense-forms-table']);
    const out = {};
    for (const block of inventory.data.blocks) {
        if (!native.has(block.type)) continue;
        let data; try {data = JSON.parse(block.body);} catch {continue;}
        if (typeof data?.title === 'string' && data.title) out['block-' + block.id] = data.title;
    }
    return out;
}
function metadata(document) {
    return {
        title: document.title,
        headings: [...document.querySelectorAll('h1')].map(n => normalize(n.textContent)),
        meta: [...document.querySelectorAll('meta[name],meta[property]')].filter(n =>
            ['description', 'robots', 'twitter:title', 'twitter:description', 'twitter:image'].includes(n.name)
            || n.getAttribute('property')?.startsWith('og:')).map(n => [n.name || n.getAttribute('property'), n.content]),
        canonical: [...document.querySelectorAll('link[rel="canonical"]')].map(n => n.href),
        jsonLd: [...document.querySelectorAll('script[type="application/ld+json"]')].map(n => JSON.parse(n.textContent)),
    };
}
async function capture(label, scope = 'canonical') {
    assert.match(label, /^[a-z0-9-]+$/);
    const inventory = JSON.parse(fs.readFileSync(path.join(privateDir, 'before-inventory.json'), 'utf8'));
    const titles = databaseTitles(inventory);
    const categories = inventory.matrix.filter(row => row.kind === 'category').flatMap(row => row.paths);
    const canonicalCategories = inventory.data.categories.filter(category => inventory.data.blocks.some(block => !block.page_id && block.page_category_id === category.id)).map(category => '/theory/' + category.slug);
    const paths = scope === 'categories' ? canonicalCategories : [...new Set([...Object.values(inventory.page_paths), ...(scope === 'canonical' ? canonicalCategories : categories),
        '/test/present-perfect-continuous/negatives',
        '/courses/english-grammar-theory/lesson/tenses/narrative-tenses', '/sitemap.xml'])];
    const directory = path.join(privateDir, label + '-html');
    fs.mkdirSync(directory, {recursive: true});
    const rows = new Array(paths.length); let cursor = 0;
    await Promise.all(Array.from({length: 4}, async () => {
        for (;;) {
            const index = cursor++; if (index >= paths.length) return;
            const target = paths[index];
            let response;
            try {
                response = await fetch(BASE + target, {redirect: 'manual', headers: {Accept: target.endsWith('.xml') ? 'application/xml' : 'text/html'}, signal: AbortSignal.timeout(60000)});
            } catch (error) {
                rows[index] = {path: target, status: null, error: error.name, message: error.message};
                continue;
            }
            const html = await response.text();
            // Never store response headers, cookies or request identifiers.
            const row = {path: target, status: response.status, location: response.headers.get('location'),
                contentType: response.headers.get('content-type'), xRobotsTag: response.headers.get('x-robots-tag')};
            if (target === '/sitemap.xml') {
                const dom = new JSDOM(html, {contentType: 'text/xml'});
                row.orderedLocs = [...dom.window.document.querySelectorAll('loc')].map(n => n.textContent);
                row.orderedSha256 = sha(JSON.stringify(row.orderedLocs)); dom.window.close();
            } else if (response.status === 200) {
                const dom = new JSDOM(html); const document = dom.window.document;
                row.metadata = metadata(document);
                const main = document.querySelector('[data-theory-main]') || document.querySelector('[data-theory-lesson-content]') || document.querySelector('main') || document.body;
                const checked = authoredHeaders(main, titles);
                row.content = content(checked.node);
                row.headingAudit = {restored: checked.restored, errors: checked.errors};
                row.blocks = [...new Set([...main.querySelectorAll('[id^="block-"]')].map(n => n.id))].map(id => ({id, ...content(main.querySelector('#' + id))}));
                row.selfChecks = [...main.querySelectorAll('[id^="self-check-"]')].map(n => ({id: n.id, ...content(n)}));
                row.newDetailControls = main.querySelectorAll('[data-theory-details]').length;
                row.phpDiagnostics = /\b(?:PHP Warning|PHP Notice|Fatal error|Deprecated):/i.test(html);
                row.assets = [...document.querySelectorAll('link[href*="/build/"],script[src*="/build/"]')].map(n => n.href || n.src);
                // CSRF meta is not retained in the evidence HTML.
                document.querySelectorAll('meta[name="csrf-token"],input[name="_token"],script').forEach(n => n.remove());
                fs.writeFileSync(path.join(directory, index + '.html'), dom.serialize(), {flag: 'wx'});
                dom.window.close();
            }
            rows[index] = row;
            if (index % 25 === 0) console.log(JSON.stringify({completed: rows.filter(Boolean).length, total: paths.length}));
        }
    }));
    const result = {at: new Date().toISOString(), base: BASE, label, rows};
    fs.writeFileSync(path.join(privateDir, label + '-http.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
    console.log(JSON.stringify({paths: rows.length, lessons: Object.keys(inventory.page_paths).length,
        statuses: rows.reduce((counts, row) => ({...counts, [row.status]: (counts[row.status] || 0) + 1}), {})}));
    return result;
}
function compare(before, after) {
    assert.equal(before.base, BASE); assert.equal(after.base, BASE);
    assert.deepEqual(after.rows.map(row => row.path), before.rows.map(row => row.path));
    const differences = []; const strict = [];
    for (const [index, previous] of before.rows.entries()) {
        const current = after.rows[index]; const errors = [];
        for (const key of ['status', 'location', 'contentType', 'xRobotsTag', 'metadata', 'orderedLocs']) {
            if (JSON.stringify(current[key]) !== JSON.stringify(previous[key])) errors.push(key);
        }
        if (previous.content) {
            for (const key of ['orderedText', 'hrefs', 'tables', 'details']) {
                if (JSON.stringify(current.content[key]) !== JSON.stringify(previous.content[key])) errors.push('content.' + key);
            }
            const missingAnchors = previous.content.anchors.filter(id => !current.content.anchors.includes(id));
            if (missingAnchors.length) errors.push({missingAnchors});
            if (current.content.duplicateIds.length) errors.push({duplicateIds: current.content.duplicateIds});
            if (current.newDetailControls) errors.push('live-detail-controls');
            if (current.phpDiagnostics) errors.push('php-diagnostics');
            if (current.headingAudit?.errors.length) errors.push({headingErrors: current.headingAudit.errors});
        }
        if (errors.length) differences.push({path: current.path, errors});
        else strict.push(current.path);
    }
    return {at: new Date().toISOString(), paths: after.rows.length, strict: strict.length, differences};
}
function deriveContent(label, output) {
    const result = JSON.parse(fs.readFileSync(path.join(privateDir, label + '-http.json'), 'utf8'));
    const titles = databaseTitles(JSON.parse(fs.readFileSync(path.join(privateDir, 'before-inventory.json'), 'utf8')));
    for (const [index, row] of result.rows.entries()) {
        if (!row.content) continue;
        const dom = new JSDOM(fs.readFileSync(path.join(privateDir, label + '-html', index + '.html'), 'utf8'));
        const document = dom.window.document;
        const main = document.querySelector('[data-theory-main]') || document.querySelector('[data-theory-lesson-content]') || document.querySelector('main') || document.body;
        const checked = authoredHeaders(main, titles);
        row.content = content(checked.node);
        row.headingAudit = {restored: checked.restored, errors: checked.errors};
        row.blocks = [...new Set([...main.querySelectorAll('[id^="block-"]')].map(n => n.id))].map(id => ({id, ...content(main.querySelector('#' + id))}));
        row.selfChecks = [...main.querySelectorAll('[id^="self-check-"]')].map(n => ({id: n.id, ...content(n)}));
        dom.window.close();
    }
    result.normalization = 'Whitespace uses ordinary block-flow boundaries; source HTML remains the independently saved BEFORE response.';
    fs.writeFileSync(path.join(privateDir, output + '-http.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
}
function mergeCanonicalBaseline(allLabel, categoryLabel, output) {
    const load = label => JSON.parse(fs.readFileSync(path.join(privateDir, label + '-http.json'), 'utf8'));
    const all = load(allLabel), categories = load(categoryLabel);
    assert.equal(all.base, BASE); assert.equal(categories.base, BASE);
    const inventory = JSON.parse(fs.readFileSync(path.join(privateDir, 'before-inventory.json'), 'utf8'));
    const lessons = new Set(Object.values(inventory.page_paths));
    const rows = [...all.rows.filter(row => lessons.has(row.path)), ...categories.rows,
        ...all.rows.filter(row => !row.path.startsWith('/theory/'))];
    assert.equal(new Set(rows.map(row => row.path)).size, rows.length);
    const result = {at: all.at, base: BASE, label: output, rows,
        independentBaselineParts: [allLabel, categoryLabel],
        note: 'Canonical category URLs supplement the independently captured before lesson/controls baseline; erroneous nested category candidates are not site failures.'};
    fs.writeFileSync(path.join(privateDir, output + '-http.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
    console.log(JSON.stringify({paths: rows.length, lessons: lessons.size, categories: categories.rows.length}));
}
function verifyProtectedFiles(label) {
    assert.match(label, /^[a-z0-9-]+$/);
    const baseline = JSON.parse(fs.readFileSync(path.join(privateDir, 'before-protected-files.json'), 'utf8'));
    const files = {}, unchanged = [], changed = [], missing = [];
    for (const [relative, hash] of Object.entries(baseline.files)) {
        assert.ok(['.env', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json', 'public/build/manifest.json'].includes(relative)
            || /^public\/build\/assets\/[A-Za-z0-9_.-]+$/.test(relative));
        const full = path.join('D:/DEV/htdocs/gramlyze.loc', relative);
        if (!fs.existsSync(full)) { missing.push(relative); continue; }
        files[relative] = sha(fs.readFileSync(full));
        (files[relative] === hash ? unchanged : changed).push(relative);
    }
    const result = {at: new Date().toISOString(), files, unchanged, changed, missing,
        allowedPresentationChange: 'public/build/manifest.json'};
    assert.deepEqual(missing, []);
    assert.deepEqual(changed, ['public/build/manifest.json']);
    fs.writeFileSync(path.join(privateDir, label + '-protected-files.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
    console.log(JSON.stringify({unchanged: unchanged.length, changed, missing}));
}
function verifyAppliedFiles(label) {
    assert.match(label, /^[a-z0-9-]+$/);
    const main = 'D:/DEV/htdocs/gramlyze.loc', worktree = path.resolve(__dirname, '../..');
    // The accepted production-style build is deliberately generated privately;
    // worktree/public/build retains the independent pre-M25 baseline artifacts.
    const buildCandidate = path.join(worktree, 'storage/app/seo-m25-build');
    const sources = [
        'app/Support/TheoryPresentation.php', 'app/Support/TheorySection.php',
        'resources/css/catalog-public.css', 'resources/css/theory-unified-design.css',
        'resources/js/catalog-public.js', 'resources/js/theory-sections.js',
        'resources/lang/en/theory_blocks.php', 'resources/lang/pl/theory_blocks.php', 'resources/lang/uk/theory_blocks.php',
        'resources/views/components/theory-native-header.blade.php', 'resources/views/components/theory-rich-box.blade.php',
        'resources/views/components/theory-section.blade.php', 'resources/views/courses/partials/theory-page-content.blade.php',
        'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
        'resources/views/engram/theory/blocks-v3/mistakes-grid.blade.php',
        'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
        'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
        'resources/views/engram/theory/blocks-v3/tense-forms-table.blade.php',
        'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
        'resources/views/theory/partials/category-description.blade.php',
        'resources/views/theory/partials/content-block.blade.php',
        'resources/views/theory/partials/lesson-toc.blade.php', 'resources/views/theory/show.blade.php',
        'resources/views/engram/theory/widgets/lesson-rule-cards.blade.php',
    ];
    const manifestPath = 'public/build/manifest.json';
    const manifest = JSON.parse(fs.readFileSync(path.join(main, manifestPath), 'utf8'));
    const assets = [...new Set(Object.values(manifest).flatMap(entry => [entry.file, ...(entry.css || [])]))];
    assert.equal(assets.length, 4, 'Expected the four explicitly applied public build assets');
    for (const asset of assets) assert.match(asset, /^assets\/[A-Za-z0-9_.-]+$/);
    const rows = [...sources.map(relative => ({relative, kind: 'source'})),
        ...[manifestPath, ...assets.map(asset => 'public/build/' + asset)].map(relative => ({relative, kind: 'build'}))]
        .map(({relative, kind}) => {
            const digest = root => {
                const full = kind === 'build' && root === worktree
                    ? path.join(buildCandidate, relative.slice('public/build/'.length)) : path.join(root, relative);
                const bytes = fs.readFileSync(full);
                return sha(kind === 'source' ? bytes.toString('utf8').replace(/\r\n/g, '\n') : bytes);
            };
            const mainSha256 = digest(main), worktreeSha256 = digest(worktree);
            assert.equal(mainSha256, worktreeSha256, relative + ' differs between working .loc and worktree');
            return {relative, kind, mainSha256, worktreeSha256, identical: true};
        });
    const result = {at: new Date().toISOString(), scope: 'Explicit approved M25 presentation sources and current public build only',
        normalization: 'Source CRLF becomes LF; no other character is changed. Build bytes are exact.',
        buildCandidate, sources: sources.length, buildFiles: assets.length + 1, rows};
    fs.writeFileSync(path.join(privateDir, label + '-applied-files.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
    console.log(JSON.stringify({sources: sources.length, buildFiles: assets.length + 1, identical: rows.length,
        manifestSha256: rows.find(row => row.relative === manifestPath).mainSha256, aggregateSha256: sha(JSON.stringify(rows))}));
}
async function compareIsolated(before, after) {
    assert.equal(after.count, before.count);
    if (before.sources) assert.deepEqual(after.sources, before.sources, 'Versioned source bytes changed');
    else assert.equal(after.content_sha256, before.content_sha256, 'Actual learning-data snapshot changed');
    assert.deepEqual(after.rows.map(row => row.identity), before.rows.map(row => row.identity));
    const differences = []; let scopes = 0; let auditedHeadings = 0;
    const actualTitles = databaseTitles(JSON.parse(fs.readFileSync(path.join(privateDir, 'before-inventory.json'), 'utf8')));
    for (const [index, previous] of before.rows.entries()) {
        // Closed JSDOM windows can release their pending lifecycle work between
        // bounded batches. Do not retain hundreds of document trees at once.
        if (index % 8 === 0) {
            await new Promise(resolve => setImmediate(resolve));
            if (typeof global.gc === 'function') global.gc();
        }
        const current = after.rows[index];
        let titles = actualTitles;
        if (previous.identity.startsWith('database/seeders/Page_V3/')) {
            const source = JSON.parse(fs.readFileSync(path.join(__dirname, '../..', previous.identity), 'utf8'));
            titles = {};
            const category = !!source.description;
            for (const [blockIndex, block] of (source.page?.blocks || source.description?.blocks || []).entries()) {
                if (!['forms-grid', 'lesson-rule-cards', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list', 'practice-set', 'tense-forms-table'].includes(block.type)) continue;
                let data; try {data = JSON.parse(block.body);} catch {continue;}
                if (typeof data.title === 'string' && data.title) titles['block-' + (blockIndex + (category ? 2 : 1))] = data.title;
            }
        }
        for (const [view, html] of Object.entries(previous.html)) {
            scopes++;
            const oldDom = new JSDOM(html), newDom = new JSDOM(current.html[view]);
            const select = document => document.querySelector('[data-theory-main]') || document.querySelector('[data-theory-lesson-content]') || document.body;
            const oldChecked = authoredHeaders(select(oldDom.window.document), titles);
            const newChecked = authoredHeaders(select(newDom.window.document), titles);
            const oldData = content(oldChecked.node), newData = content(newChecked.node);
            auditedHeadings += oldChecked.restored.length;
            const errors = [];
            for (const key of ['orderedText', 'hrefs', 'tables', 'details']) {
                if (JSON.stringify(newData[key]) !== JSON.stringify(oldData[key])) errors.push(key);
            }
            const missing = oldData.anchors.filter(id => !newData.anchors.includes(id));
            if (missing.length) errors.push({missing});
            if (newData.duplicateIds.length) errors.push({duplicateIds: newData.duplicateIds});
            if (oldChecked.errors.length || newChecked.errors.length) errors.push({headingAudit: [...oldChecked.errors, ...newChecked.errors]});
            if (errors.length) differences.push({identity: previous.identity, view, errors});
            oldDom.window.close(); newDom.window.close();
        }
        if (index % 50 === 0) console.log(JSON.stringify({compared: index + 1, total: after.count}));
    }
    return {at: new Date().toISOString(), rows: after.count, scopes, auditedHeadings, strict: scopes - differences.length, differences};
}
if (require.main === module) {
    const [mode, label, after] = process.argv.slice(2);
    if (mode === 'derive-content') deriveContent(label, after);
    else if (mode === 'merge-baseline') mergeCanonicalBaseline(label, after, process.argv[5]);
    else if (mode === 'protected') verifyProtectedFiles(label);
    else if (mode === 'applied') verifyAppliedFiles(label);
    else if (mode === 'isolated-compare') {
        const load = value => JSON.parse(fs.readFileSync(path.join(privateDir, value + '.json'), 'utf8'));
        compareIsolated(load(label), load(after)).then(result => {
            fs.writeFileSync(path.join(privateDir, after + '-comparison.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
            console.log(JSON.stringify({rows: result.rows, scopes: result.scopes, strict: result.strict, differences: result.differences.length}));
        }).catch(error => {console.error(error); process.exitCode = 1;});
    } else if (mode === 'sanitize') {
        for (const file of fs.readdirSync(path.join(privateDir, label + '-html'))) {
            const full = path.join(privateDir, label + '-html', file);
            const dom = new JSDOM(fs.readFileSync(full, 'utf8'));
            dom.window.document.querySelectorAll('meta[name="csrf-token"],input[name="_token"],script').forEach(n => n.remove());
            fs.writeFileSync(full, dom.serialize()); dom.window.close();
        }
    } else if (mode === 'capture') capture(label, after).catch(error => {console.error(error); process.exitCode = 1;});
    else if (mode === 'compare') {
        const load = value => JSON.parse(fs.readFileSync(path.join(privateDir, value + '-http.json'), 'utf8'));
        const result = compare(load(label), load(after));
        fs.writeFileSync(path.join(privateDir, after + '-http-comparison.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
        console.log(JSON.stringify({paths: result.paths, strict: result.strict,
            differences: result.differences.length, examples: result.differences.slice(0, 3)}));
    } else throw new Error('Use capture LABEL [canonical|categories], compare BEFORE AFTER, isolated-compare BEFORE AFTER, protected LABEL, or applied LABEL.');
}
module.exports = {capture, compare, compareIsolated, content, metadata, cleaned, normalize, orderedText, authoredHeaders};
