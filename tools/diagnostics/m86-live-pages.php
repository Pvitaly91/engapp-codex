<?php

/** Finite M86 anonymous loopback SSR profile; no bootstrap/SQL and no browser-interaction claim. */
require __DIR__.'/frozen-readonly-profile.php';
require_once __DIR__.'/m86-live-contract.php';
set_error_handler(static function(int $severity,string $message):never{throw new RuntimeException('M86 diagnostic warning: '.$message);});
$setup=<<<'PHP'
$mistakeContract=M86LiveDiagnosticContract::assertMistakeNotes($master['lessons']);
$contractPath=$root.'/tools/diagnostics/m86-live-contract.php';$inputs[$contractPath]=$fileHash($contractPath);
$editorialRelative='docs/content/m86-present-simple-continuous-basics.editorial.v1.json';$editorialPath=$root.'/'.$editorialRelative;$checksPath=$root.'/'.Package::CHECKSUMS;
if(!hash_equals(Package::CHECKSUMS_SHA,$fileHash($checksPath))){throw new RuntimeException('M86 frozen checksums differ.');}
$checks=json_decode(file_get_contents($checksPath),true,flags:JSON_THROW_ON_ERROR);$editorialPins=array_values(array_filter($checks['artifacts'],static fn($r)=>($r['path']??null)===$editorialRelative));
if(count($editorialPins)!==1||preg_match('/^[a-f0-9]{64}$/D',$editorialPins[0]['sha256']??'')!==1||!hash_equals($editorialPins[0]['sha256'],$fileHash($editorialPath))){throw new RuntimeException('M86 editorial checksum binding differs.');}
$editorial=json_decode(file_get_contents($editorialPath),true,flags:JSON_THROW_ON_ERROR);$prospective=$editorial['expected_http_metadata']??[];
$metadataPath=$private.'/prospective-metadata-v1.json';$metadataSha='fae443656f10a7cda6ed2ad148c402c9cfa26f772f436928a1015cdc22eb7b6b';
if(!hash_equals($metadataSha,$fileHash($metadataPath))||$prospective!==json_decode(file_get_contents($metadataPath),true,flags:JSON_THROW_ON_ERROR)
 ||($prospective['projection_sha256']??null)!=='d77aeaa3b24ebc8a259f39fb86a1098bffda6bcdd6b503d35e9d1030c88280da'
 ||($prospective['independent_before_sha256']??null)!==M86ContentPatch::BEFORE_SHA
 ||array_column($prospective['metadata']??[],'identity')!==Package::OWNERS){throw new RuntimeException('M86 independent prospective metadata differs.');}
