<?php

/**
 * Independent SELECT-only M81 reconstruction via the immutable M53 scanner.
 * Args: preview, reviewed digest, backup, backup raw SHA, fresh final receipt.
 * Optionally append prior final receipt + raw SHA for an exact post-UI repeat.
 * Never calls a writer, apply/restore, seeder or HTTP endpoint.
 */
require __DIR__.'/frozen-readonly-profile.php';
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array(count($argv),[6,8],true)
    ||preg_match('/^preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1
    ||preg_match('/^backup-v[1-9][0-9]*\.json$/D',$argv[3])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[5])!==1){
    throw new RuntimeException('M81 reviewed preview/digest, exclusive backup/raw SHA, fresh final basename required.');
}
$backupName=$argv[3];$backupSha=$argv[4];$private='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m81-local';
$priorName=$argv[6]??null;$priorSha=$argv[7]??null;
if(($priorName!==null||$priorSha!==null)&&(count($argv)!==8
    ||!is_string($priorName)||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$priorName)!==1
    ||!is_string($priorSha)||preg_match('/^[a-f0-9]{64}$/D',$priorSha)!==1
    ||$priorName===$argv[5]||!is_file($private.'/'.$priorName)||is_link($private.'/'.$priorName)
    ||!hash_equals($priorSha,hash_file('sha256',$private.'/'.$priorName)))){
    throw new RuntimeException('M81 optional prior final receipt name/raw SHA differs; no repeat claim.');
}
foreach([$argv[1],$backupName] as $name){if(!is_file($private.'/'.$name)||is_link($private.'/'.$name)){throw new RuntimeException('M81 reviewed preview/backup absent or linked.');}}
if(!hash_equals($backupSha,hash_file('sha256',$private.'/'.$backupName))
    ||file_get_contents($private.'/'.$argv[1])!==file_get_contents($private.'/'.$backupName)){
    throw new RuntimeException('M81 exclusive backup bytes are not the exact reviewed preview.');
}
$argv=[$argv[0],$argv[1],$argv[2],$argv[5]];
$inventory=<<<'PHP'
    $pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $nonUk = 0;
    $expectedChanges=[];$metadata=[];$bodyKinds=['subtitle'=>0,'teaching'=>0];
    $finiteBoxes=[
        4680=>['6c626976-dd3b-54ce-8d51-ab5c18583442','usage-panels','1. Як розпізнати потрібну перебудову'],
        4681=>['a818e4e0-cb37-5e1f-9509-bb32eaf8130e','comparison-table','2. Основні шаблони трансформації'],
        4743=>['52108efd-0bbc-5505-9def-93e98f5a34c9','usage-panels','1. Як розпізнати потрібну перебудову'],
        4744=>['93247bc3-bb42-518e-8000-465599e15813','comparison-table','2. Основні шаблони трансформації'],
        4659=>['b2c8e1a8-5eb0-5882-a308-3599392d7aae','usage-panels','1. Як розпізнати потрібну перебудову'],
        4660=>['96a41ce8-8cbb-59a3-abaf-065843d21ebb','comparison-table','2. Основні шаблони трансформації'],
        4806=>['782663f2-26dd-579b-be51-560b6d068356','usage-panels','1. Як розпізнати потрібну перебудову'],
        4807=>['b2255742-94da-5d19-b55b-ae8e4a7eb3ca','comparison-table','2. Основні шаблони трансформації'],
        1630=>['3f5f4b46-57d8-598a-a0db-9cf36b7ae3f3','usage-panels','1. Де плутаються найчастіше'],
        1631=>['c4602dcf-269e-55a3-a272-f4cf727003c2','usage-panels','2. Як працює правильний вибір'],
        1735=>['0bc96eb2-2788-5e79-9da5-ead51576ff34','usage-panels','1. Де плутаються найчастіше'],
        1736=>['35a24e4a-d4e1-5c39-8ba3-5ae0a62e8017','usage-panels','2. Як працює правильний шаблон'],
    ];
    foreach ($patch['targets'] as $i => $target) {
        $owner = $target['identity']; $captured = $before['pages'][$owner]; $oldPage = $captured['page'];
        if($oldPage['id']!==App\Support\M81TransformationsCommonMistakes::PAGE_IDS[$i]
            ||$oldPage['seeder']!==$owner||$oldPage['page_category_id']!==App\Support\M81TransformationsCommonMistakes::CATEGORY_IDS[$i]){
            throw new RuntimeException('M81 independently captured Page identity differs.');
        }
        foreach(['page','category_ancestry','blocks'] as $field){if(($preview['pages'][$owner][$field]??null)!==$captured[$field]){throw new RuntimeException('M81 preview original inventory differs.');}}
        App\Services\M81ContentPatch::assertDefinitionScope($sourceBefore['targets'][$i]['before'],$target['after'],$target,$i);
        $pageBefore[$oldPage['id']] = $oldPage; $pageAfter[$oldPage['id']] = array_replace($oldPage, ['text' => $target['after']['page']['subtitle_text']]);
        if($oldPage['text']!==$pageAfter[$oldPage['id']]['text']){$expectedChanges['pages:'.$oldPage['id']]=[
            'table'=>'pages','id'=>$oldPage['id'],'uuid'=>$oldPage['uuid']??null,'seeder'=>$owner,
            'before'=>['text'=>$oldPage['text']],'after'=>['text'=>$pageAfter[$oldPage['id']]['text']]];}
        $positions=[];
        foreach ($captured['blocks'] as $old) {
            if ($old['locale'] !== 'uk') { ++$nonUk; continue; }
            $position = $old['sort_order'];
            if(!is_int($position)||$position<0||$position>App\Support\M81TransformationsCommonMistakes::CONTINUING_COUNTS[$i]
                ||isset($positions[$position])||isset($ukBefore[$old['id']])||$old['seeder']!==$owner||$old['page_id']!==$oldPage['id']){
                throw new RuntimeException('M81 continuing owner/order inventory differs.');
            }
            $positions[$position]=true;
            $config=$position===0?['body'=>$target['after']['page']['subtitle_html'],'type'=>$old['type'],'heading'=>$old['heading']]
                :$target['after']['page']['blocks'][$position-1];
            $values=['body'=>$config['body'],'type'=>$config['type'],'heading'=>$config['heading']??null];
            if(!is_string($values['body'])){throw new RuntimeException('M81 continuing body missing.');}
            foreach(['column','level','css_class'] as $field){if($position!==0&&($config[$field]??null)!==$old[$field]){throw new RuntimeException('M81 continuing immutable metadata differs: '.$field);}}
            if(isset($finiteBoxes[$old['id']])){
                [$uuid,$type,$heading]=$finiteBoxes[$old['id']];
                if($old['uuid']!==$uuid||$old['type']!=='box'||$old['heading']!==$heading||$old['level']!==null
                    ||$values['type']!==$type||$values['heading']!==null){throw new RuntimeException('M81 exact twelve box transitions differ.');}
                foreach(['type','heading'] as $field){$metadata[]=['id'=>$old['id'],'uuid'=>$uuid,'field'=>$field,'before'=>$old[$field],'after'=>$values[$field]];}
            }elseif($values['type']!==$old['type']||$values['heading']!==$old['heading']){throw new RuntimeException('M81 unapproved continuing type/heading transition.');}
            $oldValues=array_intersect_key($old,array_flip(['body','type','heading']));
            $oldValues=['body'=>$oldValues['body'],'type'=>$oldValues['type'],'heading'=>$oldValues['heading']];
            $ukBefore[$old['id']] = $old; $ukAfter[$old['id']] = array_replace($old,$values);
            if($oldValues!==$values){$expectedChanges['text_blocks:'.$old['id']]=['table'=>'text_blocks','id'=>$old['id'],'uuid'=>$old['uuid'],
                'seeder'=>$owner,'before'=>$oldValues,'after'=>$values];}
            if($values['body']!==$old['body']){++$bodyKinds[$position===0?'subtitle':'teaching'];}
        }
        $actualPositions=array_keys($positions);sort($actualPositions);
        if($actualPositions!==range(0,App\Support\M81TransformationsCommonMistakes::CONTINUING_COUNTS[$i])){throw new RuntimeException('M81 continuing position vector differs.');}
    }
    $expectedUkRows=array_sum(array_map(static fn($n)=>$n+1,App\Support\M81TransformationsCommonMistakes::CONTINUING_COUNTS));
    if(count($pageBefore)!==count($patch['targets'])||$expectedUkRows!==42||count($ukBefore)!==$expectedUkRows||$nonUk!==84||count($metadata)!==2*count($finiteBoxes)
        ||count($preview['updates'])!==count($expectedChanges)||count($preview['inserts']??[])!==6||($preview['deletes']??[])!==[]
        ||isset($preview['question_supplement'])){throw new RuntimeException('M81 exact continuing/insert/metadata/no-question scope differs.');}
    $seenChanges=[];
    foreach($preview['updates'] as $change){$key=$change['table'].':'.$change['id'];$expected=$expectedChanges[$key]??null;
        if($expected===null||isset($seenChanges[$key])||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
            ||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$expected
            ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){
            throw new RuntimeException('M81 exact reviewed update inventory differs.');
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
    $service = new App\Services\M81ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm81QuestionState'))->invoke($service, 'after', $patch, false);
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
$consumers=$load(App\Services\M81ContentPatch::CONSUMERS_FILE,App\Services\M81ContentPatch::CONSUMERS_SHA);
App\Services\M51ContentPatch::assertConsumerCapture($before,$consumers);
if(count($before['sql_before'])!==46||count($before['visual_hashes'])!==326||count($before['frozen_hashes'])!==2763
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']||($consumers['all_tables_after_http']??null)!==$before['sql_before']){
    throw new RuntimeException('M81 independent full-table/old-consumer baseline differs.');
}
$lineageCheck=static function()use($root,$dir,$hash,$load,$normal):array{
    $captureSha='567b6efe48063a18ae1c6267b211bf387f02699ea263db910c8d6cb373430d54';
    $fixtureSha='63cd447c601642e04bbd918821487f7f1b96ccff5bc2e72e1d7e0d294d8da48f';
    $capture=$load('independent-source-before-v1.json',$captureSha);
    $wt=App\Services\M81ContentPatch::SOURCE_WORKTREE;$fixture=$wt.'/tests/Fixtures/m81-owned-source-hunks.json';
    if(!hash_equals($fixtureSha,$hash($fixture))){throw new RuntimeException('M81 independent source-hunk fixture bytes differ.');}
    $ledger=json_decode(file_get_contents($fixture),true,flags:JSON_THROW_ON_ERROR);
    if(($capture['base_sha']??null)!==App\Support\M81TransformationsCommonMistakes::BASE_SHA
        ||($capture['phase']??null)!=='before'||count($capture['files']??[])!==3197
        ||($ledger['base_sha']??null)!==App\Support\M81TransformationsCommonMistakes::BASE_SHA
        ||($ledger['independent_source_before_sha256']??null)!==$captureSha
        ||($ledger['independent_db_before_sha256']??null)!==App\Services\M81ContentPatch::BEFORE_SHA
        ||count($ledger['files']??[])!==8||($ledger['owned_added_registration_lines']??null)!==12
        ||($ledger['owned_validator_added_lines']??null)!==0||($ledger['owned_validator_removed_lines']??null)!==0){
        throw new RuntimeException('M81 fresh source BEFORE/finite registration lineage differs.');
    }
    $dispatch=['app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php'];
    if(array_column($ledger['files'],'path')!==$dispatch){throw new RuntimeException('M81 exact eight shared source paths differ.');}
    $checked=0;
    foreach(['served_root'=>$root,'worktree'=>$wt]as $context=>$directory){
        if($normal($capture['roots'][$context]??'')!==$normal($directory)){throw new RuntimeException('M81 independent source ROOT/WT identity differs.');}
        $addedCount=0;
        foreach($ledger['files']as $file){
            $path=$file['path'];$pin=$file['profiles'][$context]??null;$old=$capture['files'][$path][$context]??null;
            if(!is_array($pin)||!is_array($old)||($old['exists']??null)!==true
                ||($old['raw_sha256']??null)!==($pin['before_raw_sha256']??null)
                ||($old['lf_sha256']??null)!==($pin['before_lf_sha256']??null)){
                throw new RuntimeException('M81 predecessor source pin does not derive from independent BEFORE.');
            }
            if(!hash_equals($pin['after_raw_sha256'],$hash($directory.'/'.$path))){throw new RuntimeException('M81 shared raw AFTER source differs: '.$path);}
            $raw=file_get_contents($directory.'/'.$path);$lf=str_replace("\r\n","\n",$raw);
            if(hash('sha256',$lf)!==$pin['after_lf_sha256']||substr_count($raw,"\n")!==$pin['after_lf_line_count']
                ||substr_count($raw,"\r\n")!==$pin['after_crlf_line_count']){throw new RuntimeException('M81 shared LF/EOL profile differs.');}
            foreach($file['hunks']as $index=>$hunk){
                $added=substr($hunk['after'],strlen($hunk['before']));
                if($hunk['added_lines']!==1||$hunk['removed_lines']!==0||!str_starts_with($hunk['after'],$hunk['before'])
                    ||substr_count($added,"\n")!==1||!str_contains($added,'M81')||str_contains($hunk['before'],'M81')
                    ||substr_count($lf,$hunk['after'])!==1){throw new RuntimeException('M81 exact one-line registration hunk differs.');}
                $lf=str_replace($hunk['after'],$hunk['before'],$lf);
                $lines=preg_split('/(?<=\n)/',$raw,-1,PREG_SPLIT_NO_EMPTY);
                $normalized=array_map(static fn($line)=>str_replace("\r\n","\n",$line),$lines);
                $wanted=preg_split('/(?<=\n)/',$hunk['after'],-1,PREG_SPLIT_NO_EMPTY);$positions=[];
                foreach(array_keys($lines)as $n){if(array_slice($normalized,$n,count($wanted))===$wanted){$positions[]=$n;}}
                if(count($positions)!==1){throw new RuntimeException('M81 raw hunk location is missing or ambiguous.');}
                $position=$positions[0]+count($wanted)-1;$eol=$pin['hunk_added_line_eols'][$index]??null;
                if(!in_array($eol,['LF','CRLF'],true)||$lines[$position]!==($eol==='CRLF'?str_replace("\n","\r\n",$added):$added)){
                    throw new RuntimeException('M81 owned registration line EOL differs.');
                }
                array_splice($lines,$position,1);$raw=implode('',$lines);++$addedCount;
            }
            if(hash('sha256',$raw)!==$pin['before_raw_sha256']||hash('sha256',$lf)!==$pin['before_lf_sha256']){
                throw new RuntimeException('M81 reversing only owned additions does not restore independent raw/LF BEFORE.');
            }
            ++$checked;
        }
        if($addedCount!==12){throw new RuntimeException('M81 exact twelve nonvisual additions differ by context.');}
    }
    return ['independent_source_before_sha256'=>$captureSha,'independent_source_before_paths'=>3197,
        'source_hunk_fixture_sha256'=>$fixtureSha,'finite_shared_files'=>8,'separate_contexts'=>2,
        'raw_lf_reversals'=> $checked,'owned_added_lines_per_context'=>12,
        'all_3197_after_inventory_claimed'=>false];
};
$lineageFirst=$lineageCheck();
$expectedSources=array_values(array_unique([...App\Services\M81ContentPatch::sourcePaths($patch,$contract,$root),
    'private:'.App\Services\M81ContentPatch::BEFORE_FILE,'private:'.App\Services\M81ContentPatch::CONSUMERS_FILE,'private:'.App\Services\M81ContentPatch::SYNC_RECEIPT]));
sort($expectedSources);$actualSources=array_keys($preview['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M81 full reviewed source/evidence list differs.');}
foreach ($preview['sources'] as $path => $sha) {
PHP;
$physical=<<<'PHP'
App\Services\M81LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.
$guard=new App\Services\M81LocalTargetGuard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key){if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1){throw new RuntimeException('M81 physical MySQL path is not local Windows.');}}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){throw new RuntimeException('M81 fresh physical MySQL identity differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
PHP;
$recheck=<<<'PHP'
} finally { $db->rollBack(); }
foreach($preview['sources'] as $path=>$sha){$file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M81 source/evidence changed during final audit.');}}
foreach([$before['physical_hashes'],$before['visual_hashes'],$before['frozen_hashes']] as $n=>$records){foreach($records as $path=>$sha){
    if(!hash_equals($sha,$hash($n===0?$path:$root.'/'.$path))){throw new RuntimeException('M81 physical/visual/frozen bytes changed during final audit.');}
}}
foreach($sync['files'] as $record){if($hash($root.'/'.$record['path'])!==$record['root_after_sha256']
    ||$hash(App\Services\M81ContentPatch::SOURCE_WORKTREE.'/'.$record['path'])!==$record['worktree_sha256']){throw new RuntimeException('M81 source-sync changed during final audit.');}}
if($lineageCheck()!==$lineageFirst){throw new RuntimeException('M81 independent raw/LF source lineage changed during final audit.');}
$repeatEvidence=null;
if($profileContext['priorName']!==null){
    $prior=$load($profileContext['priorName'],$profileContext['priorSha']);
    if(($prior['plan_digest']??null)!==$reviewedDigest||($prior['independent_before_sha256']??null)!==App\Services\M81ContentPatch::BEFORE_SHA
        ||($prior['backup_sha256']??null)!==$profileContext['backupSha']||($prior['sql_after']??null)!==$rawAfter
        ||($prior['normalized_before']??null)!==$normalized||($prior['protected']??null)!==$protected
        ||($prior['new_practices']??null)!==$practiceFacts||($prior['source_lineage']??null)!==$lineageFirst
        ||($prior['all_old_rows_and_timestamps_exact']??null)!==true||($prior['db_writes']??null)!==0){
        throw new RuntimeException('M81 post-UI repeat differs from the exact prior successful read-only receipt.');
    }
    $repeatEvidence=['file'=>$profileContext['priorName'],'sha256'=>$profileContext['priorSha'],
        'all_46_after_fingerprints_exact'=>true,'new_practice_rows_exact'=>true,'source_lineage_exact'=>true];
}
if(!hash_equals($profileContext['backupSha'],$hash($dir.'/'.$profileContext['backupName']))
    ||file_get_contents($dir.'/'.$previewName)!==file_get_contents($dir.'/'.$profileContext['backupName'])){throw new RuntimeException('M81 exclusive backup changed during audit.');}
$receipt =
PHP;
$insertCheck=<<<'PHP'
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        if($position!==App\Support\M81TransformationsCommonMistakes::PRACTICE_SOURCE_INDICES[$i]+1
            ||$source['type']!=='practice-set'||($source['level']??null)!==App\Support\M81TransformationsCommonMistakes::LEVELS[$i]
            ||$source['column']!=='left'||isset($insertOwnerSeen[$target['identity']])){throw new RuntimeException('M81 six appended practice bindings differ.');}
        $insertOwnerSeen[$target['identity']]=true;$page=$before['pages'][$target['identity']]['page'];
        $fields=['uuid'=>$uuid,'page_id'=>$page['id'],'page_category_id'=>$page['page_category_id'],'locale'=>'uk',
            'type'=>$source['type'],'column'=>$source['column'],'heading'=>$source['heading']??null,'css_class'=>$source['css_class']??null,
            'sort_order'=>$position,'body'=>$source['body'],'level'=>$source['level'],'seeder'=>$target['identity']];
        $same($insert['fields'],$fields,'M81 reviewed practice insert does not exactly match frozen source fields.');
PHP;
runFrozenReadOnlyProfile('m53-postconditions.php','8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',[
    ['M53PassiveConstructionsPackage','M81TransformationsCommonMistakes',7],['M53','M81',15],['m53','m81',4],
    [$originalInventory,$inventory,1],[$originalQuestions,"    if(array_keys(\$insertOwnerSeen)!==App\\Support\\M81TransformationsCommonMistakes::OWNERS){throw new RuntimeException('M81 exact six inserted owners/order differs.');}\n    \$newRows=[]; \$approved=[]; // No question-family insertion or omission is authorized for M81.",1],
    ['    $newPractices = []; $practiceFacts = [];','    $newPractices = []; $practiceFacts = []; $insertOwnerSeen=[];',1],
    ['        $uuid = App\\Support\\M26DetailPackage::uuid($target[\'identity\'], $source, $position);',$insertCheck,1],
    ["\$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);","if(isset(\$before['schema'][\$table])&&\$db->getSchemaBuilder()->getColumns(\$table)!==\$before['schema'][\$table]){throw new RuntimeException('M81 captured SQL schema changed.');}\n        \$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);",1],
    ['foreach ($preview[\'sources\'] as $path => $sha) {',$extraSource,1],
    ['App\\Services\\M81LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.',$physical,1],
    ["'question_plan_sha256' => App\\Services\\M81ContentPatch::QUESTION_PLAN_SHA,","'backup_file'=>\$profileContext['backupName'],'backup_sha256'=>\$profileContext['backupSha'],'backup_equals_reviewed_preview'=>true,\n    'source_lineage'=>\$lineageFirst,'post_ui_repeat'=>\$repeatEvidence,\n    'metadata_transitions'=>\$metadata,'update_operations'=>count(\$expectedChanges),'body_updates'=>\$bodyKinds,'question_family_writes'=>0,",1],
    ["} finally { \$db->rollBack(); }\n\$receipt =",$recheck,1],
],['backupName'=>$backupName,'backupSha'=>$backupSha,'priorName'=>$priorName,'priorSha'=>$priorSha]);
