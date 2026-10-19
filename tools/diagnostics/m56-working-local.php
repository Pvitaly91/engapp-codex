<?php

/** M56 exact three-owner BEFORE profile; the immutable M52 collector stays unchanged. */
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
    ['basename' => 'PassiveVoicePassiveGerundTheorySeeder', 'folder' => 'InfinitivesGerund',
        'slug' => 'theory-passive-voice-passive-gerund',
        'ancestry' => ['passive-voice', 'passive-voice-infinitives-gerund'],
        'level' => 'B2', 'continuing_blocks' => 10,
        'levels' => ['B2', 'B2', 'B2', 'C1', 'B2', 'B2', 'B2', 'B2', 'B2', null]],
    ['basename' => 'PassiveVoicePresentSimpleTheorySeeder', 'folder' => 'Tenses',
        'slug' => 'theory-passive-voice-present-simple', 'ancestry' => ['passive-voice'],
        'level' => 'A2', 'continuing_blocks' => 9,
        'levels' => ['A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2']],
    ['basename' => 'PassiveVoicePastSimpleTheorySeeder', 'folder' => 'Tenses',
        'slug' => 'theory-passive-voice-past-simple', 'ancestry' => ['passive-voice'],
        'level' => 'A2', 'continuing_blocks' => 10,
        'levels' => ['A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2', 'A2']],
];
PHP;
runFrozenReadOnlyProfile('m52-working-local.php', '9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d', [
    ['M52', 'M56', 3], ['m52', 'm56', 5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7', 'fff71b7daa371497b3df100c2d97d108058dc175', 1],
    [$oldBindings, $bindings, 1],
    ["'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',",
        "'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',\n    'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryCourseManifestService.php',", 1],
    ["\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\Basics\\\\'.\$binding['basename'];",
        "\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\'.\$binding['folder'].'\\\\'.\$binding['basename'];", 1],
    ["\$path = 'database/seeders/Page_V3/PassiveVoice/Basics/'.\$binding['basename'].'/definition.json';",
        "\$path = 'database/seeders/Page_V3/PassiveVoice/'.\$binding['folder'].'/'.\$binding['basename'].'/definition.json';", 1],
    ["\$definition['page']['category']['slug'] !== 'passive-voice'",
        "\$definition['page']['category']['slug'] !== end(\$binding['ancestry'])", 1],
    ["array_column(\$chain, 'slug') !== ['passive-voice']",
        "array_column(\$chain, 'slug') !== \$binding['ancestry']", 1],
    ["(\$config['level'] ?? null) !== \$binding['level']",
        "(\$config['level'] ?? null) !== (\$binding['levels'][\$position - 1] ?? null)", 1],
    ["\$categoryPath !== 'passive-voice'",
        "\$categoryPath !== implode('/', \$binding['ancestry'])", 1],
    ["~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D",
        "~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/(?:passive-voice-infinitives-gerund/theory-passive-voice-passive-gerund|theory-passive-voice-(?:present-simple|past-simple))$~D", 1],
]);
