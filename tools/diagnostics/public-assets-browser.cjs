// M3: local Apache, isolated guests, production blocked before navigation.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = path.resolve('storage/app/seo-m3-local');
const phase = process.argv[2] || 'baseline';
const mode = process.argv[3] || 'visual';
assert.match(phase, /^[a-z0-9_-]+$/i);
fs.mkdirSync(root, {recursive:true});
const base = 'http://gramlyze.loc';
const pages = [
 ['home','/'], ['theory','/theory'], ['future-perfect','/theory/future-perfect'],
 ['sentence-types','/theory/basic-grammar/sentence-types'],
 ['present-perfect','/theory/tenses/present-perfect/present-perfect-forms'],
 ['questions','/test/future-perfect/questions'],
 ['course','/courses/english-grammar-theory'],
 ['lesson','/courses/english-grammar-theory/lesson/basic-grammar/sentence-types'],
 ['verb-be','/theory/basic-grammar/verb-to-be/verb-to-be-future'],
 ['collective','/theory/imennyky-artykli-ta-kilkist/collective-nouns'],
 ['one-ones','/theory/zaimennyky-ta-vkazivni-slova/one-ones'],
 ['used-to','/theory/tenses/used-to-would'],
];
const results=[];
const safeUrl = url => {const u=new URL(url); return u.origin+u.pathname;};
async function prepare(browser, spec, record) {
 const context=await browser.newContext({viewport:spec.mobile?{width:390,height:844}:{width:1440,height:1000},
  isMobile:!!spec.mobile,hasTouch:!!spec.mobile,locale:'uk-UA',colorScheme:'light',serviceWorkers:'block',javaScriptEnabled:spec.js!==false});
 const page=await context.newPage();
 const cdp=await context.newCDPSession(page);
 // Fetch interception restricted to denied origins preserves the real HTTP cache.
 const patterns=['*://gramlyze.com/*','*://*.gramlyze.com/*','*://gramlyze.ub/*','*://*.gramlyze.ub/*'];
 if(!phase.startsWith('baseline'))patterns.push('*://cdn.tailwindcss.com/*','*://unpkg.com/alpinejs*','*://unpkg.com/@alpinejs/*');
 await cdp.send('Fetch.enable',{patterns:patterns.map(urlPattern=>({urlPattern,requestStage:'Request'}))});
 cdp.on('Fetch.requestPaused',async e=>{record.blocked.push(safeUrl(e.request.url));await cdp.send('Fetch.failRequest',{requestId:e.requestId,errorReason:'BlockedByClient'}).catch(()=>{});});
 await cdp.send('Network.enable');
 await cdp.send('Performance.enable');
 record.network=[];const network=new Map();
 cdp.on('Network.responseReceived',event=>{
  const r=event.response;
  const row={url:safeUrl(r.url),type:event.type,status:r.status,mime:r.mimeType,diskCache:!!r.fromDiskCache,document:page.url()};
  network.set(event.requestId,row);record.network.push(row);
 });
 cdp.on('Network.loadingFinished',async event=>{
  const row=network.get(event.requestId);if(!row)return;
  row.wireBytes=event.encodedDataLength;
  if(['Document','Script','Stylesheet'].includes(row.type))try{
   const body=await cdp.send('Network.getResponseBody',{requestId:event.requestId});
   row.decodedBytes=body.base64Encoded?Buffer.from(body.body,'base64').length:Buffer.byteLength(body.body);
  }catch{row.decodedBytesUnavailable=true}
 });
 page.on('console',msg=>{if(['warning','error'].includes(msg.type()))record.console.push({type:msg.type(),text:msg.text().slice(0,500).replace(/(?:sk-proj-|ghp_|AIza)[\w-]+/g,'[redacted]')});});
 page.on('pageerror',e=>record.console.push({type:'pageerror',text:e.message.slice(0,500)}));
 page.on('requestfailed',req=>record.failures.push({url:safeUrl(req.url()),error:req.failure()?.errorText}));
 page.on('response',res=>record.responses.push({url:safeUrl(res.url()),type:res.request().resourceType(),status:res.status(),mime:res.headers()['content-type']}));
 await context.addInitScript(({theme,background})=>{
  if(!localStorage.getItem('theme'))localStorage.setItem('theme',theme);
  if(!localStorage.getItem('backgroundMode'))localStorage.setItem('backgroundMode',background);
  if(!localStorage.getItem('backgroundColors'))localStorage.setItem('backgroundColors',JSON.stringify({ct:'#3050a0',cr:'#6540b0',th:'#df8620',wd:'#273644',vb:'#189060'}));
  window.__m3={lcp:0,cls:0,longTasks:0,alpineInits:0};
  addEventListener('alpine:init',()=>window.__m3.alpineInits++);
  for(const [type,fn] of [
   ['largest-contentful-paint',entry=>window.__m3.lcp=entry.startTime],
   ['layout-shift',entry=>{if(!entry.hadRecentInput)window.__m3.cls+=entry.value}],
   ['longtask',entry=>window.__m3.longTasks+=entry.duration],
  ])try{new PerformanceObserver(list=>list.getEntries().forEach(fn)).observe({type,buffered:true})}catch{}
 },{theme:spec.theme||'light',background:spec.background||'blue'});
 return {context,page,cdp};
}
async function loaded(page) {
 await page.waitForLoadState('load');
 await page.evaluate(()=>document.fonts.ready);
 await page.waitForTimeout(1200);
}
async function measurements(page,cdp) {
 const data=await page.evaluate(()=>{
  const navigation=performance.getEntriesByType('navigation')[0];
  const resources=performance.getEntriesByType('resource');
  const styles=[...document.querySelectorAll('style')].map(n=>n.textContent).join('\n');
  return {url:location.origin+location.pathname,title:document.title,theme:document.documentElement.classList.contains('dark'),
   background:document.documentElement.dataset.backgroundMode,alpine:window.Alpine?.version,livewire:!!window.Livewire,
   alpineInits:window.__m3?.alpineInits,tailwindVersion:styles.match(/tailwindcss v([\d.]+)/)?.[1],
   fcp:performance.getEntriesByName('first-contentful-paint')[0]?.startTime,lcp:window.__m3?.lcp,cls:window.__m3?.cls,longTasks:window.__m3?.longTasks,
   dcl:navigation.domContentLoadedEventEnd,load:navigation.loadEventEnd,htmlBytes:navigation.decodedBodySize,
   inlineStyleBytes:new TextEncoder().encode(styles).length,
   inlineJsBytes:[...document.scripts].filter(s=>!s.src).reduce((n,s)=>n+new TextEncoder().encode(s.textContent).length,0),
   assets:[...document.querySelectorAll('script[src],link[rel="stylesheet"]')].map(n=>({type:n.tagName,url:n.src||n.href})),
   resources:resources.map(r=>({url:new URL(r.name).origin+new URL(r.name).pathname,type:r.initiatorType,transferBytes:r.transferSize,decodedBytes:r.decodedBodySize,duration:r.duration})),
   overflow:Math.max(0,document.documentElement.scrollWidth-innerWidth),
   mainTextLength:document.querySelector('main')?.innerText.length||0,
   questionCount:window.__INITIAL_JS_TEST_QUESTIONS__?.length,
  };
 });
 data.runtime=Object.fromEntries((await cdp.send('Performance.getMetrics')).metrics.filter(m=>['ScriptDuration','LayoutDuration','RecalcStyleDuration','TaskDuration'].includes(m.name)).map(m=>[m.name,m.value]));
 return data;
}
async function stabilizeDecoration(page) {
 await page.evaluate(()=>{
  // Re-render decorative randomness only; never seed question/answer shuffling.
  const original=Math.random;let seed=1979;
  Math.random=()=>{seed=(seed*1664525+1013904223)>>>0;return seed/4294967296};
  try{window.buildShellRandomShapes?.();window.randomizeAppBackgroundIcons?.()}finally{Math.random=original}
 });
 await page.addStyleTag({content:'.app-fixed-background::before,.app-bg-vector,.app-bg-icon-inner{animation-play-state:paused!important;animation-delay:0s!important}'});
}
async function computed(page) {
 return page.evaluate(()=>{
  const props=['display','color','backgroundColor','fontFamily','fontSize','fontWeight','lineHeight','padding','margin','borderColor','borderWidth','borderRadius','boxShadow','gap','gridTemplateColumns'];
  return [...document.querySelectorAll('body,.site-header,#catalog-shell,main [class]')].filter(n=>!n.closest('#shell-random-shapes,.app-fixed-background')).slice(0,1800).map((node,index)=>{
   const css=getComputedStyle(node),r=node.getBoundingClientRect();
   return {index,tag:node.tagName,classes:typeof node.className==='string'?node.className:'',style:Object.fromEntries(props.map(p=>[p,css[p]])),width:r.width,height:r.height};
  });
 });
}
async function main(){
 const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_EXECUTABLE?{executablePath:process.env.CHROMIUM_EXECUTABLE}:{})});
 try {
  const specs=[];
  if(mode==='perf')for(const [name,url] of pages.filter(p=>['sentence-types','present-perfect','questions'].includes(p[0])))for(let run=1;run<=3;run++)specs.push({name,url,run,perf:true});
  else {
   for(const [name,url] of pages)for(const mobile of [false,true])specs.push({name,url,mobile});
   for(const [name,url] of pages.filter(p=>['sentence-types','present-perfect','questions'].includes(p[0])))for(const mobile of [false,true])specs.push({name,url,mobile,theme:'dark'});
   for(const background of ['cards','custom'])specs.push({name:'background',url:'/theory/basic-grammar/sentence-types',background});
  }
  for(const spec of specs){
   const record={...spec,time:new Date().toISOString(),blocked:[],console:[],responses:[],failures:[]};
   const {context,page,cdp}=await prepare(browser,spec,record);
   try {
    const response=await page.goto(base+spec.url,{waitUntil:'load',timeout:60000});assert.equal(response.status(),200);
    await loaded(page);
    if(spec.name==='lesson') {
     await page.evaluate(()=>{const m=window.__THEORY_COURSE_MANIFEST__,s=window.TheoryCourseProgress.createStore(m.course.slug,m.lessons),i=m.lessons.findIndex(l=>l.lesson_slug===m.lesson.lesson_slug),prior=m.lessons.slice(0,i).map(l=>l.lesson_slug);s.write({...s.read(),completedLessons:prior,unlockedLessons:[...prior,m.lesson.lesson_slug],currentLessonSlug:m.lesson.lesson_slug});});
     await page.reload({waitUntil:'load'});await loaded(page);record.coursePrerequisites='own ephemeral localStorage fixture';
    }
    // Visual snapshots compare settled navigation, not an AJAX placeholder.
    // Keep the performance window unchanged for the before/after experiment.
    if(!spec.perf&&!spec.mobile&&await page.locator('[data-theory-desktop-navigation-loader]').count()){
     await page.waitForFunction(()=>document.querySelector('[data-theory-desktop-navigation-loader]')?.getAttribute('aria-busy')==='false');
    }
    record.cold=await measurements(page,cdp);
    if(spec.perf){
     record.cold.network=JSON.parse(JSON.stringify(record.network));record.network.length=0;
     await page.reload({waitUntil:'load'});await loaded(page);record.repeat=await measurements(page,cdp);
     record.repeat.network=JSON.parse(JSON.stringify(record.network));
    }else{
     await stabilizeDecoration(page);record.computed=await computed(page);
     const stem=[phase,spec.name,spec.mobile?'mobile':'desktop',spec.theme||'light',spec.background||'blue'].join('-');
     await page.screenshot({path:path.join(root,stem+'.png')});
     const section=page.locator('main section[id^="block-"]').nth(1);
     if(await section.count()){await section.scrollIntoViewIfNeeded();await page.waitForTimeout(300);await page.screenshot({path:path.join(root,stem+'-content.png')});}
    }
    assert.equal(record.blocked.length,0,'Unexpected production/CDN request');
   }catch(error){record.error=error.message;}
   finally{await context.close();results.push(record);fs.writeFileSync(path.join(root,`${phase}-${mode}.json`),JSON.stringify(results,null,2));}
   console.log(JSON.stringify({name:spec.name,mobile:!!spec.mobile,theme:spec.theme||'light',run:spec.run,error:record.error,alpine:record.cold?.alpine,tailwind:record.cold?.tailwindVersion,console:record.console.length}));
  }
 }finally{await browser.close();}
 if(results.some(r=>r.error))process.exitCode=1;
}
module.exports={prepare,loaded,measurements,stabilizeDecoration,computed,root,base};
if(require.main===module)main().catch(error=>{console.error(error.message);process.exitCode=1});
