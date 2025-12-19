<?php

namespace App\Services\Theory;

use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Service for matching questions to text blocks based on tag intersection.
 *
 * This service follows the same philosophy as marker->text_block matching in v2 tests,
 * but in reverse direction (text_block -> questions).
 *
 * Key requirement: A question must match ALL anchor tags AND at least ONE detail tag.
 * If no detail match exists, return empty collection (no fallback to general matches).
 */
class TextBlockToQuestionsMatcherService
{
    /**
     * Anchor tags - required theme/type tags that define the main topic.
     * A question MUST have ALL of these tags to be considered.
     */
    private const ANCHOR_TAGS = [
        'types of questions',
        'yes/no questions',
        'wh-questions',
        'tag questions',
        'indirect questions',
        'negative questions',
        'subject questions',
        'alternative questions',
        'question forms',
        'question formation practice',
        'general questions',
    ];

    /**
     * Detail tags - grammatical feature tags (modals, auxiliaries, tenses).
     * A question must have at least ONE of these to be a valid match when the block has details.
     */
    private const DETAIL_TAGS = [
        // Modal verbs
        'can/could',
        'will/would',
        'should',
        'must',
        'may/might',
        'modal verbs',
        // Auxiliary verbs
        'do/does/did',
        'have/has/had',
        'be (am/is/are/was/were)',
        // Tenses
        'present simple',
        'past simple',
        'future simple',
        'present continuous',
        'past continuous',
        'present perfect',
        'past perfect',
        'present perfect continuous',
        'past perfect continuous',
        // Other grammatical features
        'question word order',
        'to be',
        'to be questions',
        'auxiliary verbs',
        'question tags',
        'disjunctive questions',
    ];

    /**
     * Tags that should be ignored for matching purposes (too general).
     */
    private const IGNORED_TAGS = [
        'grammar',
        'theory',
        'english grammar theme',
        'english grammar detail',
        'english grammar tense',
        'english grammar auxiliary',
        'english grammar level',
    ];

