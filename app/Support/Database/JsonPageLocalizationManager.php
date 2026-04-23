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

    public function targetSeederClassForVirtualSeeder(string $className): ?string
    {
        $descriptor = $this->descriptorForClass($className);

        if (! is_array($descriptor)) {
            return null;
        }

        $configured = trim((string) ($descriptor['target_seeder_class'] ?? ''));

        if ($configured !== '') {
            return $configured;
        }

        try {
            $definition = $this->loadVirtualSeederDefinition($className);
            $baseIndex = $this->targetDefinitionIndex($definition, $descriptor['path'] ?? null);
            $resolved = trim((string) ($baseIndex['seeder_class'] ?? ''));

            return $resolved !== '' ? $resolved : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function flushDescriptorCache(): void
    {
        $this->descriptorCache = null;
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
        $descriptor = $this->descriptorForClass($className);
        $definition = $this->loadVirtualSeederDefinition($className);
        $baseIndex = $this->targetDefinitionIndex($definition, $descriptor['path'] ?? null);
        $result = $this->applyLocalizationDefinition($className, $definition, $baseIndex);

        if (($result['localized_blocks'] ?? 0) === 0) {
            throw new RuntimeException($this->buildMissingTargetMessage($definition, $baseIndex, $descriptor['path'] ?? null));
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
        $descriptor = $this->descriptorForClass($className);
        $definition = $this->loadVirtualSeederDefinition($className);
        $baseIndex = $this->targetDefinitionIndex($definition, $descriptor['path'] ?? null);
        $resolvedBlocks = $this->resolveLocalizationBlocks($definition, $baseIndex);
        $targetExists = $this->targetExists($baseIndex);
        $contentType = (string) ($baseIndex['content_type'] ?? 'page');
        $target = [
            'seeder_class' => trim((string) Arr::get($definition, 'target.seeder_class', '')),
            'definition' => $baseIndex['definition_key'] ?? trim((string) Arr::get($definition, 'target.definition', '')),
            'definition_path' => $baseIndex['definition_path']
                ?? $this->resolveTargetDefinitionPath($definition, $descriptor['path'] ?? null),
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
                $localizationPath = (string) ($descriptor['path'] ?? '');
                $localizationDefinition = $this->loadLocalizationDefinition($localizationPath);

                return $this->matchesTarget(
                    $localizationDefinition,
                    $localizationPath,
                    $definitionPath,
                    $baseSeederClass
                );
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

        $directory = $this->discoveryDirectory();

        if (! File::isDirectory($directory)) {
            $this->descriptorCache = [];

            return collect();
        }

        $map = collect(File::allFiles($directory))
            ->filter(fn (SplFileInfo $file) => strtolower($file->getExtension()) === 'json')
            ->filter(fn (SplFileInfo $file) => $this->isLocalizationFilePath($file->getPathname()))
            ->mapWithKeys(function (SplFileInfo $file) {
                $definition = $this->loadLocalizationDefinition($file->getPathname());
                $className = $this->resolveVirtualSeederClassName($definition, $file->getPathname());

                return [$className => [
                    'class_name' => $className,
                    'path' => $file->getPathname(),
                    'locale' => $this->normalizeLocale((string) ($definition['locale'] ?? '')),
                    'target_seeder_class' => trim((string) Arr::get($definition, 'target.seeder_class', '')),
                    'target_definition' => trim((string) Arr::get($definition, 'target.definition', '')),
                    'target_definition_path' => $this->resolveTargetDefinitionPath(
                        $definition,
                        $file->getPathname()
                    ),
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

        $normalizedPath = str_replace('\\', '/', $path);
        $parentDirectory = basename(dirname($normalizedPath));
        $grandParentDirectory = basename(dirname(dirname($normalizedPath)));
        $baseName = Str::studly(pathinfo($normalizedPath, PATHINFO_FILENAME));

        if (
            strtolower($parentDirectory) === 'localizations'
            && in_array(strtolower($baseName), ['en', 'pl', 'uk'], true)
            && Str::endsWith($grandParentDirectory, 'Seeder')
        ) {
            $localeNamespace = Str::ucfirst(strtolower($baseName));
            $classStem = preg_replace('/Seeder$/', '', $grandParentDirectory) ?: $grandParentDirectory;

            return 'Database\\Seeders\\Page_V3\\Localizations\\'
                . $localeNamespace . '\\' . $classStem . 'LocalizationSeeder';
        }

        $directory = rtrim(str_replace('\\', '/', $this->discoveryDirectory()), '/');
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

    private function discoveryDirectory(): string
    {
        return database_path('seeders/Page_V3');
    }

    private function isLocalizationFilePath(string $path): bool
    {
        return Str::contains(str_replace('\\', '/', $path), '/localizations/');
    }

    private function matchesTarget(
        array $localizationDefinition,
        ?string $localizationPath,
        ?string $definitionPath,
        string $baseSeederClass
    ): bool
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

        $targetDefinitionPath = $this->resolveTargetDefinitionPath($localizationDefinition, $localizationPath);

        if ($targetDefinitionPath !== null) {
            if (! $this->pathsEqual($targetDefinitionPath, $definitionPath)) {
                return false;
            }

            $matched = true;
        }

        return $matched;
    }

    private function targetDefinitionIndex(array $localizationDefinition, ?string $localizationPath = null): array
    {
        $definitionPath = $this->resolveTargetDefinitionPath($localizationDefinition, $localizationPath);

        if ($definitionPath === null) {
            throw new RuntimeException('Page localization definition must define target.definition or target.definition_path.');
        }

        $definition = $this->definitionIndex->loadDefinitionFromFile($definitionPath);
        $fallbackSeederClass = trim((string) Arr::get($localizationDefinition, 'target.seeder_class', ''));

        return $this->definitionIndex->indexBlocks($definition, $definitionPath, $fallbackSeederClass);
    }

    private function resolveTargetDefinitionPath(array $localizationDefinition, ?string $localizationPath = null): ?string
    {
        $configuredPath = trim((string) Arr::get($localizationDefinition, 'target.definition_path', ''));

        if ($configuredPath !== '') {
            return $this->normalizeTargetDefinitionPath($configuredPath, $localizationPath);
        }

        $adjacentDefinitionPath = $this->inferAdjacentDefinitionPath($localizationPath);

        if ($adjacentDefinitionPath !== null) {
            return $adjacentDefinitionPath;
        }

        $targetSeederClass = trim((string) Arr::get($localizationDefinition, 'target.seeder_class', ''));

        if ($targetSeederClass !== '') {
            $resolvedDefinitionPath = $this->resolveDefinitionPathFromSeederClass($targetSeederClass);

            if ($resolvedDefinitionPath !== null) {
                return $resolvedDefinitionPath;
            }
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

    private function normalizeTargetDefinitionPath(string $path, ?string $localizationPath = null): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        $normalized = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);

        if ($localizationPath !== null) {
            $relativeCandidate = dirname($localizationPath) . DIRECTORY_SEPARATOR . $normalized;
            $resolvedRelativeCandidate = realpath($relativeCandidate);

            if ($resolvedRelativeCandidate !== false) {
                return $resolvedRelativeCandidate;
            }
        }

        $unixPath = str_replace('\\', '/', $normalized);

        if (Str::startsWith($unixPath, 'database/')) {
            return base_path($normalized);
        }

        if (Str::startsWith($unixPath, 'seeders/')) {
            return database_path(Str::after($normalized, 'seeders' . DIRECTORY_SEPARATOR));
        }

        return database_path('seeders/Page_V3/definitions/' . $normalized);
    }

    private function inferAdjacentDefinitionPath(?string $localizationPath): ?string
    {
        $normalizedPath = trim((string) $localizationPath);

        if ($normalizedPath === '') {
            return null;
        }

        $localizationsDirectory = dirname($normalizedPath);

        if (Str::lower(basename($localizationsDirectory)) !== 'localizations') {
            return null;
        }

        $definitionPath = dirname($localizationsDirectory) . DIRECTORY_SEPARATOR . 'definition.json';

        if (File::exists($definitionPath)) {
            return $definitionPath;
        }

        return null;
    }

    private function resolveDefinitionPathFromSeederClass(string $className): ?string
    {
        if (! $this->classExistsSafely($className)) {
            return null;
        }

        try {
            $instance = app($className);

            if (method_exists($instance, 'resolvedDefinitionPath')) {
                $path = $instance->resolvedDefinitionPath();

                return is_string($path) && trim($path) !== '' ? $path : null;
            }

            $method = new \ReflectionMethod($instance, 'definitionPath');
            $method->setAccessible(true);
            $path = $method->invoke($instance);

            return is_string($path) && trim($path) !== '' ? $path : null;
        } catch (\Throwable) {
            return null;
        }
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

    private function buildMissingTargetMessage(array $definition, array $baseIndex, ?string $localizationPath = null): string
    {
        $details = [];
        $locale = $this->normalizeLocale((string) ($definition['locale'] ?? ''));
        $target = $this->formatTargetDescriptor($baseIndex);
        $targetSeederClass = trim((string) ($baseIndex['seeder_class'] ?? Arr::get($definition, 'target.seeder_class', '')));
        $definitionKey = trim((string) ($baseIndex['definition_key'] ?? ''));
        $definitionPath = $this->shortPath($baseIndex['definition_path'] ?? null);
        $sourcePath = $this->shortPath($localizationPath);

        if ($locale !== '') {
            $details[] = 'locale: ' . $locale;
        }

        if ($target !== '') {
            $details[] = 'target: ' . $target;
        }

        if ($targetSeederClass !== '') {
            $details[] = 'seeder: ' . $targetSeederClass;
        }

        if ($definitionKey !== '') {
            $details[] = 'definition: ' . $definitionKey;
        } elseif ($definitionPath !== '') {
            $details[] = 'definition: ' . $definitionPath;
        }

        if ($sourcePath !== '') {
            $details[] = 'source: ' . $sourcePath;
        }

        return 'Локалізацію не застосовано: не знайдено цільову сторінку або категорію'
            . ($details !== [] ? ' (' . implode('; ', $details) . ')' : '')
            . '.';
    }

    private function formatTargetDescriptor(array $baseIndex): string
    {
        $contentType = (string) ($baseIndex['content_type'] ?? 'page');

        if ($contentType === 'category') {
            $slug = trim((string) ($baseIndex['category_slug'] ?? $baseIndex['slug'] ?? ''));

            return $slug !== '' ? 'category slug "' . $slug . '"' : 'category';
        }

        $details = [];
        $slug = trim((string) ($baseIndex['slug'] ?? ''));
        $type = trim((string) ($baseIndex['type'] ?? ''));

        $details[] = $slug !== '' ? 'page slug "' . $slug . '"' : 'page';

        if ($type !== '') {
            $details[] = 'type ' . $type;
        }

        return implode(', ', $details);
    }

    private function shortPath(?string $path): string
    {
        $normalized = trim(str_replace('\\', '/', (string) $path));

        if ($normalized === '') {
            return '';
        }

        $base = str_replace('\\', '/', base_path());

        if (Str::startsWith($normalized, $base . '/')) {
            return Str::after($normalized, $base . '/');
        }

        return $normalized;
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
        $leftPath = $this->normalizeComparablePath($left);
        $rightPath = $this->normalizeComparablePath($right);

        return $leftPath !== null && $rightPath !== null && $leftPath === $rightPath;
    }

    private function normalizeComparablePath(?string $path): ?string
    {
        $normalized = trim((string) $path);

        if ($normalized === '') {
            return null;
        }

        $realPath = realpath($normalized);

        if ($realPath !== false) {
            return str_replace('\\', '/', $realPath);
        }

        return str_replace('\\', '/', $normalized);
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^(?:[A-Za-z]:[\/\\\\]|\/)/', $path) === 1;
    }

    private function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(trim($locale));

        if ($normalized === 'ua') {
            return 'uk';
        }

        return $normalized;
    }

    private function classExistsSafely(string $className): bool
    {
        if (class_exists($className, false)) {
            return true;
        }

        return @class_exists($className);
    }
}
