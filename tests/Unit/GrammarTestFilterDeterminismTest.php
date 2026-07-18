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

    public function test_mixed_level_selection_does_not_put_parallel_question_banks_back_to_back(): void
    {
        $service = (new ReflectionClass(GrammarTestFilterService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(GrammarTestFilterService::class, 'selectMixedAllLevelsQuestions');
        $method->setAccessible(true);

        $candidates = new Collection([
            $this->question(1, 'A1', 'SeederV3', 'present-perfect-forms-v3-a1-01'),
            $this->question(2, 'A1', 'SeederPolyglot', 'present-perfect-forms-poly-a1-01'),
            $this->question(3, 'A1', 'SeederV3', 'present-perfect-forms-v3-a1-02'),
            $this->question(4, 'A1', 'SeederPolyglot', 'present-perfect-forms-poly-a1-02'),
            $this->question(5, 'A1', 'SeederV3', 'present-perfect-forms-v3-a1-03'),
            $this->question(6, 'A1', 'SeederPolyglot', 'present-perfect-forms-poly-a1-03'),
            $this->question(7, 'A1', 'SeederV3', 'present-perfect-forms-v3-a1-04'),
            $this->question(8, 'A1', 'SeederPolyglot', 'present-perfect-forms-poly-a1-04'),
        ]);

        $selected = $method->invoke(
            $service,
            $candidates,
            ['A1'],
            ['SeederV3', 'SeederPolyglot'],
            8,
            false
        );

        $this->assertSame(range(1, 8), $selected->pluck('id')->sort()->values()->all());
        $this->assertSame(
            ['SeederV3', 'SeederV3', 'SeederV3', 'SeederV3', 'SeederPolyglot', 'SeederPolyglot', 'SeederPolyglot', 'SeederPolyglot'],
            $selected->pluck('seeder')->all()
        );

        $semanticKeys = $selected->map(
            fn (Question $question): string => (string) preg_replace('/-(?:v3|poly)-/', '-', (string) $question->uuid)
        )->values();

        for ($index = 1; $index < $semanticKeys->count(); $index++) {
            $this->assertNotSame($semanticKeys[$index - 1], $semanticKeys[$index]);
        }
    }

    private function question(int $id, string $level, string $seeder, ?string $uuid = null): Question
    {
        $question = new Question();
        $question->id = $id;
        $question->level = $level;
        $question->seeder = $seeder;
        $question->uuid = $uuid;

        return $question;
    }
}
