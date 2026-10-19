<?php

// SELECT-only evidence for the physical working .loc; never returns credentials.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $source = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root)) { throw new RuntimeException('Wrong working root.'); }
$db = Illuminate\Support\Facades\DB::connection();
$physical = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port');
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1'], true)
    || $physical['db'] !== 'gr2' || (int) $physical['port'] !== 3306 || strcasecmp($physical['server'], gethostname()) !== 0) {
    throw new RuntimeException('Unexpected read-only local target.');
}
$targets = []; $banks = []; $anchorSources = [];
// Exact finite identity => slug/category mapping; levels C1/C1/C2.
$definitions = [
    ['Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveReportingStructuresTheorySeeder', 'passive-reporting-structures', 'passive-voice'],
    ['Database\\Seeders\\Page_V3\\PassiveVoice\\ComplexPassiveAndCausativeTheorySeeder', 'complex-passive-and-causative', 'passive-voice'],
    ['Database\\Seeders\\Page_V3\\PassiveVoice\\ComplexPassiveImpersonalStyleTheorySeeder', 'complex-passive-impersonal-style', 'passive-voice'],
];
foreach ($definitions as $i => [$name, $slug, $categorySlug]) {
    $page = $db->table('pages')->where('seeder', $name)->sole();
    $category = $db->table('page_categories')->where('id', $page->page_category_id)->sole();
    if ($page->slug !== $slug || $page->type !== 'theory' || $category->slug !== $categorySlug || $category->parent_id !== null || $category->language !== 'uk' || $category->type !== 'theory') {
        throw new RuntimeException('Unexpected M35 owner/category identity.');
    }
    $blocks = $db->table('text_blocks')->where('page_id', $page->id)->orderBy('id')->get();
    $ids = [];
    foreach ($blocks as $block) {
        preg_match_all('/\bid\s*=\s*["\']([^"\']+)["\']/i', $block->body ?? '', $matches);
        $ids = [...$ids, ...$matches[1]];
        if ($block->type === 'practice-set') {
            $native = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            if (isset($native['m35_v1']['legacy_practice_id'])) { $ids[] = $native['m35_v1']['legacy_practice_id']; }
        }
    }
    $anchorSources[$name] = ['slug' => $slug, 'ids' => array_values(array_unique($ids))];
    $questions = $db->table('questions')->join('question_theory_text_blocks', 'questions.uuid', '=', 'question_theory_text_blocks.question_uuid')
        ->whereIn('text_block_uuid', $blocks->pluck('uuid'))->distinct()->orderBy('questions.id')
        ->get(['questions.id', 'questions.seeder', 'questions.type', 'questions.level']);
    $groups = $questions->groupBy(fn ($q) => $q->seeder.'|'.$q->type.'|'.$q->level);
    $globalLevels = [];
    foreach ($questions->pluck('seeder')->unique() as $seeder) {
        $globalLevels[$seeder] = $db->table('questions')->where('seeder', $seeder)->distinct()->pluck('level')->all();
    }
    $level = ['C1', 'C1', 'C2'][$i];
    $primary = $groups->filter(fn ($q) => (string) $q[0]->type === '4' && $q[0]->level === $level
        && count($globalLevels[$q[0]->seeder]) === 1);
    if ($primary->count() !== 1) { throw new RuntimeException('Missing/ambiguous exact M35 primary bank.'); }
    $q = $primary->first();
    $banks[$name] = ['seeder_class' => $q[0]->seeder, 'question_type' => (string) $q[0]->type, 'level' => $q[0]->level, 'question_ids' => $q->pluck('id')->all()];
    $targets[] = ['identity' => $name, 'slug' => $page->slug, 'page_id' => $page->id, 'category_id' => $page->page_category_id,
        'page_sha256' => hash('sha256', json_encode($page)),
        'category_slug' => $category->slug, 'category_sha256' => hash('sha256', json_encode($category)),
        'blocks' => $blocks->map(fn ($b) => ['id' => $b->id, 'uuid' => $b->uuid, 'type' => $b->type, 'locale' => $b->locale, 'order' => $b->sort_order, 'body_sha256' => hash('sha256', $b->body)])->all(),
        'linked_bank_groups' => $groups->map(fn ($q) => $q->count())->all(), 'global_bank_levels' => $globalLevels,
        'linked_bank_ids' => $questions->groupBy('seeder')->map(fn ($q) => $q->pluck('id')->all())->all()];
}
// Priority inventory may return before the larger protected-data snapshot; still SELECT-only.
if (($argv[1] ?? '') === '--banks-only') {
    echo json_encode(['at' => gmdate('c'), 'root' => $root, 'driver' => 'mysql', 'host' => $db->getConfig('host'), 'server' => $physical['server'], 'port' => (int) $physical['port'], 'database' => $physical['db'],
        'targets' => $targets, 'linked_banks' => $banks], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    exit(0);
}
$regressions = [];
foreach (['m27-m11-linking-words.v2.json', 'm28-m12-emphasis-inversion.v2.json', 'm29-m13-sentence-structure.v2.json', 'm30-m14-participle-clauses.v1.json', 'm31-m15-conditionals.v1.json', 'm32-m16-formal-english.v1.json', 'm33-m17-academic-english.v1.json', 'm34-m18-argumentation-cohesion.v1.json'] as $file) {
    $path = $source.'/database/content-patches/'.$file;
    if (!is_file($path)) { throw new RuntimeException('Missing regression package: '.$file); }
    $package = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    foreach ($package['targets'] as $t) {
        $page = $db->table('pages')->where('seeder', $t['identity'])->sole();
        $blocks = $db->table('text_blocks')->where('page_id', $page->id)->orderBy('id')->get();
        if ($page->type !== 'theory' || $page->slug !== $t['slug'] || $page->title !== $t['after']['page']['title'] || $page->text !== $t['after']['page']['subtitle_text']) {
            throw new RuntimeException('Regression owner metadata differs: '.$t['slug']);
        }
        if ($blocks->where('locale', 'uk')->count() !== count($t['after']['page']['blocks']) + 1) { throw new RuntimeException('Regression unexpected UK rows.'); }
        foreach ($t['after']['page']['blocks'] as $j => $b) {
            $uuid = App\Support\M26DetailPackage::uuid($t['identity'], $b, $j + 1);
            $r = $blocks->firstWhere('uuid', $uuid);
            if (!$r || $r->body !== $b['body'] || $r->type !== $b['type'] || (int) $r->sort_order !== $j + 1 || $r->locale !== 'uk' || $r->seeder !== $t['identity']) {
                throw new RuntimeException('Regression body differs: '.$t['slug']);
            }
        }
        $regressions[] = ['package' => $file, 'slug' => $t['slug'], 'page_id' => $page->id,
            'page_sha256' => hash('sha256', json_encode($page)), 'blocks_sha256' => hash('sha256', json_encode($blocks)), 'exact_full_author_payload' => true];
    }
}

[$m26Master, $m26Before] = App\Support\M26DetailPackage::load($source);
foreach ($m26Master['targets'] as $t) {
    $isCategory = $t['source_content_root'] === 'description';
    $after = App\Support\M26InteractivePractice::definition($t, $m26Before['definitions'][$t['identity']]);
    $expected = $after[$t['source_content_root']];
    $table = $isCategory ? 'page_categories' : 'pages';
    $owner = $db->table($table)->where('seeder', $t['identity'])->sole();
    $query = $db->table('text_blocks');
    if ($isCategory) { $query->whereNull('page_id')->where('page_category_id', $owner->id); }
    else { $query->where('page_id', $owner->id); }
    $blocks = $query->orderBy('id')->get();
    if ($owner->slug !== $t['slug'] || $blocks->where('locale', 'uk')->count() !== count($expected['blocks']) + 1) {
        throw new RuntimeException('M26 regression identity/rows differ.');
    }
    foreach ($expected['blocks'] as $j => $b) {
        $uuid = App\Support\M26DetailPackage::uuid($t['identity'], $b, $j + 1);
        $row = $blocks->firstWhere('uuid', $uuid);
        if (!$row || $row->body !== $b['body'] || $row->type !== $b['type'] || (int) $row->sort_order !== $j + 1
            || $row->locale !== 'uk' || $row->seeder !== $t['identity']) {
            throw new RuntimeException('M26 exact accepted body differs: '.$t['slug']);
        }
    }
    $regressions[] = ['package' => 'm26-master+interactive-v1', 'slug' => $t['slug'], 'owner_table' => $table,
        'owner_id' => $owner->id, 'owner_sha256' => hash('sha256', json_encode($owner)),
        'blocks_sha256' => hash('sha256', json_encode($blocks)), 'exact_full_author_payload' => true];
}

$fingerprints = []; $anchorReferences = [];
$allAnchorIds = array_merge(...array_column($anchorSources, 'ids'));
$targetSlugs = array_column($anchorSources, 'slug');
foreach (['pages', 'page_categories', 'text_blocks', 'tags', 'page_tag', 'page_category_tag', 'tag_text_block', 'questions', 'question_answers', 'question_options', 'question_option_question', 'question_theory_text_blocks', 'question_tag', 'question_marker_tag', 'verb_hints', 'question_hints', 'question_variants', 'saved_grammar_tests', 'saved_grammar_test_questions', 'seed_runs'] as $table) {
    $query = $db->table($table); $columns = $db->getSchemaBuilder()->getColumnListing($table);
    foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
    $hash = hash_init('sha256'); $count = 0;
    foreach ($query->cursor() as $r) {
        hash_update($hash, App\Services\PronounContentRepair::digest((array) $r)."\n"); $count++;
        if (in_array($table, ['pages', 'text_blocks'], true)) {
            preg_match_all('/\bhref\s*=\s*["\']([^"\']+)["\']/i', $r->body ?? $r->text ?? '', $matches);
            foreach ($matches[1] as $href) {
                $fragment = parse_url(html_entity_decode($href, ENT_QUOTES | ENT_HTML5), PHP_URL_FRAGMENT);
                if (($fragment !== null && in_array($fragment, $allAnchorIds, true))
                    || array_filter($targetSlugs, fn ($slug) => str_contains($href, $slug.'#'))) {
                    $anchorReferences[] = ['table' => $table, 'id' => $r->id, 'seeder' => $r->seeder ?? null, 'href' => $href];
                }
            }
        }
    }
    $fingerprints[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
}
$changedIds = [];
foreach ($targets as $target) {
    foreach ($target['blocks'] as $block) {
        if ($block['locale'] === 'uk' && (int) $block['order'] >= 2) { $changedIds[] = $block['id']; }
    }
}
$hash = hash_init('sha256'); $count = 0;
foreach ($db->table('text_blocks')->whereNotIn('id', $changedIds)->orderBy('id')->cursor() as $row) {
    hash_update($hash, App\Services\PronounContentRepair::digest((array) $row)."\n"); $count++;
}
$nonTargetBlocks = ['count' => $count, 'sha256' => hash_final($hash)];
$record = ['at' => gmdate('c'), 'target' => ['root' => $root, 'driver' => 'mysql', 'host' => $db->getConfig('host'), 'port' => $physical['port'], 'database' => $physical['db'], 'environment' => app()->environment()],
    'targets' => $targets, 'linked_banks' => $banks, 'regressions' => $regressions, 'fingerprints' => $fingerprints, 'non_target_text_blocks' => $nonTargetBlocks,
    'anchor_reference_inventory' => ['sources' => $anchorSources, 'references' => $anchorReferences]];
$dir = storage_path('app/seo-m35-local'); if (!is_dir($dir)) { mkdir($dir, 0700, true); }
if (($argv[1] ?? '') === '--save') {
    $basename = $argv[2] ?? '';
    if (!preg_match('/^[a-z0-9-]+\.json$/D', $basename)) { throw new RuntimeException('Invalid private evidence basename.'); }
    $f = fopen($dir.'/'.$basename, 'x'); if (!$f) { throw new RuntimeException('Evidence already exists.'); }
    $json = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    try { if (fwrite($f, $json) !== strlen($json) || !fflush($f)) { throw new RuntimeException('Incomplete evidence.'); } } finally { fclose($f); }
}
echo json_encode(['at' => $record['at'], 'targets' => array_map(fn ($t) => ['slug' => $t['slug'], 'page_id' => $t['page_id'], 'blocks' => count($t['blocks'])], $targets),
    'banks' => array_map(fn ($b) => ['seeder' => $b['seeder_class'], 'type' => $b['question_type'], 'level' => $b['level'], 'count' => count($b['question_ids'])], $banks), 'regression_pages' => count($regressions), 'non_target_text_blocks' => $nonTargetBlocks, 'tables' => count($fingerprints)], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
