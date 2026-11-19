<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use App\Support\WordOrderPracticeQuality as Quality;
use PHPUnit\Framework\TestCase;

final class InversionGapOptionWidthTest extends TestCase
{
    private const TOPIC = 'negative-adverbial-inversion';

    public function test_every_inversion_gap_choice_has_the_same_word_count_as_its_answer(): void
    {
        $definition = Quality::compile(self::TOPIC, 'standard');
        $path = Quality::mapping(self::TOPIC)['standard']['definition'];
        $stored = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$path), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($definition, $stored);
        self::assertCount(36, $definition['questions']);

        $wordCount = static fn (string $phrase): int => count(preg_split('/\s+/u', trim($phrase), flags: PREG_SPLIT_NO_EMPTY));
        foreach ($definition['questions'] as $row) {
            $marker = $row['markers']['a1'];
            $answer = $marker['answer'];
            $choices = $marker['options'];
            $accepted = array_map(Quality::normalized(...), array_merge(...array_map(
                AcceptedAnswerVariants::for(...), [$answer, ...$row['answer_synonyms']['a1']]
            )));

            self::assertCount(3, $choices, $row['author_id']);
            self::assertCount(3, array_unique(array_map(Quality::normalized(...), $choices)), $row['author_id']);
            self::assertSame($answer, $choices[0], $row['author_id']);
            foreach ($choices as $index => $option) {
                $message = $row['author_id'].' / '.$option;
                self::assertSame($wordCount($answer), $wordCount($option), $message);
                if ($index !== 0) { self::assertNotContains(Quality::normalized($option), $accepted, $message); }
            }
        }
    }

    public function test_cannot_find_still_accepts_contracted_and_expanded_manual_forms(): void
    {
        $row = Quality::questions()[Quality::questionUuid(self::TOPIC, 'A1-g06')];
        $payload = Quality::decorate([
            'uuid' => $row['uuid'], 'type' => '0', 'question' => $row['question'],
            'answer_map' => ['a1' => 'cannot find'], 'answers' => ['cannot find'], 'markers' => ['a1'],
        ]);

        self::assertContains("can't find", $payload['accepted_answers_by_marker']['a1']);
        self::assertContains('can not find', $payload['accepted_answers_by_marker']['a1']);
        self::assertSame(['cannot find', 'find cannot', 'cannot finds'], $row['markers']['a1']['options']);
    }
}
