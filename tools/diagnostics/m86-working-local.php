<?php

/** Fresh independent M86 source/SQL/HTTP BEFORE. No write/apply/seeder. */
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
    ['PresentSimple','PresentSimpleFormsTheorySeeder','present-simple-forms',8,'A1'],
    ['PresentSimple','PresentSimpleNegativesTheorySeeder','present-simple-negatives',8,'A1'],
    ['PresentSimple','PresentSimpleQuestionsTheorySeeder','present-simple-questions',9,'A1'],
    ['PresentSimple','PresentSimpleAdverbsFrequencyTheorySeeder','present-simple-adverbs-of-frequency',8,'A2'],
    ['PresentContinuous','PresentContinuousFormsTheorySeeder','present-continuous-forms',7,'A1'],
    ['PresentContinuous','PresentContinuousNegativesTheorySeeder','present-continuous-negatives',7,'A1'],
] as [$group,$seeder,$slug,$count,$focus]) {
    $category=$group==='PresentSimple'?'present-simple':'present-continuous';
    $bindings[]=['identity'=>'Database\\Seeders\\Page_V3\\Tenses\\'.$group.'\\'.$seeder,
        'definition'=>'database/seeders/Page_V3/Tenses/'.$group.'/'.$seeder.'/definition.json',
        'basename'=>$seeder,'slug'=>$slug,'category'=>$category,'ancestry'=>['tenses',$category],
        'continuing_blocks'=>$count,'level'=>$focus];
}
PHP;
$oldLevel = <<<'PHP'
            if ($config['type'] !== 'subtitle' && $config['type'] !== 'navigation-chips' && ($config['level'] ?? null) !== $binding['level']) {
                throw new RuntimeException('Existing technical block level differs.');
            }
PHP;
$oldField = <<<'PHP'
                if ($row[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('Continuing row/source field differs: '.$field); }
PHP;
$field = <<<'PHP'
                if ($field === 'level' && !array_key_exists('level', $config)) { continue; }
                if ($row[$field] !== ($config[$field] ?? null)) { throw new RuntimeException('Continuing row/source field differs: '.$owner.' position '.$position.' field '.$field); }
PHP;
runFrozenReadOnlyProfile('m52-working-local.php','9f3df090f345a2ec387eb07eaa69b0a87b668d80e4e6643ccf88d2470fbf023d',[
    ['M52','M86',3],['m52','m86',5],
    ['0e2ed40c438400e2f2a3992314220fdf206db9a7','f5505ae98b8babd7e5a7fa2fd28784802a961fbd',1],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBindings,$bindings,1],
    ["\$owner = 'Database\\\\Seeders\\\\Page_V3\\\\PassiveVoice\\\\Basics\\\\'.\$binding['basename'];","\$owner=\$binding['identity'];",1],
    ["\$path = 'database/seeders/Page_V3/PassiveVoice/Basics/'.\$binding['basename'].'/definition.json';","\$path=\$binding['definition'];",1],
    ["\$definition['page']['category']['slug'] !== 'passive-voice'","\$definition['page']['category']['slug'] !== \$binding['category']",1],
    ["array_column(\$chain, 'slug') !== ['passive-voice']","array_column(\$chain, 'slug') !== \$binding['ancestry']",1],
    [$oldLevel,"            // Actual SQL levels are captured separately, never inferred from editorial focus.\n",1],
    [$oldField,$field,1],
    ["\$categoryPath !== 'passive-voice'","\$categoryPath !== implode('/',\$binding['ancestry'])",1],
    ['~^/(?:theory|courses/english-grammar-theory/lesson)/passive-voice/theory-passive-voice-[a-z-]+$~D',
        '~^/(?:theory|courses/english-grammar-theory/lesson)/tenses/(?:present-simple/present-simple-(?:forms|negatives|questions|adverbs-of-frequency)|present-continuous/present-continuous-(?:forms|negatives))$~D',1],
    ["'technical_level' => \$binding['level'], 'continuing_blocks' => count(\$configs) - 1,",
        "'editorial_focus' => \$binding['level'], 'bank_inventory_level_policy'=>'Focus-level preliminary inventory only. Actual SQL practice filters are independently captured.', 'continuing_levels'=>array_column(array_slice(\$uk,1),'level'), 'existing_practices'=>array_values(array_filter(\$uk,static fn(\$b)=>\$b['type']==='practice-set')), 'continuing_blocks' => count(\$configs) - 1,",1],
    ["(\$a['marker'] <=> \$b['marker'])","strnatcmp((string) \$a['marker'], (string) \$b['marker'])",1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 45','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target.');",
        "throw new RuntimeException('Anonymous fixed-loopback BEFORE GET failed or changed target: '.\$route['path']);",1],
]);
