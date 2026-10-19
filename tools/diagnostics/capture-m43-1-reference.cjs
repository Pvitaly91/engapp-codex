'use strict';
// Presentation-only, fresh-guest evidence. Only GET gramlyze.loc and the
// reference's Google Fonts Archivo/Manrope resources may leave the browser.
// No Laravel bootstrap, DB connection, application write, or production call.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto'), assert = require('node:assert/strict');
const { chromium } = require('playwright');
const { JSDOM } = require('jsdom');
const BASE = 'http://gramlyze.loc', SERVED = 'D:/DEV/htdocs/gramlyze.loc';
const PRIVATE = path.resolve(SERVED, 'storage/app/seo-m43-1-local');
const EXPECTED_SHA='43a6c7bf701652c956221ffacadea91eb58af1b62ebb8671c620602323e08c60';
const PRACTICE_EXPECTED_SHA='7e03c0674d27c650fdbe68bbf7386960b0d2cff9b3a8d872f9e7804b0bfcc17c';
const FEEDBACK_EXPECTED_SHA='9c59dd243dd385d943296edb0926918f272b6ad84ef5d58b70ea3a5dfdc7a652';
const CONTROLS_EXPECTED_SHA='a1c8a6ce97c0df3b75d6e6ce061bf56cdb8083a470da89c6512423c0489659b1';
let expectedCache;
let practiceExtensionCache;
let controlsExtensionCache;
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const targets = [
    { key: 'reference', path: '/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms' },
    { key: 'A', path: '/theory/tenses/past-perfect-vs-past-perfect-continuous' },
    { key: 'B', path: '/theory/tenses/stative-verbs' },
    { key: 'C', path: '/theory/tenses/used-to-would' },
];
const properties = ['font-family','font-size','font-style','font-weight','line-height','color','background-color','background-image',
    'border-top','border-right','border-bottom','border-left','border-inline-start','border-radius','box-shadow',
    'padding-top','padding-right','padding-bottom','padding-left','margin-top','margin-right','margin-bottom','margin-left',
    'gap','row-gap','column-gap','display','text-decoration-line','text-transform','letter-spacing','white-space','overflow-wrap',
    'overflow-x','overflow-y','width','min-width','max-width','box-sizing'];
