<?php

namespace App\Services;

use App\Support\M66VerbPatternsCorePackage as Package;

/** Body-only native M66 policy; inherited exclusive backup, stale-plan lock and transaction. */
class M66ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '517118d0fe7cf764229b5926e82fa7044b10a92535923a7301bc46602311d6a3';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '1cafef083ae1d25457be74188a3590b847265d256d18a4d4433de167b465be40';
    public const SYNC_RECEIPT = 'source-sync-v3.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '22f12d2756503c50df3064cb2c9e6187d9beb35859b3469a9f122ce7d4ea7e3e';
    public const CONTROLLER_WT_AFTER_SHA = '03db15b6d53381df1801c08ee380d29714ec3bb7bfab49484f3f38b47d4483c6';
    public const AUDIT = \App\Support\M66BuilderSelectionPresentation::AUDIT;
    public const AUDIT_SHA = \App\Support\M66BuilderSelectionPresentation::AUDIT_SHA;
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Support/M66VerbPatternsCorePackage.php',
        'app/Services/M66ContentPatch.php', 'app/Services/M66LocalTargetGuard.php',
        'app/Console/Commands/PatchM66Content.php', 'app/Support/M66BuilderSelectionPresentation.php',
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',
        self::CONTROLLER_PATH, 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        self::AUDIT, 'docs/content/m66-verb-patterns-core.v1.0.2.uk.md',
        'docs/content/m66-verb-patterns-core.editorial.v1.0.0.md',
        'docs/content/m66-verb-patterns-core.components.v3.json',
        'docs/content/m66-verb-patterns-core-checksums.v3.json',
        'docs/content/m66-verb-patterns-core.revision-1.0.2.md',
        'app/Services/M66FormulaCorrectionPatch.php',
        'docs/content/m66-verb-patterns-core.v1.0.1.json',
        'docs/content/m66-verb-patterns-core.v1.0.1.uk.md',
        'database/content-patches/m66-verb-patterns-core.v2.json',
        'docs/content/m66-verb-patterns-core.components.v2.json',
        'docs/content/m66-verb-patterns-core-checksums.v2.json',
        'docs/content/m66-verb-patterns-core.revision-1.0.1.md',
        'docs/content/m66-verb-patterns-core.v1.0.0.json',
        'docs/content/m66-verb-patterns-core.v1.0.0.uk.md',
        'database/content-patches/m66-verb-patterns-core.v1.json',
        'docs/content/m66-verb-patterns-core-bank-audit.v1.json',
        'docs/content/m66-verb-patterns-core.components.v1.json',
        'docs/content/m66-verb-patterns-core-checksums.v1.json',
    ];
    protected const ID = 'm66-verb-patterns-core-standard-view-v3';
    protected static function packageClass(): string { return Package::class; }

    protected static function policy(): array
    {
        return ['guard'=>M66LocalTargetGuard::class,'served_root'=>M66LocalTargetGuard::ROOT,
            'source_worktree'=>self::SOURCE_WORKTREE,'before_file'=>self::BEFORE_FILE,'before_sha'=>self::BEFORE_SHA,
            'consumers_file'=>self::CONSUMERS_FILE,'consumers_sha'=>self::CONSUMERS_SHA,'sync_receipt'=>self::SYNC_RECEIPT,
            'sync_code_paths'=>self::SYNC_CODE_PATHS,'bank_source_overrides'=>[],
            'extra_source_paths'=>['app/Support/M51QuestionSupplement.php','app/Support/ComposeTokenCase.php',
                'app/Support/M65PurposeGerundInfinitivePackage.php','app/Support/M65BuilderSelectionPresentation.php',
                'app/Support/M65QuestionSupplement.php','app/Services/M65ContentPatch.php',
                'app/Services/M65QuestionInsertScope.php','app/Services/M65LocalTargetGuard.php',
                'app/Console/Commands/PatchM65Content.php',
                'app/Support/M64ReasonResultContrastPackage.php','app/Support/M64BuilderSelectionPresentation.php'],
            'controller_path'=>self::CONTROLLER_PATH,'controller_root_after_sha'=>self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha'=>self::CONTROLLER_WT_AFTER_SHA];
    }
}
