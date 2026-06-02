<?php

namespace App\Services\V3PromptGenerator;

use App\Support\PackageSeed\AbstractJsonPackageFolderPlanService;

class V3FolderPlanService extends AbstractJsonPackageFolderPlanService
{
    public function __construct(
        private readonly V3PackageRefreshService $packageRefreshService,
        private readonly V3ReleaseCheckService $releaseCheckService,
    ) {
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/V3';
    }

    protected function reportDirectory(): string
    {
        return 'folder-plans/v3';
    }

    /**
     * @return array<int, string>
     */
    protected function expectedLocales(): array
    {
        return ['uk', 'en', 'pl'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function inspectPackageTarget(string $targetInput): array
    {
        return $this->packageRefreshService->inspectTarget($targetInput);
    }

    /**
     * @return array<string, mixed>
     */
    protected function releaseCheckReport(string $targetInput, string $profile): array
    {
        return $this->releaseCheckService->run($targetInput, $profile);
    }

    protected function seedCommandName(): string
    {
        return 'v3:seed-package';
    }

    protected function refreshCommandName(): string
    {
        return 'v3:refresh-package';
    }

    protected function unseedCommandName(): string
    {
        return 'v3:unseed-package';
    }

    protected function packageTypeWeight(string $packageType): int
    {
        return 0;
    }
}
