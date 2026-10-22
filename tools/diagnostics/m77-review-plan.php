<?php

/** Pure independent exact M77 allowlist review. No bootstrap, SQL, HTTP or file writes. */
if (PHP_SAPI!=='cli'||count($argv)!==2||preg_match('/^preview-v[1-9][0-9]*\.json$/D',$argv[1])!==1) {
    throw new RuntimeException('M77 exact preview basename required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m77-local';
require $root.'/vendor/autoload.php';
use App\Support\M77OnePrepositionsPhrasal as Package;
use App\Support\M26DetailPackage as Source;
use App\Services\M77ContentPatch as Patch;
$read=static function(string $name,?string $sha=null)use($dir):array{
    $file=$dir.'/'.$name;
    if(!is_file($file)||is_link($file)||($sha!==null&&!hash_equals($sha,hash_file('sha256',$file)))) {
        throw new RuntimeException('M77 exact review evidence differs.');
    }
    return json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
};
$before=$read(Patch::BEFORE_FILE,Patch::BEFORE_SHA);$plan=$read($argv[1]);$unsigned=$plan;unset($unsigned['sha256']);
$digest=static fn($v)=>App\Services\M26ContentPatch::digest($v);
[$sourceBefore,$patch,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$patch,$master,$contract);
if(($plan['patch']??null)!=='m77-one-prepositions-phrasal-standard-view-v1'||($plan['version']??null)!==1
    ||($plan['state']??null)!=='before'||($plan['names']??null)!==Package::OWNERS
    ||count($plan['inserts']??[])!==5||($plan['deletes']??[])!==[]||$digest($unsigned)!==($plan['sha256']??null)
    ||($before['sql_before']??null)!==($before['sql_after_http']??null)) {
    throw new RuntimeException('M77 preview exact state/digest differs.');
}
$expected=[];$expectedInserts=[];$body=0;$meta=0;$metaRows=[];$pageText=0;$practice=0;
foreach($patch['targets'] as $i=>$target) {
    $owner=Package::OWNERS[$i];$captured=$before['pages'][$owner];$old=$sourceBefore['targets'][$i]['before'];$new=$target['after'];
    Patch::assertDefinitionScope($old,$new,$target,$i);
    if($before['definitions'][$owner]['definition']!==$old) { throw new RuntimeException('M77 source BEFORE is not independent capture.'); }
    foreach(['page','category_ancestry','blocks'] as $field) {
        if(($plan['pages'][$owner][$field]??null)!==$captured[$field]) { throw new RuntimeException('M77 preview captured identity differs.'); }
    }
    if($old['page']['subtitle_text']!==$new['page']['subtitle_text']) {
        $row=$captured['page'];$expected['pages:'.$row['id']]=[
            'table'=>'pages','id'=>$row['id'],'uuid'=>$row['uuid']??null,'seeder'=>$owner,
            'before'=>['text'=>$row['text']],'after'=>['text'=>$new['page']['subtitle_text']],
        ];++$pageText;
    }
    foreach($captured['blocks'] as $row) {
        if($row['locale']!=='uk') { continue; }$pos=$row['sort_order'];
        $a=$pos===0?['body'=>$old['page']['subtitle_html'],'type'=>'subtitle','heading'=>null]:$old['page']['blocks'][$pos-1];
        $b=$pos===0?['body'=>$new['page']['subtitle_html'],'type'=>'subtitle','heading'=>null]:$new['page']['blocks'][$pos-1];
        $from=$to=[];
        foreach(['body','type','heading'] as $field) {
            $from[$field]=$a[$field]??null;$to[$field]=$b[$field]??null;
            if($row[$field]!==$from[$field]) { throw new RuntimeException('M77 independent old row/source differs.'); }
        }
        if($from===$to) { continue; }
        $expected['text_blocks:'.$row['id']]=[
            'table'=>'text_blocks','id'=>$row['id'],'uuid'=>$row['uuid'],'seeder'=>$owner,'before'=>$from,'after'=>$to,
        ];
        if($from['body']!==$to['body']) { ++$body;if($pos===Package::PRACTICE_SOURCE_INDICES[$i]+1) { ++$practice; } }
        foreach(['type','heading'] as $field) { if($from[$field]!==$to[$field]) { ++$meta;$metaRows[$row['id']]=true; } }
    }
    foreach(array_slice($new['page']['blocks'],Package::CONTINUING_COUNTS[$i],null,true) as $slot=>$config) {
        if($i===0||$slot!==6||$config['type']!=='practice-set'||$config['column']!=='left'
            ||($config['level']??null)!==Package::LEVELS[$i]) { throw new RuntimeException('M77 appended practice scope differs.'); }
        $page=$captured['page'];$fields=[
            'uuid'=>Source::uuid($owner,$config,$slot+1),'page_id'=>$page['id'],'page_category_id'=>$page['page_category_id'],
            'locale'=>'uk','type'=>$config['type'],'column'=>$config['column'],'heading'=>$config['heading']??null,
            'css_class'=>$config['css_class']??null,'sort_order'=>$slot+1,'body'=>$config['body'],'level'=>$config['level']??null,'seeder'=>$owner,
        ];
        if(array_filter($captured['blocks'],static fn($r)=>$r['uuid']===$fields['uuid'])!==[]) {
            throw new RuntimeException('M77 supposedly new practice already existed in independent BEFORE.');
        }
        $expectedInserts[]=['table'=>'text_blocks','seeder'=>$owner,'fields'=>$fields,'sha256'=>$digest($fields)];
    }
}
$seen=[];
foreach($plan['updates'] as $update) {
    $key=$update['table'].':'.$update['id'];$e=$expected[$key]??null;
    if($e===null||isset($seen[$key])||array_keys($update)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']
        ||array_diff_key($update,['before_sha256'=>true,'after_sha256'=>true])!==$e
        ||$update['before_sha256']!==$digest($e['before'])||$update['after_sha256']!==$digest($e['after'])) {
        throw new RuntimeException('M77 exact update is not independently approved.');
    }
    $seen[$key]=true;
}
if(count($seen)!==count($expected)||count($seen)!==53||$meta!==57||count($metaRows)!==29
    ||$pageText!==6||$body!==47||$practice!==1||count($expectedInserts)!==5||$plan['inserts']!==$expectedInserts) {
    throw new RuntimeException('M77 exact transition fields/rows/inserts differ.');
}
echo json_encode([
    'status'=>'exact-allowlist-reviewed','plan'=>$argv[1],'plan_digest'=>$plan['sha256'],
    'plan_raw_sha256'=>hash_file('sha256',$dir.'/'.$argv[1]),'page_text_updates'=>$pageText,'body_updates'=>$body,
    'continuing_practice_body_updates'=>$practice,'metadata_fields'=>$meta,'metadata_rows'=>count($metaRows),
    'updates'=>count($seen),'inserts'=>5,'deletes'=>0,'question_family_writes'=>0,
    'db_connections'=>0,'db_writes'=>0,'http_requests'=>0,
],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
