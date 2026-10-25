<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Support\M26DetailPackage as Digest;
use App\Support\M51QuestionSupplement;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/** Invented unsaved models only. No private SQL, Laravel application, connection or filesystem writes. */
class M89VerbHintSnapshotPresentationTest extends TestCase
{
    private const SCHEMA = 'complete-loaded-verb-hints-v1';
    private const HINT_KEYS = ['id', 'question_id', 'option_id', 'marker', 'locale', 'created_at', 'updated_at'];
    private const OPTION_KEYS = ['id', 'option', 'created_at', 'updated_at'];
    private ?ConnectionResolverInterface $priorResolver = null;
    private M89ForbiddenConnectionResolver $forbidden;

    protected function setUp(): void
    {
        parent::setUp();
        $this->priorResolver = Model::getConnectionResolver();
        $this->forbidden = new M89ForbiddenConnectionResolver;
        Model::setConnectionResolver($this->forbidden);
    }

    protected function tearDown(): void
    {
        try {
            self::assertSame(0, $this->forbidden->calls, 'The presenter must not attempt even one database connection/query.');
        } finally {
            if ($this->priorResolver === null) { Model::unsetConnectionResolver(); }
            else { Model::setConnectionResolver($this->priorResolver); }
            parent::tearDown();
        }
    }

    private function model(string $class, array $attributes): Model
    {
        $model = new $class;
        $model->setRawAttributes($attributes, true);
        return $model;
    }

    private function changed(Model $model, string $key, mixed $value, bool $remove = false): void
    {
        $attributes = $model->getAttributes();
        if ($remove) { unset($attributes[$key]); }
        else { $attributes[$key] = $value; }
        $model->setRawAttributes($attributes, true);
    }

    private function helper(Question $question, array $sample): ?array
    {
        return (new ReflectionMethod(TheoryBuilderSelectionPresentation::class, 'verbHints'))->invoke(null, $question, $sample);
    }

    private function matchesSnapshot(Collection $questions, array $target): bool
    {
        return (new ReflectionMethod(TheoryBuilderSelectionPresentation::class, 'matchesQuestions'))->invoke(null, $questions, $target);
    }

    /** Independent expected projection; unlike the subject, this merely extracts fixture fields. */
    private function hintRows(Question $question): array
    {
        $out = [];
        foreach ($question->getRelation('verbHints') as $hint) {
            $a = $hint->getAttributes(); $o = $hint->getRelation('option')->getAttributes();
            $attrs = $option = [];
            foreach (self::HINT_KEYS as $key) { $attrs[$key] = $a[$key]; }
            foreach (self::OPTION_KEYS as $key) { $option[$key] = $o[$key]; }
            $out[] = ['attributes' => $attrs, 'option' => $option];
        }
        return $out;
    }

