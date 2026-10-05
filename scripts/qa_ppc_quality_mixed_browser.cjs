/* Finite real .loc acceptance. No production, credentials, or saved browser state. */
'use strict';
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const output = process.argv[2];
if (!output) throw new Error('Supply a private evidence directory.');
fs.mkdirSync(output, { recursive: false });
const base = 'http://gramlyze.loc';
const presentationRows = ['builder', 'mixed'].flatMap(scope => JSON.parse(fs.readFileSync(path.join(__dirname, '..', 'database/content-patches/ppc-compose-presentation', `${scope}.json`), 'utf8')).questions);
const presentationByUuid = new Map(presentationRows.map(row => [row.persistent_uuid, row]));
const report = { at: new Date().toISOString(), base, states: [], regressions: [], warnings: [], violations: [], guestStatePosts: 0, blockedNonGet: 0, blockedExternalGet: 0 };
const assert = (ok, message) => { if (!ok) report.violations.push(message); };
async function guardContext(context) {
  await context.route('**/*', route => {
    const request = route.request();
    const url = new URL(request.url());
    // Fresh anonymous contexts only. This audited endpoint stores this guest's
    // session snapshot, not a user/progress DB row; every other POST is blocked.
    if (request.method() === 'POST' && url.hostname === 'gramlyze.loc'
      && /^\/(?:en\/|pl\/)?test\/past-perfect-continuous\/(?:forms|negatives|questions|time-expressions)\/state$/.test(decodeURIComponent(url.pathname))) {
      const payload = request.postDataJSON();
      if (payload && Object.keys(payload).sort().join(',') === 'mode,state'
        && typeof payload.mode === 'string' && (payload.state === null || typeof payload.state === 'object')) {
        report.guestStatePosts++;
        return route.continue();
      }
    }
    if (request.method() !== 'GET') { report.blockedNonGet++; return route.abort(); }
    if (url.hostname !== 'gramlyze.loc') { report.blockedExternalGet++; return route.abort(); }
    return route.continue();
  });
}

