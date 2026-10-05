/* Explicit local PPC acceptance; fresh anonymous contexts, no user credentials. */
'use strict';
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

const root = path.resolve(__dirname, '..');
const output = process.argv[2];
if (!output) throw new Error('Provide a private output directory (outside versioned sources).');
const out = path.resolve(output);
const base = 'http://gramlyze.loc';
const locales = ['uk', 'en', 'pl'];
const courseOnly = process.argv.includes('--course-only');
const headingsOnly = process.argv.includes('--headings-only');
if (courseOnly && headingsOnly) throw new Error('Select one finite supplemental acceptance scope.');
const themes = ['Forms', 'Negatives', 'Questions', 'TimeExpressions'];
const slugs = ['forms', 'negatives', 'questions', 'time-expressions'];
// Exact App\Support\Database\QuestionUuidResolver::toPersistent contract.
function persistentUuid(value) {
    const uuid = String(value || '').trim();
    if (Buffer.byteLength(uuid, 'utf8') <= 36) return uuid;
    return uuid.slice(0, 27).replace(/-+$/g, '') + '-' + crypto.createHash('sha1').update(uuid).digest('hex').slice(0, 8);
}
const banks = [{ slug: 'polyglot-past-perfect-continuous-basics-b2', family: 'Basics', native: true,
    file: 'database/seeders/V3/Polyglot/PolyglotPastPerfectContinuousBasicsB2LessonSeeder/definition.json' }];
themes.forEach((family, i) => {
    banks.push({ slug: `past-perfect-continuous-${slugs[i]}-all-levels-v3`, family, native: false,
        file: `database/seeders/V3/Tenses/PastPerfectContinuous/PastPerfectContinuous${family}AllLevelsV3Seeder/definition.json` });
    banks.push({ slug: `polyglot-past-perfect-continuous-${slugs[i]}-all-levels`, family, native: true,
        file: `database/seeders/V3/Polyglot/PolyglotPastPerfectContinuous${family}AllLevelsLessonSeeder/definition.json` });
});
banks.forEach(bank => {
    const definition = JSON.parse(fs.readFileSync(path.join(root, bank.file), 'utf8'));
    bank.questions = definition.questions;
    bank.byUuid = new Map(bank.questions.map(q => [persistentUuid(q.uuid), q]));
});
if (courseOnly) {
    const questions = banks.flatMap(bank => bank.questions);
    const byUuid = new Map(questions.map(q => [persistentUuid(q.uuid), q]));
    banks.splice(0, banks.length, { slug: 'course-td-past-perfect-continuous-forms', family: 'Course', native: true,
        allowSubset: true, questions, byUuid });
} else if (headingsOnly) {
    for (let i = banks.length - 1; i >= 0; i--) if (!banks[i].native) banks.splice(i, 1);
}
const modes = courseOnly ? ['choose', 'manual', 'step-manual'] : headingsOnly ? ['compose'] : ['choose', 'manual', 'step-manual', 'compose'];
const expectedStates = courseOnly ? 9 : headingsOnly ? 15 : 108;
const expectedActions = courseOnly ? 3 : headingsOnly ? 0 : 37;
const report = { at: new Date().toISOString(), base, states: [], interactions: [], violations: [],
    uniqueSourceQuestions: 624, guestOnly: true, supportedStates: 0, unsupportedComposeStates: 0,
    cleanGuestCourseLocks: [], guestPrerequisiteFixtures: [], blockedProgressPosts: [],
    scope: courseOnly ? 'linked-course-227' : headingsOnly ? 'native-compose-localized-headings' : 'nine-direct-banks',
    expectedStates, expectedActions };
