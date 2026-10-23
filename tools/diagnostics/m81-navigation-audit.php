<?php

/** Explicit CLI-only M81 HTTP/SSR profile; exclusive private receipt, no application/SQL/content writes. */
require __DIR__.'/frozen-readonly-profile.php';
set_error_handler(static function(int $severity,string $message):bool{
    if(in_array($severity,[E_WARNING,E_NOTICE],true)){throw new RuntimeException('M81 diagnostic PHP warning/notice; incomplete acceptance: '.$message);}
    return false;
},E_WARNING|E_NOTICE);
// Freeze the independently projected native metadata receipt before any HTTP acceptance.
$prospectiveFile='prospective-metadata-v1.json';
$prospectiveSha='8436403fec37610fb16f76487547f1fc61f6fee69fc8ddea80d0dfccc6c82def';
$metadataBootstrap=<<<'PHP'
$metadataPath=$private.'/'.$profileContext['prospective_file'];
if(preg_match('/^prospective-metadata-v[1-9][0-9]*\\.json$/D',$profileContext['prospective_file'])!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$profileContext['prospective_sha256'])!==1
    ||!hash_equals($profileContext['prospective_sha256'],$fileHash($metadataPath))){throw new RuntimeException('M81 reviewed prospective metadata is not frozen.');}
$prospective=json_decode(file_get_contents($metadataPath),true,flags:JSON_THROW_ON_ERROR);
if(M81ContentPatch::BEFORE_SHA!=='facabc5369230dac0958f2e2277c7cd0e4b0523fb794c041afd46116e095c023'
    ||Package::BINDINGS_SHA!=='d118fb390a713cb5256e57c04377afdf6c9aa7dad0259c80db1058582353bc23'
    ||($prospective['independent_before_sha256']??null)!==M81ContentPatch::BEFORE_SHA
    ||array_column($prospective['metadata']??[],'identity')!==Package::OWNERS
    ||($prospective['application_bootstrapped']??null)!==false||($prospective['working_env_loaded']??null)!==false
    ||($prospective['db_connections']??null)!==0||($prospective['db_queries']??null)!==0
    ||($prospective['db_writes']??null)!==0||($prospective['http_requests']??null)!==0){
    throw new RuntimeException('M81 prospective metadata or exact reviewed native bindings differ.');
}
$profileBindings=Package::profile()['bindings'];$reviewedMetadataTransitions=[];$navigationExpectedCounts=[8,8,8,8,6,6];$navigationItemCount=0;
foreach(Package::OWNERS as $n=>$identity){
    $lesson=$master['lessons'][$n];$binding=$profileBindings[$n];$projected=$patch['targets'][$n]['after']['page']['blocks'];$pre=$prospective['metadata'][$n];
    if(($binding['page_id']??null)!==Package::PAGE_IDS[$n]||($pre['preserved_h1']??null)!==Package::RENDERED_TITLES[$n]
        ||($pre['local_url']??null)!==$lesson['local_url']||($pre['raw_title']??null)!==$lesson['raw_title']
        ||($pre['hero_intro_sha256']??null)!==hash('sha256',$lesson['blocks'][0]['data']['intro'])
        ||!is_array($pre['prospective_page_metadata']??null)||!is_string($pre['prospective_learning_resource_description']??null)
        ||$pre['prospective_learning_resource_description']===''){
        throw new RuntimeException('M81 independent pre-apply metadata no longer matches frozen hero/H1.');
    }
    if(preg_match('~<strong>(.*?)</strong>~su',$lesson['subtitle_html'],$title)!==1
        ||html_entity_decode(strip_tags($title[1]),ENT_QUOTES|ENT_HTML5,'UTF-8')!==Package::RENDERED_TITLES[$n]
        ||($before['pages'][$identity]['subtitle_extracted_title']??null)!==Package::RENDERED_TITLES[$n]){
        throw new RuntimeException('M81 strong subtitle title differs from independently captured H1.');
    }
    $slots=[];
    foreach($before['pages'][$identity]['blocks'] as $row){
        if($row['locale']==='uk'&&(int)$row['sort_order']>=1&&(int)$row['sort_order']<=6){$slots[(int)$row['sort_order']-1]=$row;}
    }
    ksort($slots);
    if(array_keys($slots)!==range(0,5)||array_column($slots,'level')!==$binding['continuing_levels']
        ||array_keys($binding['continuing_metadata']??[])!==[1,2]||count($lesson['blocks'])!==7
        ||$lesson['blocks'][6]['level']!==Package::LEVELS[$n]){throw new RuntimeException('M81 actual continuing SQL levels/new practice level differ.');}
    foreach($binding['continuing_metadata'] as $slot=>$transition){
        $source=$sourceBefore['targets'][$n]['before']['page']['blocks'][$slot];$old=$slots[$slot];$after=$projected[$slot];
        $expectedType=$slot===1||$n>=4?'usage-panels':'comparison-table';
        if($transition['column']!=='left'||$transition['before']['type']!=='box'
            ||$transition['after']!==['type'=>$expectedType,'heading'=>null,'level'=>null]
            ||$old['type']!=='box'||$old['heading']!==$source['heading']||$old['level']!==null||$old['column']!=='left'
            ||$transition['before']!==['type'=>$old['type'],'heading'=>$old['heading'],'level'=>$old['level']]
            ||$after['type']!==$expectedType||($after['heading']??null)!==null||($after['level']??null)!==null||$after['column']!=='left'){
            throw new RuntimeException('M81 one of twelve reviewed box-to-native metadata transitions differs.');
        }
        $reviewedMetadataTransitions[]=['identity'=>$identity,'source_index'=>$slot,'before_type'=>'box','after_type'=>$expectedType,
            'before_heading'=>$old['heading'],'after_heading'=>null,'column'=>'left','level'=>null];
    }
    foreach(['selects','choices','inputs'] as $group){foreach($lesson['blocks'][6]['data'][$group] as $task){
        if(array_key_exists('prompt',$task)){throw new RuntimeException('M81 criteria cannot rely on unsupported prompt.');}
    }}
    if(array_key_exists('intro',$lesson['blocks'][6]['data'])){throw new RuntimeException('M81 generic practice intro is not rendered.');}
}
if(count($reviewedMetadataTransitions)!==12){throw new RuntimeException('M81 exact twelve transition inventory differs.');}
$master['editorial_review']['pre_apply_metadata']=array_map(static fn($row)=>['identity'=>$row['identity'],
    'h1'=>$row['preserved_h1'],'expected_seo'=>$row['prospective_page_metadata'],
    'expected_learning_resource_description'=>$row['prospective_learning_resource_description']],$prospective['metadata']);
