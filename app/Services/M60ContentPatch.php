<?php

namespace App\Services;

use App\Support\M60ReportedSpeechBasicsPackage as Package;

/** M60 finite content-only policy; unchanged M26 locks/backup/transaction writer. */
class M60ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '2cf4f9d9038a66546b90aee3d26e25a1a9b31aa5fd552ea870a70ef2d81f705f';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'b15e05b5eb3471967a755f87ab60ef348b9149271c03c148a44cf63efc95d220';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '6589ba35e88b07d7cad3a4f9186cf77948fa2eb3580945f1c12bca6553657323';
    public const CONTROLLER_WT_AFTER_SHA = '4141665c2d9837647bdca91bde82160826c1e402a48632ee4faa26e7776ed16e';
    public const AUDIT = 'docs/content/m60-reported-speech-basics-bank-audit.v1.0.1.json';
    public const AUDIT_SHA = '310b1ff676bb34b3ac51f21b2cdf74b2313a8d182ef2b600216bec93b81cd0f0';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M60ReportedSpeechBasicsPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M60ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M60LocalTargetGuard.php',
        'app/Console/Commands/PatchM60Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryContentRenderer.php', 'app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php', 'app/Support/M60BuilderSelectionPresentation.php', self::AUDIT,
        'docs/content/m60-reported-speech-basics.v1.0.1.uk.md',
        'docs/content/m60-reported-speech-basics.editorial.v1.0.1.md',
        'docs/content/m60-reported-speech-basics.components.v1.0.1.json',
        'docs/content/m60-reported-speech-basics-checksums.v1.0.1.json',
        'app/Services/M60PracticeFeedbackCorrectionPatch.php', 'app/Console/Commands/PatchM60PracticeFeedback.php',
        'database/content-patches/m60-reported-speech-basics-feedback-correction.v1.0.1.json',
        // Keep the complete superseded 1.0.0 publication immutable and plan-bound.
        'docs/content/m60-reported-speech-basics.v1.0.0.json',
        'database/content-patches/m60-reported-speech-basics.v1.json',
        'docs/content/m60-reported-speech-basics-bank-audit.v1.json',
        'docs/content/m60-reported-speech-basics.v1.0.0.uk.md',
        'docs/content/m60-reported-speech-basics.editorial.v1.0.0.md',
        'docs/content/m60-reported-speech-basics.components.v1.json',
        'docs/content/m60-reported-speech-basics-checksums.v1.json',
    ];
    protected const ID = 'm60-reported-speech-basics-standard-view-v1';

    /** Exact preview changes only reviewed bodies and twelve box type/heading pairs; all levels/columns stay exact. */
    protected function allowedUpdateFields(): array { return ['body', 'type', 'heading']; }
    protected static function packageClass(): string { return Package::class; }

    /** Reused banks authorize no question/link/taxonomy/progress writes, before or after apply. */
    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void
    {
        $auditPath = dirname($this->databasePath).'/'.self::AUDIT;
        if (!hash_equals(self::AUDIT_SHA, self::fileHash($auditPath))) { throw new \RuntimeException('M60 reviewed reuse audit differs.'); }
        $audit = json_decode(file_get_contents($auditPath), true, flags: JSON_THROW_ON_ERROR);
        \App\Support\M60BuilderSelectionPresentation::validateAudit($audit, $patch, Package::load(dirname($this->databasePath))[3]);
        if ($baseline !== null) {
            $actual = $this->protectedFingerprints([]);
            foreach ($baseline['sql_before'] as $table => $expected) {
                if (!in_array($table, ['pages', 'text_blocks'], true) && ($actual[$table] ?? null) !== $expected) {
                    throw new \RuntimeException('M60 zero-write protected table changed since independent BEFORE: '.$table);
                }
            }
        }
        $plan['question_write_policy'] = ['questions' => 0, 'question_options' => 0, 'question_answers' => 0,
            'question_option_question' => 0, 'question_theory_text_blocks' => 0];
    }
    protected static function policy(): array
    {
        return ['guard' => M60LocalTargetGuard::class, 'served_root' => M60LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS,
            'extra_source_paths' => ['app/Support/ComposeTokenCase.php', 'app/Support/M53BuilderHintPresentation.php',
                'app/Support/M54BuilderTokenPresentation.php', 'app/Support/M55BuilderContextPresentation.php',
                'app/Support/M56BuilderSelectionPresentation.php', 'app/Support/M59BuilderSelectionPresentation.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
