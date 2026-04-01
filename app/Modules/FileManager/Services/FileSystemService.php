<?php

namespace App\Modules\FileManager\Services;

use Illuminate\Support\Facades\File;

class FileSystemService
{
    /** @var string */
    protected $basePath;

    /** @var array */
    protected $excludedDirectories;

    /** @var array */
    protected $excludedExtensions;

    public function __construct()
    {
        $configBasePath = config('file-manager.base_path', '');
        $this->basePath = $configBasePath
            ? base_path($configBasePath)
            : base_path();

        $this->excludedDirectories = config('file-manager.excluded_directories', []);
        $this->excludedExtensions = config('file-manager.excluded_extensions', []);
    }

    /**
     * Get the file tree structure starting from a given path
     */
    public function getFileTree(?string $path = null): array
    {
        $fullPath = $this->resolveExistingPath($path ?? '');

        if (! $fullPath || ! is_dir($fullPath)) {
            return [];
        }

        return $this->buildTree($fullPath, $path ?? '');
    }

    /**
     * Build tree structure recursively
     */
    protected function buildTree(string $fullPath, string $relativePath): array
    {
        if (! is_dir($fullPath) || ! is_readable($fullPath)) {
            return [];
        }

        $items = [];
        $entries = scandir($fullPath);

        if ($entries === false) {
            return [];
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $entryFullPath = $fullPath.'/'.$entry;
            $entryRelativePath = $relativePath ? $relativePath.'/'.$entry : $entry;

            // Skip excluded directories
            if (is_dir($entryFullPath) && $this->isExcludedDirectory($entry)) {
                continue;
            }

            // Skip excluded file extensions
            if (is_file($entryFullPath) && $this->isExcludedExtension($entry)) {
                continue;
            }

            $item = [
                'name' => $entry,
                'path' => $entryRelativePath,
                'type' => is_dir($entryFullPath) ? 'directory' : 'file',
                'size' => is_file($entryFullPath) ? filesize($entryFullPath) : null,
                'modified' => filemtime($entryFullPath),
                'readable' => is_readable($entryFullPath),
                'writable' => is_writable($entryFullPath),
            ];

            if ($item['type'] === 'file') {
                $item['extension'] = pathinfo($entry, PATHINFO_EXTENSION);
                $item['mime_type'] = mime_content_type($entryFullPath) ?: 'application/octet-stream';
            }

            $items[] = $item;
        }

        // Sort: directories first, then files, both alphabetically
        usort($items, function ($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcasecmp($a['name'], $b['name']);
            }

            return $a['type'] === 'directory' ? -1 : 1;
        });

