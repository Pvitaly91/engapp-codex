<?php

/** M50 explicit diagnostics: guarded SELECT/ROLLBACK, loopback GET and exclusive private evidence; never apply or copy source. */
$mode = $argv[1] ?? '';
$name = $argv[2] ?? '';
$patterns = ['capture' => '/^before-v[1-9][0-9]*\.json$/D', 'expectations' => '/^metadata-expected-v[1-9][0-9]*\.json$/D',
    'links-capture' => '/^m50-links-before-v[1-9][0-9]*\.json$/D',
    'consumers-capture' => '/^bank-consumers-before-v[1-9][0-9]*\.json$/D',
    'review' => '/^preview-v[1-9][0-9]*\.json$/D', 'audit' => '/^after-v[1-9][0-9]*\.json$/D',
    'cleanup' => '/^local-proof-[a-f0-9]{32}\.json$/D'];
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows'
    || (!isset($patterns[$mode]) && !in_array($mode, ['sync', 'proof'], true))
    || (isset($patterns[$mode]) && !preg_match($patterns[$mode], $name))) {
    throw new RuntimeException('M50 requires an explicit diagnostic mode and scoped private evidence basename.');
}

$root = 'D:/DEV/htdocs/gramlyze.loc';
$worktree = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
$normal = static fn (string $path): string => strtolower(str_replace('\\', '/', (string) realpath($path)));
if ($normal(dirname(__DIR__, 2)) !== $normal($root)) {
    throw new RuntimeException('M50 BEFORE must run from the actual served ROOT.');
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $error): void {
    $message = $error instanceof RuntimeException && !$error instanceof PDOException
        ? $error->getMessage() : get_class($error).' (diagnostic incomplete; no successful receipt)';
    fwrite(STDERR, 'M50: '.$message."\n"); exit(1);
});
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)) {
    throw new RuntimeException('M50 configured application/environment ROOT differs.');
}
$db = Illuminate\Support\Facades\DB::connection();
// Existing pure policy is reused before PDO: one exact loopback MySQL gr2:3306, no split/socket/URL.
App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$dir = $root.'/storage/app/seo-m50-local';
if (is_link($dir) || (!is_dir($dir) && !mkdir($dir, 0700, true))) {
    throw new RuntimeException('M50 private evidence directory unavailable.');
}
if (in_array($mode, ['capture', 'links-capture', 'consumers-capture', 'expectations', 'audit'], true) && file_exists($dir.'/'.$name)) {
    throw new RuntimeException('M50 evidence already exists; no overwrite.');
}
$json = static fn ($value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$exclusive = static function (string $path, string $bytes): void {
    $handle = fopen($path, 'x');
    if (!$handle) { throw new RuntimeException('M50 evidence collision.'); }
    try {
        if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M50 evidence write incomplete.'); }
    } finally { fclose($handle); }
};
$rows = static fn ($query): array => $query->get()->map(static fn ($row) => (array) $row)->all();
$htmlAudit = static function (string $html, bool $includeContentDom = false): array {
    $dom = new DOMDocument('1.0', 'UTF-8'); $old = libxml_use_internal_errors(true);
    try { $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET); }
    finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
    $xpath = new DOMXPath($dom); $get = static fn ($query) => $xpath->query($query)->item(0)?->nodeValue ?? '';
    $meta = static fn ($key) => $get('//meta[@name="'.$key.'" or @property="'.$key.'"]/@content');
    $structured = []; foreach ($xpath->query('//script[@type="application/ld+json"]') as $node) { $structured[] = json_decode($node->textContent, true); }
    $anchors = []; foreach ($xpath->query('//*[@id]') as $node) { $anchors[] = $node->getAttribute('id'); }
    $contentNodes = $xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-design ")]');
    $content = $contentNodes->item(0);
    $result = ['title' => $get('//title'), 'h1' => $get('//h1'), 'description' => $meta('description'), 'og_description' => $meta('og:description'),
        'twitter_description' => $meta('twitter:description'), 'robots' => $meta('robots'), 'canonical' => $get('//link[@rel="canonical"]/@href'),
        'structured' => $structured, 'anchors' => $anchors, 'practice_models' => substr_count($html, 'x-data="theoryPracticeSet('),
        'sentence_builders' => $xpath->query('//*[@data-sentence-builder]')->length, 'content_text' => $content?->textContent ?? ''];
    if ($includeContentDom) {
        $result['content_node_count'] = $contentNodes->length;
        $result['content_dom'] = $content ? $dom->saveHTML($content) : '';
    }
    return $result;
};
$getLocal = static function (string $path): array {
    if (!preg_match('~^/(?:theory/modal-verbs/|courses/english-grammar-theory/lesson/modal-verbs/|test/polyglot-|api/_local/m50-target-)[a-z0-9/-]+$~D', $path)) {
        throw new RuntimeException('M50 diagnostic URL outside finite local path families.');
    }
    $robots = ''; $curl = curl_init('http://gramlyze.loc'.$path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 60,
        CURLOPT_PROXY => '', CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'],
        CURLOPT_HEADERFUNCTION => static function ($handle, $header) use (&$robots) {
            if (stripos($header, 'X-Robots-Tag:') === 0) { $robots = trim(substr($header, 13)); }
            return strlen($header);
        }]);
    try {
        $html = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
        if ($html === false || $ip !== '127.0.0.1') { throw new RuntimeException('M50 loopback GET unavailable.'); }
        return ['html' => $html, 'status' => $status, 'robots_header' => $robots];
    } finally { curl_close($curl); }
};
$testPayload = static function (string $html): array {
    // Read the public SSR assignment as JSON, never eval JavaScript or copy auth/session state.
    if (preg_match_all('/window\.__INITIAL_JS_TEST_QUESTIONS__\s*=\s*(?=\[)/', $html, $matches, PREG_OFFSET_CAPTURE) !== 1) {
        throw new RuntimeException('M50 consumer requires exactly one known SSR question assignment.');
    }
    $start = $matches[0][0][1] + strlen($matches[0][0][0]); $depth = 0; $quoted = false; $escaped = false; $end = null;
    if (($html[$start] ?? '') !== '[') { throw new RuntimeException('M50 SSR question assignment is not a JSON array.'); }
    for ($i = $start, $length = strlen($html); $i < $length; ++$i) {
        $c = $html[$i];
        if ($quoted) { if ($escaped) { $escaped = false; } elseif ($c === '\\') { $escaped = true; } elseif ($c === '"') { $quoted = false; } continue; }
        if ($c === '"') { $quoted = true; } elseif ($c === '[' || $c === '{') { ++$depth; }
        elseif ($c === ']' || $c === '}') { if (--$depth === 0) { $end = $i + 1; break; } }
    }
    if ($end === null) { throw new RuntimeException('M50 SSR question JSON is incomplete.'); }
    $raw = substr($html, $start, $end - $start); $questions = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
    $byUuid = []; $theory = [];
    foreach ($questions as $question) {
        $key = (string) ($question['uuid'] ?? $question['id'] ?? '');
        if ($key === '' || isset($byUuid[$key])) { throw new RuntimeException('M50 SSR question identity missing/duplicate.'); }
        $byUuid[$key] = $question;
        $theory[$key] = ['theory_block' => $question['theory_block'] ?? null, 'theory_blocks' => $question['theory_blocks'] ?? null];
    }
    if ($byUuid === []) { throw new RuntimeException('M50 SSR question dataset empty.'); }
    ksort($byUuid); ksort($theory);
    return ['count' => count($questions), 'raw_dataset_sha256' => hash('sha256', $raw), 'raw_question_order' => array_keys(array_column($questions, null, isset($questions[0]['uuid']) ? 'uuid' : 'id')),
        'questions_by_identity' => $byUuid, 'questions_sha256' => App\Services\M26ContentPatch::digest($byUuid),
        'theory_blocks_sha256' => App\Services\M26ContentPatch::digest($theory),
        'comparison_policy' => 'All per-question fields and theory blocks exact; only top-level order keyed by identity because the existing controller shuffles compose questions per GET'];
};
$bindings = [
    ['short' => 'ModalVerbsMustHaveToTheorySeeder', 'slug' => 'must-have-to', 'bank' => 'PolyglotMustHaveToAllLevelsLessonSeeder', 'test' => 'polyglot-must-have-to-all-levels'],
    ['short' => 'ModalVerbsShouldOughtToTheorySeeder', 'slug' => 'should-ought-to', 'bank' => 'PolyglotShouldOughtToAllLevelsLessonSeeder', 'test' => 'polyglot-should-ought-to-all-levels'],
    ['short' => 'ModalVerbsNeedNeedntDontHaveToTheorySeeder', 'slug' => 'need-neednt-dont-have-to', 'bank' => 'PolyglotNeedNeedntDontHaveToAllLevelsLessonSeeder', 'test' => 'polyglot-need-neednt-dont-have-to-all-levels'],
];
$bankPrefix = 'Database\\Seeders\\V3\\Polyglot\\';
$ownerPrefix = 'Database\\Seeders\\Page_V3\\ModalVerbs\\';
$acceptedBanks = [
    $ownerPrefix.'ModalVerbsMustHaveToTheorySeeder' => ['page_id' => 75, 'classes' => [$bankPrefix.'PolyglotMustHaveToLessonSeeder'], 'ids' => [121, 122, 123, 124, 125]],
    $ownerPrefix.'ModalVerbsShouldOughtToTheorySeeder' => ['page_id' => 79, 'classes' => [$bankPrefix.'PolyglotShouldOughtToLessonSeeder'], 'ids' => [97, 98, 99, 100, 101]],
    $ownerPrefix.'ModalVerbsNeedNeedntDontHaveToTheorySeeder' => ['page_id' => 76, 'classes' => [$bankPrefix.'PolyglotGerundVsInfinitiveLessonSeeder', $bankPrefix.'PolyglotMustHaveToLessonSeeder'], 'ids' => [158, 164, 166, 133, 135]],
];
$needPracticeUuid = App\Support\M26DetailPackage::uuid($ownerPrefix.'ModalVerbsNeedNeedntDontHaveToTheorySeeder',
    ['uuid_key' => 'm50:need-neednt-dont-have-to:need-neednt-dont-have-to-practice'], 7);
