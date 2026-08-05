<?php

namespace Tests\Unit;

use App\Support\SentenceReorderQuestionFactory;
use Tests\TestCase;

class SentenceReorderQuestionFactoryTest extends TestCase
{
    public function test_it_turns_some_gap_fill_questions_in_a_mixed_theory_test_into_sentence_reorder_questions(): void
    {
        $questions = [
            [
                'id' => 10,
                'uuid' => 'gap-one',
                'type' => '0',
                'level' => 'B2',
                'question' => 'When we arrived, the film {a1}.',
                'answer_map' => ['a1' => 'had started'],
            ],
            [
                'id' => 11,
                'uuid' => 'gap-two',
                'type' => '0',
                'level' => 'B2',
                'question' => 'Maya {a1} the report before Friday.',
                'answer_map' => ['a1' => 'had completed'],
            ],
            [
                'id' => 12,
                'uuid' => 'polyglot',
                'type' => '4',
                'question' => '{a1} {a2}',
                'answer_map' => ['a1' => 'I', 'a2' => 'worked'],
            ],
        ];

        $result = SentenceReorderQuestionFactory::addToMixedTheoryTest($questions, [
            '__meta' => ['theory_page_mixed_polyglot_test' => true],
        ]);

        $reordered = collect($result)->firstWhere('presentation', SentenceReorderQuestionFactory::PRESENTATION);

        $this->assertNotNull($reordered);
        $this->assertContains($reordered['reorder_answer'], [
            'When we arrived, the film had started.',
            'Maya had completed the report before Friday.',
        ]);
        $this->assertNotSame(explode(' ', $reordered['reorder_answer']), $reordered['reorder_tokens']);
        $this->assertTrue(collect($reordered['reorder_tokens'])
            ->every(fn (string $token): bool => count(explode(' ', $token)) >= 1 && count(explode(' ', $token)) <= 3));
        $this->assertTrue(collect($reordered['reorder_tokens'])
            ->contains(fn (string $token): bool => count(explode(' ', $token)) > 1));
        $this->assertSame('4', $result[2]['type']);
        $this->assertArrayNotHasKey('presentation', $result[2]);
    }

    public function test_it_does_not_change_regular_tests(): void
    {
        $questions = [[
            'id' => 10,
            'type' => '0',
            'question' => 'I {a1} here.',
            'answer_map' => ['a1' => 'work'],
        ]];

        $this->assertSame(
            $questions,
            SentenceReorderQuestionFactory::addToMixedTheoryTest($questions, [])
        );
    }

    public function test_it_also_supports_the_normalized_mixed_test_filters_used_by_the_questions_endpoint(): void
    {
        $questions = [[
            'id' => 10,
            'type' => '0',
            'question' => 'I {a1} here every day.',
            'answer_map' => ['a1' => 'work'],
        ]];

        $result = SentenceReorderQuestionFactory::addToMixedTheoryTest($questions, [
            'aggregated_theory_page_test' => true,
            'theory_page_mixed_all_levels' => true,
        ]);

        $this->assertSame(SentenceReorderQuestionFactory::PRESENTATION, $result[0]['presentation']);
    }
}
