<?php

/** M63 exact single-category source/DB/HTTP BEFORE profile; no active draft discovery. */
require __DIR__.'/frozen-readonly-profile.php';

$oldBindings = <<<'PHP'
$bindings = [
    ['basename' => 'PassiveVoiceFormationRulesTheorySeeder', 'slug' => 'theory-passive-voice-formation-rules',
        'level' => 'A2', 'continuing_blocks' => 9],
    ['basename' => 'PassiveVoiceNegativesQuestionsTheorySeeder', 'slug' => 'theory-passive-voice-negatives-questions',
        'level' => 'B1', 'continuing_blocks' => 11],
    ['basename' => 'PassiveVoiceModalVerbsTheorySeeder', 'slug' => 'theory-passive-voice-modal-verbs',
        'level' => 'B1', 'continuing_blocks' => 11],
];
PHP;
$bindings = <<<'PHP'
$bindings = [
    ['family' => 'ClausesAndLinkingWords', 'category' => 'clauses-and-linking-words',
        'basename' => 'ClausesAndLinkingWordsNonDefiningRelativeClausesTheorySeeder', 'slug' => 'non-defining-relative-clauses',
        'level' => 'B1', 'continuing_blocks' => 7, 'levels' => ['B1', null, null, null, null, null, null]],
    ['family' => 'ClausesAndLinkingWords', 'category' => 'clauses-and-linking-words',
        'basename' => 'ClausesAndLinkingWordsOmittingRelativePronounsTheorySeeder', 'slug' => 'omitting-relative-pronouns',
        'level' => 'B1', 'continuing_blocks' => 7, 'levels' => ['B1', null, null, null, null, null, null]],
    ['family' => 'ClausesAndLinkingWords', 'category' => 'clauses-and-linking-words',
        'basename' => 'ClausesAndLinkingWordsLinkingWordsTheorySeeder', 'slug' => 'linking-words',
        'level' => 'B1', 'continuing_blocks' => 7, 'levels' => ['B1', null, null, null, null, null, null]],
];
PHP;
$schemaLoop = <<<'PHP'
    foreach (['text_blocks', 'questions', 'question_answers', 'question_options', 'question_option_question',
PHP;
$categoryCapture = <<<'PHP'
    // All-locale category learning rows are read-only consumers, never rename targets.
    $capture['read_only_categories'] = [];
    foreach ([['ClausesAndLinkingWords', 'ClausesAndLinkingWordsCategorySeeder', 'clauses-and-linking-words', "Підрядні речення та слова-зв'язки", 'Clauses and linking words']] as [$family, $basename, $slug, $title, $actualTitle]) {
        $path = 'database/seeders/Page_V3/'.$family.'/'.$basename.'/definition.json';
        $sha = $fileHash($root.'/'.$path); $fileHash($worktree.'/'.$path);
        $bytes = file_get_contents($root.'/'.$path);
        if ($bytes !== file_get_contents($worktree.'/'.$path)) { throw new RuntimeException('M63 read-only category source parity differs.'); }
        $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if (($definition['content_type'] ?? null) !== 'category'
            || ($definition['seeder']['class'] ?? null) !== 'Database\\Seeders\\Page_V3\\'.$family.'\\'.$basename
            || ($definition['category']['slug'] ?? null) !== $slug
            || ($definition['category']['title'] ?? null) !== $title
            || ($definition['category']['language'] ?? null) !== 'uk') { throw new RuntimeException('M63 exact read-only category source identity differs.'); }
        $categories = $rows($db->table('page_categories')->where('slug', $slug)->where('language', 'uk')->where('type', 'theory')->orderBy('id'));
        if (count($categories) !== 1 || $categories[0]['title'] !== $actualTitle) { throw new RuntimeException('M63 independently observed read-only actual category differs.'); }
        $descriptions = $rows($db->table('text_blocks')->where('page_category_id', $categories[0]['id'])->whereNull('page_id')->orderBy('id'));
        $capture['read_only_categories'][$slug] = ['source_path' => $path, 'source_sha256' => $sha,
            'source_definition' => $definition, 'category_row' => $categories[0], 'description_rows' => $descriptions,
            'category_tags' => $orderedRows('page_category_tag', $db->table('page_category_tag')->where('page_category_id', $categories[0]['id'])),
            'description_tags' => $orderedRows('tag_text_block', $db->table('tag_text_block')->whereIn('text_block_id', array_column($descriptions, 'id'))),
            'write_policy' => 'Read-only category and all-locale description evidence; no category rename or description writes.'];
    }
PHP;

runFrozenReadOnlyProfile('m52-working-local.php', '9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d', [
    ['M52', 'M63', 3], ['m52', 'm63', 5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7', 'c8502bc75b9d4258d79824a9be0b343ffbfc07f4', 1],
    [$oldBindings, $bindings, 1],
    ["'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',",
        "'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',\n    'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',\n    'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',\n    'app/Support/M59BuilderSelectionPresentation.php', 'app/Support/M60ReportedSpeechBasicsPackage.php',\n    'app/Support/M60BuilderSelectionPresentation.php', 'app/Services/M60PracticeFeedbackCorrectionPatch.php',\n    'app/Support/M61ReportedSpeechChangesPackage.php', 'app/Support/M61BuilderSelectionPresentation.php',\n    'app/Services/M61ContentPatch.php', 'app/Services/M61LocalTargetGuard.php',\n    'app/Console/Commands/PatchM61Content.php',\n    'app/Support/M62ReportedReviewRelativeBasicsPackage.php', 'app/Support/M62BuilderSelectionPresentation.php',\n    'app/Services/M62ContentPatch.php', 'app/Services/M62LocalTargetGuard.php', 'app/Console/Commands/PatchM62Content.php', 'app/Services/TheoryCourseManifestService.php',", 1],
    ["\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\Basics\\\\'.\$binding['basename'];",
        "\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\'.\$binding['family'].'\\\\'.\$binding['basename'];", 1],
    ["\$path = 'database/seeders/Page_V3/PassiveVoice/Basics/'.\$binding['basename'].'/definition.json';",
        "\$path = 'database/seeders/Page_V3/'.\$binding['family'].'/'.\$binding['basename'].'/definition.json';", 1],
    ["\$definition['page']['category']['slug'] !== 'passive-voice'", "\$definition['page']['category']['slug'] !== \$binding['category']", 1],
    ["array_column(\$chain, 'slug') !== ['passive-voice']", "array_column(\$chain, 'slug') !== [\$binding['category']]", 1],
    ["\$config['type'] !== 'subtitle' && \$config['type'] !== 'navigation-chips' && (\$config['level'] ?? null) !== \$binding['level']",
        "\$position > 0 && (\$config['level'] ?? null) !== \$binding['levels'][\$position - 1]", 1],
    ["\$categoryPath !== 'passive-voice'", "\$categoryPath !== \$binding['category']", 1],
    ['~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D',
        '~^/(?:theory|courses/english-grammar-theory/lesson)/clauses-and-linking-words/(?:non-defining-relative-clauses|omitting-relative-pronouns|linking-words)$~D', 1],
    ["(\$a['marker'] <=> \$b['marker'])", "strnatcmp((string) \$a['marker'], (string) \$b['marker'])", 1],
    ["    \$snapshot['ordered_answer_instances'] = array_map",
        "    \$snapshot['theory_links'] = \$orderedRows('question_theory_text_blocks', \$db->table('question_theory_text_blocks')->where('question_uuid', \$question['uuid']));\n    \$snapshot['ordered_answer_instances'] = array_map", 1],
    ["    \$bindings, \$rows, \$fileHash, \$relations, \$resolver, \$routePath): void {",
        "    \$bindings, \$rows, \$orderedRows, \$fileHash, \$relations, \$resolver, \$routePath): void {", 1],
    [$schemaLoop, $categoryCapture."\n".$schemaLoop, 1],
]);
