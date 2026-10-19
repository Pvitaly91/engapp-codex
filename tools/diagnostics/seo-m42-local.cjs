'use strict';
// Real guest GET/browser diagnostics for the finite M11-M24 presentation-only scope.
// Never publishes application data, writes progress, edits served sources, or visits production.
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const {JSDOM}=require('jsdom');
const old=require('./seo-m39-local.cjs'),m39=require('./seo-m39-practice-ui.cjs'),m40=require('./seo-m40-local.cjs'),m41=require('./seo-m41-local.cjs');
const {fidelity,metadata,htmlText}=require('./seo-m28-local.cjs');
const BASE='http://gramlyze.loc',ROOT=path.resolve(__dirname,'../..');
const norm=v=>String(v||'').replace(/\s+/gu,' ').trim(),sha=v=>crypto.createHash('sha256').update(v).digest('hex');
const available=[...old.allTargets.map(t=>t.group==='M39'?{...t,...m39.targets.find(a=>a.identity===t.identity),group:'M39'}:t),...m40.targets.map(t=>({...t,group:'M40'}))];
const registry=JSON.parse(fs.readFileSync(path.join(ROOT,'database/content-patches/m42-native-design-registry.v1.json'),'utf8'));
const targets=registry.targets.map(r=>{const t=available.find(t=>t.identity===r.identity);assert.ok(t,'Finite exact accepted source target');assert.equal(t.path,new URL(r.local_url).pathname);return {...t,registry:r};});
assert.equal(targets.length,42);assert.equal(new Set(targets.map(t=>t.identity)).size,42);assert.equal(new Set(targets.map(t=>t.path)).size,42);
const read=file=>JSON.parse(fs.readFileSync(file,'utf8'));
const save=(dir,name,value)=>{const file=path.join(dir,name);assert.ok(!fs.existsSync(file),'Evidence is exclusive: '+name);fs.writeFileSync(file,JSON.stringify(value,null,2),{flag:'wx'});};
const sourceHashes=()=>Object.fromEntries([...new Set(['database/content-patches/m42-native-design-registry.v1.json',...registry.targets.flatMap(t=>[t.definition,t.source,t.overlay_source].filter(Boolean))])].map(file=>[file,sha(fs.readFileSync(path.join(ROOT,file)))]));
const runtimePaths=['app/Support/M42NativeDesignPackage.php','database/content-patches/m42-native-design-registry.v1.json','database/content-patches/m42-native-design.v1.json','resources/views/theory/show.blade.php','resources/views/theory/partials/content-block.blade.php','resources/views/theory/partials/point-detail-fragment.blade.php','resources/views/engram/theory/widgets/lesson-rule-cards.blade.php',...['m42-native-design-styles','forms-grid','usage-panels','comparison-table','mistakes-grid','summary-list','authored-practice-ui','m39-practice-ui','m40-practice-ui','m41-practice-ui'].map(n=>'resources/views/engram/theory/blocks-v3/'+n+'.blade.php'),'public/js/authored-practice-ui.js','public/js/m39-practice-ui.js','public/js/m40-practice-ui.js','public/js/m41-practice-ui.js'];
const runtimeHashes=()=>Object.fromEntries(runtimePaths.map(file=>[file,sha(fs.readFileSync(path.join('D:/DEV/htdocs/gramlyze.loc',file)))]));
const practiceNodes=root=>[...root.querySelectorAll('[x-data],[data-m39-practice-ui],[data-m40-practice-ui],[data-m41-practice-ui]')].filter(n=>(n.getAttribute('x-data')||'').startsWith('theoryPracticeSet(')||n.matches('[data-m39-practice-ui],[data-m40-practice-ui],[data-m41-practice-ui]'));
function cloneText(node,{details=true,practice=true}={}){
 if(!node)return null;const copy=node.cloneNode(true);
 copy.querySelectorAll('script,style,noscript,[data-theory-ui],[data-sentence-builder]').forEach(n=>n.remove());
 if(!details)copy.querySelectorAll('[data-theory-native-extension],.theory-point-fragment').forEach(n=>n.remove());
 if(!practice)practiceNodes(copy).forEach(n=>n.remove());
 copy.querySelectorAll('summary').forEach(n=>n.remove());
 // Markers are native UI, not authored text (including the ordered summary bullet).
 copy.querySelectorAll('.theory-point-number,[data-theory-section-number],.theory-summary-number').forEach(n=>n.remove());
 // Equivalent to the repository htmlText separators, without reparsing every
 // clone into another JSDOM/window (bounded all42 mutation coverage).
 copy.querySelectorAll('br,p,li,td,th,h4').forEach(n=>n.after(copy.ownerDocument.createTextNode(' ')));
 return norm(copy.textContent);
}
function semantic(doc){
 const main=doc.querySelector('[data-theory-main]');assert.ok(main,'Main educational region exists');
 const sectionRoots=[...main.querySelectorAll('section.theory-native-block')].filter(n=>!n.parentElement.closest('section.theory-native-block'));
 const details=[...main.querySelectorAll('[data-theory-native-extension] > details')];
 const sections=sectionRoots.map(section=>({
  id:section.id,
  heading:cloneText(section.querySelector('.theory-section-header')),
  basic:cloneText(section,{details:false,practice:false}),
  points:[...section.querySelectorAll('article.theory-item')].filter(n=>!n.closest('details')).map(n=>cloneText(n,{details:false})),
  tables:[...section.querySelectorAll('table')].filter(n=>!n.closest('details')).map(table=>({headers:[...table.querySelectorAll('thead tr')].map(tr=>[...tr.children].map(c=>htmlText(c.innerHTML))),rows:[...table.querySelectorAll('tbody tr')].map(tr=>[...tr.children].map(c=>htmlText(c.innerHTML)))}))
 }));
 const ownDetails=details.map(d=>({id:d.querySelector('.theory-point-fragment')?.id||d.id,basic:cloneText(d.closest('article.theory-item')||d.parentElement,{details:false}),text:cloneText(d)}));
 const practiceRoots=practiceNodes(main).filter(n=>!n.parentElement.closest('[data-m39-practice-ui],[data-m40-practice-ui],[data-m41-practice-ui]'));
 const practice=practiceRoots.map(n=>({text:cloneText(n),configuration:n.getAttribute('x-data'),headings:[...n.querySelectorAll('h3,h4')].map(e=>norm(e.textContent)),prompts:[...n.querySelectorAll('[data-m39-author-prompt],[data-m40-author-prompt],[data-m41-author-prompt]')].map(e=>htmlText(e.innerHTML)),keys:[...n.querySelectorAll('[data-m39-self-check-answer],[data-m40-self-check-answer],[data-m41-self-check-answer],[data-m30-self-check-answers],[data-m31-self-check-answers],[data-m32-self-check-answers],[data-m33-self-check-answers],[data-m34-self-check-answers],[data-m35-self-check-answers],[data-m36-self-check-answers],[data-m37-self-check-answers],[data-m38-self-check-answers]')].map(e=>htmlText(e.innerHTML))}));
 const anchorIds=[...main.querySelectorAll('[id]')].map(n=>n.id);assert.equal(new Set(anchorIds).size,anchorIds.length,'Unique main anchors');
 // M42 promotes an existing native point-label span to h3; its exact text/order
 // is already protected by the complete own point/basic snapshots. This finite
 // presentational tag upgrade must not manufacture a new authored heading.
 return {meta:metadata(doc),sections,details:ownDetails,practice,anchors:anchorIds,mainText:cloneText(main,{details:true,practice:false}),headings:[...main.querySelectorAll('h2,h3,h4')].filter(n=>!n.closest('[data-sentence-builder]')&&!n.classList.contains('m42-point-label')).map(n=>cloneText(n))};
}
function equality(before,after){
 assert.deepEqual(after.meta,before.meta,'Exact SEO properties');assert.deepEqual(after.anchors,before.anchors,'Exact original anchors and order');
 assert.deepEqual(after.details,before.details,'Exact own point detail and binding');
 assert.deepEqual(after.practice,before.practice,'Exact original practice key/config/prompt/order');
 assert.deepEqual(after.sections,before.sections,'Every native section, point, table, note, and source order');
 assert.equal(after.mainText,before.mainText,'Complete educational content without any loss or duplicate');
 assert.deepEqual(after.headings,before.headings,'No heading change or duplicate author number');return true;
}
async function captureHTTP(dir,label){
 const report={at:new Date().toISOString(),base:BASE,sourceHashes:sourceHashes(),targets:[],pass:false};
 try{for(const t of targets){const response=await fetch(BASE+t.path,{headers:{Accept:'text/html'},redirect:'manual',signal:AbortSignal.timeout(30000)});assert.equal(response.status,200,t.path);const dom=new JSDOM(await response.text());try{const semanticBefore=semantic(dom.window.document);report.targets.push({identity:t.identity,path:t.path,status:response.status,contentType:response.headers.get('content-type'),xRobots:response.headers.get('x-robots-tag'),semantic:semanticBefore});}finally{dom.window.close();}console.log(JSON.stringify({phase:'semantic-before',path:t.path,pass:true}));}report.pass=true;}finally{save(dir,label+'-semantic.json',report);}return report;
}
async function observe(page,row,violations){await old.observe(page,row,violations);}
async function ready(page,t,row,violations,theme){
 await observe(page,row,violations);const response=await page.goto(BASE+t.path,{waitUntil:'domcontentloaded',timeout:60000});assert.equal(response.status(),200,t.path);
 await page.waitForFunction(()=>Boolean(window.Alpine));await page.waitForFunction(()=>[...document.querySelectorAll('[data-sentence-builder]')].every(n=>window.Alpine.$data(n)?.questions?.length>0));await page.evaluate(theme=>{document.documentElement.classList.toggle('dark',theme==='dark');localStorage.setItem('theme',theme);},theme);return response;
}
async function shot(locator,dir,name){return m41.shot(locator,dir,name);}
function practiceHelper(t){
 const base=t.group==='M39'?m39:t.group==='M40'?m40:t.group==='M41'?m41:null;if(!base)return null;
 const prefix=t.group.toLowerCase(),helper={...base};
 helper.caseLocator=(page,task)=>page.locator('[data-'+prefix+'-ui-case="'+task.source_index+'"]');
 helper.field=(card,c)=>card.locator('[data-'+prefix+'-control="'+c.id+'"]');
 helper.option=(field,c,value)=>{const option=c.options.find(o=>o.value===value);assert.ok(option);return field.getByRole(c.kind==='multi'?'checkbox':'radio',{name:option.label,exact:true});};
 helper.tokenProof=async(card,task)=>{const rows=[];for(const c of task.controls.filter(c=>c.kind==='manual')){const area=helper.field(card,c),input=area.locator('textarea'),tokens=area.locator('[data-'+prefix+'-token]');assert.equal(await tokens.count(),c.tokens.length);const first=tokens.first();await first.click();assert.equal(await first.isDisabled(),true);await input.press('ControlOrMeta+A');await input.press('Backspace');assert.equal(await first.isEnabled(),true);
  let remaining=c.answer;const used=new Set();while(remaining){const labels=(await tokens.allTextContents()).map(norm),index=labels.findIndex((label,i)=>!used.has(i)&&(remaining===label||remaining.startsWith(label+' ')));assert.ok(index>=0,'Canonical answer constructible from exact own token groups at word boundaries');await tokens.nth(index).click();used.add(index);remaining=remaining.slice(labels[index].length).trimStart();}assert.equal(await input.inputValue(),c.answer);rows.push({id:c.id,allOwnTokens:true,backspaceReusable:true});}return rows;};
 helper.keyboardProof=async(card,task)=>{const controls=[];for(const c of task.controls.filter(c=>c.kind!=='manual')){const area=helper.field(card,c);if(c.kind==='multi'){const selected=helper.option(area,c,c.answer[0]);await selected.focus();await selected.press('Space');assert.equal(await selected.getAttribute('aria-checked'),'false');await selected.press('Space');assert.equal(await selected.getAttribute('aria-checked'),'true');controls.push({id:c.id,spaceToggle:true});}else{const selected=helper.option(area,c,c.answer),index=c.options.findIndex(o=>o.value===c.answer);await selected.focus();await selected.press('ArrowRight');const next=helper.option(area,c,c.options[(index+1)%c.options.length].value);assert.equal(await next.getAttribute('aria-checked'),'true');await next.press('ArrowLeft');assert.equal(await selected.getAttribute('aria-checked'),'true');assert.equal(await selected.evaluate(n=>n.matches(':focus-visible')),true);controls.push({id:c.id,arrowCycle:true,focusVisible:true});}}
  const manual=task.controls.find(c=>c.kind==='manual');if(manual){const input=helper.field(card,manual).locator('textarea');await input.focus();await input.press('Control+Enter');assert.equal(await input.evaluate(n=>n.matches(':focus-visible')),true);}else{const check=card.locator('[data-'+prefix+'-check]');await check.focus();await check.press('Enter');assert.equal(await check.evaluate(n=>n.matches(':focus-visible')),true);}return{controls,checkKeyboard:true};};
 return helper;
}
function practiceData(t){return t.data||JSON.parse(t.after.page.blocks.find(b=>b.type==='practice-set').body);}
async function nativePractice(page,t,dir,stem,capture){
 const data=practiceData(t),root=page.locator('[x-data^="theoryPracticeSet("]');assert.equal(await root.count(),1);const groups=root.locator('.theory-exercise'),names=['selects','choices','inputs','rephrase'].filter(n=>data[n]?.length);assert.equal(await groups.count(),names.length);
 const result={groups:[],tasks:names.reduce((n,k)=>n+data[k].length,0),mechanics:null};
 for(const[g,name]of names.entries()){
  const group=groups.nth(g),items=data[name],check=group.getByRole('button',{name:'Перевірити',exact:true}),reset=group.getByRole('button',{name:'Спробувати ще раз',exact:true}),score=group.locator('p[x-text="scoreText(\''+name+'\')"]');
  const resetByKeyboard=async()=>{for(const input of await group.locator('input').all())await input.press('Escape');await reset.focus();await reset.press('Enter');};
  assert.equal(await score.isVisible(),false);const record={group:name,tasks:items.length,pre:capture?await shot(group,dir,stem+'-'+name+'-initial.png'):null};
  const fill=async()=>{for(const[i,item]of items.entries()){if(['inputs','rephrase'].includes(name)){await group.locator('input').nth(i).fill(item.answer.replace(/[.!?]+$/u,''));await old.completeSemanticParts(group,i,item);}else await group.locator('label').nth(i).locator('..').getByRole('button',{name:item.answer,exact:true}).click();}};
  await fill();await check.focus();await check.press('Enter');await score.waitFor({state:'visible'});old.assertScore(await score.textContent(),items.length,items.length);assert.equal(await check.evaluate(n=>n.matches(':focus-visible')),true);record.keyboardCheck=true;record.correct=true;record.correctShot=capture?await shot(group,dir,stem+'-'+name+'-correct.png'):null;
  if(capture)for(const[i,item]of items.entries())for(const accepted of item.accepted||[]){if(['inputs','rephrase'].includes(name)){await group.locator('input').nth(i).fill(accepted.replace(/[.!?]+$/u,''));await old.completeSemanticParts(group,i,item);}else await group.locator('label').nth(i).locator('..').getByRole('button',{name:accepted,exact:true}).click();await check.click();old.assertScore(await score.textContent(),items.length,items.length);}
  record.wrongParts=[];for(const[i,item]of items.entries()){
   await fill();if(['inputs','rephrase'].includes(name))await group.locator('input').nth(i).fill('M42 incorrect answer');else {const accepted=item.accepted||[item.answer],options=item.options||(name==='selects'?data.options:data.choice_options||['a','b']),wrong=options.find(v=>!accepted.includes(v));assert.ok(wrong,'Finite wrong candidate exists');await group.locator('label').nth(i).locator('..').getByRole('button',{name:wrong,exact:true}).click();}
   await check.click();old.assertScore(await score.textContent(),items.length-1,items.length);record.wrongParts.push({index:i,rejected:true,screenshot:capture?await shot(group,dir,stem+'-'+name+'-wrong-'+(i+1)+'.png'):null});
  }record.wrong=true;
  await resetByKeyboard();await score.waitFor({state:'hidden'});if(['inputs','rephrase'].includes(name))for(const input of await group.locator('input').all())assert.equal(await input.inputValue(),'');record.reset=true;record.keyboardReset=true;result.groups.push(record);
  record.missingCompoundParts=[];if(capture&&name==='inputs')for(const[i,item]of items.entries())for(const[missing,part]of(item.m38_semantic_checks||item.m39_semantic_checks||[]).entries()){
   // The previous checked result was reset above (and after every omission).
   // Do not try to activate its intentionally hidden reset button while filling.
   for(const[j,answer]of items.entries()){await group.locator('input').nth(j).fill(answer.answer);if(j!==i)await old.completeSemanticParts(group,j,answer);else{const prefix=item.m38_semantic_checks?'m38':'m39';for(const[k,p]of(item.m38_semantic_checks||item.m39_semantic_checks).entries())if(k!==missing)await group.locator('[data-'+prefix+'-semantic-checks="'+i+'"] [data-'+prefix+'-semantic-part="'+k+'"]').getByRole('button',{name:p.answer,exact:true}).click();}}
   await check.click();old.assertScore(await score.textContent(),items.length-1,items.length);record.missingCompoundParts.push({index:i,part:missing,rejected:true});await resetByKeyboard();
  }
 }
 result.mechanics=capture?await old.tokenAndManualProof(page,t):{exhaustiveCoverage:'This lesson desktop/light; all states still check every own question correct/wrong/reset, keyboard and visible token dimensions.'};return result;
}
async function practice(page,t,dir,stem,capture){
 const helper=practiceHelper(t);if(!helper)return nativePractice(page,t,dir,stem,capture);
 const result={tasks:[],total:0};for(const task of t.data.cases){const card=await helper.caseLocator(page,task);await helper.verifyCase(page,t,task,true);const row={source:task.source_index,pre:capture?await shot(card,dir,stem+'-case-'+task.source_index+'-initial.png'):null};
  await helper.fillCorrect(card,task);row.keyboard=await helper.keyboardProof(card,task);await helper.checkedState(card,true);row.correct=true;row.correctShot=capture?await shot(card,dir,stem+'-case-'+task.source_index+'-correct.png'):null;await helper.resetCase(card,task);
  row.tokenMechanics=capture?await helper.tokenProof(card,task):{exhaustiveCoverage:'This lesson desktop/light'};if(capture)await helper.resetCase(card,task);await helper.fillCorrect(card,task);const control=task.controls[0],field=helper.field(card,control);if(control.kind==='manual')await field.locator('textarea').fill('M42 incorrect answer');else if(control.kind==='multi'){await helper.option(field,control,control.answer[0]).click();}else await helper.option(field,control,control.options.find(o=>o.value!==control.answer).value).click();
  await card.locator('[data-'+t.group.toLowerCase()+'-check]').click();await helper.checkedState(card,false);row.wrong=true;row.wrongShot=capture?await shot(card,dir,stem+'-case-'+task.source_index+'-wrong.png'):null;await helper.resetCase(card,task);row.reset=true;row.missingParts=[];
  if(capture&&task.controls.length>1)for(const[absentIndex,absent]of task.controls.entries()){
   for(const[i,c]of task.controls.entries())if(i!==absentIndex){const area=helper.field(card,c);if(c.kind==='manual')await area.locator('textarea').fill(c.answer);else for(const value of c.kind==='multi'?c.answer:[c.answer])await helper.option(area,c,value).click();}
   await card.locator('[data-'+t.group.toLowerCase()+'-check]').click();await helper.checkedState(card,false);row.missingParts.push({id:absent.id,rejected:true});await helper.resetCase(card,task);
  }row.acceptedVariants=[];if(capture)for(const c of task.controls.filter(c=>c.kind==='manual'))for(const value of c.accepted||[c.answer]){
   await helper.fillCorrect(card,task);await helper.field(card,c).locator('textarea').fill(value.replace(/[.!?]+$/u,''));await card.locator('[data-'+t.group.toLowerCase()+'-check]').click();await helper.checkedState(card,true);row.acceptedVariants.push({id:c.id,withoutTerminalPunctuation:true,accepted:true});await helper.resetCase(card,task);
  }result.tasks.push(row);result.total++;
 }return result;
}
async function details(page,t,dir,stem,capture,deep){
 const loc=page.locator('[data-theory-native-extension] > details'),all=await loc.all(),result=[];assert.equal(await loc.evaluateAll(ns=>ns.every(n=>!n.open)),true);
 for(const[i,d]of all.entries()){
  const summary=d.locator(':scope > summary');const binding=await d.evaluate(n=>{const card=n.closest('.theory-item');return{insidePoint:Boolean(card),id:n.querySelector('.theory-point-fragment')?.id,parentText:card?.textContent?.trim(),nativeCardTag:card?.tagName};});assert.equal(binding.insidePoint,true,'Detail belongs to its own existing native point card (article or forms div)');assert.ok(binding.id);
  await summary.click();assert.equal(await d.evaluate(n=>n.open),true);assert.equal(await loc.evaluateAll(ns=>ns.filter(n=>n.open).length),1,'Only own detail opens');const detailShot=capture?await shot(d.locator('xpath=..'),dir,stem+'-detail-'+(i+1)+'-open.png'):null;
  await summary.click();await summary.focus();await summary.press('Enter');assert.equal(await d.evaluate(n=>n.open),true);assert.equal(await summary.evaluate(n=>n.matches(':focus-visible')),true);await summary.press('Space');assert.equal(await d.evaluate(n=>n.open),false);result.push({id:binding.id,ownPoint:true,mouseEnterSpace:true,independent:true,screenshot:detailShot});
 }
 if(deep)for(const row of result){await page.goto(BASE+t.path+'#'+row.id,{waitUntil:'networkidle'});await page.waitForFunction(id=>document.getElementById(id)?.closest('details')?.open,row.id);row.deep=true;}
 await page.goto(BASE+t.path,{waitUntil:'networkidle'});await page.emulateMedia({media:'print'});await page.waitForFunction(()=>[...document.querySelectorAll('[data-theory-native-extension] > details')].every(n=>n.open));await page.emulateMedia({media:'screen'});await page.waitForFunction(()=>[...document.querySelectorAll('[data-theory-native-extension] > details')].every(n=>!n.open));return {points:result,print:true};
}
async function tables(page,fullEdge=false){
 const result=[];
 for(const region of await page.locator('.theory-table-scroll').all()){
  const data=await region.evaluate(n=>({width:n.clientWidth,scroll:n.scrollWidth,initialLeft:n.scrollLeft,columns:n.querySelector('table')?.querySelectorAll('thead tr:first-child th').length,headers:[...n.querySelectorAll('thead th')].map(h=>({text:h.textContent,display:getComputedStyle(h).display,width:h.getBoundingClientRect().width})),overflow:getComputedStyle(n).overflowX,tabindex:n.getAttribute('tabindex')}));
  for(const header of data.headers){assert.notEqual(header.display,'none');assert.ok(header.width>0,'Every source comparison column is rendered');}
  if(data.scroll>data.width){
   await region.scrollIntoViewIfNeeded();await region.focus();assert.equal(await region.evaluate(n=>document.activeElement===n),true);
   const range=data.scroll-data.width,maxSteps=Math.ceil(range/20)+4;
   const edge=async key=>{const trace=[];for(let i=0;i<maxSteps;i++){const current=await region.evaluate(n=>n.scrollLeft);if(key==='ArrowLeft'?Math.abs(current)<=.5:current>=range-1)return{value:current,trace};await region.press(key);await page.waitForTimeout(450);trace.push(await region.evaluate(n=>n.scrollLeft));}return{value:await region.evaluate(n=>n.scrollLeft),trace};};
   data.baseline=await edge('ArrowLeft');assert.ok(Math.abs(data.baseline.value)<=.5,'Real keyboard establishes the left edge');
   if(fullEdge){data.farRight=await edge('ArrowRight');data.right=data.farRight.value;assert.ok(data.right>=range-1,'Keyboard reaches every source column at the far right');}
   else{await region.press('ArrowRight');await page.waitForTimeout(450);await region.press('ArrowRight');await page.waitForTimeout(450);data.right=await region.evaluate(n=>n.scrollLeft);}
   assert.ok(data.right>0,'Native keyboard scroll settles to the right');data.returnLeft=await edge('ArrowLeft');data.left=data.returnLeft.value;assert.ok(Math.abs(data.left)<=.5,'Native keyboard scroll settles at the left edge');data.keyboardLocalScroll=true;data.animationSettleMs=450;data.fullEdge=fullEdge;
  }
  result.push(data);
 }
 return result;
}
async function toc(page){
 const mobile=page.locator('.theory-mobile-toc'),isMobile=await mobile.count()===1&&await mobile.isVisible();if(isMobile&&!await mobile.evaluate(n=>n.open))await mobile.locator(':scope > summary').click();
 const menu=isMobile?mobile.locator('[data-theory-toc-links]'):page.locator('[data-theory-aside] [data-theory-toc-links]'),links=menu.locator('a[href^="#"]');assert.ok(await links.count()>0,'Actual source TOC has usable anchors');const rows=[];
 for(const link of await links.all()){const href=await link.getAttribute('href'),id=href.slice(1);assert.equal(await page.locator('[id="'+id+'"]').count(),1);await link.focus();await link.press('Enter');await page.waitForFunction(id=>{const node=document.getElementById(id),r=node?.getBoundingClientRect();return decodeURIComponent(location.hash.slice(1))===id&&r&&r.top>=-1&&r.top<innerHeight;},id);rows.push({id,keyboardEnter:true,exactSingleTarget:true,visibleAfterJump:true});}
 if(isMobile)await mobile.locator(':scope > summary').click();return{mobile:isMobile,entries:rows};
}
async function bank(page,t,dir){const widget=page.locator('[data-theory-main] [data-sentence-builder]');assert.equal(await widget.count(),1);const source=practiceData(t),state=await widget.evaluate(n=>{const s=window.Alpine.$data(n);return{ids:s.questions.map(q=>q.id),types:[...new Set(s.questions.map(q=>String(q.type)))]};});assert.ok(state.ids.length>0);assert.equal(new Set(state.ids).size,state.ids.length);assert.deepEqual(state.types,source.linked_practice.question_types.map(String));const inventory=read(path.join(dir,'m42-design-before-v2.json')),owner=inventory.targets.find(r=>r.identity===t.identity);assert.ok(owner);const classes=source.linked_practice.seeder_classes,allowed=classes.flatMap(c=>owner.linked_bank_ids[c]||[]);assert.ok(allowed.length>0);assert.ok(state.ids.every(id=>allowed.includes(id)),'Every sampled question belongs to exactly the preserved accepted own bank');return{classes,allowed:allowed.length,sampled:state.ids.length,types:state.types,exactOwnBank:true};}
async function visualContract(page,t,theme){
 const palette={light:{blue:['#eff6ff','#bfdbfe'],emerald:['#ecfdf5','#a7f3d0'],sky:['#f0f9ff','#bae6fd'],amber:['#fffbeb','#fde68a'],rose:['#fff1f2','#fecdd3'],slate:['#f8fafc','#e2e8f0']},dark:{blue:['#172d4b','#315c8b'],emerald:['#13372f','#28644d'],sky:['#153348','#2d617d'],amber:['#3a2f19','#79622d'],rose:['#3d2330','#7a4058'],slate:['#243044','#46546c']}};
 const rgb=hex=>'rgb('+[1,3,5].map(i=>parseInt(hex.slice(i,i+2),16)).join(', ')+')';
 const result=await page.locator('[data-theory-main]').evaluate(main=>{
  const css=n=>{const c=getComputedStyle(n);return{background:c.backgroundColor,backgroundImage:c.backgroundImage,border:c.borderColor,borderWidth:c.borderTopWidth,font:c.fontFamily,size:c.fontSize,transform:c.textTransform,overflow:Math.max(0,n.scrollWidth-n.clientWidth)};};
  const wrappers=[...main.querySelectorAll('.m42-native-design[data-m42-native-design="v1"]')];
  return{wrappers:wrappers.map(n=>({uuid:n.dataset.m42SourceUuid,kind:n.dataset.m42NativeKind,color:n.dataset.m42Color})),
   points:wrappers.flatMap(n=>n.dataset.m42NativeKind==='practice-set'?[]:[...n.querySelectorAll('.theory-item')].map(p=>({kind:n.dataset.m42NativeKind,color:p.closest('[data-m42-color]')?.dataset.m42Color,...css(p)}))),
   english:wrappers.flatMap(n=>[...n.querySelectorAll('em[data-m42-em-language="en"],.m42-english')].map(e=>({text:e.textContent,...css(e)}))),
   ukrainianTemplates:wrappers.flatMap(n=>[...n.querySelectorAll('em[data-m42-em-language="uk-template"]')].map(e=>({text:e.textContent,...css(e)}))),
   ukrainianTranslations:wrappers.flatMap(n=>[...n.querySelectorAll('.m42-paired-translation[lang="uk"],.theory-translation[lang="uk"],.m42-form-context')].map(e=>({text:e.textContent,paired:e.classList.contains('m42-paired-translation'),...css(e)}))),
   controls:wrappers.filter(n=>n.dataset.m42NativeKind==='practice-set').flatMap(n=>[...n.querySelectorAll('input,textarea,button')].filter(e=>!e.closest('[data-sentence-builder]')).map(e=>({tag:e.tagName,text:e.textContent?.trim()||null,selection:(e.getAttribute('@click')||'').includes('Answers')||(e.getAttribute('@click')||'').includes('appendInputToken'),...css(e)})))};
 });
 const nativeTypes=['forms-grid','lesson-rule-cards','usage-panels','comparison-table','mistakes-grid','summary-list','practice-set','tense-forms-table'];
 const expected=t.registry.blocks.filter(b=>nativeTypes.includes(b.type)).map(b=>b.uuid);assert.deepEqual(result.wrappers.map(w=>w.uuid),expected,'Exact existing native-view whitelist/source block hooks; unchanged hero/navigation never painted');
 for(const p of result.points){assert.equal(p.borderWidth,'1px','Native inner card frame is visible');if(p.kind==='forms-grid')assert.match(p.backgroundImage,/linear-gradient/u,'Native forms tile keeps its neutral gradient rather than a flat usage color');else{assert.ok(palette[theme][p.color],'Explicit semantic palette decision');assert.equal(p.background,rgb(palette[theme][p.color][0]));assert.equal(p.border,rgb(palette[theme][p.color][1]));}}
 for(const e of result.english)assert.match(e.font,/monospace/u,'Declared native English construction typography');for(const e of result.ukrainianTemplates)assert.doesNotMatch(e.font,/monospace/u,'Ukrainian template is not globally monospace');for(const e of result.ukrainianTranslations){assert.doesNotMatch(e.font,/monospace/u,'Exact Ukrainian translation/context is not formatted as English code');if(e.paired)assert.equal(e.size,'12px','Native paired translation typography');}
 const expectedPairs=t.slug==='present-perfect-vs-present-perfect-continuous'?10:t.slug==='narrative-tenses'?6:0;assert.equal(result.ukrainianTranslations.filter(e=>e.paired).length,expectedPairs,'All16 finite mixed fields have exactly one normal Ukrainian partner, never a hidden duplicate');
 for(const c of result.controls){if(c.selection||['INPUT','TEXTAREA'].includes(c.tag))assert.equal(c.transform,'none','Natural answer casing');assert.equal(c.overflow,0,'Long own answer/token/control is not clipped');}
 return result;
}
async function pageStates(dir,label,{beforeLabel=null,baseline=false,smoke=false,start=0,end=42}={}){
 const {chromium}=require('playwright');let browser=null;
 const original=beforeLabel?read(path.join(dir,beforeLabel+'-semantic.json')):null,report={at:new Date().toISOString(),base:BASE,smokeOnly:smoke,conditions:{singleActiveChrome:true,freshChromePerState:true,explicitGC:typeof global.gc==='function'},sourceHashes:sourceHashes(),runtimeHashes:baseline?null:runtimeHashes(),scope:targets.map(t=>({identity:t.identity,path:t.path})),rows:[],violations:[],pass:false};
 try{for(const t of targets.slice(start,end))for(const viewport of baseline||smoke?[{width:1440,height:1000}]:[{width:1440,height:1000},{width:390,height:844}])for(const theme of baseline||smoke?['light']:['light','dark']){
  let context=null;const row={identity:t.identity,path:t.path,group:t.group,viewport,theme,errors:[],httpErrors:[],failed:[],screenshots:[],sections:[]};report.rows.push(row);
  try{browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE,args:['--disable-background-networking']});context=await browser.newContext({viewport,colorScheme:theme,deviceScaleFactor:1});const page=await context.newPage();await context.addInitScript(theme=>localStorage.setItem('theme',theme),theme);const response=await ready(page,t,row,report.violations,theme);const dom=new JSDOM(await response.text());try{row.semantic=semantic(dom.window.document);if(original){const previous=original.targets.find(v=>v.identity===t.identity);assert.ok(previous);equality(previous.semantic,row.semantic);row.fullContentEquality=true;}}finally{dom.window.close();}
   const stem=label+'-'+t.slug+'-'+viewport.width+'-'+theme;
   row.top=stem+'-top.png';await page.screenshot({path:path.join(dir,row.top),animations:'disabled'});row.screenshots.push(row.top);
   const sections=page.locator('[data-theory-main] section.theory-native-block').filter({has:page.locator('.theory-section-header')});
   for(const[i,section]of(await sections.all()).entries()){const screenshot=await shot(section,dir,stem+'-section-'+(i+1)+'.png');row.screenshots.push(screenshot);row.sections.push(await section.evaluate(n=>{const css=e=>{if(!e)return null;const c=getComputedStyle(e);return{background:c.backgroundColor,border:c.borderColor,borderWidth:c.borderWidth,radius:c.borderRadius,font:c.fontFamily,size:c.fontSize,padding:c.padding,color:c.color};};return{id:n.id,heading:n.querySelector('.theory-section-header')?.textContent?.trim(),card:css(n.querySelector('.theory-section-card')),point:css(n.querySelector('.theory-item')),example:css(n.querySelector('.theory-example')),translation:css(n.querySelector('.theory-translation')),table:css(n.querySelector('table'))};}));}
   row.overflow=await old.overflow(page);if(!baseline)row.visual=await visualContract(page,t,theme);
   if(!baseline&&!smoke){row.details=await details(page,t,dir,stem,viewport.width===1440&&theme==='light',viewport.width===1440&&theme==='light');await page.evaluate(theme=>document.documentElement.classList.toggle('dark',theme==='dark'),theme);row.tables=await tables(page);row.bank=await bank(page,t,dir);row.practice=await practice(page,t,dir,stem,viewport.width===1440&&theme==='light');}
   old.clean(row);if(!baseline)assert.deepEqual(runtimeHashes(),report.runtimeHashes,'Actual served presentation/practice source freeze');row.pass=true;
  }catch(e){row.failure=String(e.stack||e);throw e;}finally{if(context)await context.close();if(browser)await browser.close();browser=null;if(global.gc)global.gc();}console.log(JSON.stringify({path:t.path,width:viewport.width,theme,phase:baseline?'before':'after',pass:row.pass}));
  if(!baseline&&viewport.width===390&&theme==='dark')save(dir,label+'-'+t.slug+'-complete-state.json',{at:new Date().toISOString(),base:BASE,sourceHashes:report.sourceHashes,runtimeHashes:report.runtimeHashes,conditions:report.conditions,rows:report.rows.filter(r=>r.identity===t.identity),pass:true});
 }assert.deepEqual(report.violations,[]);assert.deepEqual(sourceHashes(),report.sourceHashes);if(!baseline)assert.deepEqual(runtimeHashes(),report.runtimeHashes);report.pass=true;}finally{if(browser)await browser.close();save(dir,label+'-pages.json',report);}return report;
}
const controls=[...m41.targets.map(t=>({...t,group:'M41'})),
 {slug:'native-ppc',path:'/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms'},
 {slug:'native-sentence-types',path:'/theory/basic-grammar/sentence-types'},
 {slug:'category-sentence-structure',path:'/theory/sentence-structure'},
 {slug:'course-grammar',path:'/courses/english-grammar-theory'},
 {slug:'test-present-perfect',path:'/test/present-perfect/forms'}];
