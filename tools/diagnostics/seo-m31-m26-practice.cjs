'use strict';
// M26 practice plus M27–M29 no-JS diagnostics: actual guest GETs to .loc, never production or stateful requests.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const {execFileSync} = require('node:child_process');
const {JSDOM} = require('jsdom');
const {guard, BASE, practice, htmlText} = require('./seo-m28-local.cjs');
const {tokenAndManualProof, allTargets} = require('./seo-m31-local.cjs');
const ROOT = path.resolve(__dirname, '../..');
const SOURCE = 'database/content-patches/m26-ppc-interactive-practice.v1.json';
const SOURCE_SHA = '8b105df87c0f81172c3315927a9b5d6a35e2f9dc1475d611d8e5ee4623055466';
const MASTER = 'docs/content/m26-past-perfect-continuous-detail-master.v1.json';
const ACCEPTED_SHA = 'ceb2162c862978ca461607be3913a9f717f380b2';
const VIEW = 'resources/views/engram/theory/blocks-v3/practice-set.blade.php';
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const read = file => JSON.parse(fs.readFileSync(path.join(ROOT, file), 'utf8'));
const publicUrl = value => {try {const u = new URL(value); return u.origin + u.pathname;} catch {return '(no URL)';}};
const errorMessage = error => String(error.message || error).replace(/https?:\/\/\S+/g, publicUrl).slice(0, 1800);
const save = (dir, name, value) => fs.writeFileSync(path.join(dir, name), JSON.stringify(value, null, 2), {flag: 'wx'});

function packageData() {
    const bytes = fs.readFileSync(path.join(ROOT, SOURCE));
    assert.equal(sha(bytes), SOURCE_SHA, 'Only the accepted immutable M26 practice package');
    const source = JSON.parse(bytes);
    const master = read(MASTER);
    assert.equal(sha(fs.readFileSync(path.join(ROOT, MASTER))), source.source_master_sha256);
    assert.equal(source.locale, 'uk'); assert.equal(source.package, 'm26-ppc-interactive-practice-v1');
    assert.deepEqual(source.targets.map(target => target.key), ['forms', 'negatives', 'questions', 'time-expressions']);
    const targets = source.targets.map(target => {
        const author = master.targets.find(item => item.identity === target.identity);
        assert.ok(author && author.practice_insert, 'Exact accepted lesson owner');
        assert.equal(author.slug, target.slug); assert.equal(author.definition_path, target.definition_path);
        assert.equal(author.practice_insert.uuid_key, target.practice.uuid_key);
        const route = author.expected_theory_path;
        assert.equal(new URL(BASE + route).origin, BASE);
        assert.ok(route.startsWith('/theory/tenses/past-perfect-continuous/'));
        const data = target.practice.body_data;
        for (const group of ['selects', 'choices', 'inputs']) {
            assert.equal(data[group].length, 2); assert.equal(target.authored_case_mapping[group].length, 2);
            assert.ok(data[group].every(item => typeof item.answer === 'string' && item.answer.length > 0));
        }
        assert.ok(data.inputs.every(item => item.before.includes('/')));
        assert.equal(data.linked_practice.source, 'theory_links');
        assert.deepEqual(data.linked_practice.question_types, ['4']);
        assert.equal(data.linked_practice.seeder_classes.length, 1);
        return {...target, path: route, after: {page: {blocks: [{type: 'practice-set', body: JSON.stringify(data)}]}}};
    });
    return {targets, sourceSha256: SOURCE_SHA, masterSha256: source.source_master_sha256};
}

function noJsRegressions() {
    const packages = {M27: 'm27-m11-linking-words.v2.json', M28: 'm28-m12-emphasis-inversion.v2.json', M29: 'm29-m13-sentence-structure.v2.json'};
    const sourceHashes = {};
    for (const [group, file] of Object.entries(packages)) {
        const relative = 'database/content-patches/' + file;
        const accepted = execFileSync('git', ['show', ACCEPTED_SHA + ':' + relative], {cwd: ROOT, maxBuffer: 1024 * 1024});
        const current = fs.readFileSync(path.join(ROOT, relative));
        assert.equal(sha(current), sha(accepted), 'Only accepted ' + group + ' source in no-JS regression scope');
        sourceHashes[group] = sha(current);
    }
    const targets = allTargets.filter(target => Object.hasOwn(packages, target.group)).map(target => {
        assert.equal(new URL(BASE + target.path).origin, BASE);
        const blocks = target.after.page.blocks.filter(block => block.type === 'practice-set');
        assert.equal(blocks.length, 1); const data = JSON.parse(blocks[0].body);
        assert.equal(data.author_self_check, undefined, 'This diagnostic is only the accepted pre-M30 practice contract');
        for (const name of ['selects', 'choices', 'inputs']) assert.equal(data[name].length, 2);
        return {...target, practice: {body_data: data}};
    });
    assert.equal(targets.length, 9);
    for (const group of Object.keys(packages)) assert.equal(targets.filter(target => target.group === group).length, 3);
    return {targets, sourceHashes};
}

