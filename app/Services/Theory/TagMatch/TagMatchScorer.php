<?php

namespace App\Services\Theory\TagMatch;

use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TagMatchScorer
{
    /**
     * General tags that have lower weight in scoring.
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

    public function detailFirstTags(): array
    {
        return static::$detailFirstTags;
    }

    public function detailSecondTags(): array
    {
        return static::$detailSecondTags;
    }

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
     * Score tag intersection between input and candidate tag collections.
     *
     * @param  Collection<int, Tag>  $inputTags
     * @param  Collection<int, Tag>  $candidateTags
     * @return array{score: float, detailed_matches: int, general_matches: int, skip: bool, matched_normalized_tags: array<int, string>, matched_tag_ids: array<int>}
     */
    public function scoreCollections(Collection $inputTags, Collection $candidateTags): array
    {
        $inputNormalized = $this->normalizeTags($inputTags->pluck('name')->toArray());
        $candidateNormalized = $this->normalizeTags($candidateTags->pluck('name')->toArray());

        $matchedNormalized = array_values(array_intersect($inputNormalized, $candidateNormalized));

        $scoreData = $this->scoreMatchedTags($matchedNormalized);

        $matchedIds = $this->calculateMatchedTagIds($matchedNormalized, $inputTags, $candidateTags);

        return [
            ...$scoreData,
            'matched_normalized_tags' => $matchedNormalized,
            'matched_tag_ids' => $matchedIds,
        ];
    }

    /**
     * Score tag intersection when only tag IDs are provided.
     *
     * @param  array<int>  $inputTagIds
     * @param  array<int>  $candidateTagIds
     * @return array{score: float, detailed_matches: int, general_matches: int, skip: bool, matched_normalized_tags: array<int, string>, matched_tag_ids: array<int>}
     */
    public function scoreByIds(array $inputTagIds, array $candidateTagIds): array
    {
        $uniqueIds = array_values(array_unique(array_merge($inputTagIds, $candidateTagIds)));
        $tags = Tag::query()
            ->whereIn('id', $uniqueIds)
            ->get(['id', 'name']);

        $inputTags = $tags->whereIn('id', $inputTagIds);
        $candidateTags = $tags->whereIn('id', $candidateTagIds);

        return $this->scoreCollections($inputTags, $candidateTags);
    }

    /**
     * Normalize tags to lowercase, trimmed, unique array with alias resolution.
     *
     * @param  array|string|null  $tags
     * @return array<string>
     */
    public function normalizeTags($tags): array
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
    public function normalizeTagName(string $tag): string
    {
        $slug = Str::slug(trim($tag));

        if ($slug === '') {
            return '';
        }

        if (str_contains($tag, '/')) {
            return str_replace(' ', '-', strtolower(trim($tag)));
        }

        return $slug;
    }

    /**
     * Calculate score for matched tags with skip detection.
     *
     * @param  array<string>  $matchedTags
     * @return array{score: float, detailed_matches: int, general_matches: int, skip: bool}
     */
    public function scoreMatchedTags(array $matchedTags): array
    {
        $score = 0.0;
        $detailedMatches = 0;
        $generalMatches = 0;

        foreach ($matchedTags as $tag) {
            $weight = $this->weightForTag($tag);
            $score += $weight;

            if ($this->isGeneralTag($tag)) {
                $generalMatches++;
            } elseif ($weight >= 2.5) {
                $detailedMatches++;
            }
        }

        $score += count($matchedTags) * 0.25;

        $skip = $detailedMatches === 0 && count($matchedTags) <= 1;
        $skip = $skip || ($generalMatches === count($matchedTags));

        return [
            'score' => round($score, 2),
            'detailed_matches' => $detailedMatches,
            'general_matches' => $generalMatches,
            'skip' => $skip,
        ];
    }

    /**
     * Map normalized tag names to their IDs for a tag collection.
     *
     * @param  Collection<int, Tag>  $tags
     * @return array<string, array<int, array{id: int, name: string}>>
     */
    public function mapNormalizedTagsToIds(Collection $tags): array
    {
        $map = [];

        foreach ($tags as $tag) {
            $normalized = $this->normalizeTagName($tag->name);
            $normalized = static::$tagAliases[$normalized] ?? $normalized;

            if ($normalized === '') {
                continue;
            }

            $map[$normalized] ??= [];
            $map[$normalized][] = ['id' => $tag->id, 'name' => $tag->name];
        }

        return $map;
    }

    /**
     * Calculate matched tag IDs based on normalized matches.
     *
     * @param  array<int, string>  $matchedNormalizedTags
     * @param  Collection<int, Tag>  $inputTags
     * @param  Collection<int, Tag>  $candidateTags
     * @return array<int>
     */
    public function calculateMatchedTagIds(array $matchedNormalizedTags, Collection $inputTags, Collection $candidateTags): array
    {
        if (empty($matchedNormalizedTags)) {
            return [];
        }

        $inputMap = $this->mapNormalizedTagsToIds($inputTags);
        $candidateMap = $this->mapNormalizedTagsToIds($candidateTags);

        $matchedIds = [];

        foreach ($matchedNormalizedTags as $tag) {
            if (! isset($inputMap[$tag], $candidateMap[$tag])) {
                continue;
            }

            $inputIds = array_column($inputMap[$tag], 'id');
            $candidateIds = array_column($candidateMap[$tag], 'id');

            $matchedIds = array_merge($matchedIds, array_intersect($inputIds, $candidateIds));
        }

        return array_values(array_unique($matchedIds));
    }

    public function isIntroTag(string $tag): bool
    {
        return in_array($tag, static::$introTags, true);
    }

    public function isGeneralTag(string $tag): bool
    {
        return in_array($tag, static::$generalTags, true);
    }

    public function countIntroTags(array $normalizedTags): int
    {
        $count = 0;
        foreach ($normalizedTags as $tag) {
            if ($this->isIntroTag($tag)) {
                $count++;
            }
        }

        return $count;
    }

    protected function weightForTag(string $tag): float
    {
        if ($this->isGeneralTag($tag)) {
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
}

