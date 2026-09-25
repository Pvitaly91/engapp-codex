'use strict';
// Functional checks only; no production requests, learner accounts or global unlock.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {safeUrl, diagnosticError, requestDecision} = require('./seo-m4-browser.cjs');
const BASE = 'http://gramlyze.loc';

function decision(url, navigation, redirected, expectedPath, method = 'GET') {
    const u = new URL(url);
    return requestDecision(url) || (!['GET', 'HEAD'].includes(method) ? 'stateful-request' : null) ||
        (navigation && (redirected || u.origin !== BASE || u.pathname !== expectedPath || u.search) ? 'outside-navigation-plan' : null);
}

async function main(label) {
    assert.match(label || '', /^[a-z0-9-]+$/);
    const dir = path.resolve(__dirname, '../../storage/app/seo-m8-1-local');
    const file = path.join(dir, label + '-browser.json');
    const baseline = JSON.parse(fs.readFileSync(path.join(dir, 'before-http.json')));
    const accepted = JSON.parse(fs.readFileSync(path.join(dir, 'after-http.json')));
    const db = JSON.parse(fs.readFileSync(path.join(dir, 'after-db.json')));
    const report = {at: new Date().toISOString(), base: BASE, rows: [],
        policy: 'Separate ephemeral guest per case. Production blocked before navigation. Native local-only course progress; no server progress writes.'};
    fs.writeFileSync(file, JSON.stringify(report), {flag: 'wx'});
    const save = () => fs.writeFileSync(file, JSON.stringify(report, null, 2));
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    report.browser = browser.version();
    try {
        for (const mobile of [false, true]) for (const expected of accepted.rows.filter(r => r.source_matches && r.path.endsWith('/one-ones'))) {
            const course = expected.path.startsWith('/courses/');
            const item = db.pages.find(p => expected.path.endsWith('/' + p.page.slug));
            const row = {path: expected.path, mobile, course, blocked: [], failures: [], console: [], badResponses: [], blocks: []};
            report.rows.push(row); save();
            const context = await browser.newContext({viewport: mobile ? {width: 390, height: 844} : {width: 1440, height: 1000},
                isMobile: mobile, hasTouch: mobile, locale: 'uk-UA', colorScheme: 'light', serviceWorkers: 'block'});
            await context.route('**/*', async route => {
                const q = route.request();
                const reason = decision(q.url(), q.isNavigationRequest(), !!q.redirectedFrom(), expected.path, q.method());
                if (reason) { row.blocked.push({url: safeUrl(q.url()), reason}); await route.abort('blockedbyclient'); }
                else await route.continue();
            });
            const page = await context.newPage();
            page.setDefaultTimeout(15000);
            page.on('pageerror', e => row.console.push({type: 'pageerror', ...diagnosticError(e)}));
            page.on('console', msg => { if (['error', 'warning'].includes(msg.type())) row.console.push({type: msg.type(),
                url: safeUrl(msg.location().url), resourceFailure: /Failed to load resource/.test(msg.text()), ...diagnosticError(new Error(msg.text()))}); });
            page.on('requestfailed', q => row.failures.push({url: safeUrl(q.url()), error: q.failure()?.errorText}));
            page.on('response', r => { if (r.status() >= 400) row.badResponses.push({url: safeUrl(r.url()), status: r.status()}); });
            try {
                const response = await page.goto(BASE + expected.path, {waitUntil: 'load', timeout: 60000});
                assert.equal(response.status(), 200);
                assert.match(response.headers()['x-robots-tag'], /noindex/);
                assert.equal(page.url(), BASE + expected.path);
                await page.waitForFunction(() => !!window.Alpine);
                if (course) {
                    await page.waitForFunction(() => !!window.TheoryCourseProgress);
                    row.progress = await page.evaluate(() => {
                        const m = window.__THEORY_COURSE_MANIFEST__;
                        const store = window.TheoryCourseProgress.createStore(m.course.slug, m.lessons);
                        const before = store.getLessonStatus(m.lesson.lesson_slug);
                        const lesson = store.findLesson(m.lesson.lesson_slug);
                        if (before === 'locked' && lesson.previous_lesson_slug) store.markLessonCompleted(lesson.previous_lesson_slug, {scorePercent: 100, correct: 1, total: 1});
                        return {before, after: store.getLessonStatus(m.lesson.lesson_slug), mechanism: 'native markLessonCompleted for predecessor, ephemeral localStorage only'};
                    });
                    await page.locator('[data-theory-lesson-content]').waitFor({state: 'visible'});
                    assert.notEqual(row.progress.after, 'locked');
                }
                row.metadata = await page.evaluate(() => {
                    const out = {};
                    for (const [key, selector, attribute] of [
                        ['title', 'head title', null], ['h1', 'h1', null], ['description', 'head meta[name="description"]', 'content'],
                        ['og:title', 'head meta[property="og:title"]', 'content'], ['twitter:title', 'head meta[name="twitter:title"]', 'content'],
                        ['og:description', 'head meta[property="og:description"]', 'content'], ['twitter:description', 'head meta[name="twitter:description"]', 'content'],
                        ['canonical', 'head link[rel="canonical"]', 'href'], ['robots', 'head meta[name="robots"]', 'content']
                    ]) out[key] = [...document.querySelectorAll(selector)].map(n => attribute ? n.getAttribute(attribute) : n.textContent.trim());
                    return out;
                });
                assert.deepEqual(row.metadata, baseline.rows.find(r => r.path === expected.path).metadata);
                const name = (mobile ? 'mobile-' : 'desktop-') + (course ? 'course-' : 'theory-') + item.page.slug;
                row.screenshots = [];
                for (const match of expected.source_matches) {
                    const block = item.blocks.find(b => b.id === match.id);
                    const locator = page.locator('#block-' + block.id);
                    await locator.scrollIntoViewIfNeeded();
                    assert.equal(await locator.isVisible(), true);
                    const evidence = await locator.evaluate((node, expectedBody) => {
                        const rendered = node.querySelector('.prose');
                        const source = new DOMParser().parseFromString(expectedBody, 'text/html');
                        const normalize = text => text.replace(/\s+/g, ' ').trim();
                        const columns = [...rendered.querySelectorAll('.grid > section')].map(n => ({x: n.getBoundingClientRect().x, y: n.getBoundingClientRect().y, width: n.getBoundingClientRect().width}));
                        return {exactText: normalize(rendered.textContent) === normalize(source.body.textContent),
                            listItems: rendered.querySelectorAll('li').length, expectedListItems: source.querySelectorAll('li').length,
                            rawTags: /<\/?(?:strong|p|ul|li|div)\b/.test(rendered.textContent), columns,
                            inViewport: node.getBoundingClientRect().bottom > 0 && node.getBoundingClientRect().top < innerHeight};
                    }, block.body);
                    row.blocks.push({id: block.id, uuid: block.uuid, ...evidence});
                    assert.ok(evidence.exactText && !evidence.rawTags && evidence.inViewport);
                    assert.equal(evidence.listItems, evidence.expectedListItems);
                    if (evidence.columns.length) {
                        assert.equal(evidence.columns.length, 2);
                        assert.ok(mobile ? evidence.columns[1].y > evidence.columns[0].y : evidence.columns[1].x > evidence.columns[0].x);
                    }
                    // Both columns and a genuine below-fold practice/mistakes section.
                    if ([5, 7, 8, 9].includes(block.sort_order)) {
                        const screenshot = label + '-' + name + '-block-' + block.sort_order + '.png';
                        await page.screenshot({path: path.join(dir, screenshot), animations: 'disabled'});
                        row.screenshots.push(screenshot);
                    }
                }
                row.blockOrder = await page.locator('[id^="block-"]').evaluateAll(nodes => nodes.map(n => Number(n.id.slice(6))));
                const wantedOrder = expected.source_matches.map(b => b.id);
                assert.deepEqual(row.blockOrder.filter(id => wantedOrder.includes(id)), wantedOrder);
                const full = label + '-' + name + '-full.png';
                await page.screenshot({path: path.join(dir, full), fullPage: true, animations: 'disabled'});
                row.screenshots.push(full);
                row.overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
                assert.equal(row.overflow, false);
                row.fontLimitation = row.failures.filter(f => /fonts\.(googleapis|gstatic)\.com/.test(f.url));
                assert.deepEqual(row.blocked, []);
                assert.deepEqual(row.badResponses, []);
                assert.deepEqual(row.failures.filter(f => !/fonts\.(googleapis|gstatic)\.com/.test(f.url)), []);
                assert.deepEqual(row.console.filter(e => e.type === 'pageerror' || !e.resourceFailure), []);
                row.pass = true;
            } catch (error) { row.pass = false; row.error = diagnosticError(error); }
            finally { await context.close(); save(); }
        }
    } finally { await browser.close(); }
    report.pass = report.rows.length === 4 && report.rows.every(r => r.pass);
    save();
    console.log(JSON.stringify({file, pass: report.pass, rows: report.rows.map(r => ({path: r.path, mobile: r.mobile, pass: r.pass, blocks: r.blocks.length, error: r.error}))}, null, 2));
    return report.pass;
}

if (require.main === module) main(process.argv[2]).then(ok => {process.exitCode = ok ? 0 : 1;}).catch(e => {console.error(diagnosticError(e)); process.exitCode = 1;});

module.exports = {decision};
