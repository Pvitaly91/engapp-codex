<?php

namespace App\Services\Theory;

use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TextBlockQuestionMatcherService
{
    /**
     * General tags that have lower weight in scoring.
     * These are broad category tags that don't indicate specific topic relevance.
     */
    private const GENERAL_TAGS = [
        'types-of-questions',
        'question-forms',
        'grammar',
        'theory',
        'question-sentences',
        'types-of-question-sentences',
        'question-formation-practice',
    ];

    /**
     * Detailed tags (first priority) - highest weight.
     * These indicate very specific grammar concepts.
     */
    private const DETAIL_FIRST_TAGS = [
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
     * Detailed tags (second priority) - medium-high weight.
     * These indicate specific tenses or auxiliary verbs.
     */
    private const DETAIL_SECOND_TAGS = [
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
     * Minimum score threshold for including a question.
     */
    private const MIN_SCORE_THRESHOLD = 2.0;

    /**
     * Cache for tag frequencies (IDF calculation).
     *
     * @var array<int, float>|null
     */
    private ?array $tagFrequencyCache = null;

    /**
     * Total count of questions (for IDF).
     */
    private ?int $totalQuestionsCount = null;

    /**
     * Find questions that match a text block by tag intersection.
     *
     * @param  TextBlock  $block  The text block to find matching questions for
     * @param  int  $limit  Maximum number of results to return (3-5 random with weights)
     * @param  array<int>  $excludeQuestionIds  Question IDs to exclude (to avoid duplicates)
     * @return Collection<int, Question>
     */
    public function findQuestionsForTextBlock(
        TextBlock $block,
        int $limit = 5,
        array $excludeQuestionIds = []
    ): Collection {
        // Load block tags if not already loaded
        if (! $block->relationLoaded('tags')) {
            $block->load('tags:id,name');
        }

        $blockTagIds = $block->tags->pluck('id')->toArray();

        if (empty($blockTagIds)) {
            return collect();
        }

        $blockTagNames = $this->normalizeTagNames($block->tags->pluck('name')->toArray());

        // Find candidate questions that have at least one matching tag
        $candidatesQuery = Question::query()
            ->with('tags:id,name')
            ->whereHas('tags', function ($query) use ($blockTagIds) {
                $query->whereIn('tags.id', $blockTagIds);
            });

        // Exclude already used questions if provided
        if (! empty($excludeQuestionIds)) {
            $candidatesQuery->whereNotIn('id', $excludeQuestionIds);
        }

        // Limit initial candidates to prevent loading too many records
        $candidates = $candidatesQuery->limit(200)->get();

        if ($candidates->isEmpty()) {
            return collect();
        }

        // Calculate scores for all candidates
        $scoredCandidates = [];

        foreach ($candidates as $question) {
            $questionTagNames = $this->normalizeTagNames($question->tags->pluck('name')->toArray());

            // Calculate tag intersection
            $matchedTags = array_values(array_intersect($blockTagNames, $questionTagNames));
            $score = $this->calculateScore($matchedTags, $question->tags);

            // Filter out weak matches
            if ($score < self::MIN_SCORE_THRESHOLD) {
                continue;
            }

            // Check if match is only by general tags
            if ($this->isOnlyGeneralTagMatch($matchedTags)) {
                continue;
            }

            $scoredCandidates[] = [
                'question' => $question,
                'score' => $score,
                'matched_tags' => $matchedTags,
            ];
        }

        if (empty($scoredCandidates)) {
            return collect();
        }

        // Sort by score descending
        usort($scoredCandidates, fn ($a, $b) => $b['score'] <=> $a['score']);

        // Take top N candidates (e.g., 30) for weighted random selection
        $topCandidates = array_slice($scoredCandidates, 0, 30);

        // Apply weighted random selection
        $selectedQuestions = $this->weightedRandomSelection($topCandidates, $limit);

        return collect($selectedQuestions)->map(fn ($item) => $item['question']);
    }

    /**
     * Batch find questions for multiple text blocks, avoiding duplicates across blocks.
     *
     * @param  EloquentCollection<TextBlock>  $blocks
     * @param  int  $limitPerBlock  Maximum questions per block
     * @return array<string, Collection<Question>> Keyed by text block UUID
     */
    public function findQuestionsForTextBlocks(
        EloquentCollection $blocks,
        int $limitPerBlock = 5
    ): array {
        $results = [];
        $usedQuestionIds = [];

        // Preload tags for all blocks
        $blocks->load('tags:id,name');

        // Initialize tag frequency cache for IDF calculation
        $this->initializeTagFrequencyCache();

        foreach ($blocks as $block) {
            // Skip blocks without tags
            if ($block->tags->isEmpty()) {
                continue;
            }

            $questions = $this->findQuestionsForTextBlock($block, $limitPerBlock, $usedQuestionIds);

            if ($questions->isNotEmpty()) {
                $results[$block->uuid] = $questions;

                // Add used question IDs to exclusion list
                $usedQuestionIds = array_merge(
                    $usedQuestionIds,
                    $questions->pluck('id')->toArray()
                );
            }
        }

        return $results;
    }

    /**
     * Calculate score for matched tags with IDF weighting.
     *
     * @param  array<string>  $matchedTags  Normalized matched tag names
     * @param  Collection<Tag>  $questionTags  Original question tags (for IDF lookup)
     */
    private function calculateScore(array $matchedTags, Collection $questionTags): float
    {
        if (empty($matchedTags)) {
            return 0.0;
        }

        $score = 0.0;

        foreach ($matchedTags as $normalizedTag) {
            // Base weight based on tag type
            $baseWeight = $this->getTagWeight($normalizedTag);

            // Apply IDF bonus (rare tags get higher scores)
            $idfBonus = $this->getIdfBonus($normalizedTag, $questionTags);

            $score += $baseWeight * (1 + $idfBonus);
        }

        // Bonus for having multiple matches
        $matchCountBonus = count($matchedTags) * 0.25;
        $score += $matchCountBonus;

        return round($score, 2);
    }

    /**
     * Get base weight for a tag based on its category.
     */
    private function getTagWeight(string $normalizedTag): float
    {
        if (in_array($normalizedTag, self::GENERAL_TAGS, true)) {
            return 0.5;
        }

        if (in_array($normalizedTag, self::DETAIL_FIRST_TAGS, true)) {
            return 3.5;
        }

        if (in_array($normalizedTag, self::DETAIL_SECOND_TAGS, true)) {
            return 2.5;
        }

        // CEFR level tags (a1, a2, b1, etc.)
        if (preg_match('/^(cefr[-\s]?)?(a[12]|b[12]|c[12])$/i', $normalizedTag)) {
            return 0.75;
        }

        // Default weight for other tags
        return 1.5;
    }

    /**
     * Calculate IDF bonus for a tag (tags that appear less frequently get higher bonus).
     *
     * @param  Collection<Tag>  $questionTags
     */
    private function getIdfBonus(string $normalizedTag, Collection $questionTags): float
    {
        if ($this->tagFrequencyCache === null || $this->totalQuestionsCount === null) {
            return 0.0;
        }

        // Find the tag ID by matching normalized names
        $tagId = null;
        foreach ($questionTags as $tag) {
            if ($this->normalizeTagName($tag->name) === $normalizedTag) {
                $tagId = $tag->id;
                break;
            }
        }

        if ($tagId === null || ! isset($this->tagFrequencyCache[$tagId])) {
            return 0.0;
        }

        $frequency = $this->tagFrequencyCache[$tagId];

        // IDF = log(total / frequency)
        // Higher IDF means the tag is rarer
        if ($frequency > 0 && $this->totalQuestionsCount > 0) {
            $idf = log(($this->totalQuestionsCount + 1) / ($frequency + 1));

            // Normalize IDF to 0-1 range and cap the bonus
            return min($idf / 5, 0.5);
        }

        return 0.0;
    }

    /**
     * Initialize tag frequency cache for IDF calculations.
     */
    private function initializeTagFrequencyCache(): void
    {
        if ($this->tagFrequencyCache !== null) {
            return;
        }

        // Get tag frequencies from question_tag table
        $frequencies = DB::table('question_tag')
            ->select('tag_id', DB::raw('COUNT(*) as frequency'))
            ->groupBy('tag_id')
            ->pluck('frequency', 'tag_id')
            ->toArray();

        $this->tagFrequencyCache = $frequencies;
        $this->totalQuestionsCount = Question::count();
    }

    /**
     * Check if the match is only by general (low-value) tags.
     *
     * @param  array<string>  $matchedTags
     */
    private function isOnlyGeneralTagMatch(array $matchedTags): bool
    {
        if (count($matchedTags) <= 1) {
            // Single tag match - check if it's a general tag
            foreach ($matchedTags as $tag) {
                if (in_array($tag, self::GENERAL_TAGS, true)) {
                    return true;
                }
            }
        }

        // Count non-general tags
        $nonGeneralCount = 0;
        foreach ($matchedTags as $tag) {
            if (! in_array($tag, self::GENERAL_TAGS, true)) {
                $nonGeneralCount++;
            }
        }

        // If all matched tags are general, reject the match
        return $nonGeneralCount === 0;
    }

    /**
     * Perform weighted random selection from candidates.
     *
     * Higher score = higher probability of being selected.
     *
     * @param  array<array{question: Question, score: float, matched_tags: array}>  $candidates
     * @return array<array{question: Question, score: float, matched_tags: array}>
     */
    private function weightedRandomSelection(array $candidates, int $limit): array
    {
        if (count($candidates) <= $limit) {
            return $candidates;
        }

        // Calculate total weight
        $totalWeight = array_sum(array_column($candidates, 'score'));

        if ($totalWeight <= 0) {
            // Fallback to uniform random
            shuffle($candidates);

            return array_slice($candidates, 0, $limit);
        }

        $selected = [];
        $selectedIndices = [];

        for ($i = 0; $i < $limit && count($candidates) > count($selected); $i++) {
            // Generate random value
            $random = mt_rand() / mt_getrandmax() * $totalWeight;

            // Select based on weight
            $cumulativeWeight = 0;
            foreach ($candidates as $index => $candidate) {
                if (in_array($index, $selectedIndices, true)) {
                    continue;
                }

                $cumulativeWeight += $candidate['score'];
                if ($random <= $cumulativeWeight) {
                    $selected[] = $candidate;
                    $selectedIndices[] = $index;
                    $totalWeight -= $candidate['score'];
                    break;
                }
            }
        }

        return $selected;
    }

    /**
     * Normalize tag names to lowercase, trimmed strings.
     *
     * @param  array<string>  $tagNames
     * @return array<string>
     */
    private function normalizeTagNames(array $tagNames): array
    {
        return array_map(fn ($name) => $this->normalizeTagName($name), $tagNames);
    }

    /**
     * Normalize a single tag name.
     */
    private function normalizeTagName(string $tag): string
    {
        return strtolower(trim($tag));
    }
}