    /** Full original five-question contract, independently assembled from literal invented attributes. */
    private function fixture(bool $withHints = true, bool $optIn = true): array
    {
        $questions = $samples = [];
        for ($n = 0; $n < 5; $n++) {
            $qid = 8900000 + $n; $oid = 8910000 + $n * 10;
            $attrs = ['id' => $qid, 'uuid' => 'm89-invented-only-'.$n, 'question' => 'Синтетична видима умова '.$n.'.',
                'difficulty' => 1, 'level' => 'A2', 'category_id' => 8930000, 'source_id' => 8930001,
                'flag' => 0, 'type' => '4', 'options_by_marker' => null, 'theory_text_block_uuid' => null,
                'seeder' => 'SyntheticM89Only', 'created_at' => '2000-01-01 00:00:00', 'updated_at' => '2000-01-01 00:00:00'];
            $q = $this->model(Question::class, $attrs); $options = $answers = $optionRows = $answerRows = $pivots = [];
            foreach (['They', 'have', 'waited', 'outside'] as $i => $text) {
                $option = $this->model(QuestionOption::class, ['id' => $oid + $i, 'option' => $text,
                    'created_at' => '1999-01-01 00:00:00', 'updated_at' => '1999-01-01 00:00:00']);
                $pivot = ['question_id' => $qid, 'option_id' => $option->id, 'flag' => null];
                $option->setRelation('pivot', $this->model(Pivot::class, $pivot));
                $answer = $this->model(QuestionAnswer::class, ['id' => 8920000 + $n * 10 + $i,
                    'question_id' => $qid, 'option_id' => $option->id, 'marker' => 'a'.($i + 1), 'position' => null]);
                $answer->setRelation('option', $option);
                $options[] = $option; $answers[] = $answer; $pivots[] = $pivot;
                $optionRows[] = ['id' => $option->id, 'option' => $text];
                $answerRows[] = ['id' => $answer->id, 'marker' => $answer->marker, 'position' => null,
                    'option_id' => $option->id, 'option' => $text];
            }
            $q->setRelation('options', new Collection($options));
            $q->setRelation('answers', new Collection($answers));
            $q->setRelation('hints', new Collection);
            $hints = [];
            if ($withHints) {
                foreach ([['a2', '', 1], ['A4', 'uk', 3]] as $h => [$marker, $locale, $optionIndex]) {
                    $option = $options[$optionIndex];
                    $hint = $this->model(VerbHint::class, ['id' => 8940000 + $n * 10 + $h,
                        'question_id' => $qid, 'option_id' => $option->id, 'marker' => $marker, 'locale' => $locale,
                        'created_at' => '2000-02-01 00:00:00', 'updated_at' => '2000-02-01 00:00:00']);
                    // Distinct model with identical complete attributes is valid; pointer identity is not the snapshot contract.
                    $hint->setRelation('option', $this->model(QuestionOption::class, $option->getAttributes()));
                    $hints[] = $hint;
                }
            }
            $q->setRelation('verbHints', new Collection($hints));
            $sample = ['id' => $qid, 'uuid' => $attrs['uuid'], 'question_attribute_keys' => M51QuestionSupplement::ROW_COLUMNS['questions'],
                'visible_condition_uk' => 'Окрема погоджена умова '.$n.'.',
                'snapshot_hashes' => array_map(Digest::digest(...), [
                    'question_snapshot' => $attrs, 'ordered_answer_snapshot' => $answerRows,
                    'original_options_snapshot' => $optionRows, 'loaded_option_pivots' => $pivots,
                    'hints_snapshot' => [], 'verb_hints_snapshot' => $this->hintRows($q)])];
            if ($optIn) { $sample['verb_hint_snapshot_schema'] = self::SCHEMA; }
            $questions[] = $q; $samples[] = $sample;
        }
        return [new Collection($questions), ['expected_selected_ids' => array_column($samples, 'id'),
            'expected_selected_uuids' => array_column($samples, 'uuid'), 'first_five' => $samples]];
    }

    /** Models the existing ORM boundary: a flag=1 UA hint option is absent from question.options. */
    private function fixtureWithSeparateHintOption(): array
    {
        [$questions, $target] = $this->fixture();
        $question = $questions[0]; $hint = $question->getRelation('verbHints')[0];
        $option = $this->model(QuestionOption::class, ['id' => 8960000, 'option' => 'чекати',
            'created_at' => '1995-01-01 00:00:00', 'updated_at' => '1995-01-01 00:00:00']);
        $this->changed($hint, 'option_id', $option->id); $hint->setRelation('option', $option);
        $filteredPivot = ['question_id' => $question->id, 'option_id' => $option->id, 'flag' => 1];
        // This excluded pivot is provenance for the fixture, not a loaded options pivot or hint attribute.
        $target['first_five'][0]['snapshot_hashes']['verb_hints_snapshot'] = Digest::digest($this->hintRows($question));
        return [$questions, $target, $filteredPivot];
    }

    private function rawState(Collection $questions): array
    {
        return $questions->map(static function (Question $question): array {
            $relations = [];
            foreach ($question->getRelations() as $name => $rows) {
                $relations[$name] = ['collection_id' => is_object($rows) ? spl_object_id($rows) : null, 'rows' => []];
                if (!$rows instanceof Collection) { continue; }
                foreach ($rows as $row) {
                    $children = [];
                    if ($row instanceof Model) {
                        foreach ($row->getRelations() as $key => $child) {
                            $children[$key] = ['id' => is_object($child) ? spl_object_id($child) : null,
                                'attributes' => $child instanceof Model ? $child->getAttributes() : null];
                        }
                    }
                    $relations[$name]['rows'][] = ['id' => is_object($row) ? spl_object_id($row) : null,
                        'attributes' => $row instanceof Model ? $row->getAttributes() : null,
                        'exists' => $row instanceof Model ? $row->exists : null, 'relations' => $children];
                }
            }
            return ['attributes' => $question->getAttributes(), 'original' => $question->getRawOriginal(),
                'exists' => $question->exists, 'relations' => $relations];
        })->all();
    }

