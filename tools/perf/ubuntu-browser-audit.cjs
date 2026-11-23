#!/usr/bin/env node
'use strict';

// Browser acceptance on the verified .ub or its private Ubuntu loopback
// listener. No browser profile, trace, HTML, token, cookie or answer is kept.
const assert = require('node:assert/strict');
const { createHash } = require('node:crypto');
const fs = require('node:fs');
const http = require('node:http');
const net = require('node:net');
const path = require('node:path');
const { once } = require('node:events');

const PW = '1.62.1';
const CARD = '/test/passive-voice/theory-passive-voice-present-simple';
const STEP = '/test/word-order/theory-basic-word-order/step';
const THEORY = '/theory/passive-voice/theory-passive-voice-present-simple';
const DICTIONARY = '/api/word-search/uk';
const COMPOSE = '/pl/test/sentence-builder-to-be-a1/step/compose';
const STATE = CARD + '/state';
const PATHS = new Set(['/', CARD, STEP, THEORY, COMPOSE, '/test/present-simple/forms',
  '/test/past-simple/forms', '/test/past-perfect/forms', '/en/test/past-perfect/forms', '/pl/test/past-perfect/forms']);
const PORT_LIMIT = 65535;
const POST_LIMIT = 20;
const ASSET = /^\/(?:build\/assets|css|js|images|img|fonts|vendor)\/[A-Za-z0-9_./-]+\.(?:css|m?js|png|svg|ico|jpe?g|webp|gif|avif|woff2?|ttf|otf)$/i;
const ROOT_ASSET = /^\/[a-z0-9_-]+\.(?:png|svg|ico|webp)$/i;
const VITE = /^\/build\/assets\/[A-Za-z0-9_.-]+-[A-Za-z0-9_-]{8,}\.(?:css|m?js)$/;
const LIVEWIRE = /^\/livewire(?:-[a-z0-9]+)?\/livewire(?:\.min)?\.js$/;
const sha = x => createHash('sha256').update(x).digest('hex');

function options(args) {
  const values = Object.create(null);
  const flags = new Set(['--expect-gzip', '--guest-state']);
  const withValue = new Set(['--origin','--target-ip','--playwright-dir','--receipt','--executable','--locales','--shadow-proof-file']);
  for (let i = 0; i < args.length; i++) {
    const k = args[i];
    if (Object.hasOwn(values,k)) throw Error('duplicate option');
    if (flags.has(k)) values[k] = true;
    else if (withValue.has(k) && args[i+1] && !args[i+1].startsWith('--')) values[k] = args[++i];
    else throw Error('unknown or incomplete option');
  }
  if (process.platform !== 'linux' || process.getuid() === 0) throw Error('run on Ubuntu as an ordinary account');
  if (!values['--origin']) throw Error('--origin is required');
  const address = new URL(values['--origin']);
  if (address.href !== address.origin+'/' || address.username || address.password) throw Error('provide an origin without a path or credentials');
  const port = Number(address.port || (address.protocol === 'https:' ? '443' : '80'));
  const shadow = port > 1023 && port <= PORT_LIMIT
    && ['127.0.0.1','gramlyze.ub'].includes(address.hostname)
    && ['http:','https:'].includes(address.protocol)
    && (values['--target-ip'] === '127.0.0.1' || (address.hostname === '127.0.0.1' && !values['--target-ip']));
  const live = address.protocol === 'https:' && address.hostname === 'gramlyze.ub' && port === 443
    && values['--target-ip'] === '192.168.0.103';
  if (!shadow && !live) throw Error('only verified HTTPS .ub or a private loopback high port is allowed');
  if (values['--guest-state'] && !shadow) throw Error('guest state writes require a private shadow listener');
  const locales = (values['--locales'] ?? 'uk').split(',');
  if (!locales.includes('uk') || new Set(locales).size !== locales.length || locales.some(x=>!['uk','en','pl'].includes(x))) {
    throw Error('choose enabled locales explicitly: uk[,en,pl]');
  }
  for (const name of ['--receipt','--playwright-dir','--executable','--shadow-proof-file']) {
    if (values[name] !== undefined && !path.isAbsolute(values[name])) throw Error('absolute paths are required');
  }
  if (!values['--receipt'] || !values['--playwright-dir']) throw Error('--receipt and --playwright-dir are required');
  const receiptParent = path.dirname(values['--receipt']);
  const stat = fs.lstatSync(receiptParent);
  if (!stat.isDirectory() || stat.isSymbolicLink() || stat.uid !== process.getuid() || (stat.mode & 0o077) !== 0
    || fs.realpathSync(receiptParent) !== receiptParent) throw Error('receipt directory must be private, owned and physical');
  let proof;
  if (values['--shadow-proof-file']) {
    if (!shadow) throw Error('proof file is only for shadow');
    const proofFile = values['--shadow-proof-file'];
    const p = fs.lstatSync(proofFile);
    if (!p.isFile() || p.isSymbolicLink() || p.nlink !== 1 || (p.mode & 0o077) !== 0 || p.uid !== process.getuid()
      || p.size > 129 || fs.realpathSync(proofFile) !== proofFile) throw Error('shadow proof must be a private owned physical file');
    proof = fs.readFileSync(proofFile, 'utf8').trim();
    if (!/^[a-f0-9]{32,128}$/.test(proof)) throw Error('invalid shadow proof');
  }
  if (values['--guest-state'] && !proof) throw Error('isolated guest state requires a verified shadow marker');
  return { address, port, shadow, target:shadow ? '127.0.0.1' : '192.168.0.103', locales, proof,
    receipt:values['--receipt'], pkg:values['--playwright-dir'], executable:values['--executable'],
    expectGzip:Boolean(values['--expect-gzip']), guestState:Boolean(values['--guest-state']) };
}

