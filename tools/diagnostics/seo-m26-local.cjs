'use strict';
// Fixed local GET-only M26 acceptance; no deployment, unlock or test-answer writes.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const {metadata} = require('./seo-m11-local.cjs');
const ROOT = path.resolve(__dirname, '../..');
const BASE = 'http://gramlyze.loc';
const master = JSON.parse(fs.readFileSync(path.join(ROOT, 'docs/content/m26-past-perfect-continuous-detail-master.v1.json'), 'utf8'));
const controls = ['/theory/tenses/past-simple-vs-past-continuous', '/theory/tenses/present-perfect/present-perfect-forms',
    '/theory/tenses/narrative-tenses'];
const course = '/courses/english-grammar-theory/lesson/tenses/past-perfect-continuous/past-perfect-continuous-forms';
const targets = master.targets.map(t => t.expected_theory_path);
const paths = [...targets, ...controls, course, '/sitemap.xml'];
const sha = s => crypto.createHash('sha256').update(s).digest('hex');
const norm = s => s.replace(/\s+/gu, ' ').trim();
const document = html => new JSDOM(html).window.document;
const text = html => norm(document(html).body.textContent);
function leaves(value, top = true) {
    if (typeof value === 'string') return [text(value)];
    if (Array.isArray(value)) return value.flatMap(x => leaves(x, false));
    if (value && typeof value === 'object') return Object.entries(value)
        .filter(([k]) => !(top && k === 'title') && !['color', 'url'].includes(k)).flatMap(([, v]) => leaves(v, false));
    return [];
}
function orderedFidelity(actual, expected, label) {
    let from = 0;
    for (const part of expected) {
        const at = actual.indexOf(part, from);
        assert.ok(at >= 0, label + ' missing/reordered: ' + part);
        from = at + part.length;
    }
}
function proof(html, urlPath, plan, applied) {
    const d = document(html); const main = d.querySelector('[data-theory-main]') || d.querySelector('[data-theory-lesson-content]') || d.body;
    const row = {path: urlPath, metadata: metadata(d), textSha256: sha(norm(main.textContent)),
        ids: [...d.querySelectorAll('[id]')].map(n => n.id), links: [...main.querySelectorAll('a[href]')].map(n => n.getAttribute('href'))};
    const learner = main.cloneNode(true);
    learner.querySelectorAll('script,style').forEach(n=>n.remove());
    row.learnerSha256 = sha(norm(learner.textContent));
    row.duplicateIds = [...new Set(row.ids.filter((id,i)=>row.ids.indexOf(id)!==i))];
    const ti = targets.indexOf(urlPath);
    if (ti >= 0) {
        const t = master.targets[ti]; const snapshot = plan.pages[t.identity]; row.basics = [];
        for (const b of snapshot.blocks.filter(b => b.locale === 'uk' && ['forms-grid', 'usage-panels', 'comparison-table', 'summary-list', 'lesson-rule-cards'].includes(b.type))) {
            const node = d.getElementById('block-' + b.id); assert.ok(node, 'Missing full basic ' + b.id);
            assert.equal(node.closest('details'), null, 'Basic is hidden');
            node.querySelectorAll('input[name="_token"]').forEach(n => n.remove());
            row.basics.push({id: b.id, html: norm(node.outerHTML), text: norm(node.textContent)});
        }
        row.details = [...main.querySelectorAll('[data-theory-native-extension] > details')].map(n => ({
            key: n.parentElement.dataset.theorySection, closed: !n.open, id: n.querySelector('section').id, text: norm(n.querySelector('section').textContent)
        }));
        row.tasks = [...main.querySelectorAll('.practice-list > li')].map(n => ({id: n.id,
            prompt: norm(n.querySelector('.question-text').textContent), key: norm(n.querySelector('.answer-body').textContent),
            visibleOutsideDetails: !n.parentElement.closest('details')}));
        if (applied) {
            const expected = t.blocks.filter(b => b.progressive_v1);
            assert.equal(row.details.length, expected.length);
            for (const [i, item] of expected.entries()) {
                assert.equal(row.details[i].key, item.progressive_v1.key); assert.ok(row.details[i].closed);
                orderedFidelity(row.details[i].text, leaves(item.native_data), item.progressive_v1.key);
            }
            assert.equal(row.tasks.length, ti === 0 ? 0 : 6);
            if (t.practice_insert) {
                const original = document(t.practice_insert.body_html);
                for (const task of row.tasks) {
                    const n = original.getElementById(task.id);
                    assert.equal(task.prompt, norm(n.querySelector('.question-text').textContent));
                    assert.equal(task.key, norm(n.querySelector('.answer-body').textContent));
                    assert.ok(task.visibleOutsideDetails);
                }
            }
        } else { assert.equal(row.details.length, 0); }
    }
    d.defaultView.close(); return row;
}
async function capture(dir, label, plan, applied) {
    const out = {at: new Date().toISOString(), base: BASE, rows: []};
    for (const p of paths) {
        const r = await fetch(BASE + p, {redirect: 'manual', headers: {Accept: p.endsWith('.xml') ? 'application/xml' : 'text/html'},
            signal: AbortSignal.timeout(60000)});
        const html = await r.text();
        const row = {path: p, status: r.status, location: r.headers.get('location'), contentType: r.headers.get('content-type'), robotsHeader: r.headers.get('x-robots-tag')};
        if (p === '/sitemap.xml') {
            const xml = new JSDOM(html, {contentType: 'text/xml'}); row.orderedLocs = [...xml.window.document.querySelectorAll('loc')].map(n => n.textContent);
            row.sha256 = sha(JSON.stringify(row.orderedLocs)); xml.window.close();
        } else {
            assert.equal(r.status, 200, p + ' status');
            Object.assign(row, proof(html, p, plan, applied));
            if (targets.includes(p)) assert.deepEqual(row.metadata.canonical.map(u => new URL(u).pathname), [p], 'Resolver canonical '+p);
        }
        out.rows.push(row);
        console.log(JSON.stringify({captured:p,status:r.status}));
    }
    fs.writeFileSync(path.join(dir, label + '-http.json'), JSON.stringify(out, null, 2), {flag:'wx'});
    console.log(JSON.stringify(out.rows.map(r => ({path:r.path,status:r.status,details:r.details?.length,tasks:r.tasks?.length,locs:r.orderedLocs?.length}))));
    return out;
}
function compare(before, after) {
    assert.deepEqual(before.rows.map(r => r.path), paths); assert.deepEqual(after.rows.map(r => r.path), paths);
    for (const [i, b] of before.rows.entries()) {
        const a = after.rows[i]; for (const key of ['status', 'location', 'contentType', 'robotsHeader', 'metadata', 'orderedLocs', 'sha256', 'duplicateIds'])
            assert.deepEqual(a[key], b[key], b.path + ' unchanged ' + key);
        if (targets.includes(b.path)) assert.deepEqual(a.basics, b.basics, b.path + ' complete basic DOM fidelity');
        if (controls.includes(b.path)) { assert.equal(a.learnerSha256, b.learnerSha256); assert.deepEqual(a.ids, b.ids); assert.deepEqual(a.links, b.links); }
    }
    return {pass: true, basicBlocks: after.rows.reduce((n,r)=>n+(r.basics?.length||0),0), disclosures: after.rows.reduce((n,r)=>n+(r.details?.length||0),0),
        tasks: after.rows.reduce((n,r)=>n+(r.tasks?.length||0),0), orderedSitemap: after.rows.at(-1).sha256};
}
async function browser(dir, label, expected) {
    const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    const b = await chromium.launch({headless:true, executablePath:process.env.CHROMIUM_EXECUTABLE});
    const report = {at:new Date().toISOString(),rows:[],failures:[],fonts:[]};
    try {
        for (const viewport of [{width:1440,height:1000},{width:390,height:844}]) {
            for (const [i, p] of targets.entries()) {
                const c = await b.newContext({viewport}); const page = await c.newPage(); const errors = []; const requests = [];
                page.on('pageerror', e => errors.push(e.message));
                page.on('requestfailed', r => {if (/fonts\.(googleapis|gstatic)\.com/.test(r.url())) report.fonts.push({url:new URL(r.url()).origin,error:r.failure()?.errorText}); else errors.push(new URL(r.url()).pathname+': '+r.failure()?.errorText);});
                page.on('request', r => requests.push({url:r.url(),method:r.method()}));
                await page.route('**/*', route => {
                    const r=route.request(),u=new URL(r.url());
                    if (!['GET','HEAD'].includes(r.method()) || (u.origin!==BASE && !['fonts.googleapis.com','fonts.gstatic.com'].includes(u.hostname))) {
                        report.failures.push({path:u.pathname,reason:'outside-local-readonly'}); return route.abort();
                    } return route.continue();
                });
                const r = await page.goto(BASE+p,{waitUntil:'networkidle'}); assert.equal(r.status(),200);
                for (const basic of expected.rows[i].basics) {
                    assert.equal(norm(await page.locator('#block-'+basic.id).textContent()),basic.text,'Live full basic fidelity');
                    assert.equal(await page.locator('#block-'+basic.id).evaluate(n=>n.closest('details')),null);
                }
                const ds=page.locator('[data-theory-native-extension] > details');
                assert.equal(await ds.count(),i===0?2:3);
                for (const theme of ['light','dark']) {
                    if (theme==='dark') {
                        const toggle=page.getByRole('button',{name:'Увімкнути темну тему',exact:true});
                        if (!await toggle.isVisible()) await page.getByRole('button',{name:'Меню',exact:true}).click();
                        await toggle.click(); await page.waitForFunction(()=>document.documentElement.classList.contains('dark'));
                        const menu=page.getByRole('button',{name:'Меню',exact:true});
                        if (viewport.width<1280) await menu.click();
                    }
                    assert.equal(await page.locator('html').evaluate(n=>n.classList.contains('dark')),theme==='dark');
                    const row={path:p,viewport,theme,closed:await ds.evaluateAll(ns=>ns.every(n=>!n.open)),toggles:0};
                    assert.ok(row.closed);
                    for (let j=0;j<await ds.count();j++) {
                        const d=ds.nth(j),s=d.locator(':scope > summary'); const start=requests.length;
                        await s.click(); assert.ok(await d.evaluate(n=>n.open)); await s.click(); assert.equal(await d.evaluate(n=>n.open),false);
                        await s.focus(); await page.keyboard.press('Enter'); assert.ok(await d.evaluate(n=>n.open));
                        assert.ok(await s.evaluate(n=>n.matches(':focus-visible') && getComputedStyle(n).outlineStyle!=='none'),'Keyboard focus-visible');
                        await page.keyboard.press('Space'); assert.equal(await d.evaluate(n=>n.open),false);
                        assert.equal(requests.length,start,'No click fetch');
                        const actual=norm(await d.locator('section').textContent());
                        assert.equal(actual,expected.rows[i].details[j].text,'Live detail parity');
                        row.toggles++;
                    }
                    await ds.nth(0).locator('summary').click(); await ds.nth(1).locator('summary').click();
                    assert.ok(await ds.nth(0).evaluate(n=>n.open)); assert.ok(await ds.nth(1).evaluate(n=>n.open));
                    await ds.nth(0).locator('summary').click(); await ds.nth(1).locator('summary').click();
                    const tasks=page.locator('.practice-list > li'); assert.equal(await tasks.count(),i===0?0:6);
                    if (i) {
                        assert.ok(await tasks.nth(0).locator('h3').isVisible()); await tasks.nth(0).locator('details > summary').click();
                        assert.ok(await tasks.nth(0).locator('.answer-body').isVisible()); await tasks.nth(0).locator('details > summary').click();
                    }
                    row.overflow = await page.evaluate(()=>Math.max(0,document.documentElement.scrollWidth-innerWidth));
                    row.unclippedLearningOverflow=await page.locator('[data-theory-main]').evaluate(root=>{
                        const clipped=n=>{for(let a=n.parentElement;a;a=a.parentElement){const c=getComputedStyle(a);if(['auto','scroll','hidden','clip'].includes(c.overflowX))return true;}return false;};
                        return [root,...root.querySelectorAll('*')].filter(n=>{const r=n.getBoundingClientRect();return r.width&&(r.right>innerWidth+1||r.left<-1)&&!clipped(n);}).map(n=>({tag:n.tagName,id:n.id}));
                    });
                    assert.deepEqual(row.unclippedLearningOverflow,[],'No new learning-content overflow');
                    if(row.overflow>0) {
                        row.decorativeOverflow=await page.locator('#shell-random-shapes span').evaluateAll(ns=>ns.map(n=>n.getBoundingClientRect().right).filter(r=>r>innerWidth));
                        assert.ok(row.decorativeOverflow.length>0,'Only independently reproduced existing shell decoration may exceed viewport');
                    }
                    row.tables=await page.locator('.theory-table-scroll').evaluateAll(ns=>ns.map(n=>({local:getComputedStyle(n).overflowX,client:n.clientWidth,scroll:n.scrollWidth})));
                    assert.ok(row.tables.every(t=>['auto','scroll'].includes(t.local)));
                    await page.screenshot({path:path.join(dir,label+'-'+i+'-'+viewport.width+'-'+theme+'.png'),fullPage:true,animations:'disabled'});
                    report.rows.push(row);
                }
                await page.reload({waitUntil:'networkidle'}); assert.ok(await ds.evaluateAll(ns=>ns.every(n=>!n.open)));
                const id=expected.rows[i].details[0].id;
                await page.goto(BASE+p+'#'+id,{waitUntil:'networkidle'}); await page.waitForFunction(id=>document.getElementById(id)?.closest('details')?.open,id);
                const beforePrint=await page.locator('[data-theory-details]').evaluateAll(ns=>ns.map(n=>n.open));
                await page.emulateMedia({media:'print'});
                await page.waitForFunction(()=>[...document.querySelectorAll('[data-theory-details]')].every(n=>n.open));
                await page.emulateMedia({media:'screen'});
                await page.waitForFunction(expected=>JSON.stringify([...document.querySelectorAll('[data-theory-details]')].map(n=>n.open))===JSON.stringify(expected),beforePrint);
                assert.deepEqual(errors,[]); await c.close();
            }
        }
        for (const p of targets) {
            const c=await b.newContext({javaScriptEnabled:false}); const page=await c.newPage(); await page.goto(BASE+p);
            const ds=page.locator('[data-theory-native-extension] > details'); assert.equal(await ds.count(),p===targets[0]?2:3);
            await ds.nth(0).locator('summary').click(); assert.ok(await ds.nth(0).locator('section').isVisible()); await c.close();
        }
        const c=await b.newContext(); const page=await c.newPage(); await page.goto(BASE+targets[0]);
        for (const p of targets.slice(1)) {
            const link=page.locator('[data-theory-main] a[href]').filter({hasText:''});
            assert.ok(await link.evaluateAll((ns,p)=>ns.some(n=>new URL(n.href).pathname===p),p),'category → lesson link');
        }
        for (const p of targets.slice(1)) {
            await page.goto(BASE+p,{waitUntil:'networkidle'});
            const testLinks=await page.locator('[data-theory-main] a[href]').evaluateAll(ns=>ns.map(n=>new URL(n.href).pathname).filter(p=>p.startsWith('/test/')));
            assert.ok(testLinks.length>0,'main test links');
            for (const p of [...new Set(testLinks)]) { const r=await page.request.get(BASE+p); assert.equal(r.status(),200,'main test GET'); }
        }
        await page.goto(BASE+course,{waitUntil:'networkidle'});
        const lock=page.locator('[data-theory-lesson-lock]'),content=page.locator('[data-theory-lesson-content]');
        report.course={url:course,locked:await lock.isVisible(),contentVisible:await content.isVisible(),details:await page.locator('[data-theory-native-extension]').count()};
        assert.ok(report.course.locked); assert.equal(report.course.contentVisible,false);
        assert.equal(report.course.details,3,'SSR course copy uses same native renderer; no unlock bypass');
        await c.close(); assert.deepEqual(report.failures,[]); report.pass=true;
        report.strictDocumentOverflowPass=report.rows.every(r=>r.overflow===0);
    } finally { await b.close(); fs.writeFileSync(path.join(dir,label+'-browser.json'),JSON.stringify(report,null,2),{flag:'wx'}); }
    console.log(JSON.stringify({pass:report.pass,strictDocumentOverflowPass:report.strictDocumentOverflowPass,states:report.rows.length,course:report.course,fontFailures:report.fonts.length}));
}
if (require.main===module) {
    const [mode,dir,label,planPath]=process.argv.slice(2); assert.ok(['before','after','browser'].includes(mode)); assert.match(label,/^[a-z0-9-]+$/);
    fs.mkdirSync(dir,{recursive:true}); const plan=JSON.parse(fs.readFileSync(planPath,'utf8'));
    (async()=>{
        if (mode==='browser') return browser(dir,label,JSON.parse(fs.readFileSync(path.join(dir,'after-http.json'),'utf8')));
        const out=await capture(dir,label,plan,mode==='after');
        if(mode==='after') console.log(JSON.stringify(compare(JSON.parse(fs.readFileSync(path.join(dir,'acceptance-before-http.json'),'utf8')),out)));
    })().catch(e=>{console.error(e);process.exitCode=1;});
}
module.exports={paths,proof,compare,leaves,orderedFidelity};
