<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SavedGrammarTest;
use App\Services\GrammarTestFilterService;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\SentenceReorderQuestionFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PresentPerfectContinuousMixedProductionFlowTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    private const STANDARD_SEEDER = 'Database\\Seeders\\V3\\Tenses\\PresentPerfectContinuous\\PpcProductionFlowStandardSeeder';

    private const BUILDER_SEEDER = 'Database\\Seeders\\V3\\Polyglot\\PpcProductionFlowBuilderSeeder';

    private const EXPECTED_PRESENTATION_PATTERN = [
        'gap', 'builder', 'reorder', 'builder', 'gap', 'builder', 'reorder',
        'builder', 'gap', 'builder', 'reorder', 'builder', 'gap', 'builder',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite'
            || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new RuntimeException('This test may only rebuild an in-memory SQLite database.');
        }

        $this->rebuildComposeTestSchema();
    }

    public function test_metadata_opt_in_drives_the_production_filter_and_reorder_flow(): void
    {
        $category = Category::create(['name' => 'PPC production-flow fixture']);
        $answerOption = QuestionOption::create(['option' => 'has been reviewing']);

        foreach (self::LEVELS as $levelIndex => $level) {
            for ($questionIndex = 1; $questionIndex <= 7; $questionIndex++) {
                $standard = Question::create([
                    'uuid' => sprintf('ppc-flow-v3-%s-%02d', strtolower($level), $questionIndex),
                    'question' => sprintf(
                        'The %s team {a1} task number %d this week.',
                        $level,
                        $questionIndex
                    ),
                    'difficulty' => $levelIndex + 1,
                    'level' => $level,
                    'category_id' => $category->id,
                    'flag' => 0,
                    'type' => '0',
                    'seeder' => self::STANDARD_SEEDER,
                ]);
                $standard->answers()->create([
                    'marker' => 'a1',
                    'option_id' => $answerOption->id,
                ]);

                Question::create([
                    'uuid' => sprintf('ppc-flow-poly-%s-%02d', strtolower($level), $questionIndex),
                    'question' => sprintf(
                        'Команда рівня %s перевіряє завдання номер %d цього тижня.',
                        $level,
                        $questionIndex
                    ),
                    'difficulty' => $levelIndex + 1,
                    'level' => $level,
                    'category_id' => $category->id,
                    'flag' => 0,
                    'type' => Question::TYPE_COMPOSE_TOKENS,
                    'seeder' => self::BUILDER_SEEDER,
                ]);
            }
        }

        $filters = [
            'levels' => self::LEVELS,
            'num_questions' => 84,
            'randomize_filtered' => false,
            'seeder_classes' => [self::STANDARD_SEEDER, self::BUILDER_SEEDER],
            '__meta' => [
                'mode' => 'filters',
                'theory_page_mixed_all_levels_test' => true,
                'theory_page_mixed_questions_per_level' => 14,
                'theory_page_mixed_interleave_question_types' => true,
                'theory_page_mixed_polyglot_test' => true,
            ],
        ];

        $result = $this->app->make(GrammarTestFilterService::class)->prepare($filters);
        $selected = $result['questions'];

        $this->assertTrue($result['normalizedFilters']['theory_page_mixed_all_levels']);
        $this->assertTrue($result['normalizedFilters']['theory_page_mixed_interleave_question_types']);
        $this->assertSame(14, $result['normalizedFilters']['theory_page_mixed_questions_per_level']);
        $this->assertCount(84, $selected);
        $this->assertSame(84, $selected->pluck('uuid')->unique()->count());

        foreach (self::LEVELS as $level) {
            $levelQuestions = $selected->where('level', $level)->values();

            $this->assertCount(14, $levelQuestions, $level);
            $this->assertSame(
                array_merge(...array_fill(0, 7, ['0', Question::TYPE_COMPOSE_TOKENS])),
                $levelQuestions
                    ->map(static fn (Question $question): string => (string) $question->type)
                    ->all(),
                $level.' should alternate the two production question types before presentation conversion.'
            );
        }

        $payload = $selected
            ->map(static function (Question $question): array {
                $answerMap = $question->answers
                    ->mapWithKeys(static fn ($answer): array => [
                        (string) $answer->marker => (string) ($answer->option?->option ?? ''),
                    ])
                    ->all();

                return [
                    'id' => $question->id,
                    'uuid' => $question->uuid,
                    'type' => $question->type,
                    'question' => $question->question,
                    'answer_map' => $answerMap,
                    'level' => $question->level,
                ];
            })
            ->values()
            ->all();

        $presented = collect(SentenceReorderQuestionFactory::addToMixedTheoryTest($payload, $filters));

        foreach (self::LEVELS as $level) {
            $actualPattern = $presented
                ->where('level', $level)
                ->values()
                ->map(static function (array $question): string {
                    if ((string) ($question['type'] ?? '') === Question::TYPE_COMPOSE_TOKENS) {
                        return 'builder';
                    }

                    return ($question['presentation'] ?? null) === SentenceReorderQuestionFactory::PRESENTATION
                        ? 'reorder'
                        : 'gap';
                })
                ->all();

            $this->assertSame(self::EXPECTED_PRESENTATION_PATTERN, $actualPattern, $level);
            $this->assertSame(4, count(array_keys($actualPattern, 'gap', true)), $level);
            $this->assertSame(3, count(array_keys($actualPattern, 'reorder', true)), $level);
            $this->assertSame(7, count(array_keys($actualPattern, 'builder', true)), $level);

            for ($index = 1; $index < count($actualPattern); $index++) {
                $this->assertNotSame(
                    $actualPattern[$index - 1],
                    $actualPattern[$index],
                    sprintf('%s positions %d and %d repeat the same presentation.', $level, $index, $index + 1)
                );
            }
        }
    }

    public function test_locale_override_resolution_uses_uk_for_both_uk_and_ua_only(): void
    {
        $localizedStandard = 'Database\\Seeders\\V3\\Tenses\\PresentPerfectContinuous\\UkrainianPpcStandardSeeder';
        $localizedBuilder = 'Database\\Seeders\\V3\\Polyglot\\UkrainianPpcBuilderSeeder';
        $defaults = collect([self::STANDARD_SEEDER, self::BUILDER_SEEDER]);

        $linkedTest = new SavedGrammarTest;
        $linkedTest->setAttribute('filters', [
            'seeder_classes' => $defaults->all(),
            '__meta' => [
                'theory_page_mixed_locale_seeder_overrides' => [
                    'uk' => [
                        self::STANDARD_SEEDER => $localizedStandard,
                        self::BUILDER_SEEDER => $localizedBuilder,
                    ],
                ],
            ],
        ]);

        $service = new class extends TheoryPagePromptLinkedTestsService
        {
            public function overrides(
                Collection $seeders,
                Collection $linkedTests,
                ?string $locale = null
            ): Collection {
                return $this->applyLocaleMixedSeederOverrides($seeders, $linkedTests, $locale);
            }
        };

        $expectedUk = [$localizedStandard, $localizedBuilder];

        $this->assertSame($expectedUk, $service->overrides($defaults, collect([$linkedTest]), 'uk')->all());
        $this->assertSame($expectedUk, $service->overrides($defaults, collect([$linkedTest]), 'ua')->all());
        $this->assertSame($defaults->all(), $service->overrides($defaults, collect([$linkedTest]), 'en')->all());
        $this->assertSame($defaults->all(), $service->overrides($defaults, collect([$linkedTest]), 'pl')->all());

        $previousLocale = app()->getLocale();

        try {
            app()->setLocale('ua');
            $this->assertSame($expectedUk, $service->overrides($defaults, collect([$linkedTest]))->all());

            app()->setLocale('en');
            $this->assertSame($defaults->all(), $service->overrides($defaults, collect([$linkedTest]))->all());
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}
