<?php

// SELECT-only evidence for the physical working .loc; never returns credentials.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $source = dirname(__DIR__, 2);
$arguments = array_slice($argv, 1); $banksOnly = false; $outputName = null;
if (($arguments[0] ?? '') === '--banks-only') { $banksOnly = true; array_shift($arguments); }
if ($arguments !== []) {
    if (count($arguments) !== 2 || $arguments[0] !== '--output'
        || !preg_match('/^m40-(?:before|after|after-noop|bank-inventory)-v[1-9][0-9]*\.json$/D', $arguments[1])) {
        throw new RuntimeException('Use --banks-only optionally followed by --output and an exact private M40 evidence basename.');
    }
    $outputName = $arguments[1];
    if ($banksOnly !== str_starts_with($outputName, 'm40-bank-inventory-')) {
        throw new RuntimeException('M40 evidence basename does not match the inventory mode.');
    }
}
$saveEvidence = static function (array $record) use ($root, $outputName): ?array {
    if ($outputName === null) { return null; }
    $directory = $root.'/storage/app/seo-m40-local';
    if (is_link($directory)) { throw new RuntimeException('Private evidence directory cannot be a link.'); }
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) { throw new RuntimeException('Cannot create private M40 evidence directory.'); }
    $path = $directory.'/'.$outputName;
    $bytes = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    $file = fopen($path, 'xb');
    if (!$file) { throw new RuntimeException('Private M40 evidence already exists; no overwrite.'); }
    try { if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('Incomplete M40 evidence.'); } }
    finally { fclose($file); }
    return ['path' => $path, 'sha256' => hash('sha256', $bytes)];
};
require $root.'/vendor/autoload.php';
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
$masterPath = $source.'/docs/content/m24-authored-content.v1.json';
$masterBytes = file_get_contents($masterPath);
$masterLf = str_replace("\r\n", "\n", $masterBytes);
if (hash('sha256', $masterLf) !== '33373412ed077bf7fa0aea8dde8a6d2c211c126288b6cedfdde299ded43bf5a2'
    || hash('sha1', 'blob '.strlen($masterLf)."\0".$masterLf) !== '2d7c450533795bdab3267bd029b54fb325ff7cbb') {
    throw new RuntimeException('Immutable M24 master fingerprint differs; STOP before any source edits or writes.');
}
$authorMaster = json_decode($masterBytes, true, flags: JSON_THROW_ON_ERROR);
$notesBytes=file_get_contents($source.'/docs/content/m24-author-sources.md');$notesLf=str_replace("\r\n","\n",$notesBytes);
if(hash('sha1','blob '.strlen($notesLf)."\0".$notesLf)!=='3330b22b22e401eec9c36275651e60645b4b87f6')throw new RuntimeException('M40 frozen author notes differ.');
foreach(['rewrite','summarise','translate_again','add_examples','add_exercises','production_write','mixed_question_bank_write'] as $policy)
    if(($authorMaster['content_policy'][$policy]??null)!==false)throw new RuntimeException('M40 frozen author policy differs.');
