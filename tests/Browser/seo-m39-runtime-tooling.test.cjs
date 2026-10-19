'use strict';
// Read-only source contracts; no application boot or working-local writes.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const read = name => fs.readFileSync(path.resolve(__dirname, '../..', name), 'utf8');

test('Immutable M39 source bytes survive Git checkout without CRLF conversion', () => {
    const attributes = read('.gitattributes');
    for (const name of ['m39-m23-authored-revision-before.json', 'm39-m23-authored-revision.v1.json'])
        assert.ok(attributes.split(/\r?\n/).includes(`database/content-patches/${name} -text`));
});

test('Finite transactional adapter: exact identity/two-root category mapping with frozen author source, only type/body, protected fingerprints', () => {
    const source = read('app/Services/M39ContentPatch.php');
    const names = source.match(/public const NAMES = \[([\s\S]*?)\];/)[1];
    assert.equal((names.match(/Database/g) || []).length, 3);
    for (const name of ['NominalStyleAndInformationDensityTheorySeeder', 'C1MixedRevisionTheorySeeder', 'C2MixedRevisionTheorySeeder']) assert.ok(names.includes(name));
    assert.match(source, /extends M26ContentPatch/); assert.match(source, /return \['type', 'body'\]/);
    assert.match(source, /protectedFingerprints\(\$targetIds\)/);
    for (const [i, category] of ['formal-english', 'mixed-revision', 'mixed-revision'].entries()) assert.ok(source.includes(`self::NAMES[${i}] => '${category}'`));
    assert.ok(source.includes("self::NAMES[2] => ['mixed-revision']"));
    assert.match(source, /array_column\(\$category,'slug'\) !== self::ANCESTRIES\[\$name\]/);
});

test('Inventory is SELECT-only, all twenty table fingerprints and complete M26–M38 owners', () => {
    const source = read('tools/diagnostics/inspect-m39-working-local.php');
    const tables = source.match(/foreach \(\[('pages'[\s\S]*?'seed_runs')\] as \$table\)/)[1].match(/'[^']+'/g);
    assert.equal(tables.length, 20); assert.equal(new Set(tables).size, 20);
    assert.match(source, /whereNotIn\('id', \$changedIds\)/); assert.match(source, /exact_full_author_payload/);
    for (const stage of [27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38]) assert.ok(source.includes(`m${stage}-m`));
    assert.match(source, /M26InteractivePractice::definition/);
    assert.doesNotMatch(source, /->(?:insert|update|delete|truncate)\s*\(/);
});

test('Proof requires exact local Windows vhost/runtime and returns safe identity only', () => {
    const proof = read('tools/diagnostics/proof-m39-working-local.php'), guard = read('app/Services/M39LocalTargetGuard.php');
    assert.match(proof, /PHP_SAPI !== 'cli'/); assert.match(proof, /PHP_OS_FAMILY !== 'Windows'/);
    assert.match(guard, /http:\/\/gramlyze\.loc\/api\/_local\/m39-target-/);
    assert.match(guard, /CURLOPT_FOLLOWLOCATION=>false/); assert.match(guard, /CURLOPT_RESOLVE=>\['gramlyze\.loc:80:127\.0\.0\.1'\]/);
    const safe = guard.match(/return \[([\s\S]*?)\];/)[1]; assert.doesNotMatch(safe, /password|app\.key|cookie|token|getConfig\(\)/i);
    assert.doesNotMatch(proof, /Route::|file_put_contents|->(?:insert|update|delete|truncate)\s*\(/);
});

test('Nonce preparation preserves exact routes bytes and exclusively creates two private evidence files', () => {
    const source = read('tools/diagnostics/prepare-m39-proof.php');
    assert.match(source, /bin2hex\(random_bytes\(16\)\)/); assert.match(source, /fopen\(\$path, 'x'\)/);
    assert.equal((source.match(/\$exclusive\(/g) || []).length, 2);
    assert.match(source, /str_contains\(\$bytes, 'm39-target-'\)/);
    assert.doesNotMatch(source, /file_put_contents|Route::|DB::|\.env|password|cookie|token/i);
});

test('Finite source sync checks accepted ancestry, original ROOT HEAD and exact bytes before writes', () => {
    const source = read('tools/diagnostics/sync-m39-working-sources.php');
    const files = source.match(/\$files=\[([\s\S]*?)\];/)[1].match(/'[^']+'/g).map(s => s.slice(1, -1));
    assert.ok(files.length >= 16); assert.equal(new Set(files).size, files.length);
    assert.ok(files.every(f => !/^(?:\.env|vendor\/|public\/|routes\/|storage\/)/.test(f)));
    assert.match(source, /a525a5e03b59904b9fe5987abacf5c038fe5293f/);
    assert.match(source, /Unrelated working edit\/conflict/); assert.match(source, /Concurrent working edit; no source writes/);
    assert.ok(source.indexOf('Incomplete source manifest; no source writes.') < source.indexOf('file_put_contents'));
});

test('Independent exact review and postconditions protect all authors, IDs, anchors, banks and metadata', () => {
    const review = read('tools/diagnostics/review-m39-plan.php'), verify = read('tools/diagnostics/verify-m39-evidence.php');
    assert.match(review, /Saved preview digest differs/); assert.match(review, /Source backup finite allowlist differs/);
    assert.match(review, /count\(\$inventory\['regressions'\]\), 41/);
    assert.match(verify, /count\(\$new\['regressions'\]\), 41/);
    assert.match(verify, /Final non-target block fingerprint missing/); assert.match(verify, /Actual DB lost existing author anchor/);
    assert.doesNotMatch(review + verify, /Console\\Kernel|Facades\\DB|->(?:insert|update|delete|truncate)\s*\(/);
});


test('M23 master and author policy remain frozen and are never writes in finite source tooling', () => {
    const crypto = require('node:crypto');
    const bytes = fs.readFileSync(path.resolve(__dirname, '../..', 'docs/content/m23-authored-content.v1.json'));
    const normalized = Buffer.from(bytes.toString('utf8').replace(/\r\n/g, '\n'), 'utf8');
    assert.equal(crypto.createHash('sha256').update(normalized).digest('hex'), 'eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6');
    const master = JSON.parse(bytes.toString('utf8'));
    for (const key of ['rewrite', 'summarise', 'translate_again', 'add_examples', 'add_exercises', 'production_write', 'mixed_question_bank_write'])
        assert.equal(master.content_policy[key], false);
    const sync = read('tools/diagnostics/sync-m39-working-sources.php');
    assert.doesNotMatch(sync.match(/\$files=\[([\s\S]*?)\];/)[1], /m23-authored-content|m23-author-sources/);
});
test('Practice uses complete original keys for choice subparts, not new educational explanations', () => {
    const projection = read('tools/diagnostics/m39-practice-projection.php');
    assert.match(projection, /\$keys = array_map\('m39Plain', \$author\['answers'\]\)/);
    assert.match(projection, /'options' => \['a', 'b', 'c'\], 'answer' => 'b'/);
    assert.doesNotMatch(projection, /m39_semantic_checks|statistical|new examples/);
    assert.match(projection, /need not alternative/);
});
