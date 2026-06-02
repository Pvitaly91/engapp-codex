<?php

namespace App\Services\PageV3PromptGenerator;

use App\Support\PackageSeed\AbstractJsonPackageFolderDestroyService;

class PageV3FolderDestroyService extends AbstractJsonPackageFolderDestroyService
{
    public function __construct(
        private readonly PageV3FolderPlanService $folderPlanService,
        private readonly PageV3PackageUnseedService $packageUnseedService,
    ) {
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/Page_V3';
    }

    protected function reportDirectory(): string
    {
        return 'folder-destroy-reports/page-v3';
    }

    /**
     * @return array<int, string>
     */
    protected function expectedLocales(): array
    {
        return ['en', 'pl'];
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
}