M81BuilderSelectionPresentation::validateAudit($audit,$patch,$contract);
$inputs=[$metadataPath=>$profileContext['prospective_sha256'],$beforePath=>M81ContentPatch::BEFORE_SHA,
    $auditPath=>M81BuilderSelectionPresentation::AUDIT_SHA,$root.'/'.Package::BINDINGS=>Package::BINDINGS_SHA];
PHP;
$allowlist=<<<'PHP'
$allowedUrls=array_fill_keys(array_column($patch['targets'],'local_url'),true);
if(count($allowedUrls)!==6){throw new RuntimeException('M81 exact six URL targets differ.');}
foreach($master['lessons'] as $lesson){foreach($lesson['blocks'] as $block){if($block['type']!=='navigation-chips'){continue;}
    foreach($block['data']['items'] as $item){if(preg_match('~^/theory/(?:sentence-transformations|common-mistakes)/[a-z0-9-]+$~D',$item['url']??'')!==1){throw new RuntimeException('M81 frozen navigation contains an out-of-scope destination.');}
        $allowedUrls['http://gramlyze.loc'.$item['url']]=true;}
}}
$http = static function (string $url) use($allowedUrls): array {
    if (!isset($allowedUrls[$url])) {
        throw new RuntimeException('HTTP target is outside the exact frozen M81 lesson/navigation allowlist.');
    }
PHP;
$oldHttp=<<<'PHP'
$http = static function (string $url): array {
    if (preg_match('~^http://gramlyze\.loc/theory/passive-voice/theory-passive-voice-(?:causative|get-passive|impersonal-passive)$~D', $url) !== 1) {
        throw new RuntimeException('HTTP target is outside the exact three local lessons.');
    }
PHP;
$oldSelection=<<<'PHP'
            $expectedQuestions = [];
            foreach ([...($bank['first_five'] ?? []), ...($bank['existing_selected_two'] ?? [])] as $question) { $expectedQuestions[$question['uuid']] = $question; }
            foreach ($bank['proposed_supplement']['questions'] ?? [] as $question) {
                $expectedQuestions[$question['uuid']] = ['condition_uk' => $question['question'], 'correct_tokens' => $question['tokens_correct']];
            }
            foreach ($bank['expected_selected_uuids'] as $q => $questionUuid) {
                $expected = $expectedQuestions[$questionUuid] ?? null;
                if ($expected === null) { throw new RuntimeException('Independent selected-question source missing.'); }
                $same((string) $payload[$q]['type'], '4', 'Actual widget type'); $same($payload[$q]['level'], $contract['targets'][$i]['level'], 'Actual widget exact level');
                $same($plain($payload[$q]['question']), $plain($expected['condition_uk']), 'Actual widget selected question');
                $lower = static fn (array $tokens): array => array_map(static fn ($token) => mb_strtolower(trim((string) $token), 'UTF-8'), $tokens);
                $same($lower($payload[$q]['correct_tokens']), $lower($expected['correct_tokens']), 'Actual widget ordered correct tokens');
                $available = array_count_values($lower($payload[$q]['options']));
                foreach (array_count_values($lower($payload[$q]['correct_tokens'])) as $token => $count) {
                    if (($available[$token] ?? 0) < $count) { throw new RuntimeException('Actual widget loses repeated correct token instances.'); }
                }
            }
PHP;
$selection=<<<'PHP'
            $same(array_map('intval',array_column($payload,'id')),$bank['expected_selected_ids'],'Actual widget exact selected IDs/order');
            $same(count($bank['first_five']),5,'Independent selected question count');
            foreach($bank['first_five'] as $q=>$expected){
                $same($expected['uuid'],$bank['expected_selected_uuids'][$q],'Independent selected UUID sequence');
                $same((string)$payload[$q]['type'],'4','Actual widget type');$same($payload[$q]['level'],$contract['targets'][$i]['level'],'Actual widget exact level');
                $same($plain($payload[$q]['question']),$plain($expected['visible_condition_uk']),'Actual widget visible contextual condition');
                $same($payload[$q]['correct_tokens'],$expected['correct_tokens'],'Actual widget unchanged native ordered/cased tokens');
                $available=array_count_values($payload[$q]['options']);
                foreach(array_count_values($expected['correct_tokens']) as $token=>$count){if(($available[$token]??0)<$count){throw new RuntimeException('M81 widget loses a repeated correct token instance.');}}
            }
PHP;
$fieldVisibility=<<<'PHP'
        if($type==='practice-set'){
            $same($slot,Package::PRACTICE_SOURCE_INDICES[$i],'Exact new practice source slot');
            if(array_key_exists('intro',$data)){throw new RuntimeException('M81 generic practice intro cannot earn SSR coverage.');}
            foreach(['select_intro','choice_intro','input_intro'] as $supported){
                if(!is_string($data[$supported]??null)||trim($data[$supported])===''){throw new RuntimeException('M81 visible group intro missing.');}
            }
        }
        foreach(['title','intro','warning'] as $field){$contains($text,$data[$field]??null,$label.'/'.$field);}
PHP;
$comparisonRows=<<<'PHP'
            $tableRows=$xp->query('.//tbody/tr',$node);
            foreach($data['rows'] as $n=>$row){
                if(isset($row['cells'])){$same(array_keys($row),['cells'],'M81 cells-row shape');$cells=$row['cells'];}
                else{$same(array_keys($row),['en','ua','note'],'M81 en/ua/note row shape');$cells=[$row['en'],$row['ua'],$row['note']];}
                $same(is_array($cells)&&array_is_list($cells),true,'M81 native row cell vector');
                $same(count($cells),count($data['headers']),'M81 native table column count');
                $actualCells=$xp->query('./td',$tableRows->item($n));$same($actualCells->length,count($cells),'M81 actual row cell count');
                foreach($cells as $c=>$cell){
                    if(!is_string($cell)||trim($cell)===''){throw new RuntimeException('M81 comparison learning cell empty.');}
                    $contains($learningText($xp,$actualCells->item($c)),$cell,$label.'/rows/'.$n.'/'.$c);
                }
            }
PHP;
$oldNavigation=<<<'PHP'
        if ($type === 'navigation-chips') {
            $contains($mainText, $data['title'], $label.'/title');
            foreach ($data['items'] as $item) {
                $contains($mainText, $item['label'], $label.'/label');
                if ($item['current']) { continue; }
                if (preg_match('~^/theory/passive-voice/[a-z0-9-]+$~D', $item['url']) !== 1) { throw new RuntimeException('Navigation destination is not an exact local theory path.'); }
                $found = false;
                foreach ($xp->query('.//nav//a[@href]', $main) as $link) {
                    if ($link->getAttribute('href') === $item['url'] && $plain($link->textContent) === $plain($item['label'])) { $found = true; }
                }
                if (!$found) { throw new RuntimeException('Missing exact navigation destination: '.$item['url']); }
                $navLinks[] = 'http://gramlyze.loc'.$item['url'];
            }
            continue;
        }
PHP;
$newNavigation=<<<'PHP'
        if($type==='navigation-chips'){
            $oldNavigation=json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][5]['body'],true,flags:JSON_THROW_ON_ERROR);
            $same($data,$oldNavigation,'M81 retained navigation title/labels/order/current/URLs');
            $same(count($data['items']),$navigationExpectedCounts[$i],'M81 per-owner navigation item count');
            $navigations=$xp->query('.//nav[./p and ./div/*[self::a or self::span]]',$main);
            $same($navigations->length,1,'M81 one canonical footer navigation');
            $nav=$navigations->item(0);$navTitle=$xp->query('./p',$nav);$same($navTitle->length,1,'M81 footer navigation title count');
            $same($plain($navTitle->item(0)->textContent),$plain($data['title']),'M81 footer navigation title');
            $navNodes=$xp->query('./div/*[self::a or self::span]',$nav);$same($navNodes->length,count($data['items']),'M81 actual navigation items');
            $currentCount=0;
            foreach($data['items'] as $n=>$item){
                if(preg_match('~^/theory/(?:sentence-transformations|common-mistakes)/[a-z0-9-]+$~D',$item['url']??'')!==1){
                    throw new RuntimeException('M81 navigation destination outside exact reviewed local categories.');
                }
                $entry=$navNodes->item($n);$same($plain($entry->textContent),$plain($item['label']),'M81 ordered footer label');
                if($item['current']){
                    $currentCount++;$same($entry->tagName,'span','M81 current chip is not a link');
                    $same($entry->hasAttribute('href'),false,'M81 current chip has no href');
                    $same('http://gramlyze.loc'.$item['url'],$url,'M81 current navigation owner URL');
                }else{
                    $same($entry->tagName,'a','M81 noncurrent navigation link');
                    $same($entry->getAttribute('href'),$item['url'],'M81 ordered exact navigation href');
                    $navLinks[]='http://gramlyze.loc'.$item['url'];
                }
                $contains($mainText,$item['label'],$label.'/items/'.$n.'/label');$navigationItemCount++;
            }
            $same($currentCount,1,'M81 one current navigation item');
            continue;
        }
