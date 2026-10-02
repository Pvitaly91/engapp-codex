'use strict';
// Actual guest GET-only .loc acceptance; private evidence excludes response bodies/cookies/tokens.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),crypto=require('node:crypto');
const {JSDOM}=require('jsdom');
const ROOT=path.resolve(__dirname,'../..'),BASE='http://gramlyze.loc';
const source=JSON.parse(fs.readFileSync(path.join(ROOT,'database/content-patches/m27-m11-linking-words.v1.json'),'utf8'));
const controls=['/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms',
 '/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms',
 '/theory/tenses/present-perfect/present-perfect-forms','/theory/sentence-structure/cleft-sentences-basics'];
const targets=source.targets.map(t=>({...t,path:'/theory/clauses-and-linking-words/'+t.slug}));
const norm=x=>String(x||'').replace(/\s+/gu,' ').trim(),sha=x=>crypto.createHash('sha256').update(x).digest('hex');
const publicUrl=x=>{try{const u=new URL(x);return u.origin+u.pathname;}catch{return '(invalid URL)';}};
function save(dir,name,value){fs.writeFileSync(path.join(dir,name),JSON.stringify(value,null,2),{flag:'wx'});}
function metadata(doc){const meta=x=>doc.querySelector(x)?.getAttribute('content')||null;return {
 title:norm(doc.title),h1:[...doc.querySelectorAll('h1')].map(n=>norm(n.textContent)),description:meta('meta[name="description"]'),
 canonical:doc.querySelector('link[rel="canonical"]')?.getAttribute('href')||null,robots:meta('meta[name="robots"]'),
 ogTitle:meta('meta[property="og:title"]'),ogDescription:meta('meta[property="og:description"]'),
 twitterTitle:meta('meta[name="twitter:title"]'),twitterDescription:meta('meta[name="twitter:description"]')};}
async function get(route){assert.ok(route.startsWith('/')&&!route.startsWith('//'));const r=await fetch(BASE+route,{redirect:'manual',headers:{Accept:'text/html'}});
 assert.equal(r.status,200,route);return {response:r,body:await r.text()};}
async function http(){const rows=[];for(const route of [...targets.map(t=>t.path),...controls]){
 const {response:r,body}=await get(route),dom=new JSDOM(body),doc=dom.window.document;
 const main=doc.querySelector('[data-theory-main]')?.cloneNode(true);assert.ok(main);
 main.querySelectorAll('[x-data],script,style,[data-theory-ui]').forEach(n=>n.remove());
 const details=[...doc.querySelectorAll('[data-theory-native-extension] > details')];
 rows.push({path:route,status:r.status,meta:metadata(doc),xRobots:r.headers.get('x-robots-tag'),mainTextSha256:sha(norm(main.textContent)),
 details:details.length,closed:details.every(d=>!d.hasAttribute('open')),legacyIds:[...doc.querySelectorAll('[id^="lesson-block-"],[id^="self-check-"]')].map(n=>n.id)});
 dom.window.close();}
 const {body:xml}=await get('/sitemap.xml');const dom=new JSDOM(xml,{contentType:'text/xml'});
 const urls=[...dom.window.document.querySelectorAll('loc')].map(n=>n.textContent);dom.window.close();
 return {base:BASE,at:new Date().toISOString(),rows,sitemap:{count:urls.length,sha256:sha(JSON.stringify(urls))}};}
async function guard(page,row,violations){const requests=[];
 row.fontFailures=[];
 await page.route('**/*',async route=>{const req=route.request(),u=new URL(req.url());
  if(req.method()!=='GET'||!(u.origin===BASE||/^https:\/\/fonts\.(googleapis|gstatic)\.com$/.test(u.origin))){violations.push({method:req.method(),url:publicUrl(req.url())});await route.abort();return;}
  requests.push(publicUrl(req.url()));await route.continue();});
 page.on('pageerror',e=>row.errors.push(String(e.message).slice(0,300)));
 page.on('response',r=>{if(r.status()>=400)row.httpErrors.push({url:publicUrl(r.url()),status:r.status()});});
 page.on('requestfailed',r=>{const item={url:publicUrl(r.url()),reason:r.failure()?.errorText,resourceType:r.resourceType()};
  if(row.javaScriptEnabled===false&&item.resourceType==='script'&&item.reason==='csp'){row.expectedDisabledScripts.push(item);return;}
  if(/^https:\/\/fonts\./.test(r.url()))row.fontFailures.push(item);else row.failed.push(item);
 });return requests;}
