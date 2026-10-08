'use strict';
// Real, GET-only gramlyze.loc acceptance. Expectations come from the frozen
// author master, not generated AFTER definitions. Importing runs no HTTP/DB.
const fs = require('node:fs'), path = require('node:path'), assert = require('node:assert/strict'), crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const referenceProbe=require('./capture-m44-reference-supplement.cjs');
const beforeHelper=require('./capture-m44-before.cjs');
const BEFORE_FILE='storage/app/seo-m44-local/browser-before-v2/manifest.json';
const BEFORE_SHA='7d4877b5c48da01e52d6b538f719f5ea5f2df810542a497a20dd885b16e6b683';
const HTTP_BEFORE_FILE='storage/app/seo-m44-local/http-before-v1/manifest.json';
const HTTP_BEFORE_SHA='3b60f24043c59a2aca317e0c9da1cd69c1528b30d8565109bf2b6014f61144ad';
const HISTORICAL_REFERENCE_FILE='storage/app/seo-m44-local/browser-reference-supplement-v3/manifest.json';
const HISTORICAL_REFERENCE_SHA='d1b61830ca9899c4fd2b5fd1f9671b3cf85b3864c1bf015d0eb1fb37fb16c666';
const REFERENCE_FILE='storage/app/seo-m44-local/browser-reference-calibration-v4/manifest.json';
const REFERENCE_SHA='b7b5b01595f42d05a9a4657f333e06985bd04841ca7e3fe225b92889a35ebd50';
const SLATE_CAPTION_IDS=new Set(['m44-will-prediction','m44-choice-result','m44-choice-finishing']);
const ROSE_CAPTION_ID='m44-choice-duration';
const SOURCE_MANIFEST_FILE='storage/app/seo-m44-local/source-sync-before-v1/manifest.json';
const SOURCE_MANIFEST_SHA='67ba2e5ff3df87ea8ad971f9928946632e3c7385eeda7769cd5d368cf5754bde';
const PRESENTATION_RECEIPTS=[
    {file:'storage/app/seo-m44-local/presentation-sync-v1.json',sha256:'6d173bee1b043a3802f52246cb284285f8e7a6837a3e964f6d96f6a932d790c0',paths:['app/Support/M44NativeHtml.php','resources/views/engram/theory/blocks-v3/m44-native-section.blade.php']},
    {file:'storage/app/seo-m44-local/presentation-sync-v2.json',sha256:'5dc483f3163c403e4e059fd23cf78cb5d7a70b065dd7390bfb644afa76d9e232',paths:['resources/views/engram/theory/blocks-v3/m44-native-styles.blade.php']},
];
const ROOT = path.resolve(__dirname, '../..'), SERVED = 'D:/DEV/htdocs/gramlyze.loc';
const BASE = 'http://gramlyze.loc', CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const MASTER_PATH = 'docs/content/m44-authored-future-forms.v1.0.0.json';
const MASTER_SHA = '541db4dec57ed372d23832e849f84c0554fb40faf73693c5b7ab2d306fa7b888';
const PRACTICE_COMPONENT_SELECTOR = '[data-m44-practice-ui] [x-data^="m44PracticeUi"]';
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value ?? '').replace(/\s+/gu, ' ').trim();
const masterBytes = fs.readFileSync(path.join(ROOT, MASTER_PATH));
assert.equal(sha(masterBytes), MASTER_SHA, 'Frozen independently reviewed M44 master');
const master = JSON.parse(masterBytes);
const targets = master.lessons.map(lesson => ({key: lesson.key, path: lesson.theory_path, title: lesson.title, lesson}));
const referencePaths = referenceProbe.REFERENCES.map(r=>r.path);
function exactPrivate(file,expected){const bytes=fs.readFileSync(path.join(SERVED,file));assert.equal(sha(bytes),expected,'Independent private evidence exact hash: '+file);return JSON.parse(bytes);}
let beforeCache,referenceCache;
function beforeData(){return beforeCache ||= exactPrivate(BEFORE_FILE,BEFORE_SHA);}
function referenceData(){if(referenceCache)return referenceCache;const data=exactPrivate(REFERENCE_FILE,REFERENCE_SHA);assert.equal(data.pass,true);assert.equal(data.rows.length,16);return referenceCache=data;}
const isManual=control=>['manual','tokens'].includes(control.kind);
function canonicalTokenOrder(control){
    const tokens=control.tokens,answer=String(control.canonical_answer),seen=new Set();
    function search(offset,order){if(offset===answer.length)return order.length===tokens.length?order:null;
        for(let i=0;i<tokens.length;i++){if(seen.has(i))continue;const token=String(tokens[i]);if(!answer.startsWith(token,offset))continue;
            const end=offset+token.length;if(end!==answer.length&&answer[end]!==' ')continue;
            seen.add(i);const result=search(end===answer.length?end:end+1,[...order,i]);if(result)return result;seen.delete(i);
        }return null;}
    const order=search(0,[]);assert.ok(order,'Canonical answer must use all authored token identities exactly once: '+control.id);return order;
}