$registryPath=$private.'/registry-before-sentence-builders-v1.json';$registrySha='f7f7118e803e110df6f854e096081878b45d5ce63a605d9d02756cfd6b75e9b1';
if(!hash_equals($registrySha,$fileHash($registryPath))){throw new RuntimeException('M86 prior205 registry differs.');}
$priorRegistry=json_decode(file_get_contents($registryPath),true,flags:JSON_THROW_ON_ERROR);
if(count($priorRegistry['rows']??[])!==205||($priorRegistry['failed']??null)!==0){throw new RuntimeException('M86 exact prior205 registry population differs.');}
$controlOwners=['Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousFormsTheorySeeder',...App\Support\M85VerbToBe::OWNERS];$controls=[];
foreach($controlOwners as $owner){$found=array_values(array_filter($priorRegistry['rows'],static fn($r)=>$r['identity']===$owner));if(count($found)!==1||$found[0]['errors']!==[]||$found[0]['http_status']!==200){throw new RuntimeException('M86 reference/M85 registry control missing.');}$controls[]=$found[0];}
$navAllowed=[];foreach($master['lessons'] as $n=>$l){$nav=$l['blocks'][Package::NAVIGATION_SOURCE_INDICES[$n]]['data'];if(count($nav['items'])!==4){throw new RuntimeException('M86 exact four-item footer source differs.');}foreach($nav['items'] as $item){$navAllowed[]='http://gramlyze.loc'.$item['url'];}}
$navAllowed=array_values(array_unique($navAllowed));if(count($navAllowed)!==8){throw new RuntimeException('M86 exact eight footer destinations differ.');}
$relatedUrl='http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia';
$allowedUrls=[...$navAllowed,$relatedUrl,...array_column($controls,'url')];if(count($allowedUrls)!==16||count(array_unique($allowedUrls))!==16){throw new RuntimeException('M86 finite HTTP allowlist differs.');}
foreach($patch['targets'] as $target){if(json_decode(file_get_contents($root.'/'.$target['path']),true,flags:JSON_THROW_ON_ERROR)!==$target['after']){throw new RuntimeException('M86 canonical active definition differs.');}}
$inputs[$editorialPath]=$editorialPins[0]['sha256'];$inputs[$checksPath]=Package::CHECKSUMS_SHA;$inputs[$metadataPath]=$metadataSha;$inputs[$registryPath]=$registrySha;
foreach($prospective['native_policy_pins'] as $path=>$sha){if(!hash_equals($sha,$fileHash($root.'/'.$path))){throw new RuntimeException('M86 native metadata policy changed.');}$inputs[$root.'/'.$path]=$sha;}
$nativeConfig=static function(DOMElement $node):array{
 $value=$node->getAttribute('x-data');$prefix="theoryPracticeSet(JSON.parse('";$suffix="'))";
 if(!str_starts_with($value,$prefix)||!str_ends_with($value,$suffix)){throw new RuntimeException('M86 native config shape differs.');}
 $literal=substr($value,strlen($prefix),-strlen($suffix));if(str_contains($literal,'"')||str_contains($literal,"'")){throw new RuntimeException('M86 config is not an escaped literal.');}
 $json=json_decode('"'.$literal.'"',true,flags:JSON_THROW_ON_ERROR);$data=is_string($json)?json_decode($json,true,flags:JSON_THROW_ON_ERROR):null;
 if(!is_array($data)){throw new RuntimeException('M86 config is not an object.');}return $data;
};
$parseDocument=static function(string $html):DOMDocument{$doc=new DOMDocument('1.0','UTF-8');$old=libxml_use_internal_errors(true);try{$loaded=$doc->loadHTML('<?xml encoding="UTF-8">'.M86LiveDiagnosticContract::prepareHtmlForDom($html),LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($old);}if(!$loaded){throw new RuntimeException('M86 immutable BEFORE HTML cannot be parsed.');}return $doc;};
$priorA=array_values(array_filter($before['http'],static fn($r)=>$r['identity']===Package::OWNERS[0]&&$r['kind']==='theory'));
if(count($priorA)!==1||$priorA[0]['status']!==200){throw new RuntimeException('M86 independent A native-config HTML absent.');}
$priorAPath=$private.'/'.$priorA[0]['file'];if(!hash_equals($priorA[0]['sha256'],$fileHash($priorAPath))){throw new RuntimeException('M86 original A HTML bytes differ.');}
$priorADom=$parseDocument(file_get_contents($priorAPath));$priorAXp=new DOMXPath($priorADom);$priorFactories=$priorAXp->query('//*[@id="block-10108"]//*[@x-data and starts-with(@x-data,"theoryPracticeSet(")]');
if($priorFactories->length!==1){throw new RuntimeException('M86 original A native factory is not exact.');}$beforeConfig=$nativeConfig($priorFactories->item(0));$inputs[$priorAPath]=$priorA[0]['sha256'];
$secondaryProofPath=$private.'/secondary-navigation-review-v1.json';$secondaryProofSha='68c9eba8718f1c16041906bc901cdecfbf1eae994e760ad8802877a4f953f873';
if(!hash_equals($secondaryProofSha,$fileHash($secondaryProofPath))){throw new RuntimeException('M86 independent secondary-navigation proof differs.');}
$secondaryProof=json_decode(file_get_contents($secondaryProofPath),true,flags:JSON_THROW_ON_ERROR);
$secondaryExpected=['title'=>'Пов’язана тема','items'=>[['label'=>'Питальні речення та заперечення','url'=>'/theory/pytalni-rechennia-ta-zaperechennia','current'=>false]]];
$secondaryViewPath=$root.'/resources/views/theory/show.blade.php';$secondaryViewSha='3745e0b97a025f6c686434972f603a61ba71e5cfdafe83bada5228c58d68d87c';
if(($secondaryProof['identity']??null)!==Package::OWNERS[2]||($secondaryProof['source_index']??null)!==8||($secondaryProof['independent_before_sha256']??null)!==M86ContentPatch::BEFORE_SHA
 ||($secondaryProof['authored_secondary_data']??null)!==$secondaryExpected||($secondaryProof['source_sql_exact']??null)!==true||($secondaryProof['secondary_navigation_visible_acceptance']??null)!==false
 ||($secondaryProof['view_path']??null)!=='resources/views/theory/show.blade.php'||($secondaryProof['view_sha256']??null)!==$secondaryViewSha
 ||($before['visual_hashes']['resources/views/theory/show.blade.php']??null)!==$secondaryViewSha||!hash_equals($secondaryViewSha,$fileHash($secondaryViewPath))){throw new RuntimeException('M86 finite second-navigation source/view/BEFORE lineage differs.');}
$secondaryFacts=static function(DOMXPath $xp,DOMNode $main)use($secondaryExpected):array{
 $parts=[];foreach($xp->query('.//text()[not(ancestor::script) and not(ancestor::style)]',$main) as $t){$parts[]=$t->nodeValue;}$text=trim(preg_replace('/\s+/u',' ',implode(' ',$parts)));
 $footers=$xp->query('.//section[contains(concat(" ",normalize-space(@class)," ")," theory-content-blocks ")]/nav',$main);$titles=[];
 foreach($footers as $footer){$p=$xp->query('./p',$footer);$titles[]=$p->length===1?trim($p->item(0)->textContent):null;}$links=[];
 foreach($xp->query('.//a[@href]',$main) as $a){if(in_array($a->getAttribute('href'),[$secondaryExpected['items'][0]['url'],'http://gramlyze.loc'.$secondaryExpected['items'][0]['url'],'https://gramlyze.com'.$secondaryExpected['items'][0]['url']],true)){$links[]=['href'=>$a->getAttribute('href'),'label'=>trim(preg_replace('/\s+/u',' ',$a->textContent))];}}
 return ['main_footer_count'=>$footers->length,'main_footer_titles'=>$titles,'secondary_title_in_main_text'=>str_contains($text,$secondaryExpected['title']),'secondary_label_in_main_text'=>str_contains($text,$secondaryExpected['items'][0]['label']),'secondary_links_in_main'=>$links];
};
$secondaryBeforeHttp=array_values(array_filter($before['http'],static fn($r)=>$r['identity']===Package::OWNERS[2]&&$r['kind']==='theory'));
if(count($secondaryBeforeHttp)!==1||$secondaryBeforeHttp[0]['status']!==200||$secondaryBeforeHttp[0]['file']!==$secondaryProof['independent_before_html_file']||$secondaryBeforeHttp[0]['sha256']!==$secondaryProof['independent_before_html_sha256']){throw new RuntimeException('M86 secondary-navigation original HTML provenance differs.');}
$secondaryBeforePath=$private.'/'.$secondaryBeforeHttp[0]['file'];if(!hash_equals($secondaryBeforeHttp[0]['sha256'],$fileHash($secondaryBeforePath))){throw new RuntimeException('M86 original C HTML bytes differ.');}
$secondaryBeforeDoc=$parseDocument(file_get_contents($secondaryBeforePath));$secondaryBeforeXp=new DOMXPath($secondaryBeforeDoc);$secondaryBeforeMain=$secondaryBeforeXp->query('//*[@data-theory-main]');
if($secondaryBeforeMain->length!==1){throw new RuntimeException('M86 original C main absent.');}
$secondaryBeforeFacts=$secondaryFacts($secondaryBeforeXp,$secondaryBeforeMain->item(0));
$secondaryAbsentFacts=['main_footer_count'=>1,'main_footer_titles'=>['Уроки Present Simple'],'secondary_title_in_main_text'=>false,'secondary_label_in_main_text'=>false,'secondary_links_in_main'=>[]];
if($secondaryBeforeFacts!==$secondaryAbsentFacts||$secondaryProof['before']!==$secondaryAbsentFacts||$secondaryProof['fresh_pre_apply']!==$secondaryAbsentFacts){throw new RuntimeException('M86 exact pre-existing secondary omission not proved.');}
$inputs[$secondaryProofPath]=$secondaryProofSha;$inputs[$secondaryBeforePath]=$secondaryBeforeHttp[0]['sha256'];$inputs[$secondaryViewPath]=$secondaryViewSha;
$ownGraded=0;$fullInputCount=0;$ungradedDemos=0;$rephraseInputCount=0;$linkedIds=[];$footerSourceItems=0;$footerNoncurrentLinks=0;$secondaryNavigationResults=[];$mistakeNotesChecked=0;$pairedMistakeFieldsChecked=0;$mistakeNoteResults=[];$httpRequestCount=0;
$nonRenderedSelectPrompts=[];

PHP;
$metadataOld=<<<'PHP'
    $metadata = array_values(array_filter($master['editorial_review']['pre_apply_metadata'] ?? [], static fn ($record) => ($record['identity'] ?? null) === $owner));
    $same(count($metadata), 1, 'Independent editorial metadata record count'); $metadata = $metadata[0];
    $same($metadata['h1'], $oldHttp['h1'], 'Editorial preserved H1');
    $same($metadata['expected_seo']['title'], $oldHttp['title'], 'Editorial preserved title');
PHP;
$metadataNew=<<<'PHP'
    $metadata=$prospective['metadata'][$i];$same($metadata['identity'],$owner,'Prospective exact owner');
    $same($metadata['raw_title'],$lesson['raw_title'],'Preserved raw Page.title');$same($metadata['preserved_h1'],$oldHttp['h1'],'Preserved English H1');
    $same($metadata['prospective_page_metadata']['title'],$oldHttp['title'],'Preserved metadata title');
    $same($metadata['independent_before_protected'],array_intersect_key($oldHttp,array_flip(['h1_count','h1','title','breadcrumb','canonical','robots_header','robots_meta'])),'Independent protected BEFORE metadata');
    $same($sourceBefore['targets'][$i]['before']['page']['subtitle_text'],$before['pages'][$owner]['page']['text'],'Independent subtitle NULL');
    $same($target['after']['page']['subtitle_text'],null,'NULL subtitle preserved');$same($lesson['subtitle_text'],null,'Author NULL subtitle');
    $same($target['after']['page']['title'],$oldHttp['raw_page_title'],'Exact raw source title');
    $same(hash('sha256',$lesson['blocks'][0]['data']['intro']),$metadata['hero_intro_sha256'],'Frozen independently projected hero');
PHP;
$navigationOld=<<<'PHP'
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
$navigationNew=<<<'PHP'
        if ($type === 'navigation-chips') {
            if($owner===Package::OWNERS[2]&&$slot===8){
                $same($data,$secondaryExpected,'Exact retained C second-navigation author data');
                $oldSecondary=json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][8]['body'],true,flags:JSON_THROW_ON_ERROR);$same($oldSecondary,$secondaryExpected,'Independent original secondary source data');
                $sourceSecondary=json_decode($target['after']['page']['blocks'][8]['body'],true,flags:JSON_THROW_ON_ERROR);$sourceBasic=$sourceSecondary;unset($sourceBasic['m86_native_v1']);$same($sourceBasic,$secondaryExpected,'Exact current secondary source data');
                $same($sourceSecondary['m86_native_v1']??null,['key'=>$author['key'],'detail_keys'=>[]],'Exact C native marker');
                $actualSecondaryFacts=$secondaryFacts($xp,$main);$same($actualSecondaryFacts,$secondaryBeforeFacts,'Finite unchanged C secondary-footer omission');
                $secondaryNavigationResults[]=['identity'=>$owner,'source_index'=>8,'source_data_exact'=>true,'proof_file'=>'secondary-navigation-review-v1.json','proof_sha256'=>$secondaryProofSha,'independent_before_html_sha256'=>$secondaryBeforeHttp[0]['sha256'],'universal_view_before_and_after_sha256'=>$secondaryViewSha,'actual_after_html_sha256'=>hash('sha256',$html),'server_HTML'=>$actualSecondaryFacts,'secondary_navigation_visible_acceptance'=>false,'classification'=>'Pre-existing source-only second footer remains absent in SSR; no visible acceptance, source deletion or view fix is claimed.'];
                continue;
            }
            $same($slot,Package::NAVIGATION_SOURCE_INDICES[$i],'Exact primary-footer source slot');$same(count($data['items']),4,'Four primary footer source items');
            $footerNodes=$xp->query('.//section[contains(concat(" ",normalize-space(@class)," ")," theory-content-blocks ")]/nav',$main);$same($footerNodes->length,1,'One native primary footer');
            $footerNode=$footerNodes->item(0);$footerText=$learningText($xp,$footerNode);$contains($footerText,$data['title'],$label.'/owned footer title');
            $sourceLinks=0;foreach($data['items'] as $item){
                $contains($footerText,$item['label'],$label.'/owned item label');if($item['current']){continue;}
                if(!in_array('http://gramlyze.loc'.$item['url'],$navAllowed,true)){throw new RuntimeException('M86 footer destination outside exact verified eight paths.');}
                $matches=[];foreach($xp->query('.//a[@href]',$footerNode) as $link){if($link->getAttribute('href')===$item['url']&&$plain($link->textContent)===$plain($item['label'])){$matches[]=$link;}}
                $same(count($matches),1,'Exact owned noncurrent footer href/label');$navLinks[]='http://gramlyze.loc'.$item['url'];++$sourceLinks;
            }
            $same($sourceLinks,3,'Three primary footer noncurrent destinations');$same($xp->query('.//a[@href]',$footerNode)->length,3,'No extra primary footer links');
            $footerSourceItems+=4;$footerNoncurrentLinks+=3;continue;
        }