const seen = new Set();
let resolvedCourseIds = null;
const norm = text => String(text || '').normalize('NFKC').replace(/[’‘ʼ]/g, "'").replace(/[\p{P}\s]+/gu, ' ').trim().toLowerCase();
const source = (q, locale) => q.localizations?.[locale]?.source_text || (locale === 'uk' ? q.source_text_uk : '');
function check(condition, message) { if (!condition) report.violations.push(message); }
function errorText(error) { return String(error?.message || error).replace(/\s+/g, ' ').slice(0, 700); }
function url(bank, locale, suffix = '') { return `${base}${locale === 'uk' ? '' : '/' + locale}/test/${bank.slug}${suffix}`; }
function suffix(mode) { return mode === 'manual' ? '/manual' : mode === 'step-manual' ? '/step/manual' : mode === 'compose' ? '/step/compose' : ''; }

async function guestContext(browser) {
    const context = await browser.newContext({ viewport: { width: 1440, height: 1100 } });
    await context.route('**/*', async route => {
        const request = route.request();
        const pathname = new URL(request.url()).pathname;
        if (request.method() === 'POST' && /\/courses\/.*\/progress(?:\/|$)/.test(pathname)) {
            report.blockedProgressPosts.push({ pathname, method: request.method() });
            report.violations.push(`Unexpected server progress POST blocked: ${pathname}`);
            await route.abort();
        } else await route.continue();
    });
    return context;
}

async function prepareNativeCompose(page, bank, row) {
    await page.waitForFunction(() => ['locked', 'ready'].includes(document.querySelector('[data-polyglot-lock-state]')?.dataset.polyglotLockState));
    const state = await page.locator('[data-polyglot-lock-state]').getAttribute('data-polyglot-lock-state');
    row.cleanGuestLockState = state;
    if (bank.family === 'Basics') {
        check(state === 'ready', `${row.key || bank.slug}: initial Basics lesson is unexpectedly locked`);
    } else {
        check(state === 'locked', `${row.key || bank.slug}: prerequisite course lock missing in clean guest context`);
        report.cleanGuestCourseLocks.push({ key: row.key || `${bank.slug}/${row.mode}`, lockState: state });
        await capture(page, `lock-${(row.key || `${bank.slug}-${row.mode}`).replaceAll('/', '-')}`);
        // Explicitly approved guest-local fixture only. It uses the real browser store,
        // completing only prerequisite lessons; no server progress import or POST.
        const fixture = await page.evaluate(() => {
            const config = window.__POLYGLOT_COMPOSE_CONFIG__;
            const store = window.PolyglotCourseProgress.createStore(config.courseSlug, config.courseLessons);
            const index = store.lessons.findIndex(lesson => lesson.slug === config.slug);
            if (index < 1) throw new Error('Expected native course prerequisite manifest');
            const target = store.findLesson(config.slug);
            const prerequisite = config.previousLessonSlug || target.previous_lesson_slug;
            const completed = prerequisite && store.findLesson(prerequisite) ? [prerequisite] : [];
            for (const slug of completed) store.markLessonCompleted(slug);
            const fixture = store.read();
            // Existing theory-pages manifest omits previous/next links. A guest
            // fixture therefore records the already-earned unlock explicitly.
            fixture.lessons[config.slug].unlocked = true;
            fixture.unlocked_lessons = [...new Set([...fixture.unlocked_lessons, config.slug])];
            store.write(fixture, 'ppc-qa-guest-prerequisite-fixture');
            return { courseSlug: config.courseSlug, targetLesson: config.slug, completedPrerequisites: completed,
                manifestPreviousLesson: target.previous_lesson_slug,
                configuredPreviousLesson: config.previousLessonSlug,
                explicitGuestUnlockFixture: true,
                targetCompleted: store.read().lessons[config.slug].completed };
        });
        check(fixture.targetCompleted === false, `${row.key || bank.slug}: fixture completed the tested lesson`);
        report.guestPrerequisiteFixtures.push(fixture);
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.waitForFunction(() => document.querySelector('[data-polyglot-lock-state]')?.dataset.polyglotLockState === 'ready');
        row.unlockedWithGuestPrerequisiteFixture = true;
    }
    await page.waitForFunction(() => document.querySelector('#compose-source-text')?.textContent.trim().length > 0);
}

