<?php

/** Pure independent exact M82 allowlist review. No bootstrap, SQL, HTTP or file writes. */
if (PHP_SAPI!=='cli'||count($argv)!==2||preg_match('/^preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1) {
    throw new RuntimeException('M82 exact preview basename required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m82-local';
require $root.'/vendor/autoload.php';
use App\Support\M82CommonMistakesLevelRevision as Package;
use App\Support\M26DetailPackage as Source;
use App\Services\M82ContentPatch as Patch;
$read=static function(string $name,?string $sha=null)use($dir):array{
    $file=$dir.'/'.$name;
    if(!is_file($file)||is_link($file)||($sha!==null&&!hash_equals($sha,hash_file('sha256',$file)))) {
        throw new RuntimeException('M82 exact review evidence differs.');
    }
    return json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
};
$before=$read(Patch::BEFORE_FILE,Patch::BEFORE_SHA);$plan=$read($argv[1]);$unsigned=$plan;unset($unsigned['sha256']);
$digest=static fn($v)=>App\Services\M26ContentPatch::digest($v);
[$sourceBefore,$patch,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$patch,$master,$contract);
if(($plan['patch']??null)!=='m82-common-mistakes-level-revision-standard-view-v1'||($plan['version']??null)!==1
    ||($plan['state']??null)!=='before'||($plan['names']??null)!==Package::OWNERS
    ||count($plan['inserts']??[])!==6||($plan['deletes']??[])!==[]||$digest($unsigned)!==($plan['sha256']??null)
    ||($before['sql_before']??null)!==($before['sql_after_http']??null)||count($before['sql_before']??[])!==46
    ||array_keys($before['pages']??[])!==Package::OWNERS||isset($plan['question_supplement'])) {
    throw new RuntimeException('M82 preview exact state/digest differs.');
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
$expected=[];$expectedInserts=[];$body=0;$subtitleBodies=0;$teachingBodies=0;$meta=0;$metaRows=[];$pageText=0;$practice=0;$nonUk=0;
$finiteBoxes=[
    1714=>'5c9afe7e-018c-5bbf-a37b-423059adbe5f',1715=>'d776727b-5f0f-5a89-b09c-b5e79de3e87d',
    1672=>'cd508a49-61aa-5e34-bf35-cf5f782c164e',1673=>'b8629bfb-27d4-5cdd-a217-7539a8847352',
    1693=>'e9f04d6e-5626-587f-b061-94ccb976bb50',1694=>'04ddd24d-94a7-553d-b276-6a20a520782c',
    1651=>'0975e241-500b-5f12-9493-336bffdbae0c',1652=>'ab5515d1-7685-5839-b6d2-d63f14638802',
];
foreach($patch['targets'] as $i=>$target) {
    $owner=Package::OWNERS[$i];$captured=$before['pages'][$owner];$old=$sourceBefore['targets'][$i]['before'];$new=$target['after'];
    Patch::assertDefinitionScope($old,$new,$target,$i);
    if($captured['page']['id']!==Package::PAGE_IDS[$i]||$captured['page']['page_category_id']!==Package::CATEGORY_IDS[$i]
        ||$captured['page']['seeder']!==$owner||$captured['page']['text']!==$old['page']['subtitle_text']) {
        throw new RuntimeException('M82 independently captured Page/category/text differs.');
    }
    if($before['definitions'][$owner]['definition']!==$old) { throw new RuntimeException('M82 source BEFORE is not independent capture.'); }
    foreach(['page','category_ancestry','blocks'] as $field) {
        if(($plan['pages'][$owner][$field]??null)!==$captured[$field]) { throw new RuntimeException('M82 preview captured identity differs.'); }
    }
    if($old['page']['subtitle_text']!==$new['page']['subtitle_text']) {
        $row=$captured['page'];$expected['pages:'.$row['id']]=[
            'table'=>'pages','id'=>$row['id'],'uuid'=>$row['uuid']??null,'seeder'=>$owner,
            'before'=>['text'=>$row['text']],'after'=>['text'=>$new['page']['subtitle_text']],
        ];++$pageText;
    }
    foreach($captured['blocks'] as $row) {
        if($row['locale']!=='uk') { ++$nonUk;continue; }$pos=$row['sort_order'];
        if(!is_int($pos)||$pos<0||$pos>Package::CONTINUING_COUNTS[$i]){throw new RuntimeException('M82 captured continuing source position differs.');}
        $a=$pos===0?['body'=>$old['page']['subtitle_html'],'type'=>'subtitle','heading'=>null]:$old['page']['blocks'][$pos-1];
        $b=$pos===0?['body'=>$new['page']['subtitle_html'],'type'=>'subtitle','heading'=>null]:$new['page']['blocks'][$pos-1];
        $from=$to=[];
        foreach(['body','type','heading'] as $field) {
            $from[$field]=$a[$field]??null;$to[$field]=$b[$field]??null;
            if($row[$field]!==$from[$field]) { throw new RuntimeException('M82 independent old row/source differs.'); }
        }
        if(isset($finiteBoxes[$row['id']])){
            if($row['uuid']!==$finiteBoxes[$row['id']]||$from['type']!=='box'||$to['type']!=='usage-panels'
                ||$from['heading']!==($pos===2?'1. Де плутаються найчастіше':'2. Як працює правильний вибір')
                ||$to['heading']!==null||$row['level']!==null||$row['column']!=='left'){
                throw new RuntimeException('M82 exact eight box metadata transitions differ.');
            }
        }elseif($from['type']!==$to['type']||$from['heading']!==$to['heading']){
            throw new RuntimeException('M82 unapproved type or heading change.');
        }
        foreach(['level','column','css_class']as $field){
            if($pos!==0&&($b[$field]??null)!==$row[$field]){throw new RuntimeException('M82 immutable continuing metadata changed.');}
        }
        if($from===$to) { continue; }
        $expected['text_blocks:'.$row['id']]=[
            'table'=>'text_blocks','id'=>$row['id'],'uuid'=>$row['uuid'],'seeder'=>$owner,'before'=>$from,'after'=>$to,
        ];
        if($from['body']!==$to['body']) { ++$body;if($pos===0){++$subtitleBodies;}else{++$teachingBodies;}if($pos===Package::PRACTICE_SOURCE_INDICES[$i]+1) { ++$practice; } }
        foreach(['type','heading'] as $field) { if($from[$field]!==$to[$field]) { ++$meta;$metaRows[$row['id']]=true; } }
    }
    foreach(array_slice($new['page']['blocks'],Package::CONTINUING_COUNTS[$i],null,true) as $slot=>$config) {
        if($slot!==Package::PRACTICE_SOURCE_INDICES[$i]||$config['type']!=='practice-set'||$config['column']!=='left'
            ||($config['level']??null)!==Package::LEVELS[$i]) { throw new RuntimeException('M82 appended practice scope differs.'); }
        $page=$captured['page'];$fields=[
            'uuid'=>Source::uuid($owner,$config,$slot+1),'page_id'=>$page['id'],'page_category_id'=>$page['page_category_id'],
            'locale'=>'uk','type'=>$config['type'],'column'=>$config['column'],'heading'=>$config['heading']??null,
            'css_class'=>$config['css_class']??null,'sort_order'=>$slot+1,'body'=>$config['body'],'level'=>$config['level']??null,'seeder'=>$owner,
        ];
        if(array_filter($captured['blocks'],static fn($r)=>$r['uuid']===$fields['uuid'])!==[]) {
            throw new RuntimeException('M82 supposedly new practice already existed in independent BEFORE.');
        }
        $expectedInserts[]=['table'=>'text_blocks','seeder'=>$owner,'fields'=>$fields,'sha256'=>$digest($fields)];
    }
}

$expectedSources=array_values(array_unique([...Patch::sourcePaths($patch,$contract,$root),
    'private:'.Patch::BEFORE_FILE,'private:'.Patch::CONSUMERS_FILE,'private:'.Patch::SYNC_RECEIPT]));
sort($expectedSources);$actualSources=array_keys($plan['sources']);sort($actualSources);
if($expectedSources!==$actualSources){throw new RuntimeException('M82 exact reviewed source/evidence allowlist differs.');}
foreach($plan['sources']as $path=>$sha){
    $file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;
    if(!is_file($file)||is_link($file)||!hash_equals($sha,hash_file('sha256',$file))){throw new RuntimeException('M82 preview source/evidence byte changed: '.$path);}
}

$seen=[];
foreach($plan['updates'] as $update) {
    $key=$update['table'].':'.$update['id'];$e=$expected[$key]??null;
    if($e===null||isset($seen[$key])||array_keys($update)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
        ||array_diff_key($update,['before_sha256'=>true,'after_sha256'=>true])!==$e
        ||$update['before_sha256']!==$digest($e['before'])||$update['after_sha256']!==$digest($e['after'])) {
        throw new RuntimeException('M82 exact update is not independently approved.');
    }
    $seen[$key]=true;
}
if(count($seen)!==count($expected)||count($seen)!==44||$meta!==16||count($metaRows)!==8
    ||$pageText!==6||$body!==38||$subtitleBodies!==6||$teachingBodies!==32||$practice!==0||$nonUk!==56||count($expectedInserts)!==6||$plan['inserts']!==$expectedInserts) {
    throw new RuntimeException('M82 exact transition fields/rows/inserts differ.');
}
echo json_encode([
    'status'=>'exact-allowlist-reviewed','plan'=>$argv[1],'plan_digest'=>$plan['sha256'],
    'plan_raw_sha256'=>hash_file('sha256',$dir.'/'.$argv[1]),'page_text_updates'=>$pageText,'body_updates'=>$body,
    'continuing_practice_body_updates'=>$practice,'metadata_fields'=>$meta,'metadata_rows'=>count($metaRows),
    'subtitle_body_updates'=>$subtitleBodies,'continuing_teaching_body_updates'=>$teachingBodies,'unchanged_en_pl_rows'=>$nonUk,
    'updates'=>count($seen),'inserts'=>6,'deletes'=>0,'question_family_writes'=>0,
    'approved_footer_url_additions'=>$navigationAdditions,'all_footer_labels_order_current_preserved'=>true,
    'db_connections'=>0,'db_writes'=>0,'http_requests'=>0,
],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
