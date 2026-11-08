<?php

namespace App\Services;

use App\Support\M54PassivePatternsPackage as Package;

/** M54 data-only policy; unchanged shared native planner and M26 transaction. */
class M54ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'a0fc78b98a12e8af17c56d3f4fde8dccf95031d6de2a5a4c8081afeba15edb64';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '575def9e285f75852af17cb5385027f9903d4596ba5ebf4cabfe8534abdb79f4';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '986c7ee3f73d8748db3a95fbcac4343fe2fa10375eec943ffb7ad72bbb6e13f7';
    public const CONTROLLER_WT_AFTER_SHA = '919d4b894584b528e9d433c9073f67f6cd20b6ce2e30994f65d1cf3e314d3edc';
    public const AUDIT = 'docs/content/m54-passive-patterns-bank-audit.v1.json';
    public const AUDIT_SHA = '2ae1efb300d3fccc719a7a7ddd4865738a33cf60df40720c54fa9c86d1d0034c';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M54PassivePatternsPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M54ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M54LocalTargetGuard.php',
        'app/Console/Commands/PatchM54Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        self::AUDIT,
    ];
    protected const ID = 'm54-passive-patterns-standard-view-v1';

    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard' => M54LocalTargetGuard::class, 'served_root' => M54LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [], 'extra_source_paths' => [],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
