<?php

// Independent exact diff review against frozen author/source BEFORE and read-only DB inventory.
// No Laravel kernel, .env load, HTTP request, DB access or evidence writes.
if (PHP_SAPI !== 'cli' || count($argv) < 3 || count($argv) > 4) { exit(1); }
$source = dirname(__DIR__, 2); $root = 'D:/DEV/htdocs/gramlyze.loc'; $directory = $root.'/storage/app/seo-m45-local';
require $source.'/vendor/autoload.php';
$app = new Illuminate\Foundation\Application($source);
use App\Services\M45ContentPatch as Patch;
use App\Support\M45FutureComparisonsPackage as Package;

$name = $argv[1]; $backupName = $argv[2]; $inventoryName = $argv[3] ?? 'm45-before-v1.json';
if (!preg_match('/^m45-preview-[a-z0-9-]+\.json$/D', $name) || $backupName !== 'source-sync-before-v1'
    || !preg_match('/^m45-before-v[1-9][0-9]*\.json$/D', $inventoryName)) { throw new RuntimeException('Exact M45 preview/source-backup/inventory basenames required.'); }
$read = static function (string $path): array {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('Missing/linked private M45 review evidence.'); }
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$assert = static function ($actual, $expected, string $message): void { if ($actual !== $expected) { throw new RuntimeException($message); } };
$plan = $read($directory.'/'.$name); $inventory = $read($directory.'/'.$inventoryName);
[$before, $package] = Package::load($source); Package::validate($before, $package, $source); Package::authorMaster($before, $source);
$assert($plan['state'], 'before', 'M45 review requires unapplied state.');
$copy = $plan; unset($copy['sha256']); $assert(Patch::digest($copy), $plan['sha256'], 'M45 saved preview digest differs.');
$assert($plan['patch'], 'm45-compact-future-comparisons-v1', 'M45 patch identity differs.');
$assert($plan['version'], 1, 'M45 preview version differs.');
$assert($plan['names'], Patch::NAMES, 'M45 exact owner set differs.');
$assert($plan['connection'], ['driver' => 'mysql', 'database' => 'gr2', 'server' => gethostname(), 'port' => 3306], 'Wrong M45 physical local target.');
$assert($inventory['target'], ['root' => $root, 'driver' => 'mysql', 'host' => 'localhost', 'port' => 3306, 'database' => 'gr2'], 'M45 inventory physical target differs.');
$assert(array_column($inventory['targets'], 'identity'), Patch::NAMES, 'M45 inventory owner order/scope differs.');
$assert($plan['protected'], $inventory['protected_tables'], 'M45 protected data differs from independent BEFORE (all actual tables).');
$expectedPaths = [...Patch::sourcePaths($before, $package, $source), 'private:source-sync.json']; sort($expectedPaths);
$assert(array_keys($plan['sources']), $expectedPaths, 'M45 source binding allowlist differs.');
foreach ($plan['sources'] as $path => $hash) {
    $file = str_starts_with($path, 'private:') ? $directory.'/'.substr($path, 8) : $source.'/'.$path;
    if (!is_file($file) || is_link($file)) { throw new RuntimeException('M45 review source missing or linked.'); }
    $assert(hash_file('sha256', $file), $hash, 'Stale M45 source: '.$path);
}

