'use strict';
// Local guest GET-only acceptance for approved per-point M26 disclosures.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const ROOT = path.resolve(__dirname, '../..');
const BASE = 'http://gramlyze.loc';
const SOURCE = path.join(ROOT, 'database/content-patches/m26-ppc-point-details.v1.json');
const MASTER = path.join(ROOT, 'docs/content/m26-past-perfect-continuous-detail-master.v1.json');
const POINTS = '[data-theory-native-extension][data-theory-point-index]';
const DETAILS = POINTS + ' > details';
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const sha = value => crypto.createHash('sha256').update(value).digest('hex');

function publicText(value) {
    const document = new JSDOM(String(value || ''));
    const text = norm(document.window.document.body.textContent);
    document.window.close();
    return text;
}
function leaves(value) {
    if (typeof value === 'string') return [publicText(value)].filter(Boolean);
    if (Array.isArray(value)) return value.flatMap(leaves);
    if (value && typeof value === 'object') return Object.entries(value)
        .filter(([key]) => !['color', 'url'].includes(key)).flatMap(([, nested]) => leaves(nested));
    return [];
}
function publicUrl(value) {
    try { const url = new URL(value); return url.origin + url.pathname; } catch { return '(unparseable URL)'; }
}
function errorMessage(error) {
    return String(error?.message || error).replace(/https?:\/\/[^\s\])]+/gu, publicUrl).slice(0, 1800);
}
function packageData() {
    const master = JSON.parse(fs.readFileSync(MASTER, 'utf8'));
    const source = JSON.parse(fs.readFileSync(SOURCE, 'utf8'));
    assert.equal(source.version, 1);
    const authored = new Map(master.targets.flatMap(target => target.blocks.filter(block => block.progressive_v1)
        .map(block => [block.progressive_v1.key, block])));
    const targets = master.targets.map(target => ({path: target.expected_theory_path,
        points: target.blocks.filter(block => block.progressive_v1).flatMap(block => {
            const key = block.progressive_v1.key;
            const entries = source.blocks[key];
            assert.ok(Array.isArray(entries) && entries.length > 0, 'Approved map for every native block required');
            return entries.map((point, index) => {
                const values = point.fragments.map(fragment => {
                    const owner = authored.get(fragment.source);
                    assert.ok(owner, 'Known immutable author source required');
                    let value = owner.native_data[fragment.field];
                    if (Object.hasOwn(fragment, 'index')) value = value[fragment.index];
                    assert.notEqual(value, undefined, 'The author reference must resolve');
                    return value;
                });
                if (point.supplement) values.push(point.supplement);
                return {blockKey: key, index, sectionKey: key + '-point-' + (index + 1),
                    fragments: point.fragments, expectedLeaves: values.flatMap(leaves),
                    units: values.map(value => ({leaves: leaves(value), sha256: sha(JSON.stringify(value))})),
                    expectedSha256: sha(JSON.stringify(values))};
            });
        })}));
    assert.equal(targets.reduce((total, target) => total + target.points.length, 0), 56, 'All 56 approved base points represented');
    assert.equal(authored.size, 14);
    return {targets, sourceSha256: sha(fs.readFileSync(SOURCE)), masterSha256: sha(fs.readFileSync(MASTER))};
}

