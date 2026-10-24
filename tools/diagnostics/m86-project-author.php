<?php

/** Pure finite author projection; no Laravel bootstrap, active source, DB or HTTP writes. */
if(PHP_SAPI!=='cli'||count($argv)!==5||preg_match('/^author-projection-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^navigation-review-v[1-9][0-9]*\.json$/D',$argv[2])!==1||preg_match('/^[a-f0-9]{64}$/D',$argv[3])!==1
    ||preg_match('/^draft-input-pins-v[1-9][0-9]*\.json$/D',$argv[4])!==1){throw new RuntimeException('Exact private M86 output/navigation/pins arguments required.');}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m86-local';
$read=static function(string $name,?string $sha=null)use($dir):array{$path=$dir.'/'.$name;if(!is_file($path)||is_link($path)){throw new RuntimeException('Regular private input missing: '.$name);}$raw=file_get_contents($path);if($sha!==null&&(preg_match('/^[a-f0-9]{64}$/D',$sha)!==1||!hash_equals($sha,hash('sha256',$raw)))){throw new RuntimeException('Pinned private input differs: '.$name);}return json_decode($raw,true,flags:JSON_THROW_ON_ERROR);};
$out=$dir.'/'.$argv[1];if(file_exists($out)||is_link($out)||is_link($dir)){throw new RuntimeException('Exclusive private projection output required.');}
$beforeSha='42f8a613fcb1721b2badcfabb9c67116f2c79fa9c14b7dc4df5e19dd71892208';$navigationSha='6f95ca3fee7bbf8df7215d6ab858e57fcca9c4aaec6246f61a65c241b4ad117b';
if($argv[3]!==$navigationSha){throw new RuntimeException('Exact independently reviewed M86 navigation required.');}
$before=$read('before-v1.json',$beforeSha);$nav=$read($argv[2],$navigationSha);$pins=$read($argv[4]);$pinsSha=hash_file('sha256',$dir.'/'.$argv[4]);
$names=match($argv[4]){
    'draft-input-pins-v1.json'=>['author-ab-draft-v3.json','author-cd-draft-v1.json','author-ef-draft-v1.json'],
    'draft-input-pins-v2.json'=>['author-ab-draft-v3.json','author-cd-draft-v2.json','author-ef-draft-v1.json'],
    'draft-input-pins-v3.json'=>['author-ab-draft-v3.json','author-cd-draft-v2.json','author-ef-draft-v1.json'],
    default=>throw new RuntimeException('Only explicitly reviewed finite M86 draft-input profiles are supported.'),
};
if(array_keys($pins)!==$names||($before['base_sha']??null)!=='f5505ae98b8babd7e5a7fa2fd28784802a961fbd'
    ||($nav['independent_before_sha256']??null)!==$beforeSha||($nav['db_writes']??null)!==0
    ||($nav['learning_source_writes']??null)!==0||($nav['tables_unchanged']??null)!==true
    ||count($nav['targets']??[])!==24||($nav['sql_before']??null)!==$before['sql_before']||($nav['sql_after']??null)!==$before['sql_before']){
    throw new RuntimeException('Exact six-owner draft/navigation/BEFORE provenance differs.');
}
require $root.'/vendor/autoload.php';
use App\Support\M86PresentSimpleContinuousBasics as Package;
use App\Support\M26DetailPackage;
use App\Support\TheoryNativeContentRelease;
$parts=[];$draftFacts=[];
foreach($pins as$file=>$sha){$data=$read($file,$sha);if(($data['independent_before_sha256']??null)!==$beforeSha||count($data['parts']??[])!==2){throw new RuntimeException('Exact two-lesson draft lineage differs.');}array_push($parts,...$data['parts']);$draftFacts[]=['file'=>$file,'sha256'=>$sha,'parts'=>2];}
if(array_column(array_column($parts,'lesson'),'identity')!==Package::OWNERS||array_keys($before['definitions'])!==Package::OWNERS||array_keys($nav['owner_navigations'])!==Package::OWNERS){throw new RuntimeException('Exact ordered six-owner source vector differs.');}
$sourceBefore=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'content_type'=>'M86_source_before','targets'=>[]];
$patch=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'content_type'=>'M86_Present_Simple_Continuous_Basics','status'=>'draft','targets'=>[]];
$bindings=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'bindings'=>[]];$contract=['schema_version'=>1,'targets'=>[]];
$navigation=['schema_version'=>1,'receipt_sha256'=>$navigationSha,'targets'=>[]];
$master=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'version'=>Package::VERSION,'status'=>'draft',
    'policy'=>['visual_changes'=>0,'grader_changes'=>0,'formatter_changes'=>0,'continuing_metadata_changes'=>0,
        'practice'=>'55 graded controls:35 retained/corrected plus20 new;4 separate ungraded demos;16 complete manual/token inputs.',
        'widgets'=>'One separate exact thematic five-question widget per owner. Existing banks remain priority; bank proof is separate.',
        'level'=>'Independent M86 BEFORE confirms continuing and existing-practice SQL levels NULL. Display A1–A2 and editorial focus are not exact filters.',
        'detail'=>'Explicit semantic point decisions; no disclosure quota or runtime word threshold.'],
    'editorial_review'=>['pre_apply_metadata'=>[],'task_map'=>[],'lesson_reviews'=>[],'author_lesson_provenance'=>[],
        'practice_heading_projection'=>[],'appended_block_key_projection'=>[],'task_ledger_projection'=>[],'navigation_review'=>null],'lessons'=>[]];
