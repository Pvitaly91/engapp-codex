<?php

/**
 * Independent SELECT-only M83 reconstruction via the immutable M53 scanner.
 * Args: preview, reviewed digest, backup, backup raw SHA, fresh final receipt.
 * Optionally append prior final receipt + raw SHA for an exact post-UI repeat.
 * Never calls a writer, apply/restore, seeder or HTTP endpoint.
 */
require __DIR__.'/frozen-readonly-profile.php';
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array(count($argv),[6,8],true)
    ||preg_match('/^preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1
    ||preg_match('/^backup-v[1-9][0-9]*\.json$/D',$argv[3])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[5])!==1){
    throw new RuntimeException('M83 reviewed preview/digest, exclusive backup/raw SHA, fresh final basename required.');
}
$backupName=$argv[3];$backupSha=$argv[4];$private='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m83-local';
$priorName=$argv[6]??null;$priorSha=$argv[7]??null;
if(($priorName!==null||$priorSha!==null)&&(count($argv)!==8
    ||!is_string($priorName)||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$priorName)!==1
    ||!is_string($priorSha)||preg_match('/^[a-f0-9]{64}$/D',$priorSha)!==1
    ||$priorName===$argv[5]||!is_file($private.'/'.$priorName)||is_link($private.'/'.$priorName)
    ||!hash_equals($priorSha,hash_file('sha256',$private.'/'.$priorName)))){
    throw new RuntimeException('M83 optional prior final receipt name/raw SHA differs; no repeat claim.');
}
foreach([$argv[1],$backupName] as $name){if(!is_file($private.'/'.$name)||is_link($private.'/'.$name)){throw new RuntimeException('M83 reviewed preview/backup absent or linked.');}}
if(!hash_equals($backupSha,hash_file('sha256',$private.'/'.$backupName))
    ||file_get_contents($private.'/'.$argv[1])!==file_get_contents($private.'/'.$backupName)){
    throw new RuntimeException('M83 exclusive backup bytes are not the exact reviewed preview.');
}
$argv=[$argv[0],$argv[1],$argv[2],$argv[5]];
$inventory=<<<'PHP'
    $pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $nonUk = 0;
    $expectedChanges=[];$bodyKinds=['subtitle'=>0,'teaching'=>0];$metadata=[];
    foreach ($patch['targets'] as $i => $target) {
        $owner=$target['identity'];$captured=$before['pages'][$owner];$oldPage=$captured['page'];
        $source=$sourceBefore['targets'][$i]['before'];
        if($oldPage['id']!==App\Support\M83B2RevisionBasicGrammar::PAGE_IDS[$i]
            ||$oldPage['seeder']!==$owner||$oldPage['page_category_id']!==App\Support\M83B2RevisionBasicGrammar::CATEGORY_IDS[$i]
            ||$oldPage['text']!==$source['page']['subtitle_text']){
            throw new RuntimeException('M83 independent Page/source identity or subtitle differs.');
        }
        foreach(['page','category_ancestry','blocks'] as $field){
            if(($preview['pages'][$owner][$field]??null)!==$captured[$field]){
                throw new RuntimeException('M83 reviewed original inventory differs from independent BEFORE.');
            }
        }
        if(($before['definitions'][$owner]['definition']??null)!==$source){
            throw new RuntimeException('M83 frozen source BEFORE differs from independent working capture.');
        }
        App\Services\M83ContentPatch::assertDefinitionScope($source,$target['after'],$target,$i);
        $pageBefore[$oldPage['id']]=$oldPage;
        $pageAfter[$oldPage['id']]=array_replace($oldPage,['text'=>$target['after']['page']['subtitle_text']]);
        if($oldPage['text']!==$pageAfter[$oldPage['id']]['text']){
            $expectedChanges['pages:'.$oldPage['id']]=['table'=>'pages','id'=>$oldPage['id'],'uuid'=>$oldPage['uuid']??null,
                'seeder'=>$owner,'before'=>['text'=>$oldPage['text']],'after'=>['text'=>$pageAfter[$oldPage['id']]['text']]];
        }
        $positions=[];
        foreach($captured['blocks'] as $old){
            if($old['locale']!=='uk'){++$nonUk;continue;}
            $position=$old['sort_order'];
            if(!is_int($position)||$position<0||$position>App\Support\M83B2RevisionBasicGrammar::CONTINUING_COUNTS[$i]
                ||isset($positions[$position])||isset($ukBefore[$old['id']])
                ||$old['seeder']!==$owner||$old['page_id']!==$oldPage['id']
                ||$old['page_category_id']!==$oldPage['page_category_id']){
                throw new RuntimeException('M83 exact continuing owner/locale/order inventory differs.');
            }
            $positions[$position]=true;
            $config=$position===0?['body'=>$target['after']['page']['subtitle_html'],'type'=>'subtitle','column'=>'header',
                'heading'=>null,'level'=>$source['page']['subtitle_level']??null,'css_class'=>null]
                :$target['after']['page']['blocks'][$position-1];
            $oldConfig=$position===0?['body'=>$source['page']['subtitle_html']]
                :$source['page']['blocks'][$position-1];
            if(!is_string($config['body']??null)||($oldConfig['body']??null)!==$old['body']){
                throw new RuntimeException('M83 independent source/SQL body correspondence differs.');
            }
            foreach(['type','column','heading','level','css_class'] as $field){
                if(($config[$field]??null)!==$old[$field]){
                    throw new RuntimeException('M83 no continuing metadata transition is approved: '.$field);
                }
            }
            if($position>0&&App\Support\M26DetailPackage::uuid($owner,$oldConfig,$position)!==$old['uuid']){
                throw new RuntimeException('M83 continuing source/SQL UUID correspondence differs.');
            }
            $ukBefore[$old['id']]=$old;$ukAfter[$old['id']]=array_replace($old,['body'=>$config['body']]);
            if($config['body']!==$old['body']){
                $expectedChanges['text_blocks:'.$old['id']]=['table'=>'text_blocks','id'=>$old['id'],'uuid'=>$old['uuid'],
                    'seeder'=>$owner,'before'=>['body'=>$old['body']],'after'=>['body'=>$config['body']]];
                ++$bodyKinds[$position===0?'subtitle':'teaching'];
            }
        }
        $actualPositions=array_keys($positions);sort($actualPositions);
        if($actualPositions!==range(0,App\Support\M83B2RevisionBasicGrammar::CONTINUING_COUNTS[$i])){
            throw new RuntimeException('M83 exact continuing native position vector differs.');
        }
        if($i>0){
            $matches=array_values(array_filter($captured['blocks'],static fn($row)=>$row['locale']==='uk'
                &&$row['sort_order']===App\Support\M83B2RevisionBasicGrammar::PRACTICE_SOURCE_INDICES[$i]+1));
            if(count($matches)!==1||$matches[0]['type']!=='practice-set'||$matches[0]['level']!==null
                ||$matches[0]['uuid']!==App\Support\M83B2RevisionBasicGrammar::EXISTING_PRACTICE_UUIDS[$i]){
                throw new RuntimeException('M83 existing practice UUID/order/NULL level differs.');
            }
        }
    }
    $expectedUkRows=array_sum(array_map(static fn($count)=>$count+1,App\Support\M83B2RevisionBasicGrammar::CONTINUING_COUNTS));
    if(count($pageBefore)!==6||$expectedUkRows!==77||count($ukBefore)!==$expectedUkRows||$nonUk!==142
        ||$metadata!==[]||$expectedChanges===[]||count($preview['updates']??[])!==count($expectedChanges)
        ||count($preview['inserts']??[])!==1||($preview['deletes']??[])!==[]
        ||array_key_exists('question_supplement',$preview)){
        throw new RuntimeException('M83 exact continuing/body-only/one-append/no-question scope differs.');
    }
    $seenChanges=[];
    foreach($preview['updates'] as $change){
        $key=$change['table'].':'.$change['id'];$expected=$expectedChanges[$key]??null;
        if($expected===null||isset($seenChanges[$key])
            ||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
            ||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$expected
            ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){
            throw new RuntimeException('M83 exact independently derived reviewed update inventory differs.');
        }
        $seenChanges[$key]=true;
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
    $service = new App\Services\M83ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm83QuestionState'))->invoke($service, 'after', $patch, false);
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
$consumers=$load(App\Services\M83ContentPatch::CONSUMERS_FILE,App\Services\M83ContentPatch::CONSUMERS_SHA);
App\Services\M51ContentPatch::assertConsumerCapture($before,$consumers);
if(count($before['sql_before'])!==46||count($before['visual_hashes'])!==326||count($before['frozen_hashes'])!==2826
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']||($consumers['all_tables_after_http']??null)!==$before['sql_before']){
    throw new RuntimeException('M83 independent full-table/old-consumer baseline differs.');
}
$navigation=json_decode(file_get_contents($root.'/'.App\Support\M83B2RevisionBasicGrammar::NAVIGATION),true,flags:JSON_THROW_ON_ERROR);
if(($master['editorial_review']['navigation_review']??null)!==$navigation
    ||array_column($navigation['targets']??[],'identity')!==App\Support\M83B2RevisionBasicGrammar::OWNERS){
    throw new RuntimeException('M83 independent frozen footer navigation review differs.');
}
$navigationAdditions=0;$navigationChanges=[];
foreach($navigation['targets'] as $i=>$review){
    $slot=App\Support\M83B2RevisionBasicGrammar::NAVIGATION_SOURCE_INDICES[$i];
    $oldNavigation=json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);
    $newNavigation=$master['lessons'][$i]['blocks'][$slot]['data'];
    $labelCurrent=static fn(array $items)=>array_map(static fn(array $item)=>
        array_intersect_key($item,array_flip(['label','current'])),$items);
    if(($review['source_index']??null)!==$slot||($review['items']??null)!==($newNavigation['items']??null)
        ||$labelCurrent($newNavigation['items'])!==$labelCurrent($oldNavigation['items'])
        ||array_diff_key($newNavigation,['items'=>true])!==array_diff_key($oldNavigation,['items'=>true])){
        throw new RuntimeException('M83 footer title/labels/current/order differ from independent source and verified destinations.');
    }
    foreach($newNavigation['items'] as $n=>$item){
        $old=$oldNavigation['items'][$n];
        $beforeUrl=$old['url']??null;$afterUrl=$item['url']??null;
        $otherBefore=$old;$otherAfter=$item;unset($otherBefore['url'],$otherAfter['url']);
        if($otherBefore!==$otherAfter){throw new RuntimeException('M83 footer has a non-URL data change.');}
        if($beforeUrl!==$afterUrl){
            $navigationChanges[]=['identity'=>$review['identity'],'source_index'=>$slot,'item_index'=>$n,
                'before_url'=>$beforeUrl,'after_url'=>$afterUrl];
            if($beforeUrl===null&&is_string($afterUrl)){++$navigationAdditions;}
        }
    }
}
$lineageCheck=static function()use($root,$dir,$hash,$load,$normal):array{
    $captureSha='bae358ff481654fcd38be6a5802a9419f4b4edf7132c931e3e38ef82bc535618';
    $fixtureSha='e73158bc4d0ecc145865d86c8e72e8b08dda2234771ec7b4003b44413b475dcd';
    $courseSha='0d7129811de13e6991c84c5b6d6b2b62d837458ab95e352e87bc7cf6e33186ef';
    $capture=$load('independent-source-before-v1.json',$captureSha);
    $course=$load('course-source-before-v1.json',$courseSha);
    $wt=App\Services\M83ContentPatch::SOURCE_WORKTREE;$fixture=$wt.'/tests/Fixtures/m83-owned-source-hunks.json';
    if(!hash_equals($fixtureSha,$hash($fixture))){throw new RuntimeException('M83 independent source-hunk fixture bytes differ.');}
    $ledger=json_decode(file_get_contents($fixture),true,flags:JSON_THROW_ON_ERROR);
    if(($capture['base_sha']??null)!==App\Support\M83B2RevisionBasicGrammar::BASE_SHA
        ||($capture['phase']??null)!=='before'||count($capture['files']??[])!==3333
        ||($ledger['base_sha']??null)!==App\Support\M83B2RevisionBasicGrammar::BASE_SHA
        ||($ledger['independent_source_before_sha256']??null)!==$captureSha
        ||($ledger['independent_db_before_sha256']??null)!==App\Services\M83ContentPatch::BEFORE_SHA
        ||($ledger['independent_course_source_before_sha256']??null)!==$courseSha
        ||count($ledger['files']??[])!==8||($ledger['owned_added_registration_lines']??null)!==12
        ||($ledger['owned_validator_added_lines']??0)!==0||($ledger['owned_validator_removed_lines']??0)!==0
        ||($course['base_sha']??null)!==App\Support\M83B2RevisionBasicGrammar::BASE_SHA
        ||($course['phase']??null)!=='before'||count($course['files']??[])!==122){
        throw new RuntimeException('M83 fresh source/course BEFORE and finite shared lineage differ.');
    }
    $dispatch=['app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php'];
    if(array_column($ledger['files'],'path')!==$dispatch){throw new RuntimeException('M83 exact eight shared source paths differ.');}
    $checked=$unaffected=$courseChecked=0;
    foreach(['served_root'=>$root,'worktree'=>$wt]as $context=>$directory){
        if($normal($capture['roots'][$context]??'')!==$normal($directory)
            ||$normal($course['roots'][$context]??'')!==$normal($directory)){throw new RuntimeException('M83 independent source ROOT/WT identity differs.');}
        $addedCount=0;
        foreach($ledger['files']as $file){
            $path=$file['path'];$pin=$file['profiles'][$context]??null;$old=$capture['files'][$path][$context]??null;
            if(!is_array($pin)||!is_array($old)||($old['exists']??null)!==true
                ||($old['raw_sha256']??null)!==($pin['before_raw_sha256']??null)
                ||($old['lf_sha256']??null)!==($pin['before_lf_sha256']??null)){
                throw new RuntimeException('M83 predecessor source pin does not derive from independent BEFORE.');
            }
            if(!hash_equals($pin['after_raw_sha256'],$hash($directory.'/'.$path))){throw new RuntimeException('M83 shared raw AFTER source differs: '.$path);}
            $raw=file_get_contents($directory.'/'.$path);$lf=str_replace("\r\n","\n",$raw);
            if(hash('sha256',$lf)!==$pin['after_lf_sha256']||substr_count($raw,"\n")!==$pin['after_lf_line_count']
                ||substr_count($raw,"\r\n")!==$pin['after_crlf_line_count']){throw new RuntimeException('M83 shared LF/EOL profile differs.');}
            foreach($file['hunks']as $index=>$hunk){
                $added=substr($hunk['after'],strlen($hunk['before']));
                if($hunk['added_lines']!==1||$hunk['removed_lines']!==0||!str_starts_with($hunk['after'],$hunk['before'])
                    ||substr_count($added,"\n")!==1||!str_contains($added,'M83')||str_contains($hunk['before'],'M83')
                    ||substr_count($lf,$hunk['after'])!==1){throw new RuntimeException('M83 exact one-line registration hunk differs.');}
                $lf=str_replace($hunk['after'],$hunk['before'],$lf);
                $lines=preg_split('/(?<=\n)/',$raw,-1,PREG_SPLIT_NO_EMPTY);
                $normalized=array_map(static fn($line)=>str_replace("\r\n","\n",$line),$lines);
                $wanted=preg_split('/(?<=\n)/',$hunk['after'],-1,PREG_SPLIT_NO_EMPTY);$positions=[];
                foreach(array_keys($lines)as $n){if(array_slice($normalized,$n,count($wanted))===$wanted){$positions[]=$n;}}
                if(count($positions)!==1){throw new RuntimeException('M83 raw hunk location is missing or ambiguous.');}
                $position=$positions[0]+count($wanted)-1;$eol=$pin['hunk_added_line_eols'][$index]??null;
                if(!in_array($eol,['LF','CRLF'],true)||$lines[$position]!==($eol==='CRLF'?str_replace("\n","\r\n",$added):$added)){
                    throw new RuntimeException('M83 owned registration line EOL differs.');
                }
                array_splice($lines,$position,1);$raw=implode('',$lines);++$addedCount;
            }
            if(hash('sha256',$raw)!==$pin['before_raw_sha256']||hash('sha256',$lf)!==$pin['before_lf_sha256']){
                throw new RuntimeException('M83 reversing only owned hunks does not restore independent raw/LF BEFORE.');
            }
            ++$checked;
        }
        if($addedCount!==12){throw new RuntimeException('M83 exact shared registration addition count differs by context.');}
        $verifyUnchanged=static function(string $path,array $old)use($directory,$hash):void{
            $file=$directory.'/'.$path;
            if(($old['exists']??null)===false){if(file_exists($file)||is_link($file)){throw new RuntimeException('M83 foreign absent source was recreated: '.$path);}return;}
            if(($old['exists']??null)!==true||!hash_equals($old['raw_sha256'],$hash($file))){throw new RuntimeException('M83 non-target raw source changed: '.$path);}
            $raw=file_get_contents($file);
            if(strlen($raw)!==$old['bytes']||hash('sha256',str_replace("\r\n","\n",$raw))!==$old['lf_sha256']
                ||substr_count($raw,"\r\n")!==$old['crlf']||substr_count($raw,"\n")!==$old['lf']){throw new RuntimeException('M83 non-target LF/EOL source changed: '.$path);}
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
                throw new RuntimeException('M83 delivery attributes do not retain the exact existing raw prefix.');
            }
        }
    }
    return ['independent_source_before_sha256'=>$captureSha,'independent_source_before_paths'=>3333,
        'source_hunk_fixture_sha256'=>$fixtureSha,'finite_shared_files'=>8,'separate_contexts'=>2,
        'raw_lf_reversals'=>$checked,'owned_added_registration_lines_per_context'=>12,
        'native_release_validator_unchanged'=>true,
        'non_target_source_context_checks'=>$unaffected,'course_source_before_sha256'=>$courseSha,
        'course_source_context_checks'=>$courseChecked,'delivery_attributes'=>'Before prefix/foreign absence preserved; appended M83 EOL rules are a separate delivery review, not an unchanged-file claim.',
        'all_3333_paths_unchanged_claimed'=>false];
};
$lineageFirst=$lineageCheck();
$courseSqlCheck=static function($db)use($consumers,$same):array{
    $courses=$consumers['course_boundary']['courses']??[];
    if(array_keys($courses)!==['b2']){throw new RuntimeException('M83 actual B2 course boundary inventory is missing.');}
    $course=$courses['b2'];$savedCount=$memberships=0;$final=null;
    if(count($course['saved_tests']??[])!==16||count($course['question_memberships']??[])!==16
        ||count($course['manifest']['lessons']??[])!==16){
        throw new RuntimeException('M83 exact sixteen-lesson B2 course inventory differs.');
    }
    foreach($course['saved_tests'] as $slug=>$expected){
        $actual=(array)$db->table('saved_grammar_tests')->where('id',$expected['id'])->sole();
        $same($actual,$expected,'M83 actual B2 course saved-test metadata/timestamps changed.');++$savedCount;
        $links=$db->table('saved_grammar_test_questions')->where('saved_grammar_test_id',$expected['id'])
            ->orderBy('position')->get()->map(static fn($row)=>(array)$row)->all();
        if($links!==$course['question_memberships'][$slug]){
            throw new RuntimeException('M83 actual B2 course question membership/order/timestamps changed.');
        }
        $memberships+=count($links);
        if($slug==='polyglot-final-drill-b2'){
            $completion=json_decode($actual['filters'],true,flags:JSON_THROW_ON_ERROR)['completion']??null;
            if($completion!==['type'=>'score','minimum_rating_percent'=>80,'minimum_correct'=>38]){
                throw new RuntimeException('M83 actual raw B2 final score completion changed.');
            }
            $final=['saved_test_id'=>$expected['id'],'questions'=>count($links),'raw_completion'=>$completion,
                'normalized_public_completion'=>$course['manifest']['lessons'][15]['completion'],'unchanged'=>true];
        }
    }
    $expected=$course['questions'];
    $actual=$db->table('questions')->whereIn('uuid',array_column($expected,'uuid'))->orderBy('id')
        ->get()->map(static fn($row)=>(array)$row)->all();
    if($actual!==$expected){throw new RuntimeException('M83 B2 course original question rows changed.');}
    if($savedCount!==16||$memberships!==768||count($actual)!==768||($final['questions']??null)!==48
        ||$final['normalized_public_completion']!==['rolling_window'=>100,'min_rating'=>4.5]){
        throw new RuntimeException('M83 B2 actual lesson/final-drill bindings or counts differ.');
    }
    return ['course'=>'b2','lessons'=>16,'saved_test_bindings'=>$savedCount,'membership_rows'=>$memberships,
        'course_question_rows'=>count($actual),'final_drill'=>$final,'all_actual_rows_and_timestamps_exact'=>true,
        'enrollment_progress_attempts'=>'Every actual row is protected by exact reconstruction of all 46 SQL tables.',
        'course_runtime_ui'=>'Separate no-JS old-consumer receipt; pre-existing branded compose 404s are not successful interaction coverage.'];
};
$expectedSources=array_values(array_unique([...App\Services\M83ContentPatch::sourcePaths($patch,$contract,$root),
    'private:'.App\Services\M83ContentPatch::BEFORE_FILE,'private:'.App\Services\M83ContentPatch::CONSUMERS_FILE,'private:'.App\Services\M83ContentPatch::SYNC_RECEIPT]));
