<?php

namespace Tests\Unit;

use Database\Seeders\Ai\Claude\QuestionsDifferentTypesClaudeSeeder;
use ReflectionClass;
use ReflectionMethod;
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

    public function test_it_aligns_gap_tags_with_theory_tag_set(): void
    {
        $seeder = new QuestionsDifferentTypesClaudeSeeder();
        $method = new ReflectionMethod($seeder, 'prepareGapTagsForMarker');
        $method->setAccessible(true);

        $gapTags = ['Tag Questions', 'Question Tags'];
        $availableTagNames = ['Tag Questions', 'Question Tags', 'Do/Does/Did', 'Present Simple'];

        $result = $method->invoke($seeder, $gapTags, 'does she', $availableTagNames);

        $this->assertContains('Do/Does/Did', $result);
    }

    public function test_it_respects_theory_tags_when_auxiliary_missing(): void
    {
        $seeder = new QuestionsDifferentTypesClaudeSeeder();
        $method = new ReflectionMethod($seeder, 'prepareGapTagsForMarker');
        $method->setAccessible(true);

        $gapTags = ['Tag Questions', 'Question Tags'];
        $availableTagNames = ['Tag Questions', 'Question Tags'];

        $result = $method->invoke($seeder, $gapTags, "doesn't he", $availableTagNames);

        $this->assertNotContains('Do/Does/Did', $result);
    }
}
