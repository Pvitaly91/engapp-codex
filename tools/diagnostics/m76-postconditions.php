<?php

/** Independent SELECT-only reconstruction of every BEFORE table; never calls the writer or HTTP. */
$failure=static function(Throwable $e):void{
    fwrite(STDERR,$e instanceof RuntimeException&&!$e instanceof PDOException?'M76 final audit: '.$e->getMessage()."\n":"M76 final audit incomplete; no receipt. No SQL writes.\n");exit(1);
};
set_exception_handler($failure);
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==6
    ||preg_match('/^preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1
    ||preg_match('/^backup-v[1-9][0-9]*\.json$/D',$argv[3])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[5])!==1){
    throw new RuntimeException('M76 needs reviewed preview/digest, exclusive backup/raw SHA, fresh final basename.');
}
[$script,$planName,$planDigest,$backupName,$backupSha,$receiptName]=$argv;
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m76-local';
$normal=static fn($p)=>strtolower(str_replace('\\','/',(string)realpath($p)));
if($normal(dirname(__DIR__,2))!==$normal($root)||!is_dir($dir)||is_link($dir)||file_exists($dir.'/'.$receiptName)||is_link($dir.'/'.$receiptName)){
    throw new RuntimeException('M76 actual served/private scope differs or output already exists.');
}
require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler($failure);
use App\Services\M76ContentPatch as Patch;
use App\Services\M76LocalTargetGuard as Guard;
use App\Support\M76PronounRelations as Package;
use App\Support\M26DetailPackage as Source;
$digest=static fn(array $v)=>App\Services\M26ContentPatch::digest($v);
$pins=[__FILE__=>hash_file('sha256',__FILE__)];
$hash=static function(string $file):string{if(!is_file($file)||is_link($file)){throw new RuntimeException('M76 evidence absent or linked.');}return hash_file('sha256',$file);};
$read=static function(string $name,?string $sha=null)use($dir,$hash,&$pins):array{
    if(preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$name)!==1){throw new RuntimeException('M76 direct private JSON basename required.');}
    $p=$dir.'/'.$name;$actual=$hash($p);if($sha!==null&&!hash_equals($sha,$actual)){throw new RuntimeException('M76 exact evidence bytes differ.');}
    $pins[$p]=$actual;return json_decode(file_get_contents($p),true,flags:JSON_THROW_ON_ERROR);
};
$same=static function(array $a,array $b,string $reason):void{ksort($a);ksort($b);if($a!==$b){throw new RuntimeException($reason);}};
$before=$read(Patch::BEFORE_FILE,Patch::BEFORE_SHA);$consumers=$read(Patch::CONSUMERS_FILE,Patch::CONSUMERS_SHA);
$plan=$read($planName);$backup=$read($backupName,$backupSha);$unsigned=$plan;unset($unsigned['sha256']);
if(($plan['sha256']??null)!==$planDigest||$digest($unsigned)!==$planDigest||$backup!==$plan
    ||file_get_contents($dir.'/'.$planName)!==file_get_contents($dir.'/'.$backupName)
    ||($plan['patch']??null)!=='m76-relative-each-reciprocal-standard-view-v1'||($plan['version']??null)!==1
    ||($plan['names']??null)!==Package::OWNERS||($plan['state']??null)!=='before'
    ||($plan['inserts']??null)!==[]||($plan['deletes']??[])!==[]
    ||Package::PAGE_IDS!==[129,121,127]||Package::LEVELS!==[null,null,null]||Package::CONTINUING_COUNTS!==[12,12,12]
    ||($before['base_sha']??null)!==Package::BASE_SHA||($before['served_root']??null)!==$root
    ||($before['source_worktree']??null)!==Patch::SOURCE_WORKTREE||array_keys($before['pages']??[])!==Package::OWNERS
    ||($before['sql_before']??null)!==($before['sql_after_http']??null)||count($before['sql_before']??[])!==46
    ||count($before['visual_hashes']??[])!==326||count($before['frozen_hashes']??[])!==2642
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']
    ||($consumers['all_tables_after_http']??null)!==$before['sql_before']||($consumers['unchanged_during_http']??null)!==true){
    throw new RuntimeException('M76 reviewed backup/preview/independent BEFORE scope differs.');
}
App\Services\M51ContentPatch::assertConsumerCapture($before,$consumers);
[$sourceBefore,$patch,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$patch,$master,$contract);
$expectedSources=array_values(array_unique([...Patch::sourcePaths($patch,$contract,$root),
    'private:'.Patch::BEFORE_FILE,'private:'.Patch::CONSUMERS_FILE,'private:'.Patch::SYNC_RECEIPT]));
