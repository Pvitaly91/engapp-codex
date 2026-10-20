<?php

namespace App\Services;

use App\Support\M70IntensifiersNounBasics as Package;
use RuntimeException;

/** One new practice; two continuing practices. No question-family writes. */
class M70ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '5c2a7785156041dc8cc6e4dc26f8776b939ec59226119898fb9f0d1376d2f3b8';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '7a51fcd56f2eaf2b55387f311b59d652f3f4419d7c2a062f6bb6a307ac4a6266';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = 'dea7d8e7d65f2b2a1dd9f9d38e3e05eaf74a27e2194ff098847cca1c159f69e5';
    public const CONTROLLER_WT_AFTER_SHA = 'dea7d8e7d65f2b2a1dd9f9d38e3e05eaf74a27e2194ff098847cca1c159f69e5';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php','app/Services/TheoryNativeContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php','app/Support/TheoryBuilderSelectionPresentation.php',
        'app/Support/M70IntensifiersNounBasics.php','app/Support/M70BuilderSelectionPresentation.php',
        'app/Services/M70ContentPatch.php','app/Services/M70LocalTargetGuard.php','app/Console/Commands/ContentPatchM70.php',
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'docs/content/m70-intensifiers-noun-basics-bank-audit.v1.json',Package::CHECKSUMS,
    ];
    protected const ID = 'm70-intensifiers-noun-basics-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard'=>M70LocalTargetGuard::class,'served_root'=>M70LocalTargetGuard::ROOT,
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
                if($eligible!==$expected){throw new RuntimeException('M70 original page-wide bank/type/actual CEFR pool drifted.');}
            }
            array_push($ids,...array_column($eligible,'id'));
        }
        if(count($ids)<5||count(array_unique($ids))!==count($ids)){
            throw new RuntimeException('M70 exact thematic pool is short or repeats question IDs.');
        }
    }
}
