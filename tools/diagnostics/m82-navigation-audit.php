<?php

/** Explicit CLI-only M82 HTTP/SSR profile; exclusive private receipt, no application/SQL/content writes. */
require __DIR__.'/frozen-readonly-profile.php';
set_error_handler(static function(int $severity,string $message):bool{
    if(in_array($severity,[E_WARNING,E_NOTICE],true)){throw new RuntimeException('M82 diagnostic PHP warning/notice; incomplete acceptance: '.$message);}
    return false;
},E_WARNING|E_NOTICE);
// Freeze the independently projected native metadata receipt before any HTTP acceptance.
$prospectiveFile='prospective-metadata-v2.json';
$prospectiveSha='2e9d800395321e18a6e2f6c25d6f9dca28d7cd59c6a313d0f80690563835e9c3';
$metadataBootstrap=<<<'PHP'
$metadataPath=$private.'/'.$profileContext['prospective_file'];
if(preg_match('/^prospective-metadata-v[1-9][0-9]*\\.json$/D',$profileContext['prospective_file'])!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$profileContext['prospective_sha256'])!==1
    ||!hash_equals($profileContext['prospective_sha256'],$fileHash($metadataPath))){throw new RuntimeException('M82 reviewed prospective metadata is not frozen.');}
$prospective=json_decode(file_get_contents($metadataPath),true,flags:JSON_THROW_ON_ERROR);
if(M82ContentPatch::BEFORE_SHA!=='94312691251f96433ee72c3fe544db71767c795495874af3ba1c80c2cbb49c62'
    ||Package::BINDINGS_SHA!=='a998208e041a2d231deff859cc5ec37e72800a7a6c79c61b64deef2b67ad4fc0'
    ||M82BuilderSelectionPresentation::AUDIT_SHA!=='c99047c5719e2f7662cb8dd356f90d9aba68a18d2edfa1934024f5ac9f1e1dc2'
    ||($prospective['independent_before_sha256']??null)!==M82ContentPatch::BEFORE_SHA
    ||array_column($prospective['metadata']??[],'identity')!==Package::OWNERS
    ||($prospective['application_bootstrapped']??null)!==false||($prospective['working_env_loaded']??null)!==false
    ||($prospective['db_connections']??null)!==0||($prospective['db_queries']??null)!==0
    ||($prospective['db_writes']??null)!==0||($prospective['http_requests']??null)!==0){
    throw new RuntimeException('M82 prospective metadata or exact reviewed native bindings differ.');
}
$navigationUrls=[
    4=>['/theory/basic-grammar/verb-to-be/verb-to-be-present','/theory/basic-grammar/verb-to-be/there-is-there-are',
        '/theory/some-any/theory-some-any-things','/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative'],
    5=>['/theory/tenses/present-perfect/present-perfect-forms','/theory/conditionals/first-conditional',
        '/theory/reported-speech/reported-statements','/theory/conditionals/second-conditional'],
];
$navigationLabels=[
    4=>['Verb to be — Present','There is / There are','Some / Any — Things','Comparative vs Superlative','A1 Mixed Revision'],
    5=>['Present Perfect Forms','First Conditional','Reported Statements','Second Conditional','A2 Mixed Revision'],
];
$navigationAdditions=0;
foreach([4,5]as $n){
    $oldNavigation=json_decode($sourceBefore['targets'][$n]['before']['page']['blocks'][3]['body'],true,flags:JSON_THROW_ON_ERROR);
    $newNavigation=$master['lessons'][$n]['blocks'][3]['data'];
    if(array_column($oldNavigation['items']??[],'label')!==$navigationLabels[$n]
        ||array_column($newNavigation['items']??[],'label')!==$navigationLabels[$n]
        ||array_diff_key($newNavigation,['items'=>true])!==array_diff_key($oldNavigation,['items'=>true])
        ||array_column($oldNavigation['items'],'current')!==[false,false,false,false,true]
        ||array_column($newNavigation['items'],'current')!==[false,false,false,false,true]){
        throw new RuntimeException('M82 approved footer URL change must preserve all five labels/order/current/title.');
    }
    foreach($newNavigation['items']as $index=>$item){
        $withoutUrl=$item;unset($withoutUrl['url']);
        if($withoutUrl!==$oldNavigation['items'][$index]||array_key_exists('url',$oldNavigation['items'][$index])
            ||($index<4?($item['url']??null)!==$navigationUrls[$n][$index]:array_key_exists('url',$item))){
            throw new RuntimeException('M82 only the eight exact independently confirmed footer data URLs are approved.');
        }
        if($index<4){++$navigationAdditions;}
    }
}
if($navigationAdditions!==8){throw new RuntimeException('M82 exact eight footer URL additions differ.');}

