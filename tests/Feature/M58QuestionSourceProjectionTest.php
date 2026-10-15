<?php

namespace Tests\Feature;

use App\Support\ComposeTokenCase;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M58QuestionSupplement as Supplement;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

/** Pure finite author projection: no application .env, working SQL or progress writes. */
class M58QuestionSourceProjectionTest extends TestCase
{
    private const KEYS = [
        'The keys had been found before we left.',
        'The bags had not been checked by then.',
        'Had the guests been invited before the meeting?',
        'Where had the money been hidden?',
        'She had never been interviewed before.',
        'The winner will be announced tomorrow.',
        'The tickets will not be sold until Monday.',
        'Will you be informed about the changes?',
        'What will be sent after the meeting?',
        'When the manager arrives, the documents will be checked.',
        'By noon, the report will have been checked.',
        'The repairs will not have been completed by Friday.',
        'Will the guests have been informed by then?',
        'What will have been sent by noon?',
        'Not all the rooms will have been cleaned by noon.',
    ];

    private function bindings(): array
    {
        return array_map(static fn ($i) => ['owner' => Supplement::OWNERS[$i], 'level' => Supplement::LEVELS[$i],
            'category_id' => Supplement::CATEGORY_IDS[$i], 'source_id' => Supplement::SOURCE_IDS[$i],
            'practice_uuid' => sprintf('00000000-0000-4000-8000-%012d', $i + 1)], [0, 1, 2]);
    }

