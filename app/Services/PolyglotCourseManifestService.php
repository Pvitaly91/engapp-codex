<?php

namespace App\Services;

use App\Models\SavedGrammarTest;
use App\Support\ComposeModeEligibility;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PolyglotCourseManifestService
{
    public function build(string $courseSlug): array
    {
        $courseSlug = trim($courseSlug);

        if ($courseSlug === '') {
            return $this->emptyManifest('');
        }

        $lessons = SavedGrammarTest::query()
            ->with('questionLinks')
            ->get()
            ->filter(fn (SavedGrammarTest $test) => $this->matchesCourse($test, $courseSlug))
            ->filter(fn (SavedGrammarTest $test) => ComposeModeEligibility::isAvailableForTest($test))
            ->map(fn (SavedGrammarTest $test) => $this->normalizeLesson($test))
            ->filter()
            ->sortBy([
                ['lesson_order', 'asc'],
                ['slug', 'asc'],
            ])
            ->values();

        if ($lessons->isEmpty()) {
            return $this->emptyManifest($courseSlug);
        }

        return [
            'course' => $this->buildCourseMeta($courseSlug, $lessons),
            'lessons' => $lessons->all(),
            'by_slug' => $lessons->keyBy('slug')->all(),
            'first_lesson' => $lessons->first(),
            'total_lessons' => $lessons->count(),
        ];
    }

    public function findLesson(array $manifest, string $slug): ?array
    {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $lesson = $manifest['by_slug'][$slug] ?? null;

        return is_array($lesson) ? $lesson : null;
    }

    public function firstLesson(array $manifest): ?array
    {
        $lesson = $manifest['first_lesson'] ?? null;

        if (is_array($lesson)) {
            return $lesson;
        }

        $lessons = $manifest['lessons'] ?? [];

        return is_array($lessons[0] ?? null) ? $lessons[0] : null;
    }

    public function nextLesson(array $manifest, string $slug): ?array
    {
        $current = $this->findLesson($manifest, $slug);

        if (! $current) {
            return null;
        }

        $nextSlug = $this->nullableString($current['next_lesson_slug'] ?? null);
        if ($nextSlug !== null) {
            return $this->findLesson($manifest, $nextSlug);
        }

        return $this->adjacentLesson($manifest, $slug, 1);
    }

    public function previousLesson(array $manifest, string $slug): ?array
    {
        $current = $this->findLesson($manifest, $slug);

        if (! $current) {
            return null;
        }

        $previousSlug = $this->nullableString($current['previous_lesson_slug'] ?? null);
        if ($previousSlug !== null) {
            return $this->findLesson($manifest, $previousSlug);
        }

        return $this->adjacentLesson($manifest, $slug, -1);
    }

    public function totalLessons(array $manifest): int
    {
        $total = (int) ($manifest['total_lessons'] ?? 0);

        if ($total > 0) {
            return $total;
        }

        $lessons = $manifest['lessons'] ?? [];

        return is_array($lessons) ? count($lessons) : 0;
    }

    protected function matchesCourse(SavedGrammarTest $test, string $courseSlug): bool
    {
        $filters = ComposeModeEligibility::normalizedFilters($test);

        return trim((string) ($filters['course_slug'] ?? '')) === $courseSlug;
    }

    protected function normalizeLesson(SavedGrammarTest $test): ?array
    {
        $filters = ComposeModeEligibility::normalizedFilters($test);
        $slug = trim((string) $test->slug);

        if ($slug === '') {
            return null;
        }

        $lessonOrder = (int) ($filters['lesson_order'] ?? 0);
        if ($lessonOrder <= 0) {
            $lessonOrder = PHP_INT_MAX;
        }

        return [
            'slug' => $slug,
            'name' => trim((string) ($test->name ?? $slug)),
            'description' => trim((string) ($test->description ?? '')),
            'topic' => $this->nullableString($filters['topic'] ?? null),
            'level' => $this->nullableString($filters['level'] ?? null),
            'lesson_order' => $lessonOrder,
            'previous_lesson_slug' => $this->nullableString($filters['previous_lesson_slug'] ?? null),
            'next_lesson_slug' => $this->nullableString($filters['next_lesson_slug'] ?? null),
            'compose_url' => localized_route('test.step-compose', $slug),
            'completion' => $this->normalizeCompletion($filters['completion'] ?? []),
            'mode' => trim((string) ($filters['mode'] ?? '')),
            'course_slug' => trim((string) ($filters['course_slug'] ?? '')),
            'interface_locale' => $this->nullableString($filters['interface_locale'] ?? null),
            'study_locale' => $this->nullableString($filters['study_locale'] ?? null),
            'target_locale' => $this->nullableString($filters['target_locale'] ?? null),
        ];
    }

    protected function normalizeCompletion(mixed $completion): array
    {
        if (is_string($completion)) {
            $decoded = json_decode($completion, true);
            $completion = is_array($decoded) ? $decoded : [];
        }

        $completion = is_array($completion) ? $completion : [];

        return [
            'rolling_window' => max(1, (int) ($completion['rolling_window'] ?? 100)),
            'min_rating' => (float) ($completion['min_rating'] ?? 4.5),
        ];
    }

    protected function buildCourseMeta(string $courseSlug, Collection $lessons): array
    {
        $firstLesson = $lessons->first();

        return [
            'slug' => $courseSlug,
            'name' => Str::of($courseSlug)->replace('-', ' ')->headline()->toString(),
            'description' => is_array($firstLesson) ? (string) ($firstLesson['description'] ?? '') : '',
            'compose_url' => localized_route('courses.show', $courseSlug),
            'level' => is_array($firstLesson) ? ($firstLesson['level'] ?? null) : null,
            'mode' => is_array($firstLesson) ? ($firstLesson['mode'] ?? null) : null,
        ];
    }

    protected function adjacentLesson(array $manifest, string $slug, int $direction): ?array
    {
        $lessons = $manifest['lessons'] ?? [];

        if (! is_array($lessons) || $lessons === []) {
            return null;
        }

        foreach ($lessons as $index => $lesson) {
            if (! is_array($lesson) || ($lesson['slug'] ?? null) !== $slug) {
                continue;
            }

            $adjacent = $lessons[$index + $direction] ?? null;

            return is_array($adjacent) ? $adjacent : null;
        }

        return null;
    }

    protected function emptyManifest(string $courseSlug): array
    {
        return [
            'course' => [
                'slug' => $courseSlug,
                'name' => Str::of($courseSlug)->replace('-', ' ')->headline()->toString(),
                'description' => '',
                'compose_url' => $courseSlug !== '' ? localized_route('courses.show', $courseSlug) : null,
                'level' => null,
                'mode' => null,
            ],
            'lessons' => [],
            'by_slug' => [],
            'first_lesson' => null,
            'total_lessons' => 0,
        ];
    }

    protected function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
