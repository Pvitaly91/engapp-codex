<?php

namespace App\Console\Commands;

use App\Services\PageV3PromptGenerator\PageV3ReleaseCheckService;
use Illuminate\Console\Command;
use Throwable;

class PageV3ReleaseCheckCommand extends Command
{
    protected $signature = 'page-v3:release-check
        {target : Package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--profile=release : scaffold | release}
        {--json : Output the release check as JSON}
        {--write-report : Write a Markdown report into storage/app/release-checks/page-v3}
        {--strict : Treat warnings as failures}';

    protected $description = 'Run preflight release checks for a single Page_V3 JSON package without seeding the database.';

    public function __construct(
        private readonly PageV3ReleaseCheckService $releaseCheckService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));
        $profile = trim((string) $this->option('profile'));

        try {
            $report = $this->releaseCheckService->run($target, $profile);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($this->option('write-report')) {
            $path = $this->releaseCheckService->writeReport($report);
            $report['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $this->exitCodeForReport($report);
        }

        $this->line('Target: ' . ($report['target']['definition_relative_path'] ?? $target));
        $this->line('Package Root: ' . ($report['target']['package_root_relative_path'] ?? 'unknown'));
        $this->line('Profile: ' . ($report['profile'] ?? 'release'));
        $this->newLine();

        foreach ((array) ($report['checks'] ?? []) as $check) {
            $status = strtoupper((string) ($check['status'] ?? 'FAIL'));
            $label = (string) ($check['label'] ?? 'Unnamed check');
            $meta = $this->formatCheckMeta((array) ($check['meta'] ?? []));

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

        if (! empty($report['artifacts']['report_path'])) {
            $this->line('Report: ' . $report['artifacts']['report_path']);
        }

        return $this->exitCodeForReport($report);
    }

    /**
     * @param  array<string, mixed>  $report
     */
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

    /**
     * @param  array<string, mixed>  $meta
     */
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
