<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned mixed-revision banks; historical questions, courses and progress stay intact. */
final class MixedRevisionPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'mrq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = array (
  'a1-mixed-revision' => 'A1MixedRevision',
  'a2-mixed-revision' => 'A2MixedRevision',
  'b1-mixed-revision' => 'B1MixedRevision',
  'b2-mixed-revision' => 'B2MixedRevision',
  'c1-mixed-revision' => 'C1MixedRevision',
  'c2-mixed-revision' => 'C2MixedRevision',
);
    private const UUID_TOPICS = array (
  'a1-mixed-revision' => 'a1',
  'a2-mixed-revision' => 'a2',
  'b1-mixed-revision' => 'b1',
  'b2-mixed-revision' => 'b2',
  'c1-mixed-revision' => 'c1',
  'c2-mixed-revision' => 'c2',
);
    protected const UUID_PREFIX = 'mrq-';
    protected const SOURCE_PREFIX = 'mixed_revision_quality_';
    protected const NAMESPACE_PREFIX = 'mixed-revision-quality-';
    protected const LABEL = 'Mixed Revision Quality';
    protected const META_PREFIX = 'mixed_revision_quality';
    public const CATEGORY_ID = 59;
    public const CATEGORY_SLUG = 'mixed-revision';
    public const CATEGORY_TOPICS = array (
  0 => 'a1-mixed-revision',
  1 => 'a2-mixed-revision',
  2 => 'b1-mixed-revision',
  3 => 'b2-mixed-revision',
  4 => 'c1-mixed-revision',
  5 => 'c2-mixed-revision',
);
    public const SAVED_TEST_COUNT = 12;
    public const RETAINED_TEST_PROFILES = array (
  'a1-mixed-revision' => 
  array (
    'polyglot-final-drill-a1-saved-test' => 
    array (
      0 => 'polyglot-final-drill-a1',
      1 => '6a8720938fe7457cbe45ac52fc759903be6150608618accaed07e21ea4383727',
    ),
  ),
  'a2-mixed-revision' => 
  array (
    'polyglot-final-drill-a2-saved-test' => 
    array (
      0 => 'polyglot-final-drill-a2',
      1 => 'ac4a976004e3bf3faec05d0235411396cd8a9f4cb07c839758798bd26c5913f3',
    ),
  ),
  'b1-mixed-revision' => 
  array (
    'polyglot-final-drill-b1-saved-test' => 
    array (
      0 => 'polyglot-final-drill-b1',
      1 => 'f026008ce7cea63e0e2fa8195ec39d42b7eb2491e8d7b5902ca9bfe0eedf5cb7',
    ),
  ),
  'b2-mixed-revision' => 
  array (
    'polyglot-final-drill-b2-fd5255bec1' => 
    array (
      0 => 'polyglot-final-drill-b2',
      1 => '7c60e67d98b66c93eba3305acda6889a4ea5dacbbda1fb41f6a8883612641979',
    ),
  ),
  'c1-mixed-revision' => 
  array (
    'polyglot-final-drill-c1-saved-test' => 
    array (
      0 => 'polyglot-final-drill-c1',
      1 => '7ec5535dddb1e42cec8b42a22c399c8544a2d127319864b7a5f4f40f08742c18',
    ),
  ),
  'c2-mixed-revision' => 
  array (
    'polyglot-final-drill-c2-saved-test' => 
    array (
      0 => 'polyglot-final-drill-c2',
      1 => 'b057a0825340776c36c371d45c0c6390b9d0d782e8aaab1da22fcb5e4e81c980',
    ),
  ),
);
    public const RETAINED_DEFINITIONS = array (
  'a1-mixed-revision' => 
  array (
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillLessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotFinalDrillLessonSeeder/definition.json',
      1 => '22c4de84290254704b503654b0d8fb230a05704399a7156ecd600669f1b38b19',
    ),
  ),
  'a2-mixed-revision' => 
  array (
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillA2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotFinalDrillA2LessonSeeder/definition.json',
      1 => '8df7f00b40e1ab20aa62f74440c5f773079053c4e377ca1e5c5b2c43f96119ed',
    ),
  ),
  'b1-mixed-revision' => 
  array (
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillB1LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotFinalDrillB1LessonSeeder/definition.json',
      1 => '84d57c91fa73729e2fc2032b2ef7bf2c4d08a99965ca02013b8d81821677fd7b',
    ),
  ),
  'b2-mixed-revision' => 
  array (
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillB2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotFinalDrillB2LessonSeeder/definition.json',
      1 => '16e43dd378adcf1ca76e0d183bafa2b1166b4ff74cbc22023549cfd8c1b82df1',
    ),
  ),
  'c1-mixed-revision' => 
  array (
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillC1LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotFinalDrillC1LessonSeeder/definition.json',
      1 => 'af947264360a4ab0e2d85d6a8c74f62605c5d25c977b7db84e8b0b1da2bd0fe5',
    ),
  ),
  'c2-mixed-revision' => 
  array (
    'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillC2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotFinalDrillC2LessonSeeder/definition.json',
      1 => 'eb528ca413a2d23b6b3a353f818dcfe52eeee90260832abc51d2cb0ec7b4dd14',
    ),
  ),
);
    public const PAGES = array (
  'a1-mixed-revision' => 
  array (
    0 => 325,
    1 => 59,
    2 => 'a1-mixed-revision',
    3 => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarA1MixedRevisionTheorySeeder',
  ),
  'a2-mixed-revision' => 
  array (
    0 => 326,
    1 => 59,
    2 => 'a2-mixed-revision',
    3 => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarA2MixedRevisionTheorySeeder',
  ),
  'b1-mixed-revision' => 
  array (
    0 => 327,
    1 => 59,
    2 => 'b1-mixed-revision',
    3 => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarB1MixedRevisionTheorySeeder',
  ),
  'b2-mixed-revision' => 
  array (
    0 => 328,
    1 => 59,
    2 => 'b2-mixed-revision',
    3 => 'Database\\Seeders\\Page_V3\\BasicGrammar\\B2MixedRevisionTheorySeeder',
  ),
  'c1-mixed-revision' => 
  array (
    0 => 329,
    1 => 59,
    2 => 'c1-mixed-revision',
    3 => 'Database\\Seeders\\Page_V3\\BasicGrammar\\C1MixedRevisionTheorySeeder',
  ),
  'c2-mixed-revision' => 
  array (
    0 => 330,
    1 => 59,
    2 => 'c2-mixed-revision',
    3 => 'Database\\Seeders\\Page_V3\\BasicGrammar\\C2MixedRevisionTheorySeeder',
  ),
);
    private const ORIGINALS = array (
  'a1-mixed-revision' => 
  array (
    'standard' => 
    array (
      0 => 'mr-a1-v3-all',
      1 => '2db2284a9d045110a85ceaf0df8be8d40c22cc2b5eeff8017e4b4964aec2de31',
    ),
    'builder' => 
    array (
      0 => 'mr-a1-poly-all',
      1 => 'cade8b9a8e14f90400fc5572380fb9e3a1a6f30201fd5a2b781017fef94a9562',
    ),
  ),
  'a2-mixed-revision' => 
  array (
    'standard' => 
    array (
      0 => 'mr-a2-v3-all',
      1 => 'c4cef9061302f3e90a5a453375c2d1377013aab888d798164d5487c640f855aa',
    ),
    'builder' => 
    array (
      0 => 'mr-a2-poly-all',
      1 => 'e85cc74ec698972498823f2826b77afac4d19b8a393167c561f720bf97c8c08a',
    ),
  ),
  'b1-mixed-revision' => 
  array (
    'standard' => 
    array (
      0 => 'mr-b1-v3-all',
      1 => 'e159f77142f411ade7a4c3bc0b77a494b2061cc252c445a929a4e4ba73c3d7ff',
    ),
    'builder' => 
    array (
      0 => 'mr-b1-poly-all',
      1 => '6f170171b77c99c92bfd3779e6e996f8bc419b7069729d02bf8b2c6a192d977f',
    ),
  ),
  'b2-mixed-revision' => 
  array (
    'standard' => 
    array (
      0 => 'mr-b2-v3-all',
      1 => '69f94675a21a6f417b07faa40680fdbffe96c681b5bfa04a7b9749508548cf0c',
    ),
    'builder' => 
    array (
      0 => 'mr-b2-poly-all',
      1 => '7468e2660e3969ca7a7dc44dd874fbfdda9c3f8479f090637f310a5668d10d61',
    ),
  ),
  'c1-mixed-revision' => 
  array (
    'standard' => 
    array (
      0 => 'mr-c1-v3-all',
      1 => '50b7218813e0c4e367be1d1e2e990b34b0e1eba5a64d635030d9ab575caf885c',
    ),
    'builder' => 
    array (
      0 => 'mr-c1-poly-all',
      1 => 'f360ab9ed94a95ed81d9882baca999c0110376dc371bb1a187bc3f8f587c3d25',
    ),
  ),
  'c2-mixed-revision' => 
  array (
    'standard' => 
    array (
      0 => 'mr-c2-v3-all',
      1 => 'd0ffa07c7bdf1626350833f60d5ab24c06ef701139ed4992947ca75a1bd9555a',
    ),
    'builder' => 
    array (
      0 => 'mr-c2-poly-all',
      1 => 'b1939fce4aa1c324437cedbc738035ccebdf91e9e9707dd2ce416cbf601743b4',
    ),
  ),
);

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Mixed Revision practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/mixed-revision-quality/'.$topic.'.v1.json'];
        $map['additional_originals']=[];
        foreach (self::RETAINED_DEFINITIONS[$topic] as $class=>$binding) {
            $map['additional_originals'][]=['class'=>$class,'path'=>$binding[0],'sha256'=>$binding[1]];
        }
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'MixedRevision' : 'Polyglot';
            $name = 'UkrainianMixedMrq'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'MixedRevision/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
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
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Mixed Revision author identity.'); }
        return self::UUID_PREFIX.self::UUID_TOPICS[$topic].'-'.strtolower($authorId);
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    /** Only finite reviewed aliases belong to this catalog. */
    private static function ownsQuestionUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^mrq-(?:a1|a2|b1|b2|c1|c2)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/D', $uuid) === 1;
    }

    protected static function requiredRowsPerLevel(): array { return ['gap' => 2, 'compose' => [1]]; }

    /** Sparse historical definitions are UK by their exact class/hash-bound originals only. */
    protected static function definitionLocale(array $definition): mixed
    {
        return array_key_exists('defaults',$definition) ? parent::definitionLocale($definition) : 'uk';
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
            $payload['mrq_option_caption_revision']=$payload['psp_content_revision'];
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
            if (($payload['mrq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
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

    /** A declarative profile alone must not opt a foreign/partially migrated page into mixed-revision behavior. */
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
        return count($members)===6?$members:[];
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

