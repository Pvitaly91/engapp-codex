'use strict';
// Fresh M43.1 guest GET evidence; only http://gramlyze.loc and no response credentials.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto');
const assert = require('node:assert/strict');
const prior = require('./capture-m43-http.cjs');
const PRIVATE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-1-local';
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
function compare(before, after) {
 assert.equal(before.pass, true); assert.equal(after.pass, true);
 assert.equal(before.base, prior.BASE); assert.equal(after.base, prior.BASE);
 assert.deepEqual(after.rows.map(row => row.path), prior.routes);
 for (const row of after.rows) {
  const old = before.rows.find(r => r.path === row.path); assert.ok(old);
  for (const field of ['status','finalUrl','contentType','xRobots','meta','jsonLd','links'])
   assert.deepEqual(row[field], old[field], 'Exact fresh presentation-only equality ' + row.path + ':' + field);
  if(prior.targetPaths.includes(row.path)) {
   // Exact punctuation/lang/detail text is independently bound by author-dom BEFORE/AFTER.
   // The raw text hash may include a decorative icon that presentation is allowed to replace.
   for(const field of ['words','orderedWords','tables','practice','anchors','headings'])
    assert.deepEqual(row.learner[field],old.learner[field],'Exact target learner '+row.path+':'+field);
   assert.deepEqual(row.learner.details.map(({text,...identity})=>identity),old.learner.details.map(({text,...identity})=>identity),'Point detail identities '+row.path);
  } else assert.deepEqual(row.learner,old.learner,'Exact unchanged non-target learner '+row.path);
 }
 for (const route of Object.keys(before.extras))
  for (const field of ['status','finalUrl','sha256','count','orderedSha256'])
   assert.deepEqual(after.extras[route][field], before.extras[route][field], route + ':' + field);
 return {pass:true,targets:prior.targetPaths.length,controls:prior.controls.length,metadataExact:true,
  structuredDataExact:true,learnerStructureAndWordsExact:true,nonTargetLearnerExact:true,sitemapExact:true,cookiesSent:false,dbWrites:false};
}
async function run() {
 const [label, beforeName] = process.argv.slice(2);
 assert.match(label,/^(?:before|after|final)-v[1-9][0-9]*$/u);
 if (!label.startsWith('before')) assert.match(beforeName,/^before-v[1-9][0-9]*-http\.json$/u);
 const result = await prior.capture();
 fs.mkdirSync(PRIVATE,{recursive:true});
 const file = path.join(PRIVATE,label + '-http.json');
 fs.writeFileSync(file,JSON.stringify(result,null,2)+'\n',{flag:'wx'});
 const checked = !label.startsWith('before') && result.pass
  ? compare(JSON.parse(fs.readFileSync(path.join(PRIVATE,beforeName))),result) : null;
 console.log(JSON.stringify({pass:result.pass,file,sha256:sha(fs.readFileSync(file)),rows:result.rows.length,checked,
  errors:result.rows.filter(r=>r.status!==200||r.error).map(r=>({path:r.path,status:r.status,error:r.error}))}));
 if(!result.pass)process.exitCode=1;
}
if(require.main===module)run().catch(e=>{console.error(e.stack);process.exitCode=1;});
module.exports={compare};
