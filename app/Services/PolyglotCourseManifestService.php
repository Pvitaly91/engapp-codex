<?php

namespace App\Services;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\Test;
use App\Support\ComposeModeEligibility;
use App\Support\SentenceBuilderBranding;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PolyglotCourseManifestService
{
    /**
     * Read-only first-entry metadata; no question banks, relations or registry are loaded.
     * Keep even an empty first lesson: a later usable lesson cannot repair the actual entry link.
     * @param list<string> $courseSlugs
     * @return array<string, array{slug:string, public_slug:string, usable:bool}>
     */
    public function sitemapEntryMetadata(array $courseSlugs): array
    {
        $wanted = array_fill_keys(array_map([SentenceBuilderBranding::class, 'legacyCourseSlug'], $courseSlugs), true);
        if ($wanted === []) {
            return [];
        }
        $linked = static fn () => Question::query()->whereExists(function ($query): void {
            $query->selectRaw('1')->from('saved_grammar_test_questions')
                ->whereColumn('saved_grammar_test_questions.question_uuid', 'questions.uuid')
                ->whereColumn('saved_grammar_test_questions.saved_grammar_test_id', 'saved_grammar_tests.id');
        });
        $usable = app(GrammarTestFilterService::class)->constrainUsableQuestions($linked());
        // Normalize the same JSON/string filter representations as build(); only
        // candidate course IDs then reach the bounded question-existence query.
        $tests = SavedGrammarTest::query()->get(['id', 'slug', 'filters'])
            ->filter(fn (SavedGrammarTest $test): bool => isset($wanted[trim((string) (ComposeModeEligibility::normalizedFilters($test)['course_slug'] ?? ''))]));
        $flags = collect();
        foreach (array_chunk($tests->modelKeys(), 250) as $ids) {
            $batch = SavedGrammarTest::query()->select('id')->whereKey($ids)
                ->selectSub($linked()->where('questions.type', Question::TYPE_COMPOSE_TOKENS)->selectRaw('1')->limit(1), 'sitemap_compose')
                ->selectSub((clone $usable)->selectRaw('1')->limit(1), 'sitemap_usable')
                ->selectSub((clone $usable)->where('questions.type', Question::TYPE_COMPOSE_TOKENS)->selectRaw('1')->limit(1), 'sitemap_usable_compose')
                ->get();
            $flags = $flags->union($batch->keyBy('id'));
        }
        $courses = [];
        foreach ($tests as $test) {
            $filters = ComposeModeEligibility::normalizedFilters($test);
            $course = trim((string) ($filters['course_slug'] ?? ''));
            $slug = trim((string) $test->slug);
            $supportsFilters = ComposeModeEligibility::supportsFilters($filters);
            $state = $flags->get($test->id);
            if ($slug === '' || ! $state || (! $supportsFilters && ! $state->sitemap_compose)) {
                continue;
            }
            $order = (int) ($filters['lesson_order'] ?? 0);
            $courses[$course][] = ['slug' => $slug, 'public_slug' => SentenceBuilderBranding::canonicalLessonSlug($slug),
                // Filter-mode saved tests ignore their persisted links in the resolver.
                // Their independent pool contract is deliberately outside this course batch.
                'usable' => (string) $test->slug === $slug
                    && strpbrk($slug, '/?#\\') === false
                    && preg_match('/[\x00-\x20\x7f]/', $slug) === 0
                    && data_get($filters, '__meta.mode') !== 'filters'
                    && (bool) ($supportsFilters ? $state->sitemap_usable : $state->sitemap_usable_compose),
                'order' => $order > 0 ? $order : PHP_INT_MAX];
        }
        $entries = [];
        foreach ($courses as $course => $lessons) {
            usort($lessons, static fn (array $left, array $right): int => [$left['order'], $left['slug']] <=> [$right['order'], $right['slug']]);
            $entries[$course] = array_diff_key($lessons[0], ['order' => true]);
        }
        $shadowed = $entries === [] ? [] : Test::query()->whereIn('slug', array_column($entries, 'slug'))->pluck('slug')->all();
        foreach ($entries as &$entry) {
            // SavedTestResolver gives the legacy Test table precedence at the same slug.
            $entry['usable'] = $entry['usable'] && ! in_array($entry['slug'], $shadowed, true);
        }
        unset($entry);

        return $entries;
    }

    public function build(string $courseSlug): array
    {
        $courseSlug = SentenceBuilderBranding::legacyCourseSlug($courseSlug);

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
        $slug = SentenceBuilderBranding::legacyLessonSlug($slug);

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
        $publicSlug = SentenceBuilderBranding::canonicalLessonSlug($slug);

        if ($slug === '') {
            return null;
        }

        $lessonOrder = (int) ($filters['lesson_order'] ?? 0);
        if ($lessonOrder <= 0) {
            $lessonOrder = PHP_INT_MAX;
        }

        return [
            'slug' => $slug,
            'public_slug' => $publicSlug,
            'name' => SentenceBuilderBranding::publicText(trim((string) ($test->name ?? $slug))),
            'description' => SentenceBuilderBranding::publicText(trim((string) ($test->description ?? ''))),
            'topic' => $this->nullableString($filters['topic'] ?? null),
            'level' => $this->nullableString($filters['level'] ?? null),
            'lesson_order' => $lessonOrder,
            'previous_lesson_slug' => $this->nullableString($filters['previous_lesson_slug'] ?? null),
            'next_lesson_slug' => $this->nullableString($filters['next_lesson_slug'] ?? null),
            'compose_url' => localized_route('test.step-compose', $publicSlug),
            'question_count' => $test->questionLinks->count(),
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
        $publicCourseSlug = SentenceBuilderBranding::canonicalCourseSlug($courseSlug);

        return [
            'slug' => $courseSlug,
            'public_slug' => $publicCourseSlug,
            'name' => SentenceBuilderBranding::courseTitle($courseSlug),
            'description' => is_array($firstLesson) ? SentenceBuilderBranding::publicText((string) ($firstLesson['description'] ?? '')) : '',
            'compose_url' => localized_route('courses.show', $publicCourseSlug),
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
        $publicCourseSlug = SentenceBuilderBranding::canonicalCourseSlug($courseSlug);

        return [
            'course' => [
                'slug' => $courseSlug,
                'public_slug' => $publicCourseSlug,
                'name' => SentenceBuilderBranding::courseTitle($courseSlug),
                'description' => '',
                'compose_url' => $courseSlug !== '' ? localized_route('courses.show', $publicCourseSlug) : null,
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
