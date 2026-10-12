<?php
/** Explicit finite M49 CLI entry; capture/audit are read-only, writes delegate to M26 primitives. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($argv[1] ?? '', ['capture', 'proof', 'inventory', 'sync', 'review', 'audit', 'content:patch-advice-modals-m49'], true)) { exit(1); }
$sourceRoot = dirname(__DIR__, 2);
$working = 'D:/DEV/htdocs/gramlyze.loc';
require $working.'/vendor/autoload.php';
require_once $sourceRoot.'/app/Services/M49LocalTargetGuard.php';
$app = require $working.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class); $kernel->bootstrap();
use App\Services\M49LocalTargetGuard as Guard;
use Illuminate\Support\Facades\DB;
if (strtolower(str_replace('\\', '/', realpath(base_path()))) !== strtolower($working)) { throw new RuntimeException('M49 served ROOT differs.'); }
$db = DB::connection();
Guard::assertConfiguredDatabase($db);
$dir = Guard::privateDirectory();
if (is_link($dir) || (!is_dir($dir) && !mkdir($dir, 0700, true))) { throw new RuntimeException('M49 private directory unavailable.'); }
$exclusive = static function (string $path, string $bytes): void {
    $f = fopen($path, 'x');
    if (!$f) { throw new RuntimeException('Exclusive M49 evidence collision.'); }
    try { if (fwrite($f, $bytes) !== strlen($bytes) || !fflush($f)) { throw new RuntimeException('Incomplete M49 evidence.'); } }
    finally { fclose($f); }
};
$json = static fn ($value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";

$mode = $argv[1];
if ($mode === 'content:patch-advice-modals-m49') {
    // Served classes are already registered by the kernel; source-sync hashes bind them to this worktree.
    $app->useDatabasePath($sourceRoot.'/database');
    $kernel->registerCommand($app->make(App\Console\Commands\PatchM49Content::class));
    $input = new Symfony\Component\Console\Input\ArgvInput;
    $status = $kernel->handle($input, new Symfony\Component\Console\Output\ConsoleOutput);
    $kernel->terminate($input, $status); exit($status);
}
if ($mode === 'inventory') {
    $skip = array_column(Guard::BINDINGS, 'definition');
    $hashes=[];
    foreach(['docs/content','database/content-patches','database/seeders/Page_V3'] as $folder) {
        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($working.'/'.$folder,FilesystemIterator::SKIP_DOTS));
        foreach($it as $f) {
            if($f->isLink()){throw new RuntimeException('M49 frozen inventory link refused.');}
            if(!$f->isFile()){continue;}
            $relative=substr(str_replace('\\','/',$f->getPathname()),strlen($working)+1);
            if(preg_match('~(?:^|/)m49[-./]~i',$relative)||in_array($relative,$skip,true)){continue;}
            $hashes[$relative]=hash_file('sha256',$f->getPathname());
        }
    }
    foreach(['app/Support/M*.php','app/Services/M*.php'] as $pattern) {
        foreach(glob($working.'/'.$pattern)?:[] as $file) {
            if(str_starts_with(basename($file),'M49')){continue;}
            $hashes[substr($file,strlen($working)+1)]=hash_file('sha256',$file);
        }
    }
    ksort($hashes); $record=['base_sha'=>'3b0081eda875b3fb5f07cfd8f1441e3ac7102670','files'=>$hashes];
    $exclusive($dir.'/frozen-before-v1.json',$json($record));
    echo $json(['frozen_files'=>count($hashes),'sha256'=>hash_file('sha256',$dir.'/frozen-before-v1.json')]);exit(0);
}
if (in_array($mode,['sync','review','audit'],true)) {
    require_once $sourceRoot.'/app/Support/M49AdviceModalsPackage.php';
    require_once $sourceRoot.'/app/Services/M49ContentPatch.php';
    [$before,$package,$master]=App\Support\M49AdviceModalsPackage::load($sourceRoot);
    App\Support\M49AdviceModalsPackage::validate($before,$package,$master);
    if($mode==='sync'){
        $files=[];
        foreach(App\Services\M49ContentPatch::syncPaths($package) as $p) {
            $a=file_get_contents($working.'/'.$p);$b=file_get_contents($sourceRoot.'/'.$p);
            if($a!==$b&&!(str_ends_with($p,'.php')&&str_replace("\r\n","\n",$a)===str_replace("\r\n","\n",$b))){
                throw new RuntimeException('M49 served source differs: '.$p);
            }
            $files[]=['path'=>$p,'root_after_sha256'=>hash('sha256',$a),'worktree_sha256'=>hash('sha256',$b)];
        }
        $receipt=['base_sha'=>App\Services\M49ContentPatch::ACCEPTED_BASE,'files'=>$files,
            'visual_before'=>['file'=>App\Services\M49ContentPatch::BEFORE_FILE,'sha256'=>App\Services\M49ContentPatch::BEFORE_SHA]];
        $exclusive($dir.'/'.App\Services\M49ContentPatch::SYNC_RECEIPT,$json($receipt));
        echo $json(['status'=>'sync-reviewed','files'=>count($files),'sha256'=>hash_file('sha256',$dir.'/'.App\Services\M49ContentPatch::SYNC_RECEIPT)]);exit(0);
    }
    if($mode==='review'){
        $name=$argv[2]??'';if(!preg_match('/^preview-v[1-9][0-9]*\\.json$/D',$name)){throw new RuntimeException('M49 preview basename required.');}
        $plan=json_decode(file_get_contents($dir.'/'.$name),true,flags:JSON_THROW_ON_ERROR);
        $independent=json_decode(file_get_contents($dir.'/before-v1.json'),true,flags:JSON_THROW_ON_ERROR);
        $expectedUpdates=[];$expectedInserts=[];
        foreach($package['targets'] as $i=>$t){
            $old=$independent['pages'][$t['identity']];$page=$old['page'];
            if($page['text']!==$t['after']['page']['subtitle_text']){$expectedUpdates[]=['table'=>'pages','id'=>$page['id'],'after'=>['text'=>$t['after']['page']['subtitle_text']]];}
            $configs=[['body'=>$t['after']['page']['subtitle_html']],...$t['after']['page']['blocks']];
            $uk=array_values(array_filter($old['blocks'],static fn($r)=>$r['locale']==='uk'));
            foreach($uk as $pos=>$r){if($r['body']!==$configs[$pos]['body']){$expectedUpdates[]=['table'=>'text_blocks','id'=>$r['id'],'after'=>['body'=>$configs[$pos]['body']]];}}
            foreach(array_slice($configs,count($uk),null,true) as $pos=>$cfg){$expectedInserts[]=App\Support\M26DetailPackage::uuid($t['identity'],$cfg,$pos);}
        }
        $got=array_map(static fn($u)=>array_intersect_key($u,array_flip(['table','id','after'])),$plan['updates']);
        $sort=static function(&$a){usort($a,static fn($x,$y)=>strcmp(json_encode($x),json_encode($y)));};$sort($got);$sort($expectedUpdates);
        $uuids=array_column(array_column($plan['inserts'],'fields'),'uuid');sort($uuids);sort($expectedInserts);
        if($got!==$expectedUpdates||$uuids!==$expectedInserts||$plan['state']!=='before'){throw new RuntimeException('M49 exact independent diff review failed.');}
        $record=['preview'=>$name,'preview_sha256'=>hash_file('sha256',$dir.'/'.$name),'updated'=>count($got),'inserted'=>count($uuids),'deleted'=>0,'exact_diff_review'=>true];
        $exclusive($dir.'/reviewed-'.$name,$json($record));echo$json($record);exit(0);
    }
    if($mode==='audit'){
        if(!preg_match('/^after-v[1-9][0-9]*\\.json$/D',$argv[2]??'')){throw new RuntimeException('M49 audit basename required.');}
        $before = json_decode(file_get_contents($dir.'/before-v1.json'), true, flags: JSON_THROW_ON_ERROR);
        $backup = json_decode(file_get_contents($dir.'/backup-v1.json'), true, flags: JSON_THROW_ON_ERROR);

        $exclusive = static function ($path, $bytes): void {
            $f=fopen($path,'x');if(!$f){throw new RuntimeException('M49 audit evidence exists; no overwrite.');}
            try{if(fwrite($f,$bytes)!==strlen($bytes)||!fflush($f)){throw new RuntimeException('M49 audit incomplete.');}}finally{fclose($f);}
        };
        $records = []; $ids = [];
        foreach ($package['targets'] as $i => $target) {
            $owner = (array) $db->table('pages')->where('seeder',$target['identity'])->first();
            $oldOwner = $before['pages'][$target['identity']]['page'];
            $expectedOwner = array_replace($oldOwner,['text'=>$target['after']['page']['subtitle_text']]);
            if ($owner !== $expectedOwner) { throw new RuntimeException('M49 owner postcondition differs.'); }
            $rows = $db->table('text_blocks')->where('page_id',$owner['id'])->orderBy('sort_order')->orderBy('id')->get()->map(static fn($r)=>(array)$r)->all();
            $uk = array_values(array_filter($rows,static fn($r)=>$r['locale']==='uk'));
            $configs = [['type'=>'subtitle','column'=>'header','heading'=>null,'level'=>null,'uuid_key'=>'subtitle','body'=>$target['after']['page']['subtitle_html']],...$target['after']['page']['blocks']];
            if (count($uk)!==count($configs)) { throw new RuntimeException('M49 postcondition block count differs.'); }
            foreach($configs as $pos=>$cfg){
                $row=$uk[$pos];$ids[]=$row['id'];
                if($row['uuid']!==App\Support\M26DetailPackage::uuid($target['identity'],$cfg,$pos)||$row['body']!==$cfg['body']
                    ||$row['locale']!=='uk'||$row['page_id']!==$owner['id']||$row['page_category_id']!==$owner['page_category_id']
                    ||$row['seeder']!==$target['identity']||(int)$row['sort_order']!==$pos){throw new RuntimeException('M49 postcondition native row differs.');}
                foreach(['type','column','heading','level','css_class'] as $field){if($row[$field]!==($cfg[$field]??null)){throw new RuntimeException('M49 block metadata differs.');}}
            }
            $old = $before['pages'][$target['identity']]['blocks'];
            foreach($old as $r){
                $actual=array_values(array_filter($rows,static fn($x)=>$x['id']===$r['id']))[0]??null;
                if($r['locale']==='uk'){unset($r['body'],$actual['body']);}
                if($actual!==$r){throw new RuntimeException('M49 old row IDs/UUID/locale/timestamp/metadata changed.');}
            }
            $records[]=['id'=>$owner['id'],'identity'=>$target['identity'],'slug'=>$target['slug'],'uk_blocks'=>count($uk),'all_rows'=>$rows];
        }
        $auditor = new class($db,$working.'/database',$dir) extends App\Services\M49ContentPatch {
            public function fingerprints(array $ids): array { return $this->protectedFingerprints($ids); }
        };
        $protected = $auditor->fingerprints($ids);
        if($protected!==$backup['protected']){throw new RuntimeException('M49 non-target/bank/progress table fingerprint changed.');}
        $visual=[];
        foreach($before['visual_hashes'] as $path=>$sha){$visual[$path]=hash_file('sha256',$working.'/'.$path);}
        if($visual!==$before['visual_hashes']){throw new RuntimeException('M49 visual source changed.');}
        $htmlAudit = static function(string $html): array {
            $dom=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
            try{$dom->loadHTML($html,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($previous);}
            $x=new DOMXPath($dom);$get=static fn($q)=>$x->query($q)->item(0)?->nodeValue??'';
            $meta=static fn($n)=>$x->query('//meta[@name="'.$n.'" or @property="'.$n.'"]/@content')->item(0)?->nodeValue??'';
            $content=$x->query('//div[contains(concat(" ",normalize-space(@class)," ")," theory-design ")]')->item(0);
            $structured=[];foreach($x->query('//script[@type="application/ld+json"]') as $s){$structured[]=json_decode($s->textContent,true);}
            return ['title'=>$get('//title'),'h1'=>$get('//h1'),'description'=>$meta('description'),'og_description'=>$meta('og:description'),
                'twitter_description'=>$meta('twitter:description'),'robots'=>$meta('robots'),'canonical'=>$get('//link[@rel="canonical"]/@href'),
                'structured'=>$structured,'points'=>$x->query('//*[@data-theory-point-panel]')->length,
                'details'=>$x->query('//*[@data-theory-point-index]')->length,'practice_models'=>substr_count($html,'x-data="theoryPracticeSet('),
                'content_text'=>$content?->textContent??''];
        };
        $http=[];
        foreach($package['targets'] as $i=>$target){
         foreach([$target['local_url'],'http://gramlyze.loc/courses/english-grammar-theory/lesson/'.implode('/', $target['ancestry']).'/'.$target['slug']] as $url){
            $robotsHeader = '';
            $curl=curl_init($url);curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>30,
                CURLOPT_HEADERFUNCTION=>static function($handle,$header) use(&$robotsHeader) { if(stripos($header,'X-Robots-Tag:')===0){$robotsHeader=trim(substr($header,13));} return strlen($header); },
                CURLOPT_PROXY=>'',CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1']]);
            $html=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);$ip=curl_getinfo($curl,CURLINFO_PRIMARY_IP);
            if($html===false||$status!==200||$ip!=='127.0.0.1'){throw new RuntimeException('M49 local AFTER HTTP unavailable.');}
            $file=substr($argv[2],0,-5).'-http-'.hash('sha256',$url).'.html';$exclusive($dir.'/'.$file,$html);
            $audit=$htmlAudit($html); $audit['robots_header']=$robotsHeader;
            $prior=array_values(array_filter($before['http'],static fn($h)=>$h['url']===$url))[0];
            $old=$htmlAudit(file_get_contents($dir.'/'.$prior['file']));
            $course=str_contains($url,'/courses/');
            if($course){
                // Compare teaching text independently; volatile CSRF/session markup is not teaching content.
                foreach(['title','h1','content_text'] as $field){if(preg_replace('/\s+/u',' ',trim($audit[$field]))!==preg_replace('/\s+/u',' ',trim($old[$field]))){throw new RuntimeException('M49 course AFTER differs from live BEFORE: '.$field);}}
            }else{
                $expected=App\Support\PageMetadata::theory($target['after']['page']['title'],$target['after']['page']['category']['title'],$master['lessons'][$i]['blocks'][0]['data']['intro'],$target['identity'],'uk');
                if($audit['title']!==$expected['title']||$audit['description']!==$expected['description']
                    ||$audit['og_description']!==$expected['description']||$audit['twitter_description']!==$expected['description']
                    ||$audit['h1']!==$old['h1']||$audit['canonical']!==$old['canonical']
                    ||!str_contains($audit['robots'].' '.$audit['robots_header'],'noindex')){throw new RuntimeException('M49 page metadata/canonical/noindex postcondition differs.');}
            }
            $http[]=['url'=>$url,'status'=>$status,'file'=>$file,'sha256'=>hash('sha256',$html),'course_before_parity'=>$course,
                'before_metadata'=>array_intersect_key($old,array_flip(['title','description','og_description','twitter_description','canonical','robots','structured'])),
                'after'=>array_diff_key($audit,['content_text'=>true])];
         }
        }
        $out=['at'=>gmdate('c'),'runtime'=>Guard::publicIdentity($db,$working.'/public'),'pages'=>$records,'protected'=>$protected,'visual_hashes'=>$visual,'http'=>$http,
            'counts'=>['updated'=>count($backup['updates']),'inserted'=>count($backup['inserts']),'deleted'=>0],'bank_progress_changes'=>0,'visual_changes'=>0,'course_changes'=>0];
        $bytes=json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";$exclusive($dir.'/'.$argv[2],$bytes);
        echo json_encode(['status'=>'postconditions-pass','file'=>$argv[2],'sha256'=>hash_file('sha256',$dir.'/'.$argv[2]),'protected_tables'=>count($protected),
            'visual_files'=>count($visual),'course_changes'=>0,'http'=>array_map(static fn($h)=>['url'=>$h['url'],'status'=>$h['status'],'points'=>$h['after']['points'],
                'details'=>$h['after']['details'],'practice_models'=>$h['after']['practice_models'],'course_before_parity'=>$h['course_before_parity']],$http)],
            JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";

        exit(0);
    }
}

$physical = (new Guard)->physicalEvidence();
$physicalFiles = ['C:/Program Files/xampp/apache/conf/httpd.conf', 'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
    'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', $working.'/public/index.php'];
$physicalHashes = [];
foreach ($physicalFiles as $path) { $physicalHashes[$path] = hash_file('sha256', $path); }
if (($argv[1] ?? '') === 'proof') {
    $nonce = bin2hex(random_bytes(16));
    $routeBytes = file_get_contents($working.'/routes/api.php');
    if (str_contains($routeBytes, 'm49-target-')) { throw new RuntimeException('M49 temporary route already present.'); }
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
if (!preg_match('/^before-v[1-9][0-9]*\.json$/D', $name)) { throw new RuntimeException('M49 requires new BEFORE basename.'); }
$capture = ['at' => gmdate('c'), 'identity' => Guard::publicIdentity($db, $working.'/public'),
    'physical' => $physical, 'physical_hashes' => $physicalHashes, 'definitions' => [], 'pages' => [], 'http' => [], 'visual_hashes' => []];
$rows = static fn ($q): array => $q->get()->map(static fn ($row) => (array) $row)->all();
$http = static function (string $path) use ($exclusive, $dir, $name): array {
    if (!str_starts_with($path, '/theory/') && !str_starts_with($path, '/courses/english-grammar-theory/lesson/')) { throw new RuntimeException('M49 HTTP path outside scope.'); }
    $robotsHeader = '';
    $curl = curl_init('http://gramlyze.loc'.$path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 30,
        CURLOPT_HEADERFUNCTION => static function ($handle, $header) use (&$robotsHeader) {
            if (stripos($header, 'X-Robots-Tag:') === 0) { $robotsHeader = trim(substr($header, 13)); }
            return strlen($header);
        }, CURLOPT_PROXY => '', CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1']]);
    $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
    if ($body === false || $ip !== '127.0.0.1') { throw new RuntimeException('M49 local HTTP unavailable.'); }
    $file = substr($name, 0, -5).'-http-'.hash('sha256', $path).'.html';
    $exclusive($dir.'/'.$file, $body);
    preg_match('~<title>(.*?)</title>~s', $body, $title);
    return ['url' => 'http://gramlyze.loc'.$path, 'status' => $status, 'body_sha256' => hash('sha256', $body), 'file' => $file,
        'title' => html_entity_decode($title[1] ?? ''), 'robots_header' => $robotsHeader, 'canonical_path' => $path];
};
foreach (Guard::SCOPE as $scopeIndex => $scope) {
    $binding = Guard::BINDINGS[$scopeIndex];
    $identity = $scope['identity']; $short = substr($identity, strrpos($identity, '\\') + 1);
    $definitionPath = $binding['definition'];
    $bytes = file_get_contents($working.'/'.$definitionPath);
    $wtBytes = file_get_contents($sourceRoot.'/'.$definitionPath);
    if ($bytes !== $wtBytes) { throw new RuntimeException('M49 ROOT/worktree definition BEFORE differs.'); }
    $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    if ($definition['seeder']['class'] !== $identity || $definition['page']['locale'] !== 'uk'
        || $definition['page']['category']['slug'] !== $binding['ancestry'][0]
        || $definition['slug'] !== $binding['slug']
        || json_decode($definition['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR)['level'] !== $binding['display_level']) {
        throw new RuntimeException('M49 definition BEFORE exact owner/category/display level differs.');
    }
    $pageRows = $rows($db->table('pages')->where('seeder', $identity));
    if (count($pageRows) !== 1) { throw new RuntimeException('M49 page owner missing/ambiguous.'); }
    $page = $pageRows[0];
    if ($page['slug'] !== $definition['slug'] || $page['title'] !== $definition['page']['title'] || $page['type'] !== 'theory'
        || $page['text'] !== $definition['page']['subtitle_text']) { throw new RuntimeException('M49 page BEFORE mismatch.'); }
    $chain = []; $cat = $page['page_category_id'];
    while ($cat) {
        $row = (array) $db->table('page_categories')->where('id', $cat)->first();
        if (!$row || $row['language'] !== 'uk' || $row['type'] !== 'theory') { throw new RuntimeException('M49 category mismatch.'); }
        array_unshift($chain, $row); $cat = $row['parent_id'];
        if (count($chain) > 8) { throw new RuntimeException('M49 ancestry too deep.'); }
    }
    if (array_column($chain, 'slug') !== $binding['ancestry']) { throw new RuntimeException('M49 exact owner category ancestry differs.'); }
    $blocks = $rows($db->table('text_blocks')->where('page_id', $page['id'])->orderBy('sort_order')->orderBy('id'));
    $uk = array_values(array_filter($blocks, static fn ($row) => $row['locale'] === 'uk'));
    $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'], 'uuid_key' => 'subtitle', 'level' => null];
    $configs = [$subtitle, ...$definition['page']['blocks']];
    if (count($configs) !== count($uk)) { throw new RuntimeException('M49 unexpected UK BEFORE blocks.'); }
    foreach ($configs as $slot => $config) {
        $row = $uk[$slot]; $uuid = App\Support\M26DetailPackage::uuid($identity, $config, $slot);
        if ($row['uuid'] !== $uuid || $row['seeder'] !== $identity || (int) $row['sort_order'] !== $slot || $row['body'] !== $config['body']
            || $row['type'] !== $config['type'] || $row['column'] !== $config['column'] || $row['level'] !== ($config['level'] ?? null)) {
            throw new RuntimeException('M49 block BEFORE mismatch at '.$definition['slug'].' slot '.$slot);
        }
    }
    $uuids = array_column($blocks, 'uuid');
    $links = $rows($db->table('question_theory_text_blocks')->whereIn('text_block_uuid', $uuids)->orderBy('id'));
    $pageModel = App\Models\Page::findOrFail($page['id']);
    $topicTests = app(App\Services\TheoryPagePromptLinkedTestsService::class)->buildForPage($pageModel);
    $capture['definitions'][$identity] = ['path' => $definitionPath, 'sha256' => hash('sha256', $bytes), 'bytes' => $bytes, 'definition' => $definition];
    $capture['pages'][$identity] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $blocks, 'question_links' => $links, 'topic_tests' => $topicTests];
    $capture['http'][] = $http($scope['path']);
    $capture['http'][] = $http('/courses/english-grammar-theory/lesson/'.implode('/', $binding['ancestry']).'/'.$page['slug']);
}
foreach (['resources/views', 'resources/css', 'resources/js', 'public/js'] as $folder) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($working.'/'.$folder, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $f) {
        if ($f->isLink()) { throw new RuntimeException('M49 linked visual source refused.'); }
        if (!$f->isFile()) { continue; }
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
