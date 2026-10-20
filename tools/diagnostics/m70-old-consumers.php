<?php

/** M70 finite profile of the accepted read-only consumer procedure. */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M70 audit additionally requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file' => $argv[5], 'final_sha256' => $argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaselineLoop = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M70 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M70 consumer capture found SQL drift from working BEFORE: '.$table);
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
        throw new RuntimeException('M70 reviewed independent final receipt is missing, linked or changed.');
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
        throw new RuntimeException('M70 every current table must equal reviewed AFTER with exact independent BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table => $expected) {
        if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
            throw new RuntimeException('M70 consumer capture found SQL drift from working BEFORE: '.$table);
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
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesIntensifiersTheorySeeder'=>['prykmetniky-ta-pryslinknyky','intensifiers'],
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder'=>['imennyky-artykli-ta-kilkist','plural-nouns-s-es-ies'],
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityCountableUncountableNounsTheorySeeder'=>['imennyky-artykli-ta-kilkist','countable-vs-uncountable-nouns'],
    ];
    $scope=$scopes[$page['page_seeder_class']??'']??null;
    if ($scope===null || ($page['category_slug']??null)!==$scope[0] || ($page['slug']??null)!==$scope[1]
        || ($page['route']??null)!=='/theory/'.$scope[0].'/'.$scope[1]) {
PHP;
$manifestHook = <<<'PHP'
    $bankSets=[
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesIntensifiersTheorySeeder'=>[
            'Database\\Seeders\\V3\\Polyglot\\PolyglotIntensifiersAllLevelsLessonSeeder','Database\\Seeders\\V3\\IntensifiersAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder'=>[
            'Database\\Seeders\\V3\\Polyglot\\PolyglotPluralNounsSEsIesAllLevelsLessonSeeder','Database\\Seeders\\V3\\NounsArticlesQuantity\\PluralNounsSEsIesAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityCountableUncountableNounsTheorySeeder'=>[
            'Database\\Seeders\\V3\\Polyglot\\PolyglotCountableVsUncountableNounsAllLevelsLessonSeeder','Database\\Seeders\\V3\\NounsArticlesQuantity\\CountableVsUncountableNounsAllLevelsV3Seeder'],
    ];
    $expectedBanks=$bankSets[$page['page_seeder_class']];
    if (count($manifest['tests_on_page']??[])!==2) { throw new RuntimeException('M70 exact old manifest count differs.'); }
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
PHP;
$classGuard = <<<'PHP'
        if (!in_array($class,$expectedBanks,true)) {
PHP;
$sourceGuard='~^database/seeders/V3/(?:TheoryLinks/data/(?:adjectives-intensifiers|nouns-articles-quantity-plural-nouns-s-es-ies|nouns-articles-quantity-countable-vs-uncountable-nouns)-theory-links\.json|Polyglot/(?:PolyglotIntensifiersAllLevelsLessonSeeder|PolyglotPluralNounsSEsIesAllLevelsLessonSeeder|PolyglotCountableVsUncountableNounsAllLevelsLessonSeeder)/definition\.json|IntensifiersAllLevelsV3Seeder/definition\.json|NounsArticlesQuantity/(?:PluralNounsSEsIesAllLevelsV3Seeder|CountableVsUncountableNounsAllLevelsV3Seeder)/definition\.json)$~D';
$savedDefaults = <<<'PHP'
        $definition = $readSource($relative); $saved = $definition['saved_test'] ?? [];
        if (in_array($class,['Database\\Seeders\\V3\\IntensifiersAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotIntensifiersAllLevelsLessonSeeder'],true)) {
            if (array_key_exists('question_uuids',$saved)) { throw new RuntimeException('M70 saved-test source-order default changed.'); }
            $saved['question_uuids']=array_column($definition['questions'],'uuid');
        }
PHP;
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',[
    ['M52','M70',43],['m52','m70',4],
    [$oldBaselineLoop,$reviewedBaselineLoop,1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','5c2a7785156041dc8cc6e4dc26f8776b939ec59226119898fb9f0d1376d2f3b8',1],
    ['passive-voice-formation-rules-theory-links.json','adjectives-intensifiers-theory-links.json',1],
    ['passive-voice-negatives-questions-theory-links.json','nouns-articles-quantity-plural-nouns-s-es-ies-theory-links.json',1],
    ['passive-voice-modal-verbs-theory-links.json','nouns-articles-quantity-countable-vs-uncountable-nouns-theory-links.json',1],
    ['~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\.json)$~D',$sourceGuard,1],
    [$oldPageGuard,$pageGuard,1],
    ["    foreach (\$manifest['tests_on_page'] ?? [] as \$test) {",$manifestHook,1],
    ["\$class = \$test['seeder_class'] ?? '';","\$class = \$test['seeder_class'] ?? ((\$test['kind']??null)==='virtual_mixed_test' ? end(\$expectedBanks) : '');",1],
    ["        if (!preg_match('/^Database\\\\\\\\Seeders\\\\\\\\V3\\\\\\\\(?:Polyglot|PassiveVoice)\\\\\\\\[A-Za-z0-9]+$/D', \$class)) {",$classGuard,1],
    ["        \$definition = \$readSource(\$relative); \$saved = \$definition['saved_test'] ?? [];",$savedDefaults,1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["    \$http = \$getLocal(\$route); \$entry =","    echo \$json(['checking_consumer'=>\$path]);\n    \$http = \$getLocal(\$route); \$entry =",1],
], $context);