$navigationReviewPath=$root.'/docs/content/m82-navigation-review.v1.json';
$navigationReviewSha='0e45611733b84425689238b5068cb8d359c75b05b5ffa7295cb5fbf921aff63d';
if(!hash_equals($navigationReviewSha,$fileHash($navigationReviewPath))){throw new RuntimeException('M82 independent approved navigation source/SQL/GET mapping bytes differ.');}
$navigationReview=json_decode(file_get_contents($navigationReviewPath),true,flags:JSON_THROW_ON_ERROR);
$navigationDestinationExpectations=[];$navigationProofSeen=[];
foreach($navigationReview['targets']??[]as $record){
    $n=array_search($record['owner']??null,Package::OWNERS,true);$index=$record['item_index']??null;
    if(!in_array($n,[4,5],true)||!is_int($index)||$index<0||$index>3||isset($navigationProofSeen[$n.':'.$index])
        ||($record['source_index']??null)!==3||($record['current']??null)!==false
        ||($record['label']??null)!==$navigationLabels[$n][$index]||($record['url']??null)!==$navigationUrls[$n][$index]
        ||($record['http_status']??null)!==200||!is_int($record['target_page_id']??null)
        ||!is_string($record['h1']??null)||trim($record['h1'])===''||preg_match('/^[a-f0-9]{64}$/D',$record['html_sha256']??'')!==1){
        throw new RuntimeException('M82 independent exact eight footer destination proof differs.');
    }
    $navigationProofSeen[$n.':'.$index]=true;$navigationDestinationExpectations['http://gramlyze.loc'.$record['url']]=$record;
}
if(count($navigationProofSeen)!==8){throw new RuntimeException('M82 exact eight independently confirmed footer destinations are missing.');}

