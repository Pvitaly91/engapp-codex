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

        return array_values(array_unique($variants));
    }

    public static function normalizeTypography(string $answer): string
    {
        $answer = str_replace(["‘", "’", "ʼ", "`"], "'", $answer);

        return trim(preg_replace('/\s+/u', ' ', $answer) ?? $answer);
    }
}