async function controlStates(dir,label,beforeLabel,start=0,end=controls.length){
 const {chromium}=require('playwright');let browser=null;
 const before=beforeLabel?read(path.join(dir,beforeLabel+'-controls.json')):null,report={at:new Date().toISOString(),base:BASE,runtimeHashes:beforeLabel?runtimeHashes():null,rows:[],violations:[],pass:false};
 try{for(const t of controls.slice(start,end))for(const viewport of[{width:1440,height:1000},{width:390,height:844}])for(const theme of['light','dark']){
  browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});const context=await browser.newContext({viewport,colorScheme:theme,deviceScaleFactor:1}),page=await context.newPage(),row={path:t.path,viewport,theme,group:t.group||'non-target',errors:[],httpErrors:[],failed:[],screenshots:[]};report.rows.push(row);await context.addInitScript(theme=>localStorage.setItem('theme',theme),theme);
  try{const response=await ready(page,t,row,report.violations,theme);const dom=new JSDOM(await response.text());try{const doc=dom.window.document;row.meta=metadata(doc);row.mainText=cloneText(doc.querySelector('[data-theory-main]')||doc.querySelector('main')||doc.body,{practice:t.group!=='M41'});if(t.group==='M41')row.semantic=semantic(doc);}finally{dom.window.close();}
   row.styles=await page.evaluate(()=>{const css=n=>{if(!n)return null;const c=getComputedStyle(n);return{background:c.backgroundColor,border:c.borderColor,borderWidth:c.borderWidth,radius:c.borderRadius,font:c.fontFamily,size:c.fontSize,padding:c.padding,color:c.color};};const main=document.querySelector('[data-theory-main]')||document.querySelector('main');return{header:css(document.querySelector('header')),sidebar:css(document.querySelector('[data-theory-aside]')),main:css(main),section:css(main?.querySelector('.theory-section-card')),point:css(main?.querySelector('.theory-item')),example:css(main?.querySelector('.theory-example')),translation:css(main?.querySelector('.theory-translation'))};});
   const stem=label+'-'+t.slug+'-'+viewport.width+'-'+theme;row.top=stem+'-top.png';await page.screenshot({path:path.join(dir,row.top),animations:'disabled'});row.screenshots.push(row.top);
   if(before){const original=before.rows.find(v=>v.path===t.path&&v.viewport.width===viewport.width&&v.theme===theme);assert.ok(original);assert.deepEqual(row.meta,original.meta);assert.equal(row.mainText,original.mainText);assert.deepEqual(row.styles,original.styles,'Non-target/reference native layout and colors unchanged');assert.equal(await page.locator('.m42-native-design').count(),0,'Finite M42 layer never paints a reference/control page');if(t.group==='M41'){equality(original.semantic,row.semantic);const liveDom=new JSDOM(await page.content());try{row.author=require('./seo-m41-design-local.cjs').authorFidelity(liveDom.window.document,t);}finally{liveDom.window.close();}row.details=await details(page,t,dir,stem,viewport.width===1440&&theme==='light',viewport.width===1440&&theme==='light');row.practice=await practice(page,t,dir,stem,viewport.width===1440&&theme==='light');}row.unchanged=true;}
   if(t.slug==='test-present-perfect'){
    const state=BASE+t.path+'/state';row.expectedBlockedAutosave=report.violations.filter(v=>v.method==='POST'&&v.url===state);report.violations=report.violations.filter(v=>!row.expectedBlockedAutosave.includes(v));
    row.expectedBlockedFetch=row.failed.filter(v=>v.url===state);row.failed=row.failed.filter(v=>v.url!==state);row.expectedBlockedConsole=row.consoleErrors.filter(v=>v.source===state);row.consoleErrors=row.consoleErrors.filter(v=>v.source!==state);
    row.readOnlyLimitation='Automatic test state POST was blocked before reaching the server; no progress write or injected replacement response. GET-rendered layout/questions are inspected, state persistence is deliberately not tested.';
   }
   old.clean(row);if(before)assert.deepEqual(runtimeHashes(),report.runtimeHashes);row.pass=true;
  }catch(e){row.failure=String(e.stack||e);throw e;}finally{await context.close();await browser.close();browser=null;if(global.gc)global.gc();}console.log(JSON.stringify({phase:before?'reference-after':'reference-before',path:t.path,width:viewport.width,theme,pass:row.pass}));
 }assert.deepEqual(report.violations,[]);if(before)assert.deepEqual(runtimeHashes(),report.runtimeHashes);report.pass=true;}finally{if(browser)await browser.close();save(dir,label+'-controls.json',report);}return report;
}
async function supplemental(dir,label,beforeLabel,start=0,end=42){
 const {chromium}=require('playwright');let browser=null;const original=read(path.join(dir,beforeLabel+'-semantic.json'));
 const report={at:new Date().toISOString(),base:BASE,scope:{total:42,start,end},runtimeHashes:runtimeHashes(),noJS:[],narrow:[],zoomEquivalent:[],cssZoomStressWarning:{attempt:'browser-supplemental-v1-supplemental.json',probe:'browser-zoom-probe-v1-zoom-probe.json',classification:'CSS body zoom2 keeps desktop media queries and exposes inherited hero/layout overflow also on M41/non-target controls. Retained as failed stress, not converted to PASS; no header/sidebar/global layout patch.'},violations:[],pass:false};
 try{for(const t of targets.slice(start,end)){
  browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});const context=await browser.newContext({javaScriptEnabled:false,viewport:{width:390,height:844}}),page=await context.newPage(),row={path:t.path,javaScriptEnabled:false,errors:[],httpErrors:[],failed:[],expectedDisabledScripts:[]};report.noJS.push(row);
  try{await observe(page,row,report.violations);const response=await page.goto(BASE+t.path,{waitUntil:'domcontentloaded'});assert.equal(response.status(),200);const dom=new JSDOM(await response.text());try{equality(original.targets.find(v=>v.identity===t.identity).semantic,semantic(dom.window.document));row.completeSourceContent=true;}finally{dom.window.close();}
   row.details=[];for(const d of await page.locator('[data-theory-native-extension] > details').all()){const summary=d.locator(':scope > summary');await summary.click();assert.equal(await d.evaluate(n=>n.open),true);await summary.focus();await summary.press('Space');assert.equal(await d.evaluate(n=>n.open),false);await summary.press('Enter');assert.equal(await d.evaluate(n=>n.open),true);await summary.click();row.details.push({mouseSpaceEnter:true});}
   const helper=practiceHelper(t);if(helper){row.keys=[];for(const task of t.data.cases){const card=await helper.caseLocator(page,task),key=card.locator('[data-'+t.group.toLowerCase()+'-self-check-answer]'),details=card.locator('[data-'+t.group.toLowerCase()+'-ui-explanation]');assert.equal(htmlText(await key.innerHTML()),htmlText(t.data.author_self_check.answers[task.source_index-1]));const summary=details.locator(':scope > summary');await summary.focus();await summary.press('Enter');assert.equal(await key.isVisible(),true);row.keys.push({source:task.source_index,exact:true,accessible:true,keyboardEnter:true});}}else{const data=practiceData(t);if(data.author_self_check){const prefix=t.group==='M14'?'m30':t.group.toLowerCase(),key=page.locator('[data-'+prefix+'-self-check-answers]');assert.equal(await key.count(),1);assert.equal(await key.isVisible(),true);assert.deepEqual((await key.locator('li').evaluateAll(ns=>ns.map(n=>n.innerHTML))).map(htmlText),data.author_self_check.answers.map(htmlText));row.staticKeys=data.author_self_check.answers.length;}}
   old.clean(row);row.pass=true;
  }catch(e){row.failure=String(e.stack||e);throw e;}finally{await context.close();await browser.close();browser=null;if(global.gc)global.gc();}
  browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});const narrowContext=await browser.newContext({viewport:{width:320,height:844},deviceScaleFactor:1}),narrowPage=await narrowContext.newPage(),narrow={path:t.path,viewport:{width:320,height:844},errors:[],httpErrors:[],failed:[],screenshots:[]};report.narrow.push(narrow);
  try{await ready(narrowPage,t,narrow,report.violations,'light');narrow.overflow=await old.overflow(narrowPage);narrow.toc=await toc(narrowPage);narrow.tables=await tables(narrowPage,true);for(const[i,section]of(await narrowPage.locator('[data-theory-main] section.theory-native-block').all()).entries())narrow.screenshots.push(await shot(section,dir,label+'-'+t.slug+'-320-section-'+(i+1)+'.png'));
   old.clean(narrow);narrow.pass=true;
  }catch(e){narrow.failure=String(e.stack||e);throw e;}finally{await narrowContext.close();await browser.close();browser=null;if(global.gc)global.gc();}
  browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_EXECUTABLE});const zoomContext=await browser.newContext({viewport:{width:720,height:500},deviceScaleFactor:2}),zoomPage=await zoomContext.newPage(),zoom={path:t.path,viewport:{width:720,height:500},deviceScaleFactor:2,physicalEquivalent:{width:1440,height:1000},method:'200% desktop browser-zoom equivalent: reduced CSS viewport720x500 with DPR2, physical1440x1000. Not claimed as OS/browser UI zoom; no DOM/CSS-zoom/HTML substitution.',errors:[],httpErrors:[],failed:[],screenshots:[]};report.zoomEquivalent.push(zoom);
  try{const response=await ready(zoomPage,t,zoom,report.violations,'light');const dom=new JSDOM(await response.text());try{equality(original.targets.find(v=>v.identity===t.identity).semantic,semantic(dom.window.document));zoom.completeSourceContent=true;}finally{dom.window.close();}zoom.overflow=await old.overflow(zoomPage);zoom.tables=await tables(zoomPage,true);for(const[i,section]of(await zoomPage.locator('[data-theory-main] section.theory-native-block').all()).entries())zoom.screenshots.push(await shot(section,dir,label+'-'+t.slug+'-zoom-equivalent200-section-'+(i+1)+'.png'));old.clean(zoom);zoom.pass=true;
  }catch(e){zoom.failure=String(e.stack||e);throw e;}finally{await zoomContext.close();await browser.close();browser=null;if(global.gc)global.gc();}assert.deepEqual(runtimeHashes(),report.runtimeHashes);console.log(JSON.stringify({phase:'supplemental',path:t.path,pass:true}));
 }assert.equal(report.noJS.length,end-start);assert.equal(report.narrow.length,end-start);assert.equal(report.zoomEquivalent.length,end-start);assert.deepEqual(report.violations,[]);assert.deepEqual(runtimeHashes(),report.runtimeHashes);report.pass=true;}finally{if(browser)await browser.close();save(dir,label+'-supplemental.json',report);}return report;
}
if(require.main===module){const[mode,dir,label,beforeLabel,start,end]=process.argv.slice(2);assert.equal(path.basename(dir),'seo-m42-local');assert.match(label,/^[a-z0-9-]+$/u);Promise.resolve(mode==='--http-before'?captureHTTP(dir,label):mode==='--controls'?controlStates(dir,label,beforeLabel==='none'?null:beforeLabel,start?Number(start):0,end?Number(end):controls.length):mode==='--supplemental'?supplemental(dir,label,beforeLabel,start?Number(start):0,end?Number(end):42):pageStates(dir,label,{baseline:mode==='--before',smoke:mode==='--smoke',beforeLabel:mode==='--before'?null:beforeLabel,start:start?Number(start):0,end:end?Number(end):42})).catch(e=>{console.error(e.stack);process.exitCode=1;});}
module.exports={targets,semantic,equality,cloneText,sourceHashes,runtimeHashes,captureHTTP,pageStates,practice,practiceHelper,nativePractice,details,tables,toc,controlStates,supplemental,visualContract};
