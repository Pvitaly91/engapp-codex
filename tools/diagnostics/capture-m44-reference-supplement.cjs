'use strict';
// Fresh independent references only: pre-M44 ROOT sync/apply; client-only practice state.
// GET .loc and Google Fonts only. Importing does not launch a browser or write evidence.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),crypto=require('node:crypto');
const BASE='http://gramlyze.loc',SERVED='D:/DEV/htdocs/gramlyze.loc',CHROME='C:/Program Files/Google/Chrome/Application/chrome.exe';
const PRIVATE=path.resolve(SERVED,'storage/app/seo-m44-local');
const REFERENCES=[
    {key:'PPC',path:'/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms'},
    {key:'M43-A',path:'/theory/tenses/past-perfect-vs-past-perfect-continuous'},
    {key:'M43-B',path:'/theory/tenses/stative-verbs'},
    {key:'M43-C',path:'/theory/tenses/used-to-would'},
];
const PROPERTIES=['background-color','background-image','border-top-width','border-top-color','border-left-width','border-left-color',
    'border-radius','box-shadow','font-family','font-size','font-weight','font-style','line-height','color',
    'padding-top','padding-right','padding-bottom','padding-left','margin-top','margin-bottom','gap','text-transform','letter-spacing',
    'display','overflow-wrap','white-space','border-right-width','border-bottom-width','padding-inline-start','margin-left','margin-right','text-decoration-line'];
const ROLES=[
    ['section-card','.theory-section-card'],['section-header','.theory-section-header'],['section-heading','.theory-section-title'],
    ['section-body','.theory-section-card > .theory-section-body'],['section-number','.theory-section-number'],
    ['usage-panel','.theory-native-block:has(.theory-item > div > div > .rounded-full) .theory-item,[data-m43-native-layout="usage-panels"] .theory-item'],
    ['usage-caption','.theory-item h3 > span.uppercase,.theory-item h3 > span:not(.rounded-full),.theory-item span.text-xs.uppercase'],
    ['usage-description','.theory-item > div > .m42-rich-fragment > p,.theory-item > div > p:not(.theory-translation)'],
    ['forms-panel','.theory-item.group.relative,[data-m43-native-layout="forms-grid"] .theory-item'],
    ['forms-label','.theory-item.group.relative span.uppercase,[data-m43-native-layout="forms-grid"] .theory-item span.uppercase'],
    ['forms-formula','.theory-item.group.relative h3,[data-m43-native-layout="forms-grid"] .theory-item h3'],
    ['forms-description','.m43-form-description > p,.m44-form-description > p,.theory-item.group.relative .text-muted-foreground'],
    ['example-box','div.theory-example:not(.theory-example--wrong):not(.theory-example--right)'],
    ['example-en','.theory-example p[lang="en"],.theory-example > p.font-medium'],
    ['example-uk','.theory-example .theory-translation'],['example-note','.m43-example-note,.theory-example-note'],
    ['example-icon','.theory-example > span'],['disclosure-summary','[data-theory-native-extension] > details > summary'],
    ['disclosure-body','[data-theory-native-extension] .theory-section-detail-body'],
    ['table-scroll','.theory-table-scroll'],['table-head','.theory-table-scroll thead th'],['table-cell','.theory-table-scroll tbody td'],
    ['mistake-box','.theory-example--wrong,.theory-example--right'],['mistake-en','.theory-example--wrong [lang="en"],.theory-example--right [lang="en"]'],
    ['section-note','.theory-note.text-sm.rounded-lg.p-3'],
    ['practice-card','.theory-exercise'],['practice-header','.theory-exercise > .border-b'],
    ['practice-body','.theory-exercise > .p-4'],['practice-title','.theory-exercise > .border-b h3,.theory-exercise > .border-b h4'],
    ['practice-prompt','.theory-exercise > .border-b > p:first-of-type'],['practice-context','.theory-exercise > .border-b > p:nth-of-type(2)'],
    ['practice-control-panel','.theory-exercise > .p-4 > .bg-white\\/60'],
    ['practice-choice','.theory-exercise [data-m43-answer],.theory-exercise button.uppercase'],
    ['practice-token','.theory-exercise [data-m43-token],.theory-exercise button.text-emerald-700'],
    ['practice-input','.theory-exercise [data-m43-answer-input],.theory-exercise input[data-word-suggestion-input]'],
    ['practice-task-number','.theory-exercise > .border-b h3 > span,.theory-exercise > .border-b h4 > span'],
    ['practice-feedback','.theory-exercise [data-m43-case-feedback]'],['practice-part-feedback','.theory-exercise [data-m43-part-feedback],.theory-exercise .mt-2.text-xs.font-semibold'],
];
const sha=bytes=>crypto.createHash('sha256').update(bytes).digest('hex');
function allowedRequest(value,method){try{const u=new URL(value);if(method!=='GET'||u.username||u.password)return false;
    if(u.origin===BASE)return !/(?:progress|attempt|chatgpt|openai|(?:^|\/)ai(?:\/|$))/iu.test(u.pathname)&&!/(?:chatgpt|openai)/iu.test(u.search);
    return u.protocol==='https:'&&['https://fonts.googleapis.com','https://fonts.gstatic.com'].includes(u.origin);
}catch{return false;}}
function safeUrl(value){try{const u=new URL(value);return u.origin+u.pathname;}catch{return '(invalid URL)';}}
function hashes(){const files=['resources/css/theory-unified-design.css','resources/views/theory/show.blade.php',
    'resources/views/theory/partials/content-block.blade.php','resources/views/theory/partials/point-detail-fragment.blade.php',
    'resources/views/courses/partials/theory-page-content.blade.php','resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php',
    'resources/views/engram/theory/blocks-v3/usage-panels.blade.php','resources/views/engram/theory/widgets/lesson-rule-cards.blade.php',
    'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
    'database/seeders/Page_V3/Tenses/PastPerfectContinuous/PastPerfectContinuousFormsTheorySeeder/definition.json',
    ...['TensesPastPerfectVsPastPerfectContinuousTheorySeeder','TensesStativeVerbsTheorySeeder','TensesUsedToWouldTheorySeeder'].map(n=>'database/seeders/Page_V3/Tenses/'+n+'/definition.json')];
    return Object.fromEntries(files.map(file=>[file,sha(fs.readFileSync(path.join(SERVED,file)))]));}
