<?php

// Read-only saved plan inspection. No app kernel or database boot and no evidence writes.
if(PHP_SAPI!=='cli')exit(1);
$source=dirname(__DIR__,2);$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m39-local';
require $source.'/vendor/autoload.php';$app=new Illuminate\Foundation\Application($source);
$name=$argv[1]??'';
if(!preg_match('/^m39-practice-ui-preview-[a-z0-9-]+\.json$/D',$name))throw new RuntimeException('Invalid private UI preview basename.');
$read=fn($path)=>json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
$plan=$read($dir.'/'.$name);$inventory=$read($dir.'/m39-practice-ui-before-v1.json');
$package=App\Support\M39PracticeUiPackage::load($source);App\Support\M39PracticeUiPackage::validate($package);
$assert=static function($a,$b,$reason):void{if($a!==$b)throw new RuntimeException($reason);};
$assert($plan['state'],'before','UI preview must be before.');
$assert($plan['names'],array_column($package['targets'],'identity'),'UI scope differs.');
$assert(count($plan['updates']),count($package['targets']),'Exact three existing UI bodies required.');
$assert($plan['inserts'],[],'UI patch must never insert.');
$assert($plan['connection'],['driver'=>'mysql','database'=>'gr2','server'=>gethostname(),'port'=>3306],'Wrong physical local UI connection.');
$assert(count($inventory['regressions']),41,'Protected owner set incomplete.');
$assert(count($plan['protected']),20,'All nineteen protected tables plus non-target text_blocks required.');
$assert($inventory['non_target_text_blocks']['count'],$inventory['fingerprints']['text_blocks']['count']-count($plan['updates']),'UI non-target scope excludes rows beyond three practice bodies.');
$copy=$plan;unset($copy['sha256']);$assert(App\Services\M26ContentPatch::digest($copy),$plan['sha256'],'UI plan digest differs.');
foreach($plan['sources'] as $path=>$sha){$actual=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$source.'/'.$path;$assert(hash_file('sha256',$actual),$sha,'UI source stale.');}
foreach($plan['protected'] as $table=>$fp)$assert($fp,$table==='text_blocks'?$inventory['non_target_text_blocks']:$inventory['fingerprints'][$table],'UI protected fingerprint differs.');
foreach($package['targets'] as $i=>$target){
    $old=array_values(array_filter($target['before']['page']['blocks'],fn($block)=>$block['type']==='practice-set'))[0];
    $new=array_values(array_filter($target['after']['page']['blocks'],fn($block)=>$block['type']==='practice-set'))[0];
    $change=$plan['updates'][$i];$fidelity=$inventory['source_fidelity'][$target['identity']];
    $assert($fidelity['state'],'practice_ui_before','Actual UI before gate missing.');$assert($fidelity['practice_source_exact'],true,'Actual UI before fidelity failed.');
    $assert($change['id'],$fidelity['practice_id'],'Existing practice ID differs.');$assert($change['uuid'],$fidelity['practice_uuid'],'Existing practice UUID differs.');
    $assert($change['table'],'text_blocks','UI table differs.');$assert($change['seeder'],$target['identity'],'UI owner differs.');
    $assert($change['before'],['body'=>$old['body']],'UI old body differs from accepted v1.');$assert($change['after'],['body'=>$new['body']],'UI changed fields exceed body.');
}
$backupName=$argv[2]??'';
if(!preg_match('/^source-backup-practice-ui-[a-f0-9]{16}$/D',$backupName))throw new RuntimeException('Exclusive UI source backup required.');
$records=$read($dir.'/'.$backupName.'/manifest.json');
$expectedPaths=['app/Support/M39PracticeUiPackage.php','app/Services/M39PracticeUiPatch.php','app/Console/Commands/PatchM39PracticeUi.php',
    'tools/diagnostics/run-m39-practice-ui-working-local.php',App\Support\M39PracticeUiPackage::SOURCE,
    ...array_column($package['targets'],'path'),'resources/views/engram/theory/blocks-v3/m39-practice-ui.blade.php','public/js/m39-practice-ui.js',
    ...App\Services\M39PracticeUiPatch::SHARED_VIEWS];
$assert(array_column($records,'path'),$expectedPaths,'Exact finite UI source backup allowlist differs.');
foreach($records as $record){if($record['before_sha256']!==null)$assert(hash_file('sha256',$dir.'/'.$backupName.'/'.$record['path']),$record['before_sha256'],'UI source backup incomplete.');$assert(hash_file('sha256',$root.'/'.$record['path']),$record['after_sha256'],'UI working source differs.');$assert(hash_file('sha256',$source.'/'.$record['path']),$record['worktree_sha256'],'UI worktree source differs.');}
echo json_encode(['exact_review'=>true,'updated'=>count($plan['updates']),'inserted'=>0,'deleted'=>0,'protected_owners'=>count($inventory['regressions']),
    'protected_non_target_blocks'=>$inventory['non_target_text_blocks'],'source_backup_verified'=>true,'no_db_access'=>true,'no_evidence_writes'=>true])."\n";
