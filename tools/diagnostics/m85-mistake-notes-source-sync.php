<?php

/** Filesystem-only finite receipt. No transfer, bootstrap, SQL, HTTP or route edits. */
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==1){throw new RuntimeException('Explicit M85 note source receipt only.');}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m85-local';
$normal=static fn($p)=>strtolower(str_replace('\\','/',(string)realpath($p)));
if($normal(dirname(__DIR__,2))!==$normal($root)||is_link($dir)){throw new RuntimeException('Served ROOT/private identity differs.');}
require $root.'/vendor/autoload.php';
use App\Services\M85MistakeNotesPatch as Patch;
$initial=Patch::readPinned($dir.'/'.App\Services\M85ContentPatch::BEFORE_FILE,App\Services\M85ContentPatch::BEFORE_SHA);
$baseline=Patch::readPinned($dir.'/'.Patch::BASELINE,Patch::BASELINE_SHA);$files=[];
$paired=Patch::readPinned($dir.'/'.Patch::SOURCE_BEFORE,Patch::SOURCE_BEFORE_SHA);
$fixture=Patch::readPinned($root.'/'.Patch::SOURCE_FIXTURE,Patch::SOURCE_FIXTURE_SHA);$shared=array_column($fixture['files'],null,'path');
$original=Patch::readPinned($dir.'/'.$baseline['plan_file']);
if(Patch::fileHash($dir.'/'.$baseline['backup_file'])!==$baseline['backup_sha256']
    ||file_get_contents($dir.'/'.$baseline['plan_file'])!==file_get_contents($dir.'/'.$baseline['backup_file'])){
    throw new RuntimeException('Original independently accepted preview/backup bytes differ.');
}
$git=static function(array $args)use($wt):string{
    $env=getenv();$env['GIT_OPTIONAL_LOCKS']='0';
    $p=proc_open(['git','-C',$wt,...$args],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,$env,['bypass_shell'=>true]);
    if(!is_resource($p)){throw new RuntimeException('Read-only accepted Git source evidence unavailable.');}
    fclose($pipes[0]);$out=stream_get_contents($pipes[1]);stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    if(proc_close($p)!==0||!is_string($out)){throw new RuntimeException('Read-only accepted Git source evidence failed.');}return $out;
};
$foreign=[];
foreach(Patch::syncPaths($root) as $path){
    $a=Patch::fileHash($root.'/'.$path);$b=Patch::fileHash($wt.'/'.$path);
    $rawA=file_get_contents($root.'/'.$path);$rawB=file_get_contents($wt.'/'.$path);
    if($path===App\Services\M85ContentPatch::CONTROLLER_PATH){
        if($a!==App\Services\M85ContentPatch::CONTROLLER_ROOT_AFTER_SHA||$b!==App\Services\M85ContentPatch::CONTROLLER_WT_AFTER_SHA){throw new RuntimeException('Reviewed controller raw profiles differ.');}
    }elseif($rawA!==$rawB&&!(str_ends_with($path,'.php')&&str_replace("\r\n","\n",$rawA)===str_replace("\r\n","\n",$rawB))){
        // A protected historical dependency can already differ across ROOT/WT.
        // Preserve both independently captured raw profiles; never copy one over the other.
        $profiles=$paired['files'][$path]??null;
        $expectedA=$shared[$path]['profiles']['served_root']['after_raw_sha256']??($profiles['served_root']['raw_sha256']??null);
        $expectedB=$shared[$path]['profiles']['worktree']['after_raw_sha256']??($profiles['worktree']['raw_sha256']??null);
        if(!is_array($profiles)||$a!==$expectedA||$b!==$expectedB){
            if($path!==Patch::FOREIGN_FORMATTER_PATH){throw new RuntimeException('Finite ROOT/WT source mirror differs beyond independent raw profiles: '.$path);}
            $blob=$git(['show',App\Support\M85VerbToBe::BASE_SHA.':'.$path]);
            $profile=['path'=>$path,'root_raw_sha256'=>$a,'root_bytes'=>strlen($rawA),'root_lf'=>substr_count($rawA,"\n"),'root_crlf'=>substr_count($rawA,"\r\n"),
                'worktree_raw_sha256'=>$b,'worktree_bytes'=>strlen($rawB),'worktree_lf'=>substr_count($rawB,"\n"),'worktree_crlf'=>substr_count($rawB,"\r\n"),
                'accepted_base'=>App\Support\M85VerbToBe::BASE_SHA,'worktree_head'=>rtrim($git(['rev-parse','HEAD']),"\r\n"),
                'worktree_path_status'=>$git(['status','--porcelain','--untracked-files=no','--',$path]),
                'git_blob_sha1'=>hash('sha1','blob '.strlen($blob)."\0".$blob),'git_blob_sha256'=>hash('sha256',$blob),
                'git_blob_bytes'=>strlen($blob),'git_blob_lf'=>substr_count($blob,"\n"),'git_blob_crlf'=>substr_count($blob,"\r\n"),
                'worktree_lf_equals_accepted_git_blob'=>str_replace("\r\n","\n",$rawB)===$blob];
            $foreign[$path]=Patch::foreignFormatterProfile($profile,$original,$baseline['plan_digest']);
        }
    }
    $files[]=['path'=>$path,'root_after_sha256'=>$a,'worktree_sha256'=>$b];
}
foreach([$initial['visual_hashes'],$initial['frozen_hashes']] as $hashes){foreach($hashes as $path=>$sha){
    if(Patch::fileHash($root.'/'.$path)!==$sha){throw new RuntimeException('ROOT visual/historical frozen bytes changed.');}
}}
if(count($paired['groups']['visual']??[])!==326||count($paired['groups']['frozen']??[])!==1987){throw new RuntimeException('Exact captured WT visual/frozen group scope differs.');}
foreach(['visual','frozen'] as $group){foreach($paired['groups'][$group] as $path){
    $file=$wt.'/'.$path;if(is_link($file)){throw new RuntimeException('Captured WT visual/frozen file is linked.');}
    if(!isset($paired['files'][$path]['worktree'])){throw new RuntimeException('Captured WT visual/frozen context is missing.');}
    Patch::assertCapturedContextBytes(is_file($file)?file_get_contents($file):null,$paired['files'][$path]['worktree']);
}}
$receipt=['base_sha'=>App\Support\M85VerbToBe::BASE_SHA,'served_root'=>$root,'source_worktree'=>$wt,
    'author_revision'=>'1.0.1','native_version'=>'1.0.0','baseline_file'=>Patch::BASELINE,'baseline_sha256'=>Patch::BASELINE_SHA,
    'initial_before_sha256'=>App\Services\M85ContentPatch::BEFORE_SHA,'files'=>$files,'foreign_context_profiles'=>$foreign,
    'context_boundaries'=>['root_initial_visual_raw_files'=>326,'root_initial_frozen_raw_files'=>2884,
        'worktree_independent_visual_profiles'=>326,'worktree_independent_frozen_profiles'=>1987,
        'root_frozen_paths_without_worktree_paired_capture'=>901,'worktree_absent_among_uncaptured_root_frozen_paths'=>13,
        'worktree_historical_raw_claim_for_uncaptured_901'=>false,'root_worktree_visual_raw_differences_observed'=>167,
        'root_worktree_frozen_raw_differences_observed'=>280,'observed_context_differences_are_not_m85_mutations'=>true],
    'created_at'=>gmdate('c'),'db_writes'=>0,'http_requests'=>0];
$bytes=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";$path=$dir.'/'.Patch::SYNC_RECEIPT;
if(file_exists($path)||is_link($path)){throw new RuntimeException('New source receipt exists; no overwrite.');}
$h=fopen($path,'x');if(!$h){throw new RuntimeException('Exclusive source receipt unavailable.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('Source receipt incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>basename($path),'sha256'=>hash('sha256',$bytes),'paths'=>count($files),'db_writes'=>0,'http_requests'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