$expectedUpdates = []; $expectedInserts = [];
foreach ($package['targets'] as $index => $target) {
    $identity = $target['identity']; $old = $before['targets'][$index]['before']; $new = $target['after'];
    $snapshot = $plan['pages'][$identity]; $owner = $snapshot['page']; $rows = $snapshot['blocks'];
    $actualBefore = $inventory['targets'][$index];
    $assert($actualBefore['db_matches_frozen_source'] ?? $actualBefore['source_db_exact'], true, 'M45 independent frozen BEFORE/database gate failed.');
    $assert($owner, $actualBefore['page'], 'M45 page BEFORE changed before preview.');
    $assert($rows, $actualBefore['blocks'], 'M45 block BEFORE changed before preview.');
    $assert($snapshot['category_ancestry'], $actualBefore['category_chain'], 'M45 category BEFORE changed.');
    $assert(array_column($snapshot['category_ancestry'], 'slug'), Patch::ANCESTRIES[$identity], 'M45 wrong category ancestry.');
    $originalSource = file_get_contents($directory.'/'.$backupName.'/'.$target['path']);
    $assert(hash('sha256', $originalSource), $actualBefore['definition_sha256'], 'M45 raw author BEFORE bytes do not match independently inspected definition.');
    $assert(json_decode($originalSource, true, flags: JSON_THROW_ON_ERROR), $old, 'M45 canonical BEFORE value differs from its exact source backup.');
    // Other primary/legacy own-linked banks remain protected, but are not the new practice widget's pool.
    $ownBanks = array_values(array_filter($actualBefore['banks'], static fn ($bank) => $bank['seeder_class'] === $before['targets'][$index]['bank']['seeder_class']));
    $assert(count($ownBanks), 1, 'M45 exact compose bank missing or ambiguous.');
    $ownBank = $ownBanks[0];
    $assert($ownBank['full_own_bank'], true, 'M45 compose bank is not completely own-linked.');
    $assert($ownBank['linked_count'], $before['targets'][$index]['bank']['question_count'], 'M45 own-linked compose count differs.');
    $assert($ownBank['global_count'], $before['targets'][$index]['bank']['question_count'], 'M45 compose bank global count differs.');
    $assert($ownBank['linked_types'], [(string) $before['targets'][$index]['bank']['question_type']], 'M45 compose bank type differs.');
    $assert(Patch::digest($ownBank['global_ids']), $before['targets'][$index]['bank']['question_ids_sha256'], 'M45 compose bank exact IDs differ.');
    if ($old['page']['subtitle_text'] !== $new['page']['subtitle_text']) {
        $expectedUpdates[] = ['table' => 'pages', 'id' => $owner['id'], 'uuid' => null, 'seeder' => $identity,
            'before' => ['text' => $old['page']['subtitle_text']], 'after' => ['text' => $new['page']['subtitle_text']]];
    }
    $subtitle = ['type' => 'subtitle', 'body' => $old['page']['subtitle_html'], 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
    $newSubtitle = $subtitle; $newSubtitle['body'] = $new['page']['subtitle_html'];
    $oldConfigs = [$subtitle, ...$old['page']['blocks']]; $newConfigs = [$newSubtitle, ...$new['page']['blocks']];
    foreach ($oldConfigs as $position => $config) {
        $updated = $newConfigs[$position];
        if ($config['type'] === $updated['type'] && $config['body'] === $updated['body']) { continue; }
        $uuid = App\Support\M26DetailPackage::uuid($identity, $config, $position);
        $matches = array_values(array_filter($rows, static fn ($row) => $row['uuid'] === $uuid));
        $assert(count($matches), 1, 'M45 existing update row missing or ambiguous.');
        $expectedUpdates[] = ['table' => 'text_blocks', 'id' => $matches[0]['id'], 'uuid' => $uuid, 'seeder' => $identity,
            'before' => ['type' => $config['type'], 'body' => $config['body']], 'after' => ['type' => $updated['type'], 'body' => $updated['body']]];
    }
    $assert(count($newConfigs) - count($oldConfigs), 1, 'M45 exact added-block scope differs.');
    foreach (array_slice($newConfigs, count($oldConfigs), null, true) as $position => $config) {
        $fields = ['uuid' => App\Support\M26DetailPackage::uuid($identity, $config, $position),
            'page_id' => $owner['id'], 'page_category_id' => $owner['page_category_id'], 'locale' => 'uk', 'type' => $config['type'],
            'column' => $config['column'], 'heading' => $config['heading'] ?? null, 'css_class' => $config['css_class'] ?? null,
            'sort_order' => $position, 'body' => $config['body'], 'level' => $config['level'] ?? null, 'seeder' => $identity];
        $expectedInserts[] = ['table' => 'text_blocks', 'seeder' => $identity, 'fields' => $fields, 'sha256' => Patch::digest($fields)];
    }
}
$actualUpdates = array_map(static function ($change) {
    unset($change['before_sha256'], $change['after_sha256']); return $change;
}, $plan['updates']);
$assert($actualUpdates, $expectedUpdates, 'M45 exact update fields/values/count differs.');
$assert($plan['inserts'], $expectedInserts, 'M45 exact three inserts differ.');
$sync = $read($directory.'/source-sync.json');
$records = $sync['files'];
$assert(array_column($records, 'path'), array_values(array_filter(Patch::sourcePaths($before, $package, $source), fn($path)=>str_contains($path,'M45')||str_contains($path,'m45')||in_array($path,Patch::SHARED_VIEWS,true)||in_array($path,array_column($package['targets'],'path'),true))), 'M45 finite source-backup allowlist differs.');
foreach ($records as $record) {
    $shared = in_array($record['path'], Patch::SHARED_VIEWS, true);
    $assert($record['mode'], $shared ? 'shared-reviewed-hunks' : 'finite-sync', 'M45 source sync mode differs.');
    if ($record['root_before_sha256'] !== null) { $assert(hash_file('sha256', $directory.'/'.$backupName.'/'.$record['path']), $record['root_before_sha256'], 'M45 source original backup differs.'); }
    $assert(hash_file('sha256', $root.'/'.$record['path']), $record['root_after_sha256'], 'M45 synced ROOT bytes differ.');
    $assert(hash_file('sha256', $source.'/'.$record['path']), $record['worktree_sha256'], 'M45 current WT bytes differ.');
}
echo json_encode(['exact_field_review' => true, 'updated' => count($expectedUpdates), 'inserted' => count($expectedInserts),
    'deleted' => 0, 'source_backup_records' => count($records), 'protected_tables' => count($plan['protected']),
    'no_db_access' => true, 'no_evidence_writes' => true], JSON_THROW_ON_ERROR)."\n";
