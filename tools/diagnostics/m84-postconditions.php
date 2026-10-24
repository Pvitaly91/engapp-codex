<?php

/**
 * Independent SELECT-only M84 reconstruction via the immutable M53 scanner.
 * Six owners, 64 continuing UK rows, one new practice, 25 questions / 369 rows.
 * No writer plan, apply, transaction hook or question-state method is invoked.
 * Args: preview, reviewed digest, backup, backup raw SHA, fresh final receipt.
 * Optionally append prior final receipt + raw SHA for an exact post-UI repeat.
 * Never calls a writer, apply/restore, seeder or HTTP endpoint.
 */
require __DIR__.'/frozen-readonly-profile.php';
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array(count($argv),[6,8],true)
    ||preg_match('/^preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1
    ||preg_match('/^backup-v[1-9][0-9]*\.json$/D',$argv[3])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[5])!==1){
    throw new RuntimeException('M84 reviewed preview/digest, exclusive backup/raw SHA, fresh final basename required.');
}
$backupName=$argv[3];$backupSha=$argv[4];$private='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m84-local';
$priorName=$argv[6]??null;$priorSha=$argv[7]??null;
if(($priorName!==null||$priorSha!==null)&&(count($argv)!==8
    ||!is_string($priorName)||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$priorName)!==1
    ||!is_string($priorSha)||preg_match('/^[a-f0-9]{64}$/D',$priorSha)!==1
    ||$priorName===$argv[5]||!is_file($private.'/'.$priorName)||is_link($private.'/'.$priorName)
    ||!hash_equals($priorSha,hash_file('sha256',$private.'/'.$priorName)))){
    throw new RuntimeException('M84 optional prior final receipt name/raw SHA differs; no repeat claim.');
}
foreach([$argv[1],$backupName] as $name){if(!is_file($private.'/'.$name)||is_link($private.'/'.$name)){throw new RuntimeException('M84 reviewed preview/backup absent or linked.');}}
if(!hash_equals($backupSha,hash_file('sha256',$private.'/'.$backupName))
    ||file_get_contents($private.'/'.$argv[1])!==file_get_contents($private.'/'.$backupName)){
    throw new RuntimeException('M84 exclusive backup bytes are not the exact reviewed preview.');
}
$argv=[$argv[0],$argv[1],$argv[2],$argv[5]];
$inventory=<<<'PHP'
    $pageBefore = []; $pageAfter = []; $ukBefore = []; $ukAfter = []; $nonUk = 0;
    $expectedChanges=[];$bodyKinds=['subtitle'=>0,'teaching'=>0,'practice'=>0];$metadata=[];
    foreach ($patch['targets'] as $i => $target) {
        $owner=$target['identity'];$captured=$before['pages'][$owner];$oldPage=$captured['page'];
        $source=$sourceBefore['targets'][$i]['before'];
        if($oldPage['id']!==App\Support\M84WordOrderHaveGot::PAGE_IDS[$i]
            ||$oldPage['seeder']!==$owner||$oldPage['page_category_id']!==App\Support\M84WordOrderHaveGot::CATEGORY_IDS[$i]
            ||$oldPage['text']!==$source['page']['subtitle_text']){
            throw new RuntimeException('M84 independent Page/source identity or subtitle differs.');
        }
        foreach(['page','category_ancestry','blocks'] as $field){
            if(($preview['pages'][$owner][$field]??null)!==$captured[$field]){
                throw new RuntimeException('M84 reviewed original inventory differs from independent BEFORE.');
            }
        }
        if(($before['definitions'][$owner]['definition']??null)!==$source){
            throw new RuntimeException('M84 frozen source BEFORE differs from independent working capture.');
        }
        App\Services\M84ContentPatch::assertDefinitionScope($source,$target['after'],$target,$i);
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
            if(!is_int($position)||$position<0||$position>App\Support\M84WordOrderHaveGot::CONTINUING_COUNTS[$i]
                ||isset($positions[$position])||isset($ukBefore[$old['id']])
                ||$old['seeder']!==$owner||$old['page_id']!==$oldPage['id']
                ||$old['page_category_id']!==$oldPage['page_category_id']){
                throw new RuntimeException('M84 exact continuing owner/locale/order inventory differs.');
            }
            $positions[$position]=true;
            $config=$position===0?['body'=>$target['after']['page']['subtitle_html'],'type'=>'subtitle','column'=>'header',
                'heading'=>null,'level'=>$source['page']['subtitle_level']??null,'css_class'=>null]
                :$target['after']['page']['blocks'][$position-1];
            $oldConfig=$position===0?['body'=>$source['page']['subtitle_html']]
                :$source['page']['blocks'][$position-1];
            if(!is_string($config['body']??null)||($oldConfig['body']??null)!==$old['body']){
                throw new RuntimeException('M84 independent source/SQL body correspondence differs.');
            }
            foreach(['type','column','heading','level','css_class'] as $field){
                if(($config[$field]??null)!==$old[$field]){
                    throw new RuntimeException('M84 no continuing metadata transition is approved: '.$field);
                }
            }
            if($position>0&&App\Support\M26DetailPackage::uuid($owner,$oldConfig,$position)!==$old['uuid']){
                throw new RuntimeException('M84 continuing source/SQL UUID correspondence differs.');
            }
            $ukBefore[$old['id']]=$old;$ukAfter[$old['id']]=array_replace($old,['body'=>$config['body']]);
            if($config['body']!==$old['body']){
                $expectedChanges['text_blocks:'.$old['id']]=['table'=>'text_blocks','id'=>$old['id'],'uuid'=>$old['uuid'],
                    'seeder'=>$owner,'before'=>['body'=>$old['body']],'after'=>['body'=>$config['body']]];
                $kind=$position===0?'subtitle':($i<5&&$position===App\Support\M84WordOrderHaveGot::PRACTICE_SOURCE_INDICES[$i]+1?'practice':'teaching');
                ++$bodyKinds[$kind];
            }
        }
        $actualPositions=array_keys($positions);sort($actualPositions);
        if($actualPositions!==range(0,App\Support\M84WordOrderHaveGot::CONTINUING_COUNTS[$i])){
            throw new RuntimeException('M84 exact continuing native position vector differs.');
        }
        if($i<5){
            $matches=array_values(array_filter($captured['blocks'],static fn($row)=>$row['locale']==='uk'
                &&$row['sort_order']===App\Support\M84WordOrderHaveGot::PRACTICE_SOURCE_INDICES[$i]+1));
            if(count($matches)!==1||$matches[0]['type']!=='practice-set'||$matches[0]['level']!==null
                ||$matches[0]['uuid']!==App\Support\M84WordOrderHaveGot::EXISTING_PRACTICE_UUIDS[$i]){
                throw new RuntimeException('M84 existing practice UUID/order/NULL level differs.');
            }
        }
    }
    $expectedUkRows=array_sum(array_map(static fn($count)=>$count+1,App\Support\M84WordOrderHaveGot::CONTINUING_COUNTS));
    if(count($pageBefore)!==6||$expectedUkRows!==64||count($ukBefore)!==$expectedUkRows||$nonUk!==128
        ||$metadata!==[]||$expectedChanges===[]||count($preview['updates']??[])!==count($expectedChanges)
        ||count($preview['inserts']??[])!==1||($preview['deletes']??[])!==[]
        ||!is_array($preview['question_supplement']??null)){
        throw new RuntimeException('M84 exact continuing/body-only/one-append/question-supplement scope differs.');
    }
    $seenChanges=[];
    foreach($preview['updates'] as $change){
        $key=$change['table'].':'.$change['id'];$expected=$expectedChanges[$key]??null;
        if($expected===null||isset($seenChanges[$key])
            ||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
            ||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$expected
            ||$change['before_sha256']!==$digest($expected['before'])||$change['after_sha256']!==$digest($expected['after'])){
            throw new RuntimeException('M84 exact independently derived reviewed update inventory differs.');
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
    $service = new App\Services\M84ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm84QuestionState'))->invoke($service, 'after', $patch, false);
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
$questionInventory = $load(App\Services\M84ContentPatch::QUESTION_INVENTORY_FILE, App\Services\M84ContentPatch::QUESTION_INVENTORY_SHA);
$questionPlan = $load(App\Services\M84ContentPatch::QUESTION_PLAN_FILE, App\Services\M84ContentPatch::QUESTION_PLAN_SHA); $questionSource = App\Support\M84QuestionSupplement::load($root);
if (($questionInventory['kind'] ?? null) !== 'M84_question_supplement_inventory' || ($questionInventory['base_sha'] ?? null) !== App\Support\M84WordOrderHaveGot::BASE_SHA || ($questionInventory['before_sha256'] ?? null) !== App\Services\M84ContentPatch::BEFORE_SHA
    || ($questionInventory['source_sha256'] ?? null) !== App\Support\M84QuestionSupplement::SOURCE_SHA || ($questionInventory['identity'] ?? null) !== $before['identity']
    || ($questionInventory['sql_before'] ?? null) !== $before['sql_before'] || ($questionInventory['db_writes'] ?? null) !== 0
    || ($questionInventory['http_requests'] ?? null) !== 0 || ($questionInventory['id_step_offset'] ?? null) !== [1, 1]
    || ($questionInventory['bindings'] ?? null) !== App\Support\M84QuestionSupplement::bindings()
    || ($questionInventory['question_collisions'] ?? null) !== array_fill_keys(array_keys(App\Support\M51QuestionSupplement::ROW_COLUMNS), [])
    || ($questionPlan['kind'] ?? null) !== 'M84_question_insert_plan' || ($questionPlan['base_sha'] ?? null) !== App\Support\M84WordOrderHaveGot::BASE_SHA
    || ($questionPlan['before_sha256'] ?? null) !== App\Services\M84ContentPatch::BEFORE_SHA || ($questionPlan['source_sha256'] ?? null) !== App\Support\M84QuestionSupplement::SOURCE_SHA
    || ($questionPlan['inventory'] ?? null) !== ['file' => App\Services\M84ContentPatch::QUESTION_INVENTORY_FILE, 'sha256' => App\Services\M84ContentPatch::QUESTION_INVENTORY_SHA]
    || ($questionPlan['bindings'] ?? null) !== App\Support\M84QuestionSupplement::bindings() || ($questionPlan['ids'] ?? null) !== ($questionInventory['ids'] ?? null)
    || ($questionPlan['row_timestamp'] ?? null) !== ($questionInventory['row_timestamp'] ?? null)) { throw new RuntimeException('Question source/inventory/plan independent lineage differs.'); }
$projection = App\Support\M84QuestionSupplement::project($questionSource, App\Support\M84QuestionSupplement::bindings(), $questionInventory['option_matches']);
$questionRows = App\Support\M51QuestionSupplement::materialize($projection, $questionInventory['ids'], $questionInventory['row_timestamp']);
if ($projection !== ($questionInventory['projection'] ?? null) || $projection !== ($questionPlan['projection'] ?? null)
    || $questionRows !== ($questionInventory['materialized'] ?? null) || $questionRows !== ($questionPlan['materialized'] ?? null)
    || $questionRows !== ($preview['question_supplement']['expected'] ?? null) || $projection !== ($preview['question_supplement']['projection'] ?? null)
    || ($preview['question_supplement']['actual'] ?? null) !== array_fill_keys(array_keys(App\Support\M51QuestionSupplement::ROW_COLUMNS), [])
    || count($questionRows['questions']) !== 25 || count($questionRows['question_theory_text_blocks']) !== 25
    || array_count_values(array_column($questionRows['questions'], 'seeder')) !== array_combine(App\Support\M84QuestionSupplement::BANKS, [5, 5, 5, 5, 5])) { throw new RuntimeException('Twenty-five-question/five-family pure materialization differs.'); }

if (array_map('count', $questionRows) !== ['questions'=>25,'question_options'=>51,'question_answers'=>99,'question_option_question'=>169,'question_theory_text_blocks'=>25]
    || array_sum(array_map('count', $questionRows)) !== 369) { throw new RuntimeException('M84 exact reviewed 369 inserted family rows differ.'); }
$newRows = $questionRows; $approved = []; $actualNewRows = []; $questionIds = array_column($newRows['questions'], 'id');
foreach ($newRows as $table => $rows) {
    $approved[$table] = array_column($rows, null, 'id'); $ids = array_column($rows, 'id');
    $query = $db->table($table)->whereIn('id', $ids);
    if ($table === 'questions') { $query->orWhereIn('uuid', App\Support\M84QuestionSupplement::UUIDS)->orWhereIn('seeder', App\Support\M84QuestionSupplement::BANKS); }
    elseif ($table === 'question_theory_text_blocks') { $query->orWhereIn('question_uuid', App\Support\M84QuestionSupplement::UUIDS); }
    elseif (in_array($table, ['question_answers','question_option_question'], true)) { $query->orWhereIn('question_id', $questionIds); }
    $actualNewRows[$table] = $query->orderBy('id')->get()->map(static fn($row)=>(array)$row)->all();
}
App\Support\M51QuestionSupplement::assertApplied($newRows, $actualNewRows);
foreach (['question_tag','question_marker_tag','verb_hints','question_hints','question_variants'] as $table) {
    if ($db->table($table)->whereIn('question_id', $questionIds)->exists()) { throw new RuntimeException('M84 supplemental question gained an unapproved relation.'); }
}
if ($db->table('saved_grammar_test_questions')->whereIn('question_uuid', App\Support\M84QuestionSupplement::UUIDS)->exists()) {
    throw new RuntimeException('M84 supplemental questions cannot enter old standalone test membership.');
}
$newOptions = [];
foreach ($projection['question_options'] as $n => $record) { $newOptions[$record['key']] = $newRows['question_options'][$n]; }
$reused = array_column($projection['reused_options'], null, 'key');
foreach ($questionInventory['option_matches'] as $token => $matches) {
    $key = 'option-'.hash('sha256', mb_strtolower($token));
    $expected = $matches !== [] ? [$reused[$key]['row']] : [$newOptions[$key]];
    $actual = $db->table('question_options')->where('option', $token)->orderBy('id')->get()->map(static fn($row)=>(array)$row)->all();
    if ($actual !== $expected) { throw new RuntimeException('M84 full SQL-collation option lookup changed or collided.'); }
}
PHP;
$extraSource=<<<'PHP'
$consumers=$load(App\Services\M84ContentPatch::CONSUMERS_FILE,App\Services\M84ContentPatch::CONSUMERS_SHA);
App\Services\M51ContentPatch::assertConsumerCapture($before,$consumers);
if(count($before['sql_before'])!==46||count($before['visual_hashes'])!==326||count($before['frozen_hashes'])!==2858
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']||($consumers['all_tables_after_http']??null)!==$before['sql_before']){
    throw new RuntimeException('M84 independent full-table/old-consumer baseline differs.');
}
$navigation=json_decode(file_get_contents($root.'/'.App\Support\M84WordOrderHaveGot::NAVIGATION),true,flags:JSON_THROW_ON_ERROR);
if(($master['editorial_review']['navigation_review']??null)!==$navigation
    ||array_column($navigation['targets']??[],'identity')!==App\Support\M84WordOrderHaveGot::OWNERS){
    throw new RuntimeException('M84 independent frozen footer navigation review differs.');
}
$navigationAdditions=0;$navigationChanges=[];
foreach($navigation['targets'] as $i=>$review){
    $slot=App\Support\M84WordOrderHaveGot::NAVIGATION_SOURCE_INDICES[$i];
    if($slot===null){if(($review['items']??null)!==[]){throw new RuntimeException('M84 Have got must not invent an old footer.');}continue;}
    $oldNavigation=json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);
    $newNavigation=$master['lessons'][$i]['blocks'][$slot]['data'];
    $labelCurrent=static fn(array $items)=>array_map(static fn(array $item)=>
        array_intersect_key($item,array_flip(['label','current'])),$items);
    if(($review['source_index']??null)!==$slot||($review['items']??null)!==($newNavigation['items']??null)
        ||$labelCurrent($newNavigation['items'])!==$labelCurrent($oldNavigation['items'])
        ||array_diff_key($newNavigation,['items'=>true])!==array_diff_key($oldNavigation,['items'=>true])){
        throw new RuntimeException('M84 footer title/labels/current/order differ from independent source and verified destinations.');
    }
    foreach($newNavigation['items'] as $n=>$item){
        $old=$oldNavigation['items'][$n];
        $beforeUrl=$old['url']??null;$afterUrl=$item['url']??null;
        $otherBefore=$old;$otherAfter=$item;unset($otherBefore['url'],$otherAfter['url']);
        if($otherBefore!==$otherAfter){throw new RuntimeException('M84 footer has a non-URL data change.');}
        if($beforeUrl!==$afterUrl){
            $navigationChanges[]=['identity'=>$review['identity'],'source_index'=>$slot,'item_index'=>$n,
                'before_url'=>$beforeUrl,'after_url'=>$afterUrl];
            if($beforeUrl===null&&is_string($afterUrl)){++$navigationAdditions;}
        }
    }
}
$lineageCheck=static function()use($root,$dir,$hash,$load,$normal):array{
    $captureSha='9066938a376b3f4eeae6fa65f6fea6afc7a2bdd624a4ed93dc32e37c59b8e47e';
    $fixtureSha='9bf427faa6045c7060e2d369c9caea1c797dcd80f573e8f7e0cd4eafe2cdee84';
    $courseSha='dd8c7e8c08b256e5fe67ec24e8ba0afd3d6342c431478ab024e8a39e6b7bb870';
    $capture=$load('independent-source-before-v1.json',$captureSha);
    $course=$load('course-source-before-v1.json',$courseSha);
    $wt=App\Services\M84ContentPatch::SOURCE_WORKTREE;$fixture=$wt.'/tests/Fixtures/m84-owned-source-hunks.v1.0.1.json';
    if(!hash_equals($fixtureSha,$hash($fixture))){throw new RuntimeException('M84 independent source-hunk fixture bytes differ.');}
    $ledger=json_decode(file_get_contents($fixture),true,flags:JSON_THROW_ON_ERROR);
    if(($capture['base_sha']??null)!==App\Support\M84WordOrderHaveGot::BASE_SHA
        ||($capture['phase']??null)!=='before'||count($capture['files']??[])!==3390
        ||($ledger['base_sha']??null)!==App\Support\M84WordOrderHaveGot::BASE_SHA
        ||($ledger['independent_source_before_sha256']??null)!==$captureSha
        ||($ledger['independent_db_before_sha256']??null)!==App\Services\M84ContentPatch::BEFORE_SHA
        ||($ledger['independent_course_source_before_sha256']??null)!==$courseSha
        ||count($ledger['files']??[])!==8||($ledger['owned_added_registration_lines']??null)!==12
        ||($ledger['owned_validator_added_lines']??0)!==0||($ledger['owned_validator_removed_lines']??0)!==0
        ||($course['base_sha']??null)!==App\Support\M84WordOrderHaveGot::BASE_SHA
        ||($course['phase']??null)!=='before'||count($course['files']??[])!==122){
        throw new RuntimeException('M84 fresh source/course BEFORE and finite shared lineage differ.');
    }
    $dispatch=['app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php'];
    if(array_column($ledger['files'],'path')!==$dispatch){throw new RuntimeException('M84 exact eight shared source paths differ.');}
    $checked=$unaffected=$courseChecked=0;
    foreach(['served_root'=>$root,'worktree'=>$wt]as $context=>$directory){
        if($normal($capture['roots'][$context]??'')!==$normal($directory)
            ||$normal($course['roots'][$context]??'')!==$normal($directory)){throw new RuntimeException('M84 independent source ROOT/WT identity differs.');}
        $visual=[];
        foreach(['resources/views','resources/css','resources/js','public/js'] as $folder){
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory.'/'.$folder,FilesystemIterator::SKIP_DOTS)) as $file){
                if($file->isLink()){throw new RuntimeException('M84 visual inventory contains a link.');}
                if($file->isFile()){$visual[]=substr(str_replace('\\','/',$file->getPathname()),strlen($directory)+1);}
            }
        }
        $expectedVisual=$capture['groups']['visual']??[];sort($visual);sort($expectedVisual);
        if(count($expectedVisual)!==326||$visual!==$expectedVisual){throw new RuntimeException('M84 complete ROOT/WT visual inventory changed.');}
        $addedCount=0;
        foreach($ledger['files']as $file){
            $path=$file['path'];$pin=$file['profiles'][$context]??null;$old=$capture['files'][$path][$context]??null;
            if(!is_array($pin)||!is_array($old)||($old['exists']??null)!==true
                ||($old['raw_sha256']??null)!==($pin['before_raw_sha256']??null)
                ||($old['lf_sha256']??null)!==($pin['before_lf_sha256']??null)){
                throw new RuntimeException('M84 predecessor source pin does not derive from independent BEFORE.');
            }
            if(!hash_equals($pin['after_raw_sha256'],$hash($directory.'/'.$path))){throw new RuntimeException('M84 shared raw AFTER source differs: '.$path);}
            $raw=file_get_contents($directory.'/'.$path);$lf=str_replace("\r\n","\n",$raw);
            if(hash('sha256',$lf)!==$pin['after_lf_sha256']||substr_count($raw,"\n")!==$pin['after_lf_line_count']
                ||substr_count($raw,"\r\n")!==$pin['after_crlf_line_count']){throw new RuntimeException('M84 shared LF/EOL profile differs.');}
            foreach($file['hunks']as $index=>$hunk){
                $added=substr($hunk['after'],strlen($hunk['before']));
                if($hunk['added_lines']!==1||$hunk['removed_lines']!==0||!str_starts_with($hunk['after'],$hunk['before'])
                    ||substr_count($added,"\n")!==1||!str_contains($added,'M84')||str_contains($hunk['before'],'M84')
                    ||substr_count($lf,$hunk['after'])!==1){throw new RuntimeException('M84 exact one-line registration hunk differs.');}
                $lf=str_replace($hunk['after'],$hunk['before'],$lf);
                $lines=preg_split('/(?<=\n)/',$raw,-1,PREG_SPLIT_NO_EMPTY);
                $normalized=array_map(static fn($line)=>str_replace("\r\n","\n",$line),$lines);
                $wanted=preg_split('/(?<=\n)/',$hunk['after'],-1,PREG_SPLIT_NO_EMPTY);$positions=[];
                foreach(array_keys($lines)as $n){if(array_slice($normalized,$n,count($wanted))===$wanted){$positions[]=$n;}}
                if(count($positions)!==1){throw new RuntimeException('M84 raw hunk location is missing or ambiguous.');}
                $position=$positions[0]+count($wanted)-1;
                if($lines[$position]!==$added&&$lines[$position]!==str_replace("\n","\r\n",$added)){
                    throw new RuntimeException('M84 owned registration line has an unapproved raw EOL.');
                }
                array_splice($lines,$position,1);$raw=implode('',$lines);++$addedCount;
            }
            if(hash('sha256',$raw)!==$pin['before_raw_sha256']||hash('sha256',$lf)!==$pin['before_lf_sha256']){
                throw new RuntimeException('M84 reversing only owned hunks does not restore independent raw/LF BEFORE.');
            }
            ++$checked;
        }
        if($addedCount!==12){throw new RuntimeException('M84 exact shared registration addition count differs by context.');}
        $verifyUnchanged=static function(string $path,array $old)use($directory,$hash):void{
            $file=$directory.'/'.$path;
            if(($old['exists']??null)===false){if(file_exists($file)||is_link($file)){throw new RuntimeException('M84 foreign absent source was recreated: '.$path);}return;}
            if(($old['exists']??null)!==true||!hash_equals($old['raw_sha256'],$hash($file))){throw new RuntimeException('M84 non-target raw source changed: '.$path);}
            $raw=file_get_contents($file);
            if(strlen($raw)!==$old['bytes']||hash('sha256',str_replace("\r\n","\n",$raw))!==$old['lf_sha256']
                ||substr_count($raw,"\r\n")!==$old['crlf']||substr_count($raw,"\n")!==$old['lf']){throw new RuntimeException('M84 non-target LF/EOL source changed: '.$path);}
        };
        foreach($capture['files']as $path=>$profiles){
            if(in_array($path,$dispatch,true)||in_array($path,array_map(App\Support\TheorySentenceBuilderAcceptance::definitionPath(...),App\Support\M84WordOrderHaveGot::OWNERS),true)||$path==='.gitattributes'){continue;}
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
                throw new RuntimeException('M84 delivery attributes do not retain the exact existing raw prefix.');
            }
        }
    }
    return ['independent_source_before_sha256'=>$captureSha,'independent_source_before_paths'=>3390,
        'source_hunk_fixture_sha256'=>$fixtureSha,'finite_shared_files'=>8,'separate_contexts'=>2,
        'raw_lf_reversals'=>$checked,'owned_added_registration_lines_per_context'=>12,
        'native_release_validator_unchanged'=>true,
        'non_target_source_context_checks'=>$unaffected,'course_source_before_sha256'=>$courseSha,
        'course_source_context_checks'=>$courseChecked,'delivery_attributes'=>'Before prefix/foreign absence preserved; appended M84 EOL rules are a separate delivery review, not an unchanged-file claim.',
        'all_3390_paths_unchanged_claimed'=>false];
};
$lineageFirst=$lineageCheck();
$oldConsumerSqlCheck=static function($db)use($before,$consumers):array{
    $banks=[];
    foreach($before['pages'] as $page){foreach(array_keys($page['bank_inventory']??[]) as $bank){$banks[]=$bank;}}
    $banks=array_values(array_unique($banks));sort($banks);
    $old=$consumers['old_sql']??[];$capturedBanks=array_values(array_unique(array_column($old['questions']??[],'seeder')));sort($capturedBanks);
    if(count($banks)!==13||$banks!==$capturedBanks||count($old['questions']??[])!==888
        ||count($old['saved_tests']??[])!==13||array_keys($old['saved_tests'])!==array_keys($old['saved_question_links']??[])){
        throw new RuntimeException('M84 exact thirteen original bank/saved-test BEFORE inventory differs.');
    }
    $rows=static fn($q)=>$q->get()->map(static fn($r)=>(array)$r)->all();
    $ordered=static function(string $table,$query,array $priority=[])use($db,$rows):array{
        $columns=$db->getSchemaBuilder()->getColumnListing($table);
        $order=array_values(array_unique([...$priority,...(in_array('id',$columns,true)?['id']:$columns)]));
        foreach($order as $column){if(!in_array($column,$columns,true)){throw new RuntimeException('M84 old-consumer sort column differs.');}$query->orderBy($column);}
        return $rows($query);
    };
    $questions=$rows($db->table('questions')->whereIn('seeder',$banks)->orderBy('id'));
    if($questions!==$old['questions']){throw new RuntimeException('M84 full original bank question rows/timestamps changed.');}
    $courseBank='Database\\Seeders\\V3\\Polyglot\\PolyglotHaveGotHasGotLessonSeeder';
    if(count(array_filter($questions,static fn($q)=>$q['seeder']===$courseBank))!==24){
        throw new RuntimeException('M84 the original twenty-four-question Have got A1 course bank changed.');
    }
    $actual=['questions'=>$questions];$ids=array_column($questions,'id');$uuids=array_column($questions,'uuid');
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
    if($actual!==$old){throw new RuntimeException('M84 an original bank relation, pivot, shared option or saved membership/order/timestamp changed.');}
    return ['original_banks'=>13,'original_questions'=>888,'saved_tests'=>13,'saved_memberships'=>$memberships,
        'have_got_a1_course_bank_questions'=>24,'have_got_course_bank_used_as_theory_bank'=>false,
        'row_families'=>array_map('count',$actual),'all_original_rows_and_timestamps_exact'=>true,
        'all_old_banks_and_standalone_memberships_unchanged'=>true,
        'course_progress_enrollment_attempts'=>'Every actual row is protected by exact reconstruction of all 46 SQL tables; no runtime course interaction claim.'];
};
$schemaBefore=$load('schema-before-v1.json','5abd9d320e2f10dda152787f6fd25e7c54445ab38677e58a85cda340d09976cb');
if(($schemaBefore['kind']??null)!=='M84 fresh SELECT-only schema BEFORE'
    ||($schemaBefore['base_sha']??null)!==App\Support\M84WordOrderHaveGot::BASE_SHA
    ||($schemaBefore['before_sha256']??null)!==App\Services\M84ContentPatch::BEFORE_SHA
    ||($schemaBefore['identity']??null)!==$before['identity']||($schemaBefore['sql_before']??null)!==$before['sql_before']
    ||array_keys($schemaBefore['schema']??[])!==array_keys($before['sql_before'])
    ||($schemaBefore['db_writes']??null)!==0||($schemaBefore['http_requests']??null)!==0){
    throw new RuntimeException('M84 fresh complete forty-six-table schema BEFORE differs.');
}
$expectedSources=array_values(array_unique([...App\Services\M84ContentPatch::sourcePaths($patch,$contract,$root),
    'private:'.App\Services\M84ContentPatch::BEFORE_FILE,'private:'.App\Services\M84ContentPatch::CONSUMERS_FILE,'private:'.App\Services\M84ContentPatch::SYNC_RECEIPT,
    ...array_keys(App\Services\M84ContentPatch::questionEvidencePins()),App\Support\M84QuestionSupplement::SOURCE]));
