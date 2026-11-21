<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Page;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\SavedGrammarTest;
use App\Models\Source;
use App\Models\Tag;
use App\Services\TheoryCourseTestPoolService;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Services\VirtualSavedTest;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TheoryCourseTestPoolRelationsTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const FILTER_RELATIONS = ['category', 'answers.option', 'options', 'verbHints.option', 'source'];

    private const EXTRA_RELATIONS = ['hints', 'chatgptExplanations', 'tags'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        app()->setLocale('uk');
    }

    public function test_support_collection_queries_scale_with_missing_relations_not_question_count(): void
    {
        $ids = array_map(fn (int $number) => $this->question($number)->id, range(1, 6));
        $required = [...self::FILTER_RELATIONS, ...self::EXTRA_RELATIONS];

        foreach ([1, 6] as $count) {
            $selected = Question::with(self::FILTER_RELATIONS)->whereIn('id', array_slice($ids, 0, $count))->orderByDesc('id')->get();
            $support = collect($selected->all())->keyBy('uuid');
            foreach ($support as $question) {
                $this->assertFalse($question->relationLoaded('hints'));
                $this->assertFalse($question->relationLoaded('chatgptExplanations'));
                $this->assertFalse($question->relationLoaded('tags'));
            }

            [$result, $queries] = $this->trace(fn () => $this->withRelations($support, $required));

            $this->assertSame($selected->all(), $result->all());
            $this->assertSame(range(0, $count - 1), $result->keys()->all());
            foreach ($result as $question) {
                $this->assertCount(1, $question->hints);
                $this->assertCount(1, $question->chatgptExplanations);
                $this->assertCount(1, $question->tags);
            }
            $this->assertSame([
                'question_hints' => 1, 'chatgpt_explanations' => 1, 'tags' => 1,
            ], array_count_values($this->selectedTables($queries)), 'Only absent relations should be fetched, once each, for '.$count.' questions.');
        }
    }

    public function test_empty_and_fully_loaded_collections_do_not_read_the_database(): void
    {
        $required = [...self::FILTER_RELATIONS, ...self::EXTRA_RELATIONS];
        $question = $this->question(1)->load($required);
        $emptyHints = new EloquentCollection;
        $question->setRelation('hints', $emptyHints);
        $question->setRelation('source', null);
        $sentinel = (object) ['id' => $question->id];

        foreach ([collect(), new EloquentCollection, collect(['sentinel' => $sentinel, 'blank' => null]),
            collect([4 => $question, 8 => 'leave me']), new EloquentCollection([7 => $question])] as $input) {
            [$result, $queries] = $this->trace(fn () => $this->withRelations($input, $required));
            $this->assertSame(array_values($input->all()), $result->all());
            $this->assertSame([], $queries);
        }
        $this->assertSame($emptyHints, $question->hints);
        $this->assertTrue($question->relationLoaded('source'));
        $this->assertNull($question->source);
    }

    public static function collectionKinds(): array
    {
        return ['support' => [Collection::class], 'eloquent' => [EloquentCollection::class]];
    }

    #[DataProvider('collectionKinds')]
    public function test_partial_nested_and_empty_relations_are_respected_in_each_model(string $collectionClass): void
    {
        $first = $this->question(1)->load(['answers', 'verbHints', 'hints']);
        $second = $this->question(2)->load(['answers', 'verbHints']);
        $third = $this->question(3)->load(['answers', 'verbHints']);
        $preparedOption = new QuestionOption(['option' => 'Не перечитувати з БД']);
        $preparedHintOption = new QuestionOption(['option' => 'Підказка лише в пам’яті']);
        $first->answers[0]->setRelation('option', $preparedOption);
        $first->verbHints[0]->setRelation('option', $preparedHintOption);
        $preparedHints = $first->hints;
        $third->answers[0]->setRelation('option', null);
        $emptyVerbHints = new EloquentCollection;
        $emptyHints = new EloquentCollection;
        $third->setRelation('verbHints', $emptyVerbHints);
        $third->setRelation('hints', $emptyHints);
        $this->assertFalse($second->answers[0]->relationLoaded('option'));
        $this->assertFalse($second->verbHints[0]->relationLoaded('option'));
        $this->assertFalse($second->relationLoaded('hints'));
        $answerCollections = [$first->answers, $second->answers, $third->answers];

        $result = $this->withRelations(new $collectionClass([4 => $first, 7 => $second, 9 => $third]), [
            'answers.option', 'verbHints.option', 'hints',
        ]);

        $this->assertSame([$first, $second, $third], $result->all());
        $this->assertSame($answerCollections, $result->map(fn (Question $question) => $question->answers)->all());
        $this->assertSame($preparedOption, $first->answers[0]->option);
        $this->assertSame($preparedHintOption, $first->verbHints[0]->option);
        $this->assertSame($preparedHints, $first->hints);
        $this->assertTrue($second->answers[0]->relationLoaded('option'));
        $this->assertSame('answer-2', $second->answers[0]->option->option);
        $this->assertTrue($second->verbHints[0]->relationLoaded('option'));
        $this->assertSame('hint-option-2', $second->verbHints[0]->option->option);
        $this->assertTrue($second->relationLoaded('hints'));
        $this->assertSame('Підказка 2.', $second->hints[0]->hint);
        $this->assertTrue($third->answers[0]->relationLoaded('option'));
        $this->assertNull($third->answers[0]->option);
        $this->assertSame($emptyVerbHints, $third->verbHints);
        $this->assertSame($emptyHints, $third->hints);
    }

    public function test_support_collection_keeps_question_identity_duplicates_and_synthetic_attributes(): void
    {
        $first = $this->question(1);
        $sameId = Question::findOrFail($first->id);
        $sameId->question = 'Переклад лише в пам’яті.';
        $preparedHints = new EloquentCollection([new QuestionHint(['hint' => 'Зберегти цю підказку.'])]);
        $first->setRelation('hints', $preparedHints);

        $synthetic = new Question(['question' => 'Лише в пам’яті.', 'uuid' => (string) Str::uuid()]);
        $this->addExplanation($synthetic, 'Пояснення синтетичного завдання.');
        $syntheticOptions = new EloquentCollection([new QuestionOption(['option' => 'synthetic option'])]);
        $synthetic->setRelation('options', $syntheticOptions);
        $sentinel = (object) ['id' => $first->id, 'note' => 'Do not hydrate'];
        $items = [7 => $sameId, 10 => $sentinel, 14 => $first, 18 => $synthetic, 31 => $first];
        $attributes = [$first->getAttributes(), $sameId->getAttributes(), $synthetic->getAttributes()];
        $raw = [$first->getRawOriginal(), $sameId->getRawOriginal(), $synthetic->getRawOriginal()];

        $result = $this->withRelations(collect($items), ['hints', 'options', 'chatgptExplanations']);

        $this->assertSame(array_values($items), $result->all());
        $this->assertSame([0, 1, 2, 3, 4], $result->keys()->all());
        $this->assertSame($first->id, $sameId->id);
        $this->assertNotSame($first, $sameId);
        $this->assertSame($preparedHints, $first->hints);
        $this->assertSame('Підказка 1.', $sameId->hints[0]->hint);
        $this->assertCount(0, $sameId->chatgptExplanations, 'Explanations match the current question text, not the primary key.');
        $this->assertSame($syntheticOptions, $synthetic->options);
        $this->assertNull($synthetic->getKey());
        $this->assertFalse($synthetic->exists);
        $this->assertSame('Пояснення синтетичного завдання.', $synthetic->chatgptExplanations[0]->explanation);
        $this->assertSame($attributes, [$first->getAttributes(), $sameId->getAttributes(), $synthetic->getAttributes()]);
        $this->assertSame($raw, [$first->getRawOriginal(), $sameId->getRawOriginal(), $synthetic->getRawOriginal()]);
    }

    public function test_course_pool_keeps_saved_and_virtual_bank_context_and_mixed_payload(): void
    {
        $standard = $this->question(1, '0', 'FixtureSavedBank');
        $compose = $this->question(2, Question::TYPE_COMPOSE_TOKENS, 'FixtureVirtualBank');
        $saved = SavedGrammarTest::create(['slug' => 'fixture-saved-bank', 'name' => 'Збережений банк', 'uuid' => (string) Str::uuid(), 'filters' => []]);
        $saved->questionLinks()->create(['question_uuid' => $standard->uuid, 'position' => 1]);
        $virtual = new VirtualSavedTest('Віртуальний банк', 'fixture-virtual-bank', [
            'seeder_classes' => ['FixtureVirtualBank'], 'num_questions' => 2, 'randomize_filtered' => true,
            '__meta' => ['mode' => 'filters'], 'mode' => 'compose_tokens',
        ], totalQuestionsAvailable: 1);
        $page = new Page(['slug' => 'parts-of-speech']);
        $this->mock(TheoryPagePromptLinkedTestsService::class, function (MockInterface $mock) use ($page, $saved, $virtual): void {
            $mock->shouldReceive('findForPage')->with($page)->once()->andReturn(collect([$saved]));
            $mock->shouldReceive('buildForPage')->with($page)->once()->andReturn(collect([$virtual]));
        });

        $pool = app(TheoryCourseTestPoolService::class)->buildForPage($page, 'fixed-fixture-attempt');

        $this->assertSame(['fixture-saved-bank', 'fixture-virtual-bank'], array_column($pool['tests'], 'slug'));
        $this->assertSame([1, 1], array_column($pool['tests'], 'questions_loaded'));
        $this->assertSame(['standard', 'polyglot'], array_column($pool['tests'], 'source_type'));
        $this->assertTrue($pool['tests'][1]['filters']['randomize_filtered'], 'The original virtual bank metadata is not mutated.');
        $this->assertSame(['tests_total' => 2, 'standard_tests' => 1, 'polyglot_tests' => 1,
            'questions_total' => 2, 'compose_questions' => 1, 'standard_questions' => 1], $pool['counts']);
        $this->assertSame(['minimum_rating_percent' => 80, 'minimum_correct' => 2,
            'minimum_answered' => 2, 'requires_all_questions' => true], $pool['completion']);
        $this->assertTrue($pool['has_tests']);

        $expectedOrder = [$standard->uuid, $compose->uuid];
        if (sha1('fixed-fixture-attempt|'.$standard->uuid.'|0') > sha1('fixed-fixture-attempt|'.$compose->uuid.'|1')) {
            $expectedOrder = array_reverse($expectedOrder);
        }
        $this->assertSame($expectedOrder, array_column($pool['questions'], 'uuid'));
        $byUuid = array_column($pool['questions'], null, 'uuid');
        $this->assertSame(['a1' => 'answer-1'], $byUuid[$standard->uuid]['answer_map']);
        $this->assertSame(['a1' => 'hint-option-1'], $byUuid[$standard->uuid]['verb_hints']);
        $this->assertSame(['slug' => 'fixture-saved-bank', 'name' => 'Збережений банк', 'source_type' => 'standard',
            'is_polyglot' => false, 'is_virtual' => false], $byUuid[$standard->uuid]['source_test']);
        $this->assertSame(['answer-2'], $byUuid[$compose->uuid]['correctTokens']);
        $this->assertSame(['c1'], $byUuid[$compose->uuid]['correctTokenIds']);
        $this->assertSame('Підказка 2.', $byUuid[$compose->uuid]['hintUk']);
        $this->assertSame(['wrong' => 'Пояснення 2.'], $byUuid[$compose->uuid]['explanations']);
        $this->assertSame(['slug' => 'fixture-virtual-bank', 'name' => 'Віртуальний банк', 'source_type' => 'polyglot',
            'is_polyglot' => true, 'is_virtual' => true], $byUuid[$compose->uuid]['source_test']);
    }

    private function question(int $number, string $type = Question::TYPE_COMPOSE_TOKENS, string $seeder = 'FixtureBank'): Question
    {
        $category = Category::firstOrCreate(['name' => 'Категорія курсу']);
        $source = Source::firstOrCreate(['name' => 'Джерело курсу']);
        $question = Question::create([
            'uuid' => (string) Str::uuid(), 'question' => 'Завдання '.$number.' {a1}.', 'level' => 'A1',
            'type' => $type, 'seeder' => $seeder, 'difficulty' => 1, 'category_id' => $category->id, 'source_id' => $source->id,
        ]);
        $answer = QuestionOption::create(['option' => 'answer-'.$number]);
        $hint = QuestionOption::create(['option' => 'hint-option-'.$number]);
        $question->options()->attach($answer);
        $question->answers()->create(['marker' => 'a1', 'option_id' => $answer->id]);
        $question->verbHints()->create(['marker' => 'a1', 'option_id' => $hint->id]);
        $question->hints()->create(['locale' => 'uk', 'provider' => 'fixture', 'hint' => 'Підказка '.$number.'.']);
        $question->tags()->attach(Tag::firstOrCreate(['name' => 'Тема курсу']));
        $this->addExplanation($question, 'Пояснення '.$number.'.');

        return $question;
    }

    private function addExplanation(Question $question, string $text): void
    {
        ChatGPTExplanation::create(['question' => $question->question, 'language' => 'uk',
            'wrong_answer' => 'wrong', 'correct_answer' => 'correct', 'explanation' => $text]);
    }

    private function withRelations(Collection $questions, array $relations): Collection
    {
        return (new ReflectionMethod(TheoryCourseTestPoolService::class, 'withQuestionRelations'))
            ->invoke(app(TheoryCourseTestPoolService::class), $questions, $relations);
    }

    private function trace(callable $callback): array
    {
        $connection = DB::connection();
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        try {
            return [$callback(), array_column($connection->getQueryLog(), 'query')];
        } finally {
            $connection->disableQueryLog();
            $connection->flushQueryLog();
        }
    }

    private function selectedTables(array $queries): array
    {
        return array_map(static function (string $query): string {
            preg_match('/^select .+? from "([a-z_]+)"/i', $query, $matches);

            return $matches[1] ?? $query;
        }, $queries);
    }
}
