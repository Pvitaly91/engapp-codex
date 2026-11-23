#!/usr/bin/env node
'use strict';

// PERF-31 evidence client: fixed Gramlyze Host/SNI, direct IPv4 loopback only.
// Never follows redirects, uses proxy agents, emits bodies, or performs POSTs.
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const fs = require('node:fs');
const http = require('node:http');
const https = require('node:https');
const net = require('node:net');
const os = require('node:os');
const path = require('node:path');
const {performance} = require('node:perf_hooks');
const {spawnSync} = require('node:child_process');
const tls = require('node:tls');
const {gunzipSync} = require('node:zlib');

const HOST = 'gramlyze.ub';
const IP = '127.0.0.1';
const HEX = /^[a-f0-9]{64}$/;
const VITE = /^\/build\/assets\/[A-Za-z0-9][A-Za-z0-9_.-]{0,159}-[A-Za-z0-9_-]{8,32}\.(?:css|m?js)$/;
const COOKIE_NAME = /^perf31_[a-z0-9_]{4,48}$/;
const COOKIE_VALUE = /^[A-Za-z0-9._~+/%=-]{16,2048}$/;
const DYNAMIC = new Set(['/', '/en', '/pl',
    '/test/present-simple/forms', '/test/past-simple/forms', '/test/past-perfect/forms',
    '/test/word-order/theory-basic-word-order/step', '/test/passive-voice/theory-passive-voice-present-simple',
    '/theory/passive-voice/theory-passive-voice-present-simple', '/test/sentence-builder-to-be-a1/step/compose'
].flatMap(p => (p === '/' || p === '/en' || p === '/pl') ? [p] : [p, '/en' + p, '/pl' + p]));
for (const locale of ['uk','en','pl']) DYNAMIC.add('/api/word-search/' + locale + '?q=go');
const PERMUTATIONS = [
    ['A','B','C'], ['B','C','A'], ['C','A','B'], ['A','C','B'], ['C','B','A'], ['B','A','C'],
];
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const check = (cond, code) => { if (!cond) throw Object.assign(new Error(code), {code}); };
const oneOf = (value, choices) => choices.includes(value);
const intIn = (v, min, max) => Number.isSafeInteger(v) && v >= min && v <= max;
const plain = value => value && Object.getPrototypeOf(value) === Object.prototype;

function keys(row, allowed, code) {
    check(plain(row) && Object.keys(row).every(key => allowed.includes(key)), code);
}

// Standard management/shadow choices only; no caller-supplied network host or
// arbitrary production ports. The actual 80/443 vhost is smoke-only.
function validPort(port, smoke, secure) {
    return (smoke && port === (secure ? 443 : 80)) || intIn(port, 18000, 18999) || intIn(port, 28000, 28999);
}

