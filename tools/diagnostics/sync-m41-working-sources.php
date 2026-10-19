<?php

// Explicit finite local source sync, including declared practice assets only. Never touches .env, routes, vendor, caches or DB.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($argv[1]??'', ['--check', '--apply', '--bootstrap-support'], true)) { exit(1); }
$source=dirname(__DIR__,2); $root='D:/DEV/htdocs/gramlyze.loc';
require $source.'/vendor/autoload.php';
$app = new Illuminate\Foundation\Application($source); // path-only; no kernel, .env or DB boot
[$before, $package] = App\Support\M41AuthoredTenseComparisonsPackage::load($source);
App\Support\M41AuthoredTenseComparisonsPackage::validate($before, $package);
$ancestry = new Symfony\Component\Process\Process(['git', '-c', 'safe.directory='.$source, '-c', 'core.bare=false', 'merge-base', '--is-ancestor', 'ab61310a81f2389353c264fe23266025fe49ffd6', 'HEAD'], $source);
$ancestry->mustRun();
$head=new Symfony\Component\Process\Process(['git','-c','safe.directory='.$root,'-c','core.bare=false','rev-parse','HEAD'],$root); $head->mustRun();
if(trim($head->getOutput())!=='41820a2bebdf69004fa7209a2a38457f93efabbd'){throw new RuntimeException('Original working checkout HEAD changed.');}
if(($argv[1]??'')==='--bootstrap-support'){
    $paths=[App\Support\M41AuthoredTenseComparisonsPackage::BEFORE,App\Support\M41AuthoredTenseComparisonsPackage::SOURCE,'app/Support/M41AuthoredTenseComparisonsPackage.php'];
    $pending=[];$records=[];
    foreach($paths as $path){
        if(is_link($root.'/'.$path)||is_link($source.'/'.$path)
            ||!str_starts_with(strtolower(str_replace('\\','/',realpath(dirname($root.'/'.$path)))),strtolower($root).'/'))throw new RuntimeException('Bootstrap source is not an owned regular path.');
        $new=file_get_contents($source.'/'.$path);$old=is_file($root.'/'.$path)?file_get_contents($root.'/'.$path):null;
        if($old!==null&&$old!==$new)throw new RuntimeException('Unknown existing ROOT bootstrap source; no overwrite.');
        $pending[$path]=[$old,$new];$records[]=['path'=>$path,'before_sha256'=>$old===null?null:hash('sha256',$old),'after_sha256'=>hash('sha256',$new)];
    }
    $directory=$root.'/storage/app/seo-m41-local/bootstrap-source-backup-'.bin2hex(random_bytes(8));
    if(!mkdir($directory,0700))throw new RuntimeException('Exclusive bootstrap source backup failed.');
    $exclusive=static function($path,$bytes):void{$file=fopen($path,'xb');if(!$file)throw new RuntimeException('Bootstrap backup collision.');try{if(fwrite($file,$bytes)!==strlen($bytes)||!fflush($file))throw new RuntimeException('Incomplete bootstrap backup.');}finally{fclose($file);}};
    foreach($pending as $path=>[$old,$new])if($old!==null){$p=$directory.'/'.$path;if(!is_dir(dirname($p)))mkdir(dirname($p),0700,true);$exclusive($p,$old);}
    $exclusive($directory.'/manifest.json',json_encode($records,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
    foreach($pending as $path=>[$old])if((is_file($root.'/'.$path)?file_get_contents($root.'/'.$path):null)!==$old)throw new RuntimeException('Concurrent bootstrap source edit; no writes.');
    foreach($pending as $path=>[$old,$new])if($old===null)$exclusive($root.'/'.$path,$new);
    echo json_encode(['status'=>'bootstrapped-support-only','exclusive_bootstrap_backup'=>$directory,'records'=>$records],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";exit;
}
$shared = App\Services\M41ContentPatch::reviewedSharedSources($source, $root.'/storage/app/seo-m41-local');
$sharedByPath = array_column($shared, null, 'path');
$files=[
    'app/Support/M41AuthoredTenseComparisonsPackage.php','app/Services/M41ContentPatch.php','app/Services/M41LocalTargetGuard.php',
    'app/Console/Commands/PatchM41Content.php','tools/diagnostics/run-m41-working-local.php',
    App\Support\M41AuthoredTenseComparisonsPackage::BEFORE,App\Support\M41AuthoredTenseComparisonsPackage::SOURCE,
    App\Support\M41AuthoredTenseComparisonsPackage::MASTER_PATH,App\Support\M41AuthoredTenseComparisonsPackage::CORRECTION_PATH,App\Support\M41AuthoredTenseComparisonsPackage::APPROVAL_PATH,
    ...array_column($package['targets'],'path'),
    'resources/views/engram/theory/blocks-v3/m41-section-styles.blade.php',
    'resources/views/engram/theory/blocks-v3/m41-author-section.blade.php',
    'resources/views/engram/theory/blocks-v3/m41-practice-ui.blade.php','public/js/m41-practice-ui.js',
    ...App\Services\M41ContentPatch::SHARED_VIEWS,
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
    if (isset($sharedByPath[$path])) {
        $review = $sharedByPath[$path];
        $previous = file_get_contents($root.'/storage/app/seo-m41-local/'.$review['before_basename']);
        $records[] = ['path'=>$path,'before_sha256'=>$review['before_sha256'],
            'after_sha256'=>$review['root_sha256'],'worktree_sha256'=>$review['worktree_sha256'],'mode'=>'shared-verify-only'];
        $pending[$path] = [$old, null, $previous];
        continue;
    }
    if($old!==null && $normal($old)!==$normal($new)){
        $accepted=false;
        // Accept exact historical sources only; never an arbitrary dirty file.
        foreach(['ab61310a81f2389353c264fe23266025fe49ffd6','41820a2bebdf69004fa7209a2a38457f93efabbd'] as $sha){
            $p=new Symfony\Component\Process\Process(['git','-c','safe.directory='.$source,'-c','core.bare=false','show',$sha.':'.$path],$source);
            $p->run(); if($p->isSuccessful() && $normal($p->getOutput())===$normal($old)){$accepted=true;break;}
        }
        if(!$accepted){throw new RuntimeException('Unrelated working edit/conflict; refusing source sync: '.$path);}
    }
    $records[]=['path'=>$path,'before_sha256'=>$old===null?null:hash('sha256',$old),'after_sha256'=>hash('sha256',$new),'worktree_sha256'=>hash('sha256',$new),'mode'=>'finite-sync'];
    $pending[$path]=[$old,$new,$old];
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
$directory=$root.'/storage/app/seo-m41-local/source-backup-'.bin2hex(random_bytes(8));
if(!mkdir($directory,0700)){throw new RuntimeException('Exclusive source backup failed.');}
foreach($pending as $path=>[$old,$new,$previous]){
    if($previous!==null){$b=$directory.'/'.$path; if(!is_dir(dirname($b))){mkdir(dirname($b),0700,true);} $f=fopen($b,'x');if(!$f){throw new RuntimeException('Source backup collision.');}
        try{if(fwrite($f,$previous)!==strlen($previous)||!fflush($f)){throw new RuntimeException('Incomplete source backup; no source writes.');}}finally{fclose($f);}}
}
$manifest=json_encode($records,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
$f=fopen($directory.'/manifest.json','x');if(!$f){throw new RuntimeException('Source manifest collision.');}
try{if(fwrite($f,$manifest)!==strlen($manifest)||!fflush($f)){throw new RuntimeException('Incomplete source manifest; no source writes.');}}finally{fclose($f);}
foreach($pending as $path=>[$old,$new]){if((is_file($root.'/'.$path)?file_get_contents($root.'/'.$path):null)!==$old){throw new RuntimeException('Concurrent working edit; no source writes.');}}
foreach($pending as $path=>[$old,$new]){if($new===null){continue;} if(!is_dir(dirname($root.'/'.$path))){mkdir(dirname($root.'/'.$path),0700,true);}
    if(file_put_contents($root.'/'.$path,$new,LOCK_EX)!==strlen($new)){throw new RuntimeException('Source sync failed: '.$path);}}
echo json_encode(['status'=>'synced','files'=>count($records),'exclusive_source_backup'=>$directory,'records'=>$records],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
