<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned verb-patterns banks; historical questions, courses and progress stay intact. */
final class VerbPatternsPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'vpq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = array (
  'gerund' => 'Gerund',
  'to-infinitive' => 'ToInfinitive',
  'bare-infinitive' => 'BareInfinitive',
  'gerund-vs-infinitive' => 'GerundVsInfinitive',
  'verbs-plus-gerund' => 'VerbsPlusGerund',
  'verbs-plus-infinitive' => 'VerbsPlusInfinitive',
  'stop-remember-forget-try-regret' => 'StopRememberForgetTryRegret',
  'be-used-to-get-used-to-used-to' => 'BeUsedToGetUsedToUsedTo',
  'advanced-gerund-infinitive-patterns' => 'AdvancedGerundInfinitivePatterns',
);
    private const UUID_TOPICS = array (
  'gerund' => 'gerund',
  'to-infinitive' => 'to',
  'bare-infinitive' => 'bare',
  'gerund-vs-infinitive' => 'contrast',
  'verbs-plus-gerund' => 'verbs-gerund',
  'verbs-plus-infinitive' => 'verbs-infinitive',
  'stop-remember-forget-try-regret' => 'meaning',
  'be-used-to-get-used-to-used-to' => 'used',
  'advanced-gerund-infinitive-patterns' => 'advanced',
);
    protected const UUID_PREFIX = 'vpq-';
    protected const SOURCE_PREFIX = 'verb_patterns_quality_';
    protected const NAMESPACE_PREFIX = 'verb-patterns-quality-';
    protected const LABEL = 'Verb Patterns Quality';
    protected const META_PREFIX = 'verb_patterns_quality';
    public const CATEGORY_ID = 33;
    public const CATEGORY_SLUG = 'verb-patterns';
    public const CATEGORY_TOPICS = array (
  0 => 'gerund',
  1 => 'to-infinitive',
  2 => 'bare-infinitive',
  3 => 'gerund-vs-infinitive',
  4 => 'verbs-plus-gerund',
  5 => 'verbs-plus-infinitive',
  6 => 'stop-remember-forget-try-regret',
  7 => 'be-used-to-get-used-to-used-to',
  8 => 'advanced-gerund-infinitive-patterns',
);
    public const SAVED_TEST_COUNT = 18;
    public const RETAINED_TEST_PROFILES = array (
  'gerund' => 
  array (
  ),
  'to-infinitive' => 
  array (
  ),
  'bare-infinitive' => 
  array (
  ),
  'gerund-vs-infinitive' => 
  array (
    'c2fb3f02-9823-eae0-518b-029843e9cb30' => 
    array (
      0 => 'course-td-gerund-vs-infinitive',
      1 => 'ea024f949cbee922c5e1ad6c5bd598889e5f6043aae3fe5c28e61afa07c8f7f2',
    ),
    'polyglot-gerund-vs-infinitive-306a85' => 
    array (
      0 => 'polyglot-gerund-vs-infinitive-a2',
      1 => 'c2a0fb8d1b92f2bb3f193c0cfff19f64d5bb2798881c33e19e51faa57e2eb95f',
    ),
  ),
  'verbs-plus-gerund' => 
  array (
  ),
  'verbs-plus-infinitive' => 
  array (
  ),
  'stop-remember-forget-try-regret' => 
  array (
  ),
  'be-used-to-get-used-to-used-to' => 
  array (
  ),
  'advanced-gerund-infinitive-patterns' => 
  array (
    'a5cac6bd-fe02-fc1a-7ddd-5f74a38f5a8e' => 
    array (
      0 => 'course-td-advanced-gerund-infinitive-patterns',
      1 => '7bab2f151e2e2b04e503788eb8a81a19e385b37d8cc6e4ca8f53e04682cad299',
    ),
    'polyglot-advanced-gerund--034af2dffa' => 
    array (
      0 => 'polyglot-advanced-gerund-infinitive-patterns-b2',
      1 => '591e433663a5d5c480408ac85ebe5809c9b33b995e03ca06756115af7fc00486',
    ),
  ),
);
    public const RETAINED_DEFINITIONS = array (
  'gerund' => 
  array (
  ),
  'to-infinitive' => 
  array (
  ),
  'bare-infinitive' => 
  array (
  ),
  'gerund-vs-infinitive' => 
  array (
    'Database\\Seeders\\V3\\Polyglot\\PolyglotGerundVsInfinitiveLessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotGerundVsInfinitiveLessonSeeder/definition.json',
      1 => 'd707c33ec5b56c62173de05b86c72362abb5f0e91998b9fca21e5eb13d04b46a',
    ),
  ),
  'verbs-plus-gerund' => 
  array (
  ),
  'verbs-plus-infinitive' => 
  array (
  ),
  'stop-remember-forget-try-regret' => 
  array (
  ),
  'be-used-to-get-used-to-used-to' => 
  array (
  ),
  'advanced-gerund-infinitive-patterns' => 
  array (
    'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedGerundInfinitivePatternsB2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotAdvancedGerundInfinitivePatternsB2LessonSeeder/definition.json',
      1 => '183c9997020ae3b044575af9bc8c90279e7179fc4bddd285cc96a4e89c44294a',
    ),
  ),
);
    public const PAGES = array (
  'gerund' => 
  array (
    0 => 195,
    1 => 33,
    2 => 'gerund',
    3 => 'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsGerundTheorySeeder',
  ),
  'to-infinitive' => 
  array (
    0 => 198,
    1 => 33,
    2 => 'to-infinitive',
    3 => 'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsToInfinitiveTheorySeeder',
  ),
  'bare-infinitive' => 
  array (
    0 => 193,
    1 => 33,
    2 => 'bare-infinitive',
    3 => 'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBareInfinitiveTheorySeeder',
  ),
  'gerund-vs-infinitive' => 
  array (
    0 => 196,
    1 => 33,
    2 => 'gerund-vs-infinitive',
    3 => 'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsGerundVsInfinitiveTheorySeeder',
  ),
  'verbs-plus-gerund' => 
  array (
    0 => 199,
    1 => 33,
    2 => 'verbs-plus-gerund',
    3 => 'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusGerundTheorySeeder',
  ),
  'verbs-plus-infinitive' => 
  array (
    0 => 200,
    1 => 33,
    2 => 'verbs-plus-infinitive',
    3 => 'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusInfinitiveTheorySeeder',
  ),
  'stop-remember-forget-try-regret' => 
  array (
    0 => 197,
    1 => 33,
    2 => 'stop-remember-forget-try-regret',
    3 => 'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsStopRememberForgetTryRegretTheorySeeder',
  ),
  'be-used-to-get-used-to-used-to' => 
  array (
    0 => 194,
    1 => 33,
    2 => 'be-used-to-get-used-to-used-to',
    3 => 'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder',
  ),
  'advanced-gerund-infinitive-patterns' => 
  array (
    0 => 286,
    1 => 33,
    2 => 'advanced-gerund-infinitive-patterns',
    3 => 'Database\\Seeders\\Page_V3\\VerbPatterns\\AdvancedGerundInfinitivePatternsTheorySeeder',
  ),
);
    private const ORIGINALS = array (
  'gerund' => 
  array (
    'standard' => 
    array (
      0 => 'vp-g-v3-all',
      1 => 'f83c1bdabe685748011fb77d610b74ef9ab7accd70fcfabc9e2466c4cb3a666a',
    ),
    'builder' => 
    array (
      0 => 'vp-g-poly-all',
      1 => '7821f3327d615357a89a8fd9119fdd188a97a4d56e6bb273c51733ac2b40b5e5',
    ),
  ),
  'to-infinitive' => 
  array (
    'standard' => 
    array (
      0 => 'vp-ti-v3-all',
      1 => 'd5509ca0b5c4649b31bc5f3ab44ac9e1747f59743bcb4328b60b2a05aca262d8',
    ),
    'builder' => 
    array (
      0 => 'vp-ti-poly-all',
      1 => '048816a5d3006a6b32b74c146e5d8d0e66e18ab5c70f476c4b81001e4b58e339',
    ),
  ),
  'bare-infinitive' => 
  array (
    'standard' => 
    array (
      0 => 'vp-bi-v3-all',
      1 => 'cc3b9c2cc7e1c7d67dff2b7612675e7928a1b26648fd3b557131b33a4461c1fa',
    ),
    'builder' => 
    array (
      0 => 'vp-bi-poly-all',
      1 => '131b7d5645b22a0b5f4aff8772c67f30e1a6df6d9fd3ae17623a5a527da81efe',
    ),
  ),
  'gerund-vs-infinitive' => 
  array (
    'standard' => 
    array (
      0 => 'vp-gvi-v3-all',
      1 => 'e0c94f1e1a88c83c2658e67f12a12a536820273db0983763cd321dd0d52a4cdd',
    ),
    'builder' => 
    array (
      0 => 'vp-gvi-poly-all',
      1 => 'a90dfa862a7df0bd90d7a95639224ebb9ead24ca43504fb57b83f8fab1d4b761',
    ),
  ),
  'verbs-plus-gerund' => 
  array (
    'standard' => 
    array (
      0 => 'vp-vg-v3-all',
      1 => '99745893e4b28408d691998fc9ffc709d8e27091bc47a668d2fd93ef79807bdc',
    ),
    'builder' => 
    array (
      0 => 'vp-vg-poly-all',
      1 => '799fb7b5bf470281d41b75055bffffca339583aeb48a64a8c62061c2683766e3',
    ),
  ),
  'verbs-plus-infinitive' => 
  array (
    'standard' => 
    array (
      0 => 'vp-vi-v3-all',
      1 => 'cbd0590f816bb4bc9ebff4e0fed0e55e99e233c70d7a379e4473ab4e392a2b07',
    ),
    'builder' => 
    array (
      0 => 'vp-vi-poly-all',
      1 => '6c93408eee4b011a1234f1403c9fcdf47c48db94afb2444a44b77374d0d4acc6',
    ),
  ),
  'stop-remember-forget-try-regret' => 
  array (
    'standard' => 
    array (
      0 => 'vp-srftr-v3-all',
      1 => '1a2862c474668db727d937ccf6e7314ab2a4749c2c77d46f14b0d5d958b868d9',
    ),
    'builder' => 
    array (
      0 => 'vp-srftr-poly-all',
      1 => '49c0ed5f1d51231cf5ea1c7a6b7d15ec4f9ad0f8a893a47a2046da330aeba503',
    ),
  ),
  'be-used-to-get-used-to-used-to' => 
  array (
    'standard' => 
    array (
      0 => 'vp-bugtu-v3-all',
      1 => 'ac367e2384854d1d47bc3ab6d7470904a0985704aab183f059acd6b869a09458',
    ),
    'builder' => 
    array (
      0 => 'vp-bugtu-poly-all',
      1 => 'e3b472000cee63e99681b254610ab98324d635924da4d7c05eb0dd8eea4c5fb0',
    ),
  ),
  'advanced-gerund-infinitive-patterns' => 
  array (
    'standard' => 
    array (
      0 => 'vp-agip-v3-all',
      1 => '59d101d02ed82caae87955198e8dccbdadd5399f848e98247b079f65b9a5850d',
    ),
    'builder' => 
    array (
      0 => 'vp-agip-poly-all',
      1 => '482577660d56c866ba41ce1a0562136437c3ae84501b60ad41593e2fc2cfabd5',
    ),
  ),
);

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Verb Patterns practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/verb-patterns-quality/'.$topic.'.v1.json'];
        $map['additional_originals']=[];
        foreach (self::RETAINED_DEFINITIONS[$topic] as $class=>$binding) {
            $map['additional_originals'][]=['class'=>$class,'path'=>$binding[0],'sha256'=>$binding[1]];
        }
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'VerbPatterns' : 'Polyglot';
            $name = 'UkrainianMixedVpq'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'VerbPatterns/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
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
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Verb Patterns author identity.'); }
        return self::UUID_PREFIX.self::UUID_TOPICS[$topic].'-'.strtolower($authorId);
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    /** Only finite reviewed aliases belong to this catalog. */
    private static function ownsQuestionUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^vpq-(?:gerund|to|bare|contrast|verbs-gerund|verbs-infinitive|meaning|used|advanced)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/D', $uuid) === 1;
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
            $payload['vpq_option_caption_revision']=$payload['psp_content_revision'];
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
            if (($payload['vpq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
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

    /** A declarative profile alone must not opt a foreign/partially migrated page into verb-patterns behavior. */
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

    /** Exact homogeneous members for explicit acceptance; never discovers or writes models. */
    public static function compositeMembers(): array
    {
        $members=[];
        foreach(self::PAGES as $topic=>[$id,$category,$slug,$owner]) {
            $map=self::mapping($topic);
            $members[$id]=['page_id'=>$id,'category_id'=>$category,'slug'=>$slug,'owner'=>$owner,
                'catalog'=>self::class,'topic'=>$topic,'profile'=>self::REVISION,
                'seeder_classes'=>[$map['standard']['class'],$map['builder']['class']]];
        }
        return count($members)===9?$members:[];
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
                || !is_array($group['seeder_classes']??null) || count($group['seeder_classes'])!==2) { return null; }
            $map=self::mapping($topic);
            if (self::classes($group['seeder_classes'])!==self::classes([$map['standard']['class'],$map['builder']['class']])) { return null; }
            $seen[$topic]=true;
        }
        return self::REVISION;
    }

    private static function validCategoryContext(array $filters): bool
    {
        if (!array_key_exists('theory_category_page_test',$filters)) { return true; }
        if ($filters['theory_category_page_test']!==true) { return false; }
        if (!self::ownsCategory((int)\Illuminate\Support\Arr::get($filters,'__meta.theory_category_id'),
            (string)\Illuminate\Support\Arr::get($filters,'__meta.theory_category_slug'))) { return false; }
        $groups=$filters['theory_category_page_groups']??[];
        if (!is_array($groups) || count($groups)!==count(self::PAGES)) { return false; }
        $seen=[];
        foreach ($groups as $group) {
            $topic=null;
            foreach (self::PAGES as $key=>$binding) { if ($binding[0]===($group['page_id']??null)) { $topic=$key; break; } }
            if ($topic===null || isset($seen[$topic]) || ($group['page_slug']??null)!==self::PAGES[$topic][2]
                || ($group['page_seeder_class']??null)!==self::PAGES[$topic][3]
                || ($group['quality_profile']??null)!==self::REVISION
                || !is_array($group['seeder_classes']??null) || count($group['seeder_classes'])!==2) { return false; }
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

