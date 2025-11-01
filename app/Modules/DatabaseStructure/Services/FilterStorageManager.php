<?php

namespace App\Modules\DatabaseStructure\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

class FilterStorageManager
{
    private const SCOPE_RECORDS = 'records';
    private const SCOPE_CONTENT = 'content';

    public function __construct(private Filesystem $filesystem)
    {
    }

    /**
     * @return array{items: array<int, array{id: string, name: string, filters: array<int, array{column: string, operator: string, value: string}>, search: string, search_column: string}>, last_used: string|null}
     */
    public function getScopeFilters(string $table, string $scope): array
    {
        $tableName = $this->normalizeTable($table);
        $scopeKey = $this->normalizeScope($scope);

        if ($tableName === '') {
            return $this->emptyScope();
        }

        $data = $this->readTableFile($tableName);
        $scopeData = $data[$scopeKey] ?? [];

        if (!is_array($scopeData)) {
            return $this->emptyScope();
        }

        $lastUsed = isset($scopeData['last_used']) && is_string($scopeData['last_used'])
            ? trim($scopeData['last_used'])
            : null;

        $items = [];

        if (isset($scopeData['items']) && is_array($scopeData['items'])) {
            foreach ($scopeData['items'] as $entry) {
                $normalized = $this->normalizeFilterEntry($entry);

                if ($normalized === null) {
                    continue;
                }

                $items[] = $normalized;
            }
        }

        return [
            'items' => $items,
            'last_used' => $lastUsed !== '' ? $lastUsed : null,
        ];
    }

    /**
     * @param array<int, array{column: string, operator: string, value: string}> $filters
     *
     * @return array{items: array<int, array{id: string, name: string, filters: array<int, array{column: string, operator: string, value: string}>, search: string, search_column: string}>, last_used: string|null}
     */
    public function store(string $table, string $scope, string $name, array $filters, string $search, string $searchColumn): array
    {
        $tableName = $this->normalizeTable($table);
        $scopeKey = $this->normalizeScope($scope);
        $filterName = $this->normalizeName($name);

        if ($tableName === '') {
            throw new RuntimeException('Не вказано таблицю для збереження фільтру.');
        }

        if ($filterName === '') {
            throw new RuntimeException('Необхідно вказати назву фільтру.');
        }

        $normalizedFilters = $this->normalizeFiltersList($filters);
        $normalizedSearch = $this->normalizeSearch($search);
        $normalizedSearchColumn = $this->normalizeColumn($searchColumn);

        if ($normalizedFilters === [] && $normalizedSearch === '' && $normalizedSearchColumn === '') {
            throw new RuntimeException('Немає даних для збереження фільтру.');
        }

        $data = $this->readTableFile($tableName);

        if (!isset($data[$scopeKey]) || !is_array($data[$scopeKey])) {
            $data[$scopeKey] = $this->emptyScope();
        }

        $scopeData = $data[$scopeKey];

        $items = $scopeData['items'] ?? [];

        if (!is_array($items)) {
            $items = [];
        }

        $items = array_values(array_filter(
            $items,
            static function ($item) use ($filterName): bool {
                if (!is_array($item)) {
                    return false;
                }

                $name = isset($item['name']) && is_string($item['name']) ? trim($item['name']) : '';

                return strcasecmp($name, $filterName) !== 0;
            }
        ));

        $filterId = Str::uuid()->toString();

        $items[] = [
            'id' => $filterId,
            'name' => $filterName,
            'filters' => $normalizedFilters,
            'search' => $normalizedSearch,
            'search_column' => $normalizedSearchColumn,
        ];

        usort($items, static function ($a, $b): int {
            $aName = isset($a['name']) && is_string($a['name']) ? $a['name'] : '';
            $bName = isset($b['name']) && is_string($b['name']) ? $b['name'] : '';

            return strcasecmp($aName, $bName);
        });

        $scopeData['items'] = $items;
        $scopeData['last_used'] = $filterId;

        $data[$scopeKey] = $scopeData;

        $this->writeTableFile($tableName, $data);

        return $this->getScopeFilters($tableName, $scopeKey);
    }

    /**
     * @return array{items: array<int, array{id: string, name: string, filters: array<int, array{column: string, operator: string, value: string}>, search: string, search_column: string}>, last_used: string|null}
     */
    public function delete(string $table, string $scope, string $filterId): array
    {
        $tableName = $this->normalizeTable($table);
        $scopeKey = $this->normalizeScope($scope);
        $targetId = $this->normalizeId($filterId);

        if ($tableName === '' || $targetId === '') {
            throw new RuntimeException('Не вдалося видалити фільтр.');
        }

        $data = $this->readTableFile($tableName);

        if (!isset($data[$scopeKey]) || !is_array($data[$scopeKey])) {
            throw new RuntimeException('Фільтр не знайдено.');
        }

        $scopeData = $data[$scopeKey];
        $items = $scopeData['items'] ?? [];

        if (!is_array($items)) {
            throw new RuntimeException('Фільтр не знайдено.');
        }

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
            throw new RuntimeException('Фільтр не знайдено.');
        }

        $lastUsed = isset($scopeData['last_used']) && is_string($scopeData['last_used'])
            ? trim($scopeData['last_used'])
            : '';

