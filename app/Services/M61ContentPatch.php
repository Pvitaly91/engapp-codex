<?php

namespace App\Services;

use App\Support\M61ReportedSpeechChangesPackage as Package;

/** M61 finite content-only policy; unchanged native planner and M26 transaction writer. */
class M61ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'ba414b3857d4a6d430f7037a62a9d50b8fac589be8bea30eaeeeed99df71059d';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '6733ecf88084786905d237462318a84bf7df9c269f84e60b3476ea430f890cc8';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '39e8337aaa20ecb640a6477f15341214556397bfab031a5b7efb0a4a6e11c7c6';
    public const CONTROLLER_WT_AFTER_SHA = 'c9d925e2b7a163e956e4a7296f5204a40b8d49a9ada1846e80cda329b7f81bfb';
    public const AUDIT = 'docs/content/m61-reported-speech-changes-bank-audit.v1.json';
    public const AUDIT_SHA = '17bac6cda7ee8be7d749b98c9ba049378b4e70c8b87e58fed04bb4204a44b21f';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M61ReportedSpeechChangesPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M61ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M61LocalTargetGuard.php',
        'app/Console/Commands/PatchM61Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryContentRenderer.php', 'app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Support/M61BuilderSelectionPresentation.php', self::AUDIT,
        'docs/content/m61-reported-speech-changes.v1.0.0.uk.md',
        'docs/content/m61-reported-speech-changes.editorial.v1.0.0.md',
        'docs/content/m61-reported-speech-changes.components.v1.json',
        'docs/content/m61-reported-speech-changes-checksums.v1.json',
    ];
    protected const ID = 'm61-reported-speech-changes-standard-view-v1';

    /** Twelve exact box type/heading transitions; no level or column writes. */
    protected function allowedUpdateFields(): array { return ['body', 'type', 'heading']; }
    protected static function packageClass(): string { return Package::class; }

    /** Existing curated questions authorize no question/link/taxonomy/progress writes. */
    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void
    {
        $root = dirname($this->databasePath); $auditPath = $root.'/'.self::AUDIT;
        if (!hash_equals(self::AUDIT_SHA, self::fileHash($auditPath))) {
            throw new \RuntimeException('M61 reviewed existing-question audit differs.');
        }
        $audit = json_decode(file_get_contents($auditPath), true, flags: JSON_THROW_ON_ERROR);
        \App\Support\M61BuilderSelectionPresentation::validateAudit($audit, $patch, Package::load($root)[3]);
        if ($baseline !== null) {
            $actual = $this->protectedFingerprints([]);
            foreach ($baseline['sql_before'] as $table => $expected) {
                if (!in_array($table, ['pages', 'text_blocks'], true) && ($actual[$table] ?? null) !== $expected) {
                    throw new \RuntimeException('M61 zero-write protected table changed since independent BEFORE: '.$table);
                }
            }
        }
        $plan['question_write_policy'] = ['questions' => 0, 'question_options' => 0, 'question_answers' => 0,
            'question_option_question' => 0, 'question_theory_text_blocks' => 0];
    }

    protected static function policy(): array
    {
        return ['guard' => M61LocalTargetGuard::class, 'served_root' => M61LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS,
            'extra_source_paths' => ['app/Support/ComposeTokenCase.php', 'app/Support/M53BuilderHintPresentation.php',
                'app/Support/M54BuilderTokenPresentation.php', 'app/Support/M55BuilderContextPresentation.php',
                'app/Support/M56BuilderSelectionPresentation.php', 'app/Support/M59BuilderSelectionPresentation.php',
                'app/Support/M60ReportedSpeechBasicsPackage.php', 'app/Support/M60BuilderSelectionPresentation.php',
                'app/Services/M60ContentPatch.php', 'app/Services/M60LocalTargetGuard.php',
                'app/Services/M60PracticeFeedbackCorrectionPatch.php',
                'app/Console/Commands/PatchM60Content.php', 'app/Console/Commands/PatchM60PracticeFeedback.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
