<?php

/** M76 finite profile of the accepted read-only consumer procedure. */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^(?:anchor-)?final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M76 audit additionally requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file' => $argv[5], 'final_sha256' => $argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaselineLoop = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M76 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M76 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$reviewedBaselineLoop = <<<'PHP'
if ($mode === 'audit') {
    $finalName = $profileContext['final_file'] ?? '';
    $finalSha = $profileContext['final_sha256'] ?? '';
    if (preg_match('/^(?:anchor-)?final-readonly-v[1-9][0-9]*\.json$/D', $finalName) !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $finalSha) !== 1
        || !is_file($dir.'/'.$finalName) || is_link($dir.'/'.$finalName)
        || !hash_equals($finalSha, hash_file('sha256', $dir.'/'.$finalName))) {
        throw new RuntimeException('M76 reviewed independent final receipt is missing, linked or changed.');
    }
    $final = json_decode(file_get_contents($dir.'/'.$finalName), true, flags: JSON_THROW_ON_ERROR);
    if (str_starts_with($finalName, 'anchor-') && (($final['successor_version'] ?? null) !== '1.0.1'
        || ($final['original_final_sha256'] ?? null) !== '214ddb282ee6daad426c84ccc3cb9e9b26d12423a0ace44efe33aa2dab21b053'
        || ($final['revision_sha256'] ?? null) !== '9d30fa87cd71ba53730f7f48d77eec73fb66c245f4149db2a296ee64a8b1bbfb'
        || ($final['predecessor_sql_exact'] ?? null) !== true || ($final['anchor_body_updates'] ?? null) !== 1)) {
        throw new RuntimeException('M76 exact anchor successor lineage differs.');
    }
    if (($final['independent_before_sha256'] ?? null) !== $workingBeforeSha
        || ($final['runtime'] ?? null) !== ($workingBefore['identity'] ?? null)
        || ($final['all_old_rows_and_timestamps_exact'] ?? null) !== true
        || ($final['independent_before_projection_exact'] ?? null) !== true
        || ($final['reviewed_protected_projection_exact'] ?? null) !== true
        || ($final['normalized_before'] ?? null) !== $workingBefore['sql_before']
        || ($final['sql_after'] ?? null) !== $allTablesBeforeHttp
        || ($final['db_writes'] ?? null) !== 0 || ($final['http_requests'] ?? null) !== 0) {
        throw new RuntimeException('M76 every current table must equal reviewed AFTER with exact independent BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table => $expected) {
        if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
            throw new RuntimeException('M76 consumer capture found SQL drift from working BEFORE: '.$table);
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
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesRelativePronounsTheorySeeder'=>['zaimennyky-ta-vkazivni-slova','relative-pronouns-who-which-that-whose'],
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesEachEveryAllTheorySeeder'=>['zaimennyky-ta-vkazivni-slova','each-every-all'],
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesReciprocalPronounsTheorySeeder'=>['zaimennyky-ta-vkazivni-slova','reciprocal-pronouns-each-other-one-another'],
    ];
    $scope=$scopes[$page['page_seeder_class']??'']??null;
    if ($scope===null || ($page['category_slug']??null)!==$scope[0] || ($page['slug']??null)!==$scope[1]
        || ($page['route']??null)!=='/theory/'.$scope[0].'/'.$scope[1]) {
PHP;
$manifestHook = <<<'PHP'
    $bankSets=[
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesRelativePronounsTheorySeeder'=>[
            'Database\\Seeders\\V3\\Polyglot\\PolyglotRelativePronounsAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\PronounsDemonstratives\\RelativePronounsAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesEachEveryAllTheorySeeder'=>[
            'Database\\Seeders\\V3\\Polyglot\\PolyglotEachEveryAllAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\PronounsDemonstratives\\EachEveryAllAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesReciprocalPronounsTheorySeeder'=>[
            'Database\\Seeders\\V3\\Polyglot\\PolyglotReciprocalPronounsAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\PronounsDemonstratives\\ReciprocalPronounsAllLevelsV3Seeder'],
    ];
    $expectedBanks=$bankSets[$page['page_seeder_class']];
    if (array_column($manifest['tests_on_page']??[],'seeder_class')!==$expectedBanks) {
        throw new RuntimeException('M76 exact old manifest bank list/order differs.');
    }
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
PHP;
$classGuard = <<<'PHP'
        if (!in_array($class,$expectedBanks,true)) {
PHP;
$sourceGuard='~^database/seeders/V3/(?:TheoryLinks/data/pronouns-demonstratives-(?:relative-pronouns|each-every-all|reciprocal-pronouns)-theory-links\.json|Polyglot/(?:PolyglotRelativePronounsAllLevelsLessonSeeder|PolyglotEachEveryAllAllLevelsLessonSeeder|PolyglotReciprocalPronounsAllLevelsLessonSeeder)/definition\.json|PronounsDemonstratives/(?:RelativePronounsAllLevelsV3Seeder|EachEveryAllAllLevelsV3Seeder|ReciprocalPronounsAllLevelsV3Seeder)/definition\.json)$~D';
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',[
    ['M52','M76',43],['m52','m76',4],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBaselineLoop,$reviewedBaselineLoop,1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','b0cae6f629aae1b4354aefcf8d704b73030ef2022846c2cd1ea2f9f5d3a05985',1],
    ['passive-voice-formation-rules-theory-links.json','pronouns-demonstratives-relative-pronouns-theory-links.json',1],
    ['passive-voice-negatives-questions-theory-links.json','pronouns-demonstratives-each-every-all-theory-links.json',1],
    ['passive-voice-modal-verbs-theory-links.json','pronouns-demonstratives-reciprocal-pronouns-theory-links.json',1],
    ['~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\.json)$~D',$sourceGuard,1],
    [$oldPageGuard,$pageGuard,1],
    ["    foreach (\$manifest['tests_on_page'] ?? [] as \$test) {",$manifestHook,1],
    ["        if (!preg_match('/^Database\\\\\\\\Seeders\\\\\\\\V3\\\\\\\\(?:Polyglot|PassiveVoice)\\\\\\\\[A-Za-z0-9]+$/D', \$class)) {",$classGuard,1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["    \$http = \$getLocal(\$route); \$entry =","    echo \$json(['checking_consumer'=>\$path]);\n    \$http = \$getLocal(\$route); \$entry =",1],
], $context);
