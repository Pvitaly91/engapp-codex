<?php

namespace App\Services\V3PromptGenerator;

use App\Support\PackageSeed\AbstractJsonPackageFolderApplyService;

class V3FolderApplyService extends AbstractJsonPackageFolderApplyService
{
    public function __construct(
        private readonly V3FolderPlanService $folderPlanService,
        private readonly V3PackageSeedService $packageSeedService,
        private readonly V3PackageRefreshService $packageRefreshService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function planFolder(string $targetInput, array $options): array
    {
        return $this->folderPlanService->run($targetInput, $options);
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
        return 'folder-apply-reports/v3';
    }
}
