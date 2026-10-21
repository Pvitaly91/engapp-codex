<?php

/**
 * M74 finite profile of the immutable M52 read-only consumer procedure.
 * Capture is bound to fresh independent BEFORE 9e7abb...; no capture runs on include-free lint.
 * Six declared saved tests plus both relation-derived legacy banks are preserved.
 * No content, question, progress, environment, route or visual source writes.
 */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
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
if ($mode === 'audit') {
    $finalName = $profileContext['final_file'] ?? '';
    $finalSha = $profileContext['final_sha256'] ?? '';
    if (preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $finalName) !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $finalSha) !== 1
        || !is_file($dir.'/'.$finalName) || is_link($dir.'/'.$finalName)
        || !hash_equals($finalSha, hash_file('sha256', $dir.'/'.$finalName))) {
        throw new RuntimeException('M74 reviewed independent final receipt is missing, linked or changed.');
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
        throw new RuntimeException('M74 every current table must equal reviewed AFTER with exact independent BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table => $expected) {
        if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
            throw new RuntimeException('M74 consumer capture found SQL drift from working BEFORE: '.$table);
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
], $context);
