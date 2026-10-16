<?php

/** M61 exact old-consumer profile; unchanged M52 SELECT/HTTP procedure. */
require __DIR__.'/frozen-readonly-profile.php';

$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M61 audit additionally requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file' => $argv[5], 'final_sha256' => $argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaselineLoop = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M61 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M61 consumer capture found SQL drift from working BEFORE: '.$table);
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
        throw new RuntimeException('M61 reviewed independent final receipt is missing, linked or changed.');
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
        throw new RuntimeException('M61 every current table must equal reviewed AFTER with exact independent BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table => $expected) {
        if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
            throw new RuntimeException('M61 consumer capture found SQL drift from working BEFORE: '.$table);
        }
    }
}
PHP;

$oldManifestLoop = <<<'PHP'
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
PHP;
$manifestLoop = <<<'PHP'
    // Finite M61 source schema: mixed declarations list two existing banks.
    // Their native page-mixed endpoints remain captured by TheoryPageTestSlug.
    $bankSets = [
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechBackshiftTheorySeeder' => [
            'Database\\Seeders\\V3\\ReportedSpeech\\ReportedSpeechBackshiftOfTensesAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotReportedSpeechBackshiftOfTensesAllLevelsLessonSeeder'],
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechTimePlaceChangesTheorySeeder' => [
            'Database\\Seeders\\V3\\ReportedSpeech\\ReportedSpeechTimePlaceChangesAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotReportedSpeechTimePlaceChangesAllLevelsLessonSeeder'],
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechSayTellAskTheorySeeder' => [
            'Database\\Seeders\\V3\\ReportedSpeech\\ReportedSpeechSayTellAskAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotReportedSpeechSayTellAskAllLevelsLessonSeeder'],
    ];
    $expectedBanks = $bankSets[$page['page_seeder_class']] ?? null;
    $expectedKinds = $page['page_seeder_class'] === 'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechBackshiftTheorySeeder'
        ? ['direct_sentence_builder', 'standard_v3'] : ['direct_sentence_builder', 'mixed_a1_c2'];
    if ($expectedBanks === null
        || [...($manifest['expected_standard_seeders'] ?? []), ...($manifest['expected_polyglot_seeders'] ?? [])] !== $expectedBanks
        || array_column($manifest['tests_on_page'] ?? [], 'kind') !== $expectedKinds) {
        throw new RuntimeException('M61 exact old manifest bank/kind declarations differ.');
    }
    $bankTests = [];
    foreach ($manifest['tests_on_page'] as $test) {
        if (($test['kind'] ?? null) !== 'mixed_a1_c2') { $bankTests[] = $test; continue; }
        if (($test['expected_seeder_classes'] ?? null) !== $expectedBanks
            || ($test['saved_test_slug'] ?? null) !== 'theory-page-{page_id}-mixed-a1-c2') {
            throw new RuntimeException('M61 mixed declaration differs from the exact two old banks.');
        }
        foreach ($expectedBanks as $bank) { $bankTests[] = ['seeder_class' => $bank]; }
    }
    foreach ($bankTests as $test) {
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
    $ancestries = [
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechBackshiftTheorySeeder' => ['reported-speech'],
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechTimePlaceChangesTheorySeeder' => ['reported-speech'],
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechSayTellAskTheorySeeder' => ['reported-speech'],
    ];
    $ancestry = $ancestries[$page['page_seeder_class'] ?? ''] ?? null;
    if ($ancestry === null || ($page['category_slug'] ?? null) !== implode('/', $ancestry)
        || ($page['route'] ?? null) !== '/theory/'.implode('/', $ancestry).'/'.($page['slug'] ?? '')) {
PHP;
$oldPageLoop = <<<'PHP'
    foreach ($pages as $owner => $source) {
PHP;
$pageLoop = <<<'PHP'
    $resolver = new class extends App\Http\Controllers\PageController {
        public function __construct() {}
        public function categoryPath(App\Models\PageCategory $category): string { return $this->categorySlugPath($category); }
    };
    foreach ($pages as $owner => $source) {
PHP;
$oldPageCheck = <<<'PHP'
        if ($page->type !== 'theory' || $page->slug !== $source['slug'] || $page->category?->slug !== $source['category_slug']) {
PHP;
$pageCheck = <<<'PHP'
        if ($page->type !== 'theory' || $page->slug !== $source['slug'] || $page->category?->slug !== $source['category_slug']
            || $resolver->categoryPath($page->category) !== $source['category_path']) {
PHP;
$neighborRoutes = <<<'PHP'
// These are read-only preservation consumers, not additional M61 update targets.
// All accepted M55–M60 pages and Reported Speech neighbors remain immutable read-only consumers.
foreach ([
    ['file' => 'PassiveVoiceInfinitivesGerundCategorySeeder', 'folder' => 'InfinitivesGerund', 'content_type' => 'category',
        'route' => 'theory.category', 'parameters' => ['category' => 'passive-voice-infinitives-gerund'],
        'path' => '/theory/passive-voice-infinitives-gerund', 'kind' => 'neighbor-category'],
    ['file' => 'PassiveVoiceCommonMistakesTheorySeeder', 'folder' => 'Basics', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice', 'pageSlug' => 'theory-passive-voice-common-mistakes'],
        'path' => '/theory/passive-voice/theory-passive-voice-common-mistakes', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoiceFormalityStyleTheorySeeder', 'folder' => 'Basics', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice', 'pageSlug' => 'theory-passive-voice-formality-style'],
        'path' => '/theory/passive-voice/theory-passive-voice-formality-style', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoicePassiveInfinitiveTheorySeeder', 'folder' => 'InfinitivesGerund', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice/passive-voice-infinitives-gerund',
            'pageSlug' => 'theory-passive-voice-passive-infinitive'],
        'path' => '/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoiceTensesCategorySeeder', 'folder' => 'Tenses', 'content_type' => 'category',
        'route' => 'theory.category', 'parameters' => ['category' => 'passive-voice-tenses'],
        'path' => '/theory/passive-voice-tenses', 'kind' => 'neighbor-category'],
    ['file' => 'PassiveVoicePassiveGerundTheorySeeder', 'folder' => 'InfinitivesGerund', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice/passive-voice-infinitives-gerund',
            'pageSlug' => 'theory-passive-voice-passive-gerund'],
        'path' => '/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-gerund', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoicePresentSimpleTheorySeeder', 'folder' => 'Tenses', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice', 'pageSlug' => 'theory-passive-voice-present-simple'],
        'path' => '/theory/passive-voice/theory-passive-voice-present-simple', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoicePastSimpleTheorySeeder', 'folder' => 'Tenses', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice', 'pageSlug' => 'theory-passive-voice-past-simple'],
        'path' => '/theory/passive-voice/theory-passive-voice-past-simple', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoicePresentContinuousTheorySeeder', 'folder' => 'Tenses', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice/passive-voice-tenses',
            'pageSlug' => 'theory-passive-voice-present-continuous'],
        'path' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-continuous', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoicePastContinuousTheorySeeder', 'folder' => 'Tenses', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice/passive-voice-tenses',
            'pageSlug' => 'theory-passive-voice-past-continuous'],
        'path' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-continuous', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoicePresentPerfectTheorySeeder', 'folder' => 'Tenses', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice/passive-voice-tenses',
            'pageSlug' => 'theory-passive-voice-present-perfect'],
        'path' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-perfect', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoicePastPerfectTheorySeeder', 'folder' => 'Tenses', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice/passive-voice-tenses',
            'pageSlug' => 'theory-passive-voice-past-perfect'],
        'path' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-perfect', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoiceFutureSimpleTheorySeeder', 'folder' => 'Tenses', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice/passive-voice-tenses',
            'pageSlug' => 'theory-passive-voice-future-simple'],
        'path' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-simple', 'kind' => 'neighbor-page'],
    ['file' => 'PassiveVoiceFuturePerfectTheorySeeder', 'folder' => 'Tenses', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice/passive-voice-tenses',
            'pageSlug' => 'theory-passive-voice-future-perfect'],
        'path' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-perfect', 'kind' => 'neighbor-page'],
    ['family' => 'PassiveVoice', 'file' => 'PassiveVoiceFutureContinuousTheorySeeder', 'folder' => 'Tenses', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice/passive-voice-tenses', 'pageSlug' => 'theory-passive-voice-future-continuous'],
        'path' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-continuous', 'kind' => 'neighbor-page'],
    ['family' => 'PassiveVoice', 'file' => 'PassiveVoiceByPhraseTheorySeeder', 'folder' => 'SpecialCases', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice', 'pageSlug' => 'theory-passive-voice-by-phrase'],
        'path' => '/theory/passive-voice/theory-passive-voice-by-phrase', 'kind' => 'neighbor-page'],
    ['family' => 'PassiveVoice', 'file' => 'AdvancedPassiveVoiceTheorySeeder', 'folder' => '', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'passive-voice', 'pageSlug' => 'advanced-passive-voice'],
        'path' => '/theory/passive-voice/advanced-passive-voice', 'kind' => 'neighbor-page'],
    ['family' => 'ReportedSpeech', 'file' => 'ReportedSpeechCategorySeeder', 'folder' => '', 'content_type' => 'category',
        'route' => 'theory.category', 'parameters' => ['category' => 'reported-speech'],
        'path' => '/theory/reported-speech', 'kind' => 'neighbor-category'],
    ['family' => 'ReportedSpeech', 'file' => 'ReportedSpeechStatementsTheorySeeder', 'folder' => '', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'reported-speech', 'pageSlug' => 'reported-statements'],
        'path' => '/theory/reported-speech/reported-statements', 'kind' => 'neighbor-page'],
    ['family' => 'ReportedSpeech', 'file' => 'ReportedSpeechQuestionsTheorySeeder', 'folder' => '', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'reported-speech', 'pageSlug' => 'reported-questions'],
        'path' => '/theory/reported-speech/reported-questions', 'kind' => 'neighbor-page'],
    ['family' => 'ReportedSpeech', 'file' => 'ReportedSpeechCommandsRequestsTheorySeeder', 'folder' => '', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'reported-speech', 'pageSlug' => 'reported-commands-and-requests'],
        'path' => '/theory/reported-speech/reported-commands-and-requests', 'kind' => 'neighbor-page'],
    ['family' => 'ReportedSpeech', 'file' => 'AdvancedReportedSpeechTheorySeeder', 'folder' => '', 'content_type' => 'page',
        'route' => 'theory.show', 'parameters' => ['categoryPath' => 'reported-speech', 'pageSlug' => 'advanced-reported-speech'],
        'path' => '/theory/reported-speech/advanced-reported-speech', 'kind' => 'neighbor-page'],
] as $neighbor) {
    $family = $neighbor['family'] ?? 'PassiveVoice';
    if (!in_array($family, ['PassiveVoice', 'ReportedSpeech'], true)) { throw new RuntimeException('M61 foreign read-only source family.'); }
    $suffix = ($neighbor['folder'] === '' ? '' : $neighbor['folder'].'/').$neighbor['file'];
    $path = 'database/seeders/Page_V3/'.$family.'/'.$suffix.'/definition.json';
    if (!is_file($root.'/'.$path) || is_link($root.'/'.$path) || !is_file($worktree.'/'.$path) || is_link($worktree.'/'.$path)) {
        throw new RuntimeException('M61 read-only neighbor source unavailable or linked.');
    }
    $bytes = file_get_contents($root.'/'.$path);
    if ($bytes !== file_get_contents($worktree.'/'.$path)) { throw new RuntimeException('M61 neighbor source parity differs.'); }
    $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    if (($definition['content_type'] ?? null) !== $neighbor['content_type']
        || ($definition['seeder']['class'] ?? null) !== 'Database\\Seeders\\Page_V3\\'.$family.'\\'.str_replace('/', '\\', $suffix)) {
        throw new RuntimeException('M61 non-target neighbor identity differs.');
    }
    $sourceHashes[$path] = hash('sha256', $bytes);
    $entry = $routePath($neighbor['route'], $neighbor['parameters']);
    if ($entry['path'] !== $neighbor['path']) { throw new RuntimeException('M61 native neighbor URL differs.'); }
    $routes[$entry['path']] = $entry + ['kind' => $neighbor['kind'], 'expected_count' => null];
}
ksort($sourceHashes);
foreach (['a2', 'b1', 'b2'] as $level) {
    $manifestPath = 'database/seeders/V3/Polyglot/Course/polyglot-english-'.$level.'.json';
    if (!is_file($root.'/'.$manifestPath) || is_link($root.'/'.$manifestPath)
        || !is_file($worktree.'/'.$manifestPath) || is_link($worktree.'/'.$manifestPath)
        || file_get_contents($root.'/'.$manifestPath) !== file_get_contents($worktree.'/'.$manifestPath)) {
        throw new RuntimeException('M61 accepted course manifest parity differs.');
    }
    $sourceHashes[$manifestPath] = hash_file('sha256', $root.'/'.$manifestPath);
    $entry = $routePath('courses.show', ['courseSlug' => 'sentence-builder-english-'.$level]);
    if ($entry['path'] !== '/courses/sentence-builder-english-'.$level) { throw new RuntimeException('M61 actual course index route differs.'); }
    $routes[$entry['path']] = $entry + ['kind' => 'neighbor-course-index', 'expected_count' => null];
}
ksort($sourceHashes);
ksort($routes);
PHP;
$oldHttpBranch = <<<'PHP'
    } elseif ($route['kind'] === 'course') {
        $entry['facts'] = $htmlFacts($http['body'], true);
    } else {
PHP;
$httpBranch = <<<'PHP'
    } elseif ($route['kind'] === 'course') {
        $entry['facts'] = $htmlFacts($http['body'], true);
    } elseif (in_array($route['kind'], ['neighbor-category', 'neighbor-page', 'neighbor-course-index'], true)) {
        $entry['facts'] = $htmlFacts($http['body'], false);
        $dom = new DOMDocument('1.0', 'UTF-8'); $old = libxml_use_internal_errors(true);
        try { $dom->loadHTML('<?xml encoding="UTF-8">'.$http['body'], LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
        $xp = new DOMXPath($dom);
        $boundary = match ($route['kind']) {
            'neighbor-category' => '//*[contains(concat(" ",normalize-space(@class)," ")," theory-category-learning ")]',
            'neighbor-course-index' => '//*[@id="polyglot-course-lessons"]',
            default => '//*[@data-theory-main]',
        };
        $nodes = $xp->query($boundary);
        if ($nodes->length !== 1) { throw new RuntimeException('M61 neighbor has no unique actual learning boundary.'); }
        $content = $dom->saveHTML($nodes->item(0));
        if ($content === '' || preg_match('/(?:name=["\']_token["\']|csrf-token)/i', $content)) {
            throw new RuntimeException('M61 neighbor boundary is empty or contains a volatile/security field.');
        }
        $entry['facts']['content_dom'] = $content;
        $entry['facts']['content_dom_sha256'] = hash('sha256', $content);
        $entry['facts']['comparison_policy'] = 'Exact actual category-description/M55–M60/Reported Speech learning DOM; no selected-lesson cards or auth chrome.';
    } else {
PHP;

runFrozenReadOnlyProfile('m52-old-consumers.php', '1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775', [
    ['M52', 'M61', 43], ['m52', 'm61', 4],
    ['passive-voice-formation-rules-theory-links.json', 'reported-speech-backshift-of-tenses-theory-links.json', 1],
    ['passive-voice-negatives-questions-theory-links.json', 'reported-speech-time-place-changes-theory-links.json', 1],
    ['passive-voice-modal-verbs-theory-links.json', 'reported-speech-say-tell-ask-theory-links.json', 1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff',
        'ba414b3857d4a6d430f7037a62a9d50b8fac589be8bea30eaeeeed99df71059d', 1],

    ['(?:Polyglot|PassiveVoice)', '(?:Polyglot|ReportedSpeech)', 2],
    [$oldManifestLoop, $manifestLoop, 1],
    [$oldPageGuard, $pageGuard, 1],
    ["    \$pages[\$page['page_seeder_class']] = \$page;",
        "    \$page['category_path'] = implode('/', \$ancestry);\n    \$page['category_slug'] = end(\$ancestry);\n    \$pages[\$page['page_seeder_class']] = \$page;", 1],
    [$oldPageLoop, $pageLoop, 1], [$oldPageCheck, $pageCheck, 1],
    ["'categoryPath' => \$source['category_slug']", "'categoryPath' => \$source['category_path']", 1],
    ['~^/(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+$~D',
        '~^/(?:(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+|courses/sentence-builder-english-(?:a2|b1|b2)|theory/passive-voice-infinitives-gerund|theory/passive-voice/theory-passive-voice-common-mistakes|theory/passive-voice/theory-passive-voice-formality-style|theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive|theory/passive-voice-tenses|theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-gerund|theory/passive-voice/theory-passive-voice-present-simple|theory/passive-voice/theory-passive-voice-past-simple|theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-continuous|theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-continuous|theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-perfect|theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-perfect|theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-simple|theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-perfect|theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-continuous|theory/passive-voice/theory-passive-voice-by-phrase|theory/passive-voice/advanced-passive-voice|theory/reported-speech|theory/reported-speech/reported-statements|theory/reported-speech/reported-questions|theory/reported-speech/reported-commands-and-requests|theory/reported-speech/advanced-reported-speech)$~D', 1],
    ["ksort(\$routes);", $neighborRoutes, 1], [$oldHttpBranch, $httpBranch, 1],
    [$oldBaselineLoop, $reviewedBaselineLoop, 1],
    ["\$route['kind'] !== 'course' || !in_array(\$http['status'], [401, 403, 404], true)",
        "!in_array(\$route['kind'], ['course', 'neighbor-course-index'], true) || !in_array(\$http['status'], [401, 403, 404], true)", 1],
    ["\$result['all_tables_before_http'] = \$allTablesBeforeHttp;",
        "if (\$mode === 'audit') { \$result['authorized_apply_final_receipt'] = \$profileContext; }\n\$result['all_tables_before_http'] = \$allTablesBeforeHttp;", 1],
], $context);
