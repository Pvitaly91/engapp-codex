<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ContentOperationRunService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ContentHistoryCommand extends Command
{
    protected $signature = 'content:history
        {runId? : Optional content operation run id for detail output}
        {--limit=20 : Number of recent runs to list}
        {--kind= : apply_changed | apply_sync | deployment_apply_changed | deployment_sync_repair}
        {--status= : success | partial | failed | blocked | dry_run}
        {--domains= : v3,page-v3 | v3 | page-v3}
        {--json : Output the history result as JSON}
        {--write-report : Write a Markdown report into storage/app/content-operation-history}';

    protected $description = 'Show recent content operation runs or inspect one run in detail.';

    public function __construct(
        private readonly ContentOperationRunService $contentOperationRunService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $result = $this->buildResult();
        } catch (Throwable $exception) {
            $result = [
                'summary' => [
                    'total' => 0,
                    'by_status' => [],
                    'by_kind' => [],
                ],
                'runs' => [],
                'run' => null,
                'artifacts' => [
                    'report_path' => null,
                ],
                'error' => [
                    'stage' => 'content_history',
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ],
            ];
        }

        if ($this->option('write-report')) {
            $path = $this->writeReport($result);
            $result['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return empty($result['error']) ? self::SUCCESS : self::FAILURE;
        }

        $this->renderHumanOutput($result);

        return empty($result['error']) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildResult(): array
    {
        $runId = $this->argument('runId');

        if ($runId !== null && trim((string) $runId) !== '') {
            $run = $this->contentOperationRunService->findWithArtifacts((int) $runId);

            if ($run === null) {
                throw new RuntimeException('Content operation run not found.');
            }

            return [
                'summary' => [
                    'total' => 1,
                    'by_status' => [
                        (string) $run->status => 1,
                    ],
                    'by_kind' => [
                        (string) $run->operation_kind => 1,
                    ],
                ],
                'runs' => [],
                'run' => $this->mapRun($run, true),
                'artifacts' => [
                    'report_path' => null,
                ],
                'error' => null,
            ];
        }

        $runs = $this->contentOperationRunService->latest([
            'kind' => $this->option('kind'),
            'status' => $this->option('status'),
            'domains' => $this->option('domains'),
        ], (int) ($this->option('limit') ?? 20));

        return [
            'summary' => [
                'total' => $runs->count(),
                'by_status' => $runs->countBy('status')->sortKeys()->all(),
                'by_kind' => $runs->countBy('operation_kind')->sortKeys()->all(),
            ],
            'runs' => $runs->map(fn ($run): array => $this->mapRun($run, false))->values()->all(),
            'run' => null,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @param  object  $run
     * @return array<string, mixed>
     */
    private function mapRun(object $run, bool $withPayload): array
    {
        $mapped = [
            'id' => $run->id,
            'operation_kind' => $run->operation_kind,
            'trigger_source' => $run->trigger_source,
            'replayed_from_run_id' => $run->replayed_from_run_id,
            'domains' => is_array($run->domains) ? $run->domains : [],
            'base_ref' => $run->base_ref,
            'head_ref' => $run->head_ref,
            'base_refs_by_domain' => is_array($run->base_refs_by_domain) ? $run->base_refs_by_domain : [],
            'dry_run' => (bool) $run->dry_run,
            'strict' => (bool) $run->strict,
            'with_release_check' => $run->with_release_check,
            'skip_release_check' => $run->skip_release_check,
            'bootstrap_uninitialized' => (bool) $run->bootstrap_uninitialized,
            'status' => $run->status,
            'started_at' => $run->started_at?->toIso8601String(),
            'finished_at' => $run->finished_at?->toIso8601String(),
            'operator_user_id' => $run->operator_user_id,
            'summary' => is_array($run->summary) ? $run->summary : [],
            'payload_json_path' => $run->payload_json_path ? storage_path('app/' . $run->payload_json_path) : null,
            'report_path' => $run->report_path ? storage_path('app/' . $run->report_path) : null,
            'error_excerpt' => $run->error_excerpt,
            'meta' => is_array($run->meta) ? $run->meta : [],
            'replay_ids' => method_exists($run, 'relationLoaded') && $run->relationLoaded('replays')
                ? $run->replays->pluck('id')->values()->all()
                : [],
        ];

        if ($withPayload) {
            $mapped['payload'] = is_array($run->payload_json ?? null) ? $run->payload_json : null;
        }

        return $mapped;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderHumanOutput(array $result): void
    {
        if (! empty($result['error']['message'])) {
            $this->error((string) $result['error']['message']);

            return;
        }

        $detail = is_array($result['run'] ?? null) ? (array) $result['run'] : null;

        if ($detail !== null) {
            $this->line('Command: content:history');
            $this->line('Run ID: ' . (string) ($detail['id'] ?? '—'));
            $this->line('Kind: ' . (string) ($detail['operation_kind'] ?? '—'));
            $this->line('Trigger: ' . (string) ($detail['trigger_source'] ?? '—'));
            $this->line('Replayed From Run: ' . (string) (($detail['replayed_from_run_id'] ?? null) ?: '—'));
            $this->line('Domains: ' . implode(', ', (array) ($detail['domains'] ?? [])));
            $this->line('Refs: base=' . (string) (($detail['base_ref'] ?? null) ?: '—') . ' | head=' . (string) (($detail['head_ref'] ?? null) ?: '—'));
            $this->line('Execution: ' . ((bool) ($detail['dry_run'] ?? false) ? 'dry-run' : 'live'));
            $this->line('Status: ' . (string) ($detail['status'] ?? 'unknown'));
            $this->line('Started: ' . (string) (($detail['started_at'] ?? null) ?: '—'));
            $this->line('Finished: ' . (string) (($detail['finished_at'] ?? null) ?: '—'));
            $this->newLine();
            $this->line('Compact Summary:');

            foreach ((array) ($detail['summary'] ?? []) as $key => $value) {
                $rendered = is_array($value)
                    ? json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    : (string) $value;
                $this->line('- ' . $key . ': ' . $rendered);
            }

            $this->newLine();
            $this->line('Payload Artifact: ' . (string) (($detail['payload_json_path'] ?? null) ?: '—'));
            $this->line('Report Path: ' . (string) (($detail['report_path'] ?? null) ?: '—'));

            if (! empty($detail['error_excerpt'])) {
                $this->newLine();
                $this->warn('Error Excerpt: ' . (string) $detail['error_excerpt']);
            }
        } else {
            $this->line('Command: content:history');
            $this->line(sprintf(
                'Summary: total=%d; by-status=%s; by-kind=%s',
                (int) ($result['summary']['total'] ?? 0),
                json_encode((array) ($result['summary']['by_status'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                json_encode((array) ($result['summary']['by_kind'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ));
            $this->newLine();

            /** @var list<array<string, mixed>> $runs */
            $runs = (array) ($result['runs'] ?? []);

            if ($runs === []) {
                $this->line('No content operation runs found for the selected filters.');
            } else {
                foreach ($runs as $run) {
                    $this->line(sprintf(
                        '#%d | %s | %s%s | domains=%s | base=%s | head=%s | %s | %s -> %s',
                        (int) ($run['id'] ?? 0),
                        (string) ($run['operation_kind'] ?? 'unknown'),
                        (string) ($run['trigger_source'] ?? 'unknown'),
                        (($run['replayed_from_run_id'] ?? null) ? ' replay-of=' . (string) $run['replayed_from_run_id'] : ''),
                        implode(',', (array) ($run['domains'] ?? [])),
                        (string) (($run['base_ref'] ?? null) ?: '—'),
                        (string) (($run['head_ref'] ?? null) ?: '—'),
                        (bool) ($run['dry_run'] ?? false) ? 'dry-run' : 'live',
                        (string) (($run['started_at'] ?? null) ?: '—'),
                        (string) (($run['status'] ?? null) ?: 'unknown')
                    ));
                }
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
    private function writeReport(array $result): string
    {
        $detail = is_array($result['run'] ?? null);
        $stamp = now()->format('Ymd-His');
        $suffix = $detail
            ? 'run-' . (string) ($result['run']['id'] ?? 'unknown')
            : 'recent-' . Str::slug((string) ($this->option('kind') ?? 'all'));
        $relativePath = 'content-operation-history/' . $stamp . '-' . $suffix . '.md';

        $lines = $detail
            ? $this->detailReportLines((array) $result['run'])
            : $this->listReportLines($result);

        Storage::disk('local')->put($relativePath, implode(PHP_EOL, $lines) . PHP_EOL);

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return list<string>
     */
    private function listReportLines(array $result): array
    {
        $lines = [
            '# Content Operation History',
            '',
            '- Total: ' . (int) ($result['summary']['total'] ?? 0),
            '- By Status: `' . json_encode((array) ($result['summary']['by_status'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`',
            '- By Kind: `' . json_encode((array) ($result['summary']['by_kind'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`',
            '',
            '## Runs',
            '',
        ];

        foreach ((array) ($result['runs'] ?? []) as $run) {
            $lines[] = '- #'
                . (int) ($run['id'] ?? 0)
                . ' kind=`' . (string) ($run['operation_kind'] ?? 'unknown') . '`'
                . ' trigger=`' . (string) ($run['trigger_source'] ?? 'unknown') . '`'
                . ' domains=`' . implode(',', (array) ($run['domains'] ?? [])) . '`'
                . ' base=`' . (string) (($run['base_ref'] ?? null) ?: '') . '`'
                . ' head=`' . (string) (($run['head_ref'] ?? null) ?: '') . '`'
                . ' status=`' . (string) ($run['status'] ?? 'unknown') . '`';
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $run
     * @return list<string>
     */
    private function detailReportLines(array $run): array
    {
        return [
            '# Content Operation Run',
            '',
            '- Run ID: ' . (int) ($run['id'] ?? 0),
            '- Kind: `' . (string) ($run['operation_kind'] ?? 'unknown') . '`',
            '- Trigger: `' . (string) ($run['trigger_source'] ?? 'unknown') . '`',
            '- Domains: `' . implode(',', (array) ($run['domains'] ?? [])) . '`',
            '- Base Ref: `' . (string) (($run['base_ref'] ?? null) ?: '') . '`',
            '- Head Ref: `' . (string) (($run['head_ref'] ?? null) ?: '') . '`',
            '- Dry Run: `' . ((bool) ($run['dry_run'] ?? false) ? 'true' : 'false') . '`',
            '- Status: `' . (string) ($run['status'] ?? 'unknown') . '`',
            '- Started At: `' . (string) (($run['started_at'] ?? null) ?: '') . '`',
            '- Finished At: `' . (string) (($run['finished_at'] ?? null) ?: '') . '`',
            '- Payload Artifact: `' . (string) (($run['payload_json_path'] ?? null) ?: '') . '`',
            '- Report Path: `' . (string) (($run['report_path'] ?? null) ?: '') . '`',
            '',
            '## Summary',
            '',
            '- JSON: `' . json_encode((array) ($run['summary'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`',
            '',
            '## Error Excerpt',
            '',
            '- ' . (string) (($run['error_excerpt'] ?? null) ?: '—'),
        ];
    }
}
