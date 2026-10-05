<?php

namespace Tests\Unit;

use App\Support\SavedTestJsState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SavedTestJsStateTest extends TestCase
{
    public function test_opt_in_compose_fields_refresh_without_erasing_attempts_or_navigation(): void
    {
        $item = ['uuid' => 'ppc-editorial', 'question' => 'Old text', 'compose_source_text' => 'Old source',
            'chosen' => ['had'], 'manualInputsBySlot' => ['had'], 'attempts' => 3, 'wrongAttempt' => true];
        $state = ['items' => [$item], 'current' => 2, 'answered' => 7, '__meta' => ['started' => true]];
        $current = ['uuid' => 'ppc-editorial', 'question' => 'Current text', 'compose_source_text' => 'Current localized source',
            'compose_hint' => 'Current localized hint', 'hint' => 'Current lexical hint', 'compose_content_revision' => 'ppc-quality-v2', 'reorder_template_constraint' => true,
            'reorder_tokens' => ['Had', 'she', 'been', 'working?'], 'reorder_answer' => 'Had she been working?', 'reorder_source_question' => '{a1} she been working?'];
        $merged = SavedTestJsState::mergeCurrentQuestionData($state, [$current]);
        foreach (['compose_source_text', 'compose_hint', 'hint', 'compose_content_revision', 'reorder_template_constraint', 'reorder_tokens', 'reorder_answer', 'reorder_source_question'] as $key) {
            $this->assertSame($current[$key], $merged['items'][0][$key]);
        }
        foreach (['chosen', 'manualInputsBySlot', 'attempts', 'wrongAttempt'] as $key) {
            $this->assertSame($item[$key], $merged['items'][0][$key]);
        }
        $this->assertSame(2, $merged['current']);
        $this->assertSame(7, $merged['answered']);
        $this->assertTrue($merged['__meta']['started']);
    }

    public function test_new_opt_in_fields_do_not_change_a_legacy_snapshot(): void
    {
        $item = ['uuid' => 'legacy', 'compose_hint' => 'Keep legacy', 'hint' => 'Keep legacy lexical hint', 'reorder_answer' => 'Keep legacy target', 'reorder_source_question' => 'Keep legacy template'];
        $current = ['uuid' => 'legacy', 'question' => 'Current legacy question', 'compose_hint' => 'Not opted in', 'hint' => 'Not opted in lexical hint', 'reorder_answer' => 'Not opted in', 'reorder_source_question' => 'Ignore new template'];
        $merged = SavedTestJsState::mergeCurrentQuestionData(['items' => [$item]], [$current]);
        $this->assertSame('Keep legacy', $merged['items'][0]['compose_hint']);
        $this->assertSame('Keep legacy lexical hint', $merged['items'][0]['hint']);
        $this->assertSame('Keep legacy target', $merged['items'][0]['reorder_answer']);
        $this->assertSame('Keep legacy template', $merged['items'][0]['reorder_source_question']);
        $this->assertSame('Current legacy question', $merged['items'][0]['question']);
    }

    public function test_grouped_contraction_progress_is_not_overwritten_with_split_source_markers(): void
    {
        $item = ['uuid' => 'example', 'answers' => ['I will call', 'tomorrow'], 'chosen' => ["I'll call", null], 'contraction_slots_version' => 1];
        $questions = [['uuid' => 'example', 'answers' => ['I', 'will', 'call', 'tomorrow']]];
        $merged = SavedTestJsState::mergeCurrentQuestionData(['items' => [$item]], $questions);
        $this->assertSame($item, $merged['items'][0]);
        $this->assertSame($questions, $merged['__meta']['question_data']);
    }

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