    public function test_historical_schema_absence_still_requires_loaded_empty_verb_hints_and_digest_of_empty(): void
    {
        [$q, $t] = $this->fixture(false, false);
        self::assertSame([], $this->helper($q[0], $t['first_five'][0]));
        self::assertTrue($this->matchesSnapshot($q, $t));
        // Historical option models without timestamps remain accepted, exactly as old tests hydrate them.
        foreach ($q as $question) {
            foreach ($question->getRelation('options') as $option) {
                $this->changed($option, 'created_at', null, true); $this->changed($option, 'updated_at', null, true);
            }
        }
        self::assertTrue($this->matchesSnapshot($q, $t));
        [$nonempty, $old] = $this->fixture(true, false);
        self::assertNull($this->helper($nonempty[0], $old['first_five'][0]));
        self::assertFalse($this->matchesSnapshot($nonempty, $old), 'Nonempty historic data must not acquire opt-in implicitly.');
        $t['first_five'][0]['snapshot_hashes']['verb_hints_snapshot'] = Digest::digest([['unreviewed' => true]]);
        self::assertFalse($this->matchesSnapshot($q, $t), 'Even loaded empty relations must match the historic empty digest.');
    }

    public function test_explicit_schema_accepts_complete_loaded_snapshot_and_preserves_raw_locale_marker_and_order(): void
    {
        [$q, $t] = $this->fixture();
        $expected = $this->hintRows($q[0]);
        self::assertSame(['', 'uk'], array_column(array_column($expected, 'attributes'), 'locale'));
        self::assertSame(['a2', 'A4'], array_column(array_column($expected, 'attributes'), 'marker'));
        self::assertSame($expected, $this->helper($q[0], $t['first_five'][0]));
        self::assertTrue($this->matchesSnapshot($q, $t));
        [$empty, $et] = $this->fixture(false, true);
        self::assertSame([], $this->helper($empty[0], $et['first_five'][0]));
        self::assertTrue($this->matchesSnapshot($empty, $et));
    }

    public function test_unknown_null_wrong_type_and_missing_schema_never_opt_in_nonempty_relations(): void
    {
        foreach (['unknown-v1', '', null, true, 1, []] as $schema) {
            [$q, $t] = $this->fixture(); $t['first_five'][0]['verb_hint_snapshot_schema'] = $schema;
            self::assertNull($this->helper($q[0], $t['first_five'][0]));
            self::assertFalse($this->matchesSnapshot($q, $t));
        }
        [$q, $t] = $this->fixture(); unset($t['first_five'][0]['verb_hint_snapshot_schema']);
        self::assertNull($this->helper($q[0], $t['first_five'][0])); self::assertFalse($this->matchesSnapshot($q, $t));
    }

    public function test_unloaded_and_noncollection_verb_hint_relations_fail_without_lazy_loading(): void
    {
        foreach (['unloaded', null, [], (object) ['id' => 1]] as $case) {
            [$q, $t] = $this->fixture();
            if ($case === 'unloaded') { $q[0]->unsetRelation('verbHints'); }
            else { $q[0]->setRelation('verbHints', $case); }
            self::assertNull($this->helper($q[0], $t['first_five'][0]));
            self::assertFalse($this->matchesSnapshot($q, $t));
        }
    }

    public function test_wrong_hint_model_and_unloaded_null_or_wrong_option_relations_fail_closed(): void
    {
        foreach (['wrong-hint', 'unloaded-option', 'null-option', 'wrong-option-model'] as $case) {
            [$q, $t] = $this->fixture(); $h = $q[0]->getRelation('verbHints')[0];
            if ($case === 'wrong-hint') {
                $q[0]->setRelation('verbHints', new Collection([$this->model(QuestionHint::class, $h->getAttributes())]));
            } elseif ($case === 'unloaded-option') { $h->unsetRelation('option'); }
            elseif ($case === 'null-option') { $h->setRelation('option', null); }
            else { $h->setRelation('option', $this->model(QuestionAnswer::class, $h->getRelation('option')->getAttributes())); }
            self::assertNull($this->helper($q[0], $t['first_five'][0]), $case);
            self::assertFalse($this->matchesSnapshot($q, $t), $case);
        }
    }

