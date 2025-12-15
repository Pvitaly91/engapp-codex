<?php

namespace App\Services\Theory;

use App\Models\Question;
use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class TheoryQuestionMatcherService
{
    /**
     * CEFR level tags for bonus scoring.
     */
    private const CEFR_TAGS = ['CEFR A1', 'CEFR A2', 'CEFR B1', 'CEFR B2', 'CEFR C1', 'CEFR C2', 'A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

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

        $blockTagNames = $block->tags->pluck('name')->map(fn ($n) => strtolower(trim($n)))->toArray();

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
            $questionTagNames = $question->tags->pluck('name')->map(fn ($n) => strtolower(trim($n)))->toArray();

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

        $questionTagNames = $question->tags->pluck('name')->map(fn ($n) => strtolower(trim($n)))->toArray();

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
            $blockTagNames = $block->tags->pluck('name')->map(fn ($n) => strtolower(trim($n)))->toArray();

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
     * Calculate CEFR level bonus for matched tags.
     * Having matching CEFR tags indicates level-appropriate content.
     *
     * @param  array<string>  $matchedTags
     * @return int
     */
    private function calculateCefrBonus(array $matchedTags): int
    {
        $bonus = 0;

        foreach ($matchedTags as $tag) {
            $normalizedTag = strtolower(trim($tag));

            foreach (self::CEFR_TAGS as $cefrTag) {
                if (strtolower($cefrTag) === $normalizedTag) {
                    $bonus += 2; // Add 2 points for each matching CEFR tag
                    break;
                }
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
