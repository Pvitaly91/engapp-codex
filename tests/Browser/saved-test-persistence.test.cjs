// Execute the real persistence functions in a deterministic, isolated JS environment.
const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const vm=require('node:vm');
const source=fs.readFileSync(path.join(__dirname,'../../resources/views/components/saved-test-js-helpers.blade.php'),'utf8');
const persistence=source.slice(source.indexOf('function cloneState(data)'),source.indexOf('async function restartJsTest('));
function fixture(fetchImplementation){
 const store=()=>{const values=new Map();return {getItem:key=>values.get(key)||null,setItem:(key,value)=>values.set(key,value),removeItem:key=>values.delete(key)}};
 const timers=new Map();let serial=0;const calls=[];const listeners={};
 const window={localStorage:store(),sessionStorage:store(),__INITIAL_JS_TEST_QUESTIONS__:[{id:1,uuid:'fixture',question:'Canonical question'}],JS_TEST_PERSISTENCE:{storageKey:'owned-state',storageKeys:['owned-state'],endpoint:'/test/fixture/state',mode:'saved-test-js-v2',token:'isolated-placeholder',saved:null},addEventListener:(name,fn)=>listeners[name]=fn};
 const context=vm.createContext({window,console,Date,URL,setTimeout:fn=>{timers.set(++serial,fn);return serial},clearTimeout:id=>timers.delete(id),fetch:async(url,options)=>{const value=JSON.parse(options.body);calls.push(value);return fetchImplementation?fetchImplementation(value):{status:204,ok:true,json:()=>{throw Error('Must not parse a 204')}}},getTechnicalQuestions:()=>window.__INITIAL_JS_TEST_QUESTIONS__});
 vm.runInContext(persistence,context);
 const evaluate=expression=>vm.runInContext(expression,context);
 return {window,calls,listeners,context,evaluate,async drain(){for(const [id,fn] of [...timers]){timers.delete(id);fn()}await evaluate('JS_TEST_SAVE_QUEUE')},persist(value,immediate=false){context.input=value;evaluate(`persistState(input, ${immediate})`)}};
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
