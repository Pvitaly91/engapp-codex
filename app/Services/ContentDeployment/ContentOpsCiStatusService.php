<?php

namespace App\Services\ContentDeployment;

use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use App\Modules\GitDeployment\Services\GitHubApiClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ContentOpsCiStatusService
{
    public function __construct(
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
        $target = $this->resolveTarget($resolved);

        if (! $resolved['enabled']) {
            return $this->unavailableResult($resolved, $target, 'ci_status_disabled', 'ContentOps CI status lookup is disabled by configuration.');
        }

        if (! config('git-deployment.github.owner') || ! config('git-deployment.github.repo')) {
            return $this->unavailableResult($resolved, $target, 'ci_github_config_missing', 'GitHub owner/repo config is missing.');
        }

        try {
            $runs = $this->workflowRuns($resolved, $target);
        } catch (Throwable $exception) {
            return $this->unavailableResult($resolved, $target, 'ci_unavailable', $exception->getMessage(), $exception);
        }

        $matched = $this->matchRun($runs, $target, $resolved);
        $readiness = $this->readiness($matched, $target, $resolved);

        return [
            'provider' => 'github_actions',
            'workflow' => [
                'file' => $resolved['workflow'],
                'name' => $resolved['workflow_name'],
            ],
            'target' => $target,
            'match' => [
                'run_found' => is_array($matched['run']),
                'exact_sha_verified' => (bool) $matched['exact_sha_verified'],
                'sha_mismatch' => (bool) $matched['sha_mismatch'],
                'matched_by' => $matched['matched_by'],
            ],
            'run' => $this->mapRun($matched['run']),
            'readiness' => $readiness,
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
        $target = (string) (data_get($result, 'target.sha')
            ?: data_get($result, 'target.branch')
            ?: data_get($result, 'target.ref')
            ?: 'unknown');
        $status = (string) data_get($result, 'readiness.status', 'fail');
        $relativePath = 'content-ci-status/'
            . now()->format('Y/m/d/His')
            . '-'
            . Str::slug($target !== '' ? $target : 'unknown')
            . '-'
            . $status
            . '.md';

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        $workflow = trim((string) ($options['workflow'] ?? config('git-deployment.contentops_ci_status.workflow_file', 'contentops-release-gate.yml')));

        if ($workflow === '') {
            throw new InvalidArgumentException('ContentOps CI workflow file is required.');
        }

        $maxAge = $options['max_age_minutes'] ?? config('git-deployment.contentops_ci_status.max_age_minutes');
        $maxAge = $maxAge === null || $maxAge === '' ? null : (int) $maxAge;

        if ($maxAge !== null && $maxAge < 0) {
            throw new InvalidArgumentException('--max-age-minutes cannot be negative.');
        }

        return [
            'enabled' => (bool) config('git-deployment.contentops_ci_status.enabled', true),
            'workflow' => $workflow,
            'workflow_name' => (string) config('git-deployment.contentops_ci_status.workflow_name', 'ContentOps Release Gate'),
            'ref' => trim((string) ($options['ref'] ?? '')),
            'branch' => $this->normalizeBranch((string) ($options['branch'] ?? '')),
            'sha' => strtolower(trim((string) ($options['sha'] ?? ''))),
            'strict' => (bool) ($options['strict'] ?? false),
            'require_success' => (bool) ($options['require_success'] ?? false),
            'allow_in_progress' => array_key_exists('allow_in_progress', $options)
                ? (bool) $options['allow_in_progress']
                : (bool) config('git-deployment.contentops_ci_status.allow_in_progress', false),
            'max_age_minutes' => $maxAge,
            'require_exact_sha' => (bool) config('git-deployment.contentops_ci_status.require_exact_sha', true),
            'accepted_conclusions' => $this->acceptedConclusions(),
            'cache_ttl_seconds' => max(0, (int) config('git-deployment.contentops_ci_status.cache_ttl_seconds', 0)),
        ];
    }

    /**
     * @return list<string>
     */
    private function acceptedConclusions(): array
    {
        $configured = config('git-deployment.contentops_ci_status.accepted_conclusions', ['success']);
        $values = is_array($configured) ? $configured : ['success'];

        return collect($values)
            ->map(static fn (mixed $value): string => strtolower(trim((string) $value)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $resolved
     * @return array<string, mixed>
     */
    private function resolveTarget(array $resolved): array
    {
        $ref = $resolved['ref'];
        $branch = $resolved['branch'];
        $sha = $resolved['sha'];
        $resolvedFromGit = false;

        if ($sha === '' && $ref !== '') {
            $commit = $this->deploymentGitRefProbe->resolveCommit($ref);
            if ($commit !== null) {
                $sha = strtolower($commit);
                $resolvedFromGit = true;
            } elseif ($this->looksLikeSha($ref)) {
                $sha = strtolower($ref);
            }

            if ($branch === '' && ! $this->looksLikeSha($ref)) {
                $branch = $this->normalizeBranch($ref);
            }
        }

        if ($sha === '' && $branch === '' && $ref === '') {
            $commit = $this->deploymentGitRefProbe->currentHeadCommit();
            if ($commit !== null) {
                $sha = strtolower($commit);
                $ref = 'HEAD';
                $resolvedFromGit = true;
            }
        }

        return [
            'ref' => $ref !== '' ? $ref : null,
            'branch' => $branch !== '' ? $branch : null,
            'sha' => $sha !== '' ? strtolower($sha) : null,
            'resolved_from_git' => $resolvedFromGit,
        ];
    }

    /**
     * @param  array<string, mixed>  $resolved
     * @param  array<string, mixed>  $target
     * @return list<array<string, mixed>>
     */
    private function workflowRuns(array $resolved, array $target): array
    {
        $query = [
            'per_page' => 50,
        ];

        if (! empty($target['branch'])) {
            $query['branch'] = (string) $target['branch'];
        }

        $fetch = fn (): array => app(GitHubApiClient::class)->listWorkflowRuns($resolved['workflow'], $query);

        $response = $resolved['cache_ttl_seconds'] > 0
            ? Cache::remember($this->cacheKey($resolved, $query), $resolved['cache_ttl_seconds'], $fetch)
            : $fetch();

        return array_values(array_filter(
            (array) ($response['workflow_runs'] ?? []),
            static fn (mixed $run): bool => is_array($run)
        ));
    }

    /**
     * @param  array<string, mixed>  $resolved
     * @param  array<string, mixed>  $query
     */
    private function cacheKey(array $resolved, array $query): string
    {
        return 'contentops_ci_status:'
            . sha1(json_encode([
                'owner' => config('git-deployment.github.owner'),
                'repo' => config('git-deployment.github.repo'),
                'workflow' => $resolved['workflow'],
                'query' => $query,
            ], JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param  list<array<string, mixed>>  $runs
     * @param  array<string, mixed>  $target
     * @param  array<string, mixed>  $resolved
     * @return array{run:?array,exact_sha_verified:bool,sha_mismatch:bool,matched_by:string}
     */
    private function matchRun(array $runs, array $target, array $resolved): array
    {
        $sha = strtolower((string) ($target['sha'] ?? ''));
        $branch = (string) ($target['branch'] ?? '');

        $branchRuns = $branch !== ''
            ? array_values(array_filter($runs, fn (array $run): bool => $this->branchMatches($run, $branch)))
            : $runs;

        if ($sha !== '') {
            $exact = collect($branchRuns)
                ->first(fn (array $run): bool => $this->shaMatches((string) ($run['head_sha'] ?? ''), $sha));

            if (is_array($exact)) {
                return [
                    'run' => $exact,
                    'exact_sha_verified' => true,
                    'sha_mismatch' => false,
                    'matched_by' => 'exact_sha',
                ];
            }

            $latest = $branchRuns[0] ?? ($runs[0] ?? null);

            return [
                'run' => is_array($latest) ? $latest : null,
                'exact_sha_verified' => false,
                'sha_mismatch' => is_array($latest) && $resolved['require_exact_sha'],
                'matched_by' => is_array($latest) ? 'latest_available' : 'none',
            ];
        }

        $latest = $branchRuns[0] ?? null;

        return [
            'run' => is_array($latest) ? $latest : null,
            'exact_sha_verified' => false,
            'sha_mismatch' => false,
            'matched_by' => is_array($latest) ? ($branch !== '' ? 'branch_latest' : 'latest') : 'none',
        ];
    }

    private function branchMatches(array $run, string $branch): bool
    {
        return $this->normalizeBranch((string) ($run['head_branch'] ?? '')) === $this->normalizeBranch($branch);
    }

    /**
     * @param  array{run:?array,exact_sha_verified:bool,sha_mismatch:bool,matched_by:string}  $matched
     * @param  array<string, mixed>  $target
     * @param  array<string, mixed>  $resolved
     * @return array<string, mixed>
     */
    private function readiness(array $matched, array $target, array $resolved): array
    {
        $warnings = [];
        $failures = [];
        $run = $matched['run'];
        $code = 'ci_missing';
        $message = 'No ContentOps CI workflow run was found for the requested target.';
        $highLevel = 'missing';
        $rawReadiness = 'warn';

        if ($run === null) {
            if ($resolved['require_success']) {
                $failures[] = 'No matching ContentOps CI workflow run was found.';
                $rawReadiness = 'fail';
            } else {
                $warnings[] = 'No matching ContentOps CI workflow run was found.';
            }
        } elseif ($matched['sha_mismatch']) {
            $highLevel = 'sha_mismatch';
            $code = 'ci_sha_mismatch';
            $message = 'Latest ContentOps CI workflow run does not match the target SHA.';
            $failures[] = 'The workflow run head SHA does not match the deploy target SHA.';
            $rawReadiness = 'fail';
        } else {
            $status = strtolower((string) ($run['status'] ?? ''));
            $conclusion = strtolower((string) ($run['conclusion'] ?? ''));

            if (in_array($status, ['queued', 'requested', 'waiting', 'pending', 'in_progress'], true)) {
                $highLevel = 'running';
                $code = 'ci_running';
                $message = 'ContentOps CI workflow run is still running.';

                if ($resolved['require_success'] || ! $resolved['allow_in_progress']) {
                    $failures[] = 'ContentOps CI workflow run has not completed successfully yet.';
                    $rawReadiness = $resolved['require_success'] ? 'fail' : 'warn';
                } else {
                    $warnings[] = 'ContentOps CI workflow run is in progress; it is not a completed success.';
                }
            } elseif ($status === 'completed' && in_array($conclusion, $resolved['accepted_conclusions'], true)) {
                $highLevel = 'passed';
                $code = 'ci_passed';
                $message = 'ContentOps CI workflow run completed successfully for the target.';
                $rawReadiness = 'pass';

                if (! $matched['exact_sha_verified']) {
                    $warnings[] = 'ContentOps CI success is not verified against an exact target SHA.';
                    $rawReadiness = 'warn';
                    $code = 'ci_passed_sha_unverified';
                    $message = 'ContentOps CI workflow run passed, but exact target SHA was not verified.';
                }

                if ($this->isStale($run, $resolved['max_age_minutes'])) {
                    $highLevel = 'stale';
                    $code = 'ci_stale';
                    $message = 'ContentOps CI workflow run passed but is older than the configured max age.';
                    $warnings[] = 'ContentOps CI workflow run is stale by age.';
                    $rawReadiness = $resolved['require_success'] ? 'fail' : 'warn';

                    if ($resolved['require_success']) {
                        $failures[] = 'ContentOps CI workflow run is stale by age.';
                    }
                }
            } else {
                $highLevel = 'failed';
                $code = 'ci_failed';
                $message = 'ContentOps CI workflow run did not complete successfully.';
                $failures[] = 'Workflow conclusion is [' . ($conclusion !== '' ? $conclusion : 'unknown') . '].';
                $rawReadiness = 'fail';
            }
        }

        $exitWouldFail = $rawReadiness === 'fail'
            || ($resolved['strict'] && $rawReadiness === 'warn');

        return [
            'status' => $exitWouldFail && $rawReadiness === 'warn' ? 'fail' : $rawReadiness,
            'raw_status' => $rawReadiness,
            'high_level_status' => $highLevel,
            'code' => $exitWouldFail && $rawReadiness === 'warn' ? 'ci_warning_fatal' : $code,
            'message' => $message,
            'warnings' => array_values($warnings),
            'failures' => array_values($failures),
            'strict' => $resolved['strict'],
            'require_success' => $resolved['require_success'],
            'allow_in_progress' => $resolved['allow_in_progress'],
            'exit_would_fail' => $exitWouldFail,
            'target_sha_known' => ! empty($target['sha']),
        ];
    }

    private function isStale(array $run, ?int $maxAgeMinutes): bool
    {
        if ($maxAgeMinutes === null || $maxAgeMinutes <= 0) {
            return false;
        }

        $timestamp = (string) ($run['updated_at'] ?? $run['run_started_at'] ?? $run['created_at'] ?? '');

        if ($timestamp === '') {
            return false;
        }

        try {
            return Carbon::parse($timestamp)->addMinutes($maxAgeMinutes)->lessThan(now());
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  array<string, mixed>|null  $run
     * @return array<string, mixed>|null
     */
    private function mapRun(?array $run): ?array
    {
        if ($run === null) {
            return null;
        }

        return [
            'id' => $run['id'] ?? null,
            'status' => $run['status'] ?? null,
            'conclusion' => $run['conclusion'] ?? null,
            'head_branch' => $run['head_branch'] ?? null,
            'head_sha' => $run['head_sha'] ?? null,
            'html_url' => $run['html_url'] ?? null,
            'created_at' => $run['created_at'] ?? null,
            'updated_at' => $run['updated_at'] ?? null,
            'run_started_at' => $run['run_started_at'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $resolved
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    private function unavailableResult(array $resolved, array $target, string $code, string $message, ?Throwable $exception = null): array
    {
        $readiness = $this->unavailableReadiness($resolved, $code, $message);
        $readiness['target_sha_known'] = ! empty($target['sha']);

        return [
            'provider' => 'github_actions',
            'workflow' => [
                'file' => $resolved['workflow'],
                'name' => $resolved['workflow_name'],
            ],
            'target' => $target,
            'match' => [
                'run_found' => false,
                'exact_sha_verified' => false,
                'sha_mismatch' => false,
                'matched_by' => 'none',
            ],
            'run' => null,
            'readiness' => $readiness,
            'options' => $resolved,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => $exception === null ? null : [
                'stage' => 'github_actions_status',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $resolved
     * @return array<string, mixed>
     */
    private function unavailableReadiness(array $resolved, string $code, string $message): array
    {
        $raw = $resolved['require_success'] ? 'fail' : 'warn';
        $exitWouldFail = $raw === 'fail' || ($resolved['strict'] && $raw === 'warn');

        return [
            'status' => $exitWouldFail && $raw === 'warn' ? 'fail' : $raw,
            'raw_status' => $raw,
            'high_level_status' => 'unavailable',
            'code' => $exitWouldFail && $raw === 'warn' ? 'ci_warning_fatal' : $code,
            'message' => $message,
            'warnings' => $raw === 'warn' ? [$message] : [],
            'failures' => $raw === 'fail' ? [$message] : [],
            'strict' => $resolved['strict'],
            'require_success' => $resolved['require_success'],
            'allow_in_progress' => $resolved['allow_in_progress'],
            'exit_would_fail' => $exitWouldFail,
            'target_sha_known' => false,
        ];
    }

    private function normalizeBranch(string $branch): string
    {
        $normalized = trim($branch);
        $normalized = preg_replace('#^refs/heads/#', '', $normalized) ?? $normalized;
        $normalized = preg_replace('#^origin/#', '', $normalized) ?? $normalized;

        return trim($normalized);
    }

    private function looksLikeSha(string $value): bool
    {
        return preg_match('/^[0-9a-f]{7,40}$/i', trim($value)) === 1;
    }

    private function shaMatches(string $runSha, string $targetSha): bool
    {
        $runSha = strtolower(trim($runSha));
        $targetSha = strtolower(trim($targetSha));

        if ($runSha === '' || $targetSha === '') {
            return false;
        }

        return $runSha === $targetSha
            || (strlen($targetSha) < 40 && str_starts_with($runSha, $targetSha));
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# ContentOps CI Status',
            '',
            '- Provider: `github_actions`',
            '- Workflow: `' . (string) data_get($result, 'workflow.file', 'contentops-release-gate.yml') . '`',
            '- Target ref: `' . (string) (data_get($result, 'target.ref') ?: 'n/a') . '`',
            '- Target branch: `' . (string) (data_get($result, 'target.branch') ?: 'n/a') . '`',
            '- Target SHA: `' . (string) (data_get($result, 'target.sha') ?: 'n/a') . '`',
            '- Readiness: `' . (string) data_get($result, 'readiness.status', 'fail') . '`',
            '- Code: `' . (string) data_get($result, 'readiness.code', 'ci_unknown') . '`',
            '- Message: ' . (string) data_get($result, 'readiness.message', ''),
        ];

        if (! empty(data_get($result, 'run.html_url'))) {
            $lines[] = '- Run URL: ' . (string) data_get($result, 'run.html_url');
        }

        $warnings = (array) data_get($result, 'readiness.warnings', []);
        if ($warnings !== []) {
            $lines[] = '';
            $lines[] = '## Warnings';
            foreach ($warnings as $warning) {
                $lines[] = '- ' . (string) $warning;
            }
        }

        $failures = (array) data_get($result, 'readiness.failures', []);
        if ($failures !== []) {
            $lines[] = '';
            $lines[] = '## Failures';
            foreach ($failures as $failure) {
                $lines[] = '- ' . (string) $failure;
            }
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