function fidelity(actual, expected, key) {
    const text = norm(actual);
    for (const leaf of expected.expectedLeaves) assert.ok(text.includes(leaf), key + ' missing approved author/supplement text: ' + leaf);
    return sha(text);
}
function unitFidelity(actualUnits, expected, key) {
    assert.equal(actualUnits.length, expected.units.length, key + ' must contain only its approved fragment/supplement units');
    for (const [index, unit] of expected.units.entries()) {
        const text = norm(actualUnits[index]);
        for (const leaf of unit.leaves) assert.ok(text.includes(leaf), key + ' missing/reordered approved unit ' + index + ': ' + leaf);
    }
}
function publicMetadata(document) {
    return {title: document.title, h1: [...document.querySelectorAll('h1')].map(node => norm(node.textContent)),
        canonical: document.querySelector('link[rel="canonical"]')?.getAttribute('href') || null,
        robots: [...document.querySelectorAll('meta[name="robots"]')].map(node => node.content)};
}
function assertBasics(document, baseline) {
    const results = [];
    for (const basic of baseline.basics) {
        const node = document.getElementById('block-' + basic.id);
        assert.ok(node, 'Full original basic block present: ' + basic.id);
        assert.equal(Boolean(node.closest('details')), false, 'Original basic block outside details');
        const copy = node.cloneNode(true);
        copy.querySelectorAll('[data-theory-native-extension]').forEach(extension => extension.remove());
        assert.equal(norm(copy.textContent), basic.text, 'Complete original teaching text unchanged: ' + basic.id);
        results.push({id: basic.id, textSha256: sha(norm(copy.textContent))});
    }
    return results;
}
async function captureHttp(target, baseline) {
    const response = await fetch(BASE + target.path, {redirect: 'manual', headers: {Accept: 'text/html', Connection: 'close'}, signal: AbortSignal.timeout(60000)});
    const row = {path: target.path, at: new Date().toISOString(), status: response.status,
        contentType: response.headers.get('content-type'), robotsHeader: response.headers.get('x-robots-tag')};
    assert.equal(response.status, 200, 'Actual local guest GET returns 200');
    const dom = new JSDOM(await response.text());
    try {
        const document = dom.window.document;
        row.metadata = publicMetadata(document);
        row.basics = assertBasics(document, baseline);
        const points = [...document.querySelectorAll(POINTS)];
        assert.equal(points.length, target.points.length, 'All per-point controls server-rendered');
        assert.equal(document.querySelectorAll('[data-theory-native-extension]').length, points.length, 'No legacy whole-block disclosure remains');
        row.points = points.map((node, index) => {
            const expected = target.points[index];
            assert.equal(node.dataset.theorySection, expected.sectionKey);
            assert.equal(Number(node.dataset.theoryPointIndex), expected.index);
            const detail = node.querySelector(':scope > details');
            assert.ok(detail && !detail.open, 'Server details closed');
            assert.ok(node.closest('.theory-section-card'), 'Native point is inside its content card');
            const body = detail.querySelector('.theory-section-detail-body');
            assert.ok(body, 'Detail body exists in server HTML');
            unitFidelity([...body.querySelectorAll(':scope > section')].map(section => section.textContent), expected, expected.sectionKey);
            return {key: expected.sectionKey, index: expected.index, expectedSha256: expected.expectedSha256,
                textSha256: fidelity(body.textContent, expected, expected.sectionKey), summaryId: detail.querySelector(':scope > summary').id};
        });
        assert.equal(new Set(row.points.map(point => point.summaryId)).size, row.points.length, 'Unique stable point summary IDs');
        return row;
    } finally { dom.window.close(); }
}

