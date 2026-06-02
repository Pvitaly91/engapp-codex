<?php

namespace App\Http\Controllers;

use App\Services\PolyglotCourseManifestService;
use App\Services\PolyglotProgressService;
use App\Support\AdminDebugAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function debug(Request $request, string $courseSlug): JsonResponse
    {
        if (! AdminDebugAccess::allowed($request)) {
            throw ValidationException::withMessages([
                'debug' => __('authorization.failed'),
            ])->status(403);
        }

        $validated = $request->validate([
            'action' => ['required', 'string', 'max:64'],
            'lesson_slug' => ['nullable', 'string', 'max:255'],
            'answered' => ['nullable', 'integer', 'min:0'],
            'rating_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'required_answered' => ['nullable', 'integer', 'min:0'],
            'required_correct' => ['nullable', 'integer', 'min:0'],
            'required_answered_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'required_correct_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'minimum_rating_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'force_unlock_next' => ['nullable', 'boolean'],
            'completed' => ['nullable', 'boolean'],
            'clear_policy' => ['nullable', 'boolean'],
            'remove_next_progress' => ['nullable', 'boolean'],
            'all_courses' => ['nullable', 'boolean'],
        ]);

        $user = $this->progressService->resolveUser($request);
        if (! $user) {
            return response()->json([
                'authenticated' => false,
            ]);
        }

        $result = $this->progressService->adminDebugAction(
            $user,
            $courseSlug,
            $validated
        );

        return response()->json([
            'authenticated' => true,
            'course_slug' => $courseSlug,
            'debug' => true,
            ...$result,
        ]);
    }
}