PHP;
$tasksOld=<<<'PHP'
            $groups = $xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]', $node);
            $same($groups->length, 3, 'Practice own-group count');
            foreach (['selects', 'choices', 'inputs'] as $n => $group) {
                $same(count($data[$group]), 2, 'Frozen practice '.$group.' task count');
                $groupText = $learningText($xp, $groups->item($n));
                foreach ($data[$group] as $t => $task) {
                    $contains($groupText, $task[$group === 'inputs' ? 'after' : 'label'], $label.'/'.$group.'/'.$t.'/condition');
                    if ($group === 'choices') { $contains($groupText, $task['prompt'], $label.'/'.$group.'/'.$t.'/stimulus'); }
                }
                if ($group !== 'inputs') { $same($xp->query('.//label', $groups->item($n))->length, 2, 'Practice '.$group.' SSR task count'); }
            }
            $same($xp->query('.//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]', $node)->length, 2, 'Practice manual/token input count');
            foreach (['select_title', 'select_intro', 'choice_title', 'choice_intro', 'input_title', 'input_intro'] as $field) { $contains($text, $data[$field], $label.'/'.$field); }
            foreach (['title', 'intro', 'footer'] as $field) { $contains($text, $data['linked_practice'][$field], $label.'/linked_practice/'.$field); }