async function guard(page, report, row) {
    const requests = [];
    page.on('request', request => requests.push({method: request.method(), url: publicUrl(request.url())}));
    page.on('pageerror', error => row.pageErrors.push(errorMessage(error)));
    page.on('requestfailed', request => {
        const item = {url: publicUrl(request.url()), reason: request.failure()?.errorText || 'unknown', resourceType: request.resourceType()};
        if (row.javaScriptEnabled === false && item.resourceType === 'script' && item.reason === 'csp') {
            row.expectedDisabledScripts.push(item);
            return;
        }
        if (/^https:\/\/fonts\.(googleapis|gstatic)\.com\//u.test(item.url)) row.fontFailures.push(item);
        else row.failedRequests.push(item);
    });
    page.on('response', response => {
        if (response.status() >= 400 && new URL(response.url()).origin === BASE)
            row.httpErrors.push({url: publicUrl(response.url()), status: response.status()});
    });
    await page.route('**/*', route => {
        const request = route.request();
        const url = new URL(request.url());
        if (request.method() !== 'GET' || (url.origin !== BASE && !['fonts.googleapis.com', 'fonts.gstatic.com'].includes(url.hostname))) {
            report.policyViolations.push({method: request.method(), url: publicUrl(url.href)});
            return route.abort('blockedbyclient');
        }
        return route.continue();
    });
    return requests;
}
async function theme(page, wanted, viewport) {
    if (await page.locator('html').evaluate(node => node.classList.contains('dark')) === (wanted === 'dark')) return;
    const name = wanted === 'dark' ? 'Увімкнути темну тему' : 'Увімкнути світлу тему';
    const button = page.getByRole('button', {name, exact: true});
    const menu = page.getByRole('button', {name: 'Меню', exact: true});
    const openMenu = !await button.isVisible();
    if (openMenu) await menu.click();
    await button.click();
    await page.waitForFunction(dark => document.documentElement.classList.contains('dark') === dark, wanted === 'dark');
    if (openMenu && viewport.width < 1280) await menu.click();
}
async function browserBasics(page, baseline) {
    for (const basic of baseline.basics) {
        const node = page.locator('#block-' + basic.id);
        assert.equal(await node.count(), 1);
        assert.equal(await node.evaluate(node => Boolean(node.closest('details'))), false);
        const text = await node.evaluate(node => {
            const copy = node.cloneNode(true);
            copy.querySelectorAll('[data-theory-native-extension]').forEach(extension => extension.remove());
            return copy.textContent;
        });
        assert.equal(norm(text), basic.text, 'Browser retains original full basic: ' + basic.id);
    }
}
async function testPoints(page, target, requests) {
    const wrappers = page.locator(POINTS);
    const details = page.locator(DETAILS);
    assert.equal(await wrappers.count(), target.points.length);
    assert.equal(await details.count(), target.points.length);
    assert.equal(await details.evaluateAll(nodes => nodes.every(node => !node.open)), true);
    const results = [];
    for (const [index, expected] of target.points.entries()) {
        const wrapper = wrappers.nth(index), item = details.nth(index), summary = item.locator(':scope > summary');
        assert.equal(await wrapper.getAttribute('data-theory-section'), expected.sectionKey);
        assert.equal(Number(await wrapper.getAttribute('data-theory-point-index')), expected.index);
        const placement = await wrapper.evaluate(node => {
            const card = node.closest('.theory-section-card');
            const point = node.closest('[data-theory-basic-point],article.theory-item,.theory-item,tr,[data-theory-summary-point],div.group');
            const summary = node.querySelector('summary');
            const s = summary.getBoundingClientRect(), c = card?.getBoundingClientRect();
            return {insideCard: Boolean(card && card.contains(node)), pointTag: point?.tagName || null,
                pointIndex: point?.getAttribute('data-theory-basic-point') || null,
                insidePoint: Boolean(point && point !== card && point.contains(node)),
                summaryWithinCard: Boolean(c && s.left >= c.left - 1 && s.right <= c.right + 1)};
        });
        assert.ok(placement.insideCard && placement.insidePoint, 'Each control belongs to its own native basic point: ' + expected.sectionKey);
        // A comparison cell may be wider than the viewport; its scroll-container owns that horizontal movement.
        if (placement.pointTag !== 'TR') assert.ok(placement.summaryWithinCard, 'Point summary remains inside native card bounds');
        const start = requests.length;
        await summary.click();
        assert.equal(await item.evaluate(node => node.open), true, 'Mouse opens only this point');
        assert.equal(await details.evaluateAll(nodes => nodes.filter(node => node.open).length), 1, 'No sibling disclosure auto-opens');
        const body = await item.locator('.theory-section-detail-body').textContent();
        unitFidelity(await item.locator('.theory-section-detail-body > section').allTextContents(), expected, expected.sectionKey);
        const textSha256 = fidelity(body, expected, expected.sectionKey);
        await summary.click();
        assert.equal(await item.evaluate(node => node.open), false, 'Mouse closes');
        await summary.focus();
        await page.keyboard.press('Enter');
        assert.equal(await item.evaluate(node => node.open), true, 'Enter opens');
        assert.equal(await summary.evaluate(node => node.matches(':focus-visible') && getComputedStyle(node).outlineStyle !== 'none'), true);
        await page.keyboard.press('Space');
        assert.equal(await item.evaluate(node => node.open), false, 'Space closes');
        assert.equal(requests.length, start, 'Point disclosure never fetches extra content');
        results.push({key: expected.sectionKey, textSha256, placement, mouse: true, keyboard: true, independent: true, noFetch: true});
    }
    const first = details.nth(0), second = details.nth(1);
    await first.locator('summary').click();
    await second.locator('summary').click();
    assert.equal(await details.evaluateAll(nodes => nodes.filter(node => node.open).length), 2, 'Two point disclosures can remain independently open');
    await first.locator('summary').click();
    assert.equal(await second.evaluate(node => node.open), true, 'Closing one never closes another');
    await second.locator('summary').click();
    return results;
}
async function overflow(page) {
    const documentPixels = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - innerWidth));
    const ownership = await page.locator('[data-theory-main]').evaluate(root => ({
        mainPixels: Math.max(0, root.scrollWidth - root.clientWidth),
        main: {client: root.clientWidth, scroll: root.scrollWidth},
        cards: [...root.querySelectorAll('.theory-section-card')].filter(node => !node.closest('details:not([open])')).map(node => ({
            client: node.clientWidth, scroll: node.scrollWidth, width: node.getBoundingClientRect().width,
            containerWidth: root.getBoundingClientRect().width
        }))
    }));
    // BODY overflow:hidden must never turn an overflowing main/card into a false pass.
    assert.ok(ownership.mainPixels <= 1, 'The main teaching container must own its width without BODY clipping');
    assert.ok(ownership.cards.every(card => card.width <= card.containerWidth + 1 && card.scroll <= card.client + 1),
        'Every native teaching card must own its width without ancestor clipping');
    const unclippedLearning = await page.locator('[data-theory-main]').evaluate(root => {
        const clipped = node => {
            for (let parent = node.parentElement; parent && parent !== root; parent = parent.parentElement)
                if (['auto', 'scroll', 'hidden', 'clip'].includes(getComputedStyle(parent).overflowX)) return true;
            return false;
        };
        return [root, ...root.querySelectorAll('*')].filter(node => {
            const rectangle = node.getBoundingClientRect();
            return rectangle.width && (rectangle.right > innerWidth + 1 || rectangle.left < -1) && !clipped(node);
        }).map(node => ({tag: node.tagName, id: node.id || null}));
    });
    assert.deepEqual(unclippedLearning, [], 'No unclipped teaching content overflow');
    const tables = await page.locator('.theory-table-scroll').evaluateAll(nodes => nodes.map(node => ({
        overflowX: getComputedStyle(node).overflowX, clientWidth: node.clientWidth, scrollWidth: node.scrollWidth
    })));
    assert.ok(tables.every(table => ['auto', 'scroll'].includes(table.overflowX)));
    return {documentPixels, ownership, unclippedLearning, tables};
}

