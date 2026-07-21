<?php

namespace Tests\Unit;

use App\Support\SavedTestJsState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SavedTestJsStateTest extends TestCase
{
    #[DataProvider('startedStateProvider')]
    public function test_it_recognizes_progress_from_every_supported_state_shape(array $state): void
    {
        $this->assertTrue(SavedTestJsState::isStarted($state));
    }

    public static function startedStateProvider(): array
    {
        return [
            'selected answer' => [['items' => [['chosen' => ['answer']]]]],
            'partial manual phrase' => [['items' => [['manualInputsBySlot' => ['have']]]]],
            'advanced manual word' => [['items' => [['manualWordIndexBySlot' => [1]]]]],
            'completed question' => [['items' => [['done' => true]]]],
            'evaluated answer' => [['items' => [['isCorrect' => false]]]],
            'wrong attempt' => [['items' => [['wrongAttempt' => true]]]],
            'answered counter' => [['answered' => 1, 'items' => []]],
            'step navigation' => [['current' => 1, 'items' => []]],
            'card navigation' => [['activeCardIdx' => 1, 'items' => []]],
            'completed dialogue' => [['completed' => true, 'items' => []]],
            'evaluated match' => [['evaluated' => true, 'connections' => []]],
            'match connection' => [['connections' => [['leftKey' => 'a', 'rightKey' => 'b']]]],
            'drag placement' => [['placements' => [['questionIndex' => 0, 'blankIndex' => 0]]]],
        ];
    }

    public function test_it_does_not_treat_a_pristine_or_automatic_dialogue_item_as_user_progress(): void
    {
        $this->assertFalse(SavedTestJsState::isStarted([
            'answered' => 0,
            'current' => 0,
            'items' => [[
                'chosen' => [null],
                'inputs' => [''],
                'manualInputsBySlot' => [''],
                'manualWordIndexBySlot' => [0],
                'done' => true,
                'status' => 'auto',
                'isCorrect' => null,
                'wrongAttempt' => false,
                'attempts' => 0,
            ]],
        ]));
    }

    public function test_explicit_started_metadata_remains_authoritative(): void
    {
        $this->assertFalse(SavedTestJsState::isStarted([
            'items' => [['manualInputsBySlot' => ['have']]],
            '__meta' => ['started' => false],
        ]));
    }
}