PHP;
$navigation=<<<'PHP'
$destinations=[];
foreach($results as $page){foreach($page['navigation_exact_local_destinations'] as $url){$destinations[$url]=true;}}
ksort($destinations);$navigationResults=[];
foreach(array_keys($destinations) as $url){
    [$html,$status,$robots]=$http($url);$dom=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
    try{$loaded=$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($previous);}
    if(!$loaded){throw new RuntimeException('M81 navigation destination HTML cannot be parsed.');}$xp=new DOMXPath($dom);
    if($xp->query('//h1')->length!==1||trim($xp->query('//h1')->item(0)->textContent)===''||$xp->query('//*[@data-theory-main]')->length!==1){throw new RuntimeException('M81 navigation destination lacks one canonical theory/H1.');}
    $navigationResults[]=['url'=>$url,'status'=>$status,'h1'=>$plain($xp->query('//h1')->item(0)->textContent),'html_sha256'=>hash('sha256',$html),'anonymous_loopback'=>true,'redirects'=>0];
}
$same(count($results),6,'M81 exact six accepted targets');
$same($navigationItemCount,44,'M81 all navigation items/order/current verified');
$same(count($reviewedMetadataTransitions),12,'M81 reviewed transitions; not after-SQL acceptance');
foreach ($inputs as $path => $sha) { if (!hash_equals($sha, $fileHash($path))) { throw new RuntimeException('Reviewed input changed during HTTP acceptance.'); } }
PHP;
runFrozenReadOnlyProfile('m53-live-pages.php','66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',[
    ['M53PassiveConstructionsPackage','M81TransformationsCommonMistakes',1],['M53BuilderHintPresentation','M81BuilderSelectionPresentation',5],['M53','M81',7],['m53','m81',3],
    ["\$inputs = [\$beforePath => M81ContentPatch::BEFORE_SHA, \$auditPath => M81BuilderSelectionPresentation::AUDIT_SHA];",$metadataBootstrap,1],
    [$oldHttp,$allowlist,1],['CURLOPT_TIMEOUT => 45','CURLOPT_TIMEOUT => 120',1],
    [$oldNavigation,$newNavigation,1],
    [$oldSelection,$selection,1],
    ["        foreach (['title', 'intro', 'warning'] as \$field) { \$contains(\$text, \$data[\$field] ?? null, \$label.'/'.\$field); }",$fieldVisibility,1],
    ["            foreach (\$data['rows'] as \$n => \$row) { foreach (\$row['cells'] as \$c => \$cell) { \$contains(\$text, \$cell, \$label.'/rows/'.\$n.'/'.\$c); } }",$comparisonRows,1],
    ["                    if (\$group === 'choices') { \$contains(\$groupText, \$task['prompt'], \$label.'/'.\$group.'/'.\$t.'/stimulus'); }","                    if (array_key_exists('prompt',\$task)) { throw new RuntimeException('M81 task prompt is unsupported; use visible label/after.'); }",1],
    ['$resources = [];','$resources = []; $resourceDescriptions=[];',1],
    ['use (&$visit, &$resources): void','use (&$visit, &$resources, &$resourceDescriptions): void',1],
    ["\$resources[] = array_intersect_key(\$value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage']));","\$resources[] = array_intersect_key(\$value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage']));\n            \$resourceDescriptions[]=\$value['description']??null;",1],
    ["\$same(\$resources, \$oldResources, 'Stable LearningResource identity');","\$same(\$resources, \$oldResources, 'Stable LearningResource identity');\n    \$same(\$resourceDescriptions,[\$metadata['expected_learning_resource_description']],'Prospective LearningResource description');",1],
    ["foreach (\$inputs as \$path => \$sha) { if (!hash_equals(\$sha, \$fileHash(\$path))) { throw new RuntimeException('Reviewed input changed during HTTP acceptance.'); } }",$navigation,1],
    ["'pages' => \$results, 'SSR_learning_fields_checked' => \$fieldCount,","'pages' => \$results, 'SSR_learning_fields_checked' => \$fieldCount,\n    'navigation_destinations'=>\$navigationResults,'navigation_destinations_http_200'=>count(\$navigationResults),\n    'navigation_source_items_checked'=>\$navigationItemCount,'reviewed_metadata_transition_count'=>count(\$reviewedMetadataTransitions),\n    'reviewed_metadata_transitions'=>\$reviewedMetadataTransitions,'unsupported_not_rendered_source_fields'=>[],",1],
    ["'navigation destination availability beyond the exact three requested URLs', ",'',1],
    ["'SQL postconditions (separate diagnostic)'","'SQL postconditions (separate diagnostic)', 'dynamic assets/fonts loaded', 'client-visible settled answers (separate browser acceptance)'",1],
],['prospective_file'=>$prospectiveFile,'prospective_sha256'=>$prospectiveSha]);
