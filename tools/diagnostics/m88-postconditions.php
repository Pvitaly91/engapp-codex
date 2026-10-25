<?php

/**
 * Independent SELECT-only M88 reconstruction via the immutable M53 scanner.
 * Six owners, 51 continuing UK rows, two retained practices, four new practices, zero question-family writes.
 * No writer plan, apply, transaction hook or question-state method is invoked.
 * Args: preview, reviewed digest, backup, backup raw SHA, fresh final receipt.
 * Optionally append prior final receipt + raw SHA for an exact post-UI repeat.
 * Never calls a writer, apply/restore, seeder or HTTP endpoint.
 */
$diagnostics='D:/DEV/htdocs/gramlyze.loc/tools/diagnostics';
require $diagnostics.'/frozen-readonly-profile.php';
$profileCheckOnly=PHP_SAPI==='cli'&&count($argv)===2&&$argv[1]==='--profile-check-only';
if($profileCheckOnly){$argv=[$argv[0],'preview-v1.json',str_repeat('0',64),'backup-v1.json',str_repeat('0',64),'final-readonly-v1.json'];}
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array(count($argv),[6,8],true)
    ||preg_match('/^preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1
    ||preg_match('/^backup-v[1-9][0-9]*\.json$/D',$argv[3])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[5])!==1){
    throw new RuntimeException('M88 reviewed preview/digest, exclusive backup/raw SHA, fresh final basename required.');
}
$backupName=$argv[3];$backupSha=$argv[4];$private='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m88-local';
$priorName=$argv[6]??null;$priorSha=$argv[7]??null;
if(($priorName!==null||$priorSha!==null)&&(count($argv)!==8
    ||!is_string($priorName)||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$priorName)!==1
    ||!is_string($priorSha)||preg_match('/^[a-f0-9]{64}$/D',$priorSha)!==1
    ||$priorName===$argv[5]||!is_file($private.'/'.$priorName)||is_link($private.'/'.$priorName)
    ||!hash_equals($priorSha,hash_file('sha256',$private.'/'.$priorName)))){
    throw new RuntimeException('M88 optional prior final receipt name/raw SHA differs; no repeat claim.');
}
if(!$profileCheckOnly){
foreach([$argv[1],$backupName] as $name){if(!is_file($private.'/'.$name)||is_link($private.'/'.$name)){throw new RuntimeException('M88 reviewed preview/backup absent or linked.');}}
if(!hash_equals($backupSha,hash_file('sha256',$private.'/'.$backupName))
    ||file_get_contents($private.'/'.$argv[1])!==file_get_contents($private.'/'.$backupName)){
    throw new RuntimeException('M88 exclusive backup bytes are not the exact reviewed preview.');
}
}
$argv=[$argv[0],$argv[1],$argv[2],$argv[5]];
$inventory=<<<'PHP'
    $pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $nonUk = 0;
    $expectedChanges=[];$bodyKinds=['subtitle'=>0,'teaching'=>0];$metadata=[];$retainedPracticeFacts=[];
    foreach ($patch['targets'] as $i => $target) {
        $owner=$target['identity'];$captured=$before['pages'][$owner];$oldPage=$captured['page'];
        $source=$sourceBefore['targets'][$i]['before'];
        if($oldPage['id']!==App\Support\M88PastContinuousPresentPerfectBasics::PAGE_IDS[$i]
            ||$oldPage['seeder']!==$owner||$oldPage['page_category_id']!==App\Support\M88PastContinuousPresentPerfectBasics::CATEGORY_IDS[$i]
            ||!array_key_exists('subtitle_text',$source['page'])||$oldPage['text']!==null
            ||$source['page']['subtitle_text']!==null||$target['after']['page']['subtitle_text']!==null){
            throw new RuntimeException('M88 independent Page/source identity or subtitle differs.');
        }
        foreach(['page','category_ancestry','blocks'] as $field){
            if(($preview['pages'][$owner][$field]??null)!==$captured[$field]){
                throw new RuntimeException('M88 reviewed original inventory differs from independent BEFORE.');
            }
        }
        if(($before['definitions'][$owner]['definition']??null)!==$source){
            throw new RuntimeException('M88 frozen source BEFORE differs from independent working capture.');
        }
        App\Services\M88ContentPatch::assertDefinitionScope($source,$target['after'],$target,$i);
        $pageBefore[$oldPage['id']]=$oldPage;
        $pageAfter[$oldPage['id']]=$oldPage;
        $capturedPractices=array_values(array_filter($captured['blocks'],static fn($row)=>$row['locale']==='uk'&&$row['type']==='practice-set'));
        if(count($source['page']['blocks'])!==App\Support\M88PastContinuousPresentPerfectBasics::CONTINUING_COUNTS[$i]
            ||count($target['after']['page']['blocks'])!==App\Support\M88PastContinuousPresentPerfectBasics::CONTINUING_COUNTS[$i]+($i>=4?0:1)
            ||($captured['existing_practices']??null)!==$capturedPractices
            ||count($capturedPractices)!==($i>=4?1:0)){
            throw new RuntimeException('M88 exact two continuing E/F and four absent A-D practice inventories differ.');
        }
        if($i>=4){
            $practice=$capturedPractices[0];
            if($practice['id']!==[null,null,null,null,10584,10611][$i]||$practice['uuid']!==App\Support\M88PastContinuousPresentPerfectBasics::EXISTING_PRACTICE_UUIDS[$i]
                ||$practice['sort_order']!==App\Support\M88PastContinuousPresentPerfectBasics::PRACTICE_SOURCE_INDICES[$i]+1
                ||$practice['column']!=='left'||$practice['level']!==null){
                throw new RuntimeException('M88 retained E/F practice owner/UUID/order/NULL metadata differs.');
            }
            $retainedPracticeFacts[]=['owner'=>$owner,'id'=>$practice['id'],'uuid'=>$practice['uuid'],'sort_order'=>$practice['sort_order']];
        }
        $positions=[];
        foreach($captured['blocks'] as $old){
            if($old['locale']!=='uk'){++$nonUk;continue;}
            $position=$old['sort_order'];
            if(!is_int($position)||$position<0||$position>App\Support\M88PastContinuousPresentPerfectBasics::CONTINUING_COUNTS[$i]
                ||isset($positions[$position])||isset($ukBefore[$old['id']])
                ||$old['seeder']!==$owner||$old['page_id']!==$oldPage['id']
                ||$old['page_category_id']!==$oldPage['page_category_id']){
                throw new RuntimeException('M88 exact continuing owner/locale/order inventory differs.');
            }
            $positions[$position]=true;
            $config=$position===0?['body'=>$target['after']['page']['subtitle_html'],'type'=>'subtitle','column'=>'header',
                'heading'=>null,'level'=>$source['page']['subtitle_level']??null,'css_class'=>null]
                :$target['after']['page']['blocks'][$position-1];
            $oldConfig=$position===0?['body'=>$source['page']['subtitle_html']]
                :$source['page']['blocks'][$position-1];
            if(!is_string($config['body']??null)||($oldConfig['body']??null)!==$old['body']){
                throw new RuntimeException('M88 independent source/SQL body correspondence differs.');
            }
            foreach(['type','column','heading','level','css_class'] as $field){
                if(($config[$field]??null)!==$old[$field]){
                    throw new RuntimeException('M88 no continuing metadata transition is approved: '.$field);
                }
            }
            if($position>0&&App\Support\M26DetailPackage::uuid($owner,$oldConfig,$position)!==$old['uuid']){
                throw new RuntimeException('M88 continuing source/SQL UUID correspondence differs.');
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
        if($actualPositions!==range(0,App\Support\M88PastContinuousPresentPerfectBasics::CONTINUING_COUNTS[$i])){
            throw new RuntimeException('M88 exact continuing native position vector differs.');
        }
    }
    $expectedUkRows=array_sum(array_map(static fn($count)=>$count+1,App\Support\M88PastContinuousPresentPerfectBasics::CONTINUING_COUNTS));
    if(count($pageBefore)!==6||$expectedUkRows!==51||count($ukBefore)!==$expectedUkRows||$nonUk!==102
        ||$metadata!==[]||count($retainedPracticeFacts)!==2||count($expectedChanges)!==45||count($preview['updates']??[])!==count($expectedChanges)
        ||$bodyKinds!==['subtitle'=>0,'teaching'=>45]
        ||count($preview['inserts']??[])!==4||($preview['deletes']??[])!==[]
        ||array_key_exists('question_supplement',$preview)){
        throw new RuntimeException('M88 exact45 continuing body updates (all six subtitles unchanged), four A-D appends and zero-question scope differs.');
    }
    $seenChanges=[];
    foreach($preview['updates'] as $change){
        $key=$change['table'].':'.$change['id'];$expected=$expectedChanges[$key]??null;
        if($expected===null||isset($seenChanges[$key])
            ||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
            ||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$expected
            ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){
            throw new RuntimeException('M88 exact independently derived reviewed update inventory differs.');
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
    $service = new App\Services\M88ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm88QuestionState'))->invoke($service, 'after', $patch, false);
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
    // No question-family rows are approved for insertion or mutation by M88.
    $newRows=[];$approved=[];
    if(array_keys($insertOwnerSeen)!==array_slice(App\Support\M88PastContinuousPresentPerfectBasics::OWNERS,0,4)){throw new RuntimeException('M88 only the four exact A-D appended practices may be verified.');}
PHP;
$extraSource=<<<'PHP'
if(App\Services\M88ContentPatch::CONSUMERS_SHA!=='9c245aeb1c7cf1b067cc3a6d6d154fa60570446b04d6fba0279a7cdb1b9c1f77'){
    throw new RuntimeException('M88 independently reviewed old-consumer capture pin differs.');
}
$consumers=$load(App\Services\M88ContentPatch::CONSUMERS_FILE,App\Services\M88ContentPatch::CONSUMERS_SHA);
App\Services\M51ContentPatch::assertConsumerCapture($before,$consumers);
if(count($before['sql_before'])!==46||count($before['visual_hashes'])!==326||count($before['frozen_hashes'])!==2957
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']||($consumers['all_tables_after_http']??null)!==$before['sql_before']){
    throw new RuntimeException('M88 independent full-table/old-consumer baseline differs.');
}
$navigation=json_decode(file_get_contents($root.'/'.App\Support\M88PastContinuousPresentPerfectBasics::NAVIGATION),true,flags:JSON_THROW_ON_ERROR);
if(($master['editorial_review']['navigation_review']??null)!==$navigation
    ||array_column($navigation['targets']??[],'identity')!==App\Support\M88PastContinuousPresentPerfectBasics::OWNERS){
    throw new RuntimeException('M88 independent frozen footer navigation review differs.');
}
$navigationReceipt=$load('navigation-review-v1.json','c7a03238607c26c754df20f4d61dd34dae7d8b9b0dbdfe863bf5730c2810938b');
$secondaryReceipt=$load('secondary-navigation-review-v1.json','3b07a36eacabd414f68f2bd3bccc9a3d3ad922ad9adf6ee85c389076ae2d682e');
if(($navigation['receipt_sha256']??null)!=='c7a03238607c26c754df20f4d61dd34dae7d8b9b0dbdfe863bf5730c2810938b'
    ||($navigationReceipt['independent_before_sha256']??null)!==App\Services\M88ContentPatch::BEFORE_SHA
    ||($secondaryReceipt['independent_before_sha256']??null)!==App\Services\M88ContentPatch::BEFORE_SHA
    ||array_column($secondaryReceipt['targets']??[],'identity')!==[App\Support\M88PastContinuousPresentPerfectBasics::OWNERS[2]]
    ||array_column($secondaryReceipt['targets']??[],'source_index')!==[7]
    ||($secondaryReceipt['secondary_navigation_visible_acceptance']??null)!==false){
    throw new RuntimeException('M88 exact fresh main/secondary navigation receipt lineage differs; no visible PASS is inferred.');
}
$authorProjection=$load('author-projection-v1.json','6439bb2ec99c43c3b1c50e7f4e5d6901ad41466e4ec79a551982be42aef9702c');
if($sourceBefore!==$authorProjection['source_before']||$patch['targets']!==$authorProjection['patch']['targets']
    ||$master['lessons']!==$authorProjection['master']['lessons']||$contract!==$authorProjection['contract']){
    throw new RuntimeException('M88 frozen teaching differs from independent prefreeze author projection.');
}unset($authorProjection);
$navigationAdditions=0;$navigationChanges=[];
foreach($navigation['targets'] as $i=>$review){
    $slot=App\Support\M88PastContinuousPresentPerfectBasics::NAVIGATION_SOURCE_INDICES[$i];
    $oldNavigation=json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);
    $newNavigation=$master['lessons'][$i]['blocks'][$slot]['data'];
    if(($navigationReceipt['owner_navigations'][$review['identity']]['source_index']??null)!==$slot||($navigationReceipt['owner_navigations'][$review['identity']]['data']??null)!==$newNavigation){throw new RuntimeException('M88 exact fresh navigation data differs.');}
    $labelCurrent=static fn(array $items)=>array_map(static fn(array $item)=>
        array_intersect_key($item,array_flip(['label','current'])),$items);
    if(($review['source_index']??null)!==$slot||($review['items']??null)!==($newNavigation['items']??null)
        ||$labelCurrent($newNavigation['items'])!==$labelCurrent($oldNavigation['items'])
        ||array_diff_key($newNavigation,['items'=>true])!==array_diff_key($oldNavigation,['items'=>true])){
        throw new RuntimeException('M88 footer title/labels/current/order differ from independent source and verified destinations.');
    }
    foreach($newNavigation['items'] as $n=>$item){
        $old=$oldNavigation['items'][$n];
        $beforeUrl=$old['url']??null;$afterUrl=$item['url']??null;
        $otherBefore=$old;$otherAfter=$item;unset($otherBefore['url'],$otherAfter['url']);
        if($otherBefore!==$otherAfter){throw new RuntimeException('M88 footer has a non-URL data change.');}
        if($beforeUrl!==$afterUrl){
            $navigationChanges[]=['identity'=>$review['identity'],'source_index'=>$slot,'item_index'=>$n,
                'before_url'=>$beforeUrl,'after_url'=>$afterUrl];
            if($beforeUrl===null&&is_string($afterUrl)){++$navigationAdditions;}
        }
    }
    if($i===2){
        $relatedSlot=7;
        $oldRelated=$sourceBefore['targets'][$i]['before']['page']['blocks'][$relatedSlot];
        $newRelated=$patch['targets'][$i]['after']['page']['blocks'][$relatedSlot];
        $oldData=json_decode($oldRelated['body'],true,flags:JSON_THROW_ON_ERROR);
        $newData=json_decode($newRelated['body'],true,flags:JSON_THROW_ON_ERROR);
        if(($newData['m88_native_v1']??null)!==['key'=>'past-continuous-questions-7','detail_keys'=>[]]){
            throw new RuntimeException('M88 related-topic footer has a foreign native registration marker.');
        }
        unset($newData['m88_native_v1'],$oldRelated['body'],$newRelated['body']);
        if($oldRelated!==$newRelated||$oldData!==$newData){
            throw new RuntimeException('M88 the exact C question-page secondary related-topic navigation data and metadata must remain exact.');
        }
    }
}
if(count($navigationChanges)!==16||$navigationAdditions!==0){throw new RuntimeException('M88 only16existing short A-D footer URLs may change; no E/F or label rewrite.');}
$lineageCheck=static function()use($root,$dir,$hash,$load,$normal,$before,$consumers):array{
    $captureSha='018a23eaf1063e263d12a037e8f2de686ddfe57a97402cd29ef99a3b9f1e7099';
    $fixtureSha='1be8adcabf1569fef0fe185bce868705b998a53be66dea4d59c3fe9231f3dc81';
    $capture=$load('independent-source-before-v1.json',$captureSha);
    $wt=App\Services\M88ContentPatch::SOURCE_WORKTREE;$fixture=$wt.'/tests/Fixtures/m88-owned-source-hunks.json';
    if(!hash_equals($fixtureSha,$hash($fixture))){throw new RuntimeException('M88 independent source-hunk fixture bytes differ.');}
    $ledger=json_decode(file_get_contents($fixture),true,flags:JSON_THROW_ON_ERROR);
    if(($capture['schema_version']??null)!==1||($capture['task']??null)!=='M88'
        ||($capture['base_sha']??null)!==App\Support\M88PastContinuousPresentPerfectBasics::BASE_SHA
        ||($capture['kind']??null)!=='independent-source-before'||count($capture['records']??[])!==8435
        ||($capture['git_writes']??null)!==0||($capture['db_writes']??null)!==0||($capture['http_requests']??null)!==0
        ||($ledger['base_sha']??null)!==App\Support\M88PastContinuousPresentPerfectBasics::BASE_SHA
        ||($ledger['independent_source_before_sha256']??null)!==$captureSha
        ||($ledger['independent_db_before_sha256']??null)!==App\Services\M88ContentPatch::BEFORE_SHA
        ||count($ledger['files']??[])!==8||($ledger['owned_added_registration_lines']??null)!==12
        ||($ledger['owned_validator_added_lines']??0)!==0||($ledger['owned_validator_removed_lines']??0)!==0){
        throw new RuntimeException('M88 fresh paired source BEFORE and finite shared lineage differ.');
    }
    $dispatch=['app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php'];
    if(array_column($ledger['files'],'path')!==$dispatch){throw new RuntimeException('M88 exact eight shared source paths differ.');}
    $capturedOwn=array_values(array_filter(array_keys($capture['records']),static fn($path)=>str_contains($path,'M88')||str_contains($path,'m88')));
    if($capturedOwn!==[]){throw new RuntimeException('M88 paired BEFORE must predate every new M88 own source; no in-progress exception.');}
    $definitions=array_map(App\Support\TheorySentenceBuilderAcceptance::definitionPath(...),App\Support\M88PastContinuousPresentPerfectBasics::OWNERS);
    $checked=$unaffected=$bankSourceChecks=$foreignChecks=$absentChecks=0;
    foreach(['served_root'=>$root,'worktree'=>$wt]as $context=>$directory){
        $contextKey=$context==='served_root'?'root':'worktree';
        if($normal($before[$context==='served_root'?'served_root':'source_worktree']??'')!==$normal($directory)){
            throw new RuntimeException('M88 independent ROOT/WT directory identity differs.');
        }
        $visual=[];
        foreach(['resources/views','resources/css','resources/js','public/js']as $folder){
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory.'/'.$folder,FilesystemIterator::SKIP_DOTS))as $file){
                if($file->isLink()){throw new RuntimeException('M88 visual inventory contains a link.');}
                if($file->isFile()){$visual[]=substr(str_replace('\\','/',$file->getPathname()),strlen($directory)+1);}
            }
        }
        $expectedVisual=$capture['visual_paths']??[];sort($visual);sort($expectedVisual);
        if(count($expectedVisual)!==326||$visual!==$expectedVisual){throw new RuntimeException('M88 complete ROOT/WT visual inventory changed.');}
        $verifyUnchanged=static function(string $path,?array $old)use($directory,$hash,&$absentChecks):void{
            $file=$directory.'/'.$path;
            if($old===null){if(file_exists($file)||is_link($file)){throw new RuntimeException('M88 captured absent source was recreated: '.$path);}++$absentChecks;return;}
            if(!hash_equals($old['sha256'],$hash($file))){throw new RuntimeException('M88 non-target raw source changed: '.$path);}
            $raw=file_get_contents($file);
            if(strlen($raw)!==$old['bytes']||substr_count($raw,"\r\n")!==$old['crlf']||substr_count($raw,"\n")!==$old['lf']
                ||str_starts_with($raw,"\xEF\xBB\xBF")!==$old['utf8_bom']){
                throw new RuntimeException('M88 non-target byte/EOL/BOM profile changed: '.$path);
            }
        };
        $addedCount=0;
        foreach($ledger['files']as $file){
            $path=$file['path'];$pin=$file['profiles'][$context]??null;$old=$capture['records'][$path][$contextKey]??null;
            if(!is_array($pin)||!is_array($old)||($old['sha256']??null)!==($pin['before_raw_sha256']??null)
                ||($old['bytes']??null)!==($pin['before_bytes']??null)||($old['lf']??null)!==($pin['before_lf_line_count']??null)
                ||($old['crlf']??null)!==($pin['before_crlf_line_count']??null)){
                throw new RuntimeException('M88 shared predecessor pin does not derive from its own captured raw context.');
            }
            if(!hash_equals($pin['after_raw_sha256'],$hash($directory.'/'.$path))){throw new RuntimeException('M88 shared raw AFTER differs: '.$context.' '.$path);}
            $raw=file_get_contents($directory.'/'.$path);$lf=str_replace("\r\n","\n",$raw);
            if(hash('sha256',$lf)!==$pin['after_lf_sha256']||strlen($raw)!==$pin['after_bytes']
                ||substr_count($raw,"\n")!==$pin['after_lf_line_count']||substr_count($raw,"\r\n")!==$pin['after_crlf_line_count']){
                throw new RuntimeException('M88 exact shared LF/EOL AFTER profile differs.');
            }
            foreach($file['hunks']as $hunk){
                $added=substr($hunk['after'],strlen($hunk['before']));
                if($hunk['added_lines']!==1||$hunk['removed_lines']!==0||!str_starts_with($hunk['after'],$hunk['before'])
                    ||substr_count($added,"\n")!==1||!str_contains($added,'M88')||str_contains($hunk['before'],'M88')
                    ||substr_count($lf,$hunk['after'])!==1){throw new RuntimeException('M88 exact one-line own registration hunk differs.');}
                $lf=str_replace($hunk['after'],$hunk['before'],$lf);
                $lines=preg_split('/(?<=\n)/',$raw,-1,PREG_SPLIT_NO_EMPTY);
                $normalized=array_map(static fn($line)=>str_replace("\r\n","\n",$line),$lines);
                $wanted=preg_split('/(?<=\n)/',$hunk['after'],-1,PREG_SPLIT_NO_EMPTY);$positions=[];
                foreach(array_keys($lines)as $n){if(array_slice($normalized,$n,count($wanted))===$wanted){$positions[]=$n;}}
                if(count($positions)!==1){throw new RuntimeException('M88 exact raw owned-line position is missing or ambiguous.');}
                $position=$positions[0]+count($wanted)-1;
                if($lines[$position]!==$added&&$lines[$position]!==str_replace("\n","\r\n",$added)){
                    throw new RuntimeException('M88 owned insertion has an unapproved context EOL.');
                }
                array_splice($lines,$position,1);$raw=implode('',$lines);++$addedCount;
            }
            if(hash('sha256',$raw)!==$old['sha256']||hash('sha256',$raw)!==$pin['before_raw_sha256']||hash('sha256',$lf)!==$pin['before_lf_sha256']){
                throw new RuntimeException('M88 reversing only own hunks does not recover the exact captured raw predecessor.');
            }
            ++$checked;
        }
        if($addedCount!==12){throw new RuntimeException('M88 shared insertion count is not exactly twelve in this context.');}
        foreach($capture['records']as $path=>$profiles){
            if(in_array($path,$dispatch,true)||in_array($path,$definitions,true)||$path==='.gitattributes'){continue;}
            if(!array_key_exists($contextKey,$profiles)){throw new RuntimeException('M88 captured source context is missing.');}
            $verifyUnchanged($path,$profiles[$contextKey]);++$unaffected;
        }
        if(count($consumers['source_hashes']??[])!==15){throw new RuntimeException('M88 fifteen original bank-source declarations differ.');}
        foreach($consumers['source_hashes']as $path=>$sha){
            $profile=$capture['records'][$path][$contextKey]??null;
            if(!is_array($profile)||($capture['records'][$path]['root']['sha256']??null)!==$sha){
                throw new RuntimeException('M88 bank/course JSON is absent from the primary independently captured paired source inventory.');
            }
            $verifyUnchanged($path,$profile);++$bankSourceChecks;
        }
        // Finite M88 delivery lifecycle: ROOT stays absent; WT is its exact captured raw prefix plus one reviewed suffix.
        $attrs=$load('delivery-attributes-proposal-v1.json','2b2acef375422795de25e6c095298d780b81a347b0b0d3b48dbfcb85f12b2926');
        $attributePaths=$load('delivery-attributes-paths-v1.json','c0a7581153fa9ef670e1497f5f557fb9bd3591f2d9c96e6430102d4563d0eaa1');
        $sortedAttributePaths=$attributePaths;sort($sortedAttributePaths,SORT_STRING);
        $attributePrefix=$capture['records']['.gitattributes']['worktree'];
        $expectedTail="\n# M88 frozen UTF-8/LF release\n"
            .implode("\n",array_map(static fn($path)=>$path.' -text',$attributePaths))."\n";
        if(($attrs['schema_version']??null)!==1||($attrs['base_sha']??null)!==App\Support\M88PastContinuousPresentPerfectBasics::BASE_SHA
            ||($attrs['independent_source_before_sha256']??null)!==$captureSha
            ||($attrs['independent_before_sha256']??null)!==App\Services\M88ContentPatch::BEFORE_SHA
            ||($attrs['path']??null)!=='.gitattributes'||($attrs['write_context']??null)!=='worktree_only'
            ||($attrs['root_must_remain_absent']??null)!==true||$capture['records']['.gitattributes']['root']!==null
            ||!array_is_list($attributePaths)||count($attributePaths)!==49||count(array_unique($attributePaths))!==49
            ||$sortedAttributePaths!==$attributePaths||($attrs['paths']??null)!==$attributePaths
            ||App\Support\M26DetailPackage::digest($attributePaths)!=='78c53e53baefc45fae3bbc82bb26a1e0e16b3f6a246cce7374156ed2ae47c040'
            ||($attrs['before_worktree']??null)!==$attributePrefix
            ||($attrs['suffix']??null)!==$expectedTail||strlen($expectedTail)!==3152||substr_count($expectedTail,"\n")!==51
            ||hash('sha256',$expectedTail)!=='16364f47c3336daa7b42ffde5143501f00625957cc946eab9d7bcd32d6fcec49'
            ||($attrs['suffix_sha256']??null)!=='16364f47c3336daa7b42ffde5143501f00625957cc946eab9d7bcd32d6fcec49'
            ||($attrs['after_worktree']['sha256']??null)!=='280671550ea4ecfd23b407d3ff942d36c048ab2dd749b0bc35c0ebd96629a641'){
            throw new RuntimeException('M88 exact49-path WT-only attribute proposal/prefix/suffix authority differs.');
        }
        if($contextKey==='root'){
            $verifyUnchanged('.gitattributes',null);
        }else{
            $attributeFile=$directory.'/.gitattributes';
            if(!is_file($attributeFile)||is_link($attributeFile)){throw new RuntimeException('M88 WT attributes missing or linked.');}
            $attributeRaw=file_get_contents($attributeFile);$prefix=substr($attributeRaw,0,$attributePrefix['bytes']);
            $tail=substr($attributeRaw,$attributePrefix['bytes']);
            if(hash('sha256',$prefix)!==$attributePrefix['sha256']||$tail!==$expectedTail
                ||hash('sha256',$attributeRaw)!==$attrs['after_worktree']['sha256']
                ||strlen($attributeRaw)!==$attrs['after_worktree']['bytes']
                ||substr_count($attributeRaw,"\r\n")!==$attrs['after_worktree']['crlf']
                ||substr_count($attributeRaw,"\n")!==$attrs['after_worktree']['lf']
                ||str_starts_with($attributeRaw,"\xEF\xBB\xBF")!==$attrs['after_worktree']['utf8_bom']){
                throw new RuntimeException('M88 WT attributes are not the exact original raw prefix plus the reviewed literal suffix.');
            }
            foreach($attributePaths as $path){
                $file=$directory.'/'.$path;
                if(!is_file($file)||is_link($file)||$normal($file)!==$normal($directory).'/'.strtolower($path)){
                    throw new RuntimeException('M88 exact delivered attribute target is missing, linked or outside WT: '.$path);
                }
            }
        }

    }
    if(count($capture['foreign_untracked']??[])!==25){throw new RuntimeException('M88 exact pre-existing foreign untracked set differs.');}
    foreach($capture['foreign_untracked']as $path=>$old){
        if($old===null||!hash_equals($old['sha256'],$hash($wt.'/'.$path))){throw new RuntimeException('M88 pre-existing foreign untracked bytes changed.');}
        ++$foreignChecks;
    }
    return ['independent_source_before_sha256'=>$captureSha,'independent_source_before_paths'=>8435,
        'source_hunk_fixture_sha256'=>$fixtureSha,'finite_shared_files'=>8,'separate_contexts'=>2,'raw_lf_reversals'=>$checked,
        'owned_added_registration_lines_per_context'=>12,'native_release_validator_unchanged'=>true,
        'non_target_source_context_checks'=>$unaffected,'captured_absence_context_checks'=>$absentChecks,
        'all_8435_primary_paths_accounted_for'=>true,'primary_exceptions'=>'Only six target definition files, eight exact own-hunk shared paths, and one separately pinned WT-only delivery-attributes suffix; ROOT attributes remain absent.',
        'bank_course_definition_context_checks'=>$bankSourceChecks,'course_source_scope'=>'All15 discovered source bank declarations (including two24-question course pools and13all-level72-question banks) are bound to fresh M88 paired source capture; no old M87 membership capture reused.',
        'preexisting_foreign_untracked_context_checks'=>$foreignChecks,'in_progress_own_exceptions'=>[],
        'delivery_attributes'=>['profile'=>'Exact captured WT prefix plus49reviewed M88 -text lines; ROOT remains absent.',
            'proposal_sha256'=>'2b2acef375422795de25e6c095298d780b81a347b0b0d3b48dbfcb85f12b2926',
            'paths_digest'=>'78c53e53baefc45fae3bbc82bb26a1e0e16b3f6a246cce7374156ed2ae47c040',
            'suffix_sha256'=>'16364f47c3336daa7b42ffde5143501f00625957cc946eab9d7bcd32d6fcec49',
            'worktree_after_sha256'=>'280671550ea4ecfd23b407d3ff942d36c048ab2dd749b0bc35c0ebd96629a641','all49paths_exist'=>true,'root_absent'=>true]];
};
$lineageFirst=$lineageCheck();
$oldConsumerSqlCheck=static function($db)use($before,$consumers):array{
    $banks=[];
    foreach($before['pages'] as $page){foreach(array_keys($page['bank_inventory']??[]) as $bank){$banks[]=$bank;}}
    $banks=array_values(array_unique($banks));sort($banks);
    $old=$consumers['old_sql']??[];$capturedBanks=array_values(array_unique(array_column($old['questions']??[],'seeder')));sort($capturedBanks);
    $declaredBanks=array_keys($old['source_bank_declarations']??[]);sort($declaredBanks);
    $observedBanks=array_keys($old['source_set_observations']??[]);sort($observedBanks);
    if(count($banks)!==15||$banks!==$capturedBanks||$banks!==$declaredBanks||$banks!==$observedBanks
        ||($consumers['source_bank_count']??null)!==15||count($old['questions']??[])!==984
        ||count($old['saved_tests']??[])!==15||array_keys($old['saved_tests'])!==array_keys($old['saved_question_links']??[])
        ||array_keys($old['saved_tests'])!==array_keys($old['saved_pool_declarations']??[])){
        throw new RuntimeException('M88 exact15original banks/984questions/15saved pools BEFORE inventory differs.');
    }
    $linked=[];foreach($before['pages'] as $page){foreach($page['relations']['questions'] as $q){$linked[$q['id']]=true;}}
    $linkedIds=array_keys($linked);sort($linkedIds,SORT_NUMERIC);$fullIds=array_column($old['questions'],'id');sort($fullIds,SORT_NUMERIC);
    $extras=array_values(array_diff($fullIds,$linkedIds));sort($extras,SORT_NUMERIC);
    $digest=App\Support\M26DetailPackage::digest(...);
    if(count($linkedIds)!==918||$extras!==[...range(6638,6673),...range(6680,6709)]||array_diff($linkedIds,$fullIds)!==[]
        ||$digest($linkedIds)!=='0e7f1c732bee03ee56be68efd9e001eefd996900859c11adb7a442a6d06b7e3f'
        ||$digest($fullIds)!=='bbd3fd08644fac63cab806234a35200fab0a78ffd4da9bfcee47d29c73927e85'
        ||$digest($extras)!=='c9cf519e1f1d8db32935e00458d496d507271e4164c0f164dec15469126526c1'){
        throw new RuntimeException('M88 exact918 page-linked subset/984 full-bank/66 extras differ.');
    }
    foreach($old['questions'] as $q){if(in_array($q['id'],$extras,true)&&$q['seeder']!=='Database\\Seeders\\V3\\Polyglot\\PolyglotVerbToBePastAllLevelsLessonSeeder'){throw new RuntimeException('M88 full-bank-only row has a foreign bank.');}}
    $rows=static fn($q)=>$q->get()->map(static fn($r)=>(array)$r)->all();
    $ordered=static function(string $table,$query,array $priority=[])use($db,$rows):array{
        $columns=$db->getSchemaBuilder()->getColumnListing($table);
        $order=array_values(array_unique([...$priority,...(in_array('id',$columns,true)?['id']:$columns)]));
        foreach($order as $column){if(!in_array($column,$columns,true)){throw new RuntimeException('M88 old-consumer sort column differs.');}$query->orderBy($column);}
        return $rows($query);
    };
    $questions=$rows($db->table('questions')->whereIn('seeder',$banks)->orderBy('id'));
    if($questions!==$old['questions']){throw new RuntimeException('M88 full original bank question rows/timestamps changed.');}
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
    if($actual!==$old){throw new RuntimeException('M88 an original bank relation, pivot, shared option or saved membership/order/timestamp changed.');}
    return ['original_banks'=>15,'original_questions'=>984,'page_linked_unique_questions'=>918,'full_bank_only_question_ids'=>$extras,'saved_tests'=>15,'saved_memberships'=>$memberships,
        'question_scope'=>'All984 rows assigned to15fresh-discovered original seeders; six-page linked union918 is a proper subset with66 exact VerbToBePast full-bank-only rows. All relation/shared-option/saved-order/timestamp fields protected, not only page links.',
        'source_sql_differences_preserved_not_repaired'=>true,
        'row_families'=>array_map('count',$actual),'all_original_rows_and_timestamps_exact'=>true,
        'all_old_banks_and_standalone_memberships_unchanged'=>true,
        'course_progress_enrollment_attempts'=>'Every actual row is protected by exact reconstruction of all 46 SQL tables; no runtime course interaction claim.'];
};
$schemaBefore=$load('schema-before-v2.json','2e7d618b381d86eddc19e4add792333bb74b33c280700006c8ec41854c3936d7');
if(($schemaBefore['kind']??null)!=='M88 fresh SELECT-only schema BEFORE'
    ||($schemaBefore['base_sha']??null)!==App\Support\M88PastContinuousPresentPerfectBasics::BASE_SHA
    ||($schemaBefore['before_sha256']??null)!==App\Services\M88ContentPatch::BEFORE_SHA
    ||($schemaBefore['identity']??null)!==$before['identity']||($schemaBefore['sql_before']??null)!==$before['sql_before']
    ||array_keys($schemaBefore['schema']??[])!==array_keys($before['sql_before'])
    ||($schemaBefore['db_writes']??null)!==0||($schemaBefore['http_requests']??null)!==0){
    throw new RuntimeException('M88 fresh complete forty-six-table schema BEFORE differs.');
}
$expectedSources=array_values(array_unique([...App\Services\M88ContentPatch::sourcePaths($patch,$contract,$root),
    'private:'.App\Services\M88ContentPatch::BEFORE_FILE,'private:'.App\Services\M88ContentPatch::CONSUMERS_FILE,'private:'.App\Services\M88ContentPatch::SYNC_RECEIPT]));
