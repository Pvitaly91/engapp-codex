<?php

/** Pure finite author projection; no bootstrap, SQL/HTTP or active-definition writes. */
if(PHP_SAPI!=='cli'||count($argv)!==5||preg_match('/^author-projection-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^navigation-review-v[1-9][0-9]*\.json$/D',$argv[2])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[3])!==1
    ||preg_match('/^draft-input-pins-v[1-9][0-9]*\.json$/D',$argv[4])!==1){throw new RuntimeException('M84 exact private author/nav/pin basenames required.');}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m84-local';
require $root.'/vendor/autoload.php';
use App\Support\M84WordOrderHaveGot as Package;
use App\Support\M84QuestionSupplement as Supplement;
use App\Support\M26DetailPackage as Digest;
$read=static function($name,$sha)use($dir){$f=$dir.'/'.$name;if(!is_file($f)||is_link($f)||!hash_equals($sha,hash_file('sha256',$f))){throw new RuntimeException('M84 pinned private author input differs: '.$name);}return json_decode(file_get_contents($f),true,flags:JSON_THROW_ON_ERROR);};
$beforeSha='babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916';
$before=$read('before-v1.json',$beforeSha);$nav=$read($argv[2],$argv[3]);
$pins=json_decode(file_get_contents($dir.'/'.$argv[4]),true,flags:JSON_THROW_ON_ERROR);
if(array_keys($pins)!==['author-ab-draft.json','author-cd-draft.json','author-ef-draft.json']
    ||($nav['independent_before_sha256']??null)!==$beforeSha||($nav['db_writes']??null)!==0||($nav['source_writes']??null)!==0
    ||($nav['tables_unchanged']??null)!==true){throw new RuntimeException('M84 exact author/nav provenance differs.');}
$parts=[];
foreach($pins as $file=>$sha){$data=$read($file,$sha);if(($data['independent_before_sha256']??null)!==$beforeSha){throw new RuntimeException('M84 independent draft lineage differs.');}
    array_push($parts,...$data['parts']);}
if(array_column(array_column($parts,'lesson'),'identity')!==Package::OWNERS){throw new RuntimeException('M84 all six exact author parts required.');}
$sourceBefore=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'content_type'=>'M84_source_before','targets'=>[]];
$patch=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'content_type'=>'M84_Word_Order_Have_Got','status'=>'draft','targets'=>[]];
$bindings=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'bindings'=>[]];
$contract=['schema_version'=>1,'targets'=>[]];
$navigation=['schema_version'=>1,'receipt_sha256'=>$argv[3],'targets'=>[]];
$master=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'version'=>Package::VERSION,'status'=>'draft',
    'policy'=>['visual_changes'=>0,'grader_changes'=>0,'formatter_changes'=>0,'continuing_metadata_changes'=>0,
        'practice'=>'40 retained graded + 16 new = 56 own graded; 5 demos; 17 full manual/token inputs. Thirty widget questions are separate.',
        'widgets'=>'Five new exact full-sentence banks, 25 minimal questions; 5 existing Have got questions. Original banks and saved test memberships remain unchanged.',
        'level'=>'All continuing SQL levels and new Have got practice are NULL; display ranges and editorial focus are not exact technical filters.',
        'detail'=>'Finite semantic decisions, not global word thresholds; essential conditions and translations visible.'],
    'editorial_review'=>['pre_apply_metadata'=>[],'task_map'=>[],'lesson_reviews'=>[],'navigation_review'=>null], 'lessons'=>[]];
