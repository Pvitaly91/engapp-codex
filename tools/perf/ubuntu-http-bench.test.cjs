'use strict';

// Pure offline tests. Do not start a server, connect to any host or benchmark
// Windows; the network runner itself refuses non-Linux environments.
const assert=require('node:assert/strict');
const crypto=require('node:crypto');
const test=require('node:test');
const {gzipSync}=require('node:zlib');
const {validatePlan,schedule,percentile,summarize,validBody}=require('./ubuntu-http-bench.cjs');

const css=Buffer.from('.sample{width:12px}'.repeat(20));
const zipped=gzipSync(css);
const sha=x=>crypto.createHash('sha256').update(x).digest('hex');
const asset={path:'/build/assets/app-AbC12345.css',kind:'static',sha256:sha(css),gzipSha256:sha(zipped)};
const abc=(rest={})=>({phase:'abc',targets:{A:{port:18081},B:{port:18082},C:{port:18083,staticEncoding:'gzip'}},
    paths:[asset],rounds:6,parallelism:[1,3,5,10,20],...rest});
const dynamic={path:'/test/past-perfect/forms',kind:'dynamic'};

test('ABC has a complete ordered crossover and identical route sequences in each paired wave',()=>{
    const plan=validatePlan(abc({paths:[asset,{...asset,path:'/build/assets/app-Alternative82.css'}]}));
    const waves=schedule(plan);
    assert.equal(waves.length,5*6*3);
    for (const n of plan.parallelism) {
        const slice=waves.filter(w=>w.n===n);
        const orders=new Set();
        for (let j=0;j<slice.length;j+=3) {
            const cohort=slice.slice(j,j+3);
            orders.add(cohort.map(w=>w.label).join(''));
            assert.deepEqual(cohort[0].paths,cohort[1].paths);
            assert.deepEqual(cohort[0].paths,cohort[2].paths);
        }
        assert.equal(orders.size,6);
    }
});

test('A/A alternates starting arms and checks every eligible dynamic route serially',()=>{
    const plan=validatePlan({phase:'aa',targets:{A0:{port:18081},A1:{port:18081}},paths:[
        dynamic,{path:'/pl/test/sentence-builder-to-be-a1/step/compose',kind:'dynamic'},
    ],rounds:2});
    const waves=schedule(plan);
    assert.deepEqual(waves.map(w=>w.label),['A0','A1','A1','A0','A1','A0','A0','A1']);
    assert.equal(waves.filter(w=>w.paths[0].path===dynamic.path).length,4);
});

test('only predetermined shadow ports are permitted and live standard ports require smoke',()=>{
    assert.throws(()=>validatePlan(abc({targets:{A:{port:80},B:{port:18082},C:{port:18083}}})),/PORT_NOT_PERMITTED/);
    assert.throws(()=>validatePlan(abc({targets:{A:{port:22,tls:true},B:{port:18082},C:{port:18083}}})),/PORT_NOT_PERMITTED/);
    assert.throws(()=>validatePlan(abc({targets:{A:{port:18081,host:'other.local'},B:{port:18082},C:{port:18083}}})),/INVALID_TARGET_KEYS/);
    assert.doesNotThrow(()=>validatePlan({phase:'smoke',targets:{live:{port:443,tls:true}},paths:[dynamic]}));
});

test('arbitrary request URLs, mixed transport, unsafe plans and cookies fail closed',()=>{
    for (const url of ['https://other.local/', '//example.com/', '/.env', '/test/../present-simple/forms',
        '/api/word-search/uk?q=go&other=1','/build/assets/x-AbC12345.css.gz','/build/assets/x-AbC12345.css.map']) {
        assert.throws(()=>validatePlan(abc({paths:[{...asset,path:url}]})),/UNPINNED_STATIC_ROUTE/);
    }
    assert.throws(()=>validatePlan(abc({rounds:5})),/UNBALANCED_ROUNDS/);
    assert.throws(()=>validatePlan(abc({paths:[dynamic]})),/DYNAMIC_AND_SMOKE_ARE_SERIAL/);
    assert.throws(()=>validatePlan(abc({targets:{A:{port:18081,tls:true},B:{port:18082},C:{port:18083}}})),/ABC_TRANSPORTS_DIFFER/);
    assert.throws(()=>validatePlan(abc({targets:{A:{port:18081,sessionCookie:'personal'},B:{port:18082},C:{port:18083}}})),/FIRST_RUN_HAS_COOKIES/);
    assert.throws(()=>validatePlan({phase:'smoke',profile:'resume',targets:{live:{port:443,tls:true,
        sessionCookie:'perf31_fixture',resumeFile:'/private/resume.json'}},paths:[dynamic]}),/LIVE_PROFILE_MUST_NOT_SEND_COOKIES/);
    assert.throws(()=>schedule({...validatePlan(abc()),rounds:30}),/TOO_MANY_REQUESTS/);
});

