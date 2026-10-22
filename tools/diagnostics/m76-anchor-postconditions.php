<?php

/** Independent SELECT-only successor gate; reconstructs both accepted1.0.0 and pre-M76 all-table states. */
$failure=static function(Throwable $e):void{
    fwrite(STDERR,$e instanceof RuntimeException&&!$e instanceof PDOException?'M76 anchor final: '.$e->getMessage()."\n":"M76 anchor final incomplete; no receipt or SQL writes.\n");exit(1);
};
set_exception_handler($failure);
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==6
    ||preg_match('/^anchor-preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1
    ||preg_match('/^anchor-backup-v[1-9][0-9]*\.json$/D',$argv[3])!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1
    ||preg_match('/^anchor-final-readonly-v[1-9][0-9]*\.json$/D',$argv[5])!==1){
    throw new RuntimeException('M76 anchor needs exact reviewed preview/digest, backup/raw SHA and fresh final basename.');
}
[$script,$planName,$planDigest,$backupName,$backupSha,$receiptName]=$argv;
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m76-local';
$normal=static fn($p)=>strtolower(str_replace('\\','/',(string)realpath($p)));
if($normal(dirname(__DIR__,2))!==$normal($root)||!is_dir($dir)||is_link($dir)||file_exists($dir.'/'.$receiptName)||is_link($dir.'/'.$receiptName)){
    throw new RuntimeException('M76 anchor actual ROOT/private output scope differs.');
}
require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();set_exception_handler($failure);
use App\Services\M76AnchorPatch as Anchor;
use App\Services\M76ContentPatch as Original;
use App\Services\M76LocalTargetGuard as Guard;
use App\Support\M76PronounRelations as Package;
$digest=static fn(array $v)=>App\Services\M26ContentPatch::digest($v);$pins=[];
$hash=static fn(string $p):string=>Anchor::fileHash($p);
$read=static function(string $name,?string $sha=null)use($dir,$hash,&$pins):array{
    if(preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$name)!==1){throw new RuntimeException('M76 anchor exact private basename required.');}
    $p=$dir.'/'.$name;$actual=$hash($p);
    if($sha!==null&&!hash_equals($sha,$actual)){throw new RuntimeException('M76 anchor private evidence bytes drifted.');}
    $pins[$p]=$actual;return json_decode(file_get_contents($p),true,flags:JSON_THROW_ON_ERROR);
};
$before=$read(Original::BEFORE_FILE,Original::BEFORE_SHA);
$baseline=$read(Anchor::BASELINE_FILE,Anchor::BASELINE_SHA);
$oldPlan=$read($baseline['plan_file']);$oldBackup=$read($baseline['backup_file'],$baseline['backup_sha256']);
$oldUnsigned=$oldPlan;unset($oldUnsigned['sha256']);
if($oldPlan!==$oldBackup||file_get_contents($dir.'/'.$baseline['plan_file'])!==file_get_contents($dir.'/'.$baseline['backup_file'])
    ||$oldPlan['sha256']!==$baseline['plan_digest']||$digest($oldUnsigned)!==$baseline['plan_digest']
    ||count($oldPlan['updates'])!==42||$oldPlan['inserts']!==[]||count($baseline['sql_after'])!==46
    ||$baseline['independent_before_sha256']!==Original::BEFORE_SHA||$baseline['all_old_rows_and_timestamps_exact']!==true){
    throw new RuntimeException('M76 anchor exact accepted42-update predecessor differs.');
}
$plan=$read($planName);$backup=$read($backupName,$backupSha);$unsigned=$plan;unset($unsigned['sha256']);
if($plan!==$backup||file_get_contents($dir.'/'.$planName)!==file_get_contents($dir.'/'.$backupName)
    ||$plan['sha256']!==$planDigest||$digest($unsigned)!==$planDigest
    ||$plan['patch']!=='m76-anchor-revision-v1.0.1'||$plan['version']!==1||$plan['names']!==Anchor::NAMES
    ||$plan['state']!=='before'||count($plan['updates'])!==1||$plan['inserts']!==[]){
    throw new RuntimeException('M76 anchor one-update reviewed plan/backup differs.');
}
$publicRead=static function(string $path,string $sha)use($root,$hash,&$pins):array{
    $p=$root.'/'.$path;if(!hash_equals($sha,$hash($p))){throw new RuntimeException('M76 anchor frozen public source drift.');}
    $pins[$p]=$sha;return json_decode(file_get_contents($p),true,flags:JSON_THROW_ON_ERROR);
};
$old=$publicRead(Anchor::PREVIOUS_PATCH,Anchor::PREVIOUS_PATCH_SHA);
$oldMaster=$publicRead(Anchor::PREVIOUS_MASTER,Anchor::PREVIOUS_MASTER_SHA);
$revision=$publicRead(Anchor::REVISION,Anchor::REVISION_SHA);
[$sourceBefore,$next,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$next,$master,$contract);
$transition=Anchor::exactTransition($old,$next,$oldMaster,$master);
$change=$plan['updates'][0];
if($change['table']!=='text_blocks'||$change['id']!==4077||$change['seeder']!==Anchor::OWNER
    ||$change['uuid']!==$revision['transition']['uuid']||$change['before']!==['body'=>$transition['before']]
    ||$change['after']!==['body'=>$transition['after']]||$change['before_sha256']!==$digest($change['before'])
    ||$change['after_sha256']!==$digest($change['after'])||$plan['protected']!==$baseline['sql_after']){
    throw new RuntimeException('M76 anchor exact body allowlist differs.');
}
foreach($plan['sources'] as $path=>$sha){
    $file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M76 anchor preview source drift.');}$pins[$file]=$sha;
}
$expectedSources=[Anchor::PREVIOUS_PATCH,Anchor::PREVIOUS_MASTER,Anchor::REVISION,
    ...array_keys(Package::profile()['artifacts']),...Original::sourcePaths($next,$contract,$root),...Anchor::CODE_PATHS,
    'app/Services/M74BankCompletionPatch.php','app/Services/TheoryLocalReleaseGuard.php',
    'private:'.Anchor::BASELINE_FILE,'private:'.Original::BEFORE_FILE,'private:'.Anchor::SYNC_FILE];
