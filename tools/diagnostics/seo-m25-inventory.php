<?php

// Read-only M25 learning inventory. It cannot apply content, accept an arbitrary
// database/host, expose configuration, or run from a production host.
use Illuminate\Contracts\Console\Kernel;

if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !preg_match('/^[a-z0-9-]+$/D', $argv[1] ?? '')) {
    fwrite(STDERR, "Use the working Windows CLI and a diagnostic label.\n");
    exit(1);
}
$working = 'D:/DEV/htdocs/gramlyze.loc';
require $working.'/vendor/autoload.php';
$app = require $working.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $error): void {
    // Database exception messages can contain connection identifiers. Keep the
    // diagnostic failure attributable without printing configuration or secrets.
    fwrite(STDERR, 'M25 inventory failed: '.get_class($error).' [code '.$error->getCode().'; message SHA-256 '.hash('sha256', $error->getMessage())."]\n");
    exit(1);
});
$db = $app['db']->connection();
if (strtolower(str_replace('\\', '/', realpath($app->basePath()))) !== strtolower($working)
    || $db->getDriverName() !== 'mysql'
    || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)
    || $db->getConfig('url') || $db->getConfig('read') || $db->getConfig('write')) {
    throw new RuntimeException('M25 inventory requires the fixed working local application and one loopback MySQL connection.');
}
$pdo = $db->getPdo();
$read = static function (string $sql) use ($pdo): array {
    if (!str_starts_with($sql, 'SELECT ')) {
        throw new RuntimeException('Only SELECT is allowed.');
    }
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
};
$pages = $read("SELECT * FROM pages WHERE type = 'theory' ORDER BY id");
$categories = $read("SELECT * FROM page_categories WHERE type = 'theory' ORDER BY id");
$blocks = $read("SELECT text_blocks.* FROM text_blocks WHERE page_id IN (SELECT id FROM pages WHERE type = 'theory') OR (page_id IS NULL AND page_category_id IN (SELECT id FROM page_categories WHERE type = 'theory')) ORDER BY id");
$relations = [];
$tableNames = array_column($read('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'), 'TABLE_NAME');
foreach (['page_tag', 'page_category_tag', 'text_block_tag', 'page_prompt', 'page_question', 'site_tree_items', 'site_tree_variants'] as $table) {
    if (in_array($table, $tableNames, true)) {
        $rows = $read('SELECT * FROM `'.$table.'`');
        usort($rows, static fn (array $a, array $b): int => strcmp(json_encode($a), json_encode($b)));
        $relations[$table] = $rows;
    }
}
$categoryById = array_column($categories, null, 'id');
$path = static function ($id) use ($categoryById): string {
    $segments = []; $seen = [];
    while ($id && isset($categoryById[$id]) && !isset($seen[$id])) {
        $seen[$id] = true; $row = $categoryById[$id];
        array_unshift($segments, $row['slug']); $id = $row['parent_id'];
    }
    return implode('/', $segments);
};
$pagePaths = [];
foreach ($pages as $page) {
    $pagePaths[$page['id']] = '/theory/'.$path($page['page_category_id']).'/'.$page['slug'];
}
$blockPaths = []; $matrix = []; $locales = [];
foreach ($blocks as $block) {
    $kind = $block['page_id'] ? 'lesson' : 'category';
    $url = $block['page_id'] ? $pagePaths[$block['page_id']] : '/theory/'.$categoryById[$block['page_category_id']]['slug'];
    $type = $block['type'] ?: '[null]';
    $key = $kind.':'.$type;
    $matrix[$key] ??= ['kind' => $kind, 'type' => $type, 'blocks' => 0, 'paths' => [], 'locales' => []];
    $matrix[$key]['blocks']++;
    $matrix[$key]['paths'][$url] = true;
    $matrix[$key]['locales'][$block['locale']] = true;
    $locales[$block['locale']] = true;
    $blockPaths[$block['id']] = $url;
}
ksort($matrix);
foreach ($matrix as &$row) {
    $row['paths'] = array_keys($row['paths']);
    $row['pages'] = count($row['paths']);
    $row['locales'] = array_keys($row['locales']);
}
unset($row);
$canonical = ['pages' => $pages, 'categories' => $categories, 'blocks' => $blocks, 'relations' => $relations];
$json = static fn ($value): string => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
$hashes = array_map(static fn ($rows): string => hash('sha256', $json($rows)), $canonical);
$result = ['at' => gmdate('c'), 'scope' => 'SELECT-only working local learning data; no user or session data',
    'fingerprints' => $hashes, 'combined_sha256' => hash('sha256', $json($canonical)),
    'counts' => ['lessons' => count($pages), 'categories' => count($categories), 'blocks' => count($blocks)],
    'locales' => array_keys($locales), 'page_paths' => $pagePaths, 'matrix' => array_values($matrix),
    'data' => $canonical];
$directory = $working.'/storage/app/seo-m25-local';
if (!is_dir($directory) && !mkdir($directory, 0700, true)) {
    throw new RuntimeException('Could not create private diagnostic directory.');
}
$destination = $directory.'/'.$argv[1].'-inventory.json';
$handle = fopen($destination, 'x');
if ($handle === false) {
    throw new RuntimeException('Refusing to overwrite inventory evidence.');
}
fwrite($handle, $json($result)); fclose($handle);
echo $json(['file' => $destination, 'counts' => $result['counts'], 'locales' => $result['locales'],
    'combined_sha256' => $result['combined_sha256'], 'matrix' => array_map(static fn ($row): array => [
        'kind' => $row['kind'], 'type' => $row['type'], 'blocks' => $row['blocks'], 'pages' => $row['pages'],
        'example' => $row['paths'][0]], $result['matrix'])]).PHP_EOL;
