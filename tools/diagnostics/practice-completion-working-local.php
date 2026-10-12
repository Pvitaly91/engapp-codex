<?php
/** Explicit practice-completion CLI only; capture/proof are read-only. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows'
    || !in_array($argv[1] ?? '', ['capture', 'proof', 'inventory', 'sync', 'review', 'audit', 'content:complete-practice-m46-m49'], true)) { exit(1); }
$sourceRoot = dirname(__DIR__, 2);
$working = 'D:/DEV/htdocs/gramlyze.loc';
require $working.'/vendor/autoload.php';
require_once $sourceRoot.'/app/Services/PracticeCompletionLocalTargetGuard.php';
$app = require $working.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class); $kernel->bootstrap();
use App\Services\PracticeCompletionLocalTargetGuard as Guard;
use App\Support\PracticeCompletionPackage as Package;
use Illuminate\Support\Facades\DB;
if (strtolower(str_replace('\\', '/', realpath(base_path()))) !== strtolower($working)) { throw new RuntimeException('Practice completion served ROOT differs.'); }
$db = DB::connection(); Guard::assertConfiguredDatabase($db);
$dir = Guard::privateDirectory();
if (is_link($dir) || (!is_dir($dir) && !mkdir($dir, 0700, true))) { throw new RuntimeException('Private evidence directory unavailable.'); }
$exclusive = static function(string $path, string $bytes): void {
    $f = fopen($path, 'x'); if (!$f) { throw new RuntimeException('Exclusive practice-completion evidence collision.'); }
    try { if (fwrite($f, $bytes) !== strlen($bytes) || !fflush($f)) { throw new RuntimeException('Incomplete evidence write.'); } }
    finally { fclose($f); }
};
$json = static fn ($value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";

if ($argv[1] === 'content:complete-practice-m46-m49') {
    $app->useDatabasePath($sourceRoot.'/database');
    $kernel->registerCommand($app->make(App\Console\Commands\PatchPracticeCompletion::class));
    $status = $kernel->handle(new Symfony\Component\Console\Input\ArgvInput, new Symfony\Component\Console\Output\ConsoleOutput);
    $kernel->terminate(new Symfony\Component\Console\Input\ArgvInput, $status); exit($status);
}
if (in_array($argv[1], ['sync', 'review', 'audit'], true)) {
    [$beforePackage, $patchPackage, $master] = Package::load($sourceRoot);
    Package::validate($beforePackage, $patchPackage, $master, $sourceRoot);
    $patchClass = App\Services\PracticeCompletionPatch::class;
    if ($argv[1] === 'sync') {
        $files = [];
        foreach ($patchClass::syncPaths($patchPackage) as $path) {
            $a = file_get_contents($working.'/'.$path); $b = file_get_contents($sourceRoot.'/'.$path);
            if ($a !== $b && !(str_ends_with($path, '.php') && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b))) {
                throw new RuntimeException('Served/source sync differs: '.$path);
            }
            $files[] = ['path' => $path, 'root_after_sha256' => hash('sha256', $a), 'worktree_sha256' => hash('sha256', $b)];
        }
        $record = ['base_sha' => Package::BASE_SHA, 'files' => $files,
            'visual_before' => ['file' => $patchClass::BEFORE_FILE, 'sha256' => $patchClass::BEFORE_SHA]];
        $exclusive($dir.'/'.$patchClass::SYNC_RECEIPT, $json($record));
        echo $json(['status' => 'sync-reviewed', 'files' => count($files), 'sha256' => hash_file('sha256', $dir.'/'.$patchClass::SYNC_RECEIPT)]); exit(0);
    }
    $captured = json_decode(file_get_contents($dir.'/'.$patchClass::BEFORE_FILE), true, flags: JSON_THROW_ON_ERROR);
    if ($argv[1] === 'review') {
        $name = $argv[2] ?? '';
        if (!preg_match('/^preview-v[1-9][0-9]*\\.json$/D', $name)) { throw new RuntimeException('Fresh preview basename required.'); }
        $plan = json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
        $expected = [];
        foreach ($patchPackage['targets'] as $target) {
            $row = $captured['pages'][$target['identity']]['practice'];
            $expected[] = ['table' => 'text_blocks', 'id' => $row['id'], 'uuid' => $row['uuid'], 'seeder' => $target['identity'],
                'before' => ['body' => $row['body']], 'after' => ['body' => $target['after']['practice']['body']]];
        }
        $actual = array_map(static fn ($u) => array_intersect_key($u, array_flip(['table','id','uuid','seeder','before','after'])), $plan['updates']);
        if ($actual !== $expected || $plan['inserts'] !== [] || $plan['state'] !== 'before') { throw new RuntimeException('Independent exact twelve-body diff review failed.'); }
        $out = ['status' => 'exact-diff-reviewed', 'preview' => $name, 'updated' => count($actual), 'inserted' => 0, 'deleted' => 0,
            'preview_sha256' => hash_file('sha256', $dir.'/'.$name)];
        $exclusive($dir.'/reviewed-'.$name, $json($out)); echo $json($out); exit(0);
    }
    $name = $argv[2] ?? '';
    if (!preg_match('/^after-v[1-9][0-9]*\\.json$/D', $name)) { throw new RuntimeException('Exclusive AFTER audit basename required.'); }
    $backup = json_decode(file_get_contents($dir.'/backup-v2.json'), true, flags: JSON_THROW_ON_ERROR);
    $pages = []; $targetIds = [];
    foreach ($patchPackage['targets'] as $target) {
        $old = $captured['pages'][$target['identity']];
        $owner = (array) $db->table('pages')->where('seeder', $target['identity'])->first();
        $rows = $db->table('text_blocks')->where('page_id', $owner['id'])->orderBy('sort_order')->orderBy('id')->get()->map(static fn ($r) => (array) $r)->all();
        $practice = array_values(array_filter($rows, static fn ($r) => $r['uuid'] === $target['after']['practice']['uuid']));
        if (count($practice) !== 1 || $practice[0]['body'] !== $target['after']['practice']['body']) { throw new RuntimeException('AFTER practice differs.'); }
        $patchClass::assertMetadataBaseline($old, $owner, $rows, $practice[0]['uuid'],
            $target['before']['practice']['body'], $target['after']['practice']['body']);
        $data = json_decode($practice[0]['body'], true, flags: JSON_THROW_ON_ERROR);
        $prior = json_decode($target['before']['practice']['body'], true, flags: JSON_THROW_ON_ERROR);
        $protectedData = $data;
        foreach (['input_title','input_intro','inputs','practice_completion_v1'] as $field) {
            if (array_key_exists($field, $prior)) { $protectedData[$field] = $prior[$field]; } else { unset($protectedData[$field]); }
        }
        if ($protectedData !== $prior || count($data['inputs']) !== 2) { throw new RuntimeException('Old task fields or supplemental input count changed.'); }
        $targetIds[] = $practice[0]['id'];
        $pages[$target['identity']] = ['page' => $owner, 'blocks' => $rows, 'practice_id' => $practice[0]['id'],
            'groups' => array_map(static fn ($g) => count($data[$g] ?? []), ['selects','choices','inputs','rephrase'])];
    }
    $auditor = new class($db, $working.'/database', $dir) extends App\Services\PracticeCompletionPatch {
        public function fingerprints(array $ids): array { return $this->protectedFingerprints($ids); }
    };
    $protected = $auditor->fingerprints($targetIds);
    if ($protected !== $backup['protected']) { throw new RuntimeException('Protected SQL tables changed.'); }
    $visual = $patchClass::visualHashes($working);
    if ($visual !== $captured['visual_hashes']) { throw new RuntimeException('Visual source changed.'); }
    $frozen = json_decode(file_get_contents($dir.'/'.$patchClass::FROZEN_FILE), true, flags: JSON_THROW_ON_ERROR);
    foreach ($frozen['files'] as $path => $sha) {
        if (hash_file('sha256', $working.'/'.$path) !== $sha) { throw new RuntimeException('Historical/non-target source changed: '.$path); }
    }
    $htmlAudit = static function(string $html, ?int $removePractice = null): array {
        $dom = new DOMDocument('1.0', 'UTF-8'); $prior = libxml_use_internal_errors(true);
        try { $dom->loadHTML($html, LIBXML_NONET); } finally { libxml_clear_errors(); libxml_use_internal_errors($prior); }
        $x = new DOMXPath($dom); $get = static fn ($q) => $x->query($q)->item(0)?->nodeValue ?? '';
        $meta = static fn ($n) => $x->query('//meta[@name="'.$n.'" or @property="'.$n.'"]/@content')->item(0)?->nodeValue ?? '';
        $structured = []; foreach ($x->query('//script[@type="application/ld+json"]') as $s) { $structured[] = json_decode($s->textContent, true, flags: JSON_THROW_ON_ERROR); }
        $content = $x->query('//div[contains(concat(" ",normalize-space(@class)," ")," theory-design ")]')->item(0);
        $result = ['title' => $get('//title'), 'h1' => $get('//h1'), 'canonical' => $get('//link[@rel="canonical"]/@href'),
            'description' => $meta('description'), 'og_description' => $meta('og:description'), 'twitter_description' => $meta('twitter:description'),
            'robots' => $meta('robots'), 'structured' => $structured, 'point_count' => $x->query('//*[@data-theory-point-panel]')->length,
            'details' => $x->query('//*[@data-theory-point-index]')->length, 'practice_models' => substr_count($html, 'x-data="theoryPracticeSet('),
            'manual_fields' => $x->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]')->length];
        if ($removePractice !== null) {
            $node = $x->query('//*[@id="block-'.$removePractice.'"]')->item(0);
            if ($node === null) { throw new RuntimeException('Practice anchor missing in HTTP.'); }
            $node->parentNode->removeChild($node);
        }
        $result['teaching_dom'] = $content ? $dom->saveHTML($content) : '';
        if ($result['teaching_dom'] === '') { throw new RuntimeException('Teaching DOM missing.'); }
        return $result;
    };
    $http = [];
    foreach ($patchPackage['targets'] as $i => $target) {
        foreach ([$target['local_url'], 'http://gramlyze.loc/courses/english-grammar-theory/lesson/'.implode('/', $target['ancestry']).'/'.$target['slug']] as $url) {
            $robots = ''; $curl = curl_init($url);
            curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 30,
                CURLOPT_PROXY => '', CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'],
                CURLOPT_HEADERFUNCTION => static function($handle, $header) use (&$robots) { if (stripos($header, 'X-Robots-Tag:') === 0) { $robots = trim(substr($header,13)); } return strlen($header); }]);
            $html = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
            if ($html === false || $status !== 200 || $ip !== '127.0.0.1') { throw new RuntimeException('Local AFTER HTTP unavailable: '.$url.' errno='.curl_errno($curl)); }
            $file = substr($name,0,-5).'-http-'.hash('sha256',$url).'.html'; $exclusive($dir.'/'.$file, $html);
            $course = str_contains($url, '/courses/');
            $practiceId = $course ? null : $pages[$target['identity']]['practice_id'];
            $actual = $htmlAudit($html, $practiceId);
            $oldHttp = array_values(array_filter($captured['http'], static fn ($h) => $h['url'] === $url))[0];
            $old = $htmlAudit(file_get_contents($dir.'/'.$oldHttp['file']), $practiceId);
            foreach (['title','h1','canonical','description','og_description','twitter_description','robots','structured','point_count','details','teaching_dom'] as $field) {
                if ($actual[$field] !== $old[$field]) { throw new RuntimeException('Protected '.($course?'course':'theory').' HTTP field differs: '.$field.' '.$url); }
            }
            if ($robots !== $oldHttp['robots_header'] || (!$course && ($actual['manual_fields'] !== 2 || $actual['practice_models'] !== 1))) {
                throw new RuntimeException('Practice fields or robots header differs.');
            }
            unset($actual['teaching_dom']);
            $http[] = ['url' => $url, 'status' => $status, 'file' => $file, 'after' => $actual, 'protected_teaching_dom_equal' => true,
                'robots_header' => $robots, 'sha256' => hash('sha256', $html)];
        }
    }
    $out = ['at' => gmdate('c'), 'runtime' => Guard::publicIdentity($db, $working.'/public'), 'pages' => $pages,
        'protected' => $protected, 'visual_hashes' => $visual, 'http' => $http,
        'counts' => ['updated' => count($backup['updates']), 'inserted' => 0, 'deleted' => 0], 'old_tasks_unchanged' => 72, 'new_inputs' => 24];
    $exclusive($dir.'/'.$name, $json($out));
    echo $json(['status' => 'postconditions-pass', 'file' => $name, 'sha256' => hash_file('sha256',$dir.'/'.$name),
        'protected_tables' => count($protected), 'visual_files' => count($visual), 'http_200' => count($http),
        'old_tasks' => 72, 'new_inputs' => 24, 'theory_metadata_course_changes' => 0]); exit(0);
}
if ($argv[1] === 'inventory') {
    $skip = array_column(Package::BINDINGS, 'definition'); $hashes = [];
    foreach (['docs/content', 'database/content-patches', 'database/seeders/Page_V3'] as $folder) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($working.'/'.$folder, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isLink()) { throw new RuntimeException('Frozen inventory link refused.'); }
            if (!$f->isFile()) { continue; }
            $relative = substr(str_replace('\\', '/', $f->getPathname()), strlen($working) + 1);
            if ($relative === 'docs/content/theory-template.md'
                || str_contains($relative, '/practice-completion-drafts/') || str_contains($relative, '/m46-m49-practice-completion')
                || in_array($relative, $skip, true)) { continue; }
            $hashes[$relative] = hash_file('sha256', $f->getPathname());
        }
    }
    foreach (['app/Support/M*.php', 'app/Services/M*.php', 'docs/reports/seo-m4[6-9]-*.md'] as $pattern) {
        foreach (glob($working.'/'.$pattern) ?: [] as $file) { $hashes[substr($file, strlen($working) + 1)] = hash_file('sha256', $file); }
    }
    ksort($hashes);
    $exclusive($dir.'/frozen-before-v2.json', $json(['base_sha' => Package::BASE_SHA,
        'authorized_process_document' => 'docs/content/theory-template.md', 'files' => $hashes]));
    echo $json(['frozen_files' => count($hashes), 'sha256' => hash_file('sha256', $dir.'/frozen-before-v2.json')]); exit(0);
}
$physical = (new Guard)->physicalEvidence();
$physicalFiles = ['C:/Program Files/xampp/apache/conf/httpd.conf', 'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
    'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', $working.'/public/index.php'];
$physicalHashes = []; foreach ($physicalFiles as $path) { $physicalHashes[$path] = hash_file('sha256', $path); }
if ($argv[1] === 'proof') {
    $nonce = bin2hex(random_bytes(16)); $bytes = file_get_contents($working.'/routes/api.php');
    if (str_contains($bytes, 'practice-completion-target-')) { throw new RuntimeException('Temporary proof route already present.'); }
    $proof = ['target' => 'gramlyze.loc', 'nonce' => $nonce, 'created_at' => gmdate('c'), 'scope' => Guard::scope(),
        'physical_hashes' => $physicalHashes, 'physical' => $physical, 'routes_before_sha256' => hash('sha256', $bytes)];
    $name = 'local-proof-'.$nonce.'.json';
    $exclusive($dir.'/routes-before-'.$nonce.'.bin', $bytes); $exclusive($dir.'/'.$name, $json($proof));
    Guard::readFreshProof($name);
    echo $json(['proof' => $name, 'nonce' => $nonce, 'routes_before_sha256' => $proof['routes_before_sha256']]); exit(0);
}
$name = $argv[2] ?? '';
if (!preg_match('/^before-v[1-9][0-9]*\\.json$/D', $name)) { throw new RuntimeException('New BEFORE basename required.'); }
$capture = ['at' => gmdate('c'), 'base_sha' => Package::BASE_SHA, 'identity' => Guard::publicIdentity($db, $working.'/public'),
    'physical' => $physical, 'physical_hashes' => $physicalHashes, 'definitions' => [], 'pages' => [], 'http' => [], 'visual_hashes' => []];
$rows = static fn ($q): array => $q->get()->map(static fn ($row) => (array) $row)->all();
$http = static function(string $path) use ($exclusive, $dir, $name): array {
    if (!str_starts_with($path, '/theory/') && !str_starts_with($path, '/courses/english-grammar-theory/lesson/')
        && !str_starts_with($path, '/test/')) { throw new RuntimeException('HTTP path outside authorized local consumers.'); }
    $robots = ''; $curl = curl_init('http://gramlyze.loc'.$path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 30,
        CURLOPT_HEADERFUNCTION => static function($handle, $header) use (&$robots) {
            if (stripos($header, 'X-Robots-Tag:') === 0) { $robots = trim(substr($header, 13)); } return strlen($header);
        }, CURLOPT_PROXY => '', CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1']]);
    $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
    if ($body === false || $ip !== '127.0.0.1') {
        throw new RuntimeException('Local BEFORE HTTP unavailable: '.$path.' status='.$status.' curl_errno='.curl_errno($curl).' '.curl_error($curl));
    }
    $file = substr($name, 0, -5).'-http-'.hash('sha256', $path).'.html'; $exclusive($dir.'/'.$file, $body);
    return ['url' => 'http://gramlyze.loc'.$path, 'status' => $status, 'body_sha256' => hash('sha256', $body),
        'file' => $file, 'robots_header' => $robots];
};
foreach (Package::BINDINGS as $binding) {
    $identity = $binding['identity']; $path = $binding['definition'];
    $bytes = file_get_contents($working.'/'.$path);
    if ($bytes !== file_get_contents($sourceRoot.'/'.$path)) { throw new RuntimeException('ROOT/WT independent definition BEFORE differs.'); }
    $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    if (($definition['seeder']['class'] ?? null) !== $identity || ($definition['page']['locale'] ?? null) !== 'uk'
        || ($definition['page']['category']['slug'] ?? null) !== $binding['ancestry'][0]
        || ($definition['slug'] ?? null) !== $binding['slug']) { throw new RuntimeException('Definition BEFORE identity/category differs.'); }
    $owners = $rows($db->table('pages')->where('seeder', $identity));
    if (count($owners) !== 1) { throw new RuntimeException('Missing/ambiguous page owner.'); }
    $page = $owners[0];
    if ($page['slug'] !== $definition['slug'] || $page['title'] !== $definition['page']['title'] || $page['type'] !== 'theory'
        || $page['text'] !== $definition['page']['subtitle_text']) { throw new RuntimeException('Page BEFORE differs.'); }
    $chain = []; $cat = $page['page_category_id']; $seen = [];
    while ($cat) {
        if (isset($seen[$cat]) || count($chain) >= 8) { throw new RuntimeException('Category cycle/depth conflict.'); } $seen[$cat] = true;
        $row = (array) $db->table('page_categories')->where('id', $cat)->first();
        if (!$row || $row['language'] !== 'uk' || $row['type'] !== 'theory') { throw new RuntimeException('Category identity differs.'); }
        array_unshift($chain, $row); $cat = $row['parent_id'];
    }
    if (array_column($chain, 'slug') !== $binding['ancestry']) { throw new RuntimeException('Category ancestry differs.'); }
    $blocks = $rows($db->table('text_blocks')->where('page_id', $page['id'])->orderBy('sort_order')->orderBy('id'));
    $uk = array_values(array_filter($blocks, static fn ($r) => $r['locale'] === 'uk'));
    $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'], 'uuid_key' => 'subtitle', 'level' => null], ...$definition['page']['blocks']];
    if (count($uk) !== count($configs)) { throw new RuntimeException('Unexpected UK block count.'); }
    foreach ($configs as $slot => $cfg) {
        $row = $uk[$slot];
        if ($row['uuid'] !== App\Support\M26DetailPackage::uuid($identity, $cfg, $slot)
            || $row['seeder'] !== $identity || (int) $row['sort_order'] !== $slot || $row['body'] !== $cfg['body']
            || $row['page_id'] !== $page['id'] || $row['page_category_id'] !== $page['page_category_id']) {
            throw new RuntimeException('Block BEFORE differs at '.$binding['slug'].' slot '.$slot);
        }
        foreach (['type', 'column', 'level', 'heading', 'css_class'] as $field) {
            if ($row[$field] !== ($cfg[$field] ?? null)) { throw new RuntimeException('Block BEFORE metadata differs.'); }
        }
    }
    $practices = array_values(array_filter($uk, static fn ($r) => $r['type'] === 'practice-set'));
    if (count($practices) !== 1) { throw new RuntimeException('Exactly one existing UK practice required.'); }
    $practice = $practices[0]; $data = json_decode($practice['body'], true, flags: JSON_THROW_ON_ERROR);
    $groupInventory = [];
    foreach (['selects', 'choices', 'inputs', 'rephrase'] as $g) {
        $items = $data[$g] ?? [];
        $groupInventory[$g] = ['count' => count($items), 'ids' => array_map(static fn ($i) => $i['id'] ?? null, $items),
            'source_indices' => array_map(static fn ($i) => $i['source_index'] ?? null, $items)];
    }
    $uuids = array_column($blocks, 'uuid');
    $links = $rows($db->table('question_theory_text_blocks')->whereIn('text_block_uuid', $uuids)->orderBy('id'));
    $model = App\Models\Page::findOrFail($page['id']);
    $tests = app(App\Services\TheoryPagePromptLinkedTestsService::class)->buildForPage($model);
    $capture['definitions'][$identity] = ['path' => $path, 'sha256' => hash('sha256', $bytes), 'bytes' => $bytes, 'definition' => $definition];
    $capture['pages'][$identity] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $blocks,
        'practice' => $practice, 'group_inventory' => $groupInventory, 'question_links' => $links, 'topic_tests' => $tests];
    $capture['http'][] = $http('/theory/'.implode('/', $binding['ancestry']).'/'.$page['slug']);
    $capture['http'][] = $http('/courses/english-grammar-theory/lesson/'.implode('/', $binding['ancestry']).'/'.$page['slug']);
}
foreach (['resources/views', 'resources/css', 'resources/js', 'public/js'] as $folder) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($working.'/'.$folder, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isLink()) { throw new RuntimeException('Visual source link refused.'); }
        if (!$f->isFile()) { continue; }
        $path = str_replace('\\', '/', $f->getPathname()); $relative = substr($path, strlen($working) + 1);
        $capture['visual_hashes'][$relative] = hash_file('sha256', $path);
    }
}
ksort($capture['visual_hashes']); $exclusive($dir.'/'.$name, $json($capture));
echo $json(['status' => 'independent-before', 'file' => $name, 'sha256' => hash_file('sha256', $dir.'/'.$name),
    'runtime' => $capture['identity'], 'owners' => array_map(static fn ($p) => ['id' => $p['page']['id'], 'slug' => $p['page']['slug'],
        'practice_id' => $p['practice']['id'], 'uuid' => $p['practice']['uuid'], 'groups' => $p['group_inventory'],
        'linked_rows' => count($p['question_links'])], $capture['pages']),
    'http' => array_map(static fn ($h) => ['url' => $h['url'], 'status' => $h['status']], $capture['http']),
    'visual_files' => count($capture['visual_hashes'])]);
