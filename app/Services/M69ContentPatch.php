<?php

namespace App\Services;

use App\Support\M69AdjectivesAdverbs as Package;

/** Exact M69 content policy. The accepted native planner/writer owns the transaction. */
class M69ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v2.json';
    public const BEFORE_SHA = '3fbcc654435119c5607d0ede041bead091bce4527efadde6a2b1bef661de4d62';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'b1987af31f34444072ce6011b4d045f511de05b1e740126e6260b40ad7c7fc95';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '65f1f402b4de9f9f1fbfa899f6a22f7ed194db25ab12a0f6d2b1b332368e9824';
    public const CONTROLLER_WT_AFTER_SHA = '5f9ff824f9c780abb7fe57946ff9c9cb4a413c7eb58885a5c2af41104ac3b1cb';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Support/TheoryBuilderSelectionPresentation.php',
        'app/Support/M69AdjectivesAdverbs.php', 'app/Services/M69ContentPatch.php',
        'app/Services/M69SubtitleCorrectionPatch.php',
        'app/Services/M69LocalTargetGuard.php', 'app/Console/Commands/ContentPatchM69.php',
        'app/Support/M69BuilderSelectionPresentation.php',
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',
        self::CONTROLLER_PATH, 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'docs/content/m69-adjectives-adverbs-bank-audit.v1.json', Package::CHECKSUMS,
    ];
    protected const ID = 'm69-adjectives-adverbs-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard'=>M69LocalTargetGuard::class, 'served_root'=>M69LocalTargetGuard::ROOT,
            'source_worktree'=>self::SOURCE_WORKTREE, 'before_file'=>self::BEFORE_FILE, 'before_sha'=>self::BEFORE_SHA,
            'consumers_file'=>self::CONSUMERS_FILE, 'consumers_sha'=>self::CONSUMERS_SHA,
            'sync_receipt'=>self::SYNC_RECEIPT, 'sync_code_paths'=>self::SYNC_CODE_PATHS, 'bank_source_overrides'=>[],
            'extra_source_paths'=>['app/Support/M68ComparisonBasics.php', 'app/Support/M68BuilderSelectionPresentation.php',
                'app/Services/M68ContentPatch.php', 'app/Services/M68LocalTargetGuard.php',
                'app/Console/Commands/ContentPatchM68.php', 'app/Support/M67VerbPatternsCompletion.php',
                'app/Support/M67BuilderSelectionPresentation.php', 'app/Support/M66VerbPatternsCorePackage.php',
                'app/Support/M66BuilderSelectionPresentation.php', 'app/Support/M51QuestionSupplement.php', 'app/Support/ComposeTokenCase.php'],
            'controller_path'=>self::CONTROLLER_PATH, 'controller_root_after_sha'=>self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha'=>self::CONTROLLER_WT_AFTER_SHA];
    }
}
