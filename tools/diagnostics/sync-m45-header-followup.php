<?php

// One exact native-header hunk; frozen author/DB content remains unchanged.
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array($argv[1]??'', ['--preview','--apply-reviewed'],true))exit(1);
$source=dirname(__DIR__,2);$root='D:/DEV/htdocs/gramlyze.loc';$private=$root.'/storage/app/seo-m45-local';
require $source.'/vendor/autoload.php';new Illuminate\Foundation\Application($source);
$path='resources/views/theory/show.blade.php';
$read=static function(string $file):string{if(!is_file($file)||is_link($file))throw new RuntimeException('Missing/linked header followup source');return file_get_contents($file);};
$json=static fn(array $v)=>json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$exclusive=static function(string $file,string $bytes):void{if(!is_dir(dirname($file)))mkdir(dirname($file),0700,true);$h=fopen($file,'xb');if(!$h)throw new RuntimeException('Header followup collision');try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h))throw new RuntimeException('Incomplete header followup');}finally{fclose($h);}};
$receiptBytes=$read($private.'/source-sync.json');
if(hash('sha256',$receiptBytes)!=='1849c3591eb2a569007ba3b82128feb7dc54f1bd2dfcb9b9d31349725d573823')throw new RuntimeException('Title followup receipt changed');
$old=$read($root.'/'.$path);$wt=$read($source.'/'.$path);
if(hash('sha256',$old)!=='86b928cb26af200068f50658bbb191469b5070a60c3281eb2debaa6d3962dd95')throw new RuntimeException('Concurrent header edit');
$before='                    <h1 class="mt-4 max-w-4xl font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">{{ $page->title }}</h1>'."\n".'                    @if(!empty($heroData[\'intro\']))'."\n";
$after='                    <h1 class="mt-4 max-w-4xl font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">{{ $page->title }}</h1>'."\n".'                    @if($m45StyleContext)'."\n".'                        <p lang="uk" data-m45-subtitle class="mt-4 max-w-3xl text-sm leading-7 sm:text-base text-muted-foreground">{{ $page->getRawOriginal(\'text\') }}</p>'."\n".'                    @endif'."\n".'                    @if(!empty($heroData[\'intro\']))'."\n";
$hunks=[['before'=>$before,'after'=>$after]];$new=\App\Services\M44ContentPatch::projectSharedFragments($old,$hunks,true);
$receipt=json_decode($receiptBytes,true,flags:JSON_THROW_ON_ERROR);
$record=array_values(array_filter($receipt['files'],fn($r)=>$r['path']===$path))[0];
$base=$read($private.'/'.$record['base_basename']);
if(hash('sha256',$base)!==$record['base_sha256'])throw new RuntimeException('Header WT base changed');
$wtBefore=\App\Services\M44ContentPatch::projectSharedFragments($base,$record['hunks']);
if(\App\Services\M44ContentPatch::projectSharedFragments($wtBefore,$hunks)!==str_replace("\r\n","\n",$wt))throw new RuntimeException('Unreviewed WT header hunks');
$proposal=['scope'=>[$path],'master_sha256'=>\App\Support\M45FutureComparisonsPackage::MASTER_SHA,'db_writes'=>false,
    'root_before_sha256'=>hash('sha256',$old),'root_after_sha256'=>hash('sha256',$new),'worktree_sha256'=>hash('sha256',$wt),'hunks'=>$hunks];
$bytes=$json($proposal);$proposalFile=$private.'/header-followup-proposal-v1.json';
if($argv[1]==='--preview'){$exclusive($proposalFile,$bytes);echo $json(['status'=>'preview','sha256'=>hash('sha256',$bytes),'proposal'=>$proposal]);exit;}
if(count($argv)!==3||$argv[2]!==hash('sha256',$bytes)||$read($proposalFile)!==$bytes)throw new RuntimeException('Header followup reviewed SHA differs');
$backup=$private.'/source-sync-header-followup-before-v1';if(file_exists($backup))throw new RuntimeException('Backup already present');
$exclusive($backup.'/source-sync.json',$receiptBytes);$exclusive($backup.'/'.$path,$old);
if($read($root.'/'.$path)!==$old||$read($source.'/'.$path)!==$wt||$read($private.'/source-sync.json')!==$receiptBytes)throw new RuntimeException('Concurrent source edit');
if(file_put_contents($root.'/'.$path,$new,LOCK_EX)!==strlen($new)||$read($root.'/'.$path)!==$new)throw new RuntimeException('Header source write failed');
$receipt=json_decode($receiptBytes,true,flags:JSON_THROW_ON_ERROR);
foreach($receipt['files'] as &$record)if($record['path']===$path){$record['root_after_sha256']=$proposal['root_after_sha256'];$record['worktree_sha256']=$proposal['worktree_sha256'];$record['after_bytes']=strlen($new);$record['hunks']=[...$record['hunks'],...$hunks];}unset($record);
$receipt['technical_header_followup']=$proposal;$newReceipt=$json($receipt);
if(file_put_contents($private.'/source-sync.json',$newReceipt,LOCK_EX)!==strlen($newReceipt))throw new RuntimeException('Header receipt write failed');
echo $json(['status'=>'synced-native-subtitle','source_files'=>count($receipt['files']),'receipt_sha256'=>hash('sha256',$newReceipt),'db_writes'=>false]);
