<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;
use Throwable;

class DumpSiteDatabaseCommand extends Command
{
    protected $signature = 'db:dump-site
        {--connection= : Laravel database connection name}
        {--path=database/dumps/site-database.sql : Relative or absolute dump file path}
        {--rows-per-insert=100 : Number of rows per INSERT statement}';

    protected $description = 'Dump the full site MySQL database into a GitHub-trackable SQL file.';

    public function handle(): int
    {
        $connectionName = (string) ($this->option('connection') ?: config('database.default'));
        $config = config('database.connections.' . $connectionName);

        if (! is_array($config)) {
            $this->error('Database connection is not configured: ' . $connectionName);

            return self::FAILURE;
        }

        if (($config['driver'] ?? null) !== 'mysql') {
            $this->error('db:dump-site currently supports only mysql connections.');

            return self::FAILURE;
        }

        $database = (string) ($config['database'] ?? '');
        if ($database === '') {
            $this->error('DB_DATABASE is empty for connection: ' . $connectionName);

            return self::FAILURE;
        }

        $rowsPerInsert = max(1, min(1000, (int) $this->option('rows-per-insert')));
        $outputPath = $this->resolveOutputPath((string) $this->option('path'));

        try {
            $pdo = DB::connection($connectionName)->getPdo();
            [$tables, $views] = $this->databaseObjects($pdo);
            $rowCount = $this->writeDump($pdo, $database, $tables, $views, $outputPath, $rowsPerInsert);
        } catch (Throwable $exception) {
            $this->error('Database dump failed: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Database dump created: ' . $outputPath);
        $this->line('Connection: ' . $connectionName);
        $this->line('Database: ' . $database);
        $this->line('Tables: ' . count($tables));
        $this->line('Views: ' . count($views));
        $this->line('Rows: ' . $rowCount);
        $this->line('Size: ' . number_format((int) filesize($outputPath)) . ' bytes');

        return self::SUCCESS;
    }

    /**
     * @return array{0: list<string>, 1: list<string>}
     */
    private function databaseObjects(PDO $pdo): array
    {
        $statement = $pdo->query('SHOW FULL TABLES');
        if ($statement === false) {
            throw new RuntimeException('Unable to list database tables.');
        }

        $tables = [];
        $views = [];

        foreach ($statement->fetchAll(PDO::FETCH_NUM) as $row) {
            $name = (string) ($row[0] ?? '');
            $type = (string) ($row[1] ?? '');

            if ($name === '') {
                continue;
            }

            if (strcasecmp($type, 'VIEW') === 0) {
                $views[] = $name;
            } else {
                $tables[] = $name;
            }
        }

        sort($tables);
        sort($views);

        return [$tables, $views];
    }

    /**
     * @param  list<string>  $tables
     * @param  list<string>  $views
     */
    private function writeDump(PDO $pdo, string $database, array $tables, array $views, string $outputPath, int $rowsPerInsert): int
    {
        $directory = dirname($outputPath);
        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create dump directory: ' . $directory);
        }

        $handle = fopen($outputPath, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Unable to open dump file for writing: ' . $outputPath);
        }

        $totalRows = 0;

        try {
            $this->writeLine($handle, '-- Gramlyze site database dump');
            $this->writeLine($handle, '-- Generated at: ' . now()->toIso8601String());
            $this->writeLine($handle, '-- Database: ' . $database);
            $this->writeLine($handle);
            $this->writeLine($handle, 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";');
            $this->writeLine($handle, 'SET time_zone = "+00:00";');
            $this->writeLine($handle, 'SET FOREIGN_KEY_CHECKS = 0;');
            $this->writeLine($handle, 'SET NAMES utf8mb4;');
            $this->writeLine($handle);

            foreach ($views as $view) {
                $this->writeLine($handle, 'DROP VIEW IF EXISTS ' . $this->quoteIdentifier($view) . ';');
            }

            foreach ($tables as $table) {
                $this->writeLine($handle, 'DROP TABLE IF EXISTS ' . $this->quoteIdentifier($table) . ';');
            }

            $this->writeLine($handle);

            foreach ($tables as $table) {
                $createSql = $this->showCreate($pdo, 'TABLE', $table);
                $this->writeLine($handle, '-- Structure for table ' . $table);
                $this->writeLine($handle, $createSql . ';');
                $this->writeLine($handle);
            }

            foreach ($tables as $table) {
                $totalRows += $this->writeTableData($pdo, $handle, $table, $rowsPerInsert);
            }

            foreach ($views as $view) {
                $createSql = $this->showCreate($pdo, 'VIEW', $view);
                $this->writeLine($handle, '-- Structure for view ' . $view);
                $this->writeLine($handle, $createSql . ';');
                $this->writeLine($handle);
            }

            $this->writeLine($handle, 'SET FOREIGN_KEY_CHECKS = 1;');
        } finally {
            fclose($handle);
        }

        return $totalRows;
    }

    private function writeTableData(PDO $pdo, mixed $handle, string $table, int $rowsPerInsert): int
    {
        $columnTypes = $this->columnTypes($pdo, $table);
        $quotedTable = $this->quoteIdentifier($table);
        $statement = $pdo->query('SELECT * FROM ' . $quotedTable);

        if ($statement === false) {
            throw new RuntimeException('Unable to read rows from table: ' . $table);
        }

        $columns = null;
        $values = [];
        $rowCount = 0;

        while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            if ($columns === null) {
                $columns = array_keys($row);
            }

            $values[] = '(' . implode(', ', array_map(
                fn (string $column): string => $this->sqlValue($row[$column], $columnTypes[$column] ?? ''),
                $columns
            )) . ')';
            $rowCount++;

            if (count($values) >= $rowsPerInsert) {
                $this->flushInsert($handle, $table, $columns, $values);
                $values = [];
            }
        }

        if ($values !== [] && $columns !== null) {
            $this->flushInsert($handle, $table, $columns, $values);
        }

        if ($rowCount > 0) {
            $this->writeLine($handle);
        }

        return $rowCount;
    }

