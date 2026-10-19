<?php

namespace Tests\Feature;

use App\Support\M51QuestionSupplement as Supplement;
use RuntimeException;
use Tests\TestCase;

/** Pure array fixtures only: no working or fixture database, seeder or HTTP call. */
class M51QuestionSupplementTest extends TestCase
{
    private function source(): array
    {
        $source = ['schema_version' => 1, 'package' => 'm51-modal-meanings-widget-questions', 'version' => '1.0.0',
            'status' => 'draft', 'locale' => 'uk', 'banks' => []];
        $canonical = [['The', 'guest', 'may', 'be', 'in', 'the', 'foyer', '.'],
            ['We', 'could', 'have', 'waited', '.'], ['Could', 'you', 'help', 'me', '?']];
        foreach (Supplement::OWNERS as $i => $owner) {
            $bank = ['owner' => $owner, 'local_url' => 'http://gramlyze.loc/theory/modal-verbs/'.Supplement::SLUGS[$i],
                'bank_seeder' => Supplement::BANKS[$i], 'level' => Supplement::LEVELS[$i], 'type' => 4, 'questions' => []];
            for ($n = 1; $n <= 5; $n++) {
                $tokens = $canonical[$i]; $shuffled = array_reverse($tokens);
                $bank['questions'][] = ['source_uid' => Supplement::UID_PREFIXES[$i].str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                    'question_uk' => 'Українська умова з потрібною конструкцією для питання '.$n.'.',
                    'context_uk' => 'Умова прямо встановлює ситуацію та виконавця.',
                    'criterion_uk' => 'Побудуй саме названу конструкцію без зміни змісту.',
                    'translation_uk' => 'Точний український переклад цільового речення.',
                    'ambiguity_note_uk' => 'Інші перефразування не оголошено неправильними.',
                    'canonical_en' => preg_replace('/\s+([?.!,;:])/', '$1', implode(' ', $tokens)),
                    'tokens_correct' => $tokens, 'tokens_shuffled' => $shuffled, 'distractors' => [],
                    'learning_requirements' => ['explicit-construction']];
            }
            $source['banks'][] = $bank;
        }

        return $source;
    }

    private function bindings(): array
    {
        return array_map(static fn ($owner, $level, $i) => ['owner' => $owner, 'level' => $level,
            'category_id' => 45, 'source_id' => null, 'practice_uuid' => sprintf('00000000-0000-5000-8000-%012d', $i + 1)],
            Supplement::OWNERS, Supplement::LEVELS, array_keys(Supplement::OWNERS));
    }

    private function optionMatches(array $source): array
    {
        $matches = [];
        foreach ($source['banks'] as $bank) {
            foreach ($bank['questions'] as $question) {
                foreach ([...$question['tokens_correct'], ...$question['distractors']] as $token) { $matches[$token] = []; }
            }
        }
        $the = ['id' => 30, 'option' => 'The', 'created_at' => '2020-01-01 00:00:00', 'updated_at' => '2020-01-02 00:00:00'];
        $matches['The'] = [$the]; $matches['the'] = [$the];

        return $matches;
    }

    private function ids(array $projection): array
    {
        $ids = [];
        foreach (array_keys(Supplement::ROW_COLUMNS) as $i => $table) {
            $ids[$table] = [];
            foreach ($projection[$table] as $n => $row) { $ids[$table][$row['key']] = ($i + 1) * 10000 + $n + 1; }
        }

        return $ids;
    }

    private function refusal(callable $call): void
    {
        try { $call(); $this->fail('Expected closed M51 supplement refusal.'); }
        catch (RuntimeException $error) { $this->assertStringContainsString('M51', $error->getMessage()); }
    }

    public function test_draft_release_cannot_be_loaded_until_a_reviewed_sha_is_pinned(): void
    {
        if (preg_match('/^[a-f0-9]{64}$/D', Supplement::SOURCE_SHA) !== 1) {
            $this->refusal(static fn () => Supplement::load());
        } else {
            $source = Supplement::load();
            $this->assertContains($source['status'], ['approved', 'frozen']);
            $this->assertSame(['B1', 'B1', 'A2'], array_column($source['banks'], 'level'));
        }
    }

    public function test_pure_projection_has_fifteen_new_themed_questions_and_preserves_repeated_answer_instances_and_shared_options(): void
    {
        $source = $this->source(); $bindings = $this->bindings(); $matches = $this->optionMatches($source);
        $before = [$source, $bindings, $matches];
        $projection = Supplement::project($source, $bindings, $matches);
        $this->assertSame($before, [$source, $bindings, $matches]);
        $this->assertCount(15, $projection['questions']); $this->assertCount(15, $projection['question_theory_text_blocks']);
        $this->assertCount(15, $projection['translations']); $this->assertCount(15, $projection['provenance']);
        $this->assertSame(array_merge(array_fill(0, 10, 'B1'), array_fill(0, 5, 'A2')),
            array_map(static fn ($row) => $row['fields']['level'], $projection['questions']));
        $this->assertSame(array_fill(0, 15, '4'), array_map(static fn ($row) => $row['fields']['type'], $projection['questions']));
        $this->assertCount(1, $projection['reused_options']);
        $this->assertSame($matches['The'][0], $projection['reused_options'][0]['row']);
        $answers = array_values(array_filter($projection['question_answers'], static fn ($row) => $row['question_key'] === 'm51-ded-b1-01'));
        $this->assertCount(8, $answers);
        $this->assertSame($answers[0]['option_key'], $answers[5]['option_key']);
        $this->assertSame(['a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8'], array_column(array_column($answers, 'fields'), 'marker'));
        $pivots = array_values(array_filter($projection['question_option_question'], static fn ($row) => $row['question_key'] === 'm51-ded-b1-01'));
        $this->assertCount(7, $pivots); $this->assertSame(array_fill(0, 7, null), array_column(array_column($pivots, 'fields'), 'flag'));
        $this->assertSame(['questions', 'question_options', 'question_answers', 'question_option_question', 'question_theory_text_blocks'],
            array_keys(Supplement::ROW_COLUMNS));
    }

