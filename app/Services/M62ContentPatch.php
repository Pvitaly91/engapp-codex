<?php

namespace App\Services;

use App\Support\M62ReportedReviewRelativeBasicsPackage as Package;

/** M62 finite content-only policy; unchanged native planner and M26 transaction writer. */
class M62ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'c1d652a764102b5abc9b5dd8a8c9226cd79126302dce58a69e52aab7a9b3c55d';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'c3cc41897ec5a331a8a142279b6e0504a48bf630a5ee22fff92619f63e3931e4';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '91a5ee08f15070317a43d31d9df686faeb6415de0fba01c9794055a947d26832';
    public const CONTROLLER_WT_AFTER_SHA = '7d9b222ba0050211f288d78ad647fbd75c9ee0e68253cc4e8a2e067d77813594';
    public const AUDIT = 'docs/content/m62-reported-review-relative-basics-bank-audit.v1.json';
    public const AUDIT_SHA = '8fdc925b5d6c83604131c8bb833bc4aebe4bab5fe7eb45bf123a92aa9448cd65';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M62ReportedReviewRelativeBasicsPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M62ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M62LocalTargetGuard.php',
        'app/Console/Commands/PatchM62Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryContentRenderer.php', 'app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Support/M62BuilderSelectionPresentation.php', self::AUDIT,
        'docs/content/m62-reported-review-relative-basics.v1.0.0.uk.md',
        'docs/content/m62-reported-review-relative-basics.editorial.v1.0.0.md',
        'docs/content/m62-reported-review-relative-basics.components.v1.json',
        'docs/content/m62-reported-review-relative-basics-checksums.v1.json',
    ];
    protected const ID = 'm62-reported-review-relative-basics-standard-view-v1';

    /** Eleven exact box type/heading transitions; no level or column writes. */
    protected function allowedUpdateFields(): array { return ['body', 'type', 'heading']; }
    protected static function packageClass(): string { return Package::class; }

    /** Existing curated questions authorize no question/link/taxonomy/progress writes. */
    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void
    {
        $root = dirname($this->databasePath); $auditPath = $root.'/'.self::AUDIT;
        if (!hash_equals(self::AUDIT_SHA, self::fileHash($auditPath))) {
            throw new \RuntimeException('M62 reviewed existing-question audit differs.');
        }
        $audit = json_decode(file_get_contents($auditPath), true, flags: JSON_THROW_ON_ERROR);
        \App\Support\M62BuilderSelectionPresentation::validateAudit($audit, $patch, Package::load($root)[3]);
        if ($baseline !== null) {
            $actual = $this->protectedFingerprints([]);
            foreach ($baseline['sql_before'] as $table => $expected) {
                if (!in_array($table, ['pages', 'text_blocks'], true) && ($actual[$table] ?? null) !== $expected) {
                    throw new \RuntimeException('M62 zero-write protected table changed since independent BEFORE: '.$table);
                }
            }
        }
        $plan['question_write_policy'] = ['questions' => 0, 'question_options' => 0, 'question_answers' => 0,
            'question_option_question' => 0, 'question_theory_text_blocks' => 0];
    }

    protected static function policy(): array
    {
        return ['guard' => M62LocalTargetGuard::class, 'served_root' => M62LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS,
            'extra_source_paths' => ['app/Support/ComposeTokenCase.php', 'app/Support/M53BuilderHintPresentation.php',
                'app/Support/M54BuilderTokenPresentation.php', 'app/Support/M55BuilderContextPresentation.php',
                'app/Support/M56BuilderSelectionPresentation.php', 'app/Support/M59BuilderSelectionPresentation.php',
                'app/Support/M61ReportedSpeechChangesPackage.php', 'app/Support/M61BuilderSelectionPresentation.php',
                'app/Services/M61ContentPatch.php', 'app/Services/M61LocalTargetGuard.php',
                'app/Console/Commands/PatchM61Content.php',
                'app/Support/M60ReportedSpeechBasicsPackage.php', 'app/Support/M60BuilderSelectionPresentation.php',
                'app/Services/M60ContentPatch.php', 'app/Services/M60LocalTargetGuard.php',
                'app/Services/M60PracticeFeedbackCorrectionPatch.php',
                'app/Console/Commands/PatchM60Content.php', 'app/Console/Commands/PatchM60PracticeFeedback.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
