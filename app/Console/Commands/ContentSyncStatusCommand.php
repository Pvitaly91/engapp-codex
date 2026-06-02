<?php

namespace App\Console\Commands;

use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use App\Services\ContentDeployment\ContentSyncStateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ContentSyncStatusCommand extends Command
{
    protected $signature = 'content:sync-status
        {--domains= : v3,page-v3 | v3 | page-v3}
        {--json : Output the content sync state as JSON}
        {--write-report : Write a Markdown report into storage/app/content-sync-status}';

    protected $description = 'Show canonical per-domain content sync refs and last attempt metadata for V3 and Page_V3.';

    public function __construct(
        private readonly ContentSyncStateService $contentSyncStateService,
        private readonly DeploymentGitRefProbe $deploymentGitRefProbe,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $result = $this->buildResult();
        } catch (Throwable $exception) {
            $result = [
                'domains' => [],
                'summary' => [
                    'domains' => 0,
                    'synced' => 0,
                    'drifted' => 0,
                    'uninitialized' => 0,
                    'failed_last_apply' => 0,
                ],
                'artifacts' => [
                    'report_path' => null,
                ],
                'error' => [
                    'stage' => 'sync_status',
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
        $domains = $this->normalizeDomainsOption($this->option('domains'));
        $currentCodeRef = $this->deploymentGitRefProbe->currentHeadCommit();
        $fallbackRefs = [];

        foreach ($domains as $domain) {
            $fallbackRefs[$domain] = $currentCodeRef;
        }

        $descriptions = $this->contentSyncStateService->describe($domains, $fallbackRefs);
        $summary = [
            'domains' => count($descriptions),
            'synced' => 0,
            'drifted' => 0,
            'uninitialized' => 0,
            'failed_last_apply' => 0,
        ];

        foreach ($descriptions as $description) {
            $status = (string) ($description['status'] ?? 'uninitialized');

            if (array_key_exists($status, $summary)) {
                $summary[$status]++;
            }
        }

        return [
            'current_code_ref' => $currentCodeRef,
            'domains' => $descriptions,
            'summary' => $summary,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @return list<string>
     */
    private function normalizeDomainsOption(string|array|null $value): array
    {
        $tokens = is_array($value)
            ? $value
            : explode(',', (string) ($value ?? ''));

        $domains = collect($tokens)
            ->map(static fn ($token): string => strtolower(trim((string) $token)))
            ->map(static fn (string $domain): string => str_replace('_', '-', $domain))
            ->filter()
            ->values()
            ->all();

        if ($domains === []) {
            return $this->contentSyncStateService->supportedDomains();
        }

        $invalid = array_values(array_filter(
            $domains,
            fn (string $domain): bool => ! in_array($domain, $this->contentSyncStateService->supportedDomains(), true)
        ));

        if ($invalid !== []) {
            throw new RuntimeException(sprintf(
                'Unsupported --domains value(s): %s. Supported domains: v3, page-v3.',
                implode(', ', $invalid)
            ));
        }

        return array_values(array_unique($domains));
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

        $this->line('Command: content:sync-status');
        $this->line('Current Code Ref: ' . (string) (($result['current_code_ref'] ?? null) ?: 'unavailable'));
        $this->newLine();
        $this->line(sprintf(
            'Summary: domains=%d; synced=%d; drifted=%d; uninitialized=%d; failed-last-apply=%d',
            (int) ($result['summary']['domains'] ?? 0),
            (int) ($result['summary']['synced'] ?? 0),
            (int) ($result['summary']['drifted'] ?? 0),
            (int) ($result['summary']['uninitialized'] ?? 0),
            (int) ($result['summary']['failed_last_apply'] ?? 0)
        ));
        $this->newLine();

        foreach ((array) ($result['domains'] ?? []) as $domain => $description) {
            $description = (array) $description;
            $this->line(sprintf(
                '[%s] status=%s | sync_ref=%s | code_ref=%s | effective_base=%s',
                $domain,
                (string) ($description['status'] ?? 'uninitialized'),
                (string) (($description['sync_state_ref'] ?? null) ?: '—'),
                (string) (($description['fallback_base_ref'] ?? null) ?: '—'),
                (string) (($description['effective_base_ref'] ?? null) ?: '—')
            ));
            $this->line(sprintf(
                '  last_successful_applied_at=%s | last_attempt_status=%s | last_attempted_at=%s',
                (string) (($description['last_successful_applied_at'] ?? null) ?: '—'),
                (string) (($description['last_attempted_status'] ?? null) ?: '—'),
                (string) (($description['last_attempted_at'] ?? null) ?: '—')
            ));
            $this->line(sprintf(
                '  last_attempt_base=%s | last_attempt_head=%s',
                (string) (($description['last_attempted_base_ref'] ?? null) ?: '—'),
                (string) (($description['last_attempted_head_ref'] ?? null) ?: '—')
            ));
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
        $domains = collect(array_keys((array) ($result['domains'] ?? [])))
            ->map(fn (string $domain): string => Str::slug($domain))
            ->implode('-');
        $hash = substr(sha1(json_encode([
            array_keys((array) ($result['domains'] ?? [])),
            $result['current_code_ref'] ?? null,
        ])), 0, 8);
        $fileName = ($domains !== '' ? $domains : 'content') . '-sync-status-' . $hash . '.md';
        $relativePath = 'content-sync-status/' . $fileName;
        $lines = [
            '# Content Sync Status',
            '',
            '- Current Code Ref: `' . (string) (($result['current_code_ref'] ?? null) ?: '') . '`',
            '',
            '## Summary',
            '',
            '- Domains: ' . (int) ($result['summary']['domains'] ?? 0),
            '- Synced: ' . (int) ($result['summary']['synced'] ?? 0),
            '- Drifted: ' . (int) ($result['summary']['drifted'] ?? 0),
            '- Uninitialized: ' . (int) ($result['summary']['uninitialized'] ?? 0),
            '- Failed Last Apply: ' . (int) ($result['summary']['failed_last_apply'] ?? 0),
            '',
            '## Domains',
            '',
        ];

        foreach ((array) ($result['domains'] ?? []) as $domain => $description) {
            $description = (array) $description;
            $lines[] = '- [' . $domain . ']'
                . ' status=`' . (string) ($description['status'] ?? 'uninitialized') . '`'
                . ' sync_ref=`' . (string) (($description['sync_state_ref'] ?? null) ?: '') . '`'
                . ' code_ref=`' . (string) (($description['fallback_base_ref'] ?? null) ?: '') . '`'
                . ' effective_base=`' . (string) (($description['effective_base_ref'] ?? null) ?: '') . '`'
                . ' last_attempt_status=`' . (string) (($description['last_attempted_status'] ?? null) ?: '') . '`';
        }

        Storage::disk('local')->put($relativePath, implode(PHP_EOL, $lines) . PHP_EOL);

        return $relativePath;
    }
}