if ($needPracticeUuid !== '99bf6a7c-b493-5e4a-b66b-9b3ac8be3c65') { throw new RuntimeException('M50 independent Need practice UUID differs.'); }
$questionSnapshot = static function (int $id) use ($db, $rows): array {
    $question = (array) $db->table('questions')->where('id', $id)->sole();
    $snapshot = ['question' => $question];
    foreach (['answers' => 'question_answers', 'option_pivots' => 'question_option_question', 'verb_hints' => 'verb_hints',
        'hints' => 'question_hints', 'variants' => 'question_variants'] as $key => $table) {
        $snapshot[$key] = $rows($db->table($table)->where('question_id', $id)->orderBy('id'));
    }
    $optionIds = array_values(array_unique([...array_column($snapshot['answers'], 'option_id'),
        ...array_column($snapshot['option_pivots'], 'option_id'), ...array_column($snapshot['verb_hints'], 'option_id')]));
    $snapshot['options'] = $rows($db->table('question_options')->whereIn('id', $optionIds)->orderBy('id'));
    foreach ($snapshot as $key => $value) { $snapshot[$key.'_sha256'] = App\Services\M26ContentPatch::digest($value); }
    return $snapshot;
};
$capture = ['schema_version' => 1, 'stage' => 'M50 independent BEFORE', 'base_sha' => '4d85f890b8fe4fa0887d2f989581c18eb8cf4534',
    'started_at' => gmdate('c'), 'definitions' => [], 'pages' => [], 'banks' => [], 'http' => [], 'visual_hashes' => [], 'frozen_hashes' => [],
    'write_policy' => 'CLI SELECT in READ ONLY transactions, always ROLLBACK; HTTP GET only; no apply, route, config, cache or server changes'];
$guard = new App\Services\M49LocalTargetGuard;
$capture['physical'] = $guard->physicalEvidence();
foreach (['C:/Program Files/xampp/apache/conf/httpd.conf', 'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
    'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', $root.'/public/index.php'] as $path) {
    $capture['physical_hashes'][$path] = hash_file('sha256', $path);
}
$fingerprints = static function (array $pageIds, array $blockIds, array $excludedPivotPairs = []) use ($db): array {
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false); sort($tables);
    $full = []; $protected = [];
    foreach ($tables as $table) {
        $columns = $db->getSchemaBuilder()->getColumnListing($table);
        $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $fullHash = hash_init('sha256'); $protectedHash = hash_init('sha256'); $count = 0; $protectedCount = 0;
        foreach ($query->cursor() as $record) {
            $row = (array) $record;
            hash_update($fullHash, App\Services\M26ContentPatch::digest($row)."\n"); ++$count;
            if ($table === 'text_blocks' && in_array((int) ($row['id'] ?? 0), $blockIds, true)) { continue; }
            if ($table === 'question_theory_text_blocks' && in_array([
                'question_uuid' => $row['question_uuid'] ?? null, 'text_block_uuid' => $row['text_block_uuid'] ?? null,
                'position' => $row['position'] ?? null,
            ], $excludedPivotPairs, true)) { continue; }
            if ($table === 'pages' && in_array((int) ($row['id'] ?? 0), $pageIds, true)) { unset($row['text']); }
            hash_update($protectedHash, App\Services\M26ContentPatch::digest($row)."\n"); ++$protectedCount;
        }
        $full[$table] = ['count' => $count, 'sha256' => hash_final($fullHash)];
        $protected[$table] = ['count' => $protectedCount, 'sha256' => hash_final($protectedHash)];
    }
    return ['full' => $full, 'protected' => $protected];
};

if ($mode === 'consumers-capture') {
    $beforePath = $dir.'/before-v1.json';
    if (hash_file('sha256', $beforePath) !== 'd9f299235683df8962dcf35b9582c3ce8b40a1e6a45663f01ea6e18546de53f9') { throw new RuntimeException('M50 original consumer BEFORE differs.'); }
    $before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
    $consumers = ['schema_version' => 1, 'created_at' => gmdate('c'), 'before_sha256' => hash_file('sha256', $beforePath),
        'bank_sources' => [], 'saved_tests' => [], 'http' => [], 'write_policy' => 'READ ONLY SQL/ROLLBACK and anonymous loopback GET; no answer/auth/state submissions'];
    $paths = [];
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try {
        foreach (['PolyglotMustHaveToLessonSeeder', 'PolyglotShouldOughtToLessonSeeder', 'PolyglotGerundVsInfinitiveLessonSeeder'] as $short) {
            $path = 'database/seeders/V3/Polyglot/'.$short.'/definition.json'; $bytes = file_get_contents($root.'/'.$path);
            $source = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR); $test = $source['saved_test'];
            $row = (array) $db->table('saved_grammar_tests')->where('slug', $test['slug'])->sole();
            if ($row['uuid'] !== $test['uuid'] || !preg_match('/^polyglot-[a-z0-9-]+$/D', $row['slug'])) { throw new RuntimeException('M50 consumer saved-test source identity differs.'); }
            $questionLinks = $rows($db->table('saved_grammar_test_questions')->where('saved_grammar_test_id', $row['id'])->orderBy('position')->orderBy('id'));
            if (array_column($questionLinks, 'question_uuid') !== $test['question_uuids']) { throw new RuntimeException('M50 saved-test source question pool differs.'); }
            $consumers['bank_sources'][$path] = hash('sha256', $bytes);
            $consumers['saved_tests'][$row['slug']] = ['row' => $row, 'question_links' => $questionLinks, 'pool_sha256' => App\Services\M26ContentPatch::digest($questionLinks)];
            foreach (['test.show' => '', 'test.step-compose' => '/step/compose'] as $routeName => $suffix) {
                $localPath = '/test/'.$row['slug'].$suffix;
                $route = app('router')->getRoutes()->match(Illuminate\Http\Request::create('http://gramlyze.loc'.$localPath, 'GET'));
                if ($route->getName() !== $routeName || !str_starts_with($route->getActionName(), 'App\\Http\\Controllers\\NewDesignTestController@')
                    || in_array('App\\Http\\Middleware\\AdminAuthenticate', $route->gatherMiddleware(), true)) { throw new RuntimeException('M50 public consumer route identity differs.'); }
                $paths[$localPath] = ['route_name' => $routeName, 'action' => $route->getActionName(), 'slug' => $row['slug']];
            }
        }
        $consumers['sql_before'] = $fingerprints([], [])['full'];
        if ($consumers['sql_before'] !== $before['sql_before']['full']) { throw new RuntimeException('M50 SQL differs before bank consumer capture.'); }
    } finally { $db->rollBack(); }
    foreach ($paths as $path => $route) {
        $http = $getLocal($path); $file = substr($name, 0, -5).'-http-'.hash('sha256', $path).'.html';
        $exclusive($dir.'/'.$file, $http['html']);
        $entry = $route + ['url' => 'http://gramlyze.loc'.$path, 'status' => $http['status'], 'file' => $file, 'sha256' => hash('sha256', $http['html']), 'robots_header' => $http['robots_header']];
        if ($http['status'] === 200) {
            $entry['metadata'] = array_intersect_key($htmlAudit($http['html']), array_flip(['title', 'h1', 'description', 'og_description', 'twitter_description', 'canonical', 'structured', 'robots']));
            $entry['payload'] = $testPayload($http['html']);
        }
        $consumers['http'][] = $entry;
    }
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try { $consumers['sql_after_http'] = $fingerprints([], [])['full']; }
    finally { $db->rollBack(); }
    if ($consumers['sql_after_http'] !== $consumers['sql_before']) { throw new RuntimeException('M50 bank consumers changed SQL.'); }
    $consumers['finished_at'] = gmdate('c'); $exclusive($dir.'/'.$name, $json($consumers));
    echo $json(['status' => 'bank-consumers-before', 'file' => $name, 'sha256' => hash_file('sha256', $dir.'/'.$name),
        'unchanged_sql_tables' => count($consumers['sql_before']), 'http' => array_map(static fn ($r) => ['url' => $r['url'], 'status' => $r['status'], 'questions' => $r['payload']['count'] ?? null], $consumers['http'])]); exit(0);
}

