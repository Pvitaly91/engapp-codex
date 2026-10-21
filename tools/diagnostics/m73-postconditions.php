<?php

/**
 * Independent read-only M73 SQL/source gate. No apply, restore, seeding or HTTP.
 * php tools/diagnostics/m73-postconditions.php preview-v1.json <reviewed-plan-digest> allocation-before-v1.json <allocation-sha256> final-readonly-v1.json
 * Reconstruct all 46 BEFORE tables by reverting only reviewed Page.text/old UK body
 * fields and omitting exactly two source-bound, allocation-bound appended practices.
 */
$failure=static function(Throwable $error):void{
    fwrite(STDERR,$error instanceof RuntimeException&&!$error instanceof PDOException
        ?'M73 final audit: '.$error->getMessage()."\n":"M73 final audit incomplete; no receipt. No SQL writes.\n");
    exit(1);
};
set_exception_handler($failure);
$previewName=$argv[1]??'';$reviewedDigest=$argv[2]??'';$allocationName=$argv[3]??'';
$allocationSha=$argv[4]??'';$receiptName=$argv[5]??'';
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==6
    ||preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$previewName)!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$reviewedDigest)!==1||$allocationName!=='allocation-before-v1.json'
    ||preg_match('/^[a-f0-9]{64}$/D',$allocationSha)!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$receiptName)!==1){
    throw new RuntimeException('Reviewed preview/digest, exact allocation receipt/SHA and exclusive final basename are required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m73-local';
$normal=static fn($p):string=>rtrim(strtolower(str_replace('\\','/',(string)realpath($p))),'/');
if($normal(dirname(__DIR__,2))!==$normal($root)||is_link($dir)||!is_dir($dir)
    ||$normal($dir)!==$normal($root).'/storage/app/seo-m73-local'){
    throw new RuntimeException('Final audit must run from actual served ROOT/private directory.');
}
$receiptPath=$dir.'/'.$receiptName;
if(file_exists($receiptPath)||is_link($receiptPath)){throw new RuntimeException('Final private receipt already exists; no overwrite.');}
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler($failure);
use App\Services\M73ContentPatch as Patch;
use App\Services\M73LocalTargetGuard as Guard;
use App\Support\M73GenericIrregularCollective as Package;
use App\Support\M26DetailPackage;
if($normal(base_path())!==$normal($root)||$normal(app()->environmentPath())!==$normal($root)
    ||Patch::BEFORE_FILE!=='before-v1.json'
    ||Patch::BEFORE_SHA!=='41cb3ac163738e4d2662b4c53a3b2288de3303161d2da6ef5a1f42a9632d0ac9'
    ||Patch::CONSUMERS_SHA!=='e48d6886d85dab0bc5c96ebf8b9b8b7723ff626f2df08ab1609711bde56cda3c'
    ||Package::BASE_SHA!=='cb55b329827e52a56cb6d41edb21e70768cf86f4'
    ||Package::PAGE_IDS!==[80,86,84]||Package::CONTINUING_COUNTS!==[13,7,6]
    ||Package::PRACTICE_SOURCE_INDICES!==[11,7,6]||Package::LEVELS!==[null,'A2','B1']){
    throw new RuntimeException('Exact M73 physical/source/mixed-practice scope differs.');
}
$hash=static fn(string $p):string=>Patch::fileHash($p);
$load=static function(string $name,?string $expected=null)use($dir,$hash):array{
    if(preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$name)!==1){throw new RuntimeException('Private JSON basename differs.');}
    $actual=$hash($dir.'/'.$name);
    if($expected!==null&&!hash_equals($expected,$actual)){throw new RuntimeException('Private reviewed evidence bytes differ.');}
    return json_decode(file_get_contents($dir.'/'.$name),true,flags:JSON_THROW_ON_ERROR);
};
$digest=static fn(array $v):string=>App\Services\M26ContentPatch::digest($v);
$same=static function(array $actual,array $expected,string $message):void{
    ksort($actual);ksort($expected);if($actual!==$expected){throw new RuntimeException($message);}
};
$before=$load(Patch::BEFORE_FILE,Patch::BEFORE_SHA);$consumers=$load(Patch::CONSUMERS_FILE,Patch::CONSUMERS_SHA);
$preview=$load($previewName);$unsigned=$preview;unset($unsigned['sha256']);$allocation=$load($allocationName,$allocationSha);
if(($preview['sha256']??null)!==$reviewedDigest||$digest($unsigned)!==$reviewedDigest
    ||($preview['state']??null)!=='before'||($preview['names']??null)!==Package::OWNERS
    ||array_keys($before['pages']??[])!==Package::OWNERS||($before['base_sha']??null)!==Package::BASE_SHA
    ||($before['sql_before']??null)!==($before['sql_after_http']??null)||count($preview['inserts']??[])!==2
    ||($preview['deletes']??[])!==[]||count($before['visual_hashes']??[])!==326||count($before['frozen_hashes']??[])!==2575
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']
    ||($consumers['all_tables_after_http']??null)!==$before['sql_before']||($consumers['unchanged_during_http']??null)!==true){
    throw new RuntimeException('Reviewed preview or independent BEFORE/old-consumer scope differs.');
}
if(($allocation['schema_version']??null)!==1||($allocation['kind']??null)!=='M73_readonly_pre_apply_allocation'
    ||($allocation['before_sha256']??null)!==Patch::BEFORE_SHA||($allocation['consumers_sha256']??null)!==Patch::CONSUMERS_SHA
    ||($allocation['plan_file']??null)!==$previewName||($allocation['plan_digest']??null)!==$reviewedDigest
    ||($allocation['plan_file_sha256']??null)!==$hash($dir.'/'.$previewName)
    ||($allocation['capture_script_sha256']??null)!==$hash($dir.'/capture-allocation-v1.php')
    ||($allocation['runtime']??null)!==$before['identity']||($allocation['sql_before']??null)!==$before['sql_before']
    ||($allocation['variables']??null)!==['increment'=>1,'offset'=>1]
    ||array_keys($allocation['allocation']??[])!==array_keys($before['sql_before'])
    ||array_keys($allocation['schema']??[])!==array_keys($before['sql_before'])
    ||($allocation['db_writes']??null)!==0||($allocation['http_requests']??null)!==0){
    throw new RuntimeException('Exact pre-apply allocation/preview/runtime/46-table binding differs.');
}
foreach($before['schema'] as $table=>$columns){
    if(($allocation['schema'][$table]??null)!==$columns){throw new RuntimeException('Independent original schema differs from allocation capture.');}
}
foreach($allocation['allocation'] as $value){
    if(!is_array($value)||array_keys($value)!==['max_id','next_id']
        ||($value['max_id']!==null&&(!is_int($value['max_id'])||$value['max_id']<0))
        ||($value['next_id']!==null&&(!is_int($value['next_id'])||$value['next_id']<1))){
        throw new RuntimeException('Captured maximum/next-ID shape is invalid.');
    }
}
[$sourceBefore,$patch,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$patch,$master,$contract);
$sourceEvidence=[__FILE__=>$hash(__FILE__),$dir.'/'.$previewName=>$hash($dir.'/'.$previewName),
    $dir.'/'.$allocationName=>$allocationSha,$dir.'/capture-allocation-v1.php'=>$allocation['capture_script_sha256']];
$expectedSources=[...Patch::sourcePaths($patch,$contract,$root),'private:'.Patch::BEFORE_FILE,
    'private:'.Patch::CONSUMERS_FILE,'private:'.Patch::SYNC_RECEIPT];sort($expectedSources);
$actualSources=array_keys($preview['sources']??[]);sort($actualSources);
if($actualSources!==$expectedSources){throw new RuntimeException('Reviewed preview omits or adds a runtime/evidence source.');}
foreach($preview['sources'] as $path=>$sha){
    $file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('Reviewed preview source/evidence changed: '.$path);}
    $sourceEvidence[$file]=$sha;
}
foreach($before['physical_hashes'] as $path=>$sha){
    if(!hash_equals($sha,$hash($path))){throw new RuntimeException('Physical target/configuration changed.');}
    $sourceEvidence[$path]=$sha;
}
foreach([$before['visual_hashes'],$before['frozen_hashes']] as $hashes){
    foreach($hashes as $path=>$sha){
        if(!hash_equals($sha,$hash($root.'/'.$path))){throw new RuntimeException('Visual/historical frozen source changed: '.$path);}
        $sourceEvidence[$root.'/'.$path]=$sha;
    }
}
$sync=$load(Patch::SYNC_RECEIPT);$syncPaths=array_column($sync['files']??[],'path');sort($syncPaths);
if($syncPaths!==Patch::syncPaths($patch,$contract)||($sync['base_sha']??null)!==Package::BASE_SHA
    ||($sync['served_root']??null)!==$root||($sync['source_worktree']??null)!==Patch::SOURCE_WORKTREE){
    throw new RuntimeException('Reviewed finite ROOT/worktree source-sync scope differs.');
}
foreach($sync['files'] as $record){
    if($hash($root.'/'.$record['path'])!==$record['root_after_sha256']
        ||$hash(Patch::SOURCE_WORKTREE.'/'.$record['path'])!==$record['worktree_sha256']){
        throw new RuntimeException('Reviewed ROOT/worktree source-sync bytes changed.');
    }
    $sourceEvidence[$root.'/'.$record['path']]=$record['root_after_sha256'];
    $sourceEvidence[Patch::SOURCE_WORKTREE.'/'.$record['path']]=$record['worktree_sha256'];
}
$db=Illuminate\Support\Facades\DB::connection();Guard::assertConfiguredDatabase($db);
$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try{
    $runtime=Guard::publicIdentity($db,$root.'/public');
    if($runtime!==$before['identity']||$runtime!==$allocation['runtime']){
        throw new RuntimeException('Final local runtime differs from independent BEFORE/allocation capture.');
    }
    $pageBefore=[];$pageAfter=[];$ukBefore=[];$ukAfter=[];$nonUk=0;$expectedChanges=[];$subtitles=0;
    $ukByOwner=[];$continuingPractices=[];$newSourceFields=[];
    foreach($patch['targets'] as $i=>$target){
        $owner=$target['identity'];$captured=$before['pages'][$owner];$oldPage=$captured['page'];$ukByOwner[$owner]=0;
        $pageBefore[$oldPage['id']]=$oldPage;$pageAfter[$oldPage['id']]=array_replace($oldPage,['text'=>$target['after']['page']['subtitle_text']]);
        if($oldPage['text']!==$pageAfter[$oldPage['id']]['text']){
            $expectedChanges['pages:'.$oldPage['id']]=['table'=>'pages','id'=>$oldPage['id'],'uuid'=>$oldPage['uuid']??null,
                'seeder'=>$owner,'before'=>['text'=>$oldPage['text']],'after'=>['text'=>$pageAfter[$oldPage['id']]['text']]];
        }
        foreach($captured['blocks'] as $old){
            if($old['locale']!=='uk'){
                if(!in_array($old['locale'],['en','pl'],true)){throw new RuntimeException('Non-UK locale is outside exact EN/PL scope.');}
                ++$nonUk;continue;
            }
            ++$ukByOwner[$owner];$position=(int)$old['sort_order'];
            if($position===0){
                if($old['type']!=='subtitle'||$old['column']!=='header'){throw new RuntimeException('Continuing subtitle metadata differs.');}
                ++$subtitles;
            }
            $body=$position===0?$target['after']['page']['subtitle_html']:($target['after']['page']['blocks'][$position-1]['body']??null);
            if(!is_string($body)||isset($ukBefore[$old['id']])){throw new RuntimeException('Independent UK inventory/order is incomplete or duplicated.');}
            $ukBefore[$old['id']]=$old;$ukAfter[$old['id']]=array_replace($old,['body'=>$body]);
            if($old['body']!==$body){
                $expectedChanges['text_blocks:'.$old['id']]=['table'=>'text_blocks','id'=>$old['id'],'uuid'=>$old['uuid'],
                    'seeder'=>$owner,'before'=>['body'=>$old['body']],'after'=>['body'=>$body]];
            }
            if($old['type']==='practice-set'){
                if($i!==0||$old['uuid']!==Package::GENERIC_PRACTICE_UUID||$position!==12||$old['level']!==null||$old['column']!=='left'){
                    throw new RuntimeException('Generic continuing practice identity/NULL metadata differs.');
                }
                $continuingPractices[]=['owner'=>$owner,'id'=>$old['id'],'uuid'=>$old['uuid'],'source_index'=>11,'level'=>null,'inserted'=>false];
            }
        }
        if($i===0){continue;}
        $slot=Package::CONTINUING_COUNTS[$i];$source=$target['after']['page']['blocks'][$slot]??null;$position=$slot+1;
        if(!is_array($source)||count($target['after']['page']['blocks'])!==$position||$source['type']!=='practice-set'
            ||$source['column']!=='left'||($source['level']??null)!==Package::LEVELS[$i]
            ||($source['uuid_key']??null)!=='m73:'.Package::SLUGS[$i].':'.($i===1?'irregular-practice':'collective-practice')){
            throw new RuntimeException('Exact two appended practice source identities differ.');
        }
        $uuid=M26DetailPackage::uuid($owner,$source,$position);
        $newSourceFields[$owner]=['uuid'=>$uuid,'page_id'=>$oldPage['id'],'page_category_id'=>$oldPage['page_category_id'],
            'locale'=>'uk','type'=>'practice-set','column'=>'left','heading'=>$source['heading']??null,
            'css_class'=>$source['css_class']??null,'sort_order'=>$position,'body'=>$source['body'],'level'=>Package::LEVELS[$i],'seeder'=>$owner];
    }
    $pageUpdates=count(array_filter($expectedChanges,static fn($c)=>$c['table']==='pages'));
    $blockUpdates=count(array_filter($expectedChanges,static fn($c)=>$c['table']==='text_blocks'));
    if(array_keys($pageBefore)!==Package::PAGE_IDS||count($ukBefore)!==29||array_values($ukByOwner)!==[14,8,7]
        ||$subtitles!==3||$nonUk!==58||count($continuingPractices)!==1||$pageUpdates!==3
        ||count($preview['updates']??[])!==count($expectedChanges)||array_keys($newSourceFields)!==array_slice(Package::OWNERS,1)){
        throw new RuntimeException('Exact M73 old-row inventory or source-derived update scope differs.');
    }
    $reviewedKeys=[];
    foreach($preview['updates'] as $change){
        $key=$change['table'].':'.$change['id'];$expected=$expectedChanges[$key]??null;
        if($expected===null||isset($reviewedKeys[$key])||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
            ||array_diff_key($change,['before_sha256'=>null,'after_sha256'=>null])!==$expected
            ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){
            throw new RuntimeException('Reviewed preview contains an unapproved existing-row field or duplicate.');
        }
        $reviewedKeys[$key]=true;
    }
    $next=$allocation['allocation']['text_blocks']['next_id'];$oldMax=$allocation['allocation']['text_blocks']['max_id'];
    if(!is_int($next)||$next<1||$next<=($oldMax??0)){throw new RuntimeException('Captured new-row ID boundary differs.');}
    $newPractices=[];$newPracticeFacts=[];$insertOwners=[];
    foreach($preview['inserts'] as $n=>$insert){
        $owner=$insert['seeder'];$expectedFields=$newSourceFields[$owner]??null;
        if($expectedFields===null||array_keys($insert)!==['table','seeder','fields','sha256']||$insert['table']!=='text_blocks'
            ||$insert['fields']!==$expectedFields||$insert['sha256']!==$digest($expectedFields)||isset($insertOwners[$owner])){
            throw new RuntimeException('Reviewed insert is not one exact frozen appended practice.');
        }
        $insertOwners[$owner]=true;$matches=$db->table('text_blocks')->where('uuid',$expectedFields['uuid'])->get()->all();
        if(count($matches)!==1){throw new RuntimeException('Appended practice UUID is missing or duplicated.');}
        $actual=(array)$matches[0];$id=$actual['id'];
        if(!is_int($id)||$id!==$next+$n||isset($newPractices[$id])||isset($ukBefore[$id])
            ||!is_string($actual['created_at'])||preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/D',$actual['created_at'])!==1
            ||$actual['created_at']!==$actual['updated_at']){
            throw new RuntimeException('Appended practice allocation or new-row timestamps differ.');
        }
        $expected=$expectedFields+['id'=>$id,'created_at'=>$actual['created_at'],'updated_at'=>$actual['created_at']];
        $same($actual,$expected,'Appended practice has an unreviewed owner/locale/metadata/content field.');
        $newPractices[$id]=$expected;
        $newPracticeFacts[]=['owner'=>$owner,'id'=>$id,'uuid'=>$expectedFields['uuid'],'source_index'=>$expectedFields['sort_order']-1,
            'level'=>$expectedFields['level'],'inserted'=>true];
    }
    if(array_keys($insertOwners)!==array_slice(Package::OWNERS,1)){throw new RuntimeException('Exact source-bound insert order differs.');}
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);
    if(count($tables)!==46||$tables!==array_keys($before['sql_before'])){throw new RuntimeException('Actual 46-table SQL inventory changed.');}
    $v=(array)$db->selectOne('SELECT @@auto_increment_increment AS step_value, @@auto_increment_offset AS offset_value');
    if(['increment'=>(int)$v['step_value'],'offset'=>(int)$v['offset_value']]!==$allocation['variables']){
        throw new RuntimeException('Captured automatic ID step/offset changed.');
    }
    $rawAfter=[];$normalized=[];$protected=[];$seenPages=[];$seenUk=[];$seenPractices=[];$allocationAfter=[];
    foreach($tables as $table){
        $columnsMeta=$db->getSchemaBuilder()->getColumns($table);
        if($columnsMeta!==$allocation['schema'][$table]){throw new RuntimeException('Captured schema changed during apply.');}
        $columns=array_column($columnsMeta,'name');$hasId=in_array('id',$columns,true);
        $query=$db->table($table);foreach($hasId?['id']:$columns as $column){$query->orderBy($column);}
        $rawHash=hash_init('sha256');$oldHash=hash_init('sha256');$protectedHash=hash_init('sha256');
        $rawCount=0;$oldCount=0;$protectedCount=0;
        foreach($query->cursor() as $record){
            $row=(array)$record;hash_update($rawHash,$digest($row)."\n");++$rawCount;
            $id=$row['id']??null;$projected=$row;$skipProtected=false;
            if($table==='text_blocks'&&isset($newPractices[$id])){
                $same($row,$newPractices[$id],'Exact inserted practice changed during final scan.');
                $seenPractices[$id]=true;continue;
            }
            if($table==='pages'&&isset($pageBefore[$id])){
                $same($row,$pageAfter[$id],'Target Page metadata/text/timestamps differ.');
                $projected=$pageBefore[$id];$seenPages[$id]=true;
            }
            if($table==='text_blocks'&&isset($ukBefore[$id])){
                $same($row,$ukAfter[$id],'Continuing UK body/owner/locale/metadata/timestamps differ.');
                $projected=$ukBefore[$id];$seenUk[$id]=true;$skipProtected=true;
            }
            hash_update($oldHash,$digest($projected)."\n");++$oldCount;
            if($skipProtected){continue;}
            if($table==='pages'&&isset($pageBefore[$id])){unset($row['text']);}
            hash_update($protectedHash,$digest($row)."\n");++$protectedCount;
        }
        $rawAfter[$table]=['count'=>$rawCount,'sha256'=>hash_final($rawHash)];
        $normalized[$table]=['count'=>$oldCount,'sha256'=>hash_final($oldHash)];
        $protected[$table]=['count'=>$protectedCount,'sha256'=>hash_final($protectedHash)];
        $metadata=$db->select('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$table]);
        if(count($metadata)!==1){throw new RuntimeException('Unique table allocation metadata is unavailable.');}
        $actualNext=((array)$metadata[0])['next_id'];$actualMax=$hasId?$db->table($table)->max('id'):null;
        $allocationAfter[$table]=['max_id'=>$actualMax===null?null:(int)$actualMax,'next_id'=>$actualNext===null?null:(int)$actualNext];
        $expectedAllocation=$table==='text_blocks'?['max_id'=>$next+1,'next_id'=>$next+2]:$allocation['allocation'][$table];
        if($allocationAfter[$table]!==$expectedAllocation){throw new RuntimeException('Unapproved table maximum/auto-increment change.');}
    }
    if(count($seenPages)!==3||count($seenUk)!==29||count($seenPractices)!==2){
        throw new RuntimeException('A continuing Page/block or exact new practice disappeared.');
    }
    if($normalized!==$before['sql_before']){throw new RuntimeException('An old question/progress/EN-PL/non-target row or timestamp changed.');}
    if($protected!==$preview['protected']){throw new RuntimeException('Reviewed protected projection differs.');}
}finally{$db->rollBack();}
foreach($sourceEvidence as $file=>$sha){
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('Reviewed source/evidence changed during SQL scan.');}
}
$receipt=['checked_at'=>gmdate('c'),'runtime'=>$runtime,'plan_file'=>$previewName,'plan_digest'=>$reviewedDigest,
    'independent_before_sha256'=>Patch::BEFORE_SHA,'old_consumers_sha256'=>Patch::CONSUMERS_SHA,
    'allocation_file'=>$allocationName,'allocation_sha256'=>$allocationSha,
    'page_text_updates'=>$pageUpdates,'existing_block_body_updates'=>$blockUpdates,'update_operations'=>count($expectedChanges),
    'distinct_updated_rows'=>count($expectedChanges),'distinct_changed_rows'=>count($expectedChanges)+count($newPractices),
    'continuing_uk_rows'=>count($ukBefore),'continuing_structured_blocks'=>array_sum(Package::CONTINUING_COUNTS),
    'continuing_subtitle_rows'=>$subtitles,'uk_rows_by_owner'=>$ukByOwner,'unchanged_en_pl_rows'=>$nonUk,
    'continuing_practices'=>$continuingPractices,'new_practices'=>$newPracticeFacts,
    'metadata_transitions'=>0,'inserted_rows'=>count($newPractices),'deleted_rows'=>0,'new_question_families'=>[],
    'all_old_rows_and_timestamps_exact'=>true,'independent_before_projection_exact'=>true,'reviewed_protected_projection_exact'=>true,
    'all_46_table_allocations_exact'=>true,'text_block_allocation_advance'=>2,
    'sql_tables'=>count($rawAfter),'sql_after'=>$rawAfter,'normalized_before'=>$normalized,'protected'=>$protected,'allocation_after'=>$allocationAfter,
    'visual_files_unchanged'=>count($before['visual_hashes']),'frozen_files_unchanged'=>count($before['frozen_hashes']),
    'source_sync_unchanged'=>true,'source_sync_file'=>Patch::SYNC_RECEIPT,'all_preview_sources_exact'=>true,
    'db_writes'=>0,'http_requests'=>0,'http_metadata_and_browser_acceptance'=>'Separate evidence; not performed by this SQL/source audit.'];
$bytes=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($receiptPath,'x');if(!$h){throw new RuntimeException('Exclusive final receipt creation failed.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('Final private receipt write incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>$receiptName,'sha256'=>hash('sha256',$bytes),'sql_tables'=>count($rawAfter),
    'page_text_updates'=>$pageUpdates,'existing_block_body_updates'=>$blockUpdates,'continuing_uk_rows'=>count($ukBefore),
    'unchanged_en_pl_rows'=>$nonUk,'continuing_practices'=>count($continuingPractices),'inserted_rows'=>count($newPractices),
    'deleted_rows'=>0,'all_old_rows_and_timestamps_exact'=>true,'all_46_table_allocations_exact'=>true,'db_writes'=>0,'http_requests'=>0],
    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
