<?php

/** Explicit M51 read-only BEFORE. No seeding, apply, HTTP route or startup integration. */
$mode = $argv[1] ?? '';
$name = $argv[2] ?? '';
$patterns = ['capture' => '/^before-v[1-9][0-9]*\.json$/D',
    'question-inventory' => '/^question-inventory-v[1-9][0-9]*\.json$/D',
    'controller-capture' => '/^controller-before-v[1-9][0-9]*\.json$/D'];
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !isset($patterns[$mode]) || !preg_match($patterns[$mode], $name)) {
    throw new RuntimeException('M51 requires an explicit Windows CLI capture and private basename.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$worktree = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
$normal = static fn ($path) => strtolower(str_replace('\\', '/', (string) realpath($path)));
if ($normal(dirname(__DIR__, 2)) !== $normal($root)) { throw new RuntimeException('M51 must inspect the served ROOT.'); }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, 'M51: '.($error instanceof RuntimeException && !$error instanceof PDOException
        ? $error->getMessage() : get_class($error).' (read-only capture incomplete)')."\n");
    exit(1);
});
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)) {
    throw new RuntimeException('M51 application/environment root differs.');
}
$db = Illuminate\Support\Facades\DB::connection();
App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$guard = new class extends App\Services\M11LocalTargetGuard {
    public function inspect(): array { return $this->windowsEvidence(3306); }
};
$physical = $guard->inspect();
$directory = $root.'/storage/app/seo-m51-local';
if (is_link($directory) || (!is_dir($directory) && !mkdir($directory, 0700, true))) {
    throw new RuntimeException('M51 private evidence directory unavailable.');
}
if (file_exists($directory.'/'.$name)) { throw new RuntimeException('M51 BEFORE already exists; do not overwrite.'); }
$json = static fn ($value) => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$exclusive = static function ($path, $bytes): void {
    $handle = fopen($path, 'x');
    if (!$handle) { throw new RuntimeException('M51 exclusive evidence collision.'); }
    try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M51 evidence write incomplete.'); } }
    finally { fclose($handle); }
};
$digest = static fn ($value) => App\Services\M26ContentPatch::digest($value);
$rows = static fn ($query) => $query->get()->map(static fn ($row) => (array) $row)->all();
$bindings = [
    ['basename' => 'ModalVerbsDeductionTheorySeeder', 'slug' => 'modals-of-deduction', 'level' => 'B1',
        'banks' => ['PolyglotModalsOfDeductionAllLevelsLessonSeeder', 'PolyglotModalVerbsOfDeductionLessonSeeder']],
    ['basename' => 'ModalVerbsPastModalsTheorySeeder', 'slug' => 'past-modals', 'level' => 'B1',
        'banks' => ['PolyglotModalVerbsPastModalsAllLevelsLessonSeeder']],
    ['basename' => 'ModalVerbsRequestsOffersSuggestionsTheorySeeder', 'slug' => 'requests-offers-suggestions', 'level' => 'A2',
        'banks' => ['PolyglotModalVerbsRequestsOffersSuggestionsAllLevelsLessonSeeder']],
];
$capture = ['schema_version' => 1, 'base_sha' => '7b0ca49b3cbfd7135b80f6931cdd42834eb24c53',
    'started_at' => gmdate('c'), 'served_root' => $root, 'source_worktree' => $worktree,
    'physical' => $physical, 'physical_hashes' => [], 'pages' => [], 'definitions' => [], 'banks' => [], 'http' => []];
