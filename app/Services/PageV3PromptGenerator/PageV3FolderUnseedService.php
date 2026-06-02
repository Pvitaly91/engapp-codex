<?php

namespace App\Services\PageV3PromptGenerator;

use App\Support\PackageSeed\AbstractJsonPackageFolderUnseedService;

class PageV3FolderUnseedService extends AbstractJsonPackageFolderUnseedService
{
    public function __construct(
        private readonly PageV3FolderPlanService $folderPlanService,
        private readonly PageV3PackageUnseedService $packageUnseedService,
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
        return 'folder-unseed-reports/page-v3';
    }
}
