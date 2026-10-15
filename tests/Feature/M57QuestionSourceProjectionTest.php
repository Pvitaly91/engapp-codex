<?php

namespace Tests\Feature;

use App\Support\ComposeTokenCase;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M57QuestionSupplement as Supplement;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

/** Pure editorial/row projection; no working environment, SQL connection, writes or generated UI expectations. */
class M57QuestionSourceProjectionTest extends TestCase
{
    private const KEYS = [
        'The doors are being painted this week.',
        'I am not being interviewed now.',
        'Are the lights being checked?',
        'Where is the carpet being cleaned?',
        'The rooms are being cleaned every day this week.',
        'When we arrived, the stage was being repaired.',
        'The forms were not being checked at noon.',
        'Were you being interviewed at ten?',
        'What was being carried upstairs?',
        'While the hall was being cleaned, the chairs were being moved.',
        'The letter has already been sent.',
        'Have the windows been cleaned yet?',
        'This room has never been used before.',
        'The doors have been kept locked for two days.',
        'The hall has been cleaned twice this week.',
    ];

    private function bindings(): array
    {
        return array_map(static fn ($i) => ['owner' => Supplement::OWNERS[$i], 'level' => 'B1',
            'category_id' => Supplement::CATEGORY_IDS[$i], 'source_id' => Supplement::SOURCE_IDS[$i],
            'practice_uuid' => sprintf('00000000-0000-4000-8000-%012d', $i + 1)], [0, 1, 2]);
    }

    private function optionMatches(array $source): array
    {
        $rows = []; $matches = []; $id = 1000;
        foreach ($source['banks'] as $bank) {
            foreach ($bank['questions'] as $question) {
                foreach ($question['options'] as $token) {
                    $lower = mb_strtolower($token);
                    $rows[$lower] ??= ['id' => ++$id, 'option' => $lower,
                        'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'];
                    $matches[$token] = [$rows[$lower]];
                }
            }
        }
        return $matches;
    }

