<?php

namespace App\Console\Commands;

use App\Services\PageV3PromptGenerator\PageV3ChangedPackagesPlanService;
use Illuminate\Console\Command;
use Throwable;

class PageV3PlanChangedCommand extends Command
{
    protected $signature = 'page-v3:plan-changed
        {target? : Optional folder root, package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--base= : Base git ref for ref-diff mode}
        {--head= : Head git ref for ref-diff mode}
        {--staged : Diff staged changes against HEAD}
        {--working-tree : Diff the working tree against HEAD}
        {--include-untracked : Include untracked files as added package signals}
        {--with-release-check : Add release-check summaries for current actionable packages}
        {--check-profile=release : scaffold | release}
        {--json : Output the changed-package plan as JSON}
        {--write-report : Write a Markdown report into storage/app/changed-package-plans/page-v3}
        {--strict : Treat planner warnings, inconsistent states, and release-check warnings as failures}';

    protected $description = 'Build a read-only git-diff-aware changed-package plan for Page_V3 JSON packages.';

    public function __construct(
        private readonly PageV3ChangedPackagesPlanService $changedPackagesPlanService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));

        try {
            $result = $this->changedPackagesPlanService->run($target !== '' ? $target : null, [
                'base' => (string) ($this->option('base') ?? ''),
                'head' => (string) ($this->option('head') ?? ''),
                'staged' => (bool) $this->option('staged'),
                'working_tree' => (bool) $this->option('working-tree'),
                'include_untracked' => (bool) $this->option('include-untracked'),
                'with_release_check' => (bool) $this->option('with-release-check'),
                'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
                'strict' => (bool) $this->option('strict'),
            ]);
        } catch (Throwable $exception) {
            $result = [
                'diff' => [
                    'mode' => 'working_tree',
                    'base' => $this->option('base') ?: null,
                    'head' => $this->option('head') ?: null,
                    'include_untracked' => (bool) $this->option('include-untracked'),
                ],
                'scope' => [
                    'input' => $target !== '' ? $target : 'database/seeders/Page_V3',
                    'resolved_root_absolute_path' => null,
                    'resolved_root_relative_path' => null,
                    'single_package' => false,
                ],
                'summary' => [
                    'changed_packages' => 0,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                    'deleted_cleanup_candidates' => 0,
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'packages' => [],
                'artifacts' => [
                    'report_path' => null,
                ],
                'error' => [
                    'stage' => 'planning',
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ],
            ];
        }

        if ($this->option('write-report')) {
            $path = $this->changedPackagesPlanService->writeReport($result);
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

        $this->line('Command: page-v3:plan-changed');
        $this->line('Diff Mode: ' . (string) ($result['diff']['mode'] ?? 'working_tree'));
        $this->line('Target Scope: ' . (string) ($result['scope']['input'] ?? 'database/seeders/Page_V3'));
        $this->line('Scope Root: ' . (string) ($result['scope']['resolved_root_relative_path'] ?? 'unknown'));
        $this->line('Release Check: ' . ((bool) $this->option('with-release-check') ? 'enabled' : 'skipped'));
        $this->newLine();

        $this->line(sprintf(
            'Summary: changed=%d; seed=%d; refresh=%d; deleted-cleanup=%d; skipped=%d; blocked=%d; warnings=%d',
            (int) ($result['summary']['changed_packages'] ?? 0),
            (int) ($result['summary']['seed_candidates'] ?? 0),
            (int) ($result['summary']['refresh_candidates'] ?? 0),
            (int) ($result['summary']['deleted_cleanup_candidates'] ?? 0),
            (int) ($result['summary']['skipped'] ?? 0),
            (int) ($result['summary']['blocked'] ?? 0),
            (int) ($result['summary']['warnings'] ?? 0)
        ));
        $this->newLine();

        $this->line('Deleted Packages Cleanup Phase:');
        $this->renderPackageLines((array) ($result['phases']['cleanup_deleted'] ?? []));
        $this->newLine();

        $this->line('Current Packages Upsert Phase:');
        $this->renderPackageLines((array) ($result['phases']['upsert_present'] ?? []));

        $otherPackages = array_values(array_filter(
            (array) ($result['packages'] ?? []),
            static fn (array $package): bool => ! in_array((string) ($package['recommended_phase'] ?? 'none'), ['cleanup_deleted', 'upsert_present'], true)
        ));

        if ($otherPackages !== []) {
            $this->newLine();
            $this->line('Other Changed Packages:');
            $this->renderPackageLines($otherPackages);
        }

        $nextSteps = collect((array) ($result['packages'] ?? []))
            ->pluck('next_step_hint')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($nextSteps !== []) {
            $this->newLine();
            $this->line('Next Steps:');

            foreach ($nextSteps as $hint) {
                $this->line('- ' . (string) $hint);
            }
        }

        if (empty($result['error']) && (int) ($result['summary']['changed_packages'] ?? 0) > 0) {
            $this->newLine();
            $this->line('Apply Hint:');
            $this->line($this->applyChangedHint($result));
        }

        if (! empty($result['artifacts']['report_path'])) {
            $this->newLine();
            $this->line('Report: ' . (string) $result['artifacts']['report_path']);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     */
    private function renderPackageLines(array $packages): void
    {
        if ($packages === []) {
            $this->line('- None.');

            return;
        }

        foreach ($packages as $package) {
            $path = (string) (($package['current_relative_path'] ?? null)
                ?: ($package['historical_relative_path'] ?? null)
                ?: ($package['package_key'] ?? ''));

            $this->line(sprintf(
                '- %s | type=%s | change=%s | state=%s | action=%s | release=%s',
                $path,
                (string) ($package['package_type'] ?? 'unknown'),
                (string) ($package['change_type'] ?? 'modified'),
                (string) ($package['package_state'] ?? 'unknown'),
                (string) ($package['recommended_action'] ?? 'skip'),
                (string) ($package['release_check']['status'] ?? 'skipped')
            ));
        }
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
    private function applyChangedHint(array $result): string
    {
        $target = (string) (($result['scope']['resolved_root_relative_path'] ?? null) ?: ($result['scope']['input'] ?? 'database/seeders/Page_V3'));
        $parts = [
            'php artisan page-v3:apply-changed',
            $this->shellEscapeArgument($target),
        ];

        $parts = array_merge($parts, $this->diffModeParts($result));
        $parts[] = '--dry-run';

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

    /**
     * @param  array<string, mixed>  $result
     * @return array<int, string>
     */
    private function diffModeParts(array $result): array
    {
        $parts = match ((string) ($result['diff']['mode'] ?? 'working_tree')) {
            'staged' => ['--staged'],
            'refs' => array_values(array_filter([
                ($result['diff']['base'] ?? null) ? '--base=' . (string) $result['diff']['base'] : null,
                ($result['diff']['head'] ?? null) ? '--head=' . (string) $result['diff']['head'] : null,
            ])),
            default => [],
        };

        if ((bool) ($result['diff']['include_untracked'] ?? false)) {
            $parts[] = '--include-untracked';
        }

        return $parts;
    }

    private function shellEscapeArgument(string $value): string
    {
        return preg_match('/\s/', $value) === 1
            ? '"' . str_replace('"', '\"', $value) . '"'
            : $value;
    }
}
