<?php

namespace App\Modules\DatabaseStructure\Services;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class ContentMenuManager
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function getMenu(): array
    {
        $menu = config('database-structure.content_management.menu', []);

        if (!is_array($menu)) {
            return [];
        }

        $normalized = [];

        foreach ($menu as $item) {
            if (is_string($item)) {
                $table = $this->normalizeName($item);

                if ($table === '') {
                    continue;
                }

                $normalized[$table] = [
                    'table' => $table,
                    'label' => $table,
                ];

                continue;
            }

            if (!is_array($item)) {
                continue;
            }

            $table = $this->normalizeName($item['table'] ?? null);

            if ($table === '') {
                continue;
            }

            $label = $this->normalizeLabel($item['label'] ?? null, $table);

            $normalized[$table] = [
                'table' => $table,
                'label' => $label,
            ];
        }

        ksort($normalized);

        return array_values($normalized);
    }

    public function addItem(string $table, ?string $label = null): array
    {
        $tableName = $this->normalizeName($table);

        if ($tableName === '') {
            throw new RuntimeException('Необхідно вказати назву таблиці.');
        }

        $menu = $this->getMenu();

        foreach ($menu as $item) {
            if (($item['table'] ?? null) === $tableName) {
                throw new RuntimeException('Ця таблиця вже додана до меню.');
            }
        }

        $labelValue = $this->normalizeLabel($label, $tableName);
        $menu[] = [
            'table' => $tableName,
            'label' => $labelValue,
        ];

        $this->writeConfig($menu);

        return [
            'table' => $tableName,
            'label' => $labelValue,
        ];
    }

    private function normalizeName(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        return trim($value);
    }

    private function normalizeLabel(mixed $value, string $fallback): string
    {
        if (!is_string($value) || trim($value) === '') {
            return $fallback;
        }

        return trim($value);
    }

    private function writeConfig(array $menu): void
    {
        $normalized = [];

        foreach ($menu as $item) {
            if (!is_array($item)) {
                continue;
            }

            $table = $this->normalizeName($item['table'] ?? null);

            if ($table === '') {
                continue;
            }

            $label = $this->normalizeLabel($item['label'] ?? null, $table);

            $normalized[$table] = [
                'table' => $table,
                'label' => $label,
            ];
        }

        ksort($normalized);

        $menuForConfig = array_values($normalized);

        $config = config('database-structure');

        if (!is_array($config)) {
            $config = [];
        }

        $contentManagement = $config['content_management'] ?? [];

        if (!is_array($contentManagement)) {
            $contentManagement = [];
        }

        $contentManagement['menu'] = $menuForConfig;
        $config['content_management'] = $contentManagement;

        ksort($config);

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        $path = config_path('database-structure.php');

        $result = $this->filesystem->put($path, $content);

        if ($result === false) {
            throw new RuntimeException('Не вдалося зберегти конфігурацію меню контенту.');
        }

        config([
            'database-structure.content_management.menu' => $menuForConfig,
        ]);
    }
}
