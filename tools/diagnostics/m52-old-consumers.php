<?php

/**
 * Explicit M52 old-consumer capture, reusing the reviewed M51 read-only procedure. Run only after review and an exact ROOT mirror.
 * php tools/diagnostics/m52-old-consumers.php capture before-old-consumers-v1.json
 * php tools/diagnostics/m52-old-consumers.php audit after-old-consumers-v1.json before-old-consumers-v1.json <before-sha256>
 * SELECT/READ ONLY/ROLLBACK, anonymous loopback GET, exclusive private receipts only.
 * Never run a seeder, write learning/progress data, clear sessions, or follow redirects.
 */
$mode = $argv[1] ?? '';
$name = $argv[2] ?? '';
$beforeName = $argv[3] ?? '';
$beforeSha = $argv[4] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows'
    || !in_array($mode, ['capture', 'audit'], true)
    || !preg_match('/^'.($mode === 'capture' ? 'before' : 'after').'-old-consumers-v[1-9][0-9]*\.json$/D', $name)
    || ($mode === 'capture' && count($argv) !== 3)
    || ($mode === 'audit' && (count($argv) !== 5
        || !preg_match('/^before-old-consumers-v[1-9][0-9]*\.json$/D', $beforeName)
        || !preg_match('/^[a-f0-9]{64}$/D', $beforeSha)))) {
    throw new RuntimeException('M52 old-consumer capture requires an explicit mode and scoped receipt basenames.');
}
set_exception_handler(static function (Throwable $error): void {
    $safe = $error instanceof RuntimeException && !$error instanceof PDOException
        ? $error->getMessage() : get_class($error).' (capture incomplete; no successful receipt)';
    fwrite(STDERR, 'M52 old consumers: '.$safe."\n"); exit(1);
});
$root = 'D:/DEV/htdocs/gramlyze.loc';
$worktree = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
$normal = static function (string $path): string {
    $real = realpath($path);
    if ($real === false) { throw new RuntimeException('M52 required local path is unavailable.'); }
    return rtrim(strtolower(str_replace('\\', '/', $real)), '/');
};
if ($normal(dirname(__DIR__, 2)) !== $normal($root)) {
    throw new RuntimeException('M52 consumer capture must run from the actual served ROOT, not the source worktree.');
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)) {
    throw new RuntimeException('M52 application/environment ROOT differs.');
}
$db = Illuminate\Support\Facades\DB::connection();
App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db); // Before PDO: single local MySQL gr2:3306 only.
$guard = new App\Services\M49LocalTargetGuard;
$physical = $guard->physicalEvidence(); // Existing M11 fixed, read-only Windows listener/vhost inspector.
$server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach (['datadir', 'pid_file'] as $key) {
    if (!is_string($server[$key] ?? null) || !preg_match('~^[A-Za-z]:/~D', str_replace('\\', '/', $server[$key]))) {
        throw new RuntimeException('M52 MySQL evidence path is not an absolute local Windows drive path.');
    }
}
$dataPath = $normal($server['datadir']); $pidPath = $normal($server['pid_file']);
$mysqlPid = trim((string) file_get_contents($server['pid_file']));
if (($server['db'] ?? null) !== 'gr2' || (int) ($server['port'] ?? 0) !== 3306
    || !str_starts_with((string) ($server['os'] ?? ''), 'Win') || !ctype_digit($mysqlPid)
    || !str_starts_with($pidPath, $dataPath.'/') || str_starts_with($dataPath, '//')) {
    throw new RuntimeException('M52 read-only consumer DB identity is not the inspected local Windows database.');
}
$guard->assertEvidence($physical, $normal($root), (int) $mysqlPid, 3306);
$dir = $root.'/storage/app/seo-m52-local';
if (is_link($dir) || (!is_dir($dir) && !mkdir($dir, 0700, true))
    || $normal($dir) !== $normal($root).'/storage/app/seo-m52-local'
    || file_exists($dir.'/'.$name) || is_link($dir.'/'.$name)) {
    throw new RuntimeException('M52 private consumer receipt path is unsafe or already exists.');
}
$json = static fn ($value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$digest = static fn ($value): string => App\Services\M26ContentPatch::digest($value);
$exclusive = static function (string $path, string $bytes): void {
    $handle = fopen($path, 'x');
    if ($handle === false) { throw new RuntimeException('M52 consumer receipt collision.'); }
    try {
        if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) {
            throw new RuntimeException('M52 consumer receipt write is incomplete.');
        }
    } finally { fclose($handle); }
};
$sourceHashes = [];
$readSource = static function (string $relative) use ($root, $worktree, &$sourceHashes): array {
    if (!preg_match('~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\.json)$~D', $relative)
        || is_link($root.'/'.$relative) || is_link($worktree.'/'.$relative)) {
        throw new RuntimeException('M52 old consumer source path is outside its finite source families.');
    }
    $bytes = file_get_contents($root.'/'.$relative);
    if ($bytes === false || $bytes !== file_get_contents($worktree.'/'.$relative)) {
        throw new RuntimeException('M52 old consumer source ROOT/worktree parity differs.');
    }
    $sourceHashes[$relative] = hash('sha256', $bytes);
    return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
};
$manifests = [
    'database/seeders/V3/TheoryLinks/data/passive-voice-formation-rules-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/passive-voice-negatives-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/passive-voice-modal-verbs-theory-links.json',
];
$declarations = []; $pages = [];
foreach ($manifests as $path) {
    $manifest = $readSource($path); $page = $manifest['page'] ?? [];
    if (($page['category_slug'] ?? null) !== 'passive-voice'
        || !in_array($page['page_seeder_class'] ?? null, [
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceNegativesQuestionsTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceModalVerbsTheorySeeder',
        ], true)
        || ($page['route'] ?? null) !== '/theory/passive-voice/'.($page['slug'] ?? '')
        || !str_starts_with($page['slug'] ?? '', 'theory-passive-voice-')) {
        throw new RuntimeException('M52 old consumer manifest page identity differs.');
    }
    $pages[$page['page_seeder_class']] = $page;
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
        $class = $test['seeder_class'] ?? '';
        if ($class === '' && ($test['kind'] ?? null) === 'standard_v3') {
            $classes = $test['expected_seeder_classes'] ?? [];
            if (!is_array($classes) || count($classes) !== 1 || !is_string($classes[0] ?? null)) {
                throw new RuntimeException('M52 standard test must declare exactly one original bank.');
            }
            $class = $classes[0];
        }
        if (!preg_match('/^Database\\\\Seeders\\\\V3\\\\(?:Polyglot|PassiveVoice)\\\\[A-Za-z0-9]+$/D', $class)) {
            throw new RuntimeException('M52 declared old bank has an unexpected class.');
        }
        $relative = 'database/seeders/'.str_replace('\\', '/', substr($class, strlen('Database\\Seeders\\'))).'/definition.json';
        $definition = $readSource($relative); $saved = $definition['saved_test'] ?? [];
        if (($definition['seeder']['class'] ?? null) !== $class
            || !preg_match('/^[a-z0-9-]+$/D', $saved['slug'] ?? '')
            || !is_string($saved['uuid'] ?? null) || !is_array($saved['question_uuids'] ?? null)
            || ($saved['question_uuids'] ?? []) === []) {
            throw new RuntimeException('M52 old bank saved-test declaration is incomplete.');
        }
        $types = array_unique(array_map(static fn ($q) => (string) ($q['type'] ?? $definition['defaults']['type'] ?? ''), $definition['questions']));
        $declarations[$saved['slug']] = ['bank' => $class, 'definition' => $relative, 'saved' => $saved,
            'source_question_uuids' => array_map(static fn ($q) => $q['uuid'], $definition['questions']),
            'compose' => $types === ['4'], 'page_owner' => $page['page_seeder_class']];
    }
}
ksort($declarations); ksort($pages); ksort($sourceHashes);
// Named route templates own URL construction. Do not fetch the .com URLs present in old metadata.
$routePath = static function (string $routeName, array $parameters): array {
    $route = app('router')->getRoutes()->getByName($routeName);
    if (!$route || !in_array('GET', $route->methods(), true)) { throw new RuntimeException('M52 old consumer GET route is unavailable.'); }
    $uri = '/'.$route->uri();
    foreach ($parameters as $key => $value) { $uri = str_replace('{'.$key.'}', $value, $uri); }
    if (str_contains($uri, '{') || !preg_match('~^/(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+$~D', $uri)) {
        throw new RuntimeException('M52 source-derived consumer URI is outside local scope.');
    }
    $matched = app('router')->getRoutes()->match(Illuminate\Http\Request::create('http://gramlyze.loc'.$uri, 'GET'));
    if ($matched->getName() !== $routeName || $matched->getActionName() !== $route->getActionName()
        || in_array('App\\Http\\Middleware\\AdminAuthenticate', $matched->gatherMiddleware(), true)) {
        throw new RuntimeException('M52 source-derived URI resolves to another route or an admin gate.');
    }
    return ['path' => $uri, 'route_name' => $routeName, 'action' => $route->getActionName()];
};
$routes = [];
foreach ($declarations as $slug => $declaration) {
    foreach (['test.show' => 'ssr', 'test.js.questions' => 'json'] as $routeName => $kind) {
        $entry = $routePath($routeName, ['slug' => $slug]); $routes[$entry['path']] = $entry + ['kind' => $kind, 'expected_count' => count($declaration['source_question_uuids'])];
    }
    if ($declaration['compose']) {
        $entry = $routePath('test.step-compose', ['slug' => $slug]); $routes[$entry['path']] = $entry + ['kind' => 'ssr', 'expected_count' => count($declaration['source_question_uuids'])];
    }
}
$rows = static fn ($query): array => $query->get()->map(static fn ($row) => (array) $row)->all();
$orderedRows = static function (string $table, $query, array $priority = []) use ($db, $rows): array {
    $columns = $db->getSchemaBuilder()->getColumnListing($table);
    $order = array_values(array_unique([...$priority, ...(in_array('id', $columns, true) ? ['id'] : $columns)]));
    foreach ($order as $column) {
        if (!in_array($column, $columns, true)) { throw new RuntimeException('M52 SQL snapshot priority is not a real column.'); }
        $query->orderBy($column);
    }
    return $rows($query);
};
$sqlSnapshot = static function () use ($db, $rows, $orderedRows, $declarations): array {
    $resolver = new App\Support\Database\QuestionUuidResolver;
    $banks = array_column($declarations, 'bank');
    $questions = $rows($db->table('questions')->whereIn('seeder', $banks)->orderBy('id'));
    $ids = array_column($questions, 'id'); $uuids = array_column($questions, 'uuid');
    foreach ($declarations as $slug => $declaration) {
        $actual = array_column(array_filter($questions, static fn ($q) => $q['seeder'] === $declaration['bank']), 'uuid');
        $expected = $resolver->toPersistentMany($declaration['source_question_uuids']);
        sort($actual); sort($expected);
        if ($actual !== $expected) { throw new RuntimeException('M52 old bank persisted question identity differs: '.$slug); }
    }
    $result = ['questions' => $questions];
    foreach (['question_answers', 'question_option_question', 'verb_hints', 'question_hints', 'question_variants', 'question_tag', 'question_marker_tag'] as $table) {
        $result[$table] = $orderedRows($table, $db->table($table)->whereIn('question_id', $ids));
    }
    $optionIds = array_unique([...array_column($result['question_answers'], 'option_id'),
        ...array_column($result['question_option_question'], 'option_id'), ...array_column($result['verb_hints'], 'option_id')]);
    $result['question_options'] = $rows($db->table('question_options')->whereIn('id', $optionIds)->orderBy('id'));
    $result['question_theory_text_blocks'] = $orderedRows('question_theory_text_blocks', $db->table('question_theory_text_blocks')->whereIn('question_uuid', $uuids), ['question_uuid', 'position', 'text_block_uuid']);
    $result['saved_tests'] = []; $result['saved_question_links'] = [];
    foreach ($declarations as $slug => $declaration) {
        $test = (array) $db->table('saved_grammar_tests')->where('slug', $slug)->sole();
        if ($test['uuid'] !== $declaration['saved']['uuid']) { throw new RuntimeException('M52 old saved-test UUID differs: '.$slug); }
        $links = $orderedRows('saved_grammar_test_questions', $db->table('saved_grammar_test_questions')->where('saved_grammar_test_id', $test['id']), ['position']);
        if (array_column($links, 'question_uuid') !== $resolver->toPersistentMany($declaration['saved']['question_uuids'])) {
            throw new RuntimeException('M52 old saved-test linked pool differs: '.$slug);
        }
        $result['saved_tests'][$slug] = $test; $result['saved_question_links'][$slug] = $links;
    }
    return $result;
};
$readOnly = static function (Closure $capture) use ($db) {
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try { return $capture(); } finally { $db->rollBack(); }
};
$allTableFingerprints = static function () use ($db, $digest): array {
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false); sort($tables);
    $result = [];
    foreach ($tables as $table) {
        $columns = $db->getSchemaBuilder()->getColumnListing($table); $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $hash = hash_init('sha256'); $count = 0;
        foreach ($query->cursor() as $record) { hash_update($hash, $digest((array) $record)."\n"); $count++; }
        $result[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
    }
    return $result;
};
$sqlBefore = $readOnly($sqlSnapshot);
$allTablesBeforeHttp = $readOnly($allTableFingerprints);
$workingBeforeName = 'before-v1.json';
$workingBeforeSha = '2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff';
$workingBeforePath = $dir.'/'.$workingBeforeName;
if (!is_file($workingBeforePath) || is_link($workingBeforePath)
    || !hash_equals($workingBeforeSha, hash_file('sha256', $workingBeforePath))) {
    throw new RuntimeException('M52 independent working BEFORE receipt differs.');
}
$workingBefore = json_decode(file_get_contents($workingBeforePath), true, flags: JSON_THROW_ON_ERROR);
if (($workingBefore['served_root'] ?? null) !== $root
    || ($workingBefore['source_worktree'] ?? null) !== $worktree
    || !is_array($workingBefore['sql_before'] ?? null)
    || ($workingBefore['sql_before'] ?? null) !== ($workingBefore['sql_after_http'] ?? null)
    || array_keys($workingBefore['sql_before']) !== array_keys($allTablesBeforeHttp)) {
    throw new RuntimeException('M52 independent working BEFORE identity/table inventory differs.');
}
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M52 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M52 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
$readOnly(static function () use ($db, $pages, $routePath, &$routes): void {
    foreach ($pages as $owner => $source) {
        $page = App\Models\Page::query()->with('category')->where('seeder', $owner)->sole();
        if ($page->type !== 'theory' || $page->slug !== $source['slug'] || $page->category?->slug !== $source['category_slug']) {
            throw new RuntimeException('M52 old consumer page binding differs.');
        }
        $slug = App\Support\TheoryPageTestSlug::forPage($page); // Pure current public test slug helper.
        foreach (['test.show' => 'ssr', 'test.js.questions' => 'json'] as $routeName => $kind) {
            $entry = $routePath($routeName, ['slug' => $slug]); $routes[$entry['path']] = $entry + ['kind' => $kind, 'expected_count' => null];
        }
        $entry = $routePath('courses.theory.lesson', ['categoryPath' => $source['category_slug'], 'pageSlug' => $source['slug']]);
        $routes[$entry['path']] = $entry + ['kind' => 'course', 'expected_count' => null];
    }
});
ksort($routes);
$getLocal = static function (array $route): array {
    $path = $route['path']; $curl = curl_init('http://gramlyze.loc'.$path); $robots = '';
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_MAXREDIRS => 0,
        CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60, CURLOPT_PROXY => '',
        CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'],
        CURLOPT_HTTPHEADER => ['Accept: '.($route['kind'] === 'json' ? 'application/json' : 'text/html')],
        CURLOPT_HEADERFUNCTION => static function ($handle, $header) use (&$robots): int {
            if (stripos($header, 'X-Robots-Tag:') === 0) { $robots = trim(substr($header, 13)); }
            return strlen($header);
        }]);
    try {
        $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        if ($body === false || curl_getinfo($curl, CURLINFO_PRIMARY_IP) !== '127.0.0.1'
            || curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) !== 'http://gramlyze.loc'.$path
            || curl_getinfo($curl, CURLINFO_REDIRECT_COUNT) !== 0) {
            throw new RuntimeException('M52 anonymous consumer loopback GET failed or changed target.');
        }
        return ['body' => $body, 'status' => $status, 'robots_header' => $robots];
    } finally { curl_close($curl); }
};
$ssrQuestions = static function (string $html): array {
    if (preg_match_all('/window\.__INITIAL_JS_TEST_QUESTIONS__\s*=\s*(?=\[)/', $html, $matches, PREG_OFFSET_CAPTURE) !== 1) {
        throw new RuntimeException('M52 old consumer requires exactly one public SSR question assignment.');
    }
    $start = $matches[0][0][1] + strlen($matches[0][0][0]); $depth = 0; $quoted = false; $escaped = false;
    for ($i = $start, $length = strlen($html); $i < $length; ++$i) {
        $c = $html[$i];
        if ($quoted) { if ($escaped) { $escaped = false; } elseif ($c === '\\') { $escaped = true; } elseif ($c === '"') { $quoted = false; } continue; }
        if ($c === '"') { $quoted = true; } elseif ($c === '[' || $c === '{') { ++$depth; }
        elseif ($c === ']' || $c === '}') {
            if (--$depth === 0) { return json_decode(substr($html, $start, $i + 1 - $start), true, flags: JSON_THROW_ON_ERROR); }
        }
    }
    throw new RuntimeException('M52 old consumer SSR assignment is incomplete.');
};
$questionPayload = static function (array $questions) use ($digest): array {
    if ($questions === [] || !array_is_list($questions)) { throw new RuntimeException('M52 old consumer question payload is empty/malformed.'); }
    $byIdentity = []; $theory = [];
    foreach ($questions as $question) {
        $key = (string) ($question['uuid'] ?? $question['id'] ?? '');
        if ($key === '' || isset($byIdentity[$key])) { throw new RuntimeException('M52 old question payload identity is absent/duplicate.'); }
        $byIdentity[$key] = $question;
        $theory[$key] = ['theory_block' => $question['theory_block'] ?? null, 'theory_blocks' => $question['theory_blocks'] ?? null];
    }
    ksort($byIdentity); ksort($theory);
    return ['count' => count($questions), 'questions_by_identity' => $byIdentity, 'questions_sha256' => $digest($byIdentity),
        'theory_blocks_sha256' => $digest($theory),
        'comparison_policy' => 'Every per-question field, answer, option and linked theory body remains exact. Only top-level controller shuffle order is keyed by identity.'];
};
$htmlFacts = static function (string $html, bool $course): array {
    $dom = new DOMDocument('1.0', 'UTF-8'); $old = libxml_use_internal_errors(true);
    try { $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET); }
    finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
    $xp = new DOMXPath($dom); $get = static fn ($query): string => $xp->query($query)->item(0)?->nodeValue ?? '';
    $facts = ['title' => $get('//title'), 'h1' => $get('//h1'), 'description' => $get('//meta[@name="description"]/@content'),
        'canonical' => $get('//link[@rel="canonical"]/@href'), 'robots' => $get('//meta[@name="robots"]/@content')];
    if ($course) {
        $nodes = $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-design ")]');
        if ($nodes->length !== 1) { throw new RuntimeException('M52 course has no unique learning content boundary.'); }
        $content = $dom->saveHTML($nodes->item(0));
        if ($content === '' || preg_match('/(?:name=["\']_token["\']|csrf-token)/i', $content)) {
            throw new RuntimeException('M52 course learning DOM is empty or contains a volatile/security field; no normalization allowed.');
        }
        $facts['content_dom'] = $content; $facts['content_dom_sha256'] = hash('sha256', $content);
        $facts['comparison_policy'] = 'Exact UTF-8 DOMDocument/saveHTML(.theory-design) bytes; no auth chrome, cookies, token fields or content exclusions.';
    }
    return $facts;
};
$before = null;
if ($mode === 'audit') {
    $beforePath = $dir.'/'.$beforeName;
    if (!is_file($beforePath) || is_link($beforePath) || !hash_equals($beforeSha, hash_file('sha256', $beforePath))) {
        throw new RuntimeException('M52 immutable consumer BEFORE receipt differs.');
    }
    $before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
    if (($before['mode'] ?? null) !== 'capture' || ($before['route_declarations'] ?? null) !== $routes
        || ($before['source_hashes'] ?? null) !== $sourceHashes || ($before['old_sql'] ?? null) !== $sqlBefore) {
        throw new RuntimeException('M52 old source/routes/question SQL differs from independent BEFORE.');
    }
}
$result = ['schema_version' => 1, 'mode' => $mode, 'created_at' => gmdate('c'), 'before_sha256' => $mode === 'audit' ? $beforeSha : null,
    'working_before_sha256' => $workingBeforeSha,
    'sql_allowlist_during_apply' => ['pages', 'text_blocks'],
    'route_declarations' => $routes, 'source_hashes' => $sourceHashes, 'old_sql' => $sqlBefore, 'http' => [],
    'physical_sha256' => $digest($physical), 'write_policy' => 'Explicit SELECT/READ ONLY/ROLLBACK and anonymous exact loopback GET only. No apply/seeder/auth/state/progress submission; no cookies/full response HTML stored.'];