function permit(address, origin, method, active, postReady, body) {
  if (address.origin !== origin || address.username || address.password || address.hash || /(?:%2f|%5c|%2e)/i.test(address.pathname)) return false;
  if (method === 'POST') {
    if (!postReady || active !== CARD || address.pathname !== STATE || address.search || typeof body !== 'string'
      || Buffer.byteLength(body) > 2 * 1024 * 1024) return false;
    try {
      const data = JSON.parse(body);
      return data?.mode === 'saved-test-js-v2' && (data.state === null || (Array.isArray(data.state?.items) && data.state.items.length <= 500));
    } catch { return false; }
  }
  if (method !== 'GET' && method !== 'HEAD') return false;
  if (address.pathname === DICTIONARY) return address.search === '?q=go';
  if (address.pathname === active && PATHS.has(active)) return !address.search;
  return (ASSET.test(address.pathname) || ROOT_ASSET.test(address.pathname) || LIVEWIRE.test(address.pathname)) && !address.pathname.includes('..');
}

// A per-run localhost proxy constrains actual network connections, including
// attempts by popups/workers and browser background tasks. HTTPS is tunneled
// without terminating it, so Chromium still validates the endpoint certificate.
async function originFence(o) {
  let allowed = 0, rejected = 0;
  const sockets = new Set();
  const track = socket => { sockets.add(socket); socket.once('close', ()=>sockets.delete(socket)); return socket; };
  const proxy = http.createServer((req,res) => {
    let u; try { u = new URL(req.url); } catch {}
    if (o.address.protocol !== 'http:' || !u || u.origin !== o.address.origin || u.username || u.password
      || !['GET','HEAD','POST'].includes(req.method)) { rejected++; res.writeHead(403).end(); return; }
    allowed++;
    const headers = { ...req.headers, host:o.address.host };
    for (const key of ['proxy-authorization','proxy-connection','forwarded','x-forwarded-host','x-forwarded-proto']) delete headers[key];
    const upstream = http.request({hostname:o.target,port:o.port,path:u.pathname+u.search,
      method:req.method,headers,agent:false,timeout:30000}, response=>{
      res.writeHead(response.statusCode ?? 502, response.headers);
      response.pipe(res);
    });
    upstream.on('socket',track);
    upstream.on('timeout',()=>upstream.destroy());
    upstream.on('error',()=>{ if (!res.headersSent) res.writeHead(502); res.end(); });
    req.on('aborted',()=>upstream.destroy());
    req.pipe(upstream);
  });
  proxy.on('connection',track);
  proxy.on('connect',(req,client,head)=>{
    if (o.address.protocol !== 'https:' || req.url !== o.address.hostname+':'+o.port) {
      rejected++; client.end('HTTP/1.1 403 Forbidden\r\nConnection: close\r\n\r\n'); return;
    }
    allowed++;
    const upstream = track(net.connect(o.port,o.target));
    upstream.setTimeout(30000,()=>upstream.destroy());
    upstream.once('error',()=>client.destroy());
    client.once('error',()=>upstream.destroy());
    client.once('close',()=>upstream.destroy());
    upstream.once('close',()=>client.destroy());
    upstream.once('connect',()=>{
      client.write('HTTP/1.1 200 Connection Established\r\n\r\n');
      if (head.length) upstream.write(head);
      upstream.pipe(client); client.pipe(upstream);
    });
  });
  proxy.listen(0,'127.0.0.1');
  await once(proxy, 'listening');
  return { server:'http://127.0.0.1:'+proxy.address().port, allowed:()=>allowed,rejected:()=>rejected,
    close:async()=>{for (const socket of sockets) socket.destroy(); await new Promise(r=>proxy.close(r));} };
}

