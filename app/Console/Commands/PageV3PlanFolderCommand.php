<?php

namespace App\Console\Commands;

use App\Services\PageV3PromptGenerator\PageV3FolderPlanService;
use Illuminate\Console\Command;
use Throwable;

class PageV3PlanFolderCommand extends Command
{
    protected $signature = 'page-v3:plan-folder
        {target : Folder root, package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--mode=sync : missing | refresh | sync | unseed | destroy-files | destroy}
        {--with-release-check : Add per-package release-check summaries}
        {--check-profile=release : scaffold | release}
        {--json : Output the folder plan as JSON}
        {--write-report : Write a Markdown report into storage/app/folder-plans/page-v3}
        {--strict : Treat planner warnings and inconsistent states as failures}';

    protected $description = 'Build a read-only execution plan for one resolved Page_V3 subtree without seeding, refreshing, or deleting.';

    public function __construct(
        private readonly PageV3FolderPlanService $folderPlanService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));
        $mode = $this->modePayload();

        try {
            $result = $this->folderPlanService->run($target, [
                'mode' => (string) $this->option('mode'),
                'with_release_check' => (bool) $this->option('with-release-check'),
                'check_profile' => (string) $this->option('check-profile'),
                'strict' => (bool) $this->option('strict'),
            ]);
        } catch (Throwable $exception) {
            $result = $this->errorResult($target, $mode, $exception);
        }

        if ($this->option('write-report')) {
            $path = $this->folderPlanService->writeReport($result);
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

        $this->line('Command: page-v3:plan-folder');
        $this->line('Target: ' . ((string) ($result['scope']['input'] ?? 'unknown')));
        $this->line('Scope Root: ' . ((string) ($result['scope']['resolved_root_relative_path'] ?? 'unknown')));
        $this->line('Mode: ' . ((string) ($result['scope']['mode'] ?? 'sync')));
        $this->line(
            'Release Check: '
            . ((bool) $this->option('with-release-check') ? 'enabled' : 'skipped')
        );
        $this->line('Package Count: ' . (int) ($result['summary']['total_packages'] ?? 0));
        $this->newLine();

        $this->line(sprintf(
            'Summary: total=%d; seed=%d; refresh=%d; unseed=%d; destroy-files=%d; destroy=%d; skipped=%d; blocked=%d; warnings=%d',
            (int) ($result['summary']['total_packages'] ?? 0),
            (int) ($result['summary']['seed_candidates'] ?? 0),
            (int) ($result['summary']['refresh_candidates'] ?? 0),
            (int) ($result['summary']['unseed_candidates'] ?? 0),
            (int) ($result['summary']['destroy_files_candidates'] ?? 0),
            (int) ($result['summary']['destroy_candidates'] ?? 0),
            (int) ($result['summary']['skipped'] ?? 0),
            (int) ($result['summary']['blocked'] ?? 0),
            (int) ($result['summary']['warnings'] ?? 0)
        ));
        $this->newLine();

        $rows = array_map(function (array $package): array {
            return [
                'Path' => (string) ($package['relative_path'] ?? ''),
                'Type' => (string) ($package['package_type'] ?? 'unknown'),
                'State' => (string) ($package['package_state'] ?? 'unknown'),
                'Action' => (string) ($package['recommended_action'] ?? 'skip'),
                'Release' => (string) ($package['release_check']['status'] ?? 'skipped'),
                'Seeder' => (string) ($package['resolved_seeder_class'] ?? ''),
            ];
        }, (array) ($result['packages'] ?? []));

        if ($rows !== []) {
            $this->table(array_keys($rows[0]), $rows);
        } else {
            $this->line('No packages discovered inside the resolved subtree.');
        }

        $nextSteps = collect((array) ($result['packages'] ?? []))
            ->pluck('next_step_command')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($nextSteps !== []) {
            $this->line('Next Steps:');

            foreach ($nextSteps as $command) {
                $this->line('- ' . (string) $command);
            }
        }

        if ($applyHint = $this->applyHintCommand($result)) {
            $this->newLine();
            $this->line(match ((string) ($result['scope']['mode'] ?? 'sync')) {
                'unseed' => 'Folder Unseed Dry Run:',
                'destroy' => 'Folder Destroy Dry Run:',
                'destroy-files' => 'Destroy Files Dry Run:',
                default => 'Apply Dry Run:',
            });
            $this->line($applyHint);
        }

        if (! empty($result['artifacts']['report_path'])) {
            $this->newLine();
            $this->line('Report: ' . (string) $result['artifacts']['report_path']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function modePayload(): array
    {
        return [
            'mode' => trim((string) ($this->option('mode') ?? 'sync')) ?: 'sync',
            'with_release_check' => (bool) $this->option('with-release-check'),
            'check_profile' => trim((string) ($this->option('check-profile') ?? 'release')) ?: 'release',
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
                'mode' => $mode['mode'],
            ],
            'summary' => [
                'total_packages' => 0,
                'seed_candidates' => 0,
                'refresh_candidates' => 0,
                'unseed_candidates' => 0,
                'destroy_files_candidates' => 0,
                'destroy_candidates' => 0,
                'skipped' => 0,
                'blocked' => 0,
                'warnings' => 0,
            ],
            'packages' => [],
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
     * @param  array<string, mixed>  $result
     */
    private function applyHintCommand(array $result): ?string
    {
        $target = trim((string) (($result['scope']['resolved_root_relative_path'] ?? null) ?: ($result['scope']['input'] ?? '')));

        if ($target === '') {
            return null;
        }

        $parts = [
            'php artisan page-v3:apply-folder',
            $this->shellEscapeArgument($target),
            '--mode=' . (string) ($result['scope']['mode'] ?? 'sync'),
            '--dry-run',
        ];

        if ((string) ($result['scope']['mode'] ?? 'sync') === 'destroy-files') {
            $parts = [
                'php artisan page-v3:destroy-folder-files',
                $this->shellEscapeArgument($target),
                '--dry-run',
            ];

            if ((bool) $this->option('strict')) {
                $parts[] = '--strict';
            }

            return implode(' ', $parts);
        }

        if ((string) ($result['scope']['mode'] ?? 'sync') === 'destroy') {
            $parts = [
                'php artisan page-v3:destroy-folder',
                $this->shellEscapeArgument($target),
                '--dry-run',
            ];

            if ((bool) $this->option('strict')) {
                $parts[] = '--strict';
            }

            return implode(' ', $parts);
        }

        if ((string) ($result['scope']['mode'] ?? 'sync') === 'unseed') {
            $parts = [
                'php artisan page-v3:unseed-folder',
                $this->shellEscapeArgument($target),
                '--dry-run',
            ];

            if ((bool) $this->option('strict')) {
                $parts[] = '--strict';
            }

            return implode(' ', $parts);
        }

        if ((bool) $this->option('with-release-check')) {
            $parts[] = '--with-release-check';
        }

        if (($this->option('check-profile') ?? 'release') !== 'release') {
            $parts[] = '--check-profile=' . (string) $this->option('check-profile');
        }

        if ((bool) $this->option('strict')) {
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
}
