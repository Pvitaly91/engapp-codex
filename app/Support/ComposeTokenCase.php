<?php

namespace App\Support;

final class ComposeTokenCase
{
    /**
     * Shared question_options are case-insensitively unique in production.
     * Restore the casing that follows from a token's position in a sentence
     * without changing the shared database row.
     *
     * @param  array<int, mixed>  $tokens
     * @return array<int, string>
     */
    public static function normalize(array $tokens): array
    {
        return collect(array_values($tokens))
            ->map(function (mixed $token, int $index): string {
                $value = trim((string) $token);

                if ($value === '') {
                    return '';
                }

                if ($index === 0) {
                    return self::uppercaseFirst($value);
                }

                return self::normalizeFollowingToken($value);
            })
            ->all();
    }

    public static function normalizeQuestionPayload(array $question): array
    {
        if (! is_array($question['answers'] ?? null)) {
            return $question;
        }

        $question = AnswerOptionCase::normalizeQuestionPayload($question);
        $answers = self::normalize($question['answers']);
        $markers = is_array($question['markers'] ?? null)
            ? array_values($question['markers'])
            : array_keys(is_array($question['answer_map'] ?? null) ? $question['answer_map'] : []);

        if (count($markers) !== count($answers)) {
            $markers = array_map(fn (int $index): string => 'a'.($index + 1), array_keys($answers));
        }

        $answerMap = array_combine($markers, $answers) ?: [];
        $accepted = collect($answerMap)
            ->map(fn ($answer): array => AcceptedAnswerVariants::for((string) $answer))
            ->all();

        $question['answer'] = $answers[0] ?? '';
        $question['answers'] = $answers;
        $question['answer_map'] = $answerMap;
        $question['markers'] = $markers;
        $question['accepted_answers'] = array_map(
            fn ($marker): array => $accepted[$marker] ?? [],
            $markers
        );
        $question['accepted_answers_by_marker'] = $accepted;

        return $question;
    }

    private static function uppercaseFirst(string $value): string
    {
        return strtoupper(substr($value, 0, 1)).substr($value, 1);
    }

    private static function normalizeFollowingToken(string $value): string
    {
        $lower = strtolower($value);

        if ($lower === 'i') {
            return 'I';
        }

        $sentenceCaseWords = [
            'a', 'an', 'the',
            'you', 'he', 'she', 'it', 'we', 'they',
            'me', 'him', 'her', 'us', 'them',
            'my', 'your', 'his', 'its', 'our', 'their',
            'mine', 'yours', 'hers', 'ours', 'theirs',
            'this', 'that', 'these', 'those',
        ];

        return in_array($lower, $sentenceCaseWords, true) ? $lower : $value;
    }
}
