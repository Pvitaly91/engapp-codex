<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Services\GrammarTestFilterService;
use App\Services\TagAggregationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class GrammarTestQuestionPreparationTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->rebuildComposeTestSchema();
        config(['coming-soon.enabled' => false]);

        // Fixture-only aggregation; no shared tag configuration is changed.
        $aggregation = new class extends TagAggregationService {
            public function getAggregations(): array
            {
                return [['main_tag' => 'parent', 'similar_tags' => ['child']]];
            }
        };
        $this->app->instance(TagAggregationService::class, $aggregation);

        DB::table('categories')->insert([
            ['id' => 10, 'name' => 'Alpha category'],
            ['id' => 20, 'name' => 'Beta category'],
            ['id' => 30, 'name' => 'Empty category'],
        ]);
        DB::table('sources')->insert([
            ['id' => 10, 'name' => 'Zulu source'],
            ['id' => 20, 'name' => 'Alpha source'],
        ]);
        DB::table('tags')->insert([
            ['id' => 10, 'name' => 'parent'],
            ['id' => 20, 'name' => 'child'],
            ['id' => 30, 'name' => 'unused'],
        ]);
        DB::table('question_options')->insert([
            ['id' => 10, 'option' => 'runs (sprints)'],
            ['id' => 20, 'option' => 'walks'],
            ['id' => 30, 'option' => 'run'],
            ['id' => 40, 'option' => 'away'],
        ]);

        foreach (self::LEVELS as $i => $level) {
            $id = 100 + 10 * $i;
            $this->question($id, $level, '0', 'bank-a', 10, 10, $i + 1, tag: 10);
            $this->question($id + 1, $level, '0', 'bank-a', 20, 20, $i + 1, tag: 20, twoBlanks: true);
            $this->question($id + 2, $level, '0', 'bank-a', 10, 20, $i + 1);
            $this->question($id + 3, $level, '4', 'bank-b', 20, 10, $i + 1);
            $this->question($id + 4, $level, '4', 'bank-b', 10, 20, $i + 1);
            $this->question($id + 5, $level, '4', 'bank-b', 20, 20, $i + 1);
        }
        $this->question(1, 'A1', '0', 'ai', 10, 10, flag: 1);
        $this->question(2, 'A1', '0', 'ai', 20, 20, flag: 2);
        $this->question(3, 'A1', '0', 'other', 10, 20);
        DB::table('question_marker_tag')->insert([
            ['question_id' => 100, 'tag_id' => 10, 'marker' => 'a1'],
            ['question_id' => 100, 'tag_id' => 20, 'marker' => 'a1'],
            ['question_id' => 101, 'tag_id' => 20, 'marker' => 'a2'],
        ]);
    }

    public function test_selection_and_prepare_match_the_pre_change_fixture_contract(): void
    {
        $service = $this->app->make(GrammarTestFilterService::class);
        $expected = $this->before()['selection'];
        $this->assertSame(array_keys($expected), array_keys($this->cases()));

        foreach ($this->cases() as $label => $filters) {
            $questions = $service->questionsFromFilters($filters);
            $prepared = $service->prepare($filters);
            $this->assertSame(Collection::class, $questions::class, $label);
            $this->assertSame($prepared['questions']->toArray(), $questions->toArray(), $label);
            foreach ($questions as $question) {
                $this->assertInstanceOf(Question::class, $question);
                $this->assertSame(['category', 'answers', 'options', 'verbHints', 'source'], array_keys($question->getRelations()));
                $this->assertTrue($question->answers->every(fn ($answer) => $answer->relationLoaded('option')));
                $this->assertTrue($question->verbHints->every(fn ($hint) => $hint->relationLoaded('option')));
            }
            $this->assertSame($expected[$label], [
                $questions->pluck('id')->all(),
                $this->digest($prepared),
            ], $label);
        }
    }

    public function test_questions_only_skips_unused_form_queries_but_preserves_implicit_category_groups(): void
    {
        $service = $this->app->make(GrammarTestFilterService::class);
        $filters = $this->mixedFilters();
        $full = $this->sqlStatements(fn () => $service->prepare($filters));
        $questionOnly = $this->sqlStatements(fn () => $service->questionsFromFilters($filters));

        foreach ([
            '/from "sources" order by "name"/',
            '/from "tags" where exists/',
            '/select distinct "level" from "questions"/',
            '/select distinct "seeder" from "questions"/',
            '/group by "seeder", "source_id"/',
        ] as $metadataSelector) {
            $matches = static fn (string $sql): bool => preg_match($metadataSelector, $sql) === 1;
            $this->assertTrue($full->contains($matches), $metadataSelector.' required by the form');
            $this->assertFalse($questionOnly->contains($matches), $metadataSelector.' not required for selection');
        }

        $unfilteredCategories = static fn (string $sql): bool => preg_match('/^select .+ from "categories"(?: order by .+)?$/', $sql) === 1;
        $this->assertFalse($questionOnly->contains($unfilteredCategories), 'Explicit seeder path does not enumerate categories');
        $sourceGroups = $this->sqlStatements(fn () => $service->questionsFromFilters(['sources' => [10]]));
        $this->assertFalse($sourceGroups->contains($unfilteredCategories), 'Explicit source groups do not enumerate categories');
        $implicitGroups = $this->sqlStatements(fn () => $service->questionsFromFilters(['levels' => ['A1']]));
        $this->assertTrue($implicitGroups->contains($unfilteredCategories), 'Implicit ordinary groups still enumerate categories');
        $this->assertLessThan($full->count(), $questionOnly->count());
    }

    public function test_randomized_paths_keep_pool_quota_and_level_type_contract(): void
    {
        $service = $this->app->make(GrammarTestFilterService::class);
        $plain = $service->questionsFromFilters([
            'seeder_classes' => ['bank-a'], 'levels' => ['A1'], 'num_questions' => 2,
            'randomize_filtered' => true,
        ]);
        $this->assertCount(2, $plain);
        $this->assertCount(2, $plain->pluck('id')->unique());
        $this->assertSame([], array_diff($plain->pluck('id')->all(), [100, 101, 102]));

        $mixed = $service->questionsFromFilters($this->mixedFilters() + ['randomize_filtered' => true]);
        $this->assertSame(self::LEVELS, $mixed->pluck('level')->unique()->values()->all());
        $this->assertCount(24, $mixed->pluck('id')->unique());
        foreach ($mixed->groupBy('level') as $level => $selected) {
            $start = 100 + array_search($level, self::LEVELS, true) * 10;
            $this->assertCount(4, $selected);
            $this->assertSame([], array_diff($selected->pluck('id')->all(), range($start, $start + 5)));
            $types = $selected->pluck('type')->values()->all();
            $this->assertContains($types, [['0', '4', '0', '4'], ['4', '0', '4', '0']]);
        }
    }

    public function test_missing_type_column_preserves_supported_selection_and_form_fallback(): void
    {
        Schema::table('questions', fn (Blueprint $table) => $table->dropColumn('type'));
        $filters = ['seeder_classes' => ['bank-b'], 'question_types' => ['1'], 'levels' => ['A1']];
        $service = $this->app->make(GrammarTestFilterService::class);
        $questions = $service->questionsFromFilters($filters);
        $this->assertSame([103, 104, 105], $questions->pluck('id')->all());
        $this->assertSame([], $service->prepare($filters)['questionTypeOptions']);
    }

    public function test_optional_metadata_tables_have_their_existing_empty_form_fallback(): void
    {
        DB::table('questions')->delete();
        Schema::drop('sources');
        Schema::drop('tags');
        $service = $this->app->make(GrammarTestFilterService::class);
        $filters = ['seeder_classes' => ['missing']];
        $this->assertCount(0, $service->questionsFromFilters($filters));
        $prepared = $service->prepare($filters);
        $this->assertCount(0, $prepared['sources']);
        $this->assertCount(0, $prepared['allTags']);
        $this->assertCount(0, $prepared['seederSourceGroups']);
        $this->assertSame(Question::typeLabels(), $prepared['questionTypeOptions']);
    }

    public function test_a_missing_required_relation_table_is_not_silently_an_empty_success(): void
    {
        Schema::drop('sources');
        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->app->make(GrammarTestFilterService::class)->questionsFromFilters([
            'seeder_classes' => ['bank-a'], 'levels' => ['A1'], 'num_questions' => 1,
        ]);
    }

    public function test_combined_ai_flags_and_category_group_scope_remain_distinct(): void
    {
        $service = $this->app->make(GrammarTestFilterService::class);
        $this->assertSame([1, 2], $service->questionsFromFilters([
            'seeder_classes' => ['ai'], 'only_ai' => true, 'only_ai_v2' => true,
        ])->pluck('id')->all());
        $filters = ['seeder_classes' => ['ai'], 'theory_category_page_test' => true];
        $this->assertCount(0, $service->questionsFromFilters($filters));
        $filters['theory_category_page_groups'] = [['page_id' => 10, 'seeder_classes' => ['ai']]];
        $this->assertSame([2], $service->questionsFromFilters($filters)->pluck('id')->all());
    }

    public function test_saved_fixed_and_filter_based_questions_endpoint_contract(): void
    {
        $fixed = SavedGrammarTest::create([
            'uuid' => 'p5-fixed-oracle', 'name' => 'Fixed fixture', 'slug' => 'p5-fixed-oracle', 'filters' => [],
        ]);
        foreach ([101, 100] as $index => $id) {
            $fixed->questionLinks()->create(['question_uuid' => 'p5-fixture-'.$id, 'position' => $index]);
        }
        $filters = ['seeder_classes' => ['bank-a'], 'levels' => ['A1'], 'num_questions' => 2,
            '__meta' => ['mode' => 'filters']];
        $filtered = SavedGrammarTest::create([
            'uuid' => 'p5-filtered-oracle', 'name' => 'Filter fixture', 'slug' => 'p5-filtered-oracle',
            'filters' => $filters,
        ]);

        $expected = $this->before()['saved_test_questions_json'];
        foreach ([$fixed, $filtered] as $test) {
            $questions = $this->getJson(route('test.js.questions', $test->slug))->assertOk()->json('questions');
            $this->assertSame($expected[$test->slug], [
                'ids' => array_column($questions, 'id'), 'payload_sha256' => $this->digest($questions),
            ]);
        }

        // The equivalent virtual link should produce the same public JSON as
        // the saved filter-based test, not a different selection contract.
        $url = route('test.js.questions', ['slug' => 'p5-virtual-oracle',
            'filters' => base64_encode(json_encode($filters, JSON_THROW_ON_ERROR))]);
        $virtual = $this->getJson($url)->assertOk()->json('questions');
        $this->assertSame($expected[$filtered->slug], [
            'ids' => array_column($virtual, 'id'), 'payload_sha256' => $this->digest($virtual),
        ]);
    }

    public function test_actual_constructor_generate_route_retains_filter_metadata(): void
    {
        $this->withoutVite();
        $response = $this->withSession(['admin_authenticated' => true])->post(route('grammar-test.generate'), [
            'num_questions' => 1, 'levels' => ['A1'], 'seeder_classes' => ['bank-a'],
        ])->assertOk()->assertViewIs('grammar-test');
        $data = $response->viewData();
        $this->assertSame([10, 20, 30], $data['categories']->pluck('id')->all());
        $this->assertSame([20, 10], $data['sources']->pluck('id')->all());
        $this->assertSame([10, 20], $data['allTags']->pluck('id')->all());
        $this->assertSame(self::LEVELS, $data['levels']->all());
        $this->assertSame(['ai', 'bank-a', 'bank-b', 'other'], $data['seederClasses']->all());
        $this->assertSame(['ai', 'bank-a', 'bank-b', 'other'], $data['seederSourceGroups']->pluck('seeder')->all());
        $this->assertSame(Question::typeLabels(), $data['questionTypeOptions']);
        foreach (['Alpha category', 'Zulu source', 'bank-a', 'name="question_types[]"'] as $present) {
            $response->assertSee($present, escape: false);
        }
    }

    public function test_filter_based_retry_rotates_variants_without_changing_answers_or_ids(): void
    {
        $test = SavedGrammarTest::create([
            'uuid' => 'p5-variants', 'name' => 'Variant fixture', 'slug' => 'p5-variants',
            'filters' => ['seeder_classes' => ['bank-a'], 'levels' => ['A1'], 'num_questions' => 1,
                '__meta' => ['mode' => 'filters']],
        ]);
        $variants = ['First variant {a1}.', 'Second variant {a1}.'];
        DB::table('question_variants')->insert(array_map(
            fn (string $text): array => ['question_id' => 100, 'text' => $text], $variants));
        $url = route('test.js.questions', ['slug' => $test->slug, 'mode' => 'saved-test-js']);
        $first = $this->getJson($url)->assertOk()->json('questions.0');
        $second = $this->getJson($url)->assertOk()->json('questions.0');
        $allowed = ['Fixture 100 {a1}.', ...$variants];
        $this->assertContains($first['question'], $allowed);
        $this->assertContains($second['question'], $allowed);
        $this->assertNotSame($first['question'], $second['question']);
        foreach (['id', 'uuid', 'answer_map', 'accepted_answers_by_marker', 'options_by_marker', 'verb_hints', 'level'] as $key) {
            $this->assertSame($first[$key], $second[$key], $key);
        }
        $this->assertSame(100, $first['id']);
        $this->assertSame('runs (sprints)', $first['answer_map']['a1']);
        $this->assertSame(['runs (sprints)'], $first['accepted_answers_by_marker']['a1']);
    }

    private function cases(): array
    {
        $base = ['seeder_classes' => ['bank-a', 'bank-b'], 'levels' => ['A1'], 'num_questions' => 9];
        $pageGroups = [
            ['page_id' => 1, 'page_slug' => 'first', 'seeder_classes' => ['bank-b']],
            ['page_id' => 2, 'page_slug' => 'second', 'seeder_classes' => ['bank-a']],
            ['page_id' => 3, 'page_slug' => 'empty', 'seeder_classes' => ['missing']],
        ];

        return [
            'empty_input_implicit_category_groups' => [],
            'incomplete_input_and_group_quota' => ['num_questions' => '3', 'levels' => ['A1']],
            'explicit_category' => ['categories' => ['20'], 'levels' => ['A1'], 'num_questions' => 3],
            'source_groups' => ['sources' => [20, 10], 'levels' => ['A1'], 'num_questions' => 3],
            'category_and_source' => ['categories' => [10], 'sources' => [20, 10], 'levels' => ['A1'], 'num_questions' => 3],
            'seeder_and_type' => $base + ['question_types' => ['4']],
            'regular_tag' => $base + ['tags' => ['child']],
            'aggregated_tag' => $base + ['aggregated_tags' => ['parent']],
            'blank_limits' => $base + ['blank_count_from' => 2, 'blank_count_to' => 2],
            'difficulty_limits' => array_replace($base, ['levels' => [], 'difficulty_from' => 2, 'difficulty_to' => 2]),
            'include_ai' => ['levels' => ['A1'], 'include_ai' => 'true'],
            'include_ai_v2' => ['levels' => ['A1'], 'include_ai_v2' => true],
            'only_ai' => ['seeder_classes' => ['ai'], 'only_ai' => 'on'],
            'only_ai_v2' => ['seeder_classes' => ['ai'], 'only_ai_v2' => true],
            'category_flag_without_groups' => ['seeder_classes' => ['ai'], 'theory_category_page_test' => true],
            'aggregated_page_allows_flag_two' => ['seeder_classes' => ['ai'], 'aggregated_theory_page_test' => true],
            'empty_pool' => ['seeder_classes' => ['missing'], 'num_questions' => 3],
            'small_pool' => ['seeder_classes' => ['bank-a'], 'levels' => ['A1'], 'num_questions' => 500],
            'category_page_groups' => ['theory_category_page_test' => true, 'theory_category_page_groups' => $pageGroups,
                'theory_category_questions_per_page' => 1, 'levels' => ['A1']],
            'mixed_a1_c2_interleaved' => $this->mixedFilters(),
            'mixed_a1_c2_balanced_type_profile' => array_replace($this->mixedFilters(), ['theory_page_mixed_type_profile' => 'word-order-v1']),
        ];
    }

    private function mixedFilters(): array
    {
        return [
            'seeder_classes' => ['bank-a', 'bank-b'], 'levels' => array_reverse(self::LEVELS),
            'num_questions' => 24, '__meta' => [
                'theory_page_mixed_all_levels_test' => true,
                'theory_page_mixed_questions_per_level' => 4,
                'theory_page_mixed_interleave_question_types' => true,
            ],
        ];
    }

    private function question(int $id, string $level, string $type, string $seeder, int $category, int $source,
        int $difficulty = 1, int $flag = 0, ?int $tag = null, bool $twoBlanks = false): void
    {
        DB::table('questions')->insert([
            'id' => $id, 'uuid' => 'p5-fixture-'.$id, 'level' => $level, 'type' => $type,
            'seeder' => $seeder, 'category_id' => $category, 'source_id' => $source,
            'difficulty' => $difficulty, 'flag' => $flag, 'theory_text_block_uuid' => 'p5-theory',
            'question' => 'Fixture '.$id.' {a1}'.($twoBlanks ? ' {a2}' : '').'.',
            'options_by_marker' => json_encode(['a1' => ['walks', 'runs (sprints)']], JSON_THROW_ON_ERROR),
        ]);
        DB::table('question_answers')->insert(['question_id' => $id, 'marker' => 'a1', 'option_id' => 10]);
        if ($twoBlanks) {
            DB::table('question_answers')->insert(['question_id' => $id, 'marker' => 'a2', 'option_id' => 40]);
        }
        DB::table('verb_hints')->insert(['question_id' => $id, 'marker' => 'a1', 'option_id' => 30]);
        DB::table('question_option_question')->insert(['question_id' => $id, 'option_id' => 20, 'flag' => 0]);
        if ($tag !== null) {
            DB::table('question_tag')->insert(['question_id' => $id, 'tag_id' => $tag]);
        }
    }

    private function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    private function before(): array
    {
        return json_decode(file_get_contents(base_path('tests/Fixtures/perf-mixed-question-preparation-before.v1.json')),
            true, flags: JSON_THROW_ON_ERROR);
    }

    private function sqlStatements(callable $callback): Collection
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        try {
            $callback();

            return collect(DB::getQueryLog())->pluck('query')->map(strtolower(...));
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }
}