    public function test_every_raw_hint_attribute_is_bound_including_timestamps_locale_marker_case_and_numeric_ids(): void
    {
        foreach (['id' => 8949999, 'question_id' => 8909999, 'option_id' => 8919999,
            'marker' => 'A2', 'locale' => 'en', 'created_at' => '2001-02-01 00:00:00',
            'updated_at' => '2001-03-01 00:00:00'] as $key => $value) {
            [$q, $t] = $this->fixture(); $this->changed($q[0]->getRelation('verbHints')[0], $key, $value);
            $before = $this->rawState($q);
            self::assertFalse($this->matchesSnapshot($q, $t), $key);
            self::assertSame($before, $this->rawState($q), $key.' must not be silently repaired.');
        }
        [$q, $t] = $this->fixture(); $h = $q[0]->getRelation('verbHints')[0];
        $this->changed($h, 'id', (string) $h->getAttributes()['id']);
        self::assertFalse($this->matchesSnapshot($q, $t), 'String and integer ID snapshots differ.');
    }

    public function test_missing_or_unknown_hint_attributes_are_not_projected_away(): void
    {
        foreach ([...self::HINT_KEYS, 'unreviewed_null'] as $key) {
            [$q, $t] = $this->fixture(); $h = $q[0]->getRelation('verbHints')[0];
            $this->changed($h, $key, null, $key !== 'unreviewed_null');
            self::assertNull($this->helper($q[0], $t['first_five'][0]), $key);
            self::assertFalse($this->matchesSnapshot($q, $t), $key);
        }
    }

    public function test_every_complete_hint_option_attribute_and_unknown_or_missing_fields_are_bound(): void
    {
        foreach (['id' => 8919999, 'option' => 'Changed option', 'created_at' => '1998-01-01 00:00:00',
            'updated_at' => '1998-02-01 00:00:00', 'unreviewed_null' => null] as $key => $value) {
            [$q, $t] = $this->fixture(); $o = $q[0]->getRelation('verbHints')[0]->getRelation('option');
            $this->changed($o, $key, $value);
            self::assertNull($this->helper($q[0], $t['first_five'][0]), $key);
            self::assertFalse($this->matchesSnapshot($q, $t), $key);
        }
        foreach (self::OPTION_KEYS as $key) {
            [$q, $t] = $this->fixture(); $this->changed($q[0]->getRelation('verbHints')[0]->getRelation('option'), $key, null, true);
            self::assertNull($this->helper($q[0], $t['first_five'][0]), $key);
            self::assertFalse($this->matchesSnapshot($q, $t), $key);
        }
    }

    public function test_matching_loaded_hint_and_question_option_timestamps_still_require_the_original_full_snapshot(): void
    {
        foreach (['created_at', 'updated_at'] as $key) {
            [$q, $t] = $this->fixture(); $hintOption = $q[0]->getRelation('verbHints')[0]->getRelation('option');
            $questionOption = $q[0]->getRelation('options')[1];
            $this->changed($hintOption, $key, '1996-01-01 00:00:00');
            $this->changed($questionOption, $key, '1996-01-01 00:00:00');
            self::assertSame($hintOption->getAttributes(), $questionOption->getAttributes());
            self::assertSame($this->hintRows($q[0]), $this->helper($q[0], $t['first_five'][0]));
            self::assertFalse($this->matchesSnapshot($q, $t), $key.' is bound even when both loaded option copies agree.');
        }
    }

