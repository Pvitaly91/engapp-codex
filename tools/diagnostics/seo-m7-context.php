<?php
/** Local, opt-in read-only M7 context. Never boots Laravel or reads questions. */
declare(strict_types=1);

if (($argv[1] ?? '') !== '--local-read-only') { exit(2); }
$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$inventory = json_decode(file_get_contents($root.'/storage/app/seo-m5-local/m5-accepted.json'), true, flags: JSON_THROW_ON_ERROR);
$candidates = array_values(array_filter($inventory['rows'], static fn ($row) => str_starts_with($row['path'], '/theory/') && str_starts_with($row['metadata']['description'][0] ?? '', 'Пояснення теми «')));
$env = Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
if (($env['DB_CONNECTION'] ?? '') !== 'mysql' || !in_array($env['DB_HOST'] ?? '', ['localhost', '127.0.0.1'], true)) { exit(2); }
$pdo = new PDO('mysql:host='.$env['DB_HOST'].';port='.($env['DB_PORT'] ?? '3306').';dbname='.$env['DB_DATABASE'].';charset=utf8mb4', $env['DB_USERNAME'], $env['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('SET SESSION TRANSACTION READ ONLY');
$pdo->beginTransaction();
$categories = $pdo->query('SELECT id,slug,parent_id,title FROM page_categories')->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
$pages = $pdo->query("SELECT id,slug,title,seeder,page_category_id FROM pages WHERE type='theory'")->fetchAll(PDO::FETCH_ASSOC);
$byPath = [];
$seeders = array_count_values(array_filter(array_column($pages, 'seeder')));
foreach ($pages as $page) {
    $segments = [$page['slug']];
    $id = $page['page_category_id'];
    $seen = [];
    while ($id && isset($categories[$id])) {
        if (isset($seen[$id])) { throw new RuntimeException('Category cycle'); }
        $seen[$id] = true;
        array_unshift($segments, $categories[$id]['slug']);
        $id = $categories[$id]['parent_id'];
    }
    $byPath['/theory/'.implode('/', $segments)][] = $page;
}
$statement = $pdo->prepare("SELECT id,type,heading,body FROM text_blocks WHERE page_id=? AND locale='uk' ORDER BY sort_order,id");
$result = [];
$start = (int) ($argv[2] ?? 0);
$end = (int) ($argv[3] ?? 0);
$rendered = ['hero', 'hero-v2', 'forms-grid', 'lesson-rule-cards', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list', 'tense-forms-table', 'box', ''];
foreach ($candidates as $index => $candidate) {
    $matches = $byPath[$candidate['path']] ?? [];
    if (count($matches) !== 1) { throw new RuntimeException('Ambiguous/missing candidate: '.$candidate['path']); }
    $page = $matches[0];
    $statement->execute([$page['id']]);
    $blocks = $statement->fetchAll(PDO::FETCH_ASSOC);
    $hero = array_values(array_filter($blocks, static fn ($b) => $b['type'] === 'hero-v2'))[0] ?? array_values(array_filter($blocks, static fn ($b) => $b['type'] === 'hero'))[0] ?? null;
    $intro = json_decode($hero['body'] ?? '', true)['intro'] ?? '';
    $plain = App\Support\PageMetadata::plain($intro);
    $fallback = $plain === '' || !preg_match('/[а-яіїєґ]/ui', $plain);
    $definition = 'database/seeders/'.str_replace('\\', '/', preg_replace('/^Database\\\\Seeders\\\\/', '', $page['seeder'] ?? '')).'/definition.json';
    $canonical = is_file($root.'/'.$definition) ? json_decode(file_get_contents($root.'/'.$definition), true, flags: JSON_THROW_ON_ERROR) : null;
    $canonicalBlocks = $canonical['page']['blocks'] ?? [];
    $item = ['path' => $candidate['path'], 'identity' => $page['seeder'], 'identity_count' => $seeders[$page['seeder']] ?? 0,
        'title_source' => $page['title'], 'category' => $categories[$page['page_category_id']]['title'],
        'intro' => $intro, 'fallback' => $fallback, 'reason' => $plain === '' ? 'missing-usable-hero-intro' : ($fallback ? 'hero-intro-without-Ukrainian' : 'usable-Ukrainian-intro'),
        'definition' => $canonical ? $definition : null, 'blocks' => [], 'historical_metadata' => $candidate['metadata']];
    if ($start && $index + 1 >= $start && $index + 1 <= $end) { echo "\nPAGE ".($index + 1).' '.$candidate['path']."\nIDENTITY ".$page['seeder']."\n"; }
    foreach ($blocks as $block) {
        if (!in_array($block['type'], $rendered, true)) { continue; }
        $data = json_decode($block['body'] ?? '', true);
        $equal = false;
        foreach ($canonicalBlocks as $reference) {
            if (($reference['type'] ?? '') !== $block['type']) { continue; }
            $referenceBody = $reference['body'] ?? '';
            if ((is_array($data) && $data == json_decode($referenceBody, true)) || $block['body'] === $referenceBody) { $equal = true; break; }
        }
        $item['blocks'][] = ['id' => $block['id'], 'type' => $block['type'], 'heading' => $block['heading'],
            'title' => is_array($data) ? ($data['title'] ?? '') : '', 'body_sha256' => hash('sha256', $block['body'] ?? ''), 'matches_definition' => $equal];
        if ($start && $index + 1 >= $start && $index + 1 <= $end) {
            echo 'BLOCK '.$block['type'].' '.$block['heading'].' canonical='.($equal ? 'same' : 'DIFFERENT')."\n";
            // Only public explanatory blocks, never practice answers, verb_hint, sessions or raw page HTML.
            echo json_encode($data ?? $block['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        }
    }
    $result[] = $item;
}
$pdo->rollBack();
if (!$start) {
    $dir = $root.'/storage/app/seo-m7-local';
    if (!is_dir($dir)) { mkdir($dir, 0700, true); }
    $file = fopen($dir.'/context.json', 'x');
    if (!$file) { throw new RuntimeException('Context already exists; do not overwrite evidence'); }
    fwrite($file, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    fclose($file);
    echo json_encode(['candidates' => count($result), 'fallbacks' => count(array_filter($result, static fn ($r) => $r['fallback'])), 'unique_seeders' => count(array_filter($result, static fn ($r) => $r['identity_count'] === 1)), 'definitions' => count(array_filter($result, static fn ($r) => $r['definition']))]).PHP_EOL;
}
