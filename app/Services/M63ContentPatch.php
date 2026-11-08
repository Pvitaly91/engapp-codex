<?php

namespace App\Services;

use App\Support\M63RelativeCompletionLinkingPackage as Package;

/** M63 finite content-only policy; unchanged native planner and M26 transaction writer. */
class M63ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'daf7c1306cb8e292c5ccaa98aade4d611038509495871eb426a79be6fd67c585';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '727adc6ccdedbcf6947b8e25ad59464316934ce0f73b7239c84adbbf1b85ceba';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = 'c460af3c209eb0477d6f06293de3a468fd82f7e0ebbe91a981719d6580cb2175';
    public const CONTROLLER_WT_AFTER_SHA = 'da1ce1585a3fa68af4aad6927d1cdcb539c65576504e6b762ced06c304e96391';
    public const AUDIT = 'docs/content/m63-relative-completion-linking-bank-audit.v1.json';
    public const AUDIT_SHA = '627dc8ebd915ee6555ce9d42de6251e4da738799d43eddbb055deb25f1316b6c';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M63RelativeCompletionLinkingPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M63ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M63LocalTargetGuard.php',
        'app/Console/Commands/PatchM63Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryContentRenderer.php', 'app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Support/M63BuilderSelectionPresentation.php', self::AUDIT,
        'docs/content/m63-relative-completion-linking.v1.0.0.uk.md',
        'docs/content/m63-relative-completion-linking.editorial.v1.0.0.md',
        'docs/content/m63-relative-completion-linking.components.v1.json',
        'docs/content/m63-relative-completion-linking-checksums.v1.json',
    ];
    protected const ID = 'm63-relative-completion-linking-standard-view-v1';

    /** Fifteen exact box type/heading transitions; no level or column writes. */
    protected function allowedUpdateFields(): array { return ['body', 'type', 'heading']; }
    protected static function packageClass(): string { return Package::class; }

    /** Existing curated questions authorize no question/link/taxonomy/progress writes. */
    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void
    {
        $root = dirname($this->databasePath); $auditPath = $root.'/'.self::AUDIT;
        if (!hash_equals(self::AUDIT_SHA, self::fileHash($auditPath))) {
            throw new \RuntimeException('M63 reviewed existing-question audit differs.');
        }
        $audit = json_decode(file_get_contents($auditPath), true, flags: JSON_THROW_ON_ERROR);
        \App\Support\M63BuilderSelectionPresentation::validateAudit($audit, $patch, Package::load($root)[3]);
        if ($baseline !== null) {
            $actual = $this->protectedFingerprints([]);
            foreach ($baseline['sql_before'] as $table => $expected) {
                if (!in_array($table, ['pages', 'text_blocks'], true) && ($actual[$table] ?? null) !== $expected) {
                    throw new \RuntimeException('M63 zero-write protected table changed since independent BEFORE: '.$table);
                }
            }
        }
        $plan['question_write_policy'] = ['questions' => 0, 'question_options' => 0, 'question_answers' => 0,
            'question_option_question' => 0, 'question_theory_text_blocks' => 0];
    }

    protected static function policy(): array
    {
        return ['guard' => M63LocalTargetGuard::class, 'served_root' => M63LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS,
            'extra_source_paths' => ['app/Support/ComposeTokenCase.php', 'app/Support/M53BuilderHintPresentation.php',
                'app/Support/M54BuilderTokenPresentation.php', 'app/Support/M55BuilderContextPresentation.php',
                'app/Support/M56BuilderSelectionPresentation.php', 'app/Support/M59BuilderSelectionPresentation.php',
                'app/Support/M61ReportedSpeechChangesPackage.php', 'app/Support/M61BuilderSelectionPresentation.php',
                'app/Services/M61ContentPatch.php', 'app/Services/M61LocalTargetGuard.php',
                'app/Console/Commands/PatchM61Content.php',
                'app/Support/M62ReportedReviewRelativeBasicsPackage.php', 'app/Support/M62BuilderSelectionPresentation.php',
                'app/Services/M62ContentPatch.php', 'app/Services/M62LocalTargetGuard.php',
                'app/Console/Commands/PatchM62Content.php',
                'app/Support/M60ReportedSpeechBasicsPackage.php', 'app/Support/M60BuilderSelectionPresentation.php',
                'app/Services/M60ContentPatch.php', 'app/Services/M60LocalTargetGuard.php',
                'app/Services/M60PracticeFeedbackCorrectionPatch.php',
                'app/Console/Commands/PatchM60Content.php', 'app/Console/Commands/PatchM60PracticeFeedback.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
