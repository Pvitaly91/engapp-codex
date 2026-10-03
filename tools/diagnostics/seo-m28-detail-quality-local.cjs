'use strict';
// Guest, GET-only real .loc acceptance. Private evidence omits cookies and response bodies.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),crypto=require('node:crypto');
const {JSDOM}=require('jsdom');
const {guard,BASE,fidelity,practice,metadata,htmlText}=require('./seo-m28-local.cjs');
const ROOT=path.resolve(__dirname,'../..');
const targets=['m27-m11-linking-words','m28-m12-emphasis-inversion'].flatMap(file=>{
 const old=JSON.parse(fs.readFileSync(path.join(ROOT,'database/content-patches',file+'.v1.json'),'utf8'));
 return JSON.parse(fs.readFileSync(path.join(ROOT,'database/content-patches',file+'.v2.json'),'utf8')).targets.map((t,i)=>({...t,
  path:'/theory/'+(t.ancestry||['clauses-and-linking-words']).concat(t.slug).join('/'),
  merged:t.plans.flatMap((p,j)=>p.points.flatMap((pt,k)=>old.targets[i].plans[j].points[k].detail&&!pt.detail?[{id:'block-'+p.key+'-point-'+(k+1)+'-detail',detail:old.targets[i].plans[j].points[k].detail}]:[]))}));
});
const sha=x=>crypto.createHash('sha256').update(x).digest('hex');
function save(dir,name,data){fs.writeFileSync(path.join(dir,name),JSON.stringify(data,null,2),{flag:'wx'});}
async function http(verify){const rows=[];
 for(const t of targets){const r=await fetch(BASE+t.path,{headers:{Accept:'text/html'},redirect:'manual'});assert.equal(r.status,200);
  const dom=new JSDOM(await r.text()),d=dom.window.document; if(verify)fidelity(d,t);
  const legacyIds=[...d.querySelectorAll('[id^="lesson-block-"],[id^="self-check-"]')].map(n=>n.id);
  rows.push({path:t.path,status:r.status,meta:metadata(d),xRobots:r.headers.get('x-robots-tag'),legacyIds,details:d.querySelectorAll('[data-theory-native-extension] > details').length});dom.window.close();
 }
 const r=await fetch(BASE+'/sitemap.xml',{redirect:'manual'});assert.equal(r.status,200);const d=new JSDOM(await r.text(),{contentType:'text/xml'});
 const urls=[...d.window.document.querySelectorAll('loc')].map(n=>n.textContent);d.window.close();
 return {at:new Date().toISOString(),base:BASE,rows,sitemap:{count:urls.length,sha256:sha(JSON.stringify(urls))}};
}
async function content(page,t){
 const dom=new JSDOM(await page.content());try{fidelity(dom.window.document,t);}finally{dom.window.close();}
 for(const m of t.merged){assert.equal(await page.locator('[id="'+m.id+'"]').count(),0);
  const found=await page.locator('article.theory-item').evaluateAll((ns,needle)=>ns.some(n=>{
   const c=n.cloneNode(true);c.querySelectorAll('[data-theory-native-extension],noscript').forEach(n=>n.remove());
   return c.textContent.replace(/\s+/gu,' ').trim().includes(needle);
  }),htmlText(m.detail));assert.ok(found,'Merged text immediately visible: '+m.id);
 }
}
async function run(){const [dir,mode,label]=process.argv.slice(2);assert.equal(path.basename(dir),'seo-m28-detail-quality');fs.mkdirSync(dir,{recursive:true});
 const current=await http(mode!=='before');save(dir,label+'-http.json',current);
 if(mode==='before'){console.log(JSON.stringify(current));return;}
 const before=JSON.parse(fs.readFileSync(path.join(dir,'before-http.json'),'utf8'));
 for(const row of current.rows){const old=before.rows.find(x=>x.path===row.path);assert.deepEqual(row.meta,old.meta);assert.equal(row.xRobots,old.xRobots);assert.deepEqual(row.legacyIds,old.legacyIds);}
 assert.deepEqual(current.sitemap,before.sitemap);
 const banks=JSON.parse(fs.readFileSync(path.join(ROOT,'storage/app/m28-quality-before-db.json'),'utf8'));
 const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');const b=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});
 const report={at:new Date().toISOString(),base:BASE,states:[],noJS:[],violations:[],pass:false};
 try{
  for(const viewport of [{width:1440,height:1000},{width:390,height:844}])for(const theme of ['light','dark'])for(const [i,t]of targets.entries()){
   const c=await b.newContext({viewport,colorScheme:theme}),p=await c.newPage();const row={path:t.path,viewport,theme,errors:[],httpErrors:[],failed:[]};report.states.push(row);
   try{
    const req=await guard(p,row,report.violations);await p.goto(BASE+t.path,{waitUntil:'networkidle'});await p.waitForFunction(()=>Boolean(window.Alpine));
    await p.evaluate(theme=>{document.documentElement.classList.toggle('dark',theme==='dark');localStorage.setItem('theme',theme);},theme);
    await content(p,t);const ds=p.locator('[data-theory-native-extension] > details');const expected=t.plans.reduce((n,x)=>n+x.points.filter(p=>p.detail).length,0);
    assert.equal(await ds.count(),expected);assert.ok(await ds.evaluateAll(ns=>ns.every(n=>!n.open)));
    for(const d of await ds.all()){
     assert.ok((await d.locator('.theory-point-fragment').textContent()).trim());const s=d.locator(':scope > summary'),start=req.length;
     await s.click();assert.ok(await d.evaluate(n=>n.open));assert.equal(await ds.evaluateAll(ns=>ns.filter(n=>n.open).length),1);
     await s.click();await s.focus();await p.keyboard.press('Enter');assert.ok(await d.evaluate(n=>n.open));await p.keyboard.press('Space');assert.ok(await d.evaluate(n=>!n.open));assert.equal(req.length,start);
    }
    row.practice=await practice(p,t);const bank=banks.targets.find(x=>x.slug===t.slug).linked_bank_ids[row.practice.expectedSeeder];
    assert.ok(bank?.length&&row.practice.linked.ids.every(id=>bank.includes(id)));row.practice.linked.exactBank=true;
    row.overflow=await p.locator('[data-theory-main]').evaluate(root=>({main:Math.max(0,root.scrollWidth-root.clientWidth),
     cards:[...root.querySelectorAll('.theory-section-card,article.theory-item')].filter(n=>!n.closest('details:not([open])')).map(n=>Math.max(0,n.scrollWidth-n.clientWidth)),
     decorative:[...document.querySelectorAll('#shell-random-shapes span')].map(n=>{const r=n.getBoundingClientRect();return Math.max(0,r.right-innerWidth,-r.left);}).filter(n=>n>0)}));
    assert.equal(row.overflow.main,0);assert.ok(row.overflow.cards.every(n=>n===0));
    await p.evaluate(()=>window.scrollTo(0,0));row.screenshot=`${label}-${i}-${viewport.width}-${theme}.png`;await p.screenshot({path:path.join(dir,row.screenshot),animations:'disabled'});
    await p.reload({waitUntil:'networkidle'});await content(p,t);assert.ok(await ds.evaluateAll(ns=>ns.every(n=>!n.open)));
    if(expected){const id=await ds.first().locator('.theory-point-fragment').getAttribute('id');await p.goto(BASE+t.path+'#'+id,{waitUntil:'networkidle'});await p.waitForFunction(id=>document.getElementById(id)?.closest('details')?.open,id);row.deepFragment=true;}
    const states=await p.locator('[data-theory-details]').evaluateAll(ns=>ns.map(n=>n.open));await p.emulateMedia({media:'print'});await p.waitForFunction(()=>[...document.querySelectorAll('[data-theory-details]')].every(n=>n.open));
    await p.emulateMedia({media:'screen'});await p.waitForFunction(s=>JSON.stringify([...document.querySelectorAll('[data-theory-details]')].map(n=>n.open))===JSON.stringify(s),states);
    assert.deepEqual(row.errors,[]);assert.deepEqual(row.httpErrors,[]);assert.deepEqual(row.failed,[]);row.points=expected;row.pass=true;console.log(JSON.stringify({path:t.path,width:viewport.width,theme,points:expected,pass:true}));
   }catch(e){row.failure=String(e.stack||e).slice(0,2500);throw e;}finally{await c.close();}
  }
  for(const t of targets){const c=await b.newContext({javaScriptEnabled:false}),p=await c.newPage();const row={path:t.path,javaScriptEnabled:false,expectedDisabledScripts:[],errors:[],httpErrors:[],failed:[]};report.noJS.push(row);
   try{const req=await guard(p,row,report.violations);await p.goto(BASE+t.path,{waitUntil:'networkidle'});await content(p,t);const start=req.length;
    for(const d of await p.locator('[data-theory-native-extension] > details').all()){await d.locator('summary').click();assert.ok(await d.evaluate(n=>n.open));await d.locator('summary').click();}
    assert.equal(req.length,start);assert.deepEqual(row.errors,[]);assert.deepEqual(row.httpErrors,[]);assert.deepEqual(row.failed,[]);row.pass=true;
   }finally{await c.close();}
  }
  assert.deepEqual(report.violations,[]);report.pass=true;
 }finally{await b.close();save(dir,label+'-browser.json',report);}
 console.log(JSON.stringify({pass:true,states:report.states.length,noJS:report.noJS.length,counts:current.rows.map(x=>x.details),sitemap:current.sitemap}));
}
run().catch(e=>{console.error(e.message);process.exitCode=1;});
