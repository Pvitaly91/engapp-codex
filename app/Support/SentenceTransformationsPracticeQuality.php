<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned Sentence Transformations banks; historical questions, courses and progress stay intact. */
final class SentenceTransformationsPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'stq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'tenses' => 'Tenses',
        'modals' => 'Modals',
        'passive' => 'Passive',
        'reported-speech' => 'ReportedSpeech',
        'conditionals' => 'Conditionals',
        'relative-clauses-linking-words' => 'RelativeClausesLinkingWords',
        'articles-quantifiers' => 'ArticlesQuantifiers',
    ];
    private const UUID_TOPICS = [
        'tenses' => 'tenses',
        'modals' => 'modals',
        'passive' => 'passive',
        'reported-speech' => 'reported',
        'conditionals' => 'conditionals',
        'relative-clauses-linking-words' => 'clauses',
        'articles-quantifiers' => 'articles',
    ];
    protected const UUID_PREFIX = 'stq-';
    protected const SOURCE_PREFIX = 'sentence_transformations_quality_';
    protected const NAMESPACE_PREFIX = 'sentence-transformations-quality-';
    protected const LABEL = 'Sentence Transformations Quality';
    protected const META_PREFIX = 'sentence_transformations_quality';
    public const CATEGORY_ID = 23;
    public const CATEGORY_SLUG = 'sentence-transformations';
    public const CATEGORY_TOPICS = ['tenses','modals','passive','reported-speech','conditionals','relative-clauses-linking-words','articles-quantifiers'];
    public const SAVED_TEST_COUNT = 14;
    /** Only exact fresh observed neutral descriptors may coexist; activation waits for actual capture. */
    public const RETAINED_TEST_PROFILES = [
        'tenses' => [],
        'modals' => [],
        'passive' => [],
        'reported-speech' => [],
        'conditionals' => [],
        'relative-clauses-linking-words' => [],
        'articles-quantifiers' => [],
    ];
    /** Only exact frozen ancillary banks may coexist; no foreign source is inferred by a label. */
    public const RETAINED_DEFINITIONS = [
        'tenses' => [],
        'modals' => [],
        'passive' => [],
        'reported-speech' => [],
        'conditionals' => [],
        'relative-clauses-linking-words' => [],
        'articles-quantifiers' => [],
    ];
    /** Exact fresh read-only captured Page/category/seeder identities. */
    public const PAGES = [
        'tenses' => [152,23,'tenses','Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsTensesTheorySeeder'],
        'modals' => [148,23,'modals','Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsModalsTheorySeeder'],
        'passive' => [149,23,'passive','Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsPassiveTheorySeeder'],
        'reported-speech' => [151,23,'reported-speech','Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsReportedSpeechTheorySeeder'],
        'conditionals' => [147,23,'conditionals','Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsConditionalsTheorySeeder'],
        'relative-clauses-linking-words' => [150,23,'relative-clauses-linking-words','Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsRelativeClausesLinkingWordsTheorySeeder'],
        'articles-quantifiers' => [146,23,'articles-quantifiers','Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsArticlesQuantifiersTheorySeeder'],
    ];
    /** Already accepted Word Order lesson is read-only: no author, bank or saved-filter rewrite. */
    public const REUSED_PAGE = [153,23,'word-order-emphasis','Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsWordOrderEmphasisTheorySeeder'];
    public const REUSED_DEFINITIONS = [
        'Database\\Seeders\\V3\\SentenceTransformations\\SentenceTransformationsWordOrderEmphasisAllLevelsV3Seeder' => ['database/seeders/V3/SentenceTransformations/SentenceTransformationsWordOrderEmphasisAllLevelsV3Seeder/definition.json','dd562e1951b636deb2ac416a8039cda7db757b9c1ce13363a749b17b7a8a58a9'],
        'Database\\Seeders\\V3\\Polyglot\\PolyglotSentenceTransformationsWordOrderEmphasisAllLevelsLessonSeeder' => ['database/seeders/V3/Polyglot/PolyglotSentenceTransformationsWordOrderEmphasisAllLevelsLessonSeeder/definition.json','a15132299e275aac8b6e8d301110f1ad30074101cb687b3fd1509fe107a0726c'],
        'Database\\Seeders\\V3\\WordOrder\\UkrainianMixedSentenceTransformationsWordOrderEmphasisQualityStandardSeeder' => ['database/seeders/V3/WordOrder/UkrainianMixedSentenceTransformationsWordOrderEmphasisQualityStandardSeeder/definition.json','bfcbf5ca81f0ee58d7f6a29ecdc5ba2abaa58e6b85e74926ca095d282caad7f5'],
        'Database\\Seeders\\V3\\Polyglot\\UkrainianMixedSentenceTransformationsWordOrderEmphasisQualityBuilderSeeder' => ['database/seeders/V3/Polyglot/UkrainianMixedSentenceTransformationsWordOrderEmphasisQualityBuilderSeeder/definition.json','bef720527cfa01ae984c6b964dda2e8d2055c31b1492c25d4fd67eca09798af6'],
    ];
    private const REUSED_SOURCE_PINS = [
        'app/Support/WordOrderPracticeQuality.php' => '6e3ab4aaf46f153c266fbfeb4ed709cffd587f1ba30912a5a2825829db8a0c02',
        'docs/content/word-order-quality/transformations.v1.json' => '7f4f54c04daeaae55ec7e8e5c06db487c91a2790ed5b209e67cfd553f3b5c7f8',
    ];
    private const ORIGINALS = [
        'tenses' => ['standard'=>['stten-v3-all','d1a05bb8fc581aec0774c227b4c472005a79e7c0047b8b36bedcb5fc422a952a'], 'builder'=>['stten-poly-all','edb3b5b60d8b2ea19e1ec12427dec75037dfd4c19307fec47a5f2e9fc707b37a']] ,
        'modals' => ['standard'=>['stmod-v3-all','5ac3eeabe731ae771b29e1bc1784ce5436521f89b38932629064a44d67a13c16'], 'builder'=>['stmod-poly-all','62fbe39f8e34cfb93755c39fe972eef6d57fd2b6037ed479d0f729c5e456d47d']] ,
        'passive' => ['standard'=>['stpas-v3-all','14d1a1c1fbb367f398b0c0afaf999e60b72c1529bd3fddb6d9f4ace869971934'], 'builder'=>['stpas-poly-all','706d775202d870c635fb0fb02185cf8344ddcbe936767b37c800af2352bee4be']] ,
        'reported-speech' => ['standard'=>['strsp-v3-all','7ecd9c53c7cef07d89738ddee8ce81c45f3892ca5fb9b88a0f71fb4eaa18944f'], 'builder'=>['strsp-poly-all','ad5aef7038159fb455f5e65164bb38b07e4f50682fd9c0045ff75f4dccc46c5b']] ,
        'conditionals' => ['standard'=>['stcon-v3-all','cd84c547fd95081b73b758fa835f3e6b82dff8b77b8649f1b9624cd7807b9b1e'], 'builder'=>['stcon-poly-all','bcc1c341f5a5febc6a560cca404fd592736c427927df369f2f67addfd679734b']] ,
        'relative-clauses-linking-words' => ['standard'=>['strcl-v3-all','437948d66f845e43528a42078cd1361f6a5dcda3ea709851eb8da5da1c7164b3'], 'builder'=>['strcl-poly-all','e455d8c157d31f789999dbb047a15983c991334218fbd6fa46240b76de3fff4f']] ,
        'articles-quantifiers' => ['standard'=>['staq-v3-all','c37f599459b7581172adf5cdb07d521032ed1d3e96b862f63444dd2fa9d6ac61'], 'builder'=>['staq-poly-all','fbea2ebdfb55ecbe8534ad51a7931925dc23990e7fea2ec2ce24cada330d5f6e']] ,
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Sentence Transformations practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/sentence-transformations-quality/'.$topic.'.v1.json'];
        $map['additional_originals']=[];
        foreach (self::RETAINED_DEFINITIONS[$topic] as $class=>$binding) {
            $map['additional_originals'][]=['class'=>$class,'path'=>$binding[0],'sha256'=>$binding[1]];
        }
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'SentenceTransformations' : 'Polyglot';
            $name = 'UkrainianMixedStq'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'SentenceTransformations/SentenceTransformations'.$stem.'AllLevelsV3Seeder' : 'Polyglot/PolyglotSentenceTransformations'.$stem.'AllLevelsLessonSeeder';
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
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Sentence Transformations author identity.'); }
        return self::UUID_PREFIX.self::UUID_TOPICS[$topic].'-'.strtolower($authorId);
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    /** Only finite reviewed aliases belong to this catalog. */
    private static function ownsQuestionUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^stq-(?:tenses|modals|passive|reported|conditionals|clauses|articles)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/D', $uuid) === 1;
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
        $payload = SentenceTransformationsReusedMixedHints::project($payload, $locale);
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
            $payload['stq_option_caption_revision']=$payload['psp_content_revision'];
            $payload=self::restoreClarifiedHints($payload,'uk');
        }
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (SentenceTransformationsReusedMixedHints::cachedNeedsRefresh($payloads)) { return true; }
        if (!array_filter($payloads,static fn($row)=>self::ownsQuestionUuid($row['uuid']??null))) { return false; }
        if (parent::cachedNeedsRefresh($payloads)) { return true; }
        $sources=self::questions();
        foreach ($payloads as $payload) {
            $source=$sources[$payload['uuid']??'']??null; if (!$source) { continue; }
            if (($payload['stq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
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

    /** A declarative profile alone must not opt a foreign/partially migrated page into sentence-transformations behavior. */
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

    /** Exact retained four-bank corpus, also used by author duplicate audits. No model/DB access. */
    public static function auditOnlyDefinitions(): array
    {
        $definitions = [];
        foreach (self::REUSED_DEFINITIONS as $class=>$binding) {
            $file = dirname(__DIR__,2).'/'.$binding[0];
            if (!is_file($file) || is_link($file) || !hash_equals($binding[1],hash_file('sha256',$file))) {
                throw new \RuntimeException('Sentence Transformations retained Word Order source differs.');
            }
            $definition = json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
            if (($definition['seeder']['class']??null)!==$class || ($definition['defaults']['default_locale']??null)!=='uk') {
                throw new \RuntimeException('Sentence Transformations retained Word Order identity differs.');
            }
            $definitions[$class]=$definition;
        }
        return $definitions;
    }

    public static function ownsCategory(int $id, string $slug): bool
    {
        return $id===self::CATEGORY_ID && $slug===self::CATEGORY_SLUG;
    }

    /** Seven new members plus exactly the unchanged, previously accepted Word Order member. */
    public static function compositeMembers(): array
    {
        if (count(self::PAGES)!==7 || count(self::REUSED_PAGE)!==4) { return []; }
        foreach (self::REUSED_SOURCE_PINS as $path=>$hash) {
            $file=dirname(__DIR__,2).'/'.$path;
            if (!is_file($file) || is_link($file) || !hash_equals($hash,hash_file('sha256',$file))) { return []; }
        }
        try { self::auditOnlyDefinitions(); } catch (\Throwable) { return []; }
        $members=[];
        foreach (self::PAGES as $topic=>[$id,$category,$slug,$owner]) {
            $map=self::mapping($topic);
            $members[$id]=['page_id'=>$id,'category_id'=>$category,'slug'=>$slug,'owner'=>$owner,
                'catalog'=>self::class,'topic'=>$topic,'profile'=>self::REVISION,
                'seeder_classes'=>[$map['standard']['class'],$map['builder']['class']]];
        }
        [$id,$category,$slug,$owner]=self::REUSED_PAGE;
        $map=WordOrderPracticeQuality::mapping('transformations');
        $members[$id]=['page_id'=>$id,'category_id'=>$category,'slug'=>$slug,'owner'=>$owner,
            'catalog'=>WordOrderPracticeQuality::class,'topic'=>'transformations','profile'=>WordOrderPracticeQuality::REVISION,
            'seeder_classes'=>[$map['standard']['class'],$map['builder']['class']]];
        return count($members)===8?$members:[];
    }

    /** Caller validates category identity first; every member/profile/pair is then exact. */
    public static function compositeCategoryProfile(array $groups): ?string
    {
        $members=self::compositeMembers();
        if (count($members)!==8 || count($groups)!==8) { return null; }
        $seen=[];
        foreach ($groups as $group) {
            $page=$group['page']??null;
            if (!$page instanceof \App\Models\Page || !isset($members[(int)$page->getKey()])) { return null; }
            $entry=$members[(int)$page->getKey()]; $classes=$group['seeder_classes']??null;
            if (isset($seen[$entry['page_id']]) || (int)$page->page_category_id!==self::CATEGORY_ID
                || (string)$page->slug!==$entry['slug'] || (string)$page->seeder!==$entry['owner']
                || ($group['base_filters']['theory_page_mixed_type_profile']??null)!==$entry['profile']
                || !is_array($classes) || count($classes)!==2
                || self::classes($classes)!==self::classes($entry['seeder_classes'])) { return null; }
            $seen[$entry['page_id']]=true;
        }
        return count($seen)===8?self::REVISION:null;
    }

    public static function categoryProfile(int $id, string $slug, array $groups): ?string
    {
        return self::ownsCategory($id,$slug)?self::compositeCategoryProfile($groups):null;
    }

    private static function isCategoryContext(array $filters): bool
    {
        return ($filters['theory_category_page_test']??null)===true;
    }

    /** Only the exact eight current member groups can activate the category namespace. */
    private static function compositeContextClasses(array $filters, string $locale): array
    {
        if (!in_array(strtolower(trim($locale)),['uk','ua'],true)
            || !self::ownsCategory((int)\Illuminate\Support\Arr::get($filters,'__meta.theory_category_id'),
                (string)\Illuminate\Support\Arr::get($filters,'__meta.theory_category_slug'))
            || ($filters['theory_page_mixed_type_profile']??null)!==self::REVISION
            || !self::isCategoryContext($filters) || \Illuminate\Support\Arr::get($filters,'__meta.mode')!=='filters'
            || \Illuminate\Support\Arr::get($filters,'__meta.aggregated_theory_category_test')!==true
            || \Illuminate\Support\Arr::get($filters,'__meta.theory_page_mixed_polyglot_test')!==true
            || filled($filters['course_slug']??null) || !is_array($filters['seeder_classes']??null)
            || count($filters['seeder_classes'])!==16
            || !is_array($filters['theory_category_page_groups']??null)
            || count($filters['theory_category_page_groups'])!==8) { return []; }
        $members=self::compositeMembers(); if (count($members)!==8) { return []; }
        $seen=[]; $classes=[];
        foreach ($filters['theory_category_page_groups'] as $group) {
            $id=$group['page_id']??null;
            if (!is_int($id) || !isset($members[$id]) || isset($seen[$id])) { return []; }
            $entry=$members[$id]; $pair=$group['seeder_classes']??null;
            if (($group['page_slug']??null)!==$entry['slug'] || ($group['quality_profile']??null)!==$entry['profile']
                || ($group['page_seeder_class']??null)!==$entry['owner'] || !is_array($pair) || count($pair)!==2
                || self::classes($pair)!==self::classes($entry['seeder_classes'])) { return []; }
            $seen[$id]=true; array_push($classes,...$entry['seeder_classes']);
        }
        $classes=self::classes($classes);
        return count($classes)===16 && self::classes($filters['seeder_classes'])===$classes?$classes:[];
    }

    public static function stateNamespace(array $filters, string $locale, string $uuid=''): ?string
    {
        if (!self::isCategoryContext($filters)) { return parent::stateNamespace($filters,$locale,$uuid); }
        $classes=self::compositeContextClasses($filters,$locale);
        return $classes===[]?null:'stq-parent-'.substr(hash('sha256',self::REVISION."\0uk\0".implode("\0",$classes)),0,20);
    }

    public static function testContextNeedsRefresh(array $cached, array $filters, string $locale, string $uuid=''): bool
    {
        if (!self::isCategoryContext($filters)) { return parent::testContextNeedsRefresh($cached,$filters,$locale,$uuid); }
        $classes=self::compositeContextClasses($filters,$locale);
        return $classes!==[] && self::cachedBankMembershipNeedsRefresh($cached,$classes);
    }
}

