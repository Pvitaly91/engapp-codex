<?php

/** Finite pre-registration M74 SELECT/HTTP BEFORE capture. */
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
    ['family'=>'NounsArticlesQuantity','category'=>'imennyky-artykli-ta-kilkist',
        'basename'=>'NounsArticlesQuantityAnotherOtherTheOtherTheorySeeder','slug'=>'another-other-the-other',
        'level'=>'B1','continuing_blocks'=>6,'levels'=>['B1','B1','B1','B1','B1',null]],
    ['family'=>'PronounsDemonstratives','category'=>'zaimennyky-ta-vkazivni-slova',
        'basename'=>'PronounsDemonstrativesPronounsTheorySeeder','slug'=>'pronouns-theory',
        'level'=>null,'continuing_blocks'=>12,'levels'=>[null,null,null,null,null,null,null,null,null,null,null,null]],
    ['family'=>'PronounsDemonstratives','category'=>'zaimennyky-ta-vkazivni-slova',
        'basename'=>'PronounsDemonstrativesPersonalObjectPronounsTheorySeeder','slug'=>'personal-object-pronouns',
        'level'=>null,'continuing_blocks'=>10,'levels'=>[null,null,null,null,null,null,null,null,null,null]],
];
PHP;
runFrozenReadOnlyProfile('m52-working-local.php','9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d',[
    ['M52','M74',3],['m52','m74',5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7','fd1ad30e710b3d950e574848c886d86f6045f2e3',1],
    [$oldBindings,$bindings,1],
    ["\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\Basics\\\\'.\$binding['basename'];",
        "\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\'.\$binding['family'].'\\\\'.\$binding['basename'];",1],
    ["\$path = 'database/seeders/Page_V3/PassiveVoice/Basics/'.\$binding['basename'].'/definition.json';",
        "\$path = 'database/seeders/Page_V3/'.\$binding['family'].'/'.\$binding['basename'].'/definition.json';",1],
    ["\$definition['page']['category']['slug'] !== 'passive-voice'","\$definition['page']['category']['slug'] !== \$binding['category']",1],
    ["array_column(\$chain, 'slug') !== ['passive-voice']","array_column(\$chain, 'slug') !== [\$binding['category']]",1],
    ["\$categoryPath !== 'passive-voice'","\$categoryPath !== \$binding['category']",1],
    ['~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D',
        '~^/(?:theory|courses/english-grammar-theory/lesson)/(?:imennyky-artykli-ta-kilkist/another-other-the-other|zaimennyky-ta-vkazivni-slova/(?:pronouns-theory|personal-object-pronouns))$~D',1],
    ["\$config['type'] !== 'subtitle' && \$config['type'] !== 'navigation-chips' && (\$config['level'] ?? null) !== \$binding['level']",
        "\$position > 0 && (\$config['level'] ?? null) !== \$binding['levels'][\$position - 1]",1],
    ["'technical_level' => \$binding['level']","'planned_practice_level' => \$binding['level']",1],
    ["(\$a['marker'] <=> \$b['marker'])","strnatcmp((string) \$a['marker'], (string) \$b['marker'])",1],
    ["CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 45","CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120",1],
    ["throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target.');",
        "throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target: '.\$route['path']);",1],
]);
