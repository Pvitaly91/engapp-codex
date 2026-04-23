<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ChangedContentPlanService;
use Illuminate\Console\Command;
use Throwable;

class ContentPlanChangedCommand extends Command
{
    protected $signature = 'content:plan-changed
        {target? : Optional folder root, package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--domains= : v3,page-v3 | v3 | page-v3}
        {--base= : Base git ref for ref-diff mode}
        {--head= : Head git ref for ref-diff mode}
        {--staged : Diff staged changes against HEAD}
        {--working-tree : Diff the working tree against HEAD}
        {--include-untracked : Include untracked files as added package signals}
        {--dry-run : Ignored because content:plan-changed is already read-only}
        {--force : Ignored because content:plan-changed is already read-only}
        {--with-release-check : Add release-check summaries for current actionable packages}
        {--check-profile=release : scaffold | release}
        {--json : Output the unified changed-content plan as JSON}
        {--write-report : Write a Markdown report into storage/app/content-changed-plans}
        {--strict : Treat planner warnings, inconsistent states, and release-check warnings as failures}';

    protected $description = 'Build a unified read-only git-diff-aware changed-content plan for V3 and Page_V3 JSON packages.';

    public function __construct(
        private readonly ChangedContentPlanService $changedContentPlanService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));

        try {
            $result = $this->changedContentPlanService->run($target !== '' ? $target : null, [
                'domains' => $this->option('domains'),
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
            $result = $this->errorResult($target, $exception);
        }

        if ($this->option('write-report')) {
            $path = $this->changedContentPlanService->writeReport($result);
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

        $this->line('Command: content:plan-changed');
        $this->line('Diff Mode: ' . (string) ($result['diff']['mode'] ?? 'working_tree'));
        $this->line('Domains: ' . implode(', ', (array) ($result['scope']['domains'] ?? [])));
        $this->line('Target Scope: ' . (string) (($result['scope']['input'] ?? null) ?: 'database/seeders/V3 + database/seeders/Page_V3'));
        $this->line('Release Check: ' . (((bool) ($result['scope']['with_release_check'] ?? false)) ? 'enabled' : 'skipped'));
        $this->newLine();

        $this->line(sprintf(
            'Summary: changed=%d; deleted-cleanup=%d; seed=%d; refresh=%d; skipped=%d; blocked=%d; warnings=%d',
            (int) ($result['summary']['changed_packages'] ?? 0),
            (int) ($result['summary']['deleted_cleanup_candidates'] ?? 0),
            (int) ($result['summary']['seed_candidates'] ?? 0),
            (int) ($result['summary']['refresh_candidates'] ?? 0),
            (int) ($result['summary']['skipped'] ?? 0),
            (int) ($result['summary']['blocked'] ?? 0),
            (int) ($result['summary']['warnings'] ?? 0)
        ));
        $this->newLine();

        $this->line('Deleted Packages Cleanup Phase');
        $this->renderPackageLines((array) ($result['phases']['cleanup_deleted'] ?? []));
        $this->newLine();

        $this->line('Current Packages Upsert Phase');
        $this->renderPackageLines((array) ($result['phases']['upsert_present'] ?? []));

        $otherPackages = array_values(array_filter(
            (array) ($result['packages'] ?? []),
            static fn (array $package): bool => ! in_array((string) ($package['recommended_phase'] ?? 'none'), ['cleanup_deleted', 'upsert_present'], true)
        ));

        if ($otherPackages !== []) {
            $this->newLine();
            $this->line('Other Changed Packages');
            $this->renderPackageLines($otherPackages);
        }

        if (empty($result['error'])) {
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
     * @param  list<array<string, mixed>>  $packages
     */
    private function renderPackageLines(array $packages): void
    {
        if ($packages === []) {
            $this->line('- None.');

            return;
        }

        foreach ($packages as $package) {
            $this->line(sprintf(
                '- [%s] %s | type=%s | change=%s | state=%s | action=%s | release=%s',
                (string) ($package['domain'] ?? 'unknown'),
                (string) (($package['relative_path'] ?? null) ?: ($package['package_key'] ?? '')),
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
    private function applyChangedHint(array $result): string
    {
        $parts = ['php artisan content:apply-changed'];
        $target = $result['scope']['input'] ?? null;

        if (is_string($target) && $target !== '') {
            $parts[] = $this->shellEscapeArgument($target);
        }

        $domains = (array) ($result['scope']['domains'] ?? []);

        if ($domains !== ['v3', 'page-v3']) {
            $parts[] = '--domains=' . implode(',', $domains);
        }

        $parts = array_merge($parts, $this->diffModeParts($result));
        $parts[] = '--dry-run';

        if ((bool) ($result['scope']['with_release_check'] ?? false)) {
            $parts[] = '--with-release-check';
        }

        if (($result['scope']['check_profile'] ?? 'release') !== 'release') {
            $parts[] = '--check-profile=' . (string) $result['scope']['check_profile'];
        }

        if ((bool) ($result['scope']['strict'] ?? false)) {
            $parts[] = '--strict';
        }

        return implode(' ', $parts);
    }

    /**
     * @param  array<string, mixed>  $result
     * @return list<string>
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

    /**
     * @return array<string, mixed>
     */
    private function errorResult(string $target, Throwable $exception): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => $this->option('base') ?: null,
                'head' => $this->option('head') ?: null,
                'include_untracked' => (bool) $this->option('include-untracked'),
            ],
            'scope' => [
                'input' => $target !== '' ? $target : null,
                'domains' => [],
                'resolved_roots' => [],
                'single_package' => false,
                'with_release_check' => (bool) $this->option('with-release-check'),
                'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
                'strict' => (bool) $this->option('strict'),
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
            'domains' => [
                'v3' => null,
                'page-v3' => null,
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

    /**
     * @param  array<string, mixed>  $result
     */
    private function exitCodeForResult(array $result): int
    {
        return empty($result['error']) ? self::SUCCESS : self::FAILURE;
    }

    private function shellEscapeArgument(string $value): string
    {
        $escaped = str_replace('"', '\"', $value);

        return '"' . $escaped . '"';
    }
}
