<?php

namespace App\Services\Theory;

use App\Models\Question;
use App\Models\TextBlock;
use App\Services\Theory\TagMatch\TagMatchScorer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TextBlockToQuestionsMatcherService
{
    public function __construct(private TagMatchScorer $tagMatchScorer) {}

    /**
     * Find the best matching questions for a specific text block using the same
     * tag-based logic as marker → theory matching.
     */
    public function findBestQuestionsForTextBlock(
        TextBlock $block,
        int $limit = 5,
        array $excludeQuestionIds = []
    ): Collection {
        if (! $block->relationLoaded('tags')) {
            $block->load('tags:id,name');
        }

        $blockTagIds = $block->tags->pluck('id')->toArray();

        if (empty($blockTagIds)) {
            return collect();
        }

        $candidates = $this->loadCandidateQuestions($blockTagIds, $excludeQuestionIds);

        if ($candidates->isEmpty()) {
            return collect();
        }

        $scored = [];
        $blockTags = $block->tags;

        foreach ($candidates as $question) {
            $score = $this->tagMatchScorer->scoreCollections($blockTags, $question->tags);

            if ($score['skip']) {
                continue;
            }

            $finalScore = $this->applyPriorityBonuses($blockTags, $question, $score['score']);

            $scored[] = [
                'question' => $question,
                'score' => round($finalScore, 2),
                'matched_tag_ids' => $score['matched_tag_ids'],
                'matched_normalized_tags' => $score['matched_normalized_tags'],
            ];
        }

        if (empty($scored)) {
            return collect();
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $topCandidates = array_slice($scored, 0, 30);
        $selected = $this->weightedRandomSelection($topCandidates, $limit);

        return collect($selected)->map(function ($item) {
            $question = $item['question'];
            $question->setAttribute('match_score', $item['score']);
            $question->setAttribute('matched_tag_ids', $item['matched_tag_ids']);

            return $question;
        });
    }

    /**
     * Batch matcher for multiple blocks with duplicate avoidance.
     *
     * @param  EloquentCollection<TextBlock>  $blocks
     * @return array<string, Collection<Question>>
     */
    public function findQuestionsForTextBlocks(EloquentCollection $blocks, int $limitPerBlock = 5): array
    {
        $results = [];
        $usedQuestionIds = [];

        $blocks->load('tags:id,name');

        foreach ($blocks as $block) {
            if ($block->tags->isEmpty()) {
                continue;
            }

            $questions = $this->findBestQuestionsForTextBlock($block, $limitPerBlock, $usedQuestionIds);

            if ($questions->isNotEmpty()) {
                $results[$block->uuid] = $questions;
                $usedQuestionIds = array_merge($usedQuestionIds, $questions->pluck('id')->toArray());
            }
        }

        return $results;
    }

    /**
     * Load candidate questions that share at least one tag with the block.
     *
     * @param  array<int>  $blockTagIds
     * @param  array<int>  $excludeQuestionIds
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
            })
            ->limit(200);

        if (! empty($excludeQuestionIds)) {
            $query->whereNotIn('id', $excludeQuestionIds);
        }

        return $query->get();
    }

    private function weightedRandomSelection(array $candidates, int $limit): array
    {
        if (count($candidates) <= $limit) {
            return $candidates;
        }

        $totalWeight = array_sum(array_column($candidates, 'score'));

        if ($totalWeight <= 0) {
            shuffle($candidates);

            return array_slice($candidates, 0, $limit);
        }

        $selected = [];
        $selectedIndices = [];

        for ($i = 0; $i < $limit && count($candidates) > count($selected); $i++) {
            $random = mt_rand() / mt_getrandmax() * $totalWeight;
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

    private function applyPriorityBonuses(Collection $blockTags, Question $question, float $baseScore): float
    {
        if (! Schema::hasColumn('questions', 'seeder')) {
            return $baseScore;
        }

        $normalizedBlockTags = $this->tagMatchScorer->normalizeTags($blockTags->pluck('name')->toArray());

        $isTypesOfQuestions = in_array('types-of-questions', $normalizedBlockTags, true);
        $fromReferenceSeeder = $question->seeder
            && str_contains((string) $question->seeder, 'QuestionsDifferentTypesClaudeSeeder');

        if ($isTypesOfQuestions && $fromReferenceSeeder) {
            return $baseScore + 0.75;
        }

        return $baseScore;
    }
}