async function pageHarness(browser,o,active) {
  const context = await browser.newContext({serviceWorkers:'block',ignoreHTTPSErrors:false,
    acceptDownloads:false,viewport:{width:1440,height:880}});
  await context.routeWebSocket('**/*', websocket=>websocket.close());
  const page = await context.newPage();
  context.on('page', popup=>{if (popup!==page) void popup.close().catch(()=>{});});
  page.setDefaultTimeout(14000);
  const cdp = await context.newCDPSession(page);
  const diagnostics = { blockedSameOriginWrites:0, blockedOutsideOrigin:0, pageErrors:0, handlerErrors:0, posts:0 };
  const assets=new Map();
  let armed = false;
  let resetAllowed = false;
  const checkActive = () => page.url() === o.address.origin + active;
  await page.addInitScript(()=>{
    window.__perf31 = { sameOriginCsp:0, otherCsp:0, lcp:null };
    document.addEventListener('securitypolicyviolation', e=>{
      if (e.disposition !== 'enforce') return;
      const blocked = e.blockedURI;
      try {
        if (['inline','eval',''].includes(blocked) || new URL(blocked,location.href).origin===location.origin) window.__perf31.sameOriginCsp++;
        else window.__perf31.otherCsp++;
      } catch {window.__perf31.otherCsp++;}
    });
    try {
      new PerformanceObserver(list=>{window.__perf31.lcp=list.getEntries().at(-1)?.startTime??null;})
        .observe({type:'largest-contentful-paint',buffered:true});
    } catch {}
  });
  await cdp.send('Network.enable',{maxPostDataSize:2 * 1024 * 1024});
  await cdp.send('Fetch.enable',{patterns:[{urlPattern:'*',requestStage:'Request'}]});
  cdp.on('Fetch.requestPaused', event=>{
    void (async()=>{
      const { requestId,request }=event;
      const address = new URL(request.url);
      const writeReady = armed && checkActive();
      let postData=request.postData;
      if (!postData && request.method==='POST' && address.origin===o.address.origin && address.pathname===STATE
        && !address.search && writeReady && request.hasPostData && event.networkId) {
        postData=(await cdp.send('Network.getRequestPostData',{requestId:event.networkId})).postData;
      }
      if (!permit(address,o.address.origin,request.method,active,writeReady,postData)) {
        if (address.origin!==o.address.origin) diagnostics.blockedOutsideOrigin++;
        else if (request.method==='POST') diagnostics.blockedSameOriginWrites++;
        await cdp.send('Fetch.failRequest',{requestId,errorReason:'BlockedByClient'});
        return;
      }
      if (request.method==='POST') {
        const body=JSON.parse(postData);
        if (diagnostics.posts>=POST_LIMIT || (body.state===null && !resetAllowed)) {
          diagnostics.blockedSameOriginWrites++;
          await cdp.send('Fetch.failRequest',{requestId,errorReason:'BlockedByClient'});
          return;
        }
        diagnostics.posts++;
      }
      await cdp.send('Fetch.continueRequest',{requestId});
    })().catch(()=>{
      diagnostics.handlerErrors++;
      void cdp.send('Fetch.failRequest',{requestId:event.requestId,errorReason:'Aborted'}).catch(()=>{});
    });
  });
  page.on('pageerror',()=>diagnostics.pageErrors++);
  page.on('response', response=>{
    const address=new URL(response.url());
    if (address.origin===o.address.origin && VITE.test(address.pathname)) assets.set(address.pathname,response);
  });
  return {context,page,assets,diagnostics,arm:()=>{armed=true;}, allowReset:()=>{resetAllowed=true;}};
}

