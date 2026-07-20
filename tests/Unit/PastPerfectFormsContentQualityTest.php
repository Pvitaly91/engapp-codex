<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use PHPUnit\Framework\TestCase;

class PastPerfectFormsContentQualityTest extends TestCase
{
    private const DEFINITION =
        'database/seeders/V3/Tenses/PastPerfect/PastPerfectFormsAllLevelsV3Seeder/definition.json';

    private function readDefinition(): array
    {
        return json_decode(
            (string) file_get_contents(dirname(__DIR__, 2).'/'.self::DEFINITION),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /** @return list<string> */
    private function sentenceTokens(string $sentence): array
    {
        $withoutPunctuation = preg_replace('/[.,!?;:]+/u', '', $sentence);

        return array_values(array_filter(
            preg_split('/\s+/u', trim((string) $withoutPunctuation)) ?: [],
            static fn (string $token): bool => $token !== ''
        ));
    }

    public function test_forms_bank_is_complete_contextual_and_unambiguous(): void
    {
        $questions = $this->readDefinition()['questions'];

        $this->assertCount(72, $questions);
        $this->assertCount(72, array_unique(array_column($questions, 'question')));

        $levelCounts = array_count_values(array_column($questions, 'level'));
        foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
            $this->assertSame(12, $levelCounts[$level] ?? 0, "Unexpected {$level} question count");
        }

        $completed = [];
        foreach ($questions as $question) {
            $uuid = (string) $question['uuid'];
            $this->assertSame(['a1'], array_keys($question['markers']), "Unexpected markers in {$uuid}");
            $this->assertSame(1, substr_count($question['question'], '{a1}'), "Wrong marker count in {$uuid}");
            $this->assertMatchesRegularExpression('/[.!?]$/u', $question['question'], "Missing punctuation in {$uuid}");
            $this->assertMatchesRegularExpression('/^[A-Z]/', $question['question'], "Sentence starts in lowercase in {$uuid}");
            $this->assertSame([$question['question']], $question['variants'], "Stale variant in {$uuid}");

            $marker = $question['markers']['a1'];
            $answer = trim((string) $marker['answer']);
            $options = array_map(
                static fn (mixed $option): string => AcceptedAnswerVariants::normalizeTypography((string) $option),
                $marker['options']
            );

            $this->assertStringStartsWith('had ', mb_strtolower($answer), "Wrong target construction in {$uuid}");
            $this->assertCount(5, $options, "Unexpected option count in {$uuid}");
            $this->assertCount(5, array_unique(array_map('mb_strtolower', $options)), "Duplicate option in {$uuid}");
            $this->assertSame($answer, $options[0], "Correct option must be first in {$uuid}");

            foreach (array_slice($options, 1) as $option) {
                $this->assertMatchesRegularExpression(
                    '/^(?:had|has|did)\b/i',
                    $option,
                    "Bare alternative tense creates an ambiguous completion in {$uuid}"
                );
                $this->assertNotContains(
                    mb_strtolower($option),
                    array_map('mb_strtolower', AcceptedAnswerVariants::for($answer)),
                    "Equivalent correct form appears as a distractor in {$uuid}"
                );
            }

            $hint = trim((string) ($marker['verb_hint'] ?? ''));
            $this->assertMatchesRegularExpression('/^Дієслово: «[^»]+»\./u', $hint, "Hint lacks a Ukrainian verb in {$uuid}");
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint, "Hint is not Ukrainian in {$uuid}");
            $this->assertStringNotContainsStringIgnoringCase($answer, $hint, "Hint reveals the full answer in {$uuid}");

            $answerTokens = preg_split('/\s+/u', $answer) ?: [];
            $lexicalToken = (string) end($answerTokens);
            if (mb_strlen($lexicalToken) >= 4) {
                $this->assertStringNotContainsStringIgnoringCase(
                    $lexicalToken,
                    $hint,
                    "Hint reveals the target V3 form in {$uuid}"
                );
            }

            $completed[] = str_replace('{a1}', $answer, $question['question']);
        }

        $this->assertCount(72, array_unique($completed), 'Completed forms sentences repeat.');
        $allContent = implode(' ', $completed);
        $this->assertStringNotContainsString('gone the station', $allContent);
        $this->assertStringNotContainsString('gone the venue', $allContent);
    }

    public function test_forms_bank_has_real_cefr_progression(): void
    {
        $questions = $this->readDefinition()['questions'];
        $averageByLevel = [];

        foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
            $tokenCounts = [];
            foreach ($questions as $question) {
                if ($question['level'] !== $level) {
                    continue;
                }

                $tokenCounts[] = count($this->sentenceTokens(str_replace(
                    '{a1}',
                    $question['markers']['a1']['answer'],
                    $question['question']
                )));
            }

            $averageByLevel[$level] = array_sum($tokenCounts) / count($tokenCounts);
        }

        $this->assertGreaterThan($averageByLevel['A1'] + 0.75, $averageByLevel['C2']);

        $advanced = implode(' ', array_map(
            static fn (array $question): string => str_replace(
                '{a1}',
                $question['markers']['a1']['answer'],
                $question['question']
            ),
            array_values(array_filter(
                $questions,
                static fn (array $question): bool => in_array($question['level'], ['C1', 'C2'], true)
            ))
        ));

        $this->assertStringContainsString('contingency framework', $advanced);
        $this->assertStringContainsString('authenticated the recordings', $advanced);
        $this->assertStringContainsString('institutional resistance', $advanced);
    }
}
