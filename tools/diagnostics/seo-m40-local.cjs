'use strict';
// GET-only real gramlyze.loc practice UI evidence; no app/DB/Git mutations.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const {htmlText, BASE} = require('./seo-m28-local.cjs');
const {observe, clean} = require('./seo-m39-local.cjs');
const ROOT = path.resolve(__dirname, '../..');
const read = name => JSON.parse(fs.readFileSync(path.join(ROOT, name), 'utf8'));
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value || '').replace(/\s+/gu, ' ').trim();
const master = read('docs/content/m24-authored-content.v1.json');
const snapshot = read('database/content-patches/m40-m24-tenses-b1.v1.json');
const targets = snapshot.targets.map((target, index) => ({...target,
    slug: target.after.slug, definitionPath: target.path, path: master.lessons[index].theory_path,
    data: JSON.parse(target.after.page.blocks.find(block => block.type === 'practice-set').body)}));
const sourceHashes = () => Object.fromEntries(['database/content-patches/m40-m24-tenses-b1.v1.json',
    'public/js/authored-practice-ui.js', 'public/js/m40-practice-ui.js',
    'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php', 'resources/views/engram/theory/blocks-v3/m40-practice-ui.blade.php']
    .map(file => [file, sha(fs.readFileSync(path.join(ROOT, file)))]));