PHP;
$tasksNew=<<<'PHP'
            $factories=$xp->query('.//*[@x-data and starts-with(@x-data,"theoryPracticeSet(")]',$node);$same($factories->length,1,'One native practice factory');
            $config=$nativeConfig($factories->item(0));
            $beforePractice=$i<4?json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][6]['body'],true,flags:JSON_THROW_ON_ERROR):[];
            $practiceFacts=M86LiveDiagnosticContract::assertPracticeDom($xp,$node,$data,$config,$beforeConfig,$i,$learningText,$plain,$contains,$same,$beforePractice);
            $ownGraded+=$practiceFacts['graded'];$fullInputCount+=$practiceFacts['full_inputs'];$ungradedDemos+=$practiceFacts['ungraded_demos'];$rephraseInputCount+=$practiceFacts['rephrase_inputs'];
            foreach($practiceFacts['nonrendered_select_prompt_metadata'] as $metadataRow){$nonRenderedSelectPrompts[]=['identity'=>$owner]+$metadataRow;}
            foreach(['title','intro','footer'] as $field){$contains($text,$data['linked_practice'][$field],$label.'/linked_practice/'.$field);}
PHP;
$questionsOld=<<<'PHP'
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
$questionsNew=<<<'PHP'
            $same(array_column($payload,'id'),$bank['expected_selected_ids'],'Exact selected existing IDs/order');$same($contract['targets'][$i]['level'],null,'Captured technical NULL practice filter');
            $poolCount=array_sum(array_map('count',$bank['expected_pool_ids_by_bank']));$same($poolCount,72,'Exact actual old bank pool');
            foreach($bank['first_five'] as $q=>$expected){
                $same((string)$payload[$q]['type'],'4','Existing type4 selection');$same($payload[$q]['level'],$bank['selected_levels'][$q],'Selected intended focus level without SQL rewrites');
                $same($plain($payload[$q]['question']),$plain($expected['visible_condition_uk']),'Reviewed canonical-theory Ukrainian condition');
                $same($payload[$q]['correct_tokens'],$expected['correct_tokens'],'Native correct tokens/case/order');
                $available=array_count_values($payload[$q]['options']);$expectedOptions=array_count_values($expected['token_bank_with_multiplicity']);ksort($available);ksort($expectedOptions);$same($available,$expectedOptions,'Native option case and multiplicity');
                foreach(array_count_values($payload[$q]['correct_tokens']) as $token=>$count){if(($available[$token]??0)<$count){throw new RuntimeException('M86 widget loses repeated correct token instance.');}}
            }
            array_push($linkedIds,...array_column($payload,'id'));
            $blockResults['practice']=['source_index'=>$slot,'uuid'=>$uuid,'SSR_block_id'=>$node->getAttribute('id'),'technical_level'=>null,'own_groups'=>$practiceFacts['groups'],'graded_own_tasks'=>$practiceFacts['graded'],'ungraded_demos'=>$practiceFacts['ungraded_demos'],'full_sentence_inputs'=>$practiceFacts['full_inputs'],'rephrase_inputs'=>$practiceFacts['rephrase_inputs'],'option_texts_SSR'=>$practiceFacts['option_texts'],'own_control_records'=>$practiceFacts['controls'],'linked_widgets'=>1,'linked_payload_count'=>5,'linked_ids'=>array_column($payload,'id'),'eligible_existing_pool'=>$poolCount,'serialized_task_keys_aliases_feedback_exact'=>true,'selected_tokens_native_case_order_and_multiplicity'=>true];
            $blockResults['practice']['nonrendered_select_prompt_metadata']=$practiceFacts['nonrendered_select_prompt_metadata'];
