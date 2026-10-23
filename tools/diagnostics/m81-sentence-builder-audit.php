<?php

/** Explicit six-owner/all-registry read-only audit; preserve every previous semantic row. */
require __DIR__.'/frozen-readonly-profile.php';
$receiptVersion='v1';
if(count($argv)===2&&in_array($argv[1],['m81-v2','registry-v2'],true)){
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
if($exit!==0||($data['failed']??null)!==0||($data['writes']??null)!==0){
    throw new RuntimeException('M81 read-only native registry command did not pass.');
}
$package=App\Support\M81TransformationsCommonMistakes::class;
[, $patch, , $contract]=$package::load($root);
$audit=App\Support\M81BuilderSelectionPresentation::loadAudit($root);
$owners=$package::OWNERS;
if(count($owners)!==6||array_column($contract['targets']??[],'identity')!==$owners
    ||array_column($patch['targets']??[],'identity')!==$owners||array_column($audit['targets']??[],'identity')!==$owners){
    throw new RuntimeException('M81 six frozen owner/contract/audit declarations differ.');
}
$current=[];
foreach($data['rows']??[] as $row){
    $identity=$row['identity']??'';
    if(!is_string($identity)||$identity===''||isset($current[$identity])){throw new RuntimeException('M81 duplicate/empty registry identity.');}
    unset($row['http_sha256']);$current[$identity]=$row;
}
foreach($owners as $i=>$owner){
    $row=$current[$owner]??null;$declared=$contract['targets'][$i];$selected=$audit['targets'][$i];$practice=$audit['practice_bindings'][$i];
    if(!is_array($row)||($row['errors']??null)!==[]||($row['ok']??null)!==true
        ||($row['url']??null)!==$declared['local_url']||($row['final_url']??null)!==$declared['local_url']
        ||($row['http_status']??null)!==200||($row['page_id']??null)!==$package::PAGE_IDS[$i]
        ||($row['practice_uuid']??null)!==$practice['uuid']||($row['sources']??null)!==[$package::SOURCE]
        ||($row['effective_config']??null)!==$practice['linked_practice']
        ||($row['selected_ids']??null)!==$selected['expected_selected_ids']
        ||($row['selected_types']??null)!==array_fill(0,5,'4')||($row['selected_count']??null)!==5
        ||($row['widget_count']??null)!==1||($row['practice_widget_count']??null)!==1){
        throw new RuntimeException('M81 exact new owner/page/practice/bank/selected-five/widget differs: '.$owner);
    }
}
if($argv[1]==='registry'){
    $path=$root.'/storage/app/seo-m81-local/registry-before-sentence-builders-v1.json';
    if(!is_file($path)||is_link($path)||!hash_equals('33547faa7aca047007ab124f9132e1f28bb74940a200e6bce5af9f2d94915f65',hash_file('sha256',$path))){
        throw new RuntimeException('M81 fresh registry BEFORE bytes differ.');
    }
    $before=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$old=[];
    foreach($before['rows']??[] as $row){
        $identity=$row['identity']??'';
        if(!is_string($identity)||$identity===''||isset($old[$identity])){throw new RuntimeException('M81 duplicate/empty BEFORE registry identity.');}
        unset($row['http_sha256']);$old[$identity]=$row;
    }
    if(($before['failed']??null)!==0||($before['writes']??null)!==0||($before['targets']??null)!==count($old)
        ||array_intersect(array_keys($old),$owners)!==[]||($data['targets']??null)!==count($old)+count($owners)){
        throw new RuntimeException('M81 registry cardinality must be fresh old owners plus exactly the six new owners.');
    }
    foreach($old as $owner=>$row){if(($current[$owner]??null)!==$row){throw new RuntimeException('M81 previous registry semantic row changed: '.$owner);}}
    $expected=array_merge(array_keys($old),$owners);sort($expected,SORT_STRING);$actual=array_keys($current);sort($actual,SORT_STRING);
    if($actual!==$expected){throw new RuntimeException('M81 current registry identity set is not the exact old/new union.');}
    $data['previous_registry_targets_exact']=count($old);
    $data['new_manifest_targets_exact']=count($owners);
    $data['expected_registry_targets_from_fresh_union']=count($expected);
    $data['previous_registry_receipt_sha256']=hash_file('sha256',$path);
    $data['registry_comparison_policy']='Every prior field exact except volatile whole-response http_sha256; no owner/config/selected-question/payload field excluded. Total derives from the exact fresh BEFORE union, not a permanent cap.';
}else{
    $actual=array_keys($current);sort($actual,SORT_STRING);$expected=$owners;sort($expected,SORT_STRING);
    if($actual!==$expected||($data['targets']??null)!==count($owners)){throw new RuntimeException('M81 scoped audit contains non-manifest owners.');}
}
PHP;
$profile=[['M52','M81',7],['m52','m81',5],
    ['m81-passive-basics.v1.json','m81-transformations-common-mistakes.v1.json',1],
    ["(\$data['targets'] ?? null) !== 3","(\$data['targets'] ?? null) !== count(App\\Support\\M81TransformationsCommonMistakes::OWNERS)",1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);",$review,1],
];
if($receiptVersion==='v2'){$profile[]=[".'-sentence-builders-v1.json'",".'-sentence-builders-v2.json'",1];}
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',$profile);
