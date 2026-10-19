<?php

/** M54 exact source/DB/HTTP BEFORE profile of the unchanged M52 collector. */
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
    ['basename' => 'PassiveVoicePhrasalVerbsTheorySeeder', 'slug' => 'theory-passive-voice-phrasal-verbs',
        'level' => 'B1', 'continuing_blocks' => 9,
        'levels' => ['B1', 'B1', 'B1', 'B1', 'B1', 'B2', 'B1', 'B1', null]],
    ['basename' => 'PassiveVoiceRestrictionsTheorySeeder', 'slug' => 'theory-passive-voice-restrictions',
        'level' => 'B1', 'continuing_blocks' => 10,
        'levels' => ['B1', 'B1', 'B1', 'B1', 'B2', 'B2', 'B2', 'B1', 'B1', null]],
    ['basename' => 'PassiveVoiceTwoObjectVerbsTheorySeeder', 'slug' => 'theory-passive-voice-two-object-verbs',
        'level' => 'B1', 'continuing_blocks' => 9,
        'levels' => ['B1', 'B1', 'B1', 'B1', 'B1', 'B2', 'B1', 'B1', null]],
];
PHP;
runFrozenReadOnlyProfile('m52-working-local.php', '9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d', [
    ['M52', 'M54', 3], ['m52', 'm54', 5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7', 'f4e1c0d94a590d630582e2bb613cbdc93654fc4a', 1],
    [$oldBindings, $bindings, 1],
    ["(\$config['level'] ?? null) !== \$binding['level']", "(\$config['level'] ?? null) !== (\$binding['levels'][\$position - 1] ?? null)", 1],
]);