function evidenceDirectory(dir){const absolute=path.resolve(dir);assert.ok(absolute.startsWith(PRIVATE+path.sep),'Exclusive private M44 reference directory');
    assert.equal(fs.existsSync(absolute),false,'Never overwrite a reference evidence directory');fs.mkdirSync(absolute,{recursive:true});return absolute;}
function save(dir,name,value){fs.writeFileSync(path.join(dir,name),JSON.stringify(value,null,2)+'\n',{flag:'wx'});}
async function observe(context,page,row){row.blocked=[];row.errors=[];row.localFailures=[];
    await context.route('**/*',route=>{const r=route.request();if(allowedRequest(r.url(),r.method()))return route.continue();
        row.blocked.push({url:safeUrl(r.url()),method:r.method()});return route.abort('blockedbyclient');});
    page.on('pageerror',e=>row.errors.push({kind:'page',sha256:sha(e.message)}));
    page.on('console',m=>{if(m.type()==='error')row.errors.push({kind:'console',sha256:sha(m.text()),source:safeUrl(m.location().url)});});
    page.on('response',r=>{if(new URL(r.url()).origin===BASE&&r.status()>=400)row.localFailures.push({url:safeUrl(r.url()),status:r.status()});});
    page.on('requestfailed',r=>{if(new URL(r.url()).origin===BASE)row.localFailures.push({url:safeUrl(r.url()),failure:r.failure()?.errorText});});
}
async function collectStyles(page,roles=ROLES){return page.evaluate(({roles,properties})=>roles.map(([role,selector])=>({role,selector,
    elements:[...document.querySelectorAll(selector)].filter(n=>n.closest('[data-theory-main]')).map(n=>({tag:n.tagName,classes:n.className,
        text:String(n.textContent||'').replace(/\s+/gu,' ').trim().slice(0,160),visible:n.getClientRects().length>0,
        styles:Object.fromEntries(properties.map(p=>[p,getComputedStyle(n).getPropertyValue(p)]))}))})),{roles,properties:PROPERTIES});}