$profileBindings=Package::profile()['bindings'];$reviewedMetadataTransitions=[];$navigationExpectedCounts=[6,6,6,6,5,5];$navigationItemCount=0;
$capturedLabelNavigation=[];$capturedHtmlInputs=[];$repairedFragmentPlaceholders=[];$authoredBodyLinks=[];
$navigationPlain=static fn(string $text):string=>trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($text),ENT_QUOTES|ENT_HTML5,'UTF-8')));
foreach(Package::OWNERS as $n=>$identity){
    $lesson=$master['lessons'][$n];$binding=$profileBindings[$n];$projected=$patch['targets'][$n]['after']['page']['blocks'];$pre=$prospective['metadata'][$n];
    if(($binding['page_id']??null)!==Package::PAGE_IDS[$n]||($pre['preserved_h1']??null)!==Package::RENDERED_TITLES[$n]
        ||($pre['local_url']??null)!==$lesson['local_url']||($pre['raw_title']??null)!==$lesson['raw_title']
        ||($pre['hero_intro_sha256']??null)!==hash('sha256',$lesson['blocks'][0]['data']['intro'])
        ||!is_array($pre['prospective_page_metadata']??null)||!is_string($pre['prospective_learning_resource_description']??null)
        ||$pre['prospective_learning_resource_description']===''){
        throw new RuntimeException('M82 independent pre-apply metadata no longer matches frozen hero/H1.');
    }
    if(preg_match('~<strong>(.*?)</strong>~su',$lesson['subtitle_html'],$title)!==1
        ||html_entity_decode(strip_tags($title[1]),ENT_QUOTES|ENT_HTML5,'UTF-8')!==Package::RENDERED_TITLES[$n]
        ||($before['pages'][$identity]['subtitle_extracted_title']??null)!==Package::RENDERED_TITLES[$n]){
        throw new RuntimeException('M82 strong subtitle title differs from independently captured H1.');
    }
    $continuing=Package::CONTINUING_COUNTS[$n];$practiceSlot=Package::PRACTICE_SOURCE_INDICES[$n];$slots=[];
    foreach($before['pages'][$identity]['blocks'] as $row){
        if($row['locale']==='uk'&&(int)$row['sort_order']>=1&&(int)$row['sort_order']<=$continuing){$slots[(int)$row['sort_order']-1]=$row;}
    }
    ksort($slots);$transitions=$binding['continuing_metadata']??[];
    if(array_keys($slots)!==range(0,$continuing-1)||array_column($slots,'level')!==$binding['continuing_levels']
        ||array_keys($transitions)!==($n<4?[1,2]:[])||count($lesson['blocks'])!==$continuing+1
        ||$practiceSlot!==$continuing||$lesson['blocks'][$practiceSlot]['level']!==Package::LEVELS[$n]){
        throw new RuntimeException('M82 actual continuing SQL levels/new practice level differ.');
    }
    foreach($transitions as $slot=>$transition){
        $source=$sourceBefore['targets'][$n]['before']['page']['blocks'][$slot];$old=$slots[$slot];$after=$projected[$slot];
        if($transition['column']!=='left'||$transition['before']['type']!=='box'
            ||$transition['after']!==['type'=>'usage-panels','heading'=>null,'level'=>null]
            ||$old['type']!=='box'||$old['heading']!==$source['heading']||$old['level']!==null||$old['column']!=='left'
            ||$transition['before']!==['type'=>$old['type'],'heading'=>$old['heading'],'level'=>$old['level']]
            ||$after['type']!=='usage-panels'||($after['heading']??null)!==null||($after['level']??null)!==null||$after['column']!=='left'){
            throw new RuntimeException('M82 one of eight reviewed box-to-native metadata transitions differs.');
        }
        $reviewedMetadataTransitions[]=['identity'=>$identity,'source_index'=>$slot,'before_type'=>'box','after_type'=>'usage-panels',
            'before_heading'=>$old['heading'],'after_heading'=>null,'column'=>'left','level'=>null];
    }
    foreach(['selects','choices','inputs'] as $group){foreach($lesson['blocks'][$practiceSlot]['data'][$group] as $task){
        if(array_key_exists('prompt',$task)){throw new RuntimeException('M82 criteria cannot rely on unsupported prompt.');}
    }}
    if(array_key_exists('intro',$lesson['blocks'][$practiceSlot]['data'])){throw new RuntimeException('M82 generic practice intro is not rendered.');}
    $walkLinks=static function(mixed $value)use(&$walkLinks,&$authoredBodyLinks,$n):void{
        if(is_array($value)){foreach($value as $child){$walkLinks($child);}return;}
        if(!is_string($value)){return;}
        preg_match_all('~href\s*=\s*["\']([^"\']+)["\']~iu',$value,$matches);
        foreach($matches[1] as $href){
            if(preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)+$~D',$href)!==1){throw new RuntimeException('M82 authored body link is outside exact local theory paths.');}
            $authoredBodyLinks[$n][]=$href;
        }
    };
    foreach($lesson['blocks'] as $block){$walkLinks($block['data']);}
    if($n<4){continue;}
    $source=$sourceBefore['targets'][$n]['before']['page']['blocks'][3];
    if(array_key_exists('preserved_label_navigation',$binding)){
        throw new RuntimeException('M82 approved data URLs must not rely on a native validator opt-in or resolver change.');
    }
    $httpBefore=array_values(array_filter($before['http'],static fn($record)=>($record['kind']??null)==='theory'&&($record['identity']??null)===$identity));
    if(count($httpBefore)!==1||preg_match('/^before-v1-http-[a-f0-9]{64}\.html$/D',$httpBefore[0]['file']??'')!==1){
        throw new RuntimeException('M82 label-only footer lacks one safe frozen HTTP input.');
    }
    $captured=$httpBefore[0];$capturedPath=$private.'/'.$captured['file'];
    if(!hash_equals($captured['sha256'],$fileHash($capturedPath))){throw new RuntimeException('M82 captured native resolver bytes differ.');}
    $capturedHtmlInputs[$capturedPath]=$captured['sha256'];
    $capturedDom=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
    try{$loaded=$capturedDom->loadHTML('<?xml encoding="UTF-8">'.file_get_contents($capturedPath),LIBXML_NONET);}
    finally{libxml_clear_errors();libxml_use_internal_errors($previous);}
    if(!$loaded){throw new RuntimeException('M82 captured footer HTML cannot be parsed.');}
    $capturedXp=new DOMXPath($capturedDom);$footers=$capturedXp->query('//*[@data-theory-main]//nav[./p and ./div/*[self::a or self::span]]');
    if($footers->length!==1){throw new RuntimeException('M82 captured label-only footer is ambiguous.');}
    $entries=$capturedXp->query('./div/*[self::a or self::span]',$footers->item(0));
    $data=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);
    if($entries->length!==5||count($data['items'])!==5){throw new RuntimeException('M82 captured label-only footer count differs.');}
    foreach($data['items'] as $index=>$item){
        $entry=$entries->item($index);$current=(bool)$item['current'];$href=$entry->hasAttribute('href')?$entry->getAttribute('href'):null;
        if(array_key_exists('url',$item)||$navigationPlain($entry->textContent)!==$navigationPlain($item['label'])
            ||$entry->tagName!==($current?'span':'a')||$href!==($current?null:'#')){
            throw new RuntimeException('M82 actual historical native resolver does not match finite label-only contract.');
        }
        $capturedLabelNavigation[$n][]=['label'=>$navigationPlain($item['label']),'current'=>$current,'tag'=>$entry->tagName,'href'=>$href];
    }
}
if(count($reviewedMetadataTransitions)!==8){throw new RuntimeException('M82 exact eight transition inventory differs.');}
$master['editorial_review']['pre_apply_metadata']=array_map(static fn($row)=>['identity'=>$row['identity'],
    'h1'=>$row['preserved_h1'],'expected_seo'=>$row['prospective_page_metadata'],
    'expected_learning_resource_description'=>$row['prospective_learning_resource_description']],$prospective['metadata']);
