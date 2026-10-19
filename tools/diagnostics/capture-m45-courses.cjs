'use strict';
// Separate course BEFORE/AFTER evidence. Never unlock a guest lesson or write
// progress. A second JavaScript-disabled context observes public fallback content.
const fs = require('node:fs'), path = require('node:path'), assert = require('node:assert/strict'), crypto = require('node:crypto');
const BASE = 'http://gramlyze.loc', SERVED = 'D:/DEV/htdocs/gramlyze.loc', PRIVATE = path.join(SERVED, 'storage/app/seo-m45-local');
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const TARGETS = [
    'future-perfect-vs-future-continuous',
    'future-perfect-vs-future-perfect-continuous',
    'future-continuous-vs-future-perfect-continuous',
].map(slug => ({ slug, path: '/courses/english-grammar-theory/lesson/maibutni-formy/' + slug }));
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const safeUrl = value => { try { const url = new URL(value); return url.origin + url.pathname; } catch { return '(unavailable URL)'; } };
function allowed(value, method) { try { const url = new URL(value); return method === 'GET' && url.origin === BASE && !url.username && !url.password; } catch { return false; } }
const files = [
    'resources/views/courses/theory-lesson.blade.php',
    'resources/views/courses/partials/theory-page-content.blade.php',
    'resources/views/theory/partials/content-block.blade.php',
    ...[
        'FutureFormsFuturePerfectVsFutureContinuousTheorySeeder',
        'FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder',
        'FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder',
    ].map(name => 'database/seeders/Page_V3/FutureForms/' + name + '/definition.json'),
];
function hashes() { return Object.fromEntries(files.map(file => [file, sha(fs.readFileSync(path.join(SERVED, file)))])); }
async function courseDOM(page) {
    return page.evaluate(() => {
        const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
        const content = document.querySelector('[data-theory-lesson-content]');
        if (!content) throw new Error('Course learner content missing');
        const copy = node => {
            const clone = node.cloneNode(true);
            clone.querySelectorAll('script,style,noscript,input,textarea,[data-sentence-builder],[data-theory-ui]').forEach(node => node.remove());
            return norm(clone.textContent);
        };
        const meta = selector => document.querySelector(selector)?.content || null;
        const css = node => {
            if (!node) return null;
            const style = getComputedStyle(node);
            return { background: style.backgroundColor, border: style.borderColor, radius: style.borderRadius, font: style.fontFamily, size: style.fontSize, padding: style.padding };
        };
        return {
            metadata: {
                title: document.title, h1: [...document.querySelectorAll('h1')].map(node => norm(node.textContent)),
                description: meta('meta[name="description"]'), canonical: document.querySelector('link[rel="canonical"]')?.href || null,
                robots: meta('meta[name="robots"]'), ogTitle: meta('meta[property="og:title"]'), ogDescription: meta('meta[property="og:description"]'),
                twitterTitle: meta('meta[name="twitter:title"]'), twitterDescription: meta('meta[name="twitter:description"]'),
            },
            learnerText: copy(content),
            sections: [...content.querySelectorAll('section.theory-native-block')].filter(node => !node.parentElement.closest('section.theory-native-block')).map(node => ({
                id: node.id, text: copy(node), headings: [...node.querySelectorAll('.theory-section-header h2')].map(heading => norm(heading.textContent)),
            })),
            tables: [...content.querySelectorAll('table')].map(table => ({
                heads: [...table.querySelectorAll('thead th')].map(node => norm(node.textContent)),
                rows: [...table.querySelectorAll('tbody tr')].map(row => [...row.children].map(node => copy(node))),
            })),
            links: [...content.querySelectorAll('a[href]')].map(node => ({ label: norm(node.textContent), href: node.getAttribute('href') })),
            anchors: [...content.querySelectorAll('[id]')].map(node => node.id),
            m45AuthorBlocks: content.querySelectorAll('[data-m45-author-section],[data-m45-practice-ui]').length,
            m45Markers: [...document.querySelectorAll('*')].flatMap(node => [...node.attributes].map(attribute => attribute.name).filter(name => name.startsWith('data-m45-'))),
            guest: {
                learnerVisible: content.getClientRects().length > 0 && !content.classList.contains('hidden'),
                lockVisible: document.querySelector('[data-theory-lesson-lock]')?.getClientRects().length > 0,
                status: norm(document.querySelector('[data-theory-lesson-status-label]')?.textContent),
            },
            layout: {
                viewportWidth: innerWidth, documentOverflow: Math.max(0, document.documentElement.scrollWidth - innerWidth),
                contentOverflow: content.getClientRects().length ? Math.max(0, content.scrollWidth - content.clientWidth) : null,
                footer: Boolean(document.querySelector('footer')), card: css(content.querySelector('.theory-section-card')),
                hero: css(content.querySelector('.theory-hero')), header: css(document.querySelector('header')),
            },
        };
    });
}
async function screenshot(pageOrLocator, dir, name, options = {}) {
    assert.equal(fs.existsSync(path.join(dir, name)), false);
    await pageOrLocator.screenshot({ path: path.join(dir, name), animations: 'disabled', ...options });
    return { file: name, sha256: sha(fs.readFileSync(path.join(dir, name))) };
}
function equality(before, after) {
    for (const key of ['metadata', 'learnerText', 'sections', 'tables', 'links', 'anchors', 'm45AuthorBlocks', 'm45Markers', 'guest']) {
        assert.deepEqual(after[key], before[key], 'Protected course ' + key);
    }
    return true;
}
async function run(mode, label, beforeLabel) {
    assert.ok(['--before', '--after'].includes(mode));
    assert.match(label, /^course-[a-z0-9-]+$/u);
    if (mode === '--after') assert.match(beforeLabel, /^course-[a-z0-9-]+$/u);
    const dir = path.join(PRIVATE, label);
    assert.equal(fs.existsSync(dir), false, 'Exclusive course evidence');
    const before = mode === '--after' ? JSON.parse(fs.readFileSync(path.join(PRIVATE, beforeLabel, 'manifest.json'), 'utf8')) : null;
    if (before) { assert.equal(before.base, BASE); assert.equal(before.mode, '--before'); assert.equal(before.pass, true); }
    const { chromium } = require('playwright');
    const browser = await chromium.launch({ headless: true, executablePath: CHROME });
    fs.mkdirSync(dir, { recursive: true });
    const report = {
        startedAt: new Date().toISOString(), mode, base: BASE, sourceBefore: hashes(), rows: [], pass: false,
        limitations: [
            'Fresh guest course progress is not bypassed. JavaScript may lock later lessons; that state is recorded.',
            'Hidden server-delivered text is a preservation snapshot, not an unlocked learner review.',
            'No-JS is a separate public fallback observation, not an unlocked JavaScript session.',
            'External Google Fonts CSS deliberately blocked.',
            'Only GET http://gramlyze.loc; no production, DB, source, config or persistent user progress changes.',
        ],
    };
    try {
        for (const target of TARGETS) for (const javaScriptEnabled of [true, false]) {
            const row = {
                path: target.path, javaScriptEnabled, screenshots: [], blockedExternal: [], blockedNonGET: [],
                localFailures: [], expectedDisabledScripts: [], localHttpErrors: [], pageErrors: [], consoleErrors: [], pass: false,
            };
            report.rows.push(row);
            const context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, colorScheme: 'light', deviceScaleFactor: 1, javaScriptEnabled, serviceWorkers: 'block' });
            const page = await context.newPage(), stem = target.slug + (javaScriptEnabled ? '-guest-js' : '-no-js');
            try {
                await context.route('**/*', route => {
                    const request = route.request();
                    if (allowed(request.url(), request.method())) return route.continue();
                    (request.method() === 'GET' ? row.blockedExternal : row.blockedNonGET).push({ method: request.method(), url: safeUrl(request.url()) });
                    return route.abort('blockedbyclient');
                });
                page.on('pageerror', error => row.pageErrors.push({ name: error.name, messageSha256: sha(error.message) }));
                page.on('console', message => {
                    if (message.type() === 'error') row.consoleErrors.push({ source: safeUrl(message.location().url), messageSha256: sha(message.text()) });
                });
                page.on('requestfailed', request => {
                    if (new URL(request.url()).origin === BASE) {
                        const record = { url: safeUrl(request.url()), reason: request.failure()?.errorText, resourceType: request.resourceType() };
                        const disabled = !javaScriptEnabled && record.reason === 'csp' && record.resourceType === 'script' && new URL(request.url()).pathname.endsWith('.js');
                        (disabled ? row.expectedDisabledScripts : row.localFailures).push(record);
                    }
                });
                page.on('response', response => {
                    if (new URL(response.url()).origin === BASE && response.status() >= 400) row.localHttpErrors.push({ url: safeUrl(response.url()), status: response.status() });
                });
                const response = await page.goto(BASE + target.path, { waitUntil: 'networkidle', timeout: 60000 });
                row.http = { status: response.status(), finalUrl: safeUrl(page.url()), contentType: response.headers()['content-type'], xRobotsTag: response.headers()['x-robots-tag'] || null };
                assert.equal(response.status(), 200);
                assert.equal(safeUrl(page.url()), BASE + target.path);
                if (javaScriptEnabled) await page.waitForFunction(() => Boolean(window.TheoryCourseProgress));
                row.dom = await courseDOM(page);
                row.learnerTextSha256 = sha(row.dom.learnerText);
                assert.ok(row.dom.learnerText.length > 100);
                assert.equal(row.dom.layout.footer, true);
                assert.equal(row.dom.m45AuthorBlocks, 0, 'Course does not opt in to M45');
                assert.deepEqual(row.dom.m45Markers, [], 'No M45 markers in course DOM');
                row.availability = row.dom.guest.learnerVisible ? 'public-visible' : row.dom.guest.lockVisible ? 'guest-gated' : 'not-visible';
                row.screenshots.push(await screenshot(page, dir, stem + '-top.png'));
                if (row.dom.guest.learnerVisible) {
                    for (const [index, section] of (await page.locator('[data-theory-lesson-content] section.theory-native-block').all()).entries()) {
                        row.screenshots.push(await screenshot(section, dir, stem + '-section-' + (index + 1) + '.png'));
                    }
                }
                await page.evaluate(() => scrollTo(0, 0));
                for (let step = 0; step < 150; step++) {
                    const end = await page.evaluate(() => { scrollBy(0, innerHeight * .8); return scrollY + innerHeight >= document.documentElement.scrollHeight - 1; });
                    if (end) break;
                    await page.waitForTimeout(35);
                }
                row.screenshots.push(await screenshot(page, dir, stem + '-footer.png'));
                await page.evaluate(() => scrollTo(0, 0));
                row.screenshots.push(await screenshot(page, dir, stem + '-full.png', { fullPage: true }));
                if (before) {
                    const old = before.rows.find(record => record.path === row.path && record.javaScriptEnabled === javaScriptEnabled);
                    assert.ok(old?.pass);
                    equality(old.dom, row.dom);
                    assert.deepEqual(row.http, old.http);
                    assert.equal(row.availability, old.availability);
                    row.matchesIndependentBefore = true;
                }
                for (const key of ['pageErrors', 'localFailures', 'localHttpErrors', 'blockedNonGET']) assert.deepEqual(row[key], []);
                row.consoleErrors = row.consoleErrors.map(error => ({ ...error, expectedExternalBlock: row.blockedExternal.some(blocked => blocked.url === error.source) }));
                assert.equal(row.consoleErrors.filter(error => !error.expectedExternalBlock).length, 0);
                row.pass = true;
            } catch (error) {
                row.failure = { name: error.name, message: error.message.split('\n')[0] };
            } finally {
                await context.close();
                fs.writeFileSync(path.join(dir, stem + '.json'), JSON.stringify(row, null, 2), { flag: 'wx' });
            }
            console.log(JSON.stringify({ path: row.path, javaScriptEnabled, pass: row.pass, availability: row.availability, learnerVisible: row.dom?.guest.learnerVisible, lockVisible: row.dom?.guest.lockVisible, failure: row.failure || null }));
        }
        report.sourceAfter = hashes();
        assert.deepEqual(report.sourceAfter, report.sourceBefore);
        report.pass = report.rows.length === 6 && report.rows.every(row => row.pass);
    } finally {
        report.finishedAt = new Date().toISOString();
        fs.writeFileSync(path.join(dir, 'manifest.json'), JSON.stringify(report, null, 2), { flag: 'wx' });
        // Keep the completed read-only snapshot even if native Chrome shutdown stalls.
        await browser.close();
    }
    return report;
}
if (require.main === module) {
    const [mode, label, beforeLabel] = process.argv.slice(2);
    run(mode, label, beforeLabel).then(report => { if (!report.pass) process.exitCode = 1; }).catch(error => { console.error(error.message); process.exitCode = 1; });
}
module.exports = { TARGETS, allowed, courseDOM, equality, run };
