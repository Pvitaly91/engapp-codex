const fs=require('node:fs');
const path=require('node:path');
const assert=require('node:assert/strict');
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const {prepare,loaded,stabilizeDecoration,root,base}=require('./public-assets-browser.cjs');
const targets=[
 ['hero-rules','/theory/tenses/present-perfect/present-perfect-forms','[data-theory-main] > section.grid'],
 ['forms-grid','/theory/basic-grammar/verb-to-be/verb-to-be-future','section[id^="block-"]:has(h2:has-text("Форма will be"))'],
 ['comparison-table','/theory/basic-grammar/verb-to-be/verb-to-be-future','section[id^="block-"]:has(table)'],
 ['usage-panels','/theory/imennyky-artykli-ta-kilkist/collective-nouns','section[id^="block-"]:has(h2:has-text("Як вибирати форму"))'],
 ['mistakes-grid','/theory/tenses/used-to-would','section[id^="block-"]:has(h2:has-text("Типові помилки"))'],
];
(async()=>{
 const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_EXECUTABLE?{executablePath:process.env.CHROMIUM_EXECUTABLE}:{})});
 const rows=[];
 try{
  for(const [name,url,selector]of targets)for(const mobile of [false,true]){
   const r={name,url,mobile,blocked:[],console:[],responses:[],failures:[]};
   const {context,page}=await prepare(browser,{mobile},r);
   await context.addInitScript(()=>{
    window.__m3shifts=[];
    new PerformanceObserver(list=>list.getEntries().forEach(e=>{if(!e.hadRecentInput)window.__m3shifts.push({value:e.value,time:e.startTime,sources:e.sources.map(s=>({tag:s.node?.tagName,classes:typeof s.node?.className==='string'?s.node.className:'',previous:s.previousRect.toJSON(),current:s.currentRect.toJSON()}))})})).observe({type:'layout-shift',buffered:true});
   });
   try{
    await page.goto(base+url,{waitUntil:'load'});await loaded(page);
    const block=page.locator(selector).first();await block.scrollIntoViewIfNeeded();await page.waitForTimeout(500);await stabilizeDecoration(page);
    r.details=await block.evaluate(el=>({textLength:el.innerText.length,strong:el.querySelectorAll('strong').length,code:el.querySelectorAll('code').length,bold:[...el.querySelectorAll('[class]')].filter(n=>Number(getComputedStyle(n).fontWeight)>=600).length,tableRows:el.querySelectorAll('tbody tr').length,width:el.getBoundingClientRect().width,visible:!!el.getBoundingClientRect().height}));
    assert.ok(r.details.visible&&r.details.textLength>60);assert.ok(r.details.strong+r.details.code+r.details.bold>0);
    const text=await block.innerText();assert.doesNotMatch(text,/<\/?(?:strong|span)\b/);
    r.shifts=await page.evaluate(()=>window.__m3shifts);
    await page.screenshot({path:path.join(root,`renderer-${name}-${mobile?'mobile':'desktop'}.png`)});
    assert.equal(r.blocked.length,0);r.pass=true;
   }catch(e){r.error=e.message;r.pass=false}
   finally{await context.close();rows.push(r);fs.writeFileSync(path.join(root,'renderers.json'),JSON.stringify(rows,null,2));}
   console.log(JSON.stringify({name,mobile,pass:r.pass,details:r.details,error:r.error}));
  }
 }finally{await browser.close()}
 if(rows.some(r=>!r.pass))process.exitCode=1;
})().catch(e=>{console.error(e.message);process.exitCode=1});