async function fontEvidence(page){await page.evaluate(async()=>{
    await Promise.all(['400 14px Manrope','500 14px Manrope','600 14px Manrope','700 14px Manrope','800 14px Manrope','700 22px Archivo','800 22px Archivo'].map(face=>document.fonts.load(face,'English Українська')));
    await document.fonts.ready;
});
    const state=await page.evaluate(()=>({faces:[...document.fonts].filter(f=>f.status==='loaded').map(f=>({family:f.family,weight:f.weight,style:f.style})),
        manrope:document.fonts.check('14px Manrope'),archivo:document.fonts.check('700 22px Archivo'),
        dpr:devicePixelRatio,innerWidth,viewportScale:visualViewport.scale,sidebar:[...document.querySelectorAll('[data-theory-aside]')].map(n=>({visible:n.getClientRects().length>0,width:n.getBoundingClientRect().width}))}));
    assert.ok(state.manrope&&state.archivo,'Reference Google fonts actually loaded');
    const cdp=await page.context().newCDPSession(page);await cdp.send('DOM.enable');await cdp.send('CSS.enable');
    const {root}=await cdp.send('DOM.getDocument'),platform=[];
    for(const selector of ['h1','[data-theory-main] .theory-example p[lang="en"]','[data-theory-main] .theory-example > p.font-medium','[data-theory-main] .theory-translation']){
        const {nodeId}=await cdp.send('DOM.querySelector',{nodeId:root.nodeId,selector});if(nodeId){const value=await cdp.send('CSS.getPlatformFontsForNode',{nodeId});platform.push({selector,fonts:value.fonts});}}
    await cdp.detach();return {...state,platformFonts:platform};
}
async function settleStyles(page,selector='.theory-exercise',maxWaitMs=2000){
    assert.ok(Number.isInteger(maxWaitMs)&&maxWaitMs>=1&&maxWaitMs<=2000,'Finite bounded style-settle window');
    return page.evaluate(async({selector,maxWaitMs})=>{
        const roots=[...document.querySelectorAll(selector)];if(!roots.length)throw new Error('Style-settle root missing');
        const frame=()=>new Promise(resolve=>requestAnimationFrame(resolve));await frame();await frame();
        const animations=[...new Set(roots.flatMap(root=>root.getAnimations({subtree:true})))];
        const finite=animations.filter(a=>a.effect?.getTiming().iterations!==Infinity&&['running','pending'].includes(a.playState));
        const started=performance.now();let timer;
        try{await Promise.race([Promise.all(finite.map(a=>a.finished.catch(()=>null))),new Promise((resolve,reject)=>{timer=setTimeout(()=>reject(new Error('Finite CSS transition did not settle within bound')),maxWaitMs);})]);}
        finally{clearTimeout(timer);}
        await frame();await frame();
        const active=[...new Set(roots.flatMap(root=>root.getAnimations({subtree:true})))].filter(a=>a.effect?.getTiming().iterations!==Infinity&&['running','pending'].includes(a.playState));
        if(active.length)throw new Error('Finite CSS transition still active after settle');
        return {selector,maxWaitMs,finiteWaited:finite.length,infiniteIgnored:animations.filter(a=>a.effect?.getTiming().iterations===Infinity).length,elapsedMs:performance.now()-started,activeFinite:active.length};
    },{selector,maxWaitMs});
}
async function feedbackState(page,reference,state){const authored=reference.key!=='PPC';
    return page.evaluate(({authored,state})=>{
        const n=document.querySelector(authored?'[data-m43-practice-ui] [x-data^="m43PracticeUi"]':'[x-data^="theoryPracticeSet"]');
        if(!n)throw new Error('Reference client practice component missing');const d=window.Alpine.$data(n);
        if(authored){d.reset(0);d.cases[0].controls.forEach((c,p)=>{
            if(state==='correct'||(state==='partial'&&p===0))d.setAnswer(0,p,c.answer);
            if(state==='wrong')d.setAnswer(0,p,c.kind==='manual'?'not the reference answer':c.options.find(o=>o.value!==c.answer).value);
        });if(state!=='initial')d.check(0);return {kind:'authored-m43',checked:d.checked[0],correct:d.isCorrect(0),parts:d.cases[0].controls.map((c,p)=>d.partCorrect(0,p))};}
        d.resetGroup('selects');d.selects.forEach((c,p)=>{
            if(state==='correct'||(state==='partial'&&p===0))d.selectAnswers[p]=d.acceptedAnswers('selects',p)[0];
            if(state==='wrong')d.selectAnswers[p]='not the reference answer';
        });if(state!=='initial')d.check('selects');return {kind:'native-ppc',checked:d.isChecked('selects'),correct:d.selects.every((c,p)=>d.isCorrect('selects',p)),parts:d.selects.map((c,p)=>d.isCorrect('selects',p))};
    },{authored,state});
}
async function capture(dir){dir=evidenceDirectory(dir);const report={at:new Date().toISOString(),base:BASE,
    policy:'fresh guest; GET .loc and Google Fonts only; DPR1/default browser100%; no application or database writes; client-only field score states',
    properties:PROPERTIES,roles:ROLES,sourceBefore:hashes(),rows:[],pass:false};
    const {chromium}=require('playwright'),browser=await chromium.launch({headless:true,executablePath:CHROME});
    try{for(const viewport of [{width:1440,height:1000},{width:390,height:844}])for(const theme of ['light','dark'])for(const reference of REFERENCES){
        const row={key:reference.key,path:reference.path,viewport,theme,pass:false,states:[],screenshots:[]};report.rows.push(row);
        const context=await browser.newContext({viewport,colorScheme:theme,deviceScaleFactor:1,serviceWorkers:'block'}),page=await context.newPage();
        const stem=reference.key+'-'+viewport.width+'-'+theme;
        try{await context.addInitScript(t=>localStorage.setItem('theme',t),theme);await observe(context,page,row);
            const response=await page.goto(BASE+reference.path,{waitUntil:'networkidle',timeout:60000});assert.equal(response.status(),200);
            row.http={status:response.status(),finalUrl:safeUrl(page.url()),contentType:response.headers()['content-type'],xRobotsTag:response.headers()['x-robots-tag']||null};
            await page.waitForFunction(()=>window.Alpine&&document.querySelector('[data-theory-main]'));
            await page.evaluate(t=>document.documentElement.classList.toggle('dark',t==='dark'),theme);
            row.fonts=await fontEvidence(page);row.styles=await collectStyles(page);
            row.metadata=await page.evaluate(()=>({title:document.title,h1:[...document.querySelectorAll('h1')].map(n=>n.textContent.trim()),canonical:document.querySelector('link[rel=canonical]')?.href||null}));
            const detail=page.locator('[data-theory-native-extension] > details').first();if(await detail.count()){
                await detail.locator(':scope > summary').click();row.openDetailStyles=await collectStyles(page,[['disclosure-summary','[data-theory-native-extension] > details[open] > summary'],['disclosure-body','[data-theory-native-extension] > details[open] .theory-section-detail-body'],['detail-example-en','[data-theory-native-extension] > details[open] .theory-example [lang="en"]'],['detail-example-uk','[data-theory-native-extension] > details[open] .theory-translation']]);await detail.locator(':scope > summary').click();}
            for(const state of ['initial','empty','partial','wrong','correct']){
                const entry={state,client:await feedbackState(page,reference,state)};
                entry.styleSettlement=await settleStyles(page,'.theory-exercise');
                entry.styles=await collectStyles(page,ROLES.filter(([role])=>['practice-feedback','practice-part-feedback','practice-choice','practice-input','practice-control-panel'].includes(role)));
                if(state==='correct')assert.equal(entry.client.correct,true);if(['empty','partial','wrong'].includes(state))assert.equal(entry.client.correct,false);
                const practice=page.locator('.theory-exercise').first(),filename=stem+'-'+state+'.png';
                await practice.screenshot({path:path.join(dir,filename),animations:'disabled',timeout:30000});entry.screenshot={file:filename,sha256:sha(fs.readFileSync(path.join(dir,filename)))};row.states.push(entry);
            }
            await feedbackState(page,reference,'initial');assert.deepEqual(row.blocked,[],'No attempted progress/AI/production writes');assert.deepEqual(row.errors,[]);assert.deepEqual(row.localFailures,[]);row.pass=true;
        }catch(e){row.failure={name:e.name,message:e.message.split('\n')[0]};}
        finally{await context.close();save(dir,stem+'.json',row);}
        console.log(JSON.stringify({reference:reference.key,width:viewport.width,theme,pass:row.pass,failure:row.failure||null}));
    }assert.deepEqual(hashes(),report.sourceBefore,'Reference ROOT source unchanged during supplement capture');report.sourceAfter=hashes();report.pass=report.rows.length===16&&report.rows.every(r=>r.pass);
    }finally{await browser.close();report.finishedAt=new Date().toISOString();save(dir,'manifest.json',report);}
    return report;
}
if(require.main===module){const [mode,dir]=process.argv.slice(2);assert.equal(mode,'--capture');capture(dir).then(r=>{if(!r.pass)process.exitCode=1;}).catch(e=>{console.error(e.stack);process.exitCode=1;});}
module.exports={BASE,SERVED,PRIVATE,REFERENCES,PROPERTIES,ROLES,allowedRequest,safeUrl,sha,collectStyles,fontEvidence,settleStyles,capture};
