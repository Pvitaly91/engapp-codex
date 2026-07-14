<?php

namespace Tests\Unit;

use App\Support\ComposeTokenCase;
use PHPUnit\Framework\TestCase;

class ComposeTokenCaseTest extends TestCase
{
    public function test_it_restores_sentence_case_without_mutating_shared_options(): void
    {
        $this->assertSame(
            ['Researchers', "won't", 'call', 'you'],
            ComposeTokenCase::normalize(['researchers', "won't", 'call', 'You'])
        );

        $this->assertSame(
            ['My sister', 'will', 'not', 'travel'],
            ComposeTokenCase::normalize(['my sister', 'will', 'not', 'travel'])
        );
    }

    public function test_it_repairs_cached_compose_question_payloads(): void
    {
        $payload = ComposeTokenCase::normalizeQuestionPayload([
            'answers' => ['researchers', "won't", 'call', 'You'],
            'markers' => ['a1', 'a2', 'a3', 'a4'],
        ]);

        $this->assertSame(['Researchers', "won't", 'call', 'you'], $payload['answers']);
        $this->assertSame('Researchers', $payload['answer_map']['a1']);
        $this->assertSame(['you'], $payload['accepted_answers_by_marker']['a4']);
    }

    public function test_it_restores_authored_case_for_an_interior_lexical_token(): void
    {
        $payload = ComposeTokenCase::normalizeQuestionPayload([
            'answers' => ['my', 'sister', 'will', 'update', 'Following', 'day'],
            'markers' => ['a1', 'a2', 'a3', 'a4', 'a5', 'a6'],
            'options_by_marker' => [
                ['my'], ['sister'], ['will'], ['update'], ['following'], ['day'],
            ],
        ]);

        $this->assertSame(
            ['My', 'sister', 'will', 'update', 'following', 'day'],
            $payload['answers']
        );
        $this->assertSame('following', $payload['answer_map']['a5']);
    }

    public function test_it_repairs_present_perfect_questions_from_shared_case_insensitive_options(): void
    {
        $this->assertSame(
            ['Have', 'you', 'seen', 'this', 'film', 'yet'],
            ComposeTokenCase::normalize(['have', 'You', 'seen', 'this', 'film', 'yet'])
        );
        $this->assertSame(
            ['Why', 'has', 'he', 'not', 'come', 'yet'],
            ComposeTokenCase::normalize(['why', 'has', 'He', 'not', 'come', 'yet'])
        );
        $this->assertSame(
            ['How', 'many', 'letters', 'have', 'you', 'written', 'today'],
            ComposeTokenCase::normalize(['how', 'many', 'letters', 'have', 'You', 'written', 'today'])
        );
    }

    public function test_it_preserves_correct_multiplicity_and_removes_case_only_distractors(): void
    {
        $correct = [
            'She', 'cannot', 'open', 'the', 'file', 'because', 'she',
            'has', 'not', 'found', 'the', 'password',
        ];

        $this->assertSame(
            [...$correct, 'have', 'find'],
            ComposeTokenCase::mergeOptions(
                ['she', 'cannot', 'open', 'the', 'file', 'because', 'has', 'not', 'found', 'password', 'have', 'find', 'She'],
                $correct
            )
        );

        $payload = ComposeTokenCase::normalizeQuestionPayload([
            'answers' => $correct,
            'markers' => array_map(fn (int $index): string => 'a'.($index + 1), array_keys($correct)),
            'options' => ['She', 'cannot', 'open', 'the', 'file', 'because', 'has', 'not', 'found', 'password', 'have'],
        ]);

        $this->assertSame(2, array_count_values($payload['options'])['the'] ?? 0);
        $this->assertSame(1, array_count_values($payload['options'])['She'] ?? 0);
        $this->assertSame(1, array_count_values($payload['options'])['she'] ?? 0);
    }
}
