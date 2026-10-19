<?php

// Technical, finite three-file source correction only; author/DB payloads remain frozen.
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array($argv[1]??'', ['--preview','--apply-reviewed'],true)){exit(1);}
$source=dirname(__DIR__,2);$root='D:/DEV/htdocs/gramlyze.loc';$private=$root.'/storage/app/seo-m45-local';
require $source.'/vendor/autoload.php';new Illuminate\Foundation\Application($source);
\App\Support\M45FutureComparisonsPackage::load($source);
$pins=[
    'app/Support/M45FutureComparisonsPackage.php'=>'ef82ae454bd93a0382b411f663ffaeab5b51fe7e2575e360987ef7254eb5c7d9',
    'app/Services/M45ContentPatch.php'=>'42d4613013288442b0f8a31a956deb130ce78905b72fecf6901ceedf9f18c697',
    'app/Http/Controllers/PageController.php'=>'f397624ad522fa1837baaf9a0201a80c1ca748a92e6e3dc21d631891ef5b60df',
];
$hunks=[['before'=>"                \$localizedTitle = \$localizedTitles[\$modelId][\$locale] ?? null;\n\n                if (is_string(\$localizedTitle) && \$localizedTitle !== '') {\n",
    'after'=>"                \$localizedTitle = \$localizedTitles[\$modelId][\$locale] ?? null;\n\n                if (\$model instanceof Page) {\n                    \$localizedTitle = \\App\\Support\\M45FutureComparisonsPackage::displayTitle(\$model, \$locale) ?? \$localizedTitle;\n                }\n\n                if (is_string(\$localizedTitle) && \$localizedTitle !== '') {\n"]];
$read=static function(string $path):string{if(!is_file($path)||is_link($path))throw new RuntimeException('Missing/linked M45 followup input');return file_get_contents($path);};
$json=static fn(array $value)=>json_encode($value,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n";
$exclusive=static function(string $file,string $bytes):void{
    if(!is_dir(dirname($file)))mkdir(dirname($file),0700,true);
    $h=fopen($file,'xb');if(!$h)throw new RuntimeException('Exclusive M45 followup collision');
    try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h))throw new RuntimeException('Incomplete followup backup');}finally{fclose($h);}
};
$receiptBytes=$read($private.'/source-sync.json');
if(hash('sha256',$receiptBytes)!=='7072b699b1dcecc831a59d067ba36a73742c56d73ddbc471cf545e060084cb8b')throw new RuntimeException('Initial sync receipt changed');
$receipt=json_decode($receiptBytes,true,flags:JSON_THROW_ON_ERROR);$pending=[];$records=[];
foreach($pins as $path=>$pin){
    $old=$read($root.'/'.$path);$wt=$read($source.'/'.$path);
    if(hash('sha256',$old)!==$pin)throw new RuntimeException('Concurrent ROOT followup change: '.$path);
    $isController=$path==='app/Http/Controllers/PageController.php';
    $new=$isController?\App\Services\M44ContentPatch::projectSharedFragments($old,$hunks,true):$wt;
    if($isController && str_replace("\r\n","\n",$new)!==str_replace("\r\n","\n",$wt))throw new RuntimeException('Controller has unreviewed WT hunks');
    $pending[$path]=['old'=>$old,'new'=>$new,'wt'=>$wt];
    $records[]=['path'=>$path,'before_sha256'=>$pin,'root_after_sha256'=>hash('sha256',$new),'worktree_sha256'=>hash('sha256',$wt),
        'hunks'=>$isController?$hunks:[],'mode'=>$isController?'shared-reviewed-hunks':'owned-m45-code'];
}
$proposal=['schema'=>'m45-title-technical-followup-v1','base_sha'=>\App\Support\M45FutureComparisonsPackage::BASE_SHA,
    'author_sha256'=>\App\Support\M45FutureComparisonsPackage::MASTER_SHA,'original_receipt_sha256'=>hash('sha256',$receiptBytes),
    'db_writes'=>false,'reason'=>'Preserve controller-derived UK title for exactly three own Page identities; plain subtitle is explanation, not a new title.', 'files'=>$records];
$proposalBytes=$json($proposal);$name=$private.'/title-followup-proposal-v1.json';
if($argv[1]==='--preview'){$exclusive($name,$proposalBytes);echo $json(['status'=>'preview','sha256'=>hash('sha256',$proposalBytes),'files'=>$records]);exit;}
if(count($argv)!==3||$argv[2]!==hash('sha256',$proposalBytes)||$read($name)!==$proposalBytes)throw new RuntimeException('Followup reviewed SHA differs');
$backup=$private.'/source-sync-title-followup-before-v1';
if(file_exists($backup))throw new RuntimeException('Followup backup already exists');
$exclusive($backup.'/source-sync.json',$receiptBytes);
foreach($pending as $path=>$item)$exclusive($backup.'/'.$path,$item['old']);
foreach($pending as $path=>$item)if($read($root.'/'.$path)!==$item['old']||$read($source.'/'.$path)!==$item['wt'])throw new RuntimeException('Concurrent source edit before followup');
foreach($pending as $path=>$item){
    if(file_put_contents($root.'/'.$path,$item['new'],LOCK_EX)!==strlen($item['new'])||$read($root.'/'.$path)!==$item['new'])throw new RuntimeException('Followup source write failed');
}
$byPath=array_column($receipt['files'],null,'path');
foreach($records as $record){
    $row=$byPath[$record['path']]??['path'=>$record['path'],'root_before_sha256'=>$record['before_sha256'],'before_bytes'=>strlen($pending[$record['path']]['old']),
        'mode'=>'shared-reviewed-hunks','hunks'=>$hunks,'backup_basename'=>'source-sync-title-followup-before-v1/'.$record['path']];
    $row['root_after_sha256']=$record['root_after_sha256'];$row['worktree_sha256']=$record['worktree_sha256'];$row['after_bytes']=strlen($pending[$record['path']]['new']);
    $byPath[$record['path']]=$row;
}
ksort($byPath);$receipt['files']=array_values($byPath);$receipt['technical_title_followup']=$proposal;
$newReceipt=$json($receipt);
if($read($private.'/source-sync.json')!==$receiptBytes)throw new RuntimeException('Concurrent receipt change');
if(file_put_contents($private.'/source-sync.json',$newReceipt,LOCK_EX)!==strlen($newReceipt))throw new RuntimeException('Followup receipt write failed');
echo $json(['status'=>'synced-title-technical-followup','source_files'=>count($receipt['files']),'receipt_sha256'=>hash('sha256',$newReceipt),'db_writes'=>false]);