PHP;
$mistakesOld=<<<'PHP'
            foreach ($data['items'] as $n => $item) {
                foreach (['label', 'title', 'wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) { $contains($text, $item[$field], $label.'/items/'.$n.'/'.$field); }
            }
PHP;
$mistakesNew=<<<'PHP'
            $points=$xp->query('.//*[@data-theory-component="mistake"]',$node);$same($points->length,3,'Exactly three canonical mistake items');
            foreach($data['items'] as $n=>$item){
                if(!is_string($item['note']??null)||trim($item['note'])===''||array_key_exists('hint',$item)){throw new RuntimeException('M86 each correction requires its own note, without legacy hint aliases.');}
                $point=$points->item($n);$pointText=$learningText($xp,$point);foreach(['label','title','wrong_en','wrong_ua','right_en','right_ua','note'] as $field){$contains($pointText,$item[$field],$label.'/items/'.$n.'/'.$field);}
                $paired=M86LiveDiagnosticContract::assertPairedMistakeDom($xp,$point,$item,$plain);$pairedMistakeFieldsChecked+=4;
                $noteNodes=$xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-note ")]',$point);$same($noteNodes->length,1,'Exactly one owned canonical mistake note');$contains($learningText($xp,$noteNodes->item(0)),$item['note'],$label.'/items/'.$n.'/owned note');++$mistakeNotesChecked;
                $mistakeNoteResults[]=['identity'=>$owner,'source_index'=>$slot,'item_index'=>$n,'note_sha256'=>hash('sha256',$item['note']),'owned_note_in_SSR'=>true,'single_note_node'=>true,'paired_EN_UK_DOM'=>$paired];
            }
PHP;
$controlsSnippet=<<<'PHP'
$same([$ownGraded,$fullInputCount,$ungradedDemos,$rephraseInputCount,count($linkedIds),count(array_unique($linkedIds))],[55,16,4,11,30,30],'Exact own/full-input/demo/rephrase/widget populations');
$expectedMetadataPositions=[];foreach([2,3] as $ownerIndex){foreach([0,1,2] as $taskIndex){$expectedMetadataPositions[]=[Package::OWNERS[$ownerIndex],'selects/'.$taskIndex];}}
$same(array_map(static fn($row)=>[$row['identity'],$row['control']],$nonRenderedSelectPrompts),$expectedMetadataPositions,'Exactly six independent BEFORE-bound generic select metadata fields, not visible stimuli');
$same([$footerSourceItems,$footerNoncurrentLinks,count($secondaryNavigationResults)],[24,18,1],'All primary footer source items/links and finite C omission');
$same([$mistakeNotesChecked,count($mistakeNoteResults),$pairedMistakeFieldsChecked],[18,18,72],'Eighteen actual owned mistake notes and paired EN/UK fields');
$mistakeContract['paired_EN_UK_fields_SSR_checked']=$pairedMistakeFieldsChecked;$controlResults=[];
foreach($controls as $n=>$control){
 [$body,$status,$robots]=$http($control['url']);$doc=$parseDocument($body);$cx=new DOMXPath($doc);$same($cx->query('//h1')->length,1,'Reference H1 count');
 $h1=$plain($cx->query('//h1')->item(0)->textContent);$same($h1,$n===0?'Past Perfect Continuous: Forms and Use':App\Support\M85VerbToBe::RENDERED_TITLES[$n-1],'Exact reference/M85 H1');
 $same($cx->query('//*[@data-sentence-builder]')->length,1,'Reference one widget');$payload=Acceptance::payload($body,$control['practice_uuid']);$same(is_array($payload)?count($payload):null,5,'Reference five-question payload');
 $same(array_column($payload,'id'),$control['selected_ids'],'Reference prior selected IDs/order');$same(array_map(static fn($q)=>(string)$q['type'],$payload),array_fill(0,5,'4'),'Reference type4');
 $controlResults[]=['identity'=>$control['identity'],'url'=>$control['url'],'status'=>$status,'h1'=>$h1,'html_sha256'=>hash('sha256',$body),'prior_registry_sha256'=>$registrySha,'practice_uuid'=>$control['practice_uuid'],'selected_ids'=>$control['selected_ids'],'native_widget_count'=>1,'whole_html_equality_claimed'=>false,'scope'=>'Fresh GET/H1/widget identities only. All205 prior-source/SQL/consumer/registry regressions are separate gates.'];
}
[$relatedBody,$relatedStatus]=$http($relatedUrl);$relatedDoc=$parseDocument($relatedBody);$relatedXp=new DOMXPath($relatedDoc);$same($relatedXp->query('//h1')->length,1,'Related category H1 count');
$same($plain($relatedXp->query('//h1')->item(0)->textContent),'Питальні речення та заперечення','Retained related-category H1');
$same($relatedXp->query('//link[@rel="canonical"]/@href')->item(0)?->nodeValue,'https://gramlyze.com/theory/pytalni-rechennia-ta-zaperechennia','Retained related-category canonical string, no production GET');
$relatedResult=['url'=>$relatedUrl,'status'=>$relatedStatus,'html_sha256'=>hash('sha256',$relatedBody),'secondary_link_preserved_in_C_source'=>true,'visible_in_C'=>false];
$same($httpRequestCount,14,'Exact six targets/seven controls/one related-category GET count');
foreach ($inputs as $path => $sha) {
PHP;
$replacements=[
 ['M53PassiveConstructionsPackage','M86PresentSimpleContinuousBasics',1],
 ['M53BuilderHintPresentation','M86BuilderSelectionPresentation',5],
 ['M53','M86',7],
 ['m53','m86',3],
 ['foreach (Package::profile()[\'artifacts\'] as $path => $sha) { $inputs[$root.\'/\'.$path] = $sha; }','foreach (Package::profile()[\'artifacts\'] as $path => $sha) { $inputs[$root.\'/\'.$path] = $sha; }
'.$setup,1],
 ['$http = static function (string $url): array {','$http = static function (string $url) use ($allowedUrls, &$httpRequestCount): array {',1],
 ['preg_match(\'~^http://gramlyze\\.loc/theory/passive-voice/theory-passive-voice-(?:causative|get-passive|impersonal-passive)$~D\', $url) !== 1','!in_array($url,$allowedUrls,true)',1],
 ['HTTP target is outside the exact three local lessons.','HTTP target is outside the exact M86 targets/navigation and seven read-only controls.',1],
 ['$curl = curl_init($url); $robots = null;','++$httpRequestCount; $curl = curl_init($url); $robots = null;',1],
 ['finally { curl_close($curl); }','finally { unset($curl); }',1],
 ['$dom->loadHTML(\'<?xml encoding="UTF-8">\'.$html, LIBXML_NONET)','$dom->loadHTML(\'<?xml encoding="UTF-8">\'.M86LiveDiagnosticContract::prepareHtmlForDom($html), LIBXML_NONET)',1],
 [$metadataOld,$metadataNew,1],
 ['$metadata[\'expected_seo\'][\'description\']','$metadata[\'prospective_page_metadata\'][\'description\']',1],
 ['[\'@type\', \'url\', \'name\', \'headline\', \'inLanguage\']','[\'@type\', \'url\', \'name\', \'headline\', \'inLanguage\', \'description\']',2],
 ['$same($resources, $oldResources, \'Stable LearningResource identity\');','$stable=static fn($rs)=>array_map(static fn($r)=>array_diff_key($r,[\'description\'=>true]),$rs);$same($stable($resources),$stable($oldResources),\'Stable LearningResource identity\');$same($resources[0][\'description\']??null,$metadata[\'prospective_learning_resource_description\'],\'Independently projected resource description\');',1],
 [$navigationOld,$navigationNew,1],
 ['foreach ($data[\'headers\'] as $header)','foreach ($data[\'headers\']??[] as $header)',1],
 ['foreach ($row[\'cells\'] as $c => $cell) { $contains($text, $cell, $label.\'/rows/\'.$n.\'/\'.$c); }','foreach ([\'en\',\'ua\',\'note\'] as $field) { $contains($text,$row[$field]??null,$label.\'/rows/\'.$n.\'/\'.$field); }',1],
 [$mistakesOld,$mistakesNew,1],
 [$tasksOld,$tasksNew,1],
 [$questionsOld,$questionsNew,1],
 ['foreach ($inputs as $path => $sha) {',$controlsSnippet,1],
 ['\'pages\' => $results, \'SSR_learning_fields_checked\' => $fieldCount,','\'pages\'=>$results,\'read_only_reference_controls\'=>$controlResults,\'related_category\'=>$relatedResult,\'HTTP_GET_count\'=>$httpRequestCount,\'SSR_learning_fields_checked\'=>$fieldCount,\'own_graded_tasks\'=>$ownGraded,\'full_sentence_inputs\'=>$fullInputCount,\'ungraded_demos\'=>$ungradedDemos,\'scored_rephrase_inputs\'=>$rephraseInputCount,\'nonrendered_select_prompt_metadata\'=>$nonRenderedSelectPrompts,\'nonrendered_select_prompt_count\'=>count($nonRenderedSelectPrompts),\'linked_questions\'=>count($linkedIds),\'primary_footer_source_items_checked\'=>$footerSourceItems,\'primary_footer_noncurrent_links_checked\'=>$footerNoncurrentLinks,\'secondary_navigation\'=>$secondaryNavigationResults,\'secondary_navigation_visible_acceptance\'=>false,\'mistake_note_contract\'=>$mistakeContract,\'mistake_notes_SSR_checked\'=>$mistakeNotesChecked,\'mistake_note_results\'=>$mistakeNoteResults,\'prior_registry_population\'=>205,\'whole_prior205_regression_claimed\'=>false,',1],
 ['\'status\' => \'read-only-local-http-ssr-accepted\'','\'status\' => \'accepted-with-pre-existing-secondary-navigation-omission\'',1],
 ['\'browser layout or visibility\', \'client Check/Reset/token interaction\'','\'browser layout or visibility\', \'C source index8 secondary navigation is absent in independent BEFORE and current SSR; no visible acceptance\', \'client Check/Reset/token interaction\'',1],
 ['navigation destination availability beyond the exact three requested URLs','availability of two non-target footer destinations or stative/future bridge targets during this run; their earlier read-only navigation proof is separate',1],
 ['\'SQL postconditions (separate diagnostic)\'','\'all205 prior-source/SQL/old-consumer/registry regressions (separate diagnostics)\'',1],
];
if(($argv[1]??null)==='--profile-check-only'){
 $bytes=file_get_contents(__DIR__.'/m53-live-pages.php');
 if(!hash_equals('66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',hash('sha256',$bytes))){throw new RuntimeException('M86 immutable M53 primitive differs.');}
 foreach($replacements as $i=>[$old,$new,$count]){$n=substr_count($bytes,$old);if($n!==$count){throw new RuntimeException('M86 literal count differs at '.$i.': '.$n.' expected '.$count);}$bytes=str_replace($old,$new,$bytes);}
 token_get_all($bytes,TOKEN_PARSE);
 echo json_encode(['profile'=>'M86 live pages','replacements'=>count($replacements),'parsed'=>true,'HTTP_requests'=>0,'SQL_access'=>false])."\n";return;
}
runFrozenReadOnlyProfile('m53-live-pages.php','66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',$replacements);
