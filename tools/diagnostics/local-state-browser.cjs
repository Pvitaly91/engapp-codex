// Behavioral acceptance against the real local UI. Session material never leaves memory.
const assert=require('node:assert/strict');
const crypto=require('node:crypto');
const fs=require('node:fs');
const path=require('node:path');
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const base='http://gramlyze.loc', target=base+'/test/future-perfect/questions';
const out=path.resolve('storage/app/seo-m2-local');fs.mkdirSync(out,{recursive:true});
const phase=process.argv[2]||'acceptance';const results=[];
const digest=value=>crypto.createHash('sha256').update(JSON.stringify(value)).digest('hex');
async function snapshot(page){
 return page.evaluate(()=>({answered:state.answered,correct:state.correct,activeCardIdx:state.activeCardIdx,
  // Laravel's ConvertEmptyStringsToNull normalizes untouched input slots on POST.
  // Compare visible progress, preserving slot positions and every nonempty value.
  order:state.items.map(q=>q.uuid||q.id),items:state.items.map(q=>({chosen:q.chosen,manual:q.manualInputsBySlot?.map(value=>value??''),done:q.done,activeSlot:q.activeSlot}))}));
}
async function settled(page){
 await page.waitForTimeout(400);
 await page.evaluate(async()=>{await JS_TEST_SAVE_QUEUE;});
 await page.waitForTimeout(200);
}
async function ready(page){
 await page.waitForFunction(()=>window.__INITIAL_JS_TEST_QUESTIONS__?.length===84&&typeof state!=='undefined'&&state.items.length===84,{timeout:30000});
 await settled(page);
 await page.evaluate(()=>{
  if(window.__m2PersistObserved)return;
  window.__m2PersistObserved=true;
  const original=persistState;
  persistState=function(value,immediate){
   window.__m2event({kind:'snapshot-enqueue',time:Date.now(),answered:value?.answered,activeCardIdx:value?.activeCardIdx,immediate:!!immediate}).catch(()=>{});
   return original.apply(this,arguments);
  };
 });
}
(async()=>{
 const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_EXECUTABLE?{executablePath:process.env.CHROMIUM_EXECUTABLE}:{})});
 async function contextFor(mobile,events,fault={value:null}){
  const context=await browser.newContext({viewport:mobile?{width:390,height:844}:{width:1440,height:1000},isMobile:mobile,hasTouch:mobile,locale:'uk-UA',serviceWorkers:'block'});
  await context.route('**/*',async route=>{
   const req=route.request(),u=new URL(req.url());
   if(/(^|\.)gramlyze\.(com|ub)$/i.test(u.hostname)||(req.isNavigationRequest()&&u.hostname!=='gramlyze.loc')||(!['GET','HEAD'].includes(req.method())&&u.hostname!=='gramlyze.loc')){
    events.push({kind:'blocked',host:u.hostname,path:u.pathname,time:Date.now()});return route.abort('blockedbyclient');
   }
   if(u.pathname==='/test/future-perfect/questions/state'&&req.method()==='POST'&&fault.value){
    const action=fault.value;fault.value=null;events.push({kind:'injected-'+action,time:Date.now()});
    if(action==='reject')return route.abort('failed');
    if(action==='delay')await new Promise(resolve=>setTimeout(resolve,1000));
   }
   return route.continue();
  });
  await context.exposeBinding('__m2event',(_,value)=>events.push(value));
  await context.addInitScript(()=>{
   const note=(kind,extra={})=>window.__m2event({kind,time:Date.now(),...extra}).catch(()=>{});
   for(const kind of ['pagehide','visibilitychange','beforeunload'])addEventListener(kind,()=>note(kind,{visibility:document.visibilityState}));
   const fetchOriginal=window.fetch;let serial=0;
   window.fetch=async function(input,options){
    const u=new URL(typeof input==='string'?input:input.url,location.href);
    if(u.hostname!=='gramlyze.loc'||!u.pathname.endsWith('/state'))return fetchOriginal.apply(this,arguments);
    const id=++serial;let value;try{value=JSON.parse(options?.body||'null')}catch{}
    note('snapshot-send',{id,path:u.pathname,answered:value?.state?.answered,activeCardIdx:value?.state?.activeCardIdx,savedAt:value?.state?.__meta?.saved_at});
    try{const response=await fetchOriginal.apply(this,arguments);note('fetch-resolved',{id,status:response.status,ok:response.ok});return response;}
    catch(error){note('fetch-rejected',{id,name:error.name,message:error.message});throw error;}
   };
  });
  return context;
 }
 try{
  for(const mobile of [false,true]){
   const r={mobile,checks:[],events:[],started:new Date().toISOString()},fault={value:null};
   const context=await contextFor(mobile,r.events,fault);const page=await context.newPage();
   const ids=new WeakMap();let seq=0;
   page.on('request',req=>{if(new URL(req.url()).pathname.endsWith('/state')){ids.set(req,++seq);r.events.push({kind:'request',id:seq,time:Date.now(),resourceType:req.resourceType(),navigation:req.isNavigationRequest()});}});
   page.on('response',res=>{if(ids.has(res.request()))r.events.push({kind:'response',id:ids.get(res.request()),status:res.status(),time:Date.now()});});
   page.on('requestfinished',req=>{if(ids.has(req))r.events.push({kind:'requestfinished',id:ids.get(req),time:Date.now()});});
   page.on('requestfailed',req=>{if(ids.has(req))r.events.push({kind:'requestfailed',id:ids.get(req),time:Date.now(),error:req.failure()?.errorText});});
   page.on('pageerror',error=>r.events.push({kind:'pageerror',message:error.message,time:Date.now()}));
   const check=(name,value)=>{assert.ok(value,name);r.checks.push({name,pass:true});console.log(JSON.stringify({mobile,check:name,pass:true}));};
   try{
    const response=await page.goto(target,{waitUntil:'load'});check('guest-200',response.status()===200);await ready(page);
    check('new-guest-empty',(await snapshot(page)).answered===0);
    const indices=await page.evaluate(()=>state.items.map((q,i)=>({q,i})).filter(({q,i})=>q.answers?.length===1&&document.querySelector(`article[data-idx="${i}"] button[data-options-toggle]`)).slice(0,4).map(({i})=>i));
    assert.equal(indices.length,4);
    for(const index of indices.slice(0,2)){
     r.events.push({kind:'user-answer',index,time:Date.now()});
     const card=page.locator(`article[data-idx="${index}"]`);
     await card.locator('button[data-options-toggle]').click();
     const answer=await page.evaluate(i=>state.items[i].answers[state.items[i].activeSlot],index);
     await card.locator('button[data-opt='+JSON.stringify(answer)+']').click();
    }
    await settled(page);const expectedA=await snapshot(page);check('A-two-answers',expectedA.answered===2&&expectedA.correct===2);
    await page.reload({waitUntil:'load'});await ready(page);
    const actualA=await snapshot(page);
    r.A={expectedHash:digest(expectedA),restoredHash:digest(actualA),expectedCounters:{answered:expectedA.answered,correct:expectedA.correct,active:expectedA.activeCardIdx},actualCounters:{answered:actualA.answered,correct:actualA.correct,active:actualA.activeCardIdx},orderEqual:digest(actualA.order)===digest(expectedA.order),changedItems:expectedA.items.map((item,index)=>({index,fields:Object.keys(item).filter(key=>JSON.stringify(item[key])!==JSON.stringify(actualA.items[index]?.[key]))})).filter(i=>i.fields.length)};
    console.log(JSON.stringify({mobile,A:r.A}));
    check('A-reload-answers-position-counters-order',digest(actualA)===digest(expectedA));
    const input=()=>page.locator(`article[data-idx="${indices[2]}"] input[data-manual-gap]:not([disabled])`).first();
    r.events.push({kind:'rapid-user-edits',time:Date.now()});
    for(const value of ['w','wi','wil'])await input().fill(value);
    await settled(page);const expectedB=await snapshot(page);
    await page.reload({waitUntil:'load'});await ready(page);check('B-latest-rapid-snapshot',digest(await snapshot(page))===digest(expectedB));
    // Genuine internal link, then browser history back.
    const link=page.locator('a[href="'+base+'/theory"]').first();
    if(await link.isVisible())await link.click();else{await page.getByRole('button',{name:'Меню',exact:true}).click();await page.locator('a[href="'+base+'/theory"]').filter({visible:true}).first().click();}
    await page.waitForURL('**/theory');await page.goBack({waitUntil:'load'});await ready(page);
    check('C-internal-navigation-return',digest(await snapshot(page))===digest(expectedB));
    // Reload before debounce can finish: local snapshot must preserve the newest edit.
    await input().fill('will');const expectedC=await snapshot(page);
    await page.reload({waitUntil:'load'});await ready(page);check('C-reload-near-autosave',digest(await snapshot(page))===digest(expectedC));
    // One real request failure is kept in evidence, followed by a successful save.
    fault.value='reject';await input().fill('willa');await settled(page);
    check('D-controlled-failure-observed',r.events.some(e=>e.kind==='injected-reject')&&r.events.some(e=>e.kind==='fetch-rejected'));
    fault.value='delay';await input().fill('willb');await page.waitForTimeout(350);await input().fill('willc');await settled(page);await settled(page);
    const expectedD=await snapshot(page);await page.reload({waitUntil:'load'});await ready(page);
    check('D-queue-recovers-and-newest-wins',digest(await snapshot(page))===digest(expectedD));
    // Move away before clearing: pagehide cannot repopulate the removed snapshots.
    const expectedServer=await snapshot(page);
    const keys=await page.evaluate(()=>window.JS_TEST_PERSISTENCE.storageKeys);
    await page.goto(base+'/theory',{waitUntil:'load'});
    await page.evaluate(keys=>{for(const key of keys){localStorage.removeItem(key);sessionStorage.removeItem(key)}},keys);
    await page.goto(target,{waitUntil:'load'});await ready(page);
    const restoredServer=await snapshot(page);
    check('E-server-only-restore-same-session',digest(restoredServer)===digest(expectedServer));
    r.serverRestore={localSnapshotsRemoved:true,cookiesKeptInMemoryOnly:true,expectedHash:digest(expectedServer),restoredHash:digest(restoredServer)};
    await page.locator(`article[data-idx="${indices[2]}"]`).scrollIntoViewIfNeeded();
    await page.screenshot({path:path.join(out,`${phase}-${mobile?'mobile':'desktop'}.png`)});
    await page.goto(target+'/step',{waitUntil:'load'});await page.waitForFunction(()=>typeof state!=='undefined'&&state.items?.length===84);await settled(page);
    check('F-other-mode-no-answers',await page.evaluate(()=>!state.items.some(q=>q.done||q.chosen?.some(Boolean))));
    await page.goto(base+'/test/future-perfect/forms',{waitUntil:'load'});await ready(page);
    check('F-other-test-no-answers',(await snapshot(page)).answered===0);
    const guestEvents=[];const guest=await contextFor(mobile,guestEvents);const guestPage=await guest.newPage();
    await guestPage.goto(target,{waitUntil:'load'});await ready(guestPage);check('F-other-guest-no-progress',(await snapshot(guestPage)).answered===0);await guest.close();
    check('no-production-attempt',!r.events.some(e=>e.kind==='blocked'));
   }catch(error){r.error=error.message;console.log(JSON.stringify({mobile,error:r.error}));}
   finally{r.events.push({kind:'closing-context',time:Date.now()});await context.close();r.finished=new Date().toISOString();results.push(r);fs.writeFileSync(path.join(out,`state-${phase}.json`),JSON.stringify(results,null,2));}
  }
 }finally{await browser.close();}
 if(results.some(r=>r.error))process.exitCode=1;
})().catch(error=>{console.error(error.message);process.exitCode=1});