    private function refused(callable $call): void
    {
        $caught = null; try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught);
    }

    public function test_frozen_fifteen_have_independently_reviewed_keys_full_words_and_no_answer_hints(): void
    {
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        $source = Supplement::load(); $questions = array_merge(...array_column($source['banks'], 'questions'));
        self::assertSame(self::KEYS, array_column($questions, 'target_text'));
        self::assertSame(Supplement::UUIDS, array_column($questions, 'uuid')); self::assertCount(15, array_unique(array_column($questions, 'uuid')));
        self::assertSame([111, 108, 112], array_column($source['banks'], 'page_id'));
        self::assertSame([45, 45, 45], array_column($source['banks'], 'category_id'));
        self::assertSame([346, 347, 344], array_column($source['banks'], 'source_id'));
        foreach ($questions as $question) {
            self::assertSame([], $question['hints']); self::assertSame([], $question['verb_hints']);
            self::assertSame('B1', $question['level']); self::assertSame(4, $question['type']);
            self::assertStringNotContainsString($question['target_text'], $question['question']);
            self::assertStringContainsString($question['translation_uk'], $question['question']);
            self::assertSame($question['tokens_correct'], array_values($question['answers']));
            self::assertSame($question['tokens_correct'], ComposeTokenCase::normalize($question['tokens_correct']));
            self::assertStringNotContainsString('in this context', $question['target_text']);
            self::assertStringNotContainsString('in this passive context', $question['target_text']);
        }
        self::assertSame(15, $source['scope']['new_questions']);
    }

    public function test_insert_only_projection_reuses_original_options_and_retains_repeated_answer_instances(): void
    {
        $source = Supplement::load(); $matches = $this->optionMatches($source); $before = $matches;
        $projection = Supplement::project($source, $this->bindings(), $matches);
        self::assertSame($before, $matches); self::assertCount(15, $projection['questions']);
        self::assertCount(15, $projection['question_theory_text_blocks']); self::assertSame([], $projection['question_options']);
        self::assertCount(15, $projection['translations']); self::assertCount(15, $projection['provenance']);
        $parallel = array_values(array_filter($projection['question_answers'], static fn ($r) => $r['question_key'] === 'm57-pac-b1-05'));
        self::assertCount(13, $parallel);
        foreach (['being' => 2, 'the' => 2] as $token => $count) {
            self::assertCount($count, array_filter($parallel, static fn ($r) => $r['option_key'] === 'option-'.hash('sha256', $token)));
        }
        self::assertSame(Supplement::BANKS, array_values(array_unique(array_column(array_column($projection['questions'], 'fields'), 'seeder'))));
        foreach ($projection['questions'] as $row) { self::assertNull($row['fields']['theory_text_block_uuid']); self::assertSame('4', $row['fields']['type']); }
        $originalById = [];
        foreach ($before as $lookup) { foreach ($lookup as $row) { $originalById[$row['id']] = $row; } }
        foreach ($projection['reused_options'] as $row) { self::assertSame($originalById[$row['id']], $row['row']); }
        $ids = []; foreach (Rows::ROW_COLUMNS as $table => $_) {
            $ids[$table] = []; $next = 900000;
            foreach ($projection[$table] as $row) { $ids[$table][$row['key']] = ++$next; }
        }
        $materialized = Rows::materialize($projection, $ids, '2026-10-15 12:00:00');
        Rows::assertApplied($materialized, $materialized);
        self::assertCount(15, $materialized['questions']); self::assertSame([], $materialized['question_options']);
        self::assertSame($before, $matches);
    }

    public function test_foreign_owner_flat_ancestry_wrong_cefr_type_short_or_duplicate_pool_and_hints_refuse(): void
    {
        $original = Supplement::load();
        foreach (['owner', 'flat-url', 'cefr', 'type', 'short', 'duplicate', 'tokens', 'hint', 'foreign-id', 'write-scope'] as $case) {
            $source = $original;
            if ($case === 'owner') { $source['banks'][0]['owner'] = 'ForeignOwner'; }
            elseif ($case === 'flat-url') { $source['banks'][0]['local_url'] = str_replace('/passive-voice-tenses', '', $source['banks'][0]['local_url']); }
            elseif ($case === 'cefr') { $source['banks'][0]['questions'][0]['level'] = 'A2'; }
            elseif ($case === 'type') { $source['banks'][0]['questions'][0]['type'] = 0; }
            elseif ($case === 'short') { array_pop($source['banks'][0]['questions']); }
            elseif ($case === 'duplicate') { $source['banks'][0]['questions'][1]['uuid'] = $source['banks'][0]['questions'][0]['uuid']; }
            elseif ($case === 'tokens') { array_pop($source['banks'][1]['questions'][4]['tokens_correct']); }
            elseif ($case === 'hint') { $source['banks'][0]['questions'][0]['hints'] = [self::KEYS[0]]; }
            elseif ($case === 'foreign-id') { $source['banks'][1]['source_id'] = 344; }
            elseif ($case === 'write-scope') { $source['scope']['old_question_updates'] = 1; }
            $this->refused(fn () => Supplement::validate($source)); self::assertSame($original, Supplement::load());
        }
    }

    public function test_missing_ambiguous_foreign_or_case_changed_shared_options_refuse_without_mutation(): void
    {
        $source = Supplement::load(); $original = $this->optionMatches($source);
        foreach (['missing', 'ambiguous', 'foreign-row', 'case', 'unrelated'] as $case) {
            $matches = $original;
            if ($case === 'missing') { unset($matches['stage']); }
            elseif ($case === 'ambiguous') { $matches['stage'][] = $matches['stage'][0]; }
            elseif ($case === 'foreign-row') { $matches['stage'][0]['option'] = 'bridge'; }
            elseif ($case === 'case') { $matches['stage'][0]['option'] = 'Stage'; }
            elseif ($case === 'unrelated') { $matches['foreign'] = []; }
            $snapshot = $matches; $this->refused(fn () => Supplement::project($source, $this->bindings(), $matches));
            self::assertSame($snapshot, $matches);
        }
        foreach (['owner', 'level', 'category_id', 'source_id', 'practice_uuid'] as $field) {
            $bindings = $this->bindings(); $bindings[1][$field] = $field === 'category_id' || $field === 'source_id' ? 999 : 'foreign';
            $this->refused(fn () => Supplement::project($source, $bindings, $original));
        }
        $bindings = $this->bindings(); $bindings[1]['practice_uuid'] = $bindings[0]['practice_uuid'];
        $this->refused(fn () => Supplement::project($source, $bindings, $original));
    }

    public function test_absent_option_projection_is_explicit_and_old_source_raw_bytes_never_change(): void
    {
        $source = Supplement::load(); $matches = $this->optionMatches($source); $matches['upstairs'] = [];
        $path = base_path(Supplement::SOURCE); $bytes = File::get($path);
        $projection = Supplement::project($source, $this->bindings(), $matches);
        self::assertSame([['key' => 'option-'.hash('sha256', 'upstairs'), 'fields' => ['option' => 'upstairs']]], $projection['question_options']);
        self::assertSame($bytes, File::get($path)); self::assertSame(Supplement::SOURCE_SHA, hash('sha256', $bytes));
    }

    public function test_bank_identity_classes_refuse_direct_reseeding_without_a_parallel_writer(): void
    {
        foreach (Supplement::BANKS as $bank) {
            self::assertTrue(class_exists($bank)); $caught = null;
            try { (new $bank)->run(); } catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught);
            self::assertSame('Use reviewed guarded M57 content patch; direct seeding is disabled.', $caught->getMessage());
        }
    }
}