sort($expectedSources);$actualSources=array_keys($preview['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M84 full reviewed source/evidence list differs.');}
foreach ($preview['sources'] as $path => $sha) {
PHP;
$physical=<<<'PHP'
App\Services\M84LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.
$guard=new App\Services\M84LocalTargetGuard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key){if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1){throw new RuntimeException('M84 physical MySQL path is not local Windows.');}}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){throw new RuntimeException('M84 fresh physical MySQL identity differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
PHP;
$recheck=<<<'PHP'
} finally { $db->rollBack(); }
if(!hash_equals('5abd9d320e2f10dda152787f6fd25e7c54445ab38677e58a85cda340d09976cb',$hash($dir.'/schema-before-v1.json'))){
    throw new RuntimeException('M84 complete schema BEFORE bytes changed during final audit.');
}
foreach($preview['sources'] as $path=>$sha){$file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!hash_equals($sha,$hash($file))){throw new RuntimeException('M84 source/evidence changed during final audit.');}}
foreach([$before['physical_hashes'],$before['visual_hashes'],$before['frozen_hashes']] as $n=>$records){foreach($records as $path=>$sha){
    if(!hash_equals($sha,$hash($n===0?$path:$root.'/'.$path))){throw new RuntimeException('M84 physical/visual/frozen bytes changed during final audit.');}
}}
foreach($sync['files'] as $record){if($hash($root.'/'.$record['path'])!==$record['root_after_sha256']
    ||$hash(App\Services\M84ContentPatch::SOURCE_WORKTREE.'/'.$record['path'])!==$record['worktree_sha256']){throw new RuntimeException('M84 source-sync changed during final audit.');}}
