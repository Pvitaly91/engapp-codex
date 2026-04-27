<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPolyglotAnswerAttempt;
use App\Models\UserPolyglotLessonProgress;
use App\Support\AdminDebugAccess;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PolyglotProgressService
{
    public const REQUIRED_ANSWERS = 100;
    public const REQUIRED_AVERAGE = 4.5;
    public const MAX_DEBUG_ANSWER_COUNT = 10000;

    public function __construct(
        private PolyglotCourseManifestService $manifestService,
    ) {}

    public function resolveUser(Request $request): ?User
    {
        $adminUserId = (int) $request->session()->get('admin_user_id', 0);
        if ($adminUserId > 0) {
            $adminUser = User::query()->find($adminUserId);
            if ($adminUser instanceof User) {
                Auth::login($adminUser);

                return $adminUser;
            }

            $request->session()->forget('admin_user_id');
        }

        if (AdminDebugAccess::allowed($request)) {
            $adminUser = $this->resolveConfiguredAdminUser();
            if (! $adminUser) {
                return null;
            }

            $request->session()->put('admin_user_id', $adminUser->id);
            Auth::login($adminUser);

            return $adminUser;
        }

        $authUser = $request->user();

        return $authUser instanceof User ? $authUser : null;
    }

    public function recordAttempt(User $user, string $courseSlug, string $lessonSlug, array $payload): array
    {
        $courseSlug = trim($courseSlug);
        $lessonSlug = trim($lessonSlug);
        $manifest = $this->knownCourseManifest($courseSlug);
        $lesson = $this->knownLesson($manifest, $lessonSlug);

        if (! $this->progressStorageAvailable()) {
            return $this->storageUnavailableActionPayload(
                $user,
                $courseSlug,
                $this->normalizeLessons($manifest['lessons'] ?? []),
                $lessonSlug
            );
        }

        if (! $this->isLessonUnlockedForUser($user, $courseSlug, $manifest, $lesson)) {
            throw ValidationException::withMessages([
                'lesson_slug' => __('frontend.tests.course.complete_previous_lesson_first'),
            ]);
        }

        return DB::transaction(function () use ($user, $courseSlug, $lessonSlug, $lesson, $payload) {
            $clientAttemptUuid = $this->nullableString($payload['client_attempt_uuid'] ?? null);

            if ($clientAttemptUuid !== null) {
                $existingAttempt = UserPolyglotAnswerAttempt::query()
                    ->where('user_id', $user->id)
                    ->where('client_attempt_uuid', $clientAttemptUuid)
                    ->first();

                if ($existingAttempt) {
                    return $this->refreshAndBuildCourseProgress($user, $courseSlug, $lessonSlug);
                }
            }

            try {
                UserPolyglotAnswerAttempt::query()->create([
                    'user_id' => $user->id,
                    'course_slug' => $courseSlug,
                    'lesson_slug' => $lessonSlug,
                    'question_uuid' => $this->nullableString($payload['question_uuid'] ?? null),
                    'rating' => $this->sanitizeRating($payload['rating'] ?? null),
                    'is_correct' => array_key_exists('is_correct', $payload)
                        ? (bool) $payload['is_correct']
                        : null,
                    'answer_payload' => $this->sanitizeAnswerPayload($payload['answer_payload'] ?? null),
                    'client_attempt_uuid' => $clientAttemptUuid,
                    'answered_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if ($clientAttemptUuid === null) {
                    throw $exception;
                }

                $duplicate = UserPolyglotAnswerAttempt::query()
                    ->where('user_id', $user->id)
                    ->where('client_attempt_uuid', $clientAttemptUuid)
                    ->exists();

                if (! $duplicate) {
                    throw $exception;
                }
            }

            $this->recalculateLessonProgress($user, $courseSlug, $lessonSlug, $lesson, $payload);

            return $this->refreshAndBuildCourseProgress($user, $courseSlug, $lessonSlug);
        });
    }

    public function getCourseProgress(?User $user, string $courseSlug, array $lessons): ?array
    {
        if (! $user) {
            return null;
        }

        $courseSlug = trim($courseSlug);
        $lessons = $this->normalizeLessons($lessons);

        if (! $this->progressStorageAvailable()) {
            return $this->buildCourseProgressPayload($user, $courseSlug, $lessons, collect());
        }

        $slugs = array_column($lessons, 'slug');
        $progressRows = UserPolyglotLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_slug', $courseSlug)
            ->whereIn('lesson_slug', $slugs)
            ->get()
            ->keyBy('lesson_slug');

        return $this->buildCourseProgressPayload($user, $courseSlug, $lessons, $progressRows);
    }

    public function getCourseProgressPayload(User $user, string $courseSlug): array
    {
        $manifest = $this->knownCourseManifest($courseSlug);
        $lessons = $this->normalizeLessons($manifest['lessons'] ?? []);

        return $this->getCourseProgress($user, $courseSlug, $lessons) ?? [
            'version' => 1,
            'course_slug' => $courseSlug,
            'user_id' => $user->id,
            'required_answers' => self::REQUIRED_ANSWERS,
            'required_average' => self::REQUIRED_AVERAGE,
            'unlocked_lessons' => [],
            'completed_lessons' => [],
            'current_lesson_slug' => null,
            'last_opened_lesson_slug' => null,
            'lessons' => [],
            'lesson_progress' => [],
            'updated_at' => now()->toJSON(),
        ];
    }

    public function adminDebugAction(User $user, string $courseSlug, array $payload): array
    {
        $manifest = $this->knownCourseManifest($courseSlug);
        $lessons = $this->normalizeLessons($manifest['lessons'] ?? []);
        $action = strtolower(trim((string) ($payload['action'] ?? '')));
        $lessonSlug = trim((string) ($payload['lesson_slug'] ?? ''));
        $lesson = $lessonSlug !== '' ? $this->knownLesson($manifest, $lessonSlug) : null;
        $nextLesson = $lesson ? $this->manifestService->nextLesson($manifest, $lessonSlug) : null;

        if (! $this->progressStorageAvailable()) {
            throw ValidationException::withMessages([
                'progress_storage' => 'Polyglot server progress tables are missing. Run php artisan migrate.',
            ])->status(503);
        }

        return DB::transaction(function () use ($user, $courseSlug, $payload, $lessons, $action, $lessonSlug, $lesson, $nextLesson) {
            switch ($action) {
            case 'mark-complete':
            case 'simulate-progress':
                if (! $lesson) {
                    throw ValidationException::withMessages([
                        'lesson_slug' => 'Unknown lesson.',
                    ]);
                }

                $answered = $this->sanitizeDebugCount($payload['answered'] ?? null);
                $ratingPercent = $this->sanitizeDebugPercent($payload['rating_percent'] ?? null);
                $completed = $action === 'mark-complete' || (bool) filter_var($payload['completed'] ?? false, FILTER_VALIDATE_BOOLEAN);

                $this->setLessonDebugState($user, $courseSlug, $lessonSlug, $lesson, $answered, $ratingPercent, $completed);
                if ($completed && $nextLesson) {
                    $this->setLessonUnlocked($user, $courseSlug, $nextLesson['slug'], true, false);
                }

                return $this->debugActionResult(
                    $user,
                    $courseSlug,
                    $lessonSlug,
                    $action,
                    true,
                    $completed ? 'marked_complete' : 'progress_simulated'
                );

            case 'apply-lesson-policy':
                if (! $lesson) {
                    throw ValidationException::withMessages([
                        'lesson_slug' => 'Unknown lesson.',
                    ]);
                }

                $policy = $this->debugPolicyForLesson($this->sanitizeDebugPolicy($payload, 'lesson'), $lesson);
                $this->setLessonDebugPolicy($user, $courseSlug, $lessonSlug, $lesson, $policy);

                if (! $nextLesson) {
                    return $this->debugActionResult($user, $courseSlug, $lessonSlug, $action, false, 'final_lesson', [
                        'policy' => $policy,
                    ]);
                }

                $progress = $this->getLessonProgressSnapshot($user, $courseSlug, $lessonSlug);
                if (! $policy['force_unlock_next'] && ! $this->debugPolicyPasses($progress, $policy)) {
                    return $this->debugActionResult($user, $courseSlug, $lessonSlug, $action, false, 'policy_failed', [
                        'policy' => $policy,
                        'progress_snapshot' => $progress,
                    ]);
                }

                if (! $policy['force_unlock_next']) {
                    $this->setLessonCompleted($user, $courseSlug, $lessonSlug, true);
                }
                $this->setLessonUnlocked($user, $courseSlug, $nextLesson['slug'], true, false);

                return $this->debugActionResult($user, $courseSlug, $lessonSlug, $action, true, 'policy_applied', [
                    'policy' => $policy,
                    'progress_snapshot' => $progress,
                ]);

            case 'apply-course-policy':
                $policy = $this->sanitizeDebugPolicy($payload, 'course');
                $applied = $this->applyCourseDebugPolicy($user, $courseSlug, $lessons, $policy);

                return $this->debugActionResult($user, $courseSlug, $lessonSlug ?: null, $action, true, 'course_policy_applied', [
                    'policy' => $policy,
                    'course_policy_result' => $applied,
                ]);

            case 'clear-debug-overrides':
                $cleared = $this->clearCourseDebugPolicies($user, $courseSlug, $lessons);

                return $this->debugActionResult($user, $courseSlug, $lessonSlug ?: null, $action, true, 'debug_overrides_cleared', [
                    'cleared_policies' => $cleared,
                ]);

            case 'unlock-next':
                if (! $lesson || ! $nextLesson) {
                    return $this->debugActionResult($user, $courseSlug, $lessonSlug ?: null, $action, false, $nextLesson ? 'missing_lesson' : 'final_lesson');
                }

                $this->setLessonUnlocked($user, $courseSlug, $nextLesson['slug'], true, false);

                return $this->debugActionResult($user, $courseSlug, $lessonSlug, $action, true, 'next_unlocked');

            case 'reset-current-lesson':
                if (! $lesson) {
                    throw ValidationException::withMessages([
                        'lesson_slug' => 'Unknown lesson.',
                    ]);
                }

                $this->resetLessonProgress(
                    $user,
                    $courseSlug,
                    $lessonSlug,
                    (bool) filter_var($payload['clear_policy'] ?? false, FILTER_VALIDATE_BOOLEAN)
                );

                return $this->debugActionResult($user, $courseSlug, $lessonSlug, $action, true, 'current_lesson_reset');

            case 'reset-current-completion':
                if (! $lesson) {
                    throw ValidationException::withMessages([
                        'lesson_slug' => 'Unknown lesson.',
                    ]);
                }

                $this->setLessonCompleted($user, $courseSlug, $lessonSlug, false);

                return $this->debugActionResult($user, $courseSlug, $lessonSlug, $action, true, 'current_completion_reset');

            case 'reset-next-unlock':
                if (! $lesson || ! $nextLesson) {
                    return $this->debugActionResult($user, $courseSlug, $lessonSlug ?: null, $action, false, $nextLesson ? 'missing_lesson' : 'final_lesson');
                }

                $this->setLessonCompleted($user, $courseSlug, $lessonSlug, false);
                $this->setLessonUnlocked($user, $courseSlug, $nextLesson['slug'], false, true);
                if ((bool) filter_var($payload['remove_next_progress'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    UserPolyglotAnswerAttempt::query()
                        ->where('user_id', $user->id)
                        ->where('course_slug', $courseSlug)
                        ->where('lesson_slug', $nextLesson['slug'])
                        ->delete();
                    UserPolyglotLessonProgress::query()
                        ->where('user_id', $user->id)
                        ->where('course_slug', $courseSlug)
                        ->where('lesson_slug', $nextLesson['slug'])
                        ->delete();
                } else {
                    $this->setLessonCompleted($user, $courseSlug, $nextLesson['slug'], false);
                }

                return $this->debugActionResult($user, $courseSlug, $lessonSlug, $action, true, 'next_unlock_reset');

            case 'reset-course-progress':
                $allCourses = (bool) filter_var($payload['all_courses'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($allCourses) {
                    UserPolyglotAnswerAttempt::query()
                        ->where('user_id', $user->id)
                        ->delete();
                    UserPolyglotLessonProgress::query()
                        ->where('user_id', $user->id)
                        ->delete();
                } else {
                    $lessonSlugs = array_values(array_filter(array_column($lessons, 'slug')));

                    if ($lessonSlugs !== []) {
                        UserPolyglotAnswerAttempt::query()
                            ->where('user_id', $user->id)
                            ->where('course_slug', $courseSlug)
                            ->whereIn('lesson_slug', $lessonSlugs)
                            ->delete();

                        UserPolyglotLessonProgress::query()
                            ->where('user_id', $user->id)
                            ->where('course_slug', $courseSlug)
                            ->whereIn('lesson_slug', $lessonSlugs)
                            ->delete();
                    }
                }

                return $this->debugActionResult($user, $courseSlug, $lessonSlug ?: null, $action, true, $allCourses ? 'all_progress_reset' : 'course_progress_reset');

            default:
                throw ValidationException::withMessages([
                    'action' => "Unknown debug action: {$action}.",
                ]);
            }
        });
    }

    private function debugActionResult(
        User $user,
        string $courseSlug,
        ?string $activeLessonSlug,
        string $action,
        bool $applied,
        string $messageKey,
        array $extra = []
    ): array {
        $courseProgress = $this->getCourseProgressPayload($user, $courseSlug);
        $activeLessonSlug = trim((string) ($activeLessonSlug ?? ''));

        return [
            'action' => $action,
            'applied' => $applied,
            'message_key' => $messageKey,
            'course_progress' => $courseProgress,
            'lesson_progress' => $activeLessonSlug !== ''
                ? ($courseProgress['lesson_progress'][$activeLessonSlug] ?? null)
                : null,
            'lesson' => $activeLessonSlug !== ''
                ? ($courseProgress['lessons'][$activeLessonSlug] ?? null)
                : null,
            ...$extra,
        ];
    }

    private function sanitizeDebugPolicy(array $payload, string $scope): array
    {
        $hasRequiredAnsweredPercent = array_key_exists('required_answered_percent', $payload)
            || array_key_exists('requiredAnsweredPercent', $payload);
        $hasRequiredCorrectPercent = array_key_exists('required_correct_percent', $payload)
            || array_key_exists('requiredCorrectPercent', $payload);

        return [
            'enabled' => true,
            'scope' => $scope,
            'required_answered' => $this->sanitizeDebugCount($payload['required_answered'] ?? $payload['answered'] ?? null),
            'required_correct' => $this->sanitizeDebugCount($payload['required_correct'] ?? null),
            'required_answered_percent' => $hasRequiredAnsweredPercent
                ? $this->sanitizeDebugPercent($payload['required_answered_percent'] ?? $payload['requiredAnsweredPercent'] ?? null)
                : null,
            'required_correct_percent' => $hasRequiredCorrectPercent
                ? $this->sanitizeDebugPercent($payload['required_correct_percent'] ?? $payload['requiredCorrectPercent'] ?? null)
                : null,
            'minimum_rating_percent' => $this->sanitizeDebugPercent($payload['minimum_rating_percent'] ?? $payload['rating_percent'] ?? null),
            'force_unlock_next' => (bool) filter_var($payload['force_unlock_next'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'updated_at' => now()->toJSON(),
        ];
    }

    private function sanitizeStoredDebugPolicy(mixed $policy): ?array
    {
        if (! is_array($policy) || ! (bool) ($policy['enabled'] ?? false)) {
            return null;
        }

        return [
            'enabled' => true,
            'scope' => in_array(($policy['scope'] ?? null), ['lesson', 'course'], true) ? $policy['scope'] : 'lesson',
            'required_answered' => $this->sanitizeDebugCount($policy['required_answered'] ?? null),
            'required_correct' => $this->sanitizeDebugCount($policy['required_correct'] ?? null),
            'required_answered_percent' => array_key_exists('required_answered_percent', $policy)
                ? $this->sanitizeDebugPercent($policy['required_answered_percent'])
                : null,
            'required_correct_percent' => array_key_exists('required_correct_percent', $policy)
                ? $this->sanitizeDebugPercent($policy['required_correct_percent'])
                : null,
            'minimum_rating_percent' => $this->sanitizeDebugPercent($policy['minimum_rating_percent'] ?? null),
            'force_unlock_next' => (bool) ($policy['force_unlock_next'] ?? false),
            'updated_at' => is_string($policy['updated_at'] ?? null) ? $policy['updated_at'] : null,
        ];
    }

    private function debugPolicyForLesson(array $policy, array $lesson): array
    {
        $questionCount = max(1, $this->lessonQuestionCount($lesson));

        if ($policy['required_answered_percent'] !== null) {
            $policy['required_answered'] = (int) ceil($questionCount * ((float) $policy['required_answered_percent'] / 100));
        }

        if ($policy['required_correct_percent'] !== null) {
            $baseAnswered = max(0, (int) ($policy['required_answered'] ?? 0));
            $policy['required_correct'] = (int) ceil($baseAnswered * ((float) $policy['required_correct_percent'] / 100));
        }

        return $policy;
    }

    private function lessonQuestionCount(array $lesson): int
    {
        return max(0, (int) ($lesson['question_count'] ?? $lesson['total_questions'] ?? 0));
    }

    private function effectiveRequiredAnswers(array $lesson): int
    {
        $questionCount = $this->lessonQuestionCount($lesson);

        if ($questionCount <= 0) {
            return self::REQUIRED_ANSWERS;
        }

        return min(self::REQUIRED_ANSWERS, $questionCount);
    }

    private function debugPolicyFromMetadata(mixed $metadata): ?array
    {
        if (! is_array($metadata)) {
            return null;
        }

        return $this->sanitizeStoredDebugPolicy($metadata['admin_debug_unlock_policy'] ?? null);
    }

    private function debugPolicyPasses(array $progress, array $policy): bool
    {
        if ((bool) ($policy['force_unlock_next'] ?? false)) {
            return true;
        }

        $ratingPercent = $progress['last_100_average'] !== null
            ? (float) $progress['last_100_average'] * 20
            : 0.0;

        return (int) ($progress['answered_count'] ?? 0) >= (int) ($policy['required_answered'] ?? 0)
            && (int) ($progress['correct_attempts'] ?? 0) >= (int) ($policy['required_correct'] ?? 0)
            && $ratingPercent >= (float) ($policy['minimum_rating_percent'] ?? 0);
    }

    private function getLessonProgressSnapshot(User $user, string $courseSlug, string $lessonSlug): array
    {
        $attemptQuery = UserPolyglotAnswerAttempt::query()
            ->where('user_id', $user->id)
            ->where('course_slug', $courseSlug)
            ->where('lesson_slug', $lessonSlug);
        $answeredCount = (clone $attemptQuery)->count();
        $lastRatings = (clone $attemptQuery)
            ->orderByDesc('answered_at')
            ->orderByDesc('id')
            ->limit(self::REQUIRED_ANSWERS)
            ->pluck('rating')
            ->map(fn ($rating) => (float) $rating)
            ->values();
        $attemptAverage = $lastRatings->count() > 0 ? round($lastRatings->avg(), 2) : null;
        $correctAttempts = (clone $attemptQuery)
            ->where(function ($query) {
                $query->where('is_correct', true)
                    ->orWhere('rating', '>=', self::REQUIRED_AVERAGE);
            })
            ->count();

        $progress = UserPolyglotLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_slug', $courseSlug)
            ->where('lesson_slug', $lessonSlug)
            ->first();
        $metadata = is_array($progress?->metadata) ? $progress->metadata : [];
        $debugCorrectAttempts = $this->sanitizeDebugCount(data_get($metadata, 'admin_debug.correct_attempts'));

        return [
            'answered_count' => max($answeredCount, (int) ($progress?->answered_count ?? 0)),
            'last_100_count' => max($lastRatings->count(), (int) ($progress?->last_100_count ?? 0)),
            'last_100_average' => $progress?->last_100_average ?? $attemptAverage,
            'correct_attempts' => max($correctAttempts, $debugCorrectAttempts),
        ];
    }

    private function setLessonDebugState(
        User $user,
        string $courseSlug,
        string $lessonSlug,
        array $lesson,
        int $answered,
        float $ratingPercent,
        bool $completed
    ): UserPolyglotLessonProgress {
        $answered = max(0, min(self::MAX_DEBUG_ANSWER_COUNT, $answered));
        $lastCount = min($answered, self::REQUIRED_ANSWERS);
        $average = round(max(0.0, min(5.0, $ratingPercent / 20)), 2);
        $correctAttempts = (int) round($answered * ($ratingPercent / 100));
        $progress = UserPolyglotLessonProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'course_slug' => $courseSlug,
            'lesson_slug' => $lessonSlug,
        ]);
        $metadata = is_array($progress->metadata) ? $progress->metadata : [];
        $metadata['admin_debug'] = [
            'simulated' => true,
            'answered_count' => $answered,
            'correct_attempts' => max(0, min($answered, $correctAttempts)),
            'rating_percent' => $ratingPercent,
            'updated_at' => now()->toJSON(),
        ];

        $progress->fill([
            'lesson_index' => $this->lessonIndex($lesson),
            'answered_count' => $answered,
            'last_100_count' => $lastCount,
            'last_100_average' => $lastCount > 0 ? $average : null,
            'is_completed' => $completed,
            'completed_at' => $completed ? ($progress->completed_at ?? now()) : null,
            'unlocked_at' => $progress->unlocked_at ?? now(),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
        $progress->save();

        return $progress;
    }

    private function setLessonDebugPolicy(
        User $user,
        string $courseSlug,
        string $lessonSlug,
        array $lesson,
        array $policy
    ): UserPolyglotLessonProgress {
        $progress = UserPolyglotLessonProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'course_slug' => $courseSlug,
            'lesson_slug' => $lessonSlug,
        ]);
        $metadata = is_array($progress->metadata) ? $progress->metadata : [];
        $metadata['admin_debug_unlock_policy'] = $policy;

        $progress->lesson_index = $this->lessonIndex($lesson);
        $progress->metadata = $metadata;
        $progress->save();

        return $progress;
    }

    private function applyCourseDebugPolicy(User $user, string $courseSlug, array $lessons, array $policy): array
    {
        $storedPolicies = 0;
        $completedLessons = 0;
        $unlockedLessons = 0;
        $lessonMap = collect($lessons)->keyBy('slug');

        foreach ($lessons as $lesson) {
            $lessonSlug = (string) ($lesson['slug'] ?? '');
            if ($lessonSlug === '') {
                continue;
            }

            $lessonPolicy = $this->debugPolicyForLesson($policy, $lesson);
            $this->setLessonDebugPolicy($user, $courseSlug, $lessonSlug, $lesson, $lessonPolicy);
            $storedPolicies++;

            $nextLessonSlug = $lesson['next_lesson_slug'] ?? null;
            if (! $nextLessonSlug || ! $lessonMap->has($nextLessonSlug)) {
                continue;
            }

            $progress = $this->getLessonProgressSnapshot($user, $courseSlug, $lessonSlug);
            if ($lessonPolicy['force_unlock_next']) {
                $this->setLessonUnlocked($user, $courseSlug, $nextLessonSlug, true, false);
                $unlockedLessons++;
                continue;
            }

            if (! $this->debugPolicyPasses($progress, $lessonPolicy)) {
                continue;
            }

            $this->setLessonCompleted($user, $courseSlug, $lessonSlug, true);
            $this->setLessonUnlocked($user, $courseSlug, $nextLessonSlug, true, false);
            $completedLessons++;
            $unlockedLessons++;
        }

        return [
            'stored_policies' => $storedPolicies,
            'completed_lessons' => $completedLessons,
            'unlocked_lessons' => $unlockedLessons,
        ];
    }

    private function clearCourseDebugPolicies(User $user, string $courseSlug, array $lessons): int
    {
        $lessonSlugs = array_values(array_filter(array_column($lessons, 'slug')));
        if ($lessonSlugs === []) {
            return 0;
        }

        $count = 0;
        UserPolyglotLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_slug', $courseSlug)
            ->whereIn('lesson_slug', $lessonSlugs)
            ->get()
            ->each(function (UserPolyglotLessonProgress $progress) use (&$count) {
                $metadata = is_array($progress->metadata) ? $progress->metadata : [];
                if (! array_key_exists('admin_debug_unlock_policy', $metadata)) {
                    return;
                }

                unset($metadata['admin_debug_unlock_policy']);
                $progress->metadata = $metadata === [] ? null : $metadata;
                $progress->save();
                $count++;
            });

        return $count;
    }

    private function setLessonCompleted(User $user, string $courseSlug, string $lessonSlug, bool $completed): UserPolyglotLessonProgress
    {
        $manifest = $this->knownCourseManifest($courseSlug);
        $lesson = $this->knownLesson($manifest, $lessonSlug);
        $progress = UserPolyglotLessonProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'course_slug' => $courseSlug,
            'lesson_slug' => $lessonSlug,
        ]);

        $progress->lesson_index = $this->lessonIndex($lesson);
        $progress->is_completed = $completed;
        $progress->completed_at = $completed ? ($progress->completed_at ?? now()) : null;
        if ($completed) {
            $progress->unlocked_at ??= now();
        }
        $progress->save();

        return $progress;
    }

    private function setLessonUnlocked(
        User $user,
        string $courseSlug,
        string $lessonSlug,
        bool $unlock,
        bool $keepCurrentUnlocked = false
    ): ?UserPolyglotLessonProgress {
        $manifest = $this->knownCourseManifest($courseSlug);
        $lesson = $this->knownLesson($manifest, $lessonSlug);
        $progress = UserPolyglotLessonProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'course_slug' => $courseSlug,
            'lesson_slug' => $lessonSlug,
        ]);

        $progress->lesson_index = $this->lessonIndex($lesson);
        if ($unlock) {
            $progress->unlocked_at ??= now();
        } else {
            $progress->unlocked_at = null;
        }
        $progress->save();

        return $progress;
    }

    private function resetLessonProgress(User $user, string $courseSlug, string $lessonSlug, bool $clearPolicy): UserPolyglotLessonProgress
    {
        $manifest = $this->knownCourseManifest($courseSlug);
        $lesson = $this->knownLesson($manifest, $lessonSlug);

        UserPolyglotAnswerAttempt::query()
            ->where('user_id', $user->id)
            ->where('course_slug', $courseSlug)
            ->where('lesson_slug', $lessonSlug)
            ->delete();

        $progress = UserPolyglotLessonProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'course_slug' => $courseSlug,
            'lesson_slug' => $lessonSlug,
        ]);
        $metadata = is_array($progress->metadata) ? $progress->metadata : [];
        unset($metadata['admin_debug']);
        if ($clearPolicy) {
            unset($metadata['admin_debug_unlock_policy']);
        }

        $progress->fill([
            'lesson_index' => $this->lessonIndex($lesson),
            'answered_count' => 0,
            'last_100_count' => 0,
            'last_100_average' => null,
            'is_completed' => false,
            'completed_at' => null,
            'unlocked_at' => $progress->unlocked_at ?? now(),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
        $progress->save();

        return $progress;
    }

    private function sanitizeDebugCount(mixed $value = null): int
    {
        $normalized = $this->sanitizeIntegerOrNull($value);

        return $normalized === null
            ? 0
            : max(0, min(self::MAX_DEBUG_ANSWER_COUNT, $normalized));
    }

    private function sanitizeDebugPercent(mixed $value = null): float
    {
        $normalized = $this->sanitizeFloatOrNull($value);

        return $normalized === null
            ? 0.0
            : max(0.0, min(100.0, round($normalized, 2)));
    }

    public function importLocalProgress(User $user, string $courseSlug, array $localProgress): array
    {
        $courseSlug = trim($courseSlug);
        $manifest = $this->knownCourseManifest($courseSlug);
        $lessons = $this->normalizeLessons($manifest['lessons'] ?? []);

        if (! $this->progressStorageAvailable()) {
            return $this->buildCourseProgressPayload($user, $courseSlug, $lessons, collect());
        }

        DB::transaction(function () use ($user, $courseSlug, $lessons, $localProgress) {
            foreach ($lessons as $lesson) {
                $lessonSlug = $lesson['slug'];
                $rawLessonProgress = $this->extractLocalLessonProgress($localProgress, $lessonSlug);
                if (! is_array($rawLessonProgress)) {
                    continue;
                }

                $rollingRatings = $this->sanitizeLocalRatings($rawLessonProgress['rolling_results'] ?? []);
                if ($rollingRatings === []) {
                    continue;
                }

                $localTotal = max(
                    count($rollingRatings),
                    (int) ($rawLessonProgress['total_attempts'] ?? count($rollingRatings))
                );
                $serverTotal = UserPolyglotAnswerAttempt::query()
                    ->where('user_id', $user->id)
                    ->where('course_slug', $courseSlug)
                    ->where('lesson_slug', $lessonSlug)
                    ->count();
                $missingCount = $localTotal - $serverTotal;

                if ($missingCount <= 0) {
                    continue;
                }

                $ratingsToImport = array_slice($rollingRatings, -min($missingCount, count($rollingRatings)));
                $firstOffset = max(0, $localTotal - count($rollingRatings));
                $startOffset = $localTotal - count($ratingsToImport);

                foreach ($ratingsToImport as $index => $rating) {
                    $attemptOffset = $startOffset + $index;
                    $clientAttemptUuid = implode(':', [
                        'polyglot-local-import',
                        $user->id,
                        $courseSlug,
                        $lessonSlug,
                        max($firstOffset, $attemptOffset),
                    ]);

                    UserPolyglotAnswerAttempt::query()->firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'client_attempt_uuid' => $clientAttemptUuid,
                        ],
                        [
                            'course_slug' => $courseSlug,
                            'lesson_slug' => $lessonSlug,
                            'question_uuid' => null,
                            'rating' => $rating,
                            'is_correct' => $rating >= self::REQUIRED_AVERAGE,
                            'answer_payload' => [
                                'source' => 'local_progress_import',
                                'local_total_attempts' => $localTotal,
                                'local_attempt_offset' => $attemptOffset,
                            ],
                            'answered_at' => now()->subSeconds(count($ratingsToImport) - $index),
                        ]
                    );
                }

                $this->recalculateLessonProgress($user, $courseSlug, $lessonSlug, $lesson, [
                    'answer_payload' => [
                        'current_queue_index' => $rawLessonProgress['current_queue_index'] ?? null,
                    ],
                ]);
            }
        });

        return $this->getCourseProgress($user, $courseSlug, $lessons) ?? [];
    }

    private function refreshAndBuildCourseProgress(User $user, string $courseSlug, string $activeLessonSlug): array
    {
        $manifest = $this->knownCourseManifest($courseSlug);
        $progress = $this->getCourseProgress($user, $courseSlug, $manifest['lessons'] ?? []) ?? [];

        return [
            'course_progress' => $progress,
            'lesson_progress' => $progress['lesson_progress'][$activeLessonSlug] ?? null,
            'lesson' => $progress['lessons'][$activeLessonSlug] ?? null,
        ];
    }

    private function recalculateLessonProgress(
        User $user,
        string $courseSlug,
        string $lessonSlug,
        array $lesson,
        array $payload = []
    ): UserPolyglotLessonProgress {
        $attemptQuery = UserPolyglotAnswerAttempt::query()
            ->where('user_id', $user->id)
            ->where('course_slug', $courseSlug)
            ->where('lesson_slug', $lessonSlug);

        $answeredCount = (clone $attemptQuery)->count();
        $lastRatings = (clone $attemptQuery)
            ->orderByDesc('answered_at')
            ->orderByDesc('id')
            ->limit(self::REQUIRED_ANSWERS)
            ->pluck('rating')
            ->map(fn ($rating) => (float) $rating)
            ->values();
        $lastCount = $lastRatings->count();
        $average = $lastCount > 0 ? round($lastRatings->avg(), 2) : null;
        $correctAttempts = (clone $attemptQuery)
            ->where(function ($query) {
                $query->where('is_correct', true)
                    ->orWhere('rating', '>=', self::REQUIRED_AVERAGE);
            })
            ->count();
        $effectiveRequiredAnswers = $this->effectiveRequiredAnswers($lesson);
        $isCompleted = $lastCount >= $effectiveRequiredAnswers
            && $average !== null
            && $average >= self::REQUIRED_AVERAGE;
        $progress = UserPolyglotLessonProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'course_slug' => $courseSlug,
            'lesson_slug' => $lessonSlug,
        ]);
        $metadata = is_array($progress->metadata) ? $progress->metadata : [];
        $currentQueueIndex = data_get($payload, 'answer_payload.current_queue_index');
        $isCorrectAttempt = data_get($payload, 'is_correct');
        if (! is_bool($isCorrectAttempt) && is_numeric(data_get($payload, 'rating', null))) {
            $isCorrectAttempt = (float) $payload['rating'] >= self::REQUIRED_AVERAGE;
        }

        if (is_numeric($currentQueueIndex) && (int) $currentQueueIndex >= 0) {
            $currentQueueIndex = (int) $currentQueueIndex;
            if ($isCorrectAttempt) {
                $currentQueueIndex += 1;
            }

            $metadata['current_queue_index'] = max(0, $currentQueueIndex);
        } elseif ((bool) $isCorrectAttempt) {
            $existingQueueIndex = isset($metadata['current_queue_index']) && is_numeric($metadata['current_queue_index'])
                ? (int) $metadata['current_queue_index']
                : 0;
            $metadata['current_queue_index'] = max(0, $existingQueueIndex + 1);
        }

        $debugPolicy = $this->debugPolicyFromMetadata($metadata);
        $forceDebugUnlock = false;
        if ($debugPolicy !== null) {
            $forceDebugUnlock = (bool) $debugPolicy['force_unlock_next'];
            $isCompleted = $forceDebugUnlock
                ? $isCompleted
                : $this->debugPolicyPasses([
                    'answered_count' => $answeredCount,
                    'last_100_count' => $lastCount,
                    'last_100_average' => $average,
                    'correct_attempts' => $correctAttempts,
                ], $debugPolicy);
        }

        $progress->fill([
            'lesson_index' => $this->lessonIndex($lesson),
            'answered_count' => $answeredCount,
            'last_100_count' => $lastCount,
            'last_100_average' => $average,
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? ($progress->completed_at ?? now()) : null,
            'unlocked_at' => $progress->unlocked_at ?? now(),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
        $progress->save();

        if ($isCompleted || $forceDebugUnlock) {
            $this->markNextLessonUnlocked($user, $courseSlug, $lessonSlug);
        }

        return $progress;
    }

    private function buildCourseProgressPayload(User $user, string $courseSlug, array $lessons, Collection $progressRows): array
    {
        $lessonMap = [];
        $lessonProgressMap = [];
        $completedLessons = [];
        $unlockedLessons = [];
        $previousSlug = null;

        foreach ($lessons as $index => $lesson) {
            $slug = $lesson['slug'];
            $row = $progressRows->get($slug);
            $previousCompleted = $previousSlug !== null
                && ($lessonMap[$previousSlug]['is_completed'] ?? false);
            $isUnlocked = $index === 0 || $previousCompleted || (bool) $row?->unlocked_at;
            $isCompleted = (bool) $row?->is_completed;
            $entry = $this->lessonEntryPayload($courseSlug, $lesson, $row, $isUnlocked, $isCompleted);

            $lessonMap[$slug] = $entry;
            $lessonProgressMap[$slug] = $this->lessonProgressPayload($courseSlug, $lesson, $entry, $row);

            if ($isUnlocked) {
                $unlockedLessons[] = $slug;
            }

            if ($isCompleted) {
                $completedLessons[] = $slug;
            }

            $previousSlug = $slug;
        }

        $currentLesson = collect($lessons)
            ->first(fn (array $lesson) => ($lessonMap[$lesson['slug']]['is_unlocked'] ?? false)
                && ! ($lessonMap[$lesson['slug']]['is_completed'] ?? false));

        if (! $currentLesson && $lessons !== []) {
            $currentLesson = $lessons[array_key_last($lessons)];
        }

        $currentLessonSlug = $currentLesson['slug'] ?? null;
        $lastOpenedRow = $progressRows
            ->filter(fn (?UserPolyglotLessonProgress $row) => $row !== null && $row->answered_count > 0)
            ->sortByDesc(fn (UserPolyglotLessonProgress $row) => $row->updated_at?->getTimestamp() ?? 0)
            ->first();
        $lastOpenedLessonSlug = $lastOpenedRow?->lesson_slug ?? $currentLessonSlug;

        return [
            'version' => 1,
            'course_slug' => $courseSlug,
            'user_id' => $user->id,
            'required_answers' => self::REQUIRED_ANSWERS,
            'required_average' => self::REQUIRED_AVERAGE,
            'unlocked_lessons' => $unlockedLessons,
            'completed_lessons' => $completedLessons,
            'current_lesson_slug' => $currentLessonSlug,
            'last_opened_lesson_slug' => $lastOpenedLessonSlug,
            'lessons' => $lessonMap,
            'lesson_progress' => $lessonProgressMap,
            'updated_at' => now()->toJSON(),
        ];
    }

    private function lessonEntryPayload(
        string $courseSlug,
        array $lesson,
        ?UserPolyglotLessonProgress $row,
        bool $isUnlocked,
        bool $isCompleted
    ): array {
        return [
            'lesson_slug' => $lesson['slug'],
            'course_slug' => $courseSlug,
            'lesson_index' => $this->lessonIndex($lesson),
            'unlocked' => $isUnlocked,
            'completed' => $isCompleted,
            'is_unlocked' => $isUnlocked,
            'is_completed' => $isCompleted,
            'has_progress' => (int) ($row?->answered_count ?? 0) > 0 || $isCompleted,
            'answered_count' => (int) ($row?->answered_count ?? 0),
            'last_100_count' => (int) ($row?->last_100_count ?? 0),
            'last_100_average' => $row?->last_100_average === null ? null : (float) $row->last_100_average,
            'required_answers' => self::REQUIRED_ANSWERS,
            'required_average' => self::REQUIRED_AVERAGE,
            'completed_at' => $row?->completed_at?->toJSON(),
            'unlocked_at' => $row?->unlocked_at?->toJSON(),
            'last_opened_at' => $row?->updated_at?->toJSON(),
            'updated_at' => $row?->updated_at?->toJSON(),
            'current_queue_index' => $this->currentQueueIndex($row),
        ];
    }

    private function lessonProgressPayload(
        string $courseSlug,
        array $lesson,
        array $entry,
        ?UserPolyglotLessonProgress $row
    ): array {
        return [
            'version' => 3,
            'lesson_slug' => $lesson['slug'],
            'course_slug' => $courseSlug,
            'current_queue_index' => $entry['current_queue_index'],
            'rolling_results' => $this->syntheticRollingResults(
                $entry['last_100_count'],
                $entry['last_100_average']
            ),
            'total_attempts' => $entry['answered_count'],
            'correct_attempts' => $this->correctAttemptCount($courseSlug, $lesson['slug'], $row?->user_id),
            'question_stats' => $this->questionStatsForLesson($courseSlug, $lesson['slug'], $row?->user_id),
            'lesson_completed' => $entry['is_completed'],
            'completed_at' => $entry['completed_at'],
            'last_seen_at' => $entry['updated_at'],
            'server' => [
                'answered_count' => $entry['answered_count'],
                'last_100_count' => $entry['last_100_count'],
                'last_100_average' => $entry['last_100_average'],
                'required_answers' => self::REQUIRED_ANSWERS,
                'required_average' => self::REQUIRED_AVERAGE,
            ],
        ];
    }

    private function correctAttemptCount(string $courseSlug, string $lessonSlug, ?int $userId): int
    {
        if (! $userId || ! Schema::hasTable('user_polyglot_answer_attempts')) {
            return 0;
        }

        return UserPolyglotAnswerAttempt::query()
            ->where('user_id', $userId)
            ->where('course_slug', $courseSlug)
            ->where('lesson_slug', $lessonSlug)
            ->where(function ($query) {
                $query->where('is_correct', true)
                    ->orWhere('rating', '>=', self::REQUIRED_AVERAGE);
            })
            ->count();
    }

    private function questionStatsForLesson(string $courseSlug, string $lessonSlug, ?int $userId): array
    {
        if (! $userId || ! Schema::hasTable('user_polyglot_answer_attempts')) {
            return [];
        }

        $stats = [];
        $attempts = UserPolyglotAnswerAttempt::query()
            ->where('user_id', $userId)
            ->where('course_slug', $courseSlug)
            ->where('lesson_slug', $lessonSlug)
            ->whereNotNull('question_uuid')
            ->orderBy('answered_at')
            ->orderBy('id')
            ->get(['question_uuid', 'rating', 'is_correct', 'answered_at']);

        foreach ($attempts as $attempt) {
            $uuid = trim((string) $attempt->question_uuid);
            if ($uuid === '') {
                continue;
            }

            $stats[$uuid] ??= [
                'uuid' => $uuid,
                'shown' => 0,
                'correct' => 0,
                'incorrect' => 0,
                'last_seen_at' => null,
                'last_answered_at' => null,
            ];

            $isCorrect = $attempt->is_correct === true
                || ((float) $attempt->rating) >= self::REQUIRED_AVERAGE;

            $stats[$uuid]['shown']++;
            $stats[$uuid][$isCorrect ? 'correct' : 'incorrect']++;
            $stats[$uuid]['last_seen_at'] = $attempt->answered_at?->toJSON();
            $stats[$uuid]['last_answered_at'] = $attempt->answered_at?->toJSON();
        }

        return $stats;
    }

    private function syntheticRollingResults(int $count, ?float $average): array
    {
        if ($count <= 0 || $average === null) {
            return [];
        }

        return array_fill(0, min($count, self::REQUIRED_ANSWERS), round($average, 2));
    }

    private function markNextLessonUnlocked(User $user, string $courseSlug, string $lessonSlug): void
    {
        $manifest = $this->knownCourseManifest($courseSlug);
        $nextLesson = $this->manifestService->nextLesson($manifest, $lessonSlug);

        if (! $nextLesson) {
            return;
        }

        $progress = UserPolyglotLessonProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'course_slug' => $courseSlug,
            'lesson_slug' => $nextLesson['slug'],
        ]);

        $progress->lesson_index = $this->lessonIndex($nextLesson);
        $progress->unlocked_at ??= now();
        $progress->save();
    }

    private function isLessonUnlockedForUser(User $user, string $courseSlug, array $manifest, array $lesson): bool
    {
        $firstLesson = $this->manifestService->firstLesson($manifest);
        if (($firstLesson['slug'] ?? null) === $lesson['slug']) {
            return true;
        }

        $previousLesson = $this->manifestService->previousLesson($manifest, $lesson['slug']);
        if (! $previousLesson) {
            return false;
        }

        return UserPolyglotLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_slug', $courseSlug)
            ->where('lesson_slug', $previousLesson['slug'])
            ->where('is_completed', true)
            ->exists();
    }

    private function progressStorageAvailable(): bool
    {
        return Schema::hasTable('user_polyglot_lesson_progress')
            && Schema::hasTable('user_polyglot_answer_attempts');
    }

    private function storageUnavailableActionPayload(
        User $user,
        string $courseSlug,
        array $lessons,
        ?string $activeLessonSlug = null
    ): array {
        $courseProgress = $this->buildCourseProgressPayload($user, $courseSlug, $lessons, collect());
        $activeLessonSlug = trim((string) ($activeLessonSlug ?? ''));

        return [
            'course_progress' => $courseProgress,
            'lesson_progress' => $activeLessonSlug !== ''
                ? ($courseProgress['lesson_progress'][$activeLessonSlug] ?? null)
                : null,
            'lesson' => $activeLessonSlug !== ''
                ? ($courseProgress['lessons'][$activeLessonSlug] ?? null)
                : null,
            'progress_storage_available' => false,
        ];
    }

    private function knownCourseManifest(string $courseSlug): array
    {
        $manifest = $this->manifestService->build($courseSlug);

        if (($manifest['total_lessons'] ?? 0) < 1) {
            throw ValidationException::withMessages([
                'course_slug' => 'Unknown polyglot course.',
            ]);
        }

        return $manifest;
    }

    private function knownLesson(array $manifest, string $lessonSlug): array
    {
        $lesson = $this->manifestService->findLesson($manifest, $lessonSlug);

        if (! $lesson) {
            throw ValidationException::withMessages([
                'lesson_slug' => 'Unknown polyglot lesson.',
            ]);
        }

        return $lesson;
    }

    private function normalizeLessons(array $lessons): array
    {
        $normalized = array_values(array_filter($lessons, fn ($lesson) => is_array($lesson) && filled($lesson['slug'] ?? null)));

        usort($normalized, fn (array $left, array $right) => [
            (int) ($left['lesson_order'] ?? PHP_INT_MAX),
            (string) ($left['slug'] ?? ''),
        ] <=> [
            (int) ($right['lesson_order'] ?? PHP_INT_MAX),
            (string) ($right['slug'] ?? ''),
        ]);

        return $normalized;
    }

    private function lessonIndex(array $lesson): ?int
    {
        $lessonOrder = (int) ($lesson['lesson_order'] ?? 0);

        return $lessonOrder > 0 ? $lessonOrder : null;
    }

    private function currentQueueIndex(?UserPolyglotLessonProgress $row): int
    {
        $value = is_array($row?->metadata) ? ($row->metadata['current_queue_index'] ?? 0) : 0;

        return is_numeric($value) && (int) $value >= 0 ? (int) $value : 0;
    }

    private function sanitizeRating(mixed $rating): float
    {
        $rating = (float) $rating;

        return max(0.0, min(5.0, round($rating, 2)));
    }

    private function sanitizeIntegerOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function sanitizeFloatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function sanitizeLocalRatings(mixed $ratings): array
    {
        if (! is_array($ratings)) {
            return [];
        }

        return collect($ratings)
            ->map(fn ($rating) => $this->sanitizeRating($rating))
            ->slice(-self::REQUIRED_ANSWERS)
            ->values()
            ->all();
    }

    private function sanitizeAnswerPayload(mixed $payload): ?array
    {
        if (! is_array($payload)) {
            return null;
        }

        $sanitized = $this->removeSensitivePayloadKeys($payload);

        return $sanitized === [] ? null : $sanitized;
    }

    private function removeSensitivePayloadKeys(array $payload): array
    {
        $blocked = [
            'password',
            'password_confirmation',
            'token',
            'access_token',
            'refresh_token',
            'api_key',
            'secret',
            'csrf',
            '_token',
            'cookie',
            'session',
            'authorization',
            'email',
            'user_id',
        ];
        $sanitized = [];

        foreach ($payload as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            if (in_array($normalizedKey, $blocked, true)) {
                continue;
            }

            $sanitized[$key] = is_array($value)
                ? $this->removeSensitivePayloadKeys($value)
                : $value;
        }

        return $sanitized;
    }

    private function extractLocalLessonProgress(array $localProgress, string $lessonSlug): ?array
    {
        $candidates = [
            data_get($localProgress, "lesson_progress.$lessonSlug"),
            data_get($localProgress, "lesson_states.$lessonSlug"),
            data_get($localProgress, "lessons.$lessonSlug.progress"),
            data_get($localProgress, "lessons.$lessonSlug"),
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) && array_key_exists('rolling_results', $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }

    private function resolveConfiguredAdminUser(): ?User
    {
        if (! Schema::hasTable('users')) {
            return null;
        }

        $candidates = array_values(array_filter(array_unique([
            trim((string) config('admin.user_email', '')),
            trim((string) config('admin.username', '')),
        ])));

        foreach ($candidates as $candidate) {
            $user = User::query()
                ->where('email', $candidate)
                ->orWhere('name', $candidate)
                ->first();

            if ($user) {
                return $user;
            }
        }

        $username = trim((string) config('admin.username', 'admin')) ?: 'admin';
        $email = $candidates[0] ?? $username;

        return User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $username,
                'password' => Str::random(40),
            ]
        );
    }
}

