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

        $this->assertSame([2, 1, 4, 3], $first->pluck('id')->all());
        $this->assertSame($first->pluck('id')->all(), $second->pluck('id')->all());
    }

    private function question(int $id, string $level, string $seeder): Question
    {
        $question = new Question();
        $question->id = $id;
        $question->level = $level;
        $question->seeder = $seeder;

        return $question;
    }
}