sort($expectedSources);$actualSources=array_keys($preview['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M88 full reviewed source/evidence list differs.');}
foreach ($preview['sources'] as $path => $sha) {
PHP;
$physical=<<<'PHP'
App\Services\M88LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.
$guard=new App\Services\M88LocalTargetGuard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key){if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1){throw new RuntimeException('M88 physical MySQL path is not local Windows.');}}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){throw new RuntimeException('M88 fresh physical MySQL identity differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
PHP;
$recheck=<<<'PHP'
} finally { $db->rollBack(); }
if(!hash_equals('2e7d618b381d86eddc19e4add792333bb74b33c280700006c8ec41854c3936d7',$hash($dir.'/schema-before-v2.json'))){
    throw new RuntimeException('M88 complete schema BEFORE bytes changed during final audit.');
}
foreach(['navigation-review-v1.json'=>'c7a03238607c26c754df20f4d61dd34dae7d8b9b0dbdfe863bf5730c2810938b','secondary-navigation-review-v1.json'=>'3b07a36eacabd414f68f2bd3bccc9a3d3ad922ad9adf6ee85c389076ae2d682e']as $name=>$sha){if(!hash_equals($sha,$hash($dir.'/'.$name))){throw new RuntimeException('M88 main/secondary navigation BEFORE evidence changed during audit.');}}
foreach($preview['sources'] as $path=>$sha){$file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M88 source/evidence changed during final audit.');}}
foreach([$before['physical_hashes'],$before['visual_hashes'],$before['frozen_hashes']] as $n=>$records){foreach($records as $path=>$sha){
    if(!hash_equals($sha,$hash($n===0?$path:$root.'/'.$path))){throw new RuntimeException('M88 physical/visual/frozen bytes changed during final audit.');}
}}
foreach($sync['files'] as $record){if($hash($root.'/'.$record['path'])!==$record['root_after_sha256']
    ||$hash(App\Services\M88ContentPatch::SOURCE_WORKTREE.'/'.$record['path'])!==$record['worktree_sha256']){throw new RuntimeException('M88 source-sync changed during final audit.');}}
if($lineageCheck()!==$lineageFirst){throw new RuntimeException('M88 independent raw/LF source lineage changed during final audit.');}
$repeatEvidence=null;
if($profileContext['priorName']!==null){
    $prior=$load($profileContext['priorName'],$profileContext['priorSha']);
    if(($prior['plan_digest']??null)!==$reviewedDigest||($prior['independent_before_sha256']??null)!==App\Services\M88ContentPatch::BEFORE_SHA
        ||($prior['backup_sha256']??null)!==$profileContext['backupSha']||($prior['sql_after']??null)!==$rawAfter
        ||($prior['normalized_before']??null)!==$normalized||($prior['protected']??null)!==$protected
        ||($prior['new_practices']??null)!==$practiceFacts||($prior['retained_practices']??null)!==$retainedPracticeFacts||($prior['source_lineage']??null)!==$lineageFirst||($prior['old_consumer_sql']??null)!==$consumerFacts
        ||($prior['approved_footer_url_additions']??null)!==$navigationAdditions||($prior['approved_footer_url_changes']??null)!==$navigationChanges
        ||($prior['all_old_rows_and_timestamps_exact']??null)!==true||($prior['db_writes']??null)!==0){
        throw new RuntimeException('M88 post-UI repeat differs from the exact prior successful read-only receipt.');
    }
    $repeatEvidence=['file'=>$profileContext['priorName'],'sha256'=>$profileContext['priorSha'],
        'all_46_after_fingerprints_exact'=>true,'new_practice_rows_exact'=>true,'source_lineage_exact'=>true];
}
if(!hash_equals($profileContext['backupSha'],$hash($dir.'/'.$profileContext['backupName']))
    ||file_get_contents($dir.'/'.$previewName)!==file_get_contents($dir.'/'.$profileContext['backupName'])){throw new RuntimeException('M88 exclusive backup changed during audit.');}
$receipt =
PHP;
$insertCheck=<<<'PHP'
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        if($i>=4||$target['identity']!==App\Support\M88PastContinuousPresentPerfectBasics::OWNERS[$i]
            ||$position!==App\Support\M88PastContinuousPresentPerfectBasics::PRACTICE_SOURCE_INDICES[$i]+1
            ||$position!==App\Support\M88PastContinuousPresentPerfectBasics::CONTINUING_COUNTS[$i]+1
            ||$source['type']!=='practice-set'||!array_key_exists('level',$source)||$source['level']!==null||$source['column']!=='left'
            ||isset($insertOwnerSeen[$target['identity']])){
            throw new RuntimeException('M88 only one appended SQL NULL left practice per exact A-D owner is approved.');
        }
        $insertOwnerSeen[$target['identity']]=true;$page=$before['pages'][$target['identity']]['page'];
        $fields=['uuid'=>$uuid,'page_id'=>$page['id'],'page_category_id'=>$page['page_category_id'],'locale'=>'uk',
            'type'=>$source['type'],'column'=>$source['column'],'heading'=>$source['heading']??null,'css_class'=>$source['css_class']??null,
            'sort_order'=>$position,'body'=>$source['body'],'level'=>$source['level'],'seeder'=>$target['identity']];
        $same($insert['fields'],$fields,'M88 reviewed practice insert does not exactly match frozen source fields.');
PHP;
$profile=[
    ['M53PassiveConstructionsPackage','M88PastContinuousPresentPerfectBasics',7],['M53','M88',15],['m53','m88',4],
    [$originalInventory,$inventory,1],[$originalQuestions,$questions,1],
    ['    $newPractices = []; $practiceFacts = [];','    $newPractices = []; $practiceFacts = []; $insertOwnerSeen=[];',1],
    ['        $uuid = App\\Support\\M26DetailPackage::uuid($target[\'identity\'], $source, $position);',$insertCheck,1],
    ["\$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);","if(!isset(\$schemaBefore['schema'][\$table])||\$db->getSchemaBuilder()->getColumns(\$table)!==\$schemaBefore['schema'][\$table]){throw new RuntimeException('M88 full captured SQL schema changed.');}\n        \$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);",1],
    ['foreach ($preview[\'sources\'] as $path => $sha) {',$extraSource,1],
    ["    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }",
        "    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }\n    \$consumerFacts=\$oldConsumerSqlCheck(\$db);",1],
    ['App\\Services\\M88LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.',$physical,1],
    ["'question_plan_sha256' => App\\Services\\M88ContentPatch::QUESTION_PLAN_SHA,","'backup_file'=>\$profileContext['backupName'],'backup_sha256'=>\$profileContext['backupSha'],'backup_equals_reviewed_preview'=>true,\n    'source_lineage'=>\$lineageFirst,'post_ui_repeat'=>\$repeatEvidence,'old_consumer_sql'=>\$consumerFacts,\n    'metadata_transitions'=>\$metadata,'retained_practices'=>\$retainedPracticeFacts,'body_update_definition'=>'Teaching includes all45continuing native blocks, including two retained practice bodies; all6subtitles, Page metadata and all old timestamps unchanged.','unchanged_subtitle_rows'=>6,'own_graded_controls'=>36,'ungraded_demonstrations'=>0,'full_sentence_inputs'=>12,'update_operations'=>count(\$expectedChanges),'body_updates'=>\$bodyKinds,'question_family_insert_counts'=>array_map('count',\$newRows),'question_family_insert_rows'=>0,'original_question_rows_updated'=>0,'full_schema_tables_checked'=>46,\n    'approved_footer_url_additions'=>\$navigationAdditions,'approved_footer_url_changes'=>\$navigationChanges,'all_footer_labels_order_current_preserved'=>true,",1],
    ["} finally { \$db->rollBack(); }\n\$receipt =",$recheck,1],
];
if($profileCheckOnly){
    $immutable=$diagnostics.'/m53-postconditions.php';$source=file_get_contents($immutable);
    if(is_link($immutable)||!hash_equals('8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',hash('sha256',$source))){throw new RuntimeException('M88 immutable scanner bytes differ.');}
    foreach($profile as [$needle,$replacement,$count]){if(substr_count($source,$needle)!==$count){throw new RuntimeException('M88 pure profile substitution count differs: '.substr($needle,0,90));}$source=str_replace($needle,$replacement,$source);}
    token_get_all($source,TOKEN_PARSE);
    echo json_encode(['profile'=>'M88 immutable SELECT-only scanner','literal_substitutions'=>count($profile),'composed_php_parsed'=>true,
        'db_bootstraps'=>0,'db_writes'=>0,'http_requests'=>0,'private_evidence_opened'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
    exit(0);
}
runFrozenReadOnlyProfile('m53-postconditions.php','8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',$profile,
    ['backupName'=>$backupName,'backupSha'=>$backupSha,'priorName'=>$priorName,'priorSha'=>$priorSha]);
