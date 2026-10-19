<?php

/** M62 exact old-consumer profile; unchanged M52 SELECT/HTTP procedure. */
require __DIR__.'/frozen-readonly-profile.php';

$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M62 audit additionally requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file' => $argv[5], 'final_sha256' => $argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaselineLoop = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M62 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M62 consumer capture found SQL drift from working BEFORE: '.$table);
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
        throw new RuntimeException('M62 reviewed independent final receipt is missing, linked or changed.');
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
        throw new RuntimeException('M62 every current table must equal reviewed AFTER with exact independent BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table => $expected) {
        if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
            throw new RuntimeException('M62 consumer capture found SQL drift from working BEFORE: '.$table);
        }
    }
}
PHP;

$oldManifestLoop = <<<'PHP'
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
PHP;
$manifestLoop = <<<'PHP'
    // Finite original bank declarations include the preserved B2 course and C1 sibling.
    $bankSets = [
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\AdvancedReportedSpeechTheorySeeder' => [
            'Database\\Seeders\\V3\\ReportedSpeech\\AdvancedReportedSpeechAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedReportedSpeechAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedReportedSpeechB2LessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedReportedSpeechC1LessonSeeder'],
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsRelativeClausesTheorySeeder' => [
            'Database\\Seeders\\V3\\ClausesAndLinkingWords\\RelativeClausesAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotRelativeClausesAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotRelativeClausesLessonSeeder'],
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsDefiningRelativeClausesTheorySeeder' => [
            'Database\\Seeders\\V3\\ClausesAndLinkingWords\\DefiningRelativeClausesAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotDefiningRelativeClausesAllLevelsLessonSeeder'],
    ];
    $kindSets = [
        ['direct_sentence_builder', 'legacy_sentence_builder', 'legacy_sentence_builder', 'mixed_a1_c2'],
        ['direct_sentence_builder', 'legacy_direct_sentence_builder', 'mixed_a1_c2'],
        ['direct_sentence_builder', 'mixed_standard_v3'],
    ];
    $i = array_search($page['page_seeder_class'] ?? '', array_keys($bankSets), true);
    $expectedBanks = $bankSets[$page['page_seeder_class']] ?? null;
    if ($i === false || $expectedBanks === null || array_column($manifest['tests_on_page'] ?? [], 'kind') !== $kindSets[$i]) {
        throw new RuntimeException('M62 exact old manifest bank/kind declarations differ.');
    }
    $bankTests = []; $foundBanks = [];
    foreach ($manifest['tests_on_page'] as $test) {
        if (($test['kind'] ?? null) !== 'mixed_a1_c2') {
            if (!in_array($test['seeder_class'] ?? null, $expectedBanks, true)) { throw new RuntimeException('M62 original bank declaration differs.'); }
            $bankTests[] = $test; $foundBanks[] = $test['seeder_class']; continue;
        }
        $mixed = $test['expected_seeder_classes'] ?? [];
        if ($mixed !== array_slice($expectedBanks, 0, 2)
            || !in_array($test['saved_test_slug'] ?? '', ['theory-page-{page_id}-mixed-a1-c2', 'virtual:theory-page-mixed-a1-c2'], true)) {
            throw new RuntimeException('M62 mixed original bank declaration differs.');
        }
        foreach ($mixed as $bank) { $bankTests[] = ['seeder_class' => $bank]; $foundBanks[] = $bank; }
    }
    $foundBanks = array_values(array_unique($foundBanks)); sort($foundBanks); $sortedBanks = $expectedBanks; sort($sortedBanks);
    if ($foundBanks !== $sortedBanks) { throw new RuntimeException('M62 original bank inventory is incomplete.'); }
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
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\AdvancedReportedSpeechTheorySeeder' => ['reported-speech'],
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsRelativeClausesTheorySeeder' => ['clauses-and-linking-words'],
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsDefiningRelativeClausesTheorySeeder' => ['clauses-and-linking-words'],
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
// Read-only source/learning consumers; none is an additional M62 update target.
foreach ([
    ['ReportedSpeech', 'ReportedSpeechCategorySeeder', 'category', 'reported-speech'],
    ['ClausesAndLinkingWords', 'ClausesAndLinkingWordsCategorySeeder', 'category', 'clauses-and-linking-words'],
    ['ReportedSpeech', 'ReportedSpeechStatementsTheorySeeder', 'page', 'reported-speech/reported-statements'],
    ['ReportedSpeech', 'ReportedSpeechQuestionsTheorySeeder', 'page', 'reported-speech/reported-questions'],
    ['ReportedSpeech', 'ReportedSpeechCommandsRequestsTheorySeeder', 'page', 'reported-speech/reported-commands-and-requests'],
    ['ReportedSpeech', 'ReportedSpeechBackshiftTheorySeeder', 'page', 'reported-speech/backshift-of-tenses'],
    ['ReportedSpeech', 'ReportedSpeechTimePlaceChangesTheorySeeder', 'page', 'reported-speech/time-and-place-changes'],
    ['ReportedSpeech', 'ReportedSpeechSayTellAskTheorySeeder', 'page', 'reported-speech/say-tell-ask'],
    ['ClausesAndLinkingWords', 'ClausesAndLinkingWordsNonDefiningRelativeClausesTheorySeeder', 'page', 'clauses-and-linking-words/non-defining-relative-clauses'],
    ['ClausesAndLinkingWords', 'ClausesAndLinkingWordsOmittingRelativePronounsTheorySeeder', 'page', 'clauses-and-linking-words/omitting-relative-pronouns'],
    ['RelativeClauses', 'ComplexRelativeClausesTheorySeeder', 'page', 'relative-clauses/complex-relative-clauses'],
    ['ClausesAndLinkingWords', 'LinkingWordsReasonResultContrastTheorySeeder', 'page', 'clauses-and-linking-words/linking-words-reason-result-contrast'],
    ['ClausesAndLinkingWords', 'AdvancedLinkingDevicesTheorySeeder', 'page', 'clauses-and-linking-words/advanced-linking-devices'],
    ['ClausesAndLinkingWords', 'ConcessiveAndContrastiveStructuresTheorySeeder', 'page', 'clauses-and-linking-words/concessive-and-contrastive-structures']
] as [$family, $basename, $kind, $suffix]) {
    $path = 'database/seeders/Page_V3/'.$family.'/'.$basename.'/definition.json';
    if (!is_file($root.'/'.$path) || is_link($root.'/'.$path) || !is_file($worktree.'/'.$path) || is_link($worktree.'/'.$path)
        || file_get_contents($root.'/'.$path) !== file_get_contents($worktree.'/'.$path)) {
        throw new RuntimeException('M62 exact read-only neighbor source parity differs.');
    }
    $bytes = file_get_contents($root.'/'.$path); $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    if (($definition['content_type'] ?? null) !== $kind
        || ($definition['seeder']['class'] ?? null) !== 'Database\\Seeders\\Page_V3\\'.$family.'\\'.$basename) {
        throw new RuntimeException('M62 exact read-only neighbor identity differs.');
    }
    $sourceHashes[$path] = hash('sha256', $bytes);
    if ($kind === 'category') { $entry = $routePath('theory.category', ['category' => $suffix]); }
    else {
        $parts = explode('/', $suffix); $slug = array_pop($parts);
        $entry = $routePath('theory.show', ['categoryPath' => implode('/', $parts), 'pageSlug' => $slug]);
    }
    if ($entry['path'] !== '/theory/'.$suffix) { throw new RuntimeException('M62 exact read-only neighbor URL differs.'); }
    $routes[$entry['path']] = $entry + ['kind' => 'neighbor-'.$kind, 'expected_count' => null];
}
foreach ([['polyglot-english-b2.json', 'sentence-builder-english-b2', 'polyglot-advanced-reported-speech-b2', 6, 'B2'],
    ['polyglot-english-c1.json', 'sentence-builder-english-c1', 'polyglot-advanced-reported-speech-c1', 12, 'C1'],
    ['theory-driven.json', 'theory-driven', 'course-td-advanced-reported-speech', 55, 'A1']] as [$file, $publicCourse, $lessonSlug, $order, $level]) {
    $manifestPath = 'database/seeders/V3/Polyglot/Course/'.$file;
    if (!is_file($root.'/'.$manifestPath) || is_link($root.'/'.$manifestPath)
        || !is_file($worktree.'/'.$manifestPath) || is_link($worktree.'/'.$manifestPath)
        || file_get_contents($root.'/'.$manifestPath) !== file_get_contents($worktree.'/'.$manifestPath)) {
        throw new RuntimeException('M62 existing course manifest source parity differs.');
    }
    $sourceHashes[$manifestPath] = hash_file('sha256', $root.'/'.$manifestPath);
    $course = json_decode(file_get_contents($root.'/'.$manifestPath), true, flags: JSON_THROW_ON_ERROR);
    $anchors = array_values(array_filter($course['lessons'] ?? [], static fn ($lesson) => ($lesson['slug'] ?? null) === $lessonSlug));
    if (count($anchors) !== 1 || ($anchors[0]['lesson_order'] ?? null) !== $order
        || ($anchors[0]['level'] ?? null) !== $level || ($anchors[0]['status'] ?? null) !== 'implemented'
        || ($anchors[0]['theory_category_slug'] ?? null) !== 'reported-speech'
        || ($anchors[0]['theory_page_slug'] ?? null) !== 'advanced-reported-speech') {
        throw new RuntimeException('M62 exact existing B2/C1/theory-driven lesson anchor differs.');
    }
    $entry = $routePath('courses.show', ['courseSlug' => $publicCourse]);
    if ($entry['path'] !== '/courses/'.$publicCourse) { throw new RuntimeException('M62 course index route differs.'); }
    $routes[$entry['path']] = $entry + ['kind' => 'neighbor-course-index', 'expected_count' => null];
    foreach (['test.show' => 'ssr', 'test.js.questions' => 'json', 'test.step-compose' => 'ssr'] as $routeName => $kind) {
        $entry = $routePath($routeName, ['slug' => $lessonSlug]);
        $routes[$entry['path']] = $entry + ['kind' => $kind, 'expected_count' => null];
    }
}
ksort($sourceHashes); ksort($routes);
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
        if ($nodes->length !== 1) { throw new RuntimeException('M62 neighbor has no unique actual learning boundary.'); }
        $content = $dom->saveHTML($nodes->item(0));
        if ($content === '' || preg_match('/(?:name=["\']_token["\']|csrf-token)/i', $content)) {
            throw new RuntimeException('M62 neighbor boundary is empty or contains a volatile/security field.');
        }
        $entry['facts']['content_dom'] = $content;
        $entry['facts']['content_dom_sha256'] = hash('sha256', $content);
        $entry['facts']['comparison_policy'] = 'Exact actual category/M60/M61/relative/linking learning DOM and B2 course lesson index; no auth chrome.';
    } else {
PHP;

runFrozenReadOnlyProfile('m52-old-consumers.php', '1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775', [
    ['M52', 'M62', 43], ['m52', 'm62', 4],
    ['passive-voice-formation-rules-theory-links.json', 'reported-speech-advanced-reported-speech-theory-links.json', 1],
    ['passive-voice-negatives-questions-theory-links.json', 'clauses-linking-words-relative-clauses-theory-links.json', 1],
    ['passive-voice-modal-verbs-theory-links.json', 'relative-clauses-defining-relative-clauses-theory-links.json', 1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff',
        'c1d652a764102b5abc9b5dd8a8c9226cd79126302dce58a69e52aab7a9b3c55d', 1],

    ['(?:Polyglot|PassiveVoice)', '(?:Polyglot|ReportedSpeech|ClausesAndLinkingWords)', 2],
    [$oldManifestLoop, $manifestLoop, 1],
    [$oldPageGuard, $pageGuard, 1],
    ["    \$pages[\$page['page_seeder_class']] = \$page;",
        "    \$page['category_path'] = implode('/', \$ancestry);\n    \$page['category_slug'] = end(\$ancestry);\n    \$pages[\$page['page_seeder_class']] = \$page;", 1],
    [$oldPageLoop, $pageLoop, 1], [$oldPageCheck, $pageCheck, 1],
    ["'categoryPath' => \$source['category_slug']", "'categoryPath' => \$source['category_path']", 1],
    ['~^/(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+$~D',
        '~^/(?:(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+|courses/(?:sentence-builder-english-(?:b2|c1)|theory-driven)|theory/reported-speech|theory/clauses-and-linking-words|theory/reported-speech/reported-statements|theory/reported-speech/reported-questions|theory/reported-speech/reported-commands-and-requests|theory/reported-speech/backshift-of-tenses|theory/reported-speech/time-and-place-changes|theory/reported-speech/say-tell-ask|theory/clauses-and-linking-words/non-defining-relative-clauses|theory/clauses-and-linking-words/omitting-relative-pronouns|theory/relative-clauses/complex-relative-clauses|theory/clauses-and-linking-words/linking-words-reason-result-contrast|theory/clauses-and-linking-words/advanced-linking-devices|theory/clauses-and-linking-words/concessive-and-contrastive-structures)$~D', 1],
    ["ksort(\$routes);", $neighborRoutes, 1], [$oldHttpBranch, $httpBranch, 1],
    [$oldBaselineLoop, $reviewedBaselineLoop, 1],
    ["\$route['kind'] !== 'course' || !in_array(\$http['status'], [401, 403, 404], true)",
        "!in_array(\$route['kind'], ['course', 'neighbor-course-index'], true) || !in_array(\$http['status'], [401, 403, 404], true)", 1],
    ["\$result['all_tables_before_http'] = \$allTablesBeforeHttp;",
        "if (\$mode === 'audit') { \$result['authorized_apply_final_receipt'] = \$profileContext; }\n\$result['all_tables_before_http'] = \$allTablesBeforeHttp;", 1],
], $context);
