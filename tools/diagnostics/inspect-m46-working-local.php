<?php
/** Explicit M46 read-only BEFORE capture. No writes to application tables. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($argv[1] ?? '', ['capture', 'proof'], true)) { exit(1); }
$sourceRoot = dirname(__DIR__, 2);
$working = 'D:/DEV/htdocs/gramlyze.loc';
require $working.'/vendor/autoload.php';
require_once $sourceRoot.'/app/Services/M46LocalTargetGuard.php';
$app = require $working.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Services\M46LocalTargetGuard as Guard;
use Illuminate\Support\Facades\DB;
if (strtolower(str_replace('\\', '/', realpath(base_path()))) !== strtolower($working)) { throw new RuntimeException('M46 served ROOT differs.'); }
$db = DB::connection();
Guard::assertConfiguredDatabase($db);
$dir = Guard::privateDirectory();
if (is_link($dir) || (!is_dir($dir) && !mkdir($dir, 0700, true))) { throw new RuntimeException('M46 private directory unavailable.'); }
$exclusive = static function (string $path, string $bytes): void {
    $f = fopen($path, 'x');
    if (!$f) { throw new RuntimeException('Exclusive M46 evidence collision.'); }
    try { if (fwrite($f, $bytes) !== strlen($bytes) || !fflush($f)) { throw new RuntimeException('Incomplete M46 evidence.'); } }
    finally { fclose($f); }
};
$json = static fn ($value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$physical = (new Guard)->physicalEvidence();
$physicalFiles = ['C:/Program Files/xampp/apache/conf/httpd.conf', 'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
    'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', $working.'/public/index.php'];
$physicalHashes = [];
foreach ($physicalFiles as $path) { $physicalHashes[$path] = hash_file('sha256', $path); }
if (($argv[1] ?? '') === 'proof') {
    $nonce = bin2hex(random_bytes(16));
    $routeBytes = file_get_contents($working.'/routes/api.php');
    if (str_contains($routeBytes, 'm46-target-')) { throw new RuntimeException('M46 temporary route already present.'); }
    $proof = ['target' => 'gramlyze.loc', 'nonce' => $nonce, 'created_at' => gmdate('c'), 'scope' => Guard::SCOPE,
        'physical_hashes' => $physicalHashes, 'physical' => $physical, 'routes_before_sha256' => hash('sha256', $routeBytes)];
    $name = 'local-proof-'.$nonce.'.json';
    $exclusive($dir.'/routes-before-'.$nonce.'.bin', $routeBytes);
    $exclusive($dir.'/'.$name, $json($proof));
    Guard::readFreshProof($name);
    echo $json(['proof' => $name, 'nonce' => $nonce, 'routes_before_sha256' => $proof['routes_before_sha256']]);
    exit(0);
}
$name = $argv[2] ?? '';
if (!preg_match('/^before-v[1-9][0-9]*\.json$/D', $name)) { throw new RuntimeException('M46 requires new BEFORE basename.'); }
$capture = ['at' => gmdate('c'), 'identity' => Guard::publicIdentity($db, $working.'/public'),
    'physical' => $physical, 'physical_hashes' => $physicalHashes, 'definitions' => [], 'pages' => [], 'http' => [], 'visual_hashes' => []];
$rows = static fn ($q): array => $q->get()->map(static fn ($row) => (array) $row)->all();
$http = static function (string $path) use ($exclusive, $dir, $name): array {
    if (!str_starts_with($path, '/theory/') && !str_starts_with($path, '/courses/english-grammar-theory/lesson/')) { throw new RuntimeException('M46 HTTP path outside scope.'); }
    $curl = curl_init('http://gramlyze.loc'.$path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 30,
        CURLOPT_PROXY => '', CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1']]);
    $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
    if ($body === false || $ip !== '127.0.0.1') { throw new RuntimeException('M46 local HTTP unavailable.'); }
    $file = substr($name, 0, -5).'-http-'.hash('sha256', $path).'.html';
    $exclusive($dir.'/'.$file, $body);
    preg_match('~<title>(.*?)</title>~s', $body, $title);
    return ['url' => 'http://gramlyze.loc'.$path, 'status' => $status, 'body_sha256' => hash('sha256', $body), 'file' => $file,
        'title' => html_entity_decode($title[1] ?? ''), 'canonical_path' => $path];
};
foreach (Guard::SCOPE as $scope) {
    $identity = $scope['identity']; $short = substr($identity, strrpos($identity, '\\') + 1);
    $definitionPath = 'database/seeders/Page_V3/Conditionals/'.$short.'/definition.json';
    $bytes = file_get_contents($working.'/'.$definitionPath);
    $wtBytes = file_get_contents($sourceRoot.'/'.$definitionPath);
    if ($bytes !== $wtBytes) { throw new RuntimeException('M46 ROOT/worktree definition BEFORE differs.'); }
    $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    $pageRows = $rows($db->table('pages')->where('seeder', $identity));
    if (count($pageRows) !== 1) { throw new RuntimeException('M46 page owner missing/ambiguous.'); }
    $page = $pageRows[0];
    if ($page['slug'] !== $definition['slug'] || $page['title'] !== $definition['page']['title'] || $page['type'] !== 'theory'
        || $page['text'] !== $definition['page']['subtitle_text']) { throw new RuntimeException('M46 page BEFORE mismatch.'); }
    $chain = []; $cat = $page['page_category_id'];
    while ($cat) {
        $row = (array) $db->table('page_categories')->where('id', $cat)->first();
        if (!$row || $row['language'] !== 'uk' || $row['type'] !== 'theory') { throw new RuntimeException('M46 category mismatch.'); }
        array_unshift($chain, $row); $cat = $row['parent_id'];
        if (count($chain) > 8) { throw new RuntimeException('M46 ancestry too deep.'); }
    }
    if (array_column($chain, 'slug') !== ['conditionals']) { throw new RuntimeException('M46 conditionals ancestry differs.'); }
    $blocks = $rows($db->table('text_blocks')->where('page_id', $page['id'])->orderBy('sort_order')->orderBy('id'));
    $uk = array_values(array_filter($blocks, static fn ($row) => $row['locale'] === 'uk'));
    $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'], 'uuid_key' => 'subtitle', 'level' => null];
    $configs = [$subtitle, ...$definition['page']['blocks']];
    if (count($configs) !== count($uk)) { throw new RuntimeException('M46 unexpected UK BEFORE blocks.'); }
    foreach ($configs as $slot => $config) {
        $row = $uk[$slot]; $uuid = App\Support\M26DetailPackage::uuid($identity, $config, $slot);
        if ($row['uuid'] !== $uuid || $row['seeder'] !== $identity || (int) $row['sort_order'] !== $slot || $row['body'] !== $config['body']
            || $row['type'] !== $config['type'] || $row['column'] !== $config['column'] || $row['level'] !== ($config['level'] ?? null)) {
            throw new RuntimeException('M46 block BEFORE mismatch at '.$definition['slug'].' slot '.$slot);
        }
    }
    $uuids = array_column($blocks, 'uuid');
    $links = $rows($db->table('question_theory_text_blocks')->whereIn('text_block_uuid', $uuids)->orderBy('id'));
    $pageModel = App\Models\Page::findOrFail($page['id']);
    $topicTests = app(App\Services\TheoryPagePromptLinkedTestsService::class)->buildForPage($pageModel);
    $capture['definitions'][$identity] = ['path' => $definitionPath, 'sha256' => hash('sha256', $bytes), 'bytes' => $bytes, 'definition' => $definition];
    $capture['pages'][$identity] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $blocks, 'question_links' => $links, 'topic_tests' => $topicTests];
    $capture['http'][] = $http($scope['path']);
    $capture['http'][] = $http('/courses/english-grammar-theory/lesson/conditionals/'.$page['slug']);
}
foreach (['resources/views', 'resources/css', 'resources/js', 'public/js'] as $folder) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($working.'/'.$folder, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $f) {
        if (!$f->isFile() || $f->isLink()) { continue; }
        $path = str_replace('\\', '/', $f->getPathname()); $relative = substr($path, strlen($working) + 1);
        $capture['visual_hashes'][$relative] = hash_file('sha256', $path);
    }
}
ksort($capture['visual_hashes']);
$exclusive($dir.'/'.$name, $json($capture));
echo $json(['status' => 'read-only-before', 'file' => $name, 'sha256' => hash_file('sha256', $dir.'/'.$name),
    'identity' => $capture['identity'], 'owners' => array_map(static fn ($item) => ['id' => $item['page']['id'], 'slug' => $item['page']['slug'],
        'blocks' => count($item['blocks']), 'question_links' => count($item['question_links'])], $capture['pages']),
    'http' => array_map(static fn ($item) => ['url' => $item['url'], 'status' => $item['status'], 'title' => $item['title']], $capture['http']),
    'visual_files' => count($capture['visual_hashes'])]);
