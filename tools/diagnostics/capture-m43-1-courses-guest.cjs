'use strict';
// AFTER-only public guest observation. It is not a fabricated live BEFORE/AFTER course comparison.
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const {JSDOM}=require('jsdom'),{guestGet}=require('./capture-m42-design-http.cjs'),{metadata}=require('./seo-m28-local.cjs');
const PRIVATE='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-1-local';
const TARGETS=['past-perfect-vs-past-perfect-continuous','stative-verbs','used-to-would'];
const sha=v=>crypto.createHash('sha256').update(v).digest('hex'),norm=v=>String(v||'').replace(/\s+/gu,' ').trim();
function learner(doc){const content=doc.querySelector('[data-theory-lesson-content]');assert.ok(content,'Public server-rendered course learner content present');
 const clone=content.cloneNode(true);clone.querySelectorAll('script,style,noscript,input,textarea,[data-sentence-builder],[data-theory-ui],[aria-hidden="true"]').forEach(n=>n.remove());
 return {text:norm(clone.textContent),headings:[...clone.querySelectorAll('h2,h3,h4')].map(n=>norm(n.textContent)),
  tables:[...clone.querySelectorAll('table')].map(t=>({headers:[...t.querySelectorAll('thead th')].map(n=>norm(n.textContent)),rows:[...t.querySelectorAll('tbody tr')].map(r=>[...r.children].map(n=>norm(n.textContent)))})),
  anchors:[...clone.querySelectorAll('[id]')].map(n=>n.id),links:[...clone.querySelectorAll('a[href]')].map(n=>({href:n.getAttribute('href'),label:norm(n.textContent)})),
  m43AuthorMarkers:content.querySelectorAll('[data-m43-author-section],[data-m43-practice-ui],.m43-native-design,.m43-practice-design').length,
  m43SafetyStylePresent:[...doc.querySelectorAll('style')].some(n=>n.textContent.includes('.m43-native-design[data-m43-author-section]'))};}
async function run(){const[label]=process.argv.slice(2);assert.match(label,/^(?:after|final)-v[1-9][0-9]*$/u);
 const rows=[];for(const slug of TARGETS){const route='/courses/english-grammar-theory/lesson/tenses/'+slug,result=await guestGet(route);assert.ok(!result.error);assert.equal(result.response.status,200);
  const dom=new JSDOM(result.body);try{const content=learner(dom.window.document);assert.ok(content.text.length>100);assert.equal(content.m43AuthorMarkers,0);assert.equal(content.m43SafetyStylePresent,false);
   rows.push({path:route,at:new Date().toISOString(),status:result.response.status,finalUrl:result.finalUrl,attempts:result.attempts,
    metadata:metadata(dom.window.document),learner:content,learnerTextSha256:sha(content.text),pass:true});}finally{dom.window.close();}}
 const report={schema:'m43-1-after-only-course-guest-v1',at:new Date().toISOString(),base:'http://gramlyze.loc',pass:true,rows,
  observation:'Current guest GETs preserve server-rendered learner content and contain no M43 author/style opt-in. No guest gate was bypassed.',
  limitation:'The initial 65-HTTP baseline has learner:null for courses. This AFTER-only record cannot establish an actual live course learner BEFORE/AFTER equality; isolated exact course preservation tests provide that separate regression evidence.',
  browserGateEvaluated:false,cookiesSent:false,authSent:false,refererSent:false,dbWrites:false};
 const file=path.join(PRIVATE,'courses-guest-'+label+'.json');fs.mkdirSync(PRIVATE,{recursive:true});fs.writeFileSync(file,JSON.stringify(report,null,2)+'\n',{flag:'wx'});
 console.log(JSON.stringify({file,sha256:sha(fs.readFileSync(file)),pass:true,guestGets:rows.length,m43AuthorMarkers:0,m43SafetyStylePresent:false,beforeAfterCourseEqualityClaimed:false}));}
run().catch(e=>{console.error(e.stack);process.exitCode=1;});