function validatePlan(input) {
    keys(input, ['phase','targets','paths','rounds','parallelism','timeoutMs','deadlineMs','maxEncodedBytes',
        'maxDecodedBytes','profile'], 'INVALID_PLAN_KEYS');
    check(oneOf(input.phase, ['aa','abc','smoke']), 'INVALID_PHASE');
    const smoke = input.phase === 'smoke';
    const expected = input.phase === 'aa' ? ['A0','A1'] : input.phase === 'abc' ? ['A','B','C'] : ['live'];
    keys(input.targets, expected, 'INVALID_TARGETS');
    check(expected.every(k => Object.hasOwn(input.targets, k)), 'MISSING_TARGET');
    const profile = input.profile ?? 'first';
    check(oneOf(profile, ['first','repeat','resume']), 'INVALID_PROFILE');
    const rounds = input.rounds ?? (smoke ? 1 : 6);
    check(smoke ? rounds === 1 : input.phase === 'aa' ?
        intIn(rounds, 2, 24) && rounds % 2 === 0 : intIn(rounds, 6, 24) && rounds % 6 === 0, 'UNBALANCED_ROUNDS');
    const parallelism = input.parallelism ?? [1];
    check(Array.isArray(parallelism) && parallelism.length > 0 && new Set(parallelism).size === parallelism.length &&
        parallelism.every(n => oneOf(n, [1,3,5,10,20])), 'INVALID_PARALLELISM');
    const timeoutMs = input.timeoutMs ?? 10000, deadlineMs = input.deadlineMs ?? 180000;
    check(intIn(timeoutMs, 250, 30000) && intIn(deadlineMs, timeoutMs, 600000), 'UNBOUNDED_DEADLINE');
    const maxEncodedBytes = input.maxEncodedBytes ?? 4 * 1024 * 1024;
    const maxDecodedBytes = input.maxDecodedBytes ?? 8 * 1024 * 1024;
    check(intIn(maxEncodedBytes, 1024, 8*1024*1024) && intIn(maxDecodedBytes, 1024, 16*1024*1024), 'UNBOUNDED_BODY');
    check(Array.isArray(input.paths) && input.paths.length > 0 && input.paths.length <= 24, 'INVALID_CORPUS');
    const paths = input.paths.map(spec => {
        keys(spec, ['path','kind','sha256','gzipSha256','offer'], 'INVALID_ROUTE_KEYS');
        if (spec.kind === 'dynamic') {
            check(DYNAMIC.has(spec.path) && !Object.hasOwn(spec,'sha256') && !Object.hasOwn(spec,'gzipSha256') &&
                !Object.hasOwn(spec,'offer'), 'UNKNOWN_DYNAMIC_ROUTE');
        } else {
            check(spec.kind === 'static' && typeof spec.path === 'string' && VITE.test(spec.path) &&
                typeof spec.sha256 === 'string' && HEX.test(spec.sha256), 'UNPINNED_STATIC_ROUTE');
            check(spec.gzipSha256 === undefined || spec.gzipSha256 === null ||
                (typeof spec.gzipSha256 === 'string' && HEX.test(spec.gzipSha256)), 'INVALID_GZIP_PIN');
            check(spec.offer === undefined || (smoke && oneOf(spec.offer, ['gzip','identity','gzip;q=0'])),
                'UNVERIFIED_CODING_OFFER');
        }
        return spec;
    });
    check(new Set(paths.map(spec => spec.path)).size === paths.length, 'DUPLICATE_ROUTE');
    if (smoke || profile !== 'first' || paths.some(spec => spec.kind === 'dynamic'))
        check(parallelism.length === 1 && parallelism[0] === 1, 'DYNAMIC_AND_SMOKE_ARE_SERIAL');
    check(profile === 'first' || paths.every(spec => spec.kind === 'dynamic'), 'SESSION_SCOPE');
    const targets = Object.fromEntries(expected.map(name => {
        const row = input.targets[name];
        keys(row, ['port','tls','caFile','staticEncoding','sessionCookie','resumeFile','observe'], 'INVALID_TARGET_KEYS');
        check(row.tls === undefined || typeof row.tls === 'boolean', 'INVALID_TLS');
        const secure = row.tls === true, staticEncoding = row.staticEncoding ?? 'identity';
        check(validPort(row.port, smoke, secure), 'PORT_NOT_PERMITTED');
        check(row.caFile === undefined || (secure && typeof row.caFile === 'string' && path.isAbsolute(row.caFile)), 'INVALID_CA_FILE');
        check(oneOf(staticEncoding, ['identity','gzip']), 'INVALID_TARGET_ENCODING');
        if (profile === 'first') check(row.sessionCookie === undefined && row.resumeFile === undefined, 'FIRST_RUN_HAS_COOKIES');
        else {
            check(typeof row.sessionCookie === 'string' && COOKIE_NAME.test(row.sessionCookie), 'NOT_A_PRIVATE_BENCH_COOKIE');
            check(profile === 'resume' ? typeof row.resumeFile === 'string' && path.isAbsolute(row.resumeFile) :
                row.resumeFile === undefined, 'INVALID_RESUME_FIXTURE');
            check(!smoke, 'LIVE_PROFILE_MUST_NOT_SEND_COOKIES');
        }
        if (row.observe !== undefined) {
            keys(row.observe, ['nginx','fpm'], 'UNEXPECTED_PROCESS_GROUP');
            for (const pids of Object.values(row.observe)) {
                check(Array.isArray(pids) && pids.length > 0 && pids.length <= 24 &&
                    pids.every(n => intIn(n, 2, 4194304)) && new Set(pids).size === pids.length, 'INVALID_PID_LIST');
            }
        }
        return [name,{...row, tls:secure, staticEncoding}];
    }));
    if (input.phase === 'aa') for (const field of ['tls','staticEncoding'])
        check(targets.A0[field] === targets.A1[field], 'AA_COMPARATORS_DIFFER');
    if (input.phase === 'abc') {
        check(new Set(Object.values(targets).map(row => row.port)).size === 3, 'ABC_PORTS_NOT_ISOLATED');
        check(new Set(Object.values(targets).map(row => row.tls)).size === 1, 'ABC_TRANSPORTS_DIFFER');
    }
    return {phase:input.phase, targets, paths, rounds, parallelism, timeoutMs, deadlineMs, maxEncodedBytes,
        maxDecodedBytes, profile};
}

