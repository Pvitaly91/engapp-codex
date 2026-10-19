'use strict';
// Fresh unauthenticated, GET-only .loc baseline/postconditions; no response bodies or secrets retained.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),crypto=require('node:crypto');
const {JSDOM}=require('jsdom');
const {metadata}=require('./seo-m28-local.cjs');
const ROOT=path.resolve(__dirname,'../..'),BASE='http://gramlyze.loc';
const read=f=>JSON.parse(fs.readFileSync(path.join(ROOT,f),'utf8'));
const m14=['participle-clauses-basics','participle-clauses','advanced-participle-and-absolute-clauses'].map(s=>'/theory/clauses-and-linking-words/'+s);
const m29=read('database/content-patches/m29-m13-sentence-structure.v1.json').targets.map(t=>'/theory/'+[...t.ancestry,t.slug].join('/'));
const m26=read('docs/content/m26-past-perfect-continuous-detail-master.v1.json').targets.map(t=>t.expected_theory_path);
const quality=['m27-m11-linking-words','m28-m12-emphasis-inversion'].flatMap(f=>read('database/content-patches/'+f+'.v2.json').targets.map(t=>'/theory/'+(t.ancestry||['clauses-and-linking-words']).concat(t.slug).join('/')));
const controls=[...m26,...quality,'/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms','/theory/conditionals/conditionals-with-unless-provided-as-long-as'];
const sha=x=>crypto.createHash('sha256').update(x).digest('hex'),norm=x=>String(x||'').replace(/\s+/gu,' ').trim();
async function capture(){const rows=[];
 for(const route of [...m14,...m29,...controls]){
  console.log(JSON.stringify({checking:route}));
  assert.ok(route.startsWith('/')&&!route.startsWith('//'));
  const r=await fetch(BASE+route,{redirect:'manual',signal:AbortSignal.timeout(30000),headers:{Accept:'text/html',Connection:'close'}});assert.equal(r.status,200,route);
  const dom=new JSDOM(await r.text()),doc=dom.window.document,main=doc.querySelector('[data-theory-main]')?.cloneNode(true);assert.ok(main,route);
  main.querySelectorAll('[x-data],script,style,[data-theory-ui]').forEach(n=>n.remove());
  rows.push({path:route,status:r.status,meta:metadata(doc),xRobots:r.headers.get('x-robots-tag'),mainTextSha256:sha(norm(main.textContent)),
   details:doc.querySelectorAll('[data-theory-native-extension] > details').length,
   legacyIds:[...doc.querySelectorAll('[id^="lesson-block-"],[id^="self-check-"]')].map(n=>n.id)});dom.window.close();
 }
 const r=await fetch(BASE+'/sitemap.xml',{redirect:'manual',signal:AbortSignal.timeout(30000)});assert.equal(r.status,200);
 const dom=new JSDOM(await r.text(),{contentType:'text/xml'}),urls=[...dom.window.document.querySelectorAll('loc')].map(n=>n.textContent);dom.window.close();
 return {at:new Date().toISOString(),base:BASE,rows,sitemap:{count:urls.length,sha256:sha(JSON.stringify(urls))}};
}
async function run(){const [dir,label]=process.argv.slice(2);assert.equal(path.basename(dir),'seo-m30-local');assert.match(label,/^[a-z0-9-]+$/);fs.mkdirSync(dir,{recursive:true});
 const result=await capture();fs.writeFileSync(path.join(dir,label+'-http.json'),JSON.stringify(result,null,2),{flag:'wx'});
 if(label!=='before'){
  const old=JSON.parse(fs.readFileSync(path.join(dir,'before-http.json'),'utf8'));assert.deepEqual(result.sitemap,old.sitemap);
  for(const row of result.rows){const before=old.rows.find(x=>x.path===row.path);assert.deepEqual(row.meta,before.meta,row.path);assert.equal(row.xRobots,before.xRobots);
   for(const id of before.legacyIds)assert.ok(row.legacyIds.includes(id),'Legacy anchor '+id);
   if(controls.includes(row.path)){assert.equal(row.mainTextSha256,before.mainTextSha256,'Unchanged control '+row.path);assert.equal(row.details,before.details);}
  }
 }
 console.log(JSON.stringify({pass:true,label,rows:result.rows.length,sitemap:result.sitemap,details:result.rows.map(r=>({path:r.path,count:r.details}))}));
}
if(require.main===module)run().catch(e=>{console.error(e.message);process.exitCode=1;});
module.exports={capture,m14,m29,controls,BASE};
