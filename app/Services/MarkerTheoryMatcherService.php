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
     * Minimum weighted score required for a valid match.
     */
    private const MIN_SCORE_THRESHOLD = 3;

    /**
     * Tag weight categories for weighted scoring.
     * Higher weights give priority to more specific/detailed tag matches.
     *
     * Weight tiers:
     * - Theme/Section tags (general): weight 1
     * - Subtopic/Forms tags: weight 2
     * - Detail tags (question types): weight 3
     * - Grammar feature tags (auxiliaries, tenses): weight 4
     */
    private const TAG_WEIGHTS = [
        // General topic/section tags (weight 1)
        'types of questions' => 1,
        'question forms' => 1,
        'grammar' => 1,
        'theory' => 1,

        // Subtopic/Forms tags (weight 2)
        'introduction' => 2,
        'overview' => 2,
        'definition' => 2,
        'summary' => 2,
        'comparison' => 2,
        'common mistakes' => 2,
        'practice' => 2,
        'exercises' => 2,

        // Detail tags - question types (weight 3)
        'yes/no questions' => 3,
        'general questions' => 3,
        'wh-questions' => 3,
        'special questions' => 3,
        'subject questions' => 3,
        'indirect questions' => 3,
        'tag questions' => 3,
        'question tags' => 3,
        'disjunctive questions' => 3,
        'alternative questions' => 3,
        'choice questions' => 3,
        'negative questions' => 3,
        'negative question forms' => 3,
        'embedded questions' => 3,
        'question word order' => 3,
        'question formation' => 3,

        // Grammar feature tags - auxiliaries and tenses (weight 4)
        'do/does/did' => 4,
        'be (am/is/are/was/were)' => 4,
        'to be' => 4,
        'have/has/had' => 4,
        'modal verbs' => 4,
        'can/could' => 4,
        'will/would' => 4,
        'should' => 4,
        'must' => 4,
        'may/might' => 4,
        'auxiliaries' => 4,
        'inversion' => 4,

        // Tense tags (weight 4)
        'present simple' => 4,
        'past simple' => 4,
        'future simple' => 4,
        'present continuous' => 4,
        'past continuous' => 4,
        'present perfect' => 4,
        'past perfect' => 4,
        'present perfect continuous' => 4,
        'past perfect continuous' => 4,

        // CEFR level tags (weight 2 - bonus for level match)
        'cefr a1' => 2,
        'cefr a2' => 2,
        'cefr b1' => 2,
        'cefr b2' => 2,
        'cefr c1' => 2,
        'cefr c2' => 2,
    ];

    /**
     * Default weight for tags not in the weight map.
     */
    private const DEFAULT_TAG_WEIGHT = 2;

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
     * Find the best matching text block based on weighted tag intersection.
     *
     * The algorithm:
     * 1. Find all text blocks with tag intersection
     * 2. Calculate weighted score for each match
     * 3. Prioritize blocks with more specific/detailed tag matches
     * 4. Return block with highest score above threshold
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

            if (empty($matched)) {
                continue;
            }

            // Filter out matches where the only common tag is 'types-of-questions'
            $hasOtherTag = collect($matched)->contains(
                fn ($tag) => $tag !== self::TYPES_OF_QUESTIONS_TAG
            );

            if (! $hasOtherTag) {
                continue;
            }

            // Calculate weighted score
            $score = $this->calculateWeightedScore($matched);

            // Apply minimum threshold check
            if ($score < self::MIN_SCORE_THRESHOLD) {
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
     * Calculate weighted score for a set of matched tags.
     *
     * @param  array  $matchedTags  Normalized matched tag names
     * @return float Total weighted score
     */
    private function calculateWeightedScore(array $matchedTags): float
    {
        $score = 0;

        foreach ($matchedTags as $tag) {
            $weight = self::TAG_WEIGHTS[$tag] ?? self::DEFAULT_TAG_WEIGHT;
            $score += $weight;
        }

        return $score;
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
