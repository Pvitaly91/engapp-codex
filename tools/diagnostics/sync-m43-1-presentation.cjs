'use strict';
// Explicit preview -> reviewed SHA -> apply. Files/Git only: never boot Laravel or connect to a database.
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const {execFileSync}=require('node:child_process');
const ROOT='D:/DEV/htdocs/gramlyze.loc',WT='C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
const BASE='477897543e91c02dff90747b439cd4ac939bfc6a',BRANCH='codex/seo-m43-ppc-reference-design';
const PRIVATE=path.join(ROOT,'storage/app/seo-m43-1-local');
const PHYSICAL_FILES=Object.freeze(['C:/Program Files/xampp/apache/conf/httpd.conf','C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
 'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf',ROOT+'/public/index.php']);
const PATHS=Object.freeze([
 'app/Support/M43NativeHtml.php',
 'resources/views/engram/theory/blocks-v3/m43-native-styles.blade.php',
 'resources/views/engram/theory/blocks-v3/m43-native-mistake.blade.php',
 'resources/views/engram/theory/blocks-v3/m43-native-section.blade.php',
 'resources/views/engram/theory/blocks-v3/m43-native-table.blade.php',
 'resources/views/engram/theory/blocks-v3/m43-practice-ui.blade.php',
 'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
 'resources/views/theory/partials/point-detail-fragment.blade.php',
 'resources/views/engram/theory/widgets/lesson-rule-cards.blade.php',
]);
const sha=bytes=>crypto.createHash('sha256').update(bytes).digest('hex');
function git(args){return execFileSync('git',['-c','safe.directory='+WT,'-c','core.bare=false','-C',WT,...args],{timeout:30000,maxBuffer:8*1024*1024,stdio:['ignore','pipe','pipe']});}
function decode(bytes){const text=bytes.toString('utf8');assert.ok(Buffer.from(text,'utf8').equals(bytes),'Only lossless UTF-8 source permitted');assert.ok(!text.replaceAll('\r\n','\n').includes('\r'),'Bare CR source rejected');return text;}
const normalized=bytes=>decode(bytes).replaceAll('\r\n','\n');
function ordinary(base,relative){assert.ok(PATHS.includes(relative));const p=path.join(base,relative);const s=fs.lstatSync(p);assert.ok(s.isFile()&&!s.isSymbolicLink(),'Ordinary fixed source required '+p);return p;}
function privateName(name,kind){const pattern=kind==='proposal'?/^source-sync-proposal-v[1-9][0-9]*\.json$/u:/^source-sync-backup-v[1-9][0-9]*$/u;assert.match(name,pattern);return path.join(PRIVATE,name);}
function lines(bytes){return [...decode(bytes).matchAll(/[^\n]*(?:\n|$)/gu)].map(m=>m[0]).filter(Boolean).map(s=>s.endsWith('\r\n')?{text:s.slice(0,-2),eol:'\r\n'}:s.endsWith('\n')?{text:s.slice(0,-1),eol:'\n'}:{text:s,eol:''});}
function parseHunks(diff){const rows=diff.split('\n');const hunks=[];let current=null;
 for(const row of rows){const header=row.match(/^@@ -(\d+)(?:,(\d+))? \+(\d+)(?:,(\d+))? @@/u);
  if(header){current={oldStart:Number(header[1]),oldCount:Number(header[2]??1),newStart:Number(header[3]),newCount:Number(header[4]??1),lines:[]};hunks.push(current);continue;}
  if(!current)continue;
  if(row==='\\ No newline at end of file'){assert.ok(current.lines.length);current.lines.at(-1).noNewline=true;continue;}
  if(/^[ +\-]/u.test(row)){current.lines.push({kind:row[0],text:row.slice(1),noNewline:false});continue;}
  assert.equal(row,'','Unexpected unified diff line');
 }
 for(const h of hunks){assert.equal(h.lines.filter(l=>l.kind!=='+').length,h.oldCount);assert.equal(h.lines.filter(l=>l.kind!=='-').length,h.newCount);}
 return hunks;
}
function project(baseBytes,rootBytes,wtBytes,diff){
 assert.equal(normalized(rootBytes),normalized(baseBytes),'Served ROOT differs from accepted BASE; exact conflict review required');
 const original=lines(rootBytes),hunks=parseHunks(diff),output=[];let cursor=0,unchanged=0,added=0,removed=0;
 const endings=original.map(l=>l.eol).filter(Boolean),crlf=endings.filter(s=>s==='\r\n').length;
 const addedEol=crlf>endings.length/2?'\r\n':'\n';
 for(const h of hunks){const start=h.oldCount===0?h.oldStart:h.oldStart-1;assert.ok(start>=cursor&&start<=original.length);
  const old=h.lines.filter(l=>l.kind!=='+').map(l=>l.text);
  assert.deepEqual(original.slice(start,start+h.oldCount).map(l=>l.text),old,'Exact accepted hunk context');
  if(old.length){let occurrences=0;for(let p=0;p<=original.length-old.length;p++)if(old.every((s,i)=>original[p+i].text===s))occurrences++;
   assert.equal(occurrences,1,'Ambiguous accepted hunk context rejected');}
  for(const line of original.slice(cursor,start)){output.push(line);unchanged++;}
  let position=start;
  for(const l of h.lines){if(l.kind===' '){const current=original[position++];assert.equal(current.text,l.text);output.push(current);unchanged++;}
   else if(l.kind==='-'){assert.equal(original[position++].text,l.text);removed++;}
   else{output.push({text:l.text,eol:l.noNewline?'':addedEol});added++;}}
  assert.equal(position,start+h.oldCount);cursor=position;
 }
 for(const line of original.slice(cursor)){output.push(line);unchanged++;}
 const bytes=Buffer.from(output.map(l=>l.text+l.eol).join(''),'utf8');
 assert.equal(normalized(bytes),normalized(wtBytes),'Complete projected ROOT must normalize exactly to current worktree');
 return {bytes,hunks:hunks.length,unchangedLinesPreserved:unchanged,addedLines:added,removedLines:removed,addedEol:addedEol==='\r\n'?'CRLF':'LF'};
}
function physicalCheck(){
 const proofPath=path.join(PRIVATE,'physical-target-before-v1.json'),proof=JSON.parse(fs.readFileSync(proofPath,'utf8'));
 assert.equal(proof.documentRoot,ROOT+'/public');assert.equal(proof.localHost,'gramlyze.loc');assert.equal(proof.scheme,'http');
 assert.deepEqual(Object.keys(proof.hashes).sort(),[...PHYSICAL_FILES].sort(),'Exact physical fingerprint paths');
 const currentHashes={};for(const [file,digest] of Object.entries(proof.hashes)){currentHashes[file]=sha(fs.readFileSync(file));assert.equal(currentHashes[file],digest,'Physical config/public root fingerprint changed '+file);}
 const effective=execFileSync('C:/Program Files/xampp/apache/bin/httpd.exe',['-S'],{encoding:'utf8',timeout:15000,stdio:['ignore','pipe','pipe']});
 const localVhost=effective.match(/port 80 namevhost gramlyze\.loc \((.+):(\d+)\)/u);
 assert.ok(localVhost,'Current exact local HTTP vhost required');assert.equal(localVhost[1],proof.vhostSource);assert.equal(Number(localVhost[2]),proof.vhostLine);
 const script='$ErrorActionPreference="Stop"; $ports=@(Get-NetTCPConnection -LocalPort 80 -State Listen | Select-Object LocalAddress,LocalPort,OwningProcess); '+
  '$procs=@(Get-CimInstance Win32_Process -Filter "Name = \'httpd.exe\'" | Select-Object Name,ProcessId); '+
  '$dns=@(Resolve-DnsName gramlyze.loc -Type A | Select-Object Name,IPAddress); @{ports=$ports;processes=$procs;dns=$dns}|ConvertTo-Json -Depth 5 -Compress';
 const runtime=JSON.parse(execFileSync('powershell.exe',['-NoProfile','-NonInteractive','-Command',script],{encoding:'utf8',timeout:20000,stdio:['ignore','pipe','pipe']}));
 assert.ok(runtime.dns.some(r=>r.Name==='gramlyze.loc'&&r.IPAddress==='127.0.0.1'));
 assert.ok(runtime.ports.some(p=>runtime.processes.some(proc=>proc.Name==='httpd.exe'&&proc.ProcessId===p.OwningProcess)));
 return {at:new Date().toISOString(),beforeProofSha256:sha(fs.readFileSync(proofPath)),hashes:currentHashes,effectiveVhostRawSha256:sha(effective),
  effectiveLocalVhostLine:localVhost[0],effectiveSemanticSha256:sha(effective.split(/\r?\n/u).map(s=>s.trim()).filter(Boolean).sort().join('\n')),runtime,
  rawDumpOrderIsNotAnInvariant:true,qaNote:'Apache -S may print Mutex registrations in a different order. Exact config/public hashes and local namevhost/source line remain strictly bound.',
  documentRoot:ROOT+'/public',configWrites:false,routeAdded:false,dbAccess:false};
}
function build(){
 assert.equal(process.platform,'win32');assert.equal(git(['branch','--show-current']).toString('utf8').trim(),BRANCH);
 assert.equal(git(['rev-parse','HEAD']).toString('utf8').trim(),BASE,'Sync only during reviewed uncommitted M43.1 phase');
 git(['merge-base','--is-ancestor',BASE,'HEAD']);
 const beforePath=path.join(PRIVATE,'source-before-v1.json'),beforeBytes=fs.readFileSync(beforePath),before=JSON.parse(beforeBytes);
 assert.equal(before.schema,'m43-1-source-inventory-v1');assert.deepEqual(before.roots,{root:ROOT,worktree:WT});
 const records=[],outputs=[];
 for(const relative of PATHS){const rootFile=ordinary(ROOT,relative),wtFile=ordinary(WT,relative),rootBytes=fs.readFileSync(rootFile),wtBytes=fs.readFileSync(wtFile);
  const saved=before.sources.root.find(r=>r.path===relative);assert.ok(saved?.exists,'Fresh ROOT BEFORE file required '+relative);
  assert.equal(sha(rootBytes),saved.sha256,'ROOT no longer equals fresh BEFORE '+relative);
  const baseBytes=git(['show',BASE+':'+relative]);const diff=normalized(git(['diff','--no-ext-diff','--no-color','--no-renames','--unified=3',BASE,'--',relative]));
  assert.ok(diff.startsWith('diff --git '),'Each fixed path must have a reviewed change '+relative);
  const projected=project(baseBytes,rootBytes,wtBytes,diff);assert.notEqual(sha(rootBytes),sha(projected.bytes),'No-op fixed path rejected');
  records.push({path:relative,baseSha256:sha(baseBytes),rootBeforeSha256:sha(rootBytes),worktreeSha256:sha(wtBytes),rootAfterSha256:sha(projected.bytes),
   normalizedAfterSha256:sha(normalized(projected.bytes)),beforeBytes:rootBytes.length,afterBytes:projected.bytes.length,hunks:projected.hunks,
   unchangedLinesPreserved:projected.unchangedLinesPreserved,addedLines:projected.addedLines,removedLines:projected.removedLines,addedEol:projected.addedEol,diff});
  outputs.push({relative,rootFile,rootBytes,wtBytes,afterBytes:projected.bytes});
 }
 return {proposal:{schema:'m43-1-presentation-sync-v1',base:BASE,branch:BRANCH,root:ROOT,worktree:WT,beforeInventorySha256:sha(beforeBytes),
  physicalCheck:physicalCheck(),records,dbAccess:false,configWrites:false,routeAdded:false},outputs};
}
function exclusive(file,bytes){assert.ok(!fs.existsSync(file),'Exclusive artifact already exists '+file);fs.writeFileSync(file,bytes,{flag:'wx'});assert.ok(fs.readFileSync(file).equals(Buffer.from(bytes)));}
function preview(name){const destination=privateName(name,'proposal');assert.ok(!fs.existsSync(destination));const {proposal}=build();
 const allowlistFile=path.join(PRIVATE,name.replace('source-sync-proposal-','source-allowlist-'));assert.ok(!fs.existsSync(allowlistFile));
 exclusive(destination,JSON.stringify(proposal,null,2)+'\n');exclusive(allowlistFile,JSON.stringify({root:[...PATHS],worktree:[...PATHS]},null,2)+'\n');
 console.log(JSON.stringify({pass:true,mode:'preview',file:destination,sha256:sha(fs.readFileSync(destination)),allowlist:allowlistFile,files:proposal.records.length,rootApplicationWrites:false,dbAccess:false}));}
