<?php

namespace App\Services\V3PromptGenerator;

use App\Support\PackageSeed\AbstractJsonPackageFolderUnseedService;

class V3FolderUnseedService extends AbstractJsonPackageFolderUnseedService
{
    public function __construct(
        private readonly V3FolderPlanService $folderPlanService,
        private readonly V3PackageUnseedService $packageUnseedService,
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
    protected function unseedPackage(string $targetInput, array $options): array
    {
        return $this->packageUnseedService->run($targetInput, $options);
    }

    protected function reportDirectory(): string
    {
        return 'folder-unseed-reports/v3';
    }
}
