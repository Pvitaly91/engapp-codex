<?php

namespace App\Support;

final class AcceptedAnswerVariants
{
    /**
     * Return spelling variants that are grammatically equivalent for input
     * checking. Stored content remains unchanged.
     *
     * @return array<int, string>
     */
    public static function for(string $answer): array
    {
        $answer = self::normalizeTypography($answer);

        if ($answer === '') {
            return [];
        }

        $variants = [$answer];

        if (preg_match("/\\bwon't\\b/i", $answer) === 1) {
            $variants[] = preg_replace("/\\bwon't\\b/i", 'will not', $answer) ?? $answer;
        }

        if (preg_match('/\\bwill\\s+not\\b/i', $answer) === 1) {
            $variants[] = preg_replace('/\\bwill\\s+not\\b/i', "won't", $answer) ?? $answer;
        }

        if (preg_match("/\\bhaven't\\b/i", $answer) === 1) {
            $variants[] = preg_replace("/\\bhaven't\\b/i", 'have not', $answer) ?? $answer;
        }

        if (preg_match('/\\bhave\\s+not\\b/i', $answer) === 1) {
            $variants[] = preg_replace('/\\bhave\\s+not\\b/i', "haven't", $answer) ?? $answer;
        }

        if (preg_match("/\\bhasn't\\b/i", $answer) === 1) {
            $variants[] = preg_replace("/\\bhasn't\\b/i", 'has not', $answer) ?? $answer;
        }

        if (preg_match('/\\bhas\\s+not\\b/i', $answer) === 1) {
            $variants[] = preg_replace('/\\bhas\\s+not\\b/i', "hasn't", $answer) ?? $answer;
        }

        return array_values(array_unique($variants));
    }

    public static function normalizeTypography(string $answer): string
    {
        $answer = str_replace(["‘", "’", "ʼ", "`"], "'", $answer);

        return trim(preg_replace('/\s+/u', ' ', $answer) ?? $answer);
    }
}
