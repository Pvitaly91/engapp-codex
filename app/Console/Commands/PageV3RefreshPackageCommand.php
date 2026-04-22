<?php

namespace App\Console\Commands;

use App\Services\PageV3PromptGenerator\PageV3PackageRefreshService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class PageV3RefreshPackageCommand extends Command
{
    protected $signature = 'page-v3:refresh-package
        {target : Package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--dry-run : Execute the refresh flow inside a rollback-only transaction}
        {--force : Allow live package refresh}
        {--json : Output the refresh result as JSON}
        {--write-report : Write a Markdown report into storage/app/package-refresh-reports/page-v3}
        {--skip-release-check : Skip the preflight release-check entirely}
        {--check-profile=release : scaffold | release}
        {--strict : Treat preflight or impact warnings as failures}';

    protected $description = 'Refresh a single resolved Page_V3 JSON package with atomic live refresh, rollback-only dry run, and release-check gating.';

    public function __construct(
        private readonly PageV3PackageRefreshService $packageRefreshService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));
        $mode = $this->modePayload();

        try {
            $result = $this->packageRefreshService->run($target, [
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'skip_release_check' => (bool) $this->option('skip-release-check'),
                'check_profile' => (string) $this->option('check-profile'),
                'strict' => (bool) $this->option('strict'),
            ]);
        } catch (Throwable $exception) {
            $result = $this->errorResult($target, $mode, $exception);
        }

        if ($this->option('write-report')) {
            $path = $this->packageRefreshService->writeReport($result);
            $result['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $this->exitCodeForResult($result);
        }

        $this->renderHumanOutput($result);

        return $this->exitCodeForResult($result);
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

        $this->line('Target: ' . ((string) ($result['target']['definition_relative_path'] ?? $result['target']['input'] ?? 'unknown')));
        $this->line('Package Root: ' . ((string) ($result['target']['package_root_relative_path'] ?? 'unknown')));
        $this->line('Seeder Class: ' . ((string) ($result['target']['resolved_seeder_class'] ?? 'unknown')));
        $this->line('Mode: ' . ((bool) ($result['mode']['dry_run'] ?? false) ? 'dry-run' : 'live'));
        $this->line('Force: ' . $this->boolString((bool) ($result['mode']['force'] ?? false)));
        $this->line('Check Profile: ' . ((string) ($result['mode']['check_profile'] ?? 'release')));
        $this->line(
            'Release Check: '
            . ((bool) ($result['mode']['release_check_skipped'] ?? false) ? 'skipped' : 'enabled')
        );
        $this->line('Strategy: ' . ((string) ($result['phases']['refresh_strategy'] ?? 'unknown')));
        $this->line(sprintf(
            'Ownership: seed_run_present=%s; package_present_in_db=%s',
            $this->boolString((bool) ($result['ownership']['seed_run_present'] ?? false)),
            $this->boolString((bool) ($result['ownership']['package_present_in_db'] ?? false))
        ));
        $this->newLine();

        if ((bool) ($result['preflight']['executed'] ?? false)) {
            foreach ((array) ($result['preflight']['checks'] ?? []) as $check) {
                $status = strtoupper((string) ($check['status'] ?? 'FAIL'));
                $label = (string) ($check['label'] ?? 'Unnamed check');
                $meta = $this->formatMeta((array) ($check['meta'] ?? []));

                $this->line(sprintf(
                    '[%s] %s%s',
                    $status,
                    $label,
                    $meta !== '' ? ' — ' . $meta : ''
                ));
            }

            $this->newLine();
            $this->line(sprintf(
                'Preflight Summary: %d PASS / %d WARN / %d FAIL',
                (int) ($result['preflight']['summary']['check_counts']['pass'] ?? 0),
                (int) ($result['preflight']['summary']['check_counts']['warn'] ?? 0),
                (int) ($result['preflight']['summary']['check_counts']['fail'] ?? 0)
            ));
            $this->newLine();
        }

        $this->line('Warnings:');

        if ((array) ($result['warnings'] ?? []) === []) {
            $this->line('- none');
        } else {
            foreach ((array) ($result['warnings'] ?? []) as $warning) {
                $this->line('- ' . (string) $warning);
            }
        }

        $this->newLine();

        if ((array) ($result['definition_summary'] ?? []) !== []) {
            $this->line('Definition Summary:');

            foreach ((array) $result['definition_summary'] as $key => $value) {
                $this->line('- ' . $this->label((string) $key) . ': ' . $this->stringify($value));
            }

            $this->newLine();
        }

        $this->line(sprintf(
            'Result: refreshed=%s; seeded_after=%s; rolled_back=%s; seed_run_logged=%s',
            $this->boolString((bool) ($result['result']['refreshed'] ?? false)),
            $this->boolString((bool) ($result['result']['seeded_after'] ?? false)),
            $this->boolString((bool) ($result['phases']['rolled_back'] ?? false)),
            $this->boolString((bool) ($result['result']['seed_run_logged'] ?? false))
        ));

        if (! empty($result['artifacts']['report_path'])) {
            $this->line('Report: ' . (string) $result['artifacts']['report_path']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function modePayload(): array
    {
        return [
            'dry_run' => (bool) $this->option('dry-run'),
            'force' => (bool) $this->option('force'),
            'check_profile' => trim((string) ($this->option('check-profile') ?? 'release')) ?: 'release',
            'release_check_skipped' => (bool) $this->option('skip-release-check'),
        ];
    }

    /**
     * @param  array<string, mixed>  $mode
     * @return array<string, mixed>
     */
    private function errorResult(string $target, array $mode, Throwable $exception): array
    {
        return [
            'target' => [
                'input' => $target,
            ],
            'mode' => $mode,
            'ownership' => [
                'seed_run_present' => false,
                'package_present_in_db' => false,
            ],
            'warnings' => [],
            'preflight' => [
                'executed' => false,
                'summary' => $this->defaultCheckSummary(),
                'checks' => [],
            ],
            'phases' => [
                'refresh_strategy' => null,
                'unseed_executed' => false,
                'seed_executed' => false,
                'rolled_back' => false,
            ],
            'definition_summary' => [],
            'result' => [
                'refreshed' => false,
                'seeded_after' => false,
                'seed_run_logged' => false,
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'target_resolution',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function exitCodeForResult(array $result): int
    {
        return empty($result['error']) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function formatMeta(array $meta): string
    {
        $parts = [];

        foreach ($meta as $key => $value) {
            if ($value === null || $value === [] || $value === '') {
                continue;
            }

            $parts[] = sprintf(
                '%s=%s',
                $key,
                $this->stringify($value)
            );
        }

        return implode('; ', $parts);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultCheckSummary(): array
    {
        return [
            'fully_valid' => null,
            'check_counts' => [
                'pass' => 0,
                'warn' => 0,
                'fail' => 0,
            ],
        ];
    }

    private function stringify(mixed $value): string
    {
        if (is_bool($value)) {
            return $this->boolString($value);
        }

        if (is_array($value)) {
            $allScalar = collect($value)->every(static fn ($item): bool => is_scalar($item) || $item === null);

            if ($allScalar) {
                $flat = array_values(array_filter(array_map(
                    static fn ($item): string => trim((string) $item),
                    $value
                ), static fn (string $item): bool => $item !== ''));

                return $flat === []
                    ? '[]'
                    : implode(', ', $flat);
            }

            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]';
        }

        if ($value === null) {
            return 'null';
        }

        return (string) $value;
    }

    private function label(string $key): string
    {
        return Str::of($key)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }

    private function boolString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