sort($expectedSources);$actualSources=array_keys($preview['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M83 full reviewed source/evidence list differs.');}
foreach ($preview['sources'] as $path => $sha) {
PHP;
$physical=<<<'PHP'
App\Services\M83LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.
$guard=new App\Services\M83LocalTargetGuard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key){if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1){throw new RuntimeException('M83 physical MySQL path is not local Windows.');}}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){throw new RuntimeException('M83 fresh physical MySQL identity differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
PHP;
$recheck=<<<'PHP'
} finally { $db->rollBack(); }
foreach($preview['sources'] as $path=>$sha){$file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M83 source/evidence changed during final audit.');}}
foreach([$before['physical_hashes'],$before['visual_hashes'],$before['frozen_hashes']] as $n=>$records){foreach($records as $path=>$sha){
    if(!hash_equals($sha,$hash($n===0?$path:$root.'/'.$path))){throw new RuntimeException('M83 physical/visual/frozen bytes changed during final audit.');}
}}
foreach($sync['files'] as $record){if($hash($root.'/'.$record['path'])!==$record['root_after_sha256']
    ||$hash(App\Services\M83ContentPatch::SOURCE_WORKTREE.'/'.$record['path'])!==$record['worktree_sha256']){throw new RuntimeException('M83 source-sync changed during final audit.');}}
if($lineageCheck()!==$lineageFirst){throw new RuntimeException('M83 independent raw/LF source lineage changed during final audit.');}
$repeatEvidence=null;
if($profileContext['priorName']!==null){
    $prior=$load($profileContext['priorName'],$profileContext['priorSha']);
    if(($prior['plan_digest']??null)!==$reviewedDigest||($prior['independent_before_sha256']??null)!==App\Services\M83ContentPatch::BEFORE_SHA
        ||($prior['backup_sha256']??null)!==$profileContext['backupSha']||($prior['sql_after']??null)!==$rawAfter
        ||($prior['normalized_before']??null)!==$normalized||($prior['protected']??null)!==$protected
        ||($prior['new_practices']??null)!==$practiceFacts||($prior['source_lineage']??null)!==$lineageFirst||($prior['course_boundary_sql']??null)!==$courseFacts
        ||($prior['approved_footer_url_additions']??null)!==$navigationAdditions||($prior['approved_footer_url_changes']??null)!==$navigationChanges
        ||($prior['all_old_rows_and_timestamps_exact']??null)!==true||($prior['db_writes']??null)!==0){
        throw new RuntimeException('M83 post-UI repeat differs from the exact prior successful read-only receipt.');
    }
    $repeatEvidence=['file'=>$profileContext['priorName'],'sha256'=>$profileContext['priorSha'],
        'all_46_after_fingerprints_exact'=>true,'new_practice_rows_exact'=>true,'source_lineage_exact'=>true];
}
if(!hash_equals($profileContext['backupSha'],$hash($dir.'/'.$profileContext['backupName']))
    ||file_get_contents($dir.'/'.$previewName)!==file_get_contents($dir.'/'.$profileContext['backupName'])){throw new RuntimeException('M83 exclusive backup changed during audit.');}
$receipt =
PHP;
$insertCheck=<<<'PHP'
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        if($i!==0||$target['identity']!==App\Support\M83B2RevisionBasicGrammar::OWNERS[0]
            ||$position!==App\Support\M83B2RevisionBasicGrammar::PRACTICE_SOURCE_INDICES[0]+1
            ||$position!==App\Support\M83B2RevisionBasicGrammar::CONTINUING_COUNTS[0]+1
            ||$source['type']!=='practice-set'||($source['level']??null)!=='B2'||$source['column']!=='left'
            ||isset($insertOwnerSeen[$target['identity']])){
            throw new RuntimeException('M83 only the one appended B2 practice binding is approved.');
        }
        $insertOwnerSeen[$target['identity']]=true;$page=$before['pages'][$target['identity']]['page'];
        $fields=['uuid'=>$uuid,'page_id'=>$page['id'],'page_category_id'=>$page['page_category_id'],'locale'=>'uk',
            'type'=>$source['type'],'column'=>$source['column'],'heading'=>$source['heading']??null,'css_class'=>$source['css_class']??null,
            'sort_order'=>$position,'body'=>$source['body'],'level'=>$source['level'],'seeder'=>$target['identity']];
        $same($insert['fields'],$fields,'M83 reviewed practice insert does not exactly match frozen source fields.');
PHP;
runFrozenReadOnlyProfile('m53-postconditions.php','8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',[
    ['M53PassiveConstructionsPackage','M83B2RevisionBasicGrammar',7],['M53','M83',15],['m53','m83',4],
    [$originalInventory,$inventory,1],[$originalQuestions,"    if(array_keys(\$insertOwnerSeen)!==[App\\Support\\M83B2RevisionBasicGrammar::OWNERS[0]]){throw new RuntimeException('M83 exact single appended B2 owner/order differs.');}\n    \$newRows=[]; \$approved=[]; // No question-family insertion or omission is authorized for M83.",1],
    ['    $newPractices = []; $practiceFacts = [];','    $newPractices = []; $practiceFacts = []; $insertOwnerSeen=[];',1],
    ['        $uuid = App\\Support\\M26DetailPackage::uuid($target[\'identity\'], $source, $position);',$insertCheck,1],
    ["\$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);","if(isset(\$before['schema'][\$table])&&\$db->getSchemaBuilder()->getColumns(\$table)!==\$before['schema'][\$table]){throw new RuntimeException('M83 captured SQL schema changed.');}\n        \$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);",1],
    ['foreach ($preview[\'sources\'] as $path => $sha) {',$extraSource,1],
    ["    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }",
        "    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }\n    \$courseFacts=\$courseSqlCheck(\$db);",1],
    ['App\\Services\\M83LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.',$physical,1],
    ["'question_plan_sha256' => App\\Services\\M83ContentPatch::QUESTION_PLAN_SHA,","'backup_file'=>\$profileContext['backupName'],'backup_sha256'=>\$profileContext['backupSha'],'backup_equals_reviewed_preview'=>true,\n    'source_lineage'=>\$lineageFirst,'post_ui_repeat'=>\$repeatEvidence,'course_boundary_sql'=>\$courseFacts,\n    'metadata_transitions'=>\$metadata,'update_operations'=>count(\$expectedChanges),'body_updates'=>\$bodyKinds,'question_family_writes'=>0,\n    'approved_footer_url_additions'=>\$navigationAdditions,'approved_footer_url_changes'=>\$navigationChanges,'all_footer_labels_order_current_preserved'=>true,",1],
    ["} finally { \$db->rollBack(); }\n\$receipt =",$recheck,1],
],['backupName'=>$backupName,'backupSha'=>$backupSha,'priorName'=>$priorName,'priorSha'=>$priorSha]);
