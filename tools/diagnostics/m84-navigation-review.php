<?php

/** Fresh SQL/resolver/loopback proof for five exact M84 Word Order footer destinations. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 2
    || preg_match('/^navigation-review-v[1-9][0-9]*\.json$/D', $argv[1]) !== 1) {
    throw new RuntimeException('M84 needs one exclusive navigation receipt basename.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = $root.'/storage/app/m75-worktree';
$dir = $root.'/storage/app/seo-m84-local';
$normal = static fn ($path) => rtrim(strtolower(str_replace('\\', '/', (string) realpath($path))), '/');
if ($normal(dirname(__DIR__, 2)) !== $normal($root)) {
    throw new RuntimeException('M84 navigation proof must run from served ROOT.');
}
$beforeSha = 'babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916';
$beforeFile = $dir.'/before-v1.json';
if (!is_file($beforeFile) || is_link($beforeFile)
    || !hash_equals($beforeSha, hash_file('sha256', $beforeFile))) {
    throw new RuntimeException('M84 independent navigation BEFORE changed.');
}
$before = json_decode(file_get_contents($beforeFile), true, flags: JSON_THROW_ON_ERROR);
$out = $dir.'/'.$argv[1];
if (file_exists($out) || is_link($out)) {
    throw new RuntimeException('M84 exclusive navigation receipt exists.');
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$guard = new App\Services\M49LocalTargetGuard;
$physical = $guard->physicalEvidence();
$server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file');
if ($server['db'] !== 'gr2' || (int) $server['port'] !== 3306 || !is_file($server['pid_file'])) {
    throw new RuntimeException('M84 navigation local database differs.');
}
$pid = trim(file_get_contents($server['pid_file']));
if (!ctype_digit($pid)) { throw new RuntimeException('M84 navigation MySQL PID differs.'); }
$guard->assertEvidence($physical, $normal($root), (int) $pid, 3306);
if (App\Services\M49LocalTargetGuard::publicIdentity($db, $root.'/public') !== $before['identity']) {
    throw new RuntimeException('M84 navigation current runtime differs from independent BEFORE.');
}
foreach ($before['physical_hashes'] as $path => $sha) {
    if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
        throw new RuntimeException('M84 navigation physical source changed: '.$path);
    }
}
$fingerprints = static function () use ($db): array {
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false);
    sort($tables); $result = [];
    foreach ($tables as $table) {
        $columns = $db->getSchemaBuilder()->getColumnListing($table);
        $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $hash = hash_init('sha256'); $count = 0;
        foreach ($query->cursor() as $row) {
            hash_update($hash, App\Services\M26ContentPatch::digest((array) $row)."\n");
            $count++;
        }
        $result[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
    }
    return $result;
};
$readonly = static function (Closure $callback) use ($db) {
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try { return $callback(); } finally { $db->rollBack(); }
};
$sqlBefore = $readonly($fingerprints);
if ($sqlBefore !== $before['sql_before'] || count($sqlBefore) !== 46) {
    throw new RuntimeException('M84 navigation found fresh working BEFORE table drift.');
}
$resolver = new class extends App\Http\Controllers\PageController {
    public function __construct() {}
    public function path(App\Models\PageCategory $category): string { return $this->categorySlugPath($category); }
};
$suffixes = [
    'BasicWordOrderTheorySeeder', 'WordOrderQuestionsNegativesTheorySeeder',
    'WordOrderAdverbsAdverbialsTheorySeeder', 'WordOrderVerbsObjectsTheorySeeder',
    'AdvancedWordOrderEmphasisTheorySeeder',
];
$labels = [
    'Basic Word Order — Базовий порядок слів',
    'Word Order in Questions and Negatives — Порядок слів у питаннях і запереченнях',
    'Word Order with Adverbs and Adverbials — Прислівники й обставини',
    'Word Order with Verbs and Objects — Дієслова й додатки',
    'Advanced Word Order / Emphasis — Інверсія й акцент',
];
$slugs = [
    'theory-basic-word-order', 'theory-word-order-questions-negatives',
    'theory-word-order-adverbs-adverbials', 'theory-word-order-verbs-objects',
    'theory-advanced-word-order-emphasis',
];
$owners = array_map(static fn ($suffix) => 'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\'.$suffix, $suffixes);
$fOwner = 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarHaveGotHasGotTheorySeeder';
if (array_keys($before['definitions']) !== [...$owners, $fOwner]) {
    throw new RuntimeException('M84 navigation independent six-owner order differs.');
}
$sourceHashes = []; $pairedSourceHashes = []; $destinations = []; $m83Neighbors = [];
$readPaired = static function (string $owner, string $path, ?array $captured) use ($root, $wt, &$sourceHashes, &$pairedSourceHashes): array {
    $paths = ['served_root' => $root.'/'.$path, 'worktree' => $wt.'/'.$path]; $bytes = [];
    foreach ($paths as $context => $file) {
        if (!is_file($file) || is_link($file)) { throw new RuntimeException('M84 navigation missing paired source: '.$path); }
        $bytes[$context] = file_get_contents($file);
        $pairedSourceHashes[$path][$context] = ['raw_sha256' => hash('sha256', $bytes[$context]),
            'lf_sha256' => hash('sha256', str_replace("\r\n", "\n", $bytes[$context])), 'bytes' => strlen($bytes[$context])];
    }
    if (str_replace("\r\n", "\n", $bytes['served_root']) !== str_replace("\r\n", "\n", $bytes['worktree'])) {
        throw new RuntimeException('M84 navigation paired source LF content differs: '.$path);
    }
    if ($captured !== null && ($captured['path'] !== $path || !hash_equals($captured['sha256'], hash('sha256', $bytes['served_root']))
        || $captured['bytes'] !== $bytes['served_root'])) {
        throw new RuntimeException('M84 navigation paired source differs from fresh BEFORE: '.$path);
    }
    $source = json_decode($bytes['served_root'], true, flags: JSON_THROW_ON_ERROR);
    if (($source['seeder']['class'] ?? null) !== $owner || ($source['page']['locale'] ?? null) !== 'uk') {
        throw new RuntimeException('M84 navigation paired source owner/locale differs.');
    }
    $sourceHashes[$path] = hash('sha256', $bytes['served_root']);
    return $source;
};
$readonly(static function () use ($before, $owners, $fOwner, $suffixes, $slugs, $labels, $resolver,
    $readPaired, &$destinations, &$m83Neighbors): void {
    foreach ($owners as $i => $owner) {
        $path = 'database/seeders/Page_V3/BasicGrammar/WordOrder/'.$suffixes[$i].'/definition.json';
        $source = $readPaired($owner, $path, $before['definitions'][$owner]);
        $page = App\Models\Page::query()->with('category')->where('seeder', $owner)->sole();
        $categoryPath = $resolver->path($page->category);
        if ($page->id !== $before['pages'][$owner]['page']['id'] || $page->type !== 'theory' || $page->slug !== $slugs[$i]
            || $source['slug'] !== $slugs[$i] || $page->category?->slug !== 'word-order'
            || $categoryPath !== 'basic-grammar/word-order' || $source['page']['category']['slug'] !== 'word-order') {
            throw new RuntimeException('M84 navigation target SQL/category/ancestry differs.');
        }
        $route = app('router')->getRoutes()->getByName('theory.show');
        $url = '/'.str_replace(['{categoryPath}', '{pageSlug}'], [$categoryPath, $page->slug], $route->uri());
        $expected = '/theory/basic-grammar/word-order/'.$slugs[$i];
        if (!in_array('GET', $route->methods(), true) || $url !== $expected
            || app('router')->getRoutes()->match(Illuminate\Http\Request::create('http://gramlyze.loc'.$url, 'GET'))->getName() !== 'theory.show'
            || $before['pages'][$owner]['target_route']['url'] !== 'http://gramlyze.loc'.$url) {
            throw new RuntimeException('M84 navigation named route or full theory-prefix path differs.');
        }
        $http = array_values(array_filter($before['http'], static fn ($row) => $row['identity'] === $owner && $row['kind'] === 'theory'));
        if (count($http) !== 1 || $http[0]['status'] !== 200) { throw new RuntimeException('M84 independent target HTTP baseline missing.'); }
        $destinations[$labels[$i]] = ['label' => $labels[$i], 'url' => $url, 'target_page_id' => $page->id,
            'target_owner' => $owner, 'expected_h1' => $http[0]['h1'], 'expected_canonical' => $http[0]['canonical']];
    }
    $readPaired($fOwner, $before['definitions'][$fOwner]['path'], $before['definitions'][$fOwner]);
    $m83 = App\Support\M83B2RevisionBasicGrammar::load();
    $m83Before = $m83[0]; $m83Nav = $m83[6];
    if (count($m83Before['targets'] ?? []) !== 6 || count($m83Nav['targets'] ?? []) !== 6) {
        throw new RuntimeException('M84 read-only M83 neighbor provenance differs.');
    }
    foreach ($m83Before['targets'] as $i => $target) {
        $owner = $target['identity']; $source = $readPaired($owner, $target['path'], null);
        $nav = $m83Nav['targets'][$i]; $slot = $nav['source_index'];
        if (($nav['identity'] ?? null) !== $owner || ($source['page']['blocks'][$slot]['type'] ?? null) !== 'navigation-chips') {
            throw new RuntimeException('M84 read-only M83 navigation exact source slot differs.');
        }
        $data = json_decode($source['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR);
        $page = App\Models\Page::query()->where('seeder', $owner)->sole();
        $sql = App\Models\TextBlock::query()->where('page_id', $page->id)->where('seeder', $owner)
            ->where('locale', 'uk')->where('type', 'navigation-chips')->sole();
        $sqlData = json_decode($sql->body, true, flags: JSON_THROW_ON_ERROR);
        if ($page->id !== $nav['page_id'] || ($data['items'] ?? null) !== $nav['items'] || $sqlData !== $data) {
            throw new RuntimeException('M84 read-only M83 source/SQL footer differs from accepted contract.');
        }
        $m83Neighbors[] = ['identity' => $owner, 'page_id' => $page->id, 'source_index' => $slot,
            'navigation_uuid' => $sql->uuid, 'navigation_body_sha256' => hash('sha256', $sql->body),
            'items' => $nav['items'], 'source_sql_exact' => true, 'accepted_navigation_exact' => true];
    }
});
foreach ($destinations as &$destination) {
    $url = 'http://gramlyze.loc'.$destination['url']; $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_MAXREDIRS => 0,
        CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120, CURLOPT_PROXY => '',
        CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: text/html']]);
    try {
        $html = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if (!is_string($html) || $status !== 200 || curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) !== $url
            || curl_getinfo($ch, CURLINFO_PRIMARY_IP) !== '127.0.0.1' || curl_getinfo($ch, CURLINFO_REDIRECT_COUNT) !== 0) {
            throw new RuntimeException('M84 navigation loopback GET failed: '.$destination['url']);
        }
    } finally { curl_close($ch); }
    $dom = new DOMDocument('1.0', 'UTF-8'); $prior = libxml_use_internal_errors(true);
    try { $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET); }
    finally { libxml_clear_errors(); libxml_use_internal_errors($prior); }
    $xp = new DOMXPath($dom); $nodes = $xp->query('//h1');
    $canonicalNodes = $xp->query('//link[@rel="canonical"]/@href');
    if ($nodes->length !== 1 || $canonicalNodes->length !== 1) { throw new RuntimeException('M84 navigation H1/canonical cardinality differs.'); }
    $h1 = trim(preg_replace('/\s+/u', ' ', $nodes->item(0)->textContent)); $canonical = $canonicalNodes->item(0)->nodeValue;
    if ($h1 !== $destination['expected_h1'] || $canonical !== $destination['expected_canonical']) {
        throw new RuntimeException('M84 navigation actual H1/canonical differs from independent BEFORE.');
    }
    $destination += ['http_status' => $status, 'h1' => $h1, 'html_sha256' => hash('sha256', $html),
        'canonical' => $canonical, 'actual_url' => $url, 'primary_ip' => '127.0.0.1', 'redirect_count' => 0];
    echo json_encode(['destination' => $destination['url'], 'status' => $status], JSON_UNESCAPED_SLASHES)."\n";
}
unset($destination);
$targets = []; $ownerNavigations = [];
$navSlots = [9, 9, 11, 9, 10];
foreach ($owners as $i => $owner) {
    $source = $before['definitions'][$owner]['definition'];
    $navs = array_keys(array_filter($source['page']['blocks'], static fn ($block) => $block['type'] === 'navigation-chips'));
    if ($navs !== [$navSlots[$i]]) { throw new RuntimeException('M84 exact source footer slot differs.'); }
    $slot = $navs[0]; $data = json_decode($source['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR);
    if (($data['title'] ?? null) !== 'Сторінки підрозділу Word Order' || array_column($data['items'] ?? [], 'label') !== $labels) {
        throw new RuntimeException('M84 source footer title/labels/order differs.');
    }
    foreach ($data['items'] as $index => &$item) {
        if (!is_bool($item['current'] ?? null) || $item['current'] !== ($index === $i) || array_key_exists('url', $item)) {
            throw new RuntimeException('M84 source footer current/label-only state differs.');
        }
        if ($item['current']) { continue; }
        $destination = $destinations[$item['label']]; $item['url'] = $destination['url'];
        $targets[] = ['owner' => $owner, 'source_index' => $slot, 'item_index' => $index, 'current' => false] + $destination;
    }
    unset($item); $ownerNavigations[$owner] = ['source_index' => $slot, 'data' => $data];
}
$fNavs = array_values(array_filter($before['definitions'][$fOwner]['definition']['page']['blocks'], static fn ($b) => $b['type'] === 'navigation-chips'));
if ($fNavs !== []) { throw new RuntimeException('M84 Have Got unexpectedly has a source footer.'); }
$ownerNavigations[$fOwner] = ['source_index' => null, 'data' => null, 'items' => [],
    'reason' => 'No source navigation; no new footer proposed or added.'];
$sqlAfter = $readonly($fingerprints);
if (count($targets) !== 20 || $sqlAfter !== $sqlBefore) {
    throw new RuntimeException('M84 navigation additions/count or all-table preservation differs.');
}
foreach ($pairedSourceHashes as $path => $pair) {
    foreach (['served_root' => $root, 'worktree' => $wt] as $context => $base) {
        if (!hash_equals($pair[$context]['raw_sha256'], hash_file('sha256', $base.'/'.$path))) {
            throw new RuntimeException('M84 paired navigation source changed during read-only proof: '.$path);
        }
    }
}
$result = ['schema_version' => 1, 'purpose' => 'M84 twenty footer URL data additions; five exact Word Order destinations, six M83 neighbor footers read-only, Have Got absent footer preserved.',
    'independent_before_sha256' => $beforeSha, 'source_sha256' => $sourceHashes,
    'paired_source_hashes' => $pairedSourceHashes, 'targets' => $targets, 'owner_navigations' => $ownerNavigations,
    'destinations' => array_values($destinations), 'm83_neighbors' => $m83Neighbors,
    'sql_before' => $sqlBefore, 'sql_after' => $sqlAfter, 'sql_table_count' => count($sqlBefore),
    'sql_readonly' => true, 'tables_unchanged' => true, 'db_writes' => 0, 'source_writes' => 0,
    'browser_visibility' => 'not_checked', 'interaction' => 'not_checked'];
$bytes = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($out, 'x');
if (!$handle) { throw new RuntimeException('M84 exclusive navigation receipt unavailable.'); }
try {
    if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M84 navigation receipt incomplete.'); }
} finally { fclose($handle); }
echo json_encode(['file' => $argv[1], 'sha256' => hash('sha256', $bytes), 'destinations' => count($destinations),
    'additions' => count($targets), 'm83_neighbors' => count($m83Neighbors), 'table_count' => count($sqlBefore), 'writes' => 0])."\n";
