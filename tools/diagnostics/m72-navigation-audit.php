<?php

/**
 * Explicit read-only M72 navigation AFTER, adapted from the accepted private M71 audit.
 * Usage: php tools/diagnostics/m72-navigation-audit.php
 *   navigation-after-v1.json final-readonly-v1.json <reviewed-final-raw-sha256>
 * No invocation/apply hooks. SELECT/READ ONLY/ROLLBACK, anonymous fixed-loopback GET.
 * New output basenames are required after an interrupted run; captures are never overwritten.
 */
$root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/seo-m72-local';
$name = $argv[1] ?? ''; $finalName = $argv[2] ?? ''; $finalSha = $argv[3] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 4
    || preg_match('/^navigation-after-v[1-9][0-9]*\.json$/D', $name) !== 1
    || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $finalName) !== 1
    || preg_match('/^[a-f0-9]{64}$/D', $finalSha) !== 1) {
    throw new RuntimeException('Explicit scoped AFTER and reviewed independent final receipt required.');
}
$failureHandler = static function (Throwable $error): void {
    $message = $error instanceof RuntimeException && !$error instanceof PDOException
        ? $error->getMessage() : get_class($error).' (audit incomplete; no successful receipt)';
    fwrite(STDERR, 'M72 navigation: '.$message."\n"); exit(1);
};
set_exception_handler($failureHandler);
$normal = static function (string $path): string {
    $real = realpath($path); if ($real === false) { throw new RuntimeException('Required local path unavailable.'); }
    return rtrim(strtolower(str_replace('\\', '/', $real)), '/');
};
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || is_link($dir) || $normal($dir) !== $normal($root).'/storage/app/seo-m72-local'
    || file_exists($dir.'/'.$name) || is_link($dir.'/'.$name)) {
    throw new RuntimeException('Private served-ROOT directory/output path differs or already exists.');
}
$json = static fn ($value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
$readPinned = static function (string $basename, string $sha) use ($dir): array {
    $path = $dir.'/'.$basename;
    if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
        throw new RuntimeException('Independent private receipt missing, linked or changed: '.$basename);
    }
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$navBeforeSha = '0aae0233102075e5b5a973af4c1c4e38ff0ca9ad7540bd51384abff5017493a4';
$workingBeforeSha = '33cd475c775288da255c3584aa3b9ef665255a1f31abc14f4df294ef749a690d';
$before = $readPinned('navigation-before-v1.json', $navBeforeSha);
$workingBefore = $readPinned('before-v1.json', $workingBeforeSha);
$final = $readPinned($finalName, $finalSha);
if (($workingBefore['served_root'] ?? null) !== $root
    || ($workingBefore['sql_before'] ?? null) !== ($workingBefore['sql_after_http'] ?? null)
    || ($final['independent_before_sha256'] ?? null) !== $workingBeforeSha
    || ($final['runtime'] ?? null) !== ($workingBefore['identity'] ?? null)
    || ($final['normalized_before'] ?? null) !== ($workingBefore['sql_before'] ?? null)
    || ($final['all_old_rows_and_timestamps_exact'] ?? null) !== true
    || ($final['independent_before_projection_exact'] ?? null) !== true
    || ($final['reviewed_protected_projection_exact'] ?? null) !== true
    || ($final['db_writes'] ?? null) !== 0 || ($final['http_requests'] ?? null) !== 0
    || !is_array($final['sql_after'] ?? null)) {
    throw new RuntimeException('Reviewed final receipt does not reconstruct the independent complete working BEFORE.');
}
require $root.'/vendor/autoload.php'; $app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
// Laravel installs its own CLI exception renderer while booting. A failed private
// audit must still return nonzero and must never be mistaken for a PASS receipt.
set_exception_handler($failureHandler);
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)) {
    throw new RuntimeException('Actual application/environment ROOT differs.');
}
$nativeSourcePins = [
    'app/Services/Theory/TextBlockToQuestionsMatcherService.php' => '73ff24952b60282e22596f0fb9d28a7cfd18f3ec7f7fa2f70c2fe9aa0c4e7f60',
    'app/Services/Theory/TagMatch/TagMatchScorer.php' => 'fd670e085346aafb79fc99ccc798afb6ace713d5f9b0726a2a44dc8c896136c9',
    'app/Support/PracticeCompletionLinkedPractice.php' => 'de7d97873415e7d658e963f61e9ca9e2a913305f24aca0eddd421dec1456520a',
    'app/Http/Controllers/PageController.php' => '491672fed84cc11a690f24d9111999049e258ad5a06f881149495a102b8d1659',
];
foreach ($nativeSourcePins as $path => $sha) {
    if (!is_file($root.'/'.$path) || is_link($root.'/'.$path) || !hash_equals($sha, hash_file('sha256', $root.'/'.$path))) {
        throw new RuntimeException('Reviewed native selection source differs: '.$path);
    }
}
if (($workingBefore['shared_source_hashes']['app/Http/Controllers/PageController.php']['root'] ?? null)
    !== $nativeSourcePins['app/Http/Controllers/PageController.php']) {
    throw new RuntimeException('Native controller source does not match the independently captured working BEFORE.');
}
// Reverse only the two new M72 selector dispatch lines from an independently
// pinned accepted-base fixture. Do not normalize or edit runtime files.
$fixturePath = $root.'/tests/Fixtures/m72-owned-source-hunks.json';
if (!is_file($fixturePath) || is_link($fixturePath)
    || hash_file('sha256', $fixturePath) !== 'c4a27b89f25a97bd4a0596ac061285843dbef1d638c5d8a3f752e28b17493134') {
    throw new RuntimeException('Reviewed independent M72 lineage fixture differs.');
}
$fixture = json_decode(file_get_contents($fixturePath), true, flags: JSON_THROW_ON_ERROR);
if (($fixture['base_sha'] ?? null) !== '2bcd468b25af3c44dcf34ccee0e012f3f683335d') {
    throw new RuntimeException('Exact independent accepted base differs.');
}
$oldNativeSource = [];
foreach ($fixture['files'] as $file) {
    if (!in_array($file['path'], ['app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php'], true)) { continue; }
    $lf = str_replace("\r\n", "\n", file_get_contents($root.'/'.$file['path']));
    if (hash('sha256', $lf) !== $file['after_lf_sha256'] || count($file['hunks']) !== 1) {
        throw new RuntimeException('Independent current selector LF source differs.');
    }
    foreach ($file['hunks'] as $hunk) {
        if (substr_count($lf, $hunk['after']) !== 1) { throw new RuntimeException('One exact M72 dispatch hunk required.'); }
        $lf = str_replace($hunk['after'], $hunk['before'], $lf);
    }
    if (hash('sha256', $lf) !== $file['base_lf_sha256']) {
        throw new RuntimeException('Selector source is not exact accepted base plus the reviewed M72 line.');
    }
    $oldNativeSource[$file['path']] = ['base_lf_sha256' => $file['base_lf_sha256']];
}
if (count($oldNativeSource) !== 2) { throw new RuntimeException('Exactly two selector lineage entries required.'); }
foreach ([App\Services\Theory\TextBlockToQuestionsMatcherService::class => 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
    App\Services\Theory\TagMatch\TagMatchScorer::class => 'app/Services/Theory/TagMatch/TagMatchScorer.php',
    App\Support\PracticeCompletionLinkedPractice::class => 'app/Support/PracticeCompletionLinkedPractice.php'] as $class => $path) {
    if ($normal((new ReflectionClass($class))->getFileName()) !== $normal($root.'/'.$path)) {
        throw new RuntimeException('Loaded native selection class is not the pinned served ROOT source.');
    }
}
$db = Illuminate\Support\Facades\DB::connection(); App\Services\M72LocalTargetGuard::assertConfiguredDatabase($db);
$guard = new App\Services\M72LocalTargetGuard; $physical = $guard->physicalEvidence();
$server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach (['datadir', 'pid_file'] as $key) {
    if (!is_string($server[$key] ?? null) || preg_match('~^[A-Za-z]:/~D', str_replace('\\', '/', $server[$key])) !== 1) {
        throw new RuntimeException('MySQL evidence is not an absolute local Windows path.');
    }
}
$dataPath = $normal($server['datadir']); $pidPath = $normal($server['pid_file']);
$mysqlPid = trim((string) file_get_contents($server['pid_file']));
if (($server['db'] ?? null) !== 'gr2' || (int) ($server['port'] ?? 0) !== 3306
    || !str_starts_with($server['os'] ?? '', 'Win') || !ctype_digit($mysqlPid)
    || !str_starts_with($pidPath, $dataPath.'/') || str_starts_with($dataPath, '//')) {
    throw new RuntimeException('Inspected physical MySQL identity differs.');
}
$guard->assertEvidence($physical, $normal($root), (int) $mysqlPid, 3306);
$runtime = App\Services\M72LocalTargetGuard::publicIdentity($db, $root.'/public');
if ($runtime !== $workingBefore['identity']) { throw new RuntimeException('Current runtime identity differs from reviewed BEFORE/final proof.'); }
$readOnly = static function (Closure $operation) use ($db) {
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try { return $operation(); } finally { $db->rollBack(); }
};
$digest = static fn ($value): string => App\Services\M26ContentPatch::digest($value);
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
$allTablesBeforeHttp = $readOnly($fingerprints);
if ($allTablesBeforeHttp !== $final['sql_after']) { throw new RuntimeException('Current SQL is not the independently reviewed exact AFTER.'); }
[$packageBefore, $patch, $master, $contract] = App\Support\M72PartitivesNegativesGeography::load($root);
App\Support\M72PartitivesNegativesGeography::validate($packageBefore, $patch, $master, $contract);
if (array_column($patch['targets'], 'identity') !== App\Support\M72PartitivesNegativesGeography::OWNERS
    || array_column(array_column($patch['targets'], 'page_binding'), 'id') !== [88, 87, 83]) {
    throw new RuntimeException('Exact three M72 target bindings differ.');
}
$targets = [88, 87, 83]; $targetSources = array_column($patch['targets'], 'path');
$targetUrls = array_map(static fn ($target) => parse_url($target['local_url'], PHP_URL_PATH), $patch['targets']);
$resolver = new class extends App\Http\Controllers\PageController {
    public function __construct() {}
    public function path($category) { return $this->categorySlugPath($category); }
};
$families = [
    'NounsArticlesQuantity' => ['PluralNouns', 'CountableUncountableNouns', 'Articles', 'ZeroArticle', 'Quantifiers', 'Partitives', 'NoNoneNeitherEither', 'ArticlesWithGeographicalNames', 'AdvancedArticlesGenericReference', 'IrregularPlurals', 'CollectiveNouns'],
    'Adjectives' => ['DegreesOfComparison', 'ComparativeVsSuperlative', 'EqualityComparisonAsAsNotAsAs', 'VsAdverbs', 'OrderOfAdjectives', 'EdIngAdjectives', 'Intensifiers'],
];
$out = ['schema_version' => 1, 'before_navigation_sha256' => $navBeforeSha,
    'working_before_sha256' => $workingBeforeSha, 'reviewed_final_file' => $finalName,
    'reviewed_final_sha256' => $finalSha, 'runtime' => $runtime, 'navigation' => [], 'sources' => [], 'categories' => [], 'http' => [],
    'db_writes' => 0, 'browser_visibility' => 'not_checked', 'interaction' => 'not_checked'];
