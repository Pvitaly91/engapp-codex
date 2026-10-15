<?php

namespace App\Services;

use App\Support\M57ContinuousPerfectPassivesPackage as Package;

/** M57 finite policy; unchanged M26 transaction/backup plus shared native planner. */
class M57ContentPatch extends TheoryNativeContentPatch
{
    use M57QuestionInsertScope;
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'dee8df7e2f51d2a9d7adb0c3858a99e93f4beb0abc961c6ff6741120e1fb0d11';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'f906806afbd61625a7d98827dacf7d060a18647ac95850aff2911940ea2cb6a6';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '8b7a54272d89357ec1415c33cb49749582aaede4be743afdaababe5ff00877e3';
    public const CONTROLLER_WT_AFTER_SHA = 'd07fabc695900777aadad20dd30fe66a1a97e91a6745658c9d284d1c5239447c';
    public const AUDIT = 'docs/content/m57-continuous-perfect-passives-bank-audit.v1.0.1.json';
    public const AUDIT_SHA = '3a33564f8c76570862e485d43d6069dba101e3f55efadb2cf7907181bebc57ab';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M57ContinuousPerfectPassivesPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M57ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M57LocalTargetGuard.php',
        'app/Console/Commands/PatchM57Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryContentRenderer.php',
        'app/Support/M57QuestionSupplement.php', 'app/Services/M57QuestionInsertScope.php',
        'docs/content/m57-continuous-perfect-passives-bank-audit.v1.json', self::AUDIT,
        'docs/content/m57-continuous-perfect-passives-widget-questions.v1.0.0.json',
        'database/seeders/V3/Polyglot/M57PresentContinuousPassiveB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M57PastContinuousPassiveB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M57PresentPerfectPassiveB1SupplementSeeder.php',
    ];
    protected const ID = 'm57-continuous-perfect-passives-standard-view-v1';

    public function apply(string $planPath, string $backupPath, ?string $expectedDatabase = null): array
    {
        $projection = $this->questionEvidence()['projection'];
        $result = parent::apply($planPath, $backupPath, $expectedDatabase);
        $counts = [];
        foreach (\App\Support\M57QuestionSupplement::ROW_COLUMNS as $table => $_) {
            $counts[$table] = $result['status'] === 'applied' ? count($projection[$table]) : 0;
        }
        return $result + ['question_families_inserted' => $counts];
    }

    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard' => M57LocalTargetGuard::class, 'served_root' => M57LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => array_fill_keys(\App\Support\M57QuestionSupplement::BANKS, \App\Support\M57QuestionSupplement::SOURCE),
            'extra_source_paths' => ['app/Support/M51QuestionSupplement.php', 'app/Support/ComposeTokenCase.php',
                'app/Support/M53BuilderHintPresentation.php', 'app/Support/M54BuilderTokenPresentation.php',
                'app/Support/M55BuilderContextPresentation.php', 'app/Support/M56BuilderSelectionPresentation.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
