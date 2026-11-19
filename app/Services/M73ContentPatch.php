<?php

namespace App\Services;

use App\Support\M73GenericIrregularCollective as Package;
use RuntimeException;

/** One continuing practice and two appended native practices; no question-family writes. */
class M73ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '41cb3ac163738e4d2662b4c53a3b2288de3303161d2da6ef5a1f42a9632d0ac9';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'e48d6886d85dab0bc5c96ebf8b9b8b7723ff626f2df08ab1609711bde56cda3c';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '79ff053c79076ac10781397d8de2b75f01e054de0850f7906770e50e7528ba82';
    public const CONTROLLER_WT_AFTER_SHA = '79ff053c79076ac10781397d8de2b75f01e054de0850f7906770e50e7528ba82';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php','app/Services/TheoryNativeContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php','app/Support/TheoryBuilderSelectionPresentation.php',
        'app/Support/M73GenericIrregularCollective.php','app/Support/M73BuilderSelectionPresentation.php',
        'app/Services/M73ContentPatch.php','app/Services/M73LocalTargetGuard.php','app/Console/Commands/ContentPatchM73.php',
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        Package::CHECKSUMS,\App\Support\M73BuilderSelectionPresentation::AUDIT,
        'app/Support/M72PartitivesNegativesGeography.php','app/Support/M72BuilderSelectionPresentation.php',
        \App\Support\M72PartitivesNegativesGeography::CHECKSUMS,\App\Support\M72BuilderSelectionPresentation::AUDIT,
        \App\Support\M72BuilderSelectionPresentation::REVISION,
    ];
    protected const ID = 'm73-generic-irregular-collective-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard'=>M73LocalTargetGuard::class,'served_root'=>M73LocalTargetGuard::ROOT,
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
        $index=array_search($contract['identity']??null,Package::OWNERS,true);
        if($index===false||!array_key_exists('level',$contract)||$contract['level']!==Package::LEVELS[$index]){
            throw new RuntimeException('M73 exact declared practice owner/level differs.');
        }
        $ids=[];
        foreach($contract['bank_seeders'] as $bank){
            // Generic NULL means no exact CEFR filter; A2/B1 remain exact filters.
            // Display ranges are never substituted for actual SQL level equality.
            $filter=static fn($q)=>$q['seeder']===$bank&&(string)$q['type']==='4'
                &&($contract['level']===null||$q['level']===$contract['level']);
            $eligible=array_values(array_filter($questions,$filter));
            usort($eligible,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
            if($baseline!==null){
                $expected=array_values(array_filter($baseline['relations']['questions'],$filter));
                usort($expected,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
                if($eligible!==$expected){throw new RuntimeException('M73 original page-wide bank/type/actual CEFR pool drifted.');}
            }
            array_push($ids,...array_column($eligible,'id'));
        }
        if(count($ids)<5||count(array_unique($ids))!==count($ids)){
            throw new RuntimeException('M73 exact thematic pool is short or repeats question IDs.');
        }
    }
}