async function capture(page, name) {
    if (name.startsWith('state-')) {
        const anchor = name.endsWith('-compose') ? '#compose-source-text' : name.endsWith('-step-manual') ? '#question-card article' : '#questions article[data-idx="0"]';
        await page.locator(anchor).scrollIntoViewIfNeeded();
    }
    await page.screenshot({ path: path.join(out, `${name}.png`), fullPage: false });
}

async function inspectDataset(page, bank, locale, mode, row) {
    const dataset = await page.evaluate(() => window.__INITIAL_JS_TEST_QUESTIONS__ || []);
    row.count = dataset.length;
    row.levels = dataset.reduce((counts, q) => ({ ...counts, [q.level]: (counts[q.level] || 0) + 1 }), {});
    const ids = new Set(dataset.map(q => q.uuid));
    if (bank.allowSubset) {
        check(dataset.length > 0 && ids.size === dataset.length && [...ids].every(id => bank.byUuid.has(id)), `${row.key}: linked course is not an exact unique subset of 624 authored UUIDs`);
        const orderedIds = [...ids].sort();
        if (resolvedCourseIds === null) resolvedCourseIds = orderedIds;
        check(JSON.stringify(orderedIds) === JSON.stringify(resolvedCourseIds), `${row.key}: linked course UUID pool changed across locale/mode`);
        row.resolvedExistingPoolCount = dataset.length;
    } else {
        check(dataset.length === bank.questions.length, `${row.key}: question count ${dataset.length}`);
        check(ids.size === bank.questions.length && bank.questions.every(q => ids.has(persistentUuid(q.uuid))), `${row.key}: UUID coverage differs`);
    }
    const expectedLevels = bank.family === 'Basics' ? { B2: 48 } : { A1: 12, A2: 12, B1: 12, B2: 12, C1: 12, C2: 12 };
    if (!bank.allowSubset) check(JSON.stringify(Object.entries(row.levels).sort()) === JSON.stringify(Object.entries(expectedLevels).sort()), `${row.key}: CEFR coverage differs`);
    row.markerHintsChecked = 0;
    row.lexicalHintsChecked = 0;
    row.sourcesChecked = 0;
    row.theoryLinksChecked = 0;
    for (const q of dataset) {
        const expected = bank.byUuid.get(q.uuid);
        if (!expected) continue;
        seen.add(q.uuid);
        if (mode === 'compose') {
            check(q.sourceTextUk === source(expected, locale), `${row.key}: wrong localized compose source ${q.uuid}`);
            check(q.hintUk === expected.localizations?.[locale]?.hints?.[0], `${row.key}: wrong localized compose lexical hint ${q.uuid}`);
            row.lexicalHintsChecked++;
            check(q.showPreAnswerHint === true, `${row.key}: native pre-answer lexical hint is not explicitly enabled ${q.uuid}`);
            check(norm(q.correctText) === norm(expected.target_text), `${row.key}: target changed ${q.uuid}`);
        } else if (bank.native) {
            check(q.question === source(expected, locale), `${row.key}: wrong localized native source ${q.uuid}`);
            check(q.hint === expected.localizations?.[locale]?.hints?.[0], `${row.key}: wrong localized native lexical hint ${q.uuid}`);
            row.lexicalHintsChecked++;
            check(norm(q.answers?.join(' ')) === norm(expected.target_text), `${row.key}: native token answer changed ${q.uuid}`);
        } else {
            check(q.question === expected.question, `${row.key}: English gap stem changed ${q.uuid}`);
            check(q.compose_source_text === source(expected, locale), `${row.key}: missing localized guided source ${q.uuid}`);
            for (const [marker, data] of Object.entries(expected.markers)) {
                row.markerHintsChecked++;
                check(q.verb_hints?.[marker] === expected.localizations?.[locale]?.verb_hints?.[marker], `${row.key}: marker hint ${q.uuid}/${marker}`);
                check(q.answer_map?.[marker] === data.answer, `${row.key}: marker answer ${q.uuid}/${marker}`);
                const slot = q.markers?.indexOf(marker);
                const options = q.options_by_marker?.[slot];
                check(Array.isArray(options) && new Set(options.map(norm)).size === options.length, `${row.key}: normalized option duplicate ${q.uuid}/${marker}`);
            }
        }
        row.sourcesChecked++;
        check(Array.isArray(q.theory_blocks) && q.theory_blocks.length > 0, `${row.key}: theory links absent ${q.uuid}`);
        row.theoryLinksChecked++;
    }
    return dataset;
}

