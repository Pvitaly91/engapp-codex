'use strict';
// Incorrect test inputs only: finite mechanical mutations of actual author
// answers. None is projected as teaching text, a candidate, or an added alias.
const changes={
    'past-q4a':[['take','took']], 'past-q4b':[['were waiting','was waiting']],
    'past-q5a':[['when the phone rang','before the phone rang']],
    'past-q6a':[['nine','eight']], 'past-q6b':[['opened','was opening']],
    'present-q2a':[['work','works']], 'present-q5a':[['usually ','']],
    'present-q5b':[['from home','at the shop']], 'present-q6b':[['eight','nine']],
    'perfect-q2a':[['Have you ever travelled','Did you ever travel']],
    'perfect-q2b':[['last summer','this summer']],
    'perfect-q4a':[['written','wrote'],['She has',"She's"]],
    'perfect-q4b':[['write','wrote']],
    'perfect-q6a':[['have sent','sent']], 'perfect-q6b':[['nine','ten']],
};
function semanticFixtures(target){
    const result=[];
    for(const [caseIndex,task]of target.data.cases.entries())for(const [controlIndex,control]of task.controls.entries()){
        if(control.kind!=='manual')continue;
        const add=(invalid,boundary)=>{
            if(!invalid||invalid===control.answer||(control.accepted||[]).includes(invalid))throw Error('M41 mutation is not a distinct incorrect author input');
            if(!result.some(row=>row.caseIndex===caseIndex&&row.controlIndex===controlIndex&&row.invalid===invalid))
                result.push({caseIndex,controlIndex,invalid,boundary});
        };
        add(control.tokens.slice(1).join(' '),'required first token group omitted');
        add(control.tokens.slice(0,-1).join(' '),'required last token group omitted');
        for(const [from,to]of changes[control.id]||[]){
            if(!control.answer.includes(from))throw Error('M41 mutation source fragment absent '+control.id);
            add(control.answer.replace(from,to),from+' → '+to);
        }
    }
    return result;
}
module.exports={semanticFixtures};
