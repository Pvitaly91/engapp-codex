<?php

namespace App\Services;

use App\Models\Page;
use App\Models\SavedGrammarTest;
use App\Support\PromptGeneratorFilterNormalizer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PolyglotCourseReleaseCheckService
{
    public function __construct(
        private readonly PolyglotCourseBlueprintService $blueprintService,
        private readonly TheoryPagePromptLinkedTestsService $theoryPagePromptLinkedTestsService,
    ) {}

    public function run(string $courseSlug): array
    {
        $courseSlug = trim($courseSlug);

        if ($courseSlug === '') {
            throw new RuntimeException('Polyglot course slug cannot be empty.');
        }

        $status = $this->blueprintService->buildCourseStatus($courseSlug);
        $blueprint = $status['blueprint'];
        $manifest = $status['manifest'];
        $blueprintLessons = $blueprint['lessons'] ?? [];
        $implementedLessons = $status['implemented_lessons'] ?? [];
        $finalLesson = collect($blueprintLessons)->last();
        $finalLessonNextSlug = is_array($finalLesson) && array_key_exists('next_lesson_slug', $finalLesson)
            ? $finalLesson['next_lesson_slug']
            : '__missing__';
        $brokenRefs = array_merge(
            $status['validation']['broken_previous_refs'] ?? [],
            $status['validation']['broken_next_refs'] ?? [],
            $status['validation']['graph_mismatches'] ?? [],
            $status['validation']['implemented_missing_from_blueprint'] ?? [],
        );
        $courseRoute = $this->safeRoute('courses.show', [$courseSlug]);
        $homeEntryPoint = $this->viewContainsCourseEntryPoint(
            resource_path('views/home.blade.php'),
            $courseSlug
        );
        $navEntryPoint = $this->viewContainsCourseEntryPoint(
            resource_path('views/layouts/catalog-public.blade.php'),
            $courseSlug
        );
        $lessonReports = collect($blueprintLessons)
            ->map(fn (array $lesson) => $this->buildLessonReport($courseSlug, $lesson))
            ->all();
        $lessonsMissingTheoryBinding = collect($lessonReports)
            ->where('has_theory_binding', false)
            ->pluck('slug')
            ->values()
            ->all();
        $lessonsWithBrokenTheoryRoute = collect($lessonReports)
            ->where('theory_route_resolves', false)
            ->pluck('slug')
            ->values()
            ->all();
        $lessonsWithBrokenComposeRoute = collect($lessonReports)
            ->where('compose_route_resolves', false)
            ->pluck('slug')
            ->values()
            ->all();
        $courseReportEquivalent = [
            'planned_total' => $status['counts']['planned_total'],
            'implemented_total' => $status['counts']['implemented_total'],
            'missing_or_planned_lessons' => $status['planned_lessons'] === []
                ? 'none'
                : collect($status['planned_lessons'])->pluck('slug')->implode(', '),
            'next_recommended_lesson' => $status['next_planned_lesson']['slug'] ?? 'none',
            'broken_previous_next_refs' => $brokenRefs === [] ? 'none' : count($brokenRefs),
        ];

        $checks = collect([
            $this->makeCheck(
                'blueprint_exists',
                'Blueprint exists',
                File::exists($blueprint['path'] ?? ''),
                [
                    'path' => $blueprint['path'] ?? null,
                ]
            ),
            $this->makeCheck(
                'course_counts_complete',
                'Course counts are 16 / 16 and fully implemented',
                (int) ($status['counts']['planned_total'] ?? 0) === count($blueprintLessons)
                    && (int) ($status['counts']['implemented_total'] ?? 0) === count($blueprintLessons)
                    && (int) ($status['counts']['planned_only_total'] ?? 0) === 0,
                [
                    'planned_total' => $status['counts']['planned_total'] ?? 0,
                    'implemented_total' => $status['counts']['implemented_total'] ?? 0,
                    'planned_only_total' => $status['counts']['planned_only_total'] ?? 0,
                ]
            ),
            $this->makeCheck(
                'no_remaining_planned_lessons',
                'No remaining planned lessons',
                ($status['counts']['planned_only_total'] ?? 0) === 0,
                [
                    'planned_only_total' => $status['counts']['planned_only_total'] ?? 0,
                ]
            ),
            $this->makeCheck(
                'no_broken_refs',
                'No broken previous/next refs',
                $brokenRefs === [],
                [
                    'broken_refs' => $brokenRefs,
                ]
            ),
            $this->makeCheck(
                'compose_routes_resolve',
                'All implemented compose lesson routes resolve logically',
                $lessonsWithBrokenComposeRoute === [],
                [
                    'broken_lesson_slugs' => $lessonsWithBrokenComposeRoute,
                ]
            ),
            $this->makeCheck(
                'theory_binding_metadata',
                'Every implemented lesson has theory binding metadata',
                $lessonsMissingTheoryBinding === [],
                [
                    'missing_binding_slugs' => $lessonsMissingTheoryBinding,
                ]
            ),
            $this->makeCheck(
                'theory_pages_resolve',
                'Relevant theory pages resolve',
                $lessonsWithBrokenTheoryRoute === [],
                [
                    'broken_lesson_slugs' => $lessonsWithBrokenTheoryRoute,
                ]
            ),
            $this->makeCheck(
                'course_route_exists',
                'Course landing route resolves',
                $courseRoute !== null && Route::has('courses.show'),
                [
                    'route' => $courseRoute,
                ]
            ),
            $this->makeCheck(
                'public_entry_points',
                'Public home/nav entry points exist',
                $homeEntryPoint['found'] && $navEntryPoint['found'],
                [
                    'home' => $homeEntryPoint,
                    'nav' => $navEntryPoint,
                ]
            ),
            $this->makeCheck(
                'final_lesson_terminal',
                'Final lesson exists and has null next',
                is_array($finalLesson)
                    && ($finalLesson['slug'] ?? null) === 'polyglot-final-drill-a1'
                    && $finalLessonNextSlug === null,
                [
                    'slug' => $finalLesson['slug'] ?? null,
                    'next_lesson_slug' => $finalLessonNextSlug === '__missing__' ? null : $finalLessonNextSlug,
                ]
            ),
            $this->makeCheck(
                'course_report_consistency',
                'Course report and release check are mutually consistent',
                ($courseReportEquivalent['planned_total'] ?? null) === ($status['counts']['planned_total'] ?? null)
                    && ($courseReportEquivalent['implemented_total'] ?? null) === ($status['counts']['implemented_total'] ?? null)
                    && ($courseReportEquivalent['next_recommended_lesson'] ?? null) === 'none'
                    && ($courseReportEquivalent['missing_or_planned_lessons'] ?? null) === 'none',
                $courseReportEquivalent
            ),
        ])->values();

        $counts = [
            'pass' => $checks->where('status', 'PASS')->count(),
            'warn' => $checks->where('status', 'WARN')->count(),
            'fail' => $checks->where('status', 'FAIL')->count(),
        ];

        return [
            'course' => [
                'slug' => $courseSlug,
                'name' => $status['course']['name'] ?? $courseSlug,
                'description' => $status['course']['description'] ?? '',
            ],
            'summary' => [
                'planned_total' => $status['counts']['planned_total'] ?? 0,
                'implemented_total' => $status['counts']['implemented_total'] ?? 0,
                'planned_only_total' => $status['counts']['planned_only_total'] ?? 0,
                'missing_total' => $status['counts']['missing_total'] ?? 0,
                'fully_complete' => ($status['counts']['planned_only_total'] ?? 0) === 0,
                'next_recommended_lesson' => $status['next_planned_lesson']['slug'] ?? 'none',
                'check_counts' => $counts,
            ],
            'checks' => $checks->all(),
            'course_report_equivalent' => $courseReportEquivalent,
            'routes' => [
                'course' => $courseRoute,
                'implemented_lessons' => collect($implementedLessons)
                    ->pluck('compose_url', 'slug')
                    ->all(),
            ],
            'entry_points' => [
                'home' => $homeEntryPoint,
                'nav' => $navEntryPoint,
            ],
            'lessons' => $lessonReports,
            'artifacts' => [
                'blueprint_path' => $blueprint['path'] ?? null,
                'release_report_path' => null,
            ],
        ];
    }

    public function writeReport(string $courseSlug, array $report): string
    {
        $path = sprintf('polyglot-reports/%s-release-check.json', trim($courseSlug));

        Storage::disk('local')->put(
            $path,
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        return $path;
    }

    private function buildLessonReport(string $courseSlug, array $lesson): array
    {
        $slug = (string) ($lesson['slug'] ?? '');
        $composeRoute = $this->safeRoute('test.step-compose', [$slug]);
        $savedTest = SavedGrammarTest::query()
            ->where('slug', $slug)
            ->first();
        $filters = $this->normalizedSavedTestFilters($savedTest);
        $promptGenerator = PromptGeneratorFilterNormalizer::normalize($filters['prompt_generator'] ?? null);
        $binding = is_array($promptGenerator)
            ? (is_array($promptGenerator['theory_page'] ?? null) ? $promptGenerator['theory_page'] : [])
            : [];
        $bindingCategorySlugPath = trim((string) ($binding['category_slug_path'] ?? ($lesson['theory_category_slug'] ?? '')));
        $bindingPageSlug = trim((string) ($binding['slug'] ?? ($lesson['theory_page_slug'] ?? '')));
        $bindingPageSeederClass = trim((string) ($binding['page_seeder_class'] ?? ''));
        $page = $this->resolveTheoryPage(
            $bindingPageSlug,
            $bindingCategorySlugPath,
            $bindingPageSeederClass
        );
        $theoryRoute = $page ? $this->safeRoute('theory.show', [$this->pageCategorySlugPath($page), $page->slug]) : null;

        if ($theoryRoute === null && $bindingCategorySlugPath !== '' && $bindingPageSlug !== '') {
            $theoryRoute = $this->safeRoute('theory.show', [$bindingCategorySlugPath, $bindingPageSlug]);
        }

        $displayMode = 'missing';

        if ($page) {
            $directTests = $this->theoryPagePromptLinkedTestsService->findForPage($page);
            $displayedTests = $this->theoryPagePromptLinkedTestsService->buildForPage($page);
            $directContainsLesson = $directTests->contains(
                fn ($test) => trim((string) ($test->slug ?? '')) === $slug
            );
            $displayContainsLesson = $displayedTests->contains(
                fn ($test) => trim((string) ($test->slug ?? '')) === $slug
            );

            if ($displayContainsLesson || $directContainsLesson) {
                $displayMode = $displayContainsLesson ? 'direct' : 'aggregated';
            }
        }

        return [
            'slug' => $slug,
            'lesson_order' => $lesson['lesson_order'] ?? null,
            'title' => $lesson['title'] ?? null,
            'course_slug' => $courseSlug,
            'compose_route' => $composeRoute,
            'compose_route_resolves' => $composeRoute !== null,
            'has_theory_binding' => $bindingCategorySlugPath !== '' && $bindingPageSlug !== '',
            'theory_binding' => [
                'category_slug_path' => $bindingCategorySlugPath !== '' ? $bindingCategorySlugPath : null,
                'page_slug' => $bindingPageSlug !== '' ? $bindingPageSlug : null,
                'page_seeder_class' => $bindingPageSeederClass !== '' ? $bindingPageSeederClass : null,
            ],
            'theory_route' => $theoryRoute,
            'theory_route_resolves' => $theoryRoute !== null,
            'theory_page_model_found' => $page !== null,
            'theory_route_source' => $page !== null ? 'page_model' : ($theoryRoute !== null ? 'binding' : 'missing'),
            'related_tests_display_mode' => $displayMode,
            'previous_lesson_slug' => $lesson['previous_lesson_slug'] ?? null,
            'next_lesson_slug' => $lesson['next_lesson_slug'] ?? null,
        ];
    }

    private function makeCheck(string $key, string $label, bool $passes, array $meta = [], string $warningWhenFalse = ''): array
    {
        $status = $passes ? 'PASS' : ($warningWhenFalse !== '' ? 'WARN' : 'FAIL');

        return [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'meta' => $meta,
        ];
    }

    private function normalizedSavedTestFilters(?SavedGrammarTest $test): array
    {
        if (! $test) {
            return [];
        }

        $filters = $test->filters ?? [];

        if (is_string($filters)) {
            $decoded = json_decode($filters, true);
            $filters = is_array($decoded) ? $decoded : [];
        }

        return is_array($filters) ? $filters : [];
    }

    private function viewContainsCourseEntryPoint(string $path, string $courseSlug): array
    {
        if (! File::exists($path)) {
            return [
                'found' => false,
                'path' => $path,
                'match' => null,
            ];
        }

        $contents = File::get($path);
        $routePattern = sprintf(
            "/localized_route\\('courses\\.show',\\s*'%s'\\)/",
            preg_quote($courseSlug, '/')
        );
        $fallbackPattern = sprintf(
            '/\\/courses\\/%s/',
            preg_quote($courseSlug, '/')
        );
        $found = preg_match($routePattern, $contents) === 1
            || preg_match($fallbackPattern, $contents) === 1;

        return [
            'found' => $found,
            'path' => $path,
            'match' => $found ? $courseSlug : null,
        ];
    }

    private function resolveTheoryPage(string $pageSlug, string $categorySlugPath, string $pageSeederClass = ''): ?Page
    {
        if ($pageSlug === '') {
            return null;
        }

        $pages = Page::query()
            ->with('category.parent.parent.parent')
            ->where('slug', $pageSlug)
            ->get();

        if ($pages->isEmpty()) {
            return null;
        }

        if ($pageSeederClass !== '') {
            $matchedBySeeder = $pages->first(
                fn (Page $page) => trim((string) ($page->seeder ?? '')) === $pageSeederClass
            );

            if ($matchedBySeeder) {
                return $matchedBySeeder;
            }
        }

        $normalizedCategorySlugPath = $this->normalizeCategorySlugPath($categorySlugPath);
        if ($normalizedCategorySlugPath === '') {
            return $pages->first();
        }

        return $pages->first(
            fn (Page $page) => $this->pageCategorySlugPath($page) === $normalizedCategorySlugPath
        );
    }

    private function pageCategorySlugPath(Page $page): string
    {
        $page->loadMissing('category');
        $category = $page->category;

        if (! $category) {
            return '';
        }

        $segments = [];
        $current = $category;
        $depth = 0;

        while ($current && $depth < 10) {
            $slug = trim((string) ($current->slug ?? ''));

            if ($slug !== '') {
                array_unshift($segments, $slug);
            }

            $current->loadMissing('parent');
            $current = $current->parent;
            $depth++;
        }

        return $this->normalizeCategorySlugPath(implode('/', $segments));
    }

    private function normalizeCategorySlugPath(string $value): string
    {
        return implode('/', array_values(array_filter(
            explode('/', str_replace('\\', '/', Str::lower(trim($value)))),
            'strlen'
        )));
    }

    private function safeRoute(string $name, array $parameters = []): ?string
    {
        if (! Route::has($name)) {
            return null;
        }

        try {
            return localized_route($name, count($parameters) === 1 ? $parameters[0] : $parameters);
        } catch (Throwable) {
            try {
                return route($name, $parameters);
            } catch (Throwable) {
                return null;
            }
        }
    }
}
