<?php

// Exclusive private file evidence only; never boots the app or accesses the DB.
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows')exit(1);
$source=dirname(__DIR__,2);$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m39-local';
$paths=['resources/views/theory/partials/content-block.blade.php','resources/views/engram/theory/blocks-v3/practice-set.blade.php'];
if(!is_dir($dir)||is_link($dir))throw new RuntimeException('Existing private directory required.');
$exclusive=static function(string $path,string $bytes):void{
    $file=fopen($path,'xb');if(!$file)throw new RuntimeException('Exclusive UI evidence collision.');
    try{if(fwrite($file,$bytes)!==strlen($bytes)||!fflush($file))throw new RuntimeException('Incomplete UI evidence.');}finally{fclose($file);}
};
if(($argv[1]??'')==='--capture'){
    foreach($paths as $i=>$path){if(is_link($root.'/'.$path))throw new RuntimeException('Shared source is linked.');$exclusive($dir.'/m39-practice-ui-shared-before-v1-'.$i.'.bin',file_get_contents($root.'/'.$path));}
    echo "Captured two original ROOT UI shared files exclusively.\n";exit;
}
if(($argv[1]??'')!=='--review')throw new RuntimeException('Use --capture before Root edits, --review after exact diff review.');
$files=[];
foreach($paths as $i=>$path){
    $basename='m39-practice-ui-shared-before-v1-'.$i.'.bin';$original=file_get_contents($dir.'/'.$basename);
    $files[]=['path'=>$path,'root_sha256'=>hash_file('sha256',$root.'/'.$path),'worktree_sha256'=>hash_file('sha256',$source.'/'.$path),
        'before_sha256'=>hash('sha256',$original),'before_basename'=>$basename];
}
$exclusive($dir.'/m39-practice-ui-shared-review-v1.json',json_encode(['version'=>1,'patch'=>'m39-practice-ui-v1','files'=>$files],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
echo "Recorded exact ROOT/worktree UI source review.\n";