    /**
     * Find questions that match a text block's tags with must-match rules.
     *
     * @param  TextBlock  $block  The text block to find questions for
     * @param  int  $limit  Maximum number of questions to return
     * @param  array  $excludeQuestionIds  Question IDs to exclude (already shown on page)
     * @return Collection<Question>
     */
    public function findQuestionsForTextBlock(TextBlock $block, int $limit = 5, array $excludeQuestionIds = []): Collection
    {
        // Load block tags if not already loaded
        if (! $block->relationLoaded('tags')) {
            $block->load('tags');
        }

        $blockTagNames = $block->tags->pluck('name')
            ->map(fn ($name) => strtolower(trim($name)))
            ->filter()
            ->unique()
            ->values();

        if ($blockTagNames->isEmpty()) {
            return collect();
        }

        // Split block tags into anchor and detail categories
        $classification = $this->classifyTags($blockTagNames);
        $anchorTagNames = $classification['anchors'];
        $detailTagNames = $classification['details'];

        // If no anchor tags, we can't do meaningful matching
        if ($anchorTagNames->isEmpty()) {
            return collect();
        }

        // Get tag IDs for database queries
        $anchorTagIds = $this->getTagIdsByNames($anchorTagNames);
        $detailTagIds = $this->getTagIdsByNames($detailTagNames);

        // If block has detail tags but we couldn't find them in DB, return empty
        if ($detailTagNames->isNotEmpty() && empty($detailTagIds)) {
            return collect();
        }

        // Build the query for candidate questions
        $query = Question::query()
            ->with(['tags:id,name', 'answers.option'])
            ->whereNotIn('id', $excludeQuestionIds);

        // Step 1: Require ALL anchor tags
        foreach ($anchorTagIds as $anchorTagId) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $anchorTagId));
        }

        // Step 2: If block has detail tags, require at least ONE detail tag
        if (! empty($detailTagIds)) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $detailTagIds));
        }

        // Get candidates
        $candidates = $query->limit(100)->get();

        if ($candidates->isEmpty()) {
            return collect();
        }

        // Score and sort candidates
        $blockTagIdsSet = $block->tags->pluck('id')->flip();
        $detailTagIdsSet = collect($detailTagIds)->flip();

        $scored = $candidates->map(function (Question $question) use ($blockTagIdsSet, $detailTagIdsSet) {
            $questionTagIds = $question->tags->pluck('id');

            // Count matching tags
            $matchCount = $questionTagIds->filter(fn ($id) => $blockTagIdsSet->has($id))->count();

            // Give extra weight to detail tag matches
            $detailMatchCount = $questionTagIds->filter(fn ($id) => $detailTagIdsSet->has($id))->count();

            // Score = base matches + detail bonus
            $score = $matchCount + ($detailMatchCount * 2);

            return [
                'question' => $question,
                'score' => $score,
                'match_count' => $matchCount,
                'detail_match_count' => $detailMatchCount,
            ];
        });

        // Sort by score descending
        $sorted = $scored->sortByDesc('score');

        // Take top candidates and apply weighted random selection
        $topCandidates = $sorted->take(30);

        if ($topCandidates->count() <= $limit) {
            return $topCandidates->pluck('question')->values();
        }

        // Weighted random selection from top candidates
        return $this->weightedRandomSelection($topCandidates, $limit);
    }

    /**
     * Classify tags into anchor (theme/type) and detail (grammatical feature) categories.
     *
     * @param  Collection  $tagNames  Normalized tag names (lowercase, trimmed)
     * @return array{anchors: Collection, details: Collection}
     */
    public function classifyTags(Collection $tagNames): array
    {
        $anchors = collect();
        $details = collect();

        $anchorSet = collect(self::ANCHOR_TAGS)->map(fn ($t) => strtolower($t))->flip();
        $detailSet = collect(self::DETAIL_TAGS)->map(fn ($t) => strtolower($t))->flip();
        $ignoredSet = collect(self::IGNORED_TAGS)->map(fn ($t) => strtolower($t))->flip();

        foreach ($tagNames as $tagName) {
            $normalized = strtolower(trim($tagName));

            // Skip ignored tags
            if ($ignoredSet->has($normalized)) {
                continue;
            }

            if ($anchorSet->has($normalized)) {
                $anchors->push($normalized);
            } elseif ($detailSet->has($normalized)) {
                $details->push($normalized);
            }
            // Tags not in either set are simply not used for matching
        }

        return [
            'anchors' => $anchors->unique()->values(),
            'details' => $details->unique()->values(),
        ];
    }

    /**
     * Get tag IDs from tag names.
     *
     * @param  Collection  $tagNames  Normalized tag names
     * @return array Tag IDs
     */
    private function getTagIdsByNames(Collection $tagNames): array
    {
        if ($tagNames->isEmpty()) {
            return [];
        }

        return Tag::query()
            ->whereIn('name', $tagNames->toArray())
            ->orWhere(function ($query) use ($tagNames) {
                // Also try case-insensitive matching
                foreach ($tagNames as $name) {
                    $query->orWhereRaw('LOWER(name) = ?', [strtolower($name)]);
                }
            })
            ->pluck('id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Perform weighted random selection from scored candidates.
     *
     * @param  Collection  $scoredCandidates  Candidates with 'score' key
     * @param  int  $limit  Number to select
     * @return Collection<Question>
     */
    private function weightedRandomSelection(Collection $scoredCandidates, int $limit): Collection
    {
        $selected = collect();
        $remaining = $scoredCandidates->values();
        $totalWeight = $remaining->sum('score');

        while ($selected->count() < $limit && $remaining->isNotEmpty()) {
            if ($totalWeight <= 0) {
                // Fallback to simple random if all scores are 0
                $index = random_int(0, $remaining->count() - 1);
            } else {
                $random = random_int(1, (int) $totalWeight);
                $cumulative = 0;
                $index = 0;

                foreach ($remaining as $i => $item) {
                    $cumulative += $item['score'];
                    if ($cumulative >= $random) {
                        $index = $i;
                        break;
                    }
                }
            }

            $chosen = $remaining->pull($index);
            $selected->push($chosen['question']);
            $totalWeight -= $chosen['score'];
            $remaining = $remaining->values();
        }

        return $selected->values();
    }

    /**
     * Get diagnostic information for a text block's matching process.
     *
     * @param  TextBlock  $block  The text block to analyze
     * @param  int  $limit  Number of top questions to return
     * @return array Diagnostic information
     */
    public function getMatchingDiagnostics(TextBlock $block, int $limit = 10): array
    {
        // Load block tags if not already loaded
        if (! $block->relationLoaded('tags')) {
            $block->load('tags');
        }

        $blockTagNames = $block->tags->pluck('name')
            ->map(fn ($name) => strtolower(trim($name)))
            ->filter()
            ->unique()
            ->values();

        $classification = $this->classifyTags($blockTagNames);
        $anchorTagNames = $classification['anchors'];
        $detailTagNames = $classification['details'];

        $anchorTagIds = $this->getTagIdsByNames($anchorTagNames);
        $detailTagIds = $this->getTagIdsByNames($detailTagNames);

        // Count candidates at each step
        $candidatesAfterAnchors = 0;
        $candidatesAfterDetails = 0;

        if ($anchorTagNames->isNotEmpty() && ! empty($anchorTagIds)) {
            $anchorQuery = Question::query();
            foreach ($anchorTagIds as $anchorTagId) {
                $anchorQuery->whereHas('tags', fn ($q) => $q->where('tags.id', $anchorTagId));
            }
            $candidatesAfterAnchors = $anchorQuery->count();

            if ($detailTagNames->isNotEmpty() && ! empty($detailTagIds)) {
                $fullQuery = clone $anchorQuery;
                $fullQuery->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $detailTagIds));
                $candidatesAfterDetails = $fullQuery->count();
            } else {
                $candidatesAfterDetails = $candidatesAfterAnchors;
            }
        }

        // Get top matching questions with details
        $questions = $this->findQuestionsForTextBlock($block, $limit);

        $blockTagIdsSet = $block->tags->pluck('id')->flip();

        $topQuestions = $questions->map(function (Question $question) use ($blockTagIdsSet) {
            $questionTags = $question->tags->pluck('name')->toArray();
            $matchedTagIds = $question->tags->pluck('id')->filter(fn ($id) => $blockTagIdsSet->has($id));
            $matchedTags = $question->tags->whereIn('id', $matchedTagIds)->pluck('name')->toArray();

            return [
                'id' => $question->id,
                'uuid' => $question->uuid,
                'question' => mb_substr($question->question, 0, 80) . (mb_strlen($question->question) > 80 ? '...' : ''),
                'all_tags' => $questionTags,
                'matched_tags' => $matchedTags,
                'score' => count($matchedTags),
            ];
        });

        return [
            'block_uuid' => $block->uuid,
            'block_heading' => $block->heading,
            'all_block_tags' => $block->tags->pluck('name')->toArray(),
            'anchor_tags' => $anchorTagNames->toArray(),
            'detail_tags' => $detailTagNames->toArray(),
            'anchor_tag_ids' => $anchorTagIds,
            'detail_tag_ids' => $detailTagIds,
            'candidates_after_anchors' => $candidatesAfterAnchors,
            'candidates_after_details' => $candidatesAfterDetails,
            'top_questions' => $topQuestions->toArray(),
        ];
    }
}
