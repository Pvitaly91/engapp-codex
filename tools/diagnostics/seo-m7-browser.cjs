'use strict';
// Functional metadata/content acceptance only. No performance or learner-state probes.
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');
const {safeUrl, diagnosticError, requestDecision} = require('./seo-m4-browser.cjs');
const BASE = 'http://gramlyze.loc';
const CASES = [
    ['one-ones', '/theory/zaimennyky-ta-vkazivni-slova/one-ones'],
    ['narrative', '/theory/tenses/narrative-tenses'],
    ['conditionals', '/theory/conditionals/conditional-alternatives-and-nuance'],
];
function decision(value, navigation, redirected, plannedPath) {
    const reason = requestDecision(value); // Production/retired CDN/credential guard; ordinary fonts allowed.
    if (reason) return reason;
    const u = new URL(value);
    if (navigation && (redirected || u.origin !== BASE || u.pathname !== plannedPath || u.search)) return 'outside-navigation-plan';
    return null;
}
async function menu(page, mobile) {
    const region = page.locator(mobile ? '#site-header [x-show="mobile"]' : '#site-header > div > nav').first();
    const toggle = page.getByRole('button', {name: 'Меню', exact: true});
    if (mobile) { await toggle.click(); await region.waitFor({state: 'visible'}); }
    const result = await region.evaluate(node => {
        const paths = [...node.querySelectorAll('a[href]')].filter(a => a.getBoundingClientRect().width > 0).map(a => new URL(a.href).pathname);
        return {theory: paths.includes('/theory'), courses: paths.includes('/courses')};
    });
    if (mobile) { await toggle.click(); await region.waitFor({state: 'hidden'}); }
    return {...result, closed: !mobile || !(await region.isVisible())};
}
async function theoryNavigation(page, mobile) {
    const toggle = page.locator('[data-theory-mobile-nav-toggle]');
    if (mobile) await toggle.click();
    const selector = mobile ? '[data-theory-mobile-nav-panel]' : '[data-theory-desktop-navigation-loader]';
    const panel = page.locator(selector);
    await page.locator(selector + '[aria-busy="false"]').waitFor({state: 'visible'});
    await panel.locator('a[href*="/theory/"]').first().waitFor({state: 'visible'});
    assert.equal(await panel.locator('[role="alert"]').isVisible(), false);
    const links = await panel.locator('a[href*="/theory/"]').count();
    if (mobile) { await toggle.click(); await panel.waitFor({state: 'hidden'}); }
    return {links, loaded: links > 0, closed: !mobile || !(await panel.isVisible())};
}
async function main(label, baseline) {
    assert.match(label || '', /^[a-z0-9_-]+$/i);
    if (baseline) assert.match(baseline, /^[a-z0-9_-]+$/i);
    const dir = path.resolve(__dirname, '../../storage/app/seo-m7-local');
    const file = path.join(dir, label + '-browser.json');
    const previous = baseline ? JSON.parse(fs.readFileSync(path.join(dir, baseline + '-browser.json'), 'utf8')) : null;
    const contextRows = JSON.parse(fs.readFileSync(path.join(dir, 'context.json'), 'utf8'));
    const registry = baseline ? JSON.parse(require('node:child_process').execFileSync(process.env.PHP_BINARY || 'php',
        ['-d', 'opcache.enable_cli=0', '-r', "require 'vendor/autoload.php'; echo json_encode(App\\Support\\TheoryEditorialDescriptions::UK, JSON_THROW_ON_ERROR);"],
        {cwd: path.resolve(__dirname, '../..'), encoding: 'utf8'})) : null;
    const report = {base: BASE, startedAt: new Date().toISOString(), rows: [],
        policy: 'Fresh context per page/viewport; production blocked before navigation; ordinary fonts allowed; no HTML/cookies/answers saved.'};
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    const save = () => fs.writeFileSync(file, JSON.stringify(report, null, 2));
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    report.browser = browser.version();
    try {
        for (const mobile of [false, true]) for (const [name, pagePath] of CASES) {
            const row = {name, path: pagePath, mobile, blocked: [], failures: [], console: [], responses: []};
            report.rows.push(row); save();
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000},
                isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', colorScheme: 'light', serviceWorkers: 'block'});
            await context.route('**/*', async route => {
                const q = route.request();
                const reason = decision(q.url(), q.isNavigationRequest(), !!q.redirectedFrom(), pagePath);
                if (reason) { row.blocked.push({url: safeUrl(q.url()), reason}); await route.abort('blockedbyclient'); }
                else await route.continue();
            });
            const page = await context.newPage();
            const cdp = await context.newCDPSession(page);
            await cdp.send('Network.enable');
            await cdp.send('Network.setBlockedURLs', {urls: ['*://gramlyze.com/*', '*://*.gramlyze.com/*', '*://gramlyze.ub/*', '*://*.gramlyze.ub/*']});
            page.on('console', msg => { if (['error', 'warning'].includes(msg.type())) row.console.push({type: msg.type(), url: safeUrl(msg.location().url),
                resourceFailure: /Failed to load resource|net::ERR_NETWORK_ACCESS_DENIED/.test(msg.text()), ...diagnosticError(new Error(msg.text()))}); });
            page.on('pageerror', e => row.console.push({type: 'pageerror', ...diagnosticError(e)}));
            page.on('requestfailed', q => row.failures.push({url: safeUrl(q.url()), type: q.resourceType(), error: q.failure()?.errorText}));
            page.on('response', r => row.responses.push({url: safeUrl(r.url()), type: r.request().resourceType(), status: r.status()}));
            try {
                const response = await page.goto(BASE + pagePath, {waitUntil: 'load', timeout: 45000});
                assert.equal(response.status(), 200); assert.equal(page.url(), BASE + pagePath);
                assert.match(response.headers()['x-robots-tag'], /noindex/);
                await page.waitForFunction(() => !!window.Alpine);
                await page.evaluate(() => document.fonts.ready);
                row.metadata = await page.evaluate(() => {
                    const result = {};
                    for (const [key, selector, attribute] of [
                        ['title', 'head title', null], ['h1', 'h1', null], ['description', 'head meta[name="description"]', 'content'],
                        ['og:title', 'head meta[property="og:title"]', 'content'], ['twitter:title', 'head meta[name="twitter:title"]', 'content'],
                        ['og:description', 'head meta[property="og:description"]', 'content'], ['twitter:description', 'head meta[name="twitter:description"]', 'content'],
                        ['canonical', 'head link[rel="canonical"]', 'href'], ['robots', 'head meta[name="robots"]', 'content']
                    ]) result[key] = [...document.querySelectorAll(selector)].map(n => attribute ? n.getAttribute(attribute) : n.textContent.trim());
                    return result;
                });
                for (const key of ['description', 'title', 'h1', 'canonical']) assert.equal(row.metadata[key].length, 1);
                assert.deepEqual(row.metadata.description, row.metadata['og:description']);
                assert.deepEqual(row.metadata.description, row.metadata['twitter:description']);
                assert.deepEqual(row.metadata.title, row.metadata['og:title']);
                assert.deepEqual(row.metadata.title, row.metadata['twitter:title']);
                assert.deepEqual(row.metadata.canonical, ['https://gramlyze.com' + pagePath]);
                const ids = contextRows.find(c => c.path === pagePath).blocks.map(b => 'block-' + b.id);
                const visible = await page.evaluate(ids => {
                    const main = document.querySelector('[data-theory-main]');
                    const sections = [...main.children].filter(n => n.tagName === 'SECTION');
                    const nodes = [sections[0], ...sections.filter(n => n.classList.contains('xl:grid-cols-3')),
                        ...ids.map(id => document.getElementById(id)).filter(Boolean)];
                    const text = nodes.map(n => n.innerText).join('\n').replace(/\s+/g, ' ').trim();
                    return {text, rawMarkup: /<\/?(?:p|strong|div|span)\b|&(?:lt|gt|amp);|\{a\d+\}/i.test(text),
                        overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth),
                        fonts: [...document.fonts].map(f => ({family: f.family, status: f.status})),
                        built: [...document.scripts].some(s => s.src.includes('/build/assets/')) && ![...document.scripts].some(s => s.src.includes('@vite/client'))};
                }, ids);
                row.content = {...visible, text: undefined, characters: visible.text.length,
                    sha256: crypto.createHash('sha256').update(visible.text).digest('hex')};
                assert.ok(row.content.characters > 100 && row.content.built && !row.content.rawMarkup);
                row.menu = await menu(page, mobile);
                assert.ok(row.menu.theory && row.menu.courses && row.menu.closed);
                row.theoryNavigation = await theoryNavigation(page, mobile);
                if (previous) {
                    const old = previous.rows.find(r => r.name === name && r.mobile === mobile);
                    const expected = registry[contextRows.find(c => c.path === pagePath).identity];
                    assert.deepEqual(row.metadata.description, [expected]);
                    assert.equal(row.content.sha256, old.content.sha256);
                    for (const key of ['title', 'h1', 'og:title', 'twitter:title', 'canonical', 'robots']) assert.deepEqual(row.metadata[key], old.metadata[key]);
                    row.visibleContentUnchanged = true;
                }
                row.screenshot = `${label}-${name}-${mobile ? 'mobile' : 'desktop'}.png`;
                await page.screenshot({path: path.join(dir, row.screenshot), fullPage: false, animations: 'disabled'});
                row.fontFailures = row.failures.filter(r => r.type === 'font' || /fonts\.(?:googleapis|gstatic)\.com/.test(r.url));
                row.unexpectedFailures = row.failures.filter(r => !row.fontFailures.includes(r));
                row.unexpectedConsole = row.console.filter(r => ['error', 'pageerror'].includes(r.type)
                    && !(r.resourceFailure && row.fontFailures.some(f => f.url === r.url)));
                assert.equal(row.blocked.length, 0); assert.equal(row.unexpectedFailures.length, 0);
                assert.equal(row.responses.filter(r => r.status >= 400).length, 0);
                assert.equal(row.unexpectedConsole.length, 0);
                row.pass = true;
            } catch (e) { row.error = diagnosticError(e); row.pass = false; }
            finally { await context.close(); save(); console.log(JSON.stringify({name, mobile, pass: row.pass, error: row.error, fontFailures: row.fontFailures})); }
        }
    } finally { await browser.close(); report.finishedAt = new Date().toISOString(); report.pass = report.rows.length === 6 && report.rows.every(r => r.pass); save(); }
    if (!report.pass) process.exitCode = 1;
}
module.exports = {decision};
if (require.main === module) main(process.argv[2], process.argv[3]).catch(e => { console.error(diagnosticError(e)); process.exitCode = 1; });