async function practice(page,target){const data=JSON.parse(target.after.page.blocks.find(b=>b.type==='practice-set').body);
 const root=page.locator('[x-data^="theoryPracticeSet("]');assert.equal(await root.count(),1);
 const groups=root.locator('.theory-exercise'),results=[];
 for(const [g,name] of ['selects','choices','inputs'].entries()){
  const group=groups.nth(g),check=group.getByRole('button',{name:'Перевірити',exact:true}),reset=group.getByRole('button',{name:'Спробувати ще раз',exact:true});
  const score=group.locator(`p[x-text="scoreText('${name}')"]`);assert.ok(await check.isVisible());
  for(const [i,item]of data[name].entries()){
   if(name==='inputs'){
    const input=group.locator('input').nth(i);assert.equal(await input.getAttribute('autocomplete'),'off');
    if(item.before.includes('/')){
     const task=input.locator('xpath=../..'),bank=task.locator('button').filter({has:page.locator('span[x-text="token.value"]')});
     const first=bank.first();await first.click();assert.ok(await first.isDisabled());await input.press('ControlOrMeta+A');await input.press('Backspace');assert.ok(await first.isEnabled());await first.click();
     await input.fill('');
     const words=s=>s.toLowerCase().replace(/[.,!?;:]+/g,'').split(/\s+/).filter(Boolean),answer=words(item.answer);
     const tokens=item.before.split('/').map(s=>s.trim());let offset=0;
     while(offset<answer.length){const j=tokens.findIndex(t=>t&&words(t).every((w,k)=>answer[offset+k]===w));assert.ok(j>=0);await bank.nth(j).click();offset+=words(tokens[j]).length;tokens[j]='';}
    }
    await input.fill(item.answer.replace(/[.!?]+$/,''));
   }else{const row=group.locator('label').nth(i).locator('..');await row.getByRole('button',{name:item.answer,exact:true}).click();}
  }
  await check.click();await score.waitFor({state:'visible'});assert.match(norm(await score.textContent()),/2\s*(?:\/|з|із)\s*2/u);
  if(name==='inputs')for(const [i,item]of data.inputs.entries())for(const value of item.accepted||[item.answer]){
   await group.locator('input').nth(i).fill(value.replace(/[.!?]+$/,''));await check.click();assert.ok(await group.locator('input').nth(i).evaluate(n=>n.classList.contains('border-emerald-400')));
  }
  if(name==='selects'&&target.slug==='linking-words-reason-result-contrast'){await group.locator('label').nth(1).locator('..').getByRole('button',{name:'due to',exact:true}).click();await check.click();assert.match(norm(await score.textContent()),/2\s*(?:\/|з|із)\s*2/u);}
  if(name==='inputs')await group.locator('input').first().fill('wrong answer');
  else{const item=data[name][0],options=name==='selects'?(item.options||data.options):['a','b'];await group.locator('label').first().locator('..').getByRole('button',{name:options.find(o=>o!==item.answer),exact:true}).click();}
  await check.click();assert.match(norm(await score.textContent()),/1\s*(?:\/|з|із)\s*2/u);
  if(name==='inputs'&&target.slug==='advanced-linking-devices'){await group.locator('input').nth(1).fill(data.inputs[1].before);await check.click();assert.ok(await group.locator('input').nth(1).evaluate(n=>n.classList.contains('border-rose-400')));}
  await reset.click();await score.waitFor({state:'hidden'});assert.equal(await score.isVisible(),false,'Reset hides checked score');if(name==='inputs'){for(const input of await group.locator('input').all())assert.equal(await input.inputValue(),'');assert.equal(await group.locator('button[disabled]').count(),0);}
  results.push({group:name,tasks:2,correct:'2/2',wrong:true,reset:true});
 }
 const widget=root.locator('[data-sentence-builder]');assert.equal(await widget.count(),1);assert.ok(await widget.isVisible());
 const pool=await widget.evaluate(n=>{const s=window.Alpine.$data(n);return {count:s.questions.length,types:[...new Set(s.questions.map(q=>q.type))],ids:s.questions.map(q=>q.id)};});
 assert.ok(pool.count>0);assert.deepEqual(pool.types,['4']);return {groups:results,linked:pool,expectedSeeder:data.linked_practice?.seeder_classes?.[0]};
}
async function browser(dir,label,baseline){const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');const b=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});
 const report={base:BASE,at:new Date().toISOString(),states:[],noJS:[],controls:[],violations:[],pass:false};
 try{
  for(const viewport of [{width:1440,height:1000},{width:390,height:844}])for(const theme of ['light','dark'])for(const [i,t]of targets.entries()){
   const context=await b.newContext({viewport,colorScheme:theme}),p=await context.newPage(),row={path:t.path,viewport,theme,errors:[],httpErrors:[],failed:[]};report.states.push(row);
   try{
    const req=await guard(p,row,report.violations);await p.goto(BASE+t.path,{waitUntil:'networkidle'});await p.waitForFunction(()=>Boolean(window.Alpine));
    await p.evaluate(w=>{document.documentElement.classList.toggle('dark',w==='dark');localStorage.setItem('theme',w);},theme);
    const ds=p.locator('[data-theory-native-extension] > details');const expected=t.plans.reduce((n,x)=>n+x.points.filter(pt=>pt.detail).length,0);assert.equal(await ds.count(),expected);
    assert.ok(await ds.evaluateAll(ns=>ns.every(n=>!n.open)));
    for(const d of await ds.all()){
     assert.ok(await d.evaluate(n=>Boolean(n.closest('article.theory-item'))));const s=d.locator(':scope > summary');const before=req.length;
     await s.click();assert.ok(await d.evaluate(n=>n.open));await s.click();await s.focus();await p.keyboard.press('Enter');assert.ok(await d.evaluate(n=>n.open));
     assert.ok(await s.evaluate(n=>n.matches(':focus-visible')&&getComputedStyle(n).outlineStyle!=='none'));await p.keyboard.press('Space');await p.waitForFunction(id=>!document.getElementById(id).closest('details').open,await s.getAttribute('id'));assert.equal(await d.evaluate(n=>n.open),false,'Space closes only focused detail');assert.equal(req.length,before);
    }
    await ds.nth(0).locator('summary').click();await ds.nth(1).locator('summary').click();assert.equal(await ds.evaluateAll(ns=>ns.filter(n=>n.open).length),2);
    await ds.nth(0).locator('summary').click();assert.ok(await ds.nth(1).evaluate(n=>n.open));await ds.nth(1).locator('summary').click();
    row.practice=await practice(p,t);
    const inventory=JSON.parse(fs.readFileSync(path.join(dir,'m27-bank-inventory-v1.json'),'utf8')).targets.find(x=>x.slug===t.slug);
    const bankIds=inventory.linked_bank_ids[row.practice.expectedSeeder];assert.ok(bankIds?.length>0);
    assert.ok(row.practice.linked.ids.every(id=>bankIds.includes(id)));row.practice.linked.exactBank=true;
    row.overflow=await p.locator('[data-theory-main]').evaluate(root=>({main:Math.max(0,root.scrollWidth-root.clientWidth),
      document:Math.max(0,document.documentElement.scrollWidth-innerWidth),
      cards:[...root.querySelectorAll('.theory-section-card')].filter(n=>!n.closest('details:not([open])')).map(n=>Math.max(0,n.scrollWidth-n.clientWidth)),
      tables:[...root.querySelectorAll('.theory-table-scroll')].map(n=>({client:n.clientWidth,scroll:n.scrollWidth,overflow:getComputedStyle(n).overflowX})),
      decorative:[...document.querySelectorAll('#shell-random-shapes span')].map(n=>{const r=n.getBoundingClientRect();return Math.max(0,r.right-innerWidth,-r.left);}).filter(n=>n>0)}));
    assert.ok(row.overflow.main<=1&&row.overflow.cards.every(n=>n<=1));assert.ok(row.overflow.tables.every(n=>['auto','scroll'].includes(n.overflow)));
    await p.evaluate(()=>window.scrollTo(0,0));const shot=`${label}-${i}-${viewport.width}-${theme}.png`;await p.screenshot({path:path.join(dir,shot),fullPage:true,animations:'disabled'});row.screenshot=shot;
    if(i===2&&viewport.width===1440&&theme==='light')for(const section of [4,5]){const block=p.locator('[data-theory-section^="m27-c2-section-'+section+'-"]').first().locator('xpath=ancestor::section[contains(@class,"theory-native-block")]');
      for(const d of await block.locator('details[data-theory-details]').all())await d.locator('summary').click();await block.screenshot({path:path.join(dir,`${label}-c2-section-${section}-expanded.png`)});}
    await p.reload({waitUntil:'networkidle'});assert.ok(await ds.evaluateAll(ns=>ns.every(n=>!n.open)));const id=await ds.first().locator('.theory-point-fragment').getAttribute('id');
    await p.goto(BASE+t.path+'#'+id,{waitUntil:'networkidle'});await p.waitForFunction(id=>document.getElementById(id)?.closest('details')?.open,id);row.deepFragment=true;
    const states=await p.locator('[data-theory-details]').evaluateAll(ns=>ns.map(n=>n.open));await p.emulateMedia({media:'print'});await p.waitForFunction(()=>[...document.querySelectorAll('[data-theory-details]')].every(n=>n.open));await p.emulateMedia({media:'screen'});
    await p.waitForFunction(s=>JSON.stringify([...document.querySelectorAll('[data-theory-details]')].map(n=>n.open))===JSON.stringify(s),states);row.printRestores=true;
    assert.deepEqual(row.errors,[]);assert.deepEqual(row.httpErrors,[]);assert.deepEqual(row.failed,[]);row.points=expected;row.pass=true;console.log(JSON.stringify({path:t.path,width:viewport.width,theme,points:expected,pass:true}));
   }catch(e){row.failure=String(e.stack||e.message).slice(0,2500);throw e;}finally{await context.close();}
  }
  for(const t of targets){const c=await b.newContext({javaScriptEnabled:false}),p=await c.newPage(),row={path:t.path,javaScriptEnabled:false,expectedDisabledScripts:[],errors:[],httpErrors:[],failed:[]};report.noJS.push(row);try{
   const req=await guard(p,row,report.violations);await p.goto(BASE+t.path,{waitUntil:'networkidle'});const ds=p.locator('[data-theory-native-extension] > details'),start=req.length;
   assert.equal(await ds.count(),t.plans.reduce((n,x)=>n+x.points.filter(pt=>pt.detail).length,0));
   for(const d of await ds.all()){await d.locator('summary').click();assert.ok(await d.evaluate(n=>n.open));await d.locator('summary').click();}
   assert.equal(req.length,start);assert.deepEqual(row.errors,[]);assert.deepEqual(row.httpErrors,[]);assert.deepEqual(row.failed,[]);row.points=await ds.count();row.pass=true;
  }finally{await c.close();}}
  for(const route of controls){const c=await b.newContext(),p=await c.newPage(),row={path:route,errors:[],httpErrors:[],failed:[]};report.controls.push(row);try{
   await guard(p,row,report.violations);await p.goto(BASE+route,{waitUntil:'networkidle'});assert.equal(await p.locator('[data-theory-section^="m27-"]').count(),0);
   if(route===controls[0]){assert.equal(await p.locator('[data-theory-native-extension] > details').count(),12);await p.locator('[data-theory-native-extension] > details').first().locator('summary').click();}
   if(route===controls[0]||route===controls[1]){
    const root=p.locator('[x-data^="theoryPracticeSet("]');assert.equal(await root.locator('.theory-exercise').count(),3);
    const data=await root.evaluate(n=>{const s=window.Alpine.$data(n);return {selects:s.selects,choices:s.choices,inputs:s.inputs,
      options:[...n.querySelectorAll('.theory-exercise:first-child button[x-text]')].map(x=>x.textContent)};});
    data.options=await root.locator('.theory-exercise').first().locator('button').evaluateAll(ns=>ns.filter(n=>n.getAttribute('@click')?.startsWith('selectAnswers')).map(n=>n.textContent.trim()));
    row.practice=await practice(p,{slug:route,after:{page:{blocks:[{type:'practice-set',body:JSON.stringify(data)}]}}});
   }
   assert.deepEqual(row.errors,[]);assert.deepEqual(row.failed,[]);row.pass=true;
  }finally{await c.close();}}
  assert.deepEqual(report.violations,[]);report.pass=true;
 }finally{await b.close();save(dir,label+'-browser.json',report);}
 return report;
}
async function runCLI(){const [dir,mode,label]=process.argv.slice(2);assert.equal(path.basename(dir),'seo-m27-local');fs.mkdirSync(dir,{recursive:true});
 if(mode==='before'){const result=await http();save(dir,'before-http.json',result);console.log(JSON.stringify({rows:result.rows.length,sitemap:result.sitemap}));return;}
 const before=JSON.parse(fs.readFileSync(path.join(dir,'before-http.json'),'utf8')),after=await http();save(dir,label+'-http.json',after);
 for(const row of after.rows){const old=before.rows.find(r=>r.path===row.path);assert.deepEqual(row.meta,old.meta);assert.equal(row.xRobots,old.xRobots);
  if(controls.includes(row.path)){assert.equal(row.mainTextSha256,old.mainTextSha256);assert.equal(row.details,old.details);}else{assert.ok(row.closed);for(const id of old.legacyIds)assert.ok(row.legacyIds.includes(id),'Preserve old deep anchor '+id);}}
 assert.deepEqual(after.sitemap,before.sitemap);const result=await browser(dir,label,before);console.log(JSON.stringify({pass:result.pass,states:result.states.length,noJS:result.noJS.length,controls:result.controls.length}));
}
if(require.main===module)runCLI().catch(e=>{console.error(String(e.message).replace(/https?:\/\/\S+/g,publicUrl));process.exitCode=1;});
module.exports={guard,BASE,targets};
