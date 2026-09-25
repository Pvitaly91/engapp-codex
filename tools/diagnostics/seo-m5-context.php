<?php
// Opt-in read-only editorial context: public titles, hero intro and block headings only.
declare(strict_types=1);

if (($argv[1] ?? '') !== '--local-read-only') { exit(2); }
$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$env = Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
if (($env['DB_CONNECTION'] ?? '') !== 'mysql' || !in_array($env['DB_HOST'] ?? '', ['localhost', '127.0.0.1'], true)) { exit(2); }
$pdo = new PDO('mysql:host='.$env['DB_HOST'].';port='.($env['DB_PORT'] ?? '3306').';dbname='.$env['DB_DATABASE'].';charset=utf8mb4', $env['DB_USERNAME'], $env['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('SET SESSION TRANSACTION READ ONLY');
$pdo->beginTransaction();
$pages = $pdo->query("SELECT p.id,p.slug,p.title,c.title category FROM pages p JOIN page_categories c ON c.id=p.page_category_id WHERE p.type='theory' ORDER BY p.id")->fetchAll(PDO::FETCH_ASSOC);
$blocks = $pdo->query("SELECT b.page_id,b.type,b.heading,b.body FROM text_blocks b JOIN pages p ON p.id=b.page_id WHERE p.type='theory' AND b.locale='uk' ORDER BY b.sort_order");
$byPage = [];
foreach ($blocks as $block) {
    $body = json_decode($block['body'] ?? '', true);
    $item = ['type' => $block['type'], 'heading' => $block['heading'], 'title' => is_array($body) ? ($body['title'] ?? '') : ''];
    if (in_array($block['type'], ['hero', 'hero-v2'], true)) { $item['intro'] = is_array($body) ? ($body['intro'] ?? '') : ''; }
    $byPage[$block['page_id']][] = $item;
}
foreach ($pages as &$page) { $page['blocks'] = $byPage[$page['id']] ?? []; }
unset($page);
$pdo->rollBack();
$dir = $root.'/storage/app/seo-m5-local';
if (!is_dir($dir)) { mkdir($dir, 0700, true); }
$file = fopen($dir.'/editorial-context.json', 'x');
fwrite($file, json_encode($pages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
fclose($file);
echo 'Public context pages: '.count($pages).PHP_EOL;
