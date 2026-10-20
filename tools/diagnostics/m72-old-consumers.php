<?php

/** M72 finite profile of the accepted read-only consumer procedure. */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M72 audit additionally requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file' => $argv[5], 'final_sha256' => $argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaselineLoop = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M72 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M72 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$reviewedBaselineLoop = <<<'PHP'
if ($mode === 'audit') {
    $finalName = $profileContext['final_file'] ?? '';
    $finalSha = $profileContext['final_sha256'] ?? '';
    if (preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $finalName) !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $finalSha) !== 1
        || !is_file($dir.'/'.$finalName) || is_link($dir.'/'.$finalName)
        || !hash_equals($finalSha, hash_file('sha256', $dir.'/'.$finalName))) {
        throw new RuntimeException('M72 reviewed independent final receipt is missing, linked or changed.');
    }
    $final = json_decode(file_get_contents($dir.'/'.$finalName), true, flags: JSON_THROW_ON_ERROR);
    if (($final['independent_before_sha256'] ?? null) !== $workingBeforeSha
        || ($final['runtime'] ?? null) !== ($workingBefore['identity'] ?? null)
        || ($final['all_old_rows_and_timestamps_exact'] ?? null) !== true
        || ($final['independent_before_projection_exact'] ?? null) !== true
        || ($final['reviewed_protected_projection_exact'] ?? null) !== true
        || ($final['normalized_before'] ?? null) !== $workingBefore['sql_before']
        || ($final['sql_after'] ?? null) !== $allTablesBeforeHttp
        || ($final['db_writes'] ?? null) !== 0 || ($final['http_requests'] ?? null) !== 0) {
        throw new RuntimeException('M72 every current table must equal reviewed AFTER with exact independent BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table => $expected) {
        if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
            throw new RuntimeException('M72 consumer capture found SQL drift from working BEFORE: '.$table);
        }
    }
}
PHP;
$oldPageGuard = <<<'PHP'
    if (($page['category_slug'] ?? null) !== 'passive-voice'
        || !in_array($page['page_seeder_class'] ?? null, [
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceNegativesQuestionsTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceModalVerbsTheorySeeder',
        ], true)
        || ($page['route'] ?? null) !== '/theory/passive-voice/'.($page['slug'] ?? '')
        || !str_starts_with($page['slug'] ?? '', 'theory-passive-voice-')) {
PHP;
$pageGuard = <<<'PHP'
    $scopes = [
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPartitivesTheorySeeder'=>['imennyky-artykli-ta-kilkist','partitives-with-uncountable-nouns-a-piece-of-a-cup-of'],
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder'=>['imennyky-artykli-ta-kilkist','no-none-neither-either'],
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder'=>['imennyky-artykli-ta-kilkist','articles-with-geographical-names'],
    ];
    $scope=$scopes[$page['page_seeder_class']??'']??null;
    if ($scope===null || ($page['category_slug']??null)!==$scope[0] || ($page['slug']??null)!==$scope[1]
        || ($page['route']??null)!=='/theory/'.$scope[0].'/'.$scope[1]) {
PHP;
$manifestHook = <<<'PHP'
    $bankSets=[
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPartitivesTheorySeeder'=>[
            'Database\\Seeders\\V3\\Polyglot\\PolyglotPartitivesWithUncountableNounsAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\NounsArticlesQuantity\\PartitivesWithUncountableNounsAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder'=>[
            'Database\\Seeders\\V3\\Polyglot\\PolyglotNoNoneNeitherEitherAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\NounsArticlesQuantity\\NoNoneNeitherEitherAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder'=>[
            'Database\\Seeders\\V3\\Polyglot\\PolyglotArticlesWithGeographicalNamesAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\NounsArticlesQuantity\\ArticlesWithGeographicalNamesAllLevelsV3Seeder'],
    ];
    $expectedBanks=$bankSets[$page['page_seeder_class']];
    if (array_column($manifest['tests_on_page']??[],'seeder_class')!==$expectedBanks) {
        throw new RuntimeException('M72 exact old manifest bank list/order differs.');
    }
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
PHP;
$classGuard = <<<'PHP'
        if (!in_array($class,$expectedBanks,true)) {
PHP;
$sourceGuard='~^database/seeders/V3/(?:TheoryLinks/data/nouns-articles-quantity-(?:partitives-with-uncountable-nouns|no-none-neither-either|articles-with-geographical-names)-theory-links\.json|Polyglot/(?:PolyglotPartitivesWithUncountableNounsAllLevelsLessonSeeder|PolyglotNoNoneNeitherEitherAllLevelsLessonSeeder|PolyglotArticlesWithGeographicalNamesAllLevelsLessonSeeder|PolyglotMuchManyALotOfLessonSeeder)/definition\.json|NounsArticlesQuantity/(?:PartitivesWithUncountableNounsAllLevelsV3Seeder|NoNoneNeitherEitherAllLevelsV3Seeder|ArticlesWithGeographicalNamesAllLevelsV3Seeder)/definition\.json)$~D';
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',[
    ['M52','M72',43],['m52','m72',4],
    [$oldBaselineLoop,$reviewedBaselineLoop,1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','33cd475c775288da255c3584aa3b9ef665255a1f31abc14f4df294ef749a690d',1],
    ['passive-voice-formation-rules-theory-links.json','nouns-articles-quantity-partitives-with-uncountable-nouns-theory-links.json',1],
    ['passive-voice-negatives-questions-theory-links.json','nouns-articles-quantity-no-none-neither-either-theory-links.json',1],
    ['passive-voice-modal-verbs-theory-links.json','nouns-articles-quantity-articles-with-geographical-names-theory-links.json',1],
    ['~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\.json)$~D',$sourceGuard,1],
    [$oldPageGuard,$pageGuard,1],
    ["    foreach (\$manifest['tests_on_page'] ?? [] as \$test) {",$manifestHook,1],
    ["        if (!preg_match('/^Database\\\\\\\\Seeders\\\\\\\\V3\\\\\\\\(?:Polyglot|PassiveVoice)\\\\\\\\[A-Za-z0-9]+$/D', \$class)) {",$classGuard,1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["    \$http = \$getLocal(\$route); \$entry =","    echo \$json(['checking_consumer'=>\$path]);\n    \$http = \$getLocal(\$route); \$entry =",1],
], $context);