foreach (['C:/Program Files/xampp/apache/conf/httpd.conf', 'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
    'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', $root.'/public/index.php'] as $path) {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('M51 physical inventory missing/linked.'); }
    $capture['physical_hashes'][$path] = hash_file('sha256', $path);
}
$fingerprints = static function () use ($db, $digest): array {
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false); sort($tables);
    $out = [];
    foreach ($tables as $table) {
        $columns = $db->getSchemaBuilder()->getColumnListing($table); $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $hash = hash_init('sha256'); $count = 0;
        foreach ($query->cursor() as $record) { hash_update($hash, $digest((array) $record)."\n"); $count++; }
        $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
    }
    return $out;
};
if ($mode === 'controller-capture') {
    $path = 'app/Http/Controllers/TestJsV2Controller.php';
    $rootBytes = file_get_contents($root.'/'.$path); $wtBytes = file_get_contents($worktree.'/'.$path);
    if (str_contains($rootBytes, 'M51ModalMeaningsPackage') || str_contains($wtBytes, 'M51ModalMeaningsPackage')) {
        throw new RuntimeException('M51 controller capture must precede owned insertions.');
    }
    $prefix = substr($name, 0, -5);
    $exclusive($directory.'/'.$prefix.'-root.bin', $rootBytes);
    $exclusive($directory.'/'.$prefix.'-worktree.bin', $wtBytes);
    $record = ['schema_version' => 1, 'base_sha' => $capture['base_sha'], 'path' => $path,
        'root_file' => $prefix.'-root.bin', 'root_sha256' => hash('sha256', $rootBytes),
        'worktree_file' => $prefix.'-worktree.bin', 'worktree_sha256' => hash('sha256', $wtBytes),
        'worktree_base_lf_sha256' => hash('sha256', str_replace("\r\n", "\n", $wtBytes)), 'at' => gmdate('c')];
    $exclusive($directory.'/'.$name, $json($record));
    echo $json($record + ['receipt_sha256' => hash_file('sha256', $directory.'/'.$name)]); exit(0);
}
if ($mode === 'question-inventory') {
    $draftPath = $worktree.'/docs/content/m51-modal-meanings-widget-questions.v1.0.0.json';
    $draftBytes = file_get_contents($draftPath);
    if (hash('sha256', $draftBytes) !== '9a8fd25f5e4f9c952a438aed2951dafbdb26551e52babf71aaeab2c09cd0a1e0') {
        throw new RuntimeException('M51 reviewed question draft changed.');
    }
    $draft = json_decode($draftBytes, true, flags: JSON_THROW_ON_ERROR);
    $capture['draft_sha256'] = hash('sha256', $draftBytes);
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try {
        $server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@hostname AS server, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
        $pid = trim((string) file_get_contents($server['pid_file']));
        if ($server['db'] !== 'gr2' || (int) $server['port'] !== 3306 || !str_starts_with($server['os'], 'Win')
            || !ctype_digit($pid) || !str_starts_with($normal($server['pid_file']), $normal($server['datadir']).'/')
            || strcasecmp($server['server'], gethostname()) !== 0) { throw new RuntimeException('M51 physical MySQL identity differs.'); }
        $guard->assertEvidence($physical, $normal($root), (int) $pid, 3306);
        $capture['identity'] = App\Services\M49LocalTargetGuard::publicIdentity($db, $root.'/public');
        $capture['sql_before'] = $fingerprints();
        $capture['categories'] = $rows($db->table('categories')->whereIn('id', [45, 70])->orderBy('id'));
        $capture['sources'] = $rows($db->table('sources')->whereIn('id', [198, 204, 397])->orderBy('id'));
        $capture['option_matches'] = [];
        $tokens = [];
        foreach ($draft['banks'] as $bank) {
            if ($db->table('questions')->where('seeder', $bank['bank_seeder'])->exists()) {
                throw new RuntimeException('M51 new bank already exists.');
            }
            foreach ($bank['questions'] as $question) {
                if ($db->table('questions')->where('uuid', $question['source_uid'])->exists()) {
                    throw new RuntimeException('M51 new question UUID already exists.');
                }
                foreach ($question['tokens_correct'] as $token) { $tokens[$token] = true; }
            }
        }
        ksort($tokens);
        foreach (array_keys($tokens) as $token) {
            $matches = $rows($db->table('question_options')->where('option', $token)->orderBy('id'));
            $capture['option_matches'][$token] = ['requested' => $token, 'rows' => $matches];
        }
        foreach (['questions', 'question_options', 'question_answers', 'question_option_question', 'question_theory_text_blocks'] as $table) {
            $capture['allocation'][$table] = ['max_id' => $db->table($table)->max('id'),
                'schema' => $db->getSchemaBuilder()->getColumns($table),
                'indexes' => $db->getSchemaBuilder()->getIndexes($table),
                'auto_increment' => (array) $db->selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$table])];
        }
        $capture['sql_after_inventory'] = $fingerprints();
        if ($capture['sql_before'] !== $capture['sql_after_inventory']) { throw new RuntimeException('M51 read-only inventory changed SQL.'); }
    } finally { $db->rollBack(); }
    $capture['finished_at'] = gmdate('c'); $exclusive($directory.'/'.$name, $json($capture));
    echo $json(['status' => 'read-only-question-inventory', 'file' => $name, 'sha256' => hash_file('sha256', $directory.'/'.$name),
        'categories' => $capture['categories'], 'sources' => $capture['sources'], 'unique_token_spellings' => count($tokens),
        'existing_matches' => count(array_filter($capture['option_matches'], static fn ($match) => $match['rows'] !== [])),
        'allocation' => array_map(static fn ($value) => ['max_id' => $value['max_id'], 'next_id' => $value['auto_increment']['next_id']], $capture['allocation'])]);
    exit(0);
}
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@hostname AS server, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
    $pid = trim((string) file_get_contents($server['pid_file']));
    if ($server['db'] !== 'gr2' || (int) $server['port'] !== 3306 || !str_starts_with($server['os'], 'Win')
        || !ctype_digit($pid) || !str_starts_with($normal($server['pid_file']), $normal($server['datadir']).'/')
        || strcasecmp($server['server'], gethostname()) !== 0) { throw new RuntimeException('M51 physical MySQL identity differs.'); }
    $guard->assertEvidence($physical, $normal($root), (int) $pid, 3306);
    $capture['identity'] = App\Services\M49LocalTargetGuard::publicIdentity($db, $root.'/public');
    $capture['body_capacity'] = (array) $db->selectOne("SELECT DATA_TYPE AS kind, CHARACTER_OCTET_LENGTH AS max_bytes FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='text_blocks' AND COLUMN_NAME='body'");
    foreach ($bindings as $binding) {
        $owner = 'Database\\Seeders\\Page_V3\\ModalVerbs\\'.$binding['basename'];
        $path = 'database/seeders/Page_V3/ModalVerbs/'.$binding['basename'].'/definition.json';
        $bytes = file_get_contents($root.'/'.$path);
        if ($bytes !== file_get_contents($worktree.'/'.$path)) { throw new RuntimeException('M51 BEFORE ROOT/WT definition bytes differ.'); }
        $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if ($definition['seeder']['class'] !== $owner || $definition['slug'] !== $binding['slug']
            || $definition['page']['locale'] !== 'uk' || $definition['type'] !== 'theory'
            || $definition['page']['category']['slug'] !== 'modal-verbs' || count($definition['page']['blocks']) !== 6) {
            throw new RuntimeException('M51 source owner/locale/category/slots differ.');
        }
        $pages = $rows($db->table('pages')->where('seeder', $owner)->orderBy('id'));
        if (count($pages) !== 1) { throw new RuntimeException('M51 target page missing/ambiguous.'); }
        $page = $pages[0]; $category = (array) $db->table('page_categories')->where('id', $page['page_category_id'])->first();
        if ($page['slug'] !== $binding['slug'] || $page['title'] !== $definition['page']['title'] || $page['type'] !== 'theory'
            || $page['text'] !== $definition['page']['subtitle_text'] || ($category['slug'] ?? null) !== 'modal-verbs'
            || !empty($category['parent_id'])) { throw new RuntimeException('M51 Page/category BEFORE binding differs.'); }
        $blocks = $rows($db->table('text_blocks')->where('page_id', $page['id'])->orderBy('id'));
        $uk = array_values(array_filter($blocks, static fn ($block) => $block['locale'] === 'uk'));
        usort($uk, static fn ($a, $b) => $a['sort_order'] <=> $b['sort_order']);
        if (count($uk) !== 7) { throw new RuntimeException('M51 UK continuing row count differs.'); }
        $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'],
            'level' => $definition['page']['subtitle_level'] ?? null, 'uuid_key' => $definition['page']['subtitle_uuid_key'] ?? 'subtitle'], ...$definition['page']['blocks']];
        foreach ($configs as $index => $config) {
            $row = $uk[$index];
            if ($row['seeder'] !== $owner || $row['page_category_id'] !== $page['page_category_id'] || (int) $row['sort_order'] !== $index
                || $row['uuid'] !== App\Support\M26DetailPackage::uuid($owner, $config, $index)) {
                throw new RuntimeException('M51 continuing row identity differs.');
            }
            foreach (['type', 'column', 'heading', 'level', 'css_class', 'body'] as $field) {
                if ($row[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('M51 continuing row/source field differs: '.$field); }
            }
        }
        $links = $rows($db->table('question_theory_text_blocks')->whereIn('text_block_uuid', array_column($blocks, 'uuid'))->orderBy('id'));
        $capture['definitions'][$owner] = ['path' => $path, 'sha256' => hash('sha256', $bytes), 'bytes' => $bytes, 'definition' => $definition];
        $capture['pages'][$owner] = ['page' => $page, 'category_ancestry' => [$category], 'blocks' => $blocks, 'links' => $links];
        foreach ($binding['banks'] as $basename) {
            $bank = 'Database\\Seeders\\V3\\Polyglot\\'.$basename;
            $questions = $rows($db->table('questions')->where('seeder', $bank)->where('type', '4')->where('level', $binding['level'])
                ->whereIn('uuid', array_column($links, 'question_uuid'))->orderBy('uuid'));
            $capture['banks'][$bank] = ['identity' => $bank, 'level' => $binding['level'], 'eligible_count' => count($questions), 'first_five' => []];
            foreach (array_slice($questions, 0, 5) as $question) {
                $snapshot = ['question' => $question];
                foreach (['answers' => 'question_answers', 'option_pivots' => 'question_option_question', 'verb_hints' => 'verb_hints',
                    'hints' => 'question_hints', 'variants' => 'question_variants'] as $key => $table) {
                    $snapshot[$key] = $rows($db->table($table)->where('question_id', $question['id'])->orderBy('id'));
                }
                $optionIds = array_values(array_unique([...array_column($snapshot['answers'], 'option_id'),
                    ...array_column($snapshot['option_pivots'], 'option_id'), ...array_column($snapshot['verb_hints'], 'option_id')]));
                $snapshot['options'] = $rows($db->table('question_options')->whereIn('id', $optionIds)->orderBy('id'));
                $capture['banks'][$bank]['first_five'][] = $snapshot;
            }
        }
    }
    foreach (['questions', 'question_options', 'question_answers', 'question_option_question', 'question_hints', 'verb_hints',
        'question_variants', 'question_theory_text_blocks', 'sources', 'categories'] as $table) {
        $capture['schema'][$table] = $db->getSchemaBuilder()->getColumns($table);
    }
    $capture['sql_before'] = $fingerprints();
} finally { $db->rollBack(); }
$inventory = static function ($folders, $exclude) use ($root): array {
    $out = [];
    foreach ($folders as $folder) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$folder, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isLink()) { throw new RuntimeException('M51 inventory symlink refused.'); }
            if (!$file->isFile()) { continue; }
            $path = substr(str_replace('\\', '/', $file->getPathname()), strlen($root) + 1);
            if (!$exclude($path)) { $out[$path] = hash_file('sha256', $file->getPathname()); }
        }
    }
    ksort($out); return $out;
};
$capture['visual_hashes'] = $inventory(['resources/views', 'resources/css', 'resources/js', 'public/js'], static fn () => false);
$targetPaths = array_column($capture['definitions'], 'path');
$capture['frozen_hashes'] = $inventory(['docs/content', 'docs/reports', 'database/content-patches', 'database/seeders/Page_V3'],
    static fn ($path) => preg_match('~(?:^|/)m51[-./]~i', $path) || str_contains($path, '/seo-m51-') || in_array($path, $targetPaths, true));
