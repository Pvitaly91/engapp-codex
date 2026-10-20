<?php

/**
 * Independent read-only M71 SQL/source gate; no apply, restore, seeder or HTTP.
 * php tools/diagnostics/m71-postconditions.php preview-v1.json <reviewed-digest> final-readonly-v1.json
 * Reconstructs BEFORE by reverting only the exact reviewed Page.text and existing UK body fields.
 * M71 has no appended rows, metadata transitions or question-family writes.
 */
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, $error instanceof RuntimeException && !$error instanceof PDOException
        ? 'M71 final audit: '.$error->getMessage()."\n"
        : "M71 final audit incomplete; no successful receipt. No SQL writes.\n");
    exit(1);
});
$previewName=$argv[1]??'';$reviewedDigest=$argv[2]??'';$receiptName=$argv[3]??'';
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==4
    ||preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$previewName)!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$reviewedDigest)!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$receiptName)!==1){
    throw new RuntimeException('Explicit preview basename, reviewed digest and new final receipt basename required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m71-local';
$normal=static fn($path):string=>rtrim(strtolower(str_replace('\\','/',(string)realpath($path))),'/');
if($normal(dirname(__DIR__,2))!==$normal($root)||is_link($dir)||!is_dir($dir)
    ||$normal($dir)!==$normal($root).'/storage/app/seo-m71-local'){
    throw new RuntimeException('Final audit must run from actual served ROOT/private directory.');
}
$receiptPath=$dir.'/'.$receiptName;
if(file_exists($receiptPath)||is_link($receiptPath)){throw new RuntimeException('Final receipt already exists; no overwrite.');}
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if($normal(base_path())!==$normal($root)||$normal(app()->environmentPath())!==$normal($root)){
    throw new RuntimeException('Final application/environment root differs.');
}
use App\Services\M71ContentPatch as Patch;
use App\Services\M71LocalTargetGuard as Guard;
use App\Support\M71ArticlesQuantifiers as Package;

$hash=static fn(string $path):string=>Patch::fileHash($path);
$load=static function(string $name,?string $expected=null)use($dir,$hash):array{
    if(preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$name)!==1
        ||($expected!==null&&!hash_equals($expected,$hash($dir.'/'.$name)))){
        throw new RuntimeException('Private reviewed evidence identity/bytes differ.');
    }
    $hash($dir.'/'.$name);
    return json_decode(file_get_contents($dir.'/'.$name),true,flags:JSON_THROW_ON_ERROR);
};
$digest=static fn(array $value):string=>App\Services\M26ContentPatch::digest($value);
$same=static function(array $actual,array $expected,string $message):void{
    ksort($actual);ksort($expected);if($actual!==$expected){throw new RuntimeException($message);}
};
$before=$load(Patch::BEFORE_FILE,Patch::BEFORE_SHA);$preview=$load($previewName);$plan=$preview;unset($plan['sha256']);
if(($preview['sha256']??null)!==$reviewedDigest||$digest($plan)!==$reviewedDigest
    ||($preview['state']??null)!=='before'||($preview['names']??null)!==Package::OWNERS
    ||array_keys($before['pages']??[])!==Package::OWNERS||($before['base_sha']??null)!==Package::BASE_SHA
    ||($before['sql_before']??null)!==($before['sql_after_http']??null)
    ||($preview['inserts']??null)!==[]){
    throw new RuntimeException('Reviewed preview/base or exact zero-insert BEFORE scope differs.');
}
[$sourceBefore,$patch,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$patch,$master,$contract);
foreach($preview['sources'] as $path=>$sha){
    $file=str_starts_with($path,'private:')?$dir.'/'.substr($path,strlen('private:')):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('Reviewed preview source/evidence changed: '.$path);}
}
foreach($before['physical_hashes'] as $path=>$sha){
    if(!hash_equals($sha,$hash($path))){throw new RuntimeException('Physical target/configuration changed.');}
}
foreach([$before['visual_hashes'],$before['frozen_hashes']] as $hashes){
    foreach($hashes as $path=>$sha){
        if(!hash_equals($sha,$hash($root.'/'.$path))){throw new RuntimeException('Visual/historical frozen source changed: '.$path);}
    }
}
$sync=$load(Patch::SYNC_RECEIPT);$syncPaths=array_column($sync['files']??[],'path');sort($syncPaths);
if($syncPaths!==Patch::syncPaths($patch,$contract)||($sync['base_sha']??null)!==Package::BASE_SHA
    ||($sync['served_root']??null)!==$root||($sync['source_worktree']??null)!==Patch::SOURCE_WORKTREE){
    throw new RuntimeException('Reviewed finite source-sync scope differs.');
}
foreach($sync['files'] as $record){
    if($hash($root.'/'.$record['path'])!==$record['root_after_sha256']
        ||$hash(Patch::SOURCE_WORKTREE.'/'.$record['path'])!==$record['worktree_sha256']){
        throw new RuntimeException('Reviewed ROOT/worktree source-sync bytes changed.');
    }
}

