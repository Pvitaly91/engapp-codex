<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ContentReleaseGateService;
use Illuminate\Console\Command;
use Throwable;

class ContentReleaseGateCommand extends Command
{
    protected $signature = 'content:release-gate
        {target? : Optional folder root, package directory, definition.json, loader stub PHP, or real seeder PHP}
        {--base= : Base git ref for ref-diff mode}
        {--head= : Head git ref for ref-diff mode}
        {--staged : Diff staged changes against HEAD}
        {--working-tree : Diff the working tree against HEAD}
        {--include-untracked : Include untracked files as added package signals}
        {--domains= : v3,page-v3 | v3 | page-v3}
        {--with-release-check : Add release-check summaries for current actionable packages}
        {--check-profile=release : scaffold | release}
        {--with-doctor : Include ContentOps doctor checks}
        {--with-git : Include read-only current git ref probing}
        {--with-artifacts : Validate recent content operation artifacts}
        {--with-deployment : Validate GitDeployment ContentOps wiring}
        {--with-package-roots : Validate V3/Page_V3 package roots}
        {--with-dry-plan : Run a harmless read-only changed-content working-tree plan through doctor}
        {--profile=local : local | ci | deployment}
        {--strict : Treat warnings as fatal for the gate exit code}
        {--fail-on-warnings : Fail the gate when any warning is present}
        {--fail-on-lock : Fail when a fresh active content-operation lock exists}
        {--fail-on-stale-lock : Fail when a stale content-operation lock exists}
        {--fail-on-sync-drift : Fail when content sync-state is drifted}
        {--fail-on-uninitialized-sync : Fail when content sync-state is uninitialized}
        {--json : Output the release-gate result as JSON}
        {--write-report : Write a Markdown report into storage/app/content-release-gates}';

    protected $description = 'Run a read-only ContentOps release gate over doctor, changed plan, sync-state, lock, and readiness checks.';

