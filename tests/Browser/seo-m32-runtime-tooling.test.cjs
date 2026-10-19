'use strict';
// Pure source-contract checks; no browser, database, ROOT copies or application boot.
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '../..');
const read = name => fs.readFileSync(path.join(root, name), 'utf8');

test('M32 transactional adapter binds only the three accepted Formal English owners and type/body updates', () => {
    const patch = read('app/Services/M32ContentPatch.php');
    const names = patch.match(/public const NAMES = \[([\s\S]*?)\];/)[1];
    assert.equal((names.match(/Database/g) || []).length, 3);
    for (const short of ['FormalRegisterAndNominalisationBasicsTheorySeeder', 'NominalisationFormalRegisterTheorySeeder', 'RegisterToneAndParaphraseTheorySeeder']) assert.ok(names.includes(short));
    assert.ok(names.includes('FormalEnglish')); assert.ok(!names.includes('Conditionals'));
    assert.match(patch, /extends M26ContentPatch/); assert.match(patch, /return \['type', 'body'\]/);
    assert.match(patch, /protectedFingerprints\(\$targetIds\)/);
});

test('SELECT inventory measures every protected table, non-target block and all M26–M31 snapshots', () => {
    const source = read('tools/diagnostics/inspect-m32-working-local.php');
    const tables = source.match(/foreach \(\[('pages'[\s\S]*?'seed_runs')\] as \$table\)/)[1].match(/'[^']+'/g).map(x => x.slice(1, -1));
    assert.equal(tables.length, 20); assert.equal(new Set(tables).size, 20); assert.equal(tables.filter(x => x !== 'text_blocks').length, 19);
    assert.match(source, /whereNotIn\('id', \$changedIds\)/); assert.match(source, /exact_full_author_payload/);
    for (const stage of [27, 28, 29, 30, 31]) assert.ok(source.includes(`m${stage}-m`));
    assert.match(source, /M26InteractivePractice::definition/); assert.match(source, /M26DetailPackage::load/);
    assert.match(source, /\['B2', 'C1', 'C1'\]/); assert.match(source, /count\(\$globalLevels\[\$q\[0\]->seeder\]\) === 1/);
    assert.match(source, /\$primary->count\(\) !== 1/);
    assert.doesNotMatch(source, /->(?:insert|update|delete|truncate)\s*\(/);
});

test('M32 proof tooling is CLI/Windows/read-only, exact .loc and safe public identity only', () => {
    const proof = read('tools/diagnostics/proof-m32-working-local.php'), guard = read('app/Services/M32LocalTargetGuard.php');
    assert.match(proof, /PHP_SAPI !== 'cli'/); assert.match(proof, /PHP_OS_FAMILY !== 'Windows'/);
    assert.match(proof, /local-proof-\[a-f0-9\]\{32\}/); assert.match(proof, /'gramlyze\.loc'/);
    assert.match(proof, /M32LocalTargetGuard::publicIdentity/); assert.match(guard, /http:\/\/gramlyze\.loc\/api\/_local\/m32-target-/);
    assert.match(guard, /CURLOPT_FOLLOWLOCATION=>false/); assert.match(guard, /CURLOPT_RESOLVE=>\['gramlyze\.loc:80:127\.0\.0\.1'\]/);
    assert.doesNotMatch(proof, /Route::|->(?:insert|update|delete|truncate)\s*\(|file_put_contents|fopen\(/);
    const safe = guard.match(/return \[([\s\S]*?)\];/)[1];
    assert.doesNotMatch(safe, /getConfig\(\)|password|app\.key|cookie|token/i);
});

test('M32 nonce preparation creates only two exclusive private evidence files, never routes or database writes', () => {
    const source = read('tools/diagnostics/prepare-m32-proof.php');
    assert.match(source, /PHP_SAPI !== 'cli'/); assert.match(source, /PHP_OS_FAMILY !== 'Windows'/);
    assert.match(source, /\$dir = \$root\.'\/storage\/app\/seo-m32-local'/);
    assert.match(source, /!is_dir\(\$dir\) \|\| is_link\(\$dir\)/);
    assert.match(source, /bin2hex\(random_bytes\(16\)\)/); assert.match(source, /fopen\(\$path, 'x'\)/);
    assert.equal((source.match(/\$exclusive\(/g) || []).length, 2);
    assert.match(source, /\$exclusive\(\$dir\.'\/routes-before-'\.\$nonce\.'\.bin', \$bytes\)/);
    assert.match(source, /\$exclusive\(\$dir\.'\/'\.\$proof, json_encode\(\['target' => 'gramlyze\.loc', 'nonce' => \$nonce\]/);
    assert.doesNotMatch(source, /file_put_contents|Route::|DB::|Console\\Kernel|bootstrap\/app|vendor\/autoload|->(?:insert|update|delete|truncate)\s*\(/);
});

test('M32 nonce preparation preserves exact raw route bytes and refuses an already installed proof', () => {
    const source = read('tools/diagnostics/prepare-m32-proof.php');
    assert.match(source, /file_get_contents\(\$root\.'\/routes\/api\.php'\)/);
    assert.match(source, /\$bytes === false \|\| str_contains\(\$bytes, 'm32-target-'\)/);
    assert.ok(source.indexOf('Routes unavailable or M32 proof already present.') < source.indexOf("$exclusive($dir.'/routes-before-"));
    assert.match(source, /hash\('sha256', \$bytes\)/);
    assert.doesNotMatch(source, /str_replace|preg_replace|json_decode|\.env|password|app\.key|cookie|token/i);
    const output = source.match(/echo json_encode\(\[([\s\S]*?)\], JSON_THROW_ON_ERROR\)/)[1];
    assert.deepEqual([...output.matchAll(/'([a-z0-9_]+)' =>/g)].map(match => match[1]), ['proof', 'nonce', 'routes_before_sha256']);
});

test('Finite source sync checks immutable projection and accepted bytes before any backup or ROOT write', () => {
    const source = read('tools/diagnostics/sync-m32-working-sources.php');
    const files = source.match(/\$files=\[([\s\S]*?)\];/)[1].match(/'[^']+'/g).map(x => x.slice(1, -1));
    assert.equal(files.length, 16); assert.equal(new Set(files).size, 16);
    assert.ok(files.every(file => !/^(?:\.env|vendor\/|public\/|routes\/|storage\/)/.test(file)));
    assert.match(source, /merge-base', '--is-ancestor', '326bf629e84cd9385ba2ac531a00fb30f82aab73'/);
    assert.match(source, /Original working checkout HEAD changed/); assert.match(source, /Unrelated working edit\/conflict/);
    assert.match(source, /Canonical source differs from the finite author projection/);
    assert.ok(source.indexOf("'checked-no-writes'") < source.indexOf("$directory=$root.'/storage/app/"));
    assert.ok(source.indexOf('Incomplete source manifest; no source writes.') < source.indexOf('file_put_contents'));
    assert.match(source, /Concurrent working edit; no source writes/);
});

test('Independent plan/evidence review binds dynamic native counts, full digest and exact backup allowlist', () => {
    const review = read('tools/diagnostics/review-m32-plan.php'), verify = read('tools/diagnostics/verify-m32-evidence.php');
    assert.match(review, /Saved preview digest differs/); assert.match(review, /Source backup finite allowlist differs/);
    assert.match(review, /\$plan\['inserts'\]\[\$insertIndex\+\+\]/); assert.match(review, /'inserted'=>\$expectedInserts/);
    assert.match(verify, /M26-M31 owner\/author payload changed/); assert.match(verify, /\$preview\['protected'\]\['text_blocks'\]/);
    assert.match(verify, /Final non-target block fingerprint missing/); assert.match(verify, /\$old\['fingerprints'\]\['text_blocks'\]\['count'\] \+ \$inserted/);
    assert.doesNotMatch(review + verify, /Console\\Kernel|Facades\\DB|DB::|->(?:insert|update|delete|truncate)\s*\(/);
});