$db=Illuminate\Support\Facades\DB::connection();Guard::assertConfiguredDatabase($db);
$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try{
    $runtime=Guard::publicIdentity($db,$root.'/public');
    if($runtime!==($before['identity']??null)){throw new RuntimeException('Final local runtime identity differs from independent BEFORE.');}
    $pageBefore=[];$pageAfter=[];$ukBefore=[];$ukAfter=[];$nonUk=0;$expectedChanges=[];$practiceFacts=[];
    foreach($patch['targets'] as $i=>$target){
        $owner=$target['identity'];$captured=$before['pages'][$owner];$oldPage=$captured['page'];
        $pageBefore[$oldPage['id']]=$oldPage;
        $pageAfter[$oldPage['id']]=array_replace($oldPage,['text'=>$target['after']['page']['subtitle_text']]);
        if($oldPage['text']!==$pageAfter[$oldPage['id']]['text']){
            $expectedChanges['pages:'.$oldPage['id']]=['table'=>'pages','id'=>$oldPage['id'],'seeder'=>$owner,
                'before'=>['text'=>$oldPage['text']],'after'=>['text'=>$pageAfter[$oldPage['id']]['text']]];
        }
        foreach($captured['blocks'] as $old){
            if($old['locale']!=='uk'){++$nonUk;continue;}
            $position=(int)$old['sort_order'];
            $body=$position===0?$target['after']['page']['subtitle_html']:($target['after']['page']['blocks'][$position-1]['body']??null);
            if(!is_string($body)||isset($ukBefore[$old['id']])){throw new RuntimeException('Independent UK inventory/order is incomplete or duplicated.');}
            $ukBefore[$old['id']]=$old;$ukAfter[$old['id']]=array_replace($old,['body'=>$body]);
            if($old['body']!==$body){
                $expectedChanges['text_blocks:'.$old['id']]=['table'=>'text_blocks','id'=>$old['id'],'seeder'=>$owner,
                    'before'=>['body'=>$old['body']],'after'=>['body'=>$body]];
            }
            if($old['type']==='practice-set'){
                if($old['uuid']!==Package::PRACTICE_UUIDS[$i]||$position!==Package::PRACTICE_SOURCE_INDICES[$i]+1
                    ||$old['level']!==null||$old['column']!=='left'){
                    throw new RuntimeException('Independent continuing practice identity/NULL metadata differs.');
                }
                $practiceFacts[]=['owner'=>$owner,'id'=>$old['id'],'uuid'=>$old['uuid'],'source_index'=>$position-1,'level'=>null,'inserted'=>false];
            }
        }
    }
    $pageUpdates=count(array_filter($expectedChanges,static fn($c)=>$c['table']==='pages'));
    $blockUpdates=count(array_filter($expectedChanges,static fn($c)=>$c['table']==='text_blocks'));
    if(count($pageBefore)!==3||count($ukBefore)!==42||count($practiceFacts)!==3||$pageUpdates!==3||$blockUpdates!==42
        ||count($preview['updates']??[])!==count($expectedChanges)){
        throw new RuntimeException('Exact M71 three-owner/continuing-row update scope differs.');
    }
    $reviewedKeys=[];
    foreach($preview['updates'] as $change){
        $key=$change['table'].':'.$change['id'];$expected=$expectedChanges[$key]??null;
        if($expected===null||isset($reviewedKeys[$key])||$change['seeder']!==$expected['seeder']
            ||$change['before']!==$expected['before']||$change['after']!==$expected['after']
            ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){
            throw new RuntimeException('Reviewed preview contains an unapproved existing-row field or duplicate.');
        }
        $reviewedKeys[$key]=true;
    }
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);
    if($tables!==array_keys($before['sql_before'])){throw new RuntimeException('Actual SQL table inventory changed.');}
    $rawAfter=[];$normalized=[];$protected=[];$seenPages=[];$seenUk=[];
    foreach($tables as $table){
        $columns=$db->getSchemaBuilder()->getColumnListing($table);$query=$db->table($table);
        foreach(in_array('id',$columns,true)?['id']:$columns as $column){$query->orderBy($column);}
        $rawHash=hash_init('sha256');$oldHash=hash_init('sha256');$protectedHash=hash_init('sha256');
        $rawCount=0;$oldCount=0;$protectedCount=0;
        foreach($query->cursor() as $record){
            $row=(array)$record;hash_update($rawHash,$digest($row)."\n");++$rawCount;
            $id=$row['id']??null;$projected=$row;$skipProtected=false;
            if($table==='pages'&&isset($pageBefore[$id])){
                $same($row,$pageAfter[$id],'Target Page metadata/text/timestamps differ.');
                $projected=$pageBefore[$id];$seenPages[$id]=true;
            }
            if($table==='text_blocks'&&isset($ukBefore[$id])){
                $same($row,$ukAfter[$id],'Continuing UK body/owner/locale/metadata/timestamps differ.');
                $projected=$ukBefore[$id];$seenUk[$id]=true;$skipProtected=true;
            }
            // No rows are omitted: M71 authorizes neither inserts nor deletes in any family.
            hash_update($oldHash,$digest($projected)."\n");++$oldCount;
            if($skipProtected){continue;}
            if($table==='pages'&&isset($pageBefore[$id])){unset($row['text']);}
            hash_update($protectedHash,$digest($row)."\n");++$protectedCount;
        }
        $rawAfter[$table]=['count'=>$rawCount,'sha256'=>hash_final($rawHash)];
        $normalized[$table]=['count'=>$oldCount,'sha256'=>hash_final($oldHash)];
        $protected[$table]=['count'=>$protectedCount,'sha256'=>hash_final($protectedHash)];
    }
    if(count($seenPages)!==count($pageBefore)||count($seenUk)!==count($ukBefore)){
        throw new RuntimeException('A continuing target Page/block disappeared from the final scan.');
    }
    if($normalized!==$before['sql_before']){throw new RuntimeException('An old row/progress/non-target payload changed outside exact reviewed content fields.');}
    if($protected!==$preview['protected']){throw new RuntimeException('Reviewed preview protected fingerprints differ.');}
}finally{$db->rollBack();}
$receipt=['checked_at'=>gmdate('c'),'runtime'=>$runtime,'plan_file'=>$previewName,'plan_digest'=>$reviewedDigest,
    'independent_before_sha256'=>Patch::BEFORE_SHA,'page_text_updates'=>$pageUpdates,'existing_block_body_updates'=>$blockUpdates,
    'distinct_changed_rows'=>count($expectedChanges),'continuing_uk_rows'=>count($ukBefore),'unchanged_en_pl_rows'=>$nonUk,
    'continuing_practices'=>$practiceFacts,'metadata_transitions'=>0,'inserted_rows'=>0,'deleted_rows'=>0,
    'new_practices'=>[],'new_question_families'=>[],'all_old_rows_and_timestamps_exact'=>true,
    'independent_before_projection_exact'=>true,'reviewed_protected_projection_exact'=>true,
    'sql_tables'=>count($rawAfter),'sql_after'=>$rawAfter,'normalized_before'=>$normalized,'protected'=>$protected,
    'visual_files_unchanged'=>count($before['visual_hashes']),'frozen_files_unchanged'=>count($before['frozen_hashes']),
    'source_sync_unchanged'=>true,'db_writes'=>0,'http_requests'=>0,
    'http_metadata_and_browser_acceptance'=>'separate evidence; not performed by this SQL/source audit'];
$bytes=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$handle=fopen($receiptPath,'x');if($handle===false){throw new RuntimeException('Exclusive final receipt creation failed.');}
try{if(fwrite($handle,$bytes)!==strlen($bytes)||!fflush($handle)){throw new RuntimeException('Final receipt write incomplete.');}}
finally{fclose($handle);}
echo json_encode(['file'=>$receiptName,'sha256'=>hash('sha256',$bytes),'sql_tables'=>count($rawAfter),
    'distinct_changed_rows'=>count($expectedChanges),'continuing_uk_rows'=>count($ukBefore),'unchanged_en_pl_rows'=>$nonUk,
    'continuing_practices'=>count($practiceFacts),'inserted_rows'=>0,'deleted_rows'=>0,
    'all_old_rows_and_timestamps_exact'=>true,'db_writes'=>0],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
