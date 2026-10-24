<?php

/**
 * M87 independent AFTER collection. Both original collectors remain immutable.
 * php -d memory_limit=2048M tools/diagnostics/m87-old-consumers-after.php audit
 *   after-old-consumers-v1.json before-old-consumers-v1.json <before-sha>
 *   final-readonly-v1.json <final-sha>
 * One loopback GET per endpoint; literal entry failures are retained, never retried or normalized.
 */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { throw new RuntimeException('M87 AFTER consumer collection is local CLI only.'); }
$m87AfterProfileOnly = ($argv[1] ?? null) === '--profile-check-only' && count($argv) === 2;
$m87AfterContext = [];
$m87ConsumerBeforeSha = '9ca77ac1e9691290f0e22a720d33aa141b5e6072895a869c4f0046681ada0221';
if (!$m87AfterProfileOnly) {
    if (count($argv) !== 7 || ($argv[1] ?? null) !== 'audit'
        || preg_match('/^after-old-consumers-v[1-9][0-9]*\.json$/D', $argv[2] ?? '') !== 1
        || ($argv[3] ?? null) !== 'before-old-consumers-v1.json' || ($argv[4] ?? null) !== $m87ConsumerBeforeSha
        || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M87 AFTER requires exact audit/output/consumer BEFORE pin and independent final receipt/hash.');
    }
    $m87Private = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m87-local';
    $m87FinalPath = $m87Private.'/'.$argv[5];
    if (!is_file($m87FinalPath) || is_link($m87FinalPath) || !hash_equals($argv[6], hash_file('sha256', $m87FinalPath))) {
        throw new RuntimeException('M87 independent final receipt is absent, linked or changed.');
    }
    $m87Final = json_decode(file_get_contents($m87FinalPath), true, flags: JSON_THROW_ON_ERROR);
    if (($m87Final['independent_before_sha256'] ?? null) !== 'c9b7cffb07a0440e5051a0b7c522eb016ce4cdcc80107edf6573e8780eb151c5'
        || ($m87Final['all_old_rows_and_timestamps_exact'] ?? null) !== true
        || ($m87Final['independent_before_projection_exact'] ?? null) !== true
        || ($m87Final['reviewed_protected_projection_exact'] ?? null) !== true
        || ($m87Final['db_writes'] ?? null) !== 0 || ($m87Final['sql_tables'] ?? null) !== 46
        || count($m87Final['sql_after'] ?? []) !== 46 || count($m87Final['normalized_before'] ?? []) !== 46
        || ($m87Final['new_question_families'] ?? null) !== []) {
        throw new RuntimeException('M87 independent final reconstruction is incomplete or permits question-family writes.');
    }
    $m87AfterContext = ['final' => $m87Final, 'final_file' => $argv[5], 'final_sha256' => $argv[6],
        'consumer_before_sha256' => $m87ConsumerBeforeSha];
    $argv = array_slice($argv, 0, 5);
}
$m87Pins = [
    'm87-old-consumers.php' => '11bbf402deeb36227544b368f7fe3af4f49871cf9eadcede7f0d5a62500e134a',
    'm52-old-consumers.php' => '1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',
    'frozen-readonly-profile.php' => 'f1a198df8fa602bd7a9e1c27048f383295c9a93dc6d0d4a287f1fffd6431ea5e',
];
foreach ($m87Pins as $m87File => $m87Sha) {
    $m87Path = __DIR__.'/'.$m87File;
    if (!is_file($m87Path) || is_link($m87Path) || !hash_equals($m87Sha, hash_file('sha256', $m87Path))) {
        throw new RuntimeException('M87 unchanged consumer primitive differs: '.$m87File);
    }
}
$m87Code = str_replace("\r\n", "\n", file_get_contents(__DIR__.'/m87-old-consumers.php'));
$m87Replace = static function (string $from, string $to) use (&$m87Code): void {
    if (substr_count($m87Code, $from) !== 1) { throw new RuntimeException('M87 AFTER finite profile literal is not unique: '.substr($from, 0, 100)); }
    $m87Code = str_replace($from, $to, $m87Code);
};
$m87Replace(<<<'PHP'
$profileOnly=($argv[1]??null)==='--profile-check-only'&&count($argv)===2;
if(!$profileOnly&&(($argv[1]??null)!=='capture'||count($argv)!==3)){throw new RuntimeException('M87 BEFORE profile supports capture only; no apply/audit shortcuts.');}
PHP, '$profileOnly=$m87AfterProfileOnly;');
$m87Replace(<<<'PHP'
if(count($allTablesBeforeHttp)!==46||$allTablesBeforeHttp!==$workingBefore['sql_before']){throw new RuntimeException('M87 all46 BEFORE SQL differs; no table exceptions.');}
PHP, <<<'PHP'
$final=$profileContext['final']??null;
if($mode!=='audit'||!is_array($final)||count($allTablesBeforeHttp)!==46
    ||($final['normalized_before']??null)!==$workingBefore['sql_before']
    ||($final['sql_after']??null)!==$allTablesBeforeHttp){
    throw new RuntimeException('M87 all46 AFTER SQL differs from independent exact final reconstruction; no table exceptions.');
}
PHP);
$m87Replace(<<<'PHP'
foreach($routes as $path=>$route){
 $http=$getLocal($route);
PHP, <<<'PHP'
$m87StrictDiffs=[];$m87RequestedPaths=[];
foreach($routes as $path=>$route){
 if(isset($m87RequestedPaths[$path])){throw new RuntimeException('M87 duplicate consumer request attempt.');}
 $m87RequestedPaths[$path]=true;
 $http=$getLocal($route);
PHP);
// Same literal full-entry comparator as M52. Collection records the failure and
// proceeds to the remaining finite endpoints; no field is removed from equality.
$m87Replace(<<<'PHP'
 $result['http'][$path]=$entry;echo $json(
PHP, <<<'PHP'
 if($before!==null&&($before['http'][$path]??null)!==$entry){
  $expected=$before['http'][$path]??null;
  if(!is_array($expected)){throw new RuntimeException('M87 BEFORE consumer entry is absent.');}
  $changed=[];foreach(array_values(array_unique([...array_keys($expected),...array_keys($entry)])) as $key){
   if(!array_key_exists($key,$expected)||!array_key_exists($key,$entry)||$expected[$key]!==$entry[$key]){$changed[]=$key;}
  }
  $m87StrictDiffs[$path]=['url'=>$entry['url'],'strict_status'=>'FAIL-retained','changed_top_level_fields'=>$changed,
   'before_entry_sha256'=>$digest($expected),'after_entry_sha256'=>$digest($entry),
   'before_body_sha256'=>$expected['body_sha256']??null,'after_body_sha256'=>$entry['body_sha256']??null,
   'policy'=>'Literal complete entry mismatch retained; exact BEFORE and AFTER entries remain in their respective bound private receipts. No normalization or retry.'];
 }
 $result['http'][$path]=$entry;echo $json(
PHP);
$m87Extra = <<<'PHP'
$m87AfterSummary=<<<'M87PHP'
if($mode!=='audit'||$before===null||count($routes)!==54||count($result['http'])!==54
    ||array_keys($routes)!==array_keys($before['http'])||array_keys($routes)!==array_keys($m87RequestedPaths)
    ||array_keys($result['http'])!==array_keys($before['http'])){
    throw new RuntimeException('M87 exact54 single-GET consumer boundary is incomplete.');
}
$m87Exceptions=[];
foreach($before['http'] as $path=>$entry){
 if(($entry['strict_requested_kind_acceptance']??null)!==true||($entry['strict_payload_acceptance']??null)!==true
     ||($entry['strict_expected_count_acceptance']??true)!==true||$entry['status']!==200){
  $m87Exceptions[$path]=['status'=>$entry['status'],'requested_kind'=>$entry['requested_consumer_kind'],
   'actual_kind'=>$entry['actual_consumer_kind'],'expected_count'=>$entry['expected_count'],
   'actual_count'=>$entry['payload']['count']??null,'acceptance'=>'Existing strict BEFORE exception remains not accepted, even if its entry is preserved exactly.'];
 }
}
if(count($m87Exceptions)!==2){throw new RuntimeException('M87 two independent BEFORE exceptions differs.');}
$result['strict_http_comparison']=['policy'=>'Exact M52 full-entry inequality, without normalization, field exclusions, variants pinning or reruns.',
 'normalization_applied'=>false,'original_gate_modified'=>false,'collection_continues_after_strict_mismatch'=>true,
 'single_GET_per_endpoint'=>true,'endpoint_count'=>54,'strict_entries_exact'=>54-count($m87StrictDiffs),
 'failed_consumers'=>count($m87StrictDiffs),'strict_entry_comparison_pass'=>$m87StrictDiffs===[],
 'baseline_exception_count'=>2,'baseline_exceptions'=>$m87Exceptions,'strict_mismatches'=>$m87StrictDiffs];
$result['independent_final_receipt']=['file'=>$profileContext['final_file'],'sha256'=>$profileContext['final_sha256'],
 'all46_actual_after_fingerprints_exact'=>true,'all46_normalized_before_exact'=>true,'table_exceptions'=>[]];
M87PHP;
$replacements[]=["$"."result['unchanged_during_http'] = true; $"."result['finished_at'] = gmdate('c');",
 "$"."result['unchanged_during_http'] = true; $"."result['finished_at'] = gmdate('c');\n".$m87AfterSummary,1];
$replacements[]=["'status' => $"."mode === 'capture' ? 'independent-old-consumers-before' : 'old-consumers-preserved',",
 "'status' => $"."m87StrictDiffs === [] ? 'strict-entry-comparison-exact; baseline-exceptions-not-accepted' : 'strict-entry-FAIL-retained; all54-collected',",1];

PHP;
$m87Replace('if($profileOnly){', $m87Extra.'if($profileOnly){');
$m87Replace("'None; capture-only BEFORE profile, all46 tables exact.'", "'No table exceptions; all46 AFTER and normalized BEFORE bound to independent final reconstruction.'");
$m87Replace("'profile'=>'M87 old-consumer BEFORE'", "'profile'=>'M87 old-consumer AFTER; final-bound strict collection'");
$m87Replace("runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',\$replacements);",
    "runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',\$replacements,\$m87AfterContext);");
if (!str_starts_with($m87Code, "<?php\n")) { throw new RuntimeException('M87 immutable collector opening differs.'); }
eval(substr($m87Code, strlen("<?php\n")));
