<?php

/**
 * Explicit M52 read-only source/DB/HTTP BEFORE capture.
 * Run from the served ROOT only, after reviewing this script:
 * php tools/diagnostics/m52-working-local.php capture before-v1.json
 * No seeder, route, apply, environment override, session mutation or progress write.
 */
set_exception_handler(static function (Throwable $error): void {
    $safe = $error instanceof RuntimeException && !$error instanceof PDOException
        ? $error->getMessage() : get_class($error).' (read-only capture incomplete)';
    fwrite(STDERR, 'M52 BEFORE: '.$safe."\n"); exit(1);
});
$name = $argv[2] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 3
    || ($argv[1] ?? '') !== 'capture' || preg_match('/^before-v[1-9][0-9]*\.json$/D', $name) !== 1) {
    throw new RuntimeException('Explicit Windows CLI capture and a private BEFORE basename are required.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$worktree = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
$normal = static function (string $path): string {
    $real = realpath($path);
    if ($real === false) { throw new RuntimeException('Required local path is unavailable.'); }
    return rtrim(strtolower(str_replace('\\', '/', $real)), '/');
};
if ($normal(dirname(__DIR__, 2)) !== $normal($root)) { throw new RuntimeException('Capture must run from the served ROOT.'); }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)) {
    throw new RuntimeException('Application/environment root differs.');
}
$db = Illuminate\Support\Facades\DB::connection();
App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db); // Before PDO: one loopback gr2:3306 connection.
$guard = new class extends App\Services\M11LocalTargetGuard {
    public function inspect(): array { return $this->windowsEvidence(3306); }
};
$physical = $guard->inspect();
$directory = $root.'/storage/app/seo-m52-local';
if (is_link($directory) || (!is_dir($directory) && !mkdir($directory, 0700, true))
    || $normal($directory) !== $normal($root).'/storage/app/seo-m52-local'
    || file_exists($directory.'/'.$name) || is_link($directory.'/'.$name)) {
    throw new RuntimeException('Private BEFORE directory/basename is unsafe or already exists.');
}
$json = static fn ($value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$digest = static fn ($value): string => App\Services\M26ContentPatch::digest($value);
$exclusive = static function (string $path, string $bytes) use ($directory, $normal): void {
    if ($normal(dirname($path)) !== $normal($directory) || is_link($path)
        || preg_match('/^before-v[1-9][0-9]*(?:-http-[a-f0-9]{64}\.html|\.json)$/D', basename($path)) !== 1) {
        throw new RuntimeException('Exclusive evidence path is outside the private allowlist.');
    }
    $handle = fopen($path, 'x');
    if ($handle === false) { throw new RuntimeException('Exclusive BEFORE evidence collision.'); }
    try {
        if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) {
            throw new RuntimeException('Evidence write is incomplete.');
        }
    } finally { fclose($handle); }
};
$fileHash = static function (string $path): string {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('Required source/evidence is missing or linked.'); }
    $sha = hash_file('sha256', $path);
    if (!is_string($sha)) { throw new RuntimeException('Source hash is unavailable.'); }
    return $sha;
};
$rows = static fn ($query): array => $query->get()->map(static fn ($row) => (array) $row)->all();
$orderedRows = static function (string $table, $query) use ($db, $rows): array {
    $columns = $db->getSchemaBuilder()->getColumnListing($table);
    foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
    return $rows($query);
};
$readOnly = static function (Closure $operation) use ($db) {
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try { return $operation(); } finally { $db->rollBack(); }
};
$bindings = [
    ['basename' => 'PassiveVoiceFormationRulesTheorySeeder', 'slug' => 'theory-passive-voice-formation-rules',
        'level' => 'A2', 'continuing_blocks' => 9],
    ['basename' => 'PassiveVoiceNegativesQuestionsTheorySeeder', 'slug' => 'theory-passive-voice-negatives-questions',
        'level' => 'B1', 'continuing_blocks' => 11],
    ['basename' => 'PassiveVoiceModalVerbsTheorySeeder', 'slug' => 'theory-passive-voice-modal-verbs',
        'level' => 'B1', 'continuing_blocks' => 11],
];
$capture = ['schema_version' => 1, 'base_sha' => '0e2ed40c438400e2f2a3992314220fdf206db9a7',
    'started_at' => gmdate('c'), 'served_root' => $root, 'source_worktree' => $worktree,
    'physical' => $physical, 'physical_hashes' => [], 'definitions' => [], 'pages' => [],
    'banks' => [], 'http' => [], 'shared_source_hashes' => []];