$allowedLesson=['identity','slug','locale','raw_title','preserved_h1','local_url','display_level','technical_block_level',
    'intended_own_practice_level','subtitle_html','subtitle_text','blocks','details','point_detail_decisions'];
$counts=['retained'=>0,'new'=>0,'demos'=>0,'graded'=>0,'full'=>0];
foreach(Package::OWNERS as$i=>$owner){
    $part=$parts[$i];$draft=$part['lesson'];$lesson=[];foreach($allowedLesson as$key){if(!array_key_exists($key,$draft)){throw new RuntimeException('Required public lesson field missing: '.$key);}$lesson[$key]=$draft[$key];}
    $captured=$before['definitions'][$owner];$old=$captured['definition'];$state=$before['pages'][$owner];$count=Package::CONTINUING_COUNTS[$i];$slot=Package::PRACTICE_SOURCE_INDICES[$i];
    if($state['page']['id']!==Package::PAGE_IDS[$i]||$state['page']['page_category_id']!==Package::CATEGORY_IDS[$i]
        ||$state['target_route']['url']!==$lesson['local_url']||$captured['sha256']!==hash('sha256',$captured['bytes'])
        ||$old!==json_decode($captured['bytes'],true,flags:JSON_THROW_ON_ERROR)||count($old['page']['blocks'])!==$count
        ||$state['page']['text']!==null||$state['continuing_levels']!==array_fill(0,$count,null)
        ||$lesson['technical_block_level']!==null||$lesson['display_level']!==Package::DISPLAY_LEVELS[$i]
        ||$lesson['intended_own_practice_level']!==Package::FOCUS_LEVELS[$i]){throw new RuntimeException('Fresh actual owner/metadata/level/source block vector differs.');}
    foreach([$root,$wt]as$base){$file=$base.'/'.$captured['path'];if(!is_file($file)||is_link($file)||file_get_contents($file)!==$captured['bytes']){throw new RuntimeException('Current paired source no longer equals independent BEFORE: '.$captured['path']);}}
    foreach($old['page']['blocks']as$n=>$source){$block=$lesson['blocks'][$n]??[];$rows=array_values(array_filter($state['blocks'],static fn($r)=>$r['locale']==='uk'&&$r['sort_order']===$n+1));
        if(count($rows)!==1||$rows[0]['body']!==$source['body']||$rows[0]['level']!==null
            ||($block['existing_source_index']??null)!==$n||($block['type']??null)!==$source['type']||($block['column']??null)!==$source['column']
            ||!array_key_exists('level',$block)||$block['level']!==null){throw new RuntimeException('Continuing source/SQL metadata or exact body differs.');}}
    $expectedPracticeCount=$i<4?1:0;if(count($state['existing_practices'])!==$expectedPracticeCount||count($lesson['blocks'])!==$count+($i<4?0:1)){throw new RuntimeException('Actual retained/missing practice inventory differs.');}
    if($i<4){$row=$state['existing_practices'][0];if($row['uuid']!==Package::EXISTING_PRACTICE_UUIDS[$i]||$row['sort_order']!==7||$row['column']!=='left'||$row['level']!==null||$row['body']!==$old['page']['blocks'][6]['body']||M26DetailPackage::uuid($owner,$old['page']['blocks'][6],7)!==$row['uuid']){throw new RuntimeException('Owner-specific legacy practice UUID/source binding differs.');}$lesson['blocks'][$slot]['existing_source_index']=$slot;}
    else{$lesson['blocks'][$slot]['existing_source_index']=null;$key=$lesson['blocks'][$slot]['key'];if(preg_match('/^m86-[a-z0-9-]+$/D',$key)!==1){$new='m86-'.Package::SLUGS[$i].'-practice';$master['editorial_review']['appended_block_key_projection'][]=['identity'=>$owner,'source_index'=>$slot,'before'=>$key,'after'=>$new,'reason'=>'Only new absent-practice key normalized to the existing M86 namespace; teaching data unchanged.'];$lesson['blocks'][$slot]['key']=$new;}}
    $practice=&$lesson['blocks'][$slot]['data'];
    if($i<4){
        if(($practice['choice_title']??null)!=='Перевір розуміння'){throw new RuntimeException('New meaning heading must remain unnumbered.');}
        foreach(['input_title'=>[2,3],'rephrase_title'=>[3,4]]as$field=>[$from,$to]){$prior=$practice[$field]??null;$sourceTitle=json_decode($old['page']['blocks'][6]['body'],true,flags:JSON_THROW_ON_ERROR)[$field]??null;
            if(!is_string($prior)||$prior!==$sourceTitle||preg_match('/^Вправа '.$from.'\.(.*)$/uD',$prior,$match)!==1){throw new RuntimeException('Finite retained heading-prefix source differs: '.$field);}
            $after='Вправа '.$to.'.'.$match[1];$practice[$field]=$after;$master['editorial_review']['practice_heading_projection'][]=['identity'=>$owner,'source_index'=>6,'field'=>$field,'source_before'=>$sourceTitle,'draft_before'=>$prior,'after'=>$after,'reason'=>'The inserted meaning group occupies native badge2; adjust only the explicit data prefix, preserving all old title words and all task positions.'];
        }
    }
    $banks=[Package::BANKS[$i]];$practice['linked_practice']=['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>$banks,
        'title'=>$i<4?'Вправа 5. Побудуй речення':'Вправа 4. Побудуй речення',
        'intro'=>'Склади англійське речення за українською умовою, клікаючи на слова.','footer'=>'Завдання підібрані лише для цієї теми.'];unset($practice);
    $navSlot=Package::NAVIGATION_SOURCE_INDICES[$i];$navRow=$nav['owner_navigations'][$owner];if($navRow['source_index']!==$navSlot){throw new RuntimeException('Reviewed footer slot differs.');}$lesson['blocks'][$navSlot]['data']=$navRow['data'];
    if($i===2&&$lesson['blocks'][8]['data']!==json_decode($old['page']['blocks'][8]['body'],true,flags:JSON_THROW_ON_ERROR)){throw new RuntimeException('Captured Questions second footer changed.');}
    $navigation['targets'][]=['identity'=>$owner,'page_id'=>Package::PAGE_IDS[$i],'source_index'=>$navSlot,'items'=>$navRow['data']['items']];
    $binding=['identity'=>$owner,'slug'=>Package::SLUGS[$i],'page_id'=>Package::PAGE_IDS[$i],'category_id'=>Package::CATEGORY_IDS[$i],
        'ancestry'=>Package::ANCESTRIES[$i],'local_url'=>$lesson['local_url'],'practice_level'=>null,'display_level'=>Package::DISPLAY_LEVELS[$i],
        'rendered_title'=>Package::RENDERED_TITLES[$i],'continuing_levels'=>$state['continuing_levels'],
        'appended_blocks'=>$i<4?[]:[['key'=>$lesson['blocks'][$slot]['key'],'type'=>'practice-set','column'=>'left','level'=>null]],
        'practice_contract'=>['counts'=>$i<4?['selects'=>3,'choices'=>2,'inputs'=>3]:['selects'=>2,'choices'=>2,'inputs'=>2],
            'sentence_input_indices'=>$i<4?[0,1,2]:[0,1]]];
    if($i<4){$binding['existing_practice']=['source_index'=>6,'uuid'=>Package::EXISTING_PRACTICE_UUIDS[$i],'level'=>null];}$bindings['bindings'][]=$binding;
    $sourceBefore['targets'][]=['identity'=>$owner,'path'=>$captured['path'],'before'=>$old];
    $contract['targets'][]=['identity'=>$owner,'definition'=>$captured['path'],'local_url'=>$lesson['local_url'],'level'=>null,'bank_seeders'=>$banks];
    $oldPractice=$i<4?json_decode($old['page']['blocks'][6]['body'],true,flags:JSON_THROW_ON_ERROR):[];$ledger=[];$seen=[];
    foreach($part['task_map']as$n=>$row){$group=$row['after_group']??'';$index=$row['after_group_index']??null;$task=is_int($index)?($lesson['blocks'][$slot]['data'][$group][$index]??null):null;
        if(!is_array($task)||($row['after_task']??null)!==$task||isset($seen[$group.':'.$index])){throw new RuntimeException('Exact draft task ledger is stale/repeated.');}$seen[$group.':'.$index]=true;
        $prior=$oldPractice[$group][$index]??null;$row['after_block_source_index']=$slot;
        if($prior!==null){if(($row['old_task']??null)!==$prior||($row['old_group']??null)!==$group||($row['old_group_index']??null)!==$index||($row['old_block_source_index']??null)!==6){throw new RuntimeException('Literal retained OLD task mapping differs.');}$row['old_block_source_index']=6;
            if(array_key_exists('answer',$prior)){$counts['retained']++;}else{$counts['demos']++;}}
        else{foreach(['old_task','old_group','old_group_index','old_block_source_index']as$field){if(!array_key_exists($field,$row)||$row[$field]!==null){throw new RuntimeException('New task invents OLD source binding.');}}$counts['new']++;
            if(empty($row['classification'])){if($i<4||array_key_exists('classification',$row)){throw new RuntimeException('Only the exact E/F omitted classification fields may be enriched.');}$master['editorial_review']['task_ledger_projection'][]=['identity'=>$owner,'after_group'=>$group,'after_group_index'=>$index,'field'=>'classification','before_present'=>false,'after'=>'new_authored','reason'=>'This exact appended E/F control has no OLD task; label the existing literal mapping, without changing task content.'];$row['classification']='new_authored';}
            $row['ledger_key']??=$task['id'];
        }
        $graded=array_key_exists('answer',$task);if(($row['graded']??null)!==$graded){throw new RuntimeException('Demo/graded task state differs.');}if($graded){$counts['graded']++;if($group==='inputs'){$counts['full']++;}}$row['after_task']=$task;$ledger[]=$row;
    }
    $master['editorial_review']['task_map'][$owner]=$ledger;$master['editorial_review']['lesson_reviews'][$owner]=$part['editorial_review'];
    $master['editorial_review']['author_lesson_provenance'][$owner]=array_diff_key($draft,array_flip($allowedLesson));
    $master['editorial_review']['pre_apply_metadata'][]=['identity'=>$owner,'mode'=>'leading-strong','sha256'=>hash('sha256',$old['page']['subtitle_html']),
        'raw_page_title'=>$old['page']['title'],'extracted_title'=>$state['subtitle_extracted_title'],'h1'=>Package::RENDERED_TITLES[$i]];
    $master['lessons'][]=$lesson;
}
if(array_values($counts)!==[35,20,4,55,16]||count($master['editorial_review']['practice_heading_projection'])!==8||count($master['editorial_review']['task_ledger_projection'])!==12){throw new RuntimeException('Exact retained/new/demo/full-input/header/12-EF-classification dimensions differ.');}
$release=new TheoryNativeContentRelease(Package::draftProfile($bindings['bindings']));
foreach($master['lessons']as$i=>$lesson){$old=$sourceBefore['targets'][$i]['before'];$after=$release->definition($lesson,$old);$patch['targets'][]=['identity'=>Package::OWNERS[$i],'slug'=>Package::SLUGS[$i],'path'=>$sourceBefore['targets'][$i]['path'],'ancestry'=>Package::ANCESTRIES[$i],'local_url'=>$lesson['local_url'],'page_binding'=>['id'=>Package::PAGE_IDS[$i],'page_category_id'=>Package::CATEGORY_IDS[$i]],'block_keys'=>array_column($lesson['blocks'],'key'),'after'=>$after];}
$master['editorial_review']['navigation_review']=$navigation;$release->validate($sourceBefore,$patch,$master,$contract);Package::assertEditorial($sourceBefore,$patch,$master,$navigation);
foreach($pins as$name=>$sha){$read($name,$sha);}$read('before-v1.json',$beforeSha);$read($argv[2],$navigationSha);if(hash_file('sha256',$dir.'/'.$argv[4])!==$pinsSha){throw new RuntimeException('Input pin manifest changed during pure projection.');}
$result=['schema_version'=>1,'purpose'=>'M86 pure finite author projection; not apply or browser acceptance','independent_before_sha256'=>$beforeSha,
    'draft_input_pins'=>$pins,'draft_input_pins_file'=>['file'=>$argv[4],'sha256'=>$pinsSha],'draft_sources'=>$draftFacts,
    'navigation_receipt'=>['file'=>$argv[2],'sha256'=>$navigationSha],'source_before'=>$sourceBefore,'native_bindings'=>$bindings,
    'master'=>$master,'patch'=>$patch,'contract'=>$contract,'navigation_review'=>$navigation,'actual_counts'=>$counts,
    'db_connections'=>0,'db_writes'=>0,'http_requests'=>0,'active_source_writes'=>0];
$bytes=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";$h=fopen($out,'x');if(!$h){throw new RuntimeException('Exclusive private projection unavailable.');}try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('Private projection incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>$argv[1],'sha256'=>hash('sha256',$bytes),'owners'=>6,'retained'=>35,'new'=>20,'graded'=>55,'demos'=>4,'full_sentence_inputs'=>16,'heading_data_refinements'=>8,'db_connections'=>0,'http_requests'=>0,'active_source_writes'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
