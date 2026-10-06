<?php

// Exact saved SELECT comparison; no app kernel, configuration mutation or database access.
if(PHP_SAPI!=='cli')exit(1);
$source=dirname(__DIR__,2);$dir='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-local';
require $source.'/vendor/autoload.php';$app=new Illuminate\Foundation\Application($source);
$name=$argv[1]??'';$previewName=$argv[2]??'m41-preview-v1.json';
if(!preg_match('/^m41-after-[a-z0-9-]+\.json$/D',$name)||!preg_match('/^m41-preview-[a-z0-9-]+\.json$/D',$previewName))throw new RuntimeException('Invalid private M41 evidence basename.');
$read=fn($p)=>json_decode(file_get_contents($p),true,flags:JSON_THROW_ON_ERROR);$assert=static function($a,$b,$m):void{if($a!==$b)throw new RuntimeException($m);};
$old=$read($dir.'/m41-before-v1.json');$new=$read($dir.'/'.$name);$preview=$read($dir.'/'.$previewName);$copy=$preview;unset($copy['sha256']);$assert(App\Services\M26ContentPatch::digest($copy),$preview['sha256'],'Reviewed preview digest differs.');
$assert($read($dir.'/m41-backup-v1.json'),$preview,'Exclusive backup differs from exact reviewed before plan.');
[, $package]=App\Support\M41AuthoredTenseComparisonsPackage::load($source);
foreach(['target','linked_banks','regressions','progress_tables','non_target_text_blocks'] as $field)$assert($new[$field],$old[$field],'Protected M41 evidence changed: '.$field);
$assert(count($new['regressions']),47,'All prior owner protections required.');$assert($new['non_target_text_blocks'],$preview['protected']['text_blocks'],'Non-target text block changed.');
$assert(array_keys($new['fingerprints']),array_keys($old['fingerprints']),'Protected table set differs.');
foreach($new['fingerprints'] as $table=>$fp)if($table!=='text_blocks')$assert($fp,$old['fingerprints'][$table],'Protected table changed: '.$table);
$assert($new['fingerprints']['text_blocks']['count'],$old['fingerprints']['text_blocks']['count']+count($preview['inserts']),'Unexpected M41 total row count.');
foreach($package['targets'] as $i=>$t){
    $before=$old['targets'][$i];$after=$new['targets'][$i];
    $assert($new['source_fidelity'][$t['identity']]['state'],'author_after','Actual after state missing.');
    $assert($new['source_fidelity'][$t['identity']]['source_db_exact'],true,'Actual after source fidelity failed.');
    foreach(['identity','slug','page_id','category_id','category_slug','category_title','category_sha256','category_ancestry','category_chain_sha256','page_protected_sha256','linked_bank_groups','global_bank_levels','linked_bank_ids'] as $f)$assert($after[$f],$before[$f],'Protected owner field changed.');
    foreach($t['after']['page']['blocks'] as $j=>$config){$uuid=App\Support\M26DetailPackage::uuid($t['identity'],$config,$j+1);$matches=array_values(array_filter($after['blocks'],fn($row)=>$row['uuid']===$uuid));$assert(count($matches),1,'Native UUID missing/ambiguous.');$row=$matches[0];$assert([$row['type'],$row['locale'],$row['order'],$row['body_sha256']],[$config['type'],'uk',$j+1,hash('sha256',$config['body'])],'Native after body/type/order differs.');}
    foreach($before['blocks'] as $row){
        $matches=array_values(array_filter($after['blocks'],fn($r)=>$r['id']===$row['id']));$assert(count($matches),1,'Existing ID lost.');$actual=$matches[0];
        foreach(['id','uuid','locale','order'] as $f)$assert($actual[$f],$row[$f],'Existing metadata identity changed.');
        $savedRows=array_values(array_filter($preview['pages'][$t['identity']]['blocks'],fn($r)=>$r['id']===$row['id']));$assert(count($savedRows),1,'Existing raw preview row missing.');
        $assert($actual['metadata'],array_diff_key($savedRows[0],['type'=>true,'body'=>true]),'Existing full metadata/timestamps changed.');
        if($row['locale']!=='uk'){$a=$actual;$b=$row;unset($a['metadata'],$b['metadata']);$assert($a,$b,'Other locale row changed.');}
    }
    foreach(array_filter($preview['inserts'],fn($r)=>$r['seeder']===$t['identity']) as $insert){
        $matches=array_values(array_filter($after['blocks'],fn($r)=>$r['uuid']===$insert['fields']['uuid']));$assert(count($matches),1,'Inserted row missing/ambiguous.');$metadata=$matches[0]['metadata'];
        if(empty($metadata['created_at'])||$metadata['created_at']!==$metadata['updated_at'])throw new RuntimeException('Inserted timestamps invalid.');
        unset($metadata['id'],$metadata['created_at'],$metadata['updated_at']);
        $assert($metadata,array_diff_key($insert['fields'],['type'=>true,'body'=>true]),'Inserted metadata differs.');
    }
    foreach($old['anchor_reference_inventory']['sources'][$t['identity']]['ids'] as $id)if(!in_array($id,$new['anchor_reference_inventory']['sources'][$t['identity']]['ids'],true))throw new RuntimeException('Existing anchor lost.');
}
echo json_encode(['pass'=>true,'updated'=>count($preview['updates']),'inserted'=>count($preview['inserts']),'deleted'=>0,'text_blocks_after'=>$new['fingerprints']['text_blocks']['count'],
    'unchanged_protected_fingerprints'=>count($new['fingerprints']),'unchanged_raw_tables'=>count($new['fingerprints'])-2,
    'allowed_pages_text_updates'=>count(array_filter($preview['updates'],fn($u)=>$u['table']==='pages')),
    'unchanged_prior_owners'=>count($new['regressions']),'actual_source_bodies_exact'=>true,'exclusive_backup_exact'=>true])."\n";