function wrongThenCorrect(web) {
  return (async()=>{
    const {page}=web;
    const index=await page.evaluate(()=>state.items.findIndex(q=>!isSentenceReorderQuestion(q)&&q.answers.length===1
      &&getActiveOptions(q).some(x=>answerMatchesSlot(q,0,x))&&getActiveOptions(q).some(x=>!answerMatchesSlot(q,0,x))));
    assert(index>=0);
    const q=page.locator('#questions article[data-idx="'+index+'"]');
    await q.locator('[data-options-toggle]').click();
    const buttons=q.locator('button[data-opt]');
    const choices=await buttons.evaluateAll((all,i)=>all.map(el=>answerMatchesSlot(state.items[i],0,el.dataset.opt)),index);
    const wrong=choices.indexOf(false);
    assert(wrong>=0&&choices.includes(true));
    const initial=await page.evaluate(()=>window.__INITIAL_JS_TEST_QUESTIONS__.map(q=>q.uuid));
    await buttons.nth(wrong).click();
    await page.waitForFunction(i=>state.items[i].feedbackMeta?.result==='incorrect'&&!state.items[i].done,index);
    const right=await buttons.evaluateAll((all,i)=>all.findIndex(el=>answerMatchesSlot(state.items[i],0,el.dataset.opt)),index);
    assert(right>=0);
    const saved=page.waitForResponse(r=>r.status()===204&&r.request().method()==='POST'&&new URL(r.url()).pathname===STATE
      &&r.request().postDataJSON().state?.items?.[index]?.done);
    await buttons.nth(right).click();
    await page.waitForFunction(i=>state.items[i].done&&state.answered===1&&state.correct===0,index);
    await saved;
    await page.addInitScript(()=>localStorage.clear());
    await page.reload({waitUntil:'domcontentloaded'});
    await page.waitForFunction(i=>state.items[i]?.done&&window.JS_TEST_PERSISTENCE?.saved?.items?.[i]?.done,index);
    assert.deepEqual(await page.evaluate(()=>window.__INITIAL_JS_TEST_QUESTIONS__.map(q=>q.uuid)),initial);
    web.allowReset();
    const reset=page.waitForResponse(r=>r.status()===204&&r.request().method()==='POST'&&new URL(r.url()).pathname===STATE
      &&r.request().postDataJSON().state===null);
    await Promise.all([page.waitForNavigation({waitUntil:'domcontentloaded'}),page.locator('#restart-test').click()]);
    await reset;
    await page.waitForFunction(()=>state.items.length>0&&state.answered===0&&state.correct===0);
    return { wrongThenCorrect:true, serverResumeWithoutLocalStorage:true, reset:true };
  })();
}

async function cardRead(page) {
  const lookup=page.waitForResponse(r=>new URL(r.url()).pathname===DICTIONARY&&new URL(r.url()).search==='?q=go'&&r.ok());
  const input=page.locator('#word-search');
  await input.fill('go');
  const body=await (await lookup).json();
  assert(Array.isArray(body)&&body.some(x=>x.word==='go'&&x.forms?.past?.includes('went')));
  await input.press('Escape');
  return { autocomplete:true, goWent:true };
}

