'use strict';
// Acceptance for the presentation-only M41 follow-up. GET/browser interactions
// only; no application/DB/source edits, no production requests.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),crypto=require('node:crypto');
const {JSDOM}=require('jsdom'),m41=require('./seo-m41-local.cjs'),legacy=require('./seo-m39-local.cjs');
const {htmlText}=require('./seo-m28-local.cjs');
const BASE='http://gramlyze.loc',ROOT=path.resolve(__dirname,'../..'),norm=v=>String(v||'').replace(/\s+/gu,' ').trim();
const sha=v=>crypto.createHash('sha256').update(v).digest('hex');
const write=(file,value)=>{assert.ok(!fs.existsSync(file),'Exclusive diagnostic evidence');fs.writeFileSync(file,JSON.stringify(value,null,2),{flag:'wx'});};
const read=(dir,name)=>JSON.parse(fs.readFileSync(path.join(dir,name),'utf8'));
function designHashes(){const files=['app/Support/M41AuthoredTenseComparisonsPackage.php','app/Support/M41ExistingDesignPackage.php','database/content-patches/m41-existing-native-design.v1.json',
 'resources/views/theory/partials/content-block.blade.php','resources/views/theory/partials/point-detail-fragment.blade.php','resources/views/engram/theory/widgets/lesson-rule-cards.blade.php',
 ...['forms-grid','usage-panels','comparison-table','mistakes-grid','summary-list','m41-existing-design-section','m41-existing-design-styles','m41-native-point-heading','m41-native-mistakes','m41-native-example','m41-native-detail'].map(n=>'resources/views/engram/theory/blocks-v3/'+n+'.blade.php')];
 return{...m41.sourceHashes(),...Object.fromEntries(files.map(file=>[file,sha(fs.readFileSync(path.join(ROOT,file)))]))};}
