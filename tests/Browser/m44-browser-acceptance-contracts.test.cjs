'use strict';
const test=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path');
const qa=require('../../tools/diagnostics/seo-m44-local.cjs');

test('dry contract is file-only and binds the independently captured references',()=>{
    const original=global.fetch;global.fetch=()=>{throw Error('Network must not be reached by dry contract');};
    try{const result=qa.dryContract();assert.equal(result.noHTTP,true);assert.equal(result.noDB,true);
        assert.equal(result.tasks,18);assert.equal(result.controls,34);assert.equal(result.details,7);assert.equal(result.tokens,12);
        assert.equal(result.referenceSha256,'b7b5b01595f42d05a9a4657f333e06985bd04841ca7e3fe225b92889a35ebd50');
    }finally{global.fetch=original;}
});

test('live payload expectation preserves original source_kind and canonical aliases',()=>{
    const bytes=fs.readFileSync(path.resolve(__dirname,'../../'+qa.MASTER_PATH));
    const master=JSON.parse(bytes),before=JSON.stringify(master);
    for(const lesson of master.lessons){const cases=qa.uiCases(lesson);
        cases.forEach((task,i)=>{assert.equal(task.source_index,lesson.practice[i].source_index);
            task.controls.forEach((control,p)=>{const authored=lesson.practice[i].controls[p];
                assert.equal(control.source_kind,authored.kind);
                assert.equal(control.kind,qa.isManual(authored)?'manual':authored.kind);
                assert.equal(control.label,authored.label_uk);assert.equal(control.required,true);
                if(qa.isManual(authored)){assert.deepEqual(control.accepted,authored.accepted_answers);assert.deepEqual(control.tokens,authored.tokens);assert.equal(control.answer,authored.canonical_answer);}
            });
        });
    }assert.equal(JSON.stringify(master),before,'Mapping must not rewrite the frozen author object');
});

for(const lesson of qa.master.lessons)for(const task of lesson.practice)for(const control of task.controls){
    if(!qa.isManual(control))continue;
    test('canonical token identities preserve lexical content and punctuation: '+control.id,()=>{
        const order=qa.canonicalTokenOrder(control);assert.equal(order.length,control.tokens.length);
        assert.equal(new Set(order).size,order.length);
        assert.equal(order.map(i=>control.tokens[i]).join(' '),control.canonical_answer);
        assert.deepEqual([...control.tokens],qa.master.lessons.find(l=>l.key===lesson.key).practice.find(t=>t.id===task.id).controls.find(c=>c.id===control.id).tokens);
    });
}

test('two separate to identities are retained rather than deduplicated or selected in bank order',()=>{
    const control=qa.master.lessons[0].practice.find(t=>t.id==='m44-will-q4').controls[0];
    const order=qa.canonicalTokenOrder(control),indices=control.tokens.map((t,i)=>t==='to'?i:null).filter(i=>i!==null);
    assert.equal(indices.length,2);assert.notEqual(indices[0],indices[1]);assert.ok(indices.every(i=>order.includes(i)));
    assert.notEqual(control.tokens.join(' '),control.canonical_answer,'The original source bank is intentionally shuffled');
    assert.equal(order.map(i=>control.tokens[i]).join(' '),'Are you going to go to the market after work?');
});

test('token order rejects omission, multiplicity loss and internal punctuation changes',()=>{
    for(const control of [
        {id:'omit-not',tokens:['I','will','go.'],canonical_answer:'I will not go.'},
        {id:'duplicate-to',tokens:['I','am','going','to','go.','to'],canonical_answer:'I am going to go.'},
        {id:'number',tokens:['By 2030,','I','will retire.'],canonical_answer:'By 2031, I will retire.'},
        {id:'comma',tokens:['By noon','I','will leave.'],canonical_answer:'By noon, I will leave.'},
    ])assert.throws(()=>qa.canonicalTokenOrder(control));
});

test('formula cells are preserved as formulas, not missing EN/UK examples',()=>{
    assert.equal(qa.cellText({formula:'will have been + V-ing'}),'will have been + V-ing');
    assert.equal(qa.cellText({text_uk:'Попередній намір'}),'Попередній намір');
    assert.equal(qa.cellText({en:'We will leave.',uk:'Ми підемо.',note_uk:'Рішення.'}),'We will leave. Ми підемо. Рішення.');
});

