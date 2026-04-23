<?php

namespace App\Services\V3PromptGenerator;

use App\Support\PackageSeed\AbstractJsonPackageFolderDestroyFilesService;

class V3FolderDestroyFilesService extends AbstractJsonPackageFolderDestroyFilesService
{
    public function __construct(
        private readonly V3FolderPlanService $folderPlanService,
    ) {
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/V3';
    }

    protected function reportDirectory(): string
    {
        return 'folder-file-destroy-reports/v3';
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
}
