'use strict';
// Focused real .loc guest pages only, plus exact DOM-after-reload fidelity and M26 practice.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const {JSDOM}=require('jsdom'),{guard,BASE,targets,fidelity,practice}=require('./seo-m28-local.cjs');
(async()=>{
 const [dir,label]=process.argv.slice(2);assert.equal(path.basename(dir),'seo-m28-local');assert.match(label,/^[a-z0-9-]+$/);
 const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
 const b=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE}),report={rows:[],m26:[],violations:[],pass:false};
 try{
  for(const [index,width,theme]of [[0,1440,'light'],[0,390,'light'],[1,1440,'light'],[2,1440,'light'],[2,1440,'dark']]){
   const c=await b.newContext({viewport:{width,height:width===390?844:1000},colorScheme:theme}),p=await c.newPage();
   const row={path:targets[index].path,width,theme,errors:[],httpErrors:[],failed:[],screenshots:[]};report.rows.push(row);
   try{
    await guard(p,row,report.violations);await p.goto(BASE+row.path,{waitUntil:'networkidle'});
    await p.reload({waitUntil:'networkidle'});const dom=new JSDOM(await p.content());fidelity(dom.window.document,targets[index]);dom.window.close();row.exactReloadFidelity=true;
    await p.evaluate(w=>document.documentElement.classList.toggle('dark',w==='dark'),theme);
    const name=`${label}-${index}-${width}-${theme}`;await p.screenshot({path:path.join(dir,name+'-basic.png')});row.screenshots.push(name+'-basic.png');
    if(width===390){
     const table=p.locator('.theory-table-scroll');row.table=await table.evaluate(n=>({client:n.clientWidth,scroll:n.scrollWidth}));
     await table.screenshot({path:path.join(dir,name+'-table.png')});row.screenshots.push(name+'-table.png');
     if(row.table.scroll>row.table.client){await table.evaluate(n=>n.scrollLeft=n.scrollWidth-n.clientWidth);assert.ok(await table.evaluate(n=>n.scrollLeft>0));
      await table.screenshot({path:path.join(dir,name+'-table-scrolled.png')});row.screenshots.push(name+'-table-scrolled.png');}
    }
    if(width===1440&&theme==='light'){
     await p.setViewportSize({width,height:2400});const root=p.locator('[x-data^="theoryPracticeSet("]');
     await root.evaluate(n=>window.scrollTo(0,n.getBoundingClientRect().top+scrollY-140));
     await root.screenshot({path:path.join(dir,name+'-practice.png')});row.screenshots.push(name+'-practice.png');
    }
    assert.deepEqual(row.errors,[]);assert.deepEqual(row.failed,[]);assert.deepEqual(row.httpErrors,[]);row.pass=true;
   }finally{await c.close();}
  }
  const m26=JSON.parse(fs.readFileSync(path.resolve(__dirname,'../../database/content-patches/m26-ppc-interactive-practice.v1.json'),'utf8'));
  for(const t of m26.targets){
   const c=await b.newContext(),p=await c.newPage(),row={path:'/theory/tenses/past-perfect-continuous/'+t.slug,errors:[],httpErrors:[],failed:[]};report.m26.push(row);
   try{
    await guard(p,row,report.violations);await p.goto(BASE+row.path,{waitUntil:'networkidle'});
    row.practice=await practice(p,{slug:t.slug,after:{page:{blocks:[{type:'practice-set',body:JSON.stringify(t.practice.body_data)}]}}});
    assert.deepEqual(row.errors,[]);assert.deepEqual(row.failed,[]);assert.deepEqual(row.httpErrors,[]);row.pass=true;
   }finally{await c.close();}
  }
  assert.deepEqual(report.violations,[]);report.pass=true;
 }finally{await b.close();fs.writeFileSync(path.join(dir,label+'-elements.json'),JSON.stringify(report,null,2),{flag:'wx'});}
 console.log(JSON.stringify({pass:report.pass,reloadFidelity:report.rows.length,m26Practice:report.m26.length,screenshots:report.rows.flatMap(r=>r.screenshots).length}));
})().catch(e=>{console.error(e.message);process.exitCode=1;});
