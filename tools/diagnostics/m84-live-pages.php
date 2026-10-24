<?php

/** Finite M84 profile of the immutable M53 anonymous HTTP/SSR reader; never browser acceptance. */
require __DIR__.'/frozen-readonly-profile.php';
set_error_handler(static function(int $severity,string $message):never{throw new RuntimeException('M84 diagnostic warning: '.$message);});
$setup = <<<'PHP'
$editorialPath=$root.'/docs/content/m84-word-order-have-got.editorial.v1.json';
$checksPath=$root.'/'.Package::CHECKSUMS;
if(!hash_equals(Package::CHECKSUMS_SHA,$fileHash($checksPath))){throw new RuntimeException('M84 frozen checksums differ.');}
$checks=json_decode(file_get_contents($checksPath),true,flags:JSON_THROW_ON_ERROR);
$pins=array_column($checks['artifacts'],'sha256','path');
if(!hash_equals($pins['docs/content/m84-word-order-have-got.editorial.v1.json']??'', $fileHash($editorialPath))){
    throw new RuntimeException('M84 frozen editorial/prospective metadata differs.');
}
$editorial=json_decode(file_get_contents($editorialPath),true,flags:JSON_THROW_ON_ERROR);
$prospective=$editorial['expected_http_metadata']??[];
if(($prospective['projection_sha256']??null)!=='442a5f202e978c6afc84e86867945c733780a1d8c1176c100f85535c5d397faf'
    ||($prospective['independent_before_sha256']??null)!==M84ContentPatch::BEFORE_SHA
    ||array_column($prospective['metadata']??[],'identity')!==Package::OWNERS){throw new RuntimeException('M84 independent prospective metadata lineage differs.');}
$registryPath=$private.'/registry-before-sentence-builders-v1.json';
$registrySha='09d687e799b18d1e72c908b0cc280151e5aa0018ed51ef10277afa0b8796b7ff';
if(!hash_equals($registrySha,$fileHash($registryPath))){throw new RuntimeException('M84 fresh prior-registry control evidence differs.');}
$priorRegistry=json_decode(file_get_contents($registryPath),true,flags:JSON_THROW_ON_ERROR);
$controlOwners=['Database\Seeders\Page_V3\Tenses\PastPerfectContinuous\PastPerfectContinuousFormsTheorySeeder',
    ...App\Support\M83B2RevisionBasicGrammar::OWNERS];
$controls=[];
foreach($controlOwners as $owner){
    $found=array_values(array_filter($priorRegistry['rows'],static fn($r)=>$r['identity']===$owner));
    if(count($found)!==1||($found[0]['errors']??null)!==[]||($found[0]['http_status']??null)!==200){throw new RuntimeException('M84 read-only PPC/M83 control is missing.');}
    $controls[]=$found[0];
}
$allowedUrls=[...array_column($contract['targets'],'local_url'),...array_column($controls,'url')];
if(count($allowedUrls)!==13||count(array_unique($allowedUrls))!==13){throw new RuntimeException('M84 exact six targets/seven controls differ.');}
foreach($patch['targets'] as $target){
    $path=$root.'/'.$target['path'];
    if(json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR)!==$target['after']){throw new RuntimeException('M84 active canonical definition differs.');}
}
$inputs[$editorialPath]=$pins['docs/content/m84-word-order-have-got.editorial.v1.json'];
$inputs[$checksPath]=Package::CHECKSUMS_SHA;$inputs[$registryPath]=$registrySha;
foreach($prospective['native_policy_pins'] as $path=>$sha){
    if(!hash_equals($sha,$fileHash($root.'/'.$path))){throw new RuntimeException('M84 prospective native metadata policy drift.');}
    $inputs[$root.'/'.$path]=$sha;
}
$nativeConfig=static function(DOMElement $node):array{
    $value=$node->getAttribute('x-data');$prefix="theoryPracticeSet(JSON.parse('";$suffix="'))";
    if(!str_starts_with($value,$prefix)||!str_ends_with($value,$suffix)){throw new RuntimeException('M84 native practice literal config shape differs.');}
    $literal=substr($value,strlen($prefix),-strlen($suffix));
    if(str_contains($literal,'"')||str_contains($literal,"'")){throw new RuntimeException('M84 config is not the reviewed escaped JSON literal.');}
    $json=json_decode('"'.$literal.'"',true,flags:JSON_THROW_ON_ERROR);
    $config=is_string($json)?json_decode($json,true,flags:JSON_THROW_ON_ERROR):null;
    if(!is_array($config)){throw new RuntimeException('M84 native config is not an object.');}return $config;
};
$ownGraded=0;$demoCount=0;$fullInputCount=0;$linkedIds=[];

