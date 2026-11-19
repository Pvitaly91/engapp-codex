<?php

namespace App\Services;

use App\Support\M71ArticlesQuantifiers as Package;
use RuntimeException;

/** Three continuing practices; no inserts or metadata transitions. No question-family writes. */
class M71ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '8644354b102cbe4c8756e13c6af76472f753c10185d122849a0bf95e0279bd35';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '472ac3bea0da96f202d914e358eff24ba9278643418e3db635ec22a699f431d5';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '8469f1e9407c193f00e77c9a2a52fc29ac602abaa18822325b45dfd99875f373';
    public const CONTROLLER_WT_AFTER_SHA = '8469f1e9407c193f00e77c9a2a52fc29ac602abaa18822325b45dfd99875f373';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php','app/Services/TheoryNativeContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php','app/Support/TheoryBuilderSelectionPresentation.php',
        'app/Support/M71ArticlesQuantifiers.php','app/Support/M71BuilderSelectionPresentation.php',
        'app/Services/M71ContentPatch.php','app/Services/M71LocalTargetGuard.php','app/Console/Commands/ContentPatchM71.php',
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'docs/content/m71-articles-quantifiers-bank-audit.v1.json',Package::CHECKSUMS,
    ];
    protected const ID = 'm71-articles-quantifiers-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard'=>M71LocalTargetGuard::class,'served_root'=>M71LocalTargetGuard::ROOT,
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
                if($eligible!==$expected){throw new RuntimeException('M71 original page-wide bank/type/actual CEFR pool drifted.');}
            }
            array_push($ids,...array_column($eligible,'id'));
        }
        if(count($ids)<5||count(array_unique($ids))!==count($ids)){
            throw new RuntimeException('M71 exact thematic pool is short or repeats question IDs.');
        }
    }
}