    /**
     * @return array<string, string>
     */
    private function columnTypes(PDO $pdo, string $table): array
    {
        $statement = $pdo->query('SHOW COLUMNS FROM ' . $this->quoteIdentifier($table));
        if ($statement === false) {
            throw new RuntimeException('Unable to read columns for table: ' . $table);
        }

        $types = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $field = (string) ($row['Field'] ?? '');
            if ($field !== '') {
                $types[$field] = strtolower((string) ($row['Type'] ?? ''));
            }
        }

        return $types;
    }

    /**
     * @param  list<string>  $columns
     * @param  list<string>  $values
     */
    private function flushInsert(mixed $handle, string $table, array $columns, array $values): void
    {
        $columnList = implode(', ', array_map(fn (string $column): string => $this->quoteIdentifier($column), $columns));

        $this->writeLine($handle, 'INSERT INTO ' . $this->quoteIdentifier($table) . ' (' . $columnList . ') VALUES');
        $this->writeLine($handle, implode(',' . PHP_EOL, $values) . ';');
    }

    private function showCreate(PDO $pdo, string $type, string $name): string
    {
        $statement = $pdo->query('SHOW CREATE ' . $type . ' ' . $this->quoteIdentifier($name));
        if ($statement === false) {
            throw new RuntimeException('Unable to read CREATE statement for ' . strtolower($type) . ': ' . $name);
        }

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (! is_array($row)) {
            throw new RuntimeException('Empty CREATE statement for ' . strtolower($type) . ': ' . $name);
        }

        $values = array_values($row);
        $createSql = $values[1] ?? null;

        if (! is_string($createSql) || $createSql === '') {
            throw new RuntimeException('Invalid CREATE statement for ' . strtolower($type) . ': ' . $name);
        }

        return $createSql;
    }

    private function sqlValue(mixed $value, string $columnType): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if ($this->isBinaryType($columnType)) {
            return '0x' . bin2hex((string) $value);
        }

        if ($this->isNumericType($columnType) && is_numeric($value)) {
            return (string) $value;
        }

        return "'" . strtr((string) $value, [
            "\0" => '\\0',
            "\n" => '\\n',
            "\r" => '\\r',
            '\\' => '\\\\',
            "'" => "\\'",
            '"' => '\\"',
            "\x1a" => '\\Z',
        ]) . "'";
    }

    private function isBinaryType(string $columnType): bool
    {
        return str_contains($columnType, 'blob')
            || str_contains($columnType, 'binary')
            || str_contains($columnType, 'geometry');
    }

    private function isNumericType(string $columnType): bool
    {
        return preg_match('/^(tinyint|smallint|mediumint|int|bigint|decimal|float|double|real|bit|year)\b/i', $columnType) === 1;
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function writeLine(mixed $handle, string $line = ''): void
    {
        if (fwrite($handle, $line . PHP_EOL) === false) {
            throw new RuntimeException('Unable to write dump file.');
        }
    }

    private function resolveOutputPath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            $path = 'database/dumps/site-database.sql';
        }

        if ($this->isAbsolutePath($path)) {
            return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        }

        return base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1
            || str_starts_with($path, '/')
            || str_starts_with($path, '\\');
    }
}
