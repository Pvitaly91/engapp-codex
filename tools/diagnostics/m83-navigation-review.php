<?php

/** Fresh exact SQL/resolver/loopback proof for data-only footer URLs. No source/content writes. */
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==2
    ||preg_match('/^navigation-review-v[1-9][0-9]*\.json$/D',$argv[1])!==1){throw new RuntimeException('M83 needs one exclusive navigation receipt basename.');}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m83-local';
if(strtolower(str_replace('\\','/',dirname(__DIR__,2)))!==strtolower($root)){throw new RuntimeException('M83 navigation proof must run from served ROOT.');}
require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db=Illuminate\Support\Facades\DB::connection();App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$beforeSha='d522a715b123748b84f52f076b156ccf5224892f138d5429062d9527a843e33e';
if(is_link($dir.'/before-v1.json')||!hash_equals($beforeSha,hash_file('sha256',$dir.'/before-v1.json'))){throw new RuntimeException('M83 navigation BEFORE changed.');}
$before=json_decode(file_get_contents($dir.'/before-v1.json'),true,flags:JSON_THROW_ON_ERROR);
$guard=new App\Services\M49LocalTargetGuard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file');
$normal=static fn($p)=>rtrim(strtolower(str_replace('\\','/',(string)realpath($p))),'/');
if($server['db']!=='gr2'||(int)$server['port']!==3306||!is_file($server['pid_file'])){throw new RuntimeException('M83 navigation local database differs.');}
$pid=trim(file_get_contents($server['pid_file']));if(!ctype_digit($pid)){throw new RuntimeException('M83 navigation MySQL PID differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
$identity=App\Services\M49LocalTargetGuard::publicIdentity($db,$root.'/public');
if($identity!==$before['identity']){throw new RuntimeException('M83 navigation current runtime differs from BEFORE.');}
$fingerprints=static function()use($db):array{
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);$out=[];
    foreach($tables as $table){$columns=$db->getSchemaBuilder()->getColumnListing($table);$q=$db->table($table);
        foreach(in_array('id',$columns,true)?['id']:$columns as $column){$q->orderBy($column);}
        $hash=hash_init('sha256');$count=0;foreach($q->cursor() as $record){hash_update($hash,App\Services\M26ContentPatch::digest((array)$record)."\n");++$count;}
        $out[$table]=['count'=>$count,'sha256'=>hash_final($hash)];
    }return $out;
};
$readonly=static function(Closure $f)use($db){$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();try{return $f();}finally{$db->rollBack();}};
$sqlBefore=$readonly($fingerprints);if($sqlBefore!==$before['sql_before']){throw new RuntimeException('M83 navigation found working BEFORE table drift.');}
$resolver=new class extends App\Http\Controllers\PageController{
    public function __construct(){}
    public function path(App\Models\PageCategory $category):string{return $this->categorySlugPath($category);}
};
$mixed=[
    ['A1 Mixed Revision','BasicGrammarA1MixedRevisionTheorySeeder'],['A2 Mixed Revision','BasicGrammarA2MixedRevisionTheorySeeder'],
    ['B1 Mixed Revision','BasicGrammarB1MixedRevisionTheorySeeder'],['B2 Mixed Revision','B2MixedRevisionTheorySeeder'],
    ['C1 Mixed Revision','C1MixedRevisionTheorySeeder'],['C2 Mixed Revision','C2MixedRevisionTheorySeeder'],
];
$basic=[
    ['Parts of Speech — Частини мови','BasicGrammarPartsOfSpeechTheorySeeder'],
    ['Sentence Structure — Будова речення (SVO)','BasicGrammarSentenceStructureSvoTheorySeeder'],
    ['Sentence Types — Види речень','BasicGrammarSentenceTypesTheorySeeder'],
    ['Imperatives — Наказові речення','BasicGrammarImperativesTheorySeeder'],
    ['Conjunctions — and, but, or, because, so','BasicGrammarConjunctionsTheorySeeder'],
    ['Basic Word Order — Базовий порядок слів','WordOrder\\BasicWordOrderTheorySeeder'],
];
$destinations=[];$sourceHashes=[];
$capturedSource=json_decode(file_get_contents($dir.'/independent-source-before-v1.json'),true,flags:JSON_THROW_ON_ERROR);
if(!hash_equals('bae358ff481654fcd38be6a5802a9419f4b4edf7132c931e3e38ef82bc535618',hash_file('sha256',$dir.'/independent-source-before-v1.json'))){throw new RuntimeException('M83 independent paired source baseline changed.');}
$readonly(static function()use($root,$wt,$db,$resolver,$mixed,$basic,$capturedSource,&$destinations,&$sourceHashes):void{
    foreach([...$mixed,...$basic] as [$label,$suffix]){
        $owner='Database\\Seeders\\Page_V3\\BasicGrammar\\'.$suffix;
        $path='database/seeders/Page_V3/BasicGrammar/'.str_replace('\\','/',$suffix).'/definition.json';
        $rootBytes=file_get_contents($root.'/'.$path);$wtBytes=file_get_contents($wt.'/'.$path);$pins=$capturedSource['files'][$path]??null;
        $capturedPair=is_array($pins)&&($pins['served_root']['raw_sha256']??null)===hash('sha256',$rootBytes)
            &&($pins['worktree']['raw_sha256']??null)===hash('sha256',$wtBytes)
            &&str_replace("\r\n","\n",$rootBytes)===str_replace("\r\n","\n",$wtBytes);
        if(is_link($root.'/'.$path)||is_link($wt.'/'.$path)||!is_file($root.'/'.$path)
            ||($rootBytes!==$wtBytes&&!$capturedPair)){throw new RuntimeException('M83 footer destination source parity differs: '.$path);}
        $source=json_decode(file_get_contents($root.'/'.$path),true,flags:JSON_THROW_ON_ERROR);$sourceHashes[$path]=hash_file('sha256',$root.'/'.$path);
        if($source['seeder']['class']!==$owner||$source['page']['locale']!=='uk'){throw new RuntimeException('M83 footer destination source identity differs.');}
        $page=App\Models\Page::query()->with('category')->where('seeder',$owner)->sole();
        if($page->type!=='theory'||$page->slug!==$source['slug']||$page->category?->slug!==$source['page']['category']['slug']){throw new RuntimeException('M83 footer destination SQL differs.');}
        $category=$resolver->path($page->category);$route=app('router')->getRoutes()->getByName('theory.show');
        $url='/'.str_replace(['{categoryPath}','{pageSlug}'],[$category,$page->slug],$route->uri());
        if(!in_array('GET',$route->methods(),true)||preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)+$~D',$url)!==1
            ||app('router')->getRoutes()->match(Illuminate\Http\Request::create('http://gramlyze.loc'.$url,'GET'))->getName()!=='theory.show'){
            throw new RuntimeException('M83 footer destination named route differs.');
        }
        $destinations[$label]=['label'=>$label,'url'=>$url,'target_page_id'=>$page->id,'target_owner'=>$owner];
    }
});
foreach($destinations as &$destination){
    $url='http://gramlyze.loc'.$destination['url'];$ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_MAXREDIRS=>0,
        CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>120,CURLOPT_PROXY=>'',CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1'],CURLOPT_HTTPHEADER=>['Accept: text/html']]);
    try{$html=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
        if(!is_string($html)||$status!==200||curl_getinfo($ch,CURLINFO_EFFECTIVE_URL)!==$url||curl_getinfo($ch,CURLINFO_PRIMARY_IP)!=='127.0.0.1'
            ||curl_getinfo($ch,CURLINFO_REDIRECT_COUNT)!==0){throw new RuntimeException('M83 footer destination GET failed: '.$destination['url']);}
    }finally{curl_close($ch);}
    $dom=new DOMDocument('1.0','UTF-8');$prior=libxml_use_internal_errors(true);
    try{$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($prior);}
    $xp=new DOMXPath($dom);if($xp->query('//h1')->length!==1){throw new RuntimeException('M83 footer destination H1 differs.');}
    $destination+=['http_status'=>$status,'h1'=>trim(preg_replace('/\s+/u',' ',$xp->query('//h1')->item(0)->textContent)),
        'html_sha256'=>hash('sha256',$html),'canonical'=>$xp->query('//link[@rel="canonical"]/@href')->item(0)?->nodeValue];
    echo json_encode(['destination'=>$destination['url'],'status'=>$status],JSON_UNESCAPED_SLASHES)."\n";
}unset($destination);
$targets=[];$ownerNavigations=[];
foreach($before['definitions'] as $owner=>$entry){
    $navs=array_keys(array_filter($entry['definition']['page']['blocks'],static fn($b)=>$b['type']==='navigation-chips'));
    if(count($navs)!==1){throw new RuntimeException('M83 exact source footer is ambiguous.');}$slot=$navs[0];
    $data=json_decode($entry['definition']['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);
    if(count($data['items']??[])!==6){throw new RuntimeException('M83 source footer must retain six labels.');}
    $expectedLabels=array_column($owner===array_key_first($before['definitions'])?$mixed:$basic,0);
    if(array_column($data['items'],'label')!==$expectedLabels){throw new RuntimeException('M83 footer label order differs.');}
    foreach($data['items'] as $index=>&$item){
        if(!is_bool($item['current']??null)||array_key_exists('url',$item)){throw new RuntimeException('M83 source footer expected label-only native data.');}
        if($item['current']){continue;}$destination=$destinations[$item['label']];$item['url']=$destination['url'];
        $targets[]=['owner'=>$owner,'source_index'=>$slot,'item_index'=>$index,'current'=>false]+$destination;
    }unset($item);$ownerNavigations[$owner]=['source_index'=>$slot,'data'=>$data];
}
if(count($targets)!==30||$readonly($fingerprints)!==$sqlBefore){throw new RuntimeException('M83 footer additions/count or read-only database preservation differs.');}
$result=['schema_version'=>1,'purpose'=>'M83 thirty explicit footer data URLs; exact six labels/order/current and unchanged native resolver.',
    'independent_before_sha256'=>$beforeSha,'source_sha256'=>$sourceHashes,'targets'=>$targets,
    'owner_navigations'=>$ownerNavigations,'destinations'=>array_values($destinations),
    'sql_readonly'=>true,'tables_unchanged'=>true,'db_writes'=>0,'source_writes'=>0];
$out=$dir.'/'.$argv[1];if(is_link($out)||file_exists($out)){throw new RuntimeException('M83 exclusive navigation receipt exists.');}
$bytes=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($out,'x');if(!$h){throw new RuntimeException('M83 navigation receipt unavailable.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M83 navigation receipt incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>$argv[1],'sha256'=>hash('sha256',$bytes),'destinations'=>count($destinations),'additions'=>count($targets),'writes'=>0])."\n";
