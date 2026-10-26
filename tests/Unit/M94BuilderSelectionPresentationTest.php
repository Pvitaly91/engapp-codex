<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Support\M26DetailPackage as Digest;
use App\Support\M51QuestionSupplement;
use App\Support\M94BuilderSelectionPresentation as Selection;
use App\Support\M94RelativeClausesWhoseWhich as Package;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/**
 * Public teaching identities plus invented unsaved full snapshots exercise structural guards.
 * Recomputed invented hashes are NOT captured SQL evidence or real five-question acceptance.
 */
class M94BuilderSelectionPresentationTest extends TestCase
{
    private ?ConnectionResolverInterface $prior = null;
    private M94ForbiddenConnectionResolver $forbidden;

    protected function setUp(): void
    {
        parent::setUp(); $this->prior = Model::getConnectionResolver();
        $this->forbidden = new M94ForbiddenConnectionResolver; Model::setConnectionResolver($this->forbidden);
    }

    protected function tearDown(): void
    {
        try { self::assertSame(0, $this->forbidden->calls, 'No query or lazy relation resolution is allowed.'); }
        finally {
            if ($this->prior === null) { Model::unsetConnectionResolver(); } else { Model::setConnectionResolver($this->prior); }
            parent::tearDown();
        }
    }

    private function model(string $class, array $attrs): Model
    {
        $m = new $class; $m->setRawAttributes($attrs, true); return $m;
    }

    private function change(Model $model, string $key, mixed $value): void
    {
        $model->setRawAttributes(array_replace($model->getAttributes(), [$key => $value]), true);
    }

    private function snapshotMatches(Collection $questions, array $target): bool
    {
        return (new ReflectionMethod(TheoryBuilderSelectionPresentation::class, 'matchesQuestions'))->invoke(null, $questions, $target);
    }

    private function project(Collection $original, Collection $conditionClones, array $target): ?Collection
    {
        return (new ReflectionMethod(Selection::class, 'projectLoadedHints'))->invoke(null, $original, $conditionClones, $target);
    }

    private function state(Collection $questions): array
    {
        return $questions->map(static function (Question $q): array {
            $relations = [];
            foreach ($q->getRelations() as $name => $rows) {
                $relations[$name] = ['collection_id' => spl_object_id($rows), 'rows' => []];
                foreach ($rows as $row) {
                    $children = [];
                    foreach ($row->getRelations() as $key => $child) { $children[$key] = ['id' => spl_object_id($child), 'attrs' => $child->getAttributes()]; }
                    $relations[$name]['rows'][] = ['id' => spl_object_id($row), 'attrs' => $row->getAttributes(), 'original' => $row->getRawOriginal(), 'relations' => $children];
                }
            }
            return ['object_id' => spl_object_id($q), 'attrs' => $q->getAttributes(), 'original' => $q->getRawOriginal(), 'exists' => $q->exists, 'relations' => $relations];
        })->all();
    }

