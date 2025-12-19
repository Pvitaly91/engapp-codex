<?php

namespace App\Services\Traits;

use Illuminate\Support\Str;

/**
 * Shared tag matching logic for theory-question matching in both directions.
 *
 * This trait provides the core logic for:
 * - Tag normalization and aliasing
 * - Tag weighting (general vs detailed tags)
 * - Score calculation with skip threshold
 * - Intro/general block detection
 *
 * Used by:
 * - MarkerTheoryMatcherService (marker tags → theory text blocks)
 * - TextBlockQuestionMatcherService (theory text blocks → questions)
 */
trait TagMatchingTrait
{
    /**
     * General tags that have lower weight in scoring.
     * These are broad category tags that don't indicate specific topic relevance.
     */
    protected static array $generalTags = [
        'types-of-questions',
        'question-forms',
        'grammar',
        'theory',
        'question-sentences',
        'types-of-question-sentences',
        'question-formation-practice',
    ];

    /**
     * Tags that indicate introductory/overview content.
     * Blocks with these tags should only be returned when no better candidate exists.
     */
    protected static array $introTags = [
        'introduction',
        'overview',
        'summary',
        'navigation',
        'index',
        'tips',
        'learning',
    ];

    /**
     * Detailed tags (first priority) - highest weight (3.5).
     * These indicate very specific grammar concepts.
     */
    protected static array $detailFirstTags = [
        'tag-questions',
        'question-tags',
        'indirect-questions',
        'embedded-questions',
        'question-word-order',
        'statement-order',
        'subject-questions',
        'negative-questions',
        'alternative-questions',
        'wh-questions',
        'yes-no-questions',
        'special-questions',
        'question-formation',
        'be-ing',
    ];

    /**
     * Detailed tags (second priority) - medium-high weight (2.5).
     * These indicate specific tenses or auxiliary verbs.
     */
    protected static array $detailSecondTags = [
        'modal-verbs',
        'do-does-did',
        'to-be',
        'be-am-is-are-was-were',
        'present-simple',
        'past-simple',
        'future-simple',
        'present-continuous',
        'past-continuous',
        'present-perfect',
        'past-perfect',
        'present-perfect-continuous',
        'past-perfect-continuous',
        'auxiliaries',
        'have-has-had',
        'can-could',
        'will-would',
        'may-might',
    ];

    /**
     * Tag aliases to normalize similar tags to a canonical form.
     */
    protected static array $tagAliases = [
        'question-tags' => 'tag-questions',
        'question-word-order' => 'statement-order',
        // Map slash versions to dash versions for common auxiliary tags
        'do/does/did' => 'do-does-did',
        'can/could' => 'can-could',
        'will/would' => 'will-would',
        'may/might' => 'may-might',
        'have/has/had' => 'have-has-had',
    ];

    /**
     * Normalize tags to lowercase, trimmed, unique array with alias resolution.
     *
     * @param  array|string|null  $tags
     * @return array<string>
     */
    protected function normalizeTags($tags): array
    {
        if ($tags === null) {
            return [];
        }

        if (is_string($tags)) {
            $tags = array_filter(array_map('trim', explode(',', $tags)));
        }

        if (! is_array($tags)) {
            return [];
        }

        return collect($tags)
            ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
            ->map(fn ($tag) => $this->normalizeTagName($tag))
            ->map(fn ($tag) => static::$tagAliases[$tag] ?? $tag)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Normalize a single tag name.
     */
    protected function normalizeTagName(string $tag): string
    {
        $slug = Str::slug(trim($tag));

        if ($slug === '') {
            return '';
        }

        // Preserve slashes that Str::slug would drop for patterns like Do/Does/Did
        if (str_contains($tag, '/')) {
            return str_replace(' ', '-', strtolower(trim($tag)));
        }

        return $slug;
    }

    /**
     * Calculate score for matched tags with skip detection.
     *
     * @param  array<string>  $matchedTags  Normalized matched tag names
     * @return array{score: float, detailed_matches: int, general_matches: int, skip: bool}
     */
    protected function scoreMatchedTags(array $matchedTags): array
    {
        $score = 0.0;
        $detailedMatches = 0;
        $generalMatches = 0;

        foreach ($matchedTags as $tag) {
            $weight = $this->weightForTag($tag);
            $score += $weight;

            if (in_array($tag, static::$generalTags, true)) {
                $generalMatches++;
            } elseif ($weight >= 2.5) {
                $detailedMatches++;
            }
        }

        // Bonus for multiple matches
        $score += count($matchedTags) * 0.25;

        // Skip if no detailed matches and only one tag
        $skip = $detailedMatches === 0 && count($matchedTags) <= 1;
        // Also skip if all matches are general tags
        $skip = $skip || ($generalMatches === count($matchedTags));

        return [
            'score' => round($score, 2),
            'detailed_matches' => $detailedMatches,
            'general_matches' => $generalMatches,
            'skip' => $skip,
        ];
    }

    /**
     * Get weight for a tag based on its category.
     */
    protected function weightForTag(string $tag): float
    {
        if (in_array($tag, static::$generalTags, true)) {
            return 0.5;
        }

        if (in_array($tag, static::$detailFirstTags, true)) {
            return 3.5;
        }

        if (in_array($tag, static::$detailSecondTags, true)) {
            return 2.5;
        }

        if (str_contains($tag, 'cefr')) {
            return 0.75;
        }

        return 1.5;
    }

    /**
     * Check if a tag is an intro/overview tag.
     */
    protected function isIntroTag(string $tag): bool
    {
        return in_array($tag, static::$introTags, true);
    }

    /**
     * Check if a tag is a general (low-value) tag.
     */
    protected function isGeneralTag(string $tag): bool
    {
        return in_array($tag, static::$generalTags, true);
    }

    /**
     * Count intro tags in a set of normalized tags.
     */
    protected function countIntroTags(array $normalizedTags): int
    {
        $count = 0;
        foreach ($normalizedTags as $tag) {
            if ($this->isIntroTag($tag)) {
                $count++;
            }
        }

        return $count;
    }
}
