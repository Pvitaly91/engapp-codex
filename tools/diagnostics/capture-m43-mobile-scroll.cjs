'use strict';
// Supplement tall full-page capture with overlapping real viewport screenshots.
// No DOM/style/content injection, access override, answers, or persistent profile.
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const qa=require('./seo-m43-local.cjs');
const ROOT='D:/DEV/htdocs/gramlyze.loc',PRIVATE=path.join(ROOT,'storage/app/seo-m43-local');
const CHROME='C:/Program Files/Google/Chrome/Application/chrome.exe';
const target=qa.targets.find(t=>t.key==='past-perfect-comparison');
const sha=x=>crypto.createHash('sha256').update(x).digest('hex');
async function learnerSnapshot(page){return page.evaluate(()=>{
    const norm=s=>String(s??'').replace(/\s+/gu,' ').trim();
    return {title:document.title,h1:[...document.querySelectorAll('h1')].map(n=>norm(n.textContent)),
        canonical:document.querySelector('link[rel="canonical"]')?.href,
        sections:[...document.querySelectorAll('[data-m43-author-section]')].map(n=>({id:n.id,text:norm(n.textContent)})),
        prompts:[...document.querySelectorAll('[data-m43-author-prompt]')].map(n=>norm(n.textContent)),
        openDetails:document.querySelectorAll('[data-m43-basic-point] details[open]').length};
});}
async function run(label){
    assert.match(label,/^mobile-scroll-[a-z0-9-]+$/u);const dir=path.join(PRIVATE,label);
    assert.equal(fs.existsSync(dir),false,'Exclusive viewport evidence');fs.mkdirSync(dir,{recursive:true});
    const report={startedAt:new Date().toISOString(),url:qa.BASE+target.path,viewport:{width:390,height:844},deviceScaleFactor:1,
        sourceBefore:qa.sourceHashes(),servedBefore:qa.sourceHashes(ROOT),rows:[],pass:false,
        purpose:'Real overlapping viewport screenshots from top to footer; independent supplement to tall full-page PNG repeat artifact.',
        limitations:['Fresh guest, external fonts blocked.','Only viewport screenshots; no synthetic stitching or image editing.','No genuine browser zoom claim.','Only GET gramlyze.loc; no DB, source, server, production or course progress writes.']};
    assert.deepEqual(report.sourceBefore,report.servedBefore);
    const{chromium}=require('playwright'),browser=await chromium.launch({headless:true,executablePath:CHROME});
    try{for(const theme of ['light','dark']){
        const row={theme,frames:[],blockedExternal:[],blockedNonGET:[],localFailures:[],localHttpErrors:[],pageErrors:[],consoleErrors:[],pass:false};report.rows.push(row);
        const context=await browser.newContext({viewport:report.viewport,colorScheme:theme,deviceScaleFactor:1,serviceWorkers:'block'}),page=await context.newPage();
        try{
            await context.route('**/*',route=>{const r=route.request();if(qa.allowedRequest(r.url(),r.method()))return route.continue();
                (r.method()==='GET'?row.blockedExternal:row.blockedNonGET).push({url:qa.safeUrl(r.url()),method:r.method()});return route.abort('blockedbyclient');});
            page.on('pageerror',e=>row.pageErrors.push({name:e.name,messageSha256:sha(e.message)}));
            page.on('requestfailed',r=>{if(new URL(r.url()).origin===qa.BASE)row.localFailures.push({url:qa.safeUrl(r.url()),reason:r.failure()?.errorText});});
            page.on('response',r=>{if(new URL(r.url()).origin===qa.BASE&&r.status()>=400)row.localHttpErrors.push({url:qa.safeUrl(r.url()),status:r.status()});});
            page.on('console',m=>{if(m.type()==='error')row.consoleErrors.push({source:qa.safeUrl(m.location().url),messageSha256:sha(m.text())});});
            const response=await page.goto(qa.BASE+target.path,{waitUntil:'networkidle',timeout:60000});
            assert.equal(response.status(),200);assert.equal(page.url(),qa.BASE+target.path);row.httpStatus=response.status();
            await page.locator('[data-theory-main]').waitFor({state:'visible'});
            await page.waitForFunction(selector=>window.Alpine?.$data(document.querySelector(selector))?.cases?.length===6,qa.PRACTICE_COMPONENT_SELECTOR);
            row.actualTheme=await page.evaluate(()=>document.documentElement.classList.contains('dark')?'dark':'light');
            assert.equal(row.actualTheme,theme,'Native theme follows fresh browser color preference; do not inject a theme class');
            const before=await learnerSnapshot(page);assert.equal(before.openDetails,0);row.learnerBeforeSha256=sha(JSON.stringify(before));
            let previous=-1,finished=false;
            for(let index=0;index<60;index++){
                const geometry=await page.evaluate(()=>{
                    const f=document.querySelector('footer'),h=document.querySelector('header');
                    return{scrollY,innerHeight,documentHeight:document.documentElement.scrollHeight,headerHeight:h?.getBoundingClientRect().height||0,
                        footerTop:f?.getBoundingClientRect().top,footerBottom:f?.getBoundingClientRect().bottom,
                        mainOverflow:Math.max(0,document.querySelector('[data-theory-main]').scrollWidth-document.querySelector('[data-theory-main]').clientWidth)};
                });
                assert.ok(geometry.scrollY>previous||index===0,'Scrolling advances');assert.equal(geometry.mainOverflow,0);
                const filename=theme+'-'+String(index+1).padStart(2,'0')+'.png';await page.screenshot({path:path.join(dir,filename),animations:'disabled'});
                row.frames.push({...geometry,file:filename,sha256:sha(fs.readFileSync(path.join(dir,filename)))});
                if(geometry.scrollY+geometry.innerHeight>=geometry.documentHeight-1){assert.ok(geometry.footerBottom<=geometry.innerHeight+1);finished=true;break;}
                previous=geometry.scrollY;
                await page.evaluate(()=>scrollBy(0,Math.floor(innerHeight*0.65)));
                await page.waitForTimeout(80);
            }
            assert.equal(finished,true,'Reached the real footer within bounded viewport captures');
            assert.equal(row.frames[0].scrollY,0,'Starts at page top');
            row.frames.slice(1).forEach((frame,i)=>assert.ok(frame.scrollY+frame.headerHeight<=row.frames[i].scrollY+row.frames[i].innerHeight,'Visible viewport intervals overlap despite sticky header'));
            const after=await learnerSnapshot(page);assert.deepEqual(after,before,'Learner DOM/content/access unchanged during scroll');row.learnerAfterSha256=sha(JSON.stringify(after));
            for(const key of ['blockedNonGET','localFailures','localHttpErrors','pageErrors'])assert.deepEqual(row[key],[]);
            row.consoleErrors=row.consoleErrors.map(e=>({...e,expectedExternalBlock:row.blockedExternal.some(b=>b.url===e.source)}));assert.ok(row.consoleErrors.every(e=>e.expectedExternalBlock));row.pass=true;
        }catch(e){row.failure={name:e.name,message:e.message.split('\n')[0]};}finally{await context.close();fs.writeFileSync(path.join(dir,theme+'.json'),JSON.stringify(row,null,2),{flag:'wx'});}
        console.log(JSON.stringify({theme,pass:row.pass,frames:row.frames.length,failure:row.failure||null}));
    }assert.deepEqual(qa.sourceHashes(),report.sourceBefore);assert.deepEqual(qa.sourceHashes(ROOT),report.servedBefore);report.pass=report.rows.length===2&&report.rows.every(r=>r.pass);
    }finally{await browser.close();report.finishedAt=new Date().toISOString();fs.writeFileSync(path.join(dir,'manifest.json'),JSON.stringify(report,null,2),{flag:'wx'});}return report;
}
if(require.main===module)run(process.argv[2]).then(r=>{if(!r.pass)process.exitCode=1;}).catch(e=>{console.error(e.message);process.exitCode=1;});
module.exports={run,learnerSnapshot};
