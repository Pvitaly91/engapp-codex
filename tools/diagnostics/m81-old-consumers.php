<?php

/** Exact six-owner M81 profile of the accepted SELECT-only old-consumer procedure. */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M81 audit requires a reviewed independent final receipt and raw SHA-256.');
    }
    $context = ['final_file'=>$argv[5], 'final_sha256'=>$argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaseline = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M81 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M81 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$baseline = <<<'PHP'
if ($mode === 'audit') {
    $finalName=$profileContext['final_file']??''; $finalSha=$profileContext['final_sha256']??'';
    if (preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$finalName)!==1
        || preg_match('/^[a-f0-9]{64}$/D',$finalSha)!==1 || is_link($dir.'/'.$finalName)
        || !is_file($dir.'/'.$finalName) || !hash_equals($finalSha,hash_file('sha256',$dir.'/'.$finalName))) {
        throw new RuntimeException('M81 independent final receipt is missing, linked or changed.');
    }
    $final=json_decode(file_get_contents($dir.'/'.$finalName),true,flags:JSON_THROW_ON_ERROR);
    if (($final['independent_before_sha256']??null)!==$workingBeforeSha
        || ($final['runtime']??null)!==($workingBefore['identity']??null)
        || ($final['all_old_rows_and_timestamps_exact']??null)!==true
        || ($final['independent_before_projection_exact']??null)!==true
        || ($final['reviewed_protected_projection_exact']??null)!==true
        || ($final['normalized_before']??null)!==$workingBefore['sql_before']
        || ($final['sql_after']??null)!==$allTablesBeforeHttp
        || ($final['db_writes']??null)!==0 || ($final['http_requests']??null)!==0) {
        throw new RuntimeException('M81 current tables must equal the independently reviewed AFTER with exact BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table=>$expected) {
        if (($allTablesBeforeHttp[$table]??null)!==$expected) { throw new RuntimeException('M81 consumer BEFORE SQL drift: '.$table); }
    }
}
PHP;
$oldManifests = <<<'PHP'
$manifests = [
    'database/seeders/V3/TheoryLinks/data/passive-voice-formation-rules-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/passive-voice-negatives-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/passive-voice-modal-verbs-theory-links.json',
];
PHP;
$manifests = <<<'PHP'
$manifests = [
    'database/seeders/V3/TheoryLinks/data/sentence-transformations-conditionals-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/sentence-transformations-relative-clauses-linking-words-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/sentence-transformations-articles-quantifiers-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/sentence-transformations-word-order-emphasis-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/common-mistakes-articles-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/common-mistakes-word-order-theory-links.json',
];
PHP;
$oldGuard = <<<'PHP'
    if (($page['category_slug'] ?? null) !== 'passive-voice'
        || !in_array($page['page_seeder_class'] ?? null, [
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceNegativesQuestionsTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceModalVerbsTheorySeeder',
        ], true)
        || ($page['route'] ?? null) !== '/theory/passive-voice/'.($page['slug'] ?? '')
        || !str_starts_with($page['slug'] ?? '', 'theory-passive-voice-')) {
PHP;
$guard = <<<'PHP'
    $scopes = [
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsConditionalsTheorySeeder'=>['sentence-transformations','conditionals','SentenceTransformationsConditionals','SentenceTransformations'],
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsRelativeClausesLinkingWordsTheorySeeder'=>['sentence-transformations','relative-clauses-linking-words','SentenceTransformationsRelativeClausesLinkingWords','SentenceTransformations'],
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsArticlesQuantifiersTheorySeeder'=>['sentence-transformations','articles-quantifiers','SentenceTransformationsArticlesQuantifiers','SentenceTransformations'],
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsWordOrderEmphasisTheorySeeder'=>['sentence-transformations','word-order-emphasis','SentenceTransformationsWordOrderEmphasis','SentenceTransformations'],
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesArticlesTheorySeeder'=>['common-mistakes','articles-common-mistakes','CommonMistakesArticles','CommonMistakes'],
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesWordOrderTheorySeeder'=>['common-mistakes','word-order-common-mistakes','CommonMistakesWordOrder','CommonMistakes'],
    ];
    $scope=$scopes[$page['page_seeder_class']??'']??null;
    if ($scope===null || ($page['category_slug']??null)!==$scope[0] || ($page['slug']??null)!==$scope[1]
        || ($page['route']??null)!=='/theory/'.$scope[0].'/'.$scope[1]) {
PHP;
$manifestHook = <<<'PHP'
    $primary='Database\\Seeders\\V3\\Polyglot\\Polyglot'.$scope[2].'AllLevelsLessonSeeder';
    $standard='Database\\Seeders\\V3\\'.$scope[3].'\\'.$scope[2].'AllLevelsV3Seeder';
    $legacy=$scope[2]==='CommonMistakesArticles'?['Database\\Seeders\\V3\\Polyglot\\PolyglotArticlesAAnTheLessonSeeder']:[];
    $expectedBanks=[$primary,$standard,...$legacy];
    $tests=$manifest['tests_on_page']??[];$mixed=$tests[count($tests)-1]??[];
    $kinds=$legacy===[]?['direct_sentence_builder','mixed_a1_c2']:['direct_sentence_builder','legacy_direct_sentence_builder','mixed_a1_c2'];
    if (array_column($tests,'kind')!==$kinds || ($tests[0]['seeder_class']??null)!==$primary
        || ($mixed['expected_seeder_classes']??null)!==[$standard,$primary] || isset($mixed['seeder_class'])
        || ($manifest['expected_standard_seeders']??null)!==[$standard]
        || ($manifest['expected_polyglot_seeders']??null)!==[$primary,...$legacy]
        || ($legacy!==[]&&($tests[1]['seeder_class']??null)!==$legacy[0])) {
        throw new RuntimeException('M81 exact direct/legacy/mixed original-bank declaration differs.');
    }
    // Existing legacy Articles bank is a third read-only consumer, not widget scope.
    foreach (array_map(static fn($bank)=>['seeder_class'=>$bank],$expectedBanks) as $test) {
PHP;
$sourceGuard='~^database/seeders/V3/(?:TheoryLinks/data/(?:sentence-transformations-(?:conditionals|relative-clauses-linking-words|articles-quantifiers|word-order-emphasis)|common-mistakes-(?:articles|word-order))-theory-links\\.json|Polyglot/(?:PolyglotSentenceTransformations(?:Conditionals|RelativeClausesLinkingWords|ArticlesQuantifiers|WordOrderEmphasis)AllLevelsLessonSeeder|PolyglotCommonMistakes(?:Articles|WordOrder)AllLevelsLessonSeeder|PolyglotArticlesAAnTheLessonSeeder)/definition\\.json|SentenceTransformations/SentenceTransformations(?:Conditionals|RelativeClausesLinkingWords|ArticlesQuantifiers|WordOrderEmphasis)AllLevelsV3Seeder/definition\\.json|CommonMistakes/CommonMistakes(?:Articles|WordOrder)AllLevelsV3Seeder/definition\\.json)$~D';
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',[
    ['M52','M81',43],['m52','m81',4],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBaseline,$baseline,1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','facabc5369230dac0958f2e2277c7cd0e4b0523fb794c041afd46116e095c023',1],
    [$oldManifests,$manifests,1],
    ['~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\.json)$~D',$sourceGuard,1],
    [$oldGuard,$guard,1],
    ["    foreach (\$manifest['tests_on_page'] ?? [] as \$test) {",$manifestHook,1],
    ["        if (!preg_match('/^Database\\\\\\\\Seeders\\\\\\\\V3\\\\\\\\(?:Polyglot|PassiveVoice)\\\\\\\\[A-Za-z0-9]+$/D', \$class)) {","        if (!in_array(\$class,\$expectedBanks,true)) {",1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["    \$http = \$getLocal(\$route); \$entry =","    echo \$json(['checking_consumer'=>\$path]);\n    \$http = \$getLocal(\$route); \$entry =",1],
],$context);