const answerFragments = {
    'm40-p1-oleh': ['has packed', 'has been packing'], 'm40-p1-marta': ['has packed', 'has been packing'],
    'm40-p2-duration': ['for', 'since'], 'm40-p2-start': ['for', 'since'],
    'm40-p4-contradiction': ['Ні', 'Так'], 'm40-p4-completion': ['Невідомо', 'Так', 'Ні'],
    'm40-p5-natural': ['Так', 'Ні'], 'm40-p5-intention': ['Ні', 'Так'],
    'm40-n2-process': ['was sorting', 'had sorted'], 'm40-n2-arrival': ['arrived', 'was arriving'],
    'm40-n2-stopped': ['Не сказано', 'Так', 'Ні'], 'm40-n4-order': ['Так', 'Ні'],
    'm40-b1-written': ['have written', 'wrote'], 'm40-b1-prepared': ['had prepared', 'prepared'],
    'm40-b2-process': ['will be packing', 'will pack'], 'm40-b2-completion': ['will have finished', 'will finish'],
};
function assertOptionOnly(label, intended, key) {
    assert.equal(norm(label), norm(intended), 'Visible candidate is exactly its intended answer fragment');
    assert.doesNotMatch(label, /<[^>]+>/u, 'No raw HTML in a selectable candidate');
    assert.notEqual(norm(label), htmlText(key), 'Full author key must not become an option');
    const dom = new JSDOM('<div>' + key + '</div>');
    try {
        for (const english of dom.window.document.querySelectorAll('[lang="en"]')) {
            const answer = norm(english.textContent);
            assert.ok(!norm(label).startsWith(answer + ' — '), 'Answer plus translation/rationale leaked into option');
        }
    } finally {dom.window.close();}
}
function uiData(target) { return target.data; }
async function caseLocator(page, task) { return page.locator('[data-m40-ui-case="' + task.source_index + '"]'); }
function field(card, control) { return card.locator('[data-m40-control="' + control.id + '"]'); }
function option(fieldLocator, control, value) {
    const item = control.options.find(candidate => candidate.value === value); assert.ok(item, 'Declared candidate exists');
    return fieldLocator.getByRole(control.kind === 'multi' ? 'checkbox' : 'radio', {name: item.label, exact: true});
}
async function showCard(card) { await card.scrollIntoViewIfNeeded(); }
async function shot(card, dir, name) {
    const file = path.join(dir, name); assert.ok(!fs.existsSync(file), 'Exclusive screenshot');
    const page = card.page(), original = page.viewportSize();
    const height = await card.evaluate(node => node.getBoundingClientRect().height);
    // Keep the tested width, but capture the bounded case rather than cutting
    // a tall open answer/key behind the sticky header. Never hide page UI.
    if (height + 140 > original.height) await page.setViewportSize({width: original.width, height: Math.ceil(height) + 160});
    try {
        await card.evaluate(node => window.scrollTo(0, node.getBoundingClientRect().top + scrollY - 110));
        await card.screenshot({path: file, animations: 'disabled'});
    } finally {if (page.viewportSize().height !== original.height) await page.setViewportSize(original);}
    return name;
}
async function pageReady(page, target, row, violations, viewport) {
    await observe(page, row, violations);
    assert.equal(new URL(BASE + target.path).origin, BASE);
    const response = await page.goto(BASE + target.path, {waitUntil: 'networkidle'});
    assert.equal(response.status(), 200);
    await page.waitForFunction(() => Boolean(window.Alpine && document.querySelector('[data-m40-practice-ui] [x-data]')));
    await page.evaluate(() => {document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light');});
    await page.waitForFunction(() => {
        const node = document.querySelector('[data-m40-practice-ui] [x-data]');
        return window.Alpine.$data(node)?.cases?.length === 6;
    });
    assert.equal(await page.locator('[data-m40-ui-case]').count(), 6);
    assert.deepEqual(await page.locator('[data-m40-ui-case]').evaluateAll(nodes => nodes.map(node => Number(node.dataset.m40UiCase))), [1,2,3,4,5,6]);
    row.viewport = viewport; row.theme = 'light';
}
async function verifyCase(page, target, task, initial = true) {
    const card = await caseLocator(page, task);
    assert.equal(await card.getAttribute('data-m40-ui-interaction'), task.interaction);
    assert.equal(htmlText(await card.locator('[data-m40-author-prompt]').innerHTML()),
        htmlText(target.data.author_self_check.prompts[task.source_index - 1]), 'Exact original prompt');
    const key = target.data.author_self_check.answers[task.source_index - 1];
    const explanation = card.locator('[data-m40-ui-explanation]');
    const keyNode = explanation.locator('[data-m40-self-check-answer]');
    assert.equal(await keyNode.count(), 1);
    assert.equal(htmlText(await keyNode.innerHTML()), htmlText(key), 'Exact full original author key');
    assert.equal(await keyNode.evaluate(node => getComputedStyle(node).textTransform), 'none');
    if (initial) {
        assert.equal(await explanation.evaluate(node => node.open), false, 'Key is closed before check');
        assert.equal(await keyNode.isVisible(), false, 'Explanation is not a visible candidate');
        assert.equal(await explanation.locator(':scope > summary').isVisible(), false);
    }
    const controls = [];
    for (const control of task.controls) {
        const area = field(card, control); assert.equal(await area.count(), 1);
        assert.equal(await area.getAttribute('data-m40-control-kind'), control.kind);
        const result = {id: control.id, kind: control.kind, labels: [], naturalCasing: true, explanationSeparate: true};
        if (control.kind === 'manual') {
            assert.equal(await area.locator('textarea').getAttribute('autocomplete'), 'off');
            assert.equal(await area.locator('textarea').evaluate(node => getComputedStyle(node).textTransform), 'none');
            const tokens = area.locator('[data-m40-token]'); assert.equal(await tokens.count(), control.tokens.length);
            const labels = (await tokens.allTextContents()).map(norm);
            assert.deepEqual(labels.slice().sort(), control.tokens.slice().sort(), 'Only original answer tokens');
            assert.ok(labels.every(label => label.split(/\s+/u).length <= 3));
            for (const token of await tokens.all()) assert.equal(await token.evaluate(node => getComputedStyle(node).textTransform), 'none');
            result.tokens = labels.length;
        } else {
            const candidates = area.locator('[data-m40-answer]');
            assert.equal(await candidates.count(), control.options.length);
            result.labels = (await candidates.allTextContents()).map(norm);
            assert.deepEqual(result.labels, answerFragments[control.id], 'Finite source answer fragments, not key/rationale payloads');
            for (const [index, candidate] of (await candidates.all()).entries()) {
                assertOptionOnly(result.labels[index], control.options[index].label, key);
                assert.equal(await candidate.evaluate(node => getComputedStyle(node).textTransform), 'none', 'No ALL CAPS normalization');
                assert.ok(await candidate.isVisible()); assert.equal(await candidate.getAttribute('data-m40-answer'), control.options[index].value);
                assert.equal(await candidate.evaluate(node => Math.max(0, node.scrollWidth - node.clientWidth)), 0, 'Candidate wraps rather than clips');
            }
        }
        assert.equal(await area.evaluate(node => Math.max(0, node.scrollWidth - node.clientWidth)), 0);
        controls.push(result);
    }
    assert.equal(await card.evaluate(node => Math.max(0, node.scrollWidth - node.clientWidth)), 0);
    return {source_index: task.source_index, interaction: task.interaction, controls,
        promptExact: true, authorKeyExact: true, initiallyHidden: initial, naturalCasing: true};
}
async function fillCorrect(card, task) {
    for (const control of task.controls) {
        const area = field(card, control);
        if (control.kind === 'manual') await area.locator('textarea').fill(control.answer);
        else for (const value of control.kind === 'multi' ? control.answer : [control.answer]) await option(area, control, value).click();
    }
}
async function resetCase(card, task) {
    await card.locator('[data-m40-reset]').click();
    const details = card.locator('[data-m40-ui-explanation]');
    await details.locator('[data-m40-self-check-answers]').waitFor({state: 'hidden'});
    await card.locator('[data-m40-case-feedback]').waitFor({state: 'hidden'});
    assert.equal(await details.evaluate(node => node.open), false);
    assert.equal(await details.locator('[data-m40-self-check-answers]').isVisible(), false);
    for (const control of task.controls) {
        const area = field(card, control);
        if (control.kind === 'manual') {
            assert.equal(await area.locator('textarea').inputValue(), '');
            assert.equal(await area.locator('[data-m40-token][disabled]').count(), 0);
        } else assert.equal(await area.locator('[aria-checked="true"]').count(), 0);
    }
    assert.equal(await card.locator('[data-m40-case-feedback]').isVisible(), false);
}
async function checkedState(card, correct) {
    await card.locator('[data-m40-case-feedback]').waitFor({state: 'visible'});
    assert.equal(norm(await card.locator('[data-m40-case-feedback]').textContent()), correct ? 'Правильно' : 'Не всі частини відповіді правильні');
    assert.equal(await card.locator('[data-m40-ui-explanation]').evaluate(node => node.open), true);
    assert.equal(await card.locator('[data-m40-self-check-answers]').isVisible(), true);
    for (const part of await card.locator('[data-m40-part-feedback]').all()) assert.ok(await part.isVisible());
}
async function tokenProof(card, task) {
    let count = 0;
    for (const control of task.controls.filter(control => control.kind === 'manual')) {
        const area = field(card, control), input = area.locator('textarea'), tokens = area.locator('[data-m40-token]');
        const first = tokens.first(); await first.click(); assert.equal(await first.isDisabled(), true);
        await input.press('ControlOrMeta+A'); await input.press('Backspace'); assert.equal(await first.isEnabled(), true);
        let remaining = control.answer;
        const used = new Set();
        while (remaining) {
            const labels = (await tokens.allTextContents()).map(norm);
            const index = labels.findIndex((label, i) => !used.has(i) && remaining.startsWith(label));
            assert.ok(index >= 0, 'Canonical answer constructible by tokens');
            await tokens.nth(index).click(); used.add(index); remaining = remaining.slice(labels[index].length).trimStart();
        }
        assert.equal(await input.inputValue(), control.answer);
        assert.equal(await tokens.locator('xpath=self::*[not(@disabled)]').count(), 0);
        count++;
    }
    return count;
}
async function keyboardProof(card, task) {
    const controls = [];
    for (const control of task.controls.filter(c => c.kind !== 'manual')) {
        const area = field(card, control);
        if (control.kind === 'multi') {
            const checkbox = option(area, control, control.answer[0]); await checkbox.focus(); await checkbox.press('Space');
            assert.equal(await checkbox.getAttribute('aria-checked'), 'false'); await checkbox.press('Space');
            assert.equal(await checkbox.getAttribute('aria-checked'), 'true'); controls.push({id: control.id, spaceToggle: true});
        } else {
            const selected = option(area, control, control.answer), index = control.options.findIndex(o => o.value === control.answer);
            await selected.focus(); await selected.press('ArrowRight');
            const next = option(area, control, control.options[(index + 1) % control.options.length].value);
            assert.equal(await next.getAttribute('aria-checked'), 'true'); await next.press('ArrowLeft');
            assert.equal(await selected.getAttribute('aria-checked'), 'true');
            assert.equal(await selected.evaluate(node => node.matches(':focus-visible')), true);
            controls.push({id: control.id, arrowCycle: true, focusVisible: true});
        }
    }
    const manual = task.controls.find(control => control.kind === 'manual');
    if (manual) {
        const input = field(card, manual).locator('textarea'); await input.focus(); await input.press('Control+Enter');
        assert.equal(await input.evaluate(node => node.matches(':focus-visible')), true);
        return {kind: 'Ctrl+Enter', focusVisible: true, controls};
    }
    const control = task.controls.find(c => c.kind !== 'multi');
    const selected = option(field(card, control), control, control.answer);
    await selected.focus(); await selected.press('Space');
    assert.equal(await selected.getAttribute('aria-checked'), 'true');
    const check = card.locator('[data-m40-check]'); await check.focus(); await check.press('Enter');
    assert.equal(await check.evaluate(node => node.matches(':focus-visible')), true);
    return {kind: 'Space/Enter', focusVisible: true, controls};
}
async function acceptanceCase(page, target, task, dir, stem) {
    const card = await caseLocator(page, task);
    const row = await verifyCase(page, target, task, true);
    await fillCorrect(card, task); row.keyboard = await keyboardProof(card, task); await checkedState(card, true);
    row.correct = true;
    row.postCorrect = await shot(card, dir, stem + '-post-correct.png');
    await resetCase(card, task);
    row.tokenManual = await tokenProof(card, task);
    if (row.tokenManual) { // Token-only construction must still answer every non-manual subpart.
        for (const control of task.controls.filter(c => c.kind !== 'manual'))
            for (const value of control.kind === 'multi' ? control.answer : [control.answer]) await option(field(card, control), control, value).click();
        await card.locator('[data-m40-check]').click(); await checkedState(card, true); await resetCase(card, task);
    }
    await fillCorrect(card, task);
    const first = task.controls[0], area = field(card, first);
    if (first.kind === 'manual') await area.locator('textarea').fill('wrong answer');
    else {
        const wrong = first.options.find(o => first.kind === 'multi' ? !first.answer.includes(o.value) : o.value !== first.answer);
        assert.ok(wrong); await option(area, first, wrong.value).click();
    }
    await card.locator('[data-m40-check]').click(); await checkedState(card, false); row.wrong = true;
    row.postWrong = await shot(card, dir, stem + '-post-wrong.png');
    await resetCase(card, task); row.reset = true;
    for (const control of task.controls.filter(c => c.kind === 'manual')) {
        for (const alias of control.accepted || [control.answer]) {
            await fillCorrect(card, task); await field(card, control).locator('textarea').fill(alias.replace(/[.!?]+$/u, ''));
            await card.locator('[data-m40-check]').click(); await checkedState(card, true); await resetCase(card, task);
        }
    }
    row.acceptedVariants = true; row.terminalPunctuationOptional = true; return row;
}
async function measure(page) {
    const data = await page.locator('[data-theory-main]').evaluate(node => ({main: Math.max(0, node.scrollWidth - node.clientWidth),
        document: Math.max(0, document.documentElement.scrollWidth - innerWidth)}));
    assert.equal(data.main, 0); return data;
}
function learnerStrings(value, key = '') {
    if (Array.isArray(value)) return value.flatMap(item => learnerStrings(item, key));
    if (value && typeof value === 'object') return Object.entries(value).flatMap(([name,item]) => learnerStrings(item,name));
    return typeof value === 'string' && !['color','level','url','icon','uuid','key'].includes(key) ? [htmlText(value)] : [];
}
function assertNative(doc, target) {
    const index = targets.findIndex(item => item.identity === target.identity), lesson = master.lessons[index];
    assert.equal(index >= 0, true);
    assert.deepEqual([...doc.querySelectorAll('h1')].map(node => norm(node.textContent)), [lesson.preserve_subtitle_strong]);
    const main = doc.querySelector('[data-theory-main]'); assert.ok(main);
    const copy = main.cloneNode(true);copy.querySelectorAll('details,noscript,script,style').forEach(node=>node.remove());
    const text = htmlText(copy.innerHTML);
    const expectedTitles=[],footerTitles=[];
    for (const original of lesson.existing_blocks) {
        const data = original.replacement_body_json;
        for (const fragment of learnerStrings(data)) assert.ok(text.includes(fragment), 'Visible native source fragment '+fragment);
        if (data.title) {
            (original.preserve_type==='navigation-chips'?footerTitles:expectedTitles).push(data.title);
            const selector=original.preserve_type==='navigation-chips'?'p':'.theory-section-header h2';
            const headings = [...main.querySelectorAll(selector)].filter(node => {
                const copy = node.cloneNode(true); copy.querySelectorAll('[data-theory-ui]').forEach(child => child.remove());
                return norm(copy.textContent) === htmlText(data.title);
            });
            assert.equal(headings.length,1,'Original native heading/number occurs once '+data.title);
        }
        if (original.preserve_type === 'navigation-chips') for (const item of data.items) {
            const selector=item.current?'nav span':'a';
            assert.ok([...main.querySelectorAll(selector)].some(link => (item.current||link.getAttribute('href')?.endsWith(item.url))
                && norm(link.textContent) === norm(item.label)), 'Original related '+(item.current?'current chip ':'link ')+item.label);
        }
    }
    for(const [j,plan] of target.plans.entries()) {
        const data=JSON.parse(target.after.page.blocks[target.native_count+j].body);expectedTitles.push(plan.source_title);
        if(plan.type!=='practice-set') {
            assert.ok(text.includes(htmlText(data.intro)),'Full pre-practice paragraphs remain immediately visible');
            for(const point of plan.points||[]) {
                assert.ok(text.includes(htmlText(point.basic)),'Complete appended author basic remains visible');
                assert.equal(point.detail,'','Finite M40 semantic detail audit is basic');
            }
        }
    }
    const actualTitles=[...main.querySelectorAll('.theory-section-header h2')].map(node=>{const c=node.cloneNode(true);c.querySelectorAll('[data-theory-ui]').forEach(n=>n.remove());return norm(c.textContent);});
    assert.deepEqual(actualTitles,expectedTitles.map(htmlText),'Content headings retain original source numbers and order');
    assert.equal(main.querySelectorAll('[data-theory-native-extension] > details').length,0);
    const ids=[...main.querySelectorAll('[id]')].map(node=>node.id);
    assert.equal(ids.length,new Set(ids).size,'Unique original/appended anchors');
    return {h1:lesson.preserve_subtitle_strong,nativeBlocks:lesson.existing_blocks.length,totalBlocks:target.after.page.blocks.length,
        headings:actualTitles,footerTitles,sourceVisible:true,uniqueAnchors:true};
}
async function bankProof(page, target, dir) {
    const inventory=readPrivate(dir,'m40-bank-inventory-v1.json');
    const owner=inventory.targets.find(item=>item.slug===target.slug); assert.ok(owner,'Actual M40 bank inventoried');
    const data=target.data.linked_practice, expected=data.seeder_classes[0];
    const widget=page.locator('[data-m40-practice-ui] [data-sentence-builder]'); assert.equal(await widget.count(),1);
    const pool=await widget.evaluate(node=>{const state=window.Alpine.$data(node);return {ids:state.questions.map(q=>q.id),types:[...new Set(state.questions.map(q=>String(q.type)))]};});
    const allowed=owner.linked_bank_ids?.[expected]; assert.ok(allowed?.length,'Exact linked primary bank exists');
    assert.ok(pool.ids.length>0); assert.equal(new Set(pool.ids).size,pool.ids.length);
    assert.ok(pool.ids.every(id=>allowed.includes(id)),'Every linked question belongs to the exact actual primary bank');
    assert.deepEqual(pool.types,['4']);
    return {seeder:expected,ownBank:allowed.length,displayed:pool.ids.length,exactPrimaryBank:true,type:'4'};
}
function readPrivate(dir,name) {return JSON.parse(fs.readFileSync(path.join(dir,name),'utf8'));}
function beforeHttp(dir) {
    const accepted=process.env.M40_BEFORE_HTTP||'before-v3-http.json';
    assert.match(accepted,/^before(?:-v\d+)?-http\.json$/u);
    const report=readPrivate(dir,accepted);assert.equal(report.pass,true);return report;
}
async function pageStates(dir,label) {
    assert.equal(path.basename(dir),'seo-m40-local'); assert.match(label,/^[a-z0-9-]+$/u);
    const file=path.join(dir,label+'-pages.json'); assert.ok(!fs.existsSync(file));
    const baseline=beforeHttp(dir);
    const {chromium}=require('playwright'), legacy=require('./seo-m39-local.cjs');
    const report={at:new Date().toISOString(),base:BASE,sourceHashes:sourceHashes(),rows:[],violations:[],pass:false};
    const browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});
    try {
        for(const viewport of [{width:1440,height:1000},{width:390,height:844}]) for(const theme of ['light','dark']) for(const target of targets) {
            const context=await browser.newContext({viewport,colorScheme:theme}), page=await context.newPage();
            const row={path:target.path,viewport,theme,errors:[],httpErrors:[],failed:[],screenshots:[]};report.rows.push(row);
            try {
                await pageReady(page,target,row,report.violations,viewport);
                await page.evaluate(theme=>{document.documentElement.classList.toggle('dark',theme==='dark');localStorage.setItem('theme',theme);},theme);row.theme=theme;
                const dom=new JSDOM(await page.content());try{row.content=assertNative(dom.window.document,target);}finally{dom.window.close();}
                row.overflow=await legacy.overflow(page); row.bank=await bankProof(page,target,dir);
                const stem=label+'-'+target.slug+'-'+viewport.width+'-'+theme;
                await page.evaluate(()=>window.scrollTo(0,0));
                const top=stem+'-top.png';assert.ok(!fs.existsSync(path.join(dir,top)));await page.screenshot({path:path.join(dir,top),fullPage:false,animations:'disabled'});row.screenshots.push(top);
                for(const [i,table] of (await page.locator('.theory-table-scroll').all()).entries()) {
                    await table.scrollIntoViewIfNeeded();const filename=stem+'-table-'+(i+1)+'.png';
                    assert.ok(!fs.existsSync(path.join(dir,filename)));await page.screenshot({path:path.join(dir,filename),fullPage:false,animations:'disabled'});row.screenshots.push(filename);
                    if(await table.evaluate(node=>node.scrollWidth>node.clientWidth)) {
                        await table.focus();assert.equal(await table.evaluate(node=>document.activeElement===node),true,'Actual scrolling region keyboard focus');
                        for(let key=0;key<14;key++)await page.keyboard.press('ArrowRight');
                        await page.waitForFunction(()=>{const node=document.querySelector('.theory-table-scroll');return node.scrollLeft>=node.scrollWidth-node.clientWidth-1;});
                        await table.scrollIntoViewIfNeeded();
                        const right=stem+'-table-'+(i+1)+'-right.png';await shot(table,dir,right);row.screenshots.push(right);
                        for(let key=0;key<14;key++)await page.keyboard.press('ArrowLeft');
                        await page.waitForFunction(()=>document.querySelector('.theory-table-scroll').scrollLeft===0);
                    }
                }
                const sourceLegacy=baseline.rows.find(item=>item.path===target.path).legacyIds;
                for(const id of sourceLegacy) assert.equal(await page.locator('[id="'+id+'"]').count(),1);
                const deep=[];
                for(const id of sourceLegacy) {
                    await page.goto(BASE+target.path+'#'+id,{waitUntil:'networkidle'});assert.equal(await page.evaluate(()=>decodeURIComponent(location.hash.slice(1))),id);
                    assert.equal(await page.locator('[id="'+id+'"]').count(),1);deep.push(id);
                }
                await page.emulateMedia({media:'print'}); assert.equal(await page.locator('[data-theory-native-extension] > details').count(),0);
                await page.emulateMedia({media:'screen'});await page.reload({waitUntil:'networkidle'});
                assert.equal(await page.locator('[data-m40-ui-explanation][open]').count(),0);
                row.navigation={legacyAnchors:true,deepLinks:deep,print:true,reloadStartsClosed:true};clean(row);row.pass=true;
                console.log(JSON.stringify({path:target.path,width:viewport.width,theme,pass:true}));
            } finally {await context.close();}
        }
        assert.equal(report.rows.length,12);assert.deepEqual(report.violations,[]);report.pass=true;
    } finally {await browser.close();fs.writeFileSync(file,JSON.stringify(report,null,2),{flag:'wx'});}
    return report;
}
async function noJSPage(browser,target,dir,label,prefix,report) {
    const context=await browser.newContext({javaScriptEnabled:false,viewport:{width:390,height:844}}),page=await context.newPage();
    const row={path:target.path,javaScriptEnabled:false,errors:[],httpErrors:[],failed:[],expectedDisabledScripts:[],keys:[]};report.noJS.push(row);
    try {
        await observe(page,row,report.violations);const response=await page.goto(BASE+target.path,{waitUntil:'networkidle'});assert.equal(response.status(),200);
        assert.equal(await page.locator('[data-'+prefix+'-ui-case]').count(),6);
        if(prefix==='m40'){const dom=new JSDOM(await page.content());try{row.content=assertNative(dom.window.document,target);}finally{dom.window.close();}}
        for(const task of target.data.cases){
            const card=page.locator('[data-'+prefix+'-ui-case="'+task.source_index+'"]');
            const details=card.locator('[data-'+prefix+'-ui-explanation]'),key=card.locator('[data-'+prefix+'-self-check-answer]');
            assert.equal(htmlText(await card.locator('[data-'+prefix+'-author-prompt]').innerHTML()),htmlText(target.data.author_self_check.prompts[task.source_index-1]));
            assert.equal(htmlText(await key.innerHTML()),htmlText(target.data.author_self_check.answers[task.source_index-1]));
            assert.equal(await key.isVisible(),false);const summary=details.locator(':scope > summary');
            await summary.click();assert.equal(await key.isVisible(),true);await summary.focus();await summary.press('Space');assert.equal(await details.evaluate(node=>node.open),false);
            await summary.press('Enter');assert.equal(await key.isVisible(),true);await summary.click();
            row.keys.push({source_index:task.source_index,nativeMouseSpaceEnter:true,exactPromptKey:true});
        }
        row.overflow=await measure(page);clean(row);row.pass=true;
    } finally {await context.close();}
    return row;
}
async function mobileTableQA(dir,label) {
    assert.equal(path.basename(dir),'seo-m40-local');assert.match(label,/^[a-z0-9-]+$/u);
    const output=path.join(dir,label+'-tables.json');assert.ok(!fs.existsSync(output));
    const {chromium}=require('playwright');
    const report={at:new Date().toISOString(),base:BASE,sourceHashes:sourceHashes(),rows:[],violations:[],pass:false,
        priorCaptureLimitation:'pages-v3 End/programmatic scrollLeft proves neither actual keyboard focus nor reliable right-column screenshot'};
    const browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});
    try {
        for(const theme of ['light','dark']) for(const target of targets.slice(0,2)) {
            const context=await browser.newContext({viewport:{width:390,height:844},colorScheme:theme}),page=await context.newPage();
            const row={path:target.path,theme,viewport:{width:390,height:844},errors:[],httpErrors:[],failed:[],keyboardPass:false};report.rows.push(row);
            try {
                await pageReady(page,target,row,report.violations,row.viewport);row.theme=theme;
                await page.evaluate(value=>{document.documentElement.classList.toggle('dark',value==='dark');localStorage.setItem('theme',value);},theme);
                const table=page.locator('.theory-table-scroll');assert.equal(await table.count(),1);
                await table.scrollIntoViewIfNeeded();
                row.before=await table.evaluate(node=>({scrollLeft:node.scrollLeft,clientWidth:node.clientWidth,scrollWidth:node.scrollWidth,attributeTabindex:node.getAttribute('tabindex'),propertyTabIndex:node.tabIndex}));
                assert.equal(row.before.scrollLeft,0);assert.ok(row.before.scrollWidth>row.before.clientWidth);
                await table.focus();
                row.focus=await table.evaluate(node=>({actual:document.activeElement===node,activeTag:document.activeElement?.tagName,
                    activeId:document.activeElement?.id||null,focusVisible:node.matches(':focus-visible')}));
                await page.keyboard.press('ArrowRight');
                try {await page.waitForFunction(()=>document.querySelector('.theory-table-scroll').scrollLeft>0,null,{timeout:2000});} catch { /* Exact failed keyboard result is evidence, not a silent pass. */ }
                row.afterFirstArrow=await table.evaluate(node=>node.scrollLeft);
                row.keyboardPass=row.focus.actual&&row.afterFirstArrow>row.before.scrollLeft;
                if(row.keyboardPass) {
                    for(let key=0;key<12;key++)await page.keyboard.press('ArrowRight');
                    await page.waitForFunction(()=>{const node=document.querySelector('.theory-table-scroll');return node.scrollLeft>=node.scrollWidth-node.clientWidth-1;},null,{timeout:3000});
                } else {
                    // Visual fallback is explicitly mouse-wheel evidence, never relabelled keyboard success.
                    await table.hover();await page.mouse.wheel(800,0);
                    await page.waitForFunction(()=>{const node=document.querySelector('.theory-table-scroll');return node.scrollLeft>=node.scrollWidth-node.clientWidth-1;},null,{timeout:3000});
                    row.visualScrollMethod='horizontal mouse wheel';
                }
                if(row.keyboardPass)row.visualScrollMethod='ArrowRight';
                await table.scrollIntoViewIfNeeded();
                row.final=await table.evaluate(node=>({scrollLeft:node.scrollLeft,clientWidth:node.clientWidth,scrollWidth:node.scrollWidth,
                    rect:{top:node.getBoundingClientRect().top,bottom:node.getBoundingClientRect().bottom}}));
                row.screenshot=await shot(table,dir,label+'-'+target.slug+'-'+theme+'-390-table-right.png');
                row.visibleTableCaptured=true;clean(row);
                console.log(JSON.stringify({path:target.path,theme,focus:row.focus.actual,arrowDelta:row.afterFirstArrow-row.before.scrollLeft,keyboardPass:row.keyboardPass,screenshot:row.screenshot}));
            } finally {await context.close();}
        }
        assert.equal(report.rows.length,4);assert.deepEqual(report.violations,[]);report.pass=report.rows.every(row=>row.keyboardPass&&row.visibleTableCaptured);
    } finally {await browser.close();fs.writeFileSync(output,JSON.stringify(report,null,2),{flag:'wx'});}
    return report;
}
async function supplemental(dir,label,beforeLabel) {
    assert.equal(path.basename(dir),'seo-m40-local');assert.match(label,/^[a-z0-9-]+$/u);
    const initial=readPrivate(dir,beforeLabel+'-visual.json');assert.equal(initial.pass,true);assert.deepEqual(initial.sourceHashes,sourceHashes());
    const output=path.join(dir,label+'-supplemental.json');assert.ok(!fs.existsSync(output));
    const {chromium}=require('playwright'), previous=require('./seo-m39-practice-ui.cjs');
    const report={at:new Date().toISOString(),base:BASE,sourceHashes:sourceHashes(),missingParts:[],semantic:[],noJS:[],m39:[],violations:[],pass:false};
    const browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});
    try {
        for(const target of targets) {
            const context=await browser.newContext({viewport:{width:1440,height:1000},colorScheme:'light'}),page=await context.newPage();
            const row={path:target.path,errors:[],httpErrors:[],failed:[],cases:[]};report.missingParts.push(row);
            try {
                await pageReady(page,target,row,report.violations,{width:1440,height:1000});
                for(const task of target.data.cases.filter(item=>item.controls.length>1)) {
                    const card=await caseLocator(page,task);
                    for(const [p,absent] of task.controls.entries()) {
                        for(const [part,control] of task.controls.entries()) if(part!==p) {
                            if(control.kind==='manual') await field(card,control).locator('textarea').fill(control.answer);
                            else await option(field(card,control),control,control.answer).click();
                        }
                        await card.locator('[data-m40-check]').click();await checkedState(card,false);
                        assert.equal(norm(await page.locator('[data-m40-ui-score]').textContent()),'Результат: 0 / 6');
                        await fillCorrect(card,task);await card.locator('[data-m40-check]').click();await checkedState(card,true);
                        row.cases.push({source_index:task.source_index,missing:absent.id,incompleteRejected:true,completeAccepted:true});await resetCase(card,task);
                    }
                }
                const semantic=require('./m40-semantic-fixtures.cjs').semanticFixtures(target);
                for(const fixture of semantic) {
                    const task=target.data.cases[fixture.caseIndex],control=task.controls[fixture.controlIndex],card=await caseLocator(page,task);
                    await fillCorrect(card,task);await field(card,control).locator('textarea').fill(fixture.invalid);
                    await card.locator('[data-m40-check]').click();await checkedState(card,false);
                    report.semantic.push({path:target.path,source_index:task.source_index,control:control.id,boundary:fixture.boundary,rejected:true});
                    await resetCase(card,task);
                }
                clean(row);row.pass=true;
            } finally {await context.close();}
            await noJSPage(browser,target,dir,label,'m40',report);
        }
        for(const target of previous.targets) {
            const context=await browser.newContext({viewport:{width:1440,height:1000},colorScheme:'light'}),page=await context.newPage();
            const row={path:target.path,errors:[],httpErrors:[],failed:[],cases:[]};report.m39.push(row);
            try {
                await previous.pageReady(page,target,row,report.violations,{width:1440,height:1000});
                for(const task of target.data.cases) row.cases.push(await previous.acceptanceCase(page,target,task,dir,label+'-m39-'+target.slug+'-'+task.source_index));
                clean(row);row.pass=true;
            } finally {await context.close();}
            await noJSPage(browser,target,dir,label,'m39',report);
        }
        const http=require('./capture-m40-http.cjs'),captured=await http.capture();http.compare(beforeHttp(dir),captured);
        fs.writeFileSync(path.join(dir,label+'-http.json'),JSON.stringify(captured,null,2),{flag:'wx'});
        report.http={rows:captured.rows.length,sitemap:captured.sitemap};
        assert.equal(report.missingParts.flatMap(row=>row.cases).length,26);
        assert.equal(report.m39.flatMap(row=>row.cases).length,18);assert.equal(report.noJS.length,6);
        assert.deepEqual(report.violations,[]);report.pass=true;
    } finally {await browser.close();fs.writeFileSync(output,JSON.stringify(report,null,2),{flag:'wx'});}
    return report;
}
async function run(dir, label, mode, beforeLabel) {
    if(mode==='--pages')return pageStates(dir,label);
    if(mode==='--mobile-table-qa')return mobileTableQA(dir,label);
    if(mode==='--supplemental')return supplemental(dir,label,beforeLabel);
    assert.equal(path.basename(dir), 'seo-m40-local'); assert.match(label, /^[a-z0-9-]+$/u);
    assert.ok(['--visual-before-acceptance', '--acceptance'].includes(mode));
    const initial = mode === '--visual-before-acceptance';
    const hashes = sourceHashes();
    if (!initial) {
        assert.match(beforeLabel || '', /^[a-z0-9-]+$/u);
        const before = JSON.parse(fs.readFileSync(path.join(dir, beforeLabel + '-visual.json'), 'utf8'));
        assert.equal(before.pass, true); assert.equal(before.caseStates, 36); assert.deepEqual(before.sourceHashes, hashes);
    }
    const output = path.join(dir, label + (initial ? '-visual.json' : '-acceptance.json'));
    assert.ok(!fs.existsSync(output));
    const {chromium} = require('playwright');
    const report = {at: new Date().toISOString(), base: BASE, mode, sourceHashes: hashes, rows: [], violations: [], pass: false};
    let browser;
    try {
        browser = await chromium.launch({headless: true, executablePath: process.env.CHROMIUM_EXECUTABLE});
        for (const viewport of [{width: 1440, height: 1000}, {width: 390, height: 844}]) for (const target of targets) {
            const context = await browser.newContext({viewport, colorScheme: 'light'}), page = await context.newPage();
            const row = {path: target.path, errors: [], httpErrors: [], failed: [], cases: []}; report.rows.push(row);
            try {
                await pageReady(page, target, row, report.violations, viewport);
                for (const task of target.data.cases) {
                    const stem = label + '-' + target.slug + '-' + viewport.width + '-case-' + task.source_index;
                    if (initial) {
                        const result = await verifyCase(page, target, task, true), card = await caseLocator(page, task);
                        result.pre = await shot(card, dir, stem + '-pre.png'); row.cases.push(result);
                    } else row.cases.push(await acceptanceCase(page, target, task, dir, stem));
                }
                if (!initial) {
                    for (const task of target.data.cases) {
                        const card = await caseLocator(page, task); await fillCorrect(card, task); await card.locator('[data-m40-check]').click();
                    }
                    assert.equal(norm(await page.locator('[data-m40-ui-score]').textContent()), 'Результат: 6 / 6');
                    for (const task of target.data.cases) await resetCase(await caseLocator(page, task), task);
                    assert.equal(norm(await page.locator('[data-m40-ui-score]').textContent()), 'Результат: 0 / 6');
                    row.scoreCorrectReset = true;
                }
                row.overflow = await measure(page); clean(row); row.pass = true;
                console.log(JSON.stringify({path: target.path, width: viewport.width, mode, cases: row.cases.length, pass: true}));
            } finally {await context.close();}
        }
        assert.deepEqual(report.violations, []); report.caseStates = report.rows.reduce((n, row) => n + row.cases.length, 0);
        assert.equal(report.caseStates, 36); report.pass = true;
    } finally {
        if (browser) await browser.close();
        fs.writeFileSync(output, JSON.stringify(report, null, 2), {flag: 'wx'});
    }
    console.log(JSON.stringify({pass: report.pass, mode, pages: report.rows.length, caseStates: report.caseStates}));
}
if (require.main === module) run(...process.argv.slice(2)).catch(error => {console.error(error.stack || error); process.exitCode = 1;});
module.exports = {targets, master, snapshot, uiData, answerFragments, assertOptionOnly, sourceHashes, verifyCase, run,
    acceptanceCase, pageReady, resetCase, fillCorrect, checkedState, measure, field, caseLocator, option, shot,
    assertNative,learnerStrings,pageStates,supplemental,noJSPage,mobileTableQA};
