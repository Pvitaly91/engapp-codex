<?php

/** M74 1.0.1 three-page HTTP/SSR successor. Prior diagnostics and shared read-only primitive remain immutable. */
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
$file = __DIR__.'/m74-live-pages.php';
if (!is_file($file) || is_link($file)) { throw new RuntimeException('Immutable M74 predecessor helper is missing or linked.'); }
$source = file_get_contents($file);
if (!is_string($source) || !str_starts_with($source, "<?php\n")
    || !hash_equals('4414722720c306d750d70cc37ac453360bf21c594e246e3aa60b90f1c397fb93', hash('sha256', $source))) {
    throw new RuntimeException('Immutable M74 predecessor helper bytes differ.');
}
$replacements = [
    [<<<'M74_OLD_0'
preg_match('/^live-pages-v[1-9][0-9]*\.json$/D'
M74_OLD_0, <<<'M74_NEW_0'
preg_match('/^bank-live-pages-v[1-9][0-9]*\.json$/D'
M74_NEW_0, 1],
    [<<<'M74_OLD_1'
foreach($audit['targets'] as $i=>$bank){
    $pool=$bank['expected_pool_ids_by_bank'][$bank['bank_seeders'][0]]??[];
    if(count($bank['bank_seeders'])!==1||count($pool)!==[12,72,72][$i]||count(array_unique($pool))!==[12,72,72][$i]
        ||count($bank['expected_selected_ids'])!==5||count($bank['first_five'])!==5
        ||($contract['targets'][$i]['level']??null)!==['B1',null,null][$i]){
        throw new RuntimeException('Exact M74 existing thematic bank pool or five-question selection differs.');
    }
    if(($bank['intended_selected_level']??null)!==['B1','A2','A1'][$i]
        ||($bank['level']??null)!==['B1',null,null][$i]){
        throw new RuntimeException('M74 bank display level and technical filter were conflated.');
    }
    $expectedBanks=['Database\\Seeders\\V3\\Polyglot\\PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPronounsTheoryAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPersonalObjectPronounsAllLevelsLessonSeeder'];
    $expectedIds=[[32707,32708,32711,32712,32714],[33123,33126,33128,33131,33133],[51184,51185,51187,51188,51189]];
    if($bank['bank_seeders']!==[$expectedBanks[$i]]||$bank['expected_selected_ids']!==$expectedIds[$i]
        ||($contract['targets'][$i]['bank_seeders']??null)!==[$expectedBanks[$i]]){
        throw new RuntimeException('M74 exact declared thematic bank or reviewed selection differs.');
    }
}
M74_OLD_1, <<<'M74_NEW_1'
foreach($audit['targets'] as $i=>$bank){
    $pool=$bank['expected_pool_ids_by_bank'][$bank['bank_seeders'][0]]??[];
    if(count($bank['bank_seeders'])!==[2,1,1][$i]||count($pool)!==[12,72,72][$i]||count(array_unique($pool))!==[12,72,72][$i]
        ||count($bank['expected_selected_ids'])!==5||count($bank['first_five'])!==5
        ||($contract['targets'][$i]['level']??null)!==['B1',null,null][$i]){
        throw new RuntimeException('Exact M74 existing thematic bank pool or five-question selection differs.');
    }
    if(($bank['intended_selected_level']??null)!==['B1','A2','A1'][$i]
        ||($bank['level']??null)!==['B1',null,null][$i]){
        throw new RuntimeException('M74 bank display level and technical filter were conflated.');
    }
    $expectedBanks=['Database\\Seeders\\V3\\Polyglot\\PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPronounsTheoryAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPersonalObjectPronounsAllLevelsLessonSeeder'];
    $expectedIds=[[32707,32708,32711,32714,59720],[33123,33126,33128,33131,33133],[51184,51185,51187,51188,51189]];
    $supplementBank='Database\\Seeders\\V3\\Polyglot\\M74AnotherRemainderSupplementSeeder';
    $declaredBanks=$i===0?[$expectedBanks[$i],$supplementBank]:[$expectedBanks[$i]];
    if(array_keys($bank['expected_pool_ids_by_bank'])!==$declaredBanks
        ||($i===0&&($bank['expected_pool_ids_by_bank'][$supplementBank]??null)!==[59720])){
        throw new RuntimeException('M74 bank-only successor requires exactly the original pool plus one isolated reviewed remainder question.');
    }
    if($bank['bank_seeders']!==$declaredBanks||$bank['expected_selected_ids']!==$expectedIds[$i]
        ||($contract['targets'][$i]['bank_seeders']??null)!==$declaredBanks){
        throw new RuntimeException('M74 exact declared thematic bank or reviewed selection differs.');
    }
}
M74_NEW_1, 1],
    [<<<'M74_OLD_2'
__FILE__
M74_OLD_2, <<<'M74_NEW_2'
(__DIR__.'/m74-live-pages.php')
M74_NEW_2, 3],
    [<<<'M74_OLD_3'
$inputs=[(__DIR__.'/m74-live-pages.php')=>$fileHash((__DIR__.'/m74-live-pages.php')),$beforePath=>Patch::BEFORE_SHA,$auditPath=>Presentation::AUDIT_SHA];
M74_OLD_3, <<<'M74_NEW_3'
$inputs=[(__DIR__.'/m74-live-pages.php')=>$fileHash((__DIR__.'/m74-live-pages.php')),$beforePath=>Patch::BEFORE_SHA,$auditPath=>Presentation::AUDIT_SHA,
    __DIR__.'/m74-bank-live-pages.php'=>$fileHash(__DIR__.'/m74-bank-live-pages.php'),
    $private.'/bank-final-readonly-v1.json'=>'9946a9146bb1e3a90ab1c7d2881a788929319b400aa42d5b004de0363f1029de'];
M74_NEW_3, 1],
    [<<<'M74_OLD_4'
$same(count($linkedIds),15,'Three five-question widgets');
M74_OLD_4, <<<'M74_NEW_4'
$same($fieldCount,706,'Unchanged complete 1.0.1 SSR learning-field coverage');
$same(count($linkedIds),15,'Three five-question widgets');
M74_NEW_4, 1],
    [<<<'M74_OLD_5'
'patch_sha256'=>Package::SOURCE_SHA,'bank_audit_sha256'=>Presentation::AUDIT_SHA,
M74_OLD_5, <<<'M74_NEW_5'
'patch_sha256'=>Package::SOURCE_SHA,'bank_audit_sha256'=>Presentation::AUDIT_SHA,
    'bank_revision'=>'1.0.1','bank_revision_wrapper_sha256'=>$fileHash(__DIR__.'/m74-bank-live-pages.php'),
    'bank_final_readonly_sha256'=>'9946a9146bb1e3a90ab1c7d2881a788929319b400aa42d5b004de0363f1029de',
M74_NEW_5, 1],
];
foreach ($replacements as [$old, $new, $count]) {
    if (substr_count($source, $old) !== $count) { throw new RuntimeException('M74 bank finite literal profile count differs.'); }
    $source = str_replace($old, $new, $source);
}
// Only this fixed hash-bound repository helper and exact finite literals execute; no runtime code interpolation.
eval(substr($source, strlen("<?php\n")));
