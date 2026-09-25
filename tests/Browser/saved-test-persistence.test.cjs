// Execute the real persistence functions in a deterministic, isolated JS environment.
const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const vm=require('node:vm');
const source=fs.readFileSync(path.join(__dirname,'../../resources/views/components/saved-test-js-helpers.blade.php'),'utf8');
const persistence=source.slice(source.indexOf('function cloneState(data)'),source.lastIndexOf('</script>'));
function fixture(fetchImplementation){
 const store=()=>{const values=new Map();return {getItem:key=>values.get(key)||null,setItem:(key,value)=>values.set(key,value),removeItem:key=>values.delete(key)}};
 const timers=new Map();let serial=0;const calls=[];const listeners={};
 const window={localStorage:store(),sessionStorage:store(),__INITIAL_JS_TEST_QUESTIONS__:[{id:1,uuid:'fixture',question:'Canonical question'}],JS_TEST_PERSISTENCE:{storageKey:'owned-state',storageKeys:['owned-state'],endpoint:'/test/fixture/state',mode:'saved-test-js-v2',token:'isolated-placeholder',saved:null},addEventListener:(name,fn)=>listeners[name]=fn};
 const navigation={reloads:0,scroll:null,alerts:[],errors:[]};
 Object.assign(window,{history:{scrollRestoration:'auto'},scrollTo:position=>navigation.scroll=position,location:{href:'http://gramlyze.loc/test/fixture/step?source=theory',reload:()=>navigation.reloads++},alert:message=>navigation.alerts.push(message)});
 const context=vm.createContext({window,console:{...console,error:error=>navigation.errors.push(error.message)},testUi:key=>key,Date,URL,setTimeout:fn=>{timers.set(++serial,fn);return serial},clearTimeout:id=>timers.delete(id),fetch:async(url,options)=>{const value=JSON.parse(options.body);calls.push(value);return fetchImplementation?fetchImplementation(value):{status:204,ok:true,json:()=>{throw Error('Must not parse a 204')}}},getTechnicalQuestions:()=>window.__INITIAL_JS_TEST_QUESTIONS__});
 context.EnglishAnswerVariants=require('./load-answer-variants.cjs');
 vm.runInContext(persistence,context);
 const evaluate=expression=>vm.runInContext(expression,context);
 return {window,calls,listeners,context,evaluate,navigation,timers,async drain(){for(const [id,fn] of [...timers]){timers.delete(id);fn()}await evaluate('JS_TEST_SAVE_QUEUE')},persist(value,immediate=false){context.input=value;evaluate(`persistState(input, ${immediate})`)}};
}
const state=n=>({items:[{uuid:'fixture',chosen:[String(n)]}],answered:n,correct:n,activeCardIdx:n});
test('204 is not parsed as JSON; payload is a detached snapshot and local copy is persisted',async()=>{
 const f=fixture(),value=state(1);f.persist(value,true);value.answered=99;await f.drain();
 assert.equal(f.calls[0].state.answered,1);assert.equal(f.calls[0].state.__meta.started,true);
 const local=JSON.parse(f.window.localStorage.getItem('owned-state')).state;
 assert.equal(local.answered,1);assert.equal(local.__meta.question_data,undefined);
});
test('rapid debounced changes send only the latest pending snapshot',async()=>{
 const f=fixture();f.persist(state(1),true);await f.drain();
 f.persist(state(2));f.persist(state(3));f.persist(state(4));await f.drain();
 assert.deepEqual(f.calls.map(c=>c.state.answered),[1,4]);
});
test('a slow earlier save cannot overtake the later queued snapshot',async()=>{
 let release;const hold=new Promise(resolve=>release=resolve);let active=0,maxActive=0;
 const f=fixture(async value=>{active++;maxActive=Math.max(active,maxActive);if(value.state.answered===1)await hold;active--;return {status:204,ok:true}});
 f.persist(state(1),true);await new Promise(resolve=>setImmediate(resolve));f.persist(state(2),true);
 assert.equal(f.calls.length,1);release();await f.drain();
 assert.deepEqual(f.calls.map(c=>c.state.answered),[1,2]);assert.equal(maxActive,1);
});
test('one rejected save does not deadlock the queue or lose the newest local snapshot',async()=>{
 let first=true;const f=fixture(async()=>{if(first){first=false;throw new TypeError('controlled failure')}return {status:204,ok:true}});
 f.persist(state(1),true);await f.drain();f.persist(state(2));await f.drain();
 assert.deepEqual(f.calls.map(c=>c.state.answered),[1,2]);assert.equal(JSON.parse(f.window.localStorage.getItem('owned-state')).state.answered,2);
});
test('server snapshot restores without local storage, including position and order',()=>{
 const f=fixture();f.window.JS_TEST_PERSISTENCE.saved={...state(2),__meta:{started:true,saved_at:'2026-01-01T00:00:00Z'}};
 const restored=f.evaluate('getSavedState()');assert.equal(restored.answered,2);assert.equal(restored.activeCardIdx,2);assert.equal(restored.items[0].uuid,'fixture');assert.equal(restored.items[0].question,'Canonical question');
});
test('a newer local snapshot wins over the older server state and pagehide keeps it locally',async()=>{
 const f=fixture();f.window.JS_TEST_PERSISTENCE.saved={...state(1),__meta:{started:true,saved_at:'2020-01-01T00:00:00Z'}};
 f.persist(state(3),true);await f.drain();assert.equal(f.evaluate('getSavedState()').answered,3);
 f.window.localStorage.removeItem('owned-state');f.listeners.pagehide();
 assert.equal(JSON.parse(f.window.localStorage.getItem('owned-state')).state.answered,3);
 assert.equal(f.calls.length,1);
});

