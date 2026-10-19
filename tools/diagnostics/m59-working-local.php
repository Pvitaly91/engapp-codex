<?php

/** M59 exact mixed-ancestry BEFORE; immutable M52 read-only capture procedure. */
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
    ['basename' => 'PassiveVoiceFutureContinuousTheorySeeder', 'folder' => 'Tenses/',
        'slug' => 'theory-passive-voice-future-continuous',
        'ancestry' => ['passive-voice', 'passive-voice-tenses'],
        'level' => 'B2', 'continuing_blocks' => 11,
        'levels' => ['B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', null]],
    ['basename' => 'PassiveVoiceByPhraseTheorySeeder', 'folder' => 'SpecialCases/',
        'slug' => 'theory-passive-voice-by-phrase', 'ancestry' => ['passive-voice'],
        'level' => 'B1', 'continuing_blocks' => 8,
        'levels' => ['B1', 'B1', 'B1', 'B2', 'B1', 'B1', 'B1', null]],
    ['basename' => 'AdvancedPassiveVoiceTheorySeeder', 'folder' => '',
        'slug' => 'advanced-passive-voice', 'ancestry' => ['passive-voice'],
        'level' => 'B2', 'continuing_blocks' => 2, 'levels' => ['B2', null]],
];
PHP;
runFrozenReadOnlyProfile('m52-working-local.php', '9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d', [
    ['M52', 'M59', 3], ['m52', 'm59', 5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7', '5c314ccfedaba52934cdfd7cae3aef3ce7f1aa89', 1],
    [$oldBindings, $bindings, 1],
    ["'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',",
        "'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',\n    'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',\n    'app/Services/TheoryCourseManifestService.php',", 1],
    ["\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\Basics\\\\'.\$binding['basename'];",
        "\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\'.str_replace('/', '\\\\', \$binding['folder']).\$binding['basename'];", 1],
    ["\$path = 'database/seeders/Page_V3/PassiveVoice/Basics/'.\$binding['basename'].'/definition.json';",
        "\$path = 'database/seeders/Page_V3/PassiveVoice/'.\$binding['folder'].\$binding['basename'].'/definition.json';", 1],
    ["\$definition['page']['category']['slug'] !== 'passive-voice'",
        "\$definition['page']['category']['slug'] !== end(\$binding['ancestry'])", 1],
    ["array_column(\$chain, 'slug') !== ['passive-voice']",
        "array_column(\$chain, 'slug') !== \$binding['ancestry']", 1],
    ["\$config['type'] !== 'subtitle' && \$config['type'] !== 'navigation-chips' && (\$config['level'] ?? null) !== \$binding['level']",
        "\$position > 0 && (\$config['level'] ?? null) !== (\$binding['levels'][\$position - 1] ?? null)", 1],
    ["\$categoryPath !== 'passive-voice'", "\$categoryPath !== implode('/', \$binding['ancestry'])", 1],
    ['~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D',
        '~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/(?:passive-voice-tenses/theory-passive-voice-future-continuous|theory-passive-voice-by-phrase|advanced-passive-voice)$~D', 1],
]);
