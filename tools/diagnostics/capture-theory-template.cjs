'use strict';
// M45.1 fresh live evidence. Historic helpers are code reuse only; no historic
// artifact is loaded as a baseline. Writes are exclusively private evidence.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto'), assert = require('node:assert/strict');
const { chromium } = require('playwright'), { JSDOM } = require('jsdom');
const ref = require('./capture-m44-reference-supplement.cjs');
const m45 = require('./capture-m45-theory.cjs');
const courses = require('./capture-m45-courses.cjs');
const { metadata } = require('./seo-m28-local.cjs');
const httpHelpers = require('./capture-m42-design-http.cjs');
const ROOT = 'D:/DEV/htdocs/gramlyze.loc', PRIVATE = path.resolve(ROOT, 'storage/app/theory-template-local');
const BASE = 'http://gramlyze.loc', CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const TARGETS = [...m45.TARGETS, ...m45.REFERENCES];
const STATES = m45.STATES;
const EXTRA_ROLES = [
    ['usage-number', 'article.theory-item .rounded-full'],
    ['usage-heading', 'article.theory-item h3,article.theory-item div.flex.items-center.gap-2 > span:last-child'],
    ['forms-card', '.theory-item.group.relative'],
    ['forms-caption', '.theory-item.group.relative span.uppercase'],
    ['forms-formula', '.theory-item.group.relative h3'],
    ['point-summary', '[data-theory-native-extension] > details > summary'],
    ['point-container', '[data-theory-native-extension] > details'],
    ['point-body', '[data-theory-native-extension] > details .theory-section-detail-body'],
    ['m45-point-number', '[data-m45-basic-point] h3 > span'],
    ['m45-point-caption', '[data-m45-basic-point] h3'],
    ['m45-detail-summary', '[data-m45-detail] > summary'],
];
function save(dir, name, value) { fs.writeFileSync(path.join(dir, name), JSON.stringify(value, null, 2) + '\n', { flag: 'wx' }); }
function directory(label) { assert.match(label, /^(?:browser|courses|registry-http|representatives)-(?:before|after)-v[1-9][0-9]*$/u); const dir = path.join(PRIVATE, label); assert.ok(dir.startsWith(PRIVATE + path.sep)); assert.equal(fs.existsSync(dir), false); fs.mkdirSync(dir, { recursive: true }); return dir; }
function hashes() {
    const files = ['resources/css/theory-unified-design.css', 'resources/views/theory/show.blade.php', 'resources/views/theory/partials/content-block.blade.php', 'resources/views/courses/partials/theory-page-content.blade.php', 'app/Support/TheoryPresentation.php', 'app/Http/Controllers/PageController.php'];
    const dirs = ['resources/views/engram/theory/blocks-v3', 'resources/views/engram/theory/widgets', 'resources/views/theory/partials', 'resources/views/theory/components'];
    for (const dir of dirs) for (const entry of fs.readdirSync(path.join(ROOT, dir), { withFileTypes: true })) if (entry.isFile()) files.push(dir + '/' + entry.name);
    for (const entry of fs.readdirSync(path.join(ROOT, 'app/Support'))) if (/^Theory.*\.php$/u.test(entry)) files.push('app/Support/' + entry);
    return Object.fromEntries([...new Set(files)].sort().map(file => [file, sha(fs.readFileSync(path.join(ROOT, file)))]));
}
async function observe(context, page, row) {
    row.blocked = []; row.errors = []; row.localFailures = []; row.assets = [];
    await context.route('**/*', route => {
        const request = route.request();
        const scriptUrl = new URL(request.url());
        const courseScript = request.method() === 'GET' && scriptUrl.origin === BASE && scriptUrl.pathname === '/js/theory-course-progress.js' && /^\??(?:v=\d+)?$/u.test(scriptUrl.search);
        if (courseScript || ref.allowedRequest(request.url(), request.method())) { const headers = { ...request.headers() }; delete headers.cookie; delete headers.authorization; delete headers.referer; return route.continue({ headers }); }
        row.blocked.push({ url: ref.safeUrl(request.url()), method: request.method() }); return route.abort('blockedbyclient');
    });
    page.on('pageerror', error => row.errors.push({ kind: 'page', name: error.name, message: error.message }));
    page.on('console', message => { if (message.type() === 'error') row.errors.push({ kind: 'console', source: ref.safeUrl(message.location().url), message: message.text() }); });
    page.on('requestfailed', request => { if (new URL(request.url()).origin === BASE || request.url().startsWith('https://fonts.')) row.localFailures.push({ url: ref.safeUrl(request.url()), failure: request.failure()?.errorText, type: request.resourceType() }); });
    page.on('response', response => {
        if (response.status() >= 400 && new URL(response.url()).origin === BASE) row.localFailures.push({ url: ref.safeUrl(response.url()), status: response.status() });
        if (['script', 'stylesheet', 'font'].includes(response.request().resourceType())) row.assets.push({ url: ref.safeUrl(response.url()), status: response.status(), type: response.request().resourceType() });
    });
}
async function screenshot(locator, dir, file, options = {}) {
    assert.equal(fs.existsSync(path.join(dir, file)), false); await locator.screenshot({ path: path.join(dir, file), animations: 'disabled', timeout: 45000, ...options }); return { file, sha256: sha(fs.readFileSync(path.join(dir, file))) };
}
async function semantics(page) {
    return page.evaluate(() => {
        const main = document.querySelector('[data-theory-main]'), norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
        if (!main) throw new Error('Missing actual theory owner root');
        const clone = main.cloneNode(true); clone.querySelectorAll('script,style,noscript,[data-theory-ui],[data-sentence-builder]').forEach(node => node.remove());
        return {
            text: norm(clone.textContent), fullText: norm(main.textContent), html: main.innerHTML,
            anchors: [...main.querySelectorAll('[id]')].filter(node => !node.closest('[data-sentence-builder]')).map(node => node.id),
            headings: [...main.querySelectorAll('h2,h3,h4')].map(node => ({ tag: node.tagName, text: norm(node.textContent) })),
            links: [...main.querySelectorAll('a[href]')].map(node => ({ href: node.getAttribute('href'), text: norm(node.textContent) })),
            details: [...main.querySelectorAll('details')].map(node => ({ id: node.id, summary: norm(node.querySelector(':scope > summary')?.textContent), text: norm(node.textContent), nested: node.querySelectorAll('details').length, open: node.open })),
            tables: [...main.querySelectorAll('table')].map(node => ({ heads: [...node.querySelectorAll('thead th')].map(cell => norm(cell.textContent)), rows: [...node.querySelectorAll('tbody tr')].map(row => [...row.children].map(cell => norm(cell.textContent))) })),
            practice: [...main.querySelectorAll('[x-data]')].filter(node => /practice/iu.test(node.getAttribute('x-data'))).map(node => ({ state: node.getAttribute('x-data'), text: norm(node.textContent), controls: [...node.querySelectorAll('input,select,textarea,fieldset')].map(control => ({ tag: control.tagName, type: control.getAttribute('type'), name: control.getAttribute('name'), label: norm(control.querySelector('legend')?.textContent) })) })),
            loadedStyles: [...document.querySelectorAll('link[rel=stylesheet]')].map(node => node.href), loadedScripts: [...document.querySelectorAll('script[src]')].map(node => node.src),
            layout: { width: innerWidth, height: innerHeight, dpr: devicePixelRatio, scale: visualViewport.scale, documentOverflow: Math.max(0, document.documentElement.scrollWidth - innerWidth), mainOverflow: Math.max(0, main.scrollWidth - main.clientWidth) },
        };
    });
}
async function captureState(browser, target, state, dir, mode, before) {
    const { viewport, theme } = state, stem = target.key + '-' + viewport.width + '-' + theme;
    const row = { key: target.key, path: target.path, target: Boolean(target.target), viewport, theme, screenshots: [], pass: false };
    const context = await browser.newContext({ viewport, colorScheme: theme, deviceScaleFactor: 1, serviceWorkers: 'block' }), page = await context.newPage();
    try {
        await context.addInitScript(theme => localStorage.setItem('theme', theme), theme); await observe(context, page, row);
        const response = await page.goto(BASE + target.path, { waitUntil: 'networkidle', timeout: 60000 });
        row.http = { status: response.status(), finalUrl: ref.safeUrl(page.url()), xRobots: response.headers()['x-robots-tag'] || null }; assert.equal(row.http.status, 200); assert.equal(row.http.finalUrl, BASE + target.path);
        await page.waitForFunction(() => Boolean(window.Alpine)); await page.evaluate(theme => document.documentElement.classList.toggle('dark', theme === 'dark'), theme);
        row.fonts = await ref.fontEvidence(page); row.fonts.visibleGlyphs = await m45.visibleFontEvidence(page);
        const html = await page.content(), document = new JSDOM(html); row.metadata = metadata(document.window.document); row.jsonLd = httpHelpers.jsonLd(document.window.document); document.window.close();
        row.dom = await m45.domSnapshot(page); row.semantic = await semantics(page); row.styles = await m45.styles(page); row.additionalStyles = await ref.collectStyles(page, EXTRA_ROLES);
        row.canonical = await canonicalEvidence(page);
        row.canonicalStyles = await ref.collectStyles(page, [
            ['canonical-rule-shell', '[data-m45-basic-point][data-theory-component="usage"]'],
            ['canonical-rule-padding', '[data-m45-basic-point][data-theory-component="usage"] > .p-4'],
            ['canonical-rule-caption', '[data-m45-basic-point][data-theory-component="usage"] > .p-4 > .flex > span.uppercase'],
            ['canonical-rule-number', '[data-m45-basic-point][data-theory-component="usage"] > .p-4 > .flex > span.rounded-full'],
            ['canonical-form-card', '[data-m45-form-card]'],
            ['canonical-form-caption', '[data-m45-form-row] > span'],
            ['canonical-form-formula', '[data-m45-form-row] > h4'],
            ['canonical-point-summary', '[data-m45-detail] > summary'],
        ]);
        row.screenshots.push(await screenshot(page, dir, stem + '-top.png')); row.screenshots.push(await screenshot(page, dir, stem + '-full.png', { fullPage: true }));
        for (const [index, section] of (await page.locator('[data-theory-main] .theory-section-card').all()).entries()) if (await section.isVisible()) row.screenshots.push(await screenshot(section, dir, stem + '-section-' + (index + 1) + '.png'));
        const details = page.locator('[data-theory-native-extension] > details,[data-m45-detail]'); row.openDetails = [];
        for (const detail of (await details.all()).slice(0, target.key === 'PPC' ? 2 : 1)) {
            await detail.locator(':scope > summary').focus(); await detail.locator(':scope > summary').press('Enter'); assert.equal(await detail.evaluate(node => node.open), true);
            row.openDetails.push({ id: await detail.getAttribute('id'), styles: await ref.collectStyles(page, EXTRA_ROLES), screenshot: await screenshot(detail, dir, stem + '-detail-' + (row.openDetails.length + 1) + '.png') });
            await detail.locator(':scope > summary').press('Space'); assert.equal(await detail.evaluate(node => node.open), false);
        }
        await page.evaluate(() => scrollTo(0, 0)); row.scrollScreenshots = [];
        for (let step = 0; step < 150; step++) { const end = await page.evaluate(() => { scrollBy(0, innerHeight * .8); return scrollY + innerHeight >= document.documentElement.scrollHeight - 1; }); if (end) break; if (viewport.width === 390 && step < 8) row.scrollScreenshots.push(await screenshot(page, dir, stem + '-scroll-' + step + '.png')); }
        row.footerReached = await page.evaluate(() => Boolean(document.querySelector('footer')) && scrollY + innerHeight >= document.documentElement.scrollHeight - 1); assert.ok(row.footerReached); row.screenshots.push(await screenshot(page, dir, stem + '-footer.png'));
        if (before) {
            const old = before.rows.find(item => item.key === row.key && item.viewport.width === viewport.width && item.theme === theme); assert.ok(old?.pass);
            assert.deepEqual(row.http, old.http); assert.deepEqual(row.metadata, old.metadata); assert.deepEqual(row.jsonLd, old.jsonLd);
            row.contentComparison = compareSemantics(old.semantic, row.semantic, target.key === 'PPC');
            if (target.target) {
                assert.equal(row.canonical.sections.length, 5);
                assert.ok(row.canonical.sections.every(node => node.component === 'section'));
                assert.equal(row.canonical.forms.length, 2); assert.ok(row.canonical.forms.every(node => node.component === 'form' && node.rows === 3));
                assert.ok(row.canonical.points.every(node => ['usage', 'fragment', 'form', 'mistake'].includes(node.component)));
                assert.ok(row.canonical.details.every(node => node.standardWrapper && node.standardSummary && node.standardBody));
                row.canonicalReferenceComparison = compareCanonicalStyles(row, before);
            }
            if (target.key === 'PPC') {
                // Added semantic lang attributes can broaden a selector. Match
                // the same original learner text within its original role,
                // retaining every original class and computed property.
                row.referenceStyles = await compareReferenceStyles(page, old);
                row.referenceExact = true;
            }
            if (target.key.startsWith('M44-')) row.nativeTableComparison = await compareNativeTables(page, old);
        }
        assert.deepEqual(row.errors, []); assert.deepEqual(row.localFailures, []); assert.deepEqual(row.blocked, []); row.pass = true;
    } catch (error) { row.failure = { name: error.name, message: error.message, stack: error.stack, actual: error.actual, expected: error.expected }; }
    finally { await context.close(); save(dir, stem + '.json', row); }
    console.log(JSON.stringify({ key: target.key, width: viewport.width, theme, pass: row.pass, error: row.failure?.message.split('\n')[0] || null })); return row;
}
async function compareNativeTables(page, before) {
    const roles = before.styles.filter(role => ['table-scroll', 'table-head', 'table-cell'].includes(role.role));
    const expectation = { styles: roles, additionalStyles: [] };
    const styles = roles.some(role => role.elements.length) ? await compareReferenceStyles(page, expectation) : { matchingNodes: 0, properties: 0 };
    const source = new JSDOM('<main>' + before.semantic.html + '</main>');
    const shape = root => [...root.querySelectorAll('table')].map(table => [...table.querySelectorAll('tbody tr')].map(row => [...row.children].map(cell => [...cell.children].map(node => ({ tag: node.tagName, classes: String(node.className || '').trim().split(/\s+/u).filter(Boolean).sort(), lang: node.getAttribute('lang'), text: node.textContent.replace(/\s+/gu, '') })))));
    let oldShape; try { oldShape = shape(source.window.document); } finally { source.window.close(); }
    const currentShape = await page.evaluate(() => [...document.querySelectorAll('[data-theory-main] table')].map(table => [...table.querySelectorAll('tbody tr')].map(row => [...row.children].map(cell => [...cell.children].map(node => ({ tag: node.tagName, classes: String(node.className || '').trim().split(/\s+/u).filter(Boolean).sort(), lang: node.getAttribute('lang'), text: node.textContent.replace(/\s+/gu, '') }))))));
    assert.deepEqual(currentShape, oldShape, 'Native M44 table direct learner children/classes/lang: no added box/padding/icon wrapper');
    return { styles, directCellStructureExact: true, tables: oldShape.length };
}
function compareCanonicalStyles(row, before) {
    const reference = before.rows.find(item => item.key === 'PPC' && item.viewport.width === row.viewport.width && item.theme === row.theme); assert.ok(reference?.pass);
    const native = [...reference.styles, ...reference.additionalStyles];
    const type = ['font-family', 'font-size', 'font-weight', 'font-style', 'line-height'];
    const surface = ['background-color', 'background-image', 'border-top-width', 'border-top-color', 'border-left-width', 'border-left-color', 'border-radius', 'box-shadow'];
    const padding = ['padding-top', 'padding-right', 'padding-bottom', 'padding-left'];
    const maps = [
        ['canonical-rule-shell', 'usage-panel', surface],
        ['canonical-rule-padding', 'reference-usage-content', padding],
        ['canonical-rule-caption', 'usage-heading', type.concat('text-transform', 'letter-spacing')],
        ['canonical-rule-number', 'usage-number', type.concat('border-radius', 'color')],
        ['canonical-form-card', 'forms-panel', surface.concat(padding)],
        ['canonical-form-caption', 'forms-caption', type.concat('color', 'background-color', 'text-transform', 'letter-spacing')],
        ['canonical-form-formula', 'forms-formula', type.concat('color')],
        ['canonical-point-summary', 'point-summary', type.concat('color').concat(padding)],
    ];
    const checks = [], differences = [];
    for (const [targetRole, sourceRole, properties] of maps) {
        const target = row.canonicalStyles.find(item => item.role === targetRole), source = native.find(item => item.role === sourceRole && item.elements.length)?.elements[0]; assert.ok(source, 'Fresh PPC role ' + sourceRole); assert.ok(target?.elements.length, 'Actual canonical role ' + targetRole);
        for (const [index, element] of target.elements.entries()) for (const property of properties) { checks.push({ role: targetRole, index, property }); if (element.styles[property] !== source.styles[property]) differences.push({ role: targetRole, index, text: element.text, sourceRole, property, expected: source.styles[property], actual: element.styles[property] }); }
    }
    const palette = new Set(native.filter(role => role.role === 'usage-number').flatMap(role => role.elements.map(element => element.styles['background-color'])));
    // PPC has no rose rule. The task explicitly permits another existing
    // native reference for absent roles. Its independent BEFORE contains the
    // same usage marker classes, so do not infer an accent from AFTER.
    const legacyBytes = fs.readFileSync(path.join(PRIVATE, 'representatives-before-v1/manifest.json'));
    assert.equal(sha(legacyBytes), '0a8d1bf2f593d30de4553ace8443e2e0b83adf39932e780308de70cd8bcdb02e');
    const legacy = JSON.parse(legacyBytes).rows.find(item => item.key === 'PAGE-1'); assert.ok(legacy?.pass);
    for (const role of legacy.additionalStyles.filter(role => role.role === 'usage-number')) for (const element of role.elements) palette.add(element.styles['background-color']);
    const actualAccents = row.canonicalStyles.find(role => role.role === 'canonical-rule-number').elements.map(element => element.styles['background-color']);
    assert.ok(actualAccents.every(color => palette.has(color)), 'Rule markers use the fresh native semantic palette');
    assert.ok(new Set(actualAccents).size > 1, 'M45 rule markers are not all a package-specific blue');
    assert.deepEqual(differences, [], 'Canonical semantic-role styles vs independent PPC BEFORE');
    return { reference: 'PPC', source: 'browser-before-v1', properties: checks.length, differences, semanticAccentColors: { additionalReference: legacy.path, source: 'representatives-before-v1/PAGE-1-1440-light.json', independentPalette: [...palette], actual: [...new Set(actualAccents)], meaning: 'Marker accents belong to the native palette; color differs by semantic accent, not package. Marker/caption font and shape compared exactly.' } };
}
async function canonicalEvidence(page) {
    return page.evaluate(() => {
        const info = node => ({ id: node.id, component: node.getAttribute('data-theory-component'), classes: node.className, tag: node.tagName });
        const main = document.querySelector('[data-theory-main]');
        return {
            components: Object.fromEntries([...new Set([...main.querySelectorAll('[data-theory-component]')].map(node => node.dataset.theoryComponent))].sort().map(kind => [kind, main.querySelectorAll('[data-theory-component="' + kind + '"]').length])),
            sections: [...main.querySelectorAll('[data-m45-author-section]')].map(info),
            points: [...main.querySelectorAll('[data-m45-basic-point]')].map(info),
            forms: [...main.querySelectorAll('[data-m45-form-card]')].map(node => ({ ...info(node), rows: node.querySelectorAll('[data-m45-form-row]').length })),
            details: [...main.querySelectorAll('[data-m45-detail]')].map(node => ({ id: node.dataset.m45Detail, standardWrapper: node.parentElement.hasAttribute('data-theory-native-extension'), standardSummary: node.querySelector(':scope > summary')?.classList.contains('theory-section-toggle'), standardBody: Boolean(node.querySelector(':scope > .theory-section-detail-body')) })),
        };
    });
}
async function compareReferenceStyles(page, before) {
    const expectations = [...before.styles, ...before.additionalStyles].flatMap(role => role.elements.map(element => ({ role: role.role, selector: role.selector, ...element })));
    const matches = await page.evaluate(({ expectations, properties }) => expectations.map(expected => {
        const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
        // Text is only the identity key here. Ignore whitespace for this key
        // (inline punctuation and table-cell separators vary by Blade output);
        // the complete learner text is checked independently above.
        const key = value => String(value || '').replace(/\s+/gu, '');
        const node = [...document.querySelectorAll(expected.selector)].find(node => node.closest('[data-theory-main]') && node.tagName === expected.tag && key(node.textContent).startsWith(key(expected.text)));
        if (!node) return { role: expected.role, text: expected.text, found: false };
        return { role: expected.role, text: expected.text, found: true, classes: norm(node.className).split(' ').filter(Boolean).sort(), technicalWrapping: Boolean(node.closest('[data-theory-component="fragment"],.theory-note,.theory-section-detail-body')), styles: Object.fromEntries(properties.map(property => [property, getComputedStyle(node).getPropertyValue(property)])) };
    }), { expectations, properties: ref.PROPERTIES });
    let count = 0; const differences = [], recordedMigrations = [];
    expectations.forEach((expected, index) => {
        const actual = matches[index];
        if (!actual.found) { differences.push({ role: expected.role, text: expected.text, missing: true }); return; }
        const classes = norm(expected.classes).split(' ').filter(Boolean).sort();
        if (JSON.stringify(actual.classes) !== JSON.stringify(classes)) {
            const removed = classes.filter(token => !actual.classes.includes(token)), added = actual.classes.filter(token => !classes.includes(token));
            const migration = { role: expected.role, text: expected.text, kind: 'class-tokens', before: classes, after: actual.classes, removed, added };
            if (['usage-description', 'forms-description'].includes(expected.role) && JSON.stringify(removed) === '["m42-rich-fragment"]' && !added.length) recordedMigrations.push(migration); else differences.push(migration);
        }
        for (const [property, value] of Object.entries(expected.styles)) {
            count++;
            if (value !== actual.styles[property]) {
                const migration = { role: expected.role, text: expected.text, kind: 'computed-property', property, before: value, after: actual.styles[property] };
                if (property === 'overflow-wrap' && value === 'normal' && actual.styles[property] === 'anywhere' && actual.technicalWrapping) recordedMigrations.push(migration); else differences.push(migration);
            }
        }
    });
    assert.deepEqual(differences, [], 'Original reference semantic structure/classes/computed appearance, excluding explicitly recorded inert class and component-scoped technical wrapping migrations');
    return { matchingNodes: expectations.length, properties: count, visualComputedExact: true, recordedMigrations, differences, normalization: 'Whitespace and class ordering only for the identity key; same original learner text matched in original selector. No class/property removed from evidence. Removed inert m42-rich-fragment and scoped normal-to-anywhere wrapping are recorded explicitly; every other mismatch fails.' };
}
function compareSemantics(before, after, exact) {
    if (exact) assert.deepEqual(after.anchors, before.anchors, 'Unchanged reference server anchors');
    else { for (const id of before.anchors) assert.ok(after.anchors.includes(id), 'Preserved server anchor ' + id); assert.equal(new Set(after.anchors).size, after.anchors.length, 'Unique server anchors'); }
    assert.deepEqual(semanticTables(after.html), semanticTables(before.html), 'Every table column and punctuation-sensitive cell (only native chat icon and whitespace separated)');
    assert.deepEqual(after.practice.map(({ state, controls }) => ({ state, controls })), before.practice.map(({ state, controls }) => ({ state, controls })), 'Exact practice state, answer payload and controls');
    assert.deepEqual(stablePracticeText(after.html), stablePracticeText(before.html), 'Static practice learner text from immutable DOM');
    if (exact) {
        const oldText = referenceTextFromHTML(before.html), currentText = referenceTextFromHTML(after.html);
        assert.equal(currentText.text, oldText.text, 'Exact reference learner text with DOM text-node whitespace boundaries');
        assert.deepEqual(after.headings, before.headings); assert.deepEqual(after.details.map(({ text, ...row }) => row), before.details.map(({ text, ...row }) => row));
        assert.deepEqual(currentText.details, oldText.details, 'Exact reference detail text with DOM text-node whitespace boundaries'); assert.deepEqual(after.links, before.links);
    }
    return { exactReference: exact, anchors: true, tables: true, practice: { stateAndControlsExact: true, staticTextExact: true, normalization: 'Static text is derived independently from both immutable HTML snapshots. Excludes scripts, noscript source, templates, runtime x-text, explicit data-theory-ui and randomized linked sentence-builder only; token/answer payload remains exact and live scoring is checked separately. Stored DOM/classes/ARIA are untouched.' } };
}
function referenceTextFromHTML(html) {
    const dom = new JSDOM('<main>' + html + '</main>');
    try {
        const text = node => { const walker = dom.window.document.createTreeWalker(node, 4), parts = []; let value; while ((value = walker.nextNode())) parts.push(value.nodeValue); return norm(parts.join(' ')); };
        const main = dom.window.document.querySelector('main');
        main.querySelectorAll('script,style,noscript,[data-theory-ui],[data-sentence-builder]').forEach(node => node.remove());
        const details = [...main.querySelectorAll('details')].map(node => ({ id: node.id, text: text(node) }));
        return { text: text(main), details };
    } finally { dom.window.close(); }
}
function stablePracticeText(html) {
    const dom = new JSDOM('<main>' + html + '</main>');
    try {
        return [...dom.window.document.querySelectorAll('[x-data]')].filter(node => /practice/iu.test(node.getAttribute('x-data')) && !node.closest('[data-sentence-builder]')).map(node => {
            const clone = node.cloneNode(true);
            clone.querySelectorAll('script,style,noscript,template,[x-text],[data-theory-ui],[data-sentence-builder]').forEach(item => item.remove());
            const walker = dom.window.document.createTreeWalker(clone, 4), parts = []; let text;
            while ((text = walker.nextNode())) parts.push(text.nodeValue);
            return parts.join(' ').match(/[\p{L}\p{N}]+(?:[’'\-][\p{L}\p{N}]+)*/gu) || [];
        });
    } finally { dom.window.close(); }
}
function semanticTables(html) {
    const dom = new JSDOM('<main>' + html + '</main>');
    try {
        const text = node => {
            const copy = node.cloneNode(true);
            for (const icon of copy.querySelectorAll('.theory-example > span.flex-shrink-0')) if (icon.textContent.trim() === '💬') icon.remove();
            return copy.textContent.replace(/\s+/gu, '');
        };
        return [...dom.window.document.querySelectorAll('table')].map(table => ({ heads: [...table.querySelectorAll('thead th')].map(text), rows: [...table.querySelectorAll('tbody tr')].map(row => [...row.children].map(text)) }));
    } finally { dom.window.close(); }
}
async function runBrowser(mode, label, beforeLabel, representativesFile) {
    const dir = directory(label), before = beforeLabel ? JSON.parse(fs.readFileSync(path.join(PRIVATE, beforeLabel, 'manifest.json'))) : null;
    if (before) { assert.equal(before.mode, '--before'); assert.equal(before.pass, true); }
    const report = { mode, base: BASE, startedAt: new Date().toISOString(), sourcesBefore: hashes(), beforeSha256: before ? sha(fs.readFileSync(path.join(PRIVATE, beforeLabel, 'manifest.json'))) : null, rows: [], pass: false,
        limitations: ['Fresh browser default zoom and DPR1. Viewports are not real zoom acceptance.', 'Only observed semantic roles are compared; screenshots require visual review.', 'No historic report or capture is used as baseline.'] };
    let targets = mode === '--after' ? [...TARGETS.filter(target => target.key === 'PPC'), ...TARGETS.filter(target => target.key !== 'PPC')] : TARGETS, states = STATES;
    if (representativesFile) {
        if (representativesFile === 'm44') targets = TARGETS.filter(target => target.key.startsWith('M44-'));
        else if (representativesFile === 'registry') {
            const registryBytes = fs.readFileSync(path.join(PRIVATE, 'registry-before-v2.json'));
            assert.equal(sha(registryBytes), 'ced519a20d3b8c9d69e2188e38746e384b6d98f6feccceb3b8e280d887b78fdb');
            const registry = JSON.parse(registryBytes), selected = new Set(), covered = new Set();
            for (const [group, ids] of Object.entries(registry.summary.package_group_page_ids)) {
                if (/^M(?:26|27|28|29|30|31|32|33|34|35|36|37|38|39|40|41|42|43)/u.test(group) || ['native-legacy', 'rich-html'].includes(group)) selected.add(ids[0]);
            }
            const variant = id => registry.registry.find(page => page.page_id === id).variants.find(item => item.locale === 'uk');
            for (const id of selected) for (const type of variant(id).block_types) covered.add(type);
            for (const [type, ids] of Object.entries(registry.summary.block_type_page_ids)) if (!covered.has(type)) { selected.add(ids[0]); for (const found of variant(ids[0]).block_types) covered.add(found); }
            targets = [...selected].map(id => ({ key: 'PAGE-' + id, path: variant(id).url, target: false, groups: variant(id).package_groups, blockTypes: variant(id).block_types }));
            report.representativeSelection = { registrySha256: sha(registryBytes), targets, allBlockTypes: [...covered] };
        } else targets = JSON.parse(fs.readFileSync(representativesFile)).targets;
        if (representativesFile !== 'm44') states = [STATES[0]];
    }
    const browser = await chromium.launch({ headless: true, executablePath: CHROME });
    try {
        capture: for (const target of targets) for (const state of states) {
            const row = await captureState(browser, target, state, dir, mode, before); report.rows.push(row);
            if (mode === '--after' && !row.pass) break capture;
        }
        report.sourcesAfter = hashes(); assert.deepEqual(report.sourcesAfter, report.sourcesBefore, 'No served source changed during live capture'); report.pass = report.rows.length === targets.length * states.length && report.rows.every(row => row.pass);
    } finally { report.finishedAt = new Date().toISOString(); save(dir, 'manifest.json', report); await browser.close(); }
    console.log(JSON.stringify({ directory: dir, count: report.rows.length, pass: report.pass, sha256: sha(fs.readFileSync(path.join(dir, 'manifest.json'))) })); return report;
}
async function runCourses(mode, label, beforeLabel) {
    const dir = directory(label), before = beforeLabel ? JSON.parse(fs.readFileSync(path.join(PRIVATE, beforeLabel, 'manifest.json'))) : null;
    const report = { mode, base: BASE, startedAt: new Date().toISOString(), sourcesBefore: hashes(), rows: [], pass: false }, browser = await chromium.launch({ headless: true, executablePath: CHROME });
    try {
        for (const target of courses.TARGETS) for (const javaScriptEnabled of [true, false]) {
            const row = { path: target.path, javaScriptEnabled, pass: false, screenshots: [] }, context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, colorScheme: 'light', deviceScaleFactor: 1, javaScriptEnabled, serviceWorkers: 'block' }), page = await context.newPage(), stem = target.slug + (javaScriptEnabled ? '-js' : '-nojs'); report.rows.push(row);
            try {
                await observe(context, page, row); const response = await page.goto(BASE + target.path, { waitUntil: 'networkidle', timeout: 60000 }); assert.equal(response.status(), 200); row.status = response.status(); if (javaScriptEnabled) await page.waitForFunction(() => Boolean(window.TheoryCourseProgress));
                row.fonts = await ref.fontEvidence(page); row.dom = await courses.courseDOM(page); row.screenshots.push(await screenshot(page, dir, stem + '-top.png')); row.screenshots.push(await screenshot(page, dir, stem + '-full.png', { fullPage: true }));
                if (row.dom.guest.learnerVisible) for (const [index, section] of (await page.locator('[data-theory-lesson-content] section.theory-native-block').all()).entries()) if (await section.isVisible()) row.screenshots.push(await screenshot(section, dir, stem + '-section-' + (index + 1) + '.png'));
                if (before) { const old = before.rows.find(item => item.path === row.path && item.javaScriptEnabled === javaScriptEnabled); assert.ok(old?.pass); courses.equality(old.dom, row.dom); assert.deepEqual(row.dom.layout, old.dom.layout); row.matchesFreshBefore = true; }
                row.expectedDisabledScripts = row.localFailures.filter(item => !javaScriptEnabled && item.failure === 'csp' && item.type === 'script');
                assert.deepEqual(row.localFailures.filter(item => !row.expectedDisabledScripts.includes(item)), []); assert.deepEqual(row.errors, []); assert.deepEqual(row.blocked, []); row.pass = true;
            } catch (error) { row.failure = { name: error.name, message: error.message, stack: error.stack }; }
            finally { await context.close(); save(dir, stem + '.json', row); }
            console.log(JSON.stringify({ course: row.path, javaScriptEnabled, pass: row.pass, error: row.failure?.message.split('\n')[0] || null }));
        }
        report.sourcesAfter = hashes(); assert.deepEqual(report.sourcesAfter, report.sourcesBefore); report.pass = report.rows.length === 6 && report.rows.every(row => row.pass);
    } finally { report.finishedAt = new Date().toISOString(); save(dir, 'manifest.json', report); await browser.close(); }
    console.log(JSON.stringify({ directory: dir, pass: report.pass, count: report.rows.length, sha256: sha(fs.readFileSync(path.join(dir, 'manifest.json'))) })); return report;
}
if (require.main === module) { const [kind, mode, label, beforeLabel, representativesFile] = process.argv.slice(2); assert.ok(['--before', '--after'].includes(mode)); const run = kind === 'courses' ? runCourses : runBrowser; run(mode, label, beforeLabel === '-' ? null : beforeLabel, representativesFile).then(report => { if (!report.pass) process.exitCode = 1; }).catch(error => { console.error(error.stack); process.exitCode = 1; }); }
module.exports = { runBrowser, runCourses, captureState, compareSemantics, stablePracticeText, semanticTables, semantics, observe, screenshot, hashes, BASE, PRIVATE, TARGETS, STATES };
