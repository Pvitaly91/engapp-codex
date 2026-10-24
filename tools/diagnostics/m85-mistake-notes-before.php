<?php

/** Fresh SELECT-only current BEFORE; original initial evidence remains immutable. No HTTP. */
$fail=static function(Throwable $e):void{fwrite(STDERR,'M85 note BEFORE: '.($e instanceof RuntimeException&&!$e instanceof PDOException?$e->getMessage():'Incomplete; no successful receipt.')."\n");exit(1);};
set_exception_handler($fail);
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==1){throw new RuntimeException('Explicit M85 note BEFORE only.');}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m85-local';
$normal=static fn($p)=>strtolower(str_replace('\\','/',(string)realpath($p)));
if($normal(dirname(__DIR__,2))!==$normal($root)||is_link($dir)){throw new RuntimeException('Actual served ROOT/private directory required.');}
require $root.'/vendor/autoload.php';
use App\Services\M85MistakeNotesPatch as Patch;
$out=$dir.'/'.Patch::CURRENT_BEFORE;if(file_exists($out)||is_link($out)){throw new RuntimeException('Current BEFORE exists; no overwrite.');}
$baseline=Patch::readPinned($dir.'/'.Patch::BASELINE,Patch::BASELINE_SHA);
$schema=Patch::readPinned($dir.'/'.Patch::SCHEMA_BEFORE,Patch::SCHEMA_BEFORE_SHA);
[$manifest,$old]=Patch::revision($root);$sources=Patch::sourceState($root,$dir);
$app=require $root.'/bootstrap/app.php';try{$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();}catch(Throwable $e){$fail($e);}set_exception_handler($fail);
if($normal(base_path())!==$normal($root)||$normal(app()->environmentPath())!==$normal($root)){throw new RuntimeException('Application/environment ROOT differs.');}
$db=Illuminate\Support\Facades\DB::connection();App\Services\M85LocalTargetGuard::assertConfiguredDatabase($db);
if($db->transactionLevel()!==0){throw new RuntimeException('No ambient transaction permitted.');}
$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try{
    $runtime=App\Services\M85LocalTargetGuard::publicIdentity($db,$root.'/public');
    if($runtime!==$baseline['runtime']){throw new RuntimeException('Fresh runtime differs from original proven local AFTER.');}
    $service=new Patch($db,$root.'/database',$dir);$sql=$service->rawFingerprints();
    if(count($sql)!==46||$sql!==$baseline['sql_after']){throw new RuntimeException('Current all46 SQL differs from original initial AFTER.');}
    $protected=$service->readOnlyProtectedFingerprints();$owners=$service->readOnlyOwnerSnapshots();
    if(array_keys($protected)!==array_keys($sql)){throw new RuntimeException('Current protected table set differs.');}
    foreach($protected as $table=>$fingerprint){if($table!=='text_blocks'&&$fingerprint!==$sql[$table]){throw new RuntimeException('Protected non-text table changed.');}}
    if(count($schema['schema']??[])!==46){throw new RuntimeException('Full schema BEFORE missing.');}
    foreach($schema['schema'] as $table=>$columns){if($db->getSchemaBuilder()->getColumns($table)!==$columns){throw new RuntimeException('Captured schema changed.');}}
    $rows=[];
    foreach(Patch::TARGETS as $n=>$target){
        $row=(array)$db->table('text_blocks')->where('id',$target['id'])->sole();
        $expected=$manifest['targets'][$n]['row']+['page_id'=>App\Support\M85VerbToBe::PAGE_IDS[$target['owner_index']],
            'page_category_id'=>8,'seeder'=>App\Support\M85VerbToBe::OWNERS[$target['owner_index']],
            'body'=>$old['targets'][$target['owner_index']]['after']['page']['blocks'][$target['source_index']]['body']];
        foreach($expected as $key=>$value){if(!array_key_exists($key,$row)||$row[$key]!==$value){throw new RuntimeException('Current exact target row differs: '.$key);}}
        $rows[]=$row;
    }
}finally{$db->rollBack();}
if($sources!==Patch::sourceState($root,$dir)){throw new RuntimeException('Source state changed during current BEFORE scan.');}
$receipt=['phase'=>'current-before-note-repair','created_at'=>gmdate('c'),'runtime'=>$runtime,'baseline_file'=>Patch::BASELINE,
    'baseline_sha256'=>Patch::BASELINE_SHA,'sql_tables'=>46,'sql_after'=>$sql,'schema_before_sha256'=>Patch::SCHEMA_BEFORE_SHA,
    'target_rows'=>$rows,'owners'=>$owners,'protected'=>$protected,'sources'=>$sources,'db_writes'=>0,'http_requests'=>0];
$bytes=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($out,'x');if(!$h){throw new RuntimeException('Exclusive current BEFORE unavailable.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('Current BEFORE receipt incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>basename($out),'sha256'=>hash('sha256',$bytes),'sql_tables'=>46,'target_rows'=>2,'db_writes'=>0,'http_requests'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