if($lineageCheck()!==$lineageFirst){throw new RuntimeException('M84 independent raw/LF source lineage changed during final audit.');}
$repeatEvidence=null;
if($profileContext['priorName']!==null){
    $prior=$load($profileContext['priorName'],$profileContext['priorSha']);
    if(($prior['plan_digest']??null)!==$reviewedDigest||($prior['independent_before_sha256']??null)!==App\Services\M84ContentPatch::BEFORE_SHA
        ||($prior['backup_sha256']??null)!==$profileContext['backupSha']||($prior['sql_after']??null)!==$rawAfter
        ||($prior['normalized_before']??null)!==$normalized||($prior['protected']??null)!==$protected
        ||($prior['new_practices']??null)!==$practiceFacts||($prior['source_lineage']??null)!==$lineageFirst||($prior['old_consumer_sql']??null)!==$consumerFacts
        ||($prior['approved_footer_url_additions']??null)!==$navigationAdditions||($prior['approved_footer_url_changes']??null)!==$navigationChanges
        ||($prior['all_old_rows_and_timestamps_exact']??null)!==true||($prior['db_writes']??null)!==0){
        throw new RuntimeException('M84 post-UI repeat differs from the exact prior successful read-only receipt.');
    }
    $repeatEvidence=['file'=>$profileContext['priorName'],'sha256'=>$profileContext['priorSha'],
        'all_46_after_fingerprints_exact'=>true,'new_practice_rows_exact'=>true,'source_lineage_exact'=>true];
}
if(!hash_equals($profileContext['backupSha'],$hash($dir.'/'.$profileContext['backupName']))
    ||file_get_contents($dir.'/'.$previewName)!==file_get_contents($dir.'/'.$profileContext['backupName'])){throw new RuntimeException('M84 exclusive backup changed during audit.');}
