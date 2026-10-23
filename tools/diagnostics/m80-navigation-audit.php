<?php

/** Anonymous six-page/native-field/navigation GET profile. Not browser interaction or SQL acceptance. */
require __DIR__.'/frozen-readonly-profile.php';
set_error_handler(static function(int $severity,string $message):bool{
    if(in_array($severity,[E_WARNING,E_NOTICE],true)){throw new RuntimeException('M80 diagnostic PHP warning/notice; incomplete acceptance: '.$message);}
    return false;
},E_WARNING|E_NOTICE);
// Freeze the independently projected native metadata receipt before any HTTP acceptance.
$prospectiveFile='prospective-metadata-v1.json';
$prospectiveSha='f8719ea8cce14cc72ae2e099256da953709d3bd6c49700de54e3611a37417d29';
$metadataBootstrap=<<<'PHP'
$metadataPath=$private.'/'.$profileContext['prospective_file'];
if(preg_match('/^prospective-metadata-v[1-9][0-9]*\.json$/D',$profileContext['prospective_file'])!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$profileContext['prospective_sha256'])!==1
    ||!hash_equals($profileContext['prospective_sha256'],$fileHash($metadataPath))){throw new RuntimeException('M80 reviewed prospective metadata is not frozen.');}
$prospective=json_decode(file_get_contents($metadataPath),true,flags:JSON_THROW_ON_ERROR);
if(($prospective['independent_before_sha256']??null)!==M80ContentPatch::BEFORE_SHA
    ||array_column($prospective['metadata']??[],'identity')!==Package::OWNERS
    ||($prospective['application_bootstrapped']??null)!==false||($prospective['db_writes']??null)!==0
    ||($prospective['http_requests']??null)!==0){throw new RuntimeException('M80 prospective metadata scope differs.');}
$master['editorial_review']['pre_apply_metadata']=array_map(static fn($row)=>['identity'=>$row['identity'],
    'h1'=>$row['preserved_h1'],'expected_seo'=>$row['prospective_page_metadata'],
    'expected_learning_resource_description'=>$row['prospective_learning_resource_description']],$prospective['metadata']);
