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
     * Отримати нормалізоване меню для керування контентом.
     */
    public function getMenu(): array
    {
        $menu = config('database-structure.content_management.menu', []);

        if (!is_array($menu)) {
            return [];
        }

        $normalized = [];
        $seenTables = [];

        foreach ($menu as $item) {
            $normalizedItem = $this->normalizeItem($item);

            if ($normalizedItem === null) {
                continue;
            }

            if (isset($seenTables[$normalizedItem['table']])) {
                continue;
            }

            $seenTables[$normalizedItem['table']] = true;
            $normalized[] = $normalizedItem;
        }

        return $normalized;
    }

    /**
     * Зберегти оновлене меню та повернути нормалізовану версію.
     *
     * @param  array  $menuItems
     */
    public function saveMenu(array $menuItems): array
    {
        $normalized = [];
        $seenTables = [];

        foreach ($menuItems as $item) {
            $normalizedItem = $this->normalizeItem($item);

            if ($normalizedItem === null) {
                continue;
            }

            if (isset($seenTables[$normalizedItem['table']])) {
                continue;
            }

            $seenTables[$normalizedItem['table']] = true;
            $normalized[] = $normalizedItem;
        }

        $config = config('database-structure');

        if (!is_array($config)) {
            $config = [];
        }

        $contentManagement = $config['content_management'] ?? [];

        if (!is_array($contentManagement)) {
            $contentManagement = [];
        }

        $contentManagement['menu'] = $normalized;
        $config['content_management'] = $contentManagement;

        $path = config_path('database-structure.php');
        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";

        $result = $this->filesystem->put($path, $content);

        if ($result === false) {
            throw new RuntimeException('Не вдалося зберегти конфігураційний файл для меню контенту.');
        }

        config(['database-structure.content_management.menu' => $normalized]);

        return $normalized;
    }

    private function normalizeItem(mixed $item): ?array
    {
        if (is_string($item)) {
            $table = trim($item);

            if ($table === '') {
                return null;
            }

            return [
                'table' => $table,
                'label' => $table,
            ];
        }

        if (!is_array($item)) {
            return null;
        }

        $table = $this->normalizeString($item['table'] ?? null);

        if ($table === '') {
            return null;
        }

        $label = $this->normalizeString($item['label'] ?? ($item['name'] ?? null));

        if ($label === '') {
            $label = $table;
        }

        return [
            'table' => $table,
            'label' => $label,
        ];
    }

    private function normalizeString(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}