PHP;
$metadataOld=<<<'PHP'
    $metadata = array_values(array_filter($master['editorial_review']['pre_apply_metadata'] ?? [], static fn ($record) => ($record['identity'] ?? null) === $owner));
    $same(count($metadata), 1, 'Independent editorial metadata record count'); $metadata = $metadata[0];
    $same($metadata['h1'], $oldHttp['h1'], 'Editorial preserved H1');
    $same($metadata['expected_seo']['title'], $oldHttp['title'], 'Editorial preserved title');
PHP;
$metadataNew=<<<'PHP'
    $metadata=$prospective['metadata'][$i];
    $same($metadata['identity'],$owner,'Independent prospective metadata owner');
    $same($metadata['raw_title'],$lesson['raw_title'],'Independent raw Page.title');
    $same($metadata['preserved_h1'],$oldHttp['h1'],'Independent preserved bilingual H1');
    $same($metadata['prospective_page_metadata']['title'],$oldHttp['title'],'Independent preserved metadata title');
    $same($metadata['independent_before_protected'],array_intersect_key($oldHttp,array_flip(['h1_count','h1','title','breadcrumb','canonical','robots_header','robots_meta'])),'Protected independent BEFORE fields');
    $same($sourceBefore['targets'][$i]['before']['page']['subtitle_text'],$before['pages'][$owner]['page']['text'],'Independent original subtitle_text/source-to-SQL correspondence');
    $same($target['after']['page']['subtitle_text'],$lesson['subtitle_text'],'Exact frozen author subtitle descriptor; SQL checked separately');
    $same($target['after']['page']['title'],$oldHttp['raw_page_title'],'Source raw title preservation; SQL checked separately');
    $same(hash('sha256',$lesson['blocks'][0]['data']['intro']),$metadata['hero_intro_sha256'],'Independent descriptor source');
PHP;
$oldTasks=<<<'PHP'
                $same(count($data[$group]), 2, 'Frozen practice '.$group.' task count');
                $groupText = $learningText($xp, $groups->item($n));
                foreach ($data[$group] as $t => $task) {
                    $contains($groupText, $task[$group === 'inputs' ? 'after' : 'label'], $label.'/'.$group.'/'.$t.'/condition');
                    if ($group === 'choices') { $contains($groupText, $task['prompt'], $label.'/'.$group.'/'.$t.'/stimulus'); }
                }
                if ($group !== 'inputs') { $same($xp->query('.//label', $groups->item($n))->length, 2, 'Practice '.$group.' SSR task count'); }
