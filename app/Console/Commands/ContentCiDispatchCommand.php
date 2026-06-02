<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ContentOpsCiDispatchService;
use Illuminate\Console\Command;
use Throwable;

class ContentCiDispatchCommand extends Command
{
    protected $signature = 'content:ci-dispatch
        {--ref= : Branch, tag, or ref to dispatch the workflow on}
        {--branch= : GitHub branch to dispatch the workflow on}
        {--sha= : Exact target SHA to validate in the workflow}
        {--workflow=contentops-release-gate.yml : ContentOps Release Gate workflow file}
        {--domains=v3,page-v3 : v3,page-v3 | v3 | page-v3}
        {--profile=ci : ci | local | deployment}
        {--with-release-check : Run release-check aggregation in CI}
        {--strict : Treat warnings as fatal in CI}
        {--base-ref= : Base ref passed into the workflow}
        {--head-ref= : Head ref passed into the workflow}
        {--dry-run : Print dispatch payload without calling GitHub}
        {--json : Output the dispatch result as JSON}
        {--write-report : Write a Markdown report into storage/app/content-ci-dispatches}
        {--force : Required for live workflow dispatch}';

    protected $description = 'Dispatch the existing read-only ContentOps Release Gate GitHub Actions workflow.';

    public function __construct(
        private readonly ContentOpsCiDispatchService $contentOpsCiDispatchService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $result = $this->contentOpsCiDispatchService->run($this->optionsPayload());
        } catch (Throwable $exception) {
            $result = $this->errorResult($exception);
        }

        if ($this->option('write-report')) {
            try {
                $path = $this->contentOpsCiDispatchService->writeReport($result);
                $result['artifacts']['report_path'] = storage_path('app/' . $path);
            } catch (Throwable $exception) {
                $result['dispatch']['status'] = 'failed';
                $result['error'] = [
                    'stage' => 'contentops_ci_dispatch_report',
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ];
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
            'ref' => (string) ($this->option('ref') ?? ''),
            'branch' => (string) ($this->option('branch') ?? ''),
            'sha' => (string) ($this->option('sha') ?? ''),
            'workflow' => (string) ($this->option('workflow') ?? 'contentops-release-gate.yml'),
            'domains' => (string) ($this->option('domains') ?? 'v3,page-v3'),
            'profile' => (string) ($this->option('profile') ?? 'ci'),
            'with_release_check' => $this->option('with-release-check')
                ? true
                : (bool) config('git-deployment.contentops_ci_status.dispatch_with_release_check', true),
            'strict' => $this->option('strict')
                ? true
                : (bool) config('git-deployment.contentops_ci_status.dispatch_strict', true),
            'base_ref' => (string) ($this->option('base-ref') ?? ''),
            'head_ref' => (string) ($this->option('head-ref') ?? ''),
            'dry_run' => (bool) $this->option('dry-run'),
            'force' => (bool) $this->option('force'),
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderHumanOutput(array $result): void
    {
        $this->line('Command: content:ci-dispatch');
        $this->line('Workflow: ' . (string) data_get($result, 'workflow.file', 'contentops-release-gate.yml'));
        $this->line('Dispatch Status: ' . (string) data_get($result, 'dispatch.status', 'failed'));
        $this->line('Dry Run: ' . (data_get($result, 'dispatch.dry_run') ? 'true' : 'false'));
        $this->line('Dispatch Ref: ' . (string) (data_get($result, 'dispatch.ref') ?: 'n/a'));
        $this->line('Target Branch: ' . (string) (data_get($result, 'target.branch') ?: 'n/a'));
        $this->line('Target SHA: ' . (string) (data_get($result, 'target.sha') ?: 'n/a'));

        if ((bool) data_get($result, 'run.found', false)) {
            $this->newLine();
            $this->line('Run ID: ' . (string) data_get($result, 'run.id', 'n/a'));
            $this->line('Run Status: ' . (string) data_get($result, 'run.status', 'n/a'));

            if (! empty(data_get($result, 'run.html_url'))) {
                $this->line('Run URL: ' . (string) data_get($result, 'run.html_url'));
            }
        } elseif (data_get($result, 'dispatch.status') === 'dispatched') {
            $this->warn('Workflow dispatch was accepted, but a matching run was not visible yet. Re-run content:ci-status shortly.');
        }

        if (is_array($result['error'] ?? null)) {
            $this->error((string) data_get($result, 'error.message', 'ContentOps CI dispatch failed.'));
        }

        if (! empty(data_get($result, 'artifacts.report_path'))) {
            $this->newLine();
            $this->line('Report: ' . (string) data_get($result, 'artifacts.report_path'));
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function exitCode(array $result): int
    {
        return (string) data_get($result, 'dispatch.status', 'failed') === 'failed'
            ? self::FAILURE
            : self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function errorResult(Throwable $exception): array
    {
        return [
            'provider' => 'github_actions',
            'workflow' => [
                'file' => (string) ($this->option('workflow') ?? 'contentops-release-gate.yml'),
                'name' => 'ContentOps Release Gate',
            ],
            'dispatch' => [
                'requested' => true,
                'dry_run' => (bool) $this->option('dry-run'),
                'ref' => $this->option('ref') ?: $this->option('branch') ?: null,
                'inputs' => [],
                'status' => 'failed',
                'http_status' => null,
            ],
            'target' => [
                'ref' => $this->option('ref') ?: null,
                'branch' => $this->option('branch') ?: null,
                'sha' => $this->option('sha') ?: null,
                'exact_sha_requested' => (bool) $this->option('sha'),
            ],
            'run' => [
                'found' => false,
                'id' => null,
                'status' => null,
                'conclusion' => null,
                'html_url' => null,
                'head_sha' => null,
            ],
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
}
