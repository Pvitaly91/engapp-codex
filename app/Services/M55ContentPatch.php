<?php

namespace App\Services;

use App\Support\M55PassiveReviewInfinitivePackage as Package;

/** M55 data-only policy; unchanged shared native planner and M26 transaction. */
class M55ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '7c9adc7e77bd37092cf7dcad7eb62516b260b6d76a8ea1609f2741c42a1a33be';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'b65b3cfa7af97a8f7c8c4487e04eae1cdfa5380a808b306629e991e8d7315449';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '9dc4e129cc4138ba85ba7b1bfa097a09a13d1d2f69a09ea8d75118ed2dd3a5f6';
    public const CONTROLLER_WT_AFTER_SHA = 'e41bd993e43c95aa931638ed3914860caede8a15fa939152e757a77cddf23c7a';
    public const AUDIT = 'docs/content/m55-passive-review-infinitive-bank-audit.v1.json';
    public const AUDIT_SHA = '861d36dc270f4a016951e57c42255b59b97606bb87229f22b97765394f68a848';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M55PassiveReviewInfinitivePackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M55ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M55LocalTargetGuard.php',
        'app/Console/Commands/PatchM55Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php',
        'app/Support/M55BuilderContextPresentation.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        self::AUDIT,
    ];
    protected const ID = 'm55-passive-review-infinitive-standard-view-v1';

    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard' => M55LocalTargetGuard::class, 'served_root' => M55LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [], 'extra_source_paths' => ['app/Support/M53BuilderHintPresentation.php', 'app/Support/M54BuilderTokenPresentation.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
