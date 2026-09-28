'use strict';
// Immutable M24 author-master is the sole text oracle; no fixture text is invented.
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require('jsdom');
const root = path.resolve(__dirname, '../..');
const working = path.resolve(root, '../../..');
const masterPath = path.join(root, 'docs/content/m24-authored-content.v1.json');
const beforePath = path.join(root, 'database/content-patches/m24-authored-native-before.json');
const masterSha = '33373412ed077bf7fa0aea8dde8a6d2c211c126288b6cedfdde299ded43bf5a2';
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const normalize = text => text.replace(/\s+/g, ' ').trim();
const readJson = file => JSON.parse(fs.readFileSync(file, 'utf8'));
const visibleText = html => normalize(new JSDOM(String(html)).window.document.body.textContent);
function assertVisible(root, value, context) {
    if (value === null || value === undefined || value === '') return;
    const expected = visibleText(value);
    assert.ok(normalize(root.textContent).includes(expected), context + ': missing exact authored text ' + expected.slice(0, 75));
}
function assertOrderedVisible(root, values, context) {
    const rendered = normalize(root.textContent);
    let cursor = 0;
    for (const [index, value] of values.entries()) {
        const expected = visibleText(value);
        const at = rendered.indexOf(expected, cursor);
        assert.ok(at >= cursor, context + ': missing or reordered item ' + index + ': ' + expected.slice(0, 60));
        cursor = at + expected.length;
    }
}
function assertNumberedTitle(root, title, context) {
    const match = /^(\d+)\.\s*(.+)$/.exec(title);
    if (match) {
        assertVisible(root.querySelector('h2'), match[2], context + ' title');
        const badge = root.querySelector('h2 span');
        assert.equal(normalize(badge?.textContent || ''), match[1], context + ' number badge');
    } else assertVisible(root.querySelector('h2'), title, context + ' title');
}
const master = () => {
    const bytes = fs.readFileSync(masterPath);
    assert.equal(sha(bytes), masterSha, 'Immutable M24 master bytes changed');
    return JSON.parse(bytes);
};
const old = () => readJson(beforePath);
const definition = lesson => readJson(path.join(root, lesson.definition_path));

