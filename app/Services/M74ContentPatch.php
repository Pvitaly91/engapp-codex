<?php

namespace App\Services;

use App\Support\M74OtherPronounsBasics as Package;
use RuntimeException;

/** One new practice, two continuing practices; no question-family or metadata writes. */
class M74ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '9e7abb860af2a61bfb10f61047f7ffaad055e20368edfc24c9ed095301516fdc';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'e77c5d572aba3da60158aae87f845dbcf70d4b1074980a4bdd7933258f60cb56';
    public const SYNC_RECEIPT = 'source-sync-v3.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '989daf62a0f6b5ccca77d07cb092e4667bc2cb99a65586ead6af3ea93e302fa4';
    public const CONTROLLER_WT_AFTER_SHA = '989daf62a0f6b5ccca77d07cb092e4667bc2cb99a65586ead6af3ea93e302fa4';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php','app/Services/TheoryNativeContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php','app/Support/TheoryBuilderSelectionPresentation.php',
        'app/Support/M74OtherPronounsBasics.php','app/Support/M74BuilderSelectionPresentation.php',
        'app/Support/M74QuestionSupplement.php','app/Support/M74BankRevision.php','app/Services/M74BankCompletionPatch.php',
        'app/Support/TheorySentenceBuilderAcceptance.php',\App\Support\M74BankRevision::REVISION,
        'app/Services/M74ContentPatch.php','app/Services/M74LocalTargetGuard.php','app/Console/Commands/ContentPatchM74.php',
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php','app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        Package::CHECKSUMS,\App\Support\M74BuilderSelectionPresentation::AUDIT,
    ];
    protected const ID = 'm74-other-pronouns-basics-standard-view-v1';
    protected static function packageClass():string { return Package::class; }
    protected static function policy():array
    {
        return ['guard'=>M74LocalTargetGuard::class,'served_root'=>M74LocalTargetGuard::ROOT,
            'source_worktree'=>self::SOURCE_WORKTREE,'before_file'=>self::BEFORE_FILE,'before_sha'=>self::BEFORE_SHA,
            'consumers_file'=>self::CONSUMERS_FILE,'consumers_sha'=>self::CONSUMERS_SHA,'sync_receipt'=>self::SYNC_RECEIPT,
            'sync_code_paths'=>self::SYNC_CODE_PATHS,'bank_source_overrides'=>[\App\Support\M74QuestionSupplement::BANK=>\App\Support\M74QuestionSupplement::SOURCE],
            'extra_source_paths'=>['app/Support/M73GenericIrregularCollective.php','app/Support/M73BuilderSelectionPresentation.php',
                'app/Support/M72PartitivesNegativesGeography.php','app/Support/M72BuilderSelectionPresentation.php',
                'app/Support/M71ArticlesQuantifiers.php','app/Support/M70IntensifiersNounBasics.php','app/Support/ComposeTokenCase.php',
                'app/Support/M51QuestionSupplement.php'],
            'controller_path'=>self::CONTROLLER_PATH,'controller_root_after_sha'=>self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha'=>self::CONTROLLER_WT_AFTER_SHA];
    }
    protected function assertExistingPools(array $questions,array $contract,?array $baseline):void
    {
        $i=array_search($contract['identity']??null,Package::OWNERS,true);
        if($i===false||!array_key_exists('level',$contract)||$contract['level']!==Package::LEVELS[$i]){
            throw new RuntimeException('M74 declared owner/actual practice level differs.');
        }
        $ids=[];
        foreach($contract['bank_seeders'] as $bank){
            $filter=static fn($q)=>$q['seeder']===$bank&&(string)$q['type']==='4'
                &&($contract['level']===null||$q['level']===$contract['level']);
            $eligible=array_values(array_filter($questions,$filter));
            usort($eligible,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
            if($baseline!==null){
                $expected=array_values(array_filter($baseline['relations']['questions'],$filter));
                usort($expected,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
                if($eligible!==$expected){throw new RuntimeException('M74 original page-wide bank/type/actual CEFR pool drifted.');}
            }
            array_push($ids,...array_column($eligible,'id'));
        }
        if(count($ids)<5||count(array_unique($ids))!==count($ids)){throw new RuntimeException('M74 thematic bank is short or repeats IDs.');}
    }
}