$targets = []; $banks = []; $anchorSources = []; $sourceFidelity = [];
// Exact finite identity => actual root-to-leaf ancestry; no slug-derived bank guesses.
$definitions=array_map(fn($lesson)=>[$lesson['seeder'],$lesson['slug'],$lesson['category_path']],$authorMaster['lessons']);
foreach ($definitions as $i => [$name, $slug, $expectedAncestry]) {
    $page = $db->table('pages')->where('seeder', $name)->sole();
    $category = $db->table('page_categories')->where('id', $page->page_category_id)->sole();
    $chain = []; $seen = []; $node = $category;
    while ($node !== null) {
        if (isset($seen[$node->id]) || count($chain) >= 8 || $node->language !== 'uk' || $node->type !== 'theory') {
            throw new RuntimeException('Invalid existing M40 category ancestry.');
        }
        $seen[$node->id] = true; array_unshift($chain, (array) $node);
        $node = $node->parent_id === null ? null : $db->table('page_categories')->where('id', $node->parent_id)->sole();
    }
    $categorySlug = $category->slug;
    if ($page->slug !== $slug || $page->type !== 'theory' || array_column($chain, 'slug') !== $expectedAncestry) {
        throw new RuntimeException('Unexpected M40 owner/category identity.');
    }
    $blocks = $db->table('text_blocks')->where('page_id', $page->id)->orderBy('id')->get();
    $author=$authorMaster['lessons'][$i];
    if($author['seeder']!==$name||$author['slug']!==$slug||$author['category_path']!==$expectedAncestry
        ||$page->title!==$author['preserve_page_title']||$page->text!==$author['subtitle_text'])throw new RuntimeException('M40 immutable owner/subtitle conflict.');
    $uk=$blocks->where('locale','uk')->sortBy('sort_order')->values();
    $raw=file_get_contents($source.'/'.$author['definition_path']);
    $definition=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);
    $acceptedBlobs=['e4a950880b6f4fc0e1b865edc886c658805ffd3c','55317a6a7bb48a640e804ec613afb83bdca05373','8b6422baea0cf0c9f4ec1ca1ed921b9fab91c53d'];
    $lf=str_replace("\r\n","\n",$raw);
    if(hash('sha1','blob '.strlen($lf)."\0".$lf)!==$acceptedBlobs[$i]){
        $beforePath=$source.'/database/content-patches/m40-m24-tenses-b1-before.json';
        if(!is_file($beforePath))throw new RuntimeException('M40 canonical differs without a frozen before manifest.');
        $before=json_decode(file_get_contents($beforePath),true,flags:JSON_THROW_ON_ERROR);
        $matches=array_values(array_filter($before['targets'],fn($target)=>($target['before']['seeder']['class']??null)===$name));
        if(count($matches)!==1||($matches[0]['source_git_blob']??null)!==$acceptedBlobs[$i])throw new RuntimeException('M40 accepted definition identity differs.');
        $definition=$matches[0]['before'];
    }
    if($definition['page']['subtitle_html']!==$author['subtitle_html']||$definition['page']['subtitle_text']!==$author['subtitle_text'])
        throw new RuntimeException('M40 master/accepted subtitle differs.');
    foreach($author['existing_blocks'] as $native){
        $config=$definition['page']['blocks'][$native['source_index']];
        if($config['type']!==$native['preserve_type']||$config['column']!==$native['preserve_column']||$config['level']!==$native['preserve_level']
            ||json_decode($config['body'],true,flags:JSON_THROW_ON_ERROR)!==$native['replacement_body_json'])throw new RuntimeException('M40 master native content differs.');
    }
    $oldPosition=count($definition['page']['blocks']);$last=$definition['page']['blocks'][$oldPosition-1];$append=$author['append_blocks'][0];
    if($last['body']!==$append['body_html']||hash('sha256',$last['body'])!==$append['body_sha256'])throw new RuntimeException('M40 master last author box differs.');
    if($uk[0]->body!==$author['subtitle_html']||$uk[0]->type!=='subtitle')throw new RuntimeException('M40 actual subtitle differs.');
    foreach(array_slice($definition['page']['blocks'],0,-1) as $j=>$config){
        $uuid=App\Support\M26DetailPackage::uuid($name,$config,$j+1);$row=$uk->firstWhere('uuid',$uuid);
        if(!$row||$row->body!==$config['body']||$row->type!==$config['type']||$row->column!==$config['column']||$row->level!==($config['level']??null)
            ||$row->heading!==($config['heading']??null)||$row->css_class!==($config['css_class']??null)||(int)$row->sort_order!==$j+1)
            throw new RuntimeException('M40 actual accepted native block differs.');
    }
    $oldUuid=App\Support\M26DetailPackage::uuid($name,$last,$oldPosition);$lastRow=$uk->firstWhere('uuid',$oldUuid);
    if(!$lastRow)throw new RuntimeException('M40 original last box identity missing.');
    if($lastRow->body===$last['body']&&$lastRow->type===$last['type']){
        if($uk->count()!==$oldPosition+1)throw new RuntimeException('M40 unexpected before UK rows.');
        $sourceFidelity[$name]=['state'=>'accepted_author_before','master_definition_db_exact'=>true,'body_sha256'=>hash('sha256',$lastRow->body),
            'box_id'=>$lastRow->id,'box_uuid'=>$lastRow->uuid,'box_order'=>$oldPosition,'existing_native_count'=>count($author['existing_blocks'])];
    }else{
        $projection=json_decode(file_get_contents($source.'/database/content-patches/m40-m24-tenses-b1.v1.json'),true,flags:JSON_THROW_ON_ERROR);
        $projectionTargets=array_values(array_filter($projection['targets'],fn($target)=>$target['identity']===$name));
        if(count($projectionTargets)!==1||$uk->count()!==count($projectionTargets[0]['after']['page']['blocks'])+1)throw new RuntimeException('M40 unexpected/partial after state.');
        foreach($projectionTargets[0]['after']['page']['blocks'] as $j=>$config){
            $uuid=App\Support\M26DetailPackage::uuid($name,$config,$j+1);$row=$uk->firstWhere('uuid',$uuid);
            if(!$row||$row->body!==$config['body']||$row->type!==$config['type']||$row->column!==$config['column']||$row->level!==($config['level']??null)
                ||$row->heading!==($config['heading']??null)||$row->css_class!==($config['css_class']??null)||(int)$row->sort_order!==$j+1)
                throw new RuntimeException('M40 after source/actual block differs.');
        }
        $sourceFidelity[$name]=['state'=>'native_after','source_exact'=>true,'box_id'=>$lastRow->id,'box_uuid'=>$lastRow->uuid,'box_order'=>$oldPosition,
            'existing_native_count'=>count($author['existing_blocks'])];
    }
    $ids = [];
    foreach ($blocks as $block) {
        preg_match_all('/\bid\s*=\s*["\']([^"\']+)["\']/i', $block->body ?? '', $matches);
        $ids = [...$ids, ...$matches[1]];
        if ($block->type === 'practice-set') {
            $native = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            if (isset($native['m40_v1']['legacy_practice_id'])) { $ids[] = $native['m40_v1']['legacy_practice_id']; }
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
    $level = ['B1', 'B1', 'B1'][$i];
    $primary = $groups->filter(fn ($q) => (string) $q[0]->type === '4' && $q[0]->level === $level
        && count($globalLevels[$q[0]->seeder]) === 1);
    if ($primary->count() !== 1) { throw new RuntimeException('Missing/ambiguous exact M40 primary bank.'); }
    $q = $primary->first();
    $primaryIds = $q->pluck('id')->all();
    if ($primaryIds !== $globalIds[$q[0]->seeder]) { throw new RuntimeException('M40 primary linked bank is incomplete; no writes.'); }
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

$m39=json_decode(file_get_contents($source.'/database/content-patches/m39-practice-ui.v1.json'),true,flags:JSON_THROW_ON_ERROR);
if(hash_file('sha256',$source.'/database/content-patches/m39-practice-ui.v1.json')!=='a155e4d53a37ced5bffd7a427305748a68c9eb6f8c8ee33c6b2b0d6e0d589e5f')
    throw new RuntimeException('Accepted M39 UI quality baseline differs.');
foreach($m39['targets'] as $t){
    $page=$db->table('pages')->where('seeder',$t['identity'])->sole();
    $rows=$db->table('text_blocks')->where('page_id',$page->id)->orderBy('id')->get();
    if($page->title!==$t['after']['page']['title']||$page->text!==$t['after']['page']['subtitle_text']||$rows->where('locale','uk')->count()!==count($t['after']['page']['blocks'])+1)
        throw new RuntimeException('M39 final UI owner metadata differs.');
    foreach($t['after']['page']['blocks'] as $j=>$config){
        $uuid=App\Support\M26DetailPackage::uuid($t['identity'],$config,$j+1);$r=$rows->firstWhere('uuid',$uuid);
        if(!$r||$r->body!==$config['body']||$r->type!==$config['type']||(int)$r->sort_order!==$j+1||$r->locale!=='uk'||$r->seeder!==$t['identity'])
            throw new RuntimeException('M39 final UI body differs.');
    }
    $regressions[]=['package'=>'m39-practice-ui.v1.json','slug'=>$t['after']['slug'],'page_id'=>$page->id,
        'page_sha256'=>hash('sha256',json_encode($page)),'blocks_sha256'=>hash('sha256',json_encode($rows)),'exact_full_author_payload'=>true];
}

[$m26Master, $m26Before] = App\Support\M26DetailPackage::load($source);
// Existing, independently verified PPC work in ROOT predates M40. This finite read-only
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
$progressTables=array_values(array_filter($db->getSchemaBuilder()->getTableListing(schema:$physical['db'],schemaQualified:false),fn($table)=>preg_match('/(?:progress|attempt|review|result|state)/i',$table)));
sort($progressTables);
$allAnchorIds = array_merge(...array_column($anchorSources, 'ids'));
$targetSlugs = array_column($anchorSources, 'slug');
foreach (['pages', 'page_categories', 'text_blocks', 'tags', 'page_tag', 'page_category_tag', 'tag_text_block', 'questions', 'question_answers', 'question_options', 'question_option_question', 'question_theory_text_blocks', 'question_tag', 'question_marker_tag', 'verb_hints', 'question_hints', 'question_variants', 'saved_grammar_tests', 'saved_grammar_test_questions', 'seed_runs', ...$progressTables] as $table) {
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
        $firstChangedOrder=$sourceFidelity[$target['identity']]['box_order'];
        if ($block['locale'] === 'uk' && (int)$block['order'] >= $firstChangedOrder) { $changedIds[] = $block['id']; }
    }
}
$hash = hash_init('sha256'); $count = 0;
foreach ($db->table('text_blocks')->whereNotIn('id', $changedIds)->orderBy('id')->cursor() as $row) {
    hash_update($hash, App\Services\PronounContentRepair::digest((array) $row)."\n"); $count++;
}
$nonTargetBlocks = ['count' => $count, 'sha256' => hash_final($hash)];
$record = ['at' => gmdate('c'), 'target' => ['root' => $root, 'driver' => 'mysql', 'host' => $db->getConfig('host'), 'port' => $physical['port'], 'database' => $physical['db'], 'environment' => app()->environment()],
    'targets' => $targets, 'linked_banks' => $banks, 'source_fidelity' => $sourceFidelity, 'regressions' => $regressions, 'fingerprints' => $fingerprints, 'non_target_text_blocks' => $nonTargetBlocks,
    'progress_tables' => $progressTables, 'anchor_reference_inventory' => ['sources' => $anchorSources, 'references' => $anchorReferences]];
$saved = $saveEvidence($record);
echo json_encode(['at' => $record['at'], 'evidence' => $saved, 'targets' => array_map(fn ($t) => ['slug' => $t['slug'], 'page_id' => $t['page_id'], 'blocks' => count($t['blocks'])], $targets),
    'banks' => array_map(fn ($b) => ['seeder' => $b['seeder_class'], 'type' => $b['question_type'], 'level' => $b['level'], 'count' => count($b['question_ids'])], $banks), 'regression_pages' => count($regressions), 'non_target_text_blocks' => $nonTargetBlocks, 'tables' => count($fingerprints)], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
