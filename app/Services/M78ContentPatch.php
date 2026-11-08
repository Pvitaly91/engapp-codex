<?php

namespace App\Services;

use App\Support\M78PhrasalQuestionBasics as Package;
use RuntimeException;

/** Six finite UK owners: two practice inserts and four in-place practices; no question-family writes. */
class M78ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '327424de32f1cebaad86adef078d25aa1d4747b257894be3fdadc6706c630422';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '073f1da1354c6227146a920ff86997dcfccb35b25b1f9068da2aa290012dada3';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '77cc36c6448b916fd7d7a4eab36766949067108a89388b659b62a9375ef1027f';
    public const CONTROLLER_WT_AFTER_SHA = 'b0338ced5103402476c4d1e603bc0914a2e20aab800b1f20b0df04304c027408';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryBuilderSelectionPresentation.php', 'app/Support/TheoryNativeContentRelease.php',
        'app/Support/M78PhrasalQuestionBasics.php', 'app/Support/M78BuilderSelectionPresentation.php',
        'app/Services/M78ContentPatch.php', 'app/Services/M78LocalTargetGuard.php',
        'app/Console/Commands/ContentPatchM78.php',
        Package::BINDINGS, Package::CHECKSUMS, \App\Support\M78BuilderSelectionPresentation::AUDIT,
    ];
    protected const ID = 'm78-phrasal-question-basics-standard-view-v1';

    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body', 'type', 'heading']; }

    protected static function policy(): array
    {
        return [
            'guard' => M78LocalTargetGuard::class, 'served_root' => M78LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS,
            'bank_source_overrides' => [],
            'extra_source_paths' => [
                'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
                'app/Services/TheoryLocalReleaseGuard.php',
                'app/Support/M76PronounRelations.php', 'app/Support/M76BuilderSelectionPresentation.php',
                'app/Support/ComposeTokenCase.php', 'app/Support/AnswerOptionCase.php',
                'app/Support/AcceptedAnswerVariants.php',
            ],
            'controller_path' => self::CONTROLLER_PATH,
            'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA,
        ];
    }

    protected function assertExistingPools(array $questions, array $contract, ?array $baseline): void
    {
        $index = array_search($contract['identity'] ?? null, Package::OWNERS, true);
        if ($index === false || !array_key_exists('level', $contract) || $contract['level'] !== Package::LEVELS[$index]) {
            throw new RuntimeException('M78 declared owner/actual practice level differs.');
        }
        $ids = [];
        foreach ($contract['bank_seeders'] as $bank) {
            $filter = static fn ($question) => $question['seeder'] === $bank && (string) $question['type'] === '4'
                && ($contract['level'] === null || $question['level'] === $contract['level']);
            $eligible = array_values(array_filter($questions, $filter));
            usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($baseline !== null) {
                $expected = array_values(array_filter($baseline['relations']['questions'], $filter));
                usort($expected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
                if ($eligible !== $expected) {
                    throw new RuntimeException('M78 complete original page-wide bank/type/actual CEFR pool changed.');
                }
            }
            array_push($ids, ...array_column($eligible, 'id'));
        }
        if (count($ids) < 5 || count(array_unique($ids)) !== count($ids)) {
            throw new RuntimeException('M78 thematic bank is short or repeats IDs.');
        }
    }
}

