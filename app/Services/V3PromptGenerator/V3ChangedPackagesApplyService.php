<?php

namespace App\Services\V3PromptGenerator;

use App\Support\PackageSeed\AbstractJsonPackageChangedApplyService;

class V3ChangedPackagesApplyService extends AbstractJsonPackageChangedApplyService
{
    public function __construct(
        private readonly V3ChangedPackagesPlanService $changedPackagesPlanService,
        private readonly V3DeletedPackageCleanupService $deletedPackageCleanupService,
        private readonly V3PackageSeedService $packageSeedService,
        private readonly V3PackageRefreshService $packageRefreshService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function planChanged(?string $targetInput, array $options): array
    {
        return $this->changedPackagesPlanService->run($targetInput, $options);
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function cleanupDeletedPackage(array $package, array $options): array
    {
        return $this->deletedPackageCleanupService->runPackageRecord($package, $options);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function seedPackage(string $targetInput, array $options): array
    {
        return $this->packageSeedService->run($targetInput, $options);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function refreshPackage(string $targetInput, array $options): array
    {
        return $this->packageRefreshService->run($targetInput, $options);
    }

    protected function reportDirectory(): string
    {
        return 'changed-package-apply-reports/v3';
    }
}
