<?php

/** M74 1.0.1 target/full-registry successor. Prior diagnostics and shared read-only primitive remain immutable. */
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows'
    || strtolower(str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2)))) !== strtolower($root)) {
    throw new RuntimeException('M74 bank successor requires actual served local Windows ROOT.');
}
$finalPath = $root.'/storage/app/seo-m74-local/bank-final-readonly-v1.json';
if (!is_file($finalPath) || is_link($finalPath)
    || !hash_equals('9946a9146bb1e3a90ab1c7d2881a788929319b400aa42d5b004de0363f1029de', hash_file('sha256', $finalPath))) {
    throw new RuntimeException('M74 bank successor requires exact reviewed all-46-table final receipt.');
}
$final = json_decode(file_get_contents($finalPath), true, flags: JSON_THROW_ON_ERROR);
if (($final['kind'] ?? null) !== 'M74_bank_completion_final' || ($final['all_old_rows_and_timestamps_exact'] ?? null) !== true
    || ($final['exact_one_bank_list_body_update'] ?? null) !== true || count($final['normalized_before'] ?? []) !== 46) {
    throw new RuntimeException('M74 bank successor final evidence is incomplete.');
}
$file = __DIR__.'/m74-sentence-builder-audit.php';
if (!is_file($file) || is_link($file)) { throw new RuntimeException('Immutable M74 predecessor helper is missing or linked.'); }
$source = file_get_contents($file);
if (!is_string($source) || !str_starts_with($source, "<?php\n")
    || !hash_equals('c0e564d0c0a1371e563cbc7dd3a5a2fc392e22d686ea5604e4567a7bf6d1b36c', hash('sha256', $source))) {
    throw new RuntimeException('Immutable M74 predecessor helper bytes differ.');
}
$replacements = [
    [<<<'M74_OLD_0'
$ids = [[32707,32708,32711,32712,32714],[33123,33126,33128,33131,33133],[51184,51185,51187,51188,51189]][$index];
M74_OLD_0, <<<'M74_NEW_0'
$ids = [[32707,32708,32711,32714,59720],[33123,33126,33128,33131,33133],[51184,51185,51187,51188,51189]][$index];
    $banks = [
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder','Database\\Seeders\\V3\\Polyglot\\M74AnotherRemainderSupplementSeeder'],
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotPronounsTheoryAllLevelsLessonSeeder'],
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotPersonalObjectPronounsAllLevelsLessonSeeder'],
    ][$index];
    if(($target['bank_seeders']??null)!==$banks||array_keys($target['expected_pool_ids_by_bank']??[])!==$banks
        ||array_map('count',array_values($target['expected_pool_ids_by_bank']))!==[[12,1],[72],[72]][$index]
        ||($index===0&&$target['expected_pool_ids_by_bank'][$banks[1]]!==[59720])
        ||($current['eligible_count']??null)!==[13,72,72][$index]){
        throw new RuntimeException('M74 bank-only successor must retain all old pools and exactly one isolated B1 remainder question.');
    }
M74_NEW_0, 1],
    [<<<'M74_OLD_1'
$data['m74_exact_selected_ids_checked'] = 15;
M74_OLD_1, <<<'M74_NEW_1'
$data['m74_exact_selected_ids_checked'] = 15;
$data['m74_bank_revision'] = '1.0.1';
$data['m74_bank_final_readonly_sha256'] = '9946a9146bb1e3a90ab1c7d2881a788929319b400aa42d5b004de0363f1029de';
$data['m74_eligible_counts'] = [13,72,72];
M74_NEW_1, 1],
    [<<<'M74_OLD_2'
    ["$"."data = json_decode($"."output, true, flags: JSON_THROW_ON_ERROR);", $registryCheck, 1],
]);
M74_OLD_2, <<<'M74_NEW_2'
    ["$"."data = json_decode($"."output, true, flags: JSON_THROW_ON_ERROR);", $registryCheck, 1],
    [".'-sentence-builders-v1.json';", ".'-bank-sentence-builders-v1.json';", 1],
]);
M74_NEW_2, 1],
];
foreach ($replacements as [$old, $new, $count]) {
    if (substr_count($source, $old) !== $count) { throw new RuntimeException('M74 bank finite literal profile count differs.'); }
    $source = str_replace($old, $new, $source);
}
// Only this fixed hash-bound repository helper and exact finite literals execute; no runtime code interpolation.
eval(substr($source, strlen("<?php\n")));