function assertDefinition(lesson, before, after) {
    assert.equal(lesson.baseline_source_block_count, lesson.existing_blocks.length);
    assert.equal(after.seeder.class, lesson.seeder);
    assert.equal(after.page.title, lesson.preserve_page_title);
    assert.equal(after.page.subtitle_html, lesson.subtitle_html);
    assert.equal(after.page.subtitle_text, lesson.subtitle_text);
    assert.equal(after.page.blocks.length, before.page.blocks.length + 1);
    const reverted = structuredClone(after);
    reverted.page.subtitle_html = before.page.subtitle_html;
    reverted.page.subtitle_text = before.page.subtitle_text;
    for (const block of lesson.existing_blocks) {
        const i = block.source_index;
        assert.equal(before.page.blocks[i].type, block.preserve_type);
        assert.equal(before.page.blocks[i].column, block.preserve_column);
        assert.equal(before.page.blocks[i].level, block.preserve_level);
        assert.deepEqual(JSON.parse(after.page.blocks[i].body), block.replacement_body_json,
            lesson.key + ' native block ' + i);
        reverted.page.blocks[i].body = before.page.blocks[i].body;
    }
    const append = lesson.append_blocks[0];
    assert.equal(append.source_index, before.page.blocks.length);
    assert.equal(sha(append.body_html), append.body_sha256);
    assert.deepEqual(after.page.blocks.at(-1), {
        type: append.type, column: append.column, heading: append.heading, level: append.level,
        body: append.body_html, uuid_key: append.uuid_key,
        inherit_base_tags: append.inherit_base_tags, tags: append.tags
    });
    reverted.page.blocks.pop();
    assert.deepEqual(reverted, before, lesson.key + ' unrelated source field changed');
}
function assertSources() {
    const data = master();
    const manifest = old();
    assert.equal(data.package, 'M24');
    assert.equal(manifest.master_sha256, masterSha);
    assert.equal(data.lessons.length, 3);
    for (const lesson of data.lessons) assertDefinition(lesson, manifest.definitions[lesson.seeder], definition(lesson));
    return {lessons: data.lessons.length, nativeBlocks: data.lessons.map(l => l.existing_blocks.length)};
}
function assertDatabaseSnapshot(before, after, plan) {
    const data = master();
    assert.equal(plan.patch, 'm24-authored-native-content-v1');
    assert.equal(plan.state, 'before');
    assert.equal(plan.updates.length, 23);
    assert.equal(plan.inserts.length, 3);
    assert.deepEqual(before.rows.map(r => r.key), after.rows.map(r => r.key));
    for (const lesson of data.lessons) {
        const oldRow = before.rows.find(r => r.key === lesson.key);
        const newRow = after.rows.find(r => r.key === lesson.key);
        const source = definition(lesson);
        assert.ok(oldRow && newRow);
        const oldPage = {...oldRow.page}; const newPage = {...newRow.page};
        assert.equal(oldPage.seeder, lesson.seeder);
        assert.equal(oldPage.text, old().definitions[lesson.seeder].page.subtitle_text);
        assert.equal(newPage.text, lesson.subtitle_text);
        delete oldPage.text; delete newPage.text;
        assert.deepEqual(newPage, oldPage, lesson.key + ' protected Page changed');
        assert.deepEqual(newRow.ancestry, oldRow.ancestry, lesson.key + ' category changed');
        assert.equal(newRow.blocks.length, oldRow.blocks.length + 1);
        const oldBlocks = oldRow.blocks;
        const newBlocks = newRow.blocks;
        const inserted = newBlocks.find(block => !oldBlocks.some(previous => previous.id === block.id));
        assert.ok(inserted, lesson.key + ' new practice missing');
        const expectedInsert = plan.inserts.find(row => row.seeder === lesson.seeder).fields;
        for (const [field, value] of Object.entries(expectedInsert)) assert.equal(inserted[field], value, lesson.key + ' insert ' + field);
        assert.equal(inserted.body, lesson.append_blocks[0].body_html);
        assert.equal(sha(inserted.body), lesson.append_blocks[0].body_sha256);
        assert.equal(inserted.sort_order, lesson.existing_blocks.length + 1);
        assert.ok(inserted.created_at && inserted.created_at === inserted.updated_at);
        for (const previous of oldBlocks) {
            const current = newBlocks.find(block => block.id === previous.id);
            assert.ok(current);
            const oldBlock = {...previous}; const newBlock = {...current};
            if (previous.locale === 'uk') {
                const expected = previous.type === 'subtitle' ? source.page.subtitle_html : source.page.blocks[previous.sort_order - 1].body;
                assert.equal(newBlock.body, expected, lesson.key + ' block ' + previous.sort_order);
            } else assert.equal(newBlock.body, oldBlock.body, lesson.key + ' non-UK body');
            delete oldBlock.body; delete newBlock.body;
            assert.deepEqual(newBlock, oldBlock, lesson.key + ' protected block changed');
        }
        assert.deepEqual(newRow.relations, oldRow.relations, lesson.key + ' relations changed');
    }
    return {updated: plan.updates.length, inserted: plan.inserts.length, protected: true};
}
function assertLiveHtml(lesson, html) {
    assert.ok(!/\b(?:PHP Warning|PHP Notice|Fatal error|Deprecated):/i.test(html),
        lesson.key + ' PHP diagnostic leaked into HTML');
    const source = definition(lesson);
    const dom = new JSDOM(html);
    const document = dom.window.document;
    const main = document.querySelector('[data-theory-main]');
    assert.ok(main, lesson.key + ' main missing');
    const metadata = lesson.expected_metadata;
    assert.equal(document.title, metadata.title);
    assert.equal(document.querySelector('meta[name="description"]')?.content, metadata.description);
    assert.equal(normalize(main.querySelector('h1')?.textContent || ''), lesson.preserve_subtitle_strong);
    const practice = main.querySelector('#self-check-m24-' + lesson.key);
    assert.ok(practice, lesson.key + ' practice not rendered');
    const author = new JSDOM(lesson.append_blocks[0].body_html).window.document;
    const authoredExercise = author.querySelector('#self-check-m24-' + lesson.key);
    assert.equal(normalize(practice.textContent), normalize(authoredExercise.textContent),
        lesson.key + ' rendered exercise text differs');
    assert.equal(practice.querySelectorAll(':scope > ol > li').length, lesson.exercise_count);
    assert.equal(practice.querySelectorAll('details > ol > li').length, lesson.answer_count);
    assert.equal(main.querySelectorAll('[id^="self-check-m24-"]').length, 1);
    const snapshot = readJson(path.join(working, 'storage/app/seo-m24-local/final-targets.json'));
    const dbRow = snapshot.rows.find(row => row.key === lesson.key);
    assert.ok(dbRow, lesson.key + ' live block ownership unavailable');
    for (const block of lesson.existing_blocks) {
        const data = block.replacement_body_json;
        const blockRow = dbRow.blocks.find(row => row.locale === 'uk' && row.sort_order === block.source_index + 1);
        assert.equal(blockRow?.type, block.preserve_type, lesson.key + ' native block identity');
        const context = lesson.key + ' ' + block.preserve_type + ' #' + block.source_index;
        if (block.preserve_type === 'hero') {
            const hero = main.querySelector(':scope > section:first-child');
            assert.ok(hero, context + ' hero missing');
            assertVisible(hero, data.intro, context + ' intro');
            const rules = main.querySelectorAll(':scope > section:nth-of-type(2) > article');
            assert.equal(rules.length, data.rules.length, context + ' rule count/order');
            data.rules.forEach((rule, index) => {
                assertVisible(rules[index], rule.label, context + ' label ' + index);
                assertVisible(rules[index], rule.text, context + ' text ' + index);
                assertVisible(rules[index], rule.example, context + ' example ' + index);
            });
            continue;
        }
        if (block.preserve_type === 'navigation-chips') {
            const nav = [...main.querySelectorAll('nav')].find(node => normalize(node.textContent).includes(data.title));
            assert.ok(nav, context + ' navigation missing');
            assertVisible(nav, data.title, context + ' title');
            for (const item of data.items) {
                const candidate = item.current ? [...nav.querySelectorAll('span')].find(node => normalize(node.textContent) === item.label)
                    : nav.querySelector('a[href="' + item.url + '"]');
                assert.ok(candidate, context + ' item missing: ' + item.label);
                assert.equal(normalize(candidate.textContent), item.label, context + ' item label');
                if (!item.current) assert.equal(candidate.getAttribute('href'), item.url, context + ' href');
            }
            assertOrderedVisible(nav, data.items.map(item => item.label), context + ' navigation order');
            continue;
        }
        const rendered = main.querySelector('#block-' + blockRow.id);
        assert.ok(rendered, context + ' rendered block missing');
        assertNumberedTitle(rendered, data.title, context);
        assertVisible(rendered, data.intro, context + ' intro');
        if (block.preserve_type === 'comparison-table') {
            const rows = rendered.querySelectorAll('tbody tr');
            assert.equal(rows.length, data.rows.length, context + ' row count/order');
            data.rows.forEach((row, index) => {
                const cells = rows[index].querySelectorAll('td');
                assert.equal(cells.length, 3);
                assertVisible(cells[0], row.en, context + ' row ' + index + ' en');
                assertVisible(cells[1], row.ua, context + ' row ' + index + ' ua');
                assertVisible(cells[2], row.note, context + ' row ' + index + ' note');
            });
            assertVisible(rendered, data.warning, context + ' warning');
        } else if (block.preserve_type === 'forms-grid') {
            assertOrderedVisible(rendered, data.items.flatMap(item => [item.label, item.title, item.subtitle]),
                context + ' item order');
            for (const [index, item] of data.items.entries()) {
                for (const key of ['label', 'title', 'subtitle']) assertVisible(rendered, item[key],
                    context + ' item ' + index + ' ' + key);
            }
        } else if (block.preserve_type === 'usage-panels') {
            assertOrderedVisible(rendered, data.sections.flatMap(section => [section.label, section.description,
                ...(section.examples || []).flatMap(example => [example.en, example.ua]), ...(section.note ? [section.note] : [])]),
                context + ' section order');
            for (const [index, section] of data.sections.entries()) {
                for (const key of ['label', 'description', 'note']) assertVisible(rendered, section[key],
                    context + ' section ' + index + ' ' + key);
                for (const [j, example] of (section.examples || []).entries()) {
                    assertVisible(rendered, example.en, context + ' example ' + j + ' en');
                    assertVisible(rendered, example.ua, context + ' example ' + j + ' ua');
                }
            }
        } else if (block.preserve_type === 'mistakes-grid') {
            assertOrderedVisible(rendered, data.items.flatMap(item => [item.label, item.title, item.wrong, item.right,
                ...(item.hint ? [item.hint] : [])]), context + ' mistake order');
            for (const [index, item] of data.items.entries()) {
                for (const key of ['label', 'title', 'wrong', 'right', 'hint']) assertVisible(rendered, item[key],
                    context + ' item ' + index + ' ' + key);
            }
        } else if (block.preserve_type === 'summary-list') {
            assertOrderedVisible(rendered, data.items, context + ' summary order');
            for (const [index, item] of data.items.entries()) assertVisible(rendered, item, context + ' item ' + index);
        }
    }
    assert.ok(!/<\/?(?:p|strong|table)\b/.test(main.textContent), lesson.key + ' raw tags visible');
    assert.ok(!/\{a[1-3]\}/i.test(main.textContent), lesson.key + ' raw markers visible');
    dom.window.close();
    return {path: lesson.theory_path, h1: lesson.preserve_subtitle_strong, practice: 1,
        questions: lesson.exercise_count, answers: lesson.answer_count};
}
function assertLiveFiles(directory) {
    const data = master();
    return data.lessons.map(lesson => assertLiveHtml(lesson,
        fs.readFileSync(path.join(directory, lesson.key + '.html'), 'utf8')));
}
async function captureLiveFiles(directory) {
    fs.mkdirSync(directory, {recursive: true});
    for (const lesson of master().lessons) {
        const response = await fetch('http://gramlyze.loc' + lesson.theory_path,
            {headers: {Accept: 'text/html'}, signal: AbortSignal.timeout(60000)});
        assert.equal(response.status, 200, lesson.theory_path);
        const html = await response.text();
        assertLiveHtml(lesson, html);
        fs.writeFileSync(path.join(directory, lesson.key + '.html'), html, {flag: 'wx'});
    }
    return assertLiveFiles(directory);
}
if (require.main === module) {
    const [mode, directory] = process.argv.slice(2);
    if (mode === 'sources') console.log(JSON.stringify(assertSources()));
    else if (mode === 'database') {
        const privateDir = directory || path.join(root, 'storage/app/seo-m24-local');
        console.log(JSON.stringify(assertDatabaseSnapshot(readJson(path.join(privateDir, 'restored-targets.json')),
            readJson(path.join(privateDir, 'final-targets.json')), readJson(path.join(privateDir, 'm24-plan-v2.json')))));
    } else if (mode === 'html') console.log(JSON.stringify(assertLiveFiles(directory)));
    else if (mode === 'capture-html') captureLiveFiles(directory).then(rows => console.log(JSON.stringify(rows)))
        .catch(error => { console.error(error); process.exitCode = 1; });
    else throw new Error('Use sources, database or html mode.');
}
module.exports = {master, old, definition, assertDefinition, assertSources, assertDatabaseSnapshot, assertLiveHtml, assertLiveFiles, captureLiveFiles};
