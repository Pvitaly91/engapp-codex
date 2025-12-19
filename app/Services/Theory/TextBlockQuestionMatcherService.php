<?php

namespace App\Services\Theory;

use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Services\Traits\TagMatchingTrait;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Service for finding practice questions that match a theory text block by tags.
 *
 * This is the "inverse" of MarkerTheoryMatcherService:
 * - MarkerTheoryMatcherService: question marker tags → best matching theory text block
 * - TextBlockQuestionMatcherService: theory text block tags → best matching questions
 *
 * Both use the same shared TagMatchingTrait for:
 * - Tag normalization and aliasing
 * - Tag weighting (general vs detailed)
 * - Score calculation and skip threshold
 */
class TextBlockQuestionMatcherService
{
    use TagMatchingTrait;

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
     * Uses the same tag matching logic as MarkerTheoryMatcherService but in reverse:
     * takes text block tags and finds questions with overlapping tags.
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

        if ($block->tags->isEmpty()) {
            return collect();
        }

        // Normalize block tags using shared trait logic
        $blockTagNames = $this->normalizeTags($block->tags->pluck('name')->toArray());

        if (empty($blockTagNames)) {
            return collect();
        }

        // Load candidate questions
        $candidates = $this->loadCandidateQuestions($block->tags->pluck('id')->toArray(), $excludeQuestionIds);

        if ($candidates->isEmpty()) {
            return collect();
        }

        // Score and filter candidates
        $scoredCandidates = $this->scoreCandidates($candidates, $blockTagNames);

        if (empty($scoredCandidates)) {
            return collect();
        }

        // Sort by score descending
        usort($scoredCandidates, fn ($a, $b) => $b['score'] <=> $a['score']);

        // Take top N candidates for weighted random selection
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
     * Load candidate questions that have at least one matching tag.
     *
     * @param  array<int>  $blockTagIds
     * @param  array<int>  $excludeQuestionIds
     * @return EloquentCollection<Question>
     */
    private function loadCandidateQuestions(array $blockTagIds, array $excludeQuestionIds): EloquentCollection
    {
        if (! Schema::hasTable('questions') || ! Schema::hasTable('question_tag')) {
            return new EloquentCollection();
        }

        $query = Question::query()
            ->with(['tags:id,name', 'options', 'answers.option', 'verbHints.option', 'hints'])
            ->whereHas('tags', function ($q) use ($blockTagIds) {
                $q->whereIn('tags.id', $blockTagIds);
            });

        if (! empty($excludeQuestionIds)) {
            $query->whereNotIn('id', $excludeQuestionIds);
        }

        // Limit candidates to prevent loading too many records
        return $query->limit(200)->get();
    }

    /**
     * Score all candidate questions against block tags.
     *
     * @param  EloquentCollection<Question>  $candidates
     * @param  array<string>  $blockTagNames  Normalized block tag names
     * @return array<array{question: Question, score: float, matched_tags: array, detailed_matches: int}>
     */
    private function scoreCandidates(EloquentCollection $candidates, array $blockTagNames): array
    {
        $scoredCandidates = [];

        foreach ($candidates as $question) {
            $questionTagNames = $this->normalizeTags($question->tags->pluck('name')->toArray());

            // Calculate tag intersection
            $matchedTags = array_values(array_intersect($blockTagNames, $questionTagNames));

            if (empty($matchedTags)) {
                continue;
            }

            // Use shared scoring logic from trait
            $scoring = $this->scoreMatchedTags($matchedTags);

            // Skip weak matches (same logic as MarkerTheoryMatcherService)
            if ($scoring['skip']) {
                continue;
            }

            // Apply IDF bonus for rare tags
            $idfBonus = $this->calculateIdfBonus($matchedTags, $question->tags);
            $finalScore = $scoring['score'] + $idfBonus;

            $scoredCandidates[] = [
                'question' => $question,
                'score' => round($finalScore, 2),
                'matched_tags' => $matchedTags,
                'detailed_matches' => $scoring['detailed_matches'],
            ];
        }

        return $scoredCandidates;
    }

    /**
     * Calculate IDF bonus for matched tags.
     * Tags that appear less frequently get higher bonus.
     *
     * @param  array<string>  $matchedTags
     * @param  Collection<Tag>  $questionTags
     */
    private function calculateIdfBonus(array $matchedTags, Collection $questionTags): float
    {
        if ($this->tagFrequencyCache === null || $this->totalQuestionsCount === null) {
            return 0.0;
        }

        $bonus = 0.0;

        foreach ($matchedTags as $normalizedTag) {
            // Find the tag ID by matching normalized names
            $tagId = null;
            foreach ($questionTags as $tag) {
                if ($this->normalizeTagName($tag->name) === $normalizedTag) {
                    $tagId = $tag->id;
                    break;
                }
            }

            if ($tagId === null || ! isset($this->tagFrequencyCache[$tagId])) {
                continue;
            }

            $frequency = $this->tagFrequencyCache[$tagId];

            // IDF = log(total / frequency)
            if ($frequency > 0 && $this->totalQuestionsCount > 0) {
                $idf = log(($this->totalQuestionsCount + 1) / ($frequency + 1));
                // Cap the bonus per tag
                $bonus += min($idf / 5, 0.5);
            }
        }

        return $bonus;
    }

    /**
     * Initialize tag frequency cache for IDF calculations.
     */
    private function initializeTagFrequencyCache(): void
    {
        if ($this->tagFrequencyCache !== null) {
            return;
        }

        if (! Schema::hasTable('question_tag')) {
            $this->tagFrequencyCache = [];
            $this->totalQuestionsCount = 0;

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
     * Perform weighted random selection from candidates.
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
}