$receipt =
PHP;
$insertCheck=<<<'PHP'
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        if($i!==5||$target['identity']!==App\Support\M84WordOrderHaveGot::OWNERS[5]
            ||$position!==App\Support\M84WordOrderHaveGot::PRACTICE_SOURCE_INDICES[5]+1
            ||$position!==App\Support\M84WordOrderHaveGot::CONTINUING_COUNTS[5]+1
            ||$source['type']!=='practice-set'||!array_key_exists('level',$source)||$source['level']!==null||$source['column']!=='left'
            ||isset($insertOwnerSeen[$target['identity']])){
            throw new RuntimeException('M84 only the one appended Have got SQL NULL practice binding is approved.');
        }
        $insertOwnerSeen[$target['identity']]=true;$page=$before['pages'][$target['identity']]['page'];
        $fields=['uuid'=>$uuid,'page_id'=>$page['id'],'page_category_id'=>$page['page_category_id'],'locale'=>'uk',
            'type'=>$source['type'],'column'=>$source['column'],'heading'=>$source['heading']??null,'css_class'=>$source['css_class']??null,
            'sort_order'=>$position,'body'=>$source['body'],'level'=>$source['level'],'seeder'=>$target['identity']];
        $same($insert['fields'],$fields,'M84 reviewed practice insert does not exactly match frozen source fields.');