async function audit(browser,o,id,route,locale,selector) {
  const row={id,route,expectedLocale:locale,status:'FAIL',checks:{}};
  const web=await pageHarness(browser,o,route);
  let stage='navigation';
  try {
    const response=await web.page.goto(o.address.origin+route,{waitUntil:'load',timeout:30000});
    row.http=response?.status()??null;
    assert(response?.ok()&&response.url()===o.address.origin+route&&web.page.url()===response.url());
    row.siteProfile=(await response.headerValue('x-site-mode'))??null;
    if (!['production','development'].includes(row.siteProfile)) row.siteProfile=null;
    row.cspHeader=Boolean(await response.headerValue('content-security-policy'));
    const encoding=(await response.headerValue('content-encoding'))??'identity';
    row.documentEncoding=['gzip','identity','br','zstd'].includes(encoding)?encoding:'unrecognized';
    assert.equal(encoding,'identity');
    stage='locale-and-visible-content';
    await web.page.locator(selector).first().waitFor({state:'visible'});
    row.locale=(await web.page.locator('html').getAttribute('lang'))?.split('-')[0]??null;
    assert.equal(row.locale,locale);
    if ([CARD,STEP,COMPOSE].includes(route)||route.endsWith('/forms')) {
      const questions=await web.page.evaluate(()=>{const q=window.__INITIAL_JS_TEST_QUESTIONS__??[];
        const ids=q.map(x=>x.uuid);return {total:q.length,uuidPresent:ids.filter(Boolean).length,unique:new Set(ids).size};});
      row.questions=questions;
      assert(questions.total>0 && questions.uuidPresent===questions.total && questions.unique===questions.total);
    }
    if (route===CARD) {
      stage='card-runtime';
      assert(await web.page.evaluate(()=>Boolean(window.Livewire&&window.Alpine)));
      row.checks.livewireAlpine=true;
      stage='autocomplete';
      Object.assign(row.checks,await cardRead(web.page));
      if (o.guestState) {
        stage='guest-shadow-fence';
        assert.equal(await response.headerValue('x-gramlyze-perf31-isolated'),o.proof);
        assert(await web.page.evaluate(()=>window.__IS_ADMIN__===false&&state.answered===0&&state.correct===0
          &&(!window.JS_TEST_PERSISTENCE?.saved||!window.JS_TEST_PERSISTENCE.saved.__meta?.started)
          &&new URL(window.JS_TEST_PERSISTENCE.endpoint,location.href).href===location.href+'/state'));
        web.arm();
        stage='guest-wrong-correct-resume-reset';
        Object.assign(row.checks,await wrongThenCorrect(web));
      }
    } else if (route===STEP) {
      stage='step-options-and-hint-control';
      const button=web.page.locator('#question-card [data-options-toggle]');
      await button.waitFor({state:'visible'});
      if ((await button.getAttribute('aria-expanded'))!=='true') await button.click();
      await web.page.locator('#question-card button[data-opt]').first().waitFor({state:'visible'});
      assert(await web.page.locator('#question-card #help').isVisible());
      row.checks.stepOptions=true;
      row.checks.hintControl=true;
    } else if (route===COMPOSE) {
      row.checks.composeTokenBank=true;
      if (o.shadow) {
        stage='compose-token-move';
        await web.page.locator('#compose-bank [data-bank-token-id]').first().click();
        const chosen=web.page.locator('#compose-answer-zone [data-answer-token-id]');
        await chosen.first().waitFor({state:'visible'});
        await chosen.first().click();
        await chosen.first().waitFor({state:'detached'});
        row.checks.composeTokenMove=true;
      }
    }
    stage='vite-and-csp';
    const assets=[];
    for (const [name,res] of web.assets) {
      const content=(await res.headerValue('content-type'))??'';
      const assetEncoding=(await res.headerValue('content-encoding'))??'identity';
      const css=name.endsWith('.css');
      assert(res.ok() && (css ? /^text\/css(?:;|$)/i : /^(?:application|text)\/javascript(?:;|$)/i).test(content));
      if (o.expectGzip) assert.equal(assetEncoding,'gzip');
      const payload=await res.body();
      assert(payload.length>0);
      assets.push({file:name,decodedBytes:payload.length,decodedSha256:sha(payload),encoding:['gzip','identity','br','zstd'].includes(assetEncoding)?assetEncoding:'unrecognized'});
    }
    assert(assets.some(x=>x.file.endsWith('.css'))&&assets.some(x=>/\.m?js$/.test(x.file)));
    row.assets=assets;
    const performance=await web.page.evaluate(()=>({
      fcp:performance.getEntriesByName('first-contentful-paint')[0]?.startTime??null,
      lcp:window.__perf31.lcp,sameOriginCsp:window.__perf31.sameOriginCsp,otherCsp:window.__perf31.otherCsp
    }));
    row.navigation=performance;
    assert.equal(performance.sameOriginCsp+web.diagnostics.pageErrors+web.diagnostics.handlerErrors,0);
    row.status='PASS';
  } catch {
    // Playwright assertion messages may include selectors, response text, or token/answer data.
    // Preserve only the named stage, not raw error messages or event bodies.
    row.failedStage=stage;
  } finally {
    row.diagnostics={...web.diagnostics};
    await web.context.close();
  }
  return row;
}

