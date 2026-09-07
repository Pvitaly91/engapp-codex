<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * Presents a portion of the regular gap-fill questions in a theory-page mixed
 * test as sentence-order tasks. The underlying question is not changed, so
 * hints, theory links and reporting keep pointing at the original exercise.
 */
class SentenceReorderQuestionFactory
{
    public const PRESENTATION = 'sentence_reorder';

    /**
     * @param  array<int, array<string, mixed>>  $questions
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public static function addToMixedTheoryTest(array $questions, array $filters): array
    {
        $isMixedTheoryTest = (bool) data_get($filters, '__meta.theory_page_mixed_polyglot_test', false)
            || (
                (bool) ($filters['aggregated_theory_page_test'] ?? false)
                && (bool) ($filters['theory_page_mixed_all_levels'] ?? false)
            );

        if (! $isMixedTheoryTest) {
            return $questions;
        }

        $eligible = [];

        foreach ($questions as $index => $question) {
            if (self::canBuild($question)) {
                $eligible[] = $index;
            }
        }

        if ($eligible === []) {
            return $questions;
        }

        $spreadAcrossLevels = (bool) data_get(
            $filters,
            '__meta.theory_page_mixed_interleave_question_types',
            $filters['theory_page_mixed_interleave_question_types'] ?? false
        );

        if ($spreadAcrossLevels) {
            $selectedIndexes = self::spreadEligibleIndexesAcrossLevels($questions, $eligible);
        } else {
            // Preserve the existing selection for mixed tests that do not opt
            // into level-aware presentation balancing.
            usort($eligible, fn (int $left, int $right): int => strcmp(
                self::stableKey($questions[$left], $left),
                self::stableKey($questions[$right], $right)
            ));

            $count = max(1, (int) floor(count($eligible) / 2));
            $selectedIndexes = array_slice($eligible, 0, $count);
        }

        foreach ($selectedIndexes as $index) {
            $reorder = self::build($questions[$index], $index, $spreadAcrossLevels);

            if ($reorder !== null) {
                $questions[$index] = array_merge($questions[$index], $reorder);
            }
        }

        return $questions;
    }

    /**
     * Keep genuine gap-fill questions available at every CEFR level. For a
     * seven-question standard bank, positions 2, 4 and 6 become
     * sentence-order tasks while 1, 3, 5 and 7 remain gaps.
     *
     * @param array<int, array<string, mixed>> $questions
     * @param array<int, int> $eligible
     * @return array<int, int>
     */
    private static function spreadEligibleIndexesAcrossLevels(array $questions, array $eligible): array
    {
        $byLevel = [];

        foreach ($eligible as $index) {
            $level = strtoupper(trim((string) ($questions[$index]['level'] ?? '')));
            $byLevel[$level !== '' ? $level : '__unknown'][] = $index;
        }

        $selected = [];

        foreach ($byLevel as $indexes) {
            usort($indexes, fn (int $left, int $right): int => strcmp(
                self::stableKey($questions[$left], $left),
                self::stableKey($questions[$right], $right)
            ));

            $available = count($indexes);
            $count = max(1, (int) floor($available / 2));

            for ($pick = 0; $pick < $count; $pick++) {
                $position = (int) floor((($pick + 0.5) * $available) / $count);
                $selected[] = $indexes[min($position, $available - 1)];
            }
        }

        return $selected;
    }

    /** @param array<string, mixed> $question */
    private static function canBuild(array $question): bool
    {
        if ((string) ($question['type'] ?? '') === '4') {
            return false;
        }

        return trim((string) ($question['question'] ?? '')) !== ''
            && is_array($question['answer_map'] ?? null)
            && ($question['answer_map'] ?? []) !== [];
    }

    /**
     * @param array<string, mixed> $question
     * @return array<string, mixed>|null
     */
    private static function build(array $question, int $index, bool $useGroupedTokenLimit = false): ?array
    {
        $sentence = (string) $question['question'];
        $answers = Arr::map((array) $question['answer_map'], fn ($answer) => trim((string) $answer));

        $sentence = preg_replace_callback('/\{([^{}]+)\}/', function (array $matches) use ($answers): string {
            return $answers[$matches[1]] ?? $matches[0];
        }, $sentence) ?? $sentence;
        $sentence = html_entity_decode(strip_tags($sentence), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $sentence = preg_replace('/\s+/u', ' ', trim($sentence)) ?? '';

        if ($sentence === '' || str_contains($sentence, '{')) {
            return null;
        }

        $tokens = preg_split('/\s+/u', $sentence, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($tokens) < 3 || (! $useGroupedTokenLimit && count($tokens) > 18)) {
            return null;
        }

        $groups = self::groupTokens(
            $tokens,
            (string) ($question['level'] ?? ''),
            self::stableKey($question, $index)
        );

        // Level-balanced mixed tests display grouped chunks rather than raw
        // words. Apply their UI-size limit after grouping so longer advanced
        // sentences are not silently left as gap-fill questions.
        if ($useGroupedTokenLimit && count($groups) > 18) {
            return null;
        }

        $shuffled = self::shuffleTokens($groups, self::stableKey($question, $index));

        return [
            'presentation' => self::PRESENTATION,
            'reorder_answer' => $sentence,
            'reorder_tokens' => $shuffled,
            'reorder_source_question' => (string) $question['question'],
        ];
    }

    /** @param array<string, mixed> $question */
    private static function stableKey(array $question, int $index): string
    {
        return (string) ($question['uuid'] ?? $question['id'] ?? $index);
    }

    /**
     * Keep meaningful neighbouring words together. Lower levels receive
     * larger chunks, while advanced levels receive shorter chunks.
     *
     * @param array<int, string> $tokens
     * @return array<int, string>
     */
    private static function groupTokens(array $tokens, string $level, string $seed): array
    {
        $groups = [];
        $offset = 0;
        $groupIndex = 0;
        $normalizedLevel = strtoupper(trim($level));

        while ($offset < count($tokens)) {
            $remaining = count($tokens) - $offset;
            $size = self::groupSize($normalizedLevel, $remaining, $seed, $groupIndex);
            $groups[] = implode(' ', array_slice($tokens, $offset, $size));
            $offset += $size;
            $groupIndex++;
        }

        return $groups;
    }

    private static function groupSize(string $level, int $remaining, string $seed, int $groupIndex): int
    {
        if ($remaining <= 3) {
            return $remaining;
        }

        if (in_array($level, ['A1', 'A2'], true)) {
            return min(3, $remaining);
        }

        if (in_array($level, ['B1', 'B2'], true)) {
            return 2 + (hexdec(substr(sha1($seed.'|group|'.$groupIndex), 0, 1)) % 2);
        }

        return 1 + (hexdec(substr(sha1($seed.'|group|'.$groupIndex), 0, 1)) % 2);
    }

    /** @param array<int, string> $tokens
     *  @return array<int, string>
     */
    private static function shuffleTokens(array $tokens, string $seed): array
    {
        $indexed = [];
        foreach ($tokens as $index => $token) {
            $indexed[] = ['token' => $token, 'sort' => sha1($seed.'|'.$index.'|'.$token)];
        }

        usort($indexed, fn (array $left, array $right): int => strcmp($left['sort'], $right['sort']));
        $shuffled = array_column($indexed, 'token');

        if ($shuffled === $tokens && count($shuffled) > 1) {
            $first = array_shift($shuffled);
            $shuffled[] = $first;
        }

        return $shuffled;
    }
}