function schedule(plan) {
    const waves = [];
    const variants = plan.phase === 'aa' ? [['A0','A1'], ['A1','A0']] :
        plan.phase === 'abc' ? PERMUTATIONS : [['live']];
    const groups = plan.paths.some(spec => spec.kind === 'dynamic') || plan.phase === 'smoke' ?
        plan.paths.map(spec => [spec]) : [plan.paths];
    let id = 0;
    for (const n of plan.parallelism) {
        for (let round=0; round < plan.rounds; round++) {
            for (let g=0; g < groups.length; g++) {
                const group = groups[g];
                const corpus = Array.from({length:n}, (_,j) => group[(round + j) % group.length]);
                for (const label of variants[(round + g) % variants.length])
                    waves.push({id:++id, n, round:round+1, label, paths:corpus});
            }
        }
    }
    check(waves.reduce((total,wave) => total + wave.paths.length,0) <= 3000, 'TOO_MANY_REQUESTS');
    return waves;
}

function percentile(samples, p) {
    const sorted = [...samples].sort((a,b)=>a-b);
    if (!sorted.length) return null;
    // Median averages the two central observations; tails use nearest rank.
    if (p === .5 && sorted.length % 2 === 0) return (sorted[sorted.length/2-1]+sorted[sorted.length/2])/2;
    return sorted[Math.ceil(p * sorted.length)-1];
}

const triplet = rows => ({median:percentile(rows,.5),p95:percentile(rows,.95),p99:percentile(rows,.99)});
const sum = (rows, key) => rows.reduce((n,r)=>n+r[key],0);

function summarize(waveRows, requestRows) {
    const stats = {};
    for (const w of waveRows) {
        const key = w.label + '/' + w.n;
        if (!stats[key]) stats[key] = {waves:[],all:[]};
        stats[key].waves.push(w);
    }
    for (const row of requestRows) if (row.measured) stats[row.label + '/' + row.n].all.push(row);
    return Object.fromEntries(Object.entries(stats).map(([key,{waves,all}]) => {
        const ok = all.filter(row=>row.result==='PASS');
        const complete = waves.filter(w=>w.passed===w.n && w.wireMs>0);
        const paths = Object.fromEntries([...new Set(all.map(r=>r.path))].sort().map(p=>{
            const rr = ok.filter(r=>r.path===p);
            return [p,{passed:rr.length, attempted:all.filter(r=>r.path===p).length,
                ttfbMs:triplet(rr.map(r=>r.ttfbMs)),totalMs:triplet(rr.map(r=>r.totalMs)),
                encodedBytes:sum(rr,'encodedBytes'),decodedBytes:sum(rr,'decodedBytes')}];
        }));
        return [key,{passed:ok.length, attempted:all.length, waves:complete.length,
            ttfbMs:triplet(ok.map(r=>r.ttfbMs)),totalMs:triplet(ok.map(r=>r.totalMs)),
            requestsPerSecond:triplet(complete.map(w=>1000*w.n/w.wireMs)),
            encodedBytes:sum(ok,'encodedBytes'),decodedBytes:sum(ok,'decodedBytes'),paths}];
    }));
}

