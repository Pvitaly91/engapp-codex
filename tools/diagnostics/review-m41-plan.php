<?php

// Independent exact saved-plan/source-backup review. No app kernel or database access.
if(PHP_SAPI!=='cli')exit(1);
$source=dirname(__DIR__,2);$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m41-local';
require $source.'/vendor/autoload.php';$app=new Illuminate\Foundation\Application($source);
$name=$argv[1]??'';$backupName=$argv[2]??'';
if(!preg_match('/^m41-preview-[a-z0-9-]+\.json$/D',$name)||!preg_match('/^source-backup-[a-f0-9]{16}$/D',$backupName))throw new RuntimeException('Exact private M41 preview/source-backup names required.');
$read=fn($path)=>json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$assert=static function($a,$b,$m):void{if($a!==$b)throw new RuntimeException($m);};
$plan=$read($dir.'/'.$name);$inventory=$read($dir.'/m41-before-v1.json');[$before,$package]=App\Support\M41AuthoredTenseComparisonsPackage::load($source);App\Support\M41AuthoredTenseComparisonsPackage::validate($before,$package);
$assert($plan['state'],'before','M41 review requires unapplied state.');$copy=$plan;unset($copy['sha256']);$assert(App\Services\M26ContentPatch::digest($copy),$plan['sha256'],'Saved M41 digest differs.');
$assert($plan['names'],array_column($package['targets'],'identity'),'M41 owner set differs.');
$assert($plan['connection'],['driver'=>'mysql','database'=>'gr2','server'=>gethostname(),'port'=>3306],'Wrong physical M41 target.');
$assert(count($inventory['regressions']),47,'All prior M26–M40 owners required.');$assert(array_keys($plan['protected']),array_keys($inventory['fingerprints']),'Protected table set differs.');
foreach($plan['protected'] as $table=>$fp)$assert($fp,$table==='text_blocks'?$inventory['non_target_text_blocks']:$inventory['fingerprints'][$table],'Protected fingerprint differs.');
foreach($plan['sources'] as $path=>$sha){$p=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$source.'/'.$path;$assert(hash_file('sha256',$p),$sha,'Stale source '.$path);}
$expectedUpdates=[];$expectedInserts=[];
foreach($package['targets'] as $i=>$target){
    $identity=$target['identity'];$old=$before['targets'][$i]['before'];$new=$target['after'];$owner=$plan['pages'][$identity]['page'];$rows=$plan['pages'][$identity]['blocks'];
    $assert($inventory['source_fidelity'][$identity]['state'],'accepted_before','Actual before gate missing.');$assert($inventory['source_fidelity'][$identity]['source_db_exact'],true,'Actual source before gate failed.');
    $assert($owner['id'],$inventory['targets'][$i]['page_id'],'Actual owner ID differs.');$assert($owner['page_category_id'],$inventory['targets'][$i]['category_id'],'Actual leaf category differs.');
    $assert(array_column($plan['pages'][$identity]['category_ancestry'],'slug'),['tenses'],'Wrong M41 root category.');
    if($old['page']['subtitle_text']!==$new['page']['subtitle_text'])$expectedUpdates[]=['table'=>'pages','id'=>$owner['id'],'uuid'=>null,'seeder'=>$identity,'before'=>['text'=>$old['page']['subtitle_text']],'after'=>['text'=>$new['page']['subtitle_text']]];
    $subtitle=['type'=>'subtitle','body'=>$old['page']['subtitle_html'],'uuid_key'=>$old['page']['subtitle_uuid_key']??'subtitle'];
    $afterSubtitle=$subtitle;$afterSubtitle['body']=$new['page']['subtitle_html'];$oldConfigs=[$subtitle,...$old['page']['blocks']];$newConfigs=[$afterSubtitle,...$new['page']['blocks']];
    foreach($oldConfigs as $position=>$config){
        $updated=$newConfigs[$position];if($config['type']===$updated['type']&&$config['body']===$updated['body'])continue;
        $uuid=App\Support\M26DetailPackage::uuid($identity,$config,$position);$matches=array_values(array_filter($rows,fn($row)=>$row['uuid']===$uuid));$assert(count($matches),1,'Existing update row missing/ambiguous.');
        $row=$matches[0];$expectedUpdates[]=['table'=>'text_blocks','id'=>$row['id'],'uuid'=>$uuid,'seeder'=>$identity,'before'=>['type'=>$config['type'],'body'=>$config['body']],'after'=>['type'=>$updated['type'],'body'=>$updated['body']]];
    }
    foreach(array_slice($newConfigs,count($oldConfigs),null,true) as $position=>$config){
        $fields=['uuid'=>App\Support\M26DetailPackage::uuid($identity,$config,$position),'page_id'=>$owner['id'],'page_category_id'=>$owner['page_category_id'],'locale'=>'uk','type'=>$config['type'],'column'=>$config['column'],
            'heading'=>$config['heading']??null,'css_class'=>$config['css_class']??null,'sort_order'=>$position,'body'=>$config['body'],'level'=>$config['level']??null,'seeder'=>$identity];
        $expectedInserts[]=['table'=>'text_blocks','seeder'=>$identity,'fields'=>$fields,'sha256'=>App\Services\M26ContentPatch::digest($fields)];
    }
}
$actualUpdates=array_map(function($change){unset($change['before_sha256'],$change['after_sha256']);return $change;},$plan['updates']);
$assert($actualUpdates,$expectedUpdates,'M41 exact update fields/count differ.');$assert($plan['inserts'],$expectedInserts,'M41 exact source-derived inserts differ.');
$records=$read($dir.'/'.$backupName.'/manifest.json');
$expectedSourcePaths=[
    'app/Support/M41AuthoredTenseComparisonsPackage.php','app/Services/M41ContentPatch.php','app/Services/M41LocalTargetGuard.php',
    'app/Console/Commands/PatchM41Content.php','tools/diagnostics/run-m41-working-local.php',
    App\Support\M41AuthoredTenseComparisonsPackage::BEFORE,App\Support\M41AuthoredTenseComparisonsPackage::SOURCE,
    App\Support\M41AuthoredTenseComparisonsPackage::MASTER_PATH,App\Support\M41AuthoredTenseComparisonsPackage::CORRECTION_PATH,App\Support\M41AuthoredTenseComparisonsPackage::APPROVAL_PATH,
    ...array_column($package['targets'],'path'),
    'resources/views/engram/theory/blocks-v3/m41-section-styles.blade.php','resources/views/engram/theory/blocks-v3/m41-author-section.blade.php',
    'resources/views/engram/theory/blocks-v3/m41-practice-ui.blade.php','public/js/m41-practice-ui.js',
    ...App\Services\M41ContentPatch::SHARED_VIEWS,
];
$assert(array_column($records,'path'),$expectedSourcePaths,'Finite M41 source-backup allowlist differs.');
foreach($records as $record)$assert($record['mode'],in_array($record['path'],App\Services\M41ContentPatch::SHARED_VIEWS,true)?'shared-verify-only':'finite-sync','M41 source-backup write mode differs.');
foreach($records as $r){if($r['before_sha256']!==null)$assert(hash_file('sha256',$dir.'/'.$backupName.'/'.$r['path']),$r['before_sha256'],'Source backup original missing.');$assert(hash_file('sha256',$root.'/'.$r['path']),$r['after_sha256'],'Root source differs.');$assert(hash_file('sha256',$source.'/'.$r['path']),$r['worktree_sha256'],'WT source differs.');}
echo json_encode(['exact_field_review'=>true,'updated'=>count($expectedUpdates),'inserted'=>count($expectedInserts),'deleted'=>0,'source_backup_records'=>count($records),'protected_owners'=>47,'protected_tables'=>count($plan['protected']),'no_db_access'=>true,'no_evidence_writes'=>true])."\n";
