<?php

namespace App\Modules\DatabaseStructure\Http\Controllers;

use App\Modules\DatabaseStructure\Services\ContentManagementMenuManager;
use App\Modules\DatabaseStructure\Services\DatabaseStructureFetcher;
use App\Modules\DatabaseStructure\Services\FilterStorageManager;
use App\Modules\DatabaseStructure\Services\ManualRelationManager;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DatabaseStructureController
{
    public function __construct(
        private DatabaseStructureFetcher $fetcher,
        private ManualRelationManager $manualRelationManager,
        private ContentManagementMenuManager $contentManagementMenuManager,
        private FilterStorageManager $filterStorageManager,
    )
    {
    }

    public function index(): View|ViewFactory
    {
        $structure = $this->fetcher->getStructureSummary();
        $meta = $this->fetcher->getMeta();
        $contentManagementMenu = $this->contentManagementMenuManager->getMenu();
        $contentManagementTableSettings = config('database-structure.content_management.table_settings', []);

        return view('database-structure::index', [
            'structure' => $structure,
            'meta' => $meta,
            'contentManagementMenu' => $contentManagementMenu,
            'contentManagementTableSettings' => $contentManagementTableSettings,
            'activeTab' => 'structure',
            'standaloneTab' => 'structure',
        ]);
    }

    public function contentManagement(): View|ViewFactory
    {
        $structure = $this->fetcher->getStructureSummary();
        $meta = $this->fetcher->getMeta();
        $contentManagementMenu = $this->contentManagementMenuManager->getMenu();
        $contentManagementTableSettings = config('database-structure.content_management.table_settings', []);

        return view('database-structure::index', [
            'structure' => $structure,
            'meta' => $meta,
            'contentManagementMenu' => $contentManagementMenu,
            'contentManagementTableSettings' => $contentManagementTableSettings,
            'activeTab' => 'content-management',
            'standaloneTab' => 'content-management',
        ]);
    }

    public function storeContentManagementMenu(Request $request): JsonResponse
    {
        try {
            $table = is_string($request->input('table'))
                ? trim((string) $request->input('table'))
                : '';

            $label = is_string($request->input('label'))
                ? trim((string) $request->input('label'))
                : null;

            $item = $this->contentManagementMenuManager->add($table, $label);

            return response()->json($item);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function destroyContentManagementMenu(string $table): JsonResponse
    {
        try {
            $tableName = is_string($table) ? trim($table) : '';

            $this->contentManagementMenuManager->delete($tableName);

            return response()->json([
                'deleted' => true,
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function filters(string $table, string $scope): JsonResponse
    {
        try {
            $scopeKey = $this->resolveFilterScope($scope);
            $filters = $this->filterStorageManager->getScopeFilters($table, $scopeKey);

            return response()->json([
                'filters' => $filters['items'],
                'last_used' => $filters['last_used'],
                'default' => $filters['default'],
                'default_disabled' => $filters['default_disabled'],
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function storeFilter(Request $request, string $table, string $scope): JsonResponse
    {
        try {
            $scopeKey = $this->resolveFilterScope($scope);
            $name = is_string($request->input('name')) ? trim((string) $request->input('name')) : '';
            $filters = $this->normalizeFiltersPayload($request->input('filters'));
            $search = is_string($request->input('search')) ? trim((string) $request->input('search')) : '';
            $searchColumn = is_string($request->input('search_column')) ? trim((string) $request->input('search_column')) : '';

            $result = $this->filterStorageManager->store(
                $table,
                $scopeKey,
                $name,
                $filters,
                $search,
                $searchColumn,
            );

            return response()->json([
                'filters' => $result['items'],
                'last_used' => $result['last_used'],
                'default' => $result['default'],
                'default_disabled' => $result['default_disabled'],
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function useFilter(string $table, string $scope, string $filter): JsonResponse
    {
        try {
            $scopeKey = $this->resolveFilterScope($scope);
            $filterId = is_string($filter) ? trim($filter) : '';

            $this->filterStorageManager->markAsLastUsed($table, $scopeKey, $filterId);
            $result = $this->filterStorageManager->getScopeFilters($table, $scopeKey);

            return response()->json([
                'last_used' => $result['last_used'],
                'default' => $result['default'],
                'default_disabled' => $result['default_disabled'],
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function destroyFilter(string $table, string $scope, string $filter): JsonResponse
    {
        try {
            $scopeKey = $this->resolveFilterScope($scope);
            $filterId = is_string($filter) ? trim($filter) : '';
            $result = $this->filterStorageManager->delete($table, $scopeKey, $filterId);

            return response()->json([
                'filters' => $result['items'],
                'last_used' => $result['last_used'],
                'default' => $result['default'],
                'default_disabled' => $result['default_disabled'],
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function setDefaultFilter(Request $request, string $table, string $scope): JsonResponse
    {
        try {
            $scopeKey = $this->resolveFilterScope($scope);
            $filterId = $request->input('filter_id');
            $normalized = is_string($filterId) ? trim((string) $filterId) : '';

            $result = $this->filterStorageManager->setDefault(
                $table,
                $scopeKey,
                $normalized !== '' ? $normalized : null,
            );

            return response()->json([
                'filters' => $result['items'],
                'last_used' => $result['last_used'],
                'default' => $result['default'],
                'default_disabled' => $result['default_disabled'],
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function structure(string $table): JsonResponse
    {
        try {
            $structure = $this->fetcher->getTableStructure($table);

            return response()->json($structure);
        } catch (RuntimeException $exception) {
            $status = str_contains($exception->getMessage(), 'Table') ? 404 : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function records(Request $request, string $table): JsonResponse
    {
        try {
            $page = max(1, (int) $request->query('page', 1));
            $perPage = max(1, min((int) $request->query('per_page', 20), 100));
            $sort = $request->query('sort');
            $direction = strtolower((string) $request->query('direction', 'asc'));
            $filters = $this->extractFilters($request);
            $search = $request->query('search');
            $searchColumn = $request->query('search_column');

            $relationOverrides = $this->extractRelationDisplayOverrides($request);

            $preview = $this->fetcher->getPreview(
                $table,
                $page,
                $perPage,
                $sort,
                $direction,
                $filters,
                $search,
                $searchColumn,
                $relationOverrides,
            );

            return response()->json($preview);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function record(Request $request, string $table): JsonResponse
    {
        try {
            $identifiers = $this->extractIdentifiers($request);

            if (empty($identifiers)) {
                throw new RuntimeException('Не вдалося визначити ідентифікатори запису.');
            }

            $record = $this->fetcher->getRecord($table, $identifiers);

            return response()->json($record);
        } catch (RuntimeException $exception) {
            $status = str_contains($exception->getMessage(), 'Table') ? 404 : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function value(Request $request, string $table): JsonResponse
    {
        try {
            $column = is_string($request->input('column'))
                ? trim((string) $request->input('column'))
                : '';

            if ($column === '') {
                throw new RuntimeException('Не вказано колонку для отримання значення.');
            }

            $identifiers = $this->extractIdentifiers($request);

            if (empty($identifiers)) {
                throw new RuntimeException('Не вдалося визначити ідентифікатори запису.');
            }

            $value = $this->fetcher->getRecordValue($table, $column, $identifiers);

            return response()->json([
                'value' => $value,
            ]);
        } catch (RuntimeException $exception) {
            $status = str_contains($exception->getMessage(), 'Table') ? 404 : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, string $table): JsonResponse
    {
        try {
            $column = is_string($request->input('column'))
                ? trim((string) $request->input('column'))
                : '';

            if ($column === '') {
                throw new RuntimeException('Не вказано колонку для оновлення значення.');
            }

            $payload = $request->all();

            if (!array_key_exists('value', $payload)) {
                throw new RuntimeException('Не вказано значення для збереження.');
            }

            $identifiers = $this->extractIdentifiers($request);

            if (empty($identifiers)) {
                throw new RuntimeException('Не вдалося визначити ідентифікатори запису для оновлення.');
            }

            $updatedValue = $this->fetcher->updateRecordValue($table, $column, $identifiers, $payload['value']);

            return response()->json([
                'value' => $updatedValue,
                'message' => 'Значення успішно оновлено.',
            ]);
        } catch (RuntimeException $exception) {
            $status = str_contains($exception->getMessage(), 'Table') ? 404 : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function destroy(Request $request, string $table): JsonResponse
    {
        try {
            $identifiers = $this->extractIdentifiers($request);

            if (empty($identifiers)) {
                throw new RuntimeException('Не вдалося визначити ідентифікатори запису для видалення.');
            }

            $deleted = $this->fetcher->deleteRecord($table, $identifiers);

            return response()->json([
                'deleted' => $deleted,
                'message' => 'Запис успішно видалено.',
            ]);
        } catch (RuntimeException $exception) {
            $status = str_contains($exception->getMessage(), 'Table') ? 404 : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function storeManualForeign(Request $request, string $table, string $column): JsonResponse
    {
        try {
            $tableName = is_string($table) ? trim($table) : '';
            $columnName = is_string($column) ? trim($column) : '';

            if ($tableName === '' || $columnName === '') {
                throw new RuntimeException('Не вказано таблицю або колонку для налаштування зв\'язку.');
            }

            $structure = $this->fetcher->getTableStructure($tableName);
            $columns = collect($structure['columns'] ?? []);
            $columnDefinition = $columns->firstWhere('name', $columnName);

            if (!$columnDefinition) {
                throw new RuntimeException('Колонку не знайдено у вибраній таблиці.');
            }

            if (!empty($columnDefinition['foreign']) && empty($columnDefinition['foreign']['manual'])) {
                throw new RuntimeException('Для цього поля вже існує зовнішній ключ у базі даних.');
            }

            $foreignTable = is_string($request->input('foreign_table'))
                ? trim((string) $request->input('foreign_table'))
                : '';
            $foreignColumn = is_string($request->input('foreign_column'))
                ? trim((string) $request->input('foreign_column'))
                : '';
            $displayColumn = is_string($request->input('display_column'))
                ? trim((string) $request->input('display_column'))
                : '';

            if ($foreignTable === '' || $foreignColumn === '') {
                throw new RuntimeException('Потрібно вказати таблицю та колонку, на які посилатиметься поле.');
            }

            $targetStructure = $this->fetcher->getTableStructure($foreignTable);
            $targetColumns = collect($targetStructure['columns'] ?? []);

            $targetExists = $targetColumns->contains(fn ($item) => is_array($item) && ($item['name'] ?? null) === $foreignColumn);

            if (!$targetExists) {
                throw new RuntimeException('У вибраній таблиці не знайдено колонку для зв\'язку.');
            }

            if ($displayColumn !== '') {
                $displayExists = $targetColumns->contains(fn ($item) => is_array($item) && ($item['name'] ?? null) === $displayColumn);

                if (!$displayExists) {
                    throw new RuntimeException('Колонку для відображення не знайдено у вибраній таблиці.');
                }
            }

            $this->manualRelationManager->save($tableName, $columnName, [
                'table' => $foreignTable,
                'column' => $foreignColumn,
                'display_column' => $displayColumn !== '' ? $displayColumn : null,
            ]);

            $this->fetcher->clearCache();

            $updatedStructure = $this->fetcher->getTableStructure($tableName);
            $updatedColumn = collect($updatedStructure['columns'] ?? [])->firstWhere('name', $columnName);

            return response()->json([
                'message' => 'Ручний зв\'язок успішно збережено.',
                'foreign' => $updatedColumn['foreign'] ?? null,
            ]);
        } catch (RuntimeException $exception) {
            $status = str_contains($exception->getMessage(), 'Table') ? 404 : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function destroyManualForeign(string $table, string $column): JsonResponse
    {
        try {
            $tableName = is_string($table) ? trim($table) : '';
            $columnName = is_string($column) ? trim($column) : '';

            if ($tableName === '' || $columnName === '') {
                throw new RuntimeException('Не вказано таблицю або колонку для видалення зв\'язку.');
            }

            $structure = $this->fetcher->getTableStructure($tableName);
            $columns = collect($structure['columns'] ?? []);
            $columnDefinition = $columns->firstWhere('name', $columnName);

            if (!$columnDefinition) {
                throw new RuntimeException('Колонку не знайдено у вибраній таблиці.');
            }

            if (empty($columnDefinition['foreign']) || empty($columnDefinition['foreign']['manual'])) {
                throw new RuntimeException('Для цього поля немає ручного зв\'язку.');
            }

            $this->manualRelationManager->delete($tableName, $columnName);
            $this->fetcher->clearCache();

            $updatedStructure = $this->fetcher->getTableStructure($tableName);
            $updatedColumn = collect($updatedStructure['columns'] ?? [])->firstWhere('name', $columnName);

            return response()->json([
                'message' => 'Ручний зв\'язок успішно видалено.',
                'foreign' => $updatedColumn['foreign'] ?? null,
            ]);
        } catch (RuntimeException $exception) {
            $status = str_contains($exception->getMessage(), 'Table') ? 404 : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function resolveFilterScope(string $scope): string
    {
        return $scope === 'content' ? 'content' : 'records';
    }

    /**
     * @param mixed $filters
     * @return array<int, array{column: string, operator: string, value: string}>
     */
    private function normalizeFiltersPayload(mixed $filters): array
    {
        if (!is_array($filters)) {
            return [];
        }

        $normalized = [];

        foreach ($filters as $filter) {
            if (!is_array($filter)) {
                continue;
            }

            $column = isset($filter['column']) && is_string($filter['column'])
                ? trim($filter['column'])
                : '';
            $operator = isset($filter['operator']) && is_string($filter['operator'])
                ? trim($filter['operator'])
                : '';

            if ($column === '' || $operator === '') {
                continue;
            }

            $value = '';

            if (array_key_exists('value', $filter)) {
                $raw = $filter['value'];

                if ($raw === null) {
                    $value = '';
                } elseif (is_scalar($raw)) {
                    $value = (string) $raw;
                }
            }

            $normalized[] = [
                'column' => $column,
                'operator' => $operator,
                'value' => $value,
            ];
        }

        return $normalized;
    }

    /**
     * @return array<int, array{column: string, operator: string, value?: mixed}>
     */
    private function extractFilters(Request $request): array
    {
        $filters = $request->query('filters', []);

        if (!is_array($filters)) {
            return [];
        }

        return collect($filters)
            ->filter(fn ($filter) => is_array($filter))
            ->map(function (array $filter): array {
                $column = isset($filter['column']) && is_string($filter['column'])
                    ? trim($filter['column'])
                    : '';
                $operator = isset($filter['operator']) && is_string($filter['operator'])
                    ? trim($filter['operator'])
                    : '';
                $value = $filter['value'] ?? null;

                $payload = [
                    'column' => $column,
                    'operator' => $operator,
                ];

                if (array_key_exists('value', $filter)) {
                    $payload['value'] = $value;
                }

                return $payload;
            })
            ->values()
            ->all();
    }

    private function extractRelationDisplayOverrides(Request $request): array
    {
        $overrides = $request->query('display_relations', []);

        if (!is_array($overrides)) {
            return [];
        }

        $normalized = [];

        foreach ($overrides as $column => $definition) {
            if (is_string($column)) {
                $normalizedColumn = trim($column);

                if ($normalizedColumn === '') {
                    continue;
                }

                $normalized[$normalizedColumn] = $definition;
                continue;
            }

            if (!is_array($definition)) {
                continue;
            }

            $columnName = null;

            foreach (['column', 'field', 'source', 'name'] as $key) {
                if (isset($definition[$key]) && is_string($definition[$key])) {
                    $candidate = trim($definition[$key]);

                    if ($candidate !== '') {
                        $columnName = $candidate;
                        break;
                    }
                }
            }

            if ($columnName === null) {
                continue;
            }

            $normalized[$columnName] = $definition;
        }

        return $normalized;
    }

    /**
     * @return array<int, array{column: string, value: mixed}>
     */
    private function extractIdentifiers(Request $request): array
    {
        $identifiers = $request->input('identifiers', []);

        if (!is_array($identifiers)) {
            return [];
        }

        return collect($identifiers)
            ->filter(fn ($identifier) => is_array($identifier))
            ->map(function (array $identifier): ?array {
                $column = isset($identifier['column']) && is_string($identifier['column'])
                    ? trim($identifier['column'])
                    : '';

                if ($column === '') {
                    return null;
                }

                if (!array_key_exists('value', $identifier)) {
                    return null;
                }

                $value = $identifier['value'];

                if ($value !== null && !is_scalar($value)) {
                    return null;
                }

                return [
                    'column' => $column,
                    'value' => $value,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
