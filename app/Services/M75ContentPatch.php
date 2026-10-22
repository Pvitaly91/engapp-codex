<?php

namespace App\Services;

use App\Support\M75PronounForms as Package;
use App\Support\M75QuestionSupplement as Supplement;
use RuntimeException;

/** Three continuing practices and one finite insert-only thematic bank exception. */
class M75ContentPatch extends TheoryNativeContentPatch
{
    use M75QuestionInsertScope;

    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '5e238ff98020caf4ce97a3057c7a624bd48898d64791e83a720e3fe792b09c4a';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'f59c52af9be8d73446bc3310ff1e435cc4b93de34f7976f488a280f20ad35090';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '08ef36c7d2bcd70a14a48c5d86fdb3b0f3682cf4d780ba0b5d482a0fbe27048b';
    public const CONTROLLER_WT_AFTER_SHA = '60706ea081db30c2c977f391dc416186454660b18d915ebe5f7b6f63bd074e19';
    public const SYNC_CODE_PATHS = [
        'app/Support/M75PronounForms.php','app/Support/M75BuilderSelectionPresentation.php',
        'app/Support/M75QuestionSupplement.php','app/Services/M75QuestionInsertScope.php',
        'app/Services/M75ContentPatch.php','app/Services/M75LocalTargetGuard.php','app/Console/Commands/ContentPatchM75.php',
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        Package::CHECKSUMS,\App\Support\M75BuilderSelectionPresentation::AUDIT,Supplement::SOURCE,
    ];
    protected const ID = 'm75-possessive-reflexive-indefinite-standard-view-v1';
    protected static function packageClass():string { return Package::class; }
    protected static function policy():array
    {
        return ['guard'=>M75LocalTargetGuard::class,'served_root'=>M75LocalTargetGuard::ROOT,
            'source_worktree'=>self::SOURCE_WORKTREE,'before_file'=>self::BEFORE_FILE,'before_sha'=>self::BEFORE_SHA,
            'consumers_file'=>self::CONSUMERS_FILE,'consumers_sha'=>self::CONSUMERS_SHA,'sync_receipt'=>self::SYNC_RECEIPT,
            'sync_code_paths'=>self::SYNC_CODE_PATHS,'bank_source_overrides'=>[Supplement::BANK=>Supplement::SOURCE],
            'extra_source_paths'=>['app/Support/M74OtherPronounsBasics.php','app/Support/M74BuilderSelectionPresentation.php',
                'app/Support/M74QuestionSupplement.php','app/Support/M74BankRevision.php','app/Services/M74BankCompletionPatch.php',
                'app/Support/ComposeTokenCase.php','app/Support/M51QuestionSupplement.php'],
            'controller_path'=>self::CONTROLLER_PATH,'controller_root_after_sha'=>self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha'=>self::CONTROLLER_WT_AFTER_SHA];
    }
    protected function assertExistingPools(array $questions,array $contract,?array $baseline):void
    {
        $i=array_search($contract['identity']??null,Package::OWNERS,true);
        if($i===false||!array_key_exists('level',$contract)||$contract['level']!==null){
            throw new RuntimeException('M75 declared owner/actual NULL practice level differs.');
        }
        $ids=[];
        foreach($contract['bank_seeders'] as $bank){
            if($bank===Supplement::BANK){if($i!==2){throw new RuntimeException('M75 supplement belongs only to Indefinite.');}continue;}
            $filter=static fn($q)=>$q['seeder']===$bank&&(string)$q['type']==='4';
            $eligible=array_values(array_filter($questions,$filter));
            usort($eligible,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
            if($baseline!==null){
                $expected=array_values(array_filter($baseline['relations']['questions'],$filter));
                usort($expected,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
                if($eligible!==$expected){throw new RuntimeException('M75 complete original type4 pool changed.');}
            }
            array_push($ids,...array_column($eligible,'id'));
        }
        if(count($ids)<5||count(array_unique($ids))!==count($ids)){throw new RuntimeException('M75 thematic bank is short or repeats IDs.');}
    }
}