PHP;
runFrozenReadOnlyProfile('m53-postconditions.php','8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d',[
    ['M53PassiveConstructionsPackage','M84WordOrderHaveGot',7],['M53','M84',15],['m53','m84',4],
    [$originalInventory,$inventory,1],[$originalQuestions,$questions,1],
    ['    $newPractices = []; $practiceFacts = [];','    $newPractices = []; $practiceFacts = []; $insertOwnerSeen=[];',1],
    ['        $uuid = App\\Support\\M26DetailPackage::uuid($target[\'identity\'], $source, $position);',$insertCheck,1],
    ["\$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);","if(!isset(\$schemaBefore['schema'][\$table])||\$db->getSchemaBuilder()->getColumns(\$table)!==\$schemaBefore['schema'][\$table]){throw new RuntimeException('M84 full captured SQL schema changed.');}\n        \$columns = \$db->getSchemaBuilder()->getColumnListing(\$table); \$query = \$db->table(\$table);",1],
    ['foreach ($preview[\'sources\'] as $path => $sha) {',$extraSource,1],
    ["    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }",
        "    if (\$protected !== \$preview['protected']) { throw new RuntimeException('Reviewed preview protected fingerprints differ.'); }\n    \$consumerFacts=\$oldConsumerSqlCheck(\$db);",1],
    ['App\\Services\\M84LocalTargetGuard::assertConfiguredDatabase($db); // Pure config check, before lazy PDO.',$physical,1],
    ["'question_plan_sha256' => App\\Services\\M84ContentPatch::QUESTION_PLAN_SHA,","'backup_file'=>\$profileContext['backupName'],'backup_sha256'=>\$profileContext['backupSha'],'backup_equals_reviewed_preview'=>true,\n    'source_lineage'=>\$lineageFirst,'post_ui_repeat'=>\$repeatEvidence,'old_consumer_sql'=>\$consumerFacts,\n    'metadata_transitions'=>\$metadata,'update_operations'=>count(\$expectedChanges),'body_updates'=>\$bodyKinds,'question_family_insert_counts'=>array_map('count',\$newRows),'question_family_insert_rows'=>369,'full_schema_tables_checked'=>46,\n    'approved_footer_url_additions'=>\$navigationAdditions,'approved_footer_url_changes'=>\$navigationChanges,'all_footer_labels_order_current_preserved'=>true,",1],
    ["} finally { \$db->rollBack(); }\n\$receipt =",$recheck,1],
],['backupName'=>$backupName,'backupSha'=>$backupSha,'priorName'=>$priorName,'priorSha'=>$priorSha]);
