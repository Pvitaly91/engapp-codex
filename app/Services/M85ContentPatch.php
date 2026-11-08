<?php

namespace App\Services;

use App\Support\M85VerbToBe as Package;
use RuntimeException;

/** Six exact UK native owners; six new practices, no question-family mutations. */
class M85ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES=Package::OWNERS;
    public const BEFORE_FILE='before-v1.json';
    public const BEFORE_SHA='fe639c06446c6fb46b5b4ab25ae654e3910b6733cc124a9e4bee262e41b411b7';
    public const CONSUMERS_FILE='before-old-consumers-v1.json';
    public const CONSUMERS_SHA='10fd77a4d0e500333b83907c44f675dae629f026160731a9ad7825e77335f5b7';
    public const SYNC_RECEIPT='source-sync-v1.json';
    public const SOURCE_WORKTREE='D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS=[71,72,72,72,72,70];
    public const CONTROLLER_PATH='app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA='f247da5fff791dd4ed2f52b566bbbb5e0a9182940b1bac3c955fdf1a6a30d881';
    public const CONTROLLER_WT_AFTER_SHA='1aa4d6f865d9641619af793f3e991463ec2a42d81c11cef61572ba7a2ccb006a';
    public const SYNC_CODE_PATHS=[
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php','app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,
        'app/Support/TheoryBuilderSelectionPresentation.php','app/Support/TheoryNativeContentRelease.php',
        'app/Support/M85VerbToBe.php','app/Support/M85BuilderSelectionPresentation.php',
        'app/Services/M85ContentPatch.php','app/Services/M85LocalTargetGuard.php','app/Console/Commands/ContentPatchM85.php',
        Package::BINDINGS,Package::CHECKSUMS,Package::NAVIGATION,
        'docs/content/m85-sentence-builder-bank-audit.v1.json',
        'docs/content/m85-verb-to-be.components.v1.json','docs/content/m85-verb-to-be.tasks.v1.json',
        'docs/content/m85-verb-to-be.editorial.v1.json','docs/content/m85-verb-to-be.coverage.v1.json',
        'docs/content/m85-widget-selection.v1.json','docs/content/m85-verb-to-be.v1.0.0.uk.md',
        'docs/content/m85-author-parts/ab.v1.json','docs/content/m85-author-parts/cd.v1.json','docs/content/m85-author-parts/ef.v1.json',
        'tests/Fixtures/m85-owned-source-hunks.json',
    ];
    protected const ID='m85-verb-to-be-standard-view-v1';
    protected static function packageClass():string{return Package::class;}
    protected function allowedUpdateFields():array{return ['body'];}
    protected static function policy():array
    {
        return ['guard'=>M85LocalTargetGuard::class,'served_root'=>M85LocalTargetGuard::ROOT,
            'source_worktree'=>self::SOURCE_WORKTREE,'before_file'=>self::BEFORE_FILE,'before_sha'=>self::BEFORE_SHA,
            'consumers_file'=>self::CONSUMERS_FILE,'consumers_sha'=>self::CONSUMERS_SHA,
            'sync_receipt'=>self::SYNC_RECEIPT,'sync_code_paths'=>self::SYNC_CODE_PATHS,'bank_source_overrides'=>[],
            'extra_source_paths'=>['app/Support/TheoryNativeContentRelease.php','app/Services/TheoryNativeContentPatch.php',
                'app/Services/TheoryLocalReleaseGuard.php','app/Support/M51QuestionSupplement.php',
                'app/Support/M76PronounRelations.php','app/Support/M76BuilderSelectionPresentation.php',
                'app/Support/ComposeTokenCase.php','app/Support/AnswerOptionCase.php','app/Support/AcceptedAnswerVariants.php'],
            'controller_path'=>self::CONTROLLER_PATH,'controller_root_after_sha'=>self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha'=>self::CONTROLLER_WT_AFTER_SHA];
    }
    protected function assertExistingPools(array $questions,array $contract,?array $baseline):void
    {
        $i=array_search($contract['identity']??null,Package::OWNERS,true);
        if($i===false||!array_key_exists('level',$contract)||$contract['level']!==null||($contract['bank_seeders']??null)!==[Package::BANKS[$i]]){
            throw new RuntimeException('M85 exact original bank/owner/actual practice level differs.');
        }
        $filter=static fn($q)=>$q['seeder']===Package::BANKS[$i]&&(string)$q['type']==='4';
        $eligible=array_values(array_filter($questions,$filter));usort($eligible,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
        if($baseline!==null){$expected=array_values(array_filter($baseline['relations']['questions'],$filter));usort($expected,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
            if($eligible!==$expected){throw new RuntimeException('M85 complete original linked thematic pool changed.');}}
        $ids=array_column($eligible,'id');
        if(count($ids)!==self::ELIGIBLE_POOL_COUNTS[$i]||count(array_unique($ids))!==count($ids)){
            throw new RuntimeException('M85 independently captured original pool count/identity differs.');
        }
    }
}
