<?php

/**
 * Independent SELECT-only two-overlay scan. Original initial receipts are never rewritten.
 * CLI: notes-preview-vN.json DIGEST notes-backup-vN.json RAW_SHA notes-final-readonly-vN.json cleanup-NONCE.json RAW_SHA [PRIOR_FINAL RAW_SHA]
 * No writer/plan/apply/restore invocation, HTTP, seeding or configuration edits.
 */
$fail=static function(Throwable $e):void{fwrite(STDERR,'M85 note AFTER: '.($e instanceof RuntimeException&&!$e instanceof PDOException?$e->getMessage():'Incomplete; no successful receipt.')."\n");exit(1);};
set_exception_handler($fail);
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array(count($argv),[8,10],true)
    ||preg_match('/^notes-preview-v[1-9][0-9]*\.json$/D',$argv[1]??'')!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[2]??'')!==1
    ||preg_match('/^notes-backup-v[1-9][0-9]*\.json$/D',$argv[3]??'')!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[4]??'')!==1
    ||preg_match('/^notes-final-readonly-v[1-9][0-9]*\.json$/D',$argv[5]??'')!==1
    ||preg_match('/^cleanup-[a-f0-9]{32}\.json$/D',$argv[6]??'')!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[7]??'')!==1
    ||(count($argv)===10&&(preg_match('/^notes-final-readonly-v[1-9][0-9]*\.json$/D',$argv[8])!==1
        ||preg_match('/^[a-f0-9]{64}$/D',$argv[9])!==1||$argv[5]===$argv[8]))){
    throw new RuntimeException('Explicit finite correction preview/digest/backup/cleanup/new output and optional prior evidence required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m85-local';
$normal=static fn($p)=>strtolower(str_replace('\\','/',(string)realpath($p)));
if($normal(dirname(__DIR__,2))!==$normal($root)||is_link($dir)){throw new RuntimeException('Actual served ROOT/private directory required.');}
$out=$dir.'/'.$argv[5];if(file_exists($out)||is_link($out)){throw new RuntimeException('New AFTER receipt exists; no overwrite.');}
require $root.'/vendor/autoload.php';
use App\Services\M85MistakeNotesPatch as Patch;
use App\Support\M85VerbToBe as Package;
$digest=App\Services\M26ContentPatch::digest(...);
$baseline=Patch::readPinned($dir.'/'.Patch::BASELINE,Patch::BASELINE_SHA);
$initial=Patch::readPinned($dir.'/'.App\Services\M85ContentPatch::BEFORE_FILE,App\Services\M85ContentPatch::BEFORE_SHA);
$fresh=Patch::readPinned($dir.'/'.Patch::CURRENT_BEFORE);
$schema=Patch::readPinned($dir.'/'.Patch::SCHEMA_BEFORE,Patch::SCHEMA_BEFORE_SHA);
$preview=Patch::readPinned($dir.'/'.$argv[1]);$copy=$preview;unset($copy['sha256']);
if(($preview['sha256']??null)!==$argv[2]||$digest($copy)!==$argv[2]
    ||($preview['patch']??null)!=='m85-mistake-notes-1.0.0-to-1.0.1'||($preview['version']??null)!==1
    ||($preview['state']??null)!=='before'||($preview['names']??null)!==Package::OWNERS
    ||array_keys($preview['pages']??[])!==Package::OWNERS||count($preview['updates']??[])!==2
    ||($preview['inserts']??null)!==[]||isset($preview['deletes'])||isset($preview['question_supplement'])
    ||count($preview['protected']??[])!==46||Patch::fileHash($dir.'/'.$argv[3])!==$argv[4]
    ||file_get_contents($dir.'/'.$argv[1])!==file_get_contents($dir.'/'.$argv[3])){
    throw new RuntimeException('Exact correction digest/scope/exclusive backup differs.');
}
foreach(['all_old_rows_and_timestamps_exact','independent_before_projection_exact','reviewed_protected_projection_exact','source_sync_unchanged'] as $field){
    if(($baseline[$field]??null)!==true){throw new RuntimeException('Pinned initial preservation evidence incomplete.');}
}
if(($baseline['sql_tables']??null)!==46||count($baseline['sql_after']??[])!==46
    ||($baseline['normalized_before']??null)!==$initial['sql_before']
    ||($fresh['sql_after']??null)!==$baseline['sql_after']||($fresh['baseline_sha256']??null)!==Patch::BASELINE_SHA
    ||($baseline['independent_before_sha256']??null)!==App\Services\M85ContentPatch::BEFORE_SHA
    ||($baseline['new_question_families']??null)!==[]||count($baseline['new_practices']??[])!==6
    ||($baseline['db_writes']??null)!==0||($baseline['http_requests']??null)!==0){
    throw new RuntimeException('Initial/current BEFORE lineage differs.');
}
$original=Patch::readPinned($dir.'/'.$baseline['plan_file']);$originalCopy=$original;unset($originalCopy['sha256']);
if(($original['sha256']??null)!==$baseline['plan_digest']||$digest($originalCopy)!==$baseline['plan_digest']
    ||Patch::fileHash($dir.'/'.$baseline['backup_file'])!==$baseline['backup_sha256']
    ||file_get_contents($dir.'/'.$baseline['backup_file'])!==file_get_contents($dir.'/'.$baseline['plan_file'])
    ||count($original['updates']??[])!==49||count($original['inserts']??[])!==6){
    throw new RuntimeException('Immutable initial preview/backup/digest differs.');
}
[$manifest,$old,$next]=Patch::revision($root);
$changes=[];$targets=[];
foreach(Patch::TARGETS as $n=>$target){
    $i=$target['owner_index'];$slot=$target['source_index'];$a=$old['targets'][$i]['after']['page']['blocks'][$slot]['body'];
    $b=$next['targets'][$i]['after']['page']['blocks'][$slot]['body'];
    $expected=['table'=>'text_blocks','id'=>$target['id'],'uuid'=>$target['uuid'],'seeder'=>Package::OWNERS[$i],
        'before'=>['body'=>$a],'after'=>['body'=>$b],'before_sha256'=>$digest(['body'=>$a]),'after_sha256'=>$digest(['body'=>$b])];
    if($preview['updates'][$n]!==$expected){throw new RuntimeException('Correction update is not the independently derived exact body cell.');}
    $captured=array_values(array_filter($fresh['target_rows']??[],static fn($r)=>$r['id']===$target['id']));
    if(count($captured)!==1||$captured[0]['body']!==$a){throw new RuntimeException('Fresh current BEFORE target row missing.');}
    $targets[$target['id']]=['before'=>$a,'after'=>$b,'row'=>$captured[0]];$changes[]=$expected;
}
$cleanup=Patch::readPinned($dir.'/'.$argv[6],$argv[7]);$nonce=$cleanup['nonce']??'';
if(($cleanup['target']??null)!=='gramlyze.loc'||($cleanup['exact_route_bytes_restored']??null)!==true
    ||($cleanup['removed_endpoint_status']??null)!==404||($cleanup['physical_hashes_unchanged']??null)!==true
    ||($cleanup['db_writes']??null)!==0||($cleanup['route_writes']??null)!==0
    ||$argv[6]!=='cleanup-'.$nonce.'.json'||preg_match('/^[a-f0-9]{32}$/D',$nonce)!==1){
    throw new RuntimeException('Exact independent nonce cleanup/404 receipt differs.');
}
$proofName='local-proof-'.$nonce.'.json';$savedName='routes-before-'.$nonce.'.bin';
$proof=Patch::readPinned($dir.'/'.$proofName,$cleanup['proof_sha256']??'');
if(($proof['routes_before_file']??null)!==$savedName||file_get_contents($root.'/routes/api.php')!==file_get_contents($dir.'/'.$savedName)
    ||Patch::fileHash($root.'/routes/api.php')!==($cleanup['routes_after_sha256']??null)
    ||($preview['sources']['private:'.$proofName]??null)!==Patch::fileHash($dir.'/'.$proofName)
    ||($preview['sources']['private:'.$savedName]??null)!==Patch::fileHash($dir.'/'.$savedName)){
    throw new RuntimeException('Cleaned nonce proof/snapshot is not the exact reviewed correction source.');
}
foreach($preview['sources'] as $path=>$sha){
    if($path==='served:routes/api.php'){
        // The exact installed bytes are recorded in the plan; the current route is independently restored.
        $routePreviewHash=$sha;continue;
    }
    $file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(Patch::fileHash($file)!==$sha){throw new RuntimeException('Reviewed correction source/evidence changed: '.$path);}
}
if(!isset($routePreviewHash)||!is_string($routePreviewHash)||preg_match('/^[a-f0-9]{64}$/D',$routePreviewHash)!==1){
    throw new RuntimeException('Reviewed installed route hash absent.');
}
$sourceState=Patch::sourceState($root,$dir);$expectedSources=$preview['sources'];
unset($expectedSources['served:routes/api.php'],$expectedSources['private:'.$proofName],$expectedSources['private:'.$savedName],$expectedSources['private:'.Patch::CURRENT_BEFORE]);
if($sourceState!==$expectedSources){throw new RuntimeException('Exact cleaned source namespace differs from reviewed correction.');}
$initialChanges=[];
foreach($original['updates'] as $change){
    if($change['table']!=='text_blocks'||array_keys($change['before'])!==['body']||array_keys($change['after'])!==['body']
        ||$change['before_sha256']!==$digest($change['before'])||$change['after_sha256']!==$digest($change['after'])){
        throw new RuntimeException('Original 49-cell initial write scope differs.');
    }
    $initialChanges[$change['id']]=$change;
}
foreach($targets as $id=>$target){if(($initialChanges[$id]['after']['body']??null)!==$target['before']){throw new RuntimeException('Two-note overlay is not on an original accepted body.');}}
$newPractices=array_column($baseline['new_practices'],null,'id');$originalInserts=array_column(array_column($original['inserts'],'fields'),null,'uuid');
$app=require $root.'/bootstrap/app.php';try{$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();}catch(Throwable $e){$fail($e);}set_exception_handler($fail);
if($normal(base_path())!==$normal($root)||$normal(app()->environmentPath())!==$normal($root)){throw new RuntimeException('Application/environment ROOT differs.');}
$db=Illuminate\Support\Facades\DB::connection();App\Services\M85LocalTargetGuard::assertConfiguredDatabase($db);
if($db->transactionLevel()!==0){throw new RuntimeException('No ambient transaction permitted.');}
$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try{
    $runtime=App\Services\M85LocalTargetGuard::publicIdentity($db,$root.'/public');
    if($runtime!==$baseline['runtime']||$runtime!==$fresh['runtime']){throw new RuntimeException('Fresh local runtime differs.');}
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);
    if($tables!==array_keys($baseline['sql_after'])||count($tables)!==46||count($schema['schema']??[])!==46){throw new RuntimeException('Actual all46 table/schema set differs.');}
    $raw=[];$normalizedCurrent=[];$normalizedInitial=[];$protected=[];$seenTargets=[];$seenOriginal=[];$seenNew=[];
    foreach($tables as $table){
        if($db->getSchemaBuilder()->getColumns($table)!==($schema['schema'][$table]??null)){throw new RuntimeException('Full captured schema changed: '.$table);}
        $columns=$db->getSchemaBuilder()->getColumnListing($table);$q=$db->table($table);
        foreach(in_array('id',$columns,true)?['id']:$columns as $column){$q->orderBy($column);}
        $hashes=[];$counts=['raw'=>0,'current'=>0,'initial'=>0,'protected'=>0];foreach(array_keys($counts) as $key){$hashes[$key]=hash_init('sha256');}
        foreach($q->cursor() as $record){
            $row=(array)$record;$versions=['raw'=>$row,'current'=>$row,'initial'=>$row,'protected'=>$row];$id=$row['id']??null;
            if($table==='text_blocks'&&isset($targets[$id])){
                $t=$targets[$id];if($row!==array_replace($t['row'],['body'=>$t['after']])){throw new RuntimeException('Exact applied note row/metadata/timestamps differ.');}
                $versions['current']['body']=$t['before'];unset($versions['protected']['body']);$seenTargets[$id]=true;
            }
            if($table==='text_blocks'&&isset($initialChanges[$id])){
                $change=$initialChanges[$id];$expected=$targets[$id]['after']??$change['after']['body'];
                if($row['body']!==$expected){throw new RuntimeException('Current canonical body does not equal exact initial-plus-note overlay.');}
                $versions['initial']['body']=$change['before']['body'];$seenOriginal[$id]=true;
            }
            if($table==='text_blocks'&&isset($newPractices[$id])){
                $fact=$newPractices[$id];$fields=$originalInserts[$row['uuid']]??null;
                if($fields===null||($fact['uuid']??null)!==$row['uuid']||$row['created_at']!==$row['updated_at']){throw new RuntimeException('Original six new practice identities differ.');}
                foreach($fields as $key=>$value){if(($row[$key]??null)!==$value){throw new RuntimeException('Original six practice fields changed.');}}
                $versions['initial']=null;$seenNew[$id]=true;
            }
            foreach($versions as $key=>$value){if($value!==null){hash_update($hashes[$key],$digest($value)."\n");++$counts[$key];}}
        }
        foreach(['raw'=>&$raw,'current'=>&$normalizedCurrent,'initial'=>&$normalizedInitial,'protected'=>&$protected] as $key=>&$map){
            $map[$table]=['count'=>$counts[$key],'sha256'=>hash_final($hashes[$key])];
        }unset($map);
    }
    if(count($seenTargets)!==2||count($seenOriginal)!==49||count($seenNew)!==6
        ||$normalizedCurrent!==$baseline['sql_after']||$normalizedInitial!==$initial['sql_before']||$protected!==$preview['protected']){
        throw new RuntimeException('Fresh all46 current/initial/protected reconstruction differs.');
    }
    $pageChecks=[];$uk=0;$other=0;
    foreach($next['targets'] as $i=>$target){
        $captured=$preview['pages'][$target['identity']];$page=(array)$db->table('pages')->where('id',Package::PAGE_IDS[$i])->sole();
        if($page!==$captured['page']||$page['text']!==null){throw new RuntimeException('Canonical page metadata/text/timestamp changed.');}
        $rows=$db->table('text_blocks')->where('page_id',$page['id'])->orderBy('id')->get()->map(static fn($r)=>(array)$r)->all();
        $expectedRows=$captured['blocks'];foreach($expectedRows as &$r){if(isset($targets[$r['id']])){$r['body']=$targets[$r['id']]['after'];}}unset($r);
        if($rows!==$expectedRows){throw new RuntimeException('Exact page block rowset changed.');}
        foreach($rows as $row){if($row['locale']!=='uk'){++$other;continue;}++$uk;$position=$row['sort_order'];
            $body=$position===0?$target['after']['page']['subtitle_html']:($target['after']['page']['blocks'][$position-1]['body']??null);
            if($row['body']!==$body||$row['level']!==null){throw new RuntimeException('Actual canonical UK body/NULL level differs.');}
        }
        $pageChecks[]=['page_id'=>$page['id'],'canonical_uk_bodies_exact'=>true,'all_rows_and_timestamps_exact'=>true];
    }
    if($uk!==57||$other!==102){throw new RuntimeException('Actual UK/EN/PL row counts differ.');}
}finally{$db->rollBack();}
if($sourceState!==Patch::sourceState($root,$dir)){throw new RuntimeException('Source state changed during independent AFTER scan.');}
$repeat=null;
if(count($argv)===10){
    $prior=Patch::readPinned($dir.'/'.$argv[8],$argv[9]);
    foreach(['sql_after'=>$raw,'normalized_initial_after'=>$normalizedCurrent,'normalized_before'=>$normalizedInitial,'protected'=>$protected,
        'source_state'=>$sourceState,'plan_digest'=>$argv[2],'backup_sha256'=>$argv[4]] as $field=>$expected){
        if(($prior[$field]??null)!==$expected){throw new RuntimeException('Post-UI strict repeat differs: '.$field);}
    }$repeat=['file'=>$argv[8],'sha256'=>$argv[9],'all_sql_sources_and_exact_plans_same'=>true];
}
$receipt=['checked_at'=>gmdate('c'),'runtime'=>$runtime,'author_revision'=>'1.0.1','native_version'=>'1.0.0',
    'plan_file'=>$argv[1],'plan_digest'=>$argv[2],'backup_file'=>$argv[3],'backup_sha256'=>$argv[4],'backup_equals_reviewed_preview'=>true,
    'initial_after_file'=>Patch::BASELINE,'initial_after_sha256'=>Patch::BASELINE_SHA,'initial_preview_file'=>$baseline['plan_file'],
    'initial_preview_digest'=>$baseline['plan_digest'],'initial_backup_file'=>$baseline['backup_file'],'initial_backup_sha256'=>$baseline['backup_sha256'],
    'independent_before_sha256'=>App\Services\M85ContentPatch::BEFORE_SHA,'current_before_sha256'=>Patch::fileHash($dir.'/'.Patch::CURRENT_BEFORE),
    'manifest'=>Patch::MANIFEST,'manifest_sha256'=>Patch::MANIFEST_SHA,'source_state'=>$sourceState,'cleanup_file'=>$argv[6],'cleanup_sha256'=>$argv[7],
    'sql_tables'=>46,'full_schema_tables_checked'=>46,'sql_after'=>$raw,'normalized_initial_after'=>$normalizedCurrent,
    'normalized_before'=>$normalizedInitial,'protected'=>$protected,'update_operations'=>2,'insert_operations'=>0,'delete_operations'=>0,
    'note_additions'=>6,'learning_text_exact_existing_hints'=>true,'new_practices'=>$baseline['new_practices'],'new_question_families'=>[],
    'continuing_uk_rows'=>51,'unchanged_en_pl_rows'=>102,'fresh_uk_rows'=>57,'fresh_page_checks'=>$pageChecks,
    'all_old_rows_and_timestamps_exact'=>true,'independent_before_projection_exact'=>true,'reviewed_protected_projection_exact'=>true,
    'fresh_original_before_reconstruction_rerun'=>true,'source_sync_unchanged'=>true,'post_ui_repeat'=>$repeat,
    'inherited_initial_source_lineage'=>$baseline['source_lineage'],'initial_receipts_modified'=>false,
    'db_writes'=>0,'http_requests'=>0,'browser_acceptance'=>'Not checked by this SELECT-only gate; separate actual UI receipt required.'];
$bytes=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($out,'x');if(!$h){throw new RuntimeException('Exclusive AFTER receipt unavailable.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('AFTER receipt incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>basename($out),'sha256'=>hash('sha256',$bytes),'fresh_sql_tables'=>46,'normalized_note_cells'=>2,
    'original_49_updates_reversed'=>49,'original_6_practices_omitted'=>6,'db_writes'=>0,'http_requests'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
