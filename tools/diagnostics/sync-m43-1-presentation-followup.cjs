'use strict';
// Finite presentation correction after reviewed M43.1 sync v1. No Laravel/DB/config operations.
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const {execFileSync}=require('node:child_process');
const {project,physicalCheck}=require('./sync-m43-1-presentation.cjs');
const ROOT='D:/DEV/htdocs/gramlyze.loc',WT='C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
const PRIVATE=path.join(ROOT,'storage/app/seo-m43-1-local'),BASE='477897543e91c02dff90747b439cd4ac939bfc6a';
const BRANCH='codex/seo-m43-ppc-reference-design';
const V1_RESULT_SHA='9068e4124cd9d77b6422aa5dcb87fb20934f692e55e8759ab8b7f8c97eb08ce4';
const V1_PROPOSAL_SHA='6999186ded1aace403c7f5bb400fdb749808a43f304367f455538f34a2fbe4b9';
const UNUSED_V2_PROPOSAL_SHA='7469666676cde7fdab1ec4ff24adbbb4aa8a79daddbb1baf1df0ea3387c85e83';
const PATHS=Object.freeze(['app/Support/M43NativeHtml.php','resources/views/engram/theory/blocks-v3/m43-native-styles.blade.php',
 'resources/views/engram/theory/blocks-v3/m43-practice-ui.blade.php','resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php',
 'resources/views/engram/theory/blocks-v3/m43-native-section.blade.php','resources/views/engram/theory/blocks-v3/usage-panels.blade.php']);
// Preview v2 was not applied: final reference instruction typography superseded its helper hash.
// Keep that private proposal immutable and pin its unused status in the v3 evidence.
const COMMON=PATHS[3],PROPOSAL='source-sync-proposal-v3.json',BACKUP='source-sync-backup-v3';
const sha=v=>crypto.createHash('sha256').update(v).digest('hex');
const normalized=b=>{const s=b.toString('utf8');assert.ok(Buffer.from(s,'utf8').equals(b));assert.ok(!s.replaceAll('\r\n','\n').includes('\r'));return s.replaceAll('\r\n','\n');};
function ordinary(base,relative){assert.ok(PATHS.includes(relative));const file=path.join(base,relative),s=fs.lstatSync(file);assert.ok(s.isFile()&&!s.isSymbolicLink());return file;}
function git(args,allowDifference=false){try{return execFileSync('git',['-c','safe.directory='+WT,'-c','core.bare=false','-C',WT,...args],{timeout:30000,maxBuffer:8*1024*1024,stdio:['ignore','pipe','pipe']});}
 catch(error){if(allowDifference&&error.status===1&&error.stdout)return error.stdout;throw error;}}
