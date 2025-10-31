<?php

namespace App\Modules\DatabaseStructure\Services;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class ManualRelationManager
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function getManualRelations(): array
    {
        $relations = config('database-structure.manual_relations', []);

        if (!is_array($relations)) {
            return [];
        }

        $normalized = [];

        foreach ($relations as $table => $columns) {
            if (!is_string($table) || trim($table) === '' || !is_array($columns)) {
                continue;
            }

            $tableName = trim($table);
            $normalized[$tableName] = [];

            foreach ($columns as $column => $definition) {
                if (!is_string($column) || trim($column) === '' || !is_array($definition)) {
                    continue;
                }

                $normalized[$tableName][trim($column)] = $this->normalizeDefinition($definition);
            }

            if (empty($normalized[$tableName])) {
                unset($normalized[$tableName]);
            }
        }

        return $normalized;
    }

    public function save(string $table, string $column, array $definition): array
    {
        $tableName = $this->normalizeName($table);
        $columnName = $this->normalizeName($column);

        if ($tableName === '' || $columnName === '') {
            throw new RuntimeException('Не вдалося визначити таблицю або колонку для збереження зв\'язку.');
        }

        $foreignTable = $this->normalizeName($definition['table'] ?? null);
        $foreignColumn = $this->normalizeName($definition['column'] ?? null);

        if ($foreignTable === '' || $foreignColumn === '') {
            throw new RuntimeException('Необхідно вказати таблицю та колонку для зв\'язку.');
        }

        $displayColumn = $this->normalizeName($definition['display_column'] ?? null);

        $relations = $this->getManualRelations();

        if (!isset($relations[$tableName])) {
            $relations[$tableName] = [];
        }

        $payload = [
            'table' => $foreignTable,
            'column' => $foreignColumn,
        ];

        if ($displayColumn !== '') {
            $payload['display_column'] = $displayColumn;
        }

        $relations[$tableName][$columnName] = $payload;

        $this->writeConfig($relations);

        return $payload;
    }

    public function delete(string $table, string $column): void
    {
        $tableName = $this->normalizeName($table);
        $columnName = $this->normalizeName($column);

        if ($tableName === '' || $columnName === '') {
            throw new RuntimeException('Не вдалося визначити таблицю або колонку для видалення зв\'язку.');
        }

        $relations = $this->getManualRelations();

        if (!isset($relations[$tableName][$columnName])) {
            throw new RuntimeException('Ручний зв\'язок для цього поля не знайдено.');
        }

        unset($relations[$tableName][$columnName]);

        if (empty($relations[$tableName])) {
            unset($relations[$tableName]);
        }

        $this->writeConfig($relations);
    }

    private function normalizeName(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function normalizeDefinition(array $definition): array
    {
        $table = $this->normalizeName($definition['table'] ?? null);
        $column = $this->normalizeName($definition['column'] ?? null);

        if ($table === '' || $column === '') {
            return [];
        }

        $payload = [
            'table' => $table,
            'column' => $column,
        ];

        $displayColumn = $this->normalizeName($definition['display_column'] ?? null);

        if ($displayColumn !== '') {
            $payload['display_column'] = $displayColumn;
        }

        return $payload;
    }

    private function writeConfig(array $relations): void
    {
        ksort($relations);

        foreach ($relations as $table => $columns) {
            if (!is_array($columns)) {
                unset($relations[$table]);
                continue;
            }

            ksort($relations[$table]);
        }

        $config = config('database-structure');

        if (!is_array($config)) {
            $config = [];
        }

        $config['manual_relations'] = $relations;

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        $path = config_path('database-structure.php');

        $result = $this->filesystem->put($path, $content);

        if ($result === false) {
            throw new RuntimeException('Не вдалося зберегти конфігураційний файл для ручних зв\'язків.');
        }

        config(['database-structure.manual_relations' => $relations]);
    }
}

