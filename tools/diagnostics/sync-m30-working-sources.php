<?php

// Explicit local mechanical source sync only. Never touches .env, routes, assets, vendor, caches or DB.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ($argv[1]??'') !== '--apply') { exit(1); }
$source=dirname(__DIR__,2); $root='D:/DEV/htdocs/gramlyze.loc';
require $source.'/vendor/autoload.php';
$head=new Symfony\Component\Process\Process(['git','rev-parse','HEAD'],$root); $head->mustRun();
if(trim($head->getOutput())!=='41820a2bebdf69004fa7209a2a38457f93efabbd'){throw new RuntimeException('Original working checkout HEAD changed.');}
$files=[
    'app/Support/M29SentenceStructurePackage.php', 'database/content-patches/m29-m13-sentence-structure.v2.json',
    'app/Support/M30ParticipleClausesPackage.php','app/Services/M30ContentPatch.php','app/Services/M30LocalTargetGuard.php',
    'app/Console/Commands/PatchM30Content.php','database/content-patches/m30-m14-participle-clauses-before.json',
    'database/content-patches/m30-m14-participle-clauses.v1.json',
    'database/seeders/Page_V3/ClausesAndLinkingWords/ParticipleClausesBasicsTheorySeeder/definition.json',
    'database/seeders/Page_V3/ClausesAndLinkingWords/ParticipleClausesTheorySeeder/definition.json',
    'database/seeders/Page_V3/ClausesAndLinkingWords/AdvancedParticipleAndAbsoluteClausesTheorySeeder/definition.json',
    'resources/views/theory/partials/content-block.blade.php',
    'resources/views/theory/partials/point-detail-fragment.blade.php',
    'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
    'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
    'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
    'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
];
$normal=fn($x)=>str_replace("\r\n","\n",$x); $records=[]; $pending=[];
foreach($files as $path){
    if(is_link($root.'/'.$path) || is_link($source.'/'.$path)
        || !str_starts_with(strtolower(str_replace('\\','/',realpath(dirname($root.'/'.$path)))),strtolower($root).'/')){
        throw new RuntimeException('Source sync path is not an owned regular file: '.$path);
    }
    $new=file_get_contents($source.'/'.$path); if($new===false){throw new RuntimeException('Missing finite source: '.$path);}
    $old=is_file($root.'/'.$path)?file_get_contents($root.'/'.$path):null;
    if($old!==null && $normal($old)!==$normal($new)){
        $accepted=false;
        // ROOT retains older tracked M14 sources; accept their exact original checkout blob,
        // never an arbitrary dirty file. The working DB already has the accepted M14 author copy.
        foreach(['2a5043f9de7307bbb41a620df24729e214fa3f75','0b7538ebe2303e8ebac00a9afcf825339bf2b256','c308b328ece596d92f91943d9dfb2495fc9af10a','41820a2bebdf69004fa7209a2a38457f93efabbd'] as $sha){
            $p=new Symfony\Component\Process\Process(['git','show',$sha.':'.$path],$source);
            $p->run(); if($p->isSuccessful() && $normal($p->getOutput())===$normal($old)){$accepted=true;break;}
        }
        if(!$accepted){throw new RuntimeException('Unrelated working edit/conflict; refusing source sync: '.$path);}
    }
    $records[]=['path'=>$path,'before_sha256'=>$old===null?null:hash('sha256',$old),'after_sha256'=>hash('sha256',$new)];
    $pending[$path]=[$old,$new];
}
$directory=$root.'/storage/app/seo-m30-local/source-backup-'.bin2hex(random_bytes(8));
if(!mkdir($directory,0700)){throw new RuntimeException('Exclusive source backup failed.');}
foreach($pending as $path=>[$old,$new]){
    if($old!==null){$b=$directory.'/'.$path; if(!is_dir(dirname($b))){mkdir(dirname($b),0700,true);} $f=fopen($b,'x');if(!$f){throw new RuntimeException('Source backup collision.');}
        try{if(fwrite($f,$old)!==strlen($old)||!fflush($f)){throw new RuntimeException('Incomplete source backup; no source writes.');}}finally{fclose($f);}}
}
$manifest=json_encode($records,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
$f=fopen($directory.'/manifest.json','x');if(!$f){throw new RuntimeException('Source manifest collision.');}
try{if(fwrite($f,$manifest)!==strlen($manifest)||!fflush($f)){throw new RuntimeException('Incomplete source manifest; no source writes.');}}finally{fclose($f);}
foreach($pending as $path=>[$old,$new]){if((is_file($root.'/'.$path)?file_get_contents($root.'/'.$path):null)!==$old){throw new RuntimeException('Concurrent working edit; no source writes.');}}
foreach($pending as $path=>[$old,$new]){if(!is_dir(dirname($root.'/'.$path))){mkdir(dirname($root.'/'.$path),0700,true);}
    if(file_put_contents($root.'/'.$path,$new,LOCK_EX)!==strlen($new)){throw new RuntimeException('Source sync failed: '.$path);}}
echo json_encode(['status'=>'synced','files'=>count($records),'exclusive_source_backup'=>$directory,'records'=>$records],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
