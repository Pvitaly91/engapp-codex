<?php
/** Explicit post-apply M46 read-only DB/HTTP audit; never calls apply/seed/migrate. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 2 || !preg_match('/^after-v[1-9][0-9]*\.json$/D', $argv[1])) { exit(1); }
$source = dirname(__DIR__, 2); $working = 'D:/DEV/htdocs/gramlyze.loc';
require $working.'/vendor/autoload.php';
$app = require $working.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Support\M46ConditionalsPackage as Package;
use App\Services\M46LocalTargetGuard as Guard;
use Illuminate\Support\Facades\DB;
if (realpath(base_path()) !== realpath($working)) { throw new RuntimeException('M46 audit ROOT differs.'); }
$db = DB::connection(); Guard::assertConfiguredDatabase($db);
$dir = Guard::privateDirectory();
$before = json_decode(file_get_contents($dir.'/before-v3.json'), true, flags: JSON_THROW_ON_ERROR);
$backup = json_decode(file_get_contents($dir.'/backup-v1.json'), true, flags: JSON_THROW_ON_ERROR);
[$manifest, $package, $master] = Package::load($source); Package::validate($manifest, $package, $master);
$exclusive = static function ($path, $bytes): void {
    $f=fopen($path,'x');if(!$f){throw new RuntimeException('M46 audit evidence exists; no overwrite.');}
    try{if(fwrite($f,$bytes)!==strlen($bytes)||!fflush($f)){throw new RuntimeException('M46 audit incomplete.');}}finally{fclose($f);}
};
$records = []; $ids = [];
foreach ($package['targets'] as $i => $target) {
    $owner = (array) $db->table('pages')->where('seeder',$target['identity'])->first();
    $oldOwner = $before['pages'][$target['identity']]['page'];
    $expectedOwner = array_replace($oldOwner,['text'=>$target['after']['page']['subtitle_text']]);
    if ($owner !== $expectedOwner) { throw new RuntimeException('M46 owner postcondition differs.'); }
    $rows = $db->table('text_blocks')->where('page_id',$owner['id'])->orderBy('sort_order')->orderBy('id')->get()->map(static fn($r)=>(array)$r)->all();
    $uk = array_values(array_filter($rows,static fn($r)=>$r['locale']==='uk'));
    $configs = [['type'=>'subtitle','column'=>'header','heading'=>null,'level'=>null,'uuid_key'=>'subtitle','body'=>$target['after']['page']['subtitle_html']],...$target['after']['page']['blocks']];
    if (count($uk)!==count($configs)) { throw new RuntimeException('M46 postcondition block count differs.'); }
    foreach($configs as $pos=>$cfg){
        $row=$uk[$pos];$ids[]=$row['id'];
        if($row['uuid']!==App\Support\M26DetailPackage::uuid($target['identity'],$cfg,$pos)||$row['body']!==$cfg['body']
            ||$row['locale']!=='uk'||$row['page_id']!==$owner['id']||$row['page_category_id']!==$owner['page_category_id']
            ||$row['seeder']!==$target['identity']||(int)$row['sort_order']!==$pos){throw new RuntimeException('M46 postcondition native row differs.');}
        foreach(['type','column','heading','level','css_class'] as $field){if($row[$field]!==($cfg[$field]??null)){throw new RuntimeException('M46 block metadata differs.');}}
    }
    $old = $before['pages'][$target['identity']]['blocks'];
    foreach($old as $r){
        $actual=array_values(array_filter($rows,static fn($x)=>$x['id']===$r['id']))[0]??null;
        if($r['locale']==='uk'){unset($r['body'],$actual['body']);}
        if($actual!==$r){throw new RuntimeException('M46 old row IDs/UUID/locale/timestamp/metadata changed.');}
    }
    $records[]=['id'=>$owner['id'],'identity'=>$target['identity'],'slug'=>$target['slug'],'uk_blocks'=>count($uk),'all_rows'=>$rows];
}
$auditor = new class($db,$working.'/database',$dir) extends App\Services\M46ContentPatch {
    public function fingerprints(array $ids): array { return $this->protectedFingerprints($ids); }
};
$protected = $auditor->fingerprints($ids);
if($protected!==$backup['protected']){throw new RuntimeException('M46 non-target/bank/progress table fingerprint changed.');}
$visual=[];
foreach($before['visual_hashes'] as $path=>$sha){$visual[$path]=hash_file('sha256',$working.'/'.$path);}
if($visual!==$before['visual_hashes']){throw new RuntimeException('M46 visual source changed.');}
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
 foreach([$target['local_url'],'http://gramlyze.loc/courses/english-grammar-theory/lesson/conditionals/'.$target['slug']] as $url){
    $robotsHeader = '';
    $curl=curl_init($url);curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>30,
        CURLOPT_HEADERFUNCTION=>static function($handle,$header) use(&$robotsHeader) { if(stripos($header,'X-Robots-Tag:')===0){$robotsHeader=trim(substr($header,13));} return strlen($header); },
        CURLOPT_PROXY=>'',CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1']]);
    $html=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);$ip=curl_getinfo($curl,CURLINFO_PRIMARY_IP);
    if($html===false||$status!==200||$ip!=='127.0.0.1'){throw new RuntimeException('M46 local AFTER HTTP unavailable.');}
    $file=substr($argv[1],0,-5).'-http-'.hash('sha256',$url).'.html';$exclusive($dir.'/'.$file,$html);
    $audit=$htmlAudit($html); $audit['robots_header']=$robotsHeader;
    $prior=array_values(array_filter($before['http'],static fn($h)=>$h['url']===$url))[0];
    $old=$htmlAudit(file_get_contents($dir.'/'.$prior['file']));
    $course=str_contains($url,'/courses/');
    if($course){
        // Compare teaching text independently; volatile CSRF/session markup is not teaching content.
        foreach(['title','h1','content_text'] as $field){if(preg_replace('/\s+/u',' ',trim($audit[$field]))!==preg_replace('/\s+/u',' ',trim($old[$field]))){throw new RuntimeException('M46 course AFTER differs from live BEFORE: '.$field);}}
    }else{
        $expected=App\Support\PageMetadata::theory($target['after']['page']['title'],'Умовні речення',$master['lessons'][$i]['blocks'][0]['data']['intro'],$target['identity'],'uk');
        if($audit['title']!==$expected['title']||$audit['description']!==$expected['description']
            ||$audit['og_description']!==$expected['description']||$audit['twitter_description']!==$expected['description']
            ||$audit['h1']!==$old['h1']||$audit['canonical']!==$old['canonical']
            ||!str_contains($audit['robots'].' '.$audit['robots_header'],'noindex')){throw new RuntimeException('M46 page metadata/canonical/noindex postcondition differs.');}
    }
    $http[]=['url'=>$url,'status'=>$status,'file'=>$file,'sha256'=>hash('sha256',$html),'course_before_parity'=>$course,
        'before_metadata'=>array_intersect_key($old,array_flip(['title','description','og_description','twitter_description','canonical','robots','structured'])),
        'after'=>array_diff_key($audit,['content_text'=>true])];
 }
}
$out=['at'=>gmdate('c'),'runtime'=>Guard::publicIdentity($db,$working.'/public'),'pages'=>$records,'protected'=>$protected,'visual_hashes'=>$visual,'http'=>$http,
    'counts'=>['updated'=>count($backup['updates']),'inserted'=>count($backup['inserts']),'deleted'=>0],'bank_progress_changes'=>0,'visual_changes'=>0,'course_changes'=>0];
$bytes=json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";$exclusive($dir.'/'.$argv[1],$bytes);
echo json_encode(['status'=>'postconditions-pass','file'=>$argv[1],'sha256'=>hash_file('sha256',$dir.'/'.$argv[1]),'protected_tables'=>count($protected),
    'visual_files'=>count($visual),'course_changes'=>0,'http'=>array_map(static fn($h)=>['url'=>$h['url'],'status'=>$h['status'],'points'=>$h['after']['points'],
        'details'=>$h['after']['details'],'practice_models'=>$h['after']['practice_models'],'course_before_parity'=>$h['course_before_parity']],$http)],
    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
