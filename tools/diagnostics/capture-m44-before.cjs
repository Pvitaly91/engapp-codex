'use strict';
// Independent pre-integration guest evidence. No production or progress writes.
const fs = require('node:fs'), path = require('node:path'), assert = require('node:assert/strict'), crypto = require('node:crypto');
const {chromium} = require('playwright'), {JSDOM} = require('jsdom');
const BASE = 'http://gramlyze.loc', ROOT = 'D:/DEV/htdocs/gramlyze.loc';
const PRIVATE = path.join(ROOT, 'storage/app/seo-m44-local');
const label = require.main === module ? (process.argv[2] || 'before-v1') : 'before-v1';
assert.match(label, /^before-v[1-9][0-9]*$/u);
const db = JSON.parse(fs.readFileSync(path.join(PRIVATE, 'm44-before-v1.json')));
const reference = '/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms';
const targets = [{key: 'reference', path: reference}, ...db.targets.map((t, i) => ({key: ['A', 'B', 'C'][i], path: t.theory_path})),
    ...['past-perfect-vs-past-perfect-continuous', 'stative-verbs', 'used-to-would'].map((slug, i) => ({key: 'M43-' + ['A', 'B', 'C'][i], path: '/theory/tenses/' + slug}))];
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const properties = ['background-color', 'background-image', 'border-top-width', 'border-top-color', 'border-left-width', 'border-left-color',
    'border-radius', 'box-shadow', 'font-family', 'font-size', 'font-weight', 'font-style', 'line-height', 'color', 'padding-top', 'padding-right',
    'padding-bottom', 'padding-left', 'margin-top', 'margin-bottom', 'gap', 'text-transform', 'letter-spacing'];
