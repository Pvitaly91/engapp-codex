<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Services\MarkerTheoryMatcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuestionMarkerTagController extends Controller
{
    public function availableTheoryTags(
        Question $question,
        string $marker,
        MarkerTheoryMatcherService $matcherService
    ): JsonResponse {
        $this->validateMarker($marker);

        $pageId = $this->resolveTheoryPageId($question, $marker, $matcherService);

        if (! $pageId) {
            return response()->json([
                'page_id' => null,
                'tags' => [],
            ]);
        }

        $tags = Tag::query()
            ->select('tags.id', 'tags.name', 'tags.category')
            ->whereHas('textBlocks', function ($query) use ($pageId) {
                $query->where('page_id', $pageId);
            })
            ->distinct()
            ->orderBy('tags.name')
            ->get();

        return response()->json([
            'page_id' => $pageId,
            'tags' => $tags,
        ]);
    }

    public function addTagsFromTheoryPage(
        Request $request,
        Question $question,
        string $marker,
        MarkerTheoryMatcherService $matcherService
    ): JsonResponse {
        $this->validateMarker($marker);

        $data = $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'integer',
        ]);

        $pageId = $this->resolveTheoryPageId($question, $marker, $matcherService);

        if (! $pageId) {
            return response()->json([
                'message' => 'No theory page found for this marker',
                'marker_tags' => [],
                'page_id' => null,
                'theory_block' => null,
            ], 422);
        }

        $allowedTagIds = $this->getAllowedTagIdsForPage($pageId);
        $incomingTagIds = collect($data['tag_ids'] ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $tagIdsToAdd = $incomingTagIds
            ->filter(fn ($id) => in_array($id, $allowedTagIds, true))
            ->values();

        if ($tagIdsToAdd->isEmpty()) {
            return response()->json([
                'message' => 'No valid tags found for the current theory page',
                'marker_tags' => $this->getMarkerTagNames($question, $marker, $matcherService),
                'page_id' => $pageId,
                'theory_block' => $matcherService->findTheoryBlockForMarker($question->id, $marker),
            ], 422);
        }

        DB::transaction(function () use ($question, $marker, $tagIdsToAdd) {
            foreach ($tagIdsToAdd as $tagId) {
                DB::table('question_marker_tag')->insertOrIgnore([
                    'question_id' => $question->id,
                    'marker' => $marker,
                    'tag_id' => $tagId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json([
            'marker_tags' => $this->getMarkerTagNames($question, $marker, $matcherService),
            'page_id' => $pageId,
            'theory_block' => $matcherService->findTheoryBlockForMarker($question->id, $marker),
        ]);
    }

    private function resolveTheoryPageId(
        Question $question,
        string $marker,
        MarkerTheoryMatcherService $matcherService
    ): ?int {
        if (! empty($question->theory_text_block_uuid)) {
            $block = TextBlock::where('uuid', $question->theory_text_block_uuid)->first();

            if ($block && $block->page_id) {
                return $block->page_id;
            }
        }

        $bestTheory = $matcherService->findTheoryBlockForMarker($question->id, $marker);

        if ($bestTheory && isset($bestTheory['uuid'])) {
            $block = TextBlock::where('uuid', $bestTheory['uuid'])->first();

            return $block?->page_id;
        }

        return null;
    }

    private function getAllowedTagIdsForPage(int $pageId): array
    {
        return DB::table('tag_text_block')
            ->join('text_blocks', 'tag_text_block.text_block_id', '=', 'text_blocks.id')
            ->where('text_blocks.page_id', $pageId)
            ->pluck('tag_text_block.tag_id')
            ->unique()
            ->values()
            ->all();
    }

    private function getMarkerTagNames(
        Question $question,
        string $marker,
        MarkerTheoryMatcherService $matcherService
    ): array {
        return $matcherService->getMarkerTags($question->id, $marker)
            ->pluck('name')
            ->values()
            ->all();
    }

    private function validateMarker(string $marker): void
    {
        Validator::make([
            'marker' => $marker,
        ], [
            'marker' => ['required', 'regex:/^a\d+$/'],
        ])->validate();
    }
}
