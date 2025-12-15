<?php

namespace App\Services\Theory;

use App\Models\Question;
use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class TheoryQuestionMatcherService
{
    /**
     * CEFR level tags for bonus scoring (pre-normalized to lowercase).
     */
    private const CEFR_TAGS_NORMALIZED = [
        'cefr a1', 'cefr a2', 'cefr b1', 'cefr b2', 'cefr c1', 'cefr c2',
        'a1', 'a2', 'b1', 'b2', 'c1', 'c2',
    ];

    /**
     * Find questions that match a text block by tag intersection.
     *
     * @param  TextBlock  $block  The text block to find matching questions for
     * @param  int  $limit  Maximum number of results to return
     * @return Collection<int, array{question: Question, score: int, matched_tags: array}>
     */
    public function findQuestionsForTextBlock(TextBlock $block, int $limit = 20): Collection
    {
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
        $candidates = Question::query()
            ->with('tags:id,name')
            ->whereHas('tags', function ($query) use ($blockTagIds) {
                $query->whereIn('tags.id', $blockTagIds);
            })
            ->get();

        if ($candidates->isEmpty()) {
            return collect();
        }

        $results = [];

        foreach ($candidates as $question) {
            $questionTagNames = $this->normalizeTagNames($question->tags->pluck('name')->toArray());

            // Calculate tag intersection
            $matchedTags = array_values(array_intersect($blockTagNames, $questionTagNames));
            $baseScore = count($matchedTags);

            if ($baseScore === 0) {
                continue;
            }

            // Add bonus for matching CEFR level tags
            $cefrBonus = $this->calculateCefrBonus($matchedTags);
            $totalScore = $baseScore + $cefrBonus;

            $results[] = [
                'question' => $question,
                'score' => $totalScore,
                'matched_tags' => $matchedTags,
                'base_score' => $baseScore,
                'cefr_bonus' => $cefrBonus,
            ];
        }

        // Sort by score DESC
        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        // Limit and return
        return collect(array_slice($results, 0, $limit));
    }

    /**
     * Find text blocks that match a question by tag intersection.
     * This is the reverse direction - finding theory for a question.
     *
     * @param  Question  $question  The question to find matching theory blocks for
     * @param  int  $limit  Maximum number of results to return
     * @return Collection<int, array{block: TextBlock, score: int, matched_tags: array}>
     */
    public function findTheoryBlocksForQuestion(Question $question, int $limit = 20): Collection
    {
        // Load question tags if not already loaded
        if (! $question->relationLoaded('tags')) {
            $question->load('tags:id,name');
        }

        $questionTagIds = $question->tags->pluck('id')->toArray();

        if (empty($questionTagIds)) {
            return collect();
        }

        $questionTagNames = $this->normalizeTagNames($question->tags->pluck('name')->toArray());

        // Find candidate text blocks that have at least one matching tag
        $candidates = TextBlock::query()
            ->with(['tags:id,name', 'page:id,slug,title', 'category:id,slug,title'])
            ->whereHas('tags', function ($query) use ($questionTagIds) {
                $query->whereIn('tags.id', $questionTagIds);
            })
            ->get();

        if ($candidates->isEmpty()) {
            return collect();
        }

        $results = [];

        foreach ($candidates as $block) {
            $blockTagNames = $this->normalizeTagNames($block->tags->pluck('name')->toArray());

            // Calculate tag intersection
            $matchedTags = array_values(array_intersect($questionTagNames, $blockTagNames));
            $baseScore = count($matchedTags);

            if ($baseScore === 0) {
                continue;
            }

            // Add bonus for matching CEFR level tags
            $cefrBonus = $this->calculateCefrBonus($matchedTags);
            $totalScore = $baseScore + $cefrBonus;

            $results[] = [
                'block' => $block,
                'score' => $totalScore,
                'matched_tags' => $matchedTags,
                'base_score' => $baseScore,
                'cefr_bonus' => $cefrBonus,
            ];
        }

        // Sort by score DESC
        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        // Limit and return
        return collect(array_slice($results, 0, $limit));
    }

    /**
     * Normalize tag names to lowercase trimmed strings.
     *
     * @param  array<string>  $tagNames
     * @return array<string>
     */
    private function normalizeTagNames(array $tagNames): array
    {
        return array_map(fn ($name) => strtolower(trim($name)), $tagNames);
    }

    /**
     * Calculate CEFR level bonus for matched tags.
     * Having matching CEFR tags indicates level-appropriate content.
     *
     * @param  array<string>  $matchedTags  Already normalized tag names
     * @return int
     */
    private function calculateCefrBonus(array $matchedTags): int
    {
        $bonus = 0;

        foreach ($matchedTags as $tag) {
            if (in_array($tag, self::CEFR_TAGS_NORMALIZED, true)) {
                $bonus += 2; // Add 2 points for each matching CEFR tag
            }
        }

        return $bonus;
    }

    /**
     * Batch find questions for multiple text blocks.
     *
     * @param  EloquentCollection<TextBlock>  $blocks
     * @param  int  $limitPerBlock
     * @return array<int, Collection>  Keyed by text block ID
     */
    public function findQuestionsForTextBlocks(EloquentCollection $blocks, int $limitPerBlock = 10): array
    {
        $results = [];

        foreach ($blocks as $block) {
            $results[$block->id] = $this->findQuestionsForTextBlock($block, $limitPerBlock);
        }

        return $results;
    }
}