async function run(dir, label, baselinePath = path.join(dir, 'after-http.json')) {
    const data = packageData();
    const baseline = JSON.parse(fs.readFileSync(baselinePath, 'utf8'));
    assert.equal(baseline.base, BASE);
    const expected = new Map(baseline.rows.map(row => [row.path, row]));
    const report = {at: new Date().toISOString(), base: BASE, sourceSha256: data.sourceSha256, masterSha256: data.masterSha256,
        baselineSha256: sha(fs.readFileSync(baselinePath)), conditions: {guest: true, getOnly: true, fixtures: false, chromiumContextsPerProcess: 4},
        http: [], states: [], noJavaScript: [], policyViolations: [], pass: false};
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    let browser = null, contextCount = 0;
    // Bound process resources across the long matrix, without retrying a failed
    // state or filtering any request. Each previous context is already closed.
    const freshContext = async options => {
        if (!browser || contextCount >= 4) {
            if (browser) { await browser.close(); browser = null; }
            browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
            contextCount = 0;
        }
        contextCount++;
        return browser.newContext(options);
    };
    try {
        for (const target of data.targets) {
            report.http.push(await captureHttp(target, expected.get(target.path)));
            console.log(JSON.stringify({phase: 'actual-get', path: target.path, points: target.points.length, pass: true}));
        }
        for (const viewport of [{width: 1440, height: 1000}, {width: 390, height: 844}]) {
            for (const wanted of ['light', 'dark']) for (const [index, target] of data.targets.entries()) {
                const context = await freshContext({viewport, colorScheme: wanted});
                const page = await context.newPage();
                const row = {path: target.path, viewport, theme: wanted, at: new Date().toISOString(), pageErrors: [], failedRequests: [], fontFailures: [], httpErrors: []};
                report.states.push(row);
                try {
                    const requests = await guard(page, report, row);
                    const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle'});
                    assert.equal(response.status(), 200);
                    row.status = response.status();
                    await page.waitForFunction(() => Boolean(window.Alpine));
                    await theme(page, wanted, viewport);
                    await browserBasics(page, expected.get(target.path));
                    row.points = await testPoints(page, target, requests);
                    row.overflow = await overflow(page);
                    await page.evaluate(() => window.scrollTo(0, 0));
                    await page.waitForFunction(() => window.scrollY === 0);
                    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
                    const filename = label + '-' + index + '-' + viewport.width + '-' + wanted + '.png';
                    // Optional bounded capture for memory-constrained local acceptance;
                    // all point/DOM/keyboard/print assertions remain identical.
                    await page.screenshot({path: path.join(dir, filename), fullPage: process.env.GRAMLYZE_M26_VIEWPORT_SCREENSHOTS !== '1', animations: 'disabled'});
                    row.screenshot = filename;
                    await page.reload({waitUntil: 'networkidle'});
                    const details = page.locator(DETAILS);
                    assert.equal(await details.evaluateAll(nodes => nodes.every(node => !node.open)), true, 'Reload restores clean closed disclosures');
                    const summaryId = await details.first().locator('summary').getAttribute('id');
                    await page.goto(BASE + target.path + '#' + summaryId, {waitUntil: 'networkidle'});
                    await page.waitForFunction(id => document.getElementById(id)?.closest('details')?.open, summaryId);
                    row.deepFragment = true;
                    const states = await page.locator('[data-theory-details]').evaluateAll(nodes => nodes.map(node => node.open));
                    await page.emulateMedia({media: 'print'});
                    await page.waitForFunction(() => [...document.querySelectorAll('[data-theory-details]')].every(node => node.open));
                    await page.emulateMedia({media: 'screen'});
                    await page.waitForFunction(states => JSON.stringify([...document.querySelectorAll('[data-theory-details]')].map(node => node.open)) === JSON.stringify(states), states);
                    row.printRestores = true;
                    assert.deepEqual(row.pageErrors, []);
                    assert.deepEqual(row.failedRequests, []);
                    assert.deepEqual(row.httpErrors, []);
                    row.pass = true;
                    console.log(JSON.stringify({phase: 'browser', path: target.path, viewport: viewport.width, theme: wanted,
                        points: row.points.length, pass: true, documentOverflow: row.overflow.documentPixels}));
                } catch (error) { row.failure = errorMessage(error); throw error; } finally { await context.close(); }
            }
        }
        for (const target of data.targets) {
            const context = await freshContext({javaScriptEnabled: false, viewport: {width: 1440, height: 1000}});
            const page = await context.newPage();
            const row = {path: target.path, javaScriptEnabled: false, expectedDisabledScripts: [],
                pageErrors: [], failedRequests: [], fontFailures: [], httpErrors: []};
            report.noJavaScript.push(row);
            try {
                const requests = await guard(page, report, row);
                const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle'});
                assert.equal(response.status(), 200);
                await browserBasics(page, expected.get(target.path));
                const details = page.locator(DETAILS);
                assert.equal(await details.count(), target.points.length);
                const before = requests.length;
                for (const [index, point] of target.points.entries()) {
                    const item = details.nth(index);
                    await item.locator('summary').click();
                    assert.equal(await item.evaluate(node => node.open), true);
                    fidelity(await item.locator('.theory-section-detail-body').textContent(), point, point.sectionKey);
                    unitFidelity(await item.locator('.theory-section-detail-body > section').allTextContents(), point, point.sectionKey);
                    await item.locator('summary').click();
                    assert.equal(await item.evaluate(node => node.open), false);
                }
                assert.equal(requests.length, before, 'Native no-JS disclosure performs no fetch');
                row.points = target.points.length;
                assert.deepEqual(row.failedRequests, []);
                assert.deepEqual(row.httpErrors, []);
                row.pass = true;
            } finally { await context.close(); }
        }
        assert.deepEqual(report.policyViolations, []);
        report.pass = true;
        report.strictDocumentOverflowPass = report.states.every(row => row.overflow.documentPixels === 0);
        report.learningOverflowPass = report.states.every(row => row.overflow.unclippedLearning.length === 0);
        return report;
    } catch (error) { report.failure = errorMessage(error); throw error; } finally {
        if (browser) await browser.close();
        fs.writeFileSync(path.join(dir, label + '-browser.json'), JSON.stringify(report, null, 2), {flag: 'wx'});
        console.log(JSON.stringify({pass: report.pass, actualGetPages: report.http.length,
            states: report.states.filter(row => row.pass).length, noJavaScript: report.noJavaScript.filter(row => row.pass).length,
            uniquePoints: report.http.reduce((total, row) => total + row.points.length, 0),
            learningOverflowPass: report.learningOverflowPass, strictDocumentOverflowPass: report.strictDocumentOverflowPass}));
    }
}

