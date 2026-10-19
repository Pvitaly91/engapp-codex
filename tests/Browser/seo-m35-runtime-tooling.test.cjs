'use strict';
// Read-only source contracts; no application boot or working-local writes.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const read = name => fs.readFileSync(path.resolve(__dirname, '../..', name), 'utf8');

test('Immutable M35 source bytes survive Git checkout without CRLF conversion', () => {
    const attributes = read('.gitattributes');
    for (const name of ['m35-m19-passive-reporting-before.json', 'm35-m19-passive-reporting.v1.json'])
        assert.ok(attributes.split(/\r?\n/).includes(`database/content-patches/${name} -text`));
});

test('Finite transactional adapter: three passive-voice owners, only type/body, protected fingerprints', () => {
    const source = read('app/Services/M35ContentPatch.php');
    const names = source.match(/public const NAMES = \[([\s\S]*?)\];/)[1];
    assert.equal((names.match(/Database/g) || []).length, 3);
    for (const name of ['PassiveReportingStructuresTheorySeeder', 'ComplexPassiveAndCausativeTheorySeeder', 'ComplexPassiveImpersonalStyleTheorySeeder']) assert.ok(names.includes(name));
    assert.match(source, /extends M26ContentPatch/); assert.match(source, /return \['type', 'body'\]/);
    assert.match(source, /protectedFingerprints\(\$targetIds\)/);
    for (const i of [0, 1, 2]) assert.ok(source.includes(`self::NAMES[${i}] => 'passive-voice'`));
});

test('Inventory is SELECT-only, all twenty table fingerprints and 29 M26–M34 owners', () => {
    const source = read('tools/diagnostics/inspect-m35-working-local.php');
    const tables = source.match(/foreach \(\[('pages'[\s\S]*?'seed_runs')\] as \$table\)/)[1].match(/'[^']+'/g);
    assert.equal(tables.length, 20); assert.equal(new Set(tables).size, 20);
    assert.match(source, /whereNotIn\('id', \$changedIds\)/); assert.match(source, /exact_full_author_payload/);
    for (const stage of [27, 28, 29, 30, 31, 32, 33, 34]) assert.ok(source.includes(`m${stage}-m`));
    assert.match(source, /M26InteractivePractice::definition/);
    assert.doesNotMatch(source, /->(?:insert|update|delete|truncate)\s*\(/);
});

test('Proof requires exact local Windows vhost/runtime and returns safe identity only', () => {
    const proof = read('tools/diagnostics/proof-m35-working-local.php'), guard = read('app/Services/M35LocalTargetGuard.php');
    assert.match(proof, /PHP_SAPI !== 'cli'/); assert.match(proof, /PHP_OS_FAMILY !== 'Windows'/);
    assert.match(guard, /http:\/\/gramlyze\.loc\/api\/_local\/m35-target-/);
    assert.match(guard, /CURLOPT_FOLLOWLOCATION=>false/); assert.match(guard, /CURLOPT_RESOLVE=>\['gramlyze\.loc:80:127\.0\.0\.1'\]/);
    const safe = guard.match(/return \[([\s\S]*?)\];/)[1]; assert.doesNotMatch(safe, /password|app\.key|cookie|token|getConfig\(\)/i);
    assert.doesNotMatch(proof, /Route::|file_put_contents|->(?:insert|update|delete|truncate)\s*\(/);
});

test('Nonce preparation preserves exact routes bytes and exclusively creates two private evidence files', () => {
    const source = read('tools/diagnostics/prepare-m35-proof.php');
    assert.match(source, /bin2hex\(random_bytes\(16\)\)/); assert.match(source, /fopen\(\$path, 'x'\)/);
    assert.equal((source.match(/\$exclusive\(/g) || []).length, 2);
    assert.match(source, /str_contains\(\$bytes, 'm35-target-'\)/);
    assert.doesNotMatch(source, /file_put_contents|Route::|DB::|\.env|password|cookie|token/i);
});

test('Finite source sync checks accepted ancestry, original ROOT HEAD and exact bytes before writes', () => {
    const source = read('tools/diagnostics/sync-m35-working-sources.php');
    const files = source.match(/\$files=\[([\s\S]*?)\];/)[1].match(/'[^']+'/g).map(s => s.slice(1, -1));
    assert.equal(files.length, 16); assert.equal(new Set(files).size, 16);
    assert.ok(files.every(f => !/^(?:\.env|vendor\/|public\/|routes\/|storage\/)/.test(f)));
    assert.match(source, /d2581e3bbcc8a5b89d0a93a454bb010d3c4606eb/);
    assert.match(source, /Unrelated working edit\/conflict/); assert.match(source, /Concurrent working edit; no source writes/);
    assert.ok(source.indexOf('Incomplete source manifest; no source writes.') < source.indexOf('file_put_contents'));
});

test('Independent exact review and postconditions protect all authors, IDs, anchors, banks and metadata', () => {
    const review = read('tools/diagnostics/review-m35-plan.php'), verify = read('tools/diagnostics/verify-m35-evidence.php');
    assert.match(review, /Saved preview digest differs/); assert.match(review, /Source backup finite allowlist differs/);
    assert.match(review, /count\(\$inventory\['regressions'\]\), 29/);
    assert.match(verify, /count\(\$new\['regressions'\]\), 29/);
    assert.match(verify, /Final non-target block fingerprint missing/); assert.match(verify, /Actual DB lost existing author anchor/);
    assert.doesNotMatch(review + verify, /Console\\Kernel|Facades\\DB|->(?:insert|update|delete|truncate)\s*\(/);
});