    /** Only IDs/teaching conditions/tokens are public-source-derived; every other row value is invented. */
    private function inventedHydration(): array
    {
        $audit = Selection::loadAudit(dirname(__DIR__, 2)); $target = $audit['targets'][0]; $questions = [];
        foreach ($target['first_five'] as $i => &$sample) {
            $qid = $sample['id']; $base = 9940000 + $i * 100;
            $attrs = ['id' => $qid, 'uuid' => $sample['uuid'], 'question' => 'Invented original condition '.$i.'.',
                'difficulty' => 1, 'level' => 'B2', 'category_id' => 990001, 'source_id' => 990002, 'flag' => 0, 'type' => '4',
                'options_by_marker' => null, 'theory_text_block_uuid' => null, 'seeder' => 'M94InventedSnapshotFixture',
                'created_at' => '2000-01-01 00:00:00', 'updated_at' => '2000-01-01 00:00:00'];
            $q = $this->model(Question::class, $attrs); $options = $optionRows = $pivots = [];
            foreach ($sample['token_bank_with_multiplicity'] as $n => $token) {
                $o = $this->model(QuestionOption::class, ['id' => $base + $n, 'option' => $token, 'created_at' => '2000-01-02 00:00:00', 'updated_at' => '2000-01-02 00:00:00']);
                $pivot = ['question_id' => $qid, 'option_id' => $o->id, 'flag' => null];
                $o->setRelation('pivot', $this->model(Pivot::class, $pivot));
                $options[] = $o; $optionRows[] = ['id' => $o->id, 'option' => $token]; $pivots[] = $pivot;
            }
            $answers = $answerRows = $used = [];
            foreach ($sample['correct_tokens'] as $n => $token) {
                $found = null;
                foreach ($options as $k => $o) { if (!isset($used[$k]) && $o->option === $token) { $found = $k; break; } }
                self::assertNotNull($found); $used[$found] = true; $o = $options[$found];
                $a = $this->model(QuestionAnswer::class, ['id' => $base + 50 + $n, 'question_id' => $qid, 'option_id' => $o->id, 'marker' => 'a'.($n + 1), 'position' => null]);
                $a->setRelation('option', $o); $answers[] = $a;
                $answerRows[] = ['id' => $a->id, 'marker' => $a->marker, 'position' => null, 'option_id' => $o->id, 'option' => $token];
            }
            $hints = $hintRows = [];
            foreach ($sample['visible_hints_uk'] as $n => $visible) {
                $row = ['id' => $visible['id'], 'question_id' => $qid, 'provider' => 'm94-invented', 'locale' => 'en', 'hint' => 'Invented original explanation '.$i.'-'.$n.'.'];
                $hints[] = $this->model(QuestionHint::class, $row); $hintRows[] = $row;
            }
            // A synthetic flag1-style hint option stays separate from the English native options.
            $verbHints = $verbRows = [];
            if ($i === 0) {
                $optionAttrs = ['id' => 9999901, 'option' => 'окрема синтетична дієслівна підказка', 'created_at' => '2000-01-03 00:00:00', 'updated_at' => '2000-01-03 00:00:00'];
                $hintAttrs = ['id' => 9999902, 'question_id' => $qid, 'option_id' => 9999901, 'marker' => 'a1', 'locale' => 'uk', 'created_at' => '2000-01-03 00:00:00', 'updated_at' => '2000-01-03 00:00:00'];
                $h = $this->model(VerbHint::class, $hintAttrs); $h->setRelation('option', $this->model(QuestionOption::class, $optionAttrs));
                $verbHints[] = $h; $verbRows[] = ['attributes' => $hintAttrs, 'option' => $optionAttrs];
            }
            $q->setRelation('answers', new Collection($answers)); $q->setRelation('options', new Collection($options));
            $q->setRelation('hints', new Collection($hints)); $q->setRelation('verbHints', new Collection($verbHints));
            $sample['question_attribute_keys'] = M51QuestionSupplement::ROW_COLUMNS['questions']; $ordered = [];
            foreach ($sample['question_attribute_keys'] as $key) { $ordered[$key] = $attrs[$key]; }
            $sample['snapshot_hashes'] = array_map(Digest::digest(...), ['question_snapshot' => $ordered,
                'ordered_answer_snapshot' => $answerRows, 'original_options_snapshot' => $optionRows,
                'loaded_option_pivots' => $pivots, 'hints_snapshot' => $hintRows, 'verb_hints_snapshot' => $verbRows]);
            $questions[] = $q;
        }
        unset($sample); return [new Collection($questions), $target];
    }

    private function conditionClones(Collection $original, array $target): Collection
    {
        return $original->values()->map(function (Question $q, int $i) use ($target): Question {
            $copy = clone $q; $this->change($copy, 'question', $target['first_five'][$i]['visible_condition_uk']); return $copy;
        });
    }

