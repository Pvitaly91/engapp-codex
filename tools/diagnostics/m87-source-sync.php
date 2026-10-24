<?php

/** Filesystem-only finite source receipt; never transfers files or opens app/SQL/HTTP. */
require __DIR__.'/frozen-readonly-profile.php';
$profileCheckOnly=PHP_SAPI==='cli'&&count($argv)===2&&$argv[1]==='--profile-check-only';
$sharedGuard=<<<'PHP'
$capturePath=$private.'/independent-source-before-v1.json';
$fixturePath='tests/Fixtures/m87-owned-source-hunks.json';
if(is_link($capturePath)||!is_file($capturePath)||hash_file('sha256',$capturePath)!=='4345c31ffe0233766e3edba90e5c1996f1a7d29ee23dee301fdb0a11306d42a5'){
    throw new RuntimeException('M87 independent paired source BEFORE differs.');
}
$capture=json_decode(file_get_contents($capturePath),true,flags:JSON_THROW_ON_ERROR);
foreach([$root,$wt]as $contextRoot){if(is_link($contextRoot.'/'.$fixturePath)||!is_file($contextRoot.'/'.$fixturePath)||hash_file('sha256',$contextRoot.'/'.$fixturePath)!=='3410762321f9d3cb935043f2f9d3feb47f28c0cc2958fec6ecd996dc12456ce3'){throw new RuntimeException('M87 exact owned source fixture differs.');}}
$ledger=json_decode(file_get_contents($wt.'/'.$fixturePath),true,flags:JSON_THROW_ON_ERROR);
$expectedShared=['app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php','app/Support/TheoryContentRenderer.php','app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php','app/Services/TheoryCourseManifestService.php','app/Http/Controllers/TestJsV2Controller.php'];
if(($capture['base_sha']??null)!==App\Support\M87ContinuousCompletionPastSimple::BASE_SHA||count($capture['records']??[])!==8386
    ||($ledger['base_sha']??null)!==App\Support\M87ContinuousCompletionPastSimple::BASE_SHA
    ||($ledger['independent_source_before_sha256']??null)!=='4345c31ffe0233766e3edba90e5c1996f1a7d29ee23dee301fdb0a11306d42a5'
    ||($ledger['independent_db_before_sha256']??null)!==App\Services\M87ContentPatch::BEFORE_SHA
    ||array_column($ledger['files']??[],'path')!==$expectedShared||($ledger['owned_added_registration_lines']??null)!==12
    ||($ledger['owned_validator_added_lines']??null)!==0||($ledger['owned_validator_removed_lines']??null)!==0){
    throw new RuntimeException('M87 exact twelve-addition/eight-file source-sync scope differs.');
}
foreach(['served_root'=>$root,'worktree'=>$wt]as $context=>$contextRoot){
    $contextKey=$context==='served_root'?'root':'worktree';$additions=0;
    foreach($ledger['files']as $file){
        $path=$file['path'];$pin=$file['profiles'][$context]??null;$captured=$capture['records'][$path][$contextKey]??null;
        if(!is_array($pin)||!is_array($captured)||is_link($contextRoot.'/'.$path)||!is_file($contextRoot.'/'.$path)){
            throw new RuntimeException('M87 exact shared context missing or linked.');
        }
        $raw=file_get_contents($contextRoot.'/'.$path);$lf=str_replace("\r\n","\n",$raw);
        if(hash('sha256',$raw)!==$pin['after_raw_sha256']||hash('sha256',$lf)!==$pin['after_lf_sha256']
            ||strlen($raw)!==$pin['after_bytes']||substr_count($raw,"\n")!==$pin['after_lf_line_count']
            ||substr_count($raw,"\r\n")!==$pin['after_crlf_line_count']
            ||$captured['sha256']!==$pin['before_raw_sha256']||$captured['bytes']!==$pin['before_bytes']
            ||$captured['lf']!==$pin['before_lf_line_count']||$captured['crlf']!==$pin['before_crlf_line_count']){
            throw new RuntimeException('M87 independent raw/LF/EOL shared source-sync profile differs.');
        }
        foreach($file['hunks']as $hunk){
            $added=substr($hunk['after'],strlen($hunk['before']));
            if($hunk['added_lines']!==1||$hunk['removed_lines']!==0||!str_starts_with($hunk['after'],$hunk['before'])
                ||!str_contains($hunk['before'],'M86')||str_contains($hunk['before'],'M87')||!str_contains($added,'M87')
                ||substr_count($added,"\n")!==1||substr_count($lf,$hunk['after'])!==1){
                throw new RuntimeException('M87 source-sync requires exact additive whole-line hunks.');
            }
            $lf=str_replace($hunk['after'],$hunk['before'],$lf);
            $lines=preg_split('/(?<=\n)/',$raw,-1,PREG_SPLIT_NO_EMPTY);
            $normalized=array_map(static fn($line)=>str_replace("\r\n","\n",$line),$lines);
            $wanted=preg_split('/(?<=\n)/',$hunk['after'],-1,PREG_SPLIT_NO_EMPTY);$positions=[];
            foreach(array_keys($lines)as $n){if(array_slice($normalized,$n,count($wanted))===$wanted){$positions[]=$n;}}
            if(count($positions)!==1){throw new RuntimeException('M87 source-sync raw hunk location missing or ambiguous.');}
            $position=$positions[0]+count($wanted)-1;
            if($lines[$position]!==$added&&$lines[$position]!==str_replace("\n","\r\n",$added)){throw new RuntimeException('M87 source-sync insertion EOL differs.');}
            array_splice($lines,$position,1);$raw=implode('',$lines);++$additions;
        }
        if(hash('sha256',$raw)!==$captured['sha256']||hash('sha256',$lf)!==$pin['before_lf_sha256']){
            throw new RuntimeException('M87 source-sync exact predecessor not recovered by twelve additions only.');
        }
    }
    if($additions!==12){throw new RuntimeException('M87 exact twelve registration additions required in each context.');}
}
$files = [];
PHP;
$profile=[
    ['M52PassiveBasicsPackage','M87ContinuousCompletionPastSimple',3],['M52','M87',19],['m52','m87',1],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    ['$files = [];',$sharedGuard,1],
    ["'files' => \$files, 'created_at' => gmdate('c'),","'files' => \$files, 'owned_source_fixture_sha256'=>'3410762321f9d3cb935043f2f9d3feb47f28c0cc2958fec6ecd996dc12456ce3','shared_raw_lf_contexts_checked'=>16,'shared_added_lines_per_context'=>12,'created_at' => gmdate('c'),",1],
];
if($profileCheckOnly){
    $immutable=__DIR__.'/m52-source-sync.php';$source=file_get_contents($immutable);
    if(is_link($immutable)||!hash_equals('b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273',hash('sha256',$source))){throw new RuntimeException('M87 immutable filesystem scanner differs.');}
    foreach($profile as [$needle,$replacement,$count]){if(substr_count($source,$needle)!==$count){throw new RuntimeException('M87 pure source-sync profile count differs: '.substr($needle,0,90));}$source=str_replace($needle,$replacement,$source);}
    token_get_all($source,TOKEN_PARSE);
    echo json_encode(['profile'=>'M87 immutable filesystem-only scanner','literal_substitutions'=>count($profile),'composed_php_parsed'=>true,'app_bootstraps'=>0,'SQL_access'=>false,'http_requests'=>0,'private_evidence_opened'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
    exit(0);
}
runFrozenReadOnlyProfile('m52-source-sync.php','b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273',$profile);
