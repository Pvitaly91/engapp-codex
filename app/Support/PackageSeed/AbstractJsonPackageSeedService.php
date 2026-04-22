<?php

namespace App\Support\PackageSeed;

use App\Support\ReleaseCheck\AbstractJsonPackageReleaseCheckService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

abstract class AbstractJsonPackageSeedService extends AbstractJsonPackageReleaseCheckService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(string $targetInput, array $options = []): array
    {
        $resolvedOptions = $this->normalizeSeedOptions($options);
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

        $seedingAttempted = false;

        try {
            $definition = $this->readDefinition((string) $target['definition_absolute_path']);
            $resolvedSeederClass = $this->resolveSeederClass($definition, $expectedSeederClass);
            $result['target']['resolved_seeder_class'] = $resolvedSeederClass;
            $result['definition_summary'] = $this->definitionSummary($definition, $target, $resolvedSeederClass);

            if ($resolvedOptions['dry_run']) {
                $seedingAttempted = true;
                $this->executeDryRun((string) $target['definition_absolute_path'], $resolvedSeederClass);
                $result['result'] = [
                    'seeded' => true,
                    'rolled_back' => true,
                    'seed_run_logged' => false,
                ];

                return $result;
            }

            $seedingAttempted = true;
            $seedRunLogged = $this->executeLiveSeed((string) $target['definition_absolute_path'], $resolvedSeederClass);
            $result['result'] = [
                'seeded' => true,
                'rolled_back' => false,
                'seed_run_logged' => $seedRunLogged,
            ];
        } catch (Throwable $exception) {
            $result['error'] = [
                'stage' => 'seed',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ];
            $result['result']['rolled_back'] = $seedingAttempted;
        }

        return $result;
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
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'release_check_skipped' => (bool) ($options['skip_release_check'] ?? false),
            ],
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
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function normalizeSeedOptions(array $options): array
    {
        return [
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
            'check_profile' => $this->normalizeProfile((string) ($options['check_profile'] ?? 'release')),
            'strict' => (bool) ($options['strict'] ?? false),
        ];
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

    protected function executeDryRun(string $definitionAbsolutePath, string $resolvedSeederClass): void
    {
        try {
            DB::transaction(function () use ($definitionAbsolutePath, $resolvedSeederClass): void {
                $this->executeRuntimeSeed($definitionAbsolutePath, $resolvedSeederClass);

                throw new DryRunRollbackException('Rollback-only dry run completed.');
            });
        } catch (DryRunRollbackException) {
            return;
        }
    }

    protected function executeLiveSeed(string $definitionAbsolutePath, string $resolvedSeederClass): bool
    {
        $seedRunLogged = false;

        DB::transaction(function () use ($definitionAbsolutePath, $resolvedSeederClass, &$seedRunLogged): void {
            $this->executeRuntimeSeed($definitionAbsolutePath, $resolvedSeederClass);
            $seedRunLogged = $this->logSeedRun($resolvedSeederClass);
        });

        return $seedRunLogged;
    }

    protected function logSeedRun(string $className): bool
    {
        if (! Schema::hasTable('seed_runs')) {
            return false;
        }

        DB::table('seed_runs')->updateOrInsert(
            ['class_name' => $className],
            ['ran_at' => now()]
        );

        return true;
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

    abstract protected function executeRuntimeSeed(string $definitionAbsolutePath, string $resolvedSeederClass): void;

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
}
