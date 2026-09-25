// A transport event alone cannot prove whether a learner snapshot was persisted.
// Retain raw events; this conservative label is additional evidence, not filtering.
function classifyStateRequest({status,fetchResolved,fetchRejected,error,navigationBeforeFailure=false,serverRestored=false}) {
 if(fetchRejected || (status && (status<200 || status>=300))) return 'save-request-failed';
 if(error && error!=='net::ERR_ABORTED') return 'save-request-failed';
 if(error==='net::ERR_ABORTED') {
  if(navigationBeforeFailure) return 'navigation-interrupted-unconfirmed';
  if(status===204 && fetchResolved===true) return serverRestored
   ? 'server-restored-despite-network-abort'
   : 'fetch-completed-204-restoration-not-yet-proven';
  return 'aborted-unconfirmed';
 }
 if(fetchResolved===true && status>=200 && status<300) return serverRestored
  ? 'server-restored' : 'fetch-completed-restoration-not-yet-proven';
 return 'unconfirmed';
}
module.exports={classifyStateRequest};
