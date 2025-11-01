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
     * @return array<int, array{table: string, label: string}>
     */
    public function getMenu(): array
    {
        $menu = config('database-structure.content_management.menu', []);

        if (!is_array($menu)) {
            return [];
        }

        $normalized = [];

        foreach ($menu as $item) {
            $normalizedItem = $this->normalizeItem($item);

            if ($normalizedItem === null) {
                continue;
            }

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

        $item = [
            'table' => $tableName,
            'label' => $this->normalizeLabel($label) ?: $tableName,
        ];

        $menu = array_values(array_filter(
            $menu,
            static fn (array $entry): bool => ($entry['table'] ?? '') !== $tableName,
        ));

        $menu[] = $item;

        $this->writeConfig($menu);

        return $item;
    }

    /**
     * @param array<int, array{table?: mixed, label?: mixed}|string> $items
     * @return array<int, array{table: string, label: string}>
     */
    public function updateMenu(array $items): array
    {
        $normalized = [];
        $seen = [];

        foreach ($items as $item) {
            $normalizedItem = $this->normalizeItem($item);

            if ($normalizedItem === null) {
                continue;
            }

            if (in_array($normalizedItem['table'], $seen, true)) {
                continue;
            }

            $normalized[] = $normalizedItem;
            $seen[] = $normalizedItem['table'];
        }

        $this->writeConfig($normalized);

        return $normalized;
    }

    public function delete(string $table): void
    {
        $tableName = $this->normalizeName($table);

        if ($tableName === '') {
            throw new RuntimeException('Необхідно вказати таблицю для видалення з меню.');
        }

        $menu = $this->getMenu();
        $originalCount = count($menu);

        $menu = array_values(array_filter(
            $menu,
            static fn (array $entry): bool => ($entry['table'] ?? '') !== $tableName,
        ));

        if (count($menu) === $originalCount) {
            throw new RuntimeException('Цю таблицю не знайдено в меню.');
        }

        $this->writeConfig($menu);
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

    private function writeConfig(array $menu): void
    {
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

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        $path = config_path('database-structure.php');

        if ($this->filesystem->put($path, $content) === false) {
            throw new RuntimeException('Не вдалося зберегти налаштування меню Content Management.');
        }

        config(['database-structure.content_management.menu' => $config['content_management']['menu']]);
    }
}
