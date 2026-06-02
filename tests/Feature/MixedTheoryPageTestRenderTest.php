<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionOption;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class MixedTheoryPageTestRenderTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $viewsPath = storage_path('framework/views');
        if (! is_dir($viewsPath)) {
            mkdir($viewsPath, 0777, true);
        }
        config(['view.compiled' => $viewsPath]);

        $this->rebuildComposeTestSchema();
    }

    public function test_mixed_theory_virtual_test_exposes_standard_and_compose_questions_in_safe_modes(): void
    {
        $category = Category::create(['name' => 'Mixed theory']);

        $standardSeeder = 'Database\\Seeders\\V3\\AI\\ChatGpt\\MixedTheoryStandardSeeder';
        $polyglotSeeder = 'Database\\Seeders\\V3\\Polyglot\\MixedTheoryPolyglotSeeder';

        $this->createQuestion(
            category: $category,
            uuid: 'standard-question-1',
            question: 'I {a1} at home.',
            answersByMarker: ['a1' => 'am'],
            options: ['am', 'is', 'are'],
            seeder: $standardSeeder,
            type: null
        );

        $this->createQuestion(
            category: $category,
            uuid: 'polyglot-question-1',
            question: 'Я вдома.',
            answersByMarker: ['a1' => 'I', 'a2' => 'am', 'a3' => 'at', 'a4' => 'home'],
            options: ['I', 'am', 'at', 'home', 'is', 'are'],
            seeder: $polyglotSeeder,
            type: Question::TYPE_COMPOSE_TOKENS
        );

        $filters = [
            'levels' => ['A1', 'A2'],
            'num_questions' => 2,
            'randomize_filtered' => false,
            'seeder_classes' => [$standardSeeder, $polyglotSeeder],
            '__meta' => [
                'mode' => 'filters',
                'theory_page_mixed_polyglot_test' => true,
            ],
        ];

        $query = [
            'filters' => base64_encode(json_encode($filters)),
            'name' => 'Mixed theory test',
        ];

        $showResponse = $this->get(route('test.show', 'mixed-theory-test').'?'.http_build_query($query));

        $showResponse->assertOk();
        $showResponse->assertViewHas('questionData', function ($questionData) {
            $items = collect($questionData);

            return $items->count() === 2
                && $items->pluck('uuid')->contains('standard-question-1')
                && $items->pluck('uuid')->contains('polyglot-question-1')
                && $items->pluck('type')->contains(Question::TYPE_COMPOSE_TOKENS);
        });

        $selectResponse = $this->get(route('test.select', 'mixed-theory-test').'?'.http_build_query($query));

        $selectResponse->assertRedirect(localized_route('test.show', 'mixed-theory-test').'?'.http_build_query($query));
    }

    public function test_mixed_all_levels_virtual_test_orders_questions_from_a1_to_c2(): void
    {
        $category = Category::create(['name' => 'Mixed all levels theory']);

        $standardSeeder = 'Database\\Seeders\\V3\\AI\\ChatGpt\\MixedAllLevelsStandardSeeder';
        $polyglotSeeder = 'Database\\Seeders\\V3\\Polyglot\\MixedAllLevelsPolyglotSeeder';

        foreach ([
            ['uuid' => 'standard-b2', 'level' => 'B2', 'seeder' => $standardSeeder, 'type' => null],
            ['uuid' => 'polyglot-a1', 'level' => 'A1', 'seeder' => $polyglotSeeder, 'type' => Question::TYPE_COMPOSE_TOKENS],
            ['uuid' => 'standard-c2', 'level' => 'C2', 'seeder' => $standardSeeder, 'type' => null],
            ['uuid' => 'polyglot-a2', 'level' => 'A2', 'seeder' => $polyglotSeeder, 'type' => Question::TYPE_COMPOSE_TOKENS],
            ['uuid' => 'standard-b1', 'level' => 'B1', 'seeder' => $standardSeeder, 'type' => null],
            ['uuid' => 'polyglot-c1', 'level' => 'C1', 'seeder' => $polyglotSeeder, 'type' => Question::TYPE_COMPOSE_TOKENS],
        ] as $row) {
            $this->createQuestion(
                category: $category,
                uuid: $row['uuid'],
                question: sprintf('%s mixed question.', $row['uuid']),
                answersByMarker: ['a1' => 'answer'],
                options: ['answer', 'other'],
                seeder: $row['seeder'],
                type: $row['type'],
                level: $row['level']
            );
        }

        $filters = [
            'levels' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'],
            'num_questions' => 6,
            'randomize_filtered' => false,
            'seeder_classes' => [$standardSeeder, $polyglotSeeder],
            'theory_page_mixed_all_levels' => true,
            '__meta' => [
                'mode' => 'filters',
                'theory_page_mixed_all_levels_test' => true,
                'theory_page_mixed_polyglot_test' => true,
            ],
        ];

        $query = [
            'filters' => base64_encode(json_encode($filters)),
            'name' => 'Mixed all levels theory test',
        ];

        $response = $this->get(route('test.show', 'mixed-all-levels-theory-test').'?'.http_build_query($query));

        $response->assertOk();
        $response->assertViewHas('questionData', function ($questionData) {
            $items = collect($questionData);

            return $items->pluck('level')->all() === ['A1', 'A2', 'B1', 'B2', 'C1', 'C2']
                && $items->pluck('uuid')->all() === [
                    'polyglot-a1',
                    'polyglot-a2',
                    'standard-b1',
                    'standard-b2',
                    'polyglot-c1',
                    'standard-c2',
                ];
        });
    }

    public function test_mixed_all_levels_virtual_test_limits_random_selection_to_fourteen_questions_per_level(): void
    {
        $category = Category::create(['name' => 'Mixed all levels capped theory']);

        $standardSeeder = 'Database\\Seeders\\V3\\AI\\ChatGpt\\MixedAllLevelsCappedStandardSeeder';
        $polyglotSeeder = 'Database\\Seeders\\V3\\Polyglot\\MixedAllLevelsCappedPolyglotSeeder';

        foreach ([
            'A1' => ['standard' => 10, 'polyglot' => 10],
            'A2' => ['standard' => 3, 'polyglot' => 2],
            'B1' => ['standard' => 7, 'polyglot' => 7],
        ] as $level => $counts) {
            for ($index = 1; $index <= $counts['standard']; $index++) {
                $this->createQuestion(
                    category: $category,
                    uuid: sprintf('standard-%s-%02d', strtolower($level), $index),
                    question: sprintf('standard %s question %d.', $level, $index),
                    answersByMarker: ['a1' => 'answer'],
                    options: ['answer', 'other'],
                    seeder: $standardSeeder,
                    type: null,
                    level: $level
                );
            }

            for ($index = 1; $index <= $counts['polyglot']; $index++) {
                $this->createQuestion(
                    category: $category,
                    uuid: sprintf('polyglot-%s-%02d', strtolower($level), $index),
                    question: sprintf('polyglot %s question %d.', $level, $index),
                    answersByMarker: ['a1' => 'answer'],
                    options: ['answer', 'other'],
                    seeder: $polyglotSeeder,
                    type: Question::TYPE_COMPOSE_TOKENS,
                    level: $level
                );
            }
        }

        $filters = [
            'levels' => ['A1', 'A2', 'B1'],
            'num_questions' => 999,
            'randomize_filtered' => false,
            'seeder_classes' => [$standardSeeder, $polyglotSeeder],
            'theory_page_mixed_all_levels' => true,
            'theory_page_mixed_questions_per_level' => 14,
            '__meta' => [
                'mode' => 'filters',
                'theory_page_mixed_all_levels_test' => true,
                'theory_page_mixed_questions_per_level' => 14,
                'theory_page_mixed_polyglot_test' => true,
            ],
        ];

        $query = [
            'filters' => base64_encode(json_encode($filters)),
            'name' => 'Mixed all levels capped theory test',
        ];

        $response = $this->get(route('test.show', 'mixed-all-levels-capped-theory-test').'?'.http_build_query($query));

        $response->assertOk();
        $response->assertViewHas('questionData', function ($questionData) {
            $items = collect($questionData);
            $a1Items = $items->where('level', 'A1');

            return $items->count() === 33
                && $items->pluck('level')->all() === array_merge(
                    array_fill(0, 14, 'A1'),
                    array_fill(0, 5, 'A2'),
                    array_fill(0, 14, 'B1')
                )
                && $items->pluck('uuid')->unique()->count() === 33
                && $a1Items->filter(fn (array $item) => str_starts_with($item['uuid'], 'standard-a1-'))->isNotEmpty()
                && $a1Items->filter(fn (array $item) => str_starts_with($item['uuid'], 'polyglot-a1-'))->isNotEmpty();
        });
    }

    public function test_theory_category_virtual_test_takes_four_questions_from_each_page_group(): void
    {
        $category = Category::create(['name' => 'Theory category aggregate']);

        $firstSeeder = 'Database\\Seeders\\V3\\AI\\ChatGpt\\CategoryAggregateFirstSeeder';
        $secondSeeder = 'Database\\Seeders\\V3\\AI\\Gemini\\CategoryAggregateSecondSeeder';
        $outsideSeeder = 'Database\\Seeders\\V3\\AI\\Gemini\\CategoryAggregateOutsideSeeder';

        foreach ([
            [$firstSeeder, 'first', 6],
            [$secondSeeder, 'second', 5],
            [$outsideSeeder, 'outside', 6],
        ] as [$seeder, $prefix, $count]) {
            for ($index = 1; $index <= $count; $index++) {
                $this->createQuestion(
                    category: $category,
                    uuid: sprintf('%s-a1-%02d', $prefix, $index),
                    question: sprintf('%s A1 question %d.', $prefix, $index),
                    answersByMarker: ['a1' => 'answer'],
                    options: ['answer', 'other'],
                    seeder: $seeder,
                    type: null,
                    level: 'A1'
                );
            }
        }

        $filters = [
            'levels' => ['A1'],
            'num_questions' => 8,
            'randomize_filtered' => true,
            'seeder_classes' => [$firstSeeder, $secondSeeder],
            'aggregated_theory_page_test' => true,
            'theory_category_page_test' => true,
            'theory_category_questions_per_page' => 4,
            'theory_category_page_groups' => [
                [
                    'page_id' => 1,
                    'page_slug' => 'first-topic',
                    'page_title' => 'First Topic',
                    'seeder_classes' => [$firstSeeder],
                ],
                [
                    'page_id' => 2,
                    'page_slug' => 'second-topic',
                    'page_title' => 'Second Topic',
                    'seeder_classes' => [$secondSeeder],
                ],
            ],
            '__meta' => [
                'mode' => 'filters',
                'aggregated_theory_category_test' => true,
                'theory_category_questions_per_page' => 4,
            ],
        ];

        $query = [
            'filters' => base64_encode(json_encode($filters)),
            'name' => 'Theory category A1 aggregate',
        ];

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('test.show', 'theory-category-a1-aggregate').'?'.http_build_query($query));

        $response->assertOk();
        $response->assertViewHas('questionData', function ($questionData) {
            $items = collect($questionData);
            $uuids = $items->pluck('uuid');
            $firstItems = $items->filter(fn (array $item) => str_starts_with($item['uuid'], 'first-a1-'));
            $secondItems = $items->filter(fn (array $item) => str_starts_with($item['uuid'], 'second-a1-'));

            return $items->count() === 8
                && $items->pluck('level')->unique()->values()->all() === ['A1']
                && $firstItems->count() === 4
                && $secondItems->count() === 4
                && $firstItems->every(fn (array $item) => data_get($item, 'tech_info.theory_page.title') === 'First Topic')
                && $firstItems->every(fn (array $item) => data_get($item, 'tech_info.theory_page.slug') === 'first-topic')
                && $secondItems->every(fn (array $item) => data_get($item, 'tech_info.theory_page.title') === 'Second Topic')
                && $secondItems->every(fn (array $item) => data_get($item, 'tech_info.theory_page.slug') === 'second-topic')
                && $uuids->filter(fn (string $uuid) => str_starts_with($uuid, 'outside-a1-'))->isEmpty();
        });
    }

    protected function createQuestion(
        Category $category,
        string $uuid,
        string $question,
        array $answersByMarker,
        array $options,
        string $seeder,
        ?string $type,
        string $level = 'A1'
    ): Question {
        $record = Question::create([
            'uuid' => $uuid,
            'question' => $question,
            'difficulty' => 1,
            'level' => $level,
            'category_id' => $category->id,
            'flag' => 0,
            'type' => $type,
            'seeder' => $seeder,
        ]);

        foreach ($options as $optionValue) {
            $option = QuestionOption::firstOrCreate(['option' => $optionValue]);
            $record->options()->attach($option->id, ['flag' => 0]);
        }

        foreach ($answersByMarker as $marker => $answerValue) {
            $option = QuestionOption::firstOrCreate(['option' => $answerValue]);

            $record->answers()->create([
                'option_id' => $option->id,
                'marker' => $marker,
            ]);
        }

        return $record->fresh(['options', 'answers']);
    }
}
