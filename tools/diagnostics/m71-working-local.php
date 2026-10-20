<?php

/** Finite pre-registration M71 profile of the accepted SELECT/HTTP BEFORE capture. */
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
    ['family'=>'NounsArticlesQuantity', 'category'=>'imennyky-artykli-ta-kilkist',
        'basename'=>'NounsArticlesQuantityArticlesTheorySeeder', 'slug'=>'articles-a-an-the',
        'level'=>'A2', 'continuing_blocks'=>12, 'levels'=>[null,null,null,null,null,null,null,null,null,null,null,null]],
    ['family'=>'NounsArticlesQuantity', 'category'=>'imennyky-artykli-ta-kilkist',
        'basename'=>'NounsArticlesQuantityZeroArticleTheorySeeder', 'slug'=>'zero-article',
        'level'=>'A2', 'continuing_blocks'=>14, 'levels'=>[null,null,null,null,null,null,null,null,null,null,null,null,null,null]],
    ['family'=>'NounsArticlesQuantity', 'category'=>'imennyky-artykli-ta-kilkist',
        'basename'=>'NounsArticlesQuantityQuantifiersTheorySeeder', 'slug'=>'quantifiers-much-many-a-lot-few-little',
        'level'=>'A2', 'continuing_blocks'=>13, 'levels'=>[null,null,null,null,null,null,null,null,null,null,null,null,null]],
];
PHP;
runFrozenReadOnlyProfile('m52-working-local.php', '9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d', [
    ['M52','M71',3], ['m52','m71',5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7','1291b576b47dc28e5ba9e0c58faa052f6e49460e',1],
    [$oldBindings,$bindings,1],
    ["\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\Basics\\\\'.\$binding['basename'];",
        "\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\'.\$binding['family'].'\\\\'.\$binding['basename'];",1],
    ["\$path = 'database/seeders/Page_V3/PassiveVoice/Basics/'.\$binding['basename'].'/definition.json';",
        "\$path = 'database/seeders/Page_V3/'.\$binding['family'].'/'.\$binding['basename'].'/definition.json';",1],
    ["\$definition['page']['category']['slug'] !== 'passive-voice'","\$definition['page']['category']['slug'] !== \$binding['category']",1],
    ["array_column(\$chain, 'slug') !== ['passive-voice']","array_column(\$chain, 'slug') !== [\$binding['category']]",1],
    ["\$categoryPath !== 'passive-voice'","\$categoryPath !== \$binding['category']",1],
    ['~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D',
        '~^/(?:theory|courses/english-grammar-theory/lesson)/imennyky-artykli-ta-kilkist/(?:articles-a-an-the|zero-article|quantifiers-much-many-a-lot-few-little)$~D',1],
    ["\$config['type'] !== 'subtitle' && \$config['type'] !== 'navigation-chips' && (\$config['level'] ?? null) !== \$binding['level']",
        "\$position > 0 && (\$config['level'] ?? null) !== \$binding['levels'][\$position - 1]",1],
    ["'technical_level' => \$binding['level']","'planned_practice_level' => \$binding['level']",1],
    ["(\$a['marker'] <=> \$b['marker'])","strnatcmp((string) \$a['marker'], (string) \$b['marker'])",1],
    ["CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 45","CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120",1],
    ["throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target.');",
        "throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target: '.\$route['path']);",1],
]);
