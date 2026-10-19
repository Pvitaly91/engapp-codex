<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M53BuilderHintPresentation as Presentation;
use App\Support\M53PassiveConstructionsPackage as Package;
use App\Support\TheoryContentRenderer;
use App\Support\TheorySentenceBuilderAcceptance;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Model and canonical-render fixtures only: no working environment, HTTP, MySQL or progress. */
class M53BuilderHintPresentationTest extends TestCase
{
    private const TRANSLATIONS = [
        'Каузатив із have: have/had + object + V3.',
        'Їй відремонтували машину: хтось виконав ремонт для неї.',
        'Модель: had + object + painted.',
        'Йому полагодили телефон: він організував ремонт.',
        'Їм оновили паспорти: вони скористалися послугою.',
    ];

    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk'); $this->withoutVite();
        self::assertSame('testing', app()->environment());
        self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function audit(): array
    {
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', Presentation::AUDIT_SHA);
        self::assertSame(Presentation::AUDIT_SHA, hash_file('sha256', base_path(Presentation::AUDIT)));

        return json_decode(file_get_contents(base_path(Presentation::AUDIT)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function questions(array $audit): ModelCollection
    {
        $out = []; $target = $audit['targets'][0];
        foreach ($target['first_five'] as $i => $sample) {
            $question = new Question;
            $question->setRawAttributes(['id' => $sample['id'], 'uuid' => $sample['uuid'],
                'question' => $sample['condition_uk'], 'seeder' => $target['bank_seeders'][0], 'type' => '4', 'level' => 'B1'], true);
            $answers = [];
            foreach ($sample['ordered_answer_snapshot'] as $snapshot) {
                $option = new QuestionOption; $option->setRawAttributes(['id' => $snapshot['option_id'], 'option' => $snapshot['option']], true);
                $answer = new QuestionAnswer; $answer->setRawAttributes(['id' => $snapshot['id'], 'question_id' => $sample['id'],
                    'marker' => $snapshot['marker'], 'position' => $snapshot['position'], 'option_id' => $snapshot['option_id']], true);
                $answer->setRelation('option', $option); $answers[] = $answer;
            }
            $hint = new QuestionHint; $hint->setRawAttributes($audit['hint_projection']['records'][$i]['before'], true);
            $options = [];
            foreach ($sample['source_options'] as $n => $value) {
                $option = new QuestionOption; $option->setRawAttributes(['id' => 5300000 + $n, 'option' => $value], true); $options[] = $option;
            }
            $question->setRelation('answers', new ModelCollection($answers));
            $question->setRelation('hints', new ModelCollection([$hint]));
            $question->setRelation('options', new ModelCollection($options));
            foreach (['tags', 'verbHints', 'markerTags'] as $relation) { $question->setRelation($relation, new ModelCollection); }
            $out[] = $question;
        }

        return new ModelCollection($out);
    }

    private function copiedQuestions(ModelCollection $questions): ModelCollection
    {
        return $questions->map(static function (Question $question): Question {
            $copy = clone $question;
            foreach (['answers', 'hints'] as $name) {
                $copy->setRelation($name, $question->getRelation($name)->map(static function ($model) use ($name) {
                    $clone = clone $model;
                    if ($name === 'answers') { $clone->setRelation('option', clone $model->getRelation('option')); }

                    return $clone;
                }));
            }

            return $copy;
        });
    }

    private function binding(ModelCollection $questions): array
    {
        [, $patch] = Package::load(); $target = $patch['targets'][0];
        $slot = count($target['after']['page']['blocks']) - 1;
        $source = $target['after']['page']['blocks'][$slot];
        self::assertSame('practice-set', $source['type']);
        $block = new TextBlock;
        $block->setRawAttributes(['id' => 5300092, 'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'page_id' => 92, 'page_category_id' => 1, 'seeder' => $target['identity'], 'locale' => 'uk', 'type' => 'practice-set',
            'level' => 'B1', 'column' => $source['column'], 'sort_order' => $slot + 1, 'body' => $source['body'],
            'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null], true);
        $block->setRelation('tags', new ModelCollection); $block->setRelation('page', null);
        $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
        $shown = Package::presentation($block, $data); self::assertNotNull($shown);

        return ['block' => $block, 'data' => $data, 'theoryCanonical' => true, 'practiceQuestions' => $questions,
            'lessonLinks' => [], 'm42Design' => $shown['m42_native_design']];
    }

    private function project(ModelCollection $questions, array $audit): ?Collection
    {
        return (new ReflectionMethod(Presentation::class, 'questions'))->invoke(null, $questions, $audit['targets'][0], $audit['hint_projection']);
    }

    public function test_exact_five_historical_hints_have_finite_reviewed_translations_and_only_their_clones_change(): void
    {
        $audit = $this->audit(); $questions = $this->questions($audit);
        self::assertSame([16659, 16660, 16661, 16662, 16663], $questions->pluck('id')->all());
        self::assertSame([8521, 8522, 8523, 8524, 8525], array_column(array_column($audit['hint_projection']['records'], 'before'), 'id'));
        self::assertSame(self::TRANSLATIONS, array_column($audit['hint_projection']['records'], 'hint_uk'));
        $before = $questions->map(static fn ($q) => [$q->getAttributes(), $q->hints->first()->getAttributes()])->all();
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        $copy = $this->project($questions, $audit); self::assertNotNull($copy); self::assertNotSame($questions, $copy);
        foreach ($questions as $i => $original) {
            self::assertNotSame($original, $copy[$i]);
            self::assertSame($original->getAttributes(), $copy[$i]->getAttributes());
            foreach (['answers', 'options', 'tags', 'verbHints', 'markerTags'] as $name) {
                self::assertSame($original->getRelation($name), $copy[$i]->getRelation($name));
            }
            self::assertNotSame($original->hints, $copy[$i]->hints);
            self::assertNotSame($original->hints->first(), $copy[$i]->hints->first());
            self::assertSame(self::TRANSLATIONS[$i], $copy[$i]->hints->first()->hint);
            $attrs = $copy[$i]->hints->first()->getAttributes(); unset($attrs['hint']);
            $old = $original->hints->first()->getAttributes(); unset($old['hint']); self::assertSame($old, $attrs);
            self::assertSame([], $copy[$i]->hints->first()->getDirty());
        }
        self::assertSame($before, $questions->map(static fn ($q) => [$q->getAttributes(), $q->hints->first()->getAttributes()])->all());
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_question_answer_hint_and_relation_mismatches_refuse_the_entire_projection_without_queries(): void
    {
        $audit = $this->audit(); $questions = $this->questions($audit);
        $mutations = [
            'question-id' => fn ($q) => $q[0]->id = 999,
            'question-uuid' => fn ($q) => $q[0]->uuid = 'foreign',
            'question-bank' => fn ($q) => $q[0]->seeder = 'ForeignBank',
            'question-type' => fn ($q) => $q[0]->type = '0',
            'question-level' => fn ($q) => $q[0]->level = 'B2',
            'question-condition' => fn ($q) => $q[0]->question .= ' Змінено.',
            'unloaded-answers' => fn ($q) => $q[0]->unsetRelation('answers'),
            'unloaded-hints' => fn ($q) => $q[0]->unsetRelation('hints'),
            'unloaded-option' => fn ($q) => $q[0]->answers[0]->unsetRelation('option'),
            'answer-id' => fn ($q) => $q[0]->answers[0]->id = 999,
            'answer-question' => fn ($q) => $q[0]->answers[0]->question_id = 999,
            'answer-marker' => fn ($q) => $q[0]->answers[0]->marker = 'a99',
            'answer-option-id' => fn ($q) => $q[0]->answers[0]->option_id = 999,
            'answer-option-value' => fn ($q) => $q[0]->answers[0]->option->option = 'Foreign',
            'hint-id' => fn ($q) => $q[0]->hints[0]->id = 999,
            'hint-question' => fn ($q) => $q[0]->hints[0]->question_id = 999,
            'hint-provider' => fn ($q) => $q[0]->hints[0]->provider = 'foreign',
            'hint-locale' => fn ($q) => $q[0]->hints[0]->locale = 'en',
            'hint-value-dirty' => fn ($q) => $q[0]->hints[0]->hint = 'Changed original',
            'missing-hint' => fn ($q) => $q[0]->setRelation('hints', new ModelCollection),
            'extra-hint' => fn ($q) => $q[0]->hints->push(clone $q[0]->hints[0]),
        ];
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        foreach ($mutations as $name => $mutate) {
            $bad = $this->copiedQuestions($questions); $mutate($bad);
            self::assertNull($this->project($bad, $audit), $name);
            self::assertSame($audit['hint_projection']['records'][1]['before']['hint'], $bad[1]->hints[0]->hint, $name);
        }
        self::assertNull($this->project(new ModelCollection($questions->take(4)->all()), $audit));
        self::assertNull($this->project(new ModelCollection([$questions[1], $questions[0], $questions[2], $questions[3], $questions[4]]), $audit));
        self::assertNull($this->project(new ModelCollection([$questions[0], $questions[0], $questions[2], $questions[3], $questions[4]]), $audit));
        foreach (['empty-translation', 'html-translation', 'non-uk-translation', 'stale-snapshot'] as $case) {
            $bad = $audit;
            if ($case === 'empty-translation') { $bad['hint_projection']['records'][0]['hint_uk'] = ''; }
            if ($case === 'html-translation') { $bad['hint_projection']['records'][0]['hint_uk'] = '<b>Пояснення</b>'; }
            if ($case === 'non-uk-translation') { $bad['hint_projection']['records'][0]['hint_uk'] = 'English only'; }
            if ($case === 'stale-snapshot') { $bad['targets'][0]['first_five'][0]['ordered_answer_snapshot'][0]['option'] = 'Changed'; }
            self::assertNull($this->project($questions, $bad), $case);
        }
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_only_frozen_exact_uk_causative_practice_and_canonical_caller_receive_hint_copies(): void
    {
        $questions = $this->questions($this->audit()); $bindings = $this->binding($questions);
        $after = Presentation::bindings($bindings); self::assertNotSame($questions, $after['practiceQuestions']);
        $otherBindings = $bindings; $otherBindings['theoryCanonical'] = false; self::assertSame($otherBindings, Presentation::bindings($otherBindings));
        unset($otherBindings['theoryCanonical']); self::assertSame($otherBindings, Presentation::bindings($otherBindings));
        foreach (['en', 'pl'] as $locale) {
            app()->setLocale($locale); self::assertSame($bindings, Presentation::bindings($bindings));
        }
        app()->setLocale('uk');
        foreach (['seeder' => 'ForeignOwner', 'page_id' => 95, 'page_category_id' => 999, 'type' => 'usage-panels',
            'locale' => 'en', 'level' => 'B2', 'uuid' => 'foreign', 'sort_order' => 999, 'body' => $bindings['block']->body."\n"] as $field => $value) {
            $bad = $bindings; $bad['block'] = clone $bindings['block']; $bad['block']->$field = $value;
            self::assertSame($bad, Presentation::bindings($bad), $field);
        }
        $bad = $bindings; $data = $bindings['data']; unset($data['m53_native_v1']); $bad['block'] = clone $bindings['block'];
        $bad['block']->body = json_encode($data); self::assertSame($bad, Presentation::bindings($bad));
        $source = file_get_contents(base_path('app/Support/TheoryContentRenderer.php'));
        self::assertSame(1, substr_count($source, 'M53BuilderHintPresentation::bindings($bindings)'));
        self::assertLessThan(strpos($source, 'M53BuilderHintPresentation::bindings($bindings)'), strpos($source, "if ((\$bindings['theoryCanonical'] ?? false) !== true)"));
    }

    public function test_native_widget_changes_only_hint_text_without_question_answer_option_or_original_model_changes(): void
    {
        $audit = $this->audit(); $questions = $this->questions($audit); $bindings = $this->binding($questions);
        $before = view('engram.theory.blocks-v3.practice-set', $bindings)->render();
        $after = TheoryContentRenderer::render('engram.theory.blocks-v3.practice-set', $bindings);
        $oldPayload = TheorySentenceBuilderAcceptance::payload($before, $bindings['block']->uuid);
        $newPayload = TheorySentenceBuilderAcceptance::payload($after, $bindings['block']->uuid);
        self::assertCount(5, $oldPayload); self::assertCount(5, $newPayload);
        foreach ($oldPayload as $i => $old) {
            self::assertSame([['provider' => 'polyglot-v3', 'hint' => self::TRANSLATIONS[$i]]], $newPayload[$i]['hints']);
            $new = $newPayload[$i]; unset($old['hints'], $new['hints']); self::assertSame($old, $new);
            self::assertSame($audit['hint_projection']['records'][$i]['before']['hint'], $questions[$i]->hints[0]->hint);
            self::assertSame([], $questions[$i]->getDirty()); self::assertSame([], $questions[$i]->hints[0]->getDirty());
        }
        self::assertSame(1, substr_count($before, 'data-sentence-builder'));
        self::assertSame(1, substr_count($after, 'data-sentence-builder'));
    }
}
