<?php

namespace App\Support\PackageSeed;

use App\Support\ReleaseCheck\AbstractJsonPackageReleaseCheckService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractJsonPackageRefreshService extends AbstractJsonPackageReleaseCheckService
{
    /**
     * @return array<string, mixed>
     */
    public function inspectTarget(string $targetInput): array
    {
        $target = $this->resolvePackageTarget($targetInput);
        $expectedSeederClass = $this->expectedSeederClass($target);
        $resolvedSeederClass = $expectedSeederClass;
        $definitionExists = File::exists((string) $target['definition_absolute_path']);
        $loaderExists = File::exists((string) $target['loader_absolute_path']);
        $realSeederExists = File::exists((string) $target['real_seeder_absolute_path']);
        $definition = [];
        $definitionSummary = [];
        $definitionError = null;

        if ($definitionExists) {
            try {
                $definition = $this->readDefinition((string) $target['definition_absolute_path']);
                $resolvedSeederClass = $this->resolveSeederClass($definition, $expectedSeederClass);
                $definitionSummary = $this->definitionSummary($definition, $target, $resolvedSeederClass);
            } catch (Throwable $exception) {
                $definitionError = [
                    'stage' => 'definition',
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ];
            }
        }

        $target['resolved_seeder_class'] = $resolvedSeederClass;
        $ownership = $this->ownershipContext($definition, $target, $resolvedSeederClass);
        $warnings = collect((array) ($ownership['warnings'] ?? []))
            ->map(static fn ($warning): string => trim((string) $warning))
            ->filter();

        if (! $definitionExists) {
            $warnings->push('Package definition.json is missing.');
        }

        if (! $loaderExists) {
            $warnings->push('Top-level loader stub PHP is missing.');
        }

        if (! $realSeederExists) {
            $warnings->push('Package-local real seeder PHP is missing.');
        }

        if ($definitionError !== null) {
            $warnings->push('Package definition could not be read: ' . (string) ($definitionError['message'] ?? 'Unknown error.'));
        }

        return [
            'target' => $target,
            'definition_exists' => $definitionExists,
            'loader_exists' => $loaderExists,
            'real_seeder_exists' => $realSeederExists,
            'package_type' => $this->packageType($definitionSummary, $resolvedSeederClass, $target),
            'ownership' => [
                'seed_run_present' => (bool) ($ownership['seed_run_present'] ?? false),
                'package_present_in_db' => (bool) ($ownership['package_present_in_db'] ?? false),
            ],
            'warnings' => $warnings
                ->unique()
                ->values()
                ->all(),
            'definition_summary' => $definitionSummary,
            'error' => $definitionError,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(string $targetInput, array $options = []): array
    {
        $resolvedOptions = $this->normalizeRefreshOptions($options);
        $target = ['input' => trim($targetInput)];
        $result = $this->resultTemplate($target, $resolvedOptions);

        try {
            $target = $this->resolvePackageTarget($targetInput);
            $expectedSeederClass = $this->expectedSeederClass($target);
            $target['resolved_seeder_class'] = $expectedSeederClass;
            $result = $this->resultTemplate($target, $resolvedOptions);

            if (! $resolvedOptions['skip_release_check']) {
                $preflightReport = $this->releaseCheckReport($targetInput, $resolvedOptions['check_profile']);
                $result['preflight'] = [
                    'executed' => true,
                    'summary' => (array) ($preflightReport['summary'] ?? $this->defaultCheckSummary()),
                    'checks' => array_values((array) ($preflightReport['checks'] ?? [])),
                ];

                if ($preflightFailure = $this->preflightFailure($preflightReport, $resolvedOptions['strict'])) {
                    $this->populateDefinitionSummaryIfAvailable($result, $target, $expectedSeederClass);
                    $result['error'] = $preflightFailure;

                    return $result;
                }
            }

            $preflight = $result['preflight'];
            $definition = $this->readDefinition((string) $target['definition_absolute_path']);
            $resolvedSeederClass = $this->resolveSeederClass($definition, $expectedSeederClass);
            $target['resolved_seeder_class'] = $resolvedSeederClass;
            $result = $this->resultTemplate($target, $resolvedOptions);
            $result['target']['resolved_seeder_class'] = $resolvedSeederClass;
            $result['preflight'] = $preflight;
            $result['definition_summary'] = $this->definitionSummary($definition, $target, $resolvedSeederClass);

            $context = $this->ownershipContext($definition, $target, $resolvedSeederClass);
            $result['ownership'] = [
                'seed_run_present' => (bool) ($context['seed_run_present'] ?? false),
                'package_present_in_db' => (bool) ($context['package_present_in_db'] ?? false),
            ];
            $result['warnings'] = array_values(array_unique(array_filter(array_map(
                static fn ($warning): string => trim((string) $warning),
                (array) ($context['warnings'] ?? [])
            ))));
            $result['phases']['refresh_strategy'] = $this->refreshStrategy($result['ownership']);

            if (! $resolvedOptions['dry_run'] && ! $resolvedOptions['force']) {
                $result['error'] = $this->forceRequiredError();

                return $result;
            }

            if ($resolvedOptions['strict'] && $result['warnings'] !== []) {
                $result['error'] = $this->strictWarningFailure($result['warnings']);

                return $result;
            }

            $execution = $this->executeAdminRefresh($resolvedSeederClass, $resolvedOptions);
            $selectedRan = collect($execution['selected_ran'] ?? collect())
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values();
            $errors = collect($execution['errors'] ?? collect())
                ->filter()
                ->map(fn ($value) => (string) $value)
                ->unique()
                ->values();
            $selectedSeeded = $selectedRan->contains($resolvedSeederClass);
            $rolledBack = (bool) ($execution['rolled_back'] ?? false);
            $strategy = (string) ($result['phases']['refresh_strategy'] ?? 'admin_refresh');

            $result['phases'] = array_merge($result['phases'], [
                'refresh_strategy' => $strategy,
                'unseed_executed' => $strategy !== 'seed_only_fallback',
                'seed_executed' => $selectedSeeded,
                'rolled_back' => $rolledBack,
            ]);

            if (! $selectedSeeded || $errors->isNotEmpty()) {
                $result['result'] = [
                    'refreshed' => false,
                    'seeded_after' => false,
                    'seed_run_logged' => false,
                ];
                $result['error'] = [
                    'stage' => 'refresh',
                    'reason' => 'refresh_failed',
                    'message' => $errors->isNotEmpty()
                        ? $errors->implode(' ')
                        : (string) ($execution['message'] ?? 'Package refresh failed.'),
                    'errors' => $errors->all(),
                ];

                return $result;
            }

            $result['result'] = [
                'refreshed' => true,
                'seeded_after' => true,
                'seed_run_logged' => ! $resolvedOptions['dry_run'] && $this->seedRunPresent($resolvedSeederClass),
            ];
        } catch (Throwable $exception) {
            $result['error'] = [
                'stage' => $target === ['input' => trim($targetInput)] ? 'target_resolution' : 'refresh',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function normalizeRefreshOptions(array $options): array
    {
        return [
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'force' => (bool) ($options['force'] ?? false),
            'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
            'check_profile' => $this->normalizeProfile((string) ($options['check_profile'] ?? 'release')),
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
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'release_check_skipped' => (bool) ($options['skip_release_check'] ?? false),
            ],
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
            'error' => null,
        ];
    }

    /**
     * @param  array<string, bool>  $ownership
     */
    protected function refreshStrategy(array $ownership): string
    {
        return ! ($ownership['seed_run_present'] ?? false)
            && ! ($ownership['package_present_in_db'] ?? false)
                ? 'seed_only_fallback'
                : 'admin_refresh';
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected function resolveSeederClass(array $definition, string $fallbackSeederClass): string
    {
        $configured = trim((string) data_get($definition, 'seeder.class', ''));

        return $configured !== '' ? $configured : $fallbackSeederClass;
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>|null
     */
    protected function preflightFailure(array $report, bool $strict): ?array
    {
        $failCount = (int) ($report['summary']['check_counts']['fail'] ?? 0);
        $warningCount = (int) ($report['summary']['check_counts']['warn'] ?? 0);

        if ($failCount > 0) {
            return [
                'stage' => 'preflight',
                'reason' => 'checks_failed',
                'message' => 'Preflight release-check failed.',
                'check_counts' => [
                    'pass' => (int) ($report['summary']['check_counts']['pass'] ?? 0),
                    'warn' => $warningCount,
                    'fail' => $failCount,
                ],
            ];
        }

        if ($strict && $warningCount > 0) {
            return [
                'stage' => 'preflight',
                'reason' => 'warnings_are_fatal',
                'message' => 'Preflight release-check returned warnings and --strict is enabled.',
                'check_counts' => [
                    'pass' => (int) ($report['summary']['check_counts']['pass'] ?? 0),
                    'warn' => $warningCount,
                    'fail' => $failCount,
                ],
            ];
        }

        return null;
    }

    /**
     * @param  list<string>  $warnings
     * @return array<string, mixed>
     */
    protected function strictWarningFailure(array $warnings): array
    {
        return [
            'stage' => 'impact',
            'reason' => 'warnings_are_fatal',
            'message' => 'Refresh impact returned warnings and --strict is enabled.',
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
            'message' => 'Live package refresh requires --force.',
        ];
    }

    protected function seedRunPresent(string $className): bool
    {
        return Schema::hasTable('seed_runs')
            && DB::table('seed_runs')->where('class_name', $className)->exists();
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
            (array) ($result['warnings'] ?? [])
        )));
        $lines = [
            '# JSON Package Refresh',
            '',
            '- Definition: `' . ($result['target']['definition_relative_path'] ?? '') . '`',
            '- Package Root: `' . ($result['target']['package_root_relative_path'] ?? '') . '`',
            '- Seeder Class: `' . ($result['target']['resolved_seeder_class'] ?? '') . '`',
            '- Mode: `' . ((bool) ($result['mode']['dry_run'] ?? false) ? 'dry-run' : 'live') . '`',
            '- Force: `' . $this->boolString((bool) ($result['mode']['force'] ?? false)) . '`',
            '- Check Profile: `' . ($result['mode']['check_profile'] ?? 'release') . '`',
            '- Release Check: `' . ((bool) ($result['mode']['release_check_skipped'] ?? false) ? 'skipped' : 'enabled') . '`',
            '- Strategy: `' . ($result['phases']['refresh_strategy'] ?? 'unknown') . '`',
            '- Seed Run Present: `' . $this->boolString((bool) ($result['ownership']['seed_run_present'] ?? false)) . '`',
            '- Package Present In DB: `' . $this->boolString((bool) ($result['ownership']['package_present_in_db'] ?? false)) . '`',
            '',
            '## Warnings',
            '',
        ];

        if ($warnings === []) {
            $lines[] = '- None.';
        } else {
            foreach ($warnings as $warning) {
                $lines[] = '- ' . $warning;
            }
        }

        $lines[] = '';
        $lines[] = '## Preflight';
        $lines[] = '';
        $lines[] = sprintf(
            '- Executed: `%s`',
            $this->boolString((bool) ($result['preflight']['executed'] ?? false))
        );
        $lines[] = sprintf(
            '- Summary: `%d PASS / %d WARN / %d FAIL`',
            (int) ($result['preflight']['summary']['check_counts']['pass'] ?? 0),
            (int) ($result['preflight']['summary']['check_counts']['warn'] ?? 0),
            (int) ($result['preflight']['summary']['check_counts']['fail'] ?? 0)
        );
        $lines[] = '';
        $lines[] = '## Result';
        $lines[] = '';
        $lines[] = '- Refreshed: `' . $this->boolString((bool) ($result['result']['refreshed'] ?? false)) . '`';
        $lines[] = '- Seeded After: `' . $this->boolString((bool) ($result['result']['seeded_after'] ?? false)) . '`';
        $lines[] = '- Seed Run Logged: `' . $this->boolString((bool) ($result['result']['seed_run_logged'] ?? false)) . '`';
        $lines[] = '- Rolled Back: `' . $this->boolString((bool) ($result['phases']['rolled_back'] ?? false)) . '`';

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) ($result['error']['stage'] ?? 'refresh') . '`';
            $lines[] = '- Message: ' . (string) ($result['error']['message'] ?? 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $target
     */
    protected function populateDefinitionSummaryIfAvailable(
        array &$result,
        array $target,
        string $expectedSeederClass,
    ): void {
        try {
            $definition = $this->readDefinition((string) $target['definition_absolute_path']);
            $resolvedSeederClass = $this->resolveSeederClass($definition, $expectedSeederClass);
            $result['target']['resolved_seeder_class'] = $resolvedSeederClass;
            $result['definition_summary'] = $this->definitionSummary($definition, $target, $resolvedSeederClass);
        } catch (Throwable) {
            // Keep the result stable even when definition loading fails.
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultCheckSummary(): array
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

    protected function boolString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    /**
     * @param  array<string, mixed>  $target
     */
    abstract protected function expectedSeederClass(array $target): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function releaseCheckReport(string $targetInput, string $profile): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected function readDefinition(string $definitionAbsolutePath): array;

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    abstract protected function ownershipContext(
        array $definition,
        array $target,
        string $resolvedSeederClass,
    ): array;

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    abstract protected function executeAdminRefresh(string $resolvedSeederClass, array $options): array;

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    abstract protected function definitionSummary(
        array $definition,
        array $target,
        string $resolvedSeederClass,
    ): array;

    /**
     * @param  array<string, mixed>  $definitionSummary
     * @param  array<string, mixed>  $target
     */
    abstract protected function packageType(
        array $definitionSummary,
        string $resolvedSeederClass,
        array $target,
    ): string;
}
