<?php

namespace App\Services\PageV3PromptGenerator;

use App\Support\PackageSeed\AbstractJsonPackageFolderDestroyFilesService;

class PageV3FolderDestroyFilesService extends AbstractJsonPackageFolderDestroyFilesService
{
    public function __construct(
        private readonly PageV3FolderPlanService $folderPlanService,
    ) {
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/Page_V3';
    }

    protected function reportDirectory(): string
    {
        return 'folder-file-destroy-reports/page-v3';
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
}
