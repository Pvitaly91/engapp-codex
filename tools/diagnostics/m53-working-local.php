<?php

/** M53 finite profile of the unchanged, reviewed M52 read-only collector. */
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
    ['basename' => 'PassiveVoiceCausativeTheorySeeder', 'slug' => 'theory-passive-voice-causative',
        'level' => 'B1', 'continuing_blocks' => 10,
        'levels' => ['B1', 'B1', 'B1', 'B1', 'B1', 'B2', 'B1', 'B1', 'B1', null]],
    ['basename' => 'PassiveVoiceGetPassiveTheorySeeder', 'slug' => 'theory-passive-voice-get-passive',
        'level' => 'B1', 'continuing_blocks' => 9,
        'levels' => ['B1', 'B1', 'B1', 'B1', 'B2', 'B1', 'B1', 'B1', null]],
    ['basename' => 'PassiveVoiceImpersonalPassiveTheorySeeder', 'slug' => 'theory-passive-voice-impersonal-passive',
        'level' => 'B2', 'continuing_blocks' => 10,
        'levels' => ['B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', null]],
];
PHP;
runFrozenReadOnlyProfile('m52-working-local.php', '9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d', [
    ['M52', 'M53', 3], ['m52', 'm53', 5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7', 'ba489be880d9a7c2b3dbc1698e887a05eb166fbe', 1],
    [$oldBindings, $bindings, 1],
    ["(\$config['level'] ?? null) !== \$binding['level']", "(\$config['level'] ?? null) !== (\$binding['levels'][\$position - 1] ?? null)", 1],
]);
