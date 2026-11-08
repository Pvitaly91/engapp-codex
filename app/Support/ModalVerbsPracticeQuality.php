<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned modal banks; historical questions, courses and progress stay intact. */
final class ModalVerbsPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'mvq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'can-could' => 'CanCould',
        'may-might' => 'ModalVerbsMayMight',
        'must-have-to' => 'MustHaveTo',
        'should-ought-to' => 'ShouldOughtTo',
        'need-neednt-dont-have-to' => 'NeedNeedntDontHaveTo',
        'modals-of-deduction' => 'ModalsOfDeduction',
        'past-modals' => 'ModalVerbsPastModals',
        'requests-offers-suggestions' => 'ModalVerbsRequestsOffersSuggestions',
        'modal-perfect-and-deduction' => 'ModalPerfectAndDeduction',
        'subtle-modal-meanings' => 'SubtleModalMeanings',
    ];
    private const UUID_TOPICS = [
        'can-could'=>'cc', 'may-might'=>'mm', 'must-have-to'=>'mht', 'should-ought-to'=>'sot',
        'need-neednt-dont-have-to'=>'need', 'modals-of-deduction'=>'ded', 'past-modals'=>'past',
        'requests-offers-suggestions'=>'requests', 'modal-perfect-and-deduction'=>'perfect', 'subtle-modal-meanings'=>'subtle',
    ];
    protected const UUID_PREFIX = 'mvq-';
    protected const SOURCE_PREFIX = 'modal_verbs_quality_';
    protected const NAMESPACE_PREFIX = 'modal-verbs-quality-';
    protected const LABEL = 'Modal Verbs Quality';
    protected const META_PREFIX = 'modal_verbs_quality';
    public const CATEGORY_ID = 15;
    public const CATEGORY_SLUG = 'modal-verbs';
    public const CATEGORY_TOPICS = ['can-could','may-might','must-have-to','should-ought-to','need-neednt-dont-have-to',
        'modals-of-deduction','past-modals','requests-offers-suggestions','modal-perfect-and-deduction','subtle-modal-meanings'];
    public const SAVED_TEST_COUNT = 20;
    /** Exact existing course descriptors are neutral to the page profile, never rewritten or used as banks. */
    public const RETAINED_COURSE_PROFILES = [
        'can-could'=>['41920bd5-a6f1-84e6-2bcf-815ca3e76d69','77f19990b4a4a0b777ca6de9ff16f0e11487400cb04244723cdd52eb08b94ae6'],
        'may-might'=>null,
        'must-have-to'=>['81319632-b1c5-ebe7-df52-a7f0fa15e5c5','72a6734706bf43fbe3efa543f98d7e2b424eb691e497569a243d15622fb08c2f'],
        'should-ought-to'=>['955ad58f-d644-580f-1f2f-2122360548af','fc594c050d273c3de4e9c1b72b62273013c398811e1ae5b42e8ae8ca97403365'],
        'need-neednt-dont-have-to'=>null,
        'modals-of-deduction'=>['15029b3a-7932-7e2e-66f3-d2686c149f73','0125b0f0de68a2f040f2306ddaa4cf2ed18f5e6586e061fbd8b3b4387c7ff493'],
        'past-modals'=>['f9993961-d409-2c4a-9337-6124cd84d000','5541bd54bc42827d4e26855e97ba1595ce45c15f8bf98f1087845b513616b12f'],
        'requests-offers-suggestions'=>null,
        'modal-perfect-and-deduction'=>['c723ba53-5a93-cb79-0ae1-d64d605ff1c5','76953a8a6751e8233f06c1cb0e1f1bfc3e34ac755addfea93959ccb43d6ddc82'],
        'subtle-modal-meanings'=>['7e25be17-187d-04f5-1954-e5031e48ad7b','adc90d777dc49923121322ca5571f3dc732d43726c8afa8aa6178a697a598dd2'],
    ];
    /** Nonselected historical definitions are neutral only at these exact source bytes. */
    public const RETAINED_DEFINITIONS = [
        'can-could'=>['Database\\Seeders\\V3\\Polyglot\\PolyglotCanCannotLessonSeeder','database/seeders/V3/Polyglot/PolyglotCanCannotLessonSeeder/definition.json','98916fa95b34125d7062acd6de08a22be0dd04508245d12d9f2c259599bc04ce'],
        'may-might'=>null,
        'must-have-to'=>['Database\\Seeders\\V3\\Polyglot\\PolyglotMustHaveToLessonSeeder','database/seeders/V3/Polyglot/PolyglotMustHaveToLessonSeeder/definition.json','96661d2224efebac394fb76f646e7398a345dd67fc3cd9fad4b6a0a96b47b848'],
        'should-ought-to'=>['Database\\Seeders\\V3\\Polyglot\\PolyglotShouldOughtToLessonSeeder','database/seeders/V3/Polyglot/PolyglotShouldOughtToLessonSeeder/definition.json','f873baf217355adf417127b087eccfaff193aab83c9dd3a82850031b2f25f275'],
        'need-neednt-dont-have-to'=>null,
        'modals-of-deduction'=>['Database\\Seeders\\V3\\Polyglot\\PolyglotModalVerbsOfDeductionLessonSeeder','database/seeders/V3/Polyglot/PolyglotModalVerbsOfDeductionLessonSeeder/definition.json','9390c1774fc776f80e68fbd603903fbf08cee0d7f363e8887728da3323f96106'],
        'past-modals'=>['Database\\Seeders\\V3\\Polyglot\\PolyglotModalPerfectDeductionAndRegretB2LessonSeeder','database/seeders/V3/Polyglot/PolyglotModalPerfectDeductionAndRegretB2LessonSeeder/definition.json','364c5da8b01c00f242a28569ead50949ea59663d8b1829c67e2b16f2ab53c8a0'],
        'requests-offers-suggestions'=>null,
        'modal-perfect-and-deduction'=>['Database\\Seeders\\V3\\Polyglot\\PolyglotModalPerfectAndDeductionC1LessonSeeder','database/seeders/V3/Polyglot/PolyglotModalPerfectAndDeductionC1LessonSeeder/definition.json','0901af31d5ad4b701e29473e0cc78858b7f78702e5011cac1001839a5881945b'],
        'subtle-modal-meanings'=>['Database\\Seeders\\V3\\Polyglot\\PolyglotSubtleModalMeaningsC2LessonSeeder','database/seeders/V3/Polyglot/PolyglotSubtleModalMeaningsC2LessonSeeder/definition.json','b9ecba26604181383525667724c79542a5dc285034dc7fd205b4fb2b4fc3b071'],
    ];

    /** Captured local page bindings; names alone never opt a course or another locale in. */
    public const PAGES = [
        'can-could'=>[72,15,'can-could','Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder'],
        'may-might'=>[74,15,'may-might','Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMayMightTheorySeeder'],
        'must-have-to'=>[75,15,'must-have-to','Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMustHaveToTheorySeeder'],
        'should-ought-to'=>[79,15,'should-ought-to','Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsShouldOughtToTheorySeeder'],
        'need-neednt-dont-have-to'=>[76,15,'need-neednt-dont-have-to','Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsNeedNeedntDontHaveToTheorySeeder'],
        'modals-of-deduction'=>[73,15,'modals-of-deduction','Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsDeductionTheorySeeder'],
        'past-modals'=>[77,15,'past-modals','Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsPastModalsTheorySeeder'],
        'requests-offers-suggestions'=>[78,15,'requests-offers-suggestions','Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsRequestsOffersSuggestionsTheorySeeder'],
        'modal-perfect-and-deduction'=>[303,15,'modal-perfect-and-deduction','Database\\Seeders\\Page_V3\\ModalVerbs\\ModalPerfectAndDeductionTheorySeeder'],
        'subtle-modal-meanings'=>[320,15,'subtle-modal-meanings','Database\\Seeders\\Page_V3\\ModalVerbs\\SubtleModalMeaningsTheorySeeder'],
    ];

    private const ORIGINALS = [
        'can-could' => ['standard'=>['modal-cc-v3-all','ccc5f03ec489a005353cec45da3f3f95f5e18965b3b5377a3835d95cb24b2ff9'], 'builder'=>['modal-cc-poly-all','9815fa835b2ca6449916f76cf6b5ebee7468594a67294a4a2651368defc19ad1']],
        'may-might' => ['standard'=>['mv-mm-v3-all','8d08883f179dc0f3f22153b95aea4a2d55d48819310320bb08af4c2f4b0f25ce'], 'builder'=>['mv-mm-poly-all','3cc89dc4c3d05dd0e959c2a2b0c8c7bbdea78627da963f5ffdad8897590849b3']],
        'must-have-to' => ['standard'=>['modal-mht-v3-all','cfba3183335fb64143867bf6817a00f81aaba95d163ae4dc3a83dc2d5b98ba9c'], 'builder'=>['modal-mht-poly-all','0e4736c32e8912b79149ea649938e67beda99234cfe125f351dd913e864b45aa']],
        'should-ought-to' => ['standard'=>['modal-sot-v3-all','ba0232d677f7960062b8c288b08f94063dafc2f07d85bf5036ef3236fa9dbc2e'], 'builder'=>['modal-sot-poly-all','05e75491d16f8deb71199cfe923aa506643c5b0bfd4b16c5cdfdb1cee7d1b1e7']],
        'need-neednt-dont-have-to' => ['standard'=>['modal-nndht-v3-all','9a3eb5f1e2f01b2d5b61c5f9c98fb347f262053a0a03a0800e538a5405428281'], 'builder'=>['modal-nndht-poly-all','c6e53c76709b1e40db374144b0c782e6066398bcf25fd185914136c444cd4e07']],
        'modals-of-deduction' => ['standard'=>['mv-ded-v3-all','98aef047b13240d510d0f862ef3b432a74f89683398ee8f6850ea2e5c8e13812'], 'builder'=>['mv-ded-poly-all','e8716329234adc0940858305ffac4c1623761cfcd740c7e6730702a2cf9af820']],
        'past-modals' => ['standard'=>['mv-pm-v3-all','cbaf433a73e7db38021445298e2f88047858f592d19acbefc05a0d65a21883b5'], 'builder'=>['mv-pm-poly-all','41b7b574ec469db8652e33245b17c8fdc65afbe80c497497376b20cefa5ede95']],
        'requests-offers-suggestions' => ['standard'=>['mv-ros-v3-all','2497f1c7a92ad0aca376313d0bdcc5da61da220d8da2d206973b289e798e953f'], 'builder'=>['mv-ros-poly-all','ffbff02aeb628a8a89f99bafeee946a08b737263e8cf55da8e3122ddd6079d3e']],
        'modal-perfect-and-deduction' => ['standard'=>['modal-mpd-v3-all','24c3d57f72b1701a2d800942e776bc18fe050a24d17608ba33c017efbb07fef5'], 'builder'=>['modal-mpd-poly-all','eefd74cb264d317216c088a9aca698f3a4cb3da5370b830a2e7ea07ecd2ed3e4']],
        'subtle-modal-meanings' => ['standard'=>['modal-smm-v3-all','f7dfaaa267289645846d24eb92d2018d7c285f65b53fa5a498acd6db4695c950'], 'builder'=>['modal-smm-poly-all','6d5c1e4e0d8c0214ee7a53e6f7b7b91453dad5526fac85b46f750ba63969dd27']],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Modal Verbs practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/modal-verbs-quality/'.$topic.'.v1.json'];
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'ModalVerbs' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'ModalVerbs/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
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
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Modal Verbs author identity.'); }
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
        $payload=parent::decorate($payload); $source=self::questions()[$payload['uuid']??'']??null;
        if ($source && self::sourceBound($payload,$source)) {
            if ((string)$source['type']==='0') { $payload['passive_phrase_word_inputs']=true; }
            $payload['mvq_option_caption_revision']=$payload['psp_content_revision'];
            $payload=self::restoreClarifiedHints($payload,'uk');
        }
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (parent::cachedNeedsRefresh($payloads)) { return true; }
        $sources=self::questions();
        foreach ($payloads as $payload) {
            $source=$sources[$payload['uuid']??'']??null; if (!$source) { continue; }
            if (($payload['mvq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
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

    /** A declarative profile alone must not opt a foreign/partially migrated page into modal behavior. */
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

    /** Profile-only view: one captured course may coexist with the exact two current page banks. */
    private static function profilePair(array $linkedTests, string $locale): ?array
    {
        if ($locale!=='uk' || !in_array(count($linkedTests),[2,3],true)) { return null; }
        $linked=[];
        foreach ($linkedTests as $test) {
            if (!$test instanceof \App\Models\SavedGrammarTest || isset($linked[(string)$test->uuid])) { return null; }
            $linked[(string)$test->uuid]=$test;
        }
        foreach (array_keys(self::TOPICS) as $topic) {
            $map=self::mapping($topic); $standard=$map['standard']['old_saved_test_uuid']; $builder=$map['builder']['old_saved_test_uuid'];
            if (!isset($linked[$standard],$linked[$builder])) { continue; }
            $extra=array_diff_key($linked,[$standard=>true,$builder=>true]);
            if ($extra!==[]) {
                $binding=self::RETAINED_COURSE_PROFILES[$topic];
                if ($binding===null || count($extra)!==1 || !isset($extra[$binding[0]])) { return null; }
                $course=$extra[$binding[0]]; $filters=$course->filters;
                if ($course->slug!=='course-td-'.$topic || !is_array($filters)
                    || !hash_equals($binding[1],hash('sha256',M26DetailPackage::json($filters)))
                    || ($filters['course_slug']??null)!=='theory-driven' || ($filters['seeder_classes']??[])!==[]
                    || ($filters['theory_page_slug']??null)!==self::PAGES[$topic][2]
                    || ($filters['theory_category_slug']??null)!==self::CATEGORY_SLUG) { return null; }
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

    /** Category callers see old course definitions too; none may replace the selected exact page pair. */
    private static function definitionPair(array $definitions, array $pair, string $locale): ?array
    {
        if ($definitions===[]) { return []; }
        if ($locale!=='uk' || !in_array(count($definitions),[2,3],true)) { return null; }
        foreach (array_keys(self::TOPICS) as $topic) {
            $map=self::mapping($topic);
            if ($pair[0]->uuid!==$map['standard']['old_saved_test_uuid'] || $pair[1]->uuid!==$map['builder']['old_saved_test_uuid']) { continue; }
            $selected=[];
            foreach ($definitions as $class=>$definition) {
                if (in_array($class,[$map['standard']['old_class'],$map['builder']['old_class']],true)) {
                    $selected[$class]=$definition; continue;
                }
                $binding=self::RETAINED_DEFINITIONS[$topic];
                if ($binding===null || $class!==$binding[0] || !is_array($definition) || ($definition['seeder']['class']??null)!==$class) { return null; }
                $file=dirname(__DIR__,2).'/'.$binding[1];
                if (!is_file($file) || is_link($file) || !hash_equals($binding[2],hash_file('sha256',$file))
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
