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
    ['basename'=>'PronounsDemonstrativesRelativePronounsTheorySeeder',
        'slug'=>'relative-pronouns-who-which-that-whose','level'=>null,'continuing_blocks'=>12],
    ['basename'=>'PronounsDemonstrativesEachEveryAllTheorySeeder',
        'slug'=>'each-every-all','level'=>null,'continuing_blocks'=>12],
    ['basename'=>'PronounsDemonstrativesReciprocalPronounsTheorySeeder',
        'slug'=>'reciprocal-pronouns-each-other-one-another','level'=>null,'continuing_blocks'=>12],
];
PHP;
$oldTransport = <<<'PHP'
        if ($bytes !== file_get_contents($worktree.'/'.$path)) { throw new RuntimeException('Target BEFORE ROOT/WT definition bytes differ.'); }
PHP;
$transport = <<<'PHP'
        $wtBytes = file_get_contents($worktree.'/'.$path);
        if ($bytes !== $wtBytes) {
            if ($path !== 'database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesReciprocalPronounsTheorySeeder/definition.json'
                || hash('sha256', $bytes) !== '2dadfbbaa4df22e7933e7fecc3a7fa1328357bda464538f611232d8e6b8ae0af'
                || hash('sha256', $wtBytes) !== 'cebb131eb4badd6f007ade29b446070448093d6a4c210d90b8e924878036dfbc'
                || str_replace("\r\n", "\n", $wtBytes) !== $bytes
                || json_decode($bytes, true, flags: JSON_THROW_ON_ERROR) !== json_decode($wtBytes, true, flags: JSON_THROW_ON_ERROR)) {
                throw new RuntimeException('Exact M76 BEFORE transport pair differs; no source overwrite.');
            }
            $capture['definition_transport'][$owner] = ['root_raw_sha256'=>hash('sha256',$bytes),
                'worktree_raw_sha256'=>hash('sha256',$wtBytes),'lf_sha256'=>hash('sha256',$bytes),'only_crlf_transport'=>true];
        }
PHP;
runFrozenReadOnlyProfile('m52-working-local.php','9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d',[
    ['M52','M76',3],['m52','m76',5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7','e0720938bad114c7e43e8e6efafa8355c1993fc1',1],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBindings,$bindings,1],
    [$oldTransport,$transport,1],
    ['Page_V3\\\\PassiveVoice\\\\Basics\\\\','Page_V3\\\\PronounsDemonstratives\\\\',1],
    ['database/seeders/Page_V3/PassiveVoice/Basics/','database/seeders/Page_V3/PronounsDemonstratives/',1],
    ["'passive-voice'","'zaimennyky-ta-vkazivni-slova'",3],
    ['~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D',
        '~^/(?:theory|courses/english-grammar-theory/lesson)/zaimennyky-ta-vkazivni-slova/(?:relative-pronouns-who-which-that-whose|each-every-all|reciprocal-pronouns-each-other-one-another)$~D',1],
    ["(\$a['marker'] <=> \$b['marker'])","strnatcmp((string) \$a['marker'], (string) \$b['marker'])",1],
    ["CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 45","CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120",1],
    ["throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target.');",
        "throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target: '.\$route['path']);",1],
]);