M80BuilderSelectionPresentation::validateAudit($audit,$patch,$contract);
$inputs = [$metadataPath=>$profileContext['prospective_sha256'],$beforePath => M80ContentPatch::BEFORE_SHA, $auditPath => M80BuilderSelectionPresentation::AUDIT_SHA];
PHP;
$allowlist=<<<'PHP'
$allowedUrls=array_fill_keys(array_column($patch['targets'],'local_url'),true);
if(count($allowedUrls)!==6){throw new RuntimeException('M80 exact six URL targets differ.');}
foreach($master['lessons'] as $lesson){foreach($lesson['blocks'] as $block){if($block['type']!=='navigation-chips'){continue;}
    foreach($block['data']['items'] as $item){if(preg_match('~^/theory/(?:sentence-transformations|pytalni-rechennia-ta-zaperechennia)/[a-z0-9-]+$~D',$item['url']??'')!==1){throw new RuntimeException('M80 frozen navigation contains an out-of-scope destination.');}
        $allowedUrls['http://gramlyze.loc'.$item['url']]=true;}
}}
$http = static function (string $url) use($allowedUrls): array {
    if (!isset($allowedUrls[$url])) {
        throw new RuntimeException('HTTP target is outside the exact frozen M80 lesson/navigation allowlist.');
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
                foreach(array_count_values($expected['correct_tokens']) as $token=>$count){if(($available[$token]??0)<$count){throw new RuntimeException('M80 widget loses a repeated correct token instance.');}}
            }
PHP;
$fieldVisibility=<<<'PHP'
        foreach (['title', 'intro', 'warning'] as $field) {
            if($type==='practice-set'&&$field==='intro'){
                $same($slot,Package::PRACTICE_SOURCE_INDICES[$i],'Finite unsupported intro practice source slot');
                if(!is_string($data['intro']??null)||trim($data['intro'])===''){throw new RuntimeException('M80 reviewed generic practice intro missing.');}
                foreach(['select_intro','choice_intro','input_intro'] as $supported){
                    if(!is_string($data[$supported]??null)||trim($data[$supported])===''){throw new RuntimeException('M80 visible group intro missing.');}
                }
                $unsupportedPracticeIntros[$owner]=['identity'=>$owner,'source_index'=>$slot,'field'=>'practice-set.data.intro',
                    'status'=>'unsupported/not_rendered','source_sha256'=>hash('sha256',$data['intro']),
                    'reason'=>'Unchanged native practice-set does not render generic intro. Reviewed redundant guidance, not a task criterion; no SSR PASS claimed for this field.',
                    'required_rendered_checks'=>['select_intro','choice_intro','input_intro','all six labels/inputs.after','choice stimuli']];
                continue;
            }
            $contains($text, $data[$field] ?? null, $label.'/'.$field);
        }
PHP;
$comparisonRows=<<<'PHP'
            foreach ($data['rows'] as $n => $row) {
                if(isset($row['cells'])){
                    $same(array_keys($row),['cells'],'M80 reviewed cells-row shape');$cells=$row['cells'];
                }else{
                    $same(array_keys($row),['en','ua','note'],'M80 reviewed native en/ua/note row shape');
                    $cells=[$row['en'],$row['ua'],$row['note']];
                }
                $same(is_array($cells)&&array_is_list($cells),true,'M80 native row cell vector');
                $same(count($cells),count($data['headers']),'M80 full native table column count');
                foreach($cells as $c=>$cell){if(!is_string($cell)||trim($cell)===''){throw new RuntimeException('M80 native comparison learning cell empty.');}
                    $contains($text,$cell,$label.'/rows/'.$n.'/'.$c);}
            }
PHP;
$navigation=<<<'PHP'
$destinations=[];
foreach($results as $page){foreach($page['navigation_exact_local_destinations'] as $url){$destinations[$url]=true;}}
ksort($destinations);$navigationResults=[];
foreach(array_keys($destinations) as $url){
    [$html,$status,$robots]=$http($url);$dom=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
    try{$loaded=$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($previous);}
    if(!$loaded){throw new RuntimeException('M80 navigation destination HTML cannot be parsed.');}$xp=new DOMXPath($dom);
    if($xp->query('//h1')->length!==1||trim($xp->query('//h1')->item(0)->textContent)===''||$xp->query('//*[@data-theory-main]')->length!==1){throw new RuntimeException('M80 navigation destination lacks one canonical theory/H1.');}
    $navigationResults[]=['url'=>$url,'status'=>$status,'h1'=>$plain($xp->query('//h1')->item(0)->textContent),'html_sha256'=>hash('sha256',$html),'anonymous_loopback'=>true,'redirects'=>0];
}
$same(array_keys($unsupportedPracticeIntros),Package::OWNERS,'Exact six unsupported generic practice intros recorded, not passed as SSR fields');
foreach ($inputs as $path => $sha) { if (!hash_equals($sha, $fileHash($path))) { throw new RuntimeException('Reviewed input changed during HTTP acceptance.'); } }
PHP;
runFrozenReadOnlyProfile('m53-live-pages.php','66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',[
    ['M53PassiveConstructionsPackage','M80NegativesTransformations',1],['M53BuilderHintPresentation','M80BuilderSelectionPresentation',5],['M53','M80',7],['m53','m80',3],
    ["\$inputs = [\$beforePath => M80ContentPatch::BEFORE_SHA, \$auditPath => M80BuilderSelectionPresentation::AUDIT_SHA];",$metadataBootstrap,1],
    [$oldHttp,$allowlist,1],['CURLOPT_TIMEOUT => 45','CURLOPT_TIMEOUT => 120',1],
    ["~^/theory/passive-voice/[a-z0-9-]+$~D","~^/theory/(?:sentence-transformations|pytalni-rechennia-ta-zaperechennia)/[a-z0-9-]+$~D",1],
    [$oldSelection,$selection,1],
    ["\$started = gmdate('c'); \$results = [];","\$started = gmdate('c'); \$results = []; \$unsupportedPracticeIntros=[];",1],
    ["        foreach (['title', 'intro', 'warning'] as \$field) { \$contains(\$text, \$data[\$field] ?? null, \$label.'/'.\$field); }",$fieldVisibility,1],
    ["            foreach (\$data['rows'] as \$n => \$row) { foreach (\$row['cells'] as \$c => \$cell) { \$contains(\$text, \$cell, \$label.'/rows/'.\$n.'/'.\$c); } }",$comparisonRows,1],
    ["\$contains(\$groupText, \$task['prompt'], \$label.'/'.\$group.'/'.\$t.'/stimulus');","\$contains(\$groupText, \$task['prompt'] ?? null, \$label.'/'.\$group.'/'.\$t.'/stimulus');",1],
    ['$resources = [];','$resources = []; $resourceDescriptions=[];',1],
    ['use (&$visit, &$resources): void','use (&$visit, &$resources, &$resourceDescriptions): void',1],
    ["\$resources[] = array_intersect_key(\$value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage']));","\$resources[] = array_intersect_key(\$value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage']));\n            \$resourceDescriptions[]=\$value['description']??null;",1],
    ["\$same(\$resources, \$oldResources, 'Stable LearningResource identity');","\$same(\$resources, \$oldResources, 'Stable LearningResource identity');\n    \$same(\$resourceDescriptions,[\$metadata['expected_learning_resource_description']],'Prospective LearningResource description');",1],
    ["foreach (\$inputs as \$path => \$sha) { if (!hash_equals(\$sha, \$fileHash(\$path))) { throw new RuntimeException('Reviewed input changed during HTTP acceptance.'); } }",$navigation,1],
    ["'pages' => \$results, 'SSR_learning_fields_checked' => \$fieldCount,","'pages' => \$results, 'SSR_learning_fields_checked' => \$fieldCount,\n    'navigation_destinations'=>\$navigationResults,'navigation_destinations_http_200'=>count(\$navigationResults),\n    'unsupported_not_rendered_source_fields'=>array_values(\$unsupportedPracticeIntros),",1],
    ["'navigation destination availability beyond the exact three requested URLs', ",'',1],
],['prospective_file'=>$prospectiveFile,'prospective_sha256'=>$prospectiveSha]);
