<?php

namespace App\Modules\DatabaseStructure\Services;

use DateTimeInterface;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseStructureFetcher
{
    private Connection $connection;
    private ?array $structureCache = null;
    private ?array $structureSummaryCache = null;
    private ?array $manualRelationsCache = null;

    public function __construct()
    {
        $connectionName = config('database-structure.connection');
        $this->connection = $connectionName
            ? DB::connection($connectionName)
            : DB::connection();
    }

    public function clearCache(): void
    {
        $this->structureCache = null;
        $this->structureSummaryCache = null;
        $this->manualRelationsCache = null;
    }

    public function getStructure(): array
    {
        if ($this->structureCache !== null) {
            return $this->structureCache;
        }

        $driver = $this->connection->getDriverName();

        $structure = match ($driver) {
            'mysql' => $this->structureFromMySql(),
            'pgsql' => $this->structureFromPostgres(),
            'sqlite' => $this->structureFromSqlite(),
            'sqlsrv' => $this->structureFromSqlServer(),
            default => throw new RuntimeException("Database driver '{$driver}' is not supported."),
        };

        $structureWithManualRelations = $this->applyManualForeignDefinitions($structure);

        return $this->structureCache = $structureWithManualRelations;
    }

    public function getStructureSummary(): array
    {
        if ($this->structureSummaryCache !== null) {
            return $this->structureSummaryCache;
        }

        return $this->structureSummaryCache = array_map(
            static function (array $table): array {
                $columns = $table['columns'] ?? [];

                return [
                    'name' => $table['name'] ?? null,
                    'comment' => $table['comment'] ?? null,
                    'engine' => $table['engine'] ?? null,
                    'columns_count' => is_array($columns) ? count($columns) : 0,
                ];
            },
            $this->getStructure()
        );
    }

    public function getTableStructure(string $table): array
    {
        $structure = Collection::make($this->getStructure());
        $tableInfo = $structure->firstWhere('name', $table);

        if (!$tableInfo) {
            throw new RuntimeException("Table '{$table}' was not found in the current connection.");
        }

        $columns = Collection::make($tableInfo['columns'] ?? [])
            ->map(function ($column): ?array {
                if (is_array($column)) {
                    return [
                        'name' => $column['name'] ?? null,
                        'type' => $column['type'] ?? null,
                        'nullable' => (bool) ($column['nullable'] ?? false),
                        'default' => $column['default'] ?? null,
                        'key' => $column['key'] ?? null,
                        'extra' => $column['extra'] ?? null,
                        'comment' => $column['comment'] ?? null,
                        'foreign' => $this->normalizeForeignDefinition($column['foreign'] ?? null),
                    ];
                }

                return null;
            })
            ->filter(fn ($column) => $column !== null && is_string($column['name'] ?? null) && $column['name'] !== '')
            ->values()
            ->all();

        return [
            'name' => $tableInfo['name'] ?? $table,
            'comment' => $tableInfo['comment'] ?? null,
            'engine' => $tableInfo['engine'] ?? null,
            'columns' => $columns,
            'columns_count' => count($columns),
        ];
    }

    public function getMeta(): array
    {
        $structure = Collection::make($this->getStructureSummary());

        return [
            'connection' => $this->connection->getName(),
            'driver' => $this->connection->getDriverName(),
            'database' => $this->connection->getDatabaseName(),
            'tables_count' => $structure->count(),
            'columns_count' => $structure->sum(fn ($table) => (int) ($table['columns_count'] ?? 0)),
        ];
    }

    public function getPreview(
        string $table,
        int $page = 1,
        int $perPage = 20,
        ?string $sort = null,
        string $direction = 'asc',
        array $filters = [],
        ?string $search = null,
        ?string $searchColumn = null
    ): array
    {
        $structure = Collection::make($this->getStructure());
        $tableInfo = $structure->firstWhere('name', $table);

        if (!$tableInfo) {
            throw new RuntimeException("Table '{$table}' was not found in the current connection.");
        }

        $columns = $tableInfo['columns'] ?? [];
        $columnNames = array_map(static fn ($column) => $column['name'], $columns);
        $foreignColumns = $this->extractForeignColumns($columns);
        $relationOverrides = $this->getContentManagementRelationOverrides($table);
        $displayOverrides = $this->prepareContentManagementDisplayOverrides($foreignColumns, $relationOverrides);
        $normalizedFilters = $this->prepareFilters($filters, $columnNames);
        $searchTerm = is_string($search) ? trim($search) : '';
        $normalizedSearchColumn = is_string($searchColumn) ? trim($searchColumn) : '';

        $validSort = null;
        if ($sort && in_array($sort, $columnNames, true)) {
            $validSort = $sort;
        } elseif ($sort && empty($columnNames) && preg_match('/^[A-Za-z0-9_]+$/', $sort)) {
            $validSort = $sort;
        }

        $baseSelectColumns = $this->buildBaseSelectColumns($table, $columnNames);
        $displaySelectColumns = $this->buildDisplaySelectColumns($displayOverrides);
        $selectColumns = array_merge($baseSelectColumns, $displaySelectColumns);

        if (!empty($selectColumns)) {
            $query->select($selectColumns);
        }

        foreach ($displayOverrides as $override) {
            $joinTable = $override['foreign_table'] . ' as ' . $override['join_alias'];

            $query->leftJoin($joinTable, function (JoinClause $join) use ($override, $table): void {
                $join->on(
                    $override['join_alias'] . '.' . $override['foreign_column'],
                    '=',
                    $table . '.' . $override['column_name']
                );
            });

            $countQuery->leftJoin($joinTable, function (JoinClause $join) use ($override, $table): void {
                $join->on(
                    $override['join_alias'] . '.' . $override['foreign_column'],
                    '=',
                    $table . '.' . $override['column_name']
                );
            });
        }

        $searchColumnsByField = $this->buildSearchColumns($columnNames, $displayOverrides);

        if ($normalizedSearchColumn !== '') {
            $searchColumns = $searchColumnsByField[$normalizedSearchColumn] ?? [$normalizedSearchColumn];
        } else {
            $searchColumns = [];

            foreach ($searchColumnsByField as $columnsForSearch) {
                foreach ($columnsForSearch as $searchColumn) {
                    $searchColumns[] = $searchColumn;
                }
            }

            if (empty($searchColumns)) {
                $searchColumns = $columnNames;
            }
        }

        $searchColumns = array_values(array_unique(array_filter(
            $searchColumns,
            static fn ($column) => is_string($column) && $column !== ''
        )));

        if ($normalizedSearchColumn !== '') {
            $isKnownColumn = in_array($normalizedSearchColumn, $columnNames, true);
            $isAdHocColumn = empty($columnNames) && preg_match('/^[A-Za-z0-9_]+$/', $normalizedSearchColumn);

            if ($isKnownColumn || $isAdHocColumn) {
                $searchColumns = $searchColumns ?: [$normalizedSearchColumn];
            } else {
                $normalizedSearchColumn = '';
            }
        }

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        $query = $this->connection->table($table);
        $countQuery = $this->connection->table($table);

        $this->applyFilters($query, $normalizedFilters);
        $this->applyFilters($countQuery, $normalizedFilters);

        if ($searchTerm !== '') {
            $this->applySearch($query, $searchColumns, $searchTerm);
            $this->applySearch($countQuery, $searchColumns, $searchTerm);
        }

        $sortColumnForQuery = $validSort;

        if ($validSort && isset($displayOverrides[$validSort])) {
            $override = $displayOverrides[$validSort];
            $sortColumnForQuery = $override['join_alias'] . '.' . $override['display_column'];
        }

        if ($validSort && $sortColumnForQuery) {
            $query->orderBy($sortColumnForQuery, $direction);
        }

        $total = (int) $countQuery->count();
        $page = max($page, 1);
        $perPage = max($perPage, 1);
        $offset = ($page - 1) * $perPage;

        if ($offset >= $total && $total > 0) {
            $page = (int) ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
        }

        $displayAliasMap = [];

        foreach ($displayOverrides as $columnName => $override) {
            $displayAliasMap[$override['select_alias']] = $columnName;
        }

        $rows = $query
            ->skip($offset)
            ->take($perPage)
            ->get()
            ->map(function ($row) use ($displayAliasMap) {
                $array = (array) $row;
                $display = [];
                $result = [];

                foreach ($array as $key => $value) {
                    if (isset($displayAliasMap[$key])) {
                        $column = $displayAliasMap[$key];
                        $display[$column] = $this->convertValueForResponse($value);
                        continue;
                    }

                    $result[$key] = $this->convertValueForResponse($value);
                }

                if (!empty($display)) {
                    $result['__display'] = $display;
                }

                return $result;
            })->all();

        if (empty($columnNames) && !empty($rows)) {
            $columnNames = array_values(
                array_filter(
                    array_keys($rows[0]),
                    static fn ($name) => $name !== '__display'
                )
            );
        }

        $lastPage = $perPage > 0 ? (int) max(1, ceil($total / $perPage)) : 1;

        return [
            'table' => $table,
            'columns' => $columnNames,
            'rows' => $rows,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'sort' => $validSort,
            'direction' => $validSort ? $direction : null,
            'filters' => $normalizedFilters,
            'search' => $searchTerm,
            'search_column' => $normalizedSearchColumn,
        ];
    }

    /**
     * @param array<int, array{column: string, value: mixed}> $identifiers
     */
    public function deleteRecord(string $table, array $identifiers): int
    {
        $structure = Collection::make($this->getStructure());
        $tableInfo = $structure->firstWhere('name', $table);

        if (!$tableInfo) {
            throw new RuntimeException("Table '{$table}' was not found in the current connection.");
        }

        $columns = $tableInfo['columns'] ?? [];
        $columnNames = array_map(static fn ($column) => $column['name'], $columns);

        $normalizedIdentifiers = $this->normalizeIdentifiers($identifiers, $columnNames);

        if (empty($normalizedIdentifiers)) {
            throw new RuntimeException('Не вдалося визначити ідентифікатори запису для видалення.');
        }

        $query = $this->connection->table($table);

        foreach ($normalizedIdentifiers as $identifier) {
            if ($identifier['value'] === null) {
                $query->whereNull($identifier['column']);
                continue;
            }

            $query->where($identifier['column'], '=', $identifier['value']);
        }

        $deleted = $query->delete();

        if ($deleted === 0) {
            throw new RuntimeException('Запис не знайдено або не вдалося видалити.');
        }

        return $deleted;
    }

    /**
     * @param array<int, array{column: string, value: mixed}> $identifiers
     */
    public function updateRecordValue(string $table, string $column, array $identifiers, mixed $value): mixed
    {
        $structure = Collection::make($this->getStructure());
        $tableInfo = $structure->firstWhere('name', $table);

        if (!$tableInfo) {
            throw new RuntimeException("Table '{$table}' was not found in the current connection.");
        }

        $columns = $tableInfo['columns'] ?? [];
        $columnNames = array_map(static fn ($tableColumn) => $tableColumn['name'], $columns);
        $normalizedColumn = trim($column);

        $isKnownColumn = in_array($normalizedColumn, $columnNames, true);
        $isAdHocColumn = empty($columnNames) && preg_match('/^[A-Za-z0-9_]+$/', $normalizedColumn);

        if (!$isKnownColumn && !$isAdHocColumn) {
            throw new RuntimeException("Колонку '{$normalizedColumn}' не знайдено у таблиці.");
        }

        $normalizedIdentifiers = $this->normalizeIdentifiers($identifiers, $columnNames);

        if (empty($normalizedIdentifiers)) {
            throw new RuntimeException('Не вдалося визначити ідентифікатори запису для оновлення.');
        }

        $query = $this->connection->table($table);

        foreach ($normalizedIdentifiers as $identifier) {
            if ($identifier['value'] === null) {
                $query->whereNull($identifier['column']);
                continue;
            }

            $query->where($identifier['column'], '=', $identifier['value']);
        }

        $updated = $query->update([$normalizedColumn => $value]);

        if ($updated === 0) {
            throw new RuntimeException('Запис не знайдено або не вдалося оновити.');
        }

        $identifiersForFetch = array_map(
            static function (array $identifier) use ($normalizedColumn, $value): array {
                if ($identifier['column'] === $normalizedColumn) {
                    return [
                        'column' => $identifier['column'],
                        'value' => $value,
                    ];
                }

                return $identifier;
            },
            $normalizedIdentifiers,
        );

        try {
            return $this->getRecordValue($table, $normalizedColumn, $identifiersForFetch);
        } catch (RuntimeException) {
            return $this->convertValueForResponse($value);
        }
    }

    /**
     * @param array<int, array{column: string, value: mixed}> $identifiers
     * @return array{record: array<string, mixed>, columns: array<int, string>}
     */
    public function getRecord(string $table, array $identifiers): array
    {
        $structure = Collection::make($this->getStructure());
        $tableInfo = $structure->firstWhere('name', $table);

        if (!$tableInfo) {
            throw new RuntimeException("Table '{$table}' was not found in the current connection.");
        }

        $columns = $tableInfo['columns'] ?? [];
        $columnNames = array_map(static fn ($column) => $column['name'], $columns);

        $normalizedIdentifiers = $this->normalizeIdentifiers($identifiers, $columnNames);

        if (empty($normalizedIdentifiers)) {
            throw new RuntimeException('Не вдалося визначити ідентифікатори запису.');
        }

        $query = $this->connection->table($table);

        foreach ($normalizedIdentifiers as $identifier) {
            if ($identifier['value'] === null) {
                $query->whereNull($identifier['column']);
                continue;
            }

            $query->where($identifier['column'], '=', $identifier['value']);
        }

        $record = $query->first();

        if (!$record) {
            throw new RuntimeException('Запис не знайдено або вже видалено.');
        }

        $payload = [];

        foreach ((array) $record as $column => $value) {
            $payload[$column] = $this->convertValueForResponse($value);
        }

        if (empty($columnNames) && !empty($payload)) {
            $columnNames = array_keys($payload);
        }

        return [
            'record' => $payload,
            'columns' => $columnNames,
        ];
    }

    /**
     * @param array<int, array{column: string, value: mixed}> $identifiers
     * @return mixed
     */
    public function getRecordValue(string $table, string $column, array $identifiers)
    {
        $structure = Collection::make($this->getStructure());
        $tableInfo = $structure->firstWhere('name', $table);

        if (!$tableInfo) {
            throw new RuntimeException("Table '{$table}' was not found in the current connection.");
        }

        $columns = $tableInfo['columns'] ?? [];
        $columnNames = array_map(static fn ($column) => $column['name'], $columns);
        $normalizedColumn = trim($column);

        $isKnownColumn = in_array($normalizedColumn, $columnNames, true);
        $isAdHocColumn = empty($columnNames) && preg_match('/^[A-Za-z0-9_]+$/', $normalizedColumn);

        if (!$isKnownColumn && !$isAdHocColumn) {
            throw new RuntimeException("Колонку '{$normalizedColumn}' не знайдено у таблиці.");
        }

        $normalizedIdentifiers = $this->normalizeIdentifiers($identifiers, $columnNames);

        if (empty($normalizedIdentifiers)) {
            throw new RuntimeException('Не вдалося визначити ідентифікатори запису для отримання значення.');
        }

        $query = $this->connection->table($table);

        foreach ($normalizedIdentifiers as $identifier) {
            if ($identifier['value'] === null) {
                $query->whereNull($identifier['column']);
                continue;
            }

            $query->where($identifier['column'], '=', $identifier['value']);
        }

        $record = $query->first([$normalizedColumn]);

        if (!$record) {
            throw new RuntimeException('Запис не знайдено або вже видалено.');
        }

        $value = $record->{$normalizedColumn} ?? null;

        return $this->convertValueForResponse($value);
    }

    /**
     * @param array<int, array{column: string, operator: string, value?: mixed}> $filters
     * @param array<int, string> $columnNames
     * @return array<int, array{column: string, operator: string, value?: mixed}>
     */
    private function prepareFilters(array $filters, array $columnNames): array
    {
        return Collection::make($filters)
            ->filter(function ($filter): bool {
                return is_array($filter)
                    && isset($filter['column'], $filter['operator'])
                    && is_string($filter['column'])
                    && is_string($filter['operator']);
            })
            ->map(function (array $filter) use ($columnNames): ?array {
                $column = trim($filter['column']);

                if ($column === '') {
                    return null;
                }

                $isKnownColumn = in_array($column, $columnNames, true);
                $isAdHocColumn = empty($columnNames) && preg_match('/^[A-Za-z0-9_]+$/', $column);

                if (!$isKnownColumn && !$isAdHocColumn) {
                    return null;
                }

                $operator = $this->normalizeOperator($filter['operator']);

                if ($operator === null) {
                    return null;
                }

                $requiresValue = $this->operatorRequiresValue($operator);
                $value = $filter['value'] ?? null;

                if ($requiresValue) {
                    if (!is_scalar($value) && $value !== null) {
                        return null;
                    }

                    $value = $value === null ? null : (string) $value;

                    if ($value === null) {
                        return null;
                    }
                }

                return array_filter([
                    'column' => $column,
                    'operator' => $operator,
                    'value' => $requiresValue ? $value : null,
                ], static fn ($item) => $item !== null);
            })
            ->filter(fn ($filter) => $filter !== null)
            ->values()
            ->all();
    }

    private function normalizeOperator(string $operator): ?string
    {
        return match (strtolower(trim($operator))) {
            '=' => '=',
            '!=' => '!=',
            '<>' => '!=',
            '<' => '<',
            '>' => '>',
            '<=' => '<=',
            '>=' => '>=',
            'like' => 'like',
            'not like', 'not_like' => 'not like',
            default => null,
        };
    }

    private function operatorRequiresValue(string $operator): bool
    {
        return in_array($operator, ['=', '!=', '<', '>', '<=', '>=', 'like', 'not like'], true);
    }

    /**
     * @param array<int, array{column: string, operator: string, value?: string}> $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $filter) {
            $operator = $filter['operator'];

            if (in_array($operator, ['like', 'not like'], true)) {
                $value = (string) ($filter['value'] ?? '');
                $query->where($filter['column'], $operator, $value);
                continue;
            }

            $queryOperator = $operator === '!=' ? '<>' : $operator;

            $query->where($filter['column'], $queryOperator, $filter['value']);
        }
    }

    /**
     * @param array<int, string> $columnNames
     */
    private function applySearch(Builder $query, array $columnNames, string $searchTerm): void
    {
        $searchTerm = trim($searchTerm);

        if ($searchTerm === '') {
            return;
        }

        $wrappedColumns = Collection::make($columnNames)
            ->filter(fn ($column) => is_string($column) && $column !== '')
            ->map(function (string $column): string {
                return $this->connection->getQueryGrammar()->wrap($column);
            })
            ->unique()
            ->values()
            ->all();

        if (empty($wrappedColumns)) {
            return;
        }

        $driver = $this->connection->getDriverName();
        $pattern = '%' . $searchTerm . '%';

        $query->where(function (Builder $builder) use ($wrappedColumns, $pattern, $driver): void {
            foreach ($wrappedColumns as $wrappedColumn) {
                [$sql, $bindings] = $this->buildSearchExpression($wrappedColumn, $pattern, $driver);
                $builder->orWhereRaw($sql, $bindings);
            }
        });
    }

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    private function buildSearchExpression(string $wrappedColumn, string $pattern, string $driver): array
    {
        return match ($driver) {
            'pgsql' => ["CAST({$wrappedColumn} AS TEXT) ILIKE ?", [$pattern]],
            'sqlsrv' => ["CAST({$wrappedColumn} AS NVARCHAR(MAX)) LIKE ?", [$pattern]],
            'sqlite' => ["CAST({$wrappedColumn} AS TEXT) LIKE ?", [$pattern]],
            default => ["CAST({$wrappedColumn} AS CHAR) LIKE ?", [$pattern]],
        };
    }

    private function convertValueForResponse(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_resource($value)) {
            $contents = stream_get_contents($value);

            return $contents === false ? null : $contents;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s.u');
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $encoded === false ? '[]' : $encoded;
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $encoded === false ? get_class($value) : $encoded;
        }

        return $value;
    }

    /**
     * @param array<int, array<string, mixed>> $columns
     * @return array<string, array{table: string, column: string, display_column: ?string}>
     */
    private function extractForeignColumns(array $columns): array
    {
        $result = [];

        foreach ($columns as $column) {
            if (!is_array($column)) {
                continue;
            }

            $name = isset($column['name']) && is_string($column['name'])
                ? trim($column['name'])
                : '';

            if ($name === '') {
                continue;
            }

            if (!isset($column['foreign']) || !is_array($column['foreign'])) {
                continue;
            }

            $foreignTable = isset($column['foreign']['table']) && is_string($column['foreign']['table'])
                ? trim($column['foreign']['table'])
                : '';
            $foreignColumn = isset($column['foreign']['column']) && is_string($column['foreign']['column'])
                ? trim($column['foreign']['column'])
                : '';

            if ($foreignTable === '' || $foreignColumn === '') {
                continue;
            }

            $displayColumn = isset($column['foreign']['display_column']) && is_string($column['foreign']['display_column'])
                ? trim($column['foreign']['display_column'])
                : '';

            $result[$name] = [
                'table' => $foreignTable,
                'column' => $foreignColumn,
                'display_column' => $displayColumn !== '' ? $displayColumn : null,
            ];
        }

        return $result;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getContentManagementRelationOverrides(string $table): array
    {
        $settings = config('database-structure.content_management.table_settings', []);

        if (!is_array($settings)) {
            return [];
        }

        $tableConfig = $settings[$table] ?? null;

        if (!is_array($tableConfig)) {
            return [];
        }

        $keys = [
            'relations',
            'relation_display',
            'relation_display_columns',
            'relationDisplayColumns',
            'relationDisplay',
            'foreign_display',
            'foreignDisplay',
            'relation_overrides',
            'relationOverrides',
        ];

        $normalized = [];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $tableConfig)) {
                continue;
            }

            $normalized += $this->normalizeRelationConfig($tableConfig[$key]);
        }

        if (empty($normalized)) {
            $normalized = $this->normalizeRelationConfig($tableConfig);
        }

        return $normalized;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function normalizeRelationConfig(mixed $config): array
    {
        if (!is_array($config)) {
            return [];
        }

        $reservedKeys = [
            'aliases',
            'columns',
            'fields',
            'hidden',
            'hidden_columns',
            'hiddenColumns',
            'columns_hidden',
            'columnsHidden',
            'hide',
            'menu',
            'table',
            'label',
        ];

        $normalized = [];

        foreach ($config as $column => $definition) {
            if (is_int($column)) {
                if (is_array($definition)) {
                    $normalized += $this->normalizeRelationConfig($definition);
                }

                continue;
            }

            if (!is_string($column)) {
                continue;
            }

            $columnName = trim($column);

            if ($columnName === '' || in_array($columnName, $reservedKeys, true)) {
                continue;
            }

            $entry = [];

            if (is_string($definition)) {
                $displayColumn = trim($definition);

                if ($displayColumn === '') {
                    continue;
                }

                $entry['display_column'] = $displayColumn;
                $normalized[$columnName] = $entry;
                continue;
            }

            if (!is_array($definition)) {
                continue;
            }

            $tableCandidates = [
                $definition['table'] ?? null,
                $definition['reference_table'] ?? null,
                $definition['referenced_table'] ?? null,
                $definition['target_table'] ?? null,
            ];

            foreach ($tableCandidates as $candidate) {
                if (is_string($candidate)) {
                    $candidate = trim($candidate);

                    if ($candidate !== '') {
                        $entry['table'] = $candidate;
                        break;
                    }
                }
            }

            $columnCandidates = [
                $definition['column'] ?? null,
                $definition['reference_column'] ?? null,
                $definition['referenced_column'] ?? null,
                $definition['target_column'] ?? null,
                $definition['foreign_column'] ?? null,
            ];

            foreach ($columnCandidates as $candidate) {
                if (is_string($candidate)) {
                    $candidate = trim($candidate);

                    if ($candidate !== '') {
                        $entry['column'] = $candidate;
                        break;
                    }
                }
            }

            $displayCandidates = [
                $definition['display_column'] ?? null,
                $definition['displayColumn'] ?? null,
                $definition['label_column'] ?? null,
                $definition['labelColumn'] ?? null,
                $definition['value_column'] ?? null,
            ];

            foreach ($displayCandidates as $candidate) {
                if (is_string($candidate)) {
                    $candidate = trim($candidate);

                    if ($candidate !== '') {
                        $entry['display_column'] = $candidate;
                        break;
                    }
                }
            }

            $searchColumns = [];

            $searchCandidates = [
                $definition['search_columns'] ?? null,
                $definition['searchColumns'] ?? null,
                $definition['search'] ?? null,
            ];

            foreach ($searchCandidates as $candidate) {
                if (is_string($candidate)) {
                    $value = trim($candidate);

                    if ($value !== '') {
                        $searchColumns[] = $value;
                    }

                    continue;
                }

                if (!is_array($candidate)) {
                    continue;
                }

                foreach ($candidate as $item) {
                    if (!is_string($item)) {
                        continue;
                    }

                    $value = trim($item);

                    if ($value !== '') {
                        $searchColumns[] = $value;
                    }
                }
            }

            if (!empty($searchColumns)) {
                $entry['search_columns'] = array_values(array_unique($searchColumns));
            }

            if (!empty($entry)) {
                $normalized[$columnName] = $entry;
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, array{table: string, column: string, display_column: ?string}> $foreignColumns
     * @param array<string, array<string, mixed>> $relationOverrides
     * @return array<string, array{foreign_table: string, foreign_column: string, display_column: string, join_alias: string, select_alias: string, column_name: string, search_columns: array<int, string>}>
     */
    private function prepareContentManagementDisplayOverrides(
        array $foreignColumns,
        array $relationOverrides
    ): array {
        $overrides = [];
        $columns = array_unique(array_merge(array_keys($foreignColumns), array_keys($relationOverrides)));

        foreach ($columns as $columnName) {
            $structure = $foreignColumns[$columnName] ?? null;
            $config = $relationOverrides[$columnName] ?? [];

            $foreignTable = '';
            $foreignColumn = '';

            if (is_array($config)) {
                if (isset($config['table']) && is_string($config['table'])) {
                    $foreignTable = trim($config['table']);
                }

                if (isset($config['column']) && is_string($config['column'])) {
                    $foreignColumn = trim($config['column']);
                }
            }

            if ($foreignTable === '' && $structure && isset($structure['table'])) {
                $foreignTable = (string) $structure['table'];
            }

            if ($foreignColumn === '' && $structure && isset($structure['column'])) {
                $foreignColumn = (string) $structure['column'];
            }

            $displayColumn = '';

            if (is_array($config) && isset($config['display_column']) && is_string($config['display_column'])) {
                $displayColumn = trim($config['display_column']);
            } elseif ($structure && isset($structure['display_column']) && is_string($structure['display_column'])) {
                $displayColumn = trim($structure['display_column']);
            }

            if ($foreignTable === '' || $foreignColumn === '' || $displayColumn === '') {
                continue;
            }

            $searchColumns = [];

            if (is_array($config) && isset($config['search_columns'])) {
                $searchColumns = array_filter(
                    array_map(
                        static function ($column): string {
                            return is_string($column) ? trim($column) : '';
                        },
                        (array) $config['search_columns']
                    ),
                    static fn ($column) => $column !== ''
                );
            }

            if (empty($searchColumns)) {
                $searchColumns = [$displayColumn];
            }

            $sanitized = $this->sanitizeIdentifier($columnName);
            $joinAlias = '__ds_' . $sanitized;
            $selectAlias = '__display_' . $sanitized;

            $overrides[$columnName] = [
                'foreign_table' => $foreignTable,
                'foreign_column' => $foreignColumn,
                'display_column' => $displayColumn,
                'join_alias' => $joinAlias,
                'select_alias' => $selectAlias,
                'column_name' => $columnName,
                'search_columns' => array_values(array_unique($searchColumns)),
            ];
        }

        return $overrides;
    }

    private function sanitizeIdentifier(string $value): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9_]/', '_', $value);

        if ($normalized === null || $normalized === '') {
            $normalized = 'column';
        }

        if (preg_match('/^[0-9]/', $normalized) === 1) {
            $normalized = '_' . $normalized;
        }

        return substr($normalized, 0, 50);
    }

    /**
     * @param array<int, string> $columnNames
     * @return array<int, string>
     */
    private function buildBaseSelectColumns(string $table, array $columnNames): array
    {
        if (empty($columnNames)) {
            return [$this->wrapQualified($table, '*')];
        }

        return array_map(function (string $column) use ($table): string {
            return sprintf(
                '%s as %s',
                $this->wrapQualified($table, $column),
                $this->connection->getQueryGrammar()->wrap($column)
            );
        }, $columnNames);
    }

    /**
     * @param array<string, array{display_column: string, join_alias: string, select_alias: string}> $displayOverrides
     * @return array<int, string>
     */
    private function buildDisplaySelectColumns(array $displayOverrides): array
    {
        $selects = [];

        foreach ($displayOverrides as $override) {
            $selects[] = sprintf(
                '%s as %s',
                $this->wrapQualified($override['join_alias'], $override['display_column']),
                $this->connection->getQueryGrammar()->wrap($override['select_alias'])
            );
        }

        return $selects;
    }

    private function wrapQualified(string $table, string $column): string
    {
        $grammar = $this->connection->getQueryGrammar();

        if ($column === '*') {
            return $grammar->wrapTable($table) . '.*';
        }

        return $grammar->wrapTable($table) . '.' . $grammar->wrap($column);
    }

    /**
     * @param array<int, string> $columnNames
     * @param array<string, array{join_alias: string, search_columns: array<int, string>}> $displayOverrides
     * @return array<string, array<int, string>>
     */
    private function buildSearchColumns(array $columnNames, array $displayOverrides): array
    {
        $searchColumns = [];

        foreach ($columnNames as $columnName) {
            $columns = [$columnName];

            if (isset($displayOverrides[$columnName])) {
                $override = $displayOverrides[$columnName];

                foreach ($override['search_columns'] as $searchColumn) {
                    $columns[] = $override['join_alias'] . '.' . $searchColumn;
                }
            }

            $searchColumns[$columnName] = array_values(array_unique($columns));
        }

        foreach ($displayOverrides as $columnName => $override) {
            if (isset($searchColumns[$columnName])) {
                continue;
            }

            $columns = [];

            foreach ($override['search_columns'] as $searchColumn) {
                $columns[] = $override['join_alias'] . '.' . $searchColumn;
            }

            if (!empty($columns)) {
                $searchColumns[$columnName] = array_values(array_unique($columns));
            }
        }

        return $searchColumns;
    }

    /**
     * @param array<int, array{column: string, value: mixed}> $identifiers
     * @param array<int, string> $columnNames
     * @return array<int, array{column: string, value: mixed}>
     */
    private function normalizeIdentifiers(array $identifiers, array $columnNames): array
    {
        return Collection::make($identifiers)
            ->filter(function ($identifier): bool {
                return is_array($identifier)
                    && isset($identifier['column'])
                    && is_string($identifier['column']);
            })
            ->map(function (array $identifier) use ($columnNames): ?array {
                $column = trim($identifier['column']);

                if ($column === '') {
                    return null;
                }

                $isKnownColumn = in_array($column, $columnNames, true);
                $isAdHocColumn = empty($columnNames) && preg_match('/^[A-Za-z0-9_]+$/', $column);

                if (!$isKnownColumn && !$isAdHocColumn) {
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

    private function normalizeForeignDefinition(mixed $foreign): ?array
    {
        if (!is_array($foreign)) {
            return null;
        }

        $table = isset($foreign['table']) && is_string($foreign['table'])
            ? trim($foreign['table'])
            : '';
        $column = isset($foreign['column']) && is_string($foreign['column'])
            ? trim($foreign['column'])
            : '';

        if ($table === '' || $column === '') {
            return null;
        }

        $constraint = isset($foreign['constraint']) && is_string($foreign['constraint'])
            ? trim($foreign['constraint'])
            : '';
        $displayColumn = isset($foreign['display_column']) && is_string($foreign['display_column'])
            ? trim($foreign['display_column'])
            : '';

        $labelColumns = [];

        if (isset($foreign['label_columns']) && is_array($foreign['label_columns'])) {
            foreach ($foreign['label_columns'] as $labelColumn) {
                if (is_string($labelColumn) && $labelColumn !== '') {
                    $labelColumns[] = $labelColumn;
                }
            }
        }

        return [
            'table' => $table,
            'column' => $column,
            'constraint' => $constraint !== '' ? $constraint : null,
            'display_column' => $displayColumn !== '' ? $displayColumn : null,
            'label_columns' => $labelColumns,
            'manual' => isset($foreign['manual']) && $foreign['manual'] ? true : false,
        ];
    }

    private function getManualRelations(): array
    {
        if ($this->manualRelationsCache !== null) {
            return $this->manualRelationsCache;
        }

        $relations = config('database-structure.manual_relations', []);

        if (!is_array($relations)) {
            return $this->manualRelationsCache = [];
        }

        $normalized = [];

        foreach ($relations as $table => $columns) {
            if (!is_string($table) || trim($table) === '' || !is_array($columns)) {
                continue;
            }

            $tableName = trim($table);

            foreach ($columns as $column => $definition) {
                if (!is_string($column) || trim($column) === '' || !is_array($definition)) {
                    continue;
                }

                $normalized[$tableName][trim($column)] = $definition;
            }
        }

        return $this->manualRelationsCache = $normalized;
    }

    private function applyManualForeignDefinitions(array $structure): array
    {
        $manualRelations = $this->getManualRelations();

        if (empty($manualRelations)) {
            return $structure;
        }

        $structureByTable = [];

        foreach ($structure as $table) {
            if (!is_array($table)) {
                continue;
            }

            $name = $table['name'] ?? null;

            if (is_string($name) && $name !== '') {
                $structureByTable[$name] = $table;
            }
        }

        foreach ($structure as &$table) {
            if (!is_array($table)) {
                continue;
            }

            $tableName = $table['name'] ?? null;

            if (!is_string($tableName) || $tableName === '') {
                continue;
            }

            if (!isset($table['columns']) || !is_array($table['columns'])) {
                continue;
            }

            foreach ($table['columns'] as &$column) {
                if (!is_array($column)) {
                    continue;
                }

                $columnName = $column['name'] ?? null;

                if (!is_string($columnName) || $columnName === '') {
                    continue;
                }

                if (isset($column['foreign']) && is_array($column['foreign'])) {
                    $column['foreign']['manual'] = $column['foreign']['manual'] ?? false;
                    continue;
                }

                $definition = $manualRelations[$tableName][$columnName] ?? null;

                if (!is_array($definition)) {
                    continue;
                }

                $manualDefinition = $this->prepareManualForeignDefinition($definition, $structureByTable);

                if ($manualDefinition !== null) {
                    $column['foreign'] = $manualDefinition;
                }
            }

            unset($column);
        }

        unset($table);

        return $structure;
    }

    private function prepareManualForeignDefinition(array $definition, array $structureByTable): ?array
    {
        $table = isset($definition['table']) && is_string($definition['table'])
            ? trim($definition['table'])
            : '';
        $column = isset($definition['column']) && is_string($definition['column'])
            ? trim($definition['column'])
            : '';

        if ($table === '' || $column === '') {
            return null;
        }

        $displayColumn = isset($definition['display_column']) && is_string($definition['display_column'])
            ? trim($definition['display_column'])
            : '';

        $labelColumns = [];

        if (isset($definition['label_columns']) && is_array($definition['label_columns'])) {
            foreach ($definition['label_columns'] as $labelColumn) {
                if (is_string($labelColumn) && trim($labelColumn) !== '') {
                    $labelColumns[] = trim($labelColumn);
                }
            }
        }

        if (empty($labelColumns)) {
            $referencedTable = $structureByTable[$table]['columns'] ?? null;

            if (is_array($referencedTable)) {
                $labelColumns = $this->guessLabelColumns($referencedTable, $column);
            }
        }

        $payload = [
            'table' => $table,
            'column' => $column,
            'display_column' => $displayColumn !== '' ? $displayColumn : null,
            'label_columns' => $labelColumns,
            'manual' => true,
        ];

        if ($payload['display_column'] === null && !empty($labelColumns)) {
            $payload['display_column'] = $labelColumns[0];
        }

        return $payload;
    }

    /**
     * @param array<int, array<string, mixed>> $columns
     * @return array<int, string>
     */
    private function guessLabelColumns(array $columns, string $referencedColumn): array
    {
        $preferredNames = [
            'name',
            'title',
            'label',
            'description',
            'slug',
            'email',
            'username',
            'code',
        ];

        $result = [];
        $lowerPreferred = array_map('strtolower', $preferredNames);

        foreach ($columns as $column) {
            if (!is_array($column)) {
                continue;
            }

            $name = $column['name'] ?? null;

            if (!is_string($name) || $name === '') {
                continue;
            }

            if (in_array(strtolower($name), $lowerPreferred, true)) {
                $result[] = $name;
            }
        }

        foreach ($columns as $column) {
            if (!is_array($column)) {
                continue;
            }

            $name = $column['name'] ?? null;

            if (!is_string($name) || $name === '') {
                continue;
            }

            $type = strtolower((string) ($column['type'] ?? ''));

            if (
                str_contains($type, 'char') ||
                str_contains($type, 'text') ||
                str_contains($type, 'json') ||
                str_contains($type, 'enum')
            ) {
                $result[] = $name;
            }
        }

        $result[] = $referencedColumn;

        $unique = [];

        foreach ($result as $candidate) {
            if (is_string($candidate) && $candidate !== '' && !in_array($candidate, $unique, true)) {
                $unique[] = $candidate;
            }
        }

        return array_slice($unique, 0, 5);
    }

    private function structureFromMySql(): array
    {
        $database = $this->connection->getDatabaseName();

        $tables = $this->connection->select(
            'SELECT TABLE_NAME, TABLE_COMMENT, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
            [$database]
        );

        $columnsByTable = [];

        foreach ($tables as $table) {
            $rawColumns = $this->connection->select(
                'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION',
                [$database, $table->TABLE_NAME]
            );

            $columnsByTable[$table->TABLE_NAME] = array_map(
                static function ($column): array {
                    return [
                        'name' => $column->COLUMN_NAME,
                        'type' => $column->COLUMN_TYPE,
                        'nullable' => $column->IS_NULLABLE === 'YES',
                        'default' => $column->COLUMN_DEFAULT,
                        'key' => $column->COLUMN_KEY ?: null,
                        'extra' => $column->EXTRA ?: null,
                        'comment' => $column->COLUMN_COMMENT ?: null,
                        'foreign' => null,
                    ];
                },
                $rawColumns,
            );
        }

        $foreignKeyRows = $this->connection->select(
            'SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database]
        );

        $labelColumnsCache = [];

        foreach ($foreignKeyRows as $foreignKey) {
            $tableName = $foreignKey->TABLE_NAME;
            $columnName = $foreignKey->COLUMN_NAME;

            if (!isset($columnsByTable[$tableName])) {
                continue;
            }

            foreach ($columnsByTable[$tableName] as &$column) {
                if (($column['name'] ?? null) !== $columnName) {
                    continue;
                }

                $referencedTable = (string) $foreignKey->REFERENCED_TABLE_NAME;
                $referencedColumn = (string) $foreignKey->REFERENCED_COLUMN_NAME;

                if (!array_key_exists($referencedTable, $labelColumnsCache)) {
                    $labelColumnsCache[$referencedTable] = $this->guessLabelColumns(
                        $columnsByTable[$referencedTable] ?? [],
                        $referencedColumn,
                    );
                }

                $labelColumns = $labelColumnsCache[$referencedTable];

                $column['foreign'] = [
                    'table' => $referencedTable,
                    'column' => $referencedColumn,
                    'constraint' => (string) $foreignKey->CONSTRAINT_NAME,
                    'display_column' => $labelColumns[0] ?? null,
                    'label_columns' => $labelColumns,
                ];
                break;
            }

            unset($column);
        }

        return array_map(function ($table) use ($columnsByTable) {
            $tableName = $table->TABLE_NAME;

            return [
                'name' => $tableName,
                'comment' => $table->TABLE_COMMENT ?: null,
                'engine' => $table->ENGINE ?: null,
                'columns' => array_values($columnsByTable[$tableName] ?? []),
            ];
        }, $tables);
    }

    private function structureFromPostgres(): array
    {
        $schema = $this->connection->selectOne('SELECT current_schema() AS schema_name');
        $schemaName = $schema?->schema_name ?? 'public';

        $tables = $this->connection->select(
            'SELECT table_name
             FROM information_schema.tables
             WHERE table_schema = ? AND table_type = ?
             ORDER BY table_name',
            [$schemaName, 'BASE TABLE']
        );

        return array_map(function ($table) use ($schemaName) {
            $columns = $this->connection->select(
                'SELECT column_name, data_type, is_nullable, column_default
                 FROM information_schema.columns
                 WHERE table_schema = ? AND table_name = ?
                 ORDER BY ordinal_position',
                [$schemaName, $table->table_name]
            );

            return [
                'name' => $table->table_name,
                'comment' => null,
                'engine' => null,
                'columns' => array_map(fn ($column) => [
                    'name' => $column->column_name,
                    'type' => $column->data_type,
                    'nullable' => $column->is_nullable === 'YES',
                    'default' => $column->column_default,
                    'key' => null,
                    'extra' => null,
                    'comment' => null,
                ], $columns),
            ];
        }, $tables);
    }

    private function structureFromSqlite(): array
    {
        $tables = $this->connection->select(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
        );

        return array_map(function ($table) {
            $tableName = str_replace("'", "''", $table->name);
            $columns = $this->connection->select("PRAGMA table_info('{$tableName}')");

            return [
                'name' => $table->name,
                'comment' => null,
                'engine' => null,
                'columns' => array_map(fn ($column) => [
                    'name' => $column->name,
                    'type' => $column->type,
                    'nullable' => $column->notnull === 0,
                    'default' => $column->dflt_value,
                    'key' => $column->pk ? 'PRI' : null,
                    'extra' => null,
                    'comment' => null,
                ], $columns),
            ];
        }, $tables);
    }

    private function structureFromSqlServer(): array
    {
        $tables = $this->connection->select(
            'SELECT TABLE_NAME
             FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_TYPE = ?
             ORDER BY TABLE_NAME',
            ['BASE TABLE']
        );

        return array_map(function ($table) {
            $columns = $this->connection->select(
                'SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION',
                [$table->TABLE_NAME]
            );

            return [
                'name' => $table->TABLE_NAME,
                'comment' => null,
                'engine' => null,
                'columns' => array_map(fn ($column) => [
                    'name' => $column->COLUMN_NAME,
                    'type' => $column->DATA_TYPE,
                    'nullable' => $column->IS_NULLABLE === 'YES',
                    'default' => $column->COLUMN_DEFAULT,
                    'key' => null,
                    'extra' => null,
                    'comment' => null,
                ], $columns),
            ];
        }, $tables);
    }
}
