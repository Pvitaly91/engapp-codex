<?php

namespace App\Console\Commands;

use App\Services\PolyglotCourseReleaseCheckService;
use Illuminate\Console\Command;
use Throwable;

class PolyglotReleaseCheckCommand extends Command
{
    protected $signature = 'polyglot:release-check
        {courseSlug}
        {--json : Output the release check as JSON}
        {--write-report : Write the JSON release report into storage/app/polyglot-reports}
        {--strict : Treat warnings as failures}';

    protected $description = 'Run release-readiness checks for a Polyglot course.';

    public function __construct(
        private readonly PolyglotCourseReleaseCheckService $releaseCheckService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $courseSlug = trim((string) $this->argument('courseSlug'));

        try {
            $report = $this->releaseCheckService->run($courseSlug);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($this->option('write-report')) {
            $path = $this->releaseCheckService->writeReport($courseSlug, $report);
            $report['artifacts']['release_report_path'] = storage_path('app/' . $path);
        }

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $this->exitCodeForReport($report);
        }

        $this->line('Course: ' . ($report['course']['name'] ?? $courseSlug));
        $this->line('Slug: ' . $courseSlug);
        $this->newLine();
        $this->info(sprintf(
            'Implemented total: %d / %d',
            (int) ($report['summary']['implemented_total'] ?? 0),
            (int) ($report['summary']['planned_total'] ?? 0)
        ));
        $this->line('Course status: ' . (($report['summary']['fully_complete'] ?? false) ? 'fully complete' : 'incomplete'));
        $this->line('Remaining planned lessons: ' . (($report['summary']['planned_only_total'] ?? 0) === 0 ? 'none' : (string) $report['summary']['planned_only_total']));
        $this->line('Next recommended lesson: ' . ($report['summary']['next_recommended_lesson'] ?? 'none'));
        $this->newLine();

        foreach ($report['checks'] as $check) {
            $status = strtoupper((string) ($check['status'] ?? 'FAIL'));
            $label = (string) ($check['label'] ?? 'Unnamed check');
            $meta = $this->formatCheckMeta($check['meta'] ?? []);

            $this->line(sprintf(
                '[%s] %s%s',
                $status,
                $label,
                $meta !== '' ? ' — ' . $meta : ''
            ));
        }

        $this->newLine();
        $this->line(sprintf(
            'Summary: %d PASS / %d WARN / %d FAIL',
            (int) ($report['summary']['check_counts']['pass'] ?? 0),
            (int) ($report['summary']['check_counts']['warn'] ?? 0),
            (int) ($report['summary']['check_counts']['fail'] ?? 0)
        ));

        if (! empty($report['artifacts']['release_report_path'])) {
            $this->line('Report: ' . $report['artifacts']['release_report_path']);
        }

        return $this->exitCodeForReport($report);
    }

    private function exitCodeForReport(array $report): int
    {
        $fails = (int) ($report['summary']['check_counts']['fail'] ?? 0);
        $warnings = (int) ($report['summary']['check_counts']['warn'] ?? 0);

        if ($fails > 0) {
            return self::FAILURE;
        }

        if ($this->option('strict') && $warnings > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function formatCheckMeta(array $meta): string
    {
        $parts = [];

        foreach ($meta as $key => $value) {
            if ($value === null || $value === [] || $value === '') {
                continue;
            }

            $parts[] = sprintf(
                '%s=%s',
                $key,
                is_scalar($value)
                    ? (string) $value
                    : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
        }

        return implode('; ', $parts);
    }
}
