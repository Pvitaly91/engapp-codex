'use strict';
// Compare learner-visible text with the immutable authored master, not with a generated after-state.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const crypto = require('node:crypto');
const {JSDOM} = require('jsdom');
const {lessons} = require('./seo-m23-local.cjs');

const MASTER_PATH = 'docs/content/m23-authored-content.v1.json';
const MASTER_SHA = 'eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6';
const normalize = value => String(value ?? '').replace(/\u00a0/g, ' ').replace(/\s+/g, ' ').trim();
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const dom = html => new JSDOM(html).window.document;
const textOf = nodes => [...nodes].map(node => normalize(node.textContent));
const attrPairs = nodes => [...nodes].map(node => [node.getAttribute('href'), normalize(node.textContent)]);

function master() {
    const bytes = fs.readFileSync(MASTER_PATH);
    assert.equal(sha(bytes), MASTER_SHA, 'Author master bytes differ');
    assert.ok(bytes.toString('utf8').endsWith('\n') && !bytes.toString('utf8').endsWith('\n\n'));
    const data = JSON.parse(bytes.toString('utf8'));
    assert.equal(data.handoff_schema, 'gramlyze.authored-content.v1');
    assert.equal(data.lessons.length, 3);
    for (const lesson of data.lessons) {
        assert.equal(sha(lesson.body_html), lesson.body_sha256);
        assert.equal(lesson.exercise_count, 6);
        assert.equal(lesson.answer_count, 6);
        assert.deepEqual(lesson.category_path.length, 1);
        assert.equal(lesson.theory_path, lessons[lesson.slug].theory);
    }
    return data;
}
function expected(lesson) {
    const body = dom(lesson.body_html);
    const selfCheck = body.querySelector('section[id^="self-check-"]');
    assert.ok(selfCheck, lesson.key + ' self-check');
    return {
        text: normalize(body.body.textContent),
        headings: textOf(body.body.querySelectorAll('h4')),
        paragraphs: textOf(body.body.querySelectorAll('p')),
        cells: textOf(body.body.querySelectorAll('th,td')),
        questions: textOf(selfCheck.querySelectorAll(':scope > ol > li')),
        keys: textOf(selfCheck.querySelectorAll('details > ol > li')),
        hrefs: attrPairs(body.body.querySelectorAll('a[href]')),
        tables: body.body.querySelectorAll('table').length
    };
}
function actual(body) {
    const selfCheck = body.querySelector('section[id^="self-check-"]');
    assert.ok(selfCheck, 'Rendered self-check missing');
    return {
        text: normalize(body.textContent),
        headings: textOf(body.querySelectorAll('h4')),
        paragraphs: textOf(body.querySelectorAll('p')),
        cells: textOf(body.querySelectorAll('th,td')),
        questions: textOf(selfCheck.querySelectorAll(':scope > ol > li')),
        keys: textOf(selfCheck.querySelectorAll('details > ol > li')),
        hrefs: attrPairs(body.querySelectorAll('a[href]')),
        tables: body.querySelectorAll('table').length
    };
}
function assertBody(lesson, candidateHtml, rendered = false) {
    const candidate = rendered ? dom(candidateHtml).querySelector('.theory-rich-content') : dom(candidateHtml).body;
    assert.ok(candidate, 'Rendered rich content missing');
    assert.deepEqual(actual(candidate), expected(lesson), 'Learner-visible body differs from authored master: ' + lesson.key);
    return expected(lesson);
}
function assertDefinition(lesson, definition, before) {
    assert.equal(definition.seeder.class, lesson.seeder);
    assert.equal(definition.slug, lesson.slug);
    assert.equal(definition.page.title, lesson.preserve_page_title);
    assert.equal(definition.page.locale, 'uk');
    assert.equal(definition.page.category.slug, lesson.category_path[0]);
    assert.equal(definition.page.subtitle_html, lesson.subtitle_html);
    assert.equal(definition.page.subtitle_text, lesson.subtitle_text);
    assert.deepEqual(JSON.parse(definition.page.blocks[0].body), lesson.hero);
    assert.equal(definition.page.blocks[1].heading, lesson.box_heading);
    assert.equal(definition.page.blocks[1].body, lesson.body_html);
    const protectedCopy = value => {
        const copy = structuredClone(value);
        delete copy.page.subtitle_html;
        delete copy.page.subtitle_text;
        for (const block of copy.page.blocks) { delete block.heading; delete block.body; }
        return copy;
    };
    assert.deepEqual(protectedCopy(definition), protectedCopy(before), 'Immutable source changed');
    const subtitle = dom(lesson.subtitle_html);
    assert.equal(normalize(subtitle.querySelector('strong')?.textContent), lesson.preserve_subtitle_strong);
    assertBody(lesson, definition.page.blocks[1].body);
}
function assertDatabase(lesson, row) {
    assert.equal(row.page.seeder, lesson.seeder);
    assert.equal(row.page.title, lesson.preserve_page_title);
    assert.equal(row.page.slug, lesson.slug);
    assert.deepEqual(row.ancestry.map(c => c.slug), lesson.category_path);
    assert.equal(row.page.text, lesson.subtitle_text);
    const uk = row.blocks.filter(b => b.locale === 'uk').sort((a,b)=>a.sort_order-b.sort_order);
    assert.equal(uk.length, 3);
    assert.equal(uk[0].body, lesson.subtitle_html);
    assert.deepEqual(JSON.parse(uk[1].body), lesson.hero);
    assert.equal(uk[2].heading, lesson.box_heading);
    assertBody(lesson, uk[2].body);
}
function assertHero(lesson, main) {
    const heroItems = [lesson.hero.intro, ...lesson.hero.rules.flatMap(rule => [rule.label, rule.text, rule.example])];
    const visible = normalize(main.textContent);
    let cursor = 0;
    for (const item of heroItems) {
        const text = normalize(dom(item).body.textContent);
        const index = visible.indexOf(text, cursor);
        assert.ok(index >= 0, 'Hero text/order differs from author: ' + item.slice(0, 45));
        cursor = index + text.length;
    }
}
function assertHtml(lesson, html) {
    const page = dom(html);
    const main = page.querySelector('[data-theory-main]');
    assert.ok(main, 'No real theory main');
    assert.equal(normalize(page.querySelector('h1')?.textContent), lesson.preserve_subtitle_strong);
    assert.equal(normalize(page.querySelector('title')?.textContent), lesson.expected_metadata.title);
    for (const selector of ['meta[name="description"]','meta[property="og:description"]','meta[name="twitter:description"]']) {
        assert.equal(page.querySelector(selector)?.getAttribute('content'), lesson.expected_metadata.description);
    }
    const block = main.querySelector('article .theory-rich-content');
    assert.ok(block, 'No rich renderer opt-in');
    const expectedBody = expected(lesson);
    assert.deepEqual(actual(block), expectedBody, 'Rendered text/sections/questions/keys differ: ' + lesson.key);
    assert.equal(main.querySelectorAll('#self-check-' + lesson.slug).length, 1);
    // The page template renders the preserved strong title as H1 and the
    // authored subtitle sentence separately, without its em-dash separator.
    const subtitleSentence = lesson.subtitle_text.split(' — ').slice(1).join(' — ');
    assert.ok(subtitleSentence && normalize(main.textContent).includes(normalize(subtitleSentence)));
    assert.ok(normalize(main.textContent).includes(normalize(lesson.box_heading)));
    assertHero(lesson, main);
    return {bodySha256: sha(expectedBody.text), headings: expectedBody.headings.length, questions: expectedBody.questions.length, keys: expectedBody.keys.length, tables: expectedBody.tables, links: expectedBody.hrefs.length};
}
function definitions(data) {
    const before = JSON.parse(fs.readFileSync('database/content-patches/m23-authored-revision-before.json', 'utf8'));
    assert.equal(before.patch, 'm23-authored-revision-v1');
    assert.deepEqual(Object.keys(before.definitions), data.lessons.map(l => l.seeder));
    for (const lesson of data.lessons) {
        const definition = JSON.parse(fs.readFileSync(lesson.definition_path, 'utf8'));
        assertDefinition(lesson, definition, before.definitions[lesson.seeder]);
    }
}
async function live(base, outPath) {
    assert.equal(base, 'http://gramlyze.loc');
    const data = master();
    definitions(data);
    const report = {at: new Date().toISOString(), masterSha256: MASTER_SHA, base, rows: []};
    for (const lesson of data.lessons) {
        const response = await fetch(base + lesson.theory_path, {headers: {Accept: 'text/html'}, redirect: 'manual', signal: AbortSignal.timeout(60000)});
        assert.equal(response.status, 200, lesson.theory_path);
        const result = assertHtml(lesson, await response.text());
        report.rows.push({key: lesson.key, path: lesson.theory_path, ...result});
    }
    fs.writeFileSync(outPath, JSON.stringify(report, null, 2), {flag: 'wx'});
    return report;
}
if (require.main === module) {
    const [mode, ...args] = process.argv.slice(2);
    if (mode === 'source') { const data = master(); definitions(data); console.log(JSON.stringify({masterSha256: MASTER_SHA, lessons: data.lessons.map(l => l.key), exact: true})); }
    else if (mode === 'live') live(args[0], args[1]).then(r => console.log(JSON.stringify(r, null, 2))).catch(e => {console.error(e);process.exitCode=1;});
    else throw Error('M23 fidelity accepts only source or live read-only modes');
}
module.exports = {MASTER_SHA, master, expected, actual, assertBody, assertDefinition, assertDatabase, assertHtml, definitions, live, normalize};
