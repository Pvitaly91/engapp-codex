<?php

// M45-only browser-state fix; no shared JS, author payload or DB mutation.
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||($argv[1]??'')!=='--apply-reviewed'
    ||!in_array($argv[2]??'',['cfeb092aed8a3cdefa23e0a35a046cb51aa678ad23c58792e227b18bd2639e1a','807649a2eff21f8212ee82ddabc60c096a18d5461a155b4157dfcde86fab929d'],true))exit(1);
$second=$argv[2]==='807649a2eff21f8212ee82ddabc60c096a18d5461a155b4157dfcde86fab929d';
$oldSha=$second?'cfeb092aed8a3cdefa23e0a35a046cb51aa678ad23c58792e227b18bd2639e1a':'45c026404ab1bc3f3438ea1d51590103a75f2290d06d2efacb316a6494c72e03';
$receiptSha=$second?'f9b9cdf84158705d6a4eb8fd840bdaf0fcb94f7a45438f579a82431df3f927b4':'a54da3311f0f6a7a59dccddbb8364d4b83d9a7f7ee7a7b183ae27c025acc0898';
$source=dirname(__DIR__,2);$root='D:/DEV/htdocs/gramlyze.loc';$private=$root.'/storage/app/seo-m45-local';$path='public/js/m45-practice-ui.js';
$old=file_get_contents($root.'/'.$path);$new=file_get_contents($source.'/'.$path);$receiptBytes=file_get_contents($private.'/source-sync.json');
if(is_link($root.'/'.$path)||is_link($source.'/'.$path)||is_link($private.'/source-sync.json')
    ||hash('sha256',$old)!==$oldSha
    ||hash('sha256',$new)!==$argv[2]||hash('sha256',$receiptBytes)!==$receiptSha){
    throw new RuntimeException('M45 token followup source/receipt changed; no writes');
}
$directory=$private.'/source-sync-token-followup-before-v'.($second?'2':'1');if(file_exists($directory))throw new RuntimeException('Exclusive token backup collision');
mkdir($directory,0700,true);
$exclusive=static function(string $file,string $bytes):void{$h=fopen($file,'xb');if(!$h)throw new RuntimeException('Exclusive evidence collision');try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h))throw new RuntimeException('Incomplete token evidence');}finally{fclose($h);}};
$exclusive($directory.'/m45-practice-ui.js',$old);$exclusive($directory.'/source-sync.json',$receiptBytes);
if(file_get_contents($root.'/'.$path)!==$old||file_get_contents($source.'/'.$path)!==$new||file_get_contents($private.'/source-sync.json')!==$receiptBytes)throw new RuntimeException('Concurrent source edit');
if(file_put_contents($root.'/'.$path,$new,LOCK_EX)!==strlen($new)||hash_file('sha256',$root.'/'.$path)!==$argv[2])throw new RuntimeException('Token source sync failed');
$receipt=json_decode($receiptBytes,true,flags:JSON_THROW_ON_ERROR);
foreach($receipt['files'] as &$record)if($record['path']===$path){$record['root_after_sha256']=$argv[2];$record['worktree_sha256']=$argv[2];$record['after_bytes']=strlen($new);}unset($record);
$receipt['practice_token_followup']=['path'=>$path,'before_sha256'=>hash('sha256',$old),'after_sha256'=>$argv[2],
    'scope'=>$second?'M45 bare-question tokens track literal question marks, attached or separate, in history order; shared word/grade matching unchanged':'M45 empty-answer usedTokens returns an empty Set; nonempty matching delegates unchanged shared factory','db_writes'=>false];
$bytes=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n";
if(file_put_contents($private.'/source-sync.json',$bytes,LOCK_EX)!==strlen($bytes))throw new RuntimeException('Token receipt sync failed');
echo json_encode(['status'=>'synced-m45-only-token-empty-fix','source_files'=>count($receipt['files']),'receipt_sha256'=>hash('sha256',$bytes),'db_writes'=>false],JSON_THROW_ON_ERROR)."\n";
