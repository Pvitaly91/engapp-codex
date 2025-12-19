<?php

namespace App\Services\Theory\TagMatch;

use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TagMatchScorer
{
    /**
     * Tag name that should not be the sole matching criterion.
     */
    public const TYPES_OF_QUESTIONS_TAG = 'types-of-questions';

    public const GENERAL_TAGS = [
        'types-of-questions',
        'question-forms',
        'grammar',
        'theory',
        'question-sentences',
        'types-of-question-sentences',
        'question-formation-practice',
    ];

    public const DETAIL_FIRST_TAGS = [
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

    public const DETAIL_SECOND_TAGS = [
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

    public const TAG_ALIASES = [
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
     * Normalize tags to lowercase, trimmed, unique array.
     *
     * @param  array|string|null  $tags
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
            ->map(fn ($tag) => self::TAG_ALIASES[$tag] ?? $tag)
            ->unique()
            ->values()
            ->all();
    }

    public function normalizeTagName(string $tag): string
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

            if (in_array($tag, self::GENERAL_TAGS, true)) {
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
     * @return array{matched_tags: array<int, string>, matched_tag_ids: array<int>, matched_tag_names: array<int, string>, score: float, detailed_matches: int, general_matches: int, skip: bool}
     */
    public function scoreMatch(Collection $inputTags, Collection $candidateTags): array
    {
        $normalizedInputTags = $this->normalizeTags($inputTags->pluck('name')->toArray());
        $normalizedCandidateTags = $this->normalizeTags($candidateTags->pluck('name')->toArray());

        $matched = array_values(array_intersect($normalizedInputTags, $normalizedCandidateTags));

        if (empty($matched)) {
            return [
                'matched_tags' => [],
                'matched_tag_ids' => [],
                'matched_tag_names' => [],
                'score' => 0.0,
                'detailed_matches' => 0,
                'general_matches' => 0,
                'skip' => true,
            ];
        }

        $scoring = $this->scoreMatchedTags($matched);
        $matchedTagData = $this->calculateMatchedTagData($matched, $inputTags, $candidateTags);

        return array_merge($scoring, $matchedTagData, [
            'matched_tags' => $matched,
        ]);
    }

    /**
     * Calculate matched tag IDs and original names based on normalized matches.
     *
     * @param  array<int, string>  $matchedNormalizedTags
     * @param  Collection<int, Tag>  $inputTags
     * @param  Collection<int, Tag>  $candidateTags
     * @return array{matched_tag_ids: array<int>, matched_tag_names: array<int, string>}
     */
    public function calculateMatchedTagData(array $matchedNormalizedTags, Collection $inputTags, Collection $candidateTags): array
    {
        if (empty($matchedNormalizedTags)) {
            return ['matched_tag_ids' => [], 'matched_tag_names' => []];
        }

        $inputMap = $this->mapNormalizedTagsToIds($inputTags);
        $candidateMap = $this->mapNormalizedTagsToIds($candidateTags);

        $matchedIds = [];
        $matchedNames = [];

        foreach ($matchedNormalizedTags as $tag) {
            if (! isset($inputMap[$tag], $candidateMap[$tag])) {
                continue;
            }

            $inputIds = array_column($inputMap[$tag], 'id');
            $candidateEntries = $candidateMap[$tag];
            $candidateIds = array_column($candidateEntries, 'id');

            $matchedIds = array_merge($matchedIds, array_intersect($inputIds, $candidateIds));
            $matchedNames = array_merge($matchedNames, array_column($candidateEntries, 'name'));
        }

        return [
            'matched_tag_ids' => array_values(array_unique($matchedIds)),
            'matched_tag_names' => array_values(array_unique($matchedNames)),
        ];
    }

    /**
     * Map normalized tag names to their IDs and original names.
     *
     * @param  Collection<int, Tag>  $tags
     * @return array<string, array<int, array{id: int, name: string}>>
     */
    private function mapNormalizedTagsToIds(Collection $tags): array
    {
        $map = [];

        foreach ($tags as $tag) {
            $normalized = $this->normalizeTagName($tag->name);
            $normalized = self::TAG_ALIASES[$normalized] ?? $normalized;

            if ($normalized === '') {
                continue;
            }

            $map[$normalized] ??= [];
            $map[$normalized][] = ['id' => $tag->id, 'name' => $tag->name];
        }

        return $map;
    }

    private function weightForTag(string $tag): float
    {
        if (in_array($tag, self::GENERAL_TAGS, true)) {
            return 0.5;
        }

        if (in_array($tag, self::DETAIL_FIRST_TAGS, true)) {
            return 3.5;
        }

        if (in_array($tag, self::DETAIL_SECOND_TAGS, true)) {
            return 2.5;
        }

        if (str_contains($tag, 'cefr')) {
            return 0.75;
        }

        return 1.5;
    }
}
