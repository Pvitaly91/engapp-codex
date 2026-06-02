<?php

namespace App\Services\PageV3PromptGenerator;

use Throwable;

class PageV3DeletedPackageCleanupService extends PageV3PackageUnseedService
{
    /**
     * @param  array<string, mixed>  $packageRecord
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function runPackageRecord(array $packageRecord, array $options = []): array
    {
        $resolvedOptions = $this->normalizeUnseedOptions($options);
        $target = $this->deletedTarget($packageRecord);
        $result = $this->resultTemplate($target, $resolvedOptions);
        $resolvedSeederClass = trim((string) ($packageRecord['resolved_seeder_class'] ?? ''));

        try {
            $target['resolved_seeder_class'] = $resolvedSeederClass;
            $result = $this->resultTemplate($target, $resolvedOptions);
            $result['definition_summary'] = array_merge(
                (array) ($packageRecord['historical_definition_summary'] ?? []),
                ['resolved_seeder_class' => $resolvedSeederClass !== '' ? $resolvedSeederClass : null]
            );

            $context = $this->buildContext(
                $resolvedSeederClass,
                $this->expandAdditionalCleanupClasses((array) ($options['additional_cleanup_classes'] ?? []))
            );
            $result['ownership'] = $context['ownership'];
            $result['impact']['warnings'] = $context['warnings'];
            $result['impact']['counts'] = $context['impact_counts'];

            if ($context['guard_error'] !== null) {
                $result['error'] = $context['guard_error'];

                return $result;
            }

            if (! $resolvedOptions['dry_run'] && ! $resolvedOptions['force']) {
                $result['error'] = $this->forceRequiredError();

                return $result;
            }

            if ($resolvedOptions['strict'] && $context['warnings'] !== []) {
                $result['error'] = $this->strictWarningFailure($context['warnings']);

                return $result;
            }

            if (! $context['ownership']['package_present_in_db'] && ! $context['ownership']['seed_run_present']) {
                return $result;
            }

            $execution = $this->runDeleteOperation(
                fn (): array => $this->executeUnseed(
                    $resolvedSeederClass,
                    $context['cleanup_classes'],
                    $resolvedOptions['dry_run']
                ),
                $resolvedOptions['dry_run']
            );

            $result['impact']['counts'] = $this->filterZeroCounts((array) ($execution['counts'] ?? []));
            $result['result'] = [
                'deleted' => (bool) ($execution['deleted'] ?? false),
                'rolled_back' => $resolvedOptions['dry_run'],
                'seed_run_removed' => (bool) ($execution['seed_run_removed'] ?? false),
            ];
        } catch (Throwable $exception) {
            $result['error'] = [
                'stage' => 'deleted_cleanup',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $packageRecord
     * @return array<string, mixed>
     */
    private function deletedTarget(array $packageRecord): array
    {
        $packageRootRelativePath = (string) (($packageRecord['historical_relative_path'] ?? null)
            ?: ($packageRecord['package_key'] ?? ''));

        return [
            'input' => $packageRootRelativePath,
            'definition_relative_path' => $packageRootRelativePath !== '' ? $packageRootRelativePath . '/definition.json' : null,
            'package_root_relative_path' => $packageRootRelativePath !== '' ? $packageRootRelativePath : null,
            'resolved_seeder_class' => trim((string) ($packageRecord['resolved_seeder_class'] ?? '')),
        ];
    }
}
