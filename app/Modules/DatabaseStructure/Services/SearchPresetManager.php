<?php

namespace App\Modules\DatabaseStructure\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

class SearchPresetManager
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    /**
     * @return array{items: array<int, array{id: string, name: string, query: string}>, last_used: string|null}
     */
    public function all(): array
    {
        $data = $this->readFile();

        $items = [];

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $entry) {
                $normalized = $this->normalizeEntry($entry);

                if ($normalized === null) {
                    continue;
                }

                $items[] = $normalized;
            }
        }

        $lastUsed = isset($data['last_used']) && is_string($data['last_used'])
            ? trim($data['last_used'])
            : null;

        return [
            'items' => $items,
            'last_used' => $lastUsed !== '' ? $lastUsed : null,
        ];
    }

    /**
     * @return array{items: array<int, array{id: string, name: string, query: string}>, last_used: string|null}
     */
    public function store(string $name, string $query): array
    {
        $normalizedName = $this->normalizeName($name);
        $normalizedQuery = $this->normalizeQuery($query);

        if ($normalizedQuery === '') {
            throw new RuntimeException('Необхідно вказати пошуковий запит.');
        }

        if ($normalizedName === '') {
            throw new RuntimeException('Необхідно вказати назву пошуку.');
        }

        $data = $this->readFile();

        $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

        $items = array_values(array_filter(
            $items,
            static function ($item) use ($normalizedName): bool {
                if (!is_array($item)) {
                    return false;
                }

                $name = isset($item['name']) && is_string($item['name']) ? trim($item['name']) : '';

                return $name !== '' && strcasecmp($name, $normalizedName) !== 0;
            }
        ));

        $items[] = [
            'id' => Str::uuid()->toString(),
            'name' => $normalizedName,
            'query' => $normalizedQuery,
        ];

        usort($items, static function ($a, $b): int {
            $aName = isset($a['name']) && is_string($a['name']) ? $a['name'] : '';
            $bName = isset($b['name']) && is_string($b['name']) ? $b['name'] : '';

            return strcasecmp($aName, $bName);
        });

        $data['items'] = $items;
        $data['last_used'] = end($items)['id'] ?? null;

        $this->writeFile($data);

        return $this->all();
    }

    /**
     * @return array{items: array<int, array{id: string, name: string, query: string}>, last_used: string|null}
     */
    public function markAsLastUsed(string $presetId): array
    {
        $targetId = $this->normalizeId($presetId);

        if ($targetId === '') {
            throw new RuntimeException('Не вдалося оновити пошук.');
        }

        $data = $this->readFile();
        $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

        $exists = false;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = isset($item['id']) && is_string($item['id']) ? trim($item['id']) : '';

            if ($id === $targetId) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            throw new RuntimeException('Збережений пошук не знайдено.');
        }

        $data['last_used'] = $targetId;
        $this->writeFile($data);

        return $this->all();
    }

    /**
     * @return array{items: array<int, array{id: string, name: string, query: string}>, last_used: string|null}
     */
    public function delete(string $presetId): array
    {
        $targetId = $this->normalizeId($presetId);

        if ($targetId === '') {
            throw new RuntimeException('Не вдалося видалити пошук.');
        }

        $data = $this->readFile();

        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new RuntimeException('Збережений пошук не знайдено.');
        }

        $items = $data['items'];
        $initialCount = count($items);

        $items = array_values(array_filter(
            $items,
            static function ($item) use ($targetId): bool {
                if (!is_array($item)) {
                    return false;
                }

                $id = isset($item['id']) && is_string($item['id']) ? trim($item['id']) : '';

                return $id !== '' && $id !== $targetId;
            }
        ));

        if (count($items) === $initialCount) {
            throw new RuntimeException('Збережений пошук не знайдено.');
        }

        $data['items'] = $items;

        if (isset($data['last_used']) && is_string($data['last_used'])) {
            $lastUsed = trim($data['last_used']);

            if ($lastUsed === $targetId) {
                $data['last_used'] = null;
            }
        }

        $this->writeFile($data);

        return $this->all();
    }

    private function readFile(): array
    {
        $path = $this->storagePath();

        if (!$this->filesystem->exists($path)) {
            return [
                'items' => [],
                'last_used' => null,
            ];
        }

        $raw = $this->filesystem->get($path);

        if ($raw === false || $raw === '') {
            return [
                'items' => [],
                'last_used' => null,
            ];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [
            'items' => [],
            'last_used' => null,
        ];
    }

    private function writeFile(array $data): void
    {
        $directory = dirname($this->storagePath());

        if (!$this->filesystem->isDirectory($directory)) {
            if (!$this->filesystem->makeDirectory($directory, 0775, true)) {
                throw new RuntimeException('Не вдалося створити теку для збереження пошуків.');
            }
        }

        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            throw new RuntimeException('Не вдалося підготувати дані пошуку до збереження.');
        }

        if ($this->filesystem->put($this->storagePath(), $encoded) === false) {
            throw new RuntimeException('Не вдалося зберегти пошук.');
        }
    }

    private function storagePath(): string
    {
        return dirname(__DIR__, 1) . '/storage/search-presets.json';
    }

    private function normalizeEntry(mixed $entry): ?array
    {
        if (!is_array($entry)) {
            return null;
        }

        $id = $this->normalizeId($entry['id'] ?? null);
        $name = $this->normalizeName($entry['name'] ?? null);
        $query = $this->normalizeQuery($entry['query'] ?? null);

        if ($id === '' || $name === '' || $query === '') {
            return null;
        }

        return [
            'id' => $id,
            'name' => $name,
            'query' => $query,
        ];
    }

    private function normalizeName(?string $name): string
    {
        $value = is_string($name) ? trim($name) : '';

        return Str::limit($value, 120, '');
    }

    private function normalizeQuery(?string $query): string
    {
        return is_string($query) ? trim($query) : '';
    }

    private function normalizeId(?string $id): string
    {
        return is_string($id) ? trim($id) : '';
    }
}