test('repeat accepts the three exact shadow cookie names without permitting ordinary session cookies',()=>{
    const targets={A:{port:18081,sessionCookie:'perf31_a'},B:{port:18082,sessionCookie:'perf31_b'},
        C:{port:18083,sessionCookie:'perf31_c',staticEncoding:'gzip'}};
    const plan=abc({targets,paths:[dynamic],parallelism:[1],profile:'repeat'});
    assert.equal(validatePlan(plan).targets.C.sessionCookie,'perf31_c');
    assert.throws(()=>validatePlan({...plan,targets:{...targets,A:{...targets.A,sessionCookie:'laravel_session'}}}),
        /NOT_A_PRIVATE_BENCH_COOKIE/);
    assert.throws(()=>validatePlan({...plan,targets:{...targets,A:{...targets.A,sessionCookie:'perf31_x'}}}),
        /NOT_A_PRIVATE_BENCH_COOKIE/);
});

test('native bytes and MIME must match the gzip and decoded pins; identity fallback remains distinct',()=>{
    function raw(body,header={}) {
        return {body,headers:{'content-length':String(body.length),'content-type':'text/css',...header}};
    }
    const gzip=validBody(raw(zipped,{'content-encoding':'gzip'}),asset,{staticEncoding:'gzip'},4096);
    assert.equal(gzip.encodedBytes,zipped.length);
    assert.equal(gzip.decodedBytes,css.length);
    assert.equal(gzip.decodedSha256,asset.sha256);
    assert.throws(()=>validBody(raw(zipped,{'content-encoding':'gzip'}),asset,{staticEncoding:'identity'},4096),/UNEXPECTED_ENCODING/);
    assert.throws(()=>validBody(raw(zipped,{'content-encoding':'gzip'}),{...asset,gzipSha256:'1'.repeat(64)},{
        staticEncoding:'gzip'},4096),/ENCODED_SHA_MISMATCH/);
    assert.throws(()=>validBody(raw(zipped,{'content-encoding':'gzip'}),{...asset,sha256:'1'.repeat(64)},{
        staticEncoding:'gzip'},4096),/DECODED_SHA_MISMATCH/);
    assert.throws(()=>validBody(raw(zipped,{'content-encoding':'gzip'}),asset,{staticEncoding:'gzip'},32),/INVALID_GZIP/);
    assert.equal(validBody(raw(css),{...asset,offer:'gzip;q=0'},{staticEncoding:'gzip'},4096).encoding,'identity');
    assert.equal(validBody(raw(css),{...asset,gzipSha256:null},{staticEncoding:'gzip'},4096).encoding,'identity');
});

test('dynamic acceptance refuses global gzip, unknown locales, invalid JSON and wrong dictionary',()=>{
    const uk={body:Buffer.from('<html lang="uk-UA"><h1>Test</h1></html>'),headers:{'content-type':'text/html; charset=UTF-8'}};
    assert.equal(validBody(uk,dynamic,{staticEncoding:'gzip'},4096).encoding,'identity');
    assert.throws(()=>validBody(uk,{...dynamic,path:'/pl'+dynamic.path},{staticEncoding:'gzip'},4096),/UNEXPECTED_LANGUAGE/);
    assert.throws(()=>validBody({...uk,headers:{...uk.headers,'content-encoding':'gzip'}},dynamic,{staticEncoding:'gzip'},4096),/UNEXPECTED_ENCODING/);
    const json={body:Buffer.from('[{"word":"go"}]'),headers:{'content-type':'application/json'}};
    const path={path:'/api/word-search/uk?q=go',kind:'dynamic'};
    assert.equal(validBody(json,path,{staticEncoding:'identity'},4096).decodedBytes,15);
    assert.throws(()=>validBody({...json,body:Buffer.from('[]')},path,{staticEncoding:'identity'},4096),/UNEXPECTED_DICTIONARY/);
});

test('stats distinguish failed attempts from complete-wave throughput',()=>{
    assert.equal(percentile([],.99),null);
    assert.equal(percentile([4,1,3,2],.5),2.5);
    assert.equal(percentile([4,1,3,2],.95),4);
    const rows=[{measured:true,label:'A',n:1,path:'/',result:'PASS',ttfbMs:10,totalMs:12,encodedBytes:20,decodedBytes:20},
        {measured:true,label:'A',n:1,path:'/',result:'FAIL',ttfbMs:null,totalMs:300,encodedBytes:0,decodedBytes:null},
        {measured:false,label:'A',n:1,path:'/',result:'PASS',ttfbMs:1000,totalMs:1001,encodedBytes:99,decodedBytes:99}];
    const waves=[{label:'A',n:1,wireMs:13,passed:1},{label:'A',n:1,wireMs:300,passed:0}];
    const result=summarize(waves,rows)['A/1'];
    assert.equal(result.attempted,2);
    assert.equal(result.waves,1);
    assert.equal(result.ttfbMs.median,10);
    assert.equal(result.requestsPerSecond.median,1000/13);
    assert.equal(result.encodedBytes,20);
});
