'use strict';
const test=require('node:test'),assert=require('node:assert/strict'),{JSDOM}=require('jsdom');
const qa=require('../../tools/diagnostics/seo-m41-design-local.cjs'),m41=require('../../tools/diagnostics/seo-m41-local.cjs');
const esc=v=>String(v).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
function fixture(target,forms=true){const lesson=m41.masterLesson(target);
 let html='<h1>'+esc(lesson.title)+'</h1><main data-theory-main><p>'+esc(lesson.subtitle)+'</p>';
 for(const rule of lesson.hero)html+=Object.values(rule).map(value=>'<p>'+esc(value)+'</p>').join('');
 for(const section of lesson.sections){html+='<section data-m41-author-section="'+esc(section.id)+'"><div class="theory-section-header"><h2>'+esc(section.title)+'</h2></div>';
  for(const point of section.points){html+='<article data-m41-basic-point data-m41-author-point id="'+esc(point.id)+'"><h3>'+esc(point.title)+'</h3>'+point.paragraphs_uk.map(v=>'<p>'+esc(v)+'</p>').join('')+point.examples.map(e=>'<p>'+esc(e.en)+'</p><p>'+esc(e.uk)+'</p>').join('');
   if(point.detail)html+='<div data-theory-native-extension><details><summary>Докладніше</summary><div class="theory-point-fragment" id="block-m41-'+esc(point.id)+'-detail"><h4>'+esc(point.detail.title)+'</h4>'+point.detail.paragraphs_uk.map(v=>'<p>'+esc(v)+'</p>').join('')+point.detail.examples.map(e=>'<p>'+esc(e.en)+'</p><p>'+esc(e.uk)+'</p>').join('')+'</div></details></div>';
   html+='</article>';}
  if(section.table){if(forms){html+=section.table.columns.map((c,i)=>'<p data-m41-form-column="'+i+'">'+esc(c)+'</p>').join('');
   for(const[r,row]of section.table.rows.entries()){html+='<p data-m41-form-row-label="'+r+'">'+esc(row[0])+'</p>';for(const[c,cell]of row.entries())if(c>0){const[en,uk]=cell.split('\n');html+='<div data-m41-form-cell="'+r+'-'+c+'"><p data-m41-form-en>'+esc(en)+'</p><p data-m41-form-uk>'+esc(uk)+'</p></div>';}}}
   else html+='<table><thead><tr>'+section.table.columns.map(c=>'<th>'+esc(c)+'</th>').join('')+'</tr></thead><tbody>'+section.table.rows.map(row=>'<tr>'+row.map(c=>'<td>'+esc(c)+'</td>').join('')+'</tr>').join('')+'</tbody></table>';}
  html+='</section>';}
 return html+'</main>';}
for(const target of m41.targets){test(target.slug+' exact native forms fidelity and own details',()=>{const dom=new JSDOM(fixture(target));try{const result=qa.authorFidelity(dom.window.document,target);assert.equal(result.points,m41.masterLesson(target).sections.flatMap(s=>s.points).length);assert.equal(result.details,target.slug==='past-simple-vs-past-continuous'?4:5);}finally{dom.window.close();}});
 test(target.slug+' exact table fallback fidelity',()=>{const dom=new JSDOM(fixture(target,false));try{qa.authorFidelity(dom.window.document,target);}finally{dom.window.close();}});
 test(target.slug+' dropped basic point is rejected',()=>{const dom=new JSDOM(fixture(target));try{dom.window.document.querySelector('[data-m41-basic-point]').remove();assert.throws(()=>qa.authorFidelity(dom.window.document,target));}finally{dom.window.close();}});
 test(target.slug+' changed grammar formula cell is rejected',()=>{const dom=new JSDOM(fixture(target));try{dom.window.document.querySelector('[data-m41-form-cell]').textContent='Unapproved changed formula.';assert.throws(()=>qa.authorFidelity(dom.window.document,target));}finally{dom.window.close();}});
 test(target.slug+' wrong order is rejected',()=>{const dom=new JSDOM(fixture(target));try{const first=dom.window.document.querySelector('[data-m41-basic-point]');first.parentNode.append(first);assert.throws(()=>qa.authorFidelity(dom.window.document,target));}finally{dom.window.close();}});
 test(target.slug+' another point detail cannot satisfy ownership',()=>{const dom=new JSDOM(fixture(target));try{const details=[...dom.window.document.querySelectorAll('[data-theory-native-extension]')];details[1].append(details[0].firstElementChild);assert.throws(()=>qa.authorFidelity(dom.window.document,target));}finally{dom.window.close();}});
}
