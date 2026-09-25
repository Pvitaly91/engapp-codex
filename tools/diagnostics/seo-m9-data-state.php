<?php
/** Read-only M9 state classification. No Laravel kernel, plan, apply, restore or seeder. */
declare(strict_types=1);
if (($argv[1] ?? '') !== '--local-read-only' || !preg_match('/^[a-z0-9-]+$/', $argv[2] ?? '')) { exit(2); }
$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
try {
    $env = Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
    if (($env['DB_CONNECTION'] ?? '') !== 'mysql' || !in_array($env['DB_HOST'] ?? '', ['localhost', '127.0.0.1'], true)) {
        throw new RuntimeException('Only configured local MySQL is allowed.');
    }
    $pdo = new PDO('mysql:host='.$env['DB_HOST'].';port='.($env['DB_PORT'] ?? '3306').';dbname='.$env['DB_DATABASE'].';charset=utf8mb4',
        $env['DB_USERNAME'], $env['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $identity = $pdo->query('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port, VERSION() AS version')->fetch(PDO::FETCH_ASSOC);
    if ($identity['db'] !== $env['DB_DATABASE'] || strcasecmp($identity['server'], getenv('COMPUTERNAME') ?: '') !== 0) {
        throw new RuntimeException('Actual local machine/database identity differs.');
    }
    $pdo->exec('SET SESSION TRANSACTION READ ONLY'); $pdo->beginTransaction();
    $report = ['at' => gmdate('c'), 'connection' => $identity, 'pronouns' => [], 'pass' => true];
    $pages = $pdo->prepare('SELECT id,slug,title FROM pages WHERE seeder = ?');
    $blocks = $pdo->prepare('SELECT uuid,sort_order,type,heading,body FROM text_blocks WHERE page_id = ? AND locale = ? ORDER BY sort_order,id');
    foreach (['OneOnes', 'ReciprocalPronouns'] as $name) {
        $seeder = App\Services\PronounContentRepair::PREFIX.$name.'TheorySeeder';
        $source = json_decode(file_get_contents($root.'/database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstratives'.$name.'TheorySeeder/definition.json'), true, flags: JSON_THROW_ON_ERROR);
        $pages->execute([$seeder]); $found = $pages->fetchAll(PDO::FETCH_ASSOC);
        if (count($found) !== 1) { throw new RuntimeException('Missing/ambiguous pronoun Page.'); }
        $page = $found[0]; $blocks->execute([$page['id'], 'uk']); $rows = $blocks->fetchAll(PDO::FETCH_ASSOC);
        $checks = [];
        foreach ($source['page']['blocks'] as $i => $block) {
            if (!isset($block['layout'])) { continue; }
            $uuid = App\Support\TextBlock\TextBlockUuidGenerator::generate($seeder.'::uk', $i + 1);
            $matches = array_values(array_filter($rows, fn ($r) => $r['uuid'] === $uuid && (int) $r['sort_order'] === $i + 1));
            $checks[] = count($matches) === 1 && $matches[0]['body'] === $block['body'] && $matches[0]['heading'] === ($block['heading'] ?? null) && $matches[0]['type'] === $block['type'];
        }
        $ok = !in_array(false, $checks, true) && $checks && $page['slug'] === $source['slug'] && $page['title'] === $source['page']['title'];
        $report['pronouns'][] = ['slug' => $page['slug'], 'checked_blocks' => count($checks), 'exact_current_source' => (bool) $ok];
        $report['pass'] &= $ok;
    }
    $source = json_decode(file_get_contents($root.'/database/'.App\Services\PassiveVoiceDebugContentRepair::DEFINITION), true, flags: JSON_THROW_ON_ERROR);
    $pages->execute([$source['seeder']['class']]); $found = $pages->fetchAll(PDO::FETCH_ASSOC);
    if (count($found) !== 1) { throw new RuntimeException('Missing/ambiguous Passive Voice Page.'); }
    $page = $found[0];
    $query = $pdo->prepare('SELECT uuid,body FROM text_blocks WHERE page_id = ?'); $query->execute([$page['id']]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    $bad = array_filter($rows, fn ($r) => str_contains($r['body'] ?? '', 'Page Folder Unseed Targets Debug') || str_contains($r['body'] ?? '', 'Page_V3 folder unseed block.'));
    $report['passive_voice'] = ['slug' => $page['slug'], 'blocks' => count($rows), 'debug_blocks' => count($bad), 'canonical_title' => $page['title'] === $source['page']['title']];
    $report['pass'] = (bool) ($report['pass'] && !$bad && count($rows) > 3 && $report['passive_voice']['canonical_title']);
    $pdo->rollBack();
    $file = $root.'/storage/app/seo-m9-local/'.$argv[2].'-data.json';
    $handle = fopen($file, 'x'); if (!$handle) { throw new RuntimeException('Evidence already exists.'); }
    fwrite($handle, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)); fclose($handle);
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    exit($report['pass'] ? 0 : 1);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    fwrite(STDERR, 'Read-only evidence failed: '.get_class($error).PHP_EOL); exit(1);
}
