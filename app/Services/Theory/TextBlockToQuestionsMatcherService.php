<?php

namespace App\Services\Theory;

use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Services\Theory\TagMatch\TagMatchScorer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TextBlockToQuestionsMatcherService
{
    private const ANCHOR_TAGS = [
        'types-of-questions',
        'yes-no-questions',
        'wh-questions',
        'tag-questions',
        'question-tags',
        'indirect-questions',
        'embedded-questions',
        'negative-questions',
        'subject-questions',
        'alternative-questions',
        'question-formation',
    ];

    private const DETAIL_TAGS = [
        'can-could',
        'will-would',
        'may-might',
        'must',
        'should',
        'do-does-did',
        'have-has-had',
        'to-be',
        'be-am-is-are-was-were',
        'auxiliaries',
        'modal-verbs',
        'present-simple',
        'past-simple',
        'future-simple',
        'present-continuous',
        'past-continuous',
        'question-word-order',
        'statement-order',
    ];

    private const CATEGORY_ANCHOR_KEYWORDS = ['question', 'type'];
    private const CATEGORY_DETAIL_KEYWORDS = ['aux', 'modal', 'tense', 'verb', 'form'];

    public function __construct(private TagMatchScorer $tagMatchScorer) {}

    public function findQuestionsForTextBlock(TextBlock $block, int $limit = 5, array $excludeQuestionIds = []): Collection
    {
        $matchData = $this->buildMatchData($block);

        if (empty($matchData['anchorTagIds'])) {
            return collect();
        }

        $candidates = $this->loadCandidateQuestions(
            $matchData['anchorTagIds'],
            $matchData['detailTagIds'],
            $excludeQuestionIds,
            $block->level
        );

        if ($candidates->isEmpty()) {
            return collect();
        }

        $scored = $this->scoreCandidates($matchData, $candidates);

        if ($scored->isEmpty()) {
            return collect();
        }

        $topCandidates = $scored->sortByDesc('score')->take(30)->values()->all();
        $selected = $this->weightedRandomSelection($topCandidates, $limit);

        return collect($selected)->map(function ($item) {
            /** @var Question $question */
            $question = $item['question'];
            $question->setAttribute('match_score', $item['score']);
            $question->setAttribute('matched_tag_ids', $item['matched_tag_ids']);
            $question->setAttribute('marker_tags', $this->groupMarkerTags($question));

            return $question;
        });
    }

    /**
     * @param  EloquentCollection<int, TextBlock>  $blocks
     * @return array<string, Collection<Question>>
     */
    public function findQuestionsForTextBlocks(EloquentCollection $blocks, int $limitPerBlock = 5): array
    {
        $results = [];
        $usedQuestionIds = [];

        $blocks->load('tags:id,name,category');

        foreach ($blocks as $block) {
            if ($block->tags->isEmpty()) {
                continue;
            }

            $questions = $this->findQuestionsForTextBlock($block, $limitPerBlock, $usedQuestionIds);

            if ($questions->isNotEmpty()) {
                $results[$block->uuid] = $questions;
                $usedQuestionIds = array_merge($usedQuestionIds, $questions->pluck('id')->toArray());
            }
        }

        return $results;
    }

    public function debugMatches(TextBlock $block, int $limit = 10, array $excludeQuestionIds = []): array
    {
        $matchData = $this->buildMatchData($block);

        $candidatesAfterAnchors = $this->loadCandidateQuestions(
            $matchData['anchorTagIds'],
            [],
            $excludeQuestionIds,
            $block->level
        );

        $candidatesAfterDetails = $matchData['detailTagIds']
            ? $this->loadCandidateQuestions(
                $matchData['anchorTagIds'],
                $matchData['detailTagIds'],
                $excludeQuestionIds,
                $block->level
            )
            : $candidatesAfterAnchors;

        $scored = $this->scoreCandidates($matchData, $candidatesAfterDetails)->sortByDesc('score')->take($limit);

        $questions = $scored->map(function ($item) {
            /** @var Question $q */
            $q = $item['question'];

            return [
                'id' => $q->id,
                'question' => $q->question,
                'score' => $item['score'],
                'matched_tags' => $item['matched_normalized_tags'],
            ];
        });

        return [
            'anchor_tags' => $matchData['anchorTagNames'],
            'detail_tags' => $matchData['detailTagNames'],
            'candidates_after_anchors' => $candidatesAfterAnchors->count(),
            'candidates_after_details' => $candidatesAfterDetails->count(),
            'questions' => $questions,
        ];
    }

    private function buildMatchData(TextBlock $block): array
    {
        if (! $block->relationLoaded('tags')) {
            $block->load('tags:id,name,category');
        }

        $tags = $block->tags;

        if ($tags->isEmpty()) {
            return [
                'anchorTagIds' => [],
                'detailTagIds' => [],
                'anchorTagNames' => [],
                'detailTagNames' => [],
                'blockNormalized' => [],
                'detailNormalized' => [],
            ];
        }

        $normalizedMap = $tags->mapWithKeys(function (Tag $tag) {
            $normalized = $this->tagMatchScorer->normalizeTags([$tag->name]);
            $normalizedName = $normalized[0] ?? $this->tagMatchScorer->normalizeTagName($tag->name);

            return [$tag->id => [
                'id' => $tag->id,
                'name' => $tag->name,
                'category' => $tag->category,
                'normalized' => $normalizedName,
            ]];
        });

        $anchorTagIds = [];
        $detailTagIds = [];
        $anchorTagNames = [];
        $detailTagNames = [];
        $detailNormalized = [];
        $blockNormalized = [];

        foreach ($normalizedMap as $data) {
            $blockNormalized[] = $data['normalized'];

            if ($this->isAnchorTag($data['normalized'], $data['category'])) {
                $anchorTagIds[] = $data['id'];
                $anchorTagNames[] = $data['name'];
                continue;
            }

            if ($this->isDetailTag($data['normalized'], $data['category'])) {
                $detailTagIds[] = $data['id'];
                $detailTagNames[] = $data['name'];
                $detailNormalized[] = $data['normalized'];
            }
        }

        return [
            'anchorTagIds' => array_values(array_unique($anchorTagIds)),
            'detailTagIds' => array_values(array_unique($detailTagIds)),
            'anchorTagNames' => array_values(array_unique($anchorTagNames)),
            'detailTagNames' => array_values(array_unique($detailTagNames)),
            'blockNormalized' => array_values(array_filter(array_unique($blockNormalized))),
            'detailNormalized' => array_values(array_filter(array_unique($detailNormalized))),
            'blockTags' => $tags,
        ];
    }

    private function isAnchorTag(string $normalized, ?string $category): bool
    {
        $category = strtolower((string) $category);

        if (in_array($normalized, self::ANCHOR_TAGS, true)) {
            return true;
        }

        foreach (self::CATEGORY_ANCHOR_KEYWORDS as $keyword) {
            if (str_contains($category, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function isDetailTag(string $normalized, ?string $category): bool
    {
        $category = strtolower((string) $category);

        if (in_array($normalized, self::DETAIL_TAGS, true)) {
            return true;
        }

        foreach (self::CATEGORY_DETAIL_KEYWORDS as $keyword) {
            if (str_contains($category, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function loadCandidateQuestions(
        array $anchorTagIds,
        array $detailTagIds,
        array $excludeQuestionIds,
        ?string $blockLevel
    ): EloquentCollection {
        if (empty($anchorTagIds) || ! Schema::hasTable('questions') || ! Schema::hasTable('question_tag')) {
            return new EloquentCollection();
        }

        $query = Question::query()
            ->with([
                'tags:id,name',
                'options',
                'answers.option',
                'verbHints.option',
                'hints',
                'markerTags:id,name,category',
            ])
            ->where(function ($q) use ($anchorTagIds) {
                foreach ($anchorTagIds as $anchorId) {
                    $q->whereHas('tags', fn ($tagQuery) => $tagQuery->where('tags.id', $anchorId));
                }
            });

        if (! empty($detailTagIds)) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $detailTagIds));
        }

        if (! empty($excludeQuestionIds)) {
            $query->whereNotIn('id', $excludeQuestionIds);
        }

        if ($blockLevel && Schema::hasColumn('questions', 'level')) {
            $query->where('level', $blockLevel);
        }

        return $query->limit(200)->get();
    }

    private function scoreCandidates(array $matchData, EloquentCollection $candidates): Collection
    {
        $blockNormalized = $matchData['blockNormalized'];
        $detailNormalized = $matchData['detailNormalized'];
        /** @var Collection<int, Tag> $blockTags */
        $blockTags = $matchData['blockTags'] ?? collect();

        return $candidates->map(function (Question $question) use ($blockNormalized, $detailNormalized, $blockTags) {
            $questionNormalized = $this->tagMatchScorer->normalizeTags($question->tags->pluck('name')->toArray());

            $matchedNormalized = array_values(array_intersect($blockNormalized, $questionNormalized));

            if (empty($matchedNormalized)) {
                return null;
            }

            $score = 0;
            foreach ($matchedNormalized as $tag) {
                $score += in_array($tag, $detailNormalized, true) ? 2 : 1;
            }

            $matchedTagIds = $this->tagMatchScorer->calculateMatchedTagIds($matchedNormalized, $blockTags, $question->tags);

            return [
                'question' => $question,
                'score' => $score,
                'matched_tag_ids' => $matchedTagIds,
                'matched_normalized_tags' => $matchedNormalized,
            ];
        })->filter();
    }

    private function groupMarkerTags(Question $question): array
    {
        if (! $question->relationLoaded('markerTags')) {
            $question->load('markerTags:id,name,category');
        }

        if (! $question->relationLoaded('answers')) {
            $question->load('answers');
        }

        $markers = $question->answers->pluck('marker')->filter()->unique();

        return $question->markerTags
            ->filter(fn ($tag) => $tag->pivot?->marker && $markers->contains($tag->pivot->marker))
            ->groupBy(fn ($tag) => $tag->pivot->marker)
            ->map(fn ($tags) => $tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'category' => $tag->category,
            ])->values()->toArray())
            ->toArray();
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
}

