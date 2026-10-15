<?php

namespace App\Services;

use App\Support\M56GerundSimplePassivesPackage as Package;

/** M56 data-only policy; unchanged shared native planner and M26 transaction. */
class M56ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '8198c1bcff87fbf270ab5d71a7d6c3343b728846ce9cc7be59e1ff44e5bee406';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '8f6d8b9b415a28229eef8b11ad9029867f84beaeec636ab1c65cd3e6609b1c78';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '9f17539f795107553a5d4b6994ea8acf9bff58d5ddea22eeff08e170bdd7a3cc';
    public const CONTROLLER_WT_AFTER_SHA = '36f02df67681d0262c039108e32fee64110c0acccdd03702cf61a30c401ae43a';
    public const AUDIT = 'docs/content/m56-gerund-simple-passives-bank-audit.v1.json';
    public const AUDIT_SHA = 'ee7caf9cc71f872d1a2cf5a97e3f80488016b67b3ba9ff68931873a625c34faf';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M56GerundSimplePassivesPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M56ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M56LocalTargetGuard.php',
        'app/Console/Commands/PatchM56Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php',
        'app/Support/M56BuilderSelectionPresentation.php',
        'app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        self::AUDIT,
        'app/Services/M56HrefCorrectionPatch.php', 'app/Console/Commands/PatchM56Hrefs.php',
        'tools/diagnostics/m56-href-postconditions.php',
        'database/content-patches/m56-gerund-simple-passives-correction-before.v1.0.1.json',
        'database/content-patches/m56-gerund-simple-passives-correction-delta.v1.0.1.json',
        'docs/content/m56-gerund-simple-passives.v1.0.0.json',
        'database/content-patches/m56-gerund-simple-passives.v1.json',
    ];
    protected const ID = 'm56-gerund-simple-passives-standard-view-v1';

    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard' => M56LocalTargetGuard::class, 'served_root' => M56LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [],
            'extra_source_paths' => ['app/Support/M53BuilderHintPresentation.php', 'app/Support/M54BuilderTokenPresentation.php',
                'app/Support/M55BuilderContextPresentation.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