$expectedSources=array_values(array_unique($expectedSources));sort($expectedSources);$actualSources=array_keys($plan['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M76 anchor complete preview source inventory differs.');}
foreach(Anchor::CODE_PATHS as $path){$file=Original::SOURCE_WORKTREE.'/'.$path;
    if(!hash_equals($plan['sources'][$path],$hash($file))){throw new RuntimeException('M76 anchor own successor ROOT/WT code differs.');}$pins[$file]=$plan['sources'][$path];
}
$sync=$read(Anchor::SYNC_FILE,Anchor::SYNC_SHA);$syncPaths=array_column($sync['files'],'path');sort($syncPaths);
if($syncPaths!==Anchor::syncPaths($next,$contract)||$sync['base_sha']!==Package::BASE_SHA
    ||$sync['served_root']!==$root||$sync['source_worktree']!==Original::SOURCE_WORKTREE
    ||$sync['visual_before']!==['file'=>Original::BEFORE_FILE,'sha256'=>Original::BEFORE_SHA]){
    throw new RuntimeException('M76 anchor successor source-sync scope differs.');
}
foreach($sync['files'] as $r){foreach([$root.'/'.$r['path']=>$r['root_after_sha256'],Original::SOURCE_WORKTREE.'/'.$r['path']=>$r['worktree_sha256']] as $file=>$sha){
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M76 anchor source-sync bytes drift.');}$pins[$file]=$sha;
}}
foreach($before['visual_hashes'] as $path=>$sha){if(!hash_equals($sha,$hash($root.'/'.$path))){throw new RuntimeException('M76 anchor visual bytes changed.');}$pins[$root.'/'.$path]=$sha;}
foreach($before['frozen_hashes'] as $path=>$sha){if(!hash_equals($sha,$hash($root.'/'.$path))){throw new RuntimeException('M76 anchor historical frozen bytes changed.');}$pins[$root.'/'.$path]=$sha;}
foreach($before['physical_hashes'] as $file=>$sha){if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M76 anchor physical config changed.');}$pins[$file]=$sha;}
$originalRows=$expectedRows=[];$ukIds=$pageIds=[];
foreach($oldPlan['pages'] as $owner=>$p){
    $key='pages:'.$p['page']['id'];$originalRows[$key]=$expectedRows[$key]=$p['page'];$pageIds[$p['page']['id']]=true;
    foreach($p['blocks'] as $row){$key='text_blocks:'.$row['id'];$originalRows[$key]=$expectedRows[$key]=$row;if($row['locale']==='uk'){$ukIds[$row['id']]=true;}}
}
foreach($oldPlan['updates'] as $r){$key=$r['table'].':'.$r['id'];$expectedRows[$key]=array_replace($expectedRows[$key],$r['after']);}
if(($expectedRows['text_blocks:4077']['body']??null)!==$transition['before']){throw new RuntimeException('M76 anchor predecessor fullrow mismatch.');}
$expectedRows['text_blocks:4077']['body']=$transition['after'];
$db=Illuminate\Support\Facades\DB::connection();Guard::assertConfiguredDatabase($db);
$runtime=Guard::publicIdentity($db,$root.'/public');if($runtime!==$baseline['runtime']||$runtime!==$before['identity']){throw new RuntimeException('M76 anchor actual runtime identity drift.');}
$physical=(new Guard)->physicalEvidence();if($digest($physical)!==$digest($before['physical'])){throw new RuntimeException('M76 anchor live physical vhost/listener evidence drift.');}
$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try{
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);
    if($tables!==array_keys($baseline['sql_after'])){throw new RuntimeException('M76 anchor table inventory differs.');}
    $after=$predecessor=$normalized=$protected=[];$seen=[];
    foreach($tables as $table){
        $schema=$db->getSchemaBuilder()->getColumns($table);
        if($schema!==$before['schema'][$table]){throw new RuntimeException('M76 anchor detailed schema drift.');}
        $cols=array_column($schema,'name');$q=$db->table($table);foreach(in_array('id',$cols,true)?['id']:$cols as $col){$q->orderBy($col);}
        $h=hash_init('sha256');$p=hash_init('sha256');$n=hash_init('sha256');$g=hash_init('sha256');$count=$gc=0;
        foreach($q->cursor() as $record){
            $row=(array)$record;$id=$row['id']??null;$key=$table.':'.$id;$old=$row;$prev=$row;
            if(isset($expectedRows[$key])){$a=$row;$b=$expectedRows[$key];ksort($a);ksort($b);if($a!==$b){throw new RuntimeException('M76 anchor target fullrow metadata/timestamps differ.');}$old=$originalRows[$key];$seen[$key]=true;}
            if($table==='text_blocks'&&$id===4077){$prev['body']=$transition['before'];}
            hash_update($h,$digest($row)."\n");hash_update($p,$digest($prev)."\n");hash_update($n,$digest($old)."\n");++$count;
            if($table==='text_blocks'&&isset($ukIds[$id])){continue;}
            if($table==='pages'&&isset($pageIds[$id])){unset($row['text']);}hash_update($g,$digest($row)."\n");++$gc;
        }
        $after[$table]=['count'=>$count,'sha256'=>hash_final($h)];$predecessor[$table]=['count'=>$count,'sha256'=>hash_final($p)];
        $normalized[$table]=['count'=>$count,'sha256'=>hash_final($n)];$protected[$table]=['count'=>$gc,'sha256'=>hash_final($g)];
    }
    if(count($seen)!==count($expectedRows)||$predecessor!==$baseline['sql_after']||$normalized!==$before['sql_before']||$protected!==$oldPlan['protected']){
        throw new RuntimeException('M76 anchor complete predecessor/original/protected reconstruction failed.');
    }
}finally{$db->rollBack();}
foreach($pins as $file=>$sha){if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M76 anchor evidence changed during read-only audit.');}}
$result=array_replace($baseline,[
    'checked_at'=>gmdate('c'),'runtime'=>$runtime,'plan_file'=>$planName,'plan_digest'=>$planDigest,
    'backup_file'=>$backupName,'backup_sha256'=>$backupSha,'original_final_file'=>Anchor::BASELINE_FILE,'original_final_sha256'=>Anchor::BASELINE_SHA,
    'initial_plan_file'=>$baseline['plan_file'],'initial_plan_digest'=>$baseline['plan_digest'],'initial_backup_file'=>$baseline['backup_file'],
    'initial_backup_sha256'=>$baseline['backup_sha256'],'initial_update_operations'=>42,'update_operations'=>1,'cumulative_update_operations'=>43,
    'distinct_changed_rows'=>42,'anchor_body_updates'=>1,'insert_operations'=>0,'deleted_rows'=>0,
    'revision_file'=>Anchor::REVISION,'revision_sha256'=>Anchor::REVISION_SHA,'successor_version'=>'1.0.1','successor_state'=>'after',
    'predecessor_sql_exact'=>true,'predecessor_sql'=>$predecessor,'sql_after'=>$after,'normalized_before'=>$normalized,'protected'=>$protected,
    'source_sync_file'=>Anchor::SYNC_FILE,'source_sync_sha256'=>Anchor::SYNC_SHA,'all_preview_sources_exact'=>true,
    'all_old_rows_and_timestamps_exact'=>true,'independent_before_projection_exact'=>true,'reviewed_protected_projection_exact'=>true,
    'db_writes'=>0,'http_requests'=>0,'browser_acceptance'=>'Separate real-page evidence; not claimed by this SQL gate.',
]);
$bytes=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($dir.'/'.$receiptName,'x');if(!$h){throw new RuntimeException('M76 anchor exclusive final output unavailable.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M76 anchor final receipt incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>$receiptName,'sha256'=>hash('sha256',$bytes),'update_operations'=>1,'sql_tables'=>count($after),
    'predecessor_sql_exact'=>true,'independent_before_projection_exact'=>true,'db_writes'=>0,'http_requests'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";

