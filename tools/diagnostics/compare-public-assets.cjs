// Aggregate local evidence only; no HTTP, cookies, page bodies or secrets.
const fs=require('node:fs');
const path=require('node:path');
const root=path.resolve('storage/app/seo-m3-local');
const load=name=>JSON.parse(fs.readFileSync(path.join(root,name)));
const before=process.argv[2]||'baseline';
const after=process.argv[3]||'built';
const key=r=>[r.name,!!r.mobile,r.theme||'light',r.background||'blue'].join('|');
const baseline=load(before+'-visual.json'),built=load(after+'-visual.json');
const visual=baseline.map(a=>{
 const b=built.find(r=>key(r)===key(a));if(!b)throw Error('Missing pair: '+key(a));
 const differences=[];let aligned=0;
 for(let i=0;i<Math.min(a.computed.length,b.computed.length);i++){
  const x=a.computed[i],y=b.computed[i];
  if(x.classes!==y.classes||x.tag!==y.tag){differences.push({index:i,kind:'structure'});continue}aligned++;
  for(const k of Object.keys(x.style))if(x.style[k]!==y.style[k])differences.push({index:i,kind:k,before:x.style[k],after:y.style[k]});
  if(Math.abs(x.width-y.width)>.1||Math.abs(x.height-y.height)>.1)differences.push({index:i,kind:'size',before:[x.width,x.height],after:[y.width,y.height]});
 }
 return {scenario:key(a),aligned,differences,overflow:[a.cold.overflow,b.cold.overflow],console:[a.console.length,b.console.length]};
});
const stats=values=>{
 const sorted=values.filter(Number.isFinite).sort((a,b)=>a-b);
 return {median:sorted[Math.floor(sorted.length/2)],min:sorted[0],max:sorted.at(-1),samples:sorted.length};
};
const perf=[];
for(const phase of ['baseline-final','built-final']){
 const file=path.join(root,phase+'-perf.json');if(!fs.existsSync(file))continue;
 const rows=load(phase+'-perf.json');
 for(const name of [...new Set(rows.map(r=>r.name))])for(const cache of ['cold','repeat']){
  const measured=rows.filter(r=>r.name===name).map(r=>r[cache]);
  const metrics={};
  for(const metric of ['htmlBytes','inlineStyleBytes','inlineJsBytes','fcp','lcp','cls','longTasks'])metrics[metric]=stats(measured.map(r=>r[metric]));
  for(const type of ['Document','Stylesheet','Script'])for(const bytes of ['decodedBytes','wireBytes']){
   metrics[type+bytes]=stats(measured.map(r=>r.network.filter(n=>n.type===type).reduce((n,v)=>n+(v[bytes]||0),0)));
  }
  metrics.responses=stats(measured.map(r=>r.network.length));
  metrics.wireBytesAll=stats(measured.map(r=>r.network.reduce((n,v)=>n+(v.wireBytes||0),0)));
  metrics.scriptMs=stats(measured.map(r=>r.runtime.ScriptDuration*1000));
  metrics.layoutMs=stats(measured.map(r=>r.runtime.LayoutDuration*1000));
  metrics.styleMs=stats(measured.map(r=>r.runtime.RecalcStyleDuration*1000));
  perf.push({phase,name,cache,metrics});
 }
}
fs.writeFileSync(path.join(root,'comparison.json'),JSON.stringify({visual,perf},null,2));
console.log(JSON.stringify({visual:visual.map(r=>({...r,differences:r.differences.length})),perf},null,2));
