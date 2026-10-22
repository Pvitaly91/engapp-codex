<?php

namespace App\Services;

use App\Support\M77OnePrepositionsPhrasal as Package;
use RuntimeException;

/** Six finite UK owners: five practice inserts and one in-place practice; no question-family writes. */
class M77ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = '3ee7c26081727dd746dfe1e94a03dcc4273c48bcb40be5091b84d27bc52cab31';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = 'b1d234ed6449720e4a06bd19b2c9834ce021ece735dfb659204fba72966fe660';
    public const SYNC_RECEIPT = 'source-sync-v1.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = 'ab1f6ee4cad95674850e7d0cd1b57413d5f6cd117449d73cbc3f364800ed3475';
    public const CONTROLLER_WT_AFTER_SHA = 'b54dc1e50c905ea4d84545081d4da73c94bd08b72d71906d767e6f8a17419c9a';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/TheoryBuilderSelectionPresentation.php',
        'app/Support/M77OnePrepositionsPhrasal.php', 'app/Support/M77BuilderSelectionPresentation.php',
        'app/Services/M77ContentPatch.php', 'app/Services/M77LocalTargetGuard.php',
        'app/Console/Commands/ContentPatchM77.php',
        Package::BINDINGS, Package::CHECKSUMS, \App\Support\M77BuilderSelectionPresentation::AUDIT,
    ];
    protected const ID = 'm77-one-prepositions-phrasal-standard-view-v1';

    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body', 'type', 'heading']; }

    protected static function policy(): array
    {
        return [
            'guard' => M77LocalTargetGuard::class, 'served_root' => M77LocalTargetGuard::ROOT,
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
            throw new RuntimeException('M77 declared owner/actual practice level differs.');
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
                    throw new RuntimeException('M77 complete original page-wide bank/type/actual CEFR pool changed.');
                }
            }
            array_push($ids, ...array_column($eligible, 'id'));
        }
        if (count($ids) < 5 || count(array_unique($ids)) !== count($ids)) {
            throw new RuntimeException('M77 thematic bank is short or repeats IDs.');
        }
    }
}
