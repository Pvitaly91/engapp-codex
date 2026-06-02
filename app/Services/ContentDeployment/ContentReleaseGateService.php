<?php

namespace App\Services\ContentDeployment;

use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ContentReleaseGateService
{
    public function __construct(
        private readonly ContentOperationsDoctorService $contentOperationsDoctorService,
        private readonly ChangedContentPlanService $changedContentPlanService,
        private readonly ContentSyncStateService $contentSyncStateService,
        private readonly ContentOperationLockService $contentOperationLockService,
        private readonly DeploymentGitRefProbe $deploymentGitRefProbe,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(?string $targetInput = null, array $options = []): array
    {
        try {
            $resolved = $this->normalizeOptions($options);
        } catch (Throwable $exception) {
            return $this->errorResult($targetInput, $options, $exception);
        }

        $checks = [];
        $doctor = null;

        if ($resolved['with_doctor']) {
            $doctor = $this->contentOperationsDoctorService->run([
                'domains' => $resolved['domains'],
                'strict' => false,
                'with_git' => $resolved['with_git'],
                'with_artifacts' => $resolved['with_artifacts'],
                'with_deployment' => $resolved['with_deployment'],
                'with_package_roots' => $resolved['with_package_roots'],
                'with_dry_plan' => $resolved['with_dry_plan'],
            ]);
            $checks = array_merge($checks, $this->doctorChecks($doctor));
        }

        $changedPlan = $this->changedContentPlanService->run($targetInput, [
            'domains' => $resolved['domains'],
            'base' => $resolved['base'],
            'head' => $resolved['head'],
            'staged' => $resolved['staged'],
            'working_tree' => $resolved['working_tree'],
            'include_untracked' => $resolved['include_untracked'],
            'with_release_check' => $resolved['with_release_check'],
            'check_profile' => $resolved['check_profile'],
            'strict' => false,
        ]);
        $checks = array_merge($checks, $this->changedPlanChecks($changedPlan, $resolved));

        $effectiveDomains = (array) data_get($changedPlan, 'scope.domains', $resolved['domains']);
        $effectiveDomains = $effectiveDomains !== [] ? $effectiveDomains : $resolved['domains'];
        $syncState = $this->syncStatePayload($effectiveDomains, $resolved);
        $checks = array_merge($checks, $this->syncStateChecks($syncState, $resolved));

        $lock = $this->lockPayload();
        $checks[] = $this->lockCheck($lock, $resolved);

        if ($resolved['profile'] === 'deployment') {
            $deploymentCheck = $this->deploymentProfileCheck($resolved);

            if ($deploymentCheck !== null) {
                $checks[] = $deploymentCheck;
            }
        }

        $summary = $this->summary($checks, $changedPlan);
        $overallStatus = $summary['fail'] > 0
            ? 'fail'
            : ($summary['warn'] > 0 ? 'warn' : 'pass');
        $exitWouldFail = $overallStatus === 'fail'
            || (($resolved['strict'] || $resolved['fail_on_warnings']) && $overallStatus === 'warn');

        return [
            'gate' => [
                'overall_status' => $exitWouldFail && $overallStatus === 'warn' ? 'fail' : $overallStatus,
                'raw_status' => $overallStatus,
                'profile' => $resolved['profile'],
                'strict' => $resolved['strict'],
                'exit_would_fail' => $exitWouldFail,
                'fatal_warning_rules' => $this->fatalWarningRules($resolved),
            ],
            'diff' => [
                'mode' => (string) data_get($changedPlan, 'diff.mode', $this->diffMode($resolved)),
                'base' => data_get($changedPlan, 'diff.base', $resolved['base'] !== '' ? $resolved['base'] : null),
                'head' => data_get($changedPlan, 'diff.head', $resolved['head'] !== '' ? $resolved['head'] : null),
                'include_untracked' => (bool) data_get($changedPlan, 'diff.include_untracked', $resolved['include_untracked']),
            ],
            'scope' => [
                'domains' => $effectiveDomains,
                'target' => $targetInput,
                'resolved_roots' => (array) data_get($changedPlan, 'scope.resolved_roots', []),
            ],
            'summary' => $summary,
            'checks' => $checks,
            'changed_plan' => $changedPlan,
            'sync_state' => $syncState,
            'lock' => $lock,
            'doctor' => $doctor,
            'recommendations' => $this->recommendations($checks),
            'options' => $resolved,
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
        $domains = collect((array) data_get($result, 'scope.domains', []))
            ->map(fn (mixed $domain): string => Str::slug((string) $domain))
            ->implode('-');
        $profile = Str::slug((string) data_get($result, 'gate.profile', 'local'));
        $relativePath = 'content-release-gates/'
            . now()->format('Y/m/d/His')
            . '-'
            . ($domains !== '' ? $domains : 'content')
            . '-'
            . $profile
            . '-release-gate.md';

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        $profile = strtolower(trim((string) ($options['profile'] ?? 'local')));

        if (! in_array($profile, ['local', 'ci', 'deployment'], true)) {
            throw new RuntimeException('Unsupported release-gate profile. Use local, ci, or deployment.');
        }

        $checkProfile = strtolower(trim((string) ($options['check_profile'] ?? 'release')));

        if (! in_array($checkProfile, ['scaffold', 'release'], true)) {
            throw new RuntimeException('Unsupported check profile. Use scaffold or release.');
        }

        $base = trim((string) ($options['base'] ?? ''));
        $head = trim((string) ($options['head'] ?? ''));
        $staged = (bool) ($options['staged'] ?? false);
        $workingTree = (bool) ($options['working_tree'] ?? false);
        $refMode = $base !== '' || $head !== '';

        if ($head !== '' && $base === '') {
            throw new RuntimeException('--head requires --base for content:release-gate ref-diff mode.');
        }

        if ($base !== '' && $head === '') {
            $head = 'HEAD';
        }

        if ($refMode && ($staged || $workingTree)) {
            throw new RuntimeException('Ref-diff mode cannot be mixed with --staged or --working-tree.');
        }

        if ($staged && $workingTree) {
            throw new RuntimeException('--staged and --working-tree cannot be used together.');
        }

        if (! $refMode && ! $staged && ! $workingTree) {
            $workingTree = true;
        }

        $strict = (bool) ($options['strict'] ?? false);
        $failOnWarnings = (bool) ($options['fail_on_warnings'] ?? false) || in_array($profile, ['ci', 'deployment'], true);

        return [
            'profile' => $profile,
            'domains' => $this->normalizeDomains($options['domains'] ?? null),
            'base' => $base,
            'head' => $head,
            'staged' => $staged,
            'working_tree' => $workingTree,
            'include_untracked' => (bool) ($options['include_untracked'] ?? false),
            'with_release_check' => (bool) ($options['with_release_check'] ?? false) || in_array($profile, ['ci', 'deployment'], true),
            'check_profile' => $checkProfile,
            'with_doctor' => true,
            'with_git' => (bool) ($options['with_git'] ?? false) || in_array($profile, ['ci', 'deployment'], true),
            'with_artifacts' => (bool) ($options['with_artifacts'] ?? false),
            'with_deployment' => (bool) ($options['with_deployment'] ?? false) || $profile === 'deployment',
            'with_package_roots' => (bool) ($options['with_package_roots'] ?? false) || in_array($profile, ['ci', 'deployment'], true),
            'with_dry_plan' => (bool) ($options['with_dry_plan'] ?? false) || $profile === 'ci',
            'strict' => $strict,
            'fail_on_warnings' => $failOnWarnings,
            'fail_on_lock' => (bool) ($options['fail_on_lock'] ?? false) || in_array($profile, ['ci', 'deployment'], true),
            'fail_on_stale_lock' => (bool) ($options['fail_on_stale_lock'] ?? false) || in_array($profile, ['ci', 'deployment'], true),
            'fail_on_sync_drift' => (bool) ($options['fail_on_sync_drift'] ?? false),
            'fail_on_uninitialized_sync' => (bool) ($options['fail_on_uninitialized_sync'] ?? false) || $profile === 'ci',
        ];
    }

    /**
     * @return list<string>
     */
    private function normalizeDomains(mixed $value): array
    {
        $tokens = is_array($value)
            ? $value
            : preg_split('/\s*,\s*/', (string) ($value ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $domains = collect($tokens ?: [])
            ->map(static fn (mixed $domain): string => str_replace('_', '-', strtolower(trim((string) $domain))))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($domains === []) {
            return ['v3', 'page-v3'];
        }

        $invalid = array_values(array_diff($domains, ['v3', 'page-v3']));

        if ($invalid !== []) {
            throw new RuntimeException('Unsupported --domains value(s): ' . implode(', ', $invalid) . '. Supported domains: v3, page-v3.');
        }

        return $domains;
    }

    /**
     * @param  array<string, mixed>  $doctor
     * @return list<array<string, mixed>>
     */
    private function doctorChecks(array $doctor): array
    {
        return array_values(array_map(function (array $check): array {
            return $this->check(
                'doctor',
                (string) ($check['code'] ?? 'doctor_check'),
                (string) ($check['label'] ?? 'ContentOps doctor'),
                (string) ($check['status'] ?? 'fail'),
                (string) ($check['message'] ?? 'Doctor check returned no message.'),
                (array) ($check['meta'] ?? []),
                $check['recommendation'] ?? null,
                'doctor'
            );
        }, (array) ($doctor['checks'] ?? [])));
    }

    /**
     * @param  array<string, mixed>  $plan
     * @param  array<string, mixed>  $options
     * @return list<array<string, mixed>>
     */
    private function changedPlanChecks(array $plan, array $options): array
    {
        $summary = (array) ($plan['summary'] ?? []);
        $packages = (array) ($plan['packages'] ?? []);
        $releaseFailures = array_values(array_filter(
            $packages,
            static fn (array $package): bool => (string) data_get($package, 'release_check.status', 'skipped') === 'fail'
        ));
        $releaseWarnings = array_values(array_filter(
            $packages,
            static fn (array $package): bool => (string) data_get($package, 'release_check.status', 'skipped') === 'warn'
        ));
        $checks = [];

        $checks[] = $this->check(
            'changed_plan',
            'changed_plan_completed',
            'Changed-content plan',
            is_array($plan['error'] ?? null) ? 'fail' : 'pass',
            is_array($plan['error'] ?? null)
                ? 'Changed-content planner returned an error.'
                : 'Changed-content planner completed.',
            ['error' => $plan['error'] ?? null, 'summary' => $summary],
            is_array($plan['error'] ?? null) ? 'Run content:plan-changed --json to inspect the planner failure.' : null,
            'changed_plan'
        );

        $checks[] = $this->check(
            'changed_plan',
            'changed_plan_blocked_packages',
            'Changed-content blockers',
            ((int) ($summary['blocked'] ?? 0)) > 0 ? 'fail' : 'pass',
            ((int) ($summary['blocked'] ?? 0)) > 0
                ? 'Changed-content plan contains blocked packages.'
                : 'Changed-content plan contains no blocked packages.',
            ['blocked_packages' => (int) ($summary['blocked'] ?? 0)],
            ((int) ($summary['blocked'] ?? 0)) > 0 ? 'Resolve blocked package states before deploy/apply.' : null,
            'changed_plan'
        );

        if ((int) ($summary['warnings'] ?? 0) > 0) {
            $checks[] = $this->check(
                'changed_plan',
                'changed_plan_warnings',
                'Changed-content warnings',
                $this->warningStatus($options),
                'Changed-content plan contains warnings.',
                ['warnings' => (int) ($summary['warnings'] ?? 0)],
                'Inspect package warnings before deploy/apply.',
                'changed_plan'
            );
        }

        if ($releaseFailures !== []) {
            $checks[] = $this->check(
                'changed_plan',
                'changed_plan_release_check_failures',
                'Release-check readiness',
                'fail',
                'Release-check failed for one or more actionable packages.',
                ['packages' => $this->packagePaths($releaseFailures)],
                'Fix release-check failures before deploy/apply.',
                'changed_plan'
            );
        }

        if ($releaseWarnings !== []) {
            $checks[] = $this->check(
                'changed_plan',
                'changed_plan_release_check_warnings',
                'Release-check readiness',
                $this->warningStatus($options),
                'Release-check returned warnings for one or more actionable packages.',
                ['packages' => $this->packagePaths($releaseWarnings)],
                'Review release-check warnings before deploy/apply.',
                'changed_plan'
            );
        }

        return $checks;
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function syncStatePayload(array $domains, array $options): array
    {
        $currentCodeRef = null;
        $gitError = null;

        if ((bool) ($options['with_git'] ?? false)) {
            try {
                $currentCodeRef = $this->deploymentGitRefProbe->currentHeadCommit();
            } catch (Throwable $exception) {
                $gitError = [
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ];
            }
        }

        try {
            $fallbackRefs = array_fill_keys($domains, $currentCodeRef);

            return [
                'current_code_ref' => $currentCodeRef,
                'domains' => $this->contentSyncStateService->describe($domains, $fallbackRefs, $currentCodeRef),
                'error' => $gitError,
            ];
        } catch (Throwable $exception) {
            return [
                'current_code_ref' => $currentCodeRef,
                'domains' => [],
                'error' => [
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ],
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $syncState
     * @param  array<string, mixed>  $options
     * @return list<array<string, mixed>>
     */
    private function syncStateChecks(array $syncState, array $options): array
    {
        if (is_array($syncState['error'] ?? null)) {
            return [
                $this->check(
                    'sync_state',
                    'sync_state_read',
                    'Content sync state',
                    'fail',
                    'Unable to read content sync state or current code ref.',
                    ['error' => $syncState['error']],
                    'Fix sync-state storage or git ref probing before deploy/apply.',
                    'sync_state'
                ),
            ];
        }

        $checks = [];

        foreach ((array) ($syncState['domains'] ?? []) as $domain => $state) {
            $status = (string) ($state['status'] ?? 'uninitialized');
            $gateStatus = match ($status) {
                'synced' => 'pass',
                'drifted' => (bool) $options['fail_on_sync_drift'] ? 'fail' : 'warn',
                'uninitialized' => (bool) $options['fail_on_uninitialized_sync'] ? 'fail' : 'warn',
                'failed_last_apply' => $this->warningStatus($options),
                default => 'warn',
            };

            $checks[] = $this->check(
                'sync_state',
                'sync_state_' . str_replace('-', '_', (string) $domain),
                'Content sync state',
                $gateStatus,
                match ($status) {
                    'synced' => "{$domain} content sync state is initialized and not drifted against the available code ref.",
                    'drifted' => "{$domain} content sync state is drifted.",
                    'failed_last_apply' => "{$domain} last content apply/sync attempt failed.",
                    default => "{$domain} content sync state is uninitialized.",
                },
                (array) $state,
                match ($status) {
                    'drifted' => 'Run content:plan-sync and content:apply-sync to reconcile drift.',
                    'uninitialized' => 'Bootstrap only after an operator verifies DB content matches the deployed code ref.',
                    'failed_last_apply' => 'Inspect content:history, then retry or repair the failed operation.',
                    default => null,
                },
                'sync_state'
            );
        }

        return $checks;
    }

    /**
     * @return array<string, mixed>
     */
    private function lockPayload(): array
    {
        try {
            return $this->contentOperationLockService->status();
        } catch (Throwable $exception) {
            return [
                'status' => 'unavailable',
                'lock' => null,
                'error' => [
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ],
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $lock
     * @param  array<string, mixed>  $options
     */
    private function lockCheck(array $lock, array $options): array
    {
        $status = (string) ($lock['status'] ?? 'unavailable');
        $gateStatus = match ($status) {
            'free' => 'pass',
            'active' => (bool) $options['fail_on_lock'] ? 'fail' : 'warn',
            'stale' => (bool) $options['fail_on_stale_lock'] ? 'fail' : 'warn',
            'disabled' => $this->warningStatus($options),
            default => 'fail',
        };

        return $this->check(
            'lock',
            'content_operation_lock',
            'Content operation lock',
            $gateStatus,
            match ($status) {
                'free' => 'No global content operation lock is active.',
                'active' => 'A fresh active global content operation lock is present.',
                'stale' => 'A stale global content operation lock is present.',
                'disabled' => 'The global content operation lock is disabled.',
                default => 'Content operation lock status is unavailable.',
            },
            $lock,
            match ($status) {
                'active' => 'Wait for the current content operation to finish before deploy/apply.',
                'stale' => 'Inspect the owner run and use explicit stale-lock takeover only if safe.',
                'disabled' => 'Enable the global content lock for production ContentOps execution.',
                default => null,
            },
            'lock'
        );
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>|null
     */
    private function deploymentProfileCheck(array $options): ?array
    {
        if ($this->diffMode($options) === 'refs') {
            return null;
        }

        return $this->check(
            'deployment',
            'deployment_profile_ref_diff_not_explicit',
            'Deployment release-gate refs',
            $this->warningStatus($options),
            'Deployment profile is running without explicit --base/--head refs; the gate uses the selected diff source instead of a deployment target ref.',
            ['diff_mode' => $this->diffMode($options)],
            'For deployment CI, prefer explicit --base and --head refs that match the planned deploy/restore.',
            'deployment'
        );
    }

    /**
     * @param  list<array<string, mixed>>  $checks
     * @param  array<string, mixed>  $changedPlan
     * @return array<string, int>
     */
    private function summary(array $checks, array $changedPlan): array
    {
        $planSummary = (array) ($changedPlan['summary'] ?? []);

        return [
            'pass' => count(array_filter($checks, static fn (array $check): bool => ($check['status'] ?? null) === 'pass')),
            'warn' => count(array_filter($checks, static fn (array $check): bool => ($check['status'] ?? null) === 'warn')),
            'fail' => count(array_filter($checks, static fn (array $check): bool => ($check['status'] ?? null) === 'fail')),
            'changed_packages' => (int) ($planSummary['changed_packages'] ?? 0),
            'blocked_packages' => (int) ($planSummary['blocked'] ?? 0),
            'warnings' => (int) ($planSummary['warnings'] ?? 0),
            'deleted_cleanup_candidates' => (int) ($planSummary['deleted_cleanup_candidates'] ?? 0),
            'seed_candidates' => (int) ($planSummary['seed_candidates'] ?? 0),
            'refresh_candidates' => (int) ($planSummary['refresh_candidates'] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return list<string>
     */
    private function fatalWarningRules(array $options): array
    {
        return array_values(array_filter([
            (bool) $options['strict'] ? 'strict' : null,
            (bool) $options['fail_on_warnings'] ? 'fail_on_warnings' : null,
            (bool) $options['fail_on_lock'] ? 'fail_on_lock' : null,
            (bool) $options['fail_on_stale_lock'] ? 'fail_on_stale_lock' : null,
            (bool) $options['fail_on_sync_drift'] ? 'fail_on_sync_drift' : null,
            (bool) $options['fail_on_uninitialized_sync'] ? 'fail_on_uninitialized_sync' : null,
        ]));
    }

    /**
     * @param  list<array<string, mixed>>  $checks
     * @return list<string>
     */
    private function recommendations(array $checks): array
    {
        return collect($checks)
            ->pluck('recommendation')
            ->filter(static fn (mixed $value): bool => is_string($value) && trim($value) !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $packages
     * @return list<string>
     */
    private function packagePaths(array $packages): array
    {
        return array_values(array_map(
            static fn (array $package): string => (string) (($package['relative_path'] ?? null)
                ?: ($package['current_relative_path'] ?? null)
                ?: ($package['historical_relative_path'] ?? null)
                ?: ($package['package_key'] ?? 'unknown')),
            $packages
        ));
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function warningStatus(array $options): string
    {
        return ((bool) ($options['strict'] ?? false) || (bool) ($options['fail_on_warnings'] ?? false))
            ? 'fail'
            : 'warn';
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function diffMode(array $options): string
    {
        if (($options['base'] ?? '') !== '') {
            return 'refs';
        }

        if ((bool) ($options['staged'] ?? false)) {
            return 'staged';
        }

        return 'working_tree';
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function check(
        string $group,
        string $code,
        string $label,
        string $status,
        string $message,
        array $meta = [],
        mixed $recommendation = null,
        ?string $source = null
    ): array {
        return [
            'group' => $group,
            'code' => $code,
            'label' => $label,
            'status' => in_array($status, ['pass', 'warn', 'fail'], true) ? $status : 'fail',
            'message' => $message,
            'meta' => $meta,
            'recommendation' => is_string($recommendation) && trim($recommendation) !== '' ? $recommendation : null,
            'source' => $source ?? $group,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function errorResult(?string $targetInput, array $options, Throwable $exception): array
    {
        $check = $this->check(
            'release_gate',
            'release_gate_options_invalid',
            'Release gate options',
            'fail',
            $exception->getMessage(),
            ['exception_class' => $exception::class],
            'Fix content:release-gate options and rerun.',
            'release_gate'
        );

        return [
            'gate' => [
                'overall_status' => 'fail',
                'raw_status' => 'fail',
                'profile' => (string) ($options['profile'] ?? 'local'),
                'strict' => (bool) ($options['strict'] ?? false),
                'exit_would_fail' => true,
                'fatal_warning_rules' => [],
            ],
            'diff' => [
                'mode' => 'working_tree',
                'base' => $options['base'] ?? null,
                'head' => $options['head'] ?? null,
                'include_untracked' => (bool) ($options['include_untracked'] ?? false),
            ],
            'scope' => [
                'domains' => [],
                'target' => $targetInput,
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
            'checks' => [$check],
            'changed_plan' => null,
            'sync_state' => null,
            'lock' => null,
            'doctor' => null,
            'recommendations' => [$check['recommendation']],
            'options' => $options,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'release_gate_options',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# Content Release Gate',
            '',
            '- Overall Status: `' . (string) data_get($result, 'gate.overall_status', 'fail') . '`',
            '- Profile: `' . (string) data_get($result, 'gate.profile', 'local') . '`',
            '- Exit Would Fail: `' . (data_get($result, 'gate.exit_would_fail') ? 'true' : 'false') . '`',
            '- Domains: `' . implode(', ', (array) data_get($result, 'scope.domains', [])) . '`',
            '- Target: `' . (string) (data_get($result, 'scope.target') ?: '') . '`',
            '- Diff Mode: `' . (string) data_get($result, 'diff.mode', 'working_tree') . '`',
            '- Base: `' . (string) (data_get($result, 'diff.base') ?: '') . '`',
            '- Head: `' . (string) (data_get($result, 'diff.head') ?: '') . '`',
            '',
            '## Summary',
            '',
            '- Checks Pass: ' . (int) data_get($result, 'summary.pass', 0),
            '- Checks Warn: ' . (int) data_get($result, 'summary.warn', 0),
            '- Checks Fail: ' . (int) data_get($result, 'summary.fail', 0),
            '- Changed Packages: ' . (int) data_get($result, 'summary.changed_packages', 0),
            '- Blocked Packages: ' . (int) data_get($result, 'summary.blocked_packages', 0),
            '',
            '## Checks',
            '',
        ];

        foreach ((array) ($result['checks'] ?? []) as $check) {
            $check = (array) $check;
            $lines[] = '- `' . strtoupper((string) ($check['status'] ?? 'fail')) . '`'
                . ' [' . (string) ($check['source'] ?? $check['group'] ?? 'release_gate') . '] '
                . (string) ($check['code'] ?? 'unknown')
                . ' - '
                . (string) ($check['message'] ?? '');
        }

        $recommendations = (array) ($result['recommendations'] ?? []);

        if ($recommendations !== []) {
            $lines[] = '';
            $lines[] = '## Recommendations';
            $lines[] = '';

            foreach ($recommendations as $recommendation) {
                $lines[] = '- ' . (string) $recommendation;
            }
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