async function mixedInteraction(browser, bank, mode) {
    const context = await guestContext(browser);
    const page = await context.newPage();
    const row = { bank: bank.slug, mode, pass: false };
    try {
        await page.goto(url(bank, 'uk', mode === 'manual' ? '/manual' : ''), { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('#questions article[data-idx="0"]');
        const q = await page.evaluate(() => state.items[0]);
        row.uuid = q.uuid;
        if (bank.native) {
            const hint = page.locator('#questions article[data-idx="0"] [data-authored-compose-hint]');
            check(await hint.isVisible() && (await hint.innerText()).trim() === q.hint, `Interaction ${bank.slug}/${mode}: native lexical hint is not visible before attempt`);
        }
        if (mode === 'manual') {
            for (let i = 0; i < q.answers.length; i++) await page.locator(`#input-0-${i}`).fill(q.answers[i]);
            await page.locator(`#input-0-${q.answers.length - 1}`).press('Enter');
        } else {
            const card = page.locator('#questions article[data-idx="0"]');
            await card.locator('[data-options-toggle]').click();
            for (const answer of q.answers) {
                await card.locator('[data-opt]').filter({ hasText: '' }).evaluateAll((buttons, wanted) => {
                    const button = buttons.find(b => b.getAttribute('data-opt') === wanted);
                    if (!button) throw new Error('Expected choice is absent');
                }, answer);
                await card.locator(`[data-opt=${JSON.stringify(answer)}]`).click();
            }
        }
        await page.waitForFunction(() => state.items[0]?.done && state.items[0]?.feedback === 'correct');
        row.pass = true;
        row.feedback = await page.locator('#feedback-0').innerText();
        await capture(page, `interaction-${bank.slug}-${mode}`);
    } catch (error) { row.error = errorText(error); report.violations.push(`Interaction ${bank.slug}/${mode}: ${row.error}`); }
    finally { report.interactions.push(row); await context.close(); }
}

async function stepManualInteraction(browser, bank) {
    const context = await guestContext(browser);
    const page = await context.newPage();
    const row = { bank: bank.slug, mode: 'step-manual', pass: false };
    try {
        await page.goto(url(bank, 'uk', '/step/manual'), { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('#question-card input[data-slot]');
        const q = await page.evaluate(() => state.items[state.current]);
        row.uuid = q.uuid;
        if (bank.native) {
            const hint = page.locator('#question-card [data-authored-compose-hint]');
            check(await hint.isVisible() && (await hint.innerText()).trim() === q.hint, `Interaction ${bank.slug}/step-manual: native lexical hint is not visible before attempt`);
        }
        for (let i = 0; i < q.answers.length; i++) await page.locator(`#input-${i}`).fill(q.answers[i]);
        await page.locator('#check').click();
        await page.waitForFunction(() => state.items[state.current]?.done && state.items[state.current]?.feedback === 'correct');
        row.pass = true;
        row.feedback = await page.locator('#feedback').innerText();
        await capture(page, `interaction-${bank.slug}-step-manual`);
    } catch (error) { row.error = errorText(error); report.violations.push(`Interaction ${bank.slug}/step-manual: ${row.error}`); }
    finally { report.interactions.push(row); await context.close(); }
}

async function nativeInteraction(browser, bank, mode) {
    const context = await guestContext(browser);
    const page = await context.newPage();
    const row = { bank: bank.slug, mode, ui: 'step/compose', pass: false };
    try {
        await page.goto(url(bank, 'uk', '/step/compose'), { waitUntil: 'domcontentloaded' });
        await prepareNativeCompose(page, bank, row);
        await page.waitForSelector('#compose-bank [data-bank-token-id]');
        const sourceText = (await page.locator('#compose-source-text').innerText()).trim();
        const dataset = await page.evaluate(() => window.__INITIAL_JS_TEST_QUESTIONS__ || []);
        const q = dataset.find(q => q.sourceTextUk === sourceText);
        if (!q) throw new Error('Visible compose prompt does not match a live dataset row');
        row.uuid = q.uuid;
        const learningHint = page.locator('#compose-learning-hint');
        if (!(await learningHint.isVisible()) || (await learningHint.innerText()).trim() !== q.hintUk) {
            throw new Error('The localized lexical hint is not visible before answering');
        }
        if (mode === 'manual') {
            for (let i = 0; i < q.correctTokenValues.length; i++) {
                await page.locator(`[data-compose-manual-slot="${i}"]`).fill(q.correctTokenValues[i]);
            }
        } else {
            for (const id of q.correctTokenIds) await page.locator(`[data-bank-token-id=${JSON.stringify(id)}]`).click();
        }
        await page.locator('#compose-controls [data-action="check"]').click();
        await page.waitForFunction(() => {
            const feedback = document.querySelector('#compose-feedback');
            return feedback && !feedback.classList.contains('hidden') && /(?:правиль|correct|poprawn)/i.test(feedback.innerText);
        });
        row.feedback = await page.locator('#compose-feedback').innerText();
        row.pass = !/incorrect|неправиль|niepoprawn/i.test(row.feedback);
        check(row.pass, `Interaction ${bank.slug}/${mode}: not marked correct`);
        await capture(page, `interaction-${bank.slug}-compose-${mode}`);
    } catch (error) { row.error = errorText(error); report.violations.push(`Interaction ${bank.slug}/${mode}: ${row.error}`); }
    finally { report.interactions.push(row); await context.close(); }
}

(async () => {
    fs.mkdirSync(out, { recursive: true });
    const browser = await chromium.launch({ headless: true,
        ...(process.env.CHROMIUM_EXECUTABLE ? { executablePath: process.env.CHROMIUM_EXECUTABLE } : {}) });
    try {
        for (const locale of locales) {
            for (const bank of banks) {
                for (const mode of modes) {
                    const context = await guestContext(browser);
                    const page = await context.newPage();
                    const row = { key: `${locale}/${bank.slug}/${mode}`, locale, bank: bank.slug, mode, errors: [] };
                    page.on('pageerror', error => row.errors.push(errorText(error)));
                    try {
                        const response = await page.goto(url(bank, locale, suffix(mode)), { waitUntil: 'domcontentloaded' });
                        row.status = response.status();
                        row.finalPath = new URL(page.url()).pathname;
                        if (mode === 'compose' && !bank.native) {
                            check(row.status === 404, `${row.key}: unsupported direct gap compose route changed`);
                            row.notApplicable = 'Direct type-0 bank has no standalone compose mode; canonical mixed test supplies reorder tasks.';
                            report.unsupportedComposeStates++;
                        } else {
                            check(row.status === 200, `${row.key}: HTTP ${row.status}`);
                            await page.waitForFunction(() => (window.__INITIAL_JS_TEST_QUESTIONS__ || []).length > 0);
                            await inspectDataset(page, bank, locale, mode, row);
                            if (mode === 'compose') await prepareNativeCompose(page, bank, row);
                            else await page.waitForSelector(`${mode === 'step-manual' ? '#question-card' : '#questions'} article[data-idx="0"]`);
                            const visibleSource = mode === 'compose' ? await page.locator('#compose-source-text').innerText() : await page.locator(mode === 'step-manual' ? '#question-card' : '#questions').innerText();
                            check(visibleSource.trim() !== '', `${row.key}: rendered questions absent`);
                            if (mode === 'compose') {
                                const active = bank.questions.find(q => source(q, locale) === visibleSource.trim());
                                const learningHint = page.locator('#compose-learning-hint');
                                row.preAnswerHintVisible = await learningHint.isVisible();
                                row.preAnswerHint = (await learningHint.innerText()).trim();
                                check(row.preAnswerHintVisible && row.preAnswerHint === active?.localizations?.[locale]?.hints?.[0], `${row.key}: visible pre-answer lexical hint differs`);
                                if (headingsOnly) {
                                    row.renderedSourceHeading = (await page.locator('#compose-source-label').innerText()).trim();
                                    row.sourceHeading = (await page.locator('#compose-source-label').textContent()).trim();
                                    check(row.sourceHeading === { uk: 'Умова', en: 'Task', pl: 'Polecenie' }[locale], `${row.key}: source heading is not the localized finite task label`);
                                }
                            }
                            row.visibleManualInputs = await page.locator('#questions input[data-slot], #question-card input[data-slot], #compose-answer-zone [data-compose-manual-slot]').count();
                            if (mode === 'manual' || mode === 'step-manual') {
                                check(row.visibleManualInputs > 0, `${row.key}: actual direct manual inputs absent`);
                                if (bank.native) {
                                    const first = await page.evaluate(() => state.items[0]);
                                    const currentCard = page.locator(`${mode === 'step-manual' ? '#question-card' : '#questions'} article[data-idx="0"]`);
                                    check(await currentCard.locator('input[data-authored-compose-input]').count() === first.answers.length, `${row.key}: native manual token slots differ`);
                                    const hint = currentCard.locator('[data-authored-compose-hint]');
                                    check(await hint.isVisible() && (await hint.innerText()).trim() === first.hint, `${row.key}: native manual pre-answer lexical hint is absent`);
                                    row.preAnswerHintVisible = await hint.isVisible();
                                }
                            }
                            check(row.errors.length === 0, `${row.key}: browser script error ${row.errors.join('; ')}`);
                            report.supportedStates++;
                            await capture(page, `state-${locale}-${bank.slug}-${mode}`);
                        }
                    } catch (error) { row.error = errorText(error); report.violations.push(`${row.key}: ${row.error}`); }
                    finally {
                        report.states.push(row);
                        await context.close();
                        fs.writeFileSync(path.join(out, 'result.json'), JSON.stringify(report, null, 2));
                        console.log(JSON.stringify({ state: row.key, status: row.status, count: row.count, violations: report.violations.length }));
                    }
                }
            }
        }
        for (const bank of headingsOnly ? [] : banks) {
            for (const mode of ['choose', 'manual']) {
                await mixedInteraction(browser, bank, mode);
            }
            await stepManualInteraction(browser, bank);
            if (bank.native && !courseOnly) {
                for (const mode of ['choose', 'manual']) await nativeInteraction(browser, bank, mode);
            }
        }
        report.uniqueObservedSourceQuestions = seen.size;
        const expectedUnique = courseOnly ? resolvedCourseIds?.length : headingsOnly ? 336 : 624;
        check(seen.size === expectedUnique, `Unique live question UUID coverage ${seen.size}, expected ${expectedUnique}`);
        if (courseOnly) report.resolvedCourseQuestionUuids = resolvedCourseIds;
        report.checkedMarkerHints = report.states.reduce((sum, row) => sum + (row.markerHintsChecked || 0), 0);
        report.checkedNativeLexicalHints = report.states.reduce((sum, row) => sum + (row.lexicalHintsChecked || 0), 0);
        report.checkedSourceRows = report.states.reduce((sum, row) => sum + (row.sourcesChecked || 0), 0);
        report.pass = report.violations.length === 0 && report.states.length === expectedStates && report.interactions.length === expectedActions && report.interactions.every(row => row.pass);
    } finally {
        await browser.close();
        report.completedAt = new Date().toISOString();
        fs.writeFileSync(path.join(out, 'result.json'), JSON.stringify(report, null, 2));
        console.log(JSON.stringify({ pass: report.pass, states: report.states.length, interactions: report.interactions.length, violations: report.violations.length, evidence: out }));
        process.exitCode = report.pass ? 0 : 1;
    }
})().catch(error => { console.error(errorText(error)); process.exitCode = 1; });