function authorFidelity(doc,target){
 const lesson=m41.masterLesson(target),main=doc.querySelector('[data-theory-main]');assert.ok(main);
 assert.deepEqual([...doc.querySelectorAll('h1')].map(n=>norm(n.textContent)),[lesson.title]);
 const basic=main.cloneNode(true);basic.querySelectorAll('details,script,style,noscript').forEach(n=>n.remove());const text=htmlText(basic.innerHTML);
 assert.ok(text.includes(lesson.subtitle));for(const rule of lesson.hero)for(const key of ['label','text','formula'])assert.ok(text.includes(rule[key]));
 const result={sections:[],points:0,details:0,masterSha:sha(fs.readFileSync(path.join(ROOT,'docs/content/m41-authored-tense-comparisons.v1.0.1.json')))};
 assert.equal(result.masterSha,'9020cf977e903c76f93c7165bbe402567b5fbf40a98a8139dc6751b223b34553');
 assert.deepEqual([...main.querySelectorAll('[data-m41-author-section]')].map(n=>n.dataset.m41AuthorSection),lesson.sections.map(s=>s.id),'Exact master section order');
 for(const section of lesson.sections){
  const owners=[...main.querySelectorAll('[data-m41-author-section]')].filter(n=>n.dataset.m41AuthorSection===section.id);assert.equal(owners.length,1,'One own section '+section.id);
  const owner=owners[0],ids=[];
  const sectionHeading=owner.querySelector('.theory-section-header h2');assert.ok(sectionHeading,'Complete native section heading');const cleanHeading=sectionHeading.cloneNode(true);cleanHeading.querySelectorAll('[data-theory-ui]').forEach(n=>n.remove());assert.equal(norm(cleanHeading.textContent),section.title,'Exact original authored section title/number');
  assert.deepEqual([...owner.querySelectorAll('[data-m41-basic-point]')].map(n=>n.id),section.points.map(p=>p.id),'Exact master basic point order');
  for(const point of section.points){
   const cards=[...owner.querySelectorAll('[id]')].filter(n=>n.id===point.id);assert.equal(cards.length,1,'One authored point '+point.id);
   const card=cards[0],copy=card.cloneNode(true);copy.querySelectorAll('[data-theory-native-extension],details,script,style,noscript,[data-theory-ui]').forEach(n=>n.remove());
   const ownText=htmlText(copy.innerHTML);assert.ok(ownText.includes(point.title));
   for(const value of point.paragraphs_uk)assert.ok(ownText.includes(value),'Exact visible paragraph '+point.id);
   for(const example of point.examples)for(const key of ['en','uk'])assert.ok(ownText.includes(example[key]),'Exact visible example and translation '+point.id);
   const detail=card.querySelectorAll('[data-theory-native-extension] > details');assert.equal(detail.length,point.detail?1:0,'One finite meaningful detail '+point.id);
   if(point.detail){const depth=htmlText(detail[0].querySelector('.theory-point-fragment')?.innerHTML);for(const value of [point.detail.title,...point.detail.paragraphs_uk,...point.detail.examples.flatMap(e=>[e.en,e.uk])])assert.ok(depth.includes(value));result.details++;}
   ids.push(point.id);result.points++;
  }
  if(section.table){const table=owner.querySelector('table');if(table){assert.deepEqual([...table.querySelectorAll('thead th')].map(n=>norm(n.textContent)),section.table.columns);
   assert.deepEqual([...table.querySelectorAll('tbody tr')].map(tr=>[...tr.children].map(td=>htmlText(td.innerHTML))),section.table.rows.map(row=>row.map(norm)));}
   else {const columns=[...owner.querySelectorAll('[data-m41-form-column]')];assert.deepEqual(columns.map(n=>norm(n.textContent)),section.table.columns);
    for(const [r,row]of section.table.rows.entries()){const label=owner.querySelector('[data-m41-form-row-label="'+r+'"]');assert.ok(label);assert.equal(norm(label.textContent),norm(row[0]));
     for(const[c,cell]of row.entries())if(c>0){const own=owner.querySelectorAll('[data-m41-form-cell="'+r+'-'+c+'"]');assert.equal(own.length,1);const en=own[0].querySelectorAll('[data-m41-form-en]'),uk=own[0].querySelectorAll('[data-m41-form-uk]');assert.equal(en.length,1);assert.equal(uk.length,1);
      assert.equal(norm(en[0].textContent+' '+uk[0].textContent),norm(cell),'Exactly one original grammar formula cell, separate English/Ukrainian');}}
    assert.equal(owner.querySelectorAll('[data-m41-form-cell]').length,section.table.rows.length*(section.table.columns.length-1),'No hidden duplicate table/cells');}}
  result.sections.push({id:section.id,pointIds:ids,points:ids.length});
 }
 assert.equal(main.querySelectorAll('[data-theory-native-extension] > details').length,result.details);
 const ids=[...main.querySelectorAll('[id]')].map(n=>n.id);assert.equal(new Set(ids).size,ids.length,'No duplicated original/new anchor');return result;
}
async function detailsQA(page,target,dir,stem,capture){
 const points=m41.masterLesson(target).sections.flatMap(s=>s.points).filter(p=>p.detail),details=page.locator('[data-theory-native-extension] > details');assert.equal(await details.count(),points.length);
 assert.equal(await details.evaluateAll(ns=>ns.every(n=>!n.open)),true);const rows=[];
 for(const point of points){const own=page.locator('[id="'+point.id+'"]');assert.equal(await own.count(),1);const detail=own.locator('[data-theory-native-extension] > details'),summary=detail.locator(':scope > summary');
  const positions=await summary.evaluate(n=>{const card=n.closest('.theory-item');const sr=n.getBoundingClientRect(),cr=card?.getBoundingClientRect();return{insideOwnCard:Boolean(card),withinBounds:cr?sr.left>=cr.left&&sr.right<=cr.right&&sr.top>=cr.top&&sr.bottom<=cr.bottom:null};});
  assert.equal(positions.insideOwnCard,true,'Disclosure is inside its concrete native colored card');assert.equal(positions.withinBounds,true,'Disclosure sits within its own card bounds');
  // Point identity owns the disclosure irrespective of its legacy component tag.
  assert.equal(await detail.evaluate((n,id)=>Boolean(n.closest('[id="'+id+'"]')),point.id),true);
  await summary.click();assert.equal(await detail.evaluate(n=>n.open),true);assert.equal(await details.evaluateAll(ns=>ns.filter(n=>n.open).length),1);
  const screenshot=capture?await m41.shot(own,dir,stem+'-'+point.id+'-open.png'):null;
  await summary.click();await summary.focus();await summary.press('Enter');assert.equal(await detail.evaluate(n=>n.open),true);assert.equal(await summary.evaluate(n=>n.matches(':focus-visible')),true);
  await summary.press('Space');assert.equal(await detail.evaluate(n=>n.open),false);
  const fragment=await detail.locator('.theory-point-fragment').getAttribute('id');assert.ok(fragment);
  rows.push({point:point.id,fragment,ownPoint:true,mouseEnterSpace:true,independent:true,positions,screenshot});
 }
 return rows;
}
async function pages(dir,label){
 const {chromium}=require('playwright'),file=path.join(dir,label+'-design-pages.json');assert.ok(!fs.existsSync(file));
 const report={at:new Date().toISOString(),base:BASE,sourceHashes:designHashes(),conditions:{zoom:'100%',deviceScaleFactor:1},rows:[],violations:[],pass:false};
 const browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});
 try{for(const viewport of [{width:1440,height:1000},{width:390,height:844}])for(const theme of ['light','dark'])for(const target of m41.targets){
  const context=await browser.newContext({viewport,colorScheme:theme,deviceScaleFactor:1}),page=await context.newPage();
  const row={path:target.path,viewport,theme,errors:[],httpErrors:[],failed:[],screenshots:[],componentStyles:[]};report.rows.push(row);
  try{await m41.pageReady(page,target,row,report.violations,viewport);await page.evaluate(t=>{document.documentElement.classList.toggle('dark',t==='dark');localStorage.setItem('theme',t);},theme);row.theme=theme;
   const dom=new JSDOM(await page.content());try{row.author=authorFidelity(dom.window.document,target);}finally{dom.window.close();}
   row.visibleBasic=[];for(const point of m41.masterLesson(target).sections.flatMap(s=>s.points)){const own=page.locator('[id="'+point.id+'"]');assert.equal(await own.isVisible(),true,'Every basic point is visible before disclosure');
    if(!['past-error-did','past-error-ing','present-form-errors','perfect-real-errors'].includes(point.id))assert.equal(await own.locator('.theory-example--wrong,[data-m41-error-fragment]').count(),0,'A valid contextual comparison is never labelled a grammar error');
    for(const example of point.examples)for(const field of ['en','uk']){const matches=await own.getByText(example[field],{exact:true}).all();assert.ok(matches.length>0,'Exact basic example DOM '+point.id);let visible=false;for(const match of matches)if(await match.isVisible())visible=true;assert.equal(visible,true,'Main English/example translation is never hidden behind detail');}
    row.visibleBasic.push(point.id);}
   row.nativeVisual=await page.locator('[data-theory-main]').evaluate(root=>{
    const errors=[...root.querySelectorAll('[data-m41-error-fragment]')].map(n=>{const box=n.closest('.theory-example'),css=getComputedStyle(box);return{role:n.dataset.m41ErrorFragment,text:n.textContent,background:css.backgroundColor,border:css.borderColor,color:getComputedStyle(n).color,ownPoint:n.closest('[data-m41-author-point]')?.id};});
    const formNotes=[...root.querySelectorAll('[data-m41-native-component="forms-note"]')].map(n=>{const card=n.querySelector('.theory-item');return{id:n.id,cards:n.querySelectorAll('.theory-item').length,nestedRuleBoxes:n.querySelectorAll('.theory-rule').length,widthRatio:card?card.getBoundingClientRect().width/n.getBoundingClientRect().width:0};});return{errors,formNotes};});
   const expectedErrors=target.slug==='past-simple-vs-past-continuous'?2:3;assert.equal(row.nativeVisual.errors.filter(e=>e.role==='wrong').length,expectedErrors);assert.equal(row.nativeVisual.errors.filter(e=>e.role==='right').length,expectedErrors);
   for(const error of row.nativeVisual.errors){assert.equal(error.background,theme==='light'?(error.role==='wrong'?'rgb(255, 241, 242)':'rgb(236, 253, 245)'):(error.role==='wrong'?'rgb(64, 35, 48)':'rgb(19, 55, 47)'),'Real native wrong/right background wins global flattening');}
   for(const note of row.nativeVisual.formNotes){assert.equal(note.cards,1,'Single native note card');assert.equal(note.nestedRuleBoxes,0,'No nested border within the note');assert.ok(note.widthRatio>.95,'Native forms note uses the whole content width');}
   assert.equal(await page.evaluate(()=>visualViewport.scale),1);const stem=label+'-'+target.slug+'-'+viewport.width+'-'+theme;
   row.details=await detailsQA(page,target,dir,stem,viewport.width===1440&&theme==='light');
   for(const section of m41.masterLesson(target).sections){const block=page.locator('[data-m41-author-section="'+section.id+'"]');assert.equal(await block.count(),1);
    row.screenshots.push(await m41.shot(block,dir,stem+'-'+section.id+'-closed.png'));
    row.componentStyles.push(await block.evaluate(el=>{const css=n=>{if(!n)return null;const c=getComputedStyle(n);return{background:c.backgroundColor,border:c.borderColor,borderWidth:c.borderWidth,radius:c.borderRadius,font:c.fontFamily,size:c.fontSize,weight:c.fontWeight,padding:c.padding,transform:c.textTransform,lineHeight:c.lineHeight};};return{section:el.dataset.m41AuthorSection,layout:el.dataset.m41NativeLayout||null,components:[...el.querySelectorAll('[data-m41-native-component]')].map(n=>n.dataset.m41NativeComponent),card:css(el.querySelector('.theory-section-card')),header:css(el.querySelector('.theory-section-header')),title:css(el.querySelector('h2')),point:css(el.querySelector('.theory-item')),example:css(el.querySelector('.theory-example')),english:css(el.querySelector('.theory-example [lang="en"]')),translation:css(el.querySelector('.theory-translation'))};}));
   }
   row.nativeTables=[];for(const [tableIndex,table]of(await page.locator('.theory-table-scroll').all()).entries()){
    const layout=await table.evaluate(region=>{const table=region.querySelector('table');return{headers:[...table.querySelectorAll('thead th')].map(n=>n.textContent.trim()),rowColumns:[...table.querySelectorAll('tbody tr')].map(n=>n.querySelectorAll('td').length),emptyCells:[...table.querySelectorAll('tbody td')].filter(n=>!n.textContent.trim()).length,english:[...table.querySelectorAll('code[lang="en"]')].map(n=>{const c=getComputedStyle(n);return{text:n.textContent,font:c.fontFamily,size:c.fontSize,transform:c.textTransform};})};});
    assert.equal(layout.headers.length,2,'Exactly English and Ukrainian native table columns');assert.ok(layout.rowColumns.length>0&&layout.rowColumns.every(n=>n===2));assert.equal(layout.emptyCells,0,'No empty technical notes column');assert.ok(layout.english.length>0);for(const english of layout.english){assert.equal(english.size,'14px');assert.match(english.font,/monospace/u);assert.equal(english.transform,'none');}row.nativeTables.push(layout);
    assert.equal(await table.evaluate(n=>Math.max(0,n.scrollWidth-n.clientWidth)>=0),true);if(await table.evaluate(n=>n.scrollWidth>n.clientWidth)){
    await table.focus();assert.equal(await table.evaluate(n=>document.activeElement===n),true);await page.keyboard.press('ArrowRight');await page.waitForFunction(n=>n.scrollLeft>0,await table.elementHandle());
    for(let i=0;i<18;i++)await page.keyboard.press('ArrowRight');await page.waitForFunction(n=>n.scrollLeft>=n.scrollWidth-n.clientWidth-1,await table.elementHandle());
    row.screenshots.push(await m41.shot(table,dir,stem+'-table-'+(tableIndex+1)+'-right.png'));for(let i=0;i<18;i++)await page.keyboard.press('ArrowLeft');await page.waitForFunction(n=>n.scrollLeft===0,await table.elementHandle());}}
   row.overflow=await legacy.overflow(page);const baseline=read(dir,'before-design-v1-http.json').rows.find(r=>r.path===target.path);
   const owner=read(dir,'m41-bank-inventory-v1.json').targets.find(r=>r.slug===target.slug),expectedBank=target.data.linked_practice.seeder_classes[0];
   const allowed=owner?.linked_bank_ids?.[expectedBank];assert.ok(allowed?.length);const widget=page.locator('[data-m41-practice-ui] [data-sentence-builder]');assert.equal(await widget.count(),1);
   const pool=await widget.evaluate(n=>{const s=window.Alpine.$data(n);return{ids:s.questions.map(q=>q.id),types:[...new Set(s.questions.map(q=>String(q.type)))]};});
   assert.ok(pool.ids.length>0);assert.equal(new Set(pool.ids).size,pool.ids.length);assert.ok(pool.ids.every(id=>allowed.includes(id)));assert.deepEqual(pool.types,['4']);
   row.bank={class:expectedBank,ownCount:allowed.length,sampled:pool.ids.length,exactOwnBank:true};
   const original=baseline.nativeBlockIds.filter(id=>/^block-\d+$/u.test(id));row.originalAnchors=[];
   for(const id of original){assert.equal(await page.locator('[id="'+id+'"]').count(),1);row.originalAnchors.push(id);}
   const pointFragments=row.details.map(r=>r.fragment);row.deep=[];
   for(const id of pointFragments){await page.goto(BASE+target.path+'#'+id,{waitUntil:'networkidle'});await page.waitForFunction(id=>document.getElementById(id)?.closest('details')?.open,id);row.deep.push(id);}
   await page.goto(BASE+target.path,{waitUntil:'networkidle'});await page.emulateMedia({media:'print'});await page.waitForFunction(()=>[...document.querySelectorAll('[data-theory-native-extension] > details')].every(n=>n.open));
   await page.emulateMedia({media:'screen'});await page.waitForFunction(()=>[...document.querySelectorAll('[data-theory-native-extension] > details')].every(n=>!n.open));
   row.print=true;await page.reload({waitUntil:'networkidle'});assert.equal(await page.locator('[data-theory-native-extension] > details[open]').count(),0);row.reloadClosed=true;
   await page.evaluate(t=>{document.documentElement.classList.toggle('dark',t==='dark');localStorage.setItem('theme',t);window.scrollTo(0,0);},theme);
   const top=stem+'-top.png';await page.screenshot({path:path.join(dir,top),animations:'disabled'});row.screenshots.push(top);legacy.clean(row);row.pass=true;
  }finally{await context.close();}console.log(JSON.stringify({path:target.path,width:viewport.width,theme,pass:row.pass}));
 }assert.equal(report.rows.length,12);assert.deepEqual(report.violations,[]);assert.deepEqual(designHashes(),report.sourceHashes,'Design source is frozen during browser QA');report.pass=true;}finally{await browser.close();write(file,report);}return report;
}
async function noJS(browser,target,dir,label,report){
 const context=await browser.newContext({javaScriptEnabled:false,viewport:{width:390,height:844}}),page=await context.newPage(),row={path:target.path,javaScriptEnabled:false,errors:[],httpErrors:[],failed:[],expectedDisabledScripts:[],keys:[],tokenBanks:[],details:[]};report.noJS.push(row);
 try{await legacy.observe(page,row,report.violations);const r=await page.goto(BASE+target.path,{waitUntil:'networkidle'});assert.equal(r.status(),200);const dom=new JSDOM(await page.content());try{row.author=authorFidelity(dom.window.document,target);}finally{dom.window.close();}
  for(const detail of await page.locator('[data-theory-native-extension] > details').all()){const summary=detail.locator(':scope > summary');await summary.click();assert.equal(await detail.evaluate(n=>n.open),true);await summary.focus();await summary.press('Space');assert.equal(await detail.evaluate(n=>n.open),false);await summary.press('Enter');assert.equal(await detail.evaluate(n=>n.open),true);await summary.click();row.details.push({mouseSpaceEnter:true});}
  for(const task of target.data.cases){const card=await m41.caseLocator(page,task);assert.equal(htmlText(await card.locator('[data-m41-author-prompt]').innerHTML()),htmlText(target.data.author_self_check.prompts[task.source_index-1]));
   const detail=card.locator('[data-m41-ui-explanation]'),key=card.locator('[data-m41-self-check-answer]'),summary=detail.locator(':scope > summary');assert.equal(htmlText(await key.innerHTML()),htmlText(target.data.author_self_check.answers[task.source_index-1]));
   await summary.click();assert.equal(await key.isVisible(),true);await summary.focus();await summary.press('Space');assert.equal(await key.isVisible(),false);await summary.press('Enter');assert.equal(await key.isVisible(),true);await summary.click();row.keys.push({source:task.source_index,exact:true,mouseSpaceEnter:true});
   for(const control of task.controls.filter(c=>c.kind==='manual')){const tokens=m41.field(card,control).locator('[data-m41-static-token]');assert.deepEqual((await tokens.allTextContents()).map(norm),control.tokens);for(const token of await tokens.all())assert.equal(await token.isVisible(),true);row.tokenBanks.push({id:control.id,groups:control.tokens.length});}
  }row.overflow=await m41.measure(page);legacy.clean(row);row.pass=true;
 }finally{await context.close();}return row;
}
async function supplemental(dir,label,beforeLabel){
 const pre=read(dir,beforeLabel+'-visual.json');assert.equal(pre.pass,true);assert.equal(pre.caseStates,36);assert.deepEqual(pre.sourceHashes,m41.sourceHashes());
 const {chromium}=require('playwright'),file=path.join(dir,label+'-design-supplemental.json');assert.ok(!fs.existsSync(file));
 const browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE}),report={at:new Date().toISOString(),base:BASE,sourceHashes:designHashes(),missingParts:[],semantic:[],noJS:[],regressions:[],violations:[],pass:false};
 try{for(const target of m41.targets){const context=await browser.newContext({viewport:{width:1440,height:1000}}),page=await context.newPage(),row={path:target.path,errors:[],httpErrors:[],failed:[],cases:[]};report.missingParts.push(row);
  try{await m41.pageReady(page,target,row,report.violations,{width:1440,height:1000});
   for(const task of target.data.cases.filter(t=>t.controls.length>1)){const card=await m41.caseLocator(page,task);for(const [i,absent]of task.controls.entries()){
    for(const [j,control]of task.controls.entries())if(i!==j){if(control.kind==='manual')await m41.field(card,control).locator('textarea').fill(control.answer);else await m41.option(m41.field(card,control),control,control.answer).click();}
    await card.locator('[data-m41-check]').click();await m41.checkedState(card,false);assert.equal(norm(await page.locator('[data-m41-ui-score]').textContent()),'Результат: 0 / 6');
    await m41.fillCorrect(card,task);await card.locator('[data-m41-check]').click();await m41.checkedState(card,true);row.cases.push({source:task.source_index,missing:absent.id,incompleteRejected:true});await m41.resetCase(card,task);}}
   const fixtures=[];for(const [caseIndex,task]of target.data.cases.entries())for(const[controlIndex,control]of task.controls.entries())if(control.kind!=='manual')for(const option of control.options.filter(o=>o.value!==control.answer))fixtures.push({caseIndex,controlIndex,value:option.value,boundary:'Incorrect finite source option'});
   fixtures.push(...require('./m41-semantic-fixtures.cjs').semanticFixtures(target));for(const fixture of fixtures){const task=target.data.cases[fixture.caseIndex],control=task.controls[fixture.controlIndex],card=await m41.caseLocator(page,task);await m41.fillCorrect(card,task);
    if(control.kind==='manual')await m41.field(card,control).locator('textarea').fill(fixture.invalid);else await m41.option(m41.field(card,control),control,fixture.value).click();await card.locator('[data-m41-check]').click();await m41.checkedState(card,false);report.semantic.push({path:target.path,source:task.source_index,control:control.id,boundary:fixture.boundary,rejected:true});await m41.resetCase(card,task);}
   legacy.clean(row);row.pass=true;
  }finally{await context.close();}await noJS(browser,target,dir,label,report);
 }
 for(const[prefix,previous]of[['m39',require('./seo-m39-practice-ui.cjs')],['m40',require('./seo-m40-local.cjs')]])for(const target of previous.targets){const context=await browser.newContext({viewport:{width:1440,height:1000}}),page=await context.newPage(),row={prefix,path:target.path,errors:[],httpErrors:[],failed:[],cases:[]};report.regressions.push(row);
  try{await previous.pageReady(page,target,row,report.violations,{width:1440,height:1000});for(const task of target.data.cases)row.cases.push(await previous.acceptanceCase(page,target,task,dir,label+'-'+prefix+'-'+target.slug+'-'+task.source_index));legacy.clean(row);row.pass=true;}finally{await context.close();}await m41.noJSPage(browser,target,dir,label,prefix,report);}
 assert.equal(report.missingParts.flatMap(r=>r.cases).length,28);assert.equal(report.regressions.flatMap(r=>r.cases).length,36);assert.equal(report.noJS.length,9);assert.deepEqual(report.violations,[]);assert.deepEqual(designHashes(),report.sourceHashes);report.pass=true;
 }finally{await browser.close();write(file,report);}return report;
}
async function http(dir,label){const helper=require('./capture-m41-http.cjs'),before=read(dir,'before-design-v1-http.json'),after=await helper.capture();assert.equal(before.pass,true);assert.equal(after.pass,true);assert.deepEqual(after.sitemap,before.sitemap);
 assert.deepEqual(after.rows.map(r=>r.path),before.rows.map(r=>r.path));for(const row of after.rows){const old=before.rows.find(r=>r.path===row.path);assert.deepEqual(row.meta,old.meta,'Exact presentation-only metadata');assert.equal(row.xRobots,old.xRobots);assert.equal(row.contentType,old.contentType);assert.equal(row.details,old.details);if(!helper.targetPaths.includes(row.path))assert.equal(row.mainTextSha256,old.mainTextSha256,'Unchanged control main content');for(const id of old.nativeBlockIds||[])assert.ok(row.nativeBlockIds.includes(id));}
 after.presentationOnlyEquality=true;write(path.join(dir,label+'-design-http.json'),after);console.log(JSON.stringify({pass:true,rows:after.rows.length,sitemap:after.sitemap}));return after;}
