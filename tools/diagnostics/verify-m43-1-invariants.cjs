'use strict';
// Presentation-only M43.1 invariants. SELECT snapshots are captured separately; this tool does not connect to MySQL.
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const {guestGet}=require('./capture-m42-design-http.cjs');
const ROOT='D:/DEV/htdocs/gramlyze.loc', WT='C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
const PRIVATE=path.join(ROOT,'storage/app/seo-m43-1-local');
const sha=v=>crypto.createHash('sha256').update(v).digest('hex');
function file(name){assert.match(name,/^[a-z0-9-]+\.json$/u);return path.join(PRIVATE,name);}
function read(name){return JSON.parse(fs.readFileSync(file(name),'utf8'));}
function write(name,payload){fs.mkdirSync(PRIVATE,{recursive:true});fs.writeFileSync(file(name),JSON.stringify(payload,null,2)+'\n',{flag:'wx'});return {file:file(name),sha256:sha(fs.readFileSync(file(name)))};}
function frozenPaths(){return [...fs.readdirSync(path.join(WT,'docs/content')).filter(n=>/^m43-/u.test(n)).map(n=>'docs/content/'+n),
 ...fs.readdirSync(path.join(WT,'database/content-patches')).filter(n=>/^m43-/u.test(n)).map(n=>'database/content-patches/'+n),
 ...['TensesPastPerfectVsPastPerfectContinuousTheorySeeder','TensesStativeVerbsTheorySeeder','TensesUsedToWouldTheorySeeder'].map(n=>'database/seeders/Page_V3/Tenses/'+n+'/definition.json')].sort();}
function frozen(label){
 const inventory=read('source-before-v1.json'),files=[];
 const accepted=JSON.parse(fs.readFileSync(path.join(WT,'docs/content/m43-checksums.v1.0.0.json'),'utf8')).files;
 for(const relative of frozenPaths()){
  const left=fs.readFileSync(path.join(ROOT,relative)),right=fs.readFileSync(path.join(WT,relative));
  const digest=sha(right);assert.equal(sha(left),digest,'Served/worktree frozen bytes '+relative);
  for(const root of ['root','worktree']){const before=inventory.sources[root].find(r=>r.path===relative);assert.ok(before?.exists);assert.equal(digest,before.sha256,'Fresh BEFORE frozen preservation '+relative);}
  if(accepted[relative])assert.equal(digest,accepted[relative],'Accepted master checksum '+relative);
  files.push({path:relative,bytes:right.length,sha256:digest});
 }
 const payload={schema:'m43-1-frozen-equality-v1',at:new Date().toISOString(),pass:true,files,sourceWrites:false,dbAccess:false};
 console.log(JSON.stringify({...write('frozen-'+label+'.json',payload),pass:true,files:files.length}));
}
function compareDb(beforeName,afterName,label){
 const before=read(beforeName),after=read(afterName);
 assert.deepEqual(after.target,before.target,'Physical connection remains local');
 assert.deepEqual(after.runtime,before.runtime,'Runtime identity remains unchanged');
 assert.deepEqual(after.raw_tables,before.raw_tables,'All rows of all tables unchanged');
 assert.deepEqual(after.protected_tables,before.protected_tables,'Every table is protected in M43.1');
 assert.deepEqual(after.targets,before.targets,'Exact owners, source, banks, UUIDs, bodies, locale rows unchanged');
 const record={schema:'m43-1-db-equality-v1',at:new Date().toISOString(),pass:true,before:beforeName,after:afterName,
  beforeSha256:sha(fs.readFileSync(file(beforeName))),afterSha256:sha(fs.readFileSync(file(afterName))),tables:Object.keys(before.raw_tables).length,
  updated:0,inserted:0,deleted:0,allTablesProtected:true,dbAccess:false};
 console.log(JSON.stringify({...write('db-equality-'+label+'.json',record),...record}));
}
async function physical(label){
 const physical=read('physical-target-'+label+'.json'),db=read('m43-1-'+label+'.json'),http=read(label+'-http.json');
 assert.equal(physical.documentRoot,ROOT+'/public');assert.equal(http.base,'http://gramlyze.loc');assert.equal(http.pass,true);
 let physicalBeforeEquality=null;
 if(label.startsWith('after')){const before=read('physical-target-before-v1.json');
  assert.deepEqual(physical.hashes,before.hashes,'All fresh physical config/public fingerprints unchanged');
  for(const field of ['localHost','scheme','documentRoot','vhostSource','vhostLine'])assert.deepEqual(physical[field],before[field],'Same actual physical target '+field);
  physicalBeforeEquality={pass:true,files:Object.keys(physical.hashes).length,configAndPublicRootHashesExact:true,
   rawEffectiveDumpHashMayVaryByMutexOrder:true};}
 assert.equal(db.target.root,ROOT);assert.equal(db.target.driver,'mysql');assert.equal(db.target.database,'gr2');assert.equal(db.target.port,3306);
 const safe={};
 for(const route of ['/health','/dev/site-mode']){
  const result=await guestGet(route,'application/json');assert.ok(!result.error);assert.equal(result.response.status,200);
  const json=JSON.parse(result.body);
  if(route==='/health'){assert.equal(json.status,'ok');safe.health={status:json.status};}
  else{assert.equal(json.host,'gramlyze.loc');assert.equal(json.mode,'development');safe.siteMode={host:json.host,mode:json.mode};}
 }
 const targets=db.targets.map(t=>{
  assert.equal(t.source_db_exact,true);
  const row=http.rows.find(r=>r.finalUrl===t.url);assert.ok(row&&row.status===200&&row.learner);
  assert.deepEqual(row.meta.h1,[t.page.title]);
  assert.ok(row.learner.details.length===({ 'Past Perfect vs Past Perfect Continuous':3,'Stative Verbs':1,'Used to / Would':2 }[t.page.title]));
  return {identity:t.identity,url:t.url,title:t.page.title,definitionSha256:t.definition_sha256,
   learnerTextSha256:row.learner.textSha256,details:row.learner.details.length,sourceDbExact:true};
 });
 const payload={schema:'m43-1-current-local-identity-v1',at:new Date().toISOString(),pass:true,physical,cliTarget:db.target,
  physicalBeforeEquality,runtime:db.runtime,web:safe,targets,evidence:['fresh effective Apache vhost and running listener','fresh loopback DNS',
   'current health GET confirms web database read succeeds','current SiteMode GET confirms local host/mode',
   'exact served definitions/actual SELECT rows and current target H1/detail identities agree'],
  limitation:'No temporary probe was added. Existing read-only HTTP endpoints do not reveal DB name; web/CLI identity is established by current vhost/source/runtime/learner agreement.',
  routeAdded:false,configWrites:false,dbWrites:false,cookiesSent:false};
 console.log(JSON.stringify({...write('local-identity-'+label+'.json',payload),pass:true,targets:targets.length,routeAdded:false,dbWrites:false}));
}
const [mode,...args]=process.argv.slice(2);
if(mode==='frozen'){assert.equal(args.length,1);assert.match(args[0],/^(?:before|after|final)-v[1-9][0-9]*$/u);frozen(args[0]);}
else if(mode==='db-compare'){assert.equal(args.length,3);compareDb(...args);}
else if(mode==='physical'){assert.equal(args.length,1);assert.match(args[0],/^(?:before|after)-v[1-9][0-9]*$/u);physical(args[0]).catch(e=>{console.error(e.stack);process.exitCode=1;});}
else throw new Error('Expected frozen, db-compare, or physical mode');
