'use strict';
// Mechanical freeze from independently reviewed author candidates and real BEFORE.
// No application bootstrap, HTTP or database access; no rewriting frozen packages.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto'), assert = require('node:assert/strict');
const ROOT = 'D:/DEV/htdocs/gramlyze.loc', WT = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
const PRIVATE = path.join(ROOT, 'storage/app/seo-m44-local');
const BASE = 'd3c5fa811e3e7eb5de2e348a6cd2d0bed3a76ba0', DESIGN = 'be3c724053aa57024b2b404251104aa465d61424';
const candidates = [
    ['author-a-candidate.v4.json', '693fd311c9233e54ca5d37b7e7e59861b1bf6f5c2f83ca0b1771405f90cdea1c'],
    ['author-b-candidate.v2.json', '993cc84d3ad3346bf8cdb1941a991c2733c77795bfee90190415845ee07f3171'],
    ['author-c-candidate.v2.json', '269cb62646804dd2c70482e8462d4f21b590efa04aee7ad849401df4effc41dd'],
];
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const before = JSON.parse(fs.readFileSync(path.join(PRIVATE, 'm44-before-v1.json')));
const documents = candidates.map(([name, checksum]) => { const bytes = fs.readFileSync(path.join(PRIVATE, name));
    assert.equal(sha(bytes), checksum); assert.ok(!bytes.subarray(0, 3).equals(Buffer.from([239, 187, 191])));
    assert.equal(bytes.includes(13), false); assert.equal(bytes.at(-1), 10); assert.notEqual(bytes.at(-2), 10);
    const value = JSON.parse(bytes); assert.equal(value.package, 'M44'); assert.equal(value.lessons.length, 1); return value; });
const lessons = documents.map((document, i) => {
    const lesson = structuredClone(document.lessons[0]), target = before.targets[i];
    assert.equal(lesson.identity, target.identity); assert.equal(lesson.title, target.page.title);
    assert.equal(lesson.theory_path, target.theory_path); assert.deepEqual(lesson.category_path, target.category_chain.map(c => c.slug));
    assert.equal(lesson.practice.length, 6); assert.equal(lesson.baseline_definition_sha256, target.definition_sha256);
    lesson.resolved_navigation = target.navigation;
    return lesson;
});
const sourceMap = new Map();
const sourceIdMappings = [];
for (const [reader, document] of documents.entries()) for (const source of document.sources) {
    let sourceId = source.id;
    if (sourceMap.has(sourceId) && sourceMap.get(sourceId).url.replace(/\/$/u, '') !== source.url.replace(/\/$/u, '')) {
        sourceId += '-' + ['A', 'B', 'C'][reader];
        sourceIdMappings.push({lesson: ['A', 'B', 'C'][reader], from: source.id, to: sourceId, reason: 'Distinct actually-read URL variants retain separate access records.'});
        const rewriteRefs = value => { if (!value || typeof value !== 'object') return;
            for (const [key, item] of Object.entries(value)) {
                if (key === 'source_refs' && Array.isArray(item)) value[key] = item.map(id => id === source.id ? sourceId : id);
                else rewriteRefs(item);
            }
        };
        rewriteRefs(lessons[reader]);
    }
    if (sourceMap.has(sourceId)) {
        const existing = sourceMap.get(sourceId);
        assert.equal(existing.url.replace(/\/$/u, ''), source.url.replace(/\/$/u, ''), 'Conflicting editorial source ID');
        existing.reading_records.push({lesson: ['A', 'B', 'C'][reader], record: source});
    } else sourceMap.set(sourceId, {...source, id: sourceId, reading_records: [{lesson: ['A', 'B', 'C'][reader], record: source}]});
}
const master = {schema: 'gramlyze.authored-layer-master.v1', package: 'M44', version: '1.0.0',
    status: 'authored_editorially_reviewed_ready_for_local_implementation',
    author: 'Codex: original teaching prose and practice; internal author, independent cross-review and primary-source checks',
    locale: 'uk', base_commit: BASE, design_reference_commit: DESIGN,
    policy: {...documents[0].policy, personally_sentence_approved_by_user: false, implementation_authorized_by_user: true, frozen_master_edit: false},
    sources: [...sourceMap.values()], source_id_mappings: sourceIdMappings,
    author_candidates: candidates.map(([name, checksum]) => ({name, sha256: checksum})), lessons};
