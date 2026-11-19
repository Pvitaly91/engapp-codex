<?php

namespace App\Services;

use App\Support\M68ComparisonBasics as Package;

/** Body-only M68 policy; existing native planner owns exclusive backup, stale-plan locks and transaction. */
class M68ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'e23f859491c59bcb182dc22a73b69b2b8bad14fb85b565e3bd8fd6cbcc1dac46';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'ac3ff01b09dab2655806e868411f816e69eb6452667632b40c73d3d9d3c9db9b';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = 'c497413f22be4a327e386c95d52a68a3d0b9c8a34e1112c1fe3a5f9bf95cbc70';
    public const CONTROLLER_WT_AFTER_SHA = 'a8a3964cb38d72c6e0075b16c77b3355f039a361881854fd68489b74f23caabb';
    public const AUDIT = \App\Support\M68BuilderSelectionPresentation::AUDIT;
    public const AUDIT_SHA = \App\Support\M68BuilderSelectionPresentation::AUDIT_SHA;
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Support/M68ComparisonBasics.php',
        'app/Services/M68ContentPatch.php', 'app/Services/M68LocalTargetGuard.php',
        'app/Console/Commands/ContentPatchM68.php', 'app/Support/M68BuilderSelectionPresentation.php',
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',
        self::CONTROLLER_PATH, 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        self::AUDIT, Package::CHECKSUMS,
    ];
    protected const ID = 'm68-comparison-basics-standard-view-v1';

    protected static function packageClass(): string { return Package::class; }

    protected static function policy(): array
    {
        return ['guard' => M68LocalTargetGuard::class, 'served_root' => M68LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [],
            'extra_source_paths' => [
                'app/Support/M67VerbPatternsCompletion.php', 'app/Support/M67BuilderSelectionPresentation.php',
                'app/Services/M67ContentPatch.php', 'app/Services/M67LocalTargetGuard.php',
                'app/Console/Commands/ContentPatchM67.php',
                'app/Support/M51QuestionSupplement.php', 'app/Support/ComposeTokenCase.php',
                'app/Support/M66VerbPatternsCorePackage.php', 'app/Support/M66BuilderSelectionPresentation.php',
                'app/Services/M66ContentPatch.php', 'app/Services/M66FormulaCorrectionPatch.php',
                'app/Services/M66LocalTargetGuard.php', 'app/Console/Commands/PatchM66Content.php',
                'app/Support/M65PurposeGerundInfinitivePackage.php', 'app/Support/M65BuilderSelectionPresentation.php',
                'app/Support/M65QuestionSupplement.php', 'app/Services/M65ContentPatch.php',
                'app/Services/M65QuestionInsertScope.php', 'app/Services/M65LocalTargetGuard.php',
                'app/Console/Commands/PatchM65Content.php',
                'app/Support/M64ReasonResultContrastPackage.php', 'app/Support/M64BuilderSelectionPresentation.php',
            ],
            'controller_path' => self::CONTROLLER_PATH,
            'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
