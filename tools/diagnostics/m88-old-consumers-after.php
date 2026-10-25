<?php

/**
 * M88 independent AFTER collection. Both original collectors remain immutable.
 * php -d memory_limit=2048M tools/diagnostics/m88-old-consumers-after.php audit
 *   after-old-consumers-v1.json before-old-consumers-v4.json <before-sha>
 *   final-readonly-v1.json <final-sha>
 * One loopback GET per endpoint; literal entry failures are retained, never retried or normalized.
 */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { throw new RuntimeException('M88 AFTER consumer collection is local CLI only.'); }
$m88AfterProfileOnly = ($argv[1] ?? null) === '--profile-check-only' && count($argv) === 2;
$m88AfterContext = [];
$m88ConsumerBeforeSha = '9c245aeb1c7cf1b067cc3a6d6d154fa60570446b04d6fba0279a7cdb1b9c1f77';
if (!$m88AfterProfileOnly) {
    if (count($argv) !== 7 || ($argv[1] ?? null) !== 'audit'
        || preg_match('/^after-old-consumers-v[1-9][0-9]*\.json$/D', $argv[2] ?? '') !== 1
        || ($argv[3] ?? null) !== 'before-old-consumers-v4.json' || ($argv[4] ?? null) !== $m88ConsumerBeforeSha
        || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M88 AFTER requires exact audit/output/consumer BEFORE pin and independent final receipt/hash.');
    }
    $m88Private = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m88-local';
    $m88FinalPath = $m88Private.'/'.$argv[5];
    if (!is_file($m88FinalPath) || is_link($m88FinalPath) || !hash_equals($argv[6], hash_file('sha256', $m88FinalPath))) {
        throw new RuntimeException('M88 independent final receipt is absent, linked or changed.');
    }
    $m88Final = json_decode(file_get_contents($m88FinalPath), true, flags: JSON_THROW_ON_ERROR);
    if (($m88Final['independent_before_sha256'] ?? null) !== 'ea09671a15af34ba0045b195b13e97a9581a38a308f6e136dd0c02d457b23abc'
        || ($m88Final['all_old_rows_and_timestamps_exact'] ?? null) !== true
        || ($m88Final['independent_before_projection_exact'] ?? null) !== true
        || ($m88Final['reviewed_protected_projection_exact'] ?? null) !== true
        || ($m88Final['db_writes'] ?? null) !== 0 || ($m88Final['sql_tables'] ?? null) !== 46
        || count($m88Final['sql_after'] ?? []) !== 46 || count($m88Final['normalized_before'] ?? []) !== 46
        || ($m88Final['new_question_families'] ?? null) !== []
        || ($m88Final['continuing_uk_rows'] ?? null) !== 51 || ($m88Final['unchanged_en_pl_rows'] ?? null) !== 102
        || ($m88Final['update_operations'] ?? null) !== 45 || count($m88Final['new_practices'] ?? []) !== 4
        || ($m88Final['old_consumer_sql']['original_banks'] ?? null) !== 15
        || ($m88Final['old_consumer_sql']['original_questions'] ?? null) !== 984
        || ($m88Final['old_consumer_sql']['page_linked_unique_questions'] ?? null) !== 918
        || ($m88Final['old_consumer_sql']['saved_tests'] ?? null) !== 15
        || ($m88Final['old_consumer_sql']['full_bank_only_question_ids'] ?? null) !== [...range(6638,6673),...range(6680,6709)]) {
        throw new RuntimeException('M88 independent final reconstruction is incomplete or permits question-family writes.');
    }
    $m88AfterContext = ['final' => $m88Final, 'final_file' => $argv[5], 'final_sha256' => $argv[6],
        'consumer_before_sha256' => $m88ConsumerBeforeSha];
    $argv = array_slice($argv, 0, 5);
}
$m88Diagnostics='D:/DEV/htdocs/gramlyze.loc/tools/diagnostics';
$m88Collector='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m88-local/old-consumers-before-v4.php';
$m88Pins = [
    $m88Collector => '68e0957f150ba5bc6bf3295ff73f032a6b9a155c2b3ec335a117744df69ca62b',
    $m88Diagnostics.'/m52-old-consumers.php' => '1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',
    $m88Diagnostics.'/frozen-readonly-profile.php' => 'f1a198df8fa602bd7a9e1c27048f383295c9a93dc6d0d4a287f1fffd6431ea5e',
];
foreach ($m88Pins as $m88File => $m88Sha) {
    $m88Path = $m88File;
    if (!is_file($m88Path) || is_link($m88Path) || !hash_equals($m88Sha, hash_file('sha256', $m88Path))) {
        throw new RuntimeException('M88 unchanged consumer primitive differs: '.$m88File);
    }
}
$m88Code = str_replace("\r\n", "\n", file_get_contents($m88Collector));
$m88Replace = static function (string $from, string $to) use (&$m88Code): void {
    if (substr_count($m88Code, $from) !== 1) { throw new RuntimeException('M88 AFTER finite profile literal is not unique: '.substr($from, 0, 100)); }
    $m88Code = str_replace($from, $to, $m88Code);
};
$m88Replace(<<<'PHP'
$profileOnly=($argv[1]??null)==='--profile-check-only'&&count($argv)===2;
if(!$profileOnly&&(($argv[1]??null)!=='capture'||count($argv)!==3)){throw new RuntimeException('M88 BEFORE profile supports capture only; no apply/audit shortcuts.');}
PHP, '$profileOnly=$m88AfterProfileOnly;');
$m88Replace(<<<'PHP'
if(count($allTablesBeforeHttp)!==46||$allTablesBeforeHttp!==$workingBefore['sql_before']){throw new RuntimeException('M88 all46 BEFORE SQL differs; no table exceptions.');}
PHP, <<<'PHP'
$final=$profileContext['final']??null;
if($mode!=='audit'||!is_array($final)||count($allTablesBeforeHttp)!==46
    ||($final['normalized_before']??null)!==$workingBefore['sql_before']
    ||($final['sql_after']??null)!==$allTablesBeforeHttp){
    throw new RuntimeException('M88 all46 AFTER SQL differs from independent exact final reconstruction; no table exceptions.');
}
PHP);
$m88Replace("foreach(\$routes as \$path=>\$route){\n \$http=\$getLocal(\$route);", <<<'PHP'
$m88StrictDiffs=[];$m88RequestedPaths=[];
foreach($routes as $path=>$route){
 if(isset($m88RequestedPaths[$path])){throw new RuntimeException('M88 duplicate consumer request attempt.');}
 $m88RequestedPaths[$path]=true;
 $http=$getLocal($route);
PHP);
// Same literal full-entry comparator as M52. Collection records the failure and
// proceeds to the remaining finite endpoints; no field is removed from equality.
$m88Replace(" \$result['http'][\$path]=\$entry;echo \$json(", <<<'PHP'
 if($before!==null&&($before['http'][$path]??null)!==$entry){
  $expected=$before['http'][$path]??null;
  if(!is_array($expected)){throw new RuntimeException('M88 BEFORE consumer entry is absent.');}
  $changed=[];foreach(array_values(array_unique([...array_keys($expected),...array_keys($entry)])) as $key){
   if(!array_key_exists($key,$expected)||!array_key_exists($key,$entry)||$expected[$key]!==$entry[$key]){$changed[]=$key;}
  }
  $m88StrictDiffs[$path]=['url'=>$entry['url'],'strict_status'=>'FAIL-retained','changed_top_level_fields'=>$changed,
   'before_entry_sha256'=>$digest($expected),'after_entry_sha256'=>$digest($entry),
   'before_body_sha256'=>$expected['body_sha256']??null,'after_body_sha256'=>$entry['body_sha256']??null,
   'policy'=>'Literal complete entry mismatch retained; exact BEFORE and AFTER entries remain in their respective bound private receipts. No normalization or retry.'];
 }
 $result['http'][$path]=$entry;echo $json(
PHP);
$m88Extra = <<<'PHP'
$m88AfterSummary=<<<'M88PHP'
if($mode!=='audit'||$before===null||count($routes)!==57||count($result['http'])!==57
    ||array_keys($routes)!==array_keys($before['http'])||array_keys($routes)!==array_keys($m88RequestedPaths)
    ||array_keys($result['http'])!==array_keys($before['http'])){
    throw new RuntimeException('M88 exact57 single-GET consumer boundary is incomplete.');
}
$m88Exceptions=[];
foreach($before['http'] as $path=>$entry){
 if(($entry['strict_requested_kind_acceptance']??null)!==true||($entry['strict_payload_acceptance']??null)!==true
     ||($entry['strict_expected_count_acceptance']??true)!==true||$entry['status']!==200){
  $m88Exceptions[$path]=['status'=>$entry['status'],'requested_kind'=>$entry['requested_consumer_kind'],
   'actual_kind'=>$entry['actual_consumer_kind'],'expected_count'=>$entry['expected_count'],
   'actual_count'=>$entry['payload']['count']??null,'acceptance'=>'Existing strict BEFORE exception remains not accepted, even if its entry is preserved exactly.'];
 }
}
if(array_keys($m88Exceptions)!==['/test/past-continuous/questions']
 ||$m88Exceptions['/test/past-continuous/questions']['status']!==404
 ||$m88Exceptions['/test/past-continuous/questions']['requested_kind']!=='ssr'
 ||$m88Exceptions['/test/past-continuous/questions']['actual_kind']!=='json'
 ||count($before['old_sql']['questions']??[])!==984||($before['source_bank_count']??null)!==15
 ||count($before['old_sql']['saved_tests']??[])!==15){throw new RuntimeException('M88 exact one requested-SSR404/actual-JSON collision or15-bank984/15saved scope differs.');}
$result['strict_http_comparison']=['policy'=>'Exact M52 full-entry inequality, without normalization, field exclusions, variants pinning or reruns.',
 'normalization_applied'=>false,'original_gate_modified'=>false,'collection_continues_after_strict_mismatch'=>true,
 'single_GET_per_endpoint'=>true,'endpoint_count'=>57,'strict_entries_exact'=>57-count($m88StrictDiffs),
 'failed_consumers'=>count($m88StrictDiffs),'strict_entry_comparison_pass'=>$m88StrictDiffs===[],
 'baseline_exception_count'=>1,'baseline_exceptions'=>$m88Exceptions,'strict_mismatches'=>$m88StrictDiffs];
$result['independent_final_receipt']=['file'=>$profileContext['final_file'],'sha256'=>$profileContext['final_sha256'],
 'all46_actual_after_fingerprints_exact'=>true,'all46_normalized_before_exact'=>true,'table_exceptions'=>[]];
$result['independent_full_bank_scope']=['full_banks'=>15,'full_original_questions'=>984,'page_linked_subset'=>918,
 'full_bank_only_questions'=>66,'full_bank_only_question_ids'=>$profileContext['final']['old_consumer_sql']['full_bank_only_question_ids'],
 'saved_pools'=>15,'scope'=>'All984 original full-bank rows are compared with immutable consumer BEFORE. The918 page-linked subset plus exact66 VerbToBePast rows is separately bound to the independent46-table final scanner, not substituted for the full-bank snapshot.'];
M88PHP;
$replacements[]=["$"."result['unchanged_during_http'] = true; $"."result['finished_at'] = gmdate('c');",
 "$"."result['unchanged_during_http'] = true; $"."result['finished_at'] = gmdate('c');\n".$m88AfterSummary,1];
$replacements[]=["'status' => $"."mode === 'capture' ? 'independent-old-consumers-before' : 'old-consumers-preserved',",
 "'status' => $"."m88StrictDiffs === [] ? 'strict-entry-comparison-exact; baseline-exceptions-not-accepted' : 'strict-entry-FAIL-retained; all57-collected',",1];

PHP;
$m88Replace('if($profileOnly){', $m88Extra.'if($profileOnly){');
$m88Replace("'None; capture-only BEFORE profile, all46 tables exact.'", "'No table exceptions; all46 AFTER and normalized BEFORE bound to independent final reconstruction.'");
$m88Replace("'profile'=>'M88 old-consumer BEFORE'", "'profile'=>'M88 old-consumer AFTER; final-bound strict collection'");
$m88Replace("runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',\$replacements);",
    "runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',\$replacements,\$m88AfterContext);");
if (!str_starts_with($m88Code, "<?php\n")) { throw new RuntimeException('M88 immutable collector opening differs.'); }
eval(substr($m88Code, strlen("<?php\n")));