foreach(Package::OWNERS as $i=>$owner){
    $part=$parts[$i];$lesson=$part['lesson'];$captured=$before['definitions'][$owner];$old=$captured['definition'];$state=$before['pages'][$owner];
    if($state['page']['id']!==Package::PAGE_IDS[$i]||$state['page']['page_category_id']!==Package::CATEGORY_IDS[$i]
        ||$state['target_route']['url']!==$lesson['local_url']||$captured['sha256']!==hash('sha256',$captured['bytes'])
        ||count($old['page']['blocks'])!==Package::CONTINUING_COUNTS[$i]
        ||$state['continuing_levels']!==array_fill(0,Package::CONTINUING_COUNTS[$i],null)){
        throw new RuntimeException('M84 actual independent identity/SQL level/slot differs.');
    }
    $slot=Package::PRACTICE_SOURCE_INDICES[$i];
    if($i===5){$lesson['blocks'][$slot]['existing_source_index']=null;}
    $practice=&$lesson['blocks'][$slot]['data'];
    $banks=$i<5?[Package::BANKS[$i],Supplement::BANKS[$i]]:[Package::BANKS[$i]];
    $practice['linked_practice']=['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>$banks,
        'title'=>'Вправа '.($i<5?'5':'4').'. Побудуй речення',
        'intro'=>'Склади англійське речення за українською умовою, клікаючи на слова.',
        'footer'=>'Завдання підібрані лише для цієї теми.'];
    unset($practice);
    $navSlot=Package::NAVIGATION_SOURCE_INDICES[$i];
    if($navSlot!==null){
        $row=$nav['owner_navigations'][$owner]??null;
        if(!is_array($row)||$row['source_index']!==$navSlot){throw new RuntimeException('M84 independently reviewed footer slot missing.');}
        $lesson['blocks'][$navSlot]['data']=$row['data'];
        $navigation['targets'][]=['identity'=>$owner,'page_id'=>Package::PAGE_IDS[$i],'source_index'=>$navSlot,'items'=>$row['data']['items']];
    }else{$navigation['targets'][]=['identity'=>$owner,'page_id'=>Package::PAGE_IDS[$i],'source_index'=>null,'items'=>[]];}
    $binding=['identity'=>$owner,'slug'=>Package::SLUGS[$i],'page_id'=>Package::PAGE_IDS[$i],'category_id'=>Package::CATEGORY_IDS[$i],
        'ancestry'=>Package::ANCESTRIES[$i],'local_url'=>$lesson['local_url'],'practice_level'=>null,'display_level'=>Package::DISPLAY_LEVELS[$i],
        'rendered_title'=>Package::RENDERED_TITLES[$i],'continuing_levels'=>$state['continuing_levels'],
        'appended_blocks'=>$i===5?[['key'=>$lesson['blocks'][$slot]['key'],'type'=>'practice-set','column'=>'left','level'=>null]]:[],
        'practice_contract'=>['counts'=>['selects'=>$i<5?3:2,'choices'=>2,'inputs'=>$i<5?3:2],'sentence_input_indices'=>$i<5?[0,1,2]:[0,1]]];
    if($i<5){$binding['existing_practice']=['source_index'=>$slot,'uuid'=>Package::EXISTING_PRACTICE_UUIDS[$i],'level'=>null];}
    $bindings['bindings'][]=$binding;
    $sourceBefore['targets'][]=['identity'=>$owner,'path'=>$captured['path'],'before'=>$old];
    $contract['targets'][]=['identity'=>$owner,'definition'=>$captured['path'],'local_url'=>$lesson['local_url'],'level'=>null,'bank_seeders'=>$banks];
    $oldPractice=$i<5?json_decode($old['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR):[];
    $ledger=[];
    foreach($part['task_map'] as $row){
        $og=$row['old_group']??null;$on=$row['old_group_index']??null;$ag=$row['after_group'];$an=$row['after_group_index'];
        $oldTask=$og===null?null:($oldPractice[$og][$on]??null);$afterTask=$lesson['blocks'][$slot]['data'][$ag][$an]??null;
        if(!is_array($afterTask)||($og!==null&&!is_array($oldTask))
            ||(isset($row['old_task'])&&$row['old_task']!==$oldTask)) {throw new RuntimeException('M84 literal task ledger source differs.');}
        // The active linked declaration is deliberately assembled above; no task key changes occur here.
        $row['old_block_source_index']=$og===null?null:$slot;$row['after_block_source_index']=$slot;
        $row['old_task']=$oldTask;$row['after_task']=$afterTask;$ledger[]=$row;
    }
    $master['editorial_review']['task_map'][$owner]=$ledger;
    $master['editorial_review']['lesson_reviews'][$owner]=$part['editorial_review'];
    $master['editorial_review']['pre_apply_metadata'][]=['identity'=>$owner,'mode'=>'leading-strong',
        'sha256'=>hash('sha256',$old['page']['subtitle_html']),'raw_page_title'=>$old['page']['title'],
        'extracted_title'=>$state['subtitle_extracted_title'],'h1'=>Package::RENDERED_TITLES[$i]];
    $master['lessons'][]=$lesson;
}
$release=new App\Support\TheoryNativeContentRelease(Package::draftProfile($bindings['bindings']));
foreach($master['lessons'] as $i=>$lesson){$old=$sourceBefore['targets'][$i]['before'];$owner=Package::OWNERS[$i];$after=$release->definition($lesson,$old);
    $patch['targets'][]=['identity'=>$owner,'slug'=>Package::SLUGS[$i],'path'=>$sourceBefore['targets'][$i]['path'],
        'ancestry'=>Package::ANCESTRIES[$i],'local_url'=>$lesson['local_url'],
        'page_binding'=>['id'=>Package::PAGE_IDS[$i],'page_category_id'=>Package::CATEGORY_IDS[$i]],
        'block_keys'=>array_column($lesson['blocks'],'key'),'after'=>$after];
}
$master['editorial_review']['navigation_review']=$navigation;
$release->validate($sourceBefore,$patch,$master,$contract);Package::assertEditorial($sourceBefore,$patch,$master,$navigation);
$result=['schema_version'=>1,'purpose'=>'M84 finite author projection, not apply or browser acceptance','independent_before_sha256'=>$beforeSha,
    'draft_input_pins'=>$pins,'navigation_receipt'=>['file'=>$argv[2],'sha256'=>$argv[3]],
    'source_before'=>$sourceBefore,'native_bindings'=>$bindings,'master'=>$master,'patch'=>$patch,'contract'=>$contract,'navigation_review'=>$navigation,
    'db_connections'=>0,'db_writes'=>0,'http_requests'=>0,'active_source_writes'=>0];
$out=$dir.'/'.$argv[1];if(file_exists($out)||is_link($out)){throw new RuntimeException('M84 exclusive projection collision.');}
$bytes=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($out,'x');if(!$h){throw new RuntimeException('M84 private output unavailable.');}try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M84 private projection incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>$argv[1],'sha256'=>hash('sha256',$bytes),'owners'=>6,'graded'=>56,'demos'=>5,'full_sentence_inputs'=>17,'db_writes'=>0],JSON_THROW_ON_ERROR)."\n";
