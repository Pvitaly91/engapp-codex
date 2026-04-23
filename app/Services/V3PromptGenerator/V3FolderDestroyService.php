<?php

namespace App\Services\V3PromptGenerator;

use App\Support\PackageSeed\AbstractJsonPackageFolderDestroyService;

class V3FolderDestroyService extends AbstractJsonPackageFolderDestroyService
{
    public function __construct(
        private readonly V3FolderPlanService $folderPlanService,
        private readonly V3PackageUnseedService $packageUnseedService,
    ) {
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/V3';
    }

    protected function reportDirectory(): string
    {
        return 'folder-destroy-reports/v3';
    }

    /**
     * @return array<int, string>
     */
    protected function expectedLocales(): array
    {
        return ['uk', 'en', 'pl'];
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
