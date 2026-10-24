<?php

/**
 * Independent SELECT-only M85 reconstruction via the immutable M53 scanner.
 * Six owners, 51 continuing UK rows, six new practices, zero question-family writes.
 * No writer plan, apply, transaction hook or question-state method is invoked.
 * Args: preview, reviewed digest, backup, backup raw SHA, fresh final receipt.
 * Optionally append prior final receipt + raw SHA for an exact post-UI repeat.
 * Never calls a writer, apply/restore, seeder or HTTP endpoint.
 */
require __DIR__.'/frozen-readonly-profile.php';
$profileCheckOnly=PHP_SAPI==='cli'&&count($argv)===2&&$argv[1]==='--profile-check-only';
if($profileCheckOnly){$argv=[$argv[0],'preview-v1.json',str_repeat('0',64),'backup-v1.json',str_repeat('0',64),'final-readonly-v1.json'];}
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array(count($argv),[6,8],true)
    ||preg_match('/^preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1
    ||preg_match('/^backup-v[1-9][0-9]*\.json$/D',$argv[3])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[5])!==1){
    throw new RuntimeException('M85 reviewed preview/digest, exclusive backup/raw SHA, fresh final basename required.');
}
$backupName=$argv[3];$backupSha=$argv[4];$private='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m85-local';
$priorName=$argv[6]??null;$priorSha=$argv[7]??null;
if(($priorName!==null||$priorSha!==null)&&(count($argv)!==8
    ||!is_string($priorName)||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$priorName)!==1
    ||!is_string($priorSha)||preg_match('/^[a-f0-9]{64}$/D',$priorSha)!==1
    ||$priorName===$argv[5]||!is_file($private.'/'.$priorName)||is_link($private.'/'.$priorName)
    ||!hash_equals($priorSha,hash_file('sha256',$private.'/'.$priorName)))){
    throw new RuntimeException('M85 optional prior final receipt name/raw SHA differs; no repeat claim.');
}
if(!$profileCheckOnly){
foreach([$argv[1],$backupName] as $name){if(!is_file($private.'/'.$name)||is_link($private.'/'.$name)){throw new RuntimeException('M85 reviewed preview/backup absent or linked.');}}
if(!hash_equals($backupSha,hash_file('sha256',$private.'/'.$backupName))
    ||file_get_contents($private.'/'.$argv[1])!==file_get_contents($private.'/'.$backupName)){
    throw new RuntimeException('M85 exclusive backup bytes are not the exact reviewed preview.');
}
}
$argv=[$argv[0],$argv[1],$argv[2],$argv[5]];
$inventory=<<<'PHP'
    $pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $nonUk = 0;
    $expectedChanges=[];$bodyKinds=['subtitle'=>0,'teaching'=>0,'practice'=>0];$metadata=[];
    foreach ($patch['targets'] as $i => $target) {
        $owner=$target['identity'];$captured=$before['pages'][$owner];$oldPage=$captured['page'];
        $source=$sourceBefore['targets'][$i]['before'];
        if($oldPage['id']!==App\Support\M85VerbToBe::PAGE_IDS[$i]
            ||$oldPage['seeder']!==$owner||$oldPage['page_category_id']!==App\Support\M85VerbToBe::CATEGORY_IDS[$i]
            ||!array_key_exists('subtitle_text',$source['page'])||$oldPage['text']!==null
            ||$source['page']['subtitle_text']!==null||$target['after']['page']['subtitle_text']!==null){
            throw new RuntimeException('M85 independent Page/source identity or subtitle differs.');
        }
        foreach(['page','category_ancestry','blocks'] as $field){
            if(($preview['pages'][$owner][$field]??null)!==$captured[$field]){
                throw new RuntimeException('M85 reviewed original inventory differs from independent BEFORE.');
            }
        }
        if(($before['definitions'][$owner]['definition']??null)!==$source){
            throw new RuntimeException('M85 frozen source BEFORE differs from independent working capture.');
        }
        App\Services\M85ContentPatch::assertDefinitionScope($source,$target['after'],$target,$i);
        $pageBefore[$oldPage['id']]=$oldPage;
        $pageAfter[$oldPage['id']]=$oldPage;
        if(count($source['page']['blocks'])!==App\Support\M85VerbToBe::CONTINUING_COUNTS[$i]
            ||count($target['after']['page']['blocks'])!==App\Support\M85VerbToBe::CONTINUING_COUNTS[$i]+1
            ||App\Support\M85VerbToBe::PRACTICE_SOURCE_INDICES[$i]!==App\Support\M85VerbToBe::CONTINUING_COUNTS[$i]
            ||($captured['existing_practices']??null)!==[]){
            throw new RuntimeException('M85 exact no-old-practice and one-append source inventory differs.');
        }
        $positions=[];
        foreach($captured['blocks'] as $old){
            if($old['locale']!=='uk'){++$nonUk;continue;}
            $position=$old['sort_order'];
            if(!is_int($position)||$position<0||$position>App\Support\M85VerbToBe::CONTINUING_COUNTS[$i]
                ||isset($positions[$position])||isset($ukBefore[$old['id']])
                ||$old['seeder']!==$owner||$old['page_id']!==$oldPage['id']
                ||$old['page_category_id']!==$oldPage['page_category_id']){
                throw new RuntimeException('M85 exact continuing owner/locale/order inventory differs.');
            }
            $positions[$position]=true;
            $config=$position===0?['body'=>$target['after']['page']['subtitle_html'],'type'=>'subtitle','column'=>'header',
                'heading'=>null,'level'=>$source['page']['subtitle_level']??null,'css_class'=>null]
                :$target['after']['page']['blocks'][$position-1];
            $oldConfig=$position===0?['body'=>$source['page']['subtitle_html']]
                :$source['page']['blocks'][$position-1];
            if(!is_string($config['body']??null)||($oldConfig['body']??null)!==$old['body']){
                throw new RuntimeException('M85 independent source/SQL body correspondence differs.');
            }
            foreach(['type','column','heading','level','css_class'] as $field){
                if(($config[$field]??null)!==$old[$field]){
                    throw new RuntimeException('M85 no continuing metadata transition is approved: '.$field);
                }
            }
            if($position>0&&App\Support\M26DetailPackage::uuid($owner,$oldConfig,$position)!==$old['uuid']){
                throw new RuntimeException('M85 continuing source/SQL UUID correspondence differs.');
            }
            $ukBefore[$old['id']]=$old;$ukAfter[$old['id']]=array_replace($old,['body'=>$config['body']]);
            if($config['body']!==$old['body']){
                $expectedChanges['text_blocks:'.$old['id']]=['table'=>'text_blocks','id'=>$old['id'],'uuid'=>$old['uuid'],
                    'seeder'=>$owner,'before'=>['body'=>$old['body']],'after'=>['body'=>$config['body']]];
                $kind=$position===0?'subtitle':'teaching';
                ++$bodyKinds[$kind];
            }
        }
        $actualPositions=array_keys($positions);sort($actualPositions);
        if($actualPositions!==range(0,App\Support\M85VerbToBe::CONTINUING_COUNTS[$i])){
            throw new RuntimeException('M85 exact continuing native position vector differs.');
        }
    }
    $expectedUkRows=array_sum(array_map(static fn($count)=>$count+1,App\Support\M85VerbToBe::CONTINUING_COUNTS));
    if(count($pageBefore)!==6||$expectedUkRows!==51||count($ukBefore)!==$expectedUkRows||$nonUk!==102
        ||$metadata!==[]||count($expectedChanges)!==49||count($preview['updates']??[])!==count($expectedChanges)
        ||$bodyKinds!==['subtitle'=>4,'teaching'=>45,'practice'=>0]
        ||count($preview['inserts']??[])!==6||($preview['deletes']??[])!==[]
        ||array_key_exists('question_supplement',$preview)){
        throw new RuntimeException('M85 exact 49 body updates, six appends and zero-question scope differs.');
    }
    $seenChanges=[];
    foreach($preview['updates'] as $change){
        $key=$change['table'].':'.$change['id'];$expected=$expectedChanges[$key]??null;
        if($expected===null||isset($seenChanges[$key])
            ||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
            ||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$expected
            ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){
            throw new RuntimeException('M85 exact independently derived reviewed update inventory differs.');
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
    $service = new App\Services\M85ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm85QuestionState'))->invoke($service, 'after', $patch, false);
    if (($scope['evidence']['projection'] ?? null) !== ($preview['question_supplement']['evidence']['projection'] ?? null)
        || ($scope['evidence']['ids'] ?? null) !== ($preview['question_supplement']['evidence']['ids'] ?? null)) {
        throw new RuntimeException('Applied question projection differs from reviewed preview.');
    }
    $timestamp = $scope['actual']['questions'][0]['created_at'] ?? '';
    $newRows = App\Support\M51QuestionSupplement::materialize($scope['evidence']['projection'], $scope['evidence']['ids'], $timestamp);
    App\Support\M51QuestionSupplement::assertApplied($newRows, $scope['actual']);
    $approved = []; foreach ($newRows as $table => $rows) { $approved[$table] = array_column($rows, null, 'id'); }
PHP;
$questions=<<<'PHP'
    // No question-family rows are approved for insertion or mutation by M85.
    $newRows=[];$approved=[];
    if(array_keys($insertOwnerSeen)!==App\Support\M85VerbToBe::OWNERS){throw new RuntimeException('M85 six exact appended practices were not all verified.');}
PHP;
$extraSource=<<<'PHP'
if(App\Services\M85ContentPatch::CONSUMERS_SHA!=='10fd77a4d0e500333b83907c44f675dae629f026160731a9ad7825e77335f5b7'){
    throw new RuntimeException('M85 independently reviewed old-consumer capture pin differs.');
}
$consumers=$load(App\Services\M85ContentPatch::CONSUMERS_FILE,App\Services\M85ContentPatch::CONSUMERS_SHA);
App\Services\M51ContentPatch::assertConsumerCapture($before,$consumers);
if(count($before['sql_before'])!==46||count($before['visual_hashes'])!==326||count($before['frozen_hashes'])!==2884
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']||($consumers['all_tables_after_http']??null)!==$before['sql_before']){
    throw new RuntimeException('M85 independent full-table/old-consumer baseline differs.');
}
$navigation=json_decode(file_get_contents($root.'/'.App\Support\M85VerbToBe::NAVIGATION),true,flags:JSON_THROW_ON_ERROR);
if(($master['editorial_review']['navigation_review']??null)!==$navigation
    ||array_column($navigation['targets']??[],'identity')!==App\Support\M85VerbToBe::OWNERS){
    throw new RuntimeException('M85 independent frozen footer navigation review differs.');
}
$navigationAdditions=0;$navigationChanges=[];
foreach($navigation['targets'] as $i=>$review){
    $slot=App\Support\M85VerbToBe::NAVIGATION_SOURCE_INDICES[$i];
    $oldNavigation=json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);
    $newNavigation=$master['lessons'][$i]['blocks'][$slot]['data'];
    $labelCurrent=static fn(array $items)=>array_map(static fn(array $item)=>
        array_intersect_key($item,array_flip(['label','current'])),$items);
    if(($review['source_index']??null)!==$slot||($review['items']??null)!==($newNavigation['items']??null)
        ||$labelCurrent($newNavigation['items'])!==$labelCurrent($oldNavigation['items'])
        ||array_diff_key($newNavigation,['items'=>true])!==array_diff_key($oldNavigation,['items'=>true])){
        throw new RuntimeException('M85 footer title/labels/current/order differ from independent source and verified destinations.');
    }
    foreach($newNavigation['items'] as $n=>$item){
        $old=$oldNavigation['items'][$n];
        $beforeUrl=$old['url']??null;$afterUrl=$item['url']??null;
        $otherBefore=$old;$otherAfter=$item;unset($otherBefore['url'],$otherAfter['url']);
        if($otherBefore!==$otherAfter){throw new RuntimeException('M85 footer has a non-URL data change.');}
        if($beforeUrl!==$afterUrl){
            $navigationChanges[]=['identity'=>$review['identity'],'source_index'=>$slot,'item_index'=>$n,
                'before_url'=>$beforeUrl,'after_url'=>$afterUrl];
            if($beforeUrl===null&&is_string($afterUrl)){++$navigationAdditions;}
        }
    }
    if($i===2){
        $oldRelated=$sourceBefore['targets'][$i]['before']['page']['blocks'][7];
        $newRelated=$patch['targets'][$i]['after']['page']['blocks'][7];
        $oldData=json_decode($oldRelated['body'],true,flags:JSON_THROW_ON_ERROR);
        $newData=json_decode($newRelated['body'],true,flags:JSON_THROW_ON_ERROR);
        if(($newData['m85_native_v1']??null)!==['key'=>'verb-to-be-questions-7','detail_keys'=>[]]){
            throw new RuntimeException('M85 related-topic footer has a foreign native registration marker.');
        }
        unset($newData['m85_native_v1'],$oldRelated['body'],$newRelated['body']);
        if($oldRelated!==$newRelated||$oldData!==$newData){
            throw new RuntimeException('M85 second question-page related-topic navigation data and metadata must remain exact.');
        }
    }
}
$lineageCheck=static function()use($root,$dir,$hash,$load,$normal):array{
    $captureSha='7f554bebc5c15ea86bef65592d35da75cc1fdbec3631e1f3060de55926508fe1';
    $fixtureSha='e7debd4fd99d1c930aeefa52f7c78fc1d50d0bfb00d845571ae9252e52185352';
    $courseSha='6b8a6a974245af260486489f890062f29942235a4827d5db665d4c2f664d7c20';
    $capture=$load('independent-source-before-v1.json',$captureSha);
    $course=$load('course-source-before-v1.json',$courseSha);
    $wt=App\Services\M85ContentPatch::SOURCE_WORKTREE;$fixture=$wt.'/tests/Fixtures/m85-owned-source-hunks.json';
    if(!hash_equals($fixtureSha,$hash($fixture))){throw new RuntimeException('M85 independent source-hunk fixture bytes differ.');}
    $ledger=json_decode(file_get_contents($fixture),true,flags:JSON_THROW_ON_ERROR);
    if(($capture['base_sha']??null)!==App\Support\M85VerbToBe::BASE_SHA
        ||($capture['phase']??null)!=='before'||count($capture['files']??[])!==3458
        ||($ledger['base_sha']??null)!==App\Support\M85VerbToBe::BASE_SHA
        ||($ledger['independent_source_before_sha256']??null)!==$captureSha
        ||($ledger['independent_db_before_sha256']??null)!==App\Services\M85ContentPatch::BEFORE_SHA
        ||($ledger['independent_course_source_before_sha256']??null)!==$courseSha
        ||count($ledger['files']??[])!==8||($ledger['owned_added_registration_lines']??null)!==12
        ||($ledger['owned_validator_added_lines']??0)!==0||($ledger['owned_validator_removed_lines']??0)!==0
        ||($course['base_sha']??null)!==App\Support\M85VerbToBe::BASE_SHA
        ||($course['phase']??null)!=='before'||count($course['files']??[])!==179){
        throw new RuntimeException('M85 fresh source/course BEFORE and finite shared lineage differ.');
    }
    $dispatch=['app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php'];
    // These are new M85 tests already in progress when the independent capture ran;
    // ROOT absence and WT draft bytes are not accepted historical-source promises.
    $ownInProgress=['tests/Feature/M85LocalGuardTest.php','tests/Feature/M85SourceBoundaryTest.php',
        'tests/Feature/M85VerbToBeContentPatchTest.php','tests/Feature/M85VerbToBePackageTest.php'];
    $capturedOwn=array_values(array_filter(array_keys($capture['files']),static fn($path)=>str_contains($path,'M85')));sort($capturedOwn);
    $expectedOwn=$ownInProgress;sort($expectedOwn);
    if($capturedOwn!==$expectedOwn){throw new RuntimeException('M85 captured in-progress own-test path set differs.');}
    foreach($ownInProgress as $path){if(($capture['files'][$path]['served_root']['exists']??null)!==false
        ||($capture['files'][$path]['worktree']['exists']??null)!==true){throw new RuntimeException('M85 in-progress test provenance differs.');}}
    if(array_column($ledger['files'],'path')!==$dispatch){throw new RuntimeException('M85 exact eight shared source paths differ.');}
    $checked=$unaffected=$courseChecked=0;
    foreach(['served_root'=>$root,'worktree'=>$wt]as $context=>$directory){
        if($normal($capture['roots'][$context]??'')!==$normal($directory)
            ||$normal($course['roots'][$context]??'')!==$normal($directory)){throw new RuntimeException('M85 independent source ROOT/WT identity differs.');}
        $visual=[];
        foreach(['resources/views','resources/css','resources/js','public/js'] as $folder){
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory.'/'.$folder,FilesystemIterator::SKIP_DOTS)) as $file){
                if($file->isLink()){throw new RuntimeException('M85 visual inventory contains a link.');}
                if($file->isFile()){$visual[]=substr(str_replace('\\','/',$file->getPathname()),strlen($directory)+1);}
            }
        }
        $expectedVisual=$capture['groups']['visual']??[];sort($visual);sort($expectedVisual);
        if(count($expectedVisual)!==326||$visual!==$expectedVisual){throw new RuntimeException('M85 complete ROOT/WT visual inventory changed.');}
        $addedCount=0;
        foreach($ledger['files']as $file){
            $path=$file['path'];$pin=$file['profiles'][$context]??null;$old=$capture['files'][$path][$context]??null;
            if(!is_array($pin)||!is_array($old)||($old['exists']??null)!==true
                ||($old['raw_sha256']??null)!==($pin['before_raw_sha256']??null)
                ||($old['lf_sha256']??null)!==($pin['before_lf_sha256']??null)){
                throw new RuntimeException('M85 predecessor source pin does not derive from independent BEFORE.');
            }
            if(!hash_equals($pin['after_raw_sha256'],$hash($directory.'/'.$path))){throw new RuntimeException('M85 shared raw AFTER source differs: '.$path);}
            $raw=file_get_contents($directory.'/'.$path);$lf=str_replace("\r\n","\n",$raw);
            if(hash('sha256',$lf)!==$pin['after_lf_sha256']||substr_count($raw,"\n")!==$pin['after_lf_line_count']
                ||substr_count($raw,"\r\n")!==$pin['after_crlf_line_count']){throw new RuntimeException('M85 shared LF/EOL profile differs.');}
            foreach($file['hunks']as $index=>$hunk){
                $added=substr($hunk['after'],strlen($hunk['before']));
                if($hunk['added_lines']!==1||$hunk['removed_lines']!==0||!str_starts_with($hunk['after'],$hunk['before'])
                    ||substr_count($added,"\n")!==1||!str_contains($added,'M85')||str_contains($hunk['before'],'M85')
                    ||substr_count($lf,$hunk['after'])!==1){throw new RuntimeException('M85 exact one-line registration hunk differs.');}
                $lf=str_replace($hunk['after'],$hunk['before'],$lf);
                $lines=preg_split('/(?<=\n)/',$raw,-1,PREG_SPLIT_NO_EMPTY);
                $normalized=array_map(static fn($line)=>str_replace("\r\n","\n",$line),$lines);
                $wanted=preg_split('/(?<=\n)/',$hunk['after'],-1,PREG_SPLIT_NO_EMPTY);$positions=[];
                foreach(array_keys($lines)as $n){if(array_slice($normalized,$n,count($wanted))===$wanted){$positions[]=$n;}}
                if(count($positions)!==1){throw new RuntimeException('M85 raw hunk location is missing or ambiguous.');}
                $position=$positions[0]+count($wanted)-1;
                if($lines[$position]!==$added&&$lines[$position]!==str_replace("\n","\r\n",$added)){
                    throw new RuntimeException('M85 owned registration line has an unapproved raw EOL.');
                }
                array_splice($lines,$position,1);$raw=implode('',$lines);++$addedCount;
            }
            if(hash('sha256',$raw)!==$pin['before_raw_sha256']||hash('sha256',$lf)!==$pin['before_lf_sha256']){
                throw new RuntimeException('M85 reversing only owned hunks does not restore independent raw/LF BEFORE.');
            }
            ++$checked;
        }
        if($addedCount!==12){throw new RuntimeException('M85 exact shared registration addition count differs by context.');}
        $verifyUnchanged=static function(string $path,array $old)use($directory,$hash):void{
            $file=$directory.'/'.$path;
            if(($old['exists']??null)===false){if(file_exists($file)||is_link($file)){throw new RuntimeException('M85 foreign absent source was recreated: '.$path);}return;}
            if(($old['exists']??null)!==true||!hash_equals($old['raw_sha256'],$hash($file))){throw new RuntimeException('M85 non-target raw source changed: '.$path);}
            $raw=file_get_contents($file);
            if(strlen($raw)!==$old['bytes']||hash('sha256',str_replace("\r\n","\n",$raw))!==$old['lf_sha256']
                ||substr_count($raw,"\r\n")!==$old['crlf']||substr_count($raw,"\n")!==$old['lf']){throw new RuntimeException('M85 non-target LF/EOL source changed: '.$path);}
        };
        foreach($capture['files']as $path=>$profiles){
            if(in_array($path,$dispatch,true)||in_array($path,$ownInProgress,true)
                ||in_array($path,array_map(App\Support\TheorySentenceBuilderAcceptance::definitionPath(...),App\Support\M85VerbToBe::OWNERS),true)||$path==='.gitattributes'){continue;}
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
                throw new RuntimeException('M85 delivery attributes do not retain the exact existing raw prefix.');
            }
        }
    }
    return ['independent_source_before_sha256'=>$captureSha,'independent_source_before_paths'=>3458,
        'source_hunk_fixture_sha256'=>$fixtureSha,'finite_shared_files'=>8,'separate_contexts'=>2,
        'raw_lf_reversals'=>$checked,'owned_added_registration_lines_per_context'=>12,
        'native_release_validator_unchanged'=>true,
        'non_target_source_context_checks'=>$unaffected,'course_source_before_sha256'=>$courseSha,
        'course_source_before_paths'=>179,'course_source_context_checks'=>$courseChecked,
        'captured_new_m85_tests_excluded_from_historical_unchanged_claim'=>$ownInProgress,
        'new_tests_delivery_validation'=>'Separate final owned-file SHA review and isolated test receipts; no historical BEFORE equality claim for the four M85 drafts.',
        'delivery_attributes'=>'Before prefix/foreign absence preserved; appended M85 EOL rules are a separate delivery review, not an unchanged-file claim.',
        'all_3458_paths_unchanged_claimed'=>false];
};
$lineageFirst=$lineageCheck();
$oldConsumerSqlCheck=static function($db)use($before,$consumers):array{
    $banks=[];
    foreach($before['pages'] as $page){foreach(array_keys($page['bank_inventory']??[]) as $bank){$banks[]=$bank;}}
    $banks=array_values(array_unique($banks));sort($banks);
    $old=$consumers['old_sql']??[];$capturedBanks=array_values(array_unique(array_column($old['questions']??[],'seeder')));sort($capturedBanks);
    $declaredBanks=array_keys($old['source_bank_declarations']??[]);sort($declaredBanks);
    $observedBanks=array_keys($old['source_set_observations']??[]);sort($observedBanks);
    if(count($banks)!==20||$banks!==$capturedBanks||$banks!==$declaredBanks||$banks!==$observedBanks
        ||($consumers['source_bank_count']??null)!==20||count($old['questions']??[])!==1675
        ||count($old['saved_tests']??[])!==17||array_keys($old['saved_tests'])!==array_keys($old['saved_question_links']??[])
        ||array_keys($old['saved_tests'])!==array_keys($old['saved_pool_declarations']??[])){
        throw new RuntimeException('M85 exact twenty original banks and seventeen saved pools BEFORE inventory differs.');
    }
    $rows=static fn($q)=>$q->get()->map(static fn($r)=>(array)$r)->all();
    $ordered=static function(string $table,$query,array $priority=[])use($db,$rows):array{
        $columns=$db->getSchemaBuilder()->getColumnListing($table);
        $order=array_values(array_unique([...$priority,...(in_array('id',$columns,true)?['id']:$columns)]));
        foreach($order as $column){if(!in_array($column,$columns,true)){throw new RuntimeException('M85 old-consumer sort column differs.');}$query->orderBy($column);}
        return $rows($query);
    };
    $questions=$rows($db->table('questions')->whereIn('seeder',$banks)->orderBy('id'));
    if($questions!==$old['questions']){throw new RuntimeException('M85 full original bank question rows/timestamps changed.');}
    $actual=['questions'=>$questions,'source_set_observations'=>$old['source_set_observations'],
        'source_bank_declarations'=>$old['source_bank_declarations'],'saved_pool_declarations'=>$old['saved_pool_declarations']];
    $ids=array_column($questions,'id');$uuids=array_column($questions,'uuid');
    foreach(['question_answers','question_option_question','verb_hints','question_hints','question_variants','question_tag','question_marker_tag'] as $table){
        $actual[$table]=$ordered($table,$db->table($table)->whereIn('question_id',$ids));
    }
    $optionIds=array_unique([...array_column($actual['question_answers'],'option_id'),
        ...array_column($actual['question_option_question'],'option_id'),...array_column($actual['verb_hints'],'option_id')]);
    $actual['question_options']=$rows($db->table('question_options')->whereIn('id',$optionIds)->orderBy('id'));
    $actual['question_theory_text_blocks']=$ordered('question_theory_text_blocks',
        $db->table('question_theory_text_blocks')->whereIn('question_uuid',$uuids),['question_uuid','position','text_block_uuid']);
    $actual['saved_tests']=[];$actual['saved_question_links']=[];$memberships=0;
    foreach($old['saved_tests'] as $slug=>$test){
        $actual['saved_tests'][$slug]=(array)$db->table('saved_grammar_tests')->where('slug',$slug)->sole();
        $actual['saved_question_links'][$slug]=$ordered('saved_grammar_test_questions',
            $db->table('saved_grammar_test_questions')->where('saved_grammar_test_id',$test['id']),['position']);
        $memberships+=count($actual['saved_question_links'][$slug]);
    }
    if($actual!==$old){throw new RuntimeException('M85 an original bank relation, pivot, shared option or saved membership/order/timestamp changed.');}
    return ['original_banks'=>20,'original_questions'=>1675,'saved_tests'=>17,'saved_memberships'=>$memberships,
        'question_scope'=>'All rows assigned to the twenty discovered seeders, not only the 736 page-linked question IDs.',
        'source_sql_differences_preserved_not_repaired'=>true,
        'row_families'=>array_map('count',$actual),'all_original_rows_and_timestamps_exact'=>true,
        'all_old_banks_and_standalone_memberships_unchanged'=>true,
        'course_progress_enrollment_attempts'=>'Every actual row is protected by exact reconstruction of all 46 SQL tables; no runtime course interaction claim.'];
};
$schemaBefore=$load('schema-before-v1.json','7458fafc390d615a1b11ee7467b3004a51f1b50e12032426bf5b716de0d17174');
if(($schemaBefore['kind']??null)!=='M85 fresh SELECT-only schema BEFORE'
    ||($schemaBefore['base_sha']??null)!==App\Support\M85VerbToBe::BASE_SHA
    ||($schemaBefore['before_sha256']??null)!==App\Services\M85ContentPatch::BEFORE_SHA
    ||($schemaBefore['identity']??null)!==$before['identity']||($schemaBefore['sql_before']??null)!==$before['sql_before']
    ||array_keys($schemaBefore['schema']??[])!==array_keys($before['sql_before'])
    ||($schemaBefore['db_writes']??null)!==0||($schemaBefore['http_requests']??null)!==0){
    throw new RuntimeException('M85 fresh complete forty-six-table schema BEFORE differs.');
}
$expectedSources=array_values(array_unique([...App\Services\M85ContentPatch::sourcePaths($patch,$contract,$root),
    'private:'.App\Services\M85ContentPatch::BEFORE_FILE,'private:'.App\Services\M85ContentPatch::CONSUMERS_FILE,'private:'.App\Services\M85ContentPatch::SYNC_RECEIPT]));
