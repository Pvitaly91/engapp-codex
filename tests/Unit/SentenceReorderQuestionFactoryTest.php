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

    public function test_grouped_token_limit_is_scoped_to_level_balanced_mixed_tests(): void
    {
        $questions = [[
            'id' => 10,
            'uuid' => 'legacy-long-question',
            'type' => '0',
            'level' => 'C2',
            'question' => 'One two three four five six seven eight nine ten eleven twelve thirteen fourteen fifteen sixteen seventeen eighteen {a1}.',
            'answer_map' => ['a1' => 'nineteen'],
        ]];

        $result = SentenceReorderQuestionFactory::addToMixedTheoryTest($questions, [
            '__meta' => ['theory_page_mixed_polyglot_test' => true],
        ]);

        $this->assertArrayNotHasKey('presentation', $result[0]);
    }

    public function test_future_perfect_keeps_gap_fill_and_reorder_questions_at_every_level(): void
    {
        $questions = [];
        $id = 1;

        foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
            for ($number = 1; $number <= 7; $number++) {
                $question = "By Friday we {a1} task {$number}.";
                $answer = 'will have finished';

                if ($level === 'C2' && $number === 6) {
                    $question = 'By the time the deep-sea expedition departs, {a1} every seal in the sampling system.';
                    $answer = 'the engineers will already have pressure-tested';
                }

                $questions[] = [
                    'id' => $id++,
                    'uuid' => strtolower($level).'-v3-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'type' => '0',
                    'level' => $level,
                    'question' => $question,
                    'answer_map' => ['a1' => $answer],
                ];
                $questions[] = [
                    'id' => $id++,
                    'uuid' => strtolower($level).'-poly-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'type' => '4',
                    'level' => $level,
                    'question' => "До п’ятниці ми завершимо завдання {$number}.",
                    'answer_map' => ['a1' => 'By Friday'],
                ];
            }
        }

        $result = collect(SentenceReorderQuestionFactory::addToMixedTheoryTest($questions, [
            '__meta' => [
                'theory_page_mixed_polyglot_test' => true,
                'theory_page_mixed_interleave_question_types' => true,
            ],
        ]));

        foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
            $levelQuestions = $result->where('level', $level)->values();
            $standard = $levelQuestions
                ->where('type', '0')
                ->values();

            $this->assertCount(7, $standard);
            $this->assertCount(3, $standard->where('presentation', SentenceReorderQuestionFactory::PRESENTATION));
            $this->assertCount(4, $standard->reject(
                fn (array $question): bool => isset($question['presentation'])
            ));
            $this->assertSame(
                [null, SentenceReorderQuestionFactory::PRESENTATION, null, SentenceReorderQuestionFactory::PRESENTATION, null, SentenceReorderQuestionFactory::PRESENTATION, null],
                $standard->pluck('presentation')->all()
            );
            $this->assertSame(
                ['gap', 'builder', 'reorder', 'builder', 'gap', 'builder', 'reorder', 'builder', 'gap', 'builder', 'reorder', 'builder', 'gap', 'builder'],
                $levelQuestions->map(static function (array $question): string {
                    if ((string) ($question['type'] ?? '') === '4') {
                        return 'builder';
                    }

                    return ($question['presentation'] ?? null) === SentenceReorderQuestionFactory::PRESENTATION
                        ? 'reorder'
                        : 'gap';
                })->all()
            );
        }

        $longC2Question = $result->firstWhere('uuid', 'c2-v3-06');
        $this->assertSame(SentenceReorderQuestionFactory::PRESENTATION, $longC2Question['presentation'] ?? null);
        $this->assertLessThanOrEqual(18, count($longC2Question['reorder_tokens'] ?? []));
    }
}