(async () => {
  const browser = await chromium.launch({ executablePath: process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
  try {
    for (const locale of ['uk', 'en', 'pl']) for (const topic of ['forms', 'negatives', 'questions', 'time-expressions']) {
      const key = `${topic}-${locale}`;
      const context = await browser.newContext({ viewport: { width: 1440, height: 950 } });
      await guardContext(context);
      const page = await context.newPage();
      const errors = [];
      page.on('pageerror', e => errors.push(e.message.slice(0, 300)));
      const url = `${base}${locale === 'uk' ? '' : '/' + locale}/test/past-perfect-continuous/${topic}`;
      const row = { key, url, errors, dataCoverage: {}, realInteractions: [], realHelpActions: [] };
      try {
        const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
        row.status = response.status(); row.finalUrl = page.url();
        assert(row.status === 200, `${key}: HTTP ${row.status}`);
        await page.waitForFunction(() => typeof state !== 'undefined' && state.items.length > 0, null, { timeout: 45000 });
        const data = await page.evaluate(() => state.items.map((q, index) => ({ index, uuid: q.uuid, level: q.level, kind: isSentenceReorderQuestion(q) ? 'reorder' : String(q.type) === '4' ? 'builder' : 'gap', markers: q.markers, answers: q.answers, hints: q.verb_hints, generalHint: q.hint, question: q.question, template: q.reorder_template_constraint, tokens: q.reorder_tokens, target: q.reorder_answer, compose: q.compose_source_text })));
        row.count = data.length;
        for (const q of data.filter(q => q.kind === 'builder')) {
          const authored = presentationByUuid.get(q.uuid)?.locales[locale];
          assert(authored && q.question === authored.display_source, `${key}/${q.uuid}: heading is not the finite sentence-only source`);
          assert(await page.locator(`article[data-idx="${q.index}"] [data-authored-compose-hint]`).count() === 0, `${key}/${q.uuid}: help leaked before clicking`);
        }
        row.dataCoverage = data.reduce((out, q) => { const entry = out[q.level] ||= { builder: 0, gap: 0, reorder: 0 }; entry[q.kind]++; return out; }, {});
        assert(data.length === 84, `${key}: expected 84 interleaved questions, got ${data.length}`);
        for (const level of ['A1', 'A2', 'B1', 'B2', 'C1', 'C2']) {
          const group = data.filter(q => q.level === level);
          const counts = row.dataCoverage[level];
          assert(group.length === 14 && counts.builder === 7 && counts.gap === 4 && counts.reorder === 3, `${key}/${level}: balanced type distribution differs`);
          assert(new Set(group.map(q => q.kind)).size === 3, `${key}/${level}: missing type`);
          assert(group.every((q, i) => i < 2 || !(q.kind === group[i - 1].kind && q.kind === group[i - 2].kind)), `${key}/${level}: long type run`);
          for (const kind of ['gap', 'builder', 'reorder']) {
            const q = group.find(q => q.kind === kind);
            const card = page.locator(`article[data-idx="${q.index}"]`);
            await card.scrollIntoViewIfNeeded();
            if (kind === 'builder') {
              const help = card.locator('.help-btn');
              await help.click();
              assert(await card.locator('[data-authored-compose-hint]').innerText() === q.generalHint, `${key}/${q.uuid}: clicked authored help differs`);
              assert(await help.getAttribute('aria-expanded') === 'true', `${key}/${q.uuid}: help not expanded`);
              await help.click();
              assert(await card.locator('[data-authored-compose-hint]').count() === 0, `${key}/${q.uuid}: help failed to close`);
              row.realHelpActions.push({ level, openedAndClosed: true });
            }
            if (kind === 'reorder') {
              const order = await page.evaluate(index => {
                const q = state.items[index];
                const remaining = new Set(q.reorder_tokens.map((_, i) => i));
                const order = []; let rest = q.reorder_answer.trim();
                while (remaining.size) {
                  const i = [...remaining].find(i => rest === q.reorder_tokens[i] || rest.startsWith(q.reorder_tokens[i] + ' '));
                  if (i === undefined) throw new Error('Reorder bank cannot reconstruct target');
                  order.push(i); rest = rest.slice(q.reorder_tokens[i].length).trim(); remaining.delete(i);
                }
                return order;
              }, q.index);
              for (const tokenIndex of order) await card.locator(`[data-reorder-action="add"][data-reorder-token-index="${tokenIndex}"]`).click();
              await card.locator('[data-reorder-action="check"]').click();
            } else {
              for (const answer of q.answers) {
                const toggle = card.locator('[data-options-toggle]');
                if (await toggle.count() && await toggle.getAttribute('aria-expanded') === 'false') await toggle.click();
                // Exact data-attribute matching avoids selecting longer distractors.
                await card.locator(`button[data-opt=${JSON.stringify(answer)}]`).first().click();
              }
            }
            const result = await page.evaluate(i => ({ done: state.items[i].done, feedback: state.items[i].feedback }), q.index);
            assert(result.done && result.feedback === 'correct', `${key}/${level}/${kind}: completion failed`);
            row.realInteractions.push({ level, kind, ...result });
          }
        }
        for (const q of data) {
          assert(q.answers.length > 0 && q.markers.length === q.answers.length, `${key}/${q.uuid}: marker count`);
          if (q.kind === 'builder') {
            assert(q.generalHint && (locale === 'uk' ? /[А-Яа-яІіЇїЄє]/.test(q.generalHint) : !/[А-Яа-яІіЇїЄє]/.test(q.generalHint)), `${key}/${q.uuid}: localized lexical hint missing`);
            assert(await page.locator(`article[data-idx="${q.index}"] [data-authored-compose-hint]`).count() === 0, `${key}/${q.uuid}: closed help appeared during answer`);
          } else assert(q.markers.every(marker => q.hints?.[marker]), `${key}/${q.uuid}: marker hint missing`);
          if (q.kind === 'reorder') assert(q.template && q.tokens.length && q.target, `${key}/${q.uuid}: visible order constraint missing`);
          if (q.kind === 'builder') assert(q.compose && (locale === 'uk' ? /[А-Яа-яІіЇїЄє]/.test(q.question) : !/[А-Яа-яІіЇїЄє]/.test(q.question)), `${key}/${q.uuid}: prompt locale mismatch`);
        }
        const beforeReload = await page.evaluate(() => ({ answered: state.answered, correct: state.correct }));
        await page.reload({ waitUntil: 'domcontentloaded', timeout: 45000 });
        await page.waitForFunction(expected => typeof state !== 'undefined' && state.answered === expected, beforeReload.answered, { timeout: 45000 });
        row.reload = await page.evaluate(() => ({ answered: state.answered, correct: state.correct }));
        assert(JSON.stringify(row.reload) === JSON.stringify(beforeReload), `${key}: progress reload differs`);
        const reloadedTitles = await page.evaluate(() => state.items.filter(q => String(q.type) === '4').map(q => ({uuid: q.uuid, question: q.question})));
        assert(reloadedTitles.every(q => q.question === presentationByUuid.get(q.uuid)?.locales[locale]?.display_source), `${key}: stale full instructions returned after reload`);
        await page.locator('#restart-test').click();
        await page.waitForFunction(() => typeof state !== 'undefined' && state.items.length === 84 && state.answered === 0, null, { timeout: 45000 });
        row.retryClean = await page.evaluate(() => state.items.every(q => !q.done) && state.correct === 0);
        assert(row.retryClean, `${key}: retry is not clean`);
        const firstBuilder = page.locator(`article[data-idx="${data.find(q => q.kind === 'builder').index}"]`);
        await firstBuilder.scrollIntoViewIfNeeded();
        await page.screenshot({ path: path.join(output, `${key}-desktop.png`), fullPage: false });
        await page.setViewportSize({ width: 390, height: 844 });
        await firstBuilder.scrollIntoViewIfNeeded();
        await page.evaluate(async () => { await document.fonts.ready; await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))); });
        await page.waitForFunction(() => document.documentElement.scrollWidth <= innerWidth + 2, null, { timeout: 5000 }).catch(() => {});
        await page.screenshot({ path: path.join(output, `${key}-mobile.png`), fullPage: false });
        row.mobileOverflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
        row.mobileBounds = await page.evaluate(() => ({ viewport: innerWidth, document: document.documentElement.scrollWidth,
          overflowing: [...document.querySelectorAll('body *')].filter(e => e.getBoundingClientRect().right > innerWidth + 2).slice(0, 8)
            .map(e => ({ tag: e.tagName, id: e.id, text: e.textContent.slice(0, 80), right: e.getBoundingClientRect().right })) }));
        row.mobileLearningOverflow = await page.evaluate(() => [...document.querySelectorAll('#questions, #questions article, #questions input, #questions button, #questions [data-authored-compose-hint], #questions .verb-hint')]
          .filter(e => e.getClientRects().length && e.getBoundingClientRect().right > innerWidth + 2)
          .map(e => ({ tag: e.tagName, id: e.id, right: e.getBoundingClientRect().right })));
        assert(row.mobileLearningOverflow.length === 0, `${key}: learning controls overflow`);
        if (row.mobileOverflow) report.warnings.push(`${key}: existing shell/random-shapes document overflow; separately recorded, not repaired by this content task`);
        assert(errors.length === 0, `${key}: console/page errors`);
      } catch (error) { report.violations.push(`${key}: ${String(error.message).slice(0, 500)}`); }
      report.states.push(row); await context.close();
      fs.writeFileSync(path.join(output, 'progress.json'), JSON.stringify(report, null, 2));
      process.stdout.write(`${key}: ${report.violations.length} total violations; ${report.violations.filter(v => v.startsWith(key + ':') || v.startsWith(key + '/')).join('; ')}\n`);
    }
    for (const route of ['/test/present-perfect/forms', '/test/future-perfect/forms', '/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms']) {
      const context = await browser.newContext({ viewport: { width: 1365, height: 900 } }); const page = await context.newPage();
      await guardContext(context);
      const row = { url: base + route, errors: [] }; page.on('pageerror', e => row.errors.push(e.message.slice(0, 250)));
      try {
        row.status = (await page.goto(row.url, { waitUntil: 'domcontentloaded', timeout: 45000 })).status();
        assert(row.status === 200, `${route}: regression HTTP ${row.status}`);
        if (route.startsWith('/test/')) { await page.waitForFunction(() => typeof state !== 'undefined' && state.items.length, null, { timeout: 45000 }); row.questions = await page.evaluate(() => state.items.length); }
        else { row.practice = await page.locator('[x-data^="theoryPracticeSet"]').count(); assert(row.practice > 0, `${route}: no practice`); }
        await page.screenshot({ path: path.join(output, `regression-${report.regressions.length}.png`), fullPage: false });
        assert(row.errors.length === 0, `${route}: page error`);
      } catch (e) { report.violations.push(`${route}: ${e.message.slice(0, 400)}`); }
      report.regressions.push(row); await context.close();
    }
  } finally { await browser.close(); fs.writeFileSync(path.join(output, 'result.json'), JSON.stringify(report, null, 2)); }
  process.stdout.write(JSON.stringify({ states: report.states.length, regressions: report.regressions.length, violations: report.violations.length }) + '\n');
  if (report.violations.length) process.exitCode = 1;
})().catch(error => { process.stderr.write(error.stack + '\n'); process.exitCode = 1; });