async function control(dir,label,beforeLabel){
 const {chromium}=require('playwright'),file=path.join(dir,label+'-native-control.json'),route='/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms';assert.ok(!fs.existsSync(file));
 const baseline=beforeLabel?read(dir,beforeLabel+'-native-control.json'):null,report={at:new Date().toISOString(),base:BASE,path:route,rows:[],violations:[],pass:false};
 console.log(JSON.stringify({phase:'control-launch',at:new Date().toISOString(),label}));
 const browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});
 try{for(const viewport of[{width:1440,height:1000},{width:390,height:844}])for(const theme of['light','dark']){
  const context=await browser.newContext({viewport,colorScheme:theme,deviceScaleFactor:1}),page=await context.newPage(),row={viewport,theme,errors:[],httpErrors:[],failed:[]};report.rows.push(row);
  console.log(JSON.stringify({phase:'control-state',width:viewport.width,theme,at:new Date().toISOString()}));
  await context.addInitScript(t=>localStorage.setItem('theme',t),theme);try{await legacy.observe(page,row,report.violations);const response=await page.goto(BASE+route,{waitUntil:'networkidle'});assert.equal(response.status(),200);console.log(JSON.stringify({phase:'control-loaded',width:viewport.width,theme}));await page.waitForFunction(()=>Boolean(window.Alpine));
   await page.evaluate(t=>document.documentElement.classList.toggle('dark',t==='dark'),theme);const dom=new JSDOM(await page.content());try{const main=dom.window.document.querySelector('[data-theory-main]').cloneNode(true);main.querySelectorAll('script,style,[x-data]').forEach(n=>n.remove());row.mainTextSha=sha(htmlText(main.innerHTML));}finally{dom.window.close();}
   row.styles=await page.locator('[data-theory-main]').evaluate(el=>{const css=n=>{if(!n)return null;const c=getComputedStyle(n);return{background:c.backgroundColor,border:c.borderColor,radius:c.borderRadius,font:c.fontFamily,size:c.fontSize,padding:c.padding,width:n.getBoundingClientRect().width};};return{main:css(el),card:css(el.querySelector('.theory-section-card')),title:css(el.querySelector('.theory-section-header h2')),point:css(el.querySelector('.theory-item')),example:css(el.querySelector('.theory-example')),translation:css(el.querySelector('.theory-translation')),sidebar:css(document.querySelector('[data-theory-aside]')),header:css(document.querySelector('header'))};});
   row.overflow=await legacy.overflow(page);console.log(JSON.stringify({phase:'control-measured',width:viewport.width,theme}));const stem=label+'-'+viewport.width+'-'+theme;row.top=stem+'-native-control-top.png';await page.screenshot({path:path.join(dir,row.top),animations:'disabled'});
   const component=page.locator('.theory-native-block:has(.theory-item)').first();row.component=await m41.shot(component,dir,stem+'-native-control-component.png');
   if(baseline){const old=baseline.rows.find(r=>r.viewport.width===viewport.width&&r.theme===theme);assert.ok(old);assert.equal(row.mainTextSha,old.mainTextSha);assert.deepEqual(row.styles,old.styles,'Unchanged control layout/font/colors/header/sidebar');row.unchanged=true;}
   legacy.clean(row);row.pass=true;
  }finally{await context.close();}
 }assert.equal(report.rows.length,4);assert.deepEqual(report.violations,[]);report.pass=true;}finally{await browser.close();write(file,report);}return report;
}
async function practice(dir,label,beforeLabel,initial){const hashes=designHashes();if(!initial){const pre=read(dir,beforeLabel+'-design-freeze.json');assert.equal(pre.pass,true);assert.deepEqual(pre.sourceHashes,hashes,'Main visual gate must reference the same actual native design sources');}
 await m41.run(dir,label,initial?'--visual-before-acceptance':'--acceptance',beforeLabel);assert.deepEqual(designHashes(),hashes);write(path.join(dir,label+'-design-freeze.json'),{at:new Date().toISOString(),pass:true,sourceHashes:hashes,phase:initial?'36 PRE, no scoring':'72 POST, after main visual gate'});}
