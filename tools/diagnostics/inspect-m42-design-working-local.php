<?php

// Finite M42 presentation-only evidence. Never writes application data or exposes credentials.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
$source = dirname(__DIR__, 2);
$name = $argv[1] ?? null;
if (count($argv) !== 2 || !is_string($name)
    || !preg_match('/^m42-design-(?:before|after|after-final)-v[1-9][0-9]*\.json$/D', $name)) {
    throw new RuntimeException('Use one exclusive private M42 design evidence basename.');
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root)) { throw new RuntimeException('Wrong working root.'); }
$db = Illuminate\Support\Facades\DB::connection();
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1'], true)
    || $db->getConfig('database') !== 'gr2' || (int) $db->getConfig('port') !== 3306
    || $db->getConfig('url') || $db->getConfig('unix_socket') || $db->getConfig('read') || $db->getConfig('write')) {
    throw new RuntimeException('Unexpected configured target; no database query permitted.');
}
if ($db->getReadPdo() !== $db->getPdo()) { throw new RuntimeException('Split read connection.'); }
$physical = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port');
if ($physical['db'] !== 'gr2' || (int) $physical['port'] !== 3306 || strcasecmp($physical['server'], gethostname()) !== 0) {
    throw new RuntimeException('Unexpected physical local target.');
}
$db->beforeExecuting(static function (string $sql): void {
    if (!preg_match('/^\s*(?:select|show)\b/i', $sql)
        || preg_match('/\b(?:into\s+(?:outfile|dumpfile)|for\s+update|lock\s+in)\b/i', $sql)) {
        throw new RuntimeException('M42 evidence allows SELECT/SHOW only.');
    }
});
$files = [
    'm27-m11-linking-words.v2.json', 'm28-m12-emphasis-inversion.v2.json', 'm29-m13-sentence-structure.v2.json',
    'm30-m14-participle-clauses.v1.json', 'm31-m15-conditionals.v1.json', 'm32-m16-formal-english.v1.json',
    'm33-m17-academic-english.v1.json', 'm34-m18-argumentation-cohesion.v1.json', 'm35-m19-passive-reporting.v1.json',
    'm36-m20-modals-subjunctive.v1.json', 'm37-m21-grammar-structures.v1.json', 'm38-m22-articles-collocations.v1.json',
    'm39-practice-ui.v1.json', 'm40-m24-tenses-b1.v1.json',
];
$configs = []; $packageHashes = [];
$registry = json_decode(file_get_contents($source.'/database/content-patches/m42-native-design-registry.v1.json'), true, flags: JSON_THROW_ON_ERROR);
if (count($registry['targets'] ?? []) !== 42 || count(array_unique(array_column($registry['targets'], 'identity'))) !== 42) {
    throw new RuntimeException('The independent finite registry must contain exactly42 accepted owners.');
}
foreach ($files as $i => $file) {
    $path = $source.'/database/content-patches/'.$file;
    $package = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    if (count($package['targets'] ?? []) !== 3) { throw new RuntimeException('Each exact M11–M24 package must have three targets.'); }
    $packageHashes[$file] = hash_file('sha256', $path);
    foreach ($package['targets'] as $target) {
        if (isset($configs[$target['identity']])) { throw new RuntimeException('Duplicate finite target identity.'); }
        $expectedRegistry = array_values(array_filter($registry['targets'], static fn ($entry) => $entry['identity'] === $target['identity']));
        if (count($expectedRegistry) !== 1) { throw new RuntimeException('Exact registry/source identity required.'); }
        $binding = $expectedRegistry[0];
        $expectedHash = $binding['overlay_source'] === 'database/content-patches/'.$file ? $binding['overlay_sha256'] : $binding['source_sha256'];
        if ($packageHashes[$file] !== $expectedHash) { throw new RuntimeException('An accepted source package differs from the independent registry binding.'); }
        $configs[$target['identity']] = ['original_stage' => 'M'.($i + 11), 'package' => $file, 'config' => $target];
    }
}
if (count($configs) !== 42) { throw new RuntimeException('Exactly 42 unique M42 owners required.'); }
if (array_keys($configs) !== array_column($registry['targets'], 'identity')) { throw new RuntimeException('Finite target source order differs from the accepted registry.'); }
$m41 = json_decode(file_get_contents($source.'/database/content-patches/m41-authored-tense-comparisons.v1.0.1.json'), true, flags: JSON_THROW_ON_ERROR);
foreach ($m41['targets'] as $target) {
    if (isset($configs[$target['identity']])) { throw new RuntimeException('M41 references cannot be counted as M42 targets.'); }
    $configs[$target['identity']] = ['original_stage' => 'M41', 'package' => 'm41-authored-tense-comparisons.v1.0.1.json', 'config' => $target];
}
$targets = []; $references = []; $targetBlockIds = [];
foreach ($configs as $identity => $entry) {
    $expected = $entry['config']['after'];
    $page = $db->table('pages')->where('seeder', $identity)->sole();
    $category = $db->table('page_categories')->where('id', $page->page_category_id)->sole();
    $ancestry = []; $seen = []; $node = $category;
    while ($node !== null) {
        if (isset($seen[$node->id]) || count($seen) >= 8 || $node->language !== 'uk' || $node->type !== 'theory') {
            throw new RuntimeException('Invalid exact category ancestry.');
        }
        $seen[$node->id] = true; array_unshift($ancestry, $node->slug);
        $node = $node->parent_id === null ? null : $db->table('page_categories')->where('id', $node->parent_id)->sole();
    }
    $expectedAncestry = $entry['config']['ancestry'] ?? [$expected['page']['category']['slug']];
    if ($page->slug !== $expected['slug'] || $page->type !== 'theory' || $page->title !== $expected['page']['title']
        || $page->text !== $expected['page']['subtitle_text'] || $ancestry !== $expectedAncestry) {
        throw new RuntimeException('Accepted source/owner identity mismatch: '.$identity);
    }
    if ($entry['original_stage'] !== 'M41') {
        $binding = array_values(array_filter($registry['targets'], static fn ($record) => $record['identity'] === $identity))[0];
        if ($binding['local_url'] !== 'http://gramlyze.loc/theory/'.implode('/', [...$ancestry, $page->slug])) {
            throw new RuntimeException('Actual root-to-leaf canonical local URL differs from the accepted registry.');
        }
    }
    $rows = $db->table('text_blocks')->where('page_id', $page->id)->orderBy('id')->get();
    $uk = $rows->where('locale', 'uk')->sortBy('sort_order')->values();
    if ($uk->count() !== count($expected['page']['blocks']) + 1 || $uk[0]->body !== $expected['page']['subtitle_html']) {
        throw new RuntimeException('Accepted source/actual subtitle or row count mismatch: '.$identity);
    }
    foreach ($expected['page']['blocks'] as $index => $config) {
        $uuid = App\Support\M26DetailPackage::uuid($identity, $config, $index + 1);
        $row = $uk->firstWhere('uuid', $uuid);
        if (!$row || $row->body !== $config['body'] || $row->type !== $config['type'] || $row->column !== $config['column']
            || $row->heading !== ($config['heading'] ?? null) || $row->level !== ($config['level'] ?? null)
            || $row->css_class !== ($config['css_class'] ?? null) || (int) $row->sort_order !== $index + 1 || $row->seeder !== $identity) {
            throw new RuntimeException('Accepted source/actual complete block mismatch: '.$identity.' position '.($index + 1));
        }
    }
    $questions = $db->table('questions')->join('question_theory_text_blocks', 'questions.uuid', '=', 'question_theory_text_blocks.question_uuid')
        ->whereIn('text_block_uuid', $rows->pluck('uuid'))->distinct()->orderBy('questions.id')
        ->get(['questions.id', 'questions.uuid', 'questions.seeder', 'questions.type', 'questions.level']);
    $record = ['original_stage' => $entry['original_stage'], 'package' => $entry['package'], 'identity' => $identity,
        'definition' => $entry['config']['path'] ?? $entry['config']['definition_path'] ?? null,
        'url' => 'http://gramlyze.loc/theory/'.implode('/', [...$ancestry, $page->slug]),
        'page' => (array) $page, 'category' => (array) $category,
        'locales' => $rows->pluck('locale')->unique()->sort()->values()->all(),
        'blocks' => $rows->map(static fn ($row) => (array) $row)->all(),
        'uk_order' => $uk->map(static fn ($row) => ['id' => $row->id, 'uuid' => $row->uuid, 'type' => $row->type, 'order' => (int) $row->sort_order])->all(),
        'linked_bank_ids' => $questions->groupBy('seeder')->map(static fn ($group) => $group->pluck('id')->all())->all(),
        'linked_bank_groups' => $questions->groupBy(static fn ($q) => $q->seeder.'|'.$q->type.'|'.$q->level)->map(static fn ($group) => $group->count())->all(),
        'exact_accepted_source' => true,
    ];
    if ($entry['original_stage'] === 'M41') { $references[] = $record; }
    else { $targets[] = $record; $targetBlockIds = [...$targetBlockIds, ...$rows->pluck('id')->all()]; }
}
$tables = $db->getSchemaBuilder()->getTableListing(schema: $physical['db'], schemaQualified: false);
sort($tables); $fingerprints = [];
foreach ($tables as $table) {
    $columns = $db->getSchemaBuilder()->getColumnListing($table);
    $query = $db->table($table);
    foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
    $hash = hash_init('sha256'); $count = 0;
    foreach ($query->cursor() as $row) { hash_update($hash, App\Services\PronounContentRepair::digest((array) $row)."\n"); $count++; }
    $fingerprints[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
}
$hash = hash_init('sha256'); $count = 0;
foreach ($db->table('text_blocks')->whereNotIn('id', $targetBlockIds)->orderBy('id')->cursor() as $row) {
    hash_update($hash, App\Services\PronounContentRepair::digest((array) $row)."\n"); $count++;
}
$record = ['at' => gmdate('c'), 'target' => ['root' => $root, 'document_root' => $root.'/public', 'driver' => 'mysql',
    'host' => $db->getConfig('host'), 'port' => (int) $physical['port'], 'database' => $physical['db'],
    'environment' => app()->environment(), 'site_mode' => app(App\Support\SiteMode::class)->forHost('gramlyze.loc')],
    'package_hashes' => $packageHashes, 'targets' => $targets, 'm41_references' => $references, 'fingerprints' => $fingerprints,
    'non_target_text_blocks' => ['count' => $count, 'sha256' => hash_final($hash)], 'read_only_select_guard' => true];
$dir = $root.'/storage/app/seo-m42-local';
if (is_link($dir)) { throw new RuntimeException('Evidence directory cannot be a link.'); }
if (!is_dir($dir) && !mkdir($dir, 0700, true)) { throw new RuntimeException('Cannot create private evidence directory.'); }
$bytes = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
$file = fopen($dir.'/'.$name, 'xb');
if (!$file) { throw new RuntimeException('Exclusive M42 evidence already exists.'); }
try { if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('Incomplete private M42 evidence.'); } }
finally { fclose($file); }
echo json_encode(['pass' => true, 'at' => $record['at'], 'file' => $dir.'/'.$name, 'sha256' => hash('sha256', $bytes),
    'targets' => count($targets), 'm41_references' => count($references), 'tables' => count($fingerprints), 'target' => $record['target']], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
