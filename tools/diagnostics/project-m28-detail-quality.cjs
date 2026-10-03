'use strict';
// Mechanical, finite author projection. No word-count runtime heuristic or DB writes.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),crypto=require('node:crypto');
const root=path.resolve(__dirname,'../..');
const removals={
 'm27-c1-section-2':[3], 'm27-c2-section-1':[1],
 'm27-c2-section-4':[1,2,3,4], 'm27-c2-section-5':[1,2,3],
};
const files=['m27-m11-linking-words','m28-m12-emphasis-inversion'];
const inventory=[];
for(const file of files){
 const old=JSON.parse(fs.readFileSync(path.join(root,'database/content-patches',file+'.v1.json'),'utf8'));
 const next=structuredClone(old);next.version=2;
 for(const t of next.targets)for(const plan of t.plans)for(const [i,p]of plan.points.entries()){
  if(!p.detail)continue;
  const merge=file.startsWith('m28')||removals[plan.key]?.includes(i+1);
  inventory.push({slug:t.slug,key:plan.key,point:i+1,action:merge?'merge':'retain',detail:p.detail});
  if(merge){p.basic+='<br><br>'+p.detail;p.detail='';}
 }
 for(const [i,t]of next.targets.entries())assert.deepEqual(t.after,old.targets[i].after);
 const bytes=JSON.stringify(next,null,4)+'\n';
 if(process.argv.includes('--write'))fs.writeFileSync(path.join(root,'database/content-patches',file+'.v2.json'),bytes,{flag:'wx'});
 console.log(JSON.stringify({file,sha256:crypto.createHash('sha256').update(bytes).digest('hex'),counts:next.targets.map(t=>t.plans.reduce((n,p)=>n+p.points.filter(p=>p.detail).length,0))}));
}
console.log(JSON.stringify(inventory,null,2));
module.exports={removals};
