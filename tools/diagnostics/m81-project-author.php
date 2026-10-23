<?php

/** Pure finite M81 orchestration of the existing native-release primitive. */
if (PHP_SAPI!=='cli'||count($argv)!==2||preg_match('/^author-projection-v[1-9][0-9]*\.json$/D',$argv[1])!==1) {
    throw new RuntimeException('M81 requires one exclusive private projection basename.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m81-local';
require $root.'/vendor/autoload.php';
require_once $wt.'/app/Support/M81TransformationsCommonMistakes.php';
use App\Support\M81TransformationsCommonMistakes as Package;
use App\Support\M26DetailPackage as Digest;
$sha='facabc5369230dac0958f2e2277c7cd0e4b0523fb794c041afd46116e095c023';
if (is_link($dir.'/before-v1.json')||!hash_equals($sha,hash_file('sha256',$dir.'/before-v1.json'))) {
    throw new RuntimeException('M81 independent SQL/source/HTTP BEFORE bytes changed.');
}
$before=json_decode(file_get_contents($dir.'/before-v1.json'),true,flags:JSON_THROW_ON_ERROR);
$parts=[];$drafts=[];$bindings=[];$edits=[];
$secondary=['layout','hero_image','content','columns','exercises','mistakes','summary_points','links'];
foreach (Package::SLUGS as $i=>$name) {
    $file=$wt.'/docs/content/m81-author-parts/'.$name.'.v1.json';
    if (is_link($file)||!is_file($file)) { throw new RuntimeException('M81 author part missing: '.$name); }
    $parts[$i]=json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);$drafts[$name]=hash_file('sha256',$file);
    $lesson=&$parts[$i]['lesson'];$owner=Package::OWNERS[$i];$state=$before['pages'][$owner];$old=$before['definitions'][$owner]['definition'];
    if ($lesson['identity']!==$owner||$state['page']['id']!==Package::PAGE_IDS[$i]
        ||$state['page']['page_category_id']!==Package::CATEGORY_IDS[$i]||$lesson['local_url']!==$state['target_route']['url']
        ||array_column($state['category_ancestry'],'slug')!==Package::ANCESTRIES[$i]
        ||count($old['page']['blocks'])!==Package::CONTINUING_COUNTS[$i]
        ||count($lesson['blocks'])!==Package::CONTINUING_COUNTS[$i]+1) {
        throw new RuntimeException('M81 exact identity / continuing block vector differs.');
    }
    $edits[$owner]=['technical_block_level'=>['draft'=>$lesson['technical_block_level']??null,'projected'=>Package::LEVELS[$i]],
        'display_level'=>['draft'=>$lesson['display_level'],'projected'=>Package::DISPLAY_LEVELS[$i]],
        'continuing_level_source'=>'Exact independently captured SQL/source per-slot vector; no range-to-CEFR substitution.'];
    $lesson['technical_block_level']=Package::LEVELS[$i];$lesson['display_level']=Package::DISPLAY_LEVELS[$i];
    $lesson['blocks'][0]['data']['level']=Package::DISPLAY_LEVELS[$i];
    foreach ($lesson['blocks'] as $slot=>&$block) {
        $block['level']=$slot<count($old['page']['blocks'])?($old['page']['blocks'][$slot]['level']??null):Package::LEVELS[$i];
        if ($slot>=count($old['page']['blocks'])) { $block['existing_source_index']=null; }
    }
    unset($block);
    $practiceSlot=Package::PRACTICE_SOURCE_INDICES[$i];
    $binding=['identity'=>$owner,'slug'=>$name,'page_id'=>Package::PAGE_IDS[$i],
        'category_id'=>Package::CATEGORY_IDS[$i],'ancestry'=>Package::ANCESTRIES[$i],'local_url'=>$lesson['local_url'],
        'practice_level'=>Package::LEVELS[$i],'display_level'=>Package::DISPLAY_LEVELS[$i],'rendered_title'=>Package::RENDERED_TITLES[$i],
        'continuing_levels'=>$state['continuing_levels'],'appended_blocks'=>[
            ['key'=>$lesson['blocks'][$practiceSlot]['key'],'type'=>'practice-set','column'=>'left','level'=>Package::LEVELS[$i]]],
        'practice_contract'=>['counts'=>['selects'=>2,'choices'=>2,'inputs'=>2],'sentence_input_indices'=>[0,1]]];
    foreach ($old['page']['blocks'] as $slot=>$raw) {
        if ($raw['type']!=='box') { continue; }
        if (!in_array($slot,[1,2],true)||($raw['level']??null)!==null) {
            throw new RuntimeException('M81 unexpected legacy HTML or non-NULL level.');
        }
        $binding['continuing_metadata'][$slot]=['column'=>$raw['column'],
            'before'=>['type'=>'box','heading'=>$raw['heading']??null,'level'=>null],
            'after'=>['type'=>$lesson['blocks'][$slot]['type'],'heading'=>null,'level'=>null]];
        $binding['preserved_native_metadata'][$slot]=['column'=>$raw['column'],'level'=>null];
        $remove=array_values(array_intersect($secondary,array_keys($raw)));
        if ($remove!==[]) { $binding['legacy_box_projection'][$slot]=['before_sha256'=>Digest::digest($raw),'remove_fields'=>$remove]; }
    }
    $bindings[]=$binding;unset($lesson);
}
$release=new App\Support\TheoryNativeContentRelease(Package::draftProfile($bindings));
$base=['schema_version'=>1,'base_sha'=>Package::BASE_SHA];
$sourceBefore=$base+['content_type'=>'M81_source_before','targets'=>[]];
$patch=$base+['content_type'=>'M81_Transformations_Common_Mistakes','status'=>'editorial-review','targets'=>[]];
$master=$base+['version'=>'1.0.0','status'=>'editorial-review','policy'=>[
    'visual_changes'=>0,'grader_changes'=>0,'formatter_changes'=>0,'continuing_level_changes'=>0,
    'old_task_identity'=>'Fresh independent source and working SQL contain no own practice on the six exact UK owners; six new native practice blocks only.',
    'practice'=>'36 own graded tasks (12 form,12 meaning,12 full manual/token);30 existing selected widget questions counted separately.',
    'metadata'=>'Twelve explicit box-to-native type/heading transitions; preserve UUID,slot,column and actual SQL NULL level.',
    'detail'=>'Finite semantic decisions only; essentials, translations and brief cautions remain visible basic.',
],'editorial_review'=>['pre_apply_metadata'=>[],'post_apply_metadata'=>[],'lesson_reviews'=>[],'projection_edits'=>$edits],
    'lessons'=>[],'sources'=>[]];
$contract=['schema_version'=>1,'targets'=>[]];$maps=[];$tasks=[];
foreach ($parts as $i=>$part) {
    $lesson=$part['lesson'];$owner=Package::OWNERS[$i];$old=$before['definitions'][$owner];$state=$before['pages'][$owner];
    if (hash('sha256',$old['bytes'])!==$old['sha256']||json_decode($old['bytes'],true,flags:JSON_THROW_ON_ERROR)!==$old['definition']) {
        throw new RuntimeException('M81 independent definition bytes differ.');
    }
    $after=$release->definition($lesson,$old['definition']);
    $sourceBefore['targets'][]=['identity'=>$owner,'path'=>$old['path'],'before'=>$old['definition']];
    $patch['targets'][]=['identity'=>$owner,'slug'=>$lesson['slug'],'path'=>$old['path'],'ancestry'=>Package::ANCESTRIES[$i],
        'local_url'=>$lesson['local_url'],'page_binding'=>['id'=>$state['page']['id'],'page_category_id'=>$state['page']['page_category_id']],
        'block_keys'=>array_column($lesson['blocks'],'key'),'after'=>$after];
    $practice=$lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
    $contract['targets'][]=['identity'=>$owner,'definition'=>$old['path'],'local_url'=>$lesson['local_url'],
        'level'=>Package::LEVELS[$i],'bank_seeders'=>$practice['linked_practice']['seeder_classes']];
    $master['lessons'][]=$lesson;$master['editorial_review']['lesson_reviews'][$owner]=$part['editorial_review'];$tasks[$owner]=$part['task_map'];
    $http=array_values(array_filter($before['http'],static fn($h)=>$h['identity']===$owner&&$h['kind']==='theory'));
    if (count($http)!==1||$http[0]['h1']!==Package::RENDERED_TITLES[$i]) { throw new RuntimeException('M81 independent HTTP H1 differs.'); }
    $master['editorial_review']['pre_apply_metadata'][]=['identity'=>$owner,'mode'=>'leading-strong',
        'sha256'=>hash('sha256',$old['definition']['page']['subtitle_html']),'raw_page_title'=>$state['page']['title'],
        'extracted_title'=>$state['subtitle_extracted_title'],'h1'=>$http[0]['h1']];
    $master['editorial_review']['post_apply_metadata'][]=['identity'=>$owner,'sha256'=>hash('sha256',$lesson['subtitle_html']),
        'extracted_title'=>Package::RENDERED_TITLES[$i],'h1'=>Package::RENDERED_TITLES[$i]];
    foreach ($part['editorial_review']['sources']??[] as $source) { $master['sources'][]=['lesson'=>$owner]+$source; }
    foreach ($after['page']['blocks'] as $slot=>$block) {
        if (strlen($block['body'])>(int)$before['body_capacity']['max_bytes']) { throw new RuntimeException('M81 body exceeds inspected SQL capacity.'); }
        $raw=$old['definition']['page']['blocks'][$slot]??null;
        $maps[]=['identity'=>$owner,'source_index'=>$slot,'key'=>$lesson['blocks'][$slot]['key'],
            'before_type'=>$raw['type']??null,'type'=>$block['type'],'column'=>$block['column'],'level'=>$block['level']??null,
            'uuid'=>Digest::uuid($owner,$block,$slot+1),'before_block_sha256'=>$raw===null?null:Digest::digest($raw),
            'removed_secondary_fields'=>$raw===null?[]:array_values(array_diff(array_keys($raw),array_keys($block))),
            'body_sha256'=>hash('sha256',$block['body']),'detail_count'=>count(array_filter($lesson['details'],static fn($d)=>$d['owner_block_key']===$lesson['blocks'][$slot]['key'])),'new_block'=>$raw===null];
    }
}
$release->validate($sourceBefore,$patch,$master,$contract);Package::assertEditorial($sourceBefore,$patch,$master);
$out=['source_before'=>$sourceBefore,'master'=>$master,'patch'=>$patch,'contract'=>$contract,
    'native_bindings'=>$base+['bindings'=>$bindings],'component_map'=>$maps,'task_map'=>$tasks,'draft_sha256'=>$drafts,
    'independent_before_sha256'=>$sha,'db_connections'=>0,'db_writes'=>0,'http_requests'=>0];
$json=static fn($v)=>json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
foreach ([$argv[1]=>$out,'native-bindings-v1.json'=>$out['native_bindings']] as $name=>$value) {
    $file=$dir.'/'.$name;
    if ($name==='native-bindings-v1.json'&&is_file($file)&&!is_link($file)&&file_get_contents($file)===$json($value)) { continue; }
    if (file_exists($file)||is_link($file)) { throw new RuntimeException('M81 exclusive projection already exists.'); }
    $bytes=$json($value);$h=fopen($file,'x');if (!$h) { throw new RuntimeException('M81 exclusive projection unavailable.'); }
    try { if (fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)) { throw new RuntimeException('M81 projection incomplete.'); } } finally { fclose($h); }
}
echo $json(['file'=>$argv[1],'sha256'=>hash_file('sha256',$dir.'/'.$argv[1]),'bindings_sha256'=>hash_file('sha256',$dir.'/native-bindings-v1.json'),
    'lessons'=>6,'components'=>count($maps),'db_connections'=>0,'db_writes'=>0,'active_source_writes'=>0]);
