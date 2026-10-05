/* Local read-only presentation acceptance. Artifacts stay outside versioned sources. */
'use strict';
const fs = require('node:fs');
const path = require('node:path');
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = path.resolve(__dirname, '..');
const output = process.argv[2];
if (!output) throw new Error('Provide an absolute private output directory.');
const out = path.resolve(output);
if (!path.isAbsolute(output) || out === root || !out.includes(`${path.sep}storage${path.sep}app${path.sep}`)) {
  throw new Error('Only a private local storage/app artifact directory is allowed.');
}
const alternateOnly = process.argv.includes('--alternate-only');
const retryEnPl = process.argv.includes('--retry-en-pl');
if (retryEnPl && !alternateOnly) throw new Error('EN/PL retry is restricted to alternate-only acceptance.');
const allowGuestFixture = process.argv.includes('--allow-alternate-guest-fixture');
const base = 'http://gramlyze.loc';
const browserExecutable = process.env.CHROMIUM_EXECUTABLE || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
if (!fs.existsSync(browserExecutable)) throw new Error('Provide an already installed CHROMIUM_EXECUTABLE; do not download a browser.');
const inventory = JSON.parse(fs.readFileSync(path.join(root, 'docs/reports/past-perfect-continuous-quality-inventory.json'), 'utf8'));
const projections = ['builder', 'mixed'].flatMap(scope => JSON.parse(fs.readFileSync(path.join(root, `database/content-patches/ppc-compose-presentation/${scope}.json`), 'utf8')).questions);
const presentation = new Map(projections.map(row => [row.persistent_uuid, row]));
const canonical = new Map(inventory.questions.map(row => [row.persistent_uuid, row]));
const banks = inventory.banks;
const locales = retryEnPl ? ['en', 'pl'] : ['uk', 'en', 'pl'];
const modes = [
  {name: 'choose', suffix: '', title: true}, {name: 'manual', suffix: '/manual', title: true},
  {name: 'step', suffix: '/step', title: true}, {name: 'step-manual', suffix: '/step/manual', title: true},
  {name: 'select', suffix: '/select'}, {name: 'input', suffix: '/input'},
  {name: 'step-select', suffix: '/step/select'}, {name: 'step-input', suffix: '/step/input'},
];
const report = {base, scope: 'finite-ppc-compose-presentation-readonly', states: [], alternate: [], violations: [],
  browserExecutable, browserHeadless: true, directConcurrency: 1,
  sourceQuestions: 624, expectedDirectStates: alternateOnly ? 0 : 216, expectedAlternateStates: locales.length,
  alternateLocales: locales, navigationTimeoutMs: 60000,
  guestOnly: true, blockedNonGetRequests: [], progressPostsSent: 0};
