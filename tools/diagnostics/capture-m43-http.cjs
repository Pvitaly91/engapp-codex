'use strict';
// Independent guest GET baseline and comparison. Never follows an off-.loc redirect.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const {metadata} = require('./seo-m28-local.cjs');
const prior = require('./capture-m42-design-http.cjs');
const BASE = prior.BASE, PRIVATE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-local';
const targetPaths = ['past-perfect-vs-past-perfect-continuous', 'stative-verbs', 'used-to-would'].map(s => '/theory/tenses/' + s);
const controls = [...prior.targetPaths, ...prior.referencePaths,
 '/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms',
 '/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives',
 '/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions',
 '/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions',
 ...['en', 'pl'].flatMap(l => targetPaths.map(p => '/' + l + p)),
 '/', '/theory', '/courses/english-grammar-theory',
 ...targetPaths.map(p => '/courses/english-grammar-theory/lesson' + p.replace('/theory', '')),
 '/test/tenses/stative-verbs'];
const routes = [...targetPaths, ...controls];
assert.equal(new Set(routes).size, routes.length);
const sha = v => crypto.createHash('sha256').update(v).digest('hex');
async function capture() {
 const rows = [];
 for (const route of routes) {
  console.log(JSON.stringify({checking: route}));
  const result = await prior.guestGet(route);
  if (result.error) { rows.push({path: route, ...result}); continue; }
  const {response, body, attempts, finalUrl} = result, dom = new JSDOM(body);
  try {
   rows.push({path: route, at: new Date().toISOString(), status: response.status, attempts, finalUrl,
    contentType: response.headers.get('content-type'), xRobots: response.headers.get('x-robots-tag'),
    meta: metadata(dom.window.document), jsonLd: prior.jsonLd(dom.window.document), learner: prior.learner(dom.window.document),
    links: targetPaths.includes(route) ? [...dom.window.document.querySelectorAll('[data-theory-main] a[href]')].map(n => ({href: n.getAttribute('href'), text: n.textContent.replace(/\s+/gu, ' ').trim()})) : null});
  } finally { dom.window.close(); }
 }
 const extras = {};
 for (const route of ['/robots.txt', '/sitemap.xml']) {
  const result = await prior.guestGet(route, route.endsWith('xml') ? 'application/xml' : 'text/plain');
  if (result.error) { extras[route] = result; continue; }
  const {response, body, attempts, finalUrl} = result;
  extras[route] = {status: response.status, attempts, finalUrl, sha256: sha(body)};
  if (route.endsWith('xml') && response.status === 200) {
   const dom = new JSDOM(body, {contentType: 'text/xml'}), urls = [...dom.window.document.querySelectorAll('loc')].map(n => n.textContent);
   dom.window.close(); extras[route].count = urls.length; extras[route].orderedSha256 = sha(JSON.stringify(urls));
  }
 }
 return {base: BASE, at: new Date().toISOString(), rows, extras,
  pass: rows.every(r => r.status === 200 && !r.error) && Object.values(extras).every(r => r.status === 200 && !r.error)};
}
function compare(before, after) {
 const expected = JSON.parse(fs.readFileSync(path.join(PRIVATE, 'metadata-expectations-before-v1.json')));
 assert.equal(before.pass, true); assert.equal(after.pass, true);
 assert.deepEqual(after.rows.map(r => r.path), routes);
 for (const row of after.rows) {
  const old = before.rows.find(r => r.path === row.path);
  for (const key of ['status', 'finalUrl', 'contentType', 'xRobots']) assert.deepEqual(row[key], old[key], row.path + ':' + key);
  if (targetPaths.includes(row.path)) {
   const anticipated = expected.rows.find(r => r.path === row.path);
   assert.ok(anticipated, 'Pre-apply expectation exists');
   assert.deepEqual(row.meta, anticipated.after_meta, 'Independent master-derived metadata ' + row.path);
   assert.equal(row.jsonLd.length, 1); assert.equal(row.jsonLd[0].valid, true);
   assert.deepEqual(row.jsonLd[0].value, anticipated.after_json_ld_value, 'Independent JSON-LD ' + row.path);
   for (const key of ['title', 'h1', 'canonical', 'robots', 'ogTitle', 'twitterTitle']) assert.deepEqual(row.meta[key], old.meta[key], row.path + ':' + key);
   for (const id of old.learner.anchors.filter(id => /^block-\d+$/u.test(id))) assert.ok(row.learner.anchors.includes(id), 'Old DB anchor ' + id);
  } else for (const key of ['meta', 'jsonLd', 'learner']) assert.deepEqual(row[key], old[key], 'Unchanged control ' + row.path + ':' + key);
 }
 for (const route of Object.keys(before.extras)) for (const key of ['status', 'finalUrl', 'sha256', 'count', 'orderedSha256']) assert.deepEqual(after.extras[route][key], before.extras[route][key], route + ':' + key);
 return {pass: true, targets: targetPaths.length, controls: controls.length, sitemapExact: true};
}
async function run() {
 const [label, beforeName] = process.argv.slice(2); assert.match(label, /^(before|after|after-final)-v[1-9][0-9]*$/u);
 const result = await capture(), file = path.join(PRIVATE, label + '-http.json'); fs.mkdirSync(PRIVATE, {recursive: true});
 fs.writeFileSync(file, JSON.stringify(result, null, 2) + '\n', {flag: 'wx'});
 const checked = beforeName && result.pass ? compare(JSON.parse(fs.readFileSync(path.join(PRIVATE, beforeName))), result) : null;
 console.log(JSON.stringify({pass: result.pass, file, sha256: sha(fs.readFileSync(file)), rows: result.rows.length, checked,
  errors: result.rows.filter(r => r.status !== 200 || r.error).map(r => ({path: r.path, status: r.status, error: r.error}))}));
 if (!result.pass) process.exitCode = 1;
}
if (require.main === module) run().catch(e => {console.error(e.stack); process.exitCode = 1;});
module.exports = {BASE, PRIVATE, targetPaths, controls, routes, capture, compare};
