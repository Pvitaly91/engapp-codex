<?php

namespace App\Services;

use App\Support\M87ContinuousCompletionPastSimple as Package;
use RuntimeException;

/** Six UK owners, four continuing practices and two inserts; no question-family writes. */
class M87ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'c9b7cffb07a0440e5051a0b7c522eb016ce4cdcc80107edf6573e8780eb151c5';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '9ca77ac1e9691290f0e22a720d33aa141b5e6072895a869c4f0046681ada0221';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS = [72, 72, 72, 72, 72, 72];
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '063a04a33ad161161c5c222990c585a89ea9ab3e0391f17f34c203e0762a4848';
    public const CONTROLLER_WT_AFTER_SHA = '8662488b69306d0a0ad3f5b70525cd0185b6f1142c8178b2155bd5e2d22173d6';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/M87ContinuousCompletionPastSimple.php', 'app/Support/M87BuilderSelectionPresentation.php',
        'app/Services/M87ContentPatch.php', 'app/Services/M87LocalTargetGuard.php', 'app/Console/Commands/ContentPatchM87.php',
        Package::BINDINGS, Package::CHECKSUMS, Package::NAVIGATION,
        'docs/content/m87-sentence-builder-bank-audit.v1.json',
        'docs/content/m87-continuous-completion-past-simple.components.v1.json',
        'docs/content/m87-continuous-completion-past-simple.tasks.v1.json',
        'docs/content/m87-continuous-completion-past-simple.editorial.v1.json',
        'docs/content/m87-continuous-completion-past-simple.coverage.v1.json',
        'docs/content/m87-widget-selection.v1.json', 'docs/content/m87-continuous-completion-past-simple.v1.0.0.uk.md',
        'docs/content/m87-author-parts/ab.v1.json', 'docs/content/m87-author-parts/cd.v1.json', 'docs/content/m87-author-parts/ef.v1.json',
        'tests/Fixtures/m87-owned-source-hunks.json',
    ];
    protected const ID = 'm87-continuous-completion-past-simple-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return []; }
    protected static function policy(): array
    {
        return ['guard' => M87LocalTargetGuard::class, 'served_root' => M87LocalTargetGuard::ROOT,
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
            throw new RuntimeException('M87 exact original bank/owner/actual practice level differs.');
        }
        $filter = static fn ($q) => $q['seeder'] === Package::BANKS[$i] && (string) $q['type'] === '4';
        $eligible = array_values(array_filter($questions, $filter));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
        if ($baseline !== null) {
            $expected = array_values(array_filter($baseline['relations']['questions'], $filter));
            usort($expected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($eligible !== $expected) { throw new RuntimeException('M87 complete original linked thematic pool changed.'); }
        }
        $ids = array_column($eligible, 'id');
        if (count($ids) !== self::ELIGIBLE_POOL_COUNTS[$i] || count(array_unique($ids)) !== count($ids)) {
            throw new RuntimeException('M87 independently captured original pool count/identity differs.');
        }
    }
}