if ($mode === 'links-capture') {
    $beforePath = $dir.'/before-v1.json';
    if (!hash_equals('d9f299235683df8962dcf35b9582c3ce8b40a1e6a45663f01ea6e18546de53f9', hash_file('sha256', $beforePath))) {
        throw new RuntimeException('M50 original independent BEFORE differs.');
    }
    $before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
    $links = ['schema_version' => 1, 'stage' => 'M50 independent question reuse and five-link BEFORE', 'created_at' => gmdate('c'),
        'base_sha' => $capture['base_sha'], 'before_sha256' => hash_file('sha256', $beforePath), 'practice_uuid' => $needPracticeUuid,
        'banks' => [], 'questions' => [], 'pairs' => [], 'source_files' => [], 'write_policy' => 'SELECT READ ONLY then ROLLBACK; no question/answer/link writes'];
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try {
        $server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
        $pid = trim((string) file_get_contents($server['pid_file']));
        if ($server['db'] !== 'gr2' || (int) $server['port'] !== 3306 || !str_starts_with($server['os'], 'Win') || !ctype_digit($pid)
            || !str_starts_with($normal($server['pid_file']), $normal($server['datadir']).'/')) { throw new RuntimeException('M50 reuse capture physical database identity differs.'); }
        $guard->assertEvidence($capture['physical'], $normal($root), (int) $pid, 3306);
        if ($db->table('text_blocks')->where('uuid', $needPracticeUuid)->exists()
            || $db->table('question_theory_text_blocks')->where('text_block_uuid', $needPracticeUuid)->exists()) { throw new RuntimeException('M50 Need new practice or its links already exist.'); }
        foreach ($acceptedBanks as $identity => $bank) {
            $owner = $before['pages'][$identity]['page'];
            if ((int) $owner['id'] !== $bank['page_id'] || (int) $owner['page_category_id'] !== 15) { throw new RuntimeException('M50 independent bank owner differs.'); }
            $query = App\Models\Question::query()->where('type', '4')->where('level', 'A2')->whereIn('seeder', $bank['classes']);
            $currentlyLinked = (clone $query)->whereHas('theoryTextBlocks', static fn ($q) => $q->where('text_blocks.page_id', $bank['page_id']))->orderBy('uuid')->get();
            if ($bank['page_id'] === 76) {
                if ($currentlyLinked->isNotEmpty()) { throw new RuntimeException('M50 Need curated classes already have unexpected owner links.'); }
                // Simulate ONLY the five approved new pairs as an OR predicate; never insert them.
                $selected = (clone $query)->where(function ($q) use ($bank) {
                    $q->whereHas('theoryTextBlocks', static fn ($b) => $b->where('text_blocks.page_id', 76))->orWhereIn('questions.id', $bank['ids']);
                })->orderBy('uuid')->limit(5)->get();
            } else {
                $transient = new App\Models\TextBlock;
                $transient->setRawAttributes(['page_id' => $bank['page_id'], 'locale' => 'uk', 'level' => 'A2', 'type' => 'practice-set', 'seeder' => $identity,
                    'body' => json_encode(['linked_practice' => ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => $bank['classes']]], JSON_THROW_ON_ERROR)]);
                $selected = app(App\Services\Theory\TextBlockToQuestionsMatcherService::class)->findBestQuestionsForTextBlock($transient, 5);
            }
            $ids = $selected->pluck('id')->map(static fn ($id) => (int) $id)->all();
            if ($ids !== $bank['ids']) { throw new RuntimeException('M50 independently approved selected question ordering differs.'); }
            $links['banks'][$identity] = $bank + ['currently_linked_count' => $currentlyLinked->count(), 'selected_uuids' => $selected->pluck('uuid')->all(),
                'selection_proof' => $bank['page_id'] === 76 ? 'Read-only synthetic five-pair eligibility; not live AFTER' : 'Actual native matcher on an unpersisted proposed block'];
            foreach ($selected as $question) {
                $short = substr((string) $question->seeder, strlen($bankPrefix));
                $sourcePath = 'database/seeders/V3/Polyglot/'.$short.'/definition.json';
                $sourceBytes = file_get_contents($root.'/'.$sourcePath);
                if ($sourceBytes !== file_get_contents($worktree.'/'.$sourcePath)) { throw new RuntimeException('M50 reused bank source ROOT/WT differs.'); }
                $source = json_decode($sourceBytes, true, flags: JSON_THROW_ON_ERROR); $found = [];
                foreach ($source['questions'] as $index => $item) { if (($item['uuid'] ?? null) === $question->uuid) { $found[] = [$index, $item]; } }
                if (count($found) !== 1 || $source['seeder']['class'] !== $question->seeder || $found[0][1]['question'] !== $question->question
                    || $found[0][1]['level'] !== 'A2' || (string) $found[0][1]['type'] !== '4') { throw new RuntimeException('M50 reused question source binding differs.'); }
                $links['source_files'][$sourcePath] = hash('sha256', $sourceBytes);
                if ($bank['page_id'] === 76) {
                    $snapshot = $questionSnapshot((int) $question->id);
                    $snapshot['existing_theory_links'] = $rows($db->table('question_theory_text_blocks')->where('question_uuid', $question->uuid)->orderBy('id'));
                    $snapshot['source_path'] = $sourcePath; $snapshot['source_sha256'] = hash('sha256', $sourceBytes);
                    $snapshot['source_uid'] = '/questions/'.$found[0][0]; $snapshot['source_question_id'] = $found[0][1]['id'];
                    $snapshot['source_question_uuid'] = $question->uuid; $snapshot['source_question'] = $found[0][1];
                    $links['questions'][$question->uuid] = $snapshot;
                    $links['pairs'][] = ['question_uuid' => $question->uuid, 'text_block_uuid' => $needPracticeUuid, 'position' => 0];
                }
            }
        }
        foreach ($before['banks'] as $bank) {
            if (hash_file('sha256', $root.'/'.$bank['definition']) !== $bank['definition_sha256']) { throw new RuntimeException('M50 original AllLevels bank source changed.'); }
            $links['source_files'][$bank['definition']] = $bank['definition_sha256'];
        }
        $links['sql_before'] = $fingerprints([], []);
        if ($links['sql_before']['full'] !== $before['sql_before']['full']) { throw new RuntimeException('M50 SQL differs from original BEFORE before question reuse.'); }
        $links['new_practice_absent'] = true; $links['all_new_practice_pivots_absent'] = true;
    } finally { $db->rollBack(); }
    $links['finished_at'] = gmdate('c'); $exclusive($dir.'/'.$name, $json($links));
    echo $json(['status' => 'question-reuse-before', 'file' => $name, 'sha256' => hash_file('sha256', $dir.'/'.$name),
        'practice_uuid' => $needPracticeUuid, 'questions' => count($links['questions']), 'pairs_absent' => count($links['pairs']),
        'source_files' => count($links['source_files']), 'unchanged_sql_tables' => count($links['sql_before']['full']), 'banks' => $links['banks']]); exit(0);
}

