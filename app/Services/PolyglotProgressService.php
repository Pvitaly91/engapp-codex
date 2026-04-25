<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPolyglotAnswerAttempt;
use App\Models\UserPolyglotLessonProgress;
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

    public function __construct(
        private PolyglotCourseManifestService $manifestService,
    ) {}

    public function resolveUser(Request $request): ?User
    {
        if ($request->session()->get('admin_authenticated', false)) {
            $adminUserId = $request->session()->get('admin_user_id');
            if ($adminUserId) {
                $adminUser = User::query()->find($adminUserId);
                if ($adminUser instanceof User) {
                    Auth::login($adminUser);

                    return $adminUser;
                }
            }

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
        $slugs = array_column($lessons, 'slug');
        $progressRows = UserPolyglotLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_slug', $courseSlug)
            ->whereIn('lesson_slug', $slugs)
            ->get()
            ->keyBy('lesson_slug');

        return $this->buildCourseProgressPayload($user, $courseSlug, $lessons, $progressRows);
    }

    public function importLocalProgress(User $user, string $courseSlug, array $localProgress): array
    {
        $courseSlug = trim($courseSlug);
        $manifest = $this->knownCourseManifest($courseSlug);
        $lessons = $this->normalizeLessons($manifest['lessons'] ?? []);

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
        $isCompleted = $lastCount >= self::REQUIRED_ANSWERS
            && $average !== null
            && $average >= self::REQUIRED_AVERAGE;
        $progress = UserPolyglotLessonProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'course_slug' => $courseSlug,
            'lesson_slug' => $lessonSlug,
        ]);
        $metadata = is_array($progress->metadata) ? $progress->metadata : [];
        $currentQueueIndex = data_get($payload, 'answer_payload.current_queue_index');

        if (is_numeric($currentQueueIndex) && (int) $currentQueueIndex >= 0) {
            $metadata['current_queue_index'] = (int) $currentQueueIndex;
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

        if ($isCompleted) {
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
        if (! $userId) {
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
