<?php

// SELECT-only evidence for the physical working .loc; never returns credentials.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $source = dirname(__DIR__, 2);
$arguments = array_slice($argv, 1); $banksOnly = false; $outputName = null;
if (($arguments[0] ?? '') === '--banks-only') { $banksOnly = true; array_shift($arguments); }
if ($arguments !== []) {
    if (count($arguments) !== 2 || $arguments[0] !== '--output'
        || !preg_match('/^m39-practice-ui-(?:before|after|after-noop|bank-inventory)-v[1-9][0-9]*\.json$/D', $arguments[1])) {
        throw new RuntimeException('Use --banks-only optionally followed by --output and an exact private M39 evidence basename.');
    }
    $outputName = $arguments[1];
    if ($banksOnly !== str_starts_with($outputName, 'm39-practice-ui-bank-inventory-')) {
        throw new RuntimeException('M39 evidence basename does not match the inventory mode.');
    }
}
$saveEvidence = static function (array $record) use ($root, $outputName): ?array {
    if ($outputName === null) { return null; }
    $directory = $root.'/storage/app/seo-m39-local';
    if (is_link($directory)) { throw new RuntimeException('Private evidence directory cannot be a link.'); }
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) { throw new RuntimeException('Cannot create private M39 evidence directory.'); }
    $path = $directory.'/'.$outputName;
    $bytes = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    $file = fopen($path, 'xb');
    if (!$file) { throw new RuntimeException('Private M39 evidence already exists; no overwrite.'); }
    try { if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('Incomplete M39 evidence.'); } }
    finally { fclose($file); }
    return ['path' => $path, 'sha256' => hash('sha256', $bytes)];
};
require $root.'/vendor/autoload.php';
// Validate versioned sources in a path-only worktree container before the served ROOT is synced.
$sourceApp=new Illuminate\Foundation\Application($source);
require_once $source.'/app/Support/M39PracticeUiPackage.php';
$uiPackage=App\Support\M39PracticeUiPackage::load($source);
App\Support\M39PracticeUiPackage::validate($uiPackage);
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root)) { throw new RuntimeException('Wrong working root.'); }
$db = Illuminate\Support\Facades\DB::connection();
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1'], true)
    || $db->getConfig('database') !== 'gr2' || (int) $db->getConfig('port') !== 3306
    || $db->getConfig('url') || $db->getConfig('unix_socket') || $db->getConfig('read') || $db->getConfig('write')) {
    throw new RuntimeException('Unexpected configured target; no database query permitted.');
}
if ($db->getReadPdo() !== $db->getPdo()) { throw new RuntimeException('Split read connection; no inventory permitted.'); }
$physical = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port');
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1'], true)
    || $physical['db'] !== 'gr2' || (int) $physical['port'] !== 3306 || strcasecmp($physical['server'], gethostname()) !== 0) {
    throw new RuntimeException('Unexpected read-only local target.');
}
$masterPath = $source.'/docs/content/m23-authored-content.v1.json';
$masterBytes = file_get_contents($masterPath);
$masterLf = str_replace("\r\n", "\n", $masterBytes);
if (hash('sha256', $masterLf) !== 'eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6'
    || hash('sha1', 'blob '.strlen($masterLf)."\0".$masterLf) !== '34a03a7146141fbff50c66ec8e41f3fc2a59b787') {
    throw new RuntimeException('Immutable M23 master fingerprint differs; STOP before any source edits or writes.');
}
$authorMaster = json_decode($masterBytes, true, flags: JSON_THROW_ON_ERROR);
$targets = []; $banks = []; $anchorSources = []; $sourceFidelity = [];
// Exact finite identity => actual root-to-leaf ancestry; no slug-derived bank guesses.
$definitions = [
    ['Database\\Seeders\\Page_V3\\FormalEnglish\\NominalStyleAndInformationDensityTheorySeeder', 'nominal-style-and-information-density', ['formal-english']],
    ['Database\\Seeders\\Page_V3\\BasicGrammar\\C1MixedRevisionTheorySeeder', 'c1-mixed-revision', ['mixed-revision']],
    ['Database\\Seeders\\Page_V3\\BasicGrammar\\C2MixedRevisionTheorySeeder', 'c2-mixed-revision', ['mixed-revision']],
];
foreach ($definitions as $i => [$name, $slug, $expectedAncestry]) {
    $page = $db->table('pages')->where('seeder', $name)->sole();
    $category = $db->table('page_categories')->where('id', $page->page_category_id)->sole();
    $chain = []; $seen = []; $node = $category;
    while ($node !== null) {
        if (isset($seen[$node->id]) || count($chain) >= 8 || $node->language !== 'uk' || $node->type !== 'theory') {
            throw new RuntimeException('Invalid existing M39 category ancestry.');
        }
        $seen[$node->id] = true; array_unshift($chain, (array) $node);
        $node = $node->parent_id === null ? null : $db->table('page_categories')->where('id', $node->parent_id)->sole();
    }
    $categorySlug = $category->slug;
    if ($page->slug !== $slug || $page->type !== 'theory' || array_column($chain, 'slug') !== $expectedAncestry) {
        throw new RuntimeException('Unexpected M39 owner/category identity.');
    }
    $blocks = $db->table('text_blocks')->where('page_id', $page->id)->orderBy('id')->get();
    $author = $authorMaster['lessons'][$i];
    if ($author['seeder'] !== $name || $author['slug'] !== $slug || $author['category_path'] !== $expectedAncestry
        || $page->title !== $author['preserve_page_title'] || $page->text !== $author['subtitle_text']) {
        throw new RuntimeException('Immutable master/working owner identity or subtitle differs; STOP.');
    }
    $uk = $blocks->where('locale', 'uk')->sortBy('sort_order')->values();
    if ($uk->count() === 3 && $uk[2]->type === 'box') {
        $definition = json_decode(file_get_contents($source.'/'.$author['definition_path']), true, flags: JSON_THROW_ON_ERROR);
        $canonicalBox = $definition['page']['blocks'][1] ?? null;
        if (!$canonicalBox || $canonicalBox['type'] !== 'box') {
            $beforePath = $source.'/database/content-patches/m39-m23-authored-revision-before.json';
            if (!is_file($beforePath)) { throw new RuntimeException('Canonical native source requires a bound frozen accepted definition.'); }
            $before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
            $matches = array_values(array_filter($before['targets'] ?? [], fn ($target) => ($target['before']['seeder']['class'] ?? null) === $name));
            if (count($matches) !== 1) { throw new RuntimeException('Frozen accepted owner missing or ambiguous.'); }
            $definition = $matches[0]['before'];
            $canonicalBox = $definition['page']['blocks'][1];
        }
        if ($canonicalBox['body'] !== $author['body_html'] || $uk[2]->body !== $author['body_html']
            || $canonicalBox['heading'] !== $author['box_heading'] || $uk[2]->heading !== $author['box_heading']
            || $definition['page']['subtitle_html'] !== $author['subtitle_html']
            || $definition['page']['subtitle_text'] !== $author['subtitle_text'] || $uk[0]->body !== $author['subtitle_html']
            || json_decode($definition['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR) !== $author['hero']
            || json_decode($uk[1]->body, true, flags: JSON_THROW_ON_ERROR) !== $author['hero']) {
            throw new RuntimeException('Immutable master/accepted definition/actual DB learner content differs; STOP.');
        }
        $sourceFidelity[$name] = ['state'=>'accepted_author_before', 'master_definition_db_exact'=>true,
            'body_sha256'=>hash('sha256',$uk[2]->body), 'box_id'=>$uk[2]->id, 'box_uuid'=>$uk[2]->uuid];
    } else {
        $sourceFidelity[$name] = ['state'=>'native_after', 'master_definition_db_exact_before_verified'=>null];
    }
    $uiTargets=array_values(array_filter($uiPackage['targets'],fn($target)=>$target['identity']===$name));
    if(count($uiTargets)!==1)throw new RuntimeException('Practice UI owner missing or ambiguous.');
    $uiTarget=$uiTargets[0];
    $oldPractice=array_values(array_filter($uiTarget['before']['page']['blocks'],fn($block)=>$block['type']==='practice-set'));
    $newPractice=array_values(array_filter($uiTarget['after']['page']['blocks'],fn($block)=>$block['type']==='practice-set'));
    $actualPractice=$uk->where('type','practice-set')->values();
    if(count($oldPractice)!==1||count($newPractice)!==1||$actualPractice->count()!==1
        ||!in_array($actualPractice[0]->body,[$oldPractice[0]['body'],$newPractice[0]['body']],true))
        throw new RuntimeException('Actual M39 UI practice body differs from accepted before/after; stop.');
    $sourceFidelity[$name]=['state'=>$actualPractice[0]->body===$oldPractice[0]['body']?'practice_ui_before':'practice_ui_after',
        'practice_source_exact'=>true,'practice_id'=>$actualPractice[0]->id,'practice_uuid'=>$actualPractice[0]->uuid,
        'body_sha256'=>hash('sha256',$actualPractice[0]->body)];
    $ids = [];
    foreach ($blocks as $block) {
        preg_match_all('/\bid\s*=\s*["\']([^"\']+)["\']/i', $block->body ?? '', $matches);
        $ids = [...$ids, ...$matches[1]];
        if ($block->type === 'practice-set') {
            $native = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            if (isset($native['m39_v1']['legacy_practice_id'])) { $ids[] = $native['m39_v1']['legacy_practice_id']; }
        }
    }
    $anchorSources[$name] = ['slug' => $slug, 'ids' => array_values(array_unique($ids))];
    $questions = $db->table('questions')->join('question_theory_text_blocks', 'questions.uuid', '=', 'question_theory_text_blocks.question_uuid')
        ->whereIn('text_block_uuid', $blocks->pluck('uuid'))->distinct()->orderBy('questions.id')
        ->get(['questions.id', 'questions.seeder', 'questions.type', 'questions.level']);
    $groups = $questions->groupBy(fn ($q) => $q->seeder.'|'.$q->type.'|'.$q->level);
    $globalLevels = []; $globalIds = [];
    foreach ($questions->pluck('seeder')->unique() as $seeder) {
        $globalLevels[$seeder] = $db->table('questions')->where('seeder', $seeder)->distinct()->pluck('level')->all();
        $globalIds[$seeder] = $db->table('questions')->where('seeder', $seeder)->orderBy('id')->pluck('id')->all();
    }
    $level = ['C2', 'C1', 'C2'][$i];
    $primary = $groups->filter(fn ($q) => (string) $q[0]->type === '4' && $q[0]->level === $level
        && count($globalLevels[$q[0]->seeder]) === 1);
    if ($primary->count() !== 1) { throw new RuntimeException('Missing/ambiguous exact M39 primary bank.'); }
    $q = $primary->first();
    $primaryIds = $q->pluck('id')->all();
    if ($primaryIds !== $globalIds[$q[0]->seeder]) { throw new RuntimeException('M39 primary linked bank is incomplete; no writes.'); }
    $pageModel = new App\Models\Page;
    $pageModel->setRawAttributes((array) $page, true);
    $categoryModel = new App\Models\PageCategory;
    $categoryModel->setRawAttributes((array) $category, true);
    $pageModel->setRelation('category', $categoryModel);
    $testSlug = App\Support\TheoryPageTestSlug::forPage($pageModel);
    if ($testSlug !== $categorySlug.'/'.$slug) { throw new RuntimeException('Actual primary test routing differs; no slug guessing.'); }
    $banks[$name] = ['seeder_class' => $q[0]->seeder, 'question_type' => (string) $q[0]->type, 'level' => $q[0]->level, 'question_ids' => $primaryIds,
        'question_count' => count($primaryIds), 'test_url' => 'http://gramlyze.loc/test/'.$testSlug];
    $targets[] = ['identity' => $name, 'slug' => $page->slug, 'page_id' => $page->id, 'category_id' => $page->page_category_id,
        'page_sha256' => hash('sha256', json_encode($page)),
        'category_slug' => $category->slug, 'category_title' => $category->title, 'category_sha256' => hash('sha256', json_encode($category)),
        'category_ancestry' => array_column($chain, 'slug'), 'category_chain_sha256' => hash('sha256', json_encode($chain)),
        'blocks' => $blocks->map(fn ($b) => ['id' => $b->id, 'uuid' => $b->uuid, 'type' => $b->type, 'locale' => $b->locale, 'order' => $b->sort_order, 'body_sha256' => hash('sha256', $b->body)])->all(),
        'linked_bank_groups' => $groups->map(fn ($q) => $q->count())->all(), 'global_bank_levels' => $globalLevels,
        'linked_bank_ids' => $questions->groupBy('seeder')->map(fn ($q) => $q->pluck('id')->all())->all()];
}
// Priority inventory may return before the larger protected-data snapshot; still SELECT-only.
if ($banksOnly) {
    $record = ['at' => gmdate('c'), 'root' => $root, 'driver' => 'mysql', 'host' => $db->getConfig('host'), 'server' => $physical['server'], 'port' => (int) $physical['port'], 'database' => $physical['db'],
        'targets' => $targets, 'linked_banks' => $banks, 'source_fidelity' => $sourceFidelity];
    $saved = $saveEvidence($record);
    $result = $saved === null ? $record : ['at' => $record['at'], 'evidence' => $saved,
        'banks' => array_map(fn ($b) => ['seeder' => $b['seeder_class'], 'type' => $b['question_type'], 'level' => $b['level'], 'count' => $b['question_count']], $banks)];
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    exit(0);
}
$regressions = [];
foreach (['m27-m11-linking-words.v2.json', 'm28-m12-emphasis-inversion.v2.json', 'm29-m13-sentence-structure.v2.json', 'm30-m14-participle-clauses.v1.json', 'm31-m15-conditionals.v1.json', 'm32-m16-formal-english.v1.json', 'm33-m17-academic-english.v1.json', 'm34-m18-argumentation-cohesion.v1.json', 'm35-m19-passive-reporting.v1.json', 'm36-m20-modals-subjunctive.v1.json', 'm37-m21-grammar-structures.v1.json', 'm38-m22-articles-collocations.v1.json'] as $file) {
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
// Existing, independently verified PPC work in ROOT predates M39. This finite read-only
// allowance protects its four exact practice bodies; it is not a migration or fallback.
$existingPpcPractice = [
    'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousFormsTheorySeeder' => '871081f4fcb18b028b916b0b7290deb80ff981947329aa1a4dc4f05136a87322',
    'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousNegativesTheorySeeder' => '76eca28e439f765e99eed5867b431a4308240ff59eda07e57dfdc22d2b4cecf7',
    'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousQuestionsTheorySeeder' => '353e27db2b12e0c4a54c0deb2dff4f21d2df0adf603806df638092717bc1092f',
    'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousTimeExpressionsTheorySeeder' => 'fe33f978698d67731605e081a2d8b5151fce665a64f54acdcb48c84d3aa04de5',
];
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
    $existingScope = [];
    foreach ($expected['blocks'] as $j => $b) {
        $uuid = App\Support\M26DetailPackage::uuid($t['identity'], $b, $j + 1);
        $row = $blocks->firstWhere('uuid', $uuid);
        $bodyMatches = $row && $row->body === $b['body'];
        if (!$bodyMatches && $row && !$isCategory && $j + 1 === 7 && $b['type'] === 'practice-set'
            && isset($existingPpcPractice[$t['identity']]) && hash('sha256', $row->body) === $existingPpcPractice[$t['identity']]) {
            $rootPath = $root.'/'.$t['definition_path'];
            if (is_link($rootPath) || !is_file($rootPath)) { throw new RuntimeException('Missing existing PPC canonical source.'); }
            $rootDefinition = json_decode(file_get_contents($rootPath), true, flags: JSON_THROW_ON_ERROR);
            $rootPractice = $rootDefinition[$t['source_content_root']]['blocks'][$j] ?? null;
            if ($rootPractice !== null && $rootPractice['type'] === 'practice-set' && $rootPractice['body'] === $row->body) {
                $bodyMatches = true;
                $existingScope[] = ['order'=>7,'type'=>'practice-set','body_sha256'=>$existingPpcPractice[$t['identity']],
                    'canonical_definition_sha256'=>hash_file('sha256',$rootPath),
                    'provenance'=>'existing_PPC_scope','prior_branch_commit'=>'cfbbfa57929b779a9ef315cb178f7d08ba46c9e4'];
            }
        }
        if (!$row || !$bodyMatches || $row->type !== $b['type'] || (int) $row->sort_order !== $j + 1
            || $row->locale !== 'uk' || $row->seeder !== $t['identity']) {
            throw new RuntimeException('M26 exact accepted body differs: '.$t['slug']);
        }
        foreach (['column','heading','level','css_class'] as $field) {
            if ($row->$field !== ($b[$field] ?? null)) { throw new RuntimeException('M26 protected block field differs: '.$field); }
        }
    }
    $regressions[] = ['package' => 'm26-master+interactive-v1', 'slug' => $t['slug'], 'owner_table' => $table,
        'owner_id' => $owner->id, 'owner_sha256' => hash('sha256', json_encode($owner)),
        'blocks_sha256' => hash('sha256', json_encode($blocks)), 'exact_full_author_payload' => $existingScope === [],
        'existing_PPC_scope' => $existingScope];
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
        if ($block['locale'] === 'uk' && $block['type'] === 'practice-set' && (int) $block['order'] === 8) { $changedIds[] = $block['id']; }
    }
}
$hash = hash_init('sha256'); $count = 0;
foreach ($db->table('text_blocks')->whereNotIn('id', $changedIds)->orderBy('id')->cursor() as $row) {
    hash_update($hash, App\Services\PronounContentRepair::digest((array) $row)."\n"); $count++;
}
$nonTargetBlocks = ['count' => $count, 'sha256' => hash_final($hash)];
$record = ['at' => gmdate('c'), 'target' => ['root' => $root, 'driver' => 'mysql', 'host' => $db->getConfig('host'), 'port' => $physical['port'], 'database' => $physical['db'], 'environment' => app()->environment()],
    'targets' => $targets, 'linked_banks' => $banks, 'source_fidelity' => $sourceFidelity, 'regressions' => $regressions, 'fingerprints' => $fingerprints, 'non_target_text_blocks' => $nonTargetBlocks,
    'anchor_reference_inventory' => ['sources' => $anchorSources, 'references' => $anchorReferences]];
$saved = $saveEvidence($record);
echo json_encode(['at' => $record['at'], 'evidence' => $saved, 'targets' => array_map(fn ($t) => ['slug' => $t['slug'], 'page_id' => $t['page_id'], 'blocks' => count($t['blocks'])], $targets),
    'banks' => array_map(fn ($b) => ['seeder' => $b['seeder_class'], 'type' => $b['question_type'], 'level' => $b['level'], 'count' => count($b['question_ids'])], $banks), 'regression_pages' => count($regressions), 'non_target_text_blocks' => $nonTargetBlocks, 'tables' => count($fingerprints)], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
