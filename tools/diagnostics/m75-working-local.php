<?php

/** Independent SELECT/HTTP BEFORE; no content writes or source projection. */
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
    ['basename'=>'PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder',
        'slug'=>'possessive-adjectives-vs-pronouns-my-mine-your-yours','level'=>null,'continuing_blocks'=>11],
    ['basename'=>'PronounsDemonstrativesReflexivePronounsTheorySeeder',
        'slug'=>'reflexive-pronouns-myself-yourself-themselves','level'=>null,'continuing_blocks'=>11],
    ['basename'=>'PronounsDemonstrativesIndefinitePronounsTheorySeeder',
        'slug'=>'indefinite-pronouns-someone-anyone-nobody-nothing','level'=>null,'continuing_blocks'=>12],
];
PHP;
runFrozenReadOnlyProfile('m52-working-local.php','9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d',[
    ['M52','M75',3],['m52','m75',5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7','67ebba5ed98fae05ca6022cac802870cfacf0d52',1],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBindings,$bindings,1],
    ['Page_V3\\\\PassiveVoice\\\\Basics\\\\','Page_V3\\\\PronounsDemonstratives\\\\',1],
    ['database/seeders/Page_V3/PassiveVoice/Basics/','database/seeders/Page_V3/PronounsDemonstratives/',1],
    ["'passive-voice'","'zaimennyky-ta-vkazivni-slova'",3],
    ['~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D',
        '~^/(?:theory|courses/english-grammar-theory/lesson)/zaimennyky-ta-vkazivni-slova/(?:possessive-adjectives-vs-pronouns-my-mine-your-yours|reflexive-pronouns-myself-yourself-themselves|indefinite-pronouns-someone-anyone-nobody-nothing)$~D',1],
    ["(\$a['marker'] <=> \$b['marker'])","strnatcmp((string) \$a['marker'], (string) \$b['marker'])",1],
    ["CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 45","CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120",1],
    ["throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target.');",
        "throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target: '.\$route['path']);",1],
]);
