<?php

namespace App\Modules\DatabaseStructure\Services;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class ContentManagementMenuManager
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    /**
     * @return array<int, array{table: string, label: string, is_default: bool}>
     */
    public function getMenu(): array
    {
        $menu = config('database-structure.content_management.menu', []);
        $defaultTable = $this->getDefaultTable();

        if (!is_array($menu)) {
            return [];
        }

        $normalized = [];

        foreach ($menu as $item) {
            $normalizedItem = $this->normalizeItem($item);

            if ($normalizedItem === null) {
                continue;
            }

            $normalizedItem['is_default'] = $defaultTable !== '' && $normalizedItem['table'] === $defaultTable;

            $normalized[$normalizedItem['table']] = $normalizedItem;
        }

        return array_values($normalized);
    }

    public function add(string $table, ?string $label = null): array
    {
        $tableName = $this->normalizeName($table);

        if ($tableName === '') {
            throw new RuntimeException('Необхідно вказати таблицю для додавання до меню.');
        }

        $menu = $this->getMenu();

        $defaultTable = $this->getDefaultTable();
        $item = [
            'table' => $tableName,
            'label' => $this->normalizeLabel($label) ?: $tableName,
            'is_default' => $defaultTable !== '' && $defaultTable === $tableName,
        ];

        $menu = array_values(array_filter(
            $menu,
            static fn (array $entry): bool => ($entry['table'] ?? '') !== $tableName,
        ));

        $menu[] = $item;

        $this->writeConfig($menu, $defaultTable);

        return $item;
    }

    /**
     * @param array<int, array{table?: mixed, label?: mixed, is_default?: mixed, default?: mixed}|string> $items
     * @return array<int, array{table: string, label: string, is_default: bool}>
     */
    public function updateMenu(array $items): array
    {
        $normalized = [];
        $seen = [];
        $defaultCandidate = '';

        foreach ($items as $item) {
            $normalizedItem = $this->normalizeItem($item);

            if ($normalizedItem === null) {
                continue;
            }

            if (in_array($normalizedItem['table'], $seen, true)) {
                continue;
            }

            if ($normalizedItem['is_default'] && $defaultCandidate === '') {
                $defaultCandidate = $normalizedItem['table'];
            }

            $normalized[] = $normalizedItem;
            $seen[] = $normalizedItem['table'];
        }

        $currentDefault = $this->getDefaultTable();
        $defaultTable = $defaultCandidate !== ''
            ? $defaultCandidate
            : (in_array($currentDefault, $seen, true) ? $currentDefault : '');

        $normalized = array_map(
            function (array $entry) use ($defaultTable): array {
                $entry['is_default'] = $defaultTable !== '' && $entry['table'] === $defaultTable;

                return $entry;
            },
            $normalized,
        );

        $this->writeConfig($normalized, $defaultTable);

        return $normalized;
    }

    public function delete(string $table): void
    {
        $tableName = $this->normalizeName($table);

        if ($tableName === '') {
            throw new RuntimeException('Необхідно вказати таблицю для видалення з меню.');
        }

        $menu = $this->getMenu();
        $defaultTable = $this->getDefaultTable();
        $originalCount = count($menu);

        $menu = array_values(array_filter(
            $menu,
            static fn (array $entry): bool => ($entry['table'] ?? '') !== $tableName,
        ));

        if (count($menu) === $originalCount) {
            throw new RuntimeException('Цю таблицю не знайдено в меню.');
        }

        if ($defaultTable === $tableName) {
            $defaultTable = '';
        }

        $this->writeConfig($menu, $defaultTable);
    }

    private function normalizeItem(mixed $item): ?array
    {
        if (!is_array($item)) {
            return null;
        }

        $table = $this->normalizeName($item['table'] ?? null);

        if ($table === '') {
            return null;
        }

        $label = $this->normalizeLabel($item['label'] ?? null);

        if ($label === '') {
            $label = $table;
        }

        return [
            'table' => $table,
            'label' => $label,
            'is_default' => $this->normalizeBoolean($item['is_default'] ?? $item['default'] ?? false),
        ];
    }

    private function normalizeName(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function normalizeLabel(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
        }

        if (is_numeric($value)) {
            return (int) $value !== 0;
        }

        return false;
    }

    private function getDefaultTable(): string
    {
        $value = config('database-structure.content_management.default_table');

        return is_string($value) ? trim($value) : '';
    }

    private function writeConfig(array $menu, ?string $defaultTable = null): void
    {
        $normalizedDefault = $this->normalizeName($defaultTable);
        $availableTables = array_map(
            static fn (array $item): string => $item['table'] ?? '',
            $menu,
        );

        if ($normalizedDefault !== '' && !in_array($normalizedDefault, $availableTables, true)) {
            $normalizedDefault = '';
        }

        $config = config('database-structure');

        if (!is_array($config)) {
            $config = [];
        }

        if (!isset($config['content_management']) || !is_array($config['content_management'])) {
            $config['content_management'] = [];
        }

        $config['content_management']['menu'] = array_values(array_map(
            static function (array $item): array {
                return [
                    'table' => $item['table'],
                    'label' => $item['label'],
                ];
            },
            $menu,
        ));

        if ($normalizedDefault !== '') {
            $config['content_management']['default_table'] = $normalizedDefault;
        } else {
            unset($config['content_management']['default_table']);
        }

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        $path = config_path('database-structure.php');

        if ($this->filesystem->put($path, $content) === false) {
            throw new RuntimeException('Не вдалося зберегти налаштування меню Content Management.');
        }

        config(['database-structure.content_management.menu' => $config['content_management']['menu']]);
        config(['database-structure.content_management.default_table' => $normalizedDefault !== '' ? $normalizedDefault : null]);
    }
}
