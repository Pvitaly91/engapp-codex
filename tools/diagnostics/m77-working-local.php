<?php

/** Fresh six-owner SELECT/HTTP BEFORE. The immutable M52 capture is not a writer. */
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
    ['PronounsDemonstratives','PronounsDemonstrativesOneOnesTheorySeeder','one-ones','zaimennyky-ta-vkazivni-slova',11,'A2'],
    ['PrepositionsAndPhrasalVerbs','PrepositionsOfTimeTheorySeeder','prepositions-of-time','prepositions-and-phrasal-verbs',6,'A1'],
    ['PrepositionsAndPhrasalVerbs','PrepositionsOfPlaceTheorySeeder','prepositions-of-place','prepositions-and-phrasal-verbs',6,'A1'],
    ['PrepositionsAndPhrasalVerbs','PrepositionsOfMovementTheorySeeder','prepositions-of-movement','prepositions-and-phrasal-verbs',6,'A2'],
    ['PrepositionsAndPhrasalVerbs','DependentPrepositionsTheorySeeder','dependent-prepositions','prepositions-and-phrasal-verbs',6,'B1'],
    ['PrepositionsAndPhrasalVerbs','PhrasalVerbsBasicsTheorySeeder','phrasal-verbs-basics','prepositions-and-phrasal-verbs',6,'B1'],
] as [$group,$basename,$slug,$category,$count,$focus]) {
    $bindings[]=['identity'=>'Database\\Seeders\\Page_V3\\'.$group.'\\'.$basename,
        'definition'=>'database/seeders/Page_V3/'.$group.'/'.$basename.'/definition.json',
        'basename'=>$basename,'slug'=>$slug,'category'=>$category,'continuing_blocks'=>$count,
        'level'=>null,'intended_own_practice_level'=>$focus];
}
PHP;
$transport = <<<'PHP'
        $wtBytes=file_get_contents($worktree.'/'.$path);
        if ($bytes!==$wtBytes) {
            if ($path!=='database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder/definition.json'
                ||hash('sha256',$bytes)!=='02b25330407a6e2ca6c8777da65759ffc48294c938e0369a980872962bec0118'
                ||hash('sha256',$wtBytes)!=='ef7f91082a71cbe2e41d26c9c5286d9c9dfe50eb3fe925b8467898285b001737'
                ||str_replace("\r\n","\n",$wtBytes)!==$bytes
                ||json_decode($bytes,true,flags:JSON_THROW_ON_ERROR)!==json_decode($wtBytes,true,flags:JSON_THROW_ON_ERROR)) {
                throw new RuntimeException('Exact M77 One BEFORE transport pair differs.');
            }
            $capture['definition_transport'][$owner]=['root_raw_sha256'=>hash('sha256',$bytes),
                'worktree_raw_sha256'=>hash('sha256',$wtBytes),'lf_sha256'=>hash('sha256',$bytes),'only_crlf_transport'=>true];
        }
PHP;
$oldLevel = <<<'PHP'
            if ($config['type'] !== 'subtitle' && $config['type'] !== 'navigation-chips' && ($config['level'] ?? null) !== $binding['level']) {
                throw new RuntimeException('Existing technical block level differs.');
            }
PHP;
$level = <<<'PHP'
            // Every SQL level is already compared with its exact source config above.
            // Hero display/focus is deliberately not imposed on other continuing rows.
PHP;
runFrozenReadOnlyProfile('m52-working-local.php','9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d',[
    ['M52','M77',3],['m52','m77',5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7','5270494e0f999eee2a3d1fba6fb7c3f46a914481',1],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBindings,$bindings,1],
    ["\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\Basics\\\\'.\$binding['basename'];","\$owner=\$binding['identity'];",1],
    ["\$path = 'database/seeders/Page_V3/PassiveVoice/Basics/'.\$binding['basename'].'/definition.json';","\$path=\$binding['definition'];",1],
    ["if (\$bytes !== file_get_contents(\$worktree.'/'.\$path)) { throw new RuntimeException('Target BEFORE ROOT/WT definition bytes differ.'); }",$transport,1],
    ["\$definition['page']['category']['slug'] !== 'passive-voice'","\$definition['page']['category']['slug'] !== \$binding['category']",1],
    ["array_column(\$chain, 'slug') !== ['passive-voice']","array_column(\$chain, 'slug') !== [\$binding['category']]",1],
    [$oldLevel,$level,1],
    ["\$categoryPath !== 'passive-voice'","\$categoryPath !== \$binding['category']",1],
    ['~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D',
        '~^/(?:theory|courses/english-grammar-theory/lesson)/(?:zaimennyky-ta-vkazivni-slova/one-ones|prepositions-and-phrasal-verbs/(?:prepositions-of-time|prepositions-of-place|prepositions-of-movement|dependent-prepositions|phrasal-verbs-basics))$~D',1],
    ["'technical_level' => \$binding['level'], 'continuing_blocks' => count(\$configs) - 1,",
        "'technical_level' => null, 'intended_own_practice_level'=>\$binding['intended_own_practice_level'], 'continuing_levels'=>array_column(array_slice(\$uk,1),'level'), 'existing_practices'=>array_values(array_filter(\$uk,static fn(\$b)=>\$b['type']==='practice-set')), 'continuing_blocks' => count(\$configs) - 1,",1],
    ["(\$a['marker'] <=> \$b['marker'])","strnatcmp((string) \$a['marker'], (string) \$b['marker'])",1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 45','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target.');",
        "throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target: '.\$route['path']);",1],
]);
