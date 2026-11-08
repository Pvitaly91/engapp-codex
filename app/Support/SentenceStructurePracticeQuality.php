<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned Sentence Structure banks; historical questions, courses and progress stay intact. */
final class SentenceStructurePracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'ssq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = array (
  'cleft-sentences-basics' => 'CleftSentencesBasics',
  'cleft-sentences-emphasis' => 'CleftSentencesEmphasis',
  'ellipsis-substitution-and-reference' => 'EllipsisSubstitutionAndReference',
  'complex-noun-phrases' => 'ComplexNounPhrases',
);
    private const UUID_TOPICS = array (
  'cleft-sentences-basics' => 'csb',
  'cleft-sentences-emphasis' => 'cse',
  'ellipsis-substitution-and-reference' => 'esr',
  'complex-noun-phrases' => 'cnp',
);
    protected const UUID_PREFIX = 'ssq-';
    protected const SOURCE_PREFIX = 'sentence_structure_quality_';
    protected const NAMESPACE_PREFIX = 'sentence-structure-quality-';
    protected const LABEL = 'Sentence Structure Quality';
    protected const META_PREFIX = 'sentence_structure_quality';
    public const CATEGORY_ID = 53;
    public const CATEGORY_SLUG = 'sentence-structure';
    public const CATEGORY_TOPICS = array (
  0 => 'cleft-sentences-basics',
  1 => 'cleft-sentences-emphasis',
  2 => 'ellipsis-substitution-and-reference',
  3 => 'complex-noun-phrases',
);
    public const SAVED_TEST_COUNT = 8;
    public const RETAINED_TEST_PROFILES = array (
  'cleft-sentences-basics' => 
  array (
    'f6536e43-f76d-0cdf-b90e-852641b3a367' => 
    array (
      0 => 'course-td-cleft-sentences-basics',
      1 => '8b9b6c9af644687400d8fdcf47d3bdfd8999be42a0aa5658bf04f41f26b43f5a',
    ),
    'polyglot-cleft-sentences--214a1b03dd' => 
    array (
      0 => 'polyglot-cleft-sentences-basics-b2',
      1 => 'c0d8350288ea2444295fbfbb2a8ae1beebc934fa5cd364f88c2dc079b9fdb1fb',
    ),
  ),
  'cleft-sentences-emphasis' => 
  array (
    'acbc6978-10dc-d0b7-2429-2b37120ec062' => 
    array (
      0 => 'course-td-cleft-sentences-emphasis',
      1 => 'fd52a487e2d5731cb881b92bb93a7a37cc70ac3c3bf1b060180db6f47a3ab016',
    ),
    'polyglot-cleft-sentences-emph-dd6fd8' => 
    array (
      0 => 'polyglot-cleft-sentences-emphasis-c1',
      1 => 'ea61d8f5d8e056d073535428e5f4e097c387cc91e1083a74aa67cb7a342df392',
    ),
  ),
  'ellipsis-substitution-and-reference' => 
  array (
    'ef76807f-1abe-4d1b-76c2-b9425179ae68' => 
    array (
      0 => 'course-td-ellipsis-substitution-and-reference',
      1 => 'dda512618bee98eb39984b9b15913a78883e25caab7f57089dab333f7e995034',
    ),
    'polyglot-ellipsis-substitutio-117c22' => 
    array (
      0 => 'polyglot-ellipsis-substitution-and-reference-c2',
      1 => 'db923caed8a611a5942b6da03f11aa09216b2a0c776716fdb014c0f81690b1db',
    ),
  ),
  'complex-noun-phrases' => 
  array (
    'd30108eb-fa86-8cee-3452-fee36e51c9ee' => 
    array (
      0 => 'course-td-complex-noun-phrases',
      1 => 'd5ca10b9ade85f9799defb9e079137a52ce1db8a917c0114d22560ce74e3a17b',
    ),
    'polyglot-complex-noun-phrases-e9b904' => 
    array (
      0 => 'polyglot-complex-noun-phrases-c2',
      1 => '4990c554bf61e376f9c5d470bcf0bd9539abcd594f8d5a98bf5402d25bcd4f2d',
    ),
  ),
);
    public const RETAINED_DEFINITIONS = array (
  'cleft-sentences-basics' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotCleftSentencesBasicsB2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotCleftSentencesBasicsB2LessonSeeder/definition.json',
      1 => 'ce4a9f003b2886ff47ba12a5b139c9d4f7262fc3f3a3276e457450f00487e659',
    ),
  ),
  'cleft-sentences-emphasis' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotCleftSentencesEmphasisC1LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotCleftSentencesEmphasisC1LessonSeeder/definition.json',
      1 => 'e8d182e43b858694da4c92d079bb89901f728091a2dfaec10db4441642a01289',
    ),
  ),
  'ellipsis-substitution-and-reference' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotEllipsisSubstitutionAndReferenceC2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotEllipsisSubstitutionAndReferenceC2LessonSeeder/definition.json',
      1 => '13f250a450173ec76c344517d80f9aa536c845b0d40d238898227bf29a6b67f9',
    ),
  ),
  'complex-noun-phrases' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotComplexNounPhrasesC2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotComplexNounPhrasesC2LessonSeeder/definition.json',
      1 => '16a1774f02a2151a0bbf6874bfaa050215c77e2ce24e962649bf218072faa77c',
    ),
  ),
);
    public const PAGES = array (
  'cleft-sentences-basics' => 
  array (
    0 => 289,
    1 => 53,
    2 => 'cleft-sentences-basics',
    3 => 'Database\Seeders\Page_V3\SentenceStructure\CleftSentencesBasicsTheorySeeder',
  ),
  'cleft-sentences-emphasis' => 
  array (
    0 => 297,
    1 => 53,
    2 => 'cleft-sentences-emphasis',
    3 => 'Database\Seeders\Page_V3\SentenceStructure\CleftSentencesEmphasisTheorySeeder',
  ),
  'ellipsis-substitution-and-reference' => 
  array (
    0 => 311,
    1 => 53,
    2 => 'ellipsis-substitution-and-reference',
    3 => 'Database\Seeders\Page_V3\SentenceStructure\EllipsisSubstitutionAndReferenceTheorySeeder',
  ),
  'complex-noun-phrases' => 
  array (
    0 => 312,
    1 => 53,
    2 => 'complex-noun-phrases',
    3 => 'Database\Seeders\Page_V3\SentenceStructure\ComplexNounPhrasesTheorySeeder',
  ),
);
    private const ORIGINALS = array (
  'cleft-sentences-basics' => 
  array (
    'standard' => 
    array (
      0 => 'ss-csb-v3-all',
      1 => 'a2e5e8dcae28b557880a4121b38ae81cd4a2e4178ec85bf5e93fb61659b99deb',
    ),
    'builder' => 
    array (
      0 => 'ss-csb-poly-all',
      1 => '74b69bee39298c855407239cc4d475e52b7d5458b977517664e799a6a656ad13',
    ),
  ),
  'cleft-sentences-emphasis' => 
  array (
    'standard' => 
    array (
      0 => 'ss-cse-v3-all',
      1 => 'd4df6f77e1e118d6e23f50659923bfb90234552bf7616ddc4b718487202aff2b',
    ),
    'builder' => 
    array (
      0 => 'ss-cse-poly-all',
      1 => '61009e908ae54aab79278f46d5c5b483a84173f238539220aba26d943ee07495',
    ),
  ),
  'ellipsis-substitution-and-reference' => 
  array (
    'standard' => 
    array (
      0 => 'ss-esr-v3-all',
      1 => 'acf4571e299ffdf228117ceb164525f497357b886c8bab1f73b6dabbafad02ec',
    ),
    'builder' => 
    array (
      0 => 'ss-esr-poly-all',
      1 => 'cf930ac20d730e70f91882361f7d5e6358da9760a683cc84f7033cb2091c7290',
    ),
  ),
  'complex-noun-phrases' => 
  array (
    'standard' => 
    array (
      0 => 'ss-cnp-v3-all',
      1 => 'c229634a635035a9df245e0d8d9b9703530fcec4ffbd1ca545a812db58677ede',
    ),
    'builder' => 
    array (
      0 => 'ss-cnp-poly-all',
      1 => '0b9abac4bc43cf0f5dfe001772255536058b9a5a51aa47426087145347578f18',
    ),
  ),
);

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Sentence Structure practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/sentence-structure-quality/'.$topic.'.v1.json'];
        $map['additional_originals']=[];
        foreach (self::RETAINED_DEFINITIONS[$topic] as $class=>$binding) {
            $map['additional_originals'][]=['class'=>$class,'path'=>$binding[0],'sha256'=>$binding[1]];
        }
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'SentenceStructure' : 'Polyglot';
            $name = 'UkrainianMixedSsq'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'SentenceStructure/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
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
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Sentence Structure author identity.'); }
        return self::UUID_PREFIX.self::UUID_TOPICS[$topic].'-'.strtolower($authorId);
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    /** Only finite reviewed aliases belong to this catalog. */
    private static function ownsQuestionUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^ssq-(?:csb|cse|esr|cnp)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/D', $uuid) === 1;
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
            $payload['ssq_option_caption_revision']=$payload['psp_content_revision'];
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
            if (($payload['ssq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
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