test('request policy allows only read-only local content and declared Google Fonts',()=>{
    assert.equal(qa.allowedRequest('http://gramlyze.loc/theory/maibutni-formy/present-continuous-for-future','GET'),true);
    assert.equal(qa.allowedRequest('https://fonts.googleapis.com/css2?family=Manrope','GET'),true);
    assert.equal(qa.allowedRequest('https://fonts.gstatic.com/s/manrope/file.woff2','GET'),true);
    for(const [url,method] of [
        ['http://gramlyze.loc/api/progress','GET'],['http://gramlyze.loc/api/attempt','GET'],
        ['http://gramlyze.loc/api/chatgpt','GET'],['http://gramlyze.loc/api/questions?provider=openai','GET'],
        ['http://gramlyze.loc/api/questions','POST'],['http://gramlyze.loc/theory','HEAD'],
        ['https://gramlyze.com/theory','GET'],['https://gramlyze.ub/theory','GET'],
        ['http://user:password@gramlyze.loc/','GET'],['https://fonts.googleapis.com.evil.test/css','GET'],
    ])assert.equal(qa.allowedRequest(url,method),false,url+' '+method);
});

test('safe request evidence excludes query strings and credentials',()=>{
    assert.equal(qa.safeUrl('http://user:password@gramlyze.loc/api/questions?token=secret#private'),'http://gramlyze.loc/api/questions');
});

test('the browser harness cannot silently replace reference expectations with M44 AFTER',()=>{
    const source=fs.readFileSync(path.resolve(__dirname,'../../tools/diagnostics/seo-m44-local.cjs'),'utf8');
    assert.ok(source.includes('exactPrivate(REFERENCE_FILE,REFERENCE_SHA)'));
    assert.ok(source.includes('referenceCandidates(group.role'));
    assert.ok(source.includes('Typed clear releases repeated-token identities and history'));
    assert.ok(source.includes('beforeHelper.domSummary(dom.window.document'));
    assert.equal(source.includes('ownClasses[targets.indexOf(target)]'),false,'No borrowed M43 bank identities');
    assert.ok(source.includes("'--dry-contract'"));
});

for(const [id,colorClass] of [['m44-will-prediction','text-slate-700'],['m44-choice-result','text-slate-700'],['m44-choice-finishing','text-slate-700'],['m44-choice-duration','text-rose-700']]){
    for(const theme of ['light','dark'])test('finite caption composition preserves all properties and two independent origins: '+id+' '+theme,()=>{
        const expected=qa.finiteCaptionExpectation(id,1440,theme);
        assert.equal(expected.classes,colorClass);assert.equal(expected.tag,'SPAN');assert.equal(expected.styles.display,'inline');
        assert.equal(Object.keys(expected.styles).length,32);assert.equal(expected.provenance.layout.reference,'M43-A');
        assert.equal(expected.provenance.palette.reference,'PPC');assert.equal(expected.provenance.layout.theme,theme);
        assert.equal(expected.provenance.layout.sourceSha256,'d1b61830ca9899c4fd2b5fd1f9671b3cf85b3864c1bf015d0eb1fb37fb16c666');
        if(id==='m44-choice-duration'){assert.equal(expected.provenance.palette.theme,'light');assert.equal(expected.styles.color,'rgb(190, 18, 60)');}
        for(const [property,value] of [['display','block'],['color','rgb(0, 255, 0)'],['font-size','8px'],['font-style','italic'],['line-height','10px']]){
            assert.throws(()=>assert.deepEqual({...expected.styles,[property]:value},expected.styles),'No property is ignored: '+property);
        }
    });
}

test('finite caption composition cannot broaden to another point, viewport or theme',()=>{
    assert.equal(qa.finiteCaptionExpectation('m44-will-immediate',1440,'light'),null);
    assert.equal(qa.finiteCaptionExpectation('m44-choice-state',390,'dark'),null);
    assert.throws(()=>qa.finiteCaptionExpectation('m44-choice-duration',320,'light'));
    assert.throws(()=>qa.finiteCaptionExpectation('m44-choice-duration',1440,'unknown'));
});

test('reference-state settlement is bounded, awaits finite animations and does not cancel them',()=>{
    const source=fs.readFileSync(path.resolve(__dirname,'../../tools/diagnostics/capture-m44-reference-supplement.cjs'),'utf8');
    assert.ok(source.includes('maxWaitMs<=2000'));assert.ok(source.includes('a.finished.catch'));
    assert.ok(source.includes('Finite CSS transition still active after settle'));assert.ok(source.includes('activeFinite:active.length'));
    assert.equal(source.includes('.cancel('),false,'No cancellation to force a reference state');
    const harness=fs.readFileSync(path.resolve(__dirname,'../../tools/diagnostics/seo-m44-local.cjs'),'utf8');
    assert.ok(harness.includes('await referenceProbe.settleStyles(page,scope)'),'Targets use the same state settlement policy');
});

