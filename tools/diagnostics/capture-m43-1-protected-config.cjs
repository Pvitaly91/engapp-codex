'use strict';
// Hash-only immutable application/config/dependency files excluded from the broader learner source inventory.
const fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const ROOT='D:/DEV/htdocs/gramlyze.loc',WT='C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
const PRIVATE=path.join(ROOT,'storage/app/seo-m43-1-local');
const sha=v=>crypto.createHash('sha256').update(v).digest('hex');
const fixed=['composer.json','composer.lock','package.json','package-lock.json','vite.config.js','tailwind.config.js','phpunit.xml','.gitignore','.gitattributes','public/index.php'];
const names=new Set(fixed);
function walk(base,relative){const absolute=path.join(base,relative);if(!fs.existsSync(absolute))return;
 const s=fs.lstatSync(absolute);assert.ok(!s.isSymbolicLink());
 if(s.isDirectory()){for(const name of fs.readdirSync(absolute).sort()){if(['cache','caches'].includes(name))continue;walk(base,relative+'/'+name);}}
 else if(s.isFile())names.add(relative);}
for(const base of [ROOT,WT])for(const directory of ['config','bootstrap','scripts','.github'])walk(base,directory);
const [label,beforeName]=process.argv.slice(2);assert.match(label,/^(?:before|after|final)-v[1-9][0-9]*$/u);
const rows={};for(const [key,base] of [['root',ROOT],['worktree',WT]])rows[key]=[...names].sort().map(relative=>{
 const absolute=path.join(base,relative);return fs.existsSync(absolute)?{path:relative,exists:true,sha256:sha(fs.readFileSync(absolute))}:{path:relative,exists:false,sha256:null};});
const result={schema:'m43-1-protected-config-v1',at:new Date().toISOString(),roots:{root:ROOT,worktree:WT},rows,pass:true,dbAccess:false,configWrites:false};
if(!label.startsWith('before')){assert.match(beforeName,/^config-before-v[1-9][0-9]*\.json$/u);const before=JSON.parse(fs.readFileSync(path.join(PRIVATE,beforeName),'utf8'));assert.deepEqual(result.rows,before.rows,'Protected config/dependencies unchanged');}
const file=path.join(PRIVATE,'config-'+label+'.json');fs.mkdirSync(PRIVATE,{recursive:true});fs.writeFileSync(file,JSON.stringify(result,null,2)+'\n',{flag:'wx'});
console.log(JSON.stringify({file,sha256:sha(fs.readFileSync(file)),pass:true,paths:names.size,dbAccess:false,configWrites:false}));
