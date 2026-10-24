<?php

/** Composer-only native metadata projection. No application bootstrap, .env, SQL or HTTP. */
$projectionSha='d77aeaa3b24ebc8a259f39fb86a1098bffda6bcdd6b503d35e9d1030c88280da'; // Exact main-reviewed author projection, before apply.
if(($argv[1]??null)!=='author-projection-v2.json'||preg_match('/^[a-f0-9]{64}$/D',$projectionSha)!==1||($argv[2]??null)!==$projectionSha){throw new RuntimeException('M86 exact reviewed author-projection-v2 pin is pending or differs.');}
if(PHP_SAPI!=='cli'||count($argv)!==4||preg_match('/^author-projection-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1||preg_match('/^prospective-metadata-v[1-9][0-9]*\.json$/D',$argv[3])!==1){
    throw new RuntimeException('M86 reviewed author input/hash and exclusive metadata output required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m86-local';
require $root.'/vendor/autoload.php';require_once $wt.'/app/Support/TheoryNativeContentRelease.php';require_once $root.'/app/Support/M86PresentSimpleContinuousBasics.php';
use App\Support\M86PresentSimpleContinuousBasics as Package;
$read=static function($file,$sha){if(is_link($file)||!is_file($file)||!hash_equals($sha,hash_file('sha256',$file))){throw new RuntimeException('M86 independent metadata input drift.');}return json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);};
$beforeSha='42f8a613fcb1721b2badcfabb9c67116f2c79fa9c14b7dc4df5e19dd71892208';
$before=$read($dir.'/before-v1.json',$beforeSha);$author=$read($dir.'/'.$argv[1],$argv[2]);
(new App\Support\TheoryNativeContentRelease(Package::draftProfile($author['native_bindings']['bindings'])))
    ->validate($author['source_before'],$author['patch'],$author['master'],$author['contract']);
Package::assertEditorial($author['source_before'],$author['patch'],$author['master'],$author['navigation_review']);
$pins=['app/Support/PageMetadata.php'=>'1d782d27483bdc0cff63515a1d69153dc908adbf2c3b3eea47bec35f141e0a9e',
    'app/Http/Middleware/AddStructuredData.php'=>'ea3774ba90512bfa2bf7cee9277b87a3951e4a43fbd48eeed21ffb83ec2a58ac'];
foreach($pins as $path=>$sha){if(!hash_equals($sha,hash_file('sha256',$root.'/'.$path))){throw new RuntimeException('M86 unchanged metadata policy drift.');}}
$plain=static fn($s)=>trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($s),ENT_QUOTES|ENT_HTML5,'UTF-8')));$rows=[];
foreach(Package::OWNERS as $i=>$owner){
    $lesson=$author['master']['lessons'][$i];$definition=$author['patch']['targets'][$i]['after'];
    $hero=json_decode($definition['page']['blocks'][0]['body'],true,flags:JSON_THROW_ON_ERROR);
    $seo=App\Support\PageMetadata::theory($definition['page']['title'],'',$hero['intro'],$owner,'uk');
    $http=array_values(array_filter($before['http'],static fn($h)=>$h['kind']==='theory'&&$h['identity']===$owner));
    if(count($http)!==1||$http[0]['url']!==$lesson['local_url']||$http[0]['h1']!==Package::RENDERED_TITLES[$i]
        ||$http[0]['raw_page_title']!==$definition['page']['title']||$http[0]['title']!==$seo['title']){throw new RuntimeException('M86 prospective protected title/H1 differs.');}
    $resource=null;
    foreach($definition['page']['blocks'] as $block){$data=json_decode($block['body'],true);if(!is_array($data)){continue;}
        foreach(['intro','description','summary','subtitle','text'] as $key){if(is_string($data[$key]??null)&&trim(strip_tags($data[$key]))!==''){$resource=Illuminate\Support\Str::limit($plain($data[$key]),300,'…');break 2;}}
    }
    if(!is_string($resource)||$resource===''){throw new RuntimeException('M86 prospective LearningResource description absent.');}
    $rows[]=['identity'=>$owner,'raw_title'=>$definition['page']['title'],'preserved_h1'=>$lesson['preserved_h1'],'local_url'=>$lesson['local_url'],
        'hero_intro_sha256'=>hash('sha256',$hero['intro']),
        'independent_before_protected'=>array_intersect_key($http[0],array_flip(['h1_count','h1','title','breadcrumb','canonical','robots_header','robots_meta'])),
        'prospective_page_metadata'=>$seo,'prospective_learning_resource_description'=>$resource];
}
foreach($pins as $path=>$sha){if(!hash_equals($sha,hash_file('sha256',$root.'/'.$path))){throw new RuntimeException('M86 metadata policy changed during projection.');}}
$result=['schema_version'=>1,'purpose'=>'M86 pre-apply prospective native metadata; not served-page acceptance',
    'projection_file'=>$argv[1],'projection_sha256'=>$argv[2],'independent_before_sha256'=>$beforeSha,'native_policy_pins'=>$pins,'metadata'=>$rows,
    'application_bootstrapped'=>false,'working_env_loaded'=>false,'db_connections'=>0,'db_queries'=>0,'db_writes'=>0,'http_requests'=>0];
$bytes=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$file=$dir.'/'.$argv[3];if(is_link($file)||file_exists($file)){throw new RuntimeException('M86 metadata output exists.');}
$h=fopen($file,'x');if(!$h){throw new RuntimeException('M86 metadata exclusive output unavailable.');}try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M86 metadata output incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>$argv[3],'sha256'=>hash('sha256',$bytes),'pages'=>6,'db_writes'=>0,'http_requests'=>0],JSON_THROW_ON_ERROR)."\n";
