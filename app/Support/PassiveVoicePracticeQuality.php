<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned Passive Voice banks; historical questions and courses stay intact. */
final class PassiveVoicePracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'pvq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'formation' => 'PassiveVoiceFormationRules',
        'causative' => 'PassiveVoiceCausative',
        'mistakes' => 'PassiveVoiceCommonMistakes',
        'style' => 'PassiveVoiceFormalityStyle',
        'get' => 'PassiveVoiceGetPassive',
        'impersonal' => 'PassiveVoiceImpersonalPassive',
        'modals' => 'PassiveVoiceModalVerbs',
        'negatives' => 'PassiveVoiceNegativesQuestions',
        'phrasal' => 'PassiveVoicePhrasalVerbs',
        'restrictions' => 'PassiveVoiceRestrictions',
        'two-object' => 'PassiveVoiceTwoObjectVerbs',
        'gerund' => 'PassiveVoicePassiveGerund',
        'infinitive' => 'PassiveVoicePassiveInfinitive',
        'agent' => 'PassiveVoiceByPhrase',
        'future-continuous' => 'PassiveVoiceFutureContinuous',
        'future-perfect' => 'PassiveVoiceFuturePerfect',
        'future-simple' => 'PassiveVoiceFutureSimple',
        'past-continuous' => 'PassiveVoicePastContinuous',
        'past-perfect' => 'PassiveVoicePastPerfect',
        'past-simple' => 'PassiveVoicePastSimple',
        'present-continuous' => 'PassiveVoicePresentContinuous',
        'present-perfect' => 'PassiveVoicePresentPerfect',
        'present-simple' => 'PassiveVoicePresentSimple',
        'advanced' => 'AdvancedPassiveVoice',
        'reporting' => 'PassiveReportingStructures',
        'complex-causative' => 'ComplexPassiveAndCausative',
        'complex-impersonal' => 'ComplexPassiveImpersonalStyle',
    ];
    protected const UUID_PREFIX = 'pvq-';
    protected const SOURCE_PREFIX = 'passive_voice_quality_';
    protected const NAMESPACE_PREFIX = 'passive-voice-quality-';
    protected const LABEL = 'Passive Voice Quality';
    protected const META_PREFIX = 'passive_voice_quality';
    public const CATEGORY_ID = 1;
    public const CATEGORY_SLUG = 'passive-voice';
    public const CATEGORY_TOPICS = ['formation', 'causative', 'mistakes', 'style', 'get', 'impersonal', 'modals', 'negatives', 'phrasal', 'restrictions', 'two-object', 'gerund', 'infinitive', 'agent', 'future-continuous', 'future-perfect', 'future-simple', 'past-continuous', 'past-perfect', 'past-simple', 'present-continuous', 'present-perfect', 'present-simple', 'advanced', 'reporting', 'complex-causative', 'complex-impersonal'];
    public const SAVED_TEST_COUNT = 54;

    private const ORIGINALS = [
        'formation' => ['standard' => ['pv-fr-v3-all', '6445912db54ce31dd7f62dc4b7c7c49bd3109b3028e9604321f1fde47722ef15'], 'builder' => ['pv-fr-poly-all', 'b5547685fa2983919c18669c9d937c11e1a52f35566acd037c9fd47f878d7a28']],
        'causative' => ['standard' => ['pv-cau-v3-all', 'bb408dfe527406a08a8bb26f740e573003c3fecfe05646b497b4a3407b27846e'], 'builder' => ['pv-cau-poly-all', 'f10a88191b9588c4e2f157fcb1664aaf920752096aa87c2be7d73e8ca9a0b222']],
        'mistakes' => ['standard' => ['pv-cm-v3-all', 'c5325bdd257d4b496343d2d464abdf62bb227c4dc052d57eff0fe57e64582db6'], 'builder' => ['pv-cm-poly-all', '76a5c216b2ee91afd4fb931d40c1b944778b16d7a1d470ca23a0da5b67764393']],
        'style' => ['standard' => ['pv-fs-v3-all', '2ea326b938b3086339644df04b1ab03aac45d51188013522add9a5c595634c9a'], 'builder' => ['pv-fs-poly-all', 'b9090033e7ab901c24493bc37f29188ed075a546c999e2a531a4321f0135c33f']],
        'get' => ['standard' => ['pv-gp-v3-all', '5157d47935acf86b923dcf7d7c19505a00478a2827d9592bb91de757d4ec6f94'], 'builder' => ['pv-gp-poly-all', 'c8c2d21f8401de6dc76c2e5b84eea660f66efed9a0ffbef52ac6c945ef218f88']],
        'impersonal' => ['standard' => ['pv-ip-v3-all', '1960e88c8f86714c8ab79a24a56ca77b219891bc911017ab9651bf8f21b5293b'], 'builder' => ['pv-ip-poly-all', '8b95547768aa8e9b426e05857bae96df1a87748fa72d9a65ef12d98d49410a74']],
        'modals' => ['standard' => ['pv-mv-v3-all', '69b6c66846562d1e996a387b1d8db616e2eca359b7ee83655772addc2863a2b9'], 'builder' => ['pv-mv-poly-all', '467ada025c07003ee15fbf22f9acb752000b259b04c08674498c9fbbcf3a4881']],
        'negatives' => ['standard' => ['pv-nq-v3-all', 'ae76c0325d2ec917f680646f9cce767fc7e28472266e79c99c23979729b59214'], 'builder' => ['pv-nq-poly-all', '4c436331f38a88c5ded1bece8a692bd09ecfb2fa78e4af696da05f2ee32e360f']],
        'phrasal' => ['standard' => ['pv-ph-v3-all', 'a5c1e6663228ae2293f9bd8a55b9657dd7070358f95359bb493da709ff08753b'], 'builder' => ['pv-ph-poly-all', '72a03edc66cb6e5c80aaf38dfa06e4694d411ee22fd3b41510b4ac336f9d9cf0']],
        'restrictions' => ['standard' => ['pv-r-v3-all', 'be0a1c664a938a6cd2fa125980e39d629cc34717848ba26049eb64fcdc255de0'], 'builder' => ['pv-r-poly-all', '0b564e08890065199c10cd6dfdb9f43abcc94699698fa180521b6b2656ce981f']],
        'two-object' => ['standard' => ['pv-tov-v3-all', '1229a37052af0ff4a026a9d1f8661cab8cbe5232d0135fc99fabeda205e1d65d'], 'builder' => ['pv-tov-poly-all', '7500599d9069a41c92190b323ce12bc73b7a9e6720c0abf7af485f7ec7f69789']],
        'gerund' => ['standard' => ['pv-pg-v3-all', '52f058c95aa924b7908c6fe257e4eee38daa540cd27b4cb90150301f09086032'], 'builder' => ['pv-pg-poly-all', 'd304482c9d38d7f8196fae06652e2efcb6ed9451f9ff235358b55b6b55924eb4']],
        'infinitive' => ['standard' => ['pv-pi-v3-all', '58bec3a09638bc8e7b93934731c7a9e0aa87cc94d03afb53f87fd5a591c35a21'], 'builder' => ['pv-pi-poly-all', '3b454b72d0a0090dd33d1ee90cdb8d220a9f7f1692c863d7f1a324122734f123']],
        'agent' => ['standard' => ['pv-bp-v3-all', '154fdfe015f5d834fbb7342c268d9999a99676061d1d76f2e6aacf6145d3d860'], 'builder' => ['pv-bp-poly-all', '297ef1d869e07f4c445f2cd1efb11b483f7ee0a75d4e9f187beb3bd044268885']],
        'future-continuous' => ['standard' => ['pv-fc-v3-all', '8e37fb0ec880561ce721527f32580f66c154b95ffaf596f919f0399cdff8591a'], 'builder' => ['pv-fc-poly-all', '5845b59025a0140cd97c8f9fa8e32df8556e278c1a993af3f328b39bf42e005d']],
        'future-perfect' => ['standard' => ['pv-fp-v3-all', '995f17ac09d471d8f2d0eab64ce6f29bd50cd81ae358324243e8ccba531dde19'], 'builder' => ['pv-fp-poly-all', 'e92a17eca7af74b03b91a54dd58f852384ae2e62973389ab4cf66b78b35ea48f']],
        'future-simple' => ['standard' => ['pv-future-simple-v3-all', 'a910cb286929fe8975cf2aa22f10d718c7812679b888de68020b9699f42bc084'], 'builder' => ['pv-future-simple-poly-all', '73f9518c716b3ae73c43b5030fa95e29a3f271fa0a8baae17a8778c1f6d01e07']],
        'past-continuous' => ['standard' => ['pv-pac-v3-all', 'a47a631289d009a16b54057812e045cdb1550cc84ecb4fcf13136163e806f94e'], 'builder' => ['pv-pac-poly-all', '7ea80b6f60b4f4c219d997db9bb77e81703ecca0b4d114c1134e2237c7ba741a']],
        'past-perfect' => ['standard' => ['pv-pf-v3-all', '79ab1bcf2da664b47df9a12f40a01bdfc7bf2d9ac15249cbc050e86d32aafe60'], 'builder' => ['pv-pf-poly-all', '2fd572398631e47f82f050b9f2175f1bfe2fe385a8cd670ef702b8e8e207dda7']],
        'past-simple' => ['standard' => ['pv-past-v3-all', 'ab48e308bb97f1d2c821b6e724e3b5330841f8d77e50de8719a32281f46259ea'], 'builder' => ['pv-past-poly-all', 'debc4ead804606501cde97eb324662c145a0f49ca32918cc32237507266b4c47']],
        'present-continuous' => ['standard' => ['pv-pc-v3-all', 'c5deee50da062967027486b640ff637fc1f7cab970d637292c822d52d06102dc'], 'builder' => ['pv-pc-poly-all', 'ec32c8b5e8cb1c12ecbd70b397549e23dfdf0e1fee468b6fcbc9bd1590279a8c']],
        'present-perfect' => ['standard' => ['pv-pp-v3-all', '248af9dfad454c194bf8818c11577f81006e3ddf264e9b34e41d95d733312389'], 'builder' => ['pv-pp-poly-all', 'af515f4921d979571c38e849b62d7fdd35ba11ae796b90efcccd6d7a5d00bbd3']],
        'present-simple' => ['standard' => ['pv-ps-v3-all', '94fe979c97ab24a40eec58a1b04101e9519bec5680e1c6ad34376b3edbcae44e'], 'builder' => ['pv-ps-poly-all', '3c1cdee60b8544b5aa4202be5e649ddc9bbb352ed110a87f8204b097a710de17']],
        'advanced' => ['standard' => ['pv-adv-v3-all', '5d130fda41a1cc62269352c3fa72f355acd8339e22a845f27915e70cc071e6da'], 'builder' => ['pv-adv-poly-all', '2c9974829978329bc2d1c477a9cda5e679b02ec58ad144536b8c0f8e98c8c19c']],
        'reporting' => ['standard' => ['pv-report-v3-all', 'a887a5c2a3d6c43fb917bb8c649a1956df709ff31f2448a30eb16da18c8f9f41'], 'builder' => ['pv-report-poly-all', 'c7df05c558383ad56c0ef298212d8ebc97fe009786defae4ced00527f72c0779']],
        'complex-causative' => ['standard' => ['pv-cxc-v3-all', '36da75a89ae168de3dc732741233bba6f7f1b76481870132493f0a584cd4f15e'], 'builder' => ['pv-cxc-poly-all', '5512023540276e1bfd8c10d5c75213103b848fd2b7177146824db7f0ed69a3f5']],
        'complex-impersonal' => ['standard' => ['pv-cis-v3-all', 'aa344ac052bbdd2d5cc720e057b54890be466b0a85e198df5a133f6820abb961'], 'builder' => ['pv-cis-poly-all', 'd6f98a1791271b502e34d9b69fd47c6655439db63fe9e2076f024b51bc515dfd']],
    ];
    /** Captured canonical page bindings; folder names do not determine ancestry. */
    public const PAGES = [
        'formation' => [1, 1, 'theory-passive-voice-formation-rules', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder'],
        'causative' => [92, 1, 'theory-passive-voice-causative', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceCausativeTheorySeeder'],
        'mistakes' => [93, 1, 'theory-passive-voice-common-mistakes', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceCommonMistakesTheorySeeder'],
        'style' => [94, 1, 'theory-passive-voice-formality-style', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormalityStyleTheorySeeder'],
        'get' => [95, 1, 'theory-passive-voice-get-passive', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceGetPassiveTheorySeeder'],
        'impersonal' => [96, 1, 'theory-passive-voice-impersonal-passive', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceImpersonalPassiveTheorySeeder'],
        'modals' => [97, 1, 'theory-passive-voice-modal-verbs', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceModalVerbsTheorySeeder'],
        'negatives' => [98, 1, 'theory-passive-voice-negatives-questions', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceNegativesQuestionsTheorySeeder'],
        'phrasal' => [99, 1, 'theory-passive-voice-phrasal-verbs', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoicePhrasalVerbsTheorySeeder'],
        'restrictions' => [100, 1, 'theory-passive-voice-restrictions', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceRestrictionsTheorySeeder'],
        'two-object' => [101, 1, 'theory-passive-voice-two-object-verbs', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceTwoObjectVerbsTheorySeeder'],
        'gerund' => [102, 17, 'theory-passive-voice-passive-gerund', 'Database\\Seeders\\Page_V3\\PassiveVoice\\InfinitivesGerund\\PassiveVoicePassiveGerundTheorySeeder'],
        'infinitive' => [103, 17, 'theory-passive-voice-passive-infinitive', 'Database\\Seeders\\Page_V3\\PassiveVoice\\InfinitivesGerund\\PassiveVoicePassiveInfinitiveTheorySeeder'],
        'agent' => [104, 1, 'theory-passive-voice-by-phrase', 'Database\\Seeders\\Page_V3\\PassiveVoice\\SpecialCases\\PassiveVoiceByPhraseTheorySeeder'],
        'future-continuous' => [105, 18, 'theory-passive-voice-future-continuous', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFutureContinuousTheorySeeder'],
        'future-perfect' => [106, 18, 'theory-passive-voice-future-perfect', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFuturePerfectTheorySeeder'],
        'future-simple' => [107, 18, 'theory-passive-voice-future-simple', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFutureSimpleTheorySeeder'],
        'past-continuous' => [108, 18, 'theory-passive-voice-past-continuous', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastContinuousTheorySeeder'],
        'past-perfect' => [109, 18, 'theory-passive-voice-past-perfect', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastPerfectTheorySeeder'],
        'past-simple' => [110, 1, 'theory-passive-voice-past-simple', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastSimpleTheorySeeder'],
        'present-continuous' => [111, 18, 'theory-passive-voice-present-continuous', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentContinuousTheorySeeder'],
        'present-perfect' => [112, 18, 'theory-passive-voice-present-perfect', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentPerfectTheorySeeder'],
        'present-simple' => [113, 1, 'theory-passive-voice-present-simple', 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder'],
        'advanced' => [282, 1, 'advanced-passive-voice', 'Database\\Seeders\\Page_V3\\PassiveVoice\\AdvancedPassiveVoiceTheorySeeder'],
        'reporting' => [296, 1, 'passive-reporting-structures', 'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveReportingStructuresTheorySeeder'],
        'complex-causative' => [305, 1, 'complex-passive-and-causative', 'Database\\Seeders\\Page_V3\\PassiveVoice\\ComplexPassiveAndCausativeTheorySeeder'],
        'complex-impersonal' => [319, 1, 'complex-passive-impersonal-style', 'Database\\Seeders\\Page_V3\\PassiveVoice\\ComplexPassiveImpersonalStyleTheorySeeder'],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Passive Voice practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic' => $topic, 'stem' => $stem, 'author' => 'docs/content/passive-voice-quality/'.$topic.'.v1.json'];
        foreach (['standard', 'builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'PassiveVoice' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'PassiveVoice/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
            $map[$kind] = ['class' => 'Database\\Seeders\\V3\\'.$namespace.'\\'.$name,
                'definition' => $directory.'/definition.json', 'loader' => $directory.'.php',
                'implementation' => $directory.'/'.$name.'.php',
                'old_definition' => 'database/seeders/V3/'.$oldName.'/definition.json',
                'old_class' => 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid' => self::ORIGINALS[$topic][$kind][0], 'old_sha256' => self::ORIGINALS[$topic][$kind][1]];
        }
        return $map;
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    public static function compile(string $topic, string $kind): array
    {
        static $compiled = [];
        $source = self::source($topic); self::originalDefinition($topic, $kind);
        $key = $topic.'|'.$kind.'|'.hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        if (isset($compiled[$key])) { return $compiled[$key]; }
        $definition = parent::compileAuthored($topic, $kind);
        return $compiled[$key] = $kind === 'builder'
            ? FinitePracticeChoiceOptions::compile($definition, $source, $topic, self::composeSlots(...)) : $definition;
    }

    private static function sourceBound(array $payload, array $source): bool
    {
        return ($payload['question'] ?? null) === $source['question']
            && (string) ($payload['type'] ?? '') === (string) $source['type']
            && ($payload['psp_content_revision'] ?? null) === hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            && ($payload['answer_map'] ?? null) === array_map(static fn ($m) => $m['answer'], $source['markers']);
    }

    /** Exact authored Ukrainian clues, also after cached DB-hint localization. */
    public static function restoreClarifiedHints(array $payload, string $locale): array
    {
        if ($locale !== 'uk' || !is_string($payload['uuid'] ?? null)
            || !str_starts_with($payload['uuid'], self::UUID_PREFIX)) { return $payload; }
        $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
        if (!$source || !self::sourceBound($payload, $source)) { return $payload; }
        $payload['verb_hint'] = $source['verb_hint'];
        $payload['verb_hints'] = array_fill_keys(array_keys($source['markers']), $source['verb_hint']);
        if ((string) $source['type'] === '4') {
            $payload['hint'] = $source['verb_hint'];
            if (array_key_exists('hintUk', $payload)) { $payload['hintUk'] = $source['verb_hint']; }
        }
        return $payload;
    }

    public static function decorate(array $payload): array
    {
        $payload = parent::decorate($payload);
        $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
        if ($source && self::sourceBound($payload, $source)) {
            if ((string) $source['type'] === '0') { $payload['passive_phrase_word_inputs'] = true; }
            // Display only: canonical punctuation and all accepted answers remain untouched.
            $payload['pvq_option_caption_revision'] = $payload['psp_content_revision'];
            $payload = self::restoreClarifiedHints($payload, 'uk');
            $payload = PresentPerfectPassiveWordSlots::decorate($payload, $source);
        }
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (parent::cachedNeedsRefresh($payloads)) { return true; }
        $sources = self::questions();
        foreach ($payloads as $payload) {
            $source = $sources[$payload['uuid'] ?? ''] ?? null;
            if (!$source) { continue; }
            if (($payload['pvq_option_caption_revision'] ?? null) !== hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) { return true; }
            if ((string) $source['type'] === '0' && ($payload['passive_phrase_word_inputs'] ?? null) !== true) { return true; }
            $wordSlots = PresentPerfectPassiveWordSlots::decorate($payload, $source);
            if (($wordSlots['pvq_word_slots'] ?? null) !== ($payload['pvq_word_slots'] ?? null)) { return true; }
            $expected = self::restoreClarifiedHints($payload, 'uk');
            foreach (['verb_hint', 'verb_hints', 'hint'] as $field) {
                if (array_key_exists($field, $expected) && ($payload[$field] ?? null) !== $expected[$field]) { return true; }
            }
        }
        return false;
    }

    public static function savedBuilderDisplayCount(string $uuid, string $slug, array $filters, string $locale): ?int
    {
        if ($locale !== 'uk') { return null; }
        foreach (array_keys(self::TOPICS) as $topic) {
            if ($uuid !== self::ORIGINALS[$topic]['builder'][0]) { continue; }
            try {
                $old = self::originalDefinition($topic, 'builder')['saved_test'];
                if ($slug !== $old['slug'] || self::stateNamespace($filters, $locale, $uuid) === null) { return null; }
                return count(self::compile($topic, 'builder')['questions']);
            } catch (\Throwable) { return null; }
        }
        return null;
    }

    public static function ownsCategory(int $id, string $slug): bool
    {
        return ([1 => 'passive-voice', 17 => 'passive-voice-infinitives-gerund', 18 => 'passive-voice-tenses'][$id] ?? null) === $slug;
    }

    /** Current exact linked pairs may supersede metadata in their unchanged historical definitions. */
    public static function mixedProfileDefinitions(array $definitions, array $linkedTests, string $locale): array
    {
        if ($locale !== 'uk' || count($linkedTests) !== 2 || count($definitions) !== 2) { return $definitions; }
        $linked = [];
        foreach ($linkedTests as $test) {
            if (!$test instanceof \App\Models\SavedGrammarTest || isset($linked[(string) $test->uuid])) { return $definitions; }
            $linked[(string) $test->uuid] = $test;
        }
        foreach (array_keys(self::TOPICS) as $topic) {
            $map = self::mapping($topic);
            if (!isset($linked[$map['standard']['old_saved_test_uuid']], $linked[$map['builder']['old_saved_test_uuid']])) { continue; }
            if (self::classes(array_keys($definitions)) !== self::classes([$map['standard']['old_class'], $map['builder']['old_class']])) { return $definitions; }
            try {
                $expected = [];
                foreach (['standard', 'builder'] as $kind) {
                    $bank = $map[$kind];
                    $current = self::definitionMetadata(self::originalDefinition($topic, $kind), $locale)['saved_test'];
                    $test = $linked[$bank['old_saved_test_uuid']];
                    if (($current['filters']['theory_page_mixed_type_profile'] ?? null) !== self::REVISION
                        || (string) $test->slug !== $current['slug'] || $test->filters !== $current['filters']) { return $definitions; }
                    $expected[$bank['old_class']] = $current;
                }
                $projected = [];
                foreach ($definitions as $class => $definition) {
                    if (!is_array($definition) || ($definition['seeder']['class'] ?? null) !== $class || !isset($expected[$class])) { return $definitions; }
                    $after = self::definitionMetadata($definition, $locale);
                    // Rejected source bindings are returned unchanged by definitionMetadata().
                    if ($after === $definition || ($after['saved_test'] ?? null) !== $expected[$class]) { return $definitions; }
                    $projected[$class] = $after;
                }
                return $projected;
            } catch (\Throwable) { return $definitions; }
        }
        return $definitions;
    }

    private static function categoryTopics(int $id): array
    {
        return match ($id) {
            // The caller aggregates descendant categories, not only directly owned pages.
            1 => self::CATEGORY_TOPICS,
            17 => ['gerund', 'infinitive'],
            18 => ['future-continuous', 'future-perfect', 'future-simple', 'past-continuous', 'past-perfect', 'present-continuous', 'present-perfect'],
            default => [],
        };
    }

    private static function classes(array $classes): array
    {
        if (array_filter($classes, static fn ($v) => !is_string($v)) !== []) { return []; }
        $classes = array_values(array_unique(array_map('strtolower', $classes))); sort($classes); return $classes;
    }

    public static function categoryProfile(int $id, string $slug, array $groups): ?string
    {
        if (!self::ownsCategory($id, $slug) || count($groups) !== count(self::categoryTopics($id))) { return null; }
        $seen = [];
        foreach ($groups as $group) {
            $page = $group['page'] ?? null;
            if (!$page instanceof \App\Models\Page || ($group['base_filters']['theory_page_mixed_type_profile'] ?? null) !== self::REVISION) { return null; }
            $topic = null;
            foreach (self::categoryTopics($id) as $key) { if (self::PAGES[$key][0] === (int) $page->getKey()) { $topic = $key; break; } }
            if ($topic === null || isset($seen[$topic]) || (string) $page->slug !== self::PAGES[$topic][2]
                || (string) $page->seeder !== self::PAGES[$topic][3] || !is_array($group['seeder_classes'] ?? null)) { return null; }
            $m = self::mapping($topic);
            if (self::classes($group['seeder_classes']) !== self::classes([$m['standard']['class'], $m['builder']['class']])) { return null; }
            $seen[$topic] = true;
        }
        return self::REVISION;
    }

    /** Normalize only exact declared child scopes for the inherited ROOT context guard. */
    private static function categoryContext(array $filters): ?array
    {
        if (($filters['theory_category_page_test'] ?? false) !== true) { return $filters; }
        $id = (int) \Illuminate\Support\Arr::get($filters, '__meta.theory_category_id');
        $slug = (string) \Illuminate\Support\Arr::get($filters, '__meta.theory_category_slug');
        if (!self::ownsCategory($id, $slug)) { return null; }
        $groups = $filters['theory_category_page_groups'] ?? [];
        if (!is_array($groups) || count($groups) !== count(self::categoryTopics($id))) { return null; }
        $seen = [];
        foreach ($groups as $group) {
            $topic = null;
            foreach (self::categoryTopics($id) as $key) { if (self::PAGES[$key][0] === ($group['page_id'] ?? null)) { $topic = $key; break; } }
            if ($topic === null || isset($seen[$topic]) || ($group['page_slug'] ?? null) !== self::PAGES[$topic][2]
                || !is_array($group['seeder_classes'] ?? null)) { return null; }
            $m = self::mapping($topic);
            if (self::classes($group['seeder_classes']) !== self::classes([$m['standard']['class'], $m['builder']['class']])) { return null; }
            $seen[$topic] = true;
        }
        \Illuminate\Support\Arr::set($filters, '__meta.theory_category_id', self::CATEGORY_ID);
        \Illuminate\Support\Arr::set($filters, '__meta.theory_category_slug', self::CATEGORY_SLUG);
        return $filters;
    }

    public static function stateNamespace(array $filters, string $locale, string $uuid = ''): ?string
    {
        $context = self::categoryContext($filters); return $context === null ? null : parent::stateNamespace($context, $locale, $uuid);
    }

    public static function testContextNeedsRefresh(array $cached, array $filters, string $locale, string $uuid = ''): bool
    {
        $context = self::categoryContext($filters); return $context !== null && parent::testContextNeedsRefresh($cached, $context, $locale, $uuid);
    }

}
