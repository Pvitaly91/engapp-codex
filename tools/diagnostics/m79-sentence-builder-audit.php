<?php

/** Finite M79 read-only registry profile. No data or answer-engine changes. */
require __DIR__.'/frozen-readonly-profile.php';
$receiptVersion='v1';
if (count($argv)===2&&in_array($argv[1],['m79-v2','registry-v2'],true)) {
    $argv[1]=substr($argv[1],0,-3); $receiptVersion='v2';
}
$review = <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if ($exit !== 0 || ($data['failed'] ?? null) !== 0 || ($data['writes'] ?? null) !== 0) {
    $failed=[];
    foreach ($data['rows'] ?? [] as $row) {
        if (($row['errors'] ?? []) !== []) {
            $failed[]=['identity'=>$row['identity']??null,'url'=>$row['url']??null,'errors'=>$row['errors']];
        }
    }
    fwrite(STDERR,json_encode(['scope'=>$argv[1],'command_exit'=>$exit,'targets'=>$data['targets']??null,
        'failed'=>$data['failed']??null,'writes'=>$data['writes']??null,'failed_rows'=>$failed,
        'context'=>array_intersect_key($data['context']??[],array_flip(['stage','owner','url','source_basename']))],
        JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
}
if ($argv[1] === 'registry') {
    $beforePath=$root.'/storage/app/seo-m79-local/registry-before-sentence-builders-v1.json';
    if (!is_file($beforePath)||is_link($beforePath)
        ||!hash_equals('7f83d78cb923466ddf6a306d12c205dd476aec60c79e979a617de864bb43b73a',hash_file('sha256',$beforePath))) {
        throw new RuntimeException('M79 independent registry BEFORE receipt bytes differ.');
    }
    $before=json_decode(file_get_contents($beforePath),true,flags:JSON_THROW_ON_ERROR);
    if (($before['targets']??null)!==163||($before['failed']??null)!==0||($before['writes']??null)!==0
        ||($data['targets']??null)!==169) { throw new RuntimeException('M79 registry finite old/new cardinality differs.'); }
    $current=[];
    foreach ($data['rows']??[] as $row) {
        $identity=$row['identity']??'';
        if ($identity===''||isset($current[$identity])) { throw new RuntimeException('M79 registry duplicate identity.'); }
        unset($row['http_sha256']); $current[$identity]=$row;
    }
    foreach ($before['rows'] as $row) {
        unset($row['http_sha256']);
        if (($current[$row['identity']]??null)!==$row) { throw new RuntimeException('M79 prior registry semantic row changed: '.$row['identity']); }
    }
    $data['previous_registry_targets_exact']=163;
    $data['registry_comparison_policy']='Every prior row field is exact except whole-response http_sha256, which contains volatile page chrome; no selected-question/config/owner/payload field is excluded.';
    $data['previous_registry_receipt_sha256']=hash_file('sha256',$beforePath);
}
PHP;
$profile=[
    ['M52','M79',7],['m52','m79',5],
    ['m79-passive-basics.v1.json','m79-question-types-completion.v1.json',1],
    ["(\$data['targets'] ?? null) !== 3","(\$data['targets'] ?? null) !== 6",1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);",$review,1],
];
if ($receiptVersion==='v2') { $profile[]=[".'-sentence-builders-v1.json'",".'-sentence-builders-v2.json'",1]; }
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',$profile);