/** Fresh guest Forms usage card: all four controls closed, then only the first open. */
async function captureUsage(dir, label, baselinePath = path.join(dir, 'after-http.json')) {
    const baseline = JSON.parse(fs.readFileSync(baselinePath, 'utf8'));
    const pathname = '/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms';
    const basic = baseline.rows.find(row => row.path === pathname).basics
        .find(block => block.text.includes('Коли вживаємо Past Perfect Continuous'));
    assert.ok(basic, 'Previously accepted Forms usage card baseline');
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
    const report = {at: new Date().toISOString(), base: BASE, policyViolations: [], screenshots: [], pass: false};
    try {
        for (const viewport of [{width: 1440, height: 1000}, {width: 390, height: 844}]) {
            const context = await browser.newContext({viewport, colorScheme: 'light'});
            const page = await context.newPage();
            const row = {path: pathname, viewport, pageErrors: [], failedRequests: [], fontFailures: [], httpErrors: []};
            try {
                await guard(page, report, row);
                const response = await page.goto(BASE + pathname, {waitUntil: 'networkidle'});
                assert.equal(response.status(), 200);
                await page.waitForFunction(() => Boolean(window.Alpine));
                await theme(page, 'light', viewport);
                const card = page.locator('#block-' + basic.id + ' > .theory-section-card');
                const controls = card.locator(DETAILS);
                assert.equal(await controls.count(), 4);
                assert.equal(await controls.evaluateAll(nodes => nodes.every(node => !node.open)), true);
                for (const state of ['closed', 'first-open']) {
                    if (state === 'first-open') {
                        await controls.first().locator('summary').click();
                        assert.equal(await controls.evaluateAll(nodes => nodes.filter(node => node.open).length), 1);
                        assert.equal(await controls.first().evaluate(node => node.open), true);
                    }
                    await page.evaluate(() => window.scrollTo(0, 0));
                    await page.waitForFunction(() => window.scrollY === 0);
                    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
                    const box = await card.boundingBox();
                    assert.ok(box && box.width > 0 && box.height > 0);
                    const filename = label + '-forms-usage-' + state + '-' + viewport.width + '-light.png';
                    await page.screenshot({path: path.join(dir, filename), fullPage: true, clip: box, animations: 'disabled'});
                    report.screenshots.push({path: pathname, viewport, state, filename, clip: box, source: 'real-guest-full-page-clip-scroll-top', mask: false});
                }
                assert.deepEqual(row.pageErrors, []);
                assert.deepEqual(row.failedRequests, []);
                assert.deepEqual(row.httpErrors, []);
            } finally { await context.close(); }
        }
        assert.deepEqual(report.policyViolations, []);
        report.pass = true;
        return report;
    } catch (error) { report.failure = errorMessage(error); throw error; } finally {
        await browser.close();
        fs.writeFileSync(path.join(dir, label + '-usage-elements.json'), JSON.stringify(report, null, 2), {flag: 'wx'});
        console.log(JSON.stringify({pass: report.pass, screenshots: report.screenshots.map(item => item.filename)}));
    }
}

