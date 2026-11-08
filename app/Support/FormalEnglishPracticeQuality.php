<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned Formal English banks; historical questions, courses and progress stay intact. */
final class FormalEnglishPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'feq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = array (
  'formal-register-and-nominalisation-basics' => 'FormalRegisterAndNominalisationBasics',
  'nominalisation-formal-register' => 'NominalisationFormalRegister',
  'register-tone-and-paraphrase' => 'RegisterToneAndParaphrase',
  'paraphrase-and-reformulation' => 'ParaphraseAndReformulation',
  'subjunctive-and-formal-structures' => 'SubjunctiveAndFormalStructures',
  'nominal-style-and-information-density' => 'NominalStyleAndInformationDensity',
);
    private const UUID_TOPICS = array (
  'formal-register-and-nominalisation-basics' => 'bas',
  'nominalisation-formal-register' => 'nom',
  'register-tone-and-paraphrase' => 'ton',
  'paraphrase-and-reformulation' => 'par',
  'subjunctive-and-formal-structures' => 'sub',
  'nominal-style-and-information-density' => 'sty',
);
    protected const UUID_PREFIX = 'feq-';
    protected const SOURCE_PREFIX = 'formal_english_quality_';
    protected const NAMESPACE_PREFIX = 'formal-english-quality-';
    protected const LABEL = 'Formal English Quality';
    protected const META_PREFIX = 'formal_english_quality';
    public const CATEGORY_ID = 55;
    public const CATEGORY_SLUG = 'formal-english';
    public const CATEGORY_TOPICS = array (
  0 => 'formal-register-and-nominalisation-basics',
  1 => 'nominalisation-formal-register',
  2 => 'register-tone-and-paraphrase',
  3 => 'paraphrase-and-reformulation',
  4 => 'subjunctive-and-formal-structures',
  5 => 'nominal-style-and-information-density',
);
    public const SAVED_TEST_COUNT = 12;
    public const RETAINED_TEST_PROFILES = array (
  'formal-register-and-nominalisation-basics' => 
  array (
    '35f5dd17-6c72-7be7-7ef5-fe60c33d3c41' => 
    array (
      0 => 'course-td-formal-register-and-nominalisation-basics',
      1 => '157c9a1926e0016cb61780c16baf739197cba929b218033ab05492d9e588348d',
    ),
    'polyglot-formal-register--7bfc998c65' => 
    array (
      0 => 'polyglot-formal-register-and-nominalisation-basics-b2',
      1 => 'f02ddfaa96004cf9ad9f974b2a185a63c268bc4b79b7e78fa20d3a4fc9586c37',
    ),
  ),
  'nominalisation-formal-register' => 
  array (
    'c29a62b4-a2d7-8612-3588-cf3264f2b6e8' => 
    array (
      0 => 'course-td-nominalisation-formal-register',
      1 => 'a99c1bc7a718614388877d53c396081c8f31b40d9e20216ccdaf385475180599',
    ),
    'polyglot-nominalisation-forma-c3b20e' => 
    array (
      0 => 'polyglot-nominalisation-formal-register-c1',
      1 => '6e582b597b8fdbecf126b4e9fcbfc73f6fdfc0d0164711a859d88980048ec1fb',
    ),
  ),
  'register-tone-and-paraphrase' => 
  array (
    '6fe16052-67de-1255-9a4e-b4f63ea8bf78' => 
    array (
      0 => 'course-td-register-tone-and-paraphrase',
      1 => '09391b676e861f01384ccd71fd8746525a24d909c1fa63cd6470e400658cdbf0',
    ),
    'polyglot-register-tone-and-pa-1ed309' => 
    array (
      0 => 'polyglot-register-tone-and-paraphrase-c1',
      1 => 'be76130971cde9cf5b271de585916bf216a669139d373683054308c602f63a23',
    ),
  ),
  'paraphrase-and-reformulation' => 
  array (
    'b3cd23d0-9b54-584e-6654-06034127bf03' => 
    array (
      0 => 'course-td-paraphrase-and-reformulation',
      1 => 'ada6e50afe7c97ded8c351ae19650dea1b93db20cc85020eff74d757c7316993',
    ),
    'polyglot-paraphrase-and-refor-eb0b93' => 
    array (
      0 => 'polyglot-paraphrase-and-reformulation-c2',
      1 => '6fb0a67503fc7dcb80db38131b1dfc3b11c5cd077cdfa135714be75d23a90492',
    ),
  ),
  'subjunctive-and-formal-structures' => 
  array (
    '48177c3d-20b9-b8a3-8719-dec271d794be' => 
    array (
      0 => 'course-td-subjunctive-and-formal-structures',
      1 => '0db8101510cf8507f6f18512a07ea32dafda4e4e999ce756eed3af284cc8dc7e',
    ),
    'polyglot-subjunctive-and-form-7292de' => 
    array (
      0 => 'polyglot-subjunctive-and-formal-structures-c1',
      1 => '9b4810bc1aabf72c1e122c9d442d3fba7ffecb8c57b63d75ec701f5048ee8f07',
    ),
  ),
  'nominal-style-and-information-density' => 
  array (
    '96705503-4364-5919-ccff-56101e6aeced' => 
    array (
      0 => 'course-td-nominal-style-and-information-density',
      1 => '40135be9351316c110f5926978f73e26deb94ed8ed7852ebd75c4c05512899df',
    ),
    'polyglot-nominal-style-and-in-d28499' => 
    array (
      0 => 'polyglot-nominal-style-and-information-density-c2',
      1 => 'f8128909a86dbaf7ac755b5ce61727fcb99d7a968c8901443856d98751f98318',
    ),
  ),
);
    public const RETAINED_DEFINITIONS = array (
  'formal-register-and-nominalisation-basics' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotFormalRegisterAndNominalisationBasicsB2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotFormalRegisterAndNominalisationBasicsB2LessonSeeder/definition.json',
      1 => '1da9dc3adf104382b087e7fade24428bf42e790e3c797c35c32243e83822c11a',
    ),
  ),
  'nominalisation-formal-register' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotNominalisationFormalRegisterC1LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotNominalisationFormalRegisterC1LessonSeeder/definition.json',
      1 => '8639cfd42b768e895456e039a174c51f0bc2570c39bc934531f94ab34020246b',
    ),
  ),
  'register-tone-and-paraphrase' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotRegisterToneAndParaphraseC1LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotRegisterToneAndParaphraseC1LessonSeeder/definition.json',
      1 => '0ee1e33f952761f32a19574b3dcfc27fc614bf7a75f561ddf65816f3d4912073',
    ),
  ),
  'paraphrase-and-reformulation' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotParaphraseAndReformulationC2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotParaphraseAndReformulationC2LessonSeeder/definition.json',
      1 => 'fb9d3e011f679d05c768727d83d7ac439869fbad0356251fd345c082a1064688',
    ),
  ),
  'subjunctive-and-formal-structures' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotSubjunctiveAndFormalStructuresC1LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotSubjunctiveAndFormalStructuresC1LessonSeeder/definition.json',
      1 => '73e3823255d8d2a835667baf5750cd8a0aeee3d0c27f5cae06a47863c564d885',
    ),
  ),
  'nominal-style-and-information-density' => 
  array (
    'Database\Seeders\V3\Polyglot\PolyglotNominalStyleAndInformationDensityC2LessonSeeder' => 
    array (
      0 => 'database/seeders/V3/Polyglot/PolyglotNominalStyleAndInformationDensityC2LessonSeeder/definition.json',
      1 => '4e6c2036183f4efff9fe64f30bf73e093cbc87a3d557c905cd080f4f328d8654',
    ),
  ),
);
    public const PAGES = array (
  'formal-register-and-nominalisation-basics' => 
  array (
    0 => 291,
    1 => 55,
    2 => 'formal-register-and-nominalisation-basics',
    3 => 'Database\Seeders\Page_V3\FormalEnglish\FormalRegisterAndNominalisationBasicsTheorySeeder',
  ),
  'nominalisation-formal-register' => 
  array (
    0 => 301,
    1 => 55,
    2 => 'nominalisation-formal-register',
    3 => 'Database\Seeders\Page_V3\FormalEnglish\NominalisationFormalRegisterTheorySeeder',
  ),
  'register-tone-and-paraphrase' => 
  array (
    0 => 307,
    1 => 55,
    2 => 'register-tone-and-paraphrase',
    3 => 'Database\Seeders\Page_V3\FormalEnglish\RegisterToneAndParaphraseTheorySeeder',
  ),
  'paraphrase-and-reformulation' => 
  array (
    0 => 322,
    1 => 55,
    2 => 'paraphrase-and-reformulation',
    3 => 'Database\Seeders\Page_V3\FormalEnglish\ParaphraseAndReformulationTheorySeeder',
  ),
  'subjunctive-and-formal-structures' => 
  array (
    0 => 304,
    1 => 55,
    2 => 'subjunctive-and-formal-structures',
    3 => 'Database\Seeders\Page_V3\FormalEnglish\SubjunctiveAndFormalStructuresTheorySeeder',
  ),
  'nominal-style-and-information-density' => 
  array (
    0 => 321,
    1 => 55,
    2 => 'nominal-style-and-information-density',
    3 => 'Database\Seeders\Page_V3\FormalEnglish\NominalStyleAndInformationDensityTheorySeeder',
  ),
);
    private const ORIGINALS = array (
  'formal-register-and-nominalisation-basics' => 
  array (
    'standard' => 
    array (
      0 => 'fe-frnb-v3-all',
      1 => 'a332574641f3d77899a8b54a924568a2b0ea3f79de669218f81b2f3bc30f40ba',
    ),
    'builder' => 
    array (
      0 => 'fe-frnb-poly-all',
      1 => '97e116bfa9376606a81ff36a516f65bb209ee5fd3eeac2e4bdd278d29f0fa42a',
    ),
  ),
  'nominalisation-formal-register' => 
  array (
    'standard' => 
    array (
      0 => 'fe-nfr-v3-all',
      1 => '7a09e7c597652a08c1d4eaccd39284dd0cff4f687083d2c6b066f0b0679ac4bf',
    ),
    'builder' => 
    array (
      0 => 'fe-nfr-poly-all',
      1 => 'f93cda4ffead397d23d3ba823672b52624d6732ac0c58a8bcd2152ca3788deda',
    ),
  ),
  'register-tone-and-paraphrase' => 
  array (
    'standard' => 
    array (
      0 => 'fe-rtp-v3-all',
      1 => 'b71a23aeaeab7544326dcfa595a4c8c69136b70078c9d756e76a8af40c6a4b25',
    ),
    'builder' => 
    array (
      0 => 'fe-rtp-poly-all',
      1 => '9c9bceecfdac01c31461528932edec20241308e6358a3969f71b3ca7b06d01f4',
    ),
  ),
  'paraphrase-and-reformulation' => 
  array (
    'standard' => 
    array (
      0 => 'fe-pr-v3-all',
      1 => '7570ffb323129279921b8807a14d877db7925c22a32cf4bdf771581b1c173ade',
    ),
    'builder' => 
    array (
      0 => 'fe-pr-poly-all',
      1 => '3f3acfa8f448f2bfd1f4fb6a6c539179b5bb3c35e827c1ef8b9ac0d13219b70c',
    ),
  ),
  'subjunctive-and-formal-structures' => 
  array (
    'standard' => 
    array (
      0 => 'fe-sfs-v3-all',
      1 => '7f4150130e0172bc8d5b1c51b22447671a936ee17ab51fc18c21f7a7108ca96e',
    ),
    'builder' => 
    array (
      0 => 'fe-sfs-poly-all',
      1 => '2546b686f9f9df2b33ed3e082aa9baa71bd36ba9d53bb55a4eb3aafc12f7a9b9',
    ),
  ),
  'nominal-style-and-information-density' => 
  array (
    'standard' => 
    array (
      0 => 'fe-nsid-v3-all',
      1 => '39568145eda6ae0dbe8e725ea5837daea37f9b43f89cf7da548740c69e640b8d',
    ),
    'builder' => 
    array (
      0 => 'fe-nsid-poly-all',
      1 => '6e541b89413eefb67266230ee0897dc56bb891c9fb15c6f116ca1902635539e7',
    ),
  ),
);

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Formal English practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/formal-english-quality/'.$topic.'.v1.json'];
        $map['additional_originals']=[];
        foreach (self::RETAINED_DEFINITIONS[$topic] as $class=>$binding) {
            $map['additional_originals'][]=['class'=>$class,'path'=>$binding[0],'sha256'=>$binding[1]];
        }
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'FormalEnglish' : 'Polyglot';
            $name = 'UkrainianMixedFeq'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'FormalEnglish/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
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
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Formal English author identity.'); }
        return self::UUID_PREFIX.self::UUID_TOPICS[$topic].'-'.strtolower($authorId);
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    /** Only finite reviewed aliases belong to this catalog. */
    private static function ownsQuestionUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^feq-(?:bas|nom|ton|par|sub|sty)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/D', $uuid) === 1;
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
            $payload['feq_option_caption_revision']=$payload['psp_content_revision'];
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
            if (($payload['feq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
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
