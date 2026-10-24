<?php

namespace App\Services;

use App\Support\M86PresentSimpleContinuousBasics as Package;
use RuntimeException;

/** Six UK owners, four continuing practices and two inserts; no question-family writes. */
class M86ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '42f8a613fcb1721b2badcfabb9c67116f2c79fa9c14b7dc4df5e19dd71892208';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '1b02a5a54d5c4c2498f77b52f5f45afbc4183ce8eb66b12f0ed5bcd10f89ce14';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS = [72, 72, 72, 72, 72, 72];
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '76f79b5d17b68d9b422780b88aad86b2cdb150d85a6a6580af100af2f45b9cf9';
    public const CONTROLLER_WT_AFTER_SHA = '2f4f128fe2e9209d28dd437f86a37c7a367bc8bb603842d743d37c02ef85c7a6';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/M86PresentSimpleContinuousBasics.php', 'app/Support/M86BuilderSelectionPresentation.php',
        'app/Services/M86ContentPatch.php', 'app/Services/M86LocalTargetGuard.php', 'app/Console/Commands/ContentPatchM86.php',
        Package::BINDINGS, Package::CHECKSUMS, Package::NAVIGATION,
        'docs/content/m86-sentence-builder-bank-audit.v1.json',
        'docs/content/m86-present-simple-continuous-basics.components.v1.json',
        'docs/content/m86-present-simple-continuous-basics.tasks.v1.json',
        'docs/content/m86-present-simple-continuous-basics.editorial.v1.json',
        'docs/content/m86-present-simple-continuous-basics.coverage.v1.json',
        'docs/content/m86-widget-selection.v1.json', 'docs/content/m86-present-simple-continuous-basics.v1.0.0.uk.md',
        'docs/content/m86-author-parts/ab.v1.json', 'docs/content/m86-author-parts/cd.v1.json', 'docs/content/m86-author-parts/ef.v1.json',
        'tests/Fixtures/m86-owned-source-hunks.json',
    ];
    protected const ID = 'm86-present-simple-continuous-basics-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return []; }
    protected static function policy(): array
    {
        return ['guard' => M86LocalTargetGuard::class, 'served_root' => M86LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [],
            // Root-only fingerprints: a pre-existing ROOT/WT semantic difference is not a mirror authorisation.
            'extra_source_paths' => ['app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
                'app/Services/TheoryLocalReleaseGuard.php', 'app/Support/TheoryBuilderSelectionPresentation.php',
                'app/Support/M51QuestionSupplement.php', 'app/Support/M76PronounRelations.php',
                'app/Support/M76BuilderSelectionPresentation.php', 'app/Support/ComposeTokenCase.php',
                'app/Support/AnswerOptionCase.php', 'app/Support/AcceptedAnswerVariants.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }

    /** NULL is the captured practice filter, not an invented question-level equality. */
    protected function assertExistingPools(array $questions, array $contract, ?array $baseline): void
    {
        $i = array_search($contract['identity'] ?? null, Package::OWNERS, true);
        if ($i === false || !array_key_exists('level', $contract) || $contract['level'] !== null
            || ($contract['bank_seeders'] ?? null) !== [Package::BANKS[$i]]) {
            throw new RuntimeException('M86 exact original bank/owner/actual practice level differs.');
        }
        $filter = static fn ($q) => $q['seeder'] === Package::BANKS[$i] && (string) $q['type'] === '4';
        $eligible = array_values(array_filter($questions, $filter));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
        if ($baseline !== null) {
            $expected = array_values(array_filter($baseline['relations']['questions'], $filter));
            usort($expected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($eligible !== $expected) { throw new RuntimeException('M86 complete original linked thematic pool changed.'); }
        }
        $ids = array_column($eligible, 'id');
        if (count($ids) !== self::ELIGIBLE_POOL_COUNTS[$i] || count(array_unique($ids)) !== count($ids)) {
            throw new RuntimeException('M86 independently captured original pool count/identity differs.');
        }
    }
}
