<?php

/** Independent SELECT-only successor reconstruction. Never calls a writer/support transition or changes SQL. */
$failure=static function(Throwable $e):void {
    fwrite(STDERR,$e instanceof RuntimeException&&!$e instanceof PDOException?'M79 criteria audit: '.$e->getMessage()."\n":"M79 criteria audit incomplete; no receipt. No SQL writes.\n"); exit(1);
};
set_exception_handler($failure);
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==6
    ||array_filter([$argv[1],$argv[3],$argv[5]],static fn($s)=>preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$s)!==1)
    ||array_filter([$argv[2],$argv[4]],static fn($s)=>preg_match('/^[a-f0-9]{64}$/D',$s)!==1)){
    throw new RuntimeException('M79 criteria requires reviewed preview/digest, backup/raw SHA, and exclusive final basename.');
}
[$script,$planName,$planDigest,$backupName,$backupSha,$receiptName]=$argv;
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m79-local';
$normal=static fn($p)=>strtolower(str_replace('\\','/',(string)realpath($p)));
if($normal(dirname(__DIR__,2))!==$normal($root)||!is_dir($dir)||is_link($dir)||file_exists($dir.'/'.$receiptName)||is_link($dir.'/'.$receiptName)){
    throw new RuntimeException('M79 criteria actual served/private scope differs or receipt exists.');
}
require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();set_exception_handler($failure);
$pins=[__FILE__=>hash_file('sha256',__FILE__)];
$hash=static function($path){if(!is_file($path)||is_link($path)){throw new RuntimeException('M79 criteria audit source/evidence absent or linked.');}return hash_file('sha256',$path);};
$read=static function($path,$sha=null)use($hash,&$pins){$actual=$hash($path);if($sha!==null&&!hash_equals($sha,$actual)){throw new RuntimeException('M79 criteria audit exact source/evidence SHA differs.');}$pins[$path]=$actual;return json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);};
$baselineSha='43b480a36883d551a6b5f05320c228f1fed8e79584a6d5b7a0eb22fadb0d5b09';
$baseline=$read($dir.'/m79-practice-criteria-before-v1.json',$baselineSha);
$plan=$read($dir.'/'.$planName);$backup=$read($dir.'/'.$backupName,$backupSha);$unsigned=$plan;unset($unsigned['sha256']);
$digest=static fn($v)=>App\Services\M26ContentPatch::digest($v);
if(($plan['sha256']??null)!==$planDigest||$digest($unsigned)!==$planDigest||$backup!==$plan
    ||file_get_contents($dir.'/'.$planName)!==file_get_contents($dir.'/'.$backupName)
    ||($plan['patch']??null)!=='m79-practice-visible-criteria-v1.0.1'||($plan['state']??null)!=='before'
    ||($plan['version']??null)!==1||count($plan['updates']??[])!==2||($plan['inserts']??null)!==[]
    ||($baseline['row_ids']??null)!==[4243,4372]||count($baseline['sql_before']??[])!==46
    ||count($baseline['schema']??[])!==46||($baseline['db_writes']??null)!==0||($baseline['http_requests']??null)!==0
    ||($baseline['original_postconditions_sha256']??null)!=='c9c8f0d1b42d5a0158555ef29f3843c66a448cbcacb963348f4f46c1e5c5fb67'){
    throw new RuntimeException('M79 criteria reviewed preview/backup/independent current-v1 scope differs.');
}
$old=$read($root.'/database/content-patches/m79-question-types-completion.v1.json','0e0b1406a376e392c4b12028e589ec099e4a675599b6907a873b738474e51d11');
$next=$read($root.'/database/content-patches/m79-question-types-completion.v1.0.1.json','9af348eae92ac0cb361f5a9da2c407275037cfb1fc1673e84486abef31d7396c');
$ledger=$read($root.'/docs/content/m79-practice-criteria-revision.v1.0.1.json','333f101e6fb32c122d5d20ffbe67f4482f43d157b4d8b163cc7222d40b4abfc5');
$original=$next;$expected=[];$labels=0;
foreach([0,2] as $n=>$i){
    $row=$baseline['rows'][$n];$a=$old['targets'][$i]['after']['page']['blocks'][11]['body'];$b=$next['targets'][$i]['after']['page']['blocks'][11]['body'];
    $data=json_decode($a,true,flags:JSON_THROW_ON_ERROR);$successor=json_decode($b,true,flags:JSON_THROW_ON_ERROR);
    if(count($data['selects']??[])!==3){throw new RuntimeException('M79 criteria independent exact select count differs.');}
    foreach($data['selects'] as &$task){$task['label']=$task['prompt'].' '.$task['label'];++$labels;}unset($task);
    if($data!==$successor||$row['body']!==$a||$row['id']!==[4243,4372][$n]||$row['page_id']!==[132,136][$n]
        ||$row['seeder']!==$old['targets'][$i]['identity']||$row['locale']!=='uk'||$row['type']!=='practice-set'
        ||$row['column']!=='left'||$row['sort_order']!==12||$row['level']!==['A2','B1'][$n]){throw new RuntimeException('M79 criteria independently derived exact label/body/owner scope differs.');}
    $original['targets'][$i]['after']['page']['blocks'][11]['body']=$a;
    $expected[$row['id']]=['before'=>$row,'after'=>array_replace($row,['body'=>$b])];
    $change=['table'=>'text_blocks','id'=>$row['id'],'uuid'=>$row['uuid'],'seeder'=>$row['seeder'],'before'=>['body'=>$a],'after'=>['body'=>$b],
        'before_sha256'=>$digest(['body'=>$a]),'after_sha256'=>$digest(['body'=>$b])];
    if($plan['updates'][$n]!==$change){throw new RuntimeException('M79 criteria exact reviewed two-row update allowlist differs.');}
}
if($original!==$old||$labels!==6||array_keys($expected)!==[4243,4372]){throw new RuntimeException('M79 criteria modifies another learning field.');}
foreach($ledger['predecessor_pins'] as $path=>$sha){$read($root.'/'.$path,$sha);}
foreach($ledger['active_artifacts'] as $path=>$entry){if($hash($root.'/'.$path)!==$entry['sha256']||filesize($root.'/'.$path)!==$entry['bytes']){throw new RuntimeException('M79 criteria successor artifact drift.');}$pins[$root.'/'.$path]=$entry['sha256'];}
foreach($plan['sources'] as $path=>$sha){$file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M79 criteria reviewed source/evidence drift.');}$pins[$file]=$sha;}
$db=Illuminate\Support\Facades\DB::connection();App\Services\M79LocalTargetGuard::assertConfiguredDatabase($db);
$runtime=App\Services\M79LocalTargetGuard::publicIdentity($db,$root.'/public');
if($runtime!==$baseline['identity']){throw new RuntimeException('M79 criteria actual local runtime differs from independent current-v1 capture.');}
$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try{
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);
    if($tables!==array_keys($baseline['sql_before'])){throw new RuntimeException('M79 criteria complete 46-table inventory differs.');}
    $after=[];$normalized=[];$seen=[];$rowHashes=[];
    foreach($tables as $table){
        $schema=$db->getSchemaBuilder()->getColumns($table);
        if($schema!==$baseline['schema'][$table]){throw new RuntimeException('M79 criteria independently captured schema drift.');}
        $columns=array_column($schema,'name');$query=$db->table($table);foreach(in_array('id',$columns,true)?['id']:$columns as $column){$query->orderBy($column);}
        $current=hash_init('sha256');$oldHash=hash_init('sha256');$count=0;
        foreach($query->cursor() as $record){
            $row=(array)$record;hash_update($current,$digest($row)."\n");++$count;$id=$row['id']??null;
            if($table==='text_blocks'&&isset($expected[$id])){
                $a=$row;$b=$expected[$id]['after'];ksort($a);ksort($b);
                if($a!==$b||isset($seen[$id])){throw new RuntimeException('M79 criteria complete successor target/timestamps differs or repeats.');}
                $rowHashes[$id]=['before_sha256'=>$digest($expected[$id]['before']),'after_sha256'=>$digest($row),'non_body_fields_and_timestamps_exact'=>true];
                $row=$expected[$id]['before'];$seen[$id]=true;
            }
            hash_update($oldHash,$digest($row)."\n");
        }
        $after[$table]=['count'=>$count,'sha256'=>hash_final($current)];$normalized[$table]=['count'=>$count,'sha256'=>hash_final($oldHash)];
    }
    if(count($seen)!==2||$normalized!==$baseline['sql_before']||$plan['protected']!==$baseline['sql_before']){throw new RuntimeException('M79 criteria all46tables fail independent two-body normalization.');}
    foreach($after as $table=>$record){if($table!=='text_blocks'&&$record!==$baseline['sql_before'][$table]){throw new RuntimeException('M79 criteria excluded questions/progress/other table changed.');}}
}finally{$db->rollBack();}
foreach($pins as $path=>$sha){if(!hash_equals($sha,$hash($path))){throw new RuntimeException('M79 criteria source/evidence drifted during final audit.');}}
$result=['schema_version'=>1,'kind'=>'M79_practice_criteria_postconditions','checked_at'=>gmdate('c'),'target'=>'gramlyze.loc',
    'runtime'=>$runtime,'independent_before_sha256'=>$baselineSha,'plan_file'=>$planName,'plan_digest'=>$planDigest,
    'backup_file'=>$backupName,'backup_sha256'=>$backupSha,'backup_equals_reviewed_preview'=>true,'exact_label_prefixes'=>6,
    'updated_bodies'=>2,'inserted_rows'=>0,'deleted_rows'=>0,'row_hashes'=>$rowHashes,'sql_tables'=>46,'sql_after'=>$after,
    'normalized_before'=>$normalized,'all_other_fields_and_timestamps_exact'=>true,'all_questions_progress_and_other_rows_exact'=>true,
    'db_writes'=>0,'http_requests'=>0,'browser_acceptance'=>'Separate actual-page acceptance; not claimed by this read-only SQL audit.'];
$json=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($dir.'/'.$receiptName,'x');if(!$h){throw new RuntimeException('M79 criteria exclusive final receipt unavailable.');}
try{if(fwrite($h,$json)!==strlen($json)||!fflush($h)){throw new RuntimeException('M79 criteria final receipt incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>$receiptName,'sha256'=>hash('sha256',$json),'tables'=>46,'body_updates'=>2,'label_prefixes'=>6,
    'all_other_fields_and_timestamps_exact'=>true,'db_writes'=>0,'http_requests'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
