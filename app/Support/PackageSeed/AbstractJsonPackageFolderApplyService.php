<?php

namespace App\Support\PackageSeed;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

abstract class AbstractJsonPackageFolderApplyService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(string $targetInput, array $options = []): array
    {
        $resolvedOptions = $this->normalizeApplyOptions($options);
        $planResult = $this->planFolder($targetInput, [
            'mode' => $resolvedOptions['mode'],
            'with_release_check' => $resolvedOptions['with_release_check'],
            'check_profile' => $resolvedOptions['check_profile'],
            'strict' => $resolvedOptions['strict'],
        ]);
        $result = $this->resultTemplate($targetInput, $resolvedOptions, $planResult);
        $result['execution']['packages'] = $this->initialExecutionPackages(
            (array) ($result['plan']['packages'] ?? [])
        );

        if (is_array($result['plan']['error'] ?? null)) {
            $result['error'] = $result['plan']['error'];

            return $result;
        }

        $blockedPackages = array_values(array_filter(
            (array) ($result['plan']['packages'] ?? []),
            static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked'
        ));

        if ($blockedPackages !== []) {
            $result['execution']['stopped_on'] = (string) ($blockedPackages[0]['relative_path'] ?? null);
            $result['error'] = [
                'stage' => 'planning',
                'reason' => 'blocked_packages',
                'message' => 'Planner found blocked packages; folder apply aborted before writes.',
                'packages' => array_values(array_map(
                    static fn (array $package): string => (string) ($package['relative_path'] ?? ''),
                    $blockedPackages
                )),
            ];

            return $result;
        }

        if (! $resolvedOptions['dry_run'] && ! $resolvedOptions['force']) {
            $result['error'] = [
                'stage' => 'safety',
                'reason' => 'force_required',
                'message' => 'Live folder apply requires --force.',
            ];

            return $result;
        }

        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            $action = (string) ($package['recommended_action'] ?? 'skip');

            if (! in_array($action, ['seed', 'refresh'], true)) {
                continue;
            }

            $result['execution']['started']++;

            try {
                $serviceResult = $this->executePackageAction($action, $package, $resolvedOptions);
            } catch (Throwable $exception) {
                $serviceResult = [
                    'error' => [
                        'stage' => $action,
                        'message' => $exception->getMessage(),
                        'exception_class' => $exception::class,
                    ],
                ];
            }

            if (! $this->serviceSucceeded($action, $serviceResult)) {
                $serviceResult['error'] ??= [
                    'stage' => $action,
                    'reason' => 'unexpected_result',
                    'message' => 'Package service returned an unsuccessful result.',
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
                'message' => 'Folder apply stopped on the first package failure.',
                'package' => (string) ($package['relative_path'] ?? ''),
                'action' => $action,
                'service_error' => $serviceResult['error'],
            ];

            break;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $scopeRelativePath = (string) ($result['scope']['resolved_root_relative_path'] ?? 'scope');
        $scopeName = basename(str_replace('\\', '/', $scopeRelativePath));
        $mode = (string) ($result['scope']['mode'] ?? 'sync');
        $runType = (bool) ($result['execution']['dry_run'] ?? false) ? 'dry-run' : 'live';
        $hash = substr(sha1($scopeRelativePath . '|' . $mode . '|' . $runType), 0, 8);
        $fileName = Str::slug($scopeName !== '' ? $scopeName : 'scope')
            . '-'
            . $mode
            . '-'
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
    protected function normalizeApplyOptions(array $options): array
    {
        $mode = strtolower(trim((string) ($options['mode'] ?? 'sync')));

        if (! in_array($mode, ['missing', 'refresh', 'sync'], true)) {
            throw new RuntimeException('Unsupported apply mode. Use missing, refresh, or sync.');
        }

        $checkProfile = strtolower(trim((string) ($options['check_profile'] ?? 'release')));

        if (! in_array($checkProfile, ['scaffold', 'release'], true)) {
            throw new RuntimeException('Unsupported release-check profile. Use scaffold or release.');
        }

        return [
            'mode' => $mode,
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'force' => (bool) ($options['force'] ?? false),
            'with_release_check' => (bool) ($options['with_release_check'] ?? false),
            'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
            'check_profile' => $checkProfile,
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
                'mode' => (string) ($planScope['mode'] ?? $options['mode']),
                'with_release_check' => (bool) $options['with_release_check'],
                'check_profile' => (string) $options['check_profile'],
                'strict' => (bool) $options['strict'],
            ],
            'plan' => [
                'summary' => (array) ($planResult['summary'] ?? $this->defaultPlanSummary()),
                'packages' => array_values((array) ($planResult['packages'] ?? [])),
                'error' => is_array($planResult['error'] ?? null) ? $planResult['error'] : null,
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
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function executePackageAction(string $action, array $package, array $options): array
    {
        $target = $this->executionTarget($package);

        return match ($action) {
            'seed' => $this->seedPackage($target, [
                'dry_run' => (bool) ($options['dry_run'] ?? false),
                'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => (bool) ($options['strict'] ?? false),
            ]),
            'refresh' => $this->refreshPackage($target, [
                'dry_run' => (bool) ($options['dry_run'] ?? false),
                'force' => (bool) ($options['force'] ?? false),
                'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => (bool) ($options['strict'] ?? false),
            ]),
            default => throw new RuntimeException(sprintf('Unsupported package action [%s].', $action)),
        };
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
     */
    protected function serviceSucceeded(string $action, array $serviceResult): bool
    {
        if (is_array($serviceResult['error'] ?? null)) {
            return false;
        }

        return match ($action) {
            'seed' => (bool) ($serviceResult['result']['seeded'] ?? false),
            'refresh' => (bool) ($serviceResult['result']['refreshed'] ?? false),
            default => false,
        };
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# JSON Folder Apply',
            '',
            '- Target: `' . (string) ($result['scope']['input'] ?? '') . '`',
            '- Scope Root: `' . (string) ($result['scope']['resolved_root_relative_path'] ?? '') . '`',
            '- Single Package: `' . $this->boolString((bool) ($result['scope']['single_package'] ?? false)) . '`',
            '- Mode: `' . (string) ($result['scope']['mode'] ?? 'sync') . '`',
            '- Dry Run: `' . $this->boolString((bool) ($result['execution']['dry_run'] ?? false)) . '`',
            '- Force: `' . $this->boolString((bool) ($result['execution']['force'] ?? false)) . '`',
            '- Planner Release Check: `' . $this->boolString((bool) ($result['scope']['with_release_check'] ?? false)) . '`',
            '- Check Profile: `' . (string) ($result['scope']['check_profile'] ?? 'release') . '`',
            '- Strict: `' . $this->boolString((bool) ($result['scope']['strict'] ?? false)) . '`',
            '',
            '## Execution Contract',
            '',
            '- Planner-ordered: `true`',
            '- Package-atomic: `true`',
            '- Folder-transactional: `false`',
            '- Fail-fast: `true`',
            '',
            '## Plan Summary',
            '',
            '- Total Packages: ' . (int) ($result['plan']['summary']['total_packages'] ?? 0),
            '- Seed Candidates: ' . (int) ($result['plan']['summary']['seed_candidates'] ?? 0),
            '- Refresh Candidates: ' . (int) ($result['plan']['summary']['refresh_candidates'] ?? 0),
            '- Skipped: ' . (int) ($result['plan']['summary']['skipped'] ?? 0),
            '- Blocked: ' . (int) ($result['plan']['summary']['blocked'] ?? 0),
            '- Warnings: ' . (int) ($result['plan']['summary']['warnings'] ?? 0),
            '',
            '## Packages',
            '',
        ];

        foreach ((array) ($result['plan']['packages'] ?? []) as $index => $package) {
            $execution = (array) (($result['execution']['packages'][$index] ?? []) ?: []);
            $warnings = array_values(array_filter(array_map(
                static fn ($warning): string => trim((string) $warning),
                (array) ($package['warnings'] ?? [])
            )));

            $lines[] = '- `' . (string) ($package['relative_path'] ?? '') . '`'
                . ' | state=`' . (string) ($package['package_state'] ?? 'unknown') . '`'
                . ' | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`'
                . ' | execution=`' . (string) ($execution['status'] ?? 'pending') . '`'
                . ' | release=`' . (string) ($package['release_check']['status'] ?? 'skipped') . '`'
                . ' | warnings=' . ($warnings === [] ? 'none' : implode(' | ', $warnings));
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
    abstract protected function seedPackage(string $targetInput, array $options): array;

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    abstract protected function refreshPackage(string $targetInput, array $options): array;

    abstract protected function reportDirectory(): string;
}
