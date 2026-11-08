<?php

namespace App\Services;

use App\Support\M64ReasonResultContrastPackage as Package;

/** M64 finite content-only policy; unchanged native planner and M26 transaction writer. */
class M64ContentPatch extends TheoryNativeContentPatch
{
    use M64QuestionInsertScope;
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '46296d1b282d4f5c5c54b4eeaf1640680a173bb3e81482f5109ded710609f63d';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'acda587a95ac48c503024da7f4e69b30eef8ad847306e48943400f536b276f70';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '116c44ae457a809755be2cd464144a1c0fe86d40d48c063352a4961ab8b7503f';
    public const CONTROLLER_WT_AFTER_SHA = '6769b8beb4bab7c25055fb32d5af5a6cc1f38c0b0e8d547e2fd755f90a1cf9de';
    public const AUDIT = 'docs/content/m64-reason-result-contrast-bank-audit.v1.json';
    public const AUDIT_SHA = '2982c20a18baf5d896506c1ee5f766560cba7b783a6d043f5669b5ef755e4f59';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M64ReasonResultContrastPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M64ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M64LocalTargetGuard.php',
        'app/Console/Commands/PatchM64Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryContentRenderer.php', 'app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Support/M64BuilderSelectionPresentation.php', 'app/Support/M64QuestionSupplement.php',
        'app/Services/M64QuestionInsertScope.php', self::AUDIT,
        'docs/content/m64-reason-result-contrast-widget-questions.v1.0.0.json',
        'docs/content/m64-reason-result-contrast.v1.0.0.uk.md',
        'docs/content/m64-reason-result-contrast.editorial.v1.0.0.md',
        'docs/content/m64-reason-result-contrast.components.v1.json',
        'docs/content/m64-reason-result-contrast-checksums.v1.json',
        'database/seeders/V3/Polyglot/M64ReasonB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M64ReasonB1SupplementSeeder/M64ReasonB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M64ResultB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M64ResultB1SupplementSeeder/M64ResultB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M64ContrastB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M64ContrastB1SupplementSeeder/M64ContrastB1SupplementSeeder.php',
    ];
    protected const ID = 'm64-reason-result-contrast-standard-view-v1';

    /** Fifteen exact box type/heading transitions; no level or column writes. */
    protected function allowedUpdateFields(): array { return ['body', 'type', 'heading']; }
    protected static function packageClass(): string { return Package::class; }

    /** Only the eight reviewed inserts may extend the unchanged M26 transaction. */
    public function apply(string $planPath, string $backupPath, ?string $expectedDatabase = null): array
    {
        $projection = $this->questionEvidence()['projection'];
        $result = parent::apply($planPath, $backupPath, $expectedDatabase);
        $counts = [];
        foreach (\App\Support\M51QuestionSupplement::ROW_COLUMNS as $table => $_) {
            $counts[$table] = $result['status'] === 'applied' ? count($projection[$table]) : 0;
        }
        return $result + ['question_families_inserted' => $counts];
    }

    protected static function policy(): array
    {
        return ['guard' => M64LocalTargetGuard::class, 'served_root' => M64LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS,
            'extra_source_paths' => ['app/Support/M51QuestionSupplement.php', 'app/Support/ComposeTokenCase.php', 'app/Support/M53BuilderHintPresentation.php',
                'app/Support/M54BuilderTokenPresentation.php', 'app/Support/M55BuilderContextPresentation.php',
                'app/Support/M56BuilderSelectionPresentation.php', 'app/Support/M59BuilderSelectionPresentation.php',
                'app/Support/M61ReportedSpeechChangesPackage.php', 'app/Support/M61BuilderSelectionPresentation.php',
                'app/Services/M61ContentPatch.php', 'app/Services/M61LocalTargetGuard.php',
                'app/Console/Commands/PatchM61Content.php',
                'app/Support/M62ReportedReviewRelativeBasicsPackage.php', 'app/Support/M62BuilderSelectionPresentation.php',
                'app/Services/M62ContentPatch.php', 'app/Services/M62LocalTargetGuard.php',
                'app/Console/Commands/PatchM62Content.php',
                'app/Support/M63RelativeCompletionLinkingPackage.php', 'app/Support/M63BuilderSelectionPresentation.php',
                'app/Services/M63ContentPatch.php', 'app/Services/M63LocalTargetGuard.php', 'app/Console/Commands/PatchM63Content.php',
                'app/Support/M60ReportedSpeechBasicsPackage.php', 'app/Support/M60BuilderSelectionPresentation.php',
                'app/Services/M60ContentPatch.php', 'app/Services/M60LocalTargetGuard.php',
                'app/Services/M60PracticeFeedbackCorrectionPatch.php',
                'app/Console/Commands/PatchM60Content.php', 'app/Console/Commands/PatchM60PracticeFeedback.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
