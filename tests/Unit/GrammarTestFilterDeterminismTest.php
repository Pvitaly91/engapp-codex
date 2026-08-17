<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Services\GrammarTestFilterService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class GrammarTestFilterDeterminismTest extends TestCase
{
    public function test_mixed_level_selection_is_stable_when_randomization_is_disabled(): void
    {
        $service = (new ReflectionClass(GrammarTestFilterService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(GrammarTestFilterService::class, 'selectMixedAllLevelsQuestions');
        $method->setAccessible(true);

        $candidates = new Collection([
            $this->question(4, 'A1', 'SeederB'),
            $this->question(3, 'A1', 'SeederA'),
            $this->question(2, 'A1', 'SeederB'),
            $this->question(1, 'A1', 'SeederA'),
        ]);

        $first = $method->invoke($service, $candidates, ['A1'], ['SeederB', 'SeederA'], 4, false);
        $second = $method->invoke($service, $candidates, ['A1'], ['SeederB', 'SeederA'], 4, false);

        $this->assertSame([2, 4, 1, 3], $first->pluck('id')->all());
        $this->assertSame($first->pluck('id')->all(), $second->pluck('id')->all());
    }

    public function test_mixed_level_selection_evenly_interleaves_types_without_adjacent_parallel_questions(): void
    {
        $service = (new ReflectionClass(GrammarTestFilterService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(GrammarTestFilterService::class, 'selectMixedAllLevelsQuestions');
        $method->setAccessible(true);

        $candidates = new Collection([
            $this->question(1, 'A1', 'SeederV3', 'present-perfect-forms-v3-a1-01'),
            $this->question(2, 'A1', 'SeederPolyglot', 'present-perfect-forms-poly-a1-01', Question::TYPE_COMPOSE_TOKENS),
            $this->question(3, 'A1', 'SeederV3', 'present-perfect-forms-v3-a1-02'),
            $this->question(4, 'A1', 'SeederPolyglot', 'present-perfect-forms-poly-a1-02', Question::TYPE_COMPOSE_TOKENS),
            $this->question(5, 'A1', 'SeederV3', 'present-perfect-forms-v3-a1-03'),
            $this->question(6, 'A1', 'SeederPolyglot', 'present-perfect-forms-poly-a1-03', Question::TYPE_COMPOSE_TOKENS),
            $this->question(7, 'A1', 'SeederV3', 'present-perfect-forms-v3-a1-04'),
            $this->question(8, 'A1', 'SeederPolyglot', 'present-perfect-forms-poly-a1-04', Question::TYPE_COMPOSE_TOKENS),
        ]);

        $selected = $method->invoke(
            $service,
            $candidates,
            ['A1'],
            ['SeederV3', 'SeederPolyglot'],
            8,
            false,
            true
        );

        $this->assertSame(range(1, 8), $selected->pluck('id')->sort()->values()->all());
        $this->assertSame(
            ['SeederV3', 'SeederPolyglot', 'SeederV3', 'SeederPolyglot', 'SeederV3', 'SeederPolyglot', 'SeederV3', 'SeederPolyglot'],
            $selected->pluck('seeder')->all()
        );
        $this->assertSame([1, 6, 3, 8, 5, 2, 7, 4], $selected->pluck('id')->all());

        $semanticKeys = $selected->map(
            fn (Question $question): string => (string) preg_replace('/-(?:v3|poly)-/', '-', (string) $question->uuid)
        )->values();

        for ($index = 1; $index < $semanticKeys->count(); $index++) {
            $this->assertNotSame($semanticKeys[$index - 1], $semanticKeys[$index]);
        }
    }

    public function test_future_perfect_mixed_selection_keeps_levels_progressive_and_alternates_types(): void
    {
        $service = (new ReflectionClass(GrammarTestFilterService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(GrammarTestFilterService::class, 'selectMixedAllLevelsQuestions');
        $method->setAccessible(true);

        $candidates = new Collection([
            $this->question(1, 'A1', 'SeederV3', 'a1-v3-01'),
            $this->question(2, 'A1', 'SeederPolyglot', 'a1-poly-01', Question::TYPE_COMPOSE_TOKENS),
            $this->question(3, 'A1', 'SeederV3', 'a1-v3-02'),
            $this->question(4, 'A1', 'SeederPolyglot', 'a1-poly-02', Question::TYPE_COMPOSE_TOKENS),
            $this->question(5, 'A2', 'SeederV3', 'a2-v3-01'),
            $this->question(6, 'A2', 'SeederPolyglot', 'a2-poly-01', Question::TYPE_COMPOSE_TOKENS),
            $this->question(7, 'A2', 'SeederV3', 'a2-v3-02'),
            $this->question(8, 'A2', 'SeederPolyglot', 'a2-poly-02', Question::TYPE_COMPOSE_TOKENS),
            $this->question(9, 'B1', 'SeederV3', 'b1-v3-01'),
            $this->question(10, 'B1', 'SeederPolyglot', 'b1-poly-01', Question::TYPE_COMPOSE_TOKENS),
            $this->question(11, 'B1', 'SeederV3', 'b1-v3-02'),
            $this->question(12, 'B1', 'SeederPolyglot', 'b1-poly-02', Question::TYPE_COMPOSE_TOKENS),
        ]);

        $selected = $method->invoke(
            $service,
            $candidates,
            ['A1', 'A2', 'B1'],
            ['SeederV3', 'SeederPolyglot'],
            4,
            false,
            true
        );

        $this->assertSame(
            ['A1', 'A1', 'A1', 'A1', 'A2', 'A2', 'A2', 'A2', 'B1', 'B1', 'B1', 'B1'],
            $selected->pluck('level')->all()
        );
        $this->assertSame(
            ['0', '4', '0', '4', '0', '4', '0', '4', '0', '4', '0', '4'],
            $selected->map(fn (Question $question): string => (string) ($question->type ?? '0'))->all()
        );
        $this->assertSame(range(1, 12), $selected->pluck('id')->sort()->values()->all());
    }

    private function question(
        int $id,
        string $level,
        string $seeder,
        ?string $uuid = null,
        ?string $type = null
    ): Question
    {
        $question = new Question();
        $question->id = $id;
        $question->level = $level;
        $question->seeder = $seeder;
        $question->uuid = $uuid;
        $question->type = $type;

        return $question;
    }
}
