<?php

// Explicit local SELECT-only registry and complete table fingerprints. Never apply/seeder/migrate.
declare(strict_types=1);
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { throw new RuntimeException('Local Windows CLI required.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
$name = $argv[1] ?? '';
if (!preg_match('/^(?:registry|db)-(?:before|after)-v[1-9][0-9]*\.json$/D', $name)) { throw new RuntimeException('Exclusive evidence basename required.'); }
$directory = $root.'/storage/app/theory-template-local';
if (!is_dir($directory) || is_link($directory) || file_exists($directory.'/'.$name)) { throw new RuntimeException('Exclusive private evidence required.'); }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root) || app(App\Support\SiteMode::class)->forHost('gramlyze.loc') !== 'development') { throw new RuntimeException('Unexpected application target.'); }
$db = Illuminate\Support\Facades\DB::connection();
// Validate configuration before obtaining either PDO. Do not print credentials or connection URLs.
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)
    || $db->getConfig('database') !== 'gr2' || (int) $db->getConfig('port') !== 3306
    || $db->getConfig('url') || $db->getConfig('unix_socket') || $db->getConfig('read') || $db->getConfig('write')) {
    throw new RuntimeException('Unexpected local database configuration.');
}
if ($db->getReadPdo() !== $db->getPdo()) { throw new RuntimeException('Split connection refused.'); }
$physical = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port, @@pid_file AS pid_file, @@basedir AS basedir, @@datadir AS datadir, @@version_compile_os AS os');
if ($physical['db'] !== 'gr2' || (int) $physical['port'] !== 3306 || strcasecmp($physical['server'], gethostname()) !== 0
    || !str_contains(strtolower((string) $physical['os']), 'win')) { throw new RuntimeException('Physical database mismatch.'); }
