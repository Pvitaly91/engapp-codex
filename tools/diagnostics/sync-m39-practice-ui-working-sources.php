<?php

// Explicit finite source sync. Shared ROOT/PPC code is verified, never overwritten.
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||!in_array($argv[1]??'', ['--check','--apply'],true))exit(1);
$source=dirname(__DIR__,2);$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m39-local';
require $source.'/vendor/autoload.php';$app=new Illuminate\Foundation\Application($source);
$head=new Symfony\Component\Process\Process(['git','-c','safe.directory='.$root,'-c','core.bare=false','rev-parse','HEAD'],$root);$head->mustRun();
if(trim($head->getOutput())!=='41820a2bebdf69004fa7209a2a38457f93efabbd')throw new RuntimeException('Original ROOT checkout HEAD changed; no UI sync.');
$package=App\Support\M39PracticeUiPackage::load($source);App\Support\M39PracticeUiPackage::validate($package);
$shared=App\Services\M39PracticeUiPatch::reviewedSharedSources($source,$dir);$sharedByPath=array_column($shared,null,'path');
$files=['app/Support/M39PracticeUiPackage.php','app/Services/M39PracticeUiPatch.php','app/Console/Commands/PatchM39PracticeUi.php',
    'tools/diagnostics/run-m39-practice-ui-working-local.php',App\Support\M39PracticeUiPackage::SOURCE,
    ...array_column($package['targets'],'path'),'resources/views/engram/theory/blocks-v3/m39-practice-ui.blade.php','public/js/m39-practice-ui.js',
    ...App\Services\M39PracticeUiPatch::SHARED_VIEWS];
$normal=fn($value)=>str_replace("\r\n","\n",$value);$records=[];$pending=[];
$normalPath=fn($path)=>strtolower(str_replace('\\','/',(string)realpath($path)));
$resolvedRoot=$normalPath($root);$resolvedSource=$normalPath($source);
if($resolvedRoot===''||$resolvedSource==='')throw new RuntimeException('UI sync roots unavailable.');
foreach($files as $path){
    if(is_link($root.'/'.$path)||is_link($source.'/'.$path)||!is_file($source.'/'.$path)
        ||!str_starts_with($normalPath(dirname($root.'/'.$path)),$resolvedRoot.'/')
        ||!str_starts_with($normalPath(dirname($source.'/'.$path)),$resolvedSource.'/'))throw new RuntimeException('UI sync source missing, linked or outside owned roots.');
    $new=file_get_contents($source.'/'.$path);$old=is_file($root.'/'.$path)?file_get_contents($root.'/'.$path):null;
    if(isset($sharedByPath[$path])){
        $review=$sharedByPath[$path];$previous=file_get_contents($dir.'/'.$review['before_basename']);
        $records[]=['path'=>$path,'before_sha256'=>$review['before_sha256'],'after_sha256'=>$review['root_sha256'],
            'worktree_sha256'=>$review['worktree_sha256'],'mode'=>'shared-verify-only'];$pending[$path]=[$old,null,$previous];continue;
    }
    if($old!==null&&$normal($old)!==$normal($new)){
        $matches=array_values(array_filter($package['targets'],fn($target)=>$target['path']===$path));
        if(count($matches)!==1||json_decode($old,true,flags:JSON_THROW_ON_ERROR)!==$matches[0]['before'])throw new RuntimeException('Unknown ROOT UI source edit; refusing sync.');
    }
    $records[]=['path'=>$path,'before_sha256'=>$old===null?null:hash('sha256',$old),'after_sha256'=>hash('sha256',$new),
        'worktree_sha256'=>hash('sha256',$new),'mode'=>'finite-sync'];$pending[$path]=[$old,$new,$old];
}
foreach($package['targets'] as $target)if(json_decode($pending[$target['path']][1],true,flags:JSON_THROW_ON_ERROR)!==$target['after'])throw new RuntimeException('Canonical UI source differs.');
if($argv[1]==='--check'){echo json_encode(['status'=>'checked-no-writes','files'=>count($records),'records'=>$records],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";exit;}
$backup=$dir.'/source-backup-practice-ui-'.bin2hex(random_bytes(8));
if(!mkdir($backup,0700))throw new RuntimeException('Exclusive UI source backup failed.');
$exclusive=static function($path,$bytes):void{$f=fopen($path,'xb');if(!$f)throw new RuntimeException('UI backup collision.');try{if(fwrite($f,$bytes)!==strlen($bytes)||!fflush($f))throw new RuntimeException('Incomplete UI backup.');}finally{fclose($f);}};
foreach($pending as $path=>[$old,$new,$previous])if($previous!==null){$p=$backup.'/'.$path;if(!is_dir(dirname($p)))mkdir(dirname($p),0700,true);$exclusive($p,$previous);}
$exclusive($backup.'/manifest.json',json_encode($records,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
foreach($pending as $path=>[$old])if((is_file($root.'/'.$path)?file_get_contents($root.'/'.$path):null)!==$old)throw new RuntimeException('Concurrent UI source change; no writes.');
foreach($pending as $path=>[$old,$new]){if($new===null)continue;if(!is_dir(dirname($root.'/'.$path)))mkdir(dirname($root.'/'.$path),0700,true);if(file_put_contents($root.'/'.$path,$new,LOCK_EX)!==strlen($new))throw new RuntimeException('UI sync write failed.');}
echo json_encode(['status'=>'synced','files'=>count($records),'exclusive_source_backup'=>$backup,'records'=>$records],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
