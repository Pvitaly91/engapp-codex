'use strict';
// Real guest .loc only: focused visible merged text and all four M26 practice panels.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const {guard,BASE,practice}=require('./seo-m28-local.cjs');
async function run(){
 const [dir,label]=process.argv.slice(2);assert.equal(path.basename(dir),'seo-m28-detail-quality');assert.match(label,/^[a-z0-9-]+$/u);
 const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');const b=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});
 const report={focus:[],m26:[],violations:[],pass:false};
 try{
  for(const [route,needle]of [
   ['/theory/clauses-and-linking-words/concessive-and-contrastive-structures','Попереджали саме Нору.'],
   ['/theory/basic-grammar/word-order/advanced-fronting-and-emphasis','У художньому тексті трапляються особливі стилізації'],
  ]){
   const c=await b.newContext({viewport:{width:1440,height:1000}}),p=await c.newPage(),row={path:route,errors:[],httpErrors:[],failed:[]};report.focus.push(row);
   try{await guard(p,row,report.violations);await p.goto(BASE+route,{waitUntil:'networkidle'});
    const point=p.locator('article.theory-item').filter({hasText:needle});assert.equal(await point.count(),1);assert.equal(await point.locator('details').count(),0);
    row.screenshot=label+'-visible-'+report.focus.length+'.png';await point.screenshot({path:path.join(dir,row.screenshot)});
    assert.deepEqual(row.errors,[]);assert.deepEqual(row.httpErrors,[]);assert.deepEqual(row.failed,[]);row.pass=true;
   }finally{await c.close();}
  }
  const source=JSON.parse(fs.readFileSync(path.resolve(__dirname,'../../database/content-patches/m26-ppc-interactive-practice.v1.json'),'utf8'));
  for(const t of source.targets){const c=await b.newContext(),p=await c.newPage(),row={path:'/theory/tenses/past-perfect-continuous/'+t.slug,errors:[],httpErrors:[],failed:[]};report.m26.push(row);
   try{await guard(p,row,report.violations);await p.goto(BASE+row.path,{waitUntil:'networkidle'});
    row.practice=await practice(p,{slug:t.slug,after:{page:{blocks:[{type:'practice-set',body:JSON.stringify(t.practice.body_data)}]}}});
    assert.deepEqual(row.errors,[]);assert.deepEqual(row.httpErrors,[]);assert.deepEqual(row.failed,[]);row.pass=true;
   }finally{await c.close();}
  }
  assert.deepEqual(report.violations,[]);report.pass=true;
 }finally{await b.close();fs.writeFileSync(path.join(dir,label+'-elements.json'),JSON.stringify(report,null,2),{flag:'wx'});}
 console.log(JSON.stringify({pass:report.pass,focused:report.focus.length,m26Practice:report.m26.length}));
}
run().catch(e=>{console.error(e.message);process.exitCode=1;});