if ($mode !== 'capture') {
    foreach ([App\Support\M50ObligationAdvicePackage::class => 'app/Support/M50ObligationAdvicePackage.php',
        App\Services\M50ContentPatch::class => 'app/Services/M50ContentPatch.php',
        App\Services\M50LocalTargetGuard::class => 'app/Services/M50LocalTargetGuard.php'] as $class => $file) {
        if (!is_file($worktree.'/'.$file) || is_link($worktree.'/'.$file)) { throw new RuntimeException('M50 reviewed diagnostic dependency unavailable.'); }
        if (!class_exists($class, false)) { require_once $worktree.'/'.$file; }
        $loadedFile = (new ReflectionClass($class))->getFileName();
        if (str_replace("\r\n", "\n", file_get_contents($loadedFile)) !== str_replace("\r\n", "\n", file_get_contents($worktree.'/'.$file))) {
            throw new RuntimeException('M50 loaded diagnostic code differs from worktree.');
        }
    }
    $beforeFile = $dir.'/'.App\Services\M50ContentPatch::BEFORE_FILE;
    if (!hash_equals(App\Services\M50ContentPatch::BEFORE_SHA, hash_file('sha256', $beforeFile))) {
        throw new RuntimeException('M50 private BEFORE has not been frozen/reviewed.');
    }
    $before = json_decode(file_get_contents($beforeFile), true, flags: JSON_THROW_ON_ERROR);
    $linksBeforeFile = $dir.'/m50-links-before-v1.json';
    if (!hash_equals('98c70fb4fc8604758c9c32cbcf5f1fad2664aff1804fac6d61b1c5235c13e7da', hash_file('sha256', $linksBeforeFile))) {
        throw new RuntimeException('M50 independent question-reuse BEFORE differs.');
    }
    $linksBefore = json_decode(file_get_contents($linksBeforeFile), true, flags: JSON_THROW_ON_ERROR);
    if ($linksBefore['before_sha256'] !== hash_file('sha256', $beforeFile) || $linksBefore['practice_uuid'] !== $needPracticeUuid
        || count($linksBefore['pairs']) !== 5 || count($linksBefore['questions']) !== 5) { throw new RuntimeException('M50 question-reuse capture scope differs.'); }
    foreach ($acceptedBanks as $identity => $bank) {
        if (array_intersect_key($linksBefore['banks'][$identity] ?? [], $bank) !== $bank) { throw new RuntimeException('M50 independently accepted bank selection differs.'); }
    }
    $consumerBeforeFile = $dir.'/bank-consumers-before-v2.json';
    if (hash_file('sha256', $consumerBeforeFile) !== '9a2e0acd579ee001360bd246a9747317c834109a2ad362dd66b5da344a8ffd01') {
        throw new RuntimeException('M50 independent bank-consumer BEFORE differs.');
    }
    $consumerBefore = json_decode(file_get_contents($consumerBeforeFile), true, flags: JSON_THROW_ON_ERROR);
    [$publicBefore, $patch, $master] = App\Support\M50ObligationAdvicePackage::load($worktree);
    App\Support\M50ObligationAdvicePackage::validate($publicBefore, $patch, $master);
    $verifyPreservation = static function () use ($before, $linksBefore, $root, $worktree, $dir): array {
        $frozenPath = $dir.'/'.App\Services\M50ContentPatch::FROZEN_FILE;
        if (!hash_equals(App\Services\M50ContentPatch::FROZEN_SHA, hash_file('sha256', $frozenPath))) {
            throw new RuntimeException('M50 frozen inventory receipt differs.');
        }
        $frozen = json_decode(file_get_contents($frozenPath), true, flags: JSON_THROW_ON_ERROR);
        foreach (['visual' => $before['visual_hashes'], 'frozen' => $frozen['files']] as $kind => $files) {
            if (!$files) { throw new RuntimeException('M50 empty preservation inventory.'); }
            foreach ($files as $path => $sha) {
                if (!is_file($root.'/'.$path) || is_link($root.'/'.$path) || !hash_equals($sha, hash_file('sha256', $root.'/'.$path))) {
                    throw new RuntimeException('M50 '.$kind.' source changed: '.$path);
                }
            }
        }
        foreach ($linksBefore['source_files'] as $path => $sha) {
            if (hash_file('sha256', $root.'/'.$path) !== $sha || hash_file('sha256', $worktree.'/'.$path) !== $sha) {
                throw new RuntimeException('M50 old or reused question-bank source changed.');
            }
        }
        return ['visual_files' => count($before['visual_hashes']), 'frozen_files' => count($frozen['files']),
            'bank_source_files' => count($linksBefore['source_files']), 'changes' => 0];
    };
    if ($mode === 'expectations') {
        $expected = ['created_at' => gmdate('c'), 'before_sha256' => hash_file('sha256', $beforeFile),
            'question_reuse_before_sha256' => hash_file('sha256', $linksBeforeFile),
            'bank_consumers_before_sha256' => hash_file('sha256', $consumerBeforeFile),
            'master_sha256' => App\Support\M50ObligationAdvicePackage::MASTER_SHA, 'source_sha256' => App\Support\M50ObligationAdvicePackage::SOURCE_SHA,
            'services' => [], 'lessons' => [], 'derived_before_apply' => true];
        foreach (['app/Support/PageMetadata.php', 'app/Http/Middleware/AddStructuredData.php'] as $path) {
            $expected['services'][$path] = hash_file('sha256', $root.'/'.$path);
        }
        $description = new ReflectionMethod(App\Http\Middleware\AddStructuredData::class, 'pageDescription');
        foreach ($patch['targets'] as $i => $target) {
            $identity = $target['identity']; $old = $before['pages'][$identity];
            $prior = array_values(array_filter($before['http'], static fn ($item) => $item['url'] === $target['local_url']));
            if (count($prior) !== 1) { throw new RuntimeException('M50 BEFORE metadata target absent/ambiguous.'); }
            $after = $target['after'];
            $page = new App\Models\Page; $page->setRawAttributes(array_replace($old['page'], ['text' => $after['page']['subtitle_text']]));
            $configs = [['body' => $after['page']['subtitle_html']], ...$after['page']['blocks']];
            $models = array_map(static function ($config) { $model = new App\Models\TextBlock; $model->setRawAttributes(['body' => $config['body']]); return $model; }, $configs);
            $page->setRelation('textBlocks', new Illuminate\Database\Eloquent\Collection($models));
            $lrDescription = $description->invoke(new App\Http\Middleware\AddStructuredData, $page);
            $metadata = App\Support\PageMetadata::theory($after['page']['title'], $after['page']['category']['title'],
                $master['lessons'][$i]['blocks'][0]['data']['intro'], $identity, 'uk');
            $fields = array_intersect_key($prior[0]['audit'], array_flip(['h1', 'canonical', 'robots', 'structured']));
            $fields['title'] = $metadata['title'];
            foreach (['description', 'og_description', 'twitter_description'] as $field) { $fields[$field] = $metadata['description']; }
            $seen = 0;
            foreach ($fields['structured'] as &$container) {
                foreach ($container['@graph'] ?? [] as $index => $node) {
                    if (($node['@type'] ?? null) === 'LearningResource') { $container['@graph'][$index]['description'] = $lrDescription; ++$seen; }
                }
            }
            unset($container);
            if ($seen !== 1) { throw new RuntimeException('M50 BEFORE needs one LearningResource.'); }
            $expected['lessons'][$identity] = ['url' => $target['local_url'], 'expected' => $fields,
                'robots_header' => $prior[0]['robots_header'], 'before_anchors' => array_values(array_filter($prior[0]['audit']['anchors'], static fn ($id) => str_starts_with($id, 'block-')))];
        }
        $exclusive($dir.'/'.$name, $json($expected));
        echo $json(['status' => 'metadata-expected-before-apply', 'file' => $name, 'sha256' => hash_file('sha256', $dir.'/'.$name), 'lessons' => count($expected['lessons'])]); exit(0);
    }
    if ($mode === 'sync') {
        if ($worktree !== App\Services\M50ContentPatch::SOURCE_WORKTREE || $root !== App\Services\M50LocalTargetGuard::ROOT) {
            throw new RuntimeException('M50 source sync ROOT/WT differs from the reviewed exact paths.');
        }
        $files = [];
        foreach (App\Services\M50ContentPatch::syncPaths($patch) as $path) {
            if (is_link($root.'/'.$path) || is_link($worktree.'/'.$path)) { throw new RuntimeException('M50 source symlink refused.'); }
            $a = file_get_contents($root.'/'.$path); $b = file_get_contents($worktree.'/'.$path);
            if ($path === App\Services\M50ContentPatch::CONTROLLER_PATH) {
                $rootBeforeFile = $dir.'/'.App\Services\M50ContentPatch::CONTROLLER_BEFORE_FILE;
                $wtBeforeFile = $dir.'/'.App\Services\M50ContentPatch::CONTROLLER_WT_BEFORE_FILE;
                if (App\Services\M50ContentPatch::fileHash($rootBeforeFile) !== App\Services\M50ContentPatch::CONTROLLER_BEFORE_SHA
                    || App\Services\M50ContentPatch::fileHash($wtBeforeFile) !== App\Services\M50ContentPatch::CONTROLLER_WT_BEFORE_SHA) {
                    throw new RuntimeException('M50 independent controller ROOT/WT BEFORE snapshots drifted.');
                }
                $rootBefore = file_get_contents($rootBeforeFile); $wtBefore = file_get_contents($wtBeforeFile);
                if (hash('sha256', str_replace("\r\n", "\n", $wtBefore)) !== App\Services\M50ContentPatch::CONTROLLER_GIT_BASE_LF_SHA
                    || $a !== App\Services\M50ContentPatch::approvedControllerAfter($rootBefore)
                    || $b !== App\Services\M50ContentPatch::approvedControllerAfter($wtBefore)) {
                    throw new RuntimeException('M50 controller differs beyond the exact approved insertions into each independent BEFORE.');
                }
            } elseif ($a !== $b && !(str_ends_with($path, '.php') && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b))) {
                throw new RuntimeException('M50 source differs beyond preserved PHP EOL: '.$path);
            }
            $files[] = ['path' => $path, 'root_after_sha256' => hash('sha256', $a), 'worktree_sha256' => hash('sha256', $b)];
        }
        $receipt = ['base_sha' => App\Support\M50ObligationAdvicePackage::BASE_SHA, 'files' => $files,
            'source_worktree' => App\Services\M50ContentPatch::SOURCE_WORKTREE, 'served_root' => App\Services\M50LocalTargetGuard::ROOT,
            'visual_before' => ['file' => App\Services\M50ContentPatch::BEFORE_FILE, 'sha256' => App\Services\M50ContentPatch::BEFORE_SHA],
            'preservation' => $verifyPreservation(), 'at' => gmdate('c'), 'copied_files' => 0];
        $file = App\Services\M50ContentPatch::SYNC_RECEIPT; $exclusive($dir.'/'.$file, $json($receipt));
        echo $json(['status' => 'source-parity-only', 'file' => $file, 'sha256' => hash_file('sha256', $dir.'/'.$file), 'files' => count($files), 'copied_files' => 0]); exit(0);
    }
    if ($mode === 'review') {
        $plan = json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
        $updates = []; $inserts = [];
        foreach ($patch['targets'] as $target) {
            $old = $before['pages'][$target['identity']]; $after = $target['after'];
            if ($old['page']['text'] !== $after['page']['subtitle_text']) { $updates[] = ['table' => 'pages', 'id' => $old['page']['id'], 'after' => ['text' => $after['page']['subtitle_text']]]; }
            $configs = [['body' => $after['page']['subtitle_html']], ...$after['page']['blocks']];
            $uk = array_values(array_filter($old['blocks'], static fn ($row) => $row['locale'] === 'uk'));
            foreach ($uk as $position => $row) {
                if ($row['body'] !== $configs[$position]['body']) { $updates[] = ['table' => 'text_blocks', 'id' => $row['id'], 'after' => ['body' => $configs[$position]['body']]]; }
            }
            foreach (array_slice($configs, count($uk), null, true) as $position => $config) {
                $inserts[] = App\Support\M26DetailPackage::uuid($target['identity'], $config, $position);
            }
        }
        $gotUpdates = array_map(static fn ($item) => array_intersect_key($item, array_flip(['table', 'id', 'after'])), $plan['updates']);
        $sort = static function (&$items) { usort($items, static fn ($a, $b) => strcmp(App\Services\M26ContentPatch::digest($a), App\Services\M26ContentPatch::digest($b))); };
        $sort($updates); $sort($gotUpdates); sort($inserts);
        $gotInserts = array_column(array_column($plan['inserts'], 'fields'), 'uuid'); sort($gotInserts);
        $linkInserts = array_map(static fn ($fields) => ['table' => 'question_theory_text_blocks', 'fields' => $fields,
            'sha256' => App\Services\M26ContentPatch::digest($fields)], $linksBefore['pairs']);
        $gotLinkInserts = $plan['link_inserts'] ?? []; $sort($linkInserts); $sort($gotLinkInserts);
        if ($updates !== $gotUpdates || $inserts !== $gotInserts || $plan['state'] !== 'before' || count($inserts) !== 3
            || $linkInserts !== $gotLinkInserts || ($plan['link_state'] ?? null) !== 'before'
            || $plan['protected'] !== $before['sql_before']['protected']) { throw new RuntimeException('M50 independent exact preview review failed.'); }
        $receipt = ['preview' => $name, 'preview_sha256' => hash_file('sha256', $dir.'/'.$name), 'before_sha256' => hash_file('sha256', $beforeFile),
            'source_sha256' => App\Support\M50ObligationAdvicePackage::SOURCE_SHA, 'question_reuse_before_sha256' => hash_file('sha256', $linksBeforeFile),
            'updated' => count($updates), 'inserted' => count($inserts), 'inserted_links' => count($linkInserts), 'questions_changed' => 0,
            'deleted' => 0, 'exact_diff_review' => true];
        $file = 'reviewed-'.$name; $exclusive($dir.'/'.$file, $json($receipt)); echo $json($receipt); exit(0);
    }
    if ($mode === 'proof') {
        $nonce = bin2hex(random_bytes(16)); $routeBytes = file_get_contents($root.'/routes/api.php');
        if (str_contains($routeBytes, 'm50-target-')) { throw new RuntimeException('M50 proof route already present; do not nest proof lifecycles.'); }
        $proof = ['target' => 'gramlyze.loc', 'nonce' => $nonce, 'created_at' => gmdate('c'), 'scope' => App\Services\M50LocalTargetGuard::scope(),
            'physical_hashes' => $capture['physical_hashes'], 'physical' => $capture['physical'], 'routes_before_sha256' => hash('sha256', $routeBytes)];
        $file = 'local-proof-'.$nonce.'.json';
        $exclusive($dir.'/routes-before-'.$nonce.'.bin', $routeBytes); $exclusive($dir.'/'.$file, $json($proof));
        App\Services\M50LocalTargetGuard::readFreshProof($file);
        echo $json(['status' => 'private-proof-prepared-route-not-created', 'file' => $file, 'nonce' => $nonce, 'routes_before_sha256' => $proof['routes_before_sha256']]); exit(0);
    }
    if ($mode === 'cleanup') {
        $proof = json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
        $nonce = substr($name, strlen('local-proof-'), 32);
        if (($proof['nonce'] ?? null) !== $nonce || ($proof['scope'] ?? null) !== App\Services\M50LocalTargetGuard::scope()) { throw new RuntimeException('M50 cleanup proof identity differs.'); }
        $oldRoutes = file_get_contents($dir.'/routes-before-'.$nonce.'.bin'); $nowRoutes = file_get_contents($root.'/routes/api.php');
        if ($oldRoutes !== $nowRoutes || hash('sha256', $nowRoutes) !== $proof['routes_before_sha256']) { throw new RuntimeException('M50 routes have not been restored byte-for-byte.'); }
        $result = $getLocal('/api/_local/m50-target-'.$nonce);
        if ($result['status'] !== 404) { throw new RuntimeException('M50 removed proof endpoint did not return 404.'); }
        $receipt = ['routes_byte_exact' => true, 'routes_sha256' => hash('sha256', $nowRoutes), 'removed_nonce_get_status' => 404, 'at' => gmdate('c')];
        $file = 'cleanup-'.$nonce.'.json'; $exclusive($dir.'/'.$file, $json($receipt)); echo $json($receipt); exit(0);
    }
    if ($mode !== 'audit') { throw new RuntimeException('M50 unsupported diagnostic action.'); }
    // The audit is intentionally separate from the planner and has no apply entry.
    $metadataFile = $argv[3] ?? 'metadata-expected-v1.json';
    if (!preg_match('/^metadata-expected-v[1-9][0-9]*\.json$/D', $metadataFile)) { throw new RuntimeException('M50 expected metadata basename differs.'); }
    $expected = json_decode(file_get_contents($dir.'/'.$metadataFile), true, flags: JSON_THROW_ON_ERROR);
    if ($expected['before_sha256'] !== hash_file('sha256', $beforeFile) || $expected['question_reuse_before_sha256'] !== hash_file('sha256', $linksBeforeFile)
        || $expected['bank_consumers_before_sha256'] !== hash_file('sha256', $consumerBeforeFile)
        || $expected['master_sha256'] !== App\Support\M50ObligationAdvicePackage::MASTER_SHA
        || $expected['source_sha256'] !== App\Support\M50ObligationAdvicePackage::SOURCE_SHA) { throw new RuntimeException('M50 pre-apply metadata expectation is stale.'); }
    foreach ($expected['services'] as $path => $sha) {
        if (hash_file('sha256', $root.'/'.$path) !== $sha) { throw new RuntimeException('M50 metadata service changed since expectations.'); }
    }
    $audit = ['at' => gmdate('c'), 'before_sha256' => hash_file('sha256', $beforeFile), 'metadata_expected_sha256' => hash_file('sha256', $dir.'/'.$metadataFile),
        'question_reuse_before_sha256' => hash_file('sha256', $linksBeforeFile),
        'bank_consumers_before_sha256' => hash_file('sha256', $consumerBeforeFile),
        'preservation' => $verifyPreservation(), 'pages' => [], 'http' => []];
    $pageIds = []; $blockIds = [];
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try {
        $server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
        $pid = trim((string) file_get_contents($server['pid_file']));
        if ($server['db'] !== 'gr2' || (int) $server['port'] !== 3306 || !str_starts_with($server['os'], 'Win') || !ctype_digit($pid)
            || !str_starts_with($normal($server['pid_file']), $normal($server['datadir']).'/')) { throw new RuntimeException('M50 audit physical database identity differs.'); }
        $guard->assertEvidence($capture['physical'], $normal($root), (int) $pid, 3306);
        foreach ($patch['targets'] as $target) {
            $identity = $target['identity']; $old = $before['pages'][$identity]; $after = $target['after'];
            $owners = $rows($db->table('pages')->where('seeder', $identity));
            if (count($owners) !== 1 || $owners[0] !== array_replace($old['page'], ['text' => $after['page']['subtitle_text']])) { throw new RuntimeException('M50 exact Page AFTER differs.'); }
            $page = $owners[0]; $pageIds[] = (int) $page['id'];
            $actual = $rows($db->table('text_blocks')->where('page_id', $page['id'])->orderBy('sort_order')->orderBy('id'));
            $uk = array_values(array_filter($actual, static fn ($row) => $row['locale'] === 'uk'));
            $configs = [['type' => 'subtitle', 'column' => 'header', 'level' => null, 'uuid_key' => 'subtitle', 'body' => $after['page']['subtitle_html']], ...$after['page']['blocks']];
            if (count($uk) !== count($configs)) { throw new RuntimeException('M50 exact AFTER row count differs.'); }
            foreach ($configs as $position => $config) {
                $row = $uk[$position]; $blockIds[] = (int) $row['id'];
                if ($row['uuid'] !== App\Support\M26DetailPackage::uuid($identity, $config, $position) || $row['body'] !== $config['body']
                    || $row['seeder'] !== $identity || $row['page_category_id'] !== $page['page_category_id'] || (int) $row['sort_order'] !== $position) { throw new RuntimeException('M50 exact AFTER body/UUID/owner differs.'); }
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if ($row[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('M50 AFTER block metadata differs.'); }
                }
            }
            foreach ($old['blocks'] as $oldRow) {
                $found = array_values(array_filter($actual, static fn ($row) => $row['id'] === $oldRow['id']));
                if (count($found) !== 1) { throw new RuntimeException('M50 old row disappeared/duplicated.'); }
                $actualRow = $found[0]; if ($oldRow['locale'] === 'uk') { unset($oldRow['body'], $actualRow['body']); }
                if ($actualRow !== $oldRow) { throw new RuntimeException('M50 old timestamps/UUID/metadata or EN/PL content changed.'); }
            }
            $practiceRows = array_values(array_filter($uk, static fn ($row) => $row['type'] === 'practice-set'));
            if (count($practiceRows) !== 1 || empty($practiceRows[0]['created_at']) || $practiceRows[0]['created_at'] !== $practiceRows[0]['updated_at']) { throw new RuntimeException('M50 new practice row identity/timestamps differ.'); }
            $block = new App\Models\TextBlock; $block->setRawAttributes($practiceRows[0]);
            $selected = app(App\Services\Theory\TextBlockToQuestionsMatcherService::class)->findBestQuestionsForTextBlock($block, 5);
            $selectedIds = $selected->pluck('id')->map(static fn ($id) => (int) $id)->all();
            $practiceData = json_decode($practiceRows[0]['body'], true, flags: JSON_THROW_ON_ERROR);
            if ($selectedIds !== $acceptedBanks[$identity]['ids']
                || $selected->pluck('uuid')->all() !== $linksBefore['banks'][$identity]['selected_uuids']
                || ($practiceData['linked_practice']['seeder_classes'] ?? null) !== $acceptedBanks[$identity]['classes']) { throw new RuntimeException('M50 live practice matcher accepted bank/ordering changed.'); }
            $audit['pages'][$identity] = ['page_id' => $page['id'], 'uk_rows' => count($uk), 'all_rows' => count($actual), 'practice_id' => $practiceRows[0]['id'], 'selected_ids' => $selectedIds];
        }
        $pivotRows = $rows($db->table('question_theory_text_blocks')->where('text_block_uuid', $needPracticeUuid)->orderBy('question_uuid'));
        $actualPairs = array_map(static fn ($row) => array_intersect_key($row, array_flip(['question_uuid', 'text_block_uuid', 'position'])), $pivotRows);
        if ($actualPairs !== $linksBefore['pairs']) { throw new RuntimeException('M50 exact five new Need pairs/positions differ.'); }
        foreach ($pivotRows as $row) {
            $columns = array_keys($row); sort($columns);
            if ($columns !== ['created_at', 'id', 'position', 'question_uuid', 'text_block_uuid', 'updated_at']
                || empty($row['created_at']) || $row['created_at'] !== $row['updated_at']) { throw new RuntimeException('M50 new pair metadata/timestamps differ.'); }
        }
        foreach ($linksBefore['questions'] as $uuid => $saved) {
            $currentQuestion = $questionSnapshot((int) $saved['question']['id']);
            if ($currentQuestion !== array_intersect_key($saved, $currentQuestion)) { throw new RuntimeException('M50 reused question/answer/options changed.'); }
            $oldLinks = $rows($db->table('question_theory_text_blocks')->where('question_uuid', $uuid)->where('text_block_uuid', '<>', $needPracticeUuid)->orderBy('id'));
            if ($oldLinks !== $saved['existing_theory_links']) { throw new RuntimeException('M50 old reused-question links changed.'); }
        }
        $audit['new_need_links'] = ['pairs' => $actualPairs, 'count' => 5, 'reused_question_snapshots_unchanged' => 5, 'old_links_unchanged' => true];
        $audit['sql'] = $fingerprints($pageIds, $blockIds, $linksBefore['pairs']);
        if ($audit['sql']['protected'] !== $before['sql_before']['protected']) { throw new RuntimeException('M50 non-target SQL/pivots/banks/progress differs.'); }
        if ($audit['sql']['full']['question_theory_text_blocks']['count'] !== $before['sql_before']['full']['question_theory_text_blocks']['count'] + 5) {
            throw new RuntimeException('M50 pivot growth is not exactly five.');
        }
        $audit['unchanged_question_and_consumer_tables'] = [];
        foreach (['questions', 'question_answers', 'question_options', 'question_option_question', 'question_hints', 'question_variants',
            'verb_hints', 'question_tag', 'question_marker_tag', 'saved_grammar_tests', 'saved_grammar_test_questions',
            'user_polyglot_answer_attempts', 'user_polyglot_lesson_progress'] as $table) {
            if ($audit['sql']['full'][$table] !== $before['sql_before']['full'][$table]) { throw new RuntimeException('M50 old question/test pool/progress table changed: '.$table); }
            $audit['unchanged_question_and_consumer_tables'][$table] = $audit['sql']['full'][$table];
        }
        foreach ($consumerBefore['saved_tests'] as $slug => $saved) {
            $test = (array) $db->table('saved_grammar_tests')->where('slug', $slug)->sole();
            $pool = $rows($db->table('saved_grammar_test_questions')->where('saved_grammar_test_id', $test['id'])->orderBy('position')->orderBy('id'));
            if ($test !== $saved['row'] || $pool !== $saved['question_links'] || App\Services\M26ContentPatch::digest($pool) !== $saved['pool_sha256']) {
                throw new RuntimeException('M50 reused-bank saved-test row or pool changed.');
            }
        }
    } finally { $db->rollBack(); }
    foreach ($patch['targets'] as $target) {
        $identity = $target['identity']; $slug = $target['slug'];
        $paths = ['/theory/modal-verbs/'.$slug, '/courses/english-grammar-theory/lesson/modal-verbs/'.$slug,
            '/test/'.$before['banks'][$identity]['saved_test_slug']];
        foreach ($paths as $path) {
            $http = $getLocal($path); if ($http['status'] !== 200) { throw new RuntimeException('M50 audited local consumer did not return 200: '.$path); }
            $current = $htmlAudit($http['html'], str_starts_with($path, '/courses/'));
            $priorRows = array_values(array_filter($before['http'], static fn ($row) => $row['url'] === 'http://gramlyze.loc'.$path));
            if (count($priorRows) !== 1) { throw new RuntimeException('M50 consumer BEFORE absent.'); }
            $prior = $priorRows[0];
            if (str_starts_with($path, '/theory/')) {
                foreach ($expected['lessons'][$identity]['expected'] as $field => $value) {
                    if ($current[$field] !== $value) { throw new RuntimeException('M50 precomputed metadata differs: '.$slug.' '.$field); }
                }
                if ($http['robots_header'] !== $expected['lessons'][$identity]['robots_header'] || !str_contains($http['robots_header'].' '.$current['robots'], 'noindex')
                    || array_diff($expected['lessons'][$identity]['before_anchors'], $current['anchors']) !== []
                    || $current['practice_models'] !== 1 || $current['sentence_builders'] !== 1) { throw new RuntimeException('M50 theory anchors/practice/widget/noindex differs.'); }
            } else {
                foreach (['title', 'h1', 'description', 'og_description', 'twitter_description', 'canonical', 'structured'] as $field) {
                    if ($current[$field] !== $prior['audit'][$field]) { throw new RuntimeException('M50 non-target consumer metadata differs: '.$path.' '.$field); }
                }
                if (str_starts_with($path, '/courses/')) {
                    $priorFile = $prior['file'] ?? '';
                    if (!is_string($priorFile) || !preg_match('/^before-v[1-9][0-9]*-http-[a-f0-9]{64}\.html$/D', $priorFile)
                        || is_link($dir.'/'.$priorFile) || !is_file($dir.'/'.$priorFile)
                        || $normal(dirname($dir.'/'.$priorFile)) !== $normal($dir)) { throw new RuntimeException('M50 course BEFORE HTML path is invalid.'); }
                    $priorHtml = file_get_contents($dir.'/'.$priorFile);
                    if ($priorHtml === false || !is_string($prior['sha256'] ?? null)
                        || !hash_equals($prior['sha256'], hash('sha256', $priorHtml))) { throw new RuntimeException('M50 course BEFORE HTML differs from its immutable receipt.'); }
                    $priorDom = $htmlAudit($priorHtml, true);
                    if ($priorDom['content_node_count'] !== 1 || $current['content_node_count'] !== 1
                        || $priorDom['content_dom'] === '' || $current['content_dom'] !== $priorDom['content_dom']) { throw new RuntimeException('M50 complete course content DOM differs from independent live BEFORE.'); }
                    $current['course_content_dom'] = ['selector' => '.theory-design', 'before_html_file' => $priorFile,
                        'before_html_sha256' => $prior['sha256'], 'before_dom_sha256' => hash('sha256', $priorDom['content_dom']),
                        'after_dom_sha256' => hash('sha256', $current['content_dom']), 'bytes' => strlen($current['content_dom']),
                        'exact_bytes_equal' => true, 'normalization' => 'Same DOMDocument UTF-8 parser and saveHTML(node); no content normalization or exclusions'];
                    $normalize = static fn ($text) => preg_replace('/\s+/u', ' ', trim($text));
                    if ($normalize($current['content_text']) !== $normalize($prior['audit']['content_text']) || $current['content_text'] === '') { throw new RuntimeException('M50 course teaching text differs from independent live BEFORE.'); }
                } elseif (str_starts_with($path, '/test/')) {
                    $priorFile = $prior['file'] ?? '';
                    if (!is_string($priorFile) || !preg_match('/^before-v[1-9][0-9]*-http-[a-f0-9]{64}\.html$/D', $priorFile)
                        || is_link($dir.'/'.$priorFile)) { throw new RuntimeException('M50 old test BEFORE HTML path differs.'); }
                    $priorHtml = file_get_contents($dir.'/'.$priorFile);
                    if (!hash_equals($prior['sha256'], hash('sha256', $priorHtml))) { throw new RuntimeException('M50 old test BEFORE HTML hash differs.'); }
                    $oldPayload = $testPayload($priorHtml); $newPayload = $testPayload($http['html']);
                    if ($oldPayload['questions_by_identity'] !== $newPayload['questions_by_identity']) { throw new RuntimeException('M50 old AllLevels test payload/theory blocks changed.'); }
                    $current['test_payload'] = ['count' => $newPayload['count'], 'questions_sha256' => $newPayload['questions_sha256'],
                        'theory_blocks_sha256' => $newPayload['theory_blocks_sha256'], 'before_equal' => true, 'comparison_policy' => $newPayload['comparison_policy']];
                }
            }
            $file = substr($name, 0, -5).'-http-'.hash('sha256', $path).'.html'; $exclusive($dir.'/'.$file, $http['html']);
            unset($current['content_text'], $current['content_dom'], $current['content_node_count']);
            $audit['http'][] = ['url' => 'http://gramlyze.loc'.$path, 'status' => 200, 'file' => $file, 'sha256' => hash('sha256', $http['html']), 'audit' => $current];
        }
    }
    foreach ($consumerBefore['http'] as $prior) {
        $path = substr($prior['url'], strlen('http://gramlyze.loc'));
        if (!str_starts_with($prior['url'], 'http://gramlyze.loc/test/polyglot-')) { throw new RuntimeException('M50 bank consumer URL escaped local scope.'); }
        $http = $getLocal($path);
        if ($prior['status'] !== 200 || $http['status'] !== 200) { throw new RuntimeException('M50 approved bank consumer did not return 200.'); }
        $priorFile = $prior['file'];
        if (!preg_match('/^bank-consumers-before-v[1-9][0-9]*-http-[a-f0-9]{64}\.html$/D', $priorFile)
            || is_link($dir.'/'.$priorFile)) { throw new RuntimeException('M50 bank consumer BEFORE path differs.'); }
        $priorHtml = file_get_contents($dir.'/'.$priorFile);
        if (hash('sha256', $priorHtml) !== $prior['sha256']) { throw new RuntimeException('M50 bank consumer BEFORE HTML hash differs.'); }
        $oldPayload = $testPayload($priorHtml); $newPayload = $testPayload($http['html']);
        if ($oldPayload !== $prior['payload'] || $oldPayload['questions_by_identity'] !== $newPayload['questions_by_identity']) {
            throw new RuntimeException('M50 reused-bank exact question/theory payload differs: '.$path);
        }
        $metadata = array_intersect_key($htmlAudit($http['html']), $prior['metadata']);
        if ($metadata !== $prior['metadata'] || $http['robots_header'] !== $prior['robots_header']) { throw new RuntimeException('M50 reused-bank consumer metadata differs.'); }
        $file = substr($name, 0, -5).'-bank-http-'.hash('sha256', $path).'.html'; $exclusive($dir.'/'.$file, $http['html']);
        $audit['http'][] = ['url' => $prior['url'], 'status' => 200, 'file' => $file, 'sha256' => hash('sha256', $http['html']),
            'metadata' => $metadata, 'test_payload' => ['count' => $newPayload['count'], 'questions_sha256' => $newPayload['questions_sha256'],
                'theory_blocks_sha256' => $newPayload['theory_blocks_sha256'], 'before_equal' => true, 'comparison_policy' => $newPayload['comparison_policy']]];
    }
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try { $audit['sql_after_http'] = $fingerprints($pageIds, $blockIds, $linksBefore['pairs']); }
    finally { $db->rollBack(); }
    if ($audit['sql_after_http'] !== $audit['sql']) { throw new RuntimeException('M50 SQL changed during AFTER GET checks.'); }
    $audit['preservation'] = $verifyPreservation();
    $audit['physical'] = $capture['physical']; $audit['finished_at'] = gmdate('c');
    $exclusive($dir.'/'.$name, $json($audit));
    echo $json(['status' => 'read-only-after', 'file' => $name, 'sha256' => hash_file('sha256', $dir.'/'.$name), 'http_200' => count($audit['http']),
        'protected_tables' => count($audit['sql']['protected']), 'preservation' => $audit['preservation'], 'pages' => $audit['pages']]); exit(0);
}
$db->statement('SET TRANSACTION READ ONLY');
$db->beginTransaction();
try {
    $server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@hostname AS server, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
    $pid = trim((string) file_get_contents($server['pid_file']));
    if ($server['db'] !== 'gr2' || (int) $server['port'] !== 3306 || !str_starts_with($server['os'], 'Win')
        || !ctype_digit($pid) || !str_starts_with($normal($server['pid_file']), $normal($server['datadir']).'/')
        || strcasecmp($server['server'], gethostname()) !== 0) { throw new RuntimeException('M50 physical local MySQL identity mismatch.'); }
    $guard->assertEvidence($capture['physical'], $normal($root), (int) $pid, 3306);
    $capture['identity'] = App\Services\M49LocalTargetGuard::publicIdentity($db, $root.'/public');
    $capture['body_capacity'] = (array) $db->selectOne("SELECT DATA_TYPE AS data_type, CHARACTER_MAXIMUM_LENGTH AS characters, CHARACTER_OCTET_LENGTH AS bytes FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'text_blocks' AND COLUMN_NAME = 'body'");
    $pageIds = []; $blockIds = [];
    foreach ($bindings as $binding) {
        $identity = 'Database\\Seeders\\Page_V3\\ModalVerbs\\'.$binding['short'];
        $path = 'database/seeders/Page_V3/ModalVerbs/'.$binding['short'].'/definition.json';
        $bytes = file_get_contents($root.'/'.$path);
        if ($bytes !== file_get_contents($worktree.'/'.$path)) { throw new RuntimeException('M50 definition ROOT/worktree raw parity differs: '.$binding['slug']); }
        $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if ($definition['seeder']['class'] !== $identity || $definition['slug'] !== $binding['slug']
            || $definition['page']['locale'] !== 'uk' || $definition['page']['category']['slug'] !== 'modal-verbs'
            || count($definition['page']['blocks']) !== 6) { throw new RuntimeException('M50 finite source identity/slots mismatch.'); }
        $owners = $rows($db->table('pages')->where('seeder', $identity));
        if (count($owners) !== 1) { throw new RuntimeException('M50 page owner missing/ambiguous.'); }
        $page = $owners[0]; $pageIds[] = (int) $page['id'];
        if ($page['type'] !== 'theory' || $page['slug'] !== $binding['slug'] || $page['title'] !== $definition['page']['title']
            || $page['text'] !== $definition['page']['subtitle_text']) { throw new RuntimeException('M50 Page BEFORE/source differs.'); }
        $category = (array) $db->table('page_categories')->where('id', $page['page_category_id'])->first();
        if ($category['slug'] !== 'modal-verbs' || $category['title'] !== 'Модальні дієслова' || $category['language'] !== 'uk'
            || $category['type'] !== 'theory' || $category['parent_id'] !== null) { throw new RuntimeException('M50 exact category ancestry differs.'); }
        $blocks = $rows($db->table('text_blocks')->where('page_id', $page['id'])->orderBy('sort_order')->orderBy('id'));
        $uk = array_values(array_filter($blocks, static fn ($block) => $block['locale'] === 'uk'));
        $configs = [['type' => 'subtitle', 'column' => 'header', 'level' => null, 'uuid_key' => 'subtitle', 'body' => $definition['page']['subtitle_html']], ...$definition['page']['blocks']];
        if (count($configs) !== count($uk)) { throw new RuntimeException('M50 UK row count differs from source.'); }
        foreach ($configs as $slot => $config) {
            $block = $uk[$slot]; $blockIds[] = (int) $block['id'];
            if ($block['uuid'] !== App\Support\M26DetailPackage::uuid($identity, $config, $slot) || $block['body'] !== $config['body']
                || $block['seeder'] !== $identity || (int) $block['sort_order'] !== $slot || $block['page_category_id'] !== $page['page_category_id']) {
                throw new RuntimeException('M50 UK block source/UUID mismatch: '.$binding['slug'].' slot '.$slot);
            }
            foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                if ($block[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('M50 block metadata mismatch: '.$field); }
            }
        }
        $uuids = array_column($blocks, 'uuid');
        $links = $rows($db->table('question_theory_text_blocks')->whereIn('text_block_uuid', $uuids)->orderBy('id'));
        $bankClass = 'Database\\Seeders\\V3\\Polyglot\\'.$binding['bank'];
        $bankPath = 'database/seeders/V3/Polyglot/'.$binding['bank'].'/definition.json';
        $bankBytes = file_get_contents($root.'/'.$bankPath);
        $bankSource = json_decode($bankBytes, true, flags: JSON_THROW_ON_ERROR);
        $linksPath = 'database/seeders/V3/TheoryLinks/data/modal-verbs-'.$binding['slug'].'-theory-links.json';
        $linkSource = json_decode(file_get_contents($root.'/'.$linksPath), true, flags: JSON_THROW_ON_ERROR);
        if ($bankSource['seeder']['class'] !== $bankClass || !in_array($bankClass, $linkSource['expected_polyglot_seeders'], true)
            || $linkSource['page']['page_seeder_class'] !== $identity) { throw new RuntimeException('M50 independent bank declaration mismatch.'); }
        $bankQuestions = $rows($db->table('questions')->where('seeder', $bankClass)->orderBy('uuid'));
        $eligible = $rows($db->table('questions')->where('seeder', $bankClass)->where('type', '4')->where('level', 'A2')
            ->whereIn('uuid', array_column($links, 'question_uuid'))->orderBy('uuid'));
        if (count($eligible) < 5) { throw new RuntimeException('M50 exact A2/type4 linked bank has fewer than five candidates.'); }
        $transient = new App\Models\TextBlock;
        $transient->setRawAttributes(['page_id' => $page['id'], 'locale' => 'uk', 'level' => 'A2', 'type' => 'practice-set', 'seeder' => $identity,
            'body' => json_encode(['linked_practice' => ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => [$bankClass]]], JSON_THROW_ON_ERROR)]);
        $proposed = app(App\Services\Theory\TextBlockToQuestionsMatcherService::class)->findBestQuestionsForTextBlock($transient, 5);
        $expectedIds = array_map(static fn ($row) => (int) $row['id'], array_slice($eligible, 0, 5));
        if ($proposed->pluck('id')->map(static fn ($id) => (int) $id)->all() !== $expectedIds) { throw new RuntimeException('M50 prospective native matcher differs from independent sorted bank.'); }
        $savedTests = $rows($db->table('saved_grammar_tests')->where('slug', $binding['test'])->orderBy('id'));
        $capture['definitions'][$identity] = ['path' => $path, 'sha256' => hash('sha256', $bytes), 'bytes' => $bytes, 'definition' => $definition];
        $capture['pages'][$identity] = ['page' => $page, 'category_ancestry' => [$category], 'blocks' => $blocks, 'question_links' => $links,
            'metadata_from_current_source' => App\Support\PageMetadata::theory($page['title'], $category['title'], json_decode($configs[1]['body'], true, flags: JSON_THROW_ON_ERROR)['intro'], $identity, 'uk')];
        $capture['banks'][$identity] = ['seeder_class' => $bankClass, 'definition' => $bankPath, 'definition_sha256' => hash('sha256', $bankBytes),
            'theory_links_source' => $linksPath, 'theory_links_source_sha256' => hash_file('sha256', $root.'/'.$linksPath),
            'source_questions' => count($bankSource['questions']), 'all_questions_count' => count($bankQuestions), 'all_questions_sha256' => App\Services\M26ContentPatch::digest($bankQuestions),
            'eligible_a2_type4_count' => count($eligible), 'eligible_ids' => array_column($eligible, 'id'), 'expected_first_five_ids' => $expectedIds,
            'expected_first_five_uuids' => array_column(array_slice($eligible, 0, 5), 'uuid'), 'prospective_matcher_only' => true,
            'saved_test_slug' => $binding['test'], 'saved_tests' => $savedTests];
    }
    $capture['sql_before'] = $fingerprints($pageIds, $blockIds);
} finally { $db->rollBack(); }

