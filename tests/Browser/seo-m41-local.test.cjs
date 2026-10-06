'use strict';
// Independent source/DOM contracts only. These fixtures are not .loc acceptance.
const {test}=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path');
const {JSDOM}=require('jsdom');
const d=require('../../tools/diagnostics/seo-m41-local.cjs'),capture=require('../../tools/diagnostics/capture-m41-http.cjs');
const ROOT=path.resolve(__dirname,'../..'),escape=text=>String(text).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');
test('Exact 55 guest .loc routes include47protected M26–M40 owners plusPPC; production rejected',()=>{
    assert.equal(capture.BASE,'http://gramlyze.loc');assert.equal(capture.routes.length,55);assert.equal(new Set(capture.routes).size,55);assert.equal(capture.controls.length,48);
    assert.deepEqual(capture.targetPaths,d.targets.map(target=>target.path));
    assert.deepEqual(capture.mixedPaths,d.master.lessons.map(lesson=>lesson.test_path));
    for(const route of capture.routes)capture.assertRoute(route);
    for(const bad of ['https://gramlyze.com/theory','//gramlyze.ub/theory','gramlyze.loc'])assert.throws(()=>capture.assertRoute(bad));
});
test('Frozen v1.0.1 master is authoritative;35fullbasicpoints14details and18source tasks remain exact',()=>{
    const crypto=require('node:crypto'),bytes=fs.readFileSync(path.join(ROOT,'docs/content/m41-authored-tense-comparisons.v1.0.1.json'));
    assert.equal(crypto.createHash('sha256').update(bytes).digest('hex'),'9020cf977e903c76f93c7165bbe402567b5fbf40a98a8139dc6751b223b34553');
    assert.deepEqual(d.master.lessons.map(lesson=>lesson.sections.flatMap(section=>section.points).length),[12,13,10]);
    assert.deepEqual(d.master.lessons.map(lesson=>lesson.sections.flatMap(section=>section.points).filter(point=>point.detail).length),[4,5,5]);
    assert.deepEqual(d.targets.map(target=>target.after.page.blocks.length),[9,9,9]);
    for(const [i,target]of d.targets.entries())assert.deepEqual(target.data.author_practice,d.master.lessons[i].practice);
    assert.equal(d.master.lessons[2].sections[2].points[1].paragraphs_uk[1],
        'У прикладі нижче форму надіслали лише один раз: спочатку повідомляємо про результат, а потім уточнюємо час надсилання.');
});
function paragraphs(lines){return lines.map(line=>'<p>'+escape(line)+'</p>').join('');}
function examples(rows){return rows.map(row=>'<div class="theory-example"><p lang="en">'+escape(row.en)+'</p><p lang="uk">'+escape(row.uk)+'</p></div>').join('');}
function fixture(target){
    const lesson=d.masterLesson(target);
    const sections=lesson.sections.map(section=>'<section class="theory-native-block"><div class="theory-section-header"><h2>'+escape(section.title)+'</h2></div>'
        +(section.table?'<div class="theory-table-scroll"><table><thead><tr>'+section.table.columns.map(column=>'<th>'+escape(column)+'</th>').join('')+'</tr></thead><tbody>'
            +section.table.rows.map(row=>'<tr>'+row.map(cell=>'<td>'+escape(cell).replaceAll('\n','<br>')+'</td>').join('')+'</tr>').join('')+'</tbody></table></div>':'')
        +section.points.map(point=>'<article class="theory-item"><h3>'+escape(point.title)+'</h3>'+paragraphs(point.paragraphs_uk)+examples(point.examples)
            +(point.detail?'<div data-theory-native-extension><details><summary>Докладніше</summary><div class="theory-point-fragment"><h4>'+escape(point.detail.title)+'</h4>'
                +paragraphs(point.detail.paragraphs_uk)+examples(point.detail.examples)+'</div></details></div>':'')+'</article>').join('')+'</section>').join('');
    return new JSDOM('<h1>'+escape(lesson.title)+'</h1><main data-theory-main><p>'+escape(lesson.subtitle)+'</p>'+lesson.hero.map(rule=>Object.values(rule).map(escape).join(' ')).join(' ')+sections+'</main>');
}
test('Every master basic/detail/table text is checked in its own point; outline/wrongowner detail rejected',()=>{
    for(const target of d.targets){
        const dom=fixture(target);assert.equal(d.assertNative(dom.window.document,target).sourceVisible,true);
        const detail=dom.window.document.querySelector('.theory-point-fragment');detail.textContent='An unrelated neighbouring point';assert.throws(()=>d.assertNative(dom.window.document,target));dom.window.close();
        const outline=fixture(target);outline.window.document.querySelector('article').textContent='A short outline';assert.throws(()=>d.assertNative(outline.window.document,target));outline.window.close();
        const table=fixture(target);table.window.document.querySelector('tbody td').textContent='Altered source cell';assert.throws(()=>d.assertNative(table.window.document,target));table.window.close();
    }
});
test('M41 metadata uses pure PageMetadata over new author subtitle; descriptions cannot be silently ignored',()=>{
    const metadata=capture.expectedMetadata();assert.deepEqual(Object.keys(metadata),capture.targetPaths);
    for(const lesson of d.master.lessons){assert.equal(metadata[lesson.theory_path].title,lesson.title+' — правила | Gramlyze');assert.ok(metadata[lesson.theory_path].description.includes(lesson.subtitle.split('?')[0]+'?'));}
    const php=fs.readFileSync(path.join(ROOT,'tools/diagnostics/m41-expected-http-metadata.php'),'utf8').replace(/\/\/[^\n]*/gu,'');assert.doesNotMatch(php,/bootstrap|\.env|DB::|connect|kernel/i);
    const source=fs.readFileSync(path.join(ROOT,'tools/diagnostics/capture-m41-http.cjs'),'utf8');assert.match(source,/expected\[row\.path\]\.description/);assert.match(source,/Fresh pre-apply native-anchor inventory required/);
});
test('Read-only acceptance keeps36PRE gate108M41practiceimages, all36M39/M40cases and native noJS tokens/keys',()=>{
    const source=fs.readFileSync(path.join(ROOT,'tools/diagnostics/seo-m41-local.cjs'),'utf8');
    assert.match(source,/before\.caseStates, 36/);assert.match(source,/before\.sourceHashes, hashes/);assert.doesNotMatch(source,/fullPage:\s*true|page\.setContent|route\.fulfill/);
    assert.match(source,/source_index:task\.source_index/);assert.match(source,/m41-static-token/);assert.match(source,/report\.noJS\.length,9/);
    assert.match(source,/regressions\.flatMap\(row=>row\.cases\)\.length,36/);assert.match(source,/missingParts\.flatMap\(row=>row\.cases\)\.length,28/);
    assert.match(source,/rows\.length,12/);assert.match(source,/sourceLegacy/);assert.match(source,/detailId/);assert.match(source,/every\(node=>node\.open\)/);
    const hashes=d.sourceHashes();assert.equal(Object.keys(hashes).length,6);for(const hash of Object.values(hashes))assert.match(hash,/^[a-f0-9]{64}$/);
});
test('Table keyboard proof measuresactualfocus+ArrowRight, not End or forced scrollLeft',()=>{
    const source=fs.readFileSync(path.join(ROOT,'tools/diagnostics/seo-m41-local.cjs'),'utf8');assert.match(source,/actual:document\.activeElement===node/);
    assert.match(source,/afterFirstArrow>row\.before\.scrollLeft/);assert.match(source,/row\.screenshot=await shot\(table/);assert.doesNotMatch(source,/press\('End'\)|node\.scrollLeft\s*=/);
    assert.equal(typeof d.mobileTableQA,'function');
});
