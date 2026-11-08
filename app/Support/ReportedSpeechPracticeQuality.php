<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned reported-speech banks; historical questions, courses and progress stay intact. */
final class ReportedSpeechPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'rsq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'reported-statements' => 'ReportedSpeechStatements',
        'reported-questions' => 'ReportedSpeechQuestions',
        'reported-commands-and-requests' => 'ReportedSpeechCommandsRequests',
        'backshift-of-tenses' => 'ReportedSpeechBackshiftOfTenses',
        'time-and-place-changes' => 'ReportedSpeechTimePlaceChanges',
        'say-tell-ask' => 'ReportedSpeechSayTellAsk',
        'advanced-reported-speech' => 'AdvancedReportedSpeech',
    ];
    private const UUID_TOPICS = [
        'reported-statements' => 'statements',
        'reported-questions' => 'questions',
        'reported-commands-and-requests' => 'commands',
        'backshift-of-tenses' => 'backshift',
        'time-and-place-changes' => 'timeplace',
        'say-tell-ask' => 'saytell',
        'advanced-reported-speech' => 'advanced',
    ];
    protected const UUID_PREFIX = 'rsq-';
    protected const SOURCE_PREFIX = 'reported_speech_quality_';
    protected const NAMESPACE_PREFIX = 'reported-speech-quality-';
    protected const LABEL = 'Reported Speech Quality';
    protected const META_PREFIX = 'reported_speech_quality';
    public const CATEGORY_ID = 3;
    public const CATEGORY_SLUG = 'reported-speech';
    public const CATEGORY_TOPICS = ['reported-statements', 'reported-questions', 'reported-commands-and-requests', 'backshift-of-tenses', 'time-and-place-changes', 'say-tell-ask', 'advanced-reported-speech'];
    public const SAVED_TEST_COUNT = 14;
    /** Exact local course and legacy descriptors remain neutral; unknown metadata never opts in. */
    public const RETAINED_TEST_PROFILES = [
        'reported-statements' => ['2d8d9e14-42ab-9e62-3e86-e21ba5bdae53'=>['course-td-reported-statements','de0deabf3c2921de0c0d16cb30eb84dbc39714696fdbf421aac17b148ae1e273'], 'polyglot-reported-speech-basi-17d4a4'=>['polyglot-reported-speech-basics-a2','871e98ec385505d99fb96ccdd345247734a6dfb6ecdb38799b47b4030bd3bc6b']],
        'reported-questions' => ['749214b4-4af7-7cb1-e348-1dbada84a9b8'=>['course-td-reported-questions','5037c18dd1abc8334461ff9c8dcbe008e97efc7e0fe8121df186b4b04069454a'], 'polyglot-reported-questions-b-1b6872'=>['polyglot-reported-questions-b1','5e980f82234301f6d3a209e640b5672445ebf6825faa33412d3de99db98cf365']],
        'reported-commands-and-requests' => ['e43f6415-e8da-b912-1245-dfc861c14bd6'=>['course-td-reported-commands-and-requests','1cded2bd3c785444122816104ce6dfa69111e1b950a6808cd42fe0d1594043af'], 'polyglot-reported-commands-an-71cc67'=>['polyglot-reported-commands-and-requests-b1','559f0b7b496954cb353f85ea36f3004665517ebee901222b92991c2662107045']],
        'backshift-of-tenses' => [],
        'time-and-place-changes' => [],
        'say-tell-ask' => [],
        'advanced-reported-speech' => ['4657a41d-b386-3e5e-ed2a-2ad8e70ee220'=>['course-td-advanced-reported-speech','7c840aa4893b9a3dce413869debfe3ec8f252146c1917d02bcef67a4e2f5a589'], 'polyglot-advanced-reported-sp-fbc2ad'=>['polyglot-advanced-reported-speech-c1','17afbdc8844a993b6a861ed0a95745fdb878b5ccb9fcc389346caa204f827d00'], 'polyglot-advanced-reporte-c3477a9846'=>['polyglot-advanced-reported-speech-b2','303fe76ddaed98f3bb78c70155d9ea3b854fe8c9b7d6a018db71b54d85a1a7c7']],
    ];
    /** Nonselected historical definitions are neutral only at these exact source bytes. */
    public const RETAINED_DEFINITIONS = [
        'reported-statements' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotReportedSpeechBasicsLessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotReportedSpeechBasicsLessonSeeder/definition.json','e609ada08927838534dbb2978bb356bf5d7a0249c658a5c89ee7e95a2ca83ce3']],
        'reported-questions' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotReportedQuestionsLessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotReportedQuestionsLessonSeeder/definition.json','2eb577d5694dcdd7f0d8f056c6bf7cf8c4b0c944f0f4b2c678f48154233a060e']],
        'reported-commands-and-requests' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotReportedCommandsAndRequestsLessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotReportedCommandsAndRequestsLessonSeeder/definition.json','89c4038595139eb440c49638e41566b3e35821b70cb03bdaea9d29319111a6ca']],
        'backshift-of-tenses' => [],
        'time-and-place-changes' => [],
        'say-tell-ask' => [],
        'advanced-reported-speech' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedReportedSpeechB2LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotAdvancedReportedSpeechB2LessonSeeder/definition.json','c4f811eb72d35d7a63596d55fa154b53b58422c04455342b77f124964a7cc84c'], 'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedReportedSpeechC1LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotAdvancedReportedSpeechC1LessonSeeder/definition.json','2a911325858078c555f947edf53b0b3f1e73ea5d0db6842a7dfc175f227cd37b']],
    ];
    /** Exact read-only captured Page/category/seeder bindings. */
    public const PAGES = [
        'reported-statements' => [3,3,'reported-statements','Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechStatementsTheorySeeder'],
        'reported-questions' => [143,3,'reported-questions','Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechQuestionsTheorySeeder'],
        'reported-commands-and-requests' => [142,3,'reported-commands-and-requests','Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechCommandsRequestsTheorySeeder'],
        'backshift-of-tenses' => [141,3,'backshift-of-tenses','Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechBackshiftTheorySeeder'],
        'time-and-place-changes' => [145,3,'time-and-place-changes','Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechTimePlaceChangesTheorySeeder'],
        'say-tell-ask' => [144,3,'say-tell-ask','Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechSayTellAskTheorySeeder'],
        'advanced-reported-speech' => [283,3,'advanced-reported-speech','Database\\Seeders\\Page_V3\\ReportedSpeech\\AdvancedReportedSpeechTheorySeeder'],
    ];
    private const ORIGINALS = [
        'reported-statements' => ['standard'=>['rsst-v3-all','16d6f214cab6679cf430dea8d6c9d9a3b9defc0b58b7942028cc529f8b0872e4'], 'builder'=>['rsst-poly-all','3f45ea2d9846e32a681655bdc32101080fe4e9bc8e55578d6467533586fc2add']],
        'reported-questions' => ['standard'=>['rsq-v3-all','de9e235ce4def63bda00f94a162d05fe8b7fe357ebc49e26270f5c55ebfcfb61'], 'builder'=>['rsq-poly-all','d7cd833954f85a5d4fc6b4a52844689ecd2545a1eb312ca85dcb07cee8411963']],
        'reported-commands-and-requests' => ['standard'=>['rsc-v3-all','ebd1cc52f13451a2f3cb436a98e5e3de77c076d412097c649e257eac4ebc1c31'], 'builder'=>['rsc-poly-all','a21ff47abf8f2acad7d94b569241bd61876cda697b0fb3001470924f6bcf79ef']],
        'backshift-of-tenses' => ['standard'=>['rsb-v3-all','95811d689413202b956863f58e03aee086a5c48a887c1efb3baec7b20b96e1fa'], 'builder'=>['rsb-poly-all','88d776f342e948b32c9b1ef6c269f58a3a7d1cfce9474e50ad4bc49cc44f819c']],
        'time-and-place-changes' => ['standard'=>['rstpc-v3-all','f760f3ac62c65e76fc3118531c37bb4cfa45706122b74111b866e3cc6357de38'], 'builder'=>['rstpc-poly-all','bb75c790c22d72450dad7ef87e6b4f0195e5e78be938d43df78537af025140df']],
        'say-tell-ask' => ['standard'=>['rssay-v3-all','55f8aeb7fc607581e4c5245f2655862ca7c965fcf815c7299351fb9eabe87c11'], 'builder'=>['rssay-poly-all','4c3390dd83d1a038a2d5f28bdd2eaaffa0177a23e2e7e848b2bc986e1fea69bc']],
        'advanced-reported-speech' => ['standard'=>['ars-v3-all','9ffe95493017c5342b12c203ad10ebf0820df6862a03e017b9a6b5dffdee3b91'], 'builder'=>['ars-poly-all','07f2be3b455a8407fbb3c69cae7e80941420789de4b71a721afd375aa930b47d']],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Reported Speech practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/reported-speech-quality/'.$topic.'.v1.json'];
        $map['additional_originals']=[];
        foreach (self::RETAINED_DEFINITIONS[$topic] as $class=>$binding) {
            $map['additional_originals'][]=['class'=>$class,'path'=>$binding[0],'sha256'=>$binding[1]];
        }
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'ReportedSpeech' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'ReportedSpeech/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
            $map[$kind] = ['class'=>'Database\\Seeders\\V3\\'.$namespace.'\\'.$name,
                'definition'=>$directory.'/definition.json', 'loader'=>$directory.'.php', 'implementation'=>$directory.'/'.$name.'.php',
                'old_definition'=>'database/seeders/V3/'.$oldName.'/definition.json', 'old_class'=>'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid'=>self::ORIGINALS[$topic][$kind][0], 'old_sha256'=>self::ORIGINALS[$topic][$kind][1]];
        }
        return $map;
    }

    /** Finite aliases keep the authored level/ID visible within the persistent36-byte limit. */
    public static function questionUuid(string $topic, string $authorId): string
    {
        self::mapping($topic);
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Reported Speech author identity.'); }
        return self::UUID_PREFIX.self::UUID_TOPICS[$topic].'-'.strtolower($authorId);
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    /** The historical Questions bank also starts rsq-; only finite reviewed aliases belong here. */
    private static function ownsQuestionUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^rsq-(?:statements|questions|commands|backshift|timeplace|saytell|advanced)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/D', $uuid) === 1;
    }

    public static function compile(string $topic, string $kind): array
    {
        static $compiled = [];
        $source = self::source($topic); self::originalDefinition($topic,$kind);
        $key = $topic.'|'.$kind.'|'.hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
        if (isset($compiled[$key])) { return $compiled[$key]; }
        $definition = parent::compileAuthored($topic,$kind);
        return $compiled[$key] = $kind === 'builder' ? FinitePracticeChoiceOptions::compile($definition,$source,$topic,self::composeSlots(...)) : $definition;
    }

    private static function sourceBound(array $payload, array $source): bool
    {
        return ($payload['question']??null)===$source['question'] && (string)($payload['type']??'')===(string)$source['type']
            && ($payload['psp_content_revision']??null)===hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))
            && ($payload['answer_map']??null)===array_map(static fn($m)=>$m['answer'],$source['markers']);
    }

    public static function restoreClarifiedHints(array $payload, string $locale): array
    {
        if ($locale!=='uk' || !self::ownsQuestionUuid($payload['uuid']??null)) { return $payload; }
        $source = self::questions()[$payload['uuid']]??null;
        if (!$source || !self::sourceBound($payload,$source)) { return $payload; }
        $payload['verb_hint']=$source['verb_hint'];
        $payload['verb_hints']=array_fill_keys(array_keys($source['markers']),$source['verb_hint']);
        if ((string)$source['type']==='4') {
            $payload['hint']=$source['verb_hint'];
            if (array_key_exists('hintUk',$payload)) { $payload['hintUk']=$source['verb_hint']; }
        }
        return $payload;
    }

    public static function decorate(array $payload): array
    {
        if (!self::ownsQuestionUuid($payload['uuid']??null)) { return $payload; }
        $payload=parent::decorate($payload); $source=self::questions()[$payload['uuid']??'']??null;
        if ($source && self::sourceBound($payload,$source)) {
            if ((string)$source['type']==='0') { $payload['passive_phrase_word_inputs']=true; }
            $payload['rsq_option_caption_revision']=$payload['psp_content_revision'];
            $payload=self::restoreClarifiedHints($payload,'uk');
        }
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (!array_filter($payloads,static fn($row)=>self::ownsQuestionUuid($row['uuid']??null))) { return false; }
        if (parent::cachedNeedsRefresh($payloads)) { return true; }
        $sources=self::questions();
        foreach ($payloads as $payload) {
            $source=$sources[$payload['uuid']??'']??null; if (!$source) { continue; }
            if (($payload['rsq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
            if ((string)$source['type']==='0' && ($payload['passive_phrase_word_inputs']??null)!==true) { return true; }
            if (($payload['presentation']??null)===SentenceReorderQuestionFactory::PRESENTATION
                && (($payload['reorder_manual_input']??null)!==true || ($payload['reorder_groups']??null)!==$source['reorder_groups'])) { return true; }
            if (($payload['presentation']??null)===SentenceReorderQuestionFactory::PRESENTATION) {
                $tokens=$payload['reorder_tokens']??null;
                if (!is_array($tokens) || array_filter($tokens,static fn($t)=>!is_string($t))!==[]) { return true; }
                $expectedTokens=$source['reorder_groups']; sort($tokens,SORT_STRING); sort($expectedTokens,SORT_STRING);
                if ($tokens!==$expectedTokens) { return true; }
            }
            $expected=self::restoreClarifiedHints($payload,'uk');
            foreach (['verb_hint','verb_hints','hint'] as $field) {
                if (array_key_exists($field,$expected) && ($payload[$field]??null)!==$expected[$field]) { return true; }
            }
        }
        return false;
    }

    public static function savedBuilderDisplayCount(string $uuid, string $slug, array $filters, string $locale): ?int
    {
        if ($locale!=='uk') { return null; }
        foreach (array_keys(self::TOPICS) as $topic) {
            if ($uuid!==self::ORIGINALS[$topic]['builder'][0]) { continue; }
            try {
                $old=self::originalDefinition($topic,'builder')['saved_test'];
                if ($slug!==$old['slug'] || self::stateNamespace($filters,$locale,$uuid)===null) { return null; }
                return count(self::compile($topic,'builder')['questions']);
            } catch (\Throwable) { return null; }
        }
        return null;
    }

    /** Only current exact linked pairs supersede metadata in unchanged historical definitions. */
    public static function mixedProfileDefinitions(array $definitions, array $linkedTests, string $locale): array
    {
        $originalDefinitions=$definitions;
        $pair=self::profilePair($linkedTests,$locale);
        if ($pair===null) { return $originalDefinitions; }
        $definitions=self::definitionPair($definitions,$pair,$locale);
        if ($definitions===null || count($definitions)!==2) { return $originalDefinitions; }
        $linkedTests=$pair;
        $linked=[];
        foreach ($linkedTests as $test) {
            if (!$test instanceof \App\Models\SavedGrammarTest || isset($linked[(string)$test->uuid])) { return $originalDefinitions; }
            $linked[(string)$test->uuid]=$test;
        }
        foreach (array_keys(self::TOPICS) as $topic) {
            $map=self::mapping($topic);
            if (!isset($linked[$map['standard']['old_saved_test_uuid']],$linked[$map['builder']['old_saved_test_uuid']])) { continue; }
            if (self::classes(array_keys($definitions))!==self::classes([$map['standard']['old_class'],$map['builder']['old_class']])) { return $originalDefinitions; }
            try {
                $expected=[];
                foreach (['standard','builder'] as $kind) {
                    $bank=$map[$kind]; $current=self::definitionMetadata(self::originalDefinition($topic,$kind),$locale)['saved_test'];
                    $test=$linked[$bank['old_saved_test_uuid']];
                    if (($current['filters']['theory_page_mixed_type_profile']??null)!==self::REVISION
                        || (string)$test->slug!==$current['slug'] || !self::sameMetadata($test->filters,$current['filters'])) { return $originalDefinitions; }
                    $expected[$bank['old_class']]=$current;
                }
                $projected=[];
                foreach ($definitions as $class=>$definition) {
                    if (!is_array($definition) || ($definition['seeder']['class']??null)!==$class || !isset($expected[$class])) { return $originalDefinitions; }
                    $after=self::definitionMetadata($definition,$locale);
                    if ($after===$definition || ($after['saved_test']??null)!==$expected[$class]) { return $originalDefinitions; }
                    $projected[$class]=$after;
                }
                return $projected;
            } catch (\Throwable) { return $originalDefinitions; }
        }
        return $originalDefinitions;
    }

    /** A declarative profile alone must not opt a foreign/partially migrated page into reported-speech behavior. */
    public static function allowsMixedProfile(array $definitions, array $linkedTests, string $locale): bool
    {
        $pair=self::profilePair($linkedTests,$locale);
        if ($pair===null) { return false; }
        $definitions=self::definitionPair($definitions,$pair,$locale);
        if ($definitions===null || !in_array(count($definitions),[0,2],true)) { return false; }
        $linkedTests=$pair;
        $linked=[];
        foreach ($linkedTests as $test) {
            if (!$test instanceof \App\Models\SavedGrammarTest || isset($linked[(string)$test->uuid])) { return false; }
            $linked[(string)$test->uuid]=$test;
        }
        foreach (array_keys(self::TOPICS) as $topic) {
            $map=self::mapping($topic);
            if (!isset($linked[$map['standard']['old_saved_test_uuid']],$linked[$map['builder']['old_saved_test_uuid']])) { continue; }
            try {
                foreach (['standard','builder'] as $kind) {
                    $bank=$map[$kind]; $expected=self::definitionMetadata(self::originalDefinition($topic,$kind),'uk')['saved_test'];
                    $test=$linked[$bank['old_saved_test_uuid']];
                    if (($expected['filters']['theory_page_mixed_type_profile']??null)!==self::REVISION
                        || $test->slug!==$expected['slug'] || !self::sameMetadata($test->filters,$expected['filters'])) { return false; }
                }
                return $definitions===[] || self::mixedProfileDefinitions($definitions,$linkedTests,$locale)!==$definitions;
            } catch (\Throwable) { return false; }
        }
        return false;
    }

    /** Only the exact captured neutral course/legacy descriptors may coexist with the current owned pair. */
    private static function profilePair(array $linkedTests, string $locale): ?array
    {
        if ($locale!=='uk' || count($linkedTests)<2) { return null; }
        $linked=[];
        foreach ($linkedTests as $test) {
            if (!$test instanceof \App\Models\SavedGrammarTest || isset($linked[(string)$test->uuid])) { return null; }
            $linked[(string)$test->uuid]=$test;
        }
        foreach (array_keys(self::TOPICS) as $topic) {
            $map=self::mapping($topic); $standard=$map['standard']['old_saved_test_uuid']; $builder=$map['builder']['old_saved_test_uuid'];
            if (!isset($linked[$standard],$linked[$builder])) { continue; }
            foreach (array_diff_key($linked,[$standard=>true,$builder=>true]) as $uuid=>$test) {
                $binding=self::RETAINED_TEST_PROFILES[$topic][$uuid]??null;
                if ($binding===null || $test->slug!==$binding[0] || !is_array($test->filters)
                    || !hash_equals($binding[1],hash('sha256',M26DetailPackage::json($test->filters)))) { return null; }
            }
            return [$linked[$standard],$linked[$builder]];
        }
        return null;
    }

    /** Only the exact owned saved-filter comparison ignores JSON object member order. */
    private static function sameMetadata(mixed $actual, mixed $expected): bool
    {
        if (!is_array($actual) || !is_array($expected)) { return $actual===$expected; }
        if (array_is_list($actual)!==array_is_list($expected) || count($actual)!==count($expected)) { return false; }
        if (!array_is_list($actual)) { ksort($actual,SORT_STRING); ksort($expected,SORT_STRING); }
        if (array_keys($actual)!==array_keys($expected)) { return false; }
        foreach ($expected as $key=>$value) { if (!self::sameMetadata($actual[$key],$value)) { return false; } }
        return true;
    }

    /** Exact historical definition bytes remain visible but never replace the selected page banks. */
    private static function definitionPair(array $definitions, array $pair, string $locale): ?array
    {
        if ($definitions===[]) { return []; }
        if ($locale!=='uk' || count($definitions)<2) { return null; }
        foreach (array_keys(self::TOPICS) as $topic) {
            $map=self::mapping($topic);
            if ($pair[0]->uuid!==$map['standard']['old_saved_test_uuid'] || $pair[1]->uuid!==$map['builder']['old_saved_test_uuid']) { continue; }
            $selected=[];
            foreach ($definitions as $class=>$definition) {
                if (in_array($class,[$map['standard']['old_class'],$map['builder']['old_class']],true)) {
                    $selected[$class]=$definition; continue;
                }
                $binding=self::RETAINED_DEFINITIONS[$topic][$class]??null;
                if ($binding===null || !is_array($definition) || ($definition['seeder']['class']??null)!==$class) { return null; }
                $file=dirname(__DIR__,2).'/'.$binding[0];
                if (!is_file($file) || is_link($file) || !hash_equals($binding[1],hash_file('sha256',$file))
                    || $definition!==json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR)) { return null; }
            }
            return count($selected)===2?$selected:null;
        }
        return null;
    }

    private static function classes(array $classes): array
    {
        if (array_filter($classes,static fn($v)=>!is_string($v))!==[]) { return []; }
        $classes=array_values(array_unique(array_map('strtolower',$classes))); sort($classes); return $classes;
    }

    public static function ownsCategory(int $id, string $slug): bool
    {
        return $id===self::CATEGORY_ID && $slug===self::CATEGORY_SLUG;
    }

    public static function categoryProfile(int $id, string $slug, array $groups): ?string
    {
        if (!self::ownsCategory($id,$slug) || count($groups)!==count(self::PAGES)) { return null; }
        $seen=[];
        foreach ($groups as $group) {
            $page=$group['page']??null;
            if (!$page instanceof \App\Models\Page || ($group['base_filters']['theory_page_mixed_type_profile']??null)!==self::REVISION) { return null; }
            $topic=null;
            foreach (self::PAGES as $key=>$binding) { if ($binding[0]===(int)$page->getKey()) { $topic=$key; break; } }
            if ($topic===null || isset($seen[$topic]) || (int)$page->page_category_id!==self::CATEGORY_ID
                || (string)$page->slug!==self::PAGES[$topic][2] || (string)$page->seeder!==self::PAGES[$topic][3]
                || !is_array($group['seeder_classes']??null)) { return null; }
            $map=self::mapping($topic);
            if (self::classes($group['seeder_classes'])!==self::classes([$map['standard']['class'],$map['builder']['class']])) { return null; }
            $seen[$topic]=true;
        }
        return self::REVISION;
    }

    private static function validCategoryContext(array $filters): bool
    {
        if (($filters['theory_category_page_test']??false)!==true) { return true; }
        if (!self::ownsCategory((int)\Illuminate\Support\Arr::get($filters,'__meta.theory_category_id'),
            (string)\Illuminate\Support\Arr::get($filters,'__meta.theory_category_slug'))) { return false; }
        $groups=$filters['theory_category_page_groups']??[];
        if (!is_array($groups) || count($groups)!==count(self::PAGES)) { return false; }
        $seen=[];
        foreach ($groups as $group) {
            $topic=null;
            foreach (self::PAGES as $key=>$binding) { if ($binding[0]===($group['page_id']??null)) { $topic=$key; break; } }
            if ($topic===null || isset($seen[$topic]) || ($group['page_slug']??null)!==self::PAGES[$topic][2]
                || !is_array($group['seeder_classes']??null)) { return false; }
            $map=self::mapping($topic);
            if (self::classes($group['seeder_classes'])!==self::classes([$map['standard']['class'],$map['builder']['class']])) { return false; }
            $seen[$topic]=true;
        }
        return true;
    }

    public static function stateNamespace(array $filters, string $locale, string $uuid=''): ?string
    {
        return self::validCategoryContext($filters)?parent::stateNamespace($filters,$locale,$uuid):null;
    }

    public static function testContextNeedsRefresh(array $cached, array $filters, string $locale, string $uuid=''): bool
    {
        return self::validCategoryContext($filters) && parent::testContextNeedsRefresh($cached,$filters,$locale,$uuid);
    }
}

