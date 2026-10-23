<?php

/** Independent SELECT-only M80 reconstruction, using the immutable M53 table scanner. */
require __DIR__.'/frozen-readonly-profile.php';
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==6
    ||preg_match('/^preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1
    ||preg_match('/^backup-v[1-9][0-9]*\.json$/D',$argv[3])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[5])!==1){
    throw new RuntimeException('M80 reviewed preview/digest, exclusive backup/raw SHA, fresh final basename required.');
}
$backupName=$argv[3];$backupSha=$argv[4];$private='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m80-local';
foreach([$argv[1],$backupName] as $name){if(!is_file($private.'/'.$name)||is_link($private.'/'.$name)){throw new RuntimeException('M80 reviewed preview/backup absent or linked.');}}
if(!hash_equals($backupSha,hash_file('sha256',$private.'/'.$backupName))
    ||file_get_contents($private.'/'.$argv[1])!==file_get_contents($private.'/'.$backupName)){
    throw new RuntimeException('M80 exclusive backup bytes are not the exact reviewed preview.');
}
$argv=[$argv[0],$argv[1],$argv[2],$argv[5]];
$inventory=<<<'PHP'
    $pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $nonUk = 0;
    $expectedChanges=[];$metadata=[];$bodyKinds=['subtitle'=>0,'teaching'=>0];
    $finiteBoxes=[
        4785=>['e434d6fb-4824-54e5-a467-38f3ac7dd22e','usage-panels','1. Як розпізнати потрібну перебудову'],
        4786=>['f44f992a-39db-52db-a677-98df384f07cd','comparison-table','2. Основні шаблони трансформації'],
        4701=>['a76800f3-8baf-5a27-98b8-358bb6e01eda','usage-panels','1. Як розпізнати потрібну перебудову'],
        4702=>['427c0ea1-4b51-5033-ab92-79b6c8cee126','comparison-table','2. Основні шаблони трансформації'],
        4722=>['7b61a8f4-320c-5bb6-a67f-b050d4c97092','usage-panels','1. Як розпізнати потрібну перебудову'],
        4723=>['2245b572-d144-5b93-9d9c-dabc3f02d6c2','comparison-table','2. Основні шаблони трансформації'],
        4764=>['3bbe3eae-de1f-546b-aa69-c2df4e53b382','usage-panels','1. Як розпізнати потрібну перебудову'],
        4765=>['e9cbd4b7-7a63-51fa-9b32-a60dad35960b','comparison-table','2. Основні шаблони трансформації'],
    ];
    foreach ($patch['targets'] as $i => $target) {
        $owner = $target['identity']; $captured = $before['pages'][$owner]; $oldPage = $captured['page'];
        if($oldPage['id']!==App\Support\M80NegativesTransformations::PAGE_IDS[$i]
            ||$oldPage['seeder']!==$owner||$oldPage['page_category_id']!==App\Support\M80NegativesTransformations::CATEGORY_IDS[$i]){
            throw new RuntimeException('M80 independently captured Page identity differs.');
        }
        foreach(['page','category_ancestry','blocks'] as $field){if(($preview['pages'][$owner][$field]??null)!==$captured[$field]){throw new RuntimeException('M80 preview original inventory differs.');}}
        App\Services\M80ContentPatch::assertDefinitionScope($sourceBefore['targets'][$i]['before'],$target['after'],$target,$i);
        $pageBefore[$oldPage['id']] = $oldPage; $pageAfter[$oldPage['id']] = array_replace($oldPage, ['text' => $target['after']['page']['subtitle_text']]);
        if($oldPage['text']!==$pageAfter[$oldPage['id']]['text']){$expectedChanges['pages:'.$oldPage['id']]=[
            'table'=>'pages','id'=>$oldPage['id'],'uuid'=>$oldPage['uuid']??null,'seeder'=>$owner,
            'before'=>['text'=>$oldPage['text']],'after'=>['text'=>$pageAfter[$oldPage['id']]['text']]];}
        $positions=[];
        foreach ($captured['blocks'] as $old) {
            if ($old['locale'] !== 'uk') { ++$nonUk; continue; }
            $position = $old['sort_order'];
            if(!is_int($position)||$position<0||$position>App\Support\M80NegativesTransformations::CONTINUING_COUNTS[$i]
                ||isset($positions[$position])||isset($ukBefore[$old['id']])||$old['seeder']!==$owner||$old['page_id']!==$oldPage['id']){
                throw new RuntimeException('M80 continuing owner/order inventory differs.');
            }
            $positions[$position]=true;
            $config=$position===0?['body'=>$target['after']['page']['subtitle_html'],'type'=>$old['type'],'heading'=>$old['heading']]
                :$target['after']['page']['blocks'][$position-1];
            $values=['body'=>$config['body'],'type'=>$config['type'],'heading'=>$config['heading']??null];
            if(!is_string($values['body'])){throw new RuntimeException('M80 continuing body missing.');}
            foreach(['column','level','css_class'] as $field){if($position!==0&&($config[$field]??null)!==$old[$field]){throw new RuntimeException('M80 continuing immutable metadata differs: '.$field);}}
            if(isset($finiteBoxes[$old['id']])){
                [$uuid,$type,$heading]=$finiteBoxes[$old['id']];
                if($old['uuid']!==$uuid||$old['type']!=='box'||$old['heading']!==$heading||$old['level']!==null
                    ||$values['type']!==$type||$values['heading']!==null){throw new RuntimeException('M80 exact eight box transitions differ.');}
                foreach(['type','heading'] as $field){$metadata[]=['id'=>$old['id'],'uuid'=>$uuid,'field'=>$field,'before'=>$old[$field],'after'=>$values[$field]];}
            }elseif($values['type']!==$old['type']||$values['heading']!==$old['heading']){throw new RuntimeException('M80 unapproved continuing type/heading transition.');}
            $oldValues=array_intersect_key($old,array_flip(['body','type','heading']));
            $oldValues=['body'=>$oldValues['body'],'type'=>$oldValues['type'],'heading'=>$oldValues['heading']];
            $ukBefore[$old['id']] = $old; $ukAfter[$old['id']] = array_replace($old,$values);
            if($oldValues!==$values){$expectedChanges['text_blocks:'.$old['id']]=['table'=>'text_blocks','id'=>$old['id'],'uuid'=>$old['uuid'],
                'seeder'=>$owner,'before'=>$oldValues,'after'=>$values];}
            if($values['body']!==$old['body']){++$bodyKinds[$position===0?'subtitle':'teaching'];}
        }
        $actualPositions=array_keys($positions);sort($actualPositions);
        if($actualPositions!==range(0,App\Support\M80NegativesTransformations::CONTINUING_COUNTS[$i])){throw new RuntimeException('M80 continuing position vector differs.');}
    }
    if(count($pageBefore)!==6||count($ukBefore)!==40||$nonUk!==56||count($metadata)!==16
        ||count($preview['updates'])!==count($expectedChanges)||count($preview['inserts']??[])!==6||($preview['deletes']??[])!==[]
        ||isset($preview['question_supplement'])){throw new RuntimeException('M80 exact continuing/insert/metadata/no-question scope differs.');}
    $seenChanges=[];
    foreach($preview['updates'] as $change){$key=$change['table'].':'.$change['id'];$expected=$expectedChanges[$key]??null;
        if($expected===null||isset($seenChanges[$key])||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
            ||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$expected
            ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){
            throw new RuntimeException('M80 exact reviewed update inventory differs.');
        }$seenChanges[$key]=true;
    }
