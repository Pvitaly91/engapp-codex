<?php

/**
 * M86 independent AFTER collection. Both original collectors remain immutable.
 * php -d memory_limit=2048M tools/diagnostics/m86-old-consumers-after.php audit
 *   after-old-consumers-v1.json before-old-consumers-v1.json <before-sha>
 *   final-readonly-v1.json <final-sha>
 * One loopback GET per endpoint; literal entry failures are retained, never retried or normalized.
 */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { throw new RuntimeException('M86 AFTER consumer collection is local CLI only.'); }
$m86AfterProfileOnly = ($argv[1] ?? null) === '--profile-check-only' && count($argv) === 2;
$m86AfterContext = [];
$m86ConsumerBeforeSha = '1b02a5a54d5c4c2498f77b52f5f45afbc4183ce8eb66b12f0ed5bcd10f89ce14';
if (!$m86AfterProfileOnly) {
    if (count($argv) !== 7 || ($argv[1] ?? null) !== 'audit'
        || preg_match('/^after-old-consumers-v[1-9][0-9]*\.json$/D', $argv[2] ?? '') !== 1
        || ($argv[3] ?? null) !== 'before-old-consumers-v1.json' || ($argv[4] ?? null) !== $m86ConsumerBeforeSha
        || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M86 AFTER requires exact audit/output/consumer BEFORE pin and independent final receipt/hash.');
    }
    $m86Private = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m86-local';
    $m86FinalPath = $m86Private.'/'.$argv[5];
    if (!is_file($m86FinalPath) || is_link($m86FinalPath) || !hash_equals($argv[6], hash_file('sha256', $m86FinalPath))) {
        throw new RuntimeException('M86 independent final receipt is absent, linked or changed.');
    }
    $m86Final = json_decode(file_get_contents($m86FinalPath), true, flags: JSON_THROW_ON_ERROR);
    if (($m86Final['independent_before_sha256'] ?? null) !== '42f8a613fcb1721b2badcfabb9c67116f2c79fa9c14b7dc4df5e19dd71892208'
        || ($m86Final['all_old_rows_and_timestamps_exact'] ?? null) !== true
        || ($m86Final['independent_before_projection_exact'] ?? null) !== true
        || ($m86Final['reviewed_protected_projection_exact'] ?? null) !== true
        || ($m86Final['db_writes'] ?? null) !== 0 || ($m86Final['sql_tables'] ?? null) !== 46
        || count($m86Final['sql_after'] ?? []) !== 46 || count($m86Final['normalized_before'] ?? []) !== 46
        || ($m86Final['new_question_families'] ?? null) !== []) {
        throw new RuntimeException('M86 independent final reconstruction is incomplete or permits question-family writes.');
    }
    $m86AfterContext = ['final' => $m86Final, 'final_file' => $argv[5], 'final_sha256' => $argv[6],
        'consumer_before_sha256' => $m86ConsumerBeforeSha];
    $argv = array_slice($argv, 0, 5);
}
$m86Pins = [
    'm86-old-consumers.php' => '4c41ab4466f812b492ce43df57c6366e7bc681c8f882e74aeb940ba72fe5e7ce',
    'm52-old-consumers.php' => '1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',
    'frozen-readonly-profile.php' => 'f1a198df8fa602bd7a9e1c27048f383295c9a93dc6d0d4a287f1fffd6431ea5e',
];
foreach ($m86Pins as $m86File => $m86Sha) {
    $m86Path = __DIR__.'/'.$m86File;
    if (!is_file($m86Path) || is_link($m86Path) || !hash_equals($m86Sha, hash_file('sha256', $m86Path))) {
        throw new RuntimeException('M86 unchanged consumer primitive differs: '.$m86File);
    }
}
$m86Code = str_replace("\r\n", "\n", file_get_contents(__DIR__.'/m86-old-consumers.php'));
$m86Replace = static function (string $from, string $to) use (&$m86Code): void {
    if (substr_count($m86Code, $from) !== 1) { throw new RuntimeException('M86 AFTER finite profile literal is not unique: '.substr($from, 0, 100)); }
    $m86Code = str_replace($from, $to, $m86Code);
};
$m86Replace(<<<'PHP'
$profileOnly=($argv[1]??null)==='--profile-check-only'&&count($argv)===2;
if(!$profileOnly&&(($argv[1]??null)!=='capture'||count($argv)!==3)){throw new RuntimeException('M86 BEFORE profile supports capture only; no apply/audit shortcuts.');}
PHP, '$profileOnly=$m86AfterProfileOnly;');
$m86Replace(<<<'PHP'
if(count($allTablesBeforeHttp)!==46||$allTablesBeforeHttp!==$workingBefore['sql_before']){throw new RuntimeException('M86 all46 BEFORE SQL differs; no table exceptions.');}
PHP, <<<'PHP'
$final=$profileContext['final']??null;
if($mode!=='audit'||!is_array($final)||count($allTablesBeforeHttp)!==46
    ||($final['normalized_before']??null)!==$workingBefore['sql_before']
    ||($final['sql_after']??null)!==$allTablesBeforeHttp){
    throw new RuntimeException('M86 all46 AFTER SQL differs from independent exact final reconstruction; no table exceptions.');
}
PHP);
$m86Replace(<<<'PHP'
foreach($routes as $path=>$route){
 $http=$getLocal($route);
PHP, <<<'PHP'
$m86StrictDiffs=[];$m86RequestedPaths=[];
foreach($routes as $path=>$route){
 if(isset($m86RequestedPaths[$path])){throw new RuntimeException('M86 duplicate consumer request attempt.');}
 $m86RequestedPaths[$path]=true;
 $http=$getLocal($route);
PHP);
// Same literal full-entry comparator as M52. Collection records the failure and
// proceeds to the remaining finite endpoints; no field is removed from equality.
$m86Replace(<<<'PHP'
 $result['http'][$path]=$entry;echo $json(
PHP, <<<'PHP'
 if($before!==null&&($before['http'][$path]??null)!==$entry){
  $expected=$before['http'][$path]??null;
  if(!is_array($expected)){throw new RuntimeException('M86 BEFORE consumer entry is absent.');}
  $changed=[];foreach(array_values(array_unique([...array_keys($expected),...array_keys($entry)])) as $key){
   if(!array_key_exists($key,$expected)||!array_key_exists($key,$entry)||$expected[$key]!==$entry[$key]){$changed[]=$key;}
  }
  $m86StrictDiffs[$path]=['url'=>$entry['url'],'strict_status'=>'FAIL-retained','changed_top_level_fields'=>$changed,
   'before_entry_sha256'=>$digest($expected),'after_entry_sha256'=>$digest($entry),
   'before_body_sha256'=>$expected['body_sha256']??null,'after_body_sha256'=>$entry['body_sha256']??null,
   'policy'=>'Literal complete entry mismatch retained; exact BEFORE and AFTER entries remain in their respective bound private receipts. No normalization or retry.'];
 }
 $result['http'][$path]=$entry;echo $json(
PHP);
$m86Extra = <<<'PHP'
$m86AfterSummary=<<<'M86PHP'
if($mode!=='audit'||$before===null||count($routes)!==54||count($result['http'])!==54
    ||array_keys($routes)!==array_keys($before['http'])||array_keys($routes)!==array_keys($m86RequestedPaths)
    ||array_keys($result['http'])!==array_keys($before['http'])){
    throw new RuntimeException('M86 exact54 single-GET consumer boundary is incomplete.');
}
$m86Exceptions=[];
foreach($before['http'] as $path=>$entry){
 if(($entry['strict_requested_kind_acceptance']??null)!==true||($entry['strict_payload_acceptance']??null)!==true
     ||($entry['strict_expected_count_acceptance']??true)!==true||$entry['status']!==200){
  $m86Exceptions[$path]=['status'=>$entry['status'],'requested_kind'=>$entry['requested_consumer_kind'],
   'actual_kind'=>$entry['actual_consumer_kind'],'expected_count'=>$entry['expected_count'],
   'actual_count'=>$entry['payload']['count']??null,'acceptance'=>'Existing strict BEFORE exception remains not accepted, even if its entry is preserved exactly.'];
 }
}
if(count($m86Exceptions)!==1){throw new RuntimeException('M86 one independent BEFORE exception differs.');}
$result['strict_http_comparison']=['policy'=>'Exact M52 full-entry inequality, without normalization, field exclusions, variants pinning or reruns.',
 'normalization_applied'=>false,'original_gate_modified'=>false,'collection_continues_after_strict_mismatch'=>true,
 'single_GET_per_endpoint'=>true,'endpoint_count'=>54,'strict_entries_exact'=>54-count($m86StrictDiffs),
 'failed_consumers'=>count($m86StrictDiffs),'strict_entry_comparison_pass'=>$m86StrictDiffs===[],
 'baseline_exception_count'=>1,'baseline_exceptions'=>$m86Exceptions,'strict_mismatches'=>$m86StrictDiffs];
$result['independent_final_receipt']=['file'=>$profileContext['final_file'],'sha256'=>$profileContext['final_sha256'],
 'all46_actual_after_fingerprints_exact'=>true,'all46_normalized_before_exact'=>true,'table_exceptions'=>[]];
M86PHP;
$replacements[]=["$"."result['unchanged_during_http'] = true; $"."result['finished_at'] = gmdate('c');",
 "$"."result['unchanged_during_http'] = true; $"."result['finished_at'] = gmdate('c');\n".$m86AfterSummary,1];
$replacements[]=["'status' => $"."mode === 'capture' ? 'independent-old-consumers-before' : 'old-consumers-preserved',",
 "'status' => $"."m86StrictDiffs === [] ? 'strict-entry-comparison-exact; baseline-exceptions-not-accepted' : 'strict-entry-FAIL-retained; all54-collected',",1];

PHP;
$m86Replace('if($profileOnly){', $m86Extra.'if($profileOnly){');
$m86Replace("'None; capture-only BEFORE profile, all46 tables exact.'", "'No table exceptions; all46 AFTER and normalized BEFORE bound to independent final reconstruction.'");
$m86Replace("'profile'=>'M86 old-consumer BEFORE'", "'profile'=>'M86 old-consumer AFTER; final-bound strict collection'");
$m86Replace("runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',\$replacements);",
    "runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',\$replacements,\$m86AfterContext);");
if (!str_starts_with($m86Code, "<?php\n")) { throw new RuntimeException('M86 immutable collector opening differs.'); }
eval(substr($m86Code, strlen("<?php\n")));
