<?php

namespace App\Services\ContentDeployment;

use App\Modules\GitDeployment\Services\ChangedContentDeploymentApplyService;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Modules\GitDeployment\Services\DeploymentContentLockService;
use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ContentOperationsDoctorService
{
    public function __construct(
        private readonly ContentSyncStateService $contentSyncStateService,
        private readonly ContentOperationLockService $contentOperationLockService,
        private readonly ContentOperationRunService $contentOperationRunService,
        private readonly ChangedContentPlanService $changedContentPlanService,
        private readonly DeploymentGitRefProbe $deploymentGitRefProbe,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(array $options = []): array
    {
        $resolved = $this->normalizeOptions($options);
        $checks = [];
        $currentCodeRef = null;

        $checks = array_merge($checks, $this->schemaChecks());
        $checks = array_merge($checks, $this->configChecks());
        $checks = array_merge($checks, $this->storageChecks());
        $checks[] = $this->lockCheck();
        $checks = array_merge($checks, $this->syncStateChecks($resolved['domains'], null));

        if ($resolved['with_git']) {
            $git = $this->gitCheck();
            $checks[] = $git['check'];
            $currentCodeRef = $git['current_code_ref'];
            $checks = array_merge($checks, $this->syncStateChecks($resolved['domains'], $currentCodeRef, true));
        }

        if ($resolved['with_package_roots']) {
            $checks = array_merge($checks, $this->packageRootChecks($resolved['domains']));
        }

        if ($resolved['with_deployment']) {
            $checks = array_merge($checks, $this->deploymentWiringChecks());
        }

        if ($resolved['with_artifacts']) {
            $checks = array_merge($checks, $this->artifactChecks());
        }

        if ($resolved['with_dry_plan']) {
            $checks[] = $this->dryPlanCheck($resolved);
        }

        $summary = $this->summary($checks);
        $overallStatus = $summary['fail'] > 0
            ? 'fail'
            : ($summary['warn'] > 0 ? 'warn' : 'pass');

        return [
            'generated_at' => now()->toIso8601String(),
            'overall_status' => $overallStatus,
            'summary' => $summary,
            'options' => $resolved,
            'checks' => $checks,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $domains = collect((array) ($result['options']['domains'] ?? []))
            ->map(fn (mixed $domain): string => Str::slug((string) $domain))
            ->implode('-');
        $relativePath = 'content-doctor-reports/'
            . now()->format('Y/m/d/His')
            . '-'
            . ($domains !== '' ? $domains : 'content')
            . '-doctor.md';

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        return [
            'domains' => $this->normalizeDomains($options['domains'] ?? null),
            'strict' => (bool) ($options['strict'] ?? false),
            'with_git' => (bool) ($options['with_git'] ?? false),
            'with_artifacts' => (bool) ($options['with_artifacts'] ?? false),
            'with_deployment' => (bool) ($options['with_deployment'] ?? false),
            'with_package_roots' => (bool) ($options['with_package_roots'] ?? false),
            'with_dry_plan' => (bool) ($options['with_dry_plan'] ?? false),
        ];
    }

    /**
     * @return list<string>
     */
    private function normalizeDomains(mixed $value): array
    {
        if (is_array($value)) {
            $tokens = $value;
        } else {
            $tokens = preg_split('/\s*,\s*/', (string) ($value ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $domains = collect($tokens)
            ->map(static fn (mixed $domain): string => str_replace('_', '-', strtolower(trim((string) $domain))))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($domains === []) {
            $domains = ['v3', 'page-v3'];
        }

        $unsupported = array_values(array_diff($domains, ['v3', 'page-v3']));

        if ($unsupported !== []) {
            throw new \InvalidArgumentException('Unsupported content doctor domain(s): ' . implode(', ', $unsupported));
        }

        return $domains;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function schemaChecks(): array
    {
        return [
            $this->tableCheck('content_sync_states', [
                'domain',
                'last_successful_ref',
                'last_successful_applied_at',
                'last_attempted_base_ref',
                'last_attempted_head_ref',
                'last_attempted_status',
                'last_attempted_at',
                'last_attempt_meta',
            ]),
            $this->tableCheck('content_operation_runs', [
                'replayed_from_run_id',
                'operation_kind',
                'trigger_source',
                'domains',
                'base_ref',
                'head_ref',
                'base_refs_by_domain',
                'dry_run',
                'strict',
                'with_release_check',
                'skip_release_check',
                'bootstrap_uninitialized',
                'status',
                'started_at',
                'finished_at',
                'summary',
                'payload_json_path',
                'report_path',
                'error_excerpt',
                'meta',
            ]),
            $this->tableCheck('content_operation_locks', [
                'lock_key',
                'owner_token',
                'operation_kind',
                'trigger_source',
                'domains',
                'content_operation_run_id',
                'operator_user_id',
                'acquired_at',
                'heartbeat_at',
                'expires_at',
                'status',
                'meta',
            ]),
        ];
    }

    /**
     * @param  list<string>  $columns
     * @return array<string, mixed>
     */
    private function tableCheck(string $table, array $columns): array
    {
        if (! Schema::hasTable($table)) {
            return $this->check(
                'content_schema_' . $table,
                'ContentOps schema',
                'fail',
                "Required table [{$table}] is missing.",
                ['table' => $table, 'missing_columns' => $columns],
                'Run the pending ContentOps migrations before using changed-content execution flows.'
            );
        }

        $missing = array_values(array_filter(
            $columns,
            static fn (string $column): bool => ! Schema::hasColumn($table, $column)
        ));

        return $this->check(
            'content_schema_' . $table,
            'ContentOps schema',
            $missing === [] ? 'pass' : 'fail',
            $missing === []
                ? "Required table [{$table}] is present."
                : "Required table [{$table}] is missing columns: " . implode(', ', $missing) . '.',
            ['table' => $table, 'missing_columns' => $missing],
            $missing === [] ? null : 'Run the latest ContentOps migrations and verify the database schema.'
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function configChecks(): array
    {
        $lock = (array) config('content-deployment.lock', []);
        $deploymentPreview = (array) config('git-deployment.content_preview', []);
        $deploymentApply = (array) config('git-deployment.content_apply', []);
        $issues = [];

        if ($lock === []) {
            $issues[] = 'content-deployment.lock config is missing';
        }

        if (! array_key_exists('enabled', $lock)) {
            $issues[] = 'content-deployment.lock.enabled is missing';
        }

        if (trim((string) ($lock['key'] ?? '')) === '') {
            $issues[] = 'content-deployment.lock.key is empty';
        }

        if ((int) ($lock['ttl_seconds'] ?? 0) < 60) {
            $issues[] = 'content-deployment.lock.ttl_seconds must be at least 60';
        }

        if ((int) ($lock['stale_after_seconds'] ?? 0) < 30) {
            $issues[] = 'content-deployment.lock.stale_after_seconds must be at least 30';
        }

        if ($deploymentPreview === []) {
            $issues[] = 'git-deployment.content_preview config is missing';
        }

        if ($deploymentApply === []) {
            $issues[] = 'git-deployment.content_apply config is missing';
        }

        return [
            $this->check(
                'content_ops_config',
                'ContentOps config',
                $issues === [] ? 'pass' : 'fail',
                $issues === [] ? 'ContentOps config keys are present.' : implode('; ', $issues) . '.',
                [
                    'content_deployment_lock' => $lock,
                    'git_deployment_content_preview' => $deploymentPreview,
                    'git_deployment_content_apply' => $deploymentApply,
                ],
                $issues === [] ? null : 'Restore config/content-deployment.php and GitDeployment content config defaults.'
            ),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function storageChecks(): array
    {
        $storageRoot = storage_path('app');
        $requiredDirs = [
            'content-operation-runs',
            'content-changed-plans',
            'content-changed-apply-reports',
            'content-sync-plans',
            'content-sync-apply-reports',
            'content-sync-status',
            'content-lock-status',
            'content-operation-history',
            'content-operation-replays',
            'content-doctor-reports',
        ];

        $missing = array_values(array_filter($requiredDirs, static function (string $dir): bool {
            return ! Storage::disk('local')->exists($dir);
        }));

        $rootOk = is_dir($storageRoot) && is_readable($storageRoot) && is_writable($storageRoot);

        $checks = [
            $this->check(
                'content_storage_root',
                'ContentOps storage',
                $rootOk ? 'pass' : 'fail',
                $rootOk
                    ? 'storage/app is readable and writable.'
                    : 'storage/app is not readable and writable.',
                ['path' => $storageRoot],
                $rootOk ? null : 'Fix storage/app permissions before running ContentOps execution flows.'
            ),
        ];

        $checks[] = $this->check(
            'content_storage_artifact_dirs',
            'ContentOps artifact directories',
            $missing === [] ? 'pass' : 'warn',
            $missing === []
                ? 'Known ContentOps artifact directories are present.'
                : 'Some ContentOps artifact directories have not been created yet: ' . implode(', ', $missing) . '.',
            ['missing_directories' => $missing],
            $missing === [] ? null : 'This is safe before first use; running commands with --write-report will create needed directories.'
        );

        return $checks;
    }

    /**
     * @return array<string, mixed>
     */
    private function lockCheck(): array
    {
        if (! Schema::hasTable('content_operation_locks')) {
            return $this->check(
                'content_operation_lock_status',
                'Content operation lock',
                'fail',
                'The content operation lock table is missing.',
                [],
                'Run migrations before executing ContentOps apply/sync/replay flows.'
            );
        }

        try {
            $status = $this->contentOperationLockService->status();
        } catch (Throwable $exception) {
            return $this->check(
                'content_operation_lock_status',
                'Content operation lock',
                'fail',
                'Unable to read the content operation lock status: ' . $exception->getMessage(),
                ['exception_class' => $exception::class],
                'Inspect the content_operation_locks table and lock service configuration.'
            );
        }

        $state = (string) ($status['status'] ?? 'unavailable');
        $checkStatus = match ($state) {
            'free' => 'pass',
            'active', 'stale', 'disabled' => 'warn',
            default => 'fail',
        };

        return $this->check(
            'content_operation_lock_status',
            'Content operation lock',
            $checkStatus,
            match ($state) {
                'free' => 'No execution-grade content operation currently holds the lock.',
                'active' => 'A content operation lock is currently active.',
                'stale' => 'A stale content operation lock is present.',
                'disabled' => 'The global content operation lock is disabled by config.',
                default => 'Content operation lock status is unavailable.',
            },
            $status,
            match ($state) {
                'active' => 'Wait for the current operation to finish before running apply/sync/replay/deploy content phases.',
                'stale' => 'Inspect the owning run, then rerun with explicit stale-lock takeover only if safe.',
                'disabled' => 'Enable content-deployment.lock.enabled for production execution safety.',
                default => null,
            }
        );
    }

    /**
     * @param  list<string>  $domains
     * @return list<array<string, mixed>>
     */
    private function syncStateChecks(array $domains, ?string $currentCodeRef, bool $includeCodeRef = false): array
    {
        if (! Schema::hasTable('content_sync_states')) {
            return [
                $this->check(
                    'content_sync_state',
                    'Content sync state',
                    'fail',
                    'The content sync state table is missing.',
                    ['domains' => $domains],
                    'Run migrations before using deployment-aware changed-content flows.'
                ),
            ];
        }

        $fallbackRefs = array_fill_keys($domains, $currentCodeRef);

        try {
            $states = $this->contentSyncStateService->describe($domains, $fallbackRefs, $currentCodeRef);
        } catch (Throwable $exception) {
            return [
                $this->check(
                    'content_sync_state',
                    'Content sync state',
                    'fail',
                    'Unable to read content sync state: ' . $exception->getMessage(),
                    ['domains' => $domains, 'exception_class' => $exception::class],
                    'Inspect content_sync_states rows and domain names.'
                ),
            ];
        }

        $checks = [];

        foreach ($states as $domain => $state) {
            $status = (string) ($state['status'] ?? 'uninitialized');
            $checkStatus = match ($status) {
                'synced' => 'pass',
                'uninitialized', 'drifted', 'failed_last_apply' => 'warn',
                default => 'warn',
            };
            $suffix = $includeCodeRef ? '_with_code_ref' : '';

            $checks[] = $this->check(
                'content_sync_state_' . str_replace('-', '_', (string) $domain) . $suffix,
                'Content sync state',
                $checkStatus,
                match ($status) {
                    'synced' => "{$domain} content sync cursor is aligned with the available code ref.",
                    'drifted' => "{$domain} content sync cursor differs from the available code ref.",
                    'failed_last_apply' => "{$domain} last content sync/apply attempt failed.",
                    default => "{$domain} content sync cursor is uninitialized.",
                },
                $state,
                match ($status) {
                    'uninitialized' => 'Run content:plan-sync and bootstrap explicitly only if the DB is trusted to match current code.',
                    'drifted' => 'Run content:plan-sync and content:apply-sync to reconcile content drift.',
                    'failed_last_apply' => 'Inspect content:history and retry or repair the failed content operation.',
                    default => null,
                }
            );
        }

        return $checks;
    }

    /**
     * @return array{check:array<string,mixed>,current_code_ref:?string}
     */
    private function gitCheck(): array
    {
        try {
            $commit = $this->deploymentGitRefProbe->currentHeadCommit();
        } catch (Throwable $exception) {
            return [
                'current_code_ref' => null,
                'check' => $this->check(
                    'content_git_current_ref',
                    'Git ref probe',
                    'fail',
                    'Unable to resolve current deployed git ref: ' . $exception->getMessage(),
                    ['exception_class' => $exception::class],
                    'Verify this working copy is a git repository and git read commands are available.'
                ),
            ];
        }

        return [
            'current_code_ref' => $commit,
            'check' => $this->check(
                'content_git_current_ref',
                'Git ref probe',
                $commit !== null ? 'pass' : 'fail',
                $commit !== null
                    ? 'Current deployed git ref resolved.'
                    : 'Current deployed git ref could not be resolved.',
                ['current_code_ref' => $commit],
                $commit !== null ? null : 'Verify this working copy is a git repository with a valid HEAD commit.'
            ),
        ];
    }

    /**
     * @param  list<string>  $domains
     * @return list<array<string, mixed>>
     */
    private function packageRootChecks(array $domains): array
    {
        $roots = [
            'v3' => base_path('database/seeders/V3'),
            'page-v3' => base_path('database/seeders/Page_V3'),
        ];

        return array_map(function (string $domain) use ($roots): array {
            $path = $roots[$domain];
            $ok = is_dir($path) && is_readable($path);

            return $this->check(
                'content_package_root_' . str_replace('-', '_', $domain),
                'Content package roots',
                $ok ? 'pass' : 'fail',
                $ok
                    ? "{$domain} package root is present and readable."
                    : "{$domain} package root is missing or not readable.",
                ['domain' => $domain, 'path' => $path],
                $ok ? null : 'Restore the package root before running changed-content planning or execution.'
            );
        }, $domains);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function deploymentWiringChecks(): array
    {
        $routes = [
            'deployment.content-preview',
            'deployment.content-apply-preview',
            'deployment.content-sync-preview',
            'deployment.content-sync',
            'deployment.content-runs',
            'deployment.content-doctor',
            'deployment.native.content-preview',
            'deployment.native.content-apply-preview',
            'deployment.native.content-sync-preview',
            'deployment.native.content-sync',
            'deployment.native.content-doctor',
        ];
        $services = [
            ChangedContentDeploymentPreviewService::class,
            ChangedContentDeploymentApplyService::class,
            DeploymentContentLockService::class,
            DeploymentGitRefProbe::class,
        ];
        $missingRoutes = array_values(array_filter($routes, static fn (string $route): bool => ! Route::has($route)));
        $unresolvableServices = [];

        foreach ($services as $serviceClass) {
            try {
                app($serviceClass);
            } catch (Throwable) {
                $unresolvableServices[] = $serviceClass;
            }
        }

        $status = $missingRoutes === [] && $unresolvableServices === [] ? 'pass' : 'fail';

        return [
            $this->check(
                'content_deployment_wiring',
                'GitDeployment ContentOps wiring',
                $status,
                $status === 'pass'
                    ? 'GitDeployment ContentOps routes and services are wired.'
                    : 'GitDeployment ContentOps wiring is incomplete.',
                [
                    'missing_routes' => $missingRoutes,
                    'unresolvable_services' => $unresolvableServices,
                ],
                $status === 'pass' ? null : 'Restore GitDeployment ContentOps routes/services before deployment-aware content operations.'
            ),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function artifactChecks(): array
    {
        if (! Schema::hasTable('content_operation_runs')) {
            return [
                $this->check(
                    'content_history_artifacts',
                    'Content operation history artifacts',
                    'fail',
                    'Cannot inspect history artifacts because content_operation_runs is missing.',
                    [],
                    'Run migrations before checking operation artifacts.'
                ),
            ];
        }

        try {
            $runs = $this->contentOperationRunService->latest([], (int) config('content-deployment.doctor.artifact_history_limit', 20));
        } catch (Throwable $exception) {
            return [
                $this->check(
                    'content_history_artifacts',
                    'Content operation history artifacts',
                    'fail',
                    'Unable to query recent content operation runs: ' . $exception->getMessage(),
                    ['exception_class' => $exception::class],
                    'Inspect the content_operation_runs table.'
                ),
            ];
        }

        $missingPayloads = [];
        $invalidPayloads = [];
        $missingReports = [];

        foreach ($runs as $run) {
            $payloadPath = $this->normalizeArtifactPath($run->payload_json_path);

            if ($payloadPath !== null) {
                if (! Storage::disk('local')->exists($payloadPath)) {
                    $missingPayloads[] = ['run_id' => $run->id, 'path' => $payloadPath];
                } else {
                    $decoded = json_decode((string) Storage::disk('local')->get($payloadPath), true);

                    if (! is_array($decoded)) {
                        $invalidPayloads[] = ['run_id' => $run->id, 'path' => $payloadPath];
                    }
                }
            }

            $reportPath = $this->normalizeArtifactPath($run->report_path);

            if ($reportPath !== null && ! Storage::disk('local')->exists($reportPath)) {
                $missingReports[] = ['run_id' => $run->id, 'path' => $reportPath];
            }
        }

        $hasFailures = $missingPayloads !== [] || $invalidPayloads !== [];
        $hasWarnings = $missingReports !== [];

        return [
            $this->check(
                'content_history_artifacts',
                'Content operation history artifacts',
                $hasFailures ? 'fail' : ($hasWarnings ? 'warn' : 'pass'),
                match (true) {
                    $hasFailures => 'Some recent content operation payload artifacts are missing or invalid.',
                    $hasWarnings => 'Some recent content operation report artifacts are missing.',
                    $runs->isEmpty() => 'No content operation runs have been recorded yet.',
                    default => 'Recent content operation artifacts are readable.',
                },
                [
                    'inspected_runs' => $runs->count(),
                    'missing_payloads' => $missingPayloads,
                    'invalid_payloads' => $invalidPayloads,
                    'missing_reports' => $missingReports,
                ],
                $hasFailures
                    ? 'Inspect or restore missing payload JSON artifacts; they are the canonical detailed history.'
                    : ($hasWarnings ? 'Regenerate or ignore missing compact reports; payload JSON remains canonical.' : null)
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function dryPlanCheck(array $options): array
    {
        try {
            $plan = $this->changedContentPlanService->run(null, [
                'domains' => $options['domains'],
                'working_tree' => true,
                'include_untracked' => false,
                'with_release_check' => false,
                'check_profile' => 'scaffold',
                'strict' => false,
            ]);
        } catch (Throwable $exception) {
            return $this->check(
                'content_changed_plan_read',
                'Changed-content dry plan read',
                'fail',
                'Unable to run a read-only changed-content plan: ' . $exception->getMessage(),
                ['exception_class' => $exception::class],
                'Fix git/package planner errors before running apply/sync/replay operations.'
            );
        }

        $error = is_array($plan['error'] ?? null) ? $plan['error'] : null;
        $warnings = (int) data_get($plan, 'summary.warnings', 0);
        $blocked = (int) data_get($plan, 'summary.blocked', 0);
        $status = $error !== null || $blocked > 0 ? 'fail' : ($warnings > 0 ? 'warn' : 'pass');

        return $this->check(
            'content_changed_plan_read',
            'Changed-content dry plan read',
            $status,
            match ($status) {
                'pass' => 'Read-only changed-content plan completed without blockers or warnings.',
                'warn' => 'Read-only changed-content plan completed with warnings.',
                default => 'Read-only changed-content plan failed or found blockers.',
            },
            [
                'summary' => (array) ($plan['summary'] ?? []),
                'error' => $error,
            ],
            $status === 'pass' ? null : 'Inspect content:plan-changed --json for package-level blockers or warnings.'
        );
    }

    /**
     * @param  list<array<string, mixed>>  $checks
     * @return array<string, int>
     */
    private function summary(array $checks): array
    {
        return [
            'total' => count($checks),
            'pass' => count(array_filter($checks, static fn (array $check): bool => ($check['status'] ?? null) === 'pass')),
            'warn' => count(array_filter($checks, static fn (array $check): bool => ($check['status'] ?? null) === 'warn')),
            'fail' => count(array_filter($checks, static fn (array $check): bool => ($check['status'] ?? null) === 'fail')),
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# ContentOps Doctor',
            '',
            '- Generated At: `' . (string) ($result['generated_at'] ?? '') . '`',
            '- Overall Status: `' . (string) ($result['overall_status'] ?? 'fail') . '`',
            '- Domains: `' . implode(', ', (array) data_get($result, 'options.domains', [])) . '`',
            '- Summary: `' . json_encode((array) ($result['summary'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`',
            '',
            '## Checks',
            '',
        ];

        foreach ((array) ($result['checks'] ?? []) as $check) {
            $check = (array) $check;
            $lines[] = '- `' . strtoupper((string) ($check['status'] ?? 'fail')) . '` '
                . (string) ($check['code'] ?? 'unknown')
                . ' - '
                . (string) ($check['message'] ?? '');

            if (! empty($check['recommendation'])) {
                $lines[] = '  - Recommendation: ' . (string) $check['recommendation'];
            }
        }

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- ' . (string) data_get($result, 'error.message', 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function check(
        string $code,
        string $label,
        string $status,
        string $message,
        array $meta = [],
        ?string $recommendation = null
    ): array {
        return [
            'code' => $code,
            'label' => $label,
            'status' => in_array($status, ['pass', 'warn', 'fail'], true) ? $status : 'fail',
            'message' => $message,
            'meta' => $meta,
            'recommendation' => $recommendation,
        ];
    }

    private function normalizeArtifactPath(mixed $path): ?string
    {
        $normalized = trim((string) ($path ?? ''));

        if ($normalized === '') {
            return null;
        }

        $storagePrefix = str_replace('\\', '/', storage_path('app'));
        $candidate = str_replace('\\', '/', $normalized);

        if (str_starts_with($candidate, $storagePrefix . '/')) {
            return ltrim(substr($candidate, strlen($storagePrefix)), '/');
        }

        return ltrim($candidate, '/');
    }
}