function unchangedAcceptedTokenBranch() {
    const accepted = execFileSync('git', ['show', ACCEPTED_SHA + ':' + VIEW], {cwd: ROOT, encoding: 'utf8', maxBuffer: 1024 * 1024});
    const current = fs.readFileSync(path.join(ROOT, VIEW), 'utf8');
    const fragments = source => {
        const before = source.match(/@unless\(\$hasInputTokenBank\)\s*<span>\{!! \$item\['before'\][\s\S]*?@endunless/);
        const tokens = source.match(/<template x-for="token in inputTokenBank\([\s\S]*?<\/template>/);
        assert.ok(before && tokens, 'Accepted token-only presentation branches identifiable');
        return [before[0], tokens[0]].map(value => value.replace(/\r\n/g, '\n'));
    };
    assert.deepEqual(fragments(current), fragments(accepted), 'M31 did not change the accepted M26 input/token markup');
    return {acceptedSha: ACCEPTED_SHA, branchFragmentSha256: sha(JSON.stringify(fragments(current))), identical: true};
}

function serverPractice(doc, target, requireIntros = true) {
    // The bundled JSDOM selector parser mishandles '(' in an attribute prefix.
    // Match the identical prefix explicitly; the one-block assertion is unchanged.
    const roots = [...doc.querySelectorAll('[x-data]')].filter(node => node.getAttribute('x-data').startsWith('theoryPracticeSet('));
    assert.equal(roots.length, 1, 'One accepted interactive practice block in initial HTML');
    const groups = [...roots[0].querySelectorAll('.theory-exercise')];
    assert.equal(groups.length, 3, 'All three exercise groups remain server rendered');
    const data = target.practice.body_data;
    for (const [index, group] of ['selects', 'choices'].entries()) {
        const labels = [...groups[index].querySelectorAll('label')].map(node => htmlText(node.innerHTML));
        assert.deepEqual(labels, data[group].map(item => htmlText(item.label)), 'Full accepted labels including context');
    }
    assert.equal(groups[2].querySelectorAll('input').length, 2);
    for (const name of ['select', 'choice', 'input']) {
        assert.ok(norm(roots[0].textContent).includes(htmlText(data[name + '_title'])));
        if (requireIntros || data[name + '_intro']) assert.ok(norm(roots[0].textContent).includes(htmlText(data[name + '_intro'])));
    }
    return {groups: 3, tasks: 6, initialLabelsExact: true, introsExact: true};
}

function tokenVisibility(text, item, index) {
    const tokens = item.before.split('/').map(norm).filter(Boolean);
    const missing = tokens.filter(token => !norm(text).includes(htmlText(token)));
    return {index, tokens: tokens.length, missingTokenGroups: missing, completeContextVisible: missing.length === 0};
}

async function noJsContext(page, target) {
    const root = page.locator('[x-data^="theoryPracticeSet("]');
    assert.equal(await root.count(), 1); assert.ok(await root.isVisible());
    const groups = root.locator('.theory-exercise'); assert.equal(await groups.count(), 3);
    const data = target.practice.body_data;
    for (const [index, name] of ['selects', 'choices'].entries()) {
        assert.ok(await groups.nth(index).isVisible());
        for (const [i, item] of data[name].entries()) {
            const label = groups.nth(index).locator('label').nth(i);
            assert.ok(await label.isVisible()); assert.equal(norm(await label.textContent()), htmlText(item.label));
        }
    }
    const inputs = groups.nth(2).locator('input'); assert.equal(await inputs.count(), 2);
    const results = [];
    for (const [index, item] of data.inputs.entries()) {
        assert.ok(await inputs.nth(index).isVisible());
        const task = inputs.nth(index).locator('xpath=../..');
        const text = norm(await task.innerText());
        results.push(tokenVisibility(text, item, index));
    }
    try {
        assert.ok(results.every(item => item.completeContextVisible), 'No-JS manual/token exercises must expose every accepted token group, not just an empty input');
    } catch (error) {
        error.code = 'M26_NO_JS_TOKEN_CONTEXT'; error.contextProof = {groups: 3, tasks: 6, fullContextsVisible: false, inputs: results}; throw error;
    }
    return {groups: 3, tasks: 6, fullContextsVisible: true, inputs: results};
}

/** Two explicit accepted alternatives; a missing token bank is not automatically an empty task. */
function contextClassification(target, index, observed) {
    const item = target.practice.body_data.inputs[index];
    if (!item.before.includes('/')) return {completeManualContextVisible: observed.beforeVisible, source: 'plain before'};
    if (observed.tokens.completeContextVisible) return {completeManualContextVisible: true, source: 'visible token groups'};
    if (target.group === 'M27' && target.slug === 'linking-words-reason-result-contrast' && index === 0
        && observed.choiceLabels.some(label => norm(label).includes(htmlText(item.answer)))) {
        return {completeManualContextVisible: true, source: 'accepted full sentence already visible in first choice task', tokenBankStillMissing: true};
    }
    if (target.group === 'M29' && target.slug === 'ellipsis-substitution-and-reference' && index === 1
        && observed.afterVisible && item.after === 'Olena told Marta that her notes were missing. Загублено нотатки Олени. Усунь неоднозначність.') {
        return {completeManualContextVisible: true, source: 'accepted full rewrite prompt in visible after', tokenBankStillMissing: true};
    }
    return {completeManualContextVisible: false, source: 'grouped before is absent; other hints do not expose the full target task'};
}

async function noJsRegressionContext(page, target) {
    const root = page.locator('[x-data^="theoryPracticeSet("]'); assert.equal(await root.count(), 1); assert.ok(await root.isVisible());
    const groups = root.locator('.theory-exercise'); assert.equal(await groups.count(), 3);
    const data = target.practice.body_data, labels = {}, after = [], results = [];
    for (const [index, name] of ['selects', 'choices'].entries()) {
        const group = groups.nth(index); assert.ok(await group.isVisible()); labels[name] = [];
        for (const [i, item] of data[name].entries()) {
            const label = group.locator('label').nth(i); assert.ok(await label.isVisible());
            const text = htmlText(await label.innerHTML()); assert.equal(text, htmlText(item.label));
            const taskText = norm(await label.locator('..').innerText());
            if (item.prompt) assert.ok(taskText.includes(htmlText(item.prompt)), 'Accepted extra choice instructions remain visible');
            labels[name].push(text);
        }
    }
    const inputs = groups.nth(2).locator('input'); assert.equal(await inputs.count(), 2);
    for (const [index, item] of data.inputs.entries()) {
        assert.ok(await inputs.nth(index).isVisible()); const task = inputs.nth(index).locator('xpath=../..'); const text = norm(await task.innerText());
        const afterVisible = Boolean(item.after) && text.includes(htmlText(item.after));
        if (item.after) assert.ok(afterVisible, 'Complete accepted after/context instruction remains visible');
        const contextVisible = Boolean(item.context) && text.includes(htmlText(item.context));
        if (item.context) assert.ok(contextVisible, 'Complete accepted context remains visible');
        const beforeVisible = text.includes(htmlText(item.before)), tokens = tokenVisibility(text, item, index), grouped = item.before.includes('/');
        if (!grouped) assert.ok(beforeVisible, 'Plain before is fully visible without JavaScript');
        const observed = {beforeVisible, afterVisible, contextVisible, tokens, choiceLabels: labels.choices};
        after.push({index, text: item.after ? htmlText(item.after) : null, visible: afterVisible});
        results.push({...tokens, groupedTokenTask: grouped, beforeVisible, afterVisible, contextVisible,
            tokenButtonsRendered: await task.locator('span[x-text="token.value"]').count(), ...contextClassification(target, index, observed)});
    }
    return {groups: 3, tasks: 6, labelsExact: true, labels, after, inputs: results,
        fullTokenContextsVisible: results.every(item => item.completeContextVisible),
        completeManualContextsVisible: results.every(item => item.completeManualContextVisible),
        missingTokenBanks: results.filter(item => item.groupedTokenTask && !item.completeContextVisible).length};
}

async function observe(page, row, violations) {
    const requests = await guard(page, row, violations);
    row.consoleErrors = []; row.expectedFontConsoleErrors = [];
    page.on('console', message => {
        if (message.type() !== 'error') return;
        const item = {source: publicUrl(message.location().url), messageSha256: sha(message.text())};
        if (/^https:\/\/fonts\.(googleapis|gstatic)\.com(?:\/|$)/.test(message.location().url)) row.expectedFontConsoleErrors.push(item);
        else row.consoleErrors.push(item);
    });
    return requests;
}

function clean(row) {
    row.errors = row.errors.map(message => ({messageSha256: sha(message)}));
    assert.deepEqual(row.errors, [], 'No JavaScript page errors');
    assert.deepEqual(row.httpErrors, [], 'No failed HTTP responses');
    assert.deepEqual(row.failed, [], 'No failed local requests');
    assert.deepEqual(row.consoleErrors, [], 'No unclassified console errors');
    assert.ok(row.fontFailures.every(item => /ERR_NETWORK_ACCESS_DENIED|ERR_FAILED|ERR_NAME_NOT_RESOLVED|ERR_CONNECTION|csp/i.test(item.reason || '')), 'Font failures classified separately');
}

async function run(dir, label) {
    assert.equal(path.basename(dir), 'seo-m31-local'); assert.match(label, /^[a-z0-9-]+$/);
    fs.mkdirSync(dir, {recursive: true});
    const source = packageData(), extra = noJsRegressions();
    const report = {at: new Date().toISOString(), base: BASE, sourceSha256: source.sourceSha256,
        conditions: {guest: true, getOnly: true, chromiumContextsPerProcess: 4, viewport: {width: 1440, height: 1000}, theme: 'light'},
        acceptedTokenBranch: unchangedAcceptedTokenBranch(), regressionSourceHashes: extra.sourceHashes,
        http: [], states: [], noJS: [], noJSRegressions: [], violations: [],
        jsPass: false, fullNoJSPass: false, regressionNoJSFullPass: false, pass: false};
    let browser = null, contextCount = 0;
    try {
        for (const target of source.targets) {
            const response = await fetch(BASE + target.path, {redirect: 'manual', headers: {Accept: 'text/html', Connection: 'close'}, signal: AbortSignal.timeout(30000)});
            assert.equal(response.status, 200, 'Guest GET is accessible without previous navigation');
            const dom = new JSDOM(await response.text());
            try {report.http.push({path: target.path, at: new Date().toISOString(), status: response.status,
                contentType: response.headers.get('content-type'), practice: serverPractice(dom.window.document, target)});} finally {dom.window.close();}
        }
        const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
        const freshContext = async options => {
            if (!browser || contextCount === 4) {
                if (browser) await browser.close();
                browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE}); contextCount = 0;
            }
            contextCount++; return browser.newContext(options);
        };
        for (const target of source.targets) {
            const context = await freshContext({viewport: report.conditions.viewport, colorScheme: 'light'});
            const page = await context.newPage(), row = {path: target.path, errors: [], httpErrors: [], failed: [], pass: false}; report.states.push(row);
            try {
                await observe(page, row, report.violations);
                const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle'}); assert.equal(response.status(), 200);
                await page.waitForFunction(() => Boolean(window.Alpine));
                row.practice = await practice(page, target); row.tokenAndManual = await tokenAndManualProof(page, target);
                await page.reload({waitUntil: 'networkidle'});
                const inputState = await page.locator('[x-data^="theoryPracticeSet("]').evaluate(node => {
                    const state = window.Alpine.$data(node); return {selects: state.selectAnswers, choices: state.choiceAnswers, inputs: state.inputAnswers, checked: state.checkedGroups};
                });
                assert.deepEqual(inputState, {selects: {}, choices: {}, inputs: {}, checked: {}}, 'Reloaded practice is clean'); row.reloadClean = true;
                const root = page.locator('[x-data^="theoryPracticeSet("]'); await root.scrollIntoViewIfNeeded();
                row.screenshot = label + '-' + target.key + '-practice.png'; await page.screenshot({path: path.join(dir, row.screenshot), fullPage: false, animations: 'disabled'});
                clean(row); row.pass = true; console.log(JSON.stringify({path: target.path, jsPractice: true, pass: true}));
            } catch (error) {row.failure = errorMessage(error); throw error;} finally {await context.close();}
        }
        report.jsPass = report.states.length === 4 && report.states.every(row => row.pass);
        for (const target of source.targets) {
            const context = await freshContext({viewport: report.conditions.viewport, javaScriptEnabled: false});
            const page = await context.newPage(), row = {path: target.path, javaScriptEnabled: false, expectedDisabledScripts: [], errors: [], httpErrors: [], failed: [], pass: false}; report.noJS.push(row);
            try {
                await observe(page, row, report.violations);
                const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle'}); assert.equal(response.status(), 200);
                const dom = new JSDOM(await page.content());
                try {row.initialPractice = serverPractice(dom.window.document, target);} finally {dom.window.close();}
                try {row.practice = await noJsContext(page, target); row.fullContextsVisible = true;}
                catch (error) {
                    if (error.code !== 'M26_NO_JS_TOKEN_CONTEXT') throw error;
                    row.contextFailure = errorMessage(error); row.practice = error.contextProof; row.fullContextsVisible = false;
                    row.classification = 'Pre-existing accepted M26 token-only no-JS context limitation, not a changed M31 branch';
                }
                clean(row); row.networkClean = true; row.pass = row.fullContextsVisible === true;
                console.log(JSON.stringify({path: target.path, noJS: true, fullContextsVisible: row.fullContextsVisible, pass: row.pass}));
            } catch (error) {row.failure = errorMessage(error);} finally {await context.close();}
        }
        for (const target of extra.targets) {
            const context = await freshContext({viewport: report.conditions.viewport, javaScriptEnabled: false});
            const page = await context.newPage(), row = {path: target.path, group: target.group, javaScriptEnabled: false,
                expectedDisabledScripts: [], errors: [], httpErrors: [], failed: [], pass: false}; report.noJSRegressions.push(row);
            try {
                await observe(page, row, report.violations);
                const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle'}); assert.equal(response.status(), 200);
                const dom = new JSDOM(await page.content());
                try {row.initialPractice = serverPractice(dom.window.document, target, false);} finally {dom.window.close();}
                row.practice = await noJsRegressionContext(page, target);
                row.classification = row.practice.missingTokenBanks ? 'Pre-existing accepted grouped-token no-JS bank limitation; visible alternative context is reported per input'
                    : 'Accepted plain-text manual prompts remain fully visible';
                clean(row); row.networkClean = true; row.pass = row.practice.fullTokenContextsVisible && row.practice.completeManualContextsVisible;
                console.log(JSON.stringify({path: target.path, noJSRegression: target.group, missingTokenBanks: row.practice.missingTokenBanks,
                    completeManualContextsVisible: row.practice.completeManualContextsVisible, pass: row.pass}));
            } catch (error) {row.failure = errorMessage(error);} finally {await context.close();}
        }
        assert.deepEqual(report.violations, [], 'No forbidden origin or non-GET request attempted');
        report.fullNoJSPass = report.noJS.length === 4 && report.noJS.every(row => row.pass);
        report.regressionNoJSFullPass = report.noJSRegressions.length === 9 && report.noJSRegressions.every(row => row.pass);
        report.pass = report.jsPass && report.fullNoJSPass && report.regressionNoJSFullPass;
    } finally {if (browser) await browser.close(); save(dir, label + '-browser.json', report);}
    console.log(JSON.stringify({pass: report.pass, jsPass: report.jsPass, states: report.states.length,
        noJS: report.noJS.length, fullNoJSPass: report.fullNoJSPass, noJSRegressions: report.noJSRegressions.length,
        regressionNoJSFullPass: report.regressionNoJSFullPass, evidence: label + '-browser.json'}));
    return report;
}

if (require.main === module) {
    const [dir, label] = process.argv.slice(2);
    run(dir, label).then(report => {if (!report.pass) process.exitCode = 1;}).catch(error => {console.error(errorMessage(error)); process.exitCode = 1;});
}
module.exports = {run, packageData, noJsRegressions, unchangedAcceptedTokenBranch, serverPractice, noJsContext, tokenVisibility, contextClassification};