function exclusive(file,bytes){assert.ok(!fs.existsSync(file),'Exclusive artifact required '+file);fs.writeFileSync(file,bytes,{flag:'wx'});assert.ok(fs.readFileSync(file).equals(Buffer.from(bytes)));}
function build(){
 assert.equal(process.platform,'win32');assert.equal(git(['branch','--show-current']).toString('utf8').trim(),BRANCH);
 assert.equal(git(['rev-parse','HEAD']).toString('utf8').trim(),BASE);git(['merge-base','--is-ancestor',BASE,'HEAD']);
 const beforeBytes=fs.readFileSync(path.join(PRIVATE,'source-before-v1.json')),before=JSON.parse(beforeBytes);
 assert.equal(before.schema,'m43-1-source-inventory-v1');assert.deepEqual(before.roots,{root:ROOT,worktree:WT});
 const resultBytes=fs.readFileSync(path.join(PRIVATE,'source-sync-backup-v1/result.json'));assert.equal(sha(resultBytes),V1_RESULT_SHA);
 const result=JSON.parse(resultBytes);assert.equal(result.pass,true);assert.equal(result.files,9);assert.equal(result.proposalSha256,V1_PROPOSAL_SHA);assert.equal(result.dbAccess,false);
 const v1ProposalBytes=fs.readFileSync(path.join(PRIVATE,'source-sync-proposal-v1.json'));assert.equal(sha(v1ProposalBytes),V1_PROPOSAL_SHA);
 const unusedV2Bytes=fs.readFileSync(path.join(PRIVATE,'source-sync-proposal-v2.json'));assert.equal(sha(unusedV2Bytes),UNUSED_V2_PROPOSAL_SHA);
 assert.ok(!fs.existsSync(path.join(PRIVATE,'source-sync-backup-v2')),'Preview v2 must remain unused');
 const records=[],outputs=[];
 for(const relative of PATHS){const rootFile=ordinary(ROOT,relative),wtFile=ordinary(WT,relative),rootBytes=fs.readFileSync(rootFile),wtBytes=fs.readFileSync(wtFile);
  let authority;
  if(relative===COMMON){const row=before.sources.root.find(r=>r.path===relative);assert.ok(row?.exists);assert.equal(sha(rootBytes),row.sha256,'Untouched common consumer must equal fresh initial BEFORE');
   const baseBytes=git(['show',BASE+':'+relative]);assert.equal(normalized(rootBytes),normalized(baseBytes),'Common current ROOT must equal accepted BASE');authority={kind:'fresh-before-and-accepted-base',sha256:row.sha256};}
  else{const row=result.records.find(r=>r.path===relative);assert.ok(row);assert.equal(sha(rootBytes),row.afterSha256,'Current ROOT must equal reviewed sync-v1 after bytes');authority={kind:'reviewed-sync-v1-result',sha256:row.afterSha256,resultSha256:V1_RESULT_SHA};}
  const diff=normalized(git(['diff','--no-index','--no-ext-diff','--no-color','--no-renames','--unified=3','--',rootFile,wtFile],true));
  assert.ok(diff.startsWith('diff --git '),'Every fixed follow-up path must have a reviewed change '+relative);
  assert.ok(fs.readFileSync(rootFile).equals(rootBytes)&&fs.readFileSync(wtFile).equals(wtBytes),'Source changed during no-index diff');
  const next=project(rootBytes,rootBytes,wtBytes,diff);assert.notEqual(sha(next.bytes),sha(rootBytes));
  records.push({path:relative,authority,rootBeforeSha256:sha(rootBytes),worktreeSha256:sha(wtBytes),rootAfterSha256:sha(next.bytes),
   beforeBytes:rootBytes.length,afterBytes:next.bytes.length,normalizedAfterSha256:sha(normalized(next.bytes)),hunks:next.hunks,
   unchangedLinesPreserved:next.unchangedLinesPreserved,addedLines:next.addedLines,removedLines:next.removedLines,addedEol:next.addedEol,diff});
  outputs.push({relative,rootFile,rootBytes,wtBytes,afterBytes:next.bytes});
 }
 return {proposal:{schema:'m43-1-presentation-followup-sync-v3',base:BASE,branch:BRANCH,root:ROOT,worktree:WT,
  beforeInventorySha256:sha(beforeBytes),v1ResultSha256:V1_RESULT_SHA,v1ProposalSha256:V1_PROPOSAL_SHA,
  unusedV2Proposal:{file:'source-sync-proposal-v2.json',sha256:UNUSED_V2_PROPOSAL_SHA,applied:false,reason:'Final scoped reference instruction typography changed the helper hash after v2 preview.'},
  physicalCheck:physicalCheck(),records,dbAccess:false,configWrites:false,routeAdded:false},outputs,
  totalPaths:[...JSON.parse(v1ProposalBytes).records.map(r=>r.path),COMMON]};
}
function preview(name){assert.equal(name,PROPOSAL);const proposalFile=path.join(PRIVATE,PROPOSAL),allowFile=path.join(PRIVATE,'source-allowlist-v3.json');assert.ok(!fs.existsSync(proposalFile)&&!fs.existsSync(allowFile));
 const current=build();assert.equal(new Set(current.totalPaths).size,10);
 exclusive(proposalFile,JSON.stringify(current.proposal,null,2)+'\n');exclusive(allowFile,JSON.stringify({root:current.totalPaths,worktree:current.totalPaths},null,2)+'\n');
 console.log(JSON.stringify({pass:true,mode:'preview',file:proposalFile,sha256:sha(fs.readFileSync(proposalFile)),files:PATHS.length,totalAllowlistedFiles:10,rootApplicationWrites:false,dbAccess:false}));}
