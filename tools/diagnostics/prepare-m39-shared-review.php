<?php
// Explicit finite local source-review evidence; no Laravel boot or database access.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$source=dirname(__DIR__,2); $root='D:/DEV/htdocs/gramlyze.loc';
$dir=$root.'/storage/app/seo-m39-local';
if (!is_dir($dir) || is_link($dir)) { throw new RuntimeException('Private M39 directory unavailable.'); }
$paths=[
 'resources/views/theory/partials/content-block.blade.php',
 'resources/views/theory/partials/point-detail-fragment.blade.php',
 'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
 'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
 'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
 'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
];
$exclusive=static function(string $p,string $bytes):void{
 $f=fopen($p,'xb'); if(!$f)throw new RuntimeException('Exclusive evidence already exists.');
 try{if(fwrite($f,$bytes)!==strlen($bytes)||!fflush($f))throw new RuntimeException('Incomplete evidence.');}finally{fclose($f);}
};
if (($argv[1]??'') === '--capture') {
 foreach($paths as $i=>$p){
  if(is_link($root.'/'.$p)||is_link($source.'/'.$p))throw new RuntimeException('Unexpected shared source link.');
  $exclusive($dir.'/m39-shared-before-v1-'.$i.'.bin',file_get_contents($root.'/'.$p));
 }
 echo "Captured six prior working shared source files exclusively.\n";exit;
}
if (($argv[1]??'') !== '--review')throw new RuntimeException('Use --capture before patches, --review after exact diff review.');
$files=[];
foreach($paths as $i=>$p){
 $basename='m39-shared-before-v1-'.$i.'.bin';$old=file_get_contents($dir.'/'.$basename);
 if($old===false)throw new RuntimeException('Missing original source bytes.');
 $files[]=['path'=>$p,'root_sha256'=>hash_file('sha256',$root.'/'.$p),
  'worktree_sha256'=>hash_file('sha256',$source.'/'.$p),'before_sha256'=>hash('sha256',$old),'before_basename'=>$basename];
}
$review=['version'=>1,'base_sha'=>'a525a5e03b59904b9fe5987abacf5c038fe5293f','files'=>$files];
$exclusive($dir.'/shared-source-review-v1.json',json_encode($review,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
echo "Recorded exact reviewed ROOT/worktree shared-source identities.\n";
