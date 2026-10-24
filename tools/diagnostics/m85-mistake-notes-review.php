<?php

/** Pure exact-diff review of a captured preview. No app bootstrap, SQL, HTTP or content writes. */
if(PHP_SAPI!=='cli'||count($argv)!==4||preg_match('/^notes-preview-v[1-9][0-9]*\.json$/D',$argv[1]??'')!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[2]??'')!==1||preg_match('/^notes-review-v[1-9][0-9]*\.json$/D',$argv[3]??'')!==1){
    throw new RuntimeException('Explicit private preview, exact digest and fresh review basename required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m85-local';
if(strtolower(str_replace('\\','/',(string)realpath(dirname(__DIR__,2))))!==strtolower($root)||is_link($dir)){
    throw new RuntimeException('Actual served ROOT/private directory required.');
}
require $root.'/vendor/autoload.php';
use App\Services\M85MistakeNotesPatch as Patch;
$out=$dir.'/'.$argv[3];if(file_exists($out)||is_link($out)){throw new RuntimeException('Exclusive exact review already exists.');}
$preview=Patch::readPinned($dir.'/'.$argv[1]);$copy=$preview;unset($copy['sha256']);
if(($preview['sha256']??null)!==$argv[2]||Patch::digest($copy)!==$argv[2]
    ||($preview['patch']??null)!=='m85-mistake-notes-1.0.0-to-1.0.1'||($preview['state']??null)!=='before'
    ||($preview['names']??null)!==App\Support\M85VerbToBe::OWNERS||array_keys($preview['pages']??[])!==App\Support\M85VerbToBe::OWNERS
    ||count($preview['updates']??[])!==2||($preview['inserts']??null)!==[]||isset($preview['deletes'])||isset($preview['question_supplement'])
    ||count($preview['protected']??[])!==46){throw new RuntimeException('Exact preview digest/two-cell scope differs.');}
[$manifest,$old,$next]=Patch::revision($root);$rows=[];
$baseline=Patch::readPinned($dir.'/'.Patch::BASELINE,Patch::BASELINE_SHA);
$fresh=Patch::readPinned($dir.'/'.Patch::CURRENT_BEFORE,$preview['sources']['private:'.Patch::CURRENT_BEFORE]??'');
foreach(['all_old_rows_and_timestamps_exact','independent_before_projection_exact','reviewed_protected_projection_exact','source_sync_unchanged'] as $field){
    if(($baseline[$field]??null)!==true){throw new RuntimeException('Pinned initial AFTER preservation evidence incomplete.');}
}
$keys=array_keys($baseline['sql_after']??[]);$at=strtotime($fresh['created_at']??'');
if(count($keys)!==46||($baseline['independent_before_sha256']??null)!==App\Services\M85ContentPatch::BEFORE_SHA
    ||($baseline['db_writes']??null)!==0||($baseline['http_requests']??null)!==0
    ||($fresh['phase']??null)!=='current-before-note-repair'||($fresh['baseline_sha256']??null)!==Patch::BASELINE_SHA
    ||($fresh['sql_after']??null)!==$baseline['sql_after']||($fresh['runtime']??null)!==$baseline['runtime']
    ||($fresh['db_writes']??null)!==0||($fresh['http_requests']??null)!==0||!$at||$at>time()+60||time()-$at>1800
    ||array_keys($preview['protected'])!==$keys||($fresh['protected']??null)!==$preview['protected']
    ||array_keys($fresh['owners']??[])!==App\Support\M85VerbToBe::OWNERS){
    throw new RuntimeException('Exact current BEFORE/initial baseline/table keys/protected projection differs.');
}
foreach($keys as $table){if($table!=='text_blocks'&&$preview['protected'][$table]!==$baseline['sql_after'][$table]){
    throw new RuntimeException('One of45 fully protected non-text tables differs from initial AFTER.');
}}
foreach($fresh['owners'] as $owner=>$snapshot){
    if(($preview['pages'][$owner]['page']??null)!==$snapshot['page']||($preview['pages'][$owner]['blocks']??null)!==$snapshot['blocks']){
        throw new RuntimeException('Fresh six-owner full page/block rows or timestamps differ.');
    }
}
$proofKeys=array_values(array_filter(array_keys($preview['sources']),static fn($path)=>preg_match('/^private:local-proof-[a-f0-9]{32}\.json$/D',$path)===1));
if(count($proofKeys)!==1){throw new RuntimeException('Exactly one fresh local proof source required.');}
$sourceState=Patch::sourceState($root,$dir,substr($proofKeys[0],8));
$sourceState['private:'.Patch::CURRENT_BEFORE]=Patch::fileHash($dir.'/'.Patch::CURRENT_BEFORE);ksort($sourceState);
if($sourceState!==$preview['sources']){throw new RuntimeException('Exact current source inventory/proof namespace differs from preview.');}
$capturedState=$sourceState;
foreach(array_keys($capturedState) as $path){if($path==='served:routes/api.php'||$path==='private:'.Patch::CURRENT_BEFORE
    ||str_starts_with($path,'private:local-proof-')||str_starts_with($path,'private:routes-before-')){unset($capturedState[$path]);}}
if(($fresh['sources']??null)!==$capturedState){throw new RuntimeException('Sources differ from independent current BEFORE.');}
foreach(Patch::TARGETS as $n=>$target){
    $i=$target['owner_index'];$slot=$target['source_index'];$a=$old['targets'][$i]['after']['page']['blocks'][$slot]['body'];
    $b=$next['targets'][$i]['after']['page']['blocks'][$slot]['body'];$owner=App\Support\M85VerbToBe::OWNERS[$i];
    $expected=['table'=>'text_blocks','id'=>$target['id'],'uuid'=>$target['uuid'],'seeder'=>$owner,
        'before'=>['body'=>$a],'after'=>['body'=>$b],'before_sha256'=>Patch::digest(['body'=>$a]),'after_sha256'=>Patch::digest(['body'=>$b])];
    if($preview['updates'][$n]!==$expected){throw new RuntimeException('Preview update is not the independently derived exact body.');}
    $found=array_values(array_filter($preview['pages'][$owner]['blocks'],static fn($r)=>$r['id']===$target['id']));
    $captured=array_values(array_filter($fresh['target_rows']??[],static fn($r)=>$r['id']===$target['id']));
    $metadata=$manifest['targets'][$n]['row']+['page_id'=>App\Support\M85VerbToBe::PAGE_IDS[$i],'page_category_id'=>8,'seeder'=>$owner,'body'=>$a];
    if(count($found)!==1||count($captured)!==1||$found[0]!==$captured[0]){throw new RuntimeException('Exact independent current target row/timestamps differ.');}
    foreach($metadata as $key=>$value){if(!array_key_exists($key,$found[0])||$found[0][$key]!==$value){throw new RuntimeException('Exact target metadata differs: '.$key);}}
    $rows[]=['id'=>$target['id'],'uuid'=>$target['uuid'],'identity'=>$owner,'before_body_sha256'=>hash('sha256',$a),
        'after_body_sha256'=>hash('sha256',$b),'before_bytes'=>strlen($a),'after_bytes'=>strlen($b),'notes_copied_from_existing_hints'=>3];
}
foreach($preview['sources'] as $path=>$sha){
    $file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):(str_starts_with($path,'served:')?$root.'/'.substr($path,7):$root.'/'.$path);
    if(Patch::fileHash($file)!==$sha){throw new RuntimeException('Exact reviewed source changed: '.$path);}
}
$receipt=['checked_at'=>gmdate('c'),'preview_file'=>$argv[1],'preview_raw_sha256'=>Patch::fileHash($dir.'/'.$argv[1]),
    'reviewed_digest'=>$argv[2],'manifest_sha256'=>Patch::MANIFEST_SHA,'updates'=>2,'inserts'=>0,'deletes'=>0,'note_additions'=>6,
    'fields'=>['body'],'rows'=>$rows,'six_owner_snapshot'=>true,'protected_tables'=>46,'protected_nontext_tables_exact'=>45,
    'fresh_all_owner_rows_timestamps_exact'=>true,'exact_source_inventory'=>true,'teaching_text_exact_existing_hints'=>true,
    'questions_practices_metadata_and_timestamps_write_scope'=>false,'db_bootstraps'=>0,'db_writes'=>0,'http_requests'=>0];
$bytes=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($out,'x');if(!$h){throw new RuntimeException('Exclusive review unavailable.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('Exact review receipt incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>basename($out),'sha256'=>hash('sha256',$bytes),'updates'=>2,'note_additions'=>6,'db_writes'=>0,'http_requests'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