function apply(name,digest,backupName){assert.equal(name,PROPOSAL);assert.equal(backupName,BACKUP);assert.match(digest,/^[a-f0-9]{64}$/u);
 const proposalBytes=fs.readFileSync(path.join(PRIVATE,PROPOSAL));assert.equal(sha(proposalBytes),digest,'Exact reviewed v3 proposal SHA required');
 const accepted=JSON.parse(proposalBytes),current=build();
 for(const key of ['schema','base','branch','root','worktree','beforeInventorySha256','v1ResultSha256','v1ProposalSha256','unusedV2Proposal','records','dbAccess','configWrites','routeAdded'])assert.deepEqual(current.proposal[key],accepted[key],'Stale/replaced follow-up '+key);
 for(const key of ['hashes','effectiveLocalVhostLine','effectiveSemanticSha256'])assert.deepEqual(current.proposal.physicalCheck[key],accepted.physicalCheck[key],'Reviewed physical binding '+key);
 const backupDir=path.join(PRIVATE,BACKUP);assert.ok(!fs.existsSync(backupDir));fs.mkdirSync(backupDir);
 const manifest={schema:'m43-1-presentation-source-backup-v3',at:new Date().toISOString(),proposal:PROPOSAL,proposalSha256:digest,v1ResultSha256:V1_RESULT_SHA,
  records:accepted.records.map(r=>({path:r.path,beforeSha256:r.rootBeforeSha256,afterSha256:r.rootAfterSha256}))};
 for(const o of current.outputs){const destination=path.join(backupDir,o.relative);fs.mkdirSync(path.dirname(destination),{recursive:true});exclusive(destination,o.rootBytes);assert.equal(sha(fs.readFileSync(destination)),sha(o.rootBytes));}
 exclusive(path.join(backupDir,'manifest.json'),JSON.stringify(manifest,null,2)+'\n');
 for(const o of current.outputs){assert.ok(fs.readFileSync(o.rootFile).equals(o.rootBytes));assert.ok(fs.readFileSync(ordinary(WT,o.relative)).equals(o.wtBytes));
  o.staged=path.join(backupDir,'_staged',o.relative);fs.mkdirSync(path.dirname(o.staged),{recursive:true});exclusive(o.staged,o.afterBytes);}
 const written=[];
 try{for(const o of current.outputs){assert.ok(fs.readFileSync(o.rootFile).equals(o.rootBytes),'Concurrent ROOT edit');fs.renameSync(o.staged,o.rootFile);written.push(o);assert.ok(fs.readFileSync(o.rootFile).equals(o.afterBytes));}}
 catch(error){for(const o of written.reverse()){assert.ok(fs.readFileSync(o.rootFile).equals(o.afterBytes),'Refuse to overwrite concurrent foreign edit during rollback');fs.writeFileSync(o.rootFile,o.rootBytes);}throw error;}
 for(const o of current.outputs){assert.ok(fs.readFileSync(o.rootFile).equals(o.afterBytes));assert.equal(normalized(fs.readFileSync(o.rootFile)),normalized(o.wtBytes));}
 const result={schema:'m43-1-presentation-sync-result-v3',at:new Date().toISOString(),pass:true,proposal:PROPOSAL,proposalSha256:digest,backup:BACKUP,v1ResultSha256:V1_RESULT_SHA,files:PATHS.length,
  records:accepted.records.map(r=>({path:r.path,beforeSha256:r.rootBeforeSha256,afterSha256:r.rootAfterSha256,unchangedLinesPreserved:r.unchangedLinesPreserved})),
  rootApplicationWrites:true,dbAccess:false,configWrites:false,routeAdded:false};
 exclusive(path.join(backupDir,'result.json'),JSON.stringify(result,null,2)+'\n');console.log(JSON.stringify({pass:true,mode:'apply',files:PATHS.length,backup:backupDir,resultSha256:sha(fs.readFileSync(path.join(backupDir,'result.json'))),dbAccess:false}));}
const args=process.argv.slice(2);
if(args[0]==='--preview'){assert.equal(args.length,2);preview(args[1]);}
else if(args[0]==='--apply'){assert.equal(args.length,6);assert.equal(args[2],'--sha256');assert.equal(args[4],'--backup');apply(args[1],args[3],args[5]);}
else throw new Error('Use --preview source-sync-proposal-v3.json or --apply source-sync-proposal-v3.json --sha256 <reviewed SHA> --backup source-sync-backup-v3');
