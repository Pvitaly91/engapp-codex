<?php

namespace App\Modules\GitDeployment\Services;

use App\Services\ContentDeployment\ChangedContentApplyService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ChangedContentDeploymentApplyService
{
    public function __construct(
        private readonly ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService,
        private readonly ChangedContentApplyService $changedContentApplyService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(array $context, array $options = []): array
    {
        $normalizedOptions = $this->normalizeOptions($options);
        $preview = $this->changedContentDeploymentPreviewService->preview(
            $context,
            $this->previewOptions($normalizedOptions)
        );

        return $this->runFromPreview($preview, $normalizedOptions);
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function runFromPreview(array $preview, array $options = []): array
    {
        $normalizedOptions = $this->normalizeOptions($options);
        $result = $this->resultTemplate($preview, $normalizedOptions);

        $baseRef = trim((string) data_get($preview, 'deployment.base_ref', ''));
        $headRef = trim((string) data_get($preview, 'deployment.head_ref', ''));
        $previewError = is_array($preview['error'] ?? null) ? $preview['error'] : null;

        if ($previewError !== null || $baseRef === '' || $headRef === '') {
            $result['status'] = 'preview_failed';
            $result['error'] = $previewError ?? [
                'stage' => 'deployment_preview',
                'message' => 'Deployment content apply requires resolved base/head refs from the deployment preview.',
            ];

            return $this->finalize($result, $normalizedOptions);
        }

        try {
            $applyResult = $this->changedContentApplyService->run(null, [
                'domains' => ['v3', 'page-v3'],
                'base' => $baseRef,
                'head' => $headRef,
                'dry_run' => (bool) $normalizedOptions['dry_run'],
                'force' => ! (bool) $normalizedOptions['dry_run'],
                'with_release_check' => (bool) $normalizedOptions['with_release_check'],
                'skip_release_check' => (bool) $normalizedOptions['skip_release_check'],
                'check_profile' => (string) $normalizedOptions['check_profile'],
                'strict' => (bool) $normalizedOptions['strict'],
            ]);
        } catch (Throwable $exception) {
            $applyResult = $this->applyErrorResult($normalizedOptions, $baseRef, $headRef, $exception);
        }

        $result['deployment']['content_apply_executed'] = ! (bool) $normalizedOptions['dry_run'];
        $result['content_apply']['executed'] = true;
        $result['content_apply']['result'] = $applyResult;
        $result['status'] = empty($applyResult['error'])
            ? ((bool) $normalizedOptions['dry_run'] ? 'ready' : 'completed')
            : 'content_apply_failed';
        $result['error'] = is_array($applyResult['error'] ?? null) ? $applyResult['error'] : null;

        return $this->finalize($result, $normalizedOptions);
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function deploymentFailedResult(array $preview, string $message, array $options = []): array
    {
        $normalizedOptions = $this->normalizeOptions($options);
        $result = $this->resultTemplate($preview, $normalizedOptions);
        $result['status'] = 'deploy_failed';
        $result['error'] = [
            'stage' => 'deploy',
            'message' => $message,
        ];

        return $this->finalize($result, $normalizedOptions);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $mode = Str::slug((string) data_get($result, 'deployment.mode', 'standard'));
        $sourceKind = Str::slug((string) data_get($result, 'deployment.source_kind', 'deploy'));
        $runType = (bool) data_get($result, 'content_apply.dry_run', false) ? 'dry-run' : 'live';
        $hash = substr(sha1(json_encode([
            data_get($result, 'deployment.base_ref'),
            data_get($result, 'deployment.head_ref'),
            data_get($result, 'deployment.content_apply_requested'),
            data_get($result, 'content_apply.dry_run'),
            data_get($result, 'status'),
        ])), 0, 8);
        $fileName = $mode . '-' . $sourceKind . '-content-apply-' . $runType . '-' . $hash . '.md';
        $relativePath = 'deployment-content-apply-reports/' . $fileName;

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        $checkProfile = strtolower(trim((string) ($options['check_profile']
            ?? config('git-deployment.content_apply.check_profile', 'release'))));

        if (! in_array($checkProfile, ['scaffold', 'release'], true)) {
            throw new RuntimeException('Unsupported deployment content-apply check profile. Use scaffold or release.');
        }

        return [
            'requested' => array_key_exists('requested', $options)
                ? (bool) $options['requested']
                : true,
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'with_release_check' => array_key_exists('with_release_check', $options)
                ? (bool) $options['with_release_check']
                : (bool) config('git-deployment.content_apply.with_release_check', true),
            'skip_release_check' => array_key_exists('skip_release_check', $options)
                ? (bool) $options['skip_release_check']
                : (bool) config('git-deployment.content_apply.skip_release_check', false),
            'check_profile' => $checkProfile,
            'strict' => array_key_exists('strict', $options)
                ? (bool) $options['strict']
                : (bool) config('git-deployment.content_apply.strict', true),
            'write_report' => array_key_exists('write_report', $options)
                ? (bool) $options['write_report']
                : true,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function previewOptions(array $options): array
    {
        return [
            'with_release_check' => (bool) $options['with_release_check'],
            'check_profile' => (string) $options['check_profile'],
            'strict' => (bool) $options['strict'],
        ];
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function resultTemplate(array $preview, array $options): array
    {
        return [
            'deployment' => [
                'mode' => (string) data_get($preview, 'deployment.mode', 'standard'),
                'source_kind' => (string) data_get($preview, 'deployment.source_kind', 'deploy'),
                'base_ref' => data_get($preview, 'deployment.base_ref'),
                'head_ref' => data_get($preview, 'deployment.head_ref'),
                'branch' => data_get($preview, 'deployment.branch'),
                'commit' => data_get($preview, 'deployment.commit'),
                'content_apply_requested' => (bool) $options['requested'],
                'content_apply_executed' => false,
            ],
            'preview' => $preview,
            'content_apply' => [
                'executed' => false,
                'dry_run' => (bool) $options['dry_run'],
                'result' => null,
            ],
            'gate' => [
                'strict' => (bool) data_get($preview, 'gate.strict', $options['strict']),
                'blocked' => (bool) data_get($preview, 'gate.blocked', false),
                'reasons' => array_values((array) data_get($preview, 'gate.reasons', [])),
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'status' => 'ready',
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function finalize(array $result, array $options): array
    {
        if ((bool) $options['write_report']) {
            $relativePath = $this->writeReport($result);
            $result['artifacts']['report_path'] = storage_path('app/' . $relativePath);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function applyErrorResult(array $options, string $baseRef, string $headRef, Throwable $exception): array
    {
        return [
            'diff' => [
                'mode' => 'refs',
                'base' => $baseRef,
                'head' => $headRef,
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => null,
                'domains' => ['v3', 'page-v3'],
                'resolved_roots' => [],
                'single_package' => false,
                'with_release_check' => (bool) $options['with_release_check'],
                'skip_release_check' => (bool) $options['skip_release_check'],
                'check_profile' => (string) $options['check_profile'],
                'strict' => (bool) $options['strict'],
            ],
            'plan' => [
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
                'error' => null,
            ],
            'preflight' => [
                'executed' => false,
                'summary' => [
                    'candidates' => 0,
                    'ok' => 0,
                    'warn' => 0,
                    'fail' => 0,
                    'skipped' => 0,
                ],
                'packages' => [],
            ],
            'execution' => [
                'dry_run' => (bool) $options['dry_run'],
                'force' => ! (bool) $options['dry_run'],
                'fail_fast' => true,
                'scope_transactional' => false,
                'cleanup_deleted' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => [],
                ],
                'upsert_present' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => [],
                ],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'deployment_content_apply',
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
        $applyResult = (array) data_get($result, 'content_apply.result', []);
        $lines = [
            '# Deployment Content Apply',
            '',
            '- Mode: `' . (string) data_get($result, 'deployment.mode', 'standard') . '`',
            '- Source: `' . (string) data_get($result, 'deployment.source_kind', 'deploy') . '`',
            '- Base: `' . (string) (data_get($result, 'deployment.base_ref') ?? '') . '`',
            '- Head: `' . (string) (data_get($result, 'deployment.head_ref') ?? '') . '`',
            '- Requested: `' . (data_get($result, 'deployment.content_apply_requested', false) ? 'true' : 'false') . '`',
            '- Live Executed: `' . (data_get($result, 'deployment.content_apply_executed', false) ? 'true' : 'false') . '`',
            '- Dry Run: `' . (data_get($result, 'content_apply.dry_run', false) ? 'true' : 'false') . '`',
            '- Status: `' . (string) data_get($result, 'status', 'ready') . '`',
            '',
            '## Gate',
            '',
            '- Blocked: `' . (data_get($result, 'gate.blocked', false) ? 'true' : 'false') . '`',
            '- Strict: `' . (data_get($result, 'gate.strict', false) ? 'true' : 'false') . '`',
        ];

        $gateReasons = array_values(array_filter((array) data_get($result, 'gate.reasons', [])));

        if ($gateReasons !== []) {
            foreach ($gateReasons as $reason) {
                $lines[] = '- Reason: ' . $reason;
            }
        }

        $lines[] = '';
        $lines[] = '## Changed Content Apply';
        $lines[] = '';
        $lines[] = '- Changed Packages: ' . (int) data_get($applyResult, 'plan.summary.changed_packages', 0);
        $lines[] = '- Deleted Cleanup Candidates: ' . (int) data_get($applyResult, 'plan.summary.deleted_cleanup_candidates', 0);
        $lines[] = '- Seed Candidates: ' . (int) data_get($applyResult, 'plan.summary.seed_candidates', 0);
        $lines[] = '- Refresh Candidates: ' . (int) data_get($applyResult, 'plan.summary.refresh_candidates', 0);
        $lines[] = '- Preflight OK: ' . (int) data_get($applyResult, 'preflight.summary.ok', 0);
        $lines[] = '- Preflight Warn: ' . (int) data_get($applyResult, 'preflight.summary.warn', 0);
        $lines[] = '- Preflight Fail: ' . (int) data_get($applyResult, 'preflight.summary.fail', 0);
        $lines[] = '';
        $lines[] = '## Cleanup Deleted Phase';
        $lines[] = '';

        $cleanupPackages = (array) data_get($applyResult, 'execution.cleanup_deleted.packages', []);

        if ($cleanupPackages === []) {
            $lines[] = '- None.';
        } else {
            foreach ($cleanupPackages as $package) {
                $lines[] = '- [' . (string) ($package['domain'] ?? 'unknown') . '] `'
                    . (string) ($package['relative_path'] ?? $package['package_key'] ?? '')
                    . '` | status=`' . (string) ($package['status'] ?? 'pending') . '`';
            }
        }

        $lines[] = '';
        $lines[] = '## Upsert Present Phase';
        $lines[] = '';

        $upsertPackages = (array) data_get($applyResult, 'execution.upsert_present.packages', []);

        if ($upsertPackages === []) {
            $lines[] = '- None.';
        } else {
            foreach ($upsertPackages as $package) {
                $lines[] = '- [' . (string) ($package['domain'] ?? 'unknown') . '] `'
                    . (string) ($package['relative_path'] ?? $package['package_key'] ?? '')
                    . '` | action=`' . (string) ($package['action'] ?? 'skip') . '`'
                    . ' | status=`' . (string) ($package['status'] ?? 'pending') . '`';
            }
        }

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) data_get($result, 'error.stage', 'deployment_content_apply') . '`';
            $lines[] = '- Message: ' . (string) data_get($result, 'error.message', 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
