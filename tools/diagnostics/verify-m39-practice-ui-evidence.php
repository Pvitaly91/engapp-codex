<?php

// Independent saved SELECT evidence comparison; no app kernel or DB access.
if(PHP_SAPI!=='cli')exit(1);
$source=dirname(__DIR__,2);$dir='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m39-local';
require $source.'/vendor/autoload.php';$app=new Illuminate\Foundation\Application($source);
$name=$argv[1]??'';$preview=$argv[2]??'m39-practice-ui-preview-v1.json';
if(!preg_match('/^m39-practice-ui-after-[a-z0-9-]+\.json$/D',$name)||!preg_match('/^m39-practice-ui-preview-[a-z0-9-]+\.json$/D',$preview))throw new RuntimeException('Invalid UI evidence basename.');
$read=fn($path)=>json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
$before=$read($dir.'/m39-practice-ui-before-v1.json');$after=$read($dir.'/'.$name);$plan=$read($dir.'/'.$preview);
$assert=static function($a,$b,$reason):void{if($a!==$b)throw new RuntimeException($reason);};
$copy=$plan;unset($copy['sha256']);$assert(App\Services\M26ContentPatch::digest($copy),$plan['sha256'],'UI preview digest differs.');
foreach(['target','linked_banks','regressions','non_target_text_blocks'] as $field)$assert($after[$field],$before[$field],'UI protected evidence changed: '.$field);
$assert($after['non_target_text_blocks'],$plan['protected']['text_blocks'],'Any non-UI text block changed.');
foreach($after['fingerprints'] as $table=>$fp)if($table!=='text_blocks')$assert($fp,$before['fingerprints'][$table],'UI protected table changed.');
$assert($after['fingerprints']['text_blocks']['count'],$before['fingerprints']['text_blocks']['count'],'UI inserted/deleted rows.');
foreach($plan['updates'] as $i=>$change){
    $old=$before['targets'][$i];$new=$after['targets'][$i];
    foreach(['identity','slug','page_id','category_id','page_sha256','category_sha256','category_chain_sha256','linked_bank_groups','global_bank_levels','linked_bank_ids'] as $field)$assert($new[$field],$old[$field],'UI owner metadata changed.');
    $assert(count($new['blocks']),count($old['blocks']),'UI owner row count changed.');
    foreach($old['blocks'] as $j=>$block){$actual=$new['blocks'][$j];if($block['id']===$change['id']){$block['body_sha256']=hash('sha256',$change['after']['body']);}$assert($actual,$block,'UI changed fields beyond exact practice body.');}
}
echo json_encode(['pass'=>true,'updated'=>count($plan['updates']),'inserted'=>0,'deleted'=>0,'unchanged_tables'=>count($after['fingerprints'])-1,
    'unchanged_owners'=>count($after['regressions']),'text_blocks'=>$after['fingerprints']['text_blocks']['count'],
    'protected_non_target_blocks'=>$after['non_target_text_blocks']])."\n";
