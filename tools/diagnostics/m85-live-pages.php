<?php

/** Finite anonymous HTTP/SSR diagnostic; no bootstrap/SQL and not browser acceptance. */
require __DIR__.'/frozen-readonly-profile.php';
require_once __DIR__.'/m85-live-contract.php';
set_error_handler(static function(int $severity,string $message):never{throw new RuntimeException('M85 diagnostic warning: '.$message);});
$setup=<<<'PHP'
$mistakeContract=M85LiveDiagnosticContract::assertMistakeNotes($master['lessons']);
$diagnosticContractPath=$root.'/tools/diagnostics/m85-live-contract.php';$inputs[$diagnosticContractPath]=$fileHash($diagnosticContractPath);
$authorRevision=$master['author_revision']??null;if($authorRevision!=='1.0.1'){throw new RuntimeException('M85 finite live diagnostic requires the repaired author revision.');}
$editorialRelative='docs/content/m85-verb-to-be.editorial.v'.$authorRevision.'.json';$editorialPath=$root.'/'.$editorialRelative;$checksPath=$root.'/'.Package::CHECKSUMS;
if(!hash_equals(Package::CHECKSUMS_SHA,$fileHash($checksPath))){throw new RuntimeException('M85 frozen checksums differ.');}
$checks=json_decode(file_get_contents($checksPath),true,flags:JSON_THROW_ON_ERROR);$editorialPins=array_values(array_filter($checks['artifacts'],static fn($row)=>($row['path']??null)===$editorialRelative));
if(count($editorialPins)!==1||preg_match('/^[a-f0-9]{64}$/D',$editorialPins[0]['sha256']??'')!==1||is_link($editorialPath)||!hash_equals($editorialPins[0]['sha256'],$fileHash($editorialPath))){throw new RuntimeException('M85 active finite editorial checksum binding differs.');}$editorialSha=$editorialPins[0]['sha256'];
$editorial=json_decode(file_get_contents($editorialPath),true,flags:JSON_THROW_ON_ERROR);$prospective=$editorial['expected_http_metadata']??[];
if(($prospective['projection_sha256']??null)!=='166c4e3138b5e09bfd5cab48d0b4464567465ee708d03dcf0aba5f6839018e00'||($prospective['independent_before_sha256']??null)!==M85ContentPatch::BEFORE_SHA||array_column($prospective['metadata']??[],'identity')!==Package::OWNERS){throw new RuntimeException('M85 prospective metadata lineage differs.');}
$registryPath=$private.'/registry-before-sentence-builders-v1.json';$registrySha='afd4eba44f21824cf7bd83108a73a3e7578cd8fcee77d2f63692a4df9b29a262';
if(!hash_equals($registrySha,$fileHash($registryPath))){throw new RuntimeException('M85 original registry evidence differs.');}
$priorRegistry=json_decode(file_get_contents($registryPath),true,flags:JSON_THROW_ON_ERROR);
$controlOwners=['Database\Seeders\Page_V3\Tenses\PastPerfectContinuous\PastPerfectContinuousFormsTheorySeeder',...App\Support\M84WordOrderHaveGot::OWNERS];$controls=[];
foreach($controlOwners as $owner){$found=array_values(array_filter($priorRegistry['rows'],static fn($r)=>$r['identity']===$owner));if(count($found)!==1||($found[0]['errors']??null)!==[]||($found[0]['http_status']??null)!==200){throw new RuntimeException('M85 PPC/M84 registry control missing.');}$controls[]=$found[0];}
$navAllowed=[...array_column($contract['targets'],'local_url'),'http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia'];
$allowedUrls=[...$navAllowed,...array_column($controls,'url')];if(count($allowedUrls)!==14||count(array_unique($allowedUrls))!==14){throw new RuntimeException('M85 target/control/related URL scope differs.');}
foreach($patch['targets'] as $target){if(json_decode(file_get_contents($root.'/'.$target['path']),true,flags:JSON_THROW_ON_ERROR)!==$target['after']){throw new RuntimeException('M85 canonical active definition differs.');}}
$inputs[$editorialPath]=$editorialSha;$inputs[$checksPath]=Package::CHECKSUMS_SHA;$inputs[$registryPath]=$registrySha;
foreach($prospective['native_policy_pins'] as $path=>$sha){if(!hash_equals($sha,$fileHash($root.'/'.$path))){throw new RuntimeException('M85 native metadata policy changed.');}$inputs[$root.'/'.$path]=$sha;}
$nativeConfig=static function(DOMElement $node):array{$value=$node->getAttribute('x-data');$prefix="theoryPracticeSet(JSON.parse('";$suffix="'))";if(!str_starts_with($value,$prefix)||!str_ends_with($value,$suffix)){throw new RuntimeException('M85 native practice config shape differs.');}$literal=substr($value,strlen($prefix),-strlen($suffix));if(str_contains($literal,'"')||str_contains($literal,"'")){throw new RuntimeException('M85 config not escaped literal.');}$json=json_decode('"'.$literal.'"',true,flags:JSON_THROW_ON_ERROR);$data=is_string($json)?json_decode($json,true,flags:JSON_THROW_ON_ERROR):null;if(!is_array($data)){throw new RuntimeException('M85 practice config not object.');}return $data;};
$secondaryProofPath=$private.'/secondary-navigation-review-v1.json';$secondaryProofSha='1e4d481857313376cb2836efc3b6a6df4e7f4120e2531c208406129130b0adda';
if(!hash_equals($secondaryProofSha,$fileHash($secondaryProofPath))){throw new RuntimeException('M85 independent secondary-navigation proof differs.');}
$secondaryProof=json_decode(file_get_contents($secondaryProofPath),true,flags:JSON_THROW_ON_ERROR);
$secondaryExpected=['title'=>'Пов’язана тема','items'=>[['label'=>'Питальні речення та заперечення','url'=>'/theory/pytalni-rechennia-ta-zaperechennia','current'=>false]]];
$secondaryViewPath=$root.'/resources/views/theory/show.blade.php';$secondaryViewSha='3745e0b97a025f6c686434972f603a61ba71e5cfdafe83bada5228c58d68d87c';
if(($secondaryProof['identity']??null)!==Package::OWNERS[2]||($secondaryProof['source_index']??null)!==7||($secondaryProof['independent_before_sha256']??null)!==M85ContentPatch::BEFORE_SHA||($secondaryProof['authored_secondary_data']??null)!==$secondaryExpected||($secondaryProof['secondary_navigation_visible_acceptance']??null)!==false||($secondaryProof['authored_data_preserved_exact_except_native_marker']??null)!==true||($secondaryProof['universal_view']['before_and_after_sha256']??null)!==$secondaryViewSha||($before['visual_hashes']['resources/views/theory/show.blade.php']??null)!==$secondaryViewSha||!hash_equals($secondaryViewSha,$fileHash($secondaryViewPath))){throw new RuntimeException('M85 finite secondary-navigation lineage/source/view proof differs.');}
$secondaryFacts=static function(DOMXPath $xp,DOMNode $main)use($secondaryExpected):array{
 $parts=[];foreach($xp->query('.//text()[not(ancestor::script) and not(ancestor::style)]',$main) as $t){$parts[]=$t->nodeValue;}$text=trim(preg_replace('/\s+/u',' ',implode(' ',$parts)));$footers=$xp->query('.//section[contains(concat(" ",normalize-space(@class)," ")," theory-content-blocks ")]/nav',$main);$titles=[];
 foreach($footers as $footer){$p=$xp->query('./p',$footer);$titles[]=$p->length===1?trim($p->item(0)->textContent):null;}$links=[];
 foreach($xp->query('.//a[@href]',$main) as $a){if(in_array($a->getAttribute('href'),[$secondaryExpected['items'][0]['url'],'http://gramlyze.loc'.$secondaryExpected['items'][0]['url'],'https://gramlyze.com'.$secondaryExpected['items'][0]['url']],true)){$links[]=['href'=>$a->getAttribute('href'),'label'=>trim(preg_replace('/\s+/u',' ',$a->textContent))];}}
 return ['main_footer_count'=>$footers->length,'main_footer_titles'=>$titles,'secondary_title_in_main_text'=>str_contains($text,$secondaryExpected['title']),'secondary_label_in_main_text'=>str_contains($text,$secondaryExpected['items'][0]['label']),'secondary_links_in_main'=>$links];
};
$secondaryBeforeHttp=array_values(array_filter($before['http'],static fn($r)=>$r['identity']===Package::OWNERS[2]&&$r['kind']==='theory'));
if(count($secondaryBeforeHttp)!==1||$secondaryBeforeHttp[0]['status']!==200||$secondaryBeforeHttp[0]['sha256']!==($secondaryProof['independent_before_html_sha256']??null)||$secondaryBeforeHttp[0]['file']!==($secondaryProof['independent_before_html_file']??null)){throw new RuntimeException('M85 original secondary-navigation HTML provenance differs.');}
$secondaryBeforePath=$private.'/'.$secondaryBeforeHttp[0]['file'];if(!hash_equals($secondaryBeforeHttp[0]['sha256'],$fileHash($secondaryBeforePath))){throw new RuntimeException('M85 original C HTML bytes drifted.');}
$secondaryBeforeDoc=new DOMDocument('1.0','UTF-8');$prior=libxml_use_internal_errors(true);try{$loaded=$secondaryBeforeDoc->loadHTML('<?xml encoding="UTF-8">'.file_get_contents($secondaryBeforePath),LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($prior);}if(!$loaded){throw new RuntimeException('M85 original C HTML malformed.');}$secondaryBeforeXp=new DOMXPath($secondaryBeforeDoc);$secondaryBeforeMain=$secondaryBeforeXp->query('//*[@data-theory-main]');if($secondaryBeforeMain->length!==1){throw new RuntimeException('M85 original C main missing.');}
$secondaryBeforeFacts=$secondaryFacts($secondaryBeforeXp,$secondaryBeforeMain->item(0));$secondaryAbsentFacts=['main_footer_count'=>1,'main_footer_titles'=>['Уроки Verb to Be'],'secondary_title_in_main_text'=>false,'secondary_label_in_main_text'=>false,'secondary_links_in_main'=>[]];
if($secondaryBeforeFacts!==$secondaryAbsentFacts||array_intersect_key($secondaryProof['before'],$secondaryAbsentFacts)!==$secondaryAbsentFacts||array_intersect_key($secondaryProof['after'],$secondaryAbsentFacts)!==$secondaryAbsentFacts){throw new RuntimeException('M85 independent pre-existing secondary omission is not exact.');}
$inputs[$secondaryProofPath]=$secondaryProofSha;$inputs[$secondaryBeforePath]=$secondaryBeforeHttp[0]['sha256'];$inputs[$secondaryViewPath]=$secondaryViewSha;
$historicalHintProofPath=$private.'/mistake-hint-visibility-v1.json';$historicalHintProofSha='066462772c8740f359f745d987077a1b258c789b8fce70a3b09f329775200af5';
if(!hash_equals($historicalHintProofSha,$fileHash($historicalHintProofPath))){throw new RuntimeException('M85 historical actual hint-loss proof differs.');}$historicalHintProof=json_decode(file_get_contents($historicalHintProofPath),true,flags:JSON_THROW_ON_ERROR);
if(($historicalHintProof['source_hint_count']??null)!==6||($historicalHintProof['SSR_hint_count']??null)!==0){throw new RuntimeException('M85 historical actual hint-loss population differs.');}$inputs[$historicalHintProofPath]=$historicalHintProofSha;
$ownGraded=0;$fullInputCount=0;$linkedIds=[];$footerSourceItems=0;$footerNoncurrentLinks=0;$secondaryNavigationResults=[];$mistakeNotesChecked=0;$mistakeAliasNotesChecked=0;$mistakeNoteResults=[];$pairedMistakeFieldsChecked=0;

PHP;
$mistakesOld=<<<'PHP'
            foreach ($data['items'] as $n => $item) {
                foreach (['label', 'title', 'wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) { $contains($text, $item[$field], $label.'/items/'.$n.'/'.$field); }
            }
PHP;
$mistakesNew=<<<'PHP'
            $mistakeNodes=$xp->query('.//*[@data-theory-component="mistake"]',$node);$same($mistakeNodes->length,3,'Exact actual mistake item count');
            foreach($data['items'] as $n=>$item){
                if(!is_string($item['note']??null)||trim($item['note'])===''){throw new RuntimeException('M85 required authored mistake note missing; no legacy hint or optional-note fallback is accepted.');}
                $point=$mistakeNodes->item($n);$pointText=$learningText($xp,$point);
                foreach(['label','title','wrong_en','wrong_ua','right_en','right_ua','note'] as $field){$contains($pointText,$item[$field],$label.'/items/'.$n.'/'.$field);}
                $pairedFacts=M85LiveDiagnosticContract::assertPairedMistakeDom($xp,$point,$item,$plain);$pairedMistakeFieldsChecked+=4;
                $noteNodes=$xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-note ")]',$point);$same($noteNodes->length,1,'Exactly one canonical note in its own mistake item');$noteText=$learningText($xp,$noteNodes->item(0));$contains($noteText,$item['note'],$label.'/items/'.$n.'/owned canonical note');++$mistakeNotesChecked;
                $alias=array_key_exists('hint',$item);if($alias){$same($i===4||$i===5,true,'Only finite E/F aliases');$same($item['note'],$item['hint'],'Literal retained hint-to-note alias');$contains($noteText,$item['hint'],$label.'/items/'.$n.'/retained hint alias in owned SSR note');++$mistakeAliasNotesChecked;}
                $mistakeNoteResults[]=['identity'=>$owner,'source_index'=>$slot,'item_index'=>$n,'note_sha256'=>hash('sha256',$item['note']),'retained_hint_alias'=>$alias,'owned_note_in_SSR'=>true,'single_note_node'=>true,'paired_EN_UK_DOM'=>$pairedFacts];
            }
PHP;
$secondaryNavigation=<<<'PHP'
        if ($type === 'navigation-chips') {
            if($owner===Package::OWNERS[2]&&$slot===7){
                $same($data,$secondaryExpected,'Exact C secondary authored navigation data');
                $sourceSecondary=json_decode($target['after']['page']['blocks'][7]['body'],true,flags:JSON_THROW_ON_ERROR);$sourceBasic=$sourceSecondary;unset($sourceBasic['m85_native_v1']);
                $same($sourceBasic,$secondaryExpected,'Exact retained C secondary source data');$same($sourceSecondary['m85_native_v1']??null,['key'=>'verb-to-be-questions-7','detail_keys'=>[]],'Exact C native marker');
                $actualSecondaryFacts=$secondaryFacts($xp,$main);$same($actualSecondaryFacts,$secondaryBeforeFacts,'Unchanged finite C second-footer server-side omission');
                $secondaryNavigationResults[]=['identity'=>$owner,'source_index'=>7,'data'=>$secondaryExpected,'proof_file'=>'secondary-navigation-review-v1.json','proof_sha256'=>$secondaryProofSha,'independent_before_html_sha256'=>$secondaryBeforeHttp[0]['sha256'],'universal_view_before_and_after_sha256'=>$secondaryViewSha,'actual_after_html_sha256'=>hash('sha256',$html),'source_data_exact'=>true,'server_HTML'=>$actualSecondaryFacts,'secondary_navigation_visible_acceptance'=>false,'classification'=>'Pre-existing source-only second footer; unchanged universal view selects only the first navigation. No visible acceptance or runtime/content fix is claimed.'];
                continue;
            }
            $same(count($data['items']??[]),6,'Exact first footer source item count');$footerSourceItems+=count($data['items']);$footerNoncurrentLinks+=count(array_filter($data['items'],static fn($item)=>!$item['current']));
PHP;
$metadataOld=<<<'PHP'
    $metadata = array_values(array_filter($master['editorial_review']['pre_apply_metadata'] ?? [], static fn ($record) => ($record['identity'] ?? null) === $owner));
    $same(count($metadata), 1, 'Independent editorial metadata record count'); $metadata = $metadata[0];
    $same($metadata['h1'], $oldHttp['h1'], 'Editorial preserved H1');
    $same($metadata['expected_seo']['title'], $oldHttp['title'], 'Editorial preserved title');
PHP;
$metadataNew=<<<'PHP'
    $metadata=$prospective['metadata'][$i];$same($metadata['identity'],$owner,'Prospective owner');
    $same($metadata['raw_title'],$lesson['raw_title'],'Preserved raw Page.title');$same($metadata['preserved_h1'],$oldHttp['h1'],'Preserved English H1');
    $same($metadata['prospective_page_metadata']['title'],$oldHttp['title'],'Preserved metadata title');
    $same($metadata['independent_before_protected'],array_intersect_key($oldHttp,array_flip(['h1_count','h1','title','breadcrumb','canonical','robots_header','robots_meta'])),'Protected BEFORE metadata');
    $same($sourceBefore['targets'][$i]['before']['page']['subtitle_text'],$before['pages'][$owner]['page']['text'],'Independent original subtitle NULL');
    $same($target['after']['page']['subtitle_text'],null,'NULL subtitle preserved');$same($lesson['subtitle_text'],null,'Author NULL subtitle preserved');
    $same($target['after']['page']['title'],$oldHttp['raw_page_title'],'Source raw title');
    $same(hash('sha256',$lesson['blocks'][0]['data']['intro']),$metadata['hero_intro_sha256'],'Frozen hero descriptor');
PHP;
$tasksOld=<<<'PHP'
                $same(count($data[$group]), 2, 'Frozen practice '.$group.' task count');
                $groupText = $learningText($xp, $groups->item($n));
                foreach ($data[$group] as $t => $task) {
                    $contains($groupText, $task[$group === 'inputs' ? 'after' : 'label'], $label.'/'.$group.'/'.$t.'/condition');
                    if ($group === 'choices') { $contains($groupText, $task['prompt'], $label.'/'.$group.'/'.$t.'/stimulus'); }
                }
                if ($group !== 'inputs') { $same($xp->query('.//label', $groups->item($n))->length, 2, 'Practice '.$group.' SSR task count'); }
PHP;
$tasksNew=<<<'PHP'
                $tasks=$data[$group]??[];$same(count($tasks),2,'Two frozen '.$group.' tasks');$same($config[$group]??null,$tasks,'Exact serialized tasks/accepted/feedback');
                $groupNode=$groups->item($n);$groupText=$learningText($xp,$groupNode);
                foreach($tasks as $t=>$task){
                    if($group==='inputs'){$contains($groupText,$task['after'],$label.'/inputs/'.$t.'/visible Ukrainian criterion');$same(isset($task['hint'])||isset($task['placeholder']),false,'No ready-answer input hint');$same(str_contains($task['before'],'/'),true,'Native token bank exists');$same($xp->query('.//*[@data-word-suggestion-input="inputs-'.$t.'"]',$groupNode)->length,1,'Owned manual/token input');++$fullInputCount;}
                    else{$contains($groupText,$task['label'],$label.'/'.$group.'/'.$t.'/visible criterion');if($group==='choices'){$contains($groupText,$task['prompt']??null,$label.'/choice/'.$t.'/optional separate stimulus');}foreach($task['options']??$data[$group==='selects'?'options':'choice_options']??[] as $j=>$option){$contains($groupText,$option,$label.'/'.$group.'/'.$t.'/option/'.$j);}}
                    $same(in_array($task['answer'],$task['accepted'],true),true,'Canonical explicit accepted');foreach($task['feedback'] as $feedback){if(preg_match('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$feedback)===1){throw new RuntimeException('M85 feedback not plain text.');}}++$ownGraded;
                }
                if($group!=='inputs'){$same($xp->query('.//label',$groupNode)->length,2,'Native SSR '.$group.' controls');}
PHP;
$questionOld=<<<'PHP'
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
$questionNew=<<<'PHP'
            $same(array_column($payload,'id'),$bank['expected_selected_ids'],'Exact-bound existing IDs/order');$same($contract['targets'][$i]['level'],null,'Technical filter NULL');
            $poolCount=array_sum(array_map('count',$bank['expected_pool_ids_by_bank']));$same($poolCount,[71,72,72,72,72,70][$i],'Actual eligible old pool, not source72 assumption');
            foreach($bank['first_five'] as $q=>$expected){
                $same((string)$payload[$q]['type'],'4','Selected existing type4');$same($payload[$q]['level'],$bank['selected_levels'][$q],'Selected intended CEFR');
                $same($plain($payload[$q]['question']),$plain($expected['visible_condition_uk']),'Selected theory-only Ukrainian condition');$same($payload[$q]['correct_tokens'],$expected['correct_tokens'],'Native ordered tokens and case');
                $actualOptions=array_count_values($payload[$q]['options']);$expectedOptions=array_count_values($expected['token_bank_with_multiplicity']);ksort($actualOptions);ksort($expectedOptions);$same($actualOptions,$expectedOptions,'Native options/case/multiplicity');
                foreach(array_count_values($payload[$q]['correct_tokens']) as $token=>$count){if(($actualOptions[$token]??0)<$count){throw new RuntimeException('M85 repeated correct token missing.');}}
            }
            array_push($linkedIds,...array_column($payload,'id'));$blockResults['practice']=['source_index'=>$slot,'uuid'=>$uuid,'SSR_block_id'=>$node->getAttribute('id'),'technical_level'=>null,'own_groups'=>3,'graded_own_tasks'=>6,'ungraded_demos'=>0,'full_sentence_inputs'=>2,'linked_widgets'=>1,'linked_payload_count'=>5,'linked_ids'=>array_column($payload,'id'),'eligible_existing_pool'=>$poolCount,'selected_question_sequence_ordered_tokens_native_case_and_multiplicity'=>true];
PHP;
$controlsSnippet=<<<'PHP'
$same([$ownGraded,$fullInputCount,count($linkedIds),count(array_unique($linkedIds))],[36,12,30,30],'Exact own/full-input/existing-widget populations');$same([$footerSourceItems,$footerNoncurrentLinks,count($secondaryNavigationResults)],[36,30,1],'All first-footer source items/visible noncurrent links and finite C omission record');$same([$mistakeNotesChecked,$mistakeAliasNotesChecked,count($mistakeNoteResults),$pairedMistakeFieldsChecked],[18,6,18,72],'Exact actual mistake note count and paired correction fields');$controlResults=[];
$mistakeContract['paired_EN_UK_fields_SSR_checked']=$pairedMistakeFieldsChecked;
foreach($controls as $n=>$control){
 [$body,$status,$robots]=$http($control['url']);$doc=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);try{$loaded=$doc->loadHTML('<?xml encoding="UTF-8">'.$body,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($previous);}if(!$loaded){throw new RuntimeException('M85 control HTML malformed.');}$cx=new DOMXPath($doc);$same($cx->query('//h1')->length,1,'Control H1 count');$h1=$plain($cx->query('//h1')->item(0)->textContent);$same($h1,$n===0?'Past Perfect Continuous: Forms and Use':App\Support\M84WordOrderHaveGot::RENDERED_TITLES[$n-1],'Control exact H1');$same($cx->query('//*[@data-sentence-builder]')->length,1,'Control one widget');$payload=Acceptance::payload($body,$control['practice_uuid']);$same(is_array($payload)?count($payload):null,5,'Control five payload');$same(array_column($payload,'id'),$control['selected_ids'],'Control prior selected IDs/order');$same(array_map(static fn($q)=>(string)$q['type'],$payload),array_fill(0,5,'4'),'Control prior type4');
 $controlResults[]=['identity'=>$control['identity'],'url'=>$control['url'],'status'=>$status,'h1'=>$h1,'html_sha256'=>hash('sha256',$body),'prior_registry_sha256'=>$registrySha,'practice_uuid'=>$control['practice_uuid'],'selected_ids'=>$control['selected_ids'],'native_widget_count'=>1,'whole_html_equality_claimed'=>false,'scope'=>'Fresh GET/H1/selected IDs only; full source/SQL/old consumer equality and UI interactions are separate gates.'];
}
[$relatedBody,$relatedStatus]=$http('http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia');$relatedDoc=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);try{$relatedDoc->loadHTML('<?xml encoding="UTF-8">'.$relatedBody,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($previous);}$relatedXp=new DOMXPath($relatedDoc);$same($relatedXp->query('//h1')->length,1,'Related category H1 count');$same($plain($relatedXp->query('//h1')->item(0)->textContent),'Питальні речення та заперечення','C related category unchanged H1');$same($relatedXp->query('//link[@rel="canonical"]/@href')->item(0)?->nodeValue,'https://gramlyze.com/theory/pytalni-rechennia-ta-zaperechennia','Retained category canonical string, no production GET');$relatedResult=['url'=>'http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia','status'=>$relatedStatus,'html_sha256'=>hash('sha256',$relatedBody),'body_preserved_in_C_source'=>true];
foreach ($inputs as $path => $sha) {
PHP;
$replacements=[
 ['M53PassiveConstructionsPackage','M85VerbToBe',1],['M53BuilderHintPresentation','M85BuilderSelectionPresentation',5],['M53','M85',7],['m53','m85',3],
 ["foreach (Package::profile()['artifacts'] as \$path => \$sha) { \$inputs[\$root.'/'.\$path] = \$sha; }","foreach (Package::profile()['artifacts'] as \$path => \$sha) { \$inputs[\$root.'/'.\$path] = \$sha; }\n".$setup,1],
 ["\$http = static function (string \$url): array {","\$http = static function (string \$url) use (\$allowedUrls): array {",1],
 ["preg_match('~^http://gramlyze\\.loc/theory/passive-voice/theory-passive-voice-(?:causative|get-passive|impersonal-passive)$~D', \$url) !== 1","!in_array(\$url,\$allowedUrls,true)",1],
 ['HTTP target is outside the exact three local lessons.','HTTP target is outside the six targets, seven controls and retained category.',1],['finally { curl_close($curl); }','finally { unset($curl); }',1],
 [$metadataOld,$metadataNew,1],["\$metadata['expected_seo']['description']","\$metadata['prospective_page_metadata']['description']",1],
 ["['@type', 'url', 'name', 'headline', 'inLanguage']","['@type', 'url', 'name', 'headline', 'inLanguage', 'description']",2],
 ["\$same(\$resources, \$oldResources, 'Stable LearningResource identity');","\$stable=static fn(\$rows)=>array_map(static fn(\$r)=>array_diff_key(\$r,['description'=>true]),\$rows);\$same(\$stable(\$resources),\$stable(\$oldResources),'Stable LearningResource identity');\$same(\$resources[0]['description']??null,\$metadata['prospective_learning_resource_description'],'Prospective descriptor');",1],
 ["preg_match('~^/theory/passive-voice/[a-z0-9-]+$~D', \$item['url']) !== 1","!in_array('http://gramlyze.loc'.\$item['url'],\$navAllowed,true)",1],
 ["foreach (\$row['cells'] as \$c => \$cell) { \$contains(\$text, \$cell, \$label.'/rows/'.\$n.'/'.\$c); }","foreach (['en','ua','note'] as \$field) { \$contains(\$text, \$row[\$field]??null, \$label.'/rows/'.\$n.'/'.\$field); }",1],
 ["foreach (\$data['headers'] as \$header)","foreach (\$data['headers']??[] as \$header)",1],
 ["\$same(\$groups->length, 3, 'Practice own-group count');","\$same(\$groups->length,3,'Practice own-group count');\$factories=\$xp->query('.//*[@x-data and starts-with(@x-data,\"theoryPracticeSet(\")]',\$node);\$same(\$factories->length,1,'Native factory count');\$config=\$nativeConfig(\$factories->item(0));",1],
 [$tasksOld,$tasksNew,1],[$questionOld,$questionNew,1],
 [$mistakesOld,$mistakesNew,1],
 ["        if (\$type === 'navigation-chips') {",$secondaryNavigation,1],
 ["foreach (\$inputs as \$path => \$sha) {",$controlsSnippet,1],
 ["'pages' => \$results, 'SSR_learning_fields_checked' => \$fieldCount,","'pages'=>\$results,'read_only_controls'=>\$controlResults,'related_category'=>\$relatedResult,'SSR_learning_fields_checked'=>\$fieldCount,'own_graded_tasks'=>\$ownGraded,'full_sentence_inputs'=>\$fullInputCount,'linked_questions'=>count(\$linkedIds),'first_footer_source_items_checked'=>\$footerSourceItems,'first_footer_noncurrent_links_checked'=>\$footerNoncurrentLinks,'secondary_navigation'=>\$secondaryNavigationResults,'mistake_note_contract'=>\$mistakeContract,'mistake_notes_SSR_checked'=>\$mistakeNotesChecked,'exact_hint_note_aliases_SSR_checked'=>\$mistakeAliasNotesChecked,'mistake_note_results'=>\$mistakeNoteResults,'first_actual_attempt'=>['receipt_created'=>false,'reported_failure'=>'C source block7/title absent in server HTML; the first full run stopped before a receipt.'],'second_pre_receipt_attempt'=>['receipt_created'=>false,'diagnostic_only_failure'=>'Separate choice prompt is optional in the unchanged native view; C–F keep complete stimulus and criterion in label. New diagnostic read corrected to optional field; label/stimulus content remains strictly checked.'],'third_pre_receipt_attempt'=>['receipt_created'=>false,'real_content_mapping_omission'=>'Six newly authored E/F hints were absent from SSR because canonical correction requires note. This was not accepted as an optional field.','actual_before_repair_probe_sha256'=>\$historicalHintProofSha,'source_hints'=>6,'SSR_hints_before_repair'=>0],'secondary_navigation_visible_acceptance'=>false,",1],
 ["'status' => 'read-only-local-http-ssr-accepted'","'status' => 'accepted-with-pre-existing-secondary-navigation-omission'",1],
 ["'browser layout or visibility', 'client Check/Reset/token interaction'","'browser layout or visibility', 'C second navigation is not rendered in BEFORE or AFTER by the unchanged universal view; no visible acceptance', 'client Check/Reset/token interaction'",1],
 ['navigation destination availability beyond the exact three requested URLs','navigation targets other than the six exact pages and retained C related category',1],
];
if(($argv[1]??null)==='--profile-check-only'){
 $bytes=file_get_contents(__DIR__.'/m53-live-pages.php');if(!hash_equals('66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',hash('sha256',$bytes))){throw new RuntimeException('M85 M53 primitive drift.');}
 foreach($replacements as $i=>[$old,$new,$count]){$n=substr_count($bytes,$old);if($n!==$count){throw new RuntimeException('M85 literal count differs at '.$i.': '.$n.' expected '.$count);}$bytes=str_replace($old,$new,$bytes);}token_get_all($bytes,TOKEN_PARSE);echo json_encode(['profile'=>'M85 live pages','replacements'=>count($replacements),'parsed'=>true,'http_requests'=>0,'SQL_access'=>false])."\n";return;
}
runFrozenReadOnlyProfile('m53-live-pages.php','66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',$replacements);
