<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned clauses-linking-words banks; historical questions, courses and progress stay intact. */
final class ClausesLinkingPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'clq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'relative-clauses' => 'RelativeClauses',
        'defining-relative-clauses' => 'DefiningRelativeClauses',
        'non-defining-relative-clauses' => 'NonDefiningRelativeClauses',
        'omitting-relative-pronouns' => 'OmittingRelativePronouns',
        'relative-clauses-with-whose-and-which' => 'RelativeClausesWithWhoseAndWhich',
        'reason-because-since-as' => 'ReasonBecauseSinceAs',
        'result-so-therefore-as-a-result' => 'ResultSoThereforeAsAResult',
        'contrast-although-though-however' => 'ContrastAlthoughThoughHowever',
        'purpose-to-in-order-to-so-that' => 'PurposeToInOrderToSoThat',
        'linking-words' => 'LinkingWords',
        'linking-words-reason-result-contrast' => 'LinkingWordsReasonResultContrast',
        'advanced-linking-devices' => 'AdvancedLinkingDevices',
        'concessive-and-contrastive-structures' => 'ConcessiveAndContrastiveStructures',
        'participle-clauses-basics' => 'ParticipleClausesBasics',
        'participle-clauses' => 'ParticipleClauses',
        'advanced-participle-and-absolute-clauses' => 'AdvancedParticipleAndAbsoluteClauses',
        'discourse-markers-and-cohesion' => 'DiscourseMarkersAndCohesion',
    ];
    private const UUID_TOPICS = [
        'relative-clauses' => 'relative',
        'defining-relative-clauses' => 'defining',
        'non-defining-relative-clauses' => 'nondefining',
        'omitting-relative-pronouns' => 'omitting',
        'relative-clauses-with-whose-and-which' => 'whosewhich',
        'reason-because-since-as' => 'reason',
        'result-so-therefore-as-a-result' => 'result',
        'contrast-although-though-however' => 'contrast',
        'purpose-to-in-order-to-so-that' => 'purpose',
        'linking-words' => 'linking',
        'linking-words-reason-result-contrast' => 'reasonresult',
        'advanced-linking-devices' => 'advanced',
        'concessive-and-contrastive-structures' => 'concessive',
        'participle-clauses-basics' => 'partbasics',
        'participle-clauses' => 'participle',
        'advanced-participle-and-absolute-clauses' => 'absolute',
        'discourse-markers-and-cohesion' => 'discourse',
    ];
    protected const UUID_PREFIX = 'clq-';
    protected const SOURCE_PREFIX = 'clauses_linking_words_quality_';
    protected const NAMESPACE_PREFIX = 'clauses-linking-words-quality-';
    protected const LABEL = 'Clauses and Linking Words Quality';
    protected const META_PREFIX = 'clauses_linking_words_quality';
    public const CATEGORY_ID = 34;
    public const CATEGORY_SLUG = 'clauses-and-linking-words';
    public const CATEGORY_TOPICS = ['relative-clauses','defining-relative-clauses','non-defining-relative-clauses','omitting-relative-pronouns','relative-clauses-with-whose-and-which','reason-because-since-as','result-so-therefore-as-a-result','contrast-although-though-however','purpose-to-in-order-to-so-that','linking-words','linking-words-reason-result-contrast','advanced-linking-devices','concessive-and-contrastive-structures','participle-clauses-basics','participle-clauses','advanced-participle-and-absolute-clauses','discourse-markers-and-cohesion'];
    public const SAVED_TEST_COUNT = 34;
    /** Exact local descriptors are bound after the read-only inventory; no unknown profile opts in. */
    public const RETAINED_TEST_PROFILES = [
        'relative-clauses' => ['ed6feb91-5270-5dff-178d-63aad7586da1'=>['course-td-relative-clauses','c73c8f22528c0f7cc5dbc054770f0335340c05086777c040a8899ac9a3dd4538'], 'polyglot-relative-clauses-a2-bd7d40'=>['polyglot-relative-clauses-a2','0bf68a1c8a2a73397e0a44af831c09d90c79b47ab202207381c018dd25ba227e']],
        'defining-relative-clauses' => [],
        'non-defining-relative-clauses' => ['cf1e22ba-d403-32df-cfa3-129fb4543abf'=>['course-td-non-defining-relative-clauses','b6f91f59ba259c491b99d5e51a214fd086f5cc6477fa8492cd9b83aef78b22fb'], 'polyglot-non-defining-relativ-70c7f8'=>['polyglot-non-defining-relative-clauses-b1','1db4e53e6eeafac9e92d15e34e54d88b9f4f9a2cb4a6aab3a5ede4ad0841c761']],
        'omitting-relative-pronouns' => [],
        'relative-clauses-with-whose-and-which' => ['69176fe8-0104-c423-77a4-888a6a0a0fe1'=>['course-td-relative-clauses-with-whose-and-which','1fe1e7a3812136eb453aecf6e582a033d68c19d4429dcc8d537b96e7c0445144'], 'polyglot-relative-clauses-a3d724c605'=>['polyglot-relative-clauses-with-whose-and-which-b2','467af1cd04c2fb0baea67533e0a96f7c56c48d6dd85c84718be9e333da6ae480']],
        'reason-because-since-as' => [],
        'result-so-therefore-as-a-result' => [],
        'contrast-although-though-however' => [],
        'purpose-to-in-order-to-so-that' => [],
        'linking-words' => ['0b5d7a20-cd0a-9b0a-6519-66ccf0da968d'=>['course-td-linking-words','abc9fb2f34e8117d9c608e2b048e512a67402f34818de150ee488df66bf662e6'], 'polyglot-linking-words-and-co-ba9dc4'=>['polyglot-linking-words-and-contrast-b1','b8b670219037e20860e3c6dd50d3ad420c55545429ec2f8556b6af90ad198c0b']],
        'linking-words-reason-result-contrast' => ['7cce5d7d-2ad8-97f3-44bc-00c754eb2dbf'=>['course-td-linking-words-reason-result-contrast','f0d5c1bc431028c4e1e7162c883ac69f546117c416fc6fbf718a3ca2a40f58dd'], 'polyglot-linking-words-re-760435d4c7'=>['polyglot-linking-words-reason-result-contrast-b2','d1930b91996bd81fb39073b58c2d0b093b2850a91805b242e317a93156d61915']],
        'advanced-linking-devices' => ['7a5f399c-fc94-cc47-af41-2068338acaaf'=>['course-td-advanced-linking-devices','cdc7f5897f76cbb286cbe5ef02d1ef192e05a2435341e9d27af2e87d485083e3'], 'polyglot-advanced-linking-dev-4537c9'=>['polyglot-advanced-linking-devices-c1','66beb46b843459846a2ddf941ebe17784b16abe15e8f2553f6d2fe180479015e']],
        'concessive-and-contrastive-structures' => ['2096788d-6cc8-57f4-7759-febef0478e4a'=>['course-td-concessive-and-contrastive-structures','f0526a10e8ff48e818d8b466370693eacff3a9d13589f1b68896c8bf5a9c4b87'], 'polyglot-concessive-and-contr-3bfb33'=>['polyglot-concessive-and-contrastive-structures-c2','6b52ef06cbc88f269599edb8851915a3c70eddf0ccc57e9e9b6077bc2ac8a46b']],
        'participle-clauses-basics' => ['1ec0b6d5-72b2-a715-f684-837ceb7c4bdc'=>['course-td-participle-clauses-basics','21937c8e28ab38375e03c5558a3d989f8895a2277c010e6eb5b4e97157a25585'], 'polyglot-participle-claus-e99cd89bac'=>['polyglot-participle-clauses-basics-b2','3ecc387c65b5192bba1f5d1adc938940fe8462e41ea7077be1cd3bcaddd33ef9']],
        'participle-clauses' => ['e34d1894-396b-503c-9e72-d1d371a730a5'=>['course-td-participle-clauses','716b916c3c893b45ee5f3ffa338643aff18630a326d1d7b1f87841faf5e103ed'], 'polyglot-participle-clauses-c-332200'=>['polyglot-participle-clauses-c1','0762726135acc2abc7ba51c7c7e46d384d42cd20806fb5db35d69331eff0f4a2']],
        'advanced-participle-and-absolute-clauses' => ['647ac7f4-1f65-797e-6f23-f77ec0a1b203'=>['course-td-advanced-participle-and-absolute-clauses','0c6c3294d187b3819e6b21e4be9e16fa73b6dbfc05c4cea4f6f675846848275a'], 'polyglot-advanced-participle-93c5b3'=>['polyglot-advanced-participle-and-absolute-clauses-c2','fd31ae75636562d03f8ae49d454920649a6d1298d2890199b99b05b1e21726b9']],
        'discourse-markers-and-cohesion' => ['e9f08226-f69b-c849-a066-0053218c7058'=>['course-td-discourse-markers-and-cohesion','30e8ba62cfaf81a2e90626385071be1fac501b04b33c2aaeae6febe535b8f608'], 'polyglot-discourse-markers-an-50d22c'=>['polyglot-discourse-markers-and-cohesion-c2','041473c7af15a0ab1788ab53e9dabc75fe18fa137980a2b30d7b83bd5c66730e']],
    ];
    /** Frozen historical level and supplement banks retain their exact accepted bytes. */
    public const RETAINED_DEFINITIONS = [
        'relative-clauses' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotRelativeClausesLessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotRelativeClausesLessonSeeder/definition.json','d9fdac9f7079741ddccf0eedebe998078ae98713a4e28ac0a5bc1aeffe5dc86c']],
        'defining-relative-clauses' => [],
        'non-defining-relative-clauses' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotNonDefiningRelativeClausesLessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotNonDefiningRelativeClausesLessonSeeder/definition.json','001fa5f0e83f1f2b831a35ed686092a07bf67eda62c622b07dd2b3ec448979bf']],
        'omitting-relative-pronouns' => [],
        'relative-clauses-with-whose-and-which' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder/definition.json','688d9927deab437a25a046ce672e7b4a0b59aaaf8c045e511b4845594f08250f']],
        'reason-because-since-as' => ['Database\\Seeders\\V3\\Polyglot\\M64ReasonB1SupplementSeeder'=>['database/seeders/V3/Polyglot/M64ReasonB1SupplementSeeder/definition.json','fd66e4372406a2b53de6f1f8fa4fc806e2977c147ed278f058ec4a76ff4419bf']],
        'result-so-therefore-as-a-result' => ['Database\\Seeders\\V3\\Polyglot\\M64ResultB1SupplementSeeder'=>['database/seeders/V3/Polyglot/M64ResultB1SupplementSeeder/definition.json','dff70b31a611b097776436fd32307085a965e7627f3979953622bb1dbc24e19f']],
        'contrast-although-though-however' => ['Database\\Seeders\\V3\\Polyglot\\M64ContrastB1SupplementSeeder'=>['database/seeders/V3/Polyglot/M64ContrastB1SupplementSeeder/definition.json','01077e801434972774ab65b486423d6c884faf9fe34b1e8cbad14a63bf37a572']],
        'purpose-to-in-order-to-so-that' => ['Database\\Seeders\\V3\\Polyglot\\M65PurposeB1SupplementSeeder'=>['database/seeders/V3/Polyglot/M65PurposeB1SupplementSeeder/definition.json','4f80797947c89d62f9453d86ec2715461c58b42bd6312b795192a3340372cc80']],
        'linking-words' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotLinkingWordsAndContrastLessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotLinkingWordsAndContrastLessonSeeder/definition.json','954dee8a14819c479634c5b396aa66618d6b8b072f150b779c36ddc4808f7000']],
        'linking-words-reason-result-contrast' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotLinkingWordsReasonResultContrastB2LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotLinkingWordsReasonResultContrastB2LessonSeeder/definition.json','c226d365d7f11ecb55ab62bd7a7fe61cf361dcc7947f618d6b50b3e9212052e7']],
        'advanced-linking-devices' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedLinkingDevicesC1LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotAdvancedLinkingDevicesC1LessonSeeder/definition.json','ed114d3622d6531396f3a9138e65a29fdc529970fbef52cdb8add9eef2f38c02']],
        'concessive-and-contrastive-structures' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotConcessiveAndContrastiveStructuresC2LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotConcessiveAndContrastiveStructuresC2LessonSeeder/definition.json','61fd10a71d5efb2965a0c8411df6b21fa3d4312cbf79feb50a614300503def5a']],
        'participle-clauses-basics' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotParticipleClausesBasicsB2LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotParticipleClausesBasicsB2LessonSeeder/definition.json','d044fd2d0800f6e2e4e5a3f5c49e3960b66d95d2eb9bbe3264c7d3b4bb7d0c10']],
        'participle-clauses' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotParticipleClausesC1LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotParticipleClausesC1LessonSeeder/definition.json','c2d56dd53363a6d276c9149cb0b082ca4a5c8748fc10c34334fc593d4a025938']],
        'advanced-participle-and-absolute-clauses' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedParticipleAndAbsoluteClausesC2LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotAdvancedParticipleAndAbsoluteClausesC2LessonSeeder/definition.json','5ad5cb32f188e18b260c4577db577f4bf65c76168ad6b7b01a33cd9f9f134133']],
        'discourse-markers-and-cohesion' => ['Database\\Seeders\\V3\\Polyglot\\PolyglotDiscourseMarkersAndCohesionC2LessonSeeder'=>['database/seeders/V3/Polyglot/PolyglotDiscourseMarkersAndCohesionC2LessonSeeder/definition.json','e77b8c1866387604bab924c774d158b3566ef08fd9149d21314d4d43f05fbf8c']],
    ];
    /** Fresh read-only captured Page/category/seeder identities; no discovery-time inference. */
    public const PAGES = [
        'relative-clauses' => [202,34,'relative-clauses','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsRelativeClausesTheorySeeder'],
        'defining-relative-clauses' => [31,34,'defining-relative-clauses','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsDefiningRelativeClausesTheorySeeder'],
        'non-defining-relative-clauses' => [33,34,'non-defining-relative-clauses','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsNonDefiningRelativeClausesTheorySeeder'],
        'omitting-relative-pronouns' => [34,34,'omitting-relative-pronouns','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsOmittingRelativePronounsTheorySeeder'],
        'relative-clauses-with-whose-and-which' => [284,34,'relative-clauses-with-whose-and-which','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\RelativeClausesWithWhoseAndWhichTheorySeeder'],
        'reason-because-since-as' => [36,34,'reason-because-since-as','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsReasonTheorySeeder'],
        'result-so-therefore-as-a-result' => [37,34,'result-so-therefore-as-a-result','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsResultTheorySeeder'],
        'contrast-although-though-however' => [30,34,'contrast-although-though-however','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsContrastTheorySeeder'],
        'purpose-to-in-order-to-so-that' => [35,34,'purpose-to-in-order-to-so-that','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsPurposeTheorySeeder'],
        'linking-words' => [32,34,'linking-words','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsLinkingWordsTheorySeeder'],
        'linking-words-reason-result-contrast' => [290,34,'linking-words-reason-result-contrast','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\LinkingWordsReasonResultContrastTheorySeeder'],
        'advanced-linking-devices' => [302,34,'advanced-linking-devices','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\AdvancedLinkingDevicesTheorySeeder'],
        'concessive-and-contrastive-structures' => [315,34,'concessive-and-contrastive-structures','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ConcessiveAndContrastiveStructuresTheorySeeder'],
        'participle-clauses-basics' => [285,34,'participle-clauses-basics','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ParticipleClausesBasicsTheorySeeder'],
        'participle-clauses' => [299,34,'participle-clauses','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ParticipleClausesTheorySeeder'],
        'advanced-participle-and-absolute-clauses' => [318,34,'advanced-participle-and-absolute-clauses','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\AdvancedParticipleAndAbsoluteClausesTheorySeeder'],
        'discourse-markers-and-cohesion' => [310,34,'discourse-markers-and-cohesion','Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\DiscourseMarkersAndCohesionTheorySeeder'],
    ];
    private const ORIGINALS = [
        'relative-clauses' => ['standard'=>['clw-rc-v3-all','2fae6116d7b82372709b89befdf41c77132071c7787adf75227396bb309be9be'], 'builder'=>['clw-rc-poly-all','120510cd0efd6193c342e2686e21ee9506606389ba5633c234783cc1db4d17a2']],
        'defining-relative-clauses' => ['standard'=>['rc-def-v3-all','0026e201e38e0b2bbafe21334f0b655df45b66e97fc5fbbf06a259ef68520a98'], 'builder'=>['rc-def-poly-all','69f4a887ccce3a2b72fde1c13f3695a36898fd24d862de162dd3f68f7b732df1']],
        'non-defining-relative-clauses' => ['standard'=>['rc-nd-v3-all','93e1466867dd20d557c7be29157901095b97b83ea53095557bf5cf1c568e3925'], 'builder'=>['rc-nd-poly-all','7f0ea79b1b1812bfa61c01eed950ce96bb079a6a40b989f87cb78b76e21eaa42']],
        'omitting-relative-pronouns' => ['standard'=>['rc-orp-v3-all','26949a932cd8a35608ed7c9bcbc68f4124184fa4e32c71510cd4d435bdf2be49'], 'builder'=>['rc-orp-poly-all','db759e109561b49f4f84b0421c0f101ec3f758d37f91fb7db1d84b90a5a6fb26']],
        'relative-clauses-with-whose-and-which' => ['standard'=>['clw-whose-which-v3-all','3d18adc463d99b762329c2aafac57b0ff50aae18b3a02e4fa4f3c0130057c25c'], 'builder'=>['clw-whose-which-poly-all','12c78b904d78542f42bb173012989f8bd9ba2ec5752a4c64354ada407a13bc00']],
        'reason-because-since-as' => ['standard'=>['clw-rsn-v3-all','2ff4cb69174caa08a39c64ce9d8dfb9e3225dae4e8343051b63907b64b00db05'], 'builder'=>['clw-rsn-poly-all','a3625567df56d9e7df2ff5a0235966ff2d28ded29696cdc318d5310be2e62a0f']],
        'result-so-therefore-as-a-result' => ['standard'=>['clw-res-v3-all','99b28d07b70feaecd0eef00b75c716a15a0d61f052ea561f60393b119688f9d7'], 'builder'=>['clw-res-poly-all','0c53ac253e65fb3b80f77a01ab592d093b85d230bcd593dcad4817201bb4947a']],
        'contrast-although-though-however' => ['standard'=>['clw-ctr-v3-all','3ff0cfbac73f830638fb7d69cf15c0af1bc8cf46777cecfc8eb690317dbc49fb'], 'builder'=>['clw-ctr-poly-all','cfb94dfa959cf630739461ff7d1065989bbe04f13f1ef90a27f70fc021d8cb54']],
        'purpose-to-in-order-to-so-that' => ['standard'=>['clw-pur-v3-all','ca2a8e0a83a9bacc19e6e7ec194df6848af51407d533286f4137b4563489049c'], 'builder'=>['clw-pur-poly-all','2a51c39a14728911c6818302b3d8259fcbdef361dbe286d134fe855b547c70c5']],
        'linking-words' => ['standard'=>['clw-lw-v3-all','f3f136ed968717622b4d05af0010bd5848293249b424d68d1e42bb3631ac6559'], 'builder'=>['clw-lw-poly-all','a173862fc02ceb5b05376be15e1201ac67e79ce31e03b580f81f58cd06e7ecef']],
        'linking-words-reason-result-contrast' => ['standard'=>['clw-lwrrc-v3-all','f68e132a02fd9c56e111ab2333e8f1d2d18f063d6878c7ac6e69b5592e5313c5'], 'builder'=>['clw-lwrrc-poly-all','deb4c20f58d0a45fcb95c231b05b9f8d9f9a96a91cd3082b993a1092322a3835']],
        'advanced-linking-devices' => ['standard'=>['clw-ald-v3-all','91c2eacffafab885797fcf3683f4b0c18c2e223d38efd76deb03df5cb9ed793f'], 'builder'=>['clw-ald-poly-all','ac193fb828533ab1e3ffaed7e32d76df9099eb26eb5c027f041162f907635cad']],
        'concessive-and-contrastive-structures' => ['standard'=>['clw-ccs-v3-all','b8024bc342be5ea0a978cd41185b897616d7524805cbbce191152dccd05600bb'], 'builder'=>['clw-ccs-poly-all','c4ef631a8df0dbe71a0bbb2f0aff217495fc5ae7e0e8d8fcc59d479d613433e3']],
        'participle-clauses-basics' => ['standard'=>['clw-pcb-v3-all','d8bb8402b3f02c410b0420ac35fe48e326e53f465c9da69517bbff1c49bfc230'], 'builder'=>['clw-pcb-poly-all','cbd4d889c03025cae513bedcbedf38d1c25a38537cb47d6ae1bb4c8c756dd4bf']],
        'participle-clauses' => ['standard'=>['clw-pc-v3-all','b93baaacd19ddad41845480f2de26cf728fdfe5ec850c6c1a01f365a1d672b0b'], 'builder'=>['clw-pc-poly-all','75b97f1a49043d5012762141be2bfa00ad54d43a50573e4e88f54590e83cc0f2']],
        'advanced-participle-and-absolute-clauses' => ['standard'=>['clw-apac-v3-all','a0353ac41d0887aad63aab8c2bacd383972ee180af869e3e5cc2628b8e094115'], 'builder'=>['clw-apac-poly-all','df2166d0180e718cd9404cbb649ef2bf9fd8c8890a7d825f183277f0da759d51']],
        'discourse-markers-and-cohesion' => ['standard'=>['clw-dmc-v3-all','e52ff3757e62c0db4c8b2018bee057d4a0df532f4f40c3b2fbbf87a8a8758626'], 'builder'=>['clw-dmc-poly-all','0175f9e83375de5258ed8651855f432f92f05fb912008c6b51369a3181cfd03c']],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Clauses and Linking Words practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic'=>$topic, 'stem'=>$stem, 'author'=>'docs/content/clauses-linking-words-quality/'.$topic.'.v1.json'];
        $map['additional_originals']=[];
        foreach (self::RETAINED_DEFINITIONS[$topic] as $class=>$binding) {
            $map['additional_originals'][]=['class'=>$class,'path'=>$binding[0],'sha256'=>$binding[1]];
        }
        foreach (['standard','builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'ClausesAndLinkingWords' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'ClausesAndLinkingWords/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
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
        if (preg_match('/^(?:A1|A2|B1|B2|C1|C2)-(?:g|c)[0-9]{2}$/iD', $authorId) !== 1) { throw new InvalidArgumentException('Invalid Clauses and Linking Words author identity.'); }
        return self::UUID_PREFIX.self::UUID_TOPICS[$topic].'-'.strtolower($authorId);
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    /** Only finite reviewed aliases belong to this catalog. */
    private static function ownsQuestionUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^clq-(?:relative|defining|nondefining|omitting|whosewhich|reason|result|contrast|purpose|linking|reasonresult|advanced|concessive|partbasics|participle|absolute|discourse)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/D', $uuid) === 1;
    }

    protected static function requiredRowsPerLevel(): array { return ['gap' => 2, 'compose' => [1]]; }

    /** Two exact frozen standard banks omit this field; JsonTestSeeder's existing default is Ukrainian. */
    protected static function definitionLocale(array $definition): mixed { return $definition['defaults']['default_locale'] ?? 'uk'; }

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
            $payload['clq_option_caption_revision']=$payload['psp_content_revision'];
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
            if (($payload['clq_option_caption_revision']??null)!==hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) { return true; }
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

    /** A declarative profile alone must not opt a foreign/partially migrated page into clauses-linking-words behavior. */
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