function mockStylePage(snapshot,width=1440){
    return {viewportSize:()=>({width,height:1000}),
        evaluate:async(_fn,args)=>({selector:args.selector,maxWaitMs:args.maxWaitMs,finiteWaited:0,infiniteIgnored:0,elapsedMs:0,activeFinite:0}),
        locator:()=>({evaluate:async()=>snapshot})};
}

for(const id of ['m44-will-prediction','m44-choice-result','m44-choice-finishing','m44-choice-duration'])for(const theme of ['light','dark']){
    test('actual matcher accepts the finite caption and rejects property mutants: '+id+' '+theme,async()=>{
        const expected=qa.finiteCaptionExpectation(id,1440,theme);
        const element={tag:'SPAN',classes:expected.classes,pointId:id,controlId:null,visible:true,styles:{...expected.styles}};
        const valid=await qa.compareStyles(mockStylePage([{role:'usage-caption',elements:[element]}]),{theme});
        assert.equal(valid.differences.length,0);assert.equal(valid.components,1);assert.equal(valid.properties,32);
        for(const [property,value] of [['display','block'],['color','rgb(0, 255, 0)'],['font-size','8px'],['font-style','italic'],['line-height','10px']]){
            const mutant={...element,styles:{...element.styles,[property]:value}};
            await assert.rejects(qa.compareStyles(mockStylePage([{role:'usage-caption',elements:[mutant]}]),{theme}));
        }
    });
}

function receiptFixtures(){
    const dir=path.resolve('D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m44-local');
    return {records:JSON.parse(fs.readFileSync(path.join(dir,'source-sync-before-v1/manifest.json'))),
        receipts:['presentation-sync-v1.json','presentation-sync-v2.json'].map(name=>JSON.parse(fs.readFileSync(path.join(dir,name))))};
}

test('source audit pins the exact approved three-file chain and current ROOT/WT hashes',()=>{
    const {records,receipts}=receiptFixtures(),original=JSON.stringify(records),projection=qa.sourceReceiptProjection(records,receipts);
    assert.equal(JSON.stringify(records),original,'Original sync manifest is never rewritten');
    assert.equal(projection['app/Support/M44NativeHtml.php'].canonical,'ee42b999f523f89e50c93a5a37600937a32591b7e76e7dfadc8e436f49b169fb');
    assert.equal(projection['resources/views/engram/theory/blocks-v3/m44-native-section.blade.php'].served,'5bef5361eb792a22570ef2e0b452fef4a7ea8fdcf40bef446d1da521a0549576');
    assert.equal(projection['resources/views/engram/theory/blocks-v3/m44-native-styles.blade.php'].canonical,'a8539f335ddd32e4f4dff216c18b72524f432042b8a77428f7fd0594c3775c1f');
    qa.assertServedSources(qa.sourceHashes(),qa.sourceHashes('D:/DEV/htdocs/gramlyze.loc'));
});

test('foreign, reordered, absent and broken-continuity receipts are refused',()=>{
    const original=receiptFixtures();
    for(const mutate of [
        value=>{value.receipts[0].changes[0].path='resources/views/theory/show.blade.php';},
        value=>{value.receipts[0].changes.reverse();},
        value=>{value.receipts[0].changes[0].beforeSha256='0'.repeat(64);},
        value=>{value.receipts[1].beforeSha256='0'.repeat(64);},
        value=>{value.receipts.pop();},
        value=>{value.receipts[0].contentMasterPackageDefinitionsDatabaseUnchanged=false;},
        value=>{value.records.find(r=>r.path==='app/Support/M44NativeHtml.php').mode='shared-reviewed-hunks';},
    ]){const value=structuredClone(original);mutate(value);assert.throws(()=>qa.sourceReceiptProjection(value.records,value.receipts));}
});

test('tampered receipt bytes cannot be accepted despite valid-looking path and hash fields',()=>{
    const originalRead=fs.readFileSync;
    fs.readFileSync=function(file,...args){const bytes=originalRead.call(this,file,...args);
        if(String(file).replaceAll('\\','/').endsWith('/presentation-sync-v1.json'))return Buffer.concat([Buffer.from(bytes),Buffer.from(' ')]);
        return bytes;
    };
    try{assert.throws(()=>qa.approvedSourceProjection(),/Independent private evidence exact hash/);}
    finally{fs.readFileSync=originalRead;}
});
