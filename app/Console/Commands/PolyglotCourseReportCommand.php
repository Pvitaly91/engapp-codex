<?php

namespace App\Console\Commands;

use App\Services\PolyglotCourseBlueprintService;
use Illuminate\Console\Command;
use Throwable;

class PolyglotCourseReportCommand extends Command
{
    protected $signature = 'polyglot:course-report {courseSlug}';

    protected $description = 'Show planned vs implemented status for a Polyglot course blueprint.';

    public function __construct(
        private readonly PolyglotCourseBlueprintService $blueprintService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $courseSlug = trim((string) $this->argument('courseSlug'));

        try {
            $status = $this->blueprintService->buildCourseStatus($courseSlug);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $counts = $status['counts'];
        $validation = $status['validation'];
        $implementedRoutes = $status['implemented_routes'];
        $missingSlugs = array_map(
            fn (array $lesson) => $lesson['slug'],
            $status['planned_lessons']
        );
        $nextPlannedLesson = $status['next_planned_lesson']['slug'] ?? null;

        $this->line('Course: ' . ($status['course']['name'] ?? $courseSlug));
        $this->line('Blueprint: ' . ($status['blueprint']['path'] ?? 'n/a'));
        $this->newLine();
        $this->info('Planned total: ' . $counts['planned_total']);
        $this->info('Implemented total: ' . $counts['implemented_total']);
        $this->line('Missing / planned lessons: ' . ($missingSlugs !== [] ? implode(', ', $missingSlugs) : 'none'));
        $this->line('Next recommended lesson: ' . ($nextPlannedLesson ?? 'none'));
        $this->newLine();
        $this->line('Available routes for implemented lessons:');

        if ($implementedRoutes === []) {
            $this->line(' - none');
        } else {
            foreach ($implementedRoutes as $route) {
                $this->line(' - ' . $route);
            }
        }

        $brokenRefs = array_merge(
            $validation['broken_previous_refs'] ?? [],
            $validation['broken_next_refs'] ?? [],
            $validation['graph_mismatches'] ?? []
        );

        $this->newLine();
        $this->line('Broken previous/next refs: ' . ($brokenRefs === [] ? 'none' : count($brokenRefs)));

        if ($brokenRefs !== []) {
            foreach ($brokenRefs as $brokenRef) {
                $this->line(' - ' . json_encode($brokenRef, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }
        }

        return self::SUCCESS;
    }
}
