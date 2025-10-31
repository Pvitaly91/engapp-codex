<?php

namespace App\Modules\DatabaseStructure\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

class ContentManagementMenuManager
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    /**
     * @return array<int, array{key: string, table: string, label: string, description: string}>
     */
    public function getMenu(): array
    {
        $menu = config('database-structure.content_management.menu', []);

        if (!is_array($menu)) {
            return [];
        }

        $normalized = [];

        foreach ($menu as $index => $item) {
            $normalizedItem = $this->normalizeMenuItem($item, $index);

            if ($normalizedItem !== null) {
                $normalized[] = $normalizedItem;
            }
        }

        return $normalized;
    }

    /**
     * @return array{key: string, table: string, label: string, description: string}
     */
    public function add(string $table, ?string $label = null, ?string $description = null): array
    {
        $tableName = $this->normalizeValue($table);

        if ($tableName === '') {
            throw new RuntimeException('Не вдалося визначити таблицю для додавання в меню.');
        }

        $menu = $this->getMenu();

        foreach ($menu as $item) {
            if (isset($item['table']) && strcasecmp($item['table'], $tableName) === 0) {
                throw new RuntimeException('Ця таблиця вже додана до меню керування контентом.');
            }
        }

        $labelText = $this->normalizeOptional($label);
        $descriptionText = $this->normalizeOptional($description);

        if ($labelText === '') {
            $labelText = $tableName;
        }

        $key = $this->generateKey($tableName, $menu);

        $item = [
            'key' => $key,
            'table' => $tableName,
            'label' => $labelText,
            'description' => $descriptionText,
        ];

        $menu[] = $item;

        $this->writeConfig($menu);

        return $item;
    }

    private function normalizeMenuItem(mixed $item, int $index): ?array
    {
        if (is_string($item)) {
            $table = $this->normalizeValue($item);

            if ($table === '') {
                return null;
            }

            return [
                'key' => sprintf('content-%d-%s', $index, Str::slug($table, '-')),
                'table' => $table,
                'label' => $table,
                'description' => '',
            ];
        }

        if (!is_array($item)) {
            return null;
        }

        $tableCandidates = [
            $this->normalizeValue($item['table'] ?? null),
            $this->normalizeValue($item['name'] ?? null),
            $this->normalizeValue($item['slug'] ?? null),
        ];

        $table = '';

        foreach ($tableCandidates as $candidate) {
            if ($candidate !== '') {
                $table = $candidate;
                break;
            }
        }

        if ($table === '') {
            return null;
        }

        $labelCandidates = [
            $this->normalizeValue($item['label'] ?? null),
            $this->normalizeValue($item['title'] ?? null),
            $table,
        ];

        $label = '';

        foreach ($labelCandidates as $candidate) {
            if ($candidate !== '') {
                $label = $candidate;
                break;
            }
        }

        if ($label === '') {
            $label = $table;
        }

        $description = $this->normalizeOptional($item['description'] ?? null);
        $key = $this->normalizeValue($item['key'] ?? null);

        if ($key === '') {
            $key = sprintf('content-%d-%s', $index, Str::slug($table, '-'));
        }

        return [
            'key' => $key,
            'table' => $table,
            'label' => $label,
            'description' => $description,
        ];
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

        $config['content_management']['menu'] = array_map(
            function (array $item): array {
                return [
                    'key' => $item['key'],
                    'table' => $item['table'],
                    'label' => $item['label'],
                    'description' => $item['description'],
                ];
            },
            array_values($menu)
        );

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        $path = config_path('database-structure.php');

        $result = $this->filesystem->put($path, $content);

        if ($result === false) {
            throw new RuntimeException('Не вдалося зберегти конфігураційний файл меню керування контентом.');
        }

        config(['database-structure.content_management.menu' => $config['content_management']['menu']]);
    }

    private function normalizeValue(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function normalizeOptional(mixed $value): string
    {
        $normalized = $this->normalizeValue($value);

        return $normalized;
    }

    private function generateKey(string $table, array $menu): string
    {
        $base = 'content-' . Str::slug($table, '-');

        if ($base === 'content-') {
            $base = 'content-' . md5($table);
        }

        $existingKeys = array_map(
            static fn ($item) => is_array($item) && isset($item['key']) ? (string) $item['key'] : '',
            $menu
        );

        $key = $base;
        $suffix = 2;

        while (in_array($key, $existingKeys, true)) {
            $key = $base . '-' . $suffix;
            $suffix++;
        }

        return $key;
    }
}