    public function test_ownership_and_loaded_option_identity_are_required_even_with_recomputed_hint_digest(): void
    {
        foreach (['foreign-question', 'foreign-option-id', 'missing-question-option', 'duplicate-question-option', 'same-id-different-full-option'] as $case) {
            [$q, $t] = $this->fixture(); $h = $q[0]->getRelation('verbHints')[0];
            if ($case === 'foreign-question') { $this->changed($h, 'question_id', 8909999); }
            elseif ($case === 'foreign-option-id') { $this->changed($h, 'option_id', $h->option_id + 1); }
            elseif ($case === 'missing-question-option') {
                $q[0]->setRelation('options', $q[0]->getRelation('options')->reject(static fn ($o) => $o->id === $h->option_id)->values());
            } elseif ($case === 'duplicate-question-option') {
                $q[0]->setRelation('options', new Collection([
                    ...$q[0]->getRelation('options')->all(), clone $q[0]->getRelation('options')[1],
                ]));
            } else { $this->changed($q[0]->getRelation('options')[1], 'updated_at', '1997-01-01 00:00:00'); }
            $t['first_five'][0]['snapshot_hashes']['verb_hints_snapshot'] = Digest::digest($this->hintRows($q[0]));
            if ($case === 'missing-question-option') {
                self::assertSame($this->hintRows($q[0]), $this->helper($q[0], $t['first_five'][0]),
                    'A hint option may be independently loaded; the complete question option digest still rejects the changed subset.');
            } else { self::assertNull($this->helper($q[0], $t['first_five'][0]), $case); }
            self::assertFalse($this->matchesSnapshot($q, $t), $case);
        }
    }

    public function test_native_filtered_option_subset_accepts_a_separately_loaded_complete_ukrainian_hint_option(): void
    {
        [$q, $t, $excludedPivot] = $this->fixtureWithSeparateHintOption();
        self::assertSame(['question_id' => $q[0]->id, 'option_id' => 8960000, 'flag' => 1], $excludedPivot);
        self::assertCount(4, $q[0]->getRelation('options'));
        self::assertNotContains(8960000, $q[0]->getRelation('options')->pluck('id')->all());
        self::assertNotContains(8960000, $q[0]->getRelation('answers')->pluck('option_id')->all());
        self::assertSame('чекати', $q[0]->getRelation('verbHints')[0]->getRelation('option')->getAttributes()['option']);
        $before = $this->rawState($q);
        self::assertSame($this->hintRows($q[0]), $this->helper($q[0], $t['first_five'][0]));
        self::assertTrue($this->matchesSnapshot($q, $t), 'The native English option subset and complete separate hint snapshot both match.');
        self::assertSame($before, $this->rawState($q));
    }

    public function test_separate_hint_option_text_timestamps_schema_and_loaded_identity_remain_exactly_bound(): void
    {
        foreach (['option' => 'інше дієслово', 'created_at' => '1994-01-01 00:00:00',
            'updated_at' => '1994-02-01 00:00:00'] as $key => $value) {
            [$q, $t] = $this->fixtureWithSeparateHintOption();
            $this->changed($q[0]->getRelation('verbHints')[0]->getRelation('option'), $key, $value);
            self::assertSame($this->hintRows($q[0]), $this->helper($q[0], $t['first_five'][0]),
                'A structurally complete separate row is returned, not dropped or substituted from question.options.');
            self::assertFalse($this->matchesSnapshot($q, $t), $key.' must still match the independently frozen full option snapshot.');
        }
        foreach (['missing-attribute', 'unknown-attribute', 'unloaded-option', 'null-option', 'wrong-model', 'foreign-option-id'] as $case) {
            [$q, $t] = $this->fixtureWithSeparateHintOption(); $hint = $q[0]->getRelation('verbHints')[0];
            if ($case === 'missing-attribute') { $this->changed($hint->getRelation('option'), 'updated_at', null, true); }
            elseif ($case === 'unknown-attribute') { $this->changed($hint->getRelation('option'), 'foreign_null', null); }
            elseif ($case === 'unloaded-option') { $hint->unsetRelation('option'); }
            elseif ($case === 'null-option') { $hint->setRelation('option', null); }
            elseif ($case === 'wrong-model') { $hint->setRelation('option', $this->model(QuestionAnswer::class, $hint->getRelation('option')->getAttributes())); }
            else { $this->changed($hint, 'option_id', 8960001); }
            self::assertNull($this->helper($q[0], $t['first_five'][0]), $case);
            self::assertFalse($this->matchesSnapshot($q, $t), $case);
        }
    }