/** Read-only geometry trace. Clipped descendants and closed details are explicitly recorded, not hidden. */
async function traceOverflow(dir, label) {
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
    const report = {at: new Date().toISOString(), base: BASE, policyViolations: [], rows: [], pass: false};
    const paths = ['/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms',
        '/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives',
        '/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions',
        '/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions'];
    const measure = () => {
        const geometry = node => {
            const rectangle = node.getBoundingClientRect(), css = getComputedStyle(node);
            return {tag: node.tagName, id: node.id || null, classes: String(node.className || '').slice(0, 260),
                left: rectangle.left, right: rectangle.right, width: rectangle.width, height: rectangle.height,
                client: node.clientWidth, scroll: node.scrollWidth, computedWidth: css.width, minWidth: css.minWidth,
                maxWidth: css.maxWidth, overflowX: css.overflowX, display: css.display, position: css.position,
                closedDetailsAncestor: Boolean(node.closest('details:not([open])'))};
        };
        const main = document.querySelector('[data-theory-main]');
        const overflowing = [main, ...main.querySelectorAll('*')].filter(node => {
            const rectangle = node.getBoundingClientRect();
            return rectangle.width && (rectangle.right > innerWidth + 1 || rectangle.left < -1);
        }).sort((a, b) => b.getBoundingClientRect().right - a.getBoundingClientRect().right);
        const clippedBeforeMain = node => {
            for (let parent = node.parentElement; parent && parent !== main; parent = parent.parentElement) {
                if (['auto', 'scroll', 'hidden', 'clip'].includes(getComputedStyle(parent).overflowX)) return true;
            }
            return false;
        };
        const nodes = overflowing.slice(0, 35);
        const producers = overflowing.filter(node => !clippedBeforeMain(node)).slice(0, 35);
        return {viewport: innerWidth, root: geometry(document.documentElement), body: geometry(document.body),
            main: geometry(main), cards: [...main.querySelectorAll('.theory-section-card')].map(geometry),
            tables: [...main.querySelectorAll('.theory-table-scroll')].map(geometry),
            directMainChildren: [...main.children].map(geometry),
            unclippedBeforeMain: producers.map(node => ({node: geometry(node), ancestors: (() => {
                const chain = [];
                for (let parent = node.parentElement; parent; parent = parent.parentElement) chain.push(geometry(parent));
                return chain;
            })()})),
            nodes: nodes.map(node => {
                const ancestors = [];
                for (let parent = node.parentElement; parent; parent = parent.parentElement) ancestors.push(geometry(parent));
                return {node: geometry(node), ancestors};
            })};
    };
    try {
        for (const pathname of paths) {
            const context = await browser.newContext({viewport: {width: 390, height: 844}});
            const page = await context.newPage();
            const row = {path: pathname, pageErrors: [], failedRequests: [], fontFailures: [], httpErrors: []};
            report.rows.push(row);
            try {
                await guard(page, report, row);
                const response = await page.goto(BASE + pathname, {waitUntil: 'networkidle'});
                assert.equal(response.status(), 200);
                row.freshClosed = await page.evaluate(measure);
                const detail = page.locator('.theory-table-scroll').first().locator(DETAILS).first();
                assert.equal(await detail.count(), 1);
                await detail.locator('summary').click();
                row.tablePointOpen = await page.evaluate(measure);
                await detail.locator('summary').click();
                await page.evaluate(() => window.scrollTo(0, 0));
                await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
                row.tablePointClosed = await page.evaluate(measure);
            } finally { await context.close(); }
        }
        report.pass = report.policyViolations.length === 0;
        return report;
    } finally {
        await browser.close();
        fs.writeFileSync(path.join(dir, label + '-overflow-trace.json'), JSON.stringify(report, null, 2), {flag: 'wx'});
        console.log(JSON.stringify(report.rows.map(row => ({path: row.path,
            freshOverflow: row.freshClosed.root.scroll - row.freshClosed.viewport,
            openOverflow: row.tablePointOpen.root.scroll - row.tablePointOpen.viewport,
            closedOverflow: row.tablePointClosed.root.scroll - row.tablePointClosed.viewport,
            top: row.freshClosed.nodes.slice(0, 2).map(item => item.node)}))));
    }
}

