<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\TheoryCourseManifestService;
use App\Services\TheoryCourseTestPoolService;
use App\Support\AdminDebugAccess;
use Illuminate\Support\Collection;

class TheoryCourseController extends Controller
{
    public function __construct(
        private TheoryCourseManifestService $manifestService,
        private TheoryCourseTestPoolService $testPoolService,
    ) {}

    public function show()
    {
        $manifest = $this->manifestService->build();

        abort_if(($manifest['total_lessons'] ?? 0) < 1, 404);

        return view('courses.theory-course', [
            'course' => $manifest['course'],
            'manifest' => $manifest,
            'sections' => $manifest['sections'],
            'lessons' => $manifest['flat_lessons'],
            'firstLesson' => $this->manifestService->firstLesson($manifest),
        ]);
    }

    public function lesson(string $categoryPath, string $pageSlug)
    {
        $manifest = $this->manifestService->build();
        $lesson = $this->manifestService->findLesson($manifest, $categoryPath, $pageSlug);

        abort_unless(is_array($lesson), 404);

        $page = $this->manifestService->resolvePageForLesson($lesson);

        abort_unless($page instanceof Page, 404);

        [$page, $locale] = $this->filterPageBlocksByLocale($page);

        return view('courses.theory-lesson', [
            'course' => $manifest['course'],
            'manifest' => $manifest,
            'lesson' => $lesson,
            'page' => $page,
            'locale' => $locale,
            'previousLesson' => $this->manifestService->previousLesson($manifest, $lesson['lesson_slug']),
            'nextLesson' => $this->manifestService->nextLesson($manifest, $lesson['lesson_slug']),
            'poolSummary' => $this->testPoolService->summaryForPage($page),
            'isAdmin' => AdminDebugAccess::allowed(request()),
        ]);
    }

    public function test(string $categoryPath, string $pageSlug)
    {
        $manifest = $this->manifestService->build();
        $lesson = $this->manifestService->findLesson($manifest, $categoryPath, $pageSlug);

        abort_unless(is_array($lesson), 404);

        $page = $this->manifestService->resolvePageForLesson($lesson);

        abort_unless($page instanceof Page, 404);

        [$page] = $this->filterPageBlocksByLocale($page);

        $attemptSeed = trim((string) request()->query('attempt', ''));
        $pool = $this->testPoolService->buildForPage(
            $page,
            $attemptSeed !== '' ? $lesson['lesson_slug'].'|'.$attemptSeed : null
        );

        return view('test-modes.theory-mixed', [
            'course' => $manifest['course'],
            'manifest' => $manifest,
            'lesson' => $lesson,
            'page' => $page,
            'previousLesson' => $this->manifestService->previousLesson($manifest, $lesson['lesson_slug']),
            'nextLesson' => $this->manifestService->nextLesson($manifest, $lesson['lesson_slug']),
            'pool' => $pool,
            'questions' => $pool['questions'] ?? [],
            'completion' => $pool['completion'] ?? [],
            'isAdmin' => AdminDebugAccess::allowed(request()),
        ]);
    }

    private function filterPageBlocksByLocale(Page $page): array
    {
        $blocks = $page->textBlocks ?? collect();
        $preferred = app()->getLocale() ?: $this->fallbackLocale();
        $fallback = $this->fallbackLocale();
        $locale = $this->chooseLocaleForBlocks($blocks, $preferred, $fallback);

        $page->setRelation(
            'textBlocks',
            $blocks->where('locale', $locale)->values()
        );

        return [$page, $locale];
    }

    private function chooseLocaleForBlocks(Collection $blocks, string $preferred, string $fallback): string
    {
        if ($blocks->firstWhere('locale', $preferred)) {
            return $preferred;
        }

        if ($blocks->firstWhere('locale', $fallback)) {
            return $fallback;
        }

        return $preferred;
    }

    private function fallbackLocale(): string
    {
        return config('language-manager.fallback_locale', config('app.locale', 'uk'));
    }
}