sort($expectedSources);$actualSources=array_keys($plan['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M76 complete source/evidence list differs.');}
foreach($plan['sources'] as $path=>$sha){
    $file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M76 preview source/evidence drift.');}$pins[$file]=$sha;
}
foreach([$before['physical_hashes'],$before['visual_hashes'],$before['frozen_hashes']] as $n=>$records){foreach($records as $path=>$sha){
    $file=$n===0?$path:$root.'/'.$path;if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M76 physical/visual/frozen bytes changed.');}$pins[$file]=$sha;
}}
$sync=$read(Patch::SYNC_RECEIPT);$paths=array_column($sync['files'],'path');sort($paths);
if($paths!==Patch::syncPaths($patch,$contract)||($sync['base_sha']??null)!==Package::BASE_SHA
    ||($sync['served_root']??null)!==$root||($sync['source_worktree']??null)!==Patch::SOURCE_WORKTREE
    ||($sync['visual_before']??null)!==['file'=>Patch::BEFORE_FILE,'sha256'=>Patch::BEFORE_SHA]){throw new RuntimeException('M76 source-sync scope differs.');}
foreach($sync['files'] as $r){foreach([$root.'/'.$r['path']=>$r['root_after_sha256'],Patch::SOURCE_WORKTREE.'/'.$r['path']=>$r['worktree_sha256']] as $file=>$sha){
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M76 served/worktree source sync drift.');}$pins[$file]=$sha;
}}
$oldPages=[];$newPages=[];$oldUk=[];$newUk=[];$foreign=[];$changes=[];$metadata=[];$bodyKinds=['subtitle'=>0,'teaching'=>0,'practice'=>0];$practices=[];
foreach($patch['targets'] as $i=>$target){
    $owner=Package::OWNERS[$i];$captured=$before['pages'][$owner];$raw=$sourceBefore['targets'][$i]['before'];
    Patch::assertDefinitionScope($raw,$target['after'],$target,$i);
    if($before['definitions'][$owner]['definition']!==$raw
        ||json_decode(file_get_contents($root.'/'.$target['path']),true,flags:JSON_THROW_ON_ERROR)!==$target['after']){throw new RuntimeException('M76 active source/independent BEFORE differs.');}
    foreach(['page','category_ancestry','blocks'] as $field){if(($plan['pages'][$owner][$field]??null)!==$captured[$field]){throw new RuntimeException('M76 plan original page/block inventory differs.');}}
    $p=$captured['page'];if($p['id']!==Package::PAGE_IDS[$i]||$p['seeder']!==$owner||$p['page_category_id']!==20||$p['title']!==$raw['page']['title']||$p['text']!==$raw['page']['subtitle_text']){throw new RuntimeException('M76 independent Page identity differs.');}
    $oldPages[$p['id']]=$p;$newPages[$p['id']]=array_replace($p,['text'=>$target['after']['page']['subtitle_text']]);
    if($p!==$newPages[$p['id']]){$changes['pages:'.$p['id']]=['table'=>'pages','id'=>$p['id'],'uuid'=>$p['uuid']??null,'seeder'=>$owner,'before'=>['text'=>$p['text']],'after'=>['text'=>$newPages[$p['id']]['text']]];}
    $positions=[];
    foreach($captured['blocks'] as $row){
        if($row['locale']!=='uk'){$foreign[$row['id']]=$row;continue;}
        $pos=$row['sort_order'];if(!is_int($pos)||$pos<0||$pos>12||isset($positions[$pos])||isset($oldUk[$row['id']])
            ||$row['seeder']!==$owner||$row['page_id']!==$p['id']||$row['page_category_id']!==20||$row['level']!==null){throw new RuntimeException('M76 independent UK identity/order/NULL-level differs.');}
        $positions[$pos]=true;
        $oldConfig=$pos===0?['type'=>'subtitle','column'=>'header','body'=>$raw['page']['subtitle_html'],'uuid_key'=>$raw['page']['subtitle_uuid_key']??'subtitle']:$raw['page']['blocks'][$pos-1];
        $newConfig=$pos===0?array_replace($oldConfig,['body'=>$target['after']['page']['subtitle_html']]):$target['after']['page']['blocks'][$pos-1];
        if(Source::uuid($owner,$oldConfig,$pos)!==$row['uuid']||Source::uuid($owner,$newConfig,$pos)!==$row['uuid']){throw new RuntimeException('M76 exact continuing UUID differs.');}
        foreach(['type','column','heading','level','css_class','body'] as $field){if($row[$field]!==($oldConfig[$field]??null)){throw new RuntimeException('M76 independent source/SQL BEFORE differs: '.$field);}}
        $oldValues=[];$newValues=[];foreach(['body','type','heading'] as $field){$oldValues[$field]=$oldConfig[$field]??null;$newValues[$field]=$newConfig[$field]??null;}
        $oldUk[$row['id']]=$row;$newUk[$row['id']]=array_replace($row,$newValues);
        if($oldValues!==$newValues){$changes['text_blocks:'.$row['id']]=['table'=>'text_blocks','id'=>$row['id'],'uuid'=>$row['uuid'],'seeder'=>$owner,'before'=>$oldValues,'after'=>$newValues];}
        foreach(['type','heading'] as $field){if($oldValues[$field]!==$newValues[$field]){$metadata[]=['id'=>$row['id'],'uuid'=>$row['uuid'],'field'=>$field,'before'=>$oldValues[$field],'after'=>$newValues[$field]];}}
        if($oldValues['body']!==$newValues['body']){++$bodyKinds[$pos===0?'subtitle':($pos===Package::PRACTICE_SOURCE_INDICES[$i]+1?'practice':'teaching')];}
        if($pos===Package::PRACTICE_SOURCE_INDICES[$i]+1){
            if($row['id']!==[4140,8937,4086][$i]||$row['uuid']!==Package::PRACTICE_UUIDS[$i]||$newConfig['type']!=='practice-set'){throw new RuntimeException('M76 continuing practice binding differs.');}
            $practices[]=['owner'=>$owner,'id'=>$row['id'],'uuid'=>$row['uuid'],'source_index'=>$pos-1,'level'=>null,'inserted'=>false];
        }
    }
    $keys=array_keys($positions);sort($keys);if($keys!==range(0,12)){throw new RuntimeException('M76 complete continuing position vector differs.');}
}
if(count($oldUk)!==39||count($oldPages)!==3||count($foreign)!==56||count($metadata)!==14||count(array_unique(array_column($metadata,'id')))!==10
    ||count($changes)!==count($plan['updates'])){throw new RuntimeException('M76 exact row/field transition inventory differs.');}