PHP;
$newTasks=<<<'PHP'
                $tasks=$data[$group]??[];$expectedCounts=['selects'=>$i<5?3:2,'choices'=>2,'inputs'=>$i<5?3:2,'rephrase'=>$i<5?3:0];
                $same(count($tasks),$expectedCounts[$group],'Frozen practice '.$group.' task count');
                $same($config[$group]??null,$tasks,'Exact serialized '.$group.' controls, aliases and feedback');
                if($tasks===[]){continue;}
                $groupNode=$groups->item($n);$groupText=$learningText($xp,$groupNode);
                foreach($tasks as $t=>$task){
                    if($group==='rephrase'&&$t===0&&$i<5){
                        $same(isset($task['answer']),false,'Demo has no grading key');
                        foreach(['example_label','example_original','example_target'] as $f){$contains($groupText,$task[$f]??null,$label.'/demo/'.$f);}
                        ++$demoCount;continue;
                    }
                    if($group==='inputs'){
                        $contains($groupText,$task['after'],$label.'/inputs/'.$t.'/condition');
                        $same(isset($task['hint'])||isset($task['placeholder']),false,'Full input has no ready-answer hint/placeholder');
                        $same(str_contains($task['before'],'/'),true,'Every own full input has a native token bank');
                        $same($xp->query('.//*[@data-word-suggestion-input="inputs-'.$t.'"]',$groupNode)->length,1,'Owned manual/token input');
                        ++$fullInputCount;
                    }elseif($group==='rephrase'){
                        $contains($groupText,$task['original'],$label.'/rephrase/'.$t.'/visible criterion');
                        $controlsFound=$xp->query('.//*[@data-word-suggestion-input="rephrase-'.$t.'"]',$groupNode);
                        $same($controlsFound->length,1,'Owned graded rephrase input');
                        $same($controlsFound->item(0)->getAttribute('placeholder'),$task['placeholder']??'','Reviewed rephrase placeholder');
                    }else{
                        $contains($groupText,$task['label'],$label.'/'.$group.'/'.$t.'/visible criterion');
                        if($group==='choices'){$contains($groupText,$task['prompt']??null,$label.'/choices/'.$t.'/stimulus');}
                        foreach($task['options']??$data[$group==='selects'?'options':'choice_options']??[] as $j=>$option){$contains($groupText,$option,$label.'/'.$group.'/'.$t.'/options/'.$j);}
                    }
                    $same(in_array($task['answer'],$task['accepted'],true),true,'Canonical appears in explicit accepted');
                    foreach($task['feedback'] as $feedback){
                        if(preg_match('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$feedback)===1){throw new RuntimeException('M84 serialized x-text feedback is not plain text.');}
                    }
                    ++$ownGraded;
                }
                if(in_array($group,['selects','choices'],true)){$same($xp->query('.//label',$groupNode)->length,count($tasks),'Practice '.$group.' SSR task count');}
PHP;
$oldQuestionReview=<<<'PHP'
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
            $blockResults['practice'] = ['own_groups' => 3, 'tasks_per_group' => [2, 2, 2], 'linked_widgets' => 1,
                'linked_payload_count' => 5, 'linked_ids' => array_column($payload, 'id'), 'selected_question_sequence_and_ordered_tokens' => true];
PHP;
$questionReview=<<<'PHP'
            $same(array_column($payload,'id'),$bank['expected_selected_ids'],'Actual exact-bound selected IDs/order');
            $same($contract['targets'][$i]['level'],null,'Actual technical filter remains NULL');
            $poolCount=array_sum(array_map('count',$bank['expected_pool_ids_by_bank']));
            $same($poolCount,$i<5?77:72,'Frozen original plus supplemental eligible pool');
            foreach($bank['first_five'] as $q=>$expected){
                $same((string)$payload[$q]['type'],'4','Actual widget type');
                $same($payload[$q]['level'],$expected['level']??$bank['selected_levels'][$q],'Actual selected CEFR, not technical filter');
                $same($plain($payload[$q]['question']),$plain($expected['visible_condition_uk']),'Actual selected visible condition');
                $same($payload[$q]['correct_tokens'],$expected['correct_tokens'],'Actual native ordered correct tokens/case');
                $same($payload[$q]['hints'],[],'No unexpected hint relations');$same($payload[$q]['verb_hints'],[],'No unexpected verb-hint relations');
                $actualOptions=array_count_values($payload[$q]['options']);$expectedOptions=array_count_values($expected['token_bank_with_multiplicity']);
                ksort($actualOptions);ksort($expectedOptions);$same($actualOptions,$expectedOptions,'Actual option multiplicity/case');
                foreach(array_count_values($payload[$q]['correct_tokens']) as $token=>$count){
                    if(($actualOptions[$token]??0)<$count){throw new RuntimeException('M84 actual widget loses repeated correct token.');}
                }
            }
            array_push($linkedIds,...array_column($payload,'id'));
            $blockResults['practice']=['source_index'=>$slot,'uuid'=>$uuid,'SSR_block_id'=>$node->getAttribute('id'),'technical_level'=>null,
                'own_groups'=>$i<5?4:3,'graded_own_tasks'=>$i<5?10:6,'ungraded_demos'=>$i<5?1:0,
                'full_sentence_inputs'=>$i<5?3:2,'graded_rephrases'=>$i<5?2:0,'linked_widgets'=>1,'linked_payload_count'=>5,
                'linked_ids'=>array_column($payload,'id'),'eligible_original_plus_supplemental'=>$poolCount,
                'selected_question_sequence_ordered_tokens_native_case_and_multiplicity'=>true];
PHP;
$controls=<<<'PHP'
$same([$ownGraded,$demoCount,$fullInputCount,count($linkedIds),count(array_unique($linkedIds))],[56,5,17,30,30],'Exact six-owner own/demo/full-input/widget populations');
$controlResults=[];
foreach($controls as $n=>$control){
    [$controlHtml,$controlStatus,$controlRobots]=$http($control['url']);
    $doc=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
    try{$loaded=$doc->loadHTML('<?xml encoding="UTF-8">'.$controlHtml,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($previous);}
    if(!$loaded){throw new RuntimeException('M84 control HTML is not parseable.');}
    $cx=new DOMXPath($doc);$same($cx->query('//h1')->length,1,'Control H1 count');
    $controlH1=$plain($cx->query('//h1')->item(0)->textContent);
    if($n===0){$same($controlH1,'Past Perfect Continuous: Forms and Use','PPC reference source H1');}
    else{$same($controlH1,App\Support\M83B2RevisionBasicGrammar::RENDERED_TITLES[$n-1],'M83 preserved actual H1');}
    $same($cx->query('//*[@data-sentence-builder]')->length,1,'Control widget count');
    $oldPayload=Acceptance::payload($controlHtml,$control['practice_uuid']);
    $same(is_array($oldPayload)?count($oldPayload):null,5,'Control payload five');
    $same(array_column($oldPayload,'id'),$control['selected_ids'],'Control original selected IDs/order');
    $same(array_map(static fn($q)=>(string)$q['type'],$oldPayload),array_fill(0,5,'4'),'Control original compose type');
    $controlResults[]=['identity'=>$control['identity'],'url'=>$control['url'],'status'=>$controlStatus,'h1'=>$controlH1,
        'html_sha256'=>hash('sha256',$controlHtml),'prior_registry_sha256'=>$registrySha,'practice_uuid'=>$control['practice_uuid'],
        'selected_ids'=>$control['selected_ids'],'native_widget_count'=>1,'whole_html_equality_claimed'=>false,
        'scope'=>'Fresh GET/H1/native selected IDs. Full old source/SQL/static-learning preservation is a separate gate; no old UI interactions rerun here.'];
}
foreach ($inputs as $path => $sha) {
PHP;
$replacements=[
    ['M53PassiveConstructionsPackage','M84WordOrderHaveGot',1],['M53BuilderHintPresentation','M84BuilderSelectionPresentation',5],
    ['M53','M84',7],['m53','m84',3],
    ["foreach (Package::profile()['artifacts'] as \$path => \$sha) { \$inputs[\$root.'/'.\$path] = \$sha; }",
        "foreach (Package::profile()['artifacts'] as \$path => \$sha) { \$inputs[\$root.'/'.\$path] = \$sha; }\n".$setup,1],
    ["\$http = static function (string \$url): array {","\$http = static function (string \$url) use (\$allowedUrls): array {",1],
    ["preg_match('~^http://gramlyze\\.loc/theory/passive-voice/theory-passive-voice-(?:causative|get-passive|impersonal-passive)$~D', \$url) !== 1",
        "!in_array(\$url,\$allowedUrls,true)",1],
    ['HTTP target is outside the exact three local lessons.','HTTP target is outside the exact six local lessons/seven read-only controls.',1],
    ['finally { curl_close($curl); }','finally { unset($curl); }',1],
    [$metadataOld,$metadataNew,1],
    ["\$metadata['expected_seo']['description']","\$metadata['prospective_page_metadata']['description']",1],
    ["['@type', 'url', 'name', 'headline', 'inLanguage']","['@type', 'url', 'name', 'headline', 'inLanguage', 'description']",2],
    ["\$same(\$resources, \$oldResources, 'Stable LearningResource identity');",
        "\$stable=static fn(\$rows)=>array_map(static fn(\$r)=>array_diff_key(\$r,['description'=>true]),\$rows);\n    \$same(\$stable(\$resources),\$stable(\$oldResources),'Stable LearningResource identity');\n    \$same(\$resources[0]['description']??null,\$metadata['prospective_learning_resource_description'],'Independent prospective LearningResource description');",1],
    ["preg_match('~^/theory/passive-voice/[a-z0-9-]+$~D', \$item['url']) !== 1",
        "!in_array('http://gramlyze.loc'.\$item['url'],array_column(\$contract['targets'],'local_url'),true)",1],
    ["foreach (\$row['cells'] as \$c => \$cell) { \$contains(\$text, \$cell, \$label.'/rows/'.\$n.'/'.\$c); }",
        "foreach (['en','ua','note'] as \$field) { \$contains(\$text, \$row[\$field]??null, \$label.'/rows/'.\$n.'/'.\$field); }",1],
    ["foreach (\$data['headers'] as \$header)","foreach (\$data['headers']??[] as \$header)",1],
    ["\$same(\$groups->length, 3, 'Practice own-group count');",
        "\$same(\$groups->length,\$i<5?4:3,'Practice own-group count');\n            \$factories=\$xp->query('.//*[@x-data and starts-with(@x-data,\"theoryPracticeSet(\")]',\$node);\n            \$same(\$factories->length,1,'Native practice factory count');\$config=\$nativeConfig(\$factories->item(0));",1],
    ["['selects', 'choices', 'inputs'] as \$n => \$group","['selects', 'choices', 'inputs', 'rephrase'] as \$n => \$group",1],
    [$oldTasks,$newTasks,1],
    ["->length, 2, 'Practice manual/token input count'","->length, \$i<5?3:2, 'Practice manual/token input count'",1],
    ["['select_title', 'select_intro', 'choice_title', 'choice_intro', 'input_title', 'input_intro']",
        "['select_title', 'select_intro', 'choice_title', 'choice_intro', 'input_title', 'input_intro', 'rephrase_title', 'rephrase_intro']",1],
    ["\$contains(\$text, \$data[\$field], \$label.'/'.\$field);","\$contains(\$text, \$data[\$field]??null, \$label.'/'.\$field);",1],
    [$oldQuestionReview,$questionReview,1],
    ["foreach (\$inputs as \$path => \$sha) {",$controls,1],
    ["'pages' => \$results, 'SSR_learning_fields_checked' => \$fieldCount,",
        "'pages'=>\$results,'read_only_controls'=>\$controlResults,'SSR_learning_fields_checked'=>\$fieldCount,'own_graded_tasks'=>\$ownGraded,'ungraded_demos'=>\$demoCount,'full_sentence_inputs'=>\$fullInputCount,'linked_questions'=>count(\$linkedIds),",1],
    ['navigation destination availability beyond the exact three requested URLs','navigation destination availability beyond the six requested URLs; all twenty destinations resolve within this six-page set',1],
];
if(($argv[1]??null)==='--profile-check-only'){
    $bytes=file_get_contents(__DIR__.'/m53-live-pages.php');
    if(!hash_equals('66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',hash('sha256',$bytes))){throw new RuntimeException('M84 immutable M53 profile input drift.');}
    foreach($replacements as $i=>[$old,$new,$count]){
        $actual=substr_count($bytes,$old);
        if($actual!==$count){throw new RuntimeException('M84 literal profile count differs at replacement '.$i.': '.$actual.' expected '.$count);}
        $bytes=str_replace($old,$new,$bytes);
    }
    token_get_all($bytes,TOKEN_PARSE);
    echo json_encode(['profile'=>'M84 live pages','literal_replacements'=>count($replacements),'parsed'=>true,'http_requests'=>0,'SQL_access'=>false],JSON_THROW_ON_ERROR)."\n";
    return;
}
runFrozenReadOnlyProfile('m53-live-pages.php','66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',$replacements);