function readPrivate(filename, limit=65536) {
    const stat = fs.lstatSync(filename);
    check(stat.isFile() && stat.nlink === 1 && (stat.mode & 0o077) === 0 &&
        stat.uid === process.geteuid() && stat.size <= limit, 'UNSAFE_PRIVATE_INPUT');
    return JSON.parse(fs.readFileSync(filename,'utf8'));
}

function cap(plan) {
    const info = fs.readFileSync('/proc/meminfo','utf8').match(/^MemAvailable:\s+(\d+)\s+kB$/m);
    check(info,'MEMORY_NOT_MEASURABLE');
    const memoryKiB = Number(info[1]), cpus = Math.min(4, os.availableParallelism());
    const n = Math.max(...plan.parallelism);
    check(n <= 1 || (memoryKiB >= 262144 && cpus >= 2), 'RESOURCE_LIMIT');
    check(n < 20 || (memoryKiB >= 524288 && cpus >= 4), 'RESOURCE_LIMIT');
    check(n*plan.maxEncodedBytes <= Math.min(96*1024*1024, Math.floor(memoryKiB*1024/12)), 'BUFFER_BUDGET');
    return {cpus,memoryKiB};
}

function processSnapshot(observe={}) {
    const result = {};
    for (const [group,pids] of Object.entries(observe)) result[group] = pids.map(pid=>{
        const read = kind => fs.readFileSync('/proc/'+pid+'/'+kind,'utf8');
        const match = (text,key) => Number(text.match(new RegExp('^'+key+':\\s*(\\d+)','m'))?.[1]) || 0;
        try {
            const comm = read('comm').trim();
            if (group==='nginx' ? comm!=='nginx' : !/^php-fpm(?:[0-9.]+)?$/.test(comm))
                return {pid,error:'WRONG_PROCESS'};
            const fields = read('stat').split(/\) [RSDZTWtXxKPI] /)[1]?.split(' ');
            check(fields?.length>=19,'PROC_STAT_FORMAT');
            const status=read('status');
            let io=null;
            try {
                const raw=read('io');
                io=Object.fromEntries(['syscr','syscw','read_bytes','write_bytes'].map(key=>[key,match(raw,key)]));
            } catch {}
            return {pid,startTicks:Number(fields[18]),cpuTicks:Number(fields[10])+Number(fields[11]),
                rssKiB:match(status,'VmRSS'),peakRssKiB:match(status,'VmHWM'),io};
        } catch(error) {return {pid,error:error.code??'PROC_UNAVAILABLE'};}
    });
    return result;
}

function actualTarget(row, profile, paths) {
    const ca = row.caFile === undefined ? undefined : (() => {
        const st=fs.statSync(row.caFile);
        check(st.isFile() && st.size > 0 && st.size <= 4*1024*1024,'UNSAFE_CA_FILE');
        return fs.readFileSync(row.caFile);
    })();
    const cookies = new Map();
    if (profile==='resume') {
        const source=readPrivate(row.resumeFile);
        keys(source,paths.map(p=>p.path),'INVALID_RESUME_PATH');
        for (const {path} of paths) {
            check(typeof source[path]==='string' && COOKIE_VALUE.test(source[path]),'INVALID_RESUME_VALUE');
            cookies.set(path,source[path]);
        }
    }
    return {...row,ca,cookies};
}

