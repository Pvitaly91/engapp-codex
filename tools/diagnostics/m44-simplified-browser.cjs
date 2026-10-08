'use strict';
// Real local GET-only acceptance; expectations come from the frozen author master.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto'), assert = require('node:assert/strict');
const {chromium} = require('playwright');
const phase = process.argv[2];
assert.match(phase, /^(before|after)-v\d+$/);
const after = phase.startsWith('after'), root = 'D:/DEV/htdocs/gramlyze.loc';
const dir = path.join(root, 'storage/app/m44-simplify', phase);
fs.mkdirSync(dir, {recursive: true});
assert(!fs.existsSync(path.join(dir, 'results.json')));
const hash = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => String(value ?? '').replace(/\s+/gu, ' ').trim();
const masterPath = 'docs/content/m44-authored-future-forms.v1.0.0.json';
const masterBytes = fs.readFileSync(path.join(root, masterPath));
const masterSha = '541db4dec57ed372d23832e849f84c0554fb40faf73693c5b7ab2d306fa7b888';
assert.equal(hash(masterBytes), masterSha, 'Independent frozen master');
const master = JSON.parse(masterBytes);
const beforeBytes = after ? fs.readFileSync(path.join(root, 'storage/app/m44-simplify/before-v1/results.json')) : null;
const beforeRows = beforeBytes ? JSON.parse(beforeBytes) : [];
const counts = {A: [8,27,6], B: [5,17,4], C: [7,25,7]};
const urls = [
 ['A', '/theory/maibutni-formy/future-simple/will-vs-be-going-to'],
 ['B', '/theory/maibutni-formy/present-continuous-for-future'],
 ['C', '/theory/maibutni-formy/choosing-the-right-future-form'],
 ['reference', '/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms'],
];
function sourceHashes() {
 const blockDir = 'resources/views/engram/theory/blocks-v3';
 const files = [masterPath, 'docs/content/m44-simplified-presentation.v1.json',
  'app/Support/M44SimplifiedPresentation.php', 'app/Support/M44NativeHtml.php', 'app/Support/M44AuthoredFutureFormsPackage.php',
  'public/js/authored-practice-ui.js', 'public/js/m44-practice-ui.js', 'resources/js/theory-sections.js',
  'resources/views/theory/partials/point-disclosure.blade.php', 'resources/views/theory/partials/point-detail-fragment.blade.php',
  'resources/views/theory/partials/section-disclosure.blade.php', 'tools/diagnostics/m44-simplified-browser.cjs',
  ...fs.readdirSync(path.join(root, blockDir)).filter(f => f.startsWith('m44-') && f.endsWith('.blade.php')).map(f => blockDir + '/' + f),
  ...master.lessons.map(l => l.definition_path)];
 return Object.fromEntries([...new Set(files)].sort().map(file => [file, fs.existsSync(path.join(root, file)) ? hash(fs.readFileSync(path.join(root, file))) : null]));
}
function stripLegacyBuilder(value) {
 // before-v1 is flat text: exclude only this exact widget span. New captures
 // remove its data-sentence-builder DOM node before normalizing.
 const start = 'Вправа 4. Побудуй речення', end = 'Завдання підібрані лише для цієї теми.';
 const text = norm(value), at = text.indexOf(start), last = text.indexOf(end, at);
 assert.ok(at >= 0 && last > at && text.indexOf(start, at + start.length) < 0, 'Unique historical builder boundaries');
 return norm(text.slice(0, at) + ' ' + text.slice(last + end.length));
}
function fields(point) {
 const out = [point.title, point.formula, ...(point.paragraphs_uk || []), point.wrong_en, point.wrong_uk, point.right_en, point.right_uk,
  ...(point.examples || []).flatMap(e => [e.en, e.uk, e.note_uk])].filter(Boolean);
 if (point.detail) out.push(...fields(point.detail));
 return out.map(norm);
}
async function fidelity(page, lesson, key) {
 const observed = await page.locator('[data-theory-main]').evaluate(main => {
  const clean = node => { const c = node.cloneNode(true); c.querySelectorAll('script,style,noscript,[data-theory-ui],[data-sentence-builder]').forEach(n => n.remove()); return c.textContent.replace(/\s+/gu, ' ').trim(); };
  return {fallback: main.querySelectorAll('[data-m44-static-fallback]').length,
   sections: [...main.querySelectorAll('[data-m44-author-section]')].map(s => ({id: s.dataset.m44AuthorSection, text: clean(s),
    points: [...s.querySelectorAll('[data-m44-basic-point]')].map(p => p.dataset.m44BasicPoint),
    groups: [...s.querySelectorAll('[data-m44-compact-group]')].map(g => ({id: g.dataset.m44CompactGroup, text: clean(g), points: [...(g.matches('[data-m44-basic-point]') ? [g] : []), ...g.querySelectorAll('[data-m44-basic-point]')].map(p => p.dataset.m44BasicPoint)})),
    tables: [...s.querySelectorAll('[data-m44-native-table]')].map(t => ({head: [...t.querySelectorAll('thead th')].map(clean), rows: [...t.querySelectorAll('tbody tr')].map(r => [...r.children].map(clean))}))})),
   details: main.querySelectorAll('[data-m44-author-section] details[data-theory-details]').length};
 });
 assert.equal(observed.fallback, 0, 'Native rendering, no static fallback');
 assert.deepEqual(observed.sections.map(s => s.id), lesson.sections.map(s => s.id), 'Frozen section identity/order');
 assert.deepEqual([observed.sections.length, observed.sections.reduce((n,s) => n+s.points.length,0), observed.details], counts[key], 'Sections, original points, semantic disclosures');
 let fieldsChecked = 0;
 lesson.sections.forEach((section, index) => {
  const actual = observed.sections[index];
  assert.deepEqual(actual.points, section.points.map(p => p.id), 'Original point identity/order: ' + section.id);
  for (const value of [section.title, section.intro_uk, ...(section.notes_uk || []), ...(section.note_examples || []).flatMap(e => [e.en,e.uk,e.note_uk])].filter(Boolean)) {
   assert.ok(actual.text.includes(norm(value)), 'Frozen section field: ' + section.id); fieldsChecked++;
  }
  assert.ok(actual.groups.length, 'Simplified native groups exist');
  for (const group of actual.groups) {
   const required = new Map();
   for (const id of group.points) {
    const point = section.points.find(p => p.id === id); assert.ok(point);
    for (const field of fields(point)) required.set(field, (required.get(field) || 0) + 1);
   }
   for (const [field, count] of required) {
    assert.ok(group.text.split(field).length - 1 >= count, 'Original field occurrences in ' + group.id + ': ' + field.slice(0,80)); fieldsChecked += count;
   }
  }
  assert.equal(actual.tables.length, section.table ? 1 : 0);
  if (section.table) {
   assert.deepEqual(actual.tables[0].head, section.table.columns.map(norm));
   assert.equal(actual.tables[0].rows.length, section.table.rows.length);
   section.table.rows.forEach((row,r) => { assert.equal(actual.tables[0].rows[r].length,row.length); row.forEach((cell,c) => {
    const values = typeof cell === 'string' ? [cell] : [cell.formula,cell.text_uk,cell.en,cell.uk,cell.note_uk].flat().filter(Boolean);
    for (const value of values) { assert.ok(actual.tables[0].rows[r][c].includes(norm(value)), 'Frozen table field'); fieldsChecked++; }
   }); });
  }
 });
 return {masterSha256: masterSha, sections: counts[key][0], points: counts[key][1], details: counts[key][2], fieldsChecked};
}
async function shot(page, locator, file) {
 await locator.evaluate(n => n.scrollIntoView({block:'start',behavior:'instant'}));
 await page.evaluate(() => scrollBy(0,-112));
 assert(!fs.existsSync(path.join(dir,file)));
 await page.screenshot({path:path.join(dir,file),fullPage:false}); return file;
}
async function detailsCheck(page, lesson, state) {
 const all = page.locator('[data-m44-author-section] details[data-theory-details]'), count = await all.count();
 const openCount = () => all.evaluateAll(ns => ns.filter(n => n.open).length);
 assert.equal(await openCount(),0);
 for (let i=0;i<count;i++) {
  const item=all.nth(i), toggle=item.locator(':scope > summary');
  await toggle.focus(); await toggle.press(i%2 ? 'Enter' : 'Space');
  assert.equal(await item.evaluate(n => n.open),true); assert.equal(await openCount(),1,'Only its own detail opens');
  assert.equal(await item.locator('details').count(),0,'No nested details');
  await toggle.press(i%2 ? 'Space' : 'Enter'); assert.equal(await item.evaluate(n => n.open),false);
 }
 const screenshots=[];
 if (count) {
  await all.first().locator(':scope > summary').click();
  screenshots.push(await shot(page,all.first(),state+'-detail-viewport.png'));
  const states=await all.evaluateAll(ns=>ns.map(n=>n.open));
  await page.emulateMedia({media:'print'});
  await page.waitForFunction(()=>[...document.querySelectorAll('[data-m44-author-section] details[data-theory-details]')].every(n=>n.open));
  await page.emulateMedia({media:'screen'});
  await page.waitForFunction(expected=>JSON.stringify([...document.querySelectorAll('[data-m44-author-section] details[data-theory-details]')].map(n=>n.open))===JSON.stringify(expected),states);
  assert.deepEqual(await all.evaluateAll(ns=>ns.map(n=>n.open)),states,'Print restores mixed open/closed state');
  await all.first().locator(':scope > summary').click();
 }
 const anchors=lesson.sections.flatMap(s=>s.points).filter(p=>p.detail).map(p=>'block-'+p.detail.id);
 for (const anchor of anchors) {
  await page.evaluate(id=>{location.hash=id;},anchor);
  await page.waitForFunction(id=>document.getElementById(id)?.closest('details')?.open,anchor);
  assert.equal(await openCount(),1,'Original detail link opens only its owner');
  await page.locator('[data-m44-author-section] details[data-theory-details][open] > summary').click();
  await page.evaluate(()=>history.replaceState(null,'',location.pathname));
 }
 return {keyboardDetails:count,printRestored:true,originalDetailAnchors:anchors,screenshots};
}
async function practiceSmoke(page, lesson) {
 const task=lesson.practice[0], box=page.locator('[data-m44-ui-case="1"]'), score=page.locator('[data-m44-ui-score] span');
 assert.ok(task.controls.every(c=>['select','choice'].includes(c.kind)),'Explicit choice smoke scope');
 for (const correct of [false,true]) {
  for (const control of task.controls) {
   const value=correct ? control.correct_value : control.options.find(o=>o.value!==control.correct_value).value;
   await box.locator('[data-m44-control="'+control.id+'"] [data-m44-answer="'+value+'"]').click();
  }
  await box.locator('[data-m44-check]').click();
  const feedback=box.locator('[data-m44-case-feedback]'); await feedback.waitFor({state:'visible'});
  assert.equal(norm(await feedback.textContent()),correct ? 'Правильно' : 'Не всі частини відповіді правильні');
  assert.equal(norm(await score.textContent()),correct ? '1' : '0');
  await box.locator('[data-m44-reset]').click();
  await feedback.waitFor({state:'hidden'}); assert.equal(norm(await score.textContent()),'0');
  assert.equal(await box.locator('[data-m44-answer][aria-checked="true"]').count(),0);
 }
 return {task:task.id,controls:task.controls.map(c=>c.id),wrong:true,correct:true,resetZero:true};
}
(async()=>{
 const sourceBefore=sourceHashes(), results=[]; let pass=false;
 const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
 try {
  for(const[key,url]of urls)for(const mobile of[false,true])for(const dark of[false,true]){
   const state=`${key}-${mobile?'mobile':'desktop'}-${dark?'dark':'light'}`;
   const context=await browser.newContext({viewport:{width:mobile?390:1440,height:mobile?844:1000},colorScheme:dark?'dark':'light'});
   const errors=[],failed=[],consoleErrors=[],nonGET=[],requestFailures=[];
   await context.addInitScript(theme=>localStorage.setItem('theme',theme),dark?'dark':'light');
   await context.route('**/*',route=>{
    const req=route.request(),u=new URL(req.url());
    if(req.method()==='GET'&&(u.origin==='http://gramlyze.loc'||['fonts.googleapis.com','fonts.gstatic.com'].includes(u.hostname)))return route.continue();
    if(req.method()!=='GET')nonGET.push({method:req.method(),path:u.pathname}); return route.abort();
   });
   const page=await context.newPage();
   page.on('pageerror',e=>errors.push(e.message));
   page.on('console',m=>{if(m.type()==='error')consoleErrors.push(m.text());});
   page.on('response',r=>{if(r.status()>=400)failed.push({path:new URL(r.url()).pathname,status:r.status()});});
   page.on('requestfailed',r=>requestFailures.push({path:new URL(r.url()).pathname,error:r.failure()?.errorText}));
   try{
    const response=await page.goto('http://gramlyze.loc'+url,{waitUntil:'networkidle',timeout:60000}); assert.equal(response.status(),200);
    await page.evaluate(()=>document.fonts.ready);
    const result=await page.evaluate(()=>{
     const main=document.querySelector('[data-theory-main]'),copy=main.cloneNode(true);
     copy.querySelectorAll('script,style,noscript,[data-theory-ui],summary,[data-sentence-builder]').forEach(n=>n.remove());
     const sections=[...main.querySelectorAll('[data-m44-author-section]')];
     const learning=[...main.querySelectorAll('[data-m44-author-section],[data-m44-compact-group],[data-m44-ui-case],[data-m44-author-section] p[lang],[data-m44-practice-ui] button,[data-m44-practice-ui] textarea')].filter(n=>!n.closest('.theory-table-scroll')&&n.getClientRects().length);
     return{title:document.title,h1:document.querySelector('h1').textContent.trim(),canonical:document.querySelector('[rel="canonical"]').href,
      fullText:copy.textContent.replace(/\s+/g,' ').trim(),height:main.getBoundingClientRect().height,
      theoryHeight:sections.reduce((n,s)=>n+s.getBoundingClientRect().height,0),visibleText:sections.map(s=>s.innerText).join('\n'),
      details:sections.reduce((n,s)=>n+s.querySelectorAll('details').length,0),points:main.querySelectorAll('[data-m44-basic-point]').length,practice:main.querySelectorAll('[data-m44-ui-case]').length,
      overflow:learning.filter(n=>{const r=n.getBoundingClientRect();return r.left < -2 || r.right>innerWidth+2 || n.scrollWidth>n.clientWidth+2;}).map(n=>({id:n.id,tag:n.tagName})),
      mainOverflow:Math.max(0,main.scrollWidth-main.clientWidth),anchors:[...main.querySelectorAll('[id]')].map(n=>n.id)};
    });
    assert.deepEqual(result.overflow,[],'No learner overflow outside explicit table scroll regions'); assert.equal(result.mainOverflow,0);
    assert.equal(result.anchors.length,new Set(result.anchors).size,'No duplicate IDs');
    await page.screenshot({path:path.join(dir,state+'.png'),fullPage:true});
    if(after){
     const before=beforeRows.find(r=>r.state===state);assert.ok(before);
     assert.ok(before.anchors.every(id=>result.anchors.includes(id)),'Every original BEFORE anchor preserved');
     assert.deepEqual([result.title,result.h1,result.canonical],[before.title,before.h1,before.canonical]);result.beforeAnchorsPreserved=before.anchors.length;
     if(key==='reference'){
      assert.equal(result.fullText,stripLegacyBuilder(before.fullText),'PPC reference unchanged outside dynamic builder');result.referenceTextUnchanged=true;
     }else{
      const lesson=master.lessons[['A','B','C'].indexOf(key)];result.fidelity=await fidelity(page,lesson,key);assert.equal(result.practice,6);
      result.viewportScreenshots=[];const forms=page.locator('[data-m44-native-layout="forms-grid"]');
      if(await forms.count())result.viewportScreenshots.push(await shot(page,forms.first(),state+'-forms-viewport.png'));
      const use=lesson.sections.find(s=>s.native_kind==='usage-panels')||lesson.sections[1];
      result.viewportScreenshots.push(await shot(page,page.locator('[data-m44-author-section="'+use.id+'"]'),state+'-use-viewport.png'));
      result.detailAcceptance=await detailsCheck(page,lesson,state);
      if(!mobile&&!dark)result.practiceSmoke=await practiceSmoke(page,lesson);
     }
    }
    assert.deepEqual(errors,[],'No page errors');assert.deepEqual(consoleErrors,[],'No console errors');assert.deepEqual(failed,[],'No HTTP errors');
    assert.deepEqual(requestFailures,[],'No network failures');assert.deepEqual(nonGET,[],'No progress writes attempted');
    results.push({state,url,status:response.status(),errors,failed,consoleErrors,requestFailures,nonGET,...result});console.log(state,'PASS',Math.round(result.theoryHeight));
   }finally{await context.close();}
  }
  assert.deepEqual(sourceHashes(),sourceBefore,'Source hashes unchanged throughout browser run');pass=true;
 }finally{
  await browser.close();fs.writeFileSync(path.join(dir,'results.json'),JSON.stringify(results,null,2)+'\n',{flag:'wx'});
  fs.writeFileSync(path.join(dir,'source-stability.json'),JSON.stringify({pass,phase,at:new Date().toISOString(),masterSha256:masterSha,beforeSha256:beforeBytes?hash(beforeBytes):null,sourceBefore,sourceAfter:sourceHashes()},null,2)+'\n',{flag:'wx'});
 }
})().catch(e=>{console.error(e);process.exitCode=1;});
