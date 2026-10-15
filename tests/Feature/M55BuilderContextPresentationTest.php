<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M55BuilderContextPresentation as Presentation;
use App\Support\TheorySentenceBuilderAcceptance;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Independent BEFORE model fixtures only: no working .env, MySQL, network or progress. */
class M55BuilderContextPresentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk'); $this->withoutVite();
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function audit(): array
    {
        self::assertSame(Presentation::AUDIT_SHA, hash_file('sha256', base_path(Presentation::AUDIT)));
        return json_decode(file_get_contents(base_path(Presentation::AUDIT)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function questions(array $target): ModelCollection
    {
        $options = []; $questions = [];
        foreach ($target['first_five'] as $sample) {
            foreach ($sample['original_options_snapshot'] as $row) {
                if (!isset($options[$row['id']])) {
                    $option = new QuestionOption; $option->setRawAttributes($row, true); $options[$row['id']] = $option;
                }
                self::assertSame($row['option'], $options[$row['id']]->option);
            }
        }
        foreach ($target['first_five'] as $sample) {
            $metadata = json_encode(array_fill_keys(array_keys($sample['answers']), $sample['source_options']),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            self::assertSame($sample['question_options_by_marker_sha256'], hash('sha256', $metadata));
            $question = new Question;
            $question->setRawAttributes(['id' => $sample['id'], 'uuid' => $sample['uuid'], 'question' => $sample['condition_uk'],
                'seeder' => $target['bank_seeders'][0], 'type' => '4', 'level' => $target['level'], 'options_by_marker' => $metadata], true);
            $answers = [];
            foreach ($sample['ordered_answer_snapshot'] as $row) {
                $answer = new QuestionAnswer; $answer->setRawAttributes(['id' => $row['id'], 'question_id' => $sample['id'],
                    'marker' => $row['marker'], 'position' => $row['position'], 'option_id' => $row['option_id']], true);
                self::assertSame($row['option'], $options[$row['option_id']]->option);
                $answer->setRelation('option', $options[$row['option_id']]); $answers[] = $answer;
            }
            $question->setRelation('answers', new ModelCollection($answers));
            $question->setRelation('options', new ModelCollection(array_map(static fn ($row) => $options[$row['id']], $sample['original_options_snapshot'])));
            foreach (['tags', 'hints', 'verbHints', 'markerTags'] as $relation) { $question->setRelation($relation, new ModelCollection); }
            $questions[] = $question;
        }
        return new ModelCollection($questions);
    }

    private function binding(ModelCollection $questions): array
    {
        $audit = $this->audit(); $practice = $audit['practice_bindings'][1];
        $patch = json_decode(file_get_contents(database_path('content-patches/m55-passive-review-infinitive.v1.json')), true, flags: JSON_THROW_ON_ERROR);
        $target = $patch['targets'][1]; $slot = $practice['source_index']; $source = $target['after']['page']['blocks'][$slot];
        self::assertSame('practice-set', $source['type']);
        self::assertSame($practice['body_sha256'], hash('sha256', $source['body']));
        self::assertSame($practice['uuid'], M26DetailPackage::uuid($target['identity'], $source, $slot + 1));
        $block = new TextBlock;
        $block->setRawAttributes(['id' => 5500001, 'uuid' => $practice['uuid'], 'page_id' => 94, 'page_category_id' => 1,
            'seeder' => $target['identity'], 'locale' => 'uk', 'type' => 'practice-set', 'level' => 'B2',
            'sort_order' => $slot + 1, 'column' => $source['column'], 'body' => $source['body'],
            'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null], true);
        $block->setRelation('tags', new ModelCollection); $block->setRelation('page', null);
        return ['block' => $block, 'theoryCanonical' => true, 'practiceQuestions' => $questions];
    }

    private function project(ModelCollection $questions, array $target, array $projection): mixed
    {
        return (new ReflectionMethod(Presentation::class, 'questions'))->invoke(null, $questions, $target, $projection);
    }

    public function test_only_five_reviewed_conditions_change_in_clones_without_query_answer_option_or_model_mutation(): void
    {
        $audit = $this->audit(); $target = $audit['targets'][1]; $questions = $this->questions($target);
        $original = $questions->toArray(); $bindings = $this->binding($questions);
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        $result = Presentation::bindings($bindings); $copy = $result['practiceQuestions'];
        self::assertNotSame($questions, $copy); self::assertCount(5, $copy);
        foreach ($questions as $i => $question) {
            self::assertNotSame($question, $copy[$i]);
            $expected = $question->getAttributes(); $expected['question'] = $audit['condition_projection']['records'][$i]['condition_uk'];
            self::assertSame($expected, $copy[$i]->getAttributes()); self::assertSame([], $copy[$i]->getDirty());
            self::assertStringEndsWith($question->question, $copy[$i]->question);
            foreach (['answers', 'options', 'hints', 'verbHints', 'tags', 'markerTags'] as $relation) {
                self::assertSame($question->getRelation($relation), $copy[$i]->getRelation($relation));
            }
        }
        self::assertSame($original, $questions->toArray());
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_last_question_or_snapshot_drift_refuses_the_entire_collection_before_any_prefix_or_query(): void
    {
        $audit = $this->audit(); $target = $audit['targets'][1]; $projection = $audit['condition_projection'];
        $mutations = [
            'id' => fn ($q) => $q[4]->id = 999, 'uuid' => fn ($q) => $q[4]->uuid = 'foreign',
            'bank' => fn ($q) => $q[4]->seeder = 'ForeignBank', 'type' => fn ($q) => $q[4]->type = '0',
            'level' => fn ($q) => $q[4]->level = 'B1', 'condition' => fn ($q) => $q[4]->question .= ' Зміна.',
            'unloaded-answers' => fn ($q) => $q[4]->unsetRelation('answers'),
            'unloaded-options' => fn ($q) => $q[4]->unsetRelation('options'),
            'unloaded-hints' => fn ($q) => $q[4]->unsetRelation('hints'),
            'unloaded-verb-hints' => fn ($q) => $q[4]->unsetRelation('verbHints'),
            'unloaded-answer-option' => fn ($q) => $q[4]->answers[0]->unsetRelation('option'),
            'answer-id' => fn ($q) => $q[4]->answers[0]->id = 999,
            'answer-question' => fn ($q) => $q[4]->answers[0]->question_id = 999,
            'answer-marker' => fn ($q) => $q[4]->answers[0]->marker = 'a99',
            'answer-option-id' => fn ($q) => $q[4]->answers[0]->option_id = 999,
            'answer-option-value' => fn ($q) => $q[4]->answers[0]->option->option = 'Foreign',
            'shared-option-value' => fn ($q) => $q[4]->options[0]->option = 'Foreign',
            'shared-option-id' => fn ($q) => $q[4]->options[0]->id = 999,
            'raw-marker-options' => fn ($q) => $q[4]->setRawAttributes(array_replace($q[4]->getAttributes(), ['options_by_marker' => '{}']), true),
            'nonempty-hints' => fn ($q) => $q[4]->setRelation('hints', new ModelCollection([new QuestionHint])),
            'nonempty-verb-hints' => fn ($q) => $q[4]->setRelation('verbHints', new ModelCollection([new QuestionAnswer])),
        ];
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ($mutations as $name => $mutation) {
            $questions = $this->questions($target); $mutation($questions); $before = $questions->toArray();
            self::assertNull($this->project($questions, $target, $projection), $name);
            self::assertSame($before, $questions->toArray(), $name.' must not partially prefix questions 1–4');
        }
        $questions = $this->questions($target);
        foreach ([new ModelCollection($questions->take(4)->all()), new ModelCollection([$questions[1], $questions[0], $questions[2], $questions[3], $questions[4]]),
            new ModelCollection([$questions[0], $questions[0], $questions[2], $questions[3], $questions[4]])] as $bad) {
            self::assertNull($this->project($bad, $target, $projection));
        }
        foreach (['foreign-record', 'changed-original', 'empty-prefix', 'html-prefix', 'changed-answer', 'changed-output', 'short-records'] as $case) {
            $bad = $projection; $badTarget = $target;
            if ($case === 'foreign-record') { $bad['records'][4]['question_id'] = 999; }
            if ($case === 'changed-original') { $bad['records'][4]['before_condition_uk'] = 'Інша умова.'; }
            if ($case === 'empty-prefix') { $bad['records'][4]['context_prefix_uk'] = ''; }
            if ($case === 'html-prefix') { $bad['records'][4]['context_prefix_uk'] = '<b>Префікс</b>'; }
            if ($case === 'changed-answer') { $badTarget['first_five'][4]['ordered_answer_snapshot'][0]['option'] = 'Foreign'; }
            if ($case === 'changed-output') { $bad['records'][4]['condition_uk'] = 'Інший зміст.'; }
            if ($case === 'short-records') { array_pop($bad['records']); }
            self::assertNull($this->project($questions, $badTarget, $bad), $case);
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_only_exact_frozen_canonical_uk_formality_practice_receives_context(): void
    {
        $audit = $this->audit(); $bindings = $this->binding($this->questions($audit['targets'][1]));
        foreach (['seeder' => $audit['targets'][0]['identity'], 'page_id' => 103, 'page_category_id' => 17, 'locale' => 'en',
            'level' => 'A2', 'type' => 'usage-panels', 'uuid' => 'foreign', 'sort_order' => 999, 'column' => 'header',
            'heading' => 'foreign', 'css_class' => 'foreign', 'body' => $bindings['block']->body."\n"] as $field => $value) {
            $bad = $bindings; $bad['block'] = clone $bindings['block']; $bad['block']->$field = $value;
            self::assertSame($bad, Presentation::bindings($bad), $field);
        }
        foreach (['en', 'pl'] as $locale) {
            app()->setLocale($locale); self::assertSame($bindings, Presentation::bindings($bindings));
        }
        app()->setLocale('uk'); $bad = $bindings; $bad['theoryCanonical'] = false; self::assertSame($bad, Presentation::bindings($bad));
        unset($bad['theoryCanonical']); self::assertSame($bad, Presentation::bindings($bad));
        $bad = $bindings; $bad['practiceQuestions'] = $bindings['practiceQuestions']->take(4); self::assertSame($bad, Presentation::bindings($bad));
        foreach ([0, 2] as $i) {
            $bad = $bindings; $bad['practiceQuestions'] = $this->questions($audit['targets'][$i]); self::assertSame($bad, Presentation::bindings($bad));
        }
        $source = file_get_contents(base_path('app/Support/TheoryContentRenderer.php'));
        self::assertSame(1, substr_count($source, 'M55BuilderContextPresentation::bindings($bindings)'));
        self::assertLessThan(strpos($source, 'M55BuilderContextPresentation::bindings($bindings)'), strpos($source, 'M54BuilderTokenPresentation::bindings($bindings)'));
        self::assertSame('68734166024d4c3f65b6ebb585a9f26f396f7d79204d0c4f9a7ff99d2164a1bb', hash_file('sha256', base_path('app/Support/M54BuilderTokenPresentation.php')));
    }

    public function test_unchanged_native_widget_keeps_all_grading_data_case_and_token_instances_while_only_conditions_gain_context(): void
    {
        $audit = $this->audit(); $target = $audit['targets'][1]; $questions = $this->questions($target); $original = $questions->toArray();
        $bindings = $this->binding($questions); $uuid = $bindings['block']->uuid;
        $before = view('components.text-block-practice-questions', ['questions' => $questions, 'blockUuid' => $uuid])->render();
        $projected = Presentation::bindings($bindings)['practiceQuestions'];
        $after = view('components.text-block-practice-questions', ['questions' => $projected, 'blockUuid' => $uuid])->render();
        $old = TheorySentenceBuilderAcceptance::payload($before, $uuid); $new = TheorySentenceBuilderAcceptance::payload($after, $uuid);
        self::assertCount(5, $new); self::assertSame($target['expected_existing_selected_ids'], array_column($new, 'id'));
        foreach ($new as $i => $payload) {
            self::assertSame($audit['condition_projection']['records'][$i]['condition_uk'], $payload['question']);
            self::assertSame($target['first_five'][$i]['correct_tokens'], $payload['correct_tokens']);
            $a = $old[$i]; $b = $payload; unset($a['question'], $b['question']); self::assertSame($a, $b);
        }
        self::assertSame(1, substr_count($after, 'data-sentence-builder'));
        self::assertSame(2, count(array_filter($new[0]['correct_tokens'], static fn ($t) => mb_strtolower($t) === 'the')));
        self::assertSame($original, $questions->toArray());
        $excluded = $bindings; $excluded['theoryCanonical'] = false;
        self::assertSame($excluded, Presentation::bindings($excluded));
    }
}