function apply(name,digest,backupName){assert.match(digest,/^[a-f0-9]{64}$/u);const proposalFile=privateName(name,'proposal'),proposalBytes=fs.readFileSync(proposalFile);
 assert.equal(sha(proposalBytes),digest,'Reviewed proposal SHA required');const accepted=JSON.parse(proposalBytes),current=build();
 for(const key of ['schema','base','branch','root','worktree','beforeInventorySha256','records','dbAccess','configWrites','routeAdded'])assert.deepEqual(current.proposal[key],accepted[key],'Stale/replaced proposal '+key);
 assert.deepEqual(current.proposal.physicalCheck.hashes,accepted.physicalCheck.hashes,'Fresh physical control fingerprints');
 assert.equal(current.proposal.physicalCheck.effectiveLocalVhostLine,accepted.physicalCheck.effectiveLocalVhostLine,'Reviewed exact local vhost binding');
 assert.equal(current.proposal.physicalCheck.effectiveSemanticSha256,accepted.physicalCheck.effectiveSemanticSha256,'Reviewed effective vhost/config values, ignoring line order');
 const backupDir=privateName(backupName,'backup');assert.ok(!fs.existsSync(backupDir),'Exclusive fixed-scope source backup required');fs.mkdirSync(backupDir);
 const manifest={schema:'m43-1-presentation-source-backup-v1',at:new Date().toISOString(),proposal:name,proposalSha256:digest,base:BASE,records:accepted.records.map(r=>({path:r.path,beforeSha256:r.rootBeforeSha256,afterSha256:r.rootAfterSha256}))};
 // Back up every fixed-scope file and verify it before the first application write.
 for(const o of current.outputs){const destination=path.join(backupDir,o.relative);fs.mkdirSync(path.dirname(destination),{recursive:true});exclusive(destination,o.rootBytes);assert.equal(sha(fs.readFileSync(destination)),sha(o.rootBytes));}
 exclusive(path.join(backupDir,'manifest.json'),JSON.stringify(manifest,null,2)+'\n');
 for(const o of current.outputs){assert.equal(sha(fs.readFileSync(o.rootFile)),sha(o.rootBytes),'ROOT changed after full prevalidation');assert.equal(sha(fs.readFileSync(ordinary(WT,o.relative))),sha(o.wtBytes),'Worktree changed after full prevalidation');}
 // Stage complete next bytes on the same volume, outside application source. Each rename is atomic.
 for(const o of current.outputs){o.staged=path.join(backupDir,'_staged',o.relative);fs.mkdirSync(path.dirname(o.staged),{recursive:true});exclusive(o.staged,o.afterBytes);}
 const written=[];
 try{for(const o of current.outputs){assert.equal(sha(fs.readFileSync(o.rootFile)),sha(o.rootBytes),'Concurrent ROOT edit rejected');fs.renameSync(o.staged,o.rootFile);written.push(o);assert.ok(fs.readFileSync(o.rootFile).equals(o.afterBytes),'Exact synced bytes required');}}
 catch(error){for(const o of written.reverse()){assert.ok(fs.readFileSync(o.rootFile).equals(o.afterBytes),'Refuse to overwrite a concurrent foreign edit while rolling back');fs.writeFileSync(o.rootFile,o.rootBytes);}throw error;}
 for(const o of current.outputs){assert.ok(fs.readFileSync(o.rootFile).equals(o.afterBytes));assert.equal(normalized(fs.readFileSync(o.rootFile)),normalized(o.wtBytes));}
 const result={schema:'m43-1-presentation-sync-result-v1',at:new Date().toISOString(),pass:true,proposal:name,proposalSha256:digest,backup:backupName,
  files:accepted.records.length,records:accepted.records.map(r=>({path:r.path,beforeSha256:r.rootBeforeSha256,afterSha256:r.rootAfterSha256,unchangedLinesPreserved:r.unchangedLinesPreserved})),
  rootApplicationWrites:true,dbAccess:false,configWrites:false,routeAdded:false};
 exclusive(path.join(backupDir,'result.json'),JSON.stringify(result,null,2)+'\n');console.log(JSON.stringify({pass:true,mode:'apply',files:result.files,backup:backupDir,resultSha256:sha(fs.readFileSync(path.join(backupDir,'result.json'))),dbAccess:false}));}
if(require.main===module){
 const args=process.argv.slice(2);
 if(args[0]==='--preview'){assert.equal(args.length,2);preview(args[1]);}
 else if(args[0]==='--apply'){assert.equal(args.length,6);assert.equal(args[2],'--sha256');assert.equal(args[4],'--backup');apply(args[1],args[3],args[5]);}
 else throw new Error('Use --preview <source-sync-proposal-vN.json> or --apply <proposal> --sha256 <reviewed SHA> --backup <source-sync-backup-vN>');
}
module.exports={project,physicalCheck};
