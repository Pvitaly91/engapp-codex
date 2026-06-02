<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ContentOperationReplayService;
use Illuminate\Console\Command;
use Throwable;

class ContentRetryRunCommand extends Command
{
    protected $signature = 'content:retry-run
        {run : Content operation run id to replay}
        {--dry-run : Replay in dry-run mode}
        {--force : Replay live}
        {--json : Output the replay result as JSON}
        {--write-report : Write a replay report into storage/app/content-operation-replays}
        {--strict : Treat stale-context warnings as blockers}
        {--allow-success : Allow replaying an original run with status=success}
        {--reuse-original-mode : Reuse the original live/dry-run mode; live replay still requires --force}
        {--takeover-stale-lock : Explicitly take over a stale global content-operation lock}';

    protected $description = 'Replay a recorded execution-grade content operation from canonical history context.';

    public function __construct(
        private readonly ContentOperationReplayService $contentOperationReplayService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $result = $this->contentOperationReplayService->run((int) $this->argument('run'), [
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'write_report' => (bool) $this->option('write-report'),
                'strict' => (bool) $this->option('strict'),
                'allow_success' => (bool) $this->option('allow-success'),
                'reuse_original_mode' => (bool) $this->option('reuse-original-mode'),
                'takeover_stale_lock' => (bool) $this->option('takeover-stale-lock'),
                'trigger_source' => 'cli',
                'operator_user_id' => null,
            ]);
        } catch (Throwable $exception) {
            $result = [
                'original_run' => null,
                'replay' => [
                    'mode' => [
                        'dry_run' => true,
                        'force' => (bool) $this->option('force'),
                        'allow_success' => (bool) $this->option('allow-success'),
                        'reuse_original_mode' => (bool) $this->option('reuse-original-mode'),
                        'takeover_stale_lock' => (bool) $this->option('takeover-stale-lock'),
                    ],
                    'validation' => [
                        'blocked' => true,
                        'warnings' => [],
                        'reasons' => [],
                    ],
                    'resolved_context' => [],
                    'result' => null,
                    'new_run' => null,
                    'status' => 'failed',
                ],
                'artifacts' => [
                    'report_path' => null,
                ],
                'error' => [
                    'stage' => 'content_retry_run',
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ],
            ];
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return empty($result['error']) ? self::SUCCESS : self::FAILURE;
        }

        $this->renderHumanOutput($result);

        return empty($result['error']) ? self::SUCCESS : self::FAILURE;
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

        $this->line('Command: content:retry-run');
        $this->line('Original Run: #' . (string) data_get($result, 'original_run.id', '—'));
        $this->line('Original Kind: ' . (string) data_get($result, 'original_run.operation_kind', '—'));
        $this->line('Original Status: ' . (string) data_get($result, 'original_run.status', '—'));
        $this->line('Replay Mode: ' . ((bool) data_get($result, 'replay.mode.dry_run', true) ? 'dry-run' : 'live'));
        $this->line('Force: ' . ((bool) data_get($result, 'replay.mode.force', false) ? 'true' : 'false'));
        $this->line('Allow Success: ' . ((bool) data_get($result, 'replay.mode.allow_success', false) ? 'true' : 'false'));
        $this->line('Takeover Stale Lock: ' . ((bool) data_get($result, 'replay.mode.takeover_stale_lock', false) ? 'true' : 'false'));
        $this->newLine();

        $this->line('Validation');
        $this->line('- blocked=' . ((bool) data_get($result, 'replay.validation.blocked', false) ? 'true' : 'false'));

        foreach ((array) data_get($result, 'replay.validation.reasons', []) as $reason) {
            $this->line('[BLOCK] ' . (string) ($reason['code'] ?? 'blocked') . ': ' . (string) ($reason['message'] ?? ''));
        }

        foreach ((array) data_get($result, 'replay.validation.warnings', []) as $warning) {
            $this->line('[WARN] ' . (string) ($warning['code'] ?? 'warning') . ': ' . (string) ($warning['message'] ?? ''));
        }

        $this->newLine();
        $this->line('Resolved Context');
        $this->line('- domains=' . implode(',', (array) data_get($result, 'replay.resolved_context.domains', [])));
        $this->line('- base=' . (string) (data_get($result, 'replay.resolved_context.base_ref') ?? '—'));
        $this->line('- base_refs_by_domain=' . json_encode((array) data_get($result, 'replay.resolved_context.base_refs_by_domain', []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->line('- head=' . (string) (data_get($result, 'replay.resolved_context.head_ref') ?? '—'));
        $this->line('- bootstrap_uninitialized=' . ((bool) data_get($result, 'replay.resolved_context.bootstrap_uninitialized', false) ? 'true' : 'false'));
        $this->newLine();

        if (is_array(data_get($result, 'replay.result'))) {
            $serviceResult = (array) data_get($result, 'replay.result');
            $this->line('Canonical Replay Result');
            $this->line(sprintf(
                '- changed=%d; deleted-cleanup=%d; seed=%d; refresh=%d',
                (int) data_get($serviceResult, 'plan.summary.changed_packages', 0),
                (int) data_get($serviceResult, 'plan.summary.deleted_cleanup_candidates', 0),
                (int) data_get($serviceResult, 'plan.summary.seed_candidates', 0),
                (int) data_get($serviceResult, 'plan.summary.refresh_candidates', 0)
            ));

            if (is_array(data_get($serviceResult, 'error'))) {
                $this->line('- error=' . (string) data_get($serviceResult, 'error.message', 'Unknown error.'));
            }

            $this->newLine();
        }

        $this->line('Replay Status: ' . (string) data_get($result, 'replay.status', 'unknown'));

        if (! empty(data_get($result, 'replay.new_run.id'))) {
            $this->line('New Run ID: ' . (string) data_get($result, 'replay.new_run.id'));
            $this->line('New Run Payload: ' . (string) (data_get($result, 'replay.new_run.payload_json_path') ?? '—'));
            $this->line('New Run Report: ' . (string) (data_get($result, 'replay.new_run.report_path') ?? '—'));
        }

        if (! empty(data_get($result, 'replay.new_run.warning'))) {
            $this->warn((string) data_get($result, 'replay.new_run.warning'));
        }

        if (is_array(data_get($result, 'lock'))) {
            $this->newLine();
            $this->line('Lock: status=' . (string) data_get($result, 'lock.status', 'unknown')
                . '; acquired=' . ((bool) data_get($result, 'lock.acquired', false) ? 'true' : 'false')
                . '; takeover=' . ((bool) data_get($result, 'lock.takeover.performed', false) ? 'performed' : 'not-performed'));
        }

        if (! empty(data_get($result, 'artifacts.report_path'))) {
            $this->newLine();
            $this->line('Replay Report: ' . (string) data_get($result, 'artifacts.report_path'));
        }
    }
}
