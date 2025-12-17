<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Tag;
use App\Services\MarkerTheoryMatcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MarkerTheoryTagController extends Controller
{
    public function __construct(
        private MarkerTheoryMatcherService $matcherService,
    ) {}

    /**
     * Get available theory tags for a marker.
     * Returns tags from the theory page that the marker is linked to.
     *
     * GET /api/v2/questions/{uuid}/markers/{marker}/available-theory-tags
     */
    public function availableTheoryTags(Request $request, string $questionUuid, string $marker): JsonResponse
    {
        // Validate marker format
        if (! preg_match('/^a\d+$/', $marker)) {
            return response()->json([
                'message' => 'Invalid marker format. Expected format: a1, a2, etc.',
            ], 422);
        }

        $question = Question::where('uuid', $questionUuid)->first();

        if (! $question) {
            return response()->json([
                'message' => 'Question not found.',
            ], 404);
        }

        $tags = $this->matcherService->getAvailableTheoryTagsForMarker($question->id, $marker);

        $pageId = $this->matcherService->getTheoryPageIdForMarker($question->id, $marker);

        return response()->json([
            'tags' => $this->mapTagsForResponse($tags),
            'page_id' => $pageId,
            'marker' => $marker,
            'question_id' => $question->id,
        ]);
    }

    /**
     * Add tags to a marker from the theory page.
     * Only adds tags that are valid for the theory page.
     *
     * POST /api/v2/questions/{uuid}/markers/{marker}/add-tags-from-theory-page
     */
    public function addTagsFromTheoryPage(Request $request, string $questionUuid, string $marker): JsonResponse
    {
        // Validate marker format
        if (! preg_match('/^a\d+$/', $marker)) {
            return response()->json([
                'message' => 'Invalid marker format. Expected format: a1, a2, etc.',
            ], 422);
        }

        $data = $request->validate([
            'tag_ids' => 'required|array|min:1',
            'tag_ids.*' => 'required|integer|exists:tags,id',
        ]);

        $question = Question::where('uuid', $questionUuid)->first();

        if (! $question) {
            return response()->json([
                'message' => 'Question not found.',
            ], 404);
        }

        $pageId = $this->matcherService->getTheoryPageIdForMarker($question->id, $marker);

        if (! $pageId) {
            return response()->json([
                'message' => 'No theory page found for this marker. Cannot add tags.',
            ], 422);
        }

        // Validate that all tags belong to the theory page
        $validTagIds = $this->matcherService->validateTagsForTheoryPage($pageId, $data['tag_ids']);
        $invalidTagIds = array_diff($data['tag_ids'], $validTagIds);

        if (! empty($invalidTagIds)) {
            return response()->json([
                'message' => 'Some tag IDs do not belong to the theory page.',
                'invalid_tag_ids' => array_values($invalidTagIds),
            ], 422);
        }

        $result = $this->matcherService->addTagsToMarker($question->id, $marker, $data['tag_ids']);

        // Get updated theory block after adding tags
        $theoryBlock = $this->matcherService->findTheoryBlockForMarker($question->id, $marker);

        return response()->json([
            'added' => $result['added'],
            'skipped' => $result['skipped'],
            'marker_tags' => $this->mapTagsForResponse($result['marker_tags']),
            'theory_block' => $theoryBlock,
            'marker' => $marker,
            'question_id' => $question->id,
        ]);
    }

    /**
     * Map tags collection to response format.
     *
     * @param  Collection<int, Tag>  $tags
     * @return array<int, array{id: int, name: string, category: string|null}>
     */
    private function mapTagsForResponse(Collection $tags): array
    {
        return $tags->map(fn ($tag) => [
            'id' => $tag->id,
            'name' => $tag->name,
            'category' => $tag->category ?? null,
        ])->values()->all();
    }
}
