<?php

/**
 * M85 independent AFTER collection. Both original collectors remain immutable.
 * php -d memory_limit=2048M tools/diagnostics/m85-old-consumers-after.php audit
 *   after-old-consumers-v1.json before-old-consumers-v1.json <before-sha>
 *   final-readonly-v1.json <final-sha>
 * One loopback GET per endpoint; literal entry failures are retained, never retried or normalized.
 */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { throw new RuntimeException('M85 AFTER consumer collection is local CLI only.'); }
$m85AfterProfileOnly = ($argv[1] ?? null) === '--profile-check-only' && count($argv) === 2;
$m85AfterContext = [];
$m85ConsumerBeforeSha = '10fd77a4d0e500333b83907c44f675dae629f026160731a9ad7825e77335f5b7';
if (!$m85AfterProfileOnly) {
    if (count($argv) !== 7 || ($argv[1] ?? null) !== 'audit'
        || preg_match('/^after-old-consumers-v[1-9][0-9]*\.json$/D', $argv[2] ?? '') !== 1
        || ($argv[3] ?? null) !== 'before-old-consumers-v1.json' || ($argv[4] ?? null) !== $m85ConsumerBeforeSha
        || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M85 AFTER requires exact audit/output/consumer BEFORE pin and independent final receipt/hash.');
    }
    $m85Private = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m85-local';
    $m85FinalPath = $m85Private.'/'.$argv[5];
    if (!is_file($m85FinalPath) || is_link($m85FinalPath) || !hash_equals($argv[6], hash_file('sha256', $m85FinalPath))) {
        throw new RuntimeException('M85 independent final receipt is absent, linked or changed.');
    }
    $m85Final = json_decode(file_get_contents($m85FinalPath), true, flags: JSON_THROW_ON_ERROR);
    if (($m85Final['independent_before_sha256'] ?? null) !== 'fe639c06446c6fb46b5b4ab25ae654e3910b6733cc124a9e4bee262e41b411b7'
        || ($m85Final['all_old_rows_and_timestamps_exact'] ?? null) !== true
        || ($m85Final['independent_before_projection_exact'] ?? null) !== true
        || ($m85Final['reviewed_protected_projection_exact'] ?? null) !== true
        || ($m85Final['db_writes'] ?? null) !== 0 || ($m85Final['sql_tables'] ?? null) !== 46
        || count($m85Final['sql_after'] ?? []) !== 46 || count($m85Final['normalized_before'] ?? []) !== 46
        || ($m85Final['new_question_families'] ?? null) !== []) {
        throw new RuntimeException('M85 independent final reconstruction is incomplete or permits question-family writes.');
    }
    $m85AfterContext = ['final' => $m85Final, 'final_file' => $argv[5], 'final_sha256' => $argv[6],
        'consumer_before_sha256' => $m85ConsumerBeforeSha];
    $argv = array_slice($argv, 0, 5);
}
$m85Pins = [
    'm85-old-consumers.php' => '99a840178b7e93c3d1fcee0ed972667d5394d4c54e087d4ed49c334859e73fd4',
    'm52-old-consumers.php' => '1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',
    'frozen-readonly-profile.php' => 'f1a198df8fa602bd7a9e1c27048f383295c9a93dc6d0d4a287f1fffd6431ea5e',
];
foreach ($m85Pins as $m85File => $m85Sha) {
    $m85Path = __DIR__.'/'.$m85File;
    if (!is_file($m85Path) || is_link($m85Path) || !hash_equals($m85Sha, hash_file('sha256', $m85Path))) {
        throw new RuntimeException('M85 unchanged consumer primitive differs: '.$m85File);
    }
}
$m85Code = str_replace("\r\n", "\n", file_get_contents(__DIR__.'/m85-old-consumers.php'));
$m85Replace = static function (string $from, string $to) use (&$m85Code): void {
    if (substr_count($m85Code, $from) !== 1) { throw new RuntimeException('M85 AFTER finite profile literal is not unique: '.substr($from, 0, 100)); }
    $m85Code = str_replace($from, $to, $m85Code);
};
$m85Replace(<<<'PHP'
$profileOnly=($argv[1]??null)==='--profile-check-only'&&count($argv)===2;
if(!$profileOnly&&(($argv[1]??null)!=='capture'||count($argv)!==3)){throw new RuntimeException('M85 BEFORE profile supports capture only; no apply/audit shortcuts.');}
PHP, '$profileOnly=$m85AfterProfileOnly;');
$m85Replace(<<<'PHP'
if(count($allTablesBeforeHttp)!==46||$allTablesBeforeHttp!==$workingBefore['sql_before']){throw new RuntimeException('M85 all46 BEFORE SQL differs; no table exceptions.');}
PHP, <<<'PHP'
$final=$profileContext['final']??null;
if($mode!=='audit'||!is_array($final)||count($allTablesBeforeHttp)!==46
    ||($final['normalized_before']??null)!==$workingBefore['sql_before']
    ||($final['sql_after']??null)!==$allTablesBeforeHttp){
    throw new RuntimeException('M85 all46 AFTER SQL differs from independent exact final reconstruction; no table exceptions.');
}
PHP);
$m85Replace(<<<'PHP'
foreach($routes as $path=>$route){
 $http=$getLocal($route);
PHP, <<<'PHP'
$m85StrictDiffs=[];$m85RequestedPaths=[];
foreach($routes as $path=>$route){
 if(isset($m85RequestedPaths[$path])){throw new RuntimeException('M85 duplicate consumer request attempt.');}
 $m85RequestedPaths[$path]=true;
 $http=$getLocal($route);
PHP);
// Same literal full-entry comparator as M52. Collection records the failure and
// proceeds to the remaining finite endpoints; no field is removed from equality.
$m85Replace(<<<'PHP'
 $result['http'][$path]=$entry;echo $json(
PHP, <<<'PHP'
 if($before!==null&&($before['http'][$path]??null)!==$entry){
  $expected=$before['http'][$path]??null;
  if(!is_array($expected)){throw new RuntimeException('M85 BEFORE consumer entry is absent.');}
  $changed=[];foreach(array_values(array_unique([...array_keys($expected),...array_keys($entry)])) as $key){
   if(!array_key_exists($key,$expected)||!array_key_exists($key,$entry)||$expected[$key]!==$entry[$key]){$changed[]=$key;}
  }
  $m85StrictDiffs[$path]=['url'=>$entry['url'],'strict_status'=>'FAIL-retained','changed_top_level_fields'=>$changed,
   'before_entry_sha256'=>$digest($expected),'after_entry_sha256'=>$digest($entry),
   'before_body_sha256'=>$expected['body_sha256']??null,'after_body_sha256'=>$entry['body_sha256']??null,
   'policy'=>'Literal complete entry mismatch retained; exact BEFORE and AFTER entries remain in their respective bound private receipts. No normalization or retry.'];
 }
 $result['http'][$path]=$entry;echo $json(
PHP);
$m85Extra = <<<'PHP'
$m85AfterSummary=<<<'M85PHP'
if($mode!=='audit'||$before===null||count($routes)!==61||count($result['http'])!==61
    ||array_keys($routes)!==array_keys($before['http'])||array_keys($routes)!==array_keys($m85RequestedPaths)
    ||array_keys($result['http'])!==array_keys($before['http'])){
    throw new RuntimeException('M85 exact61 single-GET consumer boundary is incomplete.');
}
$m85Exceptions=[];
foreach($before['http'] as $path=>$entry){
 if(($entry['strict_requested_kind_acceptance']??null)!==true||($entry['strict_payload_acceptance']??null)!==true
     ||($entry['strict_expected_count_acceptance']??true)!==true||$entry['status']!==200){
  $m85Exceptions[$path]=['status'=>$entry['status'],'requested_kind'=>$entry['requested_consumer_kind'],
   'actual_kind'=>$entry['actual_consumer_kind'],'expected_count'=>$entry['expected_count'],
   'actual_count'=>$entry['payload']['count']??null,'acceptance'=>'Existing strict BEFORE exception remains not accepted, even if its entry is preserved exactly.'];
 }
}
if(count($m85Exceptions)!==7){throw new RuntimeException('M85 seven independent BEFORE exceptions differ.');}
$result['strict_http_comparison']=['policy'=>'Exact M52 full-entry inequality, without normalization, field exclusions, variants pinning or reruns.',
 'normalization_applied'=>false,'original_gate_modified'=>false,'collection_continues_after_strict_mismatch'=>true,
 'single_GET_per_endpoint'=>true,'endpoint_count'=>61,'strict_entries_exact'=>61-count($m85StrictDiffs),
 'failed_consumers'=>count($m85StrictDiffs),'strict_entry_comparison_pass'=>$m85StrictDiffs===[],
 'baseline_exception_count'=>7,'baseline_exceptions'=>$m85Exceptions,'strict_mismatches'=>$m85StrictDiffs];
$result['independent_final_receipt']=['file'=>$profileContext['final_file'],'sha256'=>$profileContext['final_sha256'],
 'all46_actual_after_fingerprints_exact'=>true,'all46_normalized_before_exact'=>true,'table_exceptions'=>[]];
M85PHP;
$replacements[]=["$"."result['unchanged_during_http'] = true; $"."result['finished_at'] = gmdate('c');",
 "$"."result['unchanged_during_http'] = true; $"."result['finished_at'] = gmdate('c');\n".$m85AfterSummary,1];
$replacements[]=["'status' => $"."mode === 'capture' ? 'independent-old-consumers-before' : 'old-consumers-preserved',",
 "'status' => $"."m85StrictDiffs === [] ? 'strict-entry-comparison-exact; baseline-exceptions-not-accepted' : 'strict-entry-FAIL-retained; all61-collected',",1];

PHP;
$m85Replace('if($profileOnly){', $m85Extra.'if($profileOnly){');
$m85Replace("'None; capture-only BEFORE profile, all46 tables exact.'", "'No table exceptions; all46 AFTER and normalized BEFORE bound to independent final reconstruction.'");
$m85Replace("'profile'=>'M85 old-consumer BEFORE'", "'profile'=>'M85 old-consumer AFTER; final-bound strict collection'");
$m85Replace("runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',\$replacements);",
    "runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',\$replacements,\$m85AfterContext);");
if (!str_starts_with($m85Code, "<?php\n")) { throw new RuntimeException('M85 immutable collector opening differs.'); }
eval(substr($m85Code, strlen("<?php\n")));