foreach ($routes as $path => $route) {
    $http = $getLocal($route); $entry = ['url' => 'http://gramlyze.loc'.$path, 'status' => $http['status'], 'robots_header' => $http['robots_header'],
        'route_name' => $route['route_name'], 'action' => $route['action'], 'kind' => $route['kind']];
    if ($http['status'] !== 200) {
        if ($route['kind'] !== 'course' || !in_array($http['status'], [401, 403, 404], true)) {
            throw new RuntimeException('M52 old test consumer is unavailable: '.$path.' HTTP '.$http['status']);
        }
        $entry['acceptance'] = 'not_checked; existing course access gate/unavailability retained, not bypassed';
    } elseif ($route['kind'] === 'course') {
        $entry['facts'] = $htmlFacts($http['body'], true);
    } else {
        $questions = $route['kind'] === 'json'
            ? (json_decode($http['body'], true, flags: JSON_THROW_ON_ERROR)['questions'] ?? [])
            : $ssrQuestions($http['body']);
        $entry['payload'] = $questionPayload($questions);
        if ($route['expected_count'] !== null && $entry['payload']['count'] !== $route['expected_count']) {
            throw new RuntimeException('M52 persisted old test question count differs: '.$path);
        }
        if ($route['kind'] === 'ssr') { $entry['facts'] = $htmlFacts($http['body'], false); }
    }
    if ($before !== null && ($before['http'][$path] ?? null) !== $entry) {
        throw new RuntimeException('M52 old consumer payload/theory body/course DOM differs: '.$path);
    }
    $result['http'][$path] = $entry;
    echo $json(['consumer' => $path, 'status' => $http['status'], 'questions' => $entry['payload']['count'] ?? null, 'compared_before' => $before !== null]);
}
$sqlAfter = $readOnly($sqlSnapshot);
if ($sqlAfter !== $sqlBefore) { throw new RuntimeException('M52 old learning/question SQL changed during consumer GETs.'); }
$allTablesAfterHttp = $readOnly($allTableFingerprints);
if ($allTablesBeforeHttp !== $allTablesAfterHttp) { throw new RuntimeException('M52 a database table changed during anonymous consumer GETs.'); }
$result['all_tables_before_http'] = $allTablesBeforeHttp;
$result['all_tables_after_http'] = $allTablesAfterHttp;
$result['unchanged_during_http'] = true; $result['finished_at'] = gmdate('c');
$exclusive($dir.'/'.$name, $json($result));
echo $json(['status' => $mode === 'capture' ? 'independent-old-consumers-before' : 'old-consumers-preserved',
    'file' => $name, 'sha256' => hash_file('sha256', $dir.'/'.$name), 'saved_tests' => count($declarations),
    'old_questions' => count($sqlBefore['questions']), 'http_consumers' => count($routes), 'db_writes' => 0,
    'browser_visibility' => 'not_checked', 'interaction' => 'not_checked']);
