<?php

/** M67 exact old-consumer profile; unchanged M52 SELECT/HTTP procedure. */
require __DIR__.'/frozen-readonly-profile.php';

$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M67 audit additionally requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file' => $argv[5], 'final_sha256' => $argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaselineLoop = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M67 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M67 consumer capture found SQL drift from working BEFORE: '.$table);
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
        throw new RuntimeException('M67 reviewed independent final receipt is missing, linked or changed.');
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
        throw new RuntimeException('M67 every current table must equal reviewed AFTER with exact independent BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table => $expected) {
        if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
            throw new RuntimeException('M67 consumer capture found SQL drift from working BEFORE: '.$table);
        }
    }
}
PHP;

$oldManifestLoop = <<<'PHP'
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
PHP;
$manifestLoop = <<<'PHP'
    $bankSets = [
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusInfinitiveTheorySeeder' => [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotVerbsPlusInfinitiveAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\VerbPatterns\\VerbsPlusInfinitiveAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsStopRememberForgetTryRegretTheorySeeder' => [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotStopRememberForgetTryRegretAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\VerbPatterns\\StopRememberForgetTryRegretAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder' => [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotBeUsedToGetUsedToUsedToAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\VerbPatterns\\BeUsedToGetUsedToUsedToAllLevelsV3Seeder'],
    ];
    $expectedBanks = $bankSets[$page['page_seeder_class']] ?? null;
    $kindSets = [['direct_sentence_builder','standard_v3'],['direct_sentence_builder','standard_v3'],['direct_sentence_builder','standard_v3']];
    $i = array_search($page['page_seeder_class'], array_keys($bankSets), true);
    if ($i === false || array_column($manifest['tests_on_page'] ?? [], 'kind') !== $kindSets[$i]) {
        throw new RuntimeException('M67 independent old manifest bank/kind scope differs.');
    }
    $bankTests = []; $foundBanks = [];
    foreach ($manifest['tests_on_page'] as $test) {
        $classes = isset($test['seeder_class']) ? [$test['seeder_class']] : ($test['expected_seeder_classes'] ?? []);
        if (count($classes) !== 1 || !in_array($classes[0], $expectedBanks, true)) {
            throw new RuntimeException('M67 original old-bank declaration differs.');
        }
        $test['seeder_class'] = $classes[0]; $bankTests[] = $test; $foundBanks[] = $classes[0];
    }
    if ($foundBanks !== $expectedBanks) { throw new RuntimeException('M67 original bank order differs.'); }
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
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusInfinitiveTheorySeeder' => ['verb-patterns'],
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsStopRememberForgetTryRegretTheorySeeder' => ['verb-patterns'],
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder' => ['verb-patterns'],
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
        $expectedPageIds = [
            'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusInfinitiveTheorySeeder' => 200,
            'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsStopRememberForgetTryRegretTheorySeeder' => 197,
            'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder' => 194,
        ];
        if ((int) $page->id !== ($expectedPageIds[$owner] ?? null) || $page->type !== 'theory' || $page->slug !== $source['slug'] || $page->category?->slug !== $source['category_slug']
            || $resolver->categoryPath($page->category) !== $source['category_path']) {
PHP;
$neighborRoutes = <<<'PHP'
// Finite accepted neighbors/categories, never M67 mutation targets.
foreach ([
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsCategorySeeder','category','clauses-and-linking-words'],
    ['VerbPatterns','VerbPatternsCategorySeeder','category','verb-patterns'],
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsReasonTheorySeeder','page','clauses-and-linking-words/reason-because-since-as'],
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsResultTheorySeeder','page','clauses-and-linking-words/result-so-therefore-as-a-result'],
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsContrastTheorySeeder','page','clauses-and-linking-words/contrast-although-though-however'],
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsLinkingWordsTheorySeeder','page','clauses-and-linking-words/linking-words'],
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsPurposeTheorySeeder','page','clauses-and-linking-words/purpose-to-in-order-to-so-that'],
    ['VerbPatterns','VerbPatternsGerundTheorySeeder','page','verb-patterns/gerund'],
    ['VerbPatterns','VerbPatternsToInfinitiveTheorySeeder','page','verb-patterns/to-infinitive'],
    ['VerbPatterns','AdvancedGerundInfinitivePatternsTheorySeeder','page','verb-patterns/advanced-gerund-infinitive-patterns'],
    ['VerbPatterns','VerbPatternsBareInfinitiveTheorySeeder','page','verb-patterns/bare-infinitive'],
    ['VerbPatterns','VerbPatternsGerundVsInfinitiveTheorySeeder','page','verb-patterns/gerund-vs-infinitive'],
    ['VerbPatterns','VerbPatternsVerbsPlusGerundTheorySeeder','page','verb-patterns/verbs-plus-gerund'],
    ['Tenses','TensesUsedToWouldTheorySeeder','page','tenses/used-to-would'],
    ['PassiveVoice/InfinitivesGerund','PassiveVoicePassiveGerundTheorySeeder','page','passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-gerund'],
    ['PassiveVoice/InfinitivesGerund','PassiveVoicePassiveInfinitiveTheorySeeder','page','passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive'],
] as [$family,$basename,$kind,$suffix]) {
    $path = 'database/seeders/Page_V3/'.$family.'/'.$basename.'/definition.json';
    if (!is_file($root.'/'.$path) || is_link($root.'/'.$path) || !is_file($worktree.'/'.$path) || is_link($worktree.'/'.$path)
        || file_get_contents($root.'/'.$path) !== file_get_contents($worktree.'/'.$path)) {
        throw new RuntimeException('M67 exact read-only neighbor source parity differs: '.$basename);
    }
    $bytes=file_get_contents($root.'/'.$path); $definition=json_decode($bytes,true,flags:JSON_THROW_ON_ERROR);
    if (($definition['content_type']??null)!==$kind || ($definition['seeder']['class']??null)!=='Database\\Seeders\\Page_V3\\'.str_replace('/', '\\', $family).'\\'.$basename) {
        throw new RuntimeException('M67 exact read-only neighbor identity differs.');
    }
    $sourceHashes[$path]=hash('sha256',$bytes);
    if ($kind==='category') { $entry=$routePath('theory.category',['category'=>$suffix]); }
    else { $parts=explode('/',$suffix);$slug=array_pop($parts);$entry=$routePath('theory.show',['categoryPath'=>implode('/',$parts),'pageSlug'=>$slug]); }
    if ($entry['path']!=='/theory/'.$suffix) { throw new RuntimeException('M67 exact read-only neighbor URL differs.'); }
    $routes[$entry['path']]=$entry+['kind'=>'neighbor-'.$kind,'expected_count'=>null];
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
        if ($nodes->length !== 1) { throw new RuntimeException('M67 neighbor has no unique actual learning boundary.'); }
        $content = $dom->saveHTML($nodes->item(0));
        if ($content === '' || preg_match('/(?:name=["\']_token["\']|csrf-token)/i', $content)) {
            throw new RuntimeException('M67 neighbor boundary is empty or contains a volatile/security field.');
        }
        $entry['facts']['content_dom'] = $content;
        $entry['facts']['content_dom_sha256'] = hash('sha256', $content);
        $entry['facts']['comparison_policy'] = 'Exact actual category/M64/M65/M66/advanced/Used to-Would/Passive Gerund-Infinitive learning DOM; no auth chrome.';
    } else {
PHP;

runFrozenReadOnlyProfile('m52-old-consumers.php', '1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775', [
    ['M52', 'M67', 43], ['m52', 'm67', 4],
    ['passive-voice-formation-rules-theory-links.json', 'verb-patterns-verbs-plus-infinitive-theory-links.json', 1],
    ['passive-voice-negatives-questions-theory-links.json', 'verb-patterns-stop-remember-forget-try-regret-theory-links.json', 1],
    ['passive-voice-modal-verbs-theory-links.json', 'verb-patterns-be-used-to-get-used-to-used-to-theory-links.json', 1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff',
        '55d8e940ce68287b31cd6522e8377023778092ac265bf5ae92c6adc6661e04b0', 1],

    ['(?:Polyglot|PassiveVoice)', '(?:Polyglot|VerbPatterns|ClausesAndLinkingWords)', 2],
    [$oldManifestLoop, $manifestLoop, 1],
    [$oldPageGuard, $pageGuard, 1],
    ["    \$pages[\$page['page_seeder_class']] = \$page;",
        "    \$page['category_path'] = implode('/', \$ancestry);\n    \$page['category_slug'] = end(\$ancestry);\n    \$pages[\$page['page_seeder_class']] = \$page;", 1],
    [$oldPageLoop, $pageLoop, 1], [$oldPageCheck, $pageCheck, 1],
    ["'categoryPath' => \$source['category_slug']", "'categoryPath' => \$source['category_path']", 1],
    ['~^/(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+$~D',
        '~^/(?:(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+|theory/(?:(?:clauses-and-linking-words|verb-patterns)(?:/[a-z0-9-]+)?|tenses/used-to-would|passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-(?:gerund|infinitive)))$~D', 1],
    ["ksort(\$routes);", $neighborRoutes, 1], [$oldHttpBranch, $httpBranch, 1],
    ["throw new RuntimeException('M67 anonymous consumer loopback GET failed or changed target.');",
        "throw new RuntimeException('M67 anonymous consumer loopback GET failed or changed target: '.\$path);", 1],
    ["    \$http = \$getLocal(\$route); \$entry =",
        "    echo \$json(['checking_consumer' => \$path]);\n    \$http = \$getLocal(\$route); \$entry =", 1],
    [$oldBaselineLoop, $reviewedBaselineLoop, 1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60',
        'CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120', 1],
    ["\$route['kind'] !== 'course' || !in_array(\$http['status'], [401, 403, 404], true)",
        "!in_array(\$route['kind'], ['course', 'neighbor-course-index'], true) || !in_array(\$http['status'], [401, 403, 404], true)", 1],
    ["\$result['all_tables_before_http'] = \$allTablesBeforeHttp;",
        "if (\$mode === 'audit') { \$result['authorized_apply_final_receipt'] = \$profileContext; }\n\$result['all_tables_before_http'] = \$allTablesBeforeHttp;", 1],
], $context);
