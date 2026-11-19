<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned prepositions-phrasal-verbs banks; historical questions, courses and progress stay intact. */
final class PrepositionsPhrasalPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'ppq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'prepositions-of-time' => 'PrepositionsOfTime',
        'prepositions-of-place' => 'PrepositionsOfPlace',
        'prepositions-of-movement' => 'PrepositionsOfMovement',
        'dependent-prepositions' => 'DependentPrepositions',
        'phrasal-verbs-basics' => 'PhrasalVerbsBasics',
        'separable-and-inseparable-phrasal-verbs' => 'SeparableInseparablePhrasalVerbs',
        'common-phrasal-verbs-by-topic' => 'CommonPhrasalVerbsByTopic',
    ];
    private const UUID_TOPICS = [
        'prepositions-of-time' => 'time',
        'prepositions-of-place' => 'place',
        'prepositions-of-movement' => 'movement',
        'dependent-prepositions' => 'dependent',
        'phrasal-verbs-basics' => 'basics',
        'separable-and-inseparable-phrasal-verbs' => 'separable',
        'common-phrasal-verbs-by-topic' => 'common',
    ];
    protected const UUID_PREFIX = 'ppq-';
    protected const SOURCE_PREFIX = 'prepositions_phrasal_quality_';
    protected const NAMESPACE_PREFIX = 'prepositions-phrasal-verbs-quality-';
    protected const LABEL = 'Prepositions and Phrasal Verbs Quality';
    protected const META_PREFIX = 'prepositions_phrasal_quality';
    public const CATEGORY_ID = 19;
    public const CATEGORY_SLUG = 'prepositions-and-phrasal-verbs';
    public const CATEGORY_TOPICS = ['prepositions-of-time','prepositions-of-place','prepositions-of-movement','dependent-prepositions','phrasal-verbs-basics','separable-and-inseparable-phrasal-verbs','common-phrasal-verbs-by-topic'];
    public const SAVED_TEST_COUNT = 14;
    /** Exact observed neutral descriptors; populated before runtime registration. */
    public const RETAINED_TEST_PROFILES = [
        'prepositions-of-time' => [],
        'prepositions-of-place' => [],
        'prepositions-of-movement' => [],
        'dependent-prepositions' => [],
        'phrasal-verbs-basics' => [],
        'separable-and-inseparable-phrasal-verbs' => [],
        'common-phrasal-verbs-by-topic' => [],
    ];
    /** Only exact frozen ancillary banks may coexist; no foreign source is inferred by a label. */
    public const RETAINED_DEFINITIONS = [
        'prepositions-of-time' => [],
        'prepositions-of-place' => [],
        'prepositions-of-movement' => [],
        'dependent-prepositions' => [],
        'phrasal-verbs-basics' => [],
        'separable-and-inseparable-phrasal-verbs' => [],
        'common-phrasal-verbs-by-topic' => [],
    ];
    /** Exact fresh read-only captured Page/category/seeder identities. */
    public const PAGES = [
        'prepositions-of-time' => [120,19,'prepositions-of-time','Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfTimeTheorySeeder'],
        'prepositions-of-place' => [119,19,'prepositions-of-place','Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfPlaceTheorySeeder'],
        'prepositions-of-movement' => [118,19,'prepositions-of-movement','Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfMovementTheorySeeder'],
        'dependent-prepositions' => [115,19,'dependent-prepositions','Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\DependentPrepositionsTheorySeeder'],
        'phrasal-verbs-basics' => [116,19,'phrasal-verbs-basics','Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PhrasalVerbsBasicsTheorySeeder'],
        'separable-and-inseparable-phrasal-verbs' => [117,19,'separable-and-inseparable-phrasal-verbs','Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PhrasalVerbsSeparableInseparableTheorySeeder'],
        'common-phrasal-verbs-by-topic' => [114,19,'common-phrasal-verbs-by-topic','Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\CommonPhrasalVerbsByTopicTheorySeeder'],
    ];
    private const ORIGINALS = [
        'prepositions-of-time' => ['standard'=>['pot-v3-all','ebcc5c348bf9a3c3dffe90e4c949334acf5ffe10afb7371565a29aa59925d464'], 'builder'=>['pot-poly-all','0ca0cefa873f46987aa7b7b38416130500edb0ce8a664d8eb2167899965986fb']],
        'prepositions-of-place' => ['standard'=>['pop-v3-all','28b5e3c26f6086bd349ac150238c85860edc56714746d53ebbea9371eea73494'], 'builder'=>['pop-poly-all','7693efba14da601dd13b3a622a1650c4b11fd71700d00c505c26492e81051e7c']],
        'prepositions-of-movement' => ['standard'=>['pom-v3-all','efaf6216fc1b850386cc6d2a5f2158aff5880c0f5a1823deaae8d4298c838ae0'], 'builder'=>['pom-poly-all','9b81087e42fecab0fead8045187485b68b025c01359b5efa987a0fdde22a3778']],
        'dependent-prepositions' => ['standard'=>['dep-v3-all','a387ecec5630510e28220b702f7ff4ac85c6d13e53d06d46ac8c55cf78a23458'], 'builder'=>['dep-poly-all','03f1e3e15526aba1044b77cb8debf2f1cbb563579ecf0a7b92eccc80f05168d7']],
        'phrasal-verbs-basics' => ['standard'=>['pvb-v3-all','4bc41a20cf5bb6b4539ae1b57c7d450f0c613c572c648d28cd8c21ca77df6f06'], 'builder'=>['pvb-poly-all','b6dde3b88ddfd2ca495cd9dee88acc26583eaa9629d265df85519413b5c8148c']],
        'separable-and-inseparable-phrasal-verbs' => ['standard'=>['sip-v3-all','0a8e342e7adda5233ced299250203c3aeaaac518f4c65da74d22421c7131bcc3'], 'builder'=>['sip-poly-all','5a9e32ceab77caacd04e735fa4f7317520cc1ce8c186ee2f3a176386a638901a']],
        'common-phrasal-verbs-by-topic' => ['standard'=>['cpt-v3-all','8b19e94e57e95efe99d3182b3fc6da712320da2f0c7f9fcee73645836397bfd2'], 'builder'=>['cpt-poly-all','7680ec533b62f5a27912f63e2d01f05d05cc3d5d81a210c339a28f6c0f8dc33e']],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Prepositions and Phrasal Verbs practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/prepositions-phrasal-verbs-quality/'.$topic.'.v1.json'];
        $map['additional_originals']=[];
        foreach (self::RETAINED_DEFINITIONS[$topic] as $class=>$binding) {
            $map['additional_originals'][]=['class'=>$class,'path'=>$binding[0],'sha256'=>$binding[1]];
        }
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'PrepositionsAndPhrasalVerbs' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'PrepositionsAndPhrasalVerbs/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
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
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Prepositions and Phrasal Verbs author identity.'); }
        return self::UUID_PREFIX.self::UUID_TOPICS[$topic].'-'.strtolower($authorId);
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    /** Only finite reviewed aliases belong to this catalog. */
    private static function ownsQuestionUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^ppq-(?:time|place|movement|dependent|basics|separable|common)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/D', $uuid) === 1;
    }

    protected static function requiredRowsPerLevel(): array { return ['gap' => 2, 'compose' => [1]]; }


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
            $payload['ppq_option_caption_revision']=$payload['psp_content_revision'];
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
            if (($payload['ppq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
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

    /** A declarative profile alone must not opt a foreign/partially migrated page into prepositions-phrasal-verbs behavior. */
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
