<?php

namespace App\Services;

use App\Support\M82CommonMistakesLevelRevision as Package;
use RuntimeException;

/** Six exact UK owners and eight declared metadata transitions; no question-family writes. */
class M82ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES=Package::OWNERS;
    public const BEFORE_FILE='before-v1.json';
    public const BEFORE_SHA='94312691251f96433ee72c3fe544db71767c795495874af3ba1c80c2cbb49c62';
    public const CONSUMERS_FILE='before-old-consumers-v1.json';
    public const CONSUMERS_SHA='1a8cb25b84914738dcab29f957c3c38087685364281d061b6fcb2980b9fdbaa8';
    public const SYNC_RECEIPT='source-sync-v1.json';
    public const SOURCE_WORKTREE='D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS=[12,12,12,12,12,24];
    public const CONTROLLER_PATH='app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA='0b493b2516d84c8495a56d678a7dc58d6f10cfcdf416a652fdfc81ae9d5423ff';
    public const CONTROLLER_WT_AFTER_SHA='f2ea95a4d6bc6273e44be1e3d7193660edd8a459efcd3f0a3a937ed98fe416b5';
    public const SYNC_CODE_PATHS=[
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php','app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,
        'app/Support/TheoryBuilderSelectionPresentation.php','app/Support/TheoryNativeContentRelease.php',
        'app/Support/M82CommonMistakesLevelRevision.php','app/Support/M82BuilderSelectionPresentation.php',
        'app/Services/M82ContentPatch.php','app/Services/M82LocalTargetGuard.php',
        'app/Console/Commands/ContentPatchM82.php',
        Package::BINDINGS,Package::CHECKSUMS,\App\Support\M82BuilderSelectionPresentation::AUDIT,
        'docs/content/m82-navigation-review.v1.json',
    ];
    protected const ID='m82-common-mistakes-level-revision-standard-view-v1';
    protected static function packageClass():string { return Package::class; }
    protected function allowedUpdateFields():array { return ['body','type','heading']; }
    protected static function policy():array
    {
        return [
            'guard'=>M82LocalTargetGuard::class,'served_root'=>M82LocalTargetGuard::ROOT,
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
            throw new RuntimeException('M82 exact owner/thematic bank/actual CEFR differs.');
        }
        $bank=$contract['bank_seeders'][0];
        $filter=static fn($q)=>$q['seeder']===$bank&&(string)$q['type']==='4'&&$q['level']===$contract['level'];
        $eligible=array_values(array_filter($questions,$filter));
        usort($eligible,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
        if ($baseline!==null) {
            $expected=array_values(array_filter($baseline['relations']['questions'],$filter));
            usort($expected,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
            if ($eligible!==$expected) { throw new RuntimeException('M82 complete original eligible pool changed.'); }
        }
        $ids=array_column($eligible,'id');
        $expectedCount=self::ELIGIBLE_POOL_COUNTS[$index];
        if (count($ids)!==$expectedCount||count(array_unique($ids))!==$expectedCount) {
            throw new RuntimeException('M82 exact existing thematic pool is short or duplicated.');
        }
    }
}
