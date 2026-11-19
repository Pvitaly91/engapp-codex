<?php

namespace App\Services;

use App\Support\M59PassiveFutureAgentReviewPackage as Package;

/** M59 finite content-only policy; unchanged M26 locks/backup/transaction writer. */
class M59ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '27fbf37afc8efb9152b0f0a90d73dfab7b2114f72a15a9ceaf4c336b678d036d';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '707421b8ce3413a773dd24d329774e63b2a46579095db83b46410173a1871b3e';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '63fd4dd28d22e2416d6bccd2ca49f8d479add79b28a0180b2ac6c333e084f016';
    public const CONTROLLER_WT_AFTER_SHA = '4a0c9d040aab2ba22a5fea79770f48f3429b8d2ac26c589a14ac640b3dbc6339';
    public const AUDIT = 'docs/content/m59-passive-future-agent-review-bank-audit.v1.json';
    public const AUDIT_SHA = 'ee6404dc7c61db91b0ec544135c9f2df60d2f04eadc2d6fd6a01607a7940622a';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M59PassiveFutureAgentReviewPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M59ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M59LocalTargetGuard.php',
        'app/Console/Commands/PatchM59Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryContentRenderer.php', 'app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php', 'app/Support/M59BuilderSelectionPresentation.php', self::AUDIT,
    ];
    protected const ID = 'm59-passive-future-agent-review-standard-view-v1';

    /** Exact preview carries unchanged metadata on ordinary rows; only Advanced slot1 differs. */
    protected function allowedUpdateFields(): array { return ['body', 'type', 'heading', 'level']; }
    protected static function packageClass(): string { return Package::class; }

    /** Reused banks authorize no question/link/taxonomy/progress writes, before or after apply. */
    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void
    {
        $auditPath = dirname($this->databasePath).'/'.self::AUDIT;
        if (!hash_equals(self::AUDIT_SHA, self::fileHash($auditPath))) { throw new \RuntimeException('M59 reviewed reuse audit differs.'); }
        $audit = json_decode(file_get_contents($auditPath), true, flags: JSON_THROW_ON_ERROR);
        \App\Support\M59BuilderSelectionPresentation::validateAudit($audit, $patch, Package::load(dirname($this->databasePath))[3]);
        if ($baseline !== null) {
            $actual = $this->protectedFingerprints([]);
            foreach ($baseline['sql_before'] as $table => $expected) {
                if (!in_array($table, ['pages', 'text_blocks'], true) && ($actual[$table] ?? null) !== $expected) {
                    throw new \RuntimeException('M59 zero-write protected table changed since independent BEFORE: '.$table);
                }
            }
        }
        $plan['question_write_policy'] = ['questions' => 0, 'question_options' => 0, 'question_answers' => 0,
            'question_option_question' => 0, 'question_theory_text_blocks' => 0];
    }
    protected static function policy(): array
    {
        return ['guard' => M59LocalTargetGuard::class, 'served_root' => M59LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS,
            'extra_source_paths' => ['app/Support/ComposeTokenCase.php', 'app/Support/M53BuilderHintPresentation.php',
                'app/Support/M54BuilderTokenPresentation.php', 'app/Support/M55BuilderContextPresentation.php',
                'app/Support/M56BuilderSelectionPresentation.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
