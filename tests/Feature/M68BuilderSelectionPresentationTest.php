<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M51QuestionSupplement;
use App\Support\M68BuilderSelectionPresentation as Presentation;
use App\Support\M68ComparisonBasics as Package;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

/** Public teaching contract plus explicitly synthetic, unsaved opaque-snapshot fixtures. No private DB snapshots. */
class M68BuilderSelectionPresentationTest extends TestCase
{
    private function audit(): array
    {
        self::assertSame(Presentation::AUDIT_SHA, hash_file('sha256', base_path(Presentation::AUDIT)));
        return json_decode(file_get_contents(base_path(Presentation::AUDIT)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function block(int $i): TextBlock
    {
        [, $patch] = Package::load(); $a = $this->audit()['practice_bindings'][$i];
        $s = $patch['targets'][$i]['after']['page']['blocks'][$a['source_index']];
        $b = new TextBlock;
        $b->setRawAttributes(['id' => 700000 + $i, 'uuid' => $a['uuid'], 'page_id' => $a['page_id'],
            'page_category_id' => $a['category_id'], 'seeder' => $a['identity'], 'locale' => 'uk',
            'type' => 'practice-set', 'level' => $a['level'], 'column' => $a['column'], 'heading' => $a['heading'],
            'css_class' => $a['css_class'], 'sort_order' => $a['source_index'] + 1, 'body' => $s['body']], true);
        return $b;
    }

    public function test_teaching_only_contract_has_fifteen_reused_questions_and_no_question_option_answer_or_progress_writes(): void
    {
        $a = $this->audit(); [, $patch, , $contract] = Package::load(); Presentation::validateAudit($a, $patch, $contract);
        self::assertCount(3, $a['targets']);
        self::assertSame(15, $a['scope']['selected_questions']);
        self::assertSame(15, $a['scope']['reused_questions']);
        self::assertSame(0, $a['scope']['new_questions']);
        foreach (['question_writes', 'option_writes', 'answer_writes', 'option_link_writes', 'old_hint_writes',
            'old_question_updates', 'old_option_updates', 'old_answer_updates', 'grader_changes', 'progress_writes'] as $field) {
            self::assertSame(0, $a['scope'][$field], $field);
        }
        foreach ($a['targets'] as $i => $target) {
            self::assertGreaterThanOrEqual(5, count($target['all_eligible_review']));
            self::assertCount(5, $target['first_five']);
            self::assertCount(5, array_unique($target['expected_selected_ids']));
            self::assertSame(Package::LEVELS[$i], $target['level']);
            foreach ($target['first_five'] as $question) {
                self::assertFalse($question['is_new']);
                foreach (['question_snapshot', 'ordered_answer_snapshot', 'original_options_snapshot', 'hints_snapshot',
                    'verb_hints_snapshot', 'loaded_option_pivots', 'created_at', 'updated_at', 'runtime', 'identity'] as $forbidden) {
                    self::assertArrayNotHasKey($forbidden, $question);
                }
                self::assertSame(M51QuestionSupplement::ROW_COLUMNS['questions'], $question['question_attribute_keys']);
                self::assertCount(6, $question['snapshot_hashes']);
                foreach ($question['snapshot_hashes'] as $sha) { self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha); }
                self::assertSame($question['context_prefix_uk'].' '.$question['displayed_condition_uk'], $question['visible_condition_uk']);
                self::assertSame($question['expected_display_sentence'], preg_replace('/\\s+([,.!?;:])/', '$1', implode(' ', $question['correct_tokens'])));
            }
        }
    }

    public function test_hash_bound_audit_refuses_any_scope_pool_context_or_practice_binding_change(): void
    {
        $a = $this->audit(); [, $p, , $c] = Package::load();
        $changes = [
            static function (&$x): void { $x['scope']['question_writes']++; },
            static function (&$x): void { $x['targets'][0]['page_id']++; },
            static function (&$x): void { $x['targets'][1]['first_five'][0]['snapshot_hashes']['question_snapshot'] = str_repeat('0', 64); },
            static function (&$x): void { $x['targets'][2]['first_five'][0]['context_prefix_uk'] = 'Інший контекст.'; },
            static function (&$x): void { array_pop($x['targets'][1]['excluded_question_ids']); },
            static function (&$x): void { $x['targets'][0]['expected_selected_ids'][4] = $x['targets'][0]['expected_selected_ids'][0]; },
            static function (&$x): void { $x['practice_bindings'][2]['uuid'] = 'foreign'; },
        ];
        foreach ($changes as $change) {
            $bad = $a; $change($bad); $refused = false;
            try { Presentation::validateAudit($bad, $p, $c); } catch (RuntimeException) { $refused = true; }
            self::assertTrue($refused);
        }
    }

    public function test_only_exact_theory_owner_binding_extends_exclusions_without_queries_or_original_mutation(): void
    {
        app()->setLocale('uk'); DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ($this->audit()['targets'] as $i => $t) {
            $b = $this->block($i); $original = $b->getAttributes();
            $config = ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => $t['bank_seeders']];
            self::assertSame([999], Presentation::exclusions($b, $config, [999]));
            $copy = Presentation::matchingBlock($b, true); self::assertNotSame($b, $copy);
            self::assertSame([999, ...$t['excluded_question_ids']], Presentation::exclusions($copy, $config, [999]));
            self::assertSame([999], Presentation::exclusions(clone $copy, $config, [999]));
            self::assertSame($original, $b->getAttributes()); self::assertSame([], $b->getDirty());
            foreach (['seeder' => 'Foreign\\\\NeverRegisteredOwner', 'locale' => 'en', 'type' => 'box', 'level' => 'B2', 'page_id' => 1,
                'page_category_id' => 1, 'uuid' => 'foreign', 'body' => $b->body."\n", 'sort_order' => 99] as $field => $value) {
                $foreign = clone $b; $foreign->$field = $value;
                self::assertSame($foreign, Presentation::matchingBlock($foreign, true), $field);
            }
            self::assertSame($b, Presentation::matchingBlock($b, false));
            foreach (['en', 'pl'] as $locale) {
                app()->setLocale($locale); self::assertSame($b, Presentation::matchingBlock($b, true));
            }
            app()->setLocale('uk');
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_only_the_existing_exact_link_matcher_decorations_are_allowed_outside_complete_db_snapshot(): void
    {
        $method = new ReflectionMethod(Presentation::class, 'matchesQuestions');
        [$q, $target] = $this->synthetic();
        foreach ($q as $question) {
            $question->setAttribute('match_score', null);
            $question->setAttribute('matched_tag_ids', []);
            $question->setAttribute('marker_tags', []);
        }
        $before = $q->toArray(); self::assertTrue($method->invoke(null, $q, $target));
        self::assertSame($before, $q->toArray());
        $q[4]->setAttribute('match_score', 1); self::assertFalse($method->invoke(null, $q, $target));
        $q[4]->setAttribute('match_score', null); $q[4]->setAttribute('matched_tag_ids', [1]);
        self::assertFalse($method->invoke(null, $q, $target));
        $q[4]->setAttribute('matched_tag_ids', []); $q[4]->setAttribute('marker_tags', 'foreign');
        self::assertFalse($method->invoke(null, $q, $target));
        $q[4]->offsetUnset('marker_tags'); self::assertFalse($method->invoke(null, $q, $target));
        [$q, $target] = $this->synthetic();
        $q[0]->setAttribute('match_score', null); self::assertFalse($method->invoke(null, $q, $target), 'A partial decoration tuple is not a complete matcher result.');
        [$q, $target] = $this->synthetic();
        $q[1]->setAttribute('unknown_matcher_decoration', []); self::assertFalse($method->invoke(null, $q, $target));
    }

    private function synthetic(): array
    {
        $questions = []; $samples = [];
        for ($n = 0; $n < 5; ++$n) {
            $id = 80000 + $n; $attrs = ['id' => $id, 'uuid' => 'synthetic-'.$n, 'question' => 'Синтетична умова.',
                'difficulty' => 3, 'level' => 'B1', 'category_id' => 45, 'source_id' => 305, 'flag' => 0, 'type' => '4',
                'options_by_marker' => null, 'theory_text_block_uuid' => null, 'seeder' => 'SyntheticOnly',
                'created_at' => '2000-01-01 00:00:00', 'updated_at' => '2000-01-01 00:00:00'];
            $q = new Question; $q->setRawAttributes($attrs, true); $options = []; $answers = []; $orows = []; $arows = []; $prows = [];
            foreach (['We', 'waited', 'because', 'of', 'rain', '.'] as $i => $token) {
                $oid = 90000 + $n * 10 + $i; $aid = 91000 + $n * 10 + $i; $marker = 'a'.($i + 1);
                $o = new QuestionOption; $o->setRawAttributes(['id' => $oid, 'option' => $token], true);
                $pivot = new Pivot; $pr = ['question_id' => $id, 'option_id' => $oid, 'flag' => null]; $pivot->setRawAttributes($pr, true);
                $o->setRelation('pivot', $pivot);
                $a = new QuestionAnswer; $a->setRawAttributes(['id' => $aid, 'question_id' => $id, 'option_id' => $oid, 'marker' => $marker, 'position' => null], true);
                $a->setRelation('option', $o); $options[] = $o; $answers[] = $a;
                $orows[] = ['id' => $oid, 'option' => $token]; $arows[] = ['id' => $aid, 'marker' => $marker, 'position' => null, 'option_id' => $oid, 'option' => $token]; $prows[] = $pr;
            }
            $q->setRelation('answers', new Collection($answers)); $q->setRelation('options', new Collection($options));
            $q->setRelation('hints', new Collection); $q->setRelation('verbHints', new Collection);
            $questions[] = $q;
            $samples[] = ['question_attribute_keys' => array_keys($attrs), 'snapshot_hashes' => array_map(
                fn ($v) => M26DetailPackage::digest($v), ['question_snapshot' => $attrs, 'ordered_answer_snapshot' => $arows,
                'original_options_snapshot' => $orows, 'hints_snapshot' => [], 'verb_hints_snapshot' => [], 'loaded_option_pivots' => $prows]),
                'token_presentations' => []];
        }
        return [new Collection($questions), ['expected_selected_ids' => array_column(array_map(fn ($q) => $q->getAttributes(), $questions), 'id'),
            'expected_selected_uuids' => array_map(fn ($q) => $q->uuid, $questions), 'first_five' => $samples]];
    }

    public function test_complete_opaque_snapshot_rejects_stale_rows_or_one_missing_relation_all_or_nothing_without_query(): void
    {
        $method = new ReflectionMethod(Presentation::class, 'matchesQuestions');
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        [$q, $target] = $this->synthetic(); $before = $q->toArray();
        self::assertTrue($method->invoke(null, $q, $target)); self::assertSame($before, $q->toArray());
        $changes = [
            fn ($q) => $q[4]->setAttribute('question', 'Зміна.'),
            fn ($q) => $q[4]->setAttribute('created_at', '2001-01-01 00:00:00'),
            fn ($q) => $q[2]->answers[0]->option->setAttribute('option', 'Changed'),
            fn ($q) => $q[1]->options[0]->pivot->setAttribute('flag', 1),
            fn ($q) => $q[3]->unsetRelation('hints'),
            fn ($q) => $q[3]->unsetRelation('verbHints'),
            fn ($q) => $q[0]->options[0]->unsetRelation('pivot'),
            fn ($q) => $q[1]->answers[0]->unsetRelation('option'),
            fn ($q) => $q[4]->setAttribute('unreviewed_field', true),
        ];
        foreach ($changes as $change) {
            [$q, $target] = $this->synthetic(); $change($q); $before = $q->toArray();
            self::assertFalse($method->invoke(null, $q, $target)); self::assertSame($before, $q->toArray());
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_public_bindings_refuse_short_duplicate_foreign_or_stale_question_collections_without_mutating_originals(): void
    {
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        $block = $this->block(0); [$questions] = $this->synthetic();
        $cases = [new Collection, $questions->take(4), new Collection([$questions[0], $questions[0], $questions[2], $questions[3], $questions[4]]), $questions];
        foreach ($cases as $pool) {
            $original = $pool->toArray();
            foreach ([false, true] as $canonical) {
                $bindings = ['block' => $block, 'theoryCanonical' => $canonical, 'practiceQuestions' => $pool, 'unrelated' => 'retained'];
                self::assertSame($bindings, Presentation::bindings($bindings));
                self::assertSame($original, $pool->toArray());
            }
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }
}
