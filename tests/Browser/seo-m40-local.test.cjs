'use strict';
// Pure source/DOM diagnostic contracts, never HTTP/Laravel/working DB.
const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const {JSDOM}=require('jsdom');
const d=require('../../tools/diagnostics/seo-m40-local.cjs');
const capture=require('../../tools/diagnostics/capture-m40-http.cjs');
const ROOT=path.resolve(__dirname,'../..');

test('Exact local-only M40 HTTP inventory includes all M39 owners and never requests production',()=>{
    assert.equal(capture.BASE,'http://gramlyze.loc');assert.equal(capture.routes.length,52);
    assert.equal(new Set(capture.routes).size,52);assert.equal(capture.controls.length,45);
    assert.deepEqual(capture.targetPaths,d.targets.map(target=>target.path));
    assert.deepEqual(capture.mixedPaths,[
        '/test/tenses/present-perfect-vs-present-perfect-continuous',
        '/test/tenses/narrative-tenses','/test/mixed-revision/b1-mixed-revision']);
    for(const route of capture.routes)capture.assertRoute(route);
    for(const invalid of ['https://gramlyze.com/theory','//gramlyze.ub/theory','gramlyze.loc'])assert.throws(()=>capture.assertRoute(invalid));
});
test('M24 native shape8/6/4 remains intact with only last box projected to three blocks',()=>{
    assert.deepEqual(d.targets.map(target=>target.native_count),[8,6,4]);
    assert.deepEqual(d.targets.map(target=>target.after.page.blocks.length),[11,9,7]);
    const before=JSON.parse(fs.readFileSync(path.join(ROOT,'database/content-patches/m40-m24-tenses-b1-before.json'),'utf8'));
    for(const [i,target]of d.targets.entries()){
        assert.deepEqual(target.after.page.blocks.slice(0,target.native_count),before.targets[i].before.page.blocks.slice(0,target.native_count));
        assert.equal(target.plans.length,3);assert.equal(target.data.author_self_check.prompts.length,6);assert.equal(target.data.author_self_check.answers.length,6);
        assert.equal(target.data.title,target.data.author_self_check.section_title);
        assert.deepEqual(target.data.cases.map(item=>item.source_index),[1,2,3,4,5,6]);
        assert.equal(target.plans.flatMap(plan=>plan.points||[]).filter(point=>point.detail).length,0);
    }
});
function fixture(target){
    const i=d.targets.findIndex(item=>item.identity===target.identity),lesson=d.master.lessons[i];
    const fragments=lesson.existing_blocks.flatMap(block=>d.learnerStrings(block.replacement_body_json));
    const headings=lesson.existing_blocks.filter(block=>block.replacement_body_json.title&&block.preserve_type!=='navigation-chips').map(block=>'<div class="theory-section-header"><h2>'+block.replacement_body_json.title+'</h2></div>');
    const footers=lesson.existing_blocks.filter(block=>block.preserve_type==='navigation-chips').map(block=>'<p>'+block.replacement_body_json.title+'</p>');
    const links=lesson.existing_blocks.filter(block=>block.preserve_type==='navigation-chips').flatMap(block=>block.replacement_body_json.items.map(item=>item.current?'<nav><span>'+item.label+'</span></nav>':'<a href="'+item.url+'">'+item.label+'</a>'));
    const appended=target.plans.map((plan,j)=>{const data=JSON.parse(target.after.page.blocks[target.native_count+j].body);return '<div class="theory-section-header"><h2>'+data.title+'</h2></div>'+(data.intro||'')+(plan.points||[]).map(point=>point.basic).join('');});
    return new JSDOM('<h1>'+lesson.preserve_subtitle_strong+'</h1><main data-theory-main>'+fragments.join(' ') +headings.join('')+links.join('')+appended.join('')+footers.join('')+'</main>');
}
test('Native source visibility rejects missing whole content, changed H1 and invented details',()=>{
    for(const target of d.targets){
        const dom=fixture(target);assert.equal(d.assertNative(dom.window.document,target).sourceVisible,true);
        dom.window.document.querySelector('h1').textContent='Narrative Tenses';
        if(target.slug==='narrative-tenses')assert.throws(()=>d.assertNative(dom.window.document,target));
        dom.window.close();
        const outline=fixture(target);outline.window.document.querySelector('main').textContent='A short outline';
        assert.throws(()=>d.assertNative(outline.window.document,target));outline.window.close();
        const detail=fixture(target);detail.window.document.querySelector('main').insertAdjacentHTML('beforeend','<div data-theory-native-extension><details></details></div>');
        assert.throws(()=>d.assertNative(detail.window.document,target));detail.window.close();
    }
});
test('Course key count handles native details and wrapper-around-OL without bypassing a course gate',()=>{
    assert.equal(capture.coursePath,'/courses/english-grammar-theory/lesson/tenses/present-perfect-vs-present-perfect-continuous');
    const source=fs.readFileSync(path.join(ROOT,'tools/diagnostics/capture-m40-http.cjs'),'utf8');
    const selector=source.match(/sourceKeys: \[\.\.\.doc\.querySelectorAll\('([^']+)'\)\]/)[1];
    const keys=d.targets[0].data.author_self_check.answers;
    const dom=new JSDOM('<div data-m40-self-check-answers><ol>'+keys.map(key=>'<li>'+key+'</li>').join('')+'</ol></div>');
    assert.equal(dom.window.document.querySelectorAll(selector).length,6);dom.window.close();
    assert.match(source,/Server properties only; no browser gate bypass or visual acceptance claim/);
    assert.doesNotMatch(source,/page\.evaluate|page\.setContent|route\.fulfill|storageState|Authorization/);
});
test('Real acceptance is phase-gated, exclusive, bounded and checks all M39 cases rather than samples',()=>{
    const source=fs.readFileSync(path.join(ROOT,'tools/diagnostics/seo-m40-local.cjs'),'utf8');
    assert.match(source,/assert\.equal\(before\.caseStates, 36\)/);assert.match(source,/before\.sourceHashes, hashes/);
    assert.match(source,/fullPage:false/);assert.doesNotMatch(source,/fullPage:\s*true|page\.setContent|route\.fulfill|retry\s*[:=(]/);
    assert.match(source,/for\(const target of previous\.targets\)/);assert.match(source,/previous\.acceptanceCase/);
    assert.match(source,/report\.m39\.flatMap\(row=>row\.cases\)\.length,18/);
    assert.match(source,/javaScriptEnabled:false/);assert.match(source,/expectedDisabledScripts:\[\]/);
    assert.match(source,/report\.rows\.length,12/);assert.match(source,/report\.noJS\.length,6/);
    for(const name of ['assertNative','pageStates','supplemental','verifyCase'])assert.equal(typeof d[name],'function');
    const hashes=d.sourceHashes();assert.equal(Object.keys(hashes).length,5);
    for(const hash of Object.values(hashes))assert.match(hash,/^[0-9a-f]{64}$/);
});
test('Mobile table QA proves actual native focus and ArrowRight delta; End/programmatic scrolling cannot masquerade as keyboard evidence',()=>{
    const source=fs.readFileSync(path.join(ROOT,'tools/diagnostics/seo-m40-local.cjs'),'utf8');
    assert.match(source,/actual:document\.activeElement===node/);assert.match(source,/row\.afterFirstArrow>row\.before\.scrollLeft/);
    assert.match(source,/page\.keyboard\.press\('ArrowRight'\)/);assert.match(source,/row\.screenshot=await shot\(table/);
    assert.doesNotMatch(source,/press\('End'\)|node\.scrollLeft\s*=/);
    assert.equal(typeof d.mobileTableQA,'function');
});