/** Temporary browser-only candidate probe; never used as acceptance of the deployed local code. */
async function probeContextPosition(dir, label) {
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
    const report = {at: new Date().toISOString(), base: BASE, diagnosticOnly: true, browserOnlyOverride: 'summary position:relative',
        policyViolations: [], rows: []};
    const paths = packageData().targets.map(target => target.path).slice(1);
    const measure = () => ({documentPixels: document.documentElement.scrollWidth - innerWidth,
        mainPixels: document.querySelector('[data-theory-main]').scrollWidth - document.querySelector('[data-theory-main]').clientWidth,
        contexts: [...document.querySelectorAll('[data-theory-point-index] summary > .sr-only')].map(node => {
            const box = node.getBoundingClientRect();
            let ancestor = node.parentElement;
            while (ancestor && getComputedStyle(ancestor).position === 'static') ancestor = ancestor.parentElement;
            const a = ancestor?.getBoundingClientRect();
            return {summaryId: node.parentElement.id, left: box.left, right: box.right, width: box.width,
                position: getComputedStyle(node).position, nearestPositioned: ancestor ? {tag: ancestor.tagName, id: ancestor.id || null,
                    classes: String(ancestor.className || '').slice(0, 180), left: a.left, right: a.right} : null};
        })});
    try {
        for (const pathname of paths) {
            const context = await browser.newContext({viewport: {width: 390, height: 844}});
            const page = await context.newPage();
            const row = {path: pathname, pageErrors: [], failedRequests: [], fontFailures: [], httpErrors: []};
            report.rows.push(row);
            try {
                await guard(page, report, row);
                const response = await page.goto(BASE + pathname, {waitUntil: 'networkidle'});
                assert.equal(response.status(), 200);
                row.before = await page.evaluate(measure);
                await page.addStyleTag({content: '[data-theory-point-index] .theory-section-toggle {position:relative;}'});
                await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
                row.candidate = await page.evaluate(measure);
                console.log(JSON.stringify({path: pathname, before: row.before.documentPixels, candidate: row.candidate.documentPixels,
                    mainBefore: row.before.mainPixels, mainCandidate: row.candidate.mainPixels}));
            } finally { await context.close(); }
        }
        assert.deepEqual(report.policyViolations, []);
    } finally {
        await browser.close();
        fs.writeFileSync(path.join(dir, label + '-context-position-probe.json'), JSON.stringify(report, null, 2), {flag: 'wx'});
    }
    return report;
}

