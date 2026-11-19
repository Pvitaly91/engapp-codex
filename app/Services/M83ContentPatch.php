<?php

namespace App\Services;

use App\Support\M83B2RevisionBasicGrammar as Package;
use RuntimeException;

/** Six exact UK native owners; preserve five existing practices and all question families. */
class M83ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES=Package::OWNERS;
    public const BEFORE_FILE='before-v1.json';
    public const BEFORE_SHA='d522a715b123748b84f52f076b156ccf5224892f138d5429062d9527a843e33e';
    public const CONSUMERS_FILE='before-old-consumers-v2.json';
    public const CONSUMERS_SHA='327edffb5a1949d3330b0799e8125d7e70afecd37cf239954600ff55a93bbd04';
    public const SYNC_RECEIPT='source-sync-v1.0.1b.json';
    public const SOURCE_WORKTREE='D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS=[12,18,72,72,72,12];
    public const CONTROLLER_PATH='app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA='56dedba0758f3806d6c71b95d5c1816956bd5d46763b54d7f400f4e4284a6ac4';
    public const CONTROLLER_WT_AFTER_SHA='f470cf85c94d4fbb1c1713af0d68ef6a34c468edaf361c6fa1bda077164a170f';
    public const SYNC_CODE_PATHS=[
        'app/Support/TheoryContentSource.php','app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php','app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php','app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php',self::CONTROLLER_PATH,
        'app/Support/TheoryBuilderSelectionPresentation.php','app/Support/TheoryNativeContentRelease.php',
        'app/Support/M83B2RevisionBasicGrammar.php','app/Support/M83BuilderSelectionPresentation.php',
        'app/Services/M83ContentPatch.php','app/Services/M83LocalTargetGuard.php',
        'app/Console/Commands/ContentPatchM83.php',
        Package::BINDINGS,Package::CHECKSUMS,\App\Support\M83BuilderSelectionPresentation::AUDIT,
        'docs/content/m83-navigation-review.v1.json',
        'docs/content/m83-inline-separation.v1.0.1.json',
        'docs/content/m83-b2-revision-basic-grammar.components.v1.0.1.json',
        'docs/content/m83-b2-revision-basic-grammar.v1.0.1.uk.md',
        'app/Services/M83InlineSeparationPatch.php',
        'tools/diagnostics/m83-inline-separation-patch.php',
        'tools/diagnostics/m83-freeze-inline-separation.php',
    ];
    protected const ID='m83-b2-revision-basic-grammar-standard-view-v1';
    protected static function packageClass():string { return Package::class; }
    protected function allowedUpdateFields():array { return ['body']; }
    protected static function policy():array
    {
        return [
            'guard'=>M83LocalTargetGuard::class,'served_root'=>M83LocalTargetGuard::ROOT,
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
            throw new RuntimeException('M83 exact owner/thematic bank/actual CEFR differs.');
        }
        $bank=$contract['bank_seeders'][0];
        $filter=static fn($q)=>$q['seeder']===$bank&&(string)$q['type']==='4'&&($contract['level']===null||$q['level']===$contract['level']);
        $eligible=array_values(array_filter($questions,$filter));
        usort($eligible,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
        if ($baseline!==null) {
            $expected=array_values(array_filter($baseline['relations']['questions'],$filter));
            usort($expected,static fn($a,$b)=>strcmp($a['uuid'],$b['uuid']));
            if ($eligible!==$expected) { throw new RuntimeException('M83 complete original eligible pool changed.'); }
        }
        $ids=array_column($eligible,'id');
        $expectedCount=self::ELIGIBLE_POOL_COUNTS[$index];
        if (count($ids)!==$expectedCount||count(array_unique($ids))!==$expectedCount) {
            throw new RuntimeException('M83 exact existing thematic pool is short or duplicated.');
        }
    }
}
