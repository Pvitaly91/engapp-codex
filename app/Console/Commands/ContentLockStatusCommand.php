<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ContentOperationLockService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ContentLockStatusCommand extends Command
{
    protected $signature = 'content:lock-status
        {--json : Output machine-readable lock status}
        {--write-report : Write a compact report into storage/app/content-lock-status}';

    protected $description = 'Show the global execution-grade content operation lock status.';

    public function __construct(
        private readonly ContentOperationLockService $contentOperationLockService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $result = [
            'lock' => $this->contentOperationLockService->status(),
            'artifacts' => [
                'report_path' => null,
            ],
        ];

        if ($this->option('write-report')) {
            $relativePath = 'content-lock-status/' . now()->format('Y/m/d/His') . '-lock-status.md';
            Storage::disk('local')->put($relativePath, $this->renderReport($result));
            $result['artifacts']['report_path'] = storage_path('app/' . $relativePath);
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->renderHuman($result);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderHuman(array $result): void
    {
        $status = (string) data_get($result, 'lock.status', 'free');
        $lock = (array) data_get($result, 'lock.lock', []);

        $this->line('Command: content:lock-status');
        $this->line('Status: ' . $status);

        if ($status === 'free' || $status === 'disabled') {
            $this->line('No active content operation lock.');
        } else {
            $this->line('Operation Kind: ' . (string) ($lock['operation_kind'] ?? 'unknown'));
            $this->line('Trigger Source: ' . (string) ($lock['trigger_source'] ?? 'unknown'));
            $this->line('Domains: ' . implode(', ', (array) ($lock['domains'] ?? [])));
            $this->line('Run ID: ' . (string) (($lock['content_operation_run_id'] ?? null) ?: '—'));
            $this->line('Operator User ID: ' . (string) (($lock['operator_user_id'] ?? null) ?: '—'));
            $this->line('Acquired At: ' . (string) (($lock['acquired_at'] ?? null) ?: '—'));
            $this->line('Heartbeat At: ' . (string) (($lock['heartbeat_at'] ?? null) ?: '—'));
            $this->line('Expires At: ' . (string) (($lock['expires_at'] ?? null) ?: '—'));
            $this->line('Age Seconds: ' . (string) (($lock['age_seconds'] ?? null) ?: '0'));
            $this->line('Stale Takeover Available: ' . (data_get($result, 'lock.takeover.available') ? 'true' : 'false'));
        }

        if (! empty($result['artifacts']['report_path'])) {
            $this->newLine();
            $this->line('Report: ' . (string) $result['artifacts']['report_path']);
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderReport(array $result): string
    {
        $lock = (array) data_get($result, 'lock.lock', []);

        return implode(PHP_EOL, [
            '# Content Operation Lock Status',
            '',
            '- Status: `' . (string) data_get($result, 'lock.status', 'free') . '`',
            '- Operation Kind: `' . (string) ($lock['operation_kind'] ?? '') . '`',
            '- Trigger Source: `' . (string) ($lock['trigger_source'] ?? '') . '`',
            '- Domains: `' . implode(',', (array) ($lock['domains'] ?? [])) . '`',
            '- Run ID: `' . (string) (($lock['content_operation_run_id'] ?? null) ?: '') . '`',
            '- Acquired At: `' . (string) (($lock['acquired_at'] ?? null) ?: '') . '`',
            '- Heartbeat At: `' . (string) (($lock['heartbeat_at'] ?? null) ?: '') . '`',
            '- Expires At: `' . (string) (($lock['expires_at'] ?? null) ?: '') . '`',
            '- Stale Takeover Available: `' . (data_get($result, 'lock.takeover.available') ? 'true' : 'false') . '`',
            '',
        ]);
    }
}
