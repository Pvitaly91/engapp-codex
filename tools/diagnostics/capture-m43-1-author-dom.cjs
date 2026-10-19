'use strict';
// Exact learner codepoints, words, punctuation and lang fragments, excluding decorative/UI nodes.
// Block markup may add separator whitespace to raw textContent; never discard that observed difference.
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const {JSDOM}=require('jsdom'), {guestGet}=require('./capture-m42-design-http.cjs');
const {targetPaths}=require('./capture-m43-http.cjs');
const PRIVATE='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-1-local';
const sha=v=>crypto.createHash('sha256').update(v).digest('hex'), norm=t=>String(t||'').replace(/\s+/gu,' ').trim();
const words=t=>String(t).match(/[\p{L}\p{N}]+(?:[’'\-][\p{L}\p{N}]+)*/gu)||[];
const BLOCKS=new Set(['p','div','section','article','li','ul','ol','table','thead','tbody','tr','th','td','h1','h2','h3','h4','h5','h6','blockquote','details','summary','form','fieldset','legend','br']);
function whitespaceSlots(text){const positions=new Set();let count=0;for(const c of text){if(/\s/u.test(c))positions.add(count);else count++;}return positions;}
function blockBoundaries(main){const positions=new Set();let count=0;
 const walk=node=>{if(node.nodeType===3){for(const c of node.nodeValue)if(!/\s/u.test(c))count++;return;}
  const block=node.nodeType===1&&(BLOCKS.has(node.localName)||node.classList.contains('block'));
  if(block)positions.add(count);for(const child of node.childNodes)walk(child);if(block)positions.add(count);};walk(main);return [...positions].sort((a,b)=>a-b);}
function author(doc){const main=doc.querySelector('[data-theory-main]')?.cloneNode(true);assert.ok(main);
 main.querySelectorAll('script,style,noscript,[data-theory-ui],.theory-section-number,[aria-hidden="true"]').forEach(n=>n.remove());
 return {text:norm(main.textContent),blockBoundaries:blockBoundaries(main),langFragments:[...main.querySelectorAll('[lang="en"],[lang="uk"]')].map(n=>({lang:n.lang,text:norm(n.textContent)})),
  notes:[...main.querySelectorAll('.m43-example-note,[data-m43-note]')].map(n=>norm(n.textContent)),
  details:[...main.querySelectorAll('[data-theory-native-extension] > details')].map(n=>({id:n.querySelector('[id]')?.id||n.id||null,
   fragmentId:n.querySelector('.theory-point-fragment')?.id||null,text:norm(n.querySelector('.theory-point-fragment')?.textContent||n.textContent),
   pointAnchor:n.closest('article')?.id||null,pointKey:n.parentElement.getAttribute('data-theory-section'),pointIndex:n.parentElement.getAttribute('data-theory-point-index')}))};}
async function run(){const[label,beforeName]=process.argv.slice(2);assert.match(label,/^(before|after|final)-v[1-9][0-9]*$/u);
 const rows=[];for(const route of targetPaths){const r=await guestGet(route);assert.ok(!r.error);assert.equal(r.response.status,200);const dom=new JSDOM(r.body);
  try{rows.push({path:route,author:author(dom.window.document)});}finally{dom.window.close();}}
 const result={schema:'m43-1-author-dom-v1',at:new Date().toISOString(),base:'http://gramlyze.loc',rows,pass:true};
 if(!label.startsWith('before')){assert.match(beforeName,/^author-dom-before-v[1-9][0-9]*\.json$/u);const old=JSON.parse(fs.readFileSync(path.join(PRIVATE,beforeName),'utf8'));
  assert.deepEqual(result.rows.map(r=>r.path),old.rows.map(r=>r.path));const comparisons=[];
  for(const row of result.rows){const prior=old.rows.find(r=>r.path===row.path);assert.ok(prior);
   for(const field of ['langFragments','notes','details'])assert.deepEqual(row.author[field],prior.author[field],'Exact '+field+' '+row.path);
   assert.equal(row.author.text.replace(/\s+/gu,''),prior.author.text.replace(/\s+/gu,''),'Every learner non-whitespace codepoint/punctuation preserved '+row.path);
   assert.deepEqual(words(row.author.text),words(prior.author.text),'Exact meaningful word boundaries/case/order '+row.path);
   const currentSpaces=whitespaceSlots(row.author.text),oldSpaces=whitespaceSlots(prior.author.text),boundaries=new Set(row.author.blockBoundaries);
   const changedSpaces=[...new Set([...currentSpaces,...oldSpaces])].filter(p=>currentSpaces.has(p)!==oldSpaces.has(p)).sort((a,b)=>a-b);
   for(const position of changedSpaces)assert.ok(boundaries.has(position),'Whitespace change must occur at an actual HTML block boundary '+row.path+':'+position);
   comparisons.push({path:row.path,rawFlatTextEquality:row.author.text===prior.author.text,
    blockSeparatorWhitespaceOnly:row.author.text!==prior.author.text,exactLangFragments:true,exactNotes:true,exactPointDetails:true,
    changedWhitespacePositions:changedSpaces,allChangedWhitespaceAtHtmlBlockBoundaries:true,
    allNonWhitespaceCodepointsExact:true,wordBoundariesCaseAndOrderExact:true});}
  result.comparison={pass:true,before:beforeName,rows:comparisons,
   note:'Initial flat textContent equality rejected separator whitespace introduced by presentation markup. Every changed whitespace position must coincide with an actual HTML block boundary. Exact lang snippets/details/notes, every non-whitespace codepoint and meaningful word boundaries/case/order remain independently required; raw text equality is reported rather than claimed.'};}
 const file=path.join(PRIVATE,'author-dom-'+label+'.json');fs.mkdirSync(PRIVATE,{recursive:true});fs.writeFileSync(file,JSON.stringify(result,null,2)+'\n',{flag:'wx'});
 console.log(JSON.stringify({file,sha256:sha(fs.readFileSync(file)),pass:true,targets:rows.length,langFragments:rows.reduce((n,r)=>n+r.author.langFragments.length,0),details:rows.reduce((n,r)=>n+r.author.details.length,0),rawFlatTextEquality:result.comparison?.rows.every(r=>r.rawFlatTextEquality)??null,semanticContentPreserved:result.comparison?.pass??null}));}
if(require.main===module)run().catch(e=>{console.error(e.stack);process.exitCode=1;});
module.exports={author};
