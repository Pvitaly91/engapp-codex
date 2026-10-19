'use strict';
// Extract exact historical sources and capture old native components in the
// current read-only .loc shell. Only isolated browser DOM is replaced.
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const {execFileSync}=require('node:child_process');
const ROOT=path.resolve(__dirname,'../..'),REF='ab61310a81f2389353c264fe23266025fe49ffd6',BASE='http://gramlyze.loc';
const sha=v=>crypto.createHash('sha256').update(v).digest('hex');
const definitions=['TensesPastSimpleVsPastContinuousTheorySeeder','TensesPresentSimpleVsPresentContinuousTheorySeeder','TensesPresentPerfectVsPastSimpleTheorySeeder'];
const templates=['engram/theory/blocks-v3/forms-grid','engram/theory/blocks-v3/usage-panels','engram/theory/blocks-v3/comparison-table','engram/theory/blocks-v3/mistakes-grid','engram/theory/blocks-v3/summary-list','engram/theory/blocks-v3/practice-set','engram/theory/widgets/lesson-rule-cards','components/theory-native-header','components/text-block-level-badge','components/text-block-tags','components/text-block-practice-questions','theory/partials/point-disclosure'];
const write=(file,value)=>{fs.mkdirSync(path.dirname(file),{recursive:true});fs.writeFileSync(file,value,{flag:'wx'});};
function extract(out){
 assert.equal(path.basename(out),'seo-m41-design-reference');assert.ok(!fs.existsSync(path.join(out,'sources.json')));
 fs.mkdirSync(out,{recursive:true});fs.mkdirSync(path.join(out,'compiled'),{recursive:true});
 const show=file=>execFileSync('git',['-c','safe.directory='+ROOT,'-c','core.bare=false','show',REF+':'+file],{cwd:ROOT});
 const index={reference_commit:REF,definitions:[],templates:[]};
 for(const name of definitions){const file='database/seeders/Page_V3/Tenses/'+name+'/definition.json',raw=show(file),private_file='definitions/'+name+'.json';write(path.join(out,private_file),raw);index.definitions.push({path:file,private_file,sha256:sha(raw),identity:'Database\\Seeders\\Page_V3\\Tenses\\'+name});}
 for(const name of templates){const file='resources/views/'+name+'.blade.php',raw=show(file);write(path.join(out,'views',name+'.blade.php'),raw);index.templates.push({path:file,sha256:sha(raw)});}
 write(path.join(out,'sources.json'),JSON.stringify(index,null,2));return index;
}
async function capture(out,label){
 assert.match(label,/^[a-z0-9-]+$/u);const file=path.join(out,label+'-reference.json');assert.ok(!fs.existsSync(file));
 const rendered=JSON.parse(fs.readFileSync(path.join(out,'render.json'),'utf8'));
 const {chromium}=require('playwright');const browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});
 const report={at:new Date().toISOString(),base:BASE,referenceCommit:REF,conditions:{browserZoom:'100%',deviceScaleFactor:1,currentAssets:true,oldContent:'reference-only'},rows:[],pass:false};
 try{for(const viewport of [{width:1440,height:1000},{width:390,height:844}])for(const theme of ['light','dark'])for(const target of rendered.rows){
  const context=await browser.newContext({viewport,colorScheme:theme,deviceScaleFactor:1}),page=await context.newPage();
  const row={path:target.path,viewport,theme,console:[],failed:[],httpErrors:[],screenshots:[],components:[]};report.rows.push(row);
  page.on('pageerror',e=>row.console.push(e.message));page.on('requestfailed',r=>row.failed.push({url:r.url().split('?')[0],error:r.failure()?.errorText}));page.on('response',r=>{if(r.status()>=400)row.httpErrors.push({url:r.url().split('?')[0],status:r.status()});});
  await context.addInitScript(t=>localStorage.setItem('theme',t),theme);
  try{const response=await page.goto(BASE+target.path,{waitUntil:'networkidle'});assert.equal(response.status(),200);await page.waitForFunction(()=>Boolean(window.Alpine));
   await page.evaluate(({html,theme})=>{document.documentElement.classList.toggle('dark',theme==='dark');const root=document.querySelector('.theory-content-blocks > .space-y-6');if(!root)throw new Error('Exact native content shell missing');root.innerHTML=html;root.dataset.m41OldReference='true';}, {html:fs.readFileSync(path.join(out,target.html),'utf8'),theme});
   await page.locator('[data-m41-old-reference] .theory-native-block').first().waitFor({state:'visible'});await page.evaluate(()=>document.fonts.ready);
   row.viewportActual=await page.evaluate(()=>({width:innerWidth,height:innerHeight,dpr:devicePixelRatio,visualScale:visualViewport.scale}));assert.equal(row.viewportActual.visualScale,1);
   const stem=label+'-'+target.slug+'-'+viewport.width+'-'+theme;
   const full=stem+'-page.png';assert.ok(!fs.existsSync(path.join(out,full)));await page.screenshot({path:path.join(out,full),fullPage:true,animations:'disabled'});row.screenshots.push(full);
   for(const component of target.native_types){const block=page.locator('#block-'+(99000+component.slot));assert.equal(await block.count(),1);
    await block.scrollIntoViewIfNeeded();const card=block.locator('.theory-section-card').first();
    const measured=await card.evaluate(el=>{const css=n=>{if(!n)return null;const c=getComputedStyle(n);return{background:c.backgroundColor,border:c.borderColor,borderWidth:c.borderWidth,radius:c.borderRadius,font:c.fontFamily,size:c.fontSize,weight:c.fontWeight,padding:c.padding,transform:c.textTransform,lineHeight:c.lineHeight};};return{card:css(el),header:css(el.querySelector('.theory-section-header')),title:css(el.querySelector('h2')),point:css(el.querySelector('.theory-item')),example:css(el.querySelector('.theory-example')),english:css(el.querySelector('.theory-example .font-mono')),translation:css(el.querySelector('.theory-translation')),mainOverflow:Math.max(0,el.scrollWidth-el.clientWidth)};});
    const shot=stem+'-'+component.type+'-'+component.slot+'.png';assert.ok(!fs.existsSync(path.join(out,shot)));await block.screenshot({path:path.join(out,shot),animations:'disabled'});row.components.push({...component,screenshot:shot,styles:measured});
   }
   assert.deepEqual(row.console,[],'No reference page errors');assert.deepEqual(row.failed,[],'No reference failed requests');assert.deepEqual(row.httpErrors,[],'No reference HTTP errors');row.pass=true;
  }finally{await context.close();}
  console.log(JSON.stringify({path:target.path,width:viewport.width,theme,pass:row.pass}));
 }assert.equal(report.rows.length,12);report.pass=true;}finally{await browser.close();write(file,JSON.stringify(report,null,2));}return report;
}
if(require.main===module){const[mode,out,label]=process.argv.slice(2);Promise.resolve(mode==='--extract'?extract(out):capture(out,label)).catch(e=>{console.error(e.stack);process.exitCode=1;});}
module.exports={extract,capture};
