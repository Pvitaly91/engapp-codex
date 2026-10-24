<?php

/** Finite M87 anonymous loopback SSR profile; no bootstrap/SQL and no browser-interaction claim. */
require __DIR__.'/frozen-readonly-profile.php';
require_once __DIR__.'/m87-live-contract.php';
set_error_handler(static function(int $severity,string $message):never{throw new RuntimeException('M87 diagnostic warning: '.$message);});
$setup=<<<'PHP'
$mistakeContract=M87LiveDiagnosticContract::assertMistakeNotes($master['lessons']);
$contractPath=$root.'/tools/diagnostics/m87-live-contract.php';$inputs[$contractPath]=$fileHash($contractPath);
foreach($master['lessons'] as $lesson){if(($lesson['details']??null)!==[]){throw new RuntimeException('M87 zero details only; keyboard interaction is not applicable, not a PASS.');}}
$editorialRelative='docs/content/m87-continuous-completion-past-simple.editorial.v1.json';$editorialPath=$root.'/'.$editorialRelative;$checksPath=$root.'/'.Package::CHECKSUMS;
if(!hash_equals(Package::CHECKSUMS_SHA,$fileHash($checksPath))){throw new RuntimeException('M87 frozen checksums differ.');}
$checks=json_decode(file_get_contents($checksPath),true,flags:JSON_THROW_ON_ERROR);$editorialPins=array_values(array_filter($checks['artifacts'],static fn($r)=>($r['path']??null)===$editorialRelative));
if(count($editorialPins)!==1||preg_match('/^[a-f0-9]{64}$/D',$editorialPins[0]['sha256']??'')!==1||!hash_equals($editorialPins[0]['sha256'],$fileHash($editorialPath))){throw new RuntimeException('M87 editorial checksum binding differs.');}
$editorial=json_decode(file_get_contents($editorialPath),true,flags:JSON_THROW_ON_ERROR);$prospective=$master['editorial_review']['expected_http_metadata']??null;
if(!is_array($prospective)||$prospective!==($editorial['expected_http_metadata']??null)){throw new RuntimeException('M87 frozen master/editorial metadata projection differs.');}
$metadataPath=$private.'/prospective-metadata-v1.json';
$expectedMetadataBytes=json_encode($prospective,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";$metadataSha=hash('sha256',$expectedMetadataBytes);
if(!hash_equals($metadataSha,$fileHash($metadataPath))||file_get_contents($metadataPath)!==$expectedMetadataBytes
 ||($prospective['independent_before_sha256']??null)!==M87ContentPatch::BEFORE_SHA
 ||array_column($prospective['metadata']??[],'identity')!==Package::OWNERS
 ||($prospective['application_bootstrapped']??null)!==false||($prospective['working_env_loaded']??null)!==false
 ||array_map(static fn($key)=>$prospective[$key]??null,['db_connections','db_queries','db_writes','http_requests'])!==[0,0,0,0]){throw new RuntimeException('M87 independent frozen prospective metadata differs.');}
$projectionName=$prospective['projection_file']??null;$projectionSha=$prospective['projection_sha256']??null;
if(!is_string($projectionName)||preg_match('/^author-projection-v[1-9][0-9]*\.json$/D',$projectionName)!==1
 ||!is_string($projectionSha)||preg_match('/^[a-f0-9]{64}$/D',$projectionSha)!==1||!hash_equals($projectionSha,$fileHash($private.'/'.$projectionName))){throw new RuntimeException('M87 frozen metadata author lineage differs.');}
$inputs[$private.'/'.$projectionName]=$projectionSha;
$registryPath=$private.'/registry-before-sentence-builders-v1.json';$registrySha='b682347510684a9423a77590b67a75947667e96eca4f1fcbad3538358a46fb2f';
if(!hash_equals($registrySha,$fileHash($registryPath))){throw new RuntimeException('M87 prior211 registry differs.');}
$priorRegistry=json_decode(file_get_contents($registryPath),true,flags:JSON_THROW_ON_ERROR);
if(count($priorRegistry['rows']??[])!==211||($priorRegistry['failed']??null)!==0){throw new RuntimeException('M87 exact prior211 registry population differs.');}
$controlOwners=['Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousFormsTheorySeeder',...App\Support\M86PresentSimpleContinuousBasics::OWNERS];$controls=[];
$controlUrls=['http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms'];
foreach(App\Support\M86PresentSimpleContinuousBasics::OWNERS as $n=>$_){$controlUrls[]='http://gramlyze.loc/theory/'.implode('/',App\Support\M86PresentSimpleContinuousBasics::ANCESTRIES[$n]).'/'.App\Support\M86PresentSimpleContinuousBasics::SLUGS[$n];}
foreach($controlOwners as $n=>$owner){$found=array_values(array_filter($priorRegistry['rows'],static fn($r)=>$r['identity']===$owner));if(count($found)!==1||$found[0]['errors']!==[]||$found[0]['http_status']!==200||$found[0]['url']!==$controlUrls[$n]){throw new RuntimeException('M87 reference/M86 registry control missing.');}$controls[]=$found[0];}
$navAllowed=[];foreach($master['lessons'] as $n=>$lesson){$nav=$lesson['blocks'][Package::NAVIGATION_SOURCE_INDICES[$n]]['data'];if(count($nav['items'])!==4){throw new RuntimeException('M87 exact four-item footer source differs.');}foreach($nav['items'] as $item){$navAllowed[]='http://gramlyze.loc'.$item['url'];}}
$navAllowed=array_values(array_unique($navAllowed));$expectedNav=[];
foreach(['present-continuous','past-simple'] as $tense){foreach(['forms','negatives','questions','time-expressions'] as $suffix){$expectedNav[]='http://gramlyze.loc/theory/tenses/'.$tense.'/'.$tense.'-'.$suffix;}}
$navSorted=$navAllowed;$expectedSorted=$expectedNav;sort($navSorted);sort($expectedSorted);
if($navSorted!==$expectedSorted){throw new RuntimeException('M87 exact eight full-ancestry footer destinations differ.');}
$relatedUrl='http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia';$stativeUrl='http://gramlyze.loc/theory/tenses/stative-verbs';
$navigationUrls=[...$navAllowed,$stativeUrl,$relatedUrl];
$allowedUrls=array_values(array_unique([...$navigationUrls,...array_column($controls,'url')]));
if(count($navigationUrls)!==10||count(array_unique($navigationUrls))!==10||count($allowedUrls)!==15){throw new RuntimeException('M87 finite ten-navigation/fifteen-HTTP allowlist differs.');}
foreach($patch['targets'] as $target){if(json_decode(file_get_contents($root.'/'.$target['path']),true,flags:JSON_THROW_ON_ERROR)!==$target['after']){throw new RuntimeException('M87 canonical active definition differs.');}}
$inputs[$editorialPath]=$editorialPins[0]['sha256'];$inputs[$checksPath]=Package::CHECKSUMS_SHA;$inputs[$metadataPath]=$metadataSha;$inputs[$registryPath]=$registrySha;
foreach($prospective['native_policy_pins'] as $path=>$sha){if(!hash_equals($sha,$fileHash($root.'/'.$path))){throw new RuntimeException('M87 native metadata policy changed.');}$inputs[$root.'/'.$path]=$sha;}
$nativeConfig=static function(DOMElement $node):array{
 $value=$node->getAttribute('x-data');$prefix="theoryPracticeSet(JSON.parse('";$suffix="'))";
 if(!str_starts_with($value,$prefix)||!str_ends_with($value,$suffix)){throw new RuntimeException('M87 native config shape differs.');}
 $literal=substr($value,strlen($prefix),-strlen($suffix));if(str_contains($literal,'"')||str_contains($literal,"'")){throw new RuntimeException('M87 config is not an escaped literal.');}
 $json=json_decode('"'.$literal.'"',true,flags:JSON_THROW_ON_ERROR);$data=is_string($json)?json_decode($json,true,flags:JSON_THROW_ON_ERROR):null;
 if(!is_array($data)){throw new RuntimeException('M87 config is not an object.');}return $data;
};
$parseDocument=static function(string $html):DOMDocument{$doc=new DOMDocument('1.0','UTF-8');$old=libxml_use_internal_errors(true);try{$loaded=$doc->loadHTML('<?xml encoding="UTF-8">'.M87LiveDiagnosticContract::prepareHtmlForDom($html),LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($old);}if(!$loaded){throw new RuntimeException('M87 immutable BEFORE HTML cannot be parsed.');}return $doc;};
$priorC=array_values(array_filter($before['http'],static fn($r)=>$r['identity']===Package::OWNERS[2]&&$r['kind']==='theory'));
if(count($priorC)!==1||$priorC[0]['status']!==200){throw new RuntimeException('M87 independent C native-config HTML absent.');}
$priorCPath=$private.'/'.$priorC[0]['file'];if(!hash_equals($priorC[0]['sha256'],$fileHash($priorCPath))){throw new RuntimeException('M87 original C HTML bytes differ.');}
$priorPractice=array_values(array_filter($before['pages'][Package::OWNERS[2]]['blocks'],static fn($r)=>$r['locale']==='uk'&&$r['type']==='practice-set'));
if(count($priorPractice)!==1||$priorPractice[0]['uuid']!==Package::EXISTING_PRACTICE_UUIDS[2]){throw new RuntimeException('M87 prior C practice identity differs.');}
$priorCDom=$parseDocument(file_get_contents($priorCPath));$priorCXp=new DOMXPath($priorCDom);$priorFactories=$priorCXp->query('//*[@id="block-'.(int)$priorPractice[0]['id'].'"]//*[@x-data and starts-with(@x-data,"theoryPracticeSet(")]');
if($priorFactories->length!==1){throw new RuntimeException('M87 original C native factory is not exact.');}$beforeConfig=$nativeConfig($priorFactories->item(0));$inputs[$priorCPath]=$priorC[0]['sha256'];
$secondaryProofPath=$private.'/secondary-navigation-review-v1.json';$secondaryProofSha='5422133ff19278a6e7d78e555d1c46980825854f2f0bbacfe0378eb5fccbdfe1';
if(!hash_equals($secondaryProofSha,$fileHash($secondaryProofPath))){throw new RuntimeException('M87 independent secondary-navigation proof differs.');}
$secondaryProof=json_decode(file_get_contents($secondaryProofPath),true,flags:JSON_THROW_ON_ERROR);$secondarySlots=[0=>7,4=>8];
$secondaryExpected=['title'=>'Пов’язана тема','items'=>[['label'=>'Питальні речення та заперечення','url'=>'/theory/pytalni-rechennia-ta-zaperechennia','current'=>false]]];
$secondaryViewPath=$root.'/resources/views/theory/show.blade.php';$secondaryViewSha='3745e0b97a025f6c686434972f603a61ba71e5cfdafe83bada5228c58d68d87c';
if(($secondaryProof['independent_before_sha256']??null)!==M87ContentPatch::BEFORE_SHA
 ||($secondaryProof['secondary_navigation_visible_acceptance']??null)!==false
 ||array_column($secondaryProof['targets']??[],'identity')!==[Package::OWNERS[0],Package::OWNERS[4]]
 ||array_column($secondaryProof['targets'],'source_index')!==[7,8]
 ||array_column($secondaryProof['source_sql']??[],'identity')!==[Package::OWNERS[0],Package::OWNERS[4]]
 ||array_column($secondaryProof['source_sql'],'source_index')!==[7,8]
 ||array_column($secondaryProof['source_sql'],'data')!==[$secondaryExpected,$secondaryExpected]
 ||array_column($secondaryProof['source_sql'],'source_sql_exact')!==[true,true]
 ||($secondaryProof['view_path']??null)!=='resources/views/theory/show.blade.php'||($secondaryProof['view_sha256']??null)!==$secondaryViewSha
 ||($before['visual_hashes']['resources/views/theory/show.blade.php']??null)!==$secondaryViewSha||!hash_equals($secondaryViewSha,$fileHash($secondaryViewPath))){throw new RuntimeException('M87 finite two-navigation source/view/BEFORE lineage differs.');}
$secondaryFacts=static function(DOMXPath $xp,DOMNode $main)use($secondaryExpected):array{
 $parts=[];foreach($xp->query('.//text()[not(ancestor::script) and not(ancestor::style)]',$main) as $t){$parts[]=$t->nodeValue;}$text=trim(preg_replace('/\s+/u',' ',implode(' ',$parts)));
 $footers=$xp->query('.//section[contains(concat(" ",normalize-space(@class)," ")," theory-content-blocks ")]/nav',$main);$titles=[];
 foreach($footers as $footer){$p=$xp->query('./p',$footer);$titles[]=$p->length===1?trim($p->item(0)->textContent):null;}$links=[];
 foreach($xp->query('.//a[@href]',$main) as $a){if(in_array($a->getAttribute('href'),[$secondaryExpected['items'][0]['url'],'http://gramlyze.loc'.$secondaryExpected['items'][0]['url'],'https://gramlyze.com'.$secondaryExpected['items'][0]['url']],true)){$links[]=['href'=>$a->getAttribute('href'),'label'=>trim(preg_replace('/\s+/u',' ',$a->textContent))];}}
 return ['main_footer_count'=>$footers->length,'main_footer_titles'=>$titles,'secondary_title_in_main_text'=>str_contains($text,$secondaryExpected['title']),'secondary_label_in_main_text'=>str_contains($text,$secondaryExpected['items'][0]['label']),'secondary_links_in_main'=>$links];
};
$secondaryBeforeHttp=[];$secondaryBeforeFacts=[];
foreach($secondarySlots as $ownerIndex=>$slot){
 $owner=Package::OWNERS[$ownerIndex];$proofIndex=$ownerIndex===0?0:1;$record=$secondaryProof['targets'][$proofIndex];$sqlRecord=$secondaryProof['source_sql'][$proofIndex];
 $oldSource=$sourceBefore['targets'][$ownerIndex]['before']['page']['blocks'][$slot]['body'];
 if(hash('sha256',$oldSource)!==$sqlRecord['source_body_sha256']||json_decode($oldSource,true,flags:JSON_THROW_ON_ERROR)!==$secondaryExpected){throw new RuntimeException('M87 exact original secondary source body differs.');}
 $httpRows=array_values(array_filter($before['http'],static fn($r)=>$r['identity']===$owner&&$r['kind']==='theory'));
 if(count($httpRows)!==1||$httpRows[0]['status']!==200||$httpRows[0]['file']!==$record['independent_before_html_file']||$httpRows[0]['sha256']!==$record['independent_before_html_sha256']){throw new RuntimeException('M87 secondary-navigation original HTML provenance differs.');}
 $secondaryBeforeHttp[$owner]=$httpRows[0];$path=$private.'/'.$httpRows[0]['file'];if(!hash_equals($httpRows[0]['sha256'],$fileHash($path))){throw new RuntimeException('M87 original secondary HTML bytes differ.');}
 $doc=$parseDocument(file_get_contents($path));$sx=new DOMXPath($doc);$main=$sx->query('//*[@data-theory-main]');if($main->length!==1){throw new RuntimeException('M87 original secondary main absent.');}
 $expected=['main_footer_count'=>1,'main_footer_titles'=>[$ownerIndex===0?'Уроки Present Continuous':'Уроки Past Simple'],'secondary_title_in_main_text'=>false,'secondary_label_in_main_text'=>false,'secondary_links_in_main'=>[]];
 $facts=$secondaryFacts($sx,$main->item(0));
 if($facts!==$expected||$record['before']!==$expected||$record['fresh_pre_apply']!==$expected){throw new RuntimeException('M87 exact pre-existing secondary omission not proved.');}
 $secondaryBeforeFacts[$owner]=$facts;$inputs[$path]=$httpRows[0]['sha256'];
}
$inputs[$secondaryProofPath]=$secondaryProofSha;$inputs[$secondaryViewPath]=$secondaryViewSha;
$ownGraded=0;$fullInputCount=0;$ungradedDemos=0;$rephraseInputCount=0;$linkedIds=[];$footerSourceItems=0;$footerNoncurrentLinks=0;$secondaryNavigationResults=[];$mistakeNotesChecked=0;$pairedMistakeFieldsChecked=0;$mistakeNoteResults=[];$httpRequestCount=0;$stativeLinkSSRCount=0;
$nonRenderedSelectPrompts=[];$practiceTaskCounts=['selects'=>0,'choices'=>0,'inputs'=>0,'rephrase'=>0];
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
            if(isset($secondarySlots[$i])&&$slot===$secondarySlots[$i]){
                $same($data,$secondaryExpected,'Exact retained A/E second-navigation author data');
                $oldSecondary=json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);$same($oldSecondary,$secondaryExpected,'Independent original secondary source data');
                $sourceSecondary=json_decode($target['after']['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);$sourceBasic=$sourceSecondary;unset($sourceBasic['m87_native_v1']);$same($sourceBasic,$secondaryExpected,'Exact current secondary source data');
                $same($sourceSecondary['m87_native_v1']??null,['key'=>$author['key'],'detail_keys'=>[]],'Exact owned native marker');
                $actualSecondaryFacts=$secondaryFacts($xp,$main);$same($actualSecondaryFacts,$secondaryBeforeFacts[$owner],'Finite unchanged A/E secondary-footer omission');
                $secondaryNavigationResults[]=['identity'=>$owner,'source_index'=>$slot,'source_data_exact'=>true,'proof_file'=>'secondary-navigation-review-v1.json','proof_sha256'=>$secondaryProofSha,'independent_before_html_sha256'=>$secondaryBeforeHttp[$owner]['sha256'],'universal_view_before_and_after_sha256'=>$secondaryViewSha,'actual_after_html_sha256'=>hash('sha256',$html),'server_HTML'=>$actualSecondaryFacts,'secondary_navigation_visible_acceptance'=>false,'classification'=>'Pre-existing source-only second footer remains absent in SSR; no visible acceptance, source deletion or view fix is claimed.'];
                continue;
            }
            $same($slot,Package::NAVIGATION_SOURCE_INDICES[$i],'Exact primary-footer source slot');$same(count($data['items']),4,'Four primary footer source items');
            $footerNodes=$xp->query('.//section[contains(concat(" ",normalize-space(@class)," ")," theory-content-blocks ")]/nav',$main);$same($footerNodes->length,1,'One native primary footer');
            $footerNode=$footerNodes->item(0);$footerText=$learningText($xp,$footerNode);$contains($footerText,$data['title'],$label.'/owned footer title');
            $sourceLinks=0;foreach($data['items'] as $item){
                $contains($footerText,$item['label'],$label.'/owned item label');if($item['current']){continue;}
                if(!in_array('http://gramlyze.loc'.$item['url'],$navAllowed,true)){throw new RuntimeException('M87 footer destination outside exact verified eight paths.');}
                $matches=[];foreach($xp->query('.//a[@href]',$footerNode) as $link){if($link->getAttribute('href')===$item['url']&&$plain($link->textContent)===$plain($item['label'])){$matches[]=$link;}}
                $same(count($matches),1,'Exact owned noncurrent footer href/label');$navLinks[]='http://gramlyze.loc'.$item['url'];++$sourceLinks;
            }
            $same($sourceLinks,3,'Three primary footer noncurrent destinations');$same($xp->query('.//a[@href]',$footerNode)->length,3,'No extra primary footer links');
            $stativeLinks=$xp->query('.//a[@href="/theory/tenses/stative-verbs"]',$main);
            $same($stativeLinks->length,$i===1?1:0,'Exact owned stative bridge source link count');
            if($i===1){$same($plain($stativeLinks->item(0)->textContent),'Stative Verbs','Exact stative bridge label');++$stativeLinkSSRCount;}
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
            $beforePractice=$i>=2?json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][6]['body'],true,flags:JSON_THROW_ON_ERROR):[];
            $practiceFacts=M87LiveDiagnosticContract::assertPracticeDom($xp,$node,$data,$config,$beforeConfig,$i,$learningText,$plain,$contains,$same,$beforePractice);
            $ownGraded+=$practiceFacts['graded'];$fullInputCount+=$practiceFacts['full_inputs'];$ungradedDemos+=$practiceFacts['ungraded_demos'];$rephraseInputCount+=$practiceFacts['rephrase_inputs'];foreach($practiceFacts['task_counts'] as $group=>$count){$practiceTaskCounts[$group]+=$count;}
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
                foreach(array_count_values($payload[$q]['correct_tokens']) as $token=>$count){if(($available[$token]??0)<$count){throw new RuntimeException('M87 widget loses repeated correct token instance.');}}
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
                if(!is_string($item['note']??null)||trim($item['note'])===''||array_key_exists('hint',$item)){throw new RuntimeException('M87 each correction requires its own note, without legacy hint aliases.');}
                $point=$points->item($n);$pointText=$learningText($xp,$point);foreach(['label','title','wrong_en','wrong_ua','right_en','right_ua','note'] as $field){$contains($pointText,$item[$field],$label.'/items/'.$n.'/'.$field);}
                $paired=M87LiveDiagnosticContract::assertPairedMistakeDom($xp,$point,$item,$plain);$pairedMistakeFieldsChecked+=4;
                $noteNodes=$xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-note ")]',$point);$same($noteNodes->length,1,'Exactly one owned canonical mistake note');$contains($learningText($xp,$noteNodes->item(0)),$item['note'],$label.'/items/'.$n.'/owned note');++$mistakeNotesChecked;
                $mistakeNoteResults[]=['identity'=>$owner,'source_index'=>$slot,'item_index'=>$n,'note_sha256'=>hash('sha256',$item['note']),'owned_note_in_SSR'=>true,'single_note_node'=>true,'paired_EN_UK_DOM'=>$paired];
            }
