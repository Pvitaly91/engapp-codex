<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarkerTheoryMatcherService
{
    /**
     * Tag name that should not be the sole matching criterion.
     */
    private const TYPES_OF_QUESTIONS_TAG = 'types-of-questions';

    /**
     * Find the best matching theory block for a specific marker in a question.
     *
     * @param  int  $questionId  The question ID
     * @param  string  $marker  The marker name (e.g., 'a1', 'a2')
     * @return array|null Returns theory_block data or null if no match
     */
    public function findTheoryBlockForMarker(int $questionId, string $marker): ?array
    {
        $markerTags = $this->getMarkerTags($questionId, $marker);

        if ($markerTags->isEmpty()) {
            return null;
        }

        $normalizedTags = $this->normalizeTags($markerTags->pluck('name')->toArray());

        if (empty($normalizedTags)) {
            return null;
        }

        $matchedBlock = $this->findBestMatchingTextBlock($normalizedTags);

        if (! $matchedBlock) {
            return null;
        }

        return [
            'uuid' => $matchedBlock['block']->uuid,
            'type' => $matchedBlock['block']->type,
            'body' => $matchedBlock['block']->body,
            'level' => $matchedBlock['block']->level,
            'matched_tags' => $matchedBlock['matched_tags'],
            'score' => $matchedBlock['score'],
            'marker' => $marker,
        ];
    }

    /**
     * Get tags associated with a specific marker for a question.
     *
     * @return Collection<Tag>
     */
    public function getMarkerTags(int $questionId, string $marker): Collection
    {
        if (! Schema::hasTable('question_marker_tag')) {
            return collect();
        }

        return Tag::query()
            ->join('question_marker_tag', 'tags.id', '=', 'question_marker_tag.tag_id')
            ->where('question_marker_tag.question_id', $questionId)
            ->where('question_marker_tag.marker', $marker)
            ->select('tags.*')
            ->get();
    }

    /**
     * Find the best matching text block based on tag intersection.
     *
     * @param  array  $normalizedTags  Normalized tag names
     * @return array|null Array with 'block', 'matched_tags', 'score' or null
     */
    private function findBestMatchingTextBlock(array $normalizedTags): ?array
    {
        if (empty($normalizedTags)) {
            return null;
        }

        $textBlocks = $this->loadCandidateTheoryBlocks();

        if ($textBlocks->isEmpty()) {
            return null;
        }

        $bestMatch = null;
        $bestScore = 0;

        foreach ($textBlocks as $textBlock) {
            $blockTags = $this->normalizeTags(
                $textBlock->tags->pluck('name')->toArray()
            );

            $matched = array_values(array_intersect($normalizedTags, $blockTags));
            $score = count($matched);

            if ($score < 1) {
                continue;
            }

            // Filter out matches where the only common tag is 'types-of-questions'
            $hasOtherTag = collect($matched)->contains(
                fn ($tag) => $tag !== self::TYPES_OF_QUESTIONS_TAG
            );

            if (! $hasOtherTag) {
                continue;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'block' => $textBlock,
                    'matched_tags' => $matched,
                    'score' => $score,
                ];
            }
        }

        return $bestMatch;
    }

    /**
     * Load candidate theory blocks for matching.
     *
     * @return \Illuminate\Database\Eloquent\Collection<TextBlock>
     */
    private function loadCandidateTheoryBlocks()
    {
        if (! Schema::hasTable('text_blocks') || ! Schema::hasTable('tag_text_block')) {
            return collect();
        }

        return TextBlock::query()
            ->with('tags:id,name')
            ->whereHas('tags')
            ->get();
    }

    /**
     * Normalize tags to lowercase, trimmed, unique array.
     *
     * @param  array|string|null  $tags
     */
    private function normalizeTags($tags): array
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
     * Check if a question has marker tags for a specific marker.
     */
    public function hasMarkerTags(int $questionId, string $marker): bool
    {
        if (! Schema::hasTable('question_marker_tag')) {
            return false;
        }

        return DB::table('question_marker_tag')
            ->where('question_id', $questionId)
            ->where('marker', $marker)
            ->exists();
    }

    /**
     * Get all marker tags for a question grouped by marker.
     *
     * @return array<string, array<string>> Map of marker => tag names
     */
    public function getAllMarkerTags(int $questionId): array
    {
        if (! Schema::hasTable('question_marker_tag')) {
            return [];
        }

        $rows = DB::table('question_marker_tag')
            ->join('tags', 'question_marker_tag.tag_id', '=', 'tags.id')
            ->where('question_marker_tag.question_id', $questionId)
            ->select('question_marker_tag.marker', 'tags.name')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            if (! isset($result[$row->marker])) {
                $result[$row->marker] = [];
            }
            $result[$row->marker][] = $row->name;
        }

        return $result;
    }
}