function uiCases(lesson) {
    return lesson.practice.map(task=>{const controls=task.controls.map(control=>({id:control.id,kind:isManual(control)?'manual':control.kind,
        source_kind:control.kind,label:control.label_uk,required:control.required,...(control.stimulus_en?{stimulus_en:control.stimulus_en}:{}),
        ...(isManual(control)?{options:[],answer:control.canonical_answer,accepted:control.accepted_answers,tokens:control.tokens}
            :{options:control.options,answer:control.correct_value})}));
        return {id:task.id,scoring:task.scoring,source_index:task.source_index,interaction:controls.length>1?'compound':controls[0].kind,controls};
    });
}
function allowedRequest(value,method){return referenceProbe.allowedRequest(value,method);}
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
function cellText(cell) { return typeof cell === 'string' ? norm(cell) : norm(cell.formula ?? cell.text_uk ?? [cell.en, cell.uk, cell.note_uk].filter(Boolean).join(' ')); }
function feedbackFidelity(node, task) {
    assert.deepEqual([...node.querySelectorAll('p')].filter(n => !n.closest('.theory-example')).map(text), task.feedback.paragraphs_uk.map(norm), 'Exact once-only feedback paragraphs');
    examplePairs(node, task.feedback.answer_examples, task.id + ' answer examples');
}
function authorFidelity(document, target) {
    const lesson = target.lesson, main = only(document, '[data-theory-main]');
    assert.deepEqual([...document.querySelectorAll('h1')].map(text), [lesson.title]);
    assert.ok(text(main).includes(norm(lesson.subtitle)), 'Exact authored subtitle');
    for (const hero of lesson.hero) for (const field of ['label', 'text', 'formula']) assert.ok(text(main).includes(norm(hero[field])), 'Exact hero ' + field);
    assert.deepEqual([...main.querySelectorAll('[data-m44-author-section]')].map(n => n.dataset.m44AuthorSection), lesson.sections.map(s => s.id));
    let points = 0, details = 0;
    for (const section of lesson.sections) {
        const owner = only(main, '[data-m44-author-section="' + section.id + '"]');
        assert.equal(text(stripped(only(owner, '.theory-section-header h2'))), section.title);
        assert.equal(owner.getAttribute('data-m44-native-layout'), section.native_kind);
        assert.deepEqual([...owner.querySelectorAll('[data-m44-basic-point]')].map(n => n.id), section.points.map(p => p.id));
        for (const point of section.points) { pointFidelity(only(owner, '[data-m44-basic-point="' + point.id + '"]'), point); points++; if (point.detail) details++; }
        if (section.intro_uk) assert.ok(text(owner).includes(section.intro_uk));
        for (const note of section.notes_uk || []) assert.ok(text(owner).includes(note));
        if (section.table) {
            const table = only(owner, '[data-m44-native-table]');
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
        const outsidePoints = stripped(owner, '[data-m44-basic-point],table,script,style,noscript,[data-theory-ui]');
        examplePairs(outsidePoints, section.note_examples || [], section.id + ' note examples');
    }
    assert.equal(main.querySelectorAll('[data-theory-native-extension] > details').length, details);
    const ids = [...main.querySelectorAll('[id]')].map(n => n.id); assert.equal(ids.length, new Set(ids).size, 'No hidden duplicate anchors');
    return {sections: lesson.sections.length, points, details, masterSha256: MASTER_SHA};
}
function sourceHashes(root = ROOT) {
    const files = [MASTER_PATH, 'public/js/authored-practice-ui.js', 'public/js/m44-practice-ui.js', 'app/Support/M44NativeHtml.php',
        'resources/views/engram/theory/blocks-v3/m44-native-styles.blade.php',
        'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php',
        'resources/views/engram/theory/blocks-v3/m44-native-section.blade.php',
        'resources/views/engram/theory/blocks-v3/m44-native-table.blade.php',
        'resources/views/engram/theory/blocks-v3/m44-native-mistake.blade.php',
        'resources/views/theory/partials/point-detail-fragment.blade.php', ...master.lessons.map(l => l.definition_path)];
    return Object.fromEntries(files.map(file => [file, sha(fs.readFileSync(path.join(root, file)))]));
}
function privateDirectory(dir, label) {
    assert.match(label, /^[a-z0-9-]+$/u);
    const root = path.resolve(SERVED, 'storage/app/seo-m44-local'), absolute = path.resolve(dir);
    assert.ok(absolute === root || absolute.startsWith(root + path.sep), 'Only private M44 local evidence');
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
        assert.equal(await page.locator(PRACTICE_COMPONENT_SELECTOR).count(), 1, 'Exactly one authored M44 practice component, separate from own-bank widget');
        assert.ok((await page.locator(PRACTICE_COMPONENT_SELECTOR).getAttribute('x-data')).startsWith('m44PracticeUi('), 'Exact authored component function');
        await page.waitForFunction(selector => window.Alpine?.$data(document.querySelector(selector))?.cases?.length === 6, PRACTICE_COMPONENT_SELECTOR);
        await page.evaluate(value => { document.documentElement.classList.toggle('dark', value === 'dark'); localStorage.setItem('theme', value); }, theme);
        const publicCases = await page.locator(PRACTICE_COMPONENT_SELECTOR).evaluate(n => JSON.parse(JSON.stringify(window.Alpine.$data(n).cases)));
        assert.deepEqual(publicCases, uiCases(target.lesson), 'Live public control payload equals independent frozen master mapping');
    }
    const dom = new JSDOM(await page.content()); try { row.author = authorFidelity(dom.window.document, target); row.jsonLd=beforeHelper.domSummary(dom.window.document,'[data-theory-main]').jsonLd; } finally { dom.window.close(); }
    row.basicVisibility = await page.locator('[data-m44-basic-point]').evaluateAll(nodes => nodes.map(node => ({id: node.id,
        visible: node.getClientRects().length > 0,
        hiddenBasicText: [...node.querySelectorAll('p[lang]')].filter(n => !n.closest('details')).filter(n => !n.getClientRects().length || getComputedStyle(n).visibility === 'hidden').length})));
    assert.ok(row.basicVisibility.every(p => p.visible && p.hiddenBasicText === 0), 'All complete basic prose/examples visible without opening details');
    row.metadata = await page.evaluate(() => {
        const meta = selector => document.querySelector(selector)?.content || null;
        return {title: document.title, h1: [...document.querySelectorAll('h1')].map(n => n.textContent.trim()), canonical: document.querySelector('link[rel="canonical"]')?.href || null,
            robots: meta('meta[name="robots"]'), description: meta('meta[name="description"]'), ogTitle: meta('meta[property="og:title"]'), ogDescription: meta('meta[property="og:description"]'),
            twitterTitle: meta('meta[name="twitter:title"]'), twitterDescription: meta('meta[name="twitter:description"]')};
    });
    const before = beforeData().rows.find(r=>r.path===target.path && r.theme===theme && r.viewport.width===(page.viewportSize().width===1440?1440:390));
    assert.ok(before, 'Independent BEFORE exists');
    for (const key of ['title','h1','canonical','robots','ogTitle','twitterTitle']) assert.deepEqual(row.metadata[key], before.dom.metadata[key], 'Preserved metadata identity ' + key);
    const httpBeforeRows=exactPrivate(HTTP_BEFORE_FILE,HTTP_BEFORE_SHA).rows.filter(r=>r.route===target.path);
    assert.equal(httpBeforeRows.length,1,'Exact independent HTTP BEFORE target row');
    assert.equal(httpBeforeRows[0].status,200);assert.equal(httpBeforeRows[0].finalUrl,BASE+target.path);
    assert.equal(row.http.xRobotsTag, httpBeforeRows[0].xRobotsTag, 'Local noindex preserved');
    row.expectedSubtitleDerivedMetadataChanges = ['description','ogDescription','twitterDescription'].filter(key => row.metadata[key] !== before.dom.metadata[key]);
}
async function bankQA(page, target) {
    const inventory = JSON.parse(fs.readFileSync(path.join(SERVED, 'storage/app/seo-m44-local/m44-before-v1.json'), 'utf8'));
    const compiled=JSON.parse(fs.readFileSync(path.join(ROOT,'database/content-patches/m44-authored-future-forms.v1.0.0.json'),'utf8'));
    const owner = inventory.targets.find(t => t.identity === target.lesson.identity);
    assert.ok(owner, 'Exact actual BEFORE bank owner');
    const klass = compiled.targets.find(t=>t.identity===target.lesson.identity).bank.seeder_class;
    const bank = owner.banks.find(b => b.seeder_class === klass); assert.ok(bank?.linked_ids?.length, 'Actual own-linked bank inventory'); assert.equal(bank.linked_count,72,'Actual inspected own builder bank'); assert.equal(bank.full_own_bank,true);
    const widget = page.locator('[data-m44-practice-ui] [data-sentence-builder]'); assert.equal(await widget.count(), 1);
    await page.waitForFunction(n => window.Alpine.$data(n)?.questions?.length > 0, await widget.elementHandle());
    const pool = await widget.evaluate(n => {const q=window.Alpine.$data(n).questions;return {ids:q.map(x=>String(x.id)),types:[...new Set(q.map(x=>String(x.type)))]};});
    const allowed = new Set(bank.linked_ids.map(String));
    assert.equal(pool.ids.length, new Set(pool.ids).size); assert.ok(pool.ids.every(id=>allowed.has(id)), 'No global AllLevels pool leakage');
    assert.deepEqual(pool.types, ['4']);
    return {class:klass,actualLinkedCount:allowed.size,sampledCount:pool.ids.length,sampleIdsSha256:sha(JSON.stringify(pool.ids)),onlyOwnLinkedType4:true};
}
const caseNode = (page, task) => page.locator('[data-m44-ui-case="' + task.source_index + '"]');
const field = (card, control) => card.locator('[data-m44-control="' + control.id + '"]');
const option = (area, control, value) => area.getByRole('radio', {name: control.options.find(o => o.value === value).label, exact: true});
async function fillControl(card, control, value, keyboard = false) {
    const area = field(card, control);
    if (isManual(control)) await area.locator('[data-m44-answer-input]').fill(value);
    else { const button = option(area, control, value); if (keyboard) { await button.focus(); await button.press('Space'); } else await button.click(); }
    await card.locator('[data-m44-case-feedback]').waitFor({state:'hidden',timeout:3000});
}
async function checkCase(card, correct) {
    await card.locator('[data-m44-check]').click();
    const feedback = card.locator('[data-m44-case-feedback]');
    await feedback.waitFor({state: 'visible'});
    assert.equal(norm(await feedback.textContent()), correct ? 'Правильно' : 'Не всі частини відповіді правильні');
    assert.equal(await card.locator('[data-m44-ui-explanation]').evaluate(n => n.open), true);
}
async function resetCase(card) {
    const button = card.locator('[data-m44-reset]'); await button.focus(); await button.press('Enter');
    await card.locator('[data-m44-case-feedback]').waitFor({state:'hidden',timeout:3000});
    assert.equal(await card.locator('[data-m44-case-feedback]').isVisible(), false);
    assert.equal(await card.locator('[data-m44-ui-explanation]').evaluate(n => n.open), false);
    for (const input of await card.locator('[data-m44-answer-input]').all()) assert.equal(await input.inputValue(), '');
    assert.equal(await card.locator('[data-m44-answer][aria-checked="true"]').count(), 0);
    assert.equal(await card.locator('[data-m44-token]:disabled').count(), 0, 'Reset returns every token');
}
function answer(control) { return isManual(control) ? control.canonical_answer : control.correct_value; }
async function practiceQA(page, target, dir, stem, capture = true, acceptanceRow = null) {
    const styleRow=acceptanceRow || {theme:await page.evaluate(()=>document.documentElement.classList.contains('dark')?'dark':'light')};
    const rows = [];
    assert.deepEqual(await page.locator('[data-m44-ui-case]').evaluateAll(ns => ns.map(n => Number(n.dataset.m44UiCase))), [1,2,3,4,5,6]);
    for (const task of target.lesson.practice) {
        const card = caseNode(page, task), result = {task: task.id, controls: [], screenshots: [], states: []}; rows.push(result);
        const prompt = norm(await card.locator('[data-m44-author-prompt]').textContent());
        onceOrdered(prompt, [task.title, task.prompt_uk, task.context_uk], task.id + ' prompt');
        const key = card.locator('[data-m44-self-check-answer]');
        const feedbackDOM = new JSDOM(await key.innerHTML()); try { feedbackFidelity(feedbackDOM.window.document, task); } finally { feedbackDOM.window.close(); }
        assert.equal(await key.isVisible(), false, 'No visible answer before check');
        assert.equal(await card.locator('[data-m44-ui-explanation] > summary').isVisible(), false);
        for (const control of task.controls) {
            const area = field(card, control); assert.equal(await area.count(), 1);
            assert.equal(norm(await area.locator('legend').textContent()), control.label_uk);
            if (control.stimulus_en) assert.equal(norm(await area.locator(':scope > p[lang="en"]').textContent()), control.stimulus_en);
            if (isManual(control)) {
                assert.equal(await area.locator('textarea').inputValue(), '');
                const tokens = area.locator('[data-m44-token]'); assert.deepEqual((await tokens.allTextContents()).map(norm).sort(), [...control.tokens].sort());
                assert.equal(await area.locator('textarea').evaluate(n => getComputedStyle(n).textTransform), 'none');
            } else {
                const buttons = area.locator('[data-m44-answer]');
                assert.deepEqual((await buttons.allTextContents()).map(norm), control.options.map(o => o.label));
                assert.deepEqual(await buttons.evaluateAll(ns => ns.map(n => n.dataset.m44Answer)), control.options.map(o => o.value));
                assert.equal(await buttons.evaluateAll(ns => ns.every(n => getComputedStyle(n).textTransform === 'none')), true);
                await buttons.first().focus(); await buttons.first().press('ArrowLeft');
                assert.equal(await buttons.last().getAttribute('aria-checked'), 'true', 'Radio reverse wrap');
                await buttons.last().press('ArrowRight'); assert.equal(await buttons.first().getAttribute('aria-checked'), 'true');
                await buttons.first().press('ArrowDown'); assert.equal(await buttons.nth(1).getAttribute('aria-checked'),'true','Radio ArrowDown chooses next option');
                await buttons.nth(1).press('ArrowUp'); assert.equal(await buttons.first().getAttribute('aria-checked'),'true','Radio ArrowUp chooses previous option');
            }
        }
        await resetCase(card); result.states.push('initial');
        if (capture) result.screenshots.push(await shot(card, dir, stem + '-' + task.id + '-initial.png'));
        await checkCase(card, false); result.states.push('empty');
        result.emptyStyles=await compareStyles(page,styleRow,'[data-m44-ui-case="'+task.source_index+'"]','empty',Object.fromEntries(task.controls.map(c=>[c.id,false])));
        await resetCase(card);
        for (const control of task.controls) await fillControl(card, control, answer(control), true);
        await checkCase(card, true); result.states.push('correct');
        result.correctStyles=await compareStyles(page,styleRow,'[data-m44-ui-case="'+task.source_index+'"]','correct',Object.fromEntries(task.controls.map(c=>[c.id,true])));
        if (capture) result.screenshots.push(await shot(card, dir, stem + '-' + task.id + '-correct.png'));
        for (const [index, control] of task.controls.entries()) {
            // Every required subpart is independently emptied and independently wrong.
            await resetCase(card);
            for (const [otherIndex, other] of task.controls.entries()) if (otherIndex !== index) await fillControl(card, other, answer(other));
            await checkCase(card, false); result.states.push('partial:' + control.id);
            await compareStyles(page,styleRow,'[data-m44-ui-case="'+task.source_index+'"]','partial',Object.fromEntries(task.controls.map((c,p)=>[c.id,p!==index])));
            const wrong = isManual(control) ? 'not the requested answer' : control.options.find(o => o.value !== control.correct_value).value;
            await fillControl(card, control, wrong); assert.equal(await card.locator('[data-m44-case-feedback]').isVisible(), false, 'Editing hides prior feedback');
            await checkCase(card, false);
            await compareStyles(page,styleRow,'[data-m44-ui-case="'+task.source_index+'"]','wrong',Object.fromEntries(task.controls.map((c,p)=>[c.id,p!==index])));
            if (capture && index === 0) result.screenshots.push(await shot(card, dir, stem + '-' + task.id + '-wrong.png'));
            const controlResult = {id: control.id, emptyAndWrongRejected: true, aliases: [], tokens: null}; result.controls.push(controlResult);
            if (isManual(control)) {
                for (const alias of control.accepted_answers) for (const variant of new Set([alias, alias.replace(/'/gu, '’'), alias.replace(/[.!?]+$/u, '')])) {
                    await fillControl(card, control, variant); await checkCase(card, true); controlResult.aliases.push(variant);
                }
                const input = field(card, control).locator('textarea'); await input.fill('');
                const tokens = field(card, control).locator('[data-m44-token]');
                const duplicates=control.tokens.filter((token,index)=>control.tokens.indexOf(token)!==index);
                for(const duplicate of new Set(duplicates)){
                    const same=field(card,control).locator('[data-m44-token]').filter({hasText:new RegExp('^'+duplicate.replace(/[.*+?^${}()|[\]\\]/gu,'\\$&')+'$')});
                    const count=control.tokens.filter(t=>t===duplicate).length;
                    for(let d=0;d<count;d++){const available=field(card,control).locator('[data-m44-token]:not(:disabled)').filter({hasText:new RegExp('^'+duplicate.replace(/[.*+?^${}()|[\]\\]/gu,'\\$&')+'$')}).first();await available.focus();await available.press('Enter');}
                    assert.equal(await same.evaluateAll(ns=>ns.every(n=>n.disabled)),true,'Distinct repeated token identities consumed');
                    await input.fill('');assert.equal(await same.evaluateAll(ns=>ns.every(n=>!n.disabled)),true,'Typed clear releases repeated-token identities and history');
                }
                // Select by canonical token text, not the shuffled screen order.
                for (const tokenIndex of canonicalTokenOrder(control)) { const token=control.tokens[tokenIndex]; const chosen = field(card, control).locator('[data-m44-token]:not(:disabled)').filter({hasText: new RegExp('^' + token.replace(/[.*+?^${}()|[\]\\]/gu, '\\$&') + '$')}).first();
                    await chosen.focus(); await chosen.press('Enter'); }
                assert.equal(await input.inputValue(), control.canonical_answer); await input.press('Control+Enter');
                assert.equal(norm(await card.locator('[data-m44-case-feedback]').textContent()), 'Правильно');
                assert.equal(await tokens.evaluateAll(ns => ns.every(n => n.disabled)), true);
                await input.fill(''); assert.equal(await tokens.evaluateAll(ns => ns.every(n => !n.disabled)), true, 'Manual deletion returns all tokens');
                await fillControl(card, control, answer(control)); controlResult.tokens = {keyboardBuiltCanonical: true, deletionReturnsAll: true, repeatedTokenClear:duplicates.length>0};
            }
        }
        await resetCase(card); result.states.push('wrong', 'reset');
        if (capture) result.screenshots.push(await shot(card, dir, stem + '-' + task.id + '-reset.png'));
        for (const control of task.controls) await fillControl(card, control, answer(control)); await checkCase(card, true);
        result.reanswerCorrect = true; console.log(JSON.stringify({task:task.id,path:target.path,states:result.states.length,controls:result.controls.length,pass:true}));
    }
    assert.equal(norm(await page.locator('[data-m44-ui-score]').textContent()), 'Результат: 6 / 6');
    for (const task of target.lesson.practice) await resetCase(caseNode(page, task));
    assert.equal(norm(await page.locator('[data-m44-ui-score]').textContent()), 'Результат: 0 / 6');
    return rows;
}
async function detailsQA(page, target, dir, stem) {
    const points = target.lesson.sections.flatMap(s => s.points).filter(p => p.detail), all = page.locator('[data-m44-basic-point] [data-theory-native-extension] > details'), rows = [];
    assert.equal(await all.count(), points.length); assert.equal(await all.evaluateAll(ns => ns.every(n => !n.open)), true);
    for (const point of points) {
        const own = page.locator('[data-m44-basic-point="' + point.id + '"]'), detail = own.locator('[data-theory-native-extension] > details'), summary = detail.locator(':scope > summary');
        assert.equal(await summary.evaluate(n => { const own = n.closest('[data-m44-basic-point]'); const a = n.getBoundingClientRect(), b = own.getBoundingClientRect(); return a.left >= b.left && a.right <= b.right && a.top >= b.top && a.bottom <= b.bottom; }), true);
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
    for (const region of await page.locator('[data-m44-author-section] .theory-table-scroll').all()) {
        const before = await region.evaluate(n => ({scrollWidth: n.scrollWidth, clientWidth: n.clientWidth, max: n.scrollWidth - n.clientWidth}));
        if (before.max > 1) { await region.focus(); for (let i = 0; i < 35; i++) await region.press('ArrowRight');
            await page.waitForFunction(n => n.scrollLeft > 0, await region.elementHandle());
            before.keyboardScroll = await region.evaluate(n => n.scrollLeft > 0); await region.evaluate(n => n.scrollLeft = 0); }
        tables.push(before);
    }
    const layout = await page.evaluate(() => {
        const main = document.querySelector('[data-theory-main]'), root = document.documentElement;
        const items = [...main.querySelectorAll('[data-m44-basic-point], [data-m44-basic-point] p[lang], [data-m44-ui-case], button, textarea')].filter(n => !n.closest('.theory-table-scroll') && n.getClientRects().length);
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
    row.componentStyles = await page.locator('[data-m44-author-section]').evaluateAll(nodes => nodes.map(node => {
        const css = item => {if(!item)return null;const c=getComputedStyle(item);return {background:c.backgroundColor,color:c.color,border:c.borderColor,borderWidth:c.borderWidth,radius:c.borderRadius,padding:c.padding,font:c.fontFamily,size:c.fontSize,transform:c.textTransform};};
        return {id:node.dataset.m44AuthorSection,layout:node.dataset.m44NativeLayout,card:css(node.querySelector('.theory-section-card')),header:css(node.querySelector('.theory-section-header')),point:css(node.querySelector('.theory-item')),english:css(node.querySelector('.theory-example [lang="en"]')),translation:css(node.querySelector('.theory-translation'))};
    }));
    for (const section of target.lesson.sections) row.screenshots.push(await shot(page.locator('[data-m44-author-section="' + section.id + '"]'), dir, stem + '-' + section.id + '.png'));
    row.screenshots.push(await shot(page.locator('[data-m44-practice-ui]'), dir, stem + '-practice.png'));
    await page.evaluate(() => scrollTo(0, 0));
    for (let step = 0; step < 220; step++) { const end = await page.evaluate(() => { scrollBy(0, innerHeight * 0.8); return scrollY + innerHeight >= document.documentElement.scrollHeight - 1; }); if (end) break; await page.waitForTimeout(35); }
    row.screenshots.push(await shot(page, dir, stem + '-footer.png'));
    row.documentHeight=await page.evaluate(()=>document.documentElement.scrollHeight);
    await page.evaluate(() => scrollTo(0, 0));
    if(row.documentHeight<=28000)row.screenshots.push(await shot(page, dir, stem + '-full.png', true));
    else{
        row.fullPageLimit='Document exceeds 28000 CSS pixels; actual scroll tiles retained instead of an unreliable oversized PNG';
        for(let tile=0;tile<240;tile++){
            row.screenshots.push(await shot(page,dir,stem+'-scroll-'+String(tile).padStart(3,'0')+'.png'));
            const end=await page.evaluate(()=>{scrollBy(0,innerHeight*.8);return scrollY+innerHeight>=document.documentElement.scrollHeight-1;});
            if(end)break;await page.waitForTimeout(35);
        }await page.evaluate(()=>scrollTo(0,0));
    }
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
    await page.waitForFunction(() => [...document.querySelectorAll('[data-m44-basic-point] details')].every(n => n.open));
    await page.emulateMedia({media: 'screen'});
    await page.waitForFunction(() => [...document.querySelectorAll('[data-m44-basic-point] details')].every(n => !n.open)); row.print = true;
    await page.reload({waitUntil: 'networkidle'});
    assert.equal(await page.locator('[data-m44-basic-point] details[open]').count(), 0); row.reloadClosed = true;
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

function sourceReceiptProjection(records,receipts){
    assert.equal(receipts.length,PRESENTATION_RECEIPTS.length,'Only the two approved presentation receipts');
    const projected=Object.fromEntries(records.map(r=>[r.path,{canonical:r.worktree_sha256,served:r.after_sha256,mode:r.mode}]));
    assert.equal(Object.keys(projected).length,records.length,'Unique original source paths');
    for(const [i,receipt] of receipts.entries()){
        if(i===0){assert.equal(receipt.contentMasterPackageDefinitionsDatabaseUnchanged,true);assert.equal(receipt.initialSourceSyncAndApplyReceiptsUnchanged,true);}
        else assert.equal(receipt.applicationContentAndDatabaseUnchanged,true);
        const changes=i===0?receipt.changes:[{path:receipt.path,beforeSha256:receipt.beforeSha256,afterSha256:receipt.afterSha256}];
        assert.deepEqual(changes.map(c=>c.path),PRESENTATION_RECEIPTS[i].paths,'Exact finite approved presentation paths');
        for(const change of changes){const row=projected[change.path];assert.ok(row,'Approved source existed in initial manifest');assert.equal(row.mode,'finite-sync');
            assert.match(change.beforeSha256,/^[a-f0-9]{64}$/u);assert.match(change.afterSha256,/^[a-f0-9]{64}$/u);
            assert.equal(row.canonical,change.beforeSha256,'Canonical source continuity from original manifest');
            assert.equal(row.served,change.beforeSha256,'ROOT source continuity from original manifest');
            row.canonical=change.afterSha256;row.served=change.afterSha256;
        }
    }return projected;
}
function approvedSourceProjection(){
    return sourceReceiptProjection(exactPrivate(SOURCE_MANIFEST_FILE,SOURCE_MANIFEST_SHA),PRESENTATION_RECEIPTS.map(r=>exactPrivate(r.file,r.sha256)));
}
function assertServedSources(canonical,served){
    const projected=approvedSourceProjection();
    for(const [file,hash] of Object.entries(canonical)){
        if(projected[file]){assert.equal(hash,projected[file].canonical,'Canonical source bound by exact approved receipt chain');assert.equal(served[file],projected[file].served,'Served source bound by exact approved receipt chain');}
        else assert.equal(served[file],hash,'Unchanged dependency source identity');
    }
}
function referenceCandidates(role,width,theme,state=null){
    const rows=referenceData().rows.filter(r=>r.viewport.width===width&&r.theme===theme);
    const values=[];
    for(const row of rows){const groups=state?row.states.find(s=>s.state===state)?.styles||[]:[...row.styles,...(row.openDetailStyles||[])];
        const roleGroup=groups.find(r=>r.role===role);for(const [index,item] of (roleGroup?.elements||[]).entries()){
            if(state&& !item.visible)continue;values.push({reference:row.key,path:row.path,role,index,classes:item.classes,tag:item.tag,styles:item.styles});}}
    return values;
}
function finiteCaptionExpectation(pointId,width,theme){
    if(!SLATE_CAPTION_IDS.has(pointId)&&pointId!==ROSE_CAPTION_ID)return null;
    // Exact named historical BEFORE, not mutable M44 AFTER values. Preserve every property.
    const before=exactPrivate(HISTORICAL_REFERENCE_FILE,HISTORICAL_REFERENCE_SHA),layoutRow=before.rows.find(r=>r.key==='M43-A'&&r.viewport.width===width&&r.theme===theme);
    const layoutGroup=layoutRow.styles.find(g=>g.role==='usage-caption');
    const layoutIndex=layoutGroup.elements.findIndex(n=>n.tag==='SPAN'&&n.classes==='text-blue-700');
    assert.ok(layoutIndex>=0);const layout=layoutGroup.elements[layoutIndex];
    const rose=pointId===ROSE_CAPTION_ID,paletteRow=before.rows.find(r=>r.key==='PPC'&&r.viewport.width===width&&r.theme===(rose?'light':theme));
    const paletteRole=rose?'practice-part-feedback':'usage-caption',paletteGroup=paletteRow.styles.find(g=>g.role===paletteRole);
    const paletteIndex=paletteGroup.elements.findIndex(n=>n.classes.split(/\s+/u).includes(rose?'text-rose-700':'text-slate-700'));
    assert.ok(paletteIndex>=0);const palette=paletteGroup.elements[paletteIndex];
    return {reference:'finite-caption-composition',path:layoutRow.path,role:'usage-caption',index:layoutIndex,
        classes:rose?'text-rose-700':'text-slate-700',tag:'SPAN',styles:{...layout.styles,color:palette.styles.color},
        provenance:{pointId,layout:{reference:layoutRow.key,path:layoutRow.path,role:'usage-caption',index:layoutIndex,theme,sourceSha256:HISTORICAL_REFERENCE_SHA},
            palette:{reference:paletteRow.key,path:paletteRow.path,role:paletteRole,index:paletteIndex,theme:paletteRow.theme,property:'color',sourceSha256:HISTORICAL_REFERENCE_SHA},
            reason:rose?'Existing native rose700 base palette; unchanged stylesheet has only practice-scoped dark override, which cannot match this caption.':'Post-M43.1 H3-span caption layout plus native PPC slate palette; flex blockification of PPC span is not the M43.1 composition.'}};
}
async function compareStyles(page,row,scope='[data-theory-main]',state=null,expectedParts=null){
    row.styleSettlement ||= [];row.styleSettlement.push(await referenceProbe.settleStyles(page,scope));
    const roles=referenceProbe.ROLES.map(([role,selector])=>[role,selector.replaceAll('m43','m44')]);
    const chosen=state?roles.filter(([role])=>['practice-feedback','practice-part-feedback'].includes(role)):roles;
    const snapshots=await page.locator(scope).evaluate((root,{roles,properties})=>roles.map(([role,selector])=>({role,
        elements:[...root.querySelectorAll(selector)].map(n=>({tag:n.tagName,classes:n.className,pointId:n.closest('[data-m44-basic-point]')?.id||null,controlId:n.closest('[data-m44-control]')?.getAttribute('data-m44-control')||null,visible:n.getClientRects().length>0,
            styles:Object.fromEntries(properties.map(p=>[p,getComputedStyle(n).getPropertyValue(p)]))}))})),{roles:chosen,properties:referenceProbe.PROPERTIES});
    const result={referenceSha256:REFERENCE_SHA,beforeSha256:BEFORE_SHA,state,expectedParts,components:0,properties:0,matches:[],differences:[],uncovered:[]};
    for(const group of snapshots){const candidates=referenceCandidates(group.role,page.viewportSize().width,row.theme,state);
        for(const [index,item] of group.elements.entries()){if(state&&!item.visible)continue;
            const finite=group.role==='usage-caption'?finiteCaptionExpectation(item.pointId,page.viewportSize().width,row.theme):null;
            let partState=null;
            if(state&&group.role==='practice-part-feedback'){
                assert.equal(typeof expectedParts?.[item.controlId],'boolean','Explicit planned state for stable required control ID');
                partState=expectedParts[item.controlId]?'correct':'wrong';
            }
            const comparisons=finite?[finite]:partState?referenceCandidates(group.role,page.viewportSize().width,row.theme,partState):candidates;
            if(finite){assert.equal(item.tag,finite.tag,'Only the exact finite caption SPAN');assert.equal(item.classes,finite.classes,'Exact frozen finite caption palette class');}
            if(!comparisons.length){result.uncovered.push({role:group.role,index});continue;}
            const distance=c=>Object.keys(item.styles).filter(p=>item.styles[p]!==c.styles[p]);
            const ranked=comparisons.map(c=>({candidate:c,diff:distance(c)})).sort((a,b)=>a.diff.length-b.diff.length);
            const best=ranked[0];result.components++;result.properties+=Object.keys(item.styles).length;
            if(best.diff.length)result.differences.push({role:group.role,index,reference:{key:best.candidate.reference,path:best.candidate.path,index:best.candidate.index},properties:best.diff.map(p=>({property:p,expected:best.candidate.styles[p],actual:item.styles[p]}))});
            else result.matches.push({role:group.role,index,reference:{key:best.candidate.reference,path:best.candidate.path,index:best.candidate.index},...(best.candidate.provenance?{provenance:best.candidate.provenance}: {})});
        }}
    row.styleEvidence ||= [];row.styleEvidence.push(result);
    assert.deepEqual(result.uncovered,[],'Every required actual component has an independent saved reference');
    assert.deepEqual(result.differences,[],'Computed presentation must match an independent reference signature');return result;
}

async function pages(dir, label) {
    dir = privateDirectory(dir, label); const file = label + '-pages.json'; assert.equal(fs.existsSync(path.join(dir, file)), false);
    const report = {at: new Date().toISOString(), base: BASE, mode: '12-live-page-states', masterSha256: MASTER_SHA, sourceBefore: sourceHashes(), servedBefore: sourceHashes(SERVED), rows: [], pass: false,
        limits: ['Google Fonts allowed and declared Latin/UK weights explicitly loaded; fresh matching reference supplement measured the same policy.', 'Viewport/DPR are not genuine browser Ctrl+zoom.', 'No DB or application mutation; GET network only.']};
    assertServedSources(report.sourceBefore,report.servedBefore);
    const {chromium} = require('playwright'), browser = await chromium.launch({headless: true, executablePath: CHROME});
    try {
        for (const viewport of [{width:1440,height:1000},{width:390,height:844}]) for (const theme of ['light','dark']) for (const target of targets) {
            const row = {path: target.path, viewport, theme, screenshots: [], pass: false}; report.rows.push(row);
            const context = await browser.newContext({viewport, colorScheme: theme, deviceScaleFactor: 1, serviceWorkers: 'block'}), page = await context.newPage();
            const stem = label + '-' + target.key + '-' + viewport.width + '-' + theme;
            try {
                await observe(context, page, row); await ready(page, target, row, theme);
                row.bank = await bankQA(page, target);
                row.fonts=await referenceProbe.fontEvidence(page);
                row.styleComparison=await compareStyles(page,row);
                row.details = await detailsQA(page, target, dir, stem);
                row.practice = await practiceQA(page, target, dir, stem, true, row);
                row.layout = await layoutQA(page); row.browserVersion=browser.version(); await fullCapture(page, target, row, dir, stem);
                await tocAndReload(page, target, row); clean(row); row.pass = true;
            } catch (error) { row.failure = failureInfo(error); }
            finally { await context.close(); save(dir, stem + '.json', row); }
            console.log(JSON.stringify({path: target.path, width: viewport.width, theme, pass: row.pass, failure: row.failure || null}));
        }
        assert.deepEqual(sourceHashes(), report.sourceBefore); assert.deepEqual(sourceHashes(SERVED), report.servedBefore);
        exactPrivate(BEFORE_FILE,BEFORE_SHA);exactPrivate(REFERENCE_FILE,REFERENCE_SHA);
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
            const detail = page.locator('[data-m44-basic-point="' + point.id + '"] details'), summary = detail.locator(':scope > summary');
            await summary.click(); assert.equal(await detail.evaluate(n => n.open), true); await summary.focus(); await summary.press('Space'); assert.equal(await detail.evaluate(n => n.open), false);
            await summary.press('Enter'); assert.equal(await detail.evaluate(n => n.open), true); await summary.click(); row.details.push(point.detail.id);
        }
        for (const task of target.lesson.practice) {
            const card = caseNode(page, task), detail = card.locator('[data-m44-ui-explanation]'), summary = detail.locator(':scope > summary');
            await summary.click(); assert.equal(await card.locator('[data-m44-self-check-answer]').isVisible(), true);
            const keyDOM = new JSDOM(await card.locator('[data-m44-self-check-answer]').innerHTML()); try { feedbackFidelity(keyDOM.window.document, task); } finally { keyDOM.window.close(); }
            for (const control of task.controls.filter(c => isManual(c))) assert.deepEqual((await field(card, control).locator('[data-m44-static-token]').allTextContents()).map(norm), control.tokens);
            await summary.focus(); await summary.press('Space'); assert.equal(await detail.evaluate(n => n.open), false); row.keys.push(task.id);
        }
        row.fonts=await referenceProbe.fontEvidence(page);row.browserVersion=browser.version();
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
            try {await observe(context,page,row);await ready(page,target,row);row.fonts=await referenceProbe.fontEvidence(page);row.browserVersion=browser.version();row.layout=await layoutQA(page);await fullCapture(page,target,row,dir,label+'-'+target.key+'-'+mode.name);clean(row);row.pass=true;}
            catch(error){row.failure={name:error.name,message:error.message.split('\n')[0]};}finally{await context.close();}
        }
        // Accepted reference pages compare to independent saved BEFORE text/metadata,
        // with dynamic bank content excluded by the same safe snapshot helper.
        const before=beforeData();
        for(const route of referencePaths){const row={path:route,pass:false},context=await browser.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'}),page=await context.newPage();report.references.push(row);
            try {await observe(context,page,row);const response=await page.goto(BASE+route,{waitUntil:'networkidle'});assert.equal(response.status(),200);await page.waitForFunction(()=>Boolean(window.Alpine));
                const dom=new JSDOM(await page.content());let snapshot;try{snapshot=beforeHelper.domSummary(dom.window.document,'[data-theory-main]');}finally{dom.window.close();} const old=before.rows.find(r=>r.path===route&&r.viewport.width===1440&&r.theme==='light');assert.ok(old);assert.deepEqual(snapshot.learner,old.dom.learner,'Protected reference learning unchanged');assert.deepEqual(snapshot.metadata,old.dom.metadata,'Protected reference metadata unchanged');
                row.textSha256=sha(snapshot.learner.text);row.metadataUnchanged=true;row.screenshot=await shot(page,dir,label+'-reference-'+route.split('/').at(-1)+'.png',true);clean(row);row.pass=true;
            }catch(error){row.failure={name:error.name,message:error.message.split('\n')[0]};}finally{await context.close();}}
        assert.deepEqual(sourceHashes(),report.sourceBefore);report.pass=[...report.noJS,...report.responsive,...report.references].every(row=>row.pass);
    }finally{await browser.close();report.finishedAt=new Date().toISOString();save(dir,file,report);}return report;
}
function dryContract(){
    assert.deepEqual(targets.map(t=>t.path),['/theory/maibutni-formy/future-simple/will-vs-be-going-to','/theory/maibutni-formy/present-continuous-for-future','/theory/maibutni-formy/choosing-the-right-future-form']);
    const ids=new Set(),counts={tasks:0,controls:0,details:0,tokens:0};
    for(const t of targets){assert.equal(t.lesson.practice.length,6);for(const p of t.lesson.sections.flatMap(s=>s.points))if(p.detail)counts.details++;
        for(const task of t.lesson.practice){counts.tasks++;for(const c of task.controls){assert.ok(!ids.has(c.id));ids.add(c.id);counts.controls++;assert.equal(c.required,true);if(isManual(c)){canonicalTokenOrder(c);counts.tokens++;}}}}
    assert.deepEqual({...counts},{tasks:18,controls:34,details:7,tokens:12});
    const before=beforeData(),reference=referenceData();assert.equal(before.rows.length,28);
    assert.deepEqual(reference.rows.map(r=>r.pass),Array(16).fill(true));
    return {mode:'dry-contract',noHTTP:true,noDB:true,masterSha256:MASTER_SHA,beforeSha256:BEFORE_SHA,referenceSha256:REFERENCE_SHA,...counts,pass:true};
}
if(require.main===module){const[mode,dir,label]=process.argv.slice(2);assert.ok(['--pages','--supplemental','--dry-contract'].includes(mode),'Explicit acceptance mode required');
    if(mode==='--dry-contract')console.log(JSON.stringify(dryContract()));
    else (mode==='--pages'?pages(dir,label):supplemental(dir,label)).then(report=>{if(!report.pass)process.exitCode=1;}).catch(error=>{console.error(error.stack);process.exitCode=1;});}
module.exports={BASE,MASTER_PATH,MASTER_SHA,BEFORE_FILE,BEFORE_SHA,REFERENCE_FILE,REFERENCE_SHA,PRACTICE_COMPONENT_SELECTOR,master,targets,uiCases,allowedRequest,safeUrl,norm,onceOrdered,cellText,examplePairs,pointFidelity,feedbackFidelity,authorFidelity,sourceHashes,pages,supplemental,canonicalTokenOrder,isManual,dryContract,
    practiceQA,detailsQA,layoutQA,tocAndReload,compareStyles,finiteCaptionExpectation,sourceReceiptProjection,approvedSourceProjection,assertServedSources};
