<?php

/** Six finite M78 owners over the accepted read-only consumer procedure. */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M78 audit requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file'=>$argv[5], 'final_sha256'=>$argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaseline = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M78 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M78 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$baseline = <<<'PHP'
if ($mode === 'audit') {
    $finalName=$profileContext['final_file']??''; $finalSha=$profileContext['final_sha256']??'';
    if (preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$finalName)!==1
        || preg_match('/^[a-f0-9]{64}$/D',$finalSha)!==1 || is_link($dir.'/'.$finalName)
        || !is_file($dir.'/'.$finalName) || !hash_equals($finalSha,hash_file('sha256',$dir.'/'.$finalName))) {
        throw new RuntimeException('M78 independent final receipt is missing, linked or changed.');
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
        throw new RuntimeException('M78 current tables must equal reviewed AFTER with exact BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table=>$expected) {
        if (($allTablesBeforeHttp[$table]??null)!==$expected) { throw new RuntimeException('M78 consumer BEFORE SQL drift: '.$table); }
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
    'database/seeders/V3/TheoryLinks/data/prepositions-phrasal-verbs-separable-inseparable-phrasal-verbs-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/prepositions-phrasal-verbs-common-phrasal-verbs-by-topic-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/questions-negations-question-forms-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/questions-negations-yes-no-questions-general-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/questions-negations-wh-questions-special-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/questions-negations-answers-to-questions-theory-links.json',
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
        'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PhrasalVerbsSeparableInseparableTheorySeeder'=>['prepositions-and-phrasal-verbs','separable-and-inseparable-phrasal-verbs','SeparableInseparablePhrasalVerbs','PrepositionsAndPhrasalVerbs',['prepositions-and-phrasal-verbs']],
        'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\CommonPhrasalVerbsByTopicTheorySeeder'=>['prepositions-and-phrasal-verbs','common-phrasal-verbs-by-topic','CommonPhrasalVerbsByTopic','PrepositionsAndPhrasalVerbs',['prepositions-and-phrasal-verbs']],
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsQuestionFormsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia','question-forms','QuestionForms','QuestionsNegations',['pytalni-rechennia-ta-zaperechennia']],
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsYesNoQuestionsGeneralQuestionsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia/types-of-questions','yesno-questions-general-questions','YesNoQuestionsGeneralQuestions','QuestionsNegations',['pytalni-rechennia-ta-zaperechennia','types-of-questions']],
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsWhQuestionsSpecialQuestionsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia/types-of-questions','wh-questions-special-questions-who-what-where-when-why-how','WhQuestionsSpecialQuestions','QuestionsNegations',['pytalni-rechennia-ta-zaperechennia','types-of-questions']],
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsAnswersToQuestionsTheorySeeder'=>['pytalni-rechennia-ta-zaperechennia','answers-to-questions-yes-i-do-no-i-dont','AnswersToQuestions','QuestionsNegations',['pytalni-rechennia-ta-zaperechennia','types-of-questions']],
    ];
    $scope=$scopes[$page['page_seeder_class']??'']??null;
    if ($scope===null || ($page['category_slug']??null)!==$scope[0] || ($page['slug']??null)!==$scope[1]
        || ($page['route']??null)!=='/theory/'.implode('/',$scope[4]).'/'.$scope[1]) {
PHP;
$manifestHook = <<<'PHP'
    $expectedBanks=[
        'Database\\Seeders\\V3\\Polyglot\\Polyglot'.$scope[2].'AllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\'.$scope[3].'\\'.$scope[2].'AllLevelsV3Seeder',
    ];
    $tests=$manifest['tests_on_page']??[];
    if (count($tests)!==2 || ($tests[0]['kind']??null)!=='direct_sentence_builder'
        || ($tests[0]['seeder_class']??null)!==$expectedBanks[0]
        || ($scope[3]==='QuestionsNegations'
            ? (($tests[1]['kind']??null)!=='mixed_a1_c2_standard_v3'
                || ($tests[1]['seeder_class']??null)!==$expectedBanks[1]
                || ($tests[1]['expected_seeder_classes']??null)!==[$expectedBanks[1]])
            : (($tests[1]['kind']??null)!=='mixed_a1_c2' || isset($tests[1]['seeder_class'])
                || ($tests[1]['expected_seeder_classes']??null)!==[$expectedBanks[1],$expectedBanks[0]]))) {
        throw new RuntimeException('M78 exact direct/standard-or-mixed manifest declarations differ.');
    }
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
        // The page mixed route is independently captured below. Its one named
        // standard source is also audited through that source's own saved test.
        if (($test['kind']??null)==='mixed_a1_c2') {
            $test['seeder_class']=$expectedBanks[1]; $test['kind']='standard_v3';
        }
PHP;
$oldPageBinding = <<<'PHP'
        if ($page->type !== 'theory' || $page->slug !== $source['slug'] || $page->category?->slug !== $source['category_slug']) {
            throw new RuntimeException('M78 old consumer page binding differs.');
        }
PHP;
$pageBinding = <<<'PHP'
        $ancestry=[]; $category=$page->category;
        while ($category) {
            if (count($ancestry)>=8 || $category->language!=='uk' || $category->type!=='theory') {
                throw new RuntimeException('M78 category locale/type or ancestry depth differs.');
            }
            array_unshift($ancestry,$category->slug); $category=$category->parent;
        }
        $categoryPath=implode('/',$source['_actual_ancestry']);
        if ($page->type !== 'theory' || $page->slug !== $source['slug']
            || $ancestry!==$source['_actual_ancestry']
            || $source['route']!=='/theory/'.$categoryPath.'/'.$page->slug) {
            throw new RuntimeException('M78 actual DB ancestry differs from the finite manifest/route binding.');
        }
        $resolver=new class extends App\Http\Controllers\PageController {
            public function __construct() {}
            public function path(App\Models\PageCategory $category): string { return $this->categorySlugPath($category); }
        };
        if ($resolver->path($page->category)!==$categoryPath) {
            throw new RuntimeException('M78 native page resolver differs.');
        }
PHP;
$sourceGuard='~^database/seeders/V3/(?:TheoryLinks/data/(?:prepositions-phrasal-verbs-separable-inseparable-phrasal-verbs|prepositions-phrasal-verbs-common-phrasal-verbs-by-topic|questions-negations-question-forms|questions-negations-yes-no-questions-general-questions|questions-negations-wh-questions-special-questions|questions-negations-answers-to-questions)-theory-links\\.json|Polyglot/Polyglot(?:SeparableInseparablePhrasalVerbs|CommonPhrasalVerbsByTopic|QuestionForms|YesNoQuestionsGeneralQuestions|WhQuestionsSpecialQuestions|AnswersToQuestions)AllLevelsLessonSeeder/definition\\.json|PrepositionsAndPhrasalVerbs/(?:SeparableInseparablePhrasalVerbs|CommonPhrasalVerbsByTopic)AllLevelsV3Seeder/definition\\.json|QuestionsNegations/(?:QuestionForms|YesNoQuestionsGeneralQuestions|WhQuestionsSpecialQuestions|AnswersToQuestions)AllLevelsV3Seeder/definition\\.json)$~D';
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',[
    ['M52','M78',43],['m52','m78',4],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBaseline,$baseline,1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','327424de32f1cebaad86adef078d25aa1d4747b257894be3fdadc6706c630422',1],
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

