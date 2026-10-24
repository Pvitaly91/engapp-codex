<?php

/**
 * The unchanged strict61 AFTER collector, with one finite receipt-basename revision.
 * CLI: audit after-old-consumers-vN.json before-old-consumers-v1.json BEFORE_SHA notes-final-readonly-vN.json FINAL_SHA
 * --profile-check-only is inherited, pure and does not bootstrap/issue HTTP.
 */
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'){throw new RuntimeException('M85 note consumer wrapper requires local CLI.');}
$file=__DIR__.'/m85-old-consumers-after.php';
if(!is_file($file)||is_link($file)||!hash_equals('300978f7ec962e2065bd7f95f53f18f33d09389801dd800d7817f13ba3fb0008',hash_file('sha256',$file))){
    throw new RuntimeException('Immutable M85 strict AFTER collector changed.');
}
$source=str_replace("\r\n","\n",file_get_contents($file));
$before="preg_match('/^final-readonly-v[1-9][0-9]*\\.json$/D', \$argv[5] ?? '')";
$after="preg_match('/^notes-final-readonly-v[1-9][0-9]*\\.json$/D', \$argv[5] ?? '')";
if(!str_starts_with($source,"<?php\n")||substr_count($source,$before)!==1){throw new RuntimeException('Single finite final receipt namespace literal differs.');}
$source=str_replace($before,$after,$source);token_get_all($source,TOKEN_PARSE);
// Only hash-pinned repository source and the one fixed literal above reach eval.
// All CLI/SQL/source/61GET/strict equality checks remain owned by the unchanged collector.
eval(substr($source,strlen("<?php\n")));
