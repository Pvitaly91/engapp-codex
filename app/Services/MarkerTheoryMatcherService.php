<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MarkerTheoryMatcherService
{
    /**
     * Tag name that should not be the sole matching criterion.
     */
    private const TYPES_OF_QUESTIONS_TAG = 'types-of-questions';

    private const GENERAL_TAGS = [
        'types-of-questions',
        'question-forms',
        'grammar',
        'theory',
        'question-sentences',
        'types-of-question-sentences',
        'question-formation-practice',
    ];

    /**
     * Tags that indicate introductory/overview content - these blocks should
     * only be returned when no better candidate exists.
     */
    private const INTRO_TAGS = [
        'introduction',
        'overview',
        'summary',
        'navigation',
        'index',
        'tips',
        'learning',
    ];

    /**
     * Block types that are considered intro/header blocks.
     */
    private const INTRO_BLOCK_TYPES = [
        'subtitle',
        'hero',
    ];

    private const DETAIL_FIRST_TAGS = [
        'tag-questions',
        'question-tags',
        'indirect-questions',
        'embedded-questions',
        'question-word-order',
        'statement-order',
        'subject-questions',
        'negative-questions',
        'alternative-questions',
        'wh-questions',
        'yes-no-questions',
        'special-questions',
        'question-formation',
        'be-ing',
    ];

    private const DETAIL_SECOND_TAGS = [
        'modal-verbs',
        'do-does-did',
        'to-be',
        'be-am-is-are-was-were',
        'present-simple',
        'past-simple',
        'future-simple',
        'present-continuous',
        'past-continuous',
        'present-perfect',
        'past-perfect',
        'present-perfect-continuous',
        'past-perfect-continuous',
        'auxiliaries',
        'have-has-had',
        'can-could',
        'will-would',
        'may-might',
    ];

    private const TAG_ALIASES = [
        'question-tags' => 'tag-questions',
        'question-word-order' => 'statement-order',
        // Map slash versions to dash versions for common auxiliary tags
        'do/does/did' => 'do-does-did',
        'can/could' => 'can-could',
        'will/would' => 'will-would',
        'may/might' => 'may-might',
        'have/has/had' => 'have-has-had',
    ];

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

        $block = $matchedBlock['block'];
        
        // Load page and category relationship for URL generation
        $block->load(['page.category']);
        
        $pageUrl = null;
        $blockAnchor = null;
        
        if ($block->page) {
            $blockAnchor = 'block-' . $block->id;
            if ($block->page->category) {
                $pageUrl = route('theory.show', [
                    'category' => $block->page->category->slug,
                    'pageSlug' => $block->page->slug,
                ]) . '#' . $blockAnchor;
            }
        }

        return [
            'uuid' => $block->uuid,
            'type' => $block->type,
            'body' => $block->body,
            'level' => $block->level,
            'matched_tags' => $matchedBlock['matched_tags'],
            'score' => $matchedBlock['score'],
            'marker' => $marker,
            'page_url' => $pageUrl,
            'block_anchor' => $blockAnchor,
            'page_title' => $block->page?->title,
        ];
    }

    /**
     * Debug-friendly matcher output with candidates.
     */
    public function debugMatches(int $questionId, string $marker, int $limit = 3): ?array
    {
        $markerTags = $this->getMarkerTags($questionId, $marker);

        if ($markerTags->isEmpty()) {
            return null;
        }

        $normalizedTags = $this->normalizeTags($markerTags->pluck('name')->toArray());

        if (empty($normalizedTags)) {
            return null;
        }

        // Debug matches need the page relationship for context display
        $result = $this->findBestMatchingTextBlock($normalizedTags, $limit, withPageRelation: true);

        if (! $result) {
            return null;
        }

        return $result;
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
     * Strategy:
     * 1. Score all candidates based on tag weights
     * 2. Penalize intro/subtitle blocks so they don't win over detailed blocks
     * 3. Skip blocks with only general tag matches
     * 4. Return the best non-intro block if one exists with good score
     *
     * @param  array  $normalizedTags  Normalized tag names
     * @return array|null Array with 'block', 'matched_tags', 'score' or null
     */
    private function findBestMatchingTextBlock(array $normalizedTags, int $candidateLimit = 1, bool $withPageRelation = false): ?array
    {
        if (empty($normalizedTags)) {
            return null;
        }

        $textBlocks = $this->loadCandidateTheoryBlocks($withPageRelation);

        if ($textBlocks->isEmpty()) {
            return null;
        }

        $bestMatch = null;
        $bestScore = 0;
        $candidates = [];
        $introCandidate = null;
        $introBestScore = 0;

        foreach ($textBlocks as $textBlock) {
            $blockTags = $this->normalizeTags(
                $textBlock->tags->pluck('name')->toArray()
            );

            $matched = array_values(array_intersect($normalizedTags, $blockTags));

            if (empty($matched)) {
                continue;
            }

            $scoring = $this->scoreMatchedTags($matched);

            if ($scoring['skip']) {
                continue;
            }

            // Check if this is an intro/overview block
            $isIntroBlock = $this->isIntroBlock($textBlock, $blockTags);

            $candidate = [
                'block' => $textBlock,
                'matched_tags' => $matched,
                'score' => $scoring['score'],
                'detailed_matches' => $scoring['detailed_matches'],
                'general_matches' => $scoring['general_matches'],
                'is_intro' => $isIntroBlock,
            ];

            $candidates[] = $candidate;

            // Separate handling for intro vs non-intro blocks
            if ($isIntroBlock) {
                // Track best intro block as a fallback
                if ($candidate['score'] > $introBestScore) {
                    $introBestScore = $candidate['score'];
                    $introCandidate = $candidate;
                }
            } else {
                // Non-intro blocks compete for best match
                if ($candidate['score'] > $bestScore
                    || ($candidate['score'] === $bestScore
                        && $candidate['detailed_matches'] > ($bestMatch['detailed_matches'] ?? 0)
                    )
                ) {
                    $bestScore = $candidate['score'];
                    $bestMatch = $candidate;
                }
            }
        }

        // If no non-intro block matched, fall back to intro block
        if (! $bestMatch && $introCandidate) {
            $bestMatch = $introCandidate;
            $bestScore = $introBestScore;
        }

        // If best match is still null, no valid candidates found
        if (! $bestMatch) {
            return null;
        }

        if ($candidateLimit > 0 && ! empty($candidates)) {
            // Sort candidates: non-intro first, then by score/detailed matches
            $candidates = collect($candidates)
                ->sortBy([
                    ['is_intro', 'asc'],  // Non-intro blocks first
                    ['score', 'desc'],
                    ['detailed_matches', 'desc'],
                ])
                ->take($candidateLimit)
                ->values()
                ->all();

            $bestMatch['candidates'] = $candidates;
        }

        return $bestMatch;
    }

    /**
     * Determine if a text block is an intro/overview block.
     *
     * Intro blocks are:
     * 1. Blocks with type 'subtitle' or 'hero'
     * 2. Blocks where majority of tags are intro tags (introduction, overview, etc.)
     *
     * @param  TextBlock  $textBlock
     * @param  array  $normalizedBlockTags
     */
    private function isIntroBlock(TextBlock $textBlock, array $normalizedBlockTags): bool
    {
        // Check block type
        if (in_array($textBlock->type, self::INTRO_BLOCK_TYPES, true)) {
            return true;
        }

        // Check if block has mostly intro tags
        $introTagCount = 0;
        foreach ($normalizedBlockTags as $tag) {
            if (in_array($tag, self::INTRO_TAGS, true)) {
                $introTagCount++;
            }
        }

        // If more than half of tags are intro tags, it's an intro block
        // Use integer arithmetic and strict greater than for true majority
        if (count($normalizedBlockTags) > 0 && $introTagCount * 2 > count($normalizedBlockTags)) {
            return true;
        }

        return false;
    }

    /**
     * Load candidate theory blocks for matching.
     *
     * @param  bool  $withPageRelation  Include page relationship (only needed for debugging)
     * @return \Illuminate\Database\Eloquent\Collection<TextBlock>
     */
    private function loadCandidateTheoryBlocks(bool $withPageRelation = false)
    {
        if (! Schema::hasTable('text_blocks') || ! Schema::hasTable('tag_text_block')) {
            return collect();
        }

        $relationships = ['tags:id,name'];

        if ($withPageRelation) {
            $relationships[] = 'page:id,title,slug';
        }

        return TextBlock::query()
            ->with($relationships)
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
            ->map(fn ($tag) => $this->normalizeTagName($tag))
            ->map(fn ($tag) => self::TAG_ALIASES[$tag] ?? $tag)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeTagName(string $tag): string
    {
        $slug = Str::slug(trim($tag));

        if ($slug === '') {
            return '';
        }

        // Preserve slashes that Str::slug would drop for patterns like Do/Does/Did
        if (str_contains($tag, '/')) {
            return str_replace(' ', '-', strtolower(trim($tag)));
        }

        return $slug;
    }

    private function scoreMatchedTags(array $matchedTags): array
    {
        $score = 0.0;
        $detailedMatches = 0;
        $generalMatches = 0;

        foreach ($matchedTags as $tag) {
            $weight = $this->weightForTag($tag);
            $score += $weight;

            if (in_array($tag, self::GENERAL_TAGS, true)) {
                $generalMatches++;
            } elseif ($weight >= 2.5) {
                $detailedMatches++;
            }
        }

        $score += count($matchedTags) * 0.25;

        $skip = $detailedMatches === 0 && count($matchedTags) <= 1;
        $skip = $skip || ($generalMatches === count($matchedTags));

        return [
            'score' => round($score, 2),
            'detailed_matches' => $detailedMatches,
            'general_matches' => $generalMatches,
            'skip' => $skip,
        ];
    }

    private function weightForTag(string $tag): float
    {
        if (in_array($tag, self::GENERAL_TAGS, true)) {
            return 0.5;
        }

        if (in_array($tag, self::DETAIL_FIRST_TAGS, true)) {
            return 3.5;
        }

        if (in_array($tag, self::DETAIL_SECOND_TAGS, true)) {
            return 2.5;
        }

        if (str_contains($tag, 'cefr')) {
            return 0.75;
        }

        return 1.5;
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

    /**
     * Get the theory page ID for a specific marker in a question.
     * First tries to find it from the best matching text block.
     *
     * @return int|null The page ID or null if not found
     */
    public function getTheoryPageIdForMarker(int $questionId, string $marker): ?int
    {
        $theoryBlock = $this->findTheoryBlockForMarker($questionId, $marker);

        if (! $theoryBlock) {
            return null;
        }

        // Load the text block to get its page_id
        $textBlock = TextBlock::where('uuid', $theoryBlock['uuid'])->first();

        return $textBlock?->page_id;
    }

    /**
     * Get available tags from the theory page for a specific marker.
     * Returns tags that are associated with text_blocks on that page.
     *
     * @return Collection<int, Tag>
     */
    public function getAvailableTheoryTagsForMarker(int $questionId, string $marker): Collection
    {
        $pageId = $this->getTheoryPageIdForMarker($questionId, $marker);

        if (! $pageId) {
            return collect();
        }

        return $this->getTagsForTheoryPage($pageId);
    }

    /**
     * Get all tags associated with text blocks on a specific page.
     *
     * @return Collection<int, Tag>
     */
    public function getTagsForTheoryPage(int $pageId): Collection
    {
        if (! Schema::hasTable('text_blocks') || ! Schema::hasTable('tag_text_block')) {
            return collect();
        }

        return Tag::query()
            ->select('tags.id', 'tags.name', 'tags.category')
            ->join('tag_text_block', 'tags.id', '=', 'tag_text_block.tag_id')
            ->join('text_blocks', 'tag_text_block.text_block_id', '=', 'text_blocks.id')
            ->where('text_blocks.page_id', $pageId)
            ->distinct()
            ->orderBy('tags.name')
            ->get();
    }

    /**
     * Validate that given tag IDs belong to a specific theory page.
     *
     * @param  array<int>  $tagIds
     * @return array<int> Valid tag IDs that exist on the page
     */
    public function validateTagsForTheoryPage(int $pageId, array $tagIds): array
    {
        if (empty($tagIds)) {
            return [];
        }

        $validTags = $this->getTagsForTheoryPage($pageId);
        $validTagIds = $validTags->pluck('id')->toArray();

        return array_values(array_intersect($tagIds, $validTagIds));
    }

    /**
     * Add tags to a marker for a question.
     * Only adds tags that are valid for the theory page and don't already exist.
     *
     * @param  array<int>  $tagIds
     * @return array{added: int, skipped: int, marker_tags: Collection<int, Tag>}
     */
    public function addTagsToMarker(int $questionId, string $marker, array $tagIds): array
    {
        if (! Schema::hasTable('question_marker_tag')) {
            return ['added' => 0, 'skipped' => count($tagIds), 'marker_tags' => collect()];
        }

        $pageId = $this->getTheoryPageIdForMarker($questionId, $marker);

        if (! $pageId) {
            return ['added' => 0, 'skipped' => count($tagIds), 'marker_tags' => $this->getMarkerTags($questionId, $marker)];
        }

        // Validate tags belong to the theory page
        $validTagIds = $this->validateTagsForTheoryPage($pageId, $tagIds);

        if (empty($validTagIds)) {
            return ['added' => 0, 'skipped' => count($tagIds), 'marker_tags' => $this->getMarkerTags($questionId, $marker)];
        }

        // Get existing marker tags to avoid duplicates
        $existingTagIds = DB::table('question_marker_tag')
            ->where('question_id', $questionId)
            ->where('marker', $marker)
            ->pluck('tag_id')
            ->toArray();

        // Filter out already existing tags
        $newTagIds = array_diff($validTagIds, $existingTagIds);

        $added = 0;
        $skipped = count($tagIds) - count($newTagIds);

        if (! empty($newTagIds)) {
            $now = now();
            $inserts = [];

            foreach ($newTagIds as $tagId) {
                $inserts[] = [
                    'question_id' => $questionId,
                    'marker' => $marker,
                    'tag_id' => $tagId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('question_marker_tag')->insert($inserts);
            $added = count($newTagIds);
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
            'marker_tags' => $this->getMarkerTags($questionId, $marker),
        ];
    }
}