async function main() {
  const o=options(process.argv.slice(2)), fd=fs.openSync(o.receipt,'wx',0o600);
  const receipt={schema:'gramlyze-perf31-browser-audit/v1',origin:o.address.origin,target:o.target,
    mode:o.shadow?'loopback-shadow':'verified-live-readonly',browserVersion:null,locales:o.locales,rows:[],pass:false};
  let browser, proxy;
  try {
    const pkg=fs.realpathSync(o.pkg);
    assert.equal(JSON.parse(fs.readFileSync(path.join(pkg,'package.json'),'utf8')).version,PW);
    const {chromium}=require(pkg);
    proxy=await originFence(o);
    browser=await chromium.launch({headless:true,chromiumSandbox:true,executablePath:o.executable,
      proxy:{server:proxy.server,bypass:'<-loopback>'},
      args:['--disable-background-networking','--disable-sync','--disable-quic','--force-webrtc-ip-handling-policy=disable_non_proxied_udp']});
    receipt.browserVersion=browser.version();
    receipt.playwright=PW;
    const cases=[
      ['home','/','uk','h1'],
      ['present-simple','/test/present-simple/forms','uk','#questions article[data-idx="0"]'],
      ['past-simple','/test/past-simple/forms','uk','#questions article[data-idx="0"]'],
      ['past-perfect','/test/past-perfect/forms','uk','#questions article[data-idx="0"]'],
      ['word-order-step',STEP,'uk','#question-card article'],
      ['passive-card',CARD,'uk','#questions article[data-idx="0"]'],
      ['native-theory',THEORY,'uk','.theory-native-block'],
    ];
    if (o.locales.includes('en')) cases.push(['english-card','/en/test/past-perfect/forms','en','#questions article[data-idx="0"]']);
    if (o.locales.includes('pl')) cases.push(['polish-card','/pl/test/past-perfect/forms','pl','#questions article[data-idx="0"]'],
      ['polish-compose',COMPOSE,'pl','#compose-bank [data-bank-token-id]']);
    for (const row of cases) receipt.rows.push(await audit(browser,o,...row));
    receipt.pass=receipt.rows.every(x=>x.status==='PASS')&&proxy.allowed()>0;
  } catch {
    receipt.failedStage=browser?'browser-context':'browser-launch-or-dependency';
  } finally {
    try {await browser?.close();}catch{receipt.pass=false; receipt.failedStage='browser-close';}
    if (proxy) {
      receipt.net={permittedProxyRequestsOrTunnels:proxy.allowed(),blockedByProxy:proxy.rejected()};
      await proxy.close();
    }
    fs.writeFileSync(fd,JSON.stringify(receipt,null,2)+'\n'); fs.closeSync(fd);
  }
  console.log(JSON.stringify({pass:receipt.pass,mode:receipt.mode,pages:receipt.rows.map(({id,status,failedStage})=>({id,status,failedStage})),
    failure:receipt.failedStage??null}));
  if (!receipt.pass) process.exitCode=1;
}

if (require.main===module) main().catch(error=>{
  // Setup validation errors are independent of browser/network payloads.
  if (error instanceof Error && !error.code) console.error(error.message);
  else console.error('browser audit setup failed');
  process.exitCode=1;
});
module.exports={permit};
