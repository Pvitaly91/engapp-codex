<?php

namespace Tests\Unit;

use App\Services\M84QuestionInsertScope;
use App\Services\TheoryNativeContentPatch;
use App\Support\ComposeTokenCase;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M84QuestionSupplement as Supplement;
use App\Support\M84WordOrderHaveGot as Package;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

/** Pure projection/materialization and isolated policy calls. No working DB or private receipts. */
class M84QuestionSupplementTest extends TestCase
{
    private function source(): array { return Supplement::load(dirname(__DIR__, 2)); }

    private function refused(callable $call, ?string $message = null): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $e) { $caught = $e; }
        self::assertInstanceOf(RuntimeException::class, $caught);
        if ($message !== null) { self::assertStringContainsString($message, $caught->getMessage()); }
    }

    /** Invented complete NOCASE lookups; original option labels must satisfy every native position. */
    private function optionMatches(array $source): array
    {
        $requests = $requirements = [];
        foreach ($source['banks'] as $bank) {
            foreach ($bank['questions'] as $q) {
                foreach ([...$q['tokens_correct'], ...$q['distractors']] as $token) { $requests[$token] = true; }
                foreach ($q['tokens_correct'] as $position => $token) { $requirements[mb_strtolower($token)][] = [$position, $token]; }
            }
        }
        $byFold = []; $next = 1000;
        foreach (array_keys($requests) as $token) {
            $fold = mb_strtolower($token);
            if (isset($byFold[$fold])) { continue; }
            $possible = array_values(array_unique([$fold, $token, ucfirst($fold)])); $label = null;
            foreach ($possible as $candidate) {
                $valid = true;
                foreach ($requirements[$fold] ?? [] as [$position, $expected]) {
                    $normalized = ComposeTokenCase::normalize($position === 0 ? [$candidate] : ['I', $candidate]);
                    if (end($normalized) !== $expected) { $valid = false; break; }
                }
                if ($valid) { $label = $candidate; break; }
            }
            self::assertNotNull($label, 'No one unchanged shared option label can satisfy all required positions: '.$token);
            $byFold[$fold] = ['id' => $next++, 'option' => $label, 'created_at' => '2001-02-03 04:05:06', 'updated_at' => '2001-02-03 04:05:06'];
        }
        $out = [];
        foreach (array_keys($requests) as $token) { $out[$token] = [$byFold[mb_strtolower($token)]]; }
        return $out;
    }

    private function allocate(array $projection): array
    {
        $ids = [];
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $ids[$table] = [];
            foreach ($projection[$table] as $n => $row) { $ids[$table][$row['key']] = 100000 + $n; }
        }
        return $ids;
    }

    private function poolPolicy(): M84SupplementPoolPolicyHarness
    {
        // This pure protected method reads only the supplied arrays; parent DB constructor is deliberately not invoked.
        return (new ReflectionClass(M84SupplementPoolPolicyHarness::class))->newInstanceWithoutConstructor();
    }

    public function test_frozen_source_has_25_unique_complete_questions_in_five_exact_focus_banks(): void
    {
        $s = $this->source(); Supplement::validate($s); $uids = $sentences = $conditions = [];
        self::assertSame('frozen', $s['status']); self::assertSame(['A1', 'A1', 'A2', 'A2', 'B2'], array_column($s['banks'], 'level'));
        self::assertSame(Supplement::BANKS, array_column($s['banks'], 'bank_seeder'));
        self::assertSame(array_slice(Package::OWNERS, 0, 5), array_column($s['banks'], 'owner'));
        foreach ($s['banks'] as $bank) {
            self::assertCount(5, $bank['questions']); self::assertSame(4, $bank['type']);
            foreach ($bank['questions'] as $q) {
                $uids[] = $q['source_uid']; $sentences[] = $q['canonical_en']; $conditions[] = $q['question_uk'];
                self::assertSame($q['tokens_correct'], ComposeTokenCase::normalize($q['tokens_correct']));
                self::assertMatchesRegularExpression('/[.!?]$/D', $q['canonical_en']);
                self::assertGreaterThanOrEqual(3, count($q['tokens_correct']));
                $correct = $q['tokens_correct']; $shuffled = $q['tokens_shuffled']; sort($correct); sort($shuffled);
                self::assertSame($correct, $shuffled); self::assertNotSame($q['tokens_correct'], $q['tokens_shuffled']);
                foreach ($q['tokens_correct'] as $token) { self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $token))); }
                foreach (['question_uk', 'context_uk', 'criterion_uk', 'translation_uk', 'ambiguity_note_uk'] as $field) {
                    self::assertNotSame('', trim($q[$field])); self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u', $q[$field]);
                    self::assertDoesNotMatchRegularExpression('/<[^>]+>|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu', $q[$field]);
                }
                self::assertSame(implode(' ', array_map(static fn ($f) => $q[$f], ['question_uk', 'context_uk', 'criterion_uk', 'ambiguity_note_uk'])), Supplement::condition($q));
            }
        }
        self::assertSame(Supplement::UUIDS, $uids); self::assertCount(25, array_unique($sentences)); self::assertCount(25, array_unique($conditions));
    }

    public function test_question_focus_does_not_rewrite_sql_null_hero_or_practice_levels(): void
    {
        [,, $m] = Package::load(dirname(__DIR__, 2)); $bindings = Supplement::bindings();
        foreach ($bindings as $i => $binding) {
            self::assertSame(48, $binding['page_category_id']); self::assertSame(45, $binding['category_id']);
            self::assertSame(Supplement::LEVELS[$i], $binding['level']); self::assertSame(Supplement::SOURCE_IDS[$i], $binding['source_id']);
            self::assertNull($m['lessons'][$i]['blocks'][0]['level']);
            self::assertNull($m['lessons'][$i]['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['level']);
            self::assertSame(Package::OWNERS[$i], $binding['owner']);
        }
        self::assertSame([28, 29, 30, 31, 32], Supplement::SOURCE_IDS);
    }

    public function test_source_is_not_misrepresented_as_human_review(): void
    {
        $walk = function (array $values) use (&$walk): void {
            foreach ($values as $key => $value) {
                if (in_array($key, ['human_review', 'human_reviewed', 'human_approved'], true)) { self::assertFalse($value); }
                if ($key === 'review_kind') { self::assertNotSame('human_review', $value); self::assertNotSame('human_approved', $value); }
                if (is_array($value)) { $walk($value); }
            }
        };
        $walk($this->source()); self::assertTrue(true);
    }

    public function test_pure_projection_and_materialization_insert_only_25_exact_complete_families(): void
    {
        $s = $this->source(); $original = $s; $matches = $this->optionMatches($s); $originalMatches = $matches;
        $p = Supplement::project($s, Supplement::bindings(), $matches);
        self::assertSame($original, $s); self::assertSame($originalMatches, $matches);
        self::assertCount(25, $p['questions']); self::assertCount(25, $p['question_theory_text_blocks']);
        self::assertSame([5, 5, 5, 5, 5], array_values(array_count_values(array_column(array_column($p['questions'], 'fields'), 'seeder'))));
        self::assertSame([], $p['question_options']); self::assertNotEmpty($p['reused_options']);
        self::assertArrayNotHasKey('saved_grammar_test_questions', $p); self::assertArrayNotHasKey('question_hints', $p); self::assertArrayNotHasKey('question_variants', $p);
        $rows = Rows::materialize($p, $this->allocate($p), '2002-03-04 05:06:07'); Rows::assertApplied($rows, $rows);
        self::assertSame(array_keys(Rows::ROW_COLUMNS), array_keys($rows)); self::assertCount(25, $rows['questions']);
        foreach ($rows as $table => $family) {
            foreach ($family as $row) { self::assertSame(Rows::ROW_COLUMNS[$table], array_keys($row)); }
        }
        foreach ($s['banks'] as $i => $bank) {
            foreach ($bank['questions'] as $q) {
                $row = array_values(array_filter($rows['questions'], static fn ($r) => $r['uuid'] === $q['source_uid']))[0];
                self::assertSame(Supplement::condition($q), $row['question']); self::assertSame($bank['level'], $row['level']);
                self::assertSame(Supplement::BANKS[$i], $row['seeder']); self::assertSame(Supplement::SOURCE_IDS[$i], $row['source_id']);
            }
        }
    }

    public function test_repeated_correct_word_instances_retain_two_distinct_markers_and_one_shared_option(): void
    {
        $s = $this->source(); $q = &$s['banks'][0]['questions'][0];
        $q['canonical_en'] = 'They say they have a map.'; $q['tokens_correct'] = ['They', 'say', 'they', 'have', 'a map.'];
        $q['tokens_shuffled'] = ['a map.', 'they', 'say', 'They', 'have']; unset($q);
        $p = Supplement::project($s, Supplement::bindings(), $this->optionMatches($s));
        $answers = array_values(array_filter($p['question_answers'], static fn ($r) => $r['question_key'] === Supplement::UUIDS[0]));
        self::assertCount(5, $answers); self::assertSame($answers[0]['option_key'], $answers[2]['option_key']);
        self::assertSame(['a1', 'a2', 'a3', 'a4', 'a5'], array_column(array_column($answers, 'fields'), 'marker'));
    }

    public function test_missing_ambiguous_colliding_or_case_incompatible_option_lookups_are_refused(): void
    {
        $s = $this->source(); $matches = $this->optionMatches($s); $keys = array_keys($matches);
        $bad = $matches; unset($bad[$keys[0]]); $this->refused(fn () => Supplement::project($s, Supplement::bindings(), $bad), 'missing');
        $bad = $matches; $bad[$keys[0]][] = $bad[$keys[0]][0]; $this->refused(fn () => Supplement::project($s, Supplement::bindings(), $bad), 'ambiguous');
        $first = $keys[0]; $second = null;
        foreach ($keys as $key) { if (mb_strtolower($key) !== mb_strtolower($first)) { $second = $key; break; } }
        self::assertNotNull($second); $bad = $matches; $bad[$second][0]['id'] = $bad[$first][0]['id'];
        $this->refused(fn () => Supplement::project($s, Supplement::bindings(), $bad), 'collision');
        $changedFold = null;
        foreach ($s['banks'] as $bank) {
            foreach ($bank['questions'] as $q) {
                foreach ($q['tokens_correct'] as $position => $token) {
                    $upper = mb_strtoupper($token); $normalized = ComposeTokenCase::normalize($position === 0 ? [$upper] : ['I', $upper]);
                    if (end($normalized) !== $token) { $changedFold = mb_strtolower($token); break 3; }
                }
            }
        }
        self::assertNotNull($changedFold); $bad = $matches;
        foreach ($bad as $token => &$rows) { if (mb_strtolower($token) === $changedFold) { $rows[0]['option'] = mb_strtoupper($rows[0]['option']); } } unset($rows);
        $this->refused(fn () => Supplement::project($s, Supplement::bindings(), $bad), 'casing');
    }

    public function test_wrong_scope_locale_range_uid_context_markup_and_token_loss_are_rejected(): void
    {
        foreach (['owner', 'bank', 'locale', 'range', 'uid', 'context', 'marker', 'shuffle', 'long-token', 'duplicate'] as $kind) {
            $s = $this->source();
            if ($kind === 'owner') { $s['banks'][4]['owner'] = 'Database\\Seeders\\Page_V3\\BasicGrammar\\AdvancedFrontingAndEmphasisTheorySeeder'; }
            elseif ($kind === 'bank') { $s['banks'][4]['bank_seeder'] = Supplement::OLD_BANKS[4]; }
            elseif ($kind === 'locale') { $s['locale'] = 'en'; }
            elseif ($kind === 'range') { $s['banks'][2]['level'] = 'A2–B1'; }
            elseif ($kind === 'uid') { $s['banks'][4]['questions'][0]['source_uid'] = Supplement::UUIDS[0]; }
            elseif ($kind === 'context') { $s['banks'][0]['questions'][0]['context_uk'] = ''; }
            elseif ($kind === 'marker') { $s['banks'][0]['questions'][0]['criterion_uk'] = 'Уведи {a1}.'; }
            elseif ($kind === 'shuffle') { array_pop($s['banks'][0]['questions'][0]['tokens_shuffled']); }
            elseif ($kind === 'long-token') { $s['banks'][0]['questions'][0]['tokens_correct'][0] = 'One two three four'; }
            else { $s['banks'][0]['questions'][1]['canonical_en'] = $s['banks'][0]['questions'][0]['canonical_en']; $s['banks'][0]['questions'][1]['tokens_correct'] = $s['banks'][0]['questions'][0]['tokens_correct']; $s['banks'][0]['questions'][1]['tokens_shuffled'] = $s['banks'][0]['questions'][0]['tokens_shuffled']; }
            $this->refused(fn () => Supplement::validate($s));
        }
    }

    public function test_original_pool_policy_preserves_all_sql_null_levels_and_refuses_original_mutation(): void
    {
        $policy = $this->poolPolicy();
        foreach (Supplement::OWNERS as $i => $owner) {
            $questions = [];
            foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $n => $level) {
                $questions[] = ['id' => 1 + $n, 'uuid' => 'old-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT), 'seeder' => Supplement::OLD_BANKS[$i], 'type' => '4', 'level' => $level, 'question' => 'Незмінна початкова умова '.$n];
            }
            $contract = ['identity' => $owner, 'level' => null, 'bank_seeders' => [Supplement::OLD_BANKS[$i], Supplement::BANKS[$i]]];
            $baseline = ['relations' => ['questions' => $questions]]; self::assertTrue($policy->assertPool($questions, $contract, $baseline));
            $bad = $questions; $bad[0]['question'] = 'Непогоджена зміна старої умови.'; $this->refused(fn () => $policy->assertPool($bad, $contract, $baseline), 'original');
            $badContract = $contract; $badContract['level'] = Package::DISPLAY_LEVELS[$i]; $this->refused(fn () => $policy->assertPool($questions, $badContract, $baseline));
            $badContract = $contract; unset($badContract['level']); $this->refused(fn () => $policy->assertPool($questions, $badContract, $baseline));
            $bad = $questions; $bad[] = ['id' => 100, 'uuid' => Supplement::UUIDS[$i * 5], 'seeder' => Supplement::BANKS[$i], 'type' => '4', 'level' => Supplement::LEVELS[$i]];
            $this->refused(fn () => $policy->assertPool($bad, $contract, $baseline), 'excluded');
        }
        self::assertFalse($policy->assertPool([], ['identity' => Package::OWNERS[5]], null));
    }

    public function test_materialized_families_reject_partial_rows_extra_memberships_and_reused_id_allocation(): void
    {
        $s = $this->source(); $p = Supplement::project($s, Supplement::bindings(), $this->optionMatches($s)); $ids = $this->allocate($p);
        $expected = Rows::materialize($p, $ids, '2002-03-04 05:06:07');
        $actual = $expected; array_pop($actual['question_theory_text_blocks']); $this->refused(fn () => Rows::assertApplied($expected, $actual));
        $actual = $expected; $actual['saved_grammar_test_questions'] = []; $this->refused(fn () => Rows::assertApplied($expected, $actual));
        $badIds = $ids; $reused = $p['reused_options'][0]; $badIds['question_options'][$reused['key']] = $reused['id'];
        $this->refused(fn () => Rows::materialize($p, $badIds, '2002-03-04 05:06:07'));
        $originals = ['questions' => [['id' => 1, 'question' => 'Стара умова.']], 'saved_grammar_test_questions' => [['question_uuid' => 'old-question']]];
        Rows::assertOriginalsUnchanged($originals, $originals); $changed = $originals; $changed['saved_grammar_test_questions'][] = ['question_uuid' => Supplement::UUIDS[0]];
        $this->refused(fn () => Rows::assertOriginalsUnchanged($originals, $changed));
    }
}

/** Exposes only the actual pure pool policy; no connection, evidence or writer method is called. */
class M84SupplementPoolPolicyHarness extends TheoryNativeContentPatch
{
    use M84QuestionInsertScope;
    public const BEFORE_FILE = 'not-used-in-pure-policy-test.json';
    public const BEFORE_SHA = 'not-used-in-pure-policy-test';
    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array { return []; }
    public function assertPool(array $questions, array $contract, ?array $baseline): bool { return $this->assertSupplementPools($questions, $contract, $baseline); }
}