const roles = [
    ['section-card', '.theory-section-card'], ['section-heading', '.theory-section-title'],
    ['usage-panel', '.theory-native-block:has(.rounded-full) .theory-item'], ['usage-caption', '.theory-item .uppercase'],
    ['forms-panel', '.theory-item.group.relative'], ['forms-label', '.theory-item.group.relative .uppercase'], ['forms-formula', '.theory-item.group.relative h3'],
    ['example-box', '.theory-example'], ['example-en', '.theory-example p[lang="en"], .theory-example code, code.theory-example'],
    ['example-uk', '.theory-translation'], ['table-scroll', '.theory-table-scroll'], ['table-head', 'thead th'], ['table-cell', 'tbody td'],
    ['disclosure-summary', '[data-theory-native-extension] summary'], ['disclosure-body', '[data-theory-native-extension] .theory-section-detail-body'],
    ['practice-card', '.theory-exercise'], ['practice-header', '.theory-exercise > .border-b'], ['practice-title', '.theory-exercise h3'],
    ['practice-prompt', '.theory-exercise > .border-b > p:first-of-type'], ['practice-control-panel', '.theory-exercise > .p-4 > .bg-white\\/60'],
    ['practice-choice', '.theory-exercise button.uppercase'], ['practice-token', '.theory-exercise button.text-emerald-700'],
    ['practice-input', '.theory-exercise input[data-word-suggestion-input]'], ['practice-task-number', '.theory-exercise > .border-b h3 > span'],
];
const cleanUrl = value => { const url = new URL(value); return url.origin + url.pathname; };
const allowed = request => {
    const url = new URL(request.url());
    return request.method() === 'GET' && (url.origin === BASE || (url.protocol === 'https:' && ['fonts.googleapis.com', 'fonts.gstatic.com'].includes(url.hostname)));
};
function save(dir, name, value) { fs.writeFileSync(path.join(dir, name), JSON.stringify(value, null, 2) + '\n', {flag: 'wx'}); }
async function guestGet(route, accept = 'text/html') {
    let url = new URL(route, BASE), redirects = [];
    for (let turn = 0; turn < 8; turn++) {
        assert.equal(url.origin, BASE);
        const response = await fetch(url, {redirect: 'manual', headers: {Accept: accept}, signal: AbortSignal.timeout(30000)});
        const location = response.headers.get('location');
        if (response.status >= 300 && response.status < 400 && location) { redirects.push({status: response.status, url: cleanUrl(url), location}); url = new URL(location, url); continue; }
        return {at: new Date().toISOString(), route, status: response.status, finalUrl: cleanUrl(url), redirects,
            contentType: response.headers.get('content-type'), xRobotsTag: response.headers.get('x-robots-tag'), body: await response.text()};
    }
    throw Error('Redirect bound exceeded: ' + route);
}
function domSummary(document, selector) {
    const norm = value => String(value || '').replace(/\s+/gu, ' ').trim(), meta = q => document.querySelector(q)?.getAttribute('content') || null;
    const root = document.querySelector(selector), clone = root?.cloneNode(true);
    clone?.querySelectorAll('script,style,noscript,input,textarea,[data-sentence-builder],[data-theory-ui]').forEach(n => n.remove());
    return {metadata: {title: document.title, h1: [...document.querySelectorAll('h1')].map(n => norm(n.textContent)),
        description: meta('meta[name="description"]'), canonical: document.querySelector('link[rel="canonical"]')?.getAttribute('href') || null,
        robots: meta('meta[name="robots"]'), ogTitle: meta('meta[property="og:title"]'), ogDescription: meta('meta[property="og:description"]'),
        twitterTitle: meta('meta[name="twitter:title"]'), twitterDescription: meta('meta[name="twitter:description"]')},
        jsonLd: [...document.querySelectorAll('script[type="application/ld+json"]')].map(n => JSON.parse(n.textContent)),
        learner: root ? {text: norm(clone.textContent), anchors: [...root.querySelectorAll('[id]')].map(n => n.id),
            links: [...root.querySelectorAll('a[href]')].map(n => ({text: norm(n.textContent), href: n.getAttribute('href')})),
            tables: [...root.querySelectorAll('table')].map(n => [...n.querySelectorAll('tr')].map(r => [...r.querySelectorAll('th,td')].map(c => norm(c.textContent))))} : null};
}
async function captureHttp() {
    const dir = path.join(PRIVATE, 'http-' + label); fs.mkdirSync(dir, {recursive: true});
    const prior = require('C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc/tools/diagnostics/capture-m43-http.cjs');
    const routes = [...new Set([...db.targets.flatMap(t => [t.theory_path, t.course_path, t.test_path,
        '/theory/' + t.category_chain.at(-1).slug, ...['en', 'pl'].map(l => '/' + l + t.theory_path), ...t.navigation.map(n => n.url)]), reference, ...prior.targetPaths, ...prior.controls])];
    const rows = [];
    for (const route of routes) {
        try {
            const result = await guestGet(route), body = result.body; delete result.body;
            const dom = new JSDOM(body);
            const selector = route.startsWith('/courses/') ? '[data-theory-lesson-content]' : '[data-theory-main]';
            rows.push({...result, ...domSummary(dom.window.document, selector)}); dom.window.close();
        } catch (error) { rows.push({route, error: error.message}); }
    }
    const extras = {};
    for (const route of ['/robots.txt', '/sitemap.xml']) { const result = await guestGet(route, route.endsWith('xml') ? 'application/xml' : 'text/plain'); const body = result.body; delete result.body; extras[route] = {...result, sha256: sha(body)};
        if (route.endsWith('xml')) { const dom = new JSDOM(body, {contentType: 'text/xml'}); extras[route].urls = [...dom.window.document.querySelectorAll('loc')].map(n => n.textContent); dom.window.close(); }
    }
    const result = {base: BASE, at: new Date().toISOString(), rows, extras}; save(dir, 'manifest.json', result);
    console.log(JSON.stringify({httpRows: rows.length, errors: rows.filter(r => r.error || r.status !== 200).map(r => ({route: r.route, status: r.status, error: r.error})), dir}));
}
async function styles(page) {
    return page.evaluate(({roles, properties}) => roles.map(([role, selector]) => ({role, selector,
        elements: [...document.querySelector('[data-theory-main]').querySelectorAll(selector)].filter(n => n.getClientRects().length && getComputedStyle(n).display !== 'none').slice(0, 6)
            .map(n => ({tag: n.tagName, classes: n.className, text: n.textContent.replace(/\s+/gu, ' ').trim().slice(0, 160),
                styles: Object.fromEntries(properties.map(p => [p, getComputedStyle(n).getPropertyValue(p)]))}))})), {roles, properties});
}
async function captureBrowser() {
    const dir = path.join(PRIVATE, 'browser-' + label); fs.mkdirSync(dir, {recursive: true});
    const browser = await chromium.launch({headless: true, executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe'}), rows = [];
    try {
        for (const viewport of [{width: 1440, height: 1000}, {width: 390, height: 844}]) for (const theme of ['light', 'dark']) for (const target of targets) {
            const context = await browser.newContext({viewport, deviceScaleFactor: 1, colorScheme: theme, serviceWorkers: 'block'}), page = await context.newPage();
            const row = {key: target.key, path: target.path, viewport, theme, errors: [], blocked: [], screenshots: [], pass: false}; rows.push(row);
            const stem = `${target.key}-${viewport.width}-${theme}`;
            await context.addInitScript(theme => { localStorage.setItem('theme', theme); localStorage.setItem('theorySidebarCollapsed', 'false'); }, theme);
            await context.route('**/*', route => { if (allowed(route.request())) { const headers = {...route.request().headers()}; delete headers.cookie; delete headers.referer; delete headers.authorization; return route.continue({headers}); }
                row.blocked.push({method: route.request().method(), url: cleanUrl(route.request().url())}); return route.abort(); });
            page.on('pageerror', e => row.errors.push(e.message));
            try {
                const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle', timeout: 60000}); assert.equal(response.status(), 200);
                await page.evaluate(async () => { await document.fonts.ready; await Promise.all([document.fonts.load('400 16px Manrope', 'Українська English'), document.fonts.load('700 22px Archivo', 'Базова formula')]); await document.fonts.ready; });
                row.fonts = await page.evaluate(() => ({faces: [...document.fonts].filter(f => f.status === 'loaded').map(f => ({family: f.family, style: f.style, weight: f.weight})),
                    manrope: document.fonts.check('400 16px Manrope'), archivo: document.fonts.check('700 22px Archivo'), dpr: devicePixelRatio, innerWidth,
                    sidebar: [...document.querySelectorAll('[data-theory-aside]')].map(n => ({visible: n.getClientRects().length > 0, width: n.getBoundingClientRect().width}))}));
                row.styles = await styles(page);
                const session = await context.newCDPSession(page);
                await session.send('DOM.enable'); await session.send('CSS.enable');
                const document = await session.send('DOM.getDocument');
                row.platformFonts = [];
                for (const selector of ['h1', '.theory-example', '[data-theory-main] p']) { const found = await session.send('DOM.querySelector', {nodeId: document.root.nodeId, selector});
                    if (found.nodeId) row.platformFonts.push({selector, fonts: (await session.send('CSS.getPlatformFontsForNode', {nodeId: found.nodeId})).fonts}); }
                row.dom = domSummary(new JSDOM(await page.content()).window.document, '[data-theory-main]');
                row.http = {status: response.status(), finalUrl: cleanUrl(page.url())};
                const shot = async (locator, name) => { const file = stem + '-' + name + '.png'; assert.equal(fs.existsSync(path.join(dir, file)), false); await locator.screenshot({path: path.join(dir, file), animations: 'disabled'}); row.screenshots.push({file, sha256: sha(fs.readFileSync(path.join(dir, file)))}); };
                await shot(page, 'top');
                const sections = page.locator('[data-theory-main] > * .theory-section-card').filter({visible: true});
                for (let i = 0; i < await sections.count(); i++) await shot(sections.nth(i), 'section-' + (i + 1));
                await shot(page.locator('footer').first(), 'footer');
                await page.evaluate(() => scrollTo(0, 0)); await page.screenshot({path: path.join(dir, stem + '-full.png'), fullPage: true, animations: 'disabled'});
                row.screenshots.push({file: stem + '-full.png', sha256: sha(fs.readFileSync(path.join(dir, stem + '-full.png')))});
                assert.deepEqual(row.errors, []); assert.ok(row.fonts.manrope && row.fonts.archivo); row.pass = true;
            } catch (error) { row.failure = error.message; } finally { await context.close(); save(dir, stem + '.json', row); }
            console.log(JSON.stringify({key: row.key, width: viewport.width, theme, pass: row.pass, failure: row.failure}));
        }
    } finally { await browser.close(); save(dir, 'manifest.json', {base: BASE, at: new Date().toISOString(), rows, pass: rows.length === targets.length * 4 && rows.every(r => r.pass), properties, roles,
        policy: 'Same fonts, DPR1, default100% browser zoom, uncollapsed sidebar; no production/progress requests.'}); }
}
async function main() { if (process.argv[3] !== '--browser-only') await captureHttp(); await captureBrowser(); }
if (require.main === module) main().catch(error => { console.error(error.stack); process.exitCode = 1; });
module.exports = {domSummary, guestGet, roles, properties, allowed, targets, styles};
