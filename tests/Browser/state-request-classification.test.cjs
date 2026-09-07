const {test}=require('node:test');
const assert=require('node:assert/strict');
const {classifyStateRequest:classify}=require('../../tools/diagnostics/state-request-classification.cjs');
test('204 and resolved fetch do not alone prove persistence',()=>{
 assert.equal(classify({status:204,fetchResolved:true,error:'net::ERR_ABORTED'}),'fetch-completed-204-restoration-not-yet-proven');
});
test('independent server restoration can confirm a 204 despite the raw aborted event',()=>{
 assert.equal(classify({status:204,fetchResolved:true,error:'net::ERR_ABORTED',serverRestored:true}),'server-restored-despite-network-abort');
});
test('an abort without resolved fetch remains unconfirmed',()=>{
 assert.equal(classify({status:204,error:'net::ERR_ABORTED',serverRestored:true}),'aborted-unconfirmed');
});
test('a real rejected fetch and non-abort network failures are not hidden',()=>{
 assert.equal(classify({status:204,fetchRejected:true,error:'net::ERR_ABORTED'}),'save-request-failed');
 assert.equal(classify({error:'net::ERR_FAILED'}),'save-request-failed');
});
test('HTTP errors remain failures even when fetch resolves',()=>{
 for(const status of [419,422,500])assert.equal(classify({status,fetchResolved:true}),'save-request-failed');
});
test('navigation before an abort is not classified as a harmless 204',()=>{
 assert.equal(classify({status:204,fetchResolved:true,error:'net::ERR_ABORTED',navigationBeforeFailure:true}),'navigation-interrupted-unconfirmed');
});
