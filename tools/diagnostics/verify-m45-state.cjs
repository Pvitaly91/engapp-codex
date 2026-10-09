'use strict';
// Compare independently captured SELECT-only evidence and the reviewed BEFORE plan.
// No Laravel boot, HTTP, database connection, source writes or private row output.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto'), assert = require('node:assert/strict');
const PRIVATE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m45-local';
const OWNERS = [
    'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFuturePerfectVsFutureContinuousTheorySeeder',
    'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder',
    'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder',
];
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const [afterName, outputName] = process.argv.slice(2);
assert.equal(process.argv.length, 4, 'verify-m45-state.cjs <m45-after-noop/final-vN.json> <state-verification-vN.json>');
assert.match(afterName, /^m45-(?:after-noop|final)-v[1-9][0-9]*\.json$/u);
assert.match(outputName, /^state-verification-v[1-9][0-9]*\.json$/u);
const inputNames = ['m45-before-v1.json', 'm45-before-v2.json', 'm45-preview-v1.json', afterName];
const hashes = {}, inputs = inputNames.map(name => {
    const filename = path.join(PRIVATE, name);
    assert.ok(fs.lstatSync(filename).isFile() && !fs.lstatSync(filename).isSymbolicLink());
    const bytes = fs.readFileSync(filename); hashes[name] = sha(bytes);
    return JSON.parse(bytes);
});
const [first, before, plan, after] = inputs;
for (const snapshot of [first, before, after]) {
    assert.deepEqual(snapshot.targets.map(target => target.identity), OWNERS);
    assert.deepEqual(snapshot.targets.map(target => target.page.id), [60, 61, 59]);
    assert.deepEqual(snapshot.target, first.target, 'Same confirmed physical local target');
    assert.equal(snapshot.target.root, 'D:/DEV/htdocs/gramlyze.loc');
    assert.equal(snapshot.target.database, 'gr2');
    assert.equal(snapshot.target.driver, 'mysql');
    assert.equal(snapshot.target.port, 3306);
    assert.ok(['localhost', '127.0.0.1'].includes(snapshot.target.host));
    assert.equal(Object.keys(snapshot.raw_tables).length, 46);
    assert.equal(Object.keys(snapshot.protected_tables).length, 46);
}
assert.deepEqual(first.raw_tables, before.raw_tables, 'All 46 raw tables unchanged between initial and fresh BEFORE');
assert.deepEqual(first.protected_tables, before.protected_tables);
assert.deepEqual(before.protected_tables, after.protected_tables, 'Complete non-target rows, pivots, banks, saved tests and timestamps unchanged');
assert.deepEqual(plan.names, OWNERS);
assert.equal(plan.state, 'before');
assert.deepEqual(plan.protected, before.protected_tables);
assert.equal(plan.updates.length, 27);
assert.equal(plan.inserts.length, 3);
const updates = new Map();
for (const update of plan.updates) {
    assert.ok(['pages', 'text_blocks'].includes(update.table));
    assert.ok(OWNERS.includes(update.seeder));
    const key = update.table + ':' + update.id;
    assert.ok(!updates.has(key), 'Unique reviewed update'); updates.set(key, update);
    const allowed = update.table === 'pages' ? ['text'] : ['type', 'body'];
    assert.ok(Object.keys(update.before).every(field => allowed.includes(field)));
    assert.ok(Object.keys(update.after).every(field => allowed.includes(field)));
}
const seenUpdates = new Set(), seenInserts = new Set(), summaries = [];
for (const [index, oldTarget] of before.targets.entries()) {
    const initial = first.targets[index], current = after.targets[index];
    assert.deepEqual(initial.page, oldTarget.page, 'Original target page unchanged before apply');
    assert.deepEqual(initial.blocks, oldTarget.blocks, 'Original target blocks unchanged before apply');
    assert.deepEqual(current.category_chain, oldTarget.category_chain);
    for (const field of ['identity', 'path', 'theory_path', 'course_path', 'url', 'test_path']) assert.equal(current[field], oldTarget[field]);
    assert.deepEqual(current.banks, oldTarget.banks, 'Exact linked/global bank IDs, types, levels and relationship scope');
    const pageUpdate = updates.get('pages:' + oldTarget.page.id);
    assert.ok(pageUpdate);
    for (const [field, value] of Object.entries(pageUpdate.before)) assert.deepEqual(oldTarget.page[field], value);
    assert.deepEqual(current.page, { ...oldTarget.page, ...pageUpdate.after }, 'Only reviewed page text changes');
    seenUpdates.add('pages:' + oldTarget.page.id);
    const oldBlocks = new Map(oldTarget.blocks.map(block => [block.id, block]));
    const currentBlocks = new Map(current.blocks.map(block => [block.id, block]));
    assert.equal(currentBlocks.size, current.blocks.length, 'Unique actual block IDs');
    let updated = 0;
    for (const [id, oldBlock] of oldBlocks) {
        assert.ok(currentBlocks.has(id), 'No original block deletion');
        const update = updates.get('text_blocks:' + id);
        if (update) {
            assert.equal(update.seeder, oldTarget.identity);
            for (const [field, value] of Object.entries(update.before)) assert.deepEqual(oldBlock[field], value);
            assert.deepEqual(currentBlocks.get(id), { ...oldBlock, ...update.after }, 'Only reviewed old block type/body change; metadata/timestamps intact');
            seenUpdates.add('text_blocks:' + id); updated++;
        } else assert.deepEqual(currentBlocks.get(id), oldBlock, 'Other locales/unreviewed target rows unchanged');
    }
    const inserted = current.blocks.filter(block => !oldBlocks.has(block.id));
    assert.equal(inserted.length, 1);
    for (const block of inserted) {
        const candidates = plan.inserts.filter(insert => insert.table === 'text_blocks' && insert.seeder === oldTarget.identity && insert.fields.uuid === block.uuid);
        assert.equal(candidates.length, 1, 'One exact reviewed insertion');
        const expected = candidates[0].fields;
        for (const [field, value] of Object.entries(expected)) assert.deepEqual(block[field], value, 'Inserted field ' + field);
        assert.deepEqual(Object.keys(block).sort(), [...Object.keys(expected), 'id', 'created_at', 'updated_at'].sort());
        assert.ok(Number.isInteger(block.id) && block.id > 0);
        assert.ok(typeof block.created_at === 'string' && block.created_at.length > 0);
        assert.equal(block.created_at, block.updated_at);
        assert.ok(!seenInserts.has(block.uuid)); seenInserts.add(block.uuid);
    }
    summaries.push({ identity: oldTarget.identity, page_id: oldTarget.page.id, existing_blocks: oldBlocks.size,
        updated_blocks: updated, inserted_blocks: inserted.length, banks_unchanged: true,
        banks: current.banks.map(bank => ({ seeder_class: bank.seeder_class, linked_count: bank.linked_count, global_count: bank.global_count, linked_types: bank.linked_types })) });
}
assert.equal(seenUpdates.size, updates.size, 'Every reviewed update accounted for, none outside target rows');
assert.equal(seenInserts.size, plan.inserts.length);
const rawChanged = Object.keys(before.raw_tables).filter(table => JSON.stringify(before.raw_tables[table]) !== JSON.stringify(after.raw_tables[table])).sort();
assert.deepEqual(rawChanged, ['pages', 'text_blocks']);
assert.equal(after.raw_tables.pages.count, before.raw_tables.pages.count);
assert.equal(after.raw_tables.text_blocks.count, before.raw_tables.text_blocks.count + 3);
const report = { schema: 'm45-independent-state-verification-v1', verified_at: new Date().toISOString(), inputs_sha256: hashes,
    pass: true, raw_tables: 46, protected_tables: 46, raw_changed_tables: rawChanged,
    changes: { updated: 27, inserted: 3, deleted: 0 }, initial_to_fresh_before_unchanged: true,
    full_non_target_rows_pivots_banks_progress_timestamps_unchanged: true, targets: summaries,
    limitations: ['Verifies independently captured DB snapshots and reviewed BEFORE plan, not a fresh live connection.', 'No HTTP, DB connection, Laravel boot or ROOT source writes.'] };
const output = path.join(PRIVATE, outputName);
assert.equal(fs.existsSync(output), false, 'Exclusive private verification evidence');
fs.writeFileSync(output, JSON.stringify(report, null, 2) + '\n', { flag: 'wx' });
console.log(JSON.stringify({ file: output, sha256: sha(fs.readFileSync(output)), pass: true, tables: 46,
    changed_tables: rawChanged, updated: 27, inserted: 3, deleted: 0, banks_unchanged: true, db_access: false }));
