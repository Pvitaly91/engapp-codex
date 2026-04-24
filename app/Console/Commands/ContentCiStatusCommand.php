<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ContentOpsCiStatusService;
use Illuminate\Console\Command;
use Throwable;

class ContentCiStatusCommand extends Command
{
    protected $signature = 'content:ci-status
        {--ref= : Branch, tag, SHA, or local git ref to check}
        {--branch= : GitHub branch filter}
        {--sha= : Exact target SHA to verify}
        {--workflow=contentops-release-gate.yml : GitHub Actions workflow file}
        {--json : Output the CI status result as JSON}
        {--write-report : Write a Markdown report into storage/app/content-ci-status}
        {--strict : Treat warnings as fatal}
        {--require-success : Require a completed successful matching workflow run}
        {--allow-in-progress : Permit queued/in_progress runs as a non-success warning}
        {--max-age-minutes= : Mark completed runs older than this as stale}';

    protected $description = 'Check read-only GitHub Actions status for the ContentOps Release Gate workflow.';

    public function __construct(
        private readonly ContentOpsCiStatusService $contentOpsCiStatusService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $result = $this->contentOpsCiStatusService->run($this->optionsPayload());
        } catch (Throwable $exception) {
            $result = $this->errorResult($exception);
        }

        if ($this->option('write-report')) {
            try {
                $path = $this->contentOpsCiStatusService->writeReport($result);
                $result['artifacts']['report_path'] = storage_path('app/' . $path);
            } catch (Throwable $exception) {
                $result['readiness']['status'] = 'fail';
                $result['readiness']['raw_status'] = 'fail';
                $result['readiness']['code'] = 'ci_status_report_write_failed';
                $result['readiness']['message'] = 'Unable to write ContentOps CI status report: ' . $exception->getMessage();
                $result['readiness']['failures'][] = $result['readiness']['message'];
                $result['readiness']['exit_would_fail'] = true;
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
        $maxAge = $this->option('max-age-minutes');

        return [
            'ref' => (string) ($this->option('ref') ?? ''),
            'branch' => (string) ($this->option('branch') ?? ''),
            'sha' => (string) ($this->option('sha') ?? ''),
            'workflow' => (string) ($this->option('workflow') ?? 'contentops-release-gate.yml'),
            'strict' => (bool) $this->option('strict'),
            'require_success' => (bool) $this->option('require-success'),
            'allow_in_progress' => (bool) $this->option('allow-in-progress'),
            'max_age_minutes' => $maxAge === null || $maxAge === '' ? null : (int) $maxAge,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderHumanOutput(array $result): void
    {
        $this->line('Command: content:ci-status');
        $this->line('Workflow: ' . (string) data_get($result, 'workflow.file', 'contentops-release-gate.yml'));
        $this->line('Target Ref: ' . (string) (data_get($result, 'target.ref') ?: 'n/a'));
        $this->line('Target Branch: ' . (string) (data_get($result, 'target.branch') ?: 'n/a'));
        $this->line('Target SHA: ' . (string) (data_get($result, 'target.sha') ?: 'n/a'));
        $this->line('Readiness: ' . (string) data_get($result, 'readiness.status', 'fail'));
        $this->line('Code: ' . (string) data_get($result, 'readiness.code', 'ci_unknown'));
        $this->line('Message: ' . (string) data_get($result, 'readiness.message', ''));

        if (is_array(data_get($result, 'run'))) {
            $this->newLine();
            $this->line('Run ID: ' . (string) data_get($result, 'run.id', 'n/a'));
            $this->line('Run Status: ' . (string) data_get($result, 'run.status', 'n/a'));
            $this->line('Conclusion: ' . (string) (data_get($result, 'run.conclusion') ?: 'n/a'));
            $this->line('Run SHA: ' . (string) (data_get($result, 'run.head_sha') ?: 'n/a'));

            if (! empty(data_get($result, 'run.html_url'))) {
                $this->line('Run URL: ' . (string) data_get($result, 'run.html_url'));
            }
        }

        foreach ((array) data_get($result, 'readiness.warnings', []) as $warning) {
            $this->warn((string) $warning);
        }

        foreach ((array) data_get($result, 'readiness.failures', []) as $failure) {
            $this->error((string) $failure);
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
        return (bool) data_get($result, 'readiness.exit_would_fail', true)
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
            'target' => [
                'ref' => $this->option('ref') ?: null,
                'branch' => $this->option('branch') ?: null,
                'sha' => $this->option('sha') ?: null,
            ],
            'match' => [
                'run_found' => false,
                'exact_sha_verified' => false,
                'sha_mismatch' => false,
                'matched_by' => 'none',
            ],
            'run' => null,
            'readiness' => [
                'status' => 'fail',
                'raw_status' => 'fail',
                'high_level_status' => 'unavailable',
                'code' => 'ci_status_exception',
                'message' => $exception->getMessage(),
                'warnings' => [],
                'failures' => [$exception->getMessage()],
                'strict' => (bool) $this->option('strict'),
                'require_success' => (bool) $this->option('require-success'),
                'allow_in_progress' => (bool) $this->option('allow-in-progress'),
                'exit_would_fail' => true,
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'content_ci_status',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }
}
