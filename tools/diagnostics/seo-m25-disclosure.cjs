/* Isolated SSR fixture acceptance; no real route, DB, session or progress writes. */
const fs = require('node:fs');
const path = require('node:path');
const { pathToFileURL } = require('node:url');
const assert = require('node:assert/strict');

const repo = path.resolve(__dirname, '../..');
const fixturePath = process.env.M25_SECTION_FIXTURE;
const output = process.env.M25_DISCLOSURE_OUTPUT;
const documentRoot = process.env.GRAMLYZE_DOCUMENT_ROOT || 'D:/DEV/htdocs/gramlyze.loc';
if (!fixturePath || !output) throw new Error('Set M25_SECTION_FIXTURE and private M25_DISCLOSURE_OUTPUT.');
const safeOutputParent = path.resolve(repo, 'storage/app');
const resolvedOutput = path.resolve(output);
if (!resolvedOutput.startsWith(safeOutputParent + path.sep)) throw new Error('Fixture output must remain private inside worktree storage/app.');
fs.mkdirSync(resolvedOutput, { recursive: true });

const fragment = fs.readFileSync(fixturePath, 'utf8');
const manifest = JSON.parse(fs.readFileSync(path.join(documentRoot, 'public/build/manifest.json'), 'utf8'));
const publicCss = manifest['resources/css/catalog-public.css'];
const publicJs = manifest['resources/js/catalog-public.js'];
const cssFiles = [...new Set([publicCss?.file, ...(publicCss?.css || []), ...(publicJs?.css || [])].filter(Boolean))];
if (!cssFiles.length) throw new Error('No protected public CSS build in document root.');
const css = cssFiles.map((file) => fs.readFileSync(path.join(documentRoot, 'public/build', file), 'utf8')).join('\n');
const moduleSource = fs.readFileSync(path.join(repo, 'resources/js/theory-sections.js'), 'utf8');
const fixtureUrl = 'http://gramlyze.loc/__m25-section-fixture';
const html = '<!doctype html><html lang="uk"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>M25 technical fixture — not an authored short lesson</title><style>'+css+'</style></head><body><div class="theory-design" data-theory-design>'+fragment+'</div><script>'+moduleSource.replace('export function initTheorySections', 'function initTheorySections')+';initTheorySections(document,window);</script></body></html>';
fs.writeFileSync(path.join(resolvedOutput, 'fixture-response.html'), html);

const playwright = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const browserOptions = { headless: true };
if (process.env.PLAYWRIGHT_EXECUTABLE_PATH) browserOptions.executablePath = process.env.PLAYWRIGHT_EXECUTABLE_PATH;
const report = { fixtureOnly: true, routeFulfilled: fixtureUrl, sourceFragment: pathToFileURL(fixturePath).href, cssFiles, scenarios: [], errors: [] };

