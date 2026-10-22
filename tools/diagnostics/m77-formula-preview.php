<?php

declare(strict_types=1);

/**
 * Private draft-only native SSR. This is neither a route nor live acceptance.
 * php tools/diagnostics/m77-formula-preview.php author-projection-v3.json <raw-sha256> v2
 */

const M77_PREVIEW_ROOT = 'D:/DEV/htdocs/gramlyze.loc';
const M77_PREVIEW_DRAFT_SHA = '985e7c0d06cd5c9de1ea2bf1bfb48ba1f174a14c4990ec95f9fe7936eef86d25';
const M77_PREVIEW_BEFORE_SHA = '3ee7c26081727dd746dfe1e94a03dcc4273c48bcb40be5091b84d27bc52cab31';
if (PHP_SAPI !== 'cli' || ($argc ?? 0) !== 4
    || preg_match('/^author-projection-v[1-9][0-9]*\.json$/D', $argv[1] ?? '') !== 1
    || ($argv[1] ?? '') !== 'author-projection-v3.json'
    || ($argv[2] ?? '') !== M77_PREVIEW_DRAFT_SHA
    || preg_match('/^v[1-9][0-9]*$/D', $argv[3] ?? '') !== 1) {
    throw new RuntimeException('M77 formula preview needs a reviewed private projection/raw SHA and fresh output version.');
}
$expectedDraftSha = $argv[2]; $outputVersion = $argv[3];
$completed = false;
register_shutdown_function(static function () use (&$completed): void {
    // Laravel's console exception handler can otherwise leave a diagnostic run
    // with exit0 even though no artifact passed the final guards.
    if (!$completed) { exit(1); }
});

$root = realpath(M77_PREVIEW_ROOT);
$private = realpath(M77_PREVIEW_ROOT.'/storage/app/seo-m77-local');
if ($root === false || $private === false || str_replace('\\', '/', $root) !== M77_PREVIEW_ROOT
    || str_replace('\\', '/', $private) !== M77_PREVIEW_ROOT.'/storage/app/seo-m77-local') {
    throw new RuntimeException('M77 preview root/private directory differs.');
}
$draftPath = $private.'/'.$argv[1];
if (!is_file($draftPath) || is_link($draftPath)
    || !hash_equals($expectedDraftSha, hash_file('sha256', $draftPath))) {
    throw new RuntimeException('M77 draft projection bytes differ.');
}
$projection = json_decode(file_get_contents($draftPath), true, flags: JSON_THROW_ON_ERROR);
$beforePath = $private.'/before-v1.json';
if (!is_file($beforePath) || is_link($beforePath)
    || !hash_equals(M77_PREVIEW_BEFORE_SHA, hash_file('sha256', $beforePath))) {
    throw new RuntimeException('M77 independent visual BEFORE differs.');
}
$before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
if (count($before['visual_hashes'] ?? []) !== 326 || count($projection['master']['lessons'] ?? []) !== 6
    || count($projection['patch']['targets'] ?? []) !== 6 || count($projection['native_bindings']['bindings'] ?? []) !== 6
    || ($projection['db_connections'] ?? null) !== 0 || ($projection['db_writes'] ?? null) !== 0
    || ($projection['http_requests'] ?? null) !== 0) {
    throw new RuntimeException('M77 six-owner draft/independent visual scope differs.');
}
$outputs = ['light' => $private.'/formula-preview-light-'.$outputVersion.'.html', 'dark' => $private.'/formula-preview-dark-'.$outputVersion.'.html'];
$receiptPath = $private.'/formula-preview-receipt-'.$outputVersion.'.json';
foreach ([...array_values($outputs), $receiptPath] as $destination) {
    if (file_exists($destination) || is_link($destination)) {
        throw new RuntimeException('M77 preview refuses to replace an existing private artifact: '.basename($destination));
    }
}

