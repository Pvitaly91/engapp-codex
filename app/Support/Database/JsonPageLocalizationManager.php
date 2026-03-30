<?php

namespace App\Support\Database;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\TextBlock\TextBlockUuidGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;

class JsonPageLocalizationManager
{
    /**
     * @var array<string, array<string, mixed>>|null
     */
    private ?array $descriptorCache = null;

    /**
     * @var array<string, int>
     */
    private array $tagCache = [];

    public function __construct(private readonly JsonPageDefinitionIndex $definitionIndex)
    {
    }

    public function isVirtualLocalizationSeeder(string $className): bool
    {
        return $this->descriptorForClass($className) !== null;
    }

    public function virtualSeederClasses(): array
    {
        return $this->descriptorMap()->keys()->all();
    }

    public function descriptorForClass(string $className): ?array
    {
        return $this->descriptorMap()->get($className);
    }

    public function filePathForClass(string $className): ?string
    {
        return $this->descriptorForClass($className)['path'] ?? null;
    }

    public function syncDefinitionLocalizations(
        array $definition,
        ?string $definitionPath = null,
        ?string $fallbackSeederClass = null,
    ): void {
        $baseSeederClass = $this->definitionIndex->resolveSeederClassName($definition, $fallbackSeederClass);
        $baseIndex = $this->definitionIndex->indexBlocks($definition, $definitionPath, $baseSeederClass);

        foreach ($this->matchingDescriptors($definitionPath, $baseSeederClass) as $descriptor) {
            $className = (string) ($descriptor['class_name'] ?? '');

            if ($className === '') {
                continue;
            }

            $this->removeVirtualSeederData($className);
            $this->applyLocalizationDefinition(
                $className,
                $this->loadLocalizationDefinition((string) $descriptor['path']),
                $baseIndex
            );
        }
    }

    public function applyVirtualSeeder(string $className): array
    {
        $definition = $this->loadVirtualSeederDefinition($className);
        $baseIndex = $this->targetDefinitionIndex($definition);
        $result = $this->applyLocalizationDefinition($className, $definition, $baseIndex);

        if (($result['localized_blocks'] ?? 0) === 0) {
            throw new RuntimeException('Локалізацію не застосовано: цільова сторінка або категорія ще не створена.');
        }

        return $result;
    }

    public function removeVirtualSeederData(string $className): array
    {
        $deletedBlocks = TextBlock::query()
            ->where('seeder', $className)
            ->delete();

        $locale = $this->normalizeLocale((string) (Arr::get($this->descriptorForClass($className), 'locale', '')));

        return [
            'locale' => $locale,
            'deleted_blocks' => $deletedBlocks,
        ];
    }

    public function buildVirtualSeederPreview(string $className): array
    {
        $definition = $this->loadVirtualSeederDefinition($className);
        $baseIndex = $this->targetDefinitionIndex($definition);
        $resolvedBlocks = $this->resolveLocalizationBlocks($definition, $baseIndex);
        $targetExists = $this->targetExists($baseIndex);
        $contentType = (string) ($baseIndex['content_type'] ?? 'page');
        $target = [
            'seeder_class' => trim((string) Arr::get($definition, 'target.seeder_class', '')),
            'definition' => $baseIndex['definition_key'] ?? trim((string) Arr::get($definition, 'target.definition', '')),
            'definition_path' => $baseIndex['definition_path'] ?? $this->resolveTargetDefinitionPath($definition),
            'slug' => (string) ($baseIndex['slug'] ?? ''),
            'category_slug' => (string) ($baseIndex['category_slug'] ?? ''),
            'content_type' => $contentType,
        ];

        $blocks = collect($resolvedBlocks)
            ->map(function (array $resolvedBlock) {
                $indexedBlock = $resolvedBlock['indexed_block'];
                $payload = $resolvedBlock['payload'];

                return [
                    'reference' => $indexedBlock['reference'] ?? null,
                    'index' => $indexedBlock['index'] ?? null,
                    'type' => $payload['type'] ?? $indexedBlock['type'] ?? null,
                    'column' => $payload['column'] ?? $indexedBlock['column'] ?? null,
                    'level' => $payload['level'] ?? $indexedBlock['level'] ?? null,
                    'heading' => $payload['heading'] ?? $indexedBlock['heading'] ?? null,
                    'body' => $payload['body'] ?? $indexedBlock['body'] ?? null,
                ];
            })
            ->values();

        return [
            'type' => 'page_localizations',
            'locale' => $this->normalizeLocale((string) ($definition['locale'] ?? '')),
            'content_type' => $contentType,
            'target' => $target,
            'blocks' => $blocks,
            'localizedBlockCount' => $blocks->count(),
            'existingTargetCount' => $targetExists ? 1 : 0,
            'hasExistingTarget' => $targetExists,
        ];
    }