foreach (['app/Support/M*.php', 'app/Services/M*.php', 'database/seeders/V3/Polyglot/*/definition.json', 'database/seeders/V3/TheoryLinks/data/*.json'] as $pattern) {
    foreach (glob($root.'/'.$pattern) ?: [] as $file) {
        if (str_starts_with(basename($file), 'M51')) { continue; }
        $capture['frozen_hashes'][substr(str_replace('\\', '/', $file), strlen($root) + 1)] = hash_file('sha256', $file);
    }
}
ksort($capture['frozen_hashes']);
foreach ($bindings as $binding) {
    foreach (['/theory/modal-verbs/'.$binding['slug'], '/courses/english-grammar-theory/lesson/modal-verbs/'.$binding['slug']] as $path) {
        $curl = curl_init('http://gramlyze.loc'.$path);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 60,
            CURLOPT_PROXY => '', CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1']]);
        try {
            $html = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
            if (!is_string($html) || $ip !== '127.0.0.1' || $status !== 200) { throw new RuntimeException('M51 exact local BEFORE GET unavailable: '.$path); }
        } finally { curl_close($curl); }
        $dom = new DOMDocument('1.0', 'UTF-8'); $oldErrors = libxml_use_internal_errors(true);
        try { $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET); } finally { libxml_clear_errors(); libxml_use_internal_errors($oldErrors); }
        $xpath = new DOMXPath($dom); $h1 = trim($xpath->query('//h1')->item(0)?->textContent ?? '');
        $bindingPage = $capture['pages']['Database\\Seeders\\Page_V3\\ModalVerbs\\'.$binding['basename']]['page'];
        if ($h1 !== $bindingPage['title']) { throw new RuntimeException('M51 BEFORE rendered title differs.'); }
        $file = substr($name, 0, -5).'-http-'.hash('sha256', $path).'.html'; $exclusive($directory.'/'.$file, $html);
        $capture['http'][] = ['url' => 'http://gramlyze.loc'.$path, 'status' => $status, 'h1' => $h1, 'file' => $file, 'sha256' => hash('sha256', $html)];
    }
}
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try { $capture['sql_after_http'] = $fingerprints(); } finally { $db->rollBack(); }
if ($capture['sql_before'] !== $capture['sql_after_http']) { throw new RuntimeException('M51 SQL changed during BEFORE GETs.'); }
$capture['finished_at'] = gmdate('c'); $exclusive($directory.'/'.$name, $json($capture));
echo $json(['status' => 'read-only-before', 'file' => $name, 'sha256' => hash_file('sha256', $directory.'/'.$name),
    'http_200' => count($capture['http']), 'sql_tables' => count($capture['sql_before']), 'visual_files' => count($capture['visual_hashes']),
    'frozen_files' => count($capture['frozen_hashes']), 'pages' => array_map(static fn ($value) => [
        'id' => $value['page']['id'], 'category_id' => $value['page']['page_category_id'], 'slug' => $value['page']['slug'], 'rows' => count($value['blocks'])], $capture['pages']),
    'bank_pools' => array_map(static fn ($value) => ['identity' => $value['identity'], 'level' => $value['level'], 'eligible_count' => $value['eligible_count']], $capture['banks'])]);