$visualPaths = [
    'public/build/manifest.json', 'resources/css/catalog-public.css', 'resources/css/theory-unified-design.css',
    'app/Support/TheoryContentRenderer.php', 'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryComponents.php',
    'app/Support/TheoryInlineHtml.php', 'app/Support/TheoryHtmlAdapter.php',
    'app/Modules/LanguageManager/Services/LocaleService.php', 'app/Providers/RouteServiceProvider.php',
    'resources/views/layouts/catalog-public.blade.php', 'resources/views/theory/show.blade.php', 'resources/views/theory/content.blade.php',
    'resources/views/theory/components/node.blade.php', 'resources/views/theory/components/section.blade.php',
    'resources/views/theory/components/form.blade.php', 'resources/views/theory/components/form-heading.blade.php',
    'resources/views/theory/components/table.blade.php', 'resources/views/theory/components/example.blade.php',
    'resources/views/components/theory-native-header.blade.php',
    'resources/views/engram/theory/blocks-v3/m42-native-design-styles.blade.php',
];
$pins = [];
foreach ($before['visual_hashes'] as $path => $sha) {
    if (!is_string($path) || preg_match('~^(?:resources/(?:views|css|js)|public/js)/[A-Za-z0-9_./-]+$~D', $path) !== 1
        || str_contains($path, '..') || !is_file($root.'/'.$path) || is_link($root.'/'.$path)
        || !hash_equals($sha, hash_file('sha256', $root.'/'.$path))) {
        throw new RuntimeException('M77 accepted visual source inventory/bytes differ.');
    }
    $pins[$path] = $sha;
}
foreach ($visualPaths as $path) {
    if (!is_file($root.'/'.$path) || is_link($root.'/'.$path)) { throw new RuntimeException('Missing/linked native preview dependency.'); }
    $pins[$path] = hash_file('sha256', $root.'/'.$path);
}
$manifest = json_decode(file_get_contents($root.'/public/build/manifest.json'), true, flags: JSON_THROW_ON_ERROR);
$css = $manifest['resources/css/catalog-public.css']['file'] ?? '';
if (!is_string($css) || preg_match('~^assets/[A-Za-z0-9_.-]+\.css$~D', $css) !== 1
    || !is_file($root.'/public/build/'.$css) || is_link($root.'/public/build/'.$css)) {
    throw new RuntimeException('M77 preview requires the existing catalog CSS manifest entry.');
}
$assetPath = 'public/build/'.$css;
$pins[$assetPath] = hash_file('sha256', $root.'/'.$assetPath);