    private function matchingDescriptors(?string $definitionPath, string $baseSeederClass): Collection
    {
        return $this->descriptorMap()
            ->filter(function (array $descriptor) use ($definitionPath, $baseSeederClass) {
                $localizationDefinition = $this->loadLocalizationDefinition((string) $descriptor['path']);

                return $this->matchesTarget($localizationDefinition, $definitionPath, $baseSeederClass);
            })
            ->values();
    }

    private function loadVirtualSeederDefinition(string $className): array
    {
        $descriptor = $this->descriptorForClass($className);

        if (! is_array($descriptor)) {
            throw new RuntimeException("Page localization seeder not found: {$className}");
        }

        return $this->loadLocalizationDefinition((string) $descriptor['path']);
    }

    private function loadLocalizationDefinition(string $path): array
    {
        $definition = $this->definitionIndex->loadDefinitionFromFile($path);

        if (! is_array($definition['blocks'] ?? null)) {
            throw new RuntimeException("Page localization definition must contain a blocks array: {$path}");
        }

        return $definition;
    }

    private function descriptorMap(): Collection
    {
        if (is_array($this->descriptorCache)) {
            return collect($this->descriptorCache);
        }

        $directory = $this->localizationsDirectory();

        if (! File::isDirectory($directory)) {
            $this->descriptorCache = [];

            return collect();
        }

        $map = collect(File::allFiles($directory))
            ->filter(fn (SplFileInfo $file) => strtolower($file->getExtension()) === 'json')
            ->mapWithKeys(function (SplFileInfo $file) {
                $definition = $this->loadLocalizationDefinition($file->getPathname());
                $className = $this->resolveVirtualSeederClassName($definition, $file->getPathname());

                return [$className => [
                    'class_name' => $className,
                    'path' => $file->getPathname(),
                    'locale' => $this->normalizeLocale((string) ($definition['locale'] ?? '')),
                    'target_seeder_class' => trim((string) Arr::get($definition, 'target.seeder_class', '')),
                    'target_definition' => trim((string) Arr::get($definition, 'target.definition', '')),
                    'target_definition_path' => $this->resolveTargetDefinitionPath($definition),
                ]];
            })
            ->all();

        $this->descriptorCache = $map;

        return collect($map);
    }

    private function resolveVirtualSeederClassName(array $definition, string $path): string
    {
        $configured = trim((string) Arr::get($definition, 'seeder.class', ''));

        if ($configured !== '') {
            return $configured;
        }

        $directory = rtrim(str_replace('\\', '/', $this->localizationsDirectory()), '/');
        $normalizedPath = str_replace('\\', '/', $path);
        $relativePath = Str::after($normalizedPath, $directory . '/');
        $segments = array_values(array_filter(explode('/', $relativePath), 'strlen'));
        $fileName = array_pop($segments) ?: 'generated';
        $baseName = Str::studly(pathinfo($fileName, PATHINFO_FILENAME));
        $className = Str::endsWith($baseName, 'LocalizationSeeder')
            ? $baseName
            : $baseName . 'LocalizationSeeder';

        $namespaceSegments = array_map(
            fn (string $segment) => Str::studly(pathinfo($segment, PATHINFO_FILENAME)),
            $segments
        );

        $namespaceSegments[] = $className;

        return 'Database\\Seeders\\Page_V3\\Localizations\\' . implode('\\', $namespaceSegments);
    }

    private function localizationsDirectory(): string
    {
        return database_path('seeders/Page_V3/localizations');
    }

    private function matchesTarget(array $localizationDefinition, ?string $definitionPath, string $baseSeederClass): bool
    {
        $matched = false;
        $targetSeederClass = trim((string) Arr::get($localizationDefinition, 'target.seeder_class', ''));

        if ($targetSeederClass !== '') {
            if ($targetSeederClass !== $baseSeederClass) {
                return false;
            }

            $matched = true;
        }

        $targetDefinition = trim((string) Arr::get($localizationDefinition, 'target.definition', ''));
        $baseDefinitionKey = $this->definitionIndex->definitionKeyFromPath($definitionPath);

        if ($targetDefinition !== '') {
            if ($baseDefinitionKey === null || $targetDefinition !== $baseDefinitionKey) {
                return false;
            }

            $matched = true;
        }

        $targetDefinitionPath = $this->resolveTargetDefinitionPath($localizationDefinition);

        if ($targetDefinitionPath !== null) {
            if (! $this->pathsEqual($targetDefinitionPath, $definitionPath)) {
                return false;
            }

            $matched = true;
        }

        return $matched;
    }

