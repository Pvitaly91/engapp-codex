<?php

namespace App\Http\Controllers;

use App\Services\PolyglotCourseManifestService;
use App\Services\PolyglotProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolyglotProgressController extends Controller
{
    public function __construct(
        private PolyglotCourseManifestService $manifestService,
        private PolyglotProgressService $progressService,
    ) {}

    public function show(Request $request, string $courseSlug): JsonResponse
    {
        $manifest = $this->manifestService->build($courseSlug);
        abort_if(($manifest['total_lessons'] ?? 0) < 1, 404);

        $user = $this->progressService->resolveUser($request);
        if (! $user) {
            return response()->json([
                'authenticated' => false,
            ]);
        }

        return response()->json([
            'authenticated' => true,
            'course_slug' => $courseSlug,
            'progress' => $this->progressService->getCourseProgress($user, $courseSlug, $manifest['lessons'] ?? []),
        ]);
    }

    public function storeAttempt(Request $request, string $courseSlug): JsonResponse
    {
        $validated = $request->validate([
            'lesson_slug' => ['required', 'string', 'max:255'],
            'question_uuid' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'is_correct' => ['nullable', 'boolean'],
            'answer_payload' => ['nullable', 'array'],
            'client_attempt_uuid' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $this->progressService->resolveUser($request);
        if (! $user) {
            return response()->json([
                'authenticated' => false,
            ]);
        }

        $progress = $this->progressService->recordAttempt($user, $courseSlug, $validated['lesson_slug'], $validated);

        return response()->json([
            'authenticated' => true,
            'course_slug' => $courseSlug,
            ...$progress,
        ]);
    }

    public function import(Request $request, string $courseSlug): JsonResponse
    {
        $validated = $request->validate([
            'local_progress' => ['required', 'array'],
        ]);

        $user = $this->progressService->resolveUser($request);
        if (! $user) {
            return response()->json([
                'authenticated' => false,
            ]);
        }

        return response()->json([
            'authenticated' => true,
            'course_slug' => $courseSlug,
            'progress' => $this->progressService->importLocalProgress($user, $courseSlug, $validated['local_progress']),
        ]);
    }
}
