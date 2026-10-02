'use strict';
// Focused actual-page screenshots complement the 12 acceptance states; no DOM masking/layout edits.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const {guard,BASE,targets}=require('./seo-m27-local.cjs');
(async()=>{
 const [dir,label]=process.argv.slice(2);assert.equal(path.basename(dir),'seo-m27-local');assert.match(label,/^[a-z0-9-]+$/);
 const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
 const b=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});
 const report={base:BASE,rows:[],violations:[],pass:false};
 try{
  for(const [index,width,theme]of [[0,1440,'light'],[0,390,'light'],[1,1440,'light'],[2,1440,'light'],[2,1440,'dark']]){
   const c=await b.newContext({viewport:{width,height:width===390?844:1600},colorScheme:theme}),p=await c.newPage();
   const row={path:targets[index].path,width,theme,errors:[],failed:[],httpErrors:[],screenshots:[]};report.rows.push(row);
   try{
    await guard(p,row,report.violations);const r=await p.goto(BASE+row.path,{waitUntil:'networkidle'});assert.equal(r.status(),200);
    await p.evaluate(w=>document.documentElement.classList.toggle('dark',w==='dark'),theme);
    async function shot(name,element){
     const filename=`${label}-${index}-${width}-${theme}-${name}.png`;
     if(element){await element.evaluate(n=>window.scrollTo(0,n.getBoundingClientRect().top+scrollY-160));
      await element.screenshot({path:path.join(dir,filename),animations:'disabled'});
     }else{await p.evaluate(()=>window.scrollTo(0,0));await p.screenshot({path:path.join(dir,filename),animations:'disabled'});}
     row.screenshots.push(filename);
    }
    await shot('basic');
    const d=p.locator('[data-theory-native-extension] > details').first();await d.locator('summary').click();
    assert.ok(await d.evaluate(n=>n.open));await shot('detail',d.locator('xpath=ancestor::section[contains(@class,"theory-native-block")]'));await d.locator('summary').click();
    if(index===0&&width===390){const table=p.locator('.theory-table-scroll');await shot('table',table.locator('xpath=ancestor::section[contains(@class,"theory-native-block")]'));
     const sizes=await table.evaluate(n=>({client:n.clientWidth,scroll:n.scrollWidth}));assert.ok(sizes.scroll>sizes.client);
     await table.evaluate(n=>n.scrollLeft=n.scrollWidth-n.clientWidth);assert.ok(await table.evaluate(n=>n.scrollLeft>0));await shot('table-scrolled',table);row.localTable=sizes;
    }
    if(index===2&&theme==='light')for(const section of [4,5]){
     const block=p.locator(`[data-theory-section^="m27-c2-section-${section}-"]`).first().locator('xpath=ancestor::section[contains(@class,"theory-native-block")]');
     assert.equal(await block.locator('details[data-theory-details]').count(),4);
     for(const item of await block.locator('details').all())await item.locator('summary').click();await shot('section-'+section,block);
    }
    if(width===1440&&theme==='light')await shot('practice',p.locator('[x-data^="theoryPracticeSet("] .theory-section-body'));
    assert.deepEqual(row.errors,[]);assert.deepEqual(row.failed,[]);assert.deepEqual(row.httpErrors,[]);row.pass=true;
   }finally{await c.close();}
  }
  assert.deepEqual(report.violations,[]);report.pass=true;
 }finally{await b.close();fs.writeFileSync(path.join(dir,label+'-elements.json'),JSON.stringify(report,null,2),{flag:'wx'});}
 console.log(JSON.stringify({pass:report.pass,rows:report.rows.length,screenshots:report.rows.flatMap(r=>r.screenshots).length}));
})().catch(e=>{console.error(e.message);process.exitCode=1;});
