<?php

namespace App\Services;

use App\Support\M53PassiveConstructionsPackage as Package;

/** M53 finite policy; unchanged M26 transaction/backup plus shared native planner. */
class M53ContentPatch extends TheoryNativeContentPatch
{
    use M53QuestionInsertScope;
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'dc1cd8f817b6013f9dad6065e795190764992feca687700aac8698efdd762185';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '794f06f305c176da2b6170f405eca3eb5a777394b695fbeb61725b0f8abba37c';
    public const CONTROLLER_WT_AFTER_SHA = '0500ac5f89f29884b02e64c07b563bb0e49635bd4d1c7b4009148324eb11f239';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M53PassiveConstructionsPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M53ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M53LocalTargetGuard.php',
        'app/Console/Commands/PatchM53Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryContentRenderer.php', 'app/Support/M53BuilderHintPresentation.php',
        'app/Support/M53QuestionSupplement.php', 'app/Services/M53QuestionInsertScope.php',
        'docs/content/m53-passive-constructions-bank-audit.v1.json',
        'docs/content/m53-get-passive-question-supplement.v1.0.0.json',
        'database/seeders/V3/Polyglot/M53GetPassiveB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M53GetPassiveB1SupplementSeeder/M53GetPassiveB1SupplementSeeder.php',
    ];
    protected const ID = 'm53-passive-constructions-standard-view-v1';

    public function apply(string $planPath, string $backupPath, ?string $expectedDatabase = null): array
    {
        $projection = $this->questionEvidence()['projection'];
        $result = parent::apply($planPath, $backupPath, $expectedDatabase);
        $counts = [];
        foreach (\App\Support\M53QuestionSupplement::ROW_COLUMNS as $table => $_) {
            $counts[$table] = $result['status'] === 'applied' ? count($projection[$table]) : 0;
        }
        return $result + ['question_families_inserted' => $counts];
    }

    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard' => M53LocalTargetGuard::class, 'served_root' => M53LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => [],
            'extra_source_paths' => ['app/Support/M51QuestionSupplement.php', 'app/Support/ComposeTokenCase.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