// These helpers establish SQLite :memory:, array cache/session, and a private
// environment path before the kernel can read configuration or boot providers.
require $root.'/tests/bootstrap.php';
$app = require $root.'/bootstrap/app.php';
Tests\Support\IsolatedTestEnvironment::configure($app);
// The preview deliberately has no schema. Supply that known, process-local
// locale fixture before route boot: otherwise LocaleService probes sqlite_master
// and catches the SQL guard exception. No provider/view or query guard is mocked.
$localeTableFixture = new ReflectionProperty(App\Modules\LanguageManager\Services\LocaleService::class, 'cachedHasLanguagesTable');
if (!$localeTableFixture->isStatic() || $localeTableFixture->getValue() !== null) {
    throw new RuntimeException('M77 preview requires a fresh process-local locale schema fixture.');
}
$localeTableFixture->setValue(null, false);
$queryCount = 0;
$blockedQueryAttempts = 0;
$blockedDiagnostics = [];
$previewStage = 'bootstrap';
$app->make('events')->listen(Illuminate\Database\Events\ConnectionEstablished::class, static function ($event) use (&$blockedQueryAttempts, &$blockedDiagnostics, &$previewStage): void {
    $event->connection->beforeExecuting(static function ($query) use (&$blockedQueryAttempts, &$blockedDiagnostics, &$previewStage): void {
        ++$blockedQueryAttempts;
        if (count($blockedDiagnostics) < 3) {
            $blockedDiagnostics[] = ['stage' => $previewStage, 'statement' => $query,
                'trace' => array_map(static fn (array $frame): array => array_intersect_key($frame, array_flip(['file', 'line', 'class', 'type', 'function'])),
                    debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 24))];
        }
        throw new RuntimeException('SQL is blocked before execution in the formula-only preview.');
    });
});
$app->make('events')->listen(Illuminate\Database\Events\QueryExecuted::class, static function () use (&$queryCount): void {
    ++$queryCount;
    throw new RuntimeException('A formula-only preview must not execute SQL.');
});
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app->setLocale('uk');
$assertIsolatedDatabase = static function () use ($app, &$queryCount): array {
    $connections = $app->make('db')->getConnections();
    $opened = 0;
    foreach ($connections as $name => $connection) {
        // Inspect the raw resolver; getPdo() would itself open the connection.
        $raw = $connection->getRawPdo();
        if ($name !== 'sqlite' || $connection->getConfig('driver') !== 'sqlite' || $connection->getDatabaseName() !== ':memory:'
            || !($raw === null || $raw instanceof Closure || ($raw instanceof PDO && $raw->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'))) {
            throw new RuntimeException('M77 formula preview connection state differs: '.json_encode([
                'name' => $name, 'driver' => $connection->getConfig('driver'),
                'memory' => $connection->getDatabaseName() === ':memory:', 'raw_pdo_type' => get_debug_type($raw),
                'queries' => $queryCount,
            ], JSON_THROW_ON_ERROR));
        }
        if ($raw instanceof PDO) { ++$opened; }
    }
    if ($queryCount !== 0) { throw new RuntimeException('M77 formula preview executed SQL.'); }
    return ['objects' => count($connections), 'isolated_memory_pdos' => $opened];
};
if (!$app->environment('testing') || config('database.default') !== 'sqlite'
    || config('database.connections.sqlite.database') !== ':memory:' || config('cache.default') !== 'array'
    || config('session.driver') !== 'array' || $app->environmentFile() !== 'isolated.env'
    || $app->environmentPath() !== Tests\Support\IsolatedTestEnvironment::prepare().'/environment') {
    throw new RuntimeException('M77 preview isolated guards differ: '.json_encode([
        'testing' => $app->environment('testing'), 'driver' => config('database.default'),
        'database_is_memory' => config('database.connections.sqlite.database') === ':memory:',
        'cache' => config('cache.default'), 'session' => config('session.driver'),
        'environment_file' => $app->environmentFile(), 'connection_names' => array_keys($app->make('db')->getConnections()),
    ], JSON_THROW_ON_ERROR));
}
$databaseState = $assertIsolatedDatabase();

$expected = [
    [0, 3, 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesOneOnesTheorySeeder', 'forms-grid', 'items', 123, 20, 'left'],
    [0, 5, 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesOneOnesTheorySeeder', 'comparison-table', 'rows', 123, 20, 'left'],
    [0, 9, 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesOneOnesTheorySeeder', 'forms-grid', 'items', 123, 20, 'footer'],
    [1, 1, 'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfTimeTheorySeeder', 'forms-grid', 'items', 120, 19, 'left'],
    [1, 2, 'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfTimeTheorySeeder', 'comparison-table', 'rows', 120, 19, 'left'],
    [2, 2, 'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfPlaceTheorySeeder', 'comparison-table', 'rows', 119, 19, 'left'],
    [3, 2, 'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfMovementTheorySeeder', 'comparison-table', 'rows', 118, 19, 'left'],
    [4, 2, 'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\DependentPrepositionsTheorySeeder', 'comparison-table', 'rows', 115, 19, 'left'],
    [5, 2, 'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PhrasalVerbsBasicsTheorySeeder', 'comparison-table', 'rows', 116, 19, 'left'],
];
$allFormulaBlocks = [];
foreach ($projection['master']['lessons'] as $i => $lesson) {
    foreach ($lesson['blocks'] as $slot => $block) {
        if (in_array($block['type'] ?? null, ['forms-grid', 'comparison-table'], true)) {
            $allFormulaBlocks[] = [$i, $slot, $lesson['identity'], $block['type'], $block['column']];
        }
    }
}
$expectedInventory = array_map(static fn ($record) => [$record[0], $record[1], $record[2], $record[3], $record[7]], $expected);
if ($allFormulaBlocks !== $expectedInventory || count(array_unique(array_column($expected, 0))) !== 6) {
    throw new RuntimeException('M77 preview must include all six owners and every authored forms-grid/table, including the One footer card.');
}
$rendered = ''; $renderedRecords = [];
foreach ($expected as $sequence => [$i, $slot, $owner, $type, $field, $pageId, $categoryId, $column]) {
    $previewStage = 'owner-'.$i.'-slot-'.$slot.'-'.$type;
    $lesson = $projection['master']['lessons'][$i] ?? null;
    $draft = $lesson['blocks'][$slot] ?? null;
    $count = is_array($draft['data'][$field] ?? null) ? count($draft['data'][$field]) : 0;
    if (!is_array($draft) || ($lesson['identity'] ?? null) !== $owner || ($lesson['locale'] ?? null) !== 'uk'
        || ($draft['existing_source_index'] ?? null) !== $slot || ($draft['type'] ?? null) !== $type
        || ($draft['column'] ?? null) !== $column || $count < 1) {
        throw new RuntimeException('M77 preview has a foreign owner or changed formula-card/table scope.');
    }
    $source = $projection['patch']['targets'][$i]['after']['page']['blocks'][$slot] ?? null;
    $sourceData = is_array($source)
        ? json_decode($source['body'] ?? '', true, flags: JSON_THROW_ON_ERROR)
        : null;
    if (!is_array($sourceData)
        || ($sourceData['m77_native_v1'] ?? null) !== ['key' => $draft['key'], 'detail_keys' => []]) {
        throw new RuntimeException('M77 preview exact native marker differs.');
    }
    unset($sourceData['m77_native_v1']);
    if (!is_array($source) || ($source['type'] ?? null) !== $type || ($source['column'] ?? null) !== $column
        || $sourceData !== $draft['data']) {
        throw new RuntimeException('M77 preview native source metadata differs.');
    }
    $block = new App\Models\TextBlock;
    $block->setRawAttributes([
        'id' => 77001 + $sequence, 'uuid' => App\Support\M26DetailPackage::uuid($owner, $source, $slot + 1),
        'page_id' => $pageId, 'page_category_id' => $categoryId, 'locale' => 'uk', 'seeder' => $owner,
        'type' => $type, 'column' => $column, 'level' => $source['level'] ?? null, 'heading' => null, 'css_class' => null,
        'sort_order' => $slot + 1, 'body' => App\Support\M26DetailPackage::json($draft['data']),
    ], true);
    $block->setRelation('tags', new Illuminate\Database\Eloquent\Collection);
    $block->setRelation('page', null);
    $block->setRelation('pageCategory', null);
    $block->setRelation('practiceQuestions', new Illuminate\Database\Eloquent\Collection);
    if ($block->exists) { throw new RuntimeException('Formula preview may use only unsaved models.'); }
    $html = App\Support\TheoryContentRenderer::render('engram.theory.blocks-v3.'.$type, [
        'theoryCanonical' => true, 'block' => $block, 'data' => $draft['data'],
        'pointSections' => [], 'lessonLinks' => [], 'embeddedDetail' => false, 'm42Design' => null,
        'practiceQuestions' => collect(),
    ]);
    $dom = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
    libxml_clear_errors(); libxml_use_internal_errors($previous);
    $xpath = new DOMXPath($dom);
    $actual = $type === 'forms-grid'
        ? $xpath->query('//*[@data-theory-component="form"]')->length
        : $xpath->query('//*[@data-theory-component="table"]//tbody/tr')->length;
    if ($actual !== $count || $xpath->query('//script')->length !== 0 || $xpath->query('//input|//textarea|//select')->length !== 0) {
        throw new RuntimeException('Native formula SSR count or noninteractive scope differs.');
    }
    if ($column === 'footer' && ($count !== 1
        || $xpath->query('//a[@data-theory-component="form"]')->length !== 1
        || $xpath->query('//a[@data-theory-component="form" and @href="/theory/zaimennyky-ta-vkazivni-slova"]')->length !== 1)) {
        throw new RuntimeException('One footer preview needs the one preserved actual category-back linkcard.');
    }
    $title = htmlspecialchars($lesson['preserved_h1'].($column === 'footer' ? ' — повернення до розділу' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $rendered .= '<section class="space-y-6"><h2 class="font-display text-xl font-extrabold">'.$title.'</h2>'.$html.'</section>';
    $renderedRecords[] = [
        'identity' => $owner, 'source_index' => $slot, 'key' => $draft['key'], 'type' => $type,
        'count' => $actual, 'data_sha256' => App\Support\M26DetailPackage::digest($draft['data']),
        'rendered_html_sha256' => hash('sha256', $html), 'unsaved_model' => true,
        'source_bound_design' => null, 'canonical_renderer' => true,
    ];
}

// Reuse the unchanged existing style view; do not reproduce or amend its CSS.
$previewStage = 'existing-styles';
$existingStyles = view('engram.theory.blocks-v3.m42-native-design-styles')->render();
$databaseState = $assertIsolatedDatabase();
if ($blockedQueryAttempts !== 0) { throw new RuntimeException('M77 preview attempted SQL, even if an adapter caught the block: '.json_encode($blockedDiagnostics, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)); }
if ($localeTableFixture->getValue() !== false) { throw new RuntimeException('M77 empty-schema locale fixture changed during native SSR.'); }
foreach ($pins as $path => $sha) {
    if (!hash_equals($sha, hash_file('sha256', $root.'/'.$path))) { throw new RuntimeException('A native source/asset changed during formula SSR.'); }
}
if (!hash_equals($expectedDraftSha, hash_file('sha256', $draftPath))) { throw new RuntimeException('Draft changed during formula SSR.'); }

$writeExclusive = static function (string $path, string $bytes): void {
    $handle = fopen($path, 'xb');
    if ($handle === false) { throw new RuntimeException('Exclusive preview artifact creation failed.'); }
    try {
        $offset = 0;
        while ($offset < strlen($bytes)) {
            $written = fwrite($handle, substr($bytes, $offset));
            if (!is_int($written) || $written < 1) { throw new RuntimeException('Preview artifact write was incomplete.'); }
            $offset += $written;
        }
        if (!fflush($handle)) { throw new RuntimeException('Preview artifact flush failed.'); }
    } finally { fclose($handle); }
};
$artifacts = [];
foreach ($outputs as $theme => $path) {
    $dark = $theme === 'dark' ? ' dark' : '';
    $html = '<!doctype html><html lang="uk" class="h-full'.$dark.'"><head><meta charset="utf-8">'
        .'<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><base href="http://gramlyze.loc/">'
        .'<title>M77 — приватне прев’ю формул ('.$theme.')</title><link rel="stylesheet" href="/build/'.$css.'">'.$existingStyles
        .'</head><body class="min-h-full font-body antialiased scroll-optimized">'
        .'<div class="catalog-frame relative mx-auto max-w-[1440px] px-0 py-0 lg:px-8 lg:py-6">'
        .'<div class="catalog-shell rounded-none border-0 shadow-none lg:rounded-[30px] lg:border lg:shadow-panel">'
        .'<main class="decorated-content px-4 lg:px-0"><div class="nd-page">'
        .'<p class="text-sm leading-6">Приватне прев’ю чернетки M77: усі картки форм, таблиці шести уроків і повернення до розділу, без інтерактивної практики. Це не live-приймання сторінок.</p>'
        .'<div class="mt-8 grid gap-6 lg:flex lg:items-start"><div class="min-w-0 flex-1 space-y-8 theory-design" data-theory-main>'
        .'<section class="theory-content-blocks"><div class="space-y-6">'.$rendered.'</div></section>'
        .'</div></div></div></main></div></div></body></html>';
    if (preg_match('/<script\b/i', $html)) { throw new RuntimeException('Formula preview must not introduce JavaScript.'); }
    $writeExclusive($path, $html);
    $artifacts[$theme] = ['basename' => basename($path), 'sha256' => hash('sha256', $html), 'bytes' => strlen($html)];
}
$receipt = [
    'schema_version' => 1, 'purpose' => 'M77 pre-freeze draft-only native formula SSR for a separate320px review',
    'not_live_acceptance' => true, 'draft_basename' => basename($draftPath), 'draft_sha256' => $expectedDraftSha,
    'served_root' => M77_PREVIEW_ROOT, 'runtime' => ['environment' => 'testing', 'database' => 'sqlite::memory:', 'cache' => 'array',
        'session' => 'array', 'working_env_loaded' => false, 'working_database_connections' => 0,
        'isolated_sqlite_connection_objects' => $databaseState['objects'], 'isolated_memory_pdos' => $databaseState['isolated_memory_pdos'],
        'blocked_before_execution_attempts' => $blockedQueryAttempts, 'db_queries' => $queryCount,
        'locale_schema_fixture' => ['process_local_cached_has_languages_table' => false, 'reason' => 'Known empty isolated SQLite memory schema; configured locale routes remain real.'],
        'http_requests' => 0, 'browser_actions' => 0, 'js_added' => false, 'css_added' => false],
    'renderer' => 'App\\Support\\TheoryContentRenderer::render', 'rendered' => $renderedRecords,
    'css_href' => '/build/'.$css, 'base_href' => 'http://gramlyze.loc/', 'accepted_visual_files_unchanged' => 326,
    'visual_pins' => $pins, 'private_artifacts' => $artifacts,
    'limitations' => ['Draft data is not a frozen/applied source.', 'All authored forms-grid and comparison-table blocks across six owners are rendered, including the One category-back linkcard; hero, usage, practice and complete page shell are not live-accepted here.',
        'No source-bound package design is fabricated; native content receives m42Design=null.',
        'Receipt proves SSR/data counts and isolation, not browser layout, font loading or real-page acceptance.'],
];
$writeExclusive($receiptPath, json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
echo json_encode(['status' => 'private-formula-preview-created', 'counts' => array_column($renderedRecords, 'count'), 'rendered_blocks' => count($renderedRecords),
    'owners' => count(array_unique(array_column($renderedRecords, 'identity'))), 'visual_files_unchanged' => 326,
    'db_queries' => 0, 'working_database_connections' => 0, 'artifacts' => array_column($artifacts, 'basename'),
    'receipt' => basename($receiptPath)], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), PHP_EOL;
$completed = true;
