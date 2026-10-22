<?php

/** Finite diagnostic-only proof for the one approved M76 token-label display hunk. No app bootstrap or SQL. */
function m76DisplayFollowupProof():array
{
    $root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';
    $normal=static fn(string $p):string=>strtolower(str_replace('\\','/',(string)realpath($p)));
    if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||$normal(dirname(__DIR__,2))!==$normal($root)){
        throw new RuntimeException('M76 display followup requires actual local served ROOT.');
    }
    $pins=[];$read=static function(string $file,string $sha)use(&$pins):string{
        if(!is_file($file)||is_link($file)||!hash_equals($sha,hash_file('sha256',$file))){
            throw new RuntimeException('M76 display followup exact source/evidence drift.');
        }$pins[$file]=$sha;return file_get_contents($file);
    };
    $sidecarPath='docs/content/m76-token-label-display-fix.v1.json';
    $sidecarSha='7b705e98f0c2be7593d1e9cb0baad57a8562c151d684ccc150f0b374b8146edd';
    $fix=json_decode($read($root.'/'.$sidecarPath,$sidecarSha),true,flags:JSON_THROW_ON_ERROR);
    $read($wt.'/'.$sidecarPath,$sidecarSha);
    $finalName='anchor-final-readonly-v2.json';$finalSha='c338abcbf44011f58b737f5c20b3b26fe0fe81485bd6e13d33b02f13841fc0e6';
    $final=json_decode($read($root.'/storage/app/seo-m76-local/'.$finalName,$finalSha),true,flags:JSON_THROW_ON_ERROR);
    if(($fix['package']??null)!=='m76-token-label-display-fix'||($fix['status']??null)!=='frozen'
        ||($fix['component']??null)!=='resources/views/components/text-block-practice-questions.blade.php'
        ||array_column($fix['profiles']??[],'profile')!==['served-root','accepted-worktree']
        ||($final['display_exception_sha256']??null)!==$sidecarSha
        ||($final['visual_files_unchanged']??null)!==325||($final['approved_visual_files']??null)!==1
        ||($final['shared_javascript_unchanged']??null)!==true||($final['shared_formatter_unchanged']??null)!==true
        ||($final['sql_unchanged_since_previous_anchor_final']??null)!==true
        ||count($final['sql_after']??[])!==46||($final['db_writes']??null)!==0||($final['http_requests']??null)!==0){
        throw new RuntimeException('M76 display followup exact final/sidecar scope differs.');
    }
    foreach($fix['profiles'] as $i=>$p){
        $file=($i===0?$root:$wt).'/'.$fix['component'];$raw=$read($file,$p['after_raw_sha256']);
        if(substr_count($raw,$fix['allowed_change']['after_binding'])!==1
            ||hash('sha256',str_replace("\r\n","\n",$raw))!==$p['after_lf_sha256']){
            throw new RuntimeException('M76 display followup coherent after profile differs.');
        }
        $old=str_replace($fix['allowed_change']['after_binding'],$p['before_binding'],$raw,$count);
        preg_match_all('~<script\b[^>]*>[\s\S]*?</script>~i',$raw,$script);
        preg_match_all('~<script\b[^>]*>[\s\S]*?</script>~i',$old,$oldScript);
        if($count!==1||hash('sha256',$old)!==$p['before_raw_sha256']||$script[0]!==$oldScript[0]
            ||count($script[0])!==$p['embedded_script_count']
            ||hash('sha256',implode("\n",$script[0]))!==$p['unchanged_embedded_script_sha256']){
            throw new RuntimeException('M76 display followup exact reversal or unchanged script proof failed.');
        }
    }
    $f=$fix['unchanged_formatter'];$read($root.'/'.$f['path'],$f['served_root_raw_sha256']);
    $formatter=$read($wt.'/'.$f['path'],$f['accepted_worktree_raw_sha256']);
    if(hash('sha256',str_replace("\r\n","\n",$formatter))!==$f['accepted_worktree_lf_sha256']){
        throw new RuntimeException('M76 display followup formatter changed.');
    }
    foreach([
        'tools/diagnostics/m76-old-consumers.php'=>'ed0140eeb6afddebc44812dbf63dd36da3fde7213a5c493f5526fbe694a978cd',
        'tools/diagnostics/m76-navigation-audit.php'=>'398bcdc48ca237a50d553df7a663c0e3de606c98162b216eb73b9b5123d905a7',
    ] as $path=>$sha){$read($root.'/'.$path,$sha);$read($wt.'/'.$path,$sha);}
    foreach(['m76-display-followup-proof.php','m76-display-old-consumers.php','m76-display-navigation-audit.php'] as $name){
        $path='tools/diagnostics/'.$name;$sha=hash_file('sha256',$root.'/'.$path);
        if(!is_string($sha)){throw new RuntimeException('M76 display followup source absent.');}
        $read($root.'/'.$path,$sha);$read($wt.'/'.$path,$sha);
    }
    return ['pins'=>$pins,'sidecar'=>$fix,'sidecar_path'=>$sidecarPath,'sidecar_sha256'=>$sidecarSha,
        'final_file'=>$finalName,'final_sha256'=>$finalSha,'final'=>$final];
}
