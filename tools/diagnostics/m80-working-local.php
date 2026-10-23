<?php

/** Six finite M80 owners; immutable SELECT-only source/DB/HTTP BEFORE procedure. */
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
$bindings = [];
foreach ([
    ['QuestionsNegations','QuestionsNegationsNegativeFormsTheorySeeder','negative-forms',['pytalni-rechennia-ta-zaperechennia'],5,'A2'],
    ['QuestionsNegations','QuestionsNegationsDoubleNegativesTheorySeeder','double-negatives',['pytalni-rechennia-ta-zaperechennia'],5,'B1'],
    ['SentenceTransformations','SentenceTransformationsTensesTheorySeeder','tenses',['sentence-transformations'],6,'B1'],
    ['SentenceTransformations','SentenceTransformationsModalsTheorySeeder','modals',['sentence-transformations'],6,'B1'],
    ['SentenceTransformations','SentenceTransformationsPassiveTheorySeeder','passive',['sentence-transformations'],6,'B1'],
    ['SentenceTransformations','SentenceTransformationsReportedSpeechTheorySeeder','reported-speech',['sentence-transformations'],6,'B1'],
] as [$group,$basename,$slug,$ancestry,$count,$focus]) {
    $bindings[]=['identity'=>'Database\\Seeders\\Page_V3\\'.$group.'\\'.$basename,
        'definition'=>'database/seeders/Page_V3/'.str_replace('\\','/',$group).'/'.$basename.'/definition.json',
        'basename'=>$basename,'slug'=>$slug,'category'=>end($ancestry),'ancestry'=>$ancestry,
        'continuing_blocks'=>$count,'level'=>$focus,'intended_own_practice_level'=>$focus];
}
PHP;
$oldLevel = <<<'PHP'
            if ($config['type'] !== 'subtitle' && $config['type'] !== 'navigation-chips' && ($config['level'] ?? null) !== $binding['level']) {
                throw new RuntimeException('Existing technical block level differs.');
            }
PHP;
$level = <<<'PHP'
            // The inspected SQL level is captured independently. A source range
            // or absent level is never rewritten into the new practice focus.
PHP;
$oldField = <<<'PHP'
                if ($row[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('Continuing row/source field differs: '.$field); }
PHP;
$field = <<<'PHP'
                if ($field === 'level' && !array_key_exists('level', $config)) { continue; }
                if ($row[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('Continuing row/source field differs: '.$owner.' position '.$position.' field '.$field); }
PHP;
runFrozenReadOnlyProfile('m52-working-local.php','9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d',[
    ['M52','M80',3],['m52','m80',5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7','b56f29df4b3409edc1ad6cfb34b7fedd901e893d',1],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBindings,$bindings,1],
    ["\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\Basics\\\\'.\$binding['basename'];","\$owner=\$binding['identity'];",1],
    ["\$path = 'database/seeders/Page_V3/PassiveVoice/Basics/'.\$binding['basename'].'/definition.json';","\$path=\$binding['definition'];",1],
    ["\$definition['page']['category']['slug'] !== 'passive-voice'","\$definition['page']['category']['slug'] !== \$binding['category']",1],
    ["array_column(\$chain, 'slug') !== ['passive-voice']","array_column(\$chain, 'slug') !== \$binding['ancestry']",1],
    [$oldLevel,$level,1],[$oldField,$field,1],
    ["\$categoryPath !== 'passive-voice'","\$categoryPath !== implode('/',\$binding['ancestry'])",1],
    ['~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D',
        '~^/(?:theory|courses/english-grammar-theory/lesson)/(?:pytalni-rechennia-ta-zaperechennia/(?:negative-forms|double-negatives)|sentence-transformations/(?:tenses|modals|passive|reported-speech))$~D',1],
    ["'technical_level' => \$binding['level'], 'continuing_blocks' => count(\$configs) - 1,",
        "'technical_level' => \$binding['level'], 'intended_own_practice_level'=>\$binding['intended_own_practice_level'], 'continuing_levels'=>array_column(array_slice(\$uk,1),'level'), 'existing_practices'=>array_values(array_filter(\$uk,static fn(\$b)=>\$b['type']==='practice-set')), 'continuing_blocks' => count(\$configs) - 1,",1],
    ["(\$a['marker'] <=> \$b['marker'])","strnatcmp((string) \$a['marker'], (string) \$b['marker'])",1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 45','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target.');",
        "throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target: '.\$route['path']);",1],
]);