M82BuilderSelectionPresentation::validateAudit($audit,$patch,$contract);
$inputs=[$metadataPath=>$profileContext['prospective_sha256'],$beforePath=>M82ContentPatch::BEFORE_SHA,
    $auditPath=>M82BuilderSelectionPresentation::AUDIT_SHA,$root.'/'.Package::BINDINGS=>Package::BINDINGS_SHA,
    $navigationReviewPath=>$navigationReviewSha]+$capturedHtmlInputs;
PHP;
$allowlist=<<<'PHP'
$allowedUrls=array_fill_keys(array_column($patch['targets'],'local_url'),true);
if(count($allowedUrls)!==6){throw new RuntimeException('M82 exact six URL targets differ.');}
foreach($master['lessons'] as $n=>$lesson){foreach($lesson['blocks'] as $block){if($block['type']!=='navigation-chips'){continue;}
    foreach($block['data']['items'] as $index=>$item){
        if($n>=4&&$item['current']){
            if(array_key_exists('url',$item)){throw new RuntimeException('M82 current fifth navigation chip gained a URL.');}
            continue;
        }
        $valid=$n<4?preg_match('~^/theory/common-mistakes/[a-z0-9-]+$~D',$item['url']??'')===1
            :($item['url']??null)===$navigationUrls[$n][$index];
        if(!$valid){throw new RuntimeException('M82 frozen navigation contains an out-of-scope destination.');}
        $allowedUrls['http://gramlyze.loc'.$item['url']]=true;
    }
}}
foreach($authoredBodyLinks as $links){foreach($links as $href){$allowedUrls['http://gramlyze.loc'.$href]=true;}}
$http = static function (string $url) use($allowedUrls): array {
    if (!isset($allowedUrls[$url])) {
        throw new RuntimeException('HTTP target is outside the exact frozen M82 lesson/navigation/body-link allowlist.');
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
                foreach(array_count_values($expected['correct_tokens']) as $token=>$count){if(($available[$token]??0)<$count){throw new RuntimeException('M82 widget loses a repeated correct token instance.');}}
            }
PHP;
$fieldVisibility=<<<'PHP'
        if($type==='practice-set'){
            $same($slot,Package::PRACTICE_SOURCE_INDICES[$i],'Exact new practice source slot');
            if(array_key_exists('intro',$data)){throw new RuntimeException('M82 generic practice intro cannot earn SSR coverage.');}
            foreach(['select_intro','choice_intro','input_intro'] as $supported){
                if(!is_string($data[$supported]??null)||trim($data[$supported])===''){throw new RuntimeException('M82 visible group intro missing.');}
            }
        }
        foreach(['title','intro','warning'] as $field){$contains($text,$data[$field]??null,$label.'/'.$field);}
PHP;
$comparisonRows=<<<'PHP'
            $tableRows=$xp->query('.//tbody/tr',$node);
            foreach($data['rows'] as $n=>$row){
                if(isset($row['cells'])){$same(array_keys($row),['cells'],'M82 cells-row shape');$cells=$row['cells'];}
                else{$same(array_keys($row),['en','ua','note'],'M82 en/ua/note row shape');$cells=[$row['en'],$row['ua'],$row['note']];}
                $same(is_array($cells)&&array_is_list($cells),true,'M82 native row cell vector');
                $same(count($cells),count($data['headers']),'M82 native table column count');
                $actualCells=$xp->query('./td',$tableRows->item($n));$same($actualCells->length,count($cells),'M82 actual row cell count');
                foreach($cells as $c=>$cell){
                    if(!is_string($cell)||trim($cell)===''){throw new RuntimeException('M82 comparison learning cell empty.');}
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
            $navSlot=$i<4?5:3;
            $oldNavigation=json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][$navSlot]['body'],true,flags:JSON_THROW_ON_ERROR);
            $withoutApprovedUrls=$data;
            if($i>=4){foreach([0,1,2,3]as $index){unset($withoutApprovedUrls['items'][$index]['url']);}}
            $same($withoutApprovedUrls,$oldNavigation,'M82 retained navigation title/labels/order/current, except eight approved data URLs');
            $same(count($data['items']),$navigationExpectedCounts[$i],'M82 per-owner navigation item count');
            $navigations=$xp->query('.//nav[./p and ./div/*[self::a or self::span]]',$main);
            $same($navigations->length,1,'M82 one canonical footer navigation');
            $nav=$navigations->item(0);$navTitle=$xp->query('./p',$nav);$same($navTitle->length,1,'M82 footer navigation title count');
            $same($plain($navTitle->item(0)->textContent),$plain($data['title']),'M82 footer navigation title');
            $navNodes=$xp->query('./div/*[self::a or self::span]',$nav);$same($navNodes->length,count($data['items']),'M82 actual navigation items');
            $currentCount=0;
            foreach($data['items'] as $n=>$item){
                $entry=$navNodes->item($n);$same($plain($entry->textContent),$plain($item['label']),'M82 ordered footer label');
                if($i>=4){
                    $oldEntry=$capturedLabelNavigation[$i][$n];
                    $same($entry->tagName,$oldEntry['tag'],'M82 unchanged native resolver tag');
                    if($item['current']){
                        $currentCount++;$same($entry->tagName,'span','M82 current fifth chip is not a link');
                        $same($entry->hasAttribute('href'),false,'M82 current fifth chip still has no href');
                        $same(array_key_exists('url',$item),false,'M82 current fifth data item still has no URL');
                    }else{
                        $same($oldEntry['href'],'#','M82 actual historical unresolved fragment');
                        $same($item['url'],$navigationUrls[$i][$n],'M82 exact approved data URL');
                        $same($entry->getAttribute('href'),$item['url'],'M82 unchanged native resolver now renders approved data URL');
                        $destination='http://gramlyze.loc'.$item['url'];$navLinks[]=$destination;
                        $repairedFragmentPlaceholders[]=['identity'=>$owner,'page_url'=>$url,'label'=>$item['label'],
                            'before_href'=>'#','after_href'=>$item['url'],'independent_target_page_id'=>$navigationDestinationExpectations[$destination]['target_page_id'],
                            'data_only_change'=>true,'native_view_and_resolver_unchanged'=>true];
                    }
                }else{
                    if(preg_match('~^/theory/common-mistakes/[a-z0-9-]+$~D',$item['url']??'')!==1){
                        throw new RuntimeException('M82 navigation destination outside exact reviewed local category.');
                    }
                    if($item['current']){
                        $currentCount++;$same($entry->tagName,'span','M82 current chip is not a link');
                        $same($entry->hasAttribute('href'),false,'M82 current chip has no href');
                        $same('http://gramlyze.loc'.$item['url'],$url,'M82 current navigation owner URL');
                    }else{
                        $same($entry->tagName,'a','M82 noncurrent navigation link');
                        $same($entry->getAttribute('href'),$item['url'],'M82 ordered exact navigation href');
                        $navLinks[]='http://gramlyze.loc'.$item['url'];
                    }
                }
                $contains($mainText,$item['label'],$label.'/items/'.$n.'/label');$navigationItemCount++;
            }
            $same($currentCount,1,'M82 one current navigation item');
            continue;
        }