$out['native_selection_source_proof'] = ['accepted_before_base' => '2bcd468b25af3c44dcf34ccee0e012f3f683335d',
    'current_raw_sha256' => $nativeSourcePins, 'accepted_before_lf_sha256' => array_map(static fn ($pin) => $pin['base_lf_sha256'], $oldNativeSource),
    'lineage' => 'Current whole-file LF selector code after reversing only the two exact M72 additions equals independently pinned accepted-base blobs. The existing M71 dispatch is retained. PageController also equals raw working BEFORE; this is not a fabricated ROOT BEFORE hash.'];
$readOnly(static function () use ($families, $root, $db, $resolver, &$out): void {
    foreach ($families as $family => $names) {
        foreach ($names as $name) {
            $basename = $family.$name.'TheorySeeder'; $path = 'database/seeders/Page_V3/'.$family.'/'.$basename.'/definition.json';
            if (!is_file($root.'/'.$path) || is_link($root.'/'.$path)) { throw new RuntimeException('Neighbor source missing/linked.'); }
            $definition = json_decode(file_get_contents($root.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
            $owner = 'Database\\Seeders\\Page_V3\\'.$family.'\\'.$basename;
            if (($definition['seeder']['class'] ?? null) !== $owner || ($definition['content_type'] ?? null) !== 'page'
                || ($definition['page']['locale'] ?? null) !== 'uk') { throw new RuntimeException('Neighbor source identity differs.'); }
            $page = App\Models\Page::with('category')->where('seeder', $owner)->sole();
            if ($page->slug !== $definition['slug'] || $page->type !== 'theory'
                || $page->category?->slug !== $definition['page']['category']['slug'] || $page->category?->language !== 'uk') {
                throw new RuntimeException('Neighbor SQL binding differs.');
            }
            $url = route('theory.show', ['categoryPath' => $resolver->path($page->category), 'pageSlug' => $page->slug], false);
            if (preg_match('~^/theory/(?:imennyky-artykli-ta-kilkist|prykmetniky-ta-pryslinknyky)/[a-z0-9-]+$~D', $url) !== 1) {
                throw new RuntimeException('Native neighbor resolver leaves the exact local category scope.');
            }
            $out['navigation'][$basename] = ['identity' => $owner, 'id' => $page->id, 'title' => $page->getRawOriginal('title'), 'url' => $url];
            $out['sources'][$path] = hash_file('sha256', $root.'/'.$path);
        }
    }
    foreach ([52, 16] as $id) {
        $category = App\Models\PageCategory::findOrFail($id);
        $out['categories'][$category->slug] = ['row' => $category->getAttributes(),
            'all_locale_learning' => $db->table('text_blocks')->where('page_category_id', $id)->whereNull('page_id')->orderBy('id')->get()->all(),
            'url' => route('theory.category', ['category' => $category->slug], false)];
    }
});
if ($out['navigation'] !== $before['navigation'] || json_decode(json_encode($out['categories'], JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR) !== $before['categories']) {
    throw new RuntimeException('Neighbor identity/URLs/raw titles or all-locale category learning changed.');
}
foreach ($out['sources'] as $path => $sha) {
    if (!in_array($path, $targetSources, true) && ($before['sources'][$path] ?? null) !== $sha) {
        throw new RuntimeException('Non-target source changed: '.$path);
    }
}
foreach ($patch['targets'] as $target) {
    if (json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR) !== $target['after']) {
        throw new RuntimeException('Current target definition does not equal its independently frozen M72 projection.');
    }
}
$routes = [];
foreach ($out['navigation'] as $item) { $routes[$item['url']] = ['kind' => 'page', 'target' => in_array($item['id'], $targets, true), 'page_id' => $item['id'], 'identity' => $item['identity']]; }
foreach ($out['categories'] as $item) { $routes[$item['url']] = ['kind' => 'category', 'target' => false]; }
if (count($routes) !== 20 || count(array_filter($routes, static fn ($scope) => $scope['target'])) !== 3
    || array_diff($targetUrls, array_keys($routes)) !== []) { throw new RuntimeException('Twenty routes / three exact targets inventory differs.'); }
foreach ($master['lessons'] as $lesson) {
    foreach ($lesson['blocks'] as $block) {
        if ($block['type'] !== 'navigation-chips') { continue; }
        foreach ($block['data']['items'] as $item) {
            if (!isset($routes[$item['url'] ?? ''])) { throw new RuntimeException('M72 navigation destination is not an independently resolver-derived capture target.'); }
        }
    }
}
$prefix = substr($name, 0, -5);
foreach ($routes as $path => $scope) {
    $output = $dir.'/'.$prefix.'-'.hash('sha256', $path).'.html';
    if (file_exists($output) || is_link($output)) { throw new RuntimeException('Exclusive capture collision; use a fresh versioned output basename.'); }
}
$exclusive = static function (string $basename, string $bytes) use ($dir): void {
    if (preg_match('/^navigation-after-v[1-9][0-9]*(?:-[a-f0-9]{64}\.html|\.json)$/D', $basename) !== 1
        || is_link($dir.'/'.$basename)) { throw new RuntimeException('Private output outside the finite basename scope.'); }
    $handle = fopen($dir.'/'.$basename, 'x'); if ($handle === false) { throw new RuntimeException('Exclusive output collision.'); }
    try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('Incomplete private evidence write.'); } }
    finally { fclose($handle); }
};
$extract = static function (string $bytes, string $kind): array {
    $dom = new DOMDocument('1.0', 'UTF-8'); $old = libxml_use_internal_errors(true);
    try { $loaded = $dom->loadHTML('<?xml encoding="UTF-8">'.$bytes, LIBXML_NONET); }
    finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
    if (!$loaded) { throw new RuntimeException('Captured HTML cannot be parsed.'); }
    $xp = new DOMXPath($dom);
    $nodes = $xp->query($kind === 'category' ? '//*[contains(concat(" ",normalize-space(@class)," ")," theory-category-learning ")]' : '//*[@data-theory-main]');
    if ($nodes->length !== 1) { throw new RuntimeException('Exactly one captured learning boundary required.'); }
    $get = static fn (string $query): string => trim($xp->query($query)->item(0)?->nodeValue ?? '');
    return [$dom->saveHTML($nodes->item(0)), ['h1' => $get('//h1'), 'title' => $get('//title'),
        'canonical' => $get('//link[@rel="canonical"]/@href'), 'robots' => $get('//meta[@name="robots"]/@content'),
        'description' => $get('//meta[@name="description"]/@content')]];
};
$normalizeRandom = static function (string $html): array {
    if (preg_match_all('/^ {16}questions: (\[[^\r\n]*\]),?\r?$/m', $html, $matches) !== 1) { throw new RuntimeException('Exactly one old native question array required.'); }
    $questions = json_decode($matches[1][0], true, flags: JSON_THROW_ON_ERROR);
    if (!array_is_list($questions) || count($questions) !== 5 || count(array_unique(array_column($questions, 'id'))) !== 5) {
        throw new RuntimeException('Old native five-question array is short or repeats IDs.');
    }
    $html = str_replace($matches[1][0], '[]', $html);
    return [$html, $questions, 0]; // No markup, metadata, hint or other content exclusion.

};
$oldHttp = array_column($before['http'], null, 'path'); $full = 0; $static = 0;
$randomPaths = ['advanced-articles-generic-reference-the-rich-a-tiger-people'];
foreach ($routes as $path => $scope) {
    $old = $oldHttp[$path] ?? null;
    if ($old === null || ($old['status'] ?? null) !== 200 || $old['kind'] !== $scope['kind'] || $old['target'] !== $scope['target']
        || ($old['file'] ?? null) !== 'navigation-before-'.hash('sha256', $path).'.html') { throw new RuntimeException('Independent route/capture binding differs.'); }
    $beforePath = $dir.'/'.$old['file'];
    if (!is_file($beforePath) || is_link($beforePath) || !hash_equals($old['body_sha256'], hash_file('sha256', $beforePath))) { throw new RuntimeException('Independent raw BEFORE capture changed.'); }
    [$oldLearning, $oldFacts] = $extract(file_get_contents($beforePath), $scope['kind']);
    if (hash('sha256', $oldLearning) !== $old['learning_sha256'] || $oldFacts['h1'] !== $old['h1']) { throw new RuntimeException('Independent parsed BEFORE facts changed.'); }
    echo $json(['checking' => $path]); $url = 'http://gramlyze.loc'.$path; $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_MAXREDIRS => 0,
        CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 45, CURLOPT_HTTPGET => true,
        CURLOPT_PROXY => '', CURLOPT_NOPROXY => '*', CURLOPT_NETRC => CURL_NETRC_IGNORED, CURLOPT_COOKIE => '',
        CURLOPT_PROTOCOLS => CURLPROTO_HTTP, CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1']]);
    try {
        $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        if (!is_string($body) || $status !== 200 || curl_getinfo($curl, CURLINFO_PRIMARY_IP) !== '127.0.0.1'
            || curl_getinfo($curl, CURLINFO_REDIRECT_COUNT) !== 0 || curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) !== $url) {
            throw new RuntimeException('Exact anonymous fixed-loopback GET failed: '.$path);
        }
    } finally { curl_close($curl); }
    [$learning, $facts] = $extract($body, $scope['kind']); $file = $prefix.'-'.hash('sha256', $path).'.html';
    $exclusive($file, $body);
    $row = ['path' => $path, 'status' => $status] + $scope + ['file' => $file, 'body_sha256' => hash('sha256', $body),
        'before_learning_sha256' => hash('sha256', $oldLearning), 'learning_sha256' => hash('sha256', $learning), 'facts' => $facts];
    foreach (['h1', 'title', 'canonical', 'robots'] as $field) { if ($facts[$field] !== $oldFacts[$field]) { throw new RuntimeException('Page identity metadata changed: '.$path.' '.$field); } }
    if ($scope['target']) {
        $index = array_search($scope['identity'], App\Support\M72PartitivesNegativesGeography::OWNERS, true);
        if ($index === false) { throw new RuntimeException('Rendered target identity outside M72.'); }
        $navBlocks = array_values(array_filter($master['lessons'][$index]['blocks'], static fn ($b) => $b['type'] === 'navigation-chips'));
        if (count($navBlocks) !== 1) { throw new RuntimeException('Exactly one frozen target navigation block required.'); }
        $targetDom = new DOMDocument('1.0', 'UTF-8'); $previous = libxml_use_internal_errors(true);
        try { $targetDom->loadHTML('<?xml encoding="UTF-8">'.$learning, LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
        $targetXp = new DOMXPath($targetDom);
        $navs = $targetXp->query('//nav[p[normalize-space(.)="Продовжуй навчання"]]');
        if ($navs->length !== 1) { throw new RuntimeException('Exactly one native target resource navigation required.'); }
        $items = $targetXp->query('./div/*', $navs->item(0)); $expectedItems = $navBlocks[0]['data']['items'];
        if ($items->length !== count($expectedItems) || count($expectedItems) !== 9) {
            throw new RuntimeException('Exact nine native target learning destinations differ.');
        }
        $links = []; $current = [];
        foreach ($expectedItems as $j => $expected) {
            $node = $items->item($j);
            if (trim($node->textContent) !== $expected['label'] || !isset($routes[$expected['url']])) {
                throw new RuntimeException('Rendered resource title or independently derived destination differs.');
            }
            if ($expected['current']) {
                if ($node->nodeName !== 'span' || $expected['url'] !== $path) { throw new RuntimeException('Exact active resource owner differs.'); }
                $current[] = $expected['url'];
            } else {
                if ($node->nodeName !== 'a' || $node->getAttribute('href') !== $expected['url']) { throw new RuntimeException('Native resource anchor differs.'); }
                $links[] = $node->getAttribute('href');
            }
        }
        if ($current !== [$path] || count($links) !== 8) { throw new RuntimeException('Eight links plus current target resource required.'); }
        $row['rendered_learning_resource_links'] = $links;
        $row['rendered_current_resource'] = $current[0];
        $row['comparison'] = 'Target learning/description changed intentionally; eight real resource links and current chip equal frozen native data. Content and browser acceptance are separate.';
    }
    else {
        if ($facts !== $oldFacts) { throw new RuntimeException('Non-target metadata changed: '.$path); }
        if ($oldLearning === $learning) { ++$full; $row['comparison'] = 'Exact full learning DOM; this is not full raw-page HTML equality.'; }
        else {
            if (!in_array(basename($path), $randomPaths, true)) { throw new RuntimeException('Unexpected non-target learning DOM drift: '.$path); }
            [$a, $oldQuestions, $oldHints] = $normalizeRandom($oldLearning);
            [$b, $newQuestions, $newHints] = $normalizeRandom($learning);
            if ($a !== $b) { throw new RuntimeException('Non-target changed beyond the single validated Generic Reference random JSON array: '.$path); }
            $oldById = array_column($oldQuestions, null, 'id'); $overlap = 0;
            foreach ($newQuestions as $question) {
                if (isset($oldById[$question['id']])) {
                    if ($question !== $oldById[$question['id']]) { throw new RuntimeException('Same-ID old native question payload changed: '.$path); }
                    ++$overlap;
                }
            }
            $bindingFacts = $readOnly(static function () use ($db, $scope, $oldQuestions, $newQuestions): array {
                $ids = array_values(array_unique([...array_column($oldQuestions, 'id'), ...array_column($newQuestions, 'id')]));
                $rows = $db->table('questions')->whereIn('id', $ids)->get()->keyBy('id');
                if ($rows->count() !== count($ids)) { throw new RuntimeException('Selected old native question ID is absent.'); }
                $ownerBlocks = App\Models\TextBlock::query()->where('page_id', $scope['page_id'])->where('seeder', $scope['identity'])->where('locale', 'uk')->orderBy('sort_order')->get();
                $practices = $ownerBlocks->where('type', 'practice-set')->values();
                if ($practices->count() !== 1) { throw new RuntimeException('Legacy random array must bind to exactly one UK owner practice block.'); }
                $storedPractice = $practices->sole();
                $practice = App\Support\PracticeCompletionLinkedPractice::matchingBlock($storedPractice, true);
                $matcher = app(App\Services\Theory\TextBlockToQuestionsMatcherService::class);
                $invoke = static fn (string $method, ...$args) => (new ReflectionMethod($matcher, $method))->invoke($matcher, ...$args);
                $config = $invoke('linkedPracticeConfig', $practice);
                $mode = ($config['source'] ?? null) === 'theory_links' ? 'theory_links' : 'legacy_tag_fallback';
                $window = [];
                $candidateCount = null;
                if ($mode === 'legacy_tag_fallback') {
                    // Only the untouched Generic Reference legacy page can vary its native random array. It retains its existing selection mode.
                    // Use the existing deterministic native candidate/scoring
                    // methods, not a blanket exemption for unrelated IDs. The
                    // random draw itself is intentionally not rerun/replaced.
                    if ($storedPractice->getAttributes() !== $practice->getAttributes()) {
                        throw new RuntimeException('Legacy tag-fallback practice unexpectedly received a presentation clone.');
                    }
                    $practice->load('tags:id,name');
                    $tags = $practice->tags;
                    if ($tags->isEmpty()) { throw new RuntimeException('Native tag-fallback practice has no tags.'); }
                    $candidates = $invoke('loadCandidateQuestions', $tags->pluck('id')->all(), [], $practice->level);
                    $candidateCount = $candidates->count();
                    $scorer = app(App\Services\Theory\TagMatch\TagMatchScorer::class);
                    $normalizedBlock = $scorer->normalizeTags($tags->pluck('name')->all());
                    $scored = [];
                    foreach ($candidates as $candidate) {
                        $normalizedQuestion = $scorer->normalizeTags($candidate->tags->pluck('name')->all());
                        if (!$invoke('passesTagCoverage', $normalizedBlock, $normalizedQuestion)) { continue; }
                        $score = $scorer->scoreCollections($tags, $candidate->tags);
                        if ($score['skip']) { continue; }
                        $scored[] = ['question' => $candidate,
                            'score' => round($invoke('applyPriorityBonuses', $tags, $candidate, $score['score']), 2),
                            'matched_tag_ids' => $score['matched_tag_ids'], 'matched_normalized_tags' => $score['matched_normalized_tags']];
                    }
                    usort($scored, static fn ($a, $b) => $b['score'] <=> $a['score']);
                    foreach ($invoke('topScoringWindow', $scored, 5) as $candidate) {
                        $window[$candidate['question']->id] = $candidate;
                    }
                    if (count($window) < 5) { throw new RuntimeException('Native deterministic tag-fallback scoring window contains fewer than five candidates.'); }
                    foreach ($ids as $id) {
                        if (!isset($window[$id])) { throw new RuntimeException('Old/new selected ID is not in the actual native tag-fallback top scoring window.'); }
                    }
                }
                $blocks = $ownerBlocks->pluck('uuid')->all();
                $linked = $db->table('question_theory_text_blocks')->whereIn('question_uuid', $rows->pluck('uuid')->all())->whereIn('text_block_uuid', $blocks)->pluck('question_uuid')->all();
                $facts = [];
                foreach ([...$oldQuestions, ...$newQuestions] as $question) {
                    $row = $rows[$question['id']];
                    if ((string) $row->type !== (string) $question['type'] || $row->level !== $question['level']) {
                        throw new RuntimeException('Native old/new random selection lost its actual SQL type/level.');
                    }
                    if ($mode === 'theory_links') {
                        if ((!in_array($row->uuid, $linked, true) && !in_array($row->theory_text_block_uuid, $blocks, true))
                            || (($config['question_types'] ?? []) !== [] && !in_array((string) $row->type, $config['question_types'], true))
                            || (($config['seeder_classes'] ?? []) !== [] && !in_array($row->seeder, $config['seeder_classes'], true))
                            || (preg_match('/^[ABC][12]$/D', (string) $practice->level) === 1 && $row->level !== $practice->level)) {
                            throw new RuntimeException('Native linked random selection lost its exact page/type/level/source contract.');
                        }
                    }
                    $facts[$row->id] = ['id' => $row->id, 'seeder' => $row->seeder, 'type' => $row->type, 'level' => $row->level];
                    if ($mode === 'legacy_tag_fallback') {
                        $facts[$row->id]['native_score'] = $window[$row->id]['score'];
                        $facts[$row->id]['matched_tag_ids'] = $window[$row->id]['matched_tag_ids'];
                        $facts[$row->id]['matched_normalized_tags'] = $window[$row->id]['matched_normalized_tags'];
                    }
                }
                ksort($facts);
                return ['source' => $mode, 'practice_block_uuid' => $storedPractice->uuid,
                    'native_config' => $config, 'native_candidate_count' => $candidateCount,
                    'native_top_scoring_window_ids' => array_keys($window), 'selected_before_after_rows' => array_values($facts)];
            });
            ++$static; $row['comparison'] = 'Exact remaining static learning DOM after the single Generic Reference five-question JSON array (no markup or hint exclusion). NOT full learning DOM or raw HTML equality.';
            $row['before_random_ids'] = array_column($oldQuestions, 'id'); $row['after_random_ids'] = array_column($newQuestions, 'id');
            $row['same_id_payloads_compared_exact'] = $overlap; $row['old_new_conditional_hint_count'] = [$oldHints, $newHints]; $row['selection_sql_bindings'] = $bindingFacts;
            $row['newly_selected_payload_limit'] = 'Both BEFORE/AFTER IDs/types/levels/source are validated against their actual native selection mode: exact page-wide link contract or deterministic legacy tag-fallback candidate/top-scoring window. Original SQL is independently unchanged. Full semantic/serialized-field equivalence for different newly selected IDs is not claimed; random selection is not replaced.';
        }
    }
    $out['http'][] = $row; echo $json(['checked' => $path, 'status' => $status, 'target' => $scope['target']]);
}
$allTablesAfterHttp = $readOnly($fingerprints);
if ($allTablesBeforeHttp !== $allTablesAfterHttp) { throw new RuntimeException('A database table changed during anonymous navigation GETs.'); }
foreach ($out['sources'] as $path => $sha) { if (!hash_equals($sha, hash_file('sha256', $root.'/'.$path))) { throw new RuntimeException('Source changed during navigation audit: '.$path); } }
foreach ($nativeSourcePins as $path => $sha) { if (!hash_equals($sha, hash_file('sha256', $root.'/'.$path))) { throw new RuntimeException('Native selector source changed during navigation audit: '.$path); } }
if ($full + $static !== 17 || count($out['http']) !== 20) { throw new RuntimeException('Not all seventeen non-target boundaries/twenty GETs were reviewed.'); }
$out['full_non_target_learning_dom_exact'] = $full; $out['static_non_target_dom_exact_with_validated_random_payload_separate'] = $static;
$out['all_resolved_paths_get_200'] = true; $out['all_tables_before_http'] = $allTablesBeforeHttp;
$out['all_tables_after_http'] = $allTablesAfterHttp; $out['sql_unchanged_during_http'] = true;
$out['target_resource_links_checked'] = array_sum(array_map(static fn ($row) => count($row['rendered_learning_resource_links'] ?? []), $out['http']));
if ($out['target_resource_links_checked'] !== 24) { throw new RuntimeException('Twenty-four real target resource anchors required.'); }
$out['status'] = 'PASS-with-explicit-legacy-random-payload-boundary';
$out['source_sql_dom_policy'] = 'Versioned source hashes, independently reconstructed SQL fingerprints, transport captures and parsed learning DOM are separate evidence. A changed random JSON array is neither full HTML equality nor an automatic content regression.';
$bytes = $json($out); $exclusive($name, $bytes);
echo $json(['file' => $name, 'sha256' => hash('sha256', $bytes), 'http_captures' => 20, 'target_pages' => 3,
    'full_non_target_learning_dom_exact' => $full, 'static_non_target_dom_exact' => $static, 'db_writes' => 0,
    'browser_visibility' => 'not_checked', 'interaction' => 'not_checked']);
