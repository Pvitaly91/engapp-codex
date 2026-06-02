<?php

namespace App\Console\Commands;

use App\Services\V3PromptGenerator\V3PackageSeedService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class V3SeedPackageCommand extends Command
{
    protected $signature = 'v3:seed-package
        {target : Package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--dry-run : Execute runtime seeding inside a rollback-only transaction}
        {--json : Output the seed result as JSON}
        {--skip-release-check : Skip the preflight release-check entirely}
        {--check-profile=release : scaffold | release}
        {--strict : Treat preflight warnings as failures}';

    protected $description = 'Seed a single resolved V3 JSON package with optional preflight gating and dry-run rollback.';

    public function __construct(
        private readonly V3PackageSeedService $packageSeedService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));
        $mode = $this->modePayload();

        try {
            $result = $this->packageSeedService->run($target, [
                'dry_run' => (bool) $this->option('dry-run'),
                'skip_release_check' => (bool) $this->option('skip-release-check'),
                'check_profile' => (string) $this->option('check-profile'),
                'strict' => (bool) $this->option('strict'),
            ]);
        } catch (Throwable $exception) {
            $result = $this->errorResult($target, $mode, $exception);
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

        $this->line('Target: '.((string) ($result['target']['definition_relative_path'] ?? $result['target']['input'] ?? 'unknown')));
        $this->line('Package Root: '.((string) ($result['target']['package_root_relative_path'] ?? 'unknown')));
        $this->line('Seeder Class: '.((string) ($result['target']['resolved_seeder_class'] ?? 'unknown')));
        $this->line('Mode: '.((bool) ($result['mode']['dry_run'] ?? false) ? 'dry-run' : 'live'));
        $this->line('Check Profile: '.((string) ($result['mode']['check_profile'] ?? 'release')));
        $this->line(
            'Release Check: '
            .((bool) ($result['mode']['release_check_skipped'] ?? false) ? 'skipped' : 'enabled')
        );
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
                    $meta !== '' ? ' — '.$meta : ''
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

        if ((array) ($result['definition_summary'] ?? []) !== []) {
            $this->line('Definition Summary:');

            foreach ((array) $result['definition_summary'] as $key => $value) {
                $this->line('- '.$this->label((string) $key).': '.$this->stringify($value));
            }

            $this->newLine();
        }

        $this->line(sprintf(
            'Result: seeded=%s; rolled_back=%s; seed_run_logged=%s',
            $this->boolString((bool) ($result['result']['seeded'] ?? false)),
            $this->boolString((bool) ($result['result']['rolled_back'] ?? false)),
            $this->boolString((bool) ($result['result']['seed_run_logged'] ?? false))
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function modePayload(): array
    {
        return [
            'dry_run' => (bool) $this->option('dry-run'),
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
            'preflight' => [
                'executed' => false,
                'summary' => $this->defaultCheckSummary(),
                'checks' => [],
            ],
            'definition_summary' => [],
            'result' => [
                'seeded' => false,
                'rolled_back' => false,
                'seed_run_logged' => false,
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