test('restart cancels debounce, resets progress and reloads the same URL at the top',async()=>{
 const f=fixture();f.persist(state(1),true);await f.drain();f.persist(state(2));
 f.window.JS_TEST_PERSISTENCE.storageKeys.push('legacy-owned-state');
 for(const storage of [f.window.localStorage,f.window.sessionStorage]){
  storage.setItem('legacy-owned-state','old');storage.setItem('another-test','keep');
 }
 await f.evaluate('restartJsTest(() => { throw Error("Do not rebuild in place"); })');
 assert.equal(f.timers.size,0);
 await f.drain();
 assert.deepEqual(f.calls.map(c=>c.state?.answered??null),[1,null]);
 assert.equal(f.navigation.reloads,1);assert.equal(f.navigation.scroll.top,0);
 assert.equal(f.window.history.scrollRestoration,'manual');
 assert.equal(f.window.location.href,'http://gramlyze.loc/test/fixture/step?source=theory');
 assert.equal(f.window.JS_TEST_PERSISTENCE.saved,null);
 f.listeners.pagehide();f.persist(state(3),true);await f.drain();
 for(const storage of [f.window.localStorage,f.window.sessionStorage]){
  assert.equal(storage.getItem('owned-state'),null);assert.equal(storage.getItem('legacy-owned-state'),null);
  assert.equal(storage.getItem('another-test'),'keep');
 }
 assert.equal(f.calls.length,2);assert.equal(f.navigation.errors.length,0);
});

test('restart waits for in-flight saves and ignores duplicate clicks and late UI saves',async()=>{
 let release;const hold=new Promise(resolve=>release=resolve);
 const f=fixture(async value=>{if(value.state)await hold;return {ok:true,status:204}});
 f.persist(state(1),true);await new Promise(resolve=>setImmediate(resolve));
 const restarting=f.evaluate('restartJsTest(() => {})');
 await f.evaluate('restartJsTest(() => {})');f.persist(state(2),true);
 assert.equal(f.calls.length,1);assert.equal(f.navigation.reloads,0);
 release();await restarting;
 assert.deepEqual(f.calls.map(c=>c.state?.answered??null),[1,null]);
 assert.equal(f.navigation.reloads,1);
});

for(const failure of ['http','network','redirect']){
 test(`a ${failure} reset failure keeps progress and allows retry without reloading`,async()=>{
  let fail=true;
  const f=fixture(async value=>{
   if(value.state===null&&fail){
    if(failure==='network')throw new TypeError('Controlled offline failure');
    return {ok:failure==='redirect',status:failure==='redirect'?200:419,redirected:failure==='redirect'};
   }
   return {ok:true,status:204};
  });
  f.persist(state(2),true);await f.drain();
  f.context.button={disabled:false,classList:{add(){},remove(){}}};
  await f.evaluate('restartJsTest(() => {}, { button })');
  assert.equal(f.navigation.reloads,0);assert.equal(f.context.button.disabled,false);
  assert.equal(f.window.JS_TEST_PERSISTENCE.saved.answered,2);
  assert.equal(JSON.parse(f.window.localStorage.getItem('owned-state')).state.answered,2);
  assert.deepEqual(f.navigation.alerts,['status.restart_failed']);
  f.persist(state(3),true);await f.drain();assert.equal(f.calls.at(-1).state.answered,3);
  fail=false;await f.evaluate('restartJsTest(() => {}, { button })');
  assert.equal(f.navigation.reloads,1);assert.equal(f.context.button.disabled,true);
 });
}
