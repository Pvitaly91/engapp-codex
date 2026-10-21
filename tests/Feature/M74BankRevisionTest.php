<?php

namespace Tests\Feature;

use App\Services\M74ContentPatch;
use App\Support\M51QuestionSupplement;
use App\Support\M74BankRevision;
use App\Support\M74OtherPronounsBasics as Package;
use App\Support\M74QuestionSupplement as Supplement;
use App\Support\TheorySentenceBuilderAcceptance;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Pure synthetic bank completion and exact frozen successor checks; no working DB or HTTP. */
class M74BankRevisionTest extends TestCase
{
    private const OLD_MASTER = 'docs/content/m74-other-pronouns-basics.v1.0.0.json';
    private const OLD_PATCH = 'database/content-patches/m74-other-pronouns-basics.v1.json';
    private const OLD_MANIFEST = 'docs/content/m74-source-checksums.v1.json';
    private const OLD_MASTER_SHA = '9d7a4183793d4005c59ef400ef191990a2fe7ba07a32fc4b363eb457f1618671';
    private const OLD_MANIFEST_SHA = 'f203ed1c8c41b8225a5b1b56063f51d50eadf92490a4527f01b1760cf3f957dc';

    private function sourceJson(string $relative): array
    {
        return json_decode(file_get_contents(base_path($relative)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    private function binding(): array
    {
        return ['owner' => Supplement::OWNER, 'level' => 'B1', 'category_id' => 45,
            'source_id' => 321, 'practice_uuid' => '19b7df8b-7c55-5725-a78f-f5935f988baa'];
    }

    /** Explicitly synthetic original rows; tests do not read a working option table. */
    private function optionMatches(): array
    {
        $matches = [];
        foreach (['The', 'others', 'are', 'outside', 'other', 'another', 'is', 'them'] as $i => $token) {
            $matches[$token] = [['id' => 740001 + $i, 'option' => strtolower($token),
                'created_at' => '2000-01-01 00:00:00', 'updated_at' => '2000-01-01 00:00:00']];
        }
        return $matches;
    }

    public function test_one_question_source_is_exact_and_all_historical_v1_artifacts_keep_their_frozen_bytes(): void
    {
        $source = Supplement::load(); Supplement::validate($source);
        self::assertSame('1.0.1', $source['version']); self::assertSame('frozen', $source['status']);
        self::assertSame('Database\\Seeders\\V3\\Polyglot\\M74AnotherRemainderSupplementSeeder', Supplement::BANK);
        self::assertNotSame(Supplement::OLD_BANK, Supplement::BANK);
        self::assertSame([81, 16, 45, 321], [Supplement::PAGE_ID, Supplement::PAGE_CATEGORY_ID, Supplement::CATEGORY_ID, Supplement::SOURCE_ID]);
        self::assertSame(['m74-another-b1-remainder-01'], Supplement::UUIDS);
        self::assertSame(['The', 'others', 'are', 'outside'], $source['questions'][0]['tokens_correct']);
        self::assertSame(['other', 'another', 'is', 'them'], $source['questions'][0]['distractors']);
        self::assertSame('The others are outside.', $source['questions'][0]['canonical_en']);
        self::assertSame('Решта надворі.', $source['questions'][0]['translation_uk']);
        self::assertStringContainsString('п’ятеро', $source['questions'][0]['question']);
        self::assertStringContainsString('усі троє', $source['questions'][0]['question']);
        self::assertSame(32707, $source['existing_taxonomy']['captured_question_id']);
        self::assertSame(45, $source['existing_taxonomy']['question_category_id']);
        self::assertSame(321, $source['existing_taxonomy']['source_id']);
        self::assertSame(0, $source['scope']['new_option_rows']);
        self::assertSame(1, $source['scope']['new_question_rows']);
        self::assertSame(self::OLD_MASTER_SHA, hash_file('sha256', base_path(self::OLD_MASTER)));
        self::assertSame(self::OLD_MANIFEST_SHA, hash_file('sha256', base_path(self::OLD_MANIFEST)));
        $old = $this->sourceJson(self::OLD_MANIFEST);
        foreach ($old['artifacts'] as $artifact) {
            self::assertSame($artifact['sha256'], hash_file('sha256', base_path($artifact['path'])), $artifact['path']);
            self::assertSame($artifact['bytes'], filesize(base_path($artifact['path'])));
        }
        // Historical active_definitions are not asserted as the active successor:
        // one new linked_practice bank is an explicitly reviewed source-body change.
    }

    public function test_pure_insert_projection_reuses_eight_exact_options_and_unchanged_m51_materialization(): void
    {
        $source = Supplement::load(); $matches = $this->optionMatches(); $originals = $matches;
        DB::flushQueryLog(); DB::enableQueryLog();
        $projection = Supplement::project($source, $this->binding(), $matches);
        self::assertSame($originals, $matches); self::assertSame([], DB::getQueryLog());
        $expected = ['questions' => 1, 'question_options' => 0, 'question_answers' => 4,
            'question_option_question' => 8, 'question_theory_text_blocks' => 1];
        $ids = [];
        foreach (Supplement::ROW_COLUMNS as $table => $_) {
            self::assertCount($expected[$table], $projection[$table]); $ids[$table] = [];
            foreach ($projection[$table] as $n => $row) { $ids[$table][$row['key']] = 750001 + $n; }
        }
        self::assertCount(8, $projection['reused_options']); $originalById = [];
        foreach ($matches as $lookup) { $originalById[$lookup[0]['id']] = $lookup[0]; }
        foreach ($projection['reused_options'] as $row) {
            self::assertSame($originalById[$row['id']], $row['row']);
            self::assertArrayNotHasKey('fields', $row);
        }
        $rows = M51QuestionSupplement::materialize($projection, $ids, '2000-01-01 00:00:00');
        foreach (Supplement::ROW_COLUMNS as $table => $columns) {
            self::assertCount($expected[$table], $rows[$table]);
            foreach ($rows[$table] as $row) { self::assertSame($columns, array_keys($row)); }
        }
        self::assertSame(Supplement::BANK, $rows['questions'][0]['seeder']);
        self::assertSame(45, $rows['questions'][0]['category_id']); self::assertSame(321, $rows['questions'][0]['source_id']);
        self::assertNull($rows['questions'][0]['theory_text_block_uuid']);
        self::assertSame('19b7df8b-7c55-5725-a78f-f5935f988baa', $rows['question_theory_text_blocks'][0]['text_block_uuid']);
        self::assertSame(['a1', 'a2', 'a3', 'a4'], array_column($rows['question_answers'], 'marker'));
        self::assertSame([], DB::getQueryLog()); M51QuestionSupplement::assertOriginalsUnchanged($originals, $matches);
    }

    public function test_missing_ambiguous_new_casing_and_foreign_binding_proposals_are_rejected_without_queries(): void
    {
        $source = Supplement::load(); DB::flushQueryLog(); DB::enableQueryLog();
        foreach (['missing', 'ambiguous', 'new-option', 'wrong-value', 'wrong-case', 'foreign-binding'] as $case) {
            $matches = $this->optionMatches(); $binding = $this->binding();
            if ($case === 'missing') { unset($matches['others']); }
            if ($case === 'ambiguous') { $matches['others'][] = $matches['others'][0]; }
            if ($case === 'new-option') { $matches['others'] = []; }
            if ($case === 'wrong-value') { $matches['others'][0]['option'] = 'other'; }
            if ($case === 'wrong-case') { $matches['outside'][0]['option'] = 'OUTSIDE'; }
            if ($case === 'foreign-binding') { $binding['category_id'] = 16; }
            $this->refuses(fn () => Supplement::project($source, $binding, $matches), $case);
        }
        foreach (['duplicate-id', 'incomplete-row', 'unknown-row-field', 'extra-token'] as $case) {
            $matches = $this->optionMatches();
            if ($case === 'duplicate-id') { $matches['others'][0]['id'] = $matches['The'][0]['id']; }
            if ($case === 'incomplete-row') { unset($matches['others'][0]['updated_at']); }
            if ($case === 'unknown-row-field') { $matches['others'][0]['new_field'] = null; }
            if ($case === 'extra-token') { $matches['unknown'] = $matches['others']; }
            $this->refuses(fn () => Supplement::project($source, $this->binding(), $matches), $case);
        }
        self::assertSame([], DB::getQueryLog());
    }

    public function test_source_scope_cannot_be_widened_or_relabelled_to_an_existing_bank(): void
    {
        $source = Supplement::load();
        foreach (['bank', 'question-count', 'new-options', 'old-update', 'taxonomy', 'canonical', 'tokens', 'question-context', 'clone-key', 'clone-condition'] as $case) {
            $bad = $source;
            if ($case === 'bank') { $bad['seeder']['class'] = Supplement::OLD_BANK; }
            if ($case === 'question-count') { $bad['questions'][] = $bad['questions'][0]; }
            if ($case === 'new-options') { $bad['scope']['new_option_rows'] = 1; }
            if ($case === 'old-update') { $bad['scope']['old_question_updates'] = 1; }
            if ($case === 'taxonomy') { $bad['existing_taxonomy']['question_category_id'] = 16; }
            if ($case === 'canonical') { $bad['questions'][0]['canonical_en'] = 'The other are outside.'; }
            if ($case === 'tokens') { $bad['questions'][0]['tokens_correct'][1] = 'other'; }
            if ($case === 'question-context') { $bad['questions'][0]['question'] = 'Інші люди надворі.'; }
            if ($case === 'clone-key') { $bad['selection_revision']['existing_question_clone_revision']['unchanged_word_key'] = 'Can I have the other tea'; }
            if ($case === 'clone-condition') { $bad['selection_revision']['existing_question_clone_revision']['new_visible_condition_uk'] .= '»'; }
            $this->refuses(fn () => Supplement::validate($bad), $case);
        }
    }

    public function test_active_successor_changes_only_another_linkage_and_master_version_not_own_practice_or_teaching(): void
    {
        [, $patch, $master, $contract] = Package::load();
        self::assertSame('1.0.1', $master['version']); self::assertNotSame(self::OLD_MASTER, Package::MASTER);
        $legacy = $this->sourceJson(self::OLD_MASTER); $restored = $master;
        self::assertSame([Supplement::OLD_BANK, Supplement::BANK], $restored['lessons'][0]['blocks'][6]['data']['linked_practice']['seeder_classes']);
        array_pop($restored['lessons'][0]['blocks'][6]['data']['linked_practice']['seeder_classes']);
        $restored['version'] = '1.0.0'; self::assertSame($legacy, $restored);
        $oldPatch = $this->sourceJson(self::OLD_PATCH); $body = $patch['targets'][0]['after']['page']['blocks'][6]['body'];
        $newData = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([Supplement::OLD_BANK, Supplement::BANK], $newData['linked_practice']['seeder_classes']);
        array_pop($newData['linked_practice']['seeder_classes']);
        self::assertSame(json_decode($oldPatch['targets'][0]['after']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR), $newData);
        $patch['targets'][0]['after']['page']['blocks'][6]['body'] = $oldPatch['targets'][0]['after']['page']['blocks'][6]['body'];
        self::assertSame($oldPatch, $patch, 'Only the exact Another practice body changes in source AFTER.');
        $oldContract = $this->sourceJson(M74BankRevision::LEGACY_CONTRACT); $restoredContract = $contract;
        self::assertSame([Supplement::OLD_BANK, Supplement::BANK], $restoredContract['targets'][0]['bank_seeders']);
        array_pop($restoredContract['targets'][0]['bank_seeders']); self::assertSame($oldContract, $restoredContract);
        self::assertSame(M74BankRevision::CONTRACT, Package::CONTRACT);
    }

    private function fixtureRoot(): string
    {
        $root = storage_path('framework/testing/m74-bank-revision-'.bin2hex(random_bytes(8)));
        IsolatedTestEnvironment::assertOwnedPath(dirname($root));
        if (!mkdir($root, 0777, true)) { throw new RuntimeException('Cannot create owned contract fixture.'); }
        return $root;
    }

    private function fixtureFile(string $root, string $relative, string $body): string
    {
        $path = $root.'/'.$relative;
        if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0777, true)) { throw new RuntimeException('Cannot create owned contract fixture parent.'); }
        IsolatedTestEnvironment::assertOwnedPath(dirname($path));
        if (file_put_contents($path, $body) !== strlen($body)) { throw new RuntimeException('Cannot write owned isolated fixture.'); }
        return $path;
    }

    public function test_contract_revision_is_exact_predecessor_successor_and_foreign_paths_keep_their_original_contract(): void
    {
        self::assertSame('9511dd71f6e56e01bc446b3b7b0ea2dfa5ed7c075a1f6d65b0cf1ea833a29a1d', M74BankRevision::LEGACY_CONTRACT_SHA);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', M74BankRevision::CONTRACT_SHA);
        self::assertSame(str_replace('\\', '/', base_path(M74BankRevision::CONTRACT)),
            str_replace('\\', '/', M74BankRevision::contractFile(base_path(), base_path(M74BankRevision::LEGACY_CONTRACT))));
        $foreign = base_path('docs/content/theory-sentence-builders/m73-generic-irregular-collective.v1.json');
        self::assertSame($foreign, M74BankRevision::contractFile(base_path(), $foreign));
        $root = $this->fixtureRoot();
        $oldBytes = file_get_contents(base_path(M74BankRevision::LEGACY_CONTRACT));
        $nextBytes = file_get_contents(base_path(M74BankRevision::CONTRACT));
        $old = $this->fixtureFile($root, M74BankRevision::LEGACY_CONTRACT, $oldBytes);
        $next = $this->fixtureFile($root, M74BankRevision::CONTRACT, $nextBytes);
        self::assertSame($next, M74BankRevision::contractFile($root, $old));
        $this->fixtureFile($root, M74BankRevision::LEGACY_CONTRACT, $oldBytes.' ');
        $this->refuses(fn () => M74BankRevision::contractFile($root, $old), 'stale predecessor');
        $this->fixtureFile($root, M74BankRevision::LEGACY_CONTRACT, $oldBytes);
        $this->fixtureFile($root, M74BankRevision::CONTRACT, $nextBytes.' ');
        $this->refuses(fn () => M74BankRevision::contractFile($root, $old), 'stale successor');
    }

    public function test_registry_preserves_all_old_contracts_and_discovers_future_owners_without_a_145_cap(): void
    {
        $actual = TheorySentenceBuilderAcceptance::contracts(base_path()); $prior = [];
        foreach (glob(base_path('docs/content/theory-sentence-builders/*.json')) as $file) {
            if (str_replace('\\', '/', $file) === str_replace('\\', '/', base_path(M74BankRevision::LEGACY_CONTRACT))) { continue; }
            $source = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
            foreach ($source['targets'] as $target) { $prior[$target['identity']] = $target; }
        }
        self::assertGreaterThanOrEqual(142, count($prior), 'Finite M73 predecessor owners retained; not a global registry cap.');
        foreach ($prior as $owner => $target) { self::assertSame($target, $actual[$owner]); }
        foreach (Package::OWNERS as $i => $owner) {
            self::assertSame(Package::LEVELS[$i], $actual[$owner]['level']);
            if ($i === 0) { self::assertSame([Supplement::OLD_BANK, Supplement::BANK], $actual[$owner]['bank_seeders']); }
        }
        $root = $this->fixtureRoot(); $owner = 'Database\\Seeders\\Page_V3\\FutureRevisionFixture\\FuturePronounsTheorySeeder';
        $definition = TheorySentenceBuilderAcceptance::definitionPath($owner);
        $target = ['identity' => $owner, 'definition' => $definition,
            'local_url' => 'http://gramlyze.loc/theory/future-pronouns/future-native-owner', 'level' => null,
            'bank_seeders' => ['Database\\Seeders\\V3\\Polyglot\\FuturePronounsSeeder']];
        $this->fixtureFile($root, 'docs/content/theory-sentence-builders/future.v1.json', json_encode(['schema_version' => 1, 'targets' => [$target]], JSON_THROW_ON_ERROR));
        $this->fixtureFile($root, $definition, '{}');
        $this->fixtureFile($root, 'database/content-patches/future-native.v1.json', json_encode(['targets' => [['identity' => $owner, 'after' => ['page' => ['locale' => 'uk']]]]], JSON_THROW_ON_ERROR));
        $future = TheorySentenceBuilderAcceptance::discoverTargets($root);
        self::assertCount(1, $future); self::assertSame($owner, $future[0]['identity']); self::assertSame([], $future[0]['errors']);
    }

    public function test_distinct_new_bank_uses_exact_source_override_without_requiring_or_instantiating_a_seeder_class(): void
    {
        [, , , $contract] = Package::load(); $paths = M74ContentPatch::bankPaths($contract);
        self::assertContains(Supplement::SOURCE, $paths);
        $definition = 'database/seeders/V3/Polyglot/M74AnotherRemainderSupplementSeeder/definition.json';
        self::assertNotContains($definition, $paths); self::assertFileDoesNotExist(base_path($definition));
        self::assertFalse(class_exists(Supplement::BANK, false), 'Bank identity is a SQL filter string, not an instantiated runtime seeder.');
        foreach ($paths as $path) { self::assertFileExists(base_path($path)); }
    }
}
