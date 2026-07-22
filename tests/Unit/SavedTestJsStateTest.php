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

    public function test_it_refreshes_authored_question_content_without_changing_progress_or_order(): void
    {
        $state = [
            'current' => 1,
            'items' => [
                [
                    'id' => 202,
                    'uuid' => 'question-two',
                    'question' => 'Old question two',
                    'verb_hint' => 'Old hint two',
                    'chosen' => ['answer two'],
                    'done' => true,
                    'attempts' => 2,
                ],
                [
                    'id' => 101,
                    'uuid' => 'question-one',
                    'question' => 'Old question one',
                    'verb_hints' => ['a1' => 'Old hint one'],
                    'manualInputsBySlot' => ['have', 'finished'],
                    'wrongAttempt' => true,
                ],
            ],
            '__meta' => [
                'started' => true,
                'saved_at' => '2026-07-22T12:00:00Z',
                'question_data' => [['uuid' => 'stale']],
            ],
        ];
        $questions = [
            [
                'id' => 101,
                'uuid' => 'question-one',
                'question' => 'Current question one',
                'verb_hint' => 'Current hint one',
                'verb_hints' => ['a1' => 'Current hint one'],
                'options' => ['Have you finished'],
            ],
            [
                'id' => 202,
                'uuid' => 'question-two',
                'question' => 'Current question two',
                'verb_hint' => 'Current hint two',
                'verb_hints' => ['a1' => 'Current hint two'],
                'options' => ['Has she finished'],
            ],
        ];

        $merged = SavedTestJsState::mergeCurrentQuestionData($state, $questions);

        $this->assertSame(['question-two', 'question-one'], array_column($merged['items'], 'uuid'));
        $this->assertSame('Current hint two', $merged['items'][0]['verb_hint']);
        $this->assertSame(['answer two'], $merged['items'][0]['chosen']);
        $this->assertTrue($merged['items'][0]['done']);
        $this->assertSame(2, $merged['items'][0]['attempts']);
        $this->assertSame('Current question one', $merged['items'][1]['question']);
        $this->assertSame(['have', 'finished'], $merged['items'][1]['manualInputsBySlot']);
        $this->assertTrue($merged['items'][1]['wrongAttempt']);
        $this->assertSame('2026-07-22T12:00:00Z', $merged['__meta']['saved_at']);
        $this->assertSame($questions, $merged['__meta']['question_data']);
    }

    public function test_uuid_is_authoritative_and_id_is_only_a_fallback_when_uuid_is_absent(): void
    {
        $questions = [
            ['id' => 10, 'uuid' => 'current-a', 'verb_hint' => 'Hint A'],
            ['id' => 20, 'uuid' => 'current-b', 'verb_hint' => 'Hint B'],
        ];
        $state = [
            'items' => [
                ['id' => 10, 'uuid' => 'missing-uuid', 'verb_hint' => 'Keep me'],
                ['id' => 20, 'verb_hint' => 'Old hint'],
                ['custom' => 'special-mode-item'],
            ],
        ];

        $merged = SavedTestJsState::mergeCurrentQuestionData($state, $questions);

        $this->assertSame('Keep me', $merged['items'][0]['verb_hint']);
        $this->assertSame('Hint B', $merged['items'][1]['verb_hint']);
        $this->assertSame(['custom' => 'special-mode-item'], $merged['items'][2]);
    }
}
