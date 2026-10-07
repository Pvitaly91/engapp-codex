'use strict';
// Finite read-only pre-integration browser evidence for M43 and accepted references.
// Output is private and exclusive. No raw HTML, cookies, headers, storage or tokens.
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const {chromium} = require('playwright');

const BASE = 'http://gramlyze.loc';
const SERVED_ROOT = 'D:/DEV/htdocs/gramlyze.loc';
const OUTPUT = path.join(SERVED_ROOT, 'storage/app/seo-m43-local/browser-before-v1');
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const TARGETS = [
    {key: 'past-perfect-vs-continuous', group: 'M43', title: 'Past Perfect vs Past Perfect Continuous', path: '/theory/tenses/past-perfect-vs-past-perfect-continuous'},
    {key: 'stative-verbs', group: 'M43', title: 'Stative Verbs', path: '/theory/tenses/stative-verbs'},
    {key: 'used-to-would', group: 'M43', title: 'Used to / Would', path: '/theory/tenses/used-to-would'},
    {key: 'm41-past-simple-continuous', group: 'M41', title: 'Past Simple vs Past Continuous', path: '/theory/tenses/past-simple-vs-past-continuous'},
    {key: 'm41-present-simple-continuous', group: 'M41', title: 'Present Simple vs Present Continuous', path: '/theory/tenses/present-simple-vs-present-continuous'},
    {key: 'm41-present-perfect-past', group: 'M41', title: 'Present Perfect vs Past Simple', path: '/theory/tenses/present-perfect-vs-past-simple'},
    {key: 'm26-past-perfect-continuous-forms', group: 'M26', title: 'Past Perfect Continuous: Forms and Use', path: '/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms'},
];
const SOURCE_PATHS = [
    'resources/views/theory/show.blade.php',
    'resources/views/theory/partials/content-block.blade.php',
    'resources/views/theory/partials/point-detail-fragment.blade.php',
    'resources/views/engram/theory/blocks-v3/m41-existing-design-styles.blade.php',
    'resources/views/engram/theory/blocks-v3/m42-native-design-styles.blade.php',
    ...['forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list'].map(name => 'resources/views/engram/theory/blocks-v3/' + name + '.blade.php'),
    ...['TensesPastPerfectVsPastPerfectContinuousTheorySeeder', 'TensesStativeVerbsTheorySeeder', 'TensesUsedToWouldTheorySeeder'].map(name => 'database/seeders/Page_V3/Tenses/' + name + '/definition.json'),
];
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
function publicUrl(value) {
    try { const url = new URL(value); return url.origin + url.pathname; } catch { return '(unavailable URL)'; }
}
function allowedRequest(value, method) {
    try { const url = new URL(value); return method === 'GET' && url.origin === BASE && !url.username && !url.password; } catch { return false; }
}
function sourceHashes() {
    return Object.fromEntries(SOURCE_PATHS.map(file => [file, sha(fs.readFileSync(path.join(SERVED_ROOT, file)))]));
}
function save(name, value) {
    assert.match(name, /^[a-z0-9-]+\.json$/u);
    fs.writeFileSync(path.join(OUTPUT, name), JSON.stringify(value, null, 2) + '\n', {flag: 'wx'});
}
async function screenshot(pageOrLocator, filename, options = {}) {
    assert.match(filename, /^[a-z0-9-]+\.png$/u);
    const destination = path.join(OUTPUT, filename);
    assert.equal(fs.existsSync(destination), false, 'Screenshot evidence is exclusive');
    await pageOrLocator.screenshot({path: destination, animations: 'disabled', timeout: 30000, ...options});
    return {file: filename, bytes: fs.statSync(destination).size, sha256: sha(fs.readFileSync(destination))};
}
async function safeDOM(page) {
    return page.evaluate(() => {
        const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
        const main = document.querySelector('[data-theory-main]');
        if (!main) throw new Error('Educational main is missing');
        function copyText(node) {
            if (!node) return '';
            const clone = node.cloneNode(true);
            clone.querySelectorAll('script,style,noscript,input,textarea,[data-sentence-builder],[data-theory-ui]').forEach(item => item.remove());
            return norm(clone.textContent);
        }
        function css(node) {
            if (!node) return null;
            const style = getComputedStyle(node), rect = node.getBoundingClientRect();
            return {background: style.backgroundColor, border: style.borderColor, borderWidth: style.borderTopWidth, radius: style.borderRadius,
                fontFamily: style.fontFamily, fontSize: style.fontSize, color: style.color, padding: style.padding,
                width: rect.width, overflow: Math.max(0, node.scrollWidth - node.clientWidth)};
        }
        const meta = selector => document.querySelector(selector)?.getAttribute('content') || null;
        const sections = [...main.querySelectorAll('section.theory-native-block')].filter(node => !node.parentElement.closest('section.theory-native-block'));
        const allDetails = [...main.querySelectorAll('[data-theory-native-extension] > details')];
        return {
            metadata: {title: document.title, description: meta('meta[name="description"]'), robots: meta('meta[name="robots"]'),
                canonical: document.querySelector('link[rel="canonical"]')?.getAttribute('href') || null,
                h1: [...document.querySelectorAll('h1')].map(node => norm(node.textContent)),
                ogTitle: meta('meta[property="og:title"]'), ogDescription: meta('meta[property="og:description"]'),
                twitterTitle: meta('meta[name="twitter:title"]'), twitterDescription: meta('meta[name="twitter:description"]')},
            jsonLd: [...document.querySelectorAll('script[type="application/ld+json"]')].map(node => {
                try { const value = JSON.parse(node.textContent); return {valid: true, type: value['@type'] || null}; }
                catch { return {valid: false}; }
            }),
            mainText: copyText(main),
            sections: sections.map(node => ({id: node.id, heading: norm(node.querySelector('.theory-section-header')?.textContent), text: copyText(node),
                card: css(node.querySelector('.theory-section-card')), point: css(node.querySelector('.theory-item')), example: css(node.querySelector('.theory-example')),
                translation: css(node.querySelector('.theory-translation')), english: css(node.querySelector('[lang="en"]')),
                tables: [...node.querySelectorAll('table')].map(table => ({headers: [...table.querySelectorAll('thead th')].map(cell => norm(cell.textContent)),
                    rows: [...table.querySelectorAll('tbody tr')].map(row => [...row.children].map(cell => norm(cell.textContent)))}))})),
            details: allDetails.map(node => ({id: node.querySelector('.theory-point-fragment')?.id || node.id, open: node.open,
                owner: node.closest('.theory-item')?.id || null, text: copyText(node)})),
            practice: {authoredCases: main.querySelectorAll('[data-m41-ui-case],[data-m43-ui-case]').length,
                nativePracticeSets: main.querySelectorAll('[x-data^="theoryPracticeSet("]').length,
                bankWidgets: main.querySelectorAll('[data-sentence-builder]').length,
                buttons: [...main.querySelectorAll('button')].filter(node => !node.closest('[data-sentence-builder]')).map(node => norm(node.textContent)).filter(Boolean)},
            anchors: [...main.querySelectorAll('[id]')].map(node => node.id),
            layout: {viewport: {width: innerWidth, height: innerHeight}, scale: visualViewport.scale,
                mainOverflow: Math.max(0, main.scrollWidth - main.clientWidth), documentOverflow: Math.max(0, document.documentElement.scrollWidth - innerWidth),
                footerPresent: Boolean(document.querySelector('footer')), main: css(main), header: css(document.querySelector('header')), sidebar: css(document.querySelector('[data-theory-aside]')),
                documentHeight: document.documentElement.scrollHeight, dark: document.documentElement.classList.contains('dark'),
                decorativeOverflow: [...document.querySelectorAll('#shell-random-shapes span')].map(node => {const rect = node.getBoundingClientRect(); return Math.max(0, rect.right - innerWidth, -rect.left);}).filter(Boolean)}
        };
    });
}
async function run() {
    assert.equal(fs.existsSync(CHROME), true, 'Known installed Chrome must exist');
    assert.equal(path.resolve(OUTPUT).startsWith(path.resolve(SERVED_ROOT, 'storage/app/seo-m43-local') + path.sep), true);
    assert.equal(fs.existsSync(OUTPUT), false, 'Never overwrite a previous browser-before run');
    fs.mkdirSync(OUTPUT, {recursive: true});
    const report = {schema: 'gramlyze.m43.browser-before.v1', startedAt: new Date().toISOString(), base: BASE,
        conditions: {headlessChromium: CHROME, freshGuestContextPerState: true, theme: 'light', deviceScaleFactor: 1,
            allowedNetwork: 'GET http://gramlyze.loc only; external fonts/assets blocked and separately recorded',
            noAuthOrSavedSession: true, screenshots: 'actual page fullPage plus every native section, including footer',
            limitation: 'BEFORE evidence only. No answer submission, scoring, persistence, detail opening or browser UI zoom acceptance.'},
        sourceHashesBefore: sourceHashes(), rows: [], completed: false};
    let browser;
    try {
        browser = await chromium.launch({headless: true, executablePath: CHROME, args: ['--disable-background-networking']});
        for (const target of TARGETS) for (const width of target.group === 'M43' ? [1440, 390] : [1440]) {
            const viewport = {width, height: width === 1440 ? 1000 : 844};
            const row = {key: target.key, group: target.group, path: target.path, viewport, startedAt: new Date().toISOString(),
                blockedExternal: [], blockedNonGET: [], localFailures: [], localHttpErrors: [], pageErrors: [], consoleErrors: [], screenshots: [], sections: [], pass: false};
            report.rows.push(row);
            const context = await browser.newContext({viewport, colorScheme: 'light', deviceScaleFactor: 1, serviceWorkers: 'block'});
            const page = await context.newPage();
            await context.route('**/*', route => {
                const request = route.request(), url = publicUrl(request.url());
                if (allowedRequest(request.url(), request.method())) return route.continue();
                (request.method() !== 'GET' ? row.blockedNonGET : row.blockedExternal).push({method: request.method(), url, resourceType: request.resourceType()});
                return route.abort('blockedbyclient');
            });
            page.on('pageerror', error => row.pageErrors.push({name: error.name, messageSha256: sha(error.message)}));
            page.on('console', message => {if (message.type() === 'error') row.consoleErrors.push({source: publicUrl(message.location().url), messageSha256: sha(message.text())});});
            page.on('requestfailed', request => {if (new URL(request.url()).origin === BASE) row.localFailures.push({url: publicUrl(request.url()), method: request.method(), reason: request.failure()?.errorText});});
            page.on('response', response => {if (new URL(response.url()).origin === BASE && response.status() >= 400) row.localHttpErrors.push({url: publicUrl(response.url()), status: response.status()});});
            const stem = target.key + '-' + width + '-light';
            try {
                const response = await page.goto(BASE + target.path, {waitUntil: 'domcontentloaded', timeout: 60000});
                row.http = {status: response.status(), finalUrl: publicUrl(page.url()), contentType: response.headers()['content-type'] || null,
                    xRobotsTag: response.headers()['x-robots-tag'] || null};
                assert.equal(response.status(), 200);
                assert.equal(new URL(page.url()).origin, BASE);
                await page.locator('[data-theory-main]').waitFor({state: 'visible', timeout: 30000});
                await page.waitForFunction(() => Boolean(window.Alpine), null, {timeout: 15000});
                await page.waitForFunction(() => [...document.querySelectorAll('[data-sentence-builder]')].every(node => window.Alpine.$data(node)?.questions?.length > 0), null, {timeout: 20000});
                await page.waitForFunction(() => document.fonts.status === 'loaded', null, {timeout: 15000});
                row.screenshots.push(await screenshot(page, stem + '-top.png'));
                const sections = page.locator('[data-theory-main] section.theory-native-block').filter({has: page.locator('.theory-section-header')});
                for (const [index, section] of (await sections.all()).entries()) {
                    await section.scrollIntoViewIfNeeded();
                    const shot = await screenshot(section, stem + '-section-' + String(index + 1).padStart(2, '0') + '.png');
                    row.screenshots.push(shot);
                    row.sections.push({index: index + 1, id: await section.getAttribute('id'), screenshot: shot.file});
                }
                // A full top-to-bottom scroll triggers the same lazy content as a reader.
                row.scrollTrace = [];
                for (let step = 0; step < 160; step++) {
                    const state = await page.evaluate(() => ({y: scrollY, height: innerHeight, total: document.documentElement.scrollHeight}));
                    row.scrollTrace.push(state);
                    if (state.y + state.height >= state.total - 1) break;
                    await page.evaluate(() => scrollBy(0, Math.round(innerHeight * 0.8)));
                    await page.waitForTimeout(50);
                }
                await page.evaluate(() => scrollTo(0, document.documentElement.scrollHeight));
                row.screenshots.push(await screenshot(page, stem + '-bottom.png'));
                await page.evaluate(() => scrollTo(0, 0));
                row.screenshots.push(await screenshot(page, stem + '-full.png', {fullPage: true}));
                row.dom = await safeDOM(page);
                row.dom.mainTextSha256 = sha(row.dom.mainText);
                assert.deepEqual(row.dom.metadata.h1, [target.title]);
                assert.equal(row.dom.layout.dark, false);
                assert.equal(row.dom.jsonLd.every(item => item.valid), true);
                assert.equal(new Set(row.dom.anchors).size, row.dom.anchors.length);
                row.consoleErrors = row.consoleErrors.map(item => ({...item, expectedExternalBlock: row.blockedExternal.some(blocked => blocked.url === item.source)}));
                row.pass = row.pageErrors.length === 0 && row.localHttpErrors.length === 0 && row.localFailures.length === 0 && row.blockedNonGET.length === 0;
            } catch (error) {
                row.failure = {name: error.name, message: String(error.message).split('\n')[0].replace(/https?:\/\/\S+/gu, value => publicUrl(value))};
                try {row.screenshots.push(await screenshot(page, stem + '-failure.png'));} catch {row.failureScreenshotUnavailable = true;}
            } finally {
                row.finishedAt = new Date().toISOString();
                await context.close();
                save(stem + '.json', row);
            }
            console.log(JSON.stringify({path: target.path, width, pass: row.pass, sections: row.sections.length, screenshots: row.screenshots.length, failure: row.failure || null}));
        }
        report.sourceHashesAfter = sourceHashes();
        report.servedSourcesUnchanged = JSON.stringify(report.sourceHashesBefore) === JSON.stringify(report.sourceHashesAfter);
        report.completed = report.rows.length === 10;
        report.pass = report.completed && report.servedSourcesUnchanged && report.rows.every(row => row.pass);
    } finally {
        if (browser) await browser.close();
        report.finishedAt = new Date().toISOString();
        save('manifest.json', report);
    }
    if (!report.pass) process.exitCode = 1;
    return report;
}
if (require.main === module) {
    assert.deepEqual(process.argv.slice(2), ['--run'], 'Explicit --run only; finite target/output scope');
    run().catch(error => {console.error(error.name + ': ' + error.message.split('\n')[0]); process.exitCode = 1;});
}
module.exports = {BASE, OUTPUT, TARGETS, allowedRequest, publicUrl, safeDOM};