$inventory = static function (array $folders, callable $exclude) use ($root): array {
    $hashes = [];
    foreach ($folders as $folder) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$folder, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isLink()) { throw new RuntimeException('M50 inventory symlink refused.'); }
            if (!$file->isFile()) { continue; }
            $relative = substr(str_replace('\\', '/', $file->getPathname()), strlen($root) + 1);
            if (!$exclude($relative)) { $hashes[$relative] = hash_file('sha256', $file->getPathname()); }
        }
    }
    ksort($hashes); return $hashes;
};
$capture['visual_hashes'] = $inventory(['resources/views', 'resources/css', 'resources/js', 'public/js'], static fn () => false);
$targetDefinitions = array_column($capture['definitions'], 'path');
$capture['frozen_hashes'] = $inventory(['docs/content', 'docs/reports', 'database/content-patches', 'database/seeders/Page_V3'],
    static fn ($path) => preg_match('~(?:^|/)m50[-./]~i', $path) || str_contains($path, '/seo-m50-') || in_array($path, $targetDefinitions, true));
foreach (['app/Support/M*.php', 'app/Services/M*.php', 'database/seeders/V3/Polyglot/*/definition.json', 'database/seeders/V3/TheoryLinks/data/*.json'] as $pattern) {
    foreach (glob($root.'/'.$pattern) ?: [] as $file) {
        if (str_starts_with(basename($file), 'M50')) { continue; }
        $capture['frozen_hashes'][substr(str_replace('\\', '/', $file), strlen($root) + 1)] = hash_file('sha256', $file);
    }
}
ksort($capture['frozen_hashes']);
foreach ($bindings as $binding) {
    foreach (['/theory/modal-verbs/'.$binding['slug'], '/courses/english-grammar-theory/lesson/modal-verbs/'.$binding['slug'],
        '/test/'.$binding['test'].'/js', '/test/'.$binding['test']] as $path) {
        $headers = []; $curl = curl_init('http://gramlyze.loc'.$path);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 60,
            CURLOPT_PROXY => '', CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'],
            CURLOPT_HEADERFUNCTION => static function ($handle, $header) use (&$headers) {
                if (stripos($header, 'X-Robots-Tag:') === 0) { $headers['robots'] = trim(substr($header, 13)); }
                return strlen($header);
            }]);
        $html = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
        if ($html === false || $ip !== '127.0.0.1') { throw new RuntimeException('M50 loopback HTTP unavailable at '.$path); }
        $file = substr($name, 0, -5).'-http-'.hash('sha256', $path).'.html'; $exclusive($dir.'/'.$file, $html);
        $capture['http'][] = ['url' => 'http://gramlyze.loc'.$path, 'status' => $status, 'file' => $file,
            'sha256' => hash('sha256', $html), 'robots_header' => $headers['robots'] ?? '', 'audit' => $htmlAudit($html)];
    }
}
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try { $capture['sql_after_http'] = $fingerprints($pageIds, $blockIds); }
finally { $db->rollBack(); }
$capture['sql_unchanged_during_capture'] = $capture['sql_before'] === $capture['sql_after_http'];
$capture['finished_at'] = gmdate('c');
$exclusive($dir.'/'.$name, $json($capture));
echo $json(['file' => $name, 'sha256' => hash_file('sha256', $dir.'/'.$name), 'identity' => $capture['identity'],
    'body_capacity' => $capture['body_capacity'], 'sql_tables' => count($capture['sql_before']['full']),
    'sql_unchanged_during_capture' => $capture['sql_unchanged_during_capture'], 'visual_files' => count($capture['visual_hashes']),
    'frozen_files' => count($capture['frozen_hashes']), 'owners' => array_map(static fn ($page) => ['id' => $page['page']['id'], 'category_id' => $page['page']['page_category_id'], 'slug' => $page['page']['slug'], 'blocks' => count($page['blocks'])], $capture['pages']),
    'banks' => array_map(static fn ($bank) => array_intersect_key($bank, array_flip(['seeder_class', 'eligible_a2_type4_count', 'expected_first_five_ids'])), $capture['banks']),
    'http' => array_map(static fn ($item) => ['url' => $item['url'], 'status' => $item['status']], $capture['http'])]);
exit($capture['sql_unchanged_during_capture'] ? 0 : 2);