PHP;
$controlsSnippet=<<<'PHP'
$same([$ownGraded,$fullInputCount,$ungradedDemos,$rephraseInputCount,count($linkedIds),count(array_unique($linkedIds))],[57,16,4,12,30,30],'Exact own/full-input/demo/rephrase/widget populations');
$expectedMetadataPositions=[];foreach([2,3,4,5] as $ownerIndex){foreach([0,1,2] as $taskIndex){$expectedMetadataPositions[]=[Package::OWNERS[$ownerIndex],'selects/'.$taskIndex];}}
$same(array_map(static fn($row)=>[$row['identity'],$row['control']],$nonRenderedSelectPrompts),$expectedMetadataPositions,'Exactly twelve independent BEFORE-bound generic select metadata fields, not visible stimuli');
$same([$footerSourceItems,$footerNoncurrentLinks,count($secondaryNavigationResults)],[24,18,2],'All primary footer source items/links and finite A/E omissions');
$same(array_values($practiceTaskCounts),[16,13,16,16],'Exact select/meaning/input/rephrase population including four demos');
$same($stativeLinkSSRCount,1,'Exact owned B stative bridge href/label');
$same([$mistakeNotesChecked,count($mistakeNoteResults),$pairedMistakeFieldsChecked],[18,18,72],'Eighteen actual owned mistake notes and paired EN/UK fields');
$mistakeContract['paired_EN_UK_fields_SSR_checked']=$pairedMistakeFieldsChecked;$controlResults=[];
foreach($controls as $n=>$control){
 [$body,$status,$robots]=$http($control['url']);$doc=$parseDocument($body);$cx=new DOMXPath($doc);$same($cx->query('//h1')->length,1,'Reference H1 count');
 $h1=$plain($cx->query('//h1')->item(0)->textContent);$same($h1,$n===0?'Past Perfect Continuous: Forms and Use':App\Support\M86PresentSimpleContinuousBasics::RENDERED_TITLES[$n-1],'Exact reference/M86 H1');
 $same($cx->query('//*[@data-sentence-builder]')->length,1,'Reference one widget');$payload=Acceptance::payload($body,$control['practice_uuid']);$same(is_array($payload)?count($payload):null,5,'Reference five-question payload');
 $same(array_column($payload,'id'),$control['selected_ids'],'Reference prior selected IDs/order');$same(array_map(static fn($q)=>(string)$q['type'],$payload),array_fill(0,5,'4'),'Reference type4');
 $controlResults[]=['identity'=>$control['identity'],'url'=>$control['url'],'status'=>$status,'h1'=>$h1,'html_sha256'=>hash('sha256',$body),'prior_registry_sha256'=>$registrySha,'practice_uuid'=>$control['practice_uuid'],'selected_ids'=>$control['selected_ids'],'native_widget_count'=>1,'whole_html_equality_claimed'=>false,'scope'=>'Fresh GET/H1/widget identities only. All211 prior-source/SQL/consumer/registry regressions are separate gates.'];
}
[$relatedBody,$relatedStatus]=$http($relatedUrl);$relatedDoc=$parseDocument($relatedBody);$relatedXp=new DOMXPath($relatedDoc);$same($relatedXp->query('//h1')->length,1,'Related category H1 count');
$same($plain($relatedXp->query('//h1')->item(0)->textContent),'Питальні речення та заперечення','Retained related-category H1');
$same($relatedXp->query('//link[@rel="canonical"]/@href')->item(0)?->nodeValue,'https://gramlyze.com/theory/pytalni-rechennia-ta-zaperechennia','Retained related-category canonical string, no production GET');
$relatedResult=['url'=>$relatedUrl,'status'=>$relatedStatus,'html_sha256'=>hash('sha256',$relatedBody),'secondary_links_preserved_in_A_E_source'=>true,'visible_in_A_E'=>false];
[$stativeBody,$stativeStatus]=$http($stativeUrl);$stativeDoc=$parseDocument($stativeBody);$stativeXp=new DOMXPath($stativeDoc);
$same($stativeXp->query('//h1')->length,1,'Stative bridge H1 count');$same($plain($stativeXp->query('//h1')->item(0)->textContent),'Stative Verbs','Exact stative bridge H1');
$same($stativeXp->query('//link[@rel="canonical"]/@href')->item(0)?->nodeValue,'https://gramlyze.com/theory/tenses/stative-verbs','Stative canonical string, no production GET');
$stativeResult=['url'=>$stativeUrl,'status'=>$stativeStatus,'h1'=>'Stative Verbs','html_sha256'=>hash('sha256',$stativeBody),'owned_B_href_label_SSR'=>true];
$same($httpRequestCount,15,'Exact fifteen unique local HTTP GET count');
foreach ($inputs as $path => $sha) {
PHP;
$replacements=[
 ['M53PassiveConstructionsPackage','M87ContinuousCompletionPastSimple',1],
 ['M53BuilderHintPresentation','M87BuilderSelectionPresentation',5],
 ['M53','M87',7],
 ['m53','m87',3],
 ['foreach (Package::profile()[\'artifacts\'] as $path => $sha) { $inputs[$root.\'/\'.$path] = $sha; }','foreach (Package::profile()[\'artifacts\'] as $path => $sha) { $inputs[$root.\'/\'.$path] = $sha; }
'.$setup,1],
 ['$http = static function (string $url): array {','$http = static function (string $url) use ($allowedUrls, &$httpRequestCount): array {',1],
 ['preg_match(\'~^http://gramlyze\\.loc/theory/passive-voice/theory-passive-voice-(?:causative|get-passive|impersonal-passive)$~D\', $url) !== 1','!in_array($url,$allowedUrls,true)',1],
 ['HTTP target is outside the exact three local lessons.','HTTP target is outside the exact M87 targets/navigation and seven read-only controls, one stative bridge and related category.',1],
 ['$curl = curl_init($url); $robots = null;','++$httpRequestCount; $curl = curl_init($url); $robots = null;',1],
 ['finally { curl_close($curl); }','finally { unset($curl); }',1],
 ['$dom->loadHTML(\'<?xml encoding="UTF-8">\'.$html, LIBXML_NONET)','$dom->loadHTML(\'<?xml encoding="UTF-8">\'.M87LiveDiagnosticContract::prepareHtmlForDom($html), LIBXML_NONET)',1],
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
 ['\'pages\' => $results, \'SSR_learning_fields_checked\' => $fieldCount,','\'pages\'=>$results,\'read_only_reference_controls\'=>$controlResults,\'related_category\'=>$relatedResult,\'stative_bridge\'=>$stativeResult,\'HTTP_GET_count\'=>$httpRequestCount,\'SSR_learning_fields_checked\'=>$fieldCount,\'own_graded_tasks\'=>$ownGraded,\'own_group_task_counts\'=>$practiceTaskCounts,\'full_sentence_inputs\'=>$fullInputCount,\'ungraded_demos\'=>$ungradedDemos,\'scored_rephrase_inputs\'=>$rephraseInputCount,\'nonrendered_select_prompt_metadata\'=>$nonRenderedSelectPrompts,\'nonrendered_select_prompt_count\'=>count($nonRenderedSelectPrompts),\'linked_questions\'=>count($linkedIds),\'primary_footer_source_items_checked\'=>$footerSourceItems,\'primary_footer_noncurrent_links_checked\'=>$footerNoncurrentLinks,\'secondary_navigation\'=>$secondaryNavigationResults,\'secondary_navigation_visible_acceptance\'=>false,\'mistake_note_contract\'=>$mistakeContract,\'mistake_notes_SSR_checked\'=>$mistakeNotesChecked,\'mistake_note_results\'=>$mistakeNoteResults,\'prior_registry_population\'=>211,\'whole_prior211_regression_claimed\'=>false,\'detail_keyboard\'=>\'not_applicable_zero_details\',',1],
 ['\'status\' => \'read-only-local-http-ssr-accepted\'','\'status\' => \'accepted-with-pre-existing-secondary-navigation-omission\'',1],
 ['\'browser layout or visibility\', \'client Check/Reset/token interaction\'','\'browser layout or visibility\', \'A source index7 and E source index8 secondary navigation are absent in independent BEFORE and current SSR; no visible acceptance\', \'client Check/Reset/token interaction\'',1],
 ['navigation destination availability beyond the exact three requested URLs','navigation destination browser visibility or interaction; direct local HTTP availability of all ten authored destinations is checked separately in this receipt',1],
 ['\'SQL postconditions (separate diagnostic)\'','\'all211 prior-source/SQL/old-consumer/registry regressions (separate diagnostics)\'',1],
];
if(($argv[1]??null)==='--profile-check-only'){
 $bytes=file_get_contents(__DIR__.'/m53-live-pages.php');
 if(!hash_equals('66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',hash('sha256',$bytes))){throw new RuntimeException('M87 immutable M53 primitive differs.');}
 foreach($replacements as $i=>[$old,$new,$count]){$n=substr_count($bytes,$old);if($n!==$count){throw new RuntimeException('M87 literal count differs at '.$i.': '.$n.' expected '.$count);}$bytes=str_replace($old,$new,$bytes);}
 token_get_all($bytes,TOKEN_PARSE);
 echo json_encode(['profile'=>'M87 live pages','replacements'=>count($replacements),'parsed'=>true,'HTTP_requests'=>0,'SQL_access'=>false])."\n";return;
}
runFrozenReadOnlyProfile('m53-live-pages.php','66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',$replacements);