const seen = new Set();
const check = (condition, message) => {if (!condition) report.violations.push(message);};
const norm = value => String(value || '').normalize('NFKC').replace(/[’‘ʼ]/g, "'").replace(/[\p{P}\s]+/gu, ' ').trim().toLowerCase();
const localizedPath = (locale, value) => `${locale === 'uk' ? '' : `/${locale}`}${value}`;
const shortError = error => String(error?.message || error).replace(/\s+/g, ' ').slice(0, 500);
async function contextFor(browser) {
  const context = await browser.newContext({viewport: {width: 1440, height: 1100}});
  await context.route('**/*', async route => {
    const request = route.request();
    const url = new URL(request.url());
    if (!['GET', 'HEAD'].includes(request.method())) {
      report.blockedNonGetRequests.push({method: request.method(), path: url.pathname});
      if (url.hostname === 'gramlyze.loc') check(false, `Unexpected local non-GET blocked: ${request.method()} ${url.pathname}`);
      return route.abort();
    }
    if (url.hostname.endsWith('gramlyze.com') || url.hostname.endsWith('gramlyze.ub')) return route.abort();
    return route.continue();
  });
  return context;
}
function inspect(dataset, locale, key, bank = null) {
  const ids = dataset.map(q => q.uuid);
  check(ids.length === new Set(ids).size, `${key}: duplicate payload UUIDs`);
  if (bank) {
    const expected = inventory.questions.filter(q => q.seeder_class === bank.seeder_class).map(q => q.persistent_uuid).sort();
    check(JSON.stringify([...ids].sort()) === JSON.stringify(expected), `${key}: canonical bank membership differs`);
  }
  let native = 0; let gap = 0;
  for (const q of dataset) {
    const row = canonical.get(q.uuid); const display = presentation.get(q.uuid)?.locales?.[locale];
    check(!!row && !!display, `${key}: out-of-scope or unprojected UUID ${q.uuid}`);
    if (!row || !display) continue;
    seen.add(q.uuid);
    if (String(q.type) === '4') {
      native++;
      check((q.sourceTextUk ?? q.question) === display.display_source, `${key}: native short display mismatch ${q.uuid}`);
      const hint = String(q.hintUk ?? q.hint ?? '');
      check(display.instructions === '' || hint.includes(display.instructions), `${key}: hidden instructions missing ${q.uuid}`);
      if (q.correctText !== undefined) check(norm(q.correctText) === norm(row.completed_target), `${key}: alternate target differs ${q.uuid}`);
      else check(norm((q.answers || []).join(' ')) === norm(row.completed_target), `${key}: token answer differs ${q.uuid}`);
      if (q.renderer === 'compose') check(q.showPreAnswerHint === true, `${key}: alternate finite help flag missing ${q.uuid}`);
    } else {
      gap++;
      check(q.question === row.question_template, `${key}: English gap stem changed ${q.uuid}`);
      if (bank) check(q.compose_source_text === display.display_source, `${key}: short reconstruction metadata differs ${q.uuid}`);
    }
  }
  return {count: dataset.length, native, gap,
    levels: dataset.reduce((counts, q) => ({...counts, [q.level]: (counts[q.level] || 0) + 1}), {})};
}
async function directState(browser, bank, locale, mode) {
  const key = `${locale}/${bank.saved_test_slug}/${mode.name}`;
  const row = {key, locale, bank: bank.saved_test_slug, mode: mode.name, metadataOnly: !mode.title};
  const context = await contextFor(browser); const page = await context.newPage();
  try {
    const response = await page.goto(`${base}${localizedPath(locale, `/test/${bank.saved_test_slug}${mode.suffix}`)}`, {waitUntil: 'domcontentloaded'});
    row.status = response.status(); check(row.status === 200, `${key}: HTTP ${row.status}`);
    await page.waitForFunction(() => Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__) && window.__INITIAL_JS_TEST_QUESTIONS__.length > 0);
    const dataset = await page.evaluate(() => window.__INITIAL_JS_TEST_QUESTIONS__);
    Object.assign(row, inspect(dataset, locale, key, bank));
    if (mode.title) {
      const container = mode.name.startsWith('step') ? '#question-card' : '#questions article[data-idx="0"]';
      await page.waitForSelector(`${container} .leading-relaxed`);
      if (bank.kind === 'Builder') {
        const selector = mode.name.includes('manual') ? `${container} [data-authored-compose-condition]` : `${container} .leading-relaxed`;
        const heading = page.locator(selector).first();
        row.title = (await heading.innerText()).trim();
        const expected = presentation.get(dataset[0].uuid).locales[locale].display_source;
        check(row.title === expected, `${key}: actual rendered heading is not the projected sentence`);
        const help = page.locator(`${container} [data-authored-compose-hint]`).first();
        row.authoredHelpInitiallyVisible = await help.isVisible();
        check(!row.authoredHelpInitiallyVisible, `${key}: authored instruction/hint is still always visible`);
      } else row.titleNonempty = (await page.locator(`${container} .leading-relaxed`).first().innerText()).trim() !== '';
    }
    row.pass = !report.violations.some(value => value.startsWith(`${key}:`));
  } catch (error) {row.error = shortError(error); row.pass = false; report.violations.push(`${key}: ${row.error}`);}
  finally {report.states.push(row); await context.close();}
}
async function alternateState(browser, locale) {
  const key = `${locale}/alternate-theory-course-forms`;
  const row = {key, locale, fixtureApproved: allowGuestFixture};
  const context = await contextFor(browser); const page = await context.newPage();
  try {
    const url = `${base}${localizedPath(locale, '/courses/english-grammar-theory/lesson/tenses/past-perfect-continuous/past-perfect-continuous-forms/test')}`;
    const response = await page.goto(url, {waitUntil: 'domcontentloaded', timeout: 60000});
    row.status = response.status(); check(row.status === 200, `${key}: HTTP ${row.status}`);
    await page.waitForFunction(() => Array.isArray(window.__THEORY_MIXED_TEST__?.questions) && !!window.TheoryCourseProgress);
    const dataset = await page.evaluate(() => window.__THEORY_MIXED_TEST__.questions);
    Object.assign(row, inspect(dataset, locale, key));
    await page.waitForFunction(() => document.querySelector('#theory-mixed-lock')?.classList.contains('hidden') === false
      || (document.querySelector('#theory-mixed-question-card')?.textContent.trim().length || 0) > 0);
    row.cleanGuestLocked = await page.locator('#theory-mixed-lock').isVisible();
    check(row.cleanGuestLocked, `${key}: expected clean guest theory-course gate missing`);
    if (!allowGuestFixture) {
      row.uiNotInspected = 'Clean guest gate retained; no prerequisite fixture was authorized.';
    } else {
      row.fixture = await page.evaluate(() => {
        const config = window.__THEORY_MIXED_TEST__;
        const store = window.TheoryCourseProgress.createStore(config.course.slug, config.lessons);
        const target = store.findLesson(config.lesson.lesson_slug);
        if (!target?.previous_lesson_slug) throw new Error('No actual prerequisite manifest; do not invent an unlock.');
        store.markLessonCompleted(target.previous_lesson_slug);
        const snapshot = store.read();
        return {completedPrerequisite: target.previous_lesson_slug, targetIncomplete: !snapshot.completedLessons.includes(target.slug),
          targetUnlocked: snapshot.unlockedLessons.includes(target.slug), guestLocalOnly: true};
      });
      check(row.fixture.targetIncomplete && row.fixture.targetUnlocked, `${key}: guest fixture did not leave tested lesson incomplete/unlocked`);
      await page.reload({waitUntil: 'domcontentloaded', timeout: 60000});
      await page.waitForSelector('#theory-mixed-question-card .text-2xl');
      const index = dataset.findIndex(q => q.renderer === 'compose');
      check(index >= 0, `${key}: actual alternate pool has no compose item`);
      for (let i = 0; i < index; i++) await page.locator('#theory-mixed-next').click();
      const question = dataset[index]; const expected = presentation.get(question.uuid).locales[locale];
      row.title = (await page.locator('#theory-mixed-question-card .text-2xl').innerText()).trim();
      check(row.title === expected.display_source, `${key}: actual alternate heading is not the short authored sentence`);
      const button = page.locator('[data-compose-help-toggle]'); const hint = page.locator('[data-theory-compose-hint]');
      row.helpInitiallyClosed = !(await hint.isVisible()); check(row.helpInitiallyClosed, `${key}: alternate help starts open`);
      await button.click(); await hint.waitFor({state: 'visible'});
      row.helpOpened = (await hint.innerText()).trim() === String(question.hintUk).trim();
      check(row.helpOpened, `${key}: alternate opened help differs from actual localized payload`);
      check(await button.getAttribute('aria-expanded') === 'true', `${key}: alternate help aria state differs`);
      await page.screenshot({path: path.join(out, `alternate-${locale}-opened.png`), fullPage: false});
      await button.click(); check(!(await hint.isVisible()), `${key}: alternate help cannot close`);
      await page.reload({waitUntil: 'domcontentloaded', timeout: 60000});
      await page.waitForSelector('#theory-mixed-question-card .text-2xl');
      row.reloadProjectionCurrent = (await page.evaluate(() => window.__THEORY_MIXED_TEST__.questions))
        .filter(q => q.renderer === 'compose').every(q => q.sourceTextUk === presentation.get(q.uuid)?.locales?.[locale]?.display_source);
      check(row.reloadProjectionCurrent, `${key}: reload restored a prefixed source`);
    }
    row.pass = !report.violations.some(value => value.startsWith(`${key}:`));
  } catch (error) {row.error = shortError(error); row.pass = false; report.violations.push(`${key}: ${row.error}`);}
  finally {report.alternate.push(row); await context.close();}
}
async function main() {
  fs.mkdirSync(out, {recursive: true});
  const browser = await chromium.launch({headless: true, executablePath: browserExecutable});
  try {
    if (!alternateOnly) {
      const jobs = banks.flatMap(bank => locales.flatMap(locale => modes.map(mode => ({bank, locale, mode}))));
      for (const job of jobs) {
        await directState(browser, job.bank, job.locale, job.mode);
        if (report.states.length % 18 === 0) console.log(`Direct ${report.states.length}/${report.expectedDirectStates}; violations ${report.violations.length}`);
      }
      check(report.states.length === report.expectedDirectStates, 'Finite direct state count differs');
      check(seen.size === 624, `Direct UUID coverage differs: ${seen.size}`);
    }
    for (const locale of locales) await alternateState(browser, locale);
    check(report.alternate.length === report.expectedAlternateStates, 'Finite alternate state count differs');
  } finally {await browser.close();}
  report.uniqueQuestionsSeen = seen.size;
  fs.writeFileSync(path.join(out, 'result.json'), JSON.stringify(report, null, 2) + '\n');
  console.log(JSON.stringify({states: report.states.length, alternate: report.alternate.length, seen: seen.size,
    violations: report.violations.length, blockedNonGet: report.blockedNonGetRequests.length}));
  if (report.violations.length) process.exitCode = 1;
}
main().catch(error => {console.error(shortError(error)); process.exitCode = 1;});
