<?php

namespace App\Services;

use App\Support\M80NegativesTransformations as Package;
use RuntimeException;

/** Six exact UK owners and eight declared metadata transitions; no question-family writes. */
class M80ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES=Package::OWNERS;
    public const BEFORE_FILE='before-v1.json';
    public const BEFORE_SHA='75a9e58003f9364c45e4e3c2696aab7c0b8e5d5073a2bb2acefe54fb198dbefb';
    public const CONSUMERS_FILE='before-old-consumers-v1.json';
    public const CONSUMERS_SHA='bed6e6b56dfef3c6c9b880688dc9076dbd5d5970d0c38f01e337a8ef18299cd0';
    public const SYNC_RECEIPT='source-sync-v1.json';
    public const SOURCE_WORKTREE='D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const CONTROLLER_PATH='app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA='1da05f5a9ba29958b7597928af48f9bf9d144cbd5935d22a9602eb4f4d924a8a';
    public const CONTROLLER_WT_AFTER_SHA='a744d9efdf37cd07c96d86d66efc79a43ea0e24d7490e644c70749539196faa0';
    public const SYNC_CODE_PATHS=[
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php','app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,
        'app/Support/TheoryBuilderSelectionPresentation.php','app/Support/TheoryNativeContentRelease.php',
        'app/Support/M80NegativesTransformations.php','app/Support/M80BuilderSelectionPresentation.php',
        'app/Services/M80ContentPatch.php','app/Services/M80LocalTargetGuard.php',
        'app/Console/Commands/ContentPatchM80.php',
        Package::BINDINGS,Package::CHECKSUMS,\App\Support\M80BuilderSelectionPresentation::AUDIT,
    ];
    protected const ID='m80-negatives-transformations-standard-view-v1';
    protected static function packageClass():string { return Package::class; }
    protected function allowedUpdateFields():array { return ['body','type','heading']; }
    protected static function policy():array
    {
        return [
            'guard'=>M80LocalTargetGuard::class,'served_root'=>M80LocalTargetGuard::ROOT,
            'source_worktree'=>self::SOURCE_WORKTREE,'before_file'=>self::BEFORE_FILE,'before_sha'=>self::BEFORE_SHA,
            'consumers_file'=>self::CONSUMERS_FILE,'consumers_sha'=>self::CONSUMERS_SHA,
            'sync_receipt'=>self::SYNC_RECEIPT,'sync_code_paths'=>self::SYNC_CODE_PATHS,'bank_source_overrides'=>[],
            'extra_source_paths'=>[
                'app/Support/TheoryNativeContentRelease.php','app/Services/TheoryNativeContentPatch.php',
                'app/Services/TheoryLocalReleaseGuard.php','app/Support/M51QuestionSupplement.php',
                'app/Support/M76PronounRelations.php','app/Support/M76BuilderSelectionPresentation.php',
                'app/Support/ComposeTokenCase.php','app/Support/AnswerOptionCase.php','app/Support/AcceptedAnswerVariants.php',
            ],
            'controller_path'=>self::CONTROLLER_PATH,'controller_root_after_sha'=>self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha'=>self::CONTROLLER_WT_AFTER_SHA,
        ];
    }
    protected function assertExistingPools(array $questions,array $contract,?array $baseline):void
    {
        $index=array_search($contract['identity']??null,Package::OWNERS,true);
        if ($index===false||($contract['level']??null)!==Package::LEVELS[$index]||count($contract['bank_seeders']??[])!==1) {
            throw new RuntimeException('M80 exact owner/thematic bank/actual CEFR differs.');
        }
        $bank=$contract['bank_seeders'][0];
        $filter=static fn($q)=>$q['seeder']===$bank&&(string)$q['type']==='4'&&$q['level']===$contract['level'];
        $eligible=array_values(array_filter($questions,$filter));
        usort($eligible,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
        if ($baseline!==null) {
            $expected=array_values(array_filter($baseline['relations']['questions'],$filter));
            usort($expected,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
            if ($eligible!==$expected) { throw new RuntimeException('M80 complete original eligible pool changed.'); }
        }
        $ids=array_column($eligible,'id');
        if (count($ids)!==12||count(array_unique($ids))!==12) { throw new RuntimeException('M80 exact existing pool is short or duplicated.'); }
    }
}
