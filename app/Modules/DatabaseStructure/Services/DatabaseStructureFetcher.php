<?php

namespace App\Modules\DatabaseStructure\Services;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseStructureFetcher
{
    private Connection $connection;
    private ?array $structureCache = null;

    public function __construct()
    {
        $connectionName = config('database-structure.connection');
        $this->connection = $connectionName
            ? DB::connection($connectionName)
            : DB::connection();
    }

    public function getStructure(): array
    {
        if ($this->structureCache !== null) {
            return $this->structureCache;
        }

        $driver = $this->connection->getDriverName();

        return $this->structureCache = match ($driver) {
            'mysql' => $this->structureFromMySql(),
            'pgsql' => $this->structureFromPostgres(),
            'sqlite' => $this->structureFromSqlite(),
            'sqlsrv' => $this->structureFromSqlServer(),
            default => throw new RuntimeException("Database driver '{$driver}' is not supported."),
        };
    }

    public function getMeta(): array
    {
        $structure = Collection::make($this->getStructure());

        return [
            'connection' => $this->connection->getName(),
            'driver' => $this->connection->getDriverName(),
            'database' => $this->connection->getDatabaseName(),
            'tables_count' => $structure->count(),
            'columns_count' => $structure->sum(fn ($table) => count($table['columns'] ?? [])),
        ];
    }

    private function structureFromMySql(): array
    {
        $database = $this->connection->getDatabaseName();

        $tables = $this->connection->select(
            'SELECT TABLE_NAME, TABLE_COMMENT, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
            [$database]
        );

        return array_map(function ($table) use ($database) {
            $columns = $this->connection->select(
                'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION',
                [$database, $table->TABLE_NAME]
            );

            return [
                'name' => $table->TABLE_NAME,
                'comment' => $table->TABLE_COMMENT ?: null,
                'engine' => $table->ENGINE ?: null,
                'columns' => array_map(fn ($column) => [
                    'name' => $column->COLUMN_NAME,
                    'type' => $column->COLUMN_TYPE,
                    'nullable' => $column->IS_NULLABLE === 'YES',
                    'default' => $column->COLUMN_DEFAULT,
                    'key' => $column->COLUMN_KEY ?: null,
                    'extra' => $column->EXTRA ?: null,
                    'comment' => $column->COLUMN_COMMENT ?: null,
                ], $columns),
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