function comparison(dir,label,pagesLabel){const refDir=path.join(dir,'../seo-m41-design-reference'),ref=read(refDir,'reference-v1-reference.json'),after=read(dir,pagesLabel+'-design-pages.json');assert.equal(ref.pass,true);assert.equal(after.pass,true);assert.deepEqual(after.sourceHashes,designHashes());
 const report={at:new Date().toISOString(),base:BASE,referenceCommit:'ab61310a81f2389353c264fe23266025fe49ffd6',sourceHashes:designHashes(),oldContent:'reference only; never restored into working database',
  limitations:['Current shared CSS visibly flattens historical native inner cards in the raw reference. The restored palette/borders/mono English type follow the historical native utility declarations in finite M41 scope, not pixel equality to that flattened screenshot.',
   'New author content changes text length and card/page height; those dimensions are not treated as pixel regressions.'],rows:[],pass:false};
 for(const row of after.rows){const old=ref.rows.find(r=>r.path===row.path&&r.viewport.width===row.viewport.width&&r.theme===row.theme);assert.ok(old);assert.deepEqual(row.viewport,old.viewport);
  const own={path:row.path,viewport:row.viewport,theme:row.theme,referenceScreenshots:old.components.map(c=>c.screenshot),afterScreenshots:row.screenshots,outerNative:[],typedComponents:[...new Set(row.componentStyles.flatMap(s=>s.components))]};
  const reference=old.components[0].styles;for(const section of row.componentStyles){for(const[key,properties]of[['card',['borderWidth','radius','font','size','padding']],['header',['font','size','weight','padding']],['title',['font','size','weight','transform']]])for(const property of properties)assert.equal(section[key]?.[property],reference[key]?.[property],'Existing native '+key+' '+property+' remains intact');
   own.outerNative.push({section:section.section,layout:section.layout,whiteOuterCardFrame:true,headerNumberLevelTypography:true,nativeComponents:section.components});}
  assert.ok(own.typedComponents.includes('forms-grid'));assert.ok(own.typedComponents.includes('usage-panel'));assert.ok(own.typedComponents.includes('summary-list'));assert.ok(own.typedComponents.includes('mistakes-grid'));
  own.realNativeColors=true;own.trueErrorPairs=row.nativeVisual.errors.filter(e=>e.role==='wrong').length;own.fullWidthSingleFormNote=row.nativeVisual.formNotes.every(n=>n.cards===1&&n.nestedRuleBoxes===0&&n.widthRatio>.95);report.rows.push(own);}
 assert.equal(report.rows.length,12);report.pass=true;write(path.join(dir,label+'-design-comparison.json'),report);return report;}