const ids = new Set();
function example(value) { assert.equal(typeof value.en, 'string'); assert.ok(value.en.trim()); assert.equal(typeof value.uk, 'string'); assert.ok(value.uk.trim()); }
function identity(node) { assert.match(node.id, /^m44-[a-z0-9-]+$/u); assert.ok(!ids.has(node.id), 'Duplicate source ID: ' + node.id); ids.add(node.id); }
for (const lesson of lessons) {
    const checkRefs = value => { if (!value || typeof value !== 'object') return; for (const [key, item] of Object.entries(value)) {
        if (key === 'source_refs') item.forEach(id => assert.ok(sourceMap.has(id), 'Unknown editorial source: ' + id)); else checkRefs(item);
    }}; checkRefs(lesson);
    for (const section of lesson.sections) { identity(section); assert.ok(['forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list'].includes(section.native_kind));
        for (const point of section.points) { identity(point); assert.ok(point.paragraphs_uk.length); point.examples.forEach(example);
            if (point.detail) { identity(point.detail); assert.ok(point.detail.paragraphs_uk.length >= 2); point.detail.examples.forEach(example); }
            if (section.native_kind === 'mistakes-grid') for (const field of ['wrong_en', 'wrong_uk', 'right_en', 'right_uk']) assert.ok(point[field]);
        }
        for (const row of section.table?.rows || []) { assert.equal(row.length, section.table.columns.length); for (const cell of row) if (cell && typeof cell === 'object' && cell.en) example(cell); }
        (section.note_examples || []).forEach(example);
    }
    for (const [i, task] of lesson.practice.entries()) { identity(task); assert.equal(task.source_index, i + 1); assert.equal(task.scoring, 'all_required_controls');
        assert.ok(task.prompt_uk && task.context_uk && task.feedback.paragraphs_uk.length); task.feedback.answer_examples.forEach(example);
        for (const control of task.controls) { identity(control); assert.equal(control.required, true); assert.ok(['select', 'choice', 'manual', 'tokens'].includes(control.kind));
            if (['manual', 'tokens'].includes(control.kind)) { assert.ok(control.accepted_answers.includes(control.canonical_answer)); assert.ok(control.tokens.every(t => typeof t === 'string' && t.trim()));
                if (control.kind === 'manual') assert.equal(control.tokens.join(' '), control.canonical_answer);
            } else assert.equal(control.options.filter(o => o.value === control.correct_value).length, 1);
        }
    }
}
const mapping = {schema: 'gramlyze.m44-native-map.v1', base: BASE, design_reference: DESIGN, targets: lessons.map((lesson, i) => {
    const old = JSON.parse(fs.readFileSync(path.join(WT, lesson.definition_path)));
    assert.equal(old.seeder.class, lesson.identity);
    const blocks = old.page.blocks, nav = blocks.findIndex(b => b.type === 'navigation-chips'); assert.ok(nav >= 1);
    const available = blocks.map((_, slot) => slot).filter(slot => slot !== 0 && slot !== nav);
    let next = blocks.length;
    const colors = ['blue', 'emerald', 'sky', 'amber'];
    const section_order = lesson.sections.map((section, j) => ({section_id: section.id, section_index: j, slot: available[j] ?? next++,
        component: section.native_kind, table_columns: section.table?.columns.length || 0,
        points: section.points.map((point, k) => ({id: point.id, point_index: k, color: point.color || colors[k % colors.length],
            decision: point.detail ? 'point_detail' : 'visible_basic', detail_id: point.detail?.id || null}))}));
    assert.ok(section_order.length >= available.length, 'Useful old slots must remain mapped');
    return {identity: lesson.identity, definition_path: lesson.definition_path, theory_path: lesson.theory_path,
        hero_slot: 0, navigation_slot: nav, practice_slot: next, expected_inserts: next + 1 - blocks.length,
        section_order, navigation_changes: before.targets[i].navigation.filter(n => n.old_url !== n.url)};
})};
function renderUk() {
    let out = '# M44 — Future Forms, авторська редакція 1.0.0\n\nСтворено й локально впроваджується в межах дозволу на M44. Це не особисте погодження кожного речення користувачем.\n';
    const examples = items => items.map(e => '\n> ' + e.en + '\n>\n> ' + e.uk + (e.note_uk ? '\n\nОкремий коментар: ' + e.note_uk : '') + '\n').join('');
    for (const lesson of lessons) { out += '\n## ' + lesson.title + '\n\n' + lesson.subtitle + '\n';
        for (const hero of lesson.hero) out += '\n### ' + hero.label + '\n\n' + hero.text + '\n\nФормула: ' + hero.formula + '\n';
        for (const section of lesson.sections) { out += '\n### ' + section.title + '\n\n' + (section.intro_uk ? section.intro_uk + '\n' : '');
            for (const point of section.points) { out += '\n#### ' + point.title + '\n\n' + (point.formula ? 'Формула: ' + point.formula + '\n\n' : '') + point.paragraphs_uk.join('\n\n') + '\n';
                if (point.wrong_en) out += '\nНеправильна форма: ' + point.wrong_en + '\n\n' + point.wrong_uk + '\n\nПравильна форма: ' + point.right_en + '\n\n' + point.right_uk + '\n';
                out += examples(point.examples); if (point.detail) out += '\nДокладніше: ' + point.detail.title + '\n\n' + point.detail.paragraphs_uk.join('\n\n') + '\n' + examples(point.detail.examples);
            }
            if (section.table) { out += '\nТаблиця: ' + section.table.columns.join(' | ') + '\n'; for (const row of section.table.rows) for (const cell of row) out += typeof cell === 'string' ? '\n' + cell + '\n' : cell.en ? examples([cell]) : '\n' + (cell.text_uk || cell.formula) + '\n'; }
            out += '\n' + (section.notes_uk || []).join('\n\n') + '\n' + examples(section.note_examples || []);
        }
        out += '\n### Практика\n'; for (const task of lesson.practice) { out += '\n#### ' + task.source_index + '. ' + task.title + '\n\n' + task.prompt_uk + '\n\n' + task.context_uk + '\n';
            for (const control of task.controls) { out += '\n' + control.label_uk + '\n' + (control.stimulus_en ? '\n' + control.stimulus_en + '\n' : '');
                for (const option of control.options || []) out += '\nВаріант: ' + option.label + '\n';
                if (control.tokens) out += '\nБанк токенів: ' + control.tokens.join(' | ') + '\n';
                if (control.canonical_answer) out += '\nКлюч: ' + control.canonical_answer + '\n\nДопустимі форми: ' + control.accepted_answers.join(' | ') + '\n';
                else out += '\nКлюч: ' + control.options.find(o => o.value === control.correct_value).label + '\n';
            }
            out += '\nПояснення: ' + task.feedback.paragraphs_uk.join('\n\n') + '\n' + examples(task.feedback.answer_examples);
        }
    }
    return out.trimEnd() + '\n';
}
const notes = '# M44 — редакторські примітки 1.0.0\n\nТри нові авторські редакції перевірено внутрішніми окремими author/cross-review проходами: граматика, контекст, переклад, рівень, практика й альтернативи. Це не зовнішня сертифікація й не особисте погодження користувачем речення за реченням.\n\n'
    + lessons.map(l => '## ' + l.title + '\n\n' + l.editorial_changes.map(c => '- ' + (c.old_issue || c.before || JSON.stringify(c)) + ' → ' + (c.new_rule || c.after || '')).join('\n') + '\n').join('\n')
    + '\n## Джерела та доступ\n\n' + [...sourceMap.values()].map(s => '- [' + s.title + '](' + s.url + ') — ' + (s.access || '')).join('\n')
    + '\n\nBC редакційні статті й Cambridge editorial text звірені; 403/індексоване читання прямо відокремлено від успішного прямого відкриття. Учнівські коментарі не є нормативним джерелом. Повні records усіх читачів збережено в master.sources.reading_records. Приклади й вправи створені для цього пакета.\n';
