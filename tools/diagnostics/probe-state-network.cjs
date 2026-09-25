// Local-only request lifecycle comparison. Never records headers, cookies or CSRF.
const fs=require('node:fs');
const path=require('node:path');
const {classifyStateRequest}=require('./state-request-classification.cjs');
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const out=path.resolve('storage/app/seo-m2-local');
fs.mkdirSync(out,{recursive:true});
const base='http://gramlyze.loc';
const results=[];
(async()=>{
 const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_EXECUTABLE?{executablePath:process.env.CHROMIUM_EXECUTABLE}:{})});
 try {
  for(const interceptAll of [true,false]){
   const events=[]; const context=await browser.newContext({serviceWorkers:'block',viewport:{width:1440,height:1000}});
   const block=async route=>{
    const u=new URL(route.request().url());
    if(/(^|\.)gramlyze\.(com|ub)$/i.test(u.hostname))return route.abort('blockedbyclient');
    return route.continue();
   };
   await context.route(interceptAll?'**/*':/^https?:\/\/([^/]+\.)?gramlyze\.(com|ub)([:/]|$)/i,block);
   await context.exposeBinding('__m2event',(_,value)=>events.push({...value,receivedAt:Date.now()}));
   await context.addInitScript(()=>{
    const record=(kind,extra={})=>window.__m2event({kind,time:Date.now(),...extra}).catch(()=>{});
    for(const name of ['pagehide','pageshow','visibilitychange','beforeunload'])addEventListener(name,()=>record(name,{visibility:document.visibilityState}));
    const original=window.fetch;let seq=0;
    window.fetch=async function(input,options){
     const url=new URL(typeof input==='string'?input:input.url,location.href);
     if(url.origin!==location.origin||!url.pathname.endsWith('/state'))return original.apply(this,arguments);
     const id=++seq;let data;try{data=JSON.parse(options?.body||'null')}catch{}
     record('fetch-call',{id,path:url.pathname,method:options?.method,started:data?.state?.__meta?.started,answered:data?.state?.answered});
     try {const response=await original.apply(this,arguments);record('fetch-resolved',{id,status:response.status,ok:response.ok});return response;}
     catch(error){record('fetch-rejected',{id,name:error.name,message:error.message});throw error;}
    };
   });
   const page=await context.newPage();const ids=new WeakMap();let seq=0;
   const detail=req=>({id:ids.get(req),path:new URL(req.url()).pathname,method:req.method(),resourceType:req.resourceType(),navigation:req.isNavigationRequest()});
   page.on('request',req=>{if(new URL(req.url()).pathname.endsWith('/state')){ids.set(req,++seq);events.push({kind:'request',time:Date.now(),...detail(req)});}});
   page.on('response',res=>{if(ids.has(res.request()))events.push({kind:'response',time:Date.now(),...detail(res.request()),status:res.status(),headers:{'content-length':res.headers()['content-length'],'content-type':res.headers()['content-type'],'transfer-encoding':res.headers()['transfer-encoding']}});});
   page.on('requestfinished',req=>{if(ids.has(req))events.push({kind:'requestfinished',time:Date.now(),...detail(req)});});
   page.on('requestfailed',req=>{if(ids.has(req))events.push({kind:'requestfailed',time:Date.now(),...detail(req),error:req.failure()?.errorText});});
   const response=await page.goto(base+'/test/future-perfect/questions',{waitUntil:'load'});
   await page.waitForFunction(()=>window.__INITIAL_JS_TEST_QUESTIONS__?.length===84&&typeof persistState==='function');
   await page.waitForTimeout(1800);
   const diagnostics=await page.evaluate(()=>({count:window.__INITIAL_JS_TEST_QUESTIONS__.length,mode:window.JS_TEST_PERSISTENCE.mode,answered:state.answered,items:state.items.length}));
   const requests=events.filter(e=>e.kind==='request').map(request=>{
    const responseEvent=events.find(e=>e.kind==='response'&&e.id===request.id);
    const failed=events.find(e=>e.kind==='requestfailed'&&e.id===request.id);
    const resolved=events.find(e=>e.kind==='fetch-resolved'&&e.id===request.id);
    const rejected=events.find(e=>e.kind==='fetch-rejected'&&e.id===request.id);
    const evidence={status:responseEvent?.status,fetchResolved:resolved?.ok===true,fetchRejected:!!rejected,error:failed?.error,
     navigationBeforeFailure:!!failed&&events.some(e=>['pagehide','beforeunload'].includes(e.kind)&&e.time>=request.time&&e.time<=failed.time)};
    return {id:request.id,...evidence,classification:classifyStateRequest(evidence)};
   });
   results.push({interceptAll,status:response.status(),diagnostics,requests,events});
   events.push({kind:'closing-context',time:Date.now()});await context.close();
   console.log(JSON.stringify(results.at(-1)));
  }
 }finally{await browser.close();fs.writeFileSync(path.join(out,'state-network-comparison.json'),JSON.stringify(results,null,2));}
})().catch(e=>{console.error(e.message);process.exitCode=1});
