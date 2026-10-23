<?php

namespace App\Services;

use App\Support\M79QuestionTypesCompletion as Package;
use RuntimeException;

/** Six finite UK owners: three practice inserts and three in-place practices; exact insert-only bank supplement. */
class M79ContentPatch extends TheoryNativeContentPatch
{
    use M79QuestionInsertScope;

    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '8c261b17b3f7e332fc5fc86eef8b555af83de19938aee7ae83d2a23e7dbb3add';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'ada89c6d6961dd7446313e401caec85a7dc40e9c6e673dc9042ee63ba8f80c63';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = 'e5fc33251b082401405134e475925db756875d8cf143e0c8cfd9f0e3961d281d';
    public const CONTROLLER_WT_AFTER_SHA = '36f50dd099f531009dcb94da76992527ba8d5dd3341f3b9beb9153e7315a7470';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryBuilderSelectionPresentation.php', 'app/Support/TheoryNativeContentRelease.php',
        'app/Support/M79QuestionTypesCompletion.php', 'app/Support/M79BuilderSelectionPresentation.php',
        'app/Services/M79ContentPatch.php', 'app/Services/M79LocalTargetGuard.php',
        'app/Console/Commands/ContentPatchM79.php',
        'app/Support/M79QuestionSupplement.php', 'app/Services/M79QuestionInsertScope.php',
        \App\Support\M79QuestionSupplement::SOURCE,
        Package::BINDINGS, Package::CHECKSUMS, \App\Support\M79BuilderSelectionPresentation::AUDIT,
    ];
    protected const ID = 'm79-question-types-completion-standard-view-v1';

    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body']; }

    protected static function policy(): array
    {
        return [
            'guard' => M79LocalTargetGuard::class, 'served_root' => M79LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS,
            'bank_source_overrides' => [
                'Database\\Seeders\\V3\\Polyglot\\M79NegativeFactsSupplementSeeder' => \App\Support\M79QuestionSupplement::SOURCE,
                'Database\\Seeders\\V3\\Polyglot\\M79EmbeddedStructureSupplementSeeder' => \App\Support\M79QuestionSupplement::SOURCE,
                'Database\\Seeders\\V3\\Polyglot\\M79IndirectInformationSupplementSeeder' => \App\Support\M79QuestionSupplement::SOURCE,
            ],
            'extra_source_paths' => [
                'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
                'app/Services/TheoryLocalReleaseGuard.php',
                'app/Support/M51QuestionSupplement.php',
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
        if ($this->assertSupplementPools($questions, $contract, $baseline)) { return; }
        $index = array_search($contract['identity'] ?? null, Package::OWNERS, true);
        if ($index === false || !array_key_exists('level', $contract) || $contract['level'] !== Package::LEVELS[$index]) {
            throw new RuntimeException('M79 declared owner/actual practice level differs.');
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
                    throw new RuntimeException('M79 complete original page-wide bank/type/actual CEFR pool changed.');
                }
            }
            array_push($ids, ...array_column($eligible, 'id'));
        }
        if (count($ids) < 5 || count(array_unique($ids)) !== count($ids)) {
            throw new RuntimeException('M79 thematic bank is short or repeats IDs.');
        }
    }
}
