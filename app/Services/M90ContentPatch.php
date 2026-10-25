<?php

namespace App\Services;

use App\Support\M90PastPerfectFutureSimpleBasics as Package;
use RuntimeException;

/** Six UK owners and six continuing practices; body-only updates, no inserts or question-family writes. */
class M90ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BANK_SOURCE_CAPTURE_SHA = '2c2cd753a7748d58ce25bc0db1afceef345104f59557194ef5c9d32adcf8cc58';
    public const BANK_SOURCE_PROFILES = [
        'database/seeders/V3/Polyglot/PolyglotPastPerfectFormsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectFormsAllLevelsLessonSeeder',
            'lf_sha256' => '53cab6bba1c05b715c734523ad5bde3417e186fc7b0a61af5473024a73fe8fdb',
            'root' => [
                'sha256' => '66f33c2fe71801b2d323391d55bca3ef06b962a86ae6e759209d1d1dacff577a',
                'bytes' => 109708,
                'crlf' => 1,
                'lf' => 3693,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '7126cfa455b447e5b89823237abf77b28665099374306ca9361a86099fd50a7b',
                'bytes' => 113400,
                'crlf' => 3693,
                'lf' => 3693,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPastPerfectFormsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectFormsAllLevelsLessonSeeder',
            'lf_sha256' => '826abd8c9934de1414605f3dd0b3a960228a36e09a09b5c8dba2799575454364',
            'root' => [
                'sha256' => 'a51148f4f5e7ecfb7193245916acbc8d2e8d88705f1a7411998a17cc7bfa4e68',
                'bytes' => 133,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'a51148f4f5e7ecfb7193245916acbc8d2e8d88705f1a7411998a17cc7bfa4e68',
                'bytes' => 133,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPastPerfectNegativesAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectNegativesAllLevelsLessonSeeder',
            'lf_sha256' => 'b99892e0fb32c7e6bebca353e3b2e1d90a5d1f87336bee9de5d1e6e930c788d0',
            'root' => [
                'sha256' => '42c0a422336d45cca07113511d06ac3ca3f175ab90dff407ce16338d9fcd1c77',
                'bytes' => 105299,
                'crlf' => 1,
                'lf' => 3555,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'b7ca26573853629ac9bfc160799e270386cb92f2c3ecec002631db094edf7038',
                'bytes' => 108853,
                'crlf' => 3555,
                'lf' => 3555,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPastPerfectNegativesAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectNegativesAllLevelsLessonSeeder',
            'lf_sha256' => 'ffd5bbfee61d5f9fdfae1886c681c8e32d46b98f30d857805efcb4d9ad50bbd0',
            'root' => [
                'sha256' => '233b7668cce2ee382090d671cb4624dbaedc9b764f318ed229872d3bb1f74f55',
                'bytes' => 141,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '233b7668cce2ee382090d671cb4624dbaedc9b764f318ed229872d3bb1f74f55',
                'bytes' => 141,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPastPerfectQuestionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectQuestionsAllLevelsLessonSeeder',
            'lf_sha256' => 'fdec92e00fec25e3040f24a91cb58a69741c8e62baf830ace9bd85b122e60dad',
            'root' => [
                'sha256' => '2519b2b87ff21d6a8c0c7327777b9bb05b1c927a3b1abb77b1cf39446e4d92af',
                'bytes' => 104623,
                'crlf' => 1,
                'lf' => 3433,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'dda2cb2518811fc8e7a621ac90c529950814e1ce3f7160e23696603df148d8a4',
                'bytes' => 108055,
                'crlf' => 3433,
                'lf' => 3433,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPastPerfectQuestionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectQuestionsAllLevelsLessonSeeder',
            'lf_sha256' => '63428d9a2719bdc9cb5697e4d5e569e9ea900b4492313ac9b3ae0b248827848e',
            'root' => [
                'sha256' => '29b10418cc1dab4c99e666969b89b93603f31f77eb73cbacd20bc123d150fa61',
                'bytes' => 141,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '29b10418cc1dab4c99e666969b89b93603f31f77eb73cbacd20bc123d150fa61',
                'bytes' => 141,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder',
            'lf_sha256' => '525bfcb2399e5af30e93bf15706b928e3bb20597cf79c9b57eff862639ecba57',
            'root' => [
                'sha256' => '3d2b539517bdd9422b6c5becd0884afe6cb57b1b7553c381bc954bed925ee9b1',
                'bytes' => 118399,
                'crlf' => 1,
                'lf' => 3849,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '1d5bda959f0e445bca525b8800ac3f0db9b1dc442f1521b4389bc0f18d6a699f',
                'bytes' => 122247,
                'crlf' => 3849,
                'lf' => 3849,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder',
            'lf_sha256' => '8c91e545d3b4a5ac744461c395cb7ccd8f1651abcf8e9a792b9879e077985cd2',
            'root' => [
                'sha256' => '1ab9caceabbdf5e46b15a204ce6e83ff49e29a1a1af704d62f843f467d745dc6',
                'bytes' => 153,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '1ab9caceabbdf5e46b15a204ce6e83ff49e29a1a1af704d62f843f467d745dc6',
                'bytes' => 153,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureSimpleFormsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleFormsAllLevelsLessonSeeder',
            'lf_sha256' => '2645dcd2f3280ede643699f27dac7b3096ed739194401e7571b885e23bd038e4',
            'root' => [
                'sha256' => '4daff77782d43daed182ab29192d3212f05603cd8ee0982bed5d178dfb5f6cfd',
                'bytes' => 79314,
                'crlf' => 2669,
                'lf' => 2669,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '4daff77782d43daed182ab29192d3212f05603cd8ee0982bed5d178dfb5f6cfd',
                'bytes' => 79314,
                'crlf' => 2669,
                'lf' => 2669,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureSimpleFormsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleFormsAllLevelsLessonSeeder',
            'lf_sha256' => 'b0db563a49d1c952d1644b051a1400e8bf82b0d2eb6f304f35150473cf1cf78e',
            'root' => [
                'sha256' => 'a437a04c959eb87e844533f9c3c674adcbcee3a002585d87323406ef5a24d24d',
                'bytes' => 135,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'a437a04c959eb87e844533f9c3c674adcbcee3a002585d87323406ef5a24d24d',
                'bytes' => 135,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureSimpleNegativesAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleNegativesAllLevelsLessonSeeder',
            'lf_sha256' => '302d74b4a90dc00a63fd837866daae7895e0bb6a28bef5a6c3e3e8bdbeed15ad',
            'root' => [
                'sha256' => '92e692361a7dd3f2dfd70cb50a6abf671e42f8252a84b7a064dd79a107e3f220',
                'bytes' => 77398,
                'crlf' => 2537,
                'lf' => 2537,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '92e692361a7dd3f2dfd70cb50a6abf671e42f8252a84b7a064dd79a107e3f220',
                'bytes' => 77398,
                'crlf' => 2537,
                'lf' => 2537,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotFutureSimpleNegativesAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleNegativesAllLevelsLessonSeeder',
            'lf_sha256' => '79598f0b7ded69fcf4a4b3d5adde9ea34098cd9c576b85278ebe2c7b85df3a3a',
            'root' => [
                'sha256' => 'a670c20b67cfd60505528272592eef070e7347e6bef7e6c9dfd899dc6720c8c4',
                'bytes' => 145,
                'crlf' => 4,
                'lf' => 4,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'a670c20b67cfd60505528272592eef070e7347e6bef7e6c9dfd899dc6720c8c4',
                'bytes' => 145,
                'crlf' => 4,
                'lf' => 4,
                'utf8_bom' => false,
            ],
        ],
    ];
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'a18d8a109c08a914907811dbf98ab865406ffa96a97bb3a04eac6ccd10cdc518';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'b758332c4833a381d476814af0536a94baf8b82b3afa50fef638cf310dd74001';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS = [72, 72, 72, 72, 72, 72];
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '9bc6a0c3c0a4b6d30f98aaca3728ff4185c082aca33622475965f082e456834f';
    public const CONTROLLER_WT_AFTER_SHA = '0b5c829adf0c76fda16508d977448af500cf0afb6f8824314fb804e56b670c92';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/M90PastPerfectFutureSimpleBasics.php', 'app/Support/M90BuilderSelectionPresentation.php',
        'app/Services/M90ContentPatch.php', 'app/Services/M90LocalTargetGuard.php', 'app/Console/Commands/ContentPatchM90.php',
        Package::BINDINGS, Package::CHECKSUMS, Package::NAVIGATION,
        'docs/content/m90-sentence-builder-bank-audit.v1.json',
        'docs/content/m90-past-perfect-future-simple-basics.components.v1.json',
        'docs/content/m90-past-perfect-future-simple-basics.tasks.v1.json',
        'docs/content/m90-past-perfect-future-simple-basics.editorial.v1.json',
        'docs/content/m90-past-perfect-future-simple-basics.coverage.v1.json',
        'docs/content/m90-widget-selection.v1.json', 'docs/content/m90-past-perfect-future-simple-basics.v1.0.0.uk.md',
        'docs/content/m90-author-parts/ab.v1.json', 'docs/content/m90-author-parts/cd.v1.json', 'docs/content/m90-author-parts/ef.v1.json',
        'tests/Fixtures/m90-owned-source-hunks.json',
    ];
    protected const ID = 'm90-past-perfect-future-simple-basics-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return []; }
    protected function expectedInsertCount(): int { return 0; }
    protected static function policy(): array
    {
        return ['guard' => M90LocalTargetGuard::class, 'served_root' => M90LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectFormsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPastPerfectFormsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectNegativesAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPastPerfectNegativesAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectQuestionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPastPerfectQuestionsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleFormsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFutureSimpleFormsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFutureSimpleNegativesAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotFutureSimpleNegativesAllLevelsLessonSeeder.php',
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
                'database/seeders/V3/Polyglot/PolyglotPastPerfectFormsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotPastPerfectNegativesAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotPastPerfectQuestionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFutureSimpleFormsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotFutureSimpleNegativesAllLevelsLessonSeeder/definition.json',
            ],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }


    /**
     * Preserve six captured JSON EOL profiles, never normalize/copy old banks.
     * The generic sync inventory uses unchanged PHP wrappers; all six actual
     * canonical definitions remain pinned here and in sourcePaths/protected scans.
     * Raw pins bind the entire source JSON, not only selected teaching fields.
     */
    public static function assertOriginalBankSourceBytes(array $sources): void
    {
        if (array_keys($sources) !== array_keys(self::BANK_SOURCE_PROFILES)) {
            throw new RuntimeException('M90 exact immutable six-bank source path inventory differs.');
        }
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            $pair = $sources[$path];
            if (!is_array($pair) || array_keys($pair) !== ['root', 'worktree']) {
                throw new RuntimeException('M90 exact paired bank source contexts differ.');
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
                    throw new RuntimeException('M90 original bank source raw/EOL/LF profile changed.');
                }
            }
            if ($profile['kind'] === 'definition') {
                $root = json_decode($pair['root'], true, flags: JSON_THROW_ON_ERROR);
                $worktree = json_decode($pair['worktree'], true, flags: JSON_THROW_ON_ERROR);
                if ($root !== $worktree || ($root['seeder']['class'] ?? null) !== $profile['identity']
                    || count($root['questions'] ?? []) !== 72) {
                    throw new RuntimeException('M90 complete canonical bank JSON identity/content differs.');
                }
            }
        }
    }

    /** Fixed filesystem reads only; no private capture or configurable path authority. */
    protected static function originalBankSourceBytes(): array
    {
        $out = [];
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            foreach (['root' => M90LocalTargetGuard::ROOT, 'worktree' => self::SOURCE_WORKTREE] as $context => $root) {
                $absolute = $root.'/'.$path;
                if (!is_file($absolute) || is_link($absolute)) {
                    throw new RuntimeException('M90 pinned original bank source missing or linked.');
                }
                $bytes = file_get_contents($absolute);
                if (!is_string($bytes)) { throw new RuntimeException('M90 original bank source unreadable.'); }
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
            throw new RuntimeException('M90 exact original bank/owner/actual practice level differs.');
        }
        $filter = static fn ($q) => $q['seeder'] === Package::BANKS[$i] && (string) $q['type'] === '4';
        $eligible = array_values(array_filter($questions, $filter));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
        if ($baseline !== null) {
            $expected = array_values(array_filter($baseline['relations']['questions'], $filter));
            usort($expected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($eligible !== $expected) { throw new RuntimeException('M90 complete original linked thematic pool changed.'); }
        }
        $ids = array_column($eligible, 'id');
        if (count($ids) !== self::ELIGIBLE_POOL_COUNTS[$i] || count(array_unique($ids)) !== count($ids)) {
            throw new RuntimeException('M90 independently captured original pool count/identity differs.');
        }
    }
}