foreach (['C:/Program Files/xampp/apache/conf/httpd.conf', 'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
    'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', $root.'/public/index.php'] as $path) {
    $capture['physical_hashes'][$path] = $fileHash($path);
}
foreach (['app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
    'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',
    'app/Http/Controllers/TestJsV2Controller.php', 'app/Http/Controllers/PageController.php'] as $path) {
    $capture['shared_source_hashes'][$path] = ['root' => $fileHash($root.'/'.$path), 'worktree' => $fileHash($worktree.'/'.$path)];
}
$fingerprints = static function () use ($db, $digest): array {
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false); sort($tables);
    $out = [];
    foreach ($tables as $table) {
        $columns = $db->getSchemaBuilder()->getColumnListing($table); $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $hash = hash_init('sha256'); $count = 0;
        foreach ($query->cursor() as $record) { hash_update($hash, $digest((array) $record)."\n"); ++$count; }
        $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
    }
    return $out;
};
$questionSnapshot = static function (array $question) use ($db, $rows, $orderedRows): array {
    $snapshot = ['question' => $question];
    foreach (['answers' => 'question_answers', 'option_pivots' => 'question_option_question',
        'verb_hints' => 'verb_hints', 'hints' => 'question_hints', 'variants' => 'question_variants',
        'tags' => 'question_tag', 'marker_tags' => 'question_marker_tag'] as $key => $table) {
        $snapshot[$key] = $orderedRows($table, $db->table($table)->where('question_id', $question['id']));
    }
    usort($snapshot['answers'], static fn ($a, $b) => ($a['marker'] <=> $b['marker'])
        ?: (($a['position'] ?? 0) <=> ($b['position'] ?? 0)) ?: ($a['id'] <=> $b['id']));
    $optionIds = array_values(array_unique([...array_column($snapshot['answers'], 'option_id'),
        ...array_column($snapshot['option_pivots'], 'option_id'), ...array_column($snapshot['verb_hints'], 'option_id')]));
    $snapshot['options'] = $rows($db->table('question_options')->whereIn('id', $optionIds)->orderBy('id'));
    $byId = array_column($snapshot['options'], null, 'id');
    $snapshot['ordered_answer_instances'] = array_map(static fn ($answer) => [
        'id' => $answer['id'], 'marker' => $answer['marker'], 'position' => $answer['position'] ?? null,
        'option_id' => $answer['option_id'], 'option' => $byId[$answer['option_id']]['option'] ?? null,
    ], $snapshot['answers']);
    return $snapshot;
};
$relations = static function (array $page, array $blocks) use ($db, $rows, $orderedRows, $questionSnapshot): array {
    $ids = array_column($blocks, 'id'); $uuids = array_column($blocks, 'uuid'); $out = [];
    foreach (['page_tag' => ['page_id', [$page['id']]], 'page_category_tag' => ['page_category_id', [$page['page_category_id']]],
        'tag_text_block' => ['text_block_id', $ids], 'question_theory_text_blocks' => ['text_block_uuid', $uuids]] as $table => [$column, $values]) {
        $out[$table] = $orderedRows($table, $db->table($table)->whereIn($column, $values));
    }
    $tagIds = array_values(array_unique([...array_column($out['page_tag'], 'tag_id'),
        ...array_column($out['page_category_tag'], 'tag_id'), ...array_column($out['tag_text_block'], 'tag_id')]));
    $out['tags'] = $rows($db->table('tags')->whereIn('id', $tagIds)->orderBy('id'));
    $questions = $rows($db->table('questions')->where(function ($query) use ($out, $uuids) {
        $query->whereIn('uuid', array_column($out['question_theory_text_blocks'], 'question_uuid'))
            ->orWhereIn('theory_text_block_uuid', $uuids);
    })->orderBy('uuid'));
    $out['questions'] = $questions; $out['question_snapshots'] = [];
    foreach ($questions as $question) { $out['question_snapshots'][$question['uuid']] = $questionSnapshot($question); }
    $out['saved_grammar_test_questions'] = $orderedRows('saved_grammar_test_questions', $db->table('saved_grammar_test_questions')
        ->whereIn('question_uuid', array_column($questions, 'uuid')));
    return $out;
};
$resolver = new class extends App\Http\Controllers\PageController {
    public function __construct() {}
    public function categoryPath(App\Models\PageCategory $category): string { return $this->categorySlugPath($category); }
    public function titleFromSubtitle(string $body): ?string { return $this->extractLocalizedTitleFromSubtitle($body); }
};
$routePath = static function (string $name, array $parameters): array {
    $route = app('router')->getRoutes()->getByName($name);
    if (!$route || !in_array('GET', $route->methods(), true)) { throw new RuntimeException('Named local GET route is unavailable.'); }
    $uri = '/'.$route->uri();
    foreach ($parameters as $key => $value) { $uri = str_replace('{'.$key.'}', $value, $uri); }
    if (str_contains($uri, '{') || preg_match('~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D', $uri) !== 1) {
        throw new RuntimeException('Resolved URL is outside the exact Passive Basics local scope.');
    }
    $matched = app('router')->getRoutes()->match(Illuminate\Http\Request::create('http://gramlyze.loc'.$uri, 'GET'));
    if ($matched->getName() !== $name || $matched->getActionName() !== $route->getActionName()
        || in_array('App\\Http\\Middleware\\AdminAuthenticate', $matched->gatherMiddleware(), true)) {
        throw new RuntimeException('Local URI resolved to another route or an admin gate.');
    }
    return ['path' => $uri, 'url' => 'http://gramlyze.loc'.$uri, 'route_name' => $name, 'action' => $route->getActionName()];
};
$readOnly(static function () use ($db, $normal, $root, $worktree, $guard, $physical, &$capture,
    $bindings, $rows, $fileHash, $relations, $resolver, $routePath): void {
    $server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@hostname AS server, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
    foreach (['pid_file', 'datadir'] as $key) {
        if (!is_string($server[$key] ?? null) || preg_match('~^[A-Za-z]:/~D', str_replace('\\', '/', $server[$key])) !== 1) {
            throw new RuntimeException('MySQL evidence is not an absolute local Windows drive path.');
        }
    }
    $pid = trim((string) file_get_contents($server['pid_file']));
    if ($server['db'] !== 'gr2' || (int) $server['port'] !== 3306 || !str_starts_with($server['os'], 'Win')
        || !ctype_digit($pid) || !str_starts_with($normal($server['pid_file']), $normal($server['datadir']).'/')
        || strcasecmp($server['server'], gethostname()) !== 0) { throw new RuntimeException('Physical Windows MySQL identity differs.'); }
    $guard->assertEvidence($physical, $normal($root), (int) $pid, 3306);
    $capture['identity'] = App\Services\M49LocalTargetGuard::publicIdentity($db, $root.'/public');
    $capture['body_capacity'] = (array) $db->selectOne("SELECT DATA_TYPE AS kind, CHARACTER_OCTET_LENGTH AS max_bytes FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='text_blocks' AND COLUMN_NAME='body'");
    foreach ($bindings as $binding) {
        $owner = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\'.$binding['basename'];
        $path = 'database/seeders/Page_V3/PassiveVoice/Basics/'.$binding['basename'].'/definition.json';
        $sha = $fileHash($root.'/'.$path); $fileHash($worktree.'/'.$path);
        $bytes = file_get_contents($root.'/'.$path);
        if ($bytes !== file_get_contents($worktree.'/'.$path)) { throw new RuntimeException('Target BEFORE ROOT/WT definition bytes differ.'); }
        $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if ($definition['seeder']['class'] !== $owner || $definition['slug'] !== $binding['slug']
            || $definition['page']['locale'] !== 'uk' || $definition['type'] !== 'theory'
            || $definition['page']['category']['slug'] !== 'passive-voice'
            || count($definition['page']['blocks']) !== $binding['continuing_blocks']) {
            throw new RuntimeException('Actual source owner/locale/category/block inventory differs.');
        }
        $owners = $rows($db->table('pages')->where('seeder', $owner)->orderBy('id'));
        if (count($owners) !== 1) { throw new RuntimeException('Exact target Page owner is missing or ambiguous.'); }
        $page = $owners[0]; $chain = []; $seen = []; $id = $page['page_category_id'];
        while ($id) {
            if (isset($seen[$id]) || count($seen) >= 8) { throw new RuntimeException('Category ancestry is cyclic or too deep.'); }
            $seen[$id] = true; $categories = $rows($db->table('page_categories')->where('id', $id));
            if (count($categories) !== 1 || $categories[0]['language'] !== 'uk' || $categories[0]['type'] !== 'theory') {
                throw new RuntimeException('Category ancestry identity/locale differs.');
            }
            array_unshift($chain, $categories[0]); $id = $categories[0]['parent_id'];
        }
        if (array_column($chain, 'slug') !== ['passive-voice'] || $page['slug'] !== $binding['slug']
            || $page['title'] !== $definition['page']['title'] || $page['type'] !== 'theory'
            || $page['text'] !== $definition['page']['subtitle_text']) { throw new RuntimeException('Page/category BEFORE binding differs.'); }
        $blocks = $rows($db->table('text_blocks')->where('page_id', $page['id'])->orderBy('id'));
        $uk = array_values(array_filter($blocks, static fn ($block) => $block['locale'] === 'uk'));
        usort($uk, static fn ($a, $b) => $a['sort_order'] <=> $b['sort_order']);
        $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'],
            'level' => $definition['page']['subtitle_level'] ?? null, 'uuid_key' => $definition['page']['subtitle_uuid_key'] ?? 'subtitle'],
            ...$definition['page']['blocks']];
        if (count($uk) !== count($configs)) { throw new RuntimeException('UK row count differs from the complete actual source inventory.'); }
        foreach ($configs as $position => $config) {
            $row = $uk[$position];
            if ($row['seeder'] !== $owner || $row['page_id'] !== $page['id'] || $row['page_category_id'] !== $page['page_category_id']
                || (int) $row['sort_order'] !== $position || $row['uuid'] !== App\Support\M26DetailPackage::uuid($owner, $config, $position)) {
                throw new RuntimeException('Continuing row owner/order/UUID differs.');
            }
            foreach (['type', 'column', 'heading', 'level', 'css_class', 'body'] as $field) {
                if ($row[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('Continuing row/source field differs: '.$field); }
            }
            if ($config['type'] !== 'subtitle' && $config['type'] !== 'navigation-chips' && ($config['level'] ?? null) !== $binding['level']) {
                throw new RuntimeException('Existing technical block level differs.');
            }
        }
        $pageModel = App\Models\Page::query()->with('category')->where('id', $page['id'])->sole();
        $categoryPath = $resolver->categoryPath($pageModel->category);
        if ($categoryPath !== 'passive-voice') { throw new RuntimeException('Native category URL resolver differs from audited ancestry.'); }
        $targetRoute = $routePath('theory.show', ['categoryPath' => $categoryPath, 'pageSlug' => $page['slug']]);
        $courseRoute = $routePath('courses.theory.lesson', ['categoryPath' => $categoryPath, 'pageSlug' => $page['slug']]);
        $relationRows = $relations($page, $blocks);
        $capture['definitions'][$owner] = ['path' => $path, 'sha256' => $sha, 'bytes' => $bytes, 'definition' => $definition];
        $capture['pages'][$owner] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $blocks,
            'relations' => $relationRows, 'technical_level' => $binding['level'], 'continuing_blocks' => count($configs) - 1,
            'subtitle_extracted_title' => $resolver->titleFromSubtitle($definition['page']['subtitle_html']),
            'target_route' => $targetRoute, 'course_route' => $courseRoute,
            'pagewide_question_ids' => array_column($relationRows['questions'], 'id'), 'bank_inventory' => []];
        $byBank = [];
        foreach ($relationRows['questions'] as $question) {
            $key = $question['seeder']; $byBank[$key]['all'][] = $question;
            if ((string) $question['type'] === '4' && $question['level'] === $binding['level']) { $byBank[$key]['eligible'][] = $question; }
        }
        ksort($byBank);
        foreach ($byBank as $bank => $records) {
            $eligible = $records['eligible'] ?? [];
            usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            $inventory = ['identity' => $bank, 'level' => $binding['level'], 'pagewide_count' => count($records['all']),
                'pagewide_ids' => array_column($records['all'], 'id'), 'eligible_count' => count($eligible),
                'eligible_ids_uuid_order' => array_column($eligible, 'id'), 'first_five' => []];
            foreach (array_slice($eligible, 0, 5) as $question) {
                $inventory['first_five'][] = $relationRows['question_snapshots'][$question['uuid']];
            }
            $capture['pages'][$owner]['bank_inventory'][$bank] = $inventory;
            $capture['banks'][$owner][$bank] = $inventory;
        }
    }
    foreach (['text_blocks', 'questions', 'question_answers', 'question_options', 'question_option_question',
        'question_theory_text_blocks', 'question_hints', 'verb_hints', 'question_variants', 'sources', 'categories'] as $table) {
        $capture['schema'][$table] = $db->getSchemaBuilder()->getColumns($table);
    }
});
$capture['sql_before'] = $readOnly($fingerprints);
$inventory = static function (array $folders, Closure $exclude) use ($root, $fileHash): array {
    $out = [];
    foreach ($folders as $folder) {
        if (!is_dir($root.'/'.$folder) || is_link($root.'/'.$folder)) { throw new RuntimeException('Baseline inventory directory is missing/linked.'); }
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$folder, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isLink()) { throw new RuntimeException('Baseline inventory symlink refused.'); }
            if (!$file->isFile()) { continue; }
            $path = substr(str_replace('\\', '/', $file->getPathname()), strlen($root) + 1);
            if (!$exclude($path)) { $out[$path] = $fileHash($file->getPathname()); }
        }
    }
    ksort($out); return $out;
};
$capture['visual_hashes'] = $inventory(['resources/views', 'resources/css', 'resources/js', 'public/js'], static fn () => false);
$targetPaths = array_column($capture['definitions'], 'path');
$capture['frozen_hashes'] = $inventory(['docs/content', 'docs/reports', 'database/content-patches', 'database/seeders/Page_V3'],
    static fn ($path) => preg_match('~(?:^|/)m52[-./]~i', $path) === 1 || str_contains($path, '/seo-m52-') || in_array($path, $targetPaths, true));
