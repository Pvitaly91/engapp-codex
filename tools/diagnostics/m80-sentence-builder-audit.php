<?php

/** Six-owner/all-registry GET audit; every old semantic row remains exact. */
require __DIR__.'/frozen-readonly-profile.php';
$receiptVersion='v1';
if(count($argv)===2&&in_array($argv[1],['m80-v2','registry-v2'],true)){
    $argv[1]=substr($argv[1],0,-3);$receiptVersion='v2';
}
$review=<<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if ($exit !== 0 || ($data['failed'] ?? null) !== 0 || ($data['writes'] ?? null) !== 0) {
    $failed=[];
    foreach($data['rows']??[] as $row){if(($row['errors']??[])!==[]){$failed[]=['identity'=>$row['identity']??null,'url'=>$row['url']??null,'errors'=>$row['errors']];}}
    fwrite(STDERR,json_encode(['scope'=>$argv[1],'command_exit'=>$exit,'targets'=>$data['targets']??null,
        'failed'=>$data['failed']??null,'writes'=>$data['writes']??null,'failed_rows'=>$failed,
        'context'=>array_intersect_key($data['context']??[],array_flip(['stage','owner','url','source_basename']))],
        JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
}
if($argv[1]==='registry'){
    $path=$root.'/storage/app/seo-m80-local/registry-before-sentence-builders-v1.json';
    if(!is_file($path)||is_link($path)||!hash_equals('31c537a05a7aecca2a55fca54816c3151e0efa9e1ae8cf42c66ab9bfe721c287',hash_file('sha256',$path))){
        throw new RuntimeException('M80 fresh registry BEFORE bytes differ.');
    }
    $before=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
    if(($before['targets']??null)!==169||($before['failed']??null)!==0||($before['writes']??null)!==0||($data['targets']??null)!==175){
        throw new RuntimeException('M80 exact old/new registry cardinality differs.');
    }
    $current=[];
    foreach($data['rows']??[] as $row){$id=$row['identity']??'';if($id===''||isset($current[$id])){throw new RuntimeException('M80 duplicate registry identity.');}unset($row['http_sha256']);$current[$id]=$row;}
    foreach($before['rows'] as $row){unset($row['http_sha256']);if(($current[$row['identity']]??null)!==$row){throw new RuntimeException('M80 previous registry semantic row changed: '.$row['identity']);}}
    $data['previous_registry_targets_exact']=169;
    $data['previous_registry_receipt_sha256']=hash_file('sha256',$path);
    $data['registry_comparison_policy']='Every prior field exact except volatile whole-response http_sha256; no owner/config/selected-question/payload field excluded.';
}
PHP;
$profile=[['M52','M80',7],['m52','m80',5],
    ['m80-passive-basics.v1.json','m80-negatives-transformations.v1.json',1],
    ["(\$data['targets'] ?? null) !== 3","(\$data['targets'] ?? null) !== 6",1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);",$review,1],
];
if($receiptVersion==='v2'){$profile[]=[".'-sentence-builders-v1.json'",".'-sentence-builders-v2.json'",1];}
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',$profile);
