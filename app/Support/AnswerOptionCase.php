<?php

namespace App\Support;

final class AnswerOptionCase
{
    /**
     * Restore the authored casing from options_by_marker when a shared,
     * case-insensitive question_options row has supplied different casing.
     *
     * @param  array<string, mixed>  $answerMap
     * @param  array<int, string>  $markers
     * @param  array<int, array<int, mixed>>|null  $optionsByMarker
     * @return array<string, mixed>
     */
    public static function align(array $answerMap, array $markers, ?array $optionsByMarker): array
    {
        if (! is_array($optionsByMarker)) {
            return $answerMap;
        }

        foreach ($markers as $index => $marker) {
            $answer = trim((string) ($answerMap[$marker] ?? ''));

            if ($answer === '') {
                continue;
            }

            foreach ($optionsByMarker[$index] ?? [] as $candidate) {
                $candidate = trim((string) $candidate);

                if ($candidate !== '' && mb_strtolower($candidate) === mb_strtolower($answer)) {
                    $answerMap[$marker] = $candidate;
                    break;
                }
            }
        }

        return $answerMap;
    }

    public static function normalizeQuestionPayload(array $question): array
    {
        $answers = is_array($question['answers'] ?? null)
            ? array_values($question['answers'])
            : [];
        $markers = is_array($question['markers'] ?? null)
            ? array_values($question['markers'])
            : array_keys(is_array($question['answer_map'] ?? null) ? $question['answer_map'] : []);

        if ($answers === [] || count($markers) !== count($answers)) {
            return FuturePerfectAnswerSynonyms::decorate($question);
        }

        $rawOptions = $question['options_by_marker'] ?? $question['optionsBySlot'] ?? null;
        if (! is_array($rawOptions)) {
            return FuturePerfectAnswerSynonyms::decorate($question);
        }

        $optionsByMarker = array_map(
            fn (string $marker, int $index): array => is_array($rawOptions[$marker] ?? $rawOptions[$index] ?? null)
                ? array_values($rawOptions[$marker] ?? $rawOptions[$index])
                : [],
            $markers,
            array_keys($markers)
        );
        $answerMap = array_combine($markers, $answers) ?: [];
        $answerMap = self::align($answerMap, $markers, $optionsByMarker);
        $answers = array_map(fn (string $marker): string => (string) ($answerMap[$marker] ?? ''), $markers);
        $accepted = collect($answerMap)
            ->map(fn ($answer): array => AcceptedAnswerVariants::for((string) $answer))
            ->all();

        $question['answer'] = $answers[0] ?? '';
        $question['answers'] = $answers;
        $question['answer_map'] = $answerMap;
        $question['accepted_answers'] = array_map(
            fn (string $marker): array => $accepted[$marker] ?? [],
            $markers
        );
        $question['accepted_answers_by_marker'] = $accepted;

        return FuturePerfectAnswerSynonyms::decorate($question);
    }
}
