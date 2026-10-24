<?php

/** Explicit two-body local correction. No seeder, restore, route installation or startup hook. */
$fail=static function(Throwable $e):void{
    fwrite(STDERR,'M85 note repair: '.($e instanceof RuntimeException&&!$e instanceof PDOException?$e->getMessage():'Incomplete; no successful receipt.')."\n");exit(1);
};
set_exception_handler($fail);$options=[];
foreach(array_slice($argv,1) as $arg){
    if(preg_match('/^--(mode|plan|backup|reviewed-digest|database|local-target|local-proof)=(.+)$/D',$arg,$m)!==1||isset($options[$m[1]])){
        throw new RuntimeException('Unique explicit allowlisted --name=value arguments required.');
    }$options[$m[1]]=$m[2];
}
$mode=$options['mode']??'';
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array($mode,['preview','apply'],true)
    ||($options['database']??null)!=='gr2'||($options['local-target']??null)!=='gramlyze.loc'
    ||preg_match('/^local-proof-[a-f0-9]{32}\.json$/D',$options['local-proof']??'')!==1
    ||preg_match('/^notes-preview-v[1-9][0-9]*\.json$/D',$options['plan']??'')!==1
    ||($mode==='preview'&&(isset($options['backup'])||isset($options['reviewed-digest'])))
    ||($mode==='apply'&&(preg_match('/^notes-backup-v[1-9][0-9]*\.json$/D',$options['backup']??'')!==1
        ||preg_match('/^[a-f0-9]{64}$/D',$options['reviewed-digest']??'')!==1))){
    throw new RuntimeException('Explicit local preview/apply, fresh proof, private plan/backup and reviewed digest required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m85-local';
$normal=static fn($p)=>rtrim(strtolower(str_replace('\\','/',(string)realpath($p))),'/');
if($normal(dirname(__DIR__,2))!==$normal($root)||is_link($dir)||!is_dir($dir)){
    throw new RuntimeException('Actual served ROOT/private directory required.');
}
$planPath=$dir.'/'.$options['plan'];
if($mode==='apply'){
    if(!is_file($planPath)||is_link($planPath)){throw new RuntimeException('Reviewed private plan unavailable.');}
    $plan=json_decode(file_get_contents($planPath),true,flags:JSON_THROW_ON_ERROR);
    if(($plan['sha256']??null)!==$options['reviewed-digest']){throw new RuntimeException('Reviewed digest differs.');}
}
require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';
try{$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();}catch(Throwable $e){$fail($e);}
set_exception_handler($fail);
if($normal(base_path())!==$normal($root)||$normal(app()->environmentPath())!==$normal($root)){
    throw new RuntimeException('Application/environment ROOT differs.');
}
$db=Illuminate\Support\Facades\DB::connection();App\Services\M85LocalTargetGuard::assertConfiguredDatabase($db);
$service=new App\Services\M85MistakeNotesPatch($db,$root.'/database',$dir,$options['local-target'],$options['local-proof']);
if($mode==='preview'){
    $plan=$service->savePlan($planPath);
    $result=['status'=>'preview','state'=>$plan['state'],'updated'=>count($plan['updates']),'inserted'=>count($plan['inserts']),
        'owners'=>count($plan['pages']),'protected_tables'=>count($plan['protected']),'source_files'=>count($plan['sources']),
        'plan'=>$options['plan'],'sha256'=>$plan['sha256']];
}else{$result=$service->apply($planPath,$dir.'/'.$options['backup'],$options['database']);if(isset($result['backup'])){$result['backup']=basename($result['backup']);}}
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
