<?php

namespace App\Support\Database;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class JsonPageDefinitionIndex
{
    public function loadDefinitionFromFile(string $path): array
    {
        if (! File::exists($path)) {
            throw new RuntimeException("Page V3 definition not found: {$path}");
        }

        $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid Page V3 definition: {$path}");
        }

        return $decoded;
    }

    public function definitionKeyFromPath(?string $path): ?string
    {
        $normalized = trim((string) $path);

        if ($normalized === '') {
            return null;
        }

        $realPath = realpath($normalized);
        $comparablePath = str_replace('\\', '/', $realPath !== false ? $realPath : $normalized);
        $pageDirectory = realpath(database_path('seeders/Page_V3'));

        if ($pageDirectory !== false) {
            $normalizedPageDirectory = rtrim(str_replace('\\', '/', $pageDirectory), '/');

            if ($comparablePath === $normalizedPageDirectory) {
                return null;
            }

            if (Str::startsWith($comparablePath, $normalizedPageDirectory . '/')) {
                $relativePath = Str::after($comparablePath, $normalizedPageDirectory . '/');

                return preg_replace('/\.json$/i', '', $relativePath) ?: null;
            }
        }

        return pathinfo($comparablePath, PATHINFO_FILENAME) ?: null;
    }

    public function resolveSeederClassName(array $definition, ?string $fallbackSeederClass = null): string
    {
        $configured = trim((string) Arr::get($definition, 'seeder.class', ''));

        if ($configured !== '') {
            return $configured;
        }

        return trim((string) $fallbackSeederClass);
    }

    public function resolveContentType(array $definition): string
    {
        $configured = strtolower(trim((string) Arr::get($definition, 'content_type', '')));

        if (in_array($configured, ['page', 'category'], true)) {
            return $configured;
        }

        if (is_array($definition['page'] ?? null)) {
            return 'page';
        }

        if (is_array($definition['description'] ?? null)) {
            return 'category';
        }

        return 'page';
    }

    public function resolveContentConfig(array $definition): array
    {
        $contentType = $this->resolveContentType($definition);
        $config = $contentType === 'category'
            ? ($definition['description'] ?? [])
            : ($definition['page'] ?? []);

        if ($config === []) {
            $config = $definition;
        }

        if (! is_array($config)) {
            throw new RuntimeException('Page V3 definition content must be an object.');
        }

        return $config;
    }

    public function indexBlocks(
        array $definition,
        ?string $definitionPath = null,
        ?string $fallbackSeederClass = null,
    ): array {
        $contentType = $this->resolveContentType($definition);
        $config = $this->resolveContentConfig($definition);
        $seederClass = $this->resolveSeederClassName($definition, $fallbackSeederClass);
        $definitionKey = $this->definitionKeyFromPath($definitionPath);
        $defaultLocale = $this->normalizeLocale((string) ($config['locale'] ?? 'uk'));
        $anchorTags = $this->normalizeStringList($config['tags'] ?? []);
        $baseTags = $this->normalizeStringList($config['base_tags'] ?? $anchorTags);
        $items = [];
        $byReference = [];
        $byId = [];
        $byIndex = [];
        $byUuidKey = [];

        if (! empty($config['subtitle_html'])) {
            $subtitleItem = [
                'reference' => 'subtitle',
                'id' => null,
                'index' => 0,
                'uuid_key' => trim((string) ($config['subtitle_uuid_key'] ?? 'subtitle')),
                'sort_order' => 0,
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'body' => (string) $config['subtitle_html'],
                'level' => $config['subtitle_level'] ?? null,
                'locale' => $defaultLocale,
                'tag_names' => $this->resolveBlockTagNames(
                    $baseTags,
                    $config['subtitle_tags'] ?? [],
                    $config['subtitle_inherit_base_tags'] ?? true
                ),
                'is_subtitle' => true,
            ];

            $items['subtitle'] = $subtitleItem;
            $byReference['subtitle'] = 'subtitle';
            $byIndex['0'] = 'subtitle';
            $byUuidKey[$subtitleItem['uuid_key']] = 'subtitle';
        }

        foreach (($config['blocks'] ?? []) as $arrayIndex => $block) {
            if (! is_array($block)) {
                continue;
            }

            $definitionIndex = $arrayIndex + 1;
            $reference = array_key_exists('reference', $block)
                ? trim((string) $block['reference'])
                : (array_key_exists('id', $block)
                    ? trim((string) $block['id'])
                    : (string) $definitionIndex);
            $uuidKey = trim((string) ($block['uuid_key'] ?? ''));
            $resolvedId = array_key_exists('id', $block)
                ? trim((string) $block['id'])
                : null;

            $item = [
                'reference' => $reference !== '' ? $reference : (string) $definitionIndex,
                'id' => $resolvedId !== '' ? $resolvedId : null,
                'index' => $definitionIndex,
                'uuid_key' => $uuidKey !== '' ? $uuidKey : null,
                'sort_order' => $definitionIndex,
                'type' => $block['type'] ?? 'box',
                'column' => $block['column'] ?? 'left',
                'heading' => $block['heading'] ?? null,
                'css_class' => $block['css_class'] ?? null,
                'body' => $block['body'] ?? null,
                'level' => $block['level'] ?? null,
                'locale' => $this->normalizeLocale((string) ($block['locale'] ?? $defaultLocale)),
                'tag_names' => $this->resolveBlockTagNames(
                    $baseTags,
                    $block['tags'] ?? [],
                    $block['inherit_base_tags'] ?? $block['inherit_tags'] ?? true
                ),
                'is_subtitle' => false,
            ];

            $items[$item['reference']] = $item;
            $byReference[$item['reference']] = $item['reference'];
            $byIndex[(string) $definitionIndex] = $item['reference'];

            if ($item['id'] !== null) {
                $byId[$item['id']] = $item['reference'];
            }

            if ($item['uuid_key'] !== null) {
                $byUuidKey[$item['uuid_key']] = $item['reference'];
            }
        }

        return [
            'content_type' => $contentType,
            'config' => $config,
            'items' => $items,
            'by_reference' => $byReference,
            'by_id' => $byId,
            'by_index' => $byIndex,
            'by_uuid_key' => $byUuidKey,
            'definition_key' => $definitionKey,
            'definition_path' => $definitionPath,
            'seeder_class' => $seederClass,
            'slug' => trim((string) ($definition['slug'] ?? $config['slug'] ?? '')),
            'type' => $definition['type'] ?? ($config['type'] ?? null),
            'default_locale' => $defaultLocale,
            'category_slug' => trim((string) (
                Arr::get($definition, 'category.slug', '')
                ?: Arr::get($config, 'category.slug', '')
            )),
            'category_title' => trim((string) (
                Arr::get($definition, 'category.title', '')
                ?: Arr::get($config, 'category.title', '')
            )),
        ];
    }

    public function resolveIndexedBlock(array $index, array $blockReference): ?array
    {
        $resolvedReference = $this->resolveIndexedBlockReference($index, $blockReference);

        if ($resolvedReference === null) {
            return null;
        }

        return $index['items'][$resolvedReference] ?? null;
    }

    public function resolveIndexedBlockReference(array $index, array $blockReference): ?string
    {
        $reference = trim((string) ($blockReference['reference'] ?? ''));

        if ($reference === '' && ($blockReference['subtitle'] ?? false)) {
            $reference = 'subtitle';
        }

        if ($reference !== '' && array_key_exists($reference, $index['by_reference'] ?? [])) {
            return $reference;
        }

        $uuidKey = trim((string) ($blockReference['uuid_key'] ?? ''));

        if ($uuidKey !== '' && array_key_exists($uuidKey, $index['by_uuid_key'] ?? [])) {
            return $index['by_uuid_key'][$uuidKey];
        }

        if (array_key_exists('id', $blockReference)) {
            $id = trim((string) $blockReference['id']);

            if ($id !== '' && array_key_exists($id, $index['by_id'] ?? [])) {
                return $index['by_id'][$id];
            }
        }

        if (array_key_exists('index', $blockReference)) {
            $definitionIndex = trim((string) $blockReference['index']);

            if ($definitionIndex !== '' && array_key_exists($definitionIndex, $index['by_index'] ?? [])) {
                return $index['by_index'][$definitionIndex];
            }
        }

        return null;
    }

    private function resolveBlockTagNames(array $baseTags, mixed $blockTags, mixed $inheritBaseTags): array
    {
        $resolved = ($inheritBaseTags ?? true) ? $baseTags : [];
        $resolved = array_merge($resolved, $this->normalizeStringList($blockTags));

        return array_values(array_unique($resolved));
    }

    private function normalizeStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $item) {
            $clean = trim((string) $item);

            if ($clean === '') {
                continue;
            }

            $normalized[] = preg_replace('/\s+/', ' ', $clean);
        }

        return array_values(array_unique($normalized));
    }

    private function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(trim($locale));

        if ($normalized === 'ua') {
            return 'uk';
        }

        return $normalized !== '' ? $normalized : 'uk';
    }
}
