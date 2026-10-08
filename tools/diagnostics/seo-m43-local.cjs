'use strict';
// Real, GET-only gramlyze.loc acceptance. Expectations come from the frozen
// author master, not generated AFTER definitions. Importing runs no HTTP/DB.
const fs = require('node:fs'), path = require('node:path'), assert = require('node:assert/strict'), crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const ROOT = path.resolve(__dirname, '../..'), SERVED = 'D:/DEV/htdocs/gramlyze.loc';
const BASE = 'http://gramlyze.loc', CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const MASTER_PATH = 'docs/content/m43-authored-tense-usage.v1.0.0.json';
const MASTER_SHA = 'd9afc130ec223202ea83781147f9966855227d3194ccd7707c9b3c037b62e2f5';
const PRACTICE_COMPONENT_SELECTOR = '[data-m43-practice-ui] [x-data^="m43PracticeUi"]';
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value ?? '').replace(/\s+/gu, ' ').trim();
const masterBytes = fs.readFileSync(path.join(ROOT, MASTER_PATH));
assert.equal(sha(masterBytes), MASTER_SHA, 'Frozen independently reviewed M43 master');
const master = JSON.parse(masterBytes);
const targets = master.lessons.map(lesson => ({key: lesson.key, path: lesson.theory_path, title: lesson.title, lesson}));
const referencePaths = [
    '/theory/tenses/past-simple-vs-past-continuous', '/theory/tenses/present-simple-vs-present-continuous',
    '/theory/tenses/present-perfect-vs-past-simple', '/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms',
];
function uiCases(lesson) {
    return lesson.practice.map(task => ({id: task.id, source_index: task.source_index, scoring: task.scoring,
        interaction: task.controls.length > 1 ? 'compound' : task.controls[0].kind,
        controls: task.controls.map(control => ({id: control.id, kind: control.kind, label: control.label_uk,
            required: control.required, ...(control.stimulus_en ? {stimulus_en: control.stimulus_en} : {}),
            ...(control.kind === 'manual' ? {options: [], answer: control.canonical_answer, accepted: control.accepted_answers, tokens: control.tokens}
                : {answer: control.correct_value, options: control.options})}))}));
}
function allowedRequest(value, method) { try { const u = new URL(value); return method === 'GET' && u.origin === BASE && !u.username && !u.password; } catch { return false; } }
function safeUrl(value) { try { const u = new URL(value); return u.origin + u.pathname; } catch { return '(unavailable URL)'; } }
function failureInfo(error) { return {name:error.name,message:error.message.split('\n')[0],
    frames:String(error.stack || '').split('\n').filter(line=>/^\s+at /u.test(line)).slice(0,5),
    ...(error.actual == null || ['string','number','boolean'].includes(typeof error.actual) ? {actual:error.actual} : {}),
    ...(error.expected == null || ['string','number','boolean'].includes(typeof error.expected) ? {expected:error.expected} : {})}; }
