<?php

namespace App\Services\ContentDeployment;

use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use App\Modules\GitDeployment\Services\GitHubApiClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ContentOpsCiDispatchService
{
    public function __construct(
        private readonly DeploymentGitRefProbe $deploymentGitRefProbe,
        private readonly ContentOpsCiStatusService $contentOpsCiStatusService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(array $options = []): array
    {
        try {
            $resolved = $this->normalizeOptions($options);
            $target = $this->resolveTarget($resolved);
            $inputs = $this->workflowInputs($resolved, $target);
        } catch (Throwable $exception) {
            return $this->failedResult($options, null, [], $exception);
        }

        $dispatch = [
            'requested' => true,
            'dry_run' => $resolved['dry_run'],
            'ref' => $target['dispatch_ref'],
            'inputs' => $inputs,
            'status' => $resolved['dry_run'] ? 'simulated' : 'pending',
            'http_status' => null,
        ];
        $run = [
            'found' => false,
            'id' => null,
            'status' => null,
            'conclusion' => null,
            'html_url' => null,
            'head_sha' => null,
        ];
        $statusResult = null;
        $error = null;

        if (! $resolved['dry_run']) {
            try {
                $response = app(GitHubApiClient::class)->dispatchWorkflow(
                    $resolved['workflow'],
                    $target['dispatch_ref'],
                    $inputs
                );
                $dispatch['status'] = 'dispatched';
                $dispatch['http_status'] = (int) ($response['http_status'] ?? 204);
                $statusResult = $this->contentOpsCiStatusService->run([
                    'ref' => $target['status_ref'],
                    'branch' => $target['branch'],
                    'sha' => $target['sha'],
                    'workflow' => $resolved['workflow'],
                    'allow_in_progress' => true,
                    'require_success' => false,
                    'max_age_minutes' => $resolved['max_age_minutes'],
                ]);
                $run = $this->runFromStatus($statusResult);
            } catch (Throwable $exception) {
                $dispatch['status'] = 'failed';
                $error = [
                    'stage' => 'github_actions_dispatch',
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ];
            }
        }

        return [
            'provider' => 'github_actions',
            'workflow' => [
                'file' => $resolved['workflow'],
                'name' => $resolved['workflow_name'],
            ],
            'dispatch' => $dispatch,
            'target' => [
                'ref' => $target['status_ref'],
                'branch' => $target['branch'],
                'sha' => $target['sha'],
                'exact_sha_requested' => $target['sha'] !== null,
            ],
            'run' => $run,
            'status_result' => $statusResult,
            'options' => $resolved,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => $error,
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
        $relativePath = 'content-ci-dispatches/'
            . now()->format('Y/m/d/His')
            . '-'
            . Str::slug($target !== '' ? $target : 'unknown')
            . '-'
            . (string) data_get($result, 'dispatch.status', 'unknown')
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
        $configuredWorkflow = (string) config('git-deployment.contentops_ci_status.workflow_file', 'contentops-release-gate.yml');
        $workflow = trim((string) ($options['workflow'] ?? $configuredWorkflow));

        if ($workflow !== $configuredWorkflow) {
            throw new InvalidArgumentException('Only the configured ContentOps Release Gate workflow can be dispatched.');
        }

        $profile = strtolower(trim((string) ($options['profile'] ?? 'ci')));

        if (! in_array($profile, ['ci', 'local', 'deployment'], true)) {
            throw new InvalidArgumentException('Unsupported profile. Use ci, local, or deployment.');
        }

        $domains = $this->normalizeDomains($options['domains'] ?? 'v3,page-v3');
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $force = (bool) ($options['force'] ?? false);

        if (! $dryRun && ! $force) {
            throw new RuntimeException('Live ContentOps CI dispatch requires --force.');
        }

        return [
            'workflow' => $workflow,
            'workflow_name' => (string) config('git-deployment.contentops_ci_status.workflow_name', 'ContentOps Release Gate'),
            'ref' => trim((string) ($options['ref'] ?? '')),
            'branch' => $this->normalizeBranch((string) ($options['branch'] ?? '')),
            'sha' => strtolower(trim((string) ($options['sha'] ?? ''))),
            'base_ref' => trim((string) ($options['base_ref'] ?? '')),
            'head_ref' => trim((string) ($options['head_ref'] ?? '')),
            'domains' => implode(',', $domains),
            'profile' => $profile,
            'with_release_check' => (bool) ($options['with_release_check'] ?? config('git-deployment.contentops_ci_status.dispatch_with_release_check', true)),
            'strict' => (bool) ($options['strict'] ?? config('git-deployment.contentops_ci_status.dispatch_strict', true)),
            'dry_run' => $dryRun,
            'force' => $force,
            'max_age_minutes' => $options['max_age_minutes'] ?? config('git-deployment.contentops_ci_status.max_age_minutes'),
            'default_base_ref' => (string) config('git-deployment.contentops_ci_status.default_base_ref', 'origin/main'),
        ];
    }

    /**
     * @return list<string>
     */
    private function normalizeDomains(mixed $value): array
    {
        $tokens = is_array($value)
            ? $value
            : preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        $domains = collect($tokens ?: [])
            ->map(static fn (mixed $domain): string => str_replace('_', '-', strtolower(trim((string) $domain))))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($domains === []) {
            $domains = ['v3', 'page-v3'];
        }

        $invalid = array_values(array_diff($domains, ['v3', 'page-v3']));

        if ($invalid !== []) {
            throw new InvalidArgumentException('Unsupported --domains value(s): ' . implode(', ', $invalid));
        }

        return $domains;
    }

    /**
     * @param  array<string, mixed>  $resolved
     * @return array<string, mixed>
     */
    private function resolveTarget(array $resolved): array
    {
        $branch = $resolved['branch'];
        $ref = $resolved['ref'];
        $sha = $resolved['sha'];
        $dispatchRef = '';

        if ($branch !== '') {
            $dispatchRef = $branch;
        } elseif ($ref !== '' && ! $this->looksLikeSha($ref)) {
            $dispatchRef = $this->normalizeBranch($ref);
        }

        if ($dispatchRef === '') {
            throw new RuntimeException('ContentOps CI dispatch requires --branch or a non-SHA --ref because GitHub workflow_dispatch needs a branch or tag ref.');
        }

        if ($sha === '') {
            if ($ref !== '') {
                $resolvedRefSha = $this->deploymentGitRefProbe->resolveCommit($ref);
                if ($resolvedRefSha !== null) {
                    $sha = strtolower($resolvedRefSha);
                }
            }

            if ($sha === '' && $branch !== '') {
                $remoteSha = $this->deploymentGitRefProbe->remoteBranchSha($branch);
                if ($remoteSha !== null) {
                    $sha = strtolower($remoteSha);
                }
            }
        }

        $headRef = $resolved['head_ref'] !== ''
            ? $resolved['head_ref']
            : ($sha !== '' ? $sha : ($ref !== '' ? $ref : $branch));
        $baseRef = $resolved['base_ref'] !== ''
            ? $resolved['base_ref']
            : $resolved['default_base_ref'];

        if ($baseRef === '') {
            throw new RuntimeException('ContentOps CI dispatch requires --base-ref or git-deployment.contentops_ci_status.default_base_ref.');
        }

        return [
            'dispatch_ref' => $dispatchRef,
            'status_ref' => $ref !== '' ? $ref : $dispatchRef,
            'branch' => $branch !== '' ? $branch : $dispatchRef,
            'sha' => $sha !== '' ? $sha : null,
            'base_ref' => $baseRef,
            'head_ref' => $headRef,
        ];
    }

    /**
     * @param  array<string, mixed>  $resolved
     * @param  array<string, mixed>  $target
     * @return array<string, string>
     */
    private function workflowInputs(array $resolved, array $target): array
    {
        return [
            'base_ref' => (string) $target['base_ref'],
            'head_ref' => (string) $target['head_ref'],
            'domains' => (string) $resolved['domains'],
            'profile' => (string) $resolved['profile'],
            'with_release_check' => $resolved['with_release_check'] ? 'true' : 'false',
            'strict' => $resolved['strict'] ? 'true' : 'false',
            'target_sha' => (string) ($target['sha'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $statusResult
     * @return array<string, mixed>
     */
    private function runFromStatus(?array $statusResult): array
    {
        $run = is_array($statusResult) ? (array) ($statusResult['run'] ?? []) : [];

        if ($run === []) {
            return [
                'found' => false,
                'id' => null,
                'status' => null,
                'conclusion' => null,
                'html_url' => null,
                'head_sha' => null,
            ];
        }

        return [
            'found' => true,
            'id' => $run['id'] ?? null,
            'status' => $run['status'] ?? null,
            'conclusion' => $run['conclusion'] ?? null,
            'html_url' => $run['html_url'] ?? null,
            'head_sha' => $run['head_sha'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>|null  $target
     * @param  array<string, mixed>  $inputs
     * @return array<string, mixed>
     */
    private function failedResult(array $options, ?array $target, array $inputs, Throwable $exception): array
    {
        $workflow = (string) ($options['workflow'] ?? config('git-deployment.contentops_ci_status.workflow_file', 'contentops-release-gate.yml'));

        return [
            'provider' => 'github_actions',
            'workflow' => [
                'file' => $workflow,
                'name' => (string) config('git-deployment.contentops_ci_status.workflow_name', 'ContentOps Release Gate'),
            ],
            'dispatch' => [
                'requested' => true,
                'dry_run' => (bool) ($options['dry_run'] ?? false),
                'ref' => $target['dispatch_ref'] ?? null,
                'inputs' => $inputs,
                'status' => 'failed',
                'http_status' => null,
            ],
            'target' => [
                'ref' => $target['status_ref'] ?? ($options['ref'] ?? null),
                'branch' => $target['branch'] ?? ($options['branch'] ?? null),
                'sha' => $target['sha'] ?? ($options['sha'] ?? null),
                'exact_sha_requested' => ! empty($target['sha'] ?? $options['sha'] ?? null),
            ],
            'run' => [
                'found' => false,
                'id' => null,
                'status' => null,
                'conclusion' => null,
                'html_url' => null,
                'head_sha' => null,
            ],
            'status_result' => null,
            'options' => $options,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'contentops_ci_dispatch',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
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

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# ContentOps CI Dispatch',
            '',
            '- Workflow: `' . (string) data_get($result, 'workflow.file', 'contentops-release-gate.yml') . '`',
            '- Dispatch status: `' . (string) data_get($result, 'dispatch.status', 'unknown') . '`',
            '- Dry run: `' . (data_get($result, 'dispatch.dry_run') ? 'true' : 'false') . '`',
            '- Dispatch ref: `' . (string) (data_get($result, 'dispatch.ref') ?: 'n/a') . '`',
            '- Target branch: `' . (string) (data_get($result, 'target.branch') ?: 'n/a') . '`',
            '- Target SHA: `' . (string) (data_get($result, 'target.sha') ?: 'n/a') . '`',
            '- Run found: `' . (data_get($result, 'run.found') ? 'true' : 'false') . '`',
        ];

        if (! empty(data_get($result, 'run.html_url'))) {
            $lines[] = '- Run URL: ' . (string) data_get($result, 'run.html_url');
        }

        $lines[] = '';
        $lines[] = '## Inputs';

        foreach ((array) data_get($result, 'dispatch.inputs', []) as $key => $value) {
            $lines[] = '- `' . (string) $key . '`: `' . (string) $value . '`';
        }

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '- ' . (string) data_get($result, 'error.message', 'Unknown dispatch error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
