'use strict';
// Own M45 GET-only browser/HTTP evidence. Reference readers and font probing are
// reused from the accepted M43/M44 diagnostics; no app, DB or config writes.
const fs = require('node:fs'), path = require('node:path'), assert = require('node:assert/strict'), crypto = require('node:crypto');
const { chromium } = require('playwright'), { JSDOM } = require('jsdom');
const reference = require('./capture-m44-reference-supplement.cjs');
const { metadata } = require('./seo-m28-local.cjs');
const legacyHTTP = require('./capture-m42-design-http.cjs');
const priorHTTP = require('./capture-m43-http.cjs');
const BASE = 'http://gramlyze.loc', ROOT = 'D:/DEV/htdocs/gramlyze.loc', PRIVATE = path.resolve(ROOT, 'storage/app/seo-m45-local');
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe', WT = path.resolve(__dirname, '../..');
const MASTER_PATH = 'docs/content/m45-authored-future-comparisons.v1.0.0.json';
const MASTER_SHA = '333b39ead3e5bb20c2bcde9c42878cf98f176158d7ae6324d0c6510b09b2afb7';
const BEFORE_SHA = '8a2f2f435dbb5d10036a4be22e14136b3aa7c41358fae004d79c882f48bce07d';
const HTTP_BEFORE_SHA = '067dd8652e6d182b82fa9266e06aa55345b433aa3a77f158ec2582f767999b54';
const META_PATH = 'metadata-expectations-before-v2.json', META_SHA = '36a50d4fa0f0548c56537af939f060c8c6e9a4d846c1feb59201f891dd6597fb';
let acceptance = null;
const TARGETS = ['future-perfect-vs-future-continuous', 'future-perfect-vs-future-perfect-continuous', 'future-continuous-vs-future-perfect-continuous'].map((slug, index) => ({ key: 'ABC'[index], path: '/theory/maibutni-formy/' + slug, target: true }));
const REFERENCES = [
    { key: 'PPC', path: '/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms' },
    { key: 'M44-WILL', path: '/theory/maibutni-formy/future-simple/will-vs-be-going-to' },
    { key: 'M44-PC', path: '/theory/maibutni-formy/present-continuous-for-future' },
];
const STATES = [{ width: 1440, height: 1000 }, { width: 390, height: 844 }].flatMap(viewport => ['light', 'dark'].map(theme => ({ viewport, theme })));
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value ?? '').replace(/\s+/gu, ' ').trim();
const sourceFiles = [
    ...['FutureFormsFuturePerfectVsFutureContinuousTheorySeeder', 'FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder', 'FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder'].map(name => 'database/seeders/Page_V3/FutureForms/' + name + '/definition.json'),
    'resources/views/theory/show.blade.php', 'resources/views/theory/partials/content-block.blade.php', 'resources/views/theory/partials/point-detail-fragment.blade.php',
    'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php', 'resources/css/theory-unified-design.css', 'public/js/authored-practice-ui.js',
    'app/Support/M45FutureComparisonsPackage.php', 'resources/views/engram/theory/blocks-v3/m45-section.blade.php', 'resources/views/engram/theory/blocks-v3/m45-practice-ui.blade.php',
    MASTER_PATH, 'public/js/m45-practice-ui.js', 'resources/views/engram/theory/blocks-v3/m45-detail.blade.php', 'resources/views/engram/theory/blocks-v3/m45-hero.blade.php',
    'app/Http/Controllers/PageController.php',
];
function hashes() { return Object.fromEntries(sourceFiles.map(file => [file, fs.existsSync(path.join(ROOT, file)) ? sha(fs.readFileSync(path.join(ROOT, file))) : null])); }
function directory(label) { assert.match(label, /^(?:theory|http)-(?:before|after|supplement)-[a-z0-9-]+$/u); const dir = path.resolve(PRIVATE, label); assert.ok(dir.startsWith(PRIVATE + path.sep)); assert.equal(fs.existsSync(dir), false, 'Exclusive evidence directory'); fs.mkdirSync(dir, { recursive: true }); return dir; }
function save(dir, file, value) { fs.writeFileSync(path.join(dir, file), JSON.stringify(value, null, 2) + '\n', { flag: 'wx' }); }
async function shot(locator, dir, file, options = {}) { assert.equal(fs.existsSync(path.join(dir, file)), false); await locator.screenshot({ path: path.join(dir, file), animations: 'disabled', timeout: 30000, ...options }); return { file, sha256: sha(fs.readFileSync(path.join(dir, file))) }; }
async function httpCapture(browser, label) {
    const dir = directory(label.replace(/^theory-/u, 'http-')), context = await browser.newContext({ serviceWorkers: 'block' });
    const routes = [...new Set([...TARGETS, ...REFERENCES].map(item => item.path).concat(priorHTTP.controls, ['/theory/maibutni-formy'], ['en', 'pl'].flatMap(locale => TARGETS.map(item => '/' + locale + item.path))))];
    const report = { base: BASE, at: new Date().toISOString(), rows: [], extras: {}, pass: false, limitations: ['Fresh guest GET only; unavailable controls are recorded rather than turned into successful baseline checks.'] };
    try {
        for (const route of routes) {
            assert.ok(reference.allowedRequest(BASE + route, 'GET'));
            const row = { route, path: route, at: new Date().toISOString(), target: TARGETS.some(item => item.path === route) }; report.rows.push(row);
            try {
                const response = await context.request.get(BASE + route, { timeout: 45000, maxRedirects: 0, headers: { Accept: 'text/html' } });
                const body = await response.text(), dom = new JSDOM(body, { url: BASE + route });
                try {
                    Object.assign(row, { status: response.status(), finalUrl: reference.safeUrl(response.url()), contentType: response.headers()['content-type'] || null, xRobotsTag: response.headers()['x-robots-tag'] || null,
                        metadata: metadata(dom.window.document), jsonLd: legacyHTTP.jsonLd(dom.window.document), learner: legacyHTTP.learner(dom.window.document), bodySha256: sha(body) });
                } finally { dom.window.close(); }
            } catch (error) { row.error = { name: error.name, message: error.message.split('\n')[0] }; }
            console.log(JSON.stringify({ phase: label.replace(/^theory-/u, 'http-'), route, status: row.status || null, error: row.error || null }));
        }
        for (const route of ['/robots.txt', '/sitemap.xml']) {
            const response = await context.request.get(BASE + route, { timeout: 45000, maxRedirects: 0 }), body = await response.text();
            const extra = { status: response.status(), finalUrl: reference.safeUrl(response.url()), xRobotsTag: response.headers()['x-robots-tag'] || null, sha256: sha(body) };
            if (route.endsWith('.xml')) { const dom = new JSDOM(body, { contentType: 'text/xml' }); extra.urls = [...dom.window.document.querySelectorAll('loc')].map(node => node.textContent); dom.window.close(); extra.count = extra.urls.length; extra.orderedSha256 = sha(JSON.stringify(extra.urls)); }
            else extra.body = body;
            report.extras[route] = extra;
        }
        report.pass = report.rows.filter(row => row.target).every(row => row.status === 200 && !row.error) && Object.values(report.extras).every(extra => extra.status === 200);
    } finally { await context.close(); save(dir, 'manifest.json', report); }
    return { directory: dir, manifestSha256: sha(fs.readFileSync(path.join(dir, 'manifest.json'))), ...report };
}
async function observe(context, page, row) {
    row.blocked = []; row.failures = []; row.httpErrors = []; row.pageErrors = []; row.consoleErrors = [];
    await context.route('**/*', route => { const request = route.request(); if (reference.allowedRequest(request.url(), request.method())) { const headers = { ...request.headers() }; delete headers.cookie; delete headers.referer; delete headers.authorization; return route.continue({ headers }); } row.blocked.push({ method: request.method(), url: reference.safeUrl(request.url()) }); return route.abort('blockedbyclient'); });
    page.on('pageerror', error => row.pageErrors.push({ name: error.name, messageSha256: sha(error.message) }));
    page.on('console', message => { if (message.type() === 'error') row.consoleErrors.push({ source: reference.safeUrl(message.location().url), messageSha256: sha(message.text()) }); });
    page.on('requestfailed', request => row.failures.push({ url: reference.safeUrl(request.url()), reason: request.failure()?.errorText, type: request.resourceType() }));
    page.on('response', response => { if (response.status() >= 400) row.httpErrors.push({ url: reference.safeUrl(response.url()), status: response.status() }); });
}
async function domSnapshot(page) {
    return page.evaluate(() => {
        const norm = value => String(value || '').replace(/\s+/gu, ' ').trim(), main = document.querySelector('[data-theory-main]');
        if (!main) throw new Error('Theory main missing');
        const clean = node => { const copy = node.cloneNode(true); copy.querySelectorAll('script,style,noscript,[data-sentence-builder],[data-theory-ui]').forEach(item => item.remove()); return norm(copy.textContent); };
        const rect = node => node ? { x: node.getBoundingClientRect().x, y: node.getBoundingClientRect().y, width: node.getBoundingClientRect().width, height: node.getBoundingClientRect().height } : null;
        const sections = [...main.querySelectorAll('.theory-section-card')].filter(node => !node.parentElement.closest('.theory-section-card')).map(node => ({ id: node.closest('[id]')?.id, authorId: node.closest('[data-m45-author-section]')?.dataset.m45AuthorSection || null, title: norm(node.querySelector('.theory-section-title')?.textContent), text: clean(node), rect: rect(node), practice: Boolean(node.closest('[data-m45-practice-ui],[data-m44-practice-ui],[data-m43-practice-ui]') || node.matches('[x-data^="theoryPracticeSet"]') || node.querySelector('[x-data^="theoryPracticeSet"]')) }));
        const details = [...main.querySelectorAll('details')].map(node => ({ id: node.id, open: node.open, nested: node.querySelectorAll('details').length, summary: norm(node.querySelector(':scope > summary')?.textContent), text: clean(node) }));
        const hero = document.querySelector('.theory-hero'), practice = main.querySelector('[data-m45-practice-ui],[data-m44-practice-ui],[data-m43-practice-ui]') || main.querySelector('[x-data^="theoryPracticeSet"]')?.closest('.theory-section-card');
        return { text: clean(main), html: main.innerHTML, ids: [...main.querySelectorAll('[id]')].map(node => node.id), headings: [...main.querySelectorAll('h2,h3,h4')].map(node => ({ tag: node.tagName, id: node.id, text: norm(node.textContent) })), links: [...main.querySelectorAll('a[href]')].map(node => ({ href: node.getAttribute('href'), text: norm(node.textContent) })), sections, details,
            measurements: { viewport: { width: innerWidth, height: innerHeight }, dpr: devicePixelRatio, visualViewportScale: visualViewport.scale, main: rect(main), hero: rect(hero), practice: rect(practice), theorySectionsHeight: sections.filter(section => !section.practice).reduce((sum, section) => sum + section.rect.height, 0), documentHeight: document.documentElement.scrollHeight, documentOverflow: Math.max(0, document.documentElement.scrollWidth - innerWidth), mainOverflow: Math.max(0, main.scrollWidth - main.clientWidth) },
            sidebar: [...document.querySelectorAll('[data-theory-aside]')].map(node => ({ visible: node.getClientRects().length > 0, display: getComputedStyle(node).display, width: node.getBoundingClientRect().width })),
            author: { sections: main.querySelectorAll('[data-m45-author-section]').length, points: main.querySelectorAll('[data-m45-basic-point]').length, cards: main.querySelectorAll('[data-m45-form-card]').length, formRows: main.querySelectorAll('[data-m45-form-row]').length, practice: main.querySelectorAll('[data-m45-practice-ui]').length, fallback: main.querySelectorAll('[data-m45-static-fallback]').length } };
    });
}
async function styles(page) {
    const roles = [...reference.ROLES,
        ['reference-plain-en', 'div.theory-example > p:not(.theory-translation):not([lang="uk"])'],
        ['reference-plain-uk', 'div.theory-example > .theory-translation'],
        ['reference-usage-content', 'article.theory-item > div.p-4'],
        ['reference-usage-en', 'article.theory-item .theory-example p.font-medium'],
        ['reference-usage-uk', 'article.theory-item .theory-example p.theory-translation'],
        ['reference-compact-card', '[data-m44-compact-group]:has([data-m44-form-row]) .theory-item.group.relative'],
        ['reference-compact-label', '[data-m44-form-row] > p:first-child'],
        ['reference-compact-formula', '[data-m44-form-row] > [data-m44-formula]'],
        ['reference-compact-en', '[data-m44-form-row] .m44-form-example-en,[data-m44-form-row] p[lang="en"]'],
        ['reference-compact-uk', '[data-m44-form-row] .m44-form-example-uk,[data-m44-form-row] p[lang="uk"]'],
        ['reference-choice', '[data-m44-answer]'], ['reference-token', '[data-m44-token]'], ['reference-input', '[data-m44-answer-input]'],
        ['m45-choice', '[data-m45-answer]'], ['m45-token', '[data-m45-token]'], ['m45-input', '[data-m45-answer-input]'],
        ['m45-form-card', '[data-m45-form-card]'], ['m45-form-label', '[data-m45-form-row] > p:first-child'], ['m45-form-formula', '[data-m45-form-row] > p[lang="en"]'], ['m45-form-en', '[data-m45-form-row] > div p[lang="en"]'], ['m45-form-uk', '[data-m45-form-row] > div p[lang="uk"]'], ['m45-point', '[data-m45-basic-point]']];
    return page.evaluate(({ roles, properties }) => roles.map(([role, selector]) => { const nodes = [...document.querySelectorAll(selector)].filter(node => node.closest('[data-theory-main]') && (!role.startsWith('reference-usage-') || node.checkVisibility())); return { role, selector, found: nodes.length, elements: nodes.slice(0, 4).map(node => ({ tag: node.tagName, classes: node.className, visible: node.checkVisibility(), text: String(node.textContent || '').replace(/\s+/gu, ' ').trim().slice(0, 180), styles: Object.fromEntries(properties.map(property => [property, getComputedStyle(node).getPropertyValue(property)])) })) }; }), { roles, properties: reference.PROPERTIES });
}
async function visibleFontEvidence(page) {
    const cdp = await page.context().newCDPSession(page); await cdp.send('DOM.enable'); await cdp.send('CSS.enable');
    const result = [], originalScroll = await page.evaluate(() => scrollY);
    try {
        for (const [role, selector] of [['heading', 'h1'], ['english', '[data-theory-main] .theory-example p[lang="en"],[data-theory-main] div.theory-example > p:not(.theory-translation),[data-m44-form-row] p[lang="en"],[data-theory-main] code.theory-example'], ['ukrainian', '[data-theory-main] .theory-translation,[data-m44-form-row] p[lang="uk"]']]) {
            const selected = await page.evaluate(({ role, selector }) => { const node = [...document.querySelectorAll(selector)].find(node => !node.closest('details:not([open]),[hidden],[inert]') && getComputedStyle(node).visibility !== 'hidden' && node.getClientRects().length > 0 && node.textContent.trim()); if (!node) return null; node.setAttribute('data-m45-font-probe', role); return { selector, text: node.textContent.trim(), visible: true, closedDetailAncestorExcluded: true }; }, { role, selector });
            if (!selected) { result.push({ role, available: false }); continue; }
            await page.locator('[data-m45-font-probe="' + role + '"]').scrollIntoViewIfNeeded();
            await page.waitForTimeout(50);
            const { root } = await cdp.send('DOM.getDocument'), { nodeId } = await cdp.send('DOM.querySelector', { nodeId: root.nodeId, selector: '[data-m45-font-probe="' + role + '"]' });
            const fonts = await cdp.send('CSS.getPlatformFontsForNode', { nodeId }); assert.ok(fonts.fonts.some(font => font.glyphCount > 0), 'Actual visible glyph font probe: ' + role + ' / ' + selected.text.slice(0, 80));
            result.push({ role, available: true, ...selected, fonts: fonts.fonts });
        }
    } finally { await page.evaluate(() => document.querySelectorAll('[data-m45-font-probe]').forEach(node => node.removeAttribute('data-m45-font-probe'))); await page.evaluate(y => scrollTo(0, y), originalScroll); await cdp.detach(); }
    return result;
}
function role(row, name) { const found = row.styles.find(item => item.role === name); assert.ok(found?.elements.length, 'Measured independent role ' + row.key + ':' + name); return found.elements[0].styles; }
const TYPE = ['font-family', 'font-size', 'font-weight', 'font-style', 'line-height', 'color'];
const SURFACE = ['background-color', 'border-top-width', 'border-top-color', 'border-left-width', 'border-left-color', 'border-radius', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left'];
function referenceEquality(row) {
    const before = acceptance.before.rows.find(item => item.key === row.key && item.viewport.width === row.viewport.width && item.theme === row.theme); assert.ok(before?.pass);
    assert.equal(row.dom.text, before.dom.text, 'Read-only reference full learner text'); assert.deepEqual(row.dom.ids, before.dom.ids, 'Read-only reference anchors');
    let properties = 0;
    for (const old of before.styles.filter(item => item.elements.length)) { const current = row.styles.find(item => item.role === old.role); assert.ok(current); assert.equal(current.found, old.found); old.elements.forEach((element, index) => { assert.deepEqual(current.elements[index].styles, element.styles, 'Independent BEFORE reference styles ' + row.key + ':' + old.role); properties += Object.keys(element.styles).length; }); }
    return { originalManifestSha256: BEFORE_SHA, learnerExact: true, anchorsExact: true, originalComputedProperties: properties, addedRoles: row.styles.filter(item => !before.styles.some(old => old.role === item.role) && item.elements.length).map(item => item.role), correctedBefore: correctedBeforeMeasurements(before) };
}
function designCheck(row) {
    const ppc = acceptance.references.find(item => item.key === 'PPC' && item.viewport.width === row.viewport.width && item.theme === row.theme), will = acceptance.references.find(item => item.key === 'M44-WILL' && item.viewport.width === row.viewport.width && item.theme === row.theme); assert.ok(ppc?.pass && will?.pass);
    const maps = [
        ['section-card', ppc, 'section-card', SURFACE], ['section-header', ppc, 'section-header', SURFACE], ['section-heading', ppc, 'section-heading', TYPE],
        ['m45-point', ppc, 'usage-panel', SURFACE.filter(property => !property.startsWith('padding-'))], ['m45-point', ppc, 'reference-usage-content', SURFACE.filter(property => property.startsWith('padding-'))],
        ['example-box', ppc, 'example-box', SURFACE], ['example-en', ppc, 'reference-usage-en', TYPE], ['example-uk', ppc, 'reference-usage-uk', TYPE],
        ['m45-form-card', will, 'reference-compact-card', SURFACE], ['m45-form-label', will, 'reference-compact-label', TYPE], ['m45-form-formula', will, 'reference-compact-formula', TYPE], ['m45-form-en', will, 'reference-compact-en', TYPE], ['m45-form-uk', will, 'reference-compact-uk', TYPE],
        ['practice-card', will, 'practice-card', SURFACE], ['practice-header', will, 'practice-header', SURFACE], ['practice-body', will, 'practice-body', SURFACE], ['practice-prompt', will, 'practice-prompt', TYPE],
        ['m45-choice', will, 'reference-choice', [...TYPE, ...SURFACE]], ['m45-input', will, 'reference-input', [...TYPE, ...SURFACE]], ['m45-token', will, 'reference-token', [...TYPE, ...SURFACE]],
    ];
    const differences = [], checks = [];
    for (const [name, source, origin, properties] of maps) { const target = row.styles.find(item => item.role === name); if (!target?.elements.length) { checks.push({ role: name, absentTarget: true }); continue; } const expected = role(source, origin); for (const [index, element] of target.elements.entries()) for (const property of properties) { checks.push({ role: name, index, property, reference: source.key + ':' + origin }); if (element.styles[property] !== expected[property]) differences.push({ role: name, index, property, expected: expected[property], actual: element.styles[property] }); } }
    return { differences, comparisons: checks.filter(item => item.property).length, mappings: maps.map(([role, source, origin, properties]) => ({ role, origin: source.key + ':' + origin, properties })), absentTarget: checks.filter(item => item.absentTarget).map(item => item.role), scope: 'Corresponding native surfaces/type/colors; composition-specific margins/gaps measured but not assumed equal.' };
}
async function cssValues(locator, properties = TYPE) { return locator.evaluate((node, properties) => Object.fromEntries(properties.map(property => [property, getComputedStyle(node).getPropertyValue(property)])), properties); }
async function calibrateFeedback(page) {
    const component = page.locator('[data-m44-practice-ui] [x-data^="m44PracticeUi"]'), controls = await component.evaluate(node => JSON.parse(JSON.stringify(window.Alpine.$data(node).cases[0].controls)));
    const card = page.locator('[data-m44-ui-case="1"]'), result = {};
    for (const correct of [false, true]) { await card.locator('[data-m44-reset]').click(); for (const control of controls) { const field = card.locator('[data-m44-control="' + control.id + '"]'); if (control.kind === 'manual') await field.locator('[data-m44-answer-input]').fill(correct ? control.answer : 'invalid reference probe'); else await field.locator('[data-m44-answer="' + (correct ? control.answer : control.options.find(option => option.value !== control.answer).value) + '"]').click(); } await card.locator('[data-m44-check]').click(); await reference.settleStyles(page, '.theory-exercise'); const feedback = card.locator('[data-m44-case-feedback]'); await feedback.waitFor({ state: 'visible' }); result[correct ? 'correct' : 'wrong'] = await cssValues(feedback); }
    await card.locator('[data-m44-reset]').click(); return { ...result, scope: 'Fresh unchanged reference, actual UI clicks/fill, client-only memory, no M45 answers used.' };
}
async function layoutCheck(page) {
    return page.evaluate(() => { const main = document.querySelector('[data-theory-main]'), scope = '[data-m45-basic-point],[data-m45-form-card],[data-m45-form-row],.theory-example,p[lang],textarea,[data-m45-token],[data-m45-answer]'; const overflows = [...main.querySelectorAll(scope)].filter(node => node.getClientRects().length && node.scrollWidth > node.clientWidth + 1).map(node => ({ id: node.id || null, tag: node.tagName, scope: node.closest('[data-m45-basic-point],[data-m45-form-card],[data-m45-ui-case]')?.id || null, overflow: node.scrollWidth - node.clientWidth, text: node.textContent.trim().slice(0, 100) })); return { learningOverflows: overflows, decorativeDocumentOverflow: Math.max(0, document.documentElement.scrollWidth - innerWidth), roundingAllowanceCssPx: 1 }; });
}
async function scrollTiles(page, dir, stem) {
    const result = []; await page.evaluate(() => scrollTo(0, 0));
    for (let index = 0; index < 100; index++) { const position = await page.evaluate(() => ({ scrollY, height: document.documentElement.scrollHeight, viewport: innerHeight, end: scrollY + innerHeight >= document.documentElement.scrollHeight - 1 })); result.push({ ...position, screenshot: await shot(page, dir, stem + '-closed-scroll-' + (index + 1) + '.png') }); if (position.end) break; await page.evaluate(() => scrollBy(0, innerHeight * .65)); }
    assert.ok(result.at(-1).end, 'Actual normal-header scroll reaches footer'); return result;
}
async function detailsQA(page, lesson, dir, stem) {
    const all = page.locator('[data-m45-detail]'), count = await all.count(), openCount = () => all.evaluateAll(nodes => nodes.filter(node => node.open).length); assert.equal(await openCount(), 0);
    const screenshots = [];
    for (let index = 0; index < count; index++) { const item = all.nth(index), summary = item.locator(':scope > summary'); await summary.focus(); await summary.press(index % 2 ? 'Enter' : 'Space'); assert.equal(await item.evaluate(node => node.open), true); assert.equal(await openCount(), 1); assert.equal(await item.locator('details').count(), 0); screenshots.push(await shot(item, dir, stem + '-detail-' + (index + 1) + '.png')); await summary.press(index % 2 ? 'Space' : 'Enter'); assert.equal(await item.evaluate(node => node.open), false); }
    if (count) { await all.first().locator(':scope > summary').focus(); await all.first().locator(':scope > summary').press('Enter'); const expected = await all.evaluateAll(nodes => nodes.map(node => node.open)); await page.emulateMedia({ media: 'print' }); await page.waitForFunction(() => [...document.querySelectorAll('[data-m45-detail]')].every(node => node.open)); await page.emulateMedia({ media: 'screen' }); await page.waitForFunction(expected => JSON.stringify([...document.querySelectorAll('[data-m45-detail]')].map(node => node.open)) === JSON.stringify(expected), expected); await all.first().locator(':scope > summary').focus(); await all.first().locator(':scope > summary').press('Enter'); }
    const anchors = lesson.sections.flatMap(section => [...section.points, ...(section.cards || [])]).filter(point => point.detail).map(point => point.detail.id);
    for (const id of anchors) { await page.evaluate(id => { location.hash = id; }, id); await page.waitForFunction(id => document.getElementById(id)?.closest('details')?.open, id); assert.equal(await openCount(), 1); await page.locator('#' + id + '-toggle').focus(); await page.locator('#' + id + '-toggle').press('Enter'); await page.evaluate(() => history.replaceState(null, '', location.pathname)); }
    await all.evaluateAll(nodes => nodes.forEach(node => { node.open = true; })); const expanded = await fidelity(page, lesson, false); const layout = await layoutCheck(page); assert.deepEqual(layout.learningOverflows, []); await all.evaluateAll(nodes => nodes.forEach(node => { node.open = false; }));
    return { keyboardIndependent: count, deepLinks: anchors, printRestored: true, expandedFidelity: expanded, expandedLayout: layout, screenshots };
}
async function practiceQA(page, lesson, dir, stem, referenceRow) {
    const score = page.locator('[data-m45-ui-score] span'), result = [];
    async function expectScore(value) { await page.waitForFunction(value => document.querySelector('[data-m45-ui-score] span')?.textContent.trim() === String(value), value); assert.equal(norm(await score.textContent()), String(value)); }
    async function answer(card, control, value, keyboard = false) { const field = card.locator('[data-m45-control="' + control.id + '"]'); if (['manual', 'tokens'].includes(control.kind)) await field.locator('[data-m45-answer-input]').fill(value); else { const button = field.locator('[data-m45-answer="' + value + '"]'); if (keyboard) { await button.focus(); await button.press('Space'); } else await button.click(); } }
    async function check(card, correct) { await card.locator('[data-m45-check]').click(); const feedback = card.locator('[data-m45-case-feedback]'); await feedback.waitFor({ state: 'visible' }); assert.equal(norm(await feedback.textContent()), correct ? 'Правильно' : 'Не всі частини відповіді правильні'); await expectScore(correct ? 1 : 0); await reference.settleStyles(page, '.theory-exercise'); assert.deepEqual(await cssValues(feedback), referenceRow.referenceFeedback[correct ? 'correct' : 'wrong'], 'Native settled feedback typography/color'); }
    async function reset(card) { await card.locator('[data-m45-reset]').click(); await card.locator('[data-m45-case-feedback]').waitFor({ state: 'hidden' }); await expectScore(0); assert.equal(await card.locator('[data-m45-answer][aria-checked="true"]').count(), 0); for (const input of await card.locator('[data-m45-answer-input]').all()) assert.equal(await input.inputValue(), ''); }
    async function build(card, control) { const field = card.locator('[data-m45-control="' + control.id + '"]'), words = orderedTokens(control); for (const [index, value] of words.entries()) { const candidates = await field.getByRole('button', { name: value, exact: true }).all(); let selected = null; for (const candidate of candidates) if (await candidate.isEnabled()) { selected = candidate; break; } assert.ok(selected, 'Unused actual token instance: ' + value); if (index % 2) { await selected.focus(); await selected.press('Space'); } else await selected.click(); } assert.equal(await field.locator('[data-m45-answer-input]').inputValue(), words.join(' ')); assert.equal(await field.locator('[data-m45-token]:not([disabled])').count(), 0); }
    for (const [index, task] of lesson.practice.entries()) {
        const card = page.locator('[data-m45-ui-case="' + (index + 1) + '"]'), control = task.controls[0], record = { id: task.id, requiredControls: task.controls.length, aliases: [], screenshots: [], tokens: null }; assert.equal(task.controls.length, 1); await expectScore(0);
        await check(card, false);
        if (['manual', 'tokens'].includes(control.kind)) { await answer(card, control, control.canonical_answer.split(' ')[0]); await check(card, false); record.partial = true; }
        const wrong = ['manual', 'tokens'].includes(control.kind) ? 'invalid probe one' : control.options.find(option => option.value !== control.correct_value).value;
        await answer(card, control, wrong); await check(card, false); await answer(card, control, ['manual', 'tokens'].includes(control.kind) ? 'invalid probe two' : wrong); await check(card, false);
        record.screenshots.push(await shot(card, dir, stem + '-practice-' + (index + 1) + '-second-wrong.png'));
        await answer(card, control, ['manual', 'tokens'].includes(control.kind) ? control.canonical_answer : control.correct_value, true); await card.locator('[data-m45-case-feedback]').waitFor({ state: 'hidden' });
        if (['manual', 'tokens'].includes(control.kind)) { const input = card.locator('[data-m45-answer-input]'); assert.equal(await input.isEditable(), true); await input.press('Control+Enter'); await card.locator('[data-m45-case-feedback]').waitFor({ state: 'visible' }); await expectScore(1); }
        await check(card, true); await check(card, true); assert.equal(await card.locator('[data-m45-part-feedback]').filter({ hasText: 'Перевір цю частину відповіді' }).count(), 0);
        if (control.kind === 'tokens' && control.tokens.includes('?')) {
            const field = card.locator('[data-m45-control="' + control.id + '"]');
            await page.waitForFunction(({ id, count }) => document.querySelector('[data-m45-control="' + id + '"]').querySelectorAll('[data-m45-token][disabled]').length === count, { id: control.id, count: control.tokens.length });
            assert.equal(await field.locator('[data-m45-token]:not([disabled])').count(), 0, 'Typed canonical attached question mark consumes every authored instance');
            await field.locator('[data-m45-answer-input]').fill(control.canonical_answer.replace(/\?/gu, ''));
            await card.locator('[data-m45-case-feedback]').waitFor({ state: 'hidden' });
            await page.waitForFunction(id => document.querySelector('[data-m45-control="' + id + '"]').querySelectorAll('[data-m45-token]:not([disabled])').length === 1, control.id);
            const available = field.locator('[data-m45-token]:not([disabled])'); assert.equal(norm(await available.textContent()), '?'); assert.equal(await available.isEnabled(), true);
            await field.locator('[data-m45-answer-input]').fill(control.canonical_answer); await check(card, true);
            record.typedQuestionPunctuation = { canonicalConsumesAll: control.tokens.length, removingLiteralReturnsOnlyQuestion: true, gradingUnchanged: true };
        }
        record.screenshots.push(await shot(card, dir, stem + '-practice-' + (index + 1) + '-correct.png'));
        for (const alias of [...new Set([...(control.accepted_answers || []), ...[control.canonical_answer, ...(control.accepted_answers || [])].filter(value => value?.includes("'")).map(value => value.replaceAll("'", '’'))])]) { await answer(card, control, '  ' + alias.replace(/ /gu, '  ') + '  '); await card.locator('[data-m45-case-feedback]').waitFor({ state: 'hidden' }); await check(card, true); record.aliases.push(alias); }
        if (control.kind === 'tokens') { await reset(card); await build(card, control); await check(card, true); await card.locator('[data-m45-answer-input]').fill(''); await expectScore(0); assert.equal(await card.locator('[data-m45-token][disabled]').count(), 0, 'Clear returns every duplicate token instance'); await build(card, control); await check(card, true); record.tokens = { count: control.tokens.length, duplicateInstances: control.tokens.length - new Set(control.tokens).size, keyboardBuild: true, clearReturnsAll: true, rebuild: true }; }
        await reset(card); record.screenshots.push(await shot(card, dir, stem + '-practice-' + (index + 1) + '-reset.png')); record.empty = true; record.secondWrong = true; record.editCorrect = true; record.staleRedCleared = true; record.noDoubleScore = true; record.reset = true; result.push(record);
    }
    for (const [index, task] of lesson.practice.entries()) { const card = page.locator('[data-m45-ui-case="' + (index + 1) + '"]'), control = task.controls[0]; await answer(card, control, ['manual', 'tokens'].includes(control.kind) ? control.canonical_answer : control.correct_value); await card.locator('[data-m45-check]').click(); await expectScore(index + 1); }
    await page.locator('[data-m45-ui-case="6"] [data-m45-check]').click(); await expectScore(6); assert.equal(await page.locator('[data-m45-self-check-answers]').evaluateAll(nodes => nodes.filter(node => node.getClientRects().length > 0).length), 6);
    for (const [index] of lesson.practice.entries()) await page.locator('[data-m45-ui-case="' + (index + 1) + '"] [data-m45-reset]').click(); await expectScore(0);
    return { tasks: result, allCorrectScore: 6, repeatedCheckScore: 6, finalResetScore: 0, actualUIOnly: true, programmaticAnswerWrites: false };
}
function pinnedJSON(file, expected) { const bytes = fs.readFileSync(file); assert.equal(sha(bytes), expected, 'Independent input SHA: ' + file); return JSON.parse(bytes); }
function loadAcceptance(beforeLabel) {
    assert.equal(beforeLabel, 'theory-before-v1', 'Exact independent BEFORE');
    const before = pinnedJSON(path.join(PRIVATE, beforeLabel, 'manifest.json'), BEFORE_SHA);
    const httpBefore = pinnedJSON(path.join(PRIVATE, 'http-before-v1/manifest.json'), HTTP_BEFORE_SHA);
    const meta = pinnedJSON(path.join(PRIVATE, META_PATH), META_SHA), master = pinnedJSON(path.join(WT, MASTER_PATH), MASTER_SHA);
    assert.equal(before.pass, true); assert.equal(before.mode, '--before'); assert.equal(meta.master_sha256, MASTER_SHA);
    assert.deepEqual(master.lessons.map(lesson => lesson.theory_path), TARGETS.map(target => target.path));
    assert.deepEqual(master.lessons.map(lesson => lesson.practice.length), [6, 6, 6]);
    return { before, httpBefore, meta, master, references: [] };
}
function correctedBeforeMeasurements(row) {
    const dom = new JSDOM(row.dom.html);
    try {
        const practiceIds = [...dom.window.document.querySelectorAll('.theory-section-card')].filter(node => node.closest('[data-m45-practice-ui],[data-m44-practice-ui],[data-m43-practice-ui]') || node.matches('[x-data^="theoryPracticeSet"]')).map(node => node.closest('[id]')?.id);
        const theory = row.dom.sections.filter(section => !practiceIds.includes(section.id));
        return { source: 'Original immutable BEFORE section identities/rectangles; corrected classification, not rewritten evidence.', excludedPracticeSectionIds: practiceIds, rawTheorySectionsHeight: row.dom.measurements.theorySectionsHeight, theorySectionsHeight: theory.reduce((sum, section) => sum + section.rect.height, 0), hero: row.dom.measurements.hero, practice: row.dom.measurements.practice, documentHeight: row.dom.measurements.documentHeight };
    } finally { dom.window.close(); }
}
function expectedCases(lesson) {
    return lesson.practice.map((task, index) => ({ id: task.id, source_index: index + 1, scoring: 'all_required_controls', interaction: task.controls[0].kind === 'tokens' ? 'manual' : task.controls[0].kind,
        controls: task.controls.map(control => {
            const result = { id: control.id, kind: control.kind === 'tokens' ? 'manual' : control.kind, source_kind: control.kind, label: control.label_uk, required: true };
            for (const field of ['stimulus_en', 'stimulus_uk']) if (control[field] !== undefined) result[field] = control[field];
            if (['select', 'choice'].includes(control.kind)) Object.assign(result, { options: control.options.map(option => ({ value: option.value, label: option.label_uk })), answer: control.correct_value });
            else Object.assign(result, { options: [], answer: control.canonical_answer, accepted: [...new Set([control.canonical_answer, ...(control.accepted_answers || [])])], tokens: control.tokens || [] });
            return result;
        }) }));
}
function orderedTokens(control) {
    let rest = control.canonical_answer; const available = control.tokens.map((value, index) => ({ value, index })), result = [];
    while (rest.trim()) { rest = rest.trimStart(); const token = available.filter(item => rest.startsWith(item.value)).sort((a, b) => b.value.length - a.value.length)[0]; assert.ok(token, 'Canonical token sequence can be built: ' + control.id); result.push(token.value); rest = rest.slice(token.value.length); available.splice(available.indexOf(token), 1); }
    assert.equal(available.length, 0, 'All authored token instances used'); return result;
}
async function fidelity(page, lesson, closed = true, javaScript = true) {
    const actual = await page.evaluate(() => {
        const text = node => { if (!node) return null; const copy = node.cloneNode(true); copy.querySelectorAll('[data-theory-ui]').forEach(item => item.remove()); return copy.textContent.replace(/\s+/gu, ' ').trim(); };
        const examples = node => [...node.querySelectorAll(':scope > .theory-example')].map(example => { const result = { en: text(example.querySelector('p[lang="en"]')), uk: text(example.querySelector('.theory-translation')) }; const note = example.querySelector('.m43-example-note'); if (note) result.note_uk = text(note); return result; });
        const detail = node => { const item = node.querySelector(':scope > [data-theory-native-extension] > details[data-m45-detail]'); if (!item) return null; const body = item.querySelector(':scope > .theory-section-detail-body'); return { id: item.dataset.m45Detail, title: text(body.querySelector(':scope > h4')), paragraphs_uk: [...body.querySelectorAll(':scope > p[lang="uk"]')].map(text), examples: examples(body), open: item.open, bodyVisible: body.checkVisibility(), nested: item.querySelectorAll('details').length }; };
        const sections = [...document.querySelectorAll('[data-m45-author-section]')].map(section => ({ id: section.dataset.m45AuthorSection, title: text(section.querySelector('.theory-section-title')), points: [...section.querySelectorAll('[data-m45-basic-point]')].map(point => ({ id: point.id, title: text(point.querySelector(':scope > h3')), basic_uk: [...point.querySelectorAll(':scope > p[lang="uk"]')].map(text), examples: examples(point), detail: detail(point) })), cards: [...section.querySelectorAll('[data-m45-form-card]')].map(card => ({ id: card.id, title: text(card.querySelector(':scope > h3')), rows: [...card.querySelectorAll('[data-m45-form-row]')].map(row => ({ label_uk: text(row.querySelector(':scope > p:first-child')), formula: text(row.querySelector(':scope > p[lang="en"]')), en: text(row.querySelector(':scope > div > p[lang="en"]')), uk: text(row.querySelector(':scope > div > p[lang="uk"]')) })), note_uk: text(card.querySelector(':scope > p[lang="uk"]')), detail: detail(card) })), notes_uk: [...section.querySelectorAll(':scope > .theory-section-card > .theory-section-body > p[lang="uk"]')].map(text) }));
        const component = document.querySelector('[data-m45-practice-ui] [x-data^="m45PracticeUi"]');
        const tasks = [...document.querySelectorAll('[data-m45-ui-case]')].map(card => { const prompt = card.querySelector('[data-m45-author-prompt]'), answer = card.querySelector('[data-m45-self-check-answer]'); return { index: Number(card.dataset.m45UiCase), title: text(prompt.querySelector('h4')), paragraphs: [...prompt.querySelectorAll('p[lang="uk"]')].map(text), controls: [...card.querySelectorAll('[data-m45-control]')].map(control => ({ id: control.dataset.m45Control, label: text(control.querySelector('legend')), stimulus_en: text(control.querySelector(':scope > p[lang="en"]')), stimulus_uk: text(control.querySelector(':scope > p[lang="uk"]')), options: [...control.querySelectorAll('[data-m45-answer]')].map(option => ({ value: option.dataset.m45Answer, label: text(option) })), placeholder: control.querySelector('textarea')?.getAttribute('placeholder') || null })), feedback: { paragraphs_uk: [...answer.querySelectorAll(':scope > p[lang="uk"]')].map(text), answer_examples: examples(answer) }, explanationOpen: card.querySelector('[data-m45-ui-explanation]').open }; });
        return { sections, tasks, cases: window.Alpine && component ? JSON.parse(JSON.stringify(window.Alpine.$data(component).cases)) : null, fallback: document.querySelectorAll('[data-m45-static-fallback]').length, bodyText: text(document.querySelector('[data-theory-main]')), score: document.querySelector('[data-m45-ui-score] span')?.textContent.trim(), componentCount: document.querySelectorAll('[data-m45-practice-ui]').length };
    });
    assert.equal(actual.fallback, 0); assert.equal(actual.componentCount, 1); assert.equal(actual.sections.length, 5); assert.equal(actual.tasks.length, 6);
    assert.ok(actual.bodyText.includes(norm(lesson.subtitle_uk)), 'Exact Ukrainian subtitle'); assert.ok(actual.bodyText.includes(norm(lesson.hero_intro_uk)), 'Exact hero intro');
    let fields = 2, details = 0;
    function compareDetail(observed, expected) { if (!expected) return assert.equal(observed, null); assert.ok(observed); const { open, bodyVisible, nested, ...content } = observed; assert.equal(nested, 0); if (closed) { assert.equal(open, false); assert.equal(bodyVisible, false); } else { assert.equal(open, true); assert.equal(bodyVisible, true); } const { reason_uk, ...educational } = expected; assert.deepEqual(content, educational, expected.id); details++; fields += 2 + expected.paragraphs_uk.length + expected.examples.reduce((sum, example) => sum + Object.keys(example).length, 0); }
    lesson.sections.forEach((section, index) => { const observed = actual.sections[index]; assert.equal(observed.id, section.id); assert.equal(observed.title, section.slot + '. ' + section.title); assert.deepEqual(observed.notes_uk, section.notes_uk || []); assert.equal(observed.points.length, section.points.length); assert.equal(observed.cards.length, (section.cards || []).length); fields += 1 + observed.notes_uk.length;
        section.points.forEach((point, number) => { const { detail, ...content } = observed.points[number], { detail: expected, ...educational } = point; assert.deepEqual(content, educational, point.id); compareDetail(detail, expected); fields += 2 + point.basic_uk.length + point.examples.reduce((sum, example) => sum + Object.keys(example).length, 0); });
        (section.cards || []).forEach((card, number) => { const { detail, ...content } = observed.cards[number], { detail: expected, ...educational } = card; assert.deepEqual(content, educational, card.id); compareDetail(detail, expected); fields += 3 + card.rows.length * 4; });
    });
    assert.equal(details, { A: 4, B: 4, C: 5 }[lesson.lesson_code || TARGETS.find(target => target.path === lesson.theory_path).key]);
    lesson.practice.forEach((task, index) => { const observed = actual.tasks[index]; assert.equal(observed.index, index + 1); assert.equal(observed.title, task.title); assert.deepEqual(observed.paragraphs, [task.prompt_uk, ...(task.context_uk ? [task.context_uk] : [])]); assert.deepEqual(observed.feedback, task.feedback); assert.equal(observed.explanationOpen, false); task.controls.forEach((control, number) => { const c = observed.controls[number]; assert.equal(c.id, control.id); assert.equal(c.label, control.label_uk); assert.equal(c.stimulus_en, control.stimulus_en || null); assert.equal(c.stimulus_uk, control.stimulus_uk || null); assert.deepEqual(c.options, (control.options || []).map(option => ({ value: option.value, label: option.label_uk }))); assert.equal(c.placeholder, null); }); fields += 2 + (task.context_uk ? 1 : 0) + task.feedback.paragraphs_uk.length + task.feedback.answer_examples.reduce((sum, example) => sum + Object.keys(example).length, 0); });
    if (javaScript) { assert.deepEqual(actual.cases, expectedCases(lesson), 'Exact live cases/aliases/IDs from independent master'); assert.equal(actual.score, '0'); const initial = await page.locator('[data-m45-practice-ui] [x-data^="m45PracticeUi"]').evaluate(node => { const data = window.Alpine.$data(node); return { answers: JSON.parse(JSON.stringify(data.answers)), checked: JSON.parse(JSON.stringify(data.checked)) }; }); assert.deepEqual(initial.answers, lesson.practice.map(task => task.controls.map(() => ''))); assert.deepEqual(initial.checked, lesson.practice.map(() => false)); }
    return { masterSha256: MASTER_SHA, fields, details, sections: 5, formCards: actual.sections.reduce((sum, section) => sum + section.cards.length, 0), formRows: actual.sections.reduce((sum, section) => sum + section.cards.reduce((n, card) => n + card.rows.length, 0), 0), tasks: 6, requiredControls: lesson.practice.reduce((sum, task) => sum + task.controls.length, 0), explicitNoHint: true, detailReasonRole: 'Editorial metadata, intentionally not learner prose.' };
}
async function captureState(browser, target, state, dir, label) {
    const { viewport, theme } = state, row = { key: target.key, route: target.path, target: Boolean(target.target), viewport, theme, browserVersion: browser.version(), pass: false, screenshots: [], at: new Date().toISOString(), zoom: 'Default 100% only; no real browser zoom claim.' };
    const context = await browser.newContext({ viewport, colorScheme: theme, deviceScaleFactor: 1, serviceWorkers: 'block' });
    await context.addInitScript(value => localStorage.setItem('theme', value), theme);
    const page = await context.newPage(), stem = target.key + '-' + viewport.width + '-' + theme;
    try {
        await observe(context, page, row);
        const response = await page.goto(BASE + target.path, { waitUntil: 'networkidle', timeout: 60000 });
        row.http = { status: response.status(), finalUrl: reference.safeUrl(page.url()), xRobotsTag: response.headers()['x-robots-tag'] || null };
        assert.equal(row.http.status, 200); assert.equal(row.http.finalUrl, BASE + target.path);
        await page.locator('[data-theory-main]').waitFor({ state: 'visible' }); await page.waitForFunction(() => Boolean(window.Alpine));
        if (acceptance && target.target) assert.equal(await page.locator('[data-m45-detail][open]').count(), 0, 'Real fresh initial disclosures closed before diagnostic reset');
        await page.evaluate(value => { document.documentElement.classList.toggle('dark', value === 'dark'); document.querySelector('[data-theory-main]').querySelectorAll('details').forEach(node => { node.open = false; }); }, theme);
        row.fonts = await reference.fontEvidence(page);
        assert.ok(row.fonts.faces.some(face => face.family === 'Manrope') && row.fonts.faces.some(face => face.family === 'Archivo'), 'Actually loaded faces, not fallback-only checks');
        if (acceptance) row.fonts.visibleGlyphs = await visibleFontEvidence(page);
        row.dom = await domSnapshot(page); row.styles = await styles(page);
        assert.equal(row.dom.sidebar.length, 1); assert.equal(row.dom.sidebar[0].visible, viewport.width >= 1024, 'Matching sidebar policy');
        if (acceptance) {
            const document = new JSDOM(await page.content());
            try { row.metadata = metadata(document.window.document); row.jsonLd = legacyHTTP.jsonLd(document.window.document); } finally { document.window.close(); }
            const old = acceptance.before.rows.find(item => item.key === row.key && item.viewport.width === viewport.width && item.theme === theme); assert.ok(old?.pass); assert.equal(row.http.xRobotsTag, old.http.xRobotsTag);
            if (target.target) {
                const lesson = acceptance.master.lessons.find(item => item.theory_path === target.path), expected = acceptance.meta.rows.find(item => item.path === target.path); assert.ok(lesson && expected);
                assert.deepEqual(row.metadata, expected.after_metadata, 'Independent pre-apply metadata expectation'); assert.equal(row.jsonLd.length, 1); assert.equal(row.jsonLd[0].valid, true); assert.deepEqual(row.jsonLd[0].value, expected.after_json_ld);
                row.author = await fidelity(page, lesson); row.design = designCheck(row);
                const document = new JSDOM(old.dom.html), dynamicIds = new Set([...document.window.document.querySelectorAll('[data-sentence-builder] [id]')].map(node => node.id)); document.window.close();
                const anchors = old.dom.ids.filter(id => !dynamicIds.has(id)); for (const id of anchors) assert.ok(row.dom.ids.includes(id), 'Old server anchor preserved: ' + id); assert.equal(new Set(row.dom.ids).size, row.dom.ids.length, 'Unique learning anchors');
                row.beforeAnchors = { verified: anchors.length, dynamicBuilderChildIdsExcluded: [...dynamicIds], scope: 'All original server anchors; random builder child IDs are recorded separately, not treated as article anchors.' };
                row.correctedBeforeMeasurements = correctedBeforeMeasurements(old);
            } else {
                const original = acceptance.httpBefore.rows.find(item => item.route === target.path); assert.ok(original); assert.deepEqual(row.metadata, original.metadata); assert.deepEqual(row.jsonLd, original.jsonLd); row.referenceEquality = referenceEquality(row);
            }
        }
        row.screenshots.push(await shot(page, dir, stem + '-top.png'));
        row.screenshots.push(await shot(page, dir, stem + '-full.png', { fullPage: true }));
        const cards = page.locator('[data-theory-main] .theory-section-card');
        for (let i = 0; i < await cards.count(); i++) if (await cards.nth(i).isVisible()) row.screenshots.push(await shot(cards.nth(i), dir, stem + '-section-' + (i + 1) + '.png'));
        await page.evaluate(() => scrollTo(0, 0));
        for (let i = 0; i < 120; i++) { const done = await page.evaluate(() => { scrollBy(0, innerHeight * .8); return scrollY + innerHeight >= document.documentElement.scrollHeight - 1; }); if (done) break; await page.waitForTimeout(20); }
        row.footerReached = await page.evaluate(() => Boolean(document.querySelector('footer')) && scrollY + innerHeight >= document.documentElement.scrollHeight - 1); assert.ok(row.footerReached);
        row.screenshots.push(await shot(page, dir, stem + '-footer.png'));
        if (acceptance && target.target) {
            const lesson = acceptance.master.lessons.find(item => item.theory_path === target.path), referenceRow = acceptance.references.find(item => item.key === 'M44-WILL' && item.viewport.width === viewport.width && item.theme === theme);
            row.closedScroll = await scrollTiles(page, dir, stem); row.closedLayout = await layoutCheck(page); assert.deepEqual(row.closedLayout.learningOverflows, []);
            row.detailsQA = await detailsQA(page, lesson, dir, stem);
            if (viewport.width === 1440 && theme === 'light') { row.practiceQA = await practiceQA(page, lesson, dir, stem, referenceRow); row.practiceScope = 'All six unique tasks and explicit aliases, once in this desktop/light state.'; }
            else { row.practiceQA = await practiceSmoke(page, lesson.practice[0], referenceRow, dir, stem); row.practiceScope = 'One repeated viewport/theme smoke; unique full-task acceptance is desktop/light, not an inflated total.'; }
            await page.reload({ waitUntil: 'networkidle' }); await page.waitForFunction(() => Boolean(window.Alpine)); await reference.fontEvidence(page); row.reloadFidelity = await fidelity(page, lesson); row.afterLayout = await layoutCheck(page); assert.deepEqual(row.afterLayout.learningOverflows, []);
            assert.deepEqual(row.design.differences, [], 'Native role mismatches: ' + row.design.differences.map(item => item.role + ':' + item.property).join(', '));
        } else if (acceptance && target.key === 'M44-WILL') row.referenceFeedback = await calibrateFeedback(page);
        assert.deepEqual(row.pageErrors, []); assert.deepEqual(row.blocked, []);
        assert.deepEqual(row.failures.filter(item => item.url.startsWith(BASE)), []); assert.deepEqual(row.httpErrors.filter(item => item.url.startsWith(BASE)), []);
        const fontFailures = row.failures.filter(item => item.url.startsWith('https://fonts.')); row.fontFailures = fontFailures;
        assert.deepEqual(fontFailures, [], 'Font resource failures explicitly fail the state'); assert.deepEqual(row.consoleErrors, []);
        row.pass = true;
    } catch (error) { row.failure = { name: error.name, message: error.message, stack: error.stack, actual: error.actual, expected: error.expected, operator: error.operator }; }
    finally { await context.close(); save(dir, stem + '.json', row); }
    console.log(JSON.stringify({ phase: label, key: target.key, width: viewport.width, theme, pass: row.pass, screenshots: row.screenshots.length, designDifferences: row.design?.differences.length || 0, failure: row.failure ? { name: row.failure.name, message: row.failure.message.split('\n')[0] } : null })); return row;
}
async function practiceSmoke(page, task, referenceRow, dir, stem) {
    const index = acceptance.master.lessons.find(lesson => lesson.practice.some(item => item.id === task.id)).practice.findIndex(item => item.id === task.id), card = page.locator('[data-m45-ui-case="' + (index + 1) + '"]'), control = task.controls[0], field = card.locator('[data-m45-control="' + control.id + '"]'), score = page.locator('[data-m45-ui-score] span');
    const feedback = card.locator('[data-m45-case-feedback]');
    const set = async value => { if (['manual', 'tokens'].includes(control.kind)) await field.locator('[data-m45-answer-input]').fill(value); else { const button = field.locator('[data-m45-answer="' + value + '"]'); await button.focus(); await button.press('Space'); } };
    const check = async correct => { await card.locator('[data-m45-check]').click(); await feedback.waitFor({ state: 'visible' }); assert.equal(norm(await feedback.textContent()), correct ? 'Правильно' : 'Не всі частини відповіді правильні'); assert.equal(norm(await score.textContent()), correct ? '1' : '0'); await reference.settleStyles(page, '.theory-exercise'); assert.deepEqual(await cssValues(feedback), referenceRow.referenceFeedback[correct ? 'correct' : 'wrong']); };
    await check(false); const wrong = ['manual', 'tokens'].includes(control.kind) ? 'invalid smoke one' : control.options.find(option => option.value !== control.correct_value).value;
    await set(wrong); await check(false); await set(['manual', 'tokens'].includes(control.kind) ? 'invalid smoke two' : wrong); await check(false);
    await set(['manual', 'tokens'].includes(control.kind) ? control.canonical_answer : control.correct_value); await feedback.waitFor({ state: 'hidden' }); await check(true); await check(true);
    const screenshots = [await shot(card, dir, stem + '-smoke-correct.png')]; await card.locator('[data-m45-reset]').click(); await feedback.waitFor({ state: 'hidden' }); assert.equal(norm(await score.textContent()), '0');
    return { task: task.id, empty: true, secondWrong: true, editCorrect: true, noDoubleScore: true, reset: true, actualUIOnly: true, screenshots };
}
function compareHTTP(after) {
    const observations = [];
    for (const row of after.rows) {
        const old = acceptance.httpBefore.rows.find(item => item.route === row.route); assert.ok(old);
        if (old.error || old.status !== 200) { observations.push({ route: row.route, baselineUnavailable: true, before: old.error || old.status, afterStatus: row.status || null, liveBeforeAfterProof: false }); continue; }
        assert.equal(row.status, old.status, 'HTTP status ' + row.route); assert.equal(row.finalUrl, old.finalUrl); assert.equal(row.xRobotsTag, old.xRobotsTag); assert.equal(row.contentType, old.contentType);
        if (row.target) { const expected = acceptance.meta.rows.find(item => item.path === row.route); assert.deepEqual(row.metadata, expected.after_metadata); assert.equal(row.jsonLd.length, 1); assert.equal(row.jsonLd[0].valid, true); assert.deepEqual(row.jsonLd[0].value, expected.after_json_ld); }
        else { assert.deepEqual(row.metadata, old.metadata, 'Non-target metadata ' + row.route); assert.deepEqual(row.jsonLd, old.jsonLd, 'Non-target structured data ' + row.route); assert.deepEqual(row.learner, old.learner, 'Non-target learner ' + row.route); }
        observations.push({ route: row.route, pass: true, target: row.target });
    }
    for (const [route, before] of Object.entries(acceptance.httpBefore.extras)) for (const field of ['status', 'finalUrl', 'sha256', 'count', 'orderedSha256']) assert.deepEqual(after.extras[route][field], before[field], 'Robots/sitemap ' + route + ':' + field);
    return { observations, sourceManifestSha256: HTTP_BEFORE_SHA, expectedMetadataSha256: META_SHA, sitemapExact: true, noindexExact: true };
}
function targetHTTPExtract() {
    const before = pinnedJSON(path.join(PRIVATE, 'http-before-v1/manifest.json'), HTTP_BEFORE_SHA), rows = before.rows.filter(row => row.target); assert.equal(rows.length, 3); assert.ok(rows.every(row => row.status === 200));
    const file = path.join(PRIVATE, 'http-before-targets-v1.json'); fs.writeFileSync(file, JSON.stringify({ base: BASE, at: before.at, source_manifest: 'http-before-v1/manifest.json', source_manifest_sha256: HTTP_BEFORE_SHA, rows, extras: before.extras, pass: true, generated_from_after: false }, null, 2) + '\n', { flag: 'wx' }); return { file, sha256: sha(fs.readFileSync(file)) };
}
async function supplemental(label) {
    const dir = directory(label), report = { mode: '--supplement', base: BASE, label, masterSha256: MASTER_SHA, startedAt: new Date().toISOString(), sourceBefore: hashes(), rows: [], pass: false, limitations: ['No-JS means theory, conditions and native self-check keys, not automatic scoring.', 'Viewport320 is not real browser zoom.'] }, browser = await chromium.launch({ headless: true, executablePath: CHROME });
    try {
        for (const target of TARGETS) for (const javaScriptEnabled of [false, true]) {
            const viewport = javaScriptEnabled ? { width: 320, height: 844 } : { width: 390, height: 844 }, row = { key: target.key, route: target.path, javaScriptEnabled, viewport, theme: 'light', screenshots: [], pass: false }, context = await browser.newContext({ viewport, colorScheme: 'light', deviceScaleFactor: 1, javaScriptEnabled, serviceWorkers: 'block' }), page = await context.newPage(), stem = target.key + (javaScriptEnabled ? '-320' : '-no-js'); report.rows.push(row);
            try {
                await observe(context, page, row); const response = await page.goto(BASE + target.path, { waitUntil: 'networkidle', timeout: 60000 }); assert.equal(response.status(), 200); row.http = { status: response.status(), xRobotsTag: response.headers()['x-robots-tag'] || null };
                if (javaScriptEnabled) await page.waitForFunction(() => Boolean(window.Alpine)); row.fonts = await reference.fontEvidence(page); row.fonts.visibleGlyphs = await visibleFontEvidence(page);
                const lesson = acceptance.master.lessons.find(item => item.theory_path === target.path); row.author = await fidelity(page, lesson, true, javaScriptEnabled); row.dom = await domSnapshot(page); row.layout = await layoutCheck(page); assert.deepEqual(row.layout.learningOverflows, []);
                row.screenshots.push(await shot(page, dir, stem + '-full.png', { fullPage: true }));
                if (!javaScriptEnabled) {
                    const native = page.locator('[data-m45-detail]'); for (const detail of await native.all()) { await detail.locator(':scope > summary').focus(); await detail.locator(':scope > summary').press('Enter'); assert.equal(await detail.evaluate(node => node.open), true); }
                    const keys = page.locator('[data-m45-ui-explanation]'); for (const key of await keys.all()) { await key.locator(':scope > summary').focus(); await key.locator(':scope > summary').press('Enter'); assert.equal(await key.evaluate(node => node.open), true); assert.equal(await key.locator('[data-m45-self-check-answers]').isVisible(), true); }
                    row.staticTokens = []; for (const [index, task] of lesson.practice.entries()) for (const control of task.controls) if (control.kind === 'tokens') { const tokens = await page.locator('[data-m45-ui-case="' + (index + 1) + '"] [data-m45-static-token]').allTextContents(); assert.deepEqual(tokens.map(norm), control.tokens); row.staticTokens.push({ id: control.id, tokens }); }
                    row.selfCheckKeys = await keys.count(); assert.equal(row.selfCheckKeys, 6); row.theoryDetailsAccessible = await native.count(); row.screenshots.push(await shot(page, dir, stem + '-opened-full.png', { fullPage: true }));
                    row.expectedDisabledScripts = row.failures.filter(item => item.url.startsWith(BASE) && item.type === 'script' && /csp/iu.test(item.reason) && new URL(item.url).pathname.endsWith('.js')); const expectedURLs = new Set(row.expectedDisabledScripts.map(item => item.url)); assert.deepEqual(row.failures.filter(item => item.url.startsWith(BASE) && !expectedURLs.has(item.url)), []);
                } else {
                    const tokenTask = lesson.practice.find(task => task.controls[0].kind === 'tokens'); assert.ok(tokenTask); const caseIndex = lesson.practice.indexOf(tokenTask) + 1, field = page.locator('[data-m45-ui-case="' + caseIndex + '"] [data-m45-control="' + tokenTask.controls[0].id + '"]');
                    const words = orderedTokens(tokenTask.controls[0]); for (const value of words) { let available = null; for (const button of await field.getByRole('button', { name: value, exact: true }).all()) if (await button.isEnabled()) { available = button; break; } assert.ok(available); await available.focus(); await available.press('Space'); }
                    assert.equal(await field.locator('[data-m45-answer-input]').inputValue(), words.join(' ')); await field.locator('[data-m45-answer-input]').press('Control+Enter'); await page.locator('[data-m45-ui-case="' + caseIndex + '"] [data-m45-case-feedback]').waitFor({ state: 'visible' }); assert.equal(norm(await page.locator('[data-m45-ui-score] span').textContent()), '1'); row.screenshots.push(await shot(page.locator('[data-m45-ui-case="' + caseIndex + '"]'), dir, stem + '-tokens-correct.png')); await page.locator('[data-m45-ui-case="' + caseIndex + '"] [data-m45-reset]').click(); assert.equal(norm(await page.locator('[data-m45-ui-score] span').textContent()), '0'); assert.equal(await field.locator('[data-m45-token][disabled]').count(), 0); row.tokenKeyboardReturn = { id: tokenTask.id, instances: words.length, pass: true }; assert.deepEqual(row.failures.filter(item => item.url.startsWith(BASE)), []);
                }
                row.afterLayout = await layoutCheck(page); assert.deepEqual(row.afterLayout.learningOverflows, []); assert.deepEqual(row.blocked, []); assert.deepEqual(row.pageErrors, []); assert.deepEqual(row.httpErrors.filter(item => item.url.startsWith(BASE)), []); assert.deepEqual(row.consoleErrors, []); row.pass = true;
            } catch (error) { row.failure = { name: error.name, message: error.message, stack: error.stack, actual: error.actual, expected: error.expected, operator: error.operator }; }
            finally { await context.close(); save(dir, stem + '.json', row); }
            console.log(JSON.stringify({ supplemental: stem, pass: row.pass, failure: row.failure || null }));
        }
        report.sourceAfter = hashes(); assert.deepEqual(report.sourceAfter, report.sourceBefore); report.pass = report.rows.length === 6 && report.rows.every(row => row.pass);
    } finally { report.finishedAt = new Date().toISOString(); save(dir, 'manifest.json', report); await browser.close(); }
    return report;
}
async function run(mode, label, beforeLabel) {
    if (mode === '--target-http') return targetHTTPExtract();
    assert.ok(['--before', '--after', '--pilot', '--supplement'].includes(mode));
    acceptance = mode === '--before' ? null : loadAcceptance(beforeLabel);
    if (mode === '--supplement') return supplemental(label);
    const dir = directory(label), report = { mode, base: BASE, label, startedAt: new Date().toISOString(), sourceBefore: hashes(), rows: [], pass: false, limitations: ['GET local and site Google Fonts only; all runtime interactions are client-only.', 'Computed roles are representative samples, max four per role; no unsupported complete-design PASS.', 'Viewport/DPR are not browser zoom. Screenshots still require separate visual review.'] };
    if (acceptance) Object.assign(report, { masterSha256: MASTER_SHA, beforeManifestSha256: BEFORE_SHA, httpBeforeSha256: HTTP_BEFORE_SHA, metadataExpectationSha256: META_SHA, referenceCalibration: 'Added roles measured on read-only references AFTER, verified unchanged against independent BEFORE; not relabeled as original BEFORE.' });
    const browser = await chromium.launch({ headless: true, executablePath: CHROME });
    try {
        if (!acceptance) { report.http = await httpCapture(browser, label); assert.ok(report.http.pass, 'Targets/robots/sitemap HTTP BEFORE'); }
        const states = mode === '--pilot' ? [STATES[0]] : STATES, targets = acceptance ? [...REFERENCES, ...(mode === '--pilot' ? [TARGETS[0]] : TARGETS)] : [...TARGETS, ...REFERENCES];
        for (const target of targets) {
            for (let i = 0; i < states.length; i += 2) { const batch = await Promise.all(states.slice(i, i + 2).map(state => captureState(browser, target, state, dir, label))); report.rows.push(...batch); if (acceptance && !target.target) acceptance.references.push(...batch); }
            if (!acceptance && target.key === 'C') { save(dir, 'targets-complete.json', { at: new Date().toISOString(), rows: report.rows.map(row => ({ key: row.key, viewport: row.viewport, theme: row.theme, pass: row.pass })), sourceBefore: report.sourceBefore, sourceAtCheckpoint: hashes() }); console.log(JSON.stringify({ targetBeforeComplete: report.rows.length === 12 && report.rows.every(row => row.pass), directory: dir })); }
        }
        if (acceptance && mode !== '--pilot') { report.http = await httpCapture(browser, label); assert.ok(report.http.pass); report.httpComparison = compareHTTP(report.http); }
        report.sourceAfter = hashes(); assert.deepEqual(report.sourceAfter, report.sourceBefore, 'ROOT source stability through BEFORE capture');
        report.pass = report.rows.length === (mode === '--pilot' ? 4 : 24) && report.rows.every(row => row.pass);
    } finally { report.finishedAt = new Date().toISOString(); save(dir, 'manifest.json', report); await browser.close(); }
    console.log(JSON.stringify({ pass: report.pass, directory: dir, manifestSha256: sha(fs.readFileSync(path.join(dir, 'manifest.json'))), targetStates: report.rows.filter(row => row.target).length, referenceStates: report.rows.filter(row => !row.target).length })); return report;
}
if (require.main === module) run(...process.argv.slice(2)).then(report => { if (report.pass === false) process.exitCode = 1; }).catch(error => { console.error(error.stack); process.exitCode = 1; });
module.exports = { TARGETS, REFERENCES, STATES, run, captureState, domSnapshot, styles, httpCapture, expectedCases, orderedTokens, loadAcceptance, correctedBeforeMeasurements, visibleFontEvidence, fidelity };