const files = {
    'docs/content/m44-authored-future-forms.v1.0.0.json': JSON.stringify(master, null, 2) + '\n',
    'docs/content/m44-authored-future-forms.v1.0.0.uk.md': renderUk(),
    'docs/content/m44-editorial-notes.v1.0.0.md': notes,
    'docs/content/m44-native-mapping.v1.0.0.json': JSON.stringify(mapping, null, 2) + '\n',
};
for (const [relative, bytes] of Object.entries(files)) { assert.equal(bytes.includes('\r'), false); assert.ok(bytes.endsWith('\n') && !bytes.endsWith('\n\n'));
    assert.equal(fs.existsSync(path.join(WT, relative)), false, 'Do not replace a frozen source'); fs.writeFileSync(path.join(WT, relative), bytes, {flag: 'wx'}); }
const checksums = Object.fromEntries(Object.entries(files).map(([name, bytes]) => [name, sha(Buffer.from(bytes))]));
fs.writeFileSync(path.join(WT, 'docs/content/m44-checksums.v1.0.0.json'), JSON.stringify({schema: 'gramlyze.m44-frozen-checksums.v1', version: '1.0.0', files: checksums}, null, 2) + '\n', {flag: 'wx'});
console.log(JSON.stringify({base: BASE, checksums, counts: lessons.map(l => ({sections: l.sections.length,
    details: l.sections.flatMap(s => s.points).filter(p => p.detail).length, tasks: l.practice.length, controls: l.practice.flatMap(t => t.controls).length})),
    inserts: mapping.targets.map(t => t.expected_inserts), navigationChanges: mapping.targets.map(t => t.navigation_changes.length)}));