function timedGet(target, spec, ms, bodyLimit, cookie) {
    const start=performance.now();
    return new Promise(resolve=>{
        let status=null, headersAt=null, seen=0, stream, settled=false, timer, peer=null;
        const chunks=[];
        const complete=(fields)=>{
            if (settled) return;
            settled=true;
            clearTimeout(timer);
            resolve({status,startedAt:start,endedAt:performance.now(),headersAt,seen,peer,...fields});
        };
        const opts={host:IP, hostname:IP, port:target.port, path:spec.path, method:'GET',headers:{
            'Host':HOST,'Connection':'close','Accept-Encoding':spec.offer??'gzip',
            ...(cookie ? {'Cookie':target.sessionCookie+'='+cookie} : {}),
        }};
        // Per-request non-pooling agent with an explicit transport constructor;
        // it cannot inherit any ambient proxy setting from a global Node agent.
        const agent=target.tls ? new https.Agent({keepAlive:false}) : new http.Agent({keepAlive:false});
        agent.createConnection=()=>target.tls ?
            tls.connect({host:IP,port:target.port,servername:HOST,rejectUnauthorized:true,ca:target.ca,
                checkServerIdentity:(_,cert)=>tls.checkServerIdentity(HOST,cert),ALPNProtocols:['http/1.1']}) :
            net.connect({host:IP,port:target.port,family:4});
        const request=(target.tls?https:http).request({...opts,agent}, response=>{
            stream=response;
            status=response.statusCode;
            headersAt=performance.now();
            peer={address:response.socket.remoteAddress,port:response.socket.remotePort};
            if (peer.address!==IP || peer.port!==target.port) {
                complete({error:'LOOPBACK_PEER_MISMATCH'}); request.destroy(); return;
            }
            if (status !== 200) { complete({error:'HTTP_'+status}); request.destroy(); return; }
            const len=response.headers['content-length'];
            if (len!==undefined && (!/^\d+$/.test(len) || Number(len)>bodyLimit)) {
                complete({error:'ENCODED_BODY_LIMIT'}); request.destroy(); return;
            }
            response.on('data',chunk=>{
                seen+=chunk.length;
                if (seen>bodyLimit) {complete({error:'ENCODED_BODY_LIMIT'}); request.destroy();}
                else chunks.push(chunk);
            });
            response.on('aborted',()=>complete({error:'INCOMPLETE_BODY'}));
            response.on('error',error=>complete({error:error.code??'RESPONSE_ERROR'}));
            response.on('end',()=>complete(response.complete ? {body:Buffer.concat(chunks,seen),headers:response.headers} :
                {error:'INCOMPLETE_BODY'}));
        });
        request.on('error',error=>complete({error:error.code??'TRANSPORT_ERROR'}));
        request.on('close',()=>agent.destroy());
        timer=setTimeout(()=>{complete({error:'REQUEST_DEADLINE'}); stream?.destroy(); request.destroy();},ms);
        request.end();
    });
}

