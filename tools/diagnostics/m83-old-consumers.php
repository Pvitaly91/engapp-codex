<?php

/** Exact M83 seven-manifest/six-owner/B2-course profile of the accepted SELECT-only old-consumer procedure. */
require __DIR__.'/frozen-readonly-profile.php';
$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M83 audit requires a reviewed independent final receipt and raw SHA-256.');
    }
    $context = ['final_file'=>$argv[5], 'final_sha256'=>$argv[6]];
    $argv = array_slice($argv, 0, 5);
}
$oldBaseline = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M83 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M83 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$baseline = <<<'PHP'
if ($mode === 'audit') {
    $finalName=$profileContext['final_file']??''; $finalSha=$profileContext['final_sha256']??'';
    if (preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$finalName)!==1
        || preg_match('/^[a-f0-9]{64}$/D',$finalSha)!==1 || is_link($dir.'/'.$finalName)
        || !is_file($dir.'/'.$finalName) || !hash_equals($finalSha,hash_file('sha256',$dir.'/'.$finalName))) {
        throw new RuntimeException('M83 independent final receipt is missing, linked or changed.');
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
        throw new RuntimeException('M83 current tables must equal the independently reviewed AFTER with exact BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table=>$expected) {
        if (($allTablesBeforeHttp[$table]??null)!==$expected) { throw new RuntimeException('M83 consumer BEFORE SQL drift: '.$table); }
    }
}
PHP;
$oldDeclarations = <<<'PHP'
$manifests = [
    'database/seeders/V3/TheoryLinks/data/passive-voice-formation-rules-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/passive-voice-negatives-questions-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/passive-voice-modal-verbs-theory-links.json',
];
$declarations = []; $pages = [];
foreach ($manifests as $path) {
    $manifest = $readSource($path); $page = $manifest['page'] ?? [];
    if (($page['category_slug'] ?? null) !== 'passive-voice'
        || !in_array($page['page_seeder_class'] ?? null, [
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceNegativesQuestionsTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceModalVerbsTheorySeeder',
        ], true)
        || ($page['route'] ?? null) !== '/theory/passive-voice/'.($page['slug'] ?? '')
        || !str_starts_with($page['slug'] ?? '', 'theory-passive-voice-')) {
        throw new RuntimeException('M83 old consumer manifest page identity differs.');
    }
    $pages[$page['page_seeder_class']] = $page;
    foreach ($manifest['tests_on_page'] ?? [] as $test) {
        $class = $test['seeder_class'] ?? '';
        if ($class === '' && ($test['kind'] ?? null) === 'standard_v3') {
            $classes = $test['expected_seeder_classes'] ?? [];
            if (!is_array($classes) || count($classes) !== 1 || !is_string($classes[0] ?? null)) {
                throw new RuntimeException('M83 standard test must declare exactly one original bank.');
            }
            $class = $classes[0];
        }
        if (!preg_match('/^Database\\\\Seeders\\\\V3\\\\(?:Polyglot|PassiveVoice)\\\\[A-Za-z0-9]+$/D', $class)) {
            throw new RuntimeException('M83 declared old bank has an unexpected class.');
        }
        $relative = 'database/seeders/'.str_replace('\\', '/', substr($class, strlen('Database\\Seeders\\'))).'/definition.json';
        $definition = $readSource($relative); $saved = $definition['saved_test'] ?? [];
        if (($definition['seeder']['class'] ?? null) !== $class
            || !preg_match('/^[a-z0-9-]+$/D', $saved['slug'] ?? '')
            || !is_string($saved['uuid'] ?? null) || !is_array($saved['question_uuids'] ?? null)
            || ($saved['question_uuids'] ?? []) === []) {
            throw new RuntimeException('M83 old bank saved-test declaration is incomplete.');
        }
        $types = array_unique(array_map(static fn ($q) => (string) ($q['type'] ?? $definition['defaults']['type'] ?? ''), $definition['questions']));
        $declarations[$saved['slug']] = ['bank' => $class, 'definition' => $relative, 'saved' => $saved,
            'source_question_uuids' => array_map(static fn ($q) => $q['uuid'], $definition['questions']),
            'compose' => $types === ['4'], 'page_owner' => $page['page_seeder_class']];
    }
}
ksort($declarations); ksort($pages); ksort($sourceHashes);
PHP;
$declarations = <<<'PHP'
$manifests = [
    'database/seeders/V3/TheoryLinks/data/mixed-revision-b2-mixed-revision-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/basic-grammar-parts-of-speech-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/basic-grammar-sentence-structure-svo-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/basic-grammar-sentence-types-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/basic-grammar-imperatives-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/basic-grammar-imperatives-sit-down-dont-open-it-theory-links.json',
    'database/seeders/V3/TheoryLinks/data/basic-grammar-conjunctions-theory-links.json',
];
$scopes = [
    'Database\\Seeders\\Page_V3\\BasicGrammar\\B2MixedRevisionTheorySeeder' => ['mixed-revision', 'b2-mixed-revision'],
    'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarPartsOfSpeechTheorySeeder' => ['basic-grammar', 'parts-of-speech'],
    'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarSentenceStructureSvoTheorySeeder' => ['basic-grammar', 'sentence-structure-svo'],
    'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarSentenceTypesTheorySeeder' => ['basic-grammar', 'sentence-types'],
    'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarImperativesTheorySeeder' => ['basic-grammar', 'imperatives-sit-down-dont-open-it'],
    'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarConjunctionsTheorySeeder' => ['basic-grammar', 'basic-conjunctions-and-but-or-because-so'],
];
$expectedManifestBanks = [
    'database/seeders/V3/TheoryLinks/data/mixed-revision-b2-mixed-revision-theory-links.json' => ['Database\\Seeders\\V3\\MixedRevision\\B2MixedRevisionAllLevelsV3Seeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotB2MixedRevisionAllLevelsLessonSeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillB2LessonSeeder'],
    'database/seeders/V3/TheoryLinks/data/basic-grammar-parts-of-speech-theory-links.json' => ['Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\PartsOfSpeechCastiniMoviV3QuestionsOnlySeeder', 'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\PartsOfSpeechCastiniMoviV3QuestionsOnlySeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotPartsOfSpeechAllLevelsLessonSeeder'],
    'database/seeders/V3/TheoryLinks/data/basic-grammar-sentence-structure-svo-theory-links.json' => ['Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder', 'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder', 'Database\\Seeders\\V3\\BasicGrammar\\SentenceStructureSvoAllLevelsV3Seeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotSentenceStructureSvoAllLevelsLessonSeeder'],
    'database/seeders/V3/TheoryLinks/data/basic-grammar-sentence-types-theory-links.json' => ['Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\SentenceTypesVidiRecenV3QuestionsOnlySeeder', 'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\SentenceTypesVidiRecenV3QuestionsOnlySeeder', 'Database\\Seeders\\V3\\BasicGrammar\\SentenceTypesAllLevelsV3Seeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotSentenceTypesAllLevelsLessonSeeder'],
    'database/seeders/V3/TheoryLinks/data/basic-grammar-imperatives-theory-links.json' => ['Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\ImperativesNakazoviRecenniaV3QuestionsOnlySeeder', 'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\ImperativesNakazoviRecenniaV3QuestionsOnlySeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotImperativesAllLevelsLessonSeeder'],
    'database/seeders/V3/TheoryLinks/data/basic-grammar-imperatives-sit-down-dont-open-it-theory-links.json' => ['Database\\Seeders\\V3\\BasicGrammar\\ImperativesAllLevelsV3Seeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotImperativesAllLevelsLessonSeeder'],
    'database/seeders/V3/TheoryLinks/data/basic-grammar-conjunctions-theory-links.json' => ['Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder', 'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder', 'Database\\Seeders\\V3\\Polyglot\\PolyglotConjunctionsAllLevelsLessonSeeder'],
];
$allBankClasses = [
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\ImperativesNakazoviRecenniaV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\PartsOfSpeechCastiniMoviV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\SentenceTypesVidiRecenV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\ThereIsThereAreV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\VerbToBeFutureV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\VerbToBeNegativesV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\VerbToBePastFormsV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\VerbToBePresentFormsV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Opus46\\VerbToBeQuestionsAndShortAnswersV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\ImperativesNakazoviRecenniaV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\PartsOfSpeechCastiniMoviV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\SentenceTypesVidiRecenV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\VerbToBeFutureV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\VerbToBePresentFormsV3QuestionsOnlySeeder',
    'Database\\Seeders\\V3\\BasicGrammar\\ImperativesAllLevelsV3Seeder',
    'Database\\Seeders\\V3\\BasicGrammar\\SentenceStructureSvoAllLevelsV3Seeder',
    'Database\\Seeders\\V3\\BasicGrammar\\SentenceTypesAllLevelsV3Seeder',
    'Database\\Seeders\\V3\\MixedRevision\\B2MixedRevisionAllLevelsV3Seeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotB2MixedRevisionAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotConjunctionsAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillB2LessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotImperativesAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotPartsOfSpeechAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotSentenceStructureSvoAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotSentenceTypesAllLevelsLessonSeeder',
];
$declarations=[];$pages=[];$allDeclaredBanks=[];
foreach($manifests as $path) {
    $manifest=$readSource($path);$page=$manifest['page']??[];
    $scope=$scopes[$page['page_seeder_class']??'']??null;
    if($scope===null||($page['category_slug']??null)!==$scope[0]||($page['slug']??null)!==$scope[1]
        ||($page['route']??null)!=='/theory/'.$scope[0].'/'.$scope[1]) {
        throw new RuntimeException('M83 exact theory manifest identity differs.');
    }
    $owner=$page['page_seeder_class'];$pages[$owner]=$page;$banks=[];
    foreach(['expected_standard_seeders','expected_polyglot_seeders','expected_all_seeders'] as $field) {
        foreach($manifest[$field]??[] as $class){$banks[$class]=true;}
    }
    foreach($manifest['tests_on_page']??[] as $test) {
        if(isset($test['seeder_class'])){$banks[$test['seeder_class']]=true;}
        foreach(['expected_seeder_classes','expected_standard_seeders','expected_polyglot_seeders','expected_all_seeders'] as $field){
            foreach($test[$field]??[] as $class){$banks[$class]=true;}
        }
    }
    $actualBanks=array_keys($banks);sort($actualBanks);
    if($actualBanks!==$expectedManifestBanks[$path]){
        throw new RuntimeException('M83 manifest original-bank declarations differ.');
    }
    foreach($actualBanks as $class){
        if(!in_array($class,$allBankClasses,true)){throw new RuntimeException('M83 foreign declared old bank.');}
        $relative='database/seeders/'.str_replace('\\','/',substr($class,strlen('Database\\Seeders\\'))).'/definition.json';
        $definition=$readSource($relative);
        if(($definition['seeder']['class']??null)!==$class||!is_array($definition['questions']??null)||$definition['questions']===[]){
            throw new RuntimeException('M83 old bank source identity/questions differ.');
        }
        $allDeclaredBanks[$class]=$definition;$saved=$definition['saved_test']??null;
        // AI sources have saved metadata too, sometimes underscore slugs; preserve their actual SQL below.
        // Only original direct/standard/final declarations generate explicit public saved-test GETs.
        if(str_starts_with($class,'Database\\Seeders\\V3\\AI\\')||$saved===null){continue;}
        if(!is_array($saved)||!preg_match('/^[a-z0-9-]+$/D',$saved['slug']??'')||!is_string($saved['uuid']??null)){
            throw new RuntimeException('M83 original saved-test source is incomplete.');
        }
        if(!isset($saved['question_uuids'])){
            if(!in_array($class,[
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPartsOfSpeechAllLevelsLessonSeeder',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotConjunctionsAllLevelsLessonSeeder',
            ],true)){throw new RuntimeException('M83 undeclared saved membership fallback.');}
            // JsonTestSeeder::normalizeSavedTestQuestionUuids uses source order when omitted.
            $saved['question_uuids']=array_column($definition['questions'],'uuid');
        }
        if(!is_array($saved['question_uuids'])||$saved['question_uuids']===[]){
            throw new RuntimeException('M83 original saved membership is empty.');
        }
        $types=array_unique(array_map(static fn($q)=>(string)($q['type']??$definition['defaults']['type']??''),$definition['questions']));
        $entry=['bank'=>$class,'definition'=>$relative,'saved'=>$saved,
            'source_question_uuids'=>array_column($definition['questions'],'uuid'),'compose'=>$types===['4'],'page_owner'=>$owner];
        if(isset($declarations[$saved['slug']])&&$declarations[$saved['slug']]!==$entry){
            throw new RuntimeException('M83 duplicate saved declaration differs.');
        }
        $declarations[$saved['slug']]=$entry;
    }
}
// Finite foreign page-wide be/there links are protected as found, never promoted to widget candidates.
foreach($allBankClasses as $class){
    if(isset($allDeclaredBanks[$class])){continue;}
    $relative='database/seeders/'.str_replace('\\','/',substr($class,strlen('Database\\Seeders\\'))).'/definition.json';
    $definition=$readSource($relative);
    if(($definition['seeder']['class']??null)!==$class||!is_array($definition['questions']??null)){
        throw new RuntimeException('M83 supplemental existing-pagewide bank identity differs.');
    }
    $allDeclaredBanks[$class]=$definition;
}
ksort($allDeclaredBanks);ksort($declarations);ksort($pages);ksort($sourceHashes);
PHP;
$sourceGuard = '~^(?:database/seeders/V3/AI/Copilot/Opus46/ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Opus46/ImperativesNakazoviRecenniaV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Opus46/PartsOfSpeechCastiniMoviV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Opus46/SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Opus46/SentenceTypesVidiRecenV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Opus46/ThereIsThereAreV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Opus46/VerbToBeFutureV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Opus46/VerbToBeNegativesV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Opus46/VerbToBePastFormsV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Opus46/VerbToBePresentFormsV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Opus46/VerbToBeQuestionsAndShortAnswersV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Sonate/ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Sonate/ImperativesNakazoviRecenniaV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Sonate/PartsOfSpeechCastiniMoviV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Sonate/SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Sonate/SentenceTypesVidiRecenV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Sonate/VerbToBeFutureV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/AI/Copilot/Sonate/VerbToBePresentFormsV3QuestionsOnlySeeder/definition\\.json|database/seeders/V3/BasicGrammar/ImperativesAllLevelsV3Seeder/definition\\.json|database/seeders/V3/BasicGrammar/SentenceStructureSvoAllLevelsV3Seeder/definition\\.json|database/seeders/V3/BasicGrammar/SentenceTypesAllLevelsV3Seeder/definition\\.json|database/seeders/V3/MixedRevision/B2MixedRevisionAllLevelsV3Seeder/definition\\.json|database/seeders/V3/Polyglot/Course/polyglot-english-b2\\.json|database/seeders/V3/Polyglot/PolyglotAdvancedGerundInfinitivePatternsB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotAdvancedPassiveVoiceB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotAdvancedReportedSpeechB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotB2MixedRevisionAllLevelsLessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotCleftSentencesBasicsB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotConditionalsWithUnlessProvidedAsLongAsB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotConjunctionsAllLevelsLessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotFinalDrillB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotFormalRegisterAndNominalisationBasicsB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousBasicsB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotHedgingAndCautiousLanguageBasicsB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotImperativesAllLevelsLessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotInversionBasicsB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotLinkingWordsReasonResultContrastB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotMixedConditionalsBasicsB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotModalPerfectDeductionAndRegretB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotParticipleClausesBasicsB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotPartsOfSpeechAllLevelsLessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotPastPerfectContinuousBasicsB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotSentenceStructureSvoAllLevelsLessonSeeder/definition\\.json|database/seeders/V3/Polyglot/PolyglotSentenceTypesAllLevelsLessonSeeder/definition\\.json|database/seeders/V3/TheoryLinks/data/basic-grammar-conjunctions-theory-links\\.json|database/seeders/V3/TheoryLinks/data/basic-grammar-imperatives-sit-down-dont-open-it-theory-links\\.json|database/seeders/V3/TheoryLinks/data/basic-grammar-imperatives-theory-links\\.json|database/seeders/V3/TheoryLinks/data/basic-grammar-parts-of-speech-theory-links\\.json|database/seeders/V3/TheoryLinks/data/basic-grammar-sentence-structure-svo-theory-links\\.json|database/seeders/V3/TheoryLinks/data/basic-grammar-sentence-types-theory-links\\.json|database/seeders/V3/TheoryLinks/data/mixed-revision-b2-mixed-revision-theory-links\\.json)$~D';
$coursePrelude = <<<'PHP'
$courseSourcePaths = [
    'database/seeders/V3/Polyglot/PolyglotPastPerfectContinuousBasicsB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotMixedConditionalsBasicsB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousBasicsB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotModalPerfectDeductionAndRegretB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotAdvancedPassiveVoiceB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotAdvancedReportedSpeechB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotParticipleClausesBasicsB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotAdvancedGerundInfinitivePatternsB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotConditionalsWithUnlessProvidedAsLongAsB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotInversionBasicsB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotCleftSentencesBasicsB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotLinkingWordsReasonResultContrastB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotHedgingAndCautiousLanguageBasicsB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotFormalRegisterAndNominalisationBasicsB2LessonSeeder/definition.json',
    'database/seeders/V3/Polyglot/PolyglotFinalDrillB2LessonSeeder/definition.json',
];
$courseSourceBySlug=[];
foreach ($courseSourcePaths as $path) {
    $definition=$readSource($path);$saved=$definition['saved_test']??[];
    $slug=$saved['slug']??'';
    if (!is_string($slug)||isset($courseSourceBySlug[$slug])||!preg_match('/^polyglot-[a-z0-9-]+-b2$/D',$slug)) {
        throw new RuntimeException('M83 course source slug is absent, duplicate or foreign.');
    }
    $courseSourceBySlug[$slug]=$definition;
}
$courseBlueprints=[];
foreach (['b2'] as $level) {
    $blueprint=$readSource('database/seeders/V3/Polyglot/Course/polyglot-english-'.$level.'.json');
    if (($blueprint['course_slug']??null)!=='polyglot-english-'.$level
        ||($blueprint['total_planned_lessons']??null)!==16||count($blueprint['lessons']??[])!==16) {
        throw new RuntimeException('M83 course blueprint identity/count differs.');
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
                throw new RuntimeException('M83 course lesson source/order is missing.');
            }
            $test=(array)$db->table('saved_grammar_tests')->where('slug',$slug)->sole();
            $filters=json_decode($test['filters'],true,flags:JSON_THROW_ON_ERROR);
            foreach(['course_slug'=>$courseSlug,'lesson_order'=>$index+1,
                'previous_lesson_slug'=>$lesson['previous_lesson_slug'],'next_lesson_slug'=>$lesson['next_lesson_slug'],
                'level'=>strtoupper($level),'mode'=>'compose_tokens'] as $key=>$expected) {
                if(($filters[$key]??null)!==$expected) { throw new RuntimeException('M83 course saved-test binding differs: '.$slug.'/'.$key); }
            }
            if($test['uuid']!==$source['saved_test']['uuid']) { throw new RuntimeException('M83 course saved-test UUID differs.'); }
            $links=$orderedRows('saved_grammar_test_questions',$db->table('saved_grammar_test_questions')->where('saved_grammar_test_id',$test['id']),['position']);
            $expected=$resolver->toPersistentMany($source['saved_test']['question_uuids']);
            $actual=array_column($links,'question_uuid');$sortedExpected=$expected;$sortedActual=$actual;sort($sortedExpected);sort($sortedActual);
            $comparisons[$slug]=['source_count'=>count($expected),'actual_count'=>count($actual),
                'ordered_membership_exact'=>$actual===$expected,'membership_set_exact'=>$sortedActual===$sortedExpected,
                'source_order_sha256'=>App\Services\M26ContentPatch::digest($expected),
                'actual_order_sha256'=>App\Services\M26ContentPatch::digest($actual),
                'actual_only_uuids'=>array_values(array_diff($actual,$expected)),
                'source_only_uuids'=>array_values(array_diff($expected,$actual)),
                'source_completion'=>$source['saved_test']['filters']['completion']??null,
                'actual_completion'=>$filters['completion']??null,
                'source_prompt_generator'=>$source['saved_test']['filters']['prompt_generator']??null,
                'actual_prompt_generator'=>$filters['prompt_generator']??null];
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
            ||($last['question_count']??null)!==48
            ||($last['completion']??null)!==['rolling_window'=>100,'min_rating'=>4.5]
            ||($last['next_lesson_slug']??null)!==null) {
            throw new RuntimeException('M83 actual course manifest/final-drill requirements differ.');
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
        'final_question_count'=>$last['question_count'],'normalized_public_completion'=>$last['completion'],
        'actual_raw_final_completion'=>json_decode($course['saved_tests'][$last['slug']]['filters'],true,flags:JSON_THROW_ON_ERROR)['completion']??null,
        'historical_membership_differences'=>array_filter($course['historical_source_membership_comparisons'],static fn(array $item):bool=>!$item['ordered_membership_exact'])];
},$courseBoundary['courses'])]);
PHP;
$courseRoutes = <<<'PHP'
foreach(['b2'] as $level) {
    $entry=$routePath('courses.show',['courseSlug'=>'sentence-builder-english-'.$level]);
    $routes[$entry['path']]=$entry+['kind'=>'course_catalog','expected_count'=>16,'course_level'=>$level];
}
foreach($courseBoundary['courses']['b2']['manifest']['lessons'] as $lesson){
    $entry=$routePath('test.step-compose',['slug'=>$lesson['public_slug']]);
    $routes[$entry['path']]=$entry+['kind'=>'course_compose','expected_count'=>$lesson['question_count']];
}
ksort($routes);
PHP;
$courseFacts = <<<'PHP'
$courseFacts=static function(string $html,string $level)use($htmlFacts,$courseBoundary,$digest,$routePath):array {
    $needle='window.__POLYGLOT_COURSE_MANIFEST__ = ';
    if(substr_count($html,$needle)!==1){throw new RuntimeException('M83 course catalog has no unique public manifest.');}
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
        throw new RuntimeException('M83 course catalog visible runtime manifest differs from exact SQL course binding: '.implode(',',array_keys(array_filter($parts))));
    }
    $facts=$htmlFacts($html,false);$facts['public_manifest']=$payload;$facts['public_manifest_sha256']=$digest($payload);
    $facts['comparison_policy']='Exact public course manifest and metadata; no progress-sync/CSRF/cookies/full HTML retained, no JavaScript executed.';
    return $facts;
};
PHP;
$sqlSupplement = <<<'PHP'
    $uuidProjection=new class extends App\Support\Database\JsonTestSeeder {
        protected function definitionPath():string { throw new RuntimeException('M83 UUID projection cannot run a seeder.'); }
        public function readOnlySourceUuids(array $definition):array {
            $namespace=$this->resolveUuidNamespace($definition,$definition['seeder']['class']);$out=[];
            foreach($definition['questions'] as $index=>$question){
                $out[]=$this->resolveQuestionUuid($question,$namespace,$index,trim((string)$question['question']));
            }
            return $out;
        }
    };
    $result['all_bank_source_comparisons']=[];$result['supplemental_saved_tests']=[];$result['supplemental_saved_links']=[];
    $directBanks=array_column($declarations,'bank');
    foreach($allDeclaredBanks as $bank=>$definition){
        $source=$uuidProjection->readOnlySourceUuids($definition);
        $actual=array_column(array_filter($questions,static fn($q)=>$q['seeder']===$bank),'uuid');
        $sortedSource=$source;$sortedActual=$actual;sort($sortedSource);sort($sortedActual);
        $result['all_bank_source_comparisons'][$bank]=['source_count'=>count($source),'actual_count'=>count($actual),
            'source_uuid_set_exact'=>$sortedSource===$sortedActual,
            'source_only_uuids'=>array_values(array_diff($source,$actual)),'actual_only_uuids'=>array_values(array_diff($actual,$source))];
        if(in_array($bank,$directBanks,true)||!is_array($definition['saved_test']??null)){continue;}
        $saved=$definition['saved_test'];$slug=$saved['slug']??'';
        if(!is_string($slug)||preg_match('/^[a-z0-9_-]+$/D',$slug)!==1){throw new RuntimeException('M83 finite supplemental saved slug differs.');}
        $tests=$orderedRows('saved_grammar_tests',$db->table('saved_grammar_tests')->where('slug',$slug));
        if(count($tests)>1){throw new RuntimeException('M83 duplicate supplemental saved-test rows.');}
        $result['supplemental_saved_tests'][$bank]=['source_saved_metadata'=>$saved,'actual_rows'=>$tests];
        $result['supplemental_saved_links'][$bank]=$tests===[]?[]:$orderedRows('saved_grammar_test_questions',
            $db->table('saved_grammar_test_questions')->where('saved_grammar_test_id',$tests[0]['id']),['position']);
    }
    return $result;
};
$readOnly = static function (Closure $capture) use ($db) {
PHP;
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',[
    ['M52','M83',43],['m52','m83',4],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldBaseline,$baseline,1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','d522a715b123748b84f52f076b156ccf5224892f138d5429062d9527a843e33e',1],
    [$oldDeclarations,$declarations,1],
    ['~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\\.json)$~D',$sourceGuard,1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    ["    \$http = \$getLocal(\$route); \$entry =","    echo \$json(['checking_consumer'=>\$path]);\n    \$http = \$getLocal(\$route); \$entry =",1],
    ["ksort(\$allDeclaredBanks);ksort(\$declarations);ksort(\$pages);ksort(\$sourceHashes);","ksort(\$allDeclaredBanks);ksort(\$declarations);ksort(\$pages);ksort(\$sourceHashes);\n".$coursePrelude,1],
    ["use (\$db, \$rows, \$orderedRows, \$declarations): array {","use (\$db, \$rows, \$orderedRows, \$declarations, \$allDeclaredBanks): array {",1],
    ["\$banks = array_column(\$declarations, 'bank');","\$banks = array_keys(\$allDeclaredBanks);",1],
    ["\$sqlBefore = \$readOnly(\$sqlSnapshot);",$courseSql."\n\$sqlBefore = \$readOnly(\$sqlSnapshot);",1],
    ["~^/(?:test|courses/english-grammar-theory/lesson)/[a-z0-9/-]+$~D","~^/(?:test/[a-z0-9/-]+|courses/english-grammar-theory/lesson/[a-z0-9/-]+|courses/sentence-builder-english-b2)$~D",1],
    ['ksort($routes);',$courseRoutes,1],
    ["\$before = null;",$courseFacts."\n\$before = null;",1],
    ["\$before['old_sql'] ?? null) !== \$sqlBefore","\$before['old_sql'] ?? null) !== \$sqlBefore || (\$before['course_boundary'] ?? null) !== \$courseBoundary",1],
    ["'old_sql' => \$sqlBefore, 'http' => []","'old_sql' => \$sqlBefore, 'course_boundary' => \$courseBoundary, 'http' => []",1],
    ["\$route['kind'] !== 'course' ||","!in_array(\$route['kind'], ['course','course_catalog','course_compose'], true) ||",1],
    ["    } elseif (\$route['kind'] === 'course') {","    } elseif (\$route['kind'] === 'course_catalog') {\n        \$entry['facts'] = \$courseFacts(\$http['body'], \$route['course_level']);\n    } elseif (\$route['kind'] === 'course') {",1],
    ["    return \$result;\n};\n\$readOnly = static function (Closure \$capture) use (\$db) {",$sqlSupplement,1],
    ["if (\$route['kind'] === 'ssr') { \$entry['facts'] = \$htmlFacts(\$http['body'], false); }","if (in_array(\$route['kind'], ['ssr','course_compose'], true)) { \$entry['facts'] = \$htmlFacts(\$http['body'], false); }",1],
],$context);