PHP;
$navigation=<<<'PHP'
$destinations=[];
foreach($results as $page){foreach($page['navigation_exact_local_destinations'] as $url){$destinations[$url]=true;}}
$authoredBodyLinkCount=0;
foreach($authoredBodyLinks as $links){foreach($links as $href){$destinations['http://gramlyze.loc'.$href]=true;$authoredBodyLinkCount++;}}
ksort($destinations);$navigationResults=[];
foreach(array_keys($destinations) as $url){
    [$html,$status,$robots]=$http($url);$dom=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
    try{$loaded=$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($previous);}
    if(!$loaded){throw new RuntimeException('M82 navigation destination HTML cannot be parsed.');}$xp=new DOMXPath($dom);
    if($xp->query('//h1')->length!==1||trim($xp->query('//h1')->item(0)->textContent)===''||$xp->query('//*[@data-theory-main]')->length!==1){throw new RuntimeException('M82 navigation destination lacks one canonical theory/H1.');}
    $actualH1=$plain($xp->query('//h1')->item(0)->textContent);$reviewed=$navigationDestinationExpectations[$url]??null;
    if($reviewed!==null){$same($actualH1,$plain($reviewed['h1']),'M82 independently confirmed footer destination H1');}
    $navigationResults[]=['url'=>$url,'status'=>$status,'h1'=>$actualH1,'html_sha256'=>hash('sha256',$html),
        'independent_target_page_id'=>$reviewed['target_page_id']??null,'reviewed_destination_h1_verified'=>$reviewed!==null,
        'anonymous_loopback'=>true,'redirects'=>0];
}
$same(count($results),6,'M82 exact six accepted targets');
$same($navigationItemCount,34,'M82 all navigation items/order/current verified');
$same(count($repairedFragmentPlaceholders),8,'M82 eight historical fragments repaired by approved data URLs');
$same(count(array_intersect_key($destinations,$navigationDestinationExpectations)),8,'M82 all eight repaired destinations actually GET checked');
$same(count($reviewedMetadataTransitions),8,'M82 reviewed transitions; not after-SQL acceptance');
foreach ($inputs as $path => $sha) { if (!hash_equals($sha, $fileHash($path))) { throw new RuntimeException('Reviewed input changed during HTTP acceptance.'); } }
PHP;
runFrozenReadOnlyProfile('m53-live-pages.php','66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6',[
    ['M53PassiveConstructionsPackage','M82CommonMistakesLevelRevision',1],['M53BuilderHintPresentation','M82BuilderSelectionPresentation',5],['M53','M82',7],['m53','m82',3],
    ["\$inputs = [\$beforePath => M82ContentPatch::BEFORE_SHA, \$auditPath => M82BuilderSelectionPresentation::AUDIT_SHA];",$metadataBootstrap,1],
    [$oldHttp,$allowlist,1],['CURLOPT_TIMEOUT => 45','CURLOPT_TIMEOUT => 120',1],
    [$oldNavigation,$newNavigation,1],
    [$oldSelection,$selection,1],
    ["        foreach (['title', 'intro', 'warning'] as \$field) { \$contains(\$text, \$data[\$field] ?? null, \$label.'/'.\$field); }",$fieldVisibility,1],
    ["            foreach (\$data['rows'] as \$n => \$row) { foreach (\$row['cells'] as \$c => \$cell) { \$contains(\$text, \$cell, \$label.'/rows/'.\$n.'/'.\$c); } }",$comparisonRows,1],
    ["                    if (\$group === 'choices') { \$contains(\$groupText, \$task['prompt'], \$label.'/'.\$group.'/'.\$t.'/stimulus'); }","                    if (array_key_exists('prompt',\$task)) { throw new RuntimeException('M82 task prompt is unsupported; use visible label/after.'); }",1],
    ['$resources = [];','$resources = []; $resourceDescriptions=[];',1],
    ['use (&$visit, &$resources): void','use (&$visit, &$resources, &$resourceDescriptions): void',1],
    ["\$resources[] = array_intersect_key(\$value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage']));","\$resources[] = array_intersect_key(\$value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage']));\n            \$resourceDescriptions[]=\$value['description']??null;",1],
    ["\$same(\$resources, \$oldResources, 'Stable LearningResource identity');","\$same(\$resources, \$oldResources, 'Stable LearningResource identity');\n    \$same(\$resourceDescriptions,[\$metadata['expected_learning_resource_description']],'Prospective LearningResource description');",1],
    ["foreach (\$inputs as \$path => \$sha) { if (!hash_equals(\$sha, \$fileHash(\$path))) { throw new RuntimeException('Reviewed input changed during HTTP acceptance.'); } }",$navigation,1],
    ["'pages' => \$results, 'SSR_learning_fields_checked' => \$fieldCount,","'pages' => \$results, 'SSR_learning_fields_checked' => \$fieldCount,\n    'navigation_destinations'=>\$navigationResults,'navigation_destinations_http_200'=>count(\$navigationResults),\n    'navigation_source_items_checked'=>\$navigationItemCount,'reviewed_metadata_transition_count'=>count(\$reviewedMetadataTransitions),\n    'reviewed_metadata_transitions'=>\$reviewedMetadataTransitions,'unsupported_not_rendered_source_fields'=>[],\n    'authored_body_link_count'=>\$authoredBodyLinkCount,'authored_body_links'=>\$authoredBodyLinks,\n    'repaired_historical_fragment_placeholders'=>\$repairedFragmentPlaceholders,\n    'footer_href_destinations_verified'=>true,'approved_footer_url_additions'=>\$navigationAdditions,\n    'native_view_and_resolver_unchanged'=>true,'retained_navigation_limitations'=>[],",1],
    ["'navigation destination availability beyond the exact three requested URLs', ",'',1],
    ["'SQL postconditions (separate diagnostic)'","'SQL postconditions (separate diagnostic)', 'dynamic assets/fonts loaded', 'client-visible settled answers (separate browser acceptance)'",1],
],['prospective_file'=>$prospectiveFile,'prospective_sha256'=>$prospectiveSha]);
