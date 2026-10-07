'use strict';
// Isolated helper/negative fixtures. No HTTP, Laravel, working DB or Git.
const test=require('node:test'),assert=require('node:assert/strict'),{JSDOM}=require('jsdom');
const qa=require('../../tools/diagnostics/seo-m43-local.cjs');
const esc=value=>String(value??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
const p=value=>'<p lang="uk">'+esc(value)+'</p>';
function examples(values){return values.map(e=>'<div class="theory-example"><p lang="en">'+esc(e.en)+'</p>'+p(e.uk)+(e.note_uk?p(e.note_uk):'')+'</div>').join('');}
function fixture(target){const l=target.lesson;let html='<h1>'+esc(l.title)+'</h1><main data-theory-main>'+p(l.subtitle)+l.hero.map(h=>Object.values(h).map(p).join('')).join('');
    for(const s of l.sections){html+='<section data-m43-author-section="'+s.id+'" data-m43-native-layout="'+s.native_kind+'"><header class="theory-section-header"><h2>'+esc(s.title)+'</h2></header>'+(s.intro_uk?p(s.intro_uk):'');
        for(const point of s.points){html+='<article id="'+point.id+'" data-m43-basic-point="'+point.id+'"><h3>'+esc(point.title)+'</h3>'+(point.formula?'<strong>'+esc(point.formula)+'</strong>':'')+point.paragraphs_uk.map(p).join('');
            if(point.wrong_en)html+='<div class="theory-example theory-example--wrong"><p lang="en">'+esc(point.wrong_en)+'</p>'+(point.wrong_uk?p(point.wrong_uk):'')+'</div><div class="theory-example theory-example--right"><p lang="en">'+esc(point.right_en)+'</p>'+p(point.right_uk)+'</div>';
            html+=examples(point.examples);
            if(point.detail){const d=point.detail;html+='<div data-theory-native-extension><details><summary>Докладніше</summary><section id="block-'+d.id+'" class="theory-point-fragment"><h4>'+esc(d.title)+'</h4>'+d.paragraphs_uk.map(p).join('')+examples(d.examples)+'</section></details></div>';}
            html+='</article>';}
        if(s.table){html+='<table data-m43-native-table><thead><tr>'+s.table.columns.map(c=>'<th>'+esc(c)+'</th>').join('')+'</tr></thead><tbody>'+s.table.rows.map(row=>'<tr>'+row.map(c=>'<td>'+(typeof c==='string'?esc(c):c.text_uk?esc(c.text_uk):examples([c]))+'</td>').join('')+'</tr>').join('')+'</tbody></table>';}
        html+=(s.notes_uk||[]).map(p).join('')+examples(s.note_examples||[])+'</section>';}
    return html+'</main>';}
function withDOM(target,fn){const dom=new JSDOM(fixture(target));try{return fn(dom.window.document);}finally{dom.window.close();}}
test('finite frozen scope has 3 lessons, 18 tasks, 35 controls and 6 meaningful details',()=>{
    assert.equal(qa.targets.length,3);assert.equal(qa.master.lessons.flatMap(l=>l.practice).length,18);
    assert.equal(qa.master.lessons.flatMap(l=>l.practice.flatMap(t=>t.controls)).length,35);
    assert.equal(qa.master.lessons.flatMap(l=>l.sections.flatMap(s=>s.points)).filter(p=>p.detail).length,6);
    for(const target of qa.targets)assert.deepEqual(target.lesson.practice.map(t=>t.source_index),[1,2,3,4,5,6]);
});
test('practice selector targets authored cases without confusing the nested own-bank Alpine component',()=>{
    const dom=new JSDOM('<section data-m43-practice-ui><div id="authored" x-data="m43PracticeUi({cases:[]})"></div><div data-sentence-builder x-data="practiceQuestion_own_bank()"></div></section>');
    try {
        assert.equal(dom.window.document.querySelectorAll('[data-m43-practice-ui] [x-data]').length,2);
        const matches=dom.window.document.querySelectorAll(qa.PRACTICE_COMPONENT_SELECTOR);
        assert.equal(matches.length,1); assert.equal(matches[0].id,'authored');
    }finally{dom.window.close();}
});
test('localhost GET guard never permits production, lookalike host, credentials or writes',()=>{
    assert.equal(qa.allowedRequest('http://gramlyze.loc/theory','GET'),true);
    for(const url of ['https://gramlyze.loc/','http://gramlyze.loc.evil/','https://gramlyze.com/','http://gramlyze.ub/','http://user:secret@gramlyze.loc/','http://127.0.0.1/'])assert.equal(qa.allowedRequest(url,'GET'),false);
    for(const method of ['HEAD','POST','PUT','DELETE'])assert.equal(qa.allowedRequest('http://gramlyze.loc/',method),false);
    assert.equal(qa.safeUrl('http://gramlyze.loc/route?token=secret#private'),'http://gramlyze.loc/route');
});
for(const target of qa.targets){
    test(target.key+' exact authored native DOM passes independently',()=>withDOM(target,doc=>{
        const result=qa.authorFidelity(doc,target);assert.equal(result.sections,target.lesson.sections.length);assert.equal(result.points,target.lesson.sections.flatMap(s=>s.points).length);
    }));
    for(const mutation of ['paragraph','translation','hidden-clone','point-order','lost-point','detail-owner','table-column','table-translation','wrong-not'])
        test(target.key+' rejects '+mutation,()=>withDOM(target,doc=>{
            const main=doc.querySelector('[data-theory-main]'),points=[...doc.querySelectorAll('[data-m43-basic-point]')];
            if(mutation==='paragraph')points[0].querySelector('p[lang="uk"]').textContent+=' Додано без master.';
            if(mutation==='translation')main.querySelector('.theory-example [lang="uk"]').textContent='Це не повний переклад.';
            if(mutation==='hidden-clone'){const example=main.querySelector('.theory-example'),clone=example.cloneNode(true);clone.hidden=true;example.after(clone);}
            if(mutation==='point-order'){const point=points.find(n=>n.parentElement.querySelectorAll('[data-m43-basic-point]').length>1);point.parentElement.append(point);}
            if(mutation==='lost-point')points[0].remove();
            if(mutation==='detail-owner'){const ext=main.querySelector('[data-theory-native-extension]');points.find(n=>!n.contains(ext)).append(ext);}
            if(mutation==='table-column')main.querySelector('thead th').remove();
            if(mutation==='table-translation')main.querySelector('table [lang="uk"]').textContent='Неправильний переклад';
            if(mutation==='wrong-not')main.querySelector('.theory-example--right [lang="en"]').textContent+=' not';
            assert.throws(()=>qa.authorFidelity(doc,target));
        }));
}
test('ordered exact text prevents silent omissions and reordered conditions',()=>{
    qa.onceOrdered('One. Not two. Three.',['One.','Not two.','Three.'],'fixture');
    assert.throws(()=>qa.onceOrdered('One. Two. Three.',['One.','Not two.','Three.'],'fixture'));
    assert.throws(()=>qa.onceOrdered('Three. One. Not two.',['One.','Not two.','Three.'],'fixture'));
});
for(const target of qa.targets)for(const task of target.lesson.practice)test(task.id+' full feedback uses separate exact EN/UK and prose once',()=>{
    const dom=new JSDOM(examples(task.feedback.answer_examples)+task.feedback.paragraphs_uk.map(p).join(''));
    try{qa.feedbackFidelity(dom.window.document,task);const example=dom.window.document.querySelector('.theory-example');example.after(example.cloneNode(true));assert.throws(()=>qa.feedbackFidelity(dom.window.document,task));}finally{dom.window.close();}
});
module.exports={fixture};
