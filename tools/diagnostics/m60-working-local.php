<?php

/** M60 exact Reported Speech BEFORE profile; no draft release discovery. */
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
    ['basename' => 'ReportedSpeechStatementsTheorySeeder', 'slug' => 'reported-statements',
        'level' => 'B1', 'continuing_blocks' => 6, 'levels' => ['B1', null, null, null, null, null]],
    ['basename' => 'ReportedSpeechQuestionsTheorySeeder', 'slug' => 'reported-questions',
        'level' => 'B1', 'continuing_blocks' => 6, 'levels' => ['B1', null, null, null, null, null]],
    ['basename' => 'ReportedSpeechCommandsRequestsTheorySeeder', 'slug' => 'reported-commands-and-requests',
        'level' => 'B1', 'continuing_blocks' => 6, 'levels' => ['B1', null, null, null, null, null]],
];
PHP;
$schemaLoop = <<<'PHP'
    foreach (['text_blocks', 'questions', 'question_answers', 'question_options', 'question_option_question',
PHP;
$categoryCapture = <<<'PHP'
    // Read-only category evidence: never use embedded English page.category.title to rename it.
    $categoryPath = 'database/seeders/Page_V3/ReportedSpeech/ReportedSpeechCategorySeeder/definition.json';
    $categorySha = $fileHash($root.'/'.$categoryPath); $fileHash($worktree.'/'.$categoryPath);
    $categoryBytes = file_get_contents($root.'/'.$categoryPath);
    if ($categoryBytes !== file_get_contents($worktree.'/'.$categoryPath)) {
        throw new RuntimeException('Read-only Reported Speech category source ROOT/WT parity differs.');
    }
    $categoryDefinition = json_decode($categoryBytes, true, flags: JSON_THROW_ON_ERROR);
    if (($categoryDefinition['content_type'] ?? null) !== 'category'
        || ($categoryDefinition['seeder']['class'] ?? null) !== 'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechCategorySeeder'
        || ($categoryDefinition['category']['slug'] ?? null) !== 'reported-speech'
        || ($categoryDefinition['category']['title'] ?? null) !== 'Непряма мова'
        || ($categoryDefinition['category']['language'] ?? null) !== 'uk') {
        throw new RuntimeException('Read-only Reported Speech category source identity differs.');
    }
    $categories = $rows($db->table('page_categories')->where('slug', 'reported-speech')
        ->where('language', 'uk')->where('type', 'theory')->orderBy('id'));
    if (count($categories) !== 1) { throw new RuntimeException('Read-only Reported Speech category is missing or ambiguous.'); }
    foreach ($capture['pages'] as $pageRecord) {
        if ($pageRecord['page']['page_category_id'] !== $categories[0]['id']) {
            throw new RuntimeException('Reported Speech page/category exact binding differs.');
        }
    }
    $descriptionRows = $rows($db->table('text_blocks')->where('page_category_id', $categories[0]['id'])
        ->whereNull('page_id')->orderBy('id'));
    $capture['read_only_category'] = ['source_path' => $categoryPath, 'source_sha256' => $categorySha,
        'source_definition' => $categoryDefinition, 'category_row' => $categories[0], 'description_rows' => $descriptionRows,
        'category_tags' => $orderedRows('page_category_tag', $db->table('page_category_tag')->where('page_category_id', $categories[0]['id'])),
        'description_tags' => $orderedRows('tag_text_block', $db->table('tag_text_block')->whereIn('text_block_id', array_column($descriptionRows, 'id'))),
        'write_policy' => 'Read-only source/actual category and all-locale description evidence; no category rename, reseed or description update.'];
PHP;

runFrozenReadOnlyProfile('m52-working-local.php', '9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d', [
    ['M52', 'M60', 3], ['m52', 'm60', 5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7', '69994e34da410c66d6807a0921b588f85b81908c', 1],
    [$oldBindings, $bindings, 1],
    ["'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',",
        "'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',\n    'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',\n    'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',\n    'app/Support/M59BuilderSelectionPresentation.php', 'app/Services/TheoryCourseManifestService.php',", 1],
    ["\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\Basics\\\\'.\$binding['basename'];",
        "\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\ReportedSpeech\\\\'.\$binding['basename'];", 1],
    ["\$path = 'database/seeders/Page_V3/PassiveVoice/Basics/'.\$binding['basename'].'/definition.json';",
        "\$path = 'database/seeders/Page_V3/ReportedSpeech/'.\$binding['basename'].'/definition.json';", 1],
    ["\$definition['page']['category']['slug'] !== 'passive-voice'",
        "\$definition['page']['category']['slug'] !== 'reported-speech'", 1],
    ["array_column(\$chain, 'slug') !== ['passive-voice']",
        "array_column(\$chain, 'slug') !== ['reported-speech']", 1],
    ["\$config['type'] !== 'subtitle' && \$config['type'] !== 'navigation-chips' && (\$config['level'] ?? null) !== \$binding['level']",
        "\$position > 0 && (\$config['level'] ?? null) !== \$binding['levels'][\$position - 1]", 1],
    ["\$categoryPath !== 'passive-voice'", "\$categoryPath !== 'reported-speech'", 1],
    ['~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D',
        '~^/(?:theory|courses/english-grammar-theory/lesson)/reported-speech/reported-(?:statements|questions|commands-and-requests)$~D', 1],
    ["(\$a['marker'] <=> \$b['marker'])", "strnatcmp((string) \$a['marker'], (string) \$b['marker'])", 1],
    ["    \$snapshot['ordered_answer_instances'] = array_map",
        "    \$snapshot['theory_links'] = \$orderedRows('question_theory_text_blocks', \$db->table('question_theory_text_blocks')->where('question_uuid', \$question['uuid']));\n    \$snapshot['ordered_answer_instances'] = array_map", 1],
    ["    \$bindings, \$rows, \$fileHash, \$relations, \$resolver, \$routePath): void {",
        "    \$bindings, \$rows, \$orderedRows, \$fileHash, \$relations, \$resolver, \$routePath): void {", 1],
    [$schemaLoop, $categoryCapture."\n".$schemaLoop, 1],
]);