PHP;
$originalInventory=<<<'PHP'
    $pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $nonUk = 0;
    foreach ($patch['targets'] as $i => $target) {
        $owner = $target['identity']; $captured = $before['pages'][$owner]; $oldPage = $captured['page'];
        $pageBefore[$oldPage['id']] = $oldPage; $pageAfter[$oldPage['id']] = array_replace($oldPage, ['text' => $target['after']['page']['subtitle_text']]);
        foreach ($captured['blocks'] as $old) {
            if ($old['locale'] !== 'uk') { ++$nonUk; continue; }
            $position = (int) $old['sort_order']; $body = $position === 0 ? $target['after']['page']['subtitle_html']
                : ($target['after']['page']['blocks'][$position - 1]['body'] ?? null);
            if (!is_string($body) || isset($ukBefore[$old['id']])) { throw new RuntimeException('Independent UK inventory/order is incomplete or duplicated.'); }
            $ukBefore[$old['id']] = $old; $ukAfter[$old['id']] = array_replace($old, ['body' => $body]);
        }
    }
    if (count($pageBefore) !== count($patch['targets']) || count($preview['inserts'] ?? []) !== count($patch['targets'])) {
        throw new RuntimeException('Exact three-owner/native practice scope differs.');
    }
PHP;
$originalQuestions=<<<'PHP'
    // This method only SELECTs and validates existing frozen evidence. No writer or plan/apply entry is called.
    $service = new App\Services\M80ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm80QuestionState'))->invoke($service, 'after', $patch, false);
    if (($scope['evidence']['projection'] ?? null) !== ($preview['question_supplement']['evidence']['projection'] ?? null)
        || ($scope['evidence']['ids'] ?? null) !== ($preview['question_supplement']['evidence']['ids'] ?? null)) {
        throw new RuntimeException('Applied question projection differs from reviewed preview.');
    }
    $timestamp = $scope['actual']['questions'][0]['created_at'] ?? '';
    $newRows = App\Support\M51QuestionSupplement::materialize($scope['evidence']['projection'], $scope['evidence']['ids'], $timestamp);
    App\Support\M51QuestionSupplement::assertApplied($newRows, $scope['actual']);
    $approved = []; foreach ($newRows as $table => $rows) { $approved[$table] = array_column($rows, null, 'id'); }
