<?php

namespace App\Services;

use App\Support\M89PresentPerfectCompletionContinuous as Package;
use RuntimeException;

/** Six UK owners and six continuing practices; body-only updates, no inserts or question-family writes. */
class M89ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BANK_SOURCE_CAPTURE_SHA = '8c3f38c4381f839602b507d73924bc46d5bf06b2492dab5ceffdb0bc3a2d3bb0';
    public const BANK_SOURCE_PROFILES = [
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder',
            'lf_sha256' => '680aff1f631a72dddeb49964066b88420e7a61ba031346d6a07b21efc8ccdd4c',
            'root' => [
                'sha256' => '9451f42fb98d3876e11bcb3229e3165ba6b2b522415683b35ce22f259f9bf502',
                'bytes' => 100710,
                'crlf' => 1,
                'lf' => 3343,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '48edd899daaaa6b8ce500c06a1255bd30737bae4e700591d64a3215d38a142a4',
                'bytes' => 104052,
                'crlf' => 3343,
                'lf' => 3343,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder',
            'lf_sha256' => '44e2b0c76918b2c0cad17b9fd1ca0eaf224b3e1da79aa9a0d62dfca2ebb84a3f',
            'root' => [
                'sha256' => 'f7fb04187aa6ca24767233909746a4c2169cb75b656dafdf5fb21fd82882c053',
                'bytes' => 147,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'f7fb04187aa6ca24767233909746a4c2169cb75b656dafdf5fb21fd82882c053',
                'bytes' => 147,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectTimeExpressionsAllLevelsLessonSeeder',
            'lf_sha256' => 'c5430e04cc0e0ed22576230145f2bcdf06d142c59b31e4b49bb326a19550f65f',
            'root' => [
                'sha256' => '3058f579c898dffc463407db248ba45d7a15c7851e2a35fe3ad7338f0af86505',
                'bytes' => 56577,
                'crlf' => 575,
                'lf' => 576,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '596738477e840973236bd9b218eb0aab18aad2169442a871c53607077ccb29ba',
                'bytes' => 56578,
                'crlf' => 576,
                'lf' => 576,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectTimeExpressionsAllLevelsLessonSeeder',
            'lf_sha256' => '2fce9928b0effef39ba32918f99b53ad006e8c592e3baca63744fd1876b96e21',
            'root' => [
                'sha256' => '0b1d2dca0e0bd47ff08cc7ec926bb440948f97125898662a9149ca73b7ea907b',
                'bytes' => 159,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '0b1d2dca0e0bd47ff08cc7ec926bb440948f97125898662a9149ca73b7ea907b',
                'bytes' => 159,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousFormsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousFormsAllLevelsLessonSeeder',
            'lf_sha256' => '3b3937596f20f2d5cff498f64e4db88dceb2e4a87c65862178464a67242816dd',
            'root' => [
                'sha256' => '0df6fdf95bfa2fe7fb9295fec1f15afd9e27149ec662c873747017d4fcf91b16',
                'bytes' => 126302,
                'crlf' => 4327,
                'lf' => 4327,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '0df6fdf95bfa2fe7fb9295fec1f15afd9e27149ec662c873747017d4fcf91b16',
                'bytes' => 126302,
                'crlf' => 4327,
                'lf' => 4327,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousFormsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousFormsAllLevelsLessonSeeder',
            'lf_sha256' => '3fc4314f3f267089ff20cad351b82975941c259e72e516b381fb6d0f8e0f4da7',
            'root' => [
                'sha256' => 'a338b6f3c2754e4a653f55d0b0d204dc88f50394d7e1118be1f6860f3a8598a8',
                'bytes' => 159,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'a338b6f3c2754e4a653f55d0b0d204dc88f50394d7e1118be1f6860f3a8598a8',
                'bytes' => 159,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousNegativesAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousNegativesAllLevelsLessonSeeder',
            'lf_sha256' => '7433855d90f3a424ed6c6dead378c2fb695652c9c0737790add114effb011d6b',
            'root' => [
                'sha256' => '7313f0af9bb32051f8d03ef1532c858694bc8ecbdf0554554663b59ab9c67a20',
                'bytes' => 128998,
                'crlf' => 4372,
                'lf' => 4372,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '7313f0af9bb32051f8d03ef1532c858694bc8ecbdf0554554663b59ab9c67a20',
                'bytes' => 128998,
                'crlf' => 4372,
                'lf' => 4372,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousNegativesAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousNegativesAllLevelsLessonSeeder',
            'lf_sha256' => '8860d6a376d76d1edaa35226062c9538d49864fecfbf18a09beded46ba63fe92',
            'root' => [
                'sha256' => '1dbbd026c91414f4623990246eee07dd4de58144b67fac63b127cba74cb685a0',
                'bytes' => 167,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '1dbbd026c91414f4623990246eee07dd4de58144b67fac63b127cba74cb685a0',
                'bytes' => 167,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousQuestionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousQuestionsAllLevelsLessonSeeder',
            'lf_sha256' => '3cd7ea507a101edd00154cb4d2cd5b4bf6cc30484ffe964480752a1e1d69c857',
            'root' => [
                'sha256' => '33138b36fc62289e7b9b1bbcf2bd40382380b47754b9bf481baa3f24216ebc93',
                'bytes' => 124074,
                'crlf' => 4259,
                'lf' => 4259,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '33138b36fc62289e7b9b1bbcf2bd40382380b47754b9bf481baa3f24216ebc93',
                'bytes' => 124074,
                'crlf' => 4259,
                'lf' => 4259,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousQuestionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousQuestionsAllLevelsLessonSeeder',
            'lf_sha256' => '70db4a7cddbf38b56d5a437fc36e106c23abe05570a2a24ce330ebe0eb0b1901',
            'root' => [
                'sha256' => '53e1f3b2adfe650aabae8eec18506b63272425dcaa266e5db086356c81bb29fa',
                'bytes' => 167,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '53e1f3b2adfe650aabae8eec18506b63272425dcaa266e5db086356c81bb29fa',
                'bytes' => 167,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder',
            'lf_sha256' => '0edf7a2590a0d8e84893bf4f9e74081b648dcfad4f65e17d53e06df2f54c44a5',
            'root' => [
                'sha256' => '89f944b20a522700bfe78ad2c38c05480de60009cedb31bd1c764ec1570d2944',
                'bytes' => 125291,
                'crlf' => 4245,
                'lf' => 4245,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '89f944b20a522700bfe78ad2c38c05480de60009cedb31bd1c764ec1570d2944',
                'bytes' => 125291,
                'crlf' => 4245,
                'lf' => 4245,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder',
            'lf_sha256' => '1904fff8c200410f2063af17483ff6515a7e171d16c40834d3fcba35a3a0cfbe',
            'root' => [
                'sha256' => 'e3089937c9eb0dad08a807d907af361e46d74eace6c593e0495ce724deb9bebb',
                'bytes' => 179,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'e3089937c9eb0dad08a807d907af361e46d74eace6c593e0495ce724deb9bebb',
                'bytes' => 179,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
    ];
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '6c443660442bc7f683264c381b559973e19ea2fce9e58fa8410e6b21ff0416ae';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'dca0306b77ef145cba63075cca79b34e9eac3ec23279de761c090a55ca6175ed';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS = [72, 72, 72, 72, 72, 72];
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '5e5e76ce536f23820791931b9550f936d470c7ceb2fded631aae0e4b8483e788';
    public const CONTROLLER_WT_AFTER_SHA = '5a23e49a8cfbbdc41df5eff3058c68fd77e73d81d0138e68a9fe4d41009c0e46';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryBuilderSelectionPresentation.php',
        'app/Support/M89PresentPerfectCompletionContinuous.php', 'app/Support/M89BuilderSelectionPresentation.php',
        'app/Services/M89ContentPatch.php', 'app/Services/M89LocalTargetGuard.php', 'app/Console/Commands/ContentPatchM89.php',
        Package::BINDINGS, Package::CHECKSUMS, Package::NAVIGATION,
        'docs/content/m89-sentence-builder-bank-audit.v1.json',
        'docs/content/m89-present-perfect-completion-continuous.components.v1.json',
        'docs/content/m89-present-perfect-completion-continuous.tasks.v1.json',
        'docs/content/m89-present-perfect-completion-continuous.editorial.v1.json',
        'docs/content/m89-present-perfect-completion-continuous.coverage.v1.json',
        'docs/content/m89-widget-selection.v1.json', 'docs/content/m89-present-perfect-completion-continuous.v1.0.0.uk.md',
        'docs/content/m89-author-parts/ab.v1.json', 'docs/content/m89-author-parts/cd.v1.json', 'docs/content/m89-author-parts/ef.v1.json',
        'tests/Fixtures/m89-owned-source-hunks.json',
    ];
    protected const ID = 'm89-present-perfect-completion-continuous-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return []; }
    protected function expectedInsertCount(): int { return 0; }
    protected static function policy(): array
    {
        return ['guard' => M89LocalTargetGuard::class, 'served_root' => M89LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectTimeExpressionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousFormsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousFormsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousNegativesAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousNegativesAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousQuestionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousQuestionsAllLevelsLessonSeeder.php',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder' => 'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder.php',
            ],
            // Root-only fingerprints: a pre-existing ROOT/WT semantic difference is not a mirror authorisation.
            'extra_source_paths' => [
                'app/Support/TheoryNativeContentRelease.php',
                'app/Services/TheoryNativeContentPatch.php',
                'app/Services/TheoryLocalReleaseGuard.php',
                'app/Support/M51QuestionSupplement.php',
                'app/Support/M76PronounRelations.php',
                'app/Support/M76BuilderSelectionPresentation.php',
                'app/Support/ComposeTokenCase.php',
                'app/Support/AnswerOptionCase.php',
                'app/Support/AcceptedAnswerVariants.php',
                'database/seeders/V3/Polyglot/PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousFormsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousNegativesAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousQuestionsAllLevelsLessonSeeder/definition.json',
                'database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder/definition.json',
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
            throw new RuntimeException('M89 exact immutable six-bank source path inventory differs.');
        }
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            $pair = $sources[$path];
            if (!is_array($pair) || array_keys($pair) !== ['root', 'worktree']) {
                throw new RuntimeException('M89 exact paired bank source contexts differ.');
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
                    throw new RuntimeException('M89 original bank source raw/EOL/LF profile changed.');
                }
            }
            if ($profile['kind'] === 'definition') {
                $root = json_decode($pair['root'], true, flags: JSON_THROW_ON_ERROR);
                $worktree = json_decode($pair['worktree'], true, flags: JSON_THROW_ON_ERROR);
                if ($root !== $worktree || ($root['seeder']['class'] ?? null) !== $profile['identity']
                    || count($root['questions'] ?? []) !== 72) {
                    throw new RuntimeException('M89 complete canonical bank JSON identity/content differs.');
                }
            }
        }
    }

    /** Fixed filesystem reads only; no private capture or configurable path authority. */
    protected static function originalBankSourceBytes(): array
    {
        $out = [];
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            foreach (['root' => M89LocalTargetGuard::ROOT, 'worktree' => self::SOURCE_WORKTREE] as $context => $root) {
                $absolute = $root.'/'.$path;
                if (!is_file($absolute) || is_link($absolute)) {
                    throw new RuntimeException('M89 pinned original bank source missing or linked.');
                }
                $bytes = file_get_contents($absolute);
                if (!is_string($bytes)) { throw new RuntimeException('M89 original bank source unreadable.'); }
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
            throw new RuntimeException('M89 exact original bank/owner/actual practice level differs.');
        }
        $filter = static fn ($q) => $q['seeder'] === Package::BANKS[$i] && (string) $q['type'] === '4';
        $eligible = array_values(array_filter($questions, $filter));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
        if ($baseline !== null) {
            $expected = array_values(array_filter($baseline['relations']['questions'], $filter));
            usort($expected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($eligible !== $expected) { throw new RuntimeException('M89 complete original linked thematic pool changed.'); }
        }
        $ids = array_column($eligible, 'id');
        if (count($ids) !== self::ELIGIBLE_POOL_COUNTS[$i] || count(array_unique($ids)) !== count($ids)) {
            throw new RuntimeException('M89 independently captured original pool count/identity differs.');
        }
    }
}