const roles = [
    ['outer-card', '.theory-section-card', 2], ['section-header', '.theory-section-header', 2],
    ['section-title', '.theory-section-title', 2], ['section-number', '.theory-section-number', 2],
    ['section-body', '.theory-section-card > .theory-section-body', 2],
    ['usage-panel', '.theory-native-block:has(.theory-item > div > div > .rounded-full) .theory-item, [data-m43-native-layout="usage-panels"] .theory-item', 4],
    ['usage-label', '.theory-item .uppercase', 4], ['usage-marker', '.theory-item .rounded-full', 4],
    ['forms-panel', '.theory-native-block:has(code.theory-example) .theory-item, [data-m43-native-layout="forms-grid"] .theory-item', 4],
    ['example-box', '.theory-example:not(.theory-example--wrong):not(.theory-example--right)', 6],
    ['example-en', '.theory-example p[lang="en"]:not([data-theory-native-extension] p):not(.theory-example--wrong p):not(.theory-example--right p), .theory-example p.font-mono:not([data-theory-native-extension] p)', 6],
    ['example-uk', '.theory-example .theory-translation', 6], ['example-note', '.m43-example-note, .theory-note', 3],
    ['example-icon', '.theory-example > span', 3], ['disclosure', '[data-theory-native-extension] > details', 2],
    ['disclosure-summary', '[data-theory-native-extension] > details > summary', 2],
    ['detail-body', '[data-theory-native-extension] .theory-section-detail-body', 2],
    ['detail-example-en', '[data-theory-native-extension] .theory-example p[lang="en"], [data-theory-native-extension] .theory-example p.font-mono', 3],
    ['detail-example-uk', '[data-theory-native-extension] .theory-translation', 3],
    ['table-container', '.theory-table-scroll', 2], ['table', '.theory-table-scroll table', 2],
    ['table-th', '.theory-table-scroll th', 3], ['table-td', '.theory-table-scroll td', 3],
    ['wrong-box', '.theory-example--wrong, .theory-item .bg-rose-50', 2],
    ['wrong-en', '.theory-example--wrong [lang="en"], .theory-item .bg-rose-50 .font-mono', 2],
    ['right-box', '.theory-example--right, .theory-item .bg-emerald-50', 2],
    ['right-en', '.theory-example--right [lang="en"], .theory-item .bg-emerald-50 .font-mono', 2],
    ['summary-rule', '[data-m43-native-layout="summary-list"] .group, .theory-section-card.bg-gradient-to-br .group', 3],
    ['practice-card', '.theory-exercise', 3], ['practice-header', '.theory-exercise > div:first-child', 3],
    ['practice-heading', '.theory-exercise h3, .theory-exercise [data-m43-author-prompt] h4', 3],
    ['practice-prompt', '.theory-exercise > .border-b > p:first-of-type, [data-m43-author-prompt] > p:first-of-type', 3],
    ['practice-context', '.theory-exercise > .border-b > p:nth-of-type(2), [data-m43-author-prompt] > p:nth-of-type(2)', 3],
    ['practice-control-panel', '.theory-exercise > .p-4 > .bg-white\\/60, .theory-exercise [data-m43-control-panel]', 4],
    ['practice-task-number', '.theory-exercise > .border-b h3 > span, [data-m43-author-prompt] h4 > [data-theory-ui]', 3],
    ['section-note', '.theory-note.text-sm.rounded-lg.p-3, [data-m43-author-section] > .theory-section-card > .theory-section-body > .theory-note', 4],
    ['practice-choice-initial', '.theory-exercise button.uppercase, .theory-exercise [data-m43-answer]', 3],
    ['practice-token-initial', '.theory-exercise button.text-emerald-700, .theory-exercise [data-m43-token]', 3],
    ['practice-input-initial', '.theory-exercise input[data-word-suggestion-input], .theory-exercise [data-m43-answer-input]', 2],
    ['practice-reset', '.theory-exercise button[x-show], .theory-exercise [data-m43-reset]', 1],
    ['practice-button', '.theory-exercise button', 3], ['practice-feedback', '[data-m43-case-feedback]', 3],
    ['answer-key-en', '[data-m43-self-check-answers] p[lang="en"]', 3],
    ['answer-key-uk', '[data-m43-self-check-answers] p[lang="uk"]', 3],
    ['usage-label-exact', '.theory-native-block:has(.theory-item > div > div > .rounded-full) .theory-item .uppercase:not(h3), [data-m43-native-layout="usage-panels"] .theory-item h3 > span', 4],
    ['usage-body', '.theory-native-block:has(.theory-item > div > div > .rounded-full) .theory-item > div > p, .theory-native-block:has(.theory-item > div > div > .rounded-full) .theory-item > div > .m42-rich-fragment, [data-m43-native-layout="usage-panels"] .theory-item > div > .m42-rich-fragment', 4],
    ['usage-content', '.theory-native-block:has(.theory-item > div > div > .rounded-full) .theory-item > div, [data-m43-native-layout="usage-panels"] .theory-item > div', 2],
    ['forms-panel-exact', '.theory-item.group.relative, [data-m43-native-layout="forms-grid"] .theory-item', 4],
    ['forms-label', '.theory-item.group.relative .uppercase, [data-m43-native-layout="forms-grid"] .theory-item .uppercase', 4],
    ['forms-formula', '.theory-item.group.relative h3, [data-m43-native-layout="forms-grid"] .theory-item h3', 4],
    ['forms-description', '.theory-item.group.relative .m42-rich-fragment, [data-m43-native-layout="forms-grid"] .theory-item .m41-form-description', 4],
    ['example-basic-box', '.theory-item > div > .space-y-2 > .theory-example, [data-m43-basic-point] .theory-example:not([data-theory-native-extension] .theory-example):not(.theory-example--wrong):not(.theory-example--right)', 4],
    ['detail-example-en-plain', '[data-theory-native-extension] .theory-example p:not(.theory-translation):not([lang="uk"])', 4],
    ['summary-text', '.theory-section-card.bg-gradient-to-br .group > span:nth-child(2), [data-m43-native-layout="summary-list"] .group > .m42-rich-fragment', 4],
];
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
function write(dir, file, value) { fs.writeFileSync(path.join(dir, file), JSON.stringify(value, null, 2) + '\n', { flag: 'wx' }); }
function allowed(url, method) {
    try { const u = new URL(url); if (method !== 'GET' || u.username || u.password) return false;
        return u.origin === BASE || (u.origin === 'https://fonts.googleapis.com' && u.pathname === '/css2' && /Archivo/u.test(u.searchParams.getAll('family').join(' ')) && /Manrope/u.test(u.searchParams.getAll('family').join(' ')))
            || (u.origin === 'https://fonts.gstatic.com' && /^\/s\/(?:archivo|manrope)\/[A-Za-z0-9/_-]+\.woff2$/u.test(u.pathname));
    } catch { return false; }
}
function safeUrl(url) { try { const u = new URL(url); return u.origin + u.pathname + (u.hostname === 'fonts.googleapis.com' ? u.search : ''); } catch { return '(unavailable)'; } }
function compactRule(rule, sheets) {
    return { origin: rule.origin, selector: rule.selectorList?.text, styleSheetId: rule.styleSheetId || null,
        source: sheets.get(rule.styleSheetId) || '(inline/user-agent)',
        declarationText:rule.style?.cssText||null,
        properties: (rule.style?.cssProperties || []).filter(p => properties.includes(p.name) || ['background','border','border-color','border-width','padding','margin','font'].includes(p.name) || p.name.startsWith('--theory-'))
            .map(p => ({ name: p.name, value: p.value, important: Boolean(p.important), implicit: Boolean(p.implicit), disabled: Boolean(p.disabled) })),
        range: rule.style?.range || null };
}
async function screenshot(page, dir, file, clip) {
    assert.ok(!fs.existsSync(path.join(dir,file)), 'Exclusive screenshot');
    await page.screenshot({path:path.join(dir,file), animations:'disabled', fullPage:true, ...(clip ? {clip} : {})});
    return {file,sha256:sha(fs.readFileSync(path.join(dir,file)))};
}
async function nodeScreenshot(node,dir,file){assert.ok(!fs.existsSync(path.join(dir,file)));await node.screenshot({path:path.join(dir,file),animations:'disabled'});return{file,sha256:sha(fs.readFileSync(path.join(dir,file)))};}
async function capture(browser, target, viewport, theme, dir, label) {
    const row = {at:new Date().toISOString(),key:target.key,path:target.path,viewport,theme,deviceScaleFactor:1,zoom:'Browser default 100%; no Ctrl+zoom performed',
        blocked:[],failed:[],httpErrors:[],consoleErrors:[],pageErrors:[],screenshots:[],components:[],pass:false};
    const context = await browser.newContext({viewport,colorScheme:theme,deviceScaleFactor:1,serviceWorkers:'block'});
    await context.addInitScript(value => { localStorage.setItem('theme',value); }, theme);
    const page = await context.newPage(), cdp = await context.newCDPSession(page), sheets = new Map();
    cdp.on('CSS.styleSheetAdded', e => sheets.set(e.header.styleSheetId, safeUrl(e.header.sourceURL || BASE)));
    await cdp.send('DOM.enable'); await cdp.send('CSS.enable');
    await context.route('**/*', route => {
        const request=route.request(); if(allowed(request.url(),request.method())) { const headers={...request.headers()}; delete headers.cookie; delete headers.referer; delete headers.authorization; return route.continue({headers}); }
        row.blocked.push({method:request.method(),url:safeUrl(request.url()),type:request.resourceType()}); return route.abort('blockedbyclient');
    });
    page.on('requestfailed', req => row.failed.push({url:safeUrl(req.url()),reason:req.failure()?.errorText,type:req.resourceType()}));
    page.on('response', res => {if(res.status()>=400)row.httpErrors.push({url:safeUrl(res.url()),status:res.status()});});
    page.on('console', msg => {if(msg.type()==='error')row.consoleErrors.push({source:safeUrl(msg.location().url),message:msg.text().slice(0,400)});});
    page.on('pageerror', error => row.pageErrors.push({name:error.name,message:error.message.slice(0,400)}));
    const stem = `${label}-${target.key}-${viewport.width}-${theme}`;
    try {
        const response = await page.goto(BASE+target.path,{waitUntil:'networkidle',timeout:60000});
        row.http={status:response.status(),url:safeUrl(page.url()),contentType:response.headers()['content-type'],xRobotsTag:response.headers()['x-robots-tag']||null};
        assert.equal(response.status(),200); assert.equal(new URL(page.url()).origin,BASE);
        await page.locator('[data-theory-main]').waitFor({state:'visible'});
        await page.waitForFunction(()=>Boolean(window.Alpine));
        await page.evaluate(value=>document.documentElement.classList.toggle('dark',value==='dark'),theme);
        await page.evaluate(async()=>{await document.fonts.ready;await Promise.all([document.fonts.load('400 16px Manrope','Український English'),document.fonts.load('700 22px Archivo','Базова формула')]);await document.fonts.ready;});
        await page.locator('[data-theory-main]').evaluate(n=>n.querySelectorAll('details').forEach(d=>d.open=false));
        row.fonts=await page.evaluate(()=>({status:document.fonts.status,rootFamily:getComputedStyle(document.body).fontFamily,
            loaded:[...document.fonts].filter(f=>f.status==='loaded').map(f=>({family:f.family,style:f.style,weight:f.weight,status:f.status})),
            checks:{manrope:document.fonts.check('400 16px Manrope','Український English'),archivo:document.fonts.check('700 22px Archivo','Базова формула')}}));
        row.sidebar=await page.locator('[data-theory-aside]').evaluateAll(ns=>ns.map(n=>({visible:n.getClientRects().length>0,width:n.getBoundingClientRect().width,classes:n.className,display:getComputedStyle(n).display})));
        row.dom=await page.locator('[data-theory-main]').evaluate(n=>{const copy=n.cloneNode(true);copy.querySelectorAll('script,style,[data-sentence-builder]').forEach(x=>x.remove());return {html:copy.innerHTML,text:copy.textContent.replace(/\s+/g,' ').trim(),ids:[...copy.querySelectorAll('[id]')].map(x=>x.id)};});
        row.metadata=await page.evaluate(()=>({title:document.title,h1:[...document.querySelectorAll('h1')].map(n=>n.textContent.trim()),canonical:document.querySelector('link[rel=canonical]')?.href||null,
            meta:[...document.querySelectorAll('meta[name="description"],meta[name="robots"],meta[property^="og:"],meta[name^="twitter:"]')].map(n=>({key:n.name||n.getAttribute('property'),content:n.content})),
            jsonLd:[...document.querySelectorAll('script[type="application/ld+json"]')].map(n=>JSON.parse(n.textContent))}));
        if(label.startsWith('reference-practice-calibration-')){
            assert.equal(target.key,'reference','Calibration is reference-only');
            const old=JSON.parse(fs.readFileSync(path.join(PRIVATE,'browser-before-v2',`before-v2-reference-${viewport.width}-${theme}.json`),'utf8'));
            assert.equal(row.dom.text,old.dom.text,'Reference learner text equals original BEFORE');assert.deepEqual(row.metadata,old.metadata,'Reference metadata equals original BEFORE');
            row.referenceUnchangedFromBefore={learnerText:true,metadata:true,beforeFile:`browser-before-v2/before-v2-reference-${viewport.width}-${theme}.json`};
        }
        row.screenshots.push(await screenshot(page,dir,stem+'-full.png'));
        const sections = page.locator('[data-theory-main] .theory-section-card');
        for (let s=0;s<await sections.count();s++) {
            const box=await sections.nth(s).boundingBox(); if(!box)continue;
            for(let y=0;y<box.height;y+=900)row.screenshots.push(await screenshot(page,dir,`${stem}-section-${s+1}-${Math.floor(y/900)+1}.png`,{x:Math.max(0,box.x),y:Math.max(0,box.y+y),width:Math.min(box.width,viewport.width-Math.max(0,box.x)),height:Math.min(900,box.height-y)}));
        }
        await page.locator('[data-theory-main]').evaluate(n=>n.querySelectorAll('details').forEach(d=>d.open=true));
        let sample = 0;
        for(const [role,selector,limit]of roles){const locator=page.locator('[data-theory-main]').locator(selector);let found=await locator.count();
            if(!found){row.components.push({role,available:false,selector});continue;}
            for(let i=0;i<Math.min(found,limit);i++){
                const node=locator.nth(i),id=stem+'-'+(++sample);await node.evaluate((n,id)=>n.setAttribute('data-m431-capture-id',id),id);
                const computed=await node.evaluate((n,props)=>{const c=getComputedStyle(n),marker=getComputedStyle(n,'::marker');return {tag:n.tagName,classes:n.className,text:n.textContent.replace(/\s+/g,' ').trim().slice(0,180),lang:n.getAttribute('lang'),visible:n.getClientRects().length>0,styles:Object.fromEntries(props.map(p=>[p,c.getPropertyValue(p)])),markerStyles:Object.fromEntries(['font-family','font-size','color','content'].map(p=>[p,marker.getPropertyValue(p)])),rect:{width:n.getBoundingClientRect().width,height:n.getBoundingClientRect().height},inline:n.getAttribute('style'),controlPanelIndex:[...n.parentElement.children].filter(c=>c.hasAttribute('data-m43-control-panel')||c.classList.contains('bg-white/60')).indexOf(n)};},properties);
                const document=await cdp.send('DOM.getDocument'),{nodeId}=await cdp.send('DOM.querySelector',{nodeId:document.root.nodeId,selector:'[data-m431-capture-id="'+id+'"]'});
                const matched=await cdp.send('CSS.getMatchedStylesForNode',{nodeId}),fonts=await cdp.send('CSS.getPlatformFontsForNode',{nodeId});
                row.components.push({role,index:i,found,selector,available:true,...computed,platformFonts:fonts.fonts,
                    matched:(matched.matchedCSSRules||[]).map(x=>({matchingSelectors:x.matchingSelectors,...compactRule(x.rule,sheets)})),
                    inherited:(matched.inherited||[]).map(x=>({matched:(x.matchedCSSRules||[]).map(y=>compactRule(y.rule,sheets))})),
                    inlineProperties:matched.inlineStyle?.cssProperties||[]});
            }
        }
        const details=page.locator('[data-theory-main] [data-theory-native-extension] > details');
        for(let i=0;i<await details.count();i++)row.screenshots.push(await screenshot(page,dir,`${stem}-detail-${i+1}.png`,await details.nth(i).boundingBox()));
        row.layout=await page.locator('[data-theory-main]').evaluate(n=>({documentOverflow:Math.max(0,document.documentElement.scrollWidth-innerWidth),mainOverflow:Math.max(0,n.scrollWidth-n.clientWidth),tables:[...n.querySelectorAll('.theory-table-scroll')].map(t=>({clientWidth:t.clientWidth,scrollWidth:t.scrollWidth,overflow:getComputedStyle(t).overflowX}))}));
        const footer=page.locator('footer').first();if(await footer.count()){await footer.scrollIntoViewIfNeeded();row.screenshots.push(await nodeScreenshot(footer,dir,stem+'-footer.png'));}
        await page.locator('[data-theory-main]').evaluate(n=>n.querySelectorAll('[data-m431-capture-id]').forEach(x=>x.removeAttribute('data-m431-capture-id')));
        row.cssSources=[...sheets.values()].filter((v,i,a)=>a.indexOf(v)===i);
        const localFailures=row.failed.filter(r=>r.url.startsWith(BASE)),externalFontFailures=row.failed.filter(r=>/^https:\/\/fonts\./u.test(r.url));
        row.fontPolicy='GET local + actual Archivo/Manrope Google Fonts resources allowed identically in every state';
        row.fontFailures=externalFontFailures;
        assert.equal(localFailures.length,0,'No failed local request');assert.equal(row.httpErrors.filter(r=>r.url.startsWith(BASE)).length,0,'No local HTTP error');assert.equal(row.pageErrors.length,0,'No JavaScript error');
        assert.ok(row.fonts.checks.manrope && row.fonts.checks.archivo,'Both actual site font families loaded');
        assert.ok(row.fonts.loaded.some(f=>f.family==='Manrope')&&row.fonts.loaded.some(f=>f.family==='Archivo'),'Font faces loaded, not fallback-only document.fonts.check');
        if (label.startsWith('after-')) {
            const expected = loadExpected();
            row.design = compareDesign(row, expected);
            const old = JSON.parse(fs.readFileSync(path.join(PRIVATE,'browser-before-v2',`before-v2-${target.key}-${viewport.width}-${theme}.json`),'utf8'));
            assert.deepEqual(row.metadata,old.metadata,'Exact metadata preserved');
            assert.equal(row.http.xRobotsTag,old.http.xRobotsTag,'Exact X-Robots-Tag preserved');
            if (target.key === 'reference') assert.equal(row.dom.text,old.dom.text,'Read-only reference learner text unchanged');
            else {
                const qa=require('./seo-m43-local.cjs'),author=qa.targets.find(t=>t.path===target.path);assert.ok(author,'Finite target from frozen M43 master');
                const dom=new JSDOM(await page.content());try{row.author=qa.authorFidelity(dom.window.document,author);}finally{dom.window.close();}
                const component=page.locator(qa.PRACTICE_COMPONENT_SELECTOR);assert.equal(await component.count(),1);
                const cases=await component.evaluate(n=>JSON.parse(JSON.stringify(window.Alpine.$data(n).cases)));
                assert.deepEqual(cases,qa.uiCases(author.lesson),'Live control payload equals independently frozen master');
                row.bank=await freshBankQA(page,author);
                row.allInitialControls=await allInitialControlsQA(page,viewport.width,theme);
                await page.locator('[data-theory-main]').evaluate(n=>n.querySelectorAll('details').forEach(d=>d.open=false));
                row.feedbackStates=await feedbackQA(page,author,theme,dir,stem);
                assert.ok(row.feedbackStates.every(r=>r.pass),'Feedback differs from independently captured reference; see feedbackStates differences');
                if (!label.startsWith('after-pilot-') && !label.startsWith('after-style-')) {
                    row.details=await qa.detailsQA(page,author,dir,stem+'-functional');
                    row.practice=await qa.practiceQA(page,author,dir,stem+'-functional');
                    await qa.tocAndReload(page,author,row);
                } else row.functionalChecksFromPriorFullAcceptance='browser-after-final-v2/manifest.json';
                row.layout=await qa.layoutQA(page);
                row.designPass=row.design.failures.length===0;
                assert.ok(row.designPass,'Presentation mismatches against frozen BEFORE reference: '+row.design.failures.length+'; roles: '+[...new Set(row.design.failures.map(f=>f.role))].join(', '));
            }
        }
        assert.deepEqual(row.failed.filter(r=>r.url.startsWith(BASE)),[],'No failed local requests through all browser checks');
        assert.deepEqual(row.httpErrors.filter(r=>r.url.startsWith(BASE)),[],'No local HTTP errors through all browser checks');
        assert.deepEqual(row.pageErrors,[],'No JavaScript errors through all browser checks');
        const unexpectedConsole=row.consoleErrors.filter(e=>!row.failed.some(f=>f.url===e.source)||e.source.startsWith(BASE));
        assert.deepEqual(unexpectedConsole,[],'No unexpected console errors; any external font network failure is recorded separately');
        row.pass=true;
    } catch(error){row.failure={name:error.name,message:error.message,stack:String(error.stack).split('\n').slice(0,5)};}
    finally{await context.close();write(dir,stem+'.json',row);}
    console.log(JSON.stringify({key:row.key,width:viewport.width,theme,pass:row.pass,components:row.components.length,screenshots:row.screenshots.length,failure:row.failure||null}));return row;
}
async function freshBankQA(page,target){
    const inventory=JSON.parse(fs.readFileSync(path.join(PRIVATE,'m43-1-before-v1.json'),'utf8'));
    const owner=inventory.targets.find(t=>t.identity===target.lesson.identity);assert.ok(owner,'Fresh read-only bank owner inventory');
    const ownClass={
        '/theory/tenses/past-perfect-vs-past-perfect-continuous':'PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder',
        '/theory/tenses/stative-verbs':'PolyglotStativeVerbsAllLevelsLessonSeeder',
        '/theory/tenses/used-to-would':'PolyglotUsedToWouldAllLevelsLessonSeeder',
    }[target.path];assert.ok(ownClass,'Exact finite M43 theory path in diagnostic bank inventory');
    const bank=owner.banks.find(b=>b.seeder_class==='Database\\Seeders\\V3\\Polyglot\\'+ownClass);assert.ok(bank?.linked_ids?.length,'Exact existing own bank');
    const widget=page.locator('[data-m43-practice-ui] [data-sentence-builder]');assert.equal(await widget.count(),1);
    await page.waitForFunction(n=>window.Alpine.$data(n)?.questions?.length>0,await widget.elementHandle());
    const pool=await widget.evaluate(n=>{const q=window.Alpine.$data(n).questions;return{ids:q.map(x=>String(x.id)),types:[...new Set(q.map(x=>String(x.type)))]};});
    const allowedIds=new Set(bank.linked_ids.map(String));assert.equal(new Set(pool.ids).size,pool.ids.length);assert.ok(pool.ids.every(id=>allowedIds.has(id)),'Sample is from own linked bank');assert.deepEqual(pool.types,['4']);
    return{class:ownClass,actualLinkedCount:allowedIds.size,sampledCount:pool.ids.length,sampleIdsSha256:sha(JSON.stringify(pool.ids)),onlyOwnLinkedType4:true};
}
async function allInitialControlsQA(page,width,theme){
    const ref=loadExpected().states.find(s=>s.width===width&&s.theme===theme);assert.ok(ref);const metrics=await page.locator('[data-m43-ui-case]').evaluateAll((cards,properties)=>cards.map(card=>({sourceIndex:Number(card.dataset.m43UiCase),controls:[...card.querySelectorAll('[data-m43-control]')].map(control=>({id:control.dataset.m43Control,kind:control.dataset.m43ControlKind,elements:[...control.querySelectorAll('[data-m43-answer],[data-m43-token],[data-m43-answer-input]')].map(node=>({role:node.hasAttribute('data-m43-answer')?'practice-choice-initial':node.hasAttribute('data-m43-token')?'practice-token-initial':'practice-input-initial',classes:node.className,styles:Object.fromEntries(properties.map(p=>[p,getComputedStyle(node).getPropertyValue(p)]))}))}))})),properties);
    assert.equal(metrics.length,6,'All six actual task cards initially measured');let elements=0;const differences=[];
    for(const task of metrics)for(const control of task.controls)for(const element of control.elements){elements++;const expected=ref.components.find(c=>c.role===element.role&&c.available);assert.ok(expected,'Read-only reference exact control type');for(const property of STYLE_CONTRACT[element.role])if(element.styles[property]!==expected.styles[property])differences.push({task:task.sourceIndex,control:control.id,role:element.role,property,expected:expected.styles[property],actual:element.styles[property]});assert.equal(element.styles['text-transform'],'none','Authored option/token text case preserved');}
    assert.ok(elements>0);assert.deepEqual(differences,[],'All initial controls, not only first sampled task, match independently frozen reference');return{tasks:metrics.length,controls:metrics.reduce((s,t)=>s+t.controls.length,0),elements,differences,metrics};
}
async function feedbackQA(page,target,theme,dir,stem){
    const bytes=fs.readFileSync(path.join(PRIVATE,'reference-feedback-calibration-v1/probe.json'));assert.equal(sha(bytes),FEEDBACK_EXPECTED_SHA,'Independent reference feedback calibration unchanged');
    const reference=JSON.parse(bytes).rows.find(r=>r.key==='reference'&&r.theme===theme);assert.ok(reference?.pass,'Actual read-only reference feedback theme');
    const qa=require('./seo-m43-local.cjs'),correct=qa.uiCases(target.lesson)[0].controls.map(c=>c.answer),component=page.locator(qa.PRACTICE_COMPONENT_SELECTOR),card=page.locator('[data-m43-ui-case]').first(),results=[];
    for(const state of['empty','partial','wrong','correct']){
        await component.evaluate((n,args)=>{const d=window.Alpine.$data(n);d.reset(0);if(args.state==='correct'||args.state==='wrong')args.correct.forEach((v,p)=>d.setAnswer(0,p,v));if(args.state==='partial')d.setAnswer(0,0,args.correct[0]);if(args.state==='wrong'){const c=d.cases[0].controls[0];d.setAnswer(0,0,c.kind==='manual'?'not the requested answer':c.options.find(o=>o.value!==c.answer).value);}d.check(0);},{state,correct});
        const status=card.locator('[data-m43-case-feedback]');await status.waitFor({state:'visible'});
        const result={state,case:await status.evaluate(n=>({text:n.textContent.trim(),styles:Object.fromEntries(['font-family','font-size','font-style','font-weight','line-height','color'].map(p=>[p,getComputedStyle(n).getPropertyValue(p)]))})),parts:await card.locator('[data-m43-part-feedback]').evaluateAll(ns=>ns.map(n=>({text:n.textContent.trim(),styles:Object.fromEntries(['font-family','font-size','font-style','font-weight','line-height','color'].map(p=>[p,getComputedStyle(n).getPropertyValue(p)]))}))),pass:false};
        const truth=await component.evaluate(n=>{const d=window.Alpine.$data(n);return{case:d.isCorrect(0),parts:d.cases[0].controls.map((_,p)=>d.partCorrect(0,p))};});
        const expected=success=>reference.states.find(s=>s.state===(success?'correct':'wrong')).feedback.styles;
        result.differences=[];for(const [index,item]of[result.case,...result.parts].entries()){const match=expected(index===0?truth.case:truth.parts[index-1]);for(const[p,v]of Object.entries(match))if(item.styles[p]!==v)result.differences.push({role:index===0?'case-feedback':'part-feedback',index:index-1,property:p,expected:v,actual:item.styles[p]});}
        result.pass=result.differences.length===0;result.screenshot=await nodeScreenshot(card,dir,stem+'-feedback-'+state+'.png');results.push(result);
    }
    const reset=card.locator('[data-m43-reset]');await reset.focus();await reset.press('Enter');await card.locator('[data-m43-case-feedback]').waitFor({state:'hidden'});assert.equal(await component.evaluate(n=>window.Alpine.$data(n).checked[0]),false,'Final presentation reset remains keyboard-activatable');
    return results;
}
const TYPE_PROPERTIES=['font-family','font-size','font-style','font-weight','line-height'];
const BOX_PROPERTIES=['background-color','background-image','border-top','border-right','border-bottom','border-left','border-inline-start','border-radius','box-shadow'];
const PADDING_PROPERTIES=['padding-top','padding-right','padding-bottom','padding-left'];
const STYLE_CONTRACT={
    'outer-card':[...BOX_PROPERTIES],
    'section-header':[...BOX_PROPERTIES,...PADDING_PROPERTIES,'gap'],
    'section-title':[...TYPE_PROPERTIES,'color','gap'],
    'section-number':[...TYPE_PROPERTIES,...BOX_PROPERTIES,...PADDING_PROPERTIES,'color'],
    'section-body':PADDING_PROPERTIES,
    'usage-panel':BOX_PROPERTIES,
    'usage-label-exact':[...TYPE_PROPERTIES,'text-transform','letter-spacing'],
    'usage-body':[...TYPE_PROPERTIES,'color'],
    'usage-content':PADDING_PROPERTIES,
    'forms-panel-exact':[...BOX_PROPERTIES,...PADDING_PROPERTIES],
    'forms-label':[...TYPE_PROPERTIES,...BOX_PROPERTIES,...PADDING_PROPERTIES,'color','margin-bottom','text-transform','letter-spacing'],
    'forms-formula':[...TYPE_PROPERTIES,'color','margin-bottom'],
    'forms-description':[...TYPE_PROPERTIES,'color'],
    'summary-rule':['gap','display'],
    'summary-text':[...TYPE_PROPERTIES,'color'],
    'example-en':[...TYPE_PROPERTIES,'color'],
    'example-uk':[...TYPE_PROPERTIES,'color','margin-top'],
    'example-basic-box':[...BOX_PROPERTIES,...PADDING_PROPERTIES,'gap'],
    'example-icon':TYPE_PROPERTIES,
    'disclosure':['border-top','margin-top'],
    'disclosure-summary':[...TYPE_PROPERTIES,...PADDING_PROPERTIES,'color'],
    'detail-body':[...PADDING_PROPERTIES,'gap'],
    'detail-example-en-plain':[...TYPE_PROPERTIES,'color'],
    'detail-example-uk':[...TYPE_PROPERTIES,'color','margin-top'],
    'table-container':[...BOX_PROPERTIES,'overflow-x'],
    'table':['font-family','font-size','font-style','line-height','background-color'],
    'table-th':[...TYPE_PROPERTIES,...PADDING_PROPERTIES,'background-color','border-bottom','color','text-transform','letter-spacing'],
    'table-td':['font-family','font-size','font-style','line-height',...PADDING_PROPERTIES,'background-color','border-bottom'],
    'practice-card':BOX_PROPERTIES,
    'practice-header':[...PADDING_PROPERTIES,'background-color','border-bottom'],
    'practice-heading':[...TYPE_PROPERTIES,'color','gap'],
    'practice-prompt':[...TYPE_PROPERTIES,'color','margin-top'],
    'practice-control-panel':[...BOX_PROPERTIES,...PADDING_PROPERTIES,'margin-top'],
    'practice-task-number':[...TYPE_PROPERTIES,'color','border-radius',...PADDING_PROPERTIES],
    'section-note':[...TYPE_PROPERTIES,...BOX_PROPERTIES,...PADDING_PROPERTIES,'color'],
    'practice-choice-initial':[...TYPE_PROPERTIES,...BOX_PROPERTIES,...PADDING_PROPERTIES,'color'],
    'practice-token-initial':[...TYPE_PROPERTIES,...BOX_PROPERTIES,...PADDING_PROPERTIES,'color'],
    'practice-input-initial':[...TYPE_PROPERTIES,...BOX_PROPERTIES,...PADDING_PROPERTIES,'color'],
    'practice-reset':[...TYPE_PROPERTIES,...BOX_PROPERTIES,...PADDING_PROPERTIES,'color'],
};
function compareDesign(row,expected){
    const ref=expected.states.find(r=>r.width===row.viewport.width&&r.theme===row.theme);assert.ok(ref,'Independent BEFORE reference state');
    const comparisons=[],failures=[],unavailable=[];
    for(const[role,props]of Object.entries(STYLE_CONTRACT)){
        const source=ref.components.find(c=>c.role===role&&c.available),actual=row.components.filter(c=>c.role===role&&c.available);
        if(!source||!actual.length){unavailable.push({role,referenceAvailable:Boolean(source),targetAvailable:actual.length>0});continue;}
        for(const node of actual){let chosenSource=source;if(role==='practice-control-panel'&&node.controlPanelIndex>0)chosenSource=ref.components.find(c=>c.role===role&&c.available&&parseFloat(c.styles['margin-top'])>0)||source;if(role==='practice-task-number'&&row.key==='reference')chosenSource=ref.components.find(c=>c.role===role&&c.available&&c.index===node.index)||source;const differences=props.filter(p=>node.styles[p]!==chosenSource.styles[p]).map(p=>({property:p,expected:chosenSource.styles[p],actual:node.styles[p]}));
            if(role==='table-td') { const palette=new Set(ref.components.filter(c=>c.role==='table-td'&&c.available).map(c=>c.styles.color));if(!palette.has(node.styles.color))differences.push({property:'color',expected:[...palette],actual:node.styles.color}); }
            if(role==='practice-task-number'){const palette=new Set(ref.components.filter(c=>c.role===role&&c.available).map(c=>c.styles['background-color']));if(!palette.has(node.styles['background-color']))differences.push({property:'background-color',expected:[...palette],actual:node.styles['background-color']});for(const dimension of['width','height'])if(node.rect?.[dimension]!==chosenSource.rect?.[dimension])differences.push({property:'rect.'+dimension,expected:chosenSource.rect?.[dimension],actual:node.rect?.[dimension]});}
            comparisons.push({role,index:node.index,properties:props.length+(role==='table-td'?1:0),referenceIndex:chosenSource.index,pass:differences.length===0});
            if(differences.length)failures.push({role,index:node.index,differences});}
    }
    return{beforeEvidenceSha256:expected.referenceManifestSha256,comparisons,unavailable,failures};
}
function freezeExpected(){
    const before=path.join(PRIVATE,'reference-before-aux-v1','manifest.json'),bytes=fs.readFileSync(before),report=JSON.parse(bytes);assert.equal(report.pass,true);
    const expected={at:new Date().toISOString(),referencePath:targets[0].path,referenceManifestSha256:sha(bytes),source:'Fresh read-only BEFORE reference; no target AFTER values',
        propertiesByRole:STYLE_CONTRACT,states:report.rows.map(r=>({width:r.viewport.width,theme:r.theme,fonts:r.fonts,components:r.components}))};
    write(PRIVATE,'design-expectations-before-v1.json',expected);console.log(JSON.stringify({file:'design-expectations-before-v1.json',sha256:sha(fs.readFileSync(path.join(PRIVATE,'design-expectations-before-v1.json')))}));return expected;
}
function loadExpected(){if(!expectedCache){const bytes=fs.readFileSync(path.join(PRIVATE,'design-expectations-before-v1.json'));assert.equal(sha(bytes),EXPECTED_SHA,'Frozen independent BEFORE design expectations unchanged');const full=JSON.parse(bytes);expectedCache={referenceManifestSha256:full.referenceManifestSha256,states:full.states.map(s=>{const components=s.components.map(c=>({role:c.role,index:c.index,available:c.available,styles:c.styles,rect:c.rect}));const note=s.components.find(c=>c.role==='example-note'&&c.available&&c.classes.split(' ').includes('rounded-lg')&&c.classes.split(' ').includes('p-3'));assert.ok(note,'Original BEFORE exact simple-note type');components.push({...note,role:'section-note',sourceBeforeRole:'example-note'});return{width:s.width,theme:s.theme,components};})};}
    if(!practiceExtensionCache){const bytes=fs.readFileSync(path.join(PRIVATE,'design-expectations-practice-v1.json'));assert.equal(sha(bytes),PRACTICE_EXPECTED_SHA,'Frozen reference-only practice calibration unchanged');practiceExtensionCache=JSON.parse(bytes);assert.equal(practiceExtensionCache.referenceUnchangedFromOriginalBefore,true);for(const state of expectedCache.states){const extra=practiceExtensionCache.states.find(s=>s.width===state.width&&s.theme===state.theme);assert.ok(extra);state.components.push(...extra.components);}}
    if(!controlsExtensionCache){const bytes=fs.readFileSync(path.join(PRIVATE,'design-expectations-controls-v1.json'));assert.equal(sha(bytes),CONTROLS_EXPECTED_SHA);controlsExtensionCache=JSON.parse(bytes);assert.equal(controlsExtensionCache.referenceUnchangedFromOriginalBefore,true);const reset=JSON.parse(fs.readFileSync(path.join(PRIVATE,'reference-reset-calibration-v1/probe.json')));for(const state of expectedCache.states){const extra=controlsExtensionCache.states.find(s=>s.width===state.width&&s.theme===state.theme);assert.ok(extra);state.components.push(...extra.components);const refReset=reset.rows.find(r=>r.theme===state.theme);assert.ok(refReset?.pass&&refReset.resetButton.visible);state.components.push({role:'practice-reset',available:true,index:0,...refReset.resetButton});}}
    return expectedCache;}
