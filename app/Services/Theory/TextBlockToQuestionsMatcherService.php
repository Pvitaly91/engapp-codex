<?php

namespace App\Services\Theory;

use App\Models\Question;
use App\Models\TextBlock;
use App\Services\Theory\TagMatch\TagMatchScorer;
use Database\Seeders\Ai\Claude\QuestionsDifferentTypesClaudeSeeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TextBlockToQuestionsMatcherService
{
    private const TOP_POOL_SIZE = 30;

    private const PREFERRED_SEEDER_BONUS = 0.5;

    public function __construct(private TagMatchScorer $tagMatchScorer)
    {
    }

    public function findBestQuestionsForTextBlock(TextBlock $block, int $limit = 5, array $excludeQuestionIds = []): Collection
    {
        if ($limit <= 0) {
            return collect();
        }

        $scored = $this->scoreQuestionCandidatesForTextBlock($block, $excludeQuestionIds);

        if ($scored->isEmpty()) {
            return collect();
        }

        $topPool = $scored
            ->sortByDesc('score')
            ->take(self::TOP_POOL_SIZE)
            ->values();

        return $this->weightedRandomSample($topPool, $limit)
            ->map(fn (array $item) => $this->decorateQuestion($item))
            ->values();
    }

    /**
     * @return Collection<int, array{question: Question, score: float, matched_tag_ids: array<int>, matched_tag_names: array<int, string>, matched_tags: array<int, string>, detailed_matches: int, general_matches: int}>
     */
    public function scoreQuestionCandidatesForTextBlock(TextBlock $block, array $excludeQuestionIds = []): Collection
    {
        if (! Schema::hasTable('question_tag') || ! Schema::hasTable('tags')) {
            return collect();
        }

        $block->loadMissing(['tags:id,name', 'page.category:id,slug']);

        if ($block->tags->isEmpty()) {
            return collect();
        }

        $blockTagIds = $block->tags->pluck('id')->all();

        $query = Question::query()
            ->with([
                'tags:id,name',
                'category:id,name',
                'options:id,option',
                'answers:id,question_id,marker,option_id',
                'answers.option:id,option',
            ])
            ->whereHas('tags', fn ($query) => $query->whereIn('tags.id', $blockTagIds));

        if (! empty($excludeQuestionIds)) {
            $query->whereNotIn('id', $excludeQuestionIds);
        }

        $questions = $query->get();

        if ($questions->isEmpty()) {
            return collect();
        }

        $scored = [];

        foreach ($questions as $question) {
            $scoreData = $this->tagMatchScorer->scoreMatch($block->tags, $question->tags);

            if ($scoreData['skip']) {
                continue;
            }

            $score = $scoreData['score'];

            if ($this->shouldPrioritizeSeeder($block, $question)) {
                $score += self::PREFERRED_SEEDER_BONUS;
            }

            $scored[] = [
                'question' => $question,
                'score' => $score,
                'matched_tag_ids' => $scoreData['matched_tag_ids'],
                'matched_tag_names' => $scoreData['matched_tag_names'],
                'matched_tags' => $scoreData['matched_tags'],
                'detailed_matches' => $scoreData['detailed_matches'],
                'general_matches' => $scoreData['general_matches'],
            ];
        }

        return collect($scored);
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

    private function decorateQuestion(array $item): Question
    {
        $question = $item['question'];
        $question->setAttribute('match_score', $item['score']);
        $question->setAttribute('matched_tag_ids', $item['matched_tag_ids'] ?? []);
        $question->setAttribute('matched_tag_names', $item['matched_tag_names'] ?? []);

        return $question;
    }

    private function shouldPrioritizeSeeder(TextBlock $block, Question $question): bool
    {
        $categorySlug = $block->page?->category?->slug;

        if ($categorySlug !== 'types-of-questions') {
            return false;
        }

        return $question->seeder === QuestionsDifferentTypesClaudeSeeder::class;
    }
}
