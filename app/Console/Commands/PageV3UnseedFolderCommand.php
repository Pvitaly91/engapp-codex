<?php

namespace App\Console\Commands;

use App\Services\PageV3PromptGenerator\PageV3FolderUnseedService;
use Illuminate\Console\Command;
use Throwable;

class PageV3UnseedFolderCommand extends Command
{
    protected $signature = 'page-v3:unseed-folder
        {target : Folder root, package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--dry-run : Execute the full folder preflight in rollback-only package delete mode}
        {--force : Allow live folder unseed after a clean full-scope preflight}
        {--json : Output the folder unseed result as JSON}
        {--write-report : Write a Markdown report into storage/app/folder-unseed-reports/page-v3}
        {--strict : Treat planner or preflight warnings as failures}';

    protected $description = 'Unseed one resolved Page_V3 subtree from the database using planner-driven preflight and package-by-package delete execution.';

    public function __construct(
        private readonly PageV3FolderUnseedService $folderUnseedService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));
        $mode = $this->modePayload();

        try {
            $result = $this->folderUnseedService->run($target, [
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'strict' => (bool) $this->option('strict'),
            ]);
        } catch (Throwable $exception) {
            $result = $this->errorResult($target, $mode, $exception);
        }

        if ($this->option('write-report')) {
            $path = $this->folderUnseedService->writeReport($result);
            $result['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $this->exitCodeForResult($result);
        }

        $this->renderHumanOutput($result);

        return $this->exitCodeForResult($result);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderHumanOutput(array $result): void
    {
        if (! empty($result['error']['message'])) {
            $this->error((string) $result['error']['message']);
            $this->newLine();
        }

        $this->line('Command: page-v3:unseed-folder');
        $this->line('Target: ' . (string) ($result['scope']['input'] ?? 'unknown'));
        $this->line('Scope Root: ' . (string) ($result['scope']['resolved_root_relative_path'] ?? 'unknown'));
        $this->line('Execution: ' . ((bool) ($result['execution']['dry_run'] ?? false) ? 'dry-run' : 'live'));
        $this->line('Force: ' . $this->boolString((bool) ($result['execution']['force'] ?? false)));
        $this->line('Strict: ' . $this->boolString((bool) ($result['scope']['strict'] ?? false)));
        $this->line('Package Count: ' . (int) ($result['plan']['summary']['total_packages'] ?? 0));
        $this->newLine();

        $this->line(sprintf(
            'Summary: total=%d; unseed=%d; skipped=%d; blocked=%d; warnings=%d',
            (int) ($result['plan']['summary']['total_packages'] ?? 0),
            (int) ($result['plan']['summary']['unseed_candidates'] ?? 0),
            (int) ($result['plan']['summary']['skipped'] ?? 0),
            (int) ($result['plan']['summary']['blocked'] ?? 0),
            (int) ($result['plan']['summary']['warnings'] ?? 0)
        ));
        $this->line(sprintf(
            'Preflight: candidates=%d; ok=%d; warn=%d; fail=%d; skipped=%d',
            (int) ($result['preflight']['summary']['candidates'] ?? 0),
            (int) ($result['preflight']['summary']['ok'] ?? 0),
            (int) ($result['preflight']['summary']['warn'] ?? 0),
            (int) ($result['preflight']['summary']['fail'] ?? 0),
            (int) ($result['preflight']['summary']['skipped'] ?? 0)
        ));
        $this->line('Execution Contract: global-preflight; planner-ordered; package-atomic; folder-non-transactional; fail-fast');
        $this->newLine();

        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            $preflight = (array) (($result['preflight']['packages'][$index] ?? []) ?: []);
            $execution = (array) (($result['execution']['packages'][$index] ?? []) ?: []);

            $this->line(sprintf(
                '- %s | action=%s | preflight=%s | execution=%s',
                (string) ($package['relative_path'] ?? 'unknown'),
                (string) ($package['recommended_action'] ?? 'skip'),
                (string) ($preflight['status'] ?? 'skip'),
                (string) ($execution['status'] ?? 'pending')
            ));
        }

        if (! empty($result['error']['package'])) {
            $this->newLine();
            $this->line('Stopped On: ' . (string) $result['error']['package']);
        }

        if (! empty($result['artifacts']['report_path'])) {
            $this->newLine();
            $this->line('Report: ' . (string) $result['artifacts']['report_path']);
        }

        if ((bool) ($result['execution']['dry_run'] ?? false) && empty($result['error'])) {
            $this->newLine();
            $this->line('Live Unseed Hint:');
            $this->line($this->liveCommandHint($result));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function modePayload(): array
    {
        return [
            'dry_run' => (bool) $this->option('dry-run'),
            'force' => (bool) $this->option('force'),
            'strict' => (bool) $this->option('strict'),
        ];
    }

    /**
     * @param  array<string, mixed>  $mode
     * @return array<string, mixed>
     */
    private function errorResult(string $target, array $mode, Throwable $exception): array
    {
        return [
            'scope' => [
                'input' => $target,
                'resolved_root_absolute_path' => null,
                'resolved_root_relative_path' => null,
                'single_package' => false,
                'mode' => 'unseed',
                'strict' => (bool) $mode['strict'],
            ],
            'plan' => [
                'summary' => $this->emptyPlanSummary(),
                'packages' => [],
                'error' => null,
            ],
            'preflight' => [
                'executed' => false,
                'packages' => [],
                'summary' => $this->emptyPreflightSummary(),
            ],
            'execution' => [
                'dry_run' => (bool) $mode['dry_run'],
                'force' => (bool) $mode['force'],
                'fail_fast' => true,
                'package_atomic' => true,
                'folder_transactional' => false,
                'started' => 0,
                'completed' => 0,
                'succeeded' => 0,
                'failed' => 0,
                'stopped_on' => null,
                'packages' => [],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'target_resolution',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function exitCodeForResult(array $result): int
    {
        return empty($result['error']) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<string, int>
     */
    private function emptyPlanSummary(): array
    {
        return [
            'total_packages' => 0,
            'seed_candidates' => 0,
            'refresh_candidates' => 0,
            'unseed_candidates' => 0,
            'destroy_files_candidates' => 0,
            'destroy_candidates' => 0,
            'skipped' => 0,
            'blocked' => 0,
            'warnings' => 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptyPreflightSummary(): array
    {
        return [
            'candidates' => 0,
            'ok' => 0,
            'warn' => 0,
            'fail' => 0,
            'skipped' => 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function liveCommandHint(array $result): string
    {
        $target = (string) (($result['scope']['resolved_root_relative_path'] ?? null) ?: ($result['scope']['input'] ?? ''));
        $parts = [
            'php artisan page-v3:unseed-folder',
            $this->shellEscapeArgument($target),
            '--force',
        ];

        if ((bool) ($result['scope']['strict'] ?? false)) {
            $parts[] = '--strict';
        }

        return implode(' ', $parts);
    }

    private function shellEscapeArgument(string $value): string
    {
        return preg_match('/\s/', $value) === 1
            ? '"' . str_replace('"', '\"', $value) . '"'
            : $value;
    }

    private function boolString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