function freezePractice(){
    const folder=path.join(PRIVATE,'reference-practice-calibration-v1'),bytes=fs.readFileSync(path.join(folder,'manifest.json')),report=JSON.parse(bytes);assert.equal(report.pass,true);assert.equal(report.rows.length,4);
    const states=report.rows.map(row=>{const oldBytes=fs.readFileSync(path.join(PRIVATE,'browser-before-v2',`before-v2-reference-${row.viewport.width}-${row.theme}.json`)),old=JSON.parse(oldBytes);assert.equal(row.dom.text,old.dom.text);assert.deepEqual(row.metadata,old.metadata);
        const document=new JSDOM(row.dom.html).window.document,original=new JSDOM(old.dom.html).window.document;
        const selectors=['.theory-exercise > .p-4 > [class*="bg-white/60"]','.theory-exercise > .border-b h3 > span'];
        const classes=d=>selectors.map(s=>[...d.querySelectorAll(s)].map(n=>n.className));assert.deepEqual(classes(document),classes(original),'Exact practice markup classes frozen in original BEFORE');
        const components=row.components.filter(c=>['practice-control-panel','practice-task-number'].includes(c.role));assert.ok(components.every(c=>c.available));
        return{width:row.viewport.width,theme:row.theme,originalBeforeFileSha256:sha(oldBytes),components};});
    const result={at:new Date().toISOString(),referencePath:targets[0].path,source:'Additional computed roles measured only on read-only reference; text, metadata and exact panel/badge classes equal original BEFORE',referenceUnchangedFromOriginalBefore:true,calibrationManifestSha256:sha(bytes),states};write(PRIVATE,'design-expectations-practice-v1.json',result);console.log(JSON.stringify({file:'design-expectations-practice-v1.json',sha256:sha(fs.readFileSync(path.join(PRIVATE,'design-expectations-practice-v1.json')))}));return result;
}
function freezeControls(){
    const folder=path.join(PRIVATE,'reference-controls-calibration-v1'),bytes=fs.readFileSync(path.join(folder,'manifest.json')),report=JSON.parse(bytes);assert.equal(report.pass,true);assert.equal(report.rows.length,4);
    const states=report.rows.map(row=>{const oldBytes=fs.readFileSync(path.join(PRIVATE,'browser-before-v2',`before-v2-reference-${row.viewport.width}-${row.theme}.json`)),old=JSON.parse(oldBytes);assert.equal(row.dom.text,old.dom.text);assert.deepEqual(row.metadata,old.metadata);
        const current=new JSDOM(row.dom.html).window.document,original=new JSDOM(old.dom.html).window.document;
        const selectors=['.theory-exercise button.uppercase','.theory-exercise button.text-emerald-700','.theory-exercise input[data-word-suggestion-input]'];
        const classes=d=>selectors.map(s=>[...d.querySelectorAll(s)].map(n=>n.className));assert.deepEqual(classes(current),classes(original),'Exact initial control markup classes equal original BEFORE');
        const components=row.components.filter(c=>['practice-choice-initial','practice-token-initial','practice-input-initial'].includes(c.role));assert.ok(components.every(c=>c.available));
        return{width:row.viewport.width,theme:row.theme,originalBeforeFileSha256:sha(oldBytes),components};});
    const result={at:new Date().toISOString(),referencePath:targets[0].path,source:'Read-only reference initial control computed rules; text, metadata and exact markup classes equal original BEFORE',referenceUnchangedFromOriginalBefore:true,calibrationManifestSha256:sha(bytes),states};write(PRIVATE,'design-expectations-controls-v1.json',result);console.log(JSON.stringify({file:'design-expectations-controls-v1.json',sha256:sha(fs.readFileSync(path.join(PRIVATE,'design-expectations-controls-v1.json')))}));return result;
}
function manifestRow(row,dir,label){const file=`${label}-${row.key}-${row.viewport.width}-${row.theme}.json`;return{...row,rowFile:file,rowSha256:sha(fs.readFileSync(path.join(dir,file))),dom:{textSha256:sha(row.dom?.text||''),ids:row.dom?.ids||[]},components:row.components.map(c=>({role:c.role,index:c.index,available:c.available,tag:c.tag,classes:c.classes,text:c.text,styles:c.styles,rect:c.rect,platformFonts:c.platformFonts}))};}
async function run(dir,label,referenceOnly=false,pilot=false){dir=path.resolve(dir);assert.ok(dir.startsWith(PRIVATE+path.sep),'Private M43.1 evidence only');assert.match(label,/^[a-z0-9-]+$/u);fs.mkdirSync(dir,{recursive:true});assert.ok(!fs.existsSync(path.join(dir,'manifest.json')));
    const report={at:new Date().toISOString(),base:BASE,label,conditions:{viewports:[{width:1440,height:1000},{width:390,height:844}],themes:['light','dark'],freshGuest:true,cookies:'none',referer:'none',deviceScaleFactor:1,zoom:'default 100%; actual Ctrl+zoom untested'},rows:[],pass:false};
    const browser=await chromium.launch({headless:true,executablePath:CHROME});try{for(const viewport of(pilot?report.conditions.viewports.slice(0,1):report.conditions.viewports))for(const theme of(pilot?['light']:report.conditions.themes))for(const target of (pilot?targets.filter(t=>t.key!=='reference'):referenceOnly?targets.filter(t=>t.key==='reference'):targets)){const row=await capture(browser,target,viewport,theme,dir,label);report.rows.push(label.startsWith('after-')?manifestRow(row,dir,label):row);}report.pass=report.rows.length===(pilot?3:referenceOnly?4:16)&&report.rows.every(r=>r.pass);}finally{await browser.close();report.finishedAt=new Date().toISOString();write(dir,'manifest.json',report);}return report;
}
async function supplemental(dir,label){
    dir=path.resolve(dir);assert.ok(dir.startsWith(PRIVATE+path.sep));assert.match(label,/^[a-z0-9-]+$/u);fs.mkdirSync(dir,{recursive:true});assert.ok(!fs.existsSync(path.join(dir,'supplemental.json')));
    const qa=require('./seo-m43-local.cjs'),report={at:new Date().toISOString(),base:BASE,rows:[],limits:['320 CSS pixel viewport coverage is not genuine Ctrl+zoom.'],pass:false};
    const browser=await chromium.launch({headless:true,executablePath:CHROME});
    try{for(const mode of ['no-js','320'])for(const target of qa.targets){
        const noJS=mode==='no-js',viewport={width:noJS?390:320,height:844},stem=label+'-'+target.key+'-'+mode,row={path:target.path,mode,viewport,failed:[],blocked:[],pageErrors:[],screenshots:[],pass:false};report.rows.push(row);
        const context=await browser.newContext({viewport,javaScriptEnabled:!noJS,colorScheme:'light',serviceWorkers:'block'}),page=await context.newPage();
        await context.route('**/*',route=>{const request=route.request();if(allowed(request.url(),request.method())){const headers={...request.headers()};delete headers.cookie;delete headers.referer;delete headers.authorization;return route.continue({headers});}row.blocked.push({url:safeUrl(request.url()),method:request.method()});return route.abort('blockedbyclient');});
        page.on('requestfailed',r=>row.failed.push({url:safeUrl(r.url()),type:r.resourceType(),reason:r.failure()?.errorText}));page.on('pageerror',e=>row.pageErrors.push({name:e.name,message:e.message.slice(0,400)}));
        try{const response=await page.goto(BASE+target.path,{waitUntil:'networkidle',timeout:60000});assert.equal(response.status(),200);await page.locator('[data-theory-main]').waitFor({state:'visible'});
            await page.evaluate(async()=>{await document.fonts.ready;await Promise.all([document.fonts.load('400 16px Manrope','Український English'),document.fonts.load('700 22px Archivo','Базова формула')]);await document.fonts.ready;});
            const dom=new JSDOM(await page.content());try{row.author=qa.authorFidelity(dom.window.document,target);}finally{dom.window.close();}
            if(noJS){row.details=[];row.keys=[];
                for(const point of target.lesson.sections.flatMap(s=>s.points).filter(p=>p.detail)){const detail=page.locator('[data-m43-basic-point="'+point.id+'"] details'),summary=detail.locator(':scope > summary');await summary.click();assert.equal(await detail.evaluate(n=>n.open),true);await summary.focus();await summary.press('Space');assert.equal(await detail.evaluate(n=>n.open),false);await summary.press('Enter');assert.equal(await detail.evaluate(n=>n.open),true);await summary.click();row.details.push(point.detail.id);}
                for(const task of target.lesson.practice){const card=page.locator('[data-m43-ui-case="'+task.source_index+'"]'),detail=card.locator('[data-m43-ui-explanation]'),summary=detail.locator(':scope > summary');await summary.click();assert.equal(await card.locator('[data-m43-self-check-answer]').isVisible(),true);
                    const keyDOM=new JSDOM(await card.locator('[data-m43-self-check-answer]').innerHTML());try{qa.feedbackFidelity(keyDOM.window.document,task);}finally{keyDOM.window.close();}
                    for(const c of task.controls.filter(c=>c.kind==='manual'))assert.deepEqual((await card.locator('[data-m43-control="'+c.id+'"] [data-m43-static-token]').allTextContents()).map(qa.norm),c.tokens);
                    const keyFile=stem+'-'+task.id+'-key.png';assert.ok(!fs.existsSync(path.join(dir,keyFile)));await card.screenshot({path:path.join(dir,keyFile),animations:'disabled'});row.screenshots.push({file:keyFile,sha256:sha(fs.readFileSync(path.join(dir,keyFile)))});await summary.focus();await summary.press('Space');assert.equal(await detail.evaluate(n=>n.open),false);row.keys.push(task.id);}
                row.noJSMechanism='Native details + complete static keys/tokens; no grading claim without JavaScript';
            }else{await page.waitForFunction(selector=>window.Alpine?.$data(document.querySelector(selector))?.cases?.length===6,qa.PRACTICE_COMPONENT_SELECTOR);row.bank=await freshBankQA(page,target);row.layout=await qa.layoutQA(page);}
            await page.evaluate(()=>scrollTo(0,0));row.screenshots.push(await screenshot(page,dir,stem+'-full.png'));
            const local=row.failed.filter(r=>r.url.startsWith(BASE)&&!(noJS&&r.type==='script'&&r.reason==='csp'));assert.deepEqual(local,[]);assert.deepEqual(row.pageErrors,[]);row.pass=true;
        }catch(error){row.failure={name:error.name,message:error.message};}finally{await context.close();write(dir,stem+'.json',row);}
        console.log(JSON.stringify({path:target.path,mode,pass:row.pass,failure:row.failure||null}));
    }report.pass=report.rows.length===6&&report.rows.every(r=>r.pass);}finally{await browser.close();report.finishedAt=new Date().toISOString();write(dir,'supplemental.json',report);}return report;
}
async function footerAndSidebar(dir,label,only=null){
    dir=path.resolve(dir);assert.ok(dir.startsWith(PRIVATE+path.sep));assert.match(label,/^[a-z0-9-]+$/u);fs.mkdirSync(dir,{recursive:true});assert.ok(!fs.existsSync(path.join(dir,'manifest.json')));
    const report={at:new Date().toISOString(),purpose:'Supplement legacy BEFORE crop with actual locator footer and sidebar conditions',base:BASE,rows:[],pass:false},browser=await chromium.launch({headless:true,executablePath:CHROME});
    try{for(const viewport of[{width:1440,height:1000},{width:390,height:844}])for(const theme of['light','dark'])for(const target of targets){
        if(only&&(target.key!==only.key||viewport.width!==only.width||theme!==only.theme))continue;
        const row={key:target.key,path:target.path,viewport,theme,screenshots:[],pass:false},context=await browser.newContext({viewport,colorScheme:theme,deviceScaleFactor:1,serviceWorkers:'block'}),page=await context.newPage();report.rows.push(row);const stem=label+'-'+target.key+'-'+viewport.width+'-'+theme;
        await context.addInitScript(v=>localStorage.setItem('theme',v),theme);
        await context.route('**/*',route=>{const r=route.request();if(allowed(r.url(),r.method())){const h={...r.headers()};delete h.cookie;delete h.referer;delete h.authorization;return route.continue({headers:h});}return route.abort('blockedbyclient');});
        try{const response=await page.goto(BASE+target.path,{waitUntil:'networkidle',timeout:60000});assert.equal(response.status(),200);await page.waitForFunction(()=>Boolean(window.Alpine));await page.evaluate(v=>document.documentElement.classList.toggle('dark',v==='dark'),theme);
            await page.evaluate(async()=>{await document.fonts.ready;await Promise.all([document.fonts.load('400 16px Manrope','Український English'),document.fonts.load('700 22px Archivo','Базова формула')]);await document.fonts.ready;});
            row.sidebar=await page.locator('[data-theory-aside]').evaluateAll(ns=>ns.map(n=>({display:getComputedStyle(n).display,visible:n.getClientRects().length>0,width:n.getBoundingClientRect().width,classes:n.className,collapsed:n.closest('[x-data]')?.getAttribute('x-data')})));
            assert.equal(row.sidebar.length,1,'Actual sidebar owner');assert.equal(row.sidebar[0].visible,viewport.width>=1024,'Consistent desktop visible/mobile hidden sidebar');
            if(viewport.width>=1024)row.screenshots.push(await nodeScreenshot(page.locator('[data-theory-aside]'),dir,stem+'-sidebar.png'));
            const footer=page.locator('footer').first();assert.equal(await footer.count(),1);row.footerText=(await footer.textContent()).replace(/\s+/g,' ').trim();row.screenshots.push(await nodeScreenshot(footer,dir,stem+'-footer.png'));assert.ok(row.footerText.includes('Gramlyze')||row.footerText.includes('GRAMLYZE'),'Real site footer captured');
            row.pass=true;
        }catch(error){row.failure={name:error.name,message:error.message};}finally{await context.close();write(dir,stem+'.json',row);}console.log(JSON.stringify({key:row.key,width:viewport.width,theme,pass:row.pass,failure:row.failure||null}));
    }report.pass=report.rows.length===(only?1:16)&&report.rows.every(r=>r.pass);}finally{await browser.close();report.finishedAt=new Date().toISOString();write(dir,'manifest.json',report);}return report;
}
async function feedbackProbe(dir,label,referenceOnly=false){
    dir=path.resolve(dir);assert.ok(dir.startsWith(PRIVATE+path.sep));assert.match(label,/^[a-z0-9-]+$/u);fs.mkdirSync(dir,{recursive:true});assert.ok(!fs.existsSync(path.join(dir,'probe.json')));
    const report={at:new Date().toISOString(),base:BASE,purpose:'Actual visible practice feedback typography and state colors; client-only scoring in isolated guests',rows:[]},qa=require('./seo-m43-local.cjs'),browser=await chromium.launch({headless:true,executablePath:CHROME});
    try{for(const theme of(referenceOnly?['light','dark']:['light']))for(const target of(referenceOnly?[targets[0]]:[targets[0],...targets.filter(t=>t.key!=='reference')])){
        const row={key:target.key,path:target.path,theme,states:[],blockedNonGet:[],pass:false},context=await browser.newContext({viewport:{width:1440,height:1000},colorScheme:theme,serviceWorkers:'block'}),page=await context.newPage();report.rows.push(row);await context.addInitScript(v=>localStorage.setItem('theme',v),theme);
        await context.route('**/*',route=>{const r=route.request();if(allowed(r.url(),r.method())){const h={...r.headers()};delete h.cookie;delete h.referer;delete h.authorization;return route.continue({headers:h});}if(r.method()!=='GET')row.blockedNonGet.push({method:r.method(),url:safeUrl(r.url())});return route.abort('blockedbyclient');});
        try{const response=await page.goto(BASE+target.path,{waitUntil:'networkidle'});assert.equal(response.status(),200);await page.waitForFunction(()=>Boolean(window.Alpine));await page.evaluate(v=>document.documentElement.classList.toggle('dark',v==='dark'),theme);await page.evaluate(async()=>{await document.fonts.ready;await document.fonts.load('400 16px Manrope','Український English');await document.fonts.ready;});
            if(target.key==='reference'){const card=page.locator('.theory-exercise').first();await card.locator('.bg-white\\/60').first().locator('button').nth(1).click();await card.getByRole('button',{name:'Перевірити',exact:true}).click();const status=card.locator('[x-show*="hasAnswer"]').first();await status.waitFor({state:'visible'});row.states.push({state:'wrong',feedback:await status.evaluate(n=>({tag:n.tagName,classes:n.className,text:n.textContent.replace(/\s+/g,' ').trim(),styles:Object.fromEntries(['font-family','font-size','font-style','font-weight','line-height','color'].map(p=>[p,getComputedStyle(n).getPropertyValue(p)]))}))});
                await card.locator('.bg-white\\/60').first().locator('button').first().click();await card.getByRole('button',{name:'Перевірити',exact:true}).click();row.states.push({state:'correct',feedback:await status.evaluate(n=>({tag:n.tagName,classes:n.className,text:n.textContent.replace(/\s+/g,' ').trim(),styles:Object.fromEntries(['font-family','font-size','font-style','font-weight','line-height','color'].map(p=>[p,getComputedStyle(n).getPropertyValue(p)]))}))});
                const reset=card.locator('button[x-show]').first();await reset.waitFor({state:'visible'});row.resetButton=await reset.evaluate((n,props)=>({classes:n.className,text:n.textContent.trim(),visible:n.getClientRects().length>0,styles:Object.fromEntries(props.map(p=>[p,getComputedStyle(n).getPropertyValue(p)]))}),properties);row.resetScreenshot=await nodeScreenshot(card,dir,label+'-reference-'+theme+'-checked-reset.png');
            }else{const author=qa.targets.find(t=>t.path===target.path),component=page.locator(qa.PRACTICE_COMPONENT_SELECTOR),card=page.locator('[data-m43-ui-case]').first();await page.waitForFunction(selector=>window.Alpine?.$data(document.querySelector(selector))?.cases?.length===6,qa.PRACTICE_COMPONENT_SELECTOR);
                for(const state of['empty','correct']){await component.evaluate((n,state)=>{const d=window.Alpine.$data(n);d.reset(0);if(state==='correct')d.cases[0].controls.forEach((c,p)=>d.setAnswer(0,p,c.kind==='manual'?c.answer:c.answer));d.check(0);},state);const status=card.locator('[data-m43-case-feedback]');await status.waitFor({state:'visible'});row.states.push({state,feedback:await status.evaluate(n=>({tag:n.tagName,classes:n.className,text:n.textContent.replace(/\s+/g,' ').trim(),styles:Object.fromEntries(['font-family','font-size','font-style','font-weight','line-height','color'].map(p=>[p,getComputedStyle(n).getPropertyValue(p)]))})),parts:await card.locator('[data-m43-part-feedback]').evaluateAll(ns=>ns.map(n=>({classes:n.className,color:getComputedStyle(n).color,text:n.textContent}))) });}await component.evaluate(n=>window.Alpine.$data(n).reset(0));}
            assert.deepEqual(row.blockedNonGet,[]);row.pass=true;
        }catch(error){row.failure={name:error.name,message:error.message};}finally{await context.close();}console.log(JSON.stringify(row));
    }}finally{await browser.close();write(dir,'probe.json',report);}return report;
}
if(require.main===module){const[dir,label,mode]=process.argv.slice(2);if(dir==='--freeze-expected')freezeExpected();else if(dir==='--freeze-practice')freezePractice();else if(dir==='--freeze-controls')freezeControls();else{assert.ok(!mode||['--reference-only','--supplemental','--footer-sidebar','--pilot'].includes(mode));(mode==='--supplemental'?supplemental(dir,label):mode==='--footer-sidebar'?footerAndSidebar(dir,label):run(dir,label,mode==='--reference-only',mode==='--pilot')).then(r=>{if(!r.pass)process.exitCode=1;}).catch(e=>{console.error(e.stack);process.exitCode=1;});}}
module.exports={BASE,targets,properties,roles,STYLE_CONTRACT,compareDesign,freezeExpected,freezePractice,freezeControls,allowed,run,supplemental,footerAndSidebar,feedbackProbe};
