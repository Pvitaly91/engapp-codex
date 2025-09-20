<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Test;
use App\Services\QuestionTechDraftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TechQuestionDraftController extends Controller
{
    public function __construct(private QuestionTechDraftService $draftService)
    {
    }

    public function show(string $slug, Question $question): JsonResponse
    {
        $this->ensureQuestionBelongsToTest($slug, $question);

        $dump = $this->draftService->getDraft($question);

        return response()->json($dump);
    }

    public function update(Request $request, string $slug, Question $question): JsonResponse
    {
        $this->ensureQuestionBelongsToTest($slug, $question);

        $validated = $request->validate([
            'question' => 'required|array',
            'question.question' => 'nullable|string',
            'question.difficulty' => 'nullable',
            'question.level' => 'nullable',
            'question.flag' => 'nullable',
            'question.category_id' => 'nullable',
            'question.source_id' => 'nullable',
            'question.options' => 'array',
            'question.answers' => 'array',
            'question.verb_hints' => 'array',
            'question.variants' => 'array',
            'question.hints' => 'array',
            'question.tags' => 'array',
        ]);

        $draft = $this->draftService->storeDraftFromArray(
            $question,
            $validated['question'],
            $slug
        );

        return response()->json([
            'status' => 'saved',
            'draft' => $draft,
        ]);
    }

    public function apply(string $slug, Question $question): JsonResponse
    {
        $this->ensureQuestionBelongsToTest($slug, $question);

        $draft = $this->draftService->applyDraft($question);

        return response()->json([
            'status' => 'applied',
            'draft' => $draft,
        ]);
    }

    private function ensureQuestionBelongsToTest(string $slug, Question $question): void
    {
        $test = Test::where('slug', $slug)->firstOrFail();

        if (! in_array($question->id, $test->questions ?? [], true)) {
            abort(404);
        }
    }
}
