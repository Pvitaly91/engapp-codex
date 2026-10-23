<?php

/** Exact six-owner M80 profile of the accepted SELECT-only old-consumer procedure. */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M80 audit requires a reviewed independent final receipt and raw SHA-256.');
    }
    $context = ['final_file'=>$argv[5], 'final_sha256'=>$argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaseline = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M80 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M80 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$baseline = <<<'PHP'
if ($mode === 'audit') {
    $finalName=$profileContext['final_file']??''; $finalSha=$profileContext['final_sha256']??'';
    if (preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$finalName)!==1
        || preg_match('/^[a-f0-9]{64}$/D',$finalSha)!==1 || is_link($dir.'/'.$finalName)
        || !is_file($dir.'/'.$finalName) || !hash_equals($finalSha,hash_file('sha256',$dir.'/'.$finalName))) {
        throw new RuntimeException('M80 independent final receipt is missing, linked or changed.');
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
        throw new RuntimeException('M80 current tables must equal the independently reviewed AFTER with exact BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table=>$expected) {
        if (($allTablesBeforeHttp[$table]??null)!==$expected) { throw new RuntimeException('M80 consumer BEFORE SQL drift: '.$table); }
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
    'database/seeders/V3/TheoryLinks/data/questions-negations-negative-forms-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/questions-negations-double-negatives-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/sentence-transformations-tenses-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/sentence-transformations-modals-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/sentence-transformations-passive-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/sentence-transformations-reported-speech-theory-links.json',
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
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsNegativeFormsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia','negative-forms','NegativeForms','QuestionsNegations'],
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsDoubleNegativesTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia','double-negatives','DoubleNegatives','QuestionsNegations'],
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsTensesTheorySeeder'=>['sentence-transformations','tenses','SentenceTransformationsTenses','SentenceTransformations'],
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsModalsTheorySeeder'=>['sentence-transformations','modals','SentenceTransformationsModals','SentenceTransformations'],
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsPassiveTheorySeeder'=>['sentence-transformations','passive','SentenceTransformationsPassive','SentenceTransformations'],
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsReportedSpeechTheorySeeder'=>['sentence-transformations','reported-speech','SentenceTransformationsReportedSpeech','SentenceTransformations'],
    ];
    $scope=$scopes[$page['page_seeder_class']??'']??null;
    if ($scope===null || ($page['category_slug']??null)!==$scope[0] || ($page['slug']??null)!==$scope[1]
        || ($page['route']??null)!=='/theory/'.$scope[0].'/'.$scope[1]) {
PHP;
$manifestHook = <<<'PHP'
    $expectedBanks=[
        'Database\\Seeders\\V3\\Polyglot\\Polyglot'.$scope[2].'AllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\'.$scope[3].'\\'.$scope[2].'AllLevelsV3Seeder',
    ];
    $tests=$manifest['tests_on_page']??[]; $standard=$scope[3]==='QuestionsNegations';
    if (array_column($tests,'kind')!==['direct_sentence_builder',$standard?'standard_v3':'mixed_a1_c2']
        || ($tests[0]['seeder_class']??null)!==$expectedBanks[0]
        || ($tests[1]['expected_seeder_classes']??null)!==($standard?[$expectedBanks[1]]:[$expectedBanks[1],$expectedBanks[0]])
        || isset($tests[1]['seeder_class'])
        || ($manifest['expected_standard_seeders']??null)!==[$expectedBanks[1]]
        || ($manifest['expected_polyglot_seeders']??null)!==[$expectedBanks[0]]) {
        throw new RuntimeException('M80 exact original direct/standard/mixed bank declaration differs.');
    }
    // Capture both exact persisted source banks. The native page mixed slug is
    // captured separately below; its two-bank declaration is not discarded.
    foreach (array_map(static fn($bank)=>['seeder_class'=>$bank],$expectedBanks) as $test) {
PHP;
$sourceGuard='~^database/seeders/V3/(?:TheoryLinks/data/(?:questions-negations-(?:negative-forms|double-negatives)|sentence-transformations-(?:tenses|modals|passive|reported-speech))-theory-links\\.json|Polyglot/Polyglot(?:NegativeForms|DoubleNegatives|SentenceTransformations(?:Tenses|Modals|Passive|ReportedSpeech))AllLevelsLessonSeeder/definition\\.json|QuestionsNegations/(?:NegativeForms|DoubleNegatives)AllLevelsV3Seeder/definition\\.json|SentenceTransformations/SentenceTransformations(?:Tenses|Modals|Passive|ReportedSpeech)AllLevelsV3Seeder/definition\\.json)$~D';
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',[
    ['M52','M80',43],['m52','m80',4],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBaseline,$baseline,1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','75a9e58003f9364c45e4e3c2696aab7c0b8e5d5073a2bb2acefe54fb198dbefb',1],
    [$oldManifests,$manifests,1],
    ['~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\.json)$~D',$sourceGuard,1],
    [$oldGuard,$guard,1],
    ["    foreach (\$manifest['tests_on_page'] ?? [] as \$test) {",$manifestHook,1],
    ["        if (!preg_match('/^Database\\\\\\\\Seeders\\\\\\\\V3\\\\\\\\(?:Polyglot|PassiveVoice)\\\\\\\\[A-Za-z0-9]+$/D', \$class)) {","        if (!in_array(\$class,\$expectedBanks,true)) {",1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["    \$http = \$getLocal(\$route); \$entry =","    echo \$json(['checking_consumer'=>\$path]);\n    \$http = \$getLocal(\$route); \$entry =",1],
],$context);