foreach (['app/Support/M*.php', 'app/Services/M*.php', 'database/seeders/V3/Polyglot/*/definition.json',
    'database/seeders/V3/TheoryLinks/data/*.json'] as $pattern) {
    foreach (glob($root.'/'.$pattern) ?: [] as $file) {
        if (str_starts_with(basename($file), 'M52')) { continue; }
        $capture['frozen_hashes'][substr(str_replace('\\', '/', $file), strlen($root) + 1)] = $fileHash($file);
    }
}
ksort($capture['frozen_hashes']);
foreach ($capture['pages'] as $owner => $page) {
    foreach (['theory' => $page['target_route'], 'course' => $page['course_route']] as $kind => $route) {
        $curl = curl_init($route['url']); $robots = '';
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_MAXREDIRS => 0,
            CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 45, CURLOPT_PROXY => '',
            CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: text/html'],
            CURLOPT_HEADERFUNCTION => static function ($handle, $header) use (&$robots): int {
                if (stripos($header, 'X-Robots-Tag:') === 0) { $robots = trim(substr($header, 13)); }
                return strlen($header);
            }]);
        try {
            $html = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            if (!is_string($html) || curl_getinfo($curl, CURLINFO_PRIMARY_IP) !== '127.0.0.1'
                || curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) !== $route['url'] || curl_getinfo($curl, CURLINFO_REDIRECT_COUNT) !== 0) {
                throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target.');
            }
        } finally { curl_close($curl); }
        if ($kind === 'theory' && $status !== 200) { throw new RuntimeException('Exact local theory BEFORE is unavailable.'); }
        if ($kind === 'course' && $status !== 200 && !in_array($status, [301, 302, 303, 307, 308, 401, 403, 404], true)) {
            throw new RuntimeException('Course BEFORE failed outside a recognized access/missing gate.');
        }
        $file = substr($name, 0, -5).'-http-'.hash('sha256', $route['path']).'.html';
        $exclusive($directory.'/'.$file, $html);
        $record = $route + ['kind' => $kind, 'identity' => $owner, 'status' => $status, 'file' => $file,
            'sha256' => hash('sha256', $html), 'robots_header' => $robots, 'access_gate_not_bypassed' => true];
        if ($status === 200) {
            $dom = new DOMDocument('1.0', 'UTF-8'); $oldErrors = libxml_use_internal_errors(true);
            try { $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET); }
            finally { libxml_clear_errors(); libxml_use_internal_errors($oldErrors); }
            $xpath = new DOMXPath($dom);
            $text = static fn (string $query): string => trim(preg_replace('/\s+/u', ' ', $xpath->query($query)->item(0)?->textContent ?? ''));
            $record['h1_count'] = $xpath->query('//h1')->length;
            $record['h1'] = $text('//h1');
            $record['raw_page_title'] = $page['page']['title'];
            $record['subtitle_extracted_title'] = $page['subtitle_extracted_title'];
            $record['title'] = $text('//title');
            $record['breadcrumb'] = $text('//nav[@aria-label][contains(@class,"mb-8")]');
            $record['canonical'] = $xpath->query('//link[@rel="canonical"]')->item(0)?->getAttribute('href');
            $record['description'] = $xpath->query('//meta[@name="description"]')->item(0)?->getAttribute('content');
            $record['robots_meta'] = $xpath->query('//meta[@name="robots"]')->item(0)?->getAttribute('content');
            $record['learning_resources'] = []; $record['anchors'] = [];
            foreach ($xpath->query('//*[@id]') as $node) { $record['anchors'][] = $node->getAttribute('id'); }
            foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
                $data = json_decode($script->textContent, true, flags: JSON_THROW_ON_ERROR);
                $visit = static function ($value) use (&$visit, &$record): void {
                    if (!is_array($value)) { return; }
                    if (in_array('LearningResource', (array) ($value['@type'] ?? []), true)) {
                        $record['learning_resources'][] = array_intersect_key($value,
                            array_flip(['@type', 'name', 'headline', 'description', 'url', 'inLanguage']));
                    }
                    foreach ($value as $child) { if (is_array($child)) { $visit($child); } }
                };
                $visit($data);
            }
            if ($kind === 'theory' && ($record['h1_count'] !== 1 || $record['h1'] === '' || $record['canonical'] === null)) {
                throw new RuntimeException('Actual theory H1/canonical identity is incomplete.');
            }
            $courseNodes = $xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-design ")]');
            $record['course_theory_html_sha256'] = $kind === 'course' && $courseNodes->length === 1
                ? hash('sha256', $dom->saveHTML($courseNodes->item(0))) : null;
        }
        $capture['http'][] = $record;
    }
}
$capture['sql_after_http'] = $readOnly($fingerprints);
if ($capture['sql_before'] !== $capture['sql_after_http']) { throw new RuntimeException('SQL changed during anonymous BEFORE GETs.'); }
foreach ($capture['physical_hashes'] as $path => $sha) {
    if ($fileHash($path) !== $sha) { throw new RuntimeException('Physical target changed during capture.'); }
}
$capture['finished_at'] = gmdate('c');
$exclusive($directory.'/'.$name, $json($capture));
echo $json(['status' => 'read-only-before', 'file' => $name, 'sha256' => $fileHash($directory.'/'.$name),
    'http_200' => count(array_filter($capture['http'], static fn ($record) => $record['status'] === 200)),
    'http_total' => count($capture['http']), 'sql_tables' => count($capture['sql_before']),
    'visual_files' => count($capture['visual_hashes']), 'frozen_files' => count($capture['frozen_hashes']),
    'pages' => array_map(static fn ($record) => ['id' => $record['page']['id'],
        'category_id' => $record['page']['page_category_id'], 'slug' => $record['page']['slug'],
        'continuing_blocks' => $record['continuing_blocks'], 'all_locale_rows' => count($record['blocks']),
        'eligible_bank_pools' => array_map(static fn ($bank) => $bank['eligible_count'], $record['bank_inventory'])], $capture['pages']),
    'actual_titles' => array_map(static fn ($record) => ['url' => $record['url'], 'h1' => $record['h1'] ?? null,
        'raw_page_title' => $record['raw_page_title'] ?? null], $capture['http'])]);
