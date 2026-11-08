<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned Academic English banks; historical questions, courses and progress stay intact. */
final class AcademicEnglishPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'aeq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = array (
  'hedging-and-cautious-language-basics' => 'HedgingAndCautiousLanguageBasics',
  'hedging-and-cautious-language' => 'HedgingAndCautiousLanguage',
  'stance-register-and-evaluation' => 'StanceRegisterAndEvaluation',
  'argumentation-and-academic-tone' => 'ArgumentationAndAcademicTone',
);
    private const UUID_TOPICS = array (
  'hedging-and-cautious-language-basics' => 'hcb',
  'hedging-and-cautious-language' => 'hed',
  'stance-register-and-evaluation' => 'sta',
  'argumentation-and-academic-tone' => 'arg',
);
    protected const UUID_PREFIX = 'aeq-';
    protected const SOURCE_PREFIX = 'academic_english_quality_';
    protected const NAMESPACE_PREFIX = 'academic-english-quality-';
    protected const LABEL = 'Academic English Quality';
    protected const META_PREFIX = 'academic_english_quality';
    public const CATEGORY_ID = 54;
    public const CATEGORY_SLUG = 'academic-english';
    public const CATEGORY_TOPICS = array (
  0 => 'hedging-and-cautious-language-basics',
  1 => 'hedging-and-cautious-language',
  2 => 'stance-register-and-evaluation',
  3 => 'argumentation-and-academic-tone',
);
    public const SAVED_TEST_COUNT = 8;
    public const RETAINED_TEST_PROFILES = array (
  'hedging-and-cautious-language-basics' => 
  array (
    '31985c81-92c0-bd9f-6c34-cd97892be3b0' => 
    array (
      0 => 'course-td-hedging-and-cautious-language-basics',
      1 => '5c77c625abd31622000367dc70040e930aaf263f08ff5645a3ca41935750adba',
    ),
    'polyglot-hedging-and-caut-82ebf43f81' => 
    array (
      0 => 'polyglot-hedging-and-cautious-language-basics-b2',
      1 => 'b66cca1e6e550d2db59bdad7026d8421fb1525c805a0b8c3ca9e736a03ae5b2b',
    ),
  ),
  'hedging-and-cautious-language' => 
  array (
    'bdfc9c76-e993-f2e9-446a-9c134f7f188e' => 
    array (
      0 => 'course-td-hedging-and-cautious-language',
      1 => '339bb252d0a53e4f510bc8eb4ad576315e67b905cb2de96728960858f18e4aa5',
    ),
    'polyglot-hedging-and-cautious-38bfbe' => 
    array (
      0 => 'polyglot-hedging-and-cautious-language-c1',
      1 => '842dfc7e4a2581e633a63a85939feedafeb63c9a1c94efb2f31ce8985acf1e2c',
    ),
  ),
  'stance-register-and-evaluation' => 
  array (
    '85f037c7-d664-982a-1a2d-36df8371af6e' => 
    array (
      0 => 'course-td-stance-register-and-evaluation',
      1 => 'c7a8bcaf1257d981c822b1ab7a8358c3f0d837b7b869a89a1d90dc211d766dca',
    ),
    'polyglot-stance-register-and-6a403b' => 
    array (
      0 => 'polyglot-stance-register-and-evaluation-c2',
      1 => 'e7471df0d8c891d3c531d62533c561dd89e46ecb0fd92e9e5b14b9adcd3db05c',
    ),
  ),
  'argumentation-and-academic-tone' => 
  array (
    'e0490f95-94fb-f942-20b6-d8e94f0b249e' => 
    array (
      0 => 'course-td-argumentation-and-academic-tone',
      1 => '197595a37f6f239b7dce2b11f5a27372ba2020a05f924151ce1242c8e7e9cdb7',
    ),
    'polyglot-argumentation-and-ac-7e229c' => 
    array (
      0 => 'polyglot-argumentation-and-academic-tone-c2',
      1 => '54cf85e68acd39e906901cec056495ac1856693bd500c4889e493965361fc5da',
    ),
  ),
);
    public const RETAINED_DEFINITIONS = array (
  'hedging-and-cautious-language-basics' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotHedgingAndCautiousLanguageBasicsB2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotHedgingAndCautiousLanguageBasicsB2LessonSeeder/definition.json',
      1 => 'f3bc8013c28f91e64c429b8efcb1feee3909d469be9a611ca289993873ccf1c8',
    ),
  ),
  'hedging-and-cautious-language' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotHedgingAndCautiousLanguageC1LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotHedgingAndCautiousLanguageC1LessonSeeder/definition.json',
      1 => 'a43040c2470e104e64fd15b43b5aeb5b78e075aa2acc2dac8c8c8bb824e61e89',
    ),
  ),
  'stance-register-and-evaluation' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotStanceRegisterAndEvaluationC2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotStanceRegisterAndEvaluationC2LessonSeeder/definition.json',
      1 => '0156044adc8cd3a3d5fde22c13ec5b14e4bb230dfe9eb2991bc0d273bbf248d9',
    ),
  ),
  'argumentation-and-academic-tone' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotArgumentationAndAcademicToneC2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotArgumentationAndAcademicToneC2LessonSeeder/definition.json',
      1 => 'd305cf49b469fe0e3715939c8cd8718e4b8e4a77be3681a923c2e6fcb740b830',
    ),
  ),
);
    public const PAGES = array (
  'hedging-and-cautious-language-basics' => 
  array (
    0 => 293,
    1 => 54,
    2 => 'hedging-and-cautious-language-basics',
    3 => 'Database\Seeders\Page_V3\AcademicEnglish\HedgingAndCautiousLanguageBasicsTheorySeeder',
  ),
  'hedging-and-cautious-language' => 
  array (
    0 => 298,
    1 => 54,
    2 => 'hedging-and-cautious-language',
    3 => 'Database\Seeders\Page_V3\AcademicEnglish\HedgingAndCautiousLanguageTheorySeeder',
  ),
  'stance-register-and-evaluation' => 
  array (
    0 => 314,
    1 => 54,
    2 => 'stance-register-and-evaluation',
    3 => 'Database\Seeders\Page_V3\AcademicEnglish\StanceRegisterAndEvaluationTheorySeeder',
  ),
  'argumentation-and-academic-tone' => 
  array (
    0 => 323,
    1 => 54,
    2 => 'argumentation-and-academic-tone',
    3 => 'Database\Seeders\Page_V3\AcademicEnglish\ArgumentationAndAcademicToneTheorySeeder',
  ),
);
    private const ORIGINALS = array (
  'hedging-and-cautious-language-basics' => 
  array (
    'standard' => 
    array (
      0 => 'ae-hclb-v3-all',
      1 => '87e382713ca95cf3dd9d1467ff3ac8e66aabcffa9a1d0e65b160ec2bb6621fbc',
    ),
    'builder' => 
    array (
      0 => 'ae-hclb-poly-all',
      1 => '64f51e49e726a4ced4dce547ef7712518370047d818d1abb63b9b99733b3d50a',
    ),
  ),
  'hedging-and-cautious-language' => 
  array (
    'standard' => 
    array (
      0 => 'ae-hcl-v3-all',
      1 => 'a55c38e2d690119c73aef594d7b2f561b9d5433b2015a05ecd821be1ca6ca8c4',
    ),
    'builder' => 
    array (
      0 => 'ae-hcl-poly-all',
      1 => '2addc14e1d045040d14dce089825e50219d6b3804bbccde17e0190d1512badf6',
    ),
  ),
  'stance-register-and-evaluation' => 
  array (
    'standard' => 
    array (
      0 => 'ae-sre-v3-all',
      1 => '46d1db29f44a7cc9de1106e4aa681d32a1ac1cbca0eea37633c1bfd3dd66e694',
    ),
    'builder' => 
    array (
      0 => 'ae-sre-poly-all',
      1 => '4e4e1d92bcf572be30ff22422ecf2642b823c35fa1ba48c79390e2ed45826e4f',
    ),
  ),
  'argumentation-and-academic-tone' => 
  array (
    'standard' => 
    array (
      0 => 'ae-aat-v3-all',
      1 => '15872382dd1c4c6745623b917e68b2966d1b3ec960cb672231ac8eff8f3b7fb3',
    ),
    'builder' => 
    array (
      0 => 'ae-aat-poly-all',
      1 => '8e7925f691d2ae1839129e5d7ed9782e93553961c9b6c623a63ea7eb9005895d',
    ),
  ),
);

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Academic English practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/academic-english-quality/'.$topic.'.v1.json'];
        $map['additional_originals']=[];
        foreach (self::RETAINED_DEFINITIONS[$topic] as $class=>$binding) {
            $map['additional_originals'][]=['class'=>$class,'path'=>$binding[0],'sha256'=>$binding[1]];
        }
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'AcademicEnglish' : 'Polyglot';
            $name = 'UkrainianMixedAeq'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'AcademicEnglish/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
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
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Academic English author identity.'); }
        return self::UUID_PREFIX.self::UUID_TOPICS[$topic].'-'.strtolower($authorId);
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    /** Only finite reviewed aliases belong to this catalog. */
    private static function ownsQuestionUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^aeq-(?:hcb|hed|sta|arg)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/D', $uuid) === 1;
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
            $payload['aeq_option_caption_revision']=$payload['psp_content_revision'];
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
            if (($payload['aeq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
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
        return count($members)===4?$members:[];
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

