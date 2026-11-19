<?php

namespace App\Services;

use App\Support\M92FuturePerfectContinuousBasics as Package;
use RuntimeException;

/** Six UK owners: exact frozen body/descriptor updates and two E/F practice appends; no question-family writes. */
class M92ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BANK_SOURCE_CAPTURE_SHA = '7f648e3368c79650404721c3cd8f6ee2c1035d0e48dc10347130c72750fa2a1e';
    public const BANK_SOURCE_PROFILES = [
        'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectFormsAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\FuturePerfectFormsAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => '929c3517dc8d47a8208a094b624412fbd104c6cabfe804512fbbd876ed581a80',
            'root' => [
                'sha256' => 'c65fded9fe6f03d0969c8d0e7703c894ab3565c52a9f50fbf4db47f66152e765',
                'bytes' => 71709,
                'crlf' => 1,
                'lf' => 2199,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '6076d10a77f4f73c545c234ed79bae96337e7208a2109ecaafd89d352fea57d7',
                'bytes' => 73907,
                'crlf' => 2199,
                'lf' => 2199,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectFormsAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\FuturePerfectFormsAllLevelsV3Seeder',
            'lf_sha256' => 'be247e570568d010b2e1967cb3289cadef7f18337bda1791200f4692ce94b10d',
            'root' => [
                'sha256' => '9ec7ba4d7bff680b2830f8aa6b18fa42e088efc328abeedb75db1e4675138612',
                'bytes' => 113,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '9ec7ba4d7bff680b2830f8aa6b18fa42e088efc328abeedb75db1e4675138612',
                'bytes' => 113,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectBasicsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectBasicsLessonSeeder',
            'question_count' => 24,
            'lf_sha256' => 'aa17d6cc23e381692b914853d9205811563e06df20a692587a78b1a0cacab106',
            'root' => [
                'sha256' => 'c1e2041582a5984d8e2b83c3b12a27017e882d9062407890d07e615973f64838',
                'bytes' => 33554,
                'crlf' => 1112,
                'lf' => 1112,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'c1e2041582a5984d8e2b83c3b12a27017e882d9062407890d07e615973f64838',
                'bytes' => 33554,
                'crlf' => 1112,
                'lf' => 1112,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectBasicsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectBasicsLessonSeeder',
            'lf_sha256' => '9e55b581fc46e0a0abb1e2eb88dfbf669eb61bd693789d2cb70d0deae304489f',
            'root' => [
                'sha256' => 'a7d98fb7f8b021356d9a7e459551e96feab71bc20c6e5fc9a5b8a85bae3be8d2',
                'bytes' => 119,
                'crlf' => 2,
                'lf' => 2,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'a7d98fb7f8b021356d9a7e459551e96feab71bc20c6e5fc9a5b8a85bae3be8d2',
                'bytes' => 119,
                'crlf' => 2,
                'lf' => 2,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectFormsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectFormsAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => 'd665d17f41c463fe9cce398b4bb4c90bb61e10cae1712915e8f096cec4ed7c45',
            'root' => [
                'sha256' => 'bb6be1ebee15b074e5f3ccc54c9d55cbacf6a1b611a4684669a68edbf2002d76',
                'bytes' => 145668,
                'crlf' => 1,
                'lf' => 5105,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '90b77acf37bc42d0da459fba6d6c59fe893b344af4f881321c0be51a5f33c374',
                'bytes' => 150772,
                'crlf' => 5105,
                'lf' => 5105,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectFormsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectFormsAllLevelsLessonSeeder',
            'lf_sha256' => 'a6a8e0865ae85c852983f16737867c1a0f6d93919cfd209b779c7001dcda0fb7',
            'root' => [
                'sha256' => '3dd8860283359f15c93fa74a4b77b26ebf8e9a2a1b581ae47be7c5f9cfa92fdd',
                'bytes' => 137,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '3dd8860283359f15c93fa74a4b77b26ebf8e9a2a1b581ae47be7c5f9cfa92fdd',
                'bytes' => 137,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectNegativesAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\FuturePerfectNegativesAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => '0aa206ac8dfe574e576f31eab9d9973b21d9121fd6d99989e138b89673cd2988',
            'root' => [
                'sha256' => '7bbf0d336115e04cc61b438278614879e1771557f361cf39fec85dcf538c0520',
                'bytes' => 74137,
                'crlf' => 1,
                'lf' => 2191,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'c9b5d513c04425e99364ae79140315c0e68cc4475c0dd3f65143e9a5aba596ba',
                'bytes' => 76327,
                'crlf' => 2191,
                'lf' => 2191,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectNegativesAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\FuturePerfectNegativesAllLevelsV3Seeder',
            'lf_sha256' => 'e9b8cd64efaa83e5e3220a722ff11f054b2b6855d64da1386e351bfbb182ba31',
            'root' => [
                'sha256' => 'f593abc6b568914dd9062db343f9fa26f3ce6c3af8ae2f6cb823a8c2070a7974',
                'bytes' => 121,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'f593abc6b568914dd9062db343f9fa26f3ce6c3af8ae2f6cb823a8c2070a7974',
                'bytes' => 121,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectNegativesAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectNegativesAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => '85f59b8404c8ca635038c1355abe7c997b9d45fbd7ba5e6ce08b6ab2e0b388a3',
            'root' => [
                'sha256' => '4114311dfdba70a94f390092a54039dbbddf91fe9d3b79998aff84c8df06149a',
                'bytes' => 149968,
                'crlf' => 1,
                'lf' => 5193,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '4f4c7ae3181de406a1fd3790a7df84e4178a9214a011a82341a620e0518c0604',
                'bytes' => 155160,
                'crlf' => 5193,
                'lf' => 5193,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectNegativesAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectNegativesAllLevelsLessonSeeder',
            'lf_sha256' => 'b68e5b2ff4141580572ad5aacdae6c1e632d68aa780f6f10c473172909a29556',
            'root' => [
                'sha256' => '81e0c64032286dd9aea0eda1ea5477293bc230374e5e958e332b899e824f2d1c',
                'bytes' => 145,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '81e0c64032286dd9aea0eda1ea5477293bc230374e5e958e332b899e824f2d1c',
                'bytes' => 145,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectQuestionsAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\FuturePerfectQuestionsAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => 'eb79264ffcfdd299b63fcc87b429ff1e400c0323a830f68aea2f95ab4c301420',
            'root' => [
                'sha256' => 'ce0e23449afeb0c1afebac4847d98fbbab17c4ab096ad2bf91dd7f10e3295b37',
                'bytes' => 73182,
                'crlf' => 1,
                'lf' => 2195,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'f444203608905745dad308a4b75c6caedf7602a7d123504d1724195ddc1eac51',
                'bytes' => 75376,
                'crlf' => 2195,
                'lf' => 2195,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectQuestionsAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\FuturePerfectQuestionsAllLevelsV3Seeder',
            'lf_sha256' => 'b009f40de8995608f6219d83ce75377b677c6ba2baa06a7fd2574fc929587543',
            'root' => [
                'sha256' => '005ef1641b35e070d1f19dc2022d27bafff6345b2ca99ee10630450eb026da70',
                'bytes' => 121,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '005ef1641b35e070d1f19dc2022d27bafff6345b2ca99ee10630450eb026da70',
                'bytes' => 121,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => '6fe03a77320793dd409fee744733b54c8a286c630157c2b670f984b1c5e8461c',
            'root' => [
                'sha256' => 'f93837cc10ac42c9a89a573e6b10d399895827198aa159be32ac3a2e7e276924',
                'bytes' => 157179,
                'crlf' => 1,
                'lf' => 5581,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '6c605150b41be5cb5cffa6de99ad5f3a9085425bc37595e4eb6983f84fcc1e7b',
                'bytes' => 162759,
                'crlf' => 5581,
                'lf' => 5581,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder',
            'lf_sha256' => '3420559133ccfddbaa4b5258c10216b534edeae420dedd48a088f7165cb2186f',
            'root' => [
                'sha256' => '2b119db611cf03972eda4740bceb9ba913c2610b11d887fe0ba75d148affeba5',
                'bytes' => 145,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '2b119db611cf03972eda4740bceb9ba913c2610b11d887fe0ba75d148affeba5',
                'bytes' => 145,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectTimeExpressionsAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\FuturePerfectTimeExpressionsAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => 'a06f2905ee9dada6aa182c9d74c1a3e5b1a268979a9b44a924aaefc663db92d0',
            'root' => [
                'sha256' => 'ba96602e07be1429a1d4d15398bf9500adb02112c31947db0f776705bf3b519a',
                'bytes' => 73573,
                'crlf' => 1,
                'lf' => 2199,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '7b3462fb79db202b9ae06e9bfd2f496acedea8f2e423082f7c83595fd9d522eb',
                'bytes' => 75771,
                'crlf' => 2199,
                'lf' => 2199,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectTimeExpressionsAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\FuturePerfectTimeExpressionsAllLevelsV3Seeder',
            'lf_sha256' => 'b8d9bb35cd38344a32b347df9afb5f811cbfc8465249f15dad324abe352c50a8',
            'root' => [
                'sha256' => '617d7c033a072aabcfe921f6e9b2249ef1fb751cb3216f13055bd58cc385f1cd',
                'bytes' => 133,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '617d7c033a072aabcfe921f6e9b2249ef1fb751cb3216f13055bd58cc385f1cd',
                'bytes' => 133,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => '38e86a8ad791911b6179284ab3ff057abdac71d25d3b86cf2cc789237fbf49d2',
            'root' => [
                'sha256' => '6acbf9431ca3c53961777b11ad648381a48effeb7068ae9e8a98abfd563548ab',
                'bytes' => 156113,
                'crlf' => 1,
                'lf' => 5441,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '75d1371d4c12439af35b5e8e17bb11078c79efa1c12e0b064f0d4790d32e97c6',
                'bytes' => 161553,
                'crlf' => 5441,
                'lf' => 5441,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder',
            'lf_sha256' => '9f7d9344425b09ff1fe17fb81ecb0cc995f59c892108c9800c2318e4208eed97',
            'root' => [
                'sha256' => '1154126ebd537ca38616e86c8babeaf0f4cbd780af414f3651d53090136c12b3',
                'bytes' => 157,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '1154126ebd537ca38616e86c8babeaf0f4cbd780af414f3651d53090136c12b3',
                'bytes' => 157,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfectContinuous/FuturePerfectContinuousFormsAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfectContinuous\\FuturePerfectContinuousFormsAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => '2b6e009435c3e065a14696e83227c5d9ef6b187f3a558b19592fe7582d87e848',
            'root' => [
                'sha256' => '72238186f534416e7077e4604531d464889b948432ec61154fc62ba2eb495e85',
                'bytes' => 77565,
                'crlf' => 2191,
                'lf' => 2191,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '72238186f534416e7077e4604531d464889b948432ec61154fc62ba2eb495e85',
                'bytes' => 77565,
                'crlf' => 2191,
                'lf' => 2191,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfectContinuous/FuturePerfectContinuousFormsAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfectContinuous\\FuturePerfectContinuousFormsAllLevelsV3Seeder',
            'lf_sha256' => 'df801384d12f1adf830f27882dee3d2c979244dc42882974ba3688c2e08673b8',
            'root' => [
                'sha256' => 'bf666b3ba8224ab318266a3ccf2da63d4463b7bacb838071808de7bf75c371a4',
                'bytes' => 133,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'bf666b3ba8224ab318266a3ccf2da63d4463b7bacb838071808de7bf75c371a4',
                'bytes' => 133,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousBasicsB2LessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectContinuousBasicsB2LessonSeeder',
            'question_count' => 48,
            'lf_sha256' => 'cefa71da1aacd308e0d92ac2facc9dc26848aa3040ab49e12d9e33f978640cac',
            'root' => [
                'sha256' => '7b1ddd8f05328613cd43227f34c3763fe82016b568574c4c75570bb9e84b7522',
                'bytes' => 122471,
                'crlf' => 3767,
                'lf' => 3767,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '7b1ddd8f05328613cd43227f34c3763fe82016b568574c4c75570bb9e84b7522',
                'bytes' => 122471,
                'crlf' => 3767,
                'lf' => 3767,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousBasicsB2LessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectContinuousBasicsB2LessonSeeder',
            'lf_sha256' => '0c29e873e6ded68287bd5063655d0d1172c1ac86e57894e0676c165110209def',
            'root' => [
                'sha256' => 'efc4ffa7798edaa41d065ca05a369a60dc2cb33648607e359d688a8b37c77743',
                'bytes' => 143,
                'crlf' => 2,
                'lf' => 2,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'efc4ffa7798edaa41d065ca05a369a60dc2cb33648607e359d688a8b37c77743',
                'bytes' => 143,
                'crlf' => 2,
                'lf' => 2,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousFormsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectContinuousFormsAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => '7ca9bd6184ad6873d96fa0ba2163687a39f598215b172a8b9653b19249779543',
            'root' => [
                'sha256' => '3c93b34b0eff2ca19329733bf0feb452013072cde99f19c283c9b434c3bf73c5',
                'bytes' => 180449,
                'crlf' => 5981,
                'lf' => 5981,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '3c93b34b0eff2ca19329733bf0feb452013072cde99f19c283c9b434c3bf73c5',
                'bytes' => 180449,
                'crlf' => 5981,
                'lf' => 5981,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousFormsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectContinuousFormsAllLevelsLessonSeeder',
            'lf_sha256' => 'b94aa72c5691583682b30adff05061e597e16f3b8e6a20c173c7e6969512ea0b',
            'root' => [
                'sha256' => 'af96e6dc3368c1c357aae184745f629c788e7bd32870317a1a05f025a6114bd3',
                'bytes' => 157,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'af96e6dc3368c1c357aae184745f629c788e7bd32870317a1a05f025a6114bd3',
                'bytes' => 157,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfectContinuous/FuturePerfectContinuousNegativesAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfectContinuous\\FuturePerfectContinuousNegativesAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => '2160758004df3be3d2824ec89282fbda1a798869ee1c975c260b7b899f08b7c4',
            'root' => [
                'sha256' => '1fc42f0386a600a6c0d32498e6d324997f68bca883b455245dfd74c86213059b',
                'bytes' => 79260,
                'crlf' => 2187,
                'lf' => 2187,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '1fc42f0386a600a6c0d32498e6d324997f68bca883b455245dfd74c86213059b',
                'bytes' => 79260,
                'crlf' => 2187,
                'lf' => 2187,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FuturePerfectContinuous/FuturePerfectContinuousNegativesAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FuturePerfectContinuous\\FuturePerfectContinuousNegativesAllLevelsV3Seeder',
            'lf_sha256' => '7a410b56db6e944f0d4706c98d39857484a20e8e16cccc584873ee8d1269e78c',
            'root' => [
                'sha256' => 'dc7c6846a03e2d34f39822f28b19ed942b2adc0cf331dcb6933a4ad94ad1fec0',
                'bytes' => 141,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'dc7c6846a03e2d34f39822f28b19ed942b2adc0cf331dcb6933a4ad94ad1fec0',
                'bytes' => 141,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousNegativesAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectContinuousNegativesAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => '8ca32fd15d63447829c55f849b770bb23a86f725936104e93ee8805d1e304d25',
            'root' => [
                'sha256' => 'a5a0e142077aebdc0fbf3d63d9497c5959ad5c2a92c0399c36a14df1b9ec52b8',
                'bytes' => 183414,
                'crlf' => 6037,
                'lf' => 6037,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'a5a0e142077aebdc0fbf3d63d9497c5959ad5c2a92c0399c36a14df1b9ec52b8',
                'bytes' => 183414,
                'crlf' => 6037,
                'lf' => 6037,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousNegativesAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectContinuousNegativesAllLevelsLessonSeeder',
            'lf_sha256' => '40360c7bc89b708f96033c1542601de5a4ae042a7597c61faf74f8b0f515a03a',
            'root' => [
                'sha256' => '466112394da226daca7868adfd37b9a53d1681b053f167e3f3242cdfc92420c9',
                'bytes' => 165,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '466112394da226daca7868adfd37b9a53d1681b053f167e3f3242cdfc92420c9',
                'bytes' => 165,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
    ];
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '86f2f244d5cbb3854ff452fb427e59cd88d64754730d0d228910de80a38bf65c';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'ab4d9ef50bc1d6853d348b3eee146ab527c030a621cf968799d03491852a82aa';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS = [72, 72, 72, 72, 72, 72];
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = 'eb4f79273c18192533f8bf509a8e4cafae752811f3f9722e49a638054b2c9628';
    public const CONTROLLER_WT_AFTER_SHA = '57dd4dc3265c4ae995b77e2697ba8a555a353c8d6ffa3215cee3b437bfaa38e6';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/M92FuturePerfectContinuousBasics.php', 'app/Support/M92BuilderSelectionPresentation.php',
        'app/Services/M92ContentPatch.php', 'app/Services/M92LocalTargetGuard.php', 'app/Console/Commands/ContentPatchM92.php',
        Package::BINDINGS, Package::CHECKSUMS, Package::NAVIGATION,
        'docs/content/m92-sentence-builder-bank-audit.v1.json',
        'docs/content/m92-future-perfect-continuous-basics.components.v1.json',
        'docs/content/m92-future-perfect-continuous-basics.tasks.v1.json',
        'docs/content/m92-future-perfect-continuous-basics.editorial.v1.json',
        'docs/content/m92-future-perfect-continuous-basics.coverage.v1.json',
        'docs/content/m92-widget-selection.v1.json', 'docs/content/m92-future-perfect-continuous-basics.v1.0.0.uk.md',
        'docs/content/m92-author-parts/ab.v1.json', 'docs/content/m92-author-parts/cd.v1.json', 'docs/content/m92-author-parts/ef.v1.json',
        'tests/Fixtures/m92-owned-source-hunks.json',
    ];
    protected const ID = 'm92-future-perfect-continuous-basics-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return ['text']; }
    protected function expectedInsertCount(): int { return 2; }
    protected static function policy(): array
    {
        return ['guard' => M92LocalTargetGuard::class, 'served_root' => M92LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectFormsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFuturePerfectFormsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectNegativesAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFuturePerfectNegativesAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectContinuousFormsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousFormsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectContinuousNegativesAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousNegativesAllLevelsLessonSeeder.php',
            ],
            // Root-only fingerprints: a pre-existing ROOT/WT semantic difference is not a mirror authorisation.
            'extra_source_paths' => [
                'app/Support/TheoryNativeContentRelease.php',
                'app/Support/TheoryBuilderSelectionPresentation.php',
                'app/Services/TheoryNativeContentPatch.php',
                'app/Services/TheoryLocalReleaseGuard.php',
                'app/Support/M51QuestionSupplement.php',
                'app/Support/M76PronounRelations.php',
                'app/Support/M76BuilderSelectionPresentation.php',
                'app/Support/ComposeTokenCase.php',
                'app/Support/AnswerOptionCase.php',
                'app/Support/AcceptedAnswerVariants.php',
                'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectFormsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectFormsAllLevelsV3Seeder.php',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectBasicsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectBasicsLessonSeeder.php',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectFormsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectFormsAllLevelsLessonSeeder.php',
                'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectNegativesAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectNegativesAllLevelsV3Seeder.php',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectNegativesAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectNegativesAllLevelsLessonSeeder.php',
                'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectQuestionsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectQuestionsAllLevelsV3Seeder.php',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder.php',
                'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectTimeExpressionsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FuturePerfect/FuturePerfectTimeExpressionsAllLevelsV3Seeder.php',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder.php',
                'database/seeders/V3/FutureForms/FuturePerfectContinuous/FuturePerfectContinuousFormsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FuturePerfectContinuous/FuturePerfectContinuousFormsAllLevelsV3Seeder.php',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousBasicsB2LessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousBasicsB2LessonSeeder.php',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousFormsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousFormsAllLevelsLessonSeeder.php',
                'database/seeders/V3/FutureForms/FuturePerfectContinuous/FuturePerfectContinuousNegativesAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FuturePerfectContinuous/FuturePerfectContinuousNegativesAllLevelsV3Seeder.php',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousNegativesAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFuturePerfectContinuousNegativesAllLevelsLessonSeeder.php',
            ],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }


    /**
     * Preserve all14 captured bank definitions and14 outer-wrapper EOL profiles; never normalize/copy old banks.
     * The generic sync inventory uses unchanged PHP wrappers; all14 actual
     * canonical definitions remain pinned here and in sourcePaths/protected scans.
     * Raw pins bind the entire source JSON, not only selected teaching fields.
     */
    public static function assertOriginalBankSourceBytes(array $sources): void
    {
        if (array_keys($sources) !== array_keys(self::BANK_SOURCE_PROFILES)) {
            throw new RuntimeException('M92 exact immutable fourteen-bank/28-path source inventory differs.');
        }
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            $pair = $sources[$path];
            if (!is_array($pair) || array_keys($pair) !== ['root', 'worktree']) {
                throw new RuntimeException('M92 exact paired bank source contexts differ.');
            }
            foreach (['root', 'worktree'] as $context) {
                $bytes = $pair[$context]; $expected = $profile[$context];
                if (!is_string($bytes)
                    || !hash_equals($expected['sha256'], hash('sha256', $bytes))
                    || strlen($bytes) !== $expected['bytes']
                    || substr_count($bytes, "\r\n") !== $expected['crlf']
                    || substr_count($bytes, "\n") !== $expected['lf']
                    || str_starts_with($bytes, "\xEF\xBB\xBF") !== $expected['utf8_bom']
                    || !hash_equals($profile['lf_sha256'], hash('sha256', str_replace("\r\n", "\n", $bytes)))) {
                    throw new RuntimeException('M92 original bank source raw/EOL/LF profile changed.');
                }
            }
            if ($profile['kind'] === 'definition') {
                $root = json_decode($pair['root'], true, flags: JSON_THROW_ON_ERROR);
                $worktree = json_decode($pair['worktree'], true, flags: JSON_THROW_ON_ERROR);
                if ($root !== $worktree || ($root['seeder']['class'] ?? null) !== $profile['identity']
                    || count($root['questions'] ?? []) !== $profile['question_count']) {
                    throw new RuntimeException('M92 complete canonical bank JSON identity/content differs.');
                }
            }
        }
    }

    /** Fixed filesystem reads only; no private capture or configurable path authority. */
    protected static function originalBankSourceBytes(): array
    {
        $out = [];
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            foreach (['root' => M92LocalTargetGuard::ROOT, 'worktree' => self::SOURCE_WORKTREE] as $context => $root) {
                $absolute = $root.'/'.$path;
                if (!is_file($absolute) || is_link($absolute)) {
                    throw new RuntimeException('M92 pinned original bank source missing or linked.');
                }
                $bytes = file_get_contents($absolute);
                if (!is_string($bytes)) { throw new RuntimeException('M92 original bank source unreadable.'); }
                $out[$path][$context] = $bytes;
            }
        }
        return $out;
    }

    /** Must precede parent identity/PDO/preview/apply access, including repeated no-op. */
    public function connection(?string $expected = null, bool $writing = false): array
    {
        self::assertOriginalBankSourceBytes(static::originalBankSourceBytes());
        return parent::connection($expected, $writing);
    }

    /** NULL is the captured practice filter, not an invented question-level equality. */
    protected function assertExistingPools(array $questions, array $contract, ?array $baseline): void
    {
        $i = array_search($contract['identity'] ?? null, Package::OWNERS, true);
        if ($i === false || !array_key_exists('level', $contract) || $contract['level'] !== null
            || ($contract['bank_seeders'] ?? null) !== [Package::BANKS[$i]]) {
            throw new RuntimeException('M92 exact original bank/owner/actual practice level differs.');
        }
        $filter = static fn ($q) => $q['seeder'] === Package::BANKS[$i] && (string) $q['type'] === '4';
        $eligible = array_values(array_filter($questions, $filter));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
        if ($baseline !== null) {
            $expected = array_values(array_filter($baseline['relations']['questions'], $filter));
            usort($expected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($eligible !== $expected) { throw new RuntimeException('M92 complete original linked thematic pool changed.'); }
        }
        $ids = array_column($eligible, 'id');
        if (count($ids) !== self::ELIGIBLE_POOL_COUNTS[$i] || count(array_unique($ids)) !== count($ids)) {
            throw new RuntimeException('M92 independently captured original pool count/identity differs.');
        }
    }
}
