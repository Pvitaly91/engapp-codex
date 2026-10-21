<?php

/**
 * M74 bank-only successor profile; immutable M52 consumer and exact per-entry comparisons retained.
 * Capture is bound to fresh independent BEFORE 9e7abb...; no capture runs on include-free lint.
 * Six declared saved tests plus both relation-derived legacy banks are preserved.
 * No content, question, progress, environment, route or visual source writes.
 */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') !== 'audit') { throw new RuntimeException('Bank successor supports only exact AFTER audit against immutable BEFORE.'); }
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^bank-final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M74 audit additionally requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file' => $argv[5], 'final_sha256' => $argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaselineLoop = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M74 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M74 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$reviewedBaselineLoop = <<<'PHP'
if ($mode !== 'audit') { throw new RuntimeException('M74 bank preservation is AFTER-only.'); }
$finalName = $profileContext['final_file'] ?? '';
$finalSha = $profileContext['final_sha256'] ?? '';
$pinned = static function (string $name, string $sha) use ($dir): array {
    if (preg_match('/^[a-zA-Z0-9_-]+\\.json$/D', $name) !== 1 || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1
        || !is_file($dir.'/'.$name) || is_link($dir.'/'.$name) || !hash_equals($sha, hash_file('sha256', $dir.'/'.$name))) {
        throw new RuntimeException('M74 bank preservation evidence is missing, linked or changed.');
    }
    return json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
};
$final = $pinned($finalName, $finalSha);
$inventorySha = '0647df860679b640b0cb6bbe8dd8fe6752cf3218faf81f9644a263b72062dd80';
$inventory = $pinned('bank-completion-inventory-v1.json', $inventorySha);
$coreSha = '754bceb43a9b72c31a0efc540cdd6639716e30916a43aa6682f24a70043c5bc9';
$core = $pinned('final-readonly-v2.json', $coreSha);
$counts = ['questions' => 1, 'question_options' => 0, 'question_answers' => 4,
    'question_option_question' => 8, 'question_theory_text_blocks' => 1];
