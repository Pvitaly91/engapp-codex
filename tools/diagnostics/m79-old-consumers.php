<?php

/** Six finite M79 owners over the accepted read-only consumer procedure. */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M79 audit requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file'=>$argv[5], 'final_sha256'=>$argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaseline = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M79 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M79 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$baseline = <<<'PHP'
if ($mode === 'audit') {
    $finalName=$profileContext['final_file']??''; $finalSha=$profileContext['final_sha256']??'';
    if (preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$finalName)!==1
        || preg_match('/^[a-f0-9]{64}$/D',$finalSha)!==1 || is_link($dir.'/'.$finalName)
        || !is_file($dir.'/'.$finalName) || !hash_equals($finalSha,hash_file('sha256',$dir.'/'.$finalName))) {
        throw new RuntimeException('M79 independent final receipt is missing, linked or changed.');
    }
    $final=json_decode(file_get_contents($dir.'/'.$finalName),true,flags:JSON_THROW_ON_ERROR);
    if (($final['independent_before_sha256']??null)!==$workingBeforeSha
        || ($final['runtime']??null)!==($workingBefore['identity']??null)
        || ($final['all_old_rows_and_timestamps_exact']??null)!==true
        || ($final['independent_before_projection_exact']??null)!==true
        || ($final['reviewed_protected_projection_exact']??null)!==true
        || ($final['normalized_before']??null)!==$workingBefore['sql_before']
        || ($final['sql_after']??null)!==$allTablesBeforeHttp
        || ($final['db_writes']??null)!==0 || ($final['http_requests']??null)!==0) {
        throw new RuntimeException('M79 current tables must equal reviewed AFTER with exact BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table=>$expected) {
        if (($allTablesBeforeHttp[$table]??null)!==$expected) { throw new RuntimeException('M79 consumer BEFORE SQL drift: '.$table); }
    }
}
PHP;
$oldManifests = <<<'PHP'
$manifests = [
    'database/seeders/V3/TheoryLinks/data/passive-voice-formation-rules-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/passive-voice-negatives-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/passive-voice-modal-verbs-theory-links.json',
];
PHP;
$manifests = <<<'PHP'
$manifests = [
    'database/seeders/V3/TheoryLinks/data/questions-negations-alternative-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/questions-negations-question-tags-disjunctive-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/questions-negations-negative-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/questions-negations-subject-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/questions-negations-embedded-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/questions-negations-indirect-questions-theory-links.json',
];
PHP;
$oldGuard = <<<'PHP'
    if (($page['category_slug'] ?? null) !== 'passive-voice'
        || !in_array($page['page_seeder_class'] ?? null, [
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceNegativesQuestionsTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceModalVerbsTheorySeeder',
        ], true)
        || ($page['route'] ?? null) !== '/theory/passive-voice/'.($page['slug'] ?? '')
        || !str_starts_with($page['slug'] ?? '', 'theory-passive-voice-')) {
PHP;
$guard = <<<'PHP'
    $scopes = [
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsAlternativeQuestionsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia','alternative-questions-coffee-or-tea','AlternativeQuestions','QuestionsNegations',['pytalni-rechennia-ta-zaperechennia','types-of-questions']],
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsQuestionTagsDisjunctiveQuestionsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia','question-tags-disjunctive-questions-dont-you-isnt-it','QuestionTagsDisjunctiveQuestions','QuestionsNegations',['pytalni-rechennia-ta-zaperechennia','types-of-questions']],
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsNegativeQuestionsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia','negative-questions-dont-you-know','NegativeQuestions','QuestionsNegations',['pytalni-rechennia-ta-zaperechennia','types-of-questions']],
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsSubjectQuestionsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia/types-of-questions','subject-questions','SubjectQuestions','QuestionsNegations',['pytalni-rechennia-ta-zaperechennia','types-of-questions']],
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsEmbeddedQuestionsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia/types-of-questions','embedded-questions','EmbeddedQuestions','QuestionsNegations',['pytalni-rechennia-ta-zaperechennia','types-of-questions']],
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsIndirectQuestionsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia/types-of-questions','indirect-questions','IndirectQuestions','QuestionsNegations',['pytalni-rechennia-ta-zaperechennia','types-of-questions']],
    ];
    $scope=$scopes[$page['page_seeder_class']??'']??null;
    if ($scope===null || ($page['category_slug']??null)!==$scope[0] || ($page['slug']??null)!==$scope[1]
        || ($page['route']??null)!=='/theory/'.implode('/',$scope[4]).'/'.$scope[1]) {
PHP;
$manifestHook = <<<'PHP'
    $expectedBanks=[
        'Database\\Seeders\\V3\\Polyglot\\Polyglot'.$scope[2].'AllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\QuestionsNegations\\'.$scope[2].'AllLevelsV3Seeder',
    ];
    $tests=$manifest['tests_on_page']??[];
    $legacy=$scope[2]==='QuestionTagsDisjunctiveQuestions';
    $standard=in_array($scope[2],['EmbeddedQuestions','IndirectQuestions'],true);
    $kinds=$legacy?['direct_sentence_builder','legacy_sentence_builder','mixed_a1_c2_standard_v3']
        :['direct_sentence_builder',$standard?'standard_v3':'mixed_a1_c2_standard_v3'];
    if (array_column($tests,'kind')!==$kinds||($tests[0]['seeder_class']??null)!==$expectedBanks[0]) {
        throw new RuntimeException('M79 exact manifest test kinds/direct bank differ.');
    }
    if ($legacy) {
        $expectedBanks[]='Database\\Seeders\\V3\\Polyglot\\PolyglotQuestionTagsBasicsLessonSeeder';
        if (($tests[1]['seeder_class']??null)!==$expectedBanks[2]) {throw new RuntimeException('M79 legacy Tags bank differs.');}
    }
    $last=$tests[count($tests)-1];
    if (($last['expected_seeder_classes']??null)!==[$expectedBanks[1]]
        ||($standard?isset($last['seeder_class']):($last['seeder_class']??null)!==$expectedBanks[1])) {
        throw new RuntimeException('M79 exact standard bank declaration differs.');
    }
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
PHP;
$oldPageBinding = <<<'PHP'
        if ($page->type !== 'theory' || $page->slug !== $source['slug'] || $page->category?->slug !== $source['category_slug']) {
            throw new RuntimeException('M79 old consumer page binding differs.');
        }
PHP;
$pageBinding = <<<'PHP'
        $ancestry=[]; $category=$page->category;
        while ($category) {
            if (count($ancestry)>=8 || $category->language!=='uk' || $category->type!=='theory') {
                throw new RuntimeException('M79 category locale/type or ancestry depth differs.');
            }
            array_unshift($ancestry,$category->slug); $category=$category->parent;
        }
        $categoryPath=implode('/',$source['_actual_ancestry']);
        if ($page->type !== 'theory' || $page->slug !== $source['slug']
            || $ancestry!==$source['_actual_ancestry']
            || $source['route']!=='/theory/'.$categoryPath.'/'.$page->slug) {
            throw new RuntimeException('M79 actual DB ancestry differs from the finite manifest/route binding.');
        }
        $resolver=new class extends App\Http\Controllers\PageController {
            public function __construct() {}
            public function path(App\Models\PageCategory $category): string { return $this->categorySlugPath($category); }
        };
        if ($resolver->path($page->category)!==$categoryPath) {
            throw new RuntimeException('M79 native page resolver differs.');
        }
PHP;
$sourceGuard='~^database/seeders/V3/(?:TheoryLinks/data/(?:questions-negations-alternative-questions|questions-negations-question-tags-disjunctive-questions|questions-negations-negative-questions|questions-negations-subject-questions|questions-negations-embedded-questions|questions-negations-indirect-questions)-theory-links\\.json|Polyglot/Polyglot(?:(?:AlternativeQuestions|QuestionTagsDisjunctiveQuestions|NegativeQuestions|SubjectQuestions|EmbeddedQuestions|IndirectQuestions)AllLevelsLessonSeeder|QuestionTagsBasicsLessonSeeder)/definition\\.json|QuestionsNegations/(?:AlternativeQuestions|QuestionTagsDisjunctiveQuestions|NegativeQuestions|SubjectQuestions|EmbeddedQuestions|IndirectQuestions)AllLevelsV3Seeder/definition\\.json)$~D';
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',[
    ['M52','M79',43],['m52','m79',4],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBaseline,$baseline,1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','8c261b17b3f7e332fc5fc86eef8b555af83de19938aee7ae83d2a23e7dbb3add',1],
    [$oldManifests,$manifests,1],
    ['~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\.json)$~D',$sourceGuard,1],
    [$oldGuard,$guard,1],
    ["    foreach (\$manifest['tests_on_page'] ?? [] as \$test) {",$manifestHook,1],
    ["        if (!preg_match('/^Database\\\\\\\\Seeders\\\\\\\\V3\\\\\\\\(?:Polyglot|PassiveVoice)\\\\\\\\[A-Za-z0-9]+$/D', \$class)) {","        if (!in_array(\$class,\$expectedBanks,true)) {",1],
    ["    \$pages[\$page['page_seeder_class']] = \$page;","    \$pages[\$page['page_seeder_class']] = \$page + ['_actual_ancestry'=>\$scope[4]];",1],
    [$oldPageBinding,$pageBinding,1],
    ["['categoryPath' => \$source['category_slug'], 'pageSlug' => \$source['slug']]","['categoryPath' => implode('/',\$source['_actual_ancestry']), 'pageSlug' => \$source['slug']]",1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["    \$http = \$getLocal(\$route); \$entry =","    echo \$json(['checking_consumer'=>\$path]);\n    \$http = \$getLocal(\$route); \$entry =",1],
],$context);
