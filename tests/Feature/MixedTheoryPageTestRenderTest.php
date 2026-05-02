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

        $showResponse = $this->get(route('test.show', 'mixed-theory-test') . '?' . http_build_query($query));

        $showResponse->assertOk();
        $showResponse->assertViewHas('questionData', function ($questionData) {
            $items = collect($questionData);

            return $items->count() === 2
                && $items->pluck('uuid')->contains('standard-question-1')
                && $items->pluck('uuid')->contains('polyglot-question-1')
                && $items->pluck('type')->contains(Question::TYPE_COMPOSE_TOKENS);
        });

        $selectResponse = $this->get(route('test.select', 'mixed-theory-test') . '?' . http_build_query($query));

        $selectResponse->assertRedirect(localized_route('test.show', 'mixed-theory-test') . '?' . http_build_query($query));
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

        $response = $this->get(route('test.show', 'mixed-all-levels-theory-test') . '?' . http_build_query($query));

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
