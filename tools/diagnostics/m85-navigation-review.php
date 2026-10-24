<?php

/** Six exact M85 destinations, 36 data-only footer fixes, C related category retained. */
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==2
 ||preg_match('/^navigation-review-v[1-9][0-9]*\.json$/D',$argv[1])!==1){throw new RuntimeException('M85 needs one exclusive navigation receipt.');}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m85-local';
$beforeSha='fe639c06446c6fb46b5b4ab25ae654e3910b6733cc124a9e4bee262e41b411b7';
$normal=static fn($p)=>rtrim(strtolower(str_replace('\\','/',(string)realpath($p))),'/');
if($normal(dirname(__DIR__,2))!==$normal($root)){throw new RuntimeException('M85 navigation must run from served ROOT.');}
$file=$dir.'/before-v1.json';$out=$dir.'/'.$argv[1];
if(is_link($file)||!hash_equals($beforeSha,hash_file('sha256',$file))||is_link($out)||file_exists($out)){throw new RuntimeException('M85 BEFORE/output pin differs.');}
$before=json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db=Illuminate\Support\Facades\DB::connection();App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$guard=new App\Services\M49LocalTargetGuard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file');
if($server['db']!=='gr2'||(int)$server['port']!==3306||!is_file($server['pid_file'])){throw new RuntimeException('M85 local DB differs.');}
$pid=trim(file_get_contents($server['pid_file']));if(!ctype_digit($pid)){throw new RuntimeException('M85 local DB PID missing.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
if(App\Services\M49LocalTargetGuard::publicIdentity($db,$root.'/public')!==$before['identity']){throw new RuntimeException('M85 runtime identity drift.');}
foreach($before['physical_hashes'] as $p=>$sha){if(is_link($p)||!is_file($p)||!hash_equals($sha,hash_file('sha256',$p))){throw new RuntimeException('M85 physical source drift.');}}
$readonly=static function(Closure $callback)use($db){$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();try{return $callback();}finally{$db->rollBack();}};
$fingerprints=static function()use($db):array{
 $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);$out=[];
 foreach($tables as $t){$columns=$db->getSchemaBuilder()->getColumnListing($t);$q=$db->table($t);foreach(in_array('id',$columns,true)?['id']:$columns as $c){$q->orderBy($c);}
  $h=hash_init('sha256');$n=0;foreach($q->cursor() as $r){hash_update($h,App\Services\M26ContentPatch::digest((array)$r)."\n");++$n;}$out[$t]=['count'=>$n,'sha256'=>hash_final($h)];}return $out;
};
$sqlBefore=$readonly($fingerprints);if(count($sqlBefore)!==46||$sqlBefore!==$before['sql_before']){throw new RuntimeException('M85 navigation SQL BEFORE drift.');}
$suffixes=['VerbToBePresentTheorySeeder','VerbToBeNegativesTheorySeeder','VerbToBeQuestionsTheorySeeder','VerbToBePastTheorySeeder','VerbToBeFutureTheorySeeder','ThereIsThereAreTheorySeeder'];
$slugs=['verb-to-be-present','verb-to-be-negatives','verb-to-be-questions','verb-to-be-past','verb-to-be-future','there-is-there-are'];
$labels=['Present Forms','Negatives','Questions and Short Answers','Past Forms','Future','There Is / There Are'];
$slots=[6,6,6,7,7,6];$ids=[28,26,29,27,25,24];
$owners=array_map(static fn($s)=>'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\'.$s,$suffixes);
if(array_keys($before['definitions'])!==$owners){throw new RuntimeException('M85 six-owner navigation scope drift.');}
$sourceHashes=[];$paired=[];
$readPaired=static function(string $owner,string $path,?array $captured)use($root,$wt,&$sourceHashes,&$paired):array{
 $raw=[];foreach(['served_root'=>$root,'worktree'=>$wt] as $context=>$base){$p=$base.'/'.$path;if(is_link($p)||!is_file($p)){throw new RuntimeException('M85 paired source missing.');}$raw[$context]=file_get_contents($p);$paired[$path][$context]=['raw_sha256'=>hash('sha256',$raw[$context]),'lf_sha256'=>hash('sha256',str_replace("\r\n","\n",$raw[$context])),'bytes'=>strlen($raw[$context])];}
 if(str_replace("\r\n","\n",$raw['served_root'])!==str_replace("\r\n","\n",$raw['worktree'])){throw new RuntimeException('M85 paired navigation LF differs.');}
 if($captured!==null&&($captured['path']!==$path||$captured['sha256']!==hash('sha256',$raw['served_root'])||$captured['bytes']!==$raw['served_root'])){throw new RuntimeException('M85 navigation source differs from pinned BEFORE.');}
 $data=json_decode($raw['served_root'],true,flags:JSON_THROW_ON_ERROR);if(($data['seeder']['class']??null)!==$owner||($data['page']['locale']??null)!=='uk'){throw new RuntimeException('M85 source identity/locale differs.');}$sourceHashes[$path]=hash('sha256',$raw['served_root']);return $data;
};
$resolver=new class extends App\Http\Controllers\PageController{public function __construct(){}public function path(App\Models\PageCategory $c):string{return $this->categorySlugPath($c);}};
$destinations=[];$neighbors=[];$related=[];
$readonly(static function()use($owners,$slugs,$labels,$ids,$before,$resolver,$readPaired,$root,&$destinations,&$neighbors,&$related):void{
 foreach($owners as $i=>$owner){$source=$readPaired($owner,$before['definitions'][$owner]['path'],$before['definitions'][$owner]);
  $page=App\Models\Page::query()->with('category')->where('seeder',$owner)->sole();$path=$resolver->path($page->category);
  if($page->id!==$ids[$i]||$page->id!==$before['pages'][$owner]['page']['id']||$page->type!=='theory'||$page->slug!==$slugs[$i]||$source['slug']!==$slugs[$i]||(int)$page->page_category_id!==8||$path!=='basic-grammar/verb-to-be'){throw new RuntimeException('M85 destination SQL/ancestry drift.');}
  $route=app('router')->getRoutes()->getByName('theory.show');$url='/'.str_replace(['{categoryPath}','{pageSlug}'],[$path,$page->slug],$route->uri());
  if(!in_array('GET',$route->methods(),true)||$url!=='/theory/basic-grammar/verb-to-be/'.$slugs[$i]||$before['pages'][$owner]['target_route']['url']!=='http://gramlyze.loc'.$url||app('router')->getRoutes()->match(Illuminate\Http\Request::create('http://gramlyze.loc'.$url,'GET'))->getName()!=='theory.show'){throw new RuntimeException('M85 destination named resolver differs.');}
  $http=array_values(array_filter($before['http'],static fn($r)=>$r['identity']===$owner&&$r['kind']==='theory'));if(count($http)!==1||$http[0]['status']!==200){throw new RuntimeException('M85 destination HTTP baseline absent.');}
  $destinations[$labels[$i]]=['label'=>$labels[$i],'url'=>$url,'target_page_id'=>$page->id,'target_owner'=>$owner,'expected_h1'=>$http[0]['h1'],'expected_canonical'=>$http[0]['canonical']];
 }
 // All accepted M84 footer bodies remain source/SQL exact. Have Got has no footer.
 $m84=App\Support\M84WordOrderHaveGot::class;$p=$root.'/'.$m84::NAVIGATION;if(is_link($p)||!hash_equals($m84::NAVIGATION_SHA,hash_file('sha256',$p))){throw new RuntimeException('M85 accepted M84 navigation pin differs.');}
 $nav=json_decode(file_get_contents($p),true,flags:JSON_THROW_ON_ERROR);if(count($nav['targets'])!==6){throw new RuntimeException('M85 accepted M84 scope differs.');}
 foreach($nav['targets'] as $i=>$target){$owner=$m84::OWNERS[$i];$sourcePath='database/seeders/'.str_replace('\\','/',substr($owner,strlen('Database\\Seeders\\'))).'/definition.json';$s=$readPaired($owner,$sourcePath,null);$page=App\Models\Page::query()->where('seeder',$owner)->sole();$slot=$target['source_index'];
  if($slot===null){if(array_filter($s['page']['blocks'],static fn($b)=>$b['type']==='navigation-chips')!==[]){throw new RuntimeException('M85 Have Got footer unexpectedly added.');}$neighbors[]=['identity'=>$owner,'page_id'=>$page->id,'source_index'=>null,'absent_footer_preserved'=>true];continue;}
  $data=json_decode($s['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);$sql=App\Models\TextBlock::query()->where('page_id',$page->id)->where('seeder',$owner)->where('locale','uk')->where('type','navigation-chips')->sole();
  if($page->id!==$target['page_id']||($data['items']??null)!==$target['items']||json_decode($sql->body,true,flags:JSON_THROW_ON_ERROR)!==$data){throw new RuntimeException('M85 M84 footer source/SQL/contract differs.');}
  $neighbors[]=['identity'=>$owner,'page_id'=>$page->id,'source_index'=>$slot,'navigation_uuid'=>$sql->uuid,'body_sha256'=>hash('sha256',$sql->body),'items'=>$data['items'],'source_sql_exact'=>true];
 }
 $c=$before['definitions'][$owners[2]]['definition']['page']['blocks'][7];$data=json_decode($c['body'],true,flags:JSON_THROW_ON_ERROR);
 if($c['type']!=='navigation-chips'||$data!==['title'=>'Пов’язана тема','items'=>[['label'=>'Питальні речення та заперечення','url'=>'/theory/pytalni-rechennia-ta-zaperechennia','current'=>false]]]){throw new RuntimeException('M85 C second navigation drift.');}
 $category=App\Models\PageCategory::query()->where('slug','pytalni-rechennia-ta-zaperechennia')->sole();$route=app('router')->getRoutes()->match(Illuminate\Http\Request::create('http://gramlyze.loc'.$data['items'][0]['url'],'GET'));
 if($route->getName()!=='theory.category'){throw new RuntimeException('M85 C related category resolves elsewhere.');}
 $related=['owner'=>$owners[2],'source_index'=>7,'data'=>$data,'url'=>$data['items'][0]['url'],'target_category_id'=>$category->id,'target_category_name'=>$category->title??$category->name,'route_name'=>$route->getName(),'action'=>$route->getActionName(),'source_body_sha256'=>hash('sha256',$c['body']),'preserved_exact'=>true];
});
$get=static function(string $path):array{
 if(!preg_match('~^/theory/[a-z0-9/-]+$~D',$path)){throw new RuntimeException('M85 GET outside finite theory paths.');}$url='http://gramlyze.loc'.$path;$location='';$ch=curl_init($url);
 curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_MAXREDIRS=>0,CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>120,CURLOPT_PROXY=>'',CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1'],CURLOPT_HTTPHEADER=>['Accept: text/html'],CURLOPT_HEADERFUNCTION=>static function($h,$header)use(&$location):int{if(stripos($header,'Location:')===0){$location=trim(substr($header,9));}return strlen($header);}]);
 try{$html=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);if(!is_string($html)||curl_getinfo($ch,CURLINFO_EFFECTIVE_URL)!==$url||curl_getinfo($ch,CURLINFO_PRIMARY_IP)!=='127.0.0.1'||curl_getinfo($ch,CURLINFO_REDIRECT_COUNT)!==0){throw new RuntimeException('M85 loopback GET failed.');}}finally{unset($ch);}
 $dom=new DOMDocument('1.0','UTF-8');$prior=libxml_use_internal_errors(true);try{$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($prior);}$xp=new DOMXPath($dom);$h=$xp->query('//h1');$c=$xp->query('//link[@rel="canonical"]/@href');
 return ['url'=>$path,'actual_url'=>$url,'http_status'=>$status,'location'=>$location,'html_sha256'=>hash('sha256',$html),'h1_count'=>$h->length,'h1'=>$h->length===1?trim(preg_replace('/\s+/u',' ',$h->item(0)->textContent)):null,'canonical_count'=>$c->length,'canonical'=>$c->length===1?$c->item(0)->nodeValue:null,'primary_ip'=>'127.0.0.1','redirect_count'=>0];
};
foreach($destinations as &$d){$http=$get($d['url']);if($http['http_status']!==200||$http['h1_count']!==1||$http['canonical_count']!==1||$http['h1']!==$d['expected_h1']||$http['canonical']!==$d['expected_canonical']){throw new RuntimeException('M85 full destination H1/canonical/status differs.');}$d+=$http;echo json_encode(['destination'=>$d['url'],'status'=>$d['http_status']],JSON_UNESCAPED_SLASHES)."\n";}unset($d);
$shorts=[];foreach($slugs as $s){$shorts[$s]=$get('/theory/verb-to-be/'.$s);echo json_encode(['original_short'=>$shorts[$s]['url'],'status'=>$shorts[$s]['http_status'],'location'=>$shorts[$s]['location']],JSON_UNESCAPED_SLASHES)."\n";}
$relatedHttp=$get($related['url']);if($relatedHttp['http_status']!==200||$relatedHttp['h1_count']!==1||$relatedHttp['h1']!==$related['target_category_name']||$relatedHttp['canonical_count']!==1||$relatedHttp['canonical']!=='https://gramlyze.com'.$related['url']){throw new RuntimeException('M85 C related category GET failed or existing category metadata changed.');}$related['http']=$relatedHttp;$related['canonical_scope']='Existing category canonical is a production URL string read from local HTML; no production GET and no canonical/source/router change.';
$targets=[];$ownerNav=[];
foreach($owners as $i=>$owner){$blocks=$before['definitions'][$owner]['definition']['page']['blocks'];$navSlots=array_keys(array_filter($blocks,static fn($b)=>$b['type']==='navigation-chips'));if($navSlots!==($i===2?[6,7]:[$slots[$i]])){throw new RuntimeException('M85 original navigation slots differ.');}
 $slot=$slots[$i];$data=json_decode($blocks[$slot]['body'],true,flags:JSON_THROW_ON_ERROR);if($data['title']!=='Уроки Verb to Be'||array_column($data['items'],'label')!==$labels){throw new RuntimeException('M85 footer labels/order/title differ.');}
 foreach($data['items'] as $j=>&$item){if(($item['current']??null)!==($i===$j)||$item['url']!=='/theory/verb-to-be/'.$slugs[$j]){throw new RuntimeException('M85 footer current/old URL differs.');}$old=$item['url'];$d=$destinations[$labels[$j]];$item['url']=$d['url'];$targets[]=['owner'=>$owner,'source_index'=>$slot,'item_index'=>$j,'current'=>$item['current'],'old_url'=>$old,'original_short_http'=>$shorts[$slugs[$j]],'change_reason'=>'Store full category ancestry in data; keep original label/order/current and named resolver unchanged.']+$d;}unset($item);
 $ownerNav[$owner]=['source_index'=>$slot,'data'=>$data];
}
$sqlAfter=$readonly($fingerprints);if(count($targets)!==36||$sqlAfter!==$sqlBefore){throw new RuntimeException('M85 navigation count/all46 preservation differs.');}
foreach($paired as $p=>$contexts){foreach(['served_root'=>$root,'worktree'=>$wt] as $context=>$base){if(!hash_equals($contexts[$context]['raw_sha256'],hash_file('sha256',$base.'/'.$p))){throw new RuntimeException('M85 paired source changed during GET proof.');}}}
$record=['schema_version'=>1,'purpose'=>'M85 explicit full-ancestry URL corrections only; 36 source footer items; C second category navigation exact; all M84 neighbors read-only.',
 'independent_before_sha256'=>$beforeSha,'source_sha256'=>$sourceHashes,'paired_source_hashes'=>$paired,'targets'=>$targets,'owner_navigations'=>$ownerNav,'destinations'=>array_values($destinations),'original_short_urls'=>array_values($shorts),'preserved_additional_navigation'=>[$related],'m84_neighbors'=>$neighbors,'sql_before'=>$sqlBefore,'sql_after'=>$sqlAfter,'sql_table_count'=>46,'tables_unchanged'=>true,'sql_readonly'=>true,'db_writes'=>0,'source_writes'=>0,'browser_visibility'=>'not_checked','interaction'=>'not_checked'];
$json=json_encode($record,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";$h=fopen($out,'x');if(!$h){throw new RuntimeException('M85 receipt collision.');}try{if(fwrite($h,$json)!==strlen($json)||!fflush($h)){throw new RuntimeException('M85 incomplete receipt.');}}finally{fclose($h);}
echo json_encode(['file'=>$argv[1],'sha256'=>hash('sha256',$json),'targets'=>count($targets),'destinations'=>count($destinations),'related_category_preserved'=>true,'m84_neighbors'=>count($neighbors),'sql_tables'=>46,'writes'=>0])."\n";