    public function test_exact_materialization_and_postcondition_keep_ids_relations_timestamps_and_all_original_rows(): void
    {
        $source = $this->source(); $projection = Supplement::project($source, $this->bindings(), $this->optionMatches($source));
        $rows = Supplement::materialize($projection, $this->ids($projection), '2026-10-14 12:00:00');
        Supplement::assertApplied($rows, $rows);
        foreach ($rows as $table => $family) {
            foreach ($family as $row) {
                $this->assertSame(Supplement::ROW_COLUMNS[$table], array_keys($row));
                if ($table !== 'question_option_question') { $this->assertSame($row['created_at'], $row['updated_at']); }
            }
        }
        $original = ['question' => ['id' => 11, 'question' => 'Original'], 'option' => $projection['reused_options'][0]['row']];
        Supplement::assertOriginalsUnchanged($original, $original);
        $changed = $original; $changed['option']['updated_at'] = '2026-10-14 12:00:00';
        $this->refusal(static fn () => Supplement::assertOriginalsUnchanged($original, $changed));
        $edited = $rows; $edited['questions'][0]['type'] = '1';
        $this->refusal(static fn () => Supplement::assertApplied($rows, $edited));
        $edited = $rows; $edited['question_answers'][0]['updated_at'] = '2026-10-14 12:00:01';
        $this->refusal(static fn () => Supplement::assertApplied($rows, $edited));
        $edited = $rows; $edited['question_theory_text_blocks'][] = $edited['question_theory_text_blocks'][0];
        $this->refusal(static fn () => Supplement::assertApplied($rows, $edited));
    }

    public function test_foreign_bank_owner_type_level_uid_missing_five_and_mismatched_token_multiset_refuse(): void
    {
        foreach (['owner' => 'foreign', 'bank_seeder' => Supplement::BANKS[1], 'level' => 'A2', 'type' => 1] as $field => $value) {
            $source = $this->source(); $source['banks'][0][$field] = $value;
            $this->refusal(static fn () => Supplement::validate($source));
        }
        $source = $this->source(); array_pop($source['banks'][0]['questions']);
        $this->refusal(static fn () => Supplement::validate($source));
        $source = $this->source(); $source['banks'][0]['questions'][1]['source_uid'] = $source['banks'][0]['questions'][0]['source_uid'];
        $this->refusal(static fn () => Supplement::validate($source));
        $source = $this->source(); array_pop($source['banks'][0]['questions'][0]['tokens_shuffled']);
        $this->refusal(static fn () => Supplement::validate($source));
        $source = $this->source(); $source['banks'][0]['questions'][0]['tokens_correct'][2] = 'ma';
        $this->refusal(static fn () => Supplement::validate($source));
    }

    public function test_fresh_option_lookup_absence_casing_and_exact_id_allocation_fail_closed(): void
    {
        $source = $this->source(); $bindings = $this->bindings(); $matches = $this->optionMatches($source);
        $bad = $matches; unset($bad['may']);
        $this->refusal(static fn () => Supplement::project($source, $bindings, $bad));
        $bad = $matches; $bad['may'] = [['id' => 40, 'option' => 'May', 'created_at' => null, 'updated_at' => null]];
        $this->refusal(static fn () => Supplement::project($source, $bindings, $bad));
        $bad = $matches; $bad['may'] = [$bad['The'][0], $bad['The'][0]];
        $this->refusal(static fn () => Supplement::project($source, $bindings, $bad));
        $projection = Supplement::project($source, $bindings, $matches); $ids = $this->ids($projection);
        $bad = $ids; $keys = array_keys($bad['questions']); $bad['questions'][$keys[1]] = $bad['questions'][$keys[0]];
        $this->refusal(static fn () => Supplement::materialize($projection, $bad, '2026-10-14 12:00:00'));
        $bad = $ids; $bad['question_options'][array_key_first($bad['question_options'])] = 30;
        $this->refusal(static fn () => Supplement::materialize($projection, $bad, '2026-10-14 12:00:00'));
        $empty = array_fill_keys(array_keys(Supplement::ROW_COLUMNS), []); Supplement::assertAbsent($empty);
        $collision = $empty; $collision['questions'] = [['id' => 1]];
        $this->refusal(static fn () => Supplement::assertAbsent($collision));
        unset($empty['question_answers']); $this->refusal(static fn () => Supplement::assertAbsent($empty));
    }
}
