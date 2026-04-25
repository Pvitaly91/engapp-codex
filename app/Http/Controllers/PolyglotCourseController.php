<?php

namespace App\Http\Controllers;

use App\Services\PolyglotCourseBlueprintService;
use App\Services\PolyglotCourseManifestService;
use App\Support\AdminDebugAccess;

class PolyglotCourseController extends Controller
{
    public function __construct(
        private PolyglotCourseManifestService $manifestService,
        private PolyglotCourseBlueprintService $blueprintService,
    ) {}

    public function show(string $courseSlug)
    {
        $manifest = $this->manifestService->build($courseSlug);
        $courseStatus = $this->blueprintService->buildCourseStatus($courseSlug);

        abort_if(($manifest['total_lessons'] ?? 0) < 1, 404);

        return view('courses.show', [
            'course' => $courseStatus['course'],
            'manifest' => $manifest,
            'lessons' => $manifest['lessons'],
            'displayLessons' => $courseStatus['display_lessons'],
            'firstLesson' => $this->manifestService->firstLesson($manifest),
            'totalLessons' => $this->manifestService->totalLessons($manifest),
            'plannedTotalLessons' => $courseStatus['counts']['planned_total'],
            'implementedLessonsCount' => $courseStatus['counts']['implemented_total'],
            'plannedLessonsCount' => $courseStatus['counts']['planned_only_total'],
            'nextPlannedLesson' => $courseStatus['next_planned_lesson'],
            'courseBlueprint' => $courseStatus['blueprint'],
            'isAdmin' => AdminDebugAccess::allowed(request()),
        ]);
    }
}
