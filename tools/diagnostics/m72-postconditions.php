<?php

/**
 * Independent read-only M72 SQL/source gate; no apply, restore, seeder or HTTP.
 * php tools/diagnostics/m72-postconditions.php preview-v1.json <reviewed-digest> final-readonly-v1.json
 * Optional final arguments: noop-plan-v2.json <reviewed-current-no-op-digest>.
 * Reconstructs BEFORE by reverting only the exact reviewed Page.text and existing UK body fields.
 * M72 has no appended rows, metadata transitions or question-family writes.
 */
$failureHandler=static function (Throwable $error): void {
    fwrite(STDERR, $error instanceof RuntimeException && !$error instanceof PDOException
        ? 'M72 final audit: '.$error->getMessage()."\n"
        : "M72 final audit incomplete; no successful receipt. No SQL writes.\n");
    exit(1);
};
set_exception_handler($failureHandler);
$previewName=$argv[1]??'';$reviewedDigest=$argv[2]??'';$receiptName=$argv[3]??'';
$currentPlanName=$argv[4]??null;$currentPlanDigest=$argv[5]??null;$revisionMode=count($argv)===6;
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array(count($argv),[4,6],true)
    ||preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$previewName)!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$reviewedDigest)!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$receiptName)!==1
    ||($revisionMode&&($currentPlanName!=='noop-plan-v2.json'
        ||$currentPlanDigest!=='44c0855b988eba20b0f53cc4cd9259138ac2e36fb78325b825392262e3143f94'))){
    throw new RuntimeException('Explicit apply-preview/digest/new receipt and optional exact bank-revision no-op-plan/digest required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m72-local';
$normal=static fn($path):string=>rtrim(strtolower(str_replace('\\','/',(string)realpath($path))),'/');
if($normal(dirname(__DIR__,2))!==$normal($root)||is_link($dir)||!is_dir($dir)
    ||$normal($dir)!==$normal($root).'/storage/app/seo-m72-local'){
    throw new RuntimeException('Final audit must run from actual served ROOT/private directory.');
}
$receiptPath=$dir.'/'.$receiptName;
if(file_exists($receiptPath)||is_link($receiptPath)){throw new RuntimeException('Final receipt already exists; no overwrite.');}
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
// Laravel replaces the CLI renderer while booting; failures must remain nonzero.
set_exception_handler($failureHandler);
if($normal(base_path())!==$normal($root)||$normal(app()->environmentPath())!==$normal($root)){
    throw new RuntimeException('Final application/environment root differs.');
}
use App\Services\M72ContentPatch as Patch;
use App\Services\M72LocalTargetGuard as Guard;
use App\Support\M72PartitivesNegativesGeography as Package;

if(Patch::BEFORE_FILE!=='before-v1.json'
    ||Patch::BEFORE_SHA!=='33cd475c775288da255c3584aa3b9ef665255a1f31abc14f4df294ef749a690d'
    ||Package::BASE_SHA!=='2bcd468b25af3c44dcf34ccee0e012f3f683335d'
    ||Package::PAGE_IDS!==[88,87,83]||Package::CONTINUING_COUNTS!==[14,13,15]
    ||Package::PRACTICE_SOURCE_INDICES!==[12,11,13]||Package::LEVELS!==[null,null,null]){
    throw new RuntimeException('Exact frozen M72 physical/source/continuing-practice scope differs.');
}

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
    ||($preview['inserts']??null)!==[]||($preview['deletes']??[])!==[]){
    throw new RuntimeException('Reviewed preview/base or exact zero-insert BEFORE scope differs.');
}
[$sourceBefore,$patch,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$patch,$master,$contract);
$currentPlan=null;$superseded=[];$revisionProof=null;$sourceEvidence=[];
$sourceFile=static fn(string $path):string=>str_starts_with($path,'private:')
    ?$dir.'/'.substr($path,strlen('private:')):$root.'/'.$path;
$allowedProfiles=[
    'app/Services/M72ContentPatch.php'=>'98a30817309455eeb9e76fc5db88034e1a0771e3b77687af27020be5869ca33b',
    'app/Support/M72BuilderSelectionPresentation.php'=>'be5648d0e11f678074a3887fb93a89876f865d011ba6b8448f9649e96ca01bab',
];
if($revisionMode){
    $currentPlan=$load($currentPlanName);$currentDigestBody=$currentPlan;unset($currentDigestBody['sha256']);
    if(($currentPlan['sha256']??null)!==$currentPlanDigest||$digest($currentDigestBody)!==$currentPlanDigest
        ||($currentPlan['state']??null)!=='after'||($currentPlan['names']??null)!==Package::OWNERS
        ||($currentPlan['patch']??null)!==($preview['patch']??null)||($currentPlan['version']??null)!==1
        ||($currentPlan['updates']??null)!==[]||($currentPlan['inserts']??null)!==[]||($currentPlan['deletes']??[])!==[]
        ||array_keys($currentPlan['pages']??[])!==Package::OWNERS||($currentPlan['connection']??null)!==$preview['connection']
        ||($currentPlan['protected']??null)!==$preview['protected']){
        throw new RuntimeException('Fresh guarded current runtime plan is not the exact reviewed AFTER no-op.');
    }
    if(Patch::SYNC_RECEIPT!=='source-sync-v2.json'
        ||App\Support\M72BuilderSelectionPresentation::REVISION!=='docs/content/m72-bank-selection-revision.v1.0.1.json'
        ||App\Support\M72BuilderSelectionPresentation::REVISION_SHA!=='09d3e65edf74dc1e2e05e1bc0edd11a8b63e4b4062f502d375605ea9434d00b6'
        ||App\Support\M72BuilderSelectionPresentation::AUDIT!=='docs/content/m72-partitives-negatives-geography-bank-audit.v1.0.1.json'
        ||App\Support\M72BuilderSelectionPresentation::AUDIT_SHA!=='803855b45aa9f01d4ae7a9c6e01b8fa108f975104624ff3825bea21015999529'){
        throw new RuntimeException('Optional source transition is outside the exact frozen bank-only revision1.0.1.');
    }
    $revisionPath=App\Support\M72BuilderSelectionPresentation::REVISION;
    $auditPath=App\Support\M72BuilderSelectionPresentation::AUDIT;
    if($hash($root.'/'.$revisionPath)!==App\Support\M72BuilderSelectionPresentation::REVISION_SHA
        ||$hash($root.'/'.$auditPath)!==App\Support\M72BuilderSelectionPresentation::AUDIT_SHA){
        throw new RuntimeException('Bank-only revision or active audit raw bytes differ.');
    }
    $activeAudit=App\Support\M72BuilderSelectionPresentation::loadAudit($root);
    if(($activeAudit['version']??null)!=='1.0.1'||($activeAudit['status']??null)!=='frozen'){
        throw new RuntimeException('Active bank-only audit is not frozen1.0.1.');
    }
    $oldSync=$load('source-sync-v1.json','9b4c3c11ec7ca829c6bf88ce1fb17cd58f6316a862f612823039e5b4272a7103');
    if(($preview['sources']['private:source-sync-v1.json']??null)!=='9b4c3c11ec7ca829c6bf88ce1fb17cd58f6316a862f612823039e5b4272a7103'
        ||($oldSync['base_sha']??null)!==Package::BASE_SHA||($oldSync['served_root']??null)!==$root
        ||($oldSync['source_worktree']??null)!==Patch::SOURCE_WORKTREE){
        throw new RuntimeException('Independent original apply source-sync identity differs.');
    }
    $oldRecords=array_column($oldSync['files']??[],null,'path');
    foreach($allowedProfiles as $path=>$oldSha){
        $record=$oldRecords[$path]??null;
        if(($preview['sources'][$path]??null)!==$oldSha||($record['root_after_sha256']??null)!==$oldSha
            ||($record['worktree_sha256']??null)!==$oldSha){
            throw new RuntimeException('Two old profile hashes must bind to original preview and independent source-sync-v1.');
        }
    }
    $expectedSources=[...Patch::sourcePaths($patch,$contract,$root),'private:'.Patch::BEFORE_FILE,
        'private:'.Patch::CONSUMERS_FILE,'private:'.Patch::SYNC_RECEIPT];sort($expectedSources);
    $currentSources=array_keys($currentPlan['sources']??[]);sort($currentSources);
    if($currentSources!==$expectedSources){throw new RuntimeException('Fresh no-op sources omit or add a current runtime source.');}
    foreach($currentPlan['sources'] as $path=>$sha){
        $file=$sourceFile($path);
        if(!hash_equals($sha,$hash($file))){throw new RuntimeException('Fresh no-op source/evidence changed: '.$path);}
        $sourceEvidence[$file]=$sha;
    }
    $sourceEvidence[$dir.'/'.$currentPlanName]=$hash($dir.'/'.$currentPlanName);
    $revisionProof=['version'=>'1.0.1','revision_path'=>$revisionPath,
        'revision_sha256'=>App\Support\M72BuilderSelectionPresentation::REVISION_SHA,
        'active_audit_path'=>$auditPath,'active_audit_sha256'=>App\Support\M72BuilderSelectionPresentation::AUDIT_SHA,
        'original_source_sync_file'=>'source-sync-v1.json',
        'original_source_sync_sha256'=>'9b4c3c11ec7ca829c6bf88ce1fb17cd58f6316a862f612823039e5b4272a7103'];
}
// The original apply-preview source guard is retained. Only two explicitly
// reviewed new M72 profiles may differ, never shared primitives or frozen data.
foreach($preview['sources'] as $path=>$sha){
    $file=$sourceFile($path);$actualSha=$hash($file);
    if(!hash_equals($sha,$actualSha)){
        if(!$revisionMode||!isset($allowedProfiles[$path])||$sha!==$allowedProfiles[$path]
            ||($currentPlan['sources'][$path]??null)!==$actualSha){
            throw new RuntimeException('Reviewed preview source/evidence changed: '.$path);
        }
        $superseded[$path]=['apply_preview_sha256'=>$sha,'current_guarded_noop_sha256'=>$actualSha,
            'original_sync_sha256'=>$oldRecords[$path]['root_after_sha256']];
    }
    $sourceEvidence[$file]=$actualSha;
}
if($revisionMode&&array_keys($superseded)!==array_keys($allowedProfiles)){
    throw new RuntimeException('The bank-only revision must supersede exactly the two reviewed new M72 profiles.');
}
$sourceEvidence[$dir.'/'.$previewName]=$hash($dir.'/'.$previewName);
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
    $pageBefore=[];$pageAfter=[];$ukBefore=[];$ukAfter=[];$nonUk=0;$expectedChanges=[];$practiceFacts=[];$subtitles=0;$ukByOwner=[];
    foreach($patch['targets'] as $i=>$target){
        $owner=$target['identity'];$captured=$before['pages'][$owner];$oldPage=$captured['page'];$ukByOwner[$owner]=0;
        $pageBefore[$oldPage['id']]=$oldPage;
        $pageAfter[$oldPage['id']]=array_replace($oldPage,['text'=>$target['after']['page']['subtitle_text']]);
        if($oldPage['text']!==$pageAfter[$oldPage['id']]['text']){
            $expectedChanges['pages:'.$oldPage['id']]=['table'=>'pages','id'=>$oldPage['id'],'seeder'=>$owner,
                'before'=>['text'=>$oldPage['text']],'after'=>['text'=>$pageAfter[$oldPage['id']]['text']]];
        }
        foreach($captured['blocks'] as $old){
            if($old['locale']!=='uk'){
                if(!in_array($old['locale'],['en','pl'],true)){throw new RuntimeException('Independent non-UK locale is outside exact EN/PL scope.');}
                ++$nonUk;continue;
            }
            ++$ukByOwner[$owner];$position=(int)$old['sort_order'];
            if($position===0){
                if($old['type']!=='subtitle'||$old['column']!=='header'){throw new RuntimeException('Independent continuing subtitle metadata differs.');}
                ++$subtitles;
            }
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
    if(array_keys($pageBefore)!==[88,87,83]||count($ukBefore)!==45||array_values($ukByOwner)!==[15,14,16]
        ||$subtitles!==3||$nonUk!==90||count($practiceFacts)!==3||$pageUpdates!==3||$blockUpdates!==45
        ||count($preview['updates']??[])!==count($expectedChanges)){
        throw new RuntimeException('Exact M72 three-owner/continuing-row update scope differs.');
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
    if(count($tables)!==46||$tables!==array_keys($before['sql_before'])){throw new RuntimeException('Actual SQL table inventory changed.');}
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
            // No rows are omitted: M72 authorizes neither inserts nor deletes in any family.
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
foreach($sourceEvidence as $file=>$sha){
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('Reviewed source/evidence changed during final SQL scan.');}
}
$receipt=['checked_at'=>gmdate('c'),'runtime'=>$runtime,'plan_file'=>$previewName,'plan_digest'=>$reviewedDigest,
    'independent_before_sha256'=>Patch::BEFORE_SHA,'page_text_updates'=>$pageUpdates,'existing_block_body_updates'=>$blockUpdates,
    'distinct_changed_rows'=>count($expectedChanges),'continuing_uk_rows'=>count($ukBefore),'unchanged_en_pl_rows'=>$nonUk,
    'continuing_structured_blocks'=>42,'continuing_subtitle_rows'=>$subtitles,'uk_rows_by_owner'=>$ukByOwner,
    'continuing_practices'=>$practiceFacts,'metadata_transitions'=>0,'inserted_rows'=>0,'deleted_rows'=>0,
    'new_practices'=>[],'new_question_families'=>[],'all_old_rows_and_timestamps_exact'=>true,
    'independent_before_projection_exact'=>true,'reviewed_protected_projection_exact'=>true,
    'sql_tables'=>count($rawAfter),'sql_after'=>$rawAfter,'normalized_before'=>$normalized,'protected'=>$protected,
    'visual_files_unchanged'=>count($before['visual_hashes']),'frozen_files_unchanged'=>count($before['frozen_hashes']),
    'source_sync_unchanged'=>true,'source_sync_file'=>Patch::SYNC_RECEIPT,
    'apply_plan'=>['file'=>$previewName,'digest'=>$reviewedDigest,'reviewed_sql_updates'=>count($expectedChanges)],
    'current_runtime_plan'=>$revisionMode?['file'=>$currentPlanName,'digest'=>$currentPlanDigest,'state'=>'after','updates'=>0,'inserts'=>0,'deletes'=>0]:null,
    'bank_only_source_revision'=>$revisionProof,'superseded_apply_profile_sources'=>$superseded,
    'all_other_apply_preview_sources_exact'=>true,'current_runtime_sources_exact'=>true,
    'db_writes'=>0,'http_requests'=>0,
    'http_metadata_and_browser_acceptance'=>'separate evidence; not performed by this SQL/source audit'];
$bytes=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$handle=fopen($receiptPath,'x');if($handle===false){throw new RuntimeException('Exclusive final receipt creation failed.');}
try{if(fwrite($handle,$bytes)!==strlen($bytes)||!fflush($handle)){throw new RuntimeException('Final receipt write incomplete.');}}
finally{fclose($handle);}
echo json_encode(['file'=>$receiptName,'sha256'=>hash('sha256',$bytes),'sql_tables'=>count($rawAfter),
    'distinct_changed_rows'=>count($expectedChanges),'continuing_uk_rows'=>count($ukBefore),'unchanged_en_pl_rows'=>$nonUk,
    'continuing_practices'=>count($practiceFacts),'inserted_rows'=>0,'deleted_rows'=>0,
    'all_old_rows_and_timestamps_exact'=>true,'db_writes'=>0],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
