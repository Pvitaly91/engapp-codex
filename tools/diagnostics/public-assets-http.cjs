const fs=require('node:fs');
const path=require('node:path');
const assert=require('node:assert/strict');
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const {prepare,root,base}=require('./public-assets-browser.cjs');
const phase=process.argv[2]||'built';
assert.match(phase,/^[a-z0-9_-]+$/i);
const evidence=path.join(root,`${phase}-http-nojs.json`);
assert.ok(!fs.existsSync(evidence),'Evidence exists; supply a new phase');
const paths=['/','/theory','/theory/future-perfect','/theory/basic-grammar/sentence-types',
 '/theory/tenses/present-perfect/present-perfect-forms','/test/future-perfect/questions',
 '/courses/english-grammar-theory','/courses/english-grammar-theory/lesson/basic-grammar/sentence-types'];
(async()=>{
 const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_EXECUTABLE?{executablePath:process.env.CHROMIUM_EXECUTABLE}:{})});
 const rows=[];
 try{
  assert.equal(fs.existsSync(path.resolve('public/hot')),false,'Active hot file: inspect ownership before proceeding');
  const manifest=JSON.parse(fs.readFileSync(path.resolve('public/build/manifest.json')));
  for(const value of Object.values(manifest))assert.ok(fs.existsSync(path.resolve('public/build',value.file)));
  for(const url of paths){
   const r={path:url,time:new Date().toISOString(),javascript:false,blocked:[],console:[],responses:[],failures:[]};
   const {context,page}=await prepare(browser,{js:false},r);
   try{
    const response=await page.goto(base+url,{waitUntil:'load'});assert.equal(response.status(),200);
    r.status=response.status();r.robots=response.headers()['x-robots-tag'];assert.match(r.robots,/noindex/);
    const html=await page.content();assert.doesNotMatch(html,/cdn\.tailwindcss\.com|unpkg\.com\/alpinejs|tailwind\.config|@vite\/client|localhost:5173/);
    r.html=await page.evaluate(()=>({mainTextLength:document.querySelector('main')?.innerText.length||0,h1:[...document.querySelectorAll('main h1')].map(n=>n.textContent.trim()),canonical:[...document.querySelectorAll('link[rel="canonical"]')].map(n=>n.href)}));
    assert.ok(r.html.mainTextLength>100,'Main HTML became an empty shell');assert.equal(r.html.h1.length,1);
    r.assets=[];
    for(const entry of ['resources/css/catalog-public.css','resources/js/catalog-public.js']){
     const asset='/build/'+manifest[entry].file;assert.ok(html.includes(asset));
     const res=await context.request.get(base+asset,{maxRedirects:0});
     const mime=res.headers()['content-type'];r.assets.push({asset,status:res.status(),mime,bytes:(await res.body()).length});
     assert.equal(res.status(),200);assert.match(mime,entry.endsWith('.css')?/text\/css/:/(javascript|ecmascript)/);
    }
    assert.equal(r.blocked.length,0);r.pass=true;
   }catch(error){r.error=error.message;r.pass=false}
   finally{await context.close();rows.push(r);fs.writeFileSync(evidence,JSON.stringify(rows,null,2));}
   console.log(JSON.stringify({path:url,pass:r.pass,html:r.html,error:r.error}));
  }
 }finally{await browser.close()}
 if(rows.some(r=>!r.pass))process.exitCode=1;
})().catch(e=>{console.error(e.message);process.exitCode=1});
