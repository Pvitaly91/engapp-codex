<?php

namespace App\Services;

use App\Support\M76PronounRelations as Package;
use RuntimeException;

/** Closed continuing-row migration; no question, relation, level or timestamp writes. */
class M76ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'b0cae6f629aae1b4354aefcf8d704b73030ef2022846c2cd1ea2f9f5d3a05985';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'b00cc9b447c028f747460e33161a02abf01356aef5fab5e5535f0007e3b2917a';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = 'c9d41a3f0b49e04a3eb33d060fe31bb6b0f6b00a6de67b06bb98fd08ceeb62ec';
    public const CONTROLLER_WT_AFTER_SHA = '5e31de0305ea1a2dc05d04e8553d08dd966a0451d1a46626554fdcd6beea11de';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php','app/Support/M76PronounRelations.php',
        'app/Support/M76BuilderSelectionPresentation.php','app/Services/M76ContentPatch.php',
        'app/Services/M76LocalTargetGuard.php','app/Console/Commands/ContentPatchM76.php',
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        Package::CHECKSUMS,\App\Support\M76BuilderSelectionPresentation::AUDIT,
    ];
    protected const ID = 'm76-relative-each-reciprocal-standard-view-v1';
    protected static function packageClass():string { return Package::class; }
    protected function allowedUpdateFields():array { return ['body','type','heading']; }
    protected static function policy():array
    {
        return ['guard'=>M76LocalTargetGuard::class,'served_root'=>M76LocalTargetGuard::ROOT,
            'source_worktree'=>self::SOURCE_WORKTREE,'before_file'=>self::BEFORE_FILE,'before_sha'=>self::BEFORE_SHA,
            'consumers_file'=>self::CONSUMERS_FILE,'consumers_sha'=>self::CONSUMERS_SHA,'sync_receipt'=>self::SYNC_RECEIPT,
            'sync_code_paths'=>self::SYNC_CODE_PATHS,'bank_source_overrides'=>[],
            'extra_source_paths'=>['app/Support/M75PronounForms.php','app/Support/M75BuilderSelectionPresentation.php',
                'app/Support/M75QuestionSupplement.php','app/Services/M75QuestionInsertScope.php',
                'app/Support/ComposeTokenCase.php','app/Support/M51QuestionSupplement.php'],
            'controller_path'=>self::CONTROLLER_PATH,'controller_root_after_sha'=>self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha'=>self::CONTROLLER_WT_AFTER_SHA];
    }
    protected function assertExistingPools(array $questions,array $contract,?array $baseline):void
    {
        if(!in_array($contract['identity']??null,Package::OWNERS,true)||!array_key_exists('level',$contract)||$contract['level']!==null){
            throw new RuntimeException('M76 declared owner/actual NULL practice level differs.');
        }
        $ids=[];
        foreach($contract['bank_seeders'] as $bank){
            $filter=static fn($q)=>$q['seeder']===$bank&&(string)$q['type']==='4';
            $eligible=array_values(array_filter($questions,$filter));
            usort($eligible,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
            if($baseline!==null){
                $expected=array_values(array_filter($baseline['relations']['questions'],$filter));
                usort($expected,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
                if($eligible!==$expected){throw new RuntimeException('M76 complete original type4 pool changed.');}
            }
            array_push($ids,...array_column($eligible,'id'));
        }
        if(count($ids)<5||count(array_unique($ids))!==count($ids)){throw new RuntimeException('M76 thematic bank is short or repeats IDs.');}
    }
}