        if ($lastUsed === $targetId) {
            $scopeData['last_used'] = null;
        }

        $scopeData['items'] = $items;
        $data[$scopeKey] = $scopeData;

        $this->writeTableFile($tableName, $data);

        return $this->getScopeFilters($tableName, $scopeKey);
    }

    public function markAsLastUsed(string $table, string $scope, string $filterId): void
    {
        $tableName = $this->normalizeTable($table);
        $scopeKey = $this->normalizeScope($scope);
        $targetId = $this->normalizeId($filterId);

        if ($tableName === '' || $targetId === '') {
            throw new RuntimeException('Не вдалося оновити фільтр.');
        }

        $data = $this->readTableFile($tableName);

        if (!isset($data[$scopeKey]) || !is_array($data[$scopeKey])) {
            throw new RuntimeException('Фільтр не знайдено.');
        }

        $scopeData = $data[$scopeKey];
        $items = $scopeData['items'] ?? [];

        if (!is_array($items)) {
            throw new RuntimeException('Фільтр не знайдено.');
        }

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
            throw new RuntimeException('Фільтр не знайдено.');
        }

        $scopeData['last_used'] = $targetId;
        $data[$scopeKey] = $scopeData;

        $this->writeTableFile($tableName, $data);
    }

    private function readTableFile(string $table): array
    {
        $path = $this->tablePath($table);

        if (!$this->filesystem->exists($path)) {
            return [];
        }

        $raw = $this->filesystem->get($path);

        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function writeTableFile(string $table, array $data): void
    {
        $path = $this->tablePath($table);
        $directory = dirname($path);

        if (!$this->filesystem->isDirectory($directory)) {
            if (!$this->filesystem->makeDirectory($directory, 0775, true)) {
                throw new RuntimeException('Не вдалося створити теку для збереження фільтрів.');
            }
        }

        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            throw new RuntimeException('Не вдалося підготувати дані фільтрів до збереження.');
        }

        if ($this->filesystem->put($path, $encoded) === false) {
            throw new RuntimeException('Не вдалося зберегти фільтр.');
        }
    }

    private function tablePath(string $table): string
    {
        return $this->baseDirectory() . '/' . $table . '.json';
    }

    private function baseDirectory(): string
    {
        return dirname(__DIR__, 1) . '/storage/filters';
    }

    private function normalizeScope(string $scope): string
    {
        return $scope === self::SCOPE_CONTENT ? self::SCOPE_CONTENT : self::SCOPE_RECORDS;
    }

    private function normalizeTable(?string $table): string
    {
        return $this->normalizeKey($table);
    }

    private function normalizeName(?string $name): string
    {
        $value = is_string($name) ? trim($name) : '';

        return Str::limit($value, 120, '');
    }

    private function normalizeSearch(?string $search): string
    {
        return is_string($search) ? trim($search) : '';
    }

    private function normalizeColumn(?string $column): string
    {
        $value = is_string($column) ? trim($column) : '';

        return $value === '' ? '' : Str::limit($value, 120, '');
    }

    private function normalizeId(?string $id): string
    {
        return $this->normalizeKey($id);
    }

    private function normalizeKey(?string $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    /**
     * @param array<int, array{column: string, operator: string, value: string}> $filters
     * @return array<int, array{column: string, operator: string, value: string}>
     */
    private function normalizeFiltersList(array $filters): array
    {
        $normalized = [];

        foreach ($filters as $filter) {
            if (!is_array($filter)) {
                continue;
            }

            $column = $this->normalizeColumn($filter['column'] ?? null);
            $operator = $this->normalizeOperator($filter['operator'] ?? null);
            $value = $this->normalizeValue($filter['value'] ?? null);

            if ($column === '' || $operator === '') {
                continue;
            }

            $normalized[] = [
                'column' => $column,
                'operator' => $operator,
                'value' => $value,
            ];
        }

        return $normalized;
    }

    private function normalizeOperator(?string $operator): string
    {
        $value = is_string($operator) ? trim($operator) : '';

        if ($value === '') {
            return '';
        }

        $allowed = ['=', '!=', '<', '<=', '>', '>=', 'like', 'not like'];

        foreach ($allowed as $item) {
            if (strcasecmp($item, $value) === 0) {
                return $item;
            }
        }

        return $value;
    }

    private function normalizeValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_scalar($value)) {
            return Str::limit((string) $value, 500, '');
        }

        return '';
    }

    private function emptyScope(): array
    {
        return [
            'items' => [],
            'last_used' => null,
        ];
    }

    private function normalizeFilterEntry(mixed $entry): ?array
    {
        if (!is_array($entry)) {
            return null;
        }

        $id = $this->normalizeId($entry['id'] ?? null);
        $name = $this->normalizeName($entry['name'] ?? null);

        if ($id === '' || $name === '') {
            return null;
        }

        $filters = $this->normalizeFiltersList(isset($entry['filters']) && is_array($entry['filters']) ? $entry['filters'] : []);
        $search = $this->normalizeSearch($entry['search'] ?? null);
        $searchColumn = $this->normalizeColumn($entry['search_column'] ?? null);

        return [
            'id' => $id,
            'name' => $name,
            'filters' => $filters,
            'search' => $search,
            'search_column' => $searchColumn,
        ];
    }
}
