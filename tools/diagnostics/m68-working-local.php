<?php

/** M68 exact mixed-category/level source/DB/HTTP BEFORE profile; no active draft discovery. */
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
    ['family' => 'Adjectives', 'category' => 'prykmetniky-ta-pryslinknyky',
        'basename' => 'AdjectivesDegreesOfComparisonTheorySeeder', 'slug' => 'theory-degrees-of-comparison',
        'level' => 'A2', 'continuing_blocks' => 9, 'levels' => [null, null, null, null, null, null, null, null, null]],
    ['family' => 'Adjectives', 'category' => 'prykmetniky-ta-pryslinknyky',
        'basename' => 'AdjectivesComparativeVsSuperlativeTheorySeeder', 'slug' => 'comparative-vs-superlative',
        'level' => 'A2', 'continuing_blocks' => 11, 'levels' => ['A2', 'A2', 'A2', 'A2', 'A2', 'B1', 'B1', 'B2', 'A2', 'B1', 'A1']],
    ['family' => 'Adjectives', 'category' => 'prykmetniky-ta-pryslinknyky',
        'basename' => 'AdjectivesEqualityComparisonAsAsNotAsAsTheorySeeder', 'slug' => 'equality-comparison-asas-not-asas',
        'level' => 'A2', 'continuing_blocks' => 12, 'levels' => ['A2', 'A2', 'A2', 'A2', 'B1', 'B1', 'A2', 'B1', 'B2', 'A2', 'B1', 'A1']],
];
PHP;
$schemaLoop = <<<'PHP'
    foreach (['text_blocks', 'questions', 'question_answers', 'question_options', 'question_option_question',
PHP;
$categoryCapture = <<<'PHP'
    // Preserve accepted M63 final technical evidence independently of teaching inventories.
    $capture['preserved_m63_evidence'] = [];
    foreach ([
        'source-sync-v2.json' => '2207dd087d477446c2b76a3210015343c65c0a74d87aa889892cc8e549a77577',
        'final-source-lineage-v1.json' => '6e4d8778acb25e146d1da61fa5c5f960db221565909efc0d63915aa3fb026d7f',
    ] as $basename => $expected) {
        $actual = $fileHash($root.'/storage/app/seo-m63-local/'.$basename);
        if (!hash_equals($expected, $actual)) { throw new RuntimeException('M68 accepted M63 evidence differs.'); }
        $capture['preserved_m63_evidence'][$basename] = $actual;
    }
    // The accepted M64 final technical evidence remains an immutable precondition.
    $capture['preserved_m64_evidence'] = [];
    foreach ([
        'source-sync-v2.json' => 'bd75a927c3db82cccf41325d64f4d4bdc3d0b5b88edbdae4e1f3dc2034fefb7e',
        'final-readonly-v3.json' => 'f8d9eda7eed7dca67b58fc91dc1dfa30f6757344feb8f0b24067b711b8e088e3',
    ] as $basename => $expected) {
        $actual = $fileHash($root.'/storage/app/seo-m64-local/'.$basename);
        if (!hash_equals($expected, $actual)) { throw new RuntimeException('M68 accepted M64 evidence differs.'); }
        $capture['preserved_m64_evidence'][$basename] = $actual;
    }
    // Accepted M65 teaching 1.0.0 and technical EOF 1.0.1 final evidence are immutable.
    $capture['preserved_m65_evidence'] = [];
    foreach ([
        'source-sync-v2.json' => 'a5a55455dc2cce1a20c36efa502e9d60c17f936260640f7b2f5669898e57edef',
        'final-readonly-v6.json' => '5cc4a85ce90d1242c10634dcf8775d1745455089608833fbe40ef957968034aa',
    ] as $basename => $expected) {
        $actual = $fileHash($root.'/storage/app/seo-m65-local/'.$basename);
        if (!hash_equals($expected, $actual)) { throw new RuntimeException('M68 accepted M65 evidence differs.'); }
        $capture['preserved_m65_evidence'][$basename] = $actual;
    }
    // Accepted final M66 1.0.2 evidence; NEVER APPLY aggregate is not used as a writer plan.
    $capture['preserved_m66_evidence'] = [];
    foreach ([
        'source-sync-v3.json' => 'fd7f8553a9b7dbe4de7dfb867f3464e0d3055407b71a41790194d3c0ea09d855',
        'final-readonly-v4.json' => 'b4ad237a46c655f71f1a804887571c1f8f12bbe97f07fe86e2aa8a320113abf2',
        'registry-sentence-builders-v2.json' => '76059150ab4b6bf3fefa4200697dd1b08a26a5b4abbb9b753f156a077b8039c2',
    ] as $basename => $expected) {
        $actual = $fileHash($root.'/storage/app/seo-m66-local/'.$basename);
        if (!hash_equals($expected, $actual)) { throw new RuntimeException('M68 accepted M66 final evidence differs.'); }
        $capture['preserved_m66_evidence'][$basename] = $actual;
    }
    // Accepted M67 teaching/runtime evidence is immutable; these are not fresh M68 proofs.
    $capture['preserved_m67_evidence'] = [];
    foreach ([
        'source-sync-v1.json' => 'a5609584b8ce72e4c5ada092b9a660ac6fbc86fcefd73e9e2ec7950c76da21e3',
        'final-readonly-v2.json' => 'd9268b914ed9273ff88abfe88d674c330b529f0601d21a9a3dc2e3fb03e7393e',
        'registry-sentence-builders-v1.json' => '8f84ef307a963d561655a5038ce5380dba19667d1e395c7c9101358a7bab9bcd',
    ] as $basename => $expected) {
        $actual = $fileHash($root.'/storage/app/seo-m67-local/'.$basename);
        if (!hash_equals($expected, $actual)) { throw new RuntimeException('M68 accepted M67 final evidence differs.'); }
        $capture['preserved_m67_evidence'][$basename] = $actual;
    }
    // All-locale category learning rows are read-only consumers, never rename targets.
    $capture['read_only_categories'] = [];
    foreach ([
        ['Adjectives', 'AdjectivesCategorySeeder', 'prykmetniky-ta-pryslinknyky', 'Прикметники та прислівники'],
    ] as [$family, $basename, $slug, $title]) {
        $path = 'database/seeders/Page_V3/'.$family.'/'.$basename.'/definition.json';
        $sha = $fileHash($root.'/'.$path); $fileHash($worktree.'/'.$path);
        $bytes = file_get_contents($root.'/'.$path);
        if ($bytes !== file_get_contents($worktree.'/'.$path)) { throw new RuntimeException('M68 read-only category source parity differs.'); }
        $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if (($definition['content_type'] ?? null) !== 'category'
            || ($definition['seeder']['class'] ?? null) !== 'Database\\Seeders\\Page_V3\\'.$family.'\\'.$basename
            || ($definition['category']['slug'] ?? null) !== $slug
            || ($definition['category']['title'] ?? null) !== $title
            || ($definition['category']['language'] ?? null) !== 'uk') { throw new RuntimeException('M68 exact read-only category source identity differs.'); }
        $categories = $rows($db->table('page_categories')->where('slug', $slug)->where('language', 'uk')->where('type', 'theory')->orderBy('id'));
        if (count($categories) !== 1) { throw new RuntimeException('M68 independently observed read-only actual category differs.'); }
        $descriptions = $rows($db->table('text_blocks')->where('page_category_id', $categories[0]['id'])->whereNull('page_id')->orderBy('id'));
        $capture['read_only_categories'][$slug] = ['source_path' => $path, 'source_sha256' => $sha,
            'source_definition' => $definition, 'category_row' => $categories[0], 'description_rows' => $descriptions,
            'category_tags' => $orderedRows('page_category_tag', $db->table('page_category_tag')->where('page_category_id', $categories[0]['id'])),
            'description_tags' => $orderedRows('tag_text_block', $db->table('tag_text_block')->whereIn('text_block_id', array_column($descriptions, 'id'))),
            'write_policy' => 'Read-only category and all-locale description evidence; no category rename or description writes.'];
    }
PHP;

runFrozenReadOnlyProfile('m52-working-local.php', '9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d', [
    ['M52', 'M68', 3], ['m52', 'm68', 5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7', '2161cb0b03afd203933ad022ccb4e77e8e8ca363', 1],
    [$oldBindings, $bindings, 1],
    ["'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',",
        "'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',\n    'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',\n    'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',\n    'app/Support/M59BuilderSelectionPresentation.php', 'app/Support/M60ReportedSpeechBasicsPackage.php',\n    'app/Support/M60BuilderSelectionPresentation.php', 'app/Services/M60PracticeFeedbackCorrectionPatch.php',\n    'app/Support/M61ReportedSpeechChangesPackage.php', 'app/Support/M61BuilderSelectionPresentation.php',\n    'app/Services/M61ContentPatch.php', 'app/Services/M61LocalTargetGuard.php',\n    'app/Console/Commands/PatchM61Content.php',\n    'app/Support/M62ReportedReviewRelativeBasicsPackage.php', 'app/Support/M62BuilderSelectionPresentation.php',\n    'app/Services/M62ContentPatch.php', 'app/Services/M62LocalTargetGuard.php', 'app/Console/Commands/PatchM62Content.php',\n    'app/Support/M63RelativeCompletionLinkingPackage.php', 'app/Support/M63BuilderSelectionPresentation.php',\n    'app/Services/M63ContentPatch.php', 'app/Services/M63LocalTargetGuard.php', 'app/Console/Commands/PatchM63Content.php', 'app/Services/TheoryCourseManifestService.php',", 1],
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
        '~^/(?:theory|courses/english-grammar-theory/lesson)/prykmetniky-ta-pryslinknyky/(?:theory-degrees-of-comparison|comparative-vs-superlative|equality-comparison-asas-not-asas)$~D', 1],
    ["(\$a['marker'] <=> \$b['marker'])", "strnatcmp((string) \$a['marker'], (string) \$b['marker'])", 1],
    ["    \$snapshot['ordered_answer_instances'] = array_map",
        "    \$snapshot['theory_links'] = \$orderedRows('question_theory_text_blocks', \$db->table('question_theory_text_blocks')->where('question_uuid', \$question['uuid']));\n    \$snapshot['ordered_answer_instances'] = array_map", 1],
    ["    \$bindings, \$rows, \$fileHash, \$relations, \$resolver, \$routePath): void {",
        "    \$bindings, \$rows, \$orderedRows, \$fileHash, \$relations, \$resolver, \$routePath): void {", 1],
    [$schemaLoop, $categoryCapture."\n".$schemaLoop, 1],

    ["'technical_level' => \$binding['level']", "'planned_new_practice_level' => \$binding['level']", 1],
]);
