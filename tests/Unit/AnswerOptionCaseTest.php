<?php

namespace Tests\Unit;

use App\Support\AnswerOptionCase;
use PHPUnit\Framework\TestCase;

class AnswerOptionCaseTest extends TestCase
{
    public function test_it_restores_authored_case_from_marker_options(): void
    {
        $this->assertSame(
            ['a1' => 'by then', 'a2' => 'You'],
            AnswerOptionCase::align(
                ['a1' => 'By then', 'a2' => 'you'],
                ['a1', 'a2'],
                [['by then', 'later'], ['You', 'They']]
            )
        );
    }

    public function test_it_keeps_answers_without_a_case_insensitive_option_match(): void
    {
        $answers = ['a1' => 'in due course'];

        $this->assertSame(
            $answers,
            AnswerOptionCase::align($answers, ['a1'], [['later', 'eventually']])
        );
        $this->assertSame($answers, AnswerOptionCase::align($answers, ['a1'], null));
    }

    public function test_it_repairs_cached_non_compose_question_payloads(): void
    {
        $payload = AnswerOptionCase::normalizeQuestionPayload([
            'answer' => 'By then',
            'answers' => ['By then'],
            'answer_map' => ['a1' => 'By then'],
            'markers' => ['a1'],
            'options_by_marker' => [['by then', 'later']],
        ]);

        $this->assertSame('by then', $payload['answer']);
        $this->assertSame(['by then'], $payload['answers']);
        $this->assertSame(['a1' => 'by then'], $payload['answer_map']);
        $this->assertSame(['a1' => ['by then']], $payload['accepted_answers_by_marker']);
    }
}
