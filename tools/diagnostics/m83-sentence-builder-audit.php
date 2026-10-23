<?php

/** Explicit M83 scoped/global native command, complete SQL preservation and old187 semantic comparison. */
require __DIR__.'/frozen-readonly-profile.php';
$setup=<<<'PHP'
$db=Illuminate\Support\Facades\DB::connection();
$private=$root.'/storage/app/seo-m83-local';
$finalPath=$private.'/final-readonly-v1.json';
$finalSha='80b967363dfd4c99167044d4a72cb0f827147fecaac8788caa2f5709158c3b15';
if(!is_file($finalPath)||is_link($finalPath)||!hash_equals($finalSha,hash_file('sha256',$finalPath))){
    throw new RuntimeException('M83 independently completed final AFTER receipt differs.');
}
$final=json_decode(file_get_contents($finalPath),true,flags:JSON_THROW_ON_ERROR);
if(($final['sql_tables']??null)!==46||count($final['sql_after']??[])!==46||($final['db_writes']??null)!==0
    ||($final['http_requests']??null)!==0){throw new RuntimeException('M83 final AFTER fingerprint proof is incomplete.');}
$scannerPath=__DIR__.'/m52-working-local.php';
if(!is_file($scannerPath)||is_link($scannerPath)
    ||!hash_equals('9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d',hash_file('sha256',$scannerPath))){
    throw new RuntimeException('M83 unchanged SELECT-only fingerprint primitive differs.');
}
$digest=static fn($value):string=>App\Services\M26ContentPatch::digest($value);
$fingerprints = static function () use ($db, $digest): array {
    $tables = $db->getSchemaBuilder()->getTableListing(schema: $db->getDatabaseName(), schemaQualified: false); sort($tables);
    $out = [];
    foreach ($tables as $table) {
        $columns = $db->getSchemaBuilder()->getColumnListing($table); $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $hash = hash_init('sha256'); $count = 0;
        foreach ($query->cursor() as $record) { hash_update($hash, $digest((array) $record)."\n"); ++$count; }
        $out[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
    }
    return $out;
};
$readOnlyFingerprint=static function()use($db,$fingerprints):array{
    if($db->transactionLevel()!==0){throw new RuntimeException('M83 registry fingerprint requires no ambient transaction.');}
    $db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
    try{return $fingerprints();}finally{$db->rollBack();}
};
$sqlBeforeAudit=$readOnlyFingerprint();
if($sqlBeforeAudit!==$final['sql_after']){throw new RuntimeException('M83 all46 tables differ from independently completed final AFTER before registry GET.');}

PHP;
$review=<<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
$sqlAfterAudit=$readOnlyFingerprint();
if($sqlAfterAudit!==$sqlBeforeAudit||$sqlAfterAudit!==$final['sql_after']){
    throw new RuntimeException('M83 read-only registry/GET changed at least one complete SQL table fingerprint.');
}
if($exit!==0||($data['failed']??null)!==0||($data['writes']??null)!==0){
    $failed=[];foreach($data['rows']??[] as $row){if(($row['errors']??[])!==[]){$failed[]=['identity'=>$row['identity']??null,'url'=>$row['url']??null,'errors'=>$row['errors']];}}
    fwrite(STDERR,json_encode(['scope'=>$argv[1],'command_exit'=>$exit,'targets'=>$data['targets']??null,'failed'=>$data['failed']??null,
        'writes'=>$data['writes']??null,'failed_rows'=>$failed,'context'=>$data['context']??null],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
    throw new RuntimeException('M83 actual native read-only CLI matcher/local HTTP registry gate did not pass.');
}
$package=App\Support\M83B2RevisionBasicGrammar::class;
[$sourceBefore,$patch,$master,$contract]=$package::load($root);
$package::validate($sourceBefore,$patch,$master,$contract);
$audit=App\Support\M83BuilderSelectionPresentation::loadAudit($root);
$owners=$package::OWNERS;
if(count($owners)!==6||array_column($contract['targets'],'identity')!==$owners
    ||array_column($patch['targets'],'identity')!==$owners||array_column($audit['targets'],'identity')!==$owners
    ||($data['runtime']??null)!==$final['runtime']){
    throw new RuntimeException('M83 exact frozen owners or verified local runtime differ.');
}
$current=[];$httpHashes=[];
foreach($data['rows']??[] as $row){
    $owner=$row['identity']??'';
    if(!is_string($owner)||$owner===''||isset($current[$owner])){throw new RuntimeException('M83 registry has duplicate/empty owner.');}
    if(!is_string($row['http_sha256']??null)||preg_match('/^[a-f0-9]{64}$/D',$row['http_sha256'])!==1){
        throw new RuntimeException('M83 fresh native HTTP raw response hash missing.');
    }
    $httpHashes[$owner]=$row['http_sha256'];unset($row['http_sha256']);$current[$owner]=$row;
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
        throw new RuntimeException('M83 new owner/page/native practice/actual filter/bank/selected-five/widget differs: '.$owner);
    }
}
$oldReceiptSha='ce54b0f4a88fdab0458fce7dd27a7be83560b04f6cb979051ee488e405bf8747';
if($argv[1]==='registry'){
    $path=$private.'/registry-before-sentence-builders-v1.json';
    if(!is_file($path)||is_link($path)||!hash_equals($oldReceiptSha,hash_file('sha256',$path))){
        throw new RuntimeException('M83 fresh pre-registration registry BEFORE differs.');
    }
    $before=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$old=[];$changedHttp=[];$sameHttp=0;
    foreach($before['rows']??[] as $row){
        $owner=$row['identity']??'';
        if(!is_string($owner)||$owner===''||isset($old[$owner])){throw new RuntimeException('M83 BEFORE duplicate/empty owner.');}
        if(($httpHashes[$owner]??null)===$row['http_sha256']){$sameHttp++;}else{$changedHttp[]=$owner;}
        unset($row['http_sha256']);$old[$owner]=$row;
    }
    if(($before['targets']??null)!==187||count($old)!==187||($before['failed']??null)!==0||($before['writes']??null)!==0
        ||array_intersect(array_keys($old),$owners)!==[]||($data['targets']??null)!==count($old)+count($owners)){
        throw new RuntimeException('M83 registry total is not exact fresh187 plus completed6.');
    }
    foreach($old as $owner=>$row){if(($current[$owner]??null)!==$row){throw new RuntimeException('M83 previous registry semantic row changed: '.$owner);}}
    $expected=[...array_keys($old),...$owners];sort($expected,SORT_STRING);$actual=array_keys($current);sort($actual,SORT_STRING);
    if($actual!==$expected||count($actual)!==193){throw new RuntimeException('M83 global owner set is not exact old/new union.');}
    $data['previous_registry_comparison']=['before_receipt_sha256'=>$oldReceiptSha,'exact_prior_semantic_rows'=>187,
        'new_manifest_rows'=>6,'actual_after_union'=>193,'whole_http_response_hash_equal'=>$sameHttp,
        'whole_http_response_hash_different'=>count($changedHttp),'http_hash_different_owners'=>$changedHttp,
        'only_excluded_row_field'=>'http_sha256','all_other_prior_fields_byte_exact'=>true,
        'whole_http_response_equality_claimed'=>false];
}else{
    $actual=array_keys($current);sort($actual,SORT_STRING);$expected=$owners;sort($expected,SORT_STRING);
    if($actual!==$expected||($data['targets']??null)!==6){throw new RuntimeException('M83 scoped CLI includes non-manifest owners.');}
}
$data['m83_readonly_evidence']=['final_after_receipt_sha256'=>$finalSha,'sql_before_cli'=>$sqlBeforeAudit,'sql_after_cli'=>$sqlAfterAudit,
    'all_46_sql_tables_equal_final_after'=>true,'unchanged_fingerprint_primitive'=>'tools/diagnostics/m52-working-local.php',
    'unchanged_fingerprint_primitive_sha256'=>'9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d',
    'actual_cli_invocation'=>['command'=>'theory:audit-sentence-builders','options'=>$options,
        'mechanism'=>'Actual existing Artisan command via Artisan::call inside this explicitly invoked Windows CLI helper; not a reimplementation.'],
    'actual_command_exit'=>$exit,'scoped_source'=>$argv[1]==='m83'?basename($package::SOURCE):null,
    'browser_acceptance_claimed'=>false,'sql_writes'=>0,'http_scope'=>'Fresh native command GETs only http://gramlyze.loc/theory/* resolved to127.0.0.1; redirects disabled.'];

PHP;
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',[
    ['M52','M83',7],['m52','m83',5],
    ['m83-passive-basics.v1.json','m83-b2-revision-basic-grammar.v1.json',1],
    ["(\$data['targets'] ?? null) !== 3","(\$data['targets'] ?? null) !== count(App\\Support\\M83B2RevisionBasicGrammar::OWNERS)",1],
    ['App\\Services\\M83LocalTargetGuard::assertConfiguredDatabase(Illuminate\\Support\\Facades\\DB::connection());',
        'App\\Services\\M83LocalTargetGuard::assertConfiguredDatabase(Illuminate\\Support\\Facades\\DB::connection());'."\n".$setup,1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);",$review,1],
]);
