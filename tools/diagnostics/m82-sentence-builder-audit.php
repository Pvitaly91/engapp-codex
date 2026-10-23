<?php

/** Explicit six-owner/all-registry read-only audit; preserve every previous semantic row. */
require __DIR__.'/frozen-readonly-profile.php';
$receiptVersion='v1';
if(count($argv)===2&&in_array($argv[1],['m82-v2','registry-v2','m82-v3','registry-v3'],true)){
    $receiptVersion=substr($argv[1],-2);$argv[1]=substr($argv[1],0,-3);
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
    throw new RuntimeException('M82 read-only native registry command did not pass.');
}
$package=App\Support\M82CommonMistakesLevelRevision::class;
[$sourceBefore, $patch, $master, $contract]=$package::load($root);
$audit=App\Support\M82BuilderSelectionPresentation::loadAudit($root);
$revision=App\Support\M82WhitespaceRevision::class;
$catalogSourcePins=[
    'database/content-patches/m82-common-mistakes-level-revision.v1.0.2.json'=>'cc3744501c2f5b4852dcbb064a84339a3db13142fa5ac61b270380a1ac2ee2fa',
    'database/content-patches/m82-common-mistakes-level-revision.v1.json'=>'6b679b6ac5311ec91353430dd8e62f36aac62375891b16cf62001048eb0bdfc3',
];
$historicalPins=[
    $revision::PREVIOUS_MASTER=>'e39902a16dc7a162c83c12a1c63ae96d5e7c36036a4d3c677b9648d0ea9947e2',
    $revision::PREVIOUS_CHECKSUMS=>'67f13922e89873c27ea538abf358d5d3cef5376e1d676cf77a1a23cd789a5fe8',
];
$catalogSources=array_keys($catalogSourcePins);
if($catalogSources!==[$package::SOURCE,$revision::PREVIOUS_SOURCE]
    ||$package::SOURCE!==$revision::SOURCE||$package::SOURCE_SHA!==$catalogSourcePins[$package::SOURCE]
    ||$revision::PREVIOUS_SOURCE_SHA!==$catalogSourcePins[$revision::PREVIOUS_SOURCE]
    ||$revision::PREVIOUS_MASTER!=='docs/content/m82-common-mistakes-level-revision.v1.json'
    ||$revision::PREVIOUS_CHECKSUMS!=='docs/content/m82-source-checksums.v1.json'
    ||$revision::PREVIOUS_MASTER_SHA!==$historicalPins[$revision::PREVIOUS_MASTER]
    ||$revision::PREVIOUS_CHECKSUMS_SHA!==$historicalPins[$revision::PREVIOUS_CHECKSUMS]){
    throw new RuntimeException('M82 finite active/historical source lineage differs.');
}
foreach($catalogSourcePins+$historicalPins as $path=>$sha){
    if(!is_file($root.'/'.$path)||is_link($root.'/'.$path)||!hash_equals($sha,hash_file('sha256',$root.'/'.$path))){
        throw new RuntimeException('M82 pinned active/historical artifact bytes differ: '.$path);
    }
}
$previousPatch=json_decode(file_get_contents($root.'/'.$revision::PREVIOUS_SOURCE),true,flags:JSON_THROW_ON_ERROR);
$revision::assertTransition($previousPatch,$patch,$master,$sourceBefore,new App\Support\TheoryNativeContentRelease($package::profile()));
$owners=$package::OWNERS;
if(count($owners)!==6||array_column($contract['targets']??[],'identity')!==$owners
    ||array_column($patch['targets']??[],'identity')!==$owners||array_column($audit['targets']??[],'identity')!==$owners){
    throw new RuntimeException('M82 six frozen owner/contract/audit declarations differ.');
}
$current=[];
foreach($data['rows']??[] as $row){
    $identity=$row['identity']??'';
    if(!is_string($identity)||$identity===''||isset($current[$identity])){throw new RuntimeException('M82 duplicate/empty registry identity.');}
    unset($row['http_sha256']);$current[$identity]=$row;
}
foreach($owners as $i=>$owner){
    $row=$current[$owner]??null;$declared=$contract['targets'][$i];$selected=$audit['targets'][$i];$practice=$audit['practice_bindings'][$i];
    if(!is_array($row)||($row['errors']??null)!==[]||($row['ok']??null)!==true
        ||($row['url']??null)!==$declared['local_url']||($row['final_url']??null)!==$declared['local_url']
        ||($row['http_status']??null)!==200||($row['page_id']??null)!==$package::PAGE_IDS[$i]
        ||($row['practice_uuid']??null)!==$practice['uuid']||($row['sources']??null)!==$catalogSources
        ||($row['effective_config']??null)!==$practice['linked_practice']
        ||($row['selected_ids']??null)!==$selected['expected_selected_ids']
        ||($row['selected_types']??null)!==array_fill(0,5,'4')||($row['selected_count']??null)!==5
        ||($row['widget_count']??null)!==1||($row['practice_widget_count']??null)!==1){
        throw new RuntimeException('M82 exact new owner/page/practice/bank/selected-five/widget differs: '.$owner);
    }
}
if($argv[1]==='registry'){
    $path=$root.'/storage/app/seo-m82-local/registry-before-sentence-builders-v1.json';
    if(!is_file($path)||is_link($path)||!hash_equals('56236a621a0b6cad884aceef983d98d23ef7fd1dbecff9d7906966f470182a4c',hash_file('sha256',$path))){
        throw new RuntimeException('M82 fresh registry BEFORE bytes differ.');
    }
    $before=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$old=[];
    foreach($before['rows']??[] as $row){
        $identity=$row['identity']??'';
        if(!is_string($identity)||$identity===''||isset($old[$identity])){throw new RuntimeException('M82 duplicate/empty BEFORE registry identity.');}
        unset($row['http_sha256']);$old[$identity]=$row;
    }
    if(($before['failed']??null)!==0||($before['writes']??null)!==0||($before['targets']??null)!==count($old)
        ||array_intersect(array_keys($old),$owners)!==[]||($data['targets']??null)!==count($old)+count($owners)){
        throw new RuntimeException('M82 registry cardinality must be fresh old owners plus exactly the six new owners.');
    }
    foreach($old as $owner=>$row){if(($current[$owner]??null)!==$row){throw new RuntimeException('M82 previous registry semantic row changed: '.$owner);}}
    $expected=array_merge(array_keys($old),$owners);sort($expected,SORT_STRING);$actual=array_keys($current);sort($actual,SORT_STRING);
    if($actual!==$expected){throw new RuntimeException('M82 current registry identity set is not the exact old/new union.');}
    $data['previous_registry_targets_exact']=count($old);
    $data['new_manifest_targets_exact']=count($owners);
    $data['expected_registry_targets_from_fresh_union']=count($expected);
    $data['previous_registry_receipt_sha256']=hash_file('sha256',$path);
    $data['registry_comparison_policy']='Every prior field exact except volatile whole-response http_sha256; no owner/config/selected-question/payload field excluded. Total derives from the exact fresh BEFORE union, not a permanent cap.';
}else{
    $actual=array_keys($current);sort($actual,SORT_STRING);$expected=$owners;sort($expected,SORT_STRING);
    if($actual!==$expected||($data['targets']??null)!==count($owners)){throw new RuntimeException('M82 scoped audit contains non-manifest owners.');}
}
$data['catalog_source_lineage']=[
    'active_source'=>$package::SOURCE,'active_source_sha256'=>$catalogSourcePins[$package::SOURCE],
    'historical_source'=>$revision::PREVIOUS_SOURCE,'historical_source_sha256'=>$catalogSourcePins[$revision::PREVIOUS_SOURCE],
    'historical_master_sha256'=>$historicalPins[$revision::PREVIOUS_MASTER],
    'historical_checksums_sha256'=>$historicalPins[$revision::PREVIOUS_CHECKSUMS],
    'exact_catalog_sources'=>$catalogSources,'historical_after_declaration_retained'=>true,
    'active_source_filter'=>$argv[1]==='registry'?null:basename($package::SOURCE),
    'five_literal_whitespace_replacements_only'=>true,'sources_field_not_filtered_or_normalized'=>true,
    'old_registry_rows_unchanged_except_volatile_http_hash'=>$argv[1]==='registry',
];
PHP;
$profile=[['M52','M82',7],['m52','m82',5],
    ['m82-passive-basics.v1.json','m82-common-mistakes-level-revision.v1.0.2.json',1],
    ["(\$data['targets'] ?? null) !== 3","(\$data['targets'] ?? null) !== count(App\\Support\\M82CommonMistakesLevelRevision::OWNERS)",1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);",$review,1],
];
if(in_array($receiptVersion,['v2','v3'],true)){$profile[]=[".'-sentence-builders-v1.json'",".'-sentence-builders-".$receiptVersion.".json'",1];}
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',$profile);