    public function __construct(
        private readonly ContentReleaseGateService $contentReleaseGateService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));

        try {
            $result = $this->contentReleaseGateService->run($target !== '' ? $target : null, $this->optionsPayload());
        } catch (Throwable $exception) {
            $result = $this->errorResult($exception);
        }

        if ($this->option('write-report')) {
            try {
                $path = $this->contentReleaseGateService->writeReport($result);
                $result['artifacts']['report_path'] = storage_path('app/' . $path);
            } catch (Throwable $exception) {
                $result['checks'][] = [
                    'group' => 'release_gate',
                    'code' => 'release_gate_report_write_failed',
                    'label' => 'Release gate report',
                    'status' => 'fail',
                    'message' => 'Unable to write release-gate report: ' . $exception->getMessage(),
                    'meta' => ['exception_class' => $exception::class],
                    'recommendation' => 'Fix storage/app permissions and rerun content:release-gate --write-report.',
                    'source' => 'release_gate',
                ];
                $result['summary']['fail'] = (int) ($result['summary']['fail'] ?? 0) + 1;
                $result['gate']['overall_status'] = 'fail';
                $result['gate']['exit_would_fail'] = true;
            }
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $this->exitCode($result);
        }

        $this->renderHumanOutput($result);

        return $this->exitCode($result);
    }

    /**
     * @return array<string, mixed>
     */
    private function optionsPayload(): array
    {
        return [
            'base' => (string) ($this->option('base') ?? ''),
            'head' => (string) ($this->option('head') ?? ''),
            'staged' => (bool) $this->option('staged'),
            'working_tree' => (bool) $this->option('working-tree'),
            'include_untracked' => (bool) $this->option('include-untracked'),
            'domains' => $this->option('domains'),
            'with_release_check' => (bool) $this->option('with-release-check'),
            'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
            'with_doctor' => (bool) $this->option('with-doctor'),
            'with_git' => (bool) $this->option('with-git'),
            'with_artifacts' => (bool) $this->option('with-artifacts'),
            'with_deployment' => (bool) $this->option('with-deployment'),
            'with_package_roots' => (bool) $this->option('with-package-roots'),
            'with_dry_plan' => (bool) $this->option('with-dry-plan'),
            'profile' => (string) ($this->option('profile') ?? 'local'),
            'strict' => (bool) $this->option('strict'),
            'fail_on_warnings' => (bool) $this->option('fail-on-warnings'),
            'fail_on_lock' => (bool) $this->option('fail-on-lock'),
            'fail_on_stale_lock' => (bool) $this->option('fail-on-stale-lock'),
            'fail_on_sync_drift' => (bool) $this->option('fail-on-sync-drift'),
            'fail_on_uninitialized_sync' => (bool) $this->option('fail-on-uninitialized-sync'),
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderHumanOutput(array $result): void
    {
        $this->line('Command: content:release-gate');
        $this->line('Profile: ' . (string) data_get($result, 'gate.profile', 'local'));
        $this->line('Overall Status: ' . (string) data_get($result, 'gate.overall_status', 'fail'));
        $this->line('Exit Would Fail: ' . (data_get($result, 'gate.exit_would_fail') ? 'true' : 'false'));
        $this->line('Diff Mode: ' . (string) data_get($result, 'diff.mode', 'working_tree'));
        $this->line('Domains: ' . implode(', ', (array) data_get($result, 'scope.domains', [])));
        $this->newLine();
        $this->line(sprintf(
            'Summary: pass=%d; warn=%d; fail=%d; changed=%d; blocked=%d; warnings=%d',
            (int) data_get($result, 'summary.pass', 0),
            (int) data_get($result, 'summary.warn', 0),
            (int) data_get($result, 'summary.fail', 0),
            (int) data_get($result, 'summary.changed_packages', 0),
            (int) data_get($result, 'summary.blocked_packages', 0),
            (int) data_get($result, 'summary.warnings', 0)
        ));
        $this->newLine();

        foreach ((array) ($result['checks'] ?? []) as $check) {
            $check = (array) $check;
            $this->line(sprintf(
                '[%s] [%s] %s - %s',
                strtoupper((string) ($check['status'] ?? 'fail')),
                (string) ($check['source'] ?? $check['group'] ?? 'release_gate'),
                (string) ($check['code'] ?? 'unknown'),
                (string) ($check['message'] ?? '')
            ));

            if (! empty($check['recommendation'])) {
                $this->line('  recommendation: ' . (string) $check['recommendation']);
            }
        }

        if (! empty($result['artifacts']['report_path'])) {
            $this->newLine();
            $this->line('Report: ' . (string) $result['artifacts']['report_path']);
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function exitCode(array $result): int
    {
        return (bool) data_get($result, 'gate.exit_would_fail', true)
            ? self::FAILURE
            : self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function errorResult(Throwable $exception): array
    {
        return [
            'gate' => [
                'overall_status' => 'fail',
                'raw_status' => 'fail',
                'profile' => (string) ($this->option('profile') ?? 'local'),
                'strict' => (bool) $this->option('strict'),
                'exit_would_fail' => true,
            ],
            'diff' => [
                'mode' => 'working_tree',
                'base' => $this->option('base') ?: null,
                'head' => $this->option('head') ?: null,
                'include_untracked' => (bool) $this->option('include-untracked'),
            ],
            'scope' => [
                'domains' => [],
                'target' => $this->argument('target'),
                'resolved_roots' => [],
            ],
            'summary' => [
                'pass' => 0,
                'warn' => 0,
                'fail' => 1,
                'changed_packages' => 0,
                'blocked_packages' => 0,
                'warnings' => 0,
            ],
            'checks' => [
                [
                    'group' => 'release_gate',
                    'code' => 'release_gate_exception',
                    'label' => 'Release gate',
                    'status' => 'fail',
                    'message' => $exception->getMessage(),
                    'meta' => ['exception_class' => $exception::class],
                    'recommendation' => 'Fix the release-gate error and rerun the command.',
                    'source' => 'release_gate',
                ],
            ],
            'changed_plan' => null,
            'sync_state' => null,
            'lock' => null,
            'doctor' => null,
            'recommendations' => ['Fix the release-gate error and rerun the command.'],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'release_gate',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }
}
