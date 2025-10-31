<?php

namespace App\Modules\DatabaseStructure\Services;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class ContentManagementMenuManager
{
    public function __construct(
        private Filesystem $filesystem,
        private DatabaseStructureFetcher $fetcher,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function getTables(): array
    {
        $tables = config('database-structure.content_management.tables', []);

        if (!is_array($tables)) {
            return [];
        }

        $normalized = [];

        foreach ($tables as $table) {
            $name = $this->normalizeName($table);

            if ($name === '') {
                continue;
            }

            $normalized[$name] = $name;
        }

        return array_values($normalized);
    }

    public function addTable(string $table): array
    {
        $tableName = $this->normalizeName($table);

        if ($tableName === '') {
            throw new RuntimeException('Необхідно вказати назву таблиці.');
        }

        $this->ensureTableExists($tableName);

        $tables = $this->getTables();

        if (!in_array($tableName, $tables, true)) {
            $tables[] = $tableName;
        }

        $this->writeConfig($tables);

        return $tables;
    }

    private function ensureTableExists(string $table): void
    {
        $structure = $this->fetcher->getStructureSummary();

        foreach ($structure as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = $this->normalizeName($item['name'] ?? null);

            if ($name === $table) {
                return;
            }
        }

        throw new RuntimeException("Таблиця '{$table}' не знайдена у поточному підключенні.");
    }

    private function normalizeName(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    /**
     * @param array<int, string> $tables
     */
    private function writeConfig(array $tables): void
    {
        $normalized = [];

        foreach ($tables as $table) {
            $name = $this->normalizeName($table);

            if ($name === '') {
                continue;
            }

            $normalized[$name] = $name;
        }

        $config = config('database-structure');

        if (!is_array($config)) {
            $config = [];
        }

        $contentConfig = $config['content_management'] ?? [];

        if (!is_array($contentConfig)) {
            $contentConfig = [];
        }

        $contentConfig['tables'] = array_values($normalized);
        $config['content_management'] = $contentConfig;

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        $path = config_path('database-structure.php');

        $result = $this->filesystem->put($path, $content);

        if ($result === false) {
            throw new RuntimeException('Не вдалося зберегти конфігураційний файл для меню Content Management.');
        }

        config(['database-structure.content_management.tables' => array_values($normalized)]);
    }
}

