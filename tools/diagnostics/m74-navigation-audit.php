<?php

/**
 * Explicit M74 AFTER navigation proof; no apply, route or configuration edits.
 * php tools/diagnostics/m74-navigation-audit.php navigation-after-v1.json
 *   final-readonly-v1.json <reviewed-final-raw-sha256>
 * Fresh read-only native resolver plus anonymous fixed-loopback GETs. This is not
 * a browser test or a claim of unchanged neighboring learning DOM.
 */
$failure=static function(Throwable $error):void{
    fwrite(STDERR,$error instanceof RuntimeException&&!$error instanceof PDOException
        ?'M74 navigation: '.$error->getMessage()."\n":"M74 navigation incomplete; no successful receipt. No SQL writes.\n");
    exit(1);
};
set_exception_handler($failure);
$name=$argv[1]??'';$finalName=$argv[2]??'';$finalSha=$argv[3]??'';
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==4
    ||preg_match('/^navigation-after-v[1-9][0-9]*\.json$/D',$name)!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$finalName)!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$finalSha)!==1){
    throw new RuntimeException('Fresh navigation output and reviewed independent final receipt/SHA required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m74-local';
$normal=static function(string $path):string{
    $real=realpath($path);if($real===false){throw new RuntimeException('Required local path is missing.');}
    return rtrim(strtolower(str_replace('\\','/',$real)),'/');
};
if($normal(dirname(__DIR__,2))!==$normal($root)||!is_dir($dir)||is_link($dir)
    ||$normal($dir)!==$normal($root).'/storage/app/seo-m74-local'
    ||file_exists($dir.'/'.$name)||is_link($dir.'/'.$name)){
    throw new RuntimeException('Actual served ROOT/private output differs or output exists.');
}
$hash=static function(string $path):string{
    if(!is_file($path)||is_link($path)){throw new RuntimeException('Pinned source/evidence missing or linked.');}
    $sha=hash_file('sha256',$path);if(!is_string($sha)){throw new RuntimeException('Pinned input cannot be hashed.');}return $sha;
};
$read=static function(string $basename,string $sha)use($dir,$hash):array{
    if(preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$basename)!==1||!hash_equals($sha,$hash($dir.'/'.$basename))){
        throw new RuntimeException('Independent evidence basename or bytes differ.');
    }
    return json_decode(file_get_contents($dir.'/'.$basename),true,flags:JSON_THROW_ON_ERROR);
};
$beforeSha='9e7abb860af2a61bfb10f61047f7ffaad055e20368edfc24c9ed095301516fdc';
$before=$read('before-v1.json',$beforeSha);$final=$read($finalName,$finalSha);
if(($before['served_root']??null)!==$root||($before['sql_before']??null)!==($before['sql_after_http']??null)
    ||($final['independent_before_sha256']??null)!==$beforeSha
    ||($final['runtime']??null)!==($before['identity']??null)
    ||($final['normalized_before']??null)!==($before['sql_before']??null)
    ||($final['all_old_rows_and_timestamps_exact']??null)!==true
    ||($final['independent_before_projection_exact']??null)!==true
    ||($final['reviewed_protected_projection_exact']??null)!==true
    ||($final['db_writes']??null)!==0||($final['http_requests']??null)!==0
    ||!is_array($final['sql_after']??null)||count($final['sql_after'])!==46){
    throw new RuntimeException('Independent final receipt does not reconstruct complete reviewed BEFORE.');
}
require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler($failure);
use App\Models\Page;
use App\Models\PageCategory;
use App\Services\M74LocalTargetGuard as Guard;
use App\Support\M74OtherPronounsBasics as Package;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
if($normal(base_path())!==$normal($root)||$normal(app()->environmentPath())!==$normal($root)||app()->getLocale()!=='uk'
    ||Package::BASE_SHA!=='fd1ad30e710b3d950e574848c886d86f6045f2e3'
    ||Package::PAGE_IDS!==[81,126,124]||Package::CATEGORY_IDS!==[16,20,20]){
    throw new RuntimeException('Exact M74 application/locale/page/category scope differs.');
}
[$sourceBefore,$patch,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$patch,$master,$contract);
$inputs=[__FILE__=>$hash(__FILE__),$dir.'/before-v1.json'=>$beforeSha,$dir.'/'.$finalName=>$finalSha];
foreach(Package::profile()['artifacts'] as $path=>$sha){$inputs[$root.'/'.$path]=$sha;}
foreach($patch['targets'] as $target){
    $path=$root.'/'.$target['path'];$inputs[$path]=$hash($path);
    if(json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR)!==$target['after']){
        throw new RuntimeException('Current target definition is not its exact independently frozen AFTER.');
    }
}
$controller='app/Http/Controllers/PageController.php';
$controllerSha=$before['shared_source_hashes'][$controller]['root']??null;
if(!is_string($controllerSha)||!hash_equals($controllerSha,$hash($root.'/'.$controller))
    ||$normal((new ReflectionClass(App\Http\Controllers\PageController::class))->getFileName())!==$normal($root.'/'.$controller)){
    throw new RuntimeException('Actual native PageController differs from independently captured BEFORE.');
}
$inputs[$root.'/'.$controller]=$controllerSha;
foreach($before['physical_hashes'] as $path=>$sha){
    if(!hash_equals($sha,$hash($path))){throw new RuntimeException('Independently proven physical vhost/application target changed.');}
    $inputs[$path]=$sha;
}
$db=Illuminate\Support\Facades\DB::connection();Guard::assertConfiguredDatabase($db);
$guard=new Guard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key){
    if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1){
        throw new RuntimeException('Physical MySQL evidence is not an absolute local Windows path.');
    }
}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){
    throw new RuntimeException('Actual local MySQL identity differs.');
}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
$runtime=Guard::publicIdentity($db,$root.'/public');
if($runtime!==$before['identity']||$runtime!==$final['runtime']){throw new RuntimeException('Runtime differs from independently proven BEFORE/final identity.');}
$readOnly=static function(Closure $callback)use($db){
    $db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
    try{return $callback();}finally{$db->rollBack();}
};
$fingerprints=static function()use($db):array{
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);
    if(count($tables)!==46){throw new RuntimeException('Independent 46-table inventory differs.');}
    $result=[];
    foreach($tables as $table){
        $columns=$db->getSchemaBuilder()->getColumnListing($table);$query=$db->table($table);
        foreach(in_array('id',$columns,true)?['id']:$columns as $column){$query->orderBy($column);}
        $context=hash_init('sha256');$count=0;
        foreach($query->cursor() as $record){hash_update($context,App\Services\M26ContentPatch::digest((array)$record)."\n");++$count;}
        $result[$table]=['count'=>$count,'sha256'=>hash_final($context)];
    }
    return $result;
};
$sqlBefore=$readOnly($fingerprints);
if($sqlBefore!==$final['sql_after']){throw new RuntimeException('Current SQL is not reviewed exact AFTER.');}
$same=static function(mixed $actual,mixed $expected,string $label):void{
    if($actual!==$expected){throw new RuntimeException($label.' differs.');}
};
$resolver=new class extends App\Http\Controllers\PageController{
    public function __construct(){}
    public function path(PageCategory $category):string{return $this->categorySlugPath($category);}
    public function resolve(string $path):?PageCategory{return $this->resolveCategoryBySlugPath($path);}
};
$routes=[];$navigation=[];$targetByPath=[];$chips=0;$realAnchors=0;
$readOnly(static function()use(&$routes,&$navigation,&$targetByPath,&$chips,&$realAnchors,$master,$patch,$sourceBefore,$resolver,$same,$root,$hash,&$inputs):void{
    foreach($master['lessons'] as $i=>$lesson){
        $blocks=array_values(array_filter($lesson['blocks'],static fn($b)=>$b['type']==='navigation-chips'));
        $same(count($blocks),1,'One native navigation per M74 lesson');$data=$blocks[0]['data'];
        $same(count($data['items']),[8,9,9][$i],'Per-owner footer item count');
        if($i===0){$same($data['items'],json_decode($sourceBefore['targets'][0]['before']['page']['blocks'][5]['body'],true,flags:JSON_THROW_ON_ERROR)['items'],'All eight original Another URLs/labels/current flags');}
        $owner=$lesson['identity'];$ownerPath=parse_url($patch['targets'][$i]['local_url'],PHP_URL_PATH);
        $targetByPath[$ownerPath]=['index'=>$i,'owner'=>$owner,'data'=>$data];$current=[];
        foreach($data['items'] as $item){
            $url=$item['url']??null;
            if(!is_string($url)||preg_match('~^/theory/(imennyky-artykli-ta-kilkist|zaimennyky-ta-vkazivni-slova)/([a-z0-9-]+)$~D',$url,$m)!==1
                ||!is_bool($item['current']??null)||!is_string($item['label']??null)||trim($item['label'])===''){
                throw new RuntimeException('Footer must have explicit native local destinations, labels and current flags; no # placeholders.');
            }
            ++$chips;if($item['current']){$current[]=$url;}else{++$realAnchors;}
            $category=$resolver->resolve($m[1]);
            if(!$category instanceof PageCategory||$category->type!=='theory'||$category->language!=='uk'
                ||$category->id!==($m[1]==='imennyky-artykli-ta-kilkist'?16:20)){
                throw new RuntimeException('Native category resolver does not bind exact local UK category.');
            }
            $page=Page::query()->where('page_category_id',$category->id)->where('slug',$m[2])->where('type','theory')->sole();
            $resolved=route('theory.show',['categoryPath'=>$resolver->path($category),'pageSlug'=>$page->slug],false);
            $same($resolved,$url,'Native PageController/category-derived actual destination');
            $definitionPath=Acceptance::definitionPath($page->seeder);$sourcePath=$root.'/'.$definitionPath;$sourceSha=$hash($sourcePath);
            if(isset($inputs[$sourcePath])&&!hash_equals($inputs[$sourcePath],$sourceSha)){
                throw new RuntimeException('A repeated or independently pinned source changed during native resolution.');
            }
            $inputs[$sourcePath]=$sourceSha;
            $definition=json_decode(file_get_contents($root.'/'.$definitionPath),true,flags:JSON_THROW_ON_ERROR);
            if(($definition['seeder']['class']??null)!==$page->seeder||($definition['slug']??null)!==$page->slug
                ||($definition['page']['locale']??null)!=='uk'||($definition['page']['category']['slug']??null)!==$category->slug){
                throw new RuntimeException('Resolved actual SQL page has no exact matching UK versioned source.');
            }
            $record=['identity'=>$page->seeder,'page_id'=>$page->id,'category_id'=>$category->id,'raw_title'=>$page->getRawOriginal('title'),'url'=>$url];
            if(isset($routes[$url])){$same($routes[$url],$record,'Repeated footer destination exact SQL/source identity');}
            $routes[$url]=$record;$navigation[$owner][]=$item;
        }
        $same($current,[$ownerPath],'Exact one current resource per owner');
        $same($routes[$ownerPath]['page_id'],Package::PAGE_IDS[$i],'M74 current resource actual Page ID');
    }
});
$same($chips,26,'All frozen footer chips');$same($realAnchors,23,'All noncurrent footer anchors');
$same(count($routes),17,'Deduplicated eight noun and nine pronoun destinations');
$pronoun=array_values(array_filter(array_keys($routes),static fn($p)=>str_starts_with($p,'/theory/zaimennyky-ta-vkazivni-slova/')));
$same(count($pronoun),9,'Nine actual pronoun destinations');
$plain=static fn(string $v):string=>trim((string)preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($v),ENT_QUOTES|ENT_HTML5,'UTF-8')));
$started=gmdate('c');$rows=[];$renderedAnchors=0;$renderedChips=0;
foreach($routes as $path=>$identity){
    $url='http://gramlyze.loc'.$path;$curl=curl_init($url);$robots=null;
    curl_setopt_array($curl,[CURLOPT_HTTPGET=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_MAXREDIRS=>0,
        CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>45,CURLOPT_PROXY=>'',CURLOPT_NOPROXY=>'*',CURLOPT_NETRC=>CURL_NETRC_IGNORED,
        CURLOPT_COOKIE=>'',CURLOPT_PROTOCOLS=>CURLPROTO_HTTP,CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1'],CURLOPT_HTTPHEADER=>['Accept: text/html'],
        CURLOPT_HEADERFUNCTION=>static function($handle,string $header)use(&$robots):int{
            if(stripos($header,'X-Robots-Tag:')===0){$robots=trim(substr($header,13));}return strlen($header);
        }]);
    try{
        $html=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);
        if(!is_string($html)||$status!==200||curl_getinfo($curl,CURLINFO_PRIMARY_IP)!=='127.0.0.1'
            ||curl_getinfo($curl,CURLINFO_PRIMARY_PORT)!==80||curl_getinfo($curl,CURLINFO_EFFECTIVE_URL)!==$url
            ||curl_getinfo($curl,CURLINFO_REDIRECT_COUNT)!==0){throw new RuntimeException('Exact anonymous direct loopback GET failed: '.$path);}
    }finally{unset($curl);}
    $dom=new DOMDocument('1.0','UTF-8');$old=libxml_use_internal_errors(true);
    try{$loaded=$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($old);}
    if(!$loaded){throw new RuntimeException('Navigation destination HTML cannot be parsed.');}
    $xp=new DOMXPath($dom);$h1=$xp->query('//h1');$canonical=$xp->query('//link[@rel="canonical"]');$main=$xp->query('//*[@data-theory-main]');
    $same($h1->length,1,'Destination H1 count');$same($canonical->length,1,'Destination canonical count');$same($main->length,1,'Destination canonical theory boundary count');
    if($plain($h1->item(0)->textContent)===''){throw new RuntimeException('Navigation destination has an empty H1.');}
    $same($canonical->item(0)->getAttribute('href'),'https://gramlyze.com'.$path,'Canonical string only, never requested');
    $same($robots,'noindex, nofollow, noarchive','Local robots header');
    $row=$identity+['http_status'=>$status,'html_sha256'=>hash('sha256',$html),'h1'=>$plain($h1->item(0)->textContent),
        'canonical_string'=>$canonical->item(0)->getAttribute('href'),'target'=>isset($targetByPath[$path])];
    if(isset($targetByPath[$path])){
        $target=$targetByPath[$path];$i=$target['index'];$same($row['h1'],Package::RENDERED_TITLES[$i],'M74 exact rendered H1');
        $data=$target['data'];
        if(!in_array($data['title'],['Продовжуй навчання','Сторінки цього розділу'],true)){throw new RuntimeException('Finite native navigation heading differs.');}
        $navs=$xp->query('.//nav[p[normalize-space(.)="'.$data['title'].'"]]',$main->item(0));$same($navs->length,1,'One actual native footer');
        $nodes=$xp->query('./div/*',$navs->item(0));$same($nodes->length,count($data['items']),'Actual native footer chip count');$links=[];
        foreach($data['items'] as $n=>$expected){
            $node=$nodes->item($n);$same($plain($node->textContent),$plain($expected['label']),'Actual footer label');++$renderedChips;
            if(!isset($routes[$expected['url']])){throw new RuntimeException('Footer destination was not independently native-resolved.');}
            if($expected['current']){$same($node->nodeName,'span','Current resource is not a self anchor');$same($expected['url'],$path,'Current resource owner');}
            else{$same($node->nodeName,'a','Noncurrent resource is clickable');$same($node->getAttribute('href'),$expected['url'],'Actual exact native href');$links[]=$expected['url'];++$renderedAnchors;}
        }
        $row['rendered_footer_links']=$links;$row['rendered_footer_current']=$path;
    }
    $rows[$path]=$row;
}
$same($renderedChips,26,'Rendered footer chips');$same($renderedAnchors,23,'Rendered real anchors');
$linkGets=[];
foreach($rows as $row){foreach($row['rendered_footer_links']??[] as $href){
    $get=$rows[$href]??null;if(($get['http_status']??null)!==200){throw new RuntimeException('A clickable footer link has no exact fresh HTTP 200.');}
    $linkGets[]=['owner'=>$row['identity'],'href'=>$href,'status'=>200,'destination_page_id'=>$get['page_id'],'destination_html_sha256'=>$get['html_sha256']];
}}
$same(count($linkGets),23,'Every clickable anchor has actual GET evidence');
$sqlAfter=$readOnly($fingerprints);$same($sqlAfter,$sqlBefore,'All 46 tables unchanged during read-only navigation resolution/HTTP');
foreach($inputs as $path=>$sha){if(!hash_equals($sha,$hash($path))){throw new RuntimeException('Reviewed input changed during navigation audit.');}}
$receipt=['schema_version'=>1,'status'=>'PASS','started_at'=>$started,'finished_at'=>gmdate('c'),'base_sha'=>Package::BASE_SHA,
    'independent_before_sha256'=>$beforeSha,'reviewed_final_file'=>$finalName,'reviewed_final_sha256'=>$finalSha,'runtime'=>$runtime,
    'native_page_controller_before_current_sha256'=>$controllerSha,'diagnostic_sha256'=>$inputs[__FILE__],
    'physical_target_proof'=>'Fresh Windows Apache/MySQL listener evidence plus unchanged independently proven physical files; exact runtime equals BEFORE/final.',
    'transport'=>['method'=>'GET','anonymous'=>true,'fixed_loopback'=>'127.0.0.1:80','redirects'=>0,'proxy'=>false,'cookies_sent'=>false],
    'native_resolver_destinations'=>$routes,'source_footer_items'=>$navigation,'HTTP'=>array_values($rows),'HTTP_200'=>count($rows),
    'footer_chips'=>26,'clickable_anchors'=>23,'distinct_destinations'=>17,'pronoun_explicit_url_items'=>18,'pronoun_distinct_destinations'=>9,
    'every_footer_anchor_get_200'=>$linkGets,'all_46_tables_before_http'=>$sqlBefore,'all_46_tables_after_http'=>$sqlAfter,
    'sql_unchanged_during_http'=>true,'production_requests'=>0,'db_writes'=>0,'runtime_or_route_edits'=>0,
    'not_checked'=>['Browser layout/visibility/interaction','Neighbor learning DOM equality (separate source/SQL/old-consumer evidence)','Own practice scoring (separate browser/native-factory evidence)']];
$bytes=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n";
$handle=fopen($dir.'/'.$name,'x');if(!$handle){throw new RuntimeException('Exclusive navigation receipt creation failed.');}
try{if(fwrite($handle,$bytes)!==strlen($bytes)||!fflush($handle)){throw new RuntimeException('Private navigation receipt write incomplete.');}}finally{fclose($handle);}
echo json_encode(['status'=>'PASS','file'=>$name,'sha256'=>hash('sha256',$bytes),'HTTP_200'=>count($rows),
    'rendered_footer_chips'=>$renderedChips,'all_clickable_anchors_get_200'=>count($linkGets),'distinct_destinations'=>17,
    'sql_unchanged_during_http'=>true,'db_writes'=>0,'browser_interaction'=>'not_checked'],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
