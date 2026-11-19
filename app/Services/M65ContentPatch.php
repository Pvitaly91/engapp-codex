<?php

namespace App\Services;

use App\Support\M65PurposeGerundInfinitivePackage as Package;

/** Finite M65 policy. Existing native planner and M26 backup/lock/transaction writer are unchanged. */
class M65ContentPatch extends TheoryNativeContentPatch
{
    use M65QuestionInsertScope;
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'ae650493911b7496ddc79300f47d2bffc6cfd1ec1b9f97a3e707046ed8eb07ce';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '95318c79297facaf3b0ba82362335efb8766eb5dc3dd389ac43f9139c7bedeeb';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '7e301caeae7c74f50b3f6dfdab3aace029e7e8867b8bb15e1d7f2ab276820e19';
    public const CONTROLLER_WT_AFTER_SHA = 'b0a5a89a8d9ece5c84ca49f8686fb4725bc71faaff4980b72c95ce9c54cf2f9c';
    public const AUDIT = \App\Support\M65BuilderSelectionPresentation::AUDIT;
    public const AUDIT_SHA = \App\Support\M65BuilderSelectionPresentation::AUDIT_SHA;
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Support/M65PurposeGerundInfinitivePackage.php',
        'app/Services/M65ContentPatch.php', 'app/Services/M65QuestionInsertScope.php',
        'app/Services/M65LocalTargetGuard.php', 'app/Console/Commands/PatchM65Content.php',
        'app/Support/M65QuestionSupplement.php', 'app/Support/M65BuilderSelectionPresentation.php',
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Services/TheoryCourseManifestService.php',
        self::CONTROLLER_PATH, 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        self::AUDIT, 'docs/content/m65-purpose-question-supplement.v1.0.0.json',
        'docs/content/m65-purpose-gerund-infinitive.v1.0.0.uk.md',
        'docs/content/m65-purpose-gerund-infinitive.editorial.v1.0.0.md',
        'docs/content/m65-purpose-gerund-infinitive.components.v1.json',
        'docs/content/m65-purpose-gerund-infinitive-checksums.v1.json',
        'database/seeders/V3/Polyglot/M65PurposeB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M65PurposeB1SupplementSeeder/M65PurposeB1SupplementSeeder.php',
    ];
    protected const ID = 'm65-purpose-gerund-infinitive-standard-view-v1';

    /** Five Purpose type/heading transitions only; A2 native owners have none. No level/column writes. */
    protected function allowedUpdateFields(): array { return ['body', 'type', 'heading']; }
    protected static function packageClass(): string { return Package::class; }

    public function apply(string $planPath, string $backupPath, ?string $expectedDatabase = null): array
    {
        $projection=$this->questionEvidence()['projection'];
        $result=parent::apply($planPath,$backupPath,$expectedDatabase);$counts=[];
        foreach (\App\Support\M51QuestionSupplement::ROW_COLUMNS as $table=>$_) {
            $counts[$table]=$result['status']==='applied'?count($projection[$table]):0;
        }
        return $result+['question_families_inserted'=>$counts];
    }

    protected static function policy(): array
    {
        return ['guard'=>M65LocalTargetGuard::class,'served_root'=>M65LocalTargetGuard::ROOT,
            'source_worktree'=>self::SOURCE_WORKTREE,'before_file'=>self::BEFORE_FILE,'before_sha'=>self::BEFORE_SHA,
            'consumers_file'=>self::CONSUMERS_FILE,'consumers_sha'=>self::CONSUMERS_SHA,'sync_receipt'=>self::SYNC_RECEIPT,
            'sync_code_paths'=>self::SYNC_CODE_PATHS,'bank_source_overrides'=>[],
            'extra_source_paths'=>['app/Support/M51QuestionSupplement.php','app/Support/ComposeTokenCase.php',
                'app/Support/M64ReasonResultContrastPackage.php','app/Support/M64BuilderSelectionPresentation.php',
                'app/Support/M64QuestionSupplement.php','app/Services/M64ContentPatch.php','app/Services/M64QuestionInsertScope.php',
                'app/Services/M64LocalTargetGuard.php','app/Console/Commands/PatchM64Content.php',
                'app/Support/M63RelativeCompletionLinkingPackage.php','app/Support/M63BuilderSelectionPresentation.php'],
            'controller_path'=>self::CONTROLLER_PATH,'controller_root_after_sha'=>self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha'=>self::CONTROLLER_WT_AFTER_SHA];
    }
}
