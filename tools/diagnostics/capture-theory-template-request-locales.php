<?php

// Supplemental request-locale inventory. SELECT-only: no HTTP dispatch, middleware or render.
declare(strict_types=1);

if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { throw new RuntimeException('Local Windows CLI required.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
$directory = $root.'/storage/app/theory-template-local';
$name = $argv[1] ?? '';
if (!preg_match('/^request-locales-v[1-9][0-9]*\.json$/D', $name) || !is_dir($directory) || is_link($directory)
    || file_exists($directory.'/'.$name)) { throw new RuntimeException('Exclusive private evidence required.'); }
$beforeFile = $directory.'/registry-before-v2.json';
$sourceFile = $directory.'/source-before-v1.json';
$before = json_decode(file_get_contents($beforeFile), true, flags: JSON_THROW_ON_ERROR);
$source = json_decode(file_get_contents($sourceFile), true, flags: JSON_THROW_ON_ERROR);
$sourceHashes = array_column($source['sources']['root'], 'sha256', 'path');
$routingSources = [
    'app/Http/Controllers/PageController.php', 'app/Http/Controllers/TheoryController.php',
    'app/Modules/LanguageManager/Services/LocaleService.php', 'app/Providers/RouteServiceProvider.php',
    'app/Support/SiteMode.php', 'routes/web.php',
];
$routingHashes = [];
foreach ($routingSources as $path) {
    $hash = hash_file('sha256', $root.'/'.$path);
    if (!isset($sourceHashes[$path]) || $sourceHashes[$path] !== $hash) { throw new RuntimeException('Routing source differs from independent BEFORE: '.$path); }
    $routingHashes[$path] = $hash;
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::create('http://gramlyze.loc/', 'GET', server: ['REMOTE_ADDR' => '127.0.0.1']));
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root) || app(App\Support\SiteMode::class)->current() !== 'development') { throw new RuntimeException('Unexpected local application target.'); }
$db = Illuminate\Support\Facades\DB::connection();
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)
    || $db->getConfig('database') !== 'gr2' || (int) $db->getConfig('port') !== 3306
    || $db->getConfig('url') || $db->getConfig('unix_socket') || $db->getConfig('read') || $db->getConfig('write')) {
    throw new RuntimeException('Unexpected local database configuration.');
}
if ($db->getReadPdo() !== $db->getPdo()) { throw new RuntimeException('Split connection refused.'); }
$physical = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
$pidFile = realpath((string) $physical['pid_file']); $dataDir = realpath((string) $physical['datadir']);
if ($physical['db'] !== 'gr2' || (int) $physical['port'] !== 3306 || strcasecmp($physical['server'], gethostname()) !== 0
    || !str_contains(strtolower((string) $physical['os']), 'win') || !$pidFile || !$dataDir
    || !str_starts_with(strtolower(str_replace('\\', '/', $pidFile)), rtrim(strtolower(str_replace('\\', '/', $dataDir)), '/').'/')) {
    throw new RuntimeException('Physical local database mismatch.');
}
$json = static fn ($value): string => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
$controller = app(App\Http\Controllers\TheoryController::class);
$filter = new ReflectionMethod($controller, 'filterBlocksByChosenLocale');
$registry = [];
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $locales = App\Modules\LanguageManager\Services\LocaleService::getSupportedLocaleCodes();
    $fallback = App\Modules\LanguageManager\Services\LocaleService::getDefaultLocaleCode();
    $pages = App\Models\Page::query()->with('textBlocks')->forType('theory')->get()->keyBy('id');
    if (count($pages) !== count($before['registry'])) { throw new RuntimeException('Page count changed since BEFORE.'); }
    foreach ($before['registry'] as $old) {
        $page = $pages->get($old['page_id']);
        if (!$page || hash('sha256', $json($page->getAttributes())) !== $old['page_row_sha256']) { throw new RuntimeException('Page identity/metadata changed.'); }
        $oldBlocks = [];
        foreach ($old['variants'] as $variant) { foreach ($variant['blocks'] as $block) { $oldBlocks[$block['block_id']] = $block; } }
        if ($page->textBlocks->count() !== count($oldBlocks)) { throw new RuntimeException('Block count changed.'); }
        foreach ($page->textBlocks as $block) {
            $reference = $oldBlocks[$block->id] ?? null;
            if (!$reference || $reference['body_sha256'] !== hash('sha256', (string) $block->body)
                || $reference['locale'] !== $block->locale || $reference['uuid'] !== $block->uuid
                || $reference['owner'] !== $block->seeder || $reference['type'] !== $block->type
                || $reference['sort_order'] !== $block->sort_order || $reference['heading'] !== $block->heading) {
                throw new RuntimeException('Content/identity changed since independent BEFORE.');
            }
        }
        foreach ($locales as $requested) {
            app()->setLocale($requested);
            [$selected, $chosen] = $filter->invoke($controller, $page->textBlocks->whereIn('locale', array_unique([$requested, $fallback])), $requested, $fallback);
            $url = App\Modules\LanguageManager\Services\LocaleService::localizedRoute('theory.show', ['categoryPath' => $old['category_path'], 'pageSlug' => $old['slug']], false, $requested);
            $route = app('router')->getRoutes()->match(Illuminate\Http\Request::create('http://gramlyze.loc'.$url, 'GET'));
            if ($route->getActionName() !== App\Http\Controllers\TheoryController::class.'@showByCategoryPath'
                || $route->parameter('pageSlug') !== $old['slug'] || $route->parameter('categoryPath') !== $old['category_path'] || $selected->isEmpty()) {
                throw new RuntimeException('Request route does not resolve the expected theory owner/content.');
            }
            $registry[] = ['page_id' => $page->id, 'owner' => $page->seeder, 'slug' => $page->slug,
                'requested_locale' => $requested, 'content_locale' => $chosen, 'uses_content_fallback' => $requested !== $chosen,
                'url' => $url, 'absolute_url' => 'http://gramlyze.loc'.$url, 'block_count' => $selected->count(),
                'route_name' => $route->getName(), 'route_action' => $route->getActionName(), 'route_owner_exact' => true,
                'baseline' => $requested === $chosen ? 'Fresh stored-content variant HTTP BEFORE exists separately.'
                    : 'Derived from immutable BEFORE content and unchanged routing/locale-selection code; no fresh live HTTP BEFORE for this requested locale.'];
        }
    }
    $db->rollBack();
} catch (Throwable $error) { if ($db->transactionLevel() > 0) { $db->rollBack(); } throw $error; }
$fallbacks = array_values(array_filter($registry, static fn ($item) => $item['uses_content_fallback']));
$payload = ['schema' => 'theory-template-request-locales-v1', 'captured_at' => gmdate('c'), 'origin' => 'http://gramlyze.loc',
    'read_only' => true, 'db_writes' => 0, 'http_dispatches' => 0, 'supported_locales' => $locales, 'default_content_locale' => $fallback,
    'immutable_content_before_sha256' => hash_file('sha256', $beforeFile), 'unchanged_routing_source_sha256' => $routingHashes,
    'summary' => ['theory_owners' => count($pages), 'stored_content_variants' => count($registry) - count($fallbacks),
        'supported_request_routes' => count($registry), 'fallback_request_routes' => count($fallbacks)],
    'registry' => $registry, 'fallback_registry' => $fallbacks];
$bytes = $json($payload)."\n"; $handle = fopen($directory.'/'.$name, 'xb');
try { if (!$handle || fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Incomplete exclusive evidence.'); } }
finally { if ($handle) { fclose($handle); } }
if (hash_file('sha256', $directory.'/'.$name) !== hash('sha256', $bytes)) { throw new RuntimeException('Evidence checksum mismatch.'); }
echo $json(['file' => $directory.'/'.$name, 'sha256' => hash('sha256', $bytes), 'summary' => $payload['summary']])."\n";
