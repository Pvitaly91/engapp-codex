'use strict';
// Real browser shortcut probe. Never substitutes DPR, CSS zoom or page-scale
// emulation for browser zoom; an ignored shortcut is explicitly inconclusive.
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const {chromium}=require('playwright');
const BASE='http://gramlyze.loc',PRIVATE='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-1-local';
const routes=['/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms','/theory/tenses/past-perfect-vs-past-perfect-continuous','/theory/tenses/stative-verbs','/theory/tenses/used-to-would'];
const hash=b=>crypto.createHash('sha256').update(b).digest('hex');
async function geometry(page){return page.evaluate(()=>({innerWidth,outerWidth,dpr:devicePixelRatio,visualScale:visualViewport.scale,bodyZoom:getComputedStyle(document.body).zoom,rootZoom:getComputedStyle(document.documentElement).zoom,englishSize:getComputedStyle(document.querySelector('.theory-example p')).fontSize}));}
async function run(){
 const label=process.argv[2];assert.match(label,/^zoom-probe-v[1-9][0-9]*$/);const dir=path.join(PRIVATE,label);assert.equal(fs.existsSync(dir),false);fs.mkdirSync(dir,{recursive:true});
 const report={at:new Date().toISOString(),browser:'Chrome headless',viewport:{width:1440,height:1000},deviceScaleFactor:1,rows:[],emulationUsed:false,cssZoomChanged:false};
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 try{for(const [i,route]of routes.entries()){
  const context=await browser.newContext({viewport:report.viewport,deviceScaleFactor:1,colorScheme:'light',serviceWorkers:'block'}),page=await context.newPage();
  const row={path:route,status:null,before:null,after:null,shortcut:'Control+Equal',confirmed:false};report.rows.push(row);
  try{
   await context.route('**/*',r=>{const q=r.request(),u=new URL(q.url());if(q.method()==='GET'&&(u.origin===BASE||u.origin==='https://fonts.googleapis.com'||u.origin==='https://fonts.gstatic.com')){const h={...q.headers()};delete h.cookie;delete h.referer;delete h.authorization;return r.continue({headers:h});}return r.abort('blockedbyclient');});
   row.status=(await page.goto(BASE+route,{waitUntil:'networkidle',timeout:60000})).status();assert.equal(row.status,200);await page.locator('.theory-example:visible').first().waitFor({state:'visible'});await page.evaluate(()=>document.fonts.ready);
   row.before=await geometry(page);await page.keyboard.press('Control+Equal');await page.waitForTimeout(400);row.after=await geometry(page);
   row.confirmed=row.after.dpr>row.before.dpr&&row.after.innerWidth<row.before.innerWidth&&row.after.visualScale===row.before.visualScale&&row.after.bodyZoom===row.before.bodyZoom&&row.after.rootZoom===row.before.rootZoom;
   row.note=row.confirmed?'Browser layout zoom changed after genuine keyboard shortcut.':'Shortcut did not establish browser layout zoom in this headless environment; zoom coverage is unverified.';
   const file='route-'+(i+1)+'.png';await page.screenshot({path:path.join(dir,file)});row.screenshot={file,sha256:hash(fs.readFileSync(path.join(dir,file)))};
  }catch(e){row.error={name:e.name,message:e.message.split('\n')[0]};}finally{await context.close();}
 }}finally{await browser.close();report.finishedAt=new Date().toISOString();report.allZoomConfirmed=report.rows.length===4&&report.rows.every(r=>r.confirmed);fs.writeFileSync(path.join(dir,'manifest.json'),JSON.stringify(report,null,2)+'\n',{flag:'wx'});}
 console.log(JSON.stringify({rows:report.rows.map(({path,confirmed,note,error})=>({path,confirmed,note,error})),allZoomConfirmed:report.allZoomConfirmed,evidence:path.join(dir,'manifest.json')}));
 // An unsupported zoom shortcut is a retained coverage limitation, not a site failure.
 if(report.rows.some(r=>r.error))process.exitCode=1;
}
if(require.main===module)run().catch(e=>{console.error(e.message);process.exitCode=1;});