    public function test_hint_count_duplicate_ids_and_loaded_order_are_not_erased_by_sorting(): void
    {
        foreach (['short', 'extra', 'duplicate-id', 'reverse'] as $case) {
            [$q, $t] = $this->fixture(); $hints = $q[0]->getRelation('verbHints');
            if ($case === 'short') { $q[0]->setRelation('verbHints', $hints->take(1)); }
            elseif ($case === 'extra') {
                $extra = clone $hints[0]; $this->changed($extra, 'id', $extra->id + 9);
                $q[0]->setRelation('verbHints', new Collection([...$hints->all(), $extra]));
            } elseif ($case === 'reverse') { $q[0]->setRelation('verbHints', $hints->reverse()->values()); }
            else {
                $this->changed($hints[1], 'id', $hints[0]->id);
                $t['first_five'][0]['snapshot_hashes']['verb_hints_snapshot'] = Digest::digest($this->hintRows($q[0]));
                self::assertNull($this->helper($q[0], $t['first_five'][0]), 'Duplicate IDs are structurally invalid even if a hash is recomputed.');
            }
            self::assertFalse($this->matchesSnapshot($q, $t), $case);
        }
    }

    public function test_attribute_key_order_is_canonicalized_but_actual_hint_row_order_is_preserved(): void
    {
        [$q, $t] = $this->fixture(); $h = $q[0]->getRelation('verbHints')[0]; $o = $h->getRelation('option');
        $h->setRawAttributes(array_reverse($h->getAttributes(), true), true);
        $o->setRawAttributes(array_reverse($o->getAttributes(), true), true);
        self::assertSame($this->hintRows($q[0]), $this->helper($q[0], $t['first_five'][0]));
        self::assertTrue($this->matchesSnapshot($q, $t));
        self::assertSame(['a2', 'A4'], array_column(array_column($this->helper($q[0], $t['first_five'][0]), 'attributes'), 'marker'));
    }

    public function test_full_question_answer_option_pivot_hint_and_five_id_guards_still_apply(): void
    {
        foreach (['question-timestamp', 'question-extra', 'answer-marker', 'answer-option', 'pivot',
            'ordinary-hint', 'reverse-questions', 'four-questions', 'question-id', 'question-uuid'] as $case) {
            [$q, $t] = $this->fixture();
            if ($case === 'question-timestamp') { $this->changed($q[0], 'updated_at', '2002-01-01 00:00:00'); }
            elseif ($case === 'question-extra') { $this->changed($q[0], 'unknown_null', null); }
            elseif ($case === 'answer-marker') { $this->changed($q[0]->getRelation('answers')[0], 'marker', 'a99'); }
            elseif ($case === 'answer-option') { $this->changed($q[0]->getRelation('answers')[0]->getRelation('option'), 'option', 'Changed'); }
            elseif ($case === 'pivot') { $this->changed($q[0]->getRelation('options')[0]->getRelation('pivot'), 'flag', 1); }
            elseif ($case === 'ordinary-hint') {
                $q[0]->setRelation('hints', new Collection([$this->model(QuestionHint::class, ['id' => 8950000,
                    'question_id' => $q[0]->id, 'provider' => 'synthetic', 'locale' => 'uk', 'hint' => 'Інша підказка.'])]));
            } elseif ($case === 'reverse-questions') { $q = $q->reverse()->values(); }
            elseif ($case === 'four-questions') { $q = $q->take(4)->values(); }
            elseif ($case === 'question-id') { $this->changed($q[0], 'id', 8909999); }
            else { $this->changed($q[0], 'uuid', 'foreign-uuid'); }
            self::assertFalse($this->matchesSnapshot($q, $t), $case);
        }
    }

    public function test_guard_only_reads_original_attributes_relations_and_never_marks_models_saved_or_dirty(): void
    {
        [$q, $t] = $this->fixture(); $before = $this->rawState($q);
        self::assertTrue($this->matchesSnapshot($q, $t)); self::assertTrue($this->matchesSnapshot($q, $t));
        self::assertSame($before, $this->rawState($q));
        foreach ($q as $question) { self::assertFalse($question->exists); self::assertSame([], $question->getDirty()); }
        foreach ($q[0]->getRelation('verbHints') as $hint) {
            self::assertFalse($hint->exists); self::assertSame([], $hint->getDirty());
            self::assertFalse($hint->getRelation('option')->exists); self::assertSame([], $hint->getRelation('option')->getDirty());
        }
    }
}

final class M89ForbiddenConnectionResolver implements ConnectionResolverInterface
{
    public int $calls = 0;
    public function connection($name = null) { $this->calls++; throw new RuntimeException('M89 pure snapshot guard attempted a connection.'); }
    public function getDefaultConnection() { return 'forbidden'; }
    public function setDefaultConnection($name) { throw new RuntimeException('M89 pure snapshot guard attempted to change a connection.'); }
}