if (($final['kind'] ?? null) !== 'M74_bank_completion_final'
    || ($final['inventory_sha256'] ?? null) !== $inventorySha
    || ($final['question_plan_sha256'] ?? null) !== '392401b69a9f09b925fd8af38a394214567fd7a81ad269cf3aaec733b83430c8'
    || ($final['core_post_browser_final_sha256'] ?? null) !== $coreSha
    || ($final['runtime'] ?? null) !== ($workingBefore['identity'] ?? null) || $final['runtime'] !== $core['runtime']
    || ($final['all_old_rows_and_timestamps_exact'] ?? null) !== true
    || ($final['exact_one_bank_list_body_update'] ?? null) !== true
    || ($core['independent_before_sha256'] ?? null) !== $workingBeforeSha
    || ($core['independent_before_projection_exact'] ?? null) !== true
    || ($core['all_old_rows_and_timestamps_exact'] ?? null) !== true
    || ($core['normalized_before'] ?? null) !== $workingBefore['sql_before']
    || ($inventory['sql_before'] ?? null) !== $core['sql_after']
    || ($final['sql_before'] ?? null) !== $inventory['sql_before']
    || ($final['normalized_before'] ?? null) !== $inventory['sql_before']
    || ($final['sql_after'] ?? null) !== $allTablesBeforeHttp || count($allTablesBeforeHttp) !== 46
    || ($final['new_question_rows'] ?? null) !== $inventory['materialized']
    || ($final['question_family_counts'] ?? null) !== $counts
    || array_map('count', $final['new_question_rows'] ?? []) !== $counts
    || ($final['practice_id'] ?? null) !== 12760
    || ($final['practice_uuid'] ?? null) !== '19b7df8b-7c55-5725-a78f-f5935f988baa'
    || ($final['before_body_sha256'] ?? null) !== 'aea17bc05f0b8c77598e75e2d70b8054f12a0e23602ee777286e57bba96bec6e'
    || ($final['after_body_sha256'] ?? null) !== '659bd7d475c905f8c10970526913a64f04026f7f2d470d2de0c51d517be5af68'
    || ($final['db_writes'] ?? null) !== 0 || ($final['production_requests'] ?? null) !== 0
    || ($inventory['db_writes'] ?? null) !== 0 || ($inventory['http_requests'] ?? null) !== 0) {
    throw new RuntimeException('M74 bank final must bind exact fourteen rows/one bank list, every current table and the complete independent core BEFORE reconstruction.');
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
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAnotherOtherTheOtherTheorySeeder' => ['imennyky-artykli-ta-kilkist', 'another-other-the-other'],
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPronounsTheorySeeder' => ['zaimennyky-ta-vkazivni-slova', 'pronouns-theory'],
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPersonalObjectPronounsTheorySeeder' => ['zaimennyky-ta-vkazivni-slova', 'personal-object-pronouns'],
    ];
    $scope = $scopes[$page['page_seeder_class'] ?? ''] ?? null;
    if ($scope === null || ($page['category_slug'] ?? null) !== $scope[0] || ($page['slug'] ?? null) !== $scope[1]
        || ($page['route'] ?? null) !== '/theory/'.$scope[0].'/'.$scope[1]) {
PHP;
$manifestHook = <<<'PHP'
    $bankSets = [
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAnotherOtherTheOtherTheorySeeder' => [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\NounsArticlesQuantity\\AnotherOtherTheOtherAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPronounsTheorySeeder' => [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotPronounsTheoryAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\PronounsDemonstratives\\PronounsTheoryAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPersonalObjectPronounsTheorySeeder' => [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotPersonalObjectPronounsAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\PronounsDemonstratives\\PersonalObjectPronounsAllLevelsV3Seeder'],
    ];
    $expectedBanks = $bankSets[$page['page_seeder_class']];
    if (array_column($manifest['tests_on_page'] ?? [], 'seeder_class') !== $expectedBanks) {
        throw new RuntimeException('M74 exact old manifest bank list/order differs.');
    }
    if ($page['slug'] === 'pronouns-theory'
        && ($manifest['tests_on_page'][1]['expected_seeder_classes'] ?? null) !== [
            'Database\\Seeders\\V3\\PronounsDemonstratives\\PronounsTheoryAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\PronounsDemonstrativesAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\V2\\IndefinitePronounsPracticeV3Seeder',
        ]) {
        throw new RuntimeException('M74 overview legacy manifest bank declarations differ.');
    }
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
PHP;
$classGuard = <<<'PHP'
        if (!in_array($class, $expectedBanks, true)) {
PHP;
$legacySourceHook = <<<'PHP'
// Two legacy banks are independently observed in the overview page-wide BEFORE.
// They lack the complete modern saved_test.question_uuids contract, so do not
// invent saved-test declarations. Preserve their complete SQL families, source
// hashes and the actual page-wide SSR/JSON payload instead.
$legacyBanks = [
    'Database\\Seeders\\V3\\PronounsDemonstrativesAllLevelsV3Seeder' => [
        'path' => 'database/seeders/V3/PronounsDemonstrativesAllLevelsV3Seeder/definition.json',
        'count' => 40,
    ],
    'Database\\Seeders\\V3\\V2\\IndefinitePronounsPracticeV3Seeder' => [
        'path' => 'database/seeders/V3/V2/IndefinitePronounsPracticeV3Seeder/definition.json',
        'count' => 30,
    ],
];
foreach ($legacyBanks as $class => $binding) {
    $definition = $readSource($binding['path']);
    if (($definition['seeder']['class'] ?? null) !== $class
        || count($definition['questions'] ?? []) !== $binding['count']) {
        throw new RuntimeException('M74 relation-derived legacy source identity/count differs.');
    }
}
ksort($declarations); ksort($pages); ksort($sourceHashes);
PHP;
$legacySqlHook = <<<'PHP'
    $banks = [...array_column($declarations, 'bank'), ...array_keys($legacyBanks)];
PHP;
$legacyQuestionGuard = <<<'PHP'
    foreach ($legacyBanks as $class => $binding) {
        if (count(array_filter($questions, static fn ($q) => $q['seeder'] === $class)) !== $binding['count']) {
            throw new RuntimeException('M74 relation-derived legacy SQL bank count differs.');
        }
    }
    $result = ['questions' => $questions];
PHP;
$legacyBeforeGuard = <<<'PHP'
$expectedRelations = [
    'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAnotherOtherTheOtherTheorySeeder' => [
        'Database\\Seeders\\V3\\NounsArticlesQuantity\\AnotherOtherTheOtherAllLevelsV3Seeder' => 72,
        'Database\\Seeders\\V3\\Polyglot\\PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder' => 72,
    ],
    'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPronounsTheorySeeder' => [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPronounsTheoryAllLevelsLessonSeeder' => 72,
        'Database\\Seeders\\V3\\PronounsDemonstrativesAllLevelsV3Seeder' => 40,
        'Database\\Seeders\\V3\\PronounsDemonstratives\\PronounsTheoryAllLevelsV3Seeder' => 72,
        'Database\\Seeders\\V3\\V2\\IndefinitePronounsPracticeV3Seeder' => 30,
    ],
    'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPersonalObjectPronounsTheorySeeder' => [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPersonalObjectPronounsAllLevelsLessonSeeder' => 72,
        'Database\\Seeders\\V3\\PronounsDemonstratives\\PersonalObjectPronounsAllLevelsV3Seeder' => 72,
    ],
];
if (array_keys($workingBefore['banks'] ?? []) !== array_keys($expectedRelations)) {
    throw new RuntimeException('M74 independent relation-bank owners differ.');
}
foreach ($expectedRelations as $owner => $expected) {
    $actual = $workingBefore['banks'][$owner] ?? [];
    if (array_keys($actual) !== array_keys($expected)) {
        throw new RuntimeException('M74 independent page-wide relation bank list/order differs.');
    }
    foreach ($expected as $class => $count) {
        if (($actual[$class]['identity'] ?? null) !== $class
            || ($actual[$class]['pagewide_count'] ?? null) !== $count) {
            throw new RuntimeException('M74 independent relation-bank identity/count differs.');
        }
    }
}
PHP;
$sourceGuard = '~^database/seeders/V3/(?:TheoryLinks/data/(?:nouns-articles-quantity-another-other-the-other|pronouns-demonstratives-(?:pronouns|personal-object-pronouns))-theory-links\.json|Polyglot/(?:PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder|PolyglotPronounsTheoryAllLevelsLessonSeeder|PolyglotPersonalObjectPronounsAllLevelsLessonSeeder)/definition\.json|NounsArticlesQuantity/AnotherOtherTheOtherAllLevelsV3Seeder/definition\.json|PronounsDemonstratives/(?:PronounsTheoryAllLevelsV3Seeder|PersonalObjectPronounsAllLevelsV3Seeder)/definition\.json|PronounsDemonstrativesAllLevelsV3Seeder/definition\.json|V2/IndefinitePronounsPracticeV3Seeder/definition\.json)$~D';
runFrozenReadOnlyProfile('m52-old-consumers.php', '1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775', [
    ['M52', 'M74', 43], ['m52', 'm74', 4],
    [$oldBaselineLoop, $legacyBeforeGuard."\n".$reviewedBaselineLoop, 1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff', '9e7abb860af2a61bfb10f61047f7ffaad055e20368edfc24c9ed095301516fdc', 1],
    ['passive-voice-formation-rules-theory-links.json', 'nouns-articles-quantity-another-other-the-other-theory-links.json', 1],
    ['passive-voice-negatives-questions-theory-links.json', 'pronouns-demonstratives-pronouns-theory-links.json', 1],
    ['passive-voice-modal-verbs-theory-links.json', 'pronouns-demonstratives-personal-object-pronouns-theory-links.json', 1],
    ['~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\.json)$~D', $sourceGuard, 1],
    [$oldPageGuard, $pageGuard, 1],
    ["    foreach (\$manifest['tests_on_page'] ?? [] as \$test) {", $manifestHook, 1],
    ["        if (!preg_match('/^Database\\\\\\\\Seeders\\\\\\\\V3\\\\\\\\(?:Polyglot|PassiveVoice)\\\\\\\\[A-Za-z0-9]+$/D', \$class)) {", $classGuard, 1],
    ['ksort($declarations); ksort($pages); ksort($sourceHashes);', $legacySourceHook, 1],
    ['$sqlSnapshot = static function () use ($db, $rows, $orderedRows, $declarations): array {',
        '$sqlSnapshot = static function () use ($db, $rows, $orderedRows, $declarations, $legacyBanks): array {', 1],
    ["    \$banks = array_column(\$declarations, 'bank');", $legacySqlHook, 1],
    ["    \$result = ['questions' => \$questions];", $legacyQuestionGuard, 1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60', 'CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120', 1],
    ["    \$http = \$getLocal(\$route); \$entry =", "    echo \$json(['checking_consumer' => \$path]);\n    \$http = \$getLocal(\$route); \$entry =", 1],
    ["    'sql_allowlist_during_apply' => ['pages', 'text_blocks'],",
        "    'core_sql_allowlist_during_apply' => ['pages', 'text_blocks'],\n    'bank_final_file' => \$finalName, 'bank_final_sha256' => \$finalSha,\n    'bank_revision_exact_new_rows' => \$final['new_question_rows'],\n    'bank_revision_exact_practice_body' => [\$final['before_body_sha256'], \$final['after_body_sha256']],", 1],
], $context);
