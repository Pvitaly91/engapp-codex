<?php

/** Exact six-owner M82 profile of the accepted SELECT-only old-consumer procedure. */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M82 audit requires a reviewed independent final receipt and raw SHA-256.');
    }
    $context = ['final_file'=>$argv[5], 'final_sha256'=>$argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaseline = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M82 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M82 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$baseline = <<<'PHP'
if ($mode === 'audit') {
    $finalName=$profileContext['final_file']??''; $finalSha=$profileContext['final_sha256']??'';
    if (preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$finalName)!==1
        || preg_match('/^[a-f0-9]{64}$/D',$finalSha)!==1 || is_link($dir.'/'.$finalName)
        || !is_file($dir.'/'.$finalName) || !hash_equals($finalSha,hash_file('sha256',$dir.'/'.$finalName))) {
        throw new RuntimeException('M82 independent final receipt is missing, linked or changed.');
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
        throw new RuntimeException('M82 current tables must equal the independently reviewed AFTER with exact BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table=>$expected) {
        if (($allTablesBeforeHttp[$table]??null)!==$expected) { throw new RuntimeException('M82 consumer BEFORE SQL drift: '.$table); }
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
    'database/seeders/V3/TheoryLinks/data/common-mistakes-present-perfect-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/common-mistakes-countable-uncountable-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/common-mistakes-prepositions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/common-mistakes-b1-b2-grammar-traps-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/mixed-revision-a1-mixed-revision-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/mixed-revision-a2-mixed-revision-theory-links.json',
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
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesPresentPerfectTheorySeeder'=>['common-mistakes','present-perfect-common-mistakes','CommonMistakesPresentPerfect','CommonMistakes',''],
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesCountableUncountableTheorySeeder'=>['common-mistakes','countable-vs-uncountable-nouns-common-mistakes','CommonMistakesCountableUncountable','CommonMistakes',''],
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesPrepositionsTheorySeeder'=>['common-mistakes','prepositions-common-mistakes','CommonMistakesPrepositions','CommonMistakes',''],
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesB1B2GrammarTrapsTheorySeeder'=>['common-mistakes','b1-b2-grammar-traps','CommonMistakesB1B2GrammarTraps','CommonMistakes',''],
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarA1MixedRevisionTheorySeeder'=>['mixed-revision','a1-mixed-revision','A1MixedRevision','MixedRevision','PolyglotFinalDrillLessonSeeder'],
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarA2MixedRevisionTheorySeeder'=>['mixed-revision','a2-mixed-revision','A2MixedRevision','MixedRevision','PolyglotFinalDrillA2LessonSeeder'],
    ];
    $scope=$scopes[$page['page_seeder_class']??'']??null;
    if ($scope===null || ($page['category_slug']??null)!==$scope[0] || ($page['slug']??null)!==$scope[1]
        || ($page['route']??null)!=='/theory/'.$scope[0].'/'.$scope[1]) {
PHP;
$manifestHook = <<<'PHP'
    $primary='Database\\Seeders\\V3\\Polyglot\\Polyglot'.$scope[2].'AllLevelsLessonSeeder';
    $standard='Database\\Seeders\\V3\\'.$scope[3].'\\'.$scope[2].'AllLevelsV3Seeder';
    $legacy=$scope[4]===''?[]:['Database\\Seeders\\V3\\Polyglot\\'.$scope[4]];
    $expectedBanks=[$primary,$standard,...$legacy];
    $tests=$manifest['tests_on_page']??[];$mixed=$tests[count($tests)-1]??[];
    $kinds=$legacy===[]?['direct_sentence_builder','mixed_a1_c2']:['direct_sentence_builder','legacy_sentence_builder','mixed_a1_c2'];
    if (array_column($tests,'kind')!==$kinds || ($tests[0]['seeder_class']??null)!==$primary
        || ($mixed['expected_seeder_classes']??null)!==[$standard,$primary] || isset($mixed['seeder_class'])
        || ($manifest['expected_standard_seeders']??null)!==[$standard]
        || ($manifest['expected_polyglot_seeders']??null)!==[$primary,...$legacy]
        || ($legacy!==[]&&($tests[1]['seeder_class']??null)!==$legacy[0])) {
        throw new RuntimeException('M82 exact direct/legacy-final/mixed original-bank declaration differs.');
    }
    // Course final-drill banks are read-only consumers, never the theory widget replacement.
    foreach (array_map(static fn($bank)=>['seeder_class'=>$bank],$expectedBanks) as $test) {
PHP;
$sourceGuard='~^database/seeders/V3/(?:TheoryLinks/data/(?:common-mistakes-(?:present-perfect|countable-uncountable|prepositions|b1-b2-grammar-traps)|mixed-revision-a[12]-mixed-revision)-theory-links\\.json|Polyglot/(?:PolyglotCommonMistakes(?:PresentPerfect|CountableUncountable|Prepositions|B1B2GrammarTraps)AllLevelsLessonSeeder|PolyglotA[12]MixedRevisionAllLevelsLessonSeeder|PolyglotFinalDrill(?:A2)?LessonSeeder)/definition\\.json|CommonMistakes/CommonMistakes(?:PresentPerfect|CountableUncountable|Prepositions|B1B2GrammarTraps)AllLevelsV3Seeder/definition\\.json|MixedRevision/A[12]MixedRevisionAllLevelsV3Seeder/definition\\.json|Polyglot/(?:Course/polyglot-english-a[12]\\.json|(?:PolyglotArticlesAAnTheLessonSeeder|PolyglotBeGoingToLessonSeeder|PolyglotCanCannotLessonSeeder|PolyglotComparativesLessonSeeder|PolyglotFinalDrillA2LessonSeeder|PolyglotFinalDrillLessonSeeder|PolyglotFirstConditionalLessonSeeder|PolyglotFutureSimpleWillLessonSeeder|PolyglotGerundVsInfinitiveLessonSeeder|PolyglotHaveGotHasGotLessonSeeder|PolyglotMuchManyALotOfLessonSeeder|PolyglotMustHaveToLessonSeeder|PolyglotPassiveVoiceBasicsLessonSeeder|PolyglotPastContinuousLessonSeeder|PolyglotPastSimpleIrregularVerbsLessonSeeder|PolyglotPastSimpleRegularVerbsLessonSeeder|PolyglotPastSimpleToBeLessonSeeder|PolyglotPresentContinuousLessonSeeder|PolyglotPresentPerfectBasicLessonSeeder|PolyglotPresentPerfectTimeExpressionsLessonSeeder|PolyglotPresentPerfectVsPastSimpleLessonSeeder|PolyglotPresentSimpleVerbsLessonSeeder|PolyglotQuestionTagsBasicsLessonSeeder|PolyglotRelativeClausesLessonSeeder|PolyglotReportedSpeechBasicsLessonSeeder|PolyglotSecondConditionalBasicsLessonSeeder|PolyglotShouldOughtToLessonSeeder|PolyglotSomeAnyLessonSeeder|PolyglotSuperlativesLessonSeeder|PolyglotThereIsThereAreLessonSeeder|PolyglotToBeLessonSeeder|PolyglotUsedToLessonSeeder)/definition\\.json))$~D';
$coursePrelude = <<<'PHP'
$courseSourcePaths = [
    'database/seeders/V3/Polyglot/PolyglotArticlesAAnTheLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotBeGoingToLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotCanCannotLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotComparativesLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotFinalDrillA2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotFinalDrillLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotFirstConditionalLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotFutureSimpleWillLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotGerundVsInfinitiveLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotHaveGotHasGotLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotMuchManyALotOfLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotMustHaveToLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotPassiveVoiceBasicsLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotPastContinuousLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotPastSimpleIrregularVerbsLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotPastSimpleRegularVerbsLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotPastSimpleToBeLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotPresentContinuousLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotPresentPerfectBasicLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotPresentPerfectVsPastSimpleLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotPresentSimpleVerbsLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotQuestionTagsBasicsLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotRelativeClausesLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotReportedSpeechBasicsLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotSecondConditionalBasicsLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotShouldOughtToLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotSomeAnyLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotSuperlativesLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotThereIsThereAreLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotToBeLessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotUsedToLessonSeeder/definition.json',
];
$courseSourceBySlug=[];
foreach ($courseSourcePaths as $path) {
    $definition=$readSource($path);$saved=$definition['saved_test']??[];
    $slug=$saved['slug']??'';
    if (!is_string($slug)||isset($courseSourceBySlug[$slug])||!preg_match('/^polyglot-[a-z0-9-]+-a[12]$/D',$slug)) {
        throw new RuntimeException('M82 course source slug is absent, duplicate or foreign.');
    }
    $courseSourceBySlug[$slug]=$definition;
}
$courseBlueprints=[];
foreach (['a1','a2'] as $level) {
    $blueprint=$readSource('database/seeders/V3/Polyglot/Course/polyglot-english-'.$level.'.json');
    if (($blueprint['course_slug']??null)!=='polyglot-english-'.$level
        ||($blueprint['total_planned_lessons']??null)!==16||count($blueprint['lessons']??[])!==16) {
        throw new RuntimeException('M82 course blueprint identity/count differs.');
    }
    $courseBlueprints[$level]=$blueprint;
}
ksort($sourceHashes);
PHP;
$courseSql = <<<'PHP'
$courseBoundary=$readOnly(static function() use($db,$orderedRows,$courseBlueprints,$courseSourceBySlug):array {
    $resolver=new App\Support\Database\QuestionUuidResolver;$out=[];
    foreach ($courseBlueprints as $level=>$blueprint) {
        $courseSlug='polyglot-english-'.$level;$tests=[];$memberships=[];$allUuids=[];$comparisons=[];
        foreach($blueprint['lessons'] as $index=>$lesson) {
            $slug=$lesson['slug'];$source=$courseSourceBySlug[$slug]??null;
            if($source===null||($lesson['lesson_order']??null)!==$index+1) {
                throw new RuntimeException('M82 course lesson source/order is missing.');
            }
            $test=(array)$db->table('saved_grammar_tests')->where('slug',$slug)->sole();
            $filters=json_decode($test['filters'],true,flags:JSON_THROW_ON_ERROR);
            foreach(['course_slug'=>$courseSlug,'lesson_order'=>$index+1,
                'previous_lesson_slug'=>$lesson['previous_lesson_slug'],'next_lesson_slug'=>$lesson['next_lesson_slug'],
                'level'=>strtoupper($level),'mode'=>'compose_tokens'] as $key=>$expected) {
                if(($filters[$key]??null)!==$expected) { throw new RuntimeException('M82 course saved-test binding differs: '.$slug.'/'.$key); }
            }
            if($test['uuid']!==$source['saved_test']['uuid']) { throw new RuntimeException('M82 course saved-test UUID differs.'); }
            $links=$orderedRows('saved_grammar_test_questions',$db->table('saved_grammar_test_questions')->where('saved_grammar_test_id',$test['id']),['position']);
            $expected=$resolver->toPersistentMany($source['saved_test']['question_uuids']);
            $actual=array_column($links,'question_uuid');$sortedExpected=$expected;$sortedActual=$actual;sort($sortedExpected);sort($sortedActual);
            $comparisons[$slug]=['source_count'=>count($expected),'actual_count'=>count($actual),
                'ordered_membership_exact'=>$actual===$expected,'membership_set_exact'=>$sortedActual===$sortedExpected,
                'source_order_sha256'=>App\Services\M26ContentPatch::digest($expected),
                'actual_order_sha256'=>App\Services\M26ContentPatch::digest($actual),
                'actual_only_uuids'=>array_values(array_diff($actual,$expected)),
                'source_only_uuids'=>array_values(array_diff($expected,$actual))];
            $tests[$slug]=$test;$memberships[$slug]=$links;$allUuids=[...$allUuids,...$actual];
        }
        $manifest=app(App\Services\PolyglotCourseManifestService::class)->build($courseSlug);
        $courseStatus=app(App\Services\PolyglotCourseBlueprintService::class)->buildCourseStatus($courseSlug);
        $last=$manifest['lessons'][15]??[];
        if(($manifest['total_lessons']??null)!==16
            ||($courseStatus['manifest']??null)!==$manifest
            ||($courseStatus['counts']['implemented_total']??null)!==16
            ||($courseStatus['counts']['planned_total']??null)!==16
            ||array_column($manifest['lessons'],'slug')!==array_column($blueprint['lessons'],'slug')
            ||($last['slug']??null)!=='polyglot-final-drill-'.$level
            ||!is_int($last['question_count']??null)||$last['question_count']<1
            ||($last['completion']??null)!==['rolling_window'=>100,'min_rating'=>4.5]
            ||($last['next_lesson_slug']??null)!==null) {
            throw new RuntimeException('M82 actual course manifest/final-drill requirements differ.');
        }
        $out[$level]=['blueprint'=>$blueprint,'manifest'=>$manifest,'public_course'=>$courseStatus['course'],'saved_tests'=>$tests,
            'question_memberships'=>$memberships,
            'historical_source_membership_comparisons'=>$comparisons,
            'questions'=>$orderedRows('questions',$db->table('questions')->whereIn('uuid',array_values(array_unique($allUuids))))];
    }
    return ['courses'=>$out,'enrollment_and_progress_policy'=>'All actual SQL table fingerprints retain every available enrollment/progress/attempt row; no progress endpoint or course JS is executed.'];
});
echo $json(['course_boundary_sql_guard'=>'confirmed-read-only','courses'=>array_map(static function(array $course):array {
    $last=$course['manifest']['lessons'][15];
    return ['lessons'=>$course['manifest']['total_lessons'],'final_lesson_slug'=>$last['slug'],
        'final_question_count'=>$last['question_count'],'completion'=>$last['completion'],
        'historical_membership_differences'=>array_filter($course['historical_source_membership_comparisons'],static fn(array $item):bool=>!$item['ordered_membership_exact'])];
},$courseBoundary['courses'])]);
PHP;
$courseRoutes = <<<'PHP'
foreach(['a1','a2'] as $level) {
    $entry=$routePath('courses.show',['courseSlug'=>'sentence-builder-english-'.$level]);
    $routes[$entry['path']]=$entry+['kind'=>'course_catalog','expected_count'=>16,'course_level'=>$level];
}
ksort($routes);
PHP;
$courseFacts = <<<'PHP'
$courseFacts=static function(string $html,string $level)use($htmlFacts,$courseBoundary,$digest,$routePath):array {
    $needle='window.__POLYGLOT_COURSE_MANIFEST__ = ';
    if(substr_count($html,$needle)!==1){throw new RuntimeException('M82 course catalog has no unique public manifest.');}
    $start=strpos($html,$needle)+strlen($needle);$depth=0;$quoted=false;$escaped=false;$payload=null;
    for($i=$start,$len=strlen($html);$i<$len;++$i){
        $char=$html[$i];
        if($quoted){if($escaped){$escaped=false;}elseif($char==='\\'){$escaped=true;}elseif($char==='"'){$quoted=false;}continue;}
        if($char==='"'){$quoted=true;}elseif($char==='{'||$char==='['){++$depth;}
        elseif($char==='}'||$char===']'){if(--$depth===0){$payload=json_decode(substr($html,$start,$i+1-$start),true,flags:JSON_THROW_ON_ERROR);break;}}
    }
    $expected=$courseBoundary['courses'][$level]['manifest'];
    $expected['course']=$courseBoundary['courses'][$level]['public_course'];
    // Absolute public URLs depend on the active HTTP request, not the CLI APP_URL.
    // Construct only the three declared URL fields from independently checked GET routes.
    $expected['course']['compose_url']='http://gramlyze.loc'.$routePath('courses.show',[
        'courseSlug'=>'sentence-builder-english-'.$level])['path'];
    foreach($expected['lessons'] as &$lesson){
        $lesson['compose_url']='http://gramlyze.loc'.$routePath('test.step-compose',['slug'=>$lesson['public_slug']])['path'];
    }
    unset($lesson);
    $expected['first_lesson']['compose_url']=$expected['lessons'][0]['compose_url'];
    if(!is_array($payload)||($payload['course']??null)!==$expected['course']
        ||($payload['lessons']??null)!==$expected['lessons']
        ||($payload['first_lesson']??null)!==$expected['first_lesson']
        ||($payload['planned_total_lessons']??null)!==16) {
        $parts=['course'=>($payload['course']??null)!==$expected['course'],
            'lessons'=>($payload['lessons']??null)!==$expected['lessons'],
            'first_lesson'=>($payload['first_lesson']??null)!==$expected['first_lesson'],
            'planned_total'=>($payload['planned_total_lessons']??null)!==16];
        throw new RuntimeException('M82 course catalog visible runtime manifest differs from exact SQL course binding: '.implode(',',array_keys(array_filter($parts))));
    }
    $facts=$htmlFacts($html,false);$facts['public_manifest']=$payload;$facts['public_manifest_sha256']=$digest($payload);
    $facts['comparison_policy']='Exact public course manifest and metadata; no progress-sync/CSRF/cookies/full HTML retained, no JavaScript executed.';
    return $facts;
};
PHP;
$courseEolParity = <<<'PHP'
    $worktreeBytes=file_get_contents($worktree.'/'.$relative);
    $capturedEolPairs=[
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectBasicLessonSeeder/definition.json'=>[
            '95f4eae58f5c26486c6e4482a11fd4d3714bdf1e61bfe0c1b4a4029be83c9c71',
            '09b574a1638ee21051b986db58a64ec34271b1ccdf8df74941276163d0041e35'],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsLessonSeeder/definition.json'=>[
            'b136cb03e6ef6df3e0a2c3e27c2167b28790c3ec178595486d4702aaf836f85d',
            'd72e0f41b175a04d8d96bf3ce410fc888b0c5b937cb1d3f9efebe3fde947a729'],
    ];
    $pair=$capturedEolPairs[$relative]??null;
    $exactRawPair=$pair!==null&&is_string($bytes)&&is_string($worktreeBytes)
        &&hash_equals($pair[0],hash('sha256',$bytes))&&hash_equals($pair[1],hash('sha256',$worktreeBytes))
        &&str_replace("\r\n","\n",$bytes)===str_replace("\r\n","\n",$worktreeBytes);
    if ($bytes === false || ($bytes !== $worktreeBytes && !$exactRawPair)) {
PHP;
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',[
    ['M52','M82',43],['m52','m82',4],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBaseline,$baseline,1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','94312691251f96433ee72c3fe544db71767c795495874af3ba1c80c2cbb49c62',1],
    [$oldManifests,$manifests,1],
    ['~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\.json)$~D',$sourceGuard,1],
    [$oldGuard,$guard,1],
    ["    foreach (\$manifest['tests_on_page'] ?? [] as \$test) {",$manifestHook,1],
    ["        if (!preg_match('/^Database\\\\\\\\Seeders\\\\\\\\V3\\\\\\\\(?:Polyglot|PassiveVoice)\\\\\\\\[A-Za-z0-9]+$/D', \$class)) {","        if (!in_array(\$class,\$expectedBanks,true)) {",1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["    \$http = \$getLocal(\$route); \$entry =","    echo \$json(['checking_consumer'=>\$path]);\n    \$http = \$getLocal(\$route); \$entry =",1],
    ['ksort($declarations); ksort($pages); ksort($sourceHashes);', "ksort(\$declarations); ksort(\$pages); ksort(\$sourceHashes);\n".$coursePrelude, 1],
    ["\$sqlBefore = \$readOnly(\$sqlSnapshot);", $courseSql."\n\$sqlBefore = \$readOnly(\$sqlSnapshot);", 1],
    ["~^/(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+$~D", "~^/(?:test/[a-z0-9/-]+|courses/english-grammar-theory/lesson/[a-z0-9/-]+|courses/sentence-builder-english-a[12])$~D", 1],
    ['ksort($routes);', $courseRoutes, 1],
    ["\$before = null;", $courseFacts."\n\$before = null;", 1],
    ["\$before['old_sql'] ?? null) !== \$sqlBefore", "\$before['old_sql'] ?? null) !== \$sqlBefore || (\$before['course_boundary'] ?? null) !== \$courseBoundary", 1],
    ["'old_sql' => \$sqlBefore, 'http' => []", "'old_sql' => \$sqlBefore, 'course_boundary' => \$courseBoundary, 'http' => []", 1],
    ["\$route['kind'] !== 'course' ||", "!in_array(\$route['kind'], ['course','course_catalog'], true) ||", 1],
    ["    } elseif (\$route['kind'] === 'course') {", "    } elseif (\$route['kind'] === 'course_catalog') {\n        \$entry['facts'] = \$courseFacts(\$http['body'], \$route['course_level']);\n    } elseif (\$route['kind'] === 'course') {", 1],
    ["    if (\$bytes === false || \$bytes !== file_get_contents(\$worktree.'/'.\$relative)) {", $courseEolParity, 1],
],$context);
