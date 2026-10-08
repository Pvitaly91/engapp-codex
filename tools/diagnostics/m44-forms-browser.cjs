'use strict';
const fs = require('node:fs'), path = require('node:path'), assert = require('node:assert/strict');
const {chromium} = require('playwright');
const phase = process.argv[2]; assert.match(phase, /^(before|after)-v\d+$/);
const root = 'D:/DEV/htdocs/gramlyze.loc', base = 'http://gramlyze.loc';
const dir = path.join(root, 'storage/app/m44-forms', phase); fs.mkdirSync(dir, {recursive: true});
assert(!fs.existsSync(path.join(dir, 'result.json')));
const target = '[data-m44-author-section="m44-will-forms"]';
const urls = ['/theory/maibutni-formy/future-simple/will-vs-be-going-to', '/theory/maibutni-formy/present-continuous-for-future', '/theory/maibutni-formy/choosing-the-right-future-form'];
(async () => {
 const browser = await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe', headless:true});
 const results = []; let pass = false;
 const before = phase.startsWith('after') ? JSON.parse(fs.readFileSync(path.join(root,'storage/app/m44-forms/before-v1/result.json'))) : null;
 try {
  for (const [index,url] of urls.entries()) for (const [width,theme] of index ? [[1440,'light']] : [[1440,'light'],[1440,'dark'],[390,'light'],[390,'dark']]) {
   const key = `${index}-${width}-${theme}`, errors = [];
   const context = await browser.newContext({viewport:{width,height:900},colorScheme:theme});
   await context.addInitScript(t => localStorage.setItem('theme', t), theme);
   await context.route('**/*', route => { const r=route.request(),u=new URL(r.url()); if(r.method()==='GET'&&(u.origin===base||['fonts.googleapis.com','fonts.gstatic.com'].includes(u.hostname)))return route.continue(); return route.abort(); });
   const page=await context.newPage(); page.on('pageerror',e=>errors.push(e.message)); page.on('console',m=>{if(m.type()==='error')errors.push(m.text());});
   page.on('response',r=>{if(r.status()>=400)errors.push(`${r.status()} ${new URL(r.url()).pathname}`);});
   try {
    const response=await page.goto(base+url,{waitUntil:'networkidle',timeout:60000}); assert.equal(response.status(),200);
    await page.evaluate(()=>document.fonts.ready);
    const snapshot=await page.evaluate(selector=>{
     const block=document.querySelector(selector), main=document.querySelector('[data-theory-main]');
     const copy=main.cloneNode(true); copy.querySelector(selector)?.remove(); copy.querySelectorAll('script,style,noscript,[data-sentence-builder]').forEach(n=>n.remove());
     const clean=n=>n.textContent.replace(/\s+/g,' ').trim();
     return{title:document.title,h1:clean(document.querySelector('h1')),otherText:clean(copy),
      anchors:[...main.querySelectorAll('[id]')].map(n=>n.id), height:block?.getBoundingClientRect().height??0,
      text:block?clean(block):null,groups:block?.querySelectorAll('[data-m44-compact-group]').length??0,
      points:block?.querySelectorAll('[data-m44-basic-point]').length??0,
      overflow:block?[...block.querySelectorAll('p')].filter(n=>n.getClientRects().length && (n.getBoundingClientRect().right>innerWidth+2||n.scrollWidth>n.clientWidth+2)).length:0};
    },target);
    if(before){ const old=before.rows.find(r=>r.key===key);assert.equal(snapshot.otherText,old.otherText,'Other lesson content unchanged');assert.equal(snapshot.title,old.title);assert.equal(snapshot.h1,old.h1);assert.ok(old.anchors.every(id=>snapshot.anchors.includes(id)),'Existing anchors preserved'); }
    if(index===0){
     assert.equal(snapshot.overflow,0);assert.equal(snapshot.points,8);
     await page.locator(target).evaluate(n=>n.scrollIntoView({block:'start'}));await page.evaluate(()=>scrollBy(0,-100));
     await page.screenshot({path:path.join(dir,key+'.png')});
     if(before){
      assert.equal(snapshot.groups,2);assert.ok(snapshot.height<before.rows.find(r=>r.key===key).height*.8,'At least 20% shorter');
      const details=page.locator(target+' details[data-theory-details]');assert.equal(await details.count(),2);
      for(let i=0;i<2;i++){const summary=details.nth(i).locator(':scope > summary');await summary.focus();await summary.press('Enter');assert.equal(await page.locator(target+' details[open]').count(),1);await summary.press('Space');}
      // Every formula row stays visible while depth is closed.
      assert.equal(await page.locator(target+' [data-m44-form-row]:visible').count(),6);
      for(const id of ['m44-will-form-question-more','m44-will-form-going-question-more']) {
       await page.evaluate(id=>{location.hash=id;},id);
       await page.waitForFunction(id=>document.getElementById(id)?.closest('details')?.open,id);
       assert.equal(await page.locator(target+' details[open]').count(),1);
       await page.locator(target+' details[open] > summary').press('Enter');
      }
      await page.evaluate(()=>window.dispatchEvent(new Event('beforeprint')));
      assert.equal(await page.locator(target+' details[open]').count(),2);
      await page.evaluate(()=>window.dispatchEvent(new Event('afterprint')));
      assert.equal(await page.locator(target+' details[open]').count(),0);
     }
    }
    assert.deepEqual(errors,[]);results.push({key,url,status:response.status(),...snapshot});console.log(key,'PASS',Math.round(snapshot.height));
   }finally{await context.close();}
  }
  pass=true;
 }finally{await browser.close();fs.writeFileSync(path.join(dir,'result.json'),JSON.stringify({pass,rows:results},null,2)+'\n',{flag:'wx'});}
})().catch(e=>{console.error(e);process.exitCode=1;});
