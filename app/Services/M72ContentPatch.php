<?php

namespace App\Services;

use App\Support\M72PartitivesNegativesGeography as Package;
use RuntimeException;

/** Three continuing practices; no inserts or metadata transitions. No question-family writes. */
class M72ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '33cd475c775288da255c3584aa3b9ef665255a1f31abc14f4df294ef749a690d';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '1352febf64e6e8220da1c6e7bfe29c7920d0b53275fe44b697b537c0f24661de';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '082543611f02517c918aa90705eea8d00bf34294ed6750dfd2f13df537f3d26b';
    public const CONTROLLER_WT_AFTER_SHA = '082543611f02517c918aa90705eea8d00bf34294ed6750dfd2f13df537f3d26b';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php','app/Services/TheoryNativeContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php','app/Support/TheoryBuilderSelectionPresentation.php',
        'app/Support/M72PartitivesNegativesGeography.php','app/Support/M72BuilderSelectionPresentation.php',
        'app/Services/M72ContentPatch.php','app/Services/M72LocalTargetGuard.php','app/Console/Commands/ContentPatchM72.php',
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'docs/content/m72-partitives-negatives-geography-bank-audit.v1.json',Package::CHECKSUMS,
        \App\Support\M72BuilderSelectionPresentation::AUDIT,\App\Support\M72BuilderSelectionPresentation::REVISION,
    ];
    protected const ID = 'm72-partitives-negatives-geography-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard'=>M72LocalTargetGuard::class,'served_root'=>M72LocalTargetGuard::ROOT,
            'source_worktree'=>self::SOURCE_WORKTREE,'before_file'=>self::BEFORE_FILE,'before_sha'=>self::BEFORE_SHA,
            'consumers_file'=>self::CONSUMERS_FILE,'consumers_sha'=>self::CONSUMERS_SHA,'sync_receipt'=>self::SYNC_RECEIPT,
            'sync_code_paths'=>self::SYNC_CODE_PATHS,'bank_source_overrides'=>[],
            'extra_source_paths'=>['app/Support/M69AdjectivesAdverbs.php','app/Support/M69BuilderSelectionPresentation.php',
                'app/Services/M69ContentPatch.php','app/Services/M69SubtitleCorrectionPatch.php','app/Support/ComposeTokenCase.php',
                'app/Support/M51QuestionSupplement.php'],
            'controller_path'=>self::CONTROLLER_PATH,'controller_root_after_sha'=>self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha'=>self::CONTROLLER_WT_AFTER_SHA];
    }

    protected function assertExistingPools(array $questions,array $contract,?array $baseline):void
    {
        $ids=[];
        foreach($contract['bank_seeders'] as $bank){
            $filter=static fn($q)=>$q['seeder']===$bank&&(string)$q['type']==='4'
                &&($contract['level']===null||$q['level']===$contract['level']);
            $eligible=array_values(array_filter($questions,$filter));
            usort($eligible,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
            if($baseline!==null){
                $expected=array_values(array_filter($baseline['relations']['questions'],$filter));
                usort($expected,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
                if($eligible!==$expected){throw new RuntimeException('M72 original page-wide bank/type/actual CEFR pool drifted.');}
            }
            array_push($ids,...array_column($eligible,'id'));
        }
        if(count($ids)<5||count(array_unique($ids))!==count($ids)){
            throw new RuntimeException('M72 exact thematic pool is short or repeats question IDs.');
        }
    }
}
