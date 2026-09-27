'use strict';
// M11 read-only, local-only acceptance. No session cookies, answer POSTs or unlocks.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const BASE = 'http://gramlyze.loc';
const SLUGS = ['linking-words-reason-result-contrast', 'advanced-linking-devices', 'concessive-and-contrastive-structures'];
const theory = s => '/theory/clauses-and-linking-words/' + s;
const test = s => '/test/clauses-and-linking-words/' + s;
const course = '/courses/english-grammar-theory/lesson/clauses-and-linking-words/' + SLUGS[0];
const PATHS = [...SLUGS.map(theory), ...SLUGS.map(test), course, '/sitemap.xml'];
// The historic M11 CLI keeps its exact plan. Other content packages opt into the
// same read-only runner through a fixed, versioned profile, never a CLI URL.
const M11 = {id: 'm11', slugs: SLUGS, theory, test, course, paths: PATHS};
const sha = s => crypto.createHash('sha256').update(s).digest('hex');
const safeUrl = s => { try { const u = new URL(s); return u.origin + u.pathname; } catch { return '[invalid-url]'; } };
function decision(value, method = 'GET', navigation = false, profile = M11) {
    const u = new URL(value);
    if (!['GET', 'HEAD'].includes(method)) return 'stateful-request';
    if (u.username || u.password) return 'credentialed-url';
    if (navigation) return u.origin === BASE && profile.paths.includes(u.pathname) && !u.search ? null : 'outside-plan';
    return u.origin === BASE || ['fonts.googleapis.com', 'fonts.gstatic.com'].includes(u.hostname) ? null : 'external-host';
}
function metadata(document) {
    const out = {};
    for (const [key, selector, attr] of [
        ['title', 'title'], ['h1', 'h1'], ['description', 'meta[name="description"]', 'content'],
        ['canonical', 'link[rel="canonical"]', 'href'], ['robots', 'meta[name="robots"]', 'content'],
        ['ogDescription', 'meta[property="og:description"]', 'content'], ['twitterDescription', 'meta[name="twitter:description"]', 'content'],
    ]) out[key] = [...document.querySelectorAll(selector)].map(n => attr ? n.getAttribute(attr) : n.textContent.trim());
    return out;
}
async function capture(dir, label, profile = M11) {
    assert.match(label, /^[a-z0-9-]+$/);
    const result = {at: new Date().toISOString(), base: BASE, package: profile.id, rows: []};
    for (const urlPath of profile.paths) {
        const r = await fetch(BASE + urlPath, {headers: {Accept: urlPath.endsWith('.xml') ? 'application/xml' : 'text/html'}, redirect: 'manual', signal: AbortSignal.timeout(60000)});
        const html = await r.text();
        const row = {path: urlPath, status: r.status, location: r.headers.get('location'), contentType: r.headers.get('content-type'), xRobotsTag: r.headers.get('x-robots-tag')};
        if (urlPath === '/sitemap.xml') {
            const dom = new JSDOM(html, {contentType: 'text/xml'});
            row.orderedLocs = [...dom.window.document.querySelectorAll('loc')].map(n => n.textContent);
            row.orderedSha256 = sha(JSON.stringify(row.orderedLocs)); dom.window.close();
        } else {
            const dom = new JSDOM(html); const d = dom.window.document;
            row.metadata = metadata(d);
            const main = d.querySelector('[data-theory-main]') || d.querySelector('[data-theory-lesson-content]') || d.querySelector('main') || d.body;
            row.textSha256 = sha(main.textContent.replace(/\s+/g, ' ').trim());
            row.testLinks = [...d.querySelectorAll('a[href]')].map(n => n.getAttribute('href')).filter(s => profile === M11 ? s.includes('/test/clauses-and-linking-words/') : profile.slugs.some(slug => { try { return new URL(s, BASE).pathname === profile.test(slug); } catch { return false; } }));
            row.selfChecks = d.querySelectorAll('[id^="self-check-"]').length;
            if (profile.extendedChecks) {
                row.selfCheckQuestions = d.querySelectorAll('[id^="self-check-"] > ol > li').length;
                row.selfCheckKeys = d.querySelectorAll('[id^="self-check-"] details > ol > li').length;
            }
            row.anchorPlaceholder = /theory anchor|lesson package/.test(main.textContent);
            row.details = d.querySelectorAll('details').length;
            dom.window.close();
        }
        result.rows.push(row);
    }
    fs.writeFileSync(path.join(dir, label + '-http.json'), JSON.stringify(result, null, 2), {flag: 'wx'});
    console.log(JSON.stringify(result.rows.map(r => ({path: r.path, status: r.status, metadata: r.metadata, tests: r.testLinks, locs: r.orderedLocs?.length})), null, 2));
    return result;
}
function parseCssColor(value) {
    const input = value.trim().toLowerCase();
    if (input === 'transparent') return [0, 0, 0, 0];
    const match = /^(rgba?|color)\((.*)\)$/.exec(input);
    assert.ok(match, 'Unsupported computed CSS color: ' + input);
    const tokens = match[2].trim().split(/[\s,/]+/);
    const normalized = match[1] === 'color';
    if (normalized) assert.equal(tokens.shift(), 'srgb', 'Unsupported CSS color space');
    assert.ok(tokens.length === 3 || tokens.length === 4, 'Invalid computed CSS color');
    const number = token => {
        assert.match(token, /^[-+]?(?:\d*\.)?\d+(?:e[-+]?\d+)?%?$/);
        return Number.parseFloat(token);
    };
    const channels = tokens.slice(0, 3).map(token => {
        const scale = token.endsWith('%') ? 255 / 100 : normalized ? 255 : 1;
        return Math.min(255, Math.max(0, number(token) * scale));
    });
    const alpha = tokens[3] === undefined ? 1 : number(tokens[3]) * (tokens[3].endsWith('%') ? .01 : 1);
    return [...channels, Math.min(1, Math.max(0, alpha))];
}
function readabilitySample({selector, color, backgrounds, fontSize, fontWeight}) {
    const composite = (front, back) => front.slice(0, 3).map((v, i) => v * front[3] + back[i] * (1 - front[3]));
    // Browser returns layers from the sampled element outward. Composite from
    // the root inward, retaining alpha; srgb's 0..1 channels are not RGB bytes.
    const background = [...backgrounds].reverse().reduce((back, c) => composite(parseCssColor(c), back), [255, 255, 255]);
    const foreground = composite(parseCssColor(color), background);
    const luminance = channels => channels.map(v => {v /= 255; return v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4;})
        .reduce((sum, v, i) => sum + v * [.2126, .7152, .0722][i], 0);
    const a = luminance(foreground), b = luminance(background), contrast = (Math.max(a, b) + .05) / (Math.min(a, b) + .05);
    const large = parseFloat(fontSize) >= 24 || (parseFloat(fontSize) >= 18.66 && Number(fontWeight) >= 700);
    const minimum = large ? 3 : 4.5;
    return {selector, color, backgrounds, background: background.map(Math.round), contrast: Number(contrast.toFixed(3)), minimum, passes: contrast >= minimum};
}
async function themeChecks(page, dir, label, slug, mobile, screenshots) {
    const results = [];
    for (const mode of ['light', 'dark']) {
        if (mode === 'dark') {
            // Use the existing public UI; do not inject theme classes or lesson DOM.
            await page.getByRole('button', {name: 'Тема', exact: true}).click();
            await page.waitForFunction(() => document.documentElement.classList.contains('dark'));
        }
        const dark = await page.locator('html').evaluate(n => n.classList.contains('dark'));
        assert.equal(dark, mode === 'dark');
        const exercise = page.locator('#self-check-' + slug);
        const row = {mode, dark, belowFirstScreen: await exercise.evaluate(n => n.getBoundingClientRect().top + scrollY > innerHeight)};
        assert.ok(row.belowFirstScreen);
        row.samples = (await page.locator('[data-theory-main] article .prose').evaluate(box => {
            const backgrounds = node => {
                const layers = [];
                for (let n = node; n; n = n.parentElement) layers.push(getComputedStyle(n).backgroundColor);
                return layers;
            };
            return ['p', 'h4', 'td', 'th', 'summary', 'li', 'code'].map(selector => {
                const node = box.querySelector(selector); if (!node) return null;
                const style = getComputedStyle(node);
                return {selector, color: style.color, backgrounds: backgrounds(node), fontSize: style.fontSize, fontWeight: style.fontWeight};
            }).filter(Boolean);
        })).map(readabilitySample);
        assert.ok(row.samples.length >= 3);
        assert.ok(row.samples.every(s => s.passes), 'Text contrast fails in ' + mode + ': ' + JSON.stringify(row.samples));
        row.overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
        assert.equal(row.overflow, false);
        for (const [part, target] of [['intro', page.locator('h1')], ['keys', exercise.locator('details')]]) {
            await target.scrollIntoViewIfNeeded();
            const name = `${label}-${mobile ? 'mobile' : 'desktop'}-${slug}-${mode}-${part}.png`;
            await page.screenshot({path: path.join(dir, name), animations: 'disabled'}); screenshots.push(name);
        }
        const name = `${label}-${mobile ? 'mobile' : 'desktop'}-${slug}-${mode}-full.png`;
        await page.screenshot({path: path.join(dir, name), fullPage: true, animations: 'disabled'}); screenshots.push(name);
        results.push(row);
    }
    return results;
}
async function browserChecks(dir, label, applied = false, profile = M11) {
    const {slugs: SLUGS, theory, test, course} = profile;
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    const report = {at: new Date().toISOString(), base: BASE, package: profile.id, browser: browser.version(), rows: [], applied,
        scope: 'Real local server responses only; no DOM/fixture substitution, environment override, course unlock or answer submission.'};
    const file = path.join(dir, label + '-browser.json');
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    try {
        for (const mobile of [false, true]) for (const slug of SLUGS) {
            const row = {path: theory(slug), mobile, errors: [], console: [], failed: [], blocked: [], screenshots: []}; report.rows.push(row);
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000}, isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', colorScheme: 'light', serviceWorkers: 'block'});
            await context.route('**/*', async route => {
                const q = route.request(), reason = decision(q.url(), q.method(), q.isNavigationRequest(), profile);
                if (reason) { row.blocked.push({url: safeUrl(q.url()), reason}); await route.abort(); } else await route.continue();
            });
            const page = await context.newPage();
            page.on('pageerror', e => row.errors.push({name: e.name, messageSha256: sha(e.message)}));
            page.on('console', m => {if (['warning', 'error'].includes(m.type())) row.console.push({type: m.type(), textSha256: sha(m.text()), source: safeUrl(m.location().url)});});
            page.on('requestfailed', q => row.failed.push({url: safeUrl(q.url()), reason: q.failure()?.errorText}));
            try {
                const response = await page.goto(BASE + theory(slug), {waitUntil: 'networkidle', timeout: 60000});
                row.status = response.status(); assert.equal(row.status, 200);
                if (applied) {
                    const html = await response.text();
                    const dom = new JSDOM(html);
                    row.metadata = metadata(dom.window.document);
                    row.serverResponseSha256 = sha(html);
                    row.serverQuestions = dom.window.document.querySelectorAll(`#self-check-${slug} > ol > li`).length;
                    row.serverKeys = dom.window.document.querySelectorAll(`#self-check-${slug} details > ol > li`).length;
                    assert.equal(row.serverQuestions, 6); assert.equal(row.serverKeys, 6);
                    assert.ok(!/theory anchor|lesson package/.test(dom.window.document.querySelector('[data-theory-main]').textContent));
                    row.serverContentSha256 = sha(dom.window.document.querySelector('[data-theory-main] article .prose').textContent.replace(/\s+/g, ' ').trim());
                    dom.window.close();
                    const reload = await page.reload({waitUntil: 'networkidle', timeout: 60000});
                    assert.equal(reload.status(), 200);
                    const reloaded = new JSDOM(await reload.text());
                    assert.deepEqual(metadata(reloaded.window.document), row.metadata);
                    row.reloadQuestions = reloaded.window.document.querySelectorAll(`#self-check-${slug} > ol > li`).length;
                    assert.equal(row.reloadQuestions, 6);
                    row.reloadContentSha256 = sha(reloaded.window.document.querySelector('[data-theory-main] article .prose').textContent.replace(/\s+/g, ' ').trim());
                    assert.equal(row.reloadContentSha256, row.serverContentSha256); reloaded.window.close();
                    row.reloadPassed = true;
                }
                row.selfChecks = await page.locator('[id^="self-check-"]').count();
                if (applied) assert.equal(row.selfChecks, 1);
                const box = page.locator('[data-theory-main] article .prose').first();
                await box.scrollIntoViewIfNeeded();
                row.boxVisible = await box.isVisible();
                row.rawMarkup = await box.evaluate(n => /<\/?(?:p|strong|table)\b/.test(n.textContent));
                if (profile.extendedChecks) {
                    const text = (await box.textContent()).replace(/\s+/g, ' ').trim();
                    row.domContentSha256 = sha(text);
                    assert.equal(row.domContentSha256, row.serverContentSha256);
                    assert.match(text, /[А-Яа-яІіЇїЄєҐґ]/u, 'Real lesson must contain Ukrainian explanations');
                }
                if (profile.extendedChecks) {
                    row.keyboardDetails = [];
                    for (const summary of await box.locator('details > summary').all()) {
                        await summary.focus(); await page.keyboard.press('Enter');
                        assert.ok(await summary.evaluate(n => n.parentElement.open));
                        await page.keyboard.press('Space');
                        assert.ok(!(await summary.evaluate(n => n.parentElement.open)));
                        await page.keyboard.press('Enter');
                        assert.ok(await summary.evaluate(n => n.parentElement.open));
                        row.keyboardDetails.push({enterOpens: true, spaceCloses: true, enterReopens: true});
                    }
                    assert.ok(row.keyboardDetails.length > 0);
                } else for (const summary of await box.locator('details > summary').all()) await summary.click();
                if (applied) {
                    const exercise = page.locator('#self-check-' + slug);
                    assert.equal(await exercise.locator('details[open] > ol > li').count(), 6);
                    row.numberedLists = await exercise.locator('ol').evaluateAll(nodes => nodes.every(n => getComputedStyle(n).listStyleType === 'decimal'));
                    assert.ok(row.numberedLists);
                    await exercise.scrollIntoViewIfNeeded();
                    const keyShot = label + '-' + (mobile ? 'mobile-' : 'desktop-') + slug + '-keys.png';
                    await page.screenshot({path: path.join(dir, keyShot), animations: 'disabled'}); row.screenshots.push(keyShot);
                    const tables = await box.locator('table').all();
                    for (const [index, table] of tables.entries()) {
                        await table.scrollIntoViewIfNeeded();
                        const tableShot = label + '-' + (mobile ? 'mobile-' : 'desktop-') + slug + '-table' + (profile.extendedChecks ? '-' + index : '') + '.png';
                        await page.screenshot({path: path.join(dir, tableShot), animations: 'disabled'}); row.screenshots.push(tableShot);
                        if (mobile) {
                            row.tableScroll = await table.evaluate(n => {
                                const wrapper = n.parentElement;
                                wrapper.scrollLeft = wrapper.scrollWidth;
                                return {left: wrapper.scrollLeft, width: wrapper.clientWidth, contentWidth: wrapper.scrollWidth};
                            });
                            assert.ok(profile.extendedChecks && row.tableScroll.contentWidth <= row.tableScroll.width + 2 || row.tableScroll.left > 0);
                            const right = label + '-mobile-' + slug + '-table' + (profile.extendedChecks ? '-' + index : '') + '-right.png';
                            await page.screenshot({path: path.join(dir, right), animations: 'disabled'}); row.screenshots.push(right);
                        }
                    }
                }
                row.overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
                const screenshot = label + '-' + (mobile ? 'mobile-' : 'desktop-') + slug + '.png';
                await page.screenshot({path: path.join(dir, screenshot), fullPage: true, animations: 'disabled'}); row.screenshots.push(screenshot);
                assert.ok(row.boxVisible && !row.rawMarkup && !row.overflow);
                if (profile.extendedChecks) row.themes = await themeChecks(page, dir, label, slug, mobile, row.screenshots);
                const link = page.locator('a[href]').filter({hasText: /Пройти тест/});
                const actual = await link.evaluateAll((nodes, expected) => nodes.filter(n => new URL(n.href).pathname === expected).map(n => n.href), test(slug));
                row.mainTestHref = actual[0] || null;
                if (!mobile) {
                    const candidate = page.locator(`a[href="${actual[0]}"]`).first();
                    assert.ok(actual.length);
                    await candidate.click(); await page.waitForURL(BASE + test(slug));
                    row.mainTestOpened = page.url() === BASE + test(slug);
                    row.testH1 = await page.locator('h1').allTextContents();
                }
                row.pass = true;
                if (applied) assert.equal(row.errors.length, 0);
            } catch (e) { row.pass = false; row.error = {name: e.name, message: e.message.slice(0, 400)}; }
            finally { await context.close(); fs.writeFileSync(file, JSON.stringify(report, null, 2)); }
        }
        const context = await browser.newContext({viewport: {width: 1440, height: 1000}, serviceWorkers: 'block'});
        await context.route('**/*', async route => decision(route.request().url(), route.request().method(), route.request().isNavigationRequest(), profile) ? route.abort() : route.continue());
        const page = await context.newPage(); const response = await page.goto(BASE + course, {waitUntil: 'networkidle', timeout: 60000});
        report.course = {path: course, status: response.status(), h1: await page.locator('h1').allTextContents(), contentVisible: await page.locator('[data-theory-lesson-content]').isVisible()};
        if (profile.extendedChecks) {
            assert.equal(response.status(), 200);
            const dom = new JSDOM(await response.text());
            report.course.serverQuestions = dom.window.document.querySelectorAll(`[id="self-check-${SLUGS[0]}"] > ol > li`).length;
            report.course.serverKeys = dom.window.document.querySelectorAll(`[id="self-check-${SLUGS[0]}"] details > ol > li`).length;
            report.course.gateBypassed = false;
            dom.window.close();
        }
        await page.screenshot({path: path.join(dir, label + '-course.png'), fullPage: true}); await context.close();
    } finally { await browser.close(); fs.writeFileSync(file, JSON.stringify(report, null, 2)); }
    console.log(JSON.stringify({file, rows: report.rows.map(r => ({path: r.path, mobile: r.mobile, pass: r.pass, selfChecks: r.selfChecks, overflow: r.overflow, error: r.error})), course: report.course}, null, 2));
    return report.rows.every(r => r.pass);
}
async function fixtureChecks(dir, label, fixtureDir) {
    // Clearly separate renderer-fixture evidence from live DB acceptance. Only the
    // browser DOM is replaced, inside an ephemeral context; no page/DB is updated.
    assert.ok(fixtureDir, 'Pass the private isolated-test fixture directory');
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    const report = {at: new Date().toISOString(), type: 'isolated-render-fixtures-not-live-content', rows: []};
    const file = path.join(dir, label + '-fixtures.json');
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    try {
        for (const mobile of [false, true]) for (const slug of SLUGS) {
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000}, isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', serviceWorkers: 'block'});
            const row = {slug, mobile, errors: [], failed: [], screenshots: []}; report.rows.push(row);
            await context.route('**/*', async r => decision(r.request().url(), r.request().method(), r.request().isNavigationRequest()) ? r.abort() : r.continue());
            try {
                const page = await context.newPage();
                page.on('pageerror', e => row.errors.push({name: e.name, messageSha256: sha(e.message)}));
                page.on('requestfailed', q => row.failed.push({url: safeUrl(q.url()), reason: q.failure()?.errorText}));
                await page.goto(BASE + theory(slug), {waitUntil: 'networkidle', timeout: 60000});
                const html = fs.readFileSync(path.join(fixtureDir, 'm11-' + slug + '.html'), 'utf8');
                const dom = new JSDOM(html);
                const source = dom.window.document.querySelector('[data-theory-main]');
                assert.ok(source); source.querySelectorAll('script').forEach(n => n.remove());
                await page.locator('[data-theory-main]').evaluate((n, body) => { n.innerHTML = body; }, source.innerHTML);
                dom.window.close();
                const exercise = page.locator('#self-check-' + slug);
                await exercise.scrollIntoViewIfNeeded();
                row.questions = await exercise.locator(':scope > ol > li').count();
                assert.equal(row.questions, 6);
                await exercise.locator('details > summary').click();
                row.keys = await exercise.locator('details[open] > ol > li').count();
                assert.equal(row.keys, 6);
                row.numberedLists = await exercise.locator('ol').evaluateAll(nodes => nodes.every(n => getComputedStyle(n).listStyleType === 'decimal'));
                assert.ok(row.numberedLists);
                row.rawMarkup = await page.locator('[data-theory-main]').evaluate(n => /<\/?(?:p|strong|table)\b/.test(n.textContent));
                row.overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
                assert.ok(!row.rawMarkup && !row.overflow);
                for (const [part, locator] of [['keys', exercise.locator('details')], ['table', page.locator('[data-theory-main] table').first()]]) {
                    if (!(await locator.count())) continue;
                    await locator.scrollIntoViewIfNeeded();
                    const name = label + '-' + (mobile ? 'mobile-' : 'desktop-') + slug + '-' + part + '.png';
                    await page.screenshot({path: path.join(dir, name), animations: 'disabled'}); row.screenshots.push(name);
                    if (part === 'table' && mobile) {
                        row.tableScroll = await locator.evaluate(n => {
                            const wrapper = n.parentElement;
                            wrapper.scrollLeft = wrapper.scrollWidth;
                            return {left: wrapper.scrollLeft, width: wrapper.clientWidth, contentWidth: wrapper.scrollWidth};
                        });
                        assert.ok(row.tableScroll.left > 0, 'Wide comparison table must scroll on mobile');
                        const right = label + '-mobile-' + slug + '-table-right.png';
                        await page.screenshot({path: path.join(dir, right), animations: 'disabled'}); row.screenshots.push(right);
                    }
                }
                const full = label + '-' + (mobile ? 'mobile-' : 'desktop-') + slug + '-full.png';
                await page.screenshot({path: path.join(dir, full), fullPage: true, animations: 'disabled'}); row.screenshots.push(full);
                assert.equal(row.errors.length, 0);
                row.pass = true;
            } catch (e) { row.pass = false; row.error = e.message.slice(0, 400); }
            finally { await context.close(); fs.writeFileSync(file, JSON.stringify(report, null, 2)); }
        }
    } finally { await browser.close(); }
    console.log(JSON.stringify(report, null, 2));
    return report.rows.every(r => r.pass);
}
if (require.main === module) {
    const [mode, dir, label, fixtureDir] = process.argv.slice(2);
    assert.ok(['capture', 'browser', 'browser-applied', 'fixtures'].includes(mode)); assert.match(label || '', /^[a-z0-9-]+$/);
    fs.mkdirSync(dir, {recursive: true});
    (mode === 'capture' ? capture(dir, label) : mode === 'fixtures' ? fixtureChecks(dir, label, fixtureDir) : browserChecks(dir, label, mode === 'browser-applied')).then(ok => {if (ok === false) process.exitCode = 1;}).catch(e => {console.error(e.message); process.exitCode = 1;});
}
module.exports = {decision, metadata, PATHS, M11, capture, browserChecks, parseCssColor, readabilitySample};
