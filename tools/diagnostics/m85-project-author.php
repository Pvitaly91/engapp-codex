<?php

/** Pure finite M85 author projection, never active files, application bootstrap, SQL or HTTP. */
if(PHP_SAPI!=='cli'||count($argv)!==5||preg_match('/^author-projection-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^navigation-review-v[1-9][0-9]*\.json$/D',$argv[2])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[3])!==1
    ||preg_match('/^draft-input-pins-v[1-9][0-9]*\.json$/D',$argv[4])!==1){throw new RuntimeException('M85 exact private author/navigation/pins arguments required.');}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m85-local';
require $root.'/vendor/autoload.php';
use App\Support\M85VerbToBe as Package;
$read=static function($name,$sha)use($dir){$f=$dir.'/'.$name;if(!is_file($f)||is_link($f)||!hash_equals($sha,hash_file('sha256',$f))){throw new RuntimeException('M85 pinned private input differs: '.$name);}return json_decode(file_get_contents($f),true,flags:JSON_THROW_ON_ERROR);};
$beforeSha='fe639c06446c6fb46b5b4ab25ae654e3910b6733cc124a9e4bee262e41b411b7';
$before=$read('before-v1.json',$beforeSha);$nav=$read($argv[2],$argv[3]);
$pins=json_decode(file_get_contents($dir.'/'.$argv[4]),true,flags:JSON_THROW_ON_ERROR);
if(array_keys($pins)!==['author-ab-draft.json','author-cd-draft.json','author-ef-draft.json']
    ||($nav['independent_before_sha256']??null)!==$beforeSha||($nav['db_writes']??null)!==0||($nav['source_writes']??null)!==0
    ||($nav['tables_unchanged']??null)!==true){throw new RuntimeException('M85 exact author/navigation provenance differs.');}
$parts=[];
foreach($pins as $file=>$sha){$data=$read($file,$sha);if(($data['independent_before_sha256']??null)!==$beforeSha){throw new RuntimeException('M85 draft lineage differs.');}array_push($parts,...$data['parts']);}
if(array_column(array_column($parts,'lesson'),'identity')!==Package::OWNERS){throw new RuntimeException('M85 six exact author parts required.');}
$sourceBefore=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'content_type'=>'M85_source_before','targets'=>[]];
$patch=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'content_type'=>'M85_Verb_To_Be','status'=>'draft','targets'=>[]];
$bindings=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'bindings'=>[]];$contract=['schema_version'=>1,'targets'=>[]];
$navigation=['schema_version'=>1,'receipt_sha256'=>$argv[3],'targets'=>[]];
$master=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'version'=>Package::VERSION,'status'=>'draft',
    'policy'=>['visual_changes'=>0,'grader_changes'=>0,'formatter_changes'=>0,'continuing_metadata_changes'=>0,
        'practice'=>'36 new own graded controls, including 12 full manual/token inputs; 30 separate widget questions.',
        'widgets'=>'Select 30 existing full-sentence questions; no question-family writes.',
        'level'=>'Actual continuing SQL levels and six appended native practices are NULL; editorial focus and display ranges are not technical filters.',
        'detail'=>'Explicit finite semantic decisions, no global runtime word threshold.'],
    'editorial_review'=>['pre_apply_metadata'=>[],'task_map'=>[],'lesson_reviews'=>[],'navigation_review'=>null],'lessons'=>[]];