PHP;
$extraSource=<<<'PHP'
$consumers=$load(App\Services\M80ContentPatch::CONSUMERS_FILE,App\Services\M80ContentPatch::CONSUMERS_SHA);
App\Services\M51ContentPatch::assertConsumerCapture($before,$consumers);
if(count($before['sql_before'])!==46||count($before['visual_hashes'])!==326||count($before['frozen_hashes'])!==2740
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']||($consumers['all_tables_after_http']??null)!==$before['sql_before']){
    throw new RuntimeException('M80 independent full-table/old-consumer baseline differs.');
}
$expectedSources=array_values(array_unique([...App\Services\M80ContentPatch::sourcePaths($patch,$contract,$root),
    'private:'.App\Services\M80ContentPatch::BEFORE_FILE,'private:'.App\Services\M80ContentPatch::CONSUMERS_FILE,'private:'.App\Services\M80ContentPatch::SYNC_RECEIPT]));
sort($expectedSources);$actualSources=array_keys($preview['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M80 full reviewed source/evidence list differs.');}
foreach ($preview['sources'] as $path => $sha) {
PHP;
$physical=<<<'PHP'
App\Services\M80LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.
$guard=new App\Services\M80LocalTargetGuard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key){if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1){throw new RuntimeException('M80 physical MySQL path is not local Windows.');}}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){throw new RuntimeException('M80 fresh physical MySQL identity differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
PHP;
$recheck=<<<'PHP'
} finally { $db->rollBack(); }
foreach($preview['sources'] as $path=>$sha){$file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M80 source/evidence changed during final audit.');}}
foreach([$before['physical_hashes'],$before['visual_hashes'],$before['frozen_hashes']] as $n=>$records){foreach($records as $path=>$sha){
    if(!hash_equals($sha,$hash($n===0?$path:$root.'/'.$path))){throw new RuntimeException('M80 physical/visual/frozen bytes changed during final audit.');}
}}
foreach($sync['files'] as $record){if($hash($root.'/'.$record['path'])!==$record['root_after_sha256']
    ||$hash(App\Services\M80ContentPatch::SOURCE_WORKTREE.'/'.$record['path'])!==$record['worktree_sha256']){throw new RuntimeException('M80 source-sync changed during final audit.');}}
if(!hash_equals($profileContext['backupSha'],$hash($dir.'/'.$profileContext['backupName']))
    ||file_get_contents($dir.'/'.$previewName)!==file_get_contents($dir.'/'.$profileContext['backupName'])){throw new RuntimeException('M80 exclusive backup changed during audit.');}
$receipt =
PHP;
$insertCheck=<<<'PHP'
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        if($position!==App\Support\M80NegativesTransformations::PRACTICE_SOURCE_INDICES[$i]+1
            ||$source['type']!=='practice-set'||($source['level']??null)!==App\Support\M80NegativesTransformations::LEVELS[$i]
            ||$source['column']!=='left'||isset($insertOwnerSeen[$target['identity']])){throw new RuntimeException('M80 six appended practice bindings differ.');}
        $insertOwnerSeen[$target['identity']]=true;$page=$before['pages'][$target['identity']]['page'];
        $fields=['uuid'=>$uuid,'page_id'=>$page['id'],'page_category_id'=>$page['page_category_id'],'locale'=>'uk',
            'type'=>$source['type'],'column'=>$source['column'],'heading'=>$source['heading']??null,'css_class'=>$source['css_class']??null,
            'sort_order'=>$position,'body'=>$source['body'],'level'=>$source['level'],'seeder'=>$target['identity']];
        $same($insert['fields'],$fields,'M80 reviewed practice insert does not exactly match frozen source fields.');
PHP;
runFrozenReadOnlyProfile('m53-postconditions.php','8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',[
    ['M53PassiveConstructionsPackage','M80NegativesTransformations',7],['M53','M80',15],['m53','m80',4],
    [$originalInventory,$inventory,1],[$originalQuestions,"    if(array_keys(\$insertOwnerSeen)!==App\\Support\\M80NegativesTransformations::OWNERS){throw new RuntimeException('M80 exact six inserted owners/order differs.');}\n    \$newRows=[]; \$approved=[]; // No question-family insertion or omission is authorized for M80.",1],
    ['    $newPractices = []; $practiceFacts = [];','    $newPractices = []; $practiceFacts = []; $insertOwnerSeen=[];',1],
    ['        $uuid = App\\Support\\M26DetailPackage::uuid($target[\'identity\'], $source, $position);',$insertCheck,1],
    ["\$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);","if(isset(\$before['schema'][\$table])&&\$db->getSchemaBuilder()->getColumns(\$table)!==\$before['schema'][\$table]){throw new RuntimeException('M80 captured SQL schema changed.');}\n        \$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);",1],
    ['foreach ($preview[\'sources\'] as $path => $sha) {',$extraSource,1],
    ['App\\Services\\M80LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.',$physical,1],
    ["'question_plan_sha256' => App\\Services\\M80ContentPatch::QUESTION_PLAN_SHA,","'backup_file'=>\$profileContext['backupName'],'backup_sha256'=>\$profileContext['backupSha'],'backup_equals_reviewed_preview'=>true,\n    'metadata_transitions'=>\$metadata,'update_operations'=>count(\$expectedChanges),'body_updates'=>\$bodyKinds,'question_family_writes'=>0,",1],
    ["} finally { \$db->rollBack(); }\n\$receipt =",$recheck,1],
],['backupName'=>$backupName,'backupSha'=>$backupSha]);
