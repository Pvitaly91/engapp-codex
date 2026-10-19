<?php

// Explicit SELECT-only BEFORE/AFTER inventory. No seeding, observers or cache clear.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
$source = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
$name = $argv[1] ?? '';
if (!preg_match('/^m44-(before|after|after-noop|final)-v[1-9][0-9]*\.json$/D', $name)) { throw new RuntimeException('Exclusive M44 evidence basename required.'); }
$directory = $root.'/storage/app/seo-m44-local';
if (!is_dir($directory) || is_link($directory) || file_exists($directory.'/'.$name)) { throw new RuntimeException('Exclusive private directory required.'); }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root)) { throw new RuntimeException('Wrong local application.'); }
$db = Illuminate\Support\Facades\DB::connection();
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1'], true)
    || $db->getConfig('database') !== 'gr2' || (int) $db->getConfig('port') !== 3306
    || $db->getConfig('url') || $db->getConfig('unix_socket') || $db->getConfig('read') || $db->getConfig('write')
    || $db->getReadPdo() !== $db->getPdo()) { throw new RuntimeException('Unexpected local connection.'); }
$physical = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port');
if ($physical['db'] !== 'gr2' || (int) $physical['port'] !== 3306 || strcasecmp($physical['server'], gethostname()) !== 0) { throw new RuntimeException('Physical database mismatch.'); }
$json = static fn ($v) => json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
$digest = static fn ($v) => hash('sha256', $json($v));
$classes = ['FutureFormsWillVsBeGoingToTheorySeeder', 'FutureFormsPresentContinuousForFutureTheorySeeder', 'FutureFormsChoosingTheRightFutureFormTheorySeeder'];
$resolver = app(App\Http\Controllers\TheoryController::class);
$categoryPath = new ReflectionMethod($resolver, 'categorySlugPath');
$resolveCategory = new ReflectionMethod($resolver, 'resolveCategoryBySlugPath');
$targets = [];
$allowedPages = [];
$allowedBlocks = [];
$db->statement('SET TRANSACTION READ ONLY');
$db->beginTransaction();
try {
    foreach ($classes as $short) {
        $identity = 'Database\\Seeders\\Page_V3\\FutureForms\\'.$short;
        $path = 'database/seeders/Page_V3/FutureForms/'.$short.'/definition.json';
        $bytes = file_get_contents($source.'/'.$path);
        $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if (file_get_contents($root.'/'.$path) !== $bytes) { throw new RuntimeException('Served/worktree source differs: '.$short); }
        $canonicalState = isset(json_decode($definition['page']['blocks'][0]['body'], true)['m44_v1']) ? 'after' : 'before';
        $definitionChecksum = hash('sha256', $bytes);
        if (str_starts_with($name, 'm44-before-') && is_file($source.'/database/content-patches/m44-authored-future-forms-before.json')) {
            [$frozenBefore, $frozenAfter] = App\Support\M44AuthoredFutureFormsPackage::load($source);
            App\Support\M44AuthoredFutureFormsPackage::validate($frozenBefore, $frozenAfter, $source);
            $ownerIndex = array_search($identity, array_column($frozenBefore['targets'], 'identity'), true);
            if ($ownerIndex === false) { throw new RuntimeException('Unknown frozen M44 owner.'); }
            $original = $frozenBefore['targets'][$ownerIndex]['before'];
            if ($definition !== $original && $definition !== $frozenAfter['targets'][$ownerIndex]['after']) { throw new RuntimeException('Canonical source is neither frozen BEFORE nor AFTER.'); }
            $canonicalState = $definition === $original ? 'before' : 'after';
            $definition = $original;
            $definitionChecksum = $frozenBefore['targets'][$ownerIndex]['definition_sha256'];
        }
        $page = App\Models\Page::query()->where('seeder', $identity)->with('category.parent.parent.parent.parent')->sole();
        $owner = $page->getAttributes();
        $chain = [];
        $seen = [];
        $categoryId = $owner['page_category_id'];
        while ($categoryId !== null) {
            $category = (array) $db->table('page_categories')->where('id', $categoryId)->sole();
            if (isset($seen[$categoryId]) || count($chain) > 8 || $category['language'] !== 'uk' || $category['type'] !== 'theory') { throw new RuntimeException('Category ancestry conflict.'); }
            $seen[$categoryId] = true;
            array_unshift($chain, $category);
            $categoryId = $category['parent_id'];
        }
        $resolvedPath = $categoryPath->invoke($resolver, $page->category);
        if ($resolveCategory->invoke($resolver, $resolvedPath)?->getKey() !== $page->category->getKey()
            || $definition['seeder']['class'] !== $identity || $owner['slug'] !== $definition['slug']
            || $owner['title'] !== $definition['page']['title'] || $owner['type'] !== 'theory'
            || end($chain)['slug'] !== $definition['page']['category']['slug'] || $definition['page']['locale'] !== 'uk') { throw new RuntimeException('Exact owner/resolver conflict.'); }
        $theoryPath = route('theory.show', ['categoryPath' => $resolvedPath, 'pageSlug' => $page->slug], false);
        $coursePath = route('courses.theory.lesson', ['categoryPath' => $resolvedPath, 'pageSlug' => $page->slug], false);
        $blocks = $db->table('text_blocks')->where('page_id', $owner['id'])->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
        $uk = array_values(array_filter($blocks, fn ($r) => $r['locale'] === 'uk'));
        $configs = [['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'level' => $definition['page']['subtitle_level'] ?? null,
            'body' => $definition['page']['subtitle_html'], 'uuid_key' => $definition['page']['subtitle_uuid_key'] ?? 'subtitle'], ...$definition['page']['blocks']];
        $exact = $owner['text'] === $definition['page']['subtitle_text'] && count($uk) === count($configs);
        foreach ($configs as $position => $config) {
            $uuid = App\Support\M26DetailPackage::uuid($identity, $config, $position);
            $found = array_values(array_filter($uk, fn ($r) => $r['uuid'] === $uuid));
            if (count($found) !== 1) { $exact = false; continue; }
            $row = $found[0];
            foreach (['type', 'body', 'column', 'heading', 'level', 'css_class'] as $field) { if ($row[$field] !== ($config[$field] ?? null)) { $exact = false; } }
            if ($row['seeder'] !== $identity || (int) $row['sort_order'] !== $position || $row['page_category_id'] !== $owner['page_category_id']) { $exact = false; }
        }
        if (!$exact) { throw new RuntimeException('Actual definition/DB conflict: '.$short); }
        $questions = $db->table('questions')->join('question_theory_text_blocks', 'questions.uuid', '=', 'question_theory_text_blocks.question_uuid')
            ->whereIn('text_block_uuid', array_column($blocks, 'uuid'))->distinct()->orderBy('questions.id')
            ->get(['questions.id', 'questions.seeder', 'questions.type', 'questions.level']);
        $banks = [];
        foreach ($questions->groupBy('seeder') as $seeder => $rows) {
            $global = $db->table('questions')->where('seeder', $seeder)->orderBy('id')->get(['id', 'type', 'level']);
            $banks[] = ['seeder_class' => $seeder, 'linked_ids' => $rows->pluck('id')->all(), 'linked_count' => $rows->count(),
                'linked_types' => $rows->pluck('type')->unique()->values()->all(), 'linked_levels' => $rows->pluck('level')->unique()->sort()->values()->all(),
                'global_ids' => $global->pluck('id')->all(), 'global_count' => $global->count(),
                'full_own_bank' => $rows->pluck('id')->all() === $global->pluck('id')->all()];
        }
        $navigation = [];
        foreach ($definition['page']['blocks'] as $slot => $config) {
            if ($config['type'] !== 'navigation-chips') { continue; }
            foreach (json_decode($config['body'], true, flags: JSON_THROW_ON_ERROR)['items'] as $item) {
                $slug = basename($item['url']);
                $matches = App\Models\Page::query()->where('slug', $slug)->where('type', 'theory')->with('category.parent.parent.parent.parent')->get()
                    ->filter(fn ($candidate) => $candidate->category?->language === 'uk');
                if ($matches->count() === 0) {
                    $categories = App\Models\PageCategory::query()->where('slug', $slug)->where('type', 'theory')->where('language', 'uk')
                        ->with('parent.parent.parent.parent')->get();
                    if ($categories->count() !== 1) { throw new RuntimeException('Navigation destination is not uniquely resolved: '.$item['url']); }
                    $destination = $categories->first();
                    $navigation[] = ['slot' => $slot, 'label' => $item['label'], 'old_url' => $item['url'], 'destination_category_id' => $destination->id,
                        'url' => route('theory.category', ['category' => $destination->slug], false)];
                    continue;
                }
                if ($matches->count() !== 1) { throw new RuntimeException('Navigation page is ambiguous: '.$item['url']); }
                $destination = $matches->first();
                $navigation[] = ['slot' => $slot, 'label' => $item['label'], 'old_url' => $item['url'], 'destination_page_id' => $destination->id,
                    'url' => route('theory.show', ['categoryPath' => $categoryPath->invoke($resolver, $destination->category), 'pageSlug' => $destination->slug], false)];
            }
        }
        $targets[] = ['identity' => $identity, 'path' => $path, 'theory_path' => $theoryPath, 'course_path' => $coursePath,
            'url' => 'http://gramlyze.loc'.$theoryPath, 'page' => $owner, 'category_chain' => $chain, 'blocks' => $blocks,
            'source_db_exact' => $canonicalState === 'before' || !str_starts_with($name, 'm44-before-'),
            'db_matches_frozen_source' => true, 'canonical_state' => $canonicalState,
            'canonical_definition_sha256' => hash('sha256', $bytes), 'definition_sha256' => $definitionChecksum, 'banks' => $banks, 'navigation' => $navigation,
            'test_path' => route('test.show', ['slug' => App\Support\TheoryPageTestSlug::forPage($page)], false)];
        $allowedPages[] = $owner['id'];
        $allowedBlocks = [...$allowedBlocks, ...array_column($uk, 'id')];
    }
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $physical['db'], schemaQualified: false);
    sort($tables);
    $raw = [];
    $protected = [];
    foreach ($tables as $table) {
        $columns = $db->getSchemaBuilder()->getColumnListing($table);
        $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $allHash = hash_init('sha256');
        $protectedHash = hash_init('sha256');
        $count = 0;
        $protectedCount = 0;
        foreach ($query->cursor() as $record) {
            $row = (array) $record;
            hash_update($allHash, $digest($row)."\n");
            $count++;
            if ($table === 'text_blocks' && in_array($row['id'], $allowedBlocks, true)) { continue; }
            if ($table === 'pages' && in_array($row['id'], $allowedPages, true)) { unset($row['text']); }
            hash_update($protectedHash, $digest($row)."\n");
            $protectedCount++;
        }
        $raw[$table] = ['count' => $count, 'sha256' => hash_final($allHash)];
        $protected[$table] = ['count' => $protectedCount, 'sha256' => hash_final($protectedHash)];
    }
    $result = ['at' => gmdate('c'), 'target' => ['root' => $root, 'driver' => 'mysql', 'host' => $db->getConfig('host'), 'port' => (int) $physical['port'], 'database' => $physical['db']],
        'targets' => $targets, 'raw_tables' => $raw, 'protected_tables' => $protected];
    $db->rollBack();
} catch (Throwable $error) {
    if ($db->transactionLevel() > 0) { $db->rollBack(); }
    throw $error;
}
$bytes = $json($result)."\n";
$handle = fopen($directory.'/'.$name, 'xb');
try {
    if (!$handle || fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Incomplete exclusive evidence.'); }
} finally { if ($handle) { fclose($handle); } }
echo $json(['file' => $name, 'sha256' => hash('sha256', $bytes), 'tables' => count($raw),
    'targets' => array_map(fn ($t) => ['page_id' => $t['page']['id'], 'identity' => $t['identity'], 'url' => $t['url'], 'course_path' => $t['course_path'],
        'banks' => array_map(fn ($b) => array_diff_key($b, ['linked_ids' => true, 'global_ids' => true]), $t['banks'])], $targets)])."\n";
