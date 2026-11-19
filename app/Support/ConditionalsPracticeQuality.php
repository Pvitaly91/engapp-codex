<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned conditional banks; historical questions, courses and progress stay intact. */
final class ConditionalsPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'cqq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'zero-conditional' => 'ZeroConditional',
        'first-conditional' => 'FirstConditional',
        'second-conditional' => 'SecondConditional',
        'third-conditional' => 'ThirdConditional',
        'mixed-conditionals' => 'MixedConditionals',
        'unless-provided-as-long-as' => 'UnlessProvidedAsLongAs',
        'in-case' => 'InCase',
        'wish-if-only' => 'WishIfOnly',
        'would-rather' => 'WouldRather',
        'had-better' => 'HadBetter',
        'conditionals-with-unless-provided-as-long-as' => 'ConditionalsWithUnlessProvidedAsLongAs',
        'advanced-conditionals' => 'AdvancedConditionals',
        'conditional-alternatives-and-nuance' => 'ConditionalAlternativesAndNuance',
    ];
    private const UUID_TOPICS = [
        'zero-conditional' => 'zero',
        'first-conditional' => 'first',
        'second-conditional' => 'second',
        'third-conditional' => 'third',
        'mixed-conditionals' => 'mixed',
        'unless-provided-as-long-as' => 'unless',
        'in-case' => 'case',
        'wish-if-only' => 'wish',
        'would-rather' => 'rather',
        'had-better' => 'better',
        'conditionals-with-unless-provided-as-long-as' => 'conditions',
        'advanced-conditionals' => 'advanced',
        'conditional-alternatives-and-nuance' => 'alternatives',
    ];
    protected const UUID_PREFIX = 'cqq-';
    protected const SOURCE_PREFIX = 'conditionals_quality_';
    protected const NAMESPACE_PREFIX = 'conditionals-quality-';
    protected const LABEL = 'Conditionals Quality';
    protected const META_PREFIX = 'conditionals_quality';
    public const CATEGORY_ID = 10;
    public const CATEGORY_SLUG = 'conditionals';
    public const CATEGORY_TOPICS = ['zero-conditional', 'first-conditional', 'second-conditional', 'third-conditional', 'mixed-conditionals', 'unless-provided-as-long-as', 'in-case', 'wish-if-only', 'would-rather', 'had-better', 'conditionals-with-unless-provided-as-long-as', 'advanced-conditionals', 'conditional-alternatives-and-nuance'];
    public const SAVED_TEST_COUNT = 26;
    /** Exact existing course and legacy test descriptors are neutral to the page profile, never rewritten or used as banks. */
    public const RETAINED_TEST_PROFILES = [
        'zero-conditional' => [],
        'first-conditional' => ['9e12062f-6c5e-039c-9248-9b0a7d4ef0ed'=>['course-td-first-conditional','e265ffbe20dde1140eaeab7e48cf43db9d00c11b5efc7278dacc23932fd4b8c3'], 'polyglot-first-conditional-a2-8c7e7b'=>['polyglot-first-conditional-a2','77bfef661d8353e961301d94fbe9287ce65bae8d36549c90dde46571337acb06']],
        'second-conditional' => ['77d62805-5cee-9f0c-42a4-704b70b06662'=>['course-td-second-conditional','a38183321323dc29be8193481a13be723a508190e0294425b7d07e2587eeac1e'], 'polyglot-second-conditional-b-2e32d4'=>['polyglot-second-conditional-basics-a2','96ab58725068b5c6ea05733e7516eb593d011d5b7d19d123de0b60efa19d6c72']],
        'third-conditional' => ['343b41b2-d9d9-a040-cc80-b49c0c456ac8'=>['course-td-third-conditional','7f2e6baae0791c6746c8a7c179af700db52e7091c9bd53fd4266f20d54f44fb6'], 'polyglot-third-conditional-ba-1000a2'=>['polyglot-third-conditional-basics-b1','4c563b3f2cff4d86cdb1fb6dc860c000a77737112ba6016b8bf5f0362193b5ea']],
        'mixed-conditionals' => ['ace44b48-f0a5-c8c2-45c4-1ff3b6efea4e'=>['course-td-mixed-conditionals','934b6e1734da8c8ca1007f20edc2db2932561e0da409d733385601603d46ceca'], 'polyglot-mixed-conditionals-b-bd5035'=>['polyglot-mixed-conditionals-basics-b2','0746db1f7b62ef78a66fa455bc023c19418de892227663fd9840074d005ad9f8']],
        'unless-provided-as-long-as' => [],
        'in-case' => [],
        'wish-if-only' => ['c68dcead-11ee-e5ac-ffac-1480dbf9d38f'=>['course-td-wish-if-only','658e509e3d72c53e8f2b69718c950b30c451f0fd34924dd1c087676156f7da81'], 'polyglot-wish-if-only-basics-15eaa6'=>['polyglot-wish-if-only-basics-b1','c0a5969b3f556fbec087a2159dc7b8d82e9542bdc65bc501067fa1005c88c026']],
        'would-rather' => [],
        'had-better' => [],
        'conditionals-with-unless-provided-as-long-as' => ['polyglot-conditionals-wit-d13d7e90fe'=>['polyglot-conditionals-with-unless-provided-as-long-as-b2','593af2782cbfcbed7297518ca1ca31c7ff03e1c5db919aca9103057c01f75eb0'], '364f075b-86ce-cc0c-2bfc-ec596382c327'=>['course-td-conditionals-with-unless-provided-as-long-as','ac9f03eb52882f79b08a950253cd6db5dd7108308e4eafd6d87583558aa9c1cb']],
        'advanced-conditionals' => ['polyglot-advanced-conditi-f0c5104b39'=>['polyglot-advanced-conditionals-c1','9a0ed9ee1008f0bc2252d8007f7b94e1d88cd4c831f1862eff1b1b3d9ab1ac4a'], '7ce5c25e-726e-78f4-1dfd-3f0fd6ceff4e'=>['course-td-advanced-conditionals','632fcfd0d29c2731aebd84a9e81a68f6da7721974219e3a8eee9463f4b1ba1a7']],
        'conditional-alternatives-and-nuance' => ['polyglot-conditional-alternat-7951ff'=>['polyglot-conditional-alternatives-and-nuance-c2','f8f89f9009a4cf3829e265aae649ebc698de74a7570a0ed3c7cc6c217ac0ef17'], 'fcc6e785-5637-dbb6-6f16-fb1f6be00732'=>['course-td-conditional-alternatives-and-nuance','20e1022aba8c4a1cb16ef05df3922fb0158701277a624d2a55a413e90bf4b3cb']],
    ];
    /** Nonselected historical definitions are neutral only at these exact source bytes. */
    public const RETAINED_DEFINITIONS = [
        'zero-conditional' => [],
        'first-conditional' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotFirstConditionalLessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotFirstConditionalLessonSeeder/definition.json','fdb38e031ac14d4745b12d7334644c0564bfe062aaf78bae98fb644b5dae06d7']],
        'second-conditional' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotSecondConditionalBasicsLessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotSecondConditionalBasicsLessonSeeder/definition.json','6a27ab0941c9ef18876aef1b5833783ec081521bc4b7436601bd1f3397bd1943']],
        'third-conditional' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotThirdConditionalBasicsLessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotThirdConditionalBasicsLessonSeeder/definition.json','9fcc79c0005768a7373778d7870905c9a9fe8960bbdb081f4d4af66078b952f2']],
        'mixed-conditionals' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotMixedConditionalsBasicsB2LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotMixedConditionalsBasicsB2LessonSeeder/definition.json','0fa70d57a8712393dbace438cde5f54d5df1b3fe56b10c8d0280f660d56e307f']],
        'unless-provided-as-long-as' => [],
        'in-case' => [],
        'wish-if-only' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotWishIfOnlyBasicsLessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotWishIfOnlyBasicsLessonSeeder/definition.json','9bafd2519baca15117fc5a3748ad0a3c092cf7db5ff36ff20b40f38b03f6da94']],
        'would-rather' => [],
        'had-better' => [],
        'conditionals-with-unless-provided-as-long-as' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotConditionalsWithUnlessProvidedAsLongAsB2LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotConditionalsWithUnlessProvidedAsLongAsB2LessonSeeder/definition.json','44a48eb934d4e0da5a058483b8ab31a6df834e2b47334f9f7b1eec34676f8601']],
        'advanced-conditionals' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedConditionalsC1LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotAdvancedConditionalsC1LessonSeeder/definition.json','c625f44ac6980f3a99ecf9189e548874f5eb89561360fff4b212d517d81e2455']],
        'conditional-alternatives-and-nuance' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotConditionalAlternativesAndNuanceC2LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotConditionalAlternativesAndNuanceC2LessonSeeder/definition.json','afaf5e599f3372970fa631cfdfbba9d4691a175d243e0ffa488872f3104b90b2']],
    ];

    /** Captured local page bindings; names alone never opt a course or another locale in. */
    public const PAGES = [
        'zero-conditional' => [53,10,'zero-conditional','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsZeroTheorySeeder'],
        'first-conditional' => [44,10,'first-conditional','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsFirstTheorySeeder'],
        'second-conditional' => [48,10,'second-conditional','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsSecondTheorySeeder'],
        'third-conditional' => [49,10,'third-conditional','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsThirdTheorySeeder'],
        'mixed-conditionals' => [47,10,'mixed-conditionals','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsMixedTheorySeeder'],
        'unless-provided-as-long-as' => [50,10,'unless-provided-as-long-as','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsUnlessProvidedAsLongAsTheorySeeder'],
        'in-case' => [46,10,'in-case','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsInCaseTheorySeeder'],
        'wish-if-only' => [51,10,'wish-if-only','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWishIfOnlyTheorySeeder'],
        'would-rather' => [52,10,'would-rather','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWouldRatherTheorySeeder'],
        'had-better' => [45,10,'had-better','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsHadBetterTheorySeeder'],
        'conditionals-with-unless-provided-as-long-as' => [287,10,'conditionals-with-unless-provided-as-long-as','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWithUnlessProvidedAsLongAsTheorySeeder'],
        'advanced-conditionals' => [294,10,'advanced-conditionals','Database\\Seeders\\Page_V3\\Conditionals\\AdvancedConditionalsTheorySeeder'],
        'conditional-alternatives-and-nuance' => [309,10,'conditional-alternatives-and-nuance','Database\\Seeders\\Page_V3\\Conditionals\\ConditionalAlternativesAndNuanceTheorySeeder'],
    ];

    private const ORIGINALS = [
        'zero-conditional' => ['standard'=>['conditionals-zero-v3-all','0369b9e220db4ad013f83e2901b96c937bc07b9ef765389ec48b0001d4dc12ca'], 'builder'=>['polyglot-zero-conditional-all','615ce6e706aa768f99a419a343d04a79395cfe63b6870d3ba6a0391242efeb5f']],
        'first-conditional' => ['standard'=>['conditionals-first-v3-all','ce82b593e24cc6f7c621a8d0e98dbc5a3fcea04d970a197c226ab107cd3807c8'], 'builder'=>['polyglot-first-conditional-all','7ee31c52c96e9edbfcc5fc36eff18e6ec1b1271dc99ddbd5ed10ac5efa5d32b0']],
        'second-conditional' => ['standard'=>['conditionals-second-v3-all','c6412bad50a7a21a3cb94387793540809135863613e2e42e60aecc3651ed1bb0'], 'builder'=>['polyglot-second-conditional-all','4a2e810d02b83624032a517021a1bbe9bb0ad4a84fb4f7042030c4d9fa72122c']],
        'third-conditional' => ['standard'=>['conditionals-third-v3-all','880b98b0f8336815d9f43d08f7a23779dfa13bb5092dacbf52f4dcf0b2ad6fd4'], 'builder'=>['polyglot-third-conditional-all','e294d505f4d0b40d470a62b3a17eaaae7ab84558cf64f39ec714a3f8427f613f']],
        'mixed-conditionals' => ['standard'=>['conditionals-mixed-v3-all','0e23b05409762e7be4a7d6b369810387efed94463bc60f64cfb33673bcf932d1'], 'builder'=>['polyglot-mixed-conditionals-all','9a2357b36b0dc1f84ede4be4405a49b8578a7b5f0d27deabec60e22d20dd7496']],
        'unless-provided-as-long-as' => ['standard'=>['cond-extra-upa-v3-all','5bcc4ea1bbd112c53af57edc0c8d21b26b7c25ef7eb9f73f3d8ca45482293aff'], 'builder'=>['poly-extra-upa-all','650f02ff949c027385c3b5f5ff26583f060be3bde0eabef547cbe6d11083a4e0']],
        'in-case' => ['standard'=>['cond-extra-ic-v3-all','8274763b2af7199fc9e8f65cdb619acabaf934024524abde7a0611096c5dfe38'], 'builder'=>['poly-extra-ic-all','c870596caf03cc95034e498ca9e63a9f4b76079e4a9d615348162745e0483c0b']],
        'wish-if-only' => ['standard'=>['cond-extra-wio-v3-all','86189cd1a59fdd73b1ea8f9d67d7e5221860819740d570a8db67e0b47d579126'], 'builder'=>['poly-extra-wio-all','bac9dd921a2e03218b06a167b29642d6d595a45b8222757eb7a23ed4009e80b5']],
        'would-rather' => ['standard'=>['cond-extra-wr-v3-all','2b5d3ed458618041e34be64ada8c909c79e2d923c5ad97a292c174700c161694'], 'builder'=>['poly-extra-wr-all','58dab65f6f796385379a4b6e1e6e8f7d740ebc9a6cdf79a8c513fdd3718e2b77']],
        'had-better' => ['standard'=>['cond-extra-hb-v3-all','02809f4c825e663a92b37abfddffecd0cb798de4d2452469ed689e0dc352c7f5'], 'builder'=>['poly-extra-hb-all','811fc4a717be9ee78c829ae718871b68290874177525128cebe613939eae624d']],
        'conditionals-with-unless-provided-as-long-as' => ['standard'=>['cwupa-v3-all','d50c860bbc5229ab2882928c6836fed269b5f4c7eca38de7373b1a0551be6611'], 'builder'=>['cwupa-poly-all','7b9015d22126f20604f26db78182997c9a070595aa16253cc7af66e835fb25d7']],
        'advanced-conditionals' => ['standard'=>['adv-cond-v3-all','dbab795faf3d19a9b7be90feef0318e31b9d1966f4f3a74b29efeaef506fab7c'], 'builder'=>['adv-cond-poly-all','ecdeda8fdda78d2b0cb90be05fea7009ed262f2a7d9515dc30f7f677550cc766']],
        'conditional-alternatives-and-nuance' => ['standard'=>['cond-alt-v3-all','3124265400674f5555e09ad6834e56947c4328663cbbc00b18ba878168f68914'], 'builder'=>['cond-alt-poly-all','d377c4a4e162ed5649b5d8bd6527035b4f5d564ab5d57fdcc09784354a1e8488']],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Conditionals practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/conditionals-quality/'.$topic.'.v1.json'];
        $map['additional_originals']=[];
        foreach (self::RETAINED_DEFINITIONS[$topic] as $class=>$binding) {
            $map['additional_originals'][]=['class'=>$class,'path'=>$binding[0],'sha256'=>$binding[1]];
        }
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'Conditionals' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'Conditionals/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
            $map[$kind] = ['class'=>'Database\\Seeders\\V3\\'.$namespace.'\\'.$name,
                'definition'=>$directory.'/definition.json', 'loader'=>$directory.'.php', 'implementation'=>$directory.'/'.$name.'.php',
                'old_definition'=>'database/seeders/V3/'.$oldName.'/definition.json', 'old_class'=>'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid'=>self::ORIGINALS[$topic][$kind][0], 'old_sha256'=>self::ORIGINALS[$topic][$kind][1]];
        }
        return $map;
    }

    /** Short finite aliases keep the authored level/ID visible within the persistent36-byte limit. */
    public static function questionUuid(string $topic, string $authorId): string
    {
        self::mapping($topic);
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Conditionals author identity.'); }
        return self::UUID_PREFIX.self::UUID_TOPICS[$topic].'-'.strtolower($authorId);
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

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
        if ($locale!=='uk' || !is_string($payload['uuid']??null) || !str_starts_with($payload['uuid'],self::UUID_PREFIX)) { return $payload; }
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
        if (!is_string($payload['uuid']??null) || !str_starts_with($payload['uuid'],self::UUID_PREFIX)) { return $payload; }
        $payload=parent::decorate($payload); $source=self::questions()[$payload['uuid']??'']??null;
        if ($source && self::sourceBound($payload,$source)) {
            if ((string)$source['type']==='0') { $payload['passive_phrase_word_inputs']=true; }
            $payload['cqq_option_caption_revision']=$payload['psp_content_revision'];
            $payload=self::restoreClarifiedHints($payload,'uk');
        }
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (!array_filter($payloads,static fn($row)=>is_string($row['uuid']??null) && str_starts_with($row['uuid'],self::UUID_PREFIX))) { return false; }
        if (parent::cachedNeedsRefresh($payloads)) { return true; }
        $sources=self::questions();
        foreach ($payloads as $payload) {
            $source=$sources[$payload['uuid']??'']??null; if (!$source) { continue; }
            if (($payload['cqq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
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

    /** A declarative profile alone must not opt a foreign/partially migrated page into conditional behavior. */
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