async function practiceDelta(dir,label,currentPre){const gate=read(dir,'design-main-pre-v3-review.json');assert.equal(gate.pass,true);assert.equal(gate.functional_scoring_authorized,true);assert.equal(gate.desktop_cases_reviewed+gate.mobile_cases_reviewed,36);
 assert.equal(sha(fs.readFileSync(path.join(dir,gate.manifest))),gate.manifest_sha256);assert.equal(sha(fs.readFileSync(path.join(dir,gate.source_freeze))),gate.source_freeze_sha256);
 const old=read(dir,gate.manifest),current=read(dir,currentPre+'-visual.json'),oldFreeze=read(dir,gate.source_freeze),nowFreeze=read(dir,currentPre+'-design-freeze.json');
 assert.equal(current.pass,true);assert.equal(current.caseStates,36);assert.equal(nowFreeze.pass,true);assert.deepEqual(nowFreeze.sourceHashes,designHashes());
 const changed=Object.keys(oldFreeze.sourceHashes).filter(file=>oldFreeze.sourceHashes[file]!==nowFreeze.sourceHashes[file]);assert.deepEqual(changed.slice().sort(),['resources/views/engram/theory/blocks-v3/comparison-table.blade.php','resources/views/engram/theory/blocks-v3/m41-existing-design-styles.blade.php'].sort());
 const strip=rows=>rows.map(row=>({path:row.path,viewport:row.viewport,theme:row.theme,cases:row.cases.map(item=>{const c={...item};delete c.pre;return c;})}));assert.deepEqual(strip(current.rows),strip(old.rows),'Every actual practice prompt/key/control/option/token count/state/natural casing is unchanged across all 36 cases');
 const css=fs.readFileSync(path.join(ROOT,changed.find(file=>file.endsWith('-styles.blade.php'))),'utf8').split('<style>')[1].split('</style>')[0].replace(/\/\*[\s\S]*?\*\//gu,'');
 const selectors=[...css.matchAll(/([^{}]+)\{/gu)].flatMap(match=>match[1].trim().startsWith('@media')?[]:match[1].trim().split(',').map(s=>s.trim()));assert.ok(selectors.length>0);assert.ok(selectors.every(selector=>selector.includes('.m41-existing-design[data-m41-author-section]')),'No new style can match outside finite authored native sections');
 const report={at:new Date().toISOString(),base:BASE,acceptedMainGate:gate.manifest,acceptedMainGateSha:gate.manifest_sha256,currentPre,currentPreSha:sha(fs.readFileSync(path.join(dir,currentPre+'-visual.json'))),currentSourceHashes:designHashes(),changedSourcePaths:changed,
  practiceMarkupFactoryMasterMappingUnchanged:true,all36PracticeSemanticsNaturalCasingMatch:true,allStyleSelectorsRequireNativeAuthorAncestor:true,rows:[],pass:false};
 for(const target of m41.targets){const response=await fetch(BASE+target.path,{headers:{Accept:'text/html'},redirect:'manual',signal:AbortSignal.timeout(30000)});assert.equal(response.status,200);const dom=new JSDOM(await response.text());try{const practice=dom.window.document.querySelector('[data-m41-practice-ui]');assert.ok(practice);assert.equal(practice.closest('.m41-existing-design[data-m41-author-section]'),null);assert.equal(practice.querySelectorAll('.m41-existing-design[data-m41-author-section],.theory-table-scroll').length,0);assert.equal(practice.querySelectorAll('[data-m41-ui-case]').length,6);
   report.rows.push({path:target.path,practiceOutsideEveryChangedCSSScope:true,noNativeComparisonPartialWithinPractice:true,cases:6});}finally{dom.window.close();}}
 assert.deepEqual(designHashes(),report.currentSourceHashes);report.pass=true;write(path.join(dir,label+'-practice-delta.json'),report);return report;}
if(require.main===module){const[mode,dir,label,before]=process.argv.slice(2);assert.equal(path.basename(dir),'seo-m41-local');assert.match(label,/^[a-z0-9-]+$/u);Promise.resolve(mode==='--pages'?pages(dir,label):mode==='--supplemental'?supplemental(dir,label,before):mode==='--control'?control(dir,label,before):mode==='--practice-pre'?practice(dir,label,null,true):mode==='--practice-post'?practice(dir,label,before,false):mode==='--comparison'?comparison(dir,label,before):mode==='--practice-delta'?practiceDelta(dir,label,before):http(dir,label)).catch(e=>{console.error(e.stack);process.exitCode=1;});}
module.exports={authorFidelity,detailsQA,pages,supplemental,noJS,http,control,designHashes,practice,comparison,practiceDelta};
