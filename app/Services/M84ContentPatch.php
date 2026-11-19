<?php

namespace App\Services;

use App\Support\M51QuestionSupplement as QuestionRows;
use App\Support\M84QuestionSupplement as Supplement;
use App\Support\M84WordOrderHaveGot as Package;
use RuntimeException;

/** Six finite UK owners, five continuing practices, one new practice and exact insert-only question families. */
class M84ContentPatch extends TheoryNativeContentPatch
{
    use M84QuestionInsertScope;

    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916';
    public const CONSUMERS_FILE = 'before-old-consumers-v2.json';
    public const CONSUMERS_SHA = '1a3cf5b616783d0740650b498a8fb3b036dd64fbcfea79011edd20fe6687a98a';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const ELIGIBLE_POOL_COUNTS = [72, 72, 72, 72, 72, 72];
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    // Fail closed until the exact foreign-inclusive ROOT/WT registration deltas are reviewed.
    public const CONTROLLER_ROOT_AFTER_SHA = 'dc14b4c8a81e5db41b48c4577a22d7bac857fce5dad37107ab2663ecbf4135de';
    public const CONTROLLER_WT_AFTER_SHA = '14402494da3e92b87e2036d9a4c1eb1274c48a65bc4b1c8421b358c3e1727b0c';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryBuilderSelectionPresentation.php', 'app/Support/TheoryNativeContentRelease.php',
        'app/Support/M84WordOrderHaveGot.php', 'app/Support/M84BuilderSelectionPresentation.php',
        'app/Support/M84QuestionSupplement.php', 'app/Services/M84QuestionInsertScope.php',
        'app/Services/M84ContentPatch.php', 'app/Services/M84LocalTargetGuard.php',
        'app/Console/Commands/ContentPatchM84.php', Supplement::SOURCE,
        Package::BINDINGS, Package::CHECKSUMS, Package::NAVIGATION,
        'docs/content/m84-sentence-builder-bank-audit.v1.json',
        'docs/content/m84-word-order-have-got.components.v1.json',
        'docs/content/m84-word-order-have-got.tasks.v1.json',
        'docs/content/m84-word-order-have-got.editorial.v1.json',
        'docs/content/m84-word-order-have-got.coverage.v1.json',
        'docs/content/m84-widget-selection.v1.json',
        'docs/content/m84-word-order-have-got.v1.0.0.uk.md',
        'docs/content/m84-author-parts/ab.v1.json',
        'docs/content/m84-author-parts/cd.v1.json',
        'docs/content/m84-author-parts/ef.v1.json',
        'tests/Fixtures/m84-owned-source-hunks.json',
        'tests/Fixtures/m84-owned-source-hunks.v1.0.1.json',
        'docs/content/m84-source-checksums.v1.json',
    ];
    protected const ID = 'm84-word-order-have-got-standard-view-v1';

    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return ['text']; }

    protected static function policy(): array
    {
        return [
            'guard' => M84LocalTargetGuard::class, 'served_root' => M84LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS,
            'bank_source_overrides' => array_fill_keys(Supplement::BANKS, Supplement::SOURCE),
            'extra_source_paths' => [
                'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
                'app/Services/TheoryLocalReleaseGuard.php', 'app/Support/M51QuestionSupplement.php',
                'app/Support/M76PronounRelations.php', 'app/Support/M76BuilderSelectionPresentation.php',
                'app/Support/ComposeTokenCase.php', 'app/Support/AnswerOptionCase.php', 'app/Support/AcceptedAnswerVariants.php',
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
            throw new RuntimeException('M84 declared owner and actual practice SQL level differ.');
        }
        $expectedBanks = $index < 5 ? [Package::BANKS[$index], Supplement::BANKS[$index]] : [Package::BANKS[$index]];
        if (($contract['bank_seeders'] ?? null) !== $expectedBanks) {
            throw new RuntimeException('M84 exact original and supplemental thematic bank vector differs.');
        }
        $supplemented = $this->assertSupplementPools($questions, $contract, $baseline);
        if ($supplemented !== ($index < 5)) {
            throw new RuntimeException('M84 only the five Word Order owners receive the reviewed supplement.');
        }
        $bank = Package::BANKS[$index];
        $filter = static fn ($q) => $q['seeder'] === $bank && (string) $q['type'] === '4'
            && ($contract['level'] === null || $q['level'] === $contract['level']);
        $eligible = array_values(array_filter($questions, $filter));
        usort($eligible, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
        if ($baseline !== null) {
            $expected = array_values(array_filter($baseline['relations']['questions'], $filter));
            usort($expected, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
            if ($eligible !== $expected) { throw new RuntimeException('M84 complete original thematic type-4 pool changed.'); }
        }
        $ids = array_column($eligible, 'id'); $count = self::ELIGIBLE_POOL_COUNTS[$index];
        if (count($ids) !== $count || count(array_unique($ids)) !== $count) {
            throw new RuntimeException('M84 exact existing original thematic pool is short or duplicated.');
        }
    }

    /** Report actual finite plan cardinalities; never hide the bank insert hook behind a zero-question claim. */
    public static function questionPlanSummary(array $plan): array
    {
        $rows = $plan['question_supplement']['expected'] ?? null;
        if (!is_array($rows) || array_keys($rows) !== array_keys(QuestionRows::ROW_COLUMNS)) {
            throw new RuntimeException('M84 reviewed question-family plan is incomplete.');
        }
        $counts = [];
        foreach (QuestionRows::ROW_COLUMNS as $table => $_) {
            if (!is_array($rows[$table]) || !array_is_list($rows[$table])) {
                throw new RuntimeException('M84 reviewed insert-family rows are not complete lists.');
            }
            $counts[$table] = count($rows[$table]);
        }
        if ($counts['questions'] !== count(Supplement::UUIDS) || $counts['question_theory_text_blocks'] !== count(Supplement::UUIDS)
            || array_column($rows['questions'], 'uuid') !== Supplement::UUIDS) {
            throw new RuntimeException('M84 reviewed question-family plan cardinality or identities differ.');
        }
        return ['reviewed_question_family_rows' => $counts, 'reviewed_questions' => $counts['questions']];
    }
}
