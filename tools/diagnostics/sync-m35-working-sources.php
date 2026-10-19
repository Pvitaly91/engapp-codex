<?php

// Explicit local mechanical source sync only. Never touches .env, routes, assets, vendor, caches or DB.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($argv[1]??'', ['--check', '--apply'], true)) { exit(1); }
$source=dirname(__DIR__,2); $root='D:/DEV/htdocs/gramlyze.loc';
require $source.'/vendor/autoload.php';
$app = new Illuminate\Foundation\Application($source); // path-only; no kernel, .env or DB boot
[$before, $package] = App\Support\M35PassiveReportingPackage::load($source);
App\Support\M35PassiveReportingPackage::validate($before, $package);
$ancestry = new Symfony\Component\Process\Process(['git', 'merge-base', '--is-ancestor', 'd2581e3bbcc8a5b89d0a93a454bb010d3c4606eb', 'HEAD'], $source);
$ancestry->mustRun();
$head=new Symfony\Component\Process\Process(['git','rev-parse','HEAD'],$root); $head->mustRun();
if(trim($head->getOutput())!=='41820a2bebdf69004fa7209a2a38457f93efabbd'){throw new RuntimeException('Original working checkout HEAD changed.');}
$files=[
    'app/Support/M35PassiveReportingPackage.php','app/Services/M35ContentPatch.php','app/Services/M35LocalTargetGuard.php',
    'app/Console/Commands/PatchM35Content.php','tools/diagnostics/run-m35-working-local.php','database/content-patches/m35-m19-passive-reporting-before.json',
    'database/content-patches/m35-m19-passive-reporting.v1.json',
    'database/seeders/Page_V3/PassiveVoice/PassiveReportingStructuresTheorySeeder/definition.json',
    'database/seeders/Page_V3/PassiveVoice/ComplexPassiveAndCausativeTheorySeeder/definition.json',
    'database/seeders/Page_V3/PassiveVoice/ComplexPassiveImpersonalStyleTheorySeeder/definition.json',
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
        || !str_starts_with(strtolower(str_replace('\\','/',realpath(dirname($root.'/'.$path)))),strtolower($root).'/')
        || !str_starts_with(strtolower(str_replace('\\','/',realpath(dirname($source.'/'.$path)))),strtolower(str_replace('\\','/',$source)).'/')){
        throw new RuntimeException('Source sync path is not an owned regular file: '.$path);
    }
    $new=file_get_contents($source.'/'.$path); if($new===false){throw new RuntimeException('Missing finite source: '.$path);}
    $old=is_file($root.'/'.$path)?file_get_contents($root.'/'.$path):null;
    if($old!==null && $normal($old)!==$normal($new)){
        $accepted=false;
        // ROOT retains older tracked M19 sources; accept their exact original checkout blob,
        // never an arbitrary dirty file. The working DB already has the accepted M19 author copy.
        foreach(['d2581e3bbcc8a5b89d0a93a454bb010d3c4606eb','41820a2bebdf69004fa7209a2a38457f93efabbd'] as $sha){
            $p=new Symfony\Component\Process\Process(['git','show',$sha.':'.$path],$source);
            $p->run(); if($p->isSuccessful() && $normal($p->getOutput())===$normal($old)){$accepted=true;break;}
        }
        if(!$accepted){throw new RuntimeException('Unrelated working edit/conflict; refusing source sync: '.$path);}
    }
    $records[]=['path'=>$path,'before_sha256'=>$old===null?null:hash('sha256',$old),'after_sha256'=>hash('sha256',$new)];
    $pending[$path]=[$old,$new];
}
foreach ($package['targets'] as $target) {
    if (json_decode($pending[$target['path']][1], true, flags: JSON_THROW_ON_ERROR) !== $target['after']) {
        throw new RuntimeException('Canonical source differs from the finite author projection.');
    }
}
if (($argv[1]??'') === '--check') {
    echo json_encode(['status'=>'checked-no-writes','files'=>count($records),'records'=>$records],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
    exit(0);
}
$directory=$root.'/storage/app/seo-m35-local/source-backup-'.bin2hex(random_bytes(8));
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
