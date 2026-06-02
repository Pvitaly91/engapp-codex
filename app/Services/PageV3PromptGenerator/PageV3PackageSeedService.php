<?php

namespace App\Services\PageV3PromptGenerator;

use App\Support\Database\JsonPageDefinitionIndex;
use App\Support\Database\JsonPageRuntimeSeeder;
use App\Support\PackageSeed\AbstractJsonPackageSeedService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PageV3PackageSeedService extends AbstractJsonPackageSeedService
{
    public function __construct(
        private readonly PageV3ReleaseCheckService $releaseCheckService,
        private readonly JsonPageDefinitionIndex $definitionIndex,
    ) {}

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/Page_V3';
    }

    protected function reportDirectory(): string
    {
        return 'release-checks/page-v3';
    }

    /**
     * @return array<int, string>
     */
    protected function expectedLocales(): array
    {
        return ['en', 'pl'];
    }

    /**
     * @param  array<string, mixed>  $target
     */
    protected function expectedSeederClass(array $target): string
    {
        $relative = Str::after(
            str_replace('\\', '/', (string) $target['package_root_relative_path']),
            'database/seeders/Page_V3/'
        );
        $segments = array_values(array_filter(explode('/', $relative)));
        $className = (string) array_pop($segments);
        $namespace = implode('\\', $segments);

        return 'Database\\Seeders\\Page_V3\\'.($namespace !== '' ? $namespace.'\\' : '').$className;
    }

    /**
     * @return array<string, mixed>
     */
    protected function releaseCheckReport(string $targetInput, string $profile): array
    {
        return $this->releaseCheckService->run($targetInput, $profile);
    }

    /**
     * @return array<string, mixed>
     */
    protected function readDefinition(string $definitionAbsolutePath): array
    {
        return (new JsonPageRuntimeSeeder($definitionAbsolutePath))->readDefinition();
    }

    protected function executeRuntimeSeed(string $definitionAbsolutePath, string $resolvedSeederClass): void
    {
        (new JsonPageRuntimeSeeder($definitionAbsolutePath, $resolvedSeederClass))->seedFile();
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    protected function definitionSummary(
        array $definition,
        array $target,
        string $resolvedSeederClass,
    ): array {
        $contentType = $this->definitionIndex->resolveContentType($definition);
        $config = $this->definitionIndex->resolveContentConfig($definition);
        $blocks = array_values(array_filter((array) ($config['blocks'] ?? []), 'is_array'));
        $slug = trim((string) ($definition['slug'] ?? Arr::get($definition, 'category.slug', '')));
        $title = trim((string) (
            $contentType === 'category'
                ? (Arr::get($definition, 'category.title') ?: Arr::get($config, 'title', ''))
                : Arr::get($config, 'title', '')
        ));
        $categorySlug = trim((string) (
            $contentType === 'category'
                ? Arr::get($definition, 'category.slug', $slug)
                : Arr::get($config, 'category.slug', '')
        ));
        $parentSlug = trim((string) Arr::get($definition, 'category.parent_slug', ''));

        return [
            'content_type' => $contentType,
            'slug' => $slug !== '' ? $slug : null,
            'title' => $title !== '' ? $title : null,
            'type' => $this->nullableString($definition['type'] ?? Arr::get($config, 'type')),
            'category_slug' => $categorySlug !== '' ? $categorySlug : null,
            'category_parent_slug' => $parentSlug !== '' ? $parentSlug : null,
            'block_count' => count($blocks),
            'has_subtitle_block' => ! empty($config['subtitle_html']),
            'resolved_seeder_class' => $resolvedSeederClass,
            'definition_relative_path' => (string) ($target['definition_relative_path'] ?? ''),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $resolved = trim((string) $value);

        return $resolved !== '' ? $resolved : null;
    }
}
