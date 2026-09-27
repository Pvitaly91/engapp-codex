'use strict';
// Real-server, read-only acceptance for the M11/M12 presentation-only change.
// Evidence is private; no fixture substitution, content apply or environment override.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const shared = require('./seo-m11-local.cjs');
const BASE = 'http://gramlyze.loc';
const LESSONS = Object.freeze([
    ['linking-words-reason-result-contrast', '/theory/clauses-and-linking-words/linking-words-reason-result-contrast', '/test/clauses-and-linking-words/linking-words-reason-result-contrast'],
    ['advanced-linking-devices', '/theory/clauses-and-linking-words/advanced-linking-devices', '/test/clauses-and-linking-words/advanced-linking-devices'],
    ['concessive-and-contrastive-structures', '/theory/clauses-and-linking-words/concessive-and-contrastive-structures', '/test/clauses-and-linking-words/concessive-and-contrastive-structures'],
    ['cleft-sentences-basics', '/theory/sentence-structure/cleft-sentences-basics', '/test/sentence-structure/cleft-sentences-basics'],
    ['inversion-basics', '/theory/basic-grammar/word-order/inversion-basics', '/test/word-order/inversion-basics'],
    ['advanced-fronting-and-emphasis', '/theory/basic-grammar/word-order/advanced-fronting-and-emphasis', '/test/word-order/advanced-fronting-and-emphasis'],
    ['present-perfect-forms', '/theory/tenses/present-perfect/present-perfect-forms', null],
    ['past-perfect-forms', '/theory/tenses/past-perfect/past-perfect-forms', null],
].map(row => Object.freeze({slug: row[0], theory: row[1], test: row[2], reference: row[2] === null})));
const PATHS = Object.freeze([...LESSONS.map(l => l.theory), ...LESSONS.map(l => l.test).filter(Boolean), '/sitemap.xml']);
const MODES = Object.freeze(['capture', 'browser', 'browser-styled', 'compare']);
const profile = Object.freeze({id: 'm12-styling', paths: PATHS});
const normalizeText = value => value.replace(/\s+/gu, ' ').trim();
function lessonTextContent(node) {
    const clone = node.cloneNode(true);
    // Script payloads contain per-request CSRF/session values and randomized
    // practice data, not rendered lesson text. Never persist those payloads.
    clone.querySelectorAll('script, style, template').forEach(n => n.remove());
    return normalizeText(clone.textContent);
}
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const safeUrl = value => {try {const u = new URL(value); return u.origin + u.pathname;} catch {return '[invalid-url]';}};
const isFont = value => {try {return ['fonts.googleapis.com', 'fonts.gstatic.com'].includes(new URL(value).hostname);} catch {return false;}};
function decision(value, method = 'GET', navigation = false) {
    try {return shared.decision(value, method, navigation, profile);} catch {return 'invalid-url';}
}
function contentSnapshot(html, urlPath) {
    const dom = new JSDOM(html, {contentType: urlPath === '/sitemap.xml' ? 'text/xml' : 'text/html'});
    const d = dom.window.document;
    let result;
    if (urlPath === '/sitemap.xml') {
        const orderedLocs = [...d.querySelectorAll('loc')].map(n => n.textContent);
        result = {orderedLocs, orderedSha256: sha(JSON.stringify(orderedLocs))};
    } else {
        const main = d.querySelector('[data-theory-main]') || d.querySelector('main') || d.body;
        const lesson = d.querySelector('[data-theory-main] article .prose') || main;
        const normalizedText = lessonTextContent(main);
        const lessonText = lessonTextContent(lesson);
        result = {metadata: shared.metadata(d), normalizedText, textSha256: sha(normalizedText), lessonText, lessonTextSha256: sha(lessonText),
            links: [...d.querySelectorAll('a[href]')].map(n => n.getAttribute('href')),
            mainLinks: [...main.querySelectorAll('a[href]')].map(n => n.getAttribute('href')),
            selfChecks: [...main.querySelectorAll('[id^="self-check-"]')].map(n => ({id: n.id, questions: n.querySelectorAll(':scope > ol > li').length, keys: n.querySelectorAll('details > ol > li').length}))};
    }
    dom.window.close(); return result;
}
async function capture(dir, label) {
    assert.match(label, /^[a-z0-9-]+$/);
    const rows = [];
    // Sequential requests avoid overloading the working local PHP server.
    for (const urlPath of PATHS) {
        const r = await fetch(BASE + urlPath, {redirect: 'manual', signal: AbortSignal.timeout(60000)});
        rows.push({path: urlPath, status: r.status, location: r.headers.get('location'), contentType: r.headers.get('content-type'), xRobotsTag: r.headers.get('x-robots-tag'), ...contentSnapshot(await r.text(), urlPath)});
        console.log(JSON.stringify({path: urlPath, status: r.status}));
    }
    const result = {at: new Date().toISOString(), base: BASE, package: profile.id, rows};
    fs.writeFileSync(path.join(dir, label + '-http.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
    assert.ok(rows.every(r => r.status === 200), 'All fixed local targets must return 200');
    return result;
}
function compareCaptures(before, after) {
    for (const capture of [before, after]) {
        assert.equal(capture.base, BASE); assert.equal(capture.package, profile.id);
        assert.deepEqual(capture.rows.map(r => r.path), PATHS);
    }
    const rows = before.rows.map((old, index) => {
        const next = after.rows[index];
        assert.equal(old.status, 200, old.path); assert.equal(next.status, 200, old.path);
        for (const field of ['location', 'contentType', 'xRobotsTag']) assert.deepEqual(next[field], old[field], old.path + ' ' + field);
        const fields = old.path === '/sitemap.xml' ? ['orderedLocs', 'orderedSha256'] : ['metadata', 'normalizedText', 'textSha256', 'lessonText', 'lessonTextSha256', 'links', 'mainLinks', 'selfChecks'];
        for (const field of fields) assert.deepEqual(next[field], old[field], old.path + ' ' + field);
        return {path: old.path, unchanged: true, ...(old.path === '/sitemap.xml' ? {urls: old.orderedLocs.length, orderedSha256: old.orderedSha256} : {textSha256: old.textSha256, links: old.links.length})};
    });
    return {at: new Date().toISOString(), base: BASE, package: profile.id, pass: true, before: before.at, after: after.at, rows};
}
async function browserChecks(dir, label, referencesOnly = false, expectStyled = false) {
    assert.match(label, /^[a-z0-9-]+$/); assert.equal(typeof referencesOnly, 'boolean');
    assert.equal(typeof expectStyled, 'boolean');
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    const report = {at: new Date().toISOString(), base: BASE, package: profile.id, referencesOnly, expectStyled, browser: browser.version(), rows: [],
        scope: 'Actual local HTTP/DOM; GET/HEAD only; no submissions, session import, unlocks, content substitution or application environment override.'};
    const file = path.join(dir, label + '-browser.json');
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    try {
        for (const mobile of [false, true]) for (const lesson of LESSONS.filter(l => !referencesOnly || l.reference)) {
            const row = {path: lesson.theory, reference: lesson.reference, mobile, errors: [], console: [], fontFailures: [], failed: [], blocked: [], themes: [], screenshots: []};
            report.rows.push(row);
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000}, isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', colorScheme: 'light', serviceWorkers: 'block'});
            await context.route('**/*', async route => {
                const q = route.request(); const reason = decision(q.url(), q.method(), q.isNavigationRequest());
                if (reason) {row.blocked.push({url: safeUrl(q.url()), method: q.method(), reason}); await route.abort();} else await route.continue();
            });
            const page = await context.newPage();
            page.on('pageerror', e => row.errors.push({name: e.name, messageSha256: sha(e.message)}));
            page.on('console', m => {if (['warning', 'error'].includes(m.type())) row.console.push({type: m.type(), textSha256: sha(m.text()), source: safeUrl(m.location().url)});});
            page.on('requestfailed', q => (isFont(q.url()) ? row.fontFailures : row.failed).push({url: safeUrl(q.url()), reason: q.failure()?.errorText}));
            try {
                const response = await page.goto(BASE + lesson.theory, {waitUntil: 'networkidle', timeout: 60000});
                row.status = response.status(); assert.equal(row.status, 200);
                const server = contentSnapshot(await response.text(), lesson.theory);
                row.serverLessonTextSha256 = server.lessonTextSha256;
                let box = page.locator('[data-theory-main] article .prose').first();
                if (lesson.reference && !(await box.count())) box = page.locator('[data-theory-main]').first();
                assert.equal(await box.count(), 1);
                row.domLessonTextSha256 = sha(normalizeText(await box.textContent()));
                // Legacy reference renderers hydrate their own dynamic presentation;
                // their unmodified server text remains protected by HTTP comparison.
                if (!lesson.reference) assert.equal(row.domLessonTextSha256, row.serverLessonTextSha256, 'DOM must match actual server content');
                row.richSections = await box.locator('.theory-rich-section').count();
                row.richExamples = await box.locator('.theory-rich-example').count();
                if (expectStyled) {
                    if (lesson.reference) assert.equal(row.richSections, 0, 'Old references must keep their renderer');
                    else assert.ok(row.richSections >= 3 && row.richExamples > 0, 'Real response must use the styled section/example renderer');
                }
                row.keyboardDetails = [];
                for (const summary of await box.locator('details > summary').all()) {
                    if (await summary.evaluate(n => n.parentElement.open)) await summary.click();
                    await summary.focus(); await page.keyboard.press('Enter');
                    assert.ok(await summary.evaluate(n => n.parentElement.open));
                    await page.keyboard.press('Space');
                    assert.ok(!(await summary.evaluate(n => n.parentElement.open)));
                    await page.keyboard.press('Enter');
                    assert.ok(await summary.evaluate(n => n.parentElement.open));
                    row.keyboardDetails.push({enterOpens: true, spaceCloses: true, enterReopens: true});
                }
                if (!lesson.reference) {
                    const exercise = box.locator('#self-check-' + lesson.slug);
                    assert.equal(await exercise.count(), 1);
                    row.questions = await exercise.locator(':scope > ol > li').count();
                    row.keys = await exercise.locator('details[open] > ol > li').count();
                    assert.equal(row.questions, 6); assert.equal(row.keys, 6);
                    row.numberedLists = await exercise.locator('ol').evaluateAll(nodes => nodes.every(n => getComputedStyle(n).listStyleType === 'decimal'));
                    assert.ok(row.numberedLists, 'Question and answer lists retain numbering');
                }
                for (const mode of ['light', 'dark']) {
                    if (mode === 'dark') {
                        await page.getByRole('button', {name: 'Тема', exact: true}).click();
                        await page.waitForFunction(() => document.documentElement.classList.contains('dark'));
                    }
                    assert.equal(await page.locator('html').evaluate(n => n.classList.contains('dark')), mode === 'dark');
                    const theme = {mode, tables: []}; row.themes.push(theme);
                    theme.overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
                    assert.equal(theme.overflow, false, 'Document must not overflow');
                    theme.samples = (await box.evaluate(n => {
                        // Legacy color-mix declarations may compute to OKLab. Use
                        // the browser's own color conversion without inserting or
                        // changing any DOM node, instead of approximating the space.
                        const canvas = document.createElement('canvas'); canvas.width = canvas.height = 1;
                        const context = canvas.getContext('2d', {willReadFrequently: true});
                        const srgb = color => {
                            if (/^(?:rgba?\(|color\(srgb |transparent$)/.test(color)) return color;
                            context.clearRect(0, 0, 1, 1); context.fillStyle = color; context.fillRect(0, 0, 1, 1);
                            const [r, g, b, a] = context.getImageData(0, 0, 1, 1).data;
                            return `rgba(${r}, ${g}, ${b}, ${a / 255})`;
                        };
                        const backgrounds = node => {const result = []; for (let p = node; p; p = p.parentElement) result.push(srgb(getComputedStyle(p).backgroundColor)); return result;};
                        return ['p', 'h2', 'h3', 'h4', 'td', 'th', 'summary', 'li', 'code', 'a', '.theory-rich-number', '.theory-rich-example', '.theory-rich-section--warning > .theory-rich-heading', '.theory-rich-practice > .theory-rich-heading'].map(selector => {
                            const node = n.querySelector(selector); if (!node) return null;
                            const s = getComputedStyle(node); return {selector, color: srgb(s.color), backgrounds: backgrounds(node), fontSize: s.fontSize, fontWeight: s.fontWeight};
                        }).filter(Boolean);
                    })).map(shared.readabilitySample);
                    // Keep measurements on old reference pages without retroactively changing their styling contract.
                    if (!lesson.reference) assert.ok(theme.samples.every(s => s.passes), 'Text contrast: ' + mode);
                    for (const [index, table] of (await box.locator('table').all()).entries()) {
                        const scroll = await table.evaluate(n => {
                            let wrapper = n.parentElement;
                            while (wrapper && wrapper !== document.body && !['auto', 'scroll'].includes(getComputedStyle(wrapper).overflowX)) wrapper = wrapper.parentElement;
                            if (!wrapper || wrapper === document.body) return {localized: false, tableWidth: n.getBoundingClientRect().width};
                            const previous = wrapper.scrollLeft; wrapper.scrollLeft = wrapper.scrollWidth;
                            const result = {localized: true, width: wrapper.clientWidth, contentWidth: wrapper.scrollWidth, left: wrapper.scrollLeft};
                            wrapper.scrollLeft = previous; return result;
                        });
                        theme.tables.push({index, ...scroll});
                        if (mobile && !lesson.reference) assert.ok(scroll.localized && (scroll.contentWidth <= scroll.width + 2 || scroll.left > 0), 'Wide tables must scroll locally');
                    }
                    const screenshot = async (part, locator) => {
                        if (!(await locator.count())) return;
                        await locator.first().scrollIntoViewIfNeeded();
                        const name = `${label}-${mobile ? 'mobile' : 'desktop'}-${lesson.slug}-${mode}-${part}.png`;
                        await page.screenshot({path: path.join(dir, name), animations: 'disabled'}); row.screenshots.push(name);
                    };
                    await screenshot('intro', page.locator('h1'));
                    await screenshot('section', box.locator('h2, h3, h4'));
                    await screenshot('table', box.locator('table'));
                    if (mobile && (await box.locator('table').count())) {
                        const table = box.locator('table').first();
                        await table.evaluate(n => {
                            for (let p = n.parentElement; p && p !== document.body; p = p.parentElement) {
                                if (['auto', 'scroll'].includes(getComputedStyle(p).overflowX)) {p.scrollLeft = p.scrollWidth; break;}
                            }
                        });
                        await screenshot('table-right', table);
                        await table.evaluate(n => {
                            for (let p = n.parentElement; p && p !== document.body; p = p.parentElement) {
                                if (['auto', 'scroll'].includes(getComputedStyle(p).overflowX)) {p.scrollLeft = 0; break;}
                            }
                        });
                    }
                    await screenshot('keys', box.locator('[id^="self-check-"] details > summary'));
                    const full = `${label}-${mobile ? 'mobile' : 'desktop'}-${lesson.slug}-${mode}-full.png`;
                    await page.screenshot({path: path.join(dir, full), fullPage: true, animations: 'disabled'}); row.screenshots.push(full);
                }
                if (lesson.test) {
                    const actual = await page.locator('a[href]').evaluateAll((nodes, expected) => nodes.filter(n => new URL(n.href).pathname === expected && /Пройти тест/.test(n.textContent)).map(n => n.href), lesson.test);
                    assert.ok(actual.length, 'Real main test link'); row.mainTestHref = actual[0];
                    await page.locator(`a[href="${actual[0]}"]`).first().click();
                    await page.waitForURL(BASE + lesson.test, {waitUntil: 'networkidle', timeout: 60000});
                    row.mainTestOpened = page.url() === BASE + lesson.test;
                    row.testH1 = await page.locator('h1').allTextContents();
                    assert.ok(row.testH1.length > 0);
                }
                assert.equal(row.errors.length, 0, 'JavaScript errors');
                const unexpectedFailures = row.failed.filter(f => !row.blocked.some(b => b.url === f.url));
                assert.equal(unexpectedFailures.length, 0, 'Non-font resource failures');
                row.pass = true;
            } catch (e) {row.pass = false; row.error = {name: e.name, message: e.message.slice(0, 400)};}
            finally {
                await context.close(); fs.writeFileSync(file, JSON.stringify(report, null, 2));
                console.log(JSON.stringify({path: row.path, mobile, pass: row.pass, error: row.error, fontFailures: row.fontFailures.length}));
            }
        }
    } finally {await browser.close(); fs.writeFileSync(file, JSON.stringify(report, null, 2));}
    return report.rows.every(r => r.pass);
}
if (require.main === module) {
    const [mode, dir, label, afterLabel] = process.argv.slice(2);
    assert.ok(MODES.includes(mode), 'Only capture, browser, browser-styled or compare against the fixed local inventory');
    assert.match(label || '', /^[a-z0-9-]+$/); assert.ok(dir); fs.mkdirSync(dir, {recursive: true});
    (async () => {
        if (mode === 'compare') {
            assert.match(afterLabel || '', /^[a-z0-9-]+$/);
            const read = name => JSON.parse(fs.readFileSync(path.join(dir, name + '-http.json'), 'utf8'));
            const result = compareCaptures(read(label), read(afterLabel));
            fs.writeFileSync(path.join(dir, afterLabel + '-comparison.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
            console.log(JSON.stringify(result, null, 2));
        } else if ((await (mode === 'capture' ? capture(dir, label) : browserChecks(dir, label, false, mode === 'browser-styled'))) === false) process.exitCode = 1;
    })().catch(e => {console.error(e.message); process.exitCode = 1;});
}
module.exports = {BASE, LESSONS, PATHS, MODES, profile, normalizeText, decision, contentSnapshot, capture, compareCaptures, browserChecks};
