'use strict';
// Independent private BEFORE/AFTER comparison. Reads evidence only; never connects to a DB.
const fs = require('node:fs'), path = require('node:path'), assert = require('node:assert/strict'), crypto = require('node:crypto');
const PRIVATE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/theory-template-local';
const hash = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const [beforeName, afterName, outputName] = process.argv.slice(2);
for (const name of [beforeName, afterName]) assert.match(name, /^(?:registry|db)-(?:before|after)-v[1-9][0-9]*\.json$/u);
assert.match(outputName, /^db-compare-v[1-9][0-9]*\.json$/u);
const beforeBytes = fs.readFileSync(path.join(PRIVATE, beforeName)), afterBytes = fs.readFileSync(path.join(PRIVATE, afterName));
const before = JSON.parse(beforeBytes), after = JSON.parse(afterBytes);
for (const value of [before, after]) {
    assert.equal(value.schema, 'theory-template-local-registry-v1');
    assert.equal(value.read_only, true);
    assert.equal(value.target.root, 'D:/DEV/htdocs/gramlyze.loc');
    assert.equal(value.target.origin, 'http://gramlyze.loc');
    assert.equal(value.target.driver, 'mysql'); assert.equal(value.target.database, 'gr2'); assert.equal(value.target.port, 3306);
    assert.equal(value.target.hostname_verified, true); assert.equal(value.target.pid_inside_local_datadir, true);
}
const changedTables = [];
for (const table of [...new Set([...Object.keys(before.raw_tables), ...Object.keys(after.raw_tables)])].sort()) {
    const old = before.raw_tables[table], next = after.raw_tables[table];
    if (JSON.stringify(old) !== JSON.stringify(next)) changedTables.push({table, before: old ?? null, after: next ?? null});
}
function sourceRegistry(data) {
    return data.registry.map(page => ({
        page_id: page.page_id, type: page.type, owner: page.owner, slug: page.slug, title: page.title,
        page_row_sha256: page.page_row_sha256, category_id: page.category_id, category_type: page.category_type,
        category_language: page.category_language, category_path: page.category_path, resolver_exact: page.resolver_exact,
        url: page.url, block_locales: page.block_locales, default_fallback_locale: page.default_fallback_locale,
        variants: page.variants.map(variant => ({locale: variant.locale, url: variant.url,
            blocks: variant.blocks.map(block => Object.fromEntries([
                'block_id', 'uuid', 'owner', 'locale', 'type', 'column', 'sort_order', 'heading', 'body_bytes', 'body_sha256', 'markers',
            ].map(key => [key, block[key]]))),
        })),
        distinct_non_theory_consumers: page.distinct_non_theory_consumers,
    }));
}
const registryEqual = JSON.stringify(sourceRegistry(before)) === JSON.stringify(sourceRegistry(after));
const categoriesEqual = JSON.stringify(before.categories) === JSON.stringify(after.categories);
const nonTheoryEqual = JSON.stringify(before.non_theory_pages) === JSON.stringify(after.non_theory_pages);
const pass = changedTables.length === 0 && registryEqual && categoriesEqual && nonTheoryEqual;
const result = {schema: 'theory-template-db-comparison-v1', at: new Date().toISOString(),
    before: {file: beforeName, sha256: hash(beforeBytes)}, after: {file: afterName, sha256: hash(afterBytes)},
    tables: Object.keys(before.raw_tables).length, rows: Object.values(before.raw_tables).reduce((sum, row) => sum + row.count, 0),
    changed_tables: changedTables, registry_sources_equal: registryEqual, categories_equal: categoriesEqual, non_theory_equal: nonTheoryEqual,
    updates: pass ? 0 : null, inserts: pass ? 0 : null, deletes: pass ? 0 : null,
    reason: pass ? 'All rows/columns/timestamps/schema fingerprints equal across the full table set.' : 'Changed fingerprints require separate inspection; row-change counts are not inferred.',
    pass};
const output = path.join(PRIVATE, outputName);
fs.writeFileSync(output, JSON.stringify(result, null, 2) + '\n', {flag: 'wx'});
console.log(JSON.stringify({file: output, sha256: hash(fs.readFileSync(output)), pass,
    tables: result.tables, rows: result.rows, changed_tables: changedTables.map(item => item.table),
    updates: result.updates, inserts: result.inserts, deletes: result.deletes}));
if (!pass) process.exitCode = 1;