function text(node) { return norm(node?.textContent); }
function only(root, selector, label = selector) { const nodes = root.querySelectorAll(selector); assert.equal(nodes.length, 1, 'Exactly one ' + label); return nodes[0]; }
function stripped(node, selectors = 'details,script,style,noscript,[data-theory-ui]') { const copy = node.cloneNode(true); copy.querySelectorAll(selectors).forEach(n => n.remove()); return copy; }
function onceOrdered(value, expected, label) {
    let offset = 0;
    for (const fragment of expected.filter(Boolean).map(norm)) {
        const first = value.indexOf(fragment, offset);
        assert.ok(first >= offset, 'Exact ordered text: ' + label + ' / ' + fragment.slice(0, 65));
        offset = first + fragment.length;
    }
}
function examplePairs(root, expected, label) {
    const boxes = [...root.querySelectorAll('.theory-example')].filter(n => !n.parentElement.closest('.theory-example'));
    assert.equal(boxes.length, expected.length, 'Exact non-duplicated examples: ' + label);
    boxes.forEach((box, i) => {
        const item = expected[i];
        assert.equal(text(only(box, '[lang="en"]')), norm(item.en), 'English preserved: ' + label);
        const uk = [...box.querySelectorAll('[lang="uk"]')].map(text);
        assert.deepEqual(uk, [item.uk, item.note_uk].filter(Boolean).map(norm), 'Full translation then separate note: ' + label);
    });
}
function pointFidelity(node, point) {
    const basic = stripped(node);
    assert.ok(text(basic).includes(norm(point.title)), 'Point heading preserved: ' + point.id);
    if (point.formula) assert.ok(text(basic).includes(norm(point.formula)), 'Formula preserved');
    const paragraphs = [...basic.querySelectorAll('p[lang="uk"]')].filter(n => !n.closest('.theory-example')).map(text);
    assert.deepEqual(paragraphs, point.paragraphs_uk.map(norm), 'Exact once-only ordered basic paragraphs: ' + point.id);
    let examples = point.examples;
    if (point.wrong_en) {
        const wrong = only(basic, '.theory-example--wrong'), right = only(basic, '.theory-example--right');
        assert.equal(text(only(wrong, '[lang="en"]')), point.wrong_en);
        assert.deepEqual([...wrong.querySelectorAll('[lang="uk"]')].map(text), point.wrong_uk ? [point.wrong_uk] : []);
        assert.equal(text(only(right, '[lang="en"]')), point.right_en);
        assert.equal(text(only(right, '[lang="uk"]')), point.right_uk);
        wrong.remove(); right.remove();
    } else assert.equal(basic.querySelectorAll('.theory-example--wrong').length, 0, 'Valid meaning comparisons are not grammar errors');
    examplePairs(basic, examples, point.id);
    onceOrdered(text(basic), [...point.paragraphs_uk, ...examples.flatMap(e => [e.en, e.uk, e.note_uk])], point.id);
    const detail = node.querySelectorAll('[data-theory-native-extension] > details');
    assert.equal(detail.length, point.detail ? 1 : 0, 'Finite detail ownership: ' + point.id);
    if (point.detail) {
        const fragment = only(detail[0], '.theory-point-fragment');
        assert.equal(fragment.id, 'block-' + point.detail.id, 'Stable author detail anchor');
        assert.ok(text(fragment).includes(norm(point.detail.title)));
        assert.deepEqual([...fragment.querySelectorAll('p[lang="uk"]')].filter(n => !n.closest('.theory-example')).map(text), point.detail.paragraphs_uk.map(norm));
        examplePairs(fragment, point.detail.examples, point.detail.id);
        onceOrdered(text(fragment), [point.detail.title, ...point.detail.paragraphs_uk, ...point.detail.examples.flatMap(e => [e.en, e.uk, e.note_uk])], point.detail.id);
    }
}
function cellText(cell) { return typeof cell === 'string' ? norm(cell) : norm(cell.text_uk ?? [cell.en, cell.uk, cell.note_uk].filter(Boolean).join(' ')); }
function feedbackFidelity(node, task) {
    assert.deepEqual([...node.querySelectorAll('p')].filter(n => !n.closest('.theory-example')).map(text), task.feedback.paragraphs_uk.map(norm), 'Exact once-only feedback paragraphs');
    examplePairs(node, task.feedback.answer_examples, task.id + ' answer examples');
}
function authorFidelity(document, target) {
    const lesson = target.lesson, main = only(document, '[data-theory-main]');
    assert.deepEqual([...document.querySelectorAll('h1')].map(text), [lesson.title]);
    assert.ok(text(main).includes(norm(lesson.subtitle)), 'Exact authored subtitle');
    for (const hero of lesson.hero) for (const field of ['label', 'text', 'formula']) assert.ok(text(main).includes(norm(hero[field])), 'Exact hero ' + field);
    assert.deepEqual([...main.querySelectorAll('[data-m43-author-section]')].map(n => n.dataset.m43AuthorSection), lesson.sections.map(s => s.id));
    let points = 0, details = 0;
    for (const section of lesson.sections) {
        const owner = only(main, '[data-m43-author-section="' + section.id + '"]');
        assert.equal(text(stripped(only(owner, '.theory-section-header h2'))), section.title);
        assert.equal(owner.getAttribute('data-m43-native-layout'), section.native_kind);
        assert.deepEqual([...owner.querySelectorAll('[data-m43-basic-point]')].map(n => n.id), section.points.map(p => p.id));
        for (const point of section.points) { pointFidelity(only(owner, '[data-m43-basic-point="' + point.id + '"]'), point); points++; if (point.detail) details++; }
        if (section.intro_uk) assert.ok(text(owner).includes(section.intro_uk));
        for (const note of section.notes_uk || []) assert.ok(text(owner).includes(note));
        if (section.table) {
            const table = only(owner, '[data-m43-native-table]');
            assert.deepEqual([...table.querySelectorAll('thead th')].map(text), section.table.columns.map(norm));
            const rows = [...table.querySelectorAll('tbody tr')];
            assert.equal(rows.length, section.table.rows.length);
            rows.forEach((row, r) => { assert.equal(row.children.length, section.table.columns.length);
                [...row.children].forEach((cell, c) => { const expected = section.table.rows[r][c];
                    const actual = typeof expected === 'object' && expected.en ? [...cell.querySelectorAll('p[lang]')].map(text).join(' ') : text(stripped(cell));
                    assert.equal(actual, cellText(expected), 'All table columns/notes retained');
                    if (typeof expected === 'object' && expected.en) examplePairs(cell, [expected], section.id + '/' + r + '/' + c);
                }); });
        }
        const outsidePoints = stripped(owner, '[data-m43-basic-point],table,script,style,noscript,[data-theory-ui]');
        examplePairs(outsidePoints, section.note_examples || [], section.id + ' note examples');
    }
    assert.equal(main.querySelectorAll('[data-theory-native-extension] > details').length, details);
    const ids = [...main.querySelectorAll('[id]')].map(n => n.id); assert.equal(ids.length, new Set(ids).size, 'No hidden duplicate anchors');
    return {sections: lesson.sections.length, points, details, masterSha256: MASTER_SHA};
}
function sourceHashes(root = ROOT) {
    const files = [MASTER_PATH, 'public/js/authored-practice-ui.js', 'public/js/m43-practice-ui.js',
        'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php',
        'resources/views/engram/theory/blocks-v3/m43-native-section.blade.php',
        'resources/views/engram/theory/blocks-v3/m43-native-table.blade.php',
        'resources/views/engram/theory/blocks-v3/m43-native-mistake.blade.php',
        'resources/views/theory/partials/point-detail-fragment.blade.php', ...master.lessons.map(l => l.definition_path)];
    return Object.fromEntries(files.map(file => [file, sha(fs.readFileSync(path.join(root, file)))]));
}
function privateDirectory(dir, label) {
    assert.match(label, /^[a-z0-9-]+$/u);
    const root = path.resolve(SERVED, 'storage/app/seo-m43-local'), absolute = path.resolve(dir);
    assert.ok(absolute === root || absolute.startsWith(root + path.sep), 'Only private M43 local evidence');
    fs.mkdirSync(absolute, {recursive: true}); return absolute;
}
function save(dir, filename, value) { fs.writeFileSync(path.join(dir, filename), JSON.stringify(value, null, 2) + '\n', {flag: 'wx'}); }
async function shot(locator, dir, file, fullPage = false) {
    assert.equal(fs.existsSync(path.join(dir, file)), false, 'Exclusive screenshot');
    await locator.screenshot({path: path.join(dir, file), animations: 'disabled', timeout: 30000, ...(fullPage ? {fullPage: true} : {})});
    return {file, sha256: sha(fs.readFileSync(path.join(dir, file)))};
}
async function observe(context, page, row) {
    Object.assign(row, {blockedExternal: [], blockedNonGET: [], localFailures: [], expectedDisabledScripts: [], localHttpErrors: [], pageErrors: [], consoleErrors: []});
    await context.route('**/*', route => {
        const request = route.request(); if (allowedRequest(request.url(), request.method())) return route.continue();
        (request.method() !== 'GET' ? row.blockedNonGET : row.blockedExternal).push({method: request.method(), url: safeUrl(request.url())});
        return route.abort('blockedbyclient');
    });
    page.on('pageerror', error => row.pageErrors.push({name: error.name, messageSha256: sha(error.message)}));
    page.on('console', message => { if (message.type() === 'error') row.consoleErrors.push({source: safeUrl(message.location().url), messageSha256: sha(message.text())}); });
    page.on('requestfailed', request => { if (new URL(request.url()).origin === BASE) {
        const record={url:safeUrl(request.url()),reason:request.failure()?.errorText,resourceType:request.resourceType()};
        const disabled=row.javaScriptEnabled===false&&record.reason==='csp'&&record.resourceType==='script'&&new URL(request.url()).pathname.endsWith('.js');
        (disabled?row.expectedDisabledScripts:row.localFailures).push(record);
    } });
    page.on('response', response => { if (new URL(response.url()).origin === BASE && response.status() >= 400) row.localHttpErrors.push({url: safeUrl(response.url()), status: response.status()}); });
}
function clean(row) {
    row.consoleErrors = row.consoleErrors.map(e => ({...e, expectedExternalBlock: row.blockedExternal.some(b => b.url === e.source)}));
    for (const key of ['pageErrors', 'localFailures', 'localHttpErrors', 'blockedNonGET']) assert.deepEqual(row[key], [], key);
    assert.equal(row.consoleErrors.filter(e => !e.expectedExternalBlock).length, 0, 'Unexpected console errors');
}
async function ready(page, target, row, theme = 'light', javaScript = true) {
    const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle', timeout: 60000});
    row.http = {status: response.status(), finalUrl: safeUrl(page.url()), contentType: response.headers()['content-type'], xRobotsTag: response.headers()['x-robots-tag'] || null};
    assert.equal(response.status(), 200); assert.equal(new URL(page.url()).origin, BASE);
    await page.locator('[data-theory-main]').waitFor({state: 'visible'});
    if (javaScript) {
        assert.equal(await page.locator(PRACTICE_COMPONENT_SELECTOR).count(), 1, 'Exactly one authored M43 practice component, separate from own-bank widget');
        assert.ok((await page.locator(PRACTICE_COMPONENT_SELECTOR).getAttribute('x-data')).startsWith('m43PracticeUi('), 'Exact authored component function');
        await page.waitForFunction(selector => window.Alpine?.$data(document.querySelector(selector))?.cases?.length === 6, PRACTICE_COMPONENT_SELECTOR);
        await page.evaluate(value => { document.documentElement.classList.toggle('dark', value === 'dark'); localStorage.setItem('theme', value); }, theme);
        const publicCases = await page.locator(PRACTICE_COMPONENT_SELECTOR).evaluate(n => JSON.parse(JSON.stringify(window.Alpine.$data(n).cases)));
        assert.deepEqual(publicCases, uiCases(target.lesson), 'Live public control payload equals independent frozen master mapping');
    }
    const dom = new JSDOM(await page.content()); try { row.author = authorFidelity(dom.window.document, target); } finally { dom.window.close(); }
    row.basicVisibility = await page.locator('[data-m43-basic-point]').evaluateAll(nodes => nodes.map(node => ({id: node.id,
        visible: node.getClientRects().length > 0,
        hiddenBasicText: [...node.querySelectorAll('p[lang]')].filter(n => !n.closest('details')).filter(n => !n.getClientRects().length || getComputedStyle(n).visibility === 'hidden').length})));
    assert.ok(row.basicVisibility.every(p => p.visible && p.hiddenBasicText === 0), 'All complete basic prose/examples visible without opening details');
    row.metadata = await page.evaluate(() => {
        const meta = selector => document.querySelector(selector)?.content || null;
        return {title: document.title, h1: [...document.querySelectorAll('h1')].map(n => n.textContent.trim()), canonical: document.querySelector('link[rel="canonical"]')?.href || null,
            robots: meta('meta[name="robots"]'), description: meta('meta[name="description"]'), ogTitle: meta('meta[property="og:title"]'), ogDescription: meta('meta[property="og:description"]'),
            twitterTitle: meta('meta[name="twitter:title"]'), twitterDescription: meta('meta[name="twitter:description"]')};
    });
    const before = JSON.parse(fs.readFileSync(path.join(SERVED, 'storage/app/seo-m43-local/browser-before-v1/manifest.json'), 'utf8')).rows.find(r => r.path === target.path);
    assert.ok(before, 'Independent BEFORE exists');
    for (const key of ['title','h1','canonical','robots','ogTitle','twitterTitle']) assert.deepEqual(row.metadata[key], before.dom.metadata[key], 'Preserved metadata identity ' + key);
    assert.equal(row.http.xRobotsTag, before.http.xRobotsTag, 'Local noindex preserved');
    row.expectedSubtitleDerivedMetadataChanges = ['description','ogDescription','twitterDescription'].filter(key => row.metadata[key] !== before.dom.metadata[key]);
}
async function bankQA(page, target) {
    const inventory = JSON.parse(fs.readFileSync(path.join(SERVED, 'storage/app/seo-m43-local/m43-before-v1.json'), 'utf8'));
    const ownClasses = ['PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder','PolyglotStativeVerbsAllLevelsLessonSeeder','PolyglotUsedToWouldAllLevelsLessonSeeder'];
    const owner = inventory.targets.find(t => t.identity === target.lesson.identity);
    assert.ok(owner, 'Exact actual BEFORE bank owner');
    const klass = 'Database\\Seeders\\V3\\Polyglot\\' + ownClasses[targets.indexOf(target)];
    const bank = owner.banks.find(b => b.seeder_class === klass); assert.ok(bank?.linked_ids?.length, 'Actual own-linked bank inventory');
    const widget = page.locator('[data-m43-practice-ui] [data-sentence-builder]'); assert.equal(await widget.count(), 1);
    await page.waitForFunction(n => window.Alpine.$data(n)?.questions?.length > 0, await widget.elementHandle());
    const pool = await widget.evaluate(n => {const q=window.Alpine.$data(n).questions;return {ids:q.map(x=>String(x.id)),types:[...new Set(q.map(x=>String(x.type)))]};});
    const allowed = new Set(bank.linked_ids.map(String));
    assert.equal(pool.ids.length, new Set(pool.ids).size); assert.ok(pool.ids.every(id=>allowed.has(id)), 'No global AllLevels pool leakage');
    assert.deepEqual(pool.types, ['4']);
    return {class:klass,actualLinkedCount:allowed.size,sampledCount:pool.ids.length,sampleIdsSha256:sha(JSON.stringify(pool.ids)),onlyOwnLinkedType4:true};
}
const caseNode = (page, task) => page.locator('[data-m43-ui-case="' + task.source_index + '"]');
const field = (card, control) => card.locator('[data-m43-control="' + control.id + '"]');
const option = (area, control, value) => area.getByRole('radio', {name: control.options.find(o => o.value === value).label, exact: true});
async function fillControl(card, control, value, keyboard = false) {
    const area = field(card, control);
    if (control.kind === 'manual') await area.locator('[data-m43-answer-input]').fill(value);
    else { const button = option(area, control, value); if (keyboard) { await button.focus(); await button.press('Space'); } else await button.click(); }
    await card.locator('[data-m43-case-feedback]').waitFor({state:'hidden',timeout:3000});
}
async function checkCase(card, correct) {
    await card.locator('[data-m43-check]').click();
    const feedback = card.locator('[data-m43-case-feedback]');
    await feedback.waitFor({state: 'visible'});
    assert.equal(norm(await feedback.textContent()), correct ? 'Правильно' : 'Не всі частини відповіді правильні');
    assert.equal(await card.locator('[data-m43-ui-explanation]').evaluate(n => n.open), true);
}
async function resetCase(card) {
    const button = card.locator('[data-m43-reset]'); await button.focus(); await button.press('Enter');
    await card.locator('[data-m43-case-feedback]').waitFor({state:'hidden',timeout:3000});
    assert.equal(await card.locator('[data-m43-case-feedback]').isVisible(), false);
    assert.equal(await card.locator('[data-m43-ui-explanation]').evaluate(n => n.open), false);
    for (const input of await card.locator('[data-m43-answer-input]').all()) assert.equal(await input.inputValue(), '');
    assert.equal(await card.locator('[data-m43-answer][aria-checked="true"]').count(), 0);
    assert.equal(await card.locator('[data-m43-token]:disabled').count(), 0, 'Reset returns every token');
}
function answer(control) { return control.kind === 'manual' ? control.canonical_answer : control.correct_value; }
async function practiceQA(page, target, dir, stem, capture = true) {
    const rows = [];
    assert.deepEqual(await page.locator('[data-m43-ui-case]').evaluateAll(ns => ns.map(n => Number(n.dataset.m43UiCase))), [1,2,3,4,5,6]);
    for (const task of target.lesson.practice) {
        const card = caseNode(page, task), result = {task: task.id, controls: [], screenshots: [], states: []}; rows.push(result);
        const prompt = norm(await card.locator('[data-m43-author-prompt]').textContent());
        onceOrdered(prompt, [task.title, task.prompt_uk, task.context_uk], task.id + ' prompt');
        const key = card.locator('[data-m43-self-check-answer]');
        const feedbackDOM = new JSDOM(await key.innerHTML()); try { feedbackFidelity(feedbackDOM.window.document, task); } finally { feedbackDOM.window.close(); }
        assert.equal(await key.isVisible(), false, 'No visible answer before check');
        assert.equal(await card.locator('[data-m43-ui-explanation] > summary').isVisible(), false);
        for (const control of task.controls) {
            const area = field(card, control); assert.equal(await area.count(), 1);
            assert.equal(norm(await area.locator('legend').textContent()), control.label_uk);
            if (control.stimulus_en) assert.equal(norm(await area.locator(':scope > p[lang="en"]').textContent()), control.stimulus_en);
            if (control.kind === 'manual') {
                assert.equal(await area.locator('textarea').inputValue(), '');
                const tokens = area.locator('[data-m43-token]'); assert.deepEqual((await tokens.allTextContents()).map(norm).sort(), [...control.tokens].sort());
                assert.equal(await area.locator('textarea').evaluate(n => getComputedStyle(n).textTransform), 'none');
            } else {
                const buttons = area.locator('[data-m43-answer]');
                assert.deepEqual((await buttons.allTextContents()).map(norm), control.options.map(o => o.label));
                assert.deepEqual(await buttons.evaluateAll(ns => ns.map(n => n.dataset.m43Answer)), control.options.map(o => o.value));
                assert.equal(await buttons.evaluateAll(ns => ns.every(n => getComputedStyle(n).textTransform === 'none')), true);
                await buttons.first().focus(); await buttons.first().press('ArrowLeft');
                assert.equal(await buttons.last().getAttribute('aria-checked'), 'true', 'Radio reverse wrap');
                await buttons.last().press('ArrowRight'); assert.equal(await buttons.first().getAttribute('aria-checked'), 'true');
            }
        }
        await resetCase(card); result.states.push('initial');
        if (capture) result.screenshots.push(await shot(card, dir, stem + '-' + task.id + '-initial.png'));
        await checkCase(card, false); result.states.push('empty'); await resetCase(card);
        for (const control of task.controls) await fillControl(card, control, answer(control), true);
        await checkCase(card, true); result.states.push('correct');
        if (capture) result.screenshots.push(await shot(card, dir, stem + '-' + task.id + '-correct.png'));
        for (const [index, control] of task.controls.entries()) {
            // Every required subpart is independently emptied and independently wrong.
            await resetCase(card);
            for (const [otherIndex, other] of task.controls.entries()) if (otherIndex !== index) await fillControl(card, other, answer(other));
            await checkCase(card, false); result.states.push('partial:' + control.id);
            const wrong = control.kind === 'manual' ? 'not the requested answer' : control.options.find(o => o.value !== control.correct_value).value;
            await fillControl(card, control, wrong); assert.equal(await card.locator('[data-m43-case-feedback]').isVisible(), false, 'Editing hides prior feedback');
            await checkCase(card, false);
            if (capture && index === 0) result.screenshots.push(await shot(card, dir, stem + '-' + task.id + '-wrong.png'));
            const controlResult = {id: control.id, emptyAndWrongRejected: true, aliases: [], tokens: null}; result.controls.push(controlResult);
            if (control.kind === 'manual') {
                for (const alias of control.accepted_answers) for (const variant of new Set([alias, alias.replace(/'/gu, '’'), alias.replace(/[.!?]+$/u, '')])) {
                    await fillControl(card, control, variant); await checkCase(card, true); controlResult.aliases.push(variant);
                }
                const input = field(card, control).locator('textarea'); await input.fill('');
                const tokens = field(card, control).locator('[data-m43-token]');
                // Select by canonical token text, not the shuffled screen order.
                for (const token of control.tokens) { const chosen = field(card, control).locator('[data-m43-token]:not(:disabled)').filter({hasText: new RegExp('^' + token.replace(/[.*+?^${}()|[\]\\]/gu, '\\$&') + '$')}).first();
                    await chosen.focus(); await chosen.press('Enter'); }
                assert.equal(await input.inputValue(), control.canonical_answer); await input.press('Control+Enter');
                assert.equal(norm(await card.locator('[data-m43-case-feedback]').textContent()), 'Правильно');
                assert.equal(await tokens.evaluateAll(ns => ns.every(n => n.disabled)), true);
                await input.fill(''); assert.equal(await tokens.evaluateAll(ns => ns.every(n => !n.disabled)), true, 'Manual deletion returns all tokens');
                await fillControl(card, control, answer(control)); controlResult.tokens = {keyboardBuiltCanonical: true, deletionReturnsAll: true};
            }
        }
        await resetCase(card); result.states.push('wrong', 'reset');
        if (capture) result.screenshots.push(await shot(card, dir, stem + '-' + task.id + '-reset.png'));
        for (const control of task.controls) await fillControl(card, control, answer(control)); await checkCase(card, true);
        result.reanswerCorrect = true;
    }
    assert.equal(norm(await page.locator('[data-m43-ui-score]').textContent()), 'Результат: 6 / 6');
    for (const task of target.lesson.practice) await resetCase(caseNode(page, task));
    assert.equal(norm(await page.locator('[data-m43-ui-score]').textContent()), 'Результат: 0 / 6');
    return rows;
}
async function detailsQA(page, target, dir, stem) {
    const points = target.lesson.sections.flatMap(s => s.points).filter(p => p.detail), all = page.locator('[data-m43-basic-point] [data-theory-native-extension] > details'), rows = [];
    assert.equal(await all.count(), points.length); assert.equal(await all.evaluateAll(ns => ns.every(n => !n.open)), true);
    for (const point of points) {
        const own = page.locator('[data-m43-basic-point="' + point.id + '"]'), detail = own.locator('[data-theory-native-extension] > details'), summary = detail.locator(':scope > summary');
        assert.equal(await summary.evaluate(n => { const own = n.closest('[data-m43-basic-point]'); const a = n.getBoundingClientRect(), b = own.getBoundingClientRect(); return a.left >= b.left && a.right <= b.right && a.top >= b.top && a.bottom <= b.bottom; }), true);
        await summary.click(); assert.equal(await detail.evaluate(n => n.open), true); assert.equal(await all.evaluateAll(ns => ns.filter(n => n.open).length), 1);
        const screenshot = await shot(own, dir, stem + '-' + point.id + '-detail-open.png');
        await summary.focus(); await summary.press('Space'); assert.equal(await detail.evaluate(n => n.open), false);
        await summary.press('Enter'); assert.equal(await detail.evaluate(n => n.open), true); await summary.press('Space');
        rows.push({point: point.id, fragment: 'block-' + point.detail.id, independentMouseKeyboard: true, screenshot});
    }
    return rows;
}
async function layoutQA(page) {
    const tables = [];
    for (const region of await page.locator('[data-m43-author-section] .theory-table-scroll').all()) {
        const before = await region.evaluate(n => ({scrollWidth: n.scrollWidth, clientWidth: n.clientWidth, max: n.scrollWidth - n.clientWidth}));
        if (before.max > 1) { await region.focus(); for (let i = 0; i < 35; i++) await region.press('ArrowRight');
            await page.waitForFunction(n => n.scrollLeft > 0, await region.elementHandle());
            before.keyboardScroll = await region.evaluate(n => n.scrollLeft > 0); await region.evaluate(n => n.scrollLeft = 0); }
        tables.push(before);
    }
    const layout = await page.evaluate(() => {
        const main = document.querySelector('[data-theory-main]'), root = document.documentElement;
        const items = [...main.querySelectorAll('[data-m43-basic-point], [data-m43-basic-point] p[lang], [data-m43-ui-case], button, textarea')].filter(n => !n.closest('.theory-table-scroll') && n.getClientRects().length);
        return {viewport: {width: innerWidth, height: innerHeight}, dpr: devicePixelRatio, viewportScale: visualViewport.scale,
            mainOverflow: Math.max(0, main.scrollWidth - main.clientWidth), documentOverflow: Math.max(0, root.scrollWidth - innerWidth),
            contentOverflow: items.filter(n => { const r = n.getBoundingClientRect(); return r.left < -1 || r.right > innerWidth + 1 || n.scrollWidth > n.clientWidth + 2; }).map(n => ({tag: n.tagName, id: n.id || null})),
            decorativeOverflow: [...document.querySelectorAll('#shell-random-shapes span')].map(n => { const r = n.getBoundingClientRect(); return Math.max(0, -r.left, r.right - innerWidth); }).filter(v => v > 1),
            globalOverflowX: [getComputedStyle(root).overflowX, getComputedStyle(document.body).overflowX], footer: Boolean(document.querySelector('footer'))};
    });
    assert.equal(layout.mainOverflow, 0, 'Learning main must not overflow'); assert.deepEqual(layout.contentOverflow, [], 'No clipped learning controls/points outside local table regions');
    assert.equal(layout.footer, true); return {...layout, tables};
}
async function fullCapture(page, target, row, dir, stem) {
    row.componentStyles = await page.locator('[data-m43-author-section]').evaluateAll(nodes => nodes.map(node => {
        const css = item => {if(!item)return null;const c=getComputedStyle(item);return {background:c.backgroundColor,color:c.color,border:c.borderColor,borderWidth:c.borderWidth,radius:c.borderRadius,padding:c.padding,font:c.fontFamily,size:c.fontSize,transform:c.textTransform};};
        return {id:node.dataset.m43AuthorSection,layout:node.dataset.m43NativeLayout,card:css(node.querySelector('.theory-section-card')),header:css(node.querySelector('.theory-section-header')),point:css(node.querySelector('.theory-item')),english:css(node.querySelector('.theory-example [lang="en"]')),translation:css(node.querySelector('.theory-translation'))};
    }));
    for (const section of target.lesson.sections) row.screenshots.push(await shot(page.locator('[data-m43-author-section="' + section.id + '"]'), dir, stem + '-' + section.id + '.png'));
    row.screenshots.push(await shot(page.locator('[data-m43-practice-ui]'), dir, stem + '-practice.png'));
    await page.evaluate(() => scrollTo(0, 0));
    for (let step = 0; step < 220; step++) { const end = await page.evaluate(() => { scrollBy(0, innerHeight * 0.8); return scrollY + innerHeight >= document.documentElement.scrollHeight - 1; }); if (end) break; await page.waitForTimeout(35); }
    row.screenshots.push(await shot(page, dir, stem + '-footer.png'));
    await page.evaluate(() => scrollTo(0, 0)); row.screenshots.push(await shot(page, dir, stem + '-full.png', true));
}
async function tocAndReload(page, target, row) {
    row.deep = [];
    for (const point of target.lesson.sections.flatMap(s => s.points).filter(p => p.detail)) {
        const anchor = 'block-' + point.detail.id;
        await page.goto(BASE + target.path + '#' + anchor, {waitUntil: 'networkidle'});
        await page.waitForFunction(id => document.getElementById(id)?.closest('details')?.open, anchor);
        row.deep.push(anchor);
    }
    await page.goto(BASE + target.path, {waitUntil: 'networkidle'});
    await page.emulateMedia({media: 'print'});
    await page.waitForFunction(() => [...document.querySelectorAll('[data-m43-basic-point] details')].every(n => n.open));
    await page.emulateMedia({media: 'screen'});
    await page.waitForFunction(() => [...document.querySelectorAll('[data-m43-basic-point] details')].every(n => !n.open)); row.print = true;
    await page.reload({waitUntil: 'networkidle'});
    assert.equal(await page.locator('[data-m43-basic-point] details[open]').count(), 0); row.reloadClosed = true;
    const dom = new JSDOM(await page.content()); try { row.reloadAuthor = authorFidelity(dom.window.document, target); } finally { dom.window.close(); }
    row.toc = await page.evaluate(() => [...document.querySelectorAll('[data-theory-toc-links] a[href^="#"]')].map(n => ({href: n.getAttribute('href'), resolves: Boolean(document.getElementById(n.hash.slice(1)))})));
    assert.ok(row.toc.length >= target.lesson.sections.length && row.toc.every(item => item.resolves), 'All lesson TOC anchors resolve');
    const mobile = page.viewportSize().width < 1024;
    if (mobile) {
        const navigation = page.locator('button[aria-controls="theory-mobile-navigation"]'); await navigation.click();
        await page.locator('[data-theory-mobile-nav-panel]').waitFor({state:'visible'});
        await page.waitForFunction(() => document.querySelector('[data-theory-mobile-nav-panel] a[href]'));
        row.mobileMenuLinks = await page.locator('[data-theory-mobile-nav-panel] a[href]').count(); await navigation.click();
        const toc = page.locator('.theory-mobile-toc'); await toc.locator(':scope > summary').click();
        const link = toc.locator('[data-theory-toc-links] a').last(), href = await link.getAttribute('href'); await link.focus(); await link.press('Enter');
        assert.equal(new URL(page.url()).hash, href); row.keyboardToc = href;
    } else {
        const link = page.locator('[data-theory-aside] [data-theory-toc-links] a').last(); await link.focus(); const href = await link.getAttribute('href'); await link.press('Enter');
        assert.equal(new URL(page.url()).hash, href); row.keyboardToc = href;
    }
}
async function pages(dir, label) {
    dir = privateDirectory(dir, label); const file = label + '-pages.json'; assert.equal(fs.existsSync(path.join(dir, file)), false);
    const report = {at: new Date().toISOString(), base: BASE, mode: '12-live-page-states', masterSha256: MASTER_SHA, sourceBefore: sourceHashes(), servedBefore: sourceHashes(SERVED), rows: [], pass: false,
        limits: ['Google Fonts requests blocked intentionally; screenshots may use fallback fonts.', 'Viewport/DPR are not genuine browser Ctrl+zoom.', 'No DB or application mutation; GET network only.']};
    assert.deepEqual(report.servedBefore, report.sourceBefore, 'Served target matches reviewed source before acceptance');
    const {chromium} = require('playwright'), browser = await chromium.launch({headless: true, executablePath: CHROME});
    try {
        for (const viewport of [{width:1440,height:1000},{width:390,height:844}]) for (const theme of ['light','dark']) for (const target of targets) {
            const row = {path: target.path, viewport, theme, screenshots: [], pass: false}; report.rows.push(row);
            const context = await browser.newContext({viewport, colorScheme: theme, deviceScaleFactor: 1, serviceWorkers: 'block'}), page = await context.newPage();
            const stem = label + '-' + target.key + '-' + viewport.width + '-' + theme;
            try {
                await observe(context, page, row); await ready(page, target, row, theme);
                row.bank = await bankQA(page, target);
                row.details = await detailsQA(page, target, dir, stem);
                row.practice = await practiceQA(page, target, dir, stem);
                row.layout = await layoutQA(page); await fullCapture(page, target, row, dir, stem);
                await tocAndReload(page, target, row); clean(row); row.pass = true;
            } catch (error) { row.failure = failureInfo(error); }
            finally { await context.close(); save(dir, stem + '.json', row); }
            console.log(JSON.stringify({path: target.path, width: viewport.width, theme, pass: row.pass, failure: row.failure || null}));
        }
        assert.deepEqual(sourceHashes(), report.sourceBefore); assert.deepEqual(sourceHashes(SERVED), report.servedBefore);
        report.pass = report.rows.length === 12 && report.rows.every(r => r.pass);
    } finally { await browser.close(); report.finishedAt = new Date().toISOString(); save(dir, file, report); }
    return report;
}
async function noJS(browser, target, dir, stem) {
    const row = {path: target.path, mode: 'no-JS', javaScriptEnabled: false, screenshots: [], details: [], keys: [], pass: false};
    const context = await browser.newContext({javaScriptEnabled: false, viewport:{width:390,height:844}, serviceWorkers:'block'}), page = await context.newPage();
    try {
        await observe(context, page, row); await ready(page, target, row, 'light', false);
        for (const point of target.lesson.sections.flatMap(s => s.points).filter(p => p.detail)) {
            const detail = page.locator('[data-m43-basic-point="' + point.id + '"] details'), summary = detail.locator(':scope > summary');
            await summary.click(); assert.equal(await detail.evaluate(n => n.open), true); await summary.focus(); await summary.press('Space'); assert.equal(await detail.evaluate(n => n.open), false);
            await summary.press('Enter'); assert.equal(await detail.evaluate(n => n.open), true); await summary.click(); row.details.push(point.detail.id);
        }
        for (const task of target.lesson.practice) {
            const card = caseNode(page, task), detail = card.locator('[data-m43-ui-explanation]'), summary = detail.locator(':scope > summary');
            await summary.click(); assert.equal(await card.locator('[data-m43-self-check-answer]').isVisible(), true);
            const keyDOM = new JSDOM(await card.locator('[data-m43-self-check-answer]').innerHTML()); try { feedbackFidelity(keyDOM.window.document, task); } finally { keyDOM.window.close(); }
            for (const control of task.controls.filter(c => c.kind === 'manual')) assert.deepEqual((await field(card, control).locator('[data-m43-static-token]').allTextContents()).map(norm), control.tokens);
            await summary.focus(); await summary.press('Space'); assert.equal(await detail.evaluate(n => n.open), false); row.keys.push(task.id);
        }
        await fullCapture(page, target, row, dir, stem); clean(row); row.pass = true;
    } catch (error) { row.failure = {name:error.name,message:error.message.split('\n')[0]}; }
    finally { await context.close(); } return row;
}
async function supplemental(dir, label) {
    dir = privateDirectory(dir, label); const file = label + '-supplemental.json'; assert.equal(fs.existsSync(path.join(dir,file)),false);
    const report = {at:new Date().toISOString(),base:BASE,noJS:[],responsive:[],references:[],pass:false,sourceBefore:sourceHashes(),limits:['320 CSS pixels is viewport coverage; DPR 2 is density coverage, not genuine Ctrl+zoom.','Production is not accessed.']};
    const {chromium} = require('playwright'),browser = await chromium.launch({headless:true,executablePath:CHROME});
    try {
        for (const target of targets) report.noJS.push(await noJS(browser,target,dir,label+'-'+target.key+'-no-js'));
        for (const mode of [{name:'320',width:320,dpr:1},{name:'dpr2',width:390,dpr:2}]) for (const target of targets) {
            const row={path:target.path,mode:mode.name,screenshots:[],pass:false},context=await browser.newContext({viewport:{width:mode.width,height:844},deviceScaleFactor:mode.dpr,serviceWorkers:'block'}),page=await context.newPage();report.responsive.push(row);
            try {await observe(context,page,row);await ready(page,target,row);row.layout=await layoutQA(page);await fullCapture(page,target,row,dir,label+'-'+target.key+'-'+mode.name);clean(row);row.pass=true;}
            catch(error){row.failure={name:error.name,message:error.message.split('\n')[0]};}finally{await context.close();}
        }
        // Accepted reference pages compare to independent saved BEFORE text/metadata,
        // with dynamic bank content excluded by the same safe snapshot helper.
        const before=JSON.parse(fs.readFileSync(path.join(SERVED,'storage/app/seo-m43-local/browser-before-v1/manifest.json'),'utf8'));
        const {safeDOM}=require('./capture-m43-before-browser.cjs');
        for(const route of referencePaths){const row={path:route,pass:false},context=await browser.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'}),page=await context.newPage();report.references.push(row);
            try {await observe(context,page,row);const response=await page.goto(BASE+route,{waitUntil:'networkidle'});assert.equal(response.status(),200);await page.waitForFunction(()=>Boolean(window.Alpine));
                const snapshot=await safeDOM(page),old=before.rows.find(r=>r.path===route&&r.viewport.width===1440);assert.ok(old);assert.equal(snapshot.mainText,old.dom.mainText,'Protected reference text unchanged');assert.deepEqual(snapshot.metadata,old.dom.metadata,'Protected reference metadata unchanged');
                row.textSha256=sha(snapshot.mainText);row.metadataUnchanged=true;row.screenshot=await shot(page,dir,label+'-reference-'+route.split('/').at(-1)+'.png',true);clean(row);row.pass=true;
            }catch(error){row.failure={name:error.name,message:error.message.split('\n')[0]};}finally{await context.close();}}
        assert.deepEqual(sourceHashes(),report.sourceBefore);report.pass=[...report.noJS,...report.responsive,...report.references].every(row=>row.pass);
    }finally{await browser.close();report.finishedAt=new Date().toISOString();save(dir,file,report);}return report;
}
if(require.main===module){const[mode,dir,label]=process.argv.slice(2);assert.ok(['--pages','--supplemental'].includes(mode),'Explicit acceptance mode required');
    (mode==='--pages'?pages(dir,label):supplemental(dir,label)).then(report=>{if(!report.pass)process.exitCode=1;}).catch(error=>{console.error(error.stack);process.exitCode=1;});}
module.exports={BASE,MASTER_PATH,MASTER_SHA,PRACTICE_COMPONENT_SELECTOR,master,targets,uiCases,allowedRequest,safeUrl,norm,onceOrdered,cellText,examplePairs,pointFidelity,feedbackFidelity,authorFidelity,sourceHashes,pages,supplemental,
    practiceQA,detailsQA,layoutQA,tocAndReload};
