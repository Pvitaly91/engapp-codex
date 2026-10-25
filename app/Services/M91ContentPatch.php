<?php

namespace App\Services;

use App\Support\M91FutureSimpleCompletionContinuous as Package;
use RuntimeException;

/** Six UK owners: exact body/EF descriptor updates and four practice appends; no question-family writes. */
class M91ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BANK_SOURCE_CAPTURE_SHA = '19abedf1075c58db5a08f29864c74c0fb259b0d567b64f327a71d5b1a7cf4c51';
    public const BANK_SOURCE_PROFILES = [
        'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousFormsAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureContinuous\\FutureContinuousFormsAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => 'a630cb7c868972fbf149512186242463f45b4ad7225b6ab3d02b34c9b2ac9f47',
            'root' => [
                'sha256' => '140e3babe0caf9dffd3597603add05131b14e2de24d2e95d31be96835a97e20c',
                'bytes' => 74824,
                'crlf' => 2187,
                'lf' => 2187,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '140e3babe0caf9dffd3597603add05131b14e2de24d2e95d31be96835a97e20c',
                'bytes' => 74824,
                'crlf' => 2187,
                'lf' => 2187,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousFormsAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureContinuous\\FutureContinuousFormsAllLevelsV3Seeder',
            'lf_sha256' => '7a67fdc36f7e89e7aef722538aadb57eab7f25aee18b192638ce2a8c77fc1e79',
            'root' => [
                'sha256' => '91b19697b92a8bbbbcabd014db9a46f0284b461914dc6d5f17324c6a9f7ee636',
                'bytes' => 119,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '91b19697b92a8bbbbcabd014db9a46f0284b461914dc6d5f17324c6a9f7ee636',
                'bytes' => 119,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousNegativesAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureContinuous\\FutureContinuousNegativesAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => 'ff5b700c382883221227381aefa548abc5a90d87594a064c5f95323ab790308a',
            'root' => [
                'sha256' => 'defedd2c6c94d25b11008242d156a06e18cfdefe8753120e26c7d7cff30f9b9d',
                'bytes' => 75683,
                'crlf' => 2183,
                'lf' => 2183,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'defedd2c6c94d25b11008242d156a06e18cfdefe8753120e26c7d7cff30f9b9d',
                'bytes' => 75683,
                'crlf' => 2183,
                'lf' => 2183,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousNegativesAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureContinuous\\FutureContinuousNegativesAllLevelsV3Seeder',
            'lf_sha256' => 'e36cc98e905b803dbb284d4c42fdbbdff13e383a474076ed16c67499bb03ed71',
            'root' => [
                'sha256' => '34f0b9ab4e42fc8955f7b38c5fde95255d4dc977c459ff3045de110772dcf88b',
                'bytes' => 127,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '34f0b9ab4e42fc8955f7b38c5fde95255d4dc977c459ff3045de110772dcf88b',
                'bytes' => 127,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousQuestionsAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureContinuous\\FutureContinuousQuestionsAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => 'e3a4110080c660969817624aaf2339a8ebe3e3b08ab131a188cb32de6fff5017',
            'root' => [
                'sha256' => '627dd0c3f0e6dc12d211070e0a0ed32e67d16c369e2046c5391b896f0c1cf659',
                'bytes' => 73145,
                'crlf' => 2183,
                'lf' => 2183,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '627dd0c3f0e6dc12d211070e0a0ed32e67d16c369e2046c5391b896f0c1cf659',
                'bytes' => 73145,
                'crlf' => 2183,
                'lf' => 2183,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousQuestionsAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureContinuous\\FutureContinuousQuestionsAllLevelsV3Seeder',
            'lf_sha256' => '0c79dafdd086af52279ef3b3daeeaa7503454f4c3afcad9342b218174ccfeff6',
            'root' => [
                'sha256' => '53d3fdad8e8226173ae0bbb7cd91e4f4e2c78ea65991419ca73b739466c8d18e',
                'bytes' => 127,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '53d3fdad8e8226173ae0bbb7cd91e4f4e2c78ea65991419ca73b739466c8d18e',
                'bytes' => 127,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousTimeExpressionsAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureContinuous\\FutureContinuousTimeExpressionsAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => '536a62289026c92d1bd444fd879865da047d026d9d7706a7c2d5ff87b3825c9e',
            'root' => [
                'sha256' => '96b23d1a7bfe7efbaa9375c56837c589255b0de198edf733caf14e3ca4a28321',
                'bytes' => 75510,
                'crlf' => 2187,
                'lf' => 2187,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '96b23d1a7bfe7efbaa9375c56837c589255b0de198edf733caf14e3ca4a28321',
                'bytes' => 75510,
                'crlf' => 2187,
                'lf' => 2187,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousTimeExpressionsAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureContinuous\\FutureContinuousTimeExpressionsAllLevelsV3Seeder',
            'lf_sha256' => '57a50ba36270fad75dcf947fc9f4aea385d0b16679cc8d0ee43ad72c55fc8a18',
            'root' => [
                'sha256' => 'e640808cb50f25182174152fbaee9ca56eb6813f8e05da2241b2a58f205f5bef',
                'bytes' => 139,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'e640808cb50f25182174152fbaee9ca56eb6813f8e05da2241b2a58f205f5bef',
                'bytes' => 139,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureSimple/FutureSimpleQuestionsAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureSimple\\FutureSimpleQuestionsAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => 'd88c324b697e01cf83715525fbf00be0c2d71001784fe3cdd9174008f3107bb5',
            'root' => [
                'sha256' => 'e82d1582e5c8d2d84719cc4ff385e47dcdd03f9ebdd88dfc41e36f6f63990b0b',
                'bytes' => 81886,
                'crlf' => 2197,
                'lf' => 2197,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'e82d1582e5c8d2d84719cc4ff385e47dcdd03f9ebdd88dfc41e36f6f63990b0b',
                'bytes' => 81886,
                'crlf' => 2197,
                'lf' => 2197,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureSimple/FutureSimpleQuestionsAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureSimple\\FutureSimpleQuestionsAllLevelsV3Seeder',
            'lf_sha256' => 'abaabec92ecf57907c07537c4376f0af5dff40a9fa8dd77374b309f2734a5f07',
            'root' => [
                'sha256' => 'bb4d51749cf5fd9349e5b552e0f0d6586994c11ed0a03d950958d872b08b1a39',
                'bytes' => 121,
                'crlf' => 4,
                'lf' => 4,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'bb4d51749cf5fd9349e5b552e0f0d6586994c11ed0a03d950958d872b08b1a39',
                'bytes' => 121,
                'crlf' => 4,
                'lf' => 4,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureSimple/FutureSimpleTimeExpressionsAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureSimple\\FutureSimpleTimeExpressionsAllLevelsV3Seeder',
            'question_count' => 72,
            'lf_sha256' => 'e0f3dd755d0a611f94eae5549b87b39bbacc7ee5be6e898d5996a9097d91621b',
            'root' => [
                'sha256' => '5be956fe959e6b66d5ce5e30f5d78476d8df9646017c6e80278729ad6c59dd0a',
                'bytes' => 84724,
                'crlf' => 2269,
                'lf' => 2269,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '5be956fe959e6b66d5ce5e30f5d78476d8df9646017c6e80278729ad6c59dd0a',
                'bytes' => 84724,
                'crlf' => 2269,
                'lf' => 2269,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/FutureForms/FutureSimple/FutureSimpleTimeExpressionsAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\FutureForms\\FutureSimple\\FutureSimpleTimeExpressionsAllLevelsV3Seeder',
            'lf_sha256' => '95d816dfcd3cbd30552b1322dda6286260ed1b6ae081248ddaecaf39daafafd5',
            'root' => [
                'sha256' => 'e4ab172273d806aa7cb717bc0254de75610e6d01b6240ec98bb4b73d21ea2ce0',
                'bytes' => 133,
                'crlf' => 4,
                'lf' => 4,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'e4ab172273d806aa7cb717bc0254de75610e6d01b6240ec98bb4b73d21ea2ce0',
                'bytes' => 133,
                'crlf' => 4,
                'lf' => 4,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureContinuousBasicsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousBasicsLessonSeeder',
            'question_count' => 24,
            'lf_sha256' => '4c4c2c075b02067d6074688214aa7675a7bfdbf27c5fa79f02a0f0813ba798b1',
            'root' => [
                'sha256' => '48f203958b9a5436b8f307f6dd8296f4e3bb8ee7cdc2386019a745fd6f88203c',
                'bytes' => 33004,
                'crlf' => 1072,
                'lf' => 1072,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '48f203958b9a5436b8f307f6dd8296f4e3bb8ee7cdc2386019a745fd6f88203c',
                'bytes' => 33004,
                'crlf' => 1072,
                'lf' => 1072,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureContinuousBasicsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousBasicsLessonSeeder',
            'lf_sha256' => 'a19afb2e706980b767e919807730d61bbd76e47f3feed051275c6d664730be7e',
            'root' => [
                'sha256' => '494f0f6774847d531dfb7e5da658b69688e297dedcc76c014789dccbf8b4d20e',
                'bytes' => 125,
                'crlf' => 2,
                'lf' => 2,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '494f0f6774847d531dfb7e5da658b69688e297dedcc76c014789dccbf8b4d20e',
                'bytes' => 125,
                'crlf' => 2,
                'lf' => 2,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureContinuousFormsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousFormsAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => 'fbbe75fa159209e04667543e8be0b5c75cb9079738b6c7dae069a45e420119ee',
            'root' => [
                'sha256' => '5865add7136a67a7adedf50fe6f27379648ca9160eb5e227c7225a36bfd0cdde',
                'bytes' => 156696,
                'crlf' => 5167,
                'lf' => 5167,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '5865add7136a67a7adedf50fe6f27379648ca9160eb5e227c7225a36bfd0cdde',
                'bytes' => 156696,
                'crlf' => 5167,
                'lf' => 5167,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureContinuousFormsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousFormsAllLevelsLessonSeeder',
            'lf_sha256' => '4f8a891c7162c797bb7a14901f9829cf3827b7ecbf710f4a4311e22ff4dea14a',
            'root' => [
                'sha256' => '8d50767c526e60d90a8ddd6c91b9af82dff020ef9017e861bcb76e8b1f8cf7bc',
                'bytes' => 143,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '8d50767c526e60d90a8ddd6c91b9af82dff020ef9017e861bcb76e8b1f8cf7bc',
                'bytes' => 143,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureContinuousNegativesAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousNegativesAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => '55d272be5ff14b1c6b0cc8182ff7223ca6c8f1d83ceb21bd05991f94ede52eff',
            'root' => [
                'sha256' => 'c78940edf9aa19dd18acbbbf8658e3710bea38d2f2895fbaa4134436acbcbceb',
                'bytes' => 157277,
                'crlf' => 5211,
                'lf' => 5211,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'c78940edf9aa19dd18acbbbf8658e3710bea38d2f2895fbaa4134436acbcbceb',
                'bytes' => 157277,
                'crlf' => 5211,
                'lf' => 5211,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureContinuousNegativesAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousNegativesAllLevelsLessonSeeder',
            'lf_sha256' => '613337b237b1c5659c3c2def1f2ae54b90e0791c452a7b6a35aa703155e01e1b',
            'root' => [
                'sha256' => 'f3cea97588f469f752b410c42a525f079ac55e9c05c165419ec6f66dfd5a3472',
                'bytes' => 151,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'f3cea97588f469f752b410c42a525f079ac55e9c05c165419ec6f66dfd5a3472',
                'bytes' => 151,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => '8c7ce5cc59a7f38f2a0a94c28d88de5ad713c0667b0f06dc8f4950ac0340bb11',
            'root' => [
                'sha256' => 'fec3d2bd93c03efe09de99e6faec80c5dfdb3c77fd2f2f641954b0b3b5d6c2fe',
                'bytes' => 144299,
                'crlf' => 4857,
                'lf' => 4857,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'fec3d2bd93c03efe09de99e6faec80c5dfdb3c77fd2f2f641954b0b3b5d6c2fe',
                'bytes' => 144299,
                'crlf' => 4857,
                'lf' => 4857,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder',
            'lf_sha256' => 'f4fb63291557c528a43f3e3d4738b06ab2e3d6d00f25612ab8c2d66e07241e38',
            'root' => [
                'sha256' => 'be7e4e400a5bc88884a8bed390cdc5b06c5d46553f88d48956a212ed30230c35',
                'bytes' => 151,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'be7e4e400a5bc88884a8bed390cdc5b06c5d46553f88d48956a212ed30230c35',
                'bytes' => 151,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => '53168eaa2c1c1ab95293a21170f888680184bcfbf0017492b4bbeac445964bbf',
            'root' => [
                'sha256' => 'f96482fa6a1731f39da7b0d5a74fe172de6bc0456e5713f426223e74d1720b0b',
                'bytes' => 157831,
                'crlf' => 5173,
                'lf' => 5173,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'f96482fa6a1731f39da7b0d5a74fe172de6bc0456e5713f426223e74d1720b0b',
                'bytes' => 157831,
                'crlf' => 5173,
                'lf' => 5173,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder',
            'lf_sha256' => '2d112a01607708bf8af3b1d0e015807479d75254298790abc2299bef22d6ee9c',
            'root' => [
                'sha256' => '11e33d4457ebae1dc5b3bbd7e1432610523f7db37b923c21f331b662d133ffb2',
                'bytes' => 163,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '11e33d4457ebae1dc5b3bbd7e1432610523f7db37b923c21f331b662d133ffb2',
                'bytes' => 163,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => 'e64b21bb2f15ef3a2cbeb9ff52d31a2ae534fbef63eae941a72bbe45eb886ad8',
            'root' => [
                'sha256' => '6adaf326538539a13b6adca4d040a687409b40a84d5a45e82f320858e84e96db',
                'bytes' => 77366,
                'crlf' => 2546,
                'lf' => 2546,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '6adaf326538539a13b6adca4d040a687409b40a84d5a45e82f320858e84e96db',
                'bytes' => 77366,
                'crlf' => 2546,
                'lf' => 2546,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder',
            'lf_sha256' => '72e5785b272e2d1a44b6d4dda17aed72286ad927725fe96ed4c6eb76280be3f2',
            'root' => [
                'sha256' => '9058ffb86bd9e8c702ecb0fb5e9bc8731bc47df1d265361245c26558a67365a3',
                'bytes' => 145,
                'crlf' => 4,
                'lf' => 4,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '9058ffb86bd9e8c702ecb0fb5e9bc8731bc47df1d265361245c26558a67365a3',
                'bytes' => 145,
                'crlf' => 4,
                'lf' => 4,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder',
            'question_count' => 72,
            'lf_sha256' => '09ecfac5add4b9994f6361d6f5bb4cadba4fdb673b8347720800a29b1caaa91e',
            'root' => [
                'sha256' => '3a214da25955ffdcbab1bcdc1e9460cecd6c5d5ae3e8faf281f810ea936fdd91',
                'bytes' => 90505,
                'crlf' => 2950,
                'lf' => 2950,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '3a214da25955ffdcbab1bcdc1e9460cecd6c5d5ae3e8faf281f810ea936fdd91',
                'bytes' => 90505,
                'crlf' => 2950,
                'lf' => 2950,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder',
            'lf_sha256' => '41572bc5e72eeca4bbe92499f463a08ebf4917e08768319493892887c5f05d6a',
            'root' => [
                'sha256' => '1fa7457d066456cbebe681c71e4d34b979dab5f64cf6d6129cead0904e7f10c0',
                'bytes' => 157,
                'crlf' => 4,
                'lf' => 4,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '1fa7457d066456cbebe681c71e4d34b979dab5f64cf6d6129cead0904e7f10c0',
                'bytes' => 157,
                'crlf' => 4,
                'lf' => 4,
                'utf8_bom' => false,
            ],
        ],
    ];
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '182192d1e92382649dc4179df5ca88f43a858c2f4cd8a0f527d87dc3222ee18f';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '8b80c3ce32e9dd8f10450b6a969c0bd4ec5642116dece60e22f642b3acaa2fd3';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS = [72, 72, 72, 72, 72, 72];
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '7399b9354699598563ec93e8ca2a476ad5f6216e66ece3b5e804058c0ea67757';
    public const CONTROLLER_WT_AFTER_SHA = 'e1ef7ef880d6d82b3bb97452c9a01fd55bdb84a05cf6352dd4ae6f457a450587';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/M91FutureSimpleCompletionContinuous.php', 'app/Support/M91BuilderSelectionPresentation.php',
        'app/Services/M91ContentPatch.php', 'app/Services/M91LocalTargetGuard.php', 'app/Console/Commands/ContentPatchM91.php',
        Package::BINDINGS, Package::CHECKSUMS, Package::NAVIGATION,
        'docs/content/m91-sentence-builder-bank-audit.v1.json',
        'docs/content/m91-future-simple-completion-continuous.components.v1.json',
        'docs/content/m91-future-simple-completion-continuous.tasks.v1.json',
        'docs/content/m91-future-simple-completion-continuous.editorial.v1.json',
        'docs/content/m91-future-simple-completion-continuous.coverage.v1.json',
        'docs/content/m91-widget-selection.v1.json', 'docs/content/m91-future-simple-completion-continuous.v1.0.0.uk.md',
        'docs/content/m91-author-parts/ab.v1.json', 'docs/content/m91-author-parts/cd.v1.json', 'docs/content/m91-author-parts/ef.v1.json',
        'tests/Fixtures/m91-owned-source-hunks.json',
    ];
    protected const ID = 'm91-future-simple-completion-continuous-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return ['text']; }
    protected function expectedInsertCount(): int { return 4; }
    protected static function policy(): array
    {
        return ['guard' => M91LocalTargetGuard::class, 'served_root' => M91LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousFormsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFutureContinuousFormsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousNegativesAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFutureContinuousNegativesAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder.php',
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
                'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousFormsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousFormsAllLevelsV3Seeder.php',
                'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousNegativesAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousNegativesAllLevelsV3Seeder.php',
                'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousQuestionsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousQuestionsAllLevelsV3Seeder.php',
                'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousTimeExpressionsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FutureContinuous/FutureContinuousTimeExpressionsAllLevelsV3Seeder.php',
                'database/seeders/V3/FutureForms/FutureSimple/FutureSimpleQuestionsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FutureSimple/FutureSimpleQuestionsAllLevelsV3Seeder.php',
                'database/seeders/V3/FutureForms/FutureSimple/FutureSimpleTimeExpressionsAllLevelsV3Seeder/definition.json',
                'database/seeders/V3/FutureForms/FutureSimple/FutureSimpleTimeExpressionsAllLevelsV3Seeder.php',
                'database/seeders/V3/Polyglot/PolyglotFutureContinuousBasicsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFutureContinuousBasicsLessonSeeder.php',
                'database/seeders/V3/Polyglot/PolyglotFutureContinuousFormsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFutureContinuousFormsAllLevelsLessonSeeder.php',
                'database/seeders/V3/Polyglot/PolyglotFutureContinuousNegativesAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFutureContinuousNegativesAllLevelsLessonSeeder.php',
                'database/seeders/V3/Polyglot/PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder.php',
                'database/seeders/V3/Polyglot/PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder.php',
                'database/seeders/V3/Polyglot/PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder.php',
                'database/seeders/V3/Polyglot/PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder.php',
            ],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }


    /**
     * Preserve all13 captured bank definitions and13 outer-wrapper EOL profiles; never normalize/copy old banks.
     * The generic sync inventory uses unchanged PHP wrappers; all13 actual
     * canonical definitions remain pinned here and in sourcePaths/protected scans.
     * Raw pins bind the entire source JSON, not only selected teaching fields.
     */
    public static function assertOriginalBankSourceBytes(array $sources): void
    {
        if (array_keys($sources) !== array_keys(self::BANK_SOURCE_PROFILES)) {
            throw new RuntimeException('M91 exact immutable thirteen-bank/26-path source inventory differs.');
        }
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            $pair = $sources[$path];
            if (!is_array($pair) || array_keys($pair) !== ['root', 'worktree']) {
                throw new RuntimeException('M91 exact paired bank source contexts differ.');
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
                    throw new RuntimeException('M91 original bank source raw/EOL/LF profile changed.');
                }
            }
            if ($profile['kind'] === 'definition') {
                $root = json_decode($pair['root'], true, flags: JSON_THROW_ON_ERROR);
                $worktree = json_decode($pair['worktree'], true, flags: JSON_THROW_ON_ERROR);
                if ($root !== $worktree || ($root['seeder']['class'] ?? null) !== $profile['identity']
                    || count($root['questions'] ?? []) !== $profile['question_count']) {
                    throw new RuntimeException('M91 complete canonical bank JSON identity/content differs.');
                }
            }
        }
    }

    /** Fixed filesystem reads only; no private capture or configurable path authority. */
    protected static function originalBankSourceBytes(): array
    {
        $out = [];
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            foreach (['root' => M91LocalTargetGuard::ROOT, 'worktree' => self::SOURCE_WORKTREE] as $context => $root) {
                $absolute = $root.'/'.$path;
                if (!is_file($absolute) || is_link($absolute)) {
                    throw new RuntimeException('M91 pinned original bank source missing or linked.');
                }
                $bytes = file_get_contents($absolute);
                if (!is_string($bytes)) { throw new RuntimeException('M91 original bank source unreadable.'); }
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
            throw new RuntimeException('M91 exact original bank/owner/actual practice level differs.');
        }
        $filter = static fn ($q) => $q['seeder'] === Package::BANKS[$i] && (string) $q['type'] === '4';
        $eligible = array_values(array_filter($questions, $filter));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
        if ($baseline !== null) {
            $expected = array_values(array_filter($baseline['relations']['questions'], $filter));
            usort($expected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($eligible !== $expected) { throw new RuntimeException('M91 complete original linked thematic pool changed.'); }
        }
        $ids = array_column($eligible, 'id');
        if (count($ids) !== self::ELIGIBLE_POOL_COUNTS[$i] || count(array_unique($ids)) !== count($ids)) {
            throw new RuntimeException('M91 independently captured original pool count/identity differs.');
        }
    }
}