        return $items;
    }

    /**
     * Get file content
     */
    public function getFileContent(string $path, bool $enforceSizeLimit = true): ?array
    {
        $fullPath = $this->resolveExistingPath($path);

        if (! $fullPath || ! is_file($fullPath)) {
            return null;
        }

        $maxSize = config('file-manager.max_file_display_size', 1048576);
        $size = filesize($fullPath);

        if ($enforceSizeLimit && $size > $maxSize) {
            return [
                'error' => 'File is too large to display',
                'size' => $size,
                'max_size' => $maxSize,
            ];
        }

        $content = file_get_contents($fullPath);

        return [
            'path' => $path,
            'name' => basename($path),
            'content' => $content,
            'size' => $size,
            'mime_type' => mime_content_type($fullPath),
            'is_text' => $this->isTextFile($fullPath),
        ];
    }

    /**
     * Update the contents of a file.
     */
    public function updateFileContent(string $path, string $content): array
    {
        $fullPath = $this->resolveExistingPath($path);

        if (! $fullPath || ! is_file($fullPath)) {
            return ['success' => false, 'error' => 'Файл недоступний або не існує'];
        }

        if (! is_writable($fullPath)) {
            return ['success' => false, 'error' => 'Немає прав на запис у файл'];
        }

        if (! $this->isTextFile($fullPath)) {
            return ['success' => false, 'error' => 'Редагування доступне лише для текстових файлів'];
        }

        $bytes = file_put_contents($fullPath, $content, LOCK_EX);

        if ($bytes === false) {
            return ['success' => false, 'error' => 'Не вдалося зберегти файл'];
        }

        return ['success' => true, 'size' => $bytes];
    }

    /**
     * Create a new text file.
     */
    public function createFile(string $path, string $content = ''): array
    {
        $normalizedPath = $this->normalizeRelativePath($path);

        if ($normalizedPath === '') {
            return ['success' => false, 'error' => 'Шлях до файлу є обов’язковим'];
        }

        $fullPath = $this->resolveCreatablePath($normalizedPath);

        if (! $fullPath) {
            return ['success' => false, 'error' => 'Недійсний шлях до файлу'];
        }

        if (file_exists($fullPath)) {
            return ['success' => false, 'error' => 'Файл уже існує'];
        }

        $parentDirectory = dirname($fullPath);

        if (! is_dir($parentDirectory) || ! is_writable($parentDirectory)) {
            return ['success' => false, 'error' => 'Немає прав на запис у директорію'];
        }

        $bytes = file_put_contents($fullPath, $content, LOCK_EX);

        if ($bytes === false) {
            return ['success' => false, 'error' => 'Не вдалося створити файл'];
        }

        return [
            'success' => true,
            'path' => $normalizedPath,
            'size' => $bytes,
        ];
    }

    /**
     * Create a new directory.
     */
    public function createDirectory(string $path): array
    {
        $normalizedPath = $this->normalizeRelativePath($path);

        if ($normalizedPath === '') {
            return ['success' => false, 'error' => 'Шлях до директорії є обов’язковим'];
        }

        $fullPath = $this->resolveCreatablePath($normalizedPath);

        if (! $fullPath) {
            return ['success' => false, 'error' => 'Недійсний шлях до директорії'];
        }

        if (file_exists($fullPath)) {
            return ['success' => false, 'error' => 'Директорія вже існує'];
        }

        $parentDirectory = dirname($fullPath);

        if (! is_dir($parentDirectory) || ! is_writable($parentDirectory)) {
            return ['success' => false, 'error' => 'Немає прав на створення директорії'];
        }

        if (! mkdir($fullPath, 0775, false) && ! is_dir($fullPath)) {
            return ['success' => false, 'error' => 'Не вдалося створити директорію'];
        }

        return [
            'success' => true,
            'path' => $normalizedPath,
        ];
    }

    /**
     * Delete a file or directory recursively.
     */
    public function deletePath(string $path): array
    {
        $normalizedPath = $this->normalizeRelativePath($path);

        if ($normalizedPath === '') {
            return ['success' => false, 'error' => 'Кореневу директорію не можна видалити'];
        }

        $fullPath = $this->resolveExistingPath($normalizedPath);

        if (! $fullPath || ! file_exists($fullPath)) {
            return ['success' => false, 'error' => 'Файл або директорію не знайдено'];
        }

        $parentDirectory = dirname($fullPath);

        if (! is_writable($parentDirectory)) {
            return ['success' => false, 'error' => 'Немає прав на видалення'];
        }

        if (is_file($fullPath) || is_link($fullPath)) {
            if (! @unlink($fullPath)) {
                return ['success' => false, 'error' => 'Не вдалося видалити файл'];
            }

            return [
                'success' => true,
                'path' => $normalizedPath,
                'type' => 'file',
            ];
        }

        if (! File::deleteDirectory($fullPath)) {
            return ['success' => false, 'error' => 'Не вдалося видалити директорію'];
        }

        return [
            'success' => true,
            'path' => $normalizedPath,
            'type' => 'directory',
        ];
    }

    /**
     * Get file information
     */
    public function getFileInfo(string $path): ?array
    {
        $fullPath = $this->resolveExistingPath($path);

        if (! $fullPath) {
            return null;
        }

        if (! file_exists($fullPath)) {
            return null;
        }

        $info = [
            'path' => $path,
            'name' => basename($path),
            'type' => is_dir($fullPath) ? 'directory' : 'file',
            'size' => is_file($fullPath) ? filesize($fullPath) : null,
            'modified' => filemtime($fullPath),
            'readable' => is_readable($fullPath),
            'writable' => is_writable($fullPath),
            'permissions' => substr(sprintf('%o', fileperms($fullPath)), -4),
        ];

        if ($info['type'] === 'file') {
            $info['extension'] = pathinfo($path, PATHINFO_EXTENSION);
            $info['mime_type'] = mime_content_type($fullPath) ?: 'application/octet-stream';
        }

        return $info;
    }

    /**
     * Check if path is valid and within base path
     */
    protected function isValidPath(string $path): bool
    {
        $realPath = realpath($path);
        $realBasePath = realpath($this->basePath);

        if (! $realPath || ! $realBasePath) {
            return false;
        }

        return $this->isWithinBasePath($realPath, $realBasePath);
    }

    /**
     * Check if directory should be excluded
     */
    protected function isExcludedDirectory(string $dirname): bool
    {
        return in_array($dirname, $this->excludedDirectories);
    }

    /**
     * Check if file extension should be excluded
     */
    protected function isExcludedExtension(string $filename): bool
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return in_array('.'.$extension, $this->excludedExtensions);
    }

    /**
     * Check if file is a text file
     */
    protected function isTextFile(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, $this->textFileExtensions(), true)) {
            return true;
        }

        $mimeType = mime_content_type($path);

        if (! $mimeType) {
            return false;
        }

        // Check for common text mime types
        return str_starts_with($mimeType, 'text/') ||
               in_array($mimeType, [
                   'application/json',
                   'application/ld+json',
                   'application/xml',
                   'application/javascript',
                   'application/x-httpd-php',
                   'application/x-sh',
                   'application/sql',
                   'application/toml',
                   'application/x-yaml',
                   'application/yaml',
                   'image/svg+xml',
               ]);
    }

    /**
     * Format file size to human readable format
     */
    public function formatFileSize(?int $bytes): string
    {
        if ($bytes === null) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Get base path
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    protected function resolveExistingPath(string $path): ?string
    {
        $relativePath = $this->normalizeRelativePath($path);
        $fullPath = $relativePath !== ''
            ? $this->basePath.'/'.$relativePath
            : $this->basePath;

        if (! $this->isValidPath($fullPath)) {
            return null;
        }

        return realpath($fullPath) ?: null;
    }

    protected function resolveCreatablePath(string $path): ?string
    {
        $relativePath = $this->normalizeRelativePath($path);

        if ($relativePath === '') {
            return null;
        }

        $fullPath = $this->basePath.'/'.$relativePath;
        $parentDirectory = dirname($fullPath);
        $realParentDirectory = realpath($parentDirectory);
        $realBasePath = realpath($this->basePath);

        if (! $realParentDirectory || ! $realBasePath) {
            return null;
        }

        if (! $this->isWithinBasePath($realParentDirectory, $realBasePath)) {
            return null;
        }

        return $realParentDirectory.'/'.basename($fullPath);
    }

    protected function normalizeRelativePath(string $path): string
    {
        $normalized = str_replace('\\', '/', trim($path));
        $normalized = preg_replace('#/+#', '/', $normalized) ?? $normalized;

        return trim($normalized, '/');
    }

    protected function isWithinBasePath(string $path, string $basePath): bool
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedBasePath = rtrim(str_replace('\\', '/', $basePath), '/');

        return $normalizedPath === $normalizedBasePath
            || str_starts_with($normalizedPath, $normalizedBasePath.'/');
    }

    /**
     * @return array<int, string>
     */
    protected function textFileExtensions(): array
    {
        return [
            'php',
            'phtml',
            'js',
            'jsx',
            'ts',
            'tsx',
            'mjs',
            'cjs',
            'json',
            'json5',
            'map',
            'html',
            'htm',
            'xhtml',
            'xml',
            'svg',
            'css',
            'scss',
            'sass',
            'less',
            'md',
            'markdown',
            'txt',
            'log',
            'sql',
            'yml',
            'yaml',
            'ini',
            'env',
            'conf',
            'config',
            'toml',
            'properties',
            'gitignore',
            'gitattributes',
            'editorconfig',
            'npmrc',
            'sh',
            'bash',
            'zsh',
            'bat',
            'cmd',
            'ps1',
            'py',
            'rb',
            'java',
            'kt',
            'kts',
            'c',
            'cc',
            'cpp',
            'cxx',
            'h',
            'hpp',
            'cs',
            'go',
            'rs',
            'vue',
            'svelte',
            'lock',
            'csv',
            'tsv',
            'graphql',
            'gql',
        ];
    }
}