$seen=[];foreach($plan['updates'] as $change){$key=$change['table'].':'.$change['id'];$expected=$changes[$key]??null;
    if($expected===null||isset($seen[$key])||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
        ||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$expected
        ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){throw new RuntimeException('M76 preview exact allowlist differs or repeats.');}$seen[$key]=true;
}
$db=Illuminate\Support\Facades\DB::connection();Guard::assertConfiguredDatabase($db);$guard=new Guard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key){if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1){throw new RuntimeException('M76 physical MySQL path is not absolute local Windows.');}}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){throw new RuntimeException('M76 fresh local MySQL identity differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
$runtime=Guard::publicIdentity($db,$root.'/public');if($runtime!==$before['identity']){throw new RuntimeException('M76 actual runtime changed.');}
$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try{
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);
    if($tables!==array_keys($before['sql_before'])){throw new RuntimeException('M76 table inventory changed.');}
    $after=[];$normalized=[];$protected=[];$seenPages=[];$seenUk=[];$seenForeign=[];
    foreach($tables as $table){
        $schema=$db->getSchemaBuilder()->getColumns($table);if(isset($before['schema'][$table])&&$schema!==$before['schema'][$table]){throw new RuntimeException('M76 captured schema changed.');}
        $columns=array_column($schema,'name');$query=$db->table($table);foreach(in_array('id',$columns,true)?['id']:$columns as $column){$query->orderBy($column);}
        $h=hash_init('sha256');$oldHash=hash_init('sha256');$protectedHash=hash_init('sha256');$count=$protectedCount=0;
        foreach($query->cursor() as $record){
            $row=(array)$record;$id=$row['id']??null;hash_update($h,$digest($row)."\n");++$count;$old=$row;$skip=false;
            if($table==='pages'&&isset($oldPages[$id])){$same($row,$newPages[$id],'M76 target Page metadata/text/timestamps differ.');$old=$oldPages[$id];$seenPages[$id]=true;}
            if($table==='text_blocks'&&isset($oldUk[$id])){$same($row,$newUk[$id],'M76 continuing UK fields/timestamps differ.');$old=$oldUk[$id];$seenUk[$id]=true;$skip=true;}
            if($table==='text_blocks'&&isset($foreign[$id])){$same($row,$foreign[$id],'M76 non-UK original row changed.');$seenForeign[$id]=true;}
            hash_update($oldHash,$digest($old)."\n");if($skip){continue;}
            if($table==='pages'&&isset($oldPages[$id])){unset($row['text']);}hash_update($protectedHash,$digest($row)."\n");++$protectedCount;
        }
        $after[$table]=['count'=>$count,'sha256'=>hash_final($h)];$normalized[$table]=['count'=>$count,'sha256'=>hash_final($oldHash)];
        $protected[$table]=['count'=>$protectedCount,'sha256'=>hash_final($protectedHash)];
    }
    if(count($seenPages)!==3||count($seenUk)!==39||count($seenForeign)!==56||$normalized!==$before['sql_before']||$protected!==$plan['protected']){
        throw new RuntimeException('M76 full BEFORE reconstruction/protected fingerprint differs; no acceptance.');
    }
}finally{$db->rollBack();}
foreach($pins as $file=>$sha){if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M76 evidence/source changed during audit.');}}
$result=['checked_at'=>gmdate('c'),'runtime'=>$runtime,'plan_file'=>$planName,'plan_digest'=>$planDigest,
    'independent_before_sha256'=>Patch::BEFORE_SHA,'old_consumers_sha256'=>Patch::CONSUMERS_SHA,
    'backup_file'=>$backupName,'backup_sha256'=>$backupSha,'backup_equals_reviewed_preview'=>true,
    'page_text_updates'=>count(array_filter($changes,static fn($r)=>$r['table']==='pages')),
    'body_updates'=>$bodyKinds,'metadata_transitions'=>$metadata,'update_operations'=>count($changes),'distinct_changed_rows'=>count($changes),
    'insert_operations'=>0,'deleted_rows'=>0,'continuing_uk_rows'=>39,'unchanged_non_uk_rows'=>56,'continuing_practices'=>$practices,
    'all_old_rows_and_timestamps_exact'=>true,'independent_before_projection_exact'=>true,'reviewed_protected_projection_exact'=>true,
    'sql_after'=>$after,'normalized_before'=>$normalized,'protected'=>$protected,'sql_tables'=>count($after),
    'visual_files_unchanged'=>326,'frozen_files_unchanged'=>2642,'source_sync_unchanged'=>true,'all_preview_sources_exact'=>true,
    'db_writes'=>0,'http_requests'=>0,'browser_acceptance'=>'Separate actual-page evidence, not claimed by this SQL gate.'];
$bytes=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($dir.'/'.$receiptName,'x');if(!$h){throw new RuntimeException('M76 exclusive final receipt unavailable.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M76 final receipt incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>$receiptName,'sha256'=>hash('sha256',$bytes),'tables'=>count($after),'updates'=>count($changes),
    'metadata_fields'=>count($metadata),'all_old_rows_and_timestamps_exact'=>true,'db_writes'=>0,'http_requests'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
