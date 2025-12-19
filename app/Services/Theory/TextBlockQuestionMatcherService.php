<?php

namespace App\Services\Theory;

use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use Illuminate\Support\Collection;

class TextBlockQuestionMatcherService
{
    private const GENERAL_TAG_CATEGORIES = [
        'grammar',
        'theory',
        'types of questions',
        'question forms',
    ];

    private const GENERAL_TAG_NAMES = [
        'grammar',
        'theory',
        'types of questions',
        'question forms',
    ];

    private const TOP_POOL_SIZE = 30;

    private const MIN_SCORE_THRESHOLD = 1.6;

    private ?Collection $candidateQuestions = null;

    /** @var array<int, int> */
    private array $tagFrequencies = [];

    /** @var array<int, float> */
    private array $tagIdfScores = [];

    private int $totalCandidates = 0;

    public function primeCandidatesForBlocks(Collection $blocks): void
    {
        if ($this->candidateQuestions !== null) {
            return;
        }

        $blocks->loadMissing('tags:id,name,category');

        $tagIds = $blocks
            ->flatMap(fn (TextBlock $block) => $block->tags->pluck('id'))
            ->unique()
            ->values()
            ->all();

        if (empty($tagIds)) {
            $this->candidateQuestions = collect();

            return;
        }

        $this->candidateQuestions = Question::query()
            ->with([
                'tags:id,name,category',
                'category:id,name',
                'options:id,option',
                'answers:id,question_id,marker,option_id',
                'answers.option:id,option',
            ])
            ->whereHas('tags', fn ($query) => $query->whereIn('tags.id', $tagIds))
            ->get();

        $this->totalCandidates = $this->candidateQuestions->count();

        $this->tagFrequencies = array_fill_keys($tagIds, 0);

        foreach ($this->candidateQuestions as $question) {
            $questionTagIds = $question->tags
                ->pluck('id')
                ->intersect($tagIds)
                ->unique();

            foreach ($questionTagIds as $tagId) {
                $this->tagFrequencies[$tagId] = ($this->tagFrequencies[$tagId] ?? 0) + 1;
            }
        }

        foreach ($this->tagFrequencies as $tagId => $frequency) {
            $this->tagIdfScores[$tagId] = log(($this->totalCandidates + 1) / ($frequency + 1)) + 1;
        }
    }

    public function findQuestionsForTextBlock(TextBlock $block, int $limit = 5, ?int $excludeQuestionId = null): Collection
    {
        $excludeIds = $excludeQuestionId ? [$excludeQuestionId] : [];

        return $this->findQuestionsForTextBlockWithExclusions($block, $limit, $excludeIds);
    }

    public function findQuestionsForTextBlockWithExclusions(TextBlock $block, int $limit = 5, array $excludeQuestionIds = []): Collection
    {
        if ($limit <= 0) {
            return collect();
        }

        $block->loadMissing('tags:id,name,category');

        if ($block->tags->isEmpty()) {
            return collect();
        }

        if ($this->candidateQuestions === null) {
            $this->primeCandidatesForBlocks(collect([$block]));
        }

        if ($this->candidateQuestions->isEmpty()) {
            return collect();
        }

        $blockTagIds = $block->tags->pluck('id')->all();
        $excludeLookup = array_flip($excludeQuestionIds);

        $scored = [];

        foreach ($this->candidateQuestions as $question) {
            if (isset($excludeLookup[$question->id])) {
                continue;
            }

            $matchedTagIds = array_values(array_intersect($blockTagIds, $question->tags->pluck('id')->all()));

            if (empty($matchedTagIds)) {
                continue;
            }

            $score = 0.0;
            $matchedTags = [];

            foreach ($matchedTagIds as $tagId) {
                $tag = $question->tags->firstWhere('id', $tagId);
                if (! $tag) {
                    continue;
                }

                $weight = $this->tagWeight($tag);
                $idf = $this->tagIdfScores[$tagId] ?? 1.0;
                $score += $weight * $idf;
                $matchedTags[] = $tag;
            }

            if ($this->isWeakMatch($matchedTags, $score)) {
                continue;
            }

            $score += max(0, count($matchedTags) - 1) * 0.2;

            $scored[] = [
                'question' => $question,
                'score' => $score,
            ];
        }

        if (empty($scored)) {
            return collect();
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $topPool = collect(array_slice($scored, 0, self::TOP_POOL_SIZE));

        return $this->weightedRandomSample($topPool, $limit)
            ->map(fn ($item) => $item['question'])
            ->values();
    }

    /**
     * @param  Collection<int, array{question: Question, score: float}>  $items
     * @return Collection<int, array{question: Question, score: float}>
     */
    private function weightedRandomSample(Collection $items, int $limit): Collection
    {
        $pool = $items->values()->all();
        $selected = collect();
        $limit = min($limit, count($pool));

        for ($i = 0; $i < $limit; $i++) {
            $totalWeight = 0.0;
            foreach ($pool as $item) {
                $totalWeight += max($item['score'], 0.1);
            }

            if ($totalWeight <= 0) {
                break;
            }

            $rand = (mt_rand() / mt_getrandmax()) * $totalWeight;

            foreach ($pool as $index => $item) {
                $rand -= max($item['score'], 0.1);
                if ($rand <= 0) {
                    $selected->push($item);
                    array_splice($pool, $index, 1);
                    break;
                }
            }
        }

        return $selected;
    }

    private function tagWeight(Tag $tag): float
    {
        $category = strtolower(trim((string) $tag->category));
        $name = strtolower(trim($tag->name));

        if (in_array($category, self::GENERAL_TAG_CATEGORIES, true) || in_array($name, self::GENERAL_TAG_NAMES, true)) {
            return 0.6;
        }

        return 1.4;
    }

    /**
     * @param  array<int, Tag>  $matchedTags
     */
    private function isWeakMatch(array $matchedTags, float $score): bool
    {
        if (count($matchedTags) === 1 && $this->isGeneralTag($matchedTags[0])) {
            return true;
        }

        return count($matchedTags) < 2 && $score < self::MIN_SCORE_THRESHOLD;
    }

    private function isGeneralTag(Tag $tag): bool
    {
        $category = strtolower(trim((string) $tag->category));
        $name = strtolower(trim($tag->name));

        return in_array($category, self::GENERAL_TAG_CATEGORIES, true)
            || in_array($name, self::GENERAL_TAG_NAMES, true);
    }
}