$pidFile = realpath((string) $physical['pid_file']);
$dataDir = realpath((string) $physical['datadir']);
if (!$pidFile || !$dataDir || !str_starts_with(strtolower(str_replace('\\', '/', $pidFile)), rtrim(strtolower(str_replace('\\', '/', $dataDir)), '/').'/')) {
    throw new RuntimeException('Database PID file is not inside actual local datadir.');
}
$pid = trim((string) file_get_contents($pidFile));
if (!ctype_digit($pid) || (int) $pid <= 0) { throw new RuntimeException('Invalid local database process identity.'); }
$json = static fn ($value): string => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
$digest = static fn ($value): string => hash('sha256', $json($value));
$write = static function (string $filename, array $payload) use ($json): string {
    $bytes = $json($payload)."\n"; $handle = fopen($filename, 'xb');
    try { if (!$handle || fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Incomplete exclusive evidence.'); } }
    finally { if ($handle) { fclose($handle); } }
    if (hash_file('sha256', $filename) !== hash('sha256', $bytes)) { throw new RuntimeException('Evidence write mismatch.'); }
    return hash('sha256', $bytes);
};
$resolver = app(App\Http\Controllers\TheoryController::class);
$categoryPath = new ReflectionMethod($resolver, 'categorySlugPath');
$resolveCategory = new ReflectionMethod($resolver, 'resolveCategoryBySlugPath');
$fallbackMethod = new ReflectionMethod($resolver, 'fallbackLocale');
$fallback = $fallbackMethod->invoke($resolver);
$routeForLocale = static fn (string $name, array $parameters, string $locale): string =>
    App\Modules\LanguageManager\Services\LocaleService::localizedRoute($name, $parameters, false, $locale);
$callerSource = file_get_contents($root.'/resources/views/theory/show.blade.php');
$canonicalTheory = preg_match('/[\'\"]theoryCanonical[\'\"]\s*=>\s*true/', $callerSource) === 1
    && class_exists(App\Support\TheoryComponents::class);
$adapters = [
    'M45FutureComparisonsPackage', 'M44AuthoredFutureFormsPackage', 'M43AuthoredTenseUsagePackage', 'M41AuthoredTenseComparisonsPackage',
    'M40TensesB1Package', 'M39PracticeUiPackage', 'M39AuthoredRevisionPackage', 'M38ArticlesCollocationsPackage', 'M37GrammarStructuresPackage',
    'M36ModalsSubjunctivePackage', 'M35PassiveReportingPackage', 'M34ArgumentationCohesionPackage', 'M33AcademicEnglishPackage',
    'M32FormalEnglishPackage', 'M31ConditionalsPackage', 'M30ParticipleClausesPackage', 'M29SentenceStructurePackage', 'M28EmphasisPackage', 'M27LinkingWordsPackage',
];
$rawTables = []; $registry = []; $categories = []; $nonTheoryPages = [];
$db->statement('SET TRANSACTION READ ONLY');
$db->beginTransaction();
try {
    // Fingerprints include every actual table, every row, every column and timestamps.
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $physical['db'], schemaQualified: false); sort($tables);
    foreach ($tables as $table) {
        $columns = $db->getSchemaBuilder()->getColumnListing($table);
        $primary = $db->select('SELECT COLUMN_NAME AS name FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? ORDER BY ORDINAL_POSITION', [$physical['db'], $table, 'PRIMARY']);
        $keys = array_map(static fn ($item) => $item->name, $primary);
        $query = $db->table($table);
        foreach ($keys ?: $columns as $column) { $query->orderBy($column); }
        $hash = hash_init('sha256'); $count = 0;
        foreach ($query->cursor() as $record) {
            $row = (array) $record; $rowHash = $digest($row); hash_update($hash, $rowHash."\n");
            $count++;
        }
        $schema = $db->getSchemaBuilder()->getColumns($table);
        $rawTables[$table] = ['count' => $count, 'sha256' => hash_final($hash), 'columns' => $columns,
            'schema_sha256' => $digest($schema), 'primary_key_columns' => $keys];
        echo $json(['table' => $table, 'rows' => $count, 'read_only' => true])."\n";
    }
    foreach (App\Models\PageCategory::query()->orderBy('id')->get() as $category) {
        $path = $categoryPath->invoke($resolver, $category);
        $resolved = $path !== '' ? $resolveCategory->invoke($resolver, $path) : null;
        $categories[] = ['category_id' => $category->id, 'type' => $category->type, 'language' => $category->language,
            'owner' => $category->seeder, 'slug' => $category->slug, 'category_path' => $path,
            'resolver_exact' => $resolved?->getKey() === $category->getKey(),
            'category_url' => $category->type === 'theory' ? route('theory.category', ['category' => $category->slug], false) : null,
            'is_real_theory_page' => false];
    }
    foreach (App\Models\Page::query()->with(['category.parent.parent.parent.parent.parent', 'textBlocks'])->orderBy('id')->get() as $page) {
        if ($page->type !== 'theory') {
            $nonTheoryPages[] = ['page_id' => $page->id, 'type' => $page->type, 'owner' => $page->seeder,
                'slug' => $page->slug, 'category_id' => $page->page_category_id]; continue;
        }
        $path = $page->category ? $categoryPath->invoke($resolver, $page->category) : '';
        $resolved = $path !== '' ? $resolveCategory->invoke($resolver, $path) : null;
        $sameSlug = $db->table('pages')->where('type', 'theory')->where('page_category_id', $page->page_category_id)->where('slug', $page->slug)->count();
        $routeExact = $page->category && $resolved?->getKey() === $page->category->getKey() && $sameSlug === 1;
        $url = $routeExact ? $routeForLocale('theory.show', ['categoryPath' => $path, 'pageSlug' => $page->slug], $fallback) : null;
        $locales = $page->textBlocks->pluck('locale')->unique()->sort()->values()->all();
        $variants = [];
        foreach ($locales as $locale) {
            app()->setLocale($locale);
            $blocks = $page->textBlocks->where('locale', $locale)->sortBy('sort_order')->values();
            $m45 = App\Support\M45FutureComparisonsPackage::allowsTheoryContext($blocks, $locale);
            $blocks = App\Support\M43AuthoredTenseUsagePackage::orderBlocks($blocks);
            $m44 = App\Support\M44AuthoredFutureFormsPackage::allowsTheoryContext($blocks, $locale);
            $blocks = App\Support\M44AuthoredFutureFormsPackage::orderBlocks($blocks);
            $records = []; $groups = []; $views = [];
            foreach ($blocks as $block) {
                $data = App\Support\TheoryPresentation::data($block->body);
                $native = App\Support\TheoryPresentation::nativeView($block->type);
                $presentation = null; $matched = null; $errors = [];
                $excluded = in_array($block->type, ['hero', 'hero-v2', 'navigation-chips', 'subtitle'], true);
                if ($native && !$excluded) {
                    foreach ($adapters as $adapter) {
                        if (($adapter === 'M45FutureComparisonsPackage' && !$m45) || ($adapter === 'M44AuthoredFutureFormsPackage' && !$m44)
                            || ($adapter === 'M43AuthoredTenseUsagePackage' && $locale !== 'uk')) { continue; }
                        $class = 'App\\Support\\'.$adapter;
                        try { $candidate = $class::presentation($block, $data); }
                        catch (Throwable $error) { $errors[] = ['adapter' => $adapter, 'error_class' => get_class($error), 'message_sha256' => hash('sha256', $error->getMessage())]; continue; }
                        if ($candidate !== null) { $presentation = $candidate; $matched = $adapter; break; }
                    }
                }
                $decoration = null;
                if ($native && !$excluded) {
                    try { $decoration = App\Support\M42NativeDesignPackage::decorate($block, $data, $presentation, $locale === 'uk'); }
                    catch (Throwable $error) { $errors[] = ['adapter' => 'M42NativeDesignPackage', 'error_class' => get_class($error), 'message_sha256' => hash('sha256', $error->getMessage())]; }
                }
                $effectiveView = $presentation['native_view'] ?? $native;
                if ($block->type === 'hero' || $block->type === 'hero-v2') { $effectiveView = 'theory.show:outer-hero'; }
                elseif ($block->type === 'navigation-chips') { $effectiveView = 'engram.theory.blocks-v3.navigation-chips'; }
                elseif ($block->type === 'subtitle') { $effectiveView = 'theory.partials.content-block:subtitle-anchor'; }
                elseif ($block->type === 'box' || !$block->type) { $effectiveView = 'components.theory-rich-box'; }
                elseif (!$native) { $effectiveView = 'theory.partials.content-block:escaped-fallback'; }
                $styles = ['resources/css/theory-unified-design.css'];
                if (!$canonicalTheory) {
                    if ($decoration !== null) { $styles[] = 'engram.theory.blocks-v3.m42-native-design-styles'; }
                    if ($presentation && isset($data['m43_v1'])) { $styles[] = 'engram.theory.blocks-v3.m43-native-styles'; }
                    if ($presentation && isset($data['m44_v1'])) { $styles[] = 'engram.theory.blocks-v3.m44-native-styles'; }
                    if ($presentation && isset($presentation['data']['m41_existing_design'])) { $styles[] = 'engram.theory.blocks-v3.m41-existing-design-styles'; }
                    elseif ($presentation && ($data['m41_v1']['role'] ?? null) === 'section') { $styles[] = 'engram.theory.blocks-v3.m41-section-styles'; }
                }
                $m26Points = $native && !$excluded && $presentation === null ? App\Support\M26PointDetails::fragmentsFor($block, $data) : null;
                $group = $matched ?? ($native ? 'native-legacy' : ($excluded ? 'shell-content' : ($block->type === 'box' || !$block->type ? 'rich-html' : 'escaped-fallback')));
                $groups[$group] = true; if ($decoration !== null) { $groups['M42NativeDesignPackage'] = true; }
                if ($m26Points !== null) { $groups['M26PointDetails'] = true; }
                $views[$effectiveView] = true;
                $records[] = ['block_id' => $block->id, 'uuid' => $block->uuid, 'owner' => $block->seeder, 'locale' => $block->locale,
                    'type' => $block->type, 'column' => $block->column, 'sort_order' => $block->sort_order, 'heading' => $block->heading,
                    'body_bytes' => strlen((string) $block->body), 'body_sha256' => hash('sha256', (string) $block->body),
                    'markers' => array_values(array_filter(array_keys($data), static fn ($key) => preg_match('/^m\d+_/', (string) $key) === 1)),
                    'native_view' => $native, 'presentation_adapter' => $matched, 'effective_view' => $effectiveView,
                    'theory_canonical_opt_in' => $canonicalTheory,
                    'render_source_sha256' => is_file($root.'/resources/views/'.str_replace('.', '/', $effectiveView).'.blade.php')
                        ? hash_file('sha256', $root.'/resources/views/'.str_replace('.', '/', $effectiveView).'.blade.php') : null,
                    'm42_decorated' => $decoration !== null, 'style_views' => $styles,
                    'm26_point_adapter' => $m26Points !== null, 'guarded_point_count' => count($presentation['points'] ?? $m26Points ?? []),
                    'adapter_errors' => $errors];
            }
            $variantUrl = $routeExact ? $routeForLocale('theory.show', ['categoryPath' => $path, 'pageSlug' => $page->slug], $locale) : null;
            $variants[] = ['locale' => $locale, 'url' => $variantUrl, 'absolute_url' => $variantUrl ? 'http://gramlyze.loc'.$variantUrl : null,
                'block_count' => count($records), 'block_types' => array_values(array_unique(array_column($records, 'type'))),
                'package_groups' => array_keys($groups), 'effective_views' => array_keys($views), 'm45_context_verified' => $m45,
                'm44_context_verified' => $m44, 'blocks' => $records];
        }
        app()->setLocale($fallback);
        $registry[] = ['page_id' => $page->id, 'type' => $page->type, 'owner' => $page->seeder, 'slug' => $page->slug,
            'title' => $page->title, 'page_row_sha256' => $digest($page->getAttributes()), 'category_id' => $page->page_category_id,
            'category_type' => $page->category?->type, 'category_language' => $page->category?->language,
            'category_path' => $path, 'resolver_exact' => $routeExact, 'url' => $url, 'absolute_url' => $url ? 'http://gramlyze.loc'.$url : null,
            'block_locales' => $locales, 'default_fallback_locale' => $fallback,
            'default_locale_has_blocks' => in_array($fallback, $locales, true),
            'variants' => $variants,
            'distinct_non_theory_consumers' => ['course_url' => $routeExact ? route('courses.theory.lesson', ['categoryPath' => $path, 'pageSlug' => $page->slug], false) : null,
                'topic_test_url' => route('test.show', ['slug' => App\Support\TheoryPageTestSlug::forPage($page)], false)]];
    }
    $db->rollBack();
} catch (Throwable $error) { if ($db->transactionLevel() > 0) { $db->rollBack(); } throw $error; }
$groups = []; $types = []; $localeCounts = []; $adapterErrors = [];
foreach ($registry as $page) {
    foreach ($page['variants'] as $variant) {
        $localeCounts[$variant['locale']] = ($localeCounts[$variant['locale']] ?? 0) + 1;
        foreach ($variant['package_groups'] as $group) { $groups[$group][] = $page['page_id']; }
        foreach ($variant['block_types'] as $type) { $types[$type][] = $page['page_id']; }
        foreach ($variant['blocks'] as $block) { foreach ($block['adapter_errors'] as $error) { $adapterErrors[] = ['page_id' => $page['page_id'], 'block_id' => $block['block_id'], ...$error]; } }
    }
}
foreach ($groups as &$ids) { $ids = array_values(array_unique($ids)); sort($ids); } unset($ids);
foreach ($types as &$ids) { $ids = array_values(array_unique($ids)); sort($ids); } unset($ids);
ksort($groups); ksort($types); ksort($localeCounts);
$payload = ['schema' => 'theory-template-local-registry-v1', 'captured_at' => gmdate('c'),
    'target' => ['root' => $root, 'origin' => 'http://gramlyze.loc', 'app_environment' => app()->environment(), 'site_mode_for_local_host' => app(App\Support\SiteMode::class)->forHost('gramlyze.loc'), 'driver' => 'mysql', 'host' => $db->getConfig('host'),
        'port' => (int) $physical['port'], 'database' => $physical['db'], 'hostname_verified' => true, 'physical_pid' => (int) $pid,
        'pid_inside_local_datadir' => true, 'web_cli_equivalence' => 'not claimed: no fresh nonce route requested'],
    'read_only' => true, 'render_path_evidence' => ['method' => 'Exact guarded adapters and source-inspected caller/view paths; live DOM/assets are separately checked.',
        'theory_canonical_opt_in' => $canonicalTheory, 'theory_caller_sha256' => hash('sha256', $callerSource)],
    'raw_tables' => $rawTables, 'registry' => $registry, 'categories' => $categories, 'non_theory_pages' => $nonTheoryPages,
    'summary' => ['tables' => count($rawTables), 'rows' => array_sum(array_column($rawTables, 'count')), 'real_theory_pages' => count($registry),
        'resolver_exact_pages' => count(array_filter($registry, static fn ($page) => $page['resolver_exact'])),
        'url_less_pages' => array_values(array_map(static fn ($page) => $page['page_id'], array_filter($registry, static fn ($page) => !$page['url']))),
        'locale_variants' => $localeCounts, 'package_group_page_ids' => $groups, 'block_type_page_ids' => $types, 'adapter_errors' => $adapterErrors,
        'category_records_not_page_total' => count($categories), 'non_theory_pages' => count($nonTheoryPages)]];
$checksum = $write($directory.'/'.$name, $payload);
echo $json(['file' => $directory.'/'.$name, 'sha256' => $checksum, 'summary' => $payload['summary'], 'db_writes' => 0])."\n";