foreach(Package::OWNERS as $i=>$owner){
    $part=$parts[$i];$lesson=$part['lesson'];$captured=$before['definitions'][$owner];$old=$captured['definition'];$state=$before['pages'][$owner];
    if($state['page']['id']!==Package::PAGE_IDS[$i]||$state['page']['page_category_id']!==Package::CATEGORY_IDS[$i]
        ||$state['target_route']['url']!==$lesson['local_url']||$captured['sha256']!==hash('sha256',$captured['bytes'])
        ||count($old['page']['blocks'])!==Package::CONTINUING_COUNTS[$i]||$state['existing_practices']!==[]
        ||$state['page']['text']!==null||$state['continuing_levels']!==array_fill(0,Package::CONTINUING_COUNTS[$i],null)){
        throw new RuntimeException('M85 actual independent identity/SQL level/practice/slot differs.');
    }
    $slot=Package::PRACTICE_SOURCE_INDICES[$i];$lesson['blocks'][$slot]['existing_source_index']=null;
    $banks=[Package::BANKS[$i]];$practice=&$lesson['blocks'][$slot]['data'];
    $practice['linked_practice']=['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>$banks,
        'title'=>'Вправа 4. Побудуй речення','intro'=>'Склади англійське речення за українською умовою, клікаючи на слова.',
        'footer'=>'Завдання підібрані лише для цієї теми.'];unset($practice);
    $navSlot=Package::NAVIGATION_SOURCE_INDICES[$i];$row=$nav['owner_navigations'][$owner]??null;
    if(!is_array($row)||$row['source_index']!==$navSlot){throw new RuntimeException('M85 independently reviewed footer slot missing.');}
    $lesson['blocks'][$navSlot]['data']=$row['data'];
    $navigation['targets'][]=['identity'=>$owner,'page_id'=>Package::PAGE_IDS[$i],'source_index'=>$navSlot,'items'=>$row['data']['items']];
    $bindings['bindings'][]=['identity'=>$owner,'slug'=>Package::SLUGS[$i],'page_id'=>Package::PAGE_IDS[$i],'category_id'=>Package::CATEGORY_IDS[$i],
        'ancestry'=>Package::ANCESTRIES[$i],'local_url'=>$lesson['local_url'],'practice_level'=>null,'display_level'=>Package::DISPLAY_LEVELS[$i],
        'rendered_title'=>Package::RENDERED_TITLES[$i],'continuing_levels'=>$state['continuing_levels'],
        'appended_blocks'=>[['key'=>$lesson['blocks'][$slot]['key'],'type'=>'practice-set','column'=>'left','level'=>null]],
        'practice_contract'=>['counts'=>['selects'=>2,'choices'=>2,'inputs'=>2],'sentence_input_indices'=>[0,1]]];
    $sourceBefore['targets'][]=['identity'=>$owner,'path'=>$captured['path'],'before'=>$old];
    $contract['targets'][]=['identity'=>$owner,'definition'=>$captured['path'],'local_url'=>$lesson['local_url'],'level'=>null,'bank_seeders'=>$banks];
    $ledger=[];foreach($part['task_map'] as $row){
        $ag=$row['after_group'];$an=$row['after_group_index'];$afterTask=$lesson['blocks'][$slot]['data'][$ag][$an]??null;
        if(!is_array($afterTask)||($row['old_group']??null)!==null||($row['old_group_index']??null)!==null||($row['old_task']??null)!==null){throw new RuntimeException('M85 append-only task ledger source differs.');}
        $row['old_block_source_index']=null;$row['after_block_source_index']=$slot;$row['old_task']=null;$row['after_task']=$afterTask;$ledger[]=$row;
    }
    $master['editorial_review']['task_map'][$owner]=$ledger;$master['editorial_review']['lesson_reviews'][$owner]=$part['editorial_review'];
    $master['editorial_review']['pre_apply_metadata'][]=['identity'=>$owner,'mode'=>'leading-strong','sha256'=>hash('sha256',$old['page']['subtitle_html']),
        'raw_page_title'=>$old['page']['title'],'extracted_title'=>$state['subtitle_extracted_title'],'h1'=>Package::RENDERED_TITLES[$i]];
    $master['lessons'][]=$lesson;
}
$release=new App\Support\TheoryNativeContentRelease(Package::draftProfile($bindings['bindings']));
foreach($master['lessons'] as $i=>$lesson){$old=$sourceBefore['targets'][$i]['before'];$after=$release->definition($lesson,$old);
    $patch['targets'][]=['identity'=>Package::OWNERS[$i],'slug'=>Package::SLUGS[$i],'path'=>$sourceBefore['targets'][$i]['path'],
        'ancestry'=>Package::ANCESTRIES[$i],'local_url'=>$lesson['local_url'],'page_binding'=>['id'=>Package::PAGE_IDS[$i],'page_category_id'=>Package::CATEGORY_IDS[$i]],
        'block_keys'=>array_column($lesson['blocks'],'key'),'after'=>$after];
}
$master['editorial_review']['navigation_review']=$navigation;
$release->validate($sourceBefore,$patch,$master,$contract);Package::assertEditorial($sourceBefore,$patch,$master,$navigation);
$result=['schema_version'=>1,'purpose'=>'M85 pure finite author projection, not apply or browser acceptance','independent_before_sha256'=>$beforeSha,
    'draft_input_pins'=>$pins,'navigation_receipt'=>['file'=>$argv[2],'sha256'=>$argv[3]],'source_before'=>$sourceBefore,'native_bindings'=>$bindings,
    'master'=>$master,'patch'=>$patch,'contract'=>$contract,'navigation_review'=>$navigation,'db_connections'=>0,'db_writes'=>0,'http_requests'=>0,'active_source_writes'=>0];
$out=$dir.'/'.$argv[1];if(file_exists($out)||is_link($out)){throw new RuntimeException('M85 exclusive projection collision.');}
$bytes=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($out,'x');if(!$h){throw new RuntimeException('M85 private output unavailable.');}try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M85 private projection incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>$argv[1],'sha256'=>hash('sha256',$bytes),'owners'=>6,'graded'=>36,'demos'=>0,'full_sentence_inputs'=>12,'db_writes'=>0],JSON_THROW_ON_ERROR)."\n";
