<?php

namespace App\Support\PackageSeed;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

abstract class AbstractJsonPackageFolderUnseedService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(string $targetInput, array $options = []): array
    {
        $resolvedOptions = $this->normalizeOptions($options);
        $planResult = $this->planFolder($targetInput, [
            'mode' => 'unseed',
            'strict' => $resolvedOptions['strict'],
        ]);
        $result = $this->resultTemplate($targetInput, $resolvedOptions, $planResult);
        $result['preflight']['packages'] = $this->initialPreflightPackages(
            (array) ($result['plan']['packages'] ?? [])
        );
        $result['execution']['packages'] = $this->initialExecutionPackages(
            (array) ($result['plan']['packages'] ?? [])
        );

        if (is_array($result['plan']['error'] ?? null)) {
            $result['error'] = $result['plan']['error'];

            return $result;
        }

        $result = $this->runPreflight($result, $resolvedOptions);

        if (is_array($result['error'] ?? null)) {
            return $result;
        }

        if (! $resolvedOptions['dry_run'] && ! $resolvedOptions['force']) {
            $result['error'] = $this->forceRequiredError();

            return $result;
        }

        if ($resolvedOptions['dry_run']) {
            return $result;
        }

        return $this->runExecution($result, $resolvedOptions);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $scopeRelativePath = (string) ($result['scope']['resolved_root_relative_path'] ?? 'scope');
        $scopeName = basename(str_replace('\\', '/', $scopeRelativePath));
        $runType = (bool) ($result['execution']['dry_run'] ?? false) ? 'dry-run' : 'live';
        $hash = substr(sha1($scopeRelativePath . '|' . $runType), 0, 8);
        $fileName = Str::slug($scopeName !== '' ? $scopeName : 'scope')
            . '-unseed-'
            . $runType
            . '-'
            . $hash
            . '.md';
        $relativePath = trim($this->reportDirectory(), '/') . '/' . $fileName;

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function normalizeOptions(array $options): array
    {
        return [
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'force' => (bool) ($options['force'] ?? false),
            'strict' => (bool) ($options['strict'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $planResult
     * @return array<string, mixed>
     */
    protected function resultTemplate(string $targetInput, array $options, array $planResult): array
    {
        $planScope = (array) ($planResult['scope'] ?? []);

        return [
            'scope' => [
                'input' => trim($targetInput),
                'resolved_root_absolute_path' => $planScope['resolved_root_absolute_path'] ?? null,
                'resolved_root_relative_path' => $planScope['resolved_root_relative_path'] ?? null,
                'single_package' => (bool) ($planScope['single_package'] ?? false),
                'mode' => (string) ($planScope['mode'] ?? 'unseed'),
                'strict' => (bool) $options['strict'],
            ],
            'plan' => [
                'summary' => (array) ($planResult['summary'] ?? $this->defaultPlanSummary()),
                'packages' => array_values((array) ($planResult['packages'] ?? [])),
                'error' => is_array($planResult['error'] ?? null) ? $planResult['error'] : null,
            ],
            'preflight' => [
                'executed' => false,
                'packages' => [],
                'summary' => $this->defaultPreflightSummary(),
            ],
            'execution' => [
                'dry_run' => (bool) $options['dry_run'],
                'force' => (bool) $options['force'],
                'fail_fast' => true,
                'package_atomic' => true,
                'folder_transactional' => false,
                'started' => 0,
                'completed' => 0,
                'succeeded' => 0,
                'failed' => 0,
                'stopped_on' => null,
                'packages' => [],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function defaultPlanSummary(): array
    {
        return [
            'total_packages' => 0,
            'seed_candidates' => 0,
            'refresh_candidates' => 0,
            'unseed_candidates' => 0,
            'destroy_files_candidates' => 0,
            'destroy_candidates' => 0,
            'skipped' => 0,
            'blocked' => 0,
            'warnings' => 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function defaultPreflightSummary(): array
    {
        return [
            'candidates' => 0,
            'ok' => 0,
            'warn' => 0,
            'fail' => 0,
            'skipped' => 0,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function initialPreflightPackages(array $packages): array
    {
        return array_values(array_map(function (array $package): array {
            $action = (string) ($package['recommended_action'] ?? 'skip');

            return [
                'relative_path' => (string) ($package['relative_path'] ?? ''),
                'action' => $action,
                'status' => $action === 'unseed' ? 'pending' : 'skip',
                'impact' => [
                    'counts' => [],
                    'warnings' => array_values(array_map(
                        static fn ($warning): string => trim((string) $warning),
                        (array) ($package['warnings'] ?? [])
                    )),
                ],
                'service_result' => null,
            ];
        }, $packages));
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function initialExecutionPackages(array $packages): array
    {
        return array_values(array_map(function (array $package): array {
            $action = (string) ($package['recommended_action'] ?? 'skip');

            return [
                'relative_path' => (string) ($package['relative_path'] ?? ''),
                'action' => $action,
                'executed' => false,
                'status' => match ($action) {
                    'skip' => 'skipped',
                    'blocked' => 'blocked',
                    default => 'pending',
                },
                'service_result' => null,
            ];
        }, $packages));
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function runPreflight(array $result, array $options): array
    {
        $result['preflight']['executed'] = true;

        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            $action = (string) ($package['recommended_action'] ?? 'skip');

            if ($action !== 'unseed') {
                $result['preflight']['summary']['skipped']++;

                continue;
            }

            $result['preflight']['summary']['candidates']++;

            try {
                $serviceResult = $this->unseedPackage($this->executionTarget($package), [
                    'dry_run' => true,
                    'force' => false,
                    'strict' => (bool) ($options['strict'] ?? false),
                    'additional_cleanup_classes' => $this->additionalCleanupClassesForPackage(
                        (array) ($result['plan']['packages'] ?? []),
                        $index
                    ),
                ]);
            } catch (Throwable $exception) {
                $serviceResult = [
                    'error' => [
                        'stage' => 'preflight',
                        'message' => $exception->getMessage(),
                        'exception_class' => $exception::class,
                    ],
                ];
            }

            $impact = $this->impactFromServiceResult($serviceResult);
            $status = $this->preflightStatus($serviceResult, $impact);

            $result['preflight']['packages'][$index]['status'] = $status;
            $result['preflight']['packages'][$index]['impact'] = $impact;
            $result['preflight']['packages'][$index]['service_result'] = $serviceResult;
            $result['preflight']['summary'][$status === 'skip' ? 'skipped' : $status]++;

            if ($status !== 'fail' || is_array($result['error'] ?? null)) {
                continue;
            }

            $result['execution']['stopped_on'] = (string) ($package['relative_path'] ?? null);
            $result['error'] = [
                'stage' => 'preflight',
                'reason' => 'preflight_failed',
                'message' => 'Folder unseed preflight failed; live execution did not start.',
                'package' => (string) ($package['relative_path'] ?? ''),
                'service_error' => $serviceResult['error'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function runExecution(array $result, array $options): array
    {
        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            $action = (string) ($package['recommended_action'] ?? 'skip');

            if ($action !== 'unseed') {
                continue;
            }

            $result['execution']['started']++;

            try {
                $serviceResult = $this->unseedPackage($this->executionTarget($package), [
                    'dry_run' => false,
                    'force' => true,
                    'strict' => (bool) ($options['strict'] ?? false),
                    'additional_cleanup_classes' => $this->additionalCleanupClassesForPackage(
                        (array) ($result['plan']['packages'] ?? []),
                        $index
                    ),
                ]);
            } catch (Throwable $exception) {
                $serviceResult = [
                    'error' => [
                        'stage' => 'execution',
                        'message' => $exception->getMessage(),
                        'exception_class' => $exception::class,
                    ],
                ];
            }

            if (! $this->serviceSucceeded($serviceResult)) {
                $serviceResult['error'] ??= [
                    'stage' => 'execution',
                    'reason' => 'unexpected_result',
                    'message' => 'Package unseed service returned an unsuccessful result.',
                ];
            }

            $result['execution']['completed']++;
            $result['execution']['packages'][$index]['executed'] = true;
            $result['execution']['packages'][$index]['service_result'] = $serviceResult;

            if (empty($serviceResult['error'])) {
                $result['execution']['packages'][$index]['status'] = 'ok';
                $result['execution']['succeeded']++;

                continue;
            }

            $result['execution']['packages'][$index]['status'] = 'failed';
            $result['execution']['failed']++;
            $result['execution']['stopped_on'] = (string) ($package['relative_path'] ?? null);
            $result['error'] = [
                'stage' => 'execution',
                'reason' => 'package_failed',
                'message' => 'Folder unseed stopped on the first package failure.',
                'package' => (string) ($package['relative_path'] ?? ''),
                'action' => $action,
                'service_error' => $serviceResult['error'],
            ];

            break;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function executionTarget(array $package): string
    {
        $target = trim((string) ($package['definition_relative_path'] ?? $package['relative_path'] ?? ''));

        if ($target === '') {
            throw new RuntimeException(sprintf(
                'Planner did not provide an executable target for package [%s].',
                (string) ($package['relative_path'] ?? 'unknown')
            ));
        }

        return $target;
    }

    /**
     * @param  array<string, mixed>  $serviceResult
     * @return array<string, mixed>
     */
    protected function impactFromServiceResult(array $serviceResult): array
    {
        return [
            'counts' => (array) ($serviceResult['impact']['counts'] ?? []),
            'warnings' => array_values(array_filter(array_map(
                static fn ($warning): string => trim((string) $warning),
                (array) ($serviceResult['impact']['warnings'] ?? [])
            ))),
        ];
    }

    /**
     * @param  array<string, mixed>  $serviceResult
     * @param  array<string, mixed>  $impact
     */
    protected function preflightStatus(array $serviceResult, array $impact): string
    {
        if (is_array($serviceResult['error'] ?? null)) {
            return 'fail';
        }

        return ((array) ($impact['warnings'] ?? [])) === [] ? 'ok' : 'warn';
    }

    /**
     * @param  array<string, mixed>  $serviceResult
     */
    protected function serviceSucceeded(array $serviceResult): bool
    {
        if (is_array($serviceResult['error'] ?? null)) {
            return false;
        }

        if ((bool) ($serviceResult['result']['deleted'] ?? false)) {
            return true;
        }

        if ((bool) ($serviceResult['result']['seed_run_removed'] ?? false)) {
            return true;
        }

        return ! (bool) ($serviceResult['ownership']['seed_run_present'] ?? true)
            && ! (bool) ($serviceResult['ownership']['package_present_in_db'] ?? true);
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return list<string>
     */
    protected function additionalCleanupClassesForPackage(array $packages, int $currentIndex): array
    {
        return collect(array_slice($packages, 0, $currentIndex))
            ->filter(static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'unseed')
            ->map(static fn (array $package): string => trim((string) ($package['resolved_seeder_class'] ?? '')))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function forceRequiredError(): array
    {
        return [
            'stage' => 'safety',
            'reason' => 'force_required',
            'message' => 'Live folder unseed requires --force.',
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# JSON Folder Unseed',
            '',
            '- Target: `' . (string) ($result['scope']['input'] ?? '') . '`',
            '- Scope Root: `' . (string) ($result['scope']['resolved_root_relative_path'] ?? '') . '`',
            '- Single Package: `' . $this->boolString((bool) ($result['scope']['single_package'] ?? false)) . '`',
            '- Mode: `' . (string) ($result['scope']['mode'] ?? 'unseed') . '`',
            '- Dry Run: `' . $this->boolString((bool) ($result['execution']['dry_run'] ?? false)) . '`',
            '- Force: `' . $this->boolString((bool) ($result['execution']['force'] ?? false)) . '`',
            '- Strict: `' . $this->boolString((bool) ($result['scope']['strict'] ?? false)) . '`',
            '',
            '## Contract',
            '',
            '- Planner-ordered: `true`',
            '- Package-atomic: `true`',
            '- Folder-transactional: `false`',
            '- Fail-fast: `true`',
            '- Global preflight before live writes: `true`',
            '',
            '## Plan Summary',
            '',
            '- Total Packages: ' . (int) ($result['plan']['summary']['total_packages'] ?? 0),
            '- Unseed Candidates: ' . (int) ($result['plan']['summary']['unseed_candidates'] ?? 0),
            '- Skipped: ' . (int) ($result['plan']['summary']['skipped'] ?? 0),
            '- Blocked: ' . (int) ($result['plan']['summary']['blocked'] ?? 0),
            '- Warnings: ' . (int) ($result['plan']['summary']['warnings'] ?? 0),
            '',
            '## Preflight Summary',
            '',
            '- Candidates: ' . (int) ($result['preflight']['summary']['candidates'] ?? 0),
            '- OK: ' . (int) ($result['preflight']['summary']['ok'] ?? 0),
            '- Warn: ' . (int) ($result['preflight']['summary']['warn'] ?? 0),
            '- Fail: ' . (int) ($result['preflight']['summary']['fail'] ?? 0),
            '- Skipped: ' . (int) ($result['preflight']['summary']['skipped'] ?? 0),
            '',
            '## Packages',
            '',
        ];

        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            $preflight = (array) (($result['preflight']['packages'][$index] ?? []) ?: []);
            $execution = (array) (($result['execution']['packages'][$index] ?? []) ?: []);

            $lines[] = '- `' . (string) ($package['relative_path'] ?? '') . '`'
                . ' | state=`' . (string) ($package['package_state'] ?? 'unknown') . '`'
                . ' | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`'
                . ' | preflight=`' . (string) ($preflight['status'] ?? 'skip') . '`'
                . ' | execution=`' . (string) ($execution['status'] ?? 'pending') . '`';
        }

        $lines[] = '';
        $lines[] = '## Execution Summary';
        $lines[] = '';
        $lines[] = '- Started: ' . (int) ($result['execution']['started'] ?? 0);
        $lines[] = '- Completed: ' . (int) ($result['execution']['completed'] ?? 0);
        $lines[] = '- Succeeded: ' . (int) ($result['execution']['succeeded'] ?? 0);
        $lines[] = '- Failed: ' . (int) ($result['execution']['failed'] ?? 0);
        $lines[] = '- Stopped On: `' . (string) ($result['execution']['stopped_on'] ?? '') . '`';

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) ($result['error']['stage'] ?? 'execution') . '`';
            $lines[] = '- Message: ' . (string) ($result['error']['message'] ?? 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    protected function boolString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    abstract protected function planFolder(string $targetInput, array $options): array;

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    abstract protected function unseedPackage(string $targetInput, array $options): array;

    abstract protected function reportDirectory(): string;
}
