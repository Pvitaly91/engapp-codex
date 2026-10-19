<?php

namespace App\Services;

use App\Support\M58PastFuturePassivesPackage as Package;

/** M58 finite policy; unchanged M26 transaction/backup plus shared native planner. */
class M58ContentPatch extends TheoryNativeContentPatch
{
    use M58QuestionInsertScope;
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '89dbf1213d3ebabcb1f4b30680c8600b58a4f7eb957038750addc5ed8cfe8ef0';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '19a418e6268f779c7952e37d1f66f79d6c36e9297936a39dca7900ee4e4f2616';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = 'c8c4aa00efef79f8b969e99a96ecc29453c8b10e7365e0ef0df386fccc7e3715';
    public const CONTROLLER_WT_AFTER_SHA = '5a59b896a4f90bb46b8ef22601bd384e3d70e661d105b48617a843ca9bae0ce1';
    public const AUDIT = 'docs/content/m58-past-future-passives-bank-audit.v1.json';
    public const AUDIT_SHA = '89e1dfebdb2c590da181176c8e21eff42ac7da366e2c35b0860f1c8ff8503568';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryNativeContentRelease.php', 'app/Support/M58PastFuturePassivesPackage.php',
        'app/Services/TheoryNativeContentPatch.php', 'app/Services/M58ContentPatch.php',
        'app/Services/TheoryLocalReleaseGuard.php', 'app/Services/M58LocalTargetGuard.php',
        'app/Console/Commands/PatchM58Content.php', 'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryContentRenderer.php',
        'app/Support/M58QuestionSupplement.php', 'app/Services/M58QuestionInsertScope.php',
        self::AUDIT,
        'docs/content/m58-past-future-passives-widget-questions.v1.0.0.json',
        'database/seeders/V3/Polyglot/M58PastPerfectPassiveB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M58FutureSimplePassiveB1SupplementSeeder.php',
        'database/seeders/V3/Polyglot/M58FuturePerfectPassiveB2SupplementSeeder.php',
    ];
    protected const ID = 'm58-past-future-passives-standard-view-v1';

    public function apply(string $planPath, string $backupPath, ?string $expectedDatabase = null): array
    {
        $projection = $this->questionEvidence()['projection'];
        $result = parent::apply($planPath, $backupPath, $expectedDatabase);
        $counts = [];
        foreach (\App\Support\M58QuestionSupplement::ROW_COLUMNS as $table => $_) {
            $counts[$table] = $result['status'] === 'applied' ? count($projection[$table]) : 0;
        }
        return $result + ['question_families_inserted' => $counts];
    }

    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return ['guard' => M58LocalTargetGuard::class, 'served_root' => M58LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA, 'sync_receipt' => self::SYNC_RECEIPT,
            'sync_code_paths' => self::SYNC_CODE_PATHS, 'bank_source_overrides' => array_fill_keys(\App\Support\M58QuestionSupplement::BANKS, \App\Support\M58QuestionSupplement::SOURCE),
            'extra_source_paths' => ['app/Support/M51QuestionSupplement.php', 'app/Support/ComposeTokenCase.php',
                'app/Support/M53BuilderHintPresentation.php', 'app/Support/M54BuilderTokenPresentation.php',
                'app/Support/M55BuilderContextPresentation.php', 'app/Support/M56BuilderSelectionPresentation.php'],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }
}