    public function test_public_five_bind_only_exact_b2_bank_and_plain_ukrainian_explanation_maps(): void
    {
        $audit = Selection::loadAudit(dirname(__DIR__, 2)); self::assertCount(1, $audit['targets']); $target = $audit['targets'][0];
        self::assertSame(Package::OWNERS[0], $target['identity']); self::assertSame('B2', $target['level']);
        self::assertSame(Package::BANKS, $target['bank_seeders']); self::assertCount(48, $target['expected_pool_ids_by_bank'][Package::BANKS[0]]);
        self::assertSame([17164, 17124, 17140, 17123, 17138], $target['expected_selected_ids']);
        self::assertCount(43, $target['excluded_question_ids']); self::assertCount(5, $target['first_five']);
        $uuids = array_column($target['first_five'], 'uuid'); $sorted = $uuids; sort($sorted); self::assertSame($sorted, $uuids);
        foreach ($target['first_five'] as $sample) {
            self::assertSame('complete-loaded-verb-hints-v1', $sample['verb_hint_snapshot_schema']);
            self::assertSame([], $sample['token_presentations']); self::assertSame([], $sample['option_presentations']);
            foreach ($sample['visible_hints_uk'] as $row) { self::assertSame(['id', 'hint'], array_keys($row)); self::assertIsInt($row['id']); self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $row['hint']); self::assertDoesNotMatchRegularExpression('/<[^>]+>|\{a\d+\}|&[a-z]+;/iu', $row['hint']); }
        }
    }

    public function test_invented_complete_snapshot_proof_then_deep_hint_clones_leave_every_original_relation_intact(): void
    {
        [$q, $target] = $this->inventedHydration(); $before = $this->state($q);
        self::assertTrue($this->snapshotMatches($q, $target)); $conditionClones = $this->conditionClones($q, $target);
        $projected = $this->project($q, $conditionClones, $target); self::assertInstanceOf(Collection::class, $projected); self::assertCount(5, $projected);
        foreach ($projected as $i => $copy) {
            self::assertNotSame($q[$i], $copy); self::assertNotSame($conditionClones[$i], $copy);
            self::assertSame($target['first_five'][$i]['visible_condition_uk'], $copy->question);
            self::assertNotSame($q[$i]->getRelation('hints'), $copy->getRelation('hints'));
            foreach ($copy->getRelation('hints') as $n => $hint) {
                $old = $q[$i]->getRelation('hints')[$n]; self::assertNotSame($old, $hint);
                self::assertSame(array_replace($old->getAttributes(), ['hint' => $target['first_five'][$i]['visible_hints_uk'][$n]['hint']]), $hint->getAttributes());
                self::assertSame([], $hint->getDirty()); self::assertFalse($hint->exists);
            }
            foreach (['answers', 'options', 'verbHints'] as $name) { self::assertSame($q[$i]->getRelation($name), $copy->getRelation($name)); }
        }
        self::assertSame($before, $this->state($q));
    }

    public function test_missing_edited_or_unloaded_original_hints_refuse_the_upstream_full_snapshot_without_repair(): void
    {
        foreach (range(0, 4) as $index) {
            foreach (['missing', 'edited', 'unloaded', 'foreign-question'] as $case) {
                [$q, $target] = $this->inventedHydration();
                if ($case === 'missing') { $q[$index]->setRelation('hints', new Collection); }
                elseif ($case === 'unloaded') { $q[$index]->unsetRelation('hints'); }
                else { $hint = $q[$index]->getRelation('hints')[0]; $this->change($hint, $case === 'edited' ? 'hint' : 'question_id', $case === 'edited' ? 'Changed original explanation.' : -1); }
                $before = $this->state($q); self::assertFalse($this->snapshotMatches($q, $target), $index.':'.$case); self::assertSame($before, $this->state($q));
            }
        }
    }

    public function test_translation_map_mismatch_refuses_atomically_instead_of_returning_half_translated_conditions(): void
    {
        foreach (['missing-map', 'wrong-id', 'empty', 'english-only', 'html', 'marker', 'original-object', 'wrong-condition'] as $case) {
            [$q, $target] = $this->inventedHydration(); $clones = $this->conditionClones($q, $target); $before = $this->state($q);
            if ($case === 'missing-map') { unset($target['first_five'][4]['visible_hints_uk']); }
            elseif ($case === 'wrong-id') { $target['first_five'][4]['visible_hints_uk'][0]['id'] = -1; }
            elseif ($case === 'original-object') { $clones[4] = $q[4]; }
            elseif ($case === 'wrong-condition') { $this->change($clones[4], 'question', 'Unreviewed condition.'); }
            else { $target['first_five'][4]['visible_hints_uk'][0]['hint'] = match ($case) { 'empty' => '', 'english-only' => 'English only.', 'html' => '<p>Пояснення.</p>', default => 'Пояснення {a1}.' }; }
            self::assertNull($this->project($q, $clones, $target), $case); self::assertSame($before, $this->state($q));
        }
    }

    public function test_hint_options_never_enter_english_bank_and_non_theory_bindings_keep_original_collection(): void
    {
        [$q, $target] = $this->inventedHydration(); $before = $this->state($q);
        $projected = $this->project($q, $this->conditionClones($q, $target), $target); self::assertNotNull($projected);
        $hintOption = $q[0]->getRelation('verbHints')[0]->getRelation('option');
        self::assertNotContains($hintOption->id, $projected[0]->getRelation('options')->pluck('id')->all());
        self::assertSame($q[0]->getRelation('options'), $projected[0]->getRelation('options'));
        foreach ($projected as $question) { foreach ($question->getRelation('options') as $option) { self::assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $option->option); } }
        foreach ([[], ['theoryCanonical' => false], ['theoryCanonical' => 'true']] as $caller) {
            $bindings = $caller + ['practiceQuestions' => $q]; self::assertSame($bindings, Selection::bindings($bindings));
        }
        self::assertSame($before, $this->state($q));
    }
}

final class M94ForbiddenConnectionResolver implements ConnectionResolverInterface
{
    public int $calls = 0;
    public function connection($name = null) { ++$this->calls; throw new RuntimeException('M94 forbidden database connection.'); }
    public function getDefaultConnection() { return 'forbidden'; }
    public function setDefaultConnection($name) { throw new RuntimeException('M94 forbidden database resolver mutation.'); }
}
