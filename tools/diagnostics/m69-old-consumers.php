<?php

/** M69 exact old-consumer profile; unchanged M52 SELECT/HTTP procedure. */
require __DIR__.'/frozen-readonly-profile.php';

$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M69 audit additionally requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file' => $argv[5], 'final_sha256' => $argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaselineLoop = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M69 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M69 consumer capture found SQL drift from working BEFORE: '.$table);
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
        throw new RuntimeException('M69 reviewed independent final receipt is missing, linked or changed.');
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
        throw new RuntimeException('M69 every current table must equal reviewed AFTER with exact independent BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table => $expected) {
        if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
            throw new RuntimeException('M69 consumer capture found SQL drift from working BEFORE: '.$table);
        }
    }
}
PHP;

$oldManifestLoop = <<<'PHP'
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
PHP;
$manifestLoop = <<<'PHP'
    $bankSets = [
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesVsAdverbsTheorySeeder' => [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotAdjectivesVsAdverbsAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\AdjectivesVsAdverbsAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesOrderOfAdjectivesTheorySeeder' => [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotOrderOfAdjectivesAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\OrderOfAdjectivesAllLevelsV3Seeder'],
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEdIngAdjectivesTheorySeeder' => [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotEdIngAdjectivesAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\EdIngAdjectivesAllLevelsV3Seeder'],
    ];
    $expectedBanks = $bankSets[$page['page_seeder_class']] ?? null;
    $kindSets = [
        ['direct_sentence_builder', 'virtual_mixed_test'],
        ['direct_sentence_builder', 'virtual_mixed_test'],
        ['direct_sentence_builder', 'virtual_mixed_test'],
    ];
    $i = array_search($page['page_seeder_class'], array_keys($bankSets), true);
    if ($i === false || array_column($manifest['tests_on_page'] ?? [], 'kind') !== $kindSets[$i]) {
        throw new RuntimeException('M69 independent old manifest bank/kind scope differs.');
    }
    $bankTests = []; $foundBanks = [];
    foreach ($manifest['tests_on_page'] as $test) {
        if (($test['kind'] ?? null) === 'virtual_mixed_test') {
            $virtualExpected = [end($expectedBanks), ...array_slice($expectedBanks, 0, -1)];
            if (($test['expected_seeder_classes'] ?? null) !== $virtualExpected
                || ($test['expected_standard_seeders'] ?? null) !== [end($expectedBanks)]
                || ($test['expected_polyglot_seeders'] ?? null) !== array_slice($expectedBanks, 0, -1)) {
                throw new RuntimeException('M69 original mixed bank pool/order differs.');
            }
            $class = end($expectedBanks);
            $test['seeder_class'] = $class;
        } else {
            $class = $test['seeder_class'] ?? '';
            if (!is_string($class) || !in_array($class, $expectedBanks, true)) {
                throw new RuntimeException('M69 original direct/legacy old-bank declaration differs.');
            }
        }
        $bankTests[] = $test; $foundBanks[] = $class;
    }
    if ($foundBanks !== $expectedBanks) { throw new RuntimeException('M69 original six-bank order differs.'); }
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
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesVsAdverbsTheorySeeder' => ['prykmetniky-ta-pryslinknyky'],
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesOrderOfAdjectivesTheorySeeder' => ['prykmetniky-ta-pryslinknyky'],
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEdIngAdjectivesTheorySeeder' => ['prykmetniky-ta-pryslinknyky'],
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
            'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesVsAdverbsTheorySeeder' => 279,
            'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesOrderOfAdjectivesTheorySeeder' => 278,
            'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEdIngAdjectivesTheorySeeder' => 275,
        ];
        if ((int) $page->id !== ($expectedPageIds[$owner] ?? null) || (int) $page->category?->id !== 52
            || $page->category?->language !== 'uk' || $page->category?->type !== 'theory'
            || $page->type !== 'theory' || $page->slug !== $source['slug'] || $page->category?->slug !== $source['category_slug']
            || $resolver->categoryPath($page->category) !== $source['category_path']) {
PHP;
$neighborRoutes = <<<'PHP'
// Finite source identities, never M69 mutation targets. URLs derive from the
// actual versioned slug/category and the existing category-path resolver.
$neighborResolver = new class extends App\Http\Controllers\PageController {
    public function __construct() {}
    public function categoryPath(App\Models\PageCategory $category): string { return $this->categorySlugPath($category); }
};
$neighborSources = [
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsCategorySeeder','category'],
    ['VerbPatterns','VerbPatternsCategorySeeder','category'],
    ['Adjectives','AdjectivesCategorySeeder','category'],
    ['Tenses','TensesCategorySeeder','category'],
    ['PassiveVoice','PassiveVoiceCategorySeeder','category'],
    ['PassiveVoice/InfinitivesGerund','PassiveVoiceInfinitivesGerundCategorySeeder','category'],
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsReasonTheorySeeder','page'],
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsResultTheorySeeder','page'],
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsContrastTheorySeeder','page'],
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsLinkingWordsTheorySeeder','page'],
    ['ClausesAndLinkingWords','ClausesAndLinkingWordsPurposeTheorySeeder','page'],
    ['VerbPatterns','VerbPatternsGerundTheorySeeder','page'],
    ['VerbPatterns','VerbPatternsToInfinitiveTheorySeeder','page'],
    ['VerbPatterns','AdvancedGerundInfinitivePatternsTheorySeeder','page'],
    ['VerbPatterns','VerbPatternsBareInfinitiveTheorySeeder','page'],
    ['VerbPatterns','VerbPatternsGerundVsInfinitiveTheorySeeder','page'],
    ['VerbPatterns','VerbPatternsVerbsPlusGerundTheorySeeder','page'],
    ['VerbPatterns','VerbPatternsVerbsPlusInfinitiveTheorySeeder','page'],
    ['VerbPatterns','VerbPatternsStopRememberForgetTryRegretTheorySeeder','page'],
    ['VerbPatterns','VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder','page'],
    ['Tenses','TensesUsedToWouldTheorySeeder','page'],
    ['PassiveVoice/InfinitivesGerund','PassiveVoicePassiveGerundTheorySeeder','page'],
    ['PassiveVoice/InfinitivesGerund','PassiveVoicePassiveInfinitiveTheorySeeder','page'],
    ['Adjectives','AdjectivesDegreesOfComparisonTheorySeeder','page'],
    ['Adjectives','AdjectivesComparativeVsSuperlativeTheorySeeder','page'],
    ['Adjectives','AdjectivesEqualityComparisonAsAsNotAsAsTheorySeeder','page'],
    ['Adjectives','AdjectivesIntensifiersTheorySeeder','page'],
];
$readNeighbor = static function (string $family, string $basename, string $kind) use ($root, $worktree, &$sourceHashes): array {
    $path = 'database/seeders/Page_V3/'.$family.'/'.$basename.'/definition.json';
    if (!is_file($root.'/'.$path) || is_link($root.'/'.$path) || !is_file($worktree.'/'.$path) || is_link($worktree.'/'.$path)
        || file_get_contents($root.'/'.$path) !== file_get_contents($worktree.'/'.$path)) {
        throw new RuntimeException('M69 exact read-only neighbor source parity differs: '.$basename);
    }
    $bytes = file_get_contents($root.'/'.$path); $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    if (($definition['content_type'] ?? null) !== $kind
        || ($definition['seeder']['class'] ?? null) !== 'Database\\Seeders\\Page_V3\\'.str_replace('/', '\\', $family).'\\'.$basename
        || !preg_match('/^[a-z0-9-]+$/D', $definition['slug'] ?? '')) {
        throw new RuntimeException('M69 exact read-only neighbor identity differs.');
    }
    $sourceHashes[$path] = hash('sha256', $bytes);
    return $definition;
};
$categoryDefinitions = []; $neighborDefinitions = [];
foreach ($neighborSources as [$family, $basename, $kind]) {
    if ($kind !== 'category') { continue; }
    $definition = $readNeighbor($family, $basename, $kind);
    $category = $definition['category'] ?? [];
    if (($category['slug'] ?? null) !== $definition['slug'] || ($category['language'] ?? null) !== 'uk'
        || ($category['type'] ?? null) !== 'theory') { throw new RuntimeException('M69 neighbor category source differs.'); }
    $categoryDefinitions[$definition['slug']] = $definition;
    $neighborDefinitions[$definition['seeder']['class']] = $definition;
}
$ancestryFromSources = static function (string $slug) use ($categoryDefinitions): array {
    $chain = []; $seen = [];
    do {
        if (isset($seen[$slug]) || !isset($categoryDefinitions[$slug])) { throw new RuntimeException('M69 finite neighbor category chain is missing/cyclic.'); }
        $seen[$slug] = true; array_unshift($chain, $slug);
        $slug = $categoryDefinitions[$slug]['category']['parent_slug'] ?? null;
    } while ($slug !== null);
    return $chain;
};
$readOnly(static function () use ($db, $neighborSources, $readNeighbor, $categoryDefinitions, $neighborDefinitions, $ancestryFromSources, $neighborResolver, $routePath, &$routes): void {
    foreach ($neighborSources as [$family, $basename, $kind]) {
        $identity = 'Database\\Seeders\\Page_V3\\'.str_replace('/', '\\', $family).'\\'.$basename;
        $definition = $kind === 'category' ? $neighborDefinitions[$identity] : $readNeighbor($family, $basename, $kind);
        $categorySlug = $kind === 'category' ? $definition['slug'] : ($definition['page']['category']['slug'] ?? '');
        $ancestry = $ancestryFromSources($categorySlug); $categoryPath = implode('/', $ancestry);
        $category = App\Models\PageCategory::query()->where('slug', $categorySlug)->where('language', 'uk')->where('type', 'theory')->sole();
        if ($neighborResolver->categoryPath($category) !== $categoryPath) { throw new RuntimeException('M69 neighbor actual/source category ancestry differs.'); }
        if ($kind === 'category') {
            $entry = $routePath('theory.category', ['category' => $categorySlug]); $expected = '/theory/'.$categorySlug;
        } else {
            $page = App\Models\Page::query()->with('category')->where('seeder', $definition['seeder']['class'])->sole();
            if ($page->type !== 'theory' || $page->slug !== $definition['slug'] || (int) $page->page_category_id !== (int) $category->id
                || ($definition['page']['locale'] ?? null) !== 'uk') { throw new RuntimeException('M69 neighbor actual/source page owner differs.'); }
            $entry = $routePath('theory.show', ['categoryPath' => $categoryPath, 'pageSlug' => $definition['slug']]);
            $expected = '/theory/'.$categoryPath.'/'.$definition['slug'];
        }
        if ($entry['path'] !== $expected) { throw new RuntimeException('M69 exact resolved read-only neighbor URL differs.'); }
        $routes[$entry['path']] = $entry + ['kind' => 'neighbor-'.$kind, 'expected_count' => null];
    }
});
$loaderPath = 'app/Support/Database/JsonTestSeeder.php';
if (!is_file($root.'/'.$loaderPath) || is_link($root.'/'.$loaderPath) || !is_file($worktree.'/'.$loaderPath)
    || is_link($worktree.'/'.$loaderPath)) {
    throw new RuntimeException('M69 reviewed saved-question default source is missing or linked.');
}
$loaderRoot = file_get_contents($root.'/'.$loaderPath); $loaderWorktree = file_get_contents($worktree.'/'.$loaderPath);
// Existing ROOT-only localized source-text hunks are foreign work, not a reason
// to mirror or mutate this loader. Pin both full files independently and require
// byte parity of every saved-test/default/UUID method actually used here.
if (!hash_equals('6b6d0b7be2e970fe4792582e6fbf15b3bce65c9f7ca013304e6ff4ab551c0bea', hash('sha256', $loaderRoot))
    || !hash_equals('c6636cd29ab6ae78a2777d5e97e32b5fb967a868ba4fba6eb03a1b14bb86e356', hash('sha256', $loaderWorktree))) {
    throw new RuntimeException('M69 independently reviewed saved-question loader full source changed.');
}
$savedMethodRegion = static function (string $source): string {
    $first = '    protected function syncSavedTestFromDefinition(';
    $last = '    protected function explanationLanguagesForLocale(';
    if (substr_count($source, $first) !== 1 || substr_count($source, $last) !== 1) {
        throw new RuntimeException('M69 reviewed complete saved-question method boundaries differ.');
    }
    $start = strpos($source, $first); $end = strpos($source, $last, $start);
    if ($end === false || $end <= $start) { throw new RuntimeException('M69 reviewed saved-question method order differs.'); }
    return substr($source, $start, $end - $start);
};
$rootMethods = $savedMethodRegion($loaderRoot); $worktreeMethods = $savedMethodRegion($loaderWorktree);
if (strlen($rootMethods) !== 5167 || $rootMethods !== $worktreeMethods
    || !hash_equals('76a2d46d9aeef776b6d9a47804f77357c227a4f88173b658983525a1a4d3d2a4', hash('sha256', $rootMethods))) {
    throw new RuntimeException('M69 reviewed saved-question complete method region differs.');
}
$sourceHashes[$loaderPath] = hash('sha256', $loaderRoot);
$sourceHashes['@source-worktree/'.$loaderPath] = hash('sha256', $loaderWorktree);
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
        if ($nodes->length !== 1) { throw new RuntimeException('M69 neighbor has no unique actual learning boundary.'); }
        $content = $dom->saveHTML($nodes->item(0));
        if ($content === '' || preg_match('/(?:name=["\']_token["\']|csrf-token)/i', $content)) {
            throw new RuntimeException('M69 neighbor boundary is empty or contains a volatile/security field.');
        }
        $entry['facts']['content_dom'] = $content;
        $entry['facts']['content_dom_sha256'] = hash('sha256', $content);
        $entry['facts']['comparison_policy'] = 'Exact actual category/M64/M65/M66/M67/Adjectives/advanced/Used to-Would/Passive Gerund-Infinitive learning DOM; no auth chrome.';
    } else {
PHP;

$oldSourceGuard = '~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\.json)$~D';
$finiteSourceGuard = '~^database/seeders/V3/(?:TheoryLinks/data/(?:adjectives-vs-adverbs|adjectives-order-of-adjectives|adjectives-ed-ing-adjectives)-theory-links\.json|Polyglot/(?:PolyglotAdjectivesVsAdverbsAllLevelsLessonSeeder|PolyglotOrderOfAdjectivesAllLevelsLessonSeeder|PolyglotEdIngAdjectivesAllLevelsLessonSeeder)/definition\.json|(?:AdjectivesVsAdverbsAllLevelsV3Seeder|OrderOfAdjectivesAllLevelsV3Seeder|EdIngAdjectivesAllLevelsV3Seeder)/definition\.json)$~D';
$oldClassGuard = <<<'PHP'
        if (!preg_match('/^Database\\\\Seeders\\\\V3\\\\(?:Polyglot|PassiveVoice)\\\\[A-Za-z0-9]+$/D', $class)) {
PHP;
$finiteClassGuard = <<<'PHP'
        if (!in_array($class, $expectedBanks, true)) {
PHP;
$oldSavedDeclaration = <<<'PHP'
        $definition = $readSource($relative); $saved = $definition['saved_test'] ?? [];
PHP;
$savedDeclaration = <<<'PHP'
        $definition = $readSource($relative); $saved = $definition['saved_test'] ?? [];
        // Exact six modern bank definitions omit question_uuids. The current
        // JsonTestSeeder::normalizeSavedTestQuestionUuids returns source order
        // in this case. Expected saved links are still compared in full below.
        $sourceOrderDefaults = [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotAdjectivesVsAdverbsAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\AdjectivesVsAdverbsAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotOrderOfAdjectivesAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\OrderOfAdjectivesAllLevelsV3Seeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotEdIngAdjectivesAllLevelsLessonSeeder',
            'Database\\Seeders\\V3\\EdIngAdjectivesAllLevelsV3Seeder',
        ];
        if (in_array($class, $sourceOrderDefaults, true)) {
            if (array_key_exists('question_uuids', $saved)) {
                throw new RuntimeException('M69 reviewed modern source-order default no longer applies.');
            }
            $saved['question_uuids'] = array_column($definition['questions'], 'uuid');
        }
PHP;

runFrozenReadOnlyProfile('m52-old-consumers.php', '1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775', [
    ['M52', 'M69', 43], ['m52', 'm69', 4],
    ["\$workingBeforeName = 'before-v1.json';", "\$workingBeforeName = 'before-v2.json';", 1],
    ['passive-voice-formation-rules-theory-links.json', 'adjectives-vs-adverbs-theory-links.json', 1],
    ['passive-voice-negatives-questions-theory-links.json', 'adjectives-order-of-adjectives-theory-links.json', 1],
    ['passive-voice-modal-verbs-theory-links.json', 'adjectives-ed-ing-adjectives-theory-links.json', 1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff',
        '3fbcc654435119c5607d0ede041bead091bce4527efadde6a2b1bef661de4d62', 1],

    [$oldSourceGuard, $finiteSourceGuard, 1],
    [$oldClassGuard, $finiteClassGuard, 1],
    [$oldSavedDeclaration, $savedDeclaration, 1],
    [$oldManifestLoop, $manifestLoop, 1],
    [$oldPageGuard, $pageGuard, 1],
    ["    \$pages[\$page['page_seeder_class']] = \$page;",
        "    \$page['category_path'] = implode('/', \$ancestry);\n    \$page['category_slug'] = end(\$ancestry);\n    \$pages[\$page['page_seeder_class']] = \$page;", 1],
    [$oldPageLoop, $pageLoop, 1], [$oldPageCheck, $pageCheck, 1],
    ["'categoryPath' => \$source['category_slug']", "'categoryPath' => \$source['category_path']", 1],
    ['~^/(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+$~D',
        '~^/(?:(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+|theory/(?:(?:clauses-and-linking-words|verb-patterns|prykmetniky-ta-pryslinknyky)(?:/[a-z0-9-]+)?|tenses(?:/used-to-would)?|passive-voice-infinitives-gerund|passive-voice(?:/passive-voice-infinitives-gerund(?:/theory-passive-voice-passive-(?:gerund|infinitive))?)?))$~D', 1],
    ["throw new RuntimeException('M69 source-derived URI resolves to another route or an admin gate.');",
        "throw new RuntimeException('M69 source-derived URI resolves to another route or an admin gate: '.\$routeName.' '.\$uri);", 1],
    ["ksort(\$routes);", $neighborRoutes, 1], [$oldHttpBranch, $httpBranch, 1],
    ["throw new RuntimeException('M69 anonymous consumer loopback GET failed or changed target.');",
        "throw new RuntimeException('M69 anonymous consumer loopback GET failed or changed target: '.\$path);", 1],
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
