<?php

/**
 * Independent SELECT-only M86 reconstruction via the immutable M53 scanner.
 * Six owners, 53 continuing UK rows, four retained practices, two new practices, zero question-family writes.
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
    throw new RuntimeException('M86 reviewed preview/digest, exclusive backup/raw SHA, fresh final basename required.');
}
$backupName=$argv[3];$backupSha=$argv[4];$private='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m86-local';
$priorName=$argv[6]??null;$priorSha=$argv[7]??null;
if(($priorName!==null||$priorSha!==null)&&(count($argv)!==8
    ||!is_string($priorName)||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$priorName)!==1
    ||!is_string($priorSha)||preg_match('/^[a-f0-9]{64}$/D',$priorSha)!==1
    ||$priorName===$argv[5]||!is_file($private.'/'.$priorName)||is_link($private.'/'.$priorName)
    ||!hash_equals($priorSha,hash_file('sha256',$private.'/'.$priorName)))){
    throw new RuntimeException('M86 optional prior final receipt name/raw SHA differs; no repeat claim.');
}
if(!$profileCheckOnly){
foreach([$argv[1],$backupName] as $name){if(!is_file($private.'/'.$name)||is_link($private.'/'.$name)){throw new RuntimeException('M86 reviewed preview/backup absent or linked.');}}
if(!hash_equals($backupSha,hash_file('sha256',$private.'/'.$backupName))
    ||file_get_contents($private.'/'.$argv[1])!==file_get_contents($private.'/'.$backupName)){
    throw new RuntimeException('M86 exclusive backup bytes are not the exact reviewed preview.');
}
}
$argv=[$argv[0],$argv[1],$argv[2],$argv[5]];
$inventory=<<<'PHP'
    $pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $nonUk = 0;
    $expectedChanges=[];$bodyKinds=['subtitle'=>0,'teaching'=>0];$metadata=[];$retainedPracticeFacts=[];
    foreach ($patch['targets'] as $i => $target) {
        $owner=$target['identity'];$captured=$before['pages'][$owner];$oldPage=$captured['page'];
        $source=$sourceBefore['targets'][$i]['before'];
        if($oldPage['id']!==App\Support\M86PresentSimpleContinuousBasics::PAGE_IDS[$i]
            ||$oldPage['seeder']!==$owner||$oldPage['page_category_id']!==App\Support\M86PresentSimpleContinuousBasics::CATEGORY_IDS[$i]
            ||!array_key_exists('subtitle_text',$source['page'])||$oldPage['text']!==null
            ||$source['page']['subtitle_text']!==null||$target['after']['page']['subtitle_text']!==null){
            throw new RuntimeException('M86 independent Page/source identity or subtitle differs.');
        }
        foreach(['page','category_ancestry','blocks'] as $field){
            if(($preview['pages'][$owner][$field]??null)!==$captured[$field]){
                throw new RuntimeException('M86 reviewed original inventory differs from independent BEFORE.');
            }
        }
        if(($before['definitions'][$owner]['definition']??null)!==$source){
            throw new RuntimeException('M86 frozen source BEFORE differs from independent working capture.');
        }
        App\Services\M86ContentPatch::assertDefinitionScope($source,$target['after'],$target,$i);
        $pageBefore[$oldPage['id']]=$oldPage;
        $pageAfter[$oldPage['id']]=$oldPage;
        $capturedPractices=array_values(array_filter($captured['blocks'],static fn($row)=>$row['locale']==='uk'&&$row['type']==='practice-set'));
        if(count($source['page']['blocks'])!==App\Support\M86PresentSimpleContinuousBasics::CONTINUING_COUNTS[$i]
            ||count($target['after']['page']['blocks'])!==App\Support\M86PresentSimpleContinuousBasics::CONTINUING_COUNTS[$i]+($i<4?0:1)
            ||($captured['existing_practices']??null)!==$capturedPractices
            ||count($capturedPractices)!==($i<4?1:0)){
            throw new RuntimeException('M86 exact four continuing and two absent practice inventories differ.');
        }
        if($i<4){
            $practice=$capturedPractices[0];
            if($practice['uuid']!==App\Support\M86PresentSimpleContinuousBasics::EXISTING_PRACTICE_UUIDS[$i]
                ||$practice['sort_order']!==App\Support\M86PresentSimpleContinuousBasics::PRACTICE_SOURCE_INDICES[$i]+1
                ||$practice['column']!=='left'||$practice['level']!==null){
                throw new RuntimeException('M86 retained A-D practice owner/UUID/order/NULL metadata differs.');
            }
            $retainedPracticeFacts[]=['owner'=>$owner,'id'=>$practice['id'],'uuid'=>$practice['uuid'],'sort_order'=>$practice['sort_order']];
        }
        $positions=[];
        foreach($captured['blocks'] as $old){
            if($old['locale']!=='uk'){++$nonUk;continue;}
            $position=$old['sort_order'];
            if(!is_int($position)||$position<0||$position>App\Support\M86PresentSimpleContinuousBasics::CONTINUING_COUNTS[$i]
                ||isset($positions[$position])||isset($ukBefore[$old['id']])
                ||$old['seeder']!==$owner||$old['page_id']!==$oldPage['id']
                ||$old['page_category_id']!==$oldPage['page_category_id']){
                throw new RuntimeException('M86 exact continuing owner/locale/order inventory differs.');
            }
            $positions[$position]=true;
            $config=$position===0?['body'=>$target['after']['page']['subtitle_html'],'type'=>'subtitle','column'=>'header',
                'heading'=>null,'level'=>$source['page']['subtitle_level']??null,'css_class'=>null]
                :$target['after']['page']['blocks'][$position-1];
            $oldConfig=$position===0?['body'=>$source['page']['subtitle_html']]
                :$source['page']['blocks'][$position-1];
            if(!is_string($config['body']??null)||($oldConfig['body']??null)!==$old['body']){
                throw new RuntimeException('M86 independent source/SQL body correspondence differs.');
            }
            foreach(['type','column','heading','level','css_class'] as $field){
                if(($config[$field]??null)!==$old[$field]){
                    throw new RuntimeException('M86 no continuing metadata transition is approved: '.$field);
                }
            }
            if($position>0&&App\Support\M26DetailPackage::uuid($owner,$oldConfig,$position)!==$old['uuid']){
                throw new RuntimeException('M86 continuing source/SQL UUID correspondence differs.');
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
        if($actualPositions!==range(0,App\Support\M86PresentSimpleContinuousBasics::CONTINUING_COUNTS[$i])){
            throw new RuntimeException('M86 exact continuing native position vector differs.');
        }
    }
    $expectedUkRows=array_sum(array_map(static fn($count)=>$count+1,App\Support\M86PresentSimpleContinuousBasics::CONTINUING_COUNTS));
    if(count($pageBefore)!==6||$expectedUkRows!==53||count($ukBefore)!==$expectedUkRows||$nonUk!==98
        ||$metadata!==[]||count($retainedPracticeFacts)!==4||count($expectedChanges)!==53||count($preview['updates']??[])!==count($expectedChanges)
        ||$bodyKinds!==['subtitle'=>6,'teaching'=>47]
        ||count($preview['inserts']??[])!==2||($preview['deletes']??[])!==[]
        ||array_key_exists('question_supplement',$preview)){
        throw new RuntimeException('M86 exact53 body updates (six subtitles/all47continuing native bodies), two EF appends and zero-question scope differs.');
    }
    $seenChanges=[];
    foreach($preview['updates'] as $change){
        $key=$change['table'].':'.$change['id'];$expected=$expectedChanges[$key]??null;
        if($expected===null||isset($seenChanges[$key])
            ||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
            ||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$expected
            ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){
            throw new RuntimeException('M86 exact independently derived reviewed update inventory differs.');
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
    $service = new App\Services\M86ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm86QuestionState'))->invoke($service, 'after', $patch, false);
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
    // No question-family rows are approved for insertion or mutation by M86.
    $newRows=[];$approved=[];
    if(array_keys($insertOwnerSeen)!==array_slice(App\Support\M86PresentSimpleContinuousBasics::OWNERS,4)){throw new RuntimeException('M86 only the two exact EF appended practices may be verified.');}
PHP;
$extraSource=<<<'PHP'
if(App\Services\M86ContentPatch::CONSUMERS_SHA!=='1b02a5a54d5c4c2498f77b52f5f45afbc4183ce8eb66b12f0ed5bcd10f89ce14'){
    throw new RuntimeException('M86 independently reviewed old-consumer capture pin differs.');
}
$consumers=$load(App\Services\M86ContentPatch::CONSUMERS_FILE,App\Services\M86ContentPatch::CONSUMERS_SHA);
App\Services\M51ContentPatch::assertConsumerCapture($before,$consumers);
if(count($before['sql_before'])!==46||count($before['visual_hashes'])!==326||count($before['frozen_hashes'])!==2913
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']||($consumers['all_tables_after_http']??null)!==$before['sql_before']){
    throw new RuntimeException('M86 independent full-table/old-consumer baseline differs.');
}
$navigation=json_decode(file_get_contents($root.'/'.App\Support\M86PresentSimpleContinuousBasics::NAVIGATION),true,flags:JSON_THROW_ON_ERROR);
if(($master['editorial_review']['navigation_review']??null)!==$navigation
    ||array_column($navigation['targets']??[],'identity')!==App\Support\M86PresentSimpleContinuousBasics::OWNERS){
    throw new RuntimeException('M86 independent frozen footer navigation review differs.');
}
$navigationAdditions=0;$navigationChanges=[];
foreach($navigation['targets'] as $i=>$review){
    $slot=App\Support\M86PresentSimpleContinuousBasics::NAVIGATION_SOURCE_INDICES[$i];
    $oldNavigation=json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);
    $newNavigation=$master['lessons'][$i]['blocks'][$slot]['data'];
    $labelCurrent=static fn(array $items)=>array_map(static fn(array $item)=>
        array_intersect_key($item,array_flip(['label','current'])),$items);
    if(($review['source_index']??null)!==$slot||($review['items']??null)!==($newNavigation['items']??null)
        ||$labelCurrent($newNavigation['items'])!==$labelCurrent($oldNavigation['items'])
        ||array_diff_key($newNavigation,['items'=>true])!==array_diff_key($oldNavigation,['items'=>true])){
        throw new RuntimeException('M86 footer title/labels/current/order differ from independent source and verified destinations.');
    }
    foreach($newNavigation['items'] as $n=>$item){
        $old=$oldNavigation['items'][$n];
        $beforeUrl=$old['url']??null;$afterUrl=$item['url']??null;
        $otherBefore=$old;$otherAfter=$item;unset($otherBefore['url'],$otherAfter['url']);
        if($otherBefore!==$otherAfter){throw new RuntimeException('M86 footer has a non-URL data change.');}
        if($beforeUrl!==$afterUrl){
            $navigationChanges[]=['identity'=>$review['identity'],'source_index'=>$slot,'item_index'=>$n,
                'before_url'=>$beforeUrl,'after_url'=>$afterUrl];
            if($beforeUrl===null&&is_string($afterUrl)){++$navigationAdditions;}
        }
    }
    if($i===2){
        $oldRelated=$sourceBefore['targets'][$i]['before']['page']['blocks'][8];
        $newRelated=$patch['targets'][$i]['after']['page']['blocks'][8];
        $oldData=json_decode($oldRelated['body'],true,flags:JSON_THROW_ON_ERROR);
        $newData=json_decode($newRelated['body'],true,flags:JSON_THROW_ON_ERROR);
        if(($newData['m86_native_v1']??null)!==['key'=>'present-simple-questions-8','detail_keys'=>[]]){
            throw new RuntimeException('M86 related-topic footer has a foreign native registration marker.');
        }
        unset($newData['m86_native_v1'],$oldRelated['body'],$newRelated['body']);
        if($oldRelated!==$newRelated||$oldData!==$newData){
            throw new RuntimeException('M86 second question-page related-topic navigation data and metadata must remain exact.');
        }
    }
}
$lineageCheck=static function()use($root,$dir,$hash,$load,$normal,$before,$consumers):array{
    $captureSha='3176478fb5a119bc244988d43793a5b002becb3cdd068fe736c9f1afb172d4ba';
    $fixtureSha='44bfa36b2a2ca9d8128fe9858cf6ff051474fe65fee7d1f5b0d44524f835f196';
    $supplementSha='347c3d02defd3443a7eb7859d2cc6c0e87de527d24afa753a2ff02ebaec6e24f';
    $capture=$load('independent-source-before-v1.json',$captureSha);
    $supplement=$load('before-bank-source-contexts-v1.json',$supplementSha);
    $wt=App\Services\M86ContentPatch::SOURCE_WORKTREE;$fixture=$wt.'/tests/Fixtures/m86-owned-source-hunks.json';
    if(!hash_equals($fixtureSha,$hash($fixture))){throw new RuntimeException('M86 independent source-hunk fixture bytes differ.');}
    $ledger=json_decode(file_get_contents($fixture),true,flags:JSON_THROW_ON_ERROR);
    if(($capture['schema_version']??null)!==1||($capture['task']??null)!=='M86'
        ||($capture['base_sha']??null)!==App\Support\M86PresentSimpleContinuousBasics::BASE_SHA
        ||($capture['kind']??null)!=='independent-source-before'||count($capture['records']??[])!==6856
        ||($capture['git_writes']??null)!==0||($capture['db_writes']??null)!==0||($capture['http_requests']??null)!==0
        ||($ledger['base_sha']??null)!==App\Support\M86PresentSimpleContinuousBasics::BASE_SHA
        ||($ledger['independent_source_before_sha256']??null)!==$captureSha
        ||($ledger['independent_db_before_sha256']??null)!==App\Services\M86ContentPatch::BEFORE_SHA
        ||count($ledger['files']??[])!==8||($ledger['owned_added_registration_lines']??null)!==12
        ||($ledger['owned_validator_added_lines']??0)!==0||($ledger['owned_validator_removed_lines']??0)!==0){
        throw new RuntimeException('M86 fresh paired source BEFORE and finite shared lineage differ.');
    }
    if(($supplement['kind']??null)!=='supplemental-bank-source-before'||($supplement['task']??null)!=='M86'
        ||($supplement['base_sha']??null)!==App\Support\M86PresentSimpleContinuousBasics::BASE_SHA
        ||($supplement['independent_before_sha256']??null)!==App\Services\M86ContentPatch::BEFORE_SHA
        ||($supplement['primary_source_before_sha256']??null)!==$captureSha
        ||($supplement['consumer_before_sha256']??null)!==App\Services\M86ContentPatch::CONSUMERS_SHA
        ||count($supplement['records']??[])!==6||($supplement['db_writes']??null)!==0
        ||($supplement['http_requests']??null)!==0||($supplement['git_writes']??null)!==0||($supplement['source_writes']??null)!==0){
        throw new RuntimeException('M86 supplemental pre-apply bank-source context lineage differs.');
    }
    $expectedSupplement=array_values(array_filter(array_keys($consumers['source_hashes']??[]),
        static fn($path)=>preg_match('~^database/seeders/V3/Tenses/(?:PresentSimple|PresentContinuous)/[A-Za-z0-9]+/definition\.json$~D',$path)===1));
    $actualSupplement=array_keys($supplement['records']);sort($expectedSupplement);sort($actualSupplement);
    if(count($expectedSupplement)!==6||$actualSupplement!==$expectedSupplement){throw new RuntimeException('M86 exact six supplementary gap-bank paths differ.');}
    $dispatch=['app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php'];
    if(array_column($ledger['files'],'path')!==$dispatch){throw new RuntimeException('M86 exact eight shared source paths differ.');}
    $capturedOwn=array_values(array_filter(array_keys($capture['records']),static fn($path)=>str_contains($path,'M86')||str_contains($path,'m86')));
    if($capturedOwn!==[]){throw new RuntimeException('M86 paired BEFORE must predate every new M86 own source; no in-progress exception.');}
    $definitions=array_map(App\Support\TheorySentenceBuilderAcceptance::definitionPath(...),App\Support\M86PresentSimpleContinuousBasics::OWNERS);
    $checked=$unaffected=$supplementChecks=$bankSourceChecks=$foreignChecks=$absentChecks=0;
    foreach(['served_root'=>$root,'worktree'=>$wt]as $context=>$directory){
        $contextKey=$context==='served_root'?'root':'worktree';
        if($normal($before[$context==='served_root'?'served_root':'source_worktree']??'')!==$normal($directory)){
            throw new RuntimeException('M86 independent ROOT/WT directory identity differs.');
        }
        $visual=[];
        foreach(['resources/views','resources/css','resources/js','public/js']as $folder){
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory.'/'.$folder,FilesystemIterator::SKIP_DOTS))as $file){
                if($file->isLink()){throw new RuntimeException('M86 visual inventory contains a link.');}
                if($file->isFile()){$visual[]=substr(str_replace('\\','/',$file->getPathname()),strlen($directory)+1);}
            }
        }
        $expectedVisual=$capture['visual_paths']??[];sort($visual);sort($expectedVisual);
        if(count($expectedVisual)!==326||$visual!==$expectedVisual){throw new RuntimeException('M86 complete ROOT/WT visual inventory changed.');}
        $verifyUnchanged=static function(string $path,?array $old)use($directory,$hash,&$absentChecks):void{
            $file=$directory.'/'.$path;
            if($old===null){if(file_exists($file)||is_link($file)){throw new RuntimeException('M86 captured absent source was recreated: '.$path);}++$absentChecks;return;}
            if(!hash_equals($old['sha256'],$hash($file))){throw new RuntimeException('M86 non-target raw source changed: '.$path);}
            $raw=file_get_contents($file);
            if(strlen($raw)!==$old['bytes']||substr_count($raw,"\r\n")!==$old['crlf']||substr_count($raw,"\n")!==$old['lf']
                ||str_starts_with($raw,"\xEF\xBB\xBF")!==$old['utf8_bom']){
                throw new RuntimeException('M86 non-target byte/EOL/BOM profile changed: '.$path);
            }
        };
        $addedCount=0;
        foreach($ledger['files']as $file){
            $path=$file['path'];$pin=$file['profiles'][$context]??null;$old=$capture['records'][$path][$contextKey]??null;
            if(!is_array($pin)||!is_array($old)||($old['sha256']??null)!==($pin['before_raw_sha256']??null)
                ||($old['bytes']??null)!==($pin['before_bytes']??null)||($old['lf']??null)!==($pin['before_lf_line_count']??null)
                ||($old['crlf']??null)!==($pin['before_crlf_line_count']??null)){
                throw new RuntimeException('M86 shared predecessor pin does not derive from its own captured raw context.');
            }
            if(!hash_equals($pin['after_raw_sha256'],$hash($directory.'/'.$path))){throw new RuntimeException('M86 shared raw AFTER differs: '.$context.' '.$path);}
            $raw=file_get_contents($directory.'/'.$path);$lf=str_replace("\r\n","\n",$raw);
            if(hash('sha256',$lf)!==$pin['after_lf_sha256']||strlen($raw)!==$pin['after_bytes']
                ||substr_count($raw,"\n")!==$pin['after_lf_line_count']||substr_count($raw,"\r\n")!==$pin['after_crlf_line_count']){
                throw new RuntimeException('M86 exact shared LF/EOL AFTER profile differs.');
            }
            foreach($file['hunks']as $hunk){
                $added=substr($hunk['after'],strlen($hunk['before']));
                if($hunk['added_lines']!==1||$hunk['removed_lines']!==0||!str_starts_with($hunk['after'],$hunk['before'])
                    ||substr_count($added,"\n")!==1||!str_contains($added,'M86')||str_contains($hunk['before'],'M86')
                    ||substr_count($lf,$hunk['after'])!==1){throw new RuntimeException('M86 exact one-line own registration hunk differs.');}
                $lf=str_replace($hunk['after'],$hunk['before'],$lf);
                $lines=preg_split('/(?<=\n)/',$raw,-1,PREG_SPLIT_NO_EMPTY);
                $normalized=array_map(static fn($line)=>str_replace("\r\n","\n",$line),$lines);
                $wanted=preg_split('/(?<=\n)/',$hunk['after'],-1,PREG_SPLIT_NO_EMPTY);$positions=[];
                foreach(array_keys($lines)as $n){if(array_slice($normalized,$n,count($wanted))===$wanted){$positions[]=$n;}}
                if(count($positions)!==1){throw new RuntimeException('M86 exact raw owned-line position is missing or ambiguous.');}
                $position=$positions[0]+count($wanted)-1;
                if($lines[$position]!==$added&&$lines[$position]!==str_replace("\n","\r\n",$added)){
                    throw new RuntimeException('M86 owned insertion has an unapproved context EOL.');
                }
                array_splice($lines,$position,1);$raw=implode('',$lines);++$addedCount;
            }
            if(hash('sha256',$raw)!==$old['sha256']||hash('sha256',$raw)!==$pin['before_raw_sha256']||hash('sha256',$lf)!==$pin['before_lf_sha256']){
                throw new RuntimeException('M86 reversing only own hunks does not recover the exact captured raw predecessor.');
            }
            ++$checked;
        }
        if($addedCount!==12){throw new RuntimeException('M86 shared insertion count is not exactly twelve in this context.');}
        foreach($capture['records']as $path=>$profiles){
            if(in_array($path,$dispatch,true)||in_array($path,$definitions,true)||$path==='.gitattributes'){continue;}
            if(!array_key_exists($contextKey,$profiles)){throw new RuntimeException('M86 captured source context is missing.');}
            $verifyUnchanged($path,$profiles[$contextKey]);++$unaffected;
        }
        foreach($supplement['records']as $path=>$profiles){
            if(array_key_exists($path,$capture['records'])||!is_array($profiles[$contextKey]??null)
                ||($profiles['root']['sha256']??null)!==($consumers['source_hashes'][$path]??null)){
                throw new RuntimeException('M86 supplementary bank-source context is not independently bound.');
            }
            $verifyUnchanged($path,$profiles[$contextKey]);++$supplementChecks;
        }
        if(count($consumers['source_hashes']??[])!==14){throw new RuntimeException('M86 fourteen original bank-source declarations differ.');}
        foreach($consumers['source_hashes']as $path=>$sha){
            $profile=$capture['records'][$path][$contextKey]??($supplement['records'][$path][$contextKey]??null);
            if(!is_array($profile)||($capture['records'][$path]['root']['sha256']??$supplement['records'][$path]['root']['sha256']??null)!==$sha){
                throw new RuntimeException('M86 bank/course JSON is absent from the primary or exact supplemental source capture.');
            }
            $verifyUnchanged($path,$profile);++$bankSourceChecks;
        }
        $attributes=$capture['records']['.gitattributes'][$contextKey];
        if($attributes===null){$verifyUnchanged('.gitattributes',null);}
        else{
            $raw=file_get_contents($directory.'/.gitattributes');
            if(hash('sha256',substr($raw,0,$attributes['bytes']))!==$attributes['sha256']){
                throw new RuntimeException('M86 delivery attributes changed their exact captured prefix.');
            }
            $tail=str_replace("\r\n","\n",substr($raw,$attributes['bytes']));
            foreach(explode("\n",$tail)as $line){
                if(trim($line)===''){continue;}
                if(str_starts_with($line,'#')&&(str_contains($line,'M86')||str_contains($line,'m86'))){continue;}
                if(preg_match('~^([A-Za-z0-9_./-]+)[ \t]+text[ \t]+eol=lf$~D',$line,$m)!==1
                    ||(!in_array($m[1],$definitions,true)&&!str_contains($m[1],'M86')&&!str_contains($m[1],'m86'))
                    ||str_contains($m[1],'..')||!is_file($directory.'/'.$m[1])||is_link($directory.'/'.$m[1])){
                    throw new RuntimeException('M86 delivery attributes have a non-owned or wildcard append.');
                }
            }
        }
    }
    if(count($capture['foreign_untracked']??[])!==25){throw new RuntimeException('M86 exact pre-existing foreign untracked set differs.');}
    foreach($capture['foreign_untracked']as $path=>$old){
        if($old===null||!hash_equals($old['sha256'],$hash($wt.'/'.$path))){throw new RuntimeException('M86 pre-existing foreign untracked bytes changed.');}
        ++$foreignChecks;
    }
    return ['independent_source_before_sha256'=>$captureSha,'independent_source_before_paths'=>6856,
        'supplemental_bank_source_before_sha256'=>$supplementSha,'supplemental_bank_source_paths'=>6,'supplemental_bank_context_checks'=>$supplementChecks,
        'supplemental_scope'=>'WT gap-bank raw history begins at this fresh supplemental pre-apply capture; not inferred earlier history from ROOT.',
        'source_hunk_fixture_sha256'=>$fixtureSha,'finite_shared_files'=>8,'separate_contexts'=>2,'raw_lf_reversals'=>$checked,
        'owned_added_registration_lines_per_context'=>12,'native_release_validator_unchanged'=>true,
        'non_target_source_context_checks'=>$unaffected,'captured_absence_context_checks'=>$absentChecks,
        'all_6856_primary_paths_accounted_for'=>true,'primary_exceptions'=>'Only six target definition files, eight exact own-hunk shared paths and explicitly disclosed .gitattributes append policy.',
        'bank_course_definition_context_checks'=>$bankSourceChecks,'course_source_scope'=>'Both24-question course JSON declarations are inside the unchanged Polyglot bank sources; no separate historical M85 course capture reused.',
        'preexisting_foreign_untracked_context_checks'=>$foreignChecks,'in_progress_own_exceptions'=>[],
        'delivery_attributes'=>'Captured prefix/foreign absence exact; only explicit new M86 or the exact six target-definition file LF rules and M86 comments may append, not an unchanged-file claim.'];
};
$lineageFirst=$lineageCheck();
$oldConsumerSqlCheck=static function($db)use($before,$consumers):array{
    $banks=[];
    foreach($before['pages'] as $page){foreach(array_keys($page['bank_inventory']??[]) as $bank){$banks[]=$bank;}}
    $banks=array_values(array_unique($banks));sort($banks);
    $old=$consumers['old_sql']??[];$capturedBanks=array_values(array_unique(array_column($old['questions']??[],'seeder')));sort($capturedBanks);
    $declaredBanks=array_keys($old['source_bank_declarations']??[]);sort($declaredBanks);
    $observedBanks=array_keys($old['source_set_observations']??[]);sort($observedBanks);
    if(count($banks)!==14||$banks!==$capturedBanks||$banks!==$declaredBanks||$banks!==$observedBanks
        ||($consumers['source_bank_count']??null)!==14||count($old['questions']??[])!==912
        ||count($old['saved_tests']??[])!==14||array_keys($old['saved_tests'])!==array_keys($old['saved_question_links']??[])
        ||array_keys($old['saved_tests'])!==array_keys($old['saved_pool_declarations']??[])){
        throw new RuntimeException('M86 exact14original banks/912questions/14saved pools BEFORE inventory differs.');
    }
    $rows=static fn($q)=>$q->get()->map(static fn($r)=>(array)$r)->all();
    $ordered=static function(string $table,$query,array $priority=[])use($db,$rows):array{
        $columns=$db->getSchemaBuilder()->getColumnListing($table);
        $order=array_values(array_unique([...$priority,...(in_array('id',$columns,true)?['id']:$columns)]));
        foreach($order as $column){if(!in_array($column,$columns,true)){throw new RuntimeException('M86 old-consumer sort column differs.');}$query->orderBy($column);}
        return $rows($query);
    };
    $questions=$rows($db->table('questions')->whereIn('seeder',$banks)->orderBy('id'));
    if($questions!==$old['questions']){throw new RuntimeException('M86 full original bank question rows/timestamps changed.');}
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
    if($actual!==$old){throw new RuntimeException('M86 an original bank relation, pivot, shared option or saved membership/order/timestamp changed.');}
    return ['original_banks'=>14,'original_questions'=>912,'saved_tests'=>14,'saved_memberships'=>$memberships,
        'question_scope'=>'All912 rows assigned to14discovered original seeders, including all-level gap banks and both24-question course banks; no old relation or saved order omitted.',
        'source_sql_differences_preserved_not_repaired'=>true,
        'row_families'=>array_map('count',$actual),'all_original_rows_and_timestamps_exact'=>true,
        'all_old_banks_and_standalone_memberships_unchanged'=>true,
        'course_progress_enrollment_attempts'=>'Every actual row is protected by exact reconstruction of all 46 SQL tables; no runtime course interaction claim.'];
};
$schemaBefore=$load('schema-before-v1.json','2207fb3f9ba72c5a7bb8cc23572946d755b6704425db0ea8bf4dcb8a99d1565f');
if(($schemaBefore['kind']??null)!=='M86 fresh SELECT-only schema BEFORE'
    ||($schemaBefore['base_sha']??null)!==App\Support\M86PresentSimpleContinuousBasics::BASE_SHA
    ||($schemaBefore['before_sha256']??null)!==App\Services\M86ContentPatch::BEFORE_SHA
    ||($schemaBefore['identity']??null)!==$before['identity']||($schemaBefore['sql_before']??null)!==$before['sql_before']
    ||array_keys($schemaBefore['schema']??[])!==array_keys($before['sql_before'])
    ||($schemaBefore['db_writes']??null)!==0||($schemaBefore['http_requests']??null)!==0){
    throw new RuntimeException('M86 fresh complete forty-six-table schema BEFORE differs.');
}
$expectedSources=array_values(array_unique([...App\Services\M86ContentPatch::sourcePaths($patch,$contract,$root),
    'private:'.App\Services\M86ContentPatch::BEFORE_FILE,'private:'.App\Services\M86ContentPatch::CONSUMERS_FILE,'private:'.App\Services\M86ContentPatch::SYNC_RECEIPT]));
sort($expectedSources);$actualSources=array_keys($preview['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M86 full reviewed source/evidence list differs.');}
foreach ($preview['sources'] as $path => $sha) {
PHP;
$physical=<<<'PHP'
App\Services\M86LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.
$guard=new App\Services\M86LocalTargetGuard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key){if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1){throw new RuntimeException('M86 physical MySQL path is not local Windows.');}}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){throw new RuntimeException('M86 fresh physical MySQL identity differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
PHP;
$recheck=<<<'PHP'
} finally { $db->rollBack(); }
if(!hash_equals('2207fb3f9ba72c5a7bb8cc23572946d755b6704425db0ea8bf4dcb8a99d1565f',$hash($dir.'/schema-before-v1.json'))){
    throw new RuntimeException('M86 complete schema BEFORE bytes changed during final audit.');
}
foreach($preview['sources'] as $path=>$sha){$file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M86 source/evidence changed during final audit.');}}
foreach([$before['physical_hashes'],$before['visual_hashes'],$before['frozen_hashes']] as $n=>$records){foreach($records as $path=>$sha){
    if(!hash_equals($sha,$hash($n===0?$path:$root.'/'.$path))){throw new RuntimeException('M86 physical/visual/frozen bytes changed during final audit.');}
}}
foreach($sync['files'] as $record){if($hash($root.'/'.$record['path'])!==$record['root_after_sha256']
    ||$hash(App\Services\M86ContentPatch::SOURCE_WORKTREE.'/'.$record['path'])!==$record['worktree_sha256']){throw new RuntimeException('M86 source-sync changed during final audit.');}}
if($lineageCheck()!==$lineageFirst){throw new RuntimeException('M86 independent raw/LF source lineage changed during final audit.');}
$repeatEvidence=null;
if($profileContext['priorName']!==null){
    $prior=$load($profileContext['priorName'],$profileContext['priorSha']);
    if(($prior['plan_digest']??null)!==$reviewedDigest||($prior['independent_before_sha256']??null)!==App\Services\M86ContentPatch::BEFORE_SHA
        ||($prior['backup_sha256']??null)!==$profileContext['backupSha']||($prior['sql_after']??null)!==$rawAfter
        ||($prior['normalized_before']??null)!==$normalized||($prior['protected']??null)!==$protected
        ||($prior['new_practices']??null)!==$practiceFacts||($prior['retained_practices']??null)!==$retainedPracticeFacts||($prior['source_lineage']??null)!==$lineageFirst||($prior['old_consumer_sql']??null)!==$consumerFacts
        ||($prior['approved_footer_url_additions']??null)!==$navigationAdditions||($prior['approved_footer_url_changes']??null)!==$navigationChanges
        ||($prior['all_old_rows_and_timestamps_exact']??null)!==true||($prior['db_writes']??null)!==0){
        throw new RuntimeException('M86 post-UI repeat differs from the exact prior successful read-only receipt.');
    }
    $repeatEvidence=['file'=>$profileContext['priorName'],'sha256'=>$profileContext['priorSha'],
        'all_46_after_fingerprints_exact'=>true,'new_practice_rows_exact'=>true,'source_lineage_exact'=>true];
}
if(!hash_equals($profileContext['backupSha'],$hash($dir.'/'.$profileContext['backupName']))
    ||file_get_contents($dir.'/'.$previewName)!==file_get_contents($dir.'/'.$profileContext['backupName'])){throw new RuntimeException('M86 exclusive backup changed during audit.');}
$receipt =
PHP;
$insertCheck=<<<'PHP'
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        if($i<4||$target['identity']!==App\Support\M86PresentSimpleContinuousBasics::OWNERS[$i]
            ||$position!==App\Support\M86PresentSimpleContinuousBasics::PRACTICE_SOURCE_INDICES[$i]+1
            ||$position!==App\Support\M86PresentSimpleContinuousBasics::CONTINUING_COUNTS[$i]+1
            ||$source['type']!=='practice-set'||!array_key_exists('level',$source)||$source['level']!==null||$source['column']!=='left'
            ||isset($insertOwnerSeen[$target['identity']])){
            throw new RuntimeException('M86 only one appended SQL NULL left practice per exact EF owner is approved.');
        }
        $insertOwnerSeen[$target['identity']]=true;$page=$before['pages'][$target['identity']]['page'];
        $fields=['uuid'=>$uuid,'page_id'=>$page['id'],'page_category_id'=>$page['page_category_id'],'locale'=>'uk',
            'type'=>$source['type'],'column'=>$source['column'],'heading'=>$source['heading']??null,'css_class'=>$source['css_class']??null,
            'sort_order'=>$position,'body'=>$source['body'],'level'=>$source['level'],'seeder'=>$target['identity']];
        $same($insert['fields'],$fields,'M86 reviewed practice insert does not exactly match frozen source fields.');
PHP;
$profile=[
    ['M53PassiveConstructionsPackage','M86PresentSimpleContinuousBasics',7],['M53','M86',15],['m53','m86',4],
    [$originalInventory,$inventory,1],[$originalQuestions,$questions,1],
    ['    $newPractices = []; $practiceFacts = [];','    $newPractices = []; $practiceFacts = []; $insertOwnerSeen=[];',1],
    ['        $uuid = App\\Support\\M26DetailPackage::uuid($target[\'identity\'], $source, $position);',$insertCheck,1],
    ["\$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);","if(!isset(\$schemaBefore['schema'][\$table])||\$db->getSchemaBuilder()->getColumns(\$table)!==\$schemaBefore['schema'][\$table]){throw new RuntimeException('M86 full captured SQL schema changed.');}\n        \$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);",1],
    ['foreach ($preview[\'sources\'] as $path => $sha) {',$extraSource,1],
    ["    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }",
        "    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }\n    \$consumerFacts=\$oldConsumerSqlCheck(\$db);",1],
    ['App\\Services\\M86LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.',$physical,1],
    ["'question_plan_sha256' => App\\Services\\M86ContentPatch::QUESTION_PLAN_SHA,","'backup_file'=>\$profileContext['backupName'],'backup_sha256'=>\$profileContext['backupSha'],'backup_equals_reviewed_preview'=>true,\n    'source_lineage'=>\$lineageFirst,'post_ui_repeat'=>\$repeatEvidence,'old_consumer_sql'=>\$consumerFacts,\n    'metadata_transitions'=>\$metadata,'retained_practices'=>\$retainedPracticeFacts,'body_update_definition'=>'Teaching includes all47continuing native blocks, including four retained practice bodies; no Page or continuing metadata/timestamps change.','update_operations'=>count(\$expectedChanges),'body_updates'=>\$bodyKinds,'question_family_insert_counts'=>array_map('count',\$newRows),'question_family_insert_rows'=>0,'original_question_rows_updated'=>0,'full_schema_tables_checked'=>46,\n    'approved_footer_url_additions'=>\$navigationAdditions,'approved_footer_url_changes'=>\$navigationChanges,'all_footer_labels_order_current_preserved'=>true,",1],
    ["} finally { \$db->rollBack(); }\n\$receipt =",$recheck,1],
];
if($profileCheckOnly){
    $immutable=__DIR__.'/m53-postconditions.php';$source=file_get_contents($immutable);
    if(is_link($immutable)||!hash_equals('8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',hash('sha256',$source))){throw new RuntimeException('M86 immutable scanner bytes differ.');}
    foreach($profile as [$needle,$replacement,$count]){if(substr_count($source,$needle)!==$count){throw new RuntimeException('M86 pure profile substitution count differs: '.substr($needle,0,90));}$source=str_replace($needle,$replacement,$source);}
    token_get_all($source,TOKEN_PARSE);
    echo json_encode(['profile'=>'M86 immutable SELECT-only scanner','literal_substitutions'=>count($profile),'composed_php_parsed'=>true,
        'db_bootstraps'=>0,'db_writes'=>0,'http_requests'=>0,'private_evidence_opened'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
    exit(0);
}
runFrozenReadOnlyProfile('m53-postconditions.php','8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',$profile,
    ['backupName'=>$backupName,'backupSha'=>$backupSha,'priorName'=>$priorName,'priorSha'=>$priorSha]);
