<?php

namespace App\Services;

use App\Support\M88PastContinuousPresentPerfectBasics as Package;
use RuntimeException;

/** Six UK owners, two continuing practices and four inserts; no question-family writes. */
class M88ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BANK_SOURCE_CAPTURE_SHA = '018a23eaf1063e263d12a037e8f2de686ddfe57a97402cd29ef99a3b9f1e7099';
    public const BANK_SOURCE_PROFILES = [
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectFormsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectFormsAllLevelsLessonSeeder',
            'lf_sha256' => '334cd2bb304db72ad0804f3f53a5ed15fbfff1c12b3a2295cbfbf42c7bdf9d8b',
            'root' => [
                'sha256' => 'a3ec4104ee093abf801f09e4ee00164b11782149b462b17d6eff43142229aef1',
                'bytes' => 94214,
                'crlf' => 1,
                'lf' => 3159,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'b885a1d52ac10de97f4a9cd2fdfdbc1c8b50efb643f0f3b73e3cdd322226843b',
                'bytes' => 97372,
                'crlf' => 3159,
                'lf' => 3159,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectFormsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectFormsAllLevelsLessonSeeder',
            'lf_sha256' => 'a068b2e46f852665224a2c4c9b41f94f0f8d31e3510954e0d2c1f590ad226970',
            'root' => [
                'sha256' => '1cd735823d7ec8f17661d0c256faad5fae5527c1f14ec666d8ee793b807313ef',
                'bytes' => 139,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '1cd735823d7ec8f17661d0c256faad5fae5527c1f14ec666d8ee793b807313ef',
                'bytes' => 139,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectNegativesAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectNegativesAllLevelsLessonSeeder',
            'lf_sha256' => '96b6186c5bac74623bb8fd63abb186b86c779a30c08fef3c452427acb0bfd5b7',
            'root' => [
                'sha256' => 'd9f355fc3368746f58a021d52ecc8540cabcf5213daca6933a1090764f9718e1',
                'bytes' => 98646,
                'crlf' => 1,
                'lf' => 3277,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '97b7a53f52601029506b744bb44d01d1e6803def64a3816a641dcd4a37b67dfd',
                'bytes' => 101922,
                'crlf' => 3277,
                'lf' => 3277,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectNegativesAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectNegativesAllLevelsLessonSeeder',
            'lf_sha256' => '40a19e04e7bb7e2da6ab7b2c3d9f9b3c67ced9139bbac2274e649f7612458e05',
            'root' => [
                'sha256' => 'a88c42e534891bcc859b2db5e194a688f7ff39421d09518023bf27572291656c',
                'bytes' => 147,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'a88c42e534891bcc859b2db5e194a688f7ff39421d09518023bf27572291656c',
                'bytes' => 147,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
    ];
    public const BEFORE_FILE = 'before-v2.json';
    public const BEFORE_SHA = 'ea09671a15af34ba0045b195b13e97a9581a38a308f6e136dd0c02d457b23abc';
    public const CONSUMERS_FILE = 'before-old-consumers-v4.json';
    public const CONSUMERS_SHA = '9c245aeb1c7cf1b067cc3a6d6d154fa60570446b04d6fba0279a7cdb1b9c1f77';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS = [72, 72, 72, 72, 72, 72];
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '0b2d53b1f0ec8865ab5514ade9195dba3948dff270bca387eb5952b3b7daa9e4';
    public const CONTROLLER_WT_AFTER_SHA = '808f0d352d910604e25cef711ecd90df975d583286a1c9ad0fda371cd22aa2ec';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/M88PastContinuousPresentPerfectBasics.php', 'app/Support/M88BuilderSelectionPresentation.php',
        'app/Services/M88ContentPatch.php', 'app/Services/M88LocalTargetGuard.php', 'app/Console/Commands/ContentPatchM88.php',
        Package::BINDINGS, Package::CHECKSUMS, Package::NAVIGATION,
        'docs/content/m88-sentence-builder-bank-audit.v1.json',
        'docs/content/m88-past-continuous-present-perfect-basics.components.v1.json',
        'docs/content/m88-past-continuous-present-perfect-basics.tasks.v1.json',
        'docs/content/m88-past-continuous-present-perfect-basics.editorial.v1.json',
        'docs/content/m88-past-continuous-present-perfect-basics.coverage.v1.json',
        'docs/content/m88-widget-selection.v1.json', 'docs/content/m88-past-continuous-present-perfect-basics.v1.0.0.uk.md',
        'docs/content/m88-author-parts/ab.v1.json', 'docs/content/m88-author-parts/cd.v1.json', 'docs/content/m88-author-parts/ef.v1.json',
        'tests/Fixtures/m88-owned-source-hunks.json',
    ];
    protected const ID = 'm88-past-continuous-present-perfect-basics-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return []; }
    protected static function policy(): array
    {
        return ['guard' => M88LocalTargetGuard::class, 'served_root' => M88LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectFormsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPresentPerfectFormsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectNegativesAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPresentPerfectNegativesAllLevelsLessonSeeder.php',
            ],
            // Root-only fingerprints: a pre-existing ROOT/WT semantic difference is not a mirror authorisation.
            'extra_source_paths' => ['app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
                'app/Services/TheoryLocalReleaseGuard.php', 'app/Support/TheoryBuilderSelectionPresentation.php',
                'app/Support/M51QuestionSupplement.php', 'app/Support/M76PronounRelations.php',
                'app/Support/M76BuilderSelectionPresentation.php', 'app/Support/ComposeTokenCase.php',
                'app/Support/AnswerOptionCase.php', 'app/Support/AcceptedAnswerVariants.php',
                'database/seeders/V3/Polyglot/PolyglotPresentPerfectFormsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotPresentPerfectNegativesAllLevelsLessonSeeder/definition.json'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }


    /**
     * Preserve two captured JSON EOL profiles, never normalize/copy old banks.
     * The generic sync inventory uses their unchanged PHP wrappers; both actual
     * canonical definitions remain pinned here and in sourcePaths/protected scans.
     * Raw pins bind the entire source JSON, not only selected teaching fields.
     */
    public static function assertOriginalBankSourceBytes(array $sources): void
    {
        if (array_keys($sources) !== array_keys(self::BANK_SOURCE_PROFILES)) {
            throw new RuntimeException('M88 exact immutable E/F bank source path inventory differs.');
        }
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            $pair = $sources[$path];
            if (!is_array($pair) || array_keys($pair) !== ['root', 'worktree']) {
                throw new RuntimeException('M88 exact paired bank source contexts differ.');
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
                    throw new RuntimeException('M88 original bank source raw/EOL/LF profile changed.');
                }
            }
            if ($profile['kind'] === 'definition') {
                $root = json_decode($pair['root'], true, flags: JSON_THROW_ON_ERROR);
                $worktree = json_decode($pair['worktree'], true, flags: JSON_THROW_ON_ERROR);
                if ($root !== $worktree || ($root['seeder']['class'] ?? null) !== $profile['identity']
                    || count($root['questions'] ?? []) !== 72) {
                    throw new RuntimeException('M88 complete canonical bank JSON identity/content differs.');
                }
            }
        }
    }

    /** Fixed filesystem reads only; no private capture or configurable path authority. */
    protected static function originalBankSourceBytes(): array
    {
        $out = [];
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            foreach (['root' => M88LocalTargetGuard::ROOT, 'worktree' => self::SOURCE_WORKTREE] as $context => $root) {
                $absolute = $root.'/'.$path;
                if (!is_file($absolute) || is_link($absolute)) {
                    throw new RuntimeException('M88 pinned original bank source missing or linked.');
                }
                $bytes = file_get_contents($absolute);
                if (!is_string($bytes)) { throw new RuntimeException('M88 original bank source unreadable.'); }
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
            throw new RuntimeException('M88 exact original bank/owner/actual practice level differs.');
        }
        $filter = static fn ($q) => $q['seeder'] === Package::BANKS[$i] && (string) $q['type'] === '4';
        $eligible = array_values(array_filter($questions, $filter));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
        if ($baseline !== null) {
            $expected = array_values(array_filter($baseline['relations']['questions'], $filter));
            usort($expected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($eligible !== $expected) { throw new RuntimeException('M88 complete original linked thematic pool changed.'); }
        }
        $ids = array_column($eligible, 'id');
        if (count($ids) !== self::ELIGIBLE_POOL_COUNTS[$i] || count(array_unique($ids)) !== count($ids)) {
            throw new RuntimeException('M88 independently captured original pool count/identity differs.');
        }
    }
}
