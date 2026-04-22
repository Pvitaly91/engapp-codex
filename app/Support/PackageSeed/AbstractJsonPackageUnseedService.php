<?php

namespace App\Support\PackageSeed;

use App\Support\ReleaseCheck\AbstractJsonPackageReleaseCheckService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class AbstractJsonPackageUnseedService extends AbstractJsonPackageReleaseCheckService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function normalizeUnseedOptions(array $options): array
    {
        return [
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'force' => (bool) ($options['force'] ?? false),
            'strict' => (bool) ($options['strict'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $target
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function resultTemplate(array $target, array $options): array
    {
        return [
            'target' => $target,
            'mode' => [
                'dry_run' => (bool) ($options['dry_run'] ?? false),
                'force' => (bool) ($options['force'] ?? false),
            ],
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
            'error' => null,
        ];
    }

    protected function resolveSeederClass(array $definition, string $fallbackSeederClass): string
    {
        $configured = trim((string) data_get($definition, 'seeder.class', ''));

        return $configured !== '' ? $configured : $fallbackSeederClass;
    }

    protected function seedRunPresent(string $className): bool
    {
        return Schema::hasTable('seed_runs')
            && DB::table('seed_runs')->where('class_name', $className)->exists();
    }

    protected function removeSeedRunRecord(string $className): bool
    {
        if (! Schema::hasTable('seed_runs')) {
            return false;
        }

        return DB::table('seed_runs')
            ->where('class_name', $className)
            ->delete() > 0;
    }

    /**
     * @return array<string, mixed>
     */
    protected function runDeleteOperation(callable $operation, bool $dryRun): array
    {
        if (! $dryRun) {
            /** @var array<string, mixed> */
            return (array) $operation();
        }

        $payload = [];

        try {
            DB::transaction(function () use ($operation, &$payload): void {
                $payload = (array) $operation();

                throw new DryRunRollbackException('Rollback-only package unseed dry run completed.');
            });
        } catch (DryRunRollbackException) {
            return $payload;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $packageRelativePath = (string) ($result['target']['package_root_relative_path'] ?? 'package');
        $packageName = basename(str_replace('\\', '/', $packageRelativePath));
        $modeLabel = (bool) ($result['mode']['dry_run'] ?? false) ? 'dry-run' : 'live';
        $hash = substr(sha1($packageRelativePath), 0, 8);
        $fileName = Str::slug($packageName !== '' ? $packageName : 'package')
            . '-'
            . $modeLabel
            . '-'
            . $hash
            . '.md';
        $relativePath = trim($this->reportDirectory(), '/') . '/' . $fileName;

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function renderMarkdownReport(array $result): string
    {
        $warnings = array_values(array_filter(array_map(
            static fn ($warning): string => trim((string) $warning),
            (array) ($result['impact']['warnings'] ?? [])
        )));
        $counts = (array) ($result['impact']['counts'] ?? []);
        $lines = [
            '# JSON Package Unseed',
            '',
            '- Definition: `' . ($result['target']['definition_relative_path'] ?? '') . '`',
            '- Package Root: `' . ($result['target']['package_root_relative_path'] ?? '') . '`',
            '- Seeder Class: `' . ($result['target']['resolved_seeder_class'] ?? '') . '`',
            '- Mode: `' . ((bool) ($result['mode']['dry_run'] ?? false) ? 'dry-run' : 'live') . '`',
            '- Force: `' . ((bool) ($result['mode']['force'] ?? false) ? 'true' : 'false') . '`',
            '- Seed Run Present: `' . ((bool) ($result['ownership']['seed_run_present'] ?? false) ? 'true' : 'false') . '`',
            '- Package Present In DB: `' . ((bool) ($result['ownership']['package_present_in_db'] ?? false) ? 'true' : 'false') . '`',
            '',
            '## Impact',
            '',
        ];

        if ($counts === []) {
            $lines[] = '- No impacted rows detected.';
        } else {
            foreach ($counts as $model => $count) {
                $lines[] = '- `' . $model . '`: ' . (int) $count;
            }
        }

        $lines[] = '';
        $lines[] = '## Warnings';
        $lines[] = '';

        if ($warnings === []) {
            $lines[] = '- None.';
        } else {
            foreach ($warnings as $warning) {
                $lines[] = '- ' . $warning;
            }
        }

        $lines[] = '';
        $lines[] = '## Result';
        $lines[] = '';
        $lines[] = '- Deleted: `' . ((bool) ($result['result']['deleted'] ?? false) ? 'true' : 'false') . '`';
        $lines[] = '- Rolled Back: `' . ((bool) ($result['result']['rolled_back'] ?? false) ? 'true' : 'false') . '`';
        $lines[] = '- Seed Run Removed: `' . ((bool) ($result['result']['seed_run_removed'] ?? false) ? 'true' : 'false') . '`';

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) ($result['error']['stage'] ?? 'runtime') . '`';
            $lines[] = '- Message: ' . (string) ($result['error']['message'] ?? 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param  list<string>  $warnings
     * @return array<string, mixed>|null
     */
    protected function strictWarningFailure(array $warnings): ?array
    {
        if ($warnings === []) {
            return null;
        }

        return [
            'stage' => 'impact',
            'reason' => 'warnings_are_fatal',
            'message' => 'Impact analysis returned warnings and --strict is enabled.',
            'warnings' => $warnings,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function forceRequiredError(): array
    {
        return [
            'stage' => 'safety',
            'reason' => 'force_required',
            'message' => 'Live package unseed requires --force.',
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<string, int>
     */
    protected function filterZeroCounts(array $counts): array
    {
        return collect($counts)
            ->map(fn ($count) => (int) $count)
            ->filter(fn (int $count) => $count > 0)
            ->all();
    }
}
