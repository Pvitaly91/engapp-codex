<?php

/**
 * Independent SELECT-only M82 reconstruction via the immutable M53 scanner.
 * Args: preview, reviewed digest, backup, backup raw SHA, fresh final receipt.
 * Optionally append prior final receipt + raw SHA for an exact post-UI repeat.
 * Never calls a writer, apply/restore, seeder or HTTP endpoint.
 */
require __DIR__.'/frozen-readonly-profile.php';
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array(count($argv),[6,8],true)
    ||preg_match('/^preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1
    ||preg_match('/^backup-v[1-9][0-9]*\.json$/D',$argv[3])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[5])!==1){
    throw new RuntimeException('M82 reviewed preview/digest, exclusive backup/raw SHA, fresh final basename required.');
}
$backupName=$argv[3];$backupSha=$argv[4];$private='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m82-local';
$priorName=$argv[6]??null;$priorSha=$argv[7]??null;
if(($priorName!==null||$priorSha!==null)&&(count($argv)!==8
    ||!is_string($priorName)||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$priorName)!==1
    ||!is_string($priorSha)||preg_match('/^[a-f0-9]{64}$/D',$priorSha)!==1
    ||$priorName===$argv[5]||!is_file($private.'/'.$priorName)||is_link($private.'/'.$priorName)
    ||!hash_equals($priorSha,hash_file('sha256',$private.'/'.$priorName)))){
    throw new RuntimeException('M82 optional prior final receipt name/raw SHA differs; no repeat claim.');
}
foreach([$argv[1],$backupName] as $name){if(!is_file($private.'/'.$name)||is_link($private.'/'.$name)){throw new RuntimeException('M82 reviewed preview/backup absent or linked.');}}
if(!hash_equals($backupSha,hash_file('sha256',$private.'/'.$backupName))
    ||file_get_contents($private.'/'.$argv[1])!==file_get_contents($private.'/'.$backupName)){
    throw new RuntimeException('M82 exclusive backup bytes are not the exact reviewed preview.');
}
$argv=[$argv[0],$argv[1],$argv[2],$argv[5]];
$inventory=<<<'PHP'
    $pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $nonUk = 0;
    $expectedChanges=[];$metadata=[];$bodyKinds=['subtitle'=>0,'teaching'=>0];
    $finiteBoxes=[
        1714=>['5c9afe7e-018c-5bbf-a37b-423059adbe5f','usage-panels','1. Де плутаються найчастіше'],
        1715=>['d776727b-5f0f-5a89-b09c-b5e79de3e87d','usage-panels','2. Як працює правильний вибір'],
        1672=>['cd508a49-61aa-5e34-bf35-cf5f782c164e','usage-panels','1. Де плутаються найчастіше'],
        1673=>['b8629bfb-27d4-5cdd-a217-7539a8847352','usage-panels','2. Як працює правильний вибір'],
        1693=>['e9f04d6e-5626-587f-b061-94ccb976bb50','usage-panels','1. Де плутаються найчастіше'],
        1694=>['04ddd24d-94a7-553d-b276-6a20a520782c','usage-panels','2. Як працює правильний вибір'],
        1651=>['0975e241-500b-5f12-9493-336bffdbae0c','usage-panels','1. Де плутаються найчастіше'],
        1652=>['ab5515d1-7685-5839-b6d2-d63f14638802','usage-panels','2. Як працює правильний вибір'],
    ];
    foreach ($patch['targets'] as $i => $target) {
        $owner = $target['identity']; $captured = $before['pages'][$owner]; $oldPage = $captured['page'];
        if($oldPage['id']!==App\Support\M82CommonMistakesLevelRevision::PAGE_IDS[$i]
            ||$oldPage['seeder']!==$owner||$oldPage['page_category_id']!==App\Support\M82CommonMistakesLevelRevision::CATEGORY_IDS[$i]){
            throw new RuntimeException('M82 independently captured Page identity differs.');
        }
        foreach(['page','category_ancestry','blocks'] as $field){if(($preview['pages'][$owner][$field]??null)!==$captured[$field]){throw new RuntimeException('M82 preview original inventory differs.');}}
        App\Services\M82ContentPatch::assertDefinitionScope($sourceBefore['targets'][$i]['before'],$target['after'],$target,$i);
        $pageBefore[$oldPage['id']] = $oldPage; $pageAfter[$oldPage['id']] = array_replace($oldPage, ['text' => $target['after']['page']['subtitle_text']]);
        if($oldPage['text']!==$pageAfter[$oldPage['id']]['text']){$expectedChanges['pages:'.$oldPage['id']]=[
            'table'=>'pages','id'=>$oldPage['id'],'uuid'=>$oldPage['uuid']??null,'seeder'=>$owner,
            'before'=>['text'=>$oldPage['text']],'after'=>['text'=>$pageAfter[$oldPage['id']]['text']]];}
        $positions=[];
        foreach ($captured['blocks'] as $old) {
            if ($old['locale'] !== 'uk') { ++$nonUk; continue; }
            $position = $old['sort_order'];
            if(!is_int($position)||$position<0||$position>App\Support\M82CommonMistakesLevelRevision::CONTINUING_COUNTS[$i]
                ||isset($positions[$position])||isset($ukBefore[$old['id']])||$old['seeder']!==$owner||$old['page_id']!==$oldPage['id']){
                throw new RuntimeException('M82 continuing owner/order inventory differs.');
            }
            $positions[$position]=true;
            $config=$position===0?['body'=>$target['after']['page']['subtitle_html'],'type'=>$old['type'],'heading'=>$old['heading']]
                :$target['after']['page']['blocks'][$position-1];
            $values=['body'=>$config['body'],'type'=>$config['type'],'heading'=>$config['heading']??null];
            if(!is_string($values['body'])){throw new RuntimeException('M82 continuing body missing.');}
            foreach(['column','level','css_class'] as $field){if($position!==0&&($config[$field]??null)!==$old[$field]){throw new RuntimeException('M82 continuing immutable metadata differs: '.$field);}}
            if(isset($finiteBoxes[$old['id']])){
                [$uuid,$type,$heading]=$finiteBoxes[$old['id']];
                if($old['uuid']!==$uuid||$old['type']!=='box'||$old['heading']!==$heading||$old['level']!==null
                    ||$values['type']!==$type||$values['heading']!==null){throw new RuntimeException('M82 exact eight box transitions differ.');}
                foreach(['type','heading'] as $field){$metadata[]=['id'=>$old['id'],'uuid'=>$uuid,'field'=>$field,'before'=>$old[$field],'after'=>$values[$field]];}
            }elseif($values['type']!==$old['type']||$values['heading']!==$old['heading']){throw new RuntimeException('M82 unapproved continuing type/heading transition.');}
            $oldValues=array_intersect_key($old,array_flip(['body','type','heading']));
            $oldValues=['body'=>$oldValues['body'],'type'=>$oldValues['type'],'heading'=>$oldValues['heading']];
            $ukBefore[$old['id']] = $old; $ukAfter[$old['id']] = array_replace($old,$values);
            if($oldValues!==$values){$expectedChanges['text_blocks:'.$old['id']]=['table'=>'text_blocks','id'=>$old['id'],'uuid'=>$old['uuid'],
                'seeder'=>$owner,'before'=>$oldValues,'after'=>$values];}
            if($values['body']!==$old['body']){++$bodyKinds[$position===0?'subtitle':'teaching'];}
        }
        $actualPositions=array_keys($positions);sort($actualPositions);
        if($actualPositions!==range(0,App\Support\M82CommonMistakesLevelRevision::CONTINUING_COUNTS[$i])){throw new RuntimeException('M82 continuing position vector differs.');}
    }
    $expectedUkRows=array_sum(array_map(static fn($n)=>$n+1,App\Support\M82CommonMistakesLevelRevision::CONTINUING_COUNTS));
    if(count($pageBefore)!==count($patch['targets'])||$expectedUkRows!==38||count($ukBefore)!==$expectedUkRows||$nonUk!==56||count($metadata)!==2*count($finiteBoxes)
        ||count($expectedChanges)!==44||$bodyKinds!==['subtitle'=>6,'teaching'=>32]
        ||count($preview['updates'])!==count($expectedChanges)||count($preview['inserts']??[])!==6||($preview['deletes']??[])!==[]
        ||isset($preview['question_supplement'])){throw new RuntimeException('M82 exact continuing/insert/metadata/no-question scope differs.');}
    $seenChanges=[];
    foreach($preview['updates'] as $change){$key=$change['table'].':'.$change['id'];$expected=$expectedChanges[$key]??null;
        if($expected===null||isset($seenChanges[$key])||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
            ||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$expected
            ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){
            throw new RuntimeException('M82 exact reviewed update inventory differs.');
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
    $service = new App\Services\M82ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm82QuestionState'))->invoke($service, 'after', $patch, false);
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
$consumers=$load(App\Services\M82ContentPatch::CONSUMERS_FILE,App\Services\M82ContentPatch::CONSUMERS_SHA);
App\Services\M51ContentPatch::assertConsumerCapture($before,$consumers);
if(count($before['sql_before'])!==46||count($before['visual_hashes'])!==326||count($before['frozen_hashes'])!==2787
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']||($consumers['all_tables_after_http']??null)!==$before['sql_before']){
    throw new RuntimeException('M82 independent full-table/old-consumer baseline differs.');
}
$navigationUrls=[
    4=>['/theory/basic-grammar/verb-to-be/verb-to-be-present','/theory/basic-grammar/verb-to-be/there-is-there-are',
        '/theory/some-any/theory-some-any-things','/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative'],
    5=>['/theory/tenses/present-perfect/present-perfect-forms','/theory/conditionals/first-conditional',
        '/theory/reported-speech/reported-statements','/theory/conditionals/second-conditional'],
];
$navigationLabels=[
    4=>['Verb to be — Present','There is / There are','Some / Any — Things','Comparative vs Superlative','A1 Mixed Revision'],
    5=>['Present Perfect Forms','First Conditional','Reported Statements','Second Conditional','A2 Mixed Revision'],
];
$navigationAdditions=0;
foreach([4,5]as $n){
    $oldNavigation=json_decode($sourceBefore['targets'][$n]['before']['page']['blocks'][3]['body'],true,flags:JSON_THROW_ON_ERROR);
    $newNavigation=$master['lessons'][$n]['blocks'][3]['data'];
    if(array_column($oldNavigation['items']??[],'label')!==$navigationLabels[$n]
        ||array_column($newNavigation['items']??[],'label')!==$navigationLabels[$n]
        ||array_diff_key($newNavigation,['items'=>true])!==array_diff_key($oldNavigation,['items'=>true])
        ||array_column($oldNavigation['items'],'current')!==[false,false,false,false,true]
        ||array_column($newNavigation['items'],'current')!==[false,false,false,false,true]){
        throw new RuntimeException('M82 approved footer URL change must preserve all five labels/order/current/title.');
    }
    foreach($newNavigation['items']as $index=>$item){
        $withoutUrl=$item;unset($withoutUrl['url']);
        if($withoutUrl!==$oldNavigation['items'][$index]||array_key_exists('url',$oldNavigation['items'][$index])
            ||($index<4?($item['url']??null)!==$navigationUrls[$n][$index]:array_key_exists('url',$item))){
            throw new RuntimeException('M82 only the eight exact independently confirmed footer data URLs are approved.');
        }
        if($index<4){++$navigationAdditions;}
    }
}
if($navigationAdditions!==8){throw new RuntimeException('M82 exact eight footer URL additions differ.');}
$lineageCheck=static function()use($root,$dir,$hash,$load,$normal):array{
    $captureSha='567f21ca386e0fc36c531ff2abb11f1cd244651de594f3872d39d02605c7401d';
    $fixtureSha='246203947784a04b2ebdcb740e4cef6661ccce8c7ea0aae019224f91502bc582';
    $courseSha='baf41c05a076d2c8df6c3a6132817344e61a2b8a374d456d9108f534090428c7';
    $capture=$load('independent-source-before-v1.json',$captureSha);
    $course=$load('course-source-before-v1.json',$courseSha);
    $wt=App\Services\M82ContentPatch::SOURCE_WORKTREE;$fixture=$wt.'/tests/Fixtures/m82-owned-source-hunks.json';
    if(!hash_equals($fixtureSha,$hash($fixture))){throw new RuntimeException('M82 independent source-hunk fixture bytes differ.');}
    $ledger=json_decode(file_get_contents($fixture),true,flags:JSON_THROW_ON_ERROR);
    if(($capture['base_sha']??null)!==App\Support\M82CommonMistakesLevelRevision::BASE_SHA
        ||($capture['phase']??null)!=='before'||count($capture['files']??[])!==3243
        ||($ledger['base_sha']??null)!==App\Support\M82CommonMistakesLevelRevision::BASE_SHA
        ||($ledger['independent_source_before_sha256']??null)!==$captureSha
        ||($ledger['independent_db_before_sha256']??null)!==App\Services\M82ContentPatch::BEFORE_SHA
        ||($ledger['independent_course_source_before_sha256']??null)!==$courseSha
        ||count($ledger['files']??[])!==8||($ledger['owned_added_registration_lines']??null)!==12
        ||($ledger['owned_validator_added_lines']??0)!==0||($ledger['owned_validator_removed_lines']??0)!==0
        ||($course['base_sha']??null)!==App\Support\M82CommonMistakesLevelRevision::BASE_SHA
        ||($course['phase']??null)!=='before'||count($course['files']??[])!==103){
        throw new RuntimeException('M82 fresh source/course BEFORE and finite shared lineage differ.');
    }
    $dispatch=['app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php'];
    if(array_column($ledger['files'],'path')!==$dispatch){throw new RuntimeException('M82 exact eight shared source paths differ.');}
    $checked=$unaffected=$courseChecked=0;
    foreach(['served_root'=>$root,'worktree'=>$wt]as $context=>$directory){
        if($normal($capture['roots'][$context]??'')!==$normal($directory)
            ||$normal($course['roots'][$context]??'')!==$normal($directory)){throw new RuntimeException('M82 independent source ROOT/WT identity differs.');}
        $addedCount=0;
        foreach($ledger['files']as $file){
            $path=$file['path'];$pin=$file['profiles'][$context]??null;$old=$capture['files'][$path][$context]??null;
            if(!is_array($pin)||!is_array($old)||($old['exists']??null)!==true
                ||($old['raw_sha256']??null)!==($pin['before_raw_sha256']??null)
                ||($old['lf_sha256']??null)!==($pin['before_lf_sha256']??null)){
                throw new RuntimeException('M82 predecessor source pin does not derive from independent BEFORE.');
            }
            if(!hash_equals($pin['after_raw_sha256'],$hash($directory.'/'.$path))){throw new RuntimeException('M82 shared raw AFTER source differs: '.$path);}
            $raw=file_get_contents($directory.'/'.$path);$lf=str_replace("\r\n","\n",$raw);
            if(hash('sha256',$lf)!==$pin['after_lf_sha256']||substr_count($raw,"\n")!==$pin['after_lf_line_count']
                ||substr_count($raw,"\r\n")!==$pin['after_crlf_line_count']){throw new RuntimeException('M82 shared LF/EOL profile differs.');}
            foreach($file['hunks']as $index=>$hunk){
                $added=substr($hunk['after'],strlen($hunk['before']));
                if($hunk['added_lines']!==1||$hunk['removed_lines']!==0||!str_starts_with($hunk['after'],$hunk['before'])
                    ||substr_count($added,"\n")!==1||!str_contains($added,'M82')||str_contains($hunk['before'],'M82')
                    ||substr_count($lf,$hunk['after'])!==1){throw new RuntimeException('M82 exact one-line registration hunk differs.');}
                $lf=str_replace($hunk['after'],$hunk['before'],$lf);
                $lines=preg_split('/(?<=\n)/',$raw,-1,PREG_SPLIT_NO_EMPTY);
                $normalized=array_map(static fn($line)=>str_replace("\r\n","\n",$line),$lines);
                $wanted=preg_split('/(?<=\n)/',$hunk['after'],-1,PREG_SPLIT_NO_EMPTY);$positions=[];
                foreach(array_keys($lines)as $n){if(array_slice($normalized,$n,count($wanted))===$wanted){$positions[]=$n;}}
                if(count($positions)!==1){throw new RuntimeException('M82 raw hunk location is missing or ambiguous.');}
                $position=$positions[0]+count($wanted)-1;$eol=$pin['hunk_added_line_eols'][$index]??null;
                if(!in_array($eol,['LF','CRLF'],true)||$lines[$position]!==($eol==='CRLF'?str_replace("\n","\r\n",$added):$added)){
                    throw new RuntimeException('M82 owned registration line EOL differs.');
                }
                array_splice($lines,$position,1);$raw=implode('',$lines);++$addedCount;
            }
            if(hash('sha256',$raw)!==$pin['before_raw_sha256']||hash('sha256',$lf)!==$pin['before_lf_sha256']){
                throw new RuntimeException('M82 reversing only owned hunks does not restore independent raw/LF BEFORE.');
            }
            ++$checked;
        }
        if($addedCount!==12){throw new RuntimeException('M82 exact shared registration addition count differs by context.');}
        $verifyUnchanged=static function(string $path,array $old)use($directory,$hash):void{
            $file=$directory.'/'.$path;
            if(($old['exists']??null)===false){if(file_exists($file)||is_link($file)){throw new RuntimeException('M82 foreign absent source was recreated: '.$path);}return;}
            if(($old['exists']??null)!==true||!hash_equals($old['raw_sha256'],$hash($file))){throw new RuntimeException('M82 non-target raw source changed: '.$path);}
            $raw=file_get_contents($file);
            if(strlen($raw)!==$old['bytes']||hash('sha256',str_replace("\r\n","\n",$raw))!==$old['lf_sha256']
                ||substr_count($raw,"\r\n")!==$old['crlf']||substr_count($raw,"\n")!==$old['lf']){throw new RuntimeException('M82 non-target LF/EOL source changed: '.$path);}
        };
        foreach($capture['files']as $path=>$profiles){
            if(in_array($path,$dispatch,true)||in_array($path,$ledger['approved_definition_paths'],true)||$path==='.gitattributes'){continue;}
            $verifyUnchanged($path,$profiles[$context]);++$unaffected;
        }
        foreach($course['files']as $path=>$profiles){
            if(in_array($path,$dispatch,true)){continue;}
            $verifyUnchanged($path,$profiles[$context]);++$courseChecked;
        }
        $attributes=$capture['files']['.gitattributes'][$context];
        if(($attributes['exists']??null)===false){$verifyUnchanged('.gitattributes',$attributes);}
        else{
            $raw=file_get_contents($directory.'/.gitattributes');
            if(hash('sha256',substr($raw,0,$attributes['bytes']))!==$attributes['raw_sha256']){
                throw new RuntimeException('M82 delivery attributes do not retain the exact existing raw prefix.');
            }
        }
    }
    return ['independent_source_before_sha256'=>$captureSha,'independent_source_before_paths'=>3243,
        'source_hunk_fixture_sha256'=>$fixtureSha,'finite_shared_files'=>8,'separate_contexts'=>2,
        'raw_lf_reversals'=>$checked,'owned_added_registration_lines_per_context'=>12,
        'native_release_validator_unchanged'=>true,
        'non_target_source_context_checks'=>$unaffected,'course_source_before_sha256'=>$courseSha,
        'course_source_context_checks'=>$courseChecked,'delivery_attributes'=>'Before prefix/foreign absence preserved; appended M82 EOL rules are a separate delivery review, not an unchanged-file claim.',
        'all_3243_paths_unchanged_claimed'=>false];
};
$lineageFirst=$lineageCheck();
$courseSqlCheck=static function($db)use($consumers,$same):array{
    $courses=$consumers['course_boundary']['courses']??[];
    if(array_keys($courses)!==['a1','a2']){throw new RuntimeException('M82 actual course boundary inventory is missing.');}
    $savedCount=$memberships=$questions=0;$final=[];
    foreach($courses as $level=>$course){
        if(count($course['saved_tests']??[])!==16||count($course['question_memberships']??[])!==16
            ||count($course['manifest']['lessons']??[])!==16){throw new RuntimeException('M82 preserved sixteen-lesson course inventory differs.');}
        foreach($course['saved_tests']as $slug=>$expected){
            $actual=(array)$db->table('saved_grammar_tests')->where('id',$expected['id'])->sole();
            $same($actual,$expected,'M82 actual course saved-test metadata/timestamps changed.');++$savedCount;
            $links=$db->table('saved_grammar_test_questions')->where('saved_grammar_test_id',$expected['id'])->orderBy('position')->get()->map(static fn($r)=>(array)$r)->all();
            if($links!==$course['question_memberships'][$slug]){throw new RuntimeException('M82 actual course question membership/order/timestamps changed.');}
            $memberships+=count($links);
            if($slug==='polyglot-final-drill-'.$level){$final[$level]=['saved_test_id'=>$expected['id'],'questions'=>count($links),'unchanged'=>true];}
        }
        $expected=$course['questions'];$actual=$db->table('questions')->whereIn('uuid',array_column($expected,'uuid'))->orderBy('id')->get()->map(static fn($r)=>(array)$r)->all();
        if($actual!==$expected){throw new RuntimeException('M82 actual course original question rows changed.');}$questions+=count($actual);
    }
    if($savedCount!==32||array_keys($final)!==['a1','a2']||array_column($final,'questions')!==[24,24]){
        throw new RuntimeException('M82 actual course final-drill count/bindings differ.');
    }
    return ['saved_test_bindings'=>32,'membership_rows'=>$memberships,'course_question_rows'=>$questions,'final_drills'=>$final,
        'all_actual_rows_and_timestamps_exact'=>true,'course_runtime_ui'=>'separate consumer acceptance evidence'];
};
$expectedSources=array_values(array_unique([...App\Services\M82ContentPatch::sourcePaths($patch,$contract,$root),
    'private:'.App\Services\M82ContentPatch::BEFORE_FILE,'private:'.App\Services\M82ContentPatch::CONSUMERS_FILE,'private:'.App\Services\M82ContentPatch::SYNC_RECEIPT]));
sort($expectedSources);$actualSources=array_keys($preview['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M82 full reviewed source/evidence list differs.');}
foreach ($preview['sources'] as $path => $sha) {
PHP;
$physical=<<<'PHP'
App\Services\M82LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.
$guard=new App\Services\M82LocalTargetGuard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key){if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1){throw new RuntimeException('M82 physical MySQL path is not local Windows.');}}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){throw new RuntimeException('M82 fresh physical MySQL identity differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
PHP;
$recheck=<<<'PHP'
} finally { $db->rollBack(); }
foreach($preview['sources'] as $path=>$sha){$file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M82 source/evidence changed during final audit.');}}
foreach([$before['physical_hashes'],$before['visual_hashes'],$before['frozen_hashes']] as $n=>$records){foreach($records as $path=>$sha){
    if(!hash_equals($sha,$hash($n===0?$path:$root.'/'.$path))){throw new RuntimeException('M82 physical/visual/frozen bytes changed during final audit.');}
}}
foreach($sync['files'] as $record){if($hash($root.'/'.$record['path'])!==$record['root_after_sha256']
    ||$hash(App\Services\M82ContentPatch::SOURCE_WORKTREE.'/'.$record['path'])!==$record['worktree_sha256']){throw new RuntimeException('M82 source-sync changed during final audit.');}}
if($lineageCheck()!==$lineageFirst){throw new RuntimeException('M82 independent raw/LF source lineage changed during final audit.');}
$repeatEvidence=null;
if($profileContext['priorName']!==null){
    $prior=$load($profileContext['priorName'],$profileContext['priorSha']);
    if(($prior['plan_digest']??null)!==$reviewedDigest||($prior['independent_before_sha256']??null)!==App\Services\M82ContentPatch::BEFORE_SHA
        ||($prior['backup_sha256']??null)!==$profileContext['backupSha']||($prior['sql_after']??null)!==$rawAfter
        ||($prior['normalized_before']??null)!==$normalized||($prior['protected']??null)!==$protected
        ||($prior['new_practices']??null)!==$practiceFacts||($prior['source_lineage']??null)!==$lineageFirst||($prior['course_boundary_sql']??null)!==$courseFacts
        ||($prior['approved_footer_url_additions']??null)!==$navigationAdditions
        ||($prior['all_old_rows_and_timestamps_exact']??null)!==true||($prior['db_writes']??null)!==0){
        throw new RuntimeException('M82 post-UI repeat differs from the exact prior successful read-only receipt.');
    }
    $repeatEvidence=['file'=>$profileContext['priorName'],'sha256'=>$profileContext['priorSha'],
        'all_46_after_fingerprints_exact'=>true,'new_practice_rows_exact'=>true,'source_lineage_exact'=>true];
}
if(!hash_equals($profileContext['backupSha'],$hash($dir.'/'.$profileContext['backupName']))
    ||file_get_contents($dir.'/'.$previewName)!==file_get_contents($dir.'/'.$profileContext['backupName'])){throw new RuntimeException('M82 exclusive backup changed during audit.');}
$receipt =
PHP;
$insertCheck=<<<'PHP'
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        if($position!==App\Support\M82CommonMistakesLevelRevision::PRACTICE_SOURCE_INDICES[$i]+1
            ||$source['type']!=='practice-set'||($source['level']??null)!==App\Support\M82CommonMistakesLevelRevision::LEVELS[$i]
            ||$source['column']!=='left'||isset($insertOwnerSeen[$target['identity']])){throw new RuntimeException('M82 six appended practice bindings differ.');}
        $insertOwnerSeen[$target['identity']]=true;$page=$before['pages'][$target['identity']]['page'];
        $fields=['uuid'=>$uuid,'page_id'=>$page['id'],'page_category_id'=>$page['page_category_id'],'locale'=>'uk',
            'type'=>$source['type'],'column'=>$source['column'],'heading'=>$source['heading']??null,'css_class'=>$source['css_class']??null,
            'sort_order'=>$position,'body'=>$source['body'],'level'=>$source['level'],'seeder'=>$target['identity']];
        $same($insert['fields'],$fields,'M82 reviewed practice insert does not exactly match frozen source fields.');
PHP;
runFrozenReadOnlyProfile('m53-postconditions.php','8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',[
    ['M53PassiveConstructionsPackage','M82CommonMistakesLevelRevision',7],['M53','M82',15],['m53','m82',4],
    [$originalInventory,$inventory,1],[$originalQuestions,"    if(array_keys(\$insertOwnerSeen)!==App\\Support\\M82CommonMistakesLevelRevision::OWNERS){throw new RuntimeException('M82 exact six inserted owners/order differs.');}\n    \$newRows=[]; \$approved=[]; // No question-family insertion or omission is authorized for M82.",1],
    ['    $newPractices = []; $practiceFacts = [];','    $newPractices = []; $practiceFacts = []; $insertOwnerSeen=[];',1],
    ['        $uuid = App\\Support\\M26DetailPackage::uuid($target[\'identity\'], $source, $position);',$insertCheck,1],
    ["\$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);","if(isset(\$before['schema'][\$table])&&\$db->getSchemaBuilder()->getColumns(\$table)!==\$before['schema'][\$table]){throw new RuntimeException('M82 captured SQL schema changed.');}\n        \$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);",1],
    ['foreach ($preview[\'sources\'] as $path => $sha) {',$extraSource,1],
    ["    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }",
        "    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }\n    \$courseFacts=\$courseSqlCheck(\$db);",1],
    ['App\\Services\\M82LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.',$physical,1],
    ["'question_plan_sha256' => App\\Services\\M82ContentPatch::QUESTION_PLAN_SHA,","'backup_file'=>\$profileContext['backupName'],'backup_sha256'=>\$profileContext['backupSha'],'backup_equals_reviewed_preview'=>true,\n    'source_lineage'=>\$lineageFirst,'post_ui_repeat'=>\$repeatEvidence,'course_boundary_sql'=>\$courseFacts,\n    'metadata_transitions'=>\$metadata,'update_operations'=>count(\$expectedChanges),'body_updates'=>\$bodyKinds,'question_family_writes'=>0,\n    'approved_footer_url_additions'=>\$navigationAdditions,'all_footer_labels_order_current_preserved'=>true,",1],
    ["} finally { \$db->rollBack(); }\n\$receipt =",$recheck,1],
],['backupName'=>$backupName,'backupSha'=>$backupSha,'priorName'=>$priorName,'priorSha'=>$priorSha]);