    private function targetDefinitionIndex(array $localizationDefinition): array
    {
        $definitionPath = $this->resolveTargetDefinitionPath($localizationDefinition);

        if ($definitionPath === null) {
            throw new RuntimeException('Page localization definition must define target.definition or target.definition_path.');
        }

        $definition = $this->definitionIndex->loadDefinitionFromFile($definitionPath);
        $fallbackSeederClass = trim((string) Arr::get($localizationDefinition, 'target.seeder_class', ''));

        return $this->definitionIndex->indexBlocks($definition, $definitionPath, $fallbackSeederClass);
    }

    private function resolveTargetDefinitionPath(array $localizationDefinition): ?string
    {
        $configuredPath = trim((string) Arr::get($localizationDefinition, 'target.definition_path', ''));

        if ($configuredPath !== '') {
            return $this->normalizeTargetDefinitionPath($configuredPath);
        }

        $definitionKey = trim((string) Arr::get($localizationDefinition, 'target.definition', ''));

        if ($definitionKey === '') {
            return null;
        }

        if (! Str::endsWith(strtolower($definitionKey), '.json')) {
            $definitionKey .= '.json';
        }

        return database_path('seeders/Page_V3/definitions/' . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $definitionKey));
    }

    private function normalizeTargetDefinitionPath(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        $normalized = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
        $unixPath = str_replace('\\', '/', $normalized);

        if (Str::startsWith($unixPath, 'database/')) {
            return base_path($normalized);
        }

        if (Str::startsWith($unixPath, 'seeders/')) {
            return database_path(Str::after($normalized, 'seeders' . DIRECTORY_SEPARATOR));
        }

        return database_path('seeders/Page_V3/definitions/' . $normalized);
    }

    private function resolveLocalizationBlocks(array $localizationDefinition, array $baseIndex): array
    {
        $locale = $this->normalizeLocale((string) ($localizationDefinition['locale'] ?? ''));

        if ($locale === '') {
            throw new RuntimeException('Page localization definition must define a locale.');
        }

        $resolved = [];

        foreach ($localizationDefinition['blocks'] as $blockDefinition) {
            if (! is_array($blockDefinition)) {
                continue;
            }

            $indexedBlock = $this->definitionIndex->resolveIndexedBlock($baseIndex, $blockDefinition);

            if (! is_array($indexedBlock)) {
                continue;
            }

            $body = array_key_exists('body', $blockDefinition) ? $blockDefinition['body'] : null;
            $heading = $blockDefinition['heading'] ?? $indexedBlock['heading'] ?? null;
            $cssClass = $blockDefinition['css_class'] ?? $indexedBlock['css_class'] ?? null;
            $type = $blockDefinition['type'] ?? $indexedBlock['type'] ?? 'box';
            $column = $blockDefinition['column'] ?? $indexedBlock['column'] ?? 'left';
            $level = $blockDefinition['level'] ?? $indexedBlock['level'] ?? null;

            if (! is_string($body) || trim($body) === '') {
                continue;
            }

            $resolved[] = [
                'indexed_block' => $indexedBlock,
                'payload' => [
                    'locale' => $locale,
                    'body' => $body,
                    'heading' => $heading,
                    'css_class' => $cssClass,
                    'type' => $type,
                    'column' => $column,
                    'level' => $level,
                ],
            ];
        }

        return $resolved;
    }

    private function applyLocalizationDefinition(string $className, array $definition, array $baseIndex): array
    {
        $target = $this->resolveTargetEntity($baseIndex);

        if ($target === null) {
            return [
                'locale' => $this->normalizeLocale((string) ($definition['locale'] ?? '')),
                'localized_blocks' => 0,
                'content_type' => $baseIndex['content_type'] ?? 'page',
                'missing_target' => true,
            ];
        }

        $resolvedBlocks = $this->resolveLocalizationBlocks($definition, $baseIndex);
        $localizedBlocks = 0;
        $locale = $this->normalizeLocale((string) ($definition['locale'] ?? ''));
        $baseSeederClass = (string) ($baseIndex['seeder_class'] ?? '');
        $scope = $baseSeederClass . '::' . $locale;

        foreach ($resolvedBlocks as $resolvedBlock) {
            $indexedBlock = $resolvedBlock['indexed_block'];
            $payload = $resolvedBlock['payload'];
            $uuid = $this->resolveLocalizedBlockUuid($scope, $indexedBlock);

            $block = TextBlock::query()->updateOrCreate(
                ['uuid' => $uuid],
                [
                    'page_id' => $target['page_id'],
                    'page_category_id' => $target['page_category_id'],
                    'locale' => $locale,
                    'type' => $payload['type'],
                    'column' => $payload['column'],
                    'heading' => $payload['heading'],
                    'css_class' => $payload['css_class'],
                    'sort_order' => $indexedBlock['sort_order'] ?? 0,
                    'body' => $payload['body'],
                    'level' => $payload['level'],
                    'seeder' => $className,
                ]
            );

            $tagIds = $this->resolveTagIds($indexedBlock['tag_names'] ?? []);

            if ($tagIds !== []) {
                $block->tags()->sync($tagIds);
            }

            $localizedBlocks++;
        }

        return [
            'locale' => $locale,
            'localized_blocks' => $localizedBlocks,
            'content_type' => $baseIndex['content_type'] ?? 'page',
            'missing_target' => false,
        ];
    }

    private function resolveTargetEntity(array $baseIndex): ?array
    {
        $contentType = $baseIndex['content_type'] ?? 'page';

        if ($contentType === 'category') {
            $slug = trim((string) ($baseIndex['slug'] ?? $baseIndex['category_slug'] ?? ''));

            if ($slug === '') {
                return null;
            }

            $category = PageCategory::query()
                ->where('slug', $slug)
                ->first();

            if (! $category) {
                return null;
            }

            return [
                'page_id' => null,
                'page_category_id' => $category->getKey(),
            ];
        }

        $slug = trim((string) ($baseIndex['slug'] ?? ''));

        if ($slug === '') {
            return null;
        }

        $pageQuery = Page::query()->where('slug', $slug);
        $type = $baseIndex['type'] ?? null;

        if ($type !== null) {
            $pageQuery->where('type', $type);
        }

        $seederClass = trim((string) ($baseIndex['seeder_class'] ?? ''));

        if ($seederClass !== '') {
            $page = (clone $pageQuery)->where('seeder', $seederClass)->first();

            if ($page) {
                return [
                    'page_id' => $page->getKey(),
                    'page_category_id' => $page->page_category_id,
                ];
            }
        }

        $page = $pageQuery->first();

        if (! $page) {
            return null;
        }

        return [
            'page_id' => $page->getKey(),
            'page_category_id' => $page->page_category_id,
        ];
    }

    private function targetExists(array $baseIndex): bool
    {
        return $this->resolveTargetEntity($baseIndex) !== null;
    }

    private function resolveLocalizedBlockUuid(string $scope, array $indexedBlock): string
    {
        $uuidKey = trim((string) ($indexedBlock['uuid_key'] ?? ''));

        if ($uuidKey !== '') {
            return TextBlockUuidGenerator::generateWithKey($scope, $uuidKey);
        }

        return TextBlockUuidGenerator::generate($scope, (int) ($indexedBlock['sort_order'] ?? 0));
    }

    private function resolveTagIds(array $tagNames): array
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $normalized = preg_replace('/\s+/', ' ', trim((string) $tagName));

            if ($normalized === '') {
                continue;
            }

            if (isset($this->tagCache[$normalized])) {
                $tagIds[] = $this->tagCache[$normalized];

                continue;
            }

            $tag = Tag::firstOrCreate(['name' => $normalized]);
            $this->tagCache[$normalized] = $tag->id;
            $tagIds[] = $tag->id;
        }

        return array_values(array_unique($tagIds));
    }

    private function pathsEqual(?string $left, ?string $right): bool
    {
        if ($left === null || $right === null) {
            return false;
        }

        $normalizedLeft = str_replace('\\', '/', realpath($left) ?: $left);
        $normalizedRight = str_replace('\\', '/', realpath($right) ?: $right);

        return $normalizedLeft === $normalizedRight;
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^(?:[A-Za-z]:\\\\|\\\\\\\\|\/)/', $path) === 1;
    }

    private function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(trim($locale));

        if ($normalized === 'ua') {
            return 'uk';
        }

        return $normalized;
    }
}