    private function optionMatches(array $source): array
    {
        $rows = []; $matches = []; $id = 1000;
        $observed = ['we' => 'We', 'you' => 'You', 'she' => 'She', 'what' => 'What',
            'when' => 'When', 'where' => 'Where', 'monday' => 'Monday', 'friday' => 'Friday'];
        foreach ($source['banks'] as $bank) {
            foreach ($bank['questions'] as $question) {
                foreach ($question['options'] as $token) {
                    $lower = mb_strtolower($token);
                    $rows[$lower] ??= ['id' => ++$id, 'option' => $observed[$lower] ?? $lower,
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

    public function test_frozen_fifteen_have_independent_keys_mixed_cefr_full_words_and_no_answer_hints(): void
    {
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        $source = Supplement::load(); $questions = array_merge(...array_column($source['banks'], 'questions'));
        self::assertSame(self::KEYS, array_column($questions, 'target_text'));
        self::assertSame(Supplement::UUIDS, array_column($questions, 'uuid'));
        self::assertCount(15, array_unique(array_column($questions, 'uuid')));
        self::assertSame([109, 107, 106], array_column($source['banks'], 'page_id'));
        self::assertSame([45, 45, 45], array_column($source['banks'], 'category_id'));
        self::assertSame([348, 345, 349], array_column($source['banks'], 'source_id'));
        self::assertSame(['B1', 'B1', 'B2'], array_column($source['banks'], 'level'));
        foreach ($source['banks'] as $i => $bank) {
            foreach ($bank['questions'] as $question) {
                self::assertSame([], $question['hints']); self::assertSame([], $question['verb_hints']);
                self::assertSame(Supplement::LEVELS[$i], $question['level']); self::assertSame(4, $question['type']);
                self::assertStringNotContainsString($question['target_text'], $question['question']);
                self::assertSame(1, preg_match('/[\x{0400}-\x{04FF}]/u', $question['translation_uk']));
                self::assertSame($question['tokens_correct'], array_values($question['answers']));
                self::assertSame($question['tokens_correct'], ComposeTokenCase::normalize($question['tokens_correct']));
                foreach (['in this context', 'in this passive context', 'in the grammar exercise'] as $tail) {
                    self::assertStringNotContainsString($tail, $question['target_text']);
                }
            }
        }
        self::assertSame(15, $source['scope']['new_questions']);
    }

    public function test_insert_projection_reuses_original_rows_and_preserves_repeated_token_instances(): void
    {
        $source = Supplement::load(); $matches = $this->optionMatches($source); $before = $matches;
        $projection = Supplement::project($source, $this->bindings(), $matches);
        self::assertSame($before, $matches); self::assertCount(15, $projection['questions']);
        self::assertCount(132, $projection['question_answers']); self::assertCount(175, $projection['question_option_question']);
        self::assertCount(15, $projection['question_theory_text_blocks']); self::assertSame([], $projection['question_options']);
        self::assertCount(57, $projection['reused_options']); self::assertCount(15, $projection['translations']);
        self::assertCount(15, $projection['provenance']);
        $repeated = array_values(array_filter($projection['question_answers'], static fn ($r) => $r['question_key'] === 'm58-fs-b1-05'));
        self::assertCount(11, $repeated);
        self::assertCount(2, array_filter($repeated, static fn ($r) => $r['option_key'] === 'option-'.hash('sha256', 'the')));
        self::assertCount(11, array_unique(array_column($repeated, 'key')));
        $fields = array_column($projection['questions'], 'fields');
        self::assertSame(Supplement::BANKS, array_values(array_unique(array_column($fields, 'seeder'))));
        self::assertSame(array_merge(array_fill(0, 5, 'B1'), array_fill(0, 5, 'B1'), array_fill(0, 5, 'B2')), array_column($fields, 'level'));
        self::assertSame(array_merge(array_fill(0, 10, 3), array_fill(0, 5, 4)), array_column($fields, 'difficulty'));
        foreach ($fields as $row) { self::assertNull($row['theory_text_block_uuid']); self::assertSame('4', $row['type']); }
        $originalById = [];
        foreach ($before as $lookup) { foreach ($lookup as $row) { $originalById[$row['id']] = $row; } }
        foreach ($projection['reused_options'] as $row) { self::assertSame($originalById[$row['id']], $row['row']); }
        $ids = []; foreach (Rows::ROW_COLUMNS as $table => $_) {
            $ids[$table] = []; $next = 900000;
            foreach ($projection[$table] as $row) { $ids[$table][$row['key']] = ++$next; }
        }
        $materialized = Rows::materialize($projection, $ids, '2026-10-16 12:00:00');
        Rows::assertApplied($materialized, $materialized);
        self::assertCount(15, $materialized['questions']); self::assertSame([], $materialized['question_options']);
        $instanceIds = array_column(array_filter($materialized['question_answers'], static fn ($r) => $r['question_id'] === $ids['questions']['m58-fs-b1-05']), 'id');
        self::assertCount(11, array_unique($instanceIds)); self::assertSame($before, $matches);
    }

    public function test_foreign_owner_flat_url_wrong_cefr_type_short_duplicate_pool_and_hints_refuse(): void
    {
        $original = Supplement::load();
        foreach (['owner', 'flat-url', 'bank-cefr', 'question-cefr', 'type', 'short', 'duplicate', 'tokens', 'hint', 'foreign-id', 'write-scope'] as $case) {
            $source = $original;
            if ($case === 'owner') { $source['banks'][0]['owner'] = 'ForeignOwner'; }
            elseif ($case === 'flat-url') { $source['banks'][0]['local_url'] = str_replace('/passive-voice-tenses', '', $source['banks'][0]['local_url']); }
            elseif ($case === 'bank-cefr') { $source['banks'][2]['level'] = 'B1'; }
            elseif ($case === 'question-cefr') { $source['banks'][2]['questions'][0]['level'] = 'B1'; }
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

    public function test_missing_ambiguous_foreign_or_strict_case_changed_options_refuse_without_mutation(): void
    {
        $source = Supplement::load(); $original = $this->optionMatches($source);
        foreach (['missing', 'ambiguous', 'foreign-row', 'case', 'unrelated'] as $case) {
            $matches = $original;
            if ($case === 'missing') { unset($matches['keys']); }
            elseif ($case === 'ambiguous') { $matches['keys'][] = $matches['keys'][0]; }
            elseif ($case === 'foreign-row') { $matches['keys'][0]['option'] = 'bags'; }
            elseif ($case === 'case') { $matches['keys'][0]['option'] = 'Keys'; }
            elseif ($case === 'unrelated') { $matches['foreign'] = []; }
            $snapshot = $matches; $this->refused(fn () => Supplement::project($source, $this->bindings(), $matches));
            self::assertSame($snapshot, $matches);
        }
        foreach (['owner', 'level', 'category_id', 'source_id', 'practice_uuid'] as $field) {
            $bindings = $this->bindings(); $bindings[1][$field] = in_array($field, ['category_id', 'source_id'], true) ? 999 : 'foreign';
            $this->refused(fn () => Supplement::project($source, $bindings, $original));
        }
        $bindings = $this->bindings(); $bindings[1]['practice_uuid'] = $bindings[0]['practice_uuid'];
        $this->refused(fn () => Supplement::project($source, $bindings, $original));
        $bindings = $this->bindings(); $bindings[2]['level'] = 'B1';
        $this->refused(fn () => Supplement::project($source, $bindings, $original));
    }

    public function test_absent_new_option_is_explicit_without_mutating_frozen_sources(): void
    {
        $source = Supplement::load(); $matches = $this->optionMatches($source); $matches['keys'] = [];
        $path = base_path(Supplement::SOURCE); $bytes = File::get($path);
        $oldPath = base_path(\App\Support\M57QuestionSupplement::SOURCE); $oldBytes = File::get($oldPath);
        $projection = Supplement::project($source, $this->bindings(), $matches);
        self::assertSame([['key' => 'option-'.hash('sha256', 'keys'), 'fields' => ['option' => 'keys']]], $projection['question_options']);
        self::assertSame($bytes, File::get($path)); self::assertSame(Supplement::SOURCE_SHA, hash('sha256', $bytes));
        self::assertSame($oldBytes, File::get($oldPath));
        self::assertSame(\App\Support\M57QuestionSupplement::SOURCE_SHA, hash('sha256', $oldBytes));
    }

    public function test_new_bank_identity_classes_disable_direct_reseeding(): void
    {
        foreach (Supplement::BANKS as $bank) {
            self::assertTrue(class_exists($bank)); $caught = null;
            try { (new $bank)->run(); } catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught);
            self::assertSame('Use reviewed guarded M58 content patch; direct seeding is disabled', $caught->getMessage());
        }
    }
}