sort($expectedSources);$actualSources=array_keys($preview['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M85 full reviewed source/evidence list differs.');}
foreach ($preview['sources'] as $path => $sha) {
PHP;
$physical=<<<'PHP'
App\Services\M85LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.
$guard=new App\Services\M85LocalTargetGuard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key){if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1){throw new RuntimeException('M85 physical MySQL path is not local Windows.');}}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){throw new RuntimeException('M85 fresh physical MySQL identity differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
PHP;
$recheck=<<<'PHP'
} finally { $db->rollBack(); }
if(!hash_equals('7458fafc390d615a1b11ee7467b3004a51f1b50e12032426bf5b716de0d17174',$hash($dir.'/schema-before-v1.json'))){
    throw new RuntimeException('M85 complete schema BEFORE bytes changed during final audit.');
}
foreach($preview['sources'] as $path=>$sha){$file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M85 source/evidence changed during final audit.');}}
foreach([$before['physical_hashes'],$before['visual_hashes'],$before['frozen_hashes']] as $n=>$records){foreach($records as $path=>$sha){
    if(!hash_equals($sha,$hash($n===0?$path:$root.'/'.$path))){throw new RuntimeException('M85 physical/visual/frozen bytes changed during final audit.');}
}}
foreach($sync['files'] as $record){if($hash($root.'/'.$record['path'])!==$record['root_after_sha256']
    ||$hash(App\Services\M85ContentPatch::SOURCE_WORKTREE.'/'.$record['path'])!==$record['worktree_sha256']){throw new RuntimeException('M85 source-sync changed during final audit.');}}
if($lineageCheck()!==$lineageFirst){throw new RuntimeException('M85 independent raw/LF source lineage changed during final audit.');}
$repeatEvidence=null;
if($profileContext['priorName']!==null){
    $prior=$load($profileContext['priorName'],$profileContext['priorSha']);
    if(($prior['plan_digest']??null)!==$reviewedDigest||($prior['independent_before_sha256']??null)!==App\Services\M85ContentPatch::BEFORE_SHA
        ||($prior['backup_sha256']??null)!==$profileContext['backupSha']||($prior['sql_after']??null)!==$rawAfter
        ||($prior['normalized_before']??null)!==$normalized||($prior['protected']??null)!==$protected
        ||($prior['new_practices']??null)!==$practiceFacts||($prior['source_lineage']??null)!==$lineageFirst||($prior['old_consumer_sql']??null)!==$consumerFacts
        ||($prior['approved_footer_url_additions']??null)!==$navigationAdditions||($prior['approved_footer_url_changes']??null)!==$navigationChanges
        ||($prior['all_old_rows_and_timestamps_exact']??null)!==true||($prior['db_writes']??null)!==0){
        throw new RuntimeException('M85 post-UI repeat differs from the exact prior successful read-only receipt.');
    }
    $repeatEvidence=['file'=>$profileContext['priorName'],'sha256'=>$profileContext['priorSha'],
        'all_46_after_fingerprints_exact'=>true,'new_practice_rows_exact'=>true,'source_lineage_exact'=>true];
}
if(!hash_equals($profileContext['backupSha'],$hash($dir.'/'.$profileContext['backupName']))
    ||file_get_contents($dir.'/'.$previewName)!==file_get_contents($dir.'/'.$profileContext['backupName'])){throw new RuntimeException('M85 exclusive backup changed during audit.');}
$receipt =
PHP;
$insertCheck=<<<'PHP'
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        if($target['identity']!==App\Support\M85VerbToBe::OWNERS[$i]
            ||$position!==App\Support\M85VerbToBe::PRACTICE_SOURCE_INDICES[$i]+1
            ||$position!==App\Support\M85VerbToBe::CONTINUING_COUNTS[$i]+1
            ||$source['type']!=='practice-set'||!array_key_exists('level',$source)||$source['level']!==null||$source['column']!=='left'
            ||isset($insertOwnerSeen[$target['identity']])){
            throw new RuntimeException('M85 only one appended SQL NULL left practice per exact owner is approved.');
        }
        $insertOwnerSeen[$target['identity']]=true;$page=$before['pages'][$target['identity']]['page'];
        $fields=['uuid'=>$uuid,'page_id'=>$page['id'],'page_category_id'=>$page['page_category_id'],'locale'=>'uk',
            'type'=>$source['type'],'column'=>$source['column'],'heading'=>$source['heading']??null,'css_class'=>$source['css_class']??null,
            'sort_order'=>$position,'body'=>$source['body'],'level'=>$source['level'],'seeder'=>$target['identity']];
        $same($insert['fields'],$fields,'M85 reviewed practice insert does not exactly match frozen source fields.');
PHP;
$profile=[
    ['M53PassiveConstructionsPackage','M85VerbToBe',7],['M53','M85',15],['m53','m85',4],
    [$originalInventory,$inventory,1],[$originalQuestions,$questions,1],
    ['    $newPractices = []; $practiceFacts = [];','    $newPractices = []; $practiceFacts = []; $insertOwnerSeen=[];',1],
    ['        $uuid = App\\Support\\M26DetailPackage::uuid($target[\'identity\'], $source, $position);',$insertCheck,1],
    ["\$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);","if(!isset(\$schemaBefore['schema'][\$table])||\$db->getSchemaBuilder()->getColumns(\$table)!==\$schemaBefore['schema'][\$table]){throw new RuntimeException('M85 full captured SQL schema changed.');}\n        \$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);",1],
    ['foreach ($preview[\'sources\'] as $path => $sha) {',$extraSource,1],
    ["    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }",
        "    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }\n    \$consumerFacts=\$oldConsumerSqlCheck(\$db);",1],
    ['App\\Services\\M85LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.',$physical,1],
    ["'question_plan_sha256' => App\\Services\\M85ContentPatch::QUESTION_PLAN_SHA,","'backup_file'=>\$profileContext['backupName'],'backup_sha256'=>\$profileContext['backupSha'],'backup_equals_reviewed_preview'=>true,\n    'source_lineage'=>\$lineageFirst,'post_ui_repeat'=>\$repeatEvidence,'old_consumer_sql'=>\$consumerFacts,\n    'metadata_transitions'=>\$metadata,'update_operations'=>count(\$expectedChanges),'body_updates'=>\$bodyKinds,'question_family_insert_counts'=>array_map('count',\$newRows),'question_family_insert_rows'=>0,'original_question_rows_updated'=>0,'full_schema_tables_checked'=>46,\n    'approved_footer_url_additions'=>\$navigationAdditions,'approved_footer_url_changes'=>\$navigationChanges,'all_footer_labels_order_current_preserved'=>true,",1],
    ["} finally { \$db->rollBack(); }\n\$receipt =",$recheck,1],
];
if($profileCheckOnly){
    $immutable=__DIR__.'/m53-postconditions.php';$source=file_get_contents($immutable);
    if(is_link($immutable)||!hash_equals('8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',hash('sha256',$source))){throw new RuntimeException('M85 immutable scanner bytes differ.');}
    foreach($profile as [$needle,$replacement,$count]){if(substr_count($source,$needle)!==$count){throw new RuntimeException('M85 pure profile substitution count differs: '.substr($needle,0,90));}$source=str_replace($needle,$replacement,$source);}
    token_get_all($source,TOKEN_PARSE);
    echo json_encode(['profile'=>'M85 immutable SELECT-only scanner','literal_substitutions'=>count($profile),'composed_php_parsed'=>true,
        'db_bootstraps'=>0,'db_writes'=>0,'http_requests'=>0,'private_evidence_opened'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
    exit(0);
}
runFrozenReadOnlyProfile('m53-postconditions.php','8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',$profile,
    ['backupName'=>$backupName,'backupSha'=>$backupSha,'priorName'=>$priorName,'priorSha'=>$priorSha]);