function validBody(raw, spec, target, decodedLimit) {
    check(!raw.error,raw.error);
    check(raw.body.length>0,'EMPTY_BODY');
    if (raw.headers['content-length']!==undefined)
        check(Number(raw.headers['content-length']) === raw.body.length,'LENGTH_MISMATCH');
    const encoding=String(raw.headers['content-encoding']??'identity').toLowerCase();
    const expected=spec.kind==='dynamic' || spec.gzipSha256===null || /^identity$|^gzip;q=0$/.test(spec.offer??'') ?
        'identity' : target.staticEncoding;
    check(encoding===expected,'UNEXPECTED_ENCODING');
    let body=raw.body;
    if (encoding==='gzip') {
        try {body=gunzipSync(raw.body,{maxOutputLength:decodedLimit});}
        catch {throw Object.assign(new Error('INVALID_GZIP'),{code:'INVALID_GZIP'});}
        if (spec.gzipSha256) check(sha(raw.body)===spec.gzipSha256,'ENCODED_SHA_MISMATCH');
    }
    check(body.length<=decodedLimit,'DECODED_BODY_LIMIT');
    const type=String(raw.headers['content-type']??'').split(';')[0].trim().toLowerCase();
    let decodedSha256=null;
    if (spec.kind==='static') {
        check(spec.path.endsWith('.css') ? type==='text/css' : ['application/javascript','text/javascript'].includes(type),
            'UNEXPECTED_STATIC_MIME');
        decodedSha256=sha(body);
        check(decodedSha256===spec.sha256,'DECODED_SHA_MISMATCH');
    } else if (spec.path.startsWith('/api/word-search/')) {
        check(type==='application/json','UNEXPECTED_JSON_MIME');
        let data;
        try {data=JSON.parse(body.toString('utf8'));}
        catch {throw Object.assign(new Error('INVALID_JSON'),{code:'INVALID_JSON'});}
        check(Array.isArray(data) && data.some(word=>word && String(word.word).toLowerCase()==='go'),'UNEXPECTED_DICTIONARY');
    } else {
        check(type==='text/html','UNEXPECTED_HTML_MIME');
        const m=/<html\b[^>]*\blang=["']([a-z]{2})(?:-[a-z]+)?["']/i.exec(body.subarray(0,65536).toString('utf8'));
        const lang=/^\/(en|pl)(?:\/|$)/.exec(spec.path)?.[1]??'uk';
        check(m&&m[1].toLowerCase()===lang,'UNEXPECTED_LANGUAGE');
    }
    const prefix=(target.sessionCookie??'')+'=';
    const session=(raw.headers['set-cookie']??[]).find(item=>item.startsWith(prefix))?.slice(prefix.length).split(';')[0];
    return {encoding,encodedBytes:raw.body.length,decodedBytes:body.length,decodedSha256,
        session:COOKIE_VALUE.test(session??'')?session:null};
}

async function main(args) {
    check(process.platform==='linux','REQUIRES_UBUNTU_RUNTIME');
    check(args.length===2,'USAGE node ubuntu-http-bench.cjs PRIVATE_PLAN NEW_RUN_DIR');
    process.umask(0o077);
    const plan=validatePlan(readPrivate(args[0])), waves=schedule(plan), capacity=cap(plan);
    const targets=Object.fromEntries(Object.entries(plan.targets).map(([k,v])=>[k,actualTarget(v,plan.profile,plan.paths)]));
    const out=path.resolve(args[1]);
    const parent=fs.lstatSync(path.dirname(out));
    check(parent.isDirectory() && parent.uid===process.geteuid() && !(parent.mode&0o022) &&
        fs.realpathSync(path.dirname(out))===path.dirname(out), 'UNSAFE_OUTPUT_PARENT');
    fs.mkdirSync(out,{mode:0o700});
    const save=(f,p)=>fs.writeFileSync(path.join(out,f+'.json'),JSON.stringify(p,null,2)+'\n',{flag:'wx',mode:0o600});
    const append=(f,p)=>fs.appendFileSync(path.join(out,f+'.jsonl'),JSON.stringify(p)+'\n',{mode:0o600});
    const time=performance.now(), startAt=new Date().toISOString();
    const tick=spawnSync('getconf',['CLK_TCK'],{encoding:'utf8',timeout:1000}).stdout?.trim();
    const configProof={
        ...plan, targets:Object.fromEntries(Object.entries(targets).map(([label,t])=>[label,{
            port:t.port,tls:t.tls,staticEncoding:t.staticEncoding,
            caSha256:t.ca===undefined?null:sha(t.ca),processGroups:t.observe??{},
        }])),
    };
    save('start',{utc:startAt,host:HOST,address:IP,plan:configProof,capacity,
        runnerSha256:sha(fs.readFileSync(__filename)),clockTicksPerSecond:/^\d+$/.test(tick??'')?Number(tick):null,
        planned:waves.reduce((n,w)=>n+w.n,0),scope:'GET only; no proxies, redirects, request bodies or persisted cookies'});
    const all=[], completedWaves=[];
    let failure=null;
    const snap=()=>Object.fromEntries(Object.entries(targets).map(([k,v])=>[k,processSnapshot(v.observe)]));
    let before=null;
    try {
        before=snap();
        const remaining=()=>Math.max(0,Math.min(plan.timeoutMs,Math.floor(time+plan.deadlineMs-performance.now())));
        function row(raw,spec,wave,measured) {
            const meta={wave:wave.id,round:wave.round,label:wave.label,n:wave.n,path:spec.path,measured,status:raw.status,peer:raw.peer,
                ttfbMs:raw.headersAt===null?null:raw.headersAt-raw.startedAt,totalMs:raw.endedAt-raw.startedAt};
            try {
                const {session,...validated}=validBody(raw,spec,targets[wave.label],plan.maxDecodedBytes);
                if (session && plan.profile!=='first') targets[wave.label].cookies.set(spec.path,session);
                return {...meta,result:'PASS',...validated};
            } catch (e) {
                return {...meta,result:'FAIL',error:e.code??'VALIDATION_FAILED',encodedBytes:raw.seen,decodedBytes:null};
            }
        }
        for (const w of waves) {
            const target=targets[w.label];
            if (plan.profile==='repeat' && !target.cookies.has(w.paths[0].path)) {
                check(remaining()>0,'RUN_DEADLINE');
                const prime=row(await timedGet(target,w.paths[0],remaining(),plan.maxEncodedBytes),w.paths[0],w,false);
                all.push(prime);append('requests',prime);
                check(prime.result==='PASS' && target.cookies.has(w.paths[0].path),'NO_PRIVATE_REPEAT_SESSION');
            }
            check(remaining()>0,'RUN_DEADLINE');
            const start=performance.now();
            const raw=await Promise.all(w.paths.map(spec=>timedGet(target,spec,remaining(),plan.maxEncodedBytes,target.cookies.get(spec.path))));
            const wireMs=Math.max(...raw.map(entry=>entry.endedAt))-start;
            const result=raw.map((r,index)=>row(r,w.paths[index],w,true));
            result.forEach(r=>{all.push(r);append('requests',r);});
            const done={id:w.id,label:w.label,n:w.n,round:w.round,wireMs,passed:result.filter(r=>r.result==='PASS').length};
            completedWaves.push(done);append('waves',done);
            check(done.passed===done.n,'ABORTED_AFTER_FAILED_WAVE');
        }
    } catch(error) {failure=error.code??'RUN_FAILED';}
    const after=snap(), measured=all.filter(r=>r.measured);
    const finish={utc:new Date().toISOString(),elapsedMs:performance.now()-time,status:failure?'ABORTED':'COMPLETE',
        failure,attempted:measured.length,passed:measured.filter(r=>r.result==='PASS').length,
        primes:all.length-measured.length,waves:completedWaves.length};
    save('summary',{...finish,results:summarize(completedWaves,all),processes:{before,after},
        note:'TTFB at complete response header; req/s from successful waves only. HTTP bytes are encoded body, not TCP/TLS framing.'});
    save('finish',finish);
    process.stdout.write(JSON.stringify(finish)+'\n');
    if (failure) process.exitCode=1;
}

module.exports={validatePlan,schedule,percentile,summarize,validBody,processSnapshot};
if (require.main===module) main(process.argv.slice(2)).catch(error=>{
    process.stderr.write(String(error.code??'PRECHECK_FAILED')+'\n');
    process.exitCode=1;
});
