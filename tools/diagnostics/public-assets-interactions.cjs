const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const {prepare,loaded,root,base}=require('./public-assets-browser.cjs');
const phase=process.argv[2]||'baseline';
const rows=[];
async function settleSave(page){await page.waitForTimeout(400);await page.evaluate(async()=>{await JS_TEST_SAVE_QUEUE});}
(async()=>{
 const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_EXECUTABLE?{executablePath:process.env.CHROMIUM_EXECUTABLE}:{})});
 try{
  for(const mobile of [false,true]){
   const r={mobile,blocked:[],console:[],responses:[],failures:[],checks:[]};
   const {context,page}=await prepare(browser,{mobile},r);
   const check=(name,pass)=>{r.checks.push({name,pass:!!pass});console.log(JSON.stringify({mobile,name,pass:!!pass}));};
   try{
    await page.goto(base+'/theory/basic-grammar/sentence-types',{waitUntil:'load'});await loaded(page);
    check('one-Alpine-init',await page.evaluate(()=>window.__m3.alpineInits===1));
    check('main-content',await page.locator('main h1').count()===1);
    if(mobile){await page.getByRole('button',{name:'Меню',exact:true}).click();check('mobile-menu-open',await page.locator('.site-header input:visible').count()>0);}
    await page.locator('.site-header button[\\@click="toggleTheme"]:visible').first().click();
    await page.waitForTimeout(200);check('theme-toggle',await page.evaluate(()=>document.documentElement.classList.contains('dark')));
    await page.reload({waitUntil:'load'});await loaded(page);check('theme-restored',await page.evaluate(()=>document.documentElement.classList.contains('dark')));
    for(const value of ['cards','custom','blue']){
     // Settings widget is admin-only. Exercise its existing controller, not auth.
     await page.evaluate(value=>Alpine.$data(document.documentElement).setBackgroundMode(value),value);
     await page.waitForTimeout(150);await page.reload({waitUntil:'load'});await loaded(page);
     check('background-'+value,await page.evaluate(v=>document.documentElement.dataset.backgroundMode===v,value));
    }
    check('custom-colors-restored',await page.evaluate(()=>getComputedStyle(document.documentElement).getPropertyValue('--app-custom-ct').trim()==='#3050a0'));
    if(!mobile){
     await page.waitForFunction(()=>document.querySelector('[data-theory-desktop-navigation-loader]')?.getAttribute('aria-busy')==='false');
     check('ajax-navigation-loaded',await page.locator('[data-theory-desktop-navigation-loader] [x-ref="content"] a').count()>10);
     await page.locator('[data-theory-sidebar] button').first().click();await page.waitForTimeout(350);
     check('sidebar-collapse',await page.locator('[data-theory-layout]').getAttribute('data-collapsed')==='true');
     await page.reload({waitUntil:'load'});await loaded(page);check('sidebar-restored',await page.locator('[data-theory-layout]').getAttribute('data-collapsed')==='true');
     await page.locator('[data-theory-sidebar] button').first().click();
    }else await page.getByRole('button',{name:'Меню',exact:true}).click();
    const search=page.locator('.site-header input:visible').first();
    await search.fill('Perfect');await page.waitForTimeout(900);
    check('search-results',await page.evaluate(()=>[...document.querySelectorAll('[x-data="searchBox()"]')].some(el=>Alpine.$data(el).results.length>0)));
    await search.fill('');
    if(mobile)await page.getByRole('button',{name:'Меню',exact:true}).click();
    const collapse=page.locator('main [x-collapse]').first();
    await collapse.locator('..').locator(':scope > button').click();await page.waitForTimeout(400);
    check('collapse-visible',await collapse.isVisible());
    check('collapse-plugin',await collapse.evaluate(el=>!!el._x_transition));
    await page.goto(base+'/test/future-perfect/questions',{waitUntil:'load'});await loaded(page);
    await page.waitForFunction(()=>typeof state!=='undefined'&&state.items.length===84);
    const index=await page.evaluate(()=>state.items.findIndex((q,i)=>q.answers?.length===1&&document.querySelector(`article[data-idx="${i}"] button[data-options-toggle]`)));
    const card=page.locator(`article[data-idx="${index}"]`);
    await card.locator('button[data-options-toggle]').click();
    const choice=await page.evaluate(i=>{const q=state.items[i],right=q.answers[0],wrong=q.optionsBySlot[0].find(o=>o!==right);q.explanationsCache[buildExplanationKey(wrong,right)]='Локальний кеш пояснення для перевірки стилів.';return {right,wrong}},index);
    await card.locator('button[data-opt='+JSON.stringify(choice.wrong)+']').click();
    check('incorrect-answer-state',await page.evaluate(i=>state.items[i].wrongAttempt&&state.items[i].feedback!=='correct',index));
    r.wrongStyle=await card.locator('#feedback-'+index).evaluate(el=>({textColor:getComputedStyle(el).color,classes:el.innerHTML.match(/class="[^"]+"/g)}));
    const rightButton=card.locator('button[data-opt='+JSON.stringify(choice.right)+']');
    if(!await rightButton.isVisible())await card.locator('button[data-options-toggle]').click();
    await rightButton.click();
    check('correct-answer-state',await page.evaluate(i=>state.items[i].done&&state.items[i].feedback==='correct',index));
    const reorder=page.locator('[data-sentence-reorder]').first();
    const token=reorder.locator('[data-reorder-action="add"]:not([disabled])').first();
    const tokenIndex=await token.getAttribute('data-reorder-token-index');await token.click();
    check('reorder-token-selected',await reorder.locator(`[data-reorder-token-index="${tokenIndex}"]`).isDisabled());
    await reorder.locator('[data-reorder-action="remove"]').first().click();
    check('reorder-token-available-again',await reorder.locator(`[data-reorder-token-index="${tokenIndex}"]`).isEnabled());
    const manual=page.locator('[data-polyglot-translation-preview] input[data-manual-gap]:not([disabled])').first();
    await manual.fill('will');check('builder-manual-input',await manual.inputValue()==='will');await settleSave(page);
    await page.screenshot({path:path.join(root,`${phase}-interaction-${mobile?'mobile':'desktop'}.png`)});
    await page.goto(base+'/courses/english-grammar-theory/lesson/basic-grammar/sentence-types',{waitUntil:'load'});await loaded(page);
    r.course=await page.evaluate(()=>{
     const m=window.__THEORY_COURSE_MANIFEST__,s=window.TheoryCourseProgress.createStore(m.course.slug,m.lessons),i=m.lessons.findIndex(l=>l.lesson_slug===m.lesson.lesson_slug),prior=m.lessons.slice(0,i).map(l=>l.lesson_slug);
     s.write({...s.read(),completedLessons:prior,unlockedLessons:[...prior,m.lesson.lesson_slug],currentLessonSlug:m.lesson.lesson_slug});s.markLessonOpened(m.lesson.lesson_slug);
     return {lesson:m.lesson.lesson_slug,last:s.read().lastOpenedLessonSlug};
    });
    await page.reload({waitUntil:'load'});await loaded(page);
    check('course-content-open',await page.locator('[data-theory-lesson-content]').isVisible());
    check('course-progress-restored',await page.evaluate(()=>{const m=window.__THEORY_COURSE_MANIFEST__,s=window.TheoryCourseProgress.createStore(m.course.slug,m.lessons);return s.read().lastOpenedLessonSlug===m.lesson.lesson_slug}));
    check('no-production-or-old-CDN-attempt',r.blocked.length===0);
   }catch(error){r.error=error.message;console.log(JSON.stringify({mobile,error:r.error}));}
   finally{await context.close();rows.push(r);fs.writeFileSync(path.join(root,`${phase}-interactions.json`),JSON.stringify(rows,null,2));}
  }
 }finally{await browser.close();}
 if(rows.some(r=>r.error||r.checks.some(c=>!c.pass)))process.exitCode=1;
})().catch(e=>{console.error(e.message);process.exitCode=1});
