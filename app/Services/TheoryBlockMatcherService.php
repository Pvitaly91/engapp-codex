<?php

namespace App\Services;

use App\Models\PageCategory;
use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TheoryBlockMatcherService
{
    /**
     * Normalize tags to lowercase, trimmed, unique array.
     *
     * @param  array|string|null  $tags
     * @return array
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
            ->map(fn ($tag) => strtolower(trim($tag)))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Load candidate theory blocks for matching.
     *
     * Loads text blocks from QuestionsNegations/TypesOfQuestions categories,
     * or fallback to blocks with 'types-of-questions' tag.
     *
     * @return EloquentCollection<TextBlock>
     */
    public function loadCandidateTheoryBlocks(): EloquentCollection
    {
        // First, try to find blocks under specific page categories
        $categoryIds = $this->findTheoryCategoryIds();

        $query = TextBlock::query()
            ->with(['tags:id,name', 'page:id,slug,title,page_category_id', 'page.category:id,slug,title']);

        if ($categoryIds->isNotEmpty()) {
            $query->whereIn('page_category_id', $categoryIds);
        } else {
            // Fallback: load blocks where tags contain 'types-of-questions'
            $query->whereHas('tags', fn ($q) => $q->where('name', 'types-of-questions'));
        }

        return $query->get();
    }

    /**
     * Find category IDs for QuestionsNegations/TypesOfQuestions.
     *
     * @return Collection<int>
     */
    private function findTheoryCategoryIds(): Collection
    {
        if (! Schema::hasTable('page_categories')) {
            return collect();
        }

        return PageCategory::query()
            ->where(function ($q) {
                $q->where('slug', 'like', '%questions-negations%')
                    ->orWhere('slug', 'like', '%types-of-questions%');
            })
            ->pluck('id');
    }

    /**
     * Match theory blocks to questions based on tag intersection.
     *
     * @param  Collection|array  $questions  Questions with loaded tags relation
     * @param  EloquentCollection  $blocks  Theory blocks with loaded tags relation
     * @param  int  $limit  Maximum matches per question
     * @return array<int, array>  Keyed by question ID
     */
    public function matchTheoryBlocksForQuestions($questions, EloquentCollection $blocks, int $limit = 5): array
    {
        $result = [];

        // Pre-normalize all block tags
        $blocksWithNormalizedTags = $blocks->map(function (TextBlock $block) {
            $tagNames = $block->tags->pluck('name')->all();
            $normalized = $this->normalizeTags($tagNames);

            return [
                'block' => $block,
                'tags' => $normalized,
            ];
        });

        foreach ($questions as $question) {
            $questionTags = $question->relationLoaded('tags')
                ? $question->tags->pluck('name')->all()
                : [];

            $qTags = $this->normalizeTags($questionTags);

            if (empty($qTags)) {
                $result[$question->id] = [
                    'tags_normalized' => [],
                    'matched_theory' => [],
                ];
                continue;
            }

            $matches = [];

            foreach ($blocksWithNormalizedTags as $item) {
                $bTags = $item['tags'];
                $matched = array_values(array_intersect($qTags, $bTags));
                $score = count($matched);

                // Valid match rule: score >= 1 AND at least one tag other than 'types-of-questions'
                if ($score < 1) {
                    continue;
                }

                $hasOtherTag = collect($matched)->contains(fn ($tag) => $tag !== 'types-of-questions');

                if (! $hasOtherTag) {
                    continue;
                }

                /** @var TextBlock $block */
                $block = $item['block'];

                $matches[] = [
                    'block_id' => $block->id,
                    'score' => $score,
                    'matched_tags' => $matched,
                    'block_tags' => $bTags,
                    'title' => $block->heading ?: null,
                    'snippet' => $this->makeSnippet($block->body, 200),
                    'source_page' => $block->page ? [
                        'id' => $block->page->id,
                        'slug' => $block->page->slug,
                        'title' => $block->page->title,
                    ] : null,
                    'page_category' => $block->page && $block->page->category ? [
                        'id' => $block->page->category->id,
                        'slug' => $block->page->category->slug,
                        'title' => $block->page->category->title,
                    ] : null,
                    'admin_link' => $this->buildAdminLink($block),
                ];
            }

            // Sort by score DESC
            usort($matches, fn ($a, $b) => $b['score'] <=> $a['score']);

            // Limit results
            $matches = array_slice($matches, 0, $limit);

            $result[$question->id] = [
                'tags_normalized' => $qTags,
                'matched_theory' => $matches,
            ];
        }

        return $result;
    }

    /**
     * Create a text snippet from content.
     *
     * @param  string|null  $content
     * @param  int  $maxLength
     * @return string
     */
    private function makeSnippet(?string $content, int $maxLength = 200): string
    {
        if ($content === null || trim($content) === '') {
            return '';
        }

        // Strip HTML tags
        $text = strip_tags($content);

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return mb_substr($text, 0, $maxLength) . '…';
    }

    /**
     * Build admin link for the text block if applicable.
     *
     * @param  TextBlock  $block
     * @return string|null
     */
    private function buildAdminLink(TextBlock $block): ?string
    {
        // If page relation is loaded, try to build a link
        if ($block->page && $block->page->slug && $block->page->category) {
            try {
                return route('pages.show', [
                    'category' => $block->page->category->slug,
                    'pageSlug' => $block->page->slug,
                ]);
            } catch (\Throwable $e) {
                // Route might not exist
            }
        }

        return null;
    }
}
