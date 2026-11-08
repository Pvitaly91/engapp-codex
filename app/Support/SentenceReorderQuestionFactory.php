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
    public const PRESENT_SIMPLE_PASSIVE_PROFILE = 'present-simple-passive-v1';
    public const PAST_SIMPLE_PASSIVE_PROFILE = 'past-simple-passive-v1';
    public const FUTURE_SIMPLE_PASSIVE_PROFILE = 'future-simple-passive-v1';
    public const WORD_ORDER_PROFILE = 'word-order-v1';
    public const VERB_TO_BE_PROFILE = 'verb-to-be-v1';
    public const BASIC_GRAMMAR_PROFILE = 'basic-grammar-v1';
    public const NOUNS_ARTICLES_QUANTITY_PROFILE = 'nouns-articles-quantity-v1';
    public const PRONOUNS_DEMONSTRATIVES_PROFILE = 'pronouns-demonstratives-v1';
    public const FUTURE_CONTINUOUS_COMPARISON_PROFILE = 'fccq-v1';
    public const QUESTIONS_NEGATIONS_PROFILE = 'qnq-v1';
    public const ADJECTIVES_ADVERBS_PROFILE = 'aaq-v1';
    public const SOME_ANY_PROFILE = 'saq-v1';
    public const PASSIVE_VOICE_PROFILE = 'pvq-v1';
    public const MODAL_VERBS_PROFILE = 'mvq-v1';
    public const CONDITIONALS_PROFILE = 'cqq-v1';
    public const REPORTED_SPEECH_PROFILE = 'rsq-v1';
    public const CLAUSES_LINKING_PROFILE = 'clq-v1';
    public const PREPOSITIONS_PHRASAL_PROFILE = 'ppq-v1';
    public const COMMON_MISTAKES_PROFILE = 'cmq-v1';
    public const SENTENCE_TRANSFORMATIONS_PROFILE = 'stq-v1';
    public const VERB_PATTERNS_PROFILE = 'vpq-v1';
    public const MIXED_REVISION_PROFILE = 'mrq-v1';
    public const ACADEMIC_ENGLISH_PROFILE = 'aeq-v1';
    public const ARTICLES_QUANTIFIERS_PROFILE = 'aqv-v1';
    public const FORMAL_ENGLISH_PROFILE = 'feq-v1';
    public const RELATIVE_CLAUSES_PROFILE = 'rcq-v1';
    public const SENTENCE_STRUCTURE_PROFILE = 'ssq-v1';
    public const VOCAB_COLLOCATIONS_PROFILE = 'vcq-v1';
    public const WORD_ORDER_QUESTIONS_PER_LEVEL = 9;

    public static function isPassiveQualityProfile(mixed $profile): bool
    {
        return in_array($profile, [self::PRESENT_SIMPLE_PASSIVE_PROFILE, self::PAST_SIMPLE_PASSIVE_PROFILE,
            self::FUTURE_SIMPLE_PASSIVE_PROFILE], true);
    }

    /** Explicit opt-in only; the historical passive-profile API stays unchanged. */
    public static function isBalancedQualityProfile(mixed $profile): bool
    {
        return self::isPassiveQualityProfile($profile) || self::isCompactQualityProfile($profile);
    }

    public static function isCompactQualityProfile(mixed $profile): bool
    {
        return in_array($profile, [self::WORD_ORDER_PROFILE, self::VERB_TO_BE_PROFILE, self::BASIC_GRAMMAR_PROFILE,
            self::NOUNS_ARTICLES_QUANTITY_PROFILE, self::PRONOUNS_DEMONSTRATIVES_PROFILE,
            self::FUTURE_CONTINUOUS_COMPARISON_PROFILE, self::QUESTIONS_NEGATIONS_PROFILE,
            self::ADJECTIVES_ADVERBS_PROFILE, self::SOME_ANY_PROFILE, self::PASSIVE_VOICE_PROFILE,
            self::MODAL_VERBS_PROFILE, self::CONDITIONALS_PROFILE, self::REPORTED_SPEECH_PROFILE, self::CLAUSES_LINKING_PROFILE,
            self::PREPOSITIONS_PHRASAL_PROFILE, self::COMMON_MISTAKES_PROFILE, self::SENTENCE_TRANSFORMATIONS_PROFILE, self::VERB_PATTERNS_PROFILE, self::MIXED_REVISION_PROFILE, self::ACADEMIC_ENGLISH_PROFILE, self::ARTICLES_QUANTIFIERS_PROFILE, self::FORMAL_ENGLISH_PROFILE, self::RELATIVE_CLAUSES_PROFILE, self::SENTENCE_STRUCTURE_PROFILE, self::VOCAB_COLLOCATIONS_PROFILE], true);
    }

    /** Explicit compact-bank contract; historical profiles keep their nine-question quota. */
    public static function compactQuestionsPerLevel(mixed $profile): int
    {
        return in_array($profile, [self::CLAUSES_LINKING_PROFILE, self::PREPOSITIONS_PHRASAL_PROFILE, self::COMMON_MISTAKES_PROFILE, self::SENTENCE_TRANSFORMATIONS_PROFILE, self::VERB_PATTERNS_PROFILE, self::MIXED_REVISION_PROFILE, self::ACADEMIC_ENGLISH_PROFILE, self::ARTICLES_QUANTIFIERS_PROFILE, self::FORMAL_ENGLISH_PROFILE, self::RELATIVE_CLAUSES_PROFILE, self::SENTENCE_STRUCTURE_PROFILE, self::VOCAB_COLLOCATIONS_PROFILE], true)
            ? 3 : self::WORD_ORDER_QUESTIONS_PER_LEVEL;
    }

    /** Two standard rows yield one gap and one reorder; one independently authored compose completes the cycle. */
    public static function compactTypeCounts(mixed $profile): array
    {
        $unit = intdiv(self::compactQuestionsPerLevel($profile), 3);
        return ['0' => 2 * $unit, \App\Models\Question::TYPE_COMPOSE_TOKENS => $unit];
    }

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
            )
            || (
                (bool) ($filters['theory_category_page_test'] ?? false)
                && self::isCompactQualityProfile($filters['theory_page_mixed_type_profile'] ?? '')
            );

        if (! $isMixedTheoryTest) {
            return $questions;
        }

        $balancedProfile = self::isBalancedQualityProfile(data_get($filters, 'theory_page_mixed_type_profile',
            data_get($filters, '__meta.theory_page_mixed_type_profile')));

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

        if ($spreadAcrossLevels || $balancedProfile) {
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
            $reorder = self::build($questions[$index], $index, $spreadAcrossLevels || $balancedProfile, $balancedProfile);

            if ($reorder !== null) {
                $questions[$index] = array_merge($questions[$index], $reorder);
            }
        }

        return $balancedProfile ? self::interleavePassiveProfile($questions) : $questions;
    }

    /** The finite opt-in changes presentation only; original question IDs survive. */
    private static function interleavePassiveProfile(array $questions): array
    {
        $levels = [];
        foreach ($questions as $question) {
            $kind = ($question['presentation'] ?? '') === self::PRESENTATION ? 'order'
                : ((string) ($question['type'] ?? '0') === '4' ? 'compose' : 'gap');
            $levels[(string) ($question['level'] ?? '')][$kind][] = $question;
        }
        $result = [];
        foreach ($levels as $queues) {
            do {
                $picked = false;
                foreach (['gap', 'compose', 'order'] as $kind) {
                    if (($queues[$kind] ?? []) !== []) {
                        $result[] = array_shift($queues[$kind]);
                        $picked = true;
                    }
                }
            } while ($picked);
        }
        return $result;
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
    private static function build(array $question, int $index, bool $useGroupedTokenLimit = false, bool $manualInput = false): ?array
    {
        $sentence = (string) $question['question'];
        $answers = Arr::map((array) $question['answer_map'], fn ($answer) => trim((string) $answer));

        $sentence = preg_replace_callback('/\{([^{}]+)\}/', function (array $matches) use ($answers): string {
            return $answers[$matches[1]] ?? $matches[0];
        }, $sentence) ?? $sentence;
        // An explicitly authored no-article gap requires '-' as its answer,
        // but the same source's full English sentence contains no dash.
        // Only an exact finite UUID/question/answer/revision binding opts in.
        if ($manualInput && ($answers === ['a1' => '-'])) {
            $sentence = NounsArticlesQuantityPracticeQuality::reorderSentence($question) ?? $sentence;
        }
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
        if ($manualInput && self::validAuthoredGroups($question['reorder_groups'] ?? null, $sentence)) {
            $groups = array_values($question['reorder_groups']);
        }

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
            'reorder_template_constraint' => !$manualInput && filled($question['compose_source_text'] ?? null),
        ] + ($manualInput ? ['reorder_manual_input' => true] : []);
    }

    private static function validAuthoredGroups(mixed $groups, string $sentence): bool
    {
        if (!is_array($groups) || count($groups) < 2) { return false; }
        foreach ($groups as $group) {
            if (!is_string($group) || trim($group) !== $group || count(preg_split('/\s+/u', $group) ?: []) < 1
                || count(preg_split('/\s+/u', $group) ?: []) > 3) { return false; }
        }
        return implode(' ', $groups) === $sentence;
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