(async () => {
    const browser = await playwright.chromium.launch(browserOptions);
    try {
        for (const viewport of [{ width: 1280, height: 900 }, { width: 390, height: 844 }]) {
            for (const javaScriptEnabled of [true, false]) {
                const context = await browser.newContext({ viewport, javaScriptEnabled, reducedMotion: 'reduce' });
                const page = await context.newPage();
                page.on('console', (message) => { if (message.type() === 'error') report.errors.push(message.text()); });
                page.on('pageerror', (error) => report.errors.push(error.message));
                const requests = [];
                await page.route('**/*', async (route) => {
                    requests.push({ url: route.request().url(), method: route.request().method() });
                    if (route.request().url().split('#')[0] === fixtureUrl && route.request().method() === 'GET') {
                        return route.fulfill({ status: 200, contentType: 'text/html; charset=utf-8', body: html });
                    }
                    return route.abort('blockedbyclient');
                });
                const response = await page.goto(fixtureUrl, { waitUntil: 'load' });
                assert.equal(response.status(), 200);
                assert.equal((await response.text()).includes('fixture-source'), true);
                const details = page.locator('[data-theory-details]');
                assert.equal(await details.count(), 2);
                assert.deepEqual(await details.evaluateAll((nodes) => nodes.map((node) => node.open)), [false, false]);
                assert.equal(await page.locator('.theory-section-main').first().isVisible(), true);
                assert.equal(await page.locator('#fixture-source').isVisible(), false);
                assert.equal(await page.locator('.theory-section-toggle-closed').first().isVisible(), true);
                assert.equal(await page.locator('.theory-section-toggle-open').first().isVisible(), false);
                const first = page.locator('.theory-section-toggle').first();
                const second = page.locator('.theory-section-toggle').nth(1);
                await first.focus();
                await page.keyboard.press('Tab');
                const tabClosed = await page.evaluate(() => document.activeElement.id);
                assert.notEqual(tabClosed, 'fixture-answer');
                await first.focus();
                await page.keyboard.press('Enter');
                await page.waitForTimeout(80);
                assert.equal(await details.first().getAttribute('open') !== null, true);
                assert.equal(await page.locator('#fixture-source').isVisible(), true);
                assert.equal(await page.locator('.theory-section-toggle-open').first().isVisible(), true);
                await second.focus();
                await page.keyboard.press('Space');
                await page.waitForTimeout(80);
                assert.deepEqual(await details.evaluateAll((nodes) => nodes.map((node) => node.open)), [true, true]);
                const key = page.locator('#fixture-answer > summary');
                await key.focus();
                await page.keyboard.press('Enter');
                assert.equal(await page.locator('#fixture-answer').getAttribute('open') !== null, true);
                assert.deepEqual(await details.evaluateAll((nodes) => nodes.map((node) => node.open)), [true, true]);
                const ids = await page.locator('[id]').evaluateAll((nodes) => nodes.map((node) => node.id));
                assert.equal(new Set(ids).size, ids.length);
                const horizontalOverflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
                assert.equal(horizontalOverflow <= 1, true);
                await page.screenshot({ path: path.join(resolvedOutput, `fixture-${viewport.width}-${javaScriptEnabled ? 'js' : 'nojs'}.png`), fullPage: true });

                // Fresh response closes sections again, then a direct deep link reveals its ancestors.
                await page.goto(fixtureUrl+'#fixture-deep', { waitUntil: 'load' });
                await page.waitForTimeout(100);
                const directFragmentOpen = await details.first().evaluate((node) => node.open);
                const noJsAlternative = javaScriptEnabled ? null : await page.locator('noscript .theory-section-nojs a').first().isVisible();
                if (javaScriptEnabled) assert.equal(directFragmentOpen, true);
                else assert.equal(directFragmentOpen || noJsAlternative, true);
                if (javaScriptEnabled) {
                    await page.locator('#outside-after').click();
                    assert.equal(new URL(page.url()).hash, '#second-source');
                    await page.goBack();
                    assert.equal(new URL(page.url()).hash, '#fixture-deep');
                    await page.goForward();
                    assert.equal(new URL(page.url()).hash, '#second-source');
                }
                await page.goto(fixtureUrl, { waitUntil: 'load' });
                await page.emulateMedia({ media: 'print' });
                await page.waitForTimeout(100);
                const printVisible = await page.locator('#fixture-source').isVisible();
                assert.equal(printVisible, true);
                await page.screenshot({ path: path.join(resolvedOutput, `fixture-print-${viewport.width}-${javaScriptEnabled ? 'js' : 'nojs'}.png`), fullPage: true });
                await page.emulateMedia({ media: 'screen' });
                await page.waitForTimeout(100);
                if (javaScriptEnabled) assert.deepEqual(await details.evaluateAll((nodes) => nodes.map((node) => node.open)), [false, false]);
                const reducedMotion = await page.locator('.theory-section-toggle').first().evaluate((node) => ({ transition: getComputedStyle(node).transitionDuration, animation: getComputedStyle(node).animationDuration }));
                assert.equal(reducedMotion.transition.split(',').every((duration) => parseFloat(duration) <= 0.001), true);
                report.scenarios.push({ viewport, javaScriptEnabled, keyboard: 'Enter/Space/Tab passed', independentSections: true, nestedAnswers: true, idsUnique: true, horizontalOverflow, directFragmentOpen, noJsAlternative, printVisible, reducedMotion, requests });
                await context.close();
            }
        }
        assert.deepEqual(report.errors, []);
        report.passed = true;
    } catch (error) {
        report.passed = false;
        report.failure = error.stack;
        process.exitCode = 1;
    } finally {
        await browser.close();
        fs.writeFileSync(path.join(resolvedOutput, 'report.json'), JSON.stringify(report, null, 2));
        console.log(JSON.stringify({ passed: report.passed, scenarios: report.scenarios.length, errors: report.errors, failure: report.failure, output: resolvedOutput }));
    }
})();