/** Optional actual-page visual evidence for summary points and local mobile table scrolling. */
async function captureSummaryAndTable(dir, label, baselinePath = path.join(dir, 'after-http.json')) {
    const baseline = JSON.parse(fs.readFileSync(baselinePath, 'utf8'));
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
    const report = {at: new Date().toISOString(), base: BASE, policyViolations: [], screenshots: [], pass: false};
    const cases = [
        {path: '/theory/past-perfect-continuous', key: 'm26-ppc-overview-3-point-1', kind: 'overview-summary', viewport: {width: 1440, height: 1000}},
        {path: '/theory/past-perfect-continuous', key: 'm26-ppc-overview-3-point-1', kind: 'overview-summary', viewport: {width: 390, height: 844}},
        {path: '/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms', key: 'm26-ppc-forms-3-point-1', kind: 'forms-table-notes', viewport: {width: 390, height: 844}}
    ];
    try {
        for (const item of cases) {
            const context = await browser.newContext({viewport: item.viewport, colorScheme: 'light'});
            const page = await context.newPage();
            const row = {path: item.path, pageErrors: [], failedRequests: [], fontFailures: [], httpErrors: []};
            try {
                await guard(page, report, row);
                const response = await page.goto(BASE + item.path, {waitUntil: 'networkidle'});
                assert.equal(response.status(), 200);
                await page.waitForFunction(() => Boolean(window.Alpine));
                await theme(page, 'light', item.viewport);
                await browserBasics(page, baseline.rows.find(entry => entry.path === item.path));
                const wrapper = page.locator('[data-theory-section="' + item.key + '"]');
                const cardId = await wrapper.evaluate(node => node.closest('.theory-section-card').closest('section[id]').id);
                const card = page.locator('#' + cardId + ' > .theory-section-card');
                assert.equal(await card.locator(DETAILS).count(), 4);
                if (item.kind === 'forms-table-notes') {
                    await card.locator(':scope > .theory-section-body > .theory-table-scroll').evaluate(node => { node.scrollLeft = node.scrollWidth - node.clientWidth; });
                }
                await page.evaluate(() => window.scrollTo(0, 0));
                await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
                const clip = await card.boundingBox();
                const filename = label + '-' + item.kind + '-' + item.viewport.width + '-light.png';
                await page.screenshot({path: path.join(dir, filename), fullPage: true, clip, animations: 'disabled'});
                const localScroll = item.kind === 'forms-table-notes'
                    ? await card.locator(':scope > .theory-section-body > .theory-table-scroll').evaluate(node => ({left: node.scrollLeft, client: node.clientWidth, scroll: node.scrollWidth})) : null;
                report.screenshots.push({path: item.path, viewport: item.viewport, kind: item.kind, filename, clip, localScroll, mask: false});
                assert.deepEqual(row.pageErrors, []);
                assert.deepEqual(row.failedRequests, []);
                assert.deepEqual(row.httpErrors, []);
            } finally { await context.close(); }
        }
        assert.deepEqual(report.policyViolations, []);
        report.pass = true;
        return report;
    } finally {
        await browser.close();
        fs.writeFileSync(path.join(dir, label + '-other-elements.json'), JSON.stringify(report, null, 2), {flag: 'wx'});
        console.log(JSON.stringify({pass: report.pass, screenshots: report.screenshots.map(item => item.filename)}));
    }
}

if (require.main === module) {
    const [rawDir, label, rawBaseline] = process.argv.slice(2);
    assert.ok(rawDir && label, 'Usage: node seo-m26-point-details-local.cjs PRIVATE_OUTPUT_DIR LABEL [BASELINE_HTTP_JSON]');
    assert.match(label, /^[a-z0-9-]+$/u);
    const dir = path.resolve(rawDir);
    assert.equal(path.basename(dir), 'seo-m26-local');
    assert.equal(path.basename(path.dirname(dir)), 'app');
    assert.equal(path.basename(path.dirname(path.dirname(dir))), 'storage');
    assert.ok(fs.existsSync(dir));
    assert.equal(fs.existsSync(path.join(dir, label + '-browser.json')), false, 'Prior evidence must not be overwritten');
    run(dir, label, rawBaseline ? path.resolve(rawBaseline) : undefined).catch(error => {
        console.error(errorMessage(error)); process.exitCode = 1;
    });
}
module.exports = {packageData, run, captureUsage, traceOverflow, probeContextPosition, captureSummaryAndTable};
