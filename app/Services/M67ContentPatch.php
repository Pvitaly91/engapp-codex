<?php

namespace App\Services;

use App\Support\M67VerbPatternsCompletion as Package;

/** Body-only M67 policy; existing native planner owns exclusive backup, stale-plan locks and transaction. */
class M67ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '55d8e940ce68287b31cd6522e8377023778092ac265bf5ae92c6adc6661e04b0';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'b1821fcc0a7f09c12e646504b0015747736b4abc370c433817edb7d04ad70e5c';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '0b533601c495af35eb64bcbb36ee47d3a6e59a323d8a96800354fa9bbff01c43';
    public const CONTROLLER_WT_AFTER_SHA = 'f0a9ef016825c8b33feeca1d5bc2eedb366088879a18348e6a87b5f89dfb9b39';
    public const AUDIT = \App\Support\M67BuilderSelectionPresentation::AUDIT;
    public const AUDIT_SHA = \App\Support\M67BuilderSelectionPresentation::AUDIT_SHA;
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Support/M67VerbPatternsCompletion.php',
        'app/Services/M67ContentPatch.php', 'app/Services/M67LocalTargetGuard.php',
        'app/Console/Commands/ContentPatchM67.php', 'app/Support/M67BuilderSelectionPresentation.php',
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',
        self::CONTROLLER_PATH, 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        self::AUDIT, Package::CHECKSUMS,
    ];
    protected const ID = 'm67-verb-patterns-completion-standard-view-v1';

    protected static function packageClass(): string { return Package::class; }

    protected static function policy(): array
    {
        return ['guard' => M67LocalTargetGuard::class, 'served_root' => M67LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [],
            'extra_source_paths' => [
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
