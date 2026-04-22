<?php

namespace App\Console\Commands;

use App\Services\V3PromptGenerator\V3PackageUnseedService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class V3UnseedPackageCommand extends Command
{
    protected $signature = 'v3:unseed-package
        {target : Package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--dry-run : Execute the delete flow inside a rollback-only transaction}
        {--force : Allow live package unseed}
        {--json : Output the unseed result as JSON}
        {--write-report : Write a Markdown report into storage/app/package-unseed-reports/v3}
        {--strict : Treat warnings as failures}';

    protected $description = 'Unseed a single resolved V3 JSON package from the database without touching package files.';

    public function __construct(
        private readonly V3PackageUnseedService $packageUnseedService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));
        $mode = $this->modePayload();

        try {
            $result = $this->packageUnseedService->run($target, [
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'strict' => (bool) $this->option('strict'),
            ]);
        } catch (Throwable $exception) {
            $result = $this->errorResult($target, $mode, $exception);
        }

        if ($this->option('write-report')) {
            $path = $this->packageUnseedService->writeReport($result);
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
        $this->line(sprintf(
            'Ownership: seed_run_present=%s; package_present_in_db=%s',
            $this->boolString((bool) ($result['ownership']['seed_run_present'] ?? false)),
            $this->boolString((bool) ($result['ownership']['package_present_in_db'] ?? false))
        ));
        $this->newLine();

        if ((array) ($result['definition_summary'] ?? []) !== []) {
            $this->line('Definition Summary:');

            foreach ((array) $result['definition_summary'] as $key => $value) {
                $this->line('- ' . $this->label((string) $key) . ': ' . $this->stringify($value));
            }

            $this->newLine();
        }

        $this->line('Impact Counts:');

        if ((array) ($result['impact']['counts'] ?? []) === []) {
            $this->line('- none');
        } else {
            foreach ((array) ($result['impact']['counts'] ?? []) as $model => $count) {
                $this->line('- ' . $model . ': ' . (int) $count);
            }
        }

        $this->newLine();
        $this->line('Warnings:');

        if ((array) ($result['impact']['warnings'] ?? []) === []) {
            $this->line('- none');
        } else {
            foreach ((array) ($result['impact']['warnings'] ?? []) as $warning) {
                $this->line('- ' . (string) $warning);
            }
        }

        $this->newLine();
        $this->line(sprintf(
            'Result: deleted=%s; rolled_back=%s; seed_run_removed=%s',
            $this->boolString((bool) ($result['result']['deleted'] ?? false)),
            $this->boolString((bool) ($result['result']['rolled_back'] ?? false)),
            $this->boolString((bool) ($result['result']['seed_run_removed'] ?? false))
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
            'definition_summary' => [],
            'impact' => [
                'counts' => [],
                'warnings' => [],
            ],
            'result' => [
                'deleted' => false,
                'rolled_back' => false,
                'seed_run_removed' => false,
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
