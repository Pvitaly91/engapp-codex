<?php

namespace Tests\Unit;

use Database\Seeders\Ai\Claude\QuestionsDifferentTypesClaudeSeeder;
use ReflectionClass;
use Tests\TestCase;

class QuestionsDifferentTypesClaudeSeederTest extends TestCase
{
    /**
     * @dataProvider auxiliaryAnswerProvider
     */
    public function test_it_maps_auxiliaries_from_answers(string $answer, array $expectedTags): void
    {
        $seeder = new QuestionsDifferentTypesClaudeSeeder();
        $method = (new ReflectionClass($seeder))->getMethod('getAuxiliaryTagsFromAnswer');
        $method->setAccessible(true);

        $auxiliaryTags = [
            'do_does_did' => 1,
            'have_has_had' => 2,
            'be_auxiliary' => 3,
            'will_would' => 4,
            'can_could' => 5,
            'should' => 6,
            'must' => 7,
            'may_might' => 8,
        ];

        $result = $method->invoke($seeder, $answer, $auxiliaryTags);

        $this->assertEqualsCanonicalizing($expectedTags, $result);
    }

    public static function auxiliaryAnswerProvider(): array
    {
        return [
            'do did detection' => ["Did you do it?", [1]],
            'have plus be' => ["Has she been ready?", [2, 3]],
            'modal mix' => ["Will she? She might, but she shouldn't.", [4, 8, 6]],
            'no auxiliary keywords' => ["Write the answer", []],
        ];
    }
}
